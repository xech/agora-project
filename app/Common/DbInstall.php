<?php
/**
* This file is part of the Agora-Project Software package
*
* @copyleft Agora-Project <https://www.agora-project.net>
* @license GNU General Public License (GPL-2.0)
*/


/*
 * INSTALL DE LA DB
 */
class DbInstall
{
	/********************************************************************************************************
	 * VERIFIE LA CONNEXION À LA DATABASE
	 ********************************************************************************************************/
	public static function dbControl($db_host, $db_login, $db_password, $db_name)
	{
		//Instancie PDO
		try{
			//Vérif la connexion à la db
			$objPDO=new PDO("mysql:host=".$db_host.";dbname=".$db_name.";charset=utf8;", $db_login, $db_password, array(PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION));
			//Vérif si l'appli est déjà installée sur la db
			$result=$objPDO->query("SHOW TABLES FROM `".$db_name."` WHERE `Tables_in_".$db_name."` LIKE 'gt_%' OR `Tables_in_".$db_name."` LIKE 'ap_%'");
			if(count($result->fetchAll(PDO::FETCH_COLUMN,0))>0)  {return "errorDbExist";}						//Db et tables déjà créées
		}
		//Erreur de connexion à la bdd
		catch(PDOException $exception){
			if(preg_match("/(unknown|inconnue)/i",$exception->getMessage()))	{return "dbAbsent";}			//Bdd à créer
			else																{return "errorDbConnection";}	//Pas de connexion à la Bdd
		}
		//Pas d'erreur : Db disponible
		return "dbAvailable";
	}

	/********************************************************************************************************
	 * FORMATE UNE VALEUR DANS UNE REQUETE
	 ********************************************************************************************************/
	public static function format($objPDO, $value)
	{
		$value=trim((string)$value);							//Cast la $value
		return empty($value) ? "NULL" : $objPDO->quote($value);	//Résultat filtré par pdo (addslashes, quotes, etc)
	}

	/********************************************************************************************
	 * ACTION : INSTALL LA BDD AVEC LES PARAMETRES DE BASE  (pas de "trad()"!)
	 ********************************************************************************************/
	public static function initParams($objPDO, $installParams)
	{
		if(!empty($objPDO) && !empty($installParams)){
			//Créé les variables de l'Array
			extract($installParams);
			//Paramétrage général  +  Espace principal  +  User principal (admin dénéral)
			$objPDO->query("UPDATE ap_agora SET `name`=".self::format($objPDO,$spaceName).", `version_agora`=".self::format($objPDO,$version_agora).", `timezone`=".self::format($objPDO,$spaceTimeZone).", `lang`=".self::format($objPDO,$spaceLang).", `dateUpdateDb`=NOW()");
			$objPDO->query("UPDATE ap_space SET `name`=".self::format($objPDO,$spaceName).", `description`=".self::format($objPDO,$spaceDescription).", `public`=".self::format($objPDO,$spacePublic)." WHERE _id=1");
			$objPDO->query("UPDATE ap_user  SET `login`=".self::format($objPDO,$adminMailLogin).", `password`=".self::format($objPDO,$adminPassword).", `name`=".self::format($objPDO,$adminName).", `firstName`=".self::format($objPDO,$adminFirstName).", `mail`=".self::format($objPDO,$adminMailLogin)." WHERE _id=1");
		}
	}
}