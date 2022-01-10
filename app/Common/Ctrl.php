<?php
/**
* This file is part of the Agora-Project Software package.
*
* @copyright (c) Agora-Project Limited <https://www.agora-project.net>
* @license GNU General Public License, version 2 (GPL-2.0)
*/


/*
 * Controleur principal
 */
abstract class Ctrl
{
	//Propriétés de base
	const moduleName=null;
	public static $moduleOptions=[];
	public static $agora, $curUser, $curSpace;
	public static $isMainPage=false;			//Page principale ou Iframe
	public static $userHasConnected=false;		//Controle si l'user vient de s'identifier / connecter
	public static $isMenuSelectObjects=false;	//Menu de sélection d'objets affiché ?
	public static $curContainer=null;			//Conteneur courant (dossier/sujet/agenda..)
	public static $curContainerRoot=null;		//Conteneur dossier root
	public static $curTimezone=null;			//Timezone courante
	public static $notify=[];					//Messages de Notifications (cf. Vues)
	protected static $initCtrlFull=true;		//Initialisation complete du controleur (connexion d'user, selection d'espace, etc)
	protected static $folderObjType=null;		//Module avec une arborescence
	protected static $cacheObjects=[];			//Objets mis en cache !

	/*******************************************************************************************
	 * INITIALISE LE CONTROLEUR PRINCIPAL (session, parametrages, connexion de l'user, etc)
	 *******************************************************************************************/
	public static function initCtrl()
	{
		////	Init la session
		if(defined("db_name"))  {session_name("SESSION_".db_name);}//Une session pour chaque espace et DB du serveur
		session_cache_limiter("nocache");
		session_start();

		////	Déconnexion : reinit les valeurs de session
		if(Req::isParam("disconnect")){
			$_SESSION=[];
			session_destroy();
			setcookie("AGORAP_PASS", null, -1);
		}

		////	Controle du cache du navigateur (Complété avec un .htaccess "mod_expires" pour les images & co)
		header("Etag: W/\"".md5(VERSION_AGORA)."\"");//"Etag" via VERSION_AGORA

		////	Toujours après "session_start" : Mise à jour si besoin  &&  Récup le parametrage de l'agora  &&  Récup le parametrage du host si besoin
		DbUpdate::lauchUpdate();
		self::$agora=self::getObj("agora");
		if(self::isHost())  {Host::agoraParams();}

		////	Init le fuseau horaire
		self::$curTimezone=array_search(self::$agora->timezone,Tool::$tabTimezones);
		if(empty(self::$curTimezone))	{self::$curTimezone="Europe/Paris";}
		date_default_timezone_set(self::$curTimezone);

		////	Init l'user et l'espace courant (..tjs après init de session)
		$_idUser=(!empty($_SESSION["_idUser"])) ? $_SESSION["_idUser"] : null;
		$_idSpace=(!empty($_SESSION["_idSpace"])) ? $_SESSION["_idSpace"] : null;
		self::$curUser=self::getObj("user",$_idUser);
		self::$curSpace=self::getObj("space",$_idSpace);

		////	Init complète du controleur : connexion de l'user/invité, selection d'espace, etc
		if(static::$initCtrlFull==true)
		{
			////	Connection d'un user et selection d'un espace ?
			self::userConnectionSpaceSelection();
			////	Chargement des trads et des "locales"
			Txt::loadTrads();
			////	Affiche une page principale (Controles d'accès au module, Menu principal, Footer, etc)
			if(Req::$curAction=="default")  {static::$isMainPage=true;}
			////	Controle d'accès au module de l'espace (l'user souhaite afficher un module "standard" qui n'est pas affecté à l'espace courant : redirige vers le premier module de l'espace)
			if(static::$isMainPage==true && !in_array(Req::$curCtrl,["agora","log","offline","space","user"]) && !array_key_exists(Req::$curCtrl,self::$curSpace->moduleList()))  {self::redir("?ctrl=".key(self::$curSpace->moduleList()));}
			////	Affichage administrateur demandé : switch l'affichage et "cast" la valeur en booléen (pas de "boolval()"..)
			if(self::$curUser->isAdminSpace() && Req::isParam("displayAdmin")){
				$_SESSION["displayAdmin"]=(Req::param("displayAdmin")=="true");
				if($_SESSION["displayAdmin"]==true)  {Ctrl::notify(Txt::trad("HEADER_displayAdminEnabled")." : ".Txt::trad("HEADER_displayAdminInfo"));}
			}
			////	Affichage des utilisateurs : space/all
			if(empty($_SESSION["displayUsers"]))  {$_SESSION["displayUsers"]="space";}
			////	Charge l'objet courant (toujours après "loadTrads()"!)
			if(Req::isParam("typeId"))				{$curObj=self::getObjTarget();}						//Objet passé en GET
			elseif(static::$folderObjType!==null)	{$curObj=self::getObj(static::$folderObjType,1);}	//Dossier racine par défaut
			////	Objet courant (dejà existant)
			if(!empty($curObj) && $curObj->isNew()==false){
				if($curObj->readRight()==false)  {static::$isMainPage==true ? self::redir("?ctrl=".Req::$curCtrl) : self::noAccessExit();}	//Controle d'accès : redirige vers le Ctrl principal ou affiche une notif d'erreur
				if($curObj::isContainer())  {self::$curContainer=$curObj;}																	//Charge le conteneur courant (dossier/sujet/agenda..)
				if($curObj::isFolder==true)  {self::$curContainerRoot=self::getObj(get_class($curObj),1);}									//Charge le dossier root
			}
		}
	}

