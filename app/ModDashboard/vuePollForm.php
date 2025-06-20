
<form id="pollForm<?= $newsDisplay.$curObj->_id ?>">
	<ul>
	<?php
	//Inputs de chaque réponses
	foreach($curObj->getResponses() as $tmpResponse)
	{
		$inputId="pollResponse".$newsDisplay.$tmpResponse["_id"];//$newsDisplay pour que le formulaire dans les news et le formulaire principal n'interfèrent pas
		echo "<li class='vPollResponseInput'>
				<input type=\"".($curObj->multipleResponses==true?'checkbox':'radio')."\" name='pollResponse[]' value='".$tmpResponse["_id"]."' id=\"".$inputId."\">
				<label for=\"".$inputId."\">".$tmpResponse["label"].$curObj->responseFileDiv($tmpResponse)."</label>
			  </li>";
	}
	?>
	</ul>
	<input type="hidden" name="typeId" value="<?= $curObj->_typeId ?>">
	<div class="submitButtonMain"><button type="submit" <?= Txt::tooltip($submitButtonTooltip) ?> ><?= Txt::trad("DASHBOARD_vote") ?></button></div>
</form>