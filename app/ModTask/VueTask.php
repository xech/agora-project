<style>
.lightboxTitleDetail > span	{display:inline-block; margin-right:10px;}/*categories/priorité */
.vProgressBars				{text-align:center;}
.progressBar				{margin:10px; padding:8px 12px;}
</style>

<div>
	<!--MENU CONTEXT  /  TITRE  /  CATEGORIE  /  PRIORITE-->
	<div class="lightboxTitle">
		<?= $curObj->lightboxMenu().$curObj->title ?>
		<div class="lightboxTitleDetail"><?= $curObj->categoryLabel().$curObj->priorityLabel() ?></div>
	</div>

	<!--DESCRIPTION  /  RESPONSABLES  /  AVANCEMENT  /  DATES DEBUT & FIN  /  FICHIERS JOINTS-->
	<?= $curObj->description ?>
	<div class="vProgressBars"><?= $curObj->responsiblePersons(true).$curObj->progressAdvancement(true).$curObj->progressBeginEnd(true) ?></div>
	<?= $curObj->attachedFileMenu() ?>
</div>