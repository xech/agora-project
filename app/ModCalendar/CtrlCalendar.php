<?php
/**
* This file is part of the Agora-Project Software package
*
* @copyleft Agora-Project <https://www.agora-project.net>
* @license GNU General Public License (GPL-2.0)
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
		////	INIT : LISTE D'AGENDAS / MODE D'AFFICHAGE / TPS DE REFERENCE
		$vDatas["readableCalendars"]=(!empty($_SESSION["displayAdmin"]))  ?  MdlCalendar::affectationCalendars()  :  MdlCalendar::readableCalendars();	//Agendas visibles : "readable" / "affectation" ("displayAdmin")
		$vDatas["displayedCalendars"]=MdlCalendar::displayedCalendars($vDatas["readableCalendars"]);													//Agendas affichés
		$smallDisplay=(isset($_COOKIE["windowWidth"]) && $_COOKIE["windowWidth"]<=600);																	//Affichage réduit (600px de width max)
		$displayMode=self::prefUser("calendarDisplayMode","displayMode");																				//Mode d'affichage en preference
		if(empty($displayMode))  {$displayMode="month";}																								//Sinon "month" par défaut
		if($smallDisplay==true && preg_match("/(week|7Days)/i",$displayMode))	{$displayMode="3Days";}													//Affichage réduit : switch de "week"/"7Days" à "3Days"
		elseif($smallDisplay==false && $displayMode=="3Days")					{$displayMode="week";}													//Affichage normal : switch de "3Days" à "week"
		$vDatas["displayMode"]=$displayMode;																											//Affichage courant
		$vDatas["displayModeList"]=($smallDisplay==true)  ?  ["month","3Days","1Day"]  :  ["month","week","workWeek","7Days"];							//Affichages disponibles
		$vDatas["curTime"]=$curTime=Req::isParam("curTime")  ?  Req::param("curTime")  :  time();														//Temps de référence

		////	MENU DES PROPOSITIONS D'EVT
		$vDatas["eventPropositions"]=[];
		foreach(MdlCalendar::writableCalendars() as $tmpCal){																//Liste les evts à confirmer pour chaque agenda de l'user
			foreach(Db::getObjTab("calendarEvent","SELECT T1.* FROM ap_calendarEvent T1, ap_calendarEventAffectation T2 WHERE T1._id=T2._idEvt AND T2._idCal=".$tmpCal->_id." AND T2.confirmed is null") as $tmpEvt){
				if($tmpEvt->isOldEvt(time()-5184000))	{$tmpEvt->deleteAffectation($tmpCal->_id);}							//Supprime la proposition si + de 60 jours
				else									{$vDatas["eventPropositions"][]=["evt"=>$tmpEvt,"cal"=>$tmpCal];}	//Ajoute la proposition d'evt
			}
		}

		////	DEBUT/FIN DE PERIODE : EN FONCTION DE $displayMode
		if($displayMode=="month")			{$strBegin="first day of this month 00:00:00";	$strEnd="last day of this month 23:59:59";	$strPrev="-1 month";	$strNext="+1 month";}
		elseif($displayMode=="week")		{$strBegin="monday this week 00:00:00";			$strEnd="sunday this week 23:59:59";		$strPrev="-1 week";		$strNext="+1 week";}
		elseif($displayMode=="workWeek")	{$strBegin="monday this week 00:00:00";			$strEnd="friday this week 23:59:59";		$strPrev="-1 week";		$strNext="+1 week";}
		elseif($displayMode=="7Days")		{$strBegin="today 00:00:00";					$strEnd="+6 day 23:59:59";					$strPrev="-7 day";		$strNext="+7 day";}
		elseif($displayMode=="3Days")		{$strBegin="today 00:00:00";					$strEnd="+2 day 23:59:59";					$strPrev="-3 day";		$strNext="+3 day";}
		elseif($displayMode=="1Day")		{$strBegin="today 00:00:00";					$strEnd="today 23:59:59";					$strPrev="-1 day";		$strNext="+1 day";}
		$timeBegin			=strtotime($strBegin,$curTime);		//Début de période affichée
		$timeEnd			=strtotime($strEnd,  $curTime);		//Fin de période affichée
		$vDatas["timePrev"]	=strtotime($strPrev, $timeBegin);	//Période précédente (tjs prendre $timeBegin en référence!)
		$vDatas["timeNext"]	=strtotime($strNext, $timeBegin);	//Période suivante (idem)

		////	LISTE DES JOURS AFFICHÉS
		if($displayMode=="month")	{$showTimeBegin=strtotime("monday this week 00:00:00",$timeBegin);  $showTimeEnd=strtotime("sunday this week 23:59:59",$timeEnd);}//Monday 1st week of month / Sunday last week of month
		else						{$showTimeBegin=$timeBegin;  $showTimeEnd=$timeEnd;}
		$publicHolidays =Trad::publicHolidays(date('Y',$curTime));											//Jours fériés de l'année
		$timeChangeDates=Txt::timeChangeDates(date('Y',$curTime));											//Temps de référence
		for($dayTimeBegin=$showTimeBegin; $dayTimeBegin<=$showTimeEnd; $dayTimeBegin+=86400){				//Timestamps des jours affichées
			$dayYmd=date('Y-m-d',$dayTimeBegin);															//Jour Y-m-d
			$vDatas["periodDays"][$dayYmd]=[																//Ajoute le jour à la liste (Ymd en clé)
				"dayTimeBegin" 		=>strtotime("today 00:00:00", $dayTimeBegin),							//Timestamp de début du jour
				"dayTimeEnd"		=>strtotime("today 23:59:59", $dayTimeBegin),							//Timestamp de fin du jour
				"publicHoliday"		=>(isset($publicHolidays[$dayYmd]) ? $publicHolidays[$dayYmd] : null),	//Label du jour ferie ?
				"timeChangeSummer"	=>($dayYmd==$timeChangeDates["summer"]),								//Jour de changement d'heure d'été
				"timeChangeWinter"	=>($dayYmd==$timeChangeDates["winter"])									//Jour de changement d'heure d'hiver
			];
		}

		////	LABEL DU MOIS AFFICHÉ
		if(date('Ym',$timeBegin)!=date('Ym',$timeEnd))	{$vDatas["monthLabel"]=Txt::timeLabel($timeBegin,'MMM')." / ".Txt::timeLabel($timeEnd,'MMM');}	//"Fev./Mar."	: semaine sur 2 mois
		else											{$vDatas["monthLabel"]=Txt::timeLabel($timeBegin,'MMMM');}										//"Fevrier"		: format par défaut
		if(Req::isMobile()==false || date('Y')!=date('Y',$timeBegin))	{$vDatas["monthLabel"].=" ".date('Y',$timeBegin);}								//Ajoute l'année (sauf mobile + année courante)

		////	MENU CONTEXTUEL DES ANNÉES/MOIS
		$vDatas["monthsYearsMenu"]=null;
		for($monthNb=1; $monthNb<=12; $monthNb++){
			$monthTime=strtotime(date('Y',$curTime).'/'.$monthNb.'/1');
			$vDatas["monthsYearsMenu"].='<a onclick="redir(\'?ctrl=calendar&curTime='.$monthTime.'\')" class="'.(date('Y-m',$curTime)==date('Y-m',$monthTime)?'optionSelect':'option').'">'.Txt::timeLabel($monthTime,'MMMM').'</a>';
		}
		$vDatas["monthsYearsMenu"].='<hr>';
		for($yearNb=date('Y')-5; $yearNb<=date('Y')+3; $yearNb++){
			$yearTime=strtotime($yearNb.'-'.date('m',$curTime).'-01');
			$vDatas["monthsYearsMenu"].='<a onclick="redir(\'?ctrl=calendar&curTime='.$yearTime.'\')" class="'.(date('Y',$curTime)==$yearNb?'optionSelect':'option').'">'.$yearNb.'</a>';
		}

		////	RECUPERE LA VUE DE CHAQUE AGENDA ("VueCalendarMonth.php" / "VueCalendarWeek.php")  &&   LA LISTE DES EVENEMENTS
		foreach($vDatas["displayedCalendars"] as $cptCal=>$tmpCal)
		{
			//// LABEL D'AJOUT D'ÉVÉNEMENT / PROPOSITION D'ÉVÉNEMENT
			if($tmpCal->addContentRight())		{$tmpCal->addEventLabel=Txt::trad("CALENDAR_addEvtTooltip");}
			elseif($tmpCal->addOrProposeEvt())	{$tmpCal->addEventLabel=Txt::trad("CALENDAR_proposeEvtTooltip");}
			else								{$tmpCal->addEventLabel=null;}
			//// EVENEMENTS POUR CHAQUE JOUR
			$tmpCal->evtListDays=[];																													//Init la liste des evts pour chaque jour affiché
			$tmpCal->evtListDisplayed=$tmpCal->evtList($showTimeBegin, $showTimeEnd, 0.5, true);														//Evts sur la période affichée ($accessRightMin=0.5, $categoryFilter=true)
			foreach($vDatas["periodDays"] as $dayYmd=>$tmpDay){
				$tmpCal->evtListDays[$dayYmd]=[];																										//Init la liste des evts de l'agenda
				$evtListDay=MdlCalendar::dayEvtList($tmpCal->evtListDisplayed,$tmpDay["dayTimeBegin"],$tmpDay["dayTimeEnd"]);							//Récupère uniquement les evts de la journée
				foreach($evtListDay as $tmpEvt){																										//Parcourt chaque événement du jour :
					$tmpEvt->tooltip=$tmpEvt->title.'<br>'.Txt::dateLabel($tmpEvt->timeBegin,"labelFull",$tmpEvt->timeEnd);								//Tooltip avec title et date détaillée
					if(!empty($tmpEvt->important))	{$tmpEvt->title.='<img src="app/img/calendar/importantSmall.png">';}								//Icone d'evt important
					if(!empty($tmpEvt->periodType))	{$tmpEvt->title.='<img src="app/img/calendar/periodSmall.png">';}									//Icone d'evt periodique
					$tmpEvt->evtAttributes=$tmpEvt->attributes("string", $tmpDay["dayTimeBegin"], $tmpDay["dayTimeEnd"]);								//Attributs de l'evt
					$tmpEvt->contextMenuOptions=["launcherIcon"=>"floatSmall", "_idCal"=>$tmpCal->_id, "curDateTime"=>strtotime($tmpEvt->dateBegin)];	//Options du menu contextuel
					$tmpCal->evtListDays[$dayYmd][]=$tmpEvt;																							//Ajoute l'evt à la liste !
				}
				////	Tri des evts par Heure: Minute
				usort($tmpCal->evtListDays[$dayYmd],function($objA,$objB){
					return (date("Hi",$objA->timeBegin) - date("Hi",$objB->timeBegin));
				});
			}
			//// RÉCUPÈRE ENFIN LA VUE DE L'AGENDA
			$tmpCal->isFirstCal=($cptCal==0);
			$vCalDatas=$vDatas;
			$vCalDatas["tmpCal"]=$tmpCal;
			$calendarVue=($displayMode=="month") ? "VueCalendarMonth.php" : "VueCalendarWeek.php";
			$tmpCal->calendarVue=self::getVue(Req::curModPath().$calendarVue, $vCalDatas);
		}

		////	SYNTHESE DES AGENDAS (SI + D'UN AGENDA)
		if(count($vDatas["displayedCalendars"])>1 && Req::isMobile()==false){
			$vDatas["periodSynthese"]=[];																													//Jours à afficher pour la synthese
			foreach($vDatas["periodDays"] as $dayYmd=>$tmpDay){																								//Parcourt chaque jour affiché
				if($displayMode=="month" && date("m",$tmpDay["dayTimeBegin"])!=date("m",$curTime))  {continue;}												//Continue si le jour est hors période du mois affiché
				$tmpDay["dayEvtList"]=[];																													//Init les evts du jour
				foreach($vDatas["displayedCalendars"] as $tmpCal)																							//Parcourt chaque agenda affiché
					{$tmpDay["dayEvtList"][$tmpCal->_id]=MdlCalendar::dayEvtList($tmpCal->evtListDisplayed,$tmpDay["dayTimeBegin"],$tmpDay["dayTimeEnd"]);}	//Récupère les evts du jour
				$vDatas["periodSynthese"][$dayYmd]=$tmpDay;																									//Ajoute le jour de la synthese
			}
		}
	
		////	AFFICHE LA VUE
		static::displayPage("VueIndex.php",$vDatas);
	}

	/********************************************************************************************
	 * AJAX : MODIFIE LE DÉBUT/FIN D'UN EVT VIA DRAG/DROP
	 ********************************************************************************************/
	public static function actionEvtChangeTime()
	{
		//// Récupère l'evt et Controle l'accès
		$curObj=Ctrl::getObjTarget();
		if($curObj->fullRight()==false || Req::isParam("newTimeBegin")==false)  {$result["error"]=true;}
		//// Controle Ok
		else{
			// Update la date de l'evt en Bdd
			$timeDiff=strtotime($curObj->dateEnd)-strtotime($curObj->dateBegin);
			$newTimeBegin=(int)Req::param("newTimeBegin");
			$newTimeEnd=($newTimeBegin+$timeDiff);
			$curObj=$curObj->createUpdate("dateBegin=".Db::format(date("Y-m-d H:i",$newTimeBegin)).", dateEnd=".Db::format(date("Y-m-d H:i",$newTimeEnd)));
			//Renvoi les nouvelles propriétés de l'evt
			$result["attributes"]=$curObj->attributes("array", strtotime("today 00:00:00",$newTimeBegin), strtotime("today 23:59:59",$newTimeBegin));
			$result["evtLabelDate"]=Txt::dateLabel($curObj->timeBegin,"mini",$curObj->timeEnd);
			$result["tooltip"]=$curObj->title.'<br>'.Txt::dateLabel($curObj->timeBegin,"labelFull",$curObj->timeEnd);
			$result["changed"]=true;
		}
		//// Retourne le résultat
		echo json_encode($result);
	}

	/********************************************************************************************
	 * PLUGINS DU MODULE
	 ********************************************************************************************/
	public static function getPlugins($params)
	{
		$pluginsList=[];
		//// Liste les agendas visibles
		foreach(MdlCalendar::readableCalendars() as $tmpCal){
			//// Liste des evts ($durationBegin=null, $durationEnd=null, $accessRightMin=1, $categoryFilter=false, $pluginParams=$params)
			foreach($tmpCal->evtList(null, null, 1, false, $params) as $tmpEvt){
				//// Vérif si l'evt n'a pas déjà été ajouté (car peut être affecté à plusieurs agendas) && se limite à 100 evt max (cf. affichage des nouveaux evt après import de fichier Ical)
				if(empty($pluginsList[$tmpEvt->_typeId]) && count($pluginsList)<100){
					$tmpEvt->pluginIcon=self::moduleName."/icon.png";
					$tmpEvt->pluginLabel=Txt::dateLabel($tmpEvt->dateBegin,"dateFull",$tmpEvt->dateEnd)." : ".$tmpEvt->title;
					$tmpEvt->pluginTooltip=Txt::dateLabel($tmpEvt->dateBegin,"labelFull",$tmpEvt->dateEnd)."<hr>".$tmpEvt->affectedCalendarsLabel();
					$tmpEvt->pluginJsIcon="window.top.redir('".$tmpEvt->getUrl()."')";//Affiche l'evt dans son principal agenda (surcharge "getUrl()")
					$tmpEvt->pluginJsLabel=$tmpEvt->openVue();//Affiche l'evt en détail
					$pluginsList[$tmpEvt->_typeId]=$tmpEvt;
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
			static::lightboxRedir();
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
			//// EDITE LES PRINCIPAUX CHAMPS DE L'ÉVÉNEMENT (titre, timeBegin..)
			if($curObj->fullRight())
			{
				//Prépare les dates
				$dateBegin=Txt::formatDate(Req::param("dateBegin")." ".Req::param("timeBegin"), "inputDatetime", "dbDatetime");
				$dateEnd  =Txt::formatDate(Req::param("dateEnd")." ".Req::param("timeEnd"), "inputDatetime", "dbDatetime");
				//Périodicité / répétition de l'evt
				$periodDateEnd=$periodValues=$periodDateExceptions=null;
				if(Req::isParam("periodType")){
					$periodDateEnd=Txt::formatDate(Req::param("periodDateEnd"), "inputDate", "dbDate");
					$periodValues=Txt::tab2txt(Req::param("periodValues_".Req::param("periodType")));
					if(Req::isParam("periodDateExceptions")){
						$periodDateExceptions=[];
						foreach(Req::param("periodDateExceptions") as $tmpDay)  {$periodDateExceptions[]=Txt::formatDate($tmpDay,"inputDate","dbDate");}
					}
				}
				//Invité : affiche une notif "Votre proposition sera examiné..."
				if(Ctrl::$curUser->isUser()==false && Req::isParam("guest"))  {Ctrl::notify("EDIT_guestElementRegistered");}
				//Enregistre & recharge l'objet
				$curObj=$curObj->createUpdate("title=".Db::param("title").", description=".Db::param("description").", dateBegin=".Db::format($dateBegin).", dateEnd=".Db::format($dateEnd).", _idCat=".Db::param("_idCat").", important=".Db::param("important").", contentVisible=".Db::param("contentVisible").", visioUrl=".Db::param("visioUrl").", periodType=".Db::param("periodType").", periodValues=".Db::format($periodValues).", periodDateEnd=".Db::format($periodDateEnd).", periodDateExceptions=".Db::formatTab2txt($periodDateExceptions));
			}
			//// RÉINITIALISE SI BESOIN LES AFFECTATIONS/PROPOSITIONS AUX AGENDAS
			if(Req::isParam("reinitCalendars")){
				foreach(Req::param("reinitCalendars") as $idCal)  {$curObj->deleteAffectation($idCal,true);}
			}
			//// ATTRIBUE LES NOUVELLES AFFECTATIONS/PROPOSITIONS AUX AGENDAS
			$affectationCals=(array)Req::param("affectationCalendars");
			$propositionCals=(array)Req::param("propositionCalendars");
			$propositionIdUsers=[];
			foreach(array_merge($affectationCals,$propositionCals) as $idCal){
				//Récupère l'agenda  &&  Verif si l'evt est déjà affecté à cet agenda
				$tmpCal=Ctrl::getObj("calendar",$idCal);
				if(in_array($tmpCal,MdlCalendar::affectationCalendars())){
					$isConfirmed=in_array($idCal,$affectationCals);
					Db::query("INSERT INTO ap_calendarEventAffectation SET _idEvt=".$curObj->_id.", _idCal=".$tmpCal->_id.", confirmed=".Db::format($isConfirmed));		//Ajoute l'affectation à l'agenda
					if($isConfirmed==false && $tmpCal->propositionNotify==true)  {$propositionIdUsers=array_merge($propositionIdUsers,$tmpCal->affectedUserIds(true));}	//Proposition : ajoute les proprios de l'agenda pour la notif mail
				}
			}
			//// NOTIFIE PAR MAIL LA PROPOSITION D'EVT (AUX GESTIONNAIRES/AUTEUR DES AGENDAS CONCERNES)
			if(!empty($propositionIdUsers)){
				$evtTitleDate=$curObj->title." : ".Txt::dateLabel($curObj->dateBegin,"labelFull",$curObj->dateEnd);
				$mailSubject=Txt::trad("CALENDAR_propositionEmailSubject")." ".$curObj->autorLabel();
				$mailMessage=str_replace(["--AUTOR_LABEL--","--EVT_TITLE_DATE--","--EVT_DESCRIPTION--"], [$curObj->autorLabel(),$evtTitleDate,$curObj->description], Txt::trad("CALENDAR_propositionEmailMessage"));
				Tool::sendMail($propositionIdUsers, $mailSubject, $mailMessage, ["noNotify"]);
			}
			//// NOTIFIE PAR MAIL LA CREATION D'EVT (AUX PERSONNES AFFECTEES AUX AGENDAS DE L'EVT)
			if(Req::isParam("notifMail") && $curObj->fullRight())
			{
				$objLabel=Txt::dateLabel($curObj->dateBegin,"labelFull",$curObj->dateEnd)." : <b>".$curObj->title."</b>";
				$icalPath=self::getIcal($curObj, true);
				$icsFile=[["path"=>$icalPath, "name"=>Txt::clean($curObj->title).".ics"]];
				$curObj->sendMailNotif($objLabel, $icsFile);
				File::rm($icalPath);
			}
			//Ferme la page
			static::lightboxRedir();
		}
		////	AFFICHE LE FORMULAIRE
		//// Liste des agendas pour les affectations
		$vDatas["affectationCalendars"]=MdlCalendar::affectationCalendars();
		//Evt créé par un autre user : ajoute si besoin les agendas inaccessibles pour l'user courant mais quand même affectés à l'événement
		if($curObj->isNew()==false && $curObj->isAutor()==false){
			$vDatas["affectationCalendars"]=array_merge($vDatas["affectationCalendars"], $curObj->affectedCalendars("all"));
			$vDatas["affectationCalendars"]=MdlCalendar::sortCalendars(array_unique($vDatas["affectationCalendars"],SORT_REGULAR));//"SORT_REGULAR" pour les objets
		}
		//// Prépare l'affichage de chaque agenda
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
		//// Nouvel evt : dates par défaut
		if($curObj->isNew()){
			$curObj->dateBegin =Req::isParam("newEvtTimeBegin")	?  date("Y-m-d H:i",Req::param("newEvtTimeBegin"))	:  date("Y-m-d H:00",time()+3600);							//date du jour, avec la prochaine heure courante
			$curObj->dateEnd   =Req::isParam("newEvtTimeEnd")	?  date("Y-m-d H:i",Req::param("newEvtTimeEnd"))	:  date("Y-m-d H:00",strtotime($curObj->dateBegin)+3600);	//une heure après l'heure de début
		}
		//// Affiche la vue
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
		if(Req::isParam(["dateTimeBegin","dateTimeEnd","calendarIds"])){							//Vérif la présence des params
			$timeSlotBusy=null;																		//Init le TimeSlotBusy final
			$timeSlotBegin=Txt::formatDate(Req::param("dateTimeBegin"),"inputDatetime","time")+1;	//Début/fin du timeSlot : décale de 1 sec. pour eviter les faux positifs (ex: 11h-12h recherché mais dispo et 12h-13h occupé)
			$timeSlotEnd  =Txt::formatDate(Req::param("dateTimeEnd"),"inputDatetime","time")-1;		//Idem
			$timeSlotDayBegin=strtotime(date("Y-m-d 00:00:00",$timeSlotBegin));						//Début/fin du jour du timeSlot (récupère ainsi les récurrences)
			$timeSlotDayEnd=strtotime(date("Y-m-d 23:59:59",$timeSlotEnd));							//Idem
			foreach(Req::param("calendarIds") as $calId){											//Parcourt les agendas sélectionnés (pas de "getObjectsTypeId()" sinon on ne récupère pas les agendas pour "proposition")
				$tmpCal=self::getObj("calendar", $calId);											//Récupère l'agenda
				$timeSlotBusyCalendar=null;															//Init le TimeSlotBusy de l'agenda
				foreach($tmpCal->evtList($timeSlotDayBegin, $timeSlotDayEnd, 0) as $tmpEvt){									//Evts sur le jour du timeSlot ($accessRightMin=0) : récupère ainsi les evts périodiques
					if(MdlCalendar::evtInDuration($tmpEvt,$timeSlotBegin,$timeSlotEnd) && $tmpEvt->_id!=Req::param("_evtId")){	//Vérif si l'evt s'il est sur le timeSlot (pas celui en cours d'édition : cf. modif d'evt)
						$evtTitle=($tmpCal->readRight())  ?  " - ".Txt::reduce($tmpEvt->title,80)  :  null;						//Title de l'evt si accès en lecture à l'agenda (pas un proposition d'evt)
						$timeSlotBusyCalendar.='<span title="'.Txt::dateLabel($tmpEvt->dateBegin,"labelFull",$tmpEvt->dateEnd).'<br>'.$tmpEvt->title.'"><img src="app/img/arrowRightBig.png">'.Txt::dateLabel($tmpEvt->dateBegin,"mini",$tmpEvt->dateEnd).$evtTitle.'</span>';
					}
				}
				//L'agenda est occupé?
				if(!empty($timeSlotBusyCalendar))  {$timeSlotBusy.="<table><tr><td>".$tmpCal->title."</td><td>".$timeSlotBusyCalendar."</td></tr></table>";}
			}
			//Retourne le message
			echo $timeSlotBusy;
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
				$mailSubject=Req::isParam("isConfirmed")  ?  Txt::trad("CALENDAR_evtProposeConfirmedMail")." ".Ctrl::$curUser->getLabel()  :  Txt::trad("CALENDAR_evtProposeDeclinedMail");
				$mailMessage=$mailSubject." : <br><br>".
							 $curEvt->title." : ".Txt::dateLabel($curEvt->dateBegin,"labelFull",$curEvt->dateEnd)."<br><br>".
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
		$curObj->readControl();							//Controle d'accès
		$vDatas["curObj"]=$curObj;						//Récup l'evt
		$vDatas["labelPeriod"]=$curObj->periodLabel();	//Périodicité de l'événement
		if($curObj->contentVisible=="prive")				{$vDatas["contentVisibility"]=Txt::trad("CALENDAR_visibilityPrivate");}		//Visibilité privée
		elseif($curObj->contentVisible=="public_cache")		{$vDatas["contentVisibility"]=Txt::trad("CALENDAR_visibilityPublicHide");}	//Public détails masqués
		static::displayPage("VueCalendarEvent.php",$vDatas);	//Affiche lapage
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
				require 'ICalParser.php';
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
						$tmpEvt["dbTitle"]=Txt::clean($tmpEvt["SUMMARY"],"min");
						if(!empty($tmpEvt["DESCRIPTION"]))  {$tmpEvt["dbDescription"]=Txt::clean($tmpEvt["DESCRIPTION"],"min");}
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
										$tmpEvt["dbPeriodDateEnd"]=date('Y-m-d', strtotime($tmpEvt["dbPeriodDateEnd"]));}
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
				static::lightboxRedir();
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

	/****************************************************************************************************************
	 * EXPORT .ICAL :
	 * Tester affichage Thunderbird / Outlook / Google Calendar  &&  tester https://icalendar.org/validator.html
	 ****************************************************************************************************************/
	public static function getIcal($curObj, $tmpFile=false)
	{
		////	Retour à la ligne
		$RL="\r\n";
		////	Evenement spécifié : récupère l'agenda principal
		if($curObj::objectType=="calendarEvent"){
			$eventList=[$curObj];
			$objCalendar=$curObj->containerObj();
		}
		////	Agenda spécifié : récupère ses événements
		elseif($curObj::objectType=="calendar"){
			$eventList=$curObj->evtList(null, null, 1);//$durationBegin=null, $durationEnd=null, $accessRightMin=1
			$objCalendar=$curObj;
		}

		////	Créé un fichier Ical avec les événements
		if(!empty($eventList))
		{
			////	Entête
			$ical=  'BEGIN:VCALENDAR'.$RL.
					'VERSION:2.0'.$RL.
					'PRODID:-//Omnispace.fr//Omnispace Calendar//EN'.$RL.
					'CALSCALE:GREGORIAN'.$RL.
					'METHOD:PUBLISH'.$RL.
					'NAME:'.Txt::clean($objCalendar->title).$RL.
					'DESCRIPTION:'.Txt::clean($objCalendar->description).$RL.
					'X-WR-CALNAME:'.Txt::clean($objCalendar->title).$RL.
					'X-WR-TIMEZONE:'.self::$curTimezone.$RL.
					'BEGIN:VTIMEZONE'.$RL.
					'TZID:'.self::$curTimezone.$RL.
					"BEGIN:STANDARD".$RL.
					"DTSTART:19981025T020000".$RL.
					"TZOFFSETFROM:".self::icalHour().$RL.
					"TZOFFSETTO:".self::icalHour(1).$RL.
					"TZNAME:EST".$RL.
					"END:STANDARD".$RL.
					"BEGIN:DAYLIGHT".$RL.
					"DTSTART:19990404T020000".$RL.
					"TZOFFSETFROM:".self::icalHour().$RL.
					"TZOFFSETTO:".self::icalHour(1).$RL.
					"TZNAME:EDT".$RL.
					"END:DAYLIGHT".$RL.
					"END:VTIMEZONE".$RL;

			////	Ajoute chaque evenement (plusieurs fois si l'evt est périodique)
			foreach($eventList as $tmpEvt)
			{
				//// Init
				$evtDescription=$evtCategory=$evtPeriod=$evtPeriodExcept=null;
				//// Description
				if(!empty($tmpEvt->description)){
					$evtDescription=Txt::clean($tmpEvt->description,"min");														//Anlève les balises etc. (attention : peut renvoyer une chaine vide!)
					if($tmpEvt->periodLabel())	{$evtDescription.="\\n".Txt::clean($tmpEvt->periodLabel(),"min");}				//Détails de périodicité dans la description ("\n" explicite)
					if(!empty($evtDescription))  {$evtDescription="DESCRIPTION:".wordwrap($evtDescription, 60, $RL."  ").$RL;}	//Ajoute la description pas vide
				}
				//// Categorie de l'agenda
				if(!empty($tmpEvt->_idCat))
					{$evtCategory='CATEGORIES:'.Ctrl::getObj("calendarCategory",$tmpEvt->_idCat)->title.$RL;}
				//// Périodicité / répétition
				if(!empty($tmpEvt->periodType)){
					if($tmpEvt->periodType=="year")			{$evtPeriod='RRULE:FREQ=YEARLY;INTERVAL=1';}										//Chaque année
					elseif($tmpEvt->periodType=="month")	{$evtPeriod='RRULE:FREQ=MONTHLY;INTERVAL=1';}										//Chaque mois
					elseif($tmpEvt->periodType=="weekDay" && !empty($tmpEvt->periodValues)){													//Chaque semaine
						$tmpEvtBYDAY=str_replace([1,2,3,4,5,6,7], ['MO','TU','WE','TH','FR','SA','SU'], Txt::txt2tab($tmpEvt->periodValues));	//Jours de la semaine
						$evtPeriod='RRULE:FREQ=WEEKLY;INTERVAL=1;BYDAY='.implode(',',$tmpEvtBYDAY);												//Ajoute le "RRULE"
					}
					if(!empty($evtPeriod)){
						if(!empty($tmpEvt->periodDateEnd))  {$evtPeriod.=';UNTIL='.self::icalDate($tmpEvt->periodDateEnd." 23:59:59");}			//Ajoute si besoin la date de fin (avec l'heure à 23:59:59)
						$evtPeriod.=$RL;																										//Fin de ligne du "RRULE"
					}	
				}
				//// Exceptions de périodicité
				if(!empty($tmpEvt->periodDateExceptions)){
					$periodDateExceptions=Txt::txt2tab(str_replace('-','',$tmpEvt->periodDateExceptions));//2024-07-14 => 20240714
					$evtPeriodExcept.="EXDATE;VALUE=DATE:".implode(',',$periodDateExceptions).$RL;
				}
				//// Ajoute l'evenement !
				$ical.= 'BEGIN:VEVENT'.$RL.
						'UID:'.$tmpEvt->md5Id().$RL.
						'SEQUENCE:0'.$RL.
						'STATUS:CONFIRMED'.$RL.
						"CREATED:".self::icalDate($tmpEvt->dateCrea).$RL.
						"LAST-MODIFIED:".self::icalDate($tmpEvt->dateModif ? $tmpEvt->dateModif : $tmpEvt->dateCrea).$RL.
						"DTSTAMP:".self::icalDate(date("Y-m-d H:i")).$RL.
						'DTSTART;TZID='.self::icalDate($tmpEvt->dateBegin,true).$RL.
						'DTEND;TZID='.self::icalDate($tmpEvt->dateEnd,true).$RL.
						'SUMMARY:'.Txt::clean($tmpEvt->title,"min").$RL.
						$evtDescription.$evtCategory.$evtPeriod.$evtPeriodExcept.
						'END:VEVENT'.$RL;
			}

			////	Fin du fichier ical
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
				$icsFilename='Calendar_'.Txt::clean($objCalendar->title,"max").'_export-'.date("d-m-Y").'.ics';
				header("Content-type: text/calendar; charset=utf-8");
				header("Content-Disposition: inline; filename=".$icsFilename);
				echo $ical;
			}
		}
	}

	/********************************************************************************************
	 * EXPORT .ICAL : FORMATE LA DATE
	 ********************************************************************************************/
	public static function icalDate($dateTime, $addTimezone=false)
	{
		if(!empty($dateTime)){
			$timestamp=strtotime($dateTime);
			$icalDate=date("Ymd",$timestamp).'T'.date("His",$timestamp);		//exple:  20301231T235959
			if($addTimezone==true)	{return self::$curTimezone.':'.$icalDate;}	//exple:  Europe/Paris:20301231T235959
			else					{return $icalDate.'Z';}						//exple:  20301231T235959Z
		}
	}
	
	/********************************************************************************************
	 * EXPORT .ICAL : FORMATE L'HEURE DE LA TIMEZONE
	 ********************************************************************************************/
	public static function icalHour($hourDiff=0)
	{
		$hourTimezone=Tool::$tabTimezones[self::$curTimezone];			//Récupère par exemple "-5:00"
		$sign=(substr($hourTimezone,0,1)=="-")  ?  '-'  :  '+';			//"-"
		$hourAbs=intval(str_replace(['-','+',':00'],'',$hourTimezone));	//"5"
		$hourAbs+=$hourDiff;											//Ajoute X heure à la $hourTimezone de référence
		if($hourAbs<10)	{$hourAbs="0".$hourAbs;}						//"05"
		return $sign.$hourAbs."0000";									//Retourne "-050000"
	}
}