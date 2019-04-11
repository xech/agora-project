<script>
////	Resize
lightboxSetWidth(<?= (isset($fileContent)) ? 750 : 550 ?>);

$(function(){
	////	Validation du formulaire
	$("#mainForm").submit(function(event){
		//Pas de validation par défaut du formulaire
		event.preventDefault();
		//Controle si un autre fichier porte le même nom
		$.ajax("?ctrl=object&action=ControlDuplicateName&targetObjId=<?= $curObj->_targetObjId ?>&targetObjIdContainer=<?= $curObj->containerObj()->_targetObjId ?>&controledName="+encodeURIComponent($("[name='name']").val()+$("[name='dotExtension']").val())).done(function(result){
			//Retourne false si ya doublon. Mais si le controle principal est ok, on poste le formulaire et ferme la page
			if(find("duplicate",result))	{notify("<?= Txt::trad("NOTIF_duplicateName"); ?>","warning");  return false;}
			else if(mainFormControl())		{$.ajax({url:"index.php",data:$("#mainForm").serialize()}).done(function(){ lightboxClose(); });}
		});
	});
});
</script>

<style>
[name='name']			{width:400px; max-width:80%;}
[name='dotExtension']	{width:40px!important;}
[name='description']	{margin-top:10px;}
.fileContentLabel		{margin-top:10px; font-style:italic;}
[name='fileContent']	{height:200px;}
[name='fileContentOld']	{display:none;}
</style>


<form id="mainForm" class="lightboxContent" enctype="multipart/form-data">

	<!--NOM & DESCRIPTION-->
	<input type="text" name="name" value="<?= basename($curObj->name,strrchr($curObj->name,".")) ?>" class="textBig" placeholder="<?= Txt::trad("name") ?>">
	<input type="text" name="dotExtension" value="<?= strrchr($curObj->name,".") ?>" readonly>
	<textarea name="description" placeholder="<?= Txt::trad("description") ?>"><?= $curObj->description ?></textarea>
	<!--CONTENU MODIFIABLE : FICHIER TXT/HTML-->
	<?php if(isset($fileContent)){ ?>
		<div class="fileContentLabel"><?= Txt::trad("FILE_fileContent") ?>:</div>
		<textarea name="fileContent"><?= $fileContent ?></textarea>
		<textarea name="fileContentOld"><?= $fileContent ?></textarea>
		<?php if(isset($initHtmlEditor))	{echo CtrlMisc::initHtmlEditor("fileContent");} ?>
	<?php } ?>

	<!--MENU COMMUN-->
	<?= $curObj->menuEdit() ?>
</form>