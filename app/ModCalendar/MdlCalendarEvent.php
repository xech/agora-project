<?php
/**
* This file is part of the Agora-Project Software package
*
* @copyleft Agora-Project <https://www.agora-project.net>
* @license GNU General Public License (GPL-2.0)
*/


/*
 * MODELE DES EVENEMENTS
 */
class MdlCalendarEvent extends MdlObject
{
	const moduleName="calendar";
	const objectType="calendarEvent";
	const dbTable="ap_calendarEvent";
	const MdlObjectContainer="MdlCalendar";
	const MdlCategory="MdlCalendarCategory";
	const descriptionEditor=true;
	const hasShortcut=true;
	const hasAttachedFiles=true;
	const hasNotifMail=true;
	public static $requiredFields=["title","dateBegin","timeBegin","dateEnd","timeEnd"];
	public static $searchFields=["title","description"];
	private $_confirmedCalendars=null;
	private $_propositionCalendars=null;
	private $_mainCalendarObj=null;

	/*******************************************************************************************
	 * SURCHARGE : CONSTRUCTEUR
	*******************************************************************************************/
	function __construct($objIdOrValues=null)
	{
		parent::__construct($objIdOrValues);
		//Timestamp du dateBegin / dateEnd
		if($this->dateBegin && $this->dateEnd){
			$this->timeBegin=strtotime($this->dateBegin);
			$this->timeEnd=strtotime($this->dateEnd);
		}
		//Couleur du background de l'evt, en fonction de la categorie (gris par défaut)
		$this->eventColor=($this->_idCat)  ?  $this->categoryObj()->color  :  "#555";
		//Visibilité par défaut
		if(empty($this->contentVisible))  {$this->contentVisible="public";}
		//Masque le title/description si besoin
		if($this->accessRight()<1){
			$this->title="<i>".Txt::trad("CALENDAR_evtPrivate")."</i>";
			$this->description=null;
		}
	}

	/*******************************************************************************************
	 * SURCHARGE : DROIT D'ACCÈS À UN ÉVÉNEMENT
	 * Ajoute le accessRight "0.5" qui permet juste de voir la plage horaire de l'evenement
	 *******************************************************************************************/
	public function accessRight()
	{
		//Init le cache
		if($this->_accessRight===null)
		{
			////	DROIT D'ACCES TOTAL (auteur de l'evt/admin)
			$this->_accessRight=parent::accessRight();
			////	SINON DROIT EN FONCTION DES AGENDAS AUQUELS L'ÉVÉNEMENT EST AFFECTÉ
			if($this->_accessRight<3)
			{
				//Récupère le droit maximum en fonction des affectations aux agendas
				$tmpMaxRight=0;
				$allCalendarsFullAccess=true;
				foreach($this->affectedCalendars() as $objCalendar){
					if($objCalendar->accessRight()>$tmpMaxRight)	{$tmpMaxRight=$objCalendar->accessRight();}	//Droit de l'agenda > droit max temporaire
					if($objCalendar->editContentRight()==false)		{$allCalendarsFullAccess=false;}			//L'agenda n'est pas accessible en écriture
				}
				//Attribut le droit d'accès final
				$tmpAccessRight=0;
				if($allCalendarsFullAccess==true)									{$tmpAccessRight=3;}	//Que des agendas accessibles en écriture
				elseif($tmpMaxRight>=2)												{$tmpAccessRight=2;}	//Un agenda (ou+) accessible en écriture
				elseif($tmpMaxRight>=1 && $this->contentVisible=="public")			{$tmpAccessRight=1;}	//Un agenda (ou+) accessible en lecture ou ecriture limité
				elseif($tmpMaxRight>=1 && $this->contentVisible=="public_cache")	{$tmpAccessRight=0.5;}	//Idem mais "public_cache" : lecture plage horaire uniquement!
				//Surcharge le droit d'accès?
				if($tmpAccessRight > $this->_accessRight)	{$this->_accessRight=$tmpAccessRight;}
			}
		}
		//Retourne le résultat en float et "cast" aussi les integer (cf. droit "0.5" de l'option "public_cache")
		return (float)$this->_accessRight;
	 }

