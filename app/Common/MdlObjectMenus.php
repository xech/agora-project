<?php
/**
* This file is part of the Agora-Project Software package.
*
* @copyright (c) Agora-Project Limited <https://www.agora-project.net>
* @license GNU General Public License, version 2 (GPL-2.0)
*/


/*
 * Menus des Objects
 */
trait MdlObjectMenus
{
	public static $pageNbObjects=50;	//Nb d'éléments affichés par page : 50 par défaut
	public static $displayMode=null;	//Type d'affichage en préference (ligne/block)

	/*******************************************************************************************
	 * IDENTIFIANT UNIQUE DE L'OBJET : CONTENEUR DE L'OBJET, MENU CONTEXTUEL, ETC
	 *******************************************************************************************/
	public function uniqId($prefix)
	{
		if(empty($this->objUniqId))  {$this->objUniqId=Txt::uniqId();}	//Un seul ID par instance de l'objet (tester avec les événements récurrents de l'agenda)
		return $prefix.$this->objUniqId;								//Retourne l'id avec un prefix : "objContainer" / "objMenu" / "objCheckbox" / "objAttachment"
	}

	/*******************************************************************************************
	 * BALISE OUVRANTE DU BLOCK DE L'OBJET :  CONTIENT L'ID DU MENU CONTEXTUEL (click droit && "menuContextInit()")  &&  L'URL D'ÉDITION (dblClick)
	 *******************************************************************************************/
	public function objContainer($specificClass=null, $specificAttributes=null)
	{
		if(static::isSelectable==true)	{$specificClass.=' isSelectable';}
		if($this->editRight())			{$specificAttributes.=' data-urlEdit="'.$this->getUrl('edit').'"';}
		return  '<div id="'.$this->uniqId("objContainer").'" for="'.$this->uniqId("objMenu").'" class="objContainer '.$specificClass.'" '.$specificAttributes.'>';
	}

