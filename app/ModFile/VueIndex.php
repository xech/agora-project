<meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=2.0">

<style>
.objBlocks .objContainer				{height:130px; width:150px; min-width:150px; max-width:250px;}/*surcharge*/
.objBlocks .hasThumb .objMiscMenuDiv	{filter:contrast(150%);}/*met en avant le menu context*/
.objBlocks .hasThumb .objIcon img		{margin-top:0%;}/*thumb*/
.objBlocks .thumbLandscape .objIcon img	{height:100%; width:100%; border-radius:4px;}/*thumb en affichage "full"*/
.objBlocks .pdfIcon						{position:absolute; top:0px; right:0px;}
.objBlocks .objLabel					{line-height:13px;}/*surcharge : tester avec des noms sur 2 lignes (min & maj)*/
.hasThumb [data-fancybox='images']		{cursor:url("app/img/search.png"),all-scroll!important;}
.vVersionsMenu							{margin-left:5px;}
</style>

<div class="pageFull">
	<div class="pageModMenuContainer">
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
	<div class="pageFullContent <?= (MdlFile::getDisplayMode()=="line"?"objLines":"objBlocks") ?>">
		<?php
		////	PATH DU DOSSIER COURANT & LISTE DES DOSSIERS
		echo CtrlObject::folderPathMenu(Txt::trad("FILE_addFile"),MdlFile::urlAddFiles());
		echo $foldersList;
		////	LISTE DES FICHIERS
		foreach($filesList as $tmpFile)
		{
			echo $tmpFile->divContainer("objContentCenter ".$tmpFile->hasThumbClass).$tmpFile->contextMenu().
				"<div class=\"objContent ".$tmpFile->thumbClass."\">
					<div class='objIcon'><a ".$tmpFile->iconHref." title=\"".$tmpFile->iconTooltip."\"><img src=\"".$tmpFile->typeIcon()."\"></a></div>
					<div class='objLabel'><a ".$tmpFile->downloadHref." title=\"".$tmpFile->tooltip."\">".Txt::reduce($tmpFile->name,50)."</a>".$tmpFile->versionsMenu("icon")."</div>
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