<script>
////	Resize
lightboxSetWidth(600);
</script>


<form action="index.php" method="post" id="mainForm" enctype="multipart/form-data">
	<!--TITRE MOBILE-->
	<?= $curObj->titleMobile("CONTACT_addContact") ?>
	
	<!--IMAGE-->
	<div class="objField">
		<div><?= $curObj->hasImg()  ?  "<div class='personLabelImg'>".$curObj->personImg()."</div>"  :  "<img src='app/img/person/photo.png'> ".Txt::trad("picture") ?></div>
		<div><?= $curObj->displayImgMenu() ?></div>
	</div>

	<!--CHAMPS PRINCIPAUX & MENU COMMUN-->
	<hr>
	<?= $curObj->getFieldsValues("edit").$curObj->editMenuSubmit() ?>
</form>