<?php
/**
* This file is part of the Agora-Project Software package.
*
* @copyright (c) Agora-Project Limited <https://www.agora-project.net>
* @license GNU General Public License, version 2 (GPL-2.0)
*/


/*
 * Controleur du module "Calendar"
 */
class CtrlCalendar extends Ctrl
{
	const moduleName="calendar";
	public static $moduleOptions=["createSpaceCalendar","adminAddRessourceCalendar","adminAddCategory"];
	public static $MdlObjects=array("MdlCalendar","MdlCalendarEvent");

	/*
	 * ACTION PAR DEFAUT
	 */
	public static function actionDefault()
	{
		////	AGENDAS VISIBLE POUR L'USER (OU TOUS LES AGENDAS : "affectationCalendars()" SI ADMIN GENERAL)  &&  AGENDAS AFFICHES  &&  EVT PROPOSÉS
		if(empty($_SESSION["displayAllCals"]) || Req::isParam("displayAllCals"))	{$_SESSION["displayAllCals"]=(Req::getParam("displayAllCals")==1 && Ctrl::$curUser->isAdminGeneral())  ?  true  :  false;}
		$vDatas["visibleCalendars"]=($_SESSION["displayAllCals"]==true)  ?  MdlCalendar::affectationCalendars()  :  MdlCalendar::visibleCalendars();
		$vDatas["displayedCalendars"]=MdlCalendar::displayedCalendars($vDatas["visibleCalendars"]);
		////	MODE D'AFFICHAGE (month, week, workWeek, 4Days, day)  &  TEMPS DE RÉFÉRENCE ("curTime")  &  JOURS FÉRIÉS ("celebrationDays")
		$displayMode=self::prefUser("calendarDisplayMode","displayMode");
		if(empty($displayMode))  {$displayMode=(Req::isMobile()) ? "4Days":"month";}//affichage par défaut
		$vDatas["displayMode"]=$displayMode;
		$vDatas["curTime"]=$curTime=(Req::isParam("curTime")) ? Req::getParam("curTime") : time();
		$vDatas["celebrationDays"]=Trad::celebrationDays(date("Y",$curTime));
		////	AFFICHAGE : PREPARE LES TIMES/DATES
		//AFFICHAGE MOIS
		if($displayMode=="month"){
			$vDatas["timeBegin"]	=strtotime(date("Y-m",$curTime)."-01 00:00");
			$vDatas["timeEnd"]		=strtotime(date("Y-m",$curTime)."-".date("t",$curTime)." 23:59");
			$vDatas["urlTimePrev"]	=strtotime("-1 month",$curTime);
			$vDatas["urlTimeNext"]	=strtotime("+1 month",$curTime);
			$vDatas["timeDisplayedBegin"]=strtotime("-".(date("N",$vDatas["timeBegin"])-1)." days", $vDatas["timeBegin"]);//On commence l'affichage par un lundi : si le mois commence un mercredi, on affiche aussi le lundi/mardi du mois précédent
			$vDatas["timeDisplayedEnd"]=strtotime("+".(7-date("N",$vDatas["timeEnd"]))." days", $vDatas["timeEnd"]);	  //On termine l'affichage par un dimanche (Idem)
		}
		//AFFICHAGE SEMAINE / SEMAINE DE TRAVAIL
		elseif(preg_match("/week/i",$displayMode)){
			$weekTimeBegin=strtotime("-".(date("N",$curTime)-1)." days",$curTime);//lundi=0 => dimanche=6
			$weekTimeEnd=($displayMode=="week") ? strtotime("+6 days",$weekTimeBegin) : strtotime("+4 days",$weekTimeBegin);
			$vDatas["timeBegin"]	=strtotime(date("Y-m-d",$weekTimeBegin)." 00:00");
			$vDatas["timeEnd"]		=strtotime(date("Y-m-d",$weekTimeEnd)." 23:59");
			$vDatas["urlTimePrev"]	=strtotime("-1 week",$curTime);
			$vDatas["urlTimeNext"]	=strtotime("+1 week",$curTime);
		}
		//AFFICHAGE 4 PROCHAINS JOURS
		elseif($displayMode=="4Days"){
			$vDatas["timeBegin"]	=strtotime(date("Y-m-d",$curTime)." 00:00");
			$vDatas["timeEnd"]		=$vDatas["timeBegin"]+345599;//345599 = 4 jours moins une seconde
			$vDatas["urlTimePrev"]	=strtotime("-4 day",$curTime);
			$vDatas["urlTimeNext"]	=strtotime("+4 day",$curTime);
		}
		//AFFICHAGE JOUR
		elseif($displayMode=="day"){
			$vDatas["timeBegin"]	=strtotime(date("Y-m-d",$curTime)." 00:00");
			$vDatas["timeEnd"]		=strtotime(date("Y-m-d",$curTime)." 23:59");
			$vDatas["urlTimePrev"]	=strtotime("-1 day",$curTime);
			$vDatas["urlTimeNext"]	=strtotime("+1 day",$curTime);
		}
		////	FILTRE DES CATEGORIES DANS LES LIENS "urlTimePrev" et "urlTimeNext" ?
		$vDatas["urlCatFilter"]=(Req::isParam("_idCatFilter"))  ?  "&_idCatFilter=".Req::getParam("_idCatFilter")  :  null;
		////	LABEL DU MOIS AFFICHÉ : AFFICHAGE MOBILE OU  NORMAL
		$monthTime=$vDatas["timeBegin"];//Début de période comme référence
		if(Req::isMobile())	{$vDatas["labelMonth"]=(date("Y")==date("Y",$monthTime))  ?  Txt::formatime("%B",$monthTime)  :  Txt::formatime("%b %Y",$monthTime);}//"Janvier" OU "Janvier 2018" (si affiche une autre année)
		else				{$vDatas["labelMonth"]=(date("Ym",$vDatas["timeBegin"])==date("Ym",$vDatas["timeEnd"]))  ?  Txt::formatime("%B %Y",$monthTime)  :  Txt::formatime("%b %Y",$vDatas["timeBegin"])." / ".Txt::formatime("%b %Y",$vDatas["timeEnd"]);}//"Janvier 2020" OU "Janv. 2020 / fev. 2020" (si on affiche une semaine sur 2 mois)
		////	MENU POUR CHANGER D'ANNÉE ET DE MOIS
		$vDatas["calMonthPeriodMenu"]=null;
		for($tmpMonth=1; $tmpMonth<=12; $tmpMonth++){
			$tmpMonthTime=strtotime(date("Y",$curTime)."-".($tmpMonth>9?$tmpMonth:"0".$tmpMonth)."-01");
			$vDatas["calMonthPeriodMenu"].="<a onClick=\"redir('?ctrl=calendar&curTime=".$tmpMonthTime."')\" ".(date("Y-m",$curTime)==date("Y-m",$tmpMonthTime)?"class='sLinkSelect'":null).">".Txt::formatime("%B",$tmpMonthTime)."</a>";
		}
		$vDatas["calMonthPeriodMenu"].="<hr>";
		for($tmpYear=date("Y")-3; $tmpYear<=date("Y")+5; $tmpYear++){
			$tmpYearTime=strtotime($tmpYear."-".date("m",$curTime)."-01");
			$vDatas["calMonthPeriodMenu"].="<a onClick=\"redir('?ctrl=calendar&curTime=".$tmpYearTime."')\" ".(date("Y",$curTime)==$tmpYear?"class='sLinkSelect'":null).">".$tmpYear."</a>";
		}
		////	LISTE LES JOURS À AFFICHER
		$vDatas["periodDays"]=[];
		if(empty($vDatas["timeDisplayedBegin"]))	{$vDatas["timeDisplayedBegin"]=$vDatas["timeBegin"];  $vDatas["timeDisplayedEnd"]=$vDatas["timeEnd"];}
		for($timeDay=$vDatas["timeDisplayedBegin"]+43200; $timeDay<=$vDatas["timeDisplayedEnd"]; $timeDay+=86400)//43200sec de décalage (cf. heures d'été/hiver)
		{
			//Date et Timestamp de début/fin du jour
			$tmpDay["date"]=date("Y-m-d",$timeDay);
			$tmpDay["timeBegin"]=strtotime(date("Y-m-d",$timeDay)." 00:00");
			$tmpDay["timeEnd"]=strtotime(date("Y-m-d",$timeDay)." 23:59");
			//Libelle d'un jour ferie ?
			$tmpDay["celebrationDay"]=(array_key_exists(date("Y-m-d",$timeDay),$vDatas["celebrationDays"]))  ?  $vDatas["celebrationDays"][date("Y-m-d",$timeDay)]  :  null;
			//Ajoute le jour à la liste
			$vDatas["periodDays"][]=$tmpDay;
		}
		////	RECUPERE LA VUE "MONTH"/"WEEK" DE CHAQUE AGENDA
		foreach($vDatas["displayedCalendars"] as $cptCal=>$tmpCal)
		{
			//Label d'ajout/proposition d'événement
			if($tmpCal->editContentRight())			{$tmpCal->addEventLabel=Txt::trad("CALENDAR_addEvtTooltip");}
			elseif($tmpCal->addProposeEvtRight())	{$tmpCal->addEventLabel=Txt::trad("CALENDAR_proposeEvtTooltip");}
			else									{$tmpCal->addEventLabel=null;}
			//EVENEMENTS POUR CHAQUE JOUR
			$tmpCal->eventList=[];
			$tmpCal->displayedPeriodEvtList=$tmpCal->evtList($vDatas["timeDisplayedBegin"],$vDatas["timeDisplayedEnd"]);//Récupère les evts de toute la période affichée
			foreach($vDatas["periodDays"] as $dayCpt=>$tmpDay)
			{
				//EVENEMENTS DU JOUR COURANT
				$tmpCal->eventList[$tmpDay["date"]]=[];
				$tmpCalDayEvtList=MdlCalendar::periodEvts($tmpCal->displayedPeriodEvtList,$tmpDay["timeBegin"],$tmpDay["timeEnd"]);//Récupère uniquement les evts de la journée
				foreach($tmpCalDayEvtList as $tmpEvt)
				{
					//Evt hors catégorie?
					if(Req::isParam("_idCatFilter") && $tmpEvt->_idCat!=Req::getParam("_idCatFilter"))  {continue;}
					//Element pour l'affichage "semaine"/"jour"
					if($displayMode!="month")
					{
						//Duree / Hauteur à afficher pour l'evt
						$tmpEvt->dayCpt=$dayCpt;
						$evtTimeBegin=strtotime($tmpEvt->dateBegin);
						$evtTimeEnd=strtotime($tmpEvt->dateEnd);
						$evtBeforeTmpDay=($evtTimeBegin < $tmpDay["timeBegin"]);//Evt commence avant le jour courant ?
						$evtAfterTmpDay=($evtTimeEnd > $tmpDay["timeEnd"]);		//Evt termine après le jour courant?
						if($evtBeforeTmpDay==true && $evtAfterTmpDay==true)	{$tmpEvt->durationMinutes=24*60;}									//Affiche toute la journée
						elseif($evtBeforeTmpDay==true)						{$tmpEvt->durationMinutes=($evtTimeEnd-$tmpDay["timeBegin"])/60;}	//Affiche l'evt depuis 0h00 du jour courant
						elseif($evtAfterTmpDay==true)						{$tmpEvt->durationMinutes=($tmpDay["timeEnd"]-$evtTimeBegin)/60;}	//Affiche l'evt jusqu'à 23h59 du jour courant
						else												{$tmpEvt->durationMinutes=($evtTimeEnd-$evtTimeBegin)/60;}			//Affichage normal (au cour de la journée)
						//Heure/Minutes de début d'affichage ("evtBeforeDayBegin" si l'evt a commencé avant le jour affiché)
						$tmpEvt->minutesFromDayBegin=($evtTimeBegin>$tmpDay["timeBegin"])  ?  (($evtTimeBegin-$tmpDay["timeBegin"])/60)  :  "evtBeforeDayBegin";
					}
					//Ajoute l'evt à la liste
					$tmpCal->eventList[$tmpDay["date"]][]=$tmpEvt;
				}
			}
			//Récupère la vue
			$tmpCal->isFirstCal=($cptCal==0);
			$vCalDatas=$vDatas;
			$vCalDatas["tmpCal"]=$tmpCal;
			$calendarVue=($displayMode=="month")?"VueCalendarMonth.php":"VueCalendarWeek.php";
			$tmpCal->calendarVue=self::getVue(Req::getCurModPath().$calendarVue, $vCalDatas);
		}
		////	SYNTHESE DES AGENDAS (SI + D'UN AGENDA)
		if(count($vDatas["displayedCalendars"])>1 && !Req::isMobile())
		{
			//Jours à afficher pour la synthese
			$vDatas["periodDaysSynthese"]=[];
			foreach($vDatas["periodDays"] as $tmpDay)
			{
				//affichage "month" & jour d'un autre mois : passe le jour
				if($displayMode=="month" && date("m",$tmpDay["timeBegin"])!=date("m",$curTime))	{continue;}
				//Evénements de chaque agenda pour le $tmpDay 
				$nbCalsOccuppied=0;
				$tmpDay["calsEvts"]=[];
				foreach($vDatas["displayedCalendars"] as $tmpCal){
					$tmpDay["calsEvts"][$tmpCal->_id]=MdlCalendar::periodEvts($tmpCal->displayedPeriodEvtList,$tmpDay["timeBegin"],$tmpDay["timeEnd"]);//Récupère uniquement les evts de la journée
					if(!empty($tmpDay["calsEvts"][$tmpCal->_id]))	{$nbCalsOccuppied++;}
				}
				//Tooltip de synthese si au moins un agenda possède un événement à cette date
				$tmpDay["nbCalsOccuppied"]=(!empty($nbCalsOccuppied))  ?  Txt::displayDate($tmpDay["timeBegin"],"full")." :<br>".Txt::trad("CALENDAR_calendarsPercentBusy")." : ".$nbCalsOccuppied." / ".count($tmpDay["calsEvts"])  :  null;
				//Ajoute le jour
				$vDatas["periodDaysSynthese"][]=$tmpDay;
			}
		}
		////	LANCE L'AFFICHAGE
		static::displayPage("VueIndex.php",$vDatas);
	}

