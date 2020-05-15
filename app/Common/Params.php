<?php
/**
* This file is part of the Agora-Project Software package.
*
* @copyright (c) Agora-Project Limited <https://www.agora-project.net>
* @license GNU General Public License, version 2 (GPL-2.0)
*/


//Version et Url du soft
define("VERSION_AGORA","3.7.2");//sur 3 niveaux (4 max.)
define("VERSION_AGORA_PHP_MINIMUM","5.5");
define("OMNISPACE_URL_PUBLIC","https://www.omnispace.fr");
define("OMNISPACE_URL_LABEL","www.omnispace.fr");

//Init le "PATH_DATAS" & Chemins spécifiques
if(is_file("Host.php"))	{require_once "Host.php";  Host::initHost();}
else					{define("PATH_DATAS","DATAS/");}
define("PATH_MOD_FILE",	PATH_DATAS."modFile/");
define("PATH_MOD_USER",	PATH_DATAS."modUser/");
define("PATH_MOD_CONTACT", PATH_DATAS."modContact/");
define("PATH_MOD_DASHBOARD", PATH_DATAS."modDashboard/");
define("PATH_OBJECT_ATTACHMENT", PATH_DATAS."objectAttachment/");
define("PATH_WALLPAPER_CUSTOM", PATH_DATAS."wallpaper/");
define("PATH_WALLPAPER_DEFAULT", "app/img/wallpaper/");
define("PATH_ICON_FOLDER", "app/img/folder/");
define("WALLPAPER_DEFAULT_PREFIX","default@@");//ID des fonds d'écran par défaut