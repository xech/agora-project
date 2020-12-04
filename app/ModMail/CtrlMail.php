<?php
/**
* This file is part of the Agora-Project Software package.
*
* @copyright (c) Agora-Project Limited <https://www.agora-project.net>
* @license GNU General Public License, version 2 (GPL-2.0)
*/


/*
 * Controleur du module "Mail"
 */
class CtrlMail extends Ctrl
{
	const moduleName="mail";

	/*
	 * ACTION PAR DEFAUT
	 */
	public static function actionDefault()
	{
		////	Init et Controle d'accès
		$vDatas["containerList"]=array();
		if(Ctrl::$curUser->isUser()==false)  {Ctrl::noAccessExit();}
		////	Envoi de mail!
		if(Req::isParam("formValidate","subject","message") && (Req::isParam("personList") || Req::isParam("groupList")))
		{
			////	liste des destinataires : personList & groupes d'users
			$mailTo=null;
			//liste de personnes
			if(Req::isParam("personList"))
			{
				foreach(Req::getParam("personList") as $tmpPerson){
					$tmpPersonObj=Ctrl::getTargetObj($tmpPerson);
					if(!empty($tmpPersonObj->mail))  {$mailTo.=$tmpPersonObj->mail.",";}
				}
			}
			//Liste des users de groupe
			if(Req::isParam("groupList"))
			{
				foreach(Req::getParam("groupList") as $tmpGroup){
					$tmpGroupObj=Ctrl::getTargetObj($tmpGroup);
					if(is_object($tmpGroupObj)){
						foreach($tmpGroupObj->userIds as $tmpUserId){
							$tmpUser=Ctrl::getObj("user",$tmpUserId);
							if(!empty($tmpUser->mail))	{$mailTo.=$tmpUser->mail.",";}
						}
					}
				}
			}
			////	Options
			$options=null;
			if(Req::getParam("receptionNotif"))	{$options.="receptionNotif,";}
			if(Req::getParam("hideRecipients"))	{$options.="hideRecipients,";}
			if(Req::getParam("addReplyTo"))		{$options.="addReplyTo,";}
			if(Req::getParam("noFooter"))		{$options.="noFooter,";}
			////	Fichiers joints
			$attachedFiles=[];
			if(!empty($_FILES)){
				foreach($_FILES as $tmpFile){
					if(is_file($tmpFile["tmp_name"]))  {$attachedFiles[]=array("path"=>$tmpFile["tmp_name"],"name"=>$tmpFile["name"]);}
				}
			}
			////	Envoi du mail
			$isSendMail=Tool::sendMail($mailTo, Req::getParam("subject"), Req::getParam("message"), $options, $attachedFiles);
			if($isSendMail==true){
				Db::query("INSERT INTO ap_mailHistory SET recipients=".Db::format(trim($mailTo,",")).", title=".Db::formatParam("subject").", description=".Db::formatParam("message","editor").", dateCrea=".Db::dateNow().", _idUser=".Ctrl::$curUser->_id);
			}
		}
		////	Supprime les anciens mails de plus d'1 an
		Db::query("DELETE FROM ap_mailHistory WHERE UNIX_TIMESTAMP(dateCrea) <= ".intval(time()-(360*86400)));
		////	Liste des espaces et users associés
		foreach(Ctrl::$curUser->getSpaces() as $tmpContainer){
			$tmpContainer->personList=$tmpContainer->getUsers();
			if(!empty($tmpContainer->personList))  {$vDatas["containerList"][]=$tmpContainer;}
		}
		////	Arborescence des dossiers de contacts (du dossier "root")
		foreach(Ctrl::getObj("MdlContactFolder",1)->folderTree() as $tmpContainer){
			$tmpContainer->personList=Db::getObjTab("contact", "SELECT * FROM ap_contact WHERE LENGTH(mail)>0 AND ".MdlContact::sqlDisplayedObjects($tmpContainer)." ".MdlContact::sqlSort());
			if(!empty($tmpContainer->personList))  {$vDatas["containerList"][]=$tmpContainer;}
		}
		static::displayPage("VueIndex.php",$vDatas);
	}

	/*
	 * VUE : HITORIQUE DES MAILS QUE L'USER COURANT A ENVOYE
	 */
	public static function actionMailHistory()
	{
		//Suppression de mail
		if(Req::getParam("actionDelete")){
			$sqlIdUser=(Ctrl::$curUser->isAdminGeneral()==false)  ?  "AND _idUser=".Ctrl::$curUser->_id  :  null;
			Db::query("DELETE FROM ap_mailHistory WHERE _id=".(int)Req::getParam("_idMail")." ".$sqlIdUser);
		}
		$vDatas["mailList"]=Db::getTab("SELECT * FROM ap_mailHistory WHERE _idUser=".Ctrl::$curUser->_id." AND _idUser>0 ORDER BY dateCrea desc");
		static::displayPage("VueMailHistory.php",$vDatas);
	}
}