	/*******************************************************************************************
     * GÉNÈRE UNE VUE  (cf. paramètres $datas)
     *******************************************************************************************/
	public static function getVue($filePath, $datas=array())
	{
		if(file_exists($filePath)){
			ob_start();				//Démarre la temporisation de sortie
			extract($datas);		//On rend les $datas accessibles à la vue ($data["monParametre"] devient $monParametre)
			require $filePath;		//Inclut le fichier vue
			return ob_get_clean();	//Renvoie du tampon de sortie
		}
		else{throw new Exception("File '".$filePath."' unreachable");}
	}

	/*******************************************************************************************
	 * AFFICHE UNE PAGE COMPLETE (ENSEMBLE DE VUES)
	 *******************************************************************************************/
	protected static function displayPage($fileMainVue, $vDatasMainVue=array())
	{
		////	PAGE PRINCIPALE : WALLPAPER, HEADER, ETC.
		if(static::$isMainPage==true)
		{
			//// WALLPAPER & LOGO FOOTER
			if(!empty(self::$curSpace->wallpaper))	{$vDatas["pathWallpaper"]=CtrlMisc::pathWallpaper(self::$curSpace->wallpaper);}
			elseif(!empty(self::$agora->wallpaper))	{$vDatas["pathWallpaper"]=CtrlMisc::pathWallpaper(self::$agora->wallpaper);}
			else									{$vDatas["pathWallpaper"]=CtrlMisc::pathWallpaper();}
			$vDatas["pathLogoUrl"]=(empty(self::$agora->logoUrl))  ?  OMNISPACE_URL_PUBLIC  :  self::$agora->logoUrl;
			$vDatas["pathLogoTitle"]="<div style='text-align:center;line-height:25px;'>".OMNISPACE_URL_LABEL."</div>".Txt::trad("FOOTER_pageGenerated")." ".round((microtime(true)-TPS_EXEC_BEGIN),3)." seconde";
			//// HEADERMENU & MESSENGER
			if(static::moduleName!="offline")
			{
				//Espace Disk
				$vDatasHeader["diskSpacePercent"]=ceil((File::datasFolderSize()/limite_espace_disque)*100);
				$vDatasHeader["diskSpaceAlert"]=($vDatasHeader["diskSpacePercent"]>70);
				//Récupère les plugins "shortcuts" de chaque module
				$vDatasHeader["pluginsShortcut"]=[];
				foreach(self::$curSpace->moduleList() as $tmpModule){
					if(method_exists($tmpModule["ctrl"],"getPlugins"))  {$vDatasHeader["pluginsShortcut"]=array_merge($vDatasHeader["pluginsShortcut"], $tmpModule["ctrl"]::getPlugins(["type"=>"shortcut"]));}
				}
				//Validation d'inscription d'utilisateurs  && Affiche la liste des espaces  && Liste des modules (Url, Description, Libellé, Class de l'icone)
				$vDatasHeader["userInscriptionValidate"]=(count(CtrlUser::userInscriptionValidate())>0);
				$vDatasHeader["showSpaceList"]=(count(Ctrl::$curUser->getSpaces())>1);
				$vDatasHeader["moduleList"]=self::$curSpace->moduleList();
				foreach($vDatasHeader["moduleList"] as $moduleKey=>$tmpModule)	{$vDatasHeader["moduleList"][$moduleKey]["isCurModule"]=($tmpModule["moduleName"]==static::moduleName);}
				//Récupère le menu principal : "HeaderMenu"
				$vDatas["headerMenu"]=self::getVue(Req::commonPath."VueHeaderMenu.php",$vDatasHeader);
				//Récupère le Messenger (cf. "CtrlMisc::actionMessengerUpdate()")
				if(self::$curUser->messengerEnabled())  {$vDatas["messenger"]=self::getVue(Req::commonPath."VueMessenger.php");}
			}
		}
		////	SKIN DE LA PAGE
		$vDatas["skinCss"]=(!empty(self::$agora->skin) && self::$agora->skin=="black")  ?  "black"  :  "white";
		////	NOTIFICATIONS PASSÉES EN GET/POST
		if(Req::isParam("notify")){
			foreach(Req::param("notify") as $tmpNotif)  {self::notify($tmpNotif);}
		}
		////	AFFICHE LA VUE
		$pathVue=(strstr($fileMainVue,Req::commonPath)==false)  ?  Req::curModPath()  :  null;//"app/Common/" déjà précisé?
		$vDatas["mainContent"]=self::getVue($pathVue.$fileMainVue, $vDatasMainVue);
		echo self::getVue(Req::commonPath."VueStructure.php",$vDatas);
	}

