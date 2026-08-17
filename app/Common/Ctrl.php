<?php
/**
* This file is part of the Agora-Project Software package
*
* @copyleft Agora-Project <https://www.agora-project.net>
* @license GNU General Public License (GPL-2.0)
*/


/*
 * Controleur principal
 */
abstract class Ctrl
{
	//Propriétés de base
	const moduleName=null;
	public static $agora, $curUser, $curSpace;	//Parametrage principal
	public static $isMainPage=false;			//False : Iframe  || True : page principale avec barre de menu, etc
	public static $moduleOptions=[];			//Options du module courant
	public static $notify=[];					//Notifications à afficher
	public static $curTimezone=null;			//Timezone courante
	public static $userJustConnected=false;		//Controle si l'user vient de s'identifier / connecter
	public static $curContainer=null;			//Conteneur courant : dossier / sujet / agenda
	public static $curRootFolder=null;			//Dossier root du module courant
	protected static $initCtrlFull=true;		//Initialisation complete du controleur (connexion d'user, selection d'espace, etc)
	protected static $folderObjType=null;		//Module avec une arborescence
	protected static $cacheObjects=[];			//Objets mis en cache

	/********************************************************************************************************
	 * INIT LE CONTROLEUR PRINCIPAL (session, parametrages, connexion de l'user, etc)
	 ********************************************************************************************************/
	public static function initCtrl()
	{
		////	Mise en cache désactivée  &&  Lancement de session  &&  Réinit de session
		session_cache_limiter("nocache");
		if(defined("db_name"))  {session_name("SESSION_".db_name);}
		session_start();
		if(Req::isParam("disconnect")){
			$_SESSION=[];
			session_destroy();
			self::userAuthToken(false);
		}

		////	Parametrage général  &&  Update de BDD
		self::$agora=new MdlAgora();
		if(Req::isHost())  {Host::getParams();}
		DbUpdate::lauchUpdate();

		////	Init l'user et l'espace courant
		$_idUser =(!empty($_SESSION["_idUser"]))  ? $_SESSION["_idUser"]  : null;
		$_idSpace=(!empty($_SESSION["_idSpace"])) ? $_SESSION["_idSpace"] : null;
		self::$curUser=self::getObj("user",$_idUser);
		self::$curSpace=self::getObj("space",$_idSpace);

		////	Header ETag pour le controle du cache des serveurs/browsers  &&  Init le fuseau horaire
		header('ETag: "'.md5(Req::appVersion()).'"');
		self::$curTimezone=array_search(self::$agora->timezone,Tool::$tabTimezones);
		if(empty(self::$curTimezone))	{self::$curTimezone="Europe/Paris";}
		date_default_timezone_set(self::$curTimezone);

		////	Initialisation complète du controleur
		if(static::$initCtrlFull==true){
			////	Connection d'un user  &&  selection d'un espace !
			self::userConnectionSpaceSelection();

			////	Enregistre le cookie pour "Req::isMobileApp()"
			if(!empty($_GET["mobileAppli"])){
				setcookie("mobileAppli", "true", COOKIES_OPTIONS);
				$_COOKIE["mobileAppli"]="true";
			}

			////	Page principale d'un module  &&  Controle d'accès au module demandé
			if(Req::$curAction=="default")  {static::$isMainPage=true;}
			if(self::$curSpace->moduleEnabled(Req::$curCtrl)==false){													//Pas accès au module :
				if(static::$isMainPage==true)	{self::redir("index.php?ctrl=".key(self::$curSpace->moduleList()));}	//- redir vers le 1er module de l'espace
				else							{self::noAccessExit();}													//- message d'erreur + fin de script
			}

			////	Affichage des utilisateurs de l'espace courant / tous les users  &&  Affichage administrateur activé/désactivé
			if(empty($_SESSION["displayUsers"]))  {$_SESSION["displayUsers"]="space";}
			if(Req::isParam("displayAdmin") && self::$curUser->isSpaceAdmin()){
				$_SESSION["displayAdmin"]=(Req::param("displayAdmin")=="true");
				if($_SESSION["displayAdmin"]==true)	{self::notify(Txt::trad("HEADER_displayAdminEnabled")." : ".Txt::trad("HEADER_displayAdminInfo"));}
				else								{self::notify("HEADER_displayAdminDisabled");}
			}

			////	Charge l'objet courant : Objet passé en GET || Dossier racine par défaut
			if(Req::isParam("typeId"))				{$curObj=self::getCurObj();}
			elseif(static::$folderObjType!=null)	{$curObj=self::getObj(static::$folderObjType,1);}

			////	Controle d'accès à l'objet/container courant 
			if(!empty($curObj) && $curObj->isNew()==false){
				if($curObj->readRight()==false){																				//Pas accès à l'objet :
					if(static::$isMainPage==true)	{self::redir("index.php?ctrl=".Req::$curCtrl."&notify=inaccessibleElem");}	//- redir en page principale du module + notif
					else							{self::noAccessExit();}														//- message d'erreur + fin de script
				}
				elseif($curObj::isContainer())		{self::$curContainer=$curObj;}												//Charge le conteneur courant : dossier, agenda, etc
				elseif($curObj::isInContainer())	{self::$curContainer=$curObj->containerObj();}								//Charge le conteneur de l'objet courant : fichier, task, etc
				if(is_object(self::$curContainer) && self::$curContainer::isFolder==true)										//Arborescence : récupère le dossier root du module courant
					{self::$curRootFolder=self::getObj(self::$curContainer::objectType,1);}
			}
		}
	}

