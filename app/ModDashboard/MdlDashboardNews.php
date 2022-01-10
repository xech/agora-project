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

	/*******************************************************************************************
	 * STATIC : RÉCUPÈRE LES NEWS POUR L'INFINITE SCROLL
	 * $mode :  Count des news :"count"  ||  Affichage avec "infinite scroll" : "scroll"
	 * $offline : récupère uniquement les news "offline"
	 *******************************************************************************************/
	public static function getNews($mode, $offline=false, $offsetCpt=0)
	{
		// Archiver/désarchiver automatiquement des news
		if(empty($_SESSION["dashboardNewsUpdated"])){
			Db::query("UPDATE ".static::dbTable."  SET offline=1     WHERE dateOffline is not null  AND UNIX_TIMESTAMP(dateOffline)<".time());
			Db::query("UPDATE ".static::dbTable."  SET offline=null  WHERE dateOnline is not null   AND UNIX_TIMESTAMP(dateOnline)<".time()."  AND (dateOffline is null or UNIX_TIMESTAMP(dateOffline)>".time().")");
			$_SESSION["dashboardNewsUpdated"]=true;
		}
		// uniquement les news "offline" ?
		$sqlOffline=($offline==true)  ?  "AND offline=1"  :  "AND offline is null";
		// Count des news  ||  Affichage avec "infinite scroll" (LIMIT + OFFSET)
		if($mode=="count")	{return Db::getVal("SELECT count(*) FROM ".static::dbTable." WHERE ".static::sqlDisplay()." ".$sqlOffline);}
		else				{return Db::getObjTab(static::objectType, "SELECT * FROM ".static::dbTable." WHERE ".static::sqlDisplay()." ".$sqlOffline." ".static::sqlSort("une desc")."  LIMIT 10 OFFSET ".((int)$offsetCpt * 10));}//"infinite scroll" par blocs de 10
	}

	/*******************************************************************************************
	 * DROIT D'AJOUTER UNE NOUVELLE NEWS
	 *******************************************************************************************/
	public static function addRight()
	{
		return (Ctrl::$curUser->isAdminSpace() || (Ctrl::$curUser->isUser() && Ctrl::$curSpace->moduleOptionEnabled(self::moduleName,"adminAddNews")==false));
	}
}