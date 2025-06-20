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
	private $_isDelayed=null;
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
	 * COULEUR & LABEL "PRIORITY"
	 ********************************************************************************************************/
	public function priorityLabel()
	{
		if(!empty($this->priority))
			{return '<span class="priorityLabel"><img src="app/img/task/priority'.$this->priority.'.png">&nbsp; '.Txt::trad("TASK_priority").' '.Txt::trad("TASK_priority".$this->priority).'</span>';}
	}

	/********************************************************************************************************
	 * TACHE EN RETARD : DATE DE FIN PASSÉE ET TACHE INACHEVÉE (ADVANCEMENT < 100%)
	 ********************************************************************************************************/
	public function isDelayed($displayLabel=false)
	{
		if($this->_isDelayed===null){
			$this->_isDelayed=(!empty($this->advancement) && !empty($this->dateEnd) && strtotime($this->dateEnd)<time() && (int)$this->advancement<100);
		}
		if($displayLabel==true && $this->_isDelayed==true)	{return Txt::trad("TASK_advancementLate")." <img src='app/img/important.png'>";}
		else												{return $this->_isDelayed;}
	}

	/********************************************************************************************************
	 * ICONE-BARRE :  PERSONNES ASSIGNÉES A LA TACHE
	 ********************************************************************************************************/
	public function responsiblePersons($isVueTask=false)
	{
		if(!empty($this->responsiblePersons)){
			//// Récup chaque personnes assignées
			$barLabel=$barTooltip=Txt::trad("TASK_assignedTo")." ";
			foreach(Txt::txt2tab($this->responsiblePersons) as $userId){
				$tmpUser=Ctrl::getObj("user",$userId);
				$barTooltip.=$tmpUser->getLabel().", ";				//prenoms + noms
				$barLabel  .=$tmpUser->getLabel("firstName").", ";	//prenoms uniqument
			}
			$barLabel=trim($barLabel,", ");
			$barTooltip=trim($barTooltip,", ");
			//// Affichage "icone" ou "progressBar"
			if($isVueTask==false && static::getDisplayMode()=="block")	{return '<span class="cursorHelp" '.Txt::tooltip($barTooltip).'><img src="app/img/user/iconSmall.png"></span>';}
			else{
				if($isVueTask==false)  {$barLabel=Txt::reduce($barLabel,40);}
				return Tool::progressBar("<img src='app/img/user/iconSmall.png'> ".$barLabel, $barTooltip);
			}
		}
	}

	/********************************************************************************************************
	 * ICONE-BARRE :  ETAT D'AVANCEMENT EN %
	 ********************************************************************************************************/
	public function advancement($isVueTask=false)
	{
		if(!empty($this->advancement)){
			$advancementIcon="<img src='app/img/task/advancement".($this->isDelayed()?"Delayed":null).".png'>";
			$barTooltip=Txt::trad("TASK_advancement")." : ".$this->advancement." %"." <br>".$this->isDelayed(true);
			//// Affichage "icone" ou "progressBar"
			if($isVueTask==false && static::getDisplayMode()=="block")	{return '<span class="cursorHelp" '.Txt::tooltip($barTooltip).'>'.$advancementIcon.'</span>';}
			else														{return Tool::progressBar($advancementIcon." ".Txt::trad("TASK_advancement")." ".$this->advancement."%", $barTooltip, $this->advancement, $this->isDelayed());}
		}
	}

	/********************************************************************************************************
	 * ICONE-BARRE :  DATE DE DEBUT/FIN
	 ********************************************************************************************************/
	public function dateBeginEnd($isVueTask=false)
	{
		//// Vérif si ya une date de début  OU  une date de fin
		if(!empty($this->dateBegin) || !empty($this->dateEnd)){
			//// Date de début et/ou de fin && Tooltip
			$barLabel=Txt::dateLabel($this->dateBegin,"default",$this->dateEnd);
			if(!empty($this->dateBegin) && !empty($this->dateEnd))	{$barTooltip=Txt::trad("beginEnd")." : &nbsp; ";}
			elseif(!empty($this->dateBegin))						{$barTooltip=Txt::trad("begin")." : &nbsp; ";}
			elseif(!empty($this->dateEnd))							{$barTooltip=null;}//Txt::trad("end") récup via "dateLabel()" ci-dessus
			$barTooltip.=$barLabel."<br>".$this->isDelayed(true);
			//// Affichage "icone" ou "progressBar"
			if($isVueTask==false && static::getDisplayMode()=="block")	{return '<img src="app/img/task/date.png" class="cursorHelp" '.Txt::tooltip($barTooltip).'>';}
			else														{return Tool::progressBar('<img src="app/img/task/date.png"> '.$barLabel, $barTooltip, $this->timeProgressPercent(), $this->isDelayed());}
		}
	}

	/********************************************************************************************************
	 * BARRE DE PROGESSION GANTT :  TITRE  +  DATE DE DEBUT/FIN  +  ETAT D'AVANCEMENT
	 ********************************************************************************************************/
	public function timelineGanttBar()
	{
		//Vérif si ya une date de début  ET  une date de fin
		if(!empty($this->dateBegin) && !empty($this->dateEnd)){
			$barLabel="&nbsp;";//jamais vide
			$barTooltip=$this->title."<hr>".Txt::trad("beginEnd")." : ".Txt::dateLabel($this->dateBegin,"dateBasic",$this->dateEnd);
			//Avancement de la tâche
			if(!empty($this->advancement)){
				$advancementIcon='<img src="app/img/task/advancement'.($this->isDelayed()?'Delayed':null).'.png">';
				$barLabel.=$advancementIcon.' '.$this->advancement.'%';
				$barTooltip.='<br>'.$advancementIcon.' '.Txt::trad("TASK_advancement").' : '.$this->advancement.' % '.$this->isDelayed(true);
			}
			//Affiche la barre de progression : 100% de width en fonction de la durée de la tâche (cf. "colspan" des cellules)
			return Tool::progressBar($barLabel, $barTooltip, $this->timeProgressPercent(), $this->isDelayed());
		}
	}

	/********************************************************************************************************
	 * POURCENTAGE DE PROGRESSION DANS LE TEMPS
	 ********************************************************************************************************/
	public function timeProgressPercent()
	{
		if(!empty($this->dateBegin) && !empty($this->dateEnd)){														//Vérif si ya une date de début et de fin
			$timeBegin=strtotime($this->dateBegin);																	//Timestamp de début
			$timeEnd  =strtotime($this->dateEnd);																	//Timestamp de fin
			if($timeEnd < time())			{return 100;}															//"100%" si la date de fin est déjà passée
			elseif($timeBegin < $timeEnd)	{return floor(100 * ((time()-$timeBegin) / ($timeEnd-$timeBegin)));}	//"n%" d'avancement (ex: "30%" si la tâche se déroule sur 10 jours et qu'on est au 3eme)
		}
	}
}