	/*******************************************************************************************
	 * SURCHARGE : DROIT DE SUPPRIMER UN EVT ("fullRight" UNIQUEMENT)  OU  DÉSAFFECTER UN EVT D'UN AGENDA (cf. "CtrlObject::actionDelete()")
	 *******************************************************************************************/
	public function deleteRight()
	{
		return ($this->fullRight()  ||  (Req::isParam("_idCalDeleteOn") && $this->deleteAffectationRight(Req::param("_idCalDeleteOn"))));
	}

	/*******************************************************************************************
	 * DROIT DE DÉSAFFECTER L'ÉVÉNEMENT D'UN AGENDA SPÉCIFIQUE ("fullRight" POUR LES RÉINIT D'AFFECTATION LORS D'UNE MODIF D'EVT)
	 *******************************************************************************************/
	public function deleteAffectationRight($_idCal)
	{
		return ($this->fullRight()  ||  Ctrl::getObj("calendar",$_idCal)->editContentRight());
	}

	/*******************************************************************************************
	 * SURCHARGE : SUPPRESSION D'EVENEMENT
	 *******************************************************************************************/
	public function delete()
	{
		//// Supprime uniquement sur un agenda spécifique
		if(Req::isParam("_idCalDeleteOn") && $this->deleteAffectationRight(Req::param("_idCalDeleteOn"))){
			$this->deleteAffectation(Req::param("_idCalDeleteOn"));
		}
		//// Supprime uniquement à une date spécifique (evt périodique)
		elseif($this->fullRight() && Req::isParam("periodDateExceptionsAdd")){
			$periodDateExceptions=Txt::txt2tab($this->periodDateExceptions);
			$periodDateExceptions[]=Req::param("periodDateExceptionsAdd");
			Db::query("UPDATE ap_calendarEvent SET periodDateExceptions=".Db::format(Txt::tab2txt($periodDateExceptions))." WHERE _id=".$this->_id);
		}
		//// Suppression l'evt (tous les agendas et les occurrences)
		elseif($this->fullRight()){
			Db::query("DELETE FROM ap_calendarEventAffectation WHERE _idEvt=".$this->_id);
		}
		//// Quelquesoit le mode de suppression : on supprime l'evt s'il n'est affecté à aucun agenda
		if(Db::getVal("SELECT count(*) FROM ap_calendarEventAffectation WHERE _idEvt=".$this->_id)==0)  {parent::delete();}
	}

	/*******************************************************************************************
	 * SUPPRIME UNE AFFECTATION À UN AGENDA
	 *******************************************************************************************/
	public function deleteAffectation($_idCal, $reinitAffectations=false)
	{
		//Vérif le droit de supprimer l'affectation
		if($this->deleteAffectationRight($_idCal)){
			//Supprime l'affectation  &&  Supprime l'evt s'il n'est affecté à aucun agenda et qu'on ne modifie pas l'evt (cf. $reinitAffectations via "actionCalendarEventEdit()"). Ne pas utiliser de "$this->delete()" (sinon ça boucle)
			Db::query("DELETE FROM ap_calendarEventAffectation WHERE _idEvt=".$this->_id." AND _idCal=".(int)$_idCal);
			if($reinitAffectations==false && Db::getVal("SELECT count(*) FROM ap_calendarEventAffectation WHERE _idEvt=".$this->_id)==0)  {parent::delete();}
		}
	}

