<script>
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
	if($(":checked[name='targetObjects[]']").length==0){
		$("#objSelectSubMenu").slideUp();
		$("#objSelectLabel").html("<?= Txt::trad("selectAll") ?>");
	}else{
		$("#objSelectSubMenu").slideDown();
		$("#objSelectLabel").html("<?= Txt::trad("invertSelection") ?>");
	}
}

////	Switch la sélection de tous les objets
function objSelectToggleAll()
{
	$("[name='targetObjects[]']").each(function(){
		objSelect(this.id.replace("_selectBox",""));
	});
}

////	Action sur les objets sélectionnés
function targetObjectsAction(urlRedir, openPage)
{
	//Ajoute les objets
	var tmpObjType=null;
	var objectSelector=":checked[name='targetObjects[]']";
	$(objectSelector).each(function(){
		var targetObjId=this.value.split("-");//"fileFolder-22" -> ['fileFolder',22]
		if(tmpObjType!=targetObjId[0])	{urlRedir+="&targetObjects["+targetObjId[0]+"]="+targetObjId[1];   tmpObjType=targetObjId[0];}
		else							{urlRedir+="-"+targetObjId[1];}
	});
	//Confirme une désaffectation d'espace?
	if(find("DeleteFromCurSpace",urlRedir)){
		if(!confirm("<?= Txt::trad("USER_confirmDeleteFromSpace") ?> ("+$(objectSelector).length+" elements)"))	{return false;}
	}
	//Confirme une suppression?
	else if(find("delete",urlRedir)){
		var confirmDelete="<?= Txt::trad("confirmDelete") ?> ("+$(objectSelector).length+" elements)";
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
#objSelectSubMenu					{display:none;}
#objSelectSubMenu .menuIcon		{width:40px; text-align:right;}
#objSelectSubMenu .menuIcon img	{max-height:22px;}

/*RESPONSIVE*/
@media screen and (max-width:1023px)
{
	#objSelectMenu	{display:none !important;}
}
</style>


<div id="objSelectMenu" class="menuLine sLink" onclick="objSelectToggleAll();"><div class="menuIcon"><img src="app/img/check.png"></div><div id="objSelectLabel"><?= Txt::trad("selectAll") ?></div></div>

<span id="objSelectSubMenu">
	<!--TELECHARGER FICHIERS-->
	<?php if(Req::$curCtrl=="file"){ ?>
	<div class="menuLine sLink" onclick="targetObjectsAction('?ctrl=file&action=downloadArchive','newPage');"><div class="menuIcon"><img src="app/img/download.png"></div><div><?= Txt::trad("FILE_downloadSelection") ?></div></div>
	<?php } ?>
	<!--VOIR DES CONTACTS SUR UNE CARTE-->
	<?php if(Req::$curCtrl=="contact" || Req::$curCtrl=="user"){ ?>
		<div class="menuLine sLink" onclick="targetObjectsAction('?ctrl=misc&action=PersonsMap','lightbox');"><div class='menuIcon'><img src="app/img/map.png"></div><div><?= Txt::trad("showOnMap") ?></div></div>
	<?php } ?>
	<!--DEPLACER/SUPPRIMER LES OBJETS D'UN CONTENEUR DOSSIER-->
	<?php if($curFolderIsWritable==true){ ?>
		<?php if($rootFolderHasTree==true){ ?><div class="menuLine sLink" onclick="targetObjectsAction('?ctrl=object&action=FolderMove&targetObjId=<?= Ctrl::$curContainer->_targetObjId ?>','lightbox');"><div class="menuIcon"><img src="app/img/folder/folderMove.png"></div><div><?= Txt::trad("changeFolder") ?></div></div><?php } ?>
		<div class="menuLine sLink" onclick="targetObjectsAction('?ctrl=object&action=Delete');"><div class="menuIcon"><img src="app/img/delete.png"></div><div><?= Txt::trad("deleteElems") ?></div></div>
	<?php } ?>
	<!--SUPPRIMER/DESAFFECTER DES USERS-->
	<?php if(Req::$curCtrl=="user"){ ?>
		<?php if($_SESSION["displayUsers"]=="space" && Ctrl::$curUser->isAdminSpace() && self::$curSpace->allUsersAffected()==false){ ?>
		<div class="menuLine sLink" onclick="targetObjectsAction('?ctrl=user&action=DeleteFromCurSpace');"><div class='menuIcon'><img src="app/img/delete.png"></div><div><?= Txt::trad("USER_deleteFromSpace") ?></div></div>
		<?php } ?>
		<?php if(Ctrl::$curUser->isAdminGeneral()){ ?>
		<div class="menuLine sLink" onclick="targetObjectsAction('?ctrl=object&action=delete');"><div class='menuIcon'><img src="app/img/delete.png"></div><div><?= Txt::trad("USER_deleteDefinitely") ?></div></div>
		<?php } ?>
	<?php } ?>
	<hr>
</span>