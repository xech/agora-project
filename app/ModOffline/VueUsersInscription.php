<script>
////	Resize
lightboxSetWidth(450);

$(function(){
	////	Contrôle du formulaire
	$("#usersInscriptionForm").submit(function(event){
		//Pas de validation par défaut du formulaire
		event.preventDefault();
		//Verif les champs obligatoires et l'email/login
		if($("input[name='name']").isEmpty() || $("input[name='firstName']").isEmpty() || $("textarea[name='message']").isEmpty())  {notify("<?= Txt::trad("fillAllFields") ?>","warning");  return false;}
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
select, input, textarea	{margin-bottom:15px!important;}
input, textarea			{width:100%!important;}
.formValidate			{margin-top:15px;}
</style>


<form id="usersInscriptionForm" class="lightboxContent noConfirmClose">
	<div class="lightboxTitle"><?= ucfirst(Txt::trad("usersInscriptionSpace")) ?></div>
	
	<select name="_idSpace">
		<?php foreach($objSpacesInscription as $tmpSpace){ ?>
		<option value="<?= $tmpSpace->_id ?>" title="<?= $tmpSpace->description ?>"><?= $tmpSpace->name ?></option>
		<?php } ?>
	</select><br>
	<input type="text" name="name" placeholder="<?= Txt::trad("name"); ?>"><br>
	<input type="text" name="firstName" placeholder="<?= Txt::trad("firstName"); ?>"><br>
	<input type="text" name="mail" placeholder="<?= Txt::trad("mail"); ?>"><br>
	<input type="password" name="password" class="editInputPassword" placeholder="<?= Txt::trad("password"); ?>"><br>
	<input type="password" name="passwordVerif" class="editInputPassword" placeholder="<?= Txt::trad("passwordVerif"); ?>"><br>
	<textarea name="message" placeholder="<?= Txt::trad("comment"); ?>"><?= Req::getParam("message") ?></textarea><br>
	<?= CtrlMisc::menuCaptcha() ?>
	<?= Txt::submit() ?>
</form>