<script>
////	Resize
lightboxSetWidth(550);

////	Contrôle du formulaire
function formControl()
{
	//Controle final (champs obligatoires, affectations/droits d'accès, etc)
	return mainFormControl();
}
</script>

<style>
textarea[name='description']	{<?= empty($curObj->description)?"display:none;":null ?>}
.objField>div					{display:inline-block; margin:10px 20px 20px 0px;}/*surcharge*/
</style>

<form action="index.php" method="post" onsubmit="return formControl()" enctype="multipart/form-data" class="lightboxContent">
	<?php
	////	TITRE & DESCRIPTION (sauf type "user")
	if($curObj->type!="user"){
		echo "<input type='text' name='title' value=\"".$curObj->title."\" class='textBig' placeholder=\"".Txt::trad("title")."\">
			  <img src='app/img/description.png' class='sLink' title=\"".Txt::trad("description")."\" onclick=\"$('textarea[name=description]').slideToggle();\"><br><br>
			  <textarea name='description' placeholder=\"".Txt::trad("description")."\">".$curObj->description."</textarea>";
	}

	////	PLAGE HORAIRE
	$timeSlotBeginOptions=$timeSlotEndOptions=null;
	for($h=1; $h<24; $h++)  {$timeSlotBeginOptions.="<option value='".$h."' ".($curObj->timeSlotBegin==$h?"selected":null).">".$h."h</option>";}
	for($h=1; $h<24; $h++)  {$timeSlotEndOptions  .="<option value='".$h."' ".($curObj->timeSlotEnd==$h?"selected":null).">".$h."h</option>";}
	echo "<div class='objField'>
			<div>".Txt::trad("CALENDAR_timeSlot")." :</div>
			<div><select name='timeSlotBegin'>".$timeSlotBeginOptions."</select> &nbsp; ".Txt::trad("at")." &nbsp; <select name='timeSlotEnd'>".$timeSlotEndOptions."</select></div>
		  </div>";
	
	////	MENU COMMUN
	echo $curObj->menuEdit();
	?>
</form>