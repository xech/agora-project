<script>
////	Sélectionne l'accès en écriture pour tout le monde : "L'accès en ''Ecriture'' est destiné aux modérateurs du sujet"
ready(function(){
	$("[name='objectRight[]'][value$='spaceUsers_2']").on("change",function(){
		if(this.checked)  {notify("<?= Txt::trad("FORUM_notifWriteAccess") ?>");}
	});
});

////	Controle spécifique du formulaire (cf. "VueObjMenuEdit.php")  :  Retourne false s'il n'y a que des accès en lecture
function formControl(){
	return new Promise((resolve)=>{
		if($("[name='objectRight[]']:checked").length==$("[name='objectRight[]'][value$='_1']:checked").length)	 {resolve(false);  notify("<?= Txt::trad("FORUM_notifOnlyReadAccess") ?>");}
		else																									 {resolve(true);}
	});
}
</script>


<style>
#bodyLightbox		{max-width:900px;}
[name='title']		{width:45%;}
[name="_idTheme"]	{min-width:200px; margin-left:30px;}

/*****    RESPONSIVE SMALL*/
@media screen and (max-width:499px){
	[name='title'], [name="_idTheme"]	{width:100%;}
	[name="_idTheme"]					{margin-left:0px; margin-top:20px;}
}
</style>


<form action="index.php" method="post" id="mainForm" enctype="multipart/form-data">

	<!--TITRE DE LIGHTBOX-->
	<?= $curObj->lightboxTitle("FORUM_addSubject") ?>

	<!--TITRE DU SUJET-->
	<input type="text" name="title" value="<?= $curObj->title ?>" class="inputTitleName" placeholder="<?= Txt::trad("title").' '.Txt::trad("optional") ?>">

	<!--THEME  &  DESCRIPTION  &  MENU COMMUN-->
	<?= MdlForumTheme::selectInput($curObj->_idTheme).$curObj->descriptionEditor(false).$curObj->editMenuSubmit() ?>
</form>