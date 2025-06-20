<div id="pageFull">
	<div id="moduleMenu">
		<?= MdlContact::menuSelect() ?>
		<div class="miscContainer">
			<?php
			////	MENU D'AJOUT D'ELEMENTS
			if(Ctrl::$curContainer->addContentRight()){
				echo "<div class='menuLine' onclick=\"lightboxOpen('".MdlContact::getUrlNew()."');\"><div class='menuIcon'><img src='app/img/plus.png'></div><div>".Txt::trad("CONTACT_addContact")."</div></div>
					  <div class='menuLine' onclick=\"lightboxOpen('".MdlContactFolder::getUrlNew()."')\"><div class='menuIcon'><img src='app/img/folder/folderAdd.png'></div><div>".Txt::trad("addFolder")."</div></div>";
				if(Ctrl::$curUser->isSpaceAdmin())	{echo "<div class='menuLine' onclick=\"lightboxOpen('?ctrl=contact&action=EditPersonsImportExport&typeId=".Ctrl::$curContainer->_typeId."');\"><div class='menuIcon'><img src='app/img/dataImportExport.png'></div><div>".Txt::trad("importExport_contact")."</div></div>";}
				echo "<hr>";
			}
			////	ARBORESCENCE  &  MENU DU MODE D'AFFICHAGE  &  MENU DE TRI  &  DESCRIPTION DU CONTENU
			echo MdlContactFolder::menuTree().MdlContact::menuDisplayMode().MdlContact::menuSort().
				"<div class='menuLine'><div class='menuIcon'><img src='app/img/info.png'></div><div>".Ctrl::$curContainer->contentDescription()."</div></div>";
			?>
		</div>
	</div>

	<div id="pageContent" class="<?= MdlContact::getDisplayMode()=="line"?"objLines":"objBlocks" ?>">
		<?php
		////	PATH DU DOSSIER COURANT  &  LISTE DES DOSSIERS  &  LISTE DES CONTACTS
		echo MdlFolder::menuPath(Txt::trad("CONTACT_addContact"),MdlContact::getUrlNew()).
			 CtrlObject::vueFolders();
		foreach($contactList as $tmpContact){
			echo $tmpContact->objContainerMenu("objPerson").
				'<div class="objContainerScroll">
					<div class="objContent">
						<div class="objIcon">'.$tmpContact->profileImg(true,false).'</div>
						<div class="objLabel" onclick="'.$tmpContact->openVue().'">
							<div class="objPersonDetail">'.$tmpContact->getLabel("full").'</div>
							<div class="objPersonDetails">'.$tmpContact->getFields(MdlContact::getDisplayMode()).'</div>
						</div>
						<div class="objAutorDate">'.$tmpContact->autorDate().'</div>
					</div>
				</div>
			</div>';
		}
		////	AUCUN CONTENU & AJOUTER
		if(empty(CtrlObject::vueFolders()) && empty($contactList)){
			$addElement=(Ctrl::$curContainer->addContentRight())  ?  "<div onclick=\"lightboxOpen('".MdlContact::getUrlNew()."')\"><img src='app/img/plus.png'> ".Txt::trad("CONTACT_addContact")."</div>"  :  null;
			echo '<div class="miscContainer emptyContainer">'.Txt::trad("CONTACT_noContact").$addElement.'</div>';
		}
		?>
	</div>
</div>