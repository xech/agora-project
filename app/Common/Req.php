<?php
/**
* This file is part of the Agora-Project Software package.
*
* @copyright (c) Agora-Project Limited <https://www.agora-project.net>
* @license GNU General Public License, version 2 (GPL-2.0)
*/


/*
 * Autoloader des classes de base et des controleurs (pas des classes des modèles : chargées par le controleur!)
 */
function agoraAutoloader($className)
{
	if(is_file(Req::commonPath.$className.".php"))	{require_once Req::commonPath.$className.".php";}			//exple: "app/common/Txt.php"
	elseif(is_file(Req::modClassPath($className)))	{require_once Req::modClassPath($className);}				//exple: "app/modFile/MdlFile.php"
	else											{throw new Exception("Class '".$className."' unreachable");}//sinon : class inaccessible
}
spl_autoload_register("agoraAutoloader");


/*
 * traite les requetes entrantes
 */
class Req
{
	const commonPath="app/Common/";
	private static $_getPostParams;
	private static $_isMobile=null;
	public static $curCtrl;		//exple : "offline"
	public static $curAction;	//exple : "default"

	/*
	 * Init
	 */
	function __construct()
	{
		//Fusionne GET+POST & filtre les XSS
		self::$_getPostParams=array_merge($_GET,$_POST);
		foreach(self::$_getPostParams as $tmpKey=>$tmpVal)
		{
			if(is_array($tmpVal)){
				foreach($tmpVal as $tmpKey2=>$tmpVal2)	{self::$_getPostParams[$tmpKey][$tmpKey2]=self::filterParam($tmpKey2,$tmpVal2);}
			}else{
				self::$_getPostParams[$tmpKey]=self::filterParam($tmpKey,$tmpVal);
			}
		}
		//Classe du controleur courant & Methode de l'action courante
		self::$curCtrl=(self::isParam("ctrl")) ? self::getParam("ctrl") : "offline";
		self::$curAction=(self::isParam("action")) ? self::getParam("action") : "default";
		$curCtrlClass="Ctrl".ucfirst(self::$curCtrl);
		$curActionMethod="action".ucfirst(self::$curAction);
		//Init le temps d'execution & charge les Params + Config
		define("TPS_EXEC_BEGIN",microtime(true));
		require_once self::commonPath."Params.php";
		require_once PATH_DATAS."config.inc.php";
		//Lance l'action demandée
		try{
			//"isInstalling" : pas d'initialisation du controleur
			if(self::isInstalling()==false)  {$curCtrlClass::initCtrl();}
			//Lance le controleur / Lance une 'Exception' / Erreur '404' dans le header
			if(method_exists($curCtrlClass,$curActionMethod))	{$curCtrlClass::$curActionMethod();}
			elseif(self::isDevServer())							{throw new Exception("Action '".$curActionMethod."' not found");}
			else												{header("HTTP/1.0 404 Not Found");  exit;}
		}
		//Gestion des exceptions
		catch(Exception $e){
			$this->displayExeption($e);
		}
	}

	/*
	 * Verifie si tous les parametres GET/POST ont été spécifiés et ne sont pas vides
	 */
	public static function isParam($keys)
	{
		//Keys au format "array"
		if(!is_array($keys))  {$keys=[$keys];}
		//Return false si un des parametres n'est pas spécifié OU que sa valeur est vide
		foreach($keys as $key){
			if(!isset(self::$_getPostParams[$key]) || empty(self::$_getPostParams[$key]))  {return false;}
		}
		//"True" si toutes les valeurs sont OK
		return true;
	}

	/*
	 * Recupere un parametre GET/POST
	 */
	public static function getParam($key)
	{
		if(self::isParam($key)){
			return (is_string(self::$_getPostParams[$key]))  ?  trim(self::$_getPostParams[$key])  :  self::$_getPostParams[$key];
		}
	}

