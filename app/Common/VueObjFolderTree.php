<script>
////	Resize
<?php if($context=="move"){ ?>lightboxSetWidth(500);<?php } ?>

////	Init l'affichage de l'arborescence (une fois la page chargée, pas avant!)
$(function(){
	//Ids des dossiers du "path" courant
	curPathFolderIds=[<?= implode(",",Ctrl::$curContainer->folderPath("id")) ?>];
	//Affiche chaque dossier de l'arborescence
	$(".vTreeFolder").each(function(){
		//Init
		var folderTreeLevel=parseInt($(this).attr("data-treeLevel"));
		var folderId=parseInt($(this).attr("data-folderId"));
		var containerId=parseInt($(this).attr("data-containerId"));
		//Ajoute un padding de gauche en fonction du niveau du dossier (à partir du niveau 2, on ajoute 18px à gauche pour chaque niveau, soit la largeur d'une icone "vTreeFolderDep")
		if(folderTreeLevel>=2)  {$(this).find(".vTreeFolderIcon").css("padding-left",((folderTreeLevel-1)*18)+"px");}
		//Affiche les dossiers à la racine (niveau <=1) ET les dossiers du chemin courant
		if(folderTreeLevel<=1 || $.inArray(containerId,curPathFolderIds)!==-1)  {$(this).css("display","table");}
		//Icone "open" : affiche pour les dossiers avec des sous-dossiers && on lui ajoute la class ".vIconOpened" si le dossier est dans le "path" courant
		var openIconSelector=".vTreeFolder[data-folderId='"+folderId+"'] .vIconOpen";
		if($(".vTreeFolder[data-containerId='"+folderId+"']").length>0)  {$(openIconSelector).show();}
		if($.inArray(folderId,curPathFolderIds)!==-1)  {$(openIconSelector).addClass("vIconOpened");}
	});
});

////	Ouvre/Ferme un dossier
function folderTreeDisplay(folderId, toggle)
{
	//Init
	var openIconSelector=".vTreeFolder[data-folderId='"+folderId+"'] .vIconOpen";
	var subFoldersSelector=".vTreeFolder[data-containerId='"+folderId+"']";
	//Affiche les sous-dossiers : niveau juste en dessous
	if(toggle==true && $(subFoldersSelector).is(":visible")==false){
		$(subFoldersSelector).slideDown();
		$(openIconSelector).addClass("vIconOpened");
	}
	//Ferme toute l'arborescence de sous-dossiers (de manière récursive)
	else{
		$(subFoldersSelector).each(function(){ folderTreeDisplay($(this).attr("data-folderId")); });
		$(subFoldersSelector).slideUp();
		$(openIconSelector).removeClass("vIconOpened");
	}
}

////	Déplacement d'objet(s) dans un autre dossier
function folderMove(newFolderId)
{
	//Réinitialise le label/checkbox de tous les dossiers
	$(".vTreeFolder").removeClass("sLinkSelect").find("input[name='newFolderId']").prop("checked",false);
	//Check le dossier sélectionné
	$(".vTreeFolder[data-folderId='"+newFolderId+"']").addClass("sLinkSelect").find("input[name='newFolderId']").prop("checked",true);
}

////	Réactive les inputs "newFolderId" à la validation du formulaire de changement de dossier
function formControl(){
	$(".vTreeFolder input[name='newFolderId']").each(function(){ $(this).prop("disabled",false); });
}
</script>


<style>
#treeFolders		{padding:4px;}
.vTreeFolder		{display:none;}
.vTreeFolder>div	{display:table-cell; padding:3px; vertical-align:top;}
.vTreeFolderIcon	{white-space:nowrap;}/*cellule des icones*/
.vTreeFolder .vTreeFolderDep				{margin-left:5px; margin-right:-7px;}/*icone des dépendance*/
.vTreeFolder:first-child .vTreeFolderDep	{display:none;}/*dossier root : pas d'icone de dépendance*/
.vTreeFolderIcon .vIconOpen					{display:none; position:absolute; margin-top:5px; margin-left:-8px;}
.vIconOpened								{transform:rotate(45deg); filter:brightness(0.8);}

/*RESPONSIVE*/
@media screen and (max-width:1023px){
	#respMenuContent #treeFolders	{position:relative; overflow-y:scroll; max-height:140px;}/*Resp menu : "relative" car les "arrowRight" d'ouverture de dossier sont en position absolute*/
}
</style>


<div id="treeFolders" class="noSelect">
	<?php
	////	DEPLACEMENT DE DOSSIER : AFFICHE LE FORMULAIRE
	if($context=="move")  {echo "<form action='index.php' method='post' onsubmit=\"return formControl()\" class='lightboxContent'>";}
	
	////	AFFICHE CHAQUE DOSSIER DE L'ARBORESCENCE
	foreach($rootFolderTree as $tmpFolder)
	{
		//Init
		if($tmpFolder->isRootFolder() && Ctrl::$curUser->isAdminSpace())		{$folderTooltip=Txt::trad("rootFolderEditInfo");}
		elseif(strlen($tmpFolder->name)>70 || !empty($tmpFolder->description))	{$folderTooltip=$tmpFolder->name."<hr>".$tmpFolder->description;}
		else																	{$folderTooltip=null;}
		$folderSelectClass=($tmpFolder->_id==Ctrl::$curContainer->_id)  ?  "sLinkSelect"  :  "sLink";
		$folderActionJs=($context=="nav")  ?  "redir('".$tmpFolder->getUrl()."')"  :  "folderMove(".$tmpFolder->_id.")";
		$folderMoveCheckbox=($context=="move")  ?  "<input type='checkbox' name='newFolderId' value='".$tmpFolder->_id."' ".($tmpFolder->_id==Ctrl::$curContainer->_id?'checked':null)." disabled>"  :  null;
		//Affiche le dossier
		echo "<div class='vTreeFolder ".$folderSelectClass."' data-folderId=\"".$tmpFolder->_id."\" data-containerId=\"".$tmpFolder->_idContainer."\" data-treeLevel=\"".$tmpFolder->treeLevel."\" title=\"".Txt::tooltip($folderTooltip)."\">
				<div class='vTreeFolderIcon' onclick=\"folderTreeDisplay(".(int)$tmpFolder->_id.",true)\">
					<span class='vTreeFolderDep'><img src='app/img/open.png' class='vIconOpen sLink'><img src='app/img/dependency.png' class='vIconDependency'></span>
					<img src='app/img/folder/folderSmall.png' class='vIconFolder'>
				</div>
				<div onclick=\"".$folderActionJs."\">".Txt::reduce($tmpFolder->name,80).$folderMoveCheckbox."</div>
			  </div>";
	}

	////	DEPLACEMENT DE DOSSIER : SUBMIT ET FIN DU FORMULAIRE
	if($context=="move"){
		foreach(Req::param("objectsTypeId") as $objType=>$objIds)  {echo "<input type='hidden' name=\"objectsTypeId[".$objType."]\" value=\"".$objIds."\">";}
		echo Txt::submitButton()."</form>";
	}
	////	NAVIGATION DE DOSSIERS : SEPARATION "HR"
	else  {echo "<hr>";}
	?>
</div>