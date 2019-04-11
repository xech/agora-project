<script>
lightboxSetWidth(450);//Resize
</script>

<div class="lightboxContent objVueBg">
	<div class="lightboxTitle">
		<?php
		if($curObj->editRight())	{echo "<a href=\"javascript:lightboxOpen('".$curObj->getUrl("edit")."')\" class='lightboxTitleEdit' title=\"".Txt::trad("modify")."\"><img src='app/img/edit.png'></a>";}
		echo $curObj->getLabel("all");
		?>
	</div>
	<div class="personLabelImg"><?= $curObj->getImg() ?></div>
	<div class="personVueFields"><?= $curObj->getFieldsValues("profile") ?></div>
	<?= $curObj->menuAttachedFiles() ?>
</div>