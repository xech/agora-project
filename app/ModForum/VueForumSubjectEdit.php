<script>
////	Resize
lightboxSetWidth(800);

////	INIT
$(function(){
	//// Sélectionne l'accès en écriture pour tout le monde : "L'accès en ''Ecriture'' est destiné aux modérateurs du sujet"
	$("[name='objectRight[]'][value$='spaceUsers_2']").on("change",function(){
		if(this.checked)  {notify("<?= Txt::trad("FORUM_notifWriteAccess") ?>");}
	});
});

////	Controle spécifique à l'objet (cf. "VueObjEditMenuSubmit.php")
function objectFormControl(){
	return new Promise((resolve)=>{
		//// Retourne false si ya uniquement des accès en lecture pour le sujet du forum
		if($("[name='objectRight[]']:checked").length==$("[name='objectRight[]'][value$='_1']:checked").length)		{resolve(false);  notify("<?= Txt::trad("FORUM_notifOnlyReadAccess") ?>");}
		else																										{resolve(true);}
	});
}
</script>


<style>
[name='title']			{width:45%;}
[name="_idTheme"]		{min-width:200px; margin-left:30px;}
.descriptionTextarea	{margin-top:30px!important;}/*surcharge*/

/*MOBILE FANCYBOX (440px)*/
@media screen and (max-width:440px){
	[name='title'], [name="_idTheme"]	{width:100%;}
	[name="_idTheme"]					{margin-left:0px; margin-top:20px;}
}
</style>


<form action="index.php" method="post" id="mainForm" enctype="multipart/form-data">
	<?php
	////	TITRE MOBILE &&  INPUT TITRE  &&  <SELECT> DU THEME  &&  DESCRIPTION (EDITOR)  &&  MENU COMMUN
	echo $curObj->titleMobile("FORUM_addSubject").
		'<input type="text" name="title" value="'.$curObj->title.'" class="inputTitleName" placeholder="'.Txt::trad("title")." ".Txt::trad("optional").'">'.
		 MdlForumTheme::selectInput($curObj->_idTheme).
		 $curObj->editDescription(false).
		 $curObj->editMenuSubmit();
	?>
</form>