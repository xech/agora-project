<!--DRAG/DROP SUR MOBILE-->
<script src="app/js/interact.min.js"></script>


<script>
/************************************************************************************************************
 *	AFFICHAGE PRINCIPAL + AFFICHAGE DES AGENDAS VIA calendarDisplay()
*************************************************************************************************************/
function moduleDisplay()
{
	$(".vSynthDay").outerWidth( ($("#synthHeader").width()-$(".vSynthLabel").width()) / $("#synthHeader .vSynthDay").length );					//Synthese des agendas : width des cellules des jours
	$(".vCalMain").outerHeight( (windowTopHeight - $("#pageContent").offset().top - <?= empty($_SESSION["livecounterUsers"])?10:75 ?>), true);	//Hauteur en fonction du height disponible (10 ou 80 de margin-bottom)
	$(".vCalVue").outerHeight( $(".vCalMain").innerHeight() - $(".vCalHeader").outerHeight());													//Hauteur des vues Month/Week en fonction de vCalMain
	$(".vEvtBlock").each(function(){ $(this).css("background-color",this.getAttribute("data-evt-color")); });									//Bgcolor de chaque evt
	calendarDisplay();																															//Affichage des agendas (VueCalendarMonth / VueCalendarWeek)
	evtDraggable();																																//Init le Draggable des evenements
	$(".vCalMain").css("visibility","visible");																									//Affiche les agendas : après calendarDisplay() !
}

/******************************************************************************************************************
 *	DRAG & DROP D'EVT : DESACTIVE LE ONCLICK DES .vEvtLabel  ("true" pour transmettre "event" en 1er à l'écouteur)
*******************************************************************************************************************/
document.addEventListener("click", function(event){
	if($(".vEvtBlockMoved").isVisible()){
		event.stopPropagation();
		event.preventDefault();
	}
}, true);

/************************************************************************************************************
 *	DRAG & DROP D'EVT : ENREGISTRE LE NOUVEAU TIMEBEGIN VIA AJAX
*************************************************************************************************************/
function evtDraggedRecord(targetEvt, targetCell, evtNewTimeBegin)
{
	////	TypeId de l'evt + Url d'enregistrement du nouveau datetime
	const evtTypeId=targetEvt.getAttribute("data-typeid");																						
	const ajaxUrl="?ctrl=calendar&action=EvtChangeTime&evtNewTimeBegin="+evtNewTimeBegin+"&typeId="+evtTypeId;
	$.ajax({url:ajaxUrl,dataType:"json"}).done(function(result){
		if(result.changed){
			////	Parcourt chaque instance de l'evt sur chaque agenda affiché
			$(".vEvtBlock[data-typeid='"+evtTypeId+"']").each(function(){
				////	Update les attributs de l'evt (timeBegin, timeEnd..)  +  Update le tooltip et le label de la date
				for(var keyAttr in result.attributes)  {this.setAttribute(keyAttr, result.attributes[keyAttr]);}
				$(this).find(".vEvtLabel").tooltipUpdate(result.tooltip);
				$(this).find(".vEvtLabelHM").html(result.evtLabelDate);
				////	Déplace l'evt dans la .vMonthCell des autres agendas affichés
				if(targetEvt.id!=this.id && $(".vMonthCell").exist())
					{$(this).parents(".vMonthTable").find(".vMonthCell[data-cell-ymd="+targetCell.getAttribute("data-cell-ymd")+"]").append(this);}
			});
			////	Notif  +  Reload l'affichage
			notify("<?= Txt::trad("CALENDAR_evtChangeTimeConfirmed") ?>","success");
			calendarDisplay();
		}
		else if(result.error)  {notify("Update error");}
	});
}

