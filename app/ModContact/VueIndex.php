<script>
////	Création d'un user à partir d'un contact
function contactAddUser(targetObjId)
{
	if(confirm("<?= Txt::trad("CONTACT_createUserInfo") ?>"))
		{redir("?ctrl=contact&action=contactAddUser&targetObjId="+targetObjId);}
}
</script>

<div id="pageFull">
	<div id="pageModuleMenu">
		<div id="pageModMenu" class="miscContainer">
			<?php
			////	AJOUT D'ELEMENTS
			if(Ctrl::$curContainer->editContentRight()){
				echo "<div class='menuLine sLink' onclick=\"lightboxOpen('".MdlContact::getUrlNew()."');\"><div class='menuIcon'><img src='app/img/plus.png'></div><div>".Txt::trad("CONTACT_addContact")."</div></div>
					  <div class='menuLine sLink' onclick=\"lightboxOpen('".MdlContactFolder::getUrlNew()."')\"><div class='menuIcon'><img src='app/img/folder/folderAdd.png'></div><div>".Txt::trad("addFolder")."</div></div>";
				if(Ctrl::$curUser->isAdminSpace())	{echo "<div class='menuLine sLink' onclick=\"lightboxOpen('?ctrl=contact&action=EditPersonsImportExport&targetObjId=".Ctrl::$curContainer->_targetObjId."');\"><div class='menuIcon'><img src='app/img/dataImportExport.png'></div><div>".Txt::trad("import")."/".Txt::trad("export")." ".Txt::trad("importExport_contact")."</div></div>";}
				echo "<hr>";
			}
			////	ARBORESCENCE  &  MENU DE SELECTION/AFFICHAGE/TRI
			echo CtrlObject::folderTreeMenu().MdlContact::menuSelectObjects().MdlContact::menuDisplayMode().MdlContact::menuSort();
			?>
			<div class="menuLine"><div class="menuIcon"><img src="app/img/info.png"></div><div><?= Ctrl::$curContainer->folderContentDescription() ?></div></div>
		</div>
	</div>
	<div id="pageFullContent" class="<?= MdlContact::getDisplayMode()=="line"?"objLines":"objBlocks" ?>">
		<?php
		////	PATH DU DOSSIER COURANT & LISTE DES DOSSIERS
		echo CtrlObject::folderPathMenu(Txt::trad("CONTACT_addContact"),MdlContact::getUrlNew());
		echo $foldersList;
		////	LISTE DES CONTACTS
		foreach($contactList as $tmpContact)
		{
			echo $tmpContact->divContainer("objPerson").$tmpContact->contextMenu().
				"<div class='objContentScroll'>
					<div class='objContent'>
						<div class='objIcon'>".$tmpContact->getImg(true,false,true)."</div>
						<div class='objLabel'>
							<a href=\"javascript:lightboxOpen('".$tmpContact->getUrl("vue")."');\">".$tmpContact->getLabel("all")."</a>
							<div class='objPersonDetails'>".$tmpContact->getFieldsValues(MdlContact::getDisplayMode())."</div>
						</div>
						<div class='objAutorDate'>".$tmpContact->displayAutorDate()."</div>
					</div>
				</div>
			</div>";
		}
		////	AUCUN CONTENU & AJOUTER
		if(empty($foldersList) && empty($contactList)){
			$addElement=(Ctrl::$curContainer->editContentRight())  ?  "<div class='sLink' onclick=\"lightboxOpen('".MdlContact::getUrlNew()."')\"><img src='app/img/plus.png'> ".Txt::trad("CONTACT_addContact")."</div>"  :  null;
			echo "<div class='emptyContainer'>".Txt::trad("CONTACT_noContact").$addElement."</div>";
		}
		?>
	</div>
</div>