	/********************************************************************************************************
	 * CONNECTION D'UN USER  &&  SELECTION D'UN ESPACE
	 ********************************************************************************************************/
	public static function userConnectionSpaceSelection()
	{
		////	INIT
		$userAuthentified=false;
		$connectViaForm	=Req::isParam(["connectLogin","connectPassword"]);
		$connectViaToken=(!empty($_COOKIE["userAuthToken"]));

		////	CONNEXION D'UN USER (GUEST PAS ENCORE AUTHENTIFIÉ)
		if(self::$curUser->isGuest() && Req::isParam("disconnect")==false && ($connectViaForm==true || $connectViaToken==true)){

			////	CONNEXION VIA FORMULAIRE
			if($connectViaForm==true){
				$tmpUser=Db::getLine("SELECT * FROM ap_user WHERE `login`=".Db::param("connectLogin"));
				if(!empty($tmpUser)){
					$connectPassword=Req::param("connectPassword");
					$passwordVerifiedHash=password_verify($connectPassword,$tmpUser["password"]);				//Password hash vérifié
					$passwordVerifiedHost=(Req::isHost() && Host::passwordVerifyHost($connectPassword));		//Password du host vérifié
					if($passwordVerifiedHash==true || $passwordVerifiedHost==true)  {$userAuthentified=true;}	//Authentification OK
					if($passwordVerifiedHash==true)																//Update le hash Bcrypt : $passwordVerifiedHash uniquement !
						{Db::query("UPDATE ap_user SET `password`=".Db::format(password_hash($connectPassword,PASSWORD_DEFAULT))." WHERE `_id`=".Db::format($tmpUser["_id"]));}
				}
			}
			////	CONNEXION AUTO VIA TOKEN
			elseif($connectViaToken==true){
				$cookieToken=explode("@@@",$_COOKIE["userAuthToken"]);
				$tmpUser=Db::getLine("SELECT T1.*, T2.userAuthToken FROM ap_user T1, ap_userAuthToken T2 WHERE T1._id=T2._idUser AND T1._id=".Db::format($cookieToken[0])." AND T2.userAuthToken=".Db::format($cookieToken[1]));
				if(!empty($tmpUser))   {$userAuthentified=true;}
			}

			////	USER AUTHENTIFIE
			if($userAuthentified==true){
				//// Charge l'user courant (toujours en 1er)
				self::$curUser=self::getObj("user",(int)$tmpUser["_id"]);
				$_SESSION=["_idUser"=>self::$curUser->_id];
				self::$userJustConnected=true;
				self::addLog("connexion");

				//// Update "lastconnection" / "previousconnection" (connexion courante / précédente)
				$previousConnection=(!empty($tmpUser["lastConnection"]))  ?  $tmpUser["lastConnection"]  :  time();
				Db::query("UPDATE ap_user SET lastConnection='".time()."', previousConnection=".Db::format($previousConnection)." WHERE `_id`=".self::$curUser->_id);

				//// Charge les preferences de l'user en session
				foreach(Db::getTab("SELECT * FROM ap_userPreference WHERE _idUser=".self::$curUser->_id) as $tmpPref)
					{$_SESSION["pref"][$tmpPref["keyVal"]]=$tmpPref["value"];}

				//// (Re)initialise le token de connexion auto
				if($connectViaToken==true  || ($connectViaForm==true && Req::isParam("rememberMe")))
					{self::userAuthToken(true,self::$curUser->_id);}
			}
			////	ERREUR D'AUTHENTIFICATION
			else{
				if($connectViaForm==true)  {self::notify("NOTIF_identification");}
				self::redir("index.php?disconnect=1");
			}
		}

		////	STATS DE CONNEXION DU HOST (APRES AUTHENTIFICATION & AVANT SÉLECTION D'ESPACE AVEC REDIRECTION)
		if(Req::isHost())  {Host::connectStatsHostInfos();}

		////	SELECTION D'UN ESPACE  (Tester switch d'espace + connexion d'user sans espace affecté + connexion de guest avec switch d'espace + accès à un objet depuis notif mail)
		if(self::$userJustConnected==true  ||  (static::moduleName=="offline" && (self::$curUser->isUser() || Req::isParam("_idSpaceAccess")))){
			//// Init l'espace sélectionné et les espaces disponibles
			$idSpaceSelected=null;
			$userSpaces=self::$curUser->spaceList();

			//// Sélectionne un espace
			if(!empty($userSpaces)){
				//// Espace demandé (Switch d'espace || Accès Guest en page de connexion)
				if(Req::isParam("_idSpaceAccess")){
					foreach($userSpaces as $objSpace){
						if($objSpace->_id==Req::param("_idSpaceAccess")  &&  (self::$curUser->isUser() || empty($objSpace->password) || $objSpace->password==Req::param("password")))
							{$idSpaceSelected=$objSpace->_id;  break;}
					}
				}
				//// Espace par défaut d'un user
				elseif(self::$curUser->isUser()){
					//Espace enregistré dans les préférences de l'user
					if(!empty(self::$curUser->connectionSpace)){
						foreach($userSpaces as $objSpace){
							if($objSpace->_id==self::$curUser->connectionSpace)  {$idSpaceSelected=$objSpace->_id;  break;}
						}
					}
					//Tjs pas d'espace sélectionné : on prend le premier espace disponible
					if(empty($idSpaceSelected)){
						$firstSpace=reset($userSpaces);
						$idSpaceSelected=$firstSpace->_id;
					}
				}
			}

			//// Espace sélectionné : charge l'espace + redirection
			if(!empty($idSpaceSelected)){
				$_SESSION["_idSpace"]=$idSpaceSelected;																		//Charge l'espace courant
				$spaceModules=self::getObj("space",$idSpaceSelected)->moduleList();											//Récup les modules de l'espace courant		
				if(Req::isParam("objUrl"))		{self::redir(Req::param("objUrl"));}										//Redir vers un objet de l'espace (cf. "getUrlExternal()")
				elseif(!empty($spaceModules))	{self::redir("index.php?ctrl=".key($spaceModules));}						//Redir vers le premier module de l'espace
				else							{self::notify("NOTIF_noAccess");  self::redir("index.php?disconnect=1");}	//Aucun module disponible sur l'espace (notif et déconnexion)
			}
			//// User identifié mais affecté à aucun espace (notif et déconnexion)
			elseif(self::$userJustConnected==true)   {self::notify("NOTIF_noAccessNoSpaceAffected");  self::redir("index.php?disconnect=1");}
		}
		////	USER NON IDENTIFIÉ + AUCUN ESPACE PUBLIC DISPONIBLE (notif et déconnexion)
		elseif(empty(self::$curSpace->_id) && static::moduleName!="offline"){
			self::notify("NOTIF_noAccess");
			self::redir("index.php?disconnect=1");
		}
	}

