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
	//Valeurs en cache
	private $_usersAccessRight=[];
	private $_allUsersAffected=null;
	private $_spaceUsers=null;
	private $_modulesAvailable=[];
	private $_modules=[];


	
	/********************************************************************************************************
	 * SURCHARGE : DROITS D'ACCÈS A L'ESPACE POUR L'USER COURANT
	 ********************************************************************************************************/
	public function accessRight()
	{
		return $this->accessRightUser(Ctrl::$curUser);
 	}

	 /********************************************************************************************************
	  * SURCHARGE : DROIT D'ÉDITION DE L'ESPACE POUR L'USER COURANT
	 ********************************************************************************************************/
	 public function editRight()
	 {
		 return ($this->accessRight()==2);
	 }
 
	 /********************************************************************************************************
	  * SURCHARGE : DROIT DE SUPPRESSION DE L'ESPACE POUR L'USER COURANT
	  ********************************************************************************************************/
	 public function deleteRight()
	 {
		 return (Ctrl::$curUser->isGeneralAdmin() && $this->isCurSpace()==false);
	 }

	/********************************************************************************************************
	 * VÉRIFIE SI L'ESPACE EN QUESTION EST L'ESPACE COURANT
	 ********************************************************************************************************/
	public function isCurSpace()
	{
		return ($this->_id==Ctrl::$curSpace->_id);
	}

	/*********************************************************************************************************
	 * DROIT D'ACCÈS D'UN USER À L'ESPACE
	 * admin => 2 || user lambda ou guest => 1 || aucun accès => 0
	 *********************************************************************************************************/
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

	/*********************************************************************************************************
	 * AFFECTATION D'UN USER À L'ESPACE : DROIT MAXI || "allUsers" SELECTIONNÉ
	**********************************************************************************************************/
	private function userAffectation($objUser)
	{
		return (int)Db::getVal("SELECT MAX(accessRight) FROM ap_joinSpaceUser WHERE _idSpace=".$this->_id." AND (_idUser=".(int)$objUser->_id." OR allUsers=1)");
	}

	/********************************************************************************************************
	 * VERIFIE SI TOUS LES UTILISATEURS DU SITE SONT AFFECTES À L'ESPACE
	 ********************************************************************************************************/
	public function allUsersAffected()
	{
		if($this->_allUsersAffected===null){
			$this->_allUsersAffected=(Db::getVal("SELECT count(*) FROM ap_joinSpaceUser WHERE _idSpace=".$this->_id." AND allUsers=1") > 0);
		}
		return $this->_allUsersAffected;
	}

	/********************************************************************************************************
	 * UTILISATEURS AFFECTÉS À UN ESPACE  ($return= "objects" OU "idsTab" OU "idsSql")
	 ********************************************************************************************************/
	public function getUsers($return="objects")
	{
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
				return implode(",",$idsList);										//ex: "WHERE _idUser IN (1,3,5,0)")
			}
		}
	}

	/********************************************************************************************************
	 * LISTE DES MODULES D'UN ESPACE / LISTE DE TOUS LES MODULES DISPONIBLES
	 ********************************************************************************************************/
	public function moduleList($allModulesAvailable=false, $removePersonalCalendar=false)
	{
		////	Cache des modules de l'espace
		if(empty($this->_modules)){
			////	MODULES DISPONIBLES
			$moduleNames=["dashboard","file","calendar","forum","task","link","contact","mail","user"];
			foreach($moduleNames as $moduleName){
				$modCtrl="Ctrl".ucfirst($moduleName);
				$this->_modulesAvailable[$moduleName]=[
					"moduleName"		=> $moduleName,
					"ctrl"				=> $modCtrl,
					"optionsAvailable"	=> $modCtrl::$moduleOptions,
					"url"				=> "?ctrl=".$moduleName.($moduleName=="user"?"&displayUsers=space":null),
					"label"				=> Txt::trad(strtoupper($moduleName)."_MODULE_NAME"),
					"description"		=> Txt::trad(strtoupper($moduleName)."_MODULE_DESCRIPTION"),
				];
			}
			////	MODULES DE L'ESPACE (BDD)
			$spaceModules=Db::getTab("SELECT * FROM ap_joinSpaceModule WHERE _idSpace=".$this->_id." ORDER BY `rank` ASC");
			foreach($spaceModules as $tmpModule){
				$moduleName=$tmpModule["moduleName"];																//Nom du module
				if(Ctrl::$curUser->isGuest() && preg_match("/(mail|user)/i",$moduleName))  {continue;}				//Guests : pas de module mail/user
				else{																								//Sinon ajoute le module
					$tmpModule["enabled"]=true;																		//Module activé
					$this->_modules[$moduleName]=array_merge($this->_modulesAvailable[$moduleName], $tmpModule);	//Ajoute les propriétés du module dans _modulesAvailable PUIS celles dans $tmpModule
				}
			}
			////	MODULE CALENDAR ABSENT + AGENDA PERSO ACTIVÉ : AJOUTE L'AGENDA PERSO DE L'USER
			if(empty($this->_modules["calendar"]) && Ctrl::$curUser->isUser() && empty(Ctrl::$curUser->calendarDisabled)){
				$this->_modules["calendar"]=$this->_modulesAvailable["calendar"];
				$this->_modules["calendar"]["isPersonalCalendar"]=true;
			}
			////	MODULE MAIL ACTIVÉ + OPTION "onlyAdminAccess" ACTIVÉ + PAS ADMIN DE L'ESPACE : ENLEVE LE MODULE MAIL
			if(!empty($this->_modules["mail"]["options"]) && stristr($this->_modules["mail"]["options"],"onlyAdminAccess") && Ctrl::$curUser->isSpaceAdmin()==false)
				{unset($this->_modules["mail"]);}
		}

		////	MODULES DE L'ESPACE + AUTRES MODULES DISPONIBLES (VueEditSpace)
		if($allModulesAvailable==true){
			$modulesDisabled=array_diff_key($this->_modulesAvailable,$this->_modules);
			return array_merge($this->_modules, $modulesDisabled);
		}
		////	MODULES DE L'ESPACE
		else{
			$modules=$this->_modules;
			if($removePersonalCalendar==true && isset($modules["calendar"]["isPersonalCalendar"]))//Enlève l'agenda perso si on affiche les modules activés l'espace
				{unset($modules["calendar"]);}
			return $modules;
		}
	}

	/********************************************************************************************************
	 * CONTROLE L'ACCES A UN CONTROLEUR / MODULE
	 ********************************************************************************************************/
	public function moduleEnabled($ctrlName)
	{
		$basicCtrl=in_array($ctrlName,["offline","misc","object"]);																				//Controleurs de base
		$moduleSpaceAdmin  =(in_array($ctrlName,["log"]) && Ctrl::$curUser->isSpaceAdmin());													//Mod de l'admin d'espace
		$moduleGeneralAdmin=(in_array($ctrlName,["agora","space"]) && Ctrl::$curUser->isGeneralAdmin());										//Mod de l'admin général
		return ($basicCtrl==true || $moduleSpaceAdmin==true || $moduleGeneralAdmin==true || array_key_exists($ctrlName,$this->moduleList()));	//Controleur ou Module accessible depuis l'espace courant
	}

	/********************************************************************************************************
	 * OPTION D'UN MODULE ACTIVÉ POUR L'ESPACE ?
	 ********************************************************************************************************/
	public function moduleOptionEnabled($moduleName, $optionName)
	{
		$moduleList=$this->moduleList();
		return (!empty($moduleList[$moduleName]["options"]) && stristr($moduleList[$moduleName]["options"],$optionName));
	}

	/********************************************************************************************************
	 * SURCHARGE : SUPPRIME UN ESPACE DÉFINITIVEMENT!
	 ********************************************************************************************************/
	public function delete()
	{
		if($this->deleteRight())
		{
			//Supprime les objets affectés uniquement à l'espace courant
			$objectsOnlyInCurSpace=Db::getTab("SELECT * FROM ap_objectTarget WHERE _idSpace=".$this->_id." AND concat(objectType,_idObject) NOT IN (select concat(objectType,_idObject) from ap_objectTarget where _idSpace!=".$this->_id." or _idSpace IS NULL) ORDER BY objectType, _idObject");
			foreach($objectsOnlyInCurSpace as $tmpObject){
				//Charge l'objet et le supprime (sauf les dossiers racine et agendas persos)
				$tmpObj=Ctrl::getObj($tmpObject["objectType"],$tmpObject["_idObject"]);
				$isPersonalCalendar=($tmpObj::objectType=="calendar" && $tmpObj->isMyPersoCalendar());
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