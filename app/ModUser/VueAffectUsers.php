<script>
////	Resize
lightboxSetWidth(450);

////	Confirme l'envoi?
function formControl()
{
	//Controle les champs de recherche
	if($("input[name^='searchFields']").is(":visible"))
	{
		var emptySearch=true;
		//Vérifie si tous les champs de recherche sont vides
		$("input[name^='searchFields']").each(function(){
			if($(this).isEmpty()==false)	{emptySearch=false;}
		});
		//S'ils sont tous vides : erreur
		if(emptySearch==true)	{notify("<?= Txt::trad("USER_searchPrecision") ?>"); return false;}
	}
	//Confirmer les affectations
	else if($("input[name^='usersList']").is(":visible") && !confirm("<?= Txt::trad("USER_userAffectConfirm") ?>"))  {return false;}
}
</script>


<form action="index.php" method="post" OnSubmit="return formControl();" class="lightboxContent">
	<?php
	//// Recherche
	if(empty($usersList))
	{
		//Titre du formulaire
		echo "<div class='lightboxTitle'>".Txt::trad("USER_userSearch")."</div>";
		//liste des champs de recherche
		foreach($searchFields as $tmpField){
			echo "<div class='objField'>
					<div class='fieldLabel'>".Txt::trad($tmpField)."</div>
					<div><input type='text' name='searchFields[".$tmpField."]' value=\"".(isset($searchFieldsValues[$tmpField])?$searchFieldsValues[$tmpField]:null)."\"></div>
				  </div>";
		}
		//Bouton de validation
		echo Txt::submitButton("search");
	}
	//// Affectation
	else
	{
		//Titre du formulaire & Bouton "retour"
		echo "<div class='lightboxTitle'>".Txt::trad("USER_usersSpaceAffectation")."</div>
			  <button type='button' onclick=\"window.history.back();\" style='position:absolute;top:5px;right:5px;'>Back</button>";
		//Liste les users à affecter
		if(empty($usersList))  {echo Txt::trad("USER_usersSearchNoResult");}
		else{
			foreach($usersList as $tmpUser){
				echo "<div class='objField' title=\"".$tmpUser->mail."\">
						<input type='checkbox' name='usersList[]' value='".$tmpUser->_id."' id='userId".$tmpUser->_id."'>
						<label for='userId".$tmpUser->_id."'>".$tmpUser->getLabel()."</label>
					  </div>";
			}
			//Bouton de validation
			echo Txt::submitButton();
		}
	}
	?>
</form>