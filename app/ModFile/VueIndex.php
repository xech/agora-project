<style>
.objBlocks .objContent										{height:150px; min-width:150px; max-width:250px;}									/*surcharge*/
.objBlocks .objIcon												{position:absolute; overflow:hidden; width:100%!important; height:100%!important; border-radius:7px;}/*centre l'icone (dossier, types de fichier, img "fullsize")*/
.objBlocks .objIcon img											{margin-top:15px;}																	/*idem ...sauf image "fullsize"*/
.objBlocks .thumbLandscape img, .objBlocks .thumbPortrait img	{min-width:100%; max-width:none; min-height:100%; max-height:none;}					/*images "fullsize" : couvrent tout le Block*/
.objBlocks .thumbLandscape img									{height:100%;}																		/*images "fullsize" paysage : 100% de haut*/
.objBlocks .thumbPortrait img									{width:100%; margin-top:-45%!important;}											/*images "fullsize" portrait : 100% de large + recentré*/
.objBlocks .hasThumb img										{margin-top:0px!important;}															/*vignettes : pas de margin-top pour les images*/
.objBlocks .hasThumb .menuContextLaunchFloat					{filter:contrast(200%);}															/*vignettes : surligne l'icone burger*/
.objBlocks .objContentSelect									{border:2px solid rgba(255, 139, 85, 1);}											/*surcharge la sélection de fichiers avec vignette*/
.objBlocks .objLabel											{position:absolute; bottom:0px; width:100%; padding:8px 4px; text-align:center;}	/*label "bandeau" d'un dossier/fichier (modFile)*/
.objBlocks .objFiles .objLabel a								{font-size:0.95rem; cursor:url('app/img/download.png'),default;}					/*nom des fichiers*/
.objBlocks .hasThumb .objLabel									{background:<?= Ctrl::$agora->skin ?>; border-radius:0px 0px 5px 5px;}				/*images/vignettes pdf : background des labels*/
.objLines .objLabel>span										{padding:10px 50px 10px 0px;}														/*Zone clickable élargie*/
.objLabel .versionsMenu											{margin-left:10px;}																	/*icone "versionsMenu()"*/
</style>


<div id="pageFull">
	<div id="pageMenu">
		<?= MdlFile::menuSelect() ?>
		<div class="miscContent">
			<!--AJOUT D'ELEMENTS-->
			<?php if(Ctrl::$curContainer->addContentRight()){ ?>
				<div class="menuLine forMobileAddElem" onclick="lightboxOpen('<?= MdlFile::urlAddFiles() ?>')"><div class="menuIcon"><img src="app/img/plus.png"></div><div><?= Txt::trad("FILE_addFile") ?></div></div>
				<div class="menuLine" onclick="lightboxOpen('<?= MdlFileFolder::getUrlNew() ?>')"><div class="menuIcon"><img src="app/img/plusAddFolder.png"></div><div><?= Txt::trad("addFolder") ?></div></div>
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

		<!--PATH DU DOSSIER COURANT & LISTE DES DOSSIERS-->
		<?= MdlFolder::menuPath(Txt::trad("FILE_addFile"),MdlFile::urlAddFiles()).CtrlObject::vueFolders() ?>

		<!--LISTE DES FICHIERS-->
		<?php
		foreach($filesList as $tmpFile){
			$containerClass=$tmpFile->hasTumb() ? "hasThumb" : null;
			echo $tmpFile->mainDivMenu($containerClass);
		?>
				<div class="objContentTab objFiles">
					<div class="objIcon <?= $tmpFile->iconClass ?>" <?= Txt::tooltip($tmpFile->iconTooltip) ?>><img src="<?= $tmpFile->typeIcon() ?>" <?= $tmpFile->iconLink ?> class="typeIdTarget"></div>
					<div class="objLabel" <?= Txt::tooltip($tmpFile->labelTooltip) ?>><a <?= $tmpFile->labelLink ?> ><?= Txt::reduce($tmpFile->name,$nameLength).$tmpFile->versionsMenu("icon") ?></a></div>
					<div class="objDetails"><?= File::sizeLabel($tmpFile->octetSize) ?></div>
					<div class="objAutorDate"><?= $tmpFile->autorDate(true) ?></div>
				</div>
			</div>
		<?php } ?>

		<!--AUCUN CONTENU & AJOUTER-->
		<?php if(empty(CtrlObject::vueFolders()) && empty($filesList)){ ?>
			<div class="miscContent emptyContent">
				<?= Txt::trad("FILE_noFile") ?>
				<?php if(Ctrl::$curContainer->addContentRight()){ ?><div onclick="lightboxOpen('<?= MdlFile::urlAddFiles() ?>')"><img src="app/img/plus.png"> <?= Txt::trad("FILE_addFile") ?></div><?php } ?>
			</div>
		<?php } ?>
		
	</div>
</div>