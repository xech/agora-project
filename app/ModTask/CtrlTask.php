<?php
/**
* This file is part of the Agora-Project Software package.
*
* @copyright (c) Agora-Project Limited <https://www.agora-project.net>
* @license GNU General Public License, version 2 (GPL-2.0)
*/


/*
 * Controleur du module "Task"
 */
class CtrlTask extends Ctrl
{
	const moduleName="task";
	public static $folderObjectType="taskFolder";
	public static $moduleOptions=["adminRootAddContent"];
	public static $MdlObjects=array("MdlTask","MdlTaskFolder");

	/*
	 * ACTION PAR DEFAUT
	 */
	public static function actionDefault()
	{
		$vDatas["foldersList"]=self::$curContainer->folders();
		$filterPriority=Req::getParam("filterPriority")>=1 ? "AND priority=".Db::formatParam("filterPriority") : null;
		$vDatas["tasksList"]=Db::getObjTab("task", "SELECT * FROM ap_task WHERE ".MdlTask::sqlDisplayedObjects(self::$curContainer)." ".$filterPriority." ".MdlTask::sqlSort());
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
					"newMonthLabel"=>($newMonth==true ? ucfirst(Txt::formatime("%B %Y",$dayTimeBegin)) : null),
					"newMonthColspan"=>(date("t",$dayTimeBegin)-date("j",$dayTimeBegin)+1),
					"vTimelineLeftBorder"=>(($dayTimeBegin==$timelineBegin || date("N",$dayTimeBegin)==1 || date("j",$dayTimeBegin)==1)  ?  "vTimelineLeftBorder"  :  null),//début de timeline/de mois/de semaine : affiche les pointillés
					"vTimelineToday"=>(date("Y-m-d",$dayTimeBegin)==date("Y-m-d")  ?  "vTimelineToday"  :  null),//Label d'aujourd'hui
					"dayLabel"=>date("j",$dayTimeBegin),
					"dayLabelTitle"=>Txt::displayDate($dayTimeBegin,"dateFull")
				);
				$tmpMonth=date("m",$dayTimeBegin);
			}
		}
		////	Affiche la vue
		$vDatas["timelineBegin"]=$timelineBegin;
		$vDatas["timelineEnd"]=$timelineEnd;
		static::displayPage("VueIndex.php",$vDatas);
	}

	/*
	 * PLUGINS
	 */
	public static function plugin($pluginParams)
	{
		$pluginsList=self::getPluginsFolders($pluginParams,"MdlTaskFolder");
		foreach(MdlTask::getPluginObjects($pluginParams) as $tmpObj)
		{
			$tmpObj->pluginModule=self::moduleName;
			$tmpObj->pluginIcon=self::moduleName."/icon.png";
			$tmpObj->pluginLabel=(!empty($tmpObj->title))  ?  $tmpObj->title  :  $tmpObj->description;
			$tmpObj->pluginTooltip=$tmpObj->containerObj()->folderPath("text")."<hr>".$tmpObj->description;
			if(!empty($tmpObj->dateBegin) || !empty($tmpObj->dateEnd)){
				if(!empty($tmpObj->dateBegin))		{$displayTime=Txt::displayDate($tmpObj->dateBegin,"full",$tmpObj->dateEnd);}
				elseif(!empty($tmpObj->dateEnd))	{$displayTime=Txt::trad("end")." : ".Txt::displayDate($tmpObj->dateEnd,"normal");}
				$tmpObj->pluginTooltip.="<br>".$displayTime;
			}			
			$tmpObj->pluginJsIcon="windowParent.redir('".$tmpObj->getUrl("container")."');";//Redir vers le dossier conteneur
			$tmpObj->pluginJsLabel="lightboxOpen('".$tmpObj->getUrl("vue")."');";
			$pluginsList[]=$tmpObj;
		}
		return $pluginsList;
	}

	/*
	 * ACTION : Vue détaillée d'une tache
	 */
	public static function actionVueTask()
	{
		$curObj=Ctrl::getTargetObj();
		$curObj->controlRead();
		$vDatas["curObj"]=$curObj;
		static::displayPage("VueTask.php",$vDatas);
	}

	/*
	 * ACTION : Edition d'une tache
	 */
	public static function actionTaskEdit()
	{
		//Init
		$curObj=Ctrl::getTargetObj();
		$curObj->controlEdit();
		////	Valide le formulaire
		if(Req::isParam("formValidate")){
			//Enregistre & recharge l'objet
			$dateBegin=Txt::formatDate(Req::getParam("dateBegin")." ".Req::getParam("timeBegin"), "inputDatetime", "dbDatetime");
			$dateEnd=Txt::formatDate(Req::getParam("dateEnd")." ".Req::getParam("timeEnd"), "inputDatetime", "dbDatetime");
			$curObj=$curObj->createUpdate("title=".Db::formatParam("title").", description=".Db::formatParam("description","editor").", dateBegin=".Db::format($dateBegin).", dateEnd=".Db::format($dateEnd).", advancement=".Db::formatParam("advancement").", priority=".Db::formatParam("priority").", responsiblePersons=".Db::formatTab2txt(Req::getParam("responsiblePersons")));
			//Notifie par mail & Ferme la page
			$curObj->sendMailNotif();
			static::lightboxClose();
		}
		////	Affiche la vue
		$vDatas["curObj"]=$curObj;
		static::displayPage("VueTaskEdit.php",$vDatas);
	}
}