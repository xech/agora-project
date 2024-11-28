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
		////	INIT : PROPOSITION D'EVT / LISTE D'AGENDAS / MODE D'AFFICHAGE / TPS DE REFERENCE / JOURS FERIES
		$vDatas["eventProposition"]=self::eventProposition();																							//Proposition d'evenement a confirmer
		$vDatas["readableCalendars"]=(!empty($_SESSION["displayAdmin"]))  ?  MdlCalendar::affectationCalendars()  :  MdlCalendar::readableCalendars();	//Agendas visibles : "readable" / "affectation" ("displayAdmin")
		$vDatas["displayedCalendars"]=MdlCalendar::displayedCalendars($vDatas["readableCalendars"]);													//Agendas affichés
		$smallDisplay=(isset($_COOKIE["windowWidth"]) && $_COOKIE["windowWidth"]<=600);																	//Affichage réduit (600px de width max)
		$displayMode=self::prefUser("calendarDisplayMode","displayMode");																				//Mode d'affichage en preference
		if(empty($displayMode))  {$displayMode="month";}																								//Sinon "month" par défaut
		if($smallDisplay==true && preg_match("/(week|7Days)/i",$displayMode))	{$displayMode="3Days";}													//Affichage réduit : switch de "week"/"7Days" à "3Days"
		elseif($smallDisplay==false && $displayMode=="3Days")					{$displayMode="week";}													//Affichage normal : switch de "3Days" à "week"
		$vDatas["displayMode"]=$displayMode;																											//Affichage courant
		$vDatas["displayModeList"]=($smallDisplay==true)  ?  ["month","3Days"]  :  ["month","week","7Days"];											//Affichages disponibles
		$vDatas["curTime"]=$curTime=Req::isParam("curTime")  ?  Req::param("curTime")  :  time();														//Temps de référence
		$vDatas["celebrationDays"]=Trad::celebrationDays(date("Y",$curTime));																			//Jours Fériés

		////	DEBUT/FIN DE PERIODE : EN FONCTION DE $displayMode
		if($displayMode=="month")	{$strBegin="first day of this month 00:00:00";	$strEnd="last day of this month 23:59:59";	$strPrev="-1 month";	$strNext="+1 month";}
		if($displayMode=="week")	{$strBegin="monday this week 00:00:00";			$strEnd="sunday this week 23:59:59";		$strPrev="-1 week";		$strNext="+1 week";}
		if($displayMode=="7Days")	{$strBegin="today 00:00:00";					$strEnd="+6 day 23:59:59";					$strPrev="-7 day";		$strNext="+7 day";}
		if($displayMode=="3Days")	{$strBegin="today 00:00:00";					$strEnd="+2 day 23:59:59";					$strPrev="-3 day";		$strNext="+3 day";}
		$timeBegin			=strtotime($strBegin,$curTime);		//Début de période affichée
		$timeEnd			=strtotime($strEnd,  $curTime);		//Fin de période affichée
		$vDatas["timePrev"]	=strtotime($strPrev, $timeBegin);	//Période précédente (tjs prendre $timeBegin en référence!)
		$vDatas["timeNext"]	=strtotime($strNext, $timeBegin);	//Période suivante (idem)

		////	LABEL DU MOIS AFFICHÉ  &&  MENU DES ANNÉES/MOIS
		if(date("Ym",$timeBegin)!=date("Ym",$timeEnd))	{$vDatas["monthLabel"]=Txt::formatime("MMM", $timeBegin)."/".Txt::formatime("MMM yy", $timeEnd);}	//"Fev./Mar. 50"	: semaine à cheval sur 2 mois
		elseif(Req::isMobile())							{$vDatas["monthLabel"]=Txt::formatime("MMM yy", $timeBegin);}										//"Fev. 50"			: format mobile
		else											{$vDatas["monthLabel"]=Txt::formatime("MMMM yyyy", $timeBegin);}									//"Fevrier 2050"	: format par défaut
		$vDatas["calMonthPeriodMenu"]=null;
		for($monthNb=1; $monthNb<=12; $monthNb++){
			$monthTime=strtotime(date('Y',$curTime).'/'.$monthNb.'/1');
			$vDatas["calMonthPeriodMenu"].='<a onclick="redir(\'?ctrl=calendar&curTime='.$monthTime.'\')" class="'.(date('Y-m',$curTime)==date('Y-m',$monthTime)?'optionSelect':'option').'">'.Txt::formatime("MMMM",$monthTime).'</a>';
		}
		$vDatas["calMonthPeriodMenu"].='<hr>';
		for($yearNb=date('Y')-2; $yearNb<=date('Y')+3; $yearNb++){
			$yearTime=strtotime($yearNb.'-'.date('m',$curTime).'-01');
			$vDatas["calMonthPeriodMenu"].='<a onclick="redir(\'?ctrl=calendar&curTime='.$yearTime.'\')" class="'.(date('Y',$curTime)==$yearNb?'optionSelect':'option').'">'.$yearNb.'</a>';
		}

		////	LISTE DES JOURS À AFFICHER
		if($displayMode=="month")	{$timeBeginDisplay=strtotime("monday this week 00:00:00",$timeBegin);	$timeEndDisplay=strtotime("sunday this week 23:59:59",$timeEnd);}//Lundi de 1ere semaine du mois / Dimanche de dernière semaine du mois
		else						{$timeBeginDisplay=$timeBegin;	$timeEndDisplay=$timeEnd;}
		$vDatas["periodDays"]=[];
		for($timeDay=$timeBeginDisplay; $timeDay<=$timeEndDisplay; $timeDay+=86400){																	//Liste des jours de la période
			$tmpDay["date"]=date("Y-m-d",$timeDay);																										//Date
			$tmpDay["timeBegin"]=strtotime("today 00:00:00", $timeDay);																					//Timestamp de début
			$tmpDay["timeEnd"]	=strtotime("today 23:59:59", $timeDay);																					//Timestamp de fin
			$tmpDay["celebrationDay"]=(isset($vDatas["celebrationDays"][$tmpDay["date"]]))  ?  $vDatas["celebrationDays"][$tmpDay["date"]]  :  null;	//Jour ferie
			$vDatas["periodDays"][$tmpDay["date"]]=$tmpDay;																								//Ajoute le jour à la liste
		}

		////	RECUPERE LA VUE DE CHAQUE AGENDA ("VueCalendarMonth.php" / "VueCalendarWeek.php")  &&   LA LISTE DES EVENEMENTS
		foreach($vDatas["displayedCalendars"] as $cptCal=>$tmpCal)
		{
			//// LABEL D'AJOUT D'ÉVÉNEMENT / PROPOSITION D'ÉVÉNEMENT
			if($tmpCal->addContentRight())		{$tmpCal->addEventLabel=Txt::trad("CALENDAR_addEvtTooltip");}
			elseif($tmpCal->addOrProposeEvt())	{$tmpCal->addEventLabel=Txt::trad("CALENDAR_proposeEvtTooltip");}
			else								{$tmpCal->addEventLabel=null;}
			//// EVENEMENTS POUR CHAQUE JOUR
			$tmpCal->eventList=[];
			$tmpCal->eventListDisplayed=$tmpCal->eventList($timeBeginDisplay, $timeEndDisplay, 0.5, true);//Evts sur toute la période affichée. Params : $accessRightMin=0.5, $categoryFilter=true
			foreach($vDatas["periodDays"] as $tmpDate=>$tmpDay){
				$tmpCal->eventList[$tmpDate]=[];
				$tmpDayEvts=MdlCalendar::eventsFilter($tmpCal->eventListDisplayed,$tmpDay["timeBegin"],$tmpDay["timeEnd"]);								//Récupère uniquement les evts de la journée
				foreach($tmpDayEvts as $tmpEvt){																										//Parcourt chaque événement :
					$tmpEvt->timeBegin=strtotime($tmpEvt->dateBegin);																					//"time" du début de journée
					$tmpEvt->timeEnd  =strtotime($tmpEvt->dateEnd);																						//"time" de fin de journée
					$tmpEvt->tooltip=$tmpEvt->title.'<br>'.Txt::dateLabel($tmpEvt->timeBegin,"basic",$tmpEvt->timeEnd);									//Tooltip avec title et date détaillée
					if(Req::isMobile()==false){																											//Ajoute au title l'heure de début (sauf sur mobile)
						if($displayMode=="month")	{$tmpEvt->title=Txt::dateLabel($tmpEvt->timeBegin,"mini")." &nbsp;".$tmpEvt->title;}				//- affichage Month (ex: "14h Mon titre")
						else						{$tmpEvt->title=$tmpEvt->title.'<br>'.Txt::dateLabel($tmpEvt->timeBegin,"mini",$tmpEvt->timeEnd);}	//- affichage Week  (ex: "Mon Titre <br> 14h - 15h")
					}
					if(!empty($tmpEvt->important))  {$tmpEvt->title.='<img src="app/img/important.png" class="vEvtImportant">';}						//Ajoute au title l'icone "important"
					$tmpEvt->containerClass=($tmpEvt->timeEnd < strtotime(date("Y-m-d")))  ?  "vEvtBlock vEvtBlockPast"  :  "vEvtBlock";				//"vEvtBlockPast" s'il commence avant aujourd'hui
					$tmpEvt->containerAttributes='style="background-color:'.$tmpEvt->eventColor.'"';													//Couleur de l'evt appliqué au .vEvtBlock
					$tmpEvt->contextMenuOptions=["launcherIcon"=>"floatSmall", "_idCal"=>$tmpCal->_id, "curDateTime"=>strtotime($tmpEvt->dateBegin)];	//Options du menu contextuel (cf. "divContainerContextMenu()")
					if($displayMode!="month"){																											//Affichage "week"/"3Days"/etc :
						$tmpEvt->timeFromDayBegin=($tmpDay["timeBegin"]<$tmpEvt->timeBegin) ?  ($tmpEvt->timeBegin-$tmpDay["timeBegin"])  : 0;			//-Time depuis le début du jour ("0" si l'evt commence avant le jour)
						$evtTmpDayBefore=($tmpEvt->timeBegin < $tmpDay["timeBegin"]);																	//-Evt commence avant le jour courant ?
						$evtTmpDayAfter=($tmpEvt->timeEnd > $tmpDay["timeEnd"]);																		//-Evt termine après le jour courant?
						if($evtTmpDayBefore==true && $evtTmpDayAfter==true)	{$tmpEvt->timeDuration=24*3600;}											//-Affiche toute la journée
						elseif($evtTmpDayBefore==true)						{$tmpEvt->timeDuration=($tmpEvt->timeEnd- $tmpDay["timeBegin"]);}			//-Affiche l'evt à partir de 0h00
						elseif($evtTmpDayAfter==true)						{$tmpEvt->timeDuration=($tmpDay["timeEnd"] - $tmpEvt->timeBegin);}			//-Affiche l'evt jusqu'à 23h59
						else												{$tmpEvt->timeDuration=($tmpEvt->timeEnd - $tmpEvt->timeBegin);}			//-Affichage normal
					}
					$tmpCal->eventList[$tmpDate][]=$tmpEvt;																								//Ajoute l'evt à la liste !
				}
				////	Tri des evts en affichage "Month" (par HeureMinute) ou "Week" (par timeFromDayBegin, cf. evts qui se chevauchent sur le même "timeslot")
				if($displayMode=="month"){
					usort($tmpCal->eventList[$tmpDate],function($objA,$objB){
						return (date("Hi",$objA->timeBegin) - date("Hi",$objB->timeBegin));
					});
				}else{
					usort($tmpCal->eventList[$tmpDate],function($objA,$objB){
						return ($objA->timeFromDayBegin - $objB->timeFromDayBegin);
					});
				}
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
			$vDatas["periodSynthese"]=[];																												//Jours à afficher pour la synthese
			foreach($vDatas["periodDays"] as $tmpDate=>$tmpDay){																						//Parcour chaque jour de la période affichée
				if($displayMode=="month" && date("m",$tmpDay["timeBegin"])!=date("m",$curTime))  {continue;}											//Jour du mois précédant/suivant : passe
				$tmpDay["calsEvts"]=[];																													//Init les evts du jour
				foreach($vDatas["displayedCalendars"] as $tmpCal)																						//Parcourt chaque agenda
					{$tmpDay["calsEvts"][$tmpCal->_id]=MdlCalendar::eventsFilter($tmpCal->eventListDisplayed,$tmpDay["timeBegin"],$tmpDay["timeEnd"]);}	//-> Récupère uniquement les evts de la journée
				$vDatas["periodSynthese"][$tmpDate]=$tmpDay;																							//Ajoute le jour de la synthese
			}
		}
	
		////	AFFICHE LA VUE
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
				////  Params : $timeBegin=null, $timeEnd=null, $accessRightMin=1, $categoryFilter=false, $pluginParams=$params
				foreach($tmpCal->eventList(null,null,1,false,$params) as $tmpEvt){
					//// Vérif si l'evt n'a pas déjà été ajouté (car peut être affecté à plusieurs agendas) && se limite à 200 evt max (cf. affichage des nouveaux evt après import de fichier Ical)
					if(empty($pluginsList[$tmpEvt->_typeId]) && count($pluginsList)<200){
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
			//// EDITE LES PRINCIPAUX CHAMPS DE L'ÉVÉNEMENT (titre, timeBegin..)
			if($curObj->fullRight())
			{
				//Prépare les dates
				$dateBegin=Txt::formatDate(Req::param("dateBegin")." ".Req::param("timeBegin"), "inputDatetime", "dbDatetime");
				$dateEnd=Txt::formatDate(Req::param("dateEnd")." ".Req::param("timeEnd"), "inputDatetime", "dbDatetime");
				//Périodicité / répétition de l'evt
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
			//// RÉINITIALISE SI BESOIN LES AFFECTATIONS/PROPOSITIONS AUX AGENDAS
			if(Req::isParam("reinitCalendars")){
				foreach(Req::param("reinitCalendars") as $idCal)  {$curObj->deleteAffectation($idCal,true);}
			}
			//// ATTRIBUE LES NOUVELLES AFFECTATIONS/PROPOSITIONS AUX AGENDAS
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
			//// NOTIFIE PAR MAIL LA PROPOSITION D'EVT (AUX GESTIONNAIRES/AUTEUR DES AGENDAS CONCERNES)
			if(!empty($propositionIdUsers)){
				$evtTitleDate=$curObj->title." : ".Txt::dateLabel($curObj->dateBegin,"basic",$curObj->dateEnd);
				$mailSubject=Txt::trad("CALENDAR_propositionEmailSubject")." ".$curObj->autorLabel();
				$mailMessage=str_replace(["--AUTOR_LABEL--","--EVT_TITLE_DATE--","--EVT_DESCRIPTION--"], [$curObj->autorLabel(),$evtTitleDate,$curObj->description], Txt::trad("CALENDAR_propositionEmailMessage"));
				Tool::sendMail($propositionIdUsers, $mailSubject, $mailMessage, ["noNotify"]);
			}
			//// NOTIFIE PAR MAIL LA CREATION D'EVT (AUX PERSONNES AFFECTEES AUX AGENDAS DE L'EVT)
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
				//Evts de l'agenda sur la période sélectionné. Params : $accessRightMin=0
				$eventListControled=$tmpCal->eventList($timeBegin, $timeEnd, 0);
				foreach(MdlCalendar::eventsFilter($eventListControled,$timeBegin,$timeEnd) as $tmpEvt){
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
		$curObj->readControl();									//Controle d'accès
		$vDatas["curObj"]=$curObj;								//Récup l'evt
		$vDatas["labelPeriod"]=$curObj->periodLabel();			//Périodicité de l'événement
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
			$timeBegin=time()-(86400*365);//Time - 1 an
			$timeEnd=time()+(86400*365*5);//Time + 5 ans
			$eventList=$curObj->eventList($timeBegin, $timeEnd, 1);//Params : $accessRightMin=1
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