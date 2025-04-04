<script>
////	Resize
lightboxSetWidth(550);

////	Confirme l'envoi?
ready(function(){
	$("form").on("submit",async function(event){
		event.preventDefault();
		if($("input[name='usersList[]']:checked").length==0)						{notify("<?= Txt::trad("notifSelectUser") ?>");}	//"Merci de sélectionner au moins un user"
		else if(await confirmAlt("<?= TXT::trad("USER_sendCoordsConfirm") ?>"))		{submitFinal(this);}								//Submit final (sans récursivité Jquery)
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