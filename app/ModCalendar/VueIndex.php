<script>
////	INIT
$(function(){
	/*******************************************************************************************
	 *	INITIALISE LA VUE DE CHAQUE AGENDA
	 *******************************************************************************************/
	//Style des blocks d'événement
	$(".vCalEvtBlock").each(function(){ $(this).css("background",$(this).attr("data-catColor")); });
	//Synthese des agendas : Fixe la taille des cellule de jours
	if($("#syntheseTable").exist()){
		var syntheseDayWidth=Math.round(($("#syntheseLineHeader").width()-$("#syntheseLineHeader .vSyntheseLabel").width()) / $("#syntheseLineHeader .vSyntheseDay").length);
		$(".vSyntheseDay").css("width",syntheseDayWidth);
	}

	/*******************************************************************************************
	 *	PUIS AFFICHE CHAQUE AGENDA (Timeout car "availableContentHeight()" prend en compte le "#livecounterMain" chargé via Ajax)
	 *******************************************************************************************/
	setTimeout(function(){
		//Les agendas prennent toute la hauteur et largeur disponible
		var calendarHeight=(availableContentHeight() - parseInt($(".vCalendarBlock").css("margin-bottom")));
		var calendarWidth=$("#pageFullContent").width();
		$(".vCalendarBlock").outerHeight(calendarHeight).outerWidth(calendarWidth);
		$(".vCalendarVue").each(function(){
			var calObjId=$(this).attr("data-typeId");
			var calContentHeight=$("#blockCal"+calObjId).innerHeight() - $("#headerCal"+calObjId).outerHeight(true);
			$(this).css("height",calContentHeight+"px");
		});
		//Affichage de la vue week/month !
		calendarDimensions();
		//Ré-affiche les agendas après calcul
		$(".vCalendarBlock").css("visibility","visible");
	},350);//Timeout de 350ms (cf. "mainPageDisplay()" et le "margin-bottom" du "#pageFull" en fonction du livecounter)

	/*******************************************************************************************
	 *	INIT LE DATEPICKER JQUERY-UI DANS LE MENU DU MODULE
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
	 *	SWIPE GAUCHE/DROITE : AFFICHE LA PERIODE PRECEDENTE/SUIVANTE
	 *******************************************************************************************/
	if(isMobile())
	{
		document.addEventListener("touchmove",function(event){
			if(Math.abs(swipePosY - event.touches[0].clientY) < 40  &&  $("#menuMobileMain").isVisible()==false){									//Swipe avec 40px max d'amplitude verticale  &&  "menuMobileMain" masqué
				if((event.touches[0].clientX - swipePosX) > 100  &&  $(window).width()-swipePosX > swipeWidth)	{var buttonPeriod="#calendarPrev";}	//Swipe de 100px à gauche : affiche la période précédente (150px du bord de page minimum : pour pas interférer avec "menuMobileDisplay()" > cf. "swipeWidth")
				else if((swipePosX - event.touches[0].clientX) > 100)											{var buttonPeriod="#calendarNext";}	//Swipe de 100px à droite : affiche la période suivante
				$(buttonPeriod).effect("pulsate",{times:1},1000);																					//Fait clignoter le bouton de changement de période 
				setTimeout(function(){ $(buttonPeriod).trigger("click"); },300);																	//Puis trigger "Click" sur ce bouton, avec un timeOut
			}
		});
	}
});
</script>


<style>
/*Footer & Menus du module*/
#pageFooterIcon img					{display:none;}
#calsList							{max-height:500px; overflow-y:auto; padding:5px;}
#calsListLabel 						{margin-bottom:10px;}
#calsList .calsListCalendar			{line-height:25px;}/*Label de chaque agenda*/
#calsList .menuLaunch				{display:none;}/*menu context de chaque agenda*/
#calsList>div:hover .menuLaunch		{display:inline; margin-left:5px;}/*idem*/
#calsList .submitButtonInline		{display:none; margin-top:15px;}/*bouton d'affichage des agendas*/
#calsListDisplayAll					{text-align:right;}
.ui-datepicker						{width:97%; border:0px;}
#menuCategory>div					{margin:10px 0px;}
#menuCategory>div .linkSelect		{font-style:italic;}
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
.vCalendarTitle, .vCalendarPeriodLabel  {font-size:1.1em;}
.vCalendarTitleLabel				{margin-left:10px;}
.vCalendarPeriod					{text-align:center;}
#calendarPrev,#calendarNext			{margin:0px 10px 0px 10px;}
[id^=calMonthPeriodMenu]			{width:250px; overflow:visible;}
#calMonthPeriodMenuContainer a		{display:inline-block; width:75px; padding:3px; text-align:left;}

