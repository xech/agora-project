<?php
////	AFFICHE CHAQUE DOSSIERS DE LA VUE
foreach($foldersList as $tmpFolder)
{
	echo $tmpFolder->objContainerMenu($containerClass).
			'<div class="objContent objFolders">
				<div class="objIcon"><img src="'.$tmpFolder->iconPath().'" onclick="redir(\''.$tmpFolder->getUrl().'\')" '.Txt::tooltip($tmpFolder->description).'></div>
				<div class="objLabel" onclick="redir(\''.$tmpFolder->getUrl().'\')">'.Txt::reduce($tmpFolder->name,80).'</div>
				<div class="objDetails">
					<div>'.$tmpFolder->contentDescription().'</div>
					<div>'.$tmpFolder->folderDetails().'</div>
				</div>
				<div class="objAutorDate">'.$tmpFolder->autorDate().'</div>
			</div>
		</div>';
}
?>