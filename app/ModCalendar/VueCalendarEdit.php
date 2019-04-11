<script>
////	Resize
lightboxSetWidth(650);

////	Contrôle du formulaire
function formControl()
{
	//Controle final (champs obligatoires, affectations/droits d'accès, etc)
	return mainFormControl();
}
</script>


<style>
textarea[name='description']	{<?= empty($curObj->description)?"display:none;":null ?>}
</style>


<form action="index.php" method="post" onsubmit="return formControl()" enctype="multipart/form-data" class="lightboxContent">

	<!--TITRE & DESCRIPTION (sauf type "user")-->
	<?php if($curObj->type!="user"){ ?>
	<input type="text" name="title" value="<?= $curObj->title ?>" class="textBig" placeholder="<?= Txt::trad("title") ?>">
	<img src="app/img/description.png" class="sLink" title="<?= Txt::trad("description") ?>" onclick="$('textarea[name=description]').slideToggle();">
	<br><br>
	<textarea name="description" placeholder="<?= Txt::trad("description") ?>"><?= $curObj->description ?></textarea>
	<?php } ?>

	<!--PLAGE HORAIRE-->
	<div class="objField">
		<?= Txt::trad("CALENDAR_timeSlot") ?>
		<select name="timeSlotBegin">
			<?php for($h=1; $h<24; $h++){ ?>
			<option value="<?= $h ?>" <?= ($curObj->timeSlotBegin==$h)?"selected":null ?>><?= $h ?>h</option>
			<?php } ?>
		</select>
		<?= Txt::trad("at") ?>
		<select name="timeSlotEnd">
			<?php for($h=1; $h<24; $h++){ ?>
			<option value="<?= $h ?>" <?= ($curObj->timeSlotEnd==$h)?"selected":null ?>><?= $h ?>h</option>
			<?php } ?>
		</select>
	</div>

	<!--MENU COMMUN-->
	<?= $curObj->menuEdit() ?>
</form>