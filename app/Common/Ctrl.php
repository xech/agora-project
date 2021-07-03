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
	public static $isMainPage=false;			//Page principale avec barre de menu, messenger, etc (false pour les iframe)
	public static $userHasConnected=false;		//idem : l'user vient de s'identifier / connecter
	public static $curContainer=null;			//idem : objet conteneur courant (dossier, sujet, etc)
	public static $curTimezone=null;			//Timezone courante
	public static $notify=[];					//Messages de Notifications (cf. Vues)
	public static $lightboxClose=false;			//Fermeture de lightbox (cf. Vues)
	public static $lightboxCloseParams=null;	//Parametre de fermeture de lightbox : $notify ou autre
	protected static $initCtrlFull=true;		//Initialisation complete du controleur (connexion d'user, selection d'espace, etc)
	protected static $folderObjectType=null;	//Module avec une arborescence
	protected static $cachedObjects=[];			//Objets mis en cache !

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

		////	Controle la mise à jour PUIS Récup le parametrage de l'espace (apres "session_start")
		DbUpdate::lauchUpdate();
		self::$agora=self::getObj("agora");

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
				$_SESSION["displayAdmin"]=(Req::getParam("displayAdmin")=="true");
				if($_SESSION["displayAdmin"]==true)  {Ctrl::notify(Txt::trad("HEADER_displayAdminEnabled")." : ".Txt::trad("HEADER_displayAdminInfo"));}
			}
			////	Affichage des utilisateurs : space/all
			if(empty($_SESSION["displayUsers"]))  {$_SESSION["displayUsers"]="space";}
			////	Objet à charger et à controler (tjs après chargement des trads!)
			if(Req::isParam("targetObjId"))				{$targetObj=self::getTargetObj();}//Dossier (ou autre element) passé en GET
			elseif(static::$folderObjectType!==null)	{$targetObj=self::getTargetObj(static::$folderObjectType."-1");}//Dossier racine par défaut
			////	Charge le dossier/conteneur courant & controle son accès
			if(isset($targetObj) && is_object($targetObj) && !empty($targetObj->_id)){
				if($targetObj::isContainer())  {self::$curContainer=$targetObj;}
				if($targetObj->readRight()==false){
					if(static::$isMainPage==true)	{self::redir("?ctrl=".Req::$curCtrl);}//redirige vers controleur principal
					else							{self::noAccessExit();}//message d'erreur
				}
			}
		}
	}

	/*******************************************************************************************
	 * RECUPÈRE UN OBJET (vérifie s'il est déjà en cache)
	 *******************************************************************************************/
	public static function getObj($MdlObjectClass, $objIdOrValues=null, $updateCachedObj=false)
	{
		//Si on précise uniquement le "objectType", on ajoute "Mdl" pour récupérer la classe du modèle objet (exple : "fileFolder" devient "MdlFileFolder")
		if(preg_match("/^Mdl/",$MdlObjectClass)==false)  {$MdlObjectClass="Mdl".ucfirst($MdlObjectClass);}
		//Retourne un nouvel objet OU un objet existant (déjà en cache?)
		if(empty($objIdOrValues))	{return new $MdlObjectClass();}
		else
		{
			//Init
			$objId=(!empty($objIdOrValues["_id"]))  ?  $objIdOrValues["_id"]  :  (int)$objIdOrValues;
			$objCachedKey=$MdlObjectClass::objectType."-".$objId;
			//Ajoute/Update l'objet en cache?
			if(!isset(self::$cachedObjects[$objCachedKey]) || $updateCachedObj==true)  {self::$cachedObjects[$objCachedKey]=new $MdlObjectClass($objIdOrValues);}
			//Retourne l'objet en cache
			return self::$cachedObjects[$objCachedKey];
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
	protected static function displayPage($fileMainVue=null, $vDatasMainVue=array())
	{
		////	CORPS DE LA PAGE (sauf si validation de formulaire : "lightboxClose")
		if(!empty($fileMainVue)){
			$pathVue=(strstr($fileMainVue,Req::commonPath)==false)  ?  Req::curModPath()  :  null;//"app/Common/" déjà précisé?
			$vDatas["mainContent"]=self::getVue($pathVue.$fileMainVue, $vDatasMainVue);
		}
		////	PAGE PRINCIPALE
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
				//Mise à jour récente : notification dans le "pageFooterHtml" pour l'admin de l'espace
				if(self::$curUser->isAdminSpace() && self::$curUser->previousConnection<strtotime(self::$agora->dateUpdateDb))
					{self::$agora->footerHtml="<span id='footerHtmlUpdate' style='cursor:pointer' onclick=\"javascript:lightboxOpen('docs/CHANGELOG.txt')\">".Txt::trad("NOTIF_update")." ".VERSION_AGORA."</span><script>$('#footerHtmlUpdate').pulsate();</script>";}
				//Espace Disk
				$vDatasHeader["diskSpacePercent"]=ceil((File::datasFolderSize()/limite_espace_disque)*100);
				$vDatasHeader["diskSpaceAlert"]=($vDatasHeader["diskSpacePercent"]>70);
				//Récupère les plugins "shortcuts" de chaque module
				$vDatasHeader["pluginsShortcut"]=[];
				foreach(self::$curSpace->moduleList() as $tmpModule){
					if(method_exists($tmpModule["ctrl"],"getModPlugins"))  {$vDatasHeader["pluginsShortcut"]=array_merge($vDatasHeader["pluginsShortcut"], $tmpModule["ctrl"]::getModPlugins(["type"=>"shortcut"]));}
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
		////	NOTIFICATIONS PASSÉES EN GET/POST
		if(Req::isParam("notify")){
			foreach(Req::getParam("notify") as $tmpNotif)  {self::notify($tmpNotif);}
		}
		////	AFFICHE LE RÉSULTAT
		$vDatas["skinCss"]=(!empty(self::$agora->skin) && self::$agora->skin=="black")  ?  "black"  :  "white";
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

	/********************************************************************************************
	 * AJOUTE LA LISTE DES NOTIFICATIONS À UNE URL AVANT UNE REDIRECTION (cf.  "Ctrl::$notify")
	 ********************************************************************************************/
	public static function urlNotify()
	{
		$urlNotify=null;
		foreach(self::$notify as $message)  {$urlNotify.="&notify[]=".urlencode($message["message"]);}
		return $urlNotify;
	}

	/*******************************************************************************************
	 * REDIRIGE UNE PAGE
	 *******************************************************************************************/
	public static function redir($url)
	{
		//Url de redirection, si besoin avec des notifications
		$redirUrl=$url.self::urlNotify();
		//Redirection depuis une iframe ou une page principale
		if(static::$isMainPage==false)	{echo "<script> parent.location.href=\"".$redirUrl."\"; </script>";}
		else							{header("Location: ".$redirUrl);}
		//Fin de script..
		exit;
	}

	/*******************************************************************************************
	 * FERME LE LIGHTBOX (exple : après édition d'un element)
	 *******************************************************************************************/
	public static function lightboxClose($urlMoreParms=null)
	{
		//Initialise les params de reload de la page principale, puis affiche une page vide pour lancer le JS "lightboxClose()"
		self::$lightboxClose=true;
		self::$lightboxCloseParams=self::urlNotify().$urlMoreParms;
		static::displayPage();
		//Fin de script..
		exit;
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



	/***************************************************************************************************************************/
	/*******************************************	SPECIFIC METHODS	********************************************************/
	/***************************************************************************************************************************/

	/*******************************************************************************************
	 * CONNECTION D'UN USER ET SELECTION D'UN ESPACE ?
	 *******************************************************************************************/
	public static function userConnectionSpaceSelection()
	{
		////	CONNEXION D'UN USER (demandée ou auto)
		$connectViaForm=(Req::isParam(["connectLogin","connectPassword"]));
		$connectViaCookie=(!empty($_COOKIE["AGORAP_LOG"]) && !empty($_COOKIE["AGORAP_PASS"]) && Req::isParam("disconnect")==false);
		if(self::$curUser->isUser()==false  &&  ($connectViaForm==true || $connectViaCookie==true))
		{
			//// IDENTIFICATION ET CONTROLES D'ACCES
			//Connexion demandé ou auto
			if($connectViaForm==true)		{$login=Req::getParam("connectLogin");  $passwordSha1=MdlUser::passwordSha1(Req::getParam("connectPassword"));}
			elseif($connectViaCookie==true)	{$login=$_COOKIE["AGORAP_LOG"];			$passwordSha1=$_COOKIE["AGORAP_PASS"];}
			//Identification + recup des infos sur l'user
			$sqlPasswordSha1="AND `password`=".Db::format($passwordSha1);
			if(self::isHost())  {$sqlPasswordSha1=Host::sqlPassword(Req::getParam("connectPassword"),$sqlPasswordSha1);}
			$tmpUser=Db::getLine("SELECT * FROM ap_user WHERE `login`=".Db::format($login)." ".$sqlPasswordSha1);
			//User pas connecté : tente une identification LDAP (avec creation d'user à la volee)
			if(empty($tmpUser) && $connectViaForm==true)  {$tmpUser=MdlUser::ldapConnectCreateUser(Req::getParam("connectLogin"),Req::getParam("connectPassword"));}
			//...User toujours pas connecté : message d'erreur et déconnexion
			if(empty($tmpUser))   {self::notify("NOTIF_identification");  self::redir("?disconnect=1");}
			//User déjà connecté sur un autre poste & avec une autre ip (pas de controle sur l'appli)
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
			$idSpaceSelected=null;															//Init l'espace sélectionné
			$userSpaces=self::$curUser->getSpaces();										//Espaces disponibles pour l'user courant ou le guest
			$isNotifMail=(static::moduleName=="offline" && Req::isParam("targetObjUrl"));	//Accès depuis une notif mail d'objet (cf. "MdlObject::getUrlExternal()")
			//// Sélectionne un espace
			if(!empty($userSpaces))
			{
				//// Espace demandé :  L'user switch d'espace  ||  Accès depuis une notif mail d'objet (user identifié)  ||  Accès Guest
				if(Req::isParam("_idSpaceAccess")){
					foreach($userSpaces as $objSpace){
						if($objSpace->_id==Req::getParam("_idSpaceAccess") && (self::$curUser->isUser() || empty($objSpace->password) || $objSpace->password==Req::getParam("password")))
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
				if($isNotifMail==true && self::$curUser->isUser())	{self::redir(Req::getParam("targetObjUrl"));}						//Redir vers le controleur et l'objet demandé (notif mail d'objet)
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
	 * RECUPÈRE L'OBJET DEMANDÉ
	 * "targetObjId" passé en paramètre OU passé en GET/POST et récupérés via "initCtrl()"
	 * Exple:  $targetObjId="fileFolder-19"  OU  $targetObjId="fileFolder" pour un nouvel objet
	 ******************************************************************************************/
	public static function getTargetObj($targetObjId=null)
	{
		//$targetObjId passé en argument
		if(Req::isParam("targetObjId") && $targetObjId==null)  {$targetObjId=Req::getParam("targetObjId");}
		//Renvoie l'objet ciblé
		if(!empty($targetObjId))
		{
			//Charge un nouvel objet || Charge un objet existant
			$targetObjId=explode("-",$targetObjId);
			$targetObj=(empty($targetObjId[1]))  ?  self::getObj($targetObjId[0])  :  self::getObj($targetObjId[0],$targetObjId[1]);
			//Objet inexistant ou supprimé (_id passé en parametre mais _id reste à zero car l'objet est absent en BDD) : renvoie une erreur
			if(!empty($targetObjId[1]) && $targetObj->_id==0)  {self::notify("inaccessibleElem");  self::redir("?ctrl=".static::moduleName);}
			//Ajoute un "_idContainer" pour le controle d'accès lors de la création d'un objet (cf. "createUpdate()" puis "createRight()")
			if(Req::isParam("_idContainer") && empty($targetObj->_id) && empty($targetObj->_idContainer))  {$targetObj->_idContainer=Req::getParam("_idContainer");}
			//renvoie l'objet
			return $targetObj;
		}
	}

	/*******************************************************************************************
	 * RECUPÈRE LES OBJETS SELECTIONNÉS ET ENVOYÉS VIA GET/POST
	 * Exple: $_GET['targetObjects[fileFolder]']="2-4-7"
	 *******************************************************************************************/
	public static function getTargetObjects($objectType=null)
	{
		$returnObjects=[];
		if(Req::isParam("targetObjects") && is_array(Req::getParam("targetObjects")))
		{
			//On parcourt tous les objets ciblés
			foreach(Req::getParam("targetObjects") as $tmpObjectType=>$tmpObjectIds)
			{
				//Ajoute tous les types d'objets / un type en particulier
				if($objectType==null || $tmpObjectType==$objectType)
				{
					//Ajoute les objets s'ils sont accessibles
					foreach(explode("-",$tmpObjectIds) as $tmpObjectId){
						$tmpObject=self::getObj($tmpObjectType, $tmpObjectId);
						if($tmpObject->readRight())  {$returnObjects[]=$tmpObject;}
					}
				}
			}
		}
		//Retourne les objets
		return $returnObjects;
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
		if(Req::isParam($prefParamKey))										{$prefParamVal=Req::getParam($prefParamKey);}
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
				//Moins de 500 caractères en bdd
				$comment=Txt::reduce(strip_tags($comment),500);
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

	/*******************************************************************************************
	 * RECUPERE LES PLUGINS DE TYPE "FOLDER" D'UN MODULE
	 *******************************************************************************************/
	public static function getPluginsFolders($params, $MdlObjectFolder)
	{
		$pluginsList=[];
		foreach($MdlObjectFolder::getPlugins($params) as $objFolder)
		{
			$objFolder->pluginModule=static::moduleName;
			$objFolder->pluginIcon="folder/folderSmall.png";
			$objFolder->pluginLabel=$objFolder->name;
			$objFolder->pluginTooltip=$objFolder->folderPath("text");
			if(!empty($objFolder->description))  {$objFolder->pluginTooltip.="<hr>".Txt::reduce($objFolder->description);}
			$objFolder->pluginJsIcon="windowParent.redir('".$objFolder->getUrl()."');";//Redir vers le dossier
			$objFolder->pluginJsLabel=$objFolder->pluginJsIcon;
			$objFolder->pluginIsFolder=true;
			$pluginsList[]=$objFolder;
		}
		return $pluginsList;
	}
}