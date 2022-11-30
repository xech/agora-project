<?php
/**
* This file is part of the Agora-Project Software package.
*
* @copyright (c) Agora-Project Limited <https://www.agora-project.net>
* @license GNU General Public License, version 2 (GPL-2.0)
*/


/*
 * MODELE DES AGENDAS
 */
class MdlCalendar extends MdlObject
{
	const moduleName="calendar";
	const objectType="calendar";
	const dbTable="ap_calendar";
	const MdlObjectContent="MdlCalendarEvent";
	const hasAttachedFiles=true;
	protected static $_hasAccessRight=true;
	public static $requiredFields=["title"];
	public static $searchFields=["title","description"];
	public static $displayModes=["month","week","workWeek","4Days","day"];	//Modes d'affichage des agendas
	public static $forceDeleteRight=false;									//Force la suppression de l'agenda pour les agendas persos : cf. "MdlCalendar::deleteRight()"
	//Valeurs mises en cache
	private static $_readableCalendars=null;
	private static $_myCalendars=null;
	private static $_affectationCalendars=null;


	/*******************************************************************************************
	 * SURCHARGE : CONSTRUCTEUR
	 *******************************************************************************************/
	function __construct($objIdOrValues=null)
	{
		parent::__construct($objIdOrValues);
		//Libellé de l'agenda perso
		if($this->type=="user"){
			$this->title=$this->autorLabel();//Pour l'affichage
			$this->userName=Ctrl::getObj("user",$this->_idUser)->name;//Champ utilisé pour le tri des agendas (cf. "sortCalendars()")
			$this->userFirstName=Ctrl::getObj("user",$this->_idUser)->firstName;//Idem
		}
		//Plage horaire de l'agenda
		if(empty($this->timeSlot)){
			$this->timeSlotBegin=8;
			$this->timeSlotEnd=20;
		}else{
			$tmpTimeSlot=explode("-",$this->timeSlot);
			$this->timeSlotBegin=$tmpTimeSlot[0];
			$this->timeSlotEnd=$tmpTimeSlot[1];
		}
	}

	/**************************************************************************************************************
	 * VERIF SI L'USER COURANT PEUT AJOUTER UN AGENDA DE RESSOURCE (ne concerne pas les agenda de type 'user')
	 **************************************************************************************************************/
	public static function addRight()
	{
		return (Ctrl::$curUser->isAdminSpace() || (Ctrl::$curUser->isUser() && Ctrl::$curSpace->moduleOptionEnabled(self::moduleName,"adminAddRessourceCalendar")==false));
	}

	/*************************************************************************************************************************************************************************
	 * VERIF SI L'USER COURANT PEUT AJOUTER OU PROPOSER UN ÉVÉNEMENT ("true" pour tous les users && pour les guests si l'option "propositionGuest" de l'agenda est activé)
	 *************************************************************************************************************************************************************************/
	public function addOrProposeEvt()
	{
		return (Ctrl::$curUser->isUser() || !empty($this->propositionGuest));
	}

	/***************************************************************************************************************************************************************
	 * VERIF SI L'EVT SE TROUVE SUR LA PÉRIODE SELECTIONNEE (début de l'evt dans la période || fin de l'evt dans la période || evt avant et après la période)
	 ***************************************************************************************************************************************************************/
	public static function evtInTimeSlot($evtBegin, $evtEnd, $periodBegin, $periodEnd)
	{
		return ( ($periodBegin<=$evtBegin && $evtBegin<=$periodEnd) || ($periodBegin<=$evtEnd && $evtEnd<=$periodEnd) || ($evtBegin<=$periodBegin && $periodEnd<=$evtEnd) );
	}

	/*******************************************************************************************
	 * VERIF SI C'EST L'AGENDA PERSO DE L'USER COURANT
	 *******************************************************************************************/
	public function isPersonalCalendar()
	{
		return ($this->type=="user" && $this->isAutor());
	}

