<script>
////	INIT
ready(function(){
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
					if(/true/i.test(result))  {notify("<?= Txt::trad("categoryNotifChangeOrder") ?>","success");}
				});
			}
		});
	}

	////	Affiche le formulaire d'édition des catégories
	$(".vCategoryEdit").on("click",function(){																//Click sur le bouton "modifier" ou "ajouter"
		let selectFieldset="#"+$(this).closest("fieldset").attr("id");										//Sélecteur du fieldset de la catégorie (balise parent via "closest")
		let selectHeaders=selectFieldset+" .vCategoryMain>div:not(.vCategoryEdit)";							//Sélecteur du header : libellé principal & boutons (sauf "modifier")
		if($(selectFieldset+" form").isDisplayed()){														//Si le formulaire est déjà visible :
			$(selectHeaders).show();																		//Affiche les boutons delete/changeOrder
			$(selectFieldset+" form").slideUp();															//Masque le formulaire
		}else{																								//Si le formulaire n'est pas visible :
			$(selectHeaders).hide(); 																		//Masque les boutons delete/changeOrder
			$(selectFieldset+" form").slideDown().find("input[name='title']").focusAlt();					//Affiche le formulaire + Focus sur le champ "title"
			$("fieldset").not(selectFieldset).has("form:visible").find(".vCategoryEdit").trigger("click");	//Ferme les formulaires ouverts sur d'autres catégories (via trigger .vCategoryEdit)
		}
	});

	////	Controle l'affectation aux espaces
	$("[name='spaceList[]']").on("change",function(){
		let selectFieldset="#"+$(this).closest("fieldset").attr("id");																				//Sélecteur du fieldset de la catégorie (balise parent via "closest")
		if(this.value=="allSpaces" && this.checked==true)	{$(selectFieldset+" [name='spaceList[]']").not(this).prop("checked",false);}			//Déselectionne chaque espace
		else												{$(selectFieldset+" [name='spaceList[]'][value='allSpaces']").prop("checked",false);}	//Déselectionne "Visible sur tous les espaces"
	});

	////	Controle du formulaire
	$("form").on("submit",function(){
		//Vérif la présence du titre
		if($(this).find("input[name='title']").isEmpty()){
			$(this).find("input[name='title']").focusPulsate();
			notify("<?= Txt::trad("requiredFields")." : ".Txt::trad("title") ?>");
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
function colorPickerChange(tmpColor, fieldsetId){
	$(fieldsetId+" input[name='title']").css("background-color",tmpColor.hexString);	//Modif le background du "title"
	$(fieldsetId+" input[name='color']").val(tmpColor.hexString);						//Modif l'input "hidden"
}
</script>

<!--CHARGE LE COLORPICKER-->
<script src="app/js/iro.min.js"></script>


<style>
#bodyLightbox						{max-width:700px;}
fieldset							{margin-top:35px;}/*surcharge*/
.lightboxTitle img					{margin:0px 15px;}
.vCategoryMain						{display:table; width:100%;}
.vCategoryMain>div					{display:table-cell;}
.vCategoryLabel						{font-size:1.05rem;}
.vCategoryAutor						{text-transform:lowercase; margin-top:5px; opacity:0.8;}
.vCategoryModif, .vCategoryDelete	{width:1%; white-space:nowrap; padding:0px 10px; text-align:right; vertical-align:middle;}/*Width ajusté au contenu via 'nowrap'*/
.vCategoryAdd						{font-size:1.1rem; text-align:center;}
form								{display:none; margin-top:25px;}/*masque par défaut*/
form input[name='title']			{width:300px; max-width:80%; color:white; margin-right:5px;}
form input[name='description']		{width:100%; margin-top:15px; margin-bottom:5px;}
.vSpaceList							{margin-top:10px; max-height:150px; overflow-y:auto;}
.vSpaceList>div						{display:inline-block; width:48%; margin:10px 10px 0px 0px;}
.vLabelAllSpaces					{font-style:italic;}
.submitButtonMain					{margin-top:30px;}/*surcharge du button*/
.changeOrderShadow					{height:50px;}/*surcharge*/
/*RESPONSIVE SMARTPHONE*/
@media screen and (max-width:490px){
	.vSpaceList>div 					{display:block; width:100%; margin:15px 0px;}
	.vCategoryModif, .vCategoryDelete	{font-size:0.9rem;}
}
</style>


<div>
	<div class="lightboxTitle">
		<img src="app/img/category.png"><?= Txt::trad($tradModulePrefix."_CAT_editTitle") ?>
		<div class="lightboxTitleDetail"><?= Txt::trad($tradModulePrefix."_CAT_editInfo") ?></div>
	</div>
	<div id="categoryList">
		<?php foreach($categoriesList as $tmpObj){ ?>
		<fieldset id="fieldsetCat<?= $tmpObj->_id ?>">
			<div class="vCategoryMain">
				<!--CATEGORIE EXISTANTE : LABEL ET MENU-->
				<?php if($tmpObj->isNew()==false){ ?>
					<div class="vCategoryLabel" <?= Txt::tooltip($tmpObj->description) ?> >
						<div><?= $tmpObj->getLabel() ?></div>
						<div class="vCategoryAutor"><?= Txt::trad("createdBy").' '.$tmpObj->autorLabel() ?></div>
					</div>
					<div class="vCategoryModif vCategoryEdit sLink"><img src="app/img/edit.png"> <?= Txt::trad("modify") ?></div>
					<div class="vCategoryDelete" onclick="confirmDelete('<?= $tmpObj->getUrl('delete') ?>')"><img src="app/img/delete.png"> <?= Txt::trad("delete") ?></div>
					<?php if(Ctrl::$curUser->isGeneralAdmin()){ ?>
					<div class="changeOrder" <?= Txt::tooltip("changeOrder") ?> ><img src="app/img/changeOrder.png"><input type="hidden" name="changeOrderIds[]" value="<?= $tmpObj->_id ?>"></div>
					<?php } ?>
				<!--AJOUTER UNE NOUVELLE CATEGORIE-->
				<?php }else{ ?>
					<div class="vCategoryAdd vCategoryEdit sLink"><img src="app/img/plus.png">&nbsp; <?= Txt::trad($tradModulePrefix."_CAT_editAdd") ?></div>
				<?php } ?>
			</div>
			<!--FORMULAIRE D'EDITION DE LA CATEGORIE-->
			<form action="index.php" method="post">
				<input type="text" name="title" value="<?= $tmpObj->title ?>" id="titleInput<?= $tmpObj->_id ?>" placeholder="<?= Txt::trad("title") ?>" style="background:<?= $tmpObj->color ?>">
				<img src="app/img/colorPicker.png" class="menuLauncher" for="colorPickerDiv<?= $tmpObj->_id ?>">
				<div class="colorPicker menuContext" id="colorPickerDiv<?= $tmpObj->_id ?>">
					<div id="colorPickerMenu<?= $tmpObj->_id ?>"></div>
					<script>
						////	Créé un nouveau colorPicker pour la catégorie courante
						new iro.ColorPicker('#colorPickerMenu<?= $tmpObj->_id ?>', {width:150,color:"<?= $tmpObj->color ?>"})
							.on('color:change', function(tmpColor){ colorPickerChange(tmpColor,"<?= "#fieldsetCat".$tmpObj->_id ?>"); });
					</script>
				</div>
				<input type="hidden" name="color" value="<?= $tmpObj->color ?>">
				<input type="text" name="description" value="<?= $tmpObj->description ?>" placeholder="<?= Txt::trad("description") ?>">
				<div class="vSpaceList">
					<?php
					////	"VISIBLE TOUS LES ESPACES" (Admin général || Modif d'un user et case déjà cochée)
					if(Ctrl::$curUser->isGeneralAdmin() || ($tmpObj->isNew()==false && empty($tmpObj->_idSpaces))){
						$boxId=uniqid();
						$boxChecked=empty($tmpObj->_idSpaces)  ?  "checked"  :  null;
						echo '<div>
								<input type="checkbox" name="spaceList[]" value="allSpaces" id="'.$boxId.'" '.$boxChecked.'>
								<label for="'.$boxId.'" class="vLabelAllSpaces">'.Txt::trad("visibleAllSpaces").'</label>
							</div>';
					}
					////	LISTE DES ESPACES
					foreach($spaceList as $tmpSpace){
						$boxId=uniqid();
						$boxChecked=in_array($tmpSpace->_id,$tmpObj->spaceIds)  ?  "checked"  :  null;
						echo '<div>
								<input type="checkbox" name="spaceList[]" value="'.$tmpSpace->_id.'" id="'.$boxId.'" '.$boxChecked.'>
								<label for="'.$boxId.'" '.Txt::tooltip(Txt::trad("visibleOnSpace").' : '.$tmpSpace->name).'>'.$tmpSpace->name.'</label>
							  </div>';
					}
					?>
				</div>
				<input type="hidden" name="objectType" value="<?= Req::param("objectType") ?>">
				<input type="hidden" name="typeId" value="<?= $tmpObj->_typeId ?>">
				<?= Txt::submitButton() ?>
			</form>
		</fieldset>
		<?php } ?>
	</div>
</div>