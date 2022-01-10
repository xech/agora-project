<script>
////	Resize
lightboxSetWidth("1300px");

////	Contrôle du formulaire
function formControl()
{
	//Fichier Import au format csv
	if($("input[name='importFile']").exist()){
		if($("input[name='importFile']").isEmpty())						{notify("<?= Txt::trad("specifyFile") ?>");	return false;}
		else if(extension($("input[name='importFile']").val())!="ics")	{notify("<?= Txt::trad("fileExtension") ?> ICS");	return false;}
	}
	//Affiche l'icone "loading"
	submitButtonLoading();
}
</script>

<style>
form						{text-align:center; padding:0px; margin:0px;}
.vTable						{width:98%;}
.vTable td					{text-align:left; vertical-align:top; padding:5px;}
.vTable img					{vertical-align:middle;}
.vTable tr:hover			{background:#eee;}
.vTable tr:first-child td	{background:#ddd; text-align:center;}/*Titre des colonnes*/
.vTable tr td:first-child	{width:40px;}				/*checkbox*/
.vTable tr td:nth-child(2)	{width:40px; cursor:help;}	/*isPresent*/
.vTable tr td:nth-child(3)	{width:250px;}				/*date*/
.vTable tr td:nth-child(4)	{width:400px;}				/*titre*/
.vTable tr td:nth-child(5)	{font-weight:normal;}		/*description*/
</style>


<form action="index.php" method="post" enctype="multipart/form-data" onsubmit="return formControl()" class="lightboxContent">
	<div class="lightboxTitle"><?= Txt::trad("CALENDAR_importIcal").(!empty($eventList)?" : ".count($eventList)." elements":null) ?></div>
	
	<?php
	////	SELECTION DU FICHIER D'IMPORT
	if(empty($eventList)){
		echo '<input type="file" name="importFile"><br><br>
			  <input type="checkbox" name="ignoreOldEvt" value="1" id="ignoreOldEvt" checked><label for="ignoreOldEvt">'.Txt::trad("CALENDAR_ignoreOldEvt").'</label>';
	}
	////	AFFICHAGE DES EVENEMENTS A IMPORTER
	else
	{
		////DEBUT DU TABLEAU + HEADER
		echo '<table class="vTable">
				<tr class="vTableHeader">
					<td title="'.Txt::trad("selectSwitch").'"><img src="app/img/switch.png" class="sLink" onclick="$(\':checkbox[id^=boxEvent]\').trigger(\'click\');"></td>
					<td>'.Txt::trad("CALENDAR_importIcalState").'</td>
					<td>'.Txt::trad("begin").' - '.Txt::trad("end").'</td>
					<td>'.Txt::trad("title").'</td>
					<td>'.Txt::trad("description").'</td>
				</tr>';
			////LISTE D'EVENEMENTS
			foreach($eventList as $cptEvt=>$tmpEvt)
			{
				//Prépare l'affichage
				$evtBoxId="boxEvent".$cptEvt;
				if($tmpEvt["isPresent"]==true)	{$evtCheck=null;		$isPresentImg="dotR.png";	$isPresentTrad="CALENDAR_importIcalStatePresent";}
				else							{$evtCheck="checked";	$isPresentImg="dotG.png";	$isPresentTrad="CALENDAR_importIcalStateImport";}
				//Afiche l'evt
				echo '<tr>
						<td>
							<input type="checkbox"	name="eventList['.$cptEvt.'][checked]"			value="1" id="'.$evtBoxId.'" '.$evtCheck.'>
							<input type="hidden"	name="eventList['.$cptEvt.'][dbDateBegin]"		value="'.$tmpEvt["dbDateBegin"].'">
							<input type="hidden"	name="eventList['.$cptEvt.'][dbDateEnd]"		value="'.$tmpEvt["dbDateEnd"].'">
							<input type="hidden"	name="eventList['.$cptEvt.'][dbTitle]"			value="'.$tmpEvt["dbTitle"].'">
							<input type="hidden"	name="eventList['.$cptEvt.'][dbDescription]"	value="'.$tmpEvt["dbDescription"].'">
							<input type="hidden"	name="eventList['.$cptEvt.'][dbPeriodType]"		value="'.$tmpEvt["dbPeriodType"].'">
							<input type="hidden"	name="eventList['.$cptEvt.'][dbPeriodValues]"	value="'.$tmpEvt["dbPeriodValues"].'">
							<input type="hidden"	name="eventList['.$cptEvt.'][dbPeriodDateEnd]"	value="'.$tmpEvt["dbPeriodDateEnd"].'">
						</td>
						<td><img src="app/img/'.$isPresentImg.'" title="'.Txt::trad($isPresentTrad).'"></td>
						<td>'.Txt::dateLabel($tmpEvt["dbDateBegin"],"full",$tmpEvt["dbDateEnd"]).'</td>
						<td><label for="'.$evtBoxId.'">'.$tmpEvt["dbTitle"].'</label></td>
						<td>'.Txt::reduce($tmpEvt["dbDescription"],120).'</td>
					</tr>';
			}
		////FIN DU TABLEAU
		echo '</table>';
	}
	
	////	VALIDATION DU FORM
	echo Txt::submitButton();
	?>
</form>