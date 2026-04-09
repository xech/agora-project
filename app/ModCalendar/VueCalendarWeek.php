<?php if($tmpCal->isFirstCal==true){ ?>
	<script>
	/******************************************************************************************
	 *	AFFICHAGE DES AGENDAS
	 ******************************************************************************************/
	function calendarDisplay(isPrint)
	{
		////	DIMENSIONNE LES AGENDAS
		if(isPrint==true)  {$(".vWeekTable").width(1200);}										//Print : .vWeekTable à largeur fixe (cf "@media print" en page principale)
		weekHourWidth=$(".vWeekHourLabel").outerWidth();										//Width des .vWeekHourLabel
		vWeekScrollerHeight=$(".vCalVue").innerHeight() - $(".vWeekHeader").outerHeight();		//Height des .vWeekScroller
		$(".vWeekScroller").outerHeight(vWeekScrollerHeight);									//Applique le height
		weekCellHeight=vWeekScrollerHeight / (isPrint==true?96:48);								//Height des .vWeekCell en fonction du timeSlot affiché (24h/12h x 4)
		if(weekCellHeight<7 || isPrint==true)  {weekCellHeight=7;}								//Height minimum des .weekCell (700px max)
		$(".vWeekCell").outerHeight(weekCellHeight);											//Applique le height
		weekCellWidth=($(".vWeekTable").width() - weekHourWidth) / <?= count($periodDays) ?>;	//Width des .vWeekCell
		$(".vCalLabelDays, .vWeekCell, .vEvtBlock").outerWidth(weekCellWidth);					//Applique le width
		calScrollTop=<?= $tmpCal->timeSlotBegin ?> * weekCellHeight * 4;						//Calcule le Scrolltop en fonction du timeslotbegin
		$(".vWeekScroller").scrollTop(calScrollTop);											//Scrolltop de l'agenda

		////	DIMENSIONNE & POSITIONNE LES EVTS DE CHAQUE AGENDA
		$(".vWeekScroller").each(function(){																													//Parcourt chaque agenda
			let calSelector=this;																																//Selecteur de l'agenda courant
			let evtBlockList=$(this).find(".vEvtBlock").sort(function(a,b){ return $(a).attr("data-evtTimeBegin")-$(b).attr("data-evtTimeBegin") });			//Tri les evts de l'agenda par timeBegin (cf. evtDayIndex)
			evtBlockList.each(function(){																														//Parcourt chaque Evt
				let dayYmd=this.getAttribute("data-evtDayYmd");																									//Date à laquelle l'evt est affiché
				let dayFirstCell=".vWeekCell[data-cellDayYmd='"+dayYmd+"']:first";																				//Selecteur de la 1ere cellule (0:00) du jour
				let evtWidth  =$(dayFirstCell).width();																											//Width de l'evt (cf. width jour)
				let evtPosX=$(dayFirstCell).position().left;																									//Position Left de l'evt (cf. position left du jour)
				let evtDayIndex=$(evtBlockList).filter("[data-evtDayYmd='"+dayYmd+"']").index(this);															//Index/ordre de l'evt parmi les autres evt du jour
				let evtSameTime=$(calSelector).find(".vEvtBlock[data-evtDayYmd='"+dayYmd+"'][data-evtTimeBegin='"+this.getAttribute("data-evtTimeBegin")+"']");	//Evts qui commencent en même temps
				//// D'autres evts commencent en même temps : split l'evt
				if(evtSameTime.length > 1){
					evtWidth=evtWidth / evtSameTime.length;																										//Largeur en fonction du nb d'evt à afficher cote à cote
					evtPosX+=evtWidth * evtSameTime.index(this);																								//Décale l'evt en fonction de son rang (index) parmi les autres evts
				}
				//// Evt sur le même créneau qu'un autre evt : superpose/décale l'evt
				else if(evtDayIndex > 0  && (prevEvtTimeEnd > this.getAttribute("data-evtTimeBegin") || prevEvtTimeEnd > this.getAttribute("data-evtTimeEnd"))){
					$(this).addClass("vEvtBlockSuperposed");																									//Bordure accentuée et z-index
					evtWidth-=20;																																//Réduit la largeur de l'evt de 20px
					evtPosX+=20;																																//Décale d'autant sur la droite
				}
				//// Position / dimensions de l'evt
				let evtHeight=(weekCellHeight/900) * this.getAttribute("data-evtTimeDuration");																	//Hauteur de l'evt
				let evtPosY=(weekCellHeight/900) * this.getAttribute("data-evtTimeSinceDayBegin");																//Position top (900s=15mn)
				if($(dayFirstCell).attr("data-cellSummerChange"))		{evtPosY+=(weekCellHeight*4);}															//Journée de changement en heure d'été : décale d'une heure
				else if($(dayFirstCell).attr("data-cellWinterChange"))	{evtPosY-=(weekCellHeight*4);}															//Idem pour l'heure d'hiver
				$(this).css("top",evtPosY).css("left",evtPosX).outerWidth(evtWidth).outerHeight(evtHeight-1);													//Applique les position et dimensions (height-1 pour les distinguer)
				$(this).find(".vEvtLabel").outerHeight($(this).height());																						//Hauteur au vEvtLabel (pas de css "height:inherit")
				//// Update de variables
				if(this.getAttribute("data-evtTimeSinceDayBegin") > 0 && evtPosY < calScrollTop)  {$(calSelector).scrollTop(evtPosY);}							//Scrolltop de l'agenda en fonction de l'evt le plus tôt
				if(evtDayIndex==0 || prevEvtTimeEnd < this.getAttribute("data-evtTimeEnd"))  {prevEvtTimeEnd=this.getAttribute("data-evtTimeEnd");}				//"prevEvtTimeEnd" pour l'evt suivant : 1er evt du jour ou timeEnd supérieur
			});
		});
	}
	
	/******************************************************************************************
	 * DRAGGABLE DES ÉVÉNEMENTS (POSITION ABSOLUTE)
	 ******************************************************************************************/
	function evtDraggable()
	{
		evtIsDragged=false;
		gridWidth=$(".vWeekCell").outerWidth();
		gridHeight=$(".vWeekCell").outerHeight();
		interact(".vEvtBlock[data-evtIsDraggable='true']").draggable({
			////	Config du Draggable
			transform:'none',															//Repositionne via left/top plutôt que transform:translate()
			modifiers:[																	//Zone draggable :
				interact.modifiers.restrictRect({ restriction:".vCalVue" }),			//Restriction de la zone sur la .vCalVue parent
				interact.modifiers.snap({												//Snap/Accroche :
					offset:'parent',													//Snap basé sur le parent
					relativePoints:[{ x:parseInt(weekHourWidth), y:0 }],				//Snap sur les .vWeekCell et décalé par rapport à .vWeekHourLabel
					targets:[ interact.snappers.grid({ x:gridWidth, y:gridHeight }) ]	//Grid à la dimension des .vWeekCell
				})
			],
			////	Gestion des événements
			listeners:{
				start(event){
					//// Init
					evtIsDragged=true;
					let evtDragged=event.target;
					evtStartX=parseFloat(evtDragged.style.left);			//Position left de départ (cf button "reject")
					evtStartY=parseFloat(evtDragged.style.top);				//Position top (idem)
					evtStartDate=$(evtDragged).find(".vEvtLabelHM").html();	//LabelDate (idem)
				},
				move(event){
					//// Repositionne l'evt
					let evtDragged=event.target;
					let mouseMoveX=(Math.abs(event.dx) > (gridWidth/2)) ? event.dx : 0;					//Mouvement X relative à la souris : corrigé pour les événements splités (tester 2 evts avec le meme dateBegin)
					let evtPosX=(parseFloat(evtDragged.style.left) || 0) + mouseMoveX;					//Nouvelle position X relative à la souris
					let evtPosY=(parseFloat(evtDragged.style.top) || 0) + event.dy;						//Nouvelle position Y (idem)
					if(evtPosX < weekHourWidth)  {evtPosX=weekHourWidth;}								//Corrige si besoin le décalage sur mobile
					evtDragged.style.left=evtPosX+'px';													//Repositionne en X
					evtDragged.style.top =evtPosY+'px';													//Repositionne en Y
					//// Affichage durant le drag & drop
					let hourHeight=(gridHeight * 4);													//Hauteur des heures pleines
					let tmpHM=parseFloat(evtPosY / hourHeight).toFixed(2);								//Heure/Minutes en décimales (ex: 9.75 pour 9:45)
					let tmpHour=Math.floor(tmpHM);														//Heure pleine	(ex: 9.75 => 9)
					let tmpMinutes=Math.round((tmpHM - tmpHour) * 60);									//Minutes décimales (ex: 9.75 => 0.75 => 45)
					let tmpLabel=tmpHour+":"+String(tmpMinutes).padStart(2,'0');						//Label temporaire H:M (minutes sur 2 digits)
					$(evtDragged).find(".vEvtLabelHM").html('<b class="vEvtDragged">'+tmpLabel+'</b>');	//Affiche le label dans .vEvtLabelHM
					$(evtDragged).addClass("vEvtBlockMoved");											//Evt en cours de déplacement
					$(".tooltipster-base").hide();														//Masque le tooltip durant le déplacement
				},
				end(event){
					//// Evt déplacé
					let evtDragged=event.target;
					let evtPosX=parseFloat(evtDragged.style.left);
					let evtPosY=parseFloat(evtDragged.style.top);
					if(parseInt(evtStartX)!=parseInt(evtPosX) || parseInt(evtStartY)!=parseInt(evtPosY)){
						//// Récupère la .vWeekCell dont la position est la + proche du evtDragged
						let weekCellRef=null;
						$(evtDragged).parent().find(".vWeekCell").each(function(){
							let diffY=(evtPosY-$(this).position().top);
							let diffX=(evtPosX-$(this).position().left);
							if(diffY <= 3  && diffX <= (gridWidth/2))	{weekCellRef=this;  return false;}
						});
						//// Confirme le déplacement de l'evt
						if(weekCellRef!==null){
							let confirmParams={
								title:"<?= Txt::trad("CALENDAR_evtChangeTime") ?>",
								content:'<span class="vEvtDateLabelOld"> '+$(evtDragged).attr("data-evtDateLabel")+'</span> <img src="app/img/arrowRight.png"> '+$(weekCellRef).attr("data-cellDateLabel"),
								buttons:{
									//// Confirmation rejetée
									reject:{
										text:labelConfirmCancel,
										btnClass:"btn-default",
										action:function(){
											$(evtDragged).animate({top:evtStartY,left:evtStartX},100);	//Remet l'evt à sa place d'origine
											$(evtDragged).find(".vEvtLabelHM").html(evtStartDate);		//Remet le label d'origine
										}
									},
									//// Confirmation acceptée
									accept:{
										text:labelConfirm,
										btnClass:"btn-green",
										action:function(){																													
											let evtTypeId=$(evtDragged).attr("data-typeId");
											let evtNewTimeBegin=$(weekCellRef).attr("data-cellTimeBegin");
											let ajaxUrl="?ctrl=calendar&action=EvtChangeTime&evtNewTimeBegin="+evtNewTimeBegin+"&typeId="+evtTypeId;
											$.ajax({url:ajaxUrl,dataType:"json"}).done(function(result){
												 if(result.changed){																						//Update Ok :
													$(".vEvtBlock[data-typeId='"+evtTypeId+"']").each(function(){											//Parcourt chaque instance de l'evt pour chaque agenda affiché !
														for(var keyAttr in result.attributes)  {this.setAttribute(keyAttr, result.attributes[keyAttr]);}	//Update les attributs de l'evt : timeBegin, timeEnd, etc
														$(this).find(".vEvtLabelHM").html(result.evtLabelDate);												//Update le label de la date 
														$(this).find(".vEvtLabel").tooltipUpdate(result.tooltip);											//Update le tooltip
													});
													notify("<?= Txt::trad("CALENDAR_evtChangeTimeConfirmed") ?>","success");	//Notif de confirmation
													calendarDisplay();															//Reload l'affichage des agendas
												}
												if(result.error)  {notify("Update error");}
											});
										}
									}
								}
							}
							//// Lance le Confirm (paramétrage par défaut + spécifique)
							$.confirm(Object.assign(confirmParamsDefault,confirmParams));
						}
					}
					//// Fin du Drag avec timout (evite le onclick ou swipe)
					setTimeout(function(){
						$(evtDragged).removeClass("vEvtBlockMoved");
						evtIsDragged=false;
					},300);
				}
			}
		});
	}

	/******************************************************************************************
	 *	AJOUTER/PROPOSER UN EVT : SELECTION D'UN CRÉNEAU HORAIRE
	 ******************************************************************************************/
	ready(function(){
		if(isMobile()==false){
			let isMouseDown=startTimeBegin=startTimeEnd=null;
			$(".vWeekCellEvtAdd").on("mousedown mousemove mouseup",function(event){
				if(event.type=="mousedown"){																				//// Début de sélection : init les valeurs
					isMouseDown=true;																						// Debut de sélection
					startDayYmd=this.getAttribute("data-cellDayYmd");														// Jour Ymd
					startTimeBegin=parseInt(this.getAttribute("data-cellTimeBegin"));										// Time du début de sélection
					startTimeEnd  =parseInt(this.getAttribute("data-cellTimeEnd"));											// Time de fin de sélection
					$(this).addClass("lineSelect");																			// Sélection du .vWeekCell
				}
				else if(event.type=="mousemove" && isMouseDown==true && startDayYmd==this.getAttribute("data-cellDayYmd")){	//// Continue la sélection sur le même jour
					startTimeEnd=parseInt(this.getAttribute("data-cellTimeEnd"));											// Update le Time de fin de sélection
					$(".vWeekCell[data-cellDayYmd='"+startDayYmd+"']").each(function(){										// Sélection/déselection des .vWeekCell (descend/monte la souris : ajoute/enlève .lineSelect)
						if(startTimeBegin <= parseInt(this.getAttribute("data-cellTimeBegin"))  &&  parseInt(this.getAttribute("data-cellTimeEnd")) <= startTimeEnd)	{$(this).addClass("lineSelect");}
						else																																			{$(this).removeClass("lineSelect");}
					});
				}
				else if(event.type=="mouseup" && startTimeBegin < startTimeEnd){											//// Fin de sélection : ouvre l'édition d'un nouvel événement !
					lightboxOpen("<?= $getUrlNewEvt ?>&_idCal="+this.getAttribute("data-cellIdCal")+"&newEvtTimeBegin="+startTimeBegin+"&newEvtTimeEnd="+startTimeEnd);
					$(".vWeekCell").removeClass("lineSelect");																// Réinit .lineSelect
					isMouseDown=startTimeBegin=startTimeEnd=null;															// Réinit enfin les valeurs
				}
			});
		}
	});
	</script>


	<style>
	.vCalVue									{height:100%;}
	.vWeekScroller								{position:relative; overflow-y:scroll; overflow-x:hidden;}				/*Partie visible de l'agenda*/
	.vWeekHeader, .vWeekTable					{width:100%; border-collapse:collapse;}									/*Tableau du libellé des jours et de la grille des heures*/
	.vCalLabelDays span							{margin-left:5px;}														/*Nb du jour du mois*/
	.vPublicHoliday								{margin-left:10px;}														/*Icone du jour férié*/
	.vWeekHeaderScrollbar						{width:12px;}															/*Width "fantome" de la scrollbar de .vWeekScroller*/
	.vWeekHourLabel								{width:40px; text-align:center; vertical-align:top; color:#888; font-size:0.9rem;}			  							/*Libellé des heures : 1ere colonne du tableau*/
	.vWeekCell									{padding:0px; border:0px solid <?= Ctrl::$agora->skin=='white'?'#dededf':'#3c3c3c' ?>; border-left-width:1px;}	/*Cellules du tableau : créneaux de 15mn*/
	.vWeekTable tr:nth-child(4n+1) .vWeekCell 	{border-top-width:1px;}													/*borter-top toutes les 4 lignes (1er 1/4 d'heure)*/
	.vWeekCellRedLine							{border-top:solid 1px #f00;}										   /*Heure courante : border-top rouge*/
	.vLineNotTimeSlot							{background:<?= Ctrl::$agora->skin=="white"?"#fbfbfb" : "#222" ?>} /*Heures en dehors du TimeSlot*/
	.vEvtBlock									{position:absolute;}													/*Tester un evt de 15mn*/
	.vEvtBlockSuperposed						{box-shadow:0px 0px 3px white;}											/*Evt superposé*/
	.vEvtBlock .menuContextLaunchFloat			{top:3px; right:2px;}													/*Décale le menu "burger"*/
	.vEvtLabel									{font-size:0.9rem;}														/*Label de l'evt*/
	.vEvtLabelHM								{margin-top:2px;}														/*Label de l'heure*/
	.vEvtDragged								{font-size:1.2rem; line-height:20px;}									/*Label de l'heure en cours de déplacement*/
	.vEvtDateLabelOld							{opacity:0.75;}
	.vMobileEvtAdd								{display:none;}

	/*AFFICHAGE RESPONSIVE*/
	@media screen and (max-width:1200px){
		.vWeekHourLabel, .vEvtLabel				{font-size:0.8rem;}
		.vWeekHourLabel							{width:25px;}
		.vEvtDragged							{font-size:1rem;}
		.vEvtDateLabelOld						{display:none;}
		.vWeekCell:active .vMobileEvtAdd		{display:block; position:absolute}/*affiche si on selectionne la ligne*/
		.vWeekHourLabel00, .vEvtLabelHM			{display:none;}
		.vEvtBlock 								{touch-action:none;}/*Pas de  scroll de la page durant le drag and drop*/
	}
	</style>
<?php } ?>


<div class="vCalVue">
	<!--HEADER DES JOURS : FIXE-->
	<table class="vWeekHeader">
		<tr>
			<td class="vWeekHourLabel">&nbsp;</td>
			<!--JOURS DE LA SEMAINE-->
			<?php foreach($periodDays as $dayYmd=>$tmpDay){ ?>
				<td class="vCalLabelDays" <?= Txt::tooltip($tmpDay["publicHoliday"]) ?> >
					<!--LABEL DU JOUR + JOUR DU MOIS + JOUR FERIE-->
					<?= Txt::timeLabel($tmpDay["dayTimeBegin"],'ccc') ?>
					<span <?= $tmpDay["isToday"]==true?'class="circleNb"':null ?> ><?= $tmpDay["dayOfMonth"] ?></span>
					<?php if(!empty($tmpDay["publicHoliday"])){ ?><img src="app/img/calendar/publicHoliday.png" class="vPublicHoliday"><?php } ?>
				</td>
			<?php } ?>
			<td class="vWeekHeaderScrollbar">&nbsp;</td>
		</tr>
	</table>

	<!--AGENDA SCROLLABLE-->
	<div class="vWeekScroller">
		<table class="vWeekTable">
			<!--BOUCLE SUR LES LIGNES DU TABLEAU : AVEC DES CRÉNEAUX DE 15MN-->
			<?php
			for($tmp15mn=0; $tmp15mn<96; $tmp15mn++){
				$isHourBegin=($tmp15mn % 4===0);
			?>
				<tr <?= ($tmp15mn < ($tmpCal->timeSlotBegin*4) || ($tmpCal->timeSlotEnd*4) <= $tmp15mn) ?  'class="vLineNotTimeSlot"'  : null ?> >
					<!--COLONNE DES HEURES (modulo '%' verif si multiple de 4)-->
					<?php if($isHourBegin==true){ ?><td class="vWeekHourLabel" rowspan="4"><?= ($tmp15mn/4) ?><span class="vWeekHourLabel00">:00</span></td><?php } ?>
					<?php
					////	CELLULE DE CHAQUE JOUR AFFICHÉ
					foreach($periodDays as $dayYmd=>$tmpDay){
						$cellClass="vWeekCell notooltip";
						$cellTimeBegin=$tmpDay["dayTimeBegin"]+($tmp15mn*900);										//Time du début du 1/4 d'heure
						$cellTimeEnd=$cellTimeBegin+900;															//Time de fin
						if($cellTimeBegin <= time() && time() <= $cellTimeEnd)	{$cellClass.=" vWeekCellRedLine ";}	//Heure courante : RedLine
						if($tmpCal->affectationAddRight())  					{$cellClass.=" vWeekCellEvtAdd ";}	//Droit d'ajouter un Evt
						$cellAttributes='data-cellDateLabel="'.Txt::dateLabel($cellTimeBegin,"labelFull").'" '.
										'data-cellTimeBegin="'.$cellTimeBegin.'" '.
										'data-cellTimeEnd="'.$cellTimeEnd.'" '.
										'data-cellDayYmd="'.$dayYmd.'" '.
										'data-cellIdCal="'.$tmpCal->_id.'" '.
										'data-cellSummerChange="'.$tmpDay["summerChange"].'" '.
										'data-cellWinterChange="'.$tmpDay["winterChange"].'" ';
					?>
						<!--CELLULE DU 1/4 D'HEURE & D'AJOUT D'EVT-->
						<td class="<?= $cellClass ?> " title="<?= date('H:i',$cellTimeBegin) ?>" <?= $cellAttributes ?>>
							<?php if(Req::isMobile() && $isHourBegin==true){ ?>
								<img src="app/img/plusSmall.png" class="vMobileEvtAdd" onclick="lightboxOpen('<?= $getUrlNewEvt.'&_idCal='.$tmpCal->_id.'&newEvtTimeBegin='.$cellTimeBegin ?>')">
							<?php } ?>
						</td>
					<?php } ?>
				</tr>
			<?php } ?>
		</table>

		<!--EVENEMENTS DE CHAQUE JOURS DE LA SEMAINE-->
		<?php
		foreach($tmpCal->evtListDays as $evtList){
			foreach($evtList as $tmpEvt){
				echo $tmpEvt->divContainerMenu("vEvtBlock",$tmpEvt->evtAttributes,$tmpEvt->contextMenuOptions);
		?>
				<div class="vEvtLabel" onclick="if(evtIsDragged==false) <?= $tmpEvt->lightboxVue() ?>" <?= Txt::tooltip($tmpEvt->tooltip) ?>>
					<?= $tmpEvt->title ?>
					<div class="vEvtLabelHM"><?= Txt::dateLabel($tmpEvt->timeBegin,"mini",$tmpEvt->timeEnd) ?></div>
				</div>
			</div>
		<?php
			}
		}
		?>
	</div>
</div>