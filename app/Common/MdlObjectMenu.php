<?php
/**
* This file is part of the Agora-Project Software package
*
* @copyleft Agora-Project <https://www.agora-project.net>
* @license GNU General Public License (GPL-2.0)
*/


/*
 * Classe des Objects => Menus des Objects
 */
trait MdlObjectMenu
{
	public static $nbObjsPerPage=30;	//Nb d'éléments affichés par page : 50 par défaut
	public static $displayMode=null;	//Type d'affichage en préference (ligne/block)

	/********************************************************************************************************
	 * IDENTIFIANT UNIQUE DE L'OBJET : CONTENEUR DE L'OBJET, MENU CONTEXTUEL, ETC
	 ********************************************************************************************************/
	public function uniqId($prefix)
	{
		if(empty($this->objUniqId))  {$this->objUniqId=uniqid();}	//Un seul ID par instance de l'objet (tester avec les événements périodiques de l'agenda)
		return $prefix.$this->objUniqId;							//Retourne l'id avec un prefix : "objContainer", "objMenu", "objCheckbox", "objAttachment", etc
	}

	/*******************************************************************************************************************************
	 * DIV PRINCIPAL (".objContainer")  &&  MENU CONTEXTUEL
	 * objMenu 	: id du menu contextuel via click droit et "menuContext()"
	 *******************************************************************************************************************************/
	public function objContainerMenu($classes=null, $attributes=null, $menuOptions=null)
	{
		$classes='objContainer '.$classes.' '.($this->isSelectable()?'isSelectable':null);		//Classe de base + Classe en paramètre + classe des objets sélectionnables
		if($this->editRight())  {$attributes.=' data-urlEdit="'.$this->getUrl("edit").'"';}		//Url d'édition via "dblClick"
		return  '<div id="'.$this->uniqId("objContainer").'" class="'.$classes.'" for="'.$this->uniqId("objMenu").'" data-typeId="'.$this->_typeId.'" '.$attributes.'>'.
					$this->contextMenu($menuOptions);
	}

