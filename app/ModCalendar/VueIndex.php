<script>
////	INIT
$(function(){
	/*******************************************************************************************
	 *	DIMENSIONNE LES AGENDAS
	 *******************************************************************************************/
	setTimeout(function(){
		if($("#syntheseTable").exist()){																						//Synthese des agendas : taille des cellules de jours
			let syntheseDaysWidth=$("#syntheseLineHeader").width() - $("#syntheseLineHeader .vSyntheseLabel").width();			//- Width dispo pour les cellules des jours
			$(".vSyntheseDay").outerWidth( Math.round(syntheseDaysWidth / $("#syntheseLineHeader .vSyntheseDay").length) );		//- Applique le Width des cellules des jours
		}
		let marginBottom=($("#livecounterMain").isVisible())  ?  $("#livecounterMain").outerHeight(true)+12  :  5;				//Marge entre le bas de l'agenda et de la page en foncttion du livecounter
		$(".vCalendarBlock").outerHeight( $(window).height() - $("#pageFullContent").offset().top - marginBottom);				//Agendas sur toute la hauteur de la page
		$(".vMonthMain,.vWeekMain").outerHeight( $(".vCalendarBlock").height() - $(".vCalendarHeader").outerHeight(true) );		//Vues des agendas week/month sur toute la hauteur du ".vCalendarBlock"
		$(".vEventBlock").each(function(){  $(this).css("background",$(this).attr("data-eventColor"));  });						//Applique le "background-color" à chaque événement
		calendarDisplay();																										//Calcul les dimensions de vues week / month !
		$(".vCalendarBlock").hide().css("visibility","visible").show();															//Affiche enfin les agendas (masqués par défaut)
	},<?= empty($_SESSION["livecounterUsers"]) ? 50 : 250 ?>);																	//Timeout pour lancer "calendarDisplay()", le tps d'afficher le livecounterUsers

	/*******************************************************************************************
	 *	DATEPICKER DU MENU DU MODULE (JQUERY-UI)
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
	 *	SWIPE GAUCHE/DROITE : AFFICHE LA PERIODE PRECEDENTE/SUIVANTE  (cf. "menuContextInit()")
	 *******************************************************************************************/
	if(isMobile())
	{
		isCalendarSwipe=true;																					//Spécifie à "menuContextInit()" qu'on "surcharge" la gestion du swipe
		document.addEventListener("touchmove",function(event){
			if(Math.abs(swipeStartY-event.touches[0].clientY) < 50){											//Swipe < 50px d'amplitude verticale
				if((event.touches[0].clientX - swipeStartX) > 100)		{var buttonPeriod=".vCalendarPrev";}	//Swipe de 100px à gauche : affiche la période précédente (150px du bord de page minimum : pour pas interférer avec "menuMobileDisplay()" > cf. "swipeWidth")
				else if((swipeStartX - event.touches[0].clientX) > 100)	{var buttonPeriod=".vCalendarNext";}	//Swipe de 100px à droite : affiche la période suivante
				$(buttonPeriod).effect("pulsate",{times:1},1000);												//Fait clignoter le bouton de changement de période 
				if(typeof calendarSwipeTimeout!="undefined")  {clearTimeout(calendarSwipeTimeout);}				//Pas de cumule des setTimeout avant la fin du "touchmove"
				calendarSwipeTimeout=setTimeout(function(){  $(buttonPeriod).trigger("click");  },100);			//Trigger "Click" sur ce bouton, avec un timeOut
			}
		});
	}
});
</script>


<style>
/*Réduit la taille du footer + du livecounter principal*/
#pageFooterHtml, #pageFooterIcon		{display:none;}
#pageFull								{margin-bottom:0px;}
#livecounterMain						{padding:5px 40px!important;}

