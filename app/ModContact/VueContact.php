<script>
lightboxSetWidth(550);//Resize
</script>

<div class="lightboxContent">
	<?php
	////	MENU CONTEXTUEL/D'EDITION  &&  TITRE
	echo "<div class='lightboxTitle'>".$curObj->menuContextEdit().$curObj->getLabel("normal")."</div>";
	
	////	IMAGE & DETAILS DU CONTACT
	echo "<div class='personLabelImg'>".$curObj->getImg()."</div>
		  <div class='personVueFields'>".$curObj->getFieldsValues("profile")."</div>".
		  $curObj->attachedFileMenu();
	?>
</div>