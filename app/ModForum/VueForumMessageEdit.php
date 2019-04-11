<script>
////	Resize
lightboxSetWidth(750);
</script>

<style>
[name='title']					{margin-bottom:20px !important;}
.vEvtOptionsLabel img			{max-height:15px;}
.vMessageQuoted					{overflow:auto; max-height:100px; margin-bottom:20px; opacity:0.7; background:#eee; border-radius:5px; padding:5px; font-style:italic;}
.vMessageQuoted [src*='quote']	{float:right;}
</style>

<form action="index.php" method="post" onsubmit="return mainFormControl()" enctype="multipart/form-data" class="lightboxContent">

	<!--MESSAGE A CITER?-->
	<?php if(!empty($messageParent)){ ?>
	<div class="vMessageQuoted">
		<img src="app/img/forum/quote.png"><?= $messageParent->title ?> :<br><br><?= $messageParent->description ?>
	</div>
	<?php } ?>

	<!--TITRE & DESCRIPTION (EDITOR)-->
	<input type="text" name="title" value="<?= $curObj->title ?>" class="textBig" placeholder="<?= Txt::trad("title") ?>">
	<textarea name="description"><?= $curObj->description ?></textarea>

	<!--"_idMessageParent" & MENU COMMUN-->
	<?php if(Req::isParam("_idMessageParent")){ ?>
	<input type="hidden" name="_idMessageParent" value="<?= Req::getParam("_idMessageParent") ?>">
	<?php } ?>
	<?= $curObj->menuEdit() ?>
</form>