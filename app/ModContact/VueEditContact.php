<form action="index.php" method="post" id="mainForm" enctype="multipart/form-data">
	<!--TITRE MOBILE-->
	<?= $curObj->lightboxTitle("CONTACT_addContact") ?>
	
	<!--IMAGE-->
	<div class="objField">
		<div><?= $curObj->isProfileImg()  ?  "<div class='personImgDiv'>".$curObj->profileImg()."</div>"  :  "<img src='app/img/user.svg'> ".Txt::trad("picture") ?></div>
		<div><?= $curObj->menuProfileImg() ?></div>
	</div>

	<!--CHAMPS PRINCIPAUX & MENU COMMUN-->
	<hr>
	<?= $curObj->getFields("edit").$curObj->editMenuSubmit() ?>
</form>