	/**************************************************************************************************************************************
	 * SURCHARGE : DROIT DE SUPPRIMER L'AGENDA POUR L'USER COURANT => DESACTIVÉ PAR DEFAUT POUR LES AGENDAS PERSOS (cf. $forceDeleteRight)
	 **************************************************************************************************************************************/
	public function deleteRight()
	{
		//"MdlUser::delete()" supprime l'agenda perso en passant $forceDeleteRight à "true" : cela garde ainsi le "deleteRight()" à "true" dans "MdlCalendar::delete()" et "parent::delete()"
		return ($this->type=="user" && $this::$forceDeleteRight==false)  ?  false  :  parent::deleteRight();
	}

	/*******************************************************************************************
	 * SURCHARGE : SUPPRESSION D'AGENDA
	 *******************************************************************************************/
	public function delete()
	{
		//Controle le droit d'accès
		if($this->deleteRight())
		{
			//Supprime les evenements affectés uniquement à l'agenda en question
			$eventList=Db::getCol("SELECT DISTINCT _idEvt FROM ap_calendarEventAffectation WHERE _idCal=".$this->_id." AND _idEvt NOT IN (select _idEvt from ap_calendarEventAffectation where _idCal!=".$this->_id.")");
			foreach($eventList as $_idEvt){
				$tmpEvt=Ctrl::getObj("calendarEvent",$_idEvt);
				$tmpEvt->delete();
			}
			//Puis supprime les affectations de l'agenda aux evenements sur plusieurs agendas
			Db::query("DELETE FROM ap_calendarEventAffectation WHERE _idCal=".$this->_id);
			//Supprime enfin l'agenda
			parent::delete();
		}
	}

	/*******************************************************************************************
	 * EVENEMENTS CONFIRMÉS AFFECTÉS A L'AGENDA
	 *******************************************************************************************/
	public function evtList($periodTimeBegin=null, $periodTimeEnd=null, $accessRightMini=0.5, $orderByHourMinute=true, $pluginParams=null)
	{
		//// Evénements sur un période donnée (début de l'evt dans la période || fin de l'evt dans la période || evt avant et après la période)  +  Evénements périodiques 
		$sqlPeriod=null;
		if(!empty($periodTimeBegin) && !empty($periodTimeEnd)){
			$periodBegin=Db::format(date("Y-m-d 00:00",$periodTimeBegin));
			$periodEnd  =Db::format(date("Y-m-d 23:59",$periodTimeEnd));
			$sqlPeriod="AND ( (dateBegin between ".$periodBegin." and ".$periodEnd.") OR (dateEnd between ".$periodBegin." and ".$periodEnd.") OR (dateBegin <= ".$periodBegin." and ".$periodEnd." <= dateEnd) OR periodType is not null)";
		}
		//// Evénements confirmés et affectés à l'agenda
		$sqlPlugins=(!empty($pluginParams))  ?  "AND ".MdlCalendarEvent::sqlPlugins($pluginParams)  :  null;//Sélection d'evt "plugins"
		$sqlOrderBy=($orderByHourMinute==true)  ?  "DATE_FORMAT(dateBegin,'%H:%i') ASC"  :  "dateBegin ASC";//Tri par "H:m" (affiche juste une journée) || Tri par "dateBegin" (affiche une liste complete: "plugins")
		$eventsList=Db::getObjTab("calendarEvent","SELECT * FROM ap_calendarEvent WHERE _id IN (select _idEvt from ap_calendarEventAffectation where _idCal=".$this->_id." and confirmed=1) ".$sqlPeriod." ".$sqlPlugins." ORDER BY ".$sqlOrderBy);
		//// renvoie les evts en fonction du droit d'accès minimum 
		$eventsReturned=[];
		foreach($eventsList as $evtTmp){
			if($evtTmp->accessRight()>=$accessRightMini)  {$eventsReturned[]=$evtTmp;}
		}
		//// Renvoie les evenements
		return $eventsReturned;
	}

