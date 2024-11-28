<script>
$(function(){
	/*******************************************************************************************
	 *	DIMENSIONNE LES AGENDAS
	 *******************************************************************************************/
	$(".vSyntheseDay").outerWidth( ($("#syntheseHeader").width() - $("#syntheseHeader .vSyntheseLabel").width()) / $("#syntheseHeader .vSyntheseDay").length );	//Synthese des agendas : width des cellules des jours
	$(".vCalMain").outerHeight( $(window).height() - $("#pageFullContent").offset().top - <?= !empty($_SESSION["livecounterUsers"]) ? 65 : 5 ?>);	//Hauteur des vCalMain en fonction de la hauteur dispo (cf. #livecounterMain)
	$(".vCalVue").outerHeight( $(".vCalMain").height() - $(".vCalHeader").outerHeight(true));														//Hauteur des vues Month/Week ajustées en fonction de vCalMain
	calendarDisplay();																																//Calcule ensuite l'affichage des vues Month/Week et des evts
	$(".vCalMain").css("visibility","visible");																										//Affiche enfin les agendas (masqués par défaut)

	/*******************************************************************************************
	 *	DATEPICKER DANS LE MENU DE GAUCHE (sauf pour $displayMode=="month")
	 *******************************************************************************************/
	$("#datepickerCalendar").datepicker({
		defaultDate:"<?= date("Y-m-d",$curTime) ?>",	//cf. "dateFormat" ci-après
		dateFormat:"yy-mm-dd",							//Utilisé par le "dateYmd" suivant (ex: "2050-01-01")
		firstDay:1,										//Commence chaque semaine par lundi
		showOtherMonths:true,							//Affiche les jours des mois précédents/suivants
		onSelect:function(dateYmd){						//Clique sur une date : redirection
			var dateObject=new Date(dateYmd);
			redir("?ctrl=calendar&curTime="+Math.round(dateObject.valueOf()/1000));
		}
	});

	/*******************************************************************************************
	 *	DATEPICKER : SURLIGNE LES JOURS AFFICHÉS
	 *******************************************************************************************/
	<?php foreach($periodDays as $tmpDay){ ?>
		$(".ui-state-active").removeClass("ui-state-active");//Réinit le style du jour de référence
		$("[data-month=<?= date("n",$tmpDay["timeBegin"])-1 ?>] [data-date=<?= date("j",$tmpDay["timeBegin"]) ?>]").addClass("ui-state-highlight");//Surligne les jours de la semaine affichée
	<?php } ?>

	/*******************************************************************************************
	 *	MOBILE : SWIPE GAUCHE/DROITE POUR CHANGER DE PERIODE  &  DEPLACE LE BOUTON "TODAY"
	 *******************************************************************************************/
	if(isMobile()){
		////	SWIPE GAUCHE/DROITE : AFFICHE LA PERIODE PRECEDENTE/SUIVANTE
		isCalendarSwipe=true;																					//"surcharge" le swipe via "menuContextDisplay()" (utilise "swipeStartX" and Co)
		document.addEventListener("touchmove",function(event){													//Lance le swipe de navigation
			if(Math.abs(swipeStartY-event.touches[0].clientY) < 50 && $("#menuMobileMain").isVisible()==false){	//Swipe < 50px d'amplitude verticale && Menu contextuel pas affiché
				if((event.touches[0].clientX - swipeStartX) > 100)		{var buttonPeriod=".vCalPrev";}			//Swipe de 100px à gauche : affiche la période précédente (150px ou+ du bord de page : pour pas interférer avec "menuMobileDisplay()" > cf. "swipeWidth")
				else if((swipeStartX - event.touches[0].clientX) > 100)	{var buttonPeriod=".vCalNext";}			//Swipe de 100px à droite : affiche la période suivante
				$(buttonPeriod).effect("pulsate",{times:1},1000);												//Fait clignoter le bouton de changement de période 
				if(typeof calendarSwipeTimeout!="undefined")  {clearTimeout(calendarSwipeTimeout);}				//Pas de cumule des setTimeout avant la fin du "touchmove"
				calendarSwipeTimeout=setTimeout(function(){  $(buttonPeriod).trigger("click");  },100);			//Trigger "Click" sur ce bouton, avec un timeOut
			}
		});
		////	DEPLACE LE BOUTON "TODAY" AVEC LE MENU DES DISPLAYMODE
		$(".vCalDisplayMode").each(function(){
			$(this).find(".vCalDisplayToday").appendTo(".vCalDisplayMode .menuContext");
		});
	}
});
</script>


