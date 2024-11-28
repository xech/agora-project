<script>
lightboxSetWidth(600);
</script>

<style>
.vEvtDetails						{margin-top:15px;}
.vEvtDetails img, .categoryColor	{margin-right:15px;}
.vEvtDetails img					{max-width:24px;}
.categoryColor						{width:20px; height:20px;}
</style>


<div>
	<?php
	////	MENU CONTEXTUEL & D'EDITION / TITRE / DESCRIPTION
	echo '<div class="lightboxTitle">'.$curObj->lightboxTitleMenu().$curObj->title.'</div>';
	if(!empty($curObj->description))	{echo $curObj->description.'<hr>';}

	////	DATE / PERIODICITE / CATEGORIE
	echo '<div class="vEvtDetails"><img src="app/img/calendar/clock.png">'.Txt::dateLabel($curObj->dateBegin,"basic",$curObj->dateEnd).'</div>';
	if(!empty($labelPeriod))			{echo '<hr><div class="vEvtDetails"><img src="app/img/reload.png">'.$labelPeriod.'</div>';}
	if($curObj->_idCat)					{echo '<hr><div class="vEvtDetails">'.$curObj->categoryLabel().'</div>';}

	////	AFFECTATIONS AUX AGENDAS / IMPORTANT / VISIBILITE SPECIALE
	if($curObj->important)				{echo '<hr><div class="vEvtDetails"><img src="app/img/important.png">'.Txt::trad("CALENDAR_importanceHight").'</div>';}
	if(Ctrl::$curUser->isUser())		{echo '<hr><div class="vEvtDetails"><img src="app/img/calendar/iconSmall.png">'.$curObj->affectedCalendarsLabel().'</div>';}
	if(!empty($contentVisibility))		{echo '<hr><div class="vEvtDetails" '.Txt::tooltip("CALENDAR_visibilityTooltip").'><img src="app/img/displayHide.png">'.$contentVisibility.'</div>';}

	////	VISIOCONFERENCE / FICHIERS JOINTS
	if(!empty($curObj->visioUrl))		{echo '<hr><div class="vEvtDetails" onclick="launchVisio(\''.$curObj->visioUrl.'\')"><img src="app/img/visioSmall.png">'.Txt::trad("VISIO_launchFromEvent").'</div>';}
	echo $curObj->attachedFileMenu();
	?>
</div>