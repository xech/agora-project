<script>
////	Resize
lightboxSetWidth(600);

////	INIT
$(function(){
	//// Affiche/masque la liste des utilisateurs
	$("[name='messengerDisplay']").on("click",function(){
		if(this.value=="some")	{$("#divSomeUsers").slideDown();}
		else					{$("#divSomeUsers").slideUp();}
	});

	//// Validation du formulaire : vérif
	$("form").submit(function(){
		if($("#messengerDisplaySome").prop("checked") && $("input[id^=someUser]:checked").length==0)   {notify("<?= Txt::trad("notifSelectUser") ?>");  return false;}
	});
});
</script>

<style>
.vDivRadio		{margin-bottom:10px;}
#divSomeUsers	{display:<?= empty($someUsers)?"none":"inline-block" ?>; padding-top:10px; padding-left:25px;}
.vDivSomeUser	{display:inline-block; width:50%; padding:5px;}
</style>

<form action="index.php" method="post">
	<div class="lightboxTitle"><?= ucfirst(Txt::trad("USER_livecounterVisibility")) ?></div>

	<div class="vDivRadio">
		<input type="radio" name="messengerDisplay" value="all" id="messengerDisplayAll" <?= $allUsers==true?"checked":null ?>>
		<label for="messengerDisplayAll"><?= Txt::trad("USER_livecounterAllUsers") ?></label>
	</div>
	<div class="vDivRadio">
		<input type="radio" name="messengerDisplay" value="none" id="messengerDisplayNone" <?= (empty($allUsers) && empty($someUsers))?"checked":null ?>>
		<label for="messengerDisplayNone"><?= Txt::trad("USER_livecounterDisabled") ?></label>
	</div>
	<div class="vDivRadio">
		<input type="radio" name="messengerDisplay" value="some" id="messengerDisplaySome" <?= !empty($someUsers)?"checked":null ?>>
		<label for="messengerDisplaySome"><?= Txt::trad("USER_livecounterSomeUsers") ?></label>
		<div id="divSomeUsers">
			<?php
			if(count($curObj->usersVisibles())==0)  {echo "<div class='vDivSomeUser'>".Txt::trad("USER_noUser")."</div>";}
			foreach($curObj->usersVisibles() as $tmpUser){
				echo "<div class='vDivSomeUser'>
						<input type='checkbox' name='messengerSomeUsers[]' value='".$tmpUser->_id."' id='someUser".$tmpUser->_id."' ".(in_array($tmpUser->_id,$someUsers)?"checked":null).">
						<label for='someUser".$tmpUser->_id."'>".$tmpUser->getLabel()."</label>
					  </div>";
			}
			?>
		</div>
	</div>

	<!--MENU COMMUN & SUBMIT & CONTROLE DU FORM-->
	<?= $curObj->editMenuSubmit() ?>
</form>