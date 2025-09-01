<script>
////	INIT
ready(function(){
	//// Sélectionne l'accès en écriture pour tout le monde : "L'accès en ''Ecriture'' est destiné aux modérateurs du sujet"
	$("[name='objectRight[]'][value$='spaceUsers_2']").on("change",function(){
		if(this.checked)  {notify("<?= Txt::trad("FORUM_notifWriteAccess") ?>");}
	});
});

////	Controle spécifique du formulaire (cf. "VueObjMenuEdit.php")
function objectFormControl(){
	return new Promise((resolve)=>{
		//// Retourne false s'il n'y a que des accès en lecture
		if($("[name='objectRight[]']:checked").length==$("[name='objectRight[]'][value$='_1']:checked").length)	 {notify("<?= Txt::trad("FORUM_notifOnlyReadAccess") ?>");  resolve(false);}
		else																									 {resolve(true);}
	});
}
</script>


<style>
#bodyLightbox			{max-width:900px;}
[name='title']			{width:45%;}
[name="_idTheme"]		{min-width:200px; margin-left:30px;}
.descriptionTextarea	{margin-top:30px!important;}/*surcharge*/

/*RESPONSIVE SMARTPHONE*/
@media screen and (max-width:490px){
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
		 $curObj->descriptionEditor(false).
		 $curObj->editMenuSubmit();
	?>
</form>