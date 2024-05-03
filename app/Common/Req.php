<?php
/**
* This file is part of the Agora-Project Software package.
*
* @copyright (c) Agora-Project Limited <https://www.agora-project.net>
* @license GNU General Public License, version 2 (GPL-2.0)
*/


/********************************************************************************************
 * AUTOLOADER DES CLASSES DE BASE ET DES CONTROLEURS (pas des classes des modèles : chargées par le controleur!)
 ********************************************************************************************/
function agoraAutoloader($className)
{
	if(is_file(Req::commonPath.$className.".php"))	{require_once Req::commonPath.$className.".php";}								//Ex: "app/common/Txt.php"
	elseif(is_file(Req::modClassPath($className)))	{require_once Req::modClassPath($className);}									//Ex: "app/modFile/MdlFile.php"
	else											{throw new Exception("Désolé, cette page est introuvable. (".$className.")");}	//Dev: message d'erreur
}
spl_autoload_register("agoraAutoloader");


/*
 * TRAITE LES REQUETES ENTRANTES
 */
class Req
{
	const commonPath="app/Common/";
	private static $_getPostParams;
	private static $_isMobile=null;
	private static $_appVersion=null;
	public static $curCtrl;		//exple : "offline"
	public static $curAction;	//exple : "default"

	/********************************************************************************************
	 * INIT
	 ********************************************************************************************/
	function __construct()
	{
		//Fusionne GET+POST & filtre les XSS
		self::$_getPostParams=array_merge($_GET,$_POST);
		foreach(self::$_getPostParams as $tmpKey=>$tmpVal)
		{
			//Filtre la valeur du parametre OU Filtre le tableau de valeurs du parametre
			if(is_array($tmpVal)==false)  {self::$_getPostParams[$tmpKey]=self::paramFilter($tmpKey,$tmpVal);}
			else{
				foreach($tmpVal as $tmpSubKey=>$tmpSubVal)	{self::$_getPostParams[$tmpKey][$tmpSubKey]=self::paramFilter($tmpSubKey,$tmpSubVal);}
			}
			//S'il s'agit d'un ancien paramètre (cf. liens des notif mail d'édition d'objet) : on ajoute le nouveau paramètre équivalent pour assurer la continuité (à partir de la v21.10)
			if($tmpKey=="targetObjId")			{self::$_getPostParams["typeId"]=self::$_getPostParams["targetObjId"];}
			elseif($tmpKey=="targetObjUrl")		{self::$_getPostParams["objUrl"]=self::$_getPostParams["targetObjUrl"];}
		}
		//Classe du controleur courant & Methode de l'action courante
		self::$curCtrl=(self::isParam("ctrl")) ? self::param("ctrl") : "offline";
		self::$curAction=(self::isParam("action")) ? self::param("action") : "default";
		$curCtrlClass="Ctrl".ucfirst(self::$curCtrl);
		$curActionMethod="action".ucfirst(self::$curAction);
		//Init le temps d'execution & charge les Params + Config
		define("TPS_EXEC_BEGIN",microtime(true));
		require_once self::commonPath."Params.php";
		require_once PATH_DATAS."config.inc.php";
		//Lance l'action demandée
		try{
			if(self::isInstalling()==false)  {$curCtrlClass::initCtrl();}																			//Lance le controleur principal (sauf si Install AP)
			if(method_exists($curCtrlClass,$curActionMethod))	{$curCtrlClass::$curActionMethod();}												//Lance le controleur spécifique
			else												{throw new Exception("Page introuvable : Action '".$curActionMethod."'");  exit;}	//Lance une Exception
		}
		//Gestion des exceptions
		catch(Exception $error){
			$this->displayExeption($error);
		}
	}

	/*******************************************************************************************
	 * RÉCUPÈRE LE NUMÉRO DE VERSION DE L'APPLI  &&  MODIF SI BESOIN LES FICHIERS JS/CSS
	 *******************************************************************************************/
	public static function appVersion()
	{
		//Récupère le numéro de version et l'enregistre en cache
		if(self::$_appVersion===null){
			//Récupère le numéro de version dans le fichier ad'hoc (tjs avec un trim()!)
			self::$_appVersion=trim((string)file_get_contents('app/VERSION.txt'));
			//Renomme si besoin les fichiers JS & CSS
			if(!is_file('app/js/common-'.self::$_appVersion.'.js')){
				rename(glob('app/js/common-*.js')[0], 'app/js/common-'.self::$_appVersion.'.js');
				rename(glob('app/css/common-*.css')[0], 'app/css/common-'.self::$_appVersion.'.css');
			}
		}
		//Retourne le numéro de version
		return self::$_appVersion;
	}

