<script>
lightboxSetWidth(750);//Resize

////	Init la page
$(function(){
	////	Masque les heures si une date n'est pas sélectionnée
	if($(".dateBegin").isEmpty())	{$(".timeBegin").hide();}
	if($(".dateEnd").isEmpty())		{$(".timeEnd").hide();}
	////	Donne une valeur aux inputs "select"
	$("[name='advancement']").val("<?= $curObj->advancement ?>");
	$("[name='priority']").val("<?= $curObj->priority ?>");
	////	Change de priorité : modif l'icone
	$("[name='priority']").change(function(){
		var imgPriority="app/img/task/priority"+$(this).val()+".png";
		$("img[src*='priority']").attr("src",imgPriority);
	});
	////	Affiche le block des responsables s'il y en a de sélectionnés
	if($(":checked[name='responsiblePersons[]']").length>0)	{$("#divResponsiblePersons").show();}
});
</script>

<style>
[name='title']			{width:80%; margin-right:10px;}
#blockDescription		{margin-top:20px; <?= empty($curObj->description)?"display:none;":null ?>}
[name='description']	{width:100%; height:70px; <?= empty($curObj->description)?"display:none;":null ?>}
.vTaskOption			{display:inline-block; margin:10px 10px 10px 0px;}
img[src*='arrowRight']	{margin-left:5px; margin-right:5px;}
img[src*='user/icon']	{height:20px;}
#labelResponsiblePersons{cursor:pointer; line-height:25px;}
#divResponsiblePersons	{display:none; overflow:auto; max-height:100px;}
.divResponsiblePerson	{display:inline-block; width:32%; padding:3px;}

/*RESPONSIVE FANCYBOX (440px)*/
@media screen and (max-width:440px){
	.divResponsiblePerson	{width:48%;}	
}
</style>

<form action="index.php" method="post" onsubmit="return mainFormControl()" enctype="multipart/form-data" class="lightboxContent">

	<!--TITRE & DESCRIPTION (EDITOR)-->
	<input type="text" name="title" value="<?= $curObj->title ?>" placeholder="<?= Txt::trad("title") ?>">
	<img src="app/img/description.png" class="sLink" title="<?= Txt::trad("description") ?>" onclick="$('#blockDescription').slideToggle()">
	<div id="blockDescription">
		<textarea name="description" placeholder="<?= Txt::trad("description") ?>"><?= $curObj->description ?></textarea>
	</div>
	<br><br>

	<!--DATE DEBUT & FIN-->
	<div class="vTaskOption">
		<input type="text" name="dateBegin" class="dateBegin" value="<?= Txt::formatDate($curObj->dateBegin,"dbDatetime","inputDate") ?>" placeholder="<?= Txt::trad("begin") ?>" title="<?= Txt::trad("begin") ?>">
		<input type="text" name="timeBegin" class="timeBegin" value="<?= Txt::formatDate($curObj->dateBegin,"dbDatetime","inputHM",true) ?>" placeholder="H:m">
		<img src="app/img/arrowRight.png">
		<input type="text" name="dateEnd" class="dateEnd" value="<?= Txt::formatDate($curObj->dateEnd,"dbDatetime","inputDate") ?>" placeholder="<?= Txt::trad("end") ?>" title="<?= Txt::trad("end") ?>">
		<input type="text" name="timeEnd" class="timeEnd" value="<?= Txt::formatDate($curObj->dateEnd,"dbDatetime","inputHM",true) ?>" placeholder="H:m">
	</div>

	<!--PRIORITE-->
	<div class="vTaskOption">
		<select name="priority">
			<option value=""><?= Txt::trad("TASK_priority")." : ".Txt::trad("no") ?></option>
			<?php for($i=1;$i<=4;$i++)  {echo "<option value='".$i."'>".Txt::trad("TASK_priority")." ".Txt::trad("TASK_priority".$i)."</option>";} ?>
		</select>
		<img src="app/img/task/priority<?= $curObj->priority ?>.png">
	</div>
	
	<!--AVANCEMENT-->
	<div class="vTaskOption">
		<select name="advancement">
			<option value=""><?= Txt::trad("TASK_advancement")." : ".Txt::trad("no") ?></option>
			<?php for($i=0;$i<=100;$i+=10)  {echo "<option value='".$i."'>".Txt::trad("TASK_advancement")." : ".$i." %</option>";} ?>
		</select>
	</div>
	
	<!--RESPONSABLES-->
	<label class="vTaskOption" id="labelResponsiblePersons" onclick="$('#divResponsiblePersons').slideToggle();">
		<img src="app/img/user/icon.png"> <?= txt::trad("TASK_responsiblePersons") ?> <img src="app/img/arrowBottom.png">
	</label>
	<div id="divResponsiblePersons">
		<?php
		//Affiche chaque responsable
		foreach(Ctrl::$curSpace->getUsers() as $tmpUser)
		{
			$checkedResponsible=in_array($tmpUser->_id,Txt::txt2tab($curObj->responsiblePersons))  ?  "checked"  :  null;
			echo "<div class='divResponsiblePerson'>
					<input type='checkbox' name='responsiblePersons[]' value=\"".$tmpUser->_id."\" id=\"responsiblePerson".$tmpUser->_id."\" ".$checkedResponsible." >
					<label for=\"responsiblePerson".$tmpUser->_id."\">".$tmpUser->getLabel()."</label>
				  </div>";
		}
		?>
	</div>

	<!--MENU COMMUN-->
	<?= $curObj->menuEdit() ?>
</form>