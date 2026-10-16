 <script>
////	INIT
ready(function(){
	////	Donne une valeur aux inputs "select"
	$("[name='advancement']").val("<?= $curObj->advancement ?>");
	$("[name='priority']").val("<?= $curObj->priority ?>");
	////	Affiche le block des responsables s'il y en a de sélectionnés
	if($(":checked[name='responsiblePersons[]']").length>0)	{$("#responsiblePersonsBlock").show();}
});
</script>


<style>
#bodyLightbox				{max-width:800px;}
.vTaskOptions				{display:inline-block; margin:20px 15px 0px 0px;}
.vTaskOptionsButton img		{max-height:18px;}
#responsiblePersonsBlock	{display:none; margin-top:15px; overflow:auto; max-height:300px;}

/*** RESPONSIVE SMARTPHONE*/
@media screen and (max-width:499px){
	.vTaskOptions			{display:block; margin:30px 0px 0px 0px;}
}
</style>


<form action="index.php" method="post" id="mainForm" enctype="multipart/form-data">
	<!--TITRE MOBILE-->
	<?= $curObj->titleMobile("TASK_addTask") ?>

	<!--TITRE / DESCRIPTION (EDITOR)-->
	<input type="text" name="title" value="<?= $curObj->title ?>" class="inputTitleName" placeholder="<?= Txt::trad("title") ?>">
	<?= $curObj->descriptionEditor() ?>

	<!--DATE DEBUT & FIN-->
	<div class="vTaskOptions">
		<input type="text" name="dateBegin" class="dateBegin" value="<?= Txt::formatDate($curObj->dateBegin,"dbDate","inputDate") ?>" placeholder="<?= Txt::trad("begin") ?>" <?= Txt::tooltip("begin") ?> >
		<img src="app/img/arrowRightLabel.svg">
		<input type="text" name="dateEnd" class="dateEnd" value="<?= Txt::formatDate($curObj->dateEnd,"dbDate","inputDate") ?>" placeholder="<?= Txt::trad("end") ?>" <?= Txt::tooltip("end") ?> >
	</div>

	<!--STATUT / KANBAN-->
	<div class="vTaskOptions">
		<?= MdlTaskStatus::selectInput($curObj->_idStatus) ?>
	</div>

	<!--"ASSIGNÉ À..."-->
	<button type="button" class="vTaskOptions vTaskOptionsButton" onclick="$('#responsiblePersonsBlock').slideToggle();">
		<img src="app/img/users.svg"> <?= Txt::trad("TASK_assignedTo") ?>...
	</button>
	<fieldset id="responsiblePersonsBlock">
		<!--USERS DE L'ESPACE-->
		<?php
		foreach(Ctrl::$curSpace->getUsers() as $tmpUser){
			$checkUser=in_array($tmpUser->_id,Txt::txt2tab($curObj->responsiblePersons))  ?  "checked"  :  null;
		?>
			<div class="userInputDiv">
				<input type="checkbox" name="responsiblePersons[]" value="<?= $tmpUser->_id ?>" class="vUserInput" id="userInput<?= $tmpUser->_id ?>" data-iduser="<?= $tmpUser->_id ?>" <?= $checkUser ?> >
				<label for="userInput<?= $tmpUser->_id ?>"><?= $tmpUser->getLabel() ?></label>
			</div>
		<?php } ?>
		<!--SELECTION D'USERS ET DES GROUPES D'USERS-->
		<?= MdlUser::selectUsersGroups(Ctrl::$curSpace, ".vUserInput") ?>
	</fieldset>
	
	<br> 

	<!--PRIORITE-->
	<div class="vTaskOptions">
		<select name="priority">
			<option value=""><?= Txt::trad("TASK_priorityUndefined") ?></option>
			<?php for($i=1;$i<=3;$i++)  {echo "<option value='".$i."'>".Txt::trad("TASK_priority")." ".Txt::trad("TASK_priority".$i)."</option>";} ?>
		</select>
	</div>

	<!--AVANCEMENT-->
	<div class="vTaskOptions">
		<select name="advancement">
			<option value=""><?= Txt::trad("TASK_advancement")." : ".Txt::trad("no") ?></option>
			<?php for($i=0;$i<=100;$i+=10)  {echo "<option value='".$i."'>".Txt::trad("TASK_advancement")." : ".$i." %</option>";} ?>
		</select>
	</div>

	<!--MENU D'EDITION & VALIDATION DU FORM-->
	<?= $curObj->editMenuSubmit() ?>
</form>