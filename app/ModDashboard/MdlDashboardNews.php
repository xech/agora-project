<?php
/**
* This file is part of the Agora-Project Software package.
*
* @copyright (c) Agora-Project Limited <https://www.agora-project.net>
* @license GNU General Public License, version 2 (GPL-2.0)
*/


/*
 * Modele des actualites
 */
class MdlDashboardNews extends MdlObject
{
	const moduleName="dashboard";
	const objectType="dashboardNews";
	const dbTable="ap_dashboardNews";
	const hasAccessRight=true;
	const hasAttachedFiles=true;
	const hasNotifMail=true;
	const hasUsersLike=true;
	const hasUsersComment=true;
	const htmlEditorField="description";
	public static $requiredFields=array("description");
	public static $searchFields=array("description");
	public static $sortFields=array("dateCrea@@desc","dateCrea@@asc","dateModif@@desc","dateModif@@asc","_idUser@@asc","_idUser@@desc","description@@asc","description@@desc");

	/*
	 * STATIC : Récupère les news ($mode: "count"/"list". $newsOffsetCpt pour l'infinite scroll)
	 */
	public static function getNews($mode, $newsOffsetCpt=0, $offlineNews=false)
	{
		//// Archiver/désarchiver automatiquement des news
		if(empty($_SESSION["dashboardNewsUpdated"])){
			Db::query("UPDATE ".static::dbTable."  SET offline=1     WHERE dateOffline is not null  AND UNIX_TIMESTAMP(dateOffline)<".time());
			Db::query("UPDATE ".static::dbTable."  SET offline=null  WHERE dateOnline is not null   AND UNIX_TIMESTAMP(dateOnline)<".time()."  AND (dateOffline is null or UNIX_TIMESTAMP(dateOffline)>".time().")");
			$_SESSION["dashboardNewsUpdated"]=true;
		}
		//// Nombre ou Liste de news
		$sqlOffline=(!empty($offlineNews)) ? "AND offline=1" : "AND offline is null";//Archivée (Offline) ?
		if($mode=="count")	{return Db::getVal("SELECT count(*) FROM ".static::dbTable." WHERE ".static::sqlDisplayedObjects()." ".$sqlOffline);}
		else				{return Db::getObjTab(static::objectType, "SELECT * FROM ".static::dbTable." WHERE ".static::sqlDisplayedObjects()." ".$sqlOffline." ".static::sqlSort("une desc")."  LIMIT 10 OFFSET ".((int)$newsOffsetCpt * 10));}//Affiche les news par blocks de 10
	}

	/*
	 * Droit d'ajouter une nouvelle news
	 */
	public static function addRight()
	{
		return (Ctrl::$curUser->isAdminSpace() || (Ctrl::$curUser->isUser() && Ctrl::$curSpace->moduleOptionEnabled(self::moduleName,"adminAddNews")==false));
	}
}