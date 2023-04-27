<?php
/**
* This file is part of the Agora-Project Software package.
*
* @copyright (c) Agora-Project Limited <https://www.agora-project.net>
* @license GNU General Public License, version 2 (GPL-2.0)
*/


////	URLS
$OMNISPACE_URL_PUBLIC=(Req::isDevServer())  ?  "https://".$_SERVER['SERVER_NAME']."/omnispace"  :  "https://www.omnispace.fr";//IP de dev (Flutter and Co) || IP de Prod
define("OMNISPACE_URL_PUBLIC", $OMNISPACE_URL_PUBLIC);
define("OMNISPACE_URL_LABEL","www.omnispace.fr");

////    PATH DES "DATAS" & PATHS SPÉCIFIQUES
if(is_file("Host.php"))	{require_once "Host.php";  Host::initHost();}
else					{define("PATH_DATAS","DATAS/");}
define("PATH_TMP", PATH_DATAS."tmp/");
define("PATH_MOD_FILE",	PATH_DATAS."modFile/");
define("PATH_MOD_USER",	PATH_DATAS."modUser/");
define("PATH_MOD_CONTACT", PATH_DATAS."modContact/");
define("PATH_MOD_DASHBOARD", PATH_DATAS."modDashboard/");
define("PATH_OBJECT_ATTACHMENT", PATH_DATAS."objectAttachment/");
define("PATH_WALLPAPER_CUSTOM", PATH_DATAS."wallpaper/");
define("PATH_WALLPAPER_DEFAULT", "app/img/wallpaper/");
define("WALLPAPER_DEFAULT_DB_PREFIX","default@@");//Préfixe en DB des wallpapers par défaut
define("PATH_ICON_FOLDER", "app/img/folder/");