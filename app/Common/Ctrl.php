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
	public static $moduleOptions=[];
	public static $agora, $curUser, $curSpace;
	public static $isMainPage=false;			//Page principale ou Iframe
	public static $userJustConnected=false;		//Controle si l'user vient de s'identifier / connecter
	public static $curContainer=null;			//Conteneur courant : dossier / sujet / agenda
	public static $curRootFolder=null;			//Dossier root du module courant
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
		////	Lance la session
		if(defined("db_name"))  {session_name("SESSION_".db_name);}//Correspondant à db_name
		session_start();

		////	Déconnexion demandée
		if(Req::isParam("disconnect")){
			$_SESSION=[];
			session_destroy();
			self::userAuthToken("delete");
		}

		////	Récup le parametrage général (après "session_start()")  &&  Lance si besoin la mise à jour de la DB
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

		////	Init complète du controleur (sauf action Ajax)
		if(static::$initCtrlFull==true)
		{
			////	Connection d'un user  &&  selection d'un espace !
			self::userConnectionSpaceSelection();

			////	Enregistre le cookie pour "Req::isMobileApp()"
			if(!empty($_GET["mobileAppli"])){
				setcookie("mobileAppli", "true", TIME_COOKIES, "/");//Sur tout le path/domaine
				$_COOKIE["mobileAppli"]="true";
			}

			////	Affiche une page principale  &&  Controle d'accès au module (sauf modules sans affectation spécifique)
			if(Req::$curAction=="default"){
				static::$isMainPage=true;
				if(!in_array(Req::$curCtrl,["agora","log","offline","space","user"])  &&  !array_key_exists(Req::$curCtrl,self::$curSpace->moduleList()))
					{self::redir("index.php?ctrl=".key(self::$curSpace->moduleList()));}
			}

			////	Init/Switch l'affichage administrateur
			if(self::$curUser->isSpaceAdmin() && Req::isParam("displayAdmin")){
				$_SESSION["displayAdmin"]=(bool)(Req::param("displayAdmin")=="true");
				if($_SESSION["displayAdmin"]==true)		{Ctrl::notify(Txt::trad("HEADER_displayAdminEnabled")." :<br> ".Txt::trad("HEADER_displayAdminInfo"));}
				else									{Ctrl::notify("HEADER_displayAdminDisabled");}
			}

			////	Affichage des utilisateurs ("space"=espace courant || "all"=tous)  &&  Charge l'objet courant
			if(empty($_SESSION["displayUsers"]))  {$_SESSION["displayUsers"]="space";}
			if(Req::isParam("typeId"))				{$curObj=self::getObjTarget();}						//Objet passé en GET
			elseif(static::$folderObjType!==null)	{$curObj=self::getObj(static::$folderObjType,1);}	//Dossier racine par défaut

			////	Control d'accès à l'objet courant
			if(!empty($curObj) && $curObj->isNew()==false){
				if($curObj->readRight()==false)	{static::$isMainPage==true ? self::redir("index.php?ctrl=".Req::$curCtrl) : self::noAccessExit();}	//Pas d'accès en lecture : redir vers le Ctrl principal || notif d'erreur
				if($curObj::isContainer())		{self::$curContainer=$curObj;}																		//Charge le conteneur courant (dossier/sujet/agenda..)
				if($curObj::isFolder==true)		{self::$curRootFolder=self::getObj($curObj::objectType,1);}											//Charge le dossier root du module courant
			}
		}
	}

	/*******************************************************************************************
	 * CONNECTION D'UN USER  &&  SELECTION D'UN ESPACE
	 *******************************************************************************************/
	public static function userConnectionSpaceSelection()
	{
		////	INIT
		$userAuthentified=false;
		$connectViaForm		=Req::isParam(["connectLogin","connectPassword"]);
		$connectViaToken	=(!empty($_COOKIE["userAuthToken"]));
		$connectViaCookieOld=(!empty($_COOKIE["AGORAP_LOG"]) && !empty($_COOKIE["AGORAP_PASS"]));

		////	CONNEXION D'UN USER
		if(self::$curUser->isUser()==false && Req::isParam("disconnect")==false && ($connectViaForm==true || $connectViaToken==true || $connectViaCookieOld==true))
		{
			////	CONNEXION VIA FORMULAIRE
			if($connectViaForm==true){
				$tmpUser=Db::getLine("SELECT * FROM ap_user WHERE `login`=".Db::param("connectLogin"));
				$clearPassword=Req::param("connectPassword");
				$passwordVerifyHost=(Req::isHost() && Host::passwordVerifyHost($clearPassword));
				//// Verif si le password correspond à un hash :  "password_verify()" avec hash Bcrypt  ||  "passwordSha1()" : obsolete mais tjs retro-compatible  ||  "passwordVerifyHost()" : specific aux hosts
				if(!empty($tmpUser)  &&  (password_verify($clearPassword,$tmpUser["password"]) || MdlUser::passwordSha1($clearPassword)==$tmpUser["password"] || $passwordVerifyHost==true)){
					if($passwordVerifyHost==false)  {Db::query("UPDATE ap_user SET `password`=".Db::format(password_hash($clearPassword,PASSWORD_DEFAULT))." WHERE _id=".Db::format($tmpUser["_id"]));}// Update le hash ..sauf pour les hosts!
					$userAuthentified=true;
				}
			}
			////	CONNEXION AUTO VIA TOKEN
			elseif($connectViaToken==true){
				$cookieToken=explode("@@@",$_COOKIE["userAuthToken"]);
				$tmpUser=Db::getLine("SELECT T1.*, T2.userAuthToken FROM ap_user T1, ap_userAuthToken T2 WHERE T1._id=T2._idUser AND T1._id=".Db::format($cookieToken[0])." AND T2.userAuthToken=".Db::format($cookieToken[1]));
				if(!empty($tmpUser))	{$userAuthentified=true;}
				else					{self::userAuthToken("delete");}
			}
			////	CONNEXION AUTO VIA L'ANCIENNE METHODE (obsolete depuis v23.4 mais retro-compatible : cookies supprimés dès que $userAuthentified=true)
			elseif($connectViaCookieOld==true){
				$tmpUser=Db::getLine("SELECT * FROM ap_user WHERE `login`=".Db::format($_COOKIE["AGORAP_LOG"])." AND `password`=".Db::format($_COOKIE["AGORAP_PASS"]));	
				if(!empty($tmpUser))  {$userAuthentified=true;}
			}

			////	USER AUTHENTIFIE
			if($userAuthentified==true){
				//// Charge l'user courant (toujours en 1er)
				self::$curUser=self::getObj("user",(int)$tmpUser["_id"]);
				$_SESSION=["_idUser"=>self::$curUser->_id];
				self::$userJustConnected=true;
				self::addLog("connexion");

				//// Charge les preferences de l'user  &&  update "lastconnection" (connexion courante) + "previousconnection" (connexion précédente)
				foreach(Db::getTab("SELECT * FROM ap_userPreference WHERE _idUser=".self::$curUser->_id) as $tmpPref)  {$_SESSION["pref"][$tmpPref["keyVal"]]=$tmpPref["value"];}
				$previousConnection=(!empty($tmpUser["lastConnection"]))  ?  $tmpUser["lastConnection"]  :  time();
				Db::query("UPDATE ap_user SET lastConnection='".time()."', previousConnection=".Db::format($previousConnection)." WHERE _id=".self::$curUser->_id);

				//// Reinitialise le token de connexion auto
				if( ($connectViaForm==true && Req::isParam("rememberMe")) || $connectViaToken==true || $connectViaCookieOld==true)  {self::userAuthToken("create",self::$curUser->_id);}

				//// Notif si l'user est connecté via une autre ip
				if(Db::getVal("SELECT count(*) FROM ap_userLivecouter WHERE _idUser=".Db::format($tmpUser["_id"])." AND `date`>".Db::format(time()-60)." AND ipAdress NOT LIKE ".Db::format($_SERVER["REMOTE_ADDR"])) > 0)
					{self::notify(Txt::trad("NOTIF_presentIp")." -> ".$_SERVER["REMOTE_ADDR"]);}
			}
			////	USER NON-AUTHENTIFIÉ
			else{
				//// Notif d'erreur de credentials
				if($connectViaForm==true)  {self::notify("NOTIF_identification");}
				self::redir("index.php?disconnect=1");
			}
		}

		////	STATS DE CONNEXION DU HOST (APRES AUTHENTIFICATION & AVANT SÉLECTION D'ESPACE AVEC REDIRECTION)
		if(Req::isHost())  {Host::connectStatsHostInfos();}

		////	SELECTION D'UN ESPACE  (Tester switch d'espace + connexion d'user sans espace affecté + connexion de guest avec switch d'espace + accès à un objet depuis notif mail)
		if(self::$userJustConnected==true  ||  (static::moduleName=="offline" && (self::$curUser->isUser() || Req::isParam("_idSpaceAccess"))))
		{
			//// Init l'espace sélectionné et les espaces disponibles
			$idSpaceSelected=null;
			$userSpaces=self::$curUser->spaceList();
			//// Sélectionne un espace
			if(!empty($userSpaces))
			{
				//// Espace demandé (Switch d'espace || Accès Guest en page de connexion)
				if(Req::isParam("_idSpaceAccess")){
					foreach($userSpaces as $objSpace){
						if($objSpace->_id==Req::param("_idSpaceAccess")  &&  (self::$curUser->isUser() || empty($objSpace->password) || $objSpace->password==Req::param("password")))
							{$idSpaceSelected=$objSpace->_id;  break;}
					}
				}
				//// Espace par défaut d'un user
				elseif(self::$curUser->isUser())
				{
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
		elseif(empty(self::$curSpace->_id) && static::moduleName!="offline")  {self::notify("NOTIF_noAccess");  self::redir("index.php?disconnect=1");}
	}

	/*******************************************************************************************
	 * SUPPRIME/CREE LE TOKEN DE CONNEXION AUTOMATIQUE
	 * $action :  "delete"  ||  "create" avec $_idUser
	 *******************************************************************************************/
	public static function userAuthToken($action, $_idUser=null)
	{
		////	S'il existe déjà un cookie : supprime le token correspondant en bdd
		if(!empty($_COOKIE["userAuthToken"])){
			$cookieToken=explode("@@@",$_COOKIE["userAuthToken"]);																		//Récupère le token du cookie
			if(!empty($cookieToken[1]))  {Db::query("DELETE FROM ap_userAuthToken WHERE userAuthToken=".Db::format($cookieToken[1]));}	//Supprime le token correspondant dans la bdd
			setcookie("userAuthToken", "", -1);																							//Supprime le cookie sur tout le domaine
			setcookie("userAuthToken", "", -1, "/");																					//Idem: sur tout le path/domaine (cf. "createHost()")
			unset($_COOKIE["userAuthToken"]);																							//Idem
		}
		////	Créé un nouveau token au format : enregistre le token en bdd et dans un cookie
		if($action=="create" && !empty($_idUser)){
			require_once('app/misc/Browser.php');																						//Charge la classe "Browser()"
			$browserObj=new Browser();																									//Récup les infos du browser
			$browserId=(is_object($browserObj))  ?  $browserObj->getBrowser()."-".$browserObj->getPlatform()  :  null;					//Identifie le browser et l'OS
			$userAuthToken=password_hash(uniqid(),PASSWORD_DEFAULT);																	//Créé un nouveau Token avec l'algo Bcrypt
			$cookieToken=$_idUser."@@@".$userAuthToken;																					//Créé le token du cookie
			setcookie("userAuthToken", $cookieToken, TIME_COOKIES);																		//Enregistre le cookie
			$_COOKIE["userAuthToken"]=$cookieToken;																						//Charge le cookie
			Db::query("DELETE FROM ap_userAuthToken WHERE _idUser=".$_idUser." AND browserId=".Db::format($browserId));					//Supprime en bdd les anciens tokens
			Db::query("INSERT INTO ap_userAuthToken SET _idUser=".$_idUser.", browserId=".Db::format($browserId).", userAuthToken=".Db::format($userAuthToken).", dateCrea=NOW()");	//Enregistre le token en bdd !
		}
		////	Supprime les cookies de l'ancienne méthode  &&  Supprime les tokens de plus d'un an
		if(!empty($_COOKIE["AGORAP_PASS"]))  {setcookie("AGORAP_LOG","",-1);  setcookie("AGORAP_PASS","",-1);}
		Db::query("DELETE FROM ap_userAuthToken WHERE UNIX_TIMESTAMP(dateCrea) < ".(time()-31536000));
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
		elseif($emptyValueEnabled==true && Req::isParam("formValidate"))	{$prefParamVal="";}//Enregistre une valeur vide? (Ex: checkbox non cochée dans un formulaire)
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
	public static function displayPage($fileMainVue, $vDatasMainVue=array())
	{
		////	PAGE PRINCIPALE : AFFICHE LE HEADER, WALLPAPER, ETC.
		if(static::$isMainPage==true)
		{
			//// WALLPAPER & LOGO FOOTER
			if(!empty(self::$curSpace->wallpaper))	{$vDatas["pathWallpaper"]=CtrlMisc::pathWallpaper(self::$curSpace->wallpaper);}
			elseif(!empty(self::$agora->wallpaper))	{$vDatas["pathWallpaper"]=CtrlMisc::pathWallpaper(self::$agora->wallpaper);}
			else									{$vDatas["pathWallpaper"]=CtrlMisc::pathWallpaper();}
			$vDatas["pathLogoUrl"]=(empty(self::$agora->logoUrl))  ?  OMNISPACE_URL_PUBLIC  :  self::$agora->logoUrl;
			//// HEADER & MESSENGER (sauf si ctrl externe)
			if(!in_array(Req::$curCtrl,["offline","misc"])){
				//Plugins "shortcuts" des modules
				$vDatasHeader["pluginsShortcut"]=[];
				foreach(self::$curSpace->moduleList() as $tmpModule){
					if(method_exists($tmpModule["ctrl"],"getPlugins"))  {$vDatasHeader["pluginsShortcut"]=array_merge($vDatasHeader["pluginsShortcut"], $tmpModule["ctrl"]::getPlugins(["type"=>"shortcut"]));}
				}
				//Liste des espaces et modules +  Inscription d'utilisateurs  +  Puis récupère la vue
				$vDatasHeader["spaceList"]=self::$curUser->spaceList();
				$vDatasHeader["spaceListMenu"]=(count($vDatasHeader["spaceList"])>=2);
				$vDatasHeader["moduleList"]=self::$curSpace->moduleList();
				$vDatasHeader["userInscriptionValidate"]=(count(CtrlUser::userInscriptionValidate())>0);
				$vDatas["headerMenu"]=self::getVue(Req::commonPath."VueHeaderMenu.php",$vDatasHeader);
				//Récupère le Messenger (cf. "CtrlMisc::actionMessengerUpdate()")
				if(self::$curUser->messengerEnabled())  {$vDatas["messenger"]=self::getVue(Req::commonPath."VueMessenger.php");}
			}
		}
		////	NOTIFS (GET/POST)  +  AFFICHE LA VUE
		foreach((array)Req::param("notify") as $tmpNotif)  {self::notify($tmpNotif);}
		$pathVue=(strstr($fileMainVue,Req::commonPath)==false)  ?  Req::curModPath()  :  null;//"app/Common/" déjà précisé?
		$vDatas["mainContent"]=self::getVue($pathVue.$fileMainVue, $vDatasMainVue);
		echo self::getVue(Req::commonPath."VueStructure.php",$vDatas);
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
			////	Element : ajoute les détails (nom, titre, chemin, etc)
			if(MdlObject::isObject($curObj))
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


	/*******************************************************************************************
	 * RECUPÈRE UN OBJET (vérifie s'il est déjà en cache)
	 *******************************************************************************************/
	public static function getObj($objectType, $objIdOrValues=null, $updateCache=false)
	{
		$MdlClass="Mdl".ucfirst($objectType);																//Récupère le modèle de l'objet (ex: "fileFolder" => "MdlFileFolder")
		if(empty($objIdOrValues))	{return new $MdlClass();}												//Retourne un nouvel objet OU un objet existant (déjà en cache?)
		else{
			$objId=(!empty($objIdOrValues["_id"]))  ?  $objIdOrValues["_id"]  :  (int)$objIdOrValues;													//Id de l'objet
			$cacheKey=$MdlClass::objectType."-".$objId;																									//Clé de l'objet mis en cache
			if(isset(self::$cacheObjects[$cacheKey])==false || $updateCache==true)  {self::$cacheObjects[$cacheKey]=new $MdlClass($objIdOrValues);}		//Ajoute ou Update l'objet en cache
			return self::$cacheObjects[$cacheKey];																										//Retourne l'objet en cache
		}
	}

	/*******************************************************************************************
	 * RECUPÈRE L'OBJET PASSÉ EN GET/POST || EN ARGUMENT VIA $typeId (ex: "file-55")
	 ******************************************************************************************/
	public static function getObjTarget($typeId=null)
	{
		if(Req::isParam("typeId") || !empty($typeId)){
			$typeId=(!empty($typeId))  ?  explode("-",$typeId)  :  explode("-",Req::param("typeId"));										//Récupère le "typeId" de l'objet (vérifier en premier si ya un argument!)
			$isNewObj=(empty($typeId[1]));																									//Vérif si c'est un nouvel objet
			$curObj=($isNewObj==true)  ?  self::getObj($typeId[0])  :  self::getObj($typeId[0],$typeId[1]);									//Charge un nouvel objet OU un objet existant
			if($isNewObj==false && $curObj->_id==0)  {self::notify("inaccessibleElem"); self::redir("index.php?ctrl=".static::moduleName);}	//Objet inexistant/supprimé en BDD : renvoie une erreur
			if($isNewObj==true && Req::isParam("_idContainer"))  {$curObj->_idContainer=Req::param("_idContainer");}						//Ajoute si besoin "_idContainer" pour le controle d'accès d'un nouvel objet (cf. "createUpdate()" puis "createRight()")
			return $curObj;																													//Renvoie l'objet
		}
	}

	/*******************************************************************************************
	 * RECUPÈRE LES OBJETS ENVOYÉS VIA GET/POST  (ex: objectsTypeId[file]=33-44-55)
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
	 * REDIRIGE VERS L'ADRESSE DEMANDÉE : REDIRECTION SIMPLE OU SUR LA PAGE PRINCIPALE (IFRAME)
	 *******************************************************************************************/
	public static function redir($redirUrl, $urlNotify=true)
	{
		if(!empty($redirUrl)){
			if($urlNotify==true)  {$redirUrl.=self::urlNotify();}												//Ajoute les notifs
			if(static::$isMainPage==true)	{header("Location: ".$redirUrl);}									//Redirection simple
			else							{echo '<script> parent.location.href="'.$redirUrl.'"; </script>';}	//Redirection depuis une Iframe (ex: après édit/suppr d'un objet)
			exit;																								//Fin de script
		}
	}
	
	/*******************************************************************************************
	 * FERME LE LIGHTBOX VIA JS (ex: après édit d'objet)
	 *******************************************************************************************/
	public static function lightboxClose($urlRedir=null, $urlParms=null)
	{
		echo '<script src="app/Common/js-css-'.Req::appVersion().'/app.js"></script>
			  <script>lightboxClose("'.$urlRedir.'","'.$urlParms.self::urlNotify().'");</script>';
		exit;
	}

	/*******************************************************************************************
	 * AJOUTE UNE NOTIFICATION À AFFICHER VIA "VUESTRUCTURE.PHP"
	 * $message : 	message spécifique  /  clé de traduction
	 * $type : 		"notice"  /  "success"  /  "warning"
	 *******************************************************************************************/
	public static function notify($message, $type="notice")
	{
		if(Tool::arraySearch(self::$notify,$message)==false){		//Vérifie si le message n'est pas déjà dans la liste (évite les doublons de notif)
			self::$notify[]=["message"=>$message, "type"=>$type];	//Ajoute la notification au tableau "self::$notify"
		}
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
		echo "<h2><img src='app/img/importantBig.png'> ".$message."</h2>";
		exit;
	}
}