	/***********************************************************************************************************************
	 * SURCHARGE : RECUPÈRE L'AGENDA PRINCIPAL DE L'ÉVÉNEMENT  (agenda perso  ||  agenda avec le droit d'accès le + élevé)
	 ***********************************************************************************************************************/
	public function containerObj()
	{
		if($this->_mainCalendarObj===null){
			$accessRightMax=0;																														//Init le droit d'accès le + élevé
			foreach($this->affectedCalendars() as $tmpCal){																							//Parcours la liste des agendas où est affecté l'événement
				if($tmpCal->isPersonalCalendar())					{$this->_mainCalendarObj=$tmpCal;	break;}										//Renvoie l'agenda perso && stop la boucle
				elseif($accessRightMax < $tmpCal->accessRight())	{$this->_mainCalendarObj=$tmpCal;	$accessRightMax=$tmpCal->accessRight();}	//Sinon récupère l'agenda avec le droit d'accès le + élevé
			}
		}
		return $this->_mainCalendarObj;
	}

	/*******************************************************************************************
	 * SURCHARGE : URL D'ACCÈS
	 *******************************************************************************************/
	public function getUrl($display=null)
	{
		//Url par défaut (en fonction de $display)
		if(!empty($display))  {return parent::getUrl($display);}
		//Surcharge : Affiche l'evt à la bonne date et si besoin dans l'agenda principal
		else{
			//Url du module à la bonne date 
			$url="?ctrl=calendar&curTime=".$this->timeBegin;
			//Spécifie si besoin l'agenda principal (affichage de "plugin", url accès direct.. mais inutile après un delete d'evt, sinon on perd la liste des agendas en cours d'affichage)
			if(Req::$curAction!="delete" && $this->containerObj())  {$url.="&displayedCalendars[]=".$this->containerObj()->_id;}
			return $url;
		}
	}

	/********************************************************************************************
	 * PROPRIETES DES L'EVT (cf .vEvtBlock)
	 ********************************************************************************************/
	public function attributes($return, $dayTimeBegin, $dayTimeEnd)
	{
		//// Attributs de l'evt
		$attrList=[
			'eventColor'	=>$this->eventColor,
			'timeBegin'		=>$this->timeBegin,
			'timeEnd'		=>$this->timeEnd,
			'dayTimeBegin'	=>$dayTimeBegin,
			'dayTimeEnd'	=>$dayTimeEnd,
			'dayYmd'		=>date('Y-m-d',$dayTimeBegin),					//Date à laquelle l'evt est affiché
			'pastEvent'		=>($this->timeEnd < time() ? 'true' : 'false'),	//Evt dans le passé ?
			'isDraggable'	=>($this->fullRight() && empty($this->periodType) && date('Y-m-d',$this->timeBegin)==date('Y-m-d',$this->timeEnd) ? 'true' : 'false'),//Evt "Draggable" : sauf si périodique ou plusieurs jour
		];
		//// Time depuis le début du jour && Durée de l'evt sur la journée
		$evtDayBefore=($this->timeBegin < $dayTimeBegin);																	//Evt commence avant le jour courant ?
		$evtDayAfter =($this->timeEnd > $dayTimeEnd);																		//Evt termine après le jour courant ?
		$attrList['timeSinceDayBegin']=($evtDayBefore==false)  ?  ($this->timeBegin-$dayTimeBegin)  :  0;
		if($evtDayBefore==true && $evtDayAfter==true)	{$attrList['timeDuration']=86400;}									//Affiche toute la journée
		elseif($evtDayBefore==true)						{$attrList['timeDuration']=($this->timeEnd - $dayTimeBegin);}		//Affiche l'evt à partir de 0h00
		elseif($evtDayAfter==true)						{$attrList['timeDuration']=($dayTimeEnd - $this->timeBegin);}		//Affiche l'evt jusqu'à 23h59
		else											{$attrList['timeDuration']=($this->timeEnd - $this->timeBegin);}	//Affichage normal
		//// Retourne un tableau (cf. "actionEvtChangeTime()")
		if($return=="array")  {return $attrList;}
		//// Retourne un "string" (Exple: <div data-eventColor="#500">)
		else{
			$attrString='';
			foreach($attrList as $key=>$value)  {$attrString.=' data-'.$key.'="'.$value.'" ';}
			return $attrString;
		}
	}

