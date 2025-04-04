<script>
////	Resize
lightboxSetWidth(600);
</script>


<form action="index.php" method="post" id="mainForm" enctype="multipart/form-data">
	<!--TITRE MOBILE-->
	<?= $curObj->titleMobile("CONTACT_addContact") ?>
	
	<!--IMAGE-->
	<div class="objField">
		<div><?= $curObj->profileImgExist()  ?  "<div class='personLabelImg'>".$curObj->profileImg()."</div>"  :  "<img src='app/img/person/photo.png'> ".Txt::trad("picture") ?></div>
		<div><?= $curObj->profileImgMenu() ?></div>
	</div>

	<!--CHAMPS PRINCIPAUX & MENU COMMUN-->
	<hr>
	<?= $curObj->getFields("edit").$curObj->editMenuSubmit() ?>
</form>