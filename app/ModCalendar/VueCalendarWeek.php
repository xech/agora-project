<?php if($tmpCal->isFirstCal==true){ ?>
<script>
////	Gère l'affichage de la vue "week" (cf. "VueIndex.php")
function calendarDimensions(printCalendar)
{
	////	Hauteur du block scroller des agendas, puis affichage
	weekScrollerHeight=Math.round($(".vCalWeekMain").innerHeight() - $(".vCalWeekHeader").outerHeight(true));
	$(".vCalWeekScroller").css("height",weekScrollerHeight).show();//height, puis on raffiche

	////	Init la sélection  &&  "mouseup" sur la page : réinit la sélection
	isMouseDown=daySelected=timeSelectBegin=timeSelectEnd=null;
	$(document).mouseup(function(){
		isMouseDown=daySelected=timeSelectBegin=timeSelectEnd=null;
		$(".vCalWeekHourSubCell").removeClass("vCalWeekHourSubCellSelect");
	});

	////	Lance l'affichage de chaque agenda
	$(".vCalWeekScroller").each(function(){
		////	Hauteur des lignes d'heure  (le créneau horaire de l'agenda doit faire la hauteur du "weekScrollerHeight")
		var curCalendarId="#"+this.id;															//ID de l'Agenda courant
		var timeSlotHours=parseInt($(this).attr("data-timeSlotDuration")) +1;					//+1 : nb d'heures sur la plage horaire ("TimeSlot")
		if(timeSlotHours<5  || (timeSlotHours>14 && isMobile()))  {timeSlotHours=14;}			//timeslot minimum (par défaut) et maximum (mobile)
		var calWeekLineHeight=Math.round(weekScrollerHeight / timeSlotHours)-0.5;				//enleve la bordure des lignes
		if(calWeekLineHeight<35 || printCalendar===true)  {calWeekLineHeight=35;}				//hauteur minimum des lignes d'heure
		$(".vCalWeekLine,.vCalWeekHourSubTable",curCalendarId).outerHeight(calWeekLineHeight);	//Applique la Hauteur à chaque ligne!

		////	Largeur/Hauteur de chaque jour
		var nbDaysDisplayed=$(curCalendarId+" .vCalWeekLine:first-child .vCalWeekCell").length;							//Nombre de jours affichés (sur la première ligne)
		var weekCellWidth=Math.round( ($(this).innerWidth() - $(".vCalWeekHourLabel").innerWidth()) / nbDaysDisplayed);	//Largeur de chaque jours
		$(curCalendarId+" .vCalWeekCell, .vCalWeekHeaderDay").css("width",weekCellWidth+"px");							//Applique la largeur
		weekCellHeight=$(curCalendarId+" .vCalWeekLine:first-child .vCalWeekCell").outerHeight(true);					//Récupère la hauteur des jours

		////	Affiche chaque événement
		hearlierEvtTop=null;
		$(curCalendarId+" .vCalEvtBlock").each(function(){
			//Largeur, Position left et top
			var curDaySelector=curCalendarId+" .vCalWeekLine:first-child [data-dayCpt='"+$(this).attr("data-dayCpt")+"']";
			var minutesFromDayBegin=$(this).attr("data-minutesFromDayBegin");
			var evtPosTop=(minutesFromDayBegin!="evtBeforeDayBegin")  ?  Math.round((weekCellHeight/60) * parseInt(minutesFromDayBegin))  :  0;
			var evtPosLeft=$(curDaySelector).position().left;
			var evtWidth=$(curDaySelector).outerWidth()-1;
			//L'evt précédent se trouve sur le même créneau horaire : on décale l'evt courant OU  on split les 2 evt
			if(typeof previousEvtDaySelector!=="undefined" && previousEvtDaySelector==curDaySelector)
			{
				var previousEvtSameTimeSlot=($(this).attr("data-timeBegin") < $(previousEvtId).attr("data-timeEnd"));					//Les événements se chevauchent (Evt courant commence avant la fin de l'evt précédent)
				var previousEvtSameBegin=(parseInt($(this).attr("data-timeBegin") - $(previousEvtId).attr("data-timeBegin")) < 800);	//Evt précédent commence quasi en même temps (15mn d'écart, grand max..)
				if(previousEvtSameTimeSlot==true || previousEvtSameBegin==true){
					var leftMargin=(previousEvtSameBegin==true)  ?  Math.round(weekCellWidth/2)  :  15;			//Meme début : evt splité en 2  ||  Evts se chevauchent : décale l'evt courant de 15px
					var evtPosLeft=evtPosLeft + leftMargin;														//Ajoute de la marge à gauche de l'evt courant (décale donc l'evt à droite)
					var evtWidth=weekCellWidth - leftMargin - 2;												//On réduit ensuite la largeur de l'evt pour pas dépasser de la colonne du jour
					$(this).css("border","solid 1px #888");														//ajoute une bordure pour mieux différencier les 2 evt
					if(previousEvtSameBegin==true)  {$(previousEvtId).outerWidth(Math.round(weekCellWidth/2));}	//cellule splité en 2 : réduit/ajuste la largeur de l'evt précédent
				}
			}
			//Retient les infos de l'evt courant pour l'evt suivant
			previousEvtDaySelector=curDaySelector;
			previousEvtId="#"+this.id;
			//Applique la largeur et la position de l'evt
			$(this).css("top",evtPosTop).css("left",evtPosLeft).outerWidth(evtWidth);
			//Hauteur de l'evt en pixels
			var evtHeightMini=25;//hauteur minimum
			var durationMinutes=parseInt($(this).attr("data-durationMinutes"));
			var evtHeight=Math.round((weekCellHeight/60) * durationMinutes);//30mn mini
			if(evtHeight<evtHeightMini)  {evtHeight=evtHeightMini;}
			$(this).outerHeight(evtHeight-1);//1px de marge entre chaque evt
			//L'evt commence à l'heure la plus tôt de la semaine (cf. scrolltop)
			if(minutesFromDayBegin!="evtBeforeDayBegin" && (hearlierEvtTop===null || evtPosTop<hearlierEvtTop))  {hearlierEvtTop=evtPosTop;}
		});

		////	Place l'agenda (scroll) au début de la plage horaire OU sur l'événement dont l'heure est la plus tôt de la semaine
		var scrollTopTimeSlot=weekCellHeight * parseInt($(this).attr("data-timeSlotBegin"));
		var scrollTopCalWeek=(hearlierEvtTop!==null && hearlierEvtTop<scrollTopTimeSlot)  ?  hearlierEvtTop  :  scrollTopTimeSlot;
		$(this).scrollTop(scrollTopCalWeek);

		////	Sélection de créneau horaire pour l'ajout d'un evt (sauf sur mobile)
		if(isMobile()==false)
		{
			$(curCalendarId+" .vCalWeekHourSubCell").on("mousedown mousemove mouseup",function(event){
				//Init la sélection / Sélectionne le Timeslot (si on est sur le même jour)
				if(event.type=="mousedown")  {isMouseDown=true;}
				else if(event.type=="mousemove" && isMouseDown==true && (daySelected==null || daySelected==$(this).attr("data-newEvtDay")))
				{
					//Init "timeSelectBegin" & "timeSelectBegin" & Sélection du jour
					daySelected=$(this).attr("data-newEvtDay");
					var timeCellBegin=parseInt($(this).attr("data-newEvtTimeBegin"));
					var timeCellEnd=timeCellBegin+900;//15mn
					if(timeSelectBegin==null)	{timeSelectBegin=timeCellBegin;}
					timeSelectEnd=timeCellEnd;
					//Ajoute la classe aux cellules sélectionnées (entre "timeSelectBegin" et "timeSelectEnd")
					$(".vCalWeekHourSubCell[data-newEvtDay='"+daySelected+"']").each(function(){
						var timeCellBegin=parseInt($(this).attr("data-newEvtTimeBegin"));
						(timeSelectBegin<=timeCellBegin && timeCellBegin<timeSelectEnd)  ?  $(this).addClass("vCalWeekHourSubCellSelect")  :  $(this).removeClass("vCalWeekHourSubCellSelect");
					});
				}
				//Termine la sélection et ouvre le menu d'édition
				else if(event.type=="mouseup" && timeSelectBegin<timeSelectEnd){
					if(timeSelectBegin==null || timeSelectEnd==null)  {timeSelectBegin=timeSelectEnd=parseInt($(this).attr("data-newEvtTimeBegin"));}//Sélection pas encore initialisé (click direct sur une plage horaire)
					lightboxOpen("<?= MdlCalendarEvent::getUrlNew() ?>&_idCal="+$(this).attr("data-idCal")+"&newEvtTimeBegin="+timeSelectBegin+"&newEvtTimeEnd="+timeSelectEnd);//Edition d'evt & réinitialise la sélection
				}
			});
		}
	});

	////	Affiche l'agenda
	$(".vCalWeekMain").css("visibility","visible");
}
</script>

<style>
.vCalWeekMain													{height:100%; visibility:hidden;}
.vCalWeekScroller												{display:none; position:relative; overflow-y:scroll; overflow-x:hidden;}/*masqué par défaut puis affiché après calcul des dimensions (cf. "calendarDimensions" ci-dessus)*/
.vCalWeekHeader, .vCalWeekTable, .vCalWeekHourSubTable			{display:table; width:100%;}
.vCalWeekHeaderLine, .vCalWeekLine, .vCalWeekHourSubLine		{display:table-row;}
.vCalWeekHeaderLine>div, .vCalWeekLine>div, .vCalWeekHourSubCell{display:table-cell;}
.vCalWeekHeaderLine>div											{text-align:center; vertical-align:bottom; <?= $displayMode=="day"?"visibility:hidden;":null ?>}
.vCalWeekHeaderLine>div:last-child								{width:10px;}/*width du scroller*/
.vCalWeekHourLabel												{width:35px; text-align:right; color:#aaa; font-size:0.9em!important;}
.vCalWeekCell													{background:#fff; border-top:solid 1px #ddd; border-bottom:solid 1px #fff; border-left:solid 1px #ddd;}
.vCalWeekHourSubCell											{height:25%; line-height:5px;}/*"line-height" pour contenir la hauteur des heures sur les petites résolutions*/
.vCalWeekHourSubCellCurrent										{border-top:solid 1px #f00;}
.vCalWeekHourOutTimeslot, .vCalWeekHourSubCellPastTime			{background:#fafafa;}
.vCalWeekHourSubCell:hover, .vCalWeekHourSubCellSelect			{background:#eee;}
.vCalWeekHeaderDayToday											{color:#c00;}
.vCalWeekHeaderDayNumber										{font-size:1.1em!important; margin-left:3px;}
.vCalEvtBlock													{position:absolute;}

/*RESPONSIVE*/
@media screen and (max-width:1023px){
	.vCalWeekHourLabel										{min-width:18px; max-width:18px; width:18px; font-weight:normal; text-align:center;}/*min & max pour forcer la taille*/
	.vCalWeekHeaderDay										{font-size:0.9em!important;}
	.vCalWeekHeaderCelebrationDay, .vCalWeekHourLabelZero	{display:none;}
	.vCalEvtBlock .vCalEvtLabel								{text-transform:lowercase; font-size:0.9em;}
}

/* IMPRESSION */
@media print{
	.vCalWeekLine .vCalWeekCell:last-child	{border-right:solid 1px #ddd!important;}
	.vCalWeekLine:last-child .vCalWeekCell	{border-bottom:solid 1px #ddd!important;}
	.vCalWeekScroller						{overflow:visible!important;}
	.vCalWeekHourSubCell					{display:none!important;}
	.vCalEvtBlock							{border:solid 1px #ddd!important;}
}
</style>
<?php } ?>


<div class="vCalWeekMain">
	<!--HEADER DES JOURS : FIXE-->
	<div class="vCalWeekHeader">
		<div class="vCalWeekHeaderLine">
			<div class="vCalWeekHourLabel">&nbsp;</div>
			<?php
			foreach($periodDays as $tmpDay){
				$dayLabelFormat=Req::isMobile() ? "%a" : "%A";
				$classToday=(date("y-m-d",$tmpDay["timeBegin"])==date("y-m-d"))  ?  "vCalWeekHeaderDayToday"  :  null;
				$celebrationDay=(!empty($tmpDay["celebrationDay"]))  ?  " <img src='app/img/calendar/celebrationDay.png' class='vCalWeekHeaderCelebrationDay' title=\"".$tmpDay["celebrationDay"]."\">"  :  null;
				echo "<div class=\"vCalWeekHeaderDay ".$classToday."\">".ucfirst(Txt::formatime($dayLabelFormat,$tmpDay["timeBegin"]))."<span class='vCalWeekHeaderDayNumber'>".date("j",$tmpDay["timeBegin"])."</span>".$celebrationDay."</div>";
			}
			?>
		</div>
	</div>

	<?php
	////	PARTIE SCROLLABLE DE L'AGENDA : EVENEMENTS & GRILLE DES HEURES/MINUTES
	echo "<div class='vCalWeekScroller' id=\"calWeekScroller".$tmpCal->_targetObjId."\" data-timeSlotBegin=\"".$tmpCal->timeSlotBegin."\" data-timeSlotDuration=\"".round($tmpCal->timeSlotEnd-$tmpCal->timeSlotBegin)."\">";

		////	EVENEMENTS DU JOUR
		foreach($tmpCal->eventList as $tmpDateEvts)
		{
			foreach($tmpDateEvts as $tmpEvt)
			{
				//Init l'evt (pas de menu context en responsive)
				$divContainerAttr="onclick=\"lightboxOpen('".$tmpEvt->getUrl("vue")."');event.stopPropagation();\" data-dayCpt=\"".$tmpEvt->dayCpt."\" data-timeBegin=\"".strtotime($tmpEvt->dateBegin)."\" data-timeEnd=\"".strtotime($tmpEvt->dateEnd)."\" data-minutesFromDayBegin=\"".$tmpEvt->minutesFromDayBegin."\" data-durationMinutes=\"".$tmpEvt->durationMinutes."\" data-catColor=\"".$tmpEvt->catColor."\"";
				$evtContextMenu=(Req::isMobile()==false)  ?  $tmpEvt->contextMenu(["iconBurger"=>"small","_idCal"=>$tmpCal->_id,"curDateTime"=>strtotime($tmpEvt->dateBegin)])  :  null;
				$evtDisplayDate=Txt::displayDate($tmpEvt->dateBegin,"mini",$tmpEvt->dateEnd);
				$evtImportant=(!empty($tmpEvt->important))  ?  " <img src='app/img/important.png'>"  :  null;
				//Affiche l'evt
				echo $tmpEvt->divContainer("vCalEvtBlock",$divContainerAttr).$evtContextMenu.
						"<div class='vCalEvtLabel'>".$evtDisplayDate."&nbsp; ".$tmpEvt->title.$evtImportant."</div>
					 </div>";
			}
		}

		////	GRILLE DES HEURES/MINUTES
		echo "<div class='vCalWeekTable'>";
			for($H=0; $H<=23; $H++)
			{
				//CRÉNEAU HORS DU "TIMESLOT" DE L'AGENDA?
				$tmpHourClass=($H<$tmpCal->timeSlotBegin || $H>$tmpCal->timeSlotEnd || $H==12 || $H==13)  ?  "vCalWeekHourOutTimeslot"  :  null;
				//LIGNE + LABEL DE L'HEURE COURANTE + HEURE COURANTE POUR CHAQUE JOUR
				echo "<div class='vCalWeekLine'>";
					echo "<div class='vCalWeekHourLabel'>".$H."<span class='vCalWeekHourLabelZero'>:00</span></div>";
					foreach($periodDays as $dayCpt=>$tmpDay)
					{
						//CELLULE DE L'HEURE
						echo "<div class='vCalWeekCell ".$tmpHourClass."' data-dayCpt=\"".$dayCpt."\">";
						//TABLEAU DE SELECTION D'UN CRENEAU HORAIRE, POUR LA CREATION D'UN NOUVEL EVT (PAS SUR MOBILE)
						if(Req::isMobile()==false && $tmpCal->addProposeEvtRight())
						{
							//Init le tableau
							$newEvtDay=date("Ymd",$tmpDay["timeBegin"]);
							echo "<div class='vCalWeekHourSubTable'>";
								//DIVISE L'HEURE EN CELLULES DE 15 MN POUR L'AJOUT D'EVT (agenda en lecture seule: proposition d'evt)
								foreach([0,1,2,3] as $quarterHour)
								{
									//Init
									$quarterHourBegin=$tmpDay["timeBegin"]+(3600*$H)+(900*$quarterHour);
									$quarterHourEnd=$quarterHourBegin+900;	
									if(time()>$quarterHourBegin && time()<$quarterHourEnd)	{$halfCellClass="vCalWeekHourSubCellCurrent";}
									elseif($quarterHourBegin<time())						{$halfCellClass="vCalWeekHourSubCellPastTime";}
									else													{$halfCellClass=null;}
									$newEvtTitle=$tmpCal->addEventLabel." [".date("H:i",$quarterHourBegin)."]";
									//Affiche la cellule de sélection
									echo "<div class='vCalWeekHourSubLine'>
											<div class=\"vCalWeekHourSubCell noTooltip ".$halfCellClass."\" title=\"".$newEvtTitle."\" data-idCal=\"".$tmpCal->_id."\" data-newEvtTimeBegin=\"".$quarterHourBegin."\" data-newEvtDay=\"".$newEvtDay."\">&nbsp;</div>
										  </div>";
								}
							echo "</div>";
						}
						echo "</div>";
					}
				echo "</div>";
			}
		?>
		</div>
	</div>
</div>