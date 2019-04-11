<script>
////	Resize
lightboxSetWidth(450);

////	Confirme l'envoi?
function formControl()
{
	var allEmpty=true;
	//Vérifie si tous les champs de recherche sont vides
	$("input[name^='searchFields']").each(function(){
		if($(this).isEmpty()==false)	{allEmpty=false;}
	});
	//S'ils sont tous vides : erreur
	if(allEmpty==true)	{notify("<?= Txt::trad("USER_searchPrecision") ?>"); return false;}
	//Confirmer les affectations?
	if($("[name='usersList[]']").isEmpty()==false && confirm("<?= Txt::trad("USER_userAffectConfirm") ?>")==false)	{return false;}
}
</script>


<form action="index.php" method="post" OnSubmit="return formControl();" class="lightboxContent">
	<div class="lightboxTitle"><?= Txt::trad("USER_userSearch") ?></div>

	<?php
	//Liste des champs de recherche
	foreach($searchFields as $tmpField){
		echo "<div class='objField'>
				<div class='fieldLabel'>".Txt::trad($tmpField)."</div>
				<div><input type='text' name='searchFields[".$tmpField."]' value=\"".(isset($searchFieldsValues[$tmpField])?$searchFieldsValues[$tmpField]:null)."\"></div>
			  </div>";
	}
	//Liste des users à affecter
	if(isset($usersList))
	{
		echo "<hr>";
		echo (empty($usersList)) ? Txt::trad("USER_usersSearchNoResult") : Txt::trad("USER_usersSpaceAffectation");
		foreach($usersList as $tmpUser){
			echo "<div class='objField' title=\"".$tmpUser->mail."\">
					<input type='checkbox' name='usersList[]' value='".$tmpUser->_id."' id='userId".$tmpUser->_id."'>
					<label for='userId".$tmpUser->_id."'>".$tmpUser->getLabel()."</label>
				</div>";
		}
	}
	//Validation du form
	echo Txt::submit("send")
	?>
</form>