	/*
	 * Agenda courant est affiché?
	 */
	public static function isDisplayedCal($displayedCalendars, $curCal)
	{
		foreach($displayedCalendars as $tmpCal){
			if($tmpCal->_id==$curCal->_id)  {return true;}
		}
	}

	/*
	 * PLUGINS
	 */
	public static function plugin($pluginParams)
	{
		$pluginsList=$eventList=[];
		if(preg_match("/search|dashboard/i",$pluginParams["type"]))
		{
			//"Mes agendas" si on est sur le dashboard / "Agendas visibles" si c'est la recherche
			$visibleCalendars=($pluginParams["type"]=="dashboard") ? MdlCalendar::myCalendars() : MdlCalendar::visibleCalendars();
			if(!empty($visibleCalendars))
			{
				////	Affichage "dashboard"
				if($pluginParams["type"]=="dashboard")
				{
					//Evenements à confirmer
					$menuProposedEvents=self::menuProposedEvents();
					if(!empty($menuProposedEvents)){
						$objMenuConfirm=new stdClass();
						$objMenuConfirm->pluginModule=self::moduleName;
						$objMenuConfirm->pluginSpecificMenu=$menuProposedEvents;
						$pluginsList[]=$objMenuConfirm;
					}
					//Evénements courants
					foreach($visibleCalendars as $tmpCal)
					{
						//Tous les Evt avec accessRight>=1, trié par date (et non par H:M)
						$tmpCalEvtList=$tmpCal->evtList(null,null,1,false);
						//Filtre des Evts pour une période de plusieurs jours
						$tmpCalEvtListFull=[];
						$dateTimeBegin=strtotime($pluginParams["dateTimeBegin"])+43200;//cf. heures d'été/hiver
						$dateTimeEnd=strtotime($pluginParams["dateTimeEnd"]);
						//Récupère les evt pour chaque jour de la période
						for($timeDay=$dateTimeBegin; $timeDay<=$dateTimeEnd; $timeDay+=86400){
							$subPeriodBegin=strtotime(date("Y-m-d",$timeDay)." 00:00");
							$subPeriodEnd=strtotime(date("Y-m-d",$timeDay)." 23:59");
							$tmpCalEvtListFull=array_merge($tmpCalEvtListFull, MdlCalendar::periodEvts($tmpCalEvtList,$subPeriodBegin,$subPeriodEnd));
						}
						//Ajoute à la liste des evt
						foreach($tmpCalEvtListFull as $tmpEvt)  {$tmpEvt->pluginIsCurrent=true;  $eventList[]=$tmpEvt;}
					}
				}
				////	Evenements de chaque agenda : sélection normale du plugin (date de création OU recherche)
				foreach($visibleCalendars as $tmpCal){
					$eventList=array_merge($eventList, $tmpCal->evtList(null,null,1,false,$pluginParams));//Tous les Evt avec accessRight>=1, pas triés par H:M et filtrés avec $pluginParams
				}
				$eventList=array_unique($eventList,SORT_REGULAR);
				////	Ajoute les plugins
				foreach($eventList as $tmpEvt)
				{
					if($tmpEvt->readRight())
					{
						$tmpEvt->pluginModule=self::moduleName;
						$tmpEvt->pluginIcon=self::moduleName."/icon.png";
						$tmpEvt->pluginLabel=Txt::displayDate($tmpEvt->dateBegin,"normal",$tmpEvt->dateEnd)." : ".$tmpEvt->title;
						$tmpEvt->pluginTooltip=Txt::displayDate($tmpEvt->dateBegin,"full",$tmpEvt->dateEnd)."<hr>".$tmpEvt->affectedCalendarsLabel();
						$tmpEvt->pluginJsIcon="windowParent.redir('".$tmpEvt->getUrl("container")."');";//Redir vers l'agenda principal "datetime" & "displayType" (month/week/day)
						$tmpEvt->pluginJsLabel="lightboxOpen('".$tmpEvt->getUrl("vue")."');";
						$pluginsList[]=$tmpEvt;
					}
				}
			}
		}
		return $pluginsList;
	}

