<?php
/**
* This file is part of the Agora-Project Software package
*
* @copyleft Agora-Project <https://www.agora-project.net>
* @license GNU General Public License (GPL-2.0)
*/


/*
 * CONTROLEUR DU MODULE DES "LOG"
 */
class CtrlLog extends Ctrl
{
	public static $fieldsList=["date","userName","spaceName","moduleName","action","objectType","comment"];
	const moduleName="log";

	/*******************************************************************************************
	 * VUE : PAGE PRINCIPALE
	 *******************************************************************************************/
	public static function actionDefault()
	{
		if(Ctrl::$curUser->isSpaceAdmin()==false)  {self::noAccessExit();}
		static::displayPage("VueIndex.php");
	}

	/*******************************************************************************************
	 * LISTE DES LOGS DE TOUT LE SITE / DE L'ESPACE
	 *******************************************************************************************/
	public static function logList()
	{
		$results=[];
		//Filtre uniquement les logs de l'espace (simple admin d'espace et plus d'un espace disponible)
		$sqlCurSpace=(Ctrl::$curUser->isGeneralAdmin()==false && Db::getVal("select count(*) from ap_space")>1)  ?  "WHERE _idSpace=".Ctrl::$curSpace->_id  :  null;
		//Renvoie la liste des logs 
		foreach(Db::getTab("SELECT * FROM ap_log ".$sqlCurSpace." ORDER BY date desc") as $tmpLog)
		{
			//Init
			$curLog=[];
			//Ajoute chaque champ du log
			foreach(static::$fieldsList as $tmpField)
			{
				if($tmpField=="date")			{$curLog["date"]=substr($tmpLog["date"],0,16);}
				elseif($tmpField=="userName")	{$curLog["userName"]=Ctrl::getObj("user",$tmpLog["_idUser"])->getLabel();}
				elseif($tmpField=="spaceName")	{$curLog["spaceName"]=Ctrl::getObj("space",$tmpLog["_idSpace"])->name;}
				elseif($tmpField=="moduleName"){
					$moduleTrad=strtoupper($tmpLog["moduleName"])."_MODULE_NAME";
					$curLog["moduleName"]=(Txt::isTrad($moduleTrad))  ?  Txt::trad($moduleTrad)  :  $tmpLog["moduleName"];
				}
				elseif($tmpField=="action"){
					$actionTrad="LOG_".$tmpLog["action"];
					$curLog["action"]=Txt::isTrad($actionTrad)  ?  Txt::trad($actionTrad)  :  $tmpLog["action"];
				}
				elseif($tmpField=="objectType"){
					if(!empty($tmpLog["objectType"]) && stristr($tmpLog["objectType"],"folder"))	{$curLog["objectType"]=Txt::trad("OBJECTfolder");}//dossier
					elseif(Txt::isTrad("OBJECT".$tmpLog["objectType"]))								{$curLog["objectType"]=Txt::trad("OBJECT".$tmpLog["objectType"]);}//autre objet
					else																			{$curLog["objectType"]=null;}
				}else{
					$curLog[$tmpField]=$tmpLog[$tmpField];
				}
			}
			//Ajoute le log
			$results[]=$curLog;
		}
		return $results;
	}

	/*******************************************************************************************
	 * INPUT "SELECT" POUR FILTER LES LOGS EN FONCTION D'UN CHAMP DES LOGS
	 *******************************************************************************************/
	public static function fieldFilterSelect($fieldName)
	{
		//Récupère les options du menu
		$optionsFilter=null;
		$sqlGetVals=($fieldName=="spaceName")  ?  "SELECT DISTINCT `name` FROM ap_space ORDER BY `name` asc"  :  "SELECT DISTINCT ".$fieldName." FROM ap_log ORDER BY ".$fieldName." asc";
		foreach(Db::getCol($sqlGetVals)  as  $tmpVal){
			if(Txt::isTrad("LOG_".$tmpVal))									{$tmpLabel=Txt::trad("LOG_".$tmpVal);}//"action"
			elseif(Txt::isTrad(strtoupper($tmpVal)."_MODULE_NAME"))	{$tmpLabel=Txt::trad(strtoupper($tmpVal)."_MODULE_NAME");}//"moduleName"
			else															{$tmpLabel=$tmpVal;}//"spaceName"
			$optionsFilter.="<option value=\"".$tmpLabel."\">".$tmpLabel."</option>";
		}
		//renvoie le "select" du champ
		return "<select name=\"search_".Txt::trad("LOG_".$fieldName)."\" class='searchInit'><option value=''>".Txt::trad("LOG_filter")." ".Txt::trad("LOG_".$fieldName)."</option>".$optionsFilter."</select>";
	}

	/*******************************************************************************************
	 * ACTION : TELECHARGE LES LOGS AU FORMAT CSV
	 *******************************************************************************************/
	public static function actionLogsDownload()
	{
		if(Ctrl::$curUser->isSpaceAdmin()==false)  {self::noAccessExit();}
		//Init le fichier & Entete des logs
		$fileContent=null;
		foreach(CtrlLog::$fieldsList as $tmpFieldId)	{$fileContent.='"'.Txt::trad("LOG_".$tmpFieldId).'";';}
		$fileContent.="\n";
		//Liste des logs
		foreach(self::logList() as $tmpLog){
			foreach($tmpLog as $tmpLogVal)	{$fileContent.='"'.$tmpLogVal.'";';}
			$fileContent.="\n";
		}
		File::download(Ctrl::$agora->name." - LOGS.csv", false, $fileContent);
	}
}