/*Menus du module*/
#calsList								{max-height:500px; overflow-y:auto; padding:5px;}
#calsListLabel 							{margin-bottom:10px;}
#calsList .calsListCalendar				{line-height:25px;}/*Label de chaque agenda*/
#calsList .menuLaunch					{display:none;}/*menu context de chaque agenda*/
#calsList>div:hover .menuLaunch			{display:inline; margin-left:5px;}/*idem*/
#calsList .submitButtonInline			{display:none; margin-top:15px;}/*bouton d'affichage des agendas*/
#calsListDisplayAll						{float:right;}
#menuCategory>div						{margin:10px 0px;}
#menuCategory>div .linkSelect			{font-style:italic;}
#datepickerCalendar						{margin-top:20px; margin-bottom:10px;}
.ui-datepicker							{box-shadow:none;}/*Datepicker*/
.ui-datepicker thead					{display:none;}/*pas de libellé des jours*/
.ui-datepicker .ui-state-default		{padding:7px;}/*Cellules des jours*/

/*Synthese des agendas*/
#syntheseBlock.miscContainer			{padding:8px!important; margin-bottom:20px;}/*surcharge*/
#syntheseTable							{display:table; width:100%;}
#syntheseLineHeader, .vSyntheseLine		{display:table-row;}
.vSyntheseDayCurDay						{color:#c00;}
.vSyntheseLabel							{display:table-cell; width:160px; padding:2px; padding-left:5px; vertical-align:middle;}
.vSyntheseLine:hover .vSyntheseLabel	{color:#c00;}
.vSyntheseDay							{display:table-cell; vertical-align:middle; text-align:center; height:22px;}
.vSyntheseDayEvts						{display:table; width:100%; height:100%;}
.vSyntheseDayEvt						{display:table-cell; border-left:transparent;}
.vSyntheseDayEvts:hover					{opacity:0.5;}
.vSyntheseDayEvtTooltip					{text-align:left;}
.vSyntheseDayEvtTooltip	ul				{margin:0px; margin-top:5px; padding-left:10px;}
.vSyntheseDayCal						{background:#ddd; border:dotted 1px #eee;}
.vSyntheseDayCal.vSyntheseDayCalWE		{background:#ccc;}

/*Agendas*/
.vCalendarBlock							{min-height:500px; padding:0px; visibility:hidden;}/*agenda masqué par défaut  : cf. "calendarDisplay()"*/
.vCalendarBlock:not(:last-child)		{margin-bottom:50px;}
.vCalendarHeader						{display:table; width:100%;}
.vCalendarHeader>div					{display:table-cell; width:33%; padding:8px; padding-bottom:12px; vertical-align:middle;}
.vCalendarTitle, .vCalendarPeriodLabel 	{font-size:1.15em;}
.vCalendarTitle .personImgSmall			{margin-left:20px;}
.vCalendarPeriod						{text-align:center;}
.vCalendarPeriodLabel					{margin:0px 15px;}
[id^=calMonthPeriodMenu]				{width:300px; overflow:visible;}
#calMonthPeriodMenuContainer a			{display:inline-block; width:85px; padding:5px; text-align:left;}
.vCalendarDisplayMode					{text-align:right;}
.vCalendarDisplayMode button			{border-radius:5px;}
.vCalendarDisplayToday					{margin-right:10px;}

/*Evenements*/
.vEventBlock							{margin:0px; padding:4px; padding-right:20px; box-shadow:1px 1px 2px #555; border-radius:5px; cursor:pointer;}/*"padding-right" pour le menu "burger" (pas de "overflow:hidden" sinon on masque le menuContext)*/
.vEventBlock .objMenuContextFloat		{top:3px; right:3px;}/*Surchage le menu "burger"*/
.vEventBlock .objMenuContextFloat img	{height:17px; width:15px;}/*idem*/
.vEventBlockPast:not(:hover)			{opacity:0.6;}/*événements passés opacifiés (cf. "CtrlCalendar"). Pas s'il sont survolés, pour pouvoir afficher les menuContext*/
.vEventLabel							{overflow:hidden; font-size:0.95em; font-weight:normal; color:white!important;}
.vEventLabel img						{max-height:12px;}

/*MOBILE*/
@media screen and (max-width:1023px){
	.vCalendarBlock						{margin:0px; box-shadow:none;}
	.vCalendarHeader					{font-size:0.85em; white-space:nowrap!important;}
	.vCalendarHeader>div				{width:auto!important; padding:5px 5px 10px 5px;}
	.vCalendarPeriodLabel				{margin:0px 5px;}
	.vCalendarDisplayMode button		{padding:7px;}
	.vCalendarTitle .personImgSmall, .vCalendarDisplayMode img, .vCalendarDisplayToday, #calsListDisplayAll	{display:none!important;}
	.vEventBlock						{overflow:hidden; padding:2px;}/*pas de "padding-right" car menu "burger" masqué*/
	.vEventLabel						{text-transform:lowercase; font-size:0.8em; line-height:0.9em;}
}

/* IMPRESSION */
@media print{
	@page									{size:landscape;}
	.vMonthMain, .vWeekMain, .vWeekScroller	{height:80%!important; max-height:80%!important;}
	.vCalendarBlock							{margin:0px; box-shadow:none;}
	.vCalendarBlock:not(:last-child)		{page-break-after:always;}/*saut de page après chaque agenda (sauf le dernier de la liste)*/
	.vCalendarHeader						{margin:15px 0px;}
	.vCalendarPeriod						{text-align:left;}
	.vEventBlock							{background:white!important; overflow:hidden;}
	.vEventBlockPast						{opacity:1!important;}
	.vEventLabel							{color:black!important;}
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
				{echo "<div class='menuLine' onclick=\"lightboxOpen('".MdlCalendarEvent::getUrlNew()."&_idCal=".$displayedCalendars[0]->_id."')\" title=\"".Txt::tooltip($displayedCalendars[0]->title." : ".$displayedCalendars[0]->addEventLabel)."\"><div class='menuIcon'><img src='app/img/plus.png'></div><div>".Txt::trad("CALENDAR_addEvt")."</div></div><hr>";}

			////	LISTE DES AGENDAS DISPONIBLES
			if(!empty($readableCalendars)){
				$calsListDisplayAll=(Ctrl::$curUser->isGeneralAdmin() && $_SESSION["calsListDisplayAll"]==false)  ?  '<div id="calsListDisplayAll"><a onclick="redir(\'?ctrl=calendar&calsListDisplayAll=1\')" title="'.Txt::trad("CALENDAR_calsListDisplayAll").'"><img src="app/img/plusSmall.png"></a></div>'  :  null;
				echo '<form action="index.php" id="calsList">
						<div id="calsListLabel">'.Txt::trad("CALENDAR_calsList").' :'.$calsListDisplayAll.'</div>';//"Agendas disponibles" et bouton "Afficher tous les agendas" pour les admins
						foreach($readableCalendars as $tmpCal){
							echo '<div class="calsListCalendar">
									<input type="checkbox" name="displayedCalendars[]" value="'.$tmpCal->_id.'" id="displayedCal'.$tmpCal->_typeId.'" onchange="$(\'#calsList .submitButtonInline\').fadeIn()" '.(CtrlCalendar::isDisplayedCal($displayedCalendars,$tmpCal)?'checked':null).'>
									<label for="displayedCal'.$tmpCal->_typeId.'" title="'.Txt::tooltip($tmpCal->description).'">'.$tmpCal->title.'</label> '.(Req::isMobile()==false?$tmpCal->contextMenu(["launcherIcon"=>"inlineSmall"]):null).'
								</div>';
						}
				echo Txt::submitButton("show",false).'<input type="hidden" name="curTime" value="'.Req::param("curTime").'"/></form><hr>';//Fin du formulaire
			}

			////	MENU DES CATEGORIES
			echo MdlCalendarCategory::displayMenu();
			?>

			<!--CREER AGENDA PARTAGE-->
			<?php if(MdlCalendar::addRight()){ ?>
			<div class="menuLine" onclick="lightboxOpen('<?= MdlCalendar::getUrlNew() ?>');" title="<?= Txt::trad("CALENDAR_addSharedCalendarTooltip") ?>">
				<div class="menuIcon"><img src="app/img/calendar/calendarAdd.png"></div>
				<div><?= Txt::trad("CALENDAR_addSharedCalendar") ?></div>
			</div>
			<?php } ?>

			<!--EVT PROPRIO-->
			<?php if(Ctrl::$curUser->isUser()){ ?>
			<div class="menuLine" onclick="lightboxOpen('?ctrl=calendar&action=MyEvents')" title="<?= Txt::trad("CALENDAR_evtAutorInfo") ?>">
				<div class="menuIcon"><img src="app/img/edit.png"></div>
				<div><?= Txt::trad("CALENDAR_evtAutor") ?></div>
			</div>
			<?php } ?>

			<!--IMPRIMER LA PAGE-->
			<?php if(Req::isMobile()==false){ ?>
			<div class="menuLine" onclick="calendarDisplay(true);print();" title="<?= Txt::trad("CALENDAR_printCalendarsInfos") ?>">
				<div class="menuIcon"><img src="app/img/print.png"></div>
				<div><?= Txt::trad("CALENDAR_printCalendars") ?></div>
			</div>
			<?php } ?>

			<!--CALENDRIER MOIS VIA LE DATEPICKER DE JQUERY-UI-->
			<?= ($displayMode!="month") ? "<div id='datepickerCalendar'></div>" : null ?>
		</div>
	</div>

	<div id="pageFullContent">

		<!--SYNTHESE DES AGENDAS -->
		<?php if(!empty($periodSynthese)){ ?>
			<div id="syntheseBlock" class="miscContainer noPrint">
				<div id="syntheseTable">
					<!--HEADER DE LA SYNTHESE-->
					<div id="syntheseLineHeader">
						<div class="vSyntheseLabel">&nbsp;</div>
						<?php foreach($periodSynthese as $tmpDay)  {echo "<div class=\"vSyntheseDay ".(date("Y-m-d",$tmpDay["timeBegin"])==date("Y-m-d")?"vSyntheseDayCurDay":null)."\">".(int)date("d",$tmpDay["timeBegin"])."</div>";} ?>
					</div>
					<!--AFFICHE CHAQUE AGENDA : LIBELLE & CHAQUE JOUR DE L'AGENDA-->
					<?php foreach($displayedCalendars as $tmpCal){ ?>
					<div class="vSyntheseLine">
						<div class="vSyntheseLabel" onclick="$('#calendarBlock<?= $tmpCal->_typeId ?>').scrollTo();"><?= $tmpCal->title ?></div>
						<?php
						foreach($periodSynthese as $tmpDay){
							//Tooltip des evts du jour
							$tmpEvtTooltip='<div class=\'vSyntheseDayEvtTooltip\'>'.$tmpCal->title.' <img src=\'app/img/arrowRight.png\'> '.Txt::dateLabel($tmpDay["timeBegin"],"dateFull");
							foreach($tmpDay["calsEvts"][$tmpCal->_id] as $tmpEvt)	{$tmpEvtTooltip.='<br>'.Txt::dateLabel($tmpEvt->dateBegin,"mini",$tmpEvt->dateEnd).' : '.Txt::reduce($tmpEvt->title,60);}
							$tmpEvtTooltip.='</div>';
							//Cellule des evts du jour
							$syntheseDayCalWE=$syntheseDayEvts=null;
							if(date("N",$tmpDay["timeBegin"])>5)	{$syntheseDayCalWE="vSyntheseDayCalWE";}
							foreach($tmpDay["calsEvts"][$tmpCal->_id] as $tmpEvt)	{$syntheseDayEvts.="<div class='vSyntheseDayEvt' onclick=\"".$tmpEvt->openVue()."\" style=\"background-color:".$tmpEvt->eventColor."\">&nbsp;</div>";}
							echo "<div class='vSyntheseDay vSyntheseDayCal ".$syntheseDayCalWE."'>
									<div class='vSyntheseDayEvts' title=\"".Txt::tooltip($tmpEvtTooltip)."\">".$syntheseDayEvts."</div>
								  </div>";
						}
						?>
					</div>
					<?php } ?>
				</div>
			</div>
		<?php } ?>

		<!--AFFICHE CHAQUE AGENDA-->
		<?php foreach($displayedCalendars as $tmpCal){ ?>
		<div class="vCalendarBlock miscContainer" id="calendarBlock<?= $tmpCal->_typeId ?>">
			<div class="vCalendarHeader">
				<!--TITRE DE L'AGENDA-->
				<div class="vCalendarTitle">
					<?php
					//Menu contextuel de l'agenda  &&  Label de l'agenda  &&  Icone de l'user (agenda perso)
					$launcherIcon=Req::isMobile()  ?  "inlineSmall" :  "inlineBig";
					$calendarLabel=Req::isMobile()  ?  Txt::reduce($tmpCal->title,20)  :  $tmpCal->title;
					$userIcon=($tmpCal->type=="user")  ?  Ctrl::getObj("user",$tmpCal->_idUser)->personImg(true,true)  :  null;
					$launcherLabel='<span title="'.Txt::tooltip($tmpCal->description).'">'.$calendarLabel.'</span>'.$userIcon;
					echo $tmpCal->contextMenu(["launcherIcon"=>$launcherIcon, "launcherLabel"=>$launcherLabel]);
					?>
				</div>
				<!--PERIODE AFFICHEE  &&  PRECEDENT/SUIVANT &&  AUJOURD'HUI-->
				<div class="vCalendarPeriod">
					<img src="app/img/navPrev.png" class="noPrint vCalendarPrev" onclick="redir('?ctrl=calendar&curTime=<?= $urlTimePrev ?>')" title="<?= Txt::trad("CALENDAR_periodPrevious") ?>">
					<span class="menuLaunch vCalendarPeriodLabel" for="calMonthPeriodMenu<?= $tmpCal->_typeId ?>"><?= ucfirst($calendarPeriodLabel) ?></span>
					<?php if(!empty($calMonthPeriodMenu))  {echo "<div class='menuContext' id='calMonthPeriodMenu".$tmpCal->_typeId."'><div id='calMonthPeriodMenuContainer'>".$calMonthPeriodMenu."</div></div>";} ?>
					<img src="app/img/navNext.png" class="noPrint vCalendarNext" onclick="redir('?ctrl=calendar&curTime=<?= $urlTimeNext ?>')" title="<?= Txt::trad("CALENDAR_periodNext") ?>">
				</div>
				<!--AFFICHAGE MONTH / WEEK / WORKWEEK / 4DAYS / DAY-->
				<div class="vCalendarDisplayMode noPrint">
					<button class="vCalendarDisplayToday" onclick="redir('?ctrl=calendar&curTime=<?= time() ?>')"><?= Txt::trad("today") ?></button>
					<button class="menuLaunch" for="menuDisplayMode<?= $tmpCal->_typeId ?>"><img src="app/img/calendar/display<?= ucfirst($displayMode) ?>.png"> <?= Txt::trad("CALENDAR_display_".$displayMode) ?> <img src="app/img/arrowBottom.png"></button>
					<div class="menuContext" id="menuDisplayMode<?= $tmpCal->_typeId ?>">
					<?php
					foreach(MdlCalendar::$displayModes as $displayModeTmp)
						{echo '<div class="menuLine '.($displayModeTmp==$displayMode?'linkSelect':null).'" onclick="redir(\'?ctrl=calendar&displayMode='.$displayModeTmp.'\')"><div class="menuIcon"><img src="app/img/calendar/display'.ucfirst($displayModeTmp).'.png"></div><div>'.ucfirst(Txt::trad("CALENDAR_display_".$displayModeTmp)).'</div></div>';}
					?>
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