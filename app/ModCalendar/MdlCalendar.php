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
	//Valeurs en cache
	private static $_readableCals=null;
	private static $_affectationCals=null;


	/********************************************************************************************************
	 * SURCHARGE : CONSTRUCTEUR
	 ********************************************************************************************************/
	function __construct($objIdOrValues=null)
	{
		parent::__construct($objIdOrValues);
		//Libellé de l'agenda perso
		if($this->type=="user")  {$this->title=$this->autorLabel();}
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
	 * VERIF SI C'EST L'AGENDA PARTAGE DE L'ESPACE COURANT
	 ********************************************************************************************************/
	public function isSpacelCalendar()
	{
		return ($this->type=="ressource" && ($this->_id==1 || $this->title==Ctrl::$curSpace->name));
	}

	/********************************************************************************************************
	 * VERIF SI C'EST L'AGENDA PERSONNEL DE L'USER COURANT
	 ********************************************************************************************************/
	public function isMyPersoCalendar()
	{
		return ($this->type=="user" && $this->isAutor());
	}

	/********************************************************************************************************
	 * VERIF SI C'EST AGENDA PRINCIPAL : ESPACE COURANT || PERSONNEL
	 ********************************************************************************************************/
	public function isMainCalendar()
	{
		return ($this->isSpacelCalendar() || $this->isMyPersoCalendar());
	}

	/********************************************************************************************************
	 * LISTE D'EVENEMENTS SUR UNE DURÉE/PERIODE DONNEE (>= à un jour : cf. evt récurrents)
	 ********************************************************************************************************/
	public function evtList($durationBegin, $durationEnd, $accessRightMin, $categoryFilter=false, $pluginParams=false)
	{
		////	EVT AFFECTÉS À L'AGENDA COURANT ET CONFIRMÉS (INIT LA SELECTION)
		$sqlSelection="_id IN (SELECT _idEvt FROM ap_calendarEventAffectation WHERE _idCal=".$this->_id." AND confirmed=1)";
		////	EVT DANS LA DURÉE/PERIODE  (début d'evt dans la période || fin d'evt dans la période || evt avant et après la période || evt récurrent : commence avant la période et (pas de fin de récurrence ou fin de récurrence après la période))
		$durationSelection=(!empty($durationBegin) && !empty($durationEnd));
		if($durationSelection==true){
			$sqlBegin =Db::format(date("Y-m-d H:i:00",$durationBegin));
			$sqlEnd	  =Db::format(date("Y-m-d H:i:59",$durationEnd));
			$sqlSelection.=" AND (  (dateBegin BETWEEN ".$sqlBegin." AND ".$sqlEnd.")  OR  (dateEnd BETWEEN ".$sqlBegin." AND ".$sqlEnd.")  OR  (dateBegin <= ".$sqlBegin." AND dateEnd >= ".$sqlEnd.")  OR  (periodType is not null AND dateBegin <= ".$sqlBegin." AND (periodDateEnd IS NULL OR periodDateEnd >= ".$sqlBegin."))  ) ";
		}
		////	EVT D'UNE CERTAINE CATEGORIE  ||  EVT DU PLUGIN (search/dashboard/shortcut)
		if(!empty($categoryFilter))		{$sqlSelection.=MdlCalendarCategory::sqlCategoryFilter();}
		elseif(!empty($pluginParams))	{$sqlSelection.=" AND ".MdlCalendarEvent::sqlPlugins($pluginParams);}
		////	RECUPERE LES EVTS FILTRÉS EN FONCTION DU DROIT D'ACCÈS
		$eventList=Db::getObjTab("calendarEvent", "SELECT * FROM ap_calendarEvent WHERE ".$sqlSelection." ORDER BY dateBegin ASC, dateEnd DESC");//"dateEnd DESC" : evts + long en 1er si 2 evt commencent en même tps (display "week")
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
		////	Agendas de ressource  &&  Agendas persos activés
		if(self::$_readableCals===null){
			$sqlDisplay=self::sqlDisplay();
			$ressourceCals	=Db::getObjTab("calendar","SELECT DISTINCT * FROM ap_calendar WHERE type='ressource' AND ".$sqlDisplay);
			$persoCals		=Db::getObjTab("calendar","SELECT DISTINCT * FROM ap_calendar WHERE type='user' AND (".$sqlDisplay." OR _idUser=".Ctrl::$curUser->_id.") AND _idUser NOT IN (select _id from ap_user where calendarDisabled=1)");
			self::$_readableCals=self::sort(array_merge($ressourceCals,$persoCals));
		}
		////	Delete les evts de + de 10ans (admin général)
		if(empty($_SESSION["calendarsCleanEvt"]) && Ctrl::$curUser->isGeneralAdmin()){
			$timeMin=strtotime("-50 year");
			$timeMax=strtotime("-10 year");
			$_SESSION["calendarsCleanEvt"]=true;
			foreach(self::$_readableCals as $tmpCal){
				if($tmpCal->editContentRight()){
					foreach($tmpCal->evtList($timeMin,$timeMax,1) as $tmpEvt){
						if($tmpEvt->isPastEvent($timeMax))  {$tmpEvt->delete();}
					}
				}
			}
		}
		////	Renvoi les agendas triés
		return self::sort(self::$_readableCals);
	}

	/********************************************************************************************************
	 * AGENDAS ACCESSIBLES POUR AFFECTER/PROPOSER DES ÉVÉNEMENTS PAR L'USER COURANT
	 ********************************************************************************************************/
	public static function affectationCalendars()
	{
		if(self::$_affectationCals===null){
			self::$_affectationCals=self::readableCalendars();
			////	Guests : vérif le "propositionGuest" de chaque agenda
			if(Ctrl::$curUser->isGuest()){																			
				foreach(self::$_affectationCals as $_id=>$tmpCal){
					if(empty($tmpCal->propositionGuest))  {unset(self::$_affectationCals[$_id]);}
				}
			}
			////	Users : ajoute les agendas des users de l'espace, inaccessibles en lecture (cf propositions d'evt)
			else{
				$userCalendars=Db::getObjTab("calendar","SELECT DISTINCT * FROM `ap_calendar` WHERE `type`='user' AND _idUser IN (".Ctrl::$curSpace->getUsers("idsSql").") AND _idUser NOT IN (select _id from ap_user where calendarDisabled=1)");
				foreach($userCalendars as $_id=>$tmpCal){
					if(!in_array($tmpCal,self::$_affectationCals))  {self::$_affectationCals[]=$tmpCal;}
				}
			}
			////	Tri à nouveau les agendas
			self::$_affectationCals=self::sort(self::$_affectationCals);
		}
		return self::$_affectationCals;
	}

	/********************************************************************************************************
	 * TRI DES AGENDAS PAR TYPE + TITLE
	 ********************************************************************************************************/
	public static function sort($calendarList)
	{
		////	Créé un "sortField" pour le tri
		foreach($calendarList as $tmpCal){
			if($tmpCal->isMainCalendar())	{$tmpCal->sortField="A__".$tmpCal->title;}	//Agenda principal
			elseif($tmpCal->type=="user")	{$tmpCal->sortField="B__".$tmpCal->title;}	//Agendas d'user
			else							{$tmpCal->sortField="C__".$tmpCal->title;}	//Agendas de ressource
		}
		////	Tri alphabetique via "sortField" ("strcmp()" : comparaison binaire de chaînes, sensible à la casse)
		usort($calendarList,function($objA,$objB){
			return strcmp($objA->sortField, $objB->sortField);
		});
		return $calendarList;
	}
}