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
	public static $forceDeleteRight=false;//Force la suppression d'agenda perso : cf. "deleteRight()"
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
	 * LISTE D'EVENEMENTS SUR UNE DURÉE/PERIODE DONNEE (>= à un jour : cf. evt récurrents)
	 *******************************************************************************************/
	public function evtList($durationBegin, $durationEnd, $accessRightMin, $categoryFilter=false, $pluginParams=false)
	{
		////	EVT AFFECTÉS À L'AGENDA COURANT ET CONFIRMÉS (INIT LA SELECTION)
		$sqlSelection="_id IN (select _idEvt from ap_calendarEventAffectation where _idCal=".$this->_id." and confirmed=1)";									
		////	EVT DANS LA DURÉE/PERIODE  (début d'evt dans la durée || fin d'evt dans la durée || evt avant et après la durée)  &&  EVT RÉCURRENTS (début d'evt avant la durée && (fin de de l'evt null || après le début de la durée))
		$sqlDurBegin	=Db::format(date("Y-m-d H:i:00",$durationBegin));
		$sqlDurEnd		=Db::format(date("Y-m-d H:i:59",$durationEnd));
		$sqlBeginEnd	='((dateBegin BETWEEN '.$sqlDurBegin.' AND '.$sqlDurEnd.') OR (dateEnd BETWEEN '.$sqlDurBegin.' AND '.$sqlDurEnd.') OR (dateBegin <= '.$sqlDurBegin.' AND dateEnd >= '.$sqlDurEnd.'))';
		$sqlRecurrent	='(periodType is not null AND dateBegin <= '.$sqlDurBegin.' AND (periodDateEnd IS NULL OR periodDateEnd >= '.$sqlDurBegin.'))';
		$sqlSelection.=" AND (".$sqlBeginEnd." OR ".$sqlRecurrent.") ";
		////	EVT D'UNE CERTAINE CATEGORIE  ||  EVT DU PLUGIN (search/dashboard/shortcut)
		if(!empty($categoryFilter))		{$sqlSelection.=MdlCalendarCategory::sqlCategoryFilter();}
		elseif(!empty($pluginParams))	{$sqlSelection.=" AND ".MdlCalendarEvent::sqlPlugins($pluginParams);}
		////	RECUPERE LES EVTS  :  PUIS FILTRE EN FONCTION DU DROIT D'ACCÈS (0.5=créneau horaire, 1=lecture, 2=écriture)
		$eventList=Db::getObjTab("calendarEvent", "SELECT * FROM ap_calendarEvent WHERE ".$sqlSelection." ORDER BY dateBegin ASC, dateEnd DESC");//"dateEnd DESC" : récup les evts les plus long en 1er si 2 evt commencent en même tps (cf. display "week")
		foreach($eventList as $keyEvt=>$tmpEvt){
			if($tmpEvt->accessRight() < $accessRightMin)  {unset($eventList[$keyEvt]);}
		}
		////	AJOUTE LES RÉCURRENCES D'EVENEMENTS (CLONE)
		if(($durationEnd-$durationBegin)<3888000){																				//Uniquement si affichage < 45j (semaine/mois)
			foreach($eventList as $keyEvt=>$tmpEvt){																			//Parcourt chaque evt
				if(!empty($tmpEvt->periodType)){																				//Vérif si l'evt est récurrent
					$tmpEvt->cloneNb=0;																							//Compteur de récurrence
					for($tmpDayBegin=$durationBegin; $tmpDayBegin<=$durationEnd; $tmpDayBegin+=86400){							//Parcourt chaque jour de la durée/période
						$tmpDayEnd=($tmpDayBegin+86399);																		//Fin du jour courant
						$evtInDuration=static::evtInDuration($tmpEvt,$tmpDayBegin,$tmpDayEnd);									//Zappe si la date courante correspond à la dateBegin/dateEnd de l'evt (evt de départ) 
						$evtInExceptions=preg_match("/".date("Y-m-d",$tmpDayBegin)."/",(string)$tmpEvt->periodDateExceptions);	//Zappe si la date courante est dans les "periodDateExceptions"
						$evtExpired=(!empty($tmpEvt->periodDateEnd) && $tmpDayBegin > strtotime($tmpEvt->periodDateEnd));		//Zappe si la date courante est après "periodDateEnd"
						$evtNotStarted=($tmpDayEnd < strtotime($tmpEvt->dateBegin));											//Zappe si la date courante est avant le début de l'evt (cf. dateBegin de départ)
						if($evtInDuration==false && empty($evtInExceptions) && $evtExpired==false && $evtNotStarted==false){	//Ajoute si besoin une récurrence pour le jour courant
							$dateReplace=null;
							$periodValues=Txt::txt2tab($tmpEvt->periodValues);
							if($tmpEvt->periodType=="weekDay" && in_array(date("N",$tmpDayBegin),$periodValues))														{$dateReplace="Y-m-d";}	//Remplace le jour
							elseif($tmpEvt->periodType=="month" && in_array(date("m",$tmpDayBegin),$periodValues) && date("d",$tmpDayBegin)==date("d",$tmpDayBegin))	{$dateReplace="Y-m";}	//Remplace le mois
							elseif($tmpEvt->periodType=="year" && date("m-d",$tmpEvt->timeBegin)==date("m-d",$tmpDayBegin))												{$dateReplace="Y";}		//Remplace l'année
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

	/*******************************************************************************************
	 * FILTRE LES EVT POUR UNE JOURNEE DONNEE
	 *******************************************************************************************/
	public static function evtListDay($eventList, $durationBegin, $durationEnd)
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

	/*******************************************************************************************
	 * AGENDAS ACCESSIBLES EN LECTURE A L'USER COURANT
	 *******************************************************************************************/
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
			//Ajoute les agendas persos inaccessibles en lecture, pour les propositions d'événement (sauf "guest")
			if(Ctrl::$curUser->isUser()){
				$otherPersoCalendars=Db::getObjTab("calendar", "SELECT DISTINCT * FROM ap_calendar WHERE type='user' AND _idUser IN (".Ctrl::$curSpace->getUsers("idsSql").") AND _idUser NOT IN (select _id from ap_user where calendarDisabled=1)");
				foreach($otherPersoCalendars as $tmpCal){
					if(!in_array($tmpCal,self::$_affectationCalendars))  {self::$_affectationCalendars[]=$tmpCal;}//Ajoute l'agenda?
				}
				self::$_affectationCalendars=self::sortCalendars(self::$_affectationCalendars);//Liste des agendas triée
			}
		}
		return self::$_affectationCalendars;
	}

	/*******************************************************************************************
	 * AGENDAS ACTUELLEMENT AFFICHÉS
	 *******************************************************************************************/
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
				if($tmpCal->isSpacelCalendar() || $tmpCal->isPersonalCalendar())  {$displayedCalendars[]=$tmpCal;  break;}
			}
		}
		//// Supprime les evénements de plus de 4 ans (lancé en début de session)
		if(empty($_SESSION["calendarsCleanEvt"])){
			$time20YearsAgo =time()-(86400*365*20);											//Time 20ans
			$time4YearsAgo =time()-(86400*365*4);											//Time 4ans
			foreach($displayedCalendars as $tmpCal){										//Sélectionne les agendas avec "editContentRight()"
				if($tmpCal->editContentRight()){											//Vérif si l'agenda est accessible en écriture
					foreach($tmpCal->evtList($time20YearsAgo,$time4YearsAgo,1) as $tmpEvt){	//Params : $accessRightMin=1
						if($tmpEvt->isOldEvt($time4YearsAgo))  {$tmpEvt->delete();}			//"isOldEvt()" : date de fin passé && sans périodicité ou périodicité terminé
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
			elseif($tmpCal->isPersonalCalendar())	{$tmpCal->sortField="C__".$tmpCal->title;}
			else									{$tmpCal->sortField="D__".$tmpCal->title;}
		}
		//Tri alphabetique sur le "sortField"
		usort($calendarList,function($objA,$objB){
			return strcmp($objA->sortField, $objB->sortField);
		});
		return $calendarList;
	}

	/*******************************************************************************************
	 * SURCHARGE : MENU CONTEXTUEL
	 *******************************************************************************************/
	public function contextMenu($options=null)
	{
		////	Accès en lecture
		if($this->readRight()){
			////	Adresse de partage
			$actionJsTmp="$('#urlIcal".$this->_typeId."').show().select(); document.execCommand('copy'); $('#urlIcal".$this->_typeId."').hide(); notify('".Txt::trad("copyUrlConfirmed",true)."');";
			$labelTmp=Txt::trad("CALENDAR_icalUrl")."<input id='urlIcal".$this->_typeId."' value=\"".Req::getCurUrl()."/index.php?ctrl=misc&action=DisplayIcal&typeId=".$this->_typeId."&md5Id=".$this->md5Id()."\" style='display:none;'>";
			$options["specificOptions"][]=["actionJs"=>$actionJsTmp,  "iconSrc"=>"link.png",  "label"=>$labelTmp,  "tooltip"=>Txt::trad("CALENDAR_icalUrlCopy")];
			////	Export Ical des evts
			$options["specificOptions"][]=["actionJs"=>"if(confirm('".Txt::trad("confirm",true)."')) redir('?ctrl=calendar&action=exportEvents&typeId=".$this->_typeId."')",  "iconSrc"=>"dataImportExport.png",  "label"=>Txt::trad("CALENDAR_exportIcal")];
		}
		//// Import Ical des evts
		if($this->editContentRight()){
			$options["specificOptions"][]=["actionJs"=>"lightboxOpen('?ctrl=calendar&action=importEvents&typeId=".$this->_typeId."')",  "iconSrc"=>"dataImportExport.png",  "label"=>Txt::trad("CALENDAR_importIcal")];
		}
		//Renvoie le menu surchargé
		return parent::contextMenu($options);
	}
}