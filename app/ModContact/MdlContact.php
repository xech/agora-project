<?php
/**
* This file is part of the Agora-Project Software package.
*
* @copyright (c) Agora-Project Limited <https://www.agora-project.net>
* @license GNU General Public License, version 2 (GPL-2.0)
*/


/*******************************************************************************************
 * MODELE DES CONTACT
 *******************************************************************************************/
class MdlContact extends MdlPerson
{
	const moduleName="contact";
	const objectType="contact";
	const dbTable="ap_contact";
	const MdlObjectContainer="MdlContactFolder";
	const isFolderContent=true;
	const isSelectable=true;
	const hasShortcut=true;
	const hasUsersComment=true;
	const hasUsersLike=true;
	const hasAttachedFiles=true;
	const hasNotifMail=true;
	public static $requiredFields=["name"];
	public static $sortFields=["name@@asc","name@@desc","firstName@@asc","firstName@@desc","civility@@asc","civility@@desc","postalCode@@asc","postalCode@@desc","city@@asc","city@@desc","country@@asc","country@@desc","function@@asc","function@@desc","companyOrganization@@asc","companyOrganization@@desc","_idUser@@asc","_idUser@@desc","dateCrea@@desc","dateCrea@@asc","dateModif@@desc","dateModif@@asc"];

	/*******************************************************************************************
	 * PHOTO D'UN CONTACT
	 *******************************************************************************************/
	public function pathImgThumb()
	{
		return PATH_MOD_CONTACT.$this->_id."_thumb.jpg";
	}

	/*******************************************************************************************
	 * SURCHARGE : SUPPRIME UN CONTACT
	 *******************************************************************************************/
	public function delete()
	{
		if($this->deleteRight()){
			if($this->hasImg())  {unlink($this->pathImgThumb());}
			parent::delete();
		}
	}

	/*******************************************************************************************
	 * SURCHARGE : MENU CONTEXTUEL
	 *******************************************************************************************/
	public function contextMenu($options=null)
	{
		//"Créer un utilisateur sur cet espace" : admin général uniquement!
		if(Ctrl::$curUser->isAdminGeneral())
			{$options["specificOptions"][]=["actionJs"=>"contactAddUser('".$this->_typeId."')", "iconSrc"=>"plus.png", "label"=>Txt::trad("CONTACT_createUser"), "tooltip"=>Txt::trad("CONTACT_createUserInfo")];}
		return parent::contextMenu($options);
	}
}