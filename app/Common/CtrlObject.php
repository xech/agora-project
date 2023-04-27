<?php
/**
* This file is part of the Agora-Project Software package.
*
* @copyright (c) Agora-Project Limited <https://www.agora-project.net>
* @license GNU General Public License, version 2 (GPL-2.0)
*/


/*
 * Controleur pour les "action" ou "vue" concernant les objets (menus, vues, etc)
 */
class CtrlObject extends Ctrl
{
	//Vue des Folders en cache
	public static $vueFolders=null;

	/*******************************************************************************************
	 * ACTION : AFFICHE LES LOGS D'UN OBJET
	 *******************************************************************************************/
	public static function actionLogs()
	{
		if(Req::isParam("typeId")){
			$curObj=self::getObjTarget();
			if($curObj->editRight()){
				$vDatas["logsList"]=Db::getTab("SELECT *, UNIX_TIMESTAMP(date) as dateUnix FROM ap_log WHERE objectType='".$curObj::objectType."' AND _idObject=".$curObj->_id." ORDER BY date");
				static::displayPage(Req::commonPath."VueObjLogs.php",$vDatas);
			}
		}
	}

	/*******************************************************************************************
	 * ACTION : SUPPRIME LE OU LES OBJETS SÉLECTIONNÉS : CF. $object->getUrl("delete")
	 *******************************************************************************************/
	public static function actionDelete()
	{
		// Init
		$redirUrl=$updateDatasFolderSize=null;
		$notDeletedObjects=[];
		////	Supprime le/les objets
		foreach(self::getObjectsTypeId() as $tmpObj)
		{
			//Enregistre l'Url de redirection après le delete
			if(empty($redirUrl)){
				if($tmpObj::isFolder==true)						{$redirUrl=$tmpObj->containerObj()->getUrl();}	//Suppr un dossier : affiche le dossier parent 
				elseif($tmpObj::isContainerContent())			{$redirUrl=$tmpObj->getUrl();}					//Suppr un contenu (content) : affiche le "container"
				elseif($tmpObj::objectType=="forumSubject")		{$redirUrl=$tmpObj->getUrl("theme");}			//Suppr de sujet du forum : "getUrl()" surchargé
				else											{$redirUrl="?ctrl=".$tmpObj::moduleName;}		//Sinon redir en page principale du module
			}
			//Enregistre si on doit mettre à jour le "datasFolderSize()"
			if($tmpObj::moduleName=="file")  {$updateDatasFolderSize=true;}
			//Delete si on a les droits ..ou prepare un message d'erreur
			if($tmpObj->deleteRight())	{$tmpObj->delete();}																		
			else						{$notDeletedObjects[]=$tmpObj->getLabel();}	
		}
		////	Update le "datasFolderSize()" en session
		if($updateDatasFolderSize==true)  {File::datasFolderSize(true);}
		////	Objets non supprimés : affiche les labels des objets concernés (10 objets maxi)
		if(!empty($notDeletedObjects)){
			if(count($notDeletedObjects)>10)  {$notDeletedObjects=array_slice($notDeletedObjects,0,10);  $notDeletedObjects[]="..."; }
			Ctrl::notify(Txt::trad("notDeletedElements")." :<br><br>".implode(", ",$notDeletedObjects));
		}
		////	Redirection
		self::redir($redirUrl);
	}

	/*******************************************************************************************
	 * ACTION : MENU POUR DÉPLACER DES ÉLÉMENTS DANS UN AUTRE DOSSIER
	 *******************************************************************************************/
	public static function actionFolderMove()
	{
		//Validation du formulaire
		if(Req::isParam("formValidate") && Req::isParam("newFolderId")){
			foreach(self::getObjectsTypeId() as $tmpObj)  {$tmpObj->folderMove(Req::param("newFolderId"));}
			static::lightboxClose();
		}
		//Affiche le menu de déplacement de dossier
		self::folderTreeMenu("move");
	}

