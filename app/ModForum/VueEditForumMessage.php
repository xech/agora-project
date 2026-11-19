<style>
#bodyLightbox		{max-width:900px;}
input[name='title']	{width:100%;}
</style>


<form action="index.php" method="post" id="mainForm" enctype="multipart/form-data">
	<!--TITRE DE LIGHTBOX-->
	<?= $curObj->lightboxTitle("FORUM_addMessage") ?>

	<!--CITATION D'UN AUTRE MESSAGE-->
	<?php if(!empty($messageParent)){ ?>
		<fieldset class="fieldsetQuote"><img src="app/img/forum/quote.svg"><?= $messageParent->title ?><br><?= $messageParent->description ?></fieldset>
		<input type="hidden" name="_idMessageParent" value="<?= $messageParent->_id ?>">
	<?php } ?>

	<!--TITRE DU MESSAGE-->
	<input type="text" name="title" value="<?= $curObj->title ?>" placeholder="<?= Txt::trad("title").' '.Txt::trad("optional") ?>">

	<!--DESCRIPTION  &  MENU COMMUN-->
	<?= $curObj->descriptionEditor(false).$curObj->editMenuSubmit()	?>
</form>