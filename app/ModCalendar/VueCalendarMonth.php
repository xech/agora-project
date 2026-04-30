<?php if($tmpCal->isFirstCal==true){ ?>
	<script>
	/******************************************************************************************
	 *	AFFICHAGE DES AGENDAS
	 ******************************************************************************************/
	function calendarDisplay(isPrint)
	{
		////	DIMENSIONNE LES AGENDAS
		let calendarWidth=isMobile() ?  windowTopWidth  :  (windowTopWidth - $("#pageMenu").outerWidth(true) -14);	//Width du principal container de la page (-14px de scroolbar : cf ::-webkit-scrollbar)
		let calendarHeight=$(".vCalVue").innerHeight() - $(".vMonthHeader").outerHeight();							//Height des .vMonthTable
		$(".vMonthTable").outerHeight(calendarHeight);																//Applique le height
		let monthCellWidth=Math.floor((calendarWidth-$(".vWeekNbOfYear").width()) / 7) - 2;							//Calcul le width des cellules du mois
		$(".vCalLabelDays,.vMonthCell,.vEvtBlock").innerWidth(monthCellWidth);									//Width des cellules du mois et des Evts
		$(".vCalMain").each(function(){																				//Parcours chaque agenda
			let contentHeight=$(this).find(".vCalHeader").height() + $(this).find(".vMonthHeader").height() + $(this).find(".vMonthTable").height();//Hauteur du contenu de l'agenda
			if($(this).innerHeight() < contentHeight)  {$(this).innerHeight(contentHeight);}						//Si le Height du conteneur .vCalMain est < au contenu (avec de nombreux evts) : on actualise le Height
		});
	}

	/******************************************************************************************
	 * DRAGGABLE DES ÉVÉNEMENTS
	 ******************************************************************************************/
	function evtDraggable()
	{
		////	EVT DRAGGABLES
		interact(".vEvtBlock[data-evt-is-draggable='true']").draggable({
			////	Limite la dropzone
			modifiers:[
				interact.modifiers.restrictRect({restriction:".vMonthTable"})
			],
			listeners:{
				////	Enregistre le startCell (cf boutton "reject")
				start(event){
					startCell=event.target.parentNode;
				},
				////	Déplace l'événement via "translate" en fonction du curseur
				move(event){
					const targetEvt=event.target;
					////	Déplace le targetEvt en fonction du curseur  +  Enregistre le décalage X/Y
					const evtX=(parseFloat(targetEvt.getAttribute('data-x')) || 0) + event.dx;
					const evtY=(parseFloat(targetEvt.getAttribute('data-y')) || 0) + event.dy;
					targetEvt.style.transform='translate('+evtX+'px, '+evtY+'px)';
					targetEvt.setAttribute('data-x', evtX);
					targetEvt.setAttribute('data-y', evtY);
					////	Style durant le déplacement  +  Masque le tooltip
					targetEvt.classList.add("vEvtBlockMoved");
					$(".tooltipster-base").hide();
				},
			}
		});

		////	DÉFINITION DE LA DROPZONE
		interact('.vMonthCell').dropzone({
			accept: '.vEvtBlock',
			////	Style de la .vMonthCell : en entrée et sortie
			ondragenter(event){
				event.target.classList.add("vMonthCellTarget");
			},
			ondragleave(event){
				event.target.classList.remove("vMonthCellTarget");
			},
			////	Fin du drop !
			ondrop(event){
				const targetEvt=event.relatedTarget;
				const targetCell=event.target;
				////	Réinit les styles + Déplace le targetEvt dans la targetCell
				targetEvt.classList.remove("vEvtBlockMoved");
				targetEvt.style.transform='';
				targetEvt.removeAttribute("data-x");
				targetEvt.removeAttribute("data-y");
				targetCell.classList.remove("vMonthCellTarget");
				targetCell.appendChild(targetEvt);
				////	Confirme le déplacement de l'evt
				if(startCell.getAttribute("data-cell-ymd")!=targetCell.getAttribute("data-cell-ymd")){
					//// Config le Confirm
					const confirmParams={
						title:"<?= Txt::trad("CALENDAR_evtChangeTime") ?>",
						content:'<span class="vEvtConfirmOldDate">'+startCell.getAttribute("data-cell-date-label")+'</span> <img src="app/img/arrowRight.png"> '+targetCell.getAttribute("data-cell-date-label"),
						buttons:{
							////	Confirmation rejetée
							reject:{
								text:"<?= Txt::trad("confirmCancel") ?>",
								btnClass:"btn-default",
								action:function(){
									//// Remet l'evt à sa place d'origine (même agenda)
									$(targetEvt).parents(".vMonthTable").find('.vMonthCell[data-cell-ymd="'+startCell.getAttribute("data-cell-ymd")+'"]').append(targetEvt);
								}
							},
							////  Confirmation acceptée
							accept:{
								text:"<?= Txt::trad("confirm") ?>",
								btnClass:"btn-green",
								action:function(){
									//// Nouvelle date au format ISO (ex: "2036-04-02T15:30:00"), puis transformée en timestamp
									let cellYmd=targetCell.getAttribute("data-cell-ymd");
									let newDateString=cellYmd+"T"+targetEvt.getAttribute("data-evt-hms-begin");
									let evtNewTimeBegin=new Date(newDateString).getTime() / 1000;
									//// Enregistre la nouvelle date via Ajax
									evtDraggedRecord(targetEvt, targetCell, evtNewTimeBegin);						
								}
							}
						}
					}
					////	Lance le Confirm (paramétrage par défaut + spécifique)
					$.confirm(Object.assign(confirmParamsDefault,confirmParams));
				}
			}
		});
	}
	</script>


	<style>
	/*Conteneur principal + header + lignes*/
	.vCalVue							{border-collapse:collapse;}											/*Bordures fusionnées*/
	.vMonthHeader, .vMonthTable			{width:100%; border-collapse:collapse;}								/*Tableau du libellé des jours et de la grille des heures*/
	.vWeekNbOfYear						{width:15px; font-size:0.9rem; opacity:0.5; text-align:center;}		/*numero des semaines dans l'année*/
	.vMonthRow							{height:17%; min-height:17%;}										/*Hauteur des lignes basé sur 6 semaines (soit 17%)*/

	/*Cellules du jour*/
	.vMonthCell							{vertical-align:top; padding:0px; <?= Ctrl::$agora->skin=="white" ? "background:white;border:1px solid #e2e2e2;color:#222;" : "background:black;border:1px solid #333;color:#fff;" ?>}
	.vMonthDayOtherMonth				{background:<?= Ctrl::$agora->skin=="white"?"#fafafa":"#111" ?>;} 				/*Cell des autres mois*/
	.vMonthCellTarget 					{background:<?= Ctrl::$agora->skin=="white"?"#f7fff7":"#023b02" ?>!important;}	/*Dropzone survolée*/
	.vMonthDayLabel						{height:30px; padding:5px 3px;}						/*Label du jour*/
	.vMonthDayLabel:hover				{cursor:pointer;}									/*Idem : survol*/
	.vMonthDayLabel .vMonthAddEvt		{display:none;}										/*Ajout d'evt "Plus" : masqué par défaut*/
	.vMonthDayLabel:hover .vMonthAddEvt	{display:block; float:right;}						/*Idem : affiche au survol label du jour*/
	.vPublicHoliday						{color:#080; font-size:0.85rem; margin-left:10px;}	/*Libellé du jour férié*/

	/*evenements*/
	.vEvtBlock							{max-width:98%; margin-bottom:2px;}
	.vEvtBlock .menuContextLaunchFloat	{top:2px; right:2px;}/*décale le menu "burger"*/
	.vEvtLabel							{font-size:0.85rem; white-space:nowrap;}/*white-space: Texte sur une seule ligne*/
	.vEvtLabelDate						{display:inline;}

	/*AFFICHAGE RESPONSIVE*/
	@media screen and (max-width:1200px){
		.vMonthDayLabel									{font-size:0.7em; font-weight:normal;}
		.vMonthDayLabel .vMonthAddEvt					{margin:0px;}
		.vEvtLabel										{font-size:0.8rem; line-height:12px;}
		.vEvtLabelDate, .vWeekNbOfYear, .vPublicHoliday	{display:none!important;}
	}
	</style>
<?php } ?>


<div class="vCalVue">

	<!--HEADER : JOURS DE LA SEMAINE-->
	<table class="vMonthHeader">
		<tr>
			<?php for($i=1; $i<=7; $i++){ ?><td class="vCalLabelDays"><?= Req::isMobile() ? substr(Txt::trad("day_".$i),0,3) : Txt::trad("day_".$i) ?></td><?php } ?>
			<td class="vWeekNbOfYear">&nbsp;</td>
		</tr>
	</table>

	<!--TABLEAU DES JOURS DU MOIS-->
	<table class="vMonthTable">
		<?php foreach($periodDays as $dayYmd=>$tmpDay){ ?>
			<!--LIGNE DE SEMAINE => DEBUT-->
			<?php if($tmpDay["dayOfWeek"]==1){ ?><tr class="vMonthRow"><?php } ?>

				<!--BLOCK DU JOUR-->
				<td class="vMonthCell <?= $tmpDay["isMonthCurtime"]==false?'vMonthDayOtherMonth':null ?>" data-cell-ymd="<?= $dayYmd ?>" data-cell-date-label="<?= Txt::dateLabel($tmpDay["dayTimeBegin"],"dateFull") ?>">
						<!--LABEL DU JOUR-->
						<div class="vMonthDayLabel">
							<span <?= $tmpDay["isToday"]==true?'class="circleNb"':null ?> ><?= $tmpDay["dayOfMonth"] ?></span>
							<span class="vPublicHoliday"><?= $tmpDay["publicHoliday"] ?></span>
								<!--PROPOSER/AJOUTER UN EVT-->
								<?php if($tmpCal->affectationAddRight()){ ?>
									<img src="app/img/plusSmall.png" class="vMonthAddEvt" <?= $tmpCal->addEvtTooltip ?> onclick="lightboxOpen('<?= $getUrlNewEvt.'&_idCal='.$tmpCal->_id.'&newEvtTimeBegin='.strtotime($dayYmd.' '.date('H:00')) ?>')">
								<?php } ?>
						</div>
						<!--EVENEMENTS DU JOUR-->
						<?php foreach($tmpCal->evtListDays[$dayYmd] as $tmpEvt){ ?>
							<?= $tmpEvt->mainDivMenu("vEvtBlock",$tmpEvt->contextMenuOptions) ?>
								<div class="vEvtLabel" onclick="<?= $tmpEvt->lightboxVue() ?>" <?= Txt::tooltip($tmpEvt->tooltip) ?>>
									<div class="vEvtLabelDate"><?= Txt::dateLabel($tmpEvt->timeBegin,"mini") ?></div>
									<?= $tmpEvt->title ?>
								</div>
							</div>
						<?php } ?>
				</td>

			<!--LIGNE DE SEMAINE => FIN + NUM DE SEMAINE DE L'ANNEE-->
			<?php if($tmpDay["dayOfWeek"]==7){ ?>
				<td class="vWeekNbOfYear" <?= Txt::tooltip("CALENDAR_yearWeekNum") ?> onclick="redir('<?= '?ctrl=calendar&calendarDisplayMode=week&curTime='.$tmpDay["dayTimeBegin"] ?>');">
					<?= date("W",$tmpDay["dayTimeBegin"]) ?>
				</td>
			</tr>
			<?php } ?>
		<?php } ?>
	</table>
</div>