<form id="pollForm<?= $formType.$curObj->_id ?>">
	<ul>
	<?php
	////	INPUTS DES RÉPONSES
	foreach($curObj->getResponses() as $tmpResponse){
		$inputId="pollResponse".$formType.$tmpResponse["_id"];
	?>
		<li class="vPollResponseInput">
			<input type="<?= $curObj->multipleResponses==true?'checkbox':'radio' ?>" name="pollResponse[]" value="<?= $tmpResponse["_id"] ?>" id="<?= $inputId ?>">
			<label for="<?= $inputId ?>"><?= $tmpResponse["label"].$curObj->responseFileDiv($tmpResponse) ?></label>
		</li>
	<?php } ?>
	</ul>
	<input type="hidden" name="typeId" value="<?= $curObj->typeId ?>">
	<div class="submitButtonMain">
		<button type="submit" <?= Txt::tooltip($submitButtonTooltip) ?> ><?= Txt::trad("DASHBOARD_POLLS_voteSubmit") ?></button>
	</div>
</form>