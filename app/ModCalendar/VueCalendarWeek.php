<?php if($tmpCal->isFirstCal==true){ ?>
<script>
/*******************************************************************************************
 *	DIMENSIONNE LES AGENDAS
 *******************************************************************************************/
function calendarDimensions(printCalendar)
{
	////	HAUTEUR DES AGENDAS (SCROLLER) ET DES LIGNES D'HEURE
	$(".vWeekScroller").outerHeight( Math.floor($(".vWeekMain").height()-$(".vWeekHeader").height()) );		//Height du .vWeekScroller des agendas (partie visible et scrollable des agendas)
	let timeSlotDisplayed=(printCalendar==true) ? 24 : 12;													//Plage horaire affichée sur 12h ou 24h
	let weekCellHeight=Math.floor($(".vWeekScroller").height() / timeSlotDisplayed);						//Height des .vWeekCell, en fonction du .vWeekScroller et la plage horaire affichée
	if(weekCellHeight<25 || printCalendar==true)  {weekCellHeight=25;}										//Height minimum des heures (cf. mobile & "print()")
	$(".vWeekCell").outerHeight(weekCellHeight,true);														//Height des heures (avec margins)
	$(".vWeekQuarter").outerHeight( Math.floor($(".vWeekCell").height()/4) );								//Height des 1/4 d'heures sélectionnables
	let hourHeightRef=weekCellHeight-1;																		//Height de référence : -1px de margin-bottom (les margins du .vWeekCell sont fusionnées via "border-collapse")

	////	LARGEUR DES JOURS (COLONNES)
	let weekCellWidth=Math.floor( ($(".vWeekScroller").width() - $(".vWeekHourLabel").width()) / <?= count($periodDays) ?>);
	$(".vWeekHeaderDay, .vWeekCell").outerWidth(weekCellWidth,true);//Width du label des jours et cellules des heures (avec margins)

	////	AFFICHE CHAQUE ÉVÉNEMENT DE CHAQUE AGENDA
	$(".vWeekScroller").each(function(){
		$(this).find(".vEventBlock").each(function(){
			//// Infos sur la colonne du jour
			let daySelector=".vWeekTable tr:first .vWeekCell[data-dayDate='"+$(this).attr("data-dayDate")+"']";				//Selecteur de la colonne du jour de l'evt (heure 0:00)
			let evtPosLeft=$(daySelector).position().left;																	//Position Left de l'evt en fonction de la colonne du jour
			let evtWidth=$(daySelector).width();																			//Width de l'evt en fonction de la colonne du jour (sans margins)
			//// Evt précédent sur le même timeslot : décale/split les evts
			if(typeof prevEvtDaySelector!="undefined"  &&  prevEvtDaySelector==daySelector  &&  $(this).attr("data-timeBegin") < $(prevEvtId).attr("data-timeEnd")){
				if(($(this).attr("data-timeBegin") - $(prevEvtId).attr("data-timeBegin")) >= 1800){							//Plus de 30mn de diff entre le début de chaque evts : on décale l'evt courant
					var rightShift=isMobile() ? 15 : 30;																	//- Décale l'evt de 30px
					$(this).css("border","solid 1px #888");																	//- Ajoute une bordure pour différencier les 2 evts
				}else{																										//Moins de 30mn de diff : on split chaque evts en 2
					var rightShift=Math.floor(evtWidth/2);																	//- Décale l'evt de 50%
					$(prevEvtId).outerWidth(rightShift,true);																//- Width de l'evt précédent (avec margins) réduit de moitié
				}
				evtPosLeft+=rightShift;																						//Décale l'evt sur la droite
				evtWidth-=rightShift;																						//Réduit la largeur de l'evt pour rester dans la colonne du jour
			}
			//// Position et dimension de l'Evt
			let timeFromDayBegin=parseInt($(this).attr("data-timeFromDayBegin"));											//Time de l'evt depuis le début du jour
			let evtPosTop=Math.round((hourHeightRef/3600) * timeFromDayBegin);												//Position Top de l'evt
			let evtHeight=Math.round((hourHeightRef/3600) * parseInt($(this).attr("data-timeDuration")) );					//Hauteur de l'evt
			$(this).css("top",evtPosTop).css("left",evtPosLeft).outerWidth(evtWidth,true).outerHeight(evtHeight,true);		//Applique la position et dimensions de l'evt (avec margins)
			$(this).find(".vEventLabel").outerHeight($(this).height());														//Applique la hauteur au label (pas de css "height:inherit")
			//// Infos pour l'evt suivant
			prevEvtId="#"+this.id;																							//Id de l'evt
			prevEvtDaySelector=daySelector;																					//Selecteur de l'evt
			if(timeFromDayBegin>0 && (typeof hearlierEvtTop=="undefined" || evtPosTop<hearlierEvtTop))  {hearlierEvtTop=evtPosTop;}//Scrolltop de l'agenda en fonction de l'evt le plus tôt
		});
		////	SCROLL L'AGENDA : AU DÉBUT DE LA PLAGE HORAIRE || SUR L'ÉVÉNEMENT LE PLUS TÔT DE LA SEMAINE
		let calScrollTop=Math.floor(hourHeightRef * parseInt($(this).attr("data-timeSlotBegin")));
		if(typeof hearlierEvtTop!="undefined" && hearlierEvtTop<calScrollTop)  {calScrollTop=hearlierEvtTop;}
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
.vWeekScroller								{position:relative; overflow-y:scroll; overflow-x:hidden;}			/*Partie visible de l'agenda*/
.vWeekHeader, .vWeekTable					{width:100%; border-collapse:collapse;}								/*Tableau du libellé des jour et de la grille des heures*/
.vWeekHeader td, .vWeekTable td				{padding:0px; text-align:center;}									/*Tableau du libellé des jour et de la grille des heures*/
.vWeekHeaderToday							{font-size:1.15em; color:#c00;}										/*Libellé d'aujourd'hui*/
.vWeekHeaderScrollbar						{width:15px;}														/*Width "fantome" de la scrollbar de .vWeekScroller*/
.vWeekHourLabel								{width:35px; vertical-align:top; color:#aaa; font-size:0.9em;}		/*Libellé des heures, à gauche du tableau*/
.vWeekCell									{font-size:0.1em; <?= Ctrl::$agora->skin=="white" ? "background:white;border:1px solid #dededf;" : "background:black;border:1px solid #333;" ?>}/*Cellule des heures*/
.vWeekHourNotTimeslot						{background:<?= Ctrl::$agora->skin=="white"?"#fafafa" : "#222" ?>}	/*Heures en dehors du Timeslot*/
.vWeekQuarter:hover, .vWeekQuarterSelect	{background:<?= Ctrl::$agora->skin=="white"?"#eee" : "#333" ?>;}	/*Quarts d'heure survolés/sélectionnés*/
.vWeekQuarterRedLine						{border-top:solid 1px #f00;}										/*Heure courante : ligne rouge*/
.vEventBlock								{position:absolute; min-height:20px; padding:4px;}					/*Hauteur minimum de 20px (exple: evt d'un quart d'heure)*/

/*MOBILE*/
@media screen and (max-width:1023px){
	.vWeekHourLabel				{width:18px!important; max-width:18px!important; font-weight:normal; text-align:center;}/*min & max pour forcer la taille*/
	.vWeekHeaderDay				{font-size:0.85em!important;}
	.vWeekHeaderCelebrationDay	{display:none;}
	.vEventLabel				{line-height:13px;}
}

/* IMPRESSION */
@media print{
	.vWeekScroller				{height:80%!important; max-height:80%!important;}
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