	/*
	 * ACTION : Evenements que l'user courant a créé
	 */
	public static function actionMyEvents()
	{
		$vDatas["myEvents"]=Db::getObjTab("calendarEvent","SELECT * FROM ap_calendarEvent WHERE _idUser=".Ctrl::$curUser->_id." ORDER BY dateBegin");
		static::displayPage("VueMyEvents.php",$vDatas);
	}

	/*
	 * ACTION : Edition d'un agenda
	 */
	public static function actionCalendarEdit()
	{
		//Init
		$curObj=Ctrl::getTargetObj();
		$curObj->controlEdit();
		if(MdlCalendar::addRight()==false)  {self::noAccessExit();}
		////	Valide le formulaire
		if(Req::isParam("formValidate")){
			//Enregistre & recharge l'objet
			$timeSlot=Req::getParam("timeSlotBegin")."-".Req::getParam("timeSlotEnd");
			$typeCalendar=$curObj->isNew() ? ", type='ressource'" : null;
			$curObj=$curObj->createUpdate("title=".Db::formatParam("title").", description=".Db::formatParam("description").", timeSlot=".Db::format($timeSlot).$typeCalendar);
			static::lightboxClose();
		}
		////	Affiche la vue
		$vDatas["curObj"]=$curObj;
		static::displayPage("VueCalendarEdit.php",$vDatas);
	}

