<script>
////	CONTRÔLE DU FORMULAIRE
ready(function(){
	$("#userInscriptionForm").on("submit",async function(event){
		event.preventDefault();
		////	Verif les champs obligatoires  &&  Controle l'email/login
		if($("input[name='_idSpace']:checked").isEmpty() || $("input[name='name']").isEmpty() || $("input[name='firstName']").isEmpty())	{notify("<?= Txt::trad("emptyFields") ?>","error");  return false;}
		else if($("input[name='mail']").isMail()==false)																					{notify("<?= Txt::trad("mailInvalid") ?>","error");  return false;}
		////	Captcha Ok : Controle et valide le formulaire
		if(await captchaControl()){
			$.ajax({url:"index.php",data:$(this).serialize(),method:"POST"}).done(function(result){
				if(/inscriptionOK/i.test(result))	{window.top.redir("index.php?notify=userInscriptionRecorded");}
				else								{notify(result,"error");}
			});
		}
	});
});
</script>


<style>
#bodyLightbox 				{max-width:500px;}
form>div					{margin:20px 0px;}
input[type=text], textarea	{width:100%!important;}
</style>


<form id="userInscriptionForm">
	<div><?= ucfirst(Txt::trad("userInscriptionSpace")) ?> :</div>
	<?php foreach($objSpacesInscription as $tmpSpace){ ?>
	<div>
		<input type="radio" name="_idSpace" value="<?= $tmpSpace->_id ?>" id="spaceSelect<?= $tmpSpace->_id ?>" <?= (count($objSpacesInscription)==1?'checked':null) ?>>
		<label for="spaceSelect<?= $tmpSpace->_id ?>"><?= $tmpSpace->name ?></label>
	</div>
	<?php }	?>
	<div><input type="text" name="name" placeholder="<?= Txt::trad("name"); ?>" required></div>
	<div><input type="text" name="firstName" placeholder="<?= Txt::trad("firstName"); ?>" required></div>
	<div><input type="text" name="mail" placeholder="<?= Txt::trad("mail"); ?>" required></div>
	<div><textarea name="message" placeholder="<?= Txt::trad("comment")." (".Txt::trad("option").")" ?>"><?= Req::param("message") ?></textarea></div>
	<div><?= CtrlMisc::menuCaptcha().Txt::submitButton("send") ?></div>
</form>