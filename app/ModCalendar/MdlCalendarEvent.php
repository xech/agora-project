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
	private $_affectedCalendars=null;
	private $_confirmedCalendars=null;
	private $_proposedCalendars=null;
	private $_containerObj=null;

	/********************************************************************************************************
	 * SURCHARGE : CONSTRUCTEUR
	*********************************************************************************************************/
	public function __construct($objIdOrValues=null)
	{
		parent::__construct($objIdOrValues);
		//Timestamp du dateBegin / dateEnd
		if($this->dateBegin && $this->dateEnd){
			$this->timeBegin=strtotime($this->dateBegin);
			$this->timeEnd=strtotime($this->dateEnd);
			$this->ymdBegin=date("Y-m-d",$this->timeBegin);//cf evt sur plusieurs jours
		}
		//Couleur du background de l'evt en fonction de la categorie (Couleur par défaut : toujours sur 6 valeurs > cf "contrastColor()")
		$this->bgColor=($this->_idCat)  ?  $this->categoryObj()->color  :  "#555555";
		//Visibilité par défaut
		if(empty($this->contentVisible))  {$this->contentVisible="public";}
		//Masque le détail aux users n'ayant qu'un accès en lecture (voir aucun accès)
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
			////	ACCES TOTAL : AUTEUR & ADMIN GENERAL
			if(parent::accessRight()==3)  {return 3;}
			////	DROIT EN FONCTION DES AGENDAS AUQUELS L'EVT EST AFFECTÉ
			else{
				$editCalsCpt=$readCalsCpt=0;
				foreach($this->affectedCalendars() as $objCalendar){								//Parcourt les affectations aux agendas
					if($objCalendar->editContentRight())	{$editCalsCpt++;}						//Droit d'éditer le contenu
					elseif($objCalendar->readRight())		{$readCalsCpt++;}						//Droit de lecture
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

	/********************************************************************************************************
	 * SURCHARGE : RECUPÈRE L'AGENDA PRINCIPAL DE L'ÉVÉNEMENT
	 ********************************************************************************************************/
	public function containerObj()
	{
		if($this->_containerObj===null){
			$accessRightMax=0;																													//Init le droit d'accès le + élevé
			foreach($this->affectedCalendars() as $tmpCal){																						//Agendas où l'evt est affecté : confirmé ou pas encore confirmé (cf. édition d'evt et notifs mail)
				if($tmpCal->isMyPersoCalendar())					{$this->_containerObj=$tmpCal;	break;}										//Agenda perso : stop la boucle
				elseif($accessRightMax < $tmpCal->accessRight())	{$this->_containerObj=$tmpCal;	$accessRightMax=$tmpCal->accessRight();}	//Agenda avec un droit d'accès + élevé
			}
		}
		return $this->_containerObj;
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
		////	"Retirer l'événement de l'agenda BIDULE"
		if(!empty($options["_idCal"])  &&  $this->affectationDeleteRight($options["_idCal"])  &&  count($this->affectedCalendars())>=2){
			$curCalendar=Ctrl::getObj("calendar",$options["_idCal"]);
			$options["objOptions"][]=[
				"actionJs"=>"confirmRedir('".$this->getUrl("delete")."&_idCalDeleteAffectation=".$curCalendar->_id."')",
				"iconSrc"=>"deleteRemove.png",
				"label"=>Txt::trad("CALENDAR_evtRemoveFromCal").' <i>'.$curCalendar->getLabel().'</i>'
			];
		}
		////	"Retirer l'événement de cette date" (cf. Evt répétés)
		if(!empty($options["evtDeleteTime"])  &&  !empty($this->periodType)  &&  $this->editRight()){
			$options["objOptions"][]=[
				"actionJs"=>"confirmRedir('".$this->getUrl("delete")."&periodDateExceptionsAdd=".date('Y-m-d',$options["evtDeleteTime"])."')",
				"iconSrc"=>"deleteRemove.png",
				"label"=>Txt::trad("CALENDAR_evtRemoveFromDate")
			];
		}
		////	Agendas où est affecté l'evenement
		$options["objOptions"][]=[
			"separator"=>"<hr>",
			"iconSrc"=>"calendar/iconSmall.png",
			"label"=>$this->affectedCalendarsLabel()
		];
		////	Acces en lecture seule
		if($this->editRight()==false){
			$options["objOptions"][]=[
				"separator"=>"<hr>",
				"iconSrc"=>"info.png",
				"label"=>$this->tradObj("accessReadDetail")
			];
		}
		////	"Modifier l'événement et ses affectations aux agendas"  et  "Supprimer l'événement"
		$options["editLabel"]=Txt::trad("CALENDAR_evtEdit");
		$options["deleteLabel"]=Txt::trad("CALENDAR_evtDelete");
		////	Menu parent
		return parent::contextMenu($options);
	}

	/********************************************************************************************************
	 * SURCHARGE : LISTE DES USERS AFFECTÉS AUX AGENDAS OÙ SE TROUVE L'EVT
	 ********************************************************************************************************/
	public function affectedUserIds($accessWrite=false)
	{
		$affectedUserIds=[];
		foreach($this->affectedCalendars() as $tmpCal){
			$calUserIds=($tmpCal->isPersonal())  ?  $tmpCal->affectedUserIds(true)  :  $tmpCal->affectedUserIds($accessWrite);
			$affectedUserIds=array_merge($affectedUserIds,$calUserIds);
		}
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

	/********************************************************************************************************
	 * PROPRIETES DES L'EVT (cf .vEvtBlock)
	 ********************************************************************************************************/
	public function attributes()
	{
		////	Attributs de l'evt
		$attrList=[
			'data-timebegin'	=>$this->timeBegin,
			'data-timeend'		=>$this->timeEnd,
			'data-datelabel'	=>Txt::dateLabel("dateDefault",$this->timeBegin),
			'data-ymd'			=>$this->ymdBegin,
			'data-hm'			=>date("H:i",$this->timeBegin),
			'data-allday'		=>(!empty($this->allDay) ? 'true' : 'false'),
			'data-ispast'		=>($this->timeEnd < time() ? 'true' : 'false'),
			'data-bgcolor'		=>$this->bgColor,
			'data-isdraggable'	=>($this->editRight() && empty($this->periodType) && date('Y-m-d',$this->timeBegin)==date('Y-m-d',$this->timeEnd) ? 'true' : 'false'),//Editable + non périodique + sur un jour
		];
		////	Durées en fonction de la journée affichée
		$timeDayBegin=strtotime($this->ymdBegin." 00:00:00");																	//Jour affiché : début
		$timeDayEnd  =strtotime($this->ymdBegin." 23:59:59");																	//Jour affiché : fin
		$evtDayBefore=($this->timeBegin < $timeDayBegin);																		//Evt commence avant le jour affiché ?
		$evtDayAfter =($this->timeEnd > $timeDayEnd);																			//Evt termine après le jour affiché ?
		$attrList['data-timefromdaybegin']=($evtDayBefore==false)  ?  ($this->timeBegin-$timeDayBegin)  :  0;					//Temps écoulé depuis le début de journée (0:00)
		if($evtDayBefore==true && $evtDayAfter==true)	{$attrList['data-timeduration']=86400;}									//Affiche toute la journée
		elseif($evtDayBefore==true)						{$attrList['data-timeduration']=($this->timeEnd - $timeDayBegin);}		//Affiche l'evt à partir de 0h00
		elseif($evtDayAfter==true)						{$attrList['data-timeduration']=($timeDayEnd - $this->timeBegin);}		//Affiche l'evt jusqu'à 23h59
		else											{$attrList['data-timeduration']=($this->timeEnd - $this->timeBegin);}	//Affichage normal
		////	Retourne le tableau des attributs
		return $attrList;
	}

	/********************************************************************************************************
	 * AGENDAS OÙ L'EVT EST AFFECTÉ / PROPOSÉ
	 ********************************************************************************************************/
	public function affectedCalendars($confirmed="all")
	{
		if($this->_affectedCalendars===null){
			$sqlAffectations="SELECT * FROM ap_calendar WHERE _id in (select _idCal as _id from ap_calendarEventAffectation T2 WHERE _idEvt=".$this->_id;
			$this->_confirmedCalendars	=Db::getObjTab("calendar",$sqlAffectations." and confirmed=1)");		//Evts déjà confirmés
			$this->_proposedCalendars	=Db::getObjTab("calendar", $sqlAffectations." and confirmed IS NULL)");	//Evts proposés
			$this->_affectedCalendars	=array_merge($this->_confirmedCalendars, $this->_proposedCalendars);	//Evts confirmés & proposés
		}
		if($confirmed==="all")		{return $this->_affectedCalendars;}
		elseif($confirmed===true)	{return $this->_confirmedCalendars;}
		elseif($confirmed===false)	{return $this->_proposedCalendars;}
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
			foreach($this->affectedCalendars(true) as $objCalendar)		{$calendarsConfirmed.=", ".ucfirst($objCalendar->title);}
			foreach($this->affectedCalendars(false) as $objCalendar)	{$calendarsProposed.=", ".ucfirst($objCalendar->title);}
			if(!empty($calendarsConfirmed))	{$calendarsConfirmed=Txt::trad("CALENDAR_evtAffects")." : ".trim($calendarsConfirmed,",");}
			if(!empty($calendarsProposed))	{$calendarsProposed="<hr>".Txt::trad("CALENDAR_evtAffectToConfirm")." : ".trim($calendarsProposed,",");}
			return $calendarsConfirmed.$calendarsProposed;
		}
	}

	/********************************************************************************************************
	 * AFFICHE LA DATE DE L'EVT
	 ********************************************************************************************************/
	public function dateLabel($format="default", $monthVue=false)
	{
		//// Evt sur un créneau défini
		if(empty($this->allDay)){
			$timeEnd=($monthVue==false) ? $this->timeEnd : null;
			return Txt::dateLabel($format, $this->timeBegin, $timeEnd);
		}
		//// Evt "allDay"
		elseif($format!="mini"){
			return Txt::dateLabel("dateDefault", $this->timeBegin, $this->timeEnd).
					'&nbsp;<img src="app/img/arrowRightSmall.png">'.Txt::trad("CALENDAR_allDay").'&nbsp;<img src="app/img/calendar/allDay.png">';
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
				$periodLabel.=' : ';
				foreach(Txt::txt2tab($this->periodValues) as $tmpKey=>$tmpVal){
					if($tmpKey>0)  {$periodLabel.=", ";}
					if($this->periodType=="weekDay")	{$periodLabel.=Txt::trad("day_".$tmpVal);}		//ex: "lundi, mardi, etc"
					elseif($this->periodType=="month")	{$periodLabel.=Txt::trad("month_".$tmpVal);}	//ex: "janvier, février, etc"
				}
			}
			//// Exceptions de périodicité
			if(!empty($this->periodDateExceptions)){
				$periodLabel.='<br><br><img src="app/img/calendar/periodDateExceptions.png"> '.Txt::trad("CALENDAR_periodDateExceptions").' : ';
				foreach(array_filter(Txt::txt2tab($this->periodDateExceptions)) as $tmpKey=>$tmpVal){	//"array_filter" enlève les valeurs vides
					if($tmpKey>0)  {$periodLabel.=", ";}
					$periodLabel.=ucfirst(Txt::dateLabel("dateDefault",$tmpVal));
				}
			}
			//// Fin de périodicité
			if(!empty($this->periodDateEnd)){
				$periodLabel.='<br><br><img src="app/img/dateEnd.png"> '.Txt::trad("CALENDAR_periodDateEnd").' : '.ucfirst(Txt::dateLabel("dateDefault",$this->periodDateEnd));
			}
			//// Renvoie le résultat
			return $periodLabel;
		}
	}

	/********************************************************************************************************
	 * VÉRIFIE SI L'EVENEMENT EST DANS LE PASSÉ (sans périodicité ou fin de périodicité passée)
	 ********************************************************************************************************/
	public function evtIsPast($timeMax)
	{
		return (!empty($timeMax)  &&  strtotime($this->dateEnd) < $timeMax  &&  (empty($this->periodType) || (!empty($this->periodDateEnd) && strtotime($this->periodDateEnd) < $timeMax)));
	}
}