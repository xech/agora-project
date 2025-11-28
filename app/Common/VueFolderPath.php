<div class="pathMenu miscContainer">
	<?php
	////	Icone du dossier parent (mobile)
	if(Req::isMobile() && $curFolder->isRootFolder()==false)
		{echo '<div class="pathMenuHome" onclick="redir(\''.$curFolder->containerObj()->getUrl().'\')"><img src="app/img/arrowTop.png"> Top</div>';}
	////	Chemin/path jusqu'au dossier courant
	$curFolderPath=$curFolder->folderPath("object");
	foreach($curFolderPath as $tmpKey=>$tmpFolder){
		if(Req::isMobile() && $tmpFolder->_id!=$curFolder->_id)  {continue;}											//Mobile : affiche uniquement le dossier courant
		$leftIcon=(empty($tmpFolder->_idContainer))  ?  "folder/folderSmall.png"  :  "arrowRight.png";					//Icone "Folder" (dossier racine)  OU  "arrowRight" (sous-dossier)
		$folderLink=($curFolder->_id!=$tmpFolder->_id)  ?  "onclick=\"redir('".$tmpFolder->getUrl()."')\""  :  null;	//Lien vers le dossier (sauf dossier courant)
		$contextMenu=($curFolder->isRootFolder()==false && Req::isMobile()==false && $curFolder->_id==$tmpFolder->_id)  ?  $tmpFolder->contextMenu(["launcherIcon"=>"inlineSmall"])  :  null;
		echo '<div '.$folderLink.' '.Txt::tooltip($tmpFolder->description).'>
				<img src="app/img/'.$leftIcon.'">'.Txt::reduce($tmpFolder->name,40).' '.$contextMenu.'
			  </div>';
	}
	////	Ajout d'élément / dossier
	if(!empty($addElemLabel) && $curFolder->addContentRight()){
	?>
		<div class="pathMenuAdd">
			<img src="app/img/plus.png" class="menuLauncher" for="folderPathAddMenu">
			<div id="folderPathAddMenu" class="menuContext">
				<div class="menuLine" onclick="lightboxOpen('<?= $addElemUrl ?>')"><div class="menuIcon"><img src="app/img/plus.png"></div><div><?= $addElemLabel ?></div></div>
				<div class="menuLine" onclick="lightboxOpen('<?= $curFolder::getUrlNew() ?>')"><div class="menuIcon"><img src="app/img/folder/folderAdd.png"></div><div><?= Txt::trad("addFolder") ?></div></div>
			</div>
		</div>
	<?php }	?>
</div>