	/*********************************************************************************************************************************
	 * FILTRE "$evtList" : RECUPERE LES EVENEMENTS DU JOUR + LES EVENEMENTS PERIODIQUES SUR LE JOUR (cf. $dayBegin/$dayEnd)
	 * Note : les evts périodiques sont clonés pour chaque occurence de l'evt
	 *********************************************************************************************************************************/
	public static function periodEvts($evtList, $dayBegin, $dayEnd)
	{
		$eventsReturned=[];
		foreach($evtList as $tmpEvt)
		{
			//// CLONE L'EVT POUR CHAQUE JOUR (cf. evt sur plusieurs jours ou périodique)  &&  TIME DU DEBUT/FIN DE L'EVT
			$tmpEvt=clone $tmpEvt;
			$evtBegin=strtotime($tmpEvt->dateBegin);
			$evtEnd=strtotime($tmpEvt->dateEnd);
			//// AJOUTE LES EVT DU JOUR  ||  EVT PERIODIQUE/RÉCURRENT SUR LE JOUR
			if(static::evtInTimeSlot($evtBegin,$evtEnd,$dayBegin,$dayEnd))  {$eventsReturned[]=$tmpEvt;}
			elseif(!empty($tmpEvt->periodType))
			{
				//Evenement sur le jour =>  déjà commencé  &&  (pas de fin de périodicité || fin de périodicité pas encore arrivé)  &&  (pas de date d'exception || "dateBegin" absent des dates d'exception)
				if($evtBegin<$dayBegin  &&  (empty($tmpEvt->periodDateEnd) || $dayEnd<=strtotime($tmpEvt->periodDateEnd." 23:59"))  &&  (empty($tmpEvt->periodDateExceptions) || preg_match("/".date("Y-m-d",$dayBegin)."/",$tmpEvt->periodDateExceptions)==false))
				{
					//Récupère les valeurs de la périodicité : fonction du "periodType"
					$periodValues=Txt::txt2tab($tmpEvt->periodValues);
					//Vérifie si l'evt périodique est présent sur le jour courant : il oui, on prépare le reformatage de la date
					$formatModified=$formatKept=null;
					if($tmpEvt->periodType=="weekDay" && in_array(date("N",$dayBegin),$periodValues))												{$formatModified="Y-m-d";	$formatKept=" H:i";}	//jour de semaine
					elseif($tmpEvt->periodType=="month" && in_array(date("m",$dayBegin),$periodValues) && date("d",$evtBegin)==date("d",$dayBegin))	{$formatModified="Y-m";		$formatKept="-d H:i";}	//jour du mois
					elseif($tmpEvt->periodType=="year" && date("m-d",$evtBegin)==date("m-d",$dayBegin))												{$formatModified="Y";		$formatKept="-m-d H:i";}//jour de l'année
					//Reformate pour que le début/fin de l'evt corresponde à la date courante && Ajoute enfin l'evt à $eventsReturned (vérif qu'il soit sur le créneau : cf. "actionTimeSlotBusy()")
					if(!empty($formatModified) && !empty($formatKept)){
						$tmpEvt->dateBegin=date($formatModified,$dayBegin).date($formatKept,$evtBegin);
						$tmpEvt->dateEnd  =date($formatModified,$dayEnd).date($formatKept,$evtEnd);
						if(static::evtInTimeSlot(strtotime($tmpEvt->dateBegin),strtotime($tmpEvt->dateEnd),$dayBegin,$dayEnd))  {$eventsReturned[]=$tmpEvt;}
					}
				}
			}
		}
		return $eventsReturned;
	}

