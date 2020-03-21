<script>
lightboxSetWidth(450);//Resize
</script>

<div class="lightboxContent objVueBg">
	<?php
	////	MENU CONTEXTUEL/D'EDITION  &&  TITRE
	echo $curObj->menuContextEdit()."<div class='lightboxTitle'>".$curObj->getLabel("all")."</div>";
	
	////	IMAGE & DETAILS DU CONTACT
	echo "<div class='personLabelImg'>".$curObj->getImg()."</div>
		  <div class='personVueFields'>".$curObj->getFieldsValues("profile")."</div>".
		  $curObj->menuAttachedFiles();
	?>
</div>