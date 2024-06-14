<style>
/*LABEL ET DETAILS DES LIENS*/
.objLabel		{line-height:20px;}
.objLabelUrl	{word-break:break-all;}/*"break-all" évite que l'url dépasse du block'*/
.objLabel img	{margin-right:10px;}
</style>

<div id="pageFull">
	<div id="pageModuleMenu">
		<?= MdlLink::menuSelect() ?>
		<div id="pageModMenu" class="miscContainer">
			<?php
			////	MENU D'AJOUT D'ELEMENTS
			if(Ctrl::$curContainer->addContentRight()){
				echo "<div class='menuLine' onclick=\"lightboxOpen('".MdlLink::getUrlNew()."');\"><div class='menuIcon'><img src='app/img/plus.png'></div><div>".Txt::trad("LINK_addLink")."</div></div>
					  <div class='menuLine' onclick=\"lightboxOpen('".MdlLinkFolder::getUrlNew()."')\"><div class='menuIcon'><img src='app/img/folder/folderAdd.png'></div><div>".Txt::trad("addFolder")."</div></div>
					  <hr>";
			}
			////	ARBORESCENCE  &  MENU DU MODE D'AFFICHAGE  &  MENU DE TRI  &  DESCRIPTION DU CONTENU
			echo MdlLinkFolder::menuTree().MdlLink::menuDisplayMode().MdlLink::menuSort().
				"<div class='menuLine'><div class='menuIcon'><img src='app/img/info.png'></div><div>".Ctrl::$curContainer->contentDescription()."</div></div>";
			?>
		</div>
	</div>
	<div id="pageFullContent" class="<?= MdlLink::getDisplayMode()=="line"?"objLines":"objBlocks" ?>">
		<?php
		////	PATH DU DOSSIER COURANT & LISTE DES DOSSIERS
		echo MdlFolder::menuPath(Txt::trad("LINK_addLink"),MdlLink::getUrlNew());
		echo CtrlObject::vueFolders();
		////	LISTE DES LIENS
		foreach($linkList as $tmpLink)
		{
			$linkLabel=(!empty($tmpLink->description))  ?  '<span title="'.Txt::tooltip($tmpLink->adress).'">'.$tmpLink->description.'</span>'  :  '<span class="objLabelUrl">'.Txt::reduce($tmpLink->adress).'</span>';
			echo $tmpLink->divContainerContextMenu().
				"<div class='objContainerScroll'>
					<div class='objContent'>
						<div class='objIcon objIconOpacity'><img src='app/img/link/iconOpacity.png'></div>
						<div class='objLabel' onclick=\"window.open('".$tmpLink->adress."')\"><img src=\"https://www.google.com/s2/favicons?domain=".$tmpLink->adress."\">".$linkLabel."</div>
						<div class='objAutorDate'>".$tmpLink->autorDateLabel()."</div>
					</div>
				</div>
			</div>";
		}
		////	AUCUN CONTENU & AJOUTER
		if(empty(CtrlObject::vueFolders()) && empty($linkList)){
			$addElement=(Ctrl::$curContainer->addContentRight())  ?  "<div onclick=\"lightboxOpen('".MdlLink::getUrlNew()."')\"><img src='app/img/plus.png'> ".Txt::trad("LINK_addLink")."</div>"  :  null;
			echo "<div class='emptyContainer'>".Txt::trad("LINK_noLink").$addElement."</div>";
		}
		?>
	</div>
</div>