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
		//Couleur du background de l'evt, en fonction de la categorie (gris par défaut)
		$this->eventColor=($this->_idCat)  ?  $this->categoryObj()->color  :  "#555";
		//Visibilité par défaut
		if(empty($this->contentVisible))  {$this->contentVisible="public";}
		//Masque le title/description si besoin
		if($this->readRight()==false || ($this->accessRight()==1 && $this->contentVisible=="public_cache")){
			$this->title="<i>".Txt::trad("CALENDAR_evtPrivate")."</i>";
			$this->description=null;
		}
	}

	/********************************************************************************************************
	 * SURCHARGE : DROIT D'ACCÈS A L'EVT
	 ********************************************************************************************************/
	public function accessRight()
	{
		if($this->_accessRight===null){
			////	ACCES TOTAL POUR L'AUTEUR ET L'ADMIN GENERAL
			if(parent::accessRight()==3)	{return 3;}
			////	DROIT EN FONCTION DES AGENDAS AUQUELS L'EVT EST AFFECTÉ
			else{
				$editCalsCpt=$readCalsCpt=0;
				foreach($this->affectedCalendars() as $objCalendar){								//Parcourt les affectations aux agendas
					if($objCalendar->editRight())		{$editCalsCpt++;}							//Droit d'éditer l'agenda
					elseif($objCalendar->readRight())	{$readCalsCpt++;}							//Droit de lecture
				}
				if(count($this->affectedCalendars())==$editCalsCpt)		{$this->_accessRight=2;}	//Droit en écriture : affecté uniquement à des agendas "writable"
				elseif(!empty($editCalsCpt) || !empty($readCalsCpt))	{$this->_accessRight=1;}	//Droit en lecture  : affecté à des agendas "writable" et/ou "readable"
				else													{$this->_accessRight=0;}	//Aucun droit
			}
		}
		//Retourne le résultat
		return (int)$this->_accessRight;
	}

	/********************************************************************************************************
	 * SURCHARGE : UN EVT DEPEND DE PLUSIEURS "CONTAINER" AGENDA => CF. "accessRight()"
	 ********************************************************************************************************/
	public function hasContainerAccessRight(){
		return false;
	}

	/********************************************************************************************************
	 * SURCHARGE : VERIF LE DROIT POUR L'USER COURANT DE CRÉER UN NOUVEL EVT
	 ********************************************************************************************************/
	public function createRight()
	{
		return ($this->_id==0);
	}

	/********************************************************************************************************
	 * SURCHARGE : SUPPRESSION / DÉSAFFECTATION D'UN AGENDA
	 ********************************************************************************************************/
	public function delete()
	{
		////	Supprime l'affectation à un agenda spécifique
		if(Req::isParam("_idCalDeleteAffectation") && $this->affectationDeleteRight(Req::param("_idCalDeleteAffectation"))){
			$this->affectationDelete(Req::param("_idCalDeleteAffectation"));
		}
		////	Supprime à une date spécifique (cf. evt répétés)
		elseif(Req::isParam("periodDateExceptionsAdd") && $this->editRight()){
			$periodDateExceptions=Txt::txt2tab($this->periodDateExceptions);
			$periodDateExceptions[]=Req::param("periodDateExceptionsAdd");
			Db::query("UPDATE ap_calendarEvent SET periodDateExceptions=".Db::format(Txt::tab2txt($periodDateExceptions))." WHERE _id=".$this->_id);
		}
		////	Suppression complete : supprime d'abord les affectations aux agendas
		elseif($this->editRight()){
			Db::query("DELETE FROM ap_calendarEventAffectation WHERE _idEvt=".$this->_id);
		}
		////	Suppression complete si l'evt n'est affecté à aucun agenda (cf. suppression d'affectation)
		if(Db::getVal("SELECT count(*) FROM ap_calendarEventAffectation WHERE _idEvt=".$this->_id)==0)
			{parent::delete();}
	}

	/***********************************************************************************************************************
	 * SURCHARGE : RECUPÈRE L'AGENDA PRINCIPAL DE L'ÉVÉNEMENT  (agenda perso || agenda avec le droit d'accès le + élevé)
	 ***********************************************************************************************************************/
	public function containerObj()
	{
		if($this->_mainCalendarObj===null){
			$accessRightMax=0;																														//Init le droit d'accès le + élevé
			foreach($this->affectedCalendars(true) as $tmpCal){																						//Parcours la liste des agendas où est affecté l'événement
				if($tmpCal->isMyPersoCalendar())					{$this->_mainCalendarObj=$tmpCal;	break;}										//Renvoie l'agenda perso && stop la boucle
				elseif($accessRightMax < $tmpCal->accessRight())	{$this->_mainCalendarObj=$tmpCal;	$accessRightMax=$tmpCal->accessRight();}	//Sinon récupère l'agenda avec le droit d'accès le + élevé
			}
		}
		return $this->_mainCalendarObj;
	}

	/********************************************************************************************************
	 * SURCHARGE : URL D'ACCÈS À L'OBJET  >  AJOUTE "timeBegin" POUR AFFICHER L'AGENDA À LA DATE DE L'EVT
	 ********************************************************************************************************/
	public function getUrl($display=null)
	{
		return empty($display)  ?  parent::getUrl()."&curTime=".$this->timeBegin  :  parent::getUrl($display);
	}

	/********************************************************************************************************
	 * SURCHARGE : MENU CONTEXTUEL
	 ********************************************************************************************************/
	public function contextMenu($options=null)
	{
		////	Options  "Supprimer l'événement" && "Enlever l'événement de cet agenda"
		if(!empty($options["_idCal"])){
			if($this->deleteRight()){
				$options["deleteLabel"]=Txt::trad("CALENDAR_evtDelete");
			}
			if($this->affectationDeleteRight($options["_idCal"]) && count($this->affectedCalendars())>=2){
				$options["specificOptions"][]=[
					"actionJs"=>"confirmRedir('".$this->getUrl("delete")."&_idCalDeleteAffectation=".$options["_idCal"]."')",
					"iconSrc"=>"calendar/deleteEvtCal.png",
					"label"=>Txt::trad("CALENDAR_evtDeleteCal")
				];
			}			
		}
		////	Option "Enlever l'événement à cette date" (cf. Evt répétés)
		if(!empty($options["evtDeleteTime"]) && !empty($this->periodType) && $this->editRight()){
			$options["specificOptions"][]=[
				"actionJs"=>"confirmRedir('".$this->getUrl("delete")."&periodDateExceptionsAdd=".date('Y-m-d',$options["evtDeleteTime"])."')",
				"iconSrc"=>"calendar/deleteEvtCal.png",
				"label"=>Txt::trad("CALENDAR_evtDeleteDate")
			];
		}
		////	Agendas où est affecté l'evenement  &&  Retourne le menu
		$options["specificLabels"][]=["label"=>$this->affectedCalendarsLabel()];
		return parent::contextMenu($options);															
	}

	/********************************************************************************************************
	 * SURCHARGE : LISTE DES USERS AFFECTÉS AUX AGENDAS OÙ SE TROUVE L'EVT
	 ********************************************************************************************************/
	public function affectedUserIds($accessWrite=false)
	{
		$affectedUserIds=[];
		foreach($this->affectedCalendars() as $tmpCal)  {$affectedUserIds=array_merge($affectedUserIds, $tmpCal->affectedUserIds($accessWrite));}
		return array_unique($affectedUserIds);
	}

	/*******************************************************************************************************
	 * DROIT DE DÉSAFFECTER UN AGENDA DE L'EVT : DROIT D'EDITER L'EVT OU LE CONTENU DE L'AGENDA
	 *******************************************************************************************************/
	public function affectationDeleteRight($_idCal)
	{
		return ($this->editRight() || Ctrl::getObj("calendar",$_idCal)->editContentRight());
	}

	/********************************************************************************************************
	 * SUPPRIME UNE AFFECTATION À UN AGENDA
	 ********************************************************************************************************/
	public function affectationDelete($_idCal, $isEvtUpdate=false)
	{
		if($this->affectationDeleteRight($_idCal)){
			Db::query("DELETE FROM ap_calendarEventAffectation WHERE _idEvt=".$this->_id." AND _idCal=".(int)$_idCal);									//Supprime l'affectation
			if($isEvtUpdate==false && Db::getVal("SELECT count(*) FROM ap_calendarEventAffectation WHERE _idEvt=".$this->_id)==0)   {parent::delete();}	//Supprime l'evt s'il est affecté à aucun agenda
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
			'isDraggable'	=>($this->editRight() && empty($this->periodType) && date('Y-m-d',$this->timeBegin)==date('Y-m-d',$this->timeEnd) ? 'true' : 'false'),//Evt "Draggable" : sauf si répété ou sur plusieurs jours
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
		//// Retourne un "string" (ex: <div data-eventColor="#500">)
		else{
			$attrString='';
			foreach($attrList as $key=>$value)  {$attrString.=' data-'.$key.'="'.$value.'" ';}
			return $attrString;
		}
	}

	/********************************************************************************************************
	 * AGENDAS OÙ L'EVT EST AFFECTÉ / PROPOSÉ
	 ********************************************************************************************************/
	public function affectedCalendars($confirmed="all")
	{
		if($this->_confirmedCalendars===null){
			$sqlAffectations="SELECT * FROM ap_calendar WHERE _id in (select _idCal as _id from ap_calendarEventAffectation T2 WHERE _idEvt=".$this->_id;
			$this->_confirmedCalendars=Db::getObjTab("calendar",$sqlAffectations." and confirmed=1)");				//Evts confirmés
			$this->_propositionCalendars=Db::getObjTab("calendar", $sqlAffectations." and confirmed IS NULL)");		//Evts proposés
		}
		if($confirmed===true)		{return $this->_confirmedCalendars;}											//Retourne les evts confirmés
		elseif($confirmed===false)	{return $this->_propositionCalendars;}											//Retourne les evts proposés
		elseif($confirmed=="all")	{return array_merge($this->_confirmedCalendars,$this->_propositionCalendars);}	//Retourne les evts confirmés + proposés
	}

	/********************************************************************************************************
	 * VERIF SI L'EVT EST AFFECTÉ A UN AGENDA (Cf "affectedCalendars()")
	 ********************************************************************************************************/
	public function isAffectedCalendar($tmpCal, $confirmed="all")
	{
		return in_array($tmpCal,$this->affectedCalendars($confirmed));
	}

	/********************************************************************************************************
	 * LABEL DES AGENDAS OÙ L'EVENEMENT EST AFFECTÉ + CEUX OU IL EST EN ATTENTE DE CONFIRMATION
	 ********************************************************************************************************/
	public function affectedCalendarsLabel()
	{
		if(Ctrl::$curUser->isUser()){
			$calendarsConfirmed=$calendarsProposed=null;
			foreach($this->affectedCalendars(true) as $objCalendar)		{$calendarsConfirmed.=", <i>".$objCalendar->title."</i>";}
			foreach($this->affectedCalendars(false) as $objCalendar)	{$calendarsProposed.=", <i>".$objCalendar->title."</i>";}
			if(!empty($calendarsConfirmed))	{$calendarsConfirmed=Txt::trad("CALENDAR_evtAffects")." ".trim($calendarsConfirmed,",");}
			if(!empty($calendarsProposed))	{$calendarsProposed="<hr>".Txt::trad("CALENDAR_evtAffectToConfirm")." ".trim($calendarsProposed,",");}
			return $calendarsConfirmed.$calendarsProposed;
		}
	}

	/********************************************************************************************************
	 * LABEL DE LA PERIODICITE / REPETITION DE L'EVENEMENT
	 ********************************************************************************************************/
	public function periodLabel()
	{
		if(!empty($this->periodType))
		{
			//// Type de périodicité
			$periodLabel=null;
			if($this->periodType=="weekDay")	{$periodLabel=Txt::trad("CALENDAR_period_weekDay");}															//"Toutes les semaines"
			elseif($this->periodType=="month")	{$periodLabel=str_replace("--DATE--", date("d",$this->timeBegin), Txt::trad("CALENDAR_period_monthDetail"));}	//"Tous les mois, le 15"
			elseif($this->periodType=="year")	{$periodLabel=str_replace("--DATE--", date("d/m",$this->timeBegin), Txt::trad("CALENDAR_period_yearDetail"));}	//"Tous les ans, le 15/10"
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
			//// Renvoie le résultat
			return $periodLabel;
		}
	}

	/********************************************************************************************************
	 * VÉRIFIE S'IL S'AGIT D'UN EVENEMENT PASSÉ (sans périodicité ou fin de périodicité passée)
	 ********************************************************************************************************/
	public function isPastEvent($timeMax)
	{
		return (!empty($timeMax)  &&  strtotime($this->dateEnd) < $timeMax  &&  (empty($this->periodType) || (!empty($this->periodDateEnd) && strtotime($this->periodDateEnd) < $timeMax)));
	}
}