	/*****************************************************************************************************************************************************************************************************
	 * VUE : MENU CONTEXTUEL (édition, droit d'accès, etc)
	 * $options["launcherIcon"]		: "floatBig" (par défaut) / "floatSmall" / "inlineBig" / "inlineSmall"
	 * $options["deleteLabel"]		: Label spécifique de suppression
	 * $options["specificOptions"]	: Boutons à ajouter au menu, chaque bouton ayant les propriétés  ["actionJs"=>"onclick=xxx", "iconSrc"=>"option.png", "label"=>"mon label", "tooltip"=>"mon tooltip"]
	 * $options["specificLabels"]	: Texte à afficher (ex: agendas affectés à un evenement)
	 *****************************************************************************************************************************************************************************************************/
	public function contextMenu($options=null)
	{
		////	PAS DE MENU POUR LES GUESTS
		if(Ctrl::$curUser->isGuest())  {return false;}
		////	INIT  &  DIVERSES OPTIONS
		$vDatas["curObj"]=$this;
		$vDatas["objMenuId"]=$this->uniqId("objMenu");
		$vDatas["options"]=$options;

		////	OBJET USER
		if(static::objectType=="user")
		{
			////	RETOURNE "FALSE" SI ON EST PAS PROPRIO DE L'OBJET NI ADMIN D'ESPACE
			if($this->isAutor()==false && Ctrl::$curUser->isSpaceAdmin()==false)  {return false;}
			////	MODIFIER L'OBJET  &  MODIF MESSENGER
			if($this->editRight()){
				$vDatas["editLabel"]=Txt::trad("USER_profilEdit");
				$vDatas["userEditMessengerUrl"]="?ctrl=user&action=userEditMessenger&typeId=".$this->_typeId;
			}
			////	SUPPRIMER L'AFFECTATION DE L'ESPACE COURANT
			if($this->deleteFromCurSpaceRight()){
				$vDatas["deleteFromCurSpaceConfirm"]="confirmRedir('?ctrl=user&action=deleteFromCurSpace&objectsTypeId[".static::objectType."]=".$this->_id."', '".Txt::trad("USER_deleteFromCurSpaceConfirm",true)."')";
			}
			////	SUPPRESSION DEFINITIVE
			if($this->deleteRight()){
				$vDatas["confirmDeleteJs"]="confirmDelete('".$this->getUrl("delete")."',$('#".$this->uniqId("objLabel")."').text())";
				$vDatas["deleteLabel"]=Txt::trad("USER_deleteDefinitely");
			}
			////	LISTE DES ESPACES DE L'UTILISATEUR (..pas ceux de $curUser)
			if(Ctrl::$curUser->isGeneralAdmin()){
				$vDatas["userSpaceList"]=Txt::trad("USER_spaceList")." : ";
				if(count($this->spaceList())==0)	{$vDatas["userSpaceList"].=Txt::trad("USER_spaceNoAffectation");}
				else								{ foreach($this->spaceList() as $tmpSpace)  {$vDatas["userSpaceList"].="<br>".$tmpSpace->name;} }
			}
		}
		////	OBJET LAMBDA
		else
		{
			////	MODIFIER L'OBJET  &  LOGS/HISTORIQUE  &  DEPLACER L'OBJET DANS UN AUTRE DOSSIER (si ya pas que le dossier racine)
			if($this->editRight()){
				$vDatas["editLabel"]=($this->hasAccessRight())  ?  Txt::trad("modifyAndAccesRight")  :  Txt::trad("modify");
				$vDatas["logUrl"]="?ctrl=object&action=logs&typeId=".$this->_typeId;
				if(!empty(Ctrl::$curRootFolder) && count(Ctrl::$curRootFolder->folderTree())>1)  {$vDatas["moveObjectUrl"]="?ctrl=object&action=FolderMove&typeId=".$this->containerObj()->_typeId."&objectsTypeId[".static::objectType."]=".$this->_id;}
			}
			////	URL D'ACCES EXTERNE
			if(Ctrl::$curUser->isUser() && static::objectType!="space")  {$vDatas["getUrlExternal"]=$this->getUrlExternal();}
			////	AUTEUR/DATE DE CREATION/MODIF
			$vDatas["autorDateCrea"] =(!empty($this->dateCrea))   ?  $this->autorDate()  :  null;
			$vDatas["autorDateModif"]=(!empty($this->dateModif))  ?  $this->autorDate(true)  :  null;
			////	SUPPRIMER
			if($this->deleteRight()){
				$ajaxControlUrl =(static::isFolder==true)  ?  "'?ctrl=object&action=folderDeleteControl&typeId=".$this->_typeId."'"  :  "null";	//Controle d'accès aux sous-dossiers
				$vDatas["confirmDeleteJs"]="confirmDelete('".$this->getUrl("delete")."', $('#".$this->uniqId("objLabel")."').text(), ".$ajaxControlUrl.")";
				$vDatas["deleteLabel"]=(!empty($options["deleteLabel"]))  ?  $options["deleteLabel"]  :  Txt::trad("delete");
			}
			////	LIBELLES DES DROITS D'ACCESS : AFFECTATION AUX ESPACES, USERS, ETC  (droit d'accès de l'objet OU du conteneur d'un objet)
			if($this->hasAccessRight() || $this->hasContainerAccessRight())
			{
				//Récupère les affectations (de l'objet OU de son conteneur)  &&  Ajoute le label des affectations pour chaque type de droit d'accès (lecture/ecriture limité/ecriture)
				$objAffects=($this->hasAccessRight())  ?  $this->getAffectations()  :  $this->containerObj()->getAffectations();
				$vDatas["affectLabels"]=$vDatas["affectTooltips"]=["1"=>null,"1.5"=>null,"2"=>null];
				foreach($objAffects as $tmpAffect)  {$vDatas["affectLabels"][(string)$tmpAffect["accessRight"]].=$tmpAffect["label"]."<br>";}
				//Affiche si l'objet est personnel ("isPersoAccess")
				$firstAffect=reset($objAffects);//Récup la première affectation du tableau
				$vDatas["isPersoAccess"]=(count($objAffects)==1 && $firstAffect["targetType"]=="user" && $firstAffect["targetId"]==Ctrl::$curUser->_id);
				//Tooltip spécifique
				if(static::isContainer())  					{$tooltipDetail=$this->tradObject("accessAutorPrivilege")."<hr>";}					//"Seul l'auteur ou l'admin peuvent modifier/supprimer le -dossier-"
				elseif($this->hasContainerAccessRight())	{$tooltipDetail=$this->containerObj()->tradObject("accessRightsInherited")."<hr>";}	//"Droits d'accès hérité du -dossier- parent"
				else										{$tooltipDetail=null;}
				//Tooltip : description de chaque droit d'accès
				if(!empty($vDatas["affectLabels"]["1"]))	{$vDatas["affectTooltips"]["1"]=$tooltipDetail.Txt::trad("accessReadTooltip");}
				if(!empty($vDatas["affectLabels"]["1.5"]))	{$vDatas["affectTooltips"]["1.5"]=$tooltipDetail.$this->tradObject("accessWriteLimitTooltip");}
				if(!empty($vDatas["affectLabels"]["2"]))	{$vDatas["affectTooltips"]["2"]=(static::isContainer())  ?  $tooltipDetail.$this->tradObject("accessWriteTooltipContainer")  :  $tooltipDetail.Txt::trad("accessWriteTooltip");}
			}
		}
		////	Affichage
		return Ctrl::getVue(Req::commonPath."VueObjMenuContext.php",$vDatas);
	}