	/*******************************************************************************************
	 * VUE : MENU CONTEXTUEL (édition, droit d'accès, etc)
	 * $options["iconBurger"] (text)		: Icone "burger" du launcher => "inlineSmall" / "inlineBig" / "floatSmall" / "floatBig" (par défaut)
	 * $options["deleteLabel"] (text)		: Label spécifique de suppression
	 * $options["specificOptions"] (Array)	: Boutons à ajouter au menu : chaque bouton a les propriétés suivante  ["actionJs"=>"?ctrl=file&action=monAction", "iconSrc"=>"app/img/plus.png", "label"=>"mon option", "tooltip"=>"mon tooltip"]
	 * $options["specificLabels"] (Array)	: Texte à afficher (ex: "affectedCalendarsLabel()" pour afficher les agendas affectés à un evenement)
	 *******************************************************************************************/
	public function contextMenu($options=null)
	{
		////	RETOURNE "FALSE" SI GUESTS
		if(Ctrl::$curUser->isUser()==false)  {return false;}
		////	INIT  &  DIVERSES OPTIONS
		$vDatas["curObj"]=$this;
		$vDatas["iconBurger"]=(!empty($options["iconBurger"]))  ?  $options["iconBurger"]  :  "floatBig";
		$vDatas["specificOptions"]=(!empty($options["specificOptions"]))  ?  $options["specificOptions"]  :  array();
		$vDatas["specificLabels"]=(!empty($options["specificLabels"]))  ?  $options["specificLabels"]  :  array();

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
			////	SUPPRESSION DE L'ESPACE COURANT
			if($this->deleteFromCurSpaceRight()){
				$deleteFromCurSpaceUrl="?ctrl=user&action=deleteFromCurSpace&objectsTypeId[".static::objectType."]=".$this->_id;
				$vDatas["deleteFromCurSpaceConfirm"]="if(confirm('".Txt::trad("USER_deleteFromCurSpaceConfirm",true)."')) redir('".$deleteFromCurSpaceUrl."')";
			}
			////	SUPPRESSION DEFINITIVE (double confirmation)
			if($this->deleteRight()){
				$vDatas["confirmDeleteJs"]="confirmDelete('".$this->getUrl("delete")."',labelConfirmDeleteDbl)";
				$vDatas["deleteLabel"]=Txt::trad("USER_deleteDefinitely");
			}
			////	LISTE DES ESPACES DE L'UTILISATEUR
			if(Ctrl::$curUser->isGeneralAdmin()){
				$vDatas["userSpaceList"]=Txt::trad("USER_spaceList")." : ";
				if(count($this->getSpaces())==0)	{$vDatas["userSpaceList"].=Txt::trad("USER_spaceNoAffectation");}
				else								{ foreach($this->getSpaces() as $tmpSpace)  {$vDatas["userSpaceList"].="<br>".$tmpSpace->name;} }
			}
			////	AUTEUR/DATE DE CREATION
			$vDatas["autorDateCrea"]="<a onclick=\"lightboxOpen('".Ctrl::getObj("user",$this->_idUser)->getUrl("vue")."');\">".$this->autorLabel()."</a> - ".$this->dateLabel(true,"normal");
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
			////	COPIER L'ADRESSE/URL D'ACCES
			if(Ctrl::$curUser->isUser() && static::objectType!="space")   {$vDatas["getUrlExternal"]=$this->getUrlExternal();}
			////	SUPPRIMER
			if($this->deleteRight())
			{
				//Options du "confirmDelete()" 
				$labelConfirmDbl=$ajaxControlUrl="null";
				if(static::objectType=="space")	{$labelConfirmDbl="'".Txt::trad("SPACE_confirmDeleteDbl",true)."'";}						//Suppression d'espace : double confirmation spécifique
				if(static::isContainer())		{$labelConfirmDbl="labelConfirmDeleteDbl";}													//Suppression de conteneur : double confirmation du "VueStructure.php"
				if(static::isFolder==true)		{$ajaxControlUrl="'?ctrl=object&action=folderDeleteControl&typeId=".$this->_typeId."'";}	//Suppression de dossier : controle d'accès via Ajax
				//Ajoute l'option
				$vDatas["confirmDeleteJs"]="confirmDelete('".$this->getUrl("delete")."',".$labelConfirmDbl.",".$ajaxControlUrl.",'".$this->uniqId("objLabel")."')";
				$vDatas["deleteLabel"]=(!empty($options["deleteLabel"]))  ?  $options["deleteLabel"]  :  Txt::trad("delete");
			}
			////	AUTEUR/DATE DE CREATION/MODIF
			//Init les labels  &&  vérif si c'est un nouvel objet (créé dans les 24 heures ou depuis la précédente connexion)
			$vDatas["autorDateCrea"]=$vDatas["autorDateModif"]=null;
			$vDatas["isNewObject"]=(!empty($this->dateCrea)  &&  (strtotime($this->dateCrea) > (time()-86400)  ||  strtotime($this->dateCrea) > Ctrl::$curUser->previousConnection));
			//Auteur de l'objet (Guest?)
			if($this->_idUser)		{$vDatas["autorDateCrea"]="<a onclick=\"lightboxOpen('".Ctrl::getObj("user",$this->_idUser)->getUrl("vue")."');\">".$this->autorLabel()."</a>";}
			elseif($this->guest)	{$vDatas["autorDateCrea"]=$this->autorLabel();}
			//Date de création de l'objet  &&  Précise si c'est un nouvel objet  &&  Précise l'auteur/date de modif
			if($this->dateCrea)					{$vDatas["autorDateCrea"].=" - ".$this->dateLabel(true,"normal");}
			if($vDatas["isNewObject"]==true)	{$vDatas["autorDateCrea"].="<div class='sAccessWrite'>".Txt::trad("objNew")." <img src='app/img/menuNewSmall.png'></div>";}
			if(!empty($this->_idUserModif))  	{$vDatas["autorDateModif"]="<a onclick=\"lightboxOpen('".Ctrl::getObj("user",$this->_idUserModif)->getUrl("vue")."');\">".$this->autorLabel(false)."</a> - ".$this->dateLabel(false,"normal");}

			////	LIBELLES DES DROITS D'ACCESS : AFFECTATION AUX ESPACES, USERS, ETC  (droit d'accès de l'objet OU du conteneur d'un objet)
			if($this->hasAccessRight() || $this->accessRightFromContainer())
			{
				//Récupère les affectations (de l'objet OU de son conteneur)  &&  Ajoute le label des affectations pour chaque type de droit d'accès (lecture/ecriture limité/ecriture)
				$objAffects=($this->hasAccessRight())  ?  $this->getAffectations()  :  $this->containerObj()->getAffectations();
				$vDatas["affectLabels"]=$vDatas["affectTooltips"]=["1"=>null,"1.5"=>null,"2"=>null];
				foreach($objAffects as $tmpAffect)  {$vDatas["affectLabels"][(string)$tmpAffect["accessRight"]].=$tmpAffect["label"]."<br>";}
				//Affiche si l'objet est personnel ("isPersoAccess")
				$firstAffect=reset($objAffects);//Récup la première affectation du tableau
				$vDatas["isPersoAccess"]=(count($objAffects)==1 && $firstAffect["targetType"]=="user" && $firstAffect["target_id"]==Ctrl::$curUser->_id);
				//Tooltip spécifique
				if(static::isContainer())  					{$tooltipDetail=$this->tradObject("accessAutorPrivilege")."<hr>";}					//"Seul l'auteur ou l'admin peuvent modifier/supprimer le -dossier-"
				elseif($this->accessRightFromContainer())	{$tooltipDetail=$this->containerObj()->tradObject("accessRightsInherited")."<hr>";}	//"Droits d'accès hérité du -dossier- parent"
				else										{$tooltipDetail=null;}
				//Tooltip : description de chaque droit d'accès
				if(!empty($vDatas["affectLabels"]["1"]))	{$vDatas["affectTooltips"]["1"]=$tooltipDetail.Txt::trad("accessReadTooltip");}
				if(!empty($vDatas["affectLabels"]["1.5"]))	{$vDatas["affectTooltips"]["1.5"]=$tooltipDetail.$this->tradObject("accessWriteLimitTooltip");}
				if(!empty($vDatas["affectLabels"]["2"]))	{$vDatas["affectTooltips"]["2"]=(static::isContainer())  ?  $tooltipDetail.$this->tradObject("accessWriteTooltipContainer")  :  $tooltipDetail.Txt::trad("accessWriteTooltip");}
			}
			////	USERS LIKES
			$vDatas["showMiscMenuClass"]=null;
			if($this->hasUsersLike())
			{
				$likeOptions=(Ctrl::$agora->usersLike=="likeOrNot")  ?  ["like","dontlike"]  :  ["like"];
				foreach($likeOptions as $likeOption){
					$likeMenuId="likeMenu_".$this->_typeId."_".$likeOption;//ID du menu. Ex: "likeMenu_news-55_dontlike". Cf. "usersLikeValidate()" dans le "common.js"
					$likeMenuNb=count($this->getUsersLike($likeOption));
					if(!empty($likeMenuNb))  {$vDatas["showMiscMenuClass"]="showMiscMenu";}
					$vDatas["likeMenu"][$likeOption]=["menuId"=>$likeMenuId, "likeDontLikeNb"=>$likeMenuNb];
				}
			}
			////	COMMENTAIRES
			if($this->hasUsersComment())
			{
				$commentNb=count($this->getUsersComment());
				$commentTooltip=$commentNb." ".Txt::trad($commentNb>1?"AGORA_usersComments":"AGORA_usersComment")." :<br>".Txt::trad("commentAdd");
				$commentsUrl="?ctrl=object&action=Comments&typeId=".$this->_typeId;
				if(!empty($commentNb))  {$vDatas["showMiscMenuClass"]="showMiscMenu";}
				$vDatas["commentMenu"]=["menuId"=>"commentMenu_".$this->_typeId, "commentNb"=>$commentNb, "commentTooltip"=>$commentTooltip, "commentsUrl"=>$commentsUrl];
			}
		}
		////	Affichage
		return Ctrl::getVue(Req::commonPath."VueObjMenuContext.php",$vDatas);
	}

	/*******************************************************************************************
	 * VUE D'UN OBJET : AFFICHE LE MENU CONTEXTUEL + LE BOUTON D'EDITION (AVEC LE TITRE DE L'OBJET)
	 *******************************************************************************************/
	public function inlineContextMenu()
	{
		$return=$this->contextMenu(["iconBurger"=>"inlineBig"]);
		if($this->editRight())  {$return.='&nbsp; <img src="app/img/edit.png" onclick="lightboxOpen(\''.$this->getUrl('edit').'\')" title="'.Txt::trad("modify").'">';}
		return '<span class="lightboxTitleMenu">'.$return.'</span>';
	}

	/*******************************************************************************************
	 * VUE : TITRE DE L'OBJET SUR MOBILE ("nouveau fichier", "nouveau dossier", etc)
	 *******************************************************************************************/
	public function titleMobile($keyTrad)
	{
		if(Req::isMobile() && $this->isNew())  {echo "<div class='lightboxTitle'>".Txt::trad($keyTrad)."</div>";}
	}

	/*******************************************************************************************
	 * VUE : MENU D'ÉDITION DE LA DESCRIPTION, SI BESOIN AVEC L'EDITEUR TINYMCE
	 *******************************************************************************************/
	public function editDescription($toggleButton=true)
	{
		$vDatas["curObj"]=$this;
		$vDatas["toggleButton"]=$toggleButton;
		//Sélectionne au besoin le "draftTypeId" pour n'afficher que le brouillon/draft de l'objet précédement édité (on n'utilise pas "editTypeId" car il est effacé dès qu'on sort de l'édition de l'objet...)
		$sqlTypeId=Req::isParam("typeId")  ?  "draftTypeId=".Db::param("typeId")  :  "draftTypeId IS NULL";
		$vDatas["editorDraft"]=(string)Db::getVal("SELECT editorDraft FROM ap_userLivecouter WHERE _idUser=".Ctrl::$curUser->_id." AND ".$sqlTypeId);
		//Affiche la vue
		return Ctrl::getVue(Req::commonPath."VueObjEditDescription.php",$vDatas);
	}

	/*******************************************************************************************************************************************
	 * VUE : AFFICHE LES FICHIERS JOINTS DE L'OBJET (cf. "VueObjEditMenuSubmit.php")
	 *******************************************************************************************************************************************/
	public function attachedFile()
	{
		$vDatas["curObj"]=$this;
		return Ctrl::getVue(Req::commonPath."VueObjAttachedFile.php",$vDatas);
	}

	/*******************************************************************************************
	 * VUE : MENU D'ÉDITION PRINCIPAL (droits d'accès, fichiers joints, etc)
	 *******************************************************************************************/
	public function editMenuSubmit()
	{
		////	Menu des droits d'accès
		if($this->hasAccessRight())
		{
			////	Init & Labels
			$vDatas["objMenuAccessRight"]=true;
			$vDatas["objMenuAccessRightLabel"]=(static::isContainer())  ?  "<span title=\"".$this->tradObject("accessAutorPrivilege")."<hr>".$this->tradObject("accessWriteLimitTooltip")."\">".Txt::trad("EDIT_accessRightContent")." <img src='app/img/info.png'></span>"  :  Txt::trad("EDIT_accessRight");
			$vDatas["accessWriteLimitTooltip"]=$this->tradObject("accessWriteLimitTooltip");
			$vDatas["extendToSubfolders"]=(static::isFolder==true && $this->isNew()==false && Db::getVal("SELECT count(*) FROM ".static::dbTable." WHERE _idContainer=".$this->_id)>0);//dossier avec des sous-dossiers
			////	Droits d'accès pour chaque espace ("targets")
			$vDatas["accessRightSpaces"]=[];
			foreach(Ctrl::$curUser->getSpaces() as $tmpSpace)
			{
				//Verif si le module de l'objet est bien activé sur l'espace
				if(array_key_exists(static::moduleName,$tmpSpace->moduleList()))
				{
					////	Init les "targetLines"
					$tmpSpace->targetLines=[];
					////	"Tous les utilisateurs"  OU  "Tous les utilisateurs et invités"
					if(empty($tmpSpace->public))	{$allUsersLabel=Txt::trad("EDIT_allUsers");															$allUsersLabelInfo=Txt::trad("EDIT_allUsersTooltip");}
					else							{$allUsersLabel=Txt::trad("EDIT_allUsersAndGuests").' <img src="app/img/user/accessGuest.png">';	$allUsersLabelInfo=Txt::trad("EDIT_allUsersAndGuestsTooltip");}
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
		return Ctrl::getVue(Req::commonPath."VueObjEditMenuSubmit.php",$vDatas);
	}

	/*******************************************************************************************
	 * STATIC : CLÉ DE PRÉFÉRENCE EN BDD ($prefDbKey)
	 *******************************************************************************************/
	public static function prefDbKey($containerObj)
	{
		if(is_object($containerObj))											{return $containerObj->_typeId;}		//"_typeId" de l'objet en parametre
		elseif(!empty(Ctrl::$curContainer) && is_object(Ctrl::$curContainer))	{return Ctrl::$curContainer->_typeId;}	//"_typeId" du conteneur/dossier courant
		else																	{return static::moduleName;}			//"moduleName" courant
	}

	/*******************************************************************************************
	 * VUE : MENU DE SÉLECTION DE PLUSIEURS OBJETS (cf. menu contextuel du module)
	 *******************************************************************************************/
	public static function menuSelectObjects()
	{
		if(Req::isMobile()==false){
			Ctrl::$isMenuSelectObjects=true;
			$vDatas["curContainerEditContentRight"]=(!empty(Ctrl::$curContainer) && Ctrl::$curContainer->editContentRight());											//Droit de modifier le contenu (ex: fichiers du dossier)
			$vDatas["folderMoveOption"]=(!empty(Ctrl::$curRootFolder) && count(Ctrl::$curRootFolder->folderTree())>1 && $vDatas["curContainerEditContentRight"]==true);	//Droit de déplacer le contenu (si ya pas que le dossier racine)
			return Ctrl::getVue(Req::commonPath."VueObjMenuSelection.php",$vDatas);
		}
	}

	/*******************************************************************************************
	 * STATIC : TRI D'OBJETS : PREFERENCE EN BDD / PARAMÈTRE PASSÉ EN GET (ex: "firstName@@asc")
	 *******************************************************************************************/
	private static function getSort($containerObj=null)
	{
		//Récupère la préférence en Bdd ou params GET/POST
		$objectsSort=Ctrl::prefUser("sort_".static::prefDbKey($containerObj), "sort");
		//Tri par défaut si aucune préférence n'est précisé ou le tri sélectionné n'est pas dispo pour l'objet courant 
		if(empty($objectsSort) || !in_array($objectsSort,static::$sortFields))  {$objectsSort=static::$sortFields[0];}
		//renvoie le tri
		return $objectsSort;
	}

	/*****************************************************************************************************************************
	 * STATIC SQL : TRI SQL DES OBJETS (avec premier tri si besoin : news, subject, etc. Ex: "ORDER BY firstName asc")
	 *****************************************************************************************************************************/
	public static function sqlSort($firstSort=null)
	{
		//Init
		$firstSort=(!empty($firstSort))  ?  $firstSort.", "  :  null;							//Pré-tri ? Exple pour les News: "une desc"
		$sortTab=Txt::txt2tab(self::getSort(Ctrl::$curContainer));								//Récupère la préférence de tri du conteneur courant (dossier/sujet/etc). Ex: ["name","asc"]
		$fieldSort=($sortTab[0]=="extension") ? "SUBSTRING_INDEX(name,'.',-1)" : $sortTab[0];	//Tri par "extension" de fichier ?
		//Renvoie le tri Sql
		return "ORDER BY ".$firstSort." ".$fieldSort." ".$sortTab[1];
	}

	/*******************************************************************************************
	 * VUE : MENU DE TRI D'UN TYPE D'OBJET
	 *******************************************************************************************/
	public static function menuSort($containerObj=null, $addUrlParams=null)
	{
		$vDatas["sortFields"]=static::$sortFields;
		$vDatas["curSort"]=self::getSort($containerObj);
		$curSortTab=Txt::txt2tab($vDatas["curSort"]);
		$vDatas["curSortField"]=$curSortTab[0];
		$vDatas["curSortAscDesc"]=$curSortTab[1];
		$vDatas["addUrlParams"]=$addUrlParams;
		return Ctrl::getVue(Req::commonPath."VueObjMenuSort.php",$vDatas);
	}

	/*******************************************************************************************
	 * STATIC : RÉCUPÈRE LE TYPE D'AFFICHAGE DES OBJETS DE LA PAGE
	 *******************************************************************************************/
	public static function getDisplayMode($containerObj=null)
	{
		if(static::$displayMode===null){
			if(static::mobileOnlyDisplayBlock())	{static::$displayMode="block";}																								//Affichage "block" sur mobile
			else									{static::$displayMode=Ctrl::prefUser("displayMode_".static::prefDbKey($containerObj),"displayMode");}						//Préférence d'affichage de l'user
			if(empty(static::$displayMode)){																																	//Sinon on prend l'affichage par défaut :
				if(Ctrl::$agora->folderDisplayMode && in_array(Ctrl::$agora->folderDisplayMode,static::$displayModes))	{static::$displayMode=Ctrl::$agora->folderDisplayMode;}	//Paramétrage général ("folderDisplayMode")
				else																									{static::$displayMode=static::$displayModes[0];}		//Paramétrage du $displayModes du module
			}
		}
		return static::$displayMode;
	}

	/*******************************************************************************************
	 * VUE : MENU DU MODE D'AFFICHAGE DES OBJETS DANS UNE PAGE : BLOCKS / LIGNES (cf. $displayModes)
	 *******************************************************************************************/
	public static function menuDisplayMode($containerObj=null)
	{
		if(static::mobileOnlyDisplayBlock()==false){
			$vDatas["displayModes"]=static::$displayModes;
			$vDatas["curDisplayMode"]=static::getDisplayMode($containerObj);
			$vDatas["displayModeUrl"]=Tool::getParamsUrl("displayMode")."&displayMode=";
			return Ctrl::getVue(Req::commonPath."VueObjMenuDisplayMode.php",$vDatas);
		}
	}

	/*******************************************************************************************
	 * STATIC : SUR MOBILE, ON AFFICHE TOUJOURS EN MODE "BLOCK" (SI DISPO)
	 *******************************************************************************************/
	public static function mobileOnlyDisplayBlock()
	{
		return (Req::isMobile() && in_array("block",static::$displayModes));
	}

	/*******************************************************************************************
	 * STATIC SQL : FILTRE DE PAGINATION
	 *******************************************************************************************/
	public static function sqlPagination()
	{
		$offset=Req::isParam("pageNb")  ?  ((Req::param("pageNb")-1)*static::$pageNbObjects)  :  "0";
		return "LIMIT ".static::$pageNbObjects." OFFSET ".$offset;
	}

	/*******************************************************************************************
	 * VUE : MENU DE FILTRE ALPHABÉTIQUE
	 *******************************************************************************************/
	public static function menuPagination($displayedObjNb, $getParamKey=null)
	{
		$pageNbTotal=ceil($displayedObjNb/static::$pageNbObjects);
		if($pageNbTotal>1)
		{
			//Nb de page et numéro de page courant
			$vDatas["pageNbTotal"]=$pageNbTotal;
			$vDatas["pageNb"]=$pageNb=Req::isParam("pageNb") ? Req::param("pageNb") : 1;
			//Url de redirection de base
			$vDatas["hrefBase"]="?ctrl=".Req::$curCtrl;
			if(!empty($getParamKey) && Req::isParam($getParamKey))  {$vDatas["hrefBase"].="&".$getParamKey."=".Req::param($getParamKey);}
			$vDatas["hrefBase"].="&pageNb=";
			//Page Précédente / Suivante (desactive si on est déjà en première ou dernière page)
			$vDatas["prevAttr"]=($pageNb>1)  ?  "href=\"".$vDatas["hrefBase"].((int)$pageNb-1)."\""  :  "class='vNavMenuDisabled'";
			$vDatas["nextAttr"]=($pageNb<$pageNbTotal)  ?  "href=\"".$vDatas["hrefBase"].((int)$pageNb+1)."\""  :  "class='vNavMenuDisabled'";
			//Récupère le menu
			return Ctrl::getVue(Req::commonPath."VueObjMenuPagination.php",$vDatas);
		}
	}

	/*******************************************************************************************
	 * VUE : AFFICHE LES OPTIONS DE BASE POUR L'ENVOI D'EMAIL (cf. "Tool::sendMail()") 
	 *******************************************************************************************/
	public static function sendMailBasicOptions()
	{
		return Ctrl::getVue(Req::commonPath."VueSendMailOptions.php");
	}
}