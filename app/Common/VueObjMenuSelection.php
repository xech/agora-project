<script>
$(function(){
	////	Sélectionner tous les objets (déselectionne tout, puis switch la sélection)
	$("#objSelectAll").click(function(){
		$("[name='objectsTypeId[]']").prop("checked",false);
		$("[name='objectsTypeId[]']").each(function(){ objSelect(this.id); });
	});
	////	Switch la sélection de tous les objets
	$("#objSelectSwitch").click(function(){
		$("[name='objectsTypeId[]']").each(function(){ objSelect(this.id); });
	});
});

////	Swich la sélection d'un objet
function objSelect(menuId)
{
	//"MenuId" de l'objet sans préfixe
	var menuId=menuId.replace(/(objCheckbox|objContainer)/i,"");
	//Swich la sélection de la checkbox
	$("#objCheckbox"+menuId).prop("checked", !$("#objCheckbox"+menuId).prop("checked"));
	//Swich la sélection/class du block de l'objet
	$("#objContainer"+menuId).toggleClass("objContainerSelect", $("#objCheckbox"+menuId).prop("checked"));
	//Affiche/Masque le menu des objets sélectionnés
	if($(":checked[name='objectsTypeId[]']").length==0)	{$("#objSelectMenu").slideUp();}
	else												{$("#objSelectMenu").slideDown();}
}

////	Action sur les objets sélectionnés
function objSelectAction(urlRedir, openPage)
{
	//Ajoute chaque objet sélectionné
	var objectsTypeId=null;
	var objSelector=":checked[name='objectsTypeId[]']";
	$(objSelector).each(function(){
		var typeId=this.value.split("-");																					//Transforme en tableau. Ex: "file-22" -> array('file',22)
		if(objectsTypeId!=typeId[0])	{urlRedir+="&objectsTypeId["+typeId[0]+"]="+typeId[1];  objectsTypeId=typeId[0];}	//Ajoute une nouvelle liste de "objectsTypeId" (ex: "&objectsTypeId[file]=22")
		else							{urlRedir+="-"+typeId[1];}															//Incrémente la liste (ex: "&objectsTypeId[file]=22-33")
	});
	//Confirme une désaffectation d'espace?
	if(/deleteFromCurSpace/i.test(urlRedir)){
		if(!confirm("<?= Txt::trad("USER_deleteFromCurSpaceConfirm") ?> ("+$(objSelector).length+" <?= Txt::trad("confirmDeleteNbElems") ?>)"))  {return false;}
	}
	//Confirme une suppression?
	else if(/delete/i.test(urlRedir)){
		var confirmDelete="<?= Txt::trad("confirmDelete") ?> ("+$(objSelector).length+" <?= Txt::trad("confirmDeleteNbElems") ?>)";
		var confirmDeleteDbl="<?= Txt::trad("confirmDeleteDbl") ?>";
		if(!confirm(confirmDelete) || !confirm(confirmDeleteDbl))  {return false;}
	}
	//Ouvre une page, une lightbox ou redirection simple
	if(openPage=="newPage")			{window.open(urlRedir);}
	else if(openPage=="lightbox")	{lightboxOpen(urlRedir);}
	else							{redir(urlRedir);}
}
</script>


<div id="objSelectMenu">
	<?php
	////	"TELECHARGER LES FICHIERS" (modFile)
	if(Req::$curCtrl=="file")  {echo "<div class='menuLine sLink' onclick=\"objSelectAction('?ctrl=file&action=downloadArchive','newPage')\"><div class='menuIcon'><img src='app/img/download.png'></div><div>".Txt::trad("FILE_downloadSelection")."</div></div>";}

	////	"VOIR SUR UNE CARTE" (modUser / modContact)
	if(Req::$curCtrl=="contact" || Req::$curCtrl=="user")  {echo "<div class='menuLine sLink' onclick=\"objSelectAction('?ctrl=misc&action=PersonsMap','lightbox')\"><div class='menuIcon'><img src='app/img/map.png'></div><div>".Txt::trad("showOnMap")."</div></div>";}

	////	"DEPLACER DES OBJETS" (arborescence)
	if($folderMoveOption==true)  {echo "<div class='menuLine sLink' onclick=\"objSelectAction('?ctrl=object&action=FolderMove&typeId=".Ctrl::$curContainer->_typeId."','lightbox')\"><div class='menuIcon'><img src='app/img/folder/folderMove.png'></div><div>".Txt::trad("changeFolder")."</div></div>";}

	////	"DESAFFECTER DE L'ESPACE" (modUser)
	if(Req::$curCtrl=="user" && Ctrl::$curUser->isAdminSpace() && self::$curSpace->allUsersAffected()==false)  {echo "<div class='menuLine sLink' onclick=\"objSelectAction('?ctrl=user&action=DeleteFromCurSpace')\"><div class='menuIcon'><img src='app/img/delete.png'></div><div>".Txt::trad("USER_deleteFromCurSpace")."</div></div>";}

	////	"SUPPRIMER" (arborescence / subjet / modUser)
	if($curContainerEditContentRight==true  ||  (Req::$curCtrl=="forum" && Ctrl::$curUser->isUser())  ||  (Req::$curCtrl=="user" && Ctrl::$curUser->isAdminGeneral())){
		$deleteLabel=(Req::$curCtrl=="user")  ?  Txt::trad("USER_deleteDefinitely")  :  Txt::trad("deleteElems");
		echo "<div class='menuLine sLink' onclick=\"objSelectAction('?ctrl=object&action=Delete')\"><div class='menuIcon'><img src='app/img/delete.png'></div><div>".$deleteLabel."</div></div>";
	}
	?>
	<!--"SELECTIONNER TOUT" && "INVERSER LA SELECTION"-->
	<div class="menuLine sLink" id="objSelectAll"><div class='menuIcon'><img src="app/img/checkSmall.png"></div><div><?= Txt::trad("selectAll") ?></div></div>
	<div class="menuLine sLink" id="objSelectSwitch"><div class='menuIcon'><img src="app/img/switch.png"></div><div><?= Txt::trad("selectSwitch") ?></div></div>
</div>