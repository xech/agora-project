<script>
////	Resize
lightboxSetWidth(450);
</script>

<style>
button[type='submit']				{width:60%; display:inline;}/*surcharge*/
button[type='submit']:last-child	{width:30%;}/*surcharge*/
.userInscription					{padding:10px;}
</style>


<form action="index.php" method="post" class="lightboxContent">
	<div class="lightboxTitle"><?= Txt::trad("usersInscriptionValidateInfo") ?></div>
	
	<?php
	// DEMANDES D'INSCRIPTION
	foreach($inscriptionList as $tmpInscription){
	?>
	<div class="userInscription">
		<input type="checkbox" name="inscriptionValidation[]" value="<?= $tmpInscription["_id"] ?>" id="inscription<?= $tmpInscription["_id"] ?>">
		<label for="inscription<?= $tmpInscription["_id"] ?>" title="<?= Txt::displayDate($tmpInscription["date"])."<br>".$tmpInscription["message"] ?>">
			<?= $tmpInscription["name"]." ".$tmpInscription["firstName"]." (".$tmpInscription["mail"].")" ?>
		</label>
	</div>
	<?php
	}
	// BOUTONS DE VALIDATION/INVALIDATION
	echo Txt::submit("validate", true, Txt::trad("usersInscriptionInvalidate"));
	?>
</form>