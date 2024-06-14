<?php
/**
* This file is part of the Agora-Project Software package
*
* @copyleft Agora-Project <https://www.agora-project.net>
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
		return (Ctrl::$curUser->isSpaceAdmin() || (Ctrl::$curUser->isUser() && Ctrl::$curSpace->moduleOptionEnabled(self::moduleName,"adminAddRessourceCalendar")==false));
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
	public static function eventInTimeSlot($eventTimeBegin, $eventTimeEnd, $periodTimeBegin, $periodTimeEnd)
	{
		return ( ($periodTimeBegin<=$eventTimeBegin && $eventTimeBegin<=$periodTimeEnd) || ($periodTimeBegin<=$eventTimeEnd && $eventTimeEnd<=$periodTimeEnd) || ($eventTimeBegin<=$periodTimeBegin && $periodTimeEnd<=$eventTimeEnd) );
	}

	/*******************************************************************************************
	 * VERIF SI C'EST L'AGENDA PARTAGE DE L'ESPACE COURANT
	 *******************************************************************************************/
	public function isSpacelCalendar()
	{
		return ($this->type=="ressource" && ($this->_id==1 || $this->title==Ctrl::$curSpace->name));
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
	public function eventList($timeBegin=null, $timeEnd=null, $filterByCategory=true, $orderByHourMinute=true, $accessRightMin=0.5, $pluginParams=null)
	{
		//// Evénements sur un période donnée (début de l'evt dans la période || fin de l'evt dans la période || evt avant et après la période)  +  Evénements périodiques 
		$sqlPeriod=null;
		if(!empty($timeBegin) && !empty($timeEnd)){
			$periodBegin=Db::format(date("Y-m-d 00:00",$timeBegin));
			$periodEnd  =Db::format(date("Y-m-d 23:59",$timeEnd));
			$sqlPeriod="AND ( (dateBegin between ".$periodBegin." and ".$periodEnd.") OR (dateEnd between ".$periodBegin." and ".$periodEnd.") OR (dateBegin <= ".$periodBegin." and ".$periodEnd." <= dateEnd) OR periodType is not null)";
		}
		//// Evénements confirmés et affectés à l'agenda
		$sqlCategory=($filterByCategory==true)  ?  MdlCalendarCategory::sqlCategoryFilter()  :  null;			//Filtre par catégorie 
		$sqlPlugins=(!empty($pluginParams))  ?  "AND ".MdlCalendarEvent::sqlPlugins($pluginParams)  :  null;	//Sélection d'evt "plugins"
		$sqlOrderBy=($orderByHourMinute==true)  ?  "DATE_FORMAT(dateBegin,'%H:%i') ASC"  :  "dateBegin ASC";	//Tri par "H:m" (affiche juste une journée) || Tri par "dateBegin" (affiche une liste complete: "plugins")
		$eventsList=Db::getObjTab("calendarEvent","SELECT * FROM ap_calendarEvent WHERE _id IN (select _idEvt from ap_calendarEventAffectation where _idCal=".$this->_id." and confirmed=1) ".$sqlPeriod." ".$sqlCategory." ".$sqlPlugins." ORDER BY ".$sqlOrderBy);
		//// renvoie les evts en fonction du droit d'accès minimum 
		$eventList=[];
		foreach($eventsList as $evtTmp){
			if($evtTmp->accessRight()>=$accessRightMin)  {$eventList[]=$evtTmp;}
		}
		//// Renvoie les evenements
		return $eventList;
	}

	/*********************************************************************************************************************************
	 * FILTRE LES EVENTS PASSES EN PARAMETRES, SUR UNE PERIODE DONNEE
	 * Note : les evts périodiques sont clonés pour chaque occurence de l'evt
	 *********************************************************************************************************************************/
	public static function eventFilter($eventList, $timeBegin, $timeEnd)
	{
		$eventListFiltered=[];
		foreach($eventList as $tmpEvt)
		{
			//// CLONE L'EVT POUR CHAQUE JOUR (cf. evt sur plusieurs jours ou périodique)  &&  TIME DU DEBUT/FIN DE L'EVT
			$tmpEvt=clone $tmpEvt;
			$eventTimeBegin=strtotime($tmpEvt->dateBegin);
			$eventTimeEnd  =strtotime($tmpEvt->dateEnd);
			//// AJOUTE LES EVT DU JOUR  ||  EVT PERIODIQUE/RÉCURRENT SUR LE JOUR
			if(static::eventInTimeSlot($eventTimeBegin,$eventTimeEnd,$timeBegin,$timeEnd))  {$eventListFiltered[]=$tmpEvt;}
			elseif(!empty($tmpEvt->periodType))
			{
				//Evenement sur le jour =>  déjà commencé  &&  (pas de fin de périodicité || fin de périodicité pas encore arrivé)  &&  (pas de date d'exception || "dateBegin" absent des dates d'exception)
				if($eventTimeBegin<$timeBegin  &&  (empty($tmpEvt->periodDateEnd) || $timeEnd<=strtotime($tmpEvt->periodDateEnd." 23:59"))  &&  (empty($tmpEvt->periodDateExceptions) || preg_match("/".date("Y-m-d",$timeBegin)."/",$tmpEvt->periodDateExceptions)==false))
				{
					//Récupère les valeurs de la périodicité : fonction du "periodType"
					$periodValues=Txt::txt2tab($tmpEvt->periodValues);
					//Vérifie si l'evt périodique est présent sur le jour courant : il oui, on prépare le reformatage de la date
					$formatModified=$formatKept=null;
					if($tmpEvt->periodType=="weekDay" && in_array(date("N",$timeBegin),$periodValues))														{$formatModified="Y-m-d";	$formatKept=" H:i";}	//jour de semaine
					elseif($tmpEvt->periodType=="month" && in_array(date("m",$timeBegin),$periodValues) && date("d",$eventTimeBegin)==date("d",$timeBegin))	{$formatModified="Y-m";		$formatKept="-d H:i";}	//jour du mois
					elseif($tmpEvt->periodType=="year" && date("m-d",$eventTimeBegin)==date("m-d",$timeBegin))												{$formatModified="Y";		$formatKept="-m-d H:i";}//jour de l'année
					//Reformate pour que le début/fin de l'evt corresponde à la date courante  &&  Ajoute enfin l'evt à $eventListFiltered (vérif qu'il soit sur le créneau : cf. "actionTimeSlotBusy()")
					if(!empty($formatModified) && !empty($formatKept)){
						$tmpEvt->dateBegin=date($formatModified,$timeBegin).date($formatKept,$eventTimeBegin);
						$tmpEvt->dateEnd  =date($formatModified,$timeEnd).date($formatKept,$eventTimeEnd);
						if(static::eventInTimeSlot(strtotime($tmpEvt->dateBegin),strtotime($tmpEvt->dateEnd),$timeBegin,$timeEnd))  {$eventListFiltered[]=$tmpEvt;}
					}
				}
			}
		}
		return $eventListFiltered;
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
		//// Init les agendas
		$displayedCalendars=[];
		//// Récupère d'abord les agendas enregistrés en préférence (pas forcément dans les $readableCalendars de l'espace courant !)
		$prefCalendars=Txt::txt2tab(Ctrl::prefUser("displayedCalendars"));
		if(!empty($prefCalendars)){
			foreach($readableCalendars as $tmpCal){
				if(in_array($tmpCal->_id,$prefCalendars))  {$displayedCalendars[]=$tmpCal;}
			}
		}
		//// Tjs aucun agenda : récupère l'agenda partagé de l'espace
		if(empty($displayedCalendars)){
			foreach($readableCalendars as $tmpCal){
				if($tmpCal->isSpacelCalendar())  {$displayedCalendars[]=$tmpCal;}
			}
		}
		//// Tjs aucun agenda : récupère l'agenda perso de l'user courant
		if(empty($displayedCalendars)){
			foreach($readableCalendars as $tmpCal){
				if($tmpCal->isPersonalCalendar())	{$displayedCalendars[]=$tmpCal;}
			}
		}
		//// Supprime les evénements de plus de 5 ans (lancé en début de session)
		if(empty($_SESSION["calendarsCleanEvt"]))
		{
			//Période des evenements "old"
			$time30YearsAgo=time()-(86400*365*30);
			$time5YearsAgo =time()-(86400*365*5);
			//Sélectionne les agendas avec "editContentRight()"
			foreach($displayedCalendars as $tmpCal){
				if($tmpCal->editContentRight()){
					foreach($tmpCal->eventList($time30YearsAgo,$time5YearsAgo,false,false,2) as $tmpEvt){	//$filterByCategory=false  & $orderByHourMinute=false  & $accessRightMin=2
						if($tmpEvt->isOldEvt($time5YearsAgo))  {$tmpEvt->delete();}							//"isOldEvt()" : date de fin passé && sans périodicité ou périodicité terminé
					}
				}
			}
			$_SESSION["calendarsCleanEvt"]=true;
		}
		//// Retourne les agendas affichés
		return $displayedCalendars;
	}

	/**********************************************************************************************************************************************
	 * LISTE D'AGENDAS TRIÉS :  AGENDA DE L'ESPACE COURANT  >  AGENDAS DE RESSOURCE  >  AGENDA DE L'USER COURANT  >  AUTRES AGENDAS D'USERS
	 **********************************************************************************************************************************************/
	public static function sortCalendars($calendarsTab)
	{
		//Prépare le tri en fonction du champs spécifique "sortField"
		$userSortField=(Ctrl::$agora->personsSort=="name")  ?  "userName"  :  "userFirstName";//Tri des agendas persos en fonction du nom ou du prénom (cf. "__construct()" ci-dessus)
		foreach($calendarsTab as $tmpCal){
			if($tmpCal->isSpacelCalendar())			{$tmpCal->sortField="A__".$tmpCal->$userSortField;}
			elseif($tmpCal->type=="ressource")		{$tmpCal->sortField="B__".$tmpCal->title;}
			elseif($tmpCal->isPersonalCalendar())	{$tmpCal->sortField="C__".$tmpCal->$userSortField;}
			else									{$tmpCal->sortField="D__".$tmpCal->$userSortField;}
		}
		//Tri les agendas via "MdlCalendar::sortCompareCalendars()"  (pas de "self::sortCompareCalendars()")
		usort($calendarsTab,["MdlCalendar","sortCompareCalendars"]);
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