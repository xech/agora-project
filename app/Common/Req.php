<?php
/**
* This file is part of the Agora-Project Software package
*
* @copyleft Agora-Project <https://www.agora-project.net>
* @license GNU General Public License (GPL-2.0)
*/


/********************************************************************************************
 * AUTOLOADER DE CLASSE DE CONTROLEUR
 ********************************************************************************************/
function agoraAutoloader($className)
{
	if(is_file(Req::commonPath.$className.".php"))	{require_once Req::commonPath.$className.".php";}
	elseif(is_file(Req::modClassPath($className)))	{require_once Req::modClassPath($className);}
	else											{throw new Exception("Page introuvable (".$className.")");}
}
spl_autoload_register("agoraAutoloader");


/*
 * TRAITE LES REQUETES ENTRANTES
 */
class Req
{
	const commonPath="app/Common/";
	private static $_paramsGP;
	private static $_appVersion=null;
	public static $curCtrl;	
	public static $curAction;

	/********************************************************************************************
	 * INIT
	 ********************************************************************************************/
	public function __construct()
	{
		////	Filtre et enregistre les parametres GET/POST (valeur ou tableau de valeurs)
		foreach(array_merge($_GET,$_POST) as $key=>$val){
			if(!is_array($val))  {self::$_paramsGP[$key]=self::paramFilter($key,$val);}
			else{
				foreach($val as $key2=>$val2)  {self::$_paramsGP[$key][$key2]=self::paramFilter($key,$val2);}
			}
		}
		////	Classe du controleur courant (ex: "offline")  &  Methode de l'action courante (ex: "default")
		self::$curCtrl=(self::isParam("ctrl")) ? self::param("ctrl") : "offline";
		self::$curAction=(self::isParam("action")) ? self::param("action") : "default";
		$CtrlClass="Ctrl".ucfirst(self::$curCtrl);
		$ActionMethod="action".ucfirst(self::$curAction);
		////	Init le temps d'execution & charge les Params + Config
		define("TPS_EXEC_BEGIN",microtime(true));
		require_once self::commonPath."Params.php";
		require_once PATH_DATAS."config.inc.php";
		////	Lance l'action demandée
		try{
			if(self::isInstalling()==false)				{$CtrlClass::initCtrl();}														//Controleur principal
			if(method_exists($CtrlClass,$ActionMethod))	{$CtrlClass::$ActionMethod();}													//Controleur demandé
			else										{throw new Exception("Page introuvable : Action '".$ActionMethod."'");  exit;}	//Lance une Exception
		}
		////	Gestion des exceptions
		catch(Exception $error){
			$this->displayExeption($error);
		}
	}

	/********************************************************************************************************
	 * RÉCUPÈRE LE NUMÉRO DE VERSION DE L'APPLI  &&  MODIF SI BESOIN LES FICHIERS JS/CSS
	 ********************************************************************************************************/
	public static function appVersion()
	{
		if(self::$_appVersion===null){																//Init le cache :
			self::$_appVersion=trim((string)file_get_contents('app/VERSION.txt'));					//Récupère le numéro de version (tjs avec "trim()")
			if(!file_exists("app/Common/js-css-".self::$_appVersion))								//Renomme si besoin le dossier des JS/CSS de l'appli
				{rename(glob("app/Common/js-css*")[0], "app/Common/js-css-".self::$_appVersion);}
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
			if(!isset(self::$_paramsGP[$key]) || (empty(self::$_paramsGP[$key]) && self::$_paramsGP[$key]!=="0"))  {return false;}
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
			if($key=="notify")							{return (array)self::$_paramsGP[$key];}	//"notify" tjs en array, même s'il n'y en a qu'une passée en GET
			elseif(is_string(self::$_paramsGP[$key]))	{return trim(self::$_paramsGP[$key]);}	//trim sur le texte
			else										{return self::$_paramsGP[$key];}
		}
	}