	/*******************************************************************************************
	 * VUE : MENU D'ARBORESCENCE DE DOSSIERS ($context: "nav" / "move")
	 *******************************************************************************************/
	public static function folderTreeMenu($context="nav")
	{
		//Affiche l'arborescence (si ya pas que le dossier racine)
		if(count(Ctrl::$curRootFolder->folderTree())>1){
			$vDatas["context"]=$context;
			$vueFolderTree=Req::commonPath."VueObjFolderTree.php";
			if($context=="nav")	{return Ctrl::getVue($vueFolderTree,$vDatas);}//"nav"	: renvoie le menu de navigation de l'arborescence de dossiers
			else				{static::displayPage($vueFolderTree,$vDatas);}//"move"	: affiche uniquement le menu de selection d'un dossier pour y déplacer un element
		}
	}

	/*******************************************************************************************
	 * VUE : MENU DU CHEMIN DU DOSSIER COURANT
	 *******************************************************************************************/
	public static function folderPathMenu($addElemLabel=null, $addElemUrl=null)
	{
		//Affiche le chemin d'un dossier  ET/OU  L'option d'ajout d'élement
		if(Ctrl::$curContainer->isRootFolder()==false || !empty($addElemLabel)){
			$vDatas["addElemLabel"]=$addElemLabel;
			$vDatas["addElemUrl"]=$addElemUrl;
			return Ctrl::getVue(Req::commonPath."VueObjFolderPath.php", $vDatas);
		}
	}

	/*******************************************************************************************
	 * VUE : LISTE DES DOSSIERS DU DOSSIER COURANT
	 *******************************************************************************************/
	public static function vueFolders()
	{
		if(self::$vueFolders===null)
		{
			//Récupère le dossier courant et les dossiers qu'il contient
			$curFolder=Ctrl::$curContainer;
			$vDatas["foldersList"]=Db::getObjTab($curFolder::objectType, "SELECT * FROM ".$curFolder::dbTable." WHERE ".$curFolder::sqlDisplay($curFolder)." ".$curFolder::sqlSort());
			//Aucun dossier / Liste des dossiers
			if(empty($vDatas["foldersList"]))  {self::$vueFolders="";}
			else{
				if($curFolder::moduleName=="contact")	{$vDatas["objContainerClass"]="objPerson";}			//Affichage spécifique aux contacts
				elseif($curFolder::moduleName=="file")	{$vDatas["objContainerClass"]="objContentCenter";}	//Affichage centré (icone + contenu)
				else									{$vDatas["objContainerClass"]=null;}				//Affichage normal
				self::$vueFolders=Ctrl::getVue(Req::commonPath."VueObjFolders.php",$vDatas);
			}
		}
		return self::$vueFolders;
	}

	/*******************************************************************************************
	 * ACTION VUE : EDITION D'UN DOSSIER
	 *******************************************************************************************/
	public static function actionFolderEdit()
	{
		////	Charge le dossier et Controle d'accès: dossier existant / nouveau dossier
		$curObj=Ctrl::getObjTarget();
		$curObj->editControl();
		////	Valide le formulaire
		if(Req::isParam("formValidate"))
		{
			//Enregistre et recharge l'objet
			$curObj=$curObj->createUpdate("name=".Db::param("name").", description=".Db::param("description").", icon=".Db::param("icon"));
			//Etend les droits aux sous dossiers?
			if(Req::isParam("extendToSubfolders")){
				foreach($curObj->folderTree("all") as $tmpObj)	{$tmpObj->setAffectations();}
			}
			//Notifie par mail & Ferme la page
			$curObj->sendMailNotif();
			static::lightboxClose();
		}
		////	Affiche la vue
		else
		{
			$vDatas["curObj"]=$curObj;
			static::displayPage(Req::commonPath."VueObjFolderEdit.php",$vDatas);
		}
	}