	/********************************************************************************************
	 * VERIFIE SI TOUS LES PARAMETRES GET/POST ONT ÉTÉ SPÉCIFIÉS ET NE SONT PAS VIDES
	 ********************************************************************************************/
	public static function isParam($keys)
	{
		//Keys au format "array"
		if(!is_array($keys))  {$keys=[$keys];}
		//Return false si un des parametres n'est pas spécifié  OU  Sa valeur est vide (mais pas "0")
		foreach($keys as $key){
			if(!isset(self::$_getPostParams[$key]) || (empty(self::$_getPostParams[$key]) && self::$_getPostParams[$key]!=="0"))  {return false;}
		}
		//"True" si toutes les valeurs sont OK
		return true;
	}

	/********************************************************************************************
	 * RECUPERE UN PARAMETRE GET/POST
	 ********************************************************************************************/
	public static function param($key)
	{
		if(self::isParam($key)){
			if($key=="notify")								{return (array)self::$_getPostParams[$key];}	//"notify" tjs en array, même s'il n'y en a qu'une passée en GET
			elseif(is_string(self::$_getPostParams[$key]))	{return trim(self::$_getPostParams[$key]);}		//trim sur le texte
			else											{return self::$_getPostParams[$key];}
		}
	}

	/********************************************************************************************
	 * FILTRE UN PARAMETRE (PRÉSERVE DES INSERTION XSS)
	 ********************************************************************************************/
	public static function paramFilter($tmpKey, $value)
	{
		//Verif qu'il s'agit d'une string et non pas un tableau ou autre
		if(is_string($value))
		{
			//Enlève le javascript
			$value=preg_replace('#(<[^>]+?[\x00-\x20"\'])(?:on|xmlns)[^>]*+>#iu', '$1>', $value);
			if(preg_match("/^footerHtml$/i",$tmpKey)==false || Req::isHost()==false)  {$value=preg_replace('#<script(.*?)>(.*?)</script>#is', '', $value);}
			//Enleve les balises html pour les parametres qui ne proviennent pas de l'éditeur tinyMce (sauf <p><div><span>...). Tester avec les News et avec l'url "index.php?ctrl=dashboard&notify=<svg/onload=alert(/myXss/)>"
			if(preg_match("/^(description|message|editorDraft|footerHtml)$/i",$tmpKey)==false)  {$value=strip_tags($value,"<p><div><span><a><button><img><br><hr>");}
		}
		return $value;
	}

	/********************************************************************************************
	 * PATH D'UNE CLASS DANS MODULE  (La 2ème partie du nom de classe contient le nom du module. Ex: "MdlFileFolder" => "File")
	 ********************************************************************************************/
	public static function modClassPath($className)
	{
		$majWords=preg_split("/(?=[A-Z])/",trim($className));//'MdlFileFolder' => array('','Mdl','File','Folder') => 'app/ModFile'
		if(!empty($majWords[2]))	{return "app/Mod".ucfirst($majWords[2])."/".$className.".php";}
	}

	/********************************************************************************************
	 * RECUPÈRE LE CHEMIN DU MODULE COURANT
	 ********************************************************************************************/
	public static function curModPath()
	{
		return "app/Mod".ucfirst(self::$curCtrl)."/";
	}
	
	/**************************************************************************************************************************************************************
	 * RECUPÈRE L'URL COURANTE DE BASE (exple  "https://www.mon-espace.net/agora/index.php?ctrl=file&typeId=file-55"  =>  "https://www.mon-espace.net/agora")
	 **************************************************************************************************************************************************************/
	public static function getCurUrl($urlProtocol=true)
	{
		//Spécifie le protocole dans l'url (vide si affichage simplifié de l'url)
		if($urlProtocol==false)				{$urlProtocol=null;}
		elseif(!empty($_SERVER['HTTPS']))	{$urlProtocol="https://";}
		else								{$urlProtocol="http://";}
		//Renvoie l'url sans les paramètres ni le dernier "/" (Note : toutes les requêtes passent par "index.php")
		return $urlProtocol.$_SERVER['SERVER_NAME'].rtrim(dirname($_SERVER["PHP_SELF"]),'/');
	}

