<script>
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
#bodyLightbox	{max-width:750px;}
</style>


<form action="index.php" method="post">
	<div class="lightboxTitle"><?= Txt::trad("USER_sendCoordsTooltip") ?></div>

	<!--LISTE DES USERS-->
	<?php foreach($usersList as $tmpUser){ ?>
		<div class="userInputDiv">
			<input type="checkbox" name="usersList[]" value="<?= $tmpUser->_id ?>" class="vUserInput" id="userInput<?= $tmpUser->_id ?>" data-iduser="<?= $tmpUser->_id ?>" >
			<label for="userInput<?= $tmpUser->_id ?>" <?= Txt::tooltip($tmpUser->mail) ?> ><?= $tmpUser->getLabel() ?></label>
		</div>
	<?php } ?>

	<!--SELECTION D'USERS ET DES GROUPES D'USERS-->
	<?= MdlUser::selectUsersGroups(Ctrl::$curSpace, ".vUserInput") ?>
	
	<!--BOUTON DE VALIDATION-->
	<?= Txt::submitButton("send") ?>
</form>