	/********************************************************************************************************
	 * BOUTON D'EDITION D'UN OBJET 
	 ********************************************************************************************************/
	public function editButtom()
	{
		if($this->editRight())  {return '<img src="app/img/edit.png" class="editButton" onclick="lightboxOpen(\''.$this->getUrl("edit").'\')" '.Txt::tooltip("modify").'>';}
	}

	/********************************************************************************************************
	 * VUE D'UN OBJET : MENU CONTEXTUEL + BOUTON D'EDITION
	 ********************************************************************************************************/
	public function lightboxMenu()
	{
		return '<span class="lightboxMenu">'.$this->contextMenu(["launcherIcon"=>"inlineBig"]).$this->editButtom().'</span>';
	}

	/********************************************************************************************************
	 * VUE : TITRE DE L'OBJET SUR MOBILE (Ex: "Nouveau dossier", "Mon dossier")
	 ********************************************************************************************************/
	public function titleMobile($keyTrad)
	{
		if(Req::isMobile())  {echo '<div class="lightboxTitle">'.($this->isNew() ? Txt::trad($keyTrad) : $this->getLabel()).'</div>';}
	}

	/********************************************************************************************************
	 * VUE : ÉDITION DE LA DESCRIPTION : AVEC L'EDITEUR TINYMCE ?
	 ********************************************************************************************************/
	public function descriptionEditor($toggleButton=true)
	{
		$vDatas["curObj"]=$this;
		$vDatas["toggleButton"]=$toggleButton;
		//Sélectionne au besoin le "draftTypeId" pour n'afficher que le brouillon/draft de l'objet précédement édité (on n'utilise pas "editTypeId" car il est effacé dès qu'on sort de l'édition de l'objet...)
		$sqlTypeId=Req::isParam("typeId")  ?  "draftTypeId=".Db::param("typeId")  :  "draftTypeId IS NULL";
		$vDatas["editorDraft"]=(string)Db::getVal("SELECT editorDraft FROM ap_userLivecouter WHERE _idUser=".Ctrl::$curUser->_id." AND ".$sqlTypeId);
		//Affiche la vue
		return Ctrl::getVue(Req::commonPath."VueObjEditor.php",$vDatas);
	}

