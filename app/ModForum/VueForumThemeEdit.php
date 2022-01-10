<script>
////	Resize
lightboxSetWidth(650);

////	INIT
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
.miscContainer					{margin-top:40px; border:#999 1px solid;}
.miscContainer:first-of-type	{display:none; border:#999 2px solid;}/*masque le 1er formulaire : ajout d'element*/
input[name='title']				{width:300px; max-width:80%; color:#fff;}
input[name='description']		{width:100%; margin-top:15px; margin-bottom:5px;}
.vAutorSubmit					{display:table; width:100%; margin-top:20px;}
.vAutorSubmit>div				{display:table-cell; vertical-align:middle;}
.vAutorSubmit>div:first-child	{font-style:italic; font-weight:normal;}
.vAutorSubmit>div:last-child	{text-align:right;}
.vAutorSubmit button			{width:120px; margin-right:10px;}
/*RESPONSIVE FANCYBOX (440px)*/
@media screen and (max-width:440px){
	.vAutorSubmit, .vAutorSubmit>div  {display:block; margin-top:20px;}
}
</style>


<div class="lightboxContent">
	<div class="lightboxTitle">
		<img src="app/img/category.png"> <?= Txt::trad("FORUM_editThemes") ?>
		<div class="lightboxTitleDetail"><img src="app/img/info.png"> <?= Txt::trad("FORUM_editThemesInfo") ?></div>
	</div>
	<div class="lightboxAddElem">
		<button onclick="$('form:first-of-type').slideToggle();$('form:first-of-type [name=title]').focus();"><img src="app/img/plus.png"> <?= Txt::trad("FORUM_addTheme") ?></button>	
	</div>
	<?php
	////	LISTE LES THEMES
	foreach($themesList as $tmpTheme)
	{
		//Init
		$colorPickerTextId="title-".$tmpTheme->tmpId;
		$colorPickerHiddenId="color-".$tmpTheme->tmpId;
		$buttonsSubmitDelete=($tmpTheme->isNew())  ?  Txt::submitButton("add",false)  :  Txt::submitButton("modify",false);
		if($tmpTheme->isNew()==false)  {$buttonsSubmitDelete.="<img src='app/img/delete.png' class='sLink' title=\"".Txt::trad("delete")."\" onclick=\"confirmDelete('".$tmpTheme->getUrl("delete")."')\">";}
		//Affichage du formulaire
		echo "<form action='index.php' method='post' class='miscContainer'>
				<input type='text' name='title' value=\"".$tmpTheme->title."\" id='".$colorPickerTextId."' placeholder=\"".Txt::trad("title")."\">
				".Tool::colorPicker($colorPickerTextId,$colorPickerHiddenId,"background-color")."
				<input type='hidden' name='color' id='".$colorPickerHiddenId."' value='".$tmpTheme->color."'>
				<input type='text' name='description' value=\"".$tmpTheme->description."\" placeholder=\"".Txt::trad("description")."\">
				".$tmpTheme->menuSpaceAffectation()."
				<div class='vAutorSubmit'>
					<div>".$tmpTheme->createdBy."</div>
					<div>".$buttonsSubmitDelete."<input type='hidden' name='typeId' value='".$tmpTheme->tmpId."'></div>
				</div>
			  </form>";
	}
	?>
</div>