	/*******************************************************************************************
	 * VÉRIF SI ON EST SUR UN HOST
	 *******************************************************************************************/
	public static function isHost()
	{
		return defined("HOST_DOMAINE");
	}

	/********************************************************************************************
	 * VÉRIFIE SI ON EST EN MODE 'DEV' ('DEBIAN' OU IP LOCALE POUR IONIC DEVAPP)
	 ********************************************************************************************/
	public static function isDevServer()
	{
		return (stristr($_SERVER['SERVER_NAME'],"debian") || stristr($_SERVER['SERVER_NAME'],"192.168"));
	}

	/********************************************************************************************
	 * NAVIGATION EN MODE "MOBILE" SI LE WIDTH EST INFÉRIEUR À 1024PX  (IDEM Common.js && Common.css)
	 ********************************************************************************************/
	public static function isMobile()
	{
		if(self::$_isMobile===null)  {self::$_isMobile=(isset($_COOKIE["windowWidth"]) && $_COOKIE["windowWidth"]<1024);}
		return self::$_isMobile;
	}

	/********************************************************************************************
	 * NAVIGATION SUR APP MOBILE (HOSTED && SELF-HOSTED)
	 ********************************************************************************************/
	public static function isMobileApp()
	{
		return (!empty($_COOKIE["mobileAppli"]));
	}

	/********************************************************************************************
	 * SWITCH D'ESPACE : BOUTON DE RETOUR AU MENU DE RECHERCHE (APP MOBILE OU HOST)
	 ********************************************************************************************/
	public static function isSpaceSwitch()
	{
		return (Req::isMobileApp() || Req::isHost());
	}

	/********************************************************************************************
	 * SWITCH D'ESPACE : URL DE RETOUR AU MENU DE RECHERCHE
	 ********************************************************************************************/
	public static function connectSpaceSwitchUrl()
	{
		return OMNISPACE_URL_PUBLIC."/index.php?ctrl=offline&action=connectSpace&connectSpaceSwitch=true";
	}

	/***************************************************************************************************************************/
	/*******************************************	SPECIFIC METHODS	********************************************************/
	/***************************************************************************************************************************/

	/********************************************************************************************
	 * AFFICHE UNE ERREUR D'EXECUTION
	 ********************************************************************************************/
    private function displayExeption(Exception $exception)
	{
		//Install à réaliser et pas de hosting : redirige vers le formulaire d'install
		if(preg_match("/dbInstall/i",$exception->getMessage()) && self::isInstalling()==false && Req::isHost()==false)  {Ctrl::redir("?ctrl=offline&action=install&disconnect=1");}
		//Affiche le message et si besoin un lien "Retour"
        echo "<h3 style='text-align:center;margin-top:50px;font-size:24px;'><img src='app/img/important.png' style='vertical-align:middle;margin-right:20px;'>".$exception->getMessage();
		if(preg_match("/error/i",$exception->getMessage())==false)  {echo "<br><br><a href='?ctrl=offline'>Retour</a></h3>";}
		exit;
    }

	/********************************************************************************************
	 * VÉRIF SI L'APPLI EST EN COUR D'INSTALL
	 ********************************************************************************************/
	public static function isInstalling()
	{
		return (self::$curCtrl=="offline" && stristr(self::$curAction,"install"));
	}

	/********************************************************************************************
	 * VÉRIF LA VERSION DE PHP
	 ********************************************************************************************/
	public static function verifPhpVersion()
	{
		$versionPhpMinimum="7.0";
		if(version_compare(PHP_VERSION,$versionPhpMinimum,"<=")){
			echo "<h2><img src='app/img/important.png'> ".str_replace("--CURRENT_VERSION--",static::appVersion(),Txt::trad("INSTALL_PhpOldVersion"))." : ".$versionPhpMinimum." minimum &nbsp; -> current version : ".PHP_VERSION."</h2>";
			exit;
		}
	}
}