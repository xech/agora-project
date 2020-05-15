<?php
/**
* This file is part of the Agora-Project Software package.
*
* @copyright (c) Agora-Project Limited <https://www.agora-project.net>
* @license GNU General Public License, version 2 (GPL-2.0)
*/


/*
 * Modele des espaces
 */
class MdlSpace extends MdlObject
{
	const moduleName="space";
	const objectType="space";
	const dbTable="ap_space";
	public static $requiredFields=array("name");
	public static $sortFields=array("name@@asc","name@@desc","description@@asc","description@@desc");
	//Liste des modules pouvant être affectés à un espace
	public static $moduleList=["dashboard","user","calendar","file","forum","task","link","contact","mail"];
	//Valeurs mises en cache
	private $_allUsersAffected=null;
	private $_spaceUsers=null;
	private $_moduleList=array();
	private $_usersAccessRight=array();

	/*
	 * SURCHARGE : Droit d'accès à un espace
	 */
	public function accessRight()
	{
		if($this->_accessRight===null){
			$this->_accessRight=parent::accessRight();//Droit par défaut
			if($this->userAccessRight(Ctrl::$curUser) > $this->_accessRight)  {$this->_accessRight=$this->userAccessRight(Ctrl::$curUser);}
		}
		return $this->_accessRight;
 	}

	/*
	 * Tous les modules disponibles pour l'espace
	 */
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

	/*
	 * Modules de l'espace
	 */
	public function moduleList($addPersoCalendar=true)
	{
		//// INIT LA MISE EN CACHE : MODULES AFFECTES A L'ESPACE
		if(empty($this->_moduleList))
		{
			//Modules disponibles
			$availableModules=self::availableModuleList();
			//Modules affectés à l'espace en Bdd
			foreach(Db::getTab("SELECT * FROM ap_joinSpaceModule WHERE _idSpace=".$this->_id." ORDER BY rank ASC") as $tmpModule){
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
	
	/*
	 * Verif si un module est activé sur l'espace
	 */
	public function moduleEnabled($moduleName)
	{
		$moduleList=$this->moduleList();
		return !empty($moduleList[$moduleName]);
	}
	
	/*
	 * Verifie si tous les utilisateurs du site sont affectes à l'espace
	 */
	public function allUsersAffected()
	{
		if($this->_allUsersAffected===null)
			{$this->_allUsersAffected=(Db::getVal("SELECT count(*) FROM ap_joinSpaceUser WHERE _idSpace=".$this->_id." AND allUsers=1")>0)  ?  true  :  false;}
		return $this->_allUsersAffected;
	}

	/*
	 * Utilisateurs affectés à l'espace
	 * $return= "objects" ou "idsTab" ou "idsSql"
	 */
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

	/*
	 * Droit d'accès d'un utilisateur à l'espace courant :  2 = admin  /  1 = user lambda ou guest  /  0 = aucun accès
	 */
	public function userAccessRight($objUser)
	{
		//Init la mise en cache du droit d'accès de l'user demandé :  Droit d'admin général  ||  Droit maxi affecté à un user  ||  Droit d'accès à l'espace public (guests)
		if(empty($this->_usersAccessRight[$objUser->_id]))
		{
			if($objUser->isAdminGeneral())	{$curRight=2;}
			elseif($objUser->isUser())		{$curRight=Db::getVal("SELECT MAX(accessRight) FROM ap_joinSpaceUser WHERE _idSpace=".$this->_id." AND (_idUser=".(int)$objUser->_id." OR allUsers=1)");}
			else							{$curRight=$this->public;}
			$this->_usersAccessRight[$objUser->_id]=(int)$curRight;
		}
		return $this->_usersAccessRight[$objUser->_id];
	}

	/*
	 * SURCHARGE : Droit d'édition d'un objet
	 */
	public function editRight()
	{
		return ($this->userAccessRight(Ctrl::$curUser)==2);
	}

	/*
	 * Droit de suppression d'un espace (pas l'espace courant)
	 */
	public function deleteRight()
	{
		return (Ctrl::$curUser->isAdminGeneral() && $this->isCurSpace()==false);
	}

	/*
	 * Vérifie si l'espace en question est l'espace courant
	 */
	public function isCurSpace()
	{
		return ($this->_id==Ctrl::$curSpace->_id);
	}

	/*
	 * Option d'un module activé pour l'espace ?
	 */
	public function moduleOptionEnabled($moduleName, $optionName)
	{
		$moduleList=$this->moduleList();
		return (!empty($moduleList[$moduleName]["options"]) && preg_match("/".$optionName."/i",$moduleList[$moduleName]["options"]));
	}

	/*
	 * SURCHARGE : Supprime un espace définitivement!
	 */
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
			Ctrl::addNotif("Suppression OK");
		}
	}
}