	/*******************************************************************************************
	 * AGENDAS ACCESSIBLES EN LECTURE A L'USER COURANT
	 *******************************************************************************************/
	public static function readableCalendars()
	{
		//Agendas de ressource && Agendas personnels (pas "Disabled") && Agenda de l'user
		if(self::$_readableCalendars===null){
			$sqlDisplay=self::sqlDisplay();
			$ressourceCals	=Db::getObjTab("calendar","SELECT DISTINCT * FROM ap_calendar WHERE type='ressource' AND ".$sqlDisplay);
			$persoCals		=Db::getObjTab("calendar","SELECT DISTINCT * FROM ap_calendar WHERE type='user' AND (".$sqlDisplay." OR _idUser=".Ctrl::$curUser->_id.") AND _idUser NOT IN (select _id from ap_user where calendarDisabled=1)");
			self::$_readableCalendars=self::sortCalendars( array_merge($ressourceCals,$persoCals) );//Tri les agendas
		}
		return self::$_readableCalendars;
	}

	/*******************************************************************************************
	 * AGENDAS ACCESSIBLES EN ECRITURE POUR L'USER COURANT
	 *******************************************************************************************/
	public static function writableCalendars()
	{
		if(self::$_myCalendars===null){
			self::$_myCalendars=[];
			foreach(self::readableCalendars() as $tmpCal){
				if($tmpCal->editContentRight())  {self::$_myCalendars[]=$tmpCal;}
			}
		}
		return self::$_myCalendars;
	}

	/**************************************************************************************************
	 * AGENDAS SUR LESQUELS L'USER COURANT PEUT AFFECTER OU PROPOSER DES ÉVÉNEMENTS
	 **************************************************************************************************/
	public static function affectationCalendars()
	{
		if(self::$_affectationCalendars===null)
		{
			//Agendas accessibles en lecture
			self::$_affectationCalendars=self::readableCalendars();
			//Ajoute les agendas persos des users de l'espace, mais inaccessibles en lecture ("guest" non concernés) : cf. propositions d'événement
			if(Ctrl::$curUser->isUser()){
				$otherPersoCalendars=Db::getObjTab("calendar", "SELECT DISTINCT * FROM ap_calendar WHERE type='user' AND _idUser IN (".Ctrl::$curSpace->getUsers("idsSql").") AND _idUser NOT IN (select _id from ap_user where calendarDisabled=1)");
				foreach($otherPersoCalendars as $tmpCal){
					if(!in_array($tmpCal,self::$_affectationCalendars))  {self::$_affectationCalendars[]=$tmpCal;}//Ajoute l'agenda?
				}
				self::$_affectationCalendars=self::sortCalendars(self::$_affectationCalendars);//Tri les agendas
			}
		}
		return self::$_affectationCalendars;
	}

	/*******************************************************************************************
	 * AGENDAS ACTUELLEMENT AFFICHÉS
	 *******************************************************************************************/
	public static function displayedCalendars($readableCalendars)
	{
		//Init les agendas à retourner && Récup au besoin les agendas enregistrés en préférence
		$displayedCalendars=[];
		$prefCalendars=Txt::txt2tab(Ctrl::prefUser("displayedCalendars"));
		//Récupère les agendas à afficher :  Agendas enregistrés en préférences  OU  Agenda de l'espace créé par défaut (_id=1)
		foreach($readableCalendars as $tmpCal){
			if(in_array($tmpCal->_id,$prefCalendars) || (empty($prefCalendars) && $tmpCal->_id==1))  {$displayedCalendars[]=$tmpCal;}
		}
		//Toujours pas d'agendas à afficher : on prend le premier des $readableCalendars
		if(empty($displayedCalendars) && !empty($readableCalendars))  {$displayedCalendars[]=$readableCalendars[0];}
		//Supprime les evénements de plus de 3 ans (lancé en début de session)
		if(empty($_SESSION["calendarsCleanEvt"]))
		{
			//Période des evenements "old"
			$time100YearsAgo=time()-(86400*365*100);
			$time5YearsAgo=time()-(86400*365*5);
			//Sélectionne les agendas avec "editContentRight()"
			foreach($displayedCalendars as $tmpCal){
				if($tmpCal->editContentRight()){
					foreach($tmpCal->evtList($time100YearsAgo,$time5YearsAgo,2) as $tmpEvt){	//AccessRight>=2
						if($tmpEvt->isOldEvt($time5YearsAgo))  {$tmpEvt->delete();}				//"isOldEvt()" : date de fin passé && sans périodicité ou périodicité terminé
					}
				}
			}
			$_SESSION["calendarsCleanEvt"]=true;
		}
		//Retour les agendas à afficher
		return $displayedCalendars;
	}

