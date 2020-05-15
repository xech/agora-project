<?php
/**
* This file is part of the Agora-Project Software package.
*
* @copyright (c) Agora-Project Limited <https://www.agora-project.net>
* @license GNU General Public License, version 2 (GPL-2.0)
*/


/*
 * Controleur du module des "Log"
 */
class CtrlLog extends Ctrl
{
	public static $fieldsList=array("date","userName","spaceName","moduleName","action","objectType","comment");
	const moduleName="log";

	/*
	 * ACTION PAR DEFAUT
	 */
	public static function actionDefault()
	{
		if(Ctrl::$curUser->isAdminSpace()==false)  {self::noAccessExit();}
		static::displayPage("VueIndex.php");
	}

	/*
	 * renvoie la liste des Logs de tout le site / de l'espace
	 */
	public static function logList()
	{
		$results=[];
		$sqlCurSpace=(Ctrl::$curUser->isAdminGeneral()==false)  ?  "WHERE _idSpace=".Ctrl::$curSpace->_id  :  null; 
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
					$moduleTrad=strtoupper($tmpLog["moduleName"])."_headerModuleName";
					$curLog["moduleName"]=(Txt::isTrad($moduleTrad))  ?  Txt::trad($moduleTrad)  :  $tmpLog["moduleName"];
				}
				elseif($tmpField=="action"){
					$actionTrad="LOG_".$tmpLog["action"];
					$curLog["action"]=Txt::isTrad($actionTrad)  ?  Txt::trad($actionTrad)  :  $tmpLog["action"];
				}
				elseif($tmpField=="objectType"){
					if(stristr($tmpLog["objectType"],"folder"))			{$curLog["objectType"]=Txt::trad("OBJECTfolder");}//dossier
					elseif(Txt::isTrad("OBJECT".$tmpLog["objectType"]))	{$curLog["objectType"]=Txt::trad("OBJECT".$tmpLog["objectType"]);}//autre objet
					else												{$curLog["objectType"]=null;}
				}else{
					$curLog[$tmpField]=$tmpLog[$tmpField];
				}
			}
			//Ajoute le log
			$results[]=$curLog;
		}
		return $results;
	}

	/*
	 * Revoie un "select" pour filter les logs en fonction d'un champ
	 */
	public static function fieldFilterSelect($fieldName)
	{
		//Récupère les options du menu
		$optionsFilter=null;
		$sqlGetVals=($fieldName=="spaceName")  ?  "SELECT DISTINCT name FROM ap_space ORDER BY name asc"  :  "SELECT DISTINCT ".$fieldName." FROM ap_log ORDER BY ".$fieldName." asc";
		foreach(Db::getCol($sqlGetVals)  as  $tmpVal){
			if(Txt::isTrad("LOG_".$tmpVal))									{$tmpLabel=Txt::trad("LOG_".$tmpVal);}//"action"
			elseif(Txt::isTrad(strtoupper($tmpVal)."_headerModuleName"))	{$tmpLabel=Txt::trad(strtoupper($tmpVal)."_headerModuleName");}//"moduleName"
			else															{$tmpLabel=$tmpVal;}//"spaceName"
			$optionsFilter.="<option value=\"".$tmpLabel."\">".$tmpLabel."</option>";
		}
		//renvoie le "select" du champ
		return "<select name=\"search_".Txt::trad("LOG_".$fieldName)."\" class='searchInit'><option value=''>".Txt::trad("LOG_filter")." ".Txt::trad("LOG_".$fieldName)."</option>".$optionsFilter."</select>";
	}

	/*
	 * ACTION : TELECHARGE LES LOGS AU FORMAT CSV
	 */
	public static function actionLogsDownload()
	{
		if(Ctrl::$curUser->isAdminSpace()==false)  {self::noAccessExit();}
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