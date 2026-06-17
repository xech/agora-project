<style>
.vAdminLabel		{text-align:center;}
.vAdminLabel hr		{margin-block:15px;}
.vAdminLabel img	{margin-right:10px;}
</style>

<div>
	<!--MENU CONTEXTUEL/D'EDITION  &&  TITRE-->
	<div class="lightboxTitle"><?= $curObj->lightboxMenu().$curObj->getLabel("full") ?></div>

	<!--IMAGE & DETAILS DE l'USER-->
	<div class="personProfileImg"><?= $curObj->tagProfileImg() ?></div>
	<div class="personVueFields"><?= $curObj->getFields("profile") ?></div>

	<!--GROUPES D'UTILISATEURS-->
	<?php if(!empty($userGroupList)){ ?>
		<div class="objField">
			<div><img src="app/img/user/userGroup.png"> <?= Txt::trad("USER_userGroups") ?></div>
			<div>
				<?php foreach($userGroupList as $tmpGroup) {echo ucfirst($tmpGroup->title);} ?>
			</div>
		</div>
	<?php } ?>

	<!--ADMIN GENERAL / D'ESPACE-->
	<?php if($curObj->isGeneralAdmin()){ ?>
		<div class="vAdminLabel"><hr><img src="app/img/user/userAdminGeneral.png"> <?= Txt::trad("USER_adminGeneral") ?></div>
	<?php }elseif($curObj->isSpaceAdmin()){ ?>
		<div class="vAdminLabel"><hr><img src="app/img/user/userAdminSpace.png"> <?= Txt::trad("USER_adminSpace") ?> &nbsp;<i><?= Ctrl::$curSpace->name ?></i></div>
	<?php } ?>
</div>