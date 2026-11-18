<style>
.objBlocks .objContainer	{height:150px;}/*surcharge*/

@media screen and (max-width:499px){
	.objBlocks .objContainerFolder	{height:80px;}/*surcharge*/
}
</style>


<div id="pageFull">
	<div id="pageMenu">
		<?= MdlContact::menuSelect() ?>
		<div class="miscContainer">
			<?php
			////	MENU D'AJOUT D'ELEMENTS
			if(Ctrl::$curContainer->addContentRight()){
				echo '<div class="menuLine forMobileAddElem" onclick="lightboxOpen(\''.MdlContact::getUrlNew().'\')"><div class="menuIcon"><img src="app/img/addBlue.svg"></div><div>'.Txt::trad("CONTACT_addContact").'</div></div>
					  <div class="menuLine" onclick="lightboxOpen(\''.MdlContactFolder::getUrlNew().'\')"><div class="menuIcon"><img src="app/img/addBlueFolder.svg"></div><div>'.Txt::trad("addFolder").'</div></div>';
				if(Ctrl::$curUser->isSpaceAdmin())	{echo '<div class="menuLine" onclick="lightboxOpen(\'?ctrl=contact&action=vueImportExport&typeId='.Ctrl::$curContainer->typeId.'\')"><div class="menuIcon"><img src="app/img/exchange.svg"></div><div>'.Txt::trad("importExport_contact").'</div></div>';}
				echo "<hr>";
			}
			////	ARBORESCENCE  &  MENU D'AFFICHAGE  &  MENU DE TRI  &  DESCRIPTION DU CONTENU
			echo MdlContactFolder::menuTree().MdlContact::menuDisplayMode().MdlContact::menuSort().
				'<div class="menuLine"><div class="menuIcon"><img src="app/img/info.svg"></div><div>'.Ctrl::$curContainer->contentDescription().'</div></div>';
			?>
		</div>
	</div>

	<div id="pageContent" class="<?= MdlContact::getDisplayMode()=="line"?"objLines":"objBlocks" ?>">
		<?php
		////	PATH DU DOSSIER COURANT  + LISTE DES DOSSIERS  + LISTE DES CONTACTS
		echo MdlFolder::menuPath(Txt::trad("CONTACT_addContact"),MdlContact::getUrlNew()).CtrlObject::vueFolders();
		foreach($contactList as $tmpContact){
			echo $tmpContact->objContainer();
		?>
				<div class="objContainerScroll">
					<div class="objContainerTab">
						<div class="objIcon"><?= $tmpContact->tagProfileImg(true,false) ?></div>
						<div class="objLabel" onclick="<?= $tmpContact->lightboxVue() ?>">
							<div class="personLabel"><?= $tmpContact->getLabel("full") ?></div>
							<?= $tmpContact->getFields("index") ?>
						</div>
						<div class="objAutorDate"><?= $tmpContact->autorDate(true) ?></div>
					</div>
				</div>
			</div>
		<?php } ?>

		<!--AUCUN CONTENU + AJOUTER-->
		<?php if(empty(CtrlObject::vueFolders()) && empty($contactList)){ ?>
			<div class="miscContainer emptyContent">
				<?= Txt::trad("CONTACT_noContact") ?>
				<?php if(Ctrl::$curContainer->addContentRight()){ ?><div onclick="lightboxOpen('<?= MdlContact::getUrlNew() ?>')"><img src="app/img/addBlue.svg"> <?= Txt::trad("CONTACT_addContact") ?></div><?php } ?>
			</div>
		<?php } ?>
	</div>
</div>