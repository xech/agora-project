<meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=2.0">

<style>
/*basic*/
.objIcon span, .objFileLink		{cursor:url("app/img/download.png"),pointer!important;}	/*icone "Download" sur l'icone et le nom du fichier*/
.objBlocks .objLabelLink		{padding:0px 10px;}										/*surchage le "labelLink" pour recentrer le nom du fichier*/
.hasThumb .objIcon span			{cursor:url("app/img/search.png"),pointer!important;}	/*icone "display/search" sur les vignettes d'images ou pdf*/
.vVersionsMenu					{margin-left:10px;}										/*bouton "versions de fichiers" : cf. "versionsMenu()"*/
.objBlocks .vIconDownload		{float:right; height:14px; filter:grayscale(1);}		/*icone de download*/
.objLines .vIconDownload		{display:none;}

/*Affichage Block*/
.objBlocks .objContainer														{height:150px; width:150px; min-width:150px; max-width:250px;}				/*surcharge: taille des "objBlocks"*/
.objBlocks .hasThumb img														{margin-top:0px!important;}													/*images/vignettes pdf : pas de margin-top pour les images*/
.objBlocks .hasThumb .objMenuBurger, .objBlocks .hasThumb .objMiscMenus			{filter:contrast(200%);}													/*images/vignettes pdf : met en avant les icones du menu context*/
.objBlocks .hasThumb .objLabel													{background:<?= Ctrl::$agora->skin=="black"?"#222":"#fff" ?>; padding:7px;}	/*images/vignettes pdf : background des labels*/
.objBlocks .thumbLandscape .objIcon img, .objBlocks .thumbPortrait .objIcon img	{min-width:100%; max-width:none; min-height:100%; max-height:none;}			/*les images couvrent tout le Block*/
.objBlocks .thumbLandscape .objIcon img											{height:100%;}																/*image paysage: 100% de haut*/
.objBlocks .thumbPortrait .objIcon img											{width:100%; margin-top:-45%!important;}									/*image portrait: 100% de large + recentré*/
</style>

<div id="pageFull">
	<div id="pageModuleMenu">
		<div id="pageModMenu" class="miscContainer">
			<?php
			////	MENU DE SELECTION MULTIPLE  &&  MENU D'AJOUT D'ELEMENTS
			echo MdlFile::menuSelectObjects();
			if(Ctrl::$curContainer->addContentRight()){
				echo "<div class='menuLine sLink' onclick=\"lightboxOpen('".MdlFile::urlAddFiles()."')\"><div class='menuIcon'><img src='app/img/plus.png'></div><div>".Txt::trad("FILE_addFile")."</div></div>
					  <div class='menuLine sLink' onclick=\"lightboxOpen('".MdlFileFolder::getUrlNew()."')\"><div class='menuIcon'><img src='app/img/folder/folderAdd.png'></div><div>".Txt::trad("addFolder")."</div></div>
					  <hr>";
			}
			////	ARBORESCENCE  &  MENU D'AFFICHAGE  &  MENU DE TRI  &  DESCRIPTION DU CONTENU  &  ESPACE DISQUE
			echo CtrlObject::folderTreeMenu().MdlFile::menuDisplayMode().MdlFile::menuSort();
			echo "<div class='menuLine'><div class='menuIcon'><img src='app/img/info.png'></div><div>".Ctrl::$curContainer->folderContentDescription()."</div></div>";
			if(!empty($fillRateBar))  {echo "<div class='menuLine'><div class='menuIcon'><img src='app/img/diskSpace".($diskSpaceAlert==true?"Alert":null).".png'></div><div>".$fillRateBar."</div></div>";}
			?>
		</div>
	</div>
	<div id="pageFullContent" class="<?= MdlFile::getDisplayMode()=="line"?"objLines":"objBlocks" ?>">
		<?php
		////	PATH DU DOSSIER COURANT & LISTE DES DOSSIERS
		echo CtrlObject::folderPathMenu(Txt::trad("FILE_addFile"),MdlFile::urlAddFiles());
		echo CtrlObject::vueFolders();
		////	LISTE DES FICHIERS
		foreach($filesList as $tmpFile)
		{
			echo $tmpFile->divContainer("objContentCenter ".$tmpFile->hasThumbClass).$tmpFile->contextMenu().
				"<div class=\"objContent ".$tmpFile->thumbClass."\">
					<div class='objIcon'><span ".$tmpFile->iconLink." class='stopPropagation' title=\"".Txt::tooltip($tmpFile->iconTooltip)."\"><img src=\"".$tmpFile->typeIcon()."\"></span></div>
					<div class='objLabel'><span ".$tmpFile->labelLink." class='stopPropagation objLabelLink objFileLink' title=\"".Txt::tooltip($tmpFile->tooltip)."\"><img src='app/img/download.png' class='vIconDownload'>".Txt::reduce($tmpFile->name,50)."</span>".$tmpFile->versionsMenu("icon")."</div>
					<div class='objDetails'>".File::displaySize($tmpFile->octetSize)."</div>
					<div class='objAutorDate'>".$tmpFile->autorDateLabel()."</div>
				</div>
			</div>";
		}
		////	AUCUN CONTENU & AJOUTER
		if(empty(CtrlObject::vueFolders()) && empty($filesList)){
			$addElement=(Ctrl::$curContainer->addContentRight())  ?  "<div class='sLink' onclick=\"lightboxOpen('".MdlFile::urlAddFiles()."')\"><img src='app/img/plus.png'> ".Txt::trad("FILE_addFile")."</div>"  :  null;
			echo "<div class='emptyContainer'>".Txt::trad("FILE_noFile").$addElement."</div>";
		}
		?>
	</div>
</div>