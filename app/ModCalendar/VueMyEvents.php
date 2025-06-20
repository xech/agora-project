<style>
#bodyLightbox		{max-width:800px;}
.vEvtLine			{display:table; width:100%; padding:5px;}
.vEvtLine>div		{display:table-cell; padding:5px;}
.vEvtDate			{width:300px;}
.vEvtOptions		{width:80px; text-align:right;}
</style>


<div>
	<div class="lightboxTitle"><?= Txt::trad("CALENDAR_evtAutor") ?></div>

	<!--LISTE DES EVT-->
	<?php foreach($myEvents as $tmpEvent){ ?>
	<div class="vEvtLine lineHover" <?= Txt::tooltip($tmpEvent->description) ?>>
		<div class="vEvtDate"><?= Txt::dateLabel($tmpEvent->dateBegin,"labelFull",$tmpEvent->dateEnd) ?></div>
		<div><?= $tmpEvent->title ?></div>
		<div class="vEvtOptions">
			<img src="app/img/edit.png" onclick="lightboxOpen('<?= $tmpEvent->getUrl('edit') ?>')"> &nbsp;
			<img src="app/img/delete.png" onclick="confirmDelete('<?= $tmpEvent->getUrl('delete') ?>')">
		</div>
	</div>
	<?php } ?>
	<!--AUCUN EVT-->
	<?php if(empty($myEvents)){echo "<h3>".Txt::trad("CALENDAR_noEvt")."</h3>";} ?>
</div>