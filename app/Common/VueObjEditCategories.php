<script>
////	Resize
lightboxSetWidth(600);

////	INIT
$(function(){
	////	Change l'ordre d'affichage des modules ("hightlight" : module fantome & "y" : déplacemnt vertical)
	if(isMobile())	{$(".changeOrder").hide();}
	else{
		$("#categoryList").sortable({
			handle:".changeOrder",
			placeholder:'changeOrderShadow',
			axis:"y",
			update:function(){
				let ajaxUrl="?ctrl=object&action=CategoryChangeOrder&objectsTypeId[<?= Req::param("objectType") ?>]=";
				$("input[name='changeOrderIds[]']").each(function(){  ajaxUrl+=this.value+"-";  });
				$.ajax(ajaxUrl).done(function(result){
					if(/true/i.test(result))  {notify("<?= Txt::trad("categoryNotifChangeOrder") ?>");}
				});
			}
		});
	}

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

////	MODIF DU COLORPICKER
function colorPickerChange(tmpColor, _typeId){
	$("#titleInput"+_typeId).css("background-color",tmpColor.hexString);	//Modif le background du "title"
	$("#colorInput"+_typeId).val(tmpColor.hexString);						//Modif l'input "hidden"
}
</script>

<!--CHARGE LE COLORPICKER-->
<script src="app/js/iro.min.js"></script>


<style>
.lightboxTitle img							{margin:0px 15px;}
.vCategoryInfos								{display:table; width:100%;}
.vCategoryInfos>div							{display:table-cell;}
.vCategoryLabel								{text-transform:uppercase;}
.vCategoryLabelAdd							{text-align:center;}
.vCategoryAutor								{text-transform:lowercase; margin-top:5px; opacity:0.8;}
.vCategoryButtons							{width:120px; text-align:center; vertical-align:middle;}
form										{display:none; margin:20px 10px 10px 10px; padding:10px;}/*masque par défaut*/
form input[name='title']					{width:300px; max-width:80%; color:#fff;}
form input[name='description']				{width:100%; margin-top:15px; margin-bottom:5px;}
.vSpaceList									{margin-top:10px;}
.vSpaceList>div								{display:inline-block; width:48%; margin:10px 10px 0px 0px;}
.vLabelAllSpaces							{font-style:italic;}
.submitButtonMain							{margin-top:30px;}/*surcharge du button*/
/*MOBILE FANCYBOX (440px)*/
@media screen and (max-width:440px){
	.vSpaceList>div 	{display:block; width:100%; margin:15px 0px;}
}
</style>


<div>
	<div class="lightboxTitle">
		<img src="app/img/category.png"><?= Txt::trad($tradModulePrefix."_categoriesEditTitle") ?>
		<div class="lightboxTitleDetail"><?= Txt::trad($tradModulePrefix."_categoriesEditInfo") ?><img src="app/img/info.png"></div>
	</div>
	<div id="categoryList">
		<?php foreach($objectList as $tmpObj){ ?>
		<fieldset>
			<div class="vCategoryInfos">
				<?php if($tmpObj->isNew()){ ?>
					<div class="vCategoryLabel vCategoryLabelAdd" onclick="$('#categoryForm<?= $tmpObj->_typeId ?>').toggle()"><a><img src="app/img/plus.png">&nbsp; <?= Txt::trad($tradModulePrefix."_categoriesAddButton") ?></div>
				<?php }else{ ?>
					<div class="vCategoryLabel"><div><?= $tmpObj->getLabel() ?></div><div class="vCategoryAutor"><?= Txt::trad("createBy").' '.$tmpObj->autorLabel() ?></div></div>
					<div class="vCategoryButtons" onclick="$('#categoryForm<?= $tmpObj->_typeId ?>').toggle()"><img src="app/img/edit.png"> <?= Txt::trad("modify") ?></div>
					<div class="vCategoryButtons" onclick="confirmDelete('<?= $tmpObj->getUrl('delete') ?>')"><img src="app/img/delete.png"> <?= Txt::trad("delete") ?></div>
					<?php if(Ctrl::$curUser->isGeneralAdmin()){ ?>
						<div class="changeOrder" title="<?= Txt::trad("changeOrder") ?>"><img src="app/img/changeOrder.png"></div>
						<input type="hidden" name="changeOrderIds[]" value="<?= $tmpObj->_id ?>">
					<?php } ?>
				<?php } ?>
			</div>
			<form action="index.php" method="post" id="categoryForm<?= $tmpObj->_typeId ?>" class="fieldsetSub">
				<input type="text" name="title" value="<?= $tmpObj->title ?>" id="titleInput<?= $tmpObj->_typeId ?>" placeholder="<?= Txt::trad("title") ?>" style="background-color:<?= $tmpObj->color ?>">
				<img src="app/img/colorPicker.png" class="menuLaunch" for="colorPickerDiv<?= $tmpObj->_id ?>">
				<div class="colorPicker menuContext" id="colorPickerDiv<?= $tmpObj->_id ?>">
					<div id="colorPickerMenu<?= $tmpObj->_id ?>"></div>
					<script>
						////	Créé un nouveau colorPicker pour la catégorie courante
						new iro.ColorPicker('#colorPickerMenu<?= $tmpObj->_id ?>', {width:150,color:"<?= $tmpObj->color ?>"})
							.on('color:change', function(tmpColor){ colorPickerChange(tmpColor,"<?= $tmpObj->_typeId ?>"); });
					</script>
				</div>
				<input type="hidden" name="color" id="colorInput<?= $tmpObj->_typeId ?>" value="<?= $tmpObj->color ?>">
				<input type="text" name="description" value="<?= $tmpObj->description ?>" placeholder="<?= Txt::trad("description") ?>">
				<div class="vSpaceList">
					<?php
					////	"VISIBLE TOUS LES ESPACES"
					if(Ctrl::$curUser->isGeneralAdmin() || ($tmpObj->isNew()==false && empty($tmpObj->_idSpaces))){								//Affiche si : Admin général || Modif d'un user + case déjà cochée
						$boxId='boxAllSpaces'.$tmpObj->_typeId;																					//Id de la checkbox
						$boxChecked=empty($tmpObj->_idSpaces)  ?  "checked"  :  null;															//Aucun espace sélectionné en particulier : visible sur tous
						$boxOnchange='onchange="if(this.checked) $(\'input[id^=boxSpace'.$tmpObj->_typeId.']\').prop(\'checked\',false)"';		//Déselectionne les boxes de tous les espaces
						echo '<div>
								<input type="checkbox" name="spaceList[]" value="allSpaces" id="'.$boxId.'" '.$boxChecked.' '.$boxOnchange.'>
								<label for="'.$boxId.'" class="vLabelAllSpaces">'.Txt::trad("visibleAllSpaces").'</label>
							</div>';
					}
					////	LISTE DES ESPACES
					foreach($spaceList as $tmpSpace){
						$boxId='boxSpace'.$tmpObj->_typeId.$tmpSpace->_typeId;																	//Id de la checkbox
						$boxChecked=($tmpObj->isNew()==false && in_array($tmpSpace->_id,$tmpObj->spaceIds))  ?  "checked"  :  null;				//Check si : Modif + case déjà cochée
						$boxOnchange='onchange="if(this.checked) $(\'input[id=boxAllSpaces'.$tmpObj->_typeId.']\').prop(\'checked\',false)"';	//Déselectionne la boxe "Visible sur tous les espaces"
						echo '<div>
								<input type="checkbox" name="spaceList[]" value="'.$tmpSpace->_id.'" id="'.$boxId.'" '.$boxChecked.' '.$boxOnchange.'>
								<label for="'.$boxId.'" title="'.Txt::trad("visibleOnSpace").' <i>'.$tmpSpace->name.'</i>">'.$tmpSpace->name.'</label>
							</div>';
					}
					?>
				</div>
				<input type="hidden" name="objectType" value="<?= Req::param("objectType") ?>">
				<input type="hidden" name="typeId" value="<?= $tmpObj->_typeId ?>">
				<?= Txt::submitButton($tmpObj->isNew()?"add":"modify") ?>
			</form>
		</fieldset>
		<?php } ?>
	</div>
</div>