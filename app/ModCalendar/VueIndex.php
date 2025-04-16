<script>
ready(function(){
	/*******************************************************************************************
	 *	PROPOSITION D'EVT : PULSATE L'ICONE DU MODULE DANS LE "VueHeaderMenu.php"
	 *******************************************************************************************/
	if($(".eventProposition").exist() && $("#headerMobileModule").isVisible())  {$("#headerMobileModule").pulsate();}

	/*******************************************************************************************
	 *	PROPOSITION D'EVT : CONFIRME/ANNULE UNE PROPOSITION
	 *******************************************************************************************/
	$(".eventProposition").on("click",function(){
		//// Init le Confirm
		let ajaxUrl="?ctrl=calendar&action=eventPropositionConfirm&typeId=calendar-"+$(this).attr("data-idCal")+"&_idEvt="+$(this).attr("data-idEvt");
		let redirUrl="?ctrl=calendar&notify=";
		let confirmParams={
			title:"<?= Txt::trad("CALENDAR_evtProposition") ?> :",
			content:$(this).attr("data-details"),//Détails de l'evt (date, auteur, etc)
			buttons:{
				cancel:{text:labelConfirmCancel},
				reject:{btnClass:"btn-dark",  text:"<?= Txt::trad("CALENDAR_evtProposeDecline") ?>",  action:function(){  $.ajax(ajaxUrl+"&isDeclined=true").done(function(){  redir(redirUrl+"CALENDAR_evtProposeDeclined"); });  }},
				accept:{btnClass:"btn-green", text:"<?= Txt::trad("CALENDAR_evtProposeConfirm") ?>",  action:function(){  $.ajax(ajaxUrl+"&isConfirmed=true").done(function(){ redir(redirUrl+"CALENDAR_evtProposeConfirmed"); });  }},
			}
		}
		//// Lance le Confirm (paramétrage par défaut + spécifique)
		$.confirm(Object.assign(confirmParamsDefault,confirmParams));
	});

	/*******************************************************************************************
	 *	DATEPICKER DU MOIS (menu de gauche & display "week")
	 *******************************************************************************************/
	$("#datepickerCalendar").datepicker({
		firstDay:1,										//Début de semaine le lundi
		showOtherMonths:true,							//Affiche les jours des mois précédents/suivants
		defaultDate:"<?= date("Y-m-d",$curTime) ?>",	//Mois/Date affiché
		dateFormat:"yy-mm-dd",							//Utilisé par "dayYmd" ci-dessous
		onSelect:function(dayYmd){ let dateObj=new Date(dayYmd);  redir("?ctrl=calendar&curTime="+(dateObj.getTime()/1000));}//Clique sur une date : redirection
	});
	/////	SURLIGNE LA SEMAINE COURANTE
	<?php foreach($periodDays as $tmpDay){ ?>
		$(".ui-state-active").removeClass("ui-state-active");//Réinit le style du jour de référence
		$("[data-month=<?= date("n",$tmpDay["dayTimeBegin"])-1 ?>] [data-date=<?= date("j",$tmpDay["dayTimeBegin"]) ?>]").addClass("ui-state-highlight");//Surligne les jours de la semaine affichée
	<?php } ?>

	/*******************************************************************************************
	 *	MOBILE : SWIPE GAUCHE/DROITE  &&  BOUTON "TODAY"
	 *******************************************************************************************/
	if(isMobile()){
		////	SWIPE GAUCHE/DROITE POUR AFFICHER LA PERIODE PRECEDENTE/SUIVANTE
		swipeMenuActive=false;																					//Params désactivé : cf menuContext()
		document.addEventListener("touchstart",function(event){ buttonPeriod=null; });							//Début de swipe
		document.addEventListener("touchmove",function(event){													//Lance le swipe de navigation
			if($("#menuMobileMain").isVisible()==false && Math.abs(swipeYstart-event.touches[0].clientY) < 50){	//Menu contextuel masqué && Swipe < 50px d'amplitude verticale
				if((event.touches[0].clientX - swipeXstart) > 100)		{buttonPeriod=".vCalPrev";}				//Swipe à gauche > 100px : période précédente
				else if((swipeXstart - event.touches[0].clientX) > 100)	{buttonPeriod=".vCalNext";}				//Swipe à droite > 100px : période suivante
			}
		});
		document.addEventListener("touchend",function(){														//Fin de swipe :
			if(buttonPeriod!=null)  {$(buttonPeriod).effect("pulsate",{times:1},1000).trigger("click");}		//Pulsate le bouton de la Prev/Next  && Trigger "Click"
		});
		////	AJOUTE LE 1ER BOUTON "TODAY" AU MENU DES DISPLAYMODE  &&  MASQUE CELUI DES AGENDAS SUIVANTS
		$(".vCalDisplayMode:first .vCalDisplayToday").appendTo(".vCalDisplayMode .menuContext");
		$(".vCalDisplayMode:not(:first)").hide();
	}
});