	/********************************************************************************************************
	 * VUE : MENU D'ÉDITION PRINCIPAL (droits d'accès, fichiers joints, etc)
	 ********************************************************************************************************/
	public function editMenuSubmit()
	{
		////	Menu des droits d'accès
		if($this->hasAccessRight())
		{
			////	Init & Labels
			$vDatas["objMenuAccessRight"]=true;
			$vDatas["objMenuAccessRightLabel"]=(static::isContainer())  ?  '<span title="'.$this->tradObject("accessAutorPrivilege").'<hr>'.$this->tradObject("accessWriteLimitTooltip").'">'.Txt::trad("EDIT_accessRightContent").' <img src="app/img/info.png"></span>'  :  Txt::trad("EDIT_accessRight");
			$vDatas["accessWriteLimitTooltip"]=$this->tradObject("accessWriteLimitTooltip");
			$vDatas["extendToSubfolders"]=(static::isFolder==true && $this->isNew()==false && Db::getVal("SELECT count(*) FROM ".static::dbTable." WHERE _idContainer=".$this->_id)>0);//dossier avec des sous-dossiers
			////	Droits d'accès pour chaque espace ("targets")
			$vDatas["accessRightSpaces"]=[];
			foreach(Ctrl::$curUser->spaceList() as $tmpSpace){
				//// Verif si le module de l'objet est bien activé sur l'espace
				if(array_key_exists(static::moduleName,$tmpSpace->moduleList())){
					////	Init les "targetLines"
					$tmpSpace->targetLines=[];
					////	"Tous les utilisateurs"  OU  "Tous les utilisateurs et invités"
					if(empty($tmpSpace->public))	{$allUsersLabel=Txt::trad("EDIT_allUsers");				$allUsersLabelInfo=Txt::trad("EDIT_allUsersTooltip");}
					else							{$allUsersLabel=Txt::trad("EDIT_allUsersAndGuests");	$allUsersLabelInfo=Txt::trad("EDIT_allUsersAndGuestsTooltip");}
					$tmpSpace->targetLines[]=["targetId"=>$tmpSpace->_id."_spaceUsers", "label"=>$allUsersLabel, "icon"=>"user/accessAll.png", "tooltip"=>str_replace("--SPACENAME--",$tmpSpace->name,$allUsersLabelInfo)];
					////	Groupe d'utilisateurs de l'espace
					foreach(MdlUserGroup::getGroups($tmpSpace) as $tmpGroup){
						$tmpSpace->targetLines[]=["targetId"=>$tmpSpace->_id."_G".$tmpGroup->_id, "label"=>$tmpGroup->title, "icon"=>"user/accessGroup.png", "tooltip"=>Txt::reduce($tmpGroup->usersLabel)];
					}
					////	Chaque user de l'espace
					foreach($tmpSpace->getUsers() as $tmpUser){
						if($tmpSpace->accessRightUser($tmpUser)==2)	{$tmpUserFullAccess=true;	$tmpUserIcon="user/accessUserAdmin.png";	$tmpUserTooltip=Txt::trad("EDIT_adminSpace");}	//Admin d'espace
						else										{$tmpUserFullAccess=false;	$tmpUserIcon="user/accessUser.png";			$tmpUserTooltip=null;}							//User lambda
						$tmpSpace->targetLines[]=["targetId"=>$tmpSpace->_id."_U".$tmpUser->_id, "label"=>$tmpUser->getLabel(), "icon"=>$tmpUserIcon, "tooltip"=>$tmpUserTooltip, "onlyFullAccess"=>$tmpUserFullAccess, "isUser"=>true];
					}
					////	Ajoute l'espace
					$vDatas["accessRightSpaces"][]=$tmpSpace;
				}
			}
			////	Prépare les affectations possibles de chaque espace (targets)
			$objAffects=$this->getAffectations();
			foreach($vDatas["accessRightSpaces"] as $tmpSpaceKey=>$tmpSpace)
			{
				foreach($tmpSpace->targetLines as $targetKey=>$targetLine)
				{
					//Init les propriétés des checkboxes (pas de "class"!). Utilise des "id" pour une sélection rapide des checkboxes par jQuery
					$targetId=$targetLine["targetId"];//exple : "1_spaceUsers" ou "2_G4
					foreach(["1","1.5","2"] as $tmpRight)
						{$targetLine["boxProp"][$tmpRight]="value=\"".$targetId."_".$tmpRight."\"  id=\"objectRightBox_".$targetId."_".str_replace('.','',$tmpRight)."\"";}//"_15" au lieu de "_1.5" à cause du selector jQuery
					//Check une des boxes ?
					if(isset($objAffects[$targetId])){
						$tmpRight=(string)$objAffects[$targetId]["accessRight"];//Toujours typer les index en 'string', pas en 'float'
						$targetLine["boxProp"][$tmpRight].=" checked";
						$targetLine["isChecked"]=true;
					}
					//Donne uniquement accès à la checkbox "write" (cf. administrateurs)
					if(!empty($targetLine["onlyFullAccess"]))	{$targetLine["boxProp"]["1"].=" disabled";  $targetLine["boxProp"]["1.5"].=" disabled";}
					//Met à jour les propriétés de la target ($targetKey est la concaténation des champs "_idSpace" et "target")
					$vDatas["accessRightSpaces"][$tmpSpaceKey]->targetLines[$targetKey]=$targetLine;
				}
			}
		}
		////	OPTIONS NOTIFICATION PAR MAIL
		if(static::hasNotifMail==true && Tool::mailEnabled()){
			$vDatas["objMenuNotifMail"]=true;
			$vDatas["notifMailUsers"]=Ctrl::$curUser->usersVisibles(true);
			$vDatas["curSpaceUsersIds"]=Ctrl::$curSpace->getUsers("idsTab");
			$vDatas["curSpaceUserGroups"]=MdlUserGroup::getGroups(Ctrl::$curSpace);
		}
		////	OPTION "FICHIERS JOINTS"
		if(static::hasAttachedFiles==true){
			$vDatas["objMenuAttachedFile"]=true;
		}
		////	OPTION "SHORTCUT"
		if(static::hasShortcut==true){
			$vDatas["objMenuShortcut"]=true;
		}
		////	AFFICHE LA VUE
		$vDatas["curObj"]=$this;
		return Ctrl::getVue(Req::commonPath."VueObjMenuEdit.php",$vDatas);
	}

