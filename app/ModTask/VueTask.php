<script>
lightboxSetWidth(550);//Resize
</script>

<style>
.vTaskDetails		{text-align:center;}
.percentBar			{margin:10px 15px 0px 0px;}/*Surcharge common.css*/
</style>

<div class="lightboxContent">
	<?php
	////	MENU CONTEXTUEL/D'EDITION  &&  TITRE
	echo $curObj->menuContextEdit()."<div class='lightboxTitle'>".$curObj->priority()." ".$curObj->title."</div>";

	////	DESCRIPTION / PERSONNES RESPONSABLES / AVANCEMENT / DATES DEBUT & FIN / FICHIERS JOINTS
	if(!empty($curObj->description))	{echo $curObj->description."<hr>";}
	echo "<div class='vTaskDetails'>";
		echo $curObj->responsiblePersons(true).$curObj->advancement(true).$curObj->dateBeginEnd(true);
	echo "</div>";
	echo $curObj->menuAttachedFiles();
	?>
</div>