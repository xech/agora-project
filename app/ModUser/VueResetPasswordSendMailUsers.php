<script>
////	Resize
lightboxSetWidth(450);

////	Confirme l'envoi?
$(function(){
	$("form").submit(function(event){
		if(!confirm("<?= TXT::trad("USER_sendCoordsConfirm") ?>"))  {return false;}
	});
});
</script>

<style>
.vUserLine	{margin-bottom:10px;}
</style>


<form action="index.php" method="post">
	<div class="lightboxTitle"><?= Txt::trad("USER_sendCoordsTooltip") ?></div>
	<?php
	////	LISTE DES UTILISATEURS AVEC MAIL
	foreach($usersList as $tmpUser){
		echo '<div class="vUserLine">
				<input type="checkbox" name="usersList[]" value="'.$tmpUser->_id.'" id="usersBox'.$tmpUser->_id.'">
				<label for="usersBox'.$tmpUser->_id.'" '.Txt::tooltip($tmpUser->mail).'>'.$tmpUser->getLabel().'</label>
			  </div>';
	}
	//Validation du formulaire
	echo Txt::submitButton("send");
	?>
</form>