	/*
	 * ACTION : Edition d'un evenement d'agenda
	 */
	public static function actionCalendarEventEdit()
	{
		//Init
		$curObj=Ctrl::getTargetObj();
		$curObj->controlEdit();
		////	Valide le formulaire
		if(Req::isParam("formValidate"))
		{
			//Modifie les détails ("fullRight()" uniquement)
			if($curObj->fullRight())
			{
				//Prépare les dates
				$dateBegin=Txt::formatDate(Req::getParam("dateBegin")." ".Req::getParam("timeBegin"), "inputDatetime", "dbDatetime");
				$dateEnd=Txt::formatDate(Req::getParam("dateEnd")." ".Req::getParam("timeEnd"), "inputDatetime", "dbDatetime");
				///périodicité
				$periodDateEnd=$periodValues=$periodDateExceptions=null;
				if(Req::isParam("periodType")){
					$periodDateEnd=Txt::formatDate(Req::getParam("periodDateEnd"), "inputDate", "dbDate");
					$periodValues=Txt::tab2txt(Req::getParam("periodValues_".Req::getParam("periodType")));
					if(Req::isParam("periodDateExceptions")){
						$periodDateExceptions=[];
						foreach(Req::getParam("periodDateExceptions") as $tmpDate)  {$periodDateExceptions[]=Txt::formatDate($tmpDate,"inputDate","dbDate");}
					}
				}
				//Enregistre & recharge l'objet
				$curObj=$curObj->createUpdate("title=".Db::formatParam("title").", description=".Db::formatParam("description","editor").", dateBegin=".Db::format($dateBegin).", dateEnd=".Db::format($dateEnd).", _idCat=".Db::formatParam("_idCat").", important=".Db::formatParam("important").", contentVisible=".Db::formatParam("contentVisible").", periodType=".Db::formatParam("periodType").", periodValues=".Db::format($periodValues).", periodDateEnd=".Db::format($periodDateEnd).", periodDateExceptions=".Db::formatTab2txt($periodDateExceptions));
			}
			//Réinitialise si besoin les affectations/propositions aux agendas (modif d'evt : pas de controle si l'agenda n'a plus d'affectations)
			if(Req::isParam("reinitCalendars")){
				foreach(Req::getParam("reinitCalendars") as $idCal)  {$curObj->deleteAffectation($idCal,true);}
			}
			//Attribue les nouvelles affectations/propositions aux agendas
			$affectationCals=$propositionCals=[];
			if(Req::isParam("affectationCalendars"))  {$affectationCals=Req::getParam("affectationCalendars");}
			if(Req::isParam("propositionCalendars"))  {$propositionCals=Req::getParam("propositionCalendars");}
			foreach(array_merge($affectationCals,$propositionCals) as $idCal){
				$tmpCal=Ctrl::getObj("calendar",$idCal);
				if(in_array($tmpCal,MdlCalendar::affectationCalendars())){
					$confirmed=(in_array($idCal,$affectationCals)) ? 1 : 0;
					Db::query("INSERT INTO ap_calendarEventAffectation SET _idEvt=".$curObj->_id.", _idCal=".$tmpCal->_id.", confirmed=".Db::format($confirmed));
				}
			}
			//Notifie par mail ("fullRight()" uniquement)
			if(Req::isParam("notifMail") && $curObj->fullRight())
			{
				$objLabel=Txt::displayDate($curObj->dateBegin,"full",$curObj->dateEnd)." : <b>".$curObj->title."</b>";
				$icalPath=self::getIcal($curObj, true);
				$icsFile=[["path"=>$icalPath, "name"=>Txt::clean($curObj->title,"max").".ics"]];
				$curObj->sendMailNotif($objLabel, null, $icsFile);
				File::rm($icalPath);
			}
			//Ferme la page
			static::lightboxClose();
		}
		////	Affiche la vue
		else
		{
			//Liste des agendas pour les affectations
			$vDatas["affectationCalendars"]=MdlCalendar::affectationCalendars();
			//Evt créé par un autre user : ajoute si besoin les agendas inaccessibles pour l'user courant mais quand même affectés à l'événement
			if($curObj->isNew()==false && $curObj->isAutor()==false){
				$vDatas["affectationCalendars"]=array_merge($vDatas["affectationCalendars"], $curObj->affectedCalendars("all"));
				$vDatas["affectationCalendars"]=MdlCalendar::sortCalendars(array_unique($vDatas["affectationCalendars"],SORT_REGULAR));//"SORT_REGULAR" pour les objets
			}
			//Prépare l'affichage de chaque agenda
			foreach($vDatas["affectationCalendars"] as $tmpCal){
				//Ajoute quelques propriétés à l'agenda
				$tmpCal->inputType=($tmpCal->editContentRight() || in_array($tmpCal,$curObj->affectedCalendars()))  ?  "affectation"  :  "proposition";		//Input principal : affectation OU proposition pour l'agenda
				$tmpCal->isChecked=($tmpCal->_id==Req::getParam("_idCal") || in_array($tmpCal,$curObj->affectedCalendars("all")))  ?  "checked"  :  null;	//Input principal : check l'agenda s'il est présélectionné ou déjà affecté
				$tmpCal->isDisabled=($tmpCal->editContentRight()==false && $curObj->fullRight()==false)  ?  "disabled"  :  null;							//Input principal : désactive l'agenda s'il n'est pas accessible en écriture && user courant pas auteur de l'evt
				$tmpCal->reinitCalendarInput=($curObj->isNew()==false && $tmpCal->isDisabled==null)  ?  true  :  false;										//Ajoute l'input "hidden" de réinitialisation de l'affectation : modif d'evt et input pas "disabled"
				//Tooltip du label principal
				if($tmpCal->isDisabled!=null)				{$tmpCal->tooltip=Txt::trad("CALENDAR_noModifInfo");}			//"Modification non autorisé..." (tjs mettre en premier)
				elseif($tmpCal->inputType=="affectation")	{$tmpCal->tooltip=Txt::trad("CALENDAR_addEvtTooltipBis");}		//"Ajouter l'événement.."
				else										{$tmpCal->tooltip=Txt::trad("CALENDAR_proposeEvtTooltipBis2");}	//"Proposer l'événement.. agenda en lecture seule"
				if(!empty($tmpCal->description))  {$tmpCal->tooltip.=" (".$tmpCal->description.")";}//Ajoute la description de l'agenda
			}
			//Nouvel evt : dates par défaut
			if($curObj->isNew()){
				$curObj->dateBegin =(Req::isParam("newEvtTimeBegin"))	?  date("Y-m-d H:i",Req::getParam("newEvtTimeBegin"))	:  date("Y-m-d H:00",time()+3600);							//date du jour, avec la prochaine heure courante
				$curObj->dateEnd   =(Req::isParam("newEvtTimeEnd"))		?  date("Y-m-d H:i",Req::getParam("newEvtTimeEnd"))		:  date("Y-m-d H:00",strtotime($curObj->dateBegin)+3600);	//une heure après l'heure de début
			}
			//Divers & affiche la page
			$vDatas["curObj"]=$curObj;
			$vDatas["tabPeriodValues"]=Txt::txt2tab($curObj->periodValues);
			foreach(Txt::txt2tab($curObj->periodDateExceptions) as $keyTmp=>$tmpException)	{$vDatas["periodDateExceptions"][$keyTmp+1]=Txt::formatDate($tmpException,"dbDate","inputDate");}
			$vDatas["curSpaceUserGroups"]=MdlUserGroup::getGroups(Ctrl::$curSpace);
			static::displayPage("VueCalendarEventEdit.php",$vDatas);
		}
	}