	/*******************************************************************************************
	 * AJAX : CONTROLE SI UN AUTRE OBJET FICHIER/DOSSIER PORTE LE MÊME NOM
	 *******************************************************************************************/
	public static function actionControlDuplicateName()
	{
		//Précise "typeIdContainer" pour les nouveaux dossiers/fichiers
		if(Req::isParam(["typeId","typeIdContainer","controledName"])){
			//init
			$curObj=Ctrl::getObjTarget();
			$objContainer=Ctrl::getObjTarget(Req::param("typeIdContainer"));
			//Recherche les doublons dans le conteneur courant et affectés à l'espace courant
			$nbDuplicate=Db::getVal("SELECT count(*) FROM ".$curObj::dbTable." WHERE name=".Db::param("controledName")." AND _id!=".$curObj->_id." AND _idContainer=".$objContainer->_id." AND _id IN  (select _idObject as _id from ap_objectTarget where objectType='".$curObj::objectType."' and _idSpace=".Ctrl::$curSpace->_id.")");
			if($nbDuplicate>0)  {echo "duplicate";}
		}
	}

	/*******************************************************************************************
	 * AJAX : CONTROLE D'ACCÈS AVANT SUPPRESSION D'UN DOSSIER
	 *******************************************************************************************/
	public static function actionFolderDeleteControl()
	{
		//// Init les notifications
		$result=[];
		//// Controle si tous les sous-dossiers sont bien accessibles en écriture ("Certains sous-dossiers ne vous sont pas accessibles... confirmer?")
		$curFolder=Ctrl::getObjTarget();
		$folderTreeAll=$curFolder->folderTree("all");//Liste tous les dossiers (pas forcément en lecture)
		$folderTreeWrite=$curFolder->folderTree(2);//Liste les dossiers accessibles en écriture (pas forcément en accès total)
		if(count($folderTreeAll)!=count($folderTreeWrite))  {$result["confirmDeleteFolderAccess"]=Txt::trad("confirmDeleteFolderAccess");}
		//// Arborescence de plus de 100 dossiers : notif "merci de patienter quelques instants avant la fin du processus"
		if(count($folderTreeAll)>100 )  {$result["notifyBigFolderDelete"]=str_replace("--NB_FOLDERS--",count($folderTreeAll),Txt::trad("notifyBigFolderDelete"));}
		//// Retourne le résultat au format JSON
		echo json_encode($result);
	}

	/*******************************************************************************************
	 * AJAX : EDITION D'UN DOSSIER => CONTROL LE DROIT D'ACCÈS D'UN USER/ESPACE AU DOSSIER PARENT
	 *******************************************************************************************/
	public static function actionAccessRightParentFolder()
	{
		//Init
		$parentFolder=Ctrl::getObjTarget();
		$objectRight=explode("_",Req::param("objectRight"));//exple: "1_U2_2" ou "1_spaceUsers_1.5"
		//Controle?
		if($parentFolder->isRootFolder()==false && count($objectRight)==3)
		{
			$sqlObjSelect="SELECT count(*) FROM ap_objectTarget WHERE objectType=".Db::format($parentFolder::objectType)." AND _idObject=".(int)$parentFolder->_id." AND _idSpace=".(int)$objectRight[0];
			$sqlTargetSelect=($objectRight[1]=="spaceUsers")  ?  " AND target='spaceUsers'"  :  " AND (target='spaceUsers' OR target=".Db::format($objectRight[1]).")";
			if(Db::getVal($sqlObjSelect.$sqlTargetSelect)==0)
			{
				$ajaxResult["error"]=true;
				if(preg_match("/^G/i",$objectRight[1]))		{$targetLabel=self::getObj("userGroup",str_ireplace("G","",$objectRight[1]))->title;}
				elseif(preg_match("/^U/i",$objectRight[1]))	{$targetLabel=self::getObj("user",str_ireplace("U","",$objectRight[1]))->getLabel();}
				else										{$targetLabel=Txt::trad("EDIT_allUsers");}
				$ajaxResult["message"]=str_replace(["--TARGET_LABEL--","--FOLDER_NAME--"], [$targetLabel,Txt::reduce($parentFolder->name,40)], Txt::trad("EDIT_parentFolderAccessError"));
				echo json_encode($ajaxResult);
			}
		}
	}

