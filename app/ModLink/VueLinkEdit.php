<script>
lightboxSetWidth(700);//Resize
</script>

<style>
input[name='adress']	{width:99%; height:25px;}
</style>

<form action="index.php" method="post" onsubmit="return mainFormControl()" enctype="multipart/form-data" class="lightboxContent">
	<!--TITRE RESPONSIVE-->
	<?= $curObj->editRespTitle("LINK_addLink") ?>
	
	<!--URL & DESCRIPTION-->
	<input type="url" name="adress" value="<?= empty($curObj->adress)?"http://":$curObj->adress ?>" placeholder="<?= Txt::trad("LINK_adress") ?>" pattern="http.*" required>
	<br><br>
	<textarea name="description" placeholder="<?= Txt::trad("description") ?>"><?= $curObj->description ?></textarea>

	<!--MENU COMMUN-->
	<?= $curObj->menuEdit() ?>
</form>