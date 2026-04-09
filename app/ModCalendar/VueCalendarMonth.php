<?php if($tmpCal->isFirstCal==true){ ?>
	<script>
	/******************************************************************************************
	 *	AFFICHAGE DES AGENDAS
	 ******************************************************************************************/
	function calendarDisplay(isPrint)
	{
		////	DIMENSIONNE LES AGENDAS
		let calendarWidth=isMobile() ?  windowTopWidth  :  (windowTopWidth - $("#pageMenu").outerWidth(true) -14);	//Width du principal container de la page (-14px de scroolbar : cf ::-webkit-scrollbar)
		let monthCellWidth=Math.floor((calendarWidth-$(".vWeekNbOfYear").width()) / 7) - 2;							//Calcul le width des cellules du mois
		$(".vCalLabelDays,.vMonthDayCell,.vEvtBlock").innerWidth(monthCellWidth);									//Width des cellules du mois et des Evts
		$(".vCalMain").each(function(){																				//Parcours chaque agenda
			let contentHeight=$(this).find(".vCalHeader").height() + $(this).find(".vCalVue").height();				//Hauteur du contenu de l'agenda
			if($(this).innerHeight() < contentHeight)  {$(this).innerHeight(contentHeight);}						//Si le Height du conteneur .vCalMain est < au contenu (avec de nombreux evts) : on actualise le Height
		});
	}

	/******************************************************************************************
	 * DRAGGABLE DES ÉVÉNEMENTS
	 ******************************************************************************************/
	function evtDraggable()
	{
		////	EVT DRAGGABLES
		interact(".vEvtBlock[data-evtIsDraggable='true']").draggable({
			////	Limite la dropzone
			modifiers:[	interact.modifiers.restrictRect({restriction:".vCalVue tbody"}) ],
			listeners:{
				////	Enregistre la cell de départ
				start(event){
					dragStartYmd=event.target.parentNode.getAttribute("data-dayYmd");
				},
				////	Déplace l'événement via "translate" (pas absolute)
				move(event){
					let targetEvt=event.target;
					let evtX=(parseFloat(targetEvt.getAttribute('data-x')) || 0) + event.dx;
					let evtY=(parseFloat(targetEvt.getAttribute('data-y')) || 0) + event.dy;
					targetEvt.style.transform='translate('+evtX+'px, '+evtY+'px)';
					targetEvt.setAttribute('data-x', evtX);	//Enregistre le décalage X
					targetEvt.setAttribute('data-y', evtY);	//Idem pour Y
					targetEvt.classList.add("vEvtBlockMoved");	//Style durant le déplacement
					$(".tooltipster-base").hide();				//Masque le tooltip
				}
			}
		});
		////	DÉFINITION DE LA DROPZONE
		interact('.vMonthDayCell').dropzone({
			accept: '.vEvtBlock',
			////	Style de la cell survolée
			ondragenter(event){
				event.target.classList.add("drop-target");
			},
			////	Style de la cell en sortie
			ondragleave(event){
				event.target.classList.remove("drop-target");
			},
			////	Fin du drop
			ondrop(event){
				////	targetEvt : réinit la position et style
				let targetEvt=event.relatedTarget;
				targetEvt.style.transform='';
				targetEvt.removeAttribute("data-x");
				targetEvt.removeAttribute("data-y");
				targetEvt.classList.remove("vEvtBlockMoved");
				////	vMonthDayCell : réinit le style et y intègre le targetEvt
				let targetCell=event.target;
				targetCell.classList.remove("drop-target");
				targetCell.appendChild(targetEvt);
				////	Confirme le déplacement de l'evt
				if(dragStartYmd!==targetCell.getAttribute("data-dayYmd")){
					let confirmParams={
						title:"<?= Txt::trad("CALENDAR_evtChangeTime") ?>",
						content:'<img src="app/img/arrowRight.png"> '+$(targetCell).attr("data-cellDateLabel"),
						buttons:{
							//// Confirmation rejetée
							reject:{
								text:labelConfirmCancel,
								btnClass:"btn-default",
								action:function(){
									// Remet l'evt à sa place d'origine
									document.querySelector('.vMonthDayCell[data-dayYmd="'+dragStartYmd+'"]').append(targetEvt);
								}
							},
							//// Confirmation acceptée
							accept:{
								text:labelConfirm,
								btnClass:"btn-green",
								action:function(){
									let cellDayYmd=$(targetCell).attr("data-dayYmd");
									let newDateString=cellDayYmd+"T"+targetEvt.getAttribute("data-evtHMSBegin");	//Nouvelle date de l'evt au format ISO 8601 (ex: "2036-04-02T15:30:00")
									let evtNewTimeBegin=new Date(newDateString).getTime() / 1000;					//Nouveau timestamp de l'evt 
									let evtTypeId=$(targetEvt).attr("data-typeId");
									let ajaxUrl="?ctrl=calendar&action=EvtChangeTime&evtNewTimeBegin="+evtNewTimeBegin+"&typeId="+evtTypeId;
									$.ajax({url:ajaxUrl,dataType:"json"}).done(function(result){
											if(result.changed){																						//Update Ok :
											$(".vEvtBlock[data-typeId='"+evtTypeId+"']").each(function(){											//Parcourt chaque instance de l'evt pour chaque agenda affiché !
												for(var keyAttr in result.attributes)  {this.setAttribute(keyAttr, result.attributes[keyAttr]);}	//Update les attributs de l'evt : timeBegin, timeEnd, etc
												$(this).find(".vEvtLabel").tooltipUpdate(result.tooltip);											//Update le tooltip
												if(targetEvt.id!=this.id)																			//L'evt est présent dans d'autres agendas : 
													{$(this).closest(".vCalVue").find(".vMonthDayCell[data-dayYmd="+cellDayYmd+"]").append(this);}	//-> on le déplace aussi dans les vMonthDayCell concernées
											});
											notify("<?= Txt::trad("CALENDAR_evtChangeTimeConfirmed") ?>","success");
											calendarDisplay();//Reload l'affichage
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
		});
	}
	</script>


	<style>
	/*Conteneur principal + header + lignes*/
	.vCalVue									{border-collapse:collapse;}												/*Bordures fusionnées*/
	.vWeekNbOfYear								{width:15px; font-size:0.9rem; opacity:0.5; text-align:center;}			/*numero des semaines dans l'année*/
	.vMonthWeek									{height:17%; min-height:17%;}											/*Hauteur des lignes basé sur 6 semaines (soit 17%)*/
	.vPublicHoliday								{color:#080; font-style:italic; margin-left:15px;}						/*Libellé du jour férié*/

	/*Cellules du jour*/
	.vMonthDayCell								{vertical-align:top; padding:0px; <?= Ctrl::$agora->skin=="white" ? "background:white;border:1px solid #e2e2e2;color:#222;" : "background:black;border:1px solid #333;color:#fff;" ?>}
	.vMonthDayCell:hover, .vMonthDayOtherMonth	{background:<?= Ctrl::$agora->skin=="white"?"#eee":"#222" ?>;}	  /*Cell du jour survolée ou d'un autre mois*/
	.vMonthDayCell.drop-target 					{background:#f7fff7;}													/*Dropzone survolée*/
	.vMonthDayLabel								{height:30px; padding:3px; line-height:24px;}							/*Label du jour*/
	.vMonthDayLabel:hover						{cursor:pointer;}														/*Idem : survol*/
	.vMonthDayLabel .vMonthAddEvt				{display:none;}															/*Ajout d'evt "Plus" : masqué par défaut*/
	.vMonthDayLabel:hover .vMonthAddEvt			{display:block; float:right;}											/*Idem : affiche au survol label du jour*/

	/*evenements*/
	.vEvtBlock									{max-width:98%; margin-bottom:2px;}
	.vEvtBlock .menuContextLaunchFloat			{top:2px; right:2px;}/*décale le menu "burger"*/
	.vEvtLabel									{font-size:0.85rem; white-space:nowrap;}/*white-space: Texte sur une seule ligne*/
	.vEvtLabelDate								{display:inline;}

	/*AFFICHAGE RESPONSIVE*/
	@media screen and (max-width:1200px){
		.vMonthDayLabel									{font-size:0.7em; font-weight:normal;}
		.vMonthDayLabel .vMonthAddEvt					{margin:0px;}
		.vEvtLabel										{font-size:0.8rem; line-height:12px;}
		.vEvtLabelDate, .vWeekNbOfYear, .vPublicHoliday	{display:none!important;}
	}
	</style>
<?php } ?>


<table class="vCalVue">
	<!--HEADER : JOURS DE LA SEMAINE-->
	<tr>
		<?php for($i=1; $i<=7; $i++){ ?><td class="vCalLabelDays"><?= Req::isMobile() ? substr(Txt::trad("day_".$i),0,3) : Txt::trad("day_".$i) ?></td><?php } ?>
		<td class="vWeekNbOfYear">&nbsp;</td>
	</tr>

	<!--TABLEAU DES JOURS DU MOIS-->
	<tbody>
		<?php foreach($periodDays as $dayYmd=>$tmpDay){ ?>
			<!--LIGNE DE SEMAINE => DEBUT-->
			<?php if($tmpDay["dayOfWeek"]==1){ ?><tr class="vMonthWeek"><?php } ?>

				<!--BLOCK DU JOUR-->
				<td class="vMonthDayCell <?= $tmpDay["isMonthCurtime"]==false?'vMonthDayOtherMonth':null ?>" data-dayYmd="<?= $dayYmd ?>" data-cellDateLabel="<?= Txt::dateLabel($tmpDay["dayTimeBegin"],"dateFull") ?>">
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
						<?php foreach($tmpCal->evtListDays[$dayYmd] as $tmpEvt){
							echo $tmpEvt->divContainerMenu("vEvtBlock",$tmpEvt->evtAttributes,$tmpEvt->contextMenuOptions);
						?>
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
	</tbody>
</table>