	/*
	 * AJAX : Control des créneaux horaires des agendas sélectionnés
	 */
	public static function actionTimeSlotBusy()
	{
		if(Req::isParam(["dateTimeBegin","dateTimeEnd","targetObjects"]))
		{
			//Init
			$textTimeSlotBusy=null;
			$timeBegin=Txt::formatDate(Req::getParam("dateTimeBegin"),"inputDatetime","time")+1;//Décale d'une sec. pour eviter les faux positifs. Exple: créneaux 11h-12h dispo, même si 12h-13h est occupé
			$timeEnd=Txt::formatDate(Req::getParam("dateTimeEnd"),"inputDatetime","time")-1;//idem. Exple: créneaux 11h-12h dispo, même si 12h-13h est occupé
			//Vérifie le créneau horaire sur chaque agenda sélectionné
			foreach(self::getTargetObjects() as $tmpCal)
			{
				$calendarBusy=$calendarBusyTimeSlots=null;
				//Evts de l'agenda sur la période sélectionné ($accessRightMini=0.5)
				foreach(MdlCalendar::periodEvts($tmpCal->evtList($timeBegin,$timeEnd),$timeBegin,$timeEnd) as $tmpEvt){
					if($tmpEvt->_id!=Req::getParam("_evtId")){//Sauf l'evt en cours d'édition (si modif)
						$calendarBusyTimeSlots.=" &nbsp; &nbsp; <img src='app/img/arrowRight.png'> ".Txt::displayDate($tmpEvt->dateBegin,"normal",$tmpEvt->dateEnd)." ";
						$calendarBusy=true;
					}
				}
				//L'agenda est occupé?
				if($calendarBusy==true)  {$textTimeSlotBusy.="<div class='vTimeSlotBusyRow'><div class='vTimeSlotBusyCell'>".$tmpCal->title."</div><div class='vTimeSlotBusyCell'>".$calendarBusyTimeSlots."</div></div>";}
			}
			//Retourne le message
			echo $textTimeSlotBusy;
		}
	}

	/*
	 * MENU : Liste des evenements à confirmer
	 */
	public static function menuProposedEvents()
	{
		////	Evenements proposés sur chacun de mes agendas
		$menuProposedEvents=null;
		foreach(MdlCalendar::myCalendars() as $tmpCal)
		{
			//S'il y a des propositions d'evenement : affiche le menu
			$eventsToConfirm=Db::getObjTab("calendarEvent","SELECT T1.* FROM ap_calendarEvent T1, ap_calendarEventAffectation T2 WHERE T1._id=T2._idEvt AND T2._idCal=".$tmpCal->_id." AND T2.confirmed is null");
			//Supprime les propositions d'evenements de plus de 60 jours
			$timeOneMonthAgo=time()-(86400*60);
			foreach($eventsToConfirm as $tmpKey=>$tmpEvt){
				if($tmpEvt->isOldEvt($timeOneMonthAgo))  {$tmpEvt->deleteAffectation($tmpCal->_id);  unset($eventsToConfirm[$tmpKey]);}
			}
			//S'il y a toujours des propositions
			if(!empty($eventsToConfirm))
			{
				//Evénements à confirmer sur l'agenda courant
				$calProposedEvents=null;
				foreach($eventsToConfirm as $tmpEvt){
					$evtTooltip=htmlspecialchars($tmpEvt->title)."<hr>".
								Txt::displayDate($tmpEvt->dateBegin,"normal",$tmpEvt->dateEnd)."<hr>".
								Txt::trad("CALENDAR_evtProposedBy")." ".$tmpEvt->displayAutor()."<hr>".Txt::trad("OBJECTcalendar")." : ".$tmpCal->title.
								(strlen($tmpEvt->description) ? "<hr>".Txt::reduce(strip_tags($tmpEvt->description),100) : null);
					$calProposedEvents.="<li id=\"proposedEvent".$tmpEvt->_id."-".$tmpCal->_id."\" onclick=\"proposedEventConfirm(".$tmpCal->_id.",".$tmpEvt->_id.",this.id);\" title=\"".$evtTooltip."\">".$tmpEvt->title."</li>";
				}
				//Libellé des evts proposés
				$tmpCalLabel=($tmpCal->isMyPerso())  ?  Txt::trad("CALENDAR_evtProposedForMe")  :  Txt::trad("CALENDAR_evtProposedFor")."<div style='margin-left:25px;font-style:italic;'>".$tmpCal->title."</div>";
				$menuProposedEvents.="<ul class='proposedEventList'><img src='app/img/important.png'>".$tmpCalLabel.$calProposedEvents."</ul>";
			}
		}
		////	Retourne si besoin le menu, avec un effet "pulsate" de 10sec.
		if(!empty($menuProposedEvents))  {return $menuProposedEvents."<hr><script> $(function(){ $('.proposedEventList').effect('pulsate',{times:5},5000); }); </script>";}
	}

