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
		$(".vWeekScroller").each(function(){
			const calSelector=this;																																//Selecteur de l'agenda courant
			const evtBlockList=$(this).find(".vEvtBlock").sort(function(a,b){  return $(a).attr("data-evt-time-begin") - $(b).attr("data-evt-time-begin")  });	//Tri les evts de l'agenda par timeBegin (cf. evtDayIndex)
			evtBlockList.each(function(){																														//Parcourt chaque Evt
				const ymdEvt=this.getAttribute("data-evt-ymd-displayed");										//Date à laquelle l'evt est affiché (cf evt sur plusieurs jours)
				const dayFirstCell=".vWeekCell[data-cell-ymd='"+ymdEvt+"']:first";								//Selecteur de la 1ere cellule (0:00) du jour
				const evtDayIndex=$(evtBlockList).filter("[data-evt-ymd-displayed='"+ymdEvt+"']").index(this);	//Index/classement de l'evt parmi les autres evt du jour
				const evtTimeBegin=this.getAttribute("data-evt-time-begin");
				let evtWidth=$(dayFirstCell).width();
				let evtX=$(dayFirstCell).position().left;																						//Position Left de l'evt (cf. position left du jour)
				let evtSameTime=$(calSelector).find(".vEvtBlock[data-evt-ymd-displayed='"+ymdEvt+"'][data-evt-time-begin='"+evtTimeBegin+"']");	//Evts qui commencent en même temps
				//// D'autres evts commencent en même temps : split l'evt
				if(evtSameTime.length > 1){
					evtWidth=evtWidth / evtSameTime.length;		//Largeur en fonction du nb d'evt à afficher cote à cote
					evtX+=evtWidth * evtSameTime.index(this);	//Décale l'evt en fonction de son rang (index) parmi les autres evts
				}
				//// Evt sur le même créneau qu'un autre evt : superpose/décale l'evt
				else if(evtDayIndex > 0  && (prevEvtTimeEnd > evtTimeBegin || prevEvtTimeEnd > this.getAttribute("data-evt-time-end"))){
					$(this).addClass("vEvtBlockSuperposed");	//Bordure accentuée et z-index
					evtWidth-=20;								//Réduit la largeur de l'evt de 20px
					evtX+=20;									//Décale d'autant sur la droite
				}
				//// Position / dimensions de l'evt
				let evtHeight=(weekCellHeight/900) * this.getAttribute("data-evt-time-duration");		//Hauteur de l'evt
				let evtY=(weekCellHeight/900) * this.getAttribute("data-evt-time-since-day-begin");		//Position top (900s=15mn)
				if($(dayFirstCell).attr("data-cell-summer-change"))			{evtY+=(weekCellHeight*4);}	//Journée de changement en heure d'été : décale d'une heure
				else if($(dayFirstCell).attr("data-cell-winter-change"))	{evtY-=(weekCellHeight*4);}	//Idem pour l'heure d'hiver
				this.style.left=evtX+'px';																//Positonne l'evt en X
				this.style.top =evtY+'px';																//Positonne l'evt en Y
				$(this).outerWidth(evtWidth).outerHeight(evtHeight-1);									//Dimensions de l'evt (height-1 pour les distinguer)
				$(this).find(".vEvtLabel").outerHeight($(this).height());								//Hauteur au vEvtLabel (pas de css "height:inherit")
				//// Update de variables
				if(this.getAttribute("data-evt-time-since-day-begin") > 0 && evtY < calScrollTop)  {$(calSelector).scrollTop(evtY);}					//Scrolltop de l'agenda en fonction de l'evt le plus tôt
				if(evtDayIndex==0 || prevEvtTimeEnd < this.getAttribute("data-evt-time-end"))  {prevEvtTimeEnd=this.getAttribute("data-evt-time-end");}	//"prevEvtTimeEnd" pour l'evt suivant : 1er evt du jour ou timeEnd supérieur
			});
		});
	}

	/******************************************************************************************
	 * DRAGGABLE DES ÉVÉNEMENTS
	 ******************************************************************************************/
	function evtDraggable()
	{
		////	EVT DRAGGABLES
		interact(".vEvtBlock[data-evt-is-draggable='true']").draggable({
			////	Mobile: latence pour ne pas deplacer un evt par erreur en scrollant la page
			hold:isMobile() ? 100 : 0,
			////	Limite la dropzone + Config la zone de Snap/Accroche
			modifiers:[
				interact.modifiers.restrictRect({ restriction:".vCalVue" }),
				interact.modifiers.snap({
					offset:'parent',															//Snap basé sur le parent
					relativePoints:[{ x:weekHourWidth, y:0 }],									//Décalage par rapport à la 1ere colonne .vWeekHourLabel
					targets:[ interact.snappers.grid({ x:weekCellWidth, y:weekCellHeight }) ]	//Grid à la dimension des .vWeekCell
				})
			],
			listeners: {
				////	Enregistre la position et Label de départ (cf boutton "reject")
				start(event){
					targetCell=null;
					const targetEvt=event.target;
					evtStartX=parseFloat(targetEvt.style.left);
					evtStartY=parseFloat(targetEvt.style.top);
					evtStartDate=$(targetEvt).find(".vEvtLabelHM").html();
				},
				////	Déplace l'événement via top/left (position absolute)
				move(event){
					const targetEvt=event.target;
					////	Style durant le déplacement  +  Masque le tooltip
					targetEvt.classList.add("vEvtBlockMoved");
					$(".tooltipster-base").hide();
					////	Déplace le targetEvt en X/Y
					let mouseMoveX=(Math.abs(event.dx) > (weekCellWidth/2)) ? event.dx : 0;//Mouvement X relative à la souris : corrigé pour les événements splités (tester 2 evts avec le meme dateBegin)
					const evtX = (parseFloat(targetEvt.style.left) || 0) + mouseMoveX;
					const evtY = (parseFloat(targetEvt.style.top) || 0) + event.dy;
					targetEvt.style.left=evtX+'px';
					targetEvt.style.top =evtY+'px';
					////	Récupère la .vWeekCell dont la position est la plus proche du targetEvt
					$(targetEvt).parent().find(".vWeekCell").each(function(){
						const diffY=(evtY - this.offsetTop);
						const diffX=(evtX - this.offsetLeft);
						if(diffY <= 3  && diffX <= (weekCellWidth/2))  {targetCell=this; return false;}
					});
					$(targetEvt).find(".vEvtLabelHM").html('<b class="vEvtDragged">'+targetCell.getAttribute("data-cell-hm-label")+'</b>');
				},
				////	Fin du drop !
				end(event){
					const targetEvt=event.target;
					////	Réinit le style (timeout : cf stopPropagation du click du .vEvtBlock)
					setTimeout(function(){ targetEvt.classList.remove("vEvtBlockMoved"); },50);
					////	Confirme le déplacement de l'evt
					if(targetCell!=null  &  (Math.abs(parseFloat(targetEvt.style.left) - evtStartX) > 2 || Math.abs(parseFloat(targetEvt.style.top) - evtStartY) > 2)){
						const confirmParams={
							title:"<?= Txt::trad("CALENDAR_evtChangeTime") ?>",
							content:'<span class="vEvtConfirmOldDate">'+targetEvt.getAttribute("data-evt-date-label")+'</span> <img src="app/img/arrowRight.png"> '+targetCell.getAttribute("data-cell-date-label"),
							buttons:{
								////	Confirmation rejetée
								reject:{
									text:"<?= Txt::trad("confirmCancel") ?>",
									btnClass:"btn-default",
										action:function(){
											$(targetEvt).animate({top:evtStartY,left:evtStartX},100);	//Remet l'evt à sa place d'origine
											$(targetEvt).find(".vEvtLabelHM").html(evtStartDate);		//Remet le label d'origine
										}
								},
								////  Confirmation acceptée
								accept:{
									text:"<?= Txt::trad("confirm") ?>",
									btnClass:"btn-green",
									action:function(){
										//// Enregistre la nouvelle date via Ajax
										evtDraggedRecord(targetEvt, targetCell, targetCell.getAttribute("data-cell-time-begin"));						
									}
								}
							}
						}
						//// Lance le Confirm (paramétrage par défaut + spécifique)
						$.confirm(Object.assign(confirmParamsDefault,confirmParams));
					}
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
					startDayYmd=this.getAttribute("data-cell-ymd");															// Jour Ymd
					startTimeBegin=parseInt(this.getAttribute("data-cell-time-begin"));										// Time du début de sélection
					startTimeEnd  =parseInt(this.getAttribute("data-cell-time-end"));										// Time de fin de sélection
					$(this).addClass("lineSelect");																			// Sélection du .vWeekCell
				}
				else if(event.type=="mousemove" && isMouseDown==true && startDayYmd==this.getAttribute("data-cell-ymd")){	//// Continue la sélection sur le même jour
					startTimeEnd=parseInt(this.getAttribute("data-cell-time-end"));											// Update le Time de fin de sélection
					$(".vWeekCell[data-cell-ymd='"+startDayYmd+"']").each(function(){										// Sélection/déselection des .vWeekCell (descend/monte la souris : ajoute/enlève .lineSelect)
						if(startTimeBegin <= parseInt(this.getAttribute("data-cell-time-begin"))  &&  parseInt(this.getAttribute("data-cell-time-end")) <= startTimeEnd)	{$(this).addClass("lineSelect");}
						else																																				{$(this).removeClass("lineSelect");}
					});
				}
				else if(event.type=="mouseup" && startTimeBegin < startTimeEnd){											//// Fin de sélection : ouvre l'édition d'un nouvel événement !
					lightboxOpen("<?= $getUrlNewEvt ?>&_idCal="+this.getAttribute("data-cell-idcal")+"&newEvtTimeBegin="+startTimeBegin+"&newEvtTimeEnd="+startTimeEnd);
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
	.vMobileEvtAdd								{display:none;}

	/*AFFICHAGE RESPONSIVE*/
	@media screen and (max-width:1200px){
		.vWeekHourLabel, .vEvtLabel				{font-size:0.8rem;}
		.vWeekHourLabel							{width:25px;}
		.vEvtDragged							{font-size:1rem;}
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
						$cellAttributes='data-cell-date-label="'.Txt::dateLabel($cellTimeBegin,"labelFull").'" '.
										'data-cell-hm-label="'.date("H:i",$cellTimeBegin).'" '.
										'data-cell-time-begin="'.$cellTimeBegin.'" '.
										'data-cell-time-end="'.$cellTimeEnd.'" '.
										'data-cell-ymd="'.$dayYmd.'" '.
										'data-cell-idcal="'.$tmpCal->_id.'" '.
										'data-cell-summer-change="'.$tmpDay["summerChange"].'" '.
										'data-cell-winter-change="'.$tmpDay["winterChange"].'" ';
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
		?>
			<?= $tmpEvt->mainDivMenu("vEvtBlock",$tmpEvt->contextMenuOptions) ?>
				<div class="vEvtLabel" <?= Txt::tooltip($tmpEvt->tooltip) ?> onclick="<?= $tmpEvt->lightboxVue() ?>">
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