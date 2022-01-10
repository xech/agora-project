<script>
lightboxSetWidth(550);
</script>

<style>
.vEventDetails img	{max-width:22px;}
</style>

<div class="lightboxContent">
	<?php
	////	MENU CONTEXTUEL/D'EDITION  &&  TITRE
	echo "<div class='lightboxTitle'>".$curObj->menuContextEdit().$curObj->title."</div>";

	////	DATE / PERIODICITE
	echo "<div class='vEventDetails'><img src='app/img/calendar/clock.png'> &nbsp; ".Txt::dateLabel($curObj->dateBegin,"full",$curObj->dateEnd)."</div>";
	if(!empty($labelPeriod))	{echo "<hr><div class='vEventDetails'>".$labelPeriod."</div>";}
	
	////	IMPORTANT / CATEGORIE
	if($curObj->important)	{echo "<hr><div class='vEventDetails'><img src='app/img/important.png'> &nbsp; ".Txt::trad("CALENDAR_importanceHight")."</div>";}
	if($labelCategory)		{echo "<hr><div class='vEventDetails'>".$labelCategory."</div>";}

	////	AFFECTATIONS AUX AGENDAS / VISIBILITE SPECIALE / VISIOCONFERENCE / DESCRIPTION / FICHIERS JOINTS
	if(Ctrl::$curUser->isUser())		{echo "<hr><div class='vEventDetails'><img src='app/img/calendar/iconSmall.png'>&nbsp; ".$curObj->affectedCalendarsLabel()."</div>";}
	if(!empty($contentVisibility))		{echo "<hr><div class='vEventDetails'>".$contentVisibility."</div>";}
	if(!empty($curObj->visioUrl))		{echo "<hr><a href=\"javascript:launchVisio('".$curObj->visioUrl."')\"><img src='app/img/visioSmall.png'>&nbsp; ".Txt::trad("VISIO_launchFromEvent")."</a>";}
	if(!empty($curObj->description))	{echo "<hr>".$curObj->description;}
	echo $curObj->attachedFileMenu();
	?>
</div>