<?php
/**
* This file is part of the Agora-Project Software package
*
* @copyleft Agora-Project <https://www.agora-project.net>
* @license GNU General Public License (GPL-2.0)
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

	/********************************************************************************************************
	 * SURCHARGE :  DATES AU + TÔT / AU + TARD SUR LES TÂCHES DU DOSSIER
	 ********************************************************************************************************/
	public function folderDetails()
	{
		$tasks=Db::getLine("SELECT MIN(dateBegin) as dateMin,  MAX(dateEnd) as dateMax FROM ".MdlTask::dbTable." WHERE _idContainer=".$this->_id);
		if(!empty($tasks["dateMin"]) && !empty($tasks["dateMax"])){
			$barLabel="<img src='app/img/task/date.png'> ".Txt::dateLabel($tasks["dateMin"],"dateMini",$tasks["dateMax"]);
			$barTooltip=Txt::trad("TASK_folderDateBeginEnd")." :<br>".Txt::dateLabel($tasks["dateMin"],"dateFull",$tasks["dateMax"]);
			return Tool::progressBar($barLabel, $barTooltip);
		}
	}
}