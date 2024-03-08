<script>
////	Resize
lightboxSetWidth(500);

////	Controle du formulaire
$(function(){
	$("#mainForm").submit(function(){
		////	Controle si au moins un champ de recherche est rempli
		if($("input[name^='searchFields']").isVisible()){
			var allFieldsEmpty=true;
			$("input[name^='searchFields']").each(function(){
				if($(this).isEmpty()==false)  {allFieldsEmpty=false;}
			});
			if(allFieldsEmpty==true)   {notify("<?= Txt::trad("USER_searchPrecision") ?>");  return false;}
		}
		////	Controle si au moins un utilisateur est sélectionné
		else if($("input[name^='usersList']").isVisible()){
			if($("input[name^='usersList']:checked").length==0)		 			{notify("<?= Txt::trad("notifSelectUser") ?>");  return false;}
			else if(!confirm("<?= Txt::trad("USER_userAffectConfirm") ?>"))		{return false;}
		}
	});
});
</script>


<form action="index.php" method="post" id="mainForm">
	<?php
	//// Recherche
	if(empty($usersList))
	{
		//Titre du formulaire
		echo "<div class='lightboxTitle'>".Txt::trad("USER_userSearch")."</div>";
		//liste des champs de recherche
		foreach($searchFields as $tmpField){
			echo "<div class='objField'>
					<div>".Txt::trad($tmpField)."</div>
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
		echo "<div class='lightboxTitle'>".Txt::trad("USER_usersSpaceAffectation")."</div>";
		//Liste les users à affecter
		if(empty($usersList))  {echo Txt::trad("USER_usersSearchNoResult");}
		else{
			foreach($usersList as $tmpUser){
				echo "<div class='objField' title=\"".Txt::tooltip($tmpUser->mail)."\">
						<input type='checkbox' name='usersList[]' value='".$tmpUser->_id."' id='userId".$tmpUser->_id."'>
						<label for='userId".$tmpUser->_id."'>".$tmpUser->getLabel()."</label>
					  </div>";
			}
			//Bouton de validation
			echo Txt::submitButton("validate").
				 "<button type='button' onclick=\"window.history.back();\" style='position:absolute;top:15px;right:10px;'>".Txt::trad("USER_usersSearchBack")."</button>";
		}
	}
	?>
</form>