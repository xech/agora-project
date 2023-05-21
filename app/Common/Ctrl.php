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
	public static $userJustConnected=false;		//Controle si l'user vient de s'identifier / connecter
	public static $isMenuSelectObjects=false;	//Menu de sélection d'objets affiché ?
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
		////	Lance la session || Déconnexion & réinit la session
		if(defined("db_name"))  {session_name("SESSION_".db_name);}//Différente pour chaque espace/db
		session_cache_limiter("nocache");
		session_start();
		if(Req::isParam("disconnect")){
			$_SESSION=[];
			session_destroy();
			self::resetUserAuthToken("disconnect");
		}

		////	Récup le parametrage général (après "session_start()")  &&  Lance si besoin la mise à jour de la DB
		self::$agora=new MdlAgora();
		if(Req::isHost())  {Host::agoraParams();}
		DbUpdate::lauchUpdate();

		////	Init l'user et l'espace courant
		$_idUser =(!empty($_SESSION["_idUser"]))  ? $_SESSION["_idUser"]  : null;
		$_idSpace=(!empty($_SESSION["_idSpace"])) ? $_SESSION["_idSpace"] : null;
		self::$curUser=self::getObj("user",$_idUser);
		self::$curSpace=self::getObj("space",$_idSpace);

		////	Cache control Etag (complète le .htaccess)  &&  Init le fuseau horaire
		header("Etag: ".md5(Req::appVersion()));
		self::$curTimezone=array_search(self::$agora->timezone,Tool::$tabTimezones);
		if(empty(self::$curTimezone))	{self::$curTimezone="Europe/Paris";}
		date_default_timezone_set(self::$curTimezone);

		////	Init complète du controleur
		if(static::$initCtrlFull==true)
		{
			////	Connection d'un user  &&  selection d'un espace !
			self::userConnectionSpaceSelection();

			////	Enregistre et charge le cookie pour "Req::isMobileApp()" (10ans)
			if(Req::isParam("mobileAppli")){
				setcookie("mobileAppli", "true", (time()+315360000));
				$_COOKIE["mobileAppli"]="true";
			}

			////	Affiche une page principale  &&  Controle d'accès au module (sauf module spécifique)
			if(Req::$curAction=="default"){
				static::$isMainPage=true;
				if(!in_array(Req::$curCtrl,["agora","log","offline","space","user"])  &&  !array_key_exists(Req::$curCtrl,self::$curSpace->moduleList()))  {self::redir("index.php?ctrl=".key(self::$curSpace->moduleList()));}
			}

			////	Affichage administrateur demandé
			if(self::$curUser->isAdminSpace() && Req::isParam("displayAdmin")){
				$_SESSION["displayAdmin"]=(Req::param("displayAdmin")=="true");//Bool
				if($_SESSION["displayAdmin"]==true)  {Ctrl::notify(Txt::trad("HEADER_displayAdminEnabled")." :<br>".Txt::trad("HEADER_displayAdminInfo"));}
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
				$passwordClear=Req::param("connectPassword");
				$passwordVerifyHost=(Req::isHost() && Host::passwordVerify($passwordClear));
				//// Verif si le password correspond à un hash Bcrypt / Sha1 (old) / Host (specific). Enregistre au besoin le hash Bcrypt du password (le salt est généré via "password_hash()")
				if(!empty($tmpUser)  &&  (password_verify($passwordClear,$tmpUser["password"]) || MdlUser::passwordSha1($passwordClear)==$tmpUser["password"] || $passwordVerifyHost==true)){
					if($passwordVerifyHost==false)  {Db::query("UPDATE ap_user SET `password`=".Db::format(password_hash($passwordClear,PASSWORD_DEFAULT))." WHERE _id=".Db::format($tmpUser["_id"]));}
					$userAuthentified=true;
				}
			}
			////	CONNEXION AUTO VIA TOKEN
			elseif($connectViaToken==true){
				$cookieToken=explode("@@@",$_COOKIE["userAuthToken"]);
				$tmpUser=Db::getLine("SELECT T1.*, T2.userAuthToken FROM ap_user T1, ap_userAuthToken T2 WHERE T1._id=T2._idUser AND T1._id=".Db::format($cookieToken[0])." AND T2.userAuthToken=".Db::format($cookieToken[1]));
				if(!empty($tmpUser))  {$userAuthentified=true;}
			}
			////	CONNEXION AUTO VIA L'ANCIENNE METHODE	=> OBSOLETE DEPUIS v23.4 : GARDER POUR RÉTRO-COMPATIBILITÉ
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

				//// Charge les preferences de l'user  &&  update "lastconnection"/"previousconnection"
				foreach(Db::getTab("SELECT * FROM ap_userPreference WHERE _idUser=".self::$curUser->_id) as $tmpPref)  {$_SESSION["pref"][$tmpPref["keyVal"]]=$tmpPref["value"];}
				$previousConnection=(!empty($tmpUser["lastConnection"]))  ?  $tmpUser["lastConnection"]  :  time();
				Db::query("UPDATE ap_user SET lastConnection='".time()."', previousConnection=".Db::format($previousConnection)." WHERE _id=".self::$curUser->_id);

				//// Reinitialise le token de connexion auto
				if(($connectViaForm==true && Req::isParam("rememberMe")) || $connectViaToken==true || $connectViaCookieOld==true)  {self::resetUserAuthToken("newToken");}

				//// Notif si l'user est connecté via une autre ip
				if(Db::getVal("SELECT count(*) FROM ap_userLivecouter WHERE _idUser=".Db::format($tmpUser["_id"])." AND `date`>".Db::format(time()-60)." AND ipAdress NOT LIKE ".Db::format($_SERVER["REMOTE_ADDR"])) > 0)
					{self::notify(Txt::trad("NOTIF_presentIp")." -> ".$_SERVER["REMOTE_ADDR"]);}
			}
			////	USER NON-AUTHENTIFIÉ
			else{
				//// Notif d'erreur de credentials || notif de token obsolete
				self::notify($connectViaForm==true?"NOTIF_identification":"NOTIF_identificationToken");
				self::redir("index.php?disconnect=1");
			}
		}

		////	STATS DE CONNEXION DU HOST (tjs entre connexion & sélection d'espace)
		if(Req::isHost())  {Host::connectStatsHostInfos();}

		////	SELECTION D'UN ESPACE  (Tester switch d'espace + connexion d'user sans espace affecté + connexion de guest avec switch d'espace + accès à un objet depuis notif mail)
		if(self::$userJustConnected==true  ||  (static::moduleName=="offline" && (self::$curUser->isUser() || Req::isParam("_idSpaceAccess"))))
		{
			//// Init l'espace sélectionné et les espaces disponibles
			$idSpaceSelected=null;
			$userSpaces=self::$curUser->getSpaces();
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
				else							{self::notify("NOTIF_noAccess");  self::redir("index.php?disconnect=1");}	//Aucun module disponible sur l'espace : message d'erreur et déconnexion
			}
			//// User identifié mais affecté à aucun espace : message d'erreur et déconnexion
			elseif(self::$userJustConnected==true)   {self::notify("NOTIF_noAccessNoSpaceAffected");  self::redir("index.php?disconnect=1");}
		}
		////	USER NON IDENTIFIÉ + AUCUN ESPACE PUBLIC DISPONIBLE (notif "Vous êtes maintenant déconnecté")
		elseif(empty(self::$curSpace->_id) && static::moduleName!="offline")  {self::notify("NOTIF_noAccess");  self::redir("index.php?disconnect=1");}
	}

	/*******************************************************************************************
	 * RE-INITIALISE LE TOKEN DE CONNEXION AUTO : DE LA BDD ET DU COOKIE 
	 *******************************************************************************************/
	public static function resetUserAuthToken($mode)
	{
		//// S'il existe un cookie "userAuthToken" : supprime le token en bdd ..puis le cookie 
		if(!empty($_COOKIE["userAuthToken"])){
			$cookieToken=explode("@@@",$_COOKIE["userAuthToken"]);
			Db::query("DELETE FROM ap_userAuthToken WHERE userAuthToken=".Db::format($cookieToken[1]));
			setcookie("userAuthToken", "", -1);
		}
		//// Créé un nouveau token au format Bcrypt : enregistre le token en bdd ..puis en cookie (un an)
		if($mode=="newToken"){
			$newToken=password_hash(uniqid(),PASSWORD_DEFAULT);
			Db::query("INSERT INTO ap_userAuthToken SET _idUser=".self::$curUser->_id.", userAuthToken=".Db::format($newToken).", dateCrea=NOW()");
			setcookie("userAuthToken", self::$curUser->_id."@@@".$newToken, (time()+31536000));
		}
		//// Supprime les cookies de l'ancienne méthode et les tokens obsolètes (+ d'un an)
		if(!empty($_COOKIE["AGORAP_PASS"]))  {setcookie("AGORAP_LOG",null,-1);  setcookie("AGORAP_PASS",null,-1);}
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
		elseif($emptyValueEnabled==true && Req::isParam("formValidate"))	{$prefParamVal="";}//Enregistre une valeur vide? (ex: checkbox non cochée dans un formulaire)
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
	protected static function displayPage($fileMainVue, $vDatasMainVue=array())
	{
		////	PAGE PRINCIPALE : AFFICHE LE HEADER, WALLPAPER, ETC.
		if(static::$isMainPage==true)
		{
			//// WALLPAPER & LOGO FOOTER
			if(!empty(self::$curSpace->wallpaper))	{$vDatas["pathWallpaper"]=CtrlMisc::pathWallpaper(self::$curSpace->wallpaper);}
			elseif(!empty(self::$agora->wallpaper))	{$vDatas["pathWallpaper"]=CtrlMisc::pathWallpaper(self::$agora->wallpaper);}
			else									{$vDatas["pathWallpaper"]=CtrlMisc::pathWallpaper();}
			$vDatas["pathLogoUrl"]=(empty(self::$agora->logoUrl))  ?  OMNISPACE_URL_PUBLIC  :  self::$agora->logoUrl;
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
			$typeId=(!empty($typeId))  ?  explode("-",$typeId)  :  explode("-",Req::param("typeId"));										//Récupère le "typeId" de l'objet (vérifier en premier si ya un argument!)
			$isNewObj=(empty($typeId[1]));																									//Vérif si c'est un nouvel objet
			$curObj=($isNewObj==true)  ?  self::getObj($typeId[0])  :  self::getObj($typeId[0],$typeId[1]);									//Charge un nouvel objet OU un objet existant
			if($isNewObj==false && $curObj->_id==0)  {self::notify("inaccessibleElem"); self::redir("index.php?ctrl=".static::moduleName);}	//Objet inexistant/supprimé en BDD : renvoie une erreur
			if($isNewObj==true && Req::isParam("_idContainer"))  {$curObj->_idContainer=Req::param("_idContainer");}						//Ajoute si besoin "_idContainer" pour le controle d'accès d'un nouvel objet (cf. "createUpdate()" puis "createRight()")
			return $curObj;																													//Renvoie l'objet
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
	 * REDIRIGE VERS L'ADRESSE DEMANDÉE : REDIRECTION SIMPLE OU SUR LA PAGE PRINCIPALE (IFRAME)
	 *******************************************************************************************/
	public static function redir($urlRedir)
	{
		$redirUrl=$urlRedir.self::urlNotify();																	//Ajoute si besoin les notifs
		if(static::$isMainPage==true)	{header("Location: ".$redirUrl);}										//Redirection simple
		else							{echo "<script> parent.location.href=\"".$redirUrl."\"; </script>";}	//Redirection de la page principale depuis une Iframe (ex: après édit/suppr d'un objet)
		exit;																									//Fin de script
	}

	/*******************************************************************************************
	 * AJOUTE UNE NOTIFICATION À AFFICHER VIA "VUESTRUCTURE.PHP"
	 * $message : message spécifique OU clé de traduction
	 * $type : "notice" / "success" / "warning"
	 *******************************************************************************************/
	public static function notify($messageTrad, $type="notice")
	{
		//Ajoute la notification au tableau "self::$notify" si elle n'est pas déjà présente
		if(Tool::arraySearch(self::$notify,$messageTrad)==false)  {self::$notify[]=["message"=>$messageTrad,"type"=>$type];}
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
	 * FERME LE LIGHTBOX VIA JS (ex: après édit d'objet)
	 *******************************************************************************************/
	public static function lightboxClose($urlRedir=null, $urlParms=null)
	{
		echo '<script src="app/js/common-'.Req::appVersion().'.js"></script>
			  <script>lightboxClose("'.$urlRedir.'","'.$urlParms.self::urlNotify().'");</script>';
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
}