	/**********************************************************************************************************************************************
	 * LISTE D'AGENDAS : TRI DES AGENDAS => AGENDA DE L'USER COURANT >> PUIS AGENDAS DE RESSOURCE >> PUIS AUTRES AGENDAS D'USERS
	 **********************************************************************************************************************************************/
	public static function sortCalendars($calendarsTab)
	{
		//Prépare le tri en fonction du champs spécifique "sortField"
		$userSortField=(Ctrl::$agora->personsSort=="name")  ?  "userName"  :  "userFirstName";//Tri des agendas persos en fonction du nom ou du prénom (cf. "__construct()" ci-dessus)
		foreach($calendarsTab as $tmpCal){
			if($tmpCal->isPersonalCalendar())	{$tmpCal->sortField="A__".$tmpCal->$userSortField;}
			elseif($tmpCal->type=="ressource")	{$tmpCal->sortField="B__".$tmpCal->title;}
			else								{$tmpCal->sortField="C__".$tmpCal->$userSortField;}
		}
		//Tri les agendas via "self::sortCompareCalendars()"
		usort($calendarsTab,["self","sortCompareCalendars"]);
		return $calendarsTab;
	}
	//// Comparaison binaire de caractere, mais insensible à la casse
	public static function sortCompareCalendars($obj1, $obj2){
		return strcasecmp($obj1->sortField, $obj2->sortField);
	}

	/*******************************************************************************************
	 * SURCHARGE : MENU CONTEXTUEL
	 *******************************************************************************************/
	public function contextMenu($options=null)
	{
		//Accès en écriture au contenu : Importe des evts au format ICAL (cf. "CtrlCalendar::actionImportEvents()")
		if($this->editContentRight()){
			$options["specificOptions"][]=["actionJs"=>"lightboxOpen('?ctrl=calendar&action=importEvents&typeId=".$this->_typeId."')",  "iconSrc"=>"calendar/icalImport.png",  "label"=>Txt::trad("CALENDAR_importIcal")];
		}
		//Accès en lecture : Download les evts au format ICAL (cf. "CtrlCalendar::actionExportEvents()")  &&  Lien externe de download des evts au format ICAL ("CtrlMisc::actionDisplayIcal()")
		if($this->readRight())
		{
			//Download des evts
			$options["specificOptions"][]=["actionJs"=>"if(confirm('".Txt::trad("confirm",true)."')) redir('?ctrl=calendar&action=exportEvents&typeId=".$this->_typeId."')",  "iconSrc"=>"calendar/icalExport.png",  "label"=>Txt::trad("CALENDAR_exportIcal")];
			//Lien externe de download
			$actionJsTmp="$('#urlIcal".$this->_typeId."').show().select(); document.execCommand('copy'); $('#urlIcal".$this->_typeId."').hide(); notify('".Txt::trad("copyUrlConfirmed",true)."');";
			$labelTmp=Txt::trad("CALENDAR_icalUrl")."<input id='urlIcal".$this->_typeId."' value=\"".Req::getCurUrl()."/index.php?ctrl=misc&action=DisplayIcal&typeId=".$this->_typeId."&md5Id=".$this->md5Id()."\" style='display:none;'>";
			$options["specificOptions"][]=["actionJs"=>$actionJsTmp,  "iconSrc"=>"calendar/icalExportLink.png",  "label"=>$labelTmp,  "tooltip"=>Txt::trad("CALENDAR_icalUrlCopy")];
		}
		//Renvoie le menu surchargé
		return parent::contextMenu($options);
	}
}