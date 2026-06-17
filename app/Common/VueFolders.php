
<!--AFFICHE CHAQUE DOSSIERS DE LA VUE-->
<?php foreach($foldersList as $tmpFolder){ ?>
	<?= $tmpFolder->objContentDiv($containerClass) ?>
		<div class="objContentTab objFolders" >
			<div class="objIcon">
				<img src="<?= $tmpFolder->iconPath() ?>" onclick="redir('<?= $tmpFolder->getUrl() ?>')">
			</div>
			<div class="objLabel" onclick="redir('<?= $tmpFolder->getUrl() ?>')" <?= Txt::tooltip($tmpFolder->name.'<br>'.$tmpFolder->description) ?>>
				<?= Txt::reduce($tmpFolder->name,60) ?>
			</div>
			<div class="objDetails">
				<div><?= $tmpFolder->contentDescription() ?></div>
				<div><?= $tmpFolder->folderDetails() ?></div>
			</div>
			<div class="objAutorDate"><?= $tmpFolder->autorDate(true) ?></div>
		</div>
	</div>
<?php } ?>