	/*
	 * Filtre un parametre (préserve des insertion XSS)
	 */
	public static function filterParam($tmpKey, $value)
	{
		//Verif qu'il s'agit d'une string et non pas un tableau ou autre
		if(is_string($value))
		{
			//Enlève le javascript
			$value=preg_replace('#(<[^>]+?[\x00-\x20"\'])(?:on|xmlns)[^>]*+>#iu', '$1>', $value);
			if(preg_match("/^footerHtml$/i",$tmpKey)==false || Ctrl::isHost()==false)  {$value=preg_replace('#<script(.*?)>(.*?)</script>#is', '', $value);}
			//Enleve les balises html (sauf <br><hr><a><img>) pour les parametres qui ne proviennent pas de l'éditeur ("description"/"editorDraft" du tinyMce ou "footerHtml")
			if(preg_match("/^(description|editorDraft|footerHtml)$/i",$tmpKey)==false)  {$value=strip_tags($value,"<br><hr><a><img>");}//Tester avec les News et avec l'url "index.php?ctrl=dashboard&msgNotif[]=<svg/onload=alert(/myXss/)>"
		}
		return $value;
	}

	/*
	 * Path d'une class dans module  (La 2ème partie du nom de classe contient le nom du module. exple: "MdlFileFolder" => "File")
	 */
	public static function modClassPath($className)
	{
		$majWords=preg_split("/(?=[A-Z])/",trim($className));//'MdlFileFolder' => array('','Mdl','File','Folder') => 'app/ModFile'
		if(!empty($majWords[2]))	{return "app/Mod".ucfirst($majWords[2])."/".$className.".php";}
	}

	/*
	 * Recupère le chemin du module courant
	 */
	public static function getCurModPath()
	{
		return "app/Mod".ucfirst(self::$curCtrl)."/";
	}

	/*
	 * Vérifie si on est en mode 'DEV'
	 */
	public static function isDevServer()
	{
		return stristr($_SERVER["HTTP_HOST"],"debian");
	}

	/*
	 * Navigation en mode "mobile" si le width est inférieur à 1024px  (IDEM Common.js && Common.css)
	 */
	public static function isMobile()
	{
		if(self::$_isMobile===null)  {self::$_isMobile=(isset($_COOKIE["windowWidth"]) && $_COOKIE["windowWidth"]<1024);}
		return self::$_isMobile;
	}

	/*
	 * Navigation tactile sur App
	 */
	public static function isMobileApp()
	{
		return (!empty($_COOKIE["mobileAppliTrue"]));
	}

	/*
	 * Affiche une erreur d'execution
	 */
    private function displayExeption(Exception $exception)
	{
		//Install à réaliser et pas de hosting : redirige vers le formulaire d'install
		if(preg_match("/dbInstall/i",$exception) && self::isInstalling()==false && Ctrl::isHost()==false)  {Ctrl::redir("?ctrl=offline&action=install&disconnect=1");}
		//Affiche le message
        echo "<h3 style='text-align:center;margin-top:50px;'><img src='app/img/important.png' style='vertical-align:middle'> internal error  :<br><br>".(Ctrl::isHost()?$exception->getMessage():$exception)."<br><br>[<a href='?ctrl=offline'>Back</a>]</h3>";
		exit;
    }
	
	
	/***************************************************************************************************************************/
	/*******************************************	SPECIFIC METHODS	********************************************************/
	/***************************************************************************************************************************/

	/*
	 * Recupère l'URL de l'espace (exple  "https://www.mon-espace.net/agora/index.php?ctrl=file&targetObjId=file-55"  devient  "https://www.mon-espace.net/agora")
	 */
	public static function getSpaceUrl($httpPrefix=true)
	{
		//Note : Toutes les requêtes passent par l'"index.php" à la racine de l'app
		if($httpPrefix==true)	{$httpPrefix=(!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS']!="off") ? "https://" : "http://";}
		else					{$httpPrefix=null;}
		$path=$_SERVER["REQUEST_URI"];
		if(strstr($path,"?"))  {$path=substr($path,0,strrpos($path,"?"));}//enlève les paramètres?
		$path=str_replace("index.php","",$path);//enlève "index.php"?
		return $httpPrefix.$_SERVER["HTTP_HOST"].rtrim($path,"/");
	}

	/*
	 * Vérif si l'appli est en cour d'install
	 */
	public static function isInstalling()
	{
		return (self::$curCtrl=="offline" && stristr(self::$curAction,"install"));
	}

	/*
	 * Vérif la version de PHP
	 */
	public static function verifPhpVersion()
	{
		if(version_compare(PHP_VERSION,VERSION_AGORA_PHP_MINIMUM,"<")){
			echo "<h3>".Txt::trad("INSTALL_PhpOldVersion")."</h3><h4>PHP version required : ".VERSION_AGORA_PHP_MINIMUM."</h4><h4>PHP current version : ".PHP_VERSION."</h4>";
			exit;
		}
	}
}