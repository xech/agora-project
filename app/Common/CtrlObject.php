<?php
/**
* This file is part of the Agora-Project Software package.
*
* @copyright (c) Agora-Project Limited <https://www.agora-project.net>
* @license GNU General Public License, version 2 (GPL-2.0)
*/


/*
 * Controleur concernant les objets (menus, vues, etc)
 */
class CtrlObject extends Ctrl
{
	/*
	 * ACTION : Affiche les logs d'un objet
	 */
	public static function actionLogs()
	{
		if(Req::isParam("targetObjId")){
			$curObj=self::getTargetObj();
			if($curObj->editRight()){
				$vDatas["logsList"]=Db::getTab("SELECT *, UNIX_TIMESTAMP(date) as dateUnix FROM ap_log WHERE objectType='".$curObj::objectType."' AND _idObject=".$curObj->_id." ORDER BY date");
				static::displayPage(Req::commonPath."VueObjLogs.php",$vDatas);
			}
		}
	}

	/*
	 * ACTION : Telecharge un "AttachedFile"
	 */
	public static function actionGetFile()
	{
		$curFile=MdlObject::getAttachedFile(Req::getParam("_id"));
		if(is_file($curFile["path"])  &&  ($curFile["containerObj"]->readRight() || md5($curFile["name"])==Req::getParam("nameMd5")))
			{File::download($curFile["name"],$curFile["path"]);}
	}

	/*
	 * ACTION : Affiche un fichier joint dans le browser
	 */
	 public static function actionDisplayAttachedFile()
	{
		$curFile=MdlObject::getAttachedFile(Req::getParam("_id"));
		if(is_file($curFile["path"]) && $curFile["containerObj"]->readRight())   {File::display($curFile["path"]);}
	 }

	/*
	 * AJAX : Supprime un fichier joint
	 */
	public static function actionDeleteAttachedFile()
	{
		$curFile=MdlObject::getAttachedFile(Req::getParam("_id"));
		if(is_file($curFile["path"]) && $curFile["containerObj"]->editRight()){
			$deleteResult=$curFile["containerObj"]->deleteAttachedFile($curFile);
			if($deleteResult==true)  {echo "ok";}
		}
	}

	/*
	 * ACTION : Supprime le/les objets sélectionnés
	 */
	public static function actionDelete()
	{
		//// Init
		$redirUrl=$newDatasFolderSize=null;
		$notDeletedObjects=[];
		//// Supprime le/les objets
		foreach(self::getTargetObjects() as $tmpObj)
		{
			//Url du conteneur / du module
			if(empty($redirUrl))  {$redirUrl=$tmpObj->getUrl("container");}
			//Vérifie si on doit mettre à jour le "datasFolderSize()"
			if($tmpObj::moduleName=="file")  {$newDatasFolderSize=true;}
			//Delete si on a les droits, sinon on prepare un message d'erreur
			if($tmpObj->deleteRight())	{$tmpObj->delete();}
			else						{$notDeletedObjects[]=$tmpObj->getLabel();}
		}
		//// Met à jour le "datasFolderSize()" en session 
		if($newDatasFolderSize==true)  {File::datasFolderSize(true);}
		//// Objets non supprimés : affiche les labels des objets concernés (10 objets maxi)
		if(!empty($notDeletedObjects)){
			if(count($notDeletedObjects)>10)  {$notDeletedObjects=array_slice($notDeletedObjects,0,10);  $notDeletedObjects[]="..."; }
			Ctrl::addNotif(Txt::trad("notDeletedElements")." :<br><br>".implode(", ",$notDeletedObjects));
		}
		//// Redirection sur la page du conteneur
		self::redir($redirUrl);
	}

	/*
	 * ACTION : Menu pour déplacer des éléments dans un autre dossier
	 */
	public static function actionFolderMove()
	{
		//Validation du formulaire
		if(Req::isParam("formValidate") && Req::isParam("newFolderId")){
			foreach(self::getTargetObjects() as $tmpObj)  {$tmpObj->folderMove(Req::getParam("newFolderId"));}
			static::lightboxClose();
		}
		//Affiche le menu de déplacement de dossier
		self::folderTreeMenu("move");
	}

