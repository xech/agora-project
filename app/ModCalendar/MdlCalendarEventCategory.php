<?php
/**
* This file is part of the Agora-Project Software package.
*
* @copyright (c) Agora-Project Limited <https://www.agora-project.net>
* @license GNU General Public License, version 2 (GPL-2.0)
*/


/*
 * MODELE DES CATEGORIES D'EVENEMENTS
 */
class MdlCalendarEventCategory extends MdlObject
{
	const moduleName="calendar";
	const objectType="calendarEventCategory";
	const dbTable="ap_calendarEventCategory";
	public static $requiredFields=array("title");
	public static $sortFields=array("title@asc","title@desc");

	/*******************************************************************************************
	 * SURCHARGE : CONSTRUCTEUR
	 *******************************************************************************************/
	function __construct($objIdOrValues=null)
	{
		parent::__construct($objIdOrValues);
		//Espaces ou est visible la categorie
		$this->spaceIds=Txt::txt2tab($this->_idSpaces);
		//Couleur par défaut
		if(empty($this->color))  {$this->color="#900";}
	}
	
	/*******************************************************************************************
	 * AFFICHE LA CATEGORIE AVEC UNE PASTILLE DE COULEUR
	 *******************************************************************************************/
	public function display()
	{
		if(!empty($this->title))	{return "<div class='categoryColor' style=\"background:".$this->color."\">&nbsp;</div> ".$this->title;}
	}

	/*******************************************************************************************
	 * CATEGORIES D'EVENEMENTS (FILTRE PAR ESPACE?)
	 *******************************************************************************************/
	public static function getCategories($editMode=false)
	{
		$sqlFilter=($editMode==true && Ctrl::$curUser->isAdminGeneral())  ?  null  :  " AND (_idSpaces is null OR _idSpaces LIKE '%@".Ctrl::$curSpace->_id."@%')";
		return Db::getObjTab(static::objectType, "SELECT * FROM ".self::dbTable." WHERE 1 ".$sqlFilter." ORDER BY title");
	}

	/*******************************************************************************************
	 * DROIT D'AJOUTER UNE NOUVELLE CATEGORIE
	 *******************************************************************************************/
	public static function addRight()
	{
		return (Ctrl::$curUser->isAdminSpace() || (Ctrl::$curUser->isUser() && Ctrl::$curSpace->moduleOptionEnabled(self::moduleName,"adminAddCategory")==false));
	}
	
	/*******************************************************************************************
	 * SURCHARGE : SUPPRESSION DE CATEGORIE
	 *******************************************************************************************/
	public function delete()
	{
		if($this->deleteRight()){
			Db::query("UPDATE ap_calendarEvent SET _idCat=null WHERE _idCat=".$this->_id);
			parent::delete();
		}
	}
}