<?php
/**
* This file is part of the Agora-Project Software package.
*
* @copyright (c) Agora-Project Limited <https://www.agora-project.net>
* @license GNU General Public License, version 2 (GPL-2.0)
*/


////	URLS
$OMNISPACE_URL_PUBLIC=(Req::isDevServer())  ?  "http://".$_SERVER["HTTP_HOST"]."/omnispace"  :  "https://www.omnispace.fr";//IP de dev (Ionic and Co) || IP de Prod
define("OMNISPACE_URL_PUBLIC", $OMNISPACE_URL_PUBLIC);
define("OMNISPACE_URL_LABEL","www.omnispace.fr");

////    VERSION D'AP
define("VERSION_AGORA","3.7.4.2");//Version courant d'AP (sur 3 ou 4 niveaux)
define("VERSION_AGORA_PHP_MINIMUM","5.5");//Version PHP minimum

////    INIT LE "PATH_DATAS" & CHEMINS SPÉCIFIQUES
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
define("PATH_ICON_FOLDER", "app/img/folder/");
define("WALLPAPER_DEFAULT_PREFIX","default@@");//ID des fonds d'écran par défaut