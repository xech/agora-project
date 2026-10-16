

<span for="<?= $objMenuId ?>" class="menuContextLaunch <?= stristr($burgerLauncher,"float")?'menuContextLaunchFloat':null ?>" <?= Txt::tooltip("menuOptions") ?>>
	<img src="app/img/menuDot.svg" class="<?= $curObj->isRecent()?'menuContextLaunchRecent':null ?>">
	<?= $burgerLauncherLabel ?>
</span>


<!--MENU CONTEXTUEL-->
<div id="<?= $objMenuId ?>" class="menuContext">

	<!--LABEL DE L'OBJET-->
	<div class="menuContextLabel" id="<?= $curObj->uniqId("objLabel") ?>"><?= $curObj->getLabel() ?></div>
	<hr>

	<!--MODIFIER-->
	<?php if($curObj->editRight()){ ?>
		<div class="menuLine" onclick="lightboxOpen('<?= $curObj->getUrl('edit') ?>')">
			<div class="menuIcon"><img src="app/img/edit.svg"></div>
			<div><?= $editLabel ?></div>
		</div>
	<?php } ?>

	<!--CHANGER DE DOSSIER-->
	<?php if(!empty($moveObjectUrl)){ ?>
		<div class="menuLine" onclick="lightboxOpen('<?= $moveObjectUrl ?>')">
			<div class="menuIcon"><img src="app/img/folderMove.svg"></div>
			<div><?= Txt::trad("changeFolder") ?></div>
		</div>
	<?php } ?>

	<!--SUPPRIMER-->
	<?php if(!empty($deleteLabel)){ ?>
		<div class="menuLine" onclick="<?= $confirmDeleteJs ?>">
			<div class="menuIcon"><img src="app/img/delete.svg"></div>
			<div><?= $deleteLabel ?></div>
		</div>
	<?php } ?>

	<!--SELECTIONNER-->
	<?php if($curObj->isSelectable()){ ?>
		<div class="menuLine" onclick="objSelectSwitch('<?= $curObj->uniqId('objCheckbox') ?>')">
			<div class="menuIcon"><img src="app/img/check.svg"></div>
			<div><?= Txt::trad("selectUnselect") ?></div>
		</div>
		<input type="checkbox" name="objectsTypeId[]" class="objSelectCheckbox" value="<?= $curObj->typeId ?>" id="<?= $curObj->uniqId('objCheckbox') ?>">
	<?php } ?>

	<!--HISTORIQUE/LOGS-->
	<?php if(!empty($logUrl)){ ?>
		<div class="menuLine" onclick="lightboxOpen('<?= $logUrl ?>')">
			<div class="menuIcon"><img src="app/img/history.svg"></div>
			<div><?= Txt::trad("objHistory") ?></div>
		</div>
	<?php } ?>

	<!--URL DE PARTAGE-->
	<?php if(!empty($getUrlExternal)){ ?>
		<div class="menuLine" onclick="navigator.clipboard.writeText('<?= $getUrlExternal ?>');notify('<?= Txt::trad('copyUrlNotif',true) ?>');" <?= Txt::tooltip("copyUrlTooltip") ?> >
			<div class="menuIcon"><img src="app/img/share.svg"></div>
			<div><?= Txt::trad("copyUrl") ?></div>
		</div>
	<?php } ?>

	<!--OPTIONS SPECIFIQUES A L'OBJET-->
	<?php 
	foreach($objOptions as $tmpOption){
		$actionJsTmp=(!empty($tmpOption["actionJs"])) ?  'onclick="'.$tmpOption["actionJs"].'"'  :  null;
		$tooltipTmp =(!empty($tmpOption["tooltip"]))  ?  Txt::tooltip($tmpOption["tooltip"])  :  null;
	?>
		<?= isset($tmpOption["separator"]) ? $tmpOption["separator"] : null ?>
		<div class="menuLine menuLineSpecific" <?= $actionJsTmp.$tooltipTmp ?>>
			<?php if(!empty($tmpOption["iconSrc"])){ ?><div class="menuIcon"><img src="app/img/<?= $tmpOption["iconSrc"] ?>"></div><?php } ?>
			<div><?= $tmpOption["label"] ?></div>
		</div>
	<?php 
	}
	?>

	<!--AUTEUR/DATE DE CREATION/MODIF-->
	<?php if(!empty($autorDateCrea)){ ?>
		<hr>
		<!--AUTEUR/DATE DE CREATION-->
		<?php if(!empty($autorDateCrea)){ ?>
			<div class="menuLine">
				<div class="menuContextTxtLeft"><?= Txt::trad("createdBy") ?></div>
				<div><?= $autorDateCrea ?></div>
			</div>
		<?php } ?>
		<!--AUTEUR/DATE DE MODIF-->
		<?php if(!empty($autorDateModif)){ ?>
			<div class="menuLine">
				<div class="menuContextTxtLeft"><?= Txt::trad("modifBy") ?></div>
				<div><?= $autorDateModif ?></div>
			</div>
		<?php } ?>
		<!--OBJ RECENT-->
		<?php if($curObj->isRecent()){ ?>
			<div class="menuLine" <?= Txt::tooltip("objNewTooltip") ?> >
				<div class="menuContextTxtLeft">&nbsp;</div>
				<div><img src="app/img/clock.svg" class="menuContextLaunchRecent"> <?= Txt::trad("objNew") ?></div>
			</div>
		<?php } ?>
	<?php } ?>

	<!--AFFECTATIONS ET DROITS D'ACCES-->
	<?php if(!empty($affectLabels)){ ?>
		<hr>
		<!--AFFECTATIONS EN ECRITURE-->
		<?php if(!empty($affectLabels["2"])){ ?>
			<div class="menuLine accessWrite" <?= Txt::tooltip($affectTooltips["2"]) ?> >
				<div class="menuContextTxtLeft"><?= Txt::trad("accessWrite") ?></div>
				<div><?= $affectLabels["2"] ?></div>
			</div>
		<?php } ?>
		<!--AFFECTATIONS EN ECRITURE LIMITE-->
		<?php if(!empty($affectLabels["1.5"])){ ?>
			<div class="menuLine accessWrite" <?= Txt::tooltip($affectTooltips["1.5"]) ?> >
				<div class="menuContextTxtLeft"><?= Txt::trad("accessWriteLimit") ?></div>
				<div><?= $affectLabels["1.5"] ?></div>
			</div>
		<?php } ?>
		<!--AFFECTATIONS EN LECTURE-->
		<?php if(!empty($affectLabels["1"])){ ?>
			<div class="menuLine accessRead" <?= Txt::tooltip($affectTooltips["1"]) ?> >
				<div class="menuContextTxtLeft"><?= Txt::trad("accessRead") ?></div>
				<div><?= $affectLabels["1"] ?></div>
			</div>
		<?php } ?>
	<?php } ?>

	<!--LISTE DES FICHIERS JOINTS-->
	<?=  $curObj->attachedFileMenu() ?>

