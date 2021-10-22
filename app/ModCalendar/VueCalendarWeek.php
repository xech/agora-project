<?php if($tmpCal->isFirstCal==true){ ?>
<script>
////	GÈRE L'AFFICHAGE DE LA VUE "WEEK" (cf. "VueIndex.php")
function calendarDimensions(printCalendar)
{
	////	CALCULE LA HAUTEUR D'AFFICHAGE DU SCROLLER DES AGENDAS
	weekScrollerHeight=Math.round($(".vCalWeekMain").innerHeight() - $(".vCalWeekHeader").outerHeight(true));
	$(".vCalWeekScroller").css("height",weekScrollerHeight).show();//calcul du height, puis on affiche

	////	LANCE L'AFFICHAGE DE CHAQUE AGENDA
	$(".vCalWeekScroller").each(function(){
		////	HAUTEUR DES LIGNES D'HEURE  (le créneau horaire de l'agenda doit faire la hauteur du "weekScrollerHeight")
		var curCalId="#"+this.id;																						//ID de l'Agenda courant
		var timeSlotHours=parseInt($(this).attr("data-timeSlotDuration")) +1;											//+1 : nb d'heures sur la plage horaire ("TimeSlot")
		if(timeSlotHours<5  || (timeSlotHours>14 && isMobile()))  {timeSlotHours=14;}									//timeslot minimum (par défaut) et maximum (mobile) : en heures
		var calWeekLineHeight=Math.round(weekScrollerHeight / timeSlotHours)-1;											//enleve la bordure des lignes
		if(calWeekLineHeight<35 || printCalendar===true)  {calWeekLineHeight=35;}										//hauteur minimum des lignes d'heure
		$(".vCalWeekLine,.vCalWeekHourSubTable",curCalId).outerHeight(calWeekLineHeight);								//Applique la Hauteur à chaque ligne!

		////	LARGEUR/HAUTEUR DE CHAQUE JOUR
		var nbDaysDisplayed=$(curCalId+" .vCalWeekLine:first-child .vCalWeekCell").length;								//Nombre de jours affichés (sur la première ligne)
		var weekCellWidth=Math.round( ($(this).innerWidth() - $(".vCalWeekHourLabel").innerWidth()) / nbDaysDisplayed);	//Largeur de chaque jours
		$(curCalId+" .vCalWeekCell, .vCalWeekHeaderDay").css("width",weekCellWidth+"px");								//Applique la largeur
		weekCellHeight=$(curCalId+" .vCalWeekLine:first-child .vCalWeekCell").outerHeight(true);						//Récupère la hauteur des jours

		////	AFFICHE CHAQUE ÉVÉNEMENT
		hearlierEvtTop=null;
		$(curCalId+" .vCalEvtBlock").each(function(){
			//Largeur, Position left et top
			var curDaySelector=curCalId+" .vCalWeekLine:first-child [data-dayCpt='"+$(this).attr("data-dayCpt")+"']";
			var evtPosLeft=$(curDaySelector).position().left;
			var evtWidth=$(curDaySelector).outerWidth()-1;
			//L'evt précédent se trouve sur le même créneau horaire : on décale l'evt courant OU on split les 2 evt
			if(typeof lastEvtDaySelector!=="undefined" && lastEvtDaySelector==curDaySelector)
			{
				var lastEvtSameTimeSlot=($(this).attr("data-timeBegin") < $(lastEvtId).attr("data-timeEnd"));					//Les événements se chevauchent (Evt courant commence avant la fin de l'evt précédent)
				var lastEvtSameBegin=(parseInt($(this).attr("data-timeBegin") - $(lastEvtId).attr("data-timeBegin")) < 800);	//Evt précédent commence avec moins de 15mn d'écart
				if(lastEvtSameTimeSlot==true || lastEvtSameBegin==true){
					var leftMargin=(lastEvtSameBegin==true)  ?  Math.round(weekCellWidth/2)  :  40;								//Meme début : evt splité en 2 || Evts se chevauchent : décale l'evt de 40px à droite 
					var evtPosLeft=evtPosLeft + leftMargin;																		//Ajoute de la marge à gauche de l'evt courant (décale donc l'evt à droite)
					var evtWidth=weekCellWidth - leftMargin - 2;																//On réduit ensuite la largeur de l'evt pour pas dépasser de la colonne du jour
					$(this).css("border","solid 1px #888");																		//Ajoute une bordure pour mieux différencier les 2 evts
					if(lastEvtSameBegin==true)  {$(lastEvtId).outerWidth(Math.round(weekCellWidth/2));}							//Evt splité : réduit la largeur de l'evt précédent
				}
			}
			//Position et dimension de l'Evt
			var minutesFromDayBegin=parseInt($(this).attr("data-minutesFromDayBegin"));											//Nombre de minute depuis le début du jour
			var evtPosTop=Math.round((weekCellHeight/60) * minutesFromDayBegin);												//Position Top de l'evt
			var evtHeight=Math.round((weekCellHeight/60) * parseInt($(this).attr("data-durationMinutes")) );					//Hauteur de l'evt
			var evtHeight=(evtHeight<25) ? 25 : (evtHeight-1);																	//Height de 25px minimum || Height avec 1px de marge entre chaque evt
			$(this).css("top",evtPosTop).css("left",evtPosLeft).outerWidth(evtWidth).outerHeight(evtHeight);					//Applique la position et dimensions de l'evt
			//Infos pour l'evt suivant
			if(minutesFromDayBegin>0 && (hearlierEvtTop===null || evtPosTop<hearlierEvtTop))  {hearlierEvtTop=evtPosTop;}		//Scrolltop de l'agenda en fonction de l'evt le plus tôt
			lastEvtDaySelector=curDaySelector;																					//Selecteur Jquery de l'evt
			lastEvtId="#"+this.id;																								//_id de l'evt
		});

		////	PLACE L'AGENDA (SCROLL) AU DÉBUT DE LA PLAGE HORAIRE OU SUR L'ÉVÉNEMENT DONT L'HEURE EST LA PLUS TÔT DE LA SEMAINE
		var scrollTopTimeSlot=weekCellHeight * parseInt($(this).attr("data-timeSlotBegin"));
		var scrollTopCalWeek=(hearlierEvtTop!==null && hearlierEvtTop<scrollTopTimeSlot)  ?  hearlierEvtTop  :  scrollTopTimeSlot;
		$(this).scrollTop(scrollTopCalWeek);
	});
	////	AFFICHE ENFIN TOUS LES AGENDAS!
	$(".vCalWeekMain").css("visibility","visible");

	////	SÉLECTION DE CRÉNEAU HORAIRE POUR L'AJOUT D'UN EVT (SAUF MOBILE)
	if(isMobile()==false)
	{
		//Init
		cellMouseDown=null;
		//// mousedown/mouseup/mousemove une cellule horaire
		$(".vCalWeekHourCell").on("mousedown mousemove mouseup",function(event){
			//Début de la sélection
			if(event.type=="mousedown"){
				cellMouseDown=true;														//La cellule est sélectionnée
				cellTimeBegin=parseInt($(this).attr("data-cellTimeBegin"));				//Time du début de sélection
				cellTimeEnd=parseInt($(this).attr("data-cellTimeEnd"));					//Time de fin de sélection
				cellDay=$(this).attr("data-cellDay");									//Jour sélectionné
			}
			//Sélectionne le Timeslot (verif qu'on reste sur le même jour..)
			else if(event.type=="mousemove" && cellMouseDown==true && cellDay==$(this).attr("data-cellDay")){
				cellTimeEnd=parseInt($(this).attr("data-cellTimeEnd"));					//Time de fin de sélection mis à jour
				$(".vCalWeekHourCell[data-cellDay='"+cellDay+"']").each(function(){		//Ajoute ou enlève la class des cellules sélectionnées : entre "cellTimeBegin" et "cellTimeEnd"
					(cellTimeBegin<=parseInt($(this).attr("data-cellTimeBegin")) && parseInt($(this).attr("data-cellTimeEnd"))<cellTimeEnd)  ?  $(this).addClass("vCalWeekHourCellSelect")  :  $(this).removeClass("vCalWeekHourCellSelect");
				});
			}
			//Fin de la sélection : ouvre l'édition d'un nouvel événement !
			else if(event.type=="mouseup" && cellTimeBegin<cellTimeEnd){
				lightboxOpen("<?= MdlCalendarEvent::getUrlNew() ?>&_idCal="+$(this).attr("data-idCal")+"&newEvtTimeBegin="+cellTimeBegin+"&newEvtTimeEnd="+cellTimeEnd);
				$(".vCalWeekHourCell").removeClass("vCalWeekHourCellSelect");			//Enlève la class de sélection à toutes les cellules
				cellMouseDown=cellTimeBegin=cellTimeEnd=null;							//Réinit les valeurs de sélection
			}
		});
	}
}
</script>

<style>
.vCalWeekMain													{height:100%; visibility:hidden;}
.vCalWeekScroller												{display:none; position:relative; overflow-y:scroll; overflow-x:hidden;}/*masqué par défaut puis affiché après calcul des dimensions (cf. "calendarDimensions" ci-dessus)*/
.vCalWeekHeader, .vCalWeekTable, .vCalWeekHourSubTable			{display:table; width:100%;}
.vCalWeekHeaderLine, .vCalWeekLine, .vCalWeekHourCells			{display:table-row;}
.vCalWeekHeaderLine>div, .vCalWeekLine>div, .vCalWeekHourCell	{display:table-cell;}
.vCalWeekHeaderLine>div											{text-align:center; vertical-align:bottom;}
.vCalWeekHeaderLine>div:last-child								{width:10px;}/*width du scroller*/
.vCalWeekHourLabel												{width:35px!important; max-width:35px!important; text-align:right; color:#aaa; font-size:0.9em!important;}
.vCalWeekCell													{background:#fff; border-top:solid 1px #ddd; border-bottom:solid 1px #fff; border-left:solid 1px #ddd;}
.vCalWeekHourCell												{height:25%; line-height:5px;}/*"line-height" pour contenir la hauteur des heures sur les petites résolutions*/
.vCalWeekHourCellCurrent										{border-top:solid 1px #f00;}
.vCalWeekHourOutTimeslot, .vCalWeekHourCellPastTime				{background:#fafafa;}
.vCalWeekHourCell:hover, .vCalWeekHourCellSelect				{background:#eee;}
.vCalWeekHeaderDayToday											{color:#c00;}
.vCalWeekHeaderDayNumber										{font-size:1.1em!important; margin-left:3px;}
.vCalEvtBlock													{position:absolute;}

/*RESPONSIVE*/
@media screen and (max-width:1023px){
	.vCalWeekHourLabel										{width:18px!important; max-width:18px!important; font-weight:normal; text-align:center;}/*min & max pour forcer la taille*/
	.vCalWeekHeaderDay										{font-size:0.9em!important;}
	.vCalWeekHeaderCelebrationDay, .vCalWeekHourLabelZero	{display:none;}
	.vCalEvtBlock .vCalEvtLabel								{text-transform:lowercase; font-size:0.9em;}
}

/* IMPRESSION */
@media print{
	.vCalWeekLine .vCalWeekCell:last-child	{border-right:solid 1px #ddd!important;}
	.vCalWeekLine:last-child .vCalWeekCell	{border-bottom:solid 1px #ddd!important;}
	.vCalWeekScroller						{overflow:visible!important;}
	.vCalWeekHourCell						{display:none!important;}
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
				$celebrationDay=(!empty($tmpDay["celebrationDay"]))  ?  " <img src='app/img/calendar/celebrationDay.png' class='vCalWeekHeaderCelebrationDay' title=\"".Txt::tooltip($tmpDay["celebrationDay"])."\">"  :  null;
				echo "<div class=\"vCalWeekHeaderDay ".$classToday."\">".ucfirst(Txt::formatime($dayLabelFormat,$tmpDay["timeBegin"]))."<span class='vCalWeekHeaderDayNumber'>".date("j",$tmpDay["timeBegin"])."</span>".$celebrationDay."</div>";
			}
			?>
		</div>
	</div>

	<?php
	////	PARTIE SCROLLABLE DE L'AGENDA : EVENEMENTS & GRILLE DES HEURES/MINUTES
	echo "<div class='vCalWeekScroller' id=\"calWeekScroller".$tmpCal->_typeId."\" data-timeSlotBegin=\"".$tmpCal->timeSlotBegin."\" data-timeSlotDuration=\"".round($tmpCal->timeSlotEnd-$tmpCal->timeSlotBegin)."\">";

		////	EVENEMENTS DU JOUR
		foreach($tmpCal->eventList as $tmpDateEvts)
		{
			foreach($tmpDateEvts as $tmpEvt)
			{
				//Init l'evt (pas de menu context en responsive)
				$divContainerAttr="onclick=\"lightboxOpen('".$tmpEvt->getUrl("vue")."');event.stopPropagation();\" data-dayCpt=\"".$tmpEvt->dayCpt."\" data-timeBegin=\"".strtotime($tmpEvt->dateBegin)."\" data-timeEnd=\"".strtotime($tmpEvt->dateEnd)."\" data-minutesFromDayBegin=\"".$tmpEvt->minutesFromDayBegin."\" data-durationMinutes=\"".$tmpEvt->durationMinutes."\" data-catColor=\"".$tmpEvt->catColor."\"";
				$evtContextMenu=(Req::isMobile()==false)  ?  $tmpEvt->contextMenu(["iconBurger"=>"small","_idCal"=>$tmpCal->_id,"curDateTime"=>strtotime($tmpEvt->dateBegin)])  :  null;
				$evtDisplayDate=Txt::dateLabel($tmpEvt->dateBegin,"mini",$tmpEvt->dateEnd);
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
				//LIGNE DE L'HEURE COURANTE : LABEL DE L'HEURE + CELLULE DE L'HEURE POUR CHAQUE JOUR
				echo "<div class='vCalWeekLine'>";
					echo "<div class='vCalWeekHourLabel'>".$H."<span class='vCalWeekHourLabelZero'>:00</span></div>";//:00 pour les minutes
					foreach($periodDays as $dayCpt=>$tmpDay)
					{
						//CELLULE DE L'HEURE
						echo "<div class='vCalWeekCell ".$tmpHourClass."' data-dayCpt=\"".$dayCpt."\">";
						//AJOUT OU PROPOSITION D'EVT : CREE LE TABLEAU DE SELECTION D'UN CRENEAU HORAIRE PAR "DRAG & DROP" (SAUF SUR MOBILE)
						if($tmpCal->addOrProposeEvt() && Req::isMobile()==false)
						{
							//Init le tableau
							$cellDay=date("Ymd",$tmpDay["timeBegin"]);
							echo "<div class='vCalWeekHourSubTable'>";
								//DIVISE L'HEURE EN CELLULES DE 15 MN POUR L'AJOUT D'EVT (agenda en lecture seule: proposition d'evt)
								foreach([0,1,2,3] as $quarterHour)
								{
									//Init
									$cellTimeBegin=$tmpDay["timeBegin"]+(3600*$H)+(900*$quarterHour);
									$cellTimeEnd=$cellTimeBegin+900;	
									if(time()>$cellTimeBegin && time()<$cellTimeEnd)	{$halfCellClass="vCalWeekHourCellCurrent";}
									elseif($cellTimeBegin<time())						{$halfCellClass="vCalWeekHourCellPastTime";}
									else												{$halfCellClass=null;}
									$newEvtTitle=$tmpCal->addEventLabel." [".date("H:i",$cellTimeBegin)."]";
									//Affiche la cellule de sélection
									echo "<div class='vCalWeekHourCells'>
											<div class=\"vCalWeekHourCell noTooltip ".$halfCellClass."\" title=\"".Txt::tooltip($newEvtTitle)."\" data-idCal=\"".$tmpCal->_id."\" data-cellTimeBegin=\"".$cellTimeBegin."\" data-cellTimeEnd=\"".$cellTimeEnd."\" data-cellDay=\"".$cellDay."\">&nbsp;</div>
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