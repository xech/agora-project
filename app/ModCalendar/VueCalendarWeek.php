<?php if($tmpCal->isFirstCal==true){ ?>
<script>
/*******************************************************************************************
 *	DIMENSIONNE LES AGENDAS
 *******************************************************************************************/
function calendarDisplay(isPrint)
{
	////	HAUTEUR DES AGENDAS ET DES LIGNES D'HEURE
	if(isPrint==true)  {$(".vWeekScroller, .vWeekHeader, .vWeekTable").width(1100);}		//Print : largeur fixe (tester en 1344px !)
	$(".vWeekScroller").outerHeight( ($(".vCalVue").height()-$(".vWeekHeader").height()) );	//Height du .vWeekScroller des agendas (partie visible et scrollable des agendas)
	let weekCellHeight=$(".vWeekScroller").height() / (isPrint==true?24:12);				//Height des .vWeekCell en fonction du .vWeekScroller et la plage horaire affichée (12h/24h)
	if(weekCellHeight<28 || isPrint==true)  {weekCellHeight=28;}							//Height minimum des heures
	$(".vWeekCell").outerHeight(weekCellHeight,true);										//Height des heures (avec margins)
	$(".vWeekQuarter").outerHeight( ($(".vWeekCell").height()/4) );							//Height des 1/4 d'heures sélectionnables
	 hourHeightRef=weekCellHeight-1;														//Height de référence : -1px de margin-bottom (margins du .vWeekCell sont fusionnées via "border-collapse")

	////	LARGEUR DES JOURS (COLONNES)
	let weekCellWidth=($(".vWeekTable").width() - $(".vWeekTable .vWeekHourLabel").outerWidth()) / <?= count($periodDays) ?>;
	$(".vCalLabelWeekDays, .vWeekCell, .vEvtBlock").outerWidth(weekCellWidth,true);			//Width du label des jours et cellules des heures (avec margins)

	////	AFFICHE LES ÉVÉNEMENTS DE CHAQUE AGENDA
	if(isPrint==true)	{evtDisplay();}														//Print : Affichage direct
	else				{setTimeout(function(){ evtDisplay(); },50);}						//Sinon affiche avec un timeout (tester avec plusieurs agendas et affichage de scrollbar)
}

/*******************************************************************************************
 *	DIMENSIONNE LES EVENEMENTS
 *******************************************************************************************/
function evtDisplay()
{
	$(".vWeekScroller").each(function(){																						//Parcourt chaque agenda
		let calSelector=this;																									//Selecteur de l'agenda courant
		let calScrollTop=hourHeightRef * $(this).attr("data-timeSlotBegin");													//ScrollTop de l'agenda en fonction de la plage horaire affichée (timeslot)
		$(this).find(".vEvtBlock").each(function(){																				//Affichage de chaque Evt :
			let dayDate=$(this).attr("data-dayDate");																			//Date de l'evt
			let daySelector=".vWeekCell[data-dayDate='"+dayDate+"']:first";														//Selecteur de la 1ere cellule d'heure du jour (0:00) pour récupérer ses dimensions
			let evtPosLeft=$(daySelector).position().left;																		//Position Left de l'evt en fonction de la colonne du jour
			let evtWidth=$(daySelector).width();																				//Width de l'evt en fonction de la colonne du jour (sans margins)
			let timeFromDayBegin=$(this).attr("data-timeFromDayBegin");															//Time du début de l'evt depuis le début du jour
			let sameBeginSelector=".vEvtBlock[data-dayDate='"+dayDate+"'][data-timeFromDayBegin='"+timeFromDayBegin+"']";		//Selecteur des evts qui commencent en même temps
			let hasEvtBefore=(typeof prevEvtId!="undefined" && $(prevEvtId).attr("data-dayDate")==$(this).attr("data-dayDate"));//Verif s'il ya un précédent evt le même jour
			//// D'autres evts commencent en même temps : split l'evt
			if($(calSelector).find(sameBeginSelector).length > 1){
				evtWidth=evtWidth / $(calSelector).find(sameBeginSelector).length;												//Largeur en fonction du nb d'evt à afficher cote à cote
				evtPosLeft+=evtWidth * $(calSelector).find(sameBeginSelector).index(this);										//Décale l'evt en fonction de son rang (index) parmi les autres evts
			}
			//// Evt chevauchant un autre evt OU Evt englobé dans un autre : décale l'evt (tester les 2 cas sur le même jour)
			else if(hasEvtBefore==true  &&  ($(this).attr("data-timeBegin") < timeEndDayMax || $(this).attr("data-timeEnd") < timeEndDayMax)){
				evtWidth-=15;																									//Réduit la largeur de l'evt (pas + de 15px!)
				evtPosLeft+=15;																									//Décale d'autant sur la droite
				$(this).css("border","solid 1px #777");																			//Ajoute une bordure pour différencier les 2 evts
			}
			//// Position / dimensions de l'evt
			let evtPosTop=(hourHeightRef/3600) * timeFromDayBegin;																//Position top
			let evtHeight=(hourHeightRef/3600) * $(this).attr("data-timeDuration");												//Hauteur
			$(this).css("left",evtPosLeft).css("top",evtPosTop).outerWidth(evtWidth,true).outerHeight(evtHeight,true);			//Applique la position et dimensions du vEvtBlock (avec margins)
			$(this).find(".vEvtLabel").outerHeight($(this).height());															//Hauteur au vEvtLabel (pas de css "height:inherit")

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
	if(!isMobile()){
		let isMouseDown=quarterTimeBegin=quarterTimeEnd=null;
		$(".vWeekQuarter").on("mousedown mousemove mouseup",function(event){
			if(event.type=="mousedown"){																			//// Début de sélection : init les valeurs
				isMouseDown=true;																					// Cellule sélectionnée
				quarterDate=$(this).attr("data-dayDate");															// Jour sélectionné
				quarterTimeBegin=parseInt($(this).attr("data-timeBegin"));											// Time du début de sélection
				quarterTimeEnd=parseInt($(this).attr("data-timeEnd"));												// Time de fin de sélection
			}
			else if(event.type=="mousemove" && isMouseDown==true && quarterDate==$(this).attr("data-dayDate")){		//// Continue la sélection sur le même jour
				quarterTimeEnd=parseInt($(this).attr("data-timeEnd"));												// Update le Time de fin de sélection
				$(".vWeekQuarter[data-dayDate='"+quarterDate+"']").each(function(){									// Sélectionne/déselectionne les cellules .vWeekQuarter du jour (descend/monte la souris)
					if(quarterTimeBegin <= parseInt($(this).attr("data-timeBegin"))  &&  parseInt($(this).attr("data-timeEnd")) < quarterTimeEnd)	{$(this).addClass("vWeekQuarterSelect");}
					else																															{$(this).removeClass("vWeekQuarterSelect");}
				});
			}
			else if(event.type=="mouseup" && quarterTimeBegin < quarterTimeEnd){									//// Fin de la sélection : ouvre l'édition d'un nouvel événement !
				lightboxOpen("<?= MdlCalendarEvent::getUrlNew() ?>&_idCal="+$(this).attr("data-idCal")+"&newEvtTimeBegin="+quarterTimeBegin+"&newEvtTimeEnd="+quarterTimeEnd);
				$(".vWeekQuarter").removeClass("vWeekQuarterSelect");												// Réinit la sélection
				isMouseDown=quarterTimeBegin=quarterTimeEnd=null;													// Réinit enfin les valeurs
			}
		});
	}
});
</script>

<style>
.vCalVue									{height:100%;}
.vWeekScroller								{position:relative; overflow-y:scroll; overflow-x:hidden;}				/*Partie visible de l'agenda*/
.vWeekHeader, .vWeekTable					{width:100%; border-collapse:collapse;}									/*Tableau du libellé des jours et de la grille des heures*/
.vWeekHeaderScrollbar						{width:10px;}															/*Width "fantome" de la scrollbar de .vWeekScroller*/
.vWeekHourLabel								{width:35px; vertical-align:top; color:#aaa; font-size:0.9em;}			/*Libellé des heures, à gauche du tableau*/
.vWeekCell									{vertical-align:top; font-size:0.1em; border:1px solid; border-color:<?= Ctrl::$agora->skin=="white"?"#dededf" : "#333" ?>;}/*Cellule des heures*/
.vWeekCell .vWeekAddEvt						{display:none;}															/*Bouton d'ajout d'evt masqué par défaut (cf. mobile)*/
.vWeekCurTimeRed							{border-top:solid 1px #f00;}											/*Heure courante : ligne rouge*/
.vWeekHourNotTimeslot						{background:<?= Ctrl::$agora->skin=="white"?"#fafafa" : "#222" ?>}		/*Heures en dehors du Timeslot*/
.vWeekQuarterSelect							{background:<?= Ctrl::$agora->skin=="white"?"#ccc" : "#333" ?>;}		/*Quarts d'heure sélectionnés*/
.vEvtBlock									{position:absolute; min-height:20px; padding:4px;}						/*Hauteur minimum de 20px (exple: evt d'un quart d'heure)*/
.vEvtBlock .objMenuContextFloat				{top:4px;}																/*Surchage le menu "burger"*/

/*MOBILE*/
@media screen and (max-width:1024px){
	.vWeekHourLabel							{font-size:0.8em; font-weight:normal; text-align:center;}/*min & max pour forcer la taille*/
	.vWeekCell:hover .vWeekAddEvt			{display:block; float:right;}/*Affiche le bouton d'ajout d'evt si on sélectionne le jour*/
	.vMonthDayCelebration					{display:none;}
	.vCalLabelToday							{background-color:transparent;}
}
</style>
<?php } ?>


<div class="vCalVue">
	<!--HEADER DES JOURS : FIXE-->
	<table class="vWeekHeader">
		<tr>
			<td class="vWeekHourLabel">&nbsp;</td>
			<?php
			foreach($periodDays as $tmpDay){
				$dayLabelFormat=Req::isMobile() ? "ccc d" : "cccc d";//Jour de la semaine ("lun. 12" ou "lundi 12")
				$classToday=(date("y-m-d",$tmpDay["timeBegin"])==date("y-m-d"))  ?  "vCalLabelToday"  :  null;
				$celebrationDay=(!empty($tmpDay["celebrationDay"]))  ?  '&nbsp;<img src="app/img/calendar/celebrationDay.png" class="vMonthDayCelebration" '.Txt::tooltip($tmpDay["celebrationDay"]).'>'  :  null;
				echo '<td class="vCalLabelWeekDays '.$classToday.'">'.ucfirst(Txt::formatime($dayLabelFormat,$tmpDay["timeBegin"])).$celebrationDay.'</td>';
			}
			?>
			<td class="vWeekHeaderScrollbar">&nbsp;</td>
		</tr>
	</table>

	<!--AGENDA SCROLLABLE-->
	<div class="vWeekScroller" data-timeSlotBegin="<?= $tmpCal->timeSlotBegin ?>">
		<!--GRILLE DES HEURES-->
		<table class="vWeekTable">
		<?php
			for($tmpHour=0; $tmpHour<24; $tmpHour++){																				// BOUCLE SUR LES HEURES
				$cellClass=($tmpHour < $tmpCal->timeSlotBegin || $tmpCal->timeSlotEnd <= $tmpHour || $tmpHour==12 || $tmpHour==13)  ?  "vWeekHourNotTimeslot"  :  null;//Créneau horaire sur le "Timeslot" ?
				echo '<tr>';																										// Début de ligne des heures
					echo '<td class="vWeekHourLabel">'.$tmpHour.':00'.'</td>';														// Label des heures (colonne de gauche)
					foreach($periodDays as $tmpDate=>$tmpDay){																		// BOUCLE SUR LES JOURS
						echo '<td class="vWeekCell '.$cellClass.'" data-dayDate="'.$tmpDate.'">';									// Cellule principale : jour/heure courante
						if($tmpCal->addOrProposeEvt()){																				// Verif si l'on peut ajouter un evt
							if(Req::isMobile()){																					// AFFICHAGE MOBILE : BOUTON D'AJOUT D'EVT
								$newEvtTimeBegin=$tmpDay["timeBegin"]+(3600*$tmpHour);												// Timestamp du début de l'evt
								if(date("y:m:d H",$newEvtTimeBegin)==date("y:m:d H"))	{echo '<div class="vWeekCurTimeRed">';}		// Heure en cours : ligne rouge
								echo '<img src="app/img/plusSmall.png" class="vWeekAddEvt" onclick="lightboxOpen(\''.MdlCalendarEvent::getUrlNew().'&_idCal='.$tmpCal->_id.'&newEvtTimeBegin='.$newEvtTimeBegin.'\')" '.Txt::tooltip($tmpCal->addEventLabel).'>';
							}else{																									// AFFICHAGE DESKTOP : SÉLECTION DE CRÉNEAU HORAIRE
								for($quarter=0; $quarter<4; $quarter++){															// Sélection d'un créneau horaire par tranches de 15mn
									$timeBegin=$tmpDay["timeBegin"]+(3600*$tmpHour)+(900*$quarter);									// Timestamp du début
									$timeEnd=$timeBegin+900;																		// Timestamp de fin
									$classRedLine=($timeBegin < time() && time() < $timeEnd) ? "vWeekCurTimeRed" : null;			// Heure en cours : ligne rouge
									$quarterTooltip=$tmpCal->addEventLabel." : ".date("H:i",$timeBegin);							// Tooltip : exple "Ajouter un evt à 12:00"
									echo '<div class="vWeekQuarter noTooltip '.$classRedLine.'" '.Txt::tooltip($quarterTooltip).' data-dayDate="'.$tmpDate.'" data-timeBegin="'.$timeBegin.'" data-timeEnd="'.$timeEnd.'" data-idCal="'.$tmpCal->_id.'">&nbsp;</div>';
								}
							}
						}
						echo '</td>';
					}
				echo '</tr>';
			}
		?>
		</table>

		<!--EVENEMENTS DE LA SEMAINE (".vEvtBlock")-->
		<?php
		foreach($tmpCal->eventList as $tmpDate=>$tmpDateEvts){
			foreach($tmpDateEvts as $tmpEvt){
				$tmpEvt->containerAttributes.=' data-dayDate="'.$tmpDate.'" data-timeBegin="'.$tmpEvt->timeBegin.'" data-timeEnd="'.$tmpEvt->timeEnd.'" data-timeFromDayBegin="'.$tmpEvt->timeFromDayBegin.'" data-timeDuration="'.$tmpEvt->timeDuration.'"';
				echo $tmpEvt->divContainerContextMenu($tmpEvt->containerClass, $tmpEvt->containerAttributes, $tmpEvt->contextMenuOptions).
						'<div class="vEvtLabel" onclick="'.$tmpEvt->openVue().'" '.Txt::tooltip($tmpEvt->tooltip).'>'.$tmpEvt->title.'</div>
					 </div>';
			}
		}
		?>
	</div>
</div>