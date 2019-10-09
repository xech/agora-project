
<form id="pollForm<?= $objPoll->_id ?>" class="noConfirmClose">
	<ul>
	<?php
	//Inputs de chaque réponses
	foreach($objPoll->getResponses() as $tmpResponse)
	{
		$inputId="pollResponse".$newsDisplay.$tmpResponse["_id"];//$newsDisplay pour que le formulaire dans les news et le formulaire principal n'interfèrent pas
		echo "<li class='vPollResponseInput'>
				<input type=\"".($objPoll->multipleResponses==true?'checkbox':'radio')."\" name='pollResponse[]' value='".$tmpResponse["_id"]."' id=\"".$inputId."\">
				<label for=\"".$inputId."\">".$tmpResponse["label"].$objPoll->responseFileDiv($tmpResponse)."</label>
			  </li>";
	}
	?>
	</ul>
	<input type="hidden" name="targetObjId" value="<?= $objPoll->_targetObjId ?>">
	<span class="formMainButton"><button type="submit" title="<?= $submitButtonTooltip ?>"><?= Txt::trad("DASHBOARD_vote") ?></button></span>
</form>