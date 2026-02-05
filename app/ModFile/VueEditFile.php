<script>
////	Controle spécifique du formulaire (cf. "VueObjMenuEdit.php")
function mainFormControl(){
	return new Promise((resolve)=>{
		let controledName=$("[name='name']").val()+$("[name='dotExtension']").val();
		let ajaxUrl ="?ctrl=object&action=ControlDuplicateName&typeId=<?= $curObj->_typeId ?>&typeIdContainer=<?= $curObj->containerObj()->_typeId ?>&controledName="+encodeURIComponent(controledName);
		$.ajax(ajaxUrl).done(function(result){
			if(/duplicateName/i.test(result))	{notify("<?= Txt::trad("NOTIF_duplicateName") ?>");  resolve(false);}//"Un element avec le même nom existe déjà"
			else								{resolve(true);}
		});
	});
}
</script>

<style>
[name='name']			{width:380px;}
[name='dotExtension']	{width:55px;}
</style>

<form action="index.php" method="post" id="mainForm" enctype="multipart/form-data">
	<!--NOM & DESCRIPTION-->
	<input type="text" name="name" value="<?= basename($curObj->name,strrchr($curObj->name,".")) ?>" placeholder="<?= Txt::trad("name") ?>">
	<input type="text" name="dotExtension" value="<?= strrchr($curObj->name,".") ?>" readonly>
	<!--MENU D'EDITION & VALIDATION DU FORM-->
	<?= $curObj->descriptionEditor() ?>
	<?= $curObj->editMenuSubmit() ?>
</form>