	/********************************************************************************************************
	 * SUPPRIME/CREE LE TOKEN DE CONNEXION AUTOMATIQUE
	 ********************************************************************************************************/
	public static function userAuthToken($create, $_idUser=null)
	{
		////	Supprime un token : "delete" du token OU avant renouvellement du token avec "create"
		if(!empty($_COOKIE["userAuthToken"])){
			$cookieToken=explode("@@@",$_COOKIE["userAuthToken"]);																		//Récupère le token du cookie
			if(!empty($cookieToken[1]))  {Db::query("DELETE FROM ap_userAuthToken WHERE userAuthToken=".Db::format($cookieToken[1]));}	//Supprime le token correspondant dans la bdd
			setcookie("userAuthToken", "", -1);																							//Supprime le cookie du path courant
			setcookie("userAuthToken", "", -1, COOKIES_OPTIONS_PATH);																	//Idem cf. "createHost()"
			unset($_COOKIE["userAuthToken"]);																							//Idem
		}
		////	Créé un nouveau token : enregistre le token en bdd et dans un cookie
		if($create==true && !empty($_idUser)){
			require_once('app/misc/Browser.php');																						//Charge la classe "Browser()"
			$browserObj=new Browser();																									//Récup les infos du browser (mobile/desktop)
			$browserId=(is_object($browserObj))  ?  $browserObj->getBrowser()."-".$browserObj->getPlatform()  :  null;					//Identifie le browser et l'OS
			$userAuthToken=password_hash(uniqid(),PASSWORD_DEFAULT);																	//Créé un nouveau Token avec l'algo Bcrypt
			$cookieToken=$_idUser."@@@".$userAuthToken;																					//Créé le token du cookie
			setcookie("userAuthToken", $cookieToken, COOKIES_OPTIONS);																	//Enregistre le cookie
			$_COOKIE["userAuthToken"]=$cookieToken;																						//Charge le cookie
			Db::query("DELETE FROM ap_userAuthToken WHERE _idUser=".$_idUser." AND browserId=".Db::format($browserId));					//Supprime en bdd les tokens expirés du brower
			Db::query("INSERT INTO ap_userAuthToken SET _idUser=".$_idUser.", userAuthToken=".Db::format($userAuthToken).", browserId=".Db::format($browserId).", dateCrea=NOW()");
		}
		////	Supprime les tokens obsoletes
		Db::query("DELETE FROM ap_userAuthToken WHERE UNIX_TIMESTAMP(dateCrea) < ".(time()-TIME_1YEAR));
	}

