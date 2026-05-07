<script>
////	Agenda partagé d'espace : titre non modifiable
<?php if($curObj->isSpacelCalendar()){ ?>
ready(function(){
	$(".inputTitleName").prop("readonly","readonly");
});
<?php } ?>

////	"Tous les utilisateur et invités" en écriture : "pulsate" l'option "Les invités peuvent proposer des événements"
ready(function(){
	$("input[name='objectRight[]']:not([value$='spaceUsers_1'])").on("change",function(){
		$("#divPropositionGuest").pulsate();
	});
});

////	Controle spécifique du formulaire (cf. "VueObjMenuEdit.php")
function mainFormControl(){
	return new Promise((resolve)=>{
		var ajaxUrl="?ctrl=object&action=ControlDuplicateName&typeId=<?= $curObj->typeId ?>&controledName="+encodeURIComponent($("[name='title']").val());
		$.ajax(ajaxUrl).done(function(result){
			if(/duplicateName/i.test(result))	{resolve(false);  notify("<?= Txt::trad("NOTIF_duplicateName") ?>");}//"..le nom existe déjà"
			else								{resolve(true);}
		});
	});
}
</script>


<style>
#bodyLightbox			{max-width:700px;}
.vCalOption				{margin-top:20px;}
#divPropositionGuest	{<?= $hidePropositionGuest==true ? "display:none" : null ?>}
</style>


<form action="index.php" method="post" id="mainForm" enctype="multipart/form-data">
	
	<!--TITRE / DESCRIPTION (SAUF AGENDA D'USERS)-->
	<?php if($curObj->type!="user"){ ?>
		<input type="text" name="title" value="<?= $curObj->title ?>" class="inputTitleName" placeholder="<?= Txt::trad("title") ?>" >
		<?= $curObj->descriptionEditor() ?>
	<?php } ?>

	<!--PLAGE HORAIRE EN AFFICHAGE SEMAINE-->
	<?php
	$timeSlotBegin=$timeSlotEnd=null;
	for($h=1; $h<24; $h++)  {$timeSlotBegin.="<option value='".$h."' ".($curObj->timeSlotBegin==$h?"selected":null).">".$h."h</option>";}
	for($h=1; $h<24; $h++)  {$timeSlotEnd  .="<option value='".$h."' ".($curObj->timeSlotEnd==$h?"selected":null).">".$h."h</option>";}
	?>
	<div class="vCalOption">
		<?= Txt::trad("CALENDAR_timeSlot") ?> : 
		<select name="timeSlotBegin"><?= $timeSlotBegin ?></select>
		&nbsp; <?= Txt::trad("to") ?> &nbsp; 
		<select name="timeSlotEnd"><?= $timeSlotEnd ?></select>
	</div>

	<!--OPTION DE NOTIFICATION PAR EMAIL À CHAQUE PROPOSITION D'ÉVÉNEMENT-->
	<div class="vCalOption" <?= Txt::tooltip("CALENDAR_propositionNotifTooltip") ?> >
		<input type="checkbox" name="propositionNotify" value="1" <?= !empty($curObj->propositionNotify)?'checked':null ?> id="inputPropositionNotify">
		<label for="inputPropositionNotify"><?= Txt::trad("CALENDAR_propositionNotif") ?> <img src="app/img/mail.png"></label>
	</div>

	<!--OPTION DE PROPOSITION D'ÉVÉNEMENT POUR LES GUESTS-->
	<div class="vCalOption" id="divPropositionGuest" <?= Txt::tooltip("CALENDAR_propositionGuestTooltip") ?> >
		<input type="checkbox" name="propositionGuest" value="1" <?= !empty($curObj->propositionGuest)?'checked':null ?> id="inputPropositionGuest">
		<label for="inputPropositionGuest"><?= Txt::trad("CALENDAR_propositionGuest") ?> <img src="app/img/plusSmall.png"></label>
	</div>

	<!--MENU COMMUN-->
	<?= $curObj->editMenuSubmit() ?>
</form>