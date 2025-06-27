<script>
////	Sélectionner au moins une demande d'inscription
ready(function(){
	$("form").on("submit",function(){
		if($("[name='inscriptionValidate[]']:checked").length==0)   {notify("<?= Txt::trad("notifSelectUser") ?>");  return false;}
	});
});
</script>

<style>
#bodyLightbox				{max-width:700px;}
fieldset					{text-align:left;}
fieldset label				{margin-left:20px;}
fieldset li					{line-height:25px;}
#submitButtons				{text-align:center; margin-top:30px;}
#inscriptionNotify			{margin-bottom:20px;}
#submitButtons button		{margin:10px; padding:10px; height:60px;}
#submitButtons button img	{margin-right:10px;}
</style>


<form action="index.php" method="post">
	<div class="lightboxTitle"><?= Txt::trad("userInscriptionValidateTooltip") ?></div>

	<!--LISTES DES INSCRIPTIONS D'USERS-->
	<?php foreach(CtrlUser::userInscriptionValidate() as $tmpInsc){ ?>
		<fieldset>
			<input type="checkbox" name="inscriptionValidate[]" value="<?= $tmpInsc["_id"] ?>" id="boxInscription<?= $tmpInsc["_id"] ?>">
			<label for="boxInscription<?= $tmpInsc["_id"] ?>">
				<?= $tmpInsc["name"].' '.$tmpInsc["firstName"] ?>
				<ul>
					<li><?= $tmpInsc["mail"] ?></li>
					<li><?= Txt::dateLabel($tmpInsc["date"]) ?></li>
					<li><?= ucfirst(Txt::trad("SPACE_space")).' : '.Ctrl::getObj("space",$tmpInsc["_idSpace"])->getLabel() ?></li>
					<?= !empty($tmpInsc["message"]) ?  '<li>'.Txt::trad("description").' : '.$tmpInsc["message"].'</li>'  : null ?>
				</ul>
			</label>
		</fieldset>
	<?php }  ?>

	<div id="submitButtons">
		<!--BOUTON D'INVALIDATION  &&  OPTION D'ENVOI DE NOTIFICATION PAR EMAIL-->
		<div id="inscriptionNotify">
			<input type="checkbox" name="inscriptionNotify" value="true" id="boxInscriptionNotify">
			<label for="boxInscriptionNotify"><?= Txt::trad("EDIT_notifMail2") ?></label>
		</div>
		<!--BOUTON D'INVALIDATION  /  BOUTON DE VALIDATION-->
		<div class="submitButtonInline"><button type="submit" name="submitInvalidate" value="true"><img src="app/img/delete.png"><?= Txt::trad("userInscriptionSelectInvalidate") ?></button></div>
		<?= Txt::submitButton("<img src='app/img/check.png'>".Txt::trad("userInscriptionSelectValidate"), false) ?>
	</div>
</form>