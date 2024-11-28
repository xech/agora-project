<?php if($tmpCal->isFirstCal==true){ ?>
<script>
/*******************************************************************************************
 *	DIMENSIONNE LES AGENDAS
 *******************************************************************************************/
function calendarDisplay(isPrint)
{
	if(isPrint==true)  {$(".vMonthWeek").outerHeight( (650/$(".vCalVue tr").length) );}				//Print : height fixe des lignes de semaine (tester en 1500x850)
	if(isMobile())  {$(".vEvtBlock").css("max-width", ($(window).width()/7) );}						//Contient le width des evts (pb récurrent sur le calcul suivant de "$(".vMonthDayCell").width()" )
	$(".vEvtBlock").outerWidth( $(".vMonthDayCell").width() );										//Width des evts en fonction des cellules du jour
	$(".vCalMain").each(function(){																	//Pour chaque agenda :
		let contentHeight=$(this).find(".vCalHeader").height() + $(this).find(".vCalVue").height();	//- verif si ya beaucoup d'evt (contenu de l'agenda + grand que la page)
		if($(this).innerHeight()<contentHeight)  {$(this).height(contentHeight);}					//- actualise si besoin la hauteur de .vCalMain
	});
}
</script>

<style>
/*Conteneur principal + header + lignes*/
.vCalVue									{width:100%; max-width:100%; border-collapse:collapse;}
.vMonthYearWeekNum							{padding:3px; font-size:0.9em; opacity:0.4; text-align:center;}				/*numero des semaines dans l'année*/
.vMonthWeek									{height:17%; min-height:17%;}												/*Hauteur des lignes basé sur 6 semaines (soit 17%)*/

/*Cellules du jour*/
.vMonthDayCell, .vCalLabelWeekDays			{width:14.2%!important;}													/*Width des cellules et labels des jours (14.2% = 100/7)*/
.vMonthDayCell								{vertical-align:top; padding:0px; <?= Ctrl::$agora->skin=="white" ? "background:white;border:1px solid #dededf;color:#222;" : "background:black;border:1px solid #333;color:#fff;" ?>}
.vMonthDayCell:hover, .vMonthDayOtherMonth	{background:<?= Ctrl::$agora->skin=="white"?"#fcfcfc":"#222" ?>;}			/*jour survolé / jour d'un autre mois : bg du block*/
.vMonthDayOtherMonth .vMonthDayLabel		{opacity:0.5;}																/*jour d'un mois passé/futur : style du label*/
.vMonthDayLabel								{height:28px; padding:4px;}													/*ligne du label du jour (numéro)*/
.vMonthDayLabel .vMonthAddEvt				{display:none;}																/*"Plus" d'ajout d'evt : masqué par défaut*/
.vMonthDayCell:hover .vMonthAddEvt			{display:block; float:right;}												/*-> affiche au survol du jour*/
.vMonthDayCell:hover .vMonthDayLabel		{color:#c00;}																/*jour survolé : ligne du label*/
.vMonthDayCelebration						{color:#070; font-style:italic; margin-left:10px;}							/*Jour férié*/

/*evenements*/
.vEvtBlock									{width:0px; height:20px; min-height:20px; padding:3px; margin-bottom:2px;}	/*Width calculé via calendarDisplay()*/
.vEvtBlock .objMenuContextFloat				{top:2px; right:2px;}														/*Surchage le menu "burger"*/
.vEvtLabel									{white-space:nowrap;}														/*Sur une seule ligne*/

/*MOBILE*/
@media screen and (max-width:1024px){
	.vMonthDayLabel								{font-size:0.85em;}
	.vMonthDayLabel .vMonthAddEvt				{margin:0px;}
	.vEvtBlock									{height:18px; min-height:18px; padding:2px!important;}/*pas de padding "padding-right" (cf. menu context)*/
	.vEvtLabel									{font-size:0.8em;}
	.vMonthYearWeekNum, .vMonthDayCelebration	{display:none!important;}
}
</style>
<?php } ?>


<table class="vCalVue">
	<?php
	////	HEADER : JOURS DE LA SEMAINE
	echo '<tr>';
		for($i=1; $i<=7; $i++)  {echo '<td class="vCalLabelWeekDays">'.(Req::isMobile() ? substr(Txt::trad("day_".$i),0,3) : Txt::trad("day_".$i)).'</td>';}
		echo '<td class="vMonthYearWeekNum">&nbsp;</td>
		 </tr>';

	////	JOURS DU MOIS
	foreach($periodDays as $tmpDate=>$tmpDay)
	{
		////	PREMIER JOUR DE LA SEMAINE : LIGNE DE LA SEMAINE <TR> 
		if(date("N",$tmpDay["timeBegin"])==1)  {echo '<tr class="vMonthWeek">';}

		////	INIT LA CELLULE DU JOUR
		$dayCellClass=(date("m",$tmpDay["timeBegin"])!=date("m",$curTime))  ?  "vMonthDayOtherMonth"  :  null;	//Jour du précédent/futur mois
		$dayLabelClass=(date("Y-m-d",$tmpDay["timeBegin"])==date("Y-m-d"))  ?  "vCalLabelToday"  :  null;		//Aujourd'hui
		if($tmpCal->addOrProposeEvt()){																			//Bouton d'ajout d'evt
			$newEvtTimeBegin=strtotime(date("Y-m-d",$tmpDay["timeBegin"])." ".date("H:00"));					//Timestamp du début de l'evt
			$addEvtButton='<img src="app/img/plusSmall.png" class="vMonthAddEvt" onclick="lightboxOpen(\''.MdlCalendarEvent::getUrlNew().'&_idCal='.$tmpCal->_id.'&newEvtTimeBegin='.$newEvtTimeBegin.'\')" '.Txt::tooltip($tmpCal->addEventLabel).'>';
		}else{$addEvtButton=null;}

		////	BLOCK DU JOUR ET EVENEMENTS DU JOUR (".vEvtBlock")
		echo '<td class="vMonthDayCell '.$dayCellClass.'">
				<div class="vMonthDayLabel '.$dayLabelClass.'">'.date("j",$tmpDay["timeBegin"]).'<span class="vMonthDayCelebration">'.$tmpDay["celebrationDay"].'</span>'.$addEvtButton.'</div>';
				foreach($tmpCal->eventList[$tmpDate] as $tmpEvt){
					echo $tmpEvt->divContainerContextMenu($tmpEvt->containerClass, $tmpEvt->containerAttributes,  $tmpEvt->contextMenuOptions).'
							<div class="vEvtLabel" onclick="'.$tmpEvt->openVue().'" '.Txt::tooltip($tmpEvt->tooltip).'>'.$tmpEvt->title.'</div>
						</div>';
				}
		echo '</td>';

		////	DERNIER JOUR DE LA SEMAINE : NUMERO DE SEMAINE DANS L'ANNEE + FIN DE LIGNE DE SEMAINE
		if(date("N",$tmpDay["timeBegin"])==7){
			echo '<td class="vMonthYearWeekNum" onclick="redir(\'?ctrl=calendar&displayMode=week&curTime='.$tmpDay["timeBegin"].'\')" '.Txt::tooltip(Txt::trad("CALENDAR_yearWeekNum")." ".date("W",$tmpDay["timeBegin"])).'>'.date("W",$tmpDay["timeBegin"]).'</td>
				</tr>';
		}
	}
	?>
</table>