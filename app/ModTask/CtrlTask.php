<?php
/**
* This file is part of the Agora-Project Software package.
*
* @copyright (c) Agora-Project Limited <https://www.agora-project.net>
* @license GNU General Public License, version 2 (GPL-2.0)
*/


/*
 * CONTROLEUR DU MODULE "TASK"
 */
class CtrlTask extends Ctrl
{
	const moduleName="task";
	public static $folderObjType="taskFolder";
	public static $moduleOptions=["adminRootAddContent"];
	public static $MdlObjects=["MdlTask","MdlTaskFolder"];

	/*******************************************************************************************
	 * VUE : PAGE PRINCIPALE
	 *******************************************************************************************/
	public static function actionDefault()
	{
		$filterPriority=Req::param("filterPriority")>=1 ? "AND priority=".Db::param("filterPriority") : null;
		$vDatas["tasksList"]=Db::getObjTab("task", "SELECT * FROM ap_task WHERE ".MdlTask::sqlDisplay(self::$curContainer)." ".$filterPriority." ".MdlTask::sqlSort());
		////	TIMELINE/GANTT
		$timelineBegin=$timelineEnd=null;
		$vDatas["timelineTasks"]=$vDatas["timelineDays"]=[];
		//Si ya des taches qui sont sur une période : détermine la période de la timeline
		foreach($vDatas["tasksList"] as $tmpTask)
		{
			//Ajoute la tache?
			if(!empty($tmpTask->dateBegin) && !empty($tmpTask->dateEnd))
			{
				//Prépare le début/fin de la timeline
				if(empty($timelineBegin) || strtotime($tmpTask->dateBegin)<$timelineBegin)	{$timelineBegin=strtotime(date("Y-m-d 00:00",strtotime($tmpTask->dateBegin)));}
				if(empty($timelineEnd) || strtotime($tmpTask->dateEnd)>$timelineEnd)		{$timelineEnd=strtotime(date("Y-m-d 23:59",strtotime($tmpTask->dateEnd)));}
				//Prépare la Tache de la timeline
				$tmpTask->timeBegin=strtotime(date("Y-m-d 00:00",strtotime($tmpTask->dateBegin)));
				$tmpTask->timeEnd=strtotime(date("Y-m-d 23:59",strtotime($tmpTask->dateEnd)));
				$tmpTask->timelineColspan=ceil(($tmpTask->timeEnd-$tmpTask->timeBegin)/86400);
				$vDatas["timelineTasks"][]=$tmpTask;
			}
		}
		//Prépare la timeline si il y en a une
		if(!empty($timelineBegin))
		{
			//Tri les tasks de la timeline par "dateBegin"
			usort($vDatas["timelineTasks"],function($objA,$objB){
				return (strtotime($objA->dateBegin)-strtotime($objB->dateBegin));
			});
			//60 jours mini pour la timeline (soit 5184000sec)
			if(($timelineEnd-$timelineBegin) < 5184000)   {$timelineEnd=$timelineBegin+5184000;}
			//Mois et Jours du header de la timeline
			$tmpMonth=null;
			for($dayTimeBegin=$timelineBegin; $dayTimeBegin<=$timelineEnd; $dayTimeBegin+=86400)
			{
				$newMonth=$tmpMonth!=date("m",$dayTimeBegin);
				$vDatas["timelineDays"][]=array(
					"curDate"=>date("Y-m-d",$dayTimeBegin),
					"timeBegin"=>$dayTimeBegin,
					"newMonthLabel"=>($newMonth==true ? ucfirst(Txt::formatime("MMMM y",$dayTimeBegin)) : null),
					"newMonthColspan"=>(date("t",$dayTimeBegin)-date("j",$dayTimeBegin)+1),
					"vTimelineLeftBorder"=>(($dayTimeBegin==$timelineBegin || date("N",$dayTimeBegin)==1 || date("j",$dayTimeBegin)==1)  ?  "vTimelineLeftBorder"  :  null),//début de timeline/de mois/de semaine : affiche les pointillés
					"vTimelineToday"=>(date("Y-m-d",$dayTimeBegin)==date("Y-m-d")  ?  "vTimelineToday"  :  null),//Label d'aujourd'hui
					"dayLabel"=>date("j",$dayTimeBegin),
					"dayLabelTitle"=>Txt::dateLabel($dayTimeBegin,"dateFull")
				);
				$tmpMonth=date("m",$dayTimeBegin);
			}
		}
		////	Affiche la vue
		$vDatas["timelineBegin"]=$timelineBegin;
		$vDatas["timelineEnd"]=$timelineEnd;
		static::displayPage("VueIndex.php",$vDatas);
	}