	/*******************************************************************************************
	 * AJOUTE UNE NOTIFICATION À AFFICHER VIA "VUESTRUCTURE.PHP"
	 * $message : message spécifique OU clé de traduction
	 * $type : "info" / "success" / "warning"
	 *******************************************************************************************/
	public static function notify($messageTrad, $type="notice")
	{
		//Ajoute la notification au tableau "self::$notify" si elle n'est pas déjà présente
		if(Tool::arraySearch(self::$notify,$messageTrad)==false)  {self::$notify[]=["message"=>$messageTrad,"type"=>$type];}
	}

	/*******************************************************************************************
	 * REDIRIGE À L'ADRESSE DEMANDÉE DEPUIS UNE IFRAME OU UNE PAGE PRINCIPALE
	 *******************************************************************************************/
	public static function redir($urlRedir)
	{
		$redirUrl=$urlRedir.self::urlNotify();																	//Ajoute si besoin les notifs
		if(static::$isMainPage==true)	{header("Location: ".$redirUrl);}										//Redir depuis la page principale
		else							{echo "<script> parent.location.href=\"".$redirUrl."\"; </script>";}	//Redir depuis une Iframe (cf. "CtrlObject::actionDelete()")
		exit;																									//Fin de script
	}

	/*******************************************************************************************
	 * FERME LE LIGHTBOX VIA JS (exple: après édit d'objet)
	 *******************************************************************************************/
	public static function lightboxClose($urlRedir=null, $urlParms=null)
	{
		echo '<script src="app/js/common-'.VERSION_AGORA.'.js"></script>
			  <script>lightboxClose("'.$urlRedir.'","'.$urlParms.self::urlNotify().'");</script>';
		exit;
	}

	/********************************************************************************************
	 * AJOUTE SI BESOIN LES "NOTIFY()" COURANTE À UNE URL DE REDIRECTION
	 ********************************************************************************************/
	public static function urlNotify()
	{
		$urlNotify=null;
		foreach(self::$notify as $message)  {$urlNotify.="&notify[]=".urlencode($message["message"]);}
		return $urlNotify;
	}

	/*******************************************************************************************
	 * AFFICHE "ELEMENT INACCESSIBLE" (OU AUTRE) & FIN DE SCRIPT
	 *******************************************************************************************/
	public static function noAccessExit($message=null)
	{
		if($message===null)  {$message=Txt::trad("inaccessibleElem");}
		echo "<h2><img src='app/img/important.png' style='vertical-align:middle;'> ".$message."</h2>";
		exit;
	}

	/*******************************************************************************************
	 * VÉRIF SI ON EST SUR UN HOST
	 *******************************************************************************************/
	public static function isHost()
	{
		return defined("HOST_DOMAINE");
	}