	/********************************************************************************************************
	 * VUE : MENU DE SÉLECTION D'OBJETS
	 ********************************************************************************************************/
	public static function menuSelect()
	{
		////	Déplacer dans un autre dossier (2 dossiers ou + dans l'arbo)  ||  Supprimer des objets  ||  Supprimer des users de l'espace
		if(static::menuSelectDisplay()){
			$vDatas["folderMoveOption"]		=(is_object(Ctrl::$curRootFolder) && Ctrl::$curContainer->editContentRight() && count(Ctrl::$curRootFolder->folderTree())>=2);
			$vDatas["deleteOption"]			=((is_object(Ctrl::$curContainer) && Ctrl::$curContainer->editContentRight())  ||  (Req::$curCtrl=="forum" && Ctrl::$curUser->isUser())  ||  (Req::$curCtrl=="user" && Ctrl::$curUser->isGeneralAdmin()));
			$vDatas["deleteFromSpaceOption"]=(Req::$curCtrl=="user" && Ctrl::$curUser->isSpaceAdmin() && Ctrl::$curSpace->allUsersAffected()==false);
			return Ctrl::getVue(Req::commonPath."VueObjMenuSelect.php",$vDatas);
		}
	}

	/********************************************************************************************************
	 * STATIC : AFFICHE LE MENU DE SÉLECTION D'OBJETS ?
	 ********************************************************************************************************/
	public static function menuSelectDisplay()
	{
		//Type d'objet sélectionnable + Mode desktop + User identifié
		return (static::isSelectable && Req::isMobile()==false && Ctrl::$curUser->isUser());
	}

	/********************************************************************************************************
	 * L'OBJET AFFICHÉ EST-IL SELECTIONNABLE ?
	 ********************************************************************************************************/
	public function isSelectable()
	{
		//Menu de sélection d'objets affiché  +  l'objet n'est pas le conteneur/dossier courant (cf. ".pathMenu")
		return (static::menuSelectDisplay()  &&  (empty(Ctrl::$curContainer) || Ctrl::$curContainer->_typeId!=$this->_typeId));
	}

	/********************************************************************************************************
	 * VUE : MENU DE TRI D'UN TYPE D'OBJET
	 ********************************************************************************************************/
	public static function menuSort($addUrlParams=null)
	{
		$vDatas["curSort"]=self::getSort();
		$vDatas["objectType"]=static::objectType;
		$curSortTab=Txt::txt2tab($vDatas["curSort"]);
		$vDatas["curSortField"]=$curSortTab[0];
		$vDatas["curSortValue"]=$curSortTab[1];
		$vDatas["addUrlParams"]=$addUrlParams;
		$vDatas["sortFields"]=static::$sortFields;
		return Ctrl::getVue(Req::commonPath."VueObjMenuSort.php",$vDatas);
	}

