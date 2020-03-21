<?php
/**
* This file is part of the Agora-Project Software package.
*
* @copyright (c) Agora-Project Limited <https://www.agora-project.net>
* @license GNU General Public License, version 2 (GPL-2.0)
*/


/*
 * Modele des dossiers de taches
 */
class MdlTaskFolder extends MdlObjectFolder
{
	const moduleName="task";
	const objectType="taskFolder";
	const dbTable="ap_taskFolder";
	const hasAccessRight=true;
	const MdlObjectContent="MdlTask";
	
	/*
	 * SURCHARGE : Details complementaires du dossier -> synthese des "advancement" et "dateBeginEnd()" des taches du dossier
	 */
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
			$txtBar="<img src='app/img/task/date.png'> ".Txt::displayDate($folderDetails["dateBegin"],"mini",$folderDetails["dateEnd"]);
			$txtTooltip=Txt::displayDate($folderDetails["dateBegin"],"full",$folderDetails["dateEnd"]);
			$textReturn.=Tool::percentBar($fillPercent, $txtBar, $txtTooltip, false, MdlTask::barWidth);
		}
		return $textReturn." &nbsp; &nbsp; ";
	}
}