/*******************************************************************************************
 *	AFFICHAGE DES AGENDAS  (lancé via "mainDisplay()" cf "app.js")
*******************************************************************************************/
function moduleDisplay()
{
	if(typeof moduleDisplayTimeout!="undefined")  {clearTimeout(moduleDisplayTimeout);}//Un seul timeout
	moduleDisplayTimeout=setTimeout(function(){																									//Timeout pour récupérer les dimensions globales (cf. affichage sur mobile)
		$(".vSynthDay").outerWidth( ($("#synthHeader").width()-$(".vSynthLabel").width()) / $("#synthHeader .vSynthDay").length );				//Synthese des agendas : width des cellules des jours
		$(".vCalMain").outerHeight( (windowHeight - $("#pageContent").offset().top - <?= empty($_SESSION["livecounterUsers"])?0:60 ?>), true);	//Hauteur des vCalMain en fonction #livecounterMain (chargé + tard)
		$(".vCalVue").outerHeight( $(".vCalMain").innerHeight() - $(".vCalHeader").outerHeight());												//Hauteur des vues Month/Week en fonction de vCalMain
		$(".vEvtBlock").each(function(){ $(this).css("background-color",$(this).attr("data-eventColor")); });									//Bgcolor de chaque evt
		calendarDisplay();																														//Affichage des agendas (VueCalendarMonth / VueCalendarWeek)
		$(".vCalMain").css("visibility","visible");																								//Affiche les agendas après calendarDisplay()
	},20);
}
</script>


<style>
/*Réduit la taille du footer + du livecounter principal*/
#pageFooterHtml, #pageFooterIcon		{display:none;}
#pageFull								{margin-bottom:0px;}
#livecounterMain						{max-height:60px; padding:5px 40px!important;}

/*Menu du module (gauche)*/
#eventPropositionPulsate				{float:right; margin:-10px;}
.eventProposition						{padding:10px; margin-top:10px;}
#calsList								{max-height:400px; overflow-y:auto; padding:5px;}
#calsListLabel 							{margin-bottom:10px;}
#calsList .calsListCalendar				{line-height:25px;}/*Label de chaque agenda*/
#calsList .menuLauncher					{display:none;}/*menu context de chaque agenda*/
#calsList>div:hover .menuLauncher		{display:inline; margin-left:5px;}/*idem*/
#calsList .submitButtonInline			{display:none; margin-top:15px;}/*bouton d'affichage des agendas*/
#displayAdminCals						{display:inline; float:right; height:18px;}
#calsList:not(:hover) #displayAdminCals {visibility:hidden;}
#menuCategory>div						{margin:10px 0px;}
#menuCategory>div .linkSelect			{font-style:italic;}
#datepickerCalendar						{margin-top:20px; margin-bottom:10px;}
.ui-datepicker							{box-shadow:none;}/*Datepicker*/
.ui-datepicker thead					{display:none;}/*pas de libellé des jours*/
.ui-datepicker .ui-state-default		{padding:7px;}/*Cellules des jours*/

