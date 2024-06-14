<?php if($tmpCal->isFirstCal==true){ ?>
<style>
/*conteneur principal et header*/
.vCalMonthMain									{display:table; width:100%; height:100%;}		/*Tableau des jours du mois*/
.vCalMonthMain>div								{display:table-row;}							/*Ligne de chaque jour de la semaine*/
.vCalMonthMain>div>div							{display:table-cell;}							/*Cellule principale de chaque jour*/
.vCalMonthMain>div:first-child					{height:15px;}									/*ligne du header*/
.vCalMonthWeekNb								{width:15px; vertical-align:middle; font-size:0.9em; opacity:0.4;}
.vCalMonthDayHeader								{text-align:center;}							/*label des jours de la semaine*/
/*Cellules du jour*/
.vCalMonthDayCell								{color:#222; border-top:solid 1px #ddd; border-right:solid 1px #ccc; border-bottom:solid 1px #fff; background-color:#fff;}/*background-color à préciser pour le style "black"*/
.vCalMonthDayCell:hover, .vCalMonthToday		{background:#f9f9f9;}							/*Aujourd'hui ou jour survolé : bg du block*/
.vCalMonthToday .vCalMonthDayLabel				{color:#c00; font-size:1.1em;}					/*Aujourd'hui survolé : style du label*/
.vCalMonthDayOtherMonth .vCalMonthDayLabel		{opacity:0.3;}									/*jour d'un mois passé/futur : style du label*/
.vCalMonthDayPast .vCalEvtLabel					{opacity:0.7;}									/*jour passé : label de chaque événement (pas appliquer à tout le block)*/
.vCalMonthDayLabel								{height:28px;}									/*ligne du label du jour*/
.vCalMonthDayLabel>div							{display:inline-block; margin:5px 0px 0px 5px;}	/*ligne du label du jour : contenus*/
.vCalMonthDayCell:hover .vCalMonthDayLabel		{color:#c00;}									/*jour survolé : ligne du label*/
.vCalMonthImgAddEvt								{display:none;}									/*"Plus" d'ajout d'evt : masqué par défaut*/
.vCalMonthDayAddEvt:hover .vCalMonthImgAddEvt	{display:block; float:right;}					/*"Plus" d'ajout d'evt : affiche au survol du jour*/
.vCalMonthDayCelebration						{color:#070; font-style:italic;}				/*Jour férié*/

/*MOBILE*/
@media screen and (max-width:1023px){
	.vCalMonthDayLabel							{height:20px; font-size:0.9em!important; text-align:center;}
	.vCalMonthDayLabel>div						{margin:2px 0px 0px 2px;}
	.vCalMonthImgAddEvt							{height:20px;}
	.vCalMonthWeekNb, .vCalMonthDayCelebration	{display:none!important;}
	.vCalEvtBlock .vCalEvtLabel					{text-transform:lowercase; font-size:0.8em; line-height:0.8em;}
}

/* IMPRESSION */
@media print{
	.vCalMonthMain		{display:table; max-height:620px!important;}
	.vCalMonthDaysLine	{height:auto!important;}
	.vCalMonthWeekNb	{display:none!important;}
	.vCalMonthDayCell	{color:#222; border:solid 1px #ddd;}
	.vCalMonthDayLabel	{border:none!important;}
}
</style>

<script>
////	Gère l'affichage de la vue "month" (cf. "VueIndex.php")
function calendarDimensions()
{
	//largeur/hauteur des jours
	$(".vCalMonthDayHeader,.vCalMonthDayCell").css("width", Math.round(($(".vCalMonthMain").width()-$(".vCalMonthWeekNb").width()) / 7));
	var lineHeight=Math.round($(".vCalMonthMain:first").height() / $(".vCalMonthMain:first .vCalMonthDaysLine").length)-5;//-5 du "border"
	$(".vCalMonthDaysLine").css("height", lineHeight+"px");

	//Redimentionne chaque "vCalendarBlock"
	$(".vCalendarBlock").each(function(){
		var realHeight=$(this).find(".vCalendarHeader").height() + $(this).find(".vCalMonthMain").height() -2;
		if($(this).innerHeight()<realHeight)	{$(this).css("height",realHeight);}
	});
}
</script>
<?php } ?>


<div class="vCalMonthMain">
	<?php
	////	HEADER DES JOURS
	echo '<div>';
		for($cmpDay=1; $cmpDay<=7; $cmpDay++){
			$dayLabel=Txt::trad("day_".$cmpDay);
			if(Req::isMobile())	{$dayLabel=substr($dayLabel,0,3).".";}
			echo '<div class="vCalMonthDayHeader">'.$dayLabel.'</div>';
		}
		echo '<div class="vCalMonthWeekNb">&nbsp;</div>
	</div>';

	////	JOURS DU MOIS
	foreach($periodDays as $tmpDate=>$tmpDay)
	{
		////	AJOUTE UN DEBUT DE LIGNE & LE NUMERO DE LA SEMAINE
		if(date("N",$tmpDay["timeBegin"])==1)	{echo '<div class="vCalMonthDaysLine">';}

		////	AFFICHE LE JOUR
		$styleDayCell=$addEvtLink=null;
		//Lien pour ajouter ou proposer un evt
		if($tmpCal->addOrProposeEvt())  {$styleDayCell.="vCalMonthDayAddEvt";  $addEvtLink="onclick=\"lightboxOpen('".MdlCalendarEvent::getUrlNew()."&_idCal=".$tmpCal->_id."&newEvtTimeBegin=".strtotime(date("Y-m-d",$tmpDay["timeBegin"])." ".date("H:00"))."')\"";}
		//Styles de la cellule du jour
		if(date("m",$tmpDay["timeBegin"])!=date("m",$curTime))		{$styleDayCell.=" vCalMonthDayOtherMonth";}	//Jour d'un mois précédent/futur à celui affiché ?
		if($tmpDay["timeEnd"]<time())								{$styleDayCell.=" vCalMonthDayPast";}		//Jour déjà passé?
		elseif(date("Y-m-d",$tmpDay["timeBegin"])==date("Y-m-d"))	{$styleDayCell.=" vCalMonthToday";}			//Aujourd'hui?
		//Cellule du jour
		echo '<div class="vCalMonthDayCell '.$styleDayCell.'">';
			//LABEL DU JOUR ET BOUTON "ADD"
			echo '<div class="vCalMonthDayLabel">
					<div>'.date("j",$tmpDay["timeBegin"]).'</div><div class="vCalMonthDayCelebration">'.$tmpDay["celebrationDay"].'</div>
					<img src="app/img/plus.png" class="vCalMonthImgAddEvt" '.$addEvtLink.' title="'.Txt::tooltip($tmpCal->addEventLabel).'">
					</div>';
			//EVENEMENTS DU JOUR
			foreach($tmpCal->eventList[$tmpDate] as $tmpEvt){
				echo $tmpEvt->divContainerContextMenu("vCalEvtBlock", $tmpEvt->containerAttributes,  $tmpEvt->contextMenuOptions).
						'<div class="vCalEvtLabel" onclick="'.$tmpEvt->openVue().'">'.$tmpEvt->dateTimeLabel.Txt::reduce($tmpEvt->title,(Req::isMobile()?20:45)).$tmpEvt->importantIcon.'</div>
					</div>';
			}
		echo '</div>';

		////	FIN DE LIGNE DE LA SEMAINE && NUMERO DE FIN DE SEMAINE
		if(date("N",$tmpDay["timeBegin"])==7)
			{echo '<div class="vCalMonthWeekNb" onclick="redir(\'?ctrl=calendar&displayMode=week&curTime='.$tmpDay["timeBegin"].'\')" title="'.Txt::trad("CALENDAR_weekNb").' '.date("W",$tmpDay["timeBegin"]).'">'.date("W",$tmpDay["timeBegin"]).'</div></div>';}
	}
	?>
</div>