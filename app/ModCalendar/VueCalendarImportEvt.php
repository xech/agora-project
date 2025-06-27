<script>
////	Controle du formulaire
ready(function(){
	$("#mainForm").on("submit",function(){
		if($("input[name='importFile']").exist()){
			if($("input[name='importFile']").isEmpty())						{notify("<?= Txt::trad("specifyFile") ?>");			return false;}
			else if(extension($("input[name='importFile']").val())!="ics")	{notify("<?= Txt::trad("fileExtension") ?> ICS");	return false;}
		}
		submitLoading();
	});
});
</script>


<style>
#bodyLightbox					{max-width:1200px;}
form							{padding:0px; margin:0px; text-align:center;}
.vTable							{width:98%;}
.vTable img						{vertical-align:middle;}
.vTableHeader					{text-align:center;}			/*Titre des colonnes*/
tr:not(.vTableHeader)			{text-align:left;}				/*Ligne des evts*/
.vTable td						{padding:5px;}					/*Cellules du tableau*/
.vTable tr td:first-child		{width:40px;}					/*checkbox*/
.vTable tr td:nth-child(2)		{width:60px; cursor:help;}		/*isPresent*/
.vTable tr td:nth-child(3)		{width:280px;}					/*date*/
.vTable tr td:nth-child(4)		{width:400px;}					/*titre*/
.lineHover:has(input:checked)	{background:<?= Ctrl::$agora->skin=="black"?"#333":"#ddd" ?>;}
</style>


<form action="index.php" method="post" id="mainForm" enctype="multipart/form-data">
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
		//// DEBUT DU TABLEAU + HEADER
		echo '<table class="vTable">
				<tr class="vTableHeader">
					<td '.Txt::tooltip("selectSwitch").'><img src="app/img/checkSwitch.png" onclick="$(\':checkbox[id^=boxEvent]\').trigger(\'click\');"></td>
					<td>'.Txt::trad("CALENDAR_importIcalPresent").'</td>
					<td>'.Txt::trad("begin").' - '.Txt::trad("end").'</td>
					<td>'.Txt::trad("title").'</td>
					<td>'.Txt::trad("description").'</td>
				</tr>';
			//// LISTE D'EVENEMENTS
			foreach($eventList as $cptEvt=>$tmpEvt){
				$evtBoxId="boxEvent".$cptEvt;
				$evtCheck=empty($tmpEvt["isPresent"])  ?  "checked"  :  null;
				$evtCheckLabel=empty($tmpEvt["isPresent"])  ?  Txt::trad('no')  :  '<i>'.Txt::trad('yes').'</i>';
				echo '<tr class="lineHover">
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
						<td '.Txt::tooltip("CALENDAR_importIcalPresentInfo").'>'.$evtCheckLabel.'</td>
						<td>'.Txt::dateLabel($tmpEvt["dbDateBegin"],"labelFull",$tmpEvt["dbDateEnd"]).'</td>
						<td><label for="'.$evtBoxId.'">'.$tmpEvt["dbTitle"].'</label></td>
						<td>'.Txt::reduce($tmpEvt["dbDescription"],120).'</td>
					</tr>';
			}
		//// FIN DU TABLEAU
		echo '</table>';
	}
	
	////	VALIDATION DU FORM
	echo Txt::submitButton("validate");
	?>
</form>