</div>


<!--ICONES SOUS LE LAUNCHER DU MENU CONTEXT-->
<?php if($burgerLauncher=="big-float"){ ?>
	<div class="menuContextObj">

		<!--FICHIERS JOINTS-->
		<?php if($curObj->attachedFileMenu()){ ?>
			<div><img src="app/img/attachment.svg"></div>
		<?php } ?>

		<!--LIKES-->
		<?php if($curObj->hasUsersLike()){
			$likeNb=(int)count($curObj->getUsersLike());
			if(empty($likeNb))	{$classHide='hide';	$circleNb=null;}
			else				{$classHide=null;  	$circleNb=$likeNb;}
			$likeOnclick="usersLikeUpdate('".$curObj->typeId."')";
		 ?>
			<div id="usersLike_<?= $curObj->typeId ?>" class="<?= $classHide ?>" onclick="<?= $likeOnclick ?>" <?= Txt::tooltip($curObj->usersLikeTooltip()) ?> >
				<span class="circleNb"><?= $circleNb ?></span>
				<img src="app/img/like.svg">
			</div>
		<?php } ?>

		<!--COMMENTAIRES-->
		<?php if($curObj->hasUsersComment()){
			$commentNb=(int)count($curObj->getUsersComment());
			if(empty($commentNb))	{$classHide='hide';	$circleNb=null;}
			else					{$classHide=null;  	$circleNb=$commentNb;}
			$commentTooltip=$commentNb." ".Txt::trad($commentNb>1?"AGORA_usersComments":"AGORA_usersComment")." : ".Txt::trad("commentAdd");
			$commentOnclick="lightboxOpen('?ctrl=object&action=UsersComment&typeId=".$curObj->typeId."')";
		?>
			<div id="usersComment_<?= $curObj->typeId ?>" class="<?= $classHide ?>" onclick="<?= $commentOnclick ?>" <?= Txt::tooltip($commentTooltip) ?> >
				<span class="circleNb"><?= $circleNb ?></span>
				<img src="app/img/comment.svg">
			</div>
		<?php } ?>
	</div>
<?php } ?>