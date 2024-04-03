<?php
////	AFFICHE CHAQUE DOSSIERS DE LA VUE
foreach($foldersList as $tmpFolder)
{
	echo $tmpFolder->objContainer($objContainerClass).$tmpFolder->contextMenu();
	echo '<div class="objContent objFolders">
				<div class="objIcon"><img src="'.$tmpFolder->iconPath().'" onclick="redir(\''.$tmpFolder->getUrl().'\')" title="'.Txt::tooltip($tmpFolder->description).'"></div>
				<div class="objLabel" onclick="redir(\''.$tmpFolder->getUrl().'\')">'.Txt::reduce($tmpFolder->name,80).'</div>
				<div class="objDetails">'.$tmpFolder->folderOtherDetails().$tmpFolder->contentDescription().'</div>
				<div class="objAutorDate">'.$tmpFolder->autorDateLabel().'</div>
			</div>
		</div>';
}
?>