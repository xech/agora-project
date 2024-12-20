<?php
/**
* This file is part of the Agora-Project Software package
*
* @copyleft Agora-Project <https://www.agora-project.net>
* @license GNU General Public License, version 2 (GPL-2.0)
*/


/*
 * CLASSE DE CONNEXION À LA DB
 */
class Db
{
	private static $_objPDO=null;
	//public static $readsNb=null;
	//public static $writesNb=null;

	/*******************************************************************************************
	 * RENVOIE L'OBJET PDO INITIALISÉ QU'UNE SEULE FOIS
	 *******************************************************************************************/
	private static function objPDO()
	{
		//Instancie PDO
		if(self::$_objPDO===null){
			//Connection PDO
			try{
				//Utilise l'encodage "utf8mb4" pour les emojis sur mobile ("utf8" pour les versions inférieures à PHP 7)
				$dbCharset=(version_compare(PHP_VERSION,7,">="))  ?  "utf8mb4"  :  "utf8";
				//Aucune DB n'est spécifiée : dbInstall!  /  Sinon on établit une connexion
				if(!defined("db_name") || !db_name)	{throw new Exception("dbInstall_dbNameUndefined");}
				else								{self::$_objPDO=new PDO("mysql:host=".db_host.";dbname=".db_name.";charset=".$dbCharset, db_login, db_password, array(PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION));}
				//Pas de connexion, ni d'exception : dbInstall!
				if(!is_object(self::$_objPDO))	{throw new Exception("dbInstall_pdoIsNull");}
			}
			//Erreur envoyé par PDO : renvoi une exception de base, avec demande d'install
			catch(PDOException $exception){
				throw new Exception("dbInstall_".$exception);
			}
		}
		return self::$_objPDO;
	}

	/*******************************************************************************************
	 * EXÉCUTE UNE REQUÊTE SQL (INSERT/UPDATE/DELETE/ETC)
	 *******************************************************************************************/
	public static function query($sqlQuery, $returnLastInsertId=false)
	{
		$queryResult=self::objPDO()->query($sqlQuery);//(preg_match("/(update|insert|delete)/i",$sqlQuery)) ? self::$writesNb++ : self::$readsNb++;
		if($returnLastInsertId==true)	{return self::objPDO()->lastInsertId();}
		else							{return $queryResult;}
	}

	/*******************************************************************************************
	 * RETOURNE UN TABLEAU DE RÉSULTAT
	 *******************************************************************************************/
	public static function getTab($sqlQuery)
	{
		$result=self::objPDO()->query($sqlQuery);//self::$readsNb++;
		return $result->fetchAll(PDO::FETCH_ASSOC);//faster than "fetch()"
	}

	/*******************************************************************************************
	 * RETOURNE UN TABLEAU D'OBJETS : AVEC ID DE L'OBJET EN KEY
	 *******************************************************************************************/
	public static function getObjTab($objectType, $sqlQuery)
	{
		//"getObj" pour récupérer l'objet en cache s'il a déjà été chargé (pas de "FETCH_CLASS").
		$returnTab=[];
		$result=self::objPDO()->query($sqlQuery);
		foreach($result->fetchAll(PDO::FETCH_ASSOC) as $objValues)  {$returnTab[$objValues["_id"]]=Ctrl::getObj($objectType, $objValues);}
		return $returnTab;
	}

	/*******************************************************************************************
	 * RETOURNE UNE LIGNE DE RESULTAT : PREMIER ENREGISTREMENT RETOURNÉ AVEC SES CHAMPS
	 *******************************************************************************************/
	public static function getLine($sqlQuery)
	{
		$result=self::objPDO()->query($sqlQuery);//self::$readsNb++;
		return $result->fetch(PDO::FETCH_ASSOC);
	}

	/****************************************************************************************************************************
	 * RETOURNE UNE COLONNE D'ENREGISTREMENTS : PREMIER CHAMP D'UNE LISTE D'ENREGISTREMENTS (liste d'identifiants par exemple)
	 ****************************************************************************************************************************/
	public static function getCol($sqlQuery)
	{
		$result=self::objPDO()->query($sqlQuery);//self::$readsNb++;
		return $result->fetchAll(PDO::FETCH_COLUMN,0);//que le premier champs
	}

	/*******************************************************************************************
	 * RETOURNE LA VALEUR D'UN CHAMP : PREMIER CHAMPS DU PREMIER RÉSULTAT D'UNE REQUETE
	 *******************************************************************************************/
	public static function getVal($sqlQuery)
	{
		$result=self::objPDO()->query($sqlQuery);//self::$readsNb++;
		$record=$result->fetch(PDO::FETCH_NUM);
		if(!empty($record))  {return $record[0];}
	}

