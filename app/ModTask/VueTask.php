<script>
////	Resize
lightboxSetWidth(700);
</script>

<style>
.priorityLabel, .categoryLabel	{display:inline-block; margin-left:20px; line-height:40px;}	/*Label de la priorité et du statut (surcharges)*/
.vTaskDetails					{text-align:center; margin:20px 0px;}						/*Ligne des détails*/
</style>

<div>
	<?php
	////	MENU CONTEXTUEL/D'EDITION  &&  TITRE
	echo "<div class='lightboxTitle'>".$curObj->lightboxMenu().$curObj->title."<br>".$curObj->categoryLabel().$curObj->priorityLabel()."</div>";

	////	DESCRIPTION  +  PERS. RESPONSABLES / AVANCEMENT / DATES DEBUT & FIN + FICHIERS JOINTS
	echo $curObj->description.
		 "<div class='vTaskDetails'>".$curObj->responsiblePersons(true).$curObj->advancement(true).$curObj->dateBeginEnd(true)."</div>".
		$curObj->attachedFileMenu();
	?>
</div>