	/*
	 * VUE : Menu d'arborescence de dossiers ($context: "nav" / "move")
	 */
	public static function folderTreeMenu($context="nav")
	{
		//Arborescence du dossier racine
		$vDatas["rootFolderTree"]=Ctrl::getObj(get_class(Ctrl::$curContainer),1)->folderTree();
		//Affiche l'arborescence s'il y a au moins un dossier (en + du dossier racine)
		if(count($vDatas["rootFolderTree"])>1){
			$vDatas["context"]=$context;
			$vueFolderTree=Req::commonPath."VueObjFolderTree.php";
			if($context=="nav")	{return Ctrl::getVue($vueFolderTree,$vDatas);}//"nav" -> redirige vers un dossier
			else				{static::displayPage($vueFolderTree,$vDatas);}//"move" -> selectionne un dossier pour y déplacer un element
		}
	}

	/*
	 * VUE : Menu du chemin du dossier courant
	 */
	public static function folderPathMenu($addElemLabel=null, $addElemUrl=null)
	{
		//Affiche le chemin d'un dossier  ET/OU  L'option d'ajout d'élement
		if(Ctrl::$curContainer->isRootFolder()==false || !empty($addElemLabel)){
			$vDatas["addElemLabel"]=$addElemLabel;
			$vDatas["addElemUrl"]=$addElemUrl;
			return Ctrl::getVue(Req::commonPath."VueObjFolderPath.php", $vDatas);
		}
	}

	/*
	 * ACTION VUE : Edition d'un dossier
	 */
	public static function actionFolderEdit()
	{
		////	Charge le dossier et Controle d'accès: dossier existant / nouveau dossier
		$curObj=Ctrl::getTargetObj();
		$curObj->controlEdit();
		////	Valide le formulaire
		if(Req::isParam("formValidate"))
		{
			//Enregistre et recharge l'objet
			$curObj=$curObj->createUpdate("name=".Db::formatParam("name").", description=".Db::formatParam("description").", icon=".Db::formatParam("icon"));
			//Etend les droits aux sous dossiers?
			if(Req::isParam("extendToSubfolders")){
				foreach($curObj->folderTree("all") as $tmpObj)	{$tmpObj->setAffectations();}
			}
			//Ferme la page
			static::lightboxClose();
		}
		////	Affiche la vue
		else
		{
			$vDatas["curObj"]=$curObj;
			static::displayPage(Req::commonPath."VueObjFolderEdit.php",$vDatas);
		}
	}

	/*
	 * AJAX : Controle si un autre objet fichier/dossier porte le même nom
	 */
	public static function actionControlDuplicateName()
	{
		//Précise "targetObjIdContainer" pour les nouveaux dossiers/fichiers
		if(Req::isParam(["targetObjId","targetObjIdContainer","controledName"])){
			//init
			$curObj=Ctrl::getTargetObj();
			$objContainer=Ctrl::getTargetObj(Req::getParam("targetObjIdContainer"));
			//Recherche les doublons dans le conteneur courant et affectés à l'espace courant
			$nbDuplicate=Db::getVal("SELECT count(*) FROM ".$curObj::dbTable." WHERE name=".Db::formatParam("controledName")." AND _id!=".$curObj->_id." AND _idContainer=".$objContainer->_id." AND _id IN  (select _idObject as _id from ap_objectTarget where objectType='".$curObj::objectType."' and _idSpace=".Ctrl::$curSpace->_id.")");
			if($nbDuplicate>0)  {echo "duplicate";}
		}
	}

