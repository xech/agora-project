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
	public static $persoCalendarDeleteRight=false;							//Droit de supprimer un agenda perso
	//Valeurs mises en cache
	private $_calendarOwnerIdUsers=null;
	private static $_visibleCalendars=null;
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

	/*******************************************************************************************
	 * VERIF : DROIT D'AJOUTER UN AGENDA DE RESSOURCE (ne concerne pas les agenda de type 'user')
	 *******************************************************************************************/
	public static function addRight()
	{
		return (Ctrl::$curUser->isAdminSpace() || (Ctrl::$curUser->isUser() && Ctrl::$curSpace->moduleOptionEnabled(self::moduleName,"adminAddRessourceCalendar")==false));
	}

	/*******************************************************************************************
	 * VERIF : DROIT D'AJOUTER OU PROPOSER UN ÉVÉNEMENT ("true" pour tous les users && pour les guests si l'option "propositionGuest" de l'agenda est activé)
	 *******************************************************************************************/
	public function addOrProposeEvt()
	{
		return (Ctrl::$curUser->isUser() || !empty($this->propositionGuest));
	}

	/*******************************************************************************************
	 * SURCHARGE : DROIT DE SUPPRESSION D'UN AGENDA (pas pour les agendas d'users)
	 *******************************************************************************************/
	public function deleteRight()
	{
		return ($this->type=="user" && $this::$persoCalendarDeleteRight==false)  ?  false  :  parent::deleteRight();
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
	 * LISTE DES EVENEMENTS CONFIRMÉS DE L'AGENDA
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
		//// Liste des evenements confirmés et affectés à l'agenda
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

	/***************************************************************************************************************************************************************
	 * VERIF SI L'EVENEMENT SE TROUVE SUR LA PÉRIODE SELECTIONNEE (début de l'evt dans la période || fin de l'evt dans la période || evt avant et après la période)
	 ***************************************************************************************************************************************************************/
	public static function evtInTimeSlot($evtBegin, $evtEnd, $periodBegin, $periodEnd)
	{
		return ( ($periodBegin<=$evtBegin && $evtBegin<=$periodEnd) || ($periodBegin<=$evtEnd && $evtEnd<=$periodEnd) || ($evtBegin<=$periodBegin && $periodEnd<=$evtEnd) );
	}

	/*******************************************************************************************
	 * VERIF : AGENDA PERSONNEL DE L'USER COURANT
	 *******************************************************************************************/
	public function curUserPerso()
	{
		return ($this->type=="user" && $this->isAutor());
	}

	/*******************************************************************************************
	 * VERIF : AGENDA GÉRÉ OU CREE PAR L'USER COURANT (confirmation de proposition d'événement ou autre)
	 *******************************************************************************************/
	public function curUserProperty()
	{
		return in_array(Ctrl::$curUser->_id, $this->calendarOwnerIdUsers());
	}

	/*******************************************************************************************
	 * LISTE DES USERS GESTIONNAIRES OU AUTEUR DE L'AGENDA (gestionnaires : users affectées explicitement en écriture à un agenda de "ressource". Ne pas se baser sur les droits d'accès, pour pas inclure "Tous les utilisateurs"..)
	 *******************************************************************************************/
	public function calendarOwnerIdUsers()
	{
		if($this->_calendarOwnerIdUsers===null){
			//Sélection SQL : Auteur de l'Agenda  &&  Users affectés en écriture (type "ressource" uniquement)
			$sqlSelect="_id IN (SELECT _idUser as _id FROM ap_calendar WHERE _id=".$this->_id.")";
			if($this->type=="ressource")  {$sqlSelect.=" OR _id IN (SELECT replace(target,'U','') as _id FROM ap_objectTarget WHERE objectType='calendar' AND _idObject=".$this->_id." AND target like 'U%' AND accessRight=2)";}
			$this->_calendarOwnerIdUsers=Db::getCol("SELECT DISTINCT _id FROM ap_user WHERE ".$sqlSelect);
		}
		return $this->_calendarOwnerIdUsers;
	}

	/*******************************************************************************************
	 * LISTE D'AGENDAS : AGENDAS VISIBLES POUR L'USER COURANT  (Agendas de ressource + Agenda de l'user courant + Agendas persos affectés à l'user courant)
	 *******************************************************************************************/
	public static function visibleCalendars()
	{
		if(self::$_visibleCalendars===null)
		{
			//Récupère les agendas de ressource
			$sqlDisplay=self::sqlDisplay();
			self::$_visibleCalendars=Db::getObjTab("calendar","SELECT DISTINCT * FROM ap_calendar WHERE type='ressource' AND ".$sqlDisplay);
			//Ajoute l'agenda perso de l'user courant et les agendas persos "activés" auquels on est affecté
			if(Ctrl::$curUser->isUser()){
				$personnalCals=Db::getObjTab("calendar","SELECT DISTINCT * FROM ap_calendar WHERE type='user' AND (_idUser=".Ctrl::$curUser->_id." OR ".$sqlDisplay.") AND _idUser NOT IN (select _id from ap_user where calendarDisabled=1)");
				self::$_visibleCalendars=array_merge(self::$_visibleCalendars,$personnalCals);
			}
			//Tri les agendas par leur nom
			self::$_visibleCalendars=self::sortCalendars(self::$_visibleCalendars);
		}
		return self::$_visibleCalendars;
	}

	/*******************************************************************************************
	 * LISTE D'AGENDAS : AGENDAS GÉRÉS OU PROPRIÉTÉ DE L'USER COURANT
	 *******************************************************************************************/
	public static function curUserCalendars()
	{
		if(self::$_myCalendars===null){
			self::$_myCalendars=[];
			foreach(self::visibleCalendars() as $tmpCal){
				if($tmpCal->curUserProperty())  {self::$_myCalendars[]=$tmpCal;}
			}
		}
		return self::$_myCalendars;
	}

	/*******************************************************************************************
	 * LISTE D'AGENDAS : AGENDAS SUR LESQUELS L'USER COURANT PEUT AFFECTER OU PROPOSER DES ÉVÉNEMENTS
	 *******************************************************************************************/
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

	/*******************************************************************************************
	 * LISTE D'AGENDAS : AGENDAS AFFICHÉS ACTUELLEMENT
	 *******************************************************************************************/
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
			//Sélectionne les agendas avec "editContentRight()"
			foreach($displayedCalendars as $tmpCal){
				if($tmpCal->editContentRight()){
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

	/*******************************************************************************************
	 * TRI UNE LISTE D'AGENDAS  (Agenda de l'user courant >> Puis agendas de ressource >> Enfin les agendas des autres users)
	 *******************************************************************************************/
	public static function sortCalendars($calendarsTab)
	{
		//Prépare le tri en fonction du champs spécifique "sortField"
		$userSortField=(Ctrl::$agora->personsSort=="name")  ?  "userName"  :  "userFirstName";//Tri des agendas persos en fonction du nom ou du prénom (cf. "__construct()" ci-dessus)
		foreach($calendarsTab as $tmpCal){
			if($tmpCal->curUserPerso())			{$tmpCal->sortField="A__".$tmpCal->$userSortField;}
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
		//Accès en édition : Export d'evenements (download/par mail) && Import d'événements
		if($this->editRight())
		{
			//Import/Export au format ICAL
			$options["specificOptions"][]=["actionJs"=>"lightboxOpen('?ctrl=calendar&action=importEvents&targetObjId=".$this->_targetObjId."')",  "iconSrc"=>"calendar/icalImport.png",  "label"=>Txt::trad("CALENDAR_importIcal")];
			$options["specificOptions"][]=["actionJs"=>"if(confirm('".Txt::trad("confirm",true)."')) redir('?ctrl=calendar&action=exportEvents&targetObjId=".$this->_targetObjId."')",  "iconSrc"=>"calendar/icalExport.png",  "label"=>Txt::trad("CALENDAR_exportIcal")];
			//Copie d'adresse d'accès externe Ical
			$actionJsTmp="$('#urlIcal".$this->_targetObjId."').show().select(); document.execCommand('copy'); $('#urlIcal".$this->_targetObjId."').hide(); notify('".Txt::trad("copyUrlConfirmed",true)."');";
			$labelTmp=Txt::trad("CALENDAR_icalUrl")."<input id='urlIcal".$this->_targetObjId."' value=\"".Req::getCurUrl()."/?ctrl=misc&action=DisplayIcal&targetObjId=".$this->_targetObjId."&md5Id=".$this->md5Id()."\" style='display:none;'>";
			$options["specificOptions"][]=["actionJs"=>$actionJsTmp,  "iconSrc"=>"link.png",  "label"=>$labelTmp,  "tooltip"=>Txt::trad("CALENDAR_icalUrlCopy")];
		}
		//Renvoie le menu surchargé
		return parent::contextMenu($options);
	}
}