<?php if($tmpCal->isFirstCal==true){ ?>
<script>
/******************************************************************************************
 *	AFFICHAGE DES AGENDAS
 ******************************************************************************************/
function calendarDisplay(isPrint)
{
	let monthCellWidth=Math.floor((containerWidth-$(".vMonthWeekNbOfYear").width()) / 7) - 2;		//Calcul le width des cellules du mois (containerWidth cf "app.js" puis -2px de border)
	$(".vCalLabelDays,.vMonthDayCell,.vEvtBlock").innerWidth(monthCellWidth);						//Width des cellules du mois et des Evts
	$(".vCalMain").each(function(){																	//Parcours chaque agenda
		let contentHeight=$(this).find(".vCalHeader").height() + $(this).find(".vCalVue").height();	//Hauteur du contenu de l'agenda
		if($(this).innerHeight() < contentHeight)  {$(this).innerHeight(contentHeight);}			//Si le Height du conteneur .vCalMain est < au contenu (avec de nombreux evts) : on actualise le Height
	});
}
</script>

<style>
/*Conteneur principal + header + lignes*/
.vCalVue									{border-collapse:collapse;}											/*Bordures fusionnées*/
.vMonthWeekNbOfYear							{width:15px; font-size:0.9rem; opacity:0.5; text-align:center;}		/*numero des semaines dans l'année*/
.vMonthWeek									{height:17%; min-height:17%;}										/*Hauteur des lignes basé sur 6 semaines (soit 17%)*/
.vPublicHoliday								{color:#080; font-style:italic; margin-left:15px;}					/*Libellé du jour férié*/

/*Cellules du jour*/
.vMonthDayCell								{vertical-align:top; padding:0px; <?= Ctrl::$agora->skin=="white" ? "background:white;border:1px solid #dededf;color:#222;" : "background:black;border:1px solid #333;color:#fff;" ?>}
.vMonthDayCell:hover, .vMonthDayOtherMonth	{background:<?= Ctrl::$agora->skin=="white"?"#fafafa":"#222" ?>;}	/*jour survolé / jour d'un autre mois : bg du block*/
.vMonthDayLabel								{height:30px; padding:3px; line-height:24px;}						/*ligne du label du jour (numéro)*/
.vMonthDayLabel .vMonthAddEvt				{display:none;}														/*"Plus" d'ajout d'evt : masqué par défaut*/
.vMonthDayCell:hover .vMonthAddEvt			{display:block; float:right;}										/*-> affiche au survol du jour*/
.vMonthDayCell:hover .vMonthDayLabel		{color:#c00;}														/*jour survolé : ligne du label*/

/*evenements*/
.vEvtBlock									{max-width:98%; margin-bottom:2px;}
.vEvtBlock .menuContextLaunchFloat			{top:2px; right:2px;}/*décale le menu "burger"*/
.vEvtLabel									{font-size:0.85rem; white-space:nowrap;}/*white-space: Texte sur une seule ligne*/
.vEvtLabelDate								{display:inline;}

/*AFFICHAGE RESPONSIVE*/
@media screen and (max-width:1200px){
	.vMonthDayLabel											{font-size:0.7em; font-weight:normal;}
	.vMonthDayLabel .vMonthAddEvt							{margin:0px;}
	.vEvtLabel												{font-size:0.8rem; line-height:12px;}
	.vEvtLabelDate, .vMonthWeekNbOfYear, .vPublicHoliday	{display:none!important;}
}
</style>
<?php } ?>


<table class="vCalVue">
	<!--HEADER : JOURS DE LA SEMAINE-->
	<tr>
		<?php for($i=1; $i<=7; $i++){ ?><td class="vCalLabelDays"><?= Req::isMobile() ? substr(Txt::trad("day_".$i),0,3) : Txt::trad("day_".$i) ?></td><?php } ?>
		<td class="vMonthWeekNbOfYear">&nbsp;</td>
	</tr>

	<!--TABLEAU DES JOURS DU MOIS-->
	<?php foreach($periodDays as $dayYmd=>$tmpDay){ ?>
		<!--LIGNE DE LA SEMAINE-->
		<?php if($tmpDay["dayOfWeek"]==1){ ?><tr class="vMonthWeek"><?php } ?>

			<!--BLOCK DU JOUR-->
			<td class="vMonthDayCell <?= $tmpDay["monthOfYear"]!=date("n",$curTime)?"vMonthDayOtherMonth":null ?>">
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

			<!--LIGNE DE LA SEMAINE : FIN (+ NUM DE SEMAINE DE L'ANNEE)-->
			<?php if($tmpDay["dayOfWeek"]==7){ ?>
				<td class="vMonthWeekNbOfYear" <?= Txt::tooltip("CALENDAR_yearWeekNum") ?> onclick="redir('<?= '?ctrl=calendar&calendarDisplayMode=week&curTime='.$tmpDay["dayTimeBegin"] ?>');">
					<?= date("W",$tmpDay["dayTimeBegin"]) ?>
				</td>
			</tr>
			<?php } ?>
	<?php } ?>
</table>