<?php if($tmpCal->isFirstCal==true){ ?>
<script>
/*******************************************************************************************
 *	AFFICHAGE DES AGENDAS
 *******************************************************************************************/
function calendarDisplay(isPrint)
{
	$(".vEvtBlock").css("max-width", ($(window).width()/7)-2 );										//Contient le width des evts (cf. affichage mobile. pas de $(".vMonthDayCell").width())
	$(".vEvtBlock").outerWidth($(".vMonthDayCell").width());										//Width des evts en fonction des cellules du jour
	$(".vCalMain").each(function(){																	//Pour chaque agenda :
		let contentHeight=$(this).find(".vCalHeader").height() + $(this).find(".vCalVue").height();	//- verif si ya beaucoup d'evt (contenu de l'agenda + grand que la page)
		if($(this).innerHeight()<contentHeight)  {$(this).height(contentHeight);}					//- actualise si besoin la hauteur de .vCalMain
	});
}
</script>

<style>
/*Conteneur principal + header + lignes*/
.vCalVue									{width:100%; max-width:100%; border-collapse:collapse;}
.vMonthYearWeekNum							{padding:3px; font-size:0.9em; opacity:0.5; text-align:center;}		/*numero des semaines dans l'année*/
.vMonthWeek									{height:17%; min-height:17%;}										/*Hauteur des lignes basé sur 6 semaines (soit 17%)*/
.vPublicHoliday								{color:#080; font-style:italic; margin-left:15px;}					/*Libellé du jour férié*/

/*Cellules du jour*/
.vMonthDayCell, .vCalLabelDays				{width:14.2%!important;}											/*Width des cellules et labels des jours (14.2% = 100/7)*/
.vMonthDayCell								{vertical-align:top; padding:0px; <?= Ctrl::$agora->skin=="white" ? "background:white;border:1px solid #dededf;color:#222;" : "background:black;border:1px solid #333;color:#fff;" ?>}
.vMonthDayCell:hover, .vMonthDayOtherMonth	{background:<?= Ctrl::$agora->skin=="white"?"#fafafa":"#222" ?>;}	/*jour survolé / jour d'un autre mois : bg du block*/
.vMonthDayLabel								{height:32px; padding:3px; line-height:26px;}						/*ligne du label du jour (numéro)*/
.vMonthDayLabel .vMonthAddEvt				{display:none;}														/*"Plus" d'ajout d'evt : masqué par défaut*/
.vMonthDayCell:hover .vMonthAddEvt			{display:block; float:right;}										/*-> affiche au survol du jour*/
.vMonthDayCell:hover .vMonthDayLabel		{color:#c00;}														/*jour survolé : ligne du label*/

/*evenements*/
.vEvtBlock									{width:0px; margin-bottom:2px;}			/*Width calculé via calendarDisplay()*/
.vEvtBlock .objMenuContextFloat				{top:2px; right:2px;}					/*Surchage le menu "burger"*/
.vEvtLabel									{white-space:nowrap;}					/*Texte sur une seule ligne*/
.vEvtLabelDate								{margin-left:3px; margin-right:5px;}

/*MOBILE*/
@media screen and (max-width:1024px){
	.vMonthDayLabel							{font-size:0.85em;}
	.vMonthDayLabel .vMonthAddEvt			{margin:0px;}
	.vEvtLabel								{font-size:13px; line-height:11px;}
	.vMonthYearWeekNum, .vPublicHoliday		{display:none!important;}
}
</style>
<?php } ?>


<table class="vCalVue">
	<?php
	////	HEADER : JOURS DE LA SEMAINE
	echo '<tr>';
			for($i=1; $i<=7; $i++)  {echo '<td class="vCalLabelDays">'.(Req::isMobile() ? substr(Txt::trad("day_".$i),0,3) : Txt::trad("day_".$i)).'</td>';}
			echo '<td class="vMonthYearWeekNum">&nbsp;</td>
		  </tr>';

	////	JOURS DU MOIS
	foreach($periodDays as $dayYmd=>$tmpDay)
	{
		////	PREMIER JOUR DE LA SEMAINE : LIGNE DE LA SEMAINE <TR> 
		if(date("N",$tmpDay["dayTimeBegin"])==1)  {echo '<tr class="vMonthWeek">';}

		////	INIT LA CELLULE DU JOUR
		$classDayOtherMonth=(date("m",$tmpDay["dayTimeBegin"])!=date("m",$curTime))  ?  "vMonthDayOtherMonth"  :  null;	//Class du jour du précédent/futur mois
		if($tmpCal->addOrProposeEvt()){																					//Bouton d'ajout d'evt
			$newEvtTimeBegin=strtotime($dayYmd." ".date("H:00"));														//Timestamp du nouvel evt
			$addEvtButton='<img src="app/img/plusSmall.png" class="vMonthAddEvt" onclick="lightboxOpen(\''.MdlCalendarEvent::getUrlNew().'&_idCal='.$tmpCal->_id.'&newEvtTimeBegin='.$newEvtTimeBegin.'\')" '.Txt::tooltip($tmpCal->addEventLabel).'>';
		}else{$addEvtButton=null;}

		////	BLOCK DU JOUR ET EVENEMENTS DU JOUR
		echo '<td class="vMonthDayCell '.$classDayOtherMonth.'">
				<div class="vMonthDayLabel">
					<span '.($dayYmd==date('Y-m-d')?'class="circleNb"':null).'>'.date("j",$tmpDay["dayTimeBegin"]).'</span>
					<span class="vPublicHoliday">'.$tmpDay["publicHoliday"].'</span>'.$addEvtButton.'
				</div>';
				foreach($tmpCal->evtListDays[$dayYmd] as $tmpEvt){
					echo $tmpEvt->objContainerMenu("vEvtBlock",$tmpEvt->evtAttributes,$tmpEvt->contextMenuOptions).
							'<div class="vEvtLabel" onclick="'.$tmpEvt->openVue().'" '.Txt::tooltip($tmpEvt->tooltip).'>
								<span class="vEvtLabelDate">'.Txt::dateLabel($tmpEvt->timeBegin,"mini").'</span>'.$tmpEvt->title.
							'</div>
						</div>';
				}
		echo '</td>';

		////	DERNIER JOUR DE LA SEMAINE : NUMERO DE SEMAINE DANS L'ANNEE + FIN DE LIGNE DE SEMAINE
		if(date("N",$tmpDay["dayTimeBegin"])==7){
			echo '<td class="vMonthYearWeekNum" onclick="redir(\'?ctrl=calendar&displayMode=week&curTime='.$tmpDay["dayTimeBegin"].'\')" '.Txt::tooltip(Txt::trad("CALENDAR_yearWeekNum")." ".date("W",$tmpDay["dayTimeBegin"])).'>'.date("W",$tmpDay["dayTimeBegin"]).'</td>
				</tr>';
		}
	}
	?>
</table>