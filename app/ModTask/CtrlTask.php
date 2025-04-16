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

	/*******************************************************************************************
	 * VUE : PAGE PRINCIPALE
	 *******************************************************************************************/
	public static function actionDefault()
	{
		////	LISTE DES TÂCHES
		$vDatas["tasksList"]=Db::getObjTab("task", "SELECT * FROM ap_task WHERE ".MdlTask::sqlDisplay(self::$curContainer).MdlTaskStatus::sqlCategoryFilter().MdlTask::sqlSort());
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
		//Timeline / Gantt (si besoin)
		if(!empty($timelineBegin))
		{
			//Tri les tasks de la timeline par "dateBegin"
			usort($vDatas["timelineTasks"],function($objA,$objB){
				return (strtotime($objA->dateBegin)-strtotime($objB->dateBegin));
			});
			//Timeline sur 80 jours minimum
			$timelineSeconds=86400 * 80;
			if(($timelineEnd-$timelineBegin) < $timelineSeconds)   {$timelineEnd=$timelineBegin+$timelineSeconds;}
			//Mois et Jours du header de la timeline
			$tmpMonth=null;
			for($dayTimeBegin=$timelineBegin; $dayTimeBegin<=$timelineEnd; $dayTimeBegin+=86400)
			{
				$newMonth=$tmpMonth!=date("m",$dayTimeBegin);
				if($dayTimeBegin==$timelineBegin || date("j",$dayTimeBegin)==1)	{$vTimelineLeftBorder="vTimelineLeftBorder";}	//début de tableau / début de mois : bordure de gauche pour la cellule du jour
				elseif(date("N",$dayTimeBegin)==1)								{$vTimelineLeftBorder="vTimelineLeftBorder2";}	//début de semaine : bordure de gauche plus fine
				else															{$vTimelineLeftBorder=null;}
				$vDatas["timelineDays"][]=array(
					"curDate"=>date('Y-m-d',$dayTimeBegin),
					"dayTimeBegin"=>$dayTimeBegin,
					"newMonthLabel"=>$newMonth==true ? date('y/m',$dayTimeBegin) : null,
					"newMonthColspan"=>(date("t",$dayTimeBegin)-date("j",$dayTimeBegin)+1),
					"vTimelineLeftBorder"=>$vTimelineLeftBorder,
					"vTimelineToday"=>date('Y-m-d',$dayTimeBegin)==date('Y-m-d')  ?  "vTimelineToday"  :  null,//Label d'aujourd'hui
					"dayLabel"=>date("j",$dayTimeBegin),
					"dayLabelTitle"=>Txt::dateLabel($dayTimeBegin)
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

	/*******************************************************************************************
	 * VUE : AFFICHAGE D'UNE TACHE
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
			$dateBegin=Txt::formatDate(Req::param("dateBegin"), "inputDate", "dbDate");
			$dateEnd=Txt::formatDate(Req::param("dateEnd"), "inputDate", "dbDate");
			$curObj=$curObj->createUpdate("title=".Db::param("title").", description=".Db::param("description").", _idStatus=".Db::param("_idStatus").", dateBegin=".Db::format($dateBegin).", dateEnd=".Db::format($dateEnd).", advancement=".Db::param("advancement").", priority=".Db::param("priority").", responsiblePersons=".Db::formatTab2txt(Req::param("responsiblePersons")));
			//Notifie par mail & Ferme la page
			$curObj->sendMailNotif();
			static::lightboxRedir();
		}
		////	Affiche la vue
		$vDatas["curObj"]=$curObj;
		static::displayPage("VueTaskEdit.php",$vDatas);
	}
}