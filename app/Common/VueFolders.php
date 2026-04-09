<?php
////	AFFICHE CHAQUE DOSSIERS DE LA VUE
foreach($foldersList as $tmpFolder)
{
	echo $tmpFolder->divContainerMenu($containerClass).
			'<div class="objContentTab objFolders">
				<div class="objIcon"><img src="'.$tmpFolder->iconPath().'" onclick="redir(\''.$tmpFolder->getUrl().'\')" '.Txt::tooltip($tmpFolder->description).'></div>
				<div class="objLabel" onclick="redir(\''.$tmpFolder->getUrl().'\')">'.Txt::reduce($tmpFolder->name,80).'</div>
				<div class="objDetails">
					<div>'.$tmpFolder->contentDescription().'</div>
					<div>'.$tmpFolder->folderDetails().'</div>
				</div>
				<div class="objAutorDate">'.$tmpFolder->autorDate(true).'</div>
			</div>
		</div>';
}
?>