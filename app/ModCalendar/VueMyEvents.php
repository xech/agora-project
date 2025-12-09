<style>
.lightboxTitle select	{float:right; font-size:0.95rem;}
.vEvtTable				{display:table; width:100%;}
.vEvtRow				{display:table-row;}
.vEvtRow>div			{display:table-cell; padding:5px; text-align:left;}
</style>


<div>
	<div class="lightboxTitle">
		<?= Txt::trad("CALENDAR_evtAutor") ?>
		<select onchange="location.href='?ctrl=calendar&action=MyEvents&sortEvents='+this.value">
			<option value="dateCrea"><?= Txt::trad("CALENDAR_evtAutorSortCrea") ?>
			<option value="dateBegin" <?= Req::param("sortEvents")=="dateBegin" ? "selected" : null ?>><?= Txt::trad("CALENDAR_evtAutorSortBegin") ?>
		</select>
	</div>

	<!--LISTE DES EVT-->
	<div class="vEvtTable">
		<?php foreach($myEvents as $tmpEvt){ ?>
		<div class="vEvtRow lineHover" <?= Txt::tooltip($tmpEvt->description) ?>>
			<div><?= $tmpEvt->contextMenu(["launcherIcon"=>"inlineBig"]) ?></div>
			<div><img src="app/img/edit.png" onclick="lightboxOpen('<?= $tmpEvt->getUrl('edit') ?>')"></div>
			<div onclick="lightboxOpen('<?= $tmpEvt->getUrl('vue') ?>')"><?= $tmpEvt->title ?></div>
			<div><?= Txt::dateLabel($tmpEvt->dateBegin,"labelFull",$tmpEvt->dateEnd) ?></div>
		</div>
		<?php } ?>
	</div>

	<!--AUCUN EVT-->
	<?php if(empty($myEvents)){echo "<h3>".Txt::trad("CALENDAR_noEvt")."</h3>";} ?>
</div>