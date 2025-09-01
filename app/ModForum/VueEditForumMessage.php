<style>
#bodyLightbox					{max-width:900px;}
[name='title']					{width:100%; margin-bottom:30px!important;}
.vMessageQuoted					{position:relative; display:inline-block; overflow:auto; max-height:100px; margin-bottom:20px; padding:10px; padding-left:40px; border-radius:5px; font-style:italic; font-weight:normal; background:<?= Ctrl::$agora->skin=="black"?"#333":"#eee" ?>;}
.vMessageQuoted [src*='quote']	{position:absolute; top:5px; left:5px; opacity:0.5;}
</style>


<form action="index.php" method="post" id="mainForm" enctype="multipart/form-data">
	<?php
	////	TITRE MOBILE  &&  CITATION D'UN AUTRE MESSAGE (?)
	echo $curObj->titleMobile("FORUM_addMessage");
	if(!empty($messageParent))  {echo "<div class='vMessageQuoted'>".$messageParent->title."<br>".$messageParent->description."<img src='app/img/forum/quote.png'></div><br>";}

	////	TITRE DU MESSAGE
	echo '<input type="text" name="title" value="'.$curObj->title.'" placeholder="'.Txt::trad("title").' '.Txt::trad("optional").'">';

	////	DESCRIPTION (EDITOR)  &&  MESSAGE PARENT (?)  &&  MENU COMMUN
	echo $curObj->descriptionEditor(false);
	if(Req::isParam("_idMessageParent"))  {echo "<input type='hidden' name='_idMessageParent' value=\"".Req::param("_idMessageParent")."\">";}
	echo $curObj->editMenuSubmit();
	?>
</form>