	/*
	 * AJAX : Validation (ou non) d'une proposition d'événement
	 */
	public static function actionProposedEventConfirm()
	{
		$curCal=Ctrl::getTargetObj();
		if($curCal->proposedEventConfirmRight())
		{
			$curEvt=Ctrl::getObj("calendarEvent",Req::getParam("_idEvt"));
			if(Req::getParam("confirmed")==1)	{Db::query("UPDATE ap_calendarEventAffectation SET confirmed=1 WHERE _idEvt=".(int)$curEvt->_id." AND _idCal=".$curCal->_id);}
			else								{$curEvt->deleteAffectation($curCal->_id);}
			echo "true";
		}
	}

	/*
	 * ACTION : Vue détaillée d'un événement
	 */
	public static function actionVueCalendarEvent()
	{
		$curObj=Ctrl::getTargetObj();
		$curObj->controlRead();
		// visibilite / Catégorie
		$vDatas["contentVisible"]=(preg_match("/(public_cache|prive)/i",$curObj->contentVisible))  ?  ($curObj->contentVisible=="public_cache"?Txt::trad("CALENDAR_visibilityPublicHide"):Txt::trad("CALENDAR_visibilityPrivate"))  :  null;
		$vDatas["labelCategory"]=(!empty($curObj->objCategory))  ?  $curObj->objCategory->display()  :  null;
		//Périodicité
		$vDatas["labelPeriod"]=$periodValues=null;
		if(!empty($curObj->periodType))
		{
			//Périodicité
			$vDatas["labelPeriod"]=Txt::trad("CALENDAR_period_".$curObj->periodType);
			foreach(Txt::txt2tab($curObj->periodValues) as $tmpVal){
				if($curObj->periodType=="weekDay")		{$periodValues.=Txt::trad("day_".$tmpVal).", ";}
				elseif($curObj->periodType=="month")	{$periodValues.=Txt::trad("month_".$tmpVal).", ";}
			}
			if(!empty($periodValues))	{$vDatas["labelPeriod"].=" : ".trim($periodValues, ", ");}
			//Périodicité : fin
			if(!empty($curObj->periodDateEnd))	{$vDatas["labelPeriod"].="<br>".Txt::trad("CALENDAR_periodDateEnd")." : ".Txt::displayDate($curObj->periodDateEnd,"full");}
			//Périodicité : exceptions
			if(!empty($curObj->periodDateExceptions)){
				$vDatas["labelPeriod"].="<br>".Txt::trad("CALENDAR_periodException")." : ";
				$periodDateExceptions=array_filter(Txt::txt2tab($curObj->periodDateExceptions));//"array_filter" pour enlever les valeurs vides
				foreach($periodDateExceptions as $tmpVal)	{$vDatas["labelPeriod"].=Txt::displayDate($tmpVal,"dateMini").", ";}
				$vDatas["labelPeriod"]=trim($vDatas["labelPeriod"], ", ");
			}
		}
		//Détails de l'événement
		$vDatas["curObj"]=$curObj;
		static::displayPage("VueCalendarEvent.php",$vDatas);
	}

	/*
	 * ACTION : Edition des categories d'evenements
	 */
	public static function actionCalendarEventCategoryEdit()
	{
		////	Droit d'ajouter une categorie?
		if(MdlCalendarEventCategory::addRight()==false)  {static::lightboxClose();}
		////	Validation de formulaire
		if(Req::isParam("formValidate")){
			$curObj=Ctrl::getTargetObj();
			$curObj->controlEdit();
			//Modif d'une categorie
			$_idSpaces=(!in_array("all",Req::getParam("spaceList")))  ?  Txt::tab2txt(Req::getParam("spaceList"))  :  null;
			$curObj->createUpdate("title=".Db::formatParam("title").", description=".Db::formatParam("description").", color=".Db::formatParam("color").", _idSpaces=".Db::format($_idSpaces));
			//Ferme la page
			static::lightboxClose();
		}
		////	Liste des categories
		$vDatas["categoriesList"]=MdlCalendarEventCategory::getCategories(true);
		$vDatas["categoriesList"][]=new MdlCalendarEventCategory();//nouvelle categorie vide
		foreach($vDatas["categoriesList"] as $tmpKey=>$tmpCategory){
			if($tmpCategory->editRight()==false)	{unset($vDatas["categoriesList"][$tmpKey]);}
			else{
				$tmpCategory->tmpId=$tmpCategory->_targetObjId;
				$tmpCategory->createdBy=($tmpCategory->isNew()==false)  ?  Txt::trad("creation")." : ".$tmpCategory->displayAutor()  :  null;
			}
		}
		////	Affiche le form
		static::displayPage("VueCalendarEventCategoryEdit.php",$vDatas);
	}

	/*
	 * Export des événements d'un agenda au format .Ical
	 */
	public static function actionExportEvents()
	{
		$objCalendar=Ctrl::getTargetObj();
		//Droit en édition?
		if($objCalendar->editRight())
		{
			//Téléchargement du fichier Ical
			if(Req::isParam("sendMail")==false)  {self::getIcal($objCalendar);}
			//Envoi du fichier Ical par mail
			else
			{
				//Prépare le mail
				$subject=$mainMessage=ucfirst(Txt::trad("OBJECTcalendar"))." ''".$objCalendar->title."'' : ".Txt::trad("CALENDAR_exportEvtMailList");
				$mainMessage.="<br>".Txt::trad("CALENDAR_exportEvtMailInfo");
				$icalPath=self::getIcal($objCalendar,true);
				$fileName=Txt::clean($objCalendar->title,"max")."_".date("d-m-Y").".ics";
				//Envoie le mail, Supprime le fichier temp, Puis redirige en page principale du module
				Tool::sendMail(Ctrl::$curUser->mail, $subject, $mainMessage, null, [["path"=>$icalPath, "name"=>$fileName]]);
				File::rm($icalPath);
				Ctrl::redir("?ctrl=".Req::$curCtrl);
			}
		}
	}

