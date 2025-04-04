<style>
.objBlocks .objContainer														{height:160px; width:160px; min-width:160px; max-width:250px;}						/*surcharge du conteneur "objBlocks"*/
.objBlocks .objIcon																{position:absolute; overflow:hidden; width:100%!important; height:100%!important; border-radius:3px;}/*centre l'icone (dossier, types de fichier, img "fullsize")*/
.objBlocks .objIcon img															{margin-top:20px;}																	/*idem ...sauf image "fullsize"*/
.objBlocks .thumbLandscape .objIcon img, .objBlocks .thumbPortrait .objIcon img	{min-width:100%; max-width:none; min-height:100%; max-height:none;}					/*images "fullsize" : couvrent tout le Block*/
.objBlocks .thumbLandscape .objIcon img											{height:100%;}																		/*images "fullsize" paysage : 100% de haut*/
.objBlocks .thumbPortrait .objIcon img											{width:100%; margin-top:-45%!important;}											/*images "fullsize" portrait : 100% de large + recentré*/
.objBlocks .hasThumb img														{margin-top:0px!important;}															/*vignettes : pas de margin-top pour les images*/
.objBlocks .hasThumb .objMenuContextFloat, .objBlocks .hasThumb .objMiscMenus	{filter:contrast(200%);}															/*vignettes : met en avant les icones du menu context*/
.objBlocks .objContainerSelect													{border:1px solid #f00;}															/*surcharge des fichiers sélectionnés*/
.objBlocks .objLabel															{position:absolute; bottom:0px; width:100%; padding:8px 4px; text-align:center;}	/*label "bandeau" d'un dossier/fichier (modFile)*/
.objBlocks .hasThumb .objLabel													{background:<?= Ctrl::$agora->skin=="black"?"black":"white" ?>; border-radius:3px;}	/*images/vignettes pdf : background des labels*/
.objLines .objLabel>span														{padding:10px 50px 10px 0px;}														/*Zone clickable élargie*/
.vVersionsMenu																	{margin-left:20px;}																	/*bouton "versions de fichiers" : cf. "versionsMenu()"*/
</style>


<div id="pageFull">
	<div id="pageMenu">
		<?= MdlFile::menuSelect() ?>
		<div class="miscContainer">
			<!--AJOUT D'ELEMENTS-->
			<?php if(Ctrl::$curContainer->addContentRight()){ ?>
				<div class="menuLine" onclick="lightboxOpen('<?= MdlFile::urlAddFiles() ?>')"><div class="menuIcon"><img src="app/img/plus.png"></div><div><?= Txt::trad("FILE_addFile") ?></div></div>
				<div class="menuLine" onclick="lightboxOpen('<?= MdlFileFolder::getUrlNew() ?>')"><div class="menuIcon"><img src="app/img/folder/folderAdd.png"></div><div><?= Txt::trad("addFolder") ?></div></div>
				<hr>
			<?php } ?>
			<!--ARBORESCENCE  &  MENU DU MODE D'AFFICHAGE  &  MENU DE TRI  &  DESCRIPTION DU CONTENU  &  ESPACE DISQUE-->
			<?= MdlFileFolder::menuTree().MdlFile::menuDisplayMode().MdlFile::menuSort() ?>
			<div class="menuLine"><div class="menuIcon"><img src="app/img/info.png"></div><div><?= Ctrl::$curContainer->contentDescription() ?></div></div>
			<?php if(!empty($diskSpaceBar)){ ?>
				<div class="menuLine"><div class="menuIcon"><img src="app/img/<?= $diskSpaceAlert==true?"diskSpaceAlert.png":"diskSpace.png" ?>"></div><div><?= $diskSpaceBar ?></div></div>
			<?php } ?>
		</div>
	</div>

	<div id="pageContent" class="<?= MdlFile::getDisplayMode()=="line"?"objLines":"objBlocks" ?>">
		<?php
		////	PATH DU DOSSIER COURANT & LISTE DES DOSSIERS
		echo MdlFolder::menuPath(Txt::trad("FILE_addFile"),MdlFile::urlAddFiles());
		echo CtrlObject::vueFolders();
		////	LISTE DES FICHIERS  ("iconTooltip" tjs dans le "<div>" && "iconLink" sur l'image pour pouvoir "select" le fichier)
		$fileNameLength=MdlFile::getDisplayMode()=="line" ? 80 : 50;
		foreach($filesList as $tmpFile){
			$hasThumbClass=$tmpFile->thumbExist() ? "hasThumb" : null;
			echo $tmpFile->objContainerMenu($hasThumbClass).
				'<div class="objContent objFiles '.$tmpFile->thumbClass.'">
					<div class="objIcon" '.Txt::tooltip($tmpFile->iconTooltip).'><img src="'.$tmpFile->typeIcon().'" '.$tmpFile->iconLink.'></div>
					<div class="objLabel"><span '.Txt::tooltip($tmpFile->labelTooltip).' '.$tmpFile->labelLink.'>'.Txt::reduce($tmpFile->name,$fileNameLength).$tmpFile->versionsMenu("icon").'</span></div>
					<div class="objDetails">'.File::sizeLabel($tmpFile->octetSize).'</div>
					<div class="objAutorDate">'.$tmpFile->autorDate().'</div>
				</div>
			</div>';
		}
		////	AUCUN CONTENU & AJOUTER
		if(empty(CtrlObject::vueFolders()) && empty($filesList)){
			$addElement=(Ctrl::$curContainer->addContentRight())  ?  '<div onclick="lightboxOpen(\''.MdlFile::urlAddFiles().'\')"><img src="app/img/plus.png"> '.Txt::trad("FILE_addFile").'</div>'  :  null;
			echo '<div class="emptyContainer">'.Txt::trad("FILE_noFile").$addElement.'</div>';
		}
		?>
	</div>
</div>