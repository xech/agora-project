<script>
////	Resize
lightboxWidth(600);

////	Confirmation de suppression de version
function confirmDeleteVersion(dateCrea)
{
	confirmRedir("?ctrl=file&action=DeleteFileVersion&typeId=<?= $curObj->_typeId ?>&dateCrea="+dateCrea, "<?= Txt::trad("FILE_confirmDeleteVersion")?>");
}
</script>

<style>
.vFileVersion			{margin-top:20px;}
.versionDetails			{margin-top:8px;}
img[src*='separator']	{margin:0px 8px 0px 8px;}
img[src*='download'],img[src*='delete']		{max-height:20px; margin-top:5px;}
img[src*='delete']		{margin-left:20px;}
</style>


<div>
	<div class="lightboxTitle"><?= Txt::trad("FILE_versionsOf")." <i>".$curObj->name."</i>" ?></div>
	<ol reversed>
		<?php foreach($curObj->getVersions() as $tmpVersion){ ?>
			<li class="vFileVersion">
				<?= $tmpVersion["name"] ?>
				<div class="versionDetails">
					<?php
					echo Txt::dateLabel($tmpVersion["dateCrea"],"labelFull").'<img src="app/img/separator.png">'.
					Ctrl::getObj("user",$tmpVersion["_idUser"])->getLabel().'<img src="app/img/separator.png">'.
					File::sizeLabel($tmpVersion["octetSize"]).'<br>'.
					'<a href="'.$curObj->urlDownload($tmpVersion["dateCrea"]).'" target="_blank"><img src="app/img/download.png"> '.Txt::trad("download").'</a>'.
					'<a onclick="confirmDeleteVersion(\''.urlencode($tmpVersion["dateCrea"]).'\')"><img src="app/img/delete.png"> '.Txt::trad("delete").'</a>';
					?>
				</div>
			</li>
		<?php } ?>
	</ol>
</div>