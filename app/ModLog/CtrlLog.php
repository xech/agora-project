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
	const moduleName="log";
	public static $logFields=["date","userName","moduleName","objectType","action","comment"];

	/********************************************************************************************************
	 * VUE : PAGE PRINCIPALE
	 ********************************************************************************************************/
	public static function actionDefault()
	{
		if(Ctrl::$curUser->isSpaceAdmin()==false)  {self::noAccessExit();}
		$vDatas["logList"]=self::logList();
		static::displayPage("VueIndex.php",$vDatas);
	}

	/********************************************************************************************************
	 * LISTE DES LOGS DE L'ESPACE COURANT
	 ********************************************************************************************************/
	public static function logList()
	{
		$logList=Db::getTab("SELECT * FROM ap_log WHERE _idSpace=".Ctrl::$curSpace->_id." ORDER BY date desc");				//Récupère les logs de l'espace courant
		foreach($logList as $logKey=>$log){
			if(!empty($log["date"]))		{$log["date"]=substr($log["date"],0,16);}										//Label des heures
			if(!empty($log["_idUser"]))		{$log["userName"]=Ctrl::getObj("user",$log["_idUser"])->getLabel();}			//Label des users
			if(!empty($log["moduleName"]))	{$log["moduleName"]=Txt::trad(strtoupper($log["moduleName"])."_MODULE_NAME");}	//label des modules
			if(!empty($log["objectType"]))	{$log["objectType"]=Txt::trad("OBJECT".$log["objectType"]);}					//Label du type d'objet
			if(!empty($log["action"]))		{$log["action"]=Txt::trad("LOG_".$log["action"]);}								//Label des actions
			$logList[$logKey]=$log;																							//Update le log
		}
		return $logList;
	}

	/********************************************************************************************************
	 * INPUT <SELECT> POUR FILTRER LES LOGS EN FONCTION D'UN CHAMP
	 ********************************************************************************************************/
	public static function selectFilter($logList, $fieldName)
	{
		$selectValues=[];																			//Liste des valeurs du champ
		foreach($logList as $log){																	//Parcourt chaque logs de la liste
			$logVal=$log[$fieldName];																//Valeur du champs du logs courant
			if(!empty($logVal) && !in_array($logVal,$selectValues))  {$selectValues[]=$logVal;}		//Incrémente si besoin la liste des valeurs
		}
		$selectOptions='<option value="">'.Txt::trad("LOG_".$fieldName).'</option>';				//Init les options du <select>
		foreach($selectValues as $value){															//Ajoute chaque option
			$selectOptions.='<option value="'.$value.'">'.$value.'</option>';
		}
		$selectTooltip=Txt::tooltip(Txt::trad("LOG_filterBy").' '.Txt::trad("LOG_".$fieldName));	//Tooltip du <select>
		return '<select '.$selectTooltip.'>'.$selectOptions.'</select>';							//Renvoie le filtre <select>
	}

	/********************************************************************************************************
	 * ACTION : TELECHARGE LES LOGS AU FORMAT CSV
	 ********************************************************************************************************/
	public static function actionLogsDownload()
	{
		if(Ctrl::$curUser->isSpaceAdmin()==false)  {self::noAccessExit();}
		$csv=null;																							//Init le csv
		foreach(static::$logFields as $fieldName)	{$csv.='"'.Txt::trad("LOG_".$fieldName).'";';}			//Entete : champs des logs
		$csv.="\n";																							//Retour à la ligne
		foreach(self::logList() as $tmpLog){																//Ajoute chaque logs
			foreach(static::$logFields as $fieldName)	{$csv.='"'.Txt::clean($tmpLog[$fieldName]).'";';}	//Ajoute chaque champ du log
			$csv.="\n";																						//Retour à la ligne
		}
		File::download('LOGS - '.Ctrl::$agora->name.' - '.Ctrl::$curSpace->getLabel().'.csv', false, $csv);	//Download le CSV
	}
}