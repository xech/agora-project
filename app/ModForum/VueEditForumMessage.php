<style>
#bodyLightbox					{max-width:900px;}
.vMessageQuoted					{position:relative; overflow:auto; max-height:100px; margin-bottom:30px; padding:10px; padding-left:40px; font-style:italic;}
.bodyWhite .vMessageQuoted		{background:#eee;}
.bodyBlack .vMessageQuoted		{background:#333;}
.vMessageQuoted [src*='quote']	{position:absolute; top:5px; left:5px; opacity:0.5;}
input[name='title']				{width:100%;}
</style>


<form action="index.php" method="post" id="mainForm" enctype="multipart/form-data">
	<?php
	////	TITRE MOBILE  &&  CITATION D'UN AUTRE MESSAGE (?)
	echo $curObj->titleMobile("FORUM_addMessage");
	if(!empty($messageParent))  {echo "<fieldset class='vMessageQuoted'>".$messageParent->title."<br>".$messageParent->description."<img src='app/img/forum/quote.svg'></fieldset>";}

	////	TITRE DU MESSAGE
	echo '<input type="text" name="title" value="'.$curObj->title.'" placeholder="'.Txt::trad("title").' '.Txt::trad("optional").'">';

	////	DESCRIPTION (EDITOR)  &&  MESSAGE PARENT (?)  &&  MENU COMMUN
	echo $curObj->descriptionEditor(false);
	if(Req::isParam("_idMessageParent"))  {echo "<input type='hidden' name='_idMessageParent' value=\"".Req::param("_idMessageParent")."\">";}
	echo $curObj->editMenuSubmit();
	?>
</form>