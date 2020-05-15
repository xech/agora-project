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
	public static $moduleOptions=array();
	public static $agora, $curUser, $curSpace;
	public static $isMainPage=false;			//Page principale avec barre de menu, messenger, etc (false pour les iframe)
	public static $userHasConnected=false;		//idem : l'user vient de s'identifier / connecter
	public static $curContainer=null;			//idem : objet conteneur courant (dossier, sujet, etc)
	public static $curTimezone=null;			//Timezone courante
	public static $msgNotif=array();			//Messages de Notifications (cf. Vues)
	public static $lightboxClose=false;			//Fermeture de lightbox (cf. Vues)
	public static $lightboxCloseParams=null;	//Parametre de fermeture de lightbox : $msgNotif ou autre
	protected static $initCtrlFull=true;		//Initialisation complete du controleur (connexion d'user, selection d'espace, etc)
	protected static $folderObjectType=null;	//Module avec une arborescence
	protected static $cachedObjects=array();	//Objets mis en cache !

	/*
	 * Initialise le controleur principal : session, parametrages, connexion de l'user, selection de l'espace, etc
	 */
	public static function initCtrl()
	{
		////	Init la session
		if(defined("db_name"))  {session_name("Agora_".db_name);}//Une session pour chaque espace et DB du serveur
		session_cache_limiter("nocache");
		session_start();

		////	Déconnexion : reinit les valeurs de session
		if(Req::isParam("disconnect")){
			$_SESSION=[];
			session_destroy();
			setcookie("AGORAP_PASS", null, (time()+315360000));
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
			////	Connection d'un user? Selection d'un espace? Init les stats?
			self::userConnection();
			if(self::isHost())  {Host::connectStats();}//Tjs après "userConnection()"!
			self::curSpaceSelection();
			////	Chargement des trads et des "locales"
			Txt::loadTrads();
			////	Affiche une page principale (Controles d'accès au module, Menu principal, Footer, etc)
			if(Req::$curAction=="default")  {static::$isMainPage=true;}
			////	Controle d'accès au module de l'espace (Si l'user souhaite afficher un module "standard", autre que les "logs" &co, mais que le module n'est pas affecté à l'espace courant : redirige vers le premier module de l'espace)
			if(static::$isMainPage==true && self::$curUser->isUser() && in_array(Req::$curCtrl,MdlSpace::$moduleList) && array_key_exists(Req::$curCtrl,self::$curSpace->moduleList())==false)  {self::redir("?ctrl=".key(self::$curSpace->moduleList()));}
			////	Affichage administrateur demandé : switch l'affichage et "cast" la valeur en booléen (pas de "boolval()"..)
			if(self::$curUser->isAdminSpace() && Req::isParam("displayAdmin")){
				$_SESSION["displayAdmin"]=(Req::getParam("displayAdmin")=="true");
				if($_SESSION["displayAdmin"]==true)  {Ctrl::addNotif(Txt::trad("HEADER_displayAdminEnabled")." : ".Txt::trad("HEADER_displayAdminInfo"));}
			}
			////	Affichage des utilisateurs : space/all
			if(empty($_SESSION["displayUsers"]))  {$_SESSION["displayUsers"]="space";}
			////	Objet à charger et à controler (tjs après chargement des trads!)
			if(Req::isParam("targetObjId"))				{$targetObj=self::getTargetObj();}//Dossier/conteneur courant ou autre objet
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

	/*
	 * Recupère un objet, déjà en cache?
	 */
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
			//Ajoute ou Update l'objet en cache?
			if(!isset(self::$cachedObjects[$objCachedKey]) || $updateCachedObj==true)  {self::$cachedObjects[$objCachedKey]=new $MdlObjectClass($objIdOrValues);}
			//Retourne l'objet en cache
			return self::$cachedObjects[$objCachedKey];
		}
	}

	/*
     * Génère une vue à partir d'un fichier et des parametres $datas
     */
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

	/*
	 * Affiche une page complete (ensemble de vues)
	 */
	protected static function displayPage($fileMainVue=null, $vDatasMainVue=array())
	{
		//Init
		$vDatas["mainContent"]=$vDatas["headerMenu"]=$vDatas["messengerLivecounter"]=null;
		////	CORPS DE LA PAGE (sauf si validation de formulaire : "lightboxClose")
		if($fileMainVue!=null){
			$pathVue=(strstr($fileMainVue,Req::commonPath)==false) ? Req::getCurModPath() : null;//"app/Common/" déjà précisé?
			$vDatas["mainContent"]=self::getVue($pathVue.$fileMainVue, $vDatasMainVue);
		}
		////	Page principale
		if(static::$isMainPage==true)
		{
			//Wallpaper & Logo footer
			if(!empty(self::$curSpace->wallpaper))	{$vDatas["pathWallpaper"]=CtrlMisc::pathWallpaper(self::$curSpace->wallpaper);}
			elseif(!empty(self::$agora->wallpaper))	{$vDatas["pathWallpaper"]=CtrlMisc::pathWallpaper(self::$agora->wallpaper);}
			else									{$vDatas["pathWallpaper"]=CtrlMisc::pathWallpaper();}
			$vDatas["pathLogoUrl"]=(empty(self::$agora->logoUrl))  ?  OMNISPACE_URL_PUBLIC  :  self::$agora->logoUrl;
			$vDatas["pathLogoTitle"]="<div style='text-align:center;line-height:25px;'>".OMNISPACE_URL_LABEL."</div>".Txt::trad("FOOTER_pageGenerated")." ".round((microtime(true)-TPS_EXEC_BEGIN),3)." seconde";
			//HeaderMenu & MessengerLivecounter
			if(static::moduleName!="offline")
			{
				//Mise à jour récente : notification dans le "pageFooterHtml" pour l'admin de l'espace
				if(self::$curUser->isAdminSpace() && self::$curUser->previousConnection<strtotime(self::$agora->dateUpdateDb))
					{self::$agora->footerHtml="<span id='footerHtmlUpdate'>".Txt::trad("NOTIF_update")." ".Txt::displayDate(self::$agora->dateUpdateDb,"dateMini")." : v".VERSION_AGORA."</span><script>$('#footerHtmlUpdate').effect('pulsate',{times:3},3000);</script>";}
				//Espace Disk
				$vDatasHeader["diskSpacePercent"]=ceil((File::datasFolderSize()/limite_espace_disque)*100);
				$vDatasHeader["diskSpaceAlert"]=($vDatasHeader["diskSpacePercent"]>70) ? true : false;
				//Plugin "shortcuts" de chaque module
				$vDatasHeader["pluginsShortcut"]=array();
				$pluginParams=array("type"=>"shortcut");
				foreach(self::$curSpace->moduleList() as $tmpModule){
					if(method_exists($tmpModule["ctrl"],"plugin"))  {$vDatasHeader["pluginsShortcut"]=array_merge($vDatasHeader["pluginsShortcut"],$tmpModule["ctrl"]::plugin($pluginParams));}
				}
				//Validation d'inscription d'utilisateurs  && Affiche la liste des espaces  && Liste des modules (Url, Description, Libellé, Class de l'icone)
				$vDatasHeader["userInscriptionValidate"]=(self::$curUser->isAdminSpace() && Db::getVal("SELECT count(*) FROM ap_userInscription WHERE _idSpace=".(int)self::$curSpace->_id)>0);
				$vDatasHeader["showSpaceList"]=(count(Ctrl::$curUser->getSpaces())>1) ? true : false;
				$vDatasHeader["moduleList"]=self::$curSpace->moduleList();
				foreach($vDatasHeader["moduleList"] as $moduleKey=>$tmpModule)	{$vDatasHeader["moduleList"][$moduleKey]["isCurModule"]=($tmpModule["moduleName"]==static::moduleName)  ?  true  : false;}
				//Récupère le menu principal : "HeaderMenu"
				$vDatas["headerMenu"]=self::getVue(Req::commonPath."VueHeaderMenu.php",$vDatasHeader);
				//Récupère le livecounter (cf. "CtrlMisc::actionLivecounterUpdate()")
				if(self::$curUser->messengerEnabled())  {$vDatas["messengerLivecounter"]=self::getVue(Req::commonPath."VueMessengerLivecounter.php");}
			}
		}
		////	Notifications passées en Get/Post
		if(Req::isParam("msgNotif")){
			foreach(Req::getParam("msgNotif") as $tmpNotif)  {self::addNotif($tmpNotif);}
		}
		////	Affiche le résultat
		$vDatas["skinCss"]=(!empty(self::$agora->skin) && self::$agora->skin=="black")  ?  "black"  :  "white";
		echo self::getVue(Req::commonPath."VueStructure.php",$vDatas);
	}

	/*
	 * Ajoute une notification à afficher via "VueStructure.php"
	 * $message : message spécifique OU clé de traduction
	 * $type : "info" / "success" / "warning"
	 */
	public static function addNotif($messageTrad, $type="notice")
	{
		//Ajoute la notification si elle n'est pas déjà présente
		if(Tool::arraySearch(self::$msgNotif,$messageTrad)==false)  {self::$msgNotif[]=["message"=>$messageTrad,"type"=>$type];}
	}

	/*
	 * Ajoute les "Ctrl::$msgNotif" à une url avant une redirection
	 */
	public static function urlMsgNotif()
	{
		$urlMsgNotif=null;
		foreach(self::$msgNotif as $message)  {$urlMsgNotif.="&msgNotif[]=".urlencode($message["message"]);}
		return $urlMsgNotif;
	}

	/*
	 * Redirige une page
	 */
	public static function redir($url)
	{
		//Url de redirection, si besoin avec des notifications
		$redirUrl=$url.self::urlMsgNotif();
		//Redirection depuis une iframe ou une page principale
		if(static::$isMainPage==false)	{echo "<script> parent.location.href=\"".$redirUrl."\"; </script>";}
		else							{header("Location: ".$redirUrl);}
		//Fin de script..
		exit;
	}

	/*
	 * Ferme le lightbox (exple : après édition d'un element)
	 */
	public static function lightboxClose($urlMoreParms=null)
	{
		//Initialise les params de reload de la page principale, puis affiche une page vide pour lancer le JS "lightboxClose()"
		self::$lightboxClose=true;
		self::$lightboxCloseParams=self::urlMsgNotif().$urlMoreParms;
		static::displayPage();
		//Fin de script..
		exit;
	}

	/*
	 * Affiche "element inaccessible" (ou autre) & Fin de script..
	 */
	public static function noAccessExit($message=null)
	{
		if($message===null)  {$message=Txt::trad("inaccessibleElem");}
		echo "<h2><img src='app/img/important.png' style='vertical-align:middle;'> ".$message."</h2>";
		exit;
	}

	/*
	 * Vérif si on est sur un Host
	 */
	public static function isHost()
	{
		return defined("HOST_DOMAINE");
	}


	/***************************************************************************************************************************/
	/*******************************************	SPECIFIC METHODS	********************************************************/
	/***************************************************************************************************************************/

	/*
	 * Valide au besoin la connexion d'un utilisateur
	 */
	public static function userConnection()
	{
		$connectViaForm=(Req::isParam(["connectLogin","connectPassword"])) ? true : false;
		$connectViaCookie=(!empty($_COOKIE["AGORAP_LOG"]) && !empty($_COOKIE["AGORAP_PASS"]) && Req::isParam("disconnect")==false) ? true : false;
		if(self::$curUser->isUser()==false && ($connectViaForm==true || $connectViaCookie==true))
		{
			////	CONTROLES DE CONNEXION
			//Connexion demandé ou auto ?
			if($connectViaForm==true)		{$login=Req::getParam("connectLogin");  $passwordSha1=MdlUser::passwordSha1(Req::getParam("connectPassword"));}
			elseif($connectViaCookie==true)	{$login=$_COOKIE["AGORAP_LOG"];			$passwordSha1=$_COOKIE["AGORAP_PASS"];}
			//Identification + recup des infos sur l'user
			$sqlPasswordSha1="AND password=".Db::format($passwordSha1);
			if(self::isHost())  {$sqlPasswordSha1=Host::sqlPassword(Req::getParam("connectPassword"),$sqlPasswordSha1);}
			$tmpUser=Db::getLine("SELECT * FROM ap_user WHERE login=".Db::format($login)." ".$sqlPasswordSha1);
			//User pas connecté : tente une identification LDAP (avec creation d'user à la volee)
			if(empty($tmpUser) && $connectViaForm==true)  {$tmpUser=MdlUser::ldapConnectCreateUser(Req::getParam("connectLogin"),Req::getParam("connectPassword"));}
			//...User toujours pas connecté : message d'erreur et déconnexion
			if(empty($tmpUser))   {self::addNotif("NOTIF_identification");  self::redir("?disconnect=1");}
			//User déjà connecté sur un autre poste & avec une autre ip (pas de controle sur l'appli)
			if(Req::isMobileApp()==false){
				$autreIpConnected=Db::getVal("SELECT count(*) FROM ap_userLivecouter WHERE _idUser=".(int)$tmpUser["_id"]." AND date > '".(time()-60)."' AND ipAdress NOT LIKE '".$_SERVER["REMOTE_ADDR"]."'");
				if($autreIpConnected>0)   {self::addNotif("NOTIF_presentIp");  self::redir("?disconnect=1");}
			}

			////	VALIDATION DE L'UTILISATEUR
			//Init la session
			$_SESSION=["_idUser"=>(int)$tmpUser["_id"]];//Id du client
			//Maj les dates de "lastConnection" && "previousConnection"
			$previousConnection=(!empty($tmpUser["lastConnection"]))  ?  $tmpUser["lastConnection"]  :  time();
			Db::query("UPDATE ap_user SET lastConnection='".time()."', previousConnection=".Db::format($previousConnection)." WHERE _id=".(int)$tmpUser["_id"]);
			//Charge l'utilisateur courant !!
			self::$curUser=self::getObj("user",$_SESSION["_idUser"]);
			self::$userHasConnected=true;
			self::addLog("connexion");
			//Récupère les préférences
			foreach(Db::getTab("select * from ap_userPreference where _idUser=".self::$curUser->_id) as $tmpPref)  {$_SESSION["pref"][$tmpPref["keyVal"]]=$tmpPref["value"];}
			//Enregistre login & password pour une connexion auto
			if(Req::isParam("rememberMe")){
				setcookie("AGORAP_LOG", $login, (time()+315360000));
				setcookie("AGORAP_PASS", $passwordSha1, (time()+315360000));
			}
		}
	}

	/*
	 * Selection de l'espace courant
	 */
	public static function curSpaceSelection()
	{
		//User venant d'être identifié via Post/Cookie  OU  Redir. depuis la page de connexion : Redir auto d'user déjà connecté || Espace demandé (Switch d'espace par un user - Accès à un espace par un "guest")
		if(self::$userHasConnected==true  ||  (static::moduleName=="offline" && (self::$curUser->isUser() || Req::isParam("_idSpaceAccess"))))
		{
			////	Liste des espaces de l'user.. et redirection si aucun espace dispo
			$idSpaceSelected=null;
			$spacesOfCurUser=self::$curUser->getSpaces();
			////	Aucun espace dispo (pas en page de connexion, sinon affiche une erreur avec les notifs mail de créa d'objet qui contiennent un "_idSpaceAccess"..)
			if(empty($spacesOfCurUser) && static::moduleName!="offline"){
				self::addNotif("NOTIF_noSpaceAccess");
				self::redir("?disconnect=1");
			}
			////	Espace demandé (switch d'user / accès de Guest)
			elseif(Req::isParam("_idSpaceAccess")){
				foreach($spacesOfCurUser as $objSpace){
					if($objSpace->_id==Req::getParam("_idSpaceAccess") && (self::$curUser->isUser() || empty($objSpace->password) || $objSpace->password==Req::getParam("password")))   {$idSpaceSelected=$objSpace->_id;  break;}
				}
			}
			////	Espace de connexion de l'utilisateur OU espace par defaut
			elseif(self::$curUser->isUser())
			{
				if(!empty(self::$curUser->connectionSpace)){
					foreach($spacesOfCurUser as $objSpace){
						if($objSpace->_id==self::$curUser->connectionSpace)    {$idSpaceSelected=$objSpace->_id;  break;}
					}
				}
				if(empty($idSpaceSelected)){
					$firstSpace=reset($spacesOfCurUser);
					$idSpaceSelected=$firstSpace->_id;
				}
			}
			////	Chargement de l'espace & Redirection
			if(!empty($idSpaceSelected)){
				$_SESSION["_idSpace"]=$idSpaceSelected;
				$spaceModules=self::getObj("space",$idSpaceSelected)->moduleList();
				if(Req::isParam("targetObjUrl") && self::$curUser->isUser())	{self::redir(Req::getParam("targetObjUrl"));}//Redir vers le controleur/objet demandé (notif)
				if(!empty($spaceModules))										{self::redir("?ctrl=".key($spaceModules));}//Redir vers le premier module de l'espace
				else															{self::addNotif("NOTIF_noSpaceAccess");   self::redir("?disconnect=1");}//Aucun module dans l'espace..
			}
			////	User identifié : Aucun espace dispo..
			elseif(self::$curUser->isUser()){
				self::addNotif("NOTIF_noSpaceAccess");
				self::redir("?disconnect=1");
			}
		}
		//Sortie de l'espace si aucun espace sélectionné & controleur interne sélectionné..
		elseif(empty(self::$curSpace->_id) && static::moduleName!="offline")    {self::redir("?disconnect=1");}
	}

	/*
	 * Recupère un objet avec "self::getObj()" et le "targetObjId" en GET/POST.  Exple: $_GET['targetObjId']="fileFolder-19"  (ou "fileFolder" pour un nouvel objet)
	 * Les "targetObjId" ont un controle d'accès automatique via "initCtrl()" !
	 */
	public static function getTargetObj($targetObjId=null)
	{
		//Aucun $targetObjId en paramètre et "targetObjId" spécifié par Get/Post
		if($targetObjId==null && Req::isParam("targetObjId"))	{$targetObjId=Req::getParam("targetObjId");}
		//renvoie l'objet ciblé
		if(!empty($targetObjId))
		{
			//Nouvel objet / Objet existant
			$targetObjId=explode("-",$targetObjId);
			$targetObj=(empty($targetObjId[1]))  ?  self::getObj($targetObjId[0])  :  self::getObj($targetObjId[0],$targetObjId[1]);
			//Ajoute "_idContainer" pour le controle d'accès d'un nouvel objet (cf "actionBiduleEdit()")
			if(Req::isParam("_idContainer") && empty($targetObj->_id) && empty($targetObj->_idContainer))  {$targetObj->_idContainer=Req::getParam("_idContainer");}
			//renvoie l'objet
			return $targetObj;
		}
	}

	/*
	 * Recupère les objets selectionnés et envoyés via GET/POST. Exple: $_GET['targetObjects[fileFolder]']="2-4-7"
	 */
	public static function getTargetObjects($objectType=null)
	{
		$returnObjects=array();
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

	/*
	 * Récupère une préférence  (tri des résultats/type d'affichage/etc)
	 * Passé en parametre GET/POST ? Enregistre en BDD ?
	 */
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
		if(isset($_SESSION["pref"][$prefDbKey]))	{return $_SESSION["pref"][$prefDbKey];}
	}

	/*
	 * Ajout d'un log
	 * Action : "connexion", "add", "modif", "delete"
	 */
	public static function addLog($action, $curObj=null, $comment=null)
	{
		//S'il s'agit d'une action d'un user ou d'un invité qui ajoute un élément
		if(self::$curUser->isUser() || $action=="add")
		{
			////	Init la requête Sql
			$moduleName=Req::$curCtrl;
			$sqlObjectType=$sqlObjectId=null;
			$sqlLogValues=", date=".Db::dateNow().", _idUser=".Db::format(self::$curUser->_id).", _idSpace=".Db::format(self::$curSpace->_id).", ip=".Db::format($_SERVER["REMOTE_ADDR"]);
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
			Db::query("INSERT INTO ap_log SET action=".Db::format($action).", moduleName=".Db::format($moduleName).", objectType=".Db::format($sqlObjectType).", _idObject=".Db::format($sqlObjectId).", comment=".Db::format($comment)." ".$sqlLogValues);
			////	Supprime les anciens logs (lancé qu'une fois par session)
			if(empty($_SESSION["logsCleared"])){
				Db::query("DELETE FROM ap_log WHERE action='connexion'	AND UNIX_TIMESTAMP(date) <= ".intval(time()-(14*86400)));										 //Logs de connexion			: conservés 2 semaines
				Db::query("DELETE FROM ap_log WHERE action='delete'		AND UNIX_TIMESTAMP(date) <= ".intval(time()-(360*86400)));										 //logs de suppression			: conservés un an
				Db::query("DELETE FROM ap_log WHERE action NOT IN ('connexion','delete') AND UNIX_TIMESTAMP(date) <= ".intval(time()-(self::$agora->logsTimeOut*86400)));//Autres logs (add,modif,etc)	: en fonction du "logsTimeOut" (120j par défaut)
				$_SESSION["logsCleared"]=true;
			}
		}
	}

	/*
	 * Recupere les plugins de type "Folder" d'un module
	 */
	public static function getPluginsFolders($pluginParams, $MdlObjectFolder)
	{
		$pluginsList=[];
		foreach($MdlObjectFolder::getPluginObjects($pluginParams) as $objFolder)
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