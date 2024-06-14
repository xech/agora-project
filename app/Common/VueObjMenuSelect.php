<script>
$(function(){
	////	Sélectionne tous les objets : passe tout à "false" puis switch la sélection
	$("#objSelectAll").on("click",function(){
		$(".objSelectCheckbox").prop("checked",false);
		$(".objSelectCheckbox").each(function(){ objSelectSwitch(this.id); });
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
function menuSelectAction(urlRedir, lightbox)
{
	var objListSelector=".objSelectCheckbox:checked";
	if($(objListSelector).length>0)
	{
		//Initialise "urlRedir"
		var objCurType=null;
		$(objListSelector).each(function(){																				
			var typeId=this.value.split("-");																			//- typeId en tableau (ex: "file-22" -> ["file",22])
			if(objCurType!=typeId[0])	{urlRedir+="&objectsTypeId["+typeId[0]+"]="+typeId[1];  objCurType=typeId[0];}	//- Ajoute à l'url un nouveau "objectsTypeId"  (ex: "&objectsTypeId[file]=22")
			else						{urlRedir+="-"+typeId[1];}														//- Ajoute à l'url le "_id" de l'objet courant (ex: "-33")
		});
		//Nombre d'elements sélectionnés
		var confirmDeleteSelectNb="\n\n "+$(objListSelector).length+" <?= Txt::trad("confirmDeleteSelectNb") ?>";
		//Confirmations de suppression
		if(/deleteFromCurSpace/i.test(urlRedir)){
			if(!confirm("<?= Txt::trad("USER_deleteFromCurSpaceConfirm") ?> "+confirmDeleteSelectNb))  {return false;}//Désaffectation d'un user à l'espace courant
		}
		else if(/delete/i.test(urlRedir)){
			var firstConfirmDelete=($(objListSelector).length==1)  ?  "\n "+labelConfirmDelete  :  "\n <?= Txt::trad("confirmDeleteSelect") ?> "+confirmDeleteSelectNb;//cf. "labelConfirmDelete" de " VueStructure.php"
			if(!confirm(firstConfirmDelete) || !confirm(labelConfirmDeleteDbl))  {return false;}//cf. "labelConfirmDeleteDbl" de " VueStructure.php"
		}
		//Ouvre une lightbox  ||  Redirection 
		if(lightbox==true)	{lightboxOpen(urlRedir);}
		else				{redir(urlRedir);}
	}
}
</script>


<style>
#objSelectMenu							{display:none;}										/*menu masqué par défaut*/
#objSelectMenu, .objContainerSelect		{box-shadow:2px 2px 5px rgb(80,80,80)!important;}	/*border des elements sélectionnés*/
</style>


<div id="objSelectMenu" class="miscContainer">
	<!--"TELECHARGER LES FICHIERS" (file)-->
	<?php if(Req::$curCtrl=="file"){ ?>
	<div class="menuLine" onclick="menuSelectAction('?ctrl=file&action=downloadArchive',false)"><div class="menuIcon"><img src="app/img/download.png"></div><div><?= Txt::trad("FILE_downloadSelection") ?></div></div>
	<?php } ?>

	<!--"VOIR SUR UNE CARTE" (user / contact)-->
	<?php if(Req::$curCtrl=="user" || Req::$curCtrl=="contact"){ ?>
		<div class="menuLine" onclick="menuSelectAction('?ctrl=misc&action=PersonsMap',true)" title="<?= Txt::trad("showOnMapTooltip") ?>"><div class="menuIcon"><img src="app/img/map.png"></div><div><?= Txt::trad("showOnMap") ?></div></div>
	<?php } ?>

	<!--"DEPLACER" (arbo)-->
	<?php if($folderMoveOption==true){ ?>
	<div class="menuLine" onclick="menuSelectAction('?ctrl=object&action=FolderMove&typeId=<?= Ctrl::$curContainer->_typeId ?>',true)"><div class="menuIcon"><img src="app/img/folder/folderMove.png"></div><div><?= Txt::trad("changeFolder") ?></div></div>
	<?php } ?>

	<!--"DESAFFECTER DE L'ESPACE" (user)-->
	<?php if($deleteFromSpaceOption==true){ ?>
	<div class="menuLine" onclick="menuSelectAction('?ctrl=user&action=DeleteFromCurSpace',false)"><div class="menuIcon"><img src="app/img/delete.png"></div><div><?= Txt::trad("USER_deleteFromCurSpace") ?></div></div>
	<?php } ?>

	<!--"SUPPRIMER"-->
	<?php if($deleteOption==true){ ?>
	<div class="menuLine" onclick="menuSelectAction('?ctrl=object&action=Delete',false)"><div class="menuIcon"><img src="app/img/delete.png"></div><div><?= Req::$curCtrl=="user" ? Txt::trad("USER_deleteDefinitely") : Txt::trad("deleteElems") ?></div></div>
	<?php } ?>

	<!--"SELECTIONNER TOUT" && "INVERSER LA SELECTION"-->
	<div class="menuLine sLink" id="objSelectAll"><div class='menuIcon'><img src="app/img/checkSmall.png"></div><div><?= Txt::trad("selectAll") ?></div></div>
	<div class="menuLine sLink" id="objSelectSwitch"><div class='menuIcon'><img src="app/img/checkSmall.png"></div><div><?= Txt::trad("selectSwitch") ?></div></div>
</div>