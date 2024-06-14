<script>
////	INIT
$(function(){
	//// Vérifie si la categorie est bien affectée aux espaces sélectionnés pour l'objet courant  :  cf. "VueObjEditMenuSubmit.php"  &  VueCategoryEdit.php"
	if($("[name='objectRight[]']").exist()){											//Vérif si le tableau des droits d'accès est bien instancié
		$("#selectCategory, [name='objectRight[]']").on("change",function(){			//Change de categorie  OU  Sélectionne un espace dans les droits d'accès
			let catSelector="#selectCategory option:selected";							//Sélecteur de la catégorie choisie
			let catSpaceIds=$(catSelector).attr("data-spaceIds");						//Espaces affectés à la catégorie (vide : affecté à tous les espaces)
			if($(catSelector).exist() && catSpaceIds && catSpaceIds.length>0){			//Verif si une categorie est sélectionnée et avec catSpaceIds spécifié (cf. sans theme ou categorie affecté à tous les espaces)
				$("[name='objectRight[]']:checked").each(function(){					//Parcourt chaque espaces sélectionnés
					let _idSpaceTmp=this.value.split("_").shift();						//_id de l'espace (ex: "2_spaceUsers_1" => "2")
					if(catSpaceIds.split(",").indexOf(_idSpaceTmp)==-1){
						let notifyText="<i>"+$(catSelector).text()+"</i> <?= Txt::trad("categoryNotifSpaceAccess") ?> <br><i>"+$(catSelector).attr("data-spacesLabel")+"</i>";
						notify(notifyText, "warning");									//Notif si l'espace n'est pas affecté à la catégorie sélectionnée (ex: "Catégorie truc n'est accessible que sur l'espace Bidule")
						$("#selectCategory").focusRed();								//Focus sur le champ de la categorie
						return false;													//Arrête la boucle
					}
				});
			}
		});
	}
	//// Puis init la catégorie
	if($("#selectCategory option[value='<?= $_idCategory; ?>']").exist())  {$("#selectCategory").val("<?= $_idCategory; ?>").trigger("change");}
});
</script>


<select name="<?= $dbParentField; ?>" id="selectCategory">
	<option value=""><?= Txt::trad($tradPrefix."_categoryUndefined") ?></option>
	<?php
	////	Liste les categories disponibles
	foreach($categoryList as $tmpCat){
		//Label et Ids des espaces sur lequel est dispo la catégorie courante
		$spacesLabel=null;
		if(!empty($tmpCat->spaceIds)){
			foreach($tmpCat->spaceIds as $_idSpace)  {$spacesLabel.=", ".Ctrl::getObj("space",$_idSpace)->name;}
			$spacesLabel=trim($spacesLabel,",");
		}
		//Affiche l'option
		echo '<option value="'.$tmpCat->_id.'" data-color="'.$tmpCat->color.'" data-spaceIds="'.implode(",",$tmpCat->spaceIds).'" data-spacesLabel="'.$spacesLabel.'">'.$tmpCat->title.'</option>';
	}
	?>
</select>