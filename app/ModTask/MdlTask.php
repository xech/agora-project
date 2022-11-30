<?php
/**
* This file is part of the Agora-Project Software package.
*
* @copyright (c) Agora-Project Limited <https://www.agora-project.net>
* @license GNU General Public License, version 2 (GPL-2.0)
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
	const htmlEditorField="description";
	const isFolderContent=true;
	const isSelectable=true;
	const hasShortcut=true;
	const hasAttachedFiles=true;
	const hasNotifMail=true;
	const hasUsersComment=true;
	const hasUsersLike=true;
	const barWidth="150px";
	public static $displayModes=["block","line"];
	public static $requiredFields=["title"];
	public static $searchFields=["title","description"];
	public static $sortFields=["dateCrea@@desc","dateCrea@@asc","dateModif@@desc","dateModif@@asc","_idUser@@asc","_idUser@@desc","title@@asc","title@@desc","description@@asc","description@@desc","priority@@asc","priority@@desc","advancement@@asc","advancement@@desc","dateBegin@@asc","dateBegin@@desc","dateEnd@@asc","dateEnd@@desc"];

	/*******************************************************************************************
	 * ICONE & LABEL "PRIORITY"
	 *******************************************************************************************/
	public function priority()
	{
		if(!empty($this->priority))
			{return "<img src=\"app/img/task/priority".$this->priority.".png\" class='cursorHelp' title=\"".Txt::trad("TASK_priority")." ".Txt::trad("TASK_priority".$this->priority)."\">";}
	}

	/*******************************************************************************************
	 * TACHE EN RETARD : DATE DE FIN PASSÉE ET TACHE INACHEVÉE (ADVANCEMENT < 100%)
	 *******************************************************************************************/
	public function isDelayed($displayLabel=false)
	{
		if($this->_isDelayed===null){
			$this->_isDelayed=(!empty($this->advancement) && !empty($this->dateEnd) && strtotime($this->dateEnd)<time() && (int)$this->advancement<100);
		}
		if($displayLabel==true && $this->_isDelayed==true)	{return Txt::trad("TASK_advancementLate")." <img src='app/img/important.png' style='height:20px'>";}
		else												{return $this->_isDelayed;}
	}

	/*******************************************************************************************
	 * POURCENTAGE D'AVENCEMENT EN %
	 *******************************************************************************************/
	public function fillPercent()
	{
		if(!empty($this->dateEnd) && $this->dateBegin!=$this->dateEnd){
			$timeBegin=strtotime($this->dateBegin);
			$timeEnd=strtotime($this->dateEnd);
			return floor(100 * ((time()-$timeBegin) / ($timeEnd-$timeBegin)));
		}
	}

	/*******************************************************************************************
	 * "PERCENTBAR()" : ICONE & LABEL "DATEBEGIN" & "DATEEND"
	 *******************************************************************************************/
	public function dateBeginEnd($percentBar=null)
	{
		if(!empty($this->dateBegin) || !empty($this->dateEnd))
		{
			//Affichage : Icone + tooltip / Barre détaillée
			if($percentBar==null && MdlTask::getDisplayMode()=="block")	{return "<img src='app/img/task/date.png' class='cursorHelp' title=\"".Txt::dateLabel($this->dateBegin,"normal",$this->dateEnd)."\">";}
			else{
				$txtBar="<img src='app/img/task/date.png'> ".Txt::dateLabel($this->dateBegin,"normal",$this->dateEnd);
				$txtTooltip=Txt::dateLabel($this->dateBegin,"normal",$this->dateEnd)." <br>".$this->isDelayed(true);
				return Tool::percentBar($this->fillPercent(), $txtBar, $txtTooltip, $this->isDelayed(), static::barWidth);
			}
		}
	}

	/*******************************************************************************************
	 * "PERCENTBAR()" : ICONE & LABEL "ADVANCEMENT" (ICONES / BARRE)
	 *******************************************************************************************/
	public function advancement($percentBar=null)
	{
		if(!empty($this->advancement)){
			$advancementIcon="<img src='app/img/task/advancement".($this->isDelayed()?"Delayed":null).".png'>";
			$txtTooltip=Txt::trad("TASK_advancement")." : ".$this->advancement." %"." <br>".$this->isDelayed(true);
			if($percentBar==null && MdlTask::getDisplayMode()=="block")	{return "<span class='cursorHelp' title=\"".Txt::tooltip($txtTooltip)."\">".$advancementIcon."</span>";}
			else														{return Tool::percentBar($this->advancement, $advancementIcon." ".$this->advancement."%", $txtTooltip, $this->isDelayed(), static::barWidth);}
		}
	}

	/*******************************************************************************************
	 * "PERCENTBAR()" : ICONE & LABEL "RESPONSIBLEPERSONS"
	 *******************************************************************************************/
	public function responsiblePersons($percentBar=null)
	{
		if(!empty($this->responsiblePersons))
		{
			//Liste des responsables
			$persons=$personsFirstname=null;
			foreach(Txt::txt2tab($this->responsiblePersons) as $userId){
				$persons.=Ctrl::getObj("user",$userId)->getLabel().", ";
				$personsFirstname.=Ctrl::getObj("user",$userId)->getLabel("firstName").", ";
			}
			//Affichage icone / barre
			$userIcon="<img src='app/img/user/icon.png'>";
			$txtTooltip=Txt::trad("TASK_responsiblePersons")." :<br>".trim($persons,", ");
			if($percentBar==null && MdlTask::getDisplayMode()=="block")	{return "<span class='cursorHelp' title=\"".Txt::tooltip($txtTooltip)."\">".$userIcon."</span>";}
			else{
				$txtBar=substr(Txt::trad("TASK_responsiblePersons"),0,4)." : ".trim($personsFirstname,", ");
				$txtBar=$userIcon." ".Txt::reduce($txtBar,80);
				return Tool::percentBar(0, $txtBar, $txtTooltip, false, "220px");
			}
		}
	}

	/*******************************************************************************************
	 * "PERCENTBAR()" DE LA "TIMELINE"
	 *******************************************************************************************/
	public function timelineBeginEnd()
	{
		if(!empty($this->dateBegin) || !empty($this->dateEnd)){
			$txtBar=null;
			$txtTooltip=$this->title."<br>".Txt::dateLabel($this->dateBegin,"normal",$this->dateEnd);
			if(!empty($this->advancement)){
				$txtBar.="<img src='app/img/task/advancement".($this->isDelayed()?"Delayed":null).".png'> ".$this->advancement."%";
				$txtTooltip.="<br><img src='app/img/task/advancement".($this->isDelayed()?"Delayed":null).".png'> ".Txt::trad("TASK_advancement")." : ".$this->advancement." % <br>".$this->isDelayed(true);
			}
			return "<a href=\"javascript:lightboxOpen('".$this->getUrl("vue")."')\">".Tool::percentBar($this->fillPercent(), $txtBar, $txtTooltip, $this->isDelayed())."</a>";
		}
	}
}
