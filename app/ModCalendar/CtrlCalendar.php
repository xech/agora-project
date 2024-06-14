<?php
/**
* This file is part of the Agora-Project Software package
*
* @copyleft Agora-Project <https://www.agora-project.net>
* @license GNU General Public License, version 2 (GPL-2.0)
*/

//Namespace du parser ICal.php
use ICal\ICal;

/*
 * CONTROLEUR DU MODULE "CALENDAR"
 */
class CtrlCalendar extends Ctrl
{
	const moduleName="calendar";
	public static $moduleOptions=["createSpaceCalendar","adminAddRessourceCalendar","adminAddCategory"];
	public static $MdlObjects=["MdlCalendar","MdlCalendarEvent"];

	/********************************************************************************************
	 * VUE : PAGE PRINCIPALE
	 ********************************************************************************************/
	public static function actionDefault()
	{
		////	MENU DE PROPOSITION D'EVENEMENT A CONFIRMER
		$vDatas["eventProposition"]=self::eventProposition();
		////	AGENDAS VISIBLE POUR L'USER (OU TOUS LES AGENDAS : "affectationCalendars()" SI ADMIN GENERAL)  &&  AGENDAS AFFICHES  &&  EVT PROPOSÉS
		if(empty($_SESSION["calsListDisplayAll"]) || Req::isParam("calsListDisplayAll"))   {$_SESSION["calsListDisplayAll"]=(Req::param("calsListDisplayAll")==1 && Ctrl::$curUser->isGeneralAdmin());}
		$vDatas["readableCalendars"]=($_SESSION["calsListDisplayAll"]==true)  ?  MdlCalendar::affectationCalendars()  :  MdlCalendar::readableCalendars();
		$vDatas["displayedCalendars"]=MdlCalendar::displayedCalendars($vDatas["readableCalendars"]);
		////	MODE D'AFFICHAGE (cf. MdlCalendar::$displayModes : month, week, workWeek, 4Days, day)
		$displayMode=self::prefUser("calendarDisplayMode","displayMode");
		if(empty($displayMode))  {$displayMode=(Req::isMobile()) ? "4Days" : "month";}//Affichage par défaut
		$vDatas["displayMode"]=$displayMode;
		////	TEMPS DE RÉFÉRENCE  &  JOURS FÉRIÉS
		$vDatas["curTime"]=$curTime=Req::isParam("curTime") ? Req::param("curTime") : time();
		$vDatas["celebrationDays"]=Trad::celebrationDays(date("Y",$curTime));

		////	AFFICHAGE : PREPARE LES TIMES/DATES
		//AFFICHAGE MOIS
		if($displayMode=="month"){
			$vDatas["timeBegin"]	=strtotime(date("Y-m",$curTime)."-01 00:00");
			$vDatas["timeEnd"]		=strtotime(date("Y-m",$curTime)."-".date("t",$curTime)." 23:59");
			$vDatas["urlTimePrev"]	=strtotime("-1 month",$curTime);
			$vDatas["urlTimeNext"]	=strtotime("+1 month",$curTime);
			$displayedTimeBegin		=strtotime("-".(date("N",$vDatas["timeBegin"])-1)." days", $vDatas["timeBegin"]);//Commence par un lundi (ex: si le mois commence un mercredi, on affiche le lundi/mardi du mois précédent)
			$displayedTimeEnd		=strtotime("+".(7-date("N",$vDatas["timeEnd"]))." days", $vDatas["timeEnd"]);	//Termine par un dimanche
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

		////	LABEL DU MOIS AFFICHÉ : AFFICHAGE MOBILE OU  NORMAL
		$monthTime=$vDatas["timeBegin"];//Début de période comme référence
		if(Req::isMobile())	{$vDatas["labelMonth"]=(date("Y")==date("Y",$monthTime))  ?  Txt::formatime("MMMM",$monthTime)  :  Txt::formatime("MMM y",$monthTime);}//"Janvier" OU "Janv. 2018" (si affiche une autre année)
		else				{$vDatas["labelMonth"]=(date("Ym",$vDatas["timeBegin"])==date("Ym",$vDatas["timeEnd"]))  ?  Txt::formatime("MMMM y",$monthTime)  :  Txt::formatime("MMMM y",$vDatas["timeBegin"])." / ".Txt::formatime("MMMM y",$vDatas["timeEnd"]);}//"Janvier 2020" OU "Janvier 2020 / fevrier 2020" (si on affiche une semaine sur 2 mois)
		////	MENU DES ANNÉES & MOIS
		$vDatas["calMonthPeriodMenu"]=null;
		for($tmpMonth=1; $tmpMonth<=12; $tmpMonth++){
			$tmpMonthTime=strtotime(date("Y",$curTime)."-".($tmpMonth>9?$tmpMonth:"0".$tmpMonth)."-01");
			$vDatas["calMonthPeriodMenu"].="<a onclick=\"redir('?ctrl=calendar&curTime=".$tmpMonthTime."')\" ".(date("Y-m",$curTime)==date("Y-m",$tmpMonthTime)?"class='linkSelect'":null).">".Txt::formatime("MMMM",$tmpMonthTime)."</a>";
		}
		$vDatas["calMonthPeriodMenu"].="<hr>";
		for($tmpYear=date("Y")-3; $tmpYear<=date("Y")+5; $tmpYear++){
			$tmpYearTime=strtotime($tmpYear."-".date("m",$curTime)."-01");
			$vDatas["calMonthPeriodMenu"].="<a onclick=\"redir('?ctrl=calendar&curTime=".$tmpYearTime."')\" ".(date("Y",$curTime)==$tmpYear?"class='linkSelect'":null).">".$tmpYear."</a>";
		}
		////	LISTE DES JOURS À AFFICHER (43200sec de décalage : cf. heures d'été/hiver)
		$vDatas["periodDays"]=[];
		if(empty($displayedTimeBegin))	{$displayedTimeBegin=$vDatas["timeBegin"];  $displayedTimeEnd=$vDatas["timeEnd"];}
		for($timeDay=$displayedTimeBegin+43200; $timeDay<=$displayedTimeEnd; $timeDay+=86400){
			$tmpDay["date"]=date("Y-m-d",$timeDay);													//Date
			$tmpDay["timeBegin"]=strtotime(date("Y-m-d",$timeDay)." 00:00");						//Timestamp de début
			$tmpDay["timeEnd"]=strtotime(date("Y-m-d",$timeDay)." 23:59");							//Timestamp de fin
			$tmpDay["celebrationDay"]=(array_key_exists(date("Y-m-d",$timeDay),$vDatas["celebrationDays"]))  ?  $vDatas["celebrationDays"][date("Y-m-d",$timeDay)]  :  null;//Libelle d'un jour ferie ?
			$vDatas["periodDays"][$tmpDay["date"]]=$tmpDay;//Ajoute le jour à la liste
		}

		////	RECUPERE LA VUE DE CHAQUE AGENDA ("VueCalendarMonth.php" / "VueCalendarWeek.php")  &&   LA LISTE DES EVENEMENTS
		foreach($vDatas["displayedCalendars"] as $cptCal=>$tmpCal)
		{
			//Label d'ajout d'événement OU de proposition d'événement
			if($tmpCal->addContentRight())		{$tmpCal->addEventLabel=Txt::trad("CALENDAR_addEvtTooltip");}
			elseif($tmpCal->addOrProposeEvt())	{$tmpCal->addEventLabel=Txt::trad("CALENDAR_proposeEvtTooltip");}
			else								{$tmpCal->addEventLabel=null;}
			//EVENEMENTS POUR CHAQUE JOUR
			$tmpCal->eventList=[];
			$tmpCal->eventListDisplayed=$tmpCal->eventList($displayedTimeBegin,$displayedTimeEnd);//Events de la période affichée
			foreach($vDatas["periodDays"] as $tmpDate=>$tmpDay)
			{
				//EVENEMENTS DU JOUR COURANT
				$tmpCal->eventList[$tmpDay["date"]]=[];
				$tmpDayEvts=MdlCalendar::eventFilter($tmpCal->eventListDisplayed,$tmpDay["timeBegin"],$tmpDay["timeEnd"]);								//Récupère uniquement les events de la journée
				foreach($tmpDayEvts as $tmpEvt){																										//Parcourt chaque événement :
					$tmpEvt->timeBegin=strtotime($tmpEvt->dateBegin);																					//"time" du début de journée
					$tmpEvt->timeEnd=strtotime($tmpEvt->dateEnd);																						//"time" de fin de journée
					$tmpEvt->dateTimeLabel=(Req::isMobile())  ?  null  :  Txt::dateLabel($tmpEvt->timeBegin,"mini",$tmpEvt->timeEnd)."&nbsp; ";			//label "DateTime" de l'evt
					$tmpEvt->containerAttributes='data-eventColor="'.$tmpEvt->eventColor.'"';															//Attributs de l'evt : "eventColor" + d'autres en affichage "week"
					$tmpEvt->contextMenuOptions=["iconBurger"=>"floatSmall", "_idCal"=>$tmpCal->_id, "curDateTime"=>strtotime($tmpEvt->dateBegin)];		//Options du menu contextuel (cf. "divContainerContextMenu()")
					$tmpEvt->importantIcon=(!empty($tmpEvt->important))  ?  "&nbsp;<img src='app/img/important.png'>"  :  null;							//Icone "important"
					if($displayMode!="month"){																											//Affichage semaine/jour:
						$tmpEvt->minutesFromDayBegin=($tmpDay["timeBegin"]<$tmpEvt->timeBegin) ?  (($tmpEvt->timeBegin-$tmpDay["timeBegin"])/60)  : 0;	//Heure/Minutes de début d'affichage ("0" s'il commence avant le jour)
						$evtTmpDayBefore=($tmpEvt->timeBegin < $tmpDay["timeBegin"]);																	//-Evt commence avant le jour courant ?
						$evtTmpDayAfter=($tmpEvt->timeEnd > $tmpDay["timeEnd"]);																		//-Evt termine après le jour courant?
						if($evtTmpDayBefore==true && $evtTmpDayAfter==true)	{$tmpEvt->durationMinutes=24*60;}											//-Affiche toute la journée
						elseif($evtTmpDayBefore==true)						{$tmpEvt->durationMinutes=($tmpEvt->timeEnd- $tmpDay["timeBegin"])/60;}		//-Affiche l'evt depuis 0h00 du jour courant
						elseif($evtTmpDayAfter==true)						{$tmpEvt->durationMinutes=($tmpDay["timeEnd"] - $tmpEvt->timeBegin)/60;}	//-Affiche l'evt jusqu'à 23h59 du jour courant
						else												{$tmpEvt->durationMinutes=($tmpEvt->timeEnd - $tmpEvt->timeBegin)/60;}		//-Affichage normal (au cour de la journée)
					}
					$tmpCal->eventList[$tmpDate][]=$tmpEvt;																								//Ajoute l'evt à la liste !
				}
			}
			//Récupère la vue
			$tmpCal->isFirstCal=($cptCal==0);
			$vCalDatas=$vDatas;
			$vCalDatas["tmpCal"]=$tmpCal;
			$calendarVue=($displayMode=="month")?"VueCalendarMonth.php":"VueCalendarWeek.php";
			$tmpCal->calendarVue=self::getVue(Req::curModPath().$calendarVue, $vCalDatas);
		}

		////	SYNTHESE DES AGENDAS (SI + D'UN AGENDA)
		if(count($vDatas["displayedCalendars"])>1 && !Req::isMobile())
		{
			$vDatas["periodSynthese"]=[];																												//Jours à afficher pour la synthese
			foreach($vDatas["periodDays"] as $tmpDate=>$tmpDay){																						//Parcour chaque jour de la période affichée
				if($displayMode=="month" && date("m",$tmpDay["timeBegin"])!=date("m",$curTime))  {continue;}											//affichage "month" & jour d'un autre mois : passe le jour
				$tmpDay["calsEvts"]=[];																													//Evts du jour
				$nbCalsWithEvt=0;																														//Nb de calendriers ayant un event
				foreach($vDatas["displayedCalendars"] as $tmpCal){																						//Evts de chaque agenda :
					$tmpDay["calsEvts"][$tmpCal->_id]=MdlCalendar::eventFilter($tmpCal->eventListDisplayed,$tmpDay["timeBegin"],$tmpDay["timeEnd"]);	//Récupère uniquement les events de la journée
					if(!empty($tmpDay["calsEvts"][$tmpCal->_id]))  {$nbCalsWithEvt++;}																	//Incrémente $nbCalsWithEvt ?
				}
				$tmpDay["nbCalsWithEvt"]=(!empty($nbCalsWithEvt))  ?  Txt::dateLabel($tmpDate,"dateFull")." :<br>".Txt::trad("CALENDAR_calendarsPercentBusy")." : ".$nbCalsWithEvt." sur ".count($tmpDay["calsEvts"])  :  null;//Tooltip de synthese si au moins un agenda possède un événement à cette date
				$vDatas["periodSynthese"][$tmpDate]=$tmpDay;																					//Ajoute le jour de la synthese
			}
		}
		////	LANCE L'AFFICHAGE
		static::displayPage("VueIndex.php",$vDatas);
	}

	/********************************************************************************************
	 * AGENDA COURANT EST AFFICHÉ ?
	 ********************************************************************************************/
	public static function isDisplayedCal($displayedCalendars, $curCal)
	{
		foreach($displayedCalendars as $tmpCal){
			if($tmpCal->_id==$curCal->_id)  {return true;}
		}
	}

	/********************************************************************************************
	 * PLUGINS DU MODULE
	 ********************************************************************************************/
	public static function getPlugins($params)
	{
		$pluginsList=[];
		//// Uniquement pour "search" et "dashboard"  (vérif s'il y a des agendas dispo)
		if(preg_match("/search|dashboard/i",$params["type"]) && count(MdlCalendar::readableCalendars())>0)
		{
			//// Ajoute les propositions d'evt : affichage "dashboard"
			if($params["type"]=="dashboard"){
				$eventProposition=self::eventProposition();
				if(!empty($eventProposition))  {$pluginsList["eventProposition"]=(object)["moduleName"=>self::moduleName, "pluginSpecificMenu"=>$eventProposition];}//créé un objet via "(object)" (idem "new stdClass()")
			}
			//// Parcourt chaque agenda visible : ajoute les événements de l'agenda
			foreach(MdlCalendar::readableCalendars() as $tmpCal)
			{
				////  $filterByCategory=false  & $orderByHourMinute=false  & $accessRightMin=1  & $pluginParams
				foreach($tmpCal->eventList(null,null,false,false,1,$params) as $tmpEvt)
				{
					//// Vérif si l'evt n'a pas déjà été ajouté (car peut être affecté à plusieurs agendas) && se limite à 200 evt max (cf. affichage des nouveaux evt après import de fichier Ical)
					if(empty($pluginsList[$tmpEvt->_typeId]) && count($pluginsList)<200)
					{
						$tmpEvt->pluginIcon=self::moduleName."/icon.png";
						$tmpEvt->pluginLabel=Txt::dateLabel($tmpEvt->dateBegin,"basic",$tmpEvt->dateEnd)." : ".$tmpEvt->title;
						$tmpEvt->pluginTooltip=Txt::dateLabel($tmpEvt->dateBegin,"basic",$tmpEvt->dateEnd)."<hr>".$tmpEvt->affectedCalendarsLabel();
						$tmpEvt->pluginJsIcon="windowParent.redir('".$tmpEvt->getUrl()."');";//Affiche l'evt dans son principal agenda (cf "getUrl()" surchargée)
						$tmpEvt->pluginJsLabel=$tmpEvt->openVue();//Affiche l'evt en détail
						$pluginsList[$tmpEvt->_typeId]=$tmpEvt;
					}
				}
			}
		}
		return $pluginsList;
	}

	/********************************************************************************************
	 * VUE : EDITION D'UN AGENDA
	 ********************************************************************************************/
	public static function actionCalendarEdit()
	{
		//Init
		$curObj=Ctrl::getObjTarget();
		if($curObj->isNew() && MdlCalendar::addRight()==false)	{self::noAccessExit();}
		else													{$curObj->editControl();}
		////	Valide le formulaire
		if(Req::isParam("formValidate")){
			//Enregistre & recharge l'objet
			$timeSlot=Req::param("timeSlotBegin")."-".Req::param("timeSlotEnd");
			$typeCalendar=$curObj->isNew() ? ", type='ressource'" : null;
			$curObj=$curObj->createUpdate("title=".Db::param("title").", description=".Db::param("description").", timeSlot=".Db::format($timeSlot).", propositionNotify=".Db::param("propositionNotify").", propositionGuest=".Db::param("propositionGuest").$typeCalendar);
			static::lightboxClose();
		}
		////	Affiche la vue
		$vDatas["curObj"]=$curObj;
		$vDatas["hidePropositionGuest"]=(Db::getVal("SELECT count(*) FROM ap_space WHERE public=1")==0);//Masque l'option s'il n'y a pas d'espace public disponible
		static::displayPage("VueCalendarEdit.php",$vDatas);
	}

	/********************************************************************************************
	 * VUE : EDITION D'UN EVENEMENT D'AGENDA
	 ********************************************************************************************/
	public static function actionCalendarEventEdit()
	{
		////	INIT
		$curObj=Ctrl::getObjTarget();
		$curObj->editControl();
		////	VALIDE LE FORMULAIRE
		if(Req::isParam("formValidate"))
		{
			////	EDITE LES PRINCIPAUX CHAMPS DE L'ÉVÉNEMENT (titre, timeBegin..)
			if($curObj->fullRight())
			{
				//Prépare les dates
				$dateBegin=Txt::formatDate(Req::param("dateBegin")." ".Req::param("timeBegin"), "inputDatetime", "dbDatetime");
				$dateEnd=Txt::formatDate(Req::param("dateEnd")." ".Req::param("timeEnd"), "inputDatetime", "dbDatetime");
				//Périodicité
				$periodDateEnd=$periodValues=$periodDateExceptions=null;
				if(Req::isParam("periodType")){
					$periodDateEnd=Txt::formatDate(Req::param("periodDateEnd"), "inputDate", "dbDate");
					$periodValues=Txt::tab2txt(Req::param("periodValues_".Req::param("periodType")));
					if(Req::isParam("periodDateExceptions")){
						$periodDateExceptions=[];
						foreach(Req::param("periodDateExceptions") as $tmpDate)  {$periodDateExceptions[]=Txt::formatDate($tmpDate,"inputDate","dbDate");}
					}
				}
				//Invité : affiche une notif "Votre proposition sera examiné..."
				if(Ctrl::$curUser->isUser()==false && Req::isParam("guest"))  {Ctrl::notify("EDIT_guestElementRegistered");}
				//Enregistre & recharge l'objet
				$curObj=$curObj->createUpdate("title=".Db::param("title").", description=".Db::param("description").", dateBegin=".Db::format($dateBegin).", dateEnd=".Db::format($dateEnd).", _idCat=".Db::param("_idCat").", important=".Db::param("important").", contentVisible=".Db::param("contentVisible").", visioUrl=".Db::param("visioUrl").", periodType=".Db::param("periodType").", periodValues=".Db::format($periodValues).", periodDateEnd=".Db::format($periodDateEnd).", periodDateExceptions=".Db::formatTab2txt($periodDateExceptions).", guest=".Db::param("guest").", guestMail=".Db::param("guestMail"));
			}
			////	RÉINITIALISE SI BESOIN LES AFFECTATIONS/PROPOSITIONS AUX AGENDAS
			if(Req::isParam("reinitCalendars")){
				foreach(Req::param("reinitCalendars") as $idCal)  {$curObj->deleteAffectation($idCal,true);}
			}
			////	ATTRIBUE LES NOUVELLES AFFECTATIONS/PROPOSITIONS AUX AGENDAS
			$affectationCals=(array)Req::param("affectationCalendars");
			$propositionCals=(array)Req::param("propositionCalendars");
			$propositionIdUsers=[];
			foreach(array_merge($affectationCals,$propositionCals) as $idCal)
			{
				//Récupère l'agenda  &&  Verif si l'evt est déjà affecté à cet agenda
				$tmpCal=Ctrl::getObj("calendar",$idCal);
				if(in_array($tmpCal,MdlCalendar::affectationCalendars())){
					$isConfirmed=in_array($idCal,$affectationCals);
					Db::query("INSERT INTO ap_calendarEventAffectation SET _idEvt=".$curObj->_id.", _idCal=".$tmpCal->_id.", confirmed=".Db::format($isConfirmed));		//Ajoute l'affectation à l'agenda
					if($isConfirmed==false && $tmpCal->propositionNotify==true)  {$propositionIdUsers=array_merge($propositionIdUsers,$tmpCal->affectedUserIds(true));}	//Proposition : ajoute les proprios de l'agenda pour la notif mail
				}
			}
			////	NOTIFIE PAR MAIL LA PROPOSITION D'EVT (AUX GESTIONNAIRES/AUTEUR DES AGENDAS CONCERNES)
			if(!empty($propositionIdUsers)){
				$evtTitleDate=$curObj->title." : ".Txt::dateLabel($curObj->dateBegin,"basic",$curObj->dateEnd);
				$mailSubject=Txt::trad("CALENDAR_propositionEmailSubject")." ".$curObj->autorLabel();
				$mailMessage=str_replace(["--AUTOR_LABEL--","--EVT_TITLE_DATE--","--EVT_DESCRIPTION--"], [$curObj->autorLabel(),$evtTitleDate,$curObj->description], Txt::trad("CALENDAR_propositionEmailMessage"));
				Tool::sendMail($propositionIdUsers, $mailSubject, $mailMessage, ["noNotify"]);
			}
			////	NOTIFIE PAR MAIL LA CREATION D'EVT (AUX PERSONNES AFFECTEES AUX AGENDAS DE L'EVT)
			if(Req::isParam("notifMail") && $curObj->fullRight())
			{
				$objLabel=Txt::dateLabel($curObj->dateBegin,"basic",$curObj->dateEnd)." : <b>".$curObj->title."</b>";
				$icalPath=self::getIcal($curObj, true);
				$icsFile=[["path"=>$icalPath, "name"=>Txt::clean($curObj->title).".ics"]];
				$curObj->sendMailNotif($objLabel, $icsFile);
				File::rm($icalPath);
			}
			//Ferme la page
			static::lightboxClose();
		}
		////	AFFICHE LA VUE
		////	Liste des agendas pour les affectations
		$vDatas["affectationCalendars"]=MdlCalendar::affectationCalendars();
		//Evt créé par un autre user : ajoute si besoin les agendas inaccessibles pour l'user courant mais quand même affectés à l'événement
		if($curObj->isNew()==false && $curObj->isAutor()==false){
			$vDatas["affectationCalendars"]=array_merge($vDatas["affectationCalendars"], $curObj->affectedCalendars("all"));
			$vDatas["affectationCalendars"]=MdlCalendar::sortCalendars(array_unique($vDatas["affectationCalendars"],SORT_REGULAR));//"SORT_REGULAR" pour les objets
		}
		////	Prépare l'affichage de chaque agenda
		foreach($vDatas["affectationCalendars"] as $tmpCal){
			//Ajoute quelques propriétés à l'agenda
			$tmpCal->mainInput=($tmpCal->addContentRight() || in_array($tmpCal,$curObj->affectedCalendars()))  ?  "affectation"  :  "proposition";	//Input principal : "affectation" || "proposition" pour l'agenda
			$tmpCal->isChecked=($tmpCal->_id==Req::param("_idCal") || in_array($tmpCal,$curObj->affectedCalendars("all")))  ?  "checked"  :  null;	//Input principal : check l'agenda s'il est présélectionné ou déjà affecté
			$tmpCal->isDisabled=($tmpCal->addContentRight()==false && $curObj->fullRight()==false)  ?  "disabled"  :  null;							//Input principal : désactive l'agenda s'il n'est pas accessible en écriture && user courant pas auteur de l'evt
			$tmpCal->reinitCalendarInput=($curObj->isNew()==false && $tmpCal->isDisabled==null);													//Ajoute l'input "hidden" de réinitialisation de l'affectation : modif d'evt et input pas "disabled"
			//Tooltip du label principal
			if($tmpCal->isDisabled!=null)				{$tmpCal->labelTooltip=Txt::trad("CALENDAR_noModifTooltip");}			//"Modification non autorisé..." (tjs mettre en premier)
			elseif($tmpCal->mainInput=="affectation")	{$tmpCal->labelTooltip=Txt::trad("CALENDAR_addEvtTooltipBis");}			//"Ajouter l'événement..."
			else										{$tmpCal->labelTooltip=Txt::trad("CALENDAR_proposeEvtTooltipBis2");}	//"Proposer l'événement...agenda en lecture seule"
			//Ajoute la description de l'agenda ?
			if(!empty($tmpCal->description))  			{$tmpCal->labelTooltip.=" (".$tmpCal->description.")";}						
		}
		////	Nouvel evt : dates par défaut
		if($curObj->isNew()){
			$curObj->dateBegin =Req::isParam("newEvtTimeBegin")	?  date("Y-m-d H:i",Req::param("newEvtTimeBegin"))	:  date("Y-m-d H:00",time()+3600);							//date du jour, avec la prochaine heure courante
			$curObj->dateEnd   =Req::isParam("newEvtTimeEnd")	?  date("Y-m-d H:i",Req::param("newEvtTimeEnd"))	:  date("Y-m-d H:00",strtotime($curObj->dateBegin)+3600);	//une heure après l'heure de début
		}
		////	AFFICHE LA PAGE
		$vDatas["curObj"]=$curObj;
		$vDatas["tabPeriodValues"]=Txt::txt2tab($curObj->periodValues);
		foreach(Txt::txt2tab($curObj->periodDateExceptions) as $keyTmp=>$tmpException)	{$vDatas["periodDateExceptions"][$keyTmp+1]=Txt::formatDate($tmpException,"dbDate","inputDate");}
		$vDatas["curSpaceUserGroups"]=MdlUserGroup::getGroups(Ctrl::$curSpace);
		static::displayPage("VueCalendarEventEdit.php",$vDatas);
	}

	/********************************************************************************************
	 * AJAX : CONTROL DES CRÉNEAUX HORAIRES DES AGENDAS SÉLECTIONNÉS
	 ********************************************************************************************/
	public static function actionTimeSlotBusy()
	{
		if(Req::isParam(["dateTimeBegin","dateTimeEnd","objectsTypeId"]))
		{
			//Init
			$textTimeSlotBusy=null;
			$timeBegin=Txt::formatDate(Req::param("dateTimeBegin"),"inputDatetime","time")+1;	//Décale d'une sec. pour eviter les faux positifs. Ex: créneaux 11h-12h dispo, alors que 12h-13h est occupé
			$timeEnd=Txt::formatDate(Req::param("dateTimeEnd"),"inputDatetime","time")-1;		//idem. Ex: créneaux 11h-12h dispo, même si 12h-13h est occupé
			//Vérifie le créneau horaire sur chaque agenda sélectionné
			foreach(self::getObjectsTypeId() as $tmpCal)
			{
				$calendarBusy=$calendarBusyTimeSlots=null;
				//Evts de l'agenda sur la période sélectionné =>  $filterByCategory=false  & $orderByHourMinute=false  & $accessRightMin=0
				$eventListControled=$tmpCal->eventList($timeBegin, $timeEnd, false, false, 0);
				foreach(MdlCalendar::eventFilter($eventListControled,$timeBegin,$timeEnd) as $tmpEvt){
					if($tmpEvt->_id!=Req::param("_evtId")){//Sauf l'evt en cours d'édition (si modif)
						$calendarBusyTimeSlots.=" &nbsp; &nbsp; <img src='app/img/arrowRight.png'> ".Txt::dateLabel($tmpEvt->dateBegin,"basic",$tmpEvt->dateEnd)." ";
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

	/********************************************************************************************
	 * VUE : EVENEMENTS QUE L'USER COURANT A CRÉÉ
	 ********************************************************************************************/
	public static function actionMyEvents()
	{
		$vDatas["myEvents"]=Db::getObjTab("calendarEvent","SELECT * FROM ap_calendarEvent WHERE _idUser=".Ctrl::$curUser->_id." ORDER BY dateBegin");
		static::displayPage("VueMyEvents.php",$vDatas);
	}

	/********************************************************************************************
	 * VUE : AFFICHE LES PROPOSITIONS D'EVENEMENT DES AGENDAS GÉRÉS PAR L'USER COURANT
	 ********************************************************************************************/
	public static function eventProposition()
	{
		$vDatas["eventPropositions"]=[];
		//Parcourt chaque agenda géré par l'user courant
		foreach(MdlCalendar::writableCalendars() as $tmpCal){
			// Récupère les événements pas encore confirmés sur l'agenda
			foreach(Db::getObjTab("calendarEvent","SELECT T1.* FROM ap_calendarEvent T1, ap_calendarEventAffectation T2 WHERE T1._id=T2._idEvt AND T2._idCal=".$tmpCal->_id." AND T2.confirmed is null") as $tmpEvt){
				if($tmpEvt->isOldEvt(time()-5184000))	{$tmpEvt->deleteAffectation($tmpCal->_id);}							//Supprime la proposition si elle est obsolète (+ de 60 jours)
				else									{$vDatas["eventPropositions"][]=["evt"=>$tmpEvt,"cal"=>$tmpCal];}	//Sinon on ajoute la proposition d'evt à l'agenda!
			}
		}
		//Renvoie la vue s'il y a des propositions
		if(!empty($vDatas["eventPropositions"]))  {return self::getVue("app/ModCalendar/VueCalendarEventProposition.php", $vDatas);}
	}

	/********************************************************************************************
	 * AJAX : VALIDE / DECLINE UNE PROPOSITION D'ÉVÉNEMENT
	 ********************************************************************************************/
	public static function actionEventPropositionConfirm()
	{
		//Récupère l'agenda concerné et vérif le droit d'accès (cf. "typeId")
		$curCal=Ctrl::getObjTarget();
		if($curCal->editContentRight())
		{
			//Récup L'evt et l'email pour la notif
			$curEvt=Ctrl::getObj("calendarEvent",Req::param("_idEvt"));
			$notifMail=(!empty($curEvt->guestMail))  ?  $curEvt->guestMail  :  Ctrl::getObj("user",$curEvt->_idUser)->mail;
			//Valide/Invalide la proposition
			if(Req::isParam("isConfirmed"))	{Db::query("UPDATE ap_calendarEventAffectation SET confirmed=1 WHERE _idEvt=".(int)$curEvt->_id." AND _idCal=".$curCal->_id);}
			else							{$curEvt->deleteAffectation($curCal->_id);}
			//Envoi une notification par email
			if(!empty($notifMail)){
				$mailSubject=Req::isParam("isConfirmed")  ?  Txt::trad("CALENDAR_evtProposedConfirmMail")." ".Ctrl::$curUser->getLabel()  :  Txt::trad("CALENDAR_evtProposedDeclineMail");
				$mailMessage=$mailSubject." : <br><br>".
							 $curEvt->title." : ".Txt::dateLabel($curEvt->dateBegin,"basic",$curEvt->dateEnd)."<br><br>".
							 ucfirst(Txt::trad("OBJECTcalendar"))." : ".$curCal->title;
				Tool::sendMail($notifMail, $mailSubject, $mailMessage, ["noNotify"]);
			}
		}
	}

	/********************************************************************************************
	 * VUE : DÉTAILS D'UN ÉVÉNEMENT
	 ********************************************************************************************/
	public static function actionVueCalendarEvent()
	{
		$curObj=Ctrl::getObjTarget();
		$curObj->readControl();
		// visibilite / Catégorie
		if($curObj->contentVisible=="prive")			{$vDatas["contentVisibility"]=Txt::trad("CALENDAR_visibilityPrivate");}
		elseif($curObj->contentVisible=="public_cache")	{$vDatas["contentVisibility"]=Txt::trad("CALENDAR_visibilityPublicHide");}
		else											{$vDatas["contentVisibility"]=Txt::trad("CALENDAR_visibilityPublic");}
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
			if(!empty($curObj->periodDateEnd))	{$vDatas["labelPeriod"].="<br>".Txt::trad("CALENDAR_periodDateEnd")." : ".Txt::dateLabel($curObj->periodDateEnd,"dateMini");}
			//Périodicité : exceptions
			if(!empty($curObj->periodDateExceptions)){
				$vDatas["labelPeriod"].="<br>".Txt::trad("CALENDAR_periodException")." : ";
				$periodDateExceptions=array_filter(Txt::txt2tab($curObj->periodDateExceptions));//"array_filter" pour enlever les valeurs vides
				foreach($periodDateExceptions as $tmpVal)	{$vDatas["labelPeriod"].=Txt::dateLabel($tmpVal,"dateMini").", ";}
				$vDatas["labelPeriod"]=trim($vDatas["labelPeriod"], ", ");
			}
		}
		//Détails de l'événement
		$vDatas["curObj"]=$curObj;
		static::displayPage("VueCalendarEvent.php",$vDatas);
	}

	/********************************************************************************************
	 * IMPORT D'ÉVÉNEMENTS AU FORMAT .ICAL DANS UN AGENDA (cf. "MdlCalendar->contextMenu()")
	 ********************************************************************************************/
	public static function actionImportEvents()
	{
		//Charge et controle
		$objCalendar=Ctrl::getObjTarget();
		if($objCalendar->editContentRight()==false)  {Ctrl::noAccessExit();}
		$vDatas=[];
		////	Valide le formulaire : sélection du fichier / des evt à importer
		if(Req::isParam("formValidate"))
		{
			////	PRÉPARE LE TABLEAU D'IMPORT
			if(isset($_FILES["importFile"]) && is_file($_FILES["importFile"]["tmp_name"]))
			{
				//// Importe les événements via le Parser Ical.php
				require 'ICal.php';
				$ical=new ICal($_FILES["importFile"]["tmp_name"]);
				//// Formate les evenements à importer
				if(empty($ical->cal["VEVENT"]))  {Ctrl::notify("Ical import error");}
				else
				{
					//// Init la liste des evt à importer && Ignore les evt de plus d'un an ?
					$vDatas["eventList"]=[];
					$ignoreOldEvtTime=Req::isParam("ignoreOldEvt") ? strtotime("-1 year") : strtotime("-10 year");
					//// Parcourt chaque evt parsé par Ical.php
					foreach($ical->cal["VEVENT"] as $tmpEvt)
					{
						//// S'il manque de date/titre/UID || Si l'evt a déjà été ajouté (cf. evts périodiques de Google) : on zappe l'evt!
						if(empty($tmpEvt["DTSTART"]) || empty($tmpEvt["SUMMARY"]) || empty($tmpEvt["UID"]) || isset($vDatas["eventList"][$tmpEvt["UID"]]) || strtotime($tmpEvt["DTSTART"])<$ignoreOldEvtTime)  {continue;}
						//// Init les valeurs importées en Bdd
						$tmpEvt["dbDateBegin"]=$tmpEvt["dbDateEnd"]=$tmpEvt["dbTitle"]=$tmpEvt["dbDescription"]=$tmpEvt["dbPeriodType"]=$tmpEvt["dbPeriodValues"]=$tmpEvt["dbPeriodDateEnd"]=$tmpEvt["isPresent"]=null;
						//// Prépare l'evt (attention au décalage des timezones dans le fihier .ics : mais corrigé via le "strtotime()")
						$tmpEvt["dbDateBegin"]=date("Y-m-d H:i",strtotime($tmpEvt["DTSTART"]));
						if(!empty($tmpEvt["DTEND"])){
							$tmpEvt["dbDateEnd"]=date("Y-m-d H:i",strtotime($tmpEvt["DTEND"]));
							if(strlen($tmpEvt["DTEND"])==8)  {$tmpEvt["dbDateEnd"]=date("Y-m-d H:i",(strtotime($tmpEvt["DTEND"])-86400));}//Les événements "jour" sont importés avec un jour de trop (cf. exports depuis G-Calendar)
						}
						$tmpEvt["dbTitle"]=Txt::clean($tmpEvt["SUMMARY"]);
						if(!empty($tmpEvt["DESCRIPTION"]))  {$tmpEvt["dbDescription"]=Txt::clean($tmpEvt["DESCRIPTION"]);}
						//// Evenement périodique
						if(!empty($tmpEvt["RRULE"]))
						{
							//init
							$rruleTab=explode(";",$tmpEvt["RRULE"]);
							//Périodique : semaine
							if(stristr($tmpEvt["RRULE"],"FREQ=WEEKLY") && stristr($tmpEvt["RRULE"],"BYDAY=")){
								$tmpEvt["dbPeriodType"]="weekDay";
								foreach($rruleTab as $rruleTmp){//Jours de la période
									if(stristr($rruleTmp,"BYDAY="))  {$tmpEvt["dbPeriodValues"]=str_replace(['BYDAY=',',','MO','TU','WE','TH','FR','SA','SU'], ['','@@',1,2,3,4,5,6,7], $rruleTmp);}
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
						//// Etat de l'événement : à importer OU dejà present (..donc ne pas importer)
						$tmpEvt["isPresent"]=(Db::getVal("SELECT count(*) FROM ap_calendarEvent T1, ap_calendarEventAffectation T2 WHERE T1._id=T2._idEvt AND T2._idCal=".$objCalendar->_id." AND T1.title=".Db::format($tmpEvt["dbTitle"])." AND T1.dateBegin=".Db::format($tmpEvt["dbDateBegin"])." AND T1.dateEnd=".Db::format($tmpEvt["dbDateEnd"])) > 0);
						//// Ajoute l'evt
						$vDatas["eventList"][$tmpEvt["UID"]]=$tmpEvt;
					}
				}
			}
			////	IMPORTE LES ÉVÉNEMENTS
			elseif(Req::isParam("eventList"))
			{
				//Import de chaque événement
				foreach(Req::param("eventList") as $tmpEvt)
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

	/******************************************************************************************************
	 * DOWNLOAD LES ÉVÉNEMENTS D'UN AGENDA AU FORMAT .ICAL  (cf. "MdlCalendar->contextMenu()")
	 ******************************************************************************************************/
	public static function actionExportEvents()
	{
		////	Charge l'objet + Controle d'accès + Création du fichier .ical 
		$objCalendar=Ctrl::getObjTarget();
		$objCalendar->readControl();
		self::getIcal($objCalendar);
	}

	/********************************************************************************************
	 * CRÉATION DU FICHIER .ICAL : TEST VIA https://icalendar.org/validator.html
	 ********************************************************************************************/
	public static function getIcal($curObj, $tmpFile=false)
	{
		////	Init les retours à la ligne
		$newLine="\r\n";
		////	Evenement spécifié : récupère l'agenda principal
		if($curObj::objectType=="calendarEvent"){
			$eventList=[$curObj];
			$objCalendar=$curObj->containerObj();
			$icalCalname=null;
		}
		////	Agenda spécifié : récupère ses événements
		elseif($curObj::objectType=="calendar"){
			$periodBegin=time()-(86400*365);//Time - 1 an
			$periodEnd=time()+(86400*365*5);//Time + 5 ans
			$eventList=$curObj->eventList($periodBegin,$periodEnd,false,false,1);//$filterByCategory=false  & $orderByHourMinute=false  & $accessRightMin=1
			$objCalendar=$curObj;
			$icalCalname="X-WR-CALNAME:".$objCalendar->title.$newLine;
		}else{return false;}

		////	Prépare le fichier Ical
		$ical=	"BEGIN:VCALENDAR".$newLine.
				"METHOD:PUBLISH".$newLine.
				"VERSION:2.0".$newLine.
				$icalCalname.
				"PRODID:-//Agora-Project//".self::$agora->name."//EN".$newLine.
				"X-WR-TIMEZONE:".self::$curTimezone.$newLine.
				"CALSCALE:GREGORIAN".$newLine.
				"BEGIN:VTIMEZONE".$newLine.
				"TZID:".self::$curTimezone.$newLine.
				"X-LIC-LOCATION:".self::$curTimezone.$newLine.
				"BEGIN:DAYLIGHT".$newLine.
				"TZOFFSETFROM:".self::icalHour().$newLine.
				"TZOFFSETTO:".self::icalHour(1).$newLine.
				"TZNAME:CEST".$newLine.
				"DTSTART:19700329T020000".$newLine.
				"RRULE:FREQ=YEARLY;INTERVAL=1;BYDAY=-1SU;BYMONTH=3".$newLine.
				"END:DAYLIGHT".$newLine.
				"BEGIN:STANDARD".$newLine.
				"TZOFFSETFROM:".self::icalHour(1).$newLine.
				"TZOFFSETTO:".self::icalHour().$newLine.
				"TZNAME:CET".$newLine.
				"DTSTART:19701020T030000".$newLine.
				"RRULE:FREQ=YEARLY;INTERVAL=1;BYDAY=-1SU;BYMONTH=10".$newLine.
				"END:STANDARD".$newLine.
				"END:VTIMEZONE".$newLine;
		//Ajoute chaque evenement (plusieurs fois si l'evt est périodique)
		foreach($eventList as $tmpEvt)
		{
			//Init
			$icalPeriod=$periodDateEnd=$icalCategories=null;
			if($tmpEvt->periodDateEnd)  {$periodDateEnd=";UNTIL=".self::icalDate($tmpEvt->periodDateEnd);}
			//Périodicité (année/jour/mois)
			if($tmpEvt->periodType=="year"){
				$icalPeriod="RRULE:FREQ=YEARLY;INTERVAL=1".$periodDateEnd.$newLine;
			}elseif($tmpEvt->periodType=="weekDay" && !empty($tmpEvt->periodValues)){
				$tmpEvtPeriodValues=str_replace([1,2,3,4,5,6,7], ['MO','TU','WE','TH','FR','SA','SU'], Txt::txt2tab($tmpEvt->periodValues));
				$icalPeriod="RRULE:FREQ=WEEKLY;INTERVAL=1;BYDAY=".implode(",",$tmpEvtPeriodValues).$periodDateEnd.$newLine;
			}elseif($tmpEvt->periodType=="month"){
				$icalPeriod="RRULE:FREQ=MONTHLY;INTERVAL=1".$periodDateEnd.$newLine;
			}
			//Categories de l'agenda
			if(!empty($tmpEvt->_idCat))  {$icalCategories="CATEGORIES:".Ctrl::getObj("calendarCategory",$tmpEvt->_idCat)->title.$newLine;}
			//Description
			$icalDescription=$tmpEvt->description;																	//Description principale
			if(count($tmpEvt->affectedCalendars())>0)  {$icalDescription.=" - ".$tmpEvt->affectedCalendarsLabel();}	//Ajoute les agendas où l'evt est affecté
			if(!empty($tmpEvt->periodValues)){																		//Ajoute la périodicité
				$icalDescription.=" - ".Txt::trad("CALENDAR_period_".$tmpEvt->periodType)." : ";
				foreach(Txt::txt2tab($tmpEvt->periodValues) as $tmpVal){
					if($tmpEvt->periodType=="weekDay")		{$icalDescription.=Txt::trad("day_".$tmpVal).", ";}
					elseif($tmpEvt->periodType=="month")	{$icalDescription.=Txt::trad("month_".$tmpVal).", ";}
				}
			}
			$icalDescription="DESCRIPTION:".Txt::clean($icalDescription).$newLine;//Description nettoyé (tester import sur thunderbird & ical Validator)
			//Ajoute l'evenement
			$ical.= "BEGIN:VEVENT".$newLine.
					"CREATED:".self::icalDate($tmpEvt->dateCrea).$newLine.
					"UID:".$tmpEvt->md5Id().$newLine.
					"DTEND;TZID=".self::icalDate($tmpEvt->dateEnd,true).$newLine.
					"SUMMARY:".$tmpEvt->title.$newLine.
					"LAST-MODIFIED:".self::icalDate($tmpEvt->dateModif).$newLine.
					"DTSTAMP:".self::icalDate(date("Y-m-d H:i")).$newLine.
					"DTSTART;TZID=".self::icalDate($tmpEvt->dateBegin,true).$newLine.
					"DTEND;TZID=".self::icalDate($tmpEvt->dateEnd,true).$newLine.
					$icalPeriod.$icalCategories.$icalDescription.
					"SEQUENCE:0".$newLine.
					"END:VEVENT".$newLine;
		}
		//Fin du ical
		$ical.="END:VCALENDAR";

		////	Enregistre un fichier Ical temporaire et on renvoie son "Path"
		if($tmpFile==true){
			$tmpFilePath=tempnam(File::getTempDir(),"exportIcal".uniqid());
			$fp=fopen($tmpFilePath, "w");
			fwrite($fp,$ical);
			fclose($fp);
			return $tmpFilePath;
		}
		////	Affiche directement le fichier .Ical
		else{
			header("Content-type: text/calendar; charset=utf-8");
			header("Content-Disposition: inline; filename=".Txt::clean($objCalendar->title)."_".date("d-m-Y").".ics");
			echo $ical;
		}
	}

	/********************************************************************************************
	 * EXPORT .ICAL : FORMATE L'HEURE
	 ********************************************************************************************/
	public static function icalHour($timeLag=0)
	{
		// Exemple avec "-5:30"
		$hourTimezone=Tool::$tabTimezones[self::$curTimezone];
		$valueSign=(substr($hourTimezone,0,1)=="-") ? '-' : '+';				//"-"
		$hourAbsoluteVal=str_replace(['-','+'],'',substr($hourTimezone,0,-3));	//"5"
		$hourAbsoluteVal+=$timeLag;												//Si $timeLag=2 -> "7"
		if($hourAbsoluteVal<10)	{$hourAbsoluteVal="0".$hourAbsoluteVal;}		//"05"
		$minutes=substr($hourTimezone,-2);										//"30"
		return $valueSign.$hourAbsoluteVal.$minutes;//Retourne "-0530"
	}

	/********************************************************************************************
	 * EXPORT .ICAL : FORMATE LA DATE
	 ********************************************************************************************/
	public static function icalDate($dateTime, $timezone=false)
	{
		if(!empty($dateTime)){
			$dateTime=date("Ymd",strtotime($dateTime))."T".date("Hi",strtotime($dateTime))."00";//Ex: "20151231T235900Z"
			return ($timezone==true) ? self::$curTimezone.":".$dateTime : str_replace("T000000Z","T235900Z",$dateTime."Z");
		}
	}
}