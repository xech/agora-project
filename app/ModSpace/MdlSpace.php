<?php
/**
* This file is part of the Agora-Project Software package.
*
* @copyright (c) Agora-Project Limited <https://www.agora-project.net>
* @license GNU General Public License, version 2 (GPL-2.0)
*/


/*
 * MODELE DES ESPACES
 */
class MdlSpace extends MdlObject
{
	const moduleName="space";
	const objectType="space";
	const dbTable="ap_space";
	public static $requiredFields=["name"];
	public static $sortFields=["name@@asc","name@@desc","description@@asc","description@@desc"];
	//Liste des modules pouvant être affectés à un espace
	public static $moduleList=["dashboard","user","calendar","file","forum","task","link","contact","mail"];
	//Valeurs mises en cache
	private $_allUsersAffected=null;
	private $_spaceUsers=null;
	private $_moduleList=[];
	private $_usersAccessRight=[];

	/*******************************************************************************************
	 * SURCHARGE : DROITS D'ACCÈS A L'ESPACE POUR L'USER COURANT
	 *******************************************************************************************/
	public function accessRight()
	{
		return $this->accessRightUser(Ctrl::$curUser);
 	}

	 /*******************************************************************************************
	  * SURCHARGE : DROIT D'ÉDITION DE L'ESPACE POUR L'USER COURANT
	 *******************************************************************************************/
	 public function editRight()
	 {
		 return ($this->accessRight()==2);
	 }
 
	 /*******************************************************************************************
	  * SURCHARGE : DROIT DE SUPPRESSION DE L'ESPACE POUR L'USER COURANT
	  *******************************************************************************************/
	 public function deleteRight()
	 {
		 return (Ctrl::$curUser->isAdminGeneral() && $this->isCurSpace()==false);
	 }

	/*****************************************************************************************************************
	 * DROIT D'ACCÈS D'UN USER À L'ESPACE
	 * admin => 2 || user lambda ou guest => 1 || aucun accès => 0
	 *****************************************************************************************************************/
	public function accessRightUser($objUser)
	{
		if(empty($this->_usersAccessRight[$objUser->_id]))									//Droit d'accès déjà en "cache" ?
		{
			if($objUser->isAdminGeneral())	{$curRight=2;}									//Droit d'admin général (même si aucun affectation à l'espace)
			elseif($objUser->isUser())		{$curRight=$this->userAffectation($objUser);}	//Droit d'affectation de l'user
			else							{$curRight=$this->public;}						//Droit d'accès "guest" (espace public)
			$this->_usersAccessRight[$objUser->_id]=(int)$curRight;							//Ajoute le droit d'accès en "cache"
		}
		return $this->_usersAccessRight[$objUser->_id];										//Renvoie le droit d'accès
	}

	/*****************************************************************************************************************
	 * AFFECTATION D'UN USER À L'ESPACE : DROIT MAXI || "allUsers" SELECTIONNÉ
	*****************************************************************************************************************/
	public function userAffectation($objUser)
	{
		return (int)Db::getVal("SELECT MAX(accessRight) FROM ap_joinSpaceUser WHERE _idSpace=".$this->_id." AND (_idUser=".(int)$objUser->_id." OR allUsers=1)");
	}

	/*******************************************************************************************
	 * VERIFIE SI TOUS LES UTILISATEURS DU SITE SONT AFFECTES À L'ESPACE
	 *******************************************************************************************/
	public function allUsersAffected()
	{
		if($this->_allUsersAffected===null)  {$this->_allUsersAffected=(Db::getVal("SELECT count(*) FROM ap_joinSpaceUser WHERE _idSpace=".$this->_id." AND allUsers=1") > 0);}
		return $this->_allUsersAffected;
	}

	/*******************************************************************************************
	 * UTILISATEURS AFFECTÉS À UN ESPACE  ($return= "objects" OU "idsTab" OU "idsSql")
	 *******************************************************************************************/
	public function getUsers($return="objects")
	{
		//Initialise la liste des objets "user"
		if($this->_spaceUsers===null){
			$personsSort="ORDER BY ".Ctrl::$agora->personsSort;
			$objUsers=($this->allUsersAffected())  ?  Db::getObjTab("user","SELECT * FROM ap_user ".$personsSort)  :  Db::getObjTab("user","SELECT DISTINCT T1.* FROM ap_user T1, ap_joinSpaceUser T2 WHERE T1._id=T2._idUser AND T2._idSpace=".$this->_id." ".$personsSort);
			$this->_spaceUsers=$objUsers;
		}
		//Retourne un tableau d'objets OU une liste d'identifiants
		if($return=="objects")	{return $this->_spaceUsers;}
		else
		{
			//Liste des ids d'users
			$idsList=[];
			foreach($this->_spaceUsers as $objUser)  {$idsList[]=$objUser->_id;}
			//Retourne le tableau d'identifiants
			if($return=="idsTab")  {return $idsList;}
			//Sinon retourne une liste d'identifiants pour les requêtes SQL (exple: "WHERE _idUser IN (1,3,5,0)")
			elseif($return=="idsSql"){
				$idsList[]=0;//Ajoute un pseudo user pour pas avoir d'erreur SQL si la liste est vide
				return implode(",",$idsList);
			}
		}
	}