/*Evenements*/
.vCalEvtBlock.objContainer			{height:22px; min-height:22px; padding:4px; margin:0px; margin-bottom:1px; box-shadow:1px 1px 2px #555; cursor:pointer; border-radius:3px;}/*surcharge .objContainer : "height", "padding", etc*/
.vCalEvtBlock .objMenuBurger		{position:relative; float:right; top:0px; right:0px; margin:0px;}/*Surchage du menu "burger" de chaque événement*/
.vCalEvtBlock .vCalEvtLabel			{height:98%; overflow:hidden; font-size:0.9em; font-weight:normal; color:#fff;}/*overflow pour ne pas dépasser du block parent*/
.vCalEvtBlock .vCalEvtLabel img		{max-height:13px;}

/*MOBILE*/
@media screen and (max-width:1023px){
	.vCalendarTitle, .vCalendarPeriodLabel 				{font-size:1em;}
	.vCalendarTitleLabel								{margin-left:5px; margin-right:0px;}
	.vCalendarTitle .personImgSmall						{display:none;}/*cf. "getImg()"*/
	.objMenuBurger, .objMenuBurgerInline				{margin:0px 5px;}
	#calendarPrev, #calendarNext						{margin:0px 5px;}
	.vCalendarDisplayMode>span							{margin-left:5px;}
	#calsListDisplayAll, .vCalendarDisplayModeLabel		{display:none!important;}
	.vCalendarHeader>div								{width:auto; padding:12px 3px 12px 3px; white-space:nowrap!important; text-transform:lowercase;}
	.vCalEvtBlock										{padding:2px!important;}
}

/* IMPRESSION */
@media print{
	@page {size:landscape;}
	#syntheseBlock, .vCalendarDisplayModeLabel	{display:none!important;}/*affiche pas la synthese des agendas, ni les menus de chaque agendas*/
	.vCalendarPeriod							{text-align:right;}
	.vCalendarBlock								{page-break-after:always; margin:0px; box-shadow:none;}/*saut de page, sauf pour le dernier de la liste*/
	.vCalendarBlock:last-child					{page-break-after:avoid;}
	.vCalEvtBlock								{background:none;}
	.objMenuBurger, .objMenuBurgerInline		{display:none;}/*Masque le menu contextuel*/
	.vCalEvtLabel								{color:#333;}
}
</style>


<div id="pageFull">
	<div id="pageModuleMenu">
		<div id="pageModMenu" class="miscContainer">
			<?php
			////	PROPOSITIONS D'EVENEMENT A CONFIRMER ?
			if(!empty($eventProposition))  {echo $eventProposition;}

			////	AGENDAS VISIBLES
			if(!empty($readableCalendars))
			{
				echo "<form action='index.php' method='get' id='calsList'>";
					//Label du menu d'affichage des agendas
					echo "<div id='calsListLabel'>".Txt::trad("CALENDAR_calsList")." :</div>";
					//liste des agendas
					foreach($readableCalendars as $tmpCal){
						echo "<div class='calsListCalendar'>
								<input type='checkbox' name='displayedCalendars[]' value='".$tmpCal->_id."' id=\"displayedCal".$tmpCal->_typeId."\" onchange=\"$('#calsList .submitButtonInline').fadeIn();\" ".(CtrlCalendar::isDisplayedCal($displayedCalendars,$tmpCal)?"checked":null).">
								<label for=\"displayedCal".$tmpCal->_typeId."\" title=\"".Txt::tooltip($tmpCal->description)."\" class='noTooltip'>".$tmpCal->title."</label> ".(Req::isMobile()==false?$tmpCal->contextMenu(["iconBurger"=>"inlineSmall"]):null)."
							 </div>";
					}
					// Bouton "Afficher" (cf. "submitButtonInline")  +  Input "curTime"
					echo Txt::submitButton("show",false).'<input type="hidden" name="curTime" value="'.Req::param("curTime").'"/>';
					//"Afficher tous les agendas" (Admin géneral)
					if(Ctrl::$curUser->isGeneralAdmin() && $_SESSION["calsListDisplayAll"]==false)
						{echo "<div id='calsListDisplayAll'><a onclick=\"redir('?ctrl=calendar&calsListDisplayAll=1')\" title=\"".Txt::trad("CALENDAR_calsListDisplayAll")."\"><img src='app/img/plusSmall.png'></a></div>";}
				echo "</form><hr>";
			}

			////	BOUTON POUR AJOUTER OU PROPOSER UN EVT : SI QU'UN SEUL AGENDA N'EST AFFICHÉ
			if(count($displayedCalendars)==1 && $displayedCalendars[0]->addOrProposeEvt())
				{echo "<div class='menuLine' onclick=\"lightboxOpen('".MdlCalendarEvent::getUrlNew()."&_idCal=".$displayedCalendars[0]->_id."')\" title=\"".Txt::tooltip($displayedCalendars[0]->title." : ".$displayedCalendars[0]->addEventLabel)."\"><div class='menuIcon'><img src='app/img/plus.png'></div><div>".Txt::trad("CALENDAR_addEvt")."</div></div>";}

			////	CATEGORIES D'EVT (FILTRE)
			//Catégorie courante + menu des catégores disponible pour filtrer les résultats + Menu d'édition des catégories
			$curCatLabel=Req::isParam("_idCatFilter")  ?  Ctrl::getObj("calendarEventCategory",Req::param("_idCatFilter"))->getLabel()  :  Txt::trad("all2");
			$menuCategory="<div>".Txt::trad("CALENDAR_categoryDisplayed")." : </div>".
						  "<div onclick=\"redir('?ctrl=calendar')\" ".(Req::isParam("_idCatFilter")?null:"class='linkSelect'")."> &nbsp; <div class='categoryColor categoryColorAll'>&nbsp;</div> ".Txt::trad("all2")."</div>";
			foreach(MdlCalendarEventCategory::getList() as $tmpCategory)  {$menuCategory.="<div onclick=\"redir('?ctrl=calendar&_idCatFilter=".$tmpCategory->_id."')\" ".(Req::param("_idCatFilter")==$tmpCategory->_id?'class="linkSelect"':null)." title=\"".Txt::tooltip($tmpCategory->description)."\"> &nbsp; ".$tmpCategory->getLabel()."</div>";}
			if(MdlCalendarEventCategory::addRight())  {$menuCategory.="<hr><div onclick=\"lightboxOpen('".MdlCalendarEventCategory::getUrlEditObjects()."');\"><img src='app/img/edit.png'> ".Txt::trad("CALENDAR_categoriesEditTitle")."</div>";}
			//Affiche le menu
			echo "<div class='menuLine'>
					<div class='menuIcon'><img src='app/img/category.png'></div>
					<div>
						<div class='menuLaunch' for='menuCategory'>".Txt::trad("CALENDAR_categoryCurrent")." : ".$curCatLabel."</div>
						<div id='menuCategory' class='menuContext'>".$menuCategory."</div>
					</div>
				  </div>";
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
			<div class="menuLine" onclick="calendarDimensions(true);print();" title="<?= Txt::trad("CALENDAR_printCalendarsInfos") ?>">
				<div class="menuIcon"><img src="app/img/print.png"></div>
				<div><?= Txt::trad("CALENDAR_printCalendars") ?></div>
			</div>
			<?php } ?>

			<!--CALENDRIER MOIS VIA LE DATEPICKER DE JQUERY-UI-->
			<?php if($displayMode!="month")  {echo "<div id='datepickerCalendar'></div>";} ?>
		</div>
	</div>

	<div id="pageFullContent">
		<!--SYNTHESE DES AGENDAS ?-->
		<?php if(!empty($periodSynthese)){ ?>
			<div id="syntheseBlock" class="miscContainer">
				<div id="syntheseTable">
					<!--HEADER DE LA SYNTHESE-->
					<div id="syntheseLineHeader">
						<div class="vSyntheseLabel">&nbsp;</div>
						<?php foreach($periodSynthese as $tmpDay)  {echo "<div class=\"vSyntheseDay ".(date("Y-m-d",$tmpDay["timeBegin"])==date("Y-m-d")?"vSyntheseDayCurDay":null)."\">".(int)date("d",$tmpDay["timeBegin"])."</div>";} ?>
					</div>
					<!--AFFICHE CHAQUE AGENDA-->
					<?php foreach($displayedCalendars as $tmpCal){ ?>
					<div class="vSyntheseLine">
						<div class="vSyntheseLabel" onclick="$('#blockCal<?= $tmpCal->_typeId ?>').scrollTo();"><?= $tmpCal->title ?></div>
						<!--CELLULES DE CHAQUE JOUR DE L'AGENDA-->
						<?php
						foreach($periodSynthese as $tmpDay)
						{
							//Tooltip des evts du jour
							$tmpEvtTooltip="<div class='vSyntheseDayEvtTooltip'>".$tmpCal->title." : ".Txt::dateLabel($tmpDay["timeBegin"],"dateFull")."<ul>";
							foreach($tmpDay["calsEvts"][$tmpCal->_id] as $tmpEvt)	{$tmpEvtTooltip.="<br><img src='app/img/arrowRight.png'> ".Txt::dateLabel($tmpEvt->dateBegin,"mini",$tmpEvt->dateEnd)." : ".Txt::reduce($tmpEvt->title,60);}
							$tmpEvtTooltip.="</ul></div>";
							//Cellule des evts du jour
							$syntheseDayCalWE=$syntheseDayEvts=null;
							if(date("N",$tmpDay["timeBegin"])>5)	{$syntheseDayCalWE="vSyntheseDayCalWE";}
							foreach($tmpDay["calsEvts"][$tmpCal->_id] as $tmpEvt)	{$syntheseDayEvts.="<div class='vSyntheseDayEvt' onclick=\"lightboxOpen('".$tmpEvt->getUrl("vue")."')\" style=\"background-color:".$tmpEvt->catColor."\">&nbsp;</div>";}
							echo "<div class='vSyntheseDay vSyntheseDayCal ".$syntheseDayCalWE."'>
									<div class='vSyntheseDayEvts' title=\"".Txt::tooltip($tmpEvtTooltip)."\">".$syntheseDayEvts."</div>
								  </div>";
						}
						?>
					</div>
					<?php } ?>
					<!--LIGNE DE SYNTHESE DES AGENDAS-->
					<div class="vSyntheseLineFooter">
						<div class="vSyntheseLabel"><?= Txt::trad("CALENDAR_synthese") ?></div>
						<?php foreach($periodSynthese as $tmpDay){ ?>
						<div class="vSyntheseDay vSyntheseDayCal <?= date("N",$tmpDay["timeBegin"])>5?"vSyntheseDayCalWE":null ?>">
							<div class="vSyntheseDayEvts">
								<?php if(!empty($tmpDay["nbCalsWithEvt"])){ ?><div class="vSyntheseDayEvt" style="background-color:#777" title="<div class='vSyntheseDayEvtTooltip'><?= $tmpDay["nbCalsWithEvt"]."</div>" ?>">&nbsp;</div><?php } ?>	
							</div>
						</div>
						<?php } ?>
					</div>
				</div>
			</div>
		<?php } ?>

		
		<!--AFFICHE CHAQUE AGENDA-->
		<?php foreach($displayedCalendars as $tmpCal){ ?>
		<div class="vCalendarBlock miscContainer" id="blockCal<?= $tmpCal->_typeId ?>">
			<div class="vCalendarHeader" id="headerCal<?= $tmpCal->_typeId ?>">
				<!--TITRE DE L'AGENDA-->
				<div class="vCalendarTitle">
					<?php
					//Menu contextuel de l'agenda (cf. ".objMenuBurger")  &&  Label de l'agenda  &&  Icone de l'user (agenda perso)
					$tmpCalLabel=(Req::isMobile())  ?  Txt::reduce($tmpCal->title,20)  :  $tmpCal->title;
					$tmpIconBurgerSize=(Req::isMobile())  ?  "inlineSmall"  :  "inlineBig";
					$tmpCalIcon=($tmpCal->type=="user")  ?  Ctrl::getObj("user",$tmpCal->_idUser)->getImg(true,true)  :  null;
					echo $tmpCalIcon."<span class='vCalendarTitleLabel' title=\"".Txt::tooltip($tmpCal->description)."\">".$tmpCalLabel."</span>".$tmpCal->contextMenu(["iconBurger"=>$tmpIconBurgerSize]);
					?>
				</div>
				<!--PERIODE AFFICHEE-->
				<div class="vCalendarPeriod">
					<img src="app/img/navPrev.png" id="calendarPrev" class="noPrint" onclick="redir('?ctrl=calendar&curTime=<?= $urlTimePrev.$urlCatFilter ?>')" title="<?= Txt::trad("CALENDAR_periodPrevious") ?>">
					<span class="menuLaunch vCalendarPeriodLabel" for="calMonthPeriodMenu<?= $tmpCal->_typeId ?>"><?= ucfirst($labelMonth) ?></span>
					<?php if(!empty($calMonthPeriodMenu))  {echo "<div class='menuContext' id='calMonthPeriodMenu".$tmpCal->_typeId."'><div id='calMonthPeriodMenuContainer'>".$calMonthPeriodMenu."</div></div>";} ?>
					<img src="app/img/navNext.png"  id="calendarNext" class="noPrint" onclick="redir('?ctrl=calendar&curTime=<?= $urlTimeNext.$urlCatFilter ?>')" title="<?= Txt::trad("CALENDAR_periodNext") ?>">
				</div>
				<!--OPTION "AUJOURD'HUI"  &&  AFFICHAGE MONTH/WEEK/WORKWEEK/4DAYS/DAY-->
				<div class="vCalendarDisplayMode">
					<span class="vCalendarDisplayToday" onclick="redir('?ctrl=calendar&curTime=<?= time() ?>')"><img src="app/img/calendar/displayToday.gif"><span class="vCalendarDisplayModeLabel"><?= Txt::trad("today") ?></span></span>
					<span class="menuLaunch" for="menuDisplayMode<?= $tmpCal->_typeId ?>"><img src="app/img/calendar/display<?= ucfirst($displayMode) ?>.gif"><span class="vCalendarDisplayModeLabel"><?= Txt::trad("CALENDAR_display_".$displayMode) ?></span>&nbsp;<img src="app/img/arrowBottom.png"></span>
					<div class="menuContext" id="menuDisplayMode<?= $tmpCal->_typeId ?>">
					<?php
					//Affiche les $displayModes
					foreach(MdlCalendar::$displayModes as $displayModeTmp)
						{echo "<div class='menuLine ".($displayModeTmp==$displayMode?"linkSelect":null)."' onclick=\"redir('?ctrl=calendar&displayMode=".$displayModeTmp."')\"><div class='menuIcon'><img src='app/img/calendar/display".ucfirst($displayModeTmp).".gif'></div><div>".ucfirst(Txt::trad("CALENDAR_display_".$displayModeTmp))."</div></div>";}
					?>
					</div>
				</div>
			</div>
			<!--CONTENU DU CALENDRIER ("VueCalendarMonth"/"VueCalendarWeek")-->
			<div class="vCalendarVue" data-typeId="<?= $tmpCal->_typeId ?>"><?= $tmpCal->calendarVue ?></div>
		</div>
		<?php } ?>

		
		<!--AUCUN AGENDA-->
		<?php if(empty($displayedCalendars))  {echo "<div class='emptyContainer'>".Txt::trad("CALENDAR_noCalendarDisplayed")."</div>";} ?>
	</div>
</div>