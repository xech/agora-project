<?php
/**
* This file is part of the Agora-Project Software package
*
* @copyleft Agora-Project <https://www.agora-project.net>
* @license GNU General Public License (GPL-2.0)
*/


/*
 * CONTROLEUR DU MODULE "TASK"
 */
class CtrlTask extends Ctrl
{
	const moduleName="task";
	public static $folderObjType="taskFolder";
	public static $moduleOptions=["adminRootAddContent","adminAddStatus"];
	public static $MdlObjects=["MdlTask","MdlTaskFolder"];

	/********************************************************************************************************
	 * VUE : PAGE PRINCIPALE
	 ********************************************************************************************************/
	public static function actionDefault()
	{
		////	LISTE DES TÂCHES
		$vDatas["tasksList"]=Db::getObjTab("task", "SELECT * FROM ap_task WHERE ".MdlTask::sqlDisplay(self::$curContainer).MdlTaskStatus::sqlCategoryFilter().MdlTask::sqlSort());
		////	TIMELINE/GANTT : LISTE DES TASKS
		$timelineBegin=$timelineEnd=null;
		$vDatas["timelineTasks"]=$vDatas["timelineDays"]=[];
		//Si ya des taches qui sont sur une période : détermine la période de la timeline
		foreach($vDatas["tasksList"] as $tmpTask){
			if(!empty($tmpTask->dateBegin) && !empty($tmpTask->dateEnd)){
				//Prépare le début/fin de la timeline
				if(empty($timelineBegin) || $tmpTask->timeBegin < $timelineBegin)	{$timelineBegin=strtotime(date("Y-m-d 00:00",$tmpTask->timeBegin));}
				if(empty($timelineEnd)   || $tmpTask->timeEnd > $timelineEnd)		{$timelineEnd  =strtotime(date("Y-m-d 23:59",$tmpTask->timeEnd));}
				//Prépare la Tache de la timeline
				$tmpTask->timeBegin=strtotime(date("Y-m-d 00:00",$tmpTask->timeBegin));
				$tmpTask->timeEnd=strtotime(date("Y-m-d 23:59",$tmpTask->timeEnd));
				$tmpTask->timelineColspan=ceil(($tmpTask->timeEnd-$tmpTask->timeBegin)/86400);
				$vDatas["timelineTasks"][]=$tmpTask;
			}
		}
		////	TIMELINE / GANTT
		if(!empty($timelineBegin)){
			//Tri les tasks de la timeline par "dateBegin"
			usort($vDatas["timelineTasks"],function($objA,$objB){
				return ($objA->timeBegin-$objB->timeBegin);
			});
			//Timeline sur 40 jours minimum
			$timelineDuration=86400 * 40;
			if(($timelineEnd-$timelineBegin) < $timelineDuration)   {$timelineEnd=$timelineBegin+$timelineDuration;}
			//Mois et Jours du header de la timeline
			$prevDayMonth=null;
			foreach(Tool::periodDays($timelineBegin,$timelineEnd) as $dateDay=>$tmpDay){									//Parcourt chaque jour de la période
				$timeDayBegin=$tmpDay['timeBegin'];																			//Debut du jour courant
				if($timeDayBegin==$timelineBegin || date("j",$timeDayBegin)==1)	{$classLeftBorder="vTimelineLeftBorder";}	//Bordure gauche de cellule (bold)  : début de tableau ou de mois
				elseif(date("N",$timeDayBegin)==1)								{$classLeftBorder="vTimelineLeftBorder2";}	//Bordure gauche de cellule (light) : début de semaine 
				else															{$classLeftBorder=null;}
				$vDatas["timelineDays"][]=[
					"curDate"=>$dateDay,
					"dayTimeBegin"=>$timeDayBegin,
					"newMonthLabel"=>$prevDayMonth!=date("m/y",$timeDayBegin)  ?  Txt::timeLabel($timeDayBegin,'MMM yyyy')  :  null,
					"newMonthColspan"=>(date("t",$timeDayBegin)-date("j",$timeDayBegin)+1),
					"classLeftBorder"=>$classLeftBorder,
					"dayLabel"=>date("j",$timeDayBegin),
					"dayLabelTitle"=>Txt::dateLabel($timeDayBegin)
				];
				$prevDayMonth=date("m/y",$timeDayBegin);
			}
		}
		////	AFFICHE LA VUE
		$vDatas["timelineBegin"]=$timelineBegin;
		$vDatas["timelineEnd"]=$timelineEnd;
		static::displayPage("VueIndex.php",$vDatas);
	}

	/********************************************************************************************************
	 * PLUGINS DU MODULE
	 ********************************************************************************************************/
	public static function getPlugins($params)
	{
		$pluginsList=MdlTaskFolder::getPluginFolders($params);
		foreach(MdlTask::getPluginObjects($params) as $tmpObj){
			$tmpObj->pluginIcon=self::moduleName."/icon.png";
			$tmpObj->pluginLabel=(!empty($tmpObj->title))  ?  $tmpObj->title  :  $tmpObj->description;
			$tmpObj->pluginTooltip=$tmpObj->containerObj()->folderPath("text");
			if(!empty($tmpObj->description))	{$tmpObj->pluginTooltip.="<hr>".Txt::reduce($tmpObj->description);}
			if(!empty($tmpObj->dateBegin))		{$tmpObj->pluginTooltip.="<hr>".Txt::trad("begin")." : ".Txt::dateLabel($tmpObj->dateBegin);}
			if(!empty($tmpObj->dateEnd))		{$tmpObj->pluginTooltip.="<hr>".Txt::trad("end")." : ".Txt::dateLabel($tmpObj->dateEnd);}
			$tmpObj->pluginJsIcon="window.top.redir('".$tmpObj->getUrl()."')";//Affiche dans son dossier
			$tmpObj->pluginJsLabel=$tmpObj->openVue();
			$pluginsList[]=$tmpObj;
		}
		return $pluginsList;
	}

	/********************************************************************************************************
	 * VUE : AFFICHAGE D'UNE TACHE
	 ********************************************************************************************************/
	public static function actionVueTask()
	{
		$curObj=Ctrl::getCurObj();
		$curObj->readControl();
		$vDatas["curObj"]=$curObj;
		static::displayPage("VueTask.php",$vDatas);
	}

	/********************************************************************************************************
	 * VUE : EDITION D'UNE TACHE
	 ********************************************************************************************************/
	public static function actionVueEditTask()
	{
		//Init
		$curObj=Ctrl::getCurObj();
		$curObj->editControl();
		////	Valide le formulaire
		if(Req::isParam("formValidate")){
			//Enregistre & recharge l'objet
			$dateBegin=Txt::formatDate(Req::param("dateBegin"), "inputDate", "dbDate");
			$dateEnd=Txt::formatDate(Req::param("dateEnd"), "inputDate", "dbDate");
			$curObj=$curObj->editRecord("title=".Db::param("title").", description=".Db::param("description").", _idStatus=".Db::param("_idStatus").", dateBegin=".Db::format($dateBegin).", dateEnd=".Db::format($dateEnd).", advancement=".Db::param("advancement").", priority=".Db::param("priority").", responsiblePersons=".Db::formatTab2txt(Req::param("responsiblePersons")));
			//Notifie par mail & Ferme la page
			$curObj->sendMailNotif();
			static::lightboxRedir();
		}
		////	Affiche la vue
		$vDatas["curObj"]=$curObj;
		static::displayPage("VueEditTask.php",$vDatas);
	}
}