<script>
ready(function(){
	////	RESULTAT DE RECHERCHE D'USERS (async)
	$("#affectationForm").on("submit",async function(event){
		event.preventDefault();
		if($("#affectationForm input:checked").length==0)	{notify("<?= Txt::trad("notifSelectUser") ?>");}
		else if(await confirmAlt("<?= Txt::trad("USER_userAffectConfirm") ?>"))		{asyncSubmit(this);}//Valide le formulaire
	});
});
</script>


<style>
#bodyLightbox				{max-width:550px;}
#affectationForm .objField	{padding-left:150px;}
</style>


<!--FORMULAIRE DE RECHERCHE D'USERS-->
<form action="index.php" method="post" id="searchForm">
	<div class="lightboxTitle"><?= Txt::trad("USER_addExistUserTitle") ?><br><i><?= Ctrl::$curSpace->getLabel() ?></i></div>
	<!--LISTE DES CHAMPS DE RECHERCHE-->
	<?php foreach($searchFields as $tmpField){ ?>
		<div class="objField">
			<div><?= Txt::trad($tmpField) ?></div>
			<div><input type="text" name="searchFields[<?= $tmpField ?>]" value="<?= $searchFieldsValues[$tmpField] ?? null ?>"></div>
		 </div>
	<?php } ?>
	<?= Txt::submitButton("search") ?>
</form>


<!--RESULTAT DE RECHERCHE-->
<?php if(!empty($usersList)){ ?>
	<br><hr>
	<form action="index.php" method="post" id="affectationForm">
		<fieldset>
			<!--LISTE D'USERS-->
			<?php foreach($usersList as $tmpUser){ ?>
				<div class="objField" <?= Txt::tooltip($tmpUser->mail) ?>><div>
					<input type="checkbox" name="usersList[]" value="<?= $tmpUser->_id ?>" id="userId_<?= $tmpUser->_id ?>">
					<label for="userId_<?= $tmpUser->_id ?>"><?= $tmpUser->getLabel() ?></label>
				</div></div>			
			<?php } ?>
			<?= Txt::submitButton("add") ?>
		</fieldset>
	</form>
<?php } ?>