	/*
	 * AJAX : Controle d'accès avant suppression d'un dossier
	 */
	public static function actionFolderDeleteControl()
	{
		//// Init les notifications
		$result=[];
		//// Controle si tous les sous-dossiers sont bien accessibles en écriture ("Certains sous-dossiers ne vous sont pas accessibles... confirmer?")
		$curFolder=Ctrl::getTargetObj();
		$folderTreeAll=$curFolder->folderTree("all");//Liste tous les dossiers (pas forcément en lecture)
		$folderTreeWrite=$curFolder->folderTree(2);//Liste les dossiers accessibles en écriture (pas forcément en accès total)
		if(count($folderTreeAll)!=count($folderTreeWrite))  {$result["confirmDeleteFolderAccess"]=Txt::trad("confirmDeleteFolderAccess");}
		//// Arborescence de plus de 100 dossiers : notif "merci de patienter quelques instants avant la fin du processus"
		if(count($folderTreeAll)>100 )  {$result["notifyBigFolderDelete"]=str_replace("--NB_FOLDERS--",count($folderTreeAll),Txt::trad("notifyBigFolderDelete"));}
		//// Retourne le résultat au format JSON
		echo json_encode($result);
	}

	/*
	 * AJAX : Edition d'un dossier => Control le droit d'accès d'un user/espace au dossier parent
	 */
	public static function actionAccessRightParentFolder()
	{
		//Init
		$parentFolder=Ctrl::getTargetObj();
		$objectRight=explode("_",Req::getParam("objectRight"));//exple: "1_U2_2" ou "1_spaceUsers_1.5"
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
				$ajaxResult["message"]=str_replace(["--TARGET_LABEL--","--FOLDER_NAME--"], [$targetLabel,Txt::reduce($parentFolder->name,30)], Txt::trad("EDIT_parentFolderAccessError"));
				echo json_encode($ajaxResult);
			}
		}
	}

	/*
	 * AJAX : Valide une évaluation Like
	 */
	public static function actionUsersLikeValidate()
	{
		//Vérifs de base
		if(Ctrl::$curUser->isUser() && Req::isParam("targetObjId"))
		{
			//Init
			$curObj=self::getTargetObj();
			//Applique la nouvelle valeur / le changement de valeur
			$newValue=(Req::getParam("likeValue")=="like")  ?  1  :  0;
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

	/*
	 * ACTION : Affiche les commentaires d'un objet
	 */
	public static function actionComments()
	{
		////	Charge l'element
		$curObj=Ctrl::getTargetObj();
		$curObj->controlRead();
		////	Ajoute / Modif / Supprime un commentaire
		if(Req::isParam(["formValidate","comment"]) && Req::getParam("actionComment")=="add")
			{Db::query("INSERT INTO ap_objectComment SET objectType='".$curObj::objectType."', _idObject=".$curObj->_id.", _idUser=".self::$curUser->_id.", dateCrea=".Db::dateNow().", comment=".Db::formatParam("comment"));}
		elseif(Req::isParam("idComment") && MdlObjectAttributes::userCommentEditRight(Req::getParam("idComment"))){
			$sqlSelectComment="_id=".Db::formatParam("idComment")." AND objectType='".$curObj::objectType."' AND _idObject=".$curObj->_id;
			if(Req::getParam("actionComment")=="delete")	{Db::query("DELETE FROM ap_objectComment WHERE ".$sqlSelectComment);}
			elseif(Req::getParam("actionComment")=="modif")	{Db::query("UPDATE ap_objectComment SET comment=".Db::formatParam("comment")." WHERE ".$sqlSelectComment);}
		}
		////	Affiche la vue
		$vDatas["curObj"]=$curObj;
		$vDatas["updateCircleNb"]=Req::isParam("actionComment");
		$vDatas["commentList"]=Db::getTab("SELECT * FROM ap_objectComment WHERE objectType='".$curObj::objectType."' AND _idObject=".$curObj->_id." ORDER BY dateCrea DESC");
		$vDatas["commentsTitle"]=count($vDatas["commentList"])." ".Txt::trad(count($vDatas["commentList"])>1?"AGORA_usersComments":"AGORA_usersComment");
		static::displayPage(Req::commonPath."VueObjComments.php",$vDatas);
	}
}