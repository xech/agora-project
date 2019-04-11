<?php if($cptCal==0){ ?>
<style>
/*conteneur principal et header*/
.vCalMonthMain						{display:table; width:100%; height:100%;}
.vCalMonthMain>div					{display:table-row;}
.vCalMonthMain>div>div				{display:table-cell;}
.vCalMonthMain>div:first-child		{height:15px;}/*ligne du header*/
.vCalMonthWeekNb					{width:15px; vertical-align:middle; font-size:0.9em; opacity:0.4;}
.vCalMonthDayHeader					{text-align:center;}
/*Cellules du jour*/
.vCalMonthDayCell							{color:#222; border-top:solid 1px #ddd; border-right:solid 1px #ccc; border-bottom:solid 1px #fff; background-color:#fff;}/*background-color à préciser pour le style "black"*/
.vCalMonthToday								{background:#f8f8f8;}
.vCalMonthToday .vCalMonthDayLabel			{color:#c00; background:#eee;}
.vCalMonthDayOtherMonth .vCalMonthDayLabel	{opacity:0.3;}/*jours passés ou jours du futur mois*/
.vCalMonthDayPast .vCalEvtLabel				{opacity:0.7;}/*Idem : applique au label des Evenements (pas tout le block!)*/
.vCalMonthDayLabel							{padding-top:8px; padding-left:8px;}
.vCalMonthDayCell:hover						{background:#fcfcfc;}
.vCalMonthDayCell:hover .vCalMonthDayLabel	{color:#c00;}
.vCalMonthDayCell.sLink:hover .vCalMonthDayAddEvt	{display:block; float:right; margin-right:5px;}/*Affiche si besoin le "+" au survol du block du jour*/
.vCalMonthDayAddEvt							{display:none;}
.vCalMonthDayCelebration					{color:#070; font-style:italic; margin-left:7px;}

/*RESPONSIVE*/
@media screen and (max-width:1023px){
	.vCalMonthDayHeader, .vCalMonthDayLabel		{font-size:0.9em;}
	.vCalMonthWeekNb, .vCalMonthDayCelebration	{display:none!important;}
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
	var lineHeight=Math.round($(".vCalMonthMain:first").height() / $(".vCalMonthMain:first .vCalMonthDaysLine").length)-4;//-4 pour prendre en compte le border
	$(".vCalMonthDaysLine").css("height", lineHeight+"px");

	//Redimentionne "vCalendarBlock" si la hauteur réelle est supérieure à "availableContentHeight()"
	$(".vCalendarBlock").each(function(){
		var realHeight=$(this).find(".vCalendarHeader").height() + $(this).find(".vCalMonthMain").height() -2;
		if($(this).innerHeight()<realHeight)	{$(this).css("height",realHeight);}
	});
}
</script>
<?php } ?>


<div class="vCalMonthMain">
	<!--HEADER DES JOURS-->
	<div>
		<?php
		for($cmpDay=1; $cmpDay<=7; $cmpDay++){
			$dayLabel=Txt::trad("day_".$cmpDay);
			if(Req::isMobile())	{$dayLabel=substr($dayLabel,0,3).".";}
			echo "<div class='vCalMonthDayHeader'>".$dayLabel."</div>";
		}
		?>
		<div class="vCalMonthWeekNb">&nbsp;</div>
	</div>

	<?php
	////	JOURS DU MOIS
	foreach($periodDays as $tmpDay)
	{
		////	AJOUTE UN DEBUT DE LIGNE & LE NUMERO DE LA SEMAINE
		if(date("N",$tmpDay["timeBegin"])==1)	{echo "<div class='vCalMonthDaysLine'>";}

		////	AFFICHE LE JOUR
		//Lien pour ajouter un evt (sauf pour les guests)
		$onclickAddEvt=(Ctrl::$curUser->isUser())  ? "onclick=\"lightboxOpen('".MdlCalendarEvent::getUrlNew()."&_idCal=".$tmpCal->_id."&newEvtTimeBegin=".strtotime(date("Y-m-d",$tmpDay["timeBegin"])." ".date("H:00"))."')\""  :  null;
		//Styles de la cellule du jour
		$styleDay=(!empty($onclickAddEvt))  ?  "sLink"  :  null;										//Init : ajout d'evt possible?
		if(date("m",$tmpDay["timeBegin"])!=date("m",$curTime))	{$styleDay.=" vCalMonthDayOtherMonth";}	//Jour du mois précédent/futur (à celui affiché)
		if($tmpDay["timeEnd"]<time())							{$styleDay.=" vCalMonthDayPast";}		//Jour déjà passé
		if(date("Y-m-d",$tmpDay["timeBegin"])==date("Y-m-d"))	{$styleDay.=" vCalMonthToday";}			//Aujourd'hui
		//Cellule du jour
		echo "<div class='vCalMonthDayCell ".$styleDay."' ".$onclickAddEvt.">
				<div class='vCalMonthDayLabel'>
					".date("j",$tmpDay["timeBegin"])."<span class='vCalMonthDayCelebration'>".$tmpDay["celebrationDay"]."</span>
					<img src='app/img/plusSmall.png' class='vCalMonthDayAddEvt' title=\"".$txtAddEvt."\">
				</div>";
				//EVENEMENTS DU JOUR
				foreach($eventList[$tmpDay["date"]] as $tmpEvt){
					$titleMaxSize=Req::isMobile() ? 20 : 45;//Voir en 1366x768
					$tmpEvtTooltip=ucfirst(Txt::displayDate($tmpEvt->dateBegin,"full",$tmpEvt->dateEnd))." : ".$tmpEvt->title;
					$tmpEvtImportant=(!empty($tmpEvt->important))  ?  " <img src='app/img/important.png'>"  :  null;
					$evtAttr="onclick=\"event.stopPropagation();lightboxOpen('".$tmpEvt->getUrl("vue")."')\" data-catColor='".$tmpEvt->catColor."' ";
					echo $tmpEvt->divContainer("vCalEvtBlock",$evtAttr).$tmpEvt->contextMenu(["inlineLauncher"=>true,"_idCal"=>$tmpCal->_id,"curDateTime"=>strtotime($tmpEvt->dateBegin)])."
							<div class='vCalEvtLabel' title=\"".$tmpEvtTooltip."\">".Txt::displayDate($tmpEvt->dateBegin,"mini",$tmpEvt->dateEnd).": ".Txt::reduce($tmpEvt->title,$titleMaxSize).$tmpEvtImportant."</div>
						 </div>";
				}
		echo "</div>";

		////	FIN DE LIGNE DE LA SEMAINE && NUMERO DE FIN DE SEMAINE
		if(date("N",$tmpDay["timeBegin"])==7)	{echo "<div class='vCalMonthWeekNb sLink' onClick=\"redir('?ctrl=calendar&displayMode=week&curTime=".$tmpDay["timeBegin"]."')\" title=\"".Txt::trad("CALENDAR_weekNb")." ".date("W",$tmpDay["timeBegin"])."\">".date("W",$tmpDay["timeBegin"])."</div></div>";}
	}
	?>
</div>