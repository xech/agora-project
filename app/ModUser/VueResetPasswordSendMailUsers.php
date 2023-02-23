<script>
////	Resize
lightboxSetWidth(450);

////	Confirme l'envoi?
function formControl(){
	if(!confirm("<?= TXT::trad("USER_sendCoordsConfirm") ?>"))  {return false;}
}
</script>

<style>
.vUserLine	{margin-bottom:10px;}
</style>


<form action="index.php" method="post" onsubmit="return formControl();" class="lightboxContent">
	<div class="lightboxTitle"><?= Txt::trad("USER_sendCoordsInfo") ?></div>
	<?php
	////	LISTE DES UTILISATEURS AVEC MAIL
	foreach($usersList as $tmpUser){
		echo "<div class='vUserLine'>
				<input type='checkbox' name='usersList[]' value='".$tmpUser->_id."' id='usersBox".$tmpUser->_id."'>
				<label for='usersBox".$tmpUser->_id."' title=\"".Txt::tooltip($tmpUser->mail)."\">".$tmpUser->getLabel()."</label>
			  </div>";
	}
	//Validation du formulaire
	echo Txt::submitButton("send");
	?>
</form>