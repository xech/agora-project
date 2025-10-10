<script>
ready(function(){
	////	Sélectionne tous les objets : passe tout à "false" puis switch la sélection
	$("#objSelectAll").on("click",function(){
		$(".objSelectCheckbox").prop("checked",false).each(function(){ objSelectSwitch(this.id); });
	});
	////	Switch la sélection de tous les objets
	$("#objSelectSwitch").on("click",function(){
		$(".objSelectCheckbox").each(function(){ objSelectSwitch(this.id); });
	});
});

////	Switch la sélection d'un objet
function objSelectSwitch(menuId)
{
	var menuId=menuId.replace(/(objCheckbox|objContainer)/i,"");											//Récupère le "MenuId" de l'objet (sans préfixe : cf. "VueObjMenuContext")
	$("#objCheckbox"+menuId).prop("checked", !$("#objCheckbox"+menuId).prop("checked"));					//Swich la sélection de la checkbox
	$("#objContainer"+menuId).toggleClass("objContainerSelect", $("#objCheckbox"+menuId).prop("checked"));	//Swich la sélection/class du block de l'objet
	if($(".objSelectCheckbox:checked").length>0)	{$("#objSelectMenu").slideDown(300);}					//Affiche le menu des objets sélectionnés
	else											{$("#objSelectMenu").slideUp(300);}						//Affiche le menu des objets sélectionnés
}

////	Action sur les objets sélectionnés
async function menuSelectAction(urlRedir, lightbox)
{
	let objectsSelector=".objSelectCheckbox:checked";																	// Sélecteur JQuery des objets
	if($(objectsSelector).length>0){																					// Vérif s'il y a des objets sélectionnés
		let confirmContent=$(objectsSelector).length+" <?= Txt::trad("confirmDeleteSelectNb") ?>";						// Nb d'objets sélectionnés (ex: "55 éléments sélectionnés")
		let curTypeId=null;																								// Init curTypeId
		$(objectsSelector).each(function(){																				// Parcourt chaque objet sélectionné
			let typeId=this.value.split("-");																			// TypeId en tableau (ex: "file-22" -> ["file",22])
			if(curTypeId!=typeId[0])	{urlRedir+="&objectsTypeId["+typeId[0]+"]="+typeId[1];  curTypeId=typeId[0];}	// Ajoute à l'url un nouveau "objectsTypeId"  (ex: "&objectsTypeId[file]=22")
			else						{urlRedir+="-"+typeId[1];}														// Ajoute à l'url le "_id" de l'objet courant (ex: "-33")
		});
		//// Désaffectation d'users à l'espace courant
		if(/deleteFromCurSpace/i.test(urlRedir)){
			if(await confirmAlt("<?= Txt::trad("USER_deleteFromCurSpaceConfirm") ?>",confirmContent)==false)  {return false;}
		}
		//// Suppression d'objets
		else if(/delete/i.test(urlRedir)){
			if(await confirmDelete(urlRedir,confirmContent)==false)  {return false;}
		}
		//// Redirection (ex: download d'archive)
		else if(typeof lightbox=="undefined" && await confirmAlt())
			{redir(urlRedir);}
		//// Ouvre une lightbox (ex: folderMove)
		else if(typeof lightbox!="undefined" && lightbox==true)
			{lightboxOpen(urlRedir);}
	}
}
</script>


<style>
/*menu masqué par défaut*/
#objSelectMenu	{display:none;}	
</style>


<div id="objSelectMenu" class="miscContainer">
	<!--"TELECHARGER LES FICHIERS" (file)-->
	<?php if(Req::$curCtrl=="file"){ ?>
	<div class="menuLine" onclick="menuSelectAction('?ctrl=file&action=downloadArchive')"><div class="menuIcon"><img src="app/img/download.png"></div><div><?= Txt::trad("FILE_downloadSelection") ?></div></div>
	<?php } ?>

	<!--"VOIR SUR UNE CARTE" (user / contact)-->
	<?php if(Req::$curCtrl=="user" || Req::$curCtrl=="contact"){ ?>
		<div class="menuLine" onclick="menuSelectAction('?ctrl=misc&action=PersonsMap',true)" <?= Txt::tooltip("showOnMapTooltip") ?> ><div class="menuIcon"><img src="app/img/map.png"></div><div><?= Txt::trad("showOnMap") ?></div></div>
	<?php } ?>

	<!--"DEPLACER VERS UN AUTRE DOSSIER"-->
	<?php if($folderMoveOption==true){ ?>
	<div class="menuLine" onclick="menuSelectAction('?ctrl=object&action=FolderMove&typeId=<?= Ctrl::$curContainer->_typeId ?>',true)"><div class="menuIcon"><img src="app/img/folder/folderMove.png"></div><div><?= Txt::trad("changeFolder") ?></div></div>
	<?php } ?>

	<!--"DESAFFECTER DE L'ESPACE" (user)-->
	<?php if($deleteFromSpaceOption==true){ ?>
	<div class="menuLine" onclick="menuSelectAction('?ctrl=user&action=DeleteFromCurSpace')"><div class="menuIcon"><img src="app/img/delete.png"></div><div><?= Txt::trad("USER_deleteFromCurSpace") ?></div></div>
	<?php } ?>

	<!--"SUPPRIMER"-->
	<?php if($deleteOption==true){ ?>
	<div class="menuLine" onclick="menuSelectAction('?ctrl=object&action=Delete')"><div class="menuIcon"><img src="app/img/delete.png"></div><div><?= Req::$curCtrl=="user" ? Txt::trad("USER_deleteDefinitely") : Txt::trad("deleteElems") ?></div></div>
	<?php } ?>

	<!--"SELECTIONNER TOUT" && "INVERSER LA SELECTION"-->
	<div class="menuLine sLink" id="objSelectAll"><div class='menuIcon'><img src="app/img/checkAll.png"></div><div><?= Txt::trad("selectAll") ?></div></div>
	<div class="menuLine sLink" id="objSelectSwitch"><div class='menuIcon'><img src="app/img/checkSwitch.png"></div><div><?= Txt::trad("selectSwitch") ?></div></div>
</div>