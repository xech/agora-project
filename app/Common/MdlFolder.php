<?php
/**
* This file is part of the Agora-Project Software package
*
* @copyleft Agora-Project <https://www.agora-project.net>
* @license GNU General Public License (GPL-2.0)
*/

/*
 * Classe des Objects "FOLDER"
 */
 class MdlFolder extends MdlObject
{
	const isFolder=true;
	const isSelectable=true;
	const hasShortcut=true;
	const hasNotifMail=true;
	const hasUsersComment=true;
	const hasUsersLike=true;
	protected static $_hasAccessRight=true;
	public static $displayModes=["block","line"];
	public static $requiredFields=["name"];
	public static $searchFields=["name","description"];
	public static $sortFields=["name@@asc","name@@desc","description@@asc","description@@desc","dateCrea@@desc","dateCrea@@asc","dateModif@@desc","dateModif@@asc","_idUser@@asc","_idUser@@desc"];
	//Init le cache
	private $_contentDescription=null;

	/********************************************************************************************************
	 * SURCHARGE : CONSTRUCTEUR
	 ********************************************************************************************************/
	function __construct($objIdOrValues=null)
	{
		parent::__construct($objIdOrValues);
		if($this->_id==1)  {$this->name=Txt::trad("rootFolder");}
	}

	/********************************************************************************************************
	 * SURCHARGE : DROIT D'AJOUTER DU CONTENU DANS LE DOSSIER (RACINE/LAMBDA)
	 ********************************************************************************************************/
	public function addContentRight()
	{
		if($this->isRootFolder())	{return (Ctrl::$curUser->isSpaceAdmin()  ||  (Ctrl::$curUser->isUser() && Ctrl::$curSpace->moduleOptionEnabled(static::moduleName,"adminRootAddContent")==false));}
		else						{return parent::addContentRight();}
	}

	/********************************************************************************************************
	 * SURCHARGE : URL D'ACCÈS À L'OBJET  >  EDITE VIA "actionVueEditFolder()"
	 ********************************************************************************************************/
	public function getUrl($display=null)
	{
		return ($display=="edit")  ?  "?ctrl=object&action=VueEditFolder&typeId=".$this->_typeId  :  parent::getUrl($display);
	}

	/********************************************************************************************************
	 * SURCHARGE : AFFECTATIONS DE L'OBJET
	 ********************************************************************************************************/
	public function getAffectations()
	{
		//Nouveau dossier, mais pas à la racine : récupère les droits d'accès du dossier conteneur pour faire une "pré-affectation"
		if($this->isNew() && $this->containerObj()->isRootFolder()==false)	{return $this->containerObj()->getAffectations();}
		else																{return parent::getAffectations();}
	}

	/********************************************************************************************************
	 * SURCHARGE : DROIT D'ÉDITION
	 ********************************************************************************************************/
	public function editRight()
	{
		return (parent::editRight() && $this->isRootFolder()==false);
	}

	/********************************************************************************************************
	 * SURCHARGE : DROIT DE SUPPRESSION
	 ********************************************************************************************************/
	public function deleteRight()
	{
		return (parent::deleteRight() && $this->isRootFolder()==false);
	}

	/********************************************************************************************************
	 * SURCHARGE : SUPPRESSION D'UN DOSSIER
	 ********************************************************************************************************/
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

	/********************************************************************************************************
	 * CONTROLE SI UN DOSSIER SE TROUVE DANS L'ARBORECENCE DU DOSSIER COURANT
	 ********************************************************************************************************/
	public function isInCurrentTree($newFolder)
	{
		foreach($this->folderTree("all") as $tmpFolder){
			if($newFolder->_id==$tmpFolder->_id)  {return true;}
		}
	}

	/********************************************************************************************************
	 * ICONE DU DOSSIER
	 ********************************************************************************************************/
	public function iconPath()
	{
		return (!empty($this->icon))  ?  PATH_ICON_FOLDER.$this->icon  :  PATH_ICON_FOLDER."folder.png";
	}

	/********************************************************************************************************
	 * CONTENU D'UN DOSSIER  :  NOMBRE D'ELEMENTS + TAILLE DU DOSSIER (MODULE FICHIERS)
	 ********************************************************************************************************/
	public function contentDescription()
	{
		if($this->_contentDescription===null){
			//Init
			$MdlObjectContent=static::MdlObjectContent;
			$this->_contentDescription="";
			$nbSubFolders=Db::getVal("SELECT count(*) FROM ".static::dbTable." WHERE ".self::sqlDisplay($this));
			$nbElems=Db::getVal("SELECT count(*) FROM ".$MdlObjectContent::dbTable." WHERE ".$MdlObjectContent::sqlDisplay($this));
			////	Nombre de sous-dossiers
			if(!empty($nbSubFolders))  {$this->_contentDescription.=$nbSubFolders." ".($nbSubFolders>1?Txt::trad("folders"):Txt::trad("folder"));}
			////	Nombre d'elements dans le dossier (s'il y en a)  &&  taille des fichiers (si "fileFolder")
			if(!empty($nbElems)){
				if(!empty($this->_contentDescription))	{$this->_contentDescription.=" - ";}
				$this->_contentDescription.=$nbElems." ".Txt::trad($nbElems>1?"elements":"element");
				if(static::objectType=="fileFolder")	{$this->_contentDescription.=" - ".File::sizeLabel(Db::getVal("SELECT SUM(octetSize) FROM ".$MdlObjectContent::dbTable." WHERE _idContainer=".$this->_id));}
			}
			////	Aucun element..
			if(empty($this->_contentDescription))	{$this->_contentDescription="0 ".Txt::trad("element");}
		}
		return $this->_contentDescription;
	}

