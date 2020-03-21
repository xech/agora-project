<script>
////	Resize
lightboxSetWidth(750);

////	INIT
$(function(){
	//Au changement du thème ou d'affectation d'un espace : Vérifie la dispo du thème pour l'espace
	$("select[name='_idTheme']").on("change",function(){ checkThemeSpace(); });
	$("[id^='spaceBlock'] input[type=checkbox]").on("change",function(){ checkThemeSpace(); });//Cf "VueObjMenuEdit.php"
	//"trigger" pour initialiser la couleur de l'input
	$("select[name='_idTheme']").val("<?= $curObj->_idTheme ?>").trigger("change");
});

////	Vérifie que le thème courant est accessible sur tous les espaces affectés !
function checkThemeSpace()
{
	//Vérifie sur chaque espace affecté, que le thème courant y est bien disponible!
	notifThemeSpace=null;
	$("[id^='spaceBlock']").each(function(){
		var _idSpaceTmp=this.id.replace("spaceBlock","");
		var themeSpaceIds=$("select[name='_idTheme'] option:selected").attr("data-spaceIds");
		if($("#"+this.id+" input:checked").length>0 && typeof themeSpaceIds!="undefined" && themeSpaceIds.length>0 && themeSpaceIds.split(",").indexOf(_idSpaceTmp)==-1)
			{notifThemeSpace="<?= Txt::trad("FORUM_themeSpaceAccessInfo") ?> : <i>"+$("select[name='_idTheme'] option:selected").attr("data-spaceLabels")+"</i>";}
	});
	if(notifThemeSpace!==null)	{notify(notifThemeSpace,"warning");}
}
</script>

<style>
[name='title']		{margin-bottom:20px !important;}
[name="_idTheme"]	{width:200px !important; margin:10px 0px 10px 0px;}
.vEvtOptionsLabel	{white-space:nowrap;}

/*RESPONSIVE*/
@media screen and (max-width:1023px){
	[name='title']		{width:95%;}
	[name="_idTheme"]	{width:75%;}
}
</style>

<form action="index.php" method="post" onsubmit="return mainFormControl()" enctype="multipart/form-data" class="lightboxContent">
	<!--TITRE RESPONSIVE-->
	<?php echo $curObj->editRespTitle("FORUM_addSubject"); ?>
	
	<!--TITRE & THEME-->
	<input type="text" name="title" value="<?= $curObj->title ?>" class="textBig" placeholder="<?= Txt::trad("title")." ".Txt::trad("optional") ?>"> &nbsp;
	<?php if(!empty($themesList)){ ?>
		<span class="vEvtOptionsLabel">
			<img src="app/img/category.png"><?= Txt::trad("FORUM_subjectTheme") ?>
			<select name="_idTheme">
				<option value=""></option>
				<?php foreach($themesList as $tmpTheme){echo "<option value=\"".$tmpTheme->_id."\" data-color=\"".$tmpTheme->color."\" data-spaceIds=\"".implode(",",$tmpTheme->spaceIds)."\" data-spaceLabels=\"".$tmpTheme->spaceLabels()."\">".$tmpTheme->title."</option>";} ?>
			</select>
		</span>
	<?php } ?>

	<!--DESCRIPTION (EDITOR)-->
	<textarea name="description"><?= $curObj->description ?></textarea>

	<!--MENU COMMUN-->
	<?= $curObj->menuEdit() ?>
</form>