	/*******************************************************************************************
	 * VUE : AFFICHE L'EDITEUR TINYMCE (doit déjà y avoir un champ "textarea")
	 *******************************************************************************************/
	public static function htmlEditor($fieldName)
	{
		//Nom du champ "textarea"
		$vDatas["fieldName"]=$fieldName;
		//Sélectionne au besoin le "draftTypeId" pour n'afficher que le brouillon/draft de l'objet précédement édité (on n'utilise pas "editTypeId" car il est effacé dès qu'on sort de l'édition de l'objet...)
		$sqlTypeId=Req::isParam("typeId")  ?  "draftTypeId=".Db::param("typeId")  :  "draftTypeId IS NULL";
		$vDatas["editorDraft"]=(string)Db::getVal("SELECT editorDraft FROM ap_userLivecouter WHERE _idUser=".Ctrl::$curUser->_id." AND ".$sqlTypeId);
		//Affiche la vue de l'éditeur TinyMce
		return self::getVue(Req::commonPath."VueObjHtmlEditor.php",$vDatas);
	}

	/*******************************************************************************************************************************************
	 * VUE : AFFICHE LES FICHIERS JOINTS DE L'OBJET (cf. "VueHtmlEditor.php")
	 *******************************************************************************************************************************************/
	public static function attachedFile($curObj=null)
	{
		$vDatas["curObj"]=$curObj;
		return self::getVue(Req::commonPath."VueObjAttachedFile.php",$vDatas);
	}
	
	/*******************************************************************************************
	 * VUE : AFFICHE LES OPTIONS DE BASE POUR L'ENVOI D'EMAIL (cf. "Tool::sendMail()") 
	 *******************************************************************************************/
	public static function sendMailBasicOptions()
	{
		return Ctrl::getVue(Req::commonPath."VueSendMailOptions.php");
	}

	/*******************************************************************************************
	 * ACTION : TELECHARGE UN FICHIER JOINT
	 *******************************************************************************************/
	public static function actionAttachedFileDownload()
	{
		$curFile=MdlObject::attachedFileInfos(Req::param("_id"));
		if(is_file($curFile["path"])  &&  ($curFile["containerObj"]->readRight() || md5($curFile["name"])==Req::param("nameMd5")))   {File::download($curFile["name"],$curFile["path"]);}
	}

	/*******************************************************************************************
	 * ACTION : AFFICHE UN FICHIER JOINT IMAGE/PDF/ETC
	 *******************************************************************************************/
	 public static function actionAttachedFileDisplay()
	{
		$curFile=MdlObject::attachedFileInfos(Req::param("_id"));
		if(is_file($curFile["path"])  &&  ($curFile["containerObj"]->readRight() || md5($curFile["name"])==Req::param("nameMd5")))   {File::display($curFile["path"]);}
	}

	/*******************************************************************************************
	 * FICHIER JOINT : URL D'AFFICHAGE DU FICHIER VIA "actionAttachedFileDisplay()"
	 *******************************************************************************************/
	public static function attachedFileDisplayUrl($fileId, $fileName)
	{
		//Ajoute "&amp;" pour Tinymce et ajoute l'extension en toute fin (cf. fancybox des images et controle du type de fichier)
		return "?ctrl=object&amp;action=AttachedFileDisplay&amp;_id=".$fileId."&amp;extension=.".File::extension($fileName);
	}

	/*******************************************************************************************
	 * AJAX : SUPPRIME UN FICHIER JOINT
	 *******************************************************************************************/
	public static function actionAttachedFileDelete()
	{
		$curFile=MdlObject::attachedFileInfos(Req::param("_id"));
		if(is_file($curFile["path"]) && $curFile["containerObj"]->editRight()){
			$deleteResult=$curFile["containerObj"]->attachedFileDelete($curFile);
			if($deleteResult==true)  {echo "true";}
		}
	}