	/*******************************************************************************************
	 * AGENDAS (OBJETS) OÙ L'EVENEMENT EST AFFECTÉ
	 * $confirmed = true / false / "all" pour récupérer toutes les affectations
	 *******************************************************************************************/
	public function affectedCalendars($confirmed=true)
	{
		if($this->_confirmedCalendars===null){
			$sqlAffectations="SELECT * FROM ap_calendar WHERE _id in (select _idCal as _id from ap_calendarEventAffectation T2 WHERE _idEvt=".$this->_id;
			$this->_confirmedCalendars=Db::getObjTab("calendar",$sqlAffectations." and confirmed=1)");
			$this->_propositionCalendars=Db::getObjTab("calendar", $sqlAffectations." and confirmed is null)");
		}
		if($confirmed===true)		{return $this->_confirmedCalendars;}
		elseif($confirmed===false)	{return $this->_propositionCalendars;}
		elseif($confirmed=="all")	{return array_merge($this->_confirmedCalendars,$this->_propositionCalendars);}
	}

	/*******************************************************************************************
	 * LABEL DES AGENDAS OÙ L'EVENEMENT EST AFFECTÉ + CEUX OU IL EST EN ATTENTE DE CONFIRMATION
	 *******************************************************************************************/
	public function affectedCalendarsLabel()
	{
		if(Ctrl::$curUser->isUser()){
			$calendarsConfirmed=$calendarsProposed=null;
			foreach($this->affectedCalendars(true) as $objCalendar)		{$calendarsConfirmed.=", <i>".$objCalendar->title."</i>";}
			foreach($this->affectedCalendars(false) as $objCalendar)	{$calendarsProposed.=", <i>".$objCalendar->title."</i>";}
			if(!empty($calendarsConfirmed))	{$calendarsConfirmed=Txt::trad("CALENDAR_evtAffects")." ".trim($calendarsConfirmed,",")."<br>";}
			if(!empty($calendarsProposed))	{$calendarsProposed=Txt::trad("CALENDAR_evtAffectToConfirm")." ".trim($calendarsProposed,",");}
			return $calendarsConfirmed.$calendarsProposed;
		}
	}

	/*******************************************************************************************
	 * LABEL DE LA PERIODICITE / REPETITION DE L'EVENEMENT
	 *******************************************************************************************/
	public function periodLabel()
	{
		if(!empty($this->periodType))
		{
			//// Type de périodicité
			$periodLabel=null;
			if($this->periodType=="weekDay")	{$periodLabel=Txt::trad("CALENDAR_period_weekDay");}														//"Toutes les semaines"
			elseif($this->periodType=="month")	{$periodLabel=str_replace("--DATE--", date("d",$this->timeBegin), Txt::trad("CALENDAR_period_monthBis"));}	//"Tous les mois, le 15"
			elseif($this->periodType=="year")	{$periodLabel=str_replace("--DATE--", date("d/m",$this->timeBegin), Txt::trad("CALENDAR_period_yearBis"));}	//"Tous les ans, le 15/10"
			//// Jours / Mois de la périodicité
			if(!empty($this->periodValues)){
				$periodLabel.=' : &nbsp;';
				foreach(Txt::txt2tab($this->periodValues) as $tmpKey=>$tmpVal){
					if($tmpKey>0)  {$periodLabel.=", ";}
					if($this->periodType=="weekDay")	{$periodLabel.=Txt::trad("day_".$tmpVal);}		//Exple : "lundi, mardi, etc"
					elseif($this->periodType=="month")	{$periodLabel.=Txt::trad("month_".$tmpVal);}	//Exple : "janvier, février, etc"
				}
			}
			//// Exceptions de périodicité
			if(!empty($this->periodDateExceptions)){
				$periodLabel.='<br><br><img src="app/img/calendar/periodDateExceptions.png"> '.Txt::trad("CALENDAR_periodDateExceptions").' : ';
				foreach(array_filter(Txt::txt2tab($this->periodDateExceptions)) as $tmpKey=>$tmpVal){	//"array_filter" enlève les valeurs vides
					if($tmpKey>0)  {$periodLabel.=", ";}
					$periodLabel.=ucfirst(Txt::dateLabel($tmpVal,"dateFull"));
				}
			}
			//// Fin de périodicité
			if(!empty($this->periodDateEnd)){
				$periodLabel.=' <br><br><img src="app/img/dateEnd.png"> '.Txt::trad("CALENDAR_periodDateEnd").' : '.ucfirst(Txt::dateLabel($this->periodDateEnd,"dateFull"));
			}
			//// Renvoi le résultat
			return $periodLabel;
		}
	}

