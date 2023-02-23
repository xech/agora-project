<?php
/**
* This file is part of the Agora-Project Software package.
*
* @copyright (c) Agora-Project Limited <https://www.agora-project.net>
* @license GNU General Public License, version 2 (GPL-2.0)
*/


/*
 * CONTROLEUR DU MODULE "MAIL"
 */
class CtrlMail extends Ctrl
{
	const moduleName="mail";

	/*******************************************************************************************
	 * VUE : PAGE PRINCIPALE
	 *******************************************************************************************/
	public static function actionDefault()
	{
		////	Controle d'accès && Supprime les mails de plus d'un an
		if(Ctrl::$curUser->isUser()==false)  {Ctrl::noAccessExit();}
		////	Envoi un mail
		if(Req::isParam("formValidate","title","description") && (Req::isParam("personList") || Req::isParam("groupList")))
		{
			////	Destinataires : users/contacts (personList)
			$mailTo=null;
			if(Req::isParam("personList")){
				foreach(Req::param("personList") as $personTypeId){
					$personObj=Ctrl::getObjTarget($personTypeId);
					if(!empty($personObj->mail))  {$mailTo.=$personObj->mail.",";}
				}
			}
			////	Destinataires : groupes d'users
			if(Req::isParam("groupList")){
				foreach(Req::param("groupList") as $groupTypeId){
					$groupObj=Ctrl::getObjTarget($groupTypeId);
					if(is_object($groupObj)){
						foreach($groupObj->userIds as $userId){
							$tmpUser=Ctrl::getObj("user",$userId);
							if(!empty($tmpUser->mail))	{$mailTo.=$tmpUser->mail.",";}
						}
					}
				}
			}
			////	Enregistre un nouvel email  &&  Recharge le mail pour récupérer les "attachedFileList()" des Inputs
			$curObj=Ctrl::getObj("mail");
			$curObj=$curObj->createUpdate("title=".Db::param("title").", description=".Db::param("description","editor").", recipients=".Db::format(trim($mailTo,",")));
			////	S'il s'agit d'un email reloadé : on copie chaque pièce jointe
			if(Req::isParam("oldMailTypeId"))
			{
				$oldMail=Ctrl::getObjTarget(Req::param("oldMailTypeId"));																									//Charge l'ancien email 
				foreach($oldMail->attachedFileList() as $oldFile){																											//Parcourt chaque fichier joint
					$_idNewFile=Db::query("INSERT INTO ap_objectAttachedFile SET name=".Db::format($oldFile["name"]).", objectType='mail', _idObject=".$curObj->_id, true);	//Copie le fichier en BDD
					copy($oldFile["path"], PATH_OBJECT_ATTACHMENT.$_idNewFile.".".File::extension($oldFile["name"]));														//Copie dans DATAS  
					$curObj->description=str_replace($oldFile["url"], CtrlObject::attachedFileDisplayUrl($_idNewFile,$oldFile["name"]), $curObj->description);				//Remplace le "attachedFileDisplayUrl()" dans le texte de l'éditeur
				}
				//Update le texte de l'éditeur  &&  recharge l'email (avec les nouveaux fichiers & co)
				Db::query("UPDATE ".$curObj::dbTable." SET ".$curObj::htmlEditorField."=".Db::format($curObj->description,"editor")." WHERE _id=".$curObj->_id);
				$curObj=Ctrl::getObjTarget($curObj->_typeId);
			}
			////	Envoi du mail
			$description=$curObj->description;//Description de l'objet rechargé ci-dessus !
			$description=$curObj->attachedFileImageCid($description);//Affiche si besoin les images en pièce jointe dans le corps du mail
			Tool::sendMail($mailTo, Req::param("title"), $description, Req::param("mailOptions"), $curObj->attachedFileList());
			////	Redirige vers la mage principal (évite un re-post du mail..)
			Ctrl::redir("?ctrl=mail");
		}
		////	Liste des espaces et users associés
		$vDatas["containerList"]=[];
		foreach(Ctrl::$curUser->getSpaces() as $tmpContainer){
			$tmpContainer->personList=$tmpContainer->getUsers();
			if(!empty($tmpContainer->personList))  {$vDatas["containerList"][]=$tmpContainer;}
		}
		////	Arborescence des dossiers de contacts (du dossier "root")
		foreach(Ctrl::getObj("contactFolder",1)->folderTree() as $tmpContainer){
			$tmpContainer->personList=Db::getObjTab("contact", "SELECT * FROM ap_contact WHERE LENGTH(mail)>0 AND ".MdlContact::sqlDisplay($tmpContainer)." ".MdlContact::sqlSort());
			if(!empty($tmpContainer->personList))  {$vDatas["containerList"][]=$tmpContainer;}
		}
		////	Charge un ancien mail ou un nouveau mail  &&  Affiche la page
		$vDatas["curMail"]=Req::isParam("oldMailTypeId")  ?  Ctrl::getObjTarget(Req::param("oldMailTypeId"))  :  Ctrl::getObj("mail");
		static::displayPage("VueIndex.php",$vDatas);
	}

	/*******************************************************************************************
	 * VUE : HITORIQUE DES MAILS QUE L'USER COURANT A ENVOYE
	 *******************************************************************************************/
	public static function actionMailHistory()
	{
		$vDatas["mailList"]=Db::getObjTab("mail", "SELECT * FROM ap_mail WHERE _idUser=".Ctrl::$curUser->_id." ORDER BY dateCrea desc");
		static::displayPage("VueMailHistory.php",$vDatas);
	}
}