<?php if($tmpCal->isFirstCal==true){ ?>
	<script>
	/************************************************************************************************************
	 *	DIMENSIONNE & AFFICHE LES AGENDAS
	 ************************************************************************************************************/
	function calendarDisplay(isPrint)
	{
		////	DIMENSIONNE LES AGENDAS
		$(".vMonthTable").height( $(".vCalVue").height() - $(".vMonthHeader").height() );	//Height des .vMonthTable
		$(".vCalMain").each(function(){														//Augmente le Height du .vCalMain des agendas ayant beaucoup d'evts
			let contentHeight=$(this).find(".vCalHeader").height() + $(this).find(".vMonthHeader").height() + $(this).find(".vMonthTable").height();
			if($(this).innerHeight() < contentHeight)  {$(this).innerHeight(contentHeight);}
		});
		////	POUR CHAQUE JOUR, TRI LES EVTS EN FONCTION DU "timebegin"
		$(".vMonthDayCell:has(.vEvtBlock)").each(function(){
			const monthDayCell=this;
			const evtBlockList=$(this).find(".vEvtBlock").sort(function(a,b){
				return $(a).attr("data-timebegin") - $(b).attr("data-timebegin")
			});
			evtBlockList.each(function(){ monthDayCell.append(this); });
		});
	}
	</script>


	<style>
	.vMonthHeader, .vMonthTable				{width:100%; border-collapse:collapse; table-layout:fixed;}	/*Tableau du libellé des jours et de la grille des heures*/
	.vWeekNbOfYear							{width:18px; font-size:0.8rem; opacity:0.5;}				/*numero des semaines dans l'année*/
	.vMonthTable .vWeekNbOfYear:hover		{background:var(--bg_select);}	  							/*numero des semaines dans l'année*/
	.vMonthRow								{height:17%; min-height:17%;}								/*Hauteur des lignes basé sur 6 semaines (soit 17%)*/
	/*Cellules du jour*/
	.vMonthDayCell							{position:relative; vertical-align:top; padding:0px; background:var(--bg_container);}/*cells du jour*/
	.vMonthDayCell							{border:var(--border_table);}								/*bordure des cells des jours*/
	.vMonthDayOtherMonth					{background:var(--bg_fieldset);} 	  						/*Cell des jours des autres mois*/
	.vMonthDayHeader						{width:100%; height:27px;}									/*Label du jour + jour ferie + ajout d'evt*/
	.vMonthDayHeader img[src*='add']		{width:23px; height:23px;}									/*ajouter un evt*/
	.vMonthDayNb							{width:25px; text-align:center;}							/*Numéro du jour du mois*/
	.vMonthDayPublicHoliday					{color:#080; font-size:0.85rem;}							/*Libellé du jour férié*/
	.vMonthDayHeader .vMonthDayAddEvt		{width:25px; visibility:hidden; cursor:pointer;}			/*Ajout d'evt "Plus" : masqué par défaut*/
	.vMonthDayHeader:hover .vMonthDayAddEvt	{visibility:visible;}										/*Idem : affiche au survol label du jour*/

	/*****    RESPONSIVE LARGE*/
	@media screen and (max-width:1199px){
		.vMonthDayHeader					{font-size:0.7em; font-weight:normal;}
		.vEvtLabel							{font-size:0.8rem; line-height:12px;}
		.vEvtLabelDate, .vWeekNbOfYear, .vMonthDayPublicHoliday	{display:none!important;}
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
				<td class="vMonthDayCell vCellDay <?= $tmpDay["isMonthCurtime"]==false?'vMonthDayOtherMonth':null ?>" data-datelabel="<?= Txt::dateLabel("dateDefault",$tmpDay["dayTimeBegin"]) ?>" data-ymd="<?= $dayYmd ?>">
					<!--LABEL DU JOUR + JOUR FERIE + AJOUT D'EVT-->
					<table class="vMonthDayHeader">
						<tr>
							<td class="vMonthDayNb"><span class="<?= $tmpDay["isToday"]==true?'circleNb':null ?>"><?= $tmpDay["dayOfMonth"] ?></span></td>
							<td class="vMonthDayPublicHoliday"><?= $tmpDay["publicHoliday"] ?></td>
							<?php if($tmpCal->addProposeEvt()){ ?><td class="vMonthDayAddEvt"><img src="app/img/addBlue.svg"<?= $tmpCal->addEvtTooltip ?> onclick="lightboxOpen('<?= $tmpCal->urlNewEvt.'&newEvtTimeBegin='.$tmpDay['newEvtTimeBegin'] ?>')"></td><?php } ?>
						</tr>
					</table>
					<!--EVENEMENTS DU JOUR-->
					<?php foreach($tmpCal->evtListDays[$dayYmd] as $tmpEvt){ ?>
						<?= $tmpEvt->objContainer("vEvtBlock",$tmpEvt->contextMenuOptions) ?>
							<div class="vEvtLabel" onclick="<?= $tmpEvt->lightboxVue() ?>" <?= Txt::tooltip($tmpEvt->tooltip) ?>>
								<span class="vEvtLabelDate"><?= $tmpEvt->dateLabel("mini",true) ?></span>
								<?= $tmpEvt->title ?>
							</div>
						</div>
					<?php } ?>
				</td>

			<!--LIGNE DE SEMAINE => FIN + NUM DE SEMAINE DE L'ANNEE-->
			<?php
			if($tmpDay["dayOfWeek"]==7){
				$weekUrl="index.php?ctrl=calendar&calendarDisplayMode=week&curTime=".$tmpDay["dayTimeBegin"];
			?>
				<td class="vWeekNbOfYear" <?= Txt::tooltip("CALENDAR_yearWeekNum") ?> onclick="redir('<?= $weekUrl ?>');"><?= date("W",$tmpDay["dayTimeBegin"]) ?></td>
			</tr>
			<?php } ?>
		<?php } ?>
	</table>
</div>