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
	public static $pageNbObjects=50;		//Nb d'éléments affichés par page : 50 par défaut
	public static $displayModeCurrent=null;	//Type d'affichage en préference (ligne/block)

	/*
	 * Balise ouvrante du block de l'objet : contient l'Url d'édition (pour le DblClick)  et l'id du menu contextuel (Click droit)
	 */
	public function divContainer($specificClass=null, $specificAttributes=null)
	{
		$isSelectableclass=(static::isSelectable==true)  ?  "isSelectable"  :  null;
		$urlEdit=($this->editRight())  ?  "data-urlEdit=\"".$this->getUrl("edit")."\""  :  null;
		return  "<div class=\"objContainer ".$isSelectableclass." ".$specificClass."\" ".$specificAttributes." id=\"".$this->menuId("objBlock")."\" for=\"".$this->menuId("objMenu")."\" ".$urlEdit.">";
	}

	/*
	 * Identifiant du menu contextuel : "objBlock"/"objBlock"/"objAttachment" (Cf. "initMenuContext()"!)
	 */
	public function menuId($prefix)
	{
		if(empty($this->contextMenuId))  {$this->contextMenuId=Txt::uniqId();}//Un menu par instance de l'objet (Tester avec les evts récurrents ou les menus d'agendas)
		return $prefix."_".$this->contextMenuId;
	}

	/*
	 * VUE : Menu contextuel (édition, droit d'accès, etc)
	 * $options["iconBurger"] (text)		: icone "burger" du launcher ("small", "big" ou "float" par défaut)
	 * $options["deleteLabel"] (Bool)		: label spécifique de suppression
	 * $options["specificOptions"] (Array)	: boutons à ajouter au menu. Exemple avec  ["actionJs"=>"?ctrl=file&action=monAction", "iconSrc"=>"app/img/plus.png", "label"=>"mon option", "tooltip"=>"mon tooltip"]
	 * $options["specificLabels"] (Array)	: Texte à afficher. Exemple avec les "affectedCalendarsLabel()" pour afficher la liste des agendas ou est affecté un evenement
	 */
	public function contextMenu($options=null)
	{
		////	INIT  &  DIVERSES OPTIONS
		$vDatas["curObj"]=$this;
		$vDatas["iconBurger"]=(!empty($options["iconBurger"]))  ?  $options["iconBurger"]  :  "float";//Icone "burger" du launcher : "small" inline / "big" inline / "float" en position absolute (par défaut)
		$vDatas["specificOptions"]=(!empty($options["specificOptions"]))  ?  $options["specificOptions"]  :  array();
		$vDatas["specificLabels"]=(!empty($options["specificLabels"]))  ?  $options["specificLabels"]  :  array();

		////	OBJET USER
		if(static::objectType=="user")
		{
			////	MODIFIER ELEMENT  &  MODIF MESSENGER
			if($this->editRight()){
				$vDatas["editLabel"]=Txt::trad("USER_profilEdit");
				$vDatas["editMessengerObjUrl"]="?ctrl=user&action=UserEditMessenger&targetObjId=".$this->_targetObjId;
			}
			////	SUPPRESSION DE L'ESPACE COURANT
			if($this->deleteFromCurSpaceRight()){
				$deleteFromCurSpaceUrl="?ctrl=user&action=deleteFromCurSpace&targetObjects[".static::objectType."]=".$this->_id;
				$vDatas["deleteFromCurSpaceConfirm"]="confirmDelete('".$deleteFromCurSpaceUrl."', '".Txt::trad("USER_deleteFromCurSpaceConfirm",true)."')";
			}
			////	SUPPRESSION DEFINITIVE
			if($this->deleteRight()){
				$vDatas["confirmDeleteJs"]="confirmDelete('".$this->getUrl("delete")."','".Txt::trad("confirmDeleteDbl",true)."')";
				$vDatas["deleteLabel"]=Txt::trad("USER_deleteDefinitely");
			}
			////	ESPACE DE L'UTILISATEUR
			if(Ctrl::$curUser->isAdminGeneral()){
				$vDatas["userSpaceList"]=Txt::trad("USER_spaceList")." : ";
				if(count($this->getSpaces())==0)	{$vDatas["userSpaceList"].=Txt::trad("USER_spaceNoAffectation");}
				else								{ foreach($this->getSpaces() as $tmpSpace)  {$vDatas["userSpaceList"].="<br>".$tmpSpace->name;} }
			}
		}
		////	OBJET LAMBDA
		else
		{
			////	MODIFIER ELEMENT  &  LOGS/HISTORIQUE  &  CHANGER DE DOSSIER (SI Y EN A..)
			if($this->editRight())
			{
				$vDatas["editLabel"]=(static::hasAccessRight==true && $this->isIndependant())  ?  Txt::trad("modifyAndAccesRight")  :  Txt::trad("modify");
				$vDatas["logUrl"]="?ctrl=object&action=logs&targetObjId=".$this->_targetObjId;
				if($this::isInArbo() && !empty(Ctrl::$curContainer)){
					$curRootFolder=Ctrl::getObj(get_class(Ctrl::$curContainer),1);//Récupère le dossier racine et compte le nb de sous-dossiers
					if(count($curRootFolder->folderTree())>1)  {$vDatas["moveObjectUrl"]="?ctrl=object&action=FolderMove&targetObjId=".$this->containerObj()->_targetObjId."&targetObjects[".static::objectType."]=".$this->_id;}
				}
			}
			////	SUPPRIMER
			if($this->deleteRight())
			{
				$labelConfirmDeleteDbl=$ajaxControlUrl=null;
				//Suppression d'espace ou de conteneur : Double confirmation
				if(static::objectType=="space")	{$labelConfirmDeleteDbl=",'".Txt::trad("SPACE_confirmDeleteDbl",true)."'";}	
				elseif(static::isContainer())	{$labelConfirmDeleteDbl=",'".Txt::trad("confirmDeleteDbl",true)."'";}
				//Suppression de dossier : controle Ajax (droit  d'accès and co)
				if(static::isFolder==true)  {$ajaxControlUrl=",'?ctrl=object&action=folderDeleteControl&targetObjId=".$this->_targetObjId."'";}
				//Ajoute l'option
				$vDatas["confirmDeleteJs"]="confirmDelete('".$this->getUrl("delete")."' ".$labelConfirmDeleteDbl.$ajaxControlUrl.")";
				$vDatas["deleteLabel"]=(!empty($options["deleteLabel"]))  ?  $options["deleteLabel"]  :  Txt::trad("delete");
			}
			////	LIBELLES DES DROITS D'ACCESS : AFFECTATION AUX ESPACES, USERS, ETC
			if(static::hasAccessRight==true && Ctrl::$curUser->isUser() && $this->isIndependant())
			{
				//Init le tableau des libellés
				$objAffects=$this->getAffectations();
				$vDatas["affectLabels"]=$vDatas["affectTooltips"]=["1"=>null,"1.5"=>null,"2"=>null];
				//Ajoute le label de chaque affectation : pour chaque type de droit d'accès (lecture/ecriture limité/ecriture). Ajoute ausi le nom de l'espace, si ça ne concerne pas l'espace courant
				foreach($objAffects as $tmpAffect){
					if($tmpAffect["targetType"]!="spaceUsers" && $tmpAffect["_idSpace"]!=Ctrl::$curSpace->_id)  {$tmpAffect["label"].=" (".Ctrl::getObj("space",$tmpAffect["_idSpace"])->name.")";}
					$vDatas["affectLabels"][$tmpAffect["accessRight"]].=$tmpAffect["label"]."<br>";
				}
				//Affiche si l'objet est personnel ("isPersoAccess")
				$firstAffect=reset($objAffects);//Récup la première affectation du tableau
				$vDatas["isPersoAccess"]=(count($objAffects)==1 && $firstAffect["targetType"]=="user" && $firstAffect["target_id"]==Ctrl::$curUser->_id);
				//Tooltip pour chaque type de droit d'accès
				$affectAutor=(static::isContainer())  ?  "<hr>".$this->tradObject("autorPrivilege")  :  null;//Ex: "Seul l'auteur ou l'admin peuvent modifier/supprimer le dossier"
				if(!empty($vDatas["affectLabels"]["1"]))	{$vDatas["affectTooltips"]["1"]=Txt::trad("readInfos").$affectAutor;}
				if(!empty($vDatas["affectLabels"]["1.5"]))	{$vDatas["affectTooltips"]["1.5"]=$this->tradObject("readLimitInfos").$affectAutor;}
				if(!empty($vDatas["affectLabels"]["2"]))	{$vDatas["affectTooltips"]["2"]=(static::isContainer())  ?  $this->tradObject("writeInfosContainer").$affectAutor  :  Txt::trad("writeInfos");}
			}
			////	AUTEUR ET DATE (optionnelle)
			//Init
			$vDatas["autorDateCrea"]=$vDatas["autorDateModif"]=null;
			$vDatas["isNewObject"]=(strtotime($this->dateCrea)>(time()-86400) || (Ctrl::$curUser->isUser() && strtotime($this->dateCrea)>Ctrl::$curUser->previousConnection));
			//Auteur + Date création + Nouvel objet (créé dans les 24 heures ou depuis la dernière connexion)
			if($this->_idUser)					{$vDatas["autorDateCrea"].="<div class='sLink' onclick=\"lightboxOpen('".Ctrl::getObj("user",$this->_idUser)->getUrl("vue")."');\">".$this->displayAutor()."</div>";}
			if($this->dateCrea)					{$vDatas["autorDateCrea"].=$this->displayDate(true,"full");}
			if($vDatas["isNewObject"]==true)	{$vDatas["autorDateCrea"].="<div><img src='app/img/newObj.png'> <abbr title=\"".Txt::trad("objNewInfos")."\">".Txt::trad("objNew")."</abbr></div>";}
			//Auteur + Date Modif (optionnelle)
			if(!empty($this->_idUserModif))  {$vDatas["autorDateModif"]="<div class='sLink' onclick=\"lightboxOpen('".Ctrl::getObj("user",$this->_idUserModif)->getUrl("vue")."');\">".$this->displayAutor(false)."</div>".$this->displayDate(false,"full");}
			////	USERS LIKES
			$vDatas["showMiscMenuClass"]=null;
			if($this->hasUsersLike() && Ctrl::$curUser->isUser())
			{
				$likeOptions=(Ctrl::$agora->usersLike=="likeOrNot")  ?  ["like","dontlike"]  :  ["like"];
				foreach($likeOptions as $likeOption){
					$likeMenuId="likeMenu_".$this->_targetObjId."_".$likeOption;//ID du menu. Exple: "likeMenu_news-55_dontlike". Cf. "usersLikeValidate()" dans le "common.js"
					$likeMenuNb=count($this->getUsersLike($likeOption));
					if(!empty($likeMenuNb))  {$vDatas["showMiscMenuClass"]="showMiscMenu";}
					$vDatas["likeMenu"][$likeOption]=["menuId"=>$likeMenuId, "likeDontLikeNb"=>$likeMenuNb];
				}
			}
			////	COMMENTAIRES
			if($this->hasUsersComment() && Ctrl::$curUser->isUser())
			{
				$commentNb=count($this->getUsersComment());
				$commentTooltip=$commentNb." ".Txt::trad($commentNb>1?"AGORA_usersComments":"AGORA_usersComment")." :<br>".Txt::trad("commentAdd");
				$commentsUrl="?ctrl=object&action=Comments&targetObjId=".$this->_targetObjId;
				if(!empty($commentNb))  {$vDatas["showMiscMenuClass"]="showMiscMenu";}
				$vDatas["commentMenu"]=["menuId"=>"commentMenu_".$this->_targetObjId, "commentNb"=>$commentNb, "commentTooltip"=>$commentTooltip, "commentsUrl"=>$commentsUrl];
			}
		}
		////	Affichage
		return Ctrl::getVue(Req::commonPath."VueObjMenuContext.php",$vDatas);
	}

	/*
	 * VUE DES OBJETS : AFFICHE LE MENU CONTEXTUEL  OU LE BOUTON D'EDITION
	 */
	public function menuContextEdit()
	{
		if(Req::isMobile())			{return $this->contextMenu(["iconBurger"=>"big"]);}
		elseif($this->editRight())  {return "<img src='app/img/edit.png' onclick=\"lightboxOpen('".$this->getUrl("edit")."')\" class='sLink lightboxMenuEdit' title=\"".Txt::trad("modify")."\">";}
	}

	/*
	 * VUE DES OBJETS & RESPONSIVE : TITRE "NOUVEL OBJET" ("nouveau fichier", "nouveau dossier", etc)
	 */
	public function editRespTitle($keyTrad)
	{
		if(Req::isMobile() && $this->isNew())  {echo "<div class='lightboxTitle'>".Txt::trad($keyTrad)."</div>";}
	}

	/*
	 * VUE : Menu d'édition (droits d'accès, fichiers joints, etc)
	 */
	public function menuEdit()
	{
		////	Menu des droits d'accès
		if(static::hasAccessRight==true && $this->isIndependant())
		{
			////	Init & Label
			$vDatas["accessRightMenu"]=true;
			$vDatas["accessRightMenuLabel"]=(static::isContainer())  ?  Txt::trad("EDIT_accessRightContent")." <img src='app/img/info.png' title=\"".$this->tradObject("autorPrivilege")."<hr>".Txt::trad("accessWriteLimit")." : ".$this->tradObject("readLimitInfos")."\">"  :  Txt::trad("EDIT_accessRight");
			////	Droits d'accès pour chaque espace ("targets")
			$vDatas["spacesAccessRight"]=[];
			foreach(Ctrl::$curUser->getSpaces() as $tmpSpace)
			{
				//Verif si le module de l'objet est bien activé sur l'espace
				if(array_key_exists(static::moduleName,$tmpSpace->moduleList()))
				{
					//Init
					$tmpSpace->targetLines=[];
					////	Tous les utilisateurs de l'espace  (..."et les invités" : si l'espace est public et que l'objet n'est pas un agenda perso)
					if(!empty($tmpSpace->public) && $this->type!="user")	{$allUsersLabel=Txt::trad("EDIT_allUsersAndGuests");	$allUsersLabelInfo=Txt::trad("EDIT_allUsersAndGuestsInfo");}
					else													{$allUsersLabel=Txt::trad("EDIT_allUsers");				$allUsersLabelInfo=Txt::trad("EDIT_allUsersInfo");}
					$tmpSpace->targetLines[]=["targetId"=>$tmpSpace->_id."_spaceUsers", "label"=>$allUsersLabel, "icon"=>"user/icon.png", "tooltip"=>str_replace("--SPACENAME--",$tmpSpace->name,$allUsersLabelInfo)];
					////	Groupe d'utilisateurs de l'espace
					foreach(MdlUserGroup::getGroups($tmpSpace) as $tmpGroup){
						$tmpSpace->targetLines[]=["targetId"=>$tmpSpace->_id."_G".$tmpGroup->_id, "label"=>$tmpGroup->title, "icon"=>"user/userGroup.png", "tooltip"=>Txt::reduce($tmpGroup->usersLabel)];
					}
					////	Chaque user de l'espace
					foreach($tmpSpace->getUsers() as $tmpUser){
						if($tmpSpace->userAccessRight($tmpUser)==2)	{$tmpUserFullAccess=true;	$tmpUserTooltip=Txt::trad("EDIT_adminSpace");}//Admin d'espace
						else										{$tmpUserFullAccess=false;	$tmpUserTooltip=null;}//User lambda
						$tmpSpace->targetLines[]=["targetId"=>$tmpSpace->_id."_U".$tmpUser->_id, "label"=>$tmpUser->getLabel(), "icon"=>"user/user.png", "tooltip"=>$tmpUserTooltip, "onlyFullAccess"=>$tmpUserFullAccess, "isUser"=>true];
					}
					////	Ajoute l'espace
					$vDatas["spacesAccessRight"][]=$tmpSpace;
				}
			}
			////	Prépare les targets de chaque espace
			$objAffects=$this->getAffectations();
			foreach($vDatas["spacesAccessRight"] as $tmpSpaceKey=>$tmpSpace)
			{
				foreach($tmpSpace->targetLines as $targetKey=>$targetLine)
				{
					//Init les propriétés des checkboxes (pas de "class"!). Utilise des "id" pour une sélection rapide des checkboxes par jQuery
					$targetId=$targetLine["targetId"];//exple : "1_spaceUsers" ou "2_G4
					foreach(["1","1.5","2"] as $tmpRight)
						{$targetLine["boxProp"][$tmpRight]="value=\"".$targetId."_".$tmpRight."\"  id=\"objectRightBox_".$targetId."_".str_replace('.','',$tmpRight)."\"";}//Utiliser "_15" au lieu de "_1.5" à cause du selector jQuery
					//Check une des boxes ?
					if(isset($objAffects[$targetId])){
						$tmpRight=(string)$objAffects[$targetId]["accessRight"];//Typer en 'string', pas 'float'
						$targetLine["boxProp"][$tmpRight].=" checked";
						$targetLine["isChecked"]=true;
					}
					//Disable des boxes ?
					if(!empty($targetLine["onlyFullAccess"]))	{$targetLine["boxProp"]["1"].=" disabled";  $targetLine["boxProp"]["1.5"].=" disabled";}
					if(!empty($targetLine["onlyReadAccess"]))	{$targetLine["boxProp"]["2"].=" disabled";  $targetLine["boxProp"]["1.5"].=" disabled";}
					//Met à jour les propriétés de la target ($targetKey est la concaténation des champs "_idSpace" et "target")
					$vDatas["spacesAccessRight"][$tmpSpaceKey]->targetLines[$targetKey]=$targetLine;
				}
			}
		}
		////	OPTION "FICHIERS JOINTS"
		if(static::hasAttachedFiles==true){
			$vDatas["attachedFiles"]=true;
			$vDatas["attachedFilesList"]=$this->getAttachedFileList();
		}
		////	OPTIONS NOTIFICATION PAR MAIL
		if(static::hasNotifMail==true && function_exists("mail")){
			$vDatas["moreOptions"]=$vDatas["notifMail"]=true;
			$vDatas["notifMailUsers"]=Ctrl::$curUser->usersVisibles(true);
			$vDatas["curSpaceUsersIds"]=Ctrl::$curSpace->getUsers("idsTab");
			$vDatas["curSpaceUserGroups"]=MdlUserGroup::getGroups(Ctrl::$curSpace);
		}
		////	OPTION "SHORTCUT"
		if(static::hasShortcut==true){
			$vDatas["moreOptions"]=$vDatas["shortcut"]=true;
			$vDatas["shortcutChecked"]=(!empty($this->shortcut)) ? "checked" : null;
		}
		////	AFFICHE LA VUE
		$vDatas["curObj"]=$this;
		$vDatas["writeReadLimitInfos"]=$this->tradObject("readLimitInfos");
		$vDatas["extendToSubfolders"]=(static::isFolder==true && $this->isNew()==false && Db::getVal("SELECT count(*) FROM ".static::dbTable." WHERE _idContainer=".$this->_id)>0)  ?  true  :  false;//dossier avec des sous-dossiers
		return Ctrl::getVue(Req::commonPath."VueObjMenuEdit.php",$vDatas);
	}

	/*
	 * STATIC : Clé de préférence en Bdd ($prefDbKey) : objet passé en parametre / conteneur ou dossier courant / module courant
	 */
	public static function getPrefDbKey($containerObj)
	{
		if(is_object($containerObj))		{return $containerObj->_targetObjId;}
		elseif(!empty(Ctrl::$curContainer))	{return Ctrl::$curContainer->_targetObjId;}
		else								{return static::moduleName;}
	}

	/*
	 * VUE : Menu de sélection d'objets (menu contextuel flottant)
	 */
	public static function menuSelectObjects()
	{
		if(Req::isMobile()==false){
			$vDatas["curFolderIsWritable"]=(is_object(Ctrl::$curContainer) && Ctrl::$curContainer->editContentRight())  ?  true  :  false;
			$vDatas["rootFolderHasTree"]=($vDatas["curFolderIsWritable"]==true && count(Ctrl::getObj(get_class(Ctrl::$curContainer),1)->folderTree())>1)  ?  true  :  false;
			return Ctrl::getVue(Req::commonPath."VueObjMenuSelection.php",$vDatas);
		}
	}

	/*
	 * STATIC : Tri d'objets : Preference en Bdd / paramètre passé en Get
	 * exple: "firstName@@asc"
	 */
	private static function getSort($containerObj=null)
	{
		//Récupère la préférence en Bdd ou params GET/POST
		$objectsSort=Ctrl::prefUser("sort_".static::getPrefDbKey($containerObj), "sort");
		//Tri par défaut si aucune préférence n'est précisé ou le tri sélectionné n'est pas dispo pour l'objet courant 
		if(empty($objectsSort) || !in_array($objectsSort,static::$sortFields))    {$objectsSort=static::$sortFields[0];}
		//renvoie le tri
		return $objectsSort;
	}

	/*
	 * STATIC SQL : Tri Sql des objets (avec premier tri si besoin : news, subject, etc)
	 * exple: "ORDER BY firstName asc"
	 */
	public static function sqlSort($firstSort=null)
	{
		//Init
		$firstSort=(!empty($firstSort))  ?  $firstSort.", "  :  null;							//Pré-tri ? Exple pour les News: "une desc"
		$sortTab=Txt::txt2tab(self::getSort(Ctrl::$curContainer));								//Récupère la préférence de tri du conteneur courant (dossier/sujet/etc). Exple: ["name","asc"]
		$fieldSort=($sortTab[0]=="extension") ? "SUBSTRING_INDEX(name,'.',-1)" : $sortTab[0];	//Tri par "extension" de fichier ?
		//Renvoie le tri Sql
		return "ORDER BY ".$firstSort." ".$fieldSort." ".$sortTab[1];
	}

	/*
	 * VUE : Menu de tri d'un type d'objet
	 */
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

	/*
	 * STATIC : Récupère le type d'affichage de la page
	 */
	public static function getDisplayMode($containerObj=null)
	{
		if(static::$displayModeCurrent===null)
		{
			//Affichage "block" privilégié (responsive)  OU  Récupère le mode d'affichage dans les préférences
			if(static::onlyBlockDisplayMode())	{static::$displayModeCurrent="block";}
			else								{static::$displayModeCurrent=Ctrl::prefUser("displayMode_".static::getPrefDbKey($containerObj),"displayMode");}
			//..Sinon on prend l'affichage par défaut
			if(empty(static::$displayModeCurrent))  {static::$displayModeCurrent=static::$displayModeOptions[0];}
		}
		return static::$displayModeCurrent;
	}

	/*
	 * STATIC : Sur mobile, on affiche toujours en mode "block" (si dispo)
	 */
	public static function onlyBlockDisplayMode()
	{
		return (Req::isMobile() && in_array("block",static::$displayModeOptions));
	}

	/*
	 * VUE : Menu d'affichage des objets dans une page : Blocks / Lignes (cf. $displayModeOptions)
	 */
	public static function menuDisplayMode($containerObj=null)
	{
		if(static::onlyBlockDisplayMode()==false)
		{
			$vDatas["displayModeOptions"]=static::$displayModeOptions;
			$vDatas["displayMode"]=static::getDisplayMode($containerObj);
			$vDatas["displayModeUrl"]=Tool::getParamsUrl("displayMode")."&displayMode=";
			return Ctrl::getVue(Req::commonPath."VueObjMenuDisplayMode.php",$vDatas);
		}
	}

	/*
	 * STATIC SQL : Filtrage de pagination
	 */
	public static function sqlPagination()
	{
		$offset=(Req::isParam("pageNb"))  ?  ((Req::getParam("pageNb")-1)*static::$pageNbObjects)  :  "0";
		return "LIMIT ".static::$pageNbObjects." OFFSET ".$offset;
	}

	/*
	 * VUE : Menu de filtre alphabétique (passe en parametre la requete sql pour récupérer les
	 */
	public static function menuPagination($displayedObjNb, $getParamKey=null)
	{
		$pageNbTotal=ceil($displayedObjNb/static::$pageNbObjects);
		if($pageNbTotal>1)
		{
			//Nb de page et numéro de page courant
			$vDatas["pageNbTotal"]=$pageNbTotal;
			$vDatas["pageNb"]=$pageNb=Req::isParam("pageNb") ? Req::getParam("pageNb") : 1;
			//Url de redirection de base
			$vDatas["hrefBase"]="?ctrl=".Req::$curCtrl;
			if(!empty($getParamKey) && Req::isParam($getParamKey))  {$vDatas["hrefBase"].="&".$getParamKey."=".Req::getParam($getParamKey);}
			$vDatas["hrefBase"].="&pageNb=";
			//Page Précédente / Suivante
			$vDatas["prevAttr"]=($pageNb>1)  ?  "href=\"".$vDatas["hrefBase"].((int)$pageNb-1)."\""  :  "class='vNavMenuDisabled'";
			$vDatas["nextAttr"]=($pageNb<$pageNbTotal)  ?  "href=\"".$vDatas["hrefBase"].((int)$pageNb+1)."\""  :  "class='vNavMenuDisabled'";
			//Récupère le menu
			return Ctrl::getVue(Req::commonPath."VueObjMenuPagination.php",$vDatas);
		}
	}

	/*
	 * Menu spécifique d'affectation aux espaces : Thèmes de forum / Categories d'evenement
	 */
	public function menuSpaceAffectation()
	{
		$vDatas["curObj"]=$this;
		////	Liste des espaces
		$vDatas["spaceList"]=Ctrl::$curUser->getSpaces();
		//Pour chaque espace : check espace affecté (déjà affecté à l'objet OU nouvel objet + espace courant)
		foreach($vDatas["spaceList"] as $tmpSpace){
			$tmpSpace->checked=(in_array($tmpSpace->_id,$this->spaceIds) || ($this->isNew() && $tmpSpace->isCurSpace()))  ?  "checked"  :  null;
		}
		////	pseudo Espace "Tous les espaces"
		if(Ctrl::$curUser->isAdminGeneral()){
			$spaceAllSpaces=new MdlSpace();
			$spaceAllSpaces->_id="all";
			$spaceAllSpaces->name=Txt::trad("visibleAllSpaces");
			$spaceAllSpaces->checked=(Ctrl::$curUser->isAdminGeneral() && $this->isNew()==false && empty($this->spaceIds))  ?  "checked"  :  null;//Check "tous les utilisateurs"?
			array_unshift($vDatas["spaceList"],$spaceAllSpaces);
		}
		//Affiche le menu
		$vDatas["displayMenu"]=(Ctrl::$curUser->isAdminGeneral() && count($vDatas["spaceList"])>1) ? true : false;
		return Ctrl::getVue(Req::commonPath."VueObjMenuSpaceAffectation.php",$vDatas);
	}
}