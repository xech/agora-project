<script>
lightboxSetWidth(520);//Resize
</script>

<style>
.vEventDetails img	{max-width:18px;}
</style>

<div class="lightboxContent">
	<?php
	////	ICONE EDIT / TITRE
	$editIcon=($curObj->editRight())  ?  "<a href=\"javascript:lightboxOpen('".$curObj->getUrl("edit")."')\" class='lightboxTitleEdit' title=\"".Txt::trad("modify")."\"><img src='app/img/edit.png'></a>"  :  null;
	echo "<div class='lightboxTitle'>".$editIcon.$curObj->title."</div>";

	////	DATE / PERIODICITE
	echo "<div class='vEventDetails'><img src='app/img/calendar/clock.png'> &nbsp; ".Txt::displayDate($curObj->dateBegin,"full",$curObj->dateEnd)."</div>";
	if(!empty($labelPeriod))	{echo "<hr><div class='vEventDetails'>".$labelPeriod."</div>";}
	
	////	IMPORTANT / CATEGORIE
	if($curObj->important)	{echo "<hr><div class='vEventDetails'><img src='app/img/important.png'> ".Txt::trad("important")."</div>";}
	if($labelCategory)		{echo "<hr><div class='vEventDetails'>".$labelCategory."</div>";}

	////	AFFECTATIONS AUX AGENDAS / VISIBILITE SPECIALE / DESCRIPTION / VISIBILITE SPECIALE / FICHIERS JOINTS
	if(Ctrl::$curUser->isUser())		{echo "<hr><div class='vEventDetails'><img src='app/img/calendar/iconSmall.png'>&nbsp; ".$curObj->affectedCalendarsLabel()."</div>";}
	if(!empty($contentVisible))			{echo "<hr><div class='vEventDetails'>".$contentVisible."</div>";}
	if(!empty($curObj->description))	{echo "<hr>".$curObj->description;}
	echo $curObj->menuAttachedFiles();
	?>
</div>