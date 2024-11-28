<?php
/**
* This file is part of the Agora-Project Software package
*
* @copyleft Agora-Project <https://www.agora-project.net>
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
	const descriptionEditor=true;
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
			Db::query("UPDATE ".static::dbTable."  SET offline=null  WHERE dateOnline is not null   AND UNIX_TIMESTAMP(dateOnline)<".time()."  AND (dateOffline IS NULL OR UNIX_TIMESTAMP(dateOffline)>".time().")");
			$_SESSION["dashboardNewsUpdated"]=true;
		}
		// Init/Switch l'affichage des news archivées
		if(Req::isParam("offlineNews"))  {$_SESSION["offlineNews"]=(bool)(Req::param("offlineNews")=="true");}
		// Nb de news archivées  &&  News pour l'affichage "infinite scroll"
		if($mode=="offlineNewsNb")	{return Db::getVal("SELECT count(*) FROM ".static::dbTable." WHERE ".static::sqlDisplay()." AND offline=1");}
		else{
			$sqlOffline=(empty($_SESSION["offlineNews"]))  ?  "AND offline IS NULL"  :  "AND offline='1'";
			$sqlLimit="LIMIT 10 OFFSET ".((int)$newsOffset * 10);//"infinite scroll" par blocs de 10
			return Db::getObjTab(static::objectType, "SELECT * FROM ".static::dbTable." WHERE ".static::sqlDisplay()." ".$sqlOffline." ".static::sqlSort("`une` DESC")." ".$sqlLimit);
		}
	}

	/*******************************************************************************************
	 * DROIT D'AJOUTER UNE NOUVELLE NEWS
	 *******************************************************************************************/
	public static function addRight()
	{
		return (Ctrl::$curUser->isSpaceAdmin() || (Ctrl::$curUser->isUser() && Ctrl::$curSpace->moduleOptionEnabled(self::moduleName,"adminAddNews")==false));
	}
}