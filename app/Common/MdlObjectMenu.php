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
		if(empty($this->objUniqId))  {$this->objUniqId=uniqid();}	//Un seul ID par instance de l'objet (cf. evt répétés)
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
		if(static::objectType=="user"){
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
		else{
			////	MODIFIER L'OBJET  &  LOGS/HISTORIQUE  &  DEPLACER L'OBJET DANS UN AUTRE DOSSIER (si ya pas que le dossier racine)
			if($this->editRight()){
				$vDatas["editLabel"]=($this->hasAccessRight())  ?  Txt::trad("modifyAndAccesRight")  :  Txt::trad("modify");
				$vDatas["logUrl"]="?ctrl=object&action=logs&typeId=".$this->_typeId;
				if(!empty(Ctrl::$curRootFolder) && count(Ctrl::$curRootFolder->folderTree())>1)  {$vDatas["moveObjectUrl"]="?ctrl=object&action=FolderMove&typeId=".$this->containerObj()->_typeId."&objectsTypeId[".static::objectType."]=".$this->_id;}
			}
			////	URL D'ACCES EXTERNE
			if(Ctrl::$curUser->isUser() && static::objectType!="space")  {$vDatas["getUrlExternal"]=$this->getUrlExternal();}
			////	AUTEUR/DATE DE CREATION/MODIF
			$vDatas["autorDateCrea"] =(!empty($this->dateCrea))   ?  $this->autorDate(false)  :  null;
			$vDatas["autorDateModif"]=(!empty($this->dateModif))  ?  $this->autorDate(true)  :  null;
			////	SUPPRIMER
			if($this->deleteRight()){
				$ajaxControlUrl =(static::isFolder==true)  ?  "'?ctrl=object&action=folderDeleteControl&typeId=".$this->_typeId."'"  :  "null";	//Controle d'accès aux sous-dossiers
				$vDatas["confirmDeleteJs"]="confirmDelete('".$this->getUrl("delete")."', $('#".$this->uniqId("objLabel")."').text(), ".$ajaxControlUrl.")";
				$vDatas["deleteLabel"]=(!empty($options["deleteLabel"]))  ?  $options["deleteLabel"]  :  Txt::trad("delete");
			}
			////	LIBELLES DES DROITS D'ACCESS : AFFECTATION AUX ESPACES, USERS, ETC  (droit d'accès de l'objet OU du conteneur d'un objet)
			if($this->hasAccessRight() || $this->hasContainerAccessRight()){
				//Récupère les affectations de l'objet ou de son conteneur
				$objAffectations=($this->hasAccessRight())  ?  $this->getAffectations()  :  $this->containerObj()->getAffectations();
				//Ajoute le label des affectations pour chaque droit d'accès ("1","1.5","2")
				$vDatas["affectLabels"]=$vDatas["affectTooltips"]=["1"=>null,"1.5"=>null,"2"=>null];
				foreach($objAffectations as $tmpAffect)  {$vDatas["affectLabels"][(string)$tmpAffect["accessRight"]].=$tmpAffect["label"]."<br>";}
				//Affiche si l'objet est personnel ("isPersoAccess")
				$firstAffect=reset($objAffectations);//Récup la première affectation du tableau
				$vDatas["isPersoAccess"]=(count($objAffectations)==1 && $firstAffect["targetType"]=="user" && $firstAffect["targetId"]==Ctrl::$curUser->_id);
				//Tooltip : description des droits d'accès
				if(!empty($vDatas["affectLabels"]["1"]))	{$vDatas["affectTooltips"]["1"]  =$this->tradObject("accessReadTooltip");}
				if(!empty($vDatas["affectLabels"]["1.5"]))	{$vDatas["affectTooltips"]["1.5"]=$this->tradObject("accessWriteLimitTooltip");}
				if(!empty($vDatas["affectLabels"]["2"]))	{$vDatas["affectTooltips"]["2"]  =static::isContainer() ? $this->tradObject("accessWriteTooltipContainer") : $this->tradObject("accessWriteTooltip");}
			}
		}
		////	Affichage
		return Ctrl::getVue(Req::commonPath."VueObjMenuContext.php",$vDatas);
	}

	/********************************************************************************************************
	 * VUE : MENU D'ÉDITION PRINCIPAL (droits d'accès, fichiers joints, etc)
	 ********************************************************************************************************/
	public function editMenuSubmit()
	{
		////	MENU DES AFFECTATIONS / DROITS D'ACCES
		if($this->hasAccessRight()){
			////	Affectations en BDD  +  Labels des droits d'accès
			$objAffectations=$this->getAffectations();
			$vDatas["menuAccessRight"]=true;
			$accessFullTooltip				=Txt::tooltip($this->tradObject("accessFullTooltip"));
			$vDatas["affectTooltips"]["1"]	=Txt::tooltip($this->tradObject("accessReadTooltip"));
			$vDatas["affectTooltips"]["1.5"]=static::isContainer()  ?  Txt::tooltip($this->tradObject("accessWriteLimitTooltip"))  :  null;
			$vDatas["affectTooltips"]["2"]	=static::isContainer()  ?  Txt::tooltip($this->tradObject("accessWriteTooltipContainer"))  :  Txt::tooltip($this->tradObject("accessWriteTooltip"));
			$vDatas["extendSubfolders"]=(static::isFolder==true  &&  $this->isNew()==false  &&  Db::getVal("SELECT count(*) FROM ".static::dbTable." WHERE _idContainer=".$this->_id)>0);
			////	Liste des affectations disponibles pour chaque espace
			$vDatas["spaceAffectations"]=[];
			foreach(Ctrl::$curUser->spaceList() as $tmpSpace){
				if($tmpSpace->moduleEnabled(static::moduleName)){
					$tmpSpace->targetLines=[];
					////	Ligne "Tous les utilisateurs"
					$targetId=$tmpSpace->_id."_spaceUsers";//ex: "1_spaceUsers"
					if(empty($tmpSpace->public))	{$targetLabel=Txt::trad("accessAllUsers");			$targetTooltip=Txt::tooltip("accessAllUsersTooltip");}
					else							{$targetLabel=Txt::trad("accessAllUsersGuests");	$targetTooltip=Txt::tooltip("accessAllUsersGuestsTooltip");}
					$tmpSpace->targetLines[$targetId]=["icon"=>"accessAllUsers.png", "label"=>$targetLabel, "tooltip"=>str_replace("--SPACENAME--",$tmpSpace->name,$targetTooltip)];
					////	Lignes des groupes d'users de l'espace
					foreach(MdlUserGroup::getGroups($tmpSpace) as $tmpGroup){
						$targetId=$tmpSpace->_id."_G".$tmpGroup->_id;//ex: "1_G5"
						$tmpSpace->targetLines[$targetId]=["icon"=>"accessGroup.png", "label"=>$tmpGroup->title, "tooltip"=>Txt::tooltip($tmpGroup->usersLabel)];
					}
					////	Lignes des users de l'espace
					foreach($tmpSpace->getUsers() as $tmpUser){
						$targetId=$tmpSpace->_id."_U".$tmpUser->_id;//ex: "1_U55"
						if($tmpUser->_id==Ctrl::$curUser->_id || $tmpSpace->accessRightUser($tmpUser)==2)	{$targetIcon="accessFull.png";	$targetTooltip=$accessFullTooltip;	$accessFull=true;}//Auteur/Admin
						else																				{$targetIcon="accessUser.png";	$targetTooltip=null;				$accessFull=false;}
						$tmpSpace->targetLines[$targetId]=["icon"=>$targetIcon, "label"=>$tmpUser->getLabel(), "tooltip"=>$targetTooltip, "accessFull"=>$accessFull];
					}
					////	Checkboxes de chaque target
					$accessRightList=static::isContainer()  ?  ["1","1.5","2"]  :  ["1","2"];
					foreach($tmpSpace->targetLines as $targetId=>$targetTmp){
						foreach($accessRightList as $tmpRight){
							$tmpAttr=' value="'.$targetId.'_'.$tmpRight.'"';																				//Value de la checkbox
							if(!empty($targetTmp["accessFull"]) && $tmpRight!="2")												{$tmpAttr.=" disabled";}	//Disabled si "accessFull" : Auteur/Admin
							if(!empty($objAffectations[$targetId]) && $objAffectations[$targetId]["accessRight"]==$tmpRight)	{$tmpAttr.=" checked";}		//Checked si dejà enregistrée en BDD
							$tmpSpace->targetLines[$targetId]["checkboxes"][$tmpRight]=$tmpAttr;															//Ajoute la checkbox à la target
						}
					}
					////	Enregistre les targets de l'espace
					$vDatas["spaceAffectations"][$tmpSpace->_id]=$tmpSpace;
				}
			}
		}
		////	MENU DES NOTIFS MAIL
		if(static::hasNotifMail==true && Tool::mailEnabled()){
			$vDatas["menuNotifMail"]=true;
			$vDatas["notifMailUsers"]=Ctrl::$curUser->usersVisibles(true);
			$vDatas["curSpaceUsersIds"]=Ctrl::$curSpace->getUsers("idsTab");
			$vDatas["curSpaceUserGroups"]=MdlUserGroup::getGroups(Ctrl::$curSpace);
			$vDatas["notifMailTooltip"]=$this->tradObject("EDIT_notifMailTooltip");
			if($this::objectType=="calendarEvent")  {$vDatas["notifMailTooltip"].=Txt::trad("EDIT_notifMailTooltipCal");}//"Agenda personnel : envoyé uniquement au propriétaire de l'agenda"
		}
		////	MENU DES FICHIERS JOINTS
		if(static::hasAttachedFiles==true){
			$vDatas["menuAttachedFile"]=true;
			$vDatas["attachedFilesNb"]=count($this->attachedFileList());
		}
		////	MENU DES SHORTCUT
		if(static::hasShortcut==true)
			{$vDatas["menuShortcut"]=true;}
		////	AFFICHE LA VUE
		$vDatas["curObj"]=$this;
		return Ctrl::getVue(Req::commonPath."VueObjMenuEdit.php",$vDatas);
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
	 * VUE : TITRE DE L'OBJET SUR MOBILE (ex: "Nouveau dossier", "Mon dossier")
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
		//Menu de sélection d'objets affiché  +  l'objet n'est pas le conteneur/dossier courant
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
			$vDatas["displayModeUrl"]=Tool::paramsUrl("displayMode")."&displayMode=";
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