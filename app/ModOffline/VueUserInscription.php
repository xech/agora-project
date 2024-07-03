<script>
////	Resize
lightboxSetWidth(500);

////	CONTRÔLE DU FORMULAIRE
$(function(){
	$("#userInscriptionForm").submit(function(event){
		////	Stop la validation du form
		event.preventDefault();
		////	Verif les champs obligatoires  &&  Controle l'email/login
		if($("input[name='_idSpace']:checked").isEmpty() || $("input[name='name']").isEmpty() || $("input[name='firstName']").isEmpty())  {notify("<?= Txt::trad("fillFieldsForm") ?>","warning");  return false;}
		if($("input[name='mail']").isMail()==false)  {notify("<?= Txt::trad("mailInvalid") ?>","warning");  return false;}
		////	Controle si l'user existe déjà et Valide le formulaire si c'est OK (redirection)
		$.ajax({url:"index.php",data:$(this).serialize()}).done(function(result){
			if(/inscriptionOK/i.test(result))	{parent.redir("index.php?notify=userInscriptionRecorded");}
			else								{notify(result,"warning");}
		});
	});
});
</script>


<style>
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
	<div><input type="text" name="name" placeholder="<?= Txt::trad("name"); ?>" title="<?= Txt::trad("name"); ?>"></div>
	<div><input type="text" name="firstName" placeholder="<?= Txt::trad("firstName"); ?>" title="<?= Txt::trad("firstName"); ?>"></div>
	<div><input type="text" name="mail" placeholder="<?= Txt::trad("mail"); ?>" title="<?= Txt::trad("mail"); ?>"></div>
	<div><textarea name="message" placeholder="<?= Txt::trad("comment")." (".Txt::trad("option").")" ?>"><?= Req::param("message") ?></textarea></div>
	<div><?= CtrlMisc::menuCaptcha().Txt::submitButton("send") ?></div>
</form>