<script>
////	Resize
lightboxSetWidth(600);

////	Agenda partagé d'espace : titre et description non modifiable
<?php if($curObj->isSpacelCalendar()){ ?>
$(function(){
	$(".inputTitleName, .descriptionTextarea textarea").prop("readonly","readonly");
});
<?php } ?>

////	Controle spécifique à l'objet (cf. "VueObjEditMenuSubmit.php")
function objectFormControl(){
	return new Promise((resolve)=>{
		//vérif si un autre element porte le même nom
		var ajaxUrl="?ctrl=object&action=ControlDuplicateName&typeId=<?= $curObj->_typeId ?>&controledName="+encodeURIComponent($("[name='title']").val());
		$.ajax(ajaxUrl).done(function(result){
			if(/duplicate/i.test(result))	{notify("<?= Txt::trad("NOTIF_duplicateName") ?>");  resolve(false);}	//"Un autre element porte le même nom"
			else							{resolve(true);}														//Sinon renvoie le résultat du controle principal
		});
	});
}
</script>


<style>
.inputTitleName			{width:75%;}/*surcharge*/
.vCalOption				{margin-top:20px;}
#divPropositionGuest	{<?= $hidePropositionGuest==true ? "display:none" : null ?>}
</style>


<form action="index.php" method="post" id="mainForm" enctype="multipart/form-data">
	
	<!--TITRE / DESCRIPTION (SAUF AGENDA D'USERS)-->
	<?php if($curObj->type!="user"){ ?>
		<input type="text" name="title" value="<?= $curObj->title ?>" class="inputTitleName" placeholder="<?= Txt::trad("title") ?>" >
		<?= $curObj->editDescription() ?>
	<?php } ?>

	<?php
	////	PLAGE HORAIRE EN AFFICHAGE SEMAINE
	$timeSlotBegin=$timeSlotEnd=null;
	for($h=1; $h<24; $h++)  {$timeSlotBegin.="<option value='".$h."' ".($curObj->timeSlotBegin==$h?"selected":null).">".$h."h</option>";}
	for($h=1; $h<24; $h++)  {$timeSlotEnd  .="<option value='".$h."' ".($curObj->timeSlotEnd==$h?"selected":null).">".$h."h</option>";}
	echo "<div class='vCalOption'>".Txt::trad("CALENDAR_timeSlot")." : <select name='timeSlotBegin'>".$timeSlotBegin."</select> &nbsp; ".Txt::trad("at")." &nbsp; <select name='timeSlotEnd'>".$timeSlotEnd."</select></div>";

	////	OPTION DE NOTIFICATION PAR EMAIL À CHAQUE PROPOSITION D'ÉVÉNEMENT
	echo "<div class='vCalOption' ".Txt::tooltip("CALENDAR_propositionNotifTooltip").">
			<input type='checkbox' name='propositionNotify' value='1' ".(!empty($curObj->propositionNotify)?'checked':null)." id='inputPropositionNotify'>
			<label for='inputPropositionNotify'>".Txt::trad("CALENDAR_propositionNotif")." <img src='app/img/mail.png'></label>
		  </div>";

	////	OPTION DE PROPOSITION D'ÉVÉNEMENT POUR LES GUESTS
	echo "<div class='vCalOption' id='divPropositionGuest' ".Txt::tooltip("CALENDAR_propositionGuestTooltip").">
			<input type='checkbox' name='propositionGuest' value='1' ".(!empty($curObj->propositionGuest)?'checked':null)." id='inputPropositionGuest'>
			<label for='inputPropositionGuest'>".Txt::trad("CALENDAR_propositionGuest")." <img src='app/img/user/accessGuest.png'></label>
		  </div>";

	////	MENU COMMUN
	echo $curObj->editMenuSubmit();
	?>
</form>