	/***************************************************************************************************************
	 * FILTRE LES PARAMETRES GET/POST (Cf. XSS / code inject)
	 * Test rapide :  ?description=<svg/onload=alert(1)>  ||  ?notify[]=HELX");alert(1);//`
	 ***************************************************************************************************************/
	private static function paramFilter($key, $val)
	{
		$val=(string)$val;																									//Cast la valeur d'entrée
		if(!empty($val)){																									//Vérif que la valeur existe (cf 'strip_tags()' error)
			if(preg_match("/^(description|editorDraft|message)$/i",$key)){													//Filtre le contenu de l'editeur TinyMce ou un Post du messenger
				require_once('app/misc/htmlpurifier/HTMLPurifier.auto.php');												//Charge la librairie HTMLPurifier	
				$config=HTMLPurifier_Config::createDefault();																//Config par défaut  (note : les attributs "data-" comme "data-fancybox" sont supprimés)
				$config->set('Core.Encoding', 'UTF-8');																		//Encodage UTF-8 (conserve les caractères spéciaux)
				$config->set('Attr.EnableID', true);																		//Autorise les attributs id
				$config->set('HTML.SafeIframe', true);																		//Autorise les videos Iframes
				$config->set('HTML.SafeEmbed', true);																		//Autorise les videos Embed
				$config->set('URI.SafeIframeRegexp', '%(youtube\.com|youtu\.be|twitch\.tv|dailymotion\.com|vimeo\.com)%');	//Regex des vidéos externes
				$config->set('Attr.AllowedFrameTargets', '_blank');															//Autorise la balise <a target="_blank">
				$def=$config->getHTMLDefinition(true);																		//Balises spécifiques :
				$def->addElement('video','Block','Flow','Common',['controls'=>'Enum#controls','width'=>'Length','height'=>'Length']);//Autorise la balise <video> et ses attributs
				$def->addElement('source','Inline','Empty','Common',['src'=>'URI','type'=>'Text']);							//Autorise la balise <source> et ses attributs (cf balise <video>)
				$purifier=new HTMLPurifier($config);																		//Crée un $purifier
				$val=$purifier->purify($val);																				//Filtre le code html
    			$caracAccent=['à','â','ä','é','è','ê','ë','î','ï','ô','ö','ù','û','ü','ç',"\xc2\xa0"];						//Liste des caractères accentués et espaces Unicode (cf '<p>&nbsp;</p>' remplacés précédement par '<p> </p>')
    			$caracHtml  =['&agrave;','&acirc;','&auml;','&eacute;','&egrave;','&ecirc;','&euml;','&icirc;','&iuml;','&ocirc;','&ouml;','&ugrave;','&ucirc;','&uuml;','&ccedil;','&nbsp;'];//Equivalents HTML
				$val=str_replace($caracAccent, $caracHtml, $val);															//Convertit les caractère accentués en entités HTML															
			}
			else{																											//Filtre principal
				$val=strip_tags($val,'<br>');																				//Supprime les tags html (sauf <br> pour les notify)
				if($key=="objUrl")	{$val=filter_var($val, FILTER_SANITIZE_URL);}											//Filtre une URL
				else				{$val=htmlspecialchars($val, ENT_COMPAT | ENT_HTML5, 'UTF-8', false);}					//Convertit  & " < >  en entité HTML ('false' pour ne pas convertir les entités existantes)
				$val=str_replace('&lt;br&gt;','<br>',$val);																	//Retranscrit les <br>
			}
		}
		return $val;
	}

