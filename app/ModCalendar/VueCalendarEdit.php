<script>
////	Resize
lightboxSetWidth(580);
</script>

<style>
[name='description']		{<?= empty($curObj->description)?"display:none;":null ?>}
.vCalendarOption			{margin-top:20px;}
#divPropositionGuest		{<?= $hidePropositionGuest==true ? "display:none" : null ?>}
</style>

<form action="index.php" method="post" onsubmit="return mainFormControl()" enctype="multipart/form-data" class="lightboxContent">
	<?php
	////	TITRE & DESCRIPTION (sauf type "user")
	if($curObj->type!="user"){
		echo "<input type='text' name='title' value=\"".$curObj->title."\" class='textBig' placeholder=\"".Txt::trad("title")."\">
			  <img src='app/img/description.png' class='sLink' title=\"".Txt::trad("description")."\" onclick=\"$('textarea[name=description]').slideToggle();\"><br><br>
			  <textarea name='description' placeholder=\"".Txt::trad("description")."\">".$curObj->description."</textarea>";
	}

	////	PLAGE HORAIRE EN AFFICHAGE SEMAINE
	$timeSlotBegin=$timeSlotEnd=null;
	for($h=1; $h<24; $h++)  {$timeSlotBegin.="<option value='".$h."' ".($curObj->timeSlotBegin==$h?"selected":null).">".$h."h</option>";}
	for($h=1; $h<24; $h++)  {$timeSlotEnd  .="<option value='".$h."' ".($curObj->timeSlotEnd==$h?"selected":null).">".$h."h</option>";}
	echo "<div class='vCalendarOption'>".Txt::trad("CALENDAR_timeSlot")." : <select name='timeSlotBegin'>".$timeSlotBegin."</select> &nbsp; ".Txt::trad("at")." &nbsp; <select name='timeSlotEnd'>".$timeSlotEnd."</select></div>";

	////	OPTION DE NOTIFICATION PAR EMAIL À CHAQUE PROPOSITION D'ÉVÉNEMENT
	echo "<div class='vCalendarOption' title=\"".Txt::trad("CALENDAR_propositionNotifyInfo")."\">
			<input type='checkbox' name='propositionNotify' value='1' ".(!empty($curObj->propositionNotify)?'checked':null)." id='inputPropositionNotify'>
			<label for='inputPropositionNotify'>".Txt::trad("CALENDAR_propositionNotify")." <img src='app/img/mail.png'></label>
		  </div>";

	////	OPTION DE PROPOSITION D'ÉVÉNEMENT POUR LES GUESTS
	echo "<div class='vCalendarOption' id='divPropositionGuest' title=\"".Txt::trad("CALENDAR_propositionGuestInfo")."\">
			<input type='checkbox' name='propositionGuest' value='1' ".(!empty($curObj->propositionGuest)?'checked':null)." id='inputPropositionGuest'>
			<label for='inputPropositionGuest'>".Txt::trad("CALENDAR_propositionGuest")." <img src='app/img/user/accessGuest.png'></label>
		  </div>";

	////	MENU COMMUN
	echo $curObj->menuEdit();
	?>
</form>