<script>
ready(function(){
	////	Sélectionne tous les objets : passe tout à "false" puis switch la sélection
	$("#objSelectAll").on("click",function(){
		$(".objSelectCheckbox").prop("checked",false).each(function(){ objSelectSwitch(this.id); });
	});
	////	Désélectionne tous les objets : passe tout à "true" puis switch la sélection
	$("#objUnselectAll").on("click",function(){
		$(".objSelectCheckbox").prop("checked",true).each(function(){ objSelectSwitch(this.id); });
	});
});

////	Switch la sélection d'un objet
function objSelectSwitch(menuId)
{
	var menuId=menuId.replace(/(objCheckbox|objContent)/i,"");								//Récupère le "MenuId" de l'objet (sans préfixe : cf. "VueObjMenuContext")
	$("#objCheckbox"+menuId).prop("checked", !$("#objCheckbox"+menuId).prop("checked"));	//Swich la sélection de la checkbox
	if($(".objSelectCheckbox:checked").length>0){											//Au moins un objet sélectionné
		$("#objSelectMenu").slideDown();													//Affiche le menu de sélection
		$(".objSelectCheckboxDiv").show();													//Affiche les inputs checkbox
	}
}

////	Action sur les objets sélectionnés
ready(function(){
	$(".menuSelectAction").on("click", function(){
		let selectAction=this.getAttribute("data-select-action");
		let urlRedir=this.getAttribute("data-select-url");
		let objSelector=".objSelectCheckbox:checked";
		//// Vérif s'il y a des objets sélectionnés
		if($(objSelector).length>0){
			//// Affiche le nombre d'objets sélectionnés  &&  Ajoute les "objectsTypeId" dans l'url 
			let confirmInfo=$(objSelector).length+" <?= Txt::trad("confirmDeleteSelectNb") ?>";								// Nombre d'objets sélectionnés
			let curTypeId=null;																								// Init curTypeId
			$(objSelector).each(function(){																					// Parcourt chaque objet sélectionné
				let typeId=this.value.split("-");																			// TypeId en tableau (ex: "file-22" -> ["file",22])
				if(curTypeId!=typeId[0])	{urlRedir+="&objectsTypeId["+typeId[0]+"]="+typeId[1];  curTypeId=typeId[0];}	// Ajoute à l'url un nouveau "objectsTypeId"  (ex: "&objectsTypeId[file]=22")
				else						{urlRedir+="-"+typeId[1];}														// Ajoute à l'url le "_id" de l'objet courant (ex: "-33")
			});
			//// "DESAFFECTER DE L'ESPACE"
			if(selectAction=="user"){
				confirmRedir(urlRedir, "<?= Txt::trad("USER_deleteFromCurSpaceConfirm") ?>", confirmInfo);
			}
			//// "SUPPRIMER"
			if(selectAction=="delete"){
				confirmDelete(urlRedir, confirmInfo);
			}
			//// "TELECHARGER LES FICHIERS"
			if(selectAction=="download"){
				confirmRedir(urlRedir, TRAD_confirmDownload, confirmInfo);
			}
			//// "VOIR SUR UNE CARTE" || "DEPLACER VERS UN AUTRE DOSSIER"
			if(selectAction=="map" || selectAction=="move"){
				lightboxOpen(urlRedir);
			}
		}
	});
});
</script>


<style>
/*menu masqué par défaut*/
#objSelectMenu		{display:none;}
.menuSelectAction	{cursor:pointer;}
</style>


<div id="objSelectMenu" class="miscContent">
	<!--"TELECHARGER LES FICHIERS"-->
	<?php if(Req::$curCtrl=="file"){ ?>
	<div class="menuLine menuSelectAction" data-select-action="download" data-select-url="?ctrl=file&action=downloadArchive"?ctrl=file&action=downloadArchive><div class="menuIcon"><img src="app/img/download.svg"></div><div><?= Txt::trad("FILE_downloadSelection") ?></div></div>
	<?php } ?>

	<!--"VOIR SUR UNE CARTE"-->
	<?php if(Req::$curCtrl=="user" || Req::$curCtrl=="contact"){ ?>
	<div class="menuLine menuSelectAction" data-select-action="map" data-select-url="?ctrl=misc&action=PersonsMap" <?= Txt::tooltip("showOnMapTooltip") ?> ><div class="menuIcon"><img src="app/img/map.svg"></div><div><?= Txt::trad("showOnMap") ?></div></div>
	<?php } ?>

	<!--"DEPLACER VERS UN AUTRE DOSSIER"-->
	<?php if($folderMoveOption==true){ ?>
	<div class="menuLine menuSelectAction" data-select-action="move" data-select-url="?ctrl=object&action=FolderMove&typeId=<?= Ctrl::$curContainer->typeId ?>"><div class="menuIcon"><img src="app/img/folderMove.svg"></div><div><?= Txt::trad("changeFolder") ?></div></div>
	<?php } ?>

	<!--"DESAFFECTER DE L'ESPACE"-->
	<?php if($deleteFromSpaceOption==true){ ?>
	<div class="menuLine menuSelectAction" data-select-action="user" data-select-url="?ctrl=user&action=DeleteFromCurSpace"><div class="menuIcon"><img src="app/img/delete.svg"></div><div><?= Txt::trad("USER_deleteFromCurSpace") ?></div></div>
	<?php } ?>

	<!--"SUPPRIMER"-->
	<?php if($deleteOption==true){ ?>
	<div class="menuLine menuSelectAction" data-select-action="delete" data-select-url="?ctrl=object&action=Delete"><div class="menuIcon"><img src="app/img/delete.svg"></div><div><?= Req::$curCtrl=="user" ? Txt::trad("USER_deleteDefinitely") : Txt::trad("deleteElems") ?></div></div>
	<?php } ?>

	<!--"SELECTIONNER TOUT" && "INVERSER LA SELECTION"-->
	<div class="menuLine link" id="objSelectAll"><div class='menuIcon'><img src="app/img/checkAll.svg"></div><div><?= Txt::trad("selectAll") ?></div></div>
	<div class="menuLine link" id="objUnselectAll"><div class='menuIcon'><img src="app/img/checkNone.svg"></div><div><?= Txt::trad("unselectAll") ?></div></div>
</div>