<?php if($tmpCal->isFirstCal==true){ ?>
<script>
/********************************************************************************************************
 *	AFFICHAGE DES AGENDAS
 *******************************************************************************************/
function calendarDisplay(isPrint)
{
	let monthCellWidth=Math.floor((containerWidth-$(".vMonthWeekNbYear").width()) / 7) - 2;			//Calcul le width des cellules du mois (containerWidth cf "app.js" puis -2px de border)
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
.vMonthWeekNbYear							{width:15px; font-size:0.9em; opacity:0.5; text-align:center;}		/*numero des semaines dans l'année*/
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
.vEvtBlock .objMenuContextFloat				{top:2px; right:2px;}					/*Surchage le menu "burger"*/
.vEvtLabel									{white-space:nowrap;}					/*Texte sur une seule ligne*/
.vEvtLabelDate								{margin-left:3px; margin-right:5px;}

/*RESPONSIVE SMALL*/
@media screen and (max-width:1024px){
	.vMonthDayLabel							{font-size:0.85em;}
	.vMonthDayLabel .vMonthAddEvt			{margin:0px;}
	.vEvtLabel								{font-size:13px; line-height:11px;}
	.vMonthWeekNbYear, .vPublicHoliday		{display:none!important;}
}
</style>
<?php } ?>


<table class="vCalVue">
	<?php
	////	HEADER : JOURS DE LA SEMAINE
	echo '<tr>';
			for($i=1; $i<=7; $i++)  {echo '<td class="vCalLabelDays">'.(Req::isMobile() ? substr(Txt::trad("day_".$i),0,3) : Txt::trad("day_".$i)).'</td>';}
			echo '<td class="vMonthWeekNbYear">&nbsp;</td>
		  </tr>';

	////	JOURS DU MOIS
	foreach($periodDays as $dayYmd=>$tmpDay)
	{
		////	PREMIER JOUR DE LA SEMAINE : LIGNE DE LA SEMAINE <TR> 
		if(date("N",$tmpDay["dayTimeBegin"])==1)  {echo '<tr class="vMonthWeek">';}

		////	INIT LA CELLULE DU JOUR
		$classDayOtherMonth=(date("m",$tmpDay["dayTimeBegin"])!=date("m",$curTime))  ?  "vMonthDayOtherMonth"  :  null;	//Class du jour du précédent/futur mois
		if($tmpCal->affectationAddRight()){																				//Proposer/Ajouter un evt
			$newEvtTimeBegin=strtotime($dayYmd." ".date("H:00"));														//Timestamp du nouvel evt
			$addEvtButton='<img src="app/img/plusSmall.png" class="vMonthAddEvt" onclick="lightboxOpen(\''.MdlCalendarEvent::getUrlNew().'&_idCal='.$tmpCal->_id.'&newEvtTimeBegin='.$newEvtTimeBegin.'\')" '.$tmpCal->addEvtTooltip.'>';
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
			echo '<td class="vMonthWeekNbYear" onclick="redir(\'?ctrl=calendar&calendarDisplayMode=week&curTime='.$tmpDay["dayTimeBegin"].'\')" '.Txt::tooltip(Txt::trad("CALENDAR_yearWeekNum")." ".date("W",$tmpDay["dayTimeBegin"])).'>'.date("W",$tmpDay["dayTimeBegin"]).'</td>
				</tr>';
		}
	}
	?>
</table>