	/********************************************************************************************************
	 * TRI D'OBJETS : EN PREFERENCE / PAR DEFAUT (ex: "firstName@@asc")
	 ********************************************************************************************************/
	private static function getSort()
	{
		$prefSuffix=(Ctrl::$curContainer)  ?  Ctrl::$curContainer->_typeId  :  static::objectType;	//Suffixe de préférence (ex: "sort_fileFolder-55")
		$objectsSort=Ctrl::getPref("sort",$prefSuffix);												//Préférence de tri
		if(empty($objectsSort) || !in_array($objectsSort,static::$sortFields))						//Aucune préférence OU Tri disponible :
			{$objectsSort=static::$sortFields[0];}													//Récupère le 1er tri disponible
		return $objectsSort;
	}

	/********************************************************************************************************
	 * TRI SQL DES OBJETS  (avec un 1er tri au besoin : news, subject, etc. Ex: "ORDER BY firstName asc")
	 ********************************************************************************************************/
	public static function sqlSort($firstSort=null)
	{
		$firstSort=(!empty($firstSort))  ?  $firstSort.", "  :  null;										//Pré-tri ? Exple pour les News: "une desc"
		$sortTab=Txt::txt2tab(self::getSort());																//Récupère la préférence de tri du conteneur courant (dossier/sujet/etc). Ex: ["name","asc"]
		$fieldSort=($sortTab[0]=="extension")  ?  "SUBSTRING_INDEX(`name`,'.',-1)"  :  "`".$sortTab[0]."`";	//Tri par type de fichier  : récupère son extension pour le tri 
		return " ORDER BY ".$firstSort." ".$fieldSort." ".$sortTab[1]." ";									//Renvoie le tri Sql (avec des espaces avant/après)
	}

	/********************************************************************************************************
	 * VUE : MENU DU MODE D'AFFICHAGE DES OBJETS DANS UNE PAGE : BLOCKS / LIGNES (cf. $displayModes)
	 ********************************************************************************************************/
	public static function menuDisplayMode()
	{
		if(static::isMobileDisplayBlock()==false){
			$vDatas["displayModes"]=static::$displayModes;
			$vDatas["curDisplayMode"]=static::getDisplayMode();
			$vDatas["displayModeUrl"]=Tool::getParamsUrl("displayMode")."&displayMode=";
			return Ctrl::getVue(Req::commonPath."VueObjMenuDisplayMode.php",$vDatas);
		}
	}

	/********************************************************************************************************
	 * MODE D'AFFICHAGE DES OBJETS : EN PREFERENCE / PAR DEFAUT ("block"/"line"/etc)
	 ********************************************************************************************************/
	public static function getDisplayMode()
	{
		if(static::$displayMode===null){
			$prefSuffix=(Ctrl::$curContainer)  ?  Ctrl::$curContainer->_typeId  :  static::objectType;										//Suffixe de préférence (ex: "displayMode_fileFolder-55")
			static::$displayMode=static::isMobileDisplayBlock()  ?  "block"  :  Ctrl::getPref("displayMode",$prefSuffix);					//Affichage "block" sur mobile  ||  Préférence d'affichage
			if(empty(static::$displayMode)){																								//Aucune préférence -> Affichage par défaut :
				$folderDisplayMode=(Ctrl::$agora->folderDisplayMode && in_array(Ctrl::$agora->folderDisplayMode,static::$displayModes));	//Affichage des dossiers du paramétrage général || 1er Affichage disponible
				static::$displayMode=($folderDisplayMode==true)  ?  Ctrl::$agora->folderDisplayMode  :  static::$displayModes[0];
			}
		}
		return static::$displayMode;
	}

	/********************************************************************************************************
	 * STATIC : SUR MOBILE, ON AFFICHE TOUJOURS EN MODE "BLOCK" (SI DISPO)
	 ********************************************************************************************************/
	public static function isMobileDisplayBlock()
	{
		return (Req::isMobile() && in_array("block",static::$displayModes));
	}

