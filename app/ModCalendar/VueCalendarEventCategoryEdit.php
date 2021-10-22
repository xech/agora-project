<script>
////	Resize
lightboxSetWidth(600);

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
/*formulaires*/
.miscContainer					{margin-top:40px; padding:10px; border:#999 1px solid;}
.miscContainer:last-of-type		{display:none; border:#999 2px solid;}/*masque le dernier formulaire : ajout d'element'*/
input[name='title']				{width:300px; max-width:80%; color:#fff;}
input[name='description']		{width:100%; margin-top:15px; margin-bottom:5px;}
.vAutorSubmit					{display:table; width:100%; margin-top:20px;}
.vAutorSubmit>div				{display:table-cell; vertical-align:middle;}
.vAutorSubmit>div:first-child	{font-style:italic; font-weight:normal;}
.vAutorSubmit>div:last-child	{text-align:right;}
.vAutorSubmit button			{width:120px; margin-right:10px;}
/*Ajout d'element*/
#addElem						{margin-top:50px; text-align:center;}
#addElem button					{width:200px; height:50px;}

/*RESPONSIVE FANCYBOX (440px)*/
@media screen and (max-width:440px){
	.vAutorSubmit, .vAutorSubmit>div  {display:block; margin-top:20px;}
}
</style>


<div class="lightboxContent">
	<div class="lightboxTitle"><img src="app/img/category.png"> <?= Txt::trad("CALENDAR_editCategories") ?></div>
	
	<div class="infos"><?= Txt::trad("CALENDAR_editCategoriesRight") ?></div>

	<?php
	////	LISTE LES CATEGORIES
	foreach($categoriesList as $tmpCategory)
	{
		//Init
		$colorPickerTextId="title-".$tmpCategory->tmpId;
		$colorPickerHiddenId="color-".$tmpCategory->tmpId;
		$buttonsSubmitDelete=($tmpCategory->isNew())  ?  Txt::submitButton("add",false)  :  Txt::submitButton("record",false);
		if($tmpCategory->isNew()==false)  {$buttonsSubmitDelete.="<img src='app/img/delete.png' class='sLink' title=\"".Txt::trad("delete")."\" onclick=\"confirmDelete('".$tmpCategory->getUrl("delete")."')\">";}
		//Affichage du formulaire
		echo "<form action='index.php' method='post' class='miscContainer'>
				<input type='text' name='title' value=\"".$tmpCategory->title."\" id='".$colorPickerTextId."' placeholder=\"".Txt::trad("title")."\">
				".Tool::colorPicker($colorPickerTextId,$colorPickerHiddenId,"background-color")."
				<input type='hidden' name='color' id='".$colorPickerHiddenId."' value='".$tmpCategory->color."'>
				<input type='text' name='description' value=\"".$tmpCategory->description."\" placeholder=\"".Txt::trad("description")."\">
				".$tmpCategory->menuSpaceAffectation()."
				<div class='vAutorSubmit'>
					<div>".$tmpCategory->createdBy."</div>
					<div>".$buttonsSubmitDelete."<input type='hidden' name='typeId' value='".$tmpCategory->tmpId."'></div>
				</div>
			  </form>";
	}
	?>

	<div id="addElem">
		<button onclick="$('form:last-of-type').slideToggle();$('form:last-of-type [name=title]').focus();"><img src="app/img/plus.png"> <?= Txt::trad("CALENDAR_addCategory") ?></button>	
	</div>
</div>