<style>
/*Réduit la taille du footer + du livecounter principal*/
#pageFooterHtml, #pageFooterIcon	{display:none;}
#pageFull							{margin-bottom:0px;}
#livecounterMain					{max-height:60px; padding:5px 40px!important;}

/*Menu du module (gauche)*/
#calsList							{max-height:500px; overflow-y:auto; padding:5px;}
#calsListLabel 						{margin-bottom:10px;}
#calsList .calsListCalendar			{line-height:25px;}/*Label de chaque agenda*/
#calsList .menuLaunch				{display:none;}/*menu context de chaque agenda*/
#calsList>div:hover .menuLaunch		{display:inline; margin-left:5px;}/*idem*/
#calsList .submitButtonInline		{display:none; margin-top:15px;}/*bouton d'affichage des agendas*/
#menuCategory>div					{margin:10px 0px;}
#menuCategory>div .linkSelect		{font-style:italic;}
#datepickerCalendar					{margin-top:20px; margin-bottom:10px;}
.ui-datepicker						{box-shadow:none;}/*Datepicker*/
.ui-datepicker thead				{display:none;}/*pas de libellé des jours*/
.ui-datepicker .ui-state-default	{padding:7px;}/*Cellules des jours*/

/*Synthese des agendas*/
#syntheseBlock.miscContainer		{padding:8px!important; margin-bottom:20px;}/*surcharge*/
#syntheseTable						{display:table; width:100%;}
#syntheseHeader, .vSyntheseLine		{display:table-row;}
.vSyntheseDayCurDay					{color:#c00;}
.vSyntheseLabel						{display:table-cell; width:100px; white-space:nowrap; padding-right:10px; vertical-align:middle;}
.vSyntheseDay						{display:table-cell; vertical-align:middle; text-align:center; height:22px;}
.vSyntheseDayEvts					{display:table; width:100%; height:100%;}
.vSyntheseDayEvt					{display:table-cell; border-left:transparent;}
.vSyntheseDayEvts:hover				{opacity:0.5;}
.vSyntheseDayEvtTooltip				{text-align:left;}
.vSyntheseDayEvtTooltip	ul			{margin:0px; margin-top:5px; padding-left:10px;}
.vSyntheseDayCal					{background:#ddd; border:dotted 1px #eee;}
.vSyntheseDayCal.vSyntheseDayCalWE	{background:#ccc;}

/*Agendas : conteneur + menu d'affichage + label des jours*/
.vCalMain							{min-height:500px; padding:0px; visibility:hidden;}/*Agendas masqués par défaut (pas "display:none") puis affichés via calendarDisplay()*/
.vCalMain:not(:last-child)			{margin-bottom:40px;}
.vCalHeader							{display:table; width:100%;}
.vCalHeader>div						{display:table-cell; width:33%; padding:10px; vertical-align:middle;}
.vCalTitleLabel, .vCalMonthLabel	{font-size:1.1em; margin:0px 8px;}/*Libellé de l'agenda et mois affiché*/
.vCalPeriod							{text-align:center;}
[id^=calMonthPeriodMenu]			{width:300px; overflow:visible;}
#calMonthPeriodMenuContainer a		{display:inline-block; width:85px; padding:5px; text-align:left;}
.vCalDisplayMode					{text-align:right;}
.vCalDisplayMode button				{border-radius:5px;}
.vCalDisplayToday					{margin-right:10px;}/*Afficher aujourd'hui*/
.vCalLabelWeekDays					{height:20px; padding:3px; text-align:center;}
.vCalLabelToday						{font-size:1.15em; background-color:#ddd; color:#07f; border-radius:5px 5px 0px 0px;}/*Libellé d'aujourd'hui (Affichage "week" : Header  /  Affichage "month" : Cell du jour)*/

/*Evenements*/
.vEvtBlock							{margin:0px; padding-right:20px!important; box-shadow:1px 1px 2px #555; border-radius:5px; cursor:pointer;}/*padding-right pour le menu burger (pas de "overflow:hidden"!)*/
.vEvtBlockPast:not(:hover)			{filter:brightness(90%) opacity(<?= $displayMode=="month"?"75%":"85%" ?>);}/*événements passés opacifiés (sauf si survolé : cf. menu context)*/
.vEvtLabel							{overflow:hidden; font-size:0.95em; font-weight:normal; color:white!important;}
.vEvtLabel img						{max-height:12px;}
.vEvtImportant						{margin-left:5px;}

/*MOBILE*/
@media screen and (max-width:1024px){
	.vCalMain, .vCalVue				{max-width:100%!important;}
	.vCalMain						{box-shadow:none; margin-bottom:0;}
	.vCalHeader						{white-space:nowrap;}
	.vCalHeader>div					{width:auto; padding:4px; font-size:0.9em; text-transform:lowercase;}
	.vCalTitleLabel, .vCalMonthLabel{margin:0px 3px;}
	.vCalTitleLabel					{vertical-align:middle; max-width:150px; display:inline-block; overflow:hidden; text-overflow:ellipsis;}/*Max-width avec inline-block + hidden + ellipsis*/
	.vCalTitleLabel::first-letter	{text-transform:uppercase}
	.vCalDisplayMode button			{padding:10px 7px;}
	.vCalDisplayToday				{margin-top:15px;}
	.vCalLabelWeekDays, .vEvtLabel	{font-size:0.85em;}
	.vEvtBlock						{overflow:hidden;}
	.vEvtLabel						{text-transform:lowercase; white-space:normal;}/*longs mots sur plusieurs lignes*/
	.vCalTitle .personImgSmall, .vCalDisplayMode img 	{display:none!important;}
}

/* IMPRESSION */
@media print{
	@page							{size:landscape;}/*format paysage*/
	body							{-webkit-print-color-adjust:exact; print-color-adjust:exact;}/*conserve les couleurs des evts*/
	.vCalMain						{box-shadow:none;}
	.vCalMain:not(:last-child)		{page-break-after:always;}/*saut de page après chaque agenda (sauf le dernier)*/
	.vCalHeader>div					{padding:25px 0px;}
	.vCalPeriod						{text-align:right;}
	.vEvtLabel						{font-size:0.9em;}
	.vWeekScroller					{overflow:visible!important;}/*pas d'overflow scroll en affichage "week"*/
	.vCalMain, .vCalVue, .vWeekScroller, .vWeekHeader, .vWeekTable								{height:100%!important; max-height:100%!important;}
	#syntheseBlock, .vCalPrev, .vCalNext, .vCalDisplayMode, .vMonthYearWeekNum, .vWeekQuarter	{display:none!important;}
}
</style>


<div id="pageFull">

	<div id="pageModuleMenu">
		<div id="pageModMenu" class="miscContainer">
			<?php	
			////	CONFIRMATION DE PROPOSITIONS D'EVT
			if(!empty($eventProposition))  {echo $eventProposition;}

			////	AJOUTER UN EVT
			if(count($displayedCalendars)==1 && $displayedCalendars[0]->addOrProposeEvt())
				{echo '<div class="menuLine" onclick="lightboxOpen(\''.MdlCalendarEvent::getUrlNew().'&_idCal='.$displayedCalendars[0]->_id.'\')" '.Txt::tooltip($displayedCalendars[0]->title." : ".$displayedCalendars[0]->addEventLabel).'><div class="menuIcon"><img src="app/img/plusSmall.png"></div><div>'.Txt::trad("CALENDAR_addEvt").'</div></div><hr>';}

			////	LISTE DES AGENDAS DISPONIBLES
			if(!empty($readableCalendars)){
				echo '<form action="index.php" id="calsList">
						<div id="calsListLabel">'.Txt::trad("CALENDAR_calsList").' :</div>
						<input type="hidden" name="curTime" value="'.Req::param("curTime").'"/>';
						foreach($readableCalendars as $tmpCal){
							echo '<div class="calsListCalendar">
									<input type="checkbox" name="displayedCalendars[]" value="'.$tmpCal->_id.'" id="displayedCal'.$tmpCal->_typeId.'" onchange="$(\'#calsList .submitButtonInline\').fadeIn()" '.(CtrlCalendar::isDisplayedCal($displayedCalendars,$tmpCal)?'checked':null).'>
									<label for="displayedCal'.$tmpCal->_typeId.'" '.Txt::tooltip($tmpCal->description).'>'.$tmpCal->title.'</label> '.(Req::isMobile()==false?$tmpCal->contextMenu(["launcherIcon"=>"inlineSmall"]):null).'
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

	<div id="pageFullContent">

		<!--SYNTHESE DES AGENDAS -->
		<?php if(!empty($periodSynthese)){ ?>
			<div id="syntheseBlock" class="miscContainer">
				<div id="syntheseTable">
					<!--HEADER DE LA SYNTHESE-->
					<div id="syntheseHeader">
						<div class="vSyntheseLabel">&nbsp;</div>
						<?php foreach($periodSynthese as $tmpDay)  {echo '<div class="vSyntheseDay '.(date("Y-m-d",$tmpDay["timeBegin"])==date("Y-m-d")?"vSyntheseDayCurDay":null).'">'.(int)date("d",$tmpDay["timeBegin"]).'</div>';} ?>
					</div>
					<!--AFFICHE CHAQUE AGENDA : LIBELLE & CHAQUE JOUR DE L'AGENDA-->
					<?php foreach($displayedCalendars as $tmpCal){ ?>
					<div class="vSyntheseLine">
						<div class="vSyntheseLabel" onclick="$('#calBlock<?= $tmpCal->_typeId ?>').scrollTo();"><?= $tmpCal->title ?></div>
						<?php
						foreach($periodSynthese as $tmpDay){
							//Tooltip des evts du jour
							$tmpEvtTooltip='<div class=\'vSyntheseDayEvtTooltip\'>'.Txt::dateLabel($tmpDay["timeBegin"],"dateMini").' - '.$tmpCal->title.' :<br>';
							foreach($tmpDay["calsEvts"][$tmpCal->_id] as $tmpEvt)	{$tmpEvtTooltip.='<br>'.Txt::dateLabel($tmpEvt->dateBegin,"mini",$tmpEvt->dateEnd).' : '.Txt::reduce($tmpEvt->title,60);}
							$tmpEvtTooltip.='</div>';
							//Cellule des evts du jour
							$syntheseDayCalWE=$syntheseDayEvts=null;
							if(date("N",$tmpDay["timeBegin"])>5)	{$syntheseDayCalWE="vSyntheseDayCalWE";}
							foreach($tmpDay["calsEvts"][$tmpCal->_id] as $tmpEvt)	{$syntheseDayEvts.='<div class="vSyntheseDayEvt" onclick="'.$tmpEvt->openVue().'" style="background-color:'.$tmpEvt->eventColor.'">&nbsp;</div>';}
							echo '<div class="vSyntheseDay vSyntheseDayCal '.$syntheseDayCalWE.'">
									<div class="vSyntheseDayEvts" '.Txt::tooltip($tmpEvtTooltip).'>'.$syntheseDayEvts.'</div>
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
				<!--PERIODE AFFICHEE  &&  PRECEDENT/SUIVANT &&  AUJOURD'HUI-->
				<div class="vCalPeriod">
					<img src="app/img/navPrev.png" class="vCalPrev" onclick="redir('?ctrl=calendar&curTime=<?= $timePrev ?>')" <?= Txt::tooltip("CALENDAR_periodPrevious") ?>>
					<span class="menuLaunch vCalMonthLabel" for="calMonthPeriodMenu<?= $tmpCal->_typeId ?>"><?= ucfirst($monthLabel) ?></span>
					<?php if(!empty($calMonthPeriodMenu))  {echo "<div class='menuContext' id='calMonthPeriodMenu".$tmpCal->_typeId."'><div id='calMonthPeriodMenuContainer'>".$calMonthPeriodMenu."</div></div>";} ?>
					<img src="app/img/navNext.png" class="vCalNext" onclick="redir('?ctrl=calendar&curTime=<?= $timeNext ?>')" <?= Txt::tooltip("CALENDAR_periodNext") ?>>
				</div>
				<!--AFFICHAGE TODAY / MONTH / WEEK / 7DAYS / 3DAYS -->
				<div class="vCalDisplayMode">
					<button class="vCalDisplayToday" onclick="redir('?ctrl=calendar&curTime=<?= time() ?>')"><?= ucfirst(Txt::trad("today")) ?></button>
					<button class="menuLaunch" for="menuDisplayMode<?= $tmpCal->_typeId ?>"><img src="app/img/calendar/display<?= ucfirst($displayMode) ?>.png"> <?= Txt::trad("CALENDAR_display_".$displayMode) ?> <img src="app/img/arrowBottom.png"></button>
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
		<?php if(empty($displayedCalendars))  {echo "<div class='emptyContainer'>".Txt::trad("CALENDAR_noCalendarDisplayed")."</div>";} ?>
	</div>
</div>