	/*******************************************************************************************
	 * PLUGINS DU MODULE
	 *******************************************************************************************/
	public static function getPlugins($params)
	{
		$pluginsList=MdlTaskFolder::getPluginFolders($params);
		foreach(MdlTask::getPluginObjects($params) as $tmpObj)
		{
			$tmpObj->pluginIcon=self::moduleName."/icon.png";
			$tmpObj->pluginLabel=(!empty($tmpObj->title))  ?  $tmpObj->title  :  $tmpObj->description;
			$tmpObj->pluginTooltip=$tmpObj->containerObj()->folderPath("text");
			if(!empty($tmpObj->description))	{$tmpObj->pluginTooltip.="<hr>".Txt::reduce($tmpObj->description);}
			if(!empty($tmpObj->dateBegin))		{$tmpObj->pluginTooltip.="<hr>".Txt::trad("begin")." : ".Txt::dateLabel($tmpObj->dateBegin);}
			if(!empty($tmpObj->dateEnd))		{$tmpObj->pluginTooltip.="<hr>".Txt::trad("end")." : ".Txt::dateLabel($tmpObj->dateEnd);}
			$tmpObj->pluginJsIcon="windowParent.redir('".$tmpObj->getUrl()."');";//Affiche dans son dossier
			$tmpObj->pluginJsLabel="lightboxOpen('".$tmpObj->getUrl("vue")."');";
			$pluginsList[]=$tmpObj;
		}
		return $pluginsList;
	}

	/*******************************************************************************************
	 * VUE : DÉTAILS D'UNE TACHE
	 *******************************************************************************************/
	public static function actionVueTask()
	{
		$curObj=Ctrl::getObjTarget();
		$curObj->readControl();
		$vDatas["curObj"]=$curObj;
		static::displayPage("VueTask.php",$vDatas);
	}

	/*******************************************************************************************
	 * VUE : EDITION D'UNE TACHE
	 *******************************************************************************************/
	public static function actionTaskEdit()
	{
		//Init
		$curObj=Ctrl::getObjTarget();
		$curObj->editControl();
		////	Valide le formulaire
		if(Req::isParam("formValidate")){
			//Enregistre & recharge l'objet
			$dateBegin=Txt::formatDate(Req::param("dateBegin")." ".Req::param("timeBegin"), "inputDatetime", "dbDatetime");
			$dateEnd=Txt::formatDate(Req::param("dateEnd")." ".Req::param("timeEnd"), "inputDatetime", "dbDatetime");
			$curObj=$curObj->createUpdate("title=".Db::param("title").", description=".Db::param("description").", dateBegin=".Db::format($dateBegin).", dateEnd=".Db::format($dateEnd).", advancement=".Db::param("advancement").", priority=".Db::param("priority").", responsiblePersons=".Db::formatTab2txt(Req::param("responsiblePersons")));
			//Notifie par mail & Ferme la page
			$curObj->sendMailNotif();
			static::lightboxClose();
		}
		////	Affiche la vue
		$vDatas["curObj"]=$curObj;
		static::displayPage("VueTaskEdit.php",$vDatas);
	}
}