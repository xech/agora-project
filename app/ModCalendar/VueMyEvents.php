<script>
lightboxSetWidth(650);//Resize
</script>

<style>
.vEventLine		{display:table; width:100%; margin:5px;}
.vEventLine>div	{display:table-cell;}
.vEventDate		{width:200px;}
.vEventOptions	{width:50px;}

/*RESPONSIVE FANCYBOX (440px)*/
@media screen and (max-width:440px){
	.vEventDate		{width:80px;}
}
</style>


<div class="lightboxContent">
	<div class="lightboxTitle"><?= Txt::trad("CALENDAR_evtAutor") ?></div>

	<!--LISTE DES EVT-->
	<?php foreach($myEvents as $tmpEvent){ ?>
	<div class="vEventLine sTableRow" title="<?= Txt::displayDate($tmpEvent->dateBegin,"full",$tmpEvent->dateEnd) ?><br><?= $tmpEvent->description ?>">
		<div class="vEventDate"><?= Txt::displayDate($tmpEvent->dateBegin,"normal",$tmpEvent->dateEnd) ?></div>
		<div><?= $tmpEvent->title ?></div>
		<div class="vEventOptions">
			<img src="app/img/edit.png" class="sLink" onclick="lightboxOpen('<?= $tmpEvent->getUrl("edit") ?>')">
			<img src="app/img/delete.png" class="sLink" onclick="if(confirm('<?= Txt::trad("confirmDelete",true) ?>')) {lightboxClose('<?= $tmpEvent->getUrl("delete") ?>');}">
		</div>
	</div>
	<?php } ?>
	<!--AUCUN EVT-->
	<?php if(empty($myEvents)){echo "<h3>".Txt::trad("CALENDAR_noEvt")."</h3>";} ?>
</div>