ready(function(){
	/********************************************************************************************************
	 *	PROPOSITION D'EVT : CONFIRME/ANNULE UNE PROPOSITION
	 ********************************************************************************************************/
	$(".evtPropositions").on("click",function(){
		//// Init le Confirm
		let ajaxUrl="?ctrl=calendar&action=evtPropositionsConfirm&typeId=calendar-"+this.getAttribute("data-idcal")+"&_idEvt="+this.getAttribute("data-idevt");
		let redirUrl="?ctrl=calendar&notify=";
		let confirmParams={
			title:"<?= Txt::trad("CALENDAR_evtProposition") ?> :",
			content:this.getAttribute("data-details"),//Détails de l'evt (date, auteur, etc)
			buttons:{
				cancel:{text:"<?= Txt::trad("confirmCancel") ?>"},
				accept:{btnClass:"btn-green", text:"<?= Txt::trad("CALENDAR_evtProposeConfirm") ?>",  action:function(){  $.ajax(ajaxUrl+"&isConfirmed=true").done(function(){ redir(redirUrl+"CALENDAR_evtProposeConfirmed"); });  }},
				reject:{btnClass:"btn-dark",  text:"<?= Txt::trad("CALENDAR_evtProposeDecline") ?>",  action:function(){  $.ajax(ajaxUrl+"&isDeclined=true").done(function(){  redir(redirUrl+"CALENDAR_evtProposeDeclined"); });  }},
			}
		}
		//// Lance le Confirm (paramétrage par défaut + spécifique)
		$.confirm(Object.assign(confirmParamsDefault,confirmParams));
	});

	/********************************************************************************************************
	 *	PROPOSITION D'EVT : PULSATE L'ICONE DU MODULE DANS LE "VueHeaderMenu.php"
	 ********************************************************************************************************/
	if($(".evtPropositions").exist() && $("#headerMobileModule").isVisible())
		{$("#headerMobileModule").pulsate();}

	/********************************************************************************************************
	 *	SUBMIT LA LISTE DES AGENDAS AFFICHES
	 ********************************************************************************************************/
	$("input[name='displayedCalendars[]']").on("change",function(){
		$("#readableCalendarsForm").submit();
	});

	/********************************************************************************************************
	 *	DATEPICKER DU MOIS DANS LE MENU DE GAUCHE (cf JQUERY UI)
	 ********************************************************************************************************/
	$("#datepickerCalendar").datepicker({
		firstDay:1,										//Début de semaine le lundi
		showOtherMonths:true,							//Affiche les jours des mois précédents/suivants
		defaultDate:"<?= date("Y-m-d",$curTime) ?>",	//Mois/Date affiché
		dateFormat:"yy-mm-dd",							//Utilisé par "dayYmd" ci-dessous
		onSelect:function(dayYmd){ let dateObj=new Date(dayYmd);  redir("?ctrl=calendar&curTime="+(dateObj.getTime()/1000));}//Clique sur une date : redirection
	});
	/////	DATEPICKER : SURLIGNE LES JOURS DE LA SEMAINE AFFICHÉE
	<?php foreach($periodDays as $tmpDay){ ?>
		$(".ui-state-active").removeClass("ui-state-active");//Réinit le style du jour de ref
		$("[data-month=<?= $tmpDay["monthOfYear"]-1 ?>] [data-date=<?= $tmpDay["dayOfMonth"] ?>]").addClass("ui-state-highlight");
	<?php } ?>

	/********************************************************************************************************
	 *	MOBILE : SWIPE GAUCHE/DROITE  &&  BOUTON "TODAY"
	 ********************************************************************************************************/
	if(isTouchDevice()){
		////	SWIPE GAUCHE/DROITE POUR AFFICHER LA PERIODE PRECEDENTE/SUIVANTE
		swipeMenuShowOff=true;																//Désactive l'affichage du menu context via swipe
		document.addEventListener("touchstart",function(event){ buttonPrevNext=null; });	//Début de swipe
		document.addEventListener("touchmove",function(event){								//Direction du swipe :
			if(swipeAmplitudeY < 80){														//Swipe d'amplitude < 80px  (cf "menuContext()")
				if(swipeToRight > 100)		{buttonPrevNext=".vCalPrev";}					//Affiche la période précédente
				else if(swipeToLeft > 100)	{buttonPrevNext=".vCalNext";}					//Affiche la période suivante
			}
		});
		document.addEventListener("touchend",function(){																	//Fin de swipe :
			if(buttonPrevNext!=null && $(".vEvtBlockMoved").isVisible()==false && $("#menuMobileMain").isVisible()==false){	//buttonPrevNext spécifé + Pas de drag/drop en cours + Menu context masqué
				$(buttonPrevNext).effect("pulsate",{times:2},500);															//Pulsate le bouton de la Prev/Next
				setTimeout(function(){  $(buttonPrevNext).trigger("click");  },300);										//Trigger "Click" pour afficher la période
			}
		});
	}
});
</script>


