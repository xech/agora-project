<script>
////	Resize
lightboxSetWidth(750);
</script>

<style>
[name='title']					{margin-bottom:20px !important;}
.vEvtOptionsLabel img			{max-height:15px;}
.vMessageQuoted					{position:relative; display:inline-block; overflow:auto; max-height:100px; margin-bottom:20px; padding:10px; padding-left:40px; border-radius:5px; font-style:italic; font-weight:normal; background-color:<?= Ctrl::$agora->skin=="black"?"#333":"#eee" ?>;}
.vMessageQuoted [src*='quote']	{position:absolute; top:5px; left:5px; opacity:0.5;}
</style>

<form action="index.php" method="post" onsubmit="return mainFormControl()" enctype="multipart/form-data" class="lightboxContent">
	<!--TITRE RESPONSIVE-->
	<?php echo $curObj->editRespTitle("FORUM_addMessage"); ?>
	
	<!--MESSAGE A CITER?-->
	<?php if(!empty($messageParent))  {echo "<div class='vMessageQuoted'>".$messageParent->title."<br>".$messageParent->description."<img src='app/img/forum/quote.png'></div><br>";} ?>

	<!--TITRE & DESCRIPTION (EDITOR)-->
	<input type="text" name="title" value="<?= $curObj->title ?>" class="textBig" placeholder="<?= Txt::trad("title")." ".Txt::trad("optional") ?>">
	<textarea name="description"><?= $curObj->description ?></textarea>

	<?php
	////	"_idMessageParent?
	if(Req::isParam("_idMessageParent"))  {echo "<input type='hidden' name='_idMessageParent' value=\"".Req::getParam("_idMessageParent")."\">";}
	////	MENU COMMUN
	echo $curObj->menuEdit();
	?>
</form>