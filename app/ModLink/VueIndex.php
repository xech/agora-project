<style>
/*LABEL ET DETAILS DES LIENS*/
.objLabel		{line-height:20px; word-break:break-all;}/*"break-all" évite que l'url dépasse du block'*/
.objLabel img	{margin-right:10px;}
</style>

<div id="pageFull">
	<div id="pageMenu">
		<?= MdlLink::menuSelect() ?>
		<div class="miscContent">
			<!--MENU D'AJOUT D'ELEMENTS-->
			<?php if(Ctrl::$curContainer->addContentRight()){ ?>
				<div class="menuLine forMobileAddElem" onclick="lightboxOpen('<?= MdlLink::getUrlNew() ?>')"><div class="menuIcon"><img src="app/img/plus.png"></div><div><?= Txt::trad("LINK_addLink") ?></div></div>
				<div class="menuLine" onclick="lightboxOpen('<?= MdlLinkFolder::getUrlNew() ?>')"><div class="menuIcon"><img src="app/img/plusAddFolder.png"></div><div><?= Txt::trad("addFolder") ?></div></div>
				<hr>
			<?php } ?>
			<!--ARBORESCENCE  &  MENU D'AFFICHAGE  &  MENU DE TRI  &  DESCRIPTION DU CONTENU-->
			<?= MdlLinkFolder::menuTree().MdlLink::menuDisplayMode().MdlLink::menuSort() ?>
			<div class="menuLine"><div class="menuIcon"><img src="app/img/info.png"></div><div><?= Ctrl::$curContainer->contentDescription() ?></div></div>
		</div>
	</div>

	<div id="pageContent" class="<?= MdlLink::getDisplayMode()=="line"?"objLines":"objBlocks" ?>">

		<?php
		////	PATH DU DOSSIER COURANT  + LISTE DES DOSSIERS  + LISTE DES LIENS
		echo MdlFolder::menuPath(Txt::trad("LINK_addLink"),MdlLink::getUrlNew()).CtrlObject::vueFolders();
		foreach($linkList as $tmpLink){
			$adressURL=htmlspecialchars($tmpLink->adress, ENT_QUOTES,'UTF-8');	//Encode les caractères spéciaux et échappe les guillemets simples/doubles
			if(Req::isMobileApp())  {$adressURL.="#fromMobileApp";}				//App mobile
			$adressLabel=(!empty($tmpLink->description))  ?  '<span '.Txt::tooltip($adressURL).'>'.$tmpLink->description.'</span>'  :  Txt::reduce($adressURL);//Ajoute la description
			echo $tmpLink->objContentDiv();
		?>
				<div class="objContentScroll">
					<div class="objContentTab">
						<div class="objIcon objIconOpacity"><img src="app/img/link/iconSmall.png"></div>
						<div class="objLabel"><a href="<?= $adressURL ?>" target="_blank"><img src="https://www.google.com/s2/favicons?domain=<?= $tmpLink->adress ?>"><?= $adressLabel ?></a></div>
						<div class="objAutorDate"><?= $tmpLink->autorDate(true) ?></div>
					</div>
				</div>
			</div>
		<?php  } ?>

		<!--AUCUN CONTENU + AJOUTER-->
		<?php if(empty(CtrlObject::vueFolders()) && empty($linkList)){ ?>
			<div class="miscContent emptyContent">
				<?= Txt::trad("LINK_noLink") ?>
				<?php if(Ctrl::$curContainer->addContentRight()){ ?><div onclick="lightboxOpen('<?= MdlLink::getUrlNew() ?>')"><img src="app/img/plus.png"> <?= Txt::trad("LINK_addLink") ?></div><?php } ?>
			</div>
		<?php } ?>
	
	</div>
</div>