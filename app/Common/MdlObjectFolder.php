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
	//Valeurs en cache
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
	 * SURCHARGE : Suppression d'un dossier
	 */
	public function delete($initDelete=true)
	{
		if($this->deleteRight())
		{
			//Initialise la suppression
			if($initDelete==true)
			{
				////	Pour chaque dossier de l'arborescence : supprime son contenu (fichiers, contacts, etc.) PUIS Supprime récursivement le dossier
				$MdlObjectContent=static::MdlObjectContent;
				foreach($this->folderTree("all") as $tmpFolder){
					$contentList=Db::getObjTab($MdlObjectContent::objectType, "SELECT * FROM ".$MdlObjectContent::dbTable." WHERE _idContainer=".$tmpFolder->_id);
					foreach($contentList as $tmpContent){
						$MdlObjectContent::objectType=="file"  ?  $tmpContent->delete("deleteFolder")  :  $tmpContent->delete();//Supprime un fichier : utilise le shortcut "deleteFolder"
					}
					$tmpFolder->delete(false);//Suppression récursive du dossier (cf. $initDelete==true & "parent::delete();")
				}
				////	Dossier de fichiers : supprime enfin tout le dossier sur le disque
				if(static::objectType=="fileFolder"){
					$tmpFolderPath=$this->folderPath("real");
					if($tmpFolderPath!=PATH_MOD_FILE && is_dir($tmpFolderPath))  {File::rm($tmpFolderPath);}//Toujours controler via "is_dir()"!
				}
			}
			//Suppression récursive d'un dossier (cf. "$tmpFolder->delete(false);")
			else {parent::delete();}
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
		//Init la mise en cache
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
	 * $typeReturn= object | id | real | text | zip
	 */
	public function folderPath($typeReturn, $curFolder=null, $foldersList=array())
	{
		////	Dossier de départ & Ajoute le dossier courant
		if($curFolder==null)  {$curFolder=$this;}
		$foldersList[]=$curFolder;
		////	Si on est pas à la racine (vérif que le parent existe) : on recupère le dossier conteneur de manière récursive
		if($curFolder->isRootFolder()==false && is_object($curFolder->containerObj()))  {return $this->folderPath($typeReturn, $curFolder->containerObj(), $foldersList);}
		////	Si on est à la racine : on renvoie le résultat final
		else
		{
			//// on inverse le tableau pour commencer à la racine
			$foldersList=array_reverse($foldersList);
			//// Retourne une liste d'objets
			if($typeReturn=="object") 	{return $foldersList;}
			//Retourne une liste d'identifiants de dossiers
			if($typeReturn=="id"){
				$foldersIds=array();
				foreach($foldersList as $tmpFolder)  {$foldersIds[]=$tmpFolder->_id;}
				return $foldersIds;
			}
			//// Retourne le chemin réel du dossier "DATAS/modFile/22/555/"
			elseif($typeReturn=="real"){
				$return=PATH_MOD_FILE;
				foreach($foldersList as $cpt=>$objFolder){
					if($objFolder->isRootFolder()==false)  {$return.=$objFolder->_id."/";}
				}
				return $return;
			}
			//// Retourne le chemin au format "text" ou "zip"
			else{
				$return=null;
				foreach($foldersList as $cpt=>$objFolder){
					if($typeReturn=="text")		{$return.=($cpt>0?" <img src='app/img/arrowRight.png'> ":null).$objFolder->name; }	//format "text" : Dossier racine > Mon sous-dossier
					elseif($typeReturn=="zip")	{$return.=Txt::clean($objFolder->name,"download")."/";}								//format "zip" : Dossier_racine/mon_sous-dossier/
				}
				return $return;
			}
		}
	}

	/*
	 * Arborescence d'objets dossiers (fonction récursive)
	 */
	public function folderTree($accessRightMini=1, $curFolder=null, $treeLevel=0)
	{
		//Arborescence "visible" déjà en cache : renvoie le résultat
		if($treeLevel==0 && $accessRightMini==1 && !empty($this->_visibleFolderTree))  {return $this->_visibleFolderTree;}
		//Init la liste des sous-dossiers && le dossier de départ de toute l'arborescence
		$folderList=[];
		if($curFolder==null)  {$curFolder=$this;}
		//Ajoute le dossier courant ?
		if($accessRightMini=="all" || $curFolder->accessRight()>=$accessRightMini)
		{
			//Ajoute le dossier courant à la liste, avec son niveau
			$curFolder->treeLevel=$treeLevel;
			$folderList[]=$curFolder;
			//Récupère récursivement les sous-dossiers du dossier courant (tjs triés par nom)  =>  tous les sous-dossiers ("all")  OU  les sous-dossiers en fonction de leur droit d'accès ("sqlDisplayedObjects()") 
			$sqlDisplayedObjects=($accessRightMini=="all")  ?  "_idContainer=".$curFolder->_id  :  static::sqlDisplayedObjects($curFolder);
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
	public function folders()
	{
		$vDatas["foldersList"]=Db::getObjTab(static::objectType, "SELECT * FROM ".static::dbTable." WHERE ".static::sqlDisplayedObjects($this)." ".static::sqlSort());
		if(!empty($vDatas["foldersList"])){
			$vDatas["objContainerClass"]=null;
			if(static::moduleName=="file")			{$vDatas["objContainerClass"]="objContentCenter";}//Dossier de fichiers : affichage centré de l'icone et du contenu
			elseif(static::moduleName=="contact")  {$vDatas["objContainerClass"]="objPerson";}//Affichage plus grand du conteneur 
			return Ctrl::getVue(Req::commonPath."VueObjFolders.php",$vDatas);
		}
	}
}