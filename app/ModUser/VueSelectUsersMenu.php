<script>
ready(function(){
	////	S'il y a moins de 5 users  : masque "tout sélectionner" & Co 
	if($("<?= $inputSelector ?>").length < 5)	{$("#selectUsersMenu<?= $menuId ?>>div:not(.userInputDivGroup)").hide();}
});
</script>

<style>
.selectUsersMenu hr	{margin-block:7px;}
</style>


<div class="selectUsersMenu" id="selectUsersMenu<?= $menuId ?>">
	<hr>

	<!--SELECTION D'UN GROUPE D'USERS-->
	<?php foreach($userGroupList as $tmpGroup){ ?>
		<div class="userInputDiv userInputDivGroup" onclick="userGroupSelect('<?= $inputSelector ?>','<?= implode(',',$tmpGroup->_idUsersTab) ?>')" <?=Txt::tooltip(Txt::trad("select")." : ".$tmpGroup->usersLabel) ?> >
			<img src="app/img/user/accessGroup.png">&nbsp; <?= $tmpGroup->title ?>
		</div>
	<?php } ?>

	<!--SEPARATEUR S'IL Y A DES GROUPES-->
	<?= !empty($userGroupList) ? '<hr>' : null ?>

	<!--TOUT SELECTIONNER-->
	<div class="userInputDiv selectAllOption" onclick="$('<?= $inputSelector ?>').prop('checked',false).trigger('click')">
		<img src="app/img/checkSelectAll.png"> <?= Txt::trad("selectAll") ?>
	</div>

	<!--TOUT DESELECTIONNER-->
	<div class="userInputDiv unselectAllOption" onclick="$('<?= $inputSelector ?>').prop('checked',true).trigger('click')">
		<img src="app/img/checkUnselectAll.png"> <?= Txt::trad("unselectAll") ?>
	</div>
</div>