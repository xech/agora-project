<script>
////	SUR MOBILE : PULSATE DU MENU
if(isMobile()){
	if($("#mainMenuElems").exist()) {$("#mainMenuElems").pulsate();}		//Pulsate le menu des nouveaux élément dans le "DashboardNews"
	else							{$("#headerModuleMobile").pulsate(3);}	//Pulsate l'icone du module dans le "vueheadermenu.php
}

////	CONFIRMATION / ANNULATION DE PROPOSITION D'ÉVÉNEMENT (via "jquery-confirm")
function eventPropositionConfirm(_idCal, _idEvt, eventPropositionDivId)
{
	var ajaxUrl="?ctrl=calendar&action=eventPropositionConfirm&typeId=calendar-"+_idCal+"&_idEvt="+_idEvt;
	$.confirm({
		title:"<?= Txt::trad("CALENDAR_evtProposed") ?>",					//Titre principal
		content:$("#"+eventPropositionDivId).attr("data-confirmDetails"),	//Détails de l'événement (on n'utilise pas le "title" car supprimé par "tooltipster" au moment de l'affichage!)
		useBootstrap:false,													//Pas de dépendence à bootstrap
		theme:"modern",														//Theme "modern" centré sur la page
		closeIcon:true,														//Icone "close"
		buttons:{
			propositionConfirm:{
				btnClass:"btn-green",
				text:"<?= Txt::trad("CALENDAR_evtProposedConfirm") ?>",
				action:function(){  $.ajax(ajaxUrl+"&isConfirmed=true").done(function(){ redir("?ctrl=calendar&notify=CALENDAR_evtProposedConfirmBis"); });  }	//Confirme la proposition & Reload la page avec notif
			},
			propositionDelete:{
				btnClass: "btn-blue",
				text: "<?= Txt::trad("CALENDAR_evtProposedDecline") ?>",
				action:function(){  $.ajax(ajaxUrl).done(function(){ redir("?ctrl=calendar&notify=CALENDAR_evtProposedDeclineBis"); });  }						//Décline la proposition & Reload la page avec notif
			},
			cancel:{
				text:"<?= Txt::trad("cancel") ?>"
			}
		}
	});
}
</script>

<style>
.calEventProposition			{padding:8px; background-color:rgb(248, 236, 227); border-radius:5px;}
.calEventProposition>img		{float:right; margin:0px; margin-left:3px; max-width:18px;}
.calEventProposition>label		{display:block; margin:0px; padding:10px;}
.calEventProposition li			{margin:0px; margin-left:20px; padding:3px; cursor:pointer;}
.calEventProposition li:hover	{background-color:#fff;}
</style>


<?php
////	Début et titre du block "List"
echo "<ul class='calEventProposition'><img src='app/img/important.png' class='pulsate'>".Txt::trad("CALENDAR_evtProposed");

////	Affiche chaque proposition d'événement 
foreach($eventPropositions as $cpt=>$tmpProposition)
{
	////	Récupère l'evt et l'agenda concernés par la proposition
	$tmpEvt=$tmpProposition["evt"];
	$tmpCal=$tmpProposition["cal"];
	////	Affiche le titre de l'agenda pour les propositions suivantes
	if(empty($curCalTitle) || $curCalTitle!=$tmpCal->title){
		echo "<label>".ucfirst(Txt::trad("OBJECTcalendar"))." <i>".$tmpCal->title."</i> :</label>";
		$curCalTitle=$tmpCal->title;//retient le titre courant
	}
	////	Affiche la proposition d'événement dans un tooltip
	$confirmDetails=htmlspecialchars($tmpEvt->title)." : ".Txt::dateLabel($tmpEvt->dateBegin,"normal",$tmpEvt->dateEnd)."<hr>".
					Txt::trad("CALENDAR_evtProposedBy")." ".$tmpEvt->autorLabel()."<hr>".
					ucfirst(Txt::trad("OBJECTcalendar"))." : ".$tmpCal->title;
	if($tmpEvt->description)  {$confirmDetails.="<hr>".ucfirst(Txt::trad("description"))." : ".Txt::reduce($tmpEvt->description);}
	echo '<li id="eventPropositionDiv'.$tmpEvt->_id.'" data-confirmDetails="'.Txt::tooltip($confirmDetails).'" onclick="eventPropositionConfirm('.$tmpCal->_id.','.$tmpEvt->_id.',this.id)">'.$tmpEvt->title.'</li>';
}

////	Fin du block "List"
echo "</ul>";