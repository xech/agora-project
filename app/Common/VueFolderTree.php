<script>
////	Init
ready(function(){
	////	Ids des dossiers du "path" courant
	curPathFolderIds=[<?= implode(",",Ctrl::$curContainer->folderPath("id")) ?>];
	////	Init l'affichage de l'arborescence
	$(".vTreeFolder").each(function(){
		//Init
		var folderId=parseInt($(this).attr("data-folderId"));
		var parentFolderId=parseInt($(this).attr("data-parentFolderId"));
		var folderTreeLevel=parseInt($(this).attr("data-folderTreeLevel"));
		//Affiche le dossier racine et les dossiers à la racine (niveau <=1) ET affiche les dossiers du chemin courant
		if(folderTreeLevel<=1 || $.inArray(parentFolderId,curPathFolderIds)!==-1)  {$(this).css("display","table");}
		//Ajoute un padding en fonction du niveau du dossier : 15px à gauche à partir du niveau 2
		if(folderTreeLevel>=2)  {$(this).find(".vTreeFolderIcon").css("padding-left",((folderTreeLevel-1)*15)+"px");}
		//Icone d'ouverture du dossier : affiche uniquement s'il ya des sous-dossiers + ajoute la class ".vIconOpened" s'il est déjà ouvert (dans le "path" courant)
		var openIconSelector=".vTreeFolder[data-folderId='"+folderId+"'] .vIconOpen";
		if($(".vTreeFolder[data-parentFolderId='"+folderId+"']").length>0)  {$(openIconSelector).css("visibility","visible");}//"visibility" et non "display" pour conserver les espaces
		if($.inArray(folderId,curPathFolderIds)!==-1)  {$(openIconSelector).addClass("vIconOpened");}
	});

	////	Valide le formulaire de déplacement d'un objet dans l'arborescence
	$("#folderTreeForm").on("submit",function(){
		$(".vTreeFolder .vNewFolderId").each(function(){ $(this).prop("disabled",false); });
	});
});

////	Ouvre/Ferme un dossier
function folderTreeDisplay(folderId, toggle)
{
	//Init
	var openIconSelector=".vTreeFolder[data-folderId='"+folderId+"'] .vIconOpen";
	var subFoldersSelector=".vTreeFolder[data-parentFolderId='"+folderId+"']";
	//Affiche les sous-dossiers : niveau juste en dessous
	if(toggle==true && $(subFoldersSelector).isDisplayed()==false){
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
function folderMove(newFolderId){
	$(".vTreeFolderLabel").removeClass("linkSelect").find(".vNewFolderId").prop("checked",false);											//Réinitialise le label et checkbox de tous les dossiers
	$(".vTreeFolder[data-folderId='"+newFolderId+"'] .vTreeFolderLabel").addClass("linkSelect").find(".vNewFolderId").prop("checked",true);	//Check le dossier sélectionné
}
</script>


<style>
<?= $context=='move' ? '#bodyLightbox {max-width:500px;}' : null ?>
#treeFolders						{user-select:none; -webkit-user-select:none; padding:4px;}
.vTreeFolder						{display:none;}											/*dossier masqué par défaut*/
.vTreeFolder>div					{display:table-cell; padding:3px; vertical-align:top;}	/*cellules du dossier */
.vTreeFolderIcon					{white-space:nowrap;}									/*icone du dossier : pas de retour à la ligne*/
.vTreeFolderIcon .vIconOpen			{visibility:hidden; margin-right:3px;}					/*icone d'ouverture du dossier (optionnel)*/
.vTreeFolder:first-child .vIconOpen	{display:none!important;}								/*dossier root : pas d'icone de d'ouverture du dossier*/
.vIconOpened						{transform:rotate(40deg); filter:brightness(0);}

/*RESPONSIVE SMALL*/
@media screen and (max-width:1024px){
	#menuMobileMain #treeFolders	{position:relative; max-height:400px; overflow-y:auto;}/*menu mobile: "relative" car les "arrow" d'ouverture de dossier sont en position absolute*/
}
</style>


<div id="treeFolders">
	<?php
	////	DEPLACEMENT DE DOSSIER : AFFICHE LE FORMULAIRE
	if($context=="move")  {echo '<form action="index.php" method="post" id="folderTreeForm">';}

	////	AFFICHE CHAQUE DOSSIER DE L'ARBORESCENCE
	foreach(Ctrl::$curRootFolder->folderTree() as $tmpFolder)
	{
		//Tooltip
		if($tmpFolder->isRootFolder() && Ctrl::$curUser->isSpaceAdmin())		{$folderTooltip=Txt::trad("rootFolderTooltip");}
		elseif(strlen($tmpFolder->name)>70 || !empty($tmpFolder->description))	{$folderTooltip=$tmpFolder->name."<hr>".$tmpFolder->description;}
		else																	{$folderTooltip=null;}
		//Style && Actions Js && Input de changement de dossier conteneur (jamais le dossier courant!)
		$isCurFolder=($tmpFolder->_id==Ctrl::$curContainer->_id);
		$folderLabelClass=($isCurFolder==true)   ?  'linkSelect'  :  null;
		$folderLabelActionJs=($context=="nav")   ?  'redir(\''.$tmpFolder->getUrl().'\')'  :  'folderMove('.$tmpFolder->_id.')';
		$folderLabelCheckbox=($context=="move" && $isCurFolder==false)  ?  '<input type="checkbox" name="newFolderId" class="vNewFolderId" value="'.$tmpFolder->_id.'" disabled>'  :  null;
		//Affiche le dossier
		echo '<div class="vTreeFolder" data-folderId="'.$tmpFolder->_id.'" data-parentFolderId="'.$tmpFolder->_idContainer.'" data-folderTreeLevel="'.$tmpFolder->treeLevel.'" '.Txt::tooltip($folderTooltip).'>
				<div class="vTreeFolderIcon" onclick="folderTreeDisplay('.$tmpFolder->_id.',true)"><img src="app/img/arrowRightSmall.png" class="vIconOpen"><img src="app/img/folder/folderSmall.png"></div>
				<div class="vTreeFolderLabel '.$folderLabelClass.'" onclick="'.$folderLabelActionJs.'">'.Txt::reduce($tmpFolder->name,80).$folderLabelCheckbox.'</div>
			  </div>';
	}

	////	DEPLACEMENT DE DOSSIER : SUBMIT ET FIN DU FORMULAIRE
	if($context=="move"){
		foreach(Req::param("objectsTypeId") as $objType=>$objIds)  {echo '<input type="hidden" name="objectsTypeId['.$objType.']" value="'.$objIds.'">';}
		echo Txt::submitButton()."</form>";
	}
	////	NAVIGATION DE DOSSIERS : SEPARATION "HR"
	if($context=="nav")  {echo "<hr>";}
	?>
</div>