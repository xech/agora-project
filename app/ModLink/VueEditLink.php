<style>
input[name='adress']	{width:100%;}
</style>


<form action="index.php" method="post" id="mainForm" enctype="multipart/form-data">
	<!--TITRE MOBILE-->
	<?= $curObj->titleMobile("LINK_addLink") ?>
	
	<!--URL & DESCRIPTION-->
	<input type="url" name="adress" value="<?= empty($curObj->adress)?"http://":$curObj->adress ?>" placeholder="<?= Txt::trad("LINK_adress") ?>" pattern="http.*" required>
	<br><br>
	<textarea name="description" placeholder="<?= Txt::trad("description") ?>"><?= $curObj->description ?></textarea>

	<!--MENU D'EDITION & VALIDATION DU FORM-->
	<?= $curObj->editMenuSubmit() ?>
</form>