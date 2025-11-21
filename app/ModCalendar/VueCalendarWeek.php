<?php if($tmpCal->isFirstCal==true){ ?>
<script>
/********************************************************************************************************
 *	AFFICHAGE DES AGENDAS
 *******************************************************************************************/
function calendarDisplay(isPrint)
{
	////	DIMENSIONNE LES AGENDAS
	if(isPrint==true)  {$(".vWeekTable").width(1200);}																//Print : fixe le width (idem css "@media print" d'index.php)
	let vWeekScrollerHeight=$(".vCalVue").innerHeight() - $(".vWeekHeader").outerHeight();							//Calcule le height des .vWeekScroller
	$(".vWeekScroller").outerHeight(vWeekScrollerHeight);															//Applique le height
	weekCellHeight=vWeekScrollerHeight / (isPrint==true?96:48);														//Calcule le height des .vWeekCell en fonction du timeSlot affiché (24h/12h x 4)
	if(weekCellHeight<7 || isPrint==true)  {weekCellHeight=7;}														//Height minimum des .weekCell (700px max)
	$(".vWeekCell").outerHeight(weekCellHeight);																	//Applique le height
	let weekCellWidth=($(".vWeekTable").width() - $(".vWeekHourLabel").outerWidth()) / <?= count($periodDays) ?>;	//Calcule le width des jours/colonnes
	$(".vCalLabelDays, .vWeekCell, .vEvtBlock").outerWidth(weekCellWidth,true);										//Applique le width
	calScrollTop=<?= $tmpCal->timeSlotBegin ?> * weekCellHeight * 4;												//Calcule le Scrolltop en fonction du timeslotbegin
	$(".vWeekScroller").scrollTop(calScrollTop);																	//Scrolltop de l'agenda

	////	DIMENSIONNE/POSITIONNE LES EVTS DE CHAQUE AGENDA
	$(".vWeekScroller").each(function(){																										//Parcourt chaque agenda
		let calSelector=this;																													//Selecteur de l'agenda courant
		let evtBlockList=$(this).find(".vEvtBlock").sort(function(a,b){ return $(a).attr("data-timeBegin")-$(b).attr("data-timeBegin") });		//Tri les evts de l'agenda par timeBegin (cf. evtDayIndex)
		evtBlockList.each(function(){																											//Parcourt chaque Evt
			let dayYmd=$(this).attr("data-dayYmd");																								//Date à laquelle l'evt est affiché
			let dayFirstCell=".vWeekCell[data-dayYmd='"+dayYmd+"']:first";																		//Selecteur de la 1ere cellule (0:00) du jour
			let evtWidth  =$(dayFirstCell).width();																								//Width de l'evt (cf. width jour)
			let evtPosLeft=$(dayFirstCell).position().left;																						//Position Left de l'evt (cf. position left du jour)
			let evtDayIndex=$(evtBlockList).filter("[data-dayYmd='"+dayYmd+"']").index(this);													//Index/ordre de l'evt parmi les autres evt du jour
			let evtSameTime=$(calSelector).find(".vEvtBlock[data-dayYmd='"+dayYmd+"'][data-timeBegin='"+$(this).attr("data-timeBegin")+"']");	//Evts qui commencent en même temps
			//// D'autres evts commencent en même temps : split l'evt
			if(evtSameTime.length > 1){
				evtWidth=evtWidth / evtSameTime.length;																							//Largeur en fonction du nb d'evt à afficher cote à cote
				evtPosLeft+=evtWidth * evtSameTime.index(this);																					//Décale l'evt en fonction de son rang (index) parmi les autres evts
			}
			//// Evt sur le même créneau qu'un autre evt : superpose/décale l'evt
			else if(evtDayIndex > 0  && (prevEvtTimeEnd > $(this).attr("data-timeBegin") || prevEvtTimeEnd > $(this).attr("data-timeEnd"))){
				$(this).addClass("vEvtBlockSuperposed");																						//Bordure accentuée et z-index
				evtWidth-=15;																													//Réduit la largeur de l'evt de 15px
				evtPosLeft+=15;																													//Décale sur la droite
			}
			//// Position / dimensions de l'evt
			let evtHeight=(weekCellHeight/900) * $(this).attr("data-timeDuration");																//Hauteur de l'evt
			let evtPosTop=(weekCellHeight/900) * $(this).attr("data-timeSinceDayBegin");														//Position top (900s=15mn)
			if($(dayFirstCell).attr("data-timeChangeSummer"))		{evtPosTop+=(weekCellHeight*4);}											//Journée de changement en heure d'été : décale d'une heure
			else if($(dayFirstCell).attr("data-timeChangeWinter"))	{evtPosTop-=(weekCellHeight*4);}											//Idem pour l'heure d'hiver
			$(this).css("top",evtPosTop).css("left",evtPosLeft).outerWidth(evtWidth).outerHeight(evtHeight-1);									//Applique les position et dimensions de l'evt (height-1 pour les distinguer)
			$(this).find(".vEvtLabel").outerHeight($(this).height());																			//Hauteur au vEvtLabel (pas de css "height:inherit")
			//// Update de variables
			if($(this).attr("data-timeSinceDayBegin") > 0 && evtPosTop < calScrollTop)  {$(calSelector).scrollTop(evtPosTop);}					//Scrolltop de l'agenda en fonction de l'evt le plus tôt
			if(evtDayIndex==0 || prevEvtTimeEnd < $(this).attr("data-timeEnd"))  {prevEvtTimeEnd=$(this).attr("data-timeEnd");}					//"prevEvtTimeEnd" pour l'evt suivant : 1er evt du jour ou timeEnd supérieur
		});
	});

	////	DRAGGABLE DES ÉVÉNEMENTS
	evtIsDragged=false;
	$(".vEvtBlock[data-isDraggable='true']").draggable({			//Evts avec isDraggable à true
		containment:"parent",										//Cadre dans lequel le draggable est réalisé (laisser à "parent")
		grid:[$(".vWeekCell:first").width()+1, weekCellHeight],		//Grille en fonction du width des jours (+1 de border) et créneaux de 15mn
		delay:300,													//Temps avant d'enclencher le Draggable (evite les mauvaises manips)
		opacity:0.8,												//Opacifie lors du Draggable
		zIndex:100,													//Positionne au dessus des autres evt
		scroll:false,												//Désactive le scroll dans le .vWeekScroller (pb avec "drag:function()" ci-dessous)
		//// Début du draggable
		start:function(event,ui){
			evtIsDragged=true;										//Evt en cours de déplacement
			dragCellRef=null;										//.vWeekCell de référence (créneaux de 15mn)
			draggedEvt=this;										//Evt déplacé (this)
			evtStartLeft=ui.position.left;							//Position left de départ de l'evt
			evtStartTop =ui.position.top;							//Position top
			evtStartDate=$(this).find(".vEvtLabelDate").html();		//LabelDate d'origine
		},
		//// Durant le Draggable
		drag:function(event,ui){
			let evtTmptHM=Number.parseFloat((ui.position.top / (weekCellHeight * 4))).toFixed(2);	//Heure flottante à 2 décimales	(Ex: 9:45 => 9,75)
			let evtTmpH=Math.floor(evtTmptHM);														//Heure "integer"	(9,75 => 9)
			let evtTmpM=Math.round((evtTmptHM-evtTmpH) * 60);										//Minutes décimales (0,75 => 45)
			let evtLabelDate=evtTmpH+":"+String(evtTmpM).padStart(2,'0');							//Label final
			$(draggedEvt).find(".vEvtLabel").addClass("vEvtBlockMoved");							//Evt en cours de déplacement : cursor "move" (surcharge celui de .vEvtLabel et "onclick")
			$(draggedEvt).find(".vEvtLabelDate").html('<b>'+evtLabelDate+'</b>');					//Affiche l'heure temporaire dans le vEvtLabelDate
			$(".tooltipster-box").hide();															//Masque le tooltip durant le déplacement
		},
		//// Fin du Draggable
		stop:function(event,ui){
			//// Récupère la .vWeekCell dont la position correspond au .vEvtBlock courant
			$(this).parent().find(".vWeekCell").each(function(){
				let diffTop =ui.position.top  - $(this).position().top;								//Diff de position top entre l'evt et la .vWeekCell
				let diffLeft=ui.position.left - $(this).position().left;							//Diff de position left
				if(Math.abs(diffTop) < 2 && Math.abs(diffLeft) <= ($(".vWeekCell").width()/2))		//.vWeekCell correspond avec la nouvelle position de l'evt (tester 2 evt qui commencent en même tps)
					{dragCellRef=this;  return false;}												//Enregistre la .vWeekCell de référence temporelle et sort de la boucle
			});
			//// Confirme le déplacement de l'evt et enregistre via Ajax
			if(dragCellRef!==null){
				let confirmParams={
					content:"",
					title:"<?= Txt::trad("CALENDAR_evtChangeTime") ?> "+$(dragCellRef).attr("data-cellLabelBegin")+" ?",									//Label du confirm avec la nouvelle date/heure
					buttons:{																																//Boutons "reject" / "accept"
						reject:{text:labelConfirmCancel, btnClass:"btn-default", action:function(){															//Confirmation rejetée :
							$(draggedEvt).animate({top:evtStartTop,left:evtStartLeft},100);																	//Replace l'evt à la date d'origine
							$(draggedEvt).find(".vEvtLabelDate").html(evtStartDate);																		//Affiche LabelDate d'origine
						}},
						accept:{text:labelConfirm, btnClass:"btn-green", action:function(){																	//Confirmation acceptée :
							let evtTypeId=$(draggedEvt).attr("data-typeId");																				//TypeId de l'evt
							let ajaxUrl="?ctrl=calendar&action=EvtChangeTime&newTimeBegin="+$(dragCellRef).attr("data-cellTimeBegin")+"&typeId="+evtTypeId;	//Url d'update de l'evt
							$.ajax({url:ajaxUrl,dataType:"json"}).done(function(result){																	//Lance la requête ajax
								if(result.error)  {notify("Write access","error");}																			//Update non enregistré
								else if(result.changed){																									//Nouvelle date enregistrée :
									$(".vEvtBlock[data-typeId='"+evtTypeId+"']").each(function(){															//Parcourt chaque instance de l'evt dans chaque agenda affiché
										for(var keyAttr in result.attributes)  {$(this).attr("data-"+keyAttr, result.attributes[keyAttr]);}					//Update les attributs de l'evt : timeBegin, timeEnd, etc
										$(this).find(".vEvtLabelDate").html(result.evtLabelDate);															//Update le label de la date 
										$(this).find(".vEvtLabel").tooltipUpdate(result.tooltip);															//Update le tooltip
									});
									notify("<?= Txt::trad("CALENDAR_evtChangeTimeConfirmed") ?>","success");												//Affiche une notif
									calendarDisplay();																										//Rafraichit l'affichage de l'agenda !
								}
							});
						}},
					}
				}
				//// Lance le Confirm (paramétrage par défaut + spécifique)
				$.confirm(Object.assign(confirmParamsDefault,confirmParams));
			}
			//// Fin du Drag : réinit avec timout
			setTimeout(function(){
				$(draggedEvt).find(".vEvtLabel").removeClass("vEvtBlockMoved");
				evtIsDragged=false;
			},300);										
		}
	});
}

/******************************************************************************************
 *	PROPOSER/AJOUTER UN EVT EN SELECTIONNANT UN CRÉNEAU HORAIRE
 ******************************************************************************************/
<?php if($tmpCal->affectationAddRight() && Req::isMobile()==false){ ?>
ready(function(){
	let isMouseDown=startTimeBegin=startTimeEnd=null;
	$(".vWeekCell").on("mousedown mousemove mouseup",function(event){
		if(event.type=="mousedown"){																		//// Début de sélection : init les valeurs
			isMouseDown=true;																				// Debut de sélection
			startDayYmd=$(this).attr("data-dayYmd");														// Jour Ymd
			startTimeBegin=parseInt($(this).attr("data-cellTimeBegin"));									// Time du début de sélection
			startTimeEnd  =parseInt($(this).attr("data-cellTimeEnd"));										// Time de fin de sélection
			$(this).addClass("lineSelect");																	// Sélection du .vWeekCell
		}
		else if(event.type=="mousemove" && isMouseDown==true && startDayYmd==$(this).attr("data-dayYmd")){	//// Continue la sélection sur le même jour
			startTimeEnd=parseInt($(this).attr("data-cellTimeEnd"));										// Update le Time de fin de sélection
			$(".vWeekCell[data-dayYmd='"+startDayYmd+"']").each(function(){									// Sélection/déselection des .vWeekCell (descend/monte la souris) : ajoute/enlève .lineSelect
				if(startTimeBegin <= parseInt($(this).attr("data-cellTimeBegin"))  &&  parseInt($(this).attr("data-cellTimeEnd")) <= startTimeEnd)	{$(this).addClass("lineSelect");}
				else																																{$(this).removeClass("lineSelect");}
			});
		}
		else if(event.type=="mouseup" && startTimeBegin < startTimeEnd){									//// Fin de sélection : ouvre l'édition d'un nouvel événement !
			lightboxOpen("<?= MdlCalendarEvent::getUrlNew() ?>&_idCal="+$(this).attr("data-idCal")+"&newEvtTimeBegin="+startTimeBegin+"&newEvtTimeEnd="+startTimeEnd);
			$(".vWeekCell").removeClass("lineSelect");														// Réinit .lineSelect
			isMouseDown=startTimeBegin=startTimeEnd=null;													// Réinit enfin les valeurs
		}
	});
});
<?php } ?>
</script>


<style>
.vCalVue							{height:100%;}
.vWeekScroller						{position:relative; overflow-y:scroll; overflow-x:hidden;}				/*Partie visible de l'agenda*/
.vWeekHeader, .vWeekTable			{width:100%; border-collapse:collapse;}									/*Tableau du libellé des jours et de la grille des heures*/
.vCalLabelDays span					{margin-left:5px;}														/*Nb du jour du mois*/
.vPublicHoliday						{margin-left:10px;}														/*Icone du jour férié*/
.vWeekHeaderScrollbar				{width:12px;}															/*Width "fantome" de la scrollbar de .vWeekScroller*/
.vWeekHourLabel						{width:35px; vertical-align:top; color:#888; font-size:0.9rem;}			/*Libellé des heures sur la 1ere colonne du tableau*/
.vWeekCell							{vertical-align:top; font-size:0.1rem; padding:0px; border:0px solid <?= Ctrl::$agora->skin=="white"?"#dededf" : "#333" ?>; border-left-width:1px;}/*Cellule des créneaux de 15mn*/
.vWeekCell[data-cellMinutes='00']	{border-top-width:1px;}													/*Cellules du début des heures avec border-top*/
.vWeekCellRedLine					{border-top:solid 1px #f00;}											/*Heure courante : ligne rouge*/
.vWeekCell .vMobileAddEvt			{display:none;}															/*Bouton d'ajout d'evt masqué par défaut (cf. mobile)*/
.vLineNotTimeSlot					{background:<?= Ctrl::$agora->skin=="white"?"#fbfbfb" : "#222" ?>}	/*Heures en dehors du TimeSlot*/
.vEvtBlock							{position:absolute;}													/*Tester un evt de 15mn*/
.vEvtBlockSuperposed				{box-shadow:0px 0px 3px white;}											/*Evt superposé*/
.vEvtBlock .objMenuContextFloat		{top:4px;}																/*Replace le menu "burger"*/
.vEvtBlockMoved						{cursor:move!important;}												/*Evt en cours de déplacement*/
.vEvtLabelDate						{margin-top:2px;}														/*Label de l'heure*/
.vEvtLabelDate b					{margin-top:20px; font-size:1.2rem;}									/*Label de l'heure en cours de déplacement*/

/*AFFICHAGE SMARTPHONE + TABLET*/
@media screen and (max-width:1024px){
	.vWeekHourLabel						{font-size:0.8rem; font-weight:normal; text-align:center;}
	.vWeekCell							{position:relative;}
	.vWeekCell:active .vMobileAddEvt	{display:block; position:absolute; top:0px; right:0px; padding:7px;}/*Bouton addEvt si on sélectionne le jour*/
}
</style>
<?php } ?>


<div class="vCalVue">
	<!--HEADER DES JOURS : FIXE-->
	<table class="vWeekHeader">
		<tr>
			<td class="vWeekHourLabel">&nbsp;</td>
			<?php
			foreach($periodDays as $dayYmd=>$tmpDay){
				echo '<td class="vCalLabelDays" '.Txt::tooltip($tmpDay["publicHoliday"]).'>'.													//Jours de la semaine :
						Txt::timeLabel($tmpDay["dayTimeBegin"],'ccc').																			//Label du jour
						'<span '.($dayYmd==date('Y-m-d')?'class="circleNb"':null).'>'.date("j",$tmpDay["dayTimeBegin"]).'</span>'.				//Jour du mois (.circleNb si "today")
						(!empty($tmpDay["publicHoliday"]) ? '<img src="app/img/calendar/publicHoliday.png" class="vPublicHoliday">' : null).	//Jour férié
					 '</td>';
			}
			?>
			<td class="vWeekHeaderScrollbar">&nbsp;</td>
		</tr>
	</table>

	<!--AGENDA SCROLLABLE-->
	<div class="vWeekScroller">
		<table class="vWeekTable">
		<?php
			for($tmp15mn=0; $tmp15mn<96; $tmp15mn++){																			//BOUCLE SUR DES CRÉNEAUX DE 15MN (96 dans la journée)
				$lineNotTimeslot=($tmp15mn < ($tmpCal->timeSlotBegin*4) || ($tmpCal->timeSlotEnd*4) <= $tmp15mn)  ?  'class="vLineNotTimeSlot"'  :  null;//Créneau horaire sur le "TimeSlot" ?
				echo '<tr '.$lineNotTimeslot.'>';																				//Début de ligne des heures
					if($tmp15mn % 4===0)  {echo '<td class="vWeekHourLabel" rowspan="4">'.($tmp15mn/4).':00'.'</td>';}			//Label des heures sur la 1ere colonne (multiple de 4 via l'operateur modulo '%')
					foreach($periodDays as $dayYmd=>$tmpDay){																	//BOUCLE SUR LES JOURS
						$cellTimeBegin=$tmpDay["dayTimeBegin"]+($tmp15mn*900);													//Timestamp du début du créneau de 15mn
						$cellTimeEnd=$cellTimeBegin+900;																		//Timestamp de fin
						$cellMinutes=date("i",$cellTimeBegin);																	//Minutes de l'heure ("00" à "59")
						$classRedLine=($cellTimeBegin < time() && time() < $cellTimeEnd) ? "vWeekCellRedLine" : null;			//Heure en cours : ligne rouge
						$cellAttributes='data-cellLabelBegin="'.Txt::dateLabel($cellTimeBegin,"labelFull").'" data-cellTimeBegin="'.$cellTimeBegin.'" data-cellTimeEnd="'.$cellTimeEnd.'" data-cellMinutes="'.$cellMinutes.'" data-dayYmd="'.$dayYmd.'" data-idCal="'.$tmpCal->_id.'" data-timeChangeSummer="'.$tmpDay["timeChangeSummer"].'" data-timeChangeWinter="'.$tmpDay["timeChangeWinter"].'" ';
						$mobileAddEvt=(Req::isMobile() && $cellMinutes=="00") ? '<div class="vMobileAddEvt" onclick="lightboxOpen(\''.MdlCalendarEvent::getUrlNew().'&_idCal='.$tmpCal->_id.'&newEvtTimeBegin='.$cellTimeBegin.'\')"><img src="app/img/plus.png"></div>'  :  null;
						echo '<td class="vWeekCell '.$classRedLine.'" '.$cellAttributes.'>'.$mobileAddEvt.'</td>';	//Affiche la cellule
					}
				echo '</tr>';
			}
		?>
		</table>

		<!--EVENEMENTS DE LA SEMAINE-->
		<?php
		foreach($tmpCal->evtListDays as $evtListDay){
			foreach($evtListDay as $tmpEvt){
				echo $tmpEvt->objContainerMenu("vEvtBlock",$tmpEvt->evtAttributes,$tmpEvt->contextMenuOptions).
						'<div class="vEvtLabel" onclick="if(evtIsDragged==false)'.$tmpEvt->openVue().'" '.Txt::tooltip($tmpEvt->tooltip).'>'.
							$tmpEvt->title.'<div class="vEvtLabelDate">'.Txt::dateLabel($tmpEvt->timeBegin,"mini",$tmpEvt->timeEnd).'</div>
						</div>
					 </div>';
			}
		}
		?>
	</div>
</div>