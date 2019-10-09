<?php
/**
* This file is part of the Agora-Project Software package.
*
* @copyright (c) Agora-Project Limited <https://www.agora-project.net>
* @license GNU General Public License, version 2 (GPL-2.0)
*/

/*
 * Classe des Objects "FOLDER"
 */
 class MdlObjectFolder extends MdlObject
{
	private $_contentDescription=null;
	const isFolder=true;
	const isSelectable=true;
	const hasShortcut=true;
	const hasUsersComment=true;
	const hasUsersLike=true;
	public static $displayModeOptions=array("block","line");
	public static $requiredFields=array("name");
	public static $searchFields=array("name","description");
	public static $sortFields=array("name@@asc","name@@desc","description@@asc","description@@desc","dateCrea@@desc","dateCrea@@asc","dateModif@@desc","dateModif@@asc","_idUser@@asc","_idUser@@desc");
	//Valeurs mises en cache
	private $_visibleFolderTree=null;

	/*
	 * SURCHARGE : Constructeur
	 */
	function __construct($objIdOrValues=null)
	{
		parent::__construct($objIdOrValues);
		if($this->_id==1)    {$this->name=Txt::trad("rootFolder");}//dossier racine
	}

	/*
	 * SURCHARGE : dossier racine accessible en écriture par défaut, mais ajout de contenu géré via "editContentRight()" (cf. option "adminRootAddContent" des modules)
	 */
	public function accessRight()
	{
		return ($this->isRootFolder()) ? 2 : parent::accessRight();
	}

	/*
	 * SURCHARGE : Droit d'ajouter du contenu dans le dossier racine OU dans un dossier lambda
	 */
	public function editContentRight()
	{
		if($this->isRootFolder())	{return (Ctrl::$curUser->isAdminSpace() || (Ctrl::$curUser->isUser() && Ctrl::$curSpace->moduleOptionEnabled(static::moduleName,"adminRootAddContent")==false));}//"true" si "isAdminSpace()" ou aucune limite pour les users lambda
		else						{return parent::editContentRight();}
	}

	/*
	 * SURCHARGE : Affectations de l'objet
	 */
	public function getAffectations()
	{
		//Nouveau dossier, mais pas à la racine : récupère les droits d'accès du dossier conteneur pour faire une "pré-affectation"
		if($this->isNew() && $this->containerObj()->isRootFolder()==false)	{return $this->containerObj()->getAffectations();}
		else																{return parent::getAffectations();}
	}

	/*
	 * SURCHARGE : Droit d'édition
	 */
	public function editRight()
	{
		return (parent::editRight() && $this->isRootFolder()==false);
	}

	/*
	 * SURCHARGE : Droit de suppression
	 */
	public function deleteRight()
	{
		return (parent::deleteRight() && $this->isRootFolder()==false);
	}

	/*
	 * SURCHARGE : Suppression d'un objet Dossier.. et son arborescence
	 */
	public function delete()
	{
		if($this->deleteRight())
		{
			////	Supprime l'arborescence du dossier ("0"=>Récupère tout!)
			foreach($this->folderTree("all") as $tmpFolder)
			{
				//Supprime les fichiers du dossier courant
				$MdlObjectContent=static::MdlObjectContent;
				$filesList=Db::getObjTab($MdlObjectContent::objectType, "SELECT * FROM ".$MdlObjectContent::dbTable." WHERE _idContainer=".$tmpFolder->_id);
				foreach($filesList as $tmpFile)  {$tmpFile->delete();}
				//Supprime le dossier.. sauf le dossier courant : supprimé à la fin
				if($tmpFolder->_id!=$this->_id)  {$tmpFolder->delete();}
			}
			////	Supprime le dossier courant
			if(static::objectType=="fileFolder"){
				$tmpFolderPath=$this->folderPath("real");
				if($tmpFolderPath!=PATH_MOD_FILE && is_dir($tmpFolderPath))  {File::rm($tmpFolderPath);}
			}
			parent::delete();
		}
	}

	/*
	 * Icone du dossier
	 */
	public function iconPath()
	{
		return (!empty($this->icon))  ?  PATH_ICON_FOLDER.$this->icon  :  PATH_ICON_FOLDER."folder.png";
	}

	/*
	 * Contenu d'un dossier  :  nombre d'elements + taille du dossier (module fichiers)
	 */
	public function folderContentDescription()
	{
		if($this->_contentDescription===null)
		{
			//Init
			$MdlObjectContent=static::MdlObjectContent;
			$this->_contentDescription="";
			$nbSubFolders=Db::getVal("SELECT count(*) FROM ".static::dbTable." WHERE ".self::sqlDisplayedObjects($this));
			$nbElems=Db::getVal("SELECT count(*) FROM ".$MdlObjectContent::dbTable." WHERE ".$MdlObjectContent::sqlDisplayedObjects($this));
			////	Nombre de sous-dossiers
			if(!empty($nbSubFolders))  {$this->_contentDescription.=$nbSubFolders." ".($nbSubFolders>1?Txt::trad("folders"):Txt::trad("folder"));}
			////	Nombre d'elements dans le dossier (s'il y en a)  &&  taille des fichiers (si "fileFolder")
			if(!empty($nbElems)){
				if(!empty($this->_contentDescription))	{$this->_contentDescription.=" - ";}
				$this->_contentDescription.=$nbElems." ".Txt::trad($nbElems>1?"elements":"element");
				if(static::objectType=="fileFolder")	{$this->_contentDescription.=" - ".File::displaySize(Db::getVal("SELECT SUM(octetSize) FROM ".$MdlObjectContent::dbTable." WHERE _idContainer=".$this->_id));}
			}
			////	Aucun element..
			if(empty($this->_contentDescription))	{$this->_contentDescription="0 ".Txt::trad("element");}
		}
		return $this->_contentDescription;
	}