	/*******************************************************************************************
	 * NUMERO DE VERSION DE MARIADB
	 *******************************************************************************************/
	public static function dbVersion()
	{
		$dbVersion=self::getVal("select version()");												//Récupère la version complete (Ex: "10.5.18-MariaDB")
		return (preg_match("/maria/i",$dbVersion)?"MariaDB":"MySql")." ".strtok($dbVersion, "-");	//Renvoie "MariaDb" ou "Mysql" && le numero de version (texte avant le 1er "-". Ex: 10.5.18)
	}

	/*******************************************************************************************
	 * FORMATE UNE VALEUR DANS UNE REQUETE (insert/update/etc)
	 *******************************************************************************************/
	public static function format($value, $options=null)
	{
		$value	=trim((string)$value);																						//cast la valeur en "string"
		$options=trim((string)$options);																					//cast les options en "string"
		if(empty($value))  {return "NULL";}																					//Retourne "NULL"
		else{
			if(stristr($options,"url") && !stristr($value,"http"))	{$value="http://".$value;}								//URL : ajoute "http://"
			if(stristr($options,"sqlLike"))							{$value="%".$value."%";}								//Recheche via "LIKE" : délimite $value par des "%" ("sqlPlugins()" and co)
			if(stristr($options,"inputDate"))						{$value=Txt::formatDate($value,"inputDate","dbDate");}	//Formate la date d'un datepicker
			return self::objPDO()->quote($value);																			//Retourne le résultat filtré par pdo (addslashes, quotes, etc)
		}
	}

	/********************************************************************************************
	 * RECUPERE UNE VALEUR GET/POST PUIS LA FORMATE DANS UNE REQUETE INSERT/UPDATE/ETC
	 ********************************************************************************************/
	public static function param($keyParam, $options=null)
	{
		return self::format(Req::param($keyParam),$options);
	}
	
	/*******************************************************************************************
	 * FORMAT UN TABLEAU DANS UNE REQUETTE
	 *******************************************************************************************/
	public static function formatTab2txt($text)
	{
		return Db::format(Txt::tab2txt($text));
	}

	/******************************************************************************************************************************************
	 * FORMATE LA DATE ACTUELLE D'UN CHAMP "DATETIME", AVEC LE TIMEZONE SPÉCIFIÉ (équivalent à "now()", cf. "date_default_timezone_set()")
	 ******************************************************************************************************************************************/
	public static function dateNow()
	{
		return "'".date("Y-m-d H:i:s")."'";
	}
	
	
	/***************************************************************************************************************************/
	/*******************************************	SPECIFIC METHODS	********************************************************/
	/***************************************************************************************************************************/

	/*******************************************************************************************
	 * SAUVEGARDE LA DB
	 *******************************************************************************************/
	public static function getDump()
	{
		$dumpPath=PATH_DATAS."BackupDatabase_".db_name.".sql";
		//Récupère le dump via "exec()"
		if(Req::isLinux() && function_exists('exec')){
			exec("mysqldump --user=".db_login." --password=".db_password." --host=".db_host." ".db_name." > ".$dumpPath);
		}
		//Créé un dump
		else{
			// Recupere chaque table
			$dumpTxt="";
			foreach(self::getCol("SHOW TABLES FROM `".db_name."`") as $tableName)
			{
				// Structure de la table
				$sqlTmp=self::getLine("SHOW CREATE TABLE `".$tableName."`");
				$dumpTxt.=$sqlTmp["Create Table"].";\r\n\r\n";
				// Contenu de la table
				foreach(self::getTab("SELECT * FROM `".$tableName."`") as $tmpRecord){
					$dumpTxt.="INSERT INTO `".$tableName."` VALUES(";
					foreach($tmpRecord as $tmpField){
						$dumpTxt.=(is_null($tmpField))  ?  "NULL,"  :  self::objPDO()->quote($tmpField).",";//Tjs utiliser "is_null"
					}
					$dumpTxt=trim($dumpTxt,",");
					$dumpTxt.=");\r\n\r\n";
				}
			}
			// Enregistre le fichier sql
			$fp=fopen($dumpPath, "w");
			fwrite($fp, $dumpTxt);
			fclose($fp);
		}
		//Retourne le path du dump
		return $dumpPath;
	}
}