	/*******************************************************************************************
	 * LISTE COMPLETE DES MODULES DISPONIBLES
	 *******************************************************************************************/
	public static function availableModuleList()
	{
		$moduleList=[];
		foreach(self::$moduleList as $moduleName)
		{
			$moduleList[$moduleName]=array(
				"moduleName"=>$moduleName,
				"ctrl"=>"Ctrl".ucfirst($moduleName),//controleur du module
				"url"=>"?ctrl=".$moduleName.($moduleName=="user"?"&displayUsers=space":null),
				"label"=>Txt::trad(strtoupper($moduleName)."_headerModuleName"),
				"description"=>Txt::trad(strtoupper($moduleName)."_moduleDescription")
			);
		}
		return $moduleList;
	}

	/*******************************************************************************************
	 * LISTE DES MODULES AFFECTÉS A UN ESPACE
	 *******************************************************************************************/
	public function moduleList($addPersoCalendar=true)
	{
		//// INIT LA MISE EN CACHE : MODULES AFFECTES A L'ESPACE
		if(empty($this->_moduleList))
		{
			//Modules disponibles
			$availableModules=self::availableModuleList();
			//Modules affectés à l'espace en Bdd
			foreach(Db::getTab("SELECT * FROM ap_joinSpaceModule WHERE _idSpace=".$this->_id." ORDER BY `rank` ASC") as $tmpModule){
				$moduleName=$tmpModule["moduleName"];
				if(Ctrl::$curUser->isUser()==false && ($moduleName=="mail" || ($moduleName=="user" && empty($this->password))))  {continue;}//Guests/invités : pas de module "mail" et "user" (sauf si password)
				$this->_moduleList[$moduleName]=array_merge($availableModules[$moduleName], $tmpModule);//Ajoute le module et ses propriétés
			}
			//Ajoute l'agenda perso ?
			if(Ctrl::$curUser->isUser() && Ctrl::$curUser->calendarDisabled!=1 && array_key_exists("calendar",$this->_moduleList)==false)
				{$this->_moduleList["calendar"]=array_merge($availableModules["calendar"], ["rank"=>100,"options"=>null,"persoCalendarAdded"=>true]);}
		}
		////	RENVOI LA LISTE
		$curmoduleList=$this->_moduleList;
		if($addPersoCalendar==false && isset($curmoduleList["calendar"]["persoCalendarAdded"]))  {unset($curmoduleList["calendar"]);}//enleve l'agenda perso ? (edition d'un espace ou autre)
		return $curmoduleList;
	}
	
	/*******************************************************************************************
	 * VERIF SI UN MODULE EST AFFECTÉ A UN ESPACE
	 *******************************************************************************************/
	public function moduleEnabled($moduleName)
	{
		$moduleList=$this->moduleList();
		return !empty($moduleList[$moduleName]);
	}

	/*******************************************************************************************
	 * VÉRIFIE SI L'ESPACE EN QUESTION EST L'ESPACE COURANT
	 *******************************************************************************************/
	public function isCurSpace()
	{
		return ($this->_id==Ctrl::$curSpace->_id);
	}

	/*******************************************************************************************
	 * OPTION D'UN MODULE ACTIVÉ POUR L'ESPACE ?
	 *******************************************************************************************/
	public function moduleOptionEnabled($moduleName, $optionName)
	{
		$moduleList=$this->moduleList();
		return (!empty($moduleList[$moduleName]["options"]) && preg_match("/".$optionName."/i",$moduleList[$moduleName]["options"]));
	}

	/*******************************************************************************************
	 * SURCHARGE : SUPPRIME UN ESPACE DÉFINITIVEMENT!
	 *******************************************************************************************/
	public function delete()
	{
		if($this->deleteRight())
		{
			//Supprime les objets affectés uniquement à l'espace courant
			$objectsOnlyInCurSpace=Db::getTab("SELECT * FROM ap_objectTarget WHERE _idSpace=".$this->_id." AND concat(objectType,_idObject) NOT IN (select concat(objectType,_idObject) from ap_objectTarget where _idSpace!=".$this->_id." or _idSpace is null) ORDER BY objectType, _idObject");
			foreach($objectsOnlyInCurSpace as $tmpObject){
				//Charge l'objet et vérifie qu'il est bien supprimable (important : vérifie que c'est pas un dossier racine ou un agenda perso)
				$tmpObj=Ctrl::getObj($tmpObject["objectType"],$tmpObject["_idObject"]);
				if(is_object($tmpObj) && $tmpObj->isNew()==false && $tmpObj->isRootFolder()==false && $tmpObj::objectType!="calendar" && $tmpObj->type!="user")   {$tmpObj->delete();}
			}
			//Supprime les affectations espace->modules, espace->users, espace->objets (pour les objets affectés à plusieurs espaces) et espace->invitations
			Db::query("DELETE FROM ap_joinSpaceModule WHERE _idSpace=".$this->_id);
			Db::query("DELETE FROM ap_joinSpaceUser WHERE _idSpace=".$this->_id);
			Db::query("DELETE FROM ap_objectTarget WHERE _idSpace=".$this->_id);
			Db::query("DELETE FROM ap_invitation WHERE _idSpace=".$this->_id);
			//Supprime l'espace && Recalcule la taille du 'DATAS/' && affiche une notification
			parent::delete();
			File::datasFolderSize(true);
			Ctrl::notify("Suppression OK");
		}
	}
}