	/*******************************************************************************************
	 * RECUPÈRE UN OBJET (vérifie s'il est déjà en cache)
	 *******************************************************************************************/
	public static function getObj($objTypeOrMdl, $objIdOrValues=null, $updateCache=false)
	{
		//Récupère le modèle de l'objet (exple si on passe en paramètre uniquement le "type" de l'objet : "fileFolder" => "MdlFileFolder")
		$MdlClass=(preg_match("/^Mdl/i",$objTypeOrMdl))  ?  $objTypeOrMdl  :  "Mdl".ucfirst($objTypeOrMdl);
		//Retourne un nouvel objet OU un objet existant (déjà en cache?)
		if(empty($objIdOrValues))	{return new $MdlClass();}
		else
		{
			//Id de l'objet && clé de l'objet en cache
			$objId=(!empty($objIdOrValues["_id"]))  ?  $objIdOrValues["_id"]  :  (int)$objIdOrValues;
			$cacheKey=$MdlClass::objectType."-".$objId;
			//Ajoute/Update l'objet en cache?
			if(isset(self::$cacheObjects[$cacheKey])==false || $updateCache==true)  {self::$cacheObjects[$cacheKey]=new $MdlClass($objIdOrValues);}
			//Retourne l'objet en cache
			return self::$cacheObjects[$cacheKey];
		}
	}

	/*******************************************************************************************
	 * RECUPÈRE L'OBJET PASSÉ EN GET/POST OU EN ARGUMENT (ex: "typeId=fileFolder-55")
	 ******************************************************************************************/
	public static function getObjTarget($typeId=null)
	{
		if(Req::isParam("typeId") || !empty($typeId)){
			$typeId=(!empty($typeId))  ?  explode("-",$typeId)  :  explode("-",Req::param("typeId"));								//Récupère le "typeId" de l'objet (vérifier en premier si ya un argument!)
			$isNewObj=(empty($typeId[1]));																							//Vérif si c'est un nouvel objet
			$curObj=($isNewObj==true)  ?  self::getObj($typeId[0])  :  self::getObj($typeId[0],$typeId[1]);							//Charge un nouvel objet OU un objet existant
			if($isNewObj==false && $curObj->_id==0)  {self::notify("inaccessibleElem");  self::redir("?ctrl=".static::moduleName);}	//Objet inexistant/supprimé en BDD : renvoie une erreur
			if($isNewObj==true && Req::isParam("_idContainer"))  {$curObj->_idContainer=Req::param("_idContainer");}				//Ajoute si besoin "_idContainer" pour le controle d'accès d'un nouvel objet (cf. "createUpdate()" puis "createRight()")
			return $curObj;																											//Renvoie l'objet
		}
	}

	/*******************************************************************************************
	 * RECUPÈRE LES OBJETS ENVOYÉS VIA GET/POST  (ex: objectsTypeId[file]=2-4-6)
	 *******************************************************************************************/
	public static function getObjectsTypeId($objTypeFilter=null)
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