/*Synthese des agendas*/
#synthBlock.miscContainer				{padding:2px 8px; margin-bottom:20px;}/*surcharge*/
#synthTable								{display:table; width:100%; max-width:100%;}
#synthHeader, .vSynthLine				{display:table-row;}
#synthHeader							{font-size:0.9em!important;}
#synthHeader .vSynthDayCurDay			{color:#c00;}
.vSynthLabel							{display:table-cell; width:150px; white-space:nowrap; padding-right:10px; vertical-align:middle;}
.vSynthDay								{display:table-cell; vertical-align:middle; text-align:center; height:22px;}
.vSynthDayEvts							{display:table; width:100%; height:100%;}
.vSynthDayEvt							{display:table-cell; border-left:transparent;}
.vSynthDayEvts:hover					{opacity:0.8;}
.vSynthDayEvtTooltip					{text-align:left;}
.vSynthDayEvtTooltip	ul				{margin:0px; margin-top:5px; padding-left:10px;}
.vSynthDayCal							{background:#ddd; border:dotted 1px #eee;}
.vSynthDayCal.vSynthDayCalWE			{background:#ccc;}

/*Agendas : conteneur + menu d'affichage + label des jours*/
.vCalMain								{min-height:500px; padding:0px; visibility:hidden;}/*Masqué le tps du calcul de l'affichage*/
.vCalMain:not(:last-child)				{margin-bottom:40px;}
.vCalVue								{max-width:100%; width:100%;}
.vCalHeader								{display:table; width:100%;}
.vCalHeader>div							{display:table-cell; width:33%; padding:10px; vertical-align:middle;}
.vCalTitleLabel,.vCalMonthLabel			{font-size:1.1em; margin-inline:5px;}/*Libellé de l'agenda et mois affiché*/
.vCalPeriod								{text-align:center;}
.vCalPrevNext							{padding:12px; border-radius:5px;}
.vCalPrevNext:hover						{background-color:#eee;}
[id^=monthsYearsMenu]					{width:300px; overflow:visible;}
#monthsYearsMenuContainer a				{display:inline-block; width:85px; padding:5px; text-align:left;}
.vCalDisplayMode						{text-align:right; padding-right:15px!important;}
.vCalDisplayMode button					{border-radius:5px;}
.vCalDisplayToday						{margin-right:10px;}/*Bouton "aujourd'hui"*/
.vCalLabelDays							{height:25px; padding:4px; text-align:center; text-transform:capitalize;}

/*Evenements*/
.vEvtBlock										{height:20px; min-height:20px; margin:0px; padding:3px; padding-right:20px; box-shadow:1px 1px 2px #555; border-radius:4px!important; cursor:pointer;}/*padding-right pour le menu burger*/
.vEvtBlock[data-pastEvent='true']:not(:hover)	{filter:brightness(0.9);}/*événements passés (sauf si survolé : cf. menu context)*/
.vEvtLabel										{overflow:hidden; font-size:0.95em; font-weight:normal; color:white!important;}
.vEvtLabel img									{margin-left:5px;}

/*MOBILE*/
@media screen and (max-width:1024px){
	.vCalMain						{width:100%; box-shadow:none; margin-bottom:0;}
	.vCalHeader						{white-space:nowrap;}
	.vCalHeader>div					{width:auto; padding:4px; font-size:0.9em; text-transform:lowercase;}
	.vCalTitleLabel,.vCalMonthLabel	{margin:0px;}
	.vCalTitleLabel					{vertical-align:middle; max-width:150px; display:inline-block; overflow:hidden; text-overflow:ellipsis;}/*Max-width avec inline-block + hidden + ellipsis*/
	.vCalTitleLabel::first-letter	{text-transform:uppercase}
	.vCalDisplayMode				{padding-right:10px!important;}
	.vCalDisplayMode button			{padding:10px 8px;}
	.vCalDisplayToday				{margin-top:20px; padding:12px 20px;}/*Afficher aujourd'hui*/
	.vEvtBlock						{height:25px; min-height:25px; overflow:hidden; padding-right:0px;}/*padding-right : pas menu burger*/
	.vEvtLabel						{text-transform:lowercase; white-space:normal!important;}/*longs mots splités sur plusieurs lignes*/
	.vCalTitle .personImgSmall, .vCalDisplayMode img, .vEvtLabelDate 	{display:none!important;}
}

/* IMPRESSION */
@media print{
	@page											{size:landscape;}/*format paysage*/
	body											{-webkit-print-color-adjust:exact; print-color-adjust:exact;}/*conserve les couleurs des evts*/
	.vCalMain, .vCalVue, .vCalVue>*, .vWeekTable	{width:1200px!important; max-width:1200px!important; max-height:98%!important;}
	.vEvtBlock										{max-width:165px!important;}/*1200 % 7*/
	.vCalMain										{box-shadow:none;}
	.vCalMain:not(:last-child)						{page-break-after:always;}/*saut de page après chaque agenda (sauf le dernier)*/
	.vCalHeader>div									{padding:0px 10px 0px 20px !important; font-size:1.1em;}
	.vCalPeriod										{text-align:right;}
	.vEvtLabel										{font-size:0.9em;}
	.vWeekScroller									{overflow:visible!important;}/*pas d'overflow scroll en affichage "week"*/
	#synthBlock, .vCalPrevNext, .vCalDisplayMode, .vMonthWeekNbYear	{display:none!important;}
}
</style>


<div id="pageFull">
	<div id="pageMenu">
		<!--PROPOSITIONS D'EVT-->
		<?php if(!empty($eventPropositions)){ ?>
			<div class="miscContainer">
				<legend><?= Txt::trad("CALENDAR_evtProposition") ?><img src="app/img/importantBig.png" id="eventPropositionPulsate" class="pulsate"></legend>
				<?php
				////	Affiche chaque proposition d'événement 
				foreach($eventPropositions as $tmpProposition){
					$tmpEvt=$tmpProposition["evt"];
					$tmpCal=$tmpProposition["cal"];
					$evtDetails=$evtDetailsBis=htmlspecialchars($tmpEvt->title,ENT_COMPAT)."<hr>".Txt::dateLabel($tmpEvt->dateBegin,"labelFull",$tmpEvt->dateEnd);
					$evtDetailsBis.="<hr>".ucfirst(Txt::trad("OBJECTcalendar"))." : ".$tmpCal->title."<hr>".Txt::trad("CALENDAR_evtProposedBy")." ".$tmpEvt->autorDate();
					if($tmpEvt->description)  {$evtDetailsBis.="<hr>".ucfirst(Txt::trad("description"))." : ".Txt::reduce($tmpEvt->description);}
					echo '<div class="eventProposition optionSelect" data-details="'.strip_tags($evtDetailsBis,"<br><hr>").'" data-idCal="'.$tmpCal->_id.'" data-idEvt="'.$tmpEvt->_id.'" '.Txt::tooltip($evtDetailsBis).'>'.$evtDetails.'</div>';
				} 
				?>
			</div>
		<?php } ?>

		<div class="miscContainer">
			<?php
			////	AJOUTER UN EVT
			if(count($displayedCalendars)==1 && $displayedCalendars[0]->addOrProposeEvt())
				{echo '<div class="menuLine" onclick="lightboxOpen(\''.MdlCalendarEvent::getUrlNew().'&_idCal='.$displayedCalendars[0]->_id.'\')" '.Txt::tooltip($displayedCalendars[0]->title." : ".$displayedCalendars[0]->addEventLabel).'><div class="menuIcon"><img src="app/img/plus.png"></div><div>'.Txt::trad("CALENDAR_addEvt").'</div></div><hr>';}
			////	LISTE DES AGENDAS DISPONIBLES
			if(!empty($readableCalendars)){
				$displayAdminCals=(Ctrl::$curUser->isSpaceAdmin() && empty($_SESSION["displayAdmin"]))  ?  '<img src="app/img/plusSmall.png" id="displayAdminCals" onclick="redir(\'?ctrl='.Req::$curCtrl.'&displayAdmin=true\')" title="'.Txt::trad("HEADER_displayAdmin").' : '.Txt::trad("HEADER_displayAdminInfo").'">'  :  null;
				echo '<form action="index.php" id="calsList">
						<div id="calsListLabel">'.Txt::trad("CALENDAR_calsList").' :'.$displayAdminCals.'</div>
						<input type="hidden" name="curTime" value="'.Req::param("curTime").'"/>';
						foreach($readableCalendars as $tmpCal){
							$boxChecked=in_array($tmpCal->_id,array_column($displayedCalendars,"_id"))  ?  "checked"  :  null;
							echo '<div class="calsListCalendar">
									<input type="checkbox" name="displayedCalendars[]" value="'.$tmpCal->_id.'" id="boxCal'.$tmpCal->_typeId.'" onchange="$(\'#calsList .submitButtonInline\').show()" '.$boxChecked.'>
									<label for="boxCal'.$tmpCal->_typeId.'" '.Txt::tooltip($tmpCal->description).'>'.$tmpCal->title.'</label> '.(Req::isMobile()==false?$tmpCal->contextMenu(["launcherIcon"=>"inlineSmall"]):null).'
								</div>';
						}
				echo Txt::submitButton("show",false).'</form><hr>';
			}
			////	MENU DES CATEGORIES
			echo MdlCalendarCategory::displayMenu();
			?>

			<!--CREER AGENDA PARTAGE-->
			<?php if(MdlCalendar::addRight()){ ?>
			<div class="menuLine" onclick="lightboxOpen('<?= MdlCalendar::getUrlNew() ?>');" <?= Txt::tooltip("CALENDAR_addSharedCalendarTooltip") ?>>
				<div class="menuIcon"><img src="app/img/calendar/calendarAdd.png"></div>
				<div><?= Txt::trad("CALENDAR_addSharedCalendar") ?></div>
			</div>
			<?php } ?>

			<!--EVT PROPRIO-->
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

			<!--CALENDRIER MOIS VIA LE DATEPICKER DE JQUERY-UI-->
			<?= $displayMode!="month" ? "<div id='datepickerCalendar'></div>" : null ?>
		</div>
	</div>

	<div id="pageContent">

		<!--SYNTHESE DES AGENDAS -->
		<?php if(!empty($periodSynthese)){ ?>
			<div id="synthBlock" class="miscContainer">
				<div id="synthTable">
					<!--HEADER DE LA SYNTHESE-->
					<div id="synthHeader">
						<div class="vSynthLabel">&nbsp;</div>
						<?php foreach($periodSynthese as $dayYmd=>$tmpDay)  {echo '<div class="vSynthDay '.($dayYmd==date("Y-m-d")?"vSynthDayCurDay":null).'">'.(int)date("d",$tmpDay["dayTimeBegin"]).'</div>';} ?>
					</div>
					<!--AFFICHE CHAQUE AGENDA : LIBELLE & CHAQUE JOUR DE L'AGENDA-->
					<?php foreach($displayedCalendars as $tmpCal){ ?>
					<div class="vSynthLine">
						<div class="vSynthLabel" onclick="$('#calBlock<?= $tmpCal->_typeId ?>').scrollTo();"><?= $tmpCal->title ?></div>
						<?php
						foreach($periodSynthese as $tmpDay){
							//Tooltip des evts du jour
							$tmpEvtTooltip='<div class=\'vSynthDayEvtTooltip\'>'.Txt::dateLabel($tmpDay["dayTimeBegin"],"dateBasic").' - '.$tmpCal->title.' :<br>';
							foreach($tmpDay["dayEvtList"][$tmpCal->_id] as $tmpEvt)	{$tmpEvtTooltip.='<br>'.Txt::dateLabel($tmpEvt->dateBegin,"mini",$tmpEvt->dateEnd).' : '.Txt::reduce($tmpEvt->title,60);}
							$tmpEvtTooltip.='</div>';
							//Cellule des evts du jour
							$syntheseDayCalWE=$syntheseDayEvts=null;
							if(date("N",$tmpDay["dayTimeBegin"])>5)	{$syntheseDayCalWE="vSynthDayCalWE";}
							foreach($tmpDay["dayEvtList"][$tmpCal->_id] as $tmpEvt)	{$syntheseDayEvts.='<div class="vSynthDayEvt" onclick="'.$tmpEvt->openVue().'" style="background-color:'.$tmpEvt->eventColor.'">&nbsp;</div>';}
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
		<div class="vCalMain miscContainer" id="calBlock<?= $tmpCal->_typeId ?>">
			<div class="vCalHeader">
				<!--TITRE DE L'AGENDA-->
				<div class="vCalTitle">
					<?php
					$calLabel='<span class="vCalTitleLabel" '.Txt::tooltip($tmpCal->description).'>'.$tmpCal->title.'</span>';														//Label/Title de l'agenda
					if($tmpCal->type=="user" && Req::isMobile()==false)  {$calLabel.=Ctrl::getObj("user",$tmpCal->_idUser)->profileImg(true,true);}									//Icone de l'user
					echo Ctrl::$curUser->isUser()  ?  $tmpCal->contextMenu(["launcherIcon"=>(Req::isMobile()?"inlineSmall":"inlineBig"),"launcherLabel"=>$calLabel])  :  $calLabel;	//Ajoute le label au launcher du menu ?
					?>
				</div>
				<!--LABEL DE LA PERIODE AFFICHEE  &&  MENU CONTEXT MONTHS/YEARS  &&  PRECEDENT/SUIVANT-->
				<div class="vCalPeriod">
					<span class="vCalPrevNext vCalPrev" onclick="redir('?ctrl=calendar&curTime=<?= $timePrev ?>')" <?= Txt::tooltip("CALENDAR_periodPrev") ?>><img src="app/img/navPrev.png"></span>
					<span class="menuLauncher vCalMonthLabel" for="monthsYearsMenu<?= $tmpCal->_typeId ?>"><?= ucfirst($monthLabel) ?></span>
					<?php if(!empty($monthsYearsMenu))  {echo "<div class='menuContext' id='monthsYearsMenu".$tmpCal->_typeId."'><div id='monthsYearsMenuContainer'>".$monthsYearsMenu."</div></div>";} ?>
					<span class="vCalPrevNext vCalNext" onclick="redir('?ctrl=calendar&curTime=<?= $timeNext ?>')" <?= Txt::tooltip("CALENDAR_periodNext") ?>><img src="app/img/navNext.png"></span>
				</div>
				<!--"AUJOURD'HUI"  &&  AFFICHAGE MONTH/WEEK/ETC-->
				<div class="vCalDisplayMode">
					<button class="vCalDisplayToday" onclick="redir('?ctrl=calendar&curTime=<?= time() ?>')"><?= ucfirst(Txt::trad("today")) ?></button>
					<button class="menuLauncher" for="menuDisplayMode<?= $tmpCal->_typeId ?>"><img src="app/img/calendar/display<?= ucfirst($displayMode) ?>.png"> <?= Txt::trad("CALENDAR_display_".$displayMode) ?> <img src="app/img/arrowBottom.png"></button>
					<div class="menuContext" id="menuDisplayMode<?= $tmpCal->_typeId ?>">
					<?php foreach($displayModeList as $displayModeTmp){ ?>
						<div class="menuLine <?= $displayModeTmp==$displayMode?"linkSelect":null ?>" onclick="redir('?ctrl=calendar&displayMode=<?= $displayModeTmp ?>')"><div class="menuIcon"><img src="app/img/calendar/display<?= ucfirst($displayModeTmp) ?>.png"></div><div><?= ucfirst(Txt::trad("CALENDAR_display_".$displayModeTmp)) ?></div></div>
					<?php } ?>
					</div>
				</div>
			</div>
			<!-- AGENDA "VueCalendarMonth.php" || "VueCalendarWeek.php"-->
			<?= $tmpCal->calendarVue ?>
		</div>
		<?php } ?>

		<!--AUCUN AGENDA-->
		<?php if(empty($displayedCalendars))  {echo '<div class="miscContainer emptyContainer">'.Txt::trad("CALENDAR_noCalendarDisplayed").'</div>';} ?>
	</div>
</div>