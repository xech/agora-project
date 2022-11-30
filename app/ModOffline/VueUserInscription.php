<script>
////	Resize
lightboxSetWidth(450);

////	INIT
$(function(){
	////	Contrôle du formulaire
	$("#userInscriptionForm").submit(function(event){
		//Pas de validation par défaut du formulaire
		event.preventDefault();
		//Verif les champs obligatoires et l'email/login
		if($("input[name='_idSpace']:checked").isEmpty() || $("input[name='name']").isEmpty() || $("input[name='firstName']").isEmpty())  {notify("<?= Txt::trad("fillFieldsForm") ?>","warning");  return false;}
		if($("input[name='mail']").isMail()==false)  {notify("<?= Txt::trad("mailInvalid") ?>","warning");  return false;}
		//Vérif le password et sa confirmation
		if(isValidPassword($("input[name='password']").val())==false)						{notify("<?= Txt::trad("passwordInvalid") ?>","warning");		return false;}
		else if($("input[name='password']").val()!=$("input[name='passwordVerif']").val())	{notify("<?= Txt::trad("passwordConfirmError") ?>","warning");	return false;}
		//Valide le formulaire (et controle si l'user existe déjà)
		$.ajax({url:"index.php",data:$(this).serialize(),dataType:"json"}).done(function(resultJson){
			if(resultJson.redirSuccess)		{parent.redir(resultJson.redirSuccess);}
			else if(resultJson.notifError)	{notify(resultJson.notifError,"warning");}
		});
	});
});
</script>

<style>
input:not(input[type=radio]), textarea, .vSpaceDiv, .formValidate	{margin-bottom:15px;}
input:not(input[type=radio]), textarea								{width:100%!important;}
</style>


<form id="userInscriptionForm" class="lightboxContent noConfirmClose">
	<div class='vSpaceDiv'><?= ucfirst(Txt::trad("userInscriptionSpace")) ?> :</div>
	<?php
	//Inputs radio de sélection de l'espace
	foreach($objSpacesInscription as $tmpSpace){
		echo "<div class='vSpaceDiv'>
				<input type='radio' name='_idSpace' value=\"".$tmpSpace->_id."\" id='spaceSelect".$tmpSpace->_id."' ".(count($objSpacesInscription)==1?'checked':null).">
				<label for='spaceSelect".$tmpSpace->_id."'>".$tmpSpace->name."</label>
			  </div>";
	}
	?>
	<hr>
	<input type="text" name="name" placeholder="<?= Txt::trad("name"); ?>"><br>
	<input type="text" name="firstName" placeholder="<?= Txt::trad("firstName"); ?>"><br>
	<input type="text" name="mail" placeholder="<?= Txt::trad("mail"); ?>"><br>
	<input type="password" name="password" class="editInputPassword" placeholder="<?= Txt::trad("password"); ?>"><br>
	<input type="password" name="passwordVerif" class="editInputPassword" placeholder="<?= Txt::trad("passwordVerif"); ?>"><br>
	<textarea name="message" placeholder="<?= Txt::trad("comment"); ?>"><?= Req::param("message") ?></textarea><br>
	<?= CtrlMisc::menuCaptcha().Txt::submitButton() ?>
</form>