<div class="pathMenu miscContainer">
	<?php
	$curFolder=Ctrl::$curContainer;
	////	Icone du lien "dossier parent" (Responsive)
	if(Req::isMobile() && $curFolder->isRootFolder()==false)  {echo "<div class='pathIconMenu' onclick=\"redir('".$curFolder->containerObj()->getUrl()."')\"><img src='app/img/arrowTop.png'> Top</div>";}
	////	Fil d'ariane du dossier courant
	$curFolderPath=$curFolder->folderPath("object");
	foreach($curFolderPath as $tmpKey=>$tmpFolder)
	{
		//Responsive : affiche uniquement le dossier courant
		if(Req::isMobile() && ($tmpKey+1)<count($curFolderPath))  {continue;}
		//Affiche le libellé du dossier
		$leftIcon=(empty($tmpFolder->_idContainer))  ?  "folder/folderSmall"  :  "arrowRightBig";//Icone "Folder" pour le dossier racine OU Icone "arrowRight"
		$folderLink=($curFolder->_id!=$tmpFolder->_id)  ?  "class='sLink' onclick=\"redir('".$tmpFolder->getUrl()."')\""  :  null;//Lien vers le dossier (sauf pour le dossier courant)
		$ContextMenu=(Req::isMobile()==false && $curFolder->_id==$tmpFolder->_id && $curFolder->isRootFolder()==false)  ?  "<div class='pathContextMenu'>".$tmpFolder->contextMenu(["iconBurger"=>"small"])."</div>"  :  null;//Menu contextuel du dossier courant (sauf responsive)
		echo "<div><img src='app/img/".$leftIcon.".png'></div>".$ContextMenu."<div ".$folderLink." title=\"".$tmpFolder->description."\">".Txt::reduce($tmpFolder->name,45)."</div>";/*plusieurs <div> : cf. responsive avec un "name" très long*/
	}
	////	Menu d'ajout d'élément
	if(!empty($addElemLabel) && $curFolder->addContentRight()){
		echo "<div class='pathIconMenu'>
			  	<img src='app/img/arrowRightBig2.png'> <img src='app/img/plus.png' class='menuLaunch' for='folderPathAddMenu'>
				<div id='folderPathAddMenu' class='menuContext'>
					<div class='menuLine sLink' onclick=\"lightboxOpen('".$addElemUrl."')\"><div class='menuIcon'><img src='app/img/plus.png'></div><div>".$addElemLabel."</div></div>
					<div class='menuLine sLink' onclick=\"lightboxOpen('".$curFolder::getUrlNew()."')\"><div class='menuIcon'><img src='app/img/folder/folderAdd.png'></div><div>".Txt::trad("addFolder")."</div></div>
				</div>
			  </div>";
	}
	?>
</div>