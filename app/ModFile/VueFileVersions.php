<script>
////	Resize
lightboxSetWidth(550);

////	Confirmation de suppression de version
function confirmDeleteVersion(dateCrea)
{
	if(confirm("<?= Txt::trad("FILE_confirmDeleteVersion")?>")){
		redir("?ctrl=file&action=DeleteFileVersion&targetObjId=<?= $curObj->_targetObjId ?>&dateCrea="+dateCrea);
	}
}
</script>

<style>
.vFileVersion			{margin-top:20px;}
.versionDetails			{margin-top:8px;}
img[src*='separator']	{margin:0px 8px 0px 8px;}
img[src*='download'],img[src*='delete']		{max-height:20px; margin-top:5px;}
img[src*='delete']		{margin-left:20px;}
</style>


<div class="lightboxContent">
	<div class="lightboxTitle"><?= Txt::trad("FILE_versionsOf")." <i>".$curObj->name."</i>" ?></div>
	<ol reversed>
		<?php foreach($curObj->getVersions() as $tmpVersion){ ?>
			<li class="vFileVersion">
				<?= $tmpVersion["name"] ?>
				<div class="versionDetails">
					<?= Txt::dateLabel($tmpVersion["dateCrea"],"full") ?>
					<img src="app/img/separator.png">
					<?= Ctrl::getObj("user",$tmpVersion["_idUser"])->getLabel() ?>
					<img src="app/img/separator.png">
					<?= File::displaySize($tmpVersion["octetSize"]) ?>
					<br>
					<a href="<?= $curObj->urlDownloadDisplay("download",$tmpVersion["dateCrea"]) ?>" target="_blank"><img src="app/img/download.png"> <?= Txt::trad("download")?></a>
					<a href="javascript:confirmDeleteVersion('<?= urlencode($tmpVersion["dateCrea"]) ?>')"><img src="app/img/delete.png"> <?= Txt::trad("delete")?></a>
				</div>
			</li>
		<?php } ?>
	</ol>
</div>