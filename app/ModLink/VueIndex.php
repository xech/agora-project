<style>
.objLabelBg					{background-image:url(app/img/link/iconBg.png);}
.linkIcon					{margin-right:5px;}
.linkAdress					{margin-top:5px; font-weight:normal; color:#888; word-break:break-all;}/*"break-all" évite que l'url dépasse du block'*/
</style>

<div class="pageFull">
	<div class="pageModMenuContainer">
		<div id="pageModMenu" class="miscContainer">
			<?php
			////	AJOUT D'ELEMENTS
			if(Ctrl::$curContainer->editContentRight()){
				echo "<div class='menuLine sLink' onclick=\"lightboxOpen('".MdlLink::getUrlNew()."');\"><div class='menuIcon'><img src='app/img/plus.png'></div><div>".Txt::trad("LINK_addLink")."</div></div>
					  <div class='menuLine sLink' onclick=\"lightboxOpen('".MdlLinkFolder::getUrlNew()."')\"><div class='menuIcon'><img src='app/img/folder/folderAdd.png'></div><div>".Txt::trad("addFolder")."</div></div>
					  <hr>";
			}
			////	ARBORESCENCE  &  MENU DE SELECTION/AFFICHAGE/TRI
			echo CtrlObject::folderTreeMenu().MdlLink::menuSelectObjects().MdlLink::menuDisplayMode().MdlLink::menuSort();
			?>
			<div class="menuLine"><div class="menuIcon"><img src="app/img/info.png"></div><div><?= Ctrl::$curContainer->folderContentDescription() ?></div></div>
		</div>
	</div>
	<div class="pageFullContent <?= (MdlLink::getDisplayMode()=="line"?"objLines":"objBlocks") ?>">
		<?php
		////	PATH DU DOSSIER COURANT & LISTE DES DOSSIERS
		echo CtrlObject::folderPathMenu(Txt::trad("LINK_addLink"),MdlLink::getUrlNew());
		echo $foldersList;
		////	LISTE DES LIENS
		foreach($linkList as $tmpLink)
		{
			$tmpDescription=!empty($tmpLink->description)  ?  "<span title=\"".$tmpLink->description."\">".Txt::reduce($tmpLink->description,100)."</span>"  :  null;
			echo $tmpLink->divContainer().$tmpLink->contextMenu().
				"<div class='objContent'>
					<div class='objLabel objLabelBg'>
						<a href=\"".$tmpLink->adress."\" target='_blank'>
							<img src=\"https://www.google.com/s2/favicons?domain=".$tmpLink->adress."\" class='linkIcon'>".$tmpDescription."
							<div class='linkAdress'>".Txt::reduce($tmpLink->adress,40)."</div>
						</a>
					</div>
					<div class='objAutorDate'>".$tmpLink->displayAutorDate()."</div>
				</div>
			</div>";
		}
		////	AUCUN CONTENU & AJOUTER
		if(empty($foldersList) && empty($linkList)){
			$addElement=(Ctrl::$curContainer->editContentRight())  ?  "<div class='sLink' onclick=\"lightboxOpen('".MdlLink::getUrlNew()."')\"><img src='app/img/plus.png'> ".Txt::trad("LINK_addLink")."</div>"  :  null;
			echo "<div class='emptyContainer'>".Txt::trad("LINK_noLink").$addElement."</div>";
		}
		?>
	</div>
</div>