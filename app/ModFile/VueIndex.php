<meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=2.0">

<script>
////	INIT
$(function(){
	//Télécharge/visualise un fichier : on ne sélectionne pas le block!
	$(".objIcon span,.objLabel span").on("click",function(event){
		event.stopPropagation();
	});
});
</script>

<style>
/*basic*/
.objIcon span							{cursor:pointer;}										/*cursor de l'icone/image : pas de "select"*/
.objLabel span							{cursor:url("app/img/download.png"),pointer!important;}	/*cursor du nom du fichier*/
.vVersionsMenu							{margin-left:10px;}										/*bouton "versions de fichiers"*/
.hasThumb [data-fancybox='images']		{cursor:url("app/img/search.png"),pointer!important;}	/*vignettes d'images*/
.objIconDownload						{height:12px; margin-right:5px; filter:grayscale(0.8);}	/*bouton download*/
.objBlocks .objIconDownload				{float:right;}											/*idem: blocks*/

/*Affichage Block*/
.objBlocks .objContainer				{height:150px; width:150px; min-width:150px; max-width:250px;}	/*surcharge: taille des "block"*/
.objBlocks .hasThumb .objMenuBurger, .objBlocks .hasThumb .objMiscMenus	{filter:contrast(150%);}		/*images/vignettes pdf : met en avant le menu context*/
.objBlocks .hasThumb img				{margin-top:0px!important;}										/*images/vignettes pdf : pas de margin-top*/
.objBlocks .thumbLandscape .objIcon img, .objBlocks .thumbPortrait .objIcon img  {min-width:100%; max-width:none; min-height:100%; max-height:none;}/*les images couvrent tout le Block*/
.objBlocks .thumbLandscape .objIcon img	{height:100%;}													/*image paysage: 100% de haut*/
.objBlocks .thumbPortrait .objIcon img	{width:100%; margin-top:-45%!important;}						/*image portrait: 100% de large + recentré*/
</style>

<div id="pageFull">
	<div id="pageModuleMenu">
		<div id="pageModMenu" class="miscContainer">
			<?php
			////	AJOUT D'ELEMENTS
			if(Ctrl::$curContainer->editContentRight()){
				echo "<div class='menuLine sLink' onclick=\"lightboxOpen('".MdlFile::urlAddFiles()."')\"><div class='menuIcon'><img src='app/img/plus.png'></div><div>".Txt::trad("FILE_addFile")."</div></div>
					  <div class='menuLine sLink' onclick=\"lightboxOpen('".MdlFileFolder::getUrlNew()."')\"><div class='menuIcon'><img src='app/img/folder/folderAdd.png'></div><div>".Txt::trad("addFolder")."</div></div>
					  <hr>";
			}
			////	ARBORESCENCE  &  MENU DE SELECTION/AFFICHAGE/TRI  &  DESCRIPTION DU CONTENU  &  ESPACE DISQUE
			echo CtrlObject::folderTreeMenu().MdlFile::menuSelectObjects().MdlFile::menuDisplayMode().MdlFile::menuSort();
			echo "<div class='menuLine'><div class='menuIcon'><img src='app/img/info.png'></div><div>".Ctrl::$curContainer->folderContentDescription()."</div></div>";
			if(!empty($fillRateBar))  {echo "<div class='menuLine'><div class='menuIcon'><img src='app/img/diskSpace".($diskSpaceAlert==true?"Alert":null).".png'></div><div>".$fillRateBar."</div></div>";}
			?>
		</div>
	</div>
	<div id="pageFullContent" class="<?= MdlFile::getDisplayMode()=="line"?"objLines":"objBlocks" ?>">
		<?php
		////	PATH DU DOSSIER COURANT & LISTE DES DOSSIERS
		echo CtrlObject::folderPathMenu(Txt::trad("FILE_addFile"),MdlFile::urlAddFiles());
		echo $foldersList;
		////	LISTE DES FICHIERS
		foreach($filesList as $tmpFile)
		{
			echo $tmpFile->divContainer("objContentCenter ".$tmpFile->hasThumbClass).$tmpFile->contextMenu().
				"<div class=\"objContent ".$tmpFile->thumbClass."\">
					<div class='objIcon'><span ".$tmpFile->iconLink." title=\"".$tmpFile->iconTooltip."\"><img src=\"".$tmpFile->typeIcon()."\"></span></div>
					<div class='objLabel'><span ".$tmpFile->labelLink." title=\"".$tmpFile->tooltip."\"><img src='app/img/download.png' class='objIconDownload'>".Txt::reduce($tmpFile->name,50)."</span>".$tmpFile->versionsMenu("icon")."</div>
					<div class='objDetails'>".File::displaySize($tmpFile->octetSize)."</div>
					<div class='objAutorDate'>".$tmpFile->displayAutorDate()."</div>
				</div>
			</div>";
		}
		////	AUCUN CONTENU & AJOUTER
		if(empty($foldersList) && empty($filesList)){
			$addElement=(Ctrl::$curContainer->editContentRight())  ?  "<div class='sLink' onclick=\"lightboxOpen('".MdlFile::urlAddFiles()."')\"><img src='app/img/plus.png'> ".Txt::trad("FILE_addFile")."</div>"  :  null;
			echo "<div class='emptyContainer'>".Txt::trad("FILE_noFile").$addElement."</div>";
		}
		?>
	</div>
</div>