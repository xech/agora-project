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
class MdlTaskFolder extends MdlObjectFolder
{
	const moduleName="task";
	const objectType="taskFolder";
	const dbTable="ap_taskFolder";
	const MdlObjectContent="MdlTask";
	
	/*******************************************************************************************
	 * SURCHARGE : DETAILS COMPLEMENTAIRES DU DOSSIER -> SYNTHESE DES "ADVANCEMENT" ET "DATEBEGINEND()" DES TACHES DU DOSSIER
	 *******************************************************************************************/
	public function folderOtherDetails()
	{
		$textReturn=null;
		$MdlObjectContent=static::MdlObjectContent;
		$folderDetails=Db::getLine("SELECT ROUND(AVG(advancement),0) as advancementAverage, MIN(dateBegin) as dateBegin, MAX(dateEnd) as dateEnd FROM ".$MdlObjectContent::dbTable." WHERE _idContainer=".$this->_id);
		//Avancement Moyen
		if(!empty($folderDetails["advancementAverage"])){
			$txtBar="<img src='app/img/task/advancement.png'> ".$folderDetails["advancementAverage"]." %";
			$txtTooltip="<img src='app/img/task/advancement.png'> ".Txt::trad("TASK_advancementAverage")." : ".$folderDetails["advancementAverage"]." %";
			$textReturn.=Tool::percentBar((int)$folderDetails["advancementAverage"], $txtBar, $txtTooltip, false, MdlTask::barWidth);
		}
		//Synthese des "dateBeginEnd()" des tâches
		if(!empty($folderDetails["dateBegin"]) && !empty($folderDetails["dateEnd"])){
			$fillPercent=((time()-strtotime($folderDetails["dateBegin"])) / (strtotime($folderDetails["dateEnd"])-strtotime($folderDetails["dateBegin"]))) * 100;
			$txtBar="<img src='app/img/task/date.png'> ".Txt::dateLabel($folderDetails["dateBegin"],"mini",$folderDetails["dateEnd"]);
			$txtTooltip=Txt::dateLabel($folderDetails["dateBegin"],"normal",$folderDetails["dateEnd"]);
			$textReturn.=Tool::percentBar($fillPercent, $txtBar, $txtTooltip, false, MdlTask::barWidth);
		}
		return $textReturn." &nbsp; &nbsp; ";
	}
}