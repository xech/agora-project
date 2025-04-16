<script>
////	Resize
lightboxWidth(550);

////	Confirme l'envoi?
ready(function(){
	$("form").on("submit",async function(event){
		event.preventDefault();
		////	Controle le nb d'users sélectionnés
		if($("input[name='usersList[]']:checked").length==0)   {notify("<?= Txt::trad("notifSelectUser") ?>");  return false;}
		////	Valide le formulaire
		if(await confirmAlt("<?= TXT::trad("USER_sendCoordsConfirm") ?>"))  {asyncSubmit(this);}
	});
});
</script>

<style>
.vUserLine	{display:inline-block; width:48%; padding:7px;}
</style>


<form action="index.php" method="post">
	<div class="lightboxTitle"><?= Txt::trad("USER_sendCoordsTooltip") ?></div>

	<!--Liste des users-->
	<?php foreach($usersList as $tmpUser){ ?>
		<div class="vUserLine"><input type="checkbox" name="usersList[]" value="<?= $tmpUser->_id ?>" id="usersBox<?= $tmpUser->_id ?>">
			<label for="usersBox<?= $tmpUser->_id ?>" <?= Txt::tooltip($tmpUser->mail) ?> ><?= $tmpUser->getLabel() ?></label>
		</div>
	<?php } ?>
	
	<!--Bouton de validation-->
	<?= Txt::submitButton("send") ?>
</form>