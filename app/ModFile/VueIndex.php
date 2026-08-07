<style>
.objBlocks .objContent							{height:150px; min-width:150px; max-width:240px;}					/*surcharge*/
.objBlocks .objIcon								{position:absolute; overflow:hidden; width:100%!important; height:100%!important; border-radius:var(--radius-block);}/*centre l'icone*/
.objBlocks .objContent:not(.hasThumb) .objIcon	{padding-top:20px;}													/*icone de dossier/fichier*/
.objBlocks .objIcon img:is(.thumbLandscape,.thumbPortrait)	{min-width:100%!important; min-height:100%!important; max-width:200%!important; max-height:200%!important;}/*surcharge ".objIcon img" : full zize*/
.objBlocks .objIcon img.thumbLandscape			{height:100%;}														/*image landscape  : 100% de haut + recentré à l'horizontale*/
.objBlocks .objIcon img.thumbPortrait			{width:100%; margin-top:-30%;}										/*images portrait : 100% de large + recentré à la verticale*/
.objBlocks .hasThumb .menuContextLaunchFloat	{filter:contrast(200%);}											/*vignettes : surligne l'icone burger*/
.objBlocks .objContentSelected .objLabel		{background-color:var(--blue-bg)!important;}						/*surcharge la sélection de fichiers avec vignette*/
.objBlocks .objContentSelected .objLabel a		{color:white!important;}											/*idem pour les liens*/
.objBlocks .objLabel							{position:absolute; background-color:<?= Ctrl::$agora->skin ?>; bottom:0px; width:100%; padding:8px 4px; text-align:center; border-radius:0px 0px var(--radius-block) var(--radius-block);}	/*label "bandeau" d'un dossier/fichier (modFile)*/
.objBlocks .objFiles .objLabel a				{font-size:0.95rem; cursor:url('app/img/download.png'),default;}	/*nom des fichiers*/
.objLines .objLabel>span						{padding:10px 50px 10px 0px;}										/*Zone clickable élargie*/
.objLabel .versionsMenu							{margin-left:10px;}													/*icone "versionsMenu()"*/

/*** RESPONSIVE SMARTPHONE*/
@media screen and (max-width:499px){
	.objBlocks .objContent	{float:left; margin:5px;}/*surcharge*/
}
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
			<!--ARBORESCENCE  &  MENU D'AFFICHAGE  &  MENU DE TRI  &  DESCRIPTION DU CONTENU  &  ESPACE DISQUE-->
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
			echo $tmpFile->objContentDiv($containerClass);
		?>
				<div class="objContentTab objFiles">
					<div class="objIcon" <?= Txt::tooltip($tmpFile->iconTooltip) ?>><img src="<?= $tmpFile->typeIcon() ?>" <?= $tmpFile->iconLink ?> class="typeIdTargetClick <?= $tmpFile->iconClass ?>"></div>
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