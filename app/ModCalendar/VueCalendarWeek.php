<?php if($tmpCal->isFirstCal==true){ ?>
<script>
/*******************************************************************************************
 *	DIMENSIONNE LES AGENDAS
 *******************************************************************************************/
function calendarDisplay(printCalendar)
{
	////	HAUTEUR DES AGENDAS (SCROLLER) ET DES LIGNES D'HEURE
	$(".vWeekScroller").outerHeight( Math.floor($(".vWeekMain").height()-$(".vWeekHeader").height()) );		//Height du .vWeekScroller des agendas (partie visible et scrollable des agendas)
	let weekCellHeight=Math.floor( $(".vWeekScroller").height() / (printCalendar==true?24:12) );			//Height des .vWeekCell, en fonction du .vWeekScroller et la plage horaire affichée (12h ou 24h)
	if(weekCellHeight<30 || printCalendar==true)  {weekCellHeight=30;}										//Height minimum des heures (cf. mobile & "print()" : tester en 1536x864px)
	$(".vWeekCell").outerHeight(weekCellHeight,true);														//Height des heures (avec margins)
	$(".vWeekQuarter").outerHeight( Math.floor($(".vWeekCell").height()/4) );								//Height des 1/4 d'heures sélectionnables
	let hourHeightRef=weekCellHeight-1;																		//Height de référence : -1px de margin-bottom (margins du .vWeekCell sont fusionnées via "border-collapse")

	////	LARGEUR DES JOURS (COLONNES)
	let weekCellWidth=Math.floor( ($(".vWeekScroller").width() - $(".vWeekHourLabel").width()) / <?= count($periodDays) ?>);
	$(".vWeekHeaderDay, .vWeekCell").outerWidth(weekCellWidth,true);//Width du label des jours et cellules des heures (avec margins)

	////	AFFICHE CHAQUE ÉVÉNEMENT DE CHAQUE AGENDA
	$(".vWeekScroller").each(function(){
		let calSelector=this;																										//Selecteur de l'agenda courant
		let calScrollTop=Math.floor(hourHeightRef * $(this).attr("data-timeSlotBegin"));											//ScrollTop de l'agenda en fonction de la plage horaire affichée (timeslot)
		$(this).find(".vEventBlock").each(function(){																				//Affichage de chaque Evt :
			let dayDate=$(this).attr("data-dayDate");																				//Date de l'evt
			let daySelector=".vWeekCell[data-dayDate='"+dayDate+"']:first";															//Selecteur de la 1ere cellule d'heure du jour (0:00) pour récupérer ses dimensions
			let evtPosLeft=$(daySelector).position().left;																			//Position Left de l'evt en fonction de la colonne du jour
			let evtWidth=$(daySelector).width();																					//Width de l'evt en fonction de la colonne du jour (sans margins)
			let timeFromDayBegin=$(this).attr("data-timeFromDayBegin");																//Time du début de l'evt depuis le début du jour
			let sameBeginSelector=".vEventBlock[data-dayDate='"+dayDate+"'][data-timeFromDayBegin='"+timeFromDayBegin+"']";			//Selecteur des evts qui commencent en même temps
			let hasEvtBefore=(typeof prevEvtId!="undefined" && $(prevEvtId).attr("data-dayDate")==$(this).attr("data-dayDate"));	//Verif s'il ya un précédent evt le même jour
			//// D'autres evts commencent en même temps : split l'evt
			if($(calSelector).find(sameBeginSelector).length > 1){
				evtWidth=Math.floor(evtWidth / $(calSelector).find(sameBeginSelector).length);										//Largeur en fonction du nb d'evt à afficher cote à cote
				evtPosLeft+=Math.floor(evtWidth * $(calSelector).find(sameBeginSelector).index(this));								//Décale l'evt en fonction de son rang (index) parmi les autres evts
			}
			//// Evt chevauchant un autre evt OU Evt englobé dans un autre : décale l'evt (tester les 2 cas sur le même jour)
			else if(hasEvtBefore==true  &&  ($(this).attr("data-timeBegin") < timeEndDayMax || $(this).attr("data-timeEnd") < timeEndDayMax)){
				evtWidth-=15;																										//Réduit la largeur de l'evt (pas + de 15px!)
				evtPosLeft+=15;																										//Décale d'autant sur la droite
				$(this).css("border","solid 1px #777");																				//Ajoute une bordure pour différencier les 2 evts
			}
			//// Position / dimensions de l'evt
			let evtPosTop=Math.round((hourHeightRef/3600) * timeFromDayBegin);														//Calcule la position top
			let evtHeight=Math.round((hourHeightRef/3600) * $(this).attr("data-timeDuration"));										//Calcule la hauteur
			$(this).css("left",evtPosLeft).css("top",evtPosTop).outerWidth(evtWidth,true).outerHeight(evtHeight,true);				//Applique la position et dimensions (avec margins)
			$(this).find(".vEventLabel").outerHeight($(this).height());																//Applique la hauteur au label (pas de css "height:inherit")
			//// Update de variables
			if(timeFromDayBegin > 0 && evtPosTop < calScrollTop)  {calScrollTop=evtPosTop;}											//Scrolltop de l'agenda ajusté en fonction de l'evt le plus tôt
			if(hasEvtBefore==false || $(this).attr("data-timeEnd") > timeEndDayMax)  {timeEndDayMax=$(this).attr("data-timeEnd");}	//Init/update si 1er evt du jour OU timeEnd de l'evt est supérieur
			prevEvtId="#"+this.id;																									//Update prevEvtId pour l'evt suivant
		});
		////	SCROLL L'AGENDA (DÉBUT DE PLAGE HORAIRE || EVT AU PLUS TÔT DE LA SEMAINE)
		$(this).scrollTop(calScrollTop);
	});
}

/******************************************************************************************
 *	AJOUT D'UN EVT EN SELECTIONNANT UN CRÉNEAU HORAIRE (SAUF MOBILE)
 ******************************************************************************************/
$(function(){
	if(isMobile()==false){
		//// Sélectionne une cellule .vWeekQuarter
		let isMouseDown=quarterTimeBegin=quarterTimeEnd=null;
		$(".vWeekQuarter").on("mousedown mousemove mouseup",function(event){
			if(event.type=="mousedown"){																			//// Début de sélection : init les valeurs
				isMouseDown=true;																					//Cellule sélectionnée
				quarterDate=$(this).attr("data-dayDate");															//Jour sélectionné
				quarterTimeBegin=parseInt($(this).attr("data-timeBegin"));											//Time du début de sélection
				quarterTimeEnd=parseInt($(this).attr("data-timeEnd"));												//Time de fin de sélection
			}
			else if(event.type=="mousemove" && isMouseDown==true && quarterDate==$(this).attr("data-dayDate")){		//// Continue la sélection sur le même jour
				quarterTimeEnd=parseInt($(this).attr("data-timeEnd"));												//Update le Time de fin de sélection
				$(".vWeekQuarter[data-dayDate='"+quarterDate+"']").each(function(){									//Sélectionne/déselectionne les cellules .vWeekQuarter du jour (descend/monte la souris)
					if(quarterTimeBegin <= parseInt($(this).attr("data-timeBegin"))  &&  parseInt($(this).attr("data-timeEnd")) < quarterTimeEnd)	{$(this).addClass("vWeekQuarterSelect");}
					else																															{$(this).removeClass("vWeekQuarterSelect");}
				});
			}
			else if(event.type=="mouseup" && quarterTimeBegin < quarterTimeEnd){									//// Fin de la sélection : ouvre l'édition d'un nouvel événement !
				lightboxOpen("<?= MdlCalendarEvent::getUrlNew() ?>&_idCal="+$(this).attr("data-idCal")+"&newEvtTimeBegin="+quarterTimeBegin+"&newEvtTimeEnd="+quarterTimeEnd);
				$(".vWeekQuarter").removeClass("vWeekQuarterSelect");												//Réinit la sélection
				isMouseDown=quarterTimeBegin=quarterTimeEnd=null;													//Réinit les valeurs (à la fin)
			}
		});
	}
});
</script>

<style>
.vWeekMain									{height:100%;}
.vWeekScroller								{position:relative; overflow-y:scroll; overflow-x:hidden;}				/*Partie visible de l'agenda*/
.vWeekHeader, .vWeekTable					{width:100%; border-collapse:collapse;}									/*Tableau du libellé des jour et de la grille des heures*/
.vWeekHeader td, .vWeekTable td				{padding:0px; text-align:center;}										/*Tableau du libellé des jour et de la grille des heures*/
.vWeekHeaderToday							{font-size:1.15em; color:#c00;}											/*Libellé d'aujourd'hui*/
.vWeekHeaderScrollbar						{width:15px;}															/*Width "fantome" de la scrollbar de .vWeekScroller*/
.vWeekHourLabel								{width:35px; vertical-align:top; color:#aaa; font-size:0.9em;}			/*Libellé des heures, à gauche du tableau*/
.vWeekCell									{font-size:0.1em; <?= Ctrl::$agora->skin=="white" ? "background:white;border:1px solid #dededf;" : "background:black;border:1px solid #333;" ?>}/*Cellule des heures*/
.vWeekHourNotTimeslot						{background:<?= Ctrl::$agora->skin=="white"?"#fafafa" : "#222" ?>}		/*Heures en dehors du Timeslot*/
.vWeekQuarter:hover, .vWeekQuarterSelect	{background:<?= Ctrl::$agora->skin=="white"?"#eee" : "#333" ?>;}		/*Quarts d'heure survolés/sélectionnés*/
.vWeekQuarterRedLine						{border-top:solid 1px #f00;}											/*Heure courante : ligne rouge*/
.vEventBlock								{position:absolute; min-height:20px;}									/*Hauteur minimum de 20px (exple: evt d'un quart d'heure)*/
.vEventLabel								{line-height:0.95em;}

/*MOBILE*/
@media screen and (max-width:1023px){
	.vWeekHourLabel				{width:18px!important; max-width:18px!important; font-weight:normal; text-align:center;}/*min & max pour forcer la taille*/
	.vWeekHeaderDay				{font-size:0.85em!important;}
	.vWeekHeaderCelebrationDay	{display:none;}
}

/* IMPRESSION */
@media print{
	.vWeekScroller				{overflow:visible!important;}
	.vWeekQuarter				{display:none!important;}
}
</style>
<?php } ?>


<div class="vWeekMain">
	<!--HEADER DES JOURS : FIXE-->
	<table class="vWeekHeader">
		<tr>
			<td class="vWeekHourLabel">&nbsp;</td>
			<?php
			foreach($periodDays as $tmpDay){
				$dayLabelFormat=Req::isMobile() ? "ccc d" : "cccc d";//Jour de la semaine ("lun. 12" ou "lundi 12")
				$classToday=(date("y-m-d",$tmpDay["timeBegin"])==date("y-m-d"))  ?  "vWeekHeaderToday"  :  null;
				$celebrationDay=(!empty($tmpDay["celebrationDay"]))  ?  '&nbsp;<img src="app/img/calendar/celebrationDay.png" class="vWeekHeaderCelebrationDay" title="'.Txt::tooltip($tmpDay["celebrationDay"]).'">'  :  null;
				echo '<td class="vWeekHeaderDay '.$classToday.'">'.ucfirst(Txt::formatime($dayLabelFormat,$tmpDay["timeBegin"])).$celebrationDay.'</td>';
			}
			?>
			<td class="vWeekHeaderScrollbar">&nbsp;</td>
		</tr>
	</table>

	<!--AGENDA SCROLLABLE : GRILLE DES HEURES & EVENEMENTS-->
	<div class="vWeekScroller" data-timeSlotBegin="<?= $tmpCal->timeSlotBegin ?>">
		<?php
		echo '<table class="vWeekTable">';
			for($tmpHour=0; $tmpHour<24; $tmpHour++){
				$weekHourLabel=$tmpHour.(Req::isMobile()?null:':00');
				$weekCellClass=($tmpHour < $tmpCal->timeSlotBegin || $tmpCal->timeSlotEnd <= $tmpHour || $tmpHour==12 || $tmpHour==13)  ?  "vWeekHourNotTimeslot"  :  null;
				echo '<tr>
						<td class="vWeekHourLabel">'.$weekHourLabel.'</td>';													//Label des heures : colonne de gauche
						foreach($periodDays as $tmpDate=>$tmpDay){																//Boucle sur le nombre de jours affichés
							echo '<td class="vWeekCell '.$weekCellClass.'" data-dayDate="'.$tmpDate.'">';						//Cellule principale des heures
								for($quarter=0; $quarter<4; $quarter++){														//Boucle sur les 1/4 d'heure
									$timeBegin=$tmpDay["timeBegin"]+(3600*$tmpHour)+(900*$quarter);								//Timestamp du début
									$timeEnd=$timeBegin+900;																	//Timestamp de fin
									$classRedLine=($timeBegin < time() && time() < $timeEnd) ? "vWeekQuarterRedLine" : null;	//Quart d'heure en cours : ligne rouge
									$quarterTooltip=$tmpCal->addEventLabel." : ".date("H:i",$timeBegin);						//Tooltip : exple "Ajouter un evt à 12:00"
									//Affiche la cellule de sélection
									echo '<div class="vWeekQuarter noTooltip '.$classRedLine.'" title="'.Txt::tooltip($quarterTooltip).'" data-dayDate="'.$tmpDate.'" data-timeBegin="'.$timeBegin.'" data-timeEnd="'.$timeEnd.'" data-idCal="'.$tmpCal->_id.'">&nbsp;</div>';
								}
							echo '</td>';
						}
				echo '</tr>';
			}
		echo '</table>';

		////	EVENEMENTS DE LA SEMAINE (".vEventBlock")
		foreach($tmpCal->eventList as $tmpDate=>$tmpDateEvts){
			foreach($tmpDateEvts as $tmpEvt){
				$tmpEvt->containerAttributes.=' data-dayDate="'.$tmpDate.'" data-timeBegin="'.$tmpEvt->timeBegin.'" data-timeEnd="'.$tmpEvt->timeEnd.'" data-timeFromDayBegin="'.$tmpEvt->timeFromDayBegin.'" data-timeDuration="'.$tmpEvt->timeDuration.'"';
				echo $tmpEvt->divContainerContextMenu($tmpEvt->containerClass, $tmpEvt->containerAttributes, $tmpEvt->contextMenuOptions).
						'<div class="vEventLabel" onclick="'.$tmpEvt->openVue().'" title="'.$tmpEvt->titleTooltip.'">'.$tmpEvt->title.$tmpEvt->importantIcon.'</div>
					 </div>';
			}
		}
		?>
	</div>
</div>