	/*******************************************************************************************
	 * SURCHARGE : MENU CONTEXTUEL
	 *******************************************************************************************/
	public function contextMenu($options=null)
	{
		//// Pas de menu context en mode mobile && affichage principal "floatSmall"
		if(Req::isMobile() && $options["launcherIcon"]=="floatSmall")  {return false;}
		//// Evt dans plusieurs agendas
		if(count($this->affectedCalendars())>1){														
			$options["deleteLabel"]=Txt::trad("CALENDAR_deleteEvtCals");								//"Supprimer dans tous les agendas"	(au lieu de "supprimer")
			if(!empty($options["_idCal"]) && Ctrl::getObj("calendar",$options["_idCal"])->editRight())	//"Supprimer uniquement dans cet agenda"
				{$options["specificOptions"][]=["actionJs"=>"confirmDelete('".$this->getUrl("delete")."&_idCalDeleteOn=".$options["_idCal"]."')", "iconSrc"=>"delete.png", "label"=>Txt::trad("CALENDAR_deleteEvtCal")];}
		}
		//// Evt périodique : ajoute "Supprimer uniquement à cette date"
		if(!empty($this->periodType) && !empty($options["curDateTime"]) && $this->fullRight())			
			{$options["specificOptions"][]=["actionJs"=>"confirmDelete('".$this->getUrl("delete")."&periodDateExceptionsAdd=".date('Y-m-d',$options["curDateTime"])."')", "iconSrc"=>"delete.png", "label"=>Txt::trad("CALENDAR_deleteEvtDate")];}
		//// Label des agendas où est affecté l'evenement
		$options["specificLabels"][]=["label"=>$this->affectedCalendarsLabel()];			
		//// Renvoie le menu
		return parent::contextMenu($options);															
	}

	/*********************************************************************************************************************************************
	 * SURCHARGE : USERS AFFECTÉS À L'EVT (DONC AFFECTÉS AUX AGENDAS DE L'EVT)
	 *************************************************************************************************************************************************/
	public function affectedUserIds($onlyWriteAccess=false)
	{
		$return=[];
		foreach($this->affectedCalendars("all") as $tmpCal)  {$return=array_merge($return, $tmpCal->affectedUserIds($onlyWriteAccess));}//Récupère les users affectés à chaque agendas où se trouve l'evt
		return array_unique($return);
	}

	/*******************************************************************************************
	 * VÉRIFIE S'IL S'AGIT D'UN EVENEMENT PASSÉ (PREND EN COMPTE LA PÉRIODICITÉ)
	 *******************************************************************************************/
	public function isOldEvt($referenceTime)
	{
		return ((int)$referenceTime>0 && strtotime($this->dateEnd)<$referenceTime  &&  (empty($this->periodType) || (!empty($this->periodDateEnd) && strtotime($this->periodDateEnd)<$referenceTime)));
	}
}