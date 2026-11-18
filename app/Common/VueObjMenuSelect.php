<script>
////	Switch la sélection d'un objet
function objSelectSwitch(uniqId)
{
	var toCheck=($("#objCheckboxId"+uniqId).prop("checked")==false);		//Swich la sélection de la checkbox
	$("#objCheckboxId"+uniqId).prop("checked",toCheck).trigger("change");	//Applique la sélection
	if(isMobile())	{menuMobileClose();}									//Ferme si besoin le menuMobile
}

ready(function(){
	/********************************************************************************************************************************
	 * Click d'un .objContainer : sélectionne l'objet  ("off()" annule les triggers précédents si relance de "mainTriggers()")
	 ********************************************************************************************************************************/
	if(!isMobile()){
		$(".objContainer:has(.objSelectBox)").off("click").on("click",function(){
			objSelectSwitch(this.getAttribute("data-uniqId"));
		});
	}

	/**********************************************************************************************************
	 * Affiche / Masque le menu de sélection
	 **********************************************************************************************************/
	$(".objSelectBox").on("change",function(){
		if($(".objSelectBox:checked").length>0)	{$("#objSelectMenu").slideDown();}
		else									{$("#objSelectMenu").slideUp();}
	});

	/**********************************************************************************************************
	 * Action sur les objets sélectionnés
	 **********************************************************************************************************/
	$("div[data-select-action]").on("click", function(){
		let selectAction=this.getAttribute("data-select-action");
		let urlRedir=this.getAttribute("data-select-url");
		let objSelector=".objSelectBox:checked";
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


<div id="objSelectMenu" class="miscContainer">
	<!--"TELECHARGER LES FICHIERS"-->
	<?php if(Req::$curCtrl=="file"){ ?>
	<div class="menuLine link" data-select-action="download" data-select-url="?ctrl=file&action=downloadArchive"?ctrl=file&action=downloadArchive>
		<div class="menuIcon"><img src="app/img/download.svg"></div>
		<div><?= Txt::trad("FILE_downloadSelection") ?></div>
	</div>
	<?php } ?>

	<!--"VOIR SUR UNE CARTE"-->
	<?php if(Req::$curCtrl=="user" || Req::$curCtrl=="contact"){ ?>
	<div class="menuLine link" data-select-action="map" data-select-url="?ctrl=misc&action=PersonsMap" <?= Txt::tooltip("mapShowTooltip") ?> >
		<div class="menuIcon"><img src="app/img/map.svg"></div>
		<div><?= Txt::trad("mapShow") ?></div>
	</div>
	<?php } ?>

	<!--"DEPLACER VERS UN AUTRE DOSSIER"-->
	<?php if($folderMoveOption==true){ ?>
	<div class="menuLine link" data-select-action="move" data-select-url="?ctrl=object&action=FolderMove&typeId=<?= Ctrl::$curContainer->typeId ?>">
		<div class="menuIcon"><img src="app/img/folderMove.svg"></div>
		<div><?= Txt::trad("changeFolder") ?></div>
	</div>
	<?php } ?>

	<!--"DESAFFECTER DE L'ESPACE"-->
	<?php if($deleteFromSpaceOption==true){ ?>
	<div class="menuLine link" data-select-action="user" data-select-url="?ctrl=user&action=DeleteFromCurSpace">
		<div class="menuIcon"><img src="app/img/delete.svg"></div>
		<div><?= Txt::trad("USER_deleteFromCurSpace") ?></div>
	</div>
	<?php } ?>

	<!--"SUPPRIMER"-->
	<?php if($deleteOption==true){ ?>
	<div class="menuLine link" data-select-action="delete" data-select-url="?ctrl=object&action=Delete">
		<div class="menuIcon"><img src="app/img/delete.svg"></div>
		<div><?= Req::$curCtrl=="user" ? Txt::trad("USER_deleteDefinitely") : Txt::trad("deleteElems") ?></div>
	</div>
	<?php } ?>

	<!--"SELECTIONNER TOUT"-->
	<div class="menuLine link" onclick="$('.objSelectBox').prop('checked',true);">
		<div class='menuIcon'><img src="app/img/checkAll.svg"></div>
		<div><?= Txt::trad("selectAll") ?></div>
	</div>

	<!--"INVERSER LA SELECTION"-->
	<div class="menuLine link" onclick="$('.objSelectBox').prop('checked',false);">
		<div class='menuIcon'><img src="app/img/checkNone.svg"></div>
		<div><?= Txt::trad("selectNone") ?></div>
	</div>
</div>