<?php
/**
* This file is part of the Agora-Project Software package
*
* @copyleft Agora-Project <https://www.agora-project.net>
* @license GNU General Public License (GPL-2.0)
*/


/*
 * CLASSE DES OBJETS DE TYPE "CATEGORY"  :  CATEGORIES D'EVT / THEMES DE SUJET / COLONNES KANBAN
 */
 class MdlCategory extends MdlObject
{
	const dbParentTable=null;
	const dbParentField=null;
	public static $requiredFields=["title"];
	public static $sortFields=["title@asc","title@desc"];

	/********************************************************************************************************
	 * SURCHARGE : CONSTRUCTEUR
	 ********************************************************************************************************/
	function __construct($objIdOrValues=null)
	{
		parent::__construct($objIdOrValues);
		$this->spaceIds=Txt::txt2tab($this->_idSpaces);	//Espaces où l'objet est visible
		if(empty($this->color))  {$this->color="#555";}	//Couleur par défaut
	}

	/********************************************************************************************************
	 * TITRE ET COULEUR DE L'OBJET
	 ********************************************************************************************************/
	public function getLabel()
	{
		if($this->allCategories==true)	{return '<span class="categoryColor categoryColorAll">&nbsp;</span>'.Txt::trad(static::tradPrefix."_CAT_showAll");}
		else							{return '<span class="categoryColor" style="background:'.$this->color.'">&nbsp;</span>'.ucfirst($this->title);}
	}

	/********************************************************************************************************
	 * LISTE DES CATEGORIES A AFFICHER
	 * $mode : "display" / "select" / "edit"
	 ********************************************************************************************************/
	public static function catList($mode="display", $_idCategory=null)
	{
		//// Liste des categories
		$sqlFilter=($mode=="edit" && Ctrl::$curUser->isGeneralAdmin())  ?  null  :  " WHERE _idSpaces IS NULL OR _idSpaces LIKE '%@".Ctrl::$curSpace->_id."@%'";	//Sélection filtrée par espace ?
		if($mode=="select" && !empty($_idCategory))  {$sqlFilter.="OR _id=".(int)$_idCategory;}																		//Spécifie la catégorie d'un objet en cours d'édition, avec affichage de "selectInput()"
		$catList=Db::getObjTab(static::objectType, "SELECT * FROM ".static::dbTable." ".$sqlFilter." ORDER BY `rank`, `title`");
		//// Edition des categories : vérif le droit d'édition à chacune
		if($mode=="edit"){
			foreach($catList as $tmpKey=>$tmpObj){
				if($tmpObj->editRight()==false)  {unset($catList[$tmpKey]);}
			}
			array_push($catList, new static(["_id"=>0]));
		}
		//// Menu de filtrage par categorie : ajoute le pseudo theme "allCategories" en début de liste
		elseif($mode=="display"){
			array_unshift($catList, new static(["allCategories"=>true]));
		}
		//// Renvoie la liste des catégories
		return $catList;
	}

	/********************************************************************************************************
	 * SQL : INIT LE FILTRE DES CATEGORIES ET RENVOIE LA SELECTION SQL
	 ********************************************************************************************************/
	public static function sqlCategoryFilter()
	{
		//Initialise le filtre des catégories : cf. "VueCategoryMenu.php"  (static::objectType : "forumTheme", "taskStatus", etc)
		if(Req::isParam("_idCategoryFilter"))								{$_SESSION["_idCategoryFilter"][static::objectType]=Req::param("_idCategoryFilter");}
		elseif(empty($_SESSION["_idCategoryFilter"][static::objectType]))	{$_SESSION["_idCategoryFilter"][static::objectType]=null;}
		//Renvoie le filtre SQL (avec un espace avant/après !) Exple:  " AND `_idTheme`=55 "
		if(!empty($_SESSION["_idCategoryFilter"][static::objectType]))	{return " AND `".static::dbParentField."`=".$_SESSION["_idCategoryFilter"][static::objectType]." ";}
	}

	/********************************************************************************************************
	 * VUE : MENU POUR FILTRER L'AFFICHAGE PAR CATEGORIE
	 ********************************************************************************************************/
	public static function displayMenu()
	{
		$vDatas["tradPrefix"]=static::tradPrefix;
		$vDatas["categoryList"]=static::catList();
		$vDatas["_idCategoryFilter"]=(!empty($_SESSION["_idCategoryFilter"][static::objectType]))  ?  $_SESSION["_idCategoryFilter"][static::objectType]  :  null;	//catégorie affichée
		if(static::addRight())	{$vDatas["urlEditObjects"]="?ctrl=object&action=EditCategories&objectType=".static::objectType;}									
		return Ctrl::getVue(Req::commonPath."VueCategoryMenu.php",$vDatas);
	}

	/********************************************************************************************************
	 * INPUT <SELECT> DE LA CATEGORIE : FORMULAIRE D'ÉDITION D'UN OBJET (TASK, EVT, ETC)
	 ********************************************************************************************************/
	public static function selectInput($_idCategory)
	{
		//Affiche un input <select> pour définir la catégorie du $curObj
		$categoryList=static::catList("select",$_idCategory);
		if(!empty($categoryList)){
			$vDatas["tradPrefix"]=static::tradPrefix;
			$vDatas["categoryList"]=$categoryList;
			$vDatas["dbParentField"]=static::dbParentField;		//Champ de la catégorie, dans la table de l'objet parent : _idStatus, _idCategory, _idTheme, etc
			$vDatas["_idCategory"]=(!empty($_SESSION["_idCategoryFilter"][static::objectType]))  ?  $_SESSION["_idCategoryFilter"][static::objectType]  :  $_idCategory;	//cat. affichée  || cat. de l'objet édité
			return Ctrl::getVue(Req::commonPath."VueCategorySelect.php",$vDatas);
		}
	}

	/********************************************************************************************************
	 * DROIT D'AJOUTER UNE NOUVELLE CATEGORIE : ADMIN / USERS + OPTION ACTIVÉE
	 ********************************************************************************************************/
	public static function addRight()
	{
		return (Ctrl::$curUser->isSpaceAdmin() || (Ctrl::$curUser->isUser() && Ctrl::$curSpace->moduleOptionEnabled(static::moduleName,static::optionAdminAddCategory)==false));
	}

	/********************************************************************************************************
	 * SURCHARGE : SUPPRESSION DE L'OBJET
	 ********************************************************************************************************/
	public function delete()
	{
		if($this->deleteRight()){
			Db::query("UPDATE ".static::dbParentTable." SET ".static::dbParentField."=NULL WHERE ".static::dbParentField."=".$this->_id);
			parent::delete();
		}
	}
}