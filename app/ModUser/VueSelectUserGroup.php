<script>
ready(function(){
	////	S'il y a moins de 4 users  : masque "tout sélectionner" & Co 
	if($("<?= $inputSelector ?>").length < 4)	{$("#selectUserGroup<?= $menuId ?> .userSelectOption").hide();}
});
</script>

<style>
.selectUserGroup hr	{margin-block:3px;}
</style>


<div class="selectUserGroup" id="selectUserGroup<?= $menuId ?>">

	<hr>

	<!--SELECTION D'UN GROUPE D'USERS-->
	<?php foreach($userGroupList as $tmpGroup){ ?>
		<div class="userInputDiv" onclick="userGroupSelect('<?= $inputSelector ?>','<?= implode(',',$tmpGroup->_idUsersTab) ?>')" <?=Txt::tooltip(Txt::trad("select")." :<br>".$tmpGroup->usersLabel) ?> >
			<img src="app/img/usersGroup.svg">&nbsp; <?= $tmpGroup->title ?>
		</div>
	<?php } ?>

	<hr>

	<!--TOUT SELECTIONNER-->
	<div class="userInputDiv userSelectOption" onclick="$('<?= $inputSelector ?>').prop('checked',false).trigger('click')">
		<img src="app/img/checkAll.svg"> <?= Txt::trad("selectAll") ?>
	</div>
	<!--TOUT DESELECTIONNER-->
	<div class="userInputDiv userSelectOption" onclick="$('<?= $inputSelector ?>').prop('checked',true).trigger('click')">
		<img src="app/img/checkNone.svg"> <?= Txt::trad("unselectAll") ?>
	</div>


</div>