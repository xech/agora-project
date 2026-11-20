<script>
ready(function(){
	////	S'il y a moins de 4 users  : masque "tout sélectionner" & Co 
	if($("<?= $inputSelector ?>").length <= 3)   {$("#selectUserGroup<?= $menuId ?> .userSelectOption").hide();}
});
</script>

<style>
.selectUserGroup hr	{margin-block:5px;}
.selectUserGroup:not(:has(.userInputGroup)) .userInputGroupHr	{display:none;}
</style>


<div class="selectUserGroup" id="selectUserGroup<?= $menuId ?>">

	<!--SELECTION D'UN GROUPE D'USERS-->
	<hr class="userInputGroupHr">
	<?php foreach($userGroupList as $tmpGroup){ ?>
		<div class="userInputDiv userInputGroup" onclick="userGroupSelect('<?= $inputSelector ?>','<?= implode(',',$tmpGroup->_idUsersTab) ?>')" <?=Txt::tooltip(Txt::trad("select")." :<br>".$tmpGroup->usersLabel) ?> >
			<img src="app/img/usersGroup.svg">&nbsp; <?= $tmpGroup->title ?>
		</div>
	<?php } ?>

	<!--TOUT SELECTIONNER / DESELECTIONNER-->
	<div class="userSelectOption">
		<hr>
		<div class="userInputDiv" onclick="$('<?= $inputSelector ?>').prop('checked',false).trigger('click');">
			<img src="app/img/checkAll.svg"> <?= Txt::trad("selectAll") ?>
		</div>
		<div class="userInputDiv" onclick="$('<?= $inputSelector ?>').prop('checked',true).trigger('click');">
			<img src="app/img/checkNone.svg"> <?= Txt::trad("selectNone") ?>
		</div>
	</div>

</div>