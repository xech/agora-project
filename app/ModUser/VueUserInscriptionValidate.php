<script>
////	Resize
lightboxSetWidth(450);
</script>

<style>
button[type='submit']				{width:60%; display:inline;}/*surcharge*/
button[type='submit']:last-child	{width:30%;}/*surcharge*/
.userInscription					{padding:10px;}
.userInscription label				{margin-right:10px;}
div[id^='tmpMessage']				{display:none; padding:10px; margin-top:5px; background-color:rgba(200,200,200,0.5); border-radius:3px;}
</style>


<form action="index.php" method="post" class="lightboxContent">
	<div class="lightboxTitle"><?= Txt::trad("userInscriptionValidateInfo") ?></div>
	
	<?php
	// SELECTION DES INSCRIPTIONS
	foreach($inscriptionList as $tmpInsc)
	{
		$tmpMessage=(!empty($tmpInsc["message"]))  ?  "<img src='app/img/arrowBottom.png' class='sLink' onclick=\"$('#tmpMessage".$tmpInsc["_id"]."').toggle();\" title=\"".Txt::trad('description')."\"><div id='tmpMessage".$tmpInsc["_id"]."'>".$tmpInsc["message"]."</div>"  :  null;
		echo "<div class='userInscription'>"
				."<input type='checkbox' name=\"inscriptionValidate[]\" value=\"".$tmpInsc["_id"]."\" id=\"inscriptionLabel".$tmpInsc["_id"]."\">"
				."<label for=\"inscriptionLabel".$tmpInsc["_id"]."\" title=\"".Txt::displayDate($tmpInsc["date"])."\">".$tmpInsc["name"]." ".$tmpInsc["firstName"]." (".$tmpInsc["mail"].")</label>"
				.$tmpMessage
			."</div>";
	}

	// BOUTONS DE VALIDATION/INVALIDATION
	echo Txt::submit("validate", true, Txt::trad("userInscriptionInvalidate"));
	?>
</form>