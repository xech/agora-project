<script>
////	Resize
lightboxSetWidth(580);

////	INIT
$(function(){
	//Affiche/masque la liste des utilisateurs
	$("[name='messengerDisplay']").on("click",function(){
		if($(this).val()=="some")	{$(".vDivSomeUsers").fadeIn();}
		else						{$(".vDivSomeUsers").fadeOut();}
	});
});
</script>

<style>
.vDivRadio		{margin-bottom:10px;}
.vDivSomeUsers	{display:<?= empty($someUsers)?"none":"inline-block" ?>;}
.vDivSomeUser	{margin:5px 0px 5px 30px;}
</style>

<form action="index.php" method="post" class="lightboxContent">
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
		<div class="vDivSomeUsers">
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

	<!--MENU COMMUN-->
	<?= $curObj->menuEdit() ?>
</form>