	/**********************************************************************************************************************
	 * RÉCUPÈRE UNE PRÉFÉRENCE EN GET/POST OU BDD  (mode d'affichage, tri des résultats, etc)
	 **********************************************************************************************************************/
	public static function getPref($keyParam, $suffix=null)
	{
		$keyBdd=(!empty($suffix))  ?  $keyParam."_".$suffix  :  $keyParam;	//Ajoute le suffixe d'un objet ou d'un type d'objet
		if(Req::isParam($keyParam)){										//Valeur passé en GET/POST : enregistre/update en DB et SESSION
			$value=Req::param($keyParam);									//Récup la valeur
			if(is_array($value))  {$value=Txt::tab2txt($value);}			//Formate un array en txt
			$_SESSION["pref"][$keyBdd]=$value;								//Enregistre/update en session
			if(self::$curUser->isUser()){									//Delete/Insert en DB
				Db::query("DELETE FROM ap_userPreference WHERE _idUser=".self::$curUser->_id." AND keyVal=".Db::format($keyBdd));
				Db::query("INSERT INTO ap_userPreference SET   _idUser=".self::$curUser->_id.", keyVal=".Db::format($keyBdd).", `value`=".Db::format($value));
			}
		}
		//Renvoie la pref en session
		if(isset($_SESSION["pref"][$keyBdd]))  {return $_SESSION["pref"][$keyBdd];}
	}

	/********************************************************************************************************
     * GÉNÈRE UNE VUE
     ********************************************************************************************************/
	public static function getVue($vuePath, $vDatas=[])
	{
		if(file_exists($vuePath)){
			ob_start();				//Temporisation de sortie
			extract($vDatas);		//$vDatas["var"] => $var
			require $vuePath;		//Inclut le fichier vue
			return ob_get_clean();	//Renvoie du tampon de sortie
		}
		else{throw new Exception("File '".$vuePath."' unreachable");}
	}