	/*******************************************************************************************
	 * AJAX : VALIDE/INVALIDE UN LIKE
	 *******************************************************************************************/
	public static function actionUsersLikeValidate()
	{
		//Vérifs de base
		if(Ctrl::$curUser->isUser() && Req::isParam("typeId"))
		{
			//Init
			$curObj=self::getObjTarget();
			//Applique la nouvelle valeur / le changement de valeur
			$newValue=(Req::param("likeValue")=="like")  ?  1  :  0;
			$sqlValueUser="WHERE objectType='".$curObj::objectType."' AND _idObject=".$curObj->_id." AND _idUser=".Ctrl::$curUser->_id;
			$oldValue=Db::getVal("SELECT value FROM ap_objectLike ".$sqlValueUser);			//recup l'ancienne valeur
			if($oldValue!=null)  {Db::query("DELETE FROM ap_objectLike ".$sqlValueUser);}	//reinit la valeur?
			if($oldValue==null || $newValue!=$oldValue)  {Db::query("INSERT INTO ap_objectLike SET objectType='".$curObj::objectType."', _idObject=".$curObj->_id.", _idUser=".Ctrl::$curUser->_id.", value=".$newValue);}//Ajoute la nouvelle valeur si elle change
			//Nb et liste des personnes qui likes / dontlike
			$ajaxResult["nbLikes"]=count($curObj->getUsersLike("like"));
			$ajaxResult["nbDontlikes"]=count($curObj->getUsersLike("dontlike"));
			$ajaxResult["usersLikeList"]=$curObj->getUsersLikeTooltip("like");
			$ajaxResult["usersDontlikeList"]=$curObj->getUsersLikeTooltip("dontlike");
			echo json_encode($ajaxResult);
		}
	}

	/*******************************************************************************************
	 * ACTION : AFFICHE LES COMMENTAIRES D'UN OBJET
	 *******************************************************************************************/
	public static function actionComments()
	{
		////	Charge l'element
		$curObj=Ctrl::getObjTarget();
		$curObj->readControl();
		////	Ajoute / Modif / Supprime un commentaire
		if(Req::isParam(["formValidate","comment"]) && Req::param("actionComment")=="add")
			{Db::query("INSERT INTO ap_objectComment SET objectType='".$curObj::objectType."', _idObject=".$curObj->_id.", _idUser=".self::$curUser->_id.", dateCrea=".Db::dateNow().", `comment`=".Db::param("comment"));}
		elseif(Req::isParam("idComment") && MdlObject::userCommentEditRight(Req::param("idComment"))){
			$sqlSelectComment="_id=".Db::param("idComment")." AND objectType='".$curObj::objectType."' AND _idObject=".$curObj->_id;
			if(Req::param("actionComment")=="delete")	{Db::query("DELETE FROM ap_objectComment WHERE ".$sqlSelectComment);}
			elseif(Req::param("actionComment")=="modif")	{Db::query("UPDATE ap_objectComment SET `comment`=".Db::param("comment")." WHERE ".$sqlSelectComment);}
		}
		////	Affiche la vue
		$vDatas["curObj"]=$curObj;
		$vDatas["updateCircleNb"]=Req::isParam("actionComment");
		$vDatas["commentList"]=Db::getTab("SELECT * FROM ap_objectComment WHERE objectType='".$curObj::objectType."' AND _idObject=".$curObj->_id." ORDER BY dateCrea DESC");
		$vDatas["commentsTitle"]=count($vDatas["commentList"])." ".Txt::trad(count($vDatas["commentList"])>1?"AGORA_usersComments":"AGORA_usersComment");
		static::displayPage(Req::commonPath."VueObjComments.php",$vDatas);
	}
}