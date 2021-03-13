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
	public static $requiredFields=array("title");
	public static $searchFields=array("title","description");
	public static $persoCalendarDeleteRight=false;//Droit de supprimer un agenda perso
	public static $displayModeOptions=array("month","week","workWeek","4Days","day");//Modes d'affichage des agendas
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
	 * EVENEMENTS DE L'AGENDA EN FONCTION D'UNE PÉRIODE (evt confirmés)
	 *******************************************************************************************/
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

	/*******************************************************************************************
	 * FILTRE DES EVTS POUR UNE JOURNÉE OU UN CRÉNEAU HORAIRE (cf: "actionTimeSlotBusy()")
	 * Note : les evts périodiques ne sont récupérés qu'une fois. On utilise donc cette fonction qui clone chaque occurence d'un même evenement, répété sur plusieurs jours.
	 *******************************************************************************************/
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

	/*******************************************************************************************
	 * VERIF : EVT SUR UNE PÉRIODE / UN CRÉNEAU DONNÉ ?
	 *******************************************************************************************/
	public static function evtInTimeSlot($evtBegin, $evtEnd, $curBegin, $curEnd)
	{
		//Retourne true si :  Début de l'evt dans le créneau  ||  Fin de l'evt dans le créneau  ||  evt avant et après le créneau
		return ($evtBegin>=$curBegin && $evtBegin<=$curEnd) || ($evtEnd>=$curBegin && $evtEnd<=$curEnd) || ($evtBegin<=$curBegin && $evtEnd>=$curEnd);
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
			$sqlDisplayedObjects=self::sqlDisplayedObjects();
			self::$_visibleCalendars=Db::getObjTab("calendar","SELECT DISTINCT * FROM ap_calendar WHERE type='ressource' AND ".$sqlDisplayedObjects);
			//Ajoute l'agenda perso de l'user courant et les agendas persos "activés" auquels on est affecté
			if(Ctrl::$curUser->isUser()){
				$personnalCals=Db::getObjTab("calendar","SELECT DISTINCT * FROM ap_calendar WHERE type='user' AND (_idUser=".Ctrl::$curUser->_id." OR ".$sqlDisplayedObjects.") AND _idUser NOT IN (select _id from ap_user where calendarDisabled=1)");
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