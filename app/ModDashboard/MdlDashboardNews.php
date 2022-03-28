<?php
/**
* This file is part of the Agora-Project Software package.
*
* @copyright (c) Agora-Project Limited <https://www.agora-project.net>
* @license GNU General Public License, version 2 (GPL-2.0)
*/


/*
 * MODELE DES ACTUALITES
 */
class MdlDashboardNews extends MdlObject
{
	const moduleName="dashboard";
	const objectType="dashboardNews";
	const dbTable="ap_dashboardNews";
	const htmlEditorField="description";
	const hasAttachedFiles=true;
	const hasNotifMail=true;
	const hasUsersLike=true;
	const hasUsersComment=true;
	protected static $_hasAccessRight=true;
	public static $requiredFields=["description"];
	public static $searchFields=["description"];
	public static $sortFields=["dateCrea@@desc","dateCrea@@asc","dateModif@@desc","dateModif@@asc","_idUser@@asc","_idUser@@desc","description@@asc","description@@desc"];

	/**************************************************************************************************
	 * STATIC : NEWS A AFFICHER
	 * $mode :  Nb de news archivées = "offlineNewsNb"  ||  Affichage infinite scroll = "scroll"
	 **************************************************************************************************/
	public static function getNews($mode, $newsOffset=0)
	{
		// Archive/désarchive automatiquement les news
		if(empty($_SESSION["dashboardNewsUpdated"])){
			Db::query("UPDATE ".static::dbTable."  SET offline=1     WHERE dateOffline is not null  AND UNIX_TIMESTAMP(dateOffline)<".time());
			Db::query("UPDATE ".static::dbTable."  SET offline=null  WHERE dateOnline is not null   AND UNIX_TIMESTAMP(dateOnline)<".time()."  AND (dateOffline is null or UNIX_TIMESTAMP(dateOffline)>".time().")");
			$_SESSION["dashboardNewsUpdated"]=true;
		}
		// Init/Switch l'affichage des news archivées
		if(empty($_SESSION["offlineNewsShow"]) || Req::isParam("offlineNewsShow"))  {$_SESSION["offlineNewsShow"]=(bool)(Req::param("offlineNewsShow")=="true");}
		// Nb de news archivées
		if($mode=="offlineNewsNb")	{return Db::getVal("SELECT count(*) FROM ".static::dbTable." WHERE ".static::sqlDisplay()." AND offline=1");}
		// News pour l'affichage "infinite scroll"
		else{
			$sqlOffline=($_SESSION["offlineNewsShow"]==true)  ?  "AND offline=1"  :  "AND offline is null";
			$sqlLimit="LIMIT 10 OFFSET ".((int)$newsOffset * 10);//"infinite scroll" par blocs de 10
			return Db::getObjTab(static::objectType, "SELECT * FROM ".static::dbTable." WHERE ".static::sqlDisplay()." ".$sqlOffline." ".static::sqlSort("une desc")." ".$sqlLimit);
		}
	}

	/*******************************************************************************************
	 * DROIT D'AJOUTER UNE NOUVELLE NEWS
	 *******************************************************************************************/
	public static function addRight()
	{
		return (Ctrl::$curUser->isAdminSpace() || (Ctrl::$curUser->isUser() && Ctrl::$curSpace->moduleOptionEnabled(self::moduleName,"adminAddNews")==false));
	}
}