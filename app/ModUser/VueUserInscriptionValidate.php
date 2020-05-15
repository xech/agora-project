<script>
////	Resize
lightboxSetWidth(500);
</script>

<style>
.vUserInscription					{padding:10px;}
.vUserInscription label				{margin-right:10px;}
.vTmpMessage						{display:none;}
.submitButtonMain					{padding-top:20px; padding-bottom:0px;}/*surcharge*/
.submitButtonMain button			{width:280px;}/*surcharge*/
.submitButtonMain button img		{margin-right:10px;}
</style>


<form action="index.php" method="post" class="lightboxContent">
	<div class="lightboxTitle"><?= Txt::trad("userInscriptionValidateInfo") ?></div>
	
	<?php
	// SELECTION DES INSCRIPTIONS
	foreach($inscriptionList as $tmpInsc)
	{
		$tmpMessage=(!empty($tmpInsc["message"]))  ?  "<img src='app/img/arrowBottom.png' class='sLink' onclick=\"$('#tmpMessage".$tmpInsc["_id"]."').toggle();\" title=\"".Txt::trad('description')."\"><div class='infos vTmpMessage' id='tmpMessage".$tmpInsc["_id"]."'>".$tmpInsc["message"]."</div>"  :  null;
		echo "<div class='vUserInscription'>"
				."<input type='checkbox' name=\"inscriptionValidate[]\" value=\"".$tmpInsc["_id"]."\" id=\"inscriptionLabel".$tmpInsc["_id"]."\">"
				."<label for=\"inscriptionLabel".$tmpInsc["_id"]."\" title=\"".Txt::displayDate($tmpInsc["date"])."\">".$tmpInsc["name"]." ".$tmpInsc["firstName"]." (".$tmpInsc["mail"].")</label>"
				.$tmpMessage.
			 "</div>";
	}

	// BOUTONS DE VALIDATION/INVALIDATION
	echo Txt::submitButton("<img src='app/img/check.png'>".Txt::trad("userInscriptionValidate")).
		 "<div class='submitButtonMain' id='buttonInvalidate'><button type='submit' name='submitInvalidate' value='true'><img src='app/img/delete.png'>".Txt::trad("userInscriptionInvalidateButton")."</button></div>";
	?>
</form>