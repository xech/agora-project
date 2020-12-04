<?php
/**
* This file is part of the Agora-Project Software package.
*
* @copyright (c) Agora-Project Limited <https://www.agora-project.net>
* @license GNU General Public License, version 2 (GPL-2.0)
*/


/*
 * Classe de connexion à la bdd
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
				//Utilise l'encodage "utf8mb4" pour les emojis sur mobile ("utf8" pour les versions inférieures à PHP 7 : cf. hosting Free.fr)
				$dbCharset=(version_compare(PHP_VERSION,7,">="))  ?  "utf8mb4"  :  "utf8";
				//Aucune bdd n'est spécifiée : dbInstall!  /  Sinon on établit une connexion
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
		//"getObj" pour récupérer l'objet en cache s'il a déjà été chargé (donc pas de "FETCH_CLASS").
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

	/*******************************************************************************************
	 * RETOURNE UNE COLONNE D'ENREGISTREMENTS : PREMIER CHAMP D'UNE LISTE D'ENREGISTREMENTS (liste d'identifiants par exemple)
	 *******************************************************************************************/
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
	 * NUMÉRO DE VERSION DE MariaDB
	 *******************************************************************************************/
	public static function dbVersion()
	{
		$dbVersion=self::objPDO()->getAttribute(PDO::ATTR_SERVER_VERSION);
		return str_replace(strstr($dbVersion,"-"),null,$dbVersion);//Enlève les détails après "-" (ex: "5.5.5-10.1.26-MariaDB-0+deb9u1")
	}

	/*******************************************************************************************
	 * FORMATE UNE VALEUR DANS UNE REQUETE (insert,update,etc)
	 *******************************************************************************************/
	public static function format($text, $options=null)
	{
		$text=trim($text);
		if(empty($text))  {return "NULL";}
		else{
			//Filtre le résultat
			if(!stristr($options,"editor"))							{$text=htmlspecialchars(strip_tags($text));}//Input basic : enlève les balises html et convertit les caractères spéciaux ('€'->'&#128;')
			if(stristr($options,"float"))							{$text=str_replace(",",".",$text);}			//Valeur flottante : remplace les virgules par des points
			if(stristr($options,"url") && !stristr($text,"http"))	{$text="http://".$text;}					//Url : ajoute "http://"
			if(stristr($options,"likeSearch"))						{$text="%".$text."%";}						//Search : délimite par des "%"
			//Formate une date provenant d'un datepicker + timepicker?
			if(stristr($options,"datetime"))	{$text=Txt::formatDate($text,"inputDatetime","dbDatetime");}
			elseif(stristr($options,"date"))	{$text=Txt::formatDate($text,"inputDate","dbDate");}
			//renvoie le résultat filtré par pdo (trim, addslashes, délimite par des quotes, etc)
			return (stristr($options,"noquotes"))  ?  $text  :  self::objPDO()->quote($text);
		}
	}

	/*******************************************************************************************
	 * FORMATE UNE VALEUR GET/POST DANS UNE REQUETE (insert,update,etc)
	 *******************************************************************************************/
	public static function formatParam($keyParam, $options=null)
	{
		return self::format(Req::getParam($keyParam),$options);
	}
	
	/*******************************************************************************************
	 * FORMAT UN TABLEAU DANS UNE REQUETTE
	 *******************************************************************************************/
	public static function formatTab2txt($text)
	{
		return Db::format(Txt::tab2txt($text));
	}

	/*******************************************************************************************
	 * FORMATE LA DATE ACTUELLE D'UN CHAMP "DATETIME" OU "DATE", AVEC LE TIMEZONE SPÉCIFIÉ (équivalent à "now()" mais assure le formatage via "date_default_timezone_set()")
	 *******************************************************************************************/
	public static function dateNow()
	{
		return "'".strftime("%Y-%m-%d %H:%M:%S")."'";
	}
	
	
	/***************************************************************************************************************************/
	/*******************************************	SPECIFIC METHODS	********************************************************/
	/***************************************************************************************************************************/

	/*******************************************************************************************
	 * SAUVEGARDE LA BDD
	 *******************************************************************************************/
	public static function getDump()
	{
		//Path
		$dumpPath=PATH_DATAS."BackupDatabase_".db_name.".sql";
		//Via "exec()" OU Via un script
		if(Ctrl::isHost())  {exec("mysqldump --user=".db_login." --password=".db_password." --host=".db_host." ".db_name." > ".$dumpPath);}
		else
		{
			// Recupere chaque table
			$tabDump=[];
			foreach(self::getCol("SHOW TABLES FROM `".db_name."`") as $tableName)
			{
				// Structure de la table
				$createTable=self::getLine("SHOW CREATE TABLE ".$tableName);
				$tabDump[]=str_replace(array("\r","\n"),"",$createTable["Create Table"]).";";
				// Contenu de la table
				foreach(self::getTab("SELECT * FROM ".$tableName) as $record){
					$tmpInsert="INSERT INTO ".$tableName." VALUES(";
					foreach($record as $fieldRecord)	{$tmpInsert.=($fieldRecord=="") ? "NULL," : self::objPDO()->quote($fieldRecord).",";}//pas de "empty()" car doit enregistrer aussi "0"
					$tabDump[]=trim($tmpInsert,",").");";
				}
			}
			// Transforme le tableau en texte,  Enregistre le fichier sql,  Retourne le chemin du fichier
			$fp=fopen($dumpPath, "w");
			fwrite($fp, implode("\n", $tabDump));
			fclose($fp);
		}
		return $dumpPath;
	}
}