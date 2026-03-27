<?php
////	ICONE BURGER  (LAUNCHER)
$launchFloatClass=stristr($burgerLauncher,"float")  ?  'menuContextLaunchFloat'  :  null;
$burgerImg=(stristr($burgerLauncher,"small"))  ?  "menuSmall.png"  : "menu.png";
if($curObj->isRecent())  {$burgerImg=str_ireplace('menu','menuNew',$burgerImg);}
?>
<span for="<?= $objMenuId ?>" class="menuContextLaunch <?= $launchFloatClass ?>" <?= Txt::tooltip("menuOptions") ?>>
	<img src="app/img/<?= $burgerImg ?>">
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
			<div class="menuIcon"><img src="app/img/edit.png"></div>
			<div><?= $editLabel ?></div>
		</div>
	<?php } ?>

	<!--CHANGER DE DOSSIER-->
	<?php if(!empty($moveObjectUrl)){ ?>
		<div class="menuLine" onclick="lightboxOpen('<?= $moveObjectUrl ?>')">
			<div class="menuIcon"><img src="app/img/folder/folderMove.png"></div>
			<div><?= Txt::trad("changeFolder") ?></div>
		</div>
	<?php } ?>

	<!--SUPPRIMER-->
	<?php if(!empty($deleteLabel)){ ?>
		<div class="menuLine" onclick="<?= $confirmDeleteJs ?>">
			<div class="menuIcon"><img src="app/img/delete.png"></div>
			<div><?= $deleteLabel ?></div>
		</div>
	<?php } ?>

	<!--SELECTIONNER-->
	<?php if($curObj->isSelectable()){ ?>
		<div class="menuLine" onclick="objSelectSwitch('<?= $curObj->uniqId('objCheckbox') ?>')">
			<div class="menuIcon"><img src="app/img/check.png"></div>
			<div><?= Txt::trad("selectUnselect") ?></div>
		</div>
		<input type="checkbox" name="objectsTypeId[]" class="objSelectCheckbox" value="<?= $curObj->typeId ?>" id="<?= $curObj->uniqId('objCheckbox') ?>">
	<?php } ?>

	<!--HISTORIQUE/LOGS-->
	<?php if(!empty($logUrl)){ ?>
		<div class="menuLine" onclick="lightboxOpen('<?= $logUrl ?>')">
			<div class="menuIcon"><img src="app/img/log.png"></div>
			<div><?= Txt::trad("objHistory") ?></div>
		</div>
	<?php } ?>

	<!--URL DE PARTAGE-->
	<?php if(!empty($getUrlExternal)){ ?>
		<div class="menuLine" onclick="navigator.clipboard.writeText('<?= $getUrlExternal ?>');notify(labeCopyUrlNotif);" <?= Txt::tooltip("copyUrlTooltip") ?> >
			<div class="menuIcon"><img src="app/img/share.png"></div>
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
		<div class="menuLine" <?= $actionJsTmp.$tooltipTmp ?>>
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
				<div><?= Txt::trad("objNew") ?></div>
			</div>
		<?php } ?>
	<?php } ?>

	<!--AFFECTATIONS ET DROITS D'ACCES-->
	<?php if(!empty($affectLabels)){ ?>
		<hr>
		<!--AFFECTATIONS EN ECRITURE-->
		<?php if(!empty($affectLabels["2"])){ ?>
			<div class="menuLine sAccessWrite" <?= Txt::tooltip($affectTooltips["2"]) ?> >
				<div class="menuContextTxtLeft"><?= Txt::trad("accessWrite") ?></div>
				<div><?= $affectLabels["2"] ?></div>
			</div>
		<?php } ?>
		<!--AFFECTATIONS EN ECRITURE LIMITE-->
		<?php if(!empty($affectLabels["1.5"])){ ?>
			<div class="menuLine sAccessWrite" <?= Txt::tooltip($affectTooltips["1.5"]) ?> >
				<div class="menuContextTxtLeft"><?= Txt::trad("accessWriteLimit") ?></div>
				<div><?= $affectLabels["1.5"] ?></div>
			</div>
		<?php } ?>
		<!--AFFECTATIONS EN LECTURE-->
		<?php if(!empty($affectLabels["1"])){ ?>
			<div class="menuLine sAccessRead" <?= Txt::tooltip($affectTooltips["1"]) ?> >
				<div class="menuContextTxtLeft"><?= Txt::trad("accessRead") ?></div>
				<div><?= $affectLabels["1"] ?></div>
			</div>
		<?php } ?>
	<?php } ?>

	<!--LISTE DES FICHIERS JOINTS-->
	<?=  $curObj->attachedFileMenu() ?>

</div>


<!--ICONES FLOTTANTES-->
<?php if($burgerLauncher=="big-float"){ ?>
	<div class="menuContextSub">

		<!--FICHIERS JOINTS-->
		<?php if($curObj->attachedFileMenu()){ ?>
			<div><img src="app/img/attachment.png"></div>
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
				<img src="app/img/usersLike.png">
			</div>
		<?php } ?>

		<!--COMMENTAIRES-->
		<?php if($curObj->hasUsersComment()){
			$commentNb=(int)count($curObj->getUsersComment());
			if(empty($commentNb))	{$classHide='hide';	$circleNb=null;}
			else					{$classHide=null;  	$circleNb=$likeNb;}
			$commentTooltip=$commentNb." ".Txt::trad($commentNb>1?"AGORA_usersComments":"AGORA_usersComment")." : ".Txt::trad("commentAdd");
			$commentOnclick="lightboxOpen('?ctrl=object&action=UsersComment&typeId=".$curObj->typeId."')";
		?>
			<div id="usersComment_<?= $curObj->typeId ?>" class="<?= $classHide ?>" onclick="<?= $commentOnclick ?>" <?= Txt::tooltip($commentTooltip) ?> >
				<span class="circleNb"><?= $circleNb ?></span>
				<img src="app/img/usersComment.png">
			</div>
		<?php } ?>
	</div>
<?php } ?>