<div class="pathMenu miscContainer">
	<?php
	$curFolder=Ctrl::$curContainer;
	////	Icone du lien "dossier parent" (mobile)
	if(Req::isMobile() && $curFolder->isRootFolder()==false)  {echo '<div class="pathMenuHome" onclick="redir(\''.$curFolder->containerObj()->getUrl().'\')"><img src="app/img/arrowTop.png"> Top</div>';}
	////	Fil d'ariane du dossier courant
	$curFolderPath=$curFolder->folderPath("object");
	foreach($curFolderPath as $tmpKey=>$tmpFolder)
	{
		//mobile : affiche uniquement le dossier courant
		if(Req::isMobile() && ($tmpKey+1)<count($curFolderPath))  {continue;}
		//Affiche le libellé du dossier
		$leftIcon=(empty($tmpFolder->_idContainer))  ?  "folder/folderSmall"  :  "arrowRightBig";						//Icone "Folder" pour le dossier racine OU Icone "arrowRight"
		$folderLink=($curFolder->_id!=$tmpFolder->_id)  ?  "onclick=\"redir('".$tmpFolder->getUrl()."')\""  :  null;	//Lien vers le dossier (sauf pour le dossier courant)
		$ContextMenu=(Req::isMobile()==false && $curFolder->_id==$tmpFolder->_id && $curFolder->isRootFolder()==false)  ?  $tmpFolder->contextMenu(["iconBurger"=>"inlineSmall"])  :  null;	//Menu contextuel du dossier courant (sauf sur mobile)
		echo '<div><img src="app/img/'.$leftIcon.'.png"></div><div '.$folderLink.' title="'.Txt::tooltip($tmpFolder->description).'">'.Txt::reduce($tmpFolder->name,40).' '.$ContextMenu.'</div>';//Tester sur mobile avec un "name" très long
	}
	////	Menu "+" d'ajout d'élément
	if(!empty($addElemLabel) && $curFolder->addContentRight()){
	?>
		<div class="pathMenuAdd">
			  	<img src="app/img/arrowRightBig.png">&nbsp;<img src="app/img/plusBig.png" class="menuLaunch" for="folderPathAddMenu">
				<div id="folderPathAddMenu" class="menuContext">
					<div class="menuLine" onclick="lightboxOpen('<?= $addElemUrl ?>')"><div class="menuIcon"><img src="app/img/plusBig.png"></div><div><?= $addElemLabel ?></div></div>
					<div class="menuLine" onclick="lightboxOpen('<?= $curFolder::getUrlNew() ?>')"><div class="menuIcon"><img src="app/img/folder/folderAdd.png"></div><div><?= Txt::trad("addFolder") ?></div></div>
				</div>
			  </div>
	<?php }	?>
</div>