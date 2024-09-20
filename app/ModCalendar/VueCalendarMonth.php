<?php if($tmpCal->isFirstCal==true){ ?>
<script>
/*******************************************************************************************
 *	DIMENSIONNE LES AGENDAS
 *******************************************************************************************/
function calendarDisplay()
{
	//// largeur/hauteur des jours
	let dayCellWidth=( ($(".vCalendarBlock").width() - $(".vMonthYearWeekNb").outerWidth(true)) / 7);				//Largeur de chaque jour
	$(".vMonthDayHeader, .vMonthDayCell").outerWidth(dayCellWidth,true);											//Width des cellules du jours (avec margins)
	$(".vEventBlock").outerWidth($(".vMonthDayCell").width(), true);												//Width des evts (avec margins) en fonction du width des cellules du jours
	let daysLineHeight=( ($(".vMonthMain").height()-$(".vMonthDayHeader").outerHeight(true)) / $(".vMonthMain:first .vMonthDaysLine").length);
	$(".vMonthDaysLine").outerHeight(daysLineHeight,true);															//Height des lignes des semaines (avec margins)

	//// Actualise la hauteur de chaque "vCalendarBlock" s'il ya beaucoup d'evt (contenu de l'agenda + grand que la page)
	$(".vCalendarBlock").each(function(){
		let contentHeight=$(this).find(".vCalendarHeader").height() + $(this).find(".vMonthMain").height();
		if($(this).innerHeight()<contentHeight)  {$(this).css("height",contentHeight);}
	});
}
</script>

<style>
/*conteneur principal et header*/
.vMonthMain								{width:100%; border-collapse:collapse;}
.vMonthDayHeader						{height:22px; text-align:center;}									/*label des jours de la semaine*/
.vMonthYearWeekNb						{width:15px; padding:2px; font-size:0.9em; opacity:0.4;}			/*numero des semaines dans l'année*/

/*Cellules du jour*/
.vMonthDayCell							{vertical-align:top; <?= Ctrl::$agora->skin=="white" ? "background:white;border:1px solid #dededf;color:#222;" : "background:black;border:1px solid #333;color:#fff;" ?>}
.vMonthDayCell:hover, .vMonthToday		{background:<?= Ctrl::$agora->skin=="white"?"#f9f9f9":"#222" ?>;}	/*Aujourd'hui ou jour survolé : bg du block*/
.vMonthToday .vMonthDayLabel			{color:#c00; font-size:1.1em;}										/*Aujourd'hui survolé : style du label*/
.vMonthDayOtherMonth .vMonthDayLabel	{opacity:0.3;}														/*jour d'un mois passé/futur : style du label*/
.vMonthDayLabel							{height:28px;}														/*ligne du label du jour (numéro)*/
.vMonthDayLabel>div						{display:inline-block; margin:5px 0px 0px 5px;}						/*ligne du label du jour : contenus*/
.vMonthDayCell:hover .vMonthDayLabel	{color:#c00;}														/*jour survolé : ligne du label*/
.vMonthDayCelebration					{color:#070; font-style:italic; margin-left:10px;}					/*Jour férié*/
.vMonthAddEvt							{display:none; float:right;}										/*"Plus" d'ajout d'evt : masqué par défaut*/
.vMonthDayCell:hover .vMonthAddEvt		{display:block;}													/*-> affiche au survol du jour*/

/*evenements*/
.vEventBlock							{width:0px; height:20px; min-height:20px; margin-bottom:2px;}		/*width à 0px par défaut, puis calculé via calendarDisplay()*/
.vEventLabel							{white-space:nowrap; text-overflow:ellipsis;}						/*Sur une seule ligne, ellipsis pour afficher '...' si le texte dépasse*/

/*MOBILE*/
@media screen and (max-width:1023px){
	.vMonthDayHeader							{font-size:0.9em;}
	.vMonthDayLabel								{height:20px; font-size:0.9em!important; text-align:center;}
	.vMonthDayLabel>div							{margin:2px 0px 0px 2px;}
	.vMonthAddEvt								{height:20px;}
	.vMonthYearWeekNb, .vMonthDayCelebration	{display:none!important;}
	.vEventBlock								{height:20px; min-height:24px;}
	.vEventLabel								{white-space:normal;}/*sur plusieurs lignes*/
}
</style>
<?php } ?>


<table class="vMonthMain">
	<?php
	////	HEADER : JOURS DE LA SEMAINE
	echo '<tr>';
		for($i=1; $i<=7; $i++)  {echo '<td class="vMonthDayHeader">'.(Req::isMobile() ? substr(Txt::trad("day_".$i),0,3) : Txt::trad("day_".$i)).'</td>';}
		echo '<td class="vMonthYearWeekNb">&nbsp;</td>
		 </tr>';

	////	JOURS DU MOIS
	foreach($periodDays as $tmpDate=>$tmpDay)
	{
		////	PREMIER JOUR DE LA SEMAINE : TR
		if(date("N",$tmpDay["timeBegin"])==1)  {echo '<tr class="vMonthDaysLine">';}

		////	INIT LA CELLULE DU JOUR
		$dayStyle=$buttonAddEvt=null;
		if(date("m",$tmpDay["timeBegin"])!=date("m",$curTime))		{$dayStyle.=" vMonthDayOtherMonth";}	//Jour du précédent/futur mois
		elseif(date("Y-m-d",$tmpDay["timeBegin"])==date("Y-m-d"))	{$dayStyle.=" vMonthToday";}			//Aujourd'hui
		if($tmpCal->addOrProposeEvt())  {$buttonAddEvt='<img src="app/img/plus.png" class="vMonthAddEvt" onclick="lightboxOpen(\''.MdlCalendarEvent::getUrlNew().'&_idCal='.$tmpCal->_id.'&newEvtTimeBegin='.strtotime(date("Y-m-d",$tmpDay["timeBegin"]).' '.date("H:00")).'\')" title="'.Txt::tooltip($tmpCal->addEventLabel).'">';}

		////	EVENEMENTS DU JOUR  (".vEventBlock")
		$dayEvents=null;
		foreach($tmpCal->eventList[$tmpDate] as $tmpEvt){
			$dayEvents.=$tmpEvt->divContainerContextMenu($tmpEvt->containerClass, $tmpEvt->containerAttributes,  $tmpEvt->contextMenuOptions).'
							<div class="vEventLabel" onclick="'.$tmpEvt->openVue().'" title="'.$tmpEvt->titleTooltip.'">'.$tmpEvt->title.$tmpEvt->importantIcon.'</div>
						</div>';
		}

		////	AFFICHE LE JOUR ET SES EVENEMENTS
		echo '<td class="vMonthDayCell '.$dayStyle.'">
				<div class="vMonthDayLabel">'.date("j",$tmpDay["timeBegin"]).'<span class="vMonthDayCelebration">'.$tmpDay["celebrationDay"].'</span>'.$buttonAddEvt.'</div>'
				.$dayEvents.
			 '</td>';

		////	DERNIER JOUR DE LA SEMAINE : NUMERO DE SEMAINE DANS L'ANNEE + FIN DE LIGNE
		if(date("N",$tmpDay["timeBegin"])==7){
			echo '<td class="vMonthYearWeekNb noPrint" onclick="redir(\'?ctrl=calendar&displayMode=week&curTime='.$tmpDay["timeBegin"].'\')" title="'.Txt::trad("CALENDAR_weekNb").' '.date("W",$tmpDay["timeBegin"]).'">'.date("W",$tmpDay["timeBegin"]).'</td>
				</tr>';
		}
	}
	?>
</table>