	/*******************************************************************************************
	 * CONNECTION D'UN USER ET SELECTION D'UN ESPACE ?
	 *******************************************************************************************/
	public static function userConnectionSpaceSelection()
	{
		////	CONNEXION D'UN USER (demandée ou auto)
		$connectViaForm=Req::isParam(["connectLogin","connectPassword"]);
		$connectViaCookie=(!empty($_COOKIE["AGORAP_LOG"]) && !empty($_COOKIE["AGORAP_PASS"]) && Req::isParam("disconnect")==false);
		if(self::$curUser->isUser()==false  &&  ($connectViaForm==true || $connectViaCookie==true))
		{
			//// IDENTIFICATION ET CONTROLES D'ACCES
			//Connexion demandé ou auto
			if($connectViaForm==true)		{$login=Req::param("connectLogin");  $passwordSha1=MdlUser::passwordSha1(Req::param("connectPassword"));}
			elseif($connectViaCookie==true)	{$login=$_COOKIE["AGORAP_LOG"];			$passwordSha1=$_COOKIE["AGORAP_PASS"];}
			//Identification + recup des infos sur l'user
			$sqlPasswordSha1="AND `password`=".Db::format($passwordSha1);
			if(self::isHost())  {$sqlPasswordSha1=Host::sqlPassword(Req::param("connectPassword"),$sqlPasswordSha1);}
			$tmpUser=Db::getLine("SELECT * FROM ap_user WHERE `login`=".Db::format($login)." ".$sqlPasswordSha1);
			//User pas identifié : message d'erreur et déconnexion
			if(empty($tmpUser))   {self::notify("NOTIF_identification");  self::redir("?disconnect=1");}
			//User déjà connecté avec une autre IP (appli non concerné)
			if(Req::isMobileApp()==false){
				$autreIpConnected=Db::getVal("SELECT count(*) FROM ap_userLivecouter WHERE _idUser=".(int)$tmpUser["_id"]." AND `date` > '".(time()-60)."' AND ipAdress NOT LIKE '".$_SERVER["REMOTE_ADDR"]."'");
				if($autreIpConnected>0)   {self::notify("NOTIF_presentIp");  self::redir("?disconnect=1");}
			}

			//// INIT L'USER
			//Init la session
			$_SESSION=["_idUser"=>(int)$tmpUser["_id"]];//Id du client
			//Maj les dates de "lastConnection" && "previousConnection"
			$previousConnection=(!empty($tmpUser["lastConnection"]))  ?  $tmpUser["lastConnection"]  :  time();
			Db::query("UPDATE ap_user SET lastConnection='".time()."', previousConnection=".Db::format($previousConnection)." WHERE _id=".(int)$tmpUser["_id"]);
			//Charge l'user courant!
			self::$curUser=self::getObj("user",$_SESSION["_idUser"]);
			self::$userHasConnected=true;
			self::addLog("connexion");
			//Récupère les préférences
			foreach(Db::getTab("select * from ap_userPreference where _idUser=".self::$curUser->_id) as $tmpPref)  {$_SESSION["pref"][$tmpPref["keyVal"]]=$tmpPref["value"];}
			//Enregistre login & password pour une connexion auto (pour 10ans)
			if(Req::isParam("rememberMe")){
				setcookie("AGORAP_LOG", $login, (time()+315360000));
				setcookie("AGORAP_PASS", $passwordSha1, (time()+315360000));
			}
		}

		////	STATS DE CONNEXION DU HOST (après la connexion && avant la sélection d'un espace qui redirige)
		if(self::isHost())  {Host::connectStatsHostInfos();}

		////	SELECTION D'UN ESPACE  (l'user vient de se connecter  ||  (page de connexion && (user déjà connecté || espace demandé par guest/notif mail)))
		////	=> tester le switch d'espace d'un user + connexion d'un user affecté à aucun espace + connexion d'un guest et switch d'espace + notif mail d'objet en mode connecté et déconnecté
		if(self::$userHasConnected==true  ||  (static::moduleName=="offline" && (self::$curUser->isUser() || Req::isParam("_idSpaceAccess"))))
		{
			//// Init
			$idSpaceSelected=null;													//Init l'espace sélectionné
			$userSpaces=self::$curUser->getSpaces();								//Espaces disponibles pour l'user courant ou le guest
			$isNotifMail=(static::moduleName=="offline" && Req::isParam("objUrl"));	//Accès depuis une notif mail d'objet (cf. "MdlObject::getUrlExternal()")
			//// Sélectionne un espace
			if(!empty($userSpaces))
			{
				//// Espace demandé :  L'user switch d'espace  ||  Accès depuis une notif mail d'objet (user identifié)  ||  Accès Guest
				if(Req::isParam("_idSpaceAccess")){
					foreach($userSpaces as $objSpace){
						if($objSpace->_id==Req::param("_idSpaceAccess") && (self::$curUser->isUser() || empty($objSpace->password) || $objSpace->password==Req::param("password")))
							{$idSpaceSelected=$objSpace->_id;  break;}
					}
				}
				//// Espace par défaut d'un user
				elseif(self::$curUser->isUser())
				{
					//Espace enregistré dans les préférences des l'user
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
			//// Espace sélectionné : charge l'espace + redirection vers le module principal
			if(!empty($idSpaceSelected)){
				$_SESSION["_idSpace"]=$idSpaceSelected;																					//Enregistre l'espace courant
				$spaceModules=self::getObj("space",$idSpaceSelected)->moduleList();														//Récup les modules de l'espace courant
				if($isNotifMail==true && self::$curUser->isUser())	{self::redir(Req::param("objUrl"));}								//Redir vers le controleur et l'objet demandé (notif mail d'objet)
				if(!empty($spaceModules))							{self::redir("?ctrl=".key($spaceModules));}							//Redir vers le premier module de l'espace
				else												{self::notify("NOTIF_noAccess");  self::redir("?disconnect=1");}	//Aucun module disponible sur l'espace : message d'erreur et déconnexion
			}
			//// User identifié mais affecté à aucun espace : message d'erreur et déconnexion
			elseif(self::$userHasConnected==true && $isNotifMail==false)   {self::notify("NOTIF_noSpaceAccess");  self::redir("?disconnect=1");}
		}
		//// User non identifié + aucun espace public disponible : message d'erreur et déconnexion
		elseif(empty(self::$curSpace->_id) && static::moduleName!="offline")  {self::notify("NOTIF_noAccess");  self::redir("?disconnect=1");}
	}

	/*******************************************************************************************
	 * RÉCUPÈRE UNE PRÉFÉRENCE  (tri des résultats/type d'affichage/etc)
	 * Passé en parametre GET/POST ? Enregistre en BDD ?
	 *******************************************************************************************/
	public static function prefUser($prefDbKey, $prefParamKey=null, $emptyValueEnabled=false)
	{
		//Clé identique en BDD et en GET-POST ?
		if(empty($prefParamKey))  {$prefParamKey=$prefDbKey;}
		//Préférence passé en Get/Post ?
		if(Req::isParam($prefParamKey))										{$prefParamVal=Req::param($prefParamKey);}
		elseif($emptyValueEnabled==true && Req::isParam("formValidate"))	{$prefParamVal="";}//Enregistre une valeur vide? (exple: checkbox non cochée dans un formulaire)
		//Enregistre si besoin la préférence  ("isset" pour aussi enregistrer les valeurs vides) 
		if(isset($prefParamVal))
		{
			//Formate la valeur
			if(is_array($prefParamVal))  {$prefParamVal=Txt::tab2txt($prefParamVal);}
			//User : enregistre en Bdd
			if(self::$curUser->isUser()){
				Db::query("DELETE FROM ap_userPreference WHERE _idUser=".self::$curUser->_id." AND keyVal=".Db::format($prefDbKey));
				Db::query("INSERT INTO ap_userPreference SET _idUser=".self::$curUser->_id.", keyVal=".Db::format($prefDbKey).", value=".Db::format($prefParamVal));
			}
			//Enregistre en session
			$_SESSION["pref"][$prefDbKey]=$prefParamVal;
		}
		//retourne la preference
		if(isset($_SESSION["pref"][$prefDbKey]))  {return $_SESSION["pref"][$prefDbKey];}
	}

	/*******************************************************************************************
	 * AJOUTE UN LOG
	 * Action : "connexion", "add", "modif", "delete"
	 *******************************************************************************************/
	public static function addLog($action, $curObj=null, $comment=null)
	{
		//S'il s'agit d'une action d'un user ou d'un invité qui ajoute un élément
		if(self::$curUser->isUser() || $action=="add")
		{
			////	Init la requête Sql
			$moduleName=Req::$curCtrl;
			$sqlObjectType=$sqlObjectId=null;
			$sqlLogValues=", `date`=".Db::dateNow().", _idUser=".Db::format(self::$curUser->_id).", _idSpace=".Db::format(self::$curSpace->_id).", ip=".Db::format($_SERVER["REMOTE_ADDR"]);
			////	Element : ajoute les détails (nom, titre, chemin, etc)
			if(is_object($curObj) && $curObj->isNew()==false)
			{
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
			Db::query("INSERT INTO ap_log SET action=".Db::format($action).", moduleName=".Db::format($moduleName).", objectType=".Db::format($sqlObjectType).", _idObject=".Db::format($sqlObjectId).", `comment`=".Db::format($comment)." ".$sqlLogValues);
			////	Supprime les anciens logs (lancé qu'une fois par session)
			if(empty($_SESSION["logsCleared"])){
				Db::query("DELETE FROM ap_log WHERE action='connexion'	AND UNIX_TIMESTAMP(date) <= ".intval(time()-(14*86400)));										 //Logs de connexion			: conservés 2 semaines
				Db::query("DELETE FROM ap_log WHERE action='delete'		AND UNIX_TIMESTAMP(date) <= ".intval(time()-(360*86400)));										 //logs de suppression			: conservés un an
				Db::query("DELETE FROM ap_log WHERE action NOT IN ('connexion','delete') AND UNIX_TIMESTAMP(date) <= ".intval(time()-(self::$agora->logsTimeOut*86400)));//Autres logs (add,modif,etc)	: en fonction du "logsTimeOut" (120j par défaut)
				$_SESSION["logsCleared"]=true;
			}
		}
	}
}