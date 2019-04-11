<script>
//Init
$(function(){
	////	INIT LE DATEPICKER JQUERY-UI
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

	////	INITIALISE LA VUE DES AGENDAS
	//Style des blocks d'événement
	$(".vCalEvtBlock").each(function(){ $(this).css("background",$(this).attr("data-catColor")); });
	//Synthese des agendas : Fixe la taille des cellule de jours
	if($("#syntheseTable").exist()){
		var syntheseDayWidth=Math.round(($("#syntheseLineHeader").width()-$("#syntheseLineHeader .vSyntheseLabel").width()) / $("#syntheseLineHeader .vSyntheseDay").length);
		$(".vSyntheseDay").css("width",syntheseDayWidth);
	}
	//Calcul l'affichage de chaque agenda
	setTimeout(function(){//Timeout car pour récupérer "availableContentHeight()" il faut d'abord que le "#livecounterMain" soit chargé via Ajax..
		//Les agendas prennent toute la hauteur et largeur disponible
		var calendarHeight=(availableContentHeight() - parseInt($(".vCalendarBlock").css("margin-bottom")));
		var calendarWidth=$(".pageFullContent").width();
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
});
</script>


<style>
/*Surcharge du Footer*/
#pageFooterIcon img	{display:none;}

/*Menus de gauche de la page*/
#calsList					{max-height:400px; overflow-y:auto; margin-top:5px;}
#calsList>div				{margin:4px;}
#calsList .menuLaunch			{display:none;}/*menu context des agendas*/
#calsList>div:hover .menuLaunch	{display:inline;}/*idem*/
#calsListForm button		{display:none; width:120px; margin:5px 0px 0px 30px;}
#adminDisplayAllCals		{float:right;}
.ui-datepicker				{width:97%; border:0px;}
#menuCategory label			{display:block; margin-top:15px; margin-bottom:10px;}
#menuCategory a				{display:block; margin-bottom:10px;}
#categoryColorAll			{border:solid #000 1px;}

/*Synthese des agendas*/
#syntheseBlock.objContainer	{padding-right:0px;}/*surcharge*/
#syntheseTable				{display:table; width:100%;}
#syntheseLineHeader, .vSyntheseLine, .vSyntheseLineFooter	{display:table-row;}
.vSyntheseDayCurDay			{color:#c00;}
.vSyntheseLabel				{display:table-cell; width:160px; padding:2px; padding-left:5px; vertical-align:middle;}
.vSyntheseLine:hover .vSyntheseLabel	{color:#c00;}
.vSyntheseLineFooter .vSyntheseLabel	{font-style:italic;}
.vSyntheseDay				{display:table-cell; vertical-align:middle; text-align:center; height:22px;}
.vSyntheseDayEvts			{display:table; width:100%; height:100%;}
.vSyntheseDayEvt			{display:table-cell; border-left:transparent;}
.vSyntheseDayEvts:hover		{opacity:0.5;}
.vSyntheseLineFooter .vSyntheseDayEvt {cursor:help;}
.vSyntheseDayEvtTooltip		{text-align:left;}
.vSyntheseDayEvtTooltip	ul	{margin:0px; margin-top:5px; padding-left:10px;}
.vSyntheseDayCal			{background:#ddd; border:dotted 1px #eee;}
.vSyntheseDayCal.vSyntheseDayCalWE	{background:#ccc;}

/*Agendas*/
.vCalendarBlock				{margin-top:25px; padding:0px; min-height:300px; visibility:hidden;}
.vCalendarBlock:first-child	{margin-top:0px;}
.vCalendarHeader			{display:table; width:100%;}
.vCalendarHeader>div		{display:table-cell; width:33%; padding:8px; vertical-align:middle;}
.vCalendarTitle				{text-transform:uppercase;}
.vCalendarTitle .personImgSmall	{width:30px; height:30px; margin-right:5px;}
.vCalendarPeriod			{text-align:center;}
.vCalendarDisplayMode		{text-align:right;}
.vCalendarPrevNext			{margin:0px 4px 0px 4px;}
[id^=calMonthPeriodMenu]	{width:250px; overflow:visible;}
#calMonthPeriodMenu2 a		{display:inline-block; width:70px; padding:2px; text-align:left;}

/*Evenements*/
.vCalEvtBlock				{padding:4px!important; margin:0px; box-shadow:1px 1px 2px #555; cursor:pointer;}/*"padding!important" car surcharge .objContainer. "box-shadow" idem au mode responsive. "line-height" ici car via ".vCalEvtLabel" il n'est pas pris en compte à cause du "display:inline" : wtf!*/
.vCalEvtBlock img[src*='menuSmall']	{float:right; margin:0px 0px 4px 4px;}
.vCalEvtLabel				{display:block; height:98%; overflow:hidden; line-height:12px; font-weight:normal; font-size:0.9em; color:#fff;}/*pas dépasser du block parent*/
.vCalEvtLabel:hover			{opacity:0.9;}
.vCalEvtLabel img			{max-height:13px;}

/*RESPONSIVE*/
@media screen and (max-width:1023px){
	#adminDisplayAllCals, .vCalEvtLabel img, .vCalendarDisplayModeLabel		{display:none!important;}
	.vCalendarHeader>div			{width:auto; padding-left:3px; padding-right:3px; white-space:nowrap!important; text-transform:lowercase;}
	.vCalendarHeader>div:last-child	{width:50px;}
	.vCalendarPrevNext				{width:16px!important;}
	.vCalEvtLabel					{text-transform:lowercase; font-size:0.85em!important;}
}

/* IMPRESSION */
@media print{
	@page {size:landscape;}
	#syntheseBlock, .vCalendarDisplayMode	{display:none!important;}/*affiche pas la synthese des agendas, ni les menus de chaque agendas*/
	.vCalendarPeriod			{text-align:right;}
	.vCalendarBlock			 	{page-break-after:always; margin:0px; box-shadow:none;}/*saut de page, sauf pour le dernier de la liste*/
	.vCalendarBlock:last-child	{page-break-after:avoid;}
	.vCalEvtBlock				{background:none;}
	.vCalEvtLabel				{color:#333;}
}
</style>


<div class="pageFull">
	<div class="pageModMenuContainer">
		<div id="pageModMenu" class="miscContainer">
			<?php
			////	PROPOSITIONS D'EVENEMENT?
			echo CtrlCalendar::menuProposedEvents();

			////	AGENDAS VISIBLES
			if(!empty($visibleCalendars))
			{
				echo "<form action='index.php' method='get' id='calsListForm' class='noConfirmClose'>";
					//Label du menu
					echo "<div>".Txt::trad("CALENDAR_calsList")." :</div>";
					//liste des agendas
					echo "<div id='calsList'>";
						foreach($visibleCalendars as $tmpCal){
							echo "<div>
									<input type='checkbox' name='displayedCalendars[]' value='".$tmpCal->_id."' id=\"displayedCal".$tmpCal->_targetObjId."\" onchange=\"$('#calsListForm button').fadeIn();\" ".(CtrlCalendar::isDisplayedCal($displayedCalendars,$tmpCal)?"checked":null).">
									<label for=\"displayedCal".$tmpCal->_targetObjId."\" title=\"".$tmpCal->description."\" class='noTooltip'>".$tmpCal->title."</label> ".(Req::isMobile()==false?$tmpCal->contextMenu(["inlineLauncher"=>true]):null)."
								 </div>";
						}
					echo "</div>";
					//Afficher tous les agendas (Admin général uniquement)
					if(Ctrl::$curUser->isAdminGeneral())  {echo ($_SESSION["displayAllCals"]==false)  ?  "<a id='adminDisplayAllCals' onclick=\"redir('?ctrl=calendar&displayAllCals=1')\" title=\"".Txt::trad("CALENDAR_displayAllCals")."\"><img src='app/img/plusSmall.png'></a>"  :  "<a id='adminDisplayAllCals' onclick=\"redir('?ctrl=calendar&displayAllCals=0')\"><img src='app/img/plusMinus.png'></a>";}
					//Bonton "Afficher (".formMainButton")
					echo Txt::submit("show",false);
				echo "</form><hr>";
			}

			////	CATEGORIES D'EVT (FILTRE)
			//Init les options
			$curCatLabel=Req::isParam("_idCatFilter")  ?  "<span class='sLinkSelect'>".Ctrl::getObj("calendarEventCategory",Req::getParam("_idCatFilter"))->display()."</span>"  :  Txt::trad("anyCategory");
			$catOptions=MdlCalendarEventCategory::addRight()  ?  "<a onclick=\"lightboxOpen('?ctrl=calendar&action=CalendarEventCategoryEdit');\" id='categoryEdit'><img src='app/img/edit.png'> ".Txt::trad("CALENDAR_editCategories")."</a><hr>"  :  null;
			$catOptions.="<label>".Txt::trad("CALENDAR_filterByCategory")." :</label><a href='?ctrl=calendar' ".(Req::isParam("_idCatFilter")?null:"class='sLinkSelect'")."><div id='categoryColorAll' class='categoryColor'>&nbsp;</div> ".Txt::trad("anyCategory")."</a>";
			foreach(MdlCalendarEventCategory::getCategories() as $tmpCategory)  {$catOptions.="<a href=\"?ctrl=calendar&_idCatFilter=".$tmpCategory->_id."\" ".(Req::getParam("_idCatFilter")==$tmpCategory->_id?'class="sLinkSelect"':null)." title=\"".$tmpCategory->description."\">".$tmpCategory->display()."</a>";}
			//Affiche le menu
			echo "<div class='menuLine sLink'>
					<div class='menuIcon'><img src='app/img/category.png'></div>
					<div>
						<div class='menuLaunch' for='menuCategory'>".$curCatLabel."</div>
						<div id='menuCategory' class='menuContext'>".$catOptions."</div>
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
			<?php if($displayMode!="month")  {echo "<hr><div id='datepickerCalendar'></div>";} ?>
		</div>
	</div>

	<div class="pageFullContent">
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
			<!--HEADER DU CALENDRIER-->
			<div class="vCalendarHeader" id="headerCal<?= $tmpCal->_targetObjId ?>">
				<div class="vCalendarTitle">
					<?php
					if($tmpCal->type=="user" && Req::isMobile()==false)	{echo Ctrl::getObj("user",$tmpCal->_idUser)->getImg(true,true);}//Icone de l'user (si agenda perso)
					echo "<span title=\"".Txt::formatTooltip($tmpCal->description)."\">".(Req::isMobile()?Txt::reduce($tmpCal->title,20):$tmpCal->title)."</span> &nbsp;".$tmpCal->contextMenu(["inlineLauncher"=>true]);//Titre & menu context de l'agenda
					?>
				</div>
				<div class="vCalendarPeriod">
					<img src="app/img/navPrevious.png" class="vCalendarPrevNext sLink noPrint" onclick="redir('?ctrl=calendar&curTime=<?= $timePrev ?>')" title="<?= Txt::trad("CALENDAR_periodPrevious") ?>">
					<span for="calMonthPeriodMenu<?= $tmpCal->_targetObjId ?>" class="menuLaunch"><?= $labelPeriod ?></span>
					<?php if(!empty($calMonthPeriodMenu))  {echo "<div class='menuContext' id='calMonthPeriodMenu".$tmpCal->_targetObjId."'><div id='calMonthPeriodMenu2'>".$calMonthPeriodMenu."</div></div>";} ?>
					<img src="app/img/navNext.png" class="vCalendarPrevNext sLink noPrint" onclick="redir('?ctrl=calendar&curTime=<?= $timeNext ?>')" title="<?= Txt::trad("CALENDAR_periodNext") ?>">
				</div>
				<div class="vCalendarDisplayMode">
					<span for="menuDisplayMode<?= $tmpCal->_targetObjId ?>" class="menuLaunch"><img src="app/img/calendar/display<?= ucfirst($displayMode) ?>.gif"> <span class="vCalendarDisplayModeLabel"><?= Txt::trad("CALENDAR_display".ucfirst($displayMode)) ?></span> <img src="app/img/arrowBottom.png"></span>
					<div class="menuContext" id="menuDisplayMode<?= $tmpCal->_targetObjId ?>">
						<?php
						//Affiche chaque mode d'affichage
						foreach(["month","week","workWeek","3Days","day"] as $displayModeTmp)
							{echo "<div class='menuLine ".($displayModeTmp==$displayMode?"sLinkSelect":"sLink")."' onclick=\"redir('?ctrl=calendar&displayMode=".$displayModeTmp."')\"><div class='menuIcon'><img src='app/img/calendar/display".ucfirst($displayModeTmp).".gif'></div><div>".Txt::trad("CALENDAR_display".ucfirst($displayModeTmp))."</div></div>";}
						?>
						<hr>
						<div class="menuLine sLink" onclick="redir('?ctrl=calendar&curTime=<?= time() ?>')"><div class="menuIcon"><img src="app/img/calendar/displayToday.gif"></div><div><?= Txt::trad("displayToday") ?></div></div>
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