	/********************************************************************************************************
	 * AFFICHE UNE PAGE COMPLETE (ENSEMBLE DE VUES)
	 ********************************************************************************************************/
	public static function displayPage($vuePathMain, $vDatasMain=[])
	{
		////	PAGE PRINCIPALE : AFFICHE LE HEADER, WALLPAPER, ETC.
		if(static::$isMainPage==true){
			//// HEADER & MESSENGER (sauf si ctrl externe)
			if(!in_array(Req::$curCtrl,["offline","misc"])){
				//Plugins "shortcuts" des modules
				$vDatasHeader["pluginsShortcut"]=[];
				foreach(self::$curSpace->moduleList() as $tmpModule){
					$modCtrl=$tmpModule["ctrl"];
					if(method_exists($modCtrl,"getPlugins"))  {$vDatasHeader["pluginsShortcut"]=array_merge($vDatasHeader["pluginsShortcut"], $modCtrl::getPlugins(["type"=>"shortcut"]));}
				}
				//HeaderMenu : Liste des espaces et modules +  Inscription d'utilisateurs  +  Affichage du label des modules
				$vDatasHeader["spaceList"]=self::$curUser->spaceList();
				$vDatasHeader["moduleList"]=self::$curSpace->moduleList();
				$vDatasHeader["userInscriptionValidate"]=(count(CtrlUser::userInscriptionValidate())>0);
				$vDatasHeader["moduleLabelDisplay"]=(!empty(self::$agora->moduleLabelDisplay) || Req::isMobile());
				$vDatasHeader["connectSpaceSwitchLabel"]=Req::connectSpaceSwitch()  ?  Txt::trad('connectSpaceSwitchInfo',true).' '.(Req::isHost()?HOST_DOMAINE:null)  :  null;
				$vDatas["headerMenu"]=self::getVue(Req::commonPath."VueHeaderMenu.php",$vDatasHeader);
				//Messenger (cf. "CtrlMisc::actionMessengerUpdate()")
				if(self::$curUser->messengerEnabled())  {$vDatas["messenger"]=self::getVue(Req::commonPath."VueMessenger.php");}
			}
			//// WALLPAPER & FOOTER
			if(Req::isMobile()==false){
				if(!empty(self::$curSpace->wallpaper))	{$vDatas["pathWallpaper"]=CtrlMisc::pathWallpaper(self::$curSpace->wallpaper);}
				elseif(!empty(self::$agora->wallpaper))	{$vDatas["pathWallpaper"]=CtrlMisc::pathWallpaper(self::$agora->wallpaper);}
				else									{$vDatas["pathWallpaper"]=CtrlMisc::pathWallpaper();}
				$vDatas["footerLogoUrl"]=(empty(self::$agora->logoUrl))  ?  OMNISPACE_URL_PUBLIC  :  self::$agora->logoUrl;
				$vDatas["footerLogoTooltip"]=Txt::trad("footerGeneratedTime")." ".round((microtime(true)-TPS_EXEC_BEGIN),2).' sec.';
			}
		}
		////	NOTIFS  +  RECUPERE LA VUE PRINCIPALE  +  AFFICHE LA VUE COMPLETE
		foreach((array)Req::param("notify") as $tmpNotif)  {self::notify($tmpNotif);}
		if(strstr($vuePathMain,Req::commonPath)==false)  {$vuePathMain=Req::curModPath().$vuePathMain;}//Path du module courant
		$vDatas["mainContent"]=self::getVue($vuePathMain, $vDatasMain);
		echo self::getVue(Req::commonPath."VueStructure.php",$vDatas);
	}

