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
	//Type d'affichage en préference (ligne/block)
	protected static $_displayMode=null;

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
	public function menuId($prefix, $reloadUniqid=false)
	{
		if(empty($this->contextMenuId) || $reloadUniqid==true)  {$this->contextMenuId=Txt::uniqId();}//Un menu par instance de l'objet (Tester avec les evts récurrents ou les menus d'agendas)
		return $prefix."_".$this->contextMenuId;
	}

	/*
	 * VUE : Menu contextuel (édition, droit d'accès, etc)
	 * $options => "deleteLabel" / "specificOptions"
	 */
	public function contextMenu($options=null)
	{
		////	INIT  &  DIVERSES OPTIONS (exple: "array('actionJs'=>'?ctrl=file&action=monAction','iconSrc'=>'app/img/plus.png','label'=>'mon option spécifique','tooltip'=>'mon tooltip')")
		$vDatas["curObj"]=$this;
		$vDatas["inlineLauncher"]=(!empty($options["inlineLauncher"])) ? true : false;
		$vDatas["specificOptions"]=(!empty($options["specificOptions"])) ? $options["specificOptions"] : array();
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
				$deleteFromSpaceUrl="?ctrl=user&action=deleteFromCurSpace&targetObjects[".static::objectType."]=".$this->_id;
				$vDatas["confirmDeleteFromSpace"]="confirmDelete('".$deleteFromSpaceUrl."', '".Txt::trad("USER_confirmDeleteFromSpace",true)."')";
			}
			////	SUPPRESSION DEFINITIVE
			if($this->deleteRight()){
				$vDatas["confirmDeleteJs"]="confirmDelete('".$this->getUrl("delete")."', '".Txt::trad("confirmDeleteDbl",true)."')";
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
				$confirmDeleteParams=null;
				if(static::objectType=="space"){
					$confirmDeleteParams=", '".Txt::trad("SPACE_confirmDeleteDbl",true)."'";//Double confirmation
				}elseif(static::isContainer()){
					$confirmDeleteParams=", '".Txt::trad("confirmDeleteDbl",true)."'";//Double confirmation
					if(static::isFolder==true)  {$confirmDeleteParams.=", '?ctrl=object&action=SubFoldersDeleteControl&targetObjId=".$this->_targetObjId."', '".Txt::trad("confirmDeleteFolder",true)."'";}//Controle AJAX des droits d'accès
				}
				$vDatas["confirmDeleteJs"]="confirmDelete('".$this->getUrl("delete")."' ".$confirmDeleteParams.")";
				$vDatas["deleteLabel"]=(!empty($options["deleteLabel"]))  ?  $options["deleteLabel"]  :  Txt::trad("delete");
			}
			////	INFOS DES DROITS D'ACCESS (AUX ESPACES, USERS, ETC)
			if(static::hasAccessRight==true)
			{
				if(Ctrl::$curUser->isUser() && $this->isIndependant())
				{
					//Init
					$vDatas["isPersoAccess"]=true;
					$vDatas["affectLabels"]=$vDatas["affectTooltips"]=["1"=>null,"1.5"=>null,"2"=>null];
					//Libellé des affectations (& Icone d'element Perso?)
					foreach($this->getAffectations() as $tmpAffect){
						if(!empty($tmpAffect["targetType"]) && $tmpAffect["targetType"]!="user" || Ctrl::$curUser->_id!=$tmpAffect["target_id"])  {$vDatas["isPersoAccess"]=false;}
						$vDatas["affectLabels"][$tmpAffect["accessRight"]].=$tmpAffect["label"]."<br>";
					}
					foreach($vDatas["affectLabels"] as $affectRight=>$affectLabel)	{$vDatas["affectLabels"][$affectRight]=trim($affectLabel,", ");}//Enlève dernières virgules pour les affectation d'users
					//Tooltip des droits d'accès
					$affectAutor=(static::isContainer())  ?  "<hr>".$this->tradObject("autorPrivilege")  :  null;//"Seul l'auteur peut modifier les droits d'accès.."
					if(!empty($vDatas["affectLabels"]["1"]))	{$vDatas["affectTooltips"]["1"]=Txt::trad("readInfos");}
					if(!empty($vDatas["affectLabels"]["1.5"]))	{$vDatas["affectTooltips"]["1.5"]=$this->tradObject("readLimitInfos").$affectAutor;}
					if(!empty($vDatas["affectLabels"]["2"]))	{$vDatas["affectTooltips"]["2"]=(static::isContainer())  ?  $this->tradObject("writeInfosContainer").$affectAutor  :  Txt::trad("writeInfos");}//si c'est un conteneur : "possibilité de modifier tous les elements du dossier"
				}
			}
			////	AUTEUR ET DATE
			//Auteur + date création (optionnelle)
			$vDatas["infosCrea"]["autor"]=$this->displayAutor();
			$vDatas["infosCrea"]["date"]=(!empty($this->dateCrea)) ? "<br>".$this->displayDate(true,"full") : null;
			//Auteur + date Modif
			if(!empty($this->_idUserModif)){
				$vDatas["infosModif"]["autor"]=$this->displayAutor(false);
				$vDatas["infosModif"]["date"]="<br>".$this->displayDate(false,"full");
			}
			//Nouvel objet (créé depuis la dernière connexion)
			$dateCreaTime=strtotime($this->dateCrea);
			$vDatas["newObjectSinceConnection"]=(Ctrl::$curUser->isUser() && ($dateCreaTime>Ctrl::$curUser->previousConnection || $dateCreaTime>(time()-86400)))  ?  true  :  false;
			$vDatas["hideMiscMenu"]=true;
			////	USERS LIKES
			if($this->hasUsersLike() && Ctrl::$curUser->isUser())
			{
				$likeOptions=(Ctrl::$agora->usersLike=="likeOrNot")  ?  ["like","dontlike"]  :  ["like"];
				foreach($likeOptions as $likeOption){
					$likeMenuId="likeMenu_".$this->_targetObjId."_".$likeOption;//ID du menu. Exple: "likeMenu_news-55_dontlike". Cf. "usersLikeValidate()" dans le "common.js"
					$likeMenuNb=count($this->getUsersLike($likeOption));
					if(!empty($likeMenuNb) || static::dontHideMiscMenu==true)	{$vDatas["hideMiscMenu"]=false;}
					$vDatas["likeMenu"][$likeOption]=["menuId"=>$likeMenuId, "likeDontLikeNb"=>$likeMenuNb];
				}
			}
			////	COMMENTAIRES
			if($this->hasUsersComment() && Ctrl::$curUser->isUser())
			{
				$commentNb=count($this->getUsersComment());
				$commentTooltip=$commentNb." ".Txt::trad($commentNb>1?"AGORA_usersComments":"AGORA_usersComment")." :<br>".Txt::trad("commentAdd");
				$commentsUrl="?ctrl=object&action=Comments&targetObjId=".$this->_targetObjId;
				if(!empty($commentNb) || static::dontHideMiscMenu==true)	{$vDatas["hideMiscMenu"]=false;}
				$vDatas["commentMenu"]=["menuId"=>"commentMenu_".$this->_targetObjId, "commentNb"=>$commentNb, "commentTooltip"=>$commentTooltip, "commentsUrl"=>$commentsUrl];
			}
		}
		////	Affichage
		return Ctrl::getVue(Req::commonPath."VueObjMenuContext.php",$vDatas);
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
					$tmpSpace->targetLines[]=["targetId"=>$tmpSpace->_id."_spaceUsers", "label"=>$allUsersLabel."*", "icon"=>"user/icon.png", "tooltip"=>str_replace("--SPACENAME--",$tmpSpace->name,$allUsersLabelInfo)];
					////	Groupe d'utilisateurs de l'espace
					foreach(MdlUserGroup::getGroups($tmpSpace) as $tmpGroup){
						$tmpSpace->targetLines[]=["targetId"=>$tmpSpace->_id."_G".$tmpGroup->_id, "label"=>$tmpGroup->title, "icon"=>"user/userGroup.png", "tooltip"=>Txt::reduce($tmpGroup->usersLabel)];
					}
					////	Chaque user de l'espace
					foreach($tmpSpace->getUsers() as $tmpUser){
						$curUserTooltip=($tmpSpace->userAccessRight($tmpUser)==2)  ?  Txt::trad("EDIT_adminSpace")  :  null;
						$curUserFullAccess=($tmpSpace->userAccessRight($tmpUser)==2 || $tmpUser->_id==Ctrl::$curUser->_id)  ?  true  :  false;
						$tmpSpace->targetLines[]=["targetId"=>$tmpSpace->_id."_U".$tmpUser->_id, "label"=>$tmpUser->getLabel(), "tooltip"=>$curUserTooltip, "onlyFullAccess"=>$curUserFullAccess, "isUser"=>true];
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
			$vDatas["curSpaceUsersIds"]=Ctrl::$curSpace->getUsers("ids");
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
		$vDatas["curFolderIsWritable"]=(is_object(Ctrl::$curContainer) && Ctrl::$curContainer->editContentRight())  ?  true  :  false;
		$vDatas["rootFolderHasTree"]=($vDatas["curFolderIsWritable"]==true && count(Ctrl::getObj(get_class(Ctrl::$curContainer),1)->folderTree())>1)  ?  true  :  false;
		return Ctrl::getVue(Req::commonPath."VueObjMenuSelection.php",$vDatas);
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
		if(static::$_displayMode===null){
			static::$_displayMode=Ctrl::prefUser("displayMode_".static::getPrefDbKey($containerObj), "displayMode");
			if(empty(static::$_displayMode))  {static::$_displayMode=static::$displayModeOptions[0];}//Affichage par défaut
		}
		return static::$_displayMode;
	}

	/*
	 * VUE : Menu d'affichage des objets dans une page : Blocks / Lignes (cf. $displayModeOptions)
	 */
	public static function menuDisplayMode($containerObj=null)
	{
		$vDatas["displayModeOptions"]=static::$displayModeOptions;
		$vDatas["displayMode"]=static::getDisplayMode($containerObj);
		$vDatas["displayModeUrl"]=Tool::getParamsUrl("displayMode")."&displayMode=";
		return Ctrl::getVue(Req::commonPath."VueObjMenuDisplayMode.php",$vDatas);
	}

	/*
	 * STATIC SQL : Filtrage de pagination
	 */
	public static function sqlPagination()
	{
		$offset=(Req::isParam("pageNb"))  ?  ((Req::getParam("pageNb")-1)*static::nbObjectsByPage)  :  "0";
		return "LIMIT ".static::nbObjectsByPage." OFFSET ".$offset;
	}

	/*
	 * VUE : Menu de filtre alphabétique (passe en parametre la requete sql pour récupérer les
	 */
	public static function menuPagination($displayedObjNb, $getParamKey=null)
	{
		$pageNbTotal=ceil($displayedObjNb/static::nbObjectsByPage);
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