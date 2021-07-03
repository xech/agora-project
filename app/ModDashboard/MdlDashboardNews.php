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
	const hasAttachedFiles=true;
	const hasNotifMail=true;
	const hasUsersLike=true;
	const hasUsersComment=true;
	const htmlEditorField="description";
	protected static $_hasAccessRight=true;
	public static $requiredFields=["description"];
	public static $searchFields=["description"];
	public static $sortFields=["dateCrea@@desc","dateCrea@@asc","dateModif@@desc","dateModif@@asc","_idUser@@asc","_idUser@@desc","description@@asc","description@@desc"];

	/*******************************************************************************************
	 * STATIC : RÉCUPÈRE LES NEWS POUR L'INFINITE SCROLL ($mode: "count"/"list". $newsOffsetCpt est le compteur d'affichage de l'infinite scroll)
	 *******************************************************************************************/
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
		if($mode=="count")	{return Db::getVal("SELECT count(*) FROM ".static::dbTable." WHERE ".static::sqlDisplay()." ".$sqlOffline);}
		else				{return Db::getObjTab(static::objectType, "SELECT * FROM ".static::dbTable." WHERE ".static::sqlDisplay()." ".$sqlOffline." ".static::sqlSort("une desc")."  LIMIT 10 OFFSET ".((int)$newsOffsetCpt * 10));}//Affiche les news par blocks de 10
	}

	/*******************************************************************************************
	 * DROIT D'AJOUTER UNE NOUVELLE NEWS
	 *******************************************************************************************/
	public static function addRight()
	{
		return (Ctrl::$curUser->isAdminSpace() || (Ctrl::$curUser->isUser() && Ctrl::$curSpace->moduleOptionEnabled(self::moduleName,"adminAddNews")==false));
	}
}