	/********************************************************************************************************
	 * AJOUTE UN LOG
	 * Action : "connexion", "add", "modif", "delete"
	 ********************************************************************************************************/
	public static function addLog($action, $curObj=null, $comment=null)
	{
		//S'il s'agit d'une action d'un user ou d'un invité qui ajoute un élément
		if(self::$curUser->isUser() || $action=="add"){
			////	Init la requête Sql
			$moduleName=Req::$curCtrl;
			$sqlObjectType=$sqlObjectId=null;
			////	Element : ajoute les détails (nom, titre, chemin, etc)
			if(MdlObject::isObject($curObj)){
				//init
				$moduleName=$curObj::moduleName;
				$sqlObjectType=$curObj::objectType;
				$sqlObjectId=$curObj->_id;
				if(!empty($comment))  {$comment.=" : ";}
				//Commentaire de base : nom / titre / description / adresse
				if(!empty($curObj->name))				{$comment.=$curObj->name;}
				elseif(!empty($curObj->title))			{$comment.=Txt::reduce($curObj->title);}
				elseif(!empty($curObj->description))	{$comment.=Txt::reduce($curObj->description);}
				elseif(!empty($curObj->adress))			{$comment.=Txt::reduce($curObj->adress);}
				//Ajoute si besoin le 'path' au format "zip" (minimaliste)
				if($curObj::isInArbo() && $curObj->isRootFolder()==false)  {$comment.=" (".Txt::trad("LOG_path")." : ".$curObj->containerObj()->folderPath("zip").")";}
				//800 caractères max en bdd
				$comment=Txt::reduce($comment,800);
			}
			////	Ajoute le log
			Db::query("INSERT INTO ap_log SET action=".Db::format($action).", moduleName=".Db::format($moduleName).", objectType=".Db::format($sqlObjectType).", _idObject=".Db::format($sqlObjectId).", `comment`=".Db::format($comment).", `date`=".Db::dateNow().", _idUser=".Db::format(self::$curUser->_id).", _idSpace=".Db::format(self::$curSpace->_id).", ip=".Db::format($_SERVER["REMOTE_ADDR"]));
			////	Supprime les anciens logs (lancé qu'une fois par session)
			if(empty($_SESSION["logsCleared"])){
				Db::query("DELETE FROM ap_log WHERE action='connexion'	AND UNIX_TIMESTAMP(date) <= ".intval(time()-(14*86400)));										 //Logs de connexion			: conservés 2 semaines
				Db::query("DELETE FROM ap_log WHERE action='delete'		AND UNIX_TIMESTAMP(date) <= ".intval(time()-(360*86400)));										 //logs de suppression			: conservés un an
				Db::query("DELETE FROM ap_log WHERE action NOT IN ('connexion','delete') AND UNIX_TIMESTAMP(date) <= ".intval(time()-(self::$agora->logsTimeOut*86400)));//Autres logs (add,modif,etc)	: en fonction du "logsTimeOut" (120j par défaut)
				$_SESSION["logsCleared"]=true;
			}
		}
	}


	/***************************************************************************************************************************/
	/************************************************   BASIC METHODS   ********************************************************/
	/***************************************************************************************************************************/


	/********************************************************************************************************
	 * RECUPÈRE UN OBJET (vérif si déjà en cache)
	 ********************************************************************************************************/
	public static function getObj($type, $values_id=null, $updateCache=false)
	{
		$MdlClass="Mdl".ucfirst($type);												//Modèle de l'objet (ex: "MdlFileFolder")
		if(!empty($values_id)){														//Objet existant : _id ou valeurs spécifiés
			$id=(isset($values_id["_id"])) ? $values_id["_id"] : (int)$values_id;	//Id de l'objet
			$typeId=$type."-".$id;													//typeId de l'objet (ex: "file-55")
			if(!isset(self::$cacheObjects[$typeId]) || $updateCache==true)			//Init ou Update l'objet en cache 
				{self::$cacheObjects[$typeId]=new $MdlClass($values_id);}
			return self::$cacheObjects[$typeId];									//Retourne l'objet en cache
		}
		else  {return new $MdlClass();}												//Nouvel objet 
	}

