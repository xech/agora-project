<?php
/**
* This file is part of the Agora-Project Software package
*
* @copyleft Agora-Project <https://www.agora-project.net>
* @license GNU General Public License (GPL-2.0)
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
	public static $isUserDelete=false;
	//Valeurs mises en cache
	private static $_readableCalendars=null;
	private static $_myCalendars=null;
	private static $_affectationCalendars=null;


	/********************************************************************************************************
	 * SURCHARGE : CONSTRUCTEUR
	 ********************************************************************************************************/
	function __construct($objIdOrValues=null)
	{
		parent::__construct($objIdOrValues);
		//Libellé de l'agenda perso
		if($this->type=="user"){
			$this->title=$this->autorLabel();
			$this->userName=Ctrl::getObj("user",$this->_idUser)->name;//Cf. "sortCalendars()"
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

	/********************************************************************************************************
	 * SURCHARGE : DROIT DE SUPPRIMER L'AGENDA POUR L'USER COURANT
	 ********************************************************************************************************/
	public function deleteRight()
	{
		//Droit lambda pour les agendas de ressource ou agendas perso via "MdlUser::delete()" (admin general)
		return ($this->type!="user" || $this::$isUserDelete==true)  ?  parent::deleteRight()  :  false;
	}

	/********************************************************************************************************
	 * SURCHARGE : SUPPRESSION D'AGENDA
	 ********************************************************************************************************/
	public function delete()
	{
		//Controle le droit d'accès
		if($this->deleteRight()){
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

	/********************************************************************************************************
	 * SURCHARGE : MENU CONTEXTUEL
	 ********************************************************************************************************/
	public function contextMenu($options=null)
	{
		////	Accès en lecture
		if($this->readRight()){
			////	Adresse de partage
			$actionJsTmp="$('#urlIcal".$this->_typeId."').show().select(); document.execCommand('copy'); $('#urlIcal".$this->_typeId."').hide(); notify('".Txt::trad("copyUrlNotif",true)."');";
			$labelTmp=Txt::trad("CALENDAR_icalUrl")."<input id='urlIcal".$this->_typeId."' value=\"".Req::getCurUrl()."/index.php?ctrl=misc&action=DisplayIcal&typeId=".$this->_typeId."&md5Id=".$this->md5Id()."\" style='display:none;'>";
			$options["specificOptions"][]=["actionJs"=>$actionJsTmp,  "iconSrc"=>"link.png",  "label"=>$labelTmp,  "tooltip"=>Txt::trad("CALENDAR_icalUrlCopy")];
			////	Export Ical des evts
			$options["specificOptions"][]=["actionJs"=>"confirmRedir('?ctrl=calendar&action=exportEvents&typeId=".$this->_typeId."','".Txt::trad("CALENDAR_exportIcal",true)."')",  "iconSrc"=>"dataImportExport.png",  "label"=>Txt::trad("CALENDAR_exportIcal")];
		}
		//// Import Ical des evts
		if($this->editContentRight()){
			$options["specificOptions"][]=["actionJs"=>"lightboxOpen('?ctrl=calendar&action=importEvents&typeId=".$this->_typeId."')",  "iconSrc"=>"dataImportExport.png",  "label"=>Txt::trad("CALENDAR_importIcal")];
		}
		//Renvoie le menu surchargé
		return parent::contextMenu($options);
	}

	/********************************************************************************************************
	 * VERIF SI C'EST L'AGENDA PARTAGE DE L'ESPACE COURANT
	 ********************************************************************************************************/
	public function isSpacelCalendar()
	{
		return ($this->type=="ressource" && ($this->_id==1 || $this->title==Ctrl::$curSpace->name));
	}

	/********************************************************************************************************
	 * VERIF SI C'EST L'AGENDA PERSO DE L'USER COURANT
	 ********************************************************************************************************/
	public function isMyPersonalCalendar()
	{
		return ($this->type=="user" && $this->isAutor());
	}

	/********************************************************************************************************
	 * LISTE D'EVENEMENTS SUR UNE DURÉE/PERIODE DONNEE (>= à un jour : cf. evt récurrents)
	 ********************************************************************************************************/
	public function evtList($durationBegin, $durationEnd, $accessRightMin, $categoryFilter=false, $pluginParams=false)
	{
		////	EVT AFFECTÉS À L'AGENDA COURANT ET CONFIRMÉS (INIT LA SELECTION)
		$sqlSelection="_id IN (SELECT _idEvt FROM ap_calendarEventAffectation WHERE _idCal=".$this->_id." AND confirmed=1)";
		////	EVT DANS LA DURÉE/PERIODE  (début d'evt dans la période || fin d'evt dans la période || evt avant et après la période)  &&  EVT RÉCURRENTS (evt commence avant la période && (pas de fin de récurrence || fin de récurrence après/pendant la période))
		$durationSelection=(!empty($durationBegin) && !empty($durationEnd));
		if($durationSelection==true){
			$sqlDurBegin	=Db::format(date("Y-m-d H:i:00",$durationBegin));
			$sqlDurEnd		=Db::format(date("Y-m-d H:i:59",$durationEnd));
			$sqlBeginEnd	='((dateBegin BETWEEN '.$sqlDurBegin.' AND '.$sqlDurEnd.') OR (dateEnd BETWEEN '.$sqlDurBegin.' AND '.$sqlDurEnd.') OR (dateBegin <= '.$sqlDurBegin.' AND dateEnd >= '.$sqlDurEnd.'))';
			$sqlRecurrent	='(periodType is not null AND dateBegin <= '.$sqlDurBegin.' AND (periodDateEnd IS NULL OR periodDateEnd >= '.$sqlDurBegin.'))';
			$sqlSelection.=" AND (".$sqlBeginEnd." OR ".$sqlRecurrent.") ";
		}
		////	EVT D'UNE CERTAINE CATEGORIE  ||  EVT DU PLUGIN (search/dashboard/shortcut)
		if(!empty($categoryFilter))		{$sqlSelection.=MdlCalendarCategory::sqlCategoryFilter();}
		elseif(!empty($pluginParams))	{$sqlSelection.=" AND ".MdlCalendarEvent::sqlPlugins($pluginParams);}
		////	RECUPERE LES EVTS && FILTRE EN FONCTION DU DROIT D'ACCÈS
		$eventList=Db::getObjTab("calendarEvent", "SELECT * FROM ap_calendarEvent WHERE ".$sqlSelection." ORDER BY dateBegin ASC, dateEnd DESC");//"dateEnd DESC" : récup les evts les plus long en 1er si 2 evt commencent en même tps (cf. display "week")
		foreach($eventList as $keyEvt=>$tmpEvt){
			if($tmpEvt->accessRight() < $accessRightMin)  {unset($eventList[$keyEvt]);}
		}
		////	AJOUTE LES RÉCURRENCES D'EVENEMENTS (CLONE)
		if($durationSelection==true){																							//Uniquement si sélection d'evt sur une durée donnée
			foreach($eventList as $keyEvt=>$tmpEvt){																			//Parcourt chaque evt
				if(!empty($tmpEvt->periodType)){																				//Vérif si l'evt est récurrent
					$tmpEvt->cloneNb=0;																							//Compteur de récurrence
					for($tmpDayBegin=$durationBegin; $tmpDayBegin<=$durationEnd; $tmpDayBegin+=86400){							//Parcourt chaque jour de la durée/période
						$tmpDayEnd=($tmpDayBegin+86399);																		//Fin du jour courant
						$evtInDuration=static::evtInDuration($tmpEvt,$tmpDayBegin,$tmpDayEnd);									//Zappe si la date courante correspond à la dateBegin/dateEnd de l'evt (evt de départ) 
						$evtExpired=(!empty($tmpEvt->periodDateEnd) && $tmpDayBegin > strtotime($tmpEvt->periodDateEnd));		//Zappe si la date courante est après "periodDateEnd"
						$evtNotStarted=($tmpDayEnd < strtotime($tmpEvt->dateBegin));											//Zappe si la date courante est avant le début de l'evt (cf. dateBegin de départ)
						$evtInExceptions=preg_match("/".date('Y-m-d',$tmpDayBegin)."/",(string)$tmpEvt->periodDateExceptions);	//Zappe si la date courante est dans les "periodDateExceptions"
						if($evtInDuration==false && $evtExpired==false && $evtNotStarted==false && empty($evtInExceptions)){	//Ajoute si besoin une récurrence pour le jour courant
							$dateReplace=null;
							$periodValues=Txt::txt2tab($tmpEvt->periodValues);
							if($tmpEvt->periodType=="weekDay" && in_array(date("N",$tmpDayBegin),$periodValues))															{$dateReplace="Y-m-d";}	//Remplace le jour
							elseif($tmpEvt->periodType=="month" && in_array(date("m",$tmpDayBegin),$periodValues) && date("d",$tmpEvt->timeBegin)==date("d",$tmpDayBegin))	{$dateReplace="Y-m";}	//Remplace le mois
							elseif($tmpEvt->periodType=="year" && date("m-d",$tmpEvt->timeBegin)==date("m-d",$tmpDayBegin))													{$dateReplace="Y";}		//Remplace l'année
							if(!empty($dateReplace)){
								$evtClone=clone $tmpEvt;																										//Clone l'evt de départ
								$evtClone->dateBegin=str_replace(date($dateReplace,$tmpEvt->timeBegin), date($dateReplace,$tmpDayBegin), $tmpEvt->dateBegin);	//Remplace le dateBegin par celle du jour courant
								$evtClone->dateEnd  =str_replace(date($dateReplace,$tmpEvt->timeEnd), date($dateReplace,$tmpDayEnd), $tmpEvt->dateEnd);			//Remplace le dateEnd
								$evtClone->timeBegin=strtotime($evtClone->dateBegin);																			//Remplace le timeBegin par celle du jour courant
								$evtClone->timeEnd=strtotime($evtClone->dateEnd);																				//Remplace le timeEnd
								$eventList[]=$evtClone;
								$tmpEvt->cloneNb++;
							}
						}
					}
					//Supprime l'evt si ya pas de récurrence et qu'il n'est pas sur la durée/période donnée
					if(empty($tmpEvt->cloneNb) && static::evtInDuration($tmpEvt,$durationBegin,$durationEnd)==false)  {unset($eventList[$keyEvt]);}
				}
			}
		}
		////	RENVOIE LES EVENEMENTS
		return $eventList;
	}

	/********************************************************************************************************
	 * FILTRE LES EVT POUR UNE JOURNEE DONNEE
	 ********************************************************************************************************/
	public static function dayEvtList($eventList, $durationBegin, $durationEnd)
	{
		$eventDayList=[];
		foreach($eventList as $tmpEvt){
			if(static::evtInDuration($tmpEvt, $durationBegin, $durationEnd))  {$eventDayList[]=clone $tmpEvt;}//clone : cf. evt sur plusieurs jours
		}
		return $eventDayList;
	}

	/****************************************************************************************************************************************************
	 * VERIF SI UN EVT SE TROUVE SUR UNE DURÉE/PERIODE DONNEE  (début d'evt dans la durée || fin d'evt dans la durée || evt avant et après la durée)
	 ****************************************************************************************************************************************************/
	public static function evtInDuration($evt, $durationBegin, $durationEnd)
	{
		return (($evt->timeBegin >= $durationBegin && $evt->timeBegin <= $durationEnd)  ||  ($evt->timeEnd >= $durationBegin && $evt->timeEnd <= $durationEnd)  ||  ($evt->timeBegin <= $durationBegin && $evt->timeEnd >= $durationEnd));
	}

	/********************************************************************************************************
	 * AGENDAS ACCESSIBLES EN LECTURE POUR L'USER COURANT
	 ********************************************************************************************************/
	public static function readableCalendars()
	{
		//Agendas de ressource  &&  Agendas personnels (enabled)  &&  Agenda de l'user
		if(self::$_readableCalendars===null){
			$sqlDisplay=self::sqlDisplay();
			$ressourceCals	=Db::getObjTab("calendar","SELECT DISTINCT * FROM ap_calendar WHERE type='ressource' AND ".$sqlDisplay);
			$persoCals		=Db::getObjTab("calendar","SELECT DISTINCT * FROM ap_calendar WHERE type='user' AND (".$sqlDisplay." OR _idUser=".Ctrl::$curUser->_id.") AND _idUser NOT IN (select _id from ap_user where calendarDisabled=1)");
			self::$_readableCalendars=self::sortCalendars(array_merge($ressourceCals,$persoCals));//Agendas triés via "sortCalendars()"
		}
		return self::$_readableCalendars;
	}

	/********************************************************************************************************
	 * AGENDAS ACCESSIBLES EN ECRITURE POUR L'USER COURANT
	 ********************************************************************************************************/
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

	/********************************************************************************************************
	 * AGENDAS ACCESSIBLES POUR AFFECTER/PROPOSER DES ÉVÉNEMENTS PAR L'USER COURANT
	 ********************************************************************************************************/
	public static function affectationCalendars()
	{
		if(self::$_affectationCalendars===null){
			$calendars=self::readableCalendars();
			//// Users : ajoute les agendas persos des users de l'espace courant et inaccessibles en lecture (cf propositions d'evt)
			if(Ctrl::$curUser->isUser()){
				$userCalendars=Db::getObjTab("calendar","SELECT DISTINCT * FROM `ap_calendar` WHERE `type`='user' AND _idUser IN (".Ctrl::$curSpace->getUsers("idsSql").") AND _idUser NOT IN (select _id from ap_user where calendarDisabled=1)");
				foreach($userCalendars as $_id=>$tmpCal){
					if(!in_array($tmpCal,$calendars))  {$calendars[]=$tmpCal;}
				}
			}
			//// Guests : vérif si l'agenda possède l'option "propositionGuest"
			else{																			
				foreach($calendars as $_id=>$tmpCal){
					if(empty($tmpCal->propositionGuest)) {unset($calendars[$_id]);}
				}
			}
			self::$_affectationCalendars=self::sortCalendars($calendars);//Tri les agendas
		}
		return self::$_affectationCalendars;
	}

	/********************************************************************************************************
	 * DROIT DE PROPOSER/AJOUTER UN EVT POUR L'AGENDA COURANT PAR L'USER COURANT
	 ********************************************************************************************************/
	public function affectationAddRight()
	{
		return in_array($this,self::affectationCalendars());
	}

	/********************************************************************************************************
	 * DROIT D'AJOUTER  UN AGENDA DE RESSOURCE (pas de type 'user')
	 ********************************************************************************************************/
	public static function addRight()
	{
		return (Ctrl::$curUser->isSpaceAdmin() || (Ctrl::$curUser->isUser() && Ctrl::$curSpace->moduleOptionEnabled(self::moduleName,"adminAddRessourceCalendar")==false));
	}

	/********************************************************************************************************
	 * AGENDAS ACTUELLEMENT AFFICHÉS
	 ********************************************************************************************************/
	public static function displayedCalendars($readableCalendars)
	{
		$displayedCalendars=[];
		//// Récupère chaque agenda enregistré en préférence
		$prefCalendars=Txt::txt2tab(Ctrl::prefUser("displayedCalendars"));
		if(!empty($prefCalendars)){
			foreach($readableCalendars as $tmpCal){
				if(in_array($tmpCal->_id,$prefCalendars))  {$displayedCalendars[]=$tmpCal;}
			}
		}
		//// Si aucun agenda en pref, on récupère l'agenda partagé de l'espace (en 1er) ou l'agenda perso de l'user courant
		if(empty($displayedCalendars)){
			foreach($readableCalendars as $tmpCal){
				if($tmpCal->isSpacelCalendar() || $tmpCal->isMyPersonalCalendar())  {$displayedCalendars[]=$tmpCal;  break;}
			}
		}
		//// Délestage des evts de + de 10 ans
		if(empty($_SESSION["calendarsCleanEvt"])){												//Lance en début de session
			$timeDeleteMin=time()-(TIME_1YEAR*30);												//30ans
			$timeDeleteMax=time()-(TIME_1YEAR*10);												//10ans
			foreach($displayedCalendars as $tmpCal){											//Sélectionne les agendas avec "editContentRight()"
				if($tmpCal->editContentRight()){												//Vérif si l'agenda est accessible en écriture
					foreach($tmpCal->evtList($timeDeleteMin, $timeDeleteMax, 1) as $tmpEvt){	//$accessRightMin=1
						if($tmpEvt->isOldEvt($timeDeleteMax))  {$tmpEvt->delete();}				//"isOldEvt()" : date de fin passé && sans périodicité ou périodicité terminé
					}
				}
			}
			$_SESSION["calendarsCleanEvt"]=true;
		}
		//// Retourne les agendas affichés
		return $displayedCalendars;
	}

	/********************************************************************************************************************
	 * LISTE D'AGENDAS TRIÉS :  AGENDA DE L'ESPACE COURANT  >  AGENDAS DE RESSOURCE  >  AGENDA PERSO  >  AGENDA D'USERS
	 ********************************************************************************************************************/
	public static function sortCalendars($calendarList)
	{
		foreach($calendarList as $tmpCal){
			if($tmpCal->isSpacelCalendar())			{$tmpCal->sortField="A__".$tmpCal->title;}
			elseif($tmpCal->type=="ressource")		{$tmpCal->sortField="B__".$tmpCal->title;}
			elseif($tmpCal->isMyPersonalCalendar())	{$tmpCal->sortField="C__".$tmpCal->title;}
			else									{$tmpCal->sortField="D__".$tmpCal->title;}
		}
		//Tri alphabetique sur le "sortField"
		usort($calendarList,function($objA,$objB){
			return strcmp($objA->sortField, $objB->sortField);
		});
		return $calendarList;
	}
}