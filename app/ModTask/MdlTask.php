<?php
/**
* This file is part of the Agora-Project Software package
*
* @copyleft Agora-Project <https://www.agora-project.net>
* @license GNU General Public License (GPL-2.0)
*/


/*
 * MODELE DES TACHES
 */
class MdlTask extends MdlObject
{
	const moduleName="task";
	const objectType="task";
	const dbTable="ap_task";
	const MdlObjectContainer="MdlTaskFolder";
	const MdlCategory="MdlTaskStatus";
	const descriptionEditor=true;
	const isFolderContent=true;
	const isSelectable=true;
	const hasShortcut=true;
	const hasAttachedFiles=true;
	const hasNotifMail=true;
	const hasUsersComment=true;
	const hasUsersLike=true;
	public static $displayModes=["line","block"];
	public static $requiredFields=["title"];
	public static $searchFields=["title","description"];
	public static $sortFields=["dateCrea@@desc","dateCrea@@asc","dateModif@@desc","dateModif@@asc","_idUser@@asc","_idUser@@desc","title@@asc","title@@desc","description@@asc","description@@desc","priority@@asc","priority@@desc","advancement@@asc","advancement@@desc","dateBegin@@asc","dateBegin@@desc","dateEnd@@asc","dateEnd@@desc"];


	/********************************************************************************************************
	 * SURCHARGE : CONSTRUCTEUR
	*******************************************************************************************/
	public function __construct($objIdOrValues=null)
	{
		parent::__construct($objIdOrValues);
		//Timestamp du dateBegin / dateEnd
		if($this->dateBegin && $this->dateEnd){
			$this->timeBegin=strtotime($this->dateBegin);
			$this->timeEnd=strtotime($this->dateEnd);
		}
		$this->isFinished=($this->timeEnd && $this->timeEnd < time());									//Tache terminée
		$this->isDelayed =($this->isFinished==true && $this->advancement && $this->advancement < 100);	//Tache en retard (passée et advancement < 100%)
	}

	/********************************************************************************************************
	 * COULEUR & LABEL "PRIORITY"
	 ********************************************************************************************************/
	public function priorityLabel()
	{
		if(!empty($this->priority))
			{return '<span class="priorityLabel"><img src="app/img/task/priority'.$this->priority.'.png">&nbsp; '.Txt::trad("TASK_priority").' '.Txt::trad("TASK_priority".$this->priority).'</span>';}
	}

	/********************************************************************************************************
	 * PROGRESS-BAR :  PERSONNES ASSIGNÉES
	 ********************************************************************************************************/
	public function responsiblePersons($labelFull=false)
	{
		if(!empty($this->responsiblePersons)){
			//// Liste des users responsables
			$usersTooltip=$usersLabel=null;
			foreach(Txt::txt2tab($this->responsiblePersons) as $userId){
				$tmpUser=Ctrl::getObj("user",$userId);
				$usersTooltip.=$tmpUser->getLabel().', ';				//Prénom et Nom
				$usersLabel  .=$tmpUser->getLabel("firstName").', ';	//Prenom ou Nom uniquement
			}
			//// Label full/reduced des users
			$usersTooltip=trim($usersTooltip,', ');
			$usersLabel=trim($usersLabel,', ');
			$usersLabel=($labelFull==true)  ?  $usersTooltip  :  Txt::reduce($usersLabel,35);
			//// Tooltip / Label
			$barTooltip='<img src="app/img/task/responsiblePersons.png"> '.Txt::trad("TASK_assignedTo").' &nbsp;:&nbsp; '.$usersTooltip;
			$barLabel='<img src="app/img/task/responsiblePersons.png"> <span class="progressBarLabel">'.Txt::trad("TASK_assignedTo").' '.$usersLabel.'</span>';
			//// Retourne la "progressBar"
			return Tool::progressBar($barLabel, $barTooltip);
		}
	}

	/********************************************************************************************************
	 * PROGRESS-BAR DE DEBUT-FIN
	 ********************************************************************************************************/
	public function progressBeginEnd($barLabelFull=false)
	{
		if($this->dateBegin || $this->dateEnd){
			//// Label
			$barLabel=($barLabelFull==true)  ?  Txt::dateLabel($this->dateBegin,"dateFull",$this->dateEnd)  :  Txt::dateLabel($this->dateBegin,"dateMini",$this->dateEnd);
			$barLabel='<img src="app/img/task/date.png"> <span class="progressBarLabel">'.$barLabel.'</span>';
			//// Tooltip
			$barTooltip=$this->title;
			if($this->dateBegin && $this->dateEnd)	{$barTooltip.='<hr>'.Txt::trad("beginEnd").' : '.Txt::dateLabel($this->dateBegin,"dateFull",$this->dateEnd);}
			elseif($this->dateBegin)				{$barTooltip.='<hr>'.Txt::trad("begin").' : '.Txt::dateLabel($this->dateBegin,"dateFull");}
			elseif($this->dateEnd)					{$barTooltip.='<hr>'.Txt::trad("end").' : '.Txt::dateLabel($this->dateEnd,"dateFull");}
			if($this->advancement)					{$barTooltip.='<hr>'.$this->advancementLabel();}
			//// Pourcentage de progression debut/fin
			$barPercent=0;
			if($this->timeBegin && $this->timeEnd){
				if($this->isFinished==true)					{$barPercent=100;}
				elseif($this->timeBegin < $this->timeEnd)	{$barPercent=floor(100 * ((time()-$this->timeBegin) / ($this->timeEnd-$this->timeBegin)));}
			}
			//// Retourne la "progressBar"
			return Tool::progressBar($barLabel, $barTooltip, $barPercent, $this->isDelayed);
		}
	}

	/********************************************************************************************************
	 * PROGRESS-BAR DU % D'AVANCEMENT
	 ********************************************************************************************************/
	public function progressAdvancement($barLabelFull=false)
	{
		if($this->advancement){
			$barLabel=($barLabelFull==true)  ?  Txt::trad("TASK_advancement").' : '  :  null;
			$barLabel  ='<img src="app/img/task/advancement.png"> <span class="progressBarLabel">'.$barLabel.$this->advancement.' %</span>';
			return Tool::progressBar($barLabel, $this->advancementLabel(), $this->advancement, $this->isDelayed);
		}
	}

	/********************************************************************************************************
	 * LABEL DU % D'AVANCEMENT
	 ********************************************************************************************************/
	public function advancementLabel()
	{
		if($this->isDelayed==true)  {return '<span class="progressBarDelayed">'.Txt::trad("TASK_advancementDelayed").' : '.$this->advancement.' %</span>';}
		else						{return Txt::trad("TASK_advancement").' '.$this->advancement.' %';}
	}
}