	/*******************************************************************************************************************************
	 * PATH D'UNE CLASS DANS MODULE  (La 2ème partie du nom de classe contient le nom du module. Ex: "MdlFileFolder" => "File")
	 *******************************************************************************************************************************/
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
	public static function getCurUrl($protocol=true)
	{
		//Spécifie le protocole dans l'url (vide si affichage simplifié de l'url)
		if($protocol==false)				{$protocol=null;}
		elseif(!empty($_SERVER['HTTPS']))	{$protocol="https://";}
		else								{$protocol="http://";}
		//Renvoie l'url sans les paramètres ni le dernier "/" (Note : toutes les requêtes passent par "index.php")
		return $protocol.$_SERVER['SERVER_NAME'].rtrim(dirname($_SERVER["PHP_SELF"]),'/');
	}

	/********************************************************************************************************
	 * VÉRIF HOSTED SPACE
	 ********************************************************************************************************/
	public static function isHost()
	{
		return defined("HOST_DOMAINE");
	}

	/********************************************************************************************************
	 * VÉRIF LINUX
	 ********************************************************************************************************/
	public static function isLinux()
	{
		return preg_match("/linux/i",PHP_OS);
	}

	/********************************************************************************************
	 * VÉRIF MODE DEV
	 ********************************************************************************************/
	public static function isDevServer()
	{
		return preg_match('/^(omnispace\.local\.net|debian12)$/i', $_SERVER['SERVER_NAME']);
	}

	/********************************************************************************************
	 * VÉRIF AFFICHAGE MOBILE/RESPONSIVE <= 1024PX  (Idem CSS & JS)
	 ********************************************************************************************/
	public static function isMobile()
	{
		return (isset($_COOKIE["windowWidth"]) && $_COOKIE["windowWidth"]<=1024);
	}

	/********************************************************************************************
	 * VÉRIF AFFICHAGE SUR APP MOBILE (quelquesoit la resolution, 'macintosh' = Ipads récents)
	 ********************************************************************************************/
	public static function isMobileApp()
	{
		return (!empty($_COOKIE["mobileAppli"]) && preg_match("/(android|iphone|ipad|macintosh)/i",$_SERVER['HTTP_USER_AGENT']));
	}

	/********************************************************************************************
	 * AFFICHE UNE ERREUR D'EXECUTION
	 ********************************************************************************************/
    private function displayExeption(Exception $exception)
	{
		////	Install d'Agora-Project en Auto-hébergement
		if(preg_match("/dbInstall/i",$exception->getMessage()) && self::isInstalling()==false && self::isHost()==false)
			{Ctrl::redir("?ctrl=offline&action=install&disconnect=1");}
		////	Affiche le message et lien "Retour"
        echo '<h3 style="text-align:center;margin-top:50px;font-size:24px">
				<img src="app/img/importantBig.png" style="vertical-align:middle;margin-right:20px">'.$exception->getMessage().'
				<br><br><a href="?ctrl=offline">Retour</a>
			  </h3>';
		exit;
    }


	/***************************************************************************************************************************/
	/*******************************************	SPECIFIC METHODS	********************************************************/
	/***************************************************************************************************************************/


	/********************************************************************************************
	 * SWITCH D'ESPACE : BOUTON DE RETOUR AU MENU DE RECHERCHE (APP MOBILE OU HOST)
	 ********************************************************************************************/
	public static function isSpaceSwitch()
	{
		return (self::isMobileApp() || self::isHost());
	}

	/********************************************************************************************
	 * SWITCH D'ESPACE : URL DE RETOUR AU MENU DE RECHERCHE
	 ********************************************************************************************/
	public static function connectSpaceSwitchUrl()
	{
		return OMNISPACE_URL_PUBLIC."/index.php?ctrl=offline&action=connectSpace&connectSpaceSwitch=true";
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
		$versionPhpMinimum="7.4";
		if(version_compare(PHP_VERSION,$versionPhpMinimum,"<=")){
			echo "<h2><img src='app/img/important.png'> ".str_replace("--CURRENT_VERSION--",static::appVersion(),Txt::trad("INSTALL_PhpOldVersion"))." : ".$versionPhpMinimum." minimum &nbsp; -> current version : ".PHP_VERSION."</h2>";
			exit;
		}
	}
}