<?php
/**
* This file is part of the Agora-Project Software package.
*
* @copyright (c) Agora-Project Limited <https://www.agora-project.net>
* @license GNU General Public License, version 2 (GPL-2.0)
*/


/*
 * MODELE DES DOSSIERS DE TACHES
 */
class MdlTaskFolder extends MdlFolder
{
	const moduleName="task";
	const objectType="taskFolder";
	const dbTable="ap_taskFolder";
	const MdlObjectContent="MdlTask";

	/*******************************************************************************************
	 * SURCHARGE :  POURCENTAGE DE PROGRESSION DANS LA TIMELINE
	 *******************************************************************************************/
	public function folderOtherDetails()
	{
		//Affiche la barre s'il ya une date au plus tôt et au plus tard sur l'ensemble des tâches du dossier
		$tasks=Db::getLine("SELECT  MIN(dateBegin) as dateBeginMin,  MAX(dateEnd) as dateEndMax  FROM  ".MdlTask::dbTable."  WHERE  _idContainer=".$this->_id);
		if(!empty($tasks["dateBeginMin"]) && !empty($tasks["dateEndMax"])){
			$barLabel="<img src='app/img/task/date.png'> ".Txt::dateLabel($tasks["dateBeginMin"],"date",$tasks["dateEndMax"]);
			$barTooltip=Txt::trad("TASK_folderDateBeginEnd")." : &nbsp; ".Txt::dateLabel($tasks["dateBeginMin"],"date",$tasks["dateEndMax"]);
			return Tool::progressBar($barLabel, $barTooltip);//Pas de $percentProgress (cf. "isDelayed()" multiple)
		}
	}
}