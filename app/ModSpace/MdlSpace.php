<?php
/**
* This file is part of the Agora-Project Software package
*
* @copyleft Agora-Project <https://www.agora-project.net>
* @license GNU General Public License (GPL-2.0)
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
	//Valeurs mises en cache
	private $_usersAccessRight=[];
	private $_allUsersAffected=null;
	private $_spaceUsers=null;
	private $_moduleList=[];


	
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
		 return (Ctrl::$curUser->isGeneralAdmin() && $this->isCurSpace()==false);
	 }

	/*****************************************************************************************************************
	 * DROIT D'ACCÈS D'UN USER À L'ESPACE
	 * admin => 2 || user lambda ou guest => 1 || aucun accès => 0
	 *****************************************************************************************************************/
	public function accessRightUser($objUser)
	{
		if(empty($this->_usersAccessRight[$objUser->_id])){									//Init "_usersAccessRight" si pas encore en "cache"
			if($objUser->isGeneralAdmin())	{$curRight=2;}									//Droit d'admin général (même si aucun affectation à l'espace)
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
		//Init "_allUsersAffected" si pas encore en "cache"
		if($this->_allUsersAffected===null){
			$this->_allUsersAffected=(Db::getVal("SELECT count(*) FROM ap_joinSpaceUser WHERE _idSpace=".$this->_id." AND allUsers=1") > 0);
		}
		return $this->_allUsersAffected;
	}

	/*******************************************************************************************
	 * UTILISATEURS AFFECTÉS À UN ESPACE  ($return= "objects" OU "idsTab" OU "idsSql")
	 *******************************************************************************************/
	public function getUsers($return="objects")
	{
		// Init "_spaceUsers" si pas encore en "cache"
		if($this->_spaceUsers===null){
			$personsSort="ORDER BY ".Ctrl::$agora->personsSort;
			$objUsers=($this->allUsersAffected())  ?  Db::getObjTab("user","SELECT * FROM ap_user ".$personsSort)  :  Db::getObjTab("user","SELECT DISTINCT T1.* FROM ap_user T1, ap_joinSpaceUser T2 WHERE T1._id=T2._idUser AND T2._idSpace=".$this->_id." ".$personsSort);
			$this->_spaceUsers=$objUsers;
		}
		// Renvoie un tableau d'users (objets)  ||  Renvoie une liste d'ids d'users
		if($return=="objects")	{return $this->_spaceUsers;}
		else{
			$idsList=[];
			foreach($this->_spaceUsers as $objUser)  {$idsList[]=$objUser->_id;}	//Ajoute chaque _id
			if($return=="idsTab")  {return $idsList;}								//Retourne une liste d'_id
			elseif($return=="idsSql"){												//Retourne une liste d'identifiants pour les requêtes SQL
				$idsList[]=0;														//Ajoute un pseudo user pour pas avoir d'erreur SQL si la liste est vide
				return implode(",",$idsList);										//Exple: "WHERE _idUser IN (1,3,5,0)")
			}
		}
	}

	/*******************************************************************************************
	 * MODULES DISPONIBLES ET LEURS PROPRIÉTÉS DE BASE
	 *******************************************************************************************/
	public static function availableModules()
	{
		$availableModulesName=["dashboard","file","calendar","forum","task","link","contact","mail","user"];
		foreach($availableModulesName as $moduleName){
			$availableModules[$moduleName]=[
				"moduleName"	=> $moduleName,
				"ctrl"			=> "Ctrl".ucfirst($moduleName),
				"url"			=> "?ctrl=".$moduleName.($moduleName=="user"?"&displayUsers=space":null),
				"label"			=> Txt::trad(strtoupper($moduleName)."_MODULE_NAME"),
				"description"	=> Txt::trad(strtoupper($moduleName)."_MODULE_DESCRIPTION")
			];
		}
		return $availableModules;
	}

	/*******************************************************************************************
	 * LISTE DES MODULES AFFECTÉS A UN ESPACE
	 *******************************************************************************************/
	public function moduleList($persoCalendarSkip=false)
	{
		//Init "_moduleList" si pas encore en "cache"
		if(empty($this->_moduleList)){
			$availableModules=self::availableModules();																							//Modules disponibles
			foreach(Db::getTab("SELECT * FROM ap_joinSpaceModule WHERE _idSpace=".$this->_id." ORDER BY `rank` ASC") as $tmpModule){			//Modules affectés à l'espace (DB)
				$moduleName=$tmpModule["moduleName"];																							//Nom du module
				if(Ctrl::$curUser->isUser()==false && ($moduleName=="mail" || ($moduleName=="user" && empty($this->password))))  {continue;}	//Pas de module mail/user pour les guests (sauf si user+password)
				$this->_moduleList[$moduleName]=array_merge($availableModules[$moduleName], $tmpModule);										//Ajoute le module et ses propriétés
			}
			//Ajoute l'agenda perso s'il est activé pour l'user courant et qu'il n'est pas dans la liste des modules
			if(Ctrl::$curUser->isUser() && empty(Ctrl::$curUser->calendarDisabled) && array_key_exists("calendar",$this->_moduleList)==false)
				{$this->_moduleList["calendar"]=array_merge($availableModules["calendar"], ["rank"=>100,"options"=>null,"isPersoCalendar"=>true]);}
		}
		// Renvoi les modules (avec/sans l'agenda perso ?)
		if($persoCalendarSkip==true && isset($this->_moduleList["calendar"]["isPersoCalendar"])){
			$moduleList=$this->_moduleList;
			unset($moduleList["calendar"]);
			return $moduleList;
		}else{
			return $this->_moduleList;
		}
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
				//Charge l'objet et le supprime (sauf les dossiers racine et agendas persos)
				$tmpObj=Ctrl::getObj($tmpObject["objectType"],$tmpObject["_idObject"]);
				$isPersonalCalendar=($tmpObj::objectType=="calendar" && $tmpObj->isPersonalCalendar());
				if(MdlObject::isObject($tmpObj) && $tmpObj->isRootFolder()==false && $isPersonalCalendar==false)  {$tmpObj->delete();}
			}
			//Supprime les affectations espace->modules, espace->users, espace->objets (pour les objets affectés à plusieurs espaces) et espace->invitations
			Db::query("DELETE FROM ap_joinSpaceModule WHERE _idSpace=".$this->_id);
			Db::query("DELETE FROM ap_joinSpaceUser WHERE _idSpace=".$this->_id);
			Db::query("DELETE FROM ap_objectTarget WHERE _idSpace=".$this->_id);
			Db::query("DELETE FROM ap_invitation WHERE _idSpace=".$this->_id);
			//Supprime l'espace && Recalcule la taille du 'DATAS/' && affiche une notification
			parent::delete();
			File::datasFolderSize(true);
		}
	}
}