	/********************************************************************************************************
	 * CHEMIN D'UN DOSSIER : FONCTION RÉCURSIVE
	 * $typeReturn= object | id | real | text | zip
	 ********************************************************************************************************/
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
				$foldersIds=[];
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
					if($typeReturn=="text")		{$return.=($cpt>0?'<img src="app/img/arrowRight.png">':null).$objFolder->name; }//format "text" : Dossier racine > Mon sous-dossier
					elseif($typeReturn=="zip")	{$return.=Txt::clean($objFolder->name).'/';}									//format "zip" : Dossier_racine/mon_sous-dossier/
				}
				return $return;
			}
		}
	}

	/********************************************************************************************************
	 * ARBORESCENCE D'OBJETS DOSSIERS : FONCTION RÉCURSIVE
	 ********************************************************************************************************/
	public function folderTree($accessRightMin=1, $curFolder=null, $treeLevel=0)
	{
		////	Arbo du dossier racine (arbo complete) : renvoie l'arbo en cache?
		$isRootFolderTree=($this->_id==1 && $accessRightMin==1 && $treeLevel==0);					//Verif si on récupère l'arbo complete du dossier racine
		if($isRootFolderTree==true){																//Idem
			$rootFolderTreeSessKey="rootFolderTree_".static::objectType."_".Ctrl::$curSpace->_id;	//Clé de session de l'arbo root   (cf. module et espace courant)
			$rootFolderTreeSessKeyTime=$rootFolderTreeSessKey."_time";								//Clé de session de son timestamp (cf. verif d'update ci-dessous)
			$rootFolderTreeLastModifTime=Db::getVal("SELECT MAX(UNIX_TIMESTAMP(date)) FROM ap_log WHERE objectType='".static::objectType."'");								//Date de dernière modif de l'arbo dans les logs
			if(isset($_SESSION[$rootFolderTreeSessKey]) && $_SESSION[$rootFolderTreeSessKeyTime]>$rootFolderTreeLastModifTime)  {return $_SESSION[$rootFolderTreeSessKey];}	//Renvoie l'arbo en cache !
		}
		////	Init l'arbo finale & Ajoute si besoin le dossier de départ de l'arbo
		$curFolderTree=[];
		if($curFolder==null)  {$curFolder=$this;}
		////	Ajoute le dossier courant
		if($accessRightMin=="all" || $curFolder->accessRight()>=$accessRightMin){																	//Vérif le droit d'accès au dossier courant
			$curFolder->treeLevel=$treeLevel;																										//Ajoute le niveau du dossier courant par rapport au dossier demandé
			$curFolderTree[]=$curFolder;																											//Ajoute à l'arbo le dossier courant
			$sqlFilter=($accessRightMin=="all")  ?  "_idContainer=".$curFolder->_id  :  static::sqlDisplay($curFolder);								//Tous les dossiers ("all")  ||  Dossiers en fonction des droits d'accès
			foreach(Db::getObjTab(static::objectType, "SELECT * FROM ".static::dbTable." WHERE ".$sqlFilter." ORDER BY name ASC") as $subFolder){	//Récupère les sous-dossiers du dossier courant (triés par nom)
				$subFolderTree=$this->folderTree($accessRightMin, $subFolder, $treeLevel+1);														//Lance récursivement la fonction pour récupérer leurs sous-dossiers !
				$curFolderTree=array_merge($curFolderTree,$subFolderTree);																			//Ajoute tous les sous-dossiers à l'arbo courante
			}
		}
		////	Arborescence du dossier racine : met en cache
		if($isRootFolderTree==true){
			$_SESSION[$rootFolderTreeSessKey]=$curFolderTree;	//Ajoute en cache l'arbo du dossier racine
			$_SESSION[$rootFolderTreeSessKeyTime]=time();		//Timestamp de l'arbo mise en cache
		}
		////	Renvoie l'arborescence finale
		return $curFolderTree;
	}

	/********************************************************************************************************
	 * VUE : MENU DU CHEMIN DU DOSSIER COURANT
	 ********************************************************************************************************/
	public static function menuPath($addElemLabel=null, $addElemUrl=null)
	{
		//Affiche le chemin d'un dossier  ET/OU  L'option d'ajout d'élement
		if(Ctrl::$curContainer->isRootFolder()==false || !empty($addElemLabel)){
			$vDatas["addElemLabel"]=$addElemLabel;
			$vDatas["addElemUrl"]=$addElemUrl;
			$vDatas["curFolder"]=Ctrl::$curContainer;
			return Ctrl::getVue(Req::commonPath."VueFolderPath.php", $vDatas);
		}
	}

	/********************************************************************************************************
	 * VUE : MENU D'ARBORESCENCE DE DOSSIERS  ($context : "nav" / "move")
	 ********************************************************************************************************/
	public static function menuTree($context="nav")
	{
		//Affiche l'arborescence (si ya pas que le dossier racine)
		if(count(Ctrl::$curRootFolder->folderTree())>1){
			$vDatas["context"]=$context;
			$vueFolderTree=Req::commonPath."VueFolderTree.php";
			if($context=="nav")		{return Ctrl::getVue($vueFolderTree,$vDatas);}		//"nav"	 : renvoie le menu de navigation de l'arborescence de dossiers
			else					{CtrlObject::displayPage($vueFolderTree,$vDatas);}	//"move" : affiche uniquement le menu de selection d'un dossier pour y déplacer un element
		}
	}

	/********************************************************************************************************
	 * DÉTAILS COMPLÉMENTAIRES SUR LE DOSSIER => À SURCHARGER !
	 ********************************************************************************************************/
	public function folderOtherDetails(){}
}