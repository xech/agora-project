<?php
/**
* This file is part of the Agora-Project Software package.
*
* @copyright (c) Agora-Project Limited <https://www.agora-project.net>
* @license GNU General Public License, version 2 (GPL-2.0)
*/


/*
 * CLASSE DES OBJETS DE TYPE "CATEGORY"  :  CATEGORIES D'EVT / THEMES DE SUJET / COLONNES KANBAN
 */
 class MdlObjectCategory extends MdlObject
{
	/*******************************************************************************************
	 * SURCHARGE : CONSTRUCTEUR
	 *******************************************************************************************/
	function __construct($objIdOrValues=null)
	{
		//Appel du constructeur parent
		parent::__construct($objIdOrValues);
		//Espaces où l'objet est visible
		$this->spaceIds=Txt::txt2tab($this->_idSpaces);
		//Couleur par défaut
		if(empty($this->color))  {$this->color="#777";}
	}

	/*******************************************************************************************
	 * TITRE ET COULEUR DE L'OBJET
	 *******************************************************************************************/
	public function getLabel()
	{
		return '<div class="categoryColor" style="background:'.$this->color.'">&nbsp;</div> '.$this->title;
	}

	/*******************************************************************************************
	 * URL D'EDITION DES OBJETS
	 *******************************************************************************************/
	public static function getUrlEditObjects()
	{
		return "?ctrl=object&action=EditCategories&objectType=".static::objectType;
	}

	/*******************************************************************************************
	 * LISTE DES CATEGORIES A AFFICHER
	 *******************************************************************************************/
	public static function getList($editCategories=false, $_idCategory=null)
	{
		//Liste des categories
		$sqlFilter=($editCategories==true && Ctrl::$curUser->isGeneralAdmin())  ?  null  :  " WHERE _idSpaces IS NULL OR _idSpaces LIKE '%@".Ctrl::$curSpace->_id."@%'";	//Filtré par espace si $editCategories==false
		if(!empty($sqlFilter) && !empty($_idCategory))  {$sqlFilter.="OR _id=".(int)$_idCategory;}																			//Spécifie une catégorie (cf. "selectInput()")
		$objList=Db::getObjTab(static::objectType, "SELECT * FROM ".static::dbTable." ".$sqlFilter." ORDER BY `rank`, `title`");
		//Edition des categories : vérif le droit d'édition à chacune
		if($editCategories==true){
			foreach($objList as $tmpKey=>$tmpObj){
				if($tmpObj->editRight()==false)  {unset($objList[$tmpKey]);}
			}
		}
		//Renvoie la liste
		return $objList;
	}

	/*******************************************************************************************
	 * INPUT SELECT POUR L'EDITION D'UN OBJET
	 *******************************************************************************************/
	public static function selectInput($_idCategory)
	{
		//Affiche un input <select> pour définir la catégorie du $curObj
		$categoryList=static::getList(false,$_idCategory);
		if(!empty($categoryList)){
			$vDatas["categoryList"]=$categoryList;																//Liste des <option>
			$vDatas["_idFieldName"]=static::_idFieldName;														//Nom du champ dans la table de l'objet modifié (_idStatus, _idCategory, _idTheme, etc)
			$vDatas["_idCategory"]=$_idCategory;																//_id de la catégorie correspondant à l'objet modifié (idem)
			$vDatas["tradCategoryUndefined"]=Txt::trad(strtoupper(static::moduleName)."_categoryUndefined");	//Ex: "FORUM_categoryUndefined"=>"Sans Theme" 
			return Ctrl::getVue(Req::commonPath."VueObjEditCategorySelect.php",$vDatas);
		}
	}

	/*******************************************************************************************
	 * DROIT D'AJOUTER UN NOUVEL OBJET : TOUS LES USERS  /  ADMIN UNIQUEMENT SI L'OPTION ACTIVÉE
	 *******************************************************************************************/
	public static function addRight()
	{
		return (Ctrl::$curUser->isSpaceAdmin() || (Ctrl::$curUser->isUser() && Ctrl::$curSpace->moduleOptionEnabled(static::moduleName,static::optionAdminAddCategory)==false));
	}

	/*******************************************************************************************
	 * SURCHARGE : SUPPRESSION DE L'OBJET
	 *******************************************************************************************/
	public function delete()
	{
		if($this->deleteRight()){
			Db::query("UPDATE ".static::dbTableParent." SET ".static::_idFieldName."=NULL WHERE ".static::_idFieldName."=".$this->_id);
			parent::delete();
		}
	}
}