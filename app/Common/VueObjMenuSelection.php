<script>
////	INIT : Switch la sélection des objets
$(function(){
	$("#objSelectMenu").click(function(){
		$("[name='objectsTypeId[]']").each(function(){
			objSelect(this.id.replace("_selectBox",""));
		});
	});
});

////	Switch la sélection d'un objet
function objSelect(objectBlockId)
{
	//Swich la sélection de la checkbox
	var selectBoxId="#"+objectBlockId+"_selectBox";
	$(selectBoxId).prop("checked", !$(selectBoxId).prop("checked"));
	//Change le style du block de l'objet
	if($(selectBoxId).prop("checked"))	{$("#"+objectBlockId).addClass("objContainerSelect");}
	else								{$("#"+objectBlockId).removeClass("objContainerSelect");}
	//Affiche/Masque le menu de sélection
	if($(":checked[name='objectsTypeId[]']").length==0){
		$("#objSelectSubMenu").slideUp();
		$("#objSelectLabel").html("<?= Txt::trad("selectAll") ?>");
	}else{
		$("#objSelectSubMenu").slideDown();
		$("#objSelectLabel").html("<?= Txt::trad("invertSelection") ?>");
	}
}

////	Action sur les objets sélectionnés
function objectsTypeId(urlRedir, openPage)
{
	//Ajoute les objets
	var objType=null;
	var objectSelector=":checked[name='objectsTypeId[]']";
	$(objectSelector).each(function(){
		var typeId=this.value.split("-");//"fileFolder-22" -> ['fileFolder',22]
		if(objType!=typeId[0])	{urlRedir+="&objectsTypeId["+typeId[0]+"]="+typeId[1];   objType=typeId[0];}
		else					{urlRedir+="-"+typeId[1];}
	});
	//Confirme une désaffectation d'espace?
	if(/deleteFromCurSpace/i.test(urlRedir)){
		if(!confirm("<?= Txt::trad("USER_deleteFromCurSpaceConfirm") ?>  ("+$(objectSelector).length+" <?= Txt::trad("confirmDeleteNbElems") ?>)"))	{return false;}
	}
	//Confirme une suppression?
	else if(/delete/i.test(urlRedir)){
		var confirmDelete="<?= Txt::trad("confirmDelete") ?>  ("+$(objectSelector).length+" <?= Txt::trad("confirmDeleteNbElems") ?>)";
		var confirmDeleteDbl="<?= Txt::trad("confirmDeleteDbl") ?>";
		if(!confirm(confirmDelete) || !confirm(confirmDeleteDbl))	{return false;}
	}
	//Ouvre une page ou redirige
	if(openPage=="newPage")			{window.open(urlRedir);}
	else if(openPage=="lightbox")	{lightboxOpen(urlRedir);}
	else							{redir(urlRedir);}
}
</script>

<style>
#objSelectSubMenu				{display:none;}
#objSelectSubMenu .menuIcon		{width:45px; text-align:right!important;}/*45px a lieu de 35px. srcharge "text-align"*/
#objSelectSubMenu .menuIcon img	{max-height:22px;}

/*RESPONSIVE*/
@media screen and (max-width:1023px){
	#objSelectMenu	{display:none!important;}
}
</style>


<div id="objSelectMenu" class="menuLine sLink">
	<div class='menuIcon'><img src="app/img/check.png"></div><div id="objSelectLabel"><?= Txt::trad("selectAll") ?></div>
</div>

<span id="objSelectSubMenu">
	<?php
	////	FICHIERS : TELECHARGER FICHIERS
	if(Req::$curCtrl=="file")  {echo "<div class='menuLine sLink' onclick=\"objectsTypeId('?ctrl=file&action=downloadArchive','newPage');\"><div class='menuIcon'><img src='app/img/download.png'></div><div>".Txt::trad("FILE_downloadSelection")."</div></div>";}

	////	USER/CONTACT : VOIR SUR UNE CARTE
	if(Req::$curCtrl=="contact" || Req::$curCtrl=="user")  {echo "<div class='menuLine sLink' onclick=\"objectsTypeId('?ctrl=misc&action=PersonsMap','lightbox');\"><div class='menuIcon'><img src='app/img/map.png'></div><div>".Txt::trad("showOnMap")."</div></div>";}

	////	USER : DESAFFECTER D'UN ESPACE
	if(Req::$curCtrl=="user" && Ctrl::$curUser->isAdminSpace() && self::$curSpace->allUsersAffected()==false)  {echo "<div class='menuLine sLink' onclick=\"objectsTypeId('?ctrl=user&action=DeleteFromCurSpace');\"><div class='menuIcon'><img src='app/img/delete.png'></div><div>".Txt::trad("USER_deleteFromCurSpace")."</div></div>";}

	////	USER : SUPPRIMER DEFINITIVEMENT
	if(Req::$curCtrl=="user" && Ctrl::$curUser->isAdminGeneral())  {echo "<div class='menuLine sLink' onclick=\"objectsTypeId('?ctrl=object&action=delete');\"><div class='menuIcon'><img src='app/img/delete.png'></div><div>".Txt::trad("USER_deleteDefinitely")."</div></div>";}

	////	DOSSIER : DEPLACER DES OBJETS
	if($curFolderIsWritable==true && $rootFolderHasTree==true)  {echo "<div class='menuLine sLink' onclick=\"objectsTypeId('?ctrl=object&action=FolderMove&typeId=".Ctrl::$curContainer->_typeId."','lightbox');\"><div class='menuIcon'><img src='app/img/folder/folderMove.png'></div><div>".Txt::trad("changeFolder")."</div></div>";}

	////	DOSSIER : SUPPRIMER DES OBJETS
	if($curFolderIsWritable==true)  {echo "<div class='menuLine sLink' onclick=\"objectsTypeId('?ctrl=object&action=Delete');\"><div class='menuIcon'><img src='app/img/delete.png'></div><div>".Txt::trad("deleteElems")."</div></div>";}
	?>
	<hr>
</span>