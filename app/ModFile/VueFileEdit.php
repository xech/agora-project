<script>
////	Resize
lightboxSetWidth(600);

////	Controle spécifique à l'objet (cf. "VueObjEditMenuSubmit.php")
function objectFormControl(){
	return new Promise((resolve)=>{
		// vérif si un autre element porte le même nom
		var ajaxUrl="?ctrl=object&action=ControlDuplicateName&typeId=<?= $curObj->_typeId ?>&typeIdContainer=<?= $curObj->containerObj()->_typeId ?>&controledName="+encodeURIComponent($("[name='name']").val()+$("[name='dotExtension']").val());
		$.ajax(ajaxUrl).done(function(result){
			if(/duplicate/i.test(result))	{notify("<?= Txt::trad("NOTIF_duplicateName") ?>");  resolve(false);}	//"Un autre element porte le même nom"
			else							{resolve(true);}														//Sinon renvoie le résultat du controle principal
		});
	});
}
</script>


<style>
[name='name']			{width:65%;}/*surcharge*/
[name='dotExtension']	{width:40px!important;}
[name='fileContent']	{height:200px;}
[name='fileContentOld']	{display:none;}
</style>


<form action="index.php" method="post" id="mainForm" enctype="multipart/form-data">

	<!--NOM & DESCRIPTION-->
	<input type="text" name="name" value="<?= basename($curObj->name,strrchr($curObj->name,".")) ?>" placeholder="<?= Txt::trad("name") ?>">
	<input type="text" name="dotExtension" value="<?= strrchr($curObj->name,".") ?>" readonly>
	<?= $curObj->editDescription() ?>

	<!--MENU COMMUN & SUBMIT & CONTROLE DU FORM-->
	<?= $curObj->editMenuSubmit() ?>
</form>