	/*
	 * Détails complémentaires sur le dossier => à surcharger!
	 */
	public function folderOtherDetails(){}

	/*
	 * Chemin d'un dossier (fonction récursive)
	 * $typeReturn= object | id | text | zip | real
	 */
	public function folderPath($typeReturn, $objCurFolder=null, $foldersList=array())
	{
		////	Dossier de départ & Ajoute le dossier courant
		if($objCurFolder==null)  {$objCurFolder=$this;}
		$foldersList[]=$objCurFolder;
		////	Recupère le dossier conteneur si on est pas encore à la racine (vérif que le parent existe!)
		if($objCurFolder->isRootFolder()==false && !empty($objCurFolder->containerObj()->_id))	{return $this->folderPath($typeReturn, $objCurFolder->containerObj(), $foldersList);}
		////	renvoie le résultat final si on est à la racine
		else
		{
			$foldersList=array_reverse($foldersList);//on commence par la racine..
			if($typeReturn=="object") 	{return $foldersList;}
			if($typeReturn=="id"){
				$foldersIds=array();
				foreach($foldersList as $tmpFolder)	{$foldersIds[]=$tmpFolder->_id;}
				return $foldersIds;
			}else{
				$textReturn=($typeReturn=="real") ? PATH_MOD_FILE : "";
				$imgSeparate="&nbsp;<img src='app/img/arrowRight.png'>&nbsp;";
				foreach($foldersList as $cpt=>$objFolder){
					if($typeReturn=="text")												{$textReturn.=($cpt>0?$imgSeparate:"").$objFolder->name; }	//"Dossier racine > sous-dossier testé"
					elseif($typeReturn=="zip")											{$textReturn.=Txt::clean($objFolder->name,"download")."/";}	//"Dossier_racine/sous-dossier_teste/"
					elseif($typeReturn=="real" && $objFolder->isRootFolder()==false)	{$textReturn.=$objFolder->_id."/";}							//"DATAS/gestionnaire_fichiers/2/5/" (sans dossier racine!)
				}
				return $textReturn;
			}
		}
	}

	/*
	 * Arborescence d'objets dossiers (fonction récursive)
	 */
	public function folderTree($accessRightMini=1, $objCurFolder=null, $treeLevel=0)
	{
		//Arborescence "visible" déjà en cache : renvoie le résultat
		if($treeLevel==0 && $accessRightMini==1 && !empty($this->_visibleFolderTree))  {return $this->_visibleFolderTree;}
		//Init la liste des sous-dossiers && le dossier de départ de toute l'arborescence
		$folderList=[];
		if($objCurFolder==null)  {$objCurFolder=$this;}
		//Ajoute le dossier courant ?
		if($accessRightMini=="all" || $objCurFolder->accessRight()>=$accessRightMini)
		{
			//Ajoute le dossier courant à la liste, avec son niveau
			$objCurFolder->treeLevel=$treeLevel;
			$folderList[]=$objCurFolder;
			//Récupère récursivement les sous-dossiers du dossier courant (tjs triés par nom)  =>  tous les sous-dossiers ("all")  OU  les sous-dossiers en fonction de leur droit d'accès ("sqlDisplayedObjects()") 
			$sqlDisplayedObjects=($accessRightMini=="all")  ?  "_idContainer=".$objCurFolder->_id  :  static::sqlDisplayedObjects($objCurFolder);
			foreach(Db::getObjTab(static::objectType, "SELECT * FROM ".static::dbTable." WHERE ".$sqlDisplayedObjects." ORDER BY name ASC")  as $subFolder){
				$subFolders=$this->folderTree($accessRightMini, $subFolder, $treeLevel+1);
				$folderList=array_merge($folderList,$subFolders);
			}
		}
		//Arborescence "visible" pas encore en cache : ajoute l'arborescence (cf. pour controler si on affiche l'option "Déplacer dans un autre dossier" dans les menus contextuels)
		if($treeLevel==0 && $accessRightMini==1 && $this->_visibleFolderTree===null)  {$this->_visibleFolderTree=$folderList;}
		//Renvoie le résultat
		return $folderList;
	}

	/*
	 * Controle si un dossier se trouve dans l'arborecence du dossier courant
	 */
	public function isInFolderTree($folderId)
	{
		foreach($this->folderTree("all") as $tmpFolder){
			if($folderId==$tmpFolder->_id)  {return true;}
		}
	}

	/*
	 * VUE : Liste de dossiers à afficher
	 */
	public function folders($centerContent=false)
	{
		$vDatas["foldersList"]=Db::getObjTab(static::objectType, "SELECT * FROM ".static::dbTable." WHERE ".static::sqlDisplayedObjects($this)." ".static::sqlSort());
		if(!empty($vDatas["foldersList"])){
			$vDatas["objContentCenterClass"]=($centerContent==true) ? "objContentCenter" : null;//Affichage centré du contenu? (cf. ModFile)
			return Ctrl::getVue(Req::commonPath."VueObjFolders.php",$vDatas);
		}
	}
}