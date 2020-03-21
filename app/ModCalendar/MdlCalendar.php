<?php
/**
* This file is part of the Agora-Project Software package.
*
* @copyright (c) Agora-Project Limited <https://www.agora-project.net>
* @license GNU General Public License, version 2 (GPL-2.0)
*/


/*
 * Modele des agendas
 */
class MdlCalendar extends MdlObject
{
	const moduleName="calendar";
	const objectType="calendar";
	const dbTable="ap_calendar";
	const hasAccessRight=true;
	const MdlObjectContent="MdlCalendarEvent";
	const hasAttachedFiles=true;
	public static $requiredFields=array("title");
	public static $searchFields=array("title","description");
	//Droit de supprimer l'agenda personel : "true" si on supprime l'user en question
	public static $persoCalendarDeleteRight=false;
	//Valeurs mises en cache
	private static $_visibleCalendars=null;
	private static $_myCalendars=null;
	private static $_affectationCalendars=null;
	private static $_addProposeEvtRight=null;
	


	/*
	 * SURCHARGE : Constructeur
	 */
	function __construct($objIdOrValues=null)
	{
		parent::__construct($objIdOrValues);
		//Libellé de l'agenda perso
		if($this->type=="user"){
			$this->title=$this->displayAutor();//Pour l'affichage
			$this->userName=Ctrl::getObj("user",$this->_idUser)->name;//Pour le tri
			$this->userFirstName=Ctrl::getObj("user",$this->_idUser)->firstName;//idem
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

	/*
	 * Verifie si c'est l'agenda perso de l'user courant
	 */
	function isMyPerso()
	{
		return ($this->type=="user" && $this->isAutor());
	}

	/*
	 * Droit d'ajouter un agenda de ressource
	 */
	public static function addRight()
	{
		return (Ctrl::$curUser->isAdminSpace() || (Ctrl::$curUser->isUser() && Ctrl::$curSpace->moduleOptionEnabled(self::moduleName,"adminAddRessourceCalendar")==false));
	}

	/*
	 * Droit d'ajouter/proposer un événement :  Toujours ok pour les users, sinon pour les Guests on renvoie "true" uniquement si l'agenda courant est affecté en écriture à tous les users de l'espace
	 */
	public function addProposeEvtRight()
	{
		if(self::$_addProposeEvtRight===null){
			if(Ctrl::$curUser->isUser())	{self::$_addProposeEvtRight=true;}
			else							{self::$_addProposeEvtRight=(Db::getVal("SELECT count(*) FROM ap_objectTarget WHERE objectType=".Db::format(static::objectType)." AND _idObject=".$this->_id." AND _idSpace=".Ctrl::$curSpace->_id." AND target='spaceUsers' AND accessRight>1")>0);}
		}
		return self::$_addProposeEvtRight;
	}

	/*
	 * SURCHARGE : droit de suppression d'un agenda : pas pour les agendas d'users
	 */
	public function deleteRight(){
		return ($this->type=="user" && $this::$persoCalendarDeleteRight==false)  ?  false  :  parent::deleteRight();
	}

	/*
	 * SURCHARGE : suppression d'agenda
	 */
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

	/*
	 * Evenements de l'agenda en fonction d'une période (evt confirmés)
	 */
	public function evtList($curBegin=null, $curEnd=null, $accessRightMini=0.5, $orderByHourMinute=true, $pluginParams=null)
	{
		////	Evt sur une période/créneau donnée?
		$sqlTimeSlot=null;
		if(!empty($curBegin) && !empty($curEnd)){
			$tmpBegin=Db::format(date("Y-m-d 00:00",$curBegin));
			$tmpEnd=Db::format(date("Y-m-d 23:59",$curEnd));
			$sqlTimeSlot="AND (  (dateBegin between ".$tmpBegin." and ".$tmpEnd.")  OR  (dateEnd between ".$tmpBegin." and ".$tmpEnd.")  OR  (DateBegin <= ".$tmpBegin." and DateEnd >= ".$tmpEnd.")  OR  periodType is not null)";
		}
		////	 Liste des evenements, en fonction des droits d'accès. Tri par "Heure:Minute" si affiché sur un jour (cf. evt périodiques) OU Tri par "dateBegin" si affiché une liste complete (cf. plugins)
		$sqlPlugins=(!empty($pluginParams))  ?  "AND ".MdlCalendarEvent::sqlPluginObjects($pluginParams)  :  null;//Sélection d'un plugin?
		$sqlOrderBy=($orderByHourMinute==true)  ?  "DATE_FORMAT(dateBegin,'%H:%i') ASC"  :  "dateBegin ASC";//Filtre par "H:m" ou par "dateBegin"
		$eventsObjList=Db::getObjTab("calendarEvent","SELECT * FROM ap_calendarEvent WHERE _id IN (select _idEvt from ap_calendarEventAffectation where _idCal=".$this->_id." and confirmed=1) ".$sqlTimeSlot." ".$sqlPlugins." ORDER BY ".$sqlOrderBy);
		////	renvoie les evts en fonction du droit d'accès minimum 
		$evtListReturned=[];
		foreach($eventsObjList as $tmpObj){
			if($tmpObj->accessRight()>=$accessRightMini)  {$evtListReturned[]=$tmpObj;}
		}
		//renvoie les evenements
		return $evtListReturned;
	}

	/*
	 * Filtre des Evts pour une journée OU un créneau horaire (cf: "actionTimeSlotBusy()")
	 * Note : les evts périodiques ne sont récupérés qu'une fois. On utilise donc cette fonction qui clone chaque occurence d'un même evenement, répété sur plusieurs jours.
	 */
	public static function periodEvts($evtList, $curBegin, $curEnd)
	{
		$evtListReturned=[];
		foreach($evtList as $tmpEvt)
		{
			//Clone l'evt pour chaque jour (evt sur plusieurs jours ou périodique : une instance d'evt par jour)  &&  Debut/fin de l'evt au format timestamp 
			$tmpEvt=clone $tmpEvt;
			$evtBegin=strtotime($tmpEvt->dateBegin);
			$evtEnd=strtotime($tmpEvt->dateEnd);
			//EVT SUR LA JOURNEE/CRENEAU HORAIRE : Début de l'evt dans le créneau || Fin de l'evt dans le créneau || evt avant et après le créneau
			if(static::evtInTimeSlot($evtBegin,$evtEnd,$curBegin,$curEnd))	{$evtListReturned[]=$tmpEvt;}
			//EVT PERIODIQUE
			elseif(!empty($tmpEvt->periodType))
			{
				//Evenement sur le jour =>  déjà commencé  &&  (pas de fin de périodicité || fin de périodicité pas encore arrivé)  &&  (pas de date d'exception || "dateBegin" absent des dates d'exception)
				if($evtBegin<$curBegin  &&  (empty($tmpEvt->periodDateEnd) || $curEnd<=strtotime($tmpEvt->periodDateEnd." 23:59"))  &&  (empty($tmpEvt->periodDateExceptions) || in_array(date("Y-m-d",$curBegin),Txt::txt2tab($tmpEvt->periodDateExceptions))==false))
				{
					//L'evt périodique est présent sur le jour courant : Reformate le début/fin de l'evt pour qu'il corresponde à la date courante
					$periodValues=Txt::txt2tab($tmpEvt->periodValues);
					$dateFormatModif=$dateFormatConserved=null;
					if($tmpEvt->periodType=="weekDay" && in_array(date("N",$curBegin),$periodValues))												{$dateFormatModif="Y-m-d";	$dateFormatConserved=" H:i";}//jour de semaine
					elseif($tmpEvt->periodType=="month" && in_array(date("m",$curBegin),$periodValues) && date("d",$evtBegin)==date("d",$curBegin))	{$dateFormatModif="Y-m";	$dateFormatConserved="-d H:i";}//jour du mois
					elseif($tmpEvt->periodType=="year" && date("m-d",$evtBegin)==date("m-d",$curBegin))												{$dateFormatModif="Y";		$dateFormatConserved="-m-d H:i";}//jour de l'année
					//Reformate pour que le début/fin de l'evt corresponde à la date courante
					if(!empty($dateFormatModif) && !empty($dateFormatConserved))
					{
						$tmpEvt->dateBegin=date($dateFormatModif,$curBegin).date($dateFormatConserved,$evtBegin);
						$tmpEvt->dateEnd  =date($dateFormatModif,$curEnd).date($dateFormatConserved,$evtEnd);
						$evtBegin=strtotime($tmpEvt->dateBegin);
						$evtEnd=strtotime($tmpEvt->dateEnd);
						//Ajoute l'evt s'il est bien sur le créneau courant (cf. controles de créneaux horaires occupés..)
						if(static::evtInTimeSlot($evtBegin,$evtEnd,$curBegin,$curEnd))	{$evtListReturned[]=$tmpEvt;}
					}
				}
			}
		}
		return $evtListReturned;
	}

	/*
	 * Controle si un evt est sur une période/créneau donné
	 */
	public static function evtInTimeSlot($evtBegin, $evtEnd, $curBegin, $curEnd)
	{
		//Retourne true :  Début de l'evt dans le créneau  ||  Fin de l'evt dans le créneau  ||  evt avant et après le créneau
		return ($evtBegin>=$curBegin && $evtBegin<=$curEnd) || ($evtEnd>=$curBegin && $evtEnd<=$curEnd) || ($evtBegin<=$curBegin && $evtEnd>=$curEnd);
	}

	/*
	 * Droit de confirmer une proposition d'événement?
	 */
	public function proposedEventConfirmRight()
	{
		foreach(self::myCalendars() as $tmpCal){
			if($tmpCal->_id==$this->_id)  {return true;}
		}
	}

	/*
	 * Agendas visibles pour l'user courant
	 * => Agendas de ressource & Agenda de l'user courant & Agenda personnels affectés à l'user courant
	 */
	public static function visibleCalendars()
	{
		if(self::$_visibleCalendars===null)
		{
			//Init la sélection
			$sqlDisplayedObjects=self::sqlDisplayedObjects();
			//Récupère les agendas de ressource
			self::$_visibleCalendars=Db::getObjTab("calendar","SELECT DISTINCT * FROM ap_calendar WHERE type='ressource' AND ".$sqlDisplayedObjects);
			//Ajoute notre agenda perso && les agendas persos auquels on est affecté et qui sont "activés" (pas pour les guests)
			if(Ctrl::$curUser->isUser()){
				$personnalCals=Db::getObjTab("calendar","SELECT DISTINCT * FROM ap_calendar WHERE type='user' AND (_idUser=".Ctrl::$curUser->_id." OR ".$sqlDisplayedObjects.") AND _idUser NOT IN (select _id from ap_user where calendarDisabled=1)");
				self::$_visibleCalendars=array_merge(self::$_visibleCalendars,$personnalCals);
			}
			//Tri les agendas par leur nom
			self::$_visibleCalendars=self::sortCalendars(self::$_visibleCalendars);
		}
		return self::$_visibleCalendars;
	}

	/*
	 * Agendas gérés par l'user courant
	 * => Agenda de l'user courant & Agendas de ressource en accès total ("fullRight()")
	 */
	public static function myCalendars()
	{
		if(self::$_myCalendars===null){
			self::$_myCalendars=[];
			foreach(self::visibleCalendars() as $tmpCal){
				if($tmpCal->isMyPerso() || ($tmpCal->type=="ressource" && $tmpCal->fullRight()))  {self::$_myCalendars[]=$tmpCal;}
			}
		}
		return self::$_myCalendars;
	}

	/*
	 * Agendas sur lesquels l'user courant peut affecter ou proposer des événements
	 */
	public static function affectationCalendars()
	{
		if(self::$_affectationCalendars===null)
		{
			//// Ajoute les agendas accessibles en lecture
			self::$_affectationCalendars=self::visibleCalendars();
			//// Puis ajoute les agendas persos non accessibles en lecture : pour pouvoir faire les propositions d'événement (Pas dispo pour les "guest")  
			if(Ctrl::$curUser->isUser() && count(Ctrl::$curSpace->getUsers())>0)
			{
				//Ajoute les agendas qui ne sont pas encore dans la liste
				foreach(Db::getObjTab("calendar","SELECT DISTINCT * FROM ap_calendar WHERE type='user' AND _idUser IN (".Ctrl::$curSpace->getUsers("idsSql").") AND _idUser NOT IN (select _id from ap_user where calendarDisabled=1)") as $tmpCal){
					if(!in_array($tmpCal,self::$_affectationCalendars))  {self::$_affectationCalendars[]=$tmpCal;}
				}
				//Tri les agendas par leur nom
				self::$_affectationCalendars=self::sortCalendars(self::$_affectationCalendars);//Tri les agendas par leur nom
			}
		}
		return self::$_affectationCalendars;
	}

	/*
	 * Agendas Affichés actuellement
	 */
	public static function displayedCalendars($visibleCalendars)
	{
		//Init les agendas à retourner && Récup éventuellement les agendas enregistrés en préférence
		$displayedCalendars=[];
		$prefCalendars=Txt::txt2tab(Ctrl::prefUser("displayedCalendars"));
		//Récupère les agendas à afficher :  Agendas enregistrés en préférences  OU  Agenda de l'espace créé par défaut (_id=1)
		foreach($visibleCalendars as $tmpCal){
			if(in_array($tmpCal->_id,$prefCalendars)  || (empty($prefCalendars) && $tmpCal->_id==1 && $tmpCal->type=="ressource"))   {$displayedCalendars[]=$tmpCal;}
		}
		//Toujours pas d'agendas à afficher : on prend le premier des $visibleCalendars
		if(empty($displayedCalendars) && !empty($visibleCalendars))  {$displayedCalendars[]=$visibleCalendars[0];}
		//Supprime les evénements de plus de 3 ans (lancé en début de session)
		if(empty($_SESSION["calendarsCleanEvt"]))
		{
			//Période des evenements "old"
			$time100YearsAgo=time()-(86400*365*100);
			$time5YearsAgo=time()-(86400*365*5);
			//Sélectionne les agendas avec "editFullContentRight()"
			foreach($displayedCalendars as $tmpCal){
				if($tmpCal->editFullContentRight()){
					foreach($tmpCal->evtList($time100YearsAgo,$time5YearsAgo,2) as $tmpEvt){
						if($tmpEvt->isOldEvt($time5YearsAgo))  {$tmpEvt->delete();}//"isOldEvt()" : date de fin passé && sans périodicité ou périodicité terminé
					}
				}
			}
			$_SESSION["calendarsCleanEvt"]=true;
		}
		//Retour les agendas à afficher
		return $displayedCalendars;
	}

	/*
	 * Tri d'une liste d'objets calendriers : en fonction du type, puis du titre ET met l'agenda de l'user courant en 1er
	 */
	public static function sortCalendars($calendarsTab)
	{
		//Init le tri des agendas d'users : cf. "__construct()" ci-dessus
		$userCalendarSortField=(Ctrl::$agora->personsSort=="name")  ?  "userName"  :  "userFirstName";
		//Spécifie le champ de tri : affiche en premier les calendriers de Ressource, puis le perso, puis ceux des autres users
		foreach($calendarsTab as $tmpCal){
			if($tmpCal->type=="ressource")	{$tmpCal->sortField="A__".$tmpCal->title;}
			elseif($tmpCal->isMyPerso())	{$tmpCal->sortField="B__".$tmpCal->$userCalendarSortField;}
			else							{$tmpCal->sortField="C__".$tmpCal->$userCalendarSortField;}
		}
		//Tri des agendas : puis les agendas partagés -> mon agenda -> puis les autres agendas perso
		usort($calendarsTab,["self","sortCompareCalendars"]);
		return $calendarsTab;
	}
	//Comparaison binaire de caractere, mais insensible à la casse
	public static function sortCompareCalendars($obj1, $obj2){
		return strcasecmp($obj1->sortField, $obj2->sortField);
	}

	/*
	 * SURCHARGE : Menu contextuel
	 */
	public function contextMenu($options=null)
	{
		//Accès en édition : Export d'evenements (download/par mail) && Import d'événements
		if($this->editRight())
		{
			//init
			$urlExportIcs="?ctrl=calendar&action=exportEvents&targetObjId=".$this->_targetObjId;
			$icalUrlInput=Txt::trad("CALENDAR_icalUrl")." : <br><input id='urlIcal".$this->_targetObjId."' value=\"".Req::getSpaceUrl()."/?ctrl=misc&action=DisplayIcal&targetObjId=".$this->_targetObjId."&md5Id=".$this->md5Id()."\" style='width:250px;margin-top:5px;' readonly>";
			//Ajoute les options au menu
			$options["specificOptions"][]=["actionJs"=>"lightboxOpen('?ctrl=calendar&action=importEvents&targetObjId=".$this->_targetObjId."')",  "iconSrc"=>"calendar/icalImport.png",  "label"=>Txt::trad("CALENDAR_importIcal")];
			$options["specificOptions"][]=["actionJs"=>"if(confirm('".Txt::trad("confirm",true)."')) redir('".$urlExportIcs."')",  "iconSrc"=>"calendar/icalExport.png",  "label"=>Txt::trad("CALENDAR_exportIcal")];
			$options["specificOptions"][]=["actionJs"=>"if(confirm('".Txt::trad("confirm",true)."')) redir('".$urlExportIcs."&sendMail=true')",  "iconSrc"=>"mail.png",  "label"=>Txt::trad("CALENDAR_exportEvtMail"), "tooltip"=>Txt::trad("CALENDAR_exportEvtMailInfo")."<br>".Txt::trad("sendTo")." ".Ctrl::$curUser->mail];
			$options["specificOptions"][]=["actionJs"=>"$('#urlIcal".$this->_targetObjId."').select(); if(confirm('".Txt::trad("CALENDAR_icalUrlCopy",true)."')){document.execCommand('copy');}",  "iconSrc"=>"public.png",  "label"=>$icalUrlInput];
		}
		//Renvoie le menu surchargé
		return parent::contextMenu($options);
	}
}