<style>
/*Réduit la taille du footer + du livecounter principal*/
#pageContent									{padding-bottom:10px!important;}/*Surcharge VueStructure.php pour ne pas avoir de marge sous l'agenda*/
#pageFooterHtml, #pageFooterIcon				{display:none;}
#pageFull										{margin-bottom:0px;}

/*Menu du module (gauche)*/
#evtPropositionsPulsate							{float:right; margin:-10px;}
.evtPropositions								{padding:5px; margin-top:5px;}
.evtPropositions hr								{margin:5px;}
#readableCalendarsForm							{max-height:450px; overflow-y:auto;}
#readableCalendarsTitle 						{margin-bottom:10px;}
#readableCalsAdmin								{float:right; filter:saturate(0);}
#readableCalendarsForm:not(:hover) #readableCalsAdmin {visibility:hidden;}
.readableCalendar input							{display:none;}
.readableCalendar label							{display:block; padding:4px; margin:2px;}/*Label des agendas : cf ".option"*/
#datepickerCalendar								{margin-top:20px; margin-bottom:10px;}
.ui-datepicker									{box-shadow:none;}/*Datepicker*/
.ui-datepicker thead							{display:none;}/*pas de libellé des jours*/
.ui-datepicker .ui-state-default				{padding:7px;}/*Cellules des jours*/

