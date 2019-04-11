<script>
////	Resize
lightboxSetWidth(580);

////	Init la page
$(function(){
	////	Applique les couleurs de chaque titre, en fonction du champ color "hidden"
	$("input[name='title']").each(function(){
		$(this).css("background-color", $("#color-"+this.id.replace("title-","")).val());
	});

	////	Controle du formulaire
	$("form").submit(function(){
		//Vérif la présence du titre
		if($(this).find("[name='title']").isEmpty()){
			notify("<?= Txt::trad("requiredFields")." : ".Txt::trad("title") ?>");
			$(this).find("[name='title']").focusRed();
			return false;
		}
		//Au moins un espace sélectionné
		if($(this).find("[name='spaceList[]']:checked").length==0){
			notify("<?= Txt::trad("selectSpace") ?>");
			return false;
		}
	});
});
</script>

<style>
/*Titre du lightbox*/
#lightboxTitleDetail			{margin-top:8px;}
/*formulaires*/
.miscContainer					{margin-top:40px; padding:10px; border:#999 1px solid;}
.miscContainer:last-of-type		{display:none; border:#999 2px solid;}/*masque le dernier formulaire : ajout d'element*/
input[name='title']				{width:50%; color:#fff;}
input[name='description']		{width:100%; margin-top:15px; margin-bottom:5px;}
.vAutorButtons					{display:table; width:100%; margin-top:20px;}
.vAutorButtons>div				{display:table-cell; vertical-align:middle;}
.vAutorButtons>div:first-child	{font-style:italic; font-weight:normal;}
.vAutorButtons>div:last-child	{text-align:right;}
.vAutorButtons button			{width:120px; margin-right:10px;}
/*Ajout d'element*/
#addElem						{margin-top:50px; text-align:center;}
#addElem button					{width:200px; height:50px;}
</style>


<div class="lightboxContent">
	<div class="lightboxTitle">
		<img src="app/img/category.png"> <?= Txt::trad("FORUM_editThemes") ?>
		<div id="lightboxTitleDetail"><?= Txt::trad("FORUM_editThemesInfo") ?></div>
	</div>

	<?php
	////	LISTE LES THEMES
	foreach($themesList as $tmpTheme)
	{
		//Init
		$colorPickerTextId="title-".$tmpTheme->tmpId;
		$colorPickerHiddenId="color-".$tmpTheme->tmpId;
		$buttonsSubmitDelete=($tmpTheme->isNew())  ?  Txt::submit("add",false)  :  Txt::submit("modify",false);
		if($tmpTheme->isNew()==false)  {$buttonsSubmitDelete.="<img src='app/img/delete.png' class='sLink' title=\"".Txt::trad("delete")."\" onclick=\"if(confirm('".Txt::trad("confirmDelete",true)."')){lightboxClose('".$tmpTheme->getUrl("delete")."');}\">";}
		//Affichage du formulaire
		echo "<form action='index.php' method='post' class='miscContainer'>
				<input type='text' name='title' value=\"".$tmpTheme->title."\" id='".$colorPickerTextId."' placeholder=\"".Txt::trad("title")."\">
				".Tool::colorPicker($colorPickerTextId,$colorPickerHiddenId,"background-color")."
				<input type='hidden' name='color' id='".$colorPickerHiddenId."' value='".$tmpTheme->color."'>
				<input type='text' name='description' value=\"".$tmpTheme->description."\" placeholder=\"".Txt::trad("description")."\">
				".$tmpTheme->menuSpaceAffectation()."
				<div class='vAutorButtons'>
					<div>".$tmpTheme->createdBy."</div>
					<div>".$buttonsSubmitDelete."<input type='hidden' name='targetObjId' value='".$tmpTheme->tmpId."'></div>
				</div>
			  </form>";
	}
	?>

	<div id="addElem">
		<button onclick="$('form:last-of-type').slideToggle();$('form:last-of-type [name=title]').focus();"><img src="app/img/plus.png"> <?= Txt::trad("FORUM_addTheme") ?></button>	
	</div>
</div>