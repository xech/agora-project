<?php
/**
* This file is part of the Agora-Project Software package.
*
* @copyright (c) Agora-Project Limited <https://www.agora-project.net>
* @license GNU General Public License, version 2 (GPL-2.0)
*/


/*
 * MODELE DES THEMES DE SUJETS
 */
class MdlForumTheme extends MdlObjectCategory
{
	const moduleName="forum";
	const objectType="forumTheme";
	const dbTable="ap_forumTheme";
	const dbTableParent="ap_forumSubject";
	const _idFieldName="_idTheme";
	const optionAdminAddCategory="adminAddTheme";
	public static $requiredFields=["title"];
	public static $sortFields=["title@asc","title@desc"];

	/*******************************************************************************************
	 * SURCHARGE : CONSTRUCTEUR
	 *******************************************************************************************/
	function __construct($objIdOrValues=null)
	{
		//Appel du constructeur parent
		parent::__construct($objIdOrValues);
		//Spécifie "_idThemeFilter" si besoin (cf. "noTheme")
		if(empty($this->_idThemeFilter))	{$this->_idThemeFilter=$this->_id;}
	}

	/*******************************************************************************************
	* SURCHARGE : RETOURNE "SANS THEME" OU LE TITRE DU THEME
	*******************************************************************************************/
	public function getLabel()
	{
		if($this->_idThemeFilter==="noTheme")	{return "<div class='categoryColor categoryColorAll'>&nbsp;</div> <i>".Txt::trad("FORUM_categoryUndefined")."</i>";}
		else									{return parent::getLabel();}
	}
}