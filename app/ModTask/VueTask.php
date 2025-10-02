<style>
.lightboxTitleInfos > span	{display:inline-block; margin:10px 10px 0px;}
.vProgressBars				{text-align:center;}
.progressBar				{margin:10px; padding:8px;}
</style>

<div>
	<!--MENU CONTEXT  /  TITRE  /  CATEGORIE  /  PRIORITE-->
	<div class="lightboxTitle">
		<?= $curObj->lightboxMenu().$curObj->title ?>
		<div class="lightboxTitleInfos"><?= $curObj->categoryLabel().$curObj->priorityLabel() ?></div>
	</div>

	<!--DESCRIPTION  /  RESPONSABLES  /  AVANCEMENT  /  DATES DEBUT & FIN  /  FICHIERS JOINTS-->
	<?= $curObj->description ?>
	<div class="vProgressBars"><?= $curObj->responsiblePersons(true).$curObj->progressAdvancement(true).$curObj->progressBeginEnd(true) ?></div>
	<?= $curObj->attachedFileMenu() ?>
</div>