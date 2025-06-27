<?php
/**
* This file is part of the Agora-Project Software package
*
* @copyleft Agora-Project <https://www.agora-project.net>
* @license GNU General Public License (GPL-2.0)
*/


/*
 * CONTROLEUR DU MODULE "MAIL"
 */
class CtrlMail extends Ctrl
{
	const moduleName="mail";

	/********************************************************************************************************
	 * VUE : PAGE PRINCIPALE
	 ********************************************************************************************************/
	public static function actionDefault()
	{
		////	Controle d'accès && Supprime les mails de plus d'un an
		if(Ctrl::$curUser->isGuest())  {Ctrl::noAccessExit();}
		////	Envoi un mail
		if(Req::isParam("formValidate","title","description") && (Req::isParam("personList") || Req::isParam("groupList")))
		{
			////	Destinataires : users/contacts (personList)
			$mailTo=null;
			if(Req::isParam("personList")){
				foreach(Req::param("personList") as $personTypeId){
					$personObj=Ctrl::getCurObj($personTypeId);
					if(!empty($personObj->mail))  {$mailTo.=$personObj->mail.",";}
				}
			}
			////	Destinataires : groupes d'users
			if(Req::isParam("groupList")){
				foreach(Req::param("groupList") as $groupTypeId){
					$groupObj=Ctrl::getCurObj($groupTypeId);
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
			$curObj=$curObj->editRecord("`title`=".Db::param("title").", `description`=".Db::param("description").", recipients=".Db::format(trim($mailTo,",")));
			if(Req::isParam("reloadMailTypeId")){																		//Email reloadé -> on copie chaque pièce jointe :
				$oldMail=Ctrl::getCurObj(Req::param("reloadMailTypeId"));											//Charge l'ancien mail
				foreach($oldMail->attachedFileList() as $oldFile){														//Parcourt chaque fichier joint
					$_idNewFile=Db::query("INSERT INTO ap_objectAttachedFile SET name=".Db::format($oldFile["name"]).", objectType='mail', _idObject=".$curObj->_id, true);//Enregistre en BDD
					copy($oldFile["path"], PATH_OBJECT_ATTACHMENT.$_idNewFile.'.'.File::extension($oldFile["name"]));	//Copie dans DATAS
					$dom=$curObj->descriptionDOM();																		//Récupère le DOM de la description
					$xpath=new DOMXPath($dom);																			//Créé un XPath pour naviguer dans le DOM
					$nodes=$xpath->query('//img[@id="attachedFileTag'.$oldFile["_id"].'"]');							//Trouve les tags <img> avec l'attribut id="attachedFileTagXX"
					foreach($nodes as $node){																			//Parcourt chaque tag
						$node->setAttribute('id', 'attachedFileTag'.$_idNewFile);										//Update l'attribut "id"
						$node->setAttribute('src', MdlObject::attachedFileDisplayUrl($_idNewFile,$oldFile["name"]));	//Update l'attribut "src"
					}
					$curObj->description=$dom->saveHTML();
				}
				//Update la description et recharge l'email
				Db::query("UPDATE ".$curObj::dbTable." SET `description`=".Db::format($curObj->description)." WHERE _id=".$curObj->_id);
				$curObj=Ctrl::getCurObj($curObj->_typeId);
			}
			////	Envoi du mail
			$message=$curObj->descriptionMail();																			//Description avec intégration des images (ex: <img src="cid:attachedFileXX">) 
			Tool::sendMail($mailTo, Req::param("title"), $message, Req::param("mailOptions"), $curObj->attachedFileList());	//Envoie l'email
			Ctrl::redir("?ctrl=mail");																						//Redirige vers la mage principal (évite un re-post..)
		}
		////	Liste des espaces et users associés
		$vDatas["containerList"]=[];
		foreach(Ctrl::$curUser->spaceList() as $tmpContainer){
			$tmpContainer->personList=$tmpContainer->getUsers();
			if(!empty($tmpContainer->personList))  {$vDatas["containerList"][]=$tmpContainer;}
		}
		////	Arborescence des dossiers de contacts (du dossier "root")
		foreach(Ctrl::getObj("contactFolder",1)->folderTree() as $tmpContainer){
			$tmpContainer->personList=Db::getObjTab("contact", "SELECT * FROM ap_contact WHERE LENGTH(mail)>0 AND ".MdlContact::sqlDisplay($tmpContainer).MdlContact::sqlSort());
			if(!empty($tmpContainer->personList))  {$vDatas["containerList"][]=$tmpContainer;}
		}
		////	Charge un ancien mail ou un nouveau mail  &&  Affiche la page
		$vDatas["curObj"]=Req::isParam("reloadMailTypeId")  ?  Ctrl::getCurObj(Req::param("reloadMailTypeId"))  :  Ctrl::getObj("mail");
		static::displayPage("VueIndex.php",$vDatas);
	}

	/********************************************************************************************************
	 * VUE : HITORIQUE DES MAILS QUE L'USER COURANT A ENVOYE
	 ********************************************************************************************************/
	public static function actionMailHistory()
	{
		$vDatas["mailList"]=Db::getObjTab("mail", "SELECT * FROM ap_mail WHERE _idUser=".Ctrl::$curUser->_id." ORDER BY dateCrea desc");
		static::displayPage("VueMailHistory.php",$vDatas);
	}
}