	/*
	 * Création du fichier .ICAL
	 */
	public static function getIcal($curObj, $tmpFile=false)
	{
		////	Evenement spécifié : récupère l'agenda principal  ||  Agenda spécifié : récupère ses événements
		if($curObj::objectType=="calendarEvent"){
			$eventList=[$curObj];
			$objCalendar=$curObj->containerObj();
			$icalCalname=null;
		}elseif($curObj::objectType=="calendar"){
			$periodBegin=time()-(86400*365);//Time - 1 an
			$periodEnd=time()+(86400*365*5);//Time + 5 ans
			$eventList=$curObj->evtList($periodBegin,$periodEnd);
			$objCalendar=$curObj;
			$icalCalname="X-WR-CALNAME:".$objCalendar->title."\n";
		}else{return false;}

		////	Prépare le fichier Ical
		$ical=	"BEGIN:VCALENDAR\n".
				"METHOD:PUBLISH\n".
				"VERSION:2.0\n".
				$icalCalname.
				"PRODID:-//Agora-Project//".self::$agora->name."//EN\n".
				"X-WR-TIMEZONE:".self::$curTimezone."\n".
				"CALSCALE:GREGORIAN\n".
				"BEGIN:VTIMEZONE\n".
				"TZID:".self::$curTimezone."\n".
				"X-LIC-LOCATION:".self::$curTimezone."\n".
				"BEGIN:DAYLIGHT\n".
				"TZOFFSETFROM:".self::icalHour()."\n".
				"TZOFFSETTO:".self::icalHour(1)."\n".
				"TZNAME:CEST\n".
				"DTSTART:19700329T020000\n".
				"RRULE:FREQ=YEARLY;INTERVAL=1;BYDAY=-1SU;BYMONTH=3\n".
				"END:DAYLIGHT\n".
				"BEGIN:STANDARD\n".
				"TZOFFSETFROM:".self::icalHour(1)."\n".
				"TZOFFSETTO:".self::icalHour()."\n".
				"TZNAME:CET\n".
				"DTSTART:19701020T030000\n".
				"RRULE:FREQ=YEARLY;INTERVAL=1;BYDAY=-1SU;BYMONTH=10\n".
				"END:STANDARD\n".
				"END:VTIMEZONE\n";
		//Ajoute chaque evenement (plusieurs fois si l'evt est périodique)
		foreach($eventList as $tmpEvt)
		{
			//Init
			$icalPeriod=$periodDateEnd=$icalCategories=null;
			if($tmpEvt->periodDateEnd)  {$periodDateEnd=";UNTIL=".self::icalDate($tmpEvt->periodDateEnd);}
			//Périodicité (année/jour/mois)
			if($tmpEvt->periodType=="year"){
				$icalPeriod="RRULE:FREQ=YEARLY;INTERVAL=1".$periodDateEnd."\n";
			}elseif($tmpEvt->periodType=="weekDay"){
				$tmpEvtPeriodValues=str_replace([1,2,3,4,5,6,7], ['MO','TU','WE','TH','FR','SA','SU'], Txt::txt2tab($tmpEvt->periodValues));
				$icalPeriod="RRULE:FREQ=WEEKLY;INTERVAL=1;BYDAY=".implode(",",$tmpEvtPeriodValues).$periodDateEnd."\n";
			}elseif($tmpEvt->periodType=="month"){
				$icalPeriod="RRULE:FREQ=MONTHLY;INTERVAL=1".$periodDateEnd."\n";
			}
			//Description & Périodicité & Categorie
			$icalDescription=$tmpEvt->description;
			if(!empty($tmpEvt->periodValues)){
				$icalDescription.=" - ".Txt::trad("CALENDAR_period_".$tmpEvt->periodType)." : ";
				foreach(Txt::txt2tab($tmpEvt->periodValues) as $tmpVal){
					if($tmpEvt->periodType=="weekDay")		{$icalDescription.=Txt::trad("day_".$tmpVal).", ";}
					elseif($tmpEvt->periodType=="month")	{$icalDescription.=Txt::trad("month_".$tmpVal).", ";}
				}
			}
			if(count($tmpEvt->affectedCalendars())>0)  {$icalDescription.=" - ".$tmpEvt->affectedCalendarsLabel();}//agendas où l'evt est affecté
			$icalDescription=str_replace(["\r","\n"], null, html_entity_decode(strip_tags($icalDescription)));//idem
			if(!empty($icalDescription))	{$icalDescription="DESCRIPTION:".$icalDescription."\n";}
			if(!empty($tmpEvt->_idCat))		{$icalCategories="CATEGORIES:".Ctrl::getObj("calendarEventCategory",$tmpEvt->_idCat)->title."\n";}
			//Ajoute l'evenement
			$ical.= "BEGIN:VEVENT\n".
					"CREATED:".self::icalDate($tmpEvt->dateCrea)."\n".
					"UID:".$tmpEvt->md5Id()."\n".
					"DTEND;TZID=".self::icalDate($tmpEvt->dateEnd,true)."\n".
					"SUMMARY:".$tmpEvt->title."\n".
					"LAST-MODIFIED:".self::icalDate($tmpEvt->dateModif)."\n".
					"DTSTAMP:".self::icalDate(date("Y-m-d H:i"))."\n".
					"DTSTART;TZID=".self::icalDate($tmpEvt->dateBegin,true)."\n".
					"DTEND;TZID=".self::icalDate($tmpEvt->dateEnd,true)."\n".
					$icalPeriod.$icalDescription.$icalCategories.
					"SEQUENCE:0\n".
					"END:VEVENT\n";
		}
		//Fin du ical
		$ical.="END:VCALENDAR";

		////	Enregistre un fichier Ical temporaire et on renvoie son "Path"
		if($tmpFile==true){
			$tmpFilePath=tempnam(sys_get_temp_dir(),"exportIcal".uniqid());
			$fp=fopen($tmpFilePath, "w");
			fwrite($fp,$ical);
			fclose($fp);
			return $tmpFilePath;
		}
		////	Affiche directement le fichier .Ical
		else{
			header("Content-type: text/calendar; charset=utf-8");
			header("Content-Disposition: inline; filename=".Txt::clean($objCalendar->title,"max")."_".date("d-m-Y").".ics");
			echo $ical;
		}
	}
	/* Export .ical : formate l'heure */
	public static function icalHour($timeLag=0)
	{
		// Exemple avec "-5:30"
		$hourTimezone=Tool::$tabTimezones[self::$curTimezone];
		$valueSign=(substr($hourTimezone,0,1)=="-") ? '-' : '+';				//"-"
		$hourAbsoluteVal=str_replace(['-','+'],null,substr($hourTimezone,0,-3));//"5"
		$hourAbsoluteVal+=$timeLag;												//Si $timeLag=2 -> "7"
		if($hourAbsoluteVal<10)	{$hourAbsoluteVal="0".$hourAbsoluteVal;}		//"05"
		$minutes=substr($hourTimezone,-2);										//"30"
		return $valueSign.$hourAbsoluteVal.$minutes;//Retourne "-0530"
	}
	/* Export .ical : formate la date */
	public static function icalDate($dateTime, $timezone=false)
	{
		$dateTime=date("Ymd",strtotime($dateTime))."T".date("Hi",strtotime($dateTime))."00";//exple: "20151231T235900Z"
		return ($timezone==true) ? self::$curTimezone.":".$dateTime : str_replace("T000000Z","T235900Z",$dateTime."Z");
	}

