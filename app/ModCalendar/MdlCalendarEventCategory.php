<?php
/**
* This file is part of the Agora-Project Software package.
*
* @copyright (c) Agora-Project Limited <https://www.agora-project.net>
* @license GNU General Public License, version 2 (GPL-2.0)
*/


/*
 * Modele des categories d'evenements
 */
class MdlCalendarEventCategory extends MdlObject
{
	const moduleName="calendar";
	const objectType="calendarEventCategory";
	const dbTable="ap_calendarEventCategory";
	public static $requiredFields=array("title");
	public static $sortFields=array("title@asc","title@desc");

	/*
	 * SURCHARGE : Constructeur
	 */
	function __construct($objIdOrValues=null)
	{
		parent::__construct($objIdOrValues);
		//Espaces ou est visible la categorie
		$this->spaceIds=Txt::txt2tab($this->_idSpaces);
		//Couleur par défaut
		if(empty($this->color))  {$this->color="#900";}
	}
	
	/*
	 * Affiche la categorie avec une pastille de couleur
	 */
	public function display()
	{
		if(!empty($this->title))	{return "<div class='categoryColor' style=\"background:".$this->color."\">&nbsp;</div> ".$this->title;}
	}

	/*
	 * Categories d'evenements (Filtre par espace?)
	 */
	public static function getCategories($editMode=false)
	{
		$sqlFilter=($editMode==true && Ctrl::$curUser->isAdminGeneral())  ?  null  :  " AND (_idSpaces is null OR _idSpaces LIKE '%@".Ctrl::$curSpace->_id."@%')";
		return Db::getObjTab(static::objectType, "SELECT * FROM ".self::dbTable." WHERE 1 ".$sqlFilter." ORDER BY title");
	}

	/*
	 * Droit d'ajouter une nouvelle categorie
	 */
	public static function addRight()
	{
		return (Ctrl::$curUser->isAdminSpace() || (Ctrl::$curUser->isUser() && Ctrl::$curSpace->moduleOptionEnabled(self::moduleName,"adminAddCategory")==false));
	}
	
	/*
	 * SURCHARGE : suppression de categorie
	 */
	public function delete()
	{
		if($this->deleteRight()){
			Db::query("UPDATE ap_calendarEvent SET _idCat=null WHERE _idCat=".$this->_id);
			parent::delete();
		}
	}
}