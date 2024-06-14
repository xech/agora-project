<?php
/**
* This file is part of the Agora-Project Software package
*
* @copyleft Agora-Project <https://www.agora-project.net>
* @license GNU General Public License, version 2 (GPL-2.0)
*/


/*
 * INSTALL DE LA DB
 */
class DbInstall
{
	/*******************************************************************************************
	 * VERIFIE LA CONNEXION À LA DATABASE
	 *******************************************************************************************/
	public static function dbControl($db_host, $db_login, $db_password, $db_name)
	{
		//Connection PDO
		try{
			//Vérif la connexion à la db
			$pdoSpace=new PDO("mysql:host=".$db_host.";dbname=".$db_name.";charset=utf8;", $db_login, $db_password, array(PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION));
			//Vérif si l'appli est déjà installée sur la db
			$result=$pdoSpace->query("SHOW TABLES FROM `".$db_name."` WHERE `Tables_in_".$db_name."` LIKE 'gt_%' OR `Tables_in_".$db_name."` LIKE 'ap_%'");
			if(count($result->fetchAll(PDO::FETCH_COLUMN,0))>0)  {return "errorDbExist";}						//Db et tables déjà créées
		}
		//Erreur de connexion à la bdd
		catch(PDOException $exception){
			if(preg_match("/(unknown|inconnue)/i",$exception->getMessage()))	{return "dbAbsent";}			//Bdd à créer : pas forcément une erreur
			else																{return "errorDbConnection";}	//Erreur: Pas de connexion à la Bdd
		}
		//Pas d'erreur : Db disponible
		return "dbAvailable";
	}

	/********************************************************************************************
	 * ACTION : INSTALL LA BDD AVEC LES PARAMETRES DE BASE  (pas de "trad()"!)
	 ********************************************************************************************/
	public static function initParams($pdoSpace, $installParams)
	{
		if(!empty($pdoSpace) && !empty($installParams))
		{
			//Créé les variables de l'Array
			extract($installParams);
			//Paramétrage général  +  espace principal  +  user principal (admin général)
			$pdoSpace->query("UPDATE ap_agora SET `name`=".$pdoSpace->quote($spaceName).", version_agora=".$pdoSpace->quote($version_agora).", timezone=".$pdoSpace->quote($spaceTimeZone).", lang=".$pdoSpace->quote($spaceLang).", dateUpdateDb=NOW()");
			$pdoSpace->query("UPDATE ap_space SET `name`=".$pdoSpace->quote($spaceName).", `description`=".$pdoSpace->quote($spaceDescription).", public=".$spacePublic." WHERE _id=1");
			$pdoSpace->query("UPDATE ap_user SET `login`=".$pdoSpace->quote($adminMailLogin).", `password`=".$pdoSpace->quote($adminPassword).", `name`=".$pdoSpace->quote($adminName).", firstName=".$pdoSpace->quote($adminFirstName).", mail=".$pdoSpace->quote($adminMailLogin)." WHERE _id=1");
		}
	}
}