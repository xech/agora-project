<script>
////	Controle du formulaire (async)
ready(function(){
	$("#mainForm").on("submit",async function(event){
		event.preventDefault();
		////	Formulaire de recherche : controle si au moins un champ est rempli
		if($("input[name^='searchFields']").isDisplayed()){
			let fieldNotEmpty=$("input[name^='searchFields']").is(function(){  return $(this).notEmpty();  });
			if(fieldNotEmpty==false)	{notify("<?= Txt::trad("USER_searchPrecision") ?>");}
			else						{asyncSubmit(this);}//Valide le formulaire
		}
		////	Liste des users : Controle si au moins un utilisateur est sélectionné et valide l'affectation
		else if($("input[name^='usersList']").isDisplayed()){
			if($("input[name^='usersList']:checked").length==0)		 					{notify("<?= Txt::trad("notifSelectUser") ?>");}
			else if(await confirmAlt("<?= Txt::trad("USER_userAffectConfirm") ?>"))		{asyncSubmit(this);}//Valide le formulaire
		}
	});
});
</script>


<form action="index.php" method="post" id="mainForm">
	<?php
	////	Recherche d'users
	if(empty($usersList)){
		//Titre & champs de recherche
		echo '<div class="lightboxTitle">'.Txt::trad("USER_userSearch").'</div>';
		foreach($searchFields as $tmpField){
			echo '<div class="objField">
					<div>'.Txt::trad($tmpField).'</div>
					<div><input type="text" name="searchFields['.$tmpField.']" value="'.(isset($searchFieldsValues[$tmpField])?$searchFieldsValues[$tmpField]:null).'"></div>
				  </div>';
		}
		echo Txt::submitButton("search");
	}
	//// Affectation
	else
	{
		//Titre du formulaire & Bouton "retour"
		echo '<div class="lightboxTitle">'.Txt::trad("USER_usersSpaceAffectation").'</div>';
		//Liste les users à affecter
		if(empty($usersList))  {echo Txt::trad("USER_usersSearchNoResult");}
		else{
			foreach($usersList as $tmpUser){
				echo '<div class="objField" '.Txt::tooltip($tmpUser->mail).'>
						<input type="checkbox" name="usersList[]" value="'.$tmpUser->_id.'" id="userId'.$tmpUser->_id.'">
						<label for="userId'.$tmpUser->_id.'">'.$tmpUser->getLabel().'</label>
					  </div>';
			}
			echo Txt::submitButton("validate").'<button type="button" onclick="window.history.back()" style="position:absolute;bottom:8px;right:8px;">'.Txt::trad("USER_usersSearchBack").'</button>';
		}
	}
	?>
</form>