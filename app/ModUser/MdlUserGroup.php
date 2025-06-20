<?php
/**
* This file is part of the Agora-Project Software package
*
* @copyleft Agora-Project <https://www.agora-project.net>
* @license GNU General Public License (GPL-2.0)
*/


/*
 * MODELE DES GROUPES D'UTILISATEURS
 */
class MdlUserGroup extends MdlObject
{
	const moduleName="user";
	const objectType="userGroup";
	const dbTable="ap_userGroup";
	public static $requiredFields=["title"];
	public static $sortFields=["title@asc","title@desc"];

	/********************************************************************************************************
	 * SURCHARGE : CONSTRUCTEUR
	 ********************************************************************************************************/
	function __construct($objIdOrValues=null)
	{
		parent::__construct($objIdOrValues);
		//Users affectés au groupe (verif qu'ils sont tous affectés à l'espace via "array_intersect()")
		$groupUsersIds=Txt::txt2tab($this->_idUsers);
		$spaceUsersIds=Ctrl::getObj("space",$this->_idSpace)->getUsers("idsTab");
		$this->userIds=(array)array_intersect($groupUsersIds, $spaceUsersIds);
		//Libellé des users
		$this->usersLabel=null;
		foreach($this->userIds as $userId)	{$this->usersLabel.=Ctrl::getObj("user",$userId)->getLabel().", ";}
		$this->usersLabel=@trim($this->usersLabel,", ");
	}

	/********************************************************************************************************
	 * SURCHARGE : DROIT D'ACCÈS AU GROUPE
	 ********************************************************************************************************/
	public function accessRight()
	{
		if($this->_accessRight===null){
			$this->_accessRight=parent::accessRight();
			//Ajoute l'accès en lecture si :  User courant se trouve dans le groupe  OU  l'espace du groupe fait partie des espaces de l'user (pour les affectations d'objet)
			if(empty($this->_accessRight) && (in_array(Ctrl::$curUser->_id,$this->userIds) || in_array($this->_idSpace,Ctrl::$curUser->spaceList("ids"))))	{$this->_accessRight=1;}
		}
		return $this->_accessRight;
	}

	/********************************************************************************************************
	 * SURCHARGE : DROIT D'ÉDITION DU GROUPE (ACCÈS TOTAL OU ADMIN D'ESPACE)
	 ********************************************************************************************************/
	public function editRight()
	{
		return (parent::editRight() || Ctrl::$curUser->isSpaceAdmin());
	}

	/********************************************************************************************************
	 * SURCHARGE : SUPPRIME UN GROUPE
	 ********************************************************************************************************/
	public function delete()
	{
		if($this->deleteRight()){
			Db::query("DELETE FROM ap_objectTarget WHERE target=".Db::format("G".$this->_id));
			parent::delete();
		}
	}

	/********************************************************************************************************
	 * GROUPES D'UTILISATEURS (AFFECTÉS À UN ESPACE ET/OU AFFECTÉS À UN UTILISATEUR?)
	 ********************************************************************************************************/
	public static function getGroups($objSpace=null, $objUser=null)
	{
		$sqlFilter=null;
		if(is_object($objSpace))	{$sqlFilter.=" AND _idSpace=".$objSpace->_id;}
		if(is_object($objUser))		{$sqlFilter.=" AND _idUsers LIKE '%@".$objUser->_id."@%'";}
		return Db::getObjTab(static::objectType, "SELECT * FROM ".self::dbTable." WHERE 1 ".$sqlFilter." ORDER BY title");
	}

	/********************************************************************************************************
	 * DROIT D'AJOUTER UN NOUVEAU GROUPE POUR L'USER COURANT
	 ********************************************************************************************************/
	public static function addRight()
	{
		return (Ctrl::$curUser->isSpaceAdmin() || (Ctrl::$curUser->isUser() && Ctrl::$curSpace->moduleOptionEnabled(self::moduleName,"allUsersAddGroup")));
	}
}