/*Synthese des agendas*/
#synthBlock.miscContent							{padding:2px 8px; margin-bottom:20px;}/*surcharge*/
#synthTable										{display:table; width:100%; max-width:100%;}
#synthHeader, .vSynthLine						{display:table-row;}
#synthHeader									{font-size:0.9em!important;}
#synthHeader .vSynthDayCurDay					{color:#c00;}
.vSynthLabel									{display:table-cell; width:150px; white-space:nowrap; padding-right:10px; vertical-align:middle;}
.vSynthDay										{display:table-cell; vertical-align:middle; text-align:center; height:22px;}
.vSynthDayEvts									{display:table; width:100%; height:100%;}
.vSynthDayEvt									{display:table-cell; border-left:transparent;}
.vSynthDayEvts:hover							{opacity:0.8;}
.vSynthDayEvtTooltip							{text-align:left;}
.vSynthDayEvtTooltip	ul						{margin:0px; margin-top:5px; padding-left:10px;}
.vSynthDayCal									{background:#ddd; border:dotted 1px #eee;}
.vSynthDayCal.vSynthDayCalWE					{background:#ccc;}

/*Agendas : conteneur + menu d'affichage + label des jours*/
.vCalMain										{min-height:500px; padding:0px; visibility:hidden;}/*Masqué le tps du calcul de l'affichage*/
.vCalMain:not(:last-child)						{margin-bottom:50px;}
.vCalVue										{max-width:100%; width:100%; user-select:none!important; -webkit-user-select:none!important;}
.vCalHeader										{display:table; width:100%; font-size:1.1rem;}
.vCalHeader>div									{display:table-cell; padding:10px; vertical-align:middle;}
.vCalHeaderLeft, .vCalHeaderCenter				{min-width:250px;}
.vCalHeaderLeftLabel							{margin-right:10px; vertical-align:middle;}
.vCalHeaderCenter								{text-align:center;}
.vCalHeaderCenter .vCalPrevNext					{padding:10px 15px; border-radius:5px;}
.vCalHeaderCenter .vCalPrevNext:hover			{background-color:#eee;}
[id^=monthsYearsMenu]							{width:300px; overflow:visible;}
#monthsYearsMenuContainer a						{display:inline-block; width:85px; padding:5px; text-align:left;}
.vCalHeaderRight								{width:480px; text-align:right;}
.vCalHeaderRight>span							{margin-right:8px;}
.vCalHeaderRight button							{box-shadow:none; font-weight:normal;}
.vCalLabelDays									{padding:8px 4px; text-align:center; text-transform:capitalize;}

/*Evenements*/
.vEvtBlock										{height:20px; min-height:20px; margin:0px; padding:4px; padding-right:20px; box-shadow:1px 1px 2px #555; border-radius:4px!important;}/*padding-right pour le menu burger*/
.vEvtBlock[data-evt-is-past='true']:not(:hover)	{filter:brightness(0.9);}/*événements passés (sauf si survolé : cf. menu context)*/
.vEvtBlockMoved									{z-index:1000; opacity:0.9; box-shadow:0px 0px 4px 4px white;}/*Evt en cours de déplacement*/
.vEvtLabel										{overflow:hidden; white-space:normal; font-weight:normal; color:white!important;}/*white-space: longs mots splités sur plusieurs lignes*/
.vEvtLabel img									{max-height:13px;}/*icone important/period*/
.vEvtConfirmOldDate								{opacity:0.75;}

/*AFFICHAGE RESPONSIVE*/
@media screen and (max-width:1200px){
	#pageContent								{padding-inline:0px!important;}/*surcharge app.css*/
	.vCalMain.miscContent						{margin:0px; margin-bottom:40px;}/*surcharge .miscContent*/
	.vCalMain									{width:100%; box-shadow:none; margin-bottom:0;}
	.vCalHeader									{white-space:nowrap;}
	.vCalHeader>div								{padding:4px; width:auto; text-transform:lowercase;}
	.vCalHeaderLeft, .vCalHeaderCenter			{min-width:100px;}
	.vCalHeaderLeftLabel						{display:inline-block; font-size:1rem; max-width:140px; margin-inline:0px; overflow:hidden; text-overflow:ellipsis;}/*Max-width avec inline-block + hidden + ellipsis*/
	.vCalHeaderLeftLabel::first-letter			{text-transform:uppercase}
	.vCalHeaderCenter .vCalPrevNext				{padding:3px;}
	.vCalHeaderRight>span						{display:inline-block; margin-right:6px; line-height:35px; vertical-align:middle;}
	.vCalHeaderRight img						{min-height:20px;}
	.vEvtBlock									{height:25px; min-height:25px; overflow:hidden; padding-right:0px;}/*padding-right : pas menu burger*/
	.vEvtConfirmOldDate							{display:block;}
	.vCalHeader .personImgSmall, .vEvtBlock .menuContextLaunchFloat {display:none!important;}/*Masque les icone des users, les dates des evt, le bouton burger des evt*/
}

/* IMPRESSION */
@media print{
	@page											{size:landscape;}/*format paysage*/
	html, body										{background-image:none!important;}/*surcharge*/
	body											{-webkit-print-color-adjust:exact; print-color-adjust:exact;}/*conserve les couleurs des evts*/
	.vCalMain, .vCalVue, .vCalVue>*, .vWeekTable	{width:1200px!important; max-width:1200px!important; max-height:98%!important;}
	.vEvtBlock										{max-width:165px!important;}/*1200 % 7*/
	.vCalMain										{box-shadow:none;}
	.vCalMain:not(:last-child)						{page-break-after:always;}/*saut de page après chaque agenda (sauf le dernier)*/
	.vCalHeader>div									{padding:0px 10px 0px 20px !important; font-size:1.1rem;}
	.vCalHeaderCenter								{text-align:right;}
	.vWeekScroller									{overflow:visible!important;}/*pas d'overflow scroll en affichage "week"*/
	#synthBlock, .vCalPrevNext, .vCalHeaderRight, .vWeekNbOfYear	{display:none!important;}
}
</style>


<div id="pageFull">
	<div id="pageMenu">
		<!--PROPOSITIONS D'EVT-->
		<?php if(!empty($evtPropositions)){ ?>
			<div class="miscContent">
				<legend><?= Txt::trad("CALENDAR_evtProposition") ?><img src="app/img/importantBig.png" id="evtPropositionsPulsate" class="pulsate"></legend>
				<?php foreach($evtPropositions as $evtTmp){ ?>
					<div class="evtPropositions optionSelect" data-idevt="<?= $evtTmp["_idEvt"] ?>" data-idcal="<?= $evtTmp["_idCal"] ?>" data-details="<?= strip_tags($evtTmp["evtPropDetails"],'<br><hr>') ?>" <?= Txt::tooltip($evtTmp["evtPropDetails"]) ?> ><?= $evtTmp["evtPropLabel"] ?></div>
				<?php } ?>
			</div>
		<?php } ?>

		<div class="miscContent">
			<!--AGENDAS DISPONIBLES-->
			<?php if(!empty($readableCalendars)){ ?>
				<form action="index.php" id="readableCalendarsForm">
					<!--TITRE + OPTION D'AFFICHAGE ADMIN-->
					<div id="readableCalendarsTitle">
						<?= Txt::trad("CALENDAR_readableCalendars") ?> :
						<?php if(Ctrl::$curUser->isSpaceAdmin()){ ?><img src="app/img/plusSmall.png" id="readableCalsAdmin" <?= Txt::tooltip("CALENDAR_displayAdmin") ?> onclick="redir('?ctrl=<?= Req::$curCtrl ?>&displayAdmin=<?= empty($_SESSION['displayAdmin'])?'true':'false' ?>')"><?php } ?>
					</div>
					<!--LISTE DES AGENDAS (Cf "getPref('displayedCalendars')")-->
					<?php foreach($readableCalendars as $tmpCal){ ?>
						<div class="readableCalendar" <?= Txt::tooltip(Txt::trad("CALENDAR_displayHide").'<hr>'.$tmpCal->description) ?> >
							<input type="checkbox" name="displayedCalendars[]" value="<?= $tmpCal->_id ?>" id="boxDisplay<?= $tmpCal->typeId ?>" <?= $tmpCal->isDisplayed==true?'checked':null ?> >
							<label for="boxDisplay<?= $tmpCal->typeId ?>" class="option <?= $tmpCal->isDisplayed==true?'optionSelect':null ?>"><?= $tmpCal->title ?></label>
						</div>
					<?php } ?>
					<input type="hidden" name="ctrl" value="<?= Req::$curCtrl ?>">
					<input type="hidden" name="curTime" value="<?= Req::param("curTime") ?>">
			</form>
			<hr>
			<?php }	?>

			<!--MENU DES CATEGORIES-->
			<?= MdlCalendarCategory::displayMenu()	?>

			<!--CREER UN AGENDA PARTAGE-->
			<?php if(MdlCalendar::addRight()){ ?>
			<div class="menuLine" onclick="lightboxOpen('<?= MdlCalendar::getUrlNew() ?>');" <?= Txt::tooltip("CALENDAR_addSharedCalendarTooltip") ?>>
				<div class="menuIcon"><img src="app/img/calendar/calendarAdd.png"></div>
				<div><?= Txt::trad("CALENDAR_addSharedCalendar") ?></div>
			</div>
			<?php } ?>

			<!--EVTS PROPRIO-->
			<?php if(Ctrl::$curUser->isUser()){ ?>
			<div class="menuLine" onclick="lightboxOpen('?ctrl=calendar&action=MyEvents')" <?= Txt::tooltip("CALENDAR_evtAutorInfo") ?>>
				<div class="menuIcon"><img src="app/img/edit.png"></div>
				<div><?= Txt::trad("CALENDAR_evtAutor") ?></div>
			</div>
			<?php } ?>

			<!--IMPRIMER LA PAGE-->
			<?php if(Req::isMobile()==false){ ?>
			<div class="menuLine" onclick="calendarDisplay(true);print();" <?= Txt::tooltip("CALENDAR_printCalendarsInfos") ?>>
				<div class="menuIcon"><img src="app/img/print.png"></div>
				<div><?= Txt::trad("CALENDAR_printCalendars") ?></div>
			</div>
			<?php } ?>

			<!--CALENDRIER MOIS VIA LE DATEPICKER-->
			<?= $displayMode!="month" ? "<div id='datepickerCalendar'></div>" : null ?>
		</div>
	</div>

	<div id="pageContent">

		<!--SYNTHESE DES AGENDAS -->
		<?php if(!empty($periodSynthese)){ ?>
			<div id="synthBlock" class="miscContent">
				<div id="synthTable">
					<!--HEADER DE LA SYNTHESE-->
					<div id="synthHeader">
						<div class="vSynthLabel">&nbsp;</div>
						<?php foreach($periodSynthese as $dayYmd=>$tmpDay)  {echo '<div class="vSynthDay '.($dayYmd==date("Y-m-d")?"vSynthDayCurDay":null).'">'.(int)date("d",$tmpDay["dayTimeBegin"]).'</div>';} ?>
					</div>
					<!--AFFICHE CHAQUE AGENDA : LIBELLE & CHAQUE JOUR DE L'AGENDA-->
					<?php foreach($displayedCalendars as $tmpCal){ ?>
					<div class="vSynthLine">
						<div class="vSynthLabel" onclick="$('#calBlock<?= $tmpCal->typeId ?>').scrollTo();"><?= $tmpCal->title ?></div>
						<?php
						foreach($periodSynthese as $tmpDay){
							$tmpEvtTooltip='<div class="vSynthDayEvtTooltip">'.Txt::dateLabel($tmpDay["dayTimeBegin"],"dateBasic").' - '.$tmpCal->title.' :<br>';
							foreach($tmpDay["dayEvtList"][$tmpCal->_id] as $tmpEvt)	{$tmpEvtTooltip.='<br>'.Txt::dateLabel($tmpEvt->dateBegin,"mini",$tmpEvt->dateEnd).' : '.Txt::reduce($tmpEvt->title,60);}
							$tmpEvtTooltip.='</div>';
							$syntheseDayCalWE=$syntheseDayEvts=null;
							if($tmpDay["dayOfWeek"]>5)	{$syntheseDayCalWE="vSynthDayCalWE";}
							foreach($tmpDay["dayEvtList"][$tmpCal->_id] as $tmpEvt)	{$syntheseDayEvts.='<div class="vSynthDayEvt" onclick="'.$tmpEvt->lightboxVue().'" style="background-color:'.$tmpEvt->evtColor.'">&nbsp;</div>';}
							echo '<div class="vSynthDay vSynthDayCal '.$syntheseDayCalWE.'">
									<div class="vSynthDayEvts" '.Txt::tooltip($tmpEvtTooltip).'>'.$syntheseDayEvts.'</div>
								  </div>';
						}
						?>
					</div>
					<?php } ?>
				</div>
			</div>
		<?php } ?>

		<!--AFFICHE CHAQUE AGENDA-->
		<?php foreach($displayedCalendars as $tmpCal){ ?>
		<div class="vCalMain miscContent" id="calBlock<?= $tmpCal->typeId ?>">
			<div class="vCalHeader">
				<!--TITRE DE L'AGENDA-->
				<div class="vCalHeaderLeft">
					<?php
					$calLabel='<span class="vCalHeaderLeftLabel" '.Txt::tooltip($tmpCal->description).'>'.$tmpCal->title.'</span>';								//Label de l'agenda
					if($tmpCal->isPersonal())  {$calLabel.=Ctrl::getObj("user",$tmpCal->_idUser)->tagProfileImg(true,true);}									//Ajoute l'icone de l'user ?
					echo Ctrl::$curUser->isUser()  ?  $tmpCal->contextMenu(["burgerLauncher"=>"small-inline","burgerLauncherLabel"=>$calLabel])  :  $calLabel;	//Label de l'agenda
					?>
				</div>
				<!--PERIODE AFFICHEE  &  PRECEDENT/SUIVANT  &  MENU CONTEXT MONTHS/YEARS-->
				<div class="vCalHeaderCenter">
					<span class="vCalPrevNext vCalPrev" onclick="redir('?ctrl=calendar&curTime=<?= $timePrev ?>')" <?= Txt::tooltip("CALENDAR_periodPrev") ?>><img src="app/img/arrowLeftNav.png"></span>
					<span class="menuContextLaunch vCalHeaderMonth" for="monthsYearsMenu<?= $tmpCal->typeId ?>"><?= ucfirst($monthLabel) ?></span>
					<?php if(!empty($monthsYearsMenu))  {echo "<div class='menuContext' id='monthsYearsMenu".$tmpCal->typeId."'><div id='monthsYearsMenuContainer'>".$monthsYearsMenu."</div></div>";} ?>
					<span class="vCalPrevNext vCalNext" onclick="redir('?ctrl=calendar&curTime=<?= $timeNext ?>')" <?= Txt::tooltip("CALENDAR_periodNext") ?>><img src="app/img/arrowRightNav.png"></span>
				</div>
				
				<!--PROPOSER/AJOUTER UN EVT  &  "AUJOURD'HUI"  &  AFFICHAGE MONTH/WEEK/ETC-->
				<div class="vCalHeaderRight">
					<span onclick="redir('?ctrl=calendar&curTime=<?= time() ?>')" <?= Txt::tooltip("displayToday") ?> >
						<?= Req::isMobile() ? '<img src="app/img/calendar/displayToday.png">' : '<button>'.Txt::trad("today").'</button>' ?>
					</span>
					<span class="menuContextLaunch" for="menuDisplayMode<?= $tmpCal->typeId ?>">
						<?= Req::isMobile() ? '<img src="app/img/calendar/display'.ucfirst($displayMode).'.png">' : '<button>'.Txt::trad("CALENDAR_display_".$displayMode).' <img src="app/img/arrowBottom.png"></button>' ?>
					</span>
					<div class="menuContext" id="menuDisplayMode<?= $tmpCal->typeId ?>">
						<?php foreach($displayModeList as $displayModeTmp){ ?>
						<div class="menuLine <?= $displayModeTmp==$displayMode?"linkSelect":null ?>" onclick="redir('?ctrl=calendar&calendarDisplayMode=<?= $displayModeTmp ?>')">
							<div class="menuIcon"><img src="app/img/calendar/display<?= ucfirst($displayModeTmp) ?>.png"></div><div><?= ucfirst(Txt::trad("CALENDAR_display_".$displayModeTmp)) ?></div>
						</div>
						<?php } ?>
					</div>
					<?php if($tmpCal->affectationAddRight()){ ?>
					<span onclick="lightboxOpen('<?= MdlCalendarEvent::getUrlNew().'&_idCal='.$tmpCal->_id ?>')" <?= $tmpCal->addEvtTooltip ?> >
						<?= Req::isMobile() ? '<img src="app/img/plus.png">' : '<button><img src="app/img/plusSmall.png">&nbsp; '.Txt::trad("CALENDAR_addEvt").'</button>' ?>
					</span>
					<?php } ?>
				</div>
			</div>
			<!-- VUE MONTH / WEEK DE L'AGENDA (Cf "VueCalendarMonth"/"VueCalendarWeek")-->
			<?= $tmpCal->calendarVue ?>
		</div>
		<?php } ?>

		<!--AUCUN AGENDA-->
		<?php if(empty($displayedCalendars)){ ?>
			<div class="miscContent emptyContent"><?= Txt::trad("CALENDAR_noCalendarDisplayed") ?></div>
		<?php } ?>
	</div>
</div>