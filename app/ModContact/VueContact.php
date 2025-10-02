<div>
	<?php
	////	MENU CONTEXTUEL/D'EDITION  &&  TITRE
	echo "<div class='lightboxTitle'>".$curObj->lightboxMenu().$curObj->getLabel("full")."</div>";
	
	////	IMAGE & DETAILS DU CONTACT
	echo "<div class='personProfileImg'>".$curObj->profileImg()."</div>
		  <div class='personVueFields'>".$curObj->getFields("profile")."</div>".
		  $curObj->attachedFileMenu();
	?>
</div>