<script>
////	Resize
lightboxSetWidth(500);

////	Sélectionner au moins une demande d'inscription
$(function(){
	$("form").submit(function(){
		if($("[name='inscriptionValidate[]']:checked").length==0)   {notify("<?= Txt::trad("notifSelectUser") ?>");  return false;}
	});
});
</script>


<style>
.vInscription								{padding:10px;}
.vInscription label							{margin-right:10px;}
.vInscriptionMessage, .vInscriptionSpace	{margin-top:8px; margin-left:30px; font-weight:normal; }
.submitButtonMain							{margin:30px 0px 10px 0px;}	/*surcharge*/
.submitButtonMain button					{width:400px; height:60px;}	/*idem*/
.submitButtonMain button img				{margin-right:10px;}
#divInscriptionNotify						{text-align:center;}
</style>


<form action="index.php" method="post">
	<div class="lightboxTitle"><?= Txt::trad("userInscriptionValidateTooltip") ?></div>
	<!--LISTES DES INSCRIPTIONS D'USERS-->
	<?php foreach(CtrlUser::userInscriptionValidate() as $tmpInsc){ ?>
		<fieldset>
			<input type="checkbox" name="inscriptionValidate[]" value="<?= $tmpInsc["_id"] ?>" id="inputInscription<?= $tmpInsc["_id"] ?>">
			<label for="inputInscription<?= $tmpInsc["_id"] ?>"><?= $tmpInsc["name"].' '.$tmpInsc["firstName"].' - '.$tmpInsc["mail"].' <img src="app/img/arrowRightBig.png"> '.Txt::dateLabel($tmpInsc["date"]) ?></label>
			<ul>
				<li><?= ucfirst(Txt::trad("SPACE_space")).' : '.Ctrl::getObj("space",$tmpInsc["_idSpace"])->getLabel() ?></li>
				<?= !empty($tmpInsc["message"]) ?  '<li>'.Txt::trad("description").' : '.$tmpInsc["message"].'</li>'  : null ?>
			</ul>
		</fieldset>
	<?php
	}
	////	 BOUTON DE VALIDATION
	echo Txt::submitButton("<img src='app/img/check.png'>".Txt::trad("userInscriptionSelectValidate"));
	?>
	<!--BOUTON D'INVALIDATION  &&  OPTION D'ENVOI DE NOTIFICATION PAR EMAIL-->
	<div class="submitButtonMain" id="buttonInvalidate">
		<button type="submit" name="submitInvalidate" value="true"><img src="app/img/delete.png"><?= Txt::trad("userInscriptionSelectInvalidate") ?></button>
	</div>
	 <div id="divInscriptionNotify">
		<input type="checkbox" name="inscriptionNotify" value="true" id="inputInscriptionNotify">
		<label for="inputInscriptionNotify"><?= Txt::trad("EDIT_notifMail2") ?></label>
	</div>
</form>