<script>
////	INIT
$(function(){
	////	INITIALISE LA VUE DE CHAQUE AGENDA
	//Style des blocks d'événement
	$(".vCalEvtBlock").each(function(){ $(this).css("background",$(this).attr("data-catColor")); });
	//Synthese des agendas : Fixe la taille des cellule de jours
	if($("#syntheseTable").exist()){
		var syntheseDayWidth=Math.round(($("#syntheseLineHeader").width()-$("#syntheseLineHeader .vSyntheseLabel").width()) / $("#syntheseLineHeader .vSyntheseDay").length);
		$(".vSyntheseDay").css("width",syntheseDayWidth);
	}
	//Affiche chaque agenda (avec un Timeout : "availableContentHeight()" utilise le "#livecounterMain" qui est chargé via Ajax)
	setTimeout(function(){
		//Les agendas prennent toute la hauteur et largeur disponible
		var calendarHeight=(availableContentHeight() - parseInt($(".vCalendarBlock").css("margin-bottom")));
		var calendarWidth=$("#pageFullContent").width();
		$(".vCalendarBlock").outerHeight(calendarHeight).outerWidth(calendarWidth);
		$(".vCalendarVue").each(function(){
			var calObjId=$(this).attr("data-targetObjId");
			var calContentHeight=$("#blockCal"+calObjId).innerHeight() - $("#headerCal"+calObjId).outerHeight(true);
			$(this).css("height",calContentHeight+"px");
		});
		//Affichage de la vue week/month !
		calendarDimensions();
		//Ré-affiche les agendas après calcul
		$(".vCalendarBlock").css("visibility","visible");
	},100);

	////	INIT LE DATEPICKER JQUERY-UI DANS LE MENU DU MODULE
	$("#datepickerCalendar").datepicker({
		firstDay:1,
		showOtherMonths: true,
		selectOtherMonths: true,
		dateFormat:"yy-mm-dd",//utile pour le "onSelect"
		onSelect:function(curDate){
			var dateDate=new Date(curDate);
			redir("?ctrl=calendar&curTime="+Math.round(dateDate.valueOf()/1000));
		}
	});

	////	SWIPE GAUCHE/DROITE : BASCULE SUR LA PERIODE PRECEDENTE/SUIVANTE
	if(isMobile())
	{
		document.addEventListener("touchmove",function(event){
			//Détecte un swipe horizontal (amplitude verticale < 40px)
			if(typeof swipeBeginX!="undefined"  &&  Math.abs(swipeBeginY - event.touches[0].clientY) < 40){
				//Vérifie que le swipe part du centre de la page (25% à 75% de la largeur de page) et que le menu responsive n'est pas déjà affiché ("respMenuMain")
				if(swipeBeginX > ($(window).width()*0.25)  &&  swipeBeginX < ($(window).width()*0.75) && $("#respMenuMain").is(":visible")==false){
					//Swipe vers la droite/gauche d'au moins 50px : redirige vers la période précédente/suivante
					if((event.touches[0].clientX - swipeBeginX) > 50)		{var buttonSwipePeriod="#calendarPrev";}
					else if((swipeBeginX - event.touches[0].clientX) > 50)	{var buttonSwipePeriod="#calendarNext";}
					//Fait clignoter le bouton de changement de période puis simule le click avec un timeOut
					$(buttonSwipePeriod).effect("pulsate",{times:1},400);
					setTimeout(function(){ $(buttonSwipePeriod).trigger("click"); },500);
				}
			}
		});
	}
});
</script>


<style>
/*Footer & Menus du module*/
#pageFooterIcon img					{display:none;}
#calsList							{max-height:400px; overflow-y:auto;}
#calsList>div						{margin:4px;}
#calsList>div:first-child			{margin-bottom:10px;}
#calsList .menuLaunch				{display:none;}/*menu context des agendas*/
#calsList>div:hover .menuLaunch		{display:inline; margin-left:5px;}/*idem*/
#calsList button					{display:none; width:120px; margin:5px 0px 0px 30px;}
#adminDisplayAllCals				{float:right;}
.ui-datepicker						{width:97%; border:0px;}
#menuCategory>div					{margin-bottom:10px;}
#categoryColorAll					{border:solid #ccc 2px;}
#datepickerCalendar					{margin-top:20px; margin-bottom:10px;}