	/********************************************************************************************************
	 * VUE : MENU DE FILTRE ALPHABÉTIQUE
	 ********************************************************************************************************/
	public static function menuPagination($objNbDisplayed, $paramKey=null)
	{
		$vDatas["pageNbTotal"]=ceil($objNbDisplayed / static::$nbObjsPerPage);								//Nombre de pages au total 
		if($vDatas["pageNbTotal"]>1){																		//Affiche le menu s'il ya + d'une page
			$vDatas["pageNbCur"]=Req::isParam("pageNb") ? (int)Req::param("pageNb") : 1;					//Page courante
			$vDatas["pageUrl"]="?ctrl=".Req::$curCtrl;														//Url de redirection de base
			if(Req::isParam($paramKey))  {$vDatas["pageUrl"].="&".$paramKey."=".Req::param($paramKey);}		//Ajoute un parametre dans l'url
			$vDatas["pageUrl"].="&pageNb=";																	//Termine par le parametre "pageNb"
			$vDatas["pageUrlPrev"]=($vDatas["pageNbCur"] > 1) ?  						'href="'.$vDatas["pageUrl"].($vDatas["pageNbCur"]-1).'"'  :  'class="vMenuPageDisabled"';//Page Précédente : url / disabled
			$vDatas["pageUrlNext"]=($vDatas["pageNbCur"] < $vDatas["pageNbTotal"]) ?	'href="'.$vDatas["pageUrl"].($vDatas["pageNbCur"]+1).'"'  :  'class="vMenuPageDisabled"';//Page Suivante : idem
			return Ctrl::getVue(Req::commonPath."VueObjMenuPagination.php",$vDatas);
		}
	}

	/********************************************************************************************************
	 * STATIC SQL : FILTRE DE PAGINATION
	 ********************************************************************************************************/
	public static function sqlPagination()
	{
		$offset=Req::isParam("pageNb")  ?  ((Req::param("pageNb")-1)*static::$nbObjsPerPage)  :  "0";
		return "LIMIT ".static::$nbObjsPerPage." OFFSET ".$offset;
	}

	/********************************************************************************************************
	 * MENU DE SÉLECTION DE LA LANGUE
	 ********************************************************************************************************/
	public static function selectTrad($typeConfig, $selectedLang=null)
	{
		// Langue "francais" par défaut
		if(empty($selectedLang))	{$selectedLang="francais";}
		//Ouvre le dossier des langues & init le "Onchange"
		$onchange=($typeConfig=="install")  ?  "redir('?ctrl=".Req::$curCtrl."&action=".Req::$curAction."&curTrad='+this.value);"  :  "$('.menuTradIcon').attr('src','app/trad/'+this.value+'.png');";
		// Affichage
		$menuLangOptions=null;
		foreach(scandir("app/trad/") as $tmpFileLang){
			if(strstr($tmpFileLang,".php")){
				$tmpLang=trim(str_replace(".php","",$tmpFileLang));
				$tmpLabel=($typeConfig=="user" && $tmpLang==Ctrl::$agora->lang)  ?  $tmpLang." (".Txt::trad("byDefault").")"  :  $tmpLang;
				$menuLangOptions.= '<option value="'.$tmpLang.'" '.($tmpLang==$selectedLang?'selected':null).'> '.$tmpLabel.'</option>';
			}
		}
		return '<select name="lang" onchange="'.$onchange.'">'.$menuLangOptions.'</select> &nbsp; <img src="app/trad/'.$selectedLang.'.png" class="menuTradIcon">';
	}

	/********************************************************************************************************
	 * LISTE DES BOUTONS "RADIO" D'UN INPUT
	 * Chaque element de $tabRadios doit avoir une "value" + "trad"  ("img" optionnel)
	 ********************************************************************************************************/
	public static function radioButtons($inputName, $curValue, $tabRadios)
	{
		$radioButtons=null;
		foreach($tabRadios as $tmpRadio){
			$inputId=$inputName.'_'.$tmpRadio["value"];
			$inputChecked=($curValue==$tmpRadio["value"])  ?  "checked"  :  null;
			$inputImg=(!empty($tmpRadio["img"]))  ?  '<img src="app/img/'.$tmpRadio["img"].'">'  :  null;
			$radioButtons.='<input type="radio" name="'.$inputName.'" value="'.$tmpRadio["value"].'" id="'.$inputId.'" '.$inputChecked.'>
							<label for="'.$inputId.'">'.$inputImg.Txt::trad($tmpRadio["trad"]).'</label>';
		}
		return $radioButtons;
	}

	/********************************************************************************************************
	 * VUE : AFFICHE LES OPTIONS DE BASE POUR L'ENVOI D'EMAIL (cf. "Tool::sendMail()") 
	 ********************************************************************************************************/
	public static function sendMailBasicOptions()
	{
		return Ctrl::getVue(Req::commonPath."VueSendMailOptions.php");
	}
}