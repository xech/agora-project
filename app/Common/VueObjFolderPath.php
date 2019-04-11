<div class="pathMenu miscContainer">
	<?php
	$curFolder=Ctrl::$curContainer;
	//Responsive : affiche le "dossier parent"
	if(Req::isMobile() && $curFolder->isRootFolder()==false)  {echo "<div style='width:50px;' onclick=\"redir('".$curFolder->containerObj()->getUrl()."')\"><img src='app/img/arrowTop.png'> Top</div>";}
	//Chemin du dossier courant
	$curFolderPath=$curFolder->folderPath("object");
	foreach($curFolderPath as $tmpKey=>$tmpFolder){
		if(Req::isMobile() && ($tmpKey+1)<count($curFolderPath))  {continue;}//Responsive : affiche uniquement le dossier courant
		$leftIcon=(empty($tmpFolder->_idContainer))  ?  "folder/folderSmall"  :  "arrowRightBig";//Icone "Folder" || Icone "arrowRight"
		$folderOnclick=($curFolder->_id!=$tmpFolder->_id)  ?  "class='sLink' onclick=\"redir('".$tmpFolder->getUrl()."')\""  :  null;
		echo "<div><img src='app/img/".$leftIcon.".png'></div><div ".$folderOnclick." title=\"".$tmpFolder->description."\"> ".Txt::reduce($tmpFolder->name,50)."</div>";
	}
	////	Ajout d'élément
	if(!empty($addElemLabel) && $curFolder->editContentRight()){
		echo "<div style='width:60px;'>
				<img src='app/img/arrowRightAdd.png'> <img src='app/img/plus.png' class='menuLaunch' for='folderPathAddMenu'>
				<div id='folderPathAddMenu' class='menuContext'>
					<div class='menuLine sLink' onclick=\"lightboxOpen('".$addElemUrl."')\"><div class='menuIcon'><img src='app/img/plus.png'></div><div>".$addElemLabel."</div></div>
					<div class='menuLine sLink' onclick=\"lightboxOpen('".$curFolder::getUrlNew()."')\"><div class='menuIcon'><img src='app/img/folder/folderAdd.png'></div><div>".Txt::trad("addFolder")."</div></div>
				</div>
			  </div>";
	}
	?>
</div>
<br>