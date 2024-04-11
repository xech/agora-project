<script>
////	Resize
lightboxSetWidth(700);
</script>

<style>
.vStatusPriorityLabel	{display:inline-block; margin-left:20px; line-height:40px;}	/*Label du statut kanban et de la priorité*/
.vTaskDetails			{text-align:center; margin:20px 0px;}						/*Ligne des détails*/
</style>

<div>
	<?php
	////	MENU CONTEXTUEL/D'EDITION  &&  TITRE
	echo "<div class='lightboxTitle'>".$curObj->inlineContextMenu().$curObj->title."<br>".$curObj->statusLabel().$curObj->priorityLabel()."</div>";

	////	DESCRIPTION  +  PERS. RESPONSABLES / AVANCEMENT / DATES DEBUT & FIN + FICHIERS JOINTS
	echo $curObj->description.
		 "<div class='vTaskDetails'>".$curObj->responsiblePersons(true).$curObj->advancement(true).$curObj->dateBeginEnd(true)."</div>".
		$curObj->attachedFileMenu();
	?>
</div>