	/********************************************************************************************************
	 * RECUPÈRE UN OBJET VIA LE PARAMETRE "typeId"  (ex: "file-55")
	 ********************************************************************************************************/
	public static function getCurObj($typeIdParam=null)
	{
		$typeId=(!empty($typeIdParam)) ? $typeIdParam : Req::param("typeId"); 
		if(!empty($typeId)){
			$typeId=explode("-",$typeId);
			$curObj=self::getObj($typeId[0], $typeId[1] ?? null);	//Objet existant || Nouvel objet ($typeId[1] => 0 ou null)
			if($curObj->isNew()){																										//Nouvel objet :
				if(!empty($typeId[1]))  			{self::redir("index.php?ctrl=".static::moduleName."&notify[]=inaccessibleElem");}	//Objet inexistant/supprimé en DB : notif d'erreur
				if(Req::isParam("_idContainer"))	{$curObj->_idContainer=Req::param("_idContainer");}									//Ajoute "_idContainer" pour le controle d'accès via editRecord()
			}
			return $curObj;
		}
	}

	/********************************************************************************************************
	 * RECUPÈRE LES OBJETS EN GET/POST  (ex: objectsTypeId[file]=33-44-55)
	 ********************************************************************************************************/
	public static function getCurObjects($objTypeFilter=null)
	{
		$objects=[];
		if(Req::isParam("objectsTypeId") && is_array(Req::param("objectsTypeId"))){
			foreach(Req::param("objectsTypeId") as $objType=>$objectsId){				//Parcourt chaque objet
				if($objTypeFilter==null || $objType==$objTypeFilter){					//filtre si besoin par type d'objet
					foreach(explode("-",$objectsId) as $objId){							//Récupère l'_id des objets
						$tmpObj=self::getObj($objType, $objId);							//Charge l'objet
						if($tmpObj->readRight())  {$objects[]=$tmpObj;}					//Controle ok : ajoute à la liste
					}
				}
			}
		}
		return $objects;
	}

	/********************************************************************************************************
	 * REDIRIGE VERS L'ADRESSE DEMANDÉE : REDIRECTION SIMPLE OU SUR LA PAGE PRINCIPALE (IFRAME)
	 ********************************************************************************************************/
	public static function redir($url, $urlNotify=true)
	{
		if(!empty($url)){
			if($urlNotify==true)  {$url.=self::urlNotify();}				//Ajoute les notifs
			echo '<script> window.top.location.href="'.$url.'"; </script>';	//Redirection window.top (cf. "lightbox")
			exit;
		}
	}

	/********************************************************************************************************
	 * RELOAD LA PAGE PRINCIPALE DEPUIS UNE LIGHTBOX (ex: après edit d'objet)
	 ********************************************************************************************************/
	public static function lightboxRedir()
	{
		echo '<script src="app/Common/js-css-'.Req::appVersion().'/app.js"></script>'.	//Charge la librairie  JS
			  '<script>lightboxRedir("'.self::urlNotify().'");</script>';				//Lance lightboxRedir() avec les nouvelles notifications
		exit;
	}

	/********************************************************************************************************
	 * AJOUTE SI BESOIN LES "NOTIFY()" COURANTE À UNE URL DE REDIRECTION
	 ********************************************************************************************************/
	public static function urlNotify()
	{
		$urlNotify=null;
		foreach(self::$notify as $message)  {$urlNotify.="&notify[]=".urlencode($message["message"]);}
		return $urlNotify;
	}

	/********************************************************************************************************
	 * AJOUTE UNE NOTIFICATION À AFFICHER VIA "VUESTRUCTURE.PHP"
	 * $message : 	message spécifique  /  clé de traduction
	 * $type : 		"notice"  /  "success"  /  "warning"
	 ********************************************************************************************************/
	public static function notify($message, $type="notice")
	{
		if(Tool::arraySearch(self::$notify,$message)==false){		//Vérifie si le message n'est pas déjà dans la liste (évite les doublons de notif)
			self::$notify[]=["message"=>$message, "type"=>$type];	//Ajoute la notification au tableau "self::$notify"
		}
	}

	/********************************************************************************************************
	 * AFFICHE UN MESSAGE D'ERREUR  +  FIN DE SCRIPT
	 ********************************************************************************************************/
	public static function noAccessExit($keyTrad="inaccessibleElem")
	{
		echo "<h2><img src='app/img/important.png'> ".Txt::trad($keyTrad)."</h2>";
		exit;
	}
}