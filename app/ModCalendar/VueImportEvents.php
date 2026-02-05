<script>
ready(function(){
	////	Controle du formulaire
	$("#mainForm").on("submit",function(){
		if($("input[name='importFile']").exist()){
			if($("input[name='importFile']").isEmpty())						{notify("<?= Txt::trad("specifyFile") ?>");			return false;}
			else if(extension($("input[name='importFile']").val())!="ics")	{notify("<?= Txt::trad("fileExtension") ?> ICS");	return false;}
		}
		submitLoading();
	});

	////	Switch la sélection
	$("#checkSwitch").on("click",function(){
		$(":checkbox[name^=eventList]").trigger("click");
	});
});
</script>


<style>
#bodyLightbox					{max-width:1400px;}
form							{text-align:center;}
.evtListTable					{width:100%;}
.evtListTable img				{vertical-align:middle;}
.evtListHeader					{text-align:center;}
.evtListTable td				{padding:5px;}
.evtLine						{text-align:left;}
.evtCheckbox					{width:40px; cursor:pointer;}
.evtDates						{width:300px;}
.evtTitle						{width:400px;}
.evtDescription					{font-size:0.9rem;}
.evtPresent						{width:100px; font-size:0.9rem;}
.lineHover:has(input:checked)	{background:<?= Ctrl::$agora->skin=="black"?"#333":"#ddd" ?>;}
</style>


<form action="index.php" method="post" id="mainForm" enctype="multipart/form-data">
	<div class="lightboxTitle">
		<?= Txt::trad("CALENDAR_importIcal") ?>
		<?= !empty($eventList) ?  "&nbsp; : ".count($eventList)." ".Txt::trad("OBJ_calendarEvent")."s"  : null ?>
	</div>

	<!--SELECTION DU FICHIER D'IMPORT-->
	<?php if(empty($eventList)){ ?>
		<input type="file" name="importFile"><br><br>
		<input type="checkbox" name="ignoreOldEvt" value="1" id="ignoreOldEvt" checked><label for="ignoreOldEvt"><?= Txt::trad("CALENDAR_importIgnoreOldEvt") ?></label>


	<!--AFFICHAGE DES EVENEMENTS A IMPORTER-->
	<?php }else{ ?>
		<table class="evtListTable">
			<tr class="evtListHeader">
				<td class="evtCheckbox" id="checkSwitch" <?= Txt::tooltip("selectSwitch") ?>><img src="app/img/checkSwitch.png"></td>
				<td class="evtDates"><?= Txt::trad("begin")."-".Txt::trad("end") ?></td>
				<td class="evtTitle"><?= Txt::trad("title") ?></td>
				<td class="evtDescription"><?= Txt::trad("description") ?></td>
				<td class="evtPresent">&nbsp;</td>
			</tr>

			<!--LISTE D'EVENEMENTS-->
			<?php foreach($eventList as $cptEvt=>$tmpEvt){
				$evtBoxId="boxEvent".$cptEvt;
				$evtPresent=$evtCheck=null;
				if(!empty($tmpEvt["isPresent"]))	{$evtPresent=Txt::trad("CALENDAR_importEvtPresent");} 
				else								{$evtCheck="checked";}
				$evtDates		=Txt::dateLabel($tmpEvt["db_dateBegin"],"default",$tmpEvt["db_dateEnd"]);
				$evtDatesTooltip=Txt::dateLabel($tmpEvt["db_dateBegin"],"labelFull",$tmpEvt["db_dateEnd"]);
			 ?>
				<tr class="evtLine lineHover">
					<td class="evtCheckbox">
						<input type="checkbox" name="eventList[<?= $cptEvt ?>][checked]"		value="1" id="<?= $evtBoxId ?>" <?= $evtCheck ?>>
						<input type="hidden" name="eventList[<?= $cptEvt ?>][db_dateBegin]"		value="<?= $tmpEvt["db_dateBegin"] ?>">
						<input type="hidden" name="eventList[<?= $cptEvt ?>][db_dateEnd]"		value="<?= $tmpEvt["db_dateEnd"] ?>">
						<input type="hidden" name="eventList[<?= $cptEvt ?>][db_title]"			value="<?= $tmpEvt["db_title"] ?>">
						<input type="hidden" name="eventList[<?= $cptEvt ?>][db_description]"	value="<?= $tmpEvt["db_description"] ?>">
						<input type="hidden" name="eventList[<?= $cptEvt ?>][db_periodType]"	value="<?= $tmpEvt["db_periodType"] ?>">
						<input type="hidden" name="eventList[<?= $cptEvt ?>][db_periodValues]"	value="<?= $tmpEvt["db_periodValues"] ?>">
						<input type="hidden" name="eventList[<?= $cptEvt ?>][db_periodDateEnd]"	value="<?= $tmpEvt["db_periodDateEnd"] ?>">
					</td>
					<td class="evtDates" <?= Txt::tooltip($evtDatesTooltip) ?> ><?= $evtDates ?></td>
					<td class="evtTitle"><label for="<?= $evtBoxId ?>"><?= $tmpEvt["db_title"] ?></label></td>
					<td class="evtDescription"><?= Txt::reduce($tmpEvt["db_description"],200) ?></td>
					<td class="evtPresent" <?= Txt::tooltip("CALENDAR_importEvtPresentInfo") ?> ><?= $evtPresent ?></td>
				</tr>
			<?php } ?>
		</table>
	<?php } ?>
	
	<!--VALIDATION DU FORM-->
	<?= Txt::submitButton("validate") ?>
</form>