/*Synthese des agendas*/
#syntheseBlock.objContainer			{padding-right:0px;}/*surcharge*/
#syntheseTable						{display:table; width:100%;}
#syntheseLineHeader, .vSyntheseLine, .vSyntheseLineFooter	{display:table-row;}
.vSyntheseDayCurDay					{color:#c00;}
.vSyntheseLabel						{display:table-cell; width:160px; padding:2px; padding-left:5px; vertical-align:middle;}
.vSyntheseLine:hover .vSyntheseLabel{color:#c00;}
.vSyntheseLineFooter .vSyntheseLabel{font-style:italic;}
.vSyntheseDay						{display:table-cell; vertical-align:middle; text-align:center; height:22px;}
.vSyntheseDayEvts					{display:table; width:100%; height:100%;}
.vSyntheseDayEvt					{display:table-cell; border-left:transparent;}
.vSyntheseDayEvts:hover				{opacity:0.5;}
.vSyntheseLineFooter .vSyntheseDayEvt {cursor:help;}
.vSyntheseDayEvtTooltip				{text-align:left;}
.vSyntheseDayEvtTooltip	ul			{margin:0px; margin-top:5px; padding-left:10px;}
.vSyntheseDayCal					{background:#ddd; border:dotted 1px #eee;}
.vSyntheseDayCal.vSyntheseDayCalWE	{background:#ccc;}

/*Agendas*/
.vCalendarBlock						{margin-top:25px; padding:0px; min-height:300px; visibility:hidden;}
.vCalendarBlock:first-child			{margin-top:0px;}
.vCalendarHeader					{display:table; width:100%;}
.vCalendarHeader>div				{display:table-cell; width:33%; padding:12px; vertical-align:middle;}
.vCalendarDisplayMode				{text-align:right;}
.vCalendarDisplayMode>span			{margin-left:15px;}
.vCalendarDisplayModeLabel			{margin-left:5px;}
.vCalendarPeriod					{text-align:center;}
.vCalendarTitle						{text-transform:uppercase;}
.vCalendarTitleLabel				{margin-left:10px; margin-right:5px;}
#calendarPrev,#calendarNext			{margin:0px 10px 0px 10px;}
[id^=calMonthPeriodMenu]			{width:250px; overflow:visible;}
#calMonthPeriodMenuContainer a		{display:inline-block; width:75px; padding:3px; text-align:left;}

/*Evenements*/
.vCalEvtBlock.objContainer			{height:20px; min-height:20px; padding:4px!important; margin:0px; margin-bottom:1px; box-shadow:1px 1px 2px #555; cursor:pointer;}/*surcharge .objContainer : "height", "padding", etc*/
.vCalEvtBlock .objMenuBurgerInline	{float:right; margin-left:4px; margin-bottom:4px;}/*Surchage du menu "burger" de chaque événement (que l'on affiche pas en responsive)*/
.vCalEvtBlock .vCalEvtLabel			{height:98%; overflow:hidden; font-size:0.9em; font-weight:normal; color:#fff;}/*overflow pour ne pas dépasser du block parent*/
.vCalEvtBlock .vCalEvtLabel img		{max-height:13px;}

/*RESPONSIVE*/
@media screen and (max-width:1023px){
	.vCalendarTitle .objMenuBurger		{width:15px; height:15px;}/*Icone "burger" de chaque agenda*/
	.vCalendarTitleLabel				{margin-left:5px; margin-right:0px;}
	.vCalendarTitle .personImgSmall		{display:none;}/*cf. "getImg()"*/
	#calendarPrev,#calendarNext			{margin:0px 5px 0px 5px;}
	.vCalendarDisplayMode>span			{margin-left:5px;}
	#adminDisplayAllCals, .vCalendarDisplayModeLabel, .vCalendarDisplayToday  {display:none!important;}
	.vCalendarHeader>div				{width:auto; padding:12px 3px 12px 3px; white-space:nowrap!important; text-transform:lowercase;}
	.vCalEvtBlock						{padding:2px!important;}
}

/* IMPRESSION */
@media print{
	@page {size:landscape;}
	#syntheseBlock, .vCalendarDisplayModeLabel	{display:none!important;}/*affiche pas la synthese des agendas, ni les menus de chaque agendas*/
	.vCalendarPeriod							{text-align:right;}
	.vCalendarBlock								{page-break-after:always; margin:0px; box-shadow:none;}/*saut de page, sauf pour le dernier de la liste*/
	.vCalendarBlock:last-child					{page-break-after:avoid;}
	.vCalEvtBlock								{background:none;}
	.objMenuBurger, .objMenuBurgerInline		{display:none;}/*Bouton 'burger' de chaque événement*/
	.vCalEvtLabel								{color:#333;}
}
</style>


<div id="pageFull">
	<div id="pageModuleMenu">
		<div id="pageModMenu" class="miscContainer">
			<?php
			////	PROPOSITIONS D'EVENEMENT?
			echo CtrlCalendar::menuProposedEvents();

			////	AGENDAS VISIBLES
			if(!empty($visibleCalendars))
			{
				echo "<form action='index.php' method='get' id='calsList' class='noConfirmClose'>";
					//Label du menu & liste des agendas
					echo "<div>".Txt::trad("CALENDAR_calsList")." :</div>";
					foreach($visibleCalendars as $tmpCal){
						echo "<div>
								<input type='checkbox' name='displayedCalendars[]' value='".$tmpCal->_id."' id=\"displayedCal".$tmpCal->_targetObjId."\" onchange=\"$('#calsList button').fadeIn();\" ".(CtrlCalendar::isDisplayedCal($displayedCalendars,$tmpCal)?"checked":null).">
								<label for=\"displayedCal".$tmpCal->_targetObjId."\" title=\"".$tmpCal->description."\" class='noTooltip'>".$tmpCal->title."</label> ".(Req::isMobile()==false?$tmpCal->contextMenu(["iconBurger"=>"small"]):null)."
							 </div>";
					}
					//"Afficher tous les agendas" (Admin général uniquement) && Bouton "Afficher" la sélection
					if(Ctrl::$curUser->isAdminGeneral())  {echo ($_SESSION["displayAllCals"]==false)  ?  "<a id='adminDisplayAllCals' onclick=\"redir('?ctrl=calendar&displayAllCals=1')\" title=\"".Txt::trad("CALENDAR_displayAllCals")."\"><img src='app/img/plusSmall.png'></a>"  :  null;}
					echo Txt::submitButton("show",false);
				echo "</form><hr>";
			}

			////	AJOUTER UN EVT (uniquement si ya qu'un agenda affiché)
			if(count($displayedCalendars)==1 && $displayedCalendars[0]->addProposeEvtRight())
				{echo "<div class='menuLine sLink' onclick=\"lightboxOpen('".MdlCalendarEvent::getUrlNew()."&_idCal=".$displayedCalendars[0]->_id."')\" title=\"".$displayedCalendars[0]->title." : ".$displayedCalendars[0]->addEventLabel."\"><div class='menuIcon'><img src='app/img/plus.png'></div><div>".Txt::trad("CALENDAR_addEvt")."</div></div>";}

			////	CATEGORIES D'EVT (FILTRE)
			//Catégorie courante && menu des catégores disponible pour filtrer les résultats && Menu d'édition des catégories
			$curCatLabel=Req::isParam("_idCatFilter")  ?  "<span class='sLinkSelect'>".Ctrl::getObj("calendarEventCategory",Req::getParam("_idCatFilter"))->display()."</span>"  :  Txt::trad("allCategory");
			$menuCategory="<div>".Txt::trad("CALENDAR_filterByCategory")." :</div>"
						 ."<div><a href='?ctrl=calendar' ".(Req::isParam("_idCatFilter")?null:"class='sLinkSelect'")."> &nbsp; <div id='categoryColorAll' class='categoryColor'>&nbsp;</div> ".Txt::trad("allCategory")."</a></div>";
			foreach(MdlCalendarEventCategory::getCategories() as $tmpCategory)  {$menuCategory.="<div><a href=\"?ctrl=calendar&_idCatFilter=".$tmpCategory->_id."\" ".(Req::getParam("_idCatFilter")==$tmpCategory->_id?'class="sLinkSelect"':null)." title=\"".$tmpCategory->description."\"> &nbsp; ".$tmpCategory->display()."</a></div>";}
			if(MdlCalendarEventCategory::addRight())  {$menuCategory.="<hr><div><a onclick=\"lightboxOpen('?ctrl=calendar&action=CalendarEventCategoryEdit');\" id='categoryEdit'><img src='app/img/edit.png'> ".Txt::trad("CALENDAR_editCategories")."</a></div>";}
			//Affiche le menu
			echo "<div class='menuLine sLink'>
					<div class='menuIcon'><img src='app/img/category.png'></div>
					<div>
						<div class='menuLaunch' for='menuCategory'>".$curCatLabel."</div>
						<div id='menuCategory' class='menuContext'>".$menuCategory."</div>
					</div>
				</div>";
			?>

			<!--AJOUTER AGENDA PARTAGE-->
			<?php if(MdlCalendar::addRight()){ ?>
			<div class="menuLine sLink" onclick="lightboxOpen('<?= MdlCalendar::getUrlNew() ?>');" title="<?= Txt::trad("CALENDAR_addSharedCalendarInfo") ?>">
				<div class="menuIcon"><img src="app/img/calendar/calendarAdd.png"></div>
				<div><?= Txt::trad("CALENDAR_addSharedCalendar") ?></div>
			</div>
			<?php } ?>

			<!--EVT PROPRIO-->
			<?php if(Ctrl::$curUser->isUser()){ ?>
			<div class="menuLine sLink" onclick="lightboxOpen('?ctrl=calendar&action=MyEvents')">
				<div class="menuIcon"><img src="app/img/calendar/myEvents.png"></div>
				<div><?= Txt::trad("CALENDAR_evtAutor") ?></div>
			</div>
			<?php } ?>

			<!--IMPRIMER LA PAGE-->
			<?php if(Req::isMobile()==false){ ?>
			<div class="menuLine sLink" onclick="calendarDimensions(true);print();" title="<?= Txt::trad("CALENDAR_printCalendarsInfos") ?>">
				<div class="menuIcon"><img src="app/img/print.png"></div>
				<div><?= Txt::trad("CALENDAR_printCalendars") ?></div>
			</div>
			<?php } ?>

			<!--CALENDRIER MOIS?-->
			<?php if($displayMode!="month")  {echo "<div id='datepickerCalendar'></div>";} ?>
		</div>
	</div>

	<div id="pageFullContent">
		<!--SYNTHESE DES AGENDAS ?-->
		<?php if(!empty($periodDaysSynthese)){ ?>
			<div id="syntheseBlock" class="miscContainer">
				<div id="syntheseTable">
					<!--HEADER DE LA SYNTHESE-->
					<div id="syntheseLineHeader">
						<div class="vSyntheseLabel">&nbsp;</div>
						<?php foreach($periodDaysSynthese as $tmpDay)  {echo "<div class=\"vSyntheseDay ".(date("Y-m-d",$tmpDay["timeBegin"])==date("Y-m-d")?"vSyntheseDayCurDay":null)."\">".(int)date("d",$tmpDay["timeBegin"])."</div>";} ?>
					</div>
					<!--AFFICHE CHAQUE AGENDA-->
					<?php foreach($displayedCalendars as $tmpCal){ ?>
					<div class="vSyntheseLine">
						<div class="vSyntheseLabel sLink" onclick="toScroll('#blockCal<?= $tmpCal->_targetObjId ?>')"><?= $tmpCal->title ?></div>
						<!--CELLULES DE CHAQUE JOUR DE L'AGENDA-->
						<?php
						foreach($periodDaysSynthese as $tmpDay)
						{
							//Tooltip des evts du jour
							$tmpEvtTooltip="<div class='vSyntheseDayEvtTooltip'>".$tmpCal->title." : ".Txt::displayDate($tmpDay["timeBegin"],"dateFull")."<ul>";
							foreach($tmpDay["calsEvts"][$tmpCal->_id] as $tmpEvt)	{$tmpEvtTooltip.="<li>".Txt::displayDate($tmpEvt->dateBegin,"mini",$tmpEvt->dateEnd)." : ".Txt::reduce($tmpEvt->title,60)."</li>";}
							$tmpEvtTooltip.="</ul></div>";
							//Cellule des evts du jour
							$syntheseDayCalWE=$syntheseDayEvts=null;
							if(date("N",$tmpDay["timeBegin"])>5)	{$syntheseDayCalWE="vSyntheseDayCalWE";}
							foreach($tmpDay["calsEvts"][$tmpCal->_id] as $tmpEvt)	{$syntheseDayEvts.="<div class='vSyntheseDayEvt sLink' onclick=\"lightboxOpen('".$tmpEvt->getUrl("vue")."')\" style=\"background-color:".$tmpEvt->catColor."\">&nbsp;</div>";}
							echo "<div class='vSyntheseDay vSyntheseDayCal ".$syntheseDayCalWE."'>
									<div class='vSyntheseDayEvts' title=\"".$tmpEvtTooltip."\">".$syntheseDayEvts."</div>
								  </div>";
						}
						?>
					</div>
					<?php } ?>
					<!--LIGNE DE SYNTHESE DES AGENDAS-->
					<div class="vSyntheseLineFooter">
						<div class="vSyntheseLabel"><?= Txt::trad("CALENDAR_synthese") ?></div>
						<?php foreach($periodDaysSynthese as $tmpDay){ ?>
						<div class="vSyntheseDay vSyntheseDayCal <?= date("N",$tmpDay["timeBegin"])>5?"vSyntheseDayCalWE":null ?>">
							<div class="vSyntheseDayEvts">
								<?php if(!empty($tmpDay["nbCalsOccuppied"])){ ?><div class="vSyntheseDayEvt" style="background-color:#777" title="<div class='vSyntheseDayEvtTooltip'><?= "".$tmpDay["nbCalsOccuppied"]."</div>" ?>">&nbsp;</div><?php } ?>	
							</div>
						</div>
						<?php } ?>
					</div>
				</div>
			</div>
		<?php } ?>

		
		<!--AFFICHE CHAQUE AGENDA-->
		<?php foreach($displayedCalendars as $tmpCal){ ?>
		<div class="vCalendarBlock miscContainer" id="blockCal<?= $tmpCal->_targetObjId ?>">
			<div class="vCalendarHeader" id="headerCal<?= $tmpCal->_targetObjId ?>">
				<!--TITRE DE L'AGENDA-->
				<div class="vCalendarTitle">
					<?php
					//Menu contextuel de l'agenda (cf. ".objMenuBurger")  &&  Label de l'agenda  &&  Icone de l'user (agenda perso)
					$tmpCalLabel=(Req::isMobile())  ?  Txt::reduce($tmpCal->title,25)  :  $tmpCal->title;
					$tmpCalIcon=($tmpCal->type=="user")  ?  Ctrl::getObj("user",$tmpCal->_idUser)->getImg(true,true)  :  null;
					echo $tmpCal->contextMenu(["iconBurger"=>"big"])."<span class='vCalendarTitleLabel' title=\"".Txt::formatTooltip($tmpCal->description)."\">".$tmpCalLabel."</span>".$tmpCalIcon;
					?>
				</div>
				<!--PERIODE AFFICHEE-->
				<div class="vCalendarPeriod">
					<img src="app/img/navPrev.png" id="calendarPrev" class="sLink noPrint" onclick="redir('?ctrl=calendar&curTime=<?= $urlTimePrev.$urlCatFilter ?>')" title="<?= Txt::trad("CALENDAR_periodPrevious") ?>">
					<span id="calendarLabelMonth" for="calMonthPeriodMenu<?= $tmpCal->_targetObjId ?>" class="menuLaunch"><?= ucfirst($labelMonth) ?></span>
					<?php if(!empty($calMonthPeriodMenu))  {echo "<div class='menuContext' id='calMonthPeriodMenu".$tmpCal->_targetObjId."'><div id='calMonthPeriodMenuContainer'>".$calMonthPeriodMenu."</div></div>";} ?>
					<img src="app/img/navNext.png"  id="calendarNext" class="sLink noPrint" onclick="redir('?ctrl=calendar&curTime=<?= $urlTimeNext.$urlCatFilter ?>')" title="<?= Txt::trad("CALENDAR_periodNext") ?>">
				</div>
				<!--MODE D'AFFICHAGE (mois, semaine, etc)-->
				<div class="vCalendarDisplayMode">
					<span class="vCalendarDisplayToday sLink" onclick="redir('?ctrl=calendar&curTime=<?= time() ?>')"><img src="app/img/calendar/displayToday.gif"><span class="vCalendarDisplayModeLabel"><?= Txt::trad("displayToday") ?></span></span>
					<span for="menuDisplayMode<?= $tmpCal->_targetObjId ?>" class="menuLaunch"><img src="app/img/calendar/display<?= ucfirst($displayMode) ?>.gif"><span class="vCalendarDisplayModeLabel"><?= Txt::trad("CALENDAR_display".ucfirst($displayMode)) ?></span>&nbsp;<img src="app/img/arrowBottom.png"></span>
					<div class="menuContext" id="menuDisplayMode<?= $tmpCal->_targetObjId ?>">
						<?php foreach(["month","week","workWeek","4Days","day"] as $displayModeTmp)  {echo "<div class='menuLine ".($displayModeTmp==$displayMode?"sLinkSelect":"sLink")."' onclick=\"redir('?ctrl=calendar&displayMode=".$displayModeTmp."')\"><div class='menuIcon'><img src='app/img/calendar/display".ucfirst($displayModeTmp).".gif'></div><div>".Txt::trad("CALENDAR_display".ucfirst($displayModeTmp))."</div></div>";} ?>
					</div>
				</div>
			</div>
			<!--CONTENU DU CALENDRIER ("VueCalendarMonth"/"VueCalendarWeek")-->
			<div class="vCalendarVue" data-targetObjId="<?= $tmpCal->_targetObjId ?>"><?= $tmpCal->calendarVue ?></div>
		</div>
		<?php } ?>

		
		<!--AUCUN AGENDA-->
		<?php if(empty($displayedCalendars))  {echo "<div class='emptyContainer'>".Txt::trad("CALENDAR_noCalendarDisplayed")."</div>";} ?>
	</div>
</div>