	/*
	 * Import d'événement (format .ical) dans un agenda
	 */
	public static function actionImportEvents()
	{
		//Charge et controle
		$objCalendar=Ctrl::getTargetObj();
		$objCalendar->controlEdit();
		$vDatas=[];
		////	Validation de formulaire : sélection du fichier / des evt à importer
		if(Req::isParam("formValidate"))
		{
			////	PRÉPARE LE TABLEAU D'IMPORT
			if(isset($_FILES["importFile"]) && is_file($_FILES["importFile"]["tmp_name"]))
			{
				//Récupère les événements
				require("class.iCalReader.php");
				$ical=new ical($_FILES["importFile"]["tmp_name"]);
				$vDatas["eventList"]=$ical->events();
				//Formate les evenements à importer
				if(empty($vDatas["eventList"]))  {Ctrl::addNotif("Import .Ical file : formating error");}
				else
				{
					foreach($vDatas["eventList"] as $cptEvt=>$tmpEvt)
					{
						//init
						$tmpEvt["dbDateEnd"]=$tmpEvt["dbDescription"]=$tmpEvt["dbPeriodType"]=$tmpEvt["dbPeriodValues"]=$tmpEvt["dbPeriodDateEnd"]="";
						//Prépare l'evt (attention au décalage des timezones dans le fihier .ics : mais corrigé via le "strtotime()")
						$tmpEvt["dbDateBegin"]=date("Y-m-d H:i",strtotime($tmpEvt["DTSTART"]));
						if(!empty($tmpEvt["DTEND"])){
							$tmpEvt["dbDateEnd"]=date("Y-m-d H:i",strtotime($tmpEvt["DTEND"]));
							if(strlen($tmpEvt["DTEND"])==8)  {$tmpEvt["dbDateEnd"]=date("Y-m-d H:i",(strtotime($tmpEvt["DTEND"])-86400));}//Les événements "jour" sont importés avec un jour de trop (cf. exports depuis G-Calendar)
						}
						$tmpEvt["dbTitle"]=strip_tags(nl2br($tmpEvt["SUMMARY"]));
						if(!empty($tmpEvt["DESCRIPTION"]))  {$tmpEvt["dbDescription"]=strip_tags(nl2br($tmpEvt["DESCRIPTION"]));}
						//Evenement périodique
						if(!empty($tmpEvt["RRULE"]))
						{
							//init
							$rruleTab=explode(";",$tmpEvt["RRULE"]);
							//Périodique : semaine
							if(stristr($tmpEvt["RRULE"],"FREQ=WEEKLY") && stristr($tmpEvt["RRULE"],"BYDAY=")){
								$tmpEvt["dbPeriodType"]="weekDay";
								foreach($rruleTab as $rruleTmp){//Jours de la période
									if(stristr($rruleTmp,"BYDAY="))  {$tmpEvt["dbPeriodValues"]=str_replace(['BYDAY=',',','MO','TU','WE','TH','FR','SA','SU'], [null,'@@',1,2,3,4,5,6,7], $rruleTmp);}
								}
							}
							//Périodique : mois
							if(stristr($tmpEvt["RRULE"],"FREQ=MONTHLY")){
								$tmpEvt["dbPeriodType"]="month";
								$tmpEvt["dbPeriodValues"]="@@1@@2@@3@@4@@5@@6@@7@@8@@9@@10@@11@@12@@";//sélectionne tous les mois
							}
							//Périodique : année
							if(stristr($tmpEvt["RRULE"],"FREQ=YEARLY")){
								$tmpEvt["dbPeriodType"]="year";
								$tmpEvt["dbPeriodValues"]=null;
							}
							//Périodicité : Fin de périodicité
							if(stristr($tmpEvt["RRULE"],"UNTIL=")){
								foreach($rruleTab as $rruleTmp){//Fin de période
									if(stristr($rruleTmp,"UNTIL=")){
										$tmpEvt["dbPeriodDateEnd"]=substr(intval(str_replace('UNTIL=','',$rruleTmp)), 0, 8);
										$tmpEvt["dbPeriodDateEnd"]=date("Y-m-d", strtotime($tmpEvt["dbPeriodDateEnd"]));}
								}
							}
						}
						//Etat de l'événement : à importer OU dejà present (donc ne pas importer)
						$tmpEvt["isPresent"]=(Db::getVal("SELECT count(*) FROM ap_calendarEvent T1, ap_calendarEventAffectation T2 WHERE T1._id=T2._idEvt AND T2._idCal=".$objCalendar->_id." AND T1.title=".Db::format($tmpEvt["dbTitle"])." AND T1.dateBegin=".Db::format($tmpEvt["dbDateBegin"])." AND T1.dateEnd=".Db::format($tmpEvt["dbDateEnd"])) > 0)  ?  true  :  false;
						//Ajoute l'evt
						$vDatas["eventList"][$cptEvt]=$tmpEvt;
					}
				}
			}
			////	IMPORTE LES ÉVÉNEMENTS
			elseif(Req::isParam("eventList"))
			{
				//Import de chaque événement
				foreach(Req::getParam("eventList") as $tmpEvt)
				{
					//Import sélectionné?
					if(!empty($tmpEvt["checked"])){
						//Créé et enregistre l'événement
						$curObj=new MdlCalendarEvent();
						$curObj=$curObj->createUpdate("title=".Db::format($tmpEvt["dbTitle"]).", description=".Db::format($tmpEvt["dbDescription"]).", dateBegin=".Db::format($tmpEvt["dbDateBegin"]).", dateEnd=".Db::format($tmpEvt["dbDateEnd"]).", periodType=".Db::format($tmpEvt["dbPeriodType"]).", periodValues=".Db::format($tmpEvt["dbPeriodValues"]).", periodDateEnd=".Db::format($tmpEvt["dbPeriodDateEnd"]));
						//Affecte à l'agenda courant
						Db::query("INSERT INTO ap_calendarEventAffectation SET _idEvt=".$curObj->_id.", _idCal=".$objCalendar->_id.", confirmed=1");
					}
				}
				//Ferme la page
				static::lightboxClose();
			}
		}
		////	Affiche le menu d'Import/Export
		static::displayPage("VueCalendarImportEvt.php",$vDatas);
	}
}