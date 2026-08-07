<style>
#displayUsersSelect		{margin:10px; height:40px; border-radius:var(--radius-field); font-weight:bold; cursor:pointer;}
#displayUsersSelect:has(option[value='all']:checked)	{background-color:#059; color:white!important}
#menuAlphabet a			{display:inline-block; padding:8px; font-weight:bold;}
.vAdminIcon				{margin-left:5px;}
</style>

<div id="pageFull">
	<div id="pageMenu">
		<?= MdlUser::menuSelect() ?>
		<div class="miscContent">

			<!--"USERS DE L'ESPACE" / "TOUS LES USERS"-->
			<?php if($menuDisplayUsers==true){ ?>
				<select name="displayUsers" id="displayUsersSelect" onchange="redir('?ctrl=user&displayUsers='+this.value)" <?= Txt::tooltip("USER_spaceOrAllUsersTooltip") ?>>
					<option value="space"><?= Txt::trad("USER_spaceUsers") ?></option>
					<option value="all" <?= $_SESSION["displayUsers"]=="all"?"selected":null ?> ><?= Txt::trad("USER_allUsers") ?></option>
				</select>
				<hr>
			<?php } ?>

			<!--ADD USER /  INVITATIONS  /  IMPORTER DES USERS  /  ENVOI DES CREDENTIALS  /  AFFECT USER -->
			<?php
			$affectNewUsers=(Ctrl::$curUser->isSpaceAdmin() && Ctrl::$curSpace->allUsersAffected()==false);
			if(Ctrl::$curUser->isSpaceAdmin())			{echo '<div class="menuLine forMobileAddElem" onclick="lightboxOpen(\''.MdlUser::getUrlNew().'\')" '.Txt::tooltip($_SESSION["displayUsers"]=='all'?'USER_addUserSite':'USER_addUserSpace').'><div class="menuIcon"><img src="app/img/plus.png"></div><div>'.Txt::trad("USER_addUser").'</div></div>';}
			if(Ctrl::$curUser->sendInvitationRight())	{echo '<div class="menuLine" onclick="lightboxOpen(\'?ctrl=user&action=SendInvitation\')" '.Txt::tooltip("USER_sendInvitationTooltip").'><div class="menuIcon"><img src="app/img/mail.png"></div><div>'.Txt::trad("USER_sendInvitation").'</div></div>';}
			if(Ctrl::$curUser->isGeneralAdmin())		{echo '<div class="menuLine" onclick="lightboxOpen(\'?ctrl=user&action=ResetPasswordUsers\')" '.Txt::tooltip("USER_sendCoordsTooltip").'><div class="menuIcon"><img src="app/img/user/connection.png"></div><div>'.Txt::trad("USER_sendCoords").'</div></div>';}
			if(Ctrl::$curUser->isSpaceAdmin())			{echo '<div class="menuLine" onclick="lightboxOpen(\'?ctrl=user&action=vueImportExport\')"><div class="menuIcon"><img src="app/img/dataImportExport.png"></div><div>'.Txt::trad("importExport_user").'</div></div>';}
			if($affectNewUsers==true)  					{echo '<div class="menuLine" onclick="lightboxOpen(\'?ctrl=user&action=AffectUsers\')" '.Txt::tooltip("USER_addExistUserTitle").'><div class="menuIcon"><img src="app/img/plusSmall.png"></div><div>'.Txt::trad("USER_addExistUser").'</div></div>';}
			?>

			<!--GROUPES D'UTILISATEURS-->
			<?php if($_SESSION["displayUsers"]=="space" && (!empty($userGroups) || MdlUserGroup::addRight())){ ?>
				<hr>
				<div>
					<!--TITRE DU MENU-->
					<div class="menuLine">
						<div class="menuIcon"><img src='app/img/user/userGroup.png'></div>
						<div><?= Txt::trad("USER_spaceGroups") ?></div>
					</div>
					<!--LISTE DES GROUPES-->
					<?php foreach($userGroups as $tmpGroup){ ?>
						<div class="menuLine cursorHelp" <?= Txt::tooltip($tmpGroup->usersLabel) ?>>
							<div class="menuIcon">&nbsp;</div>
							<div><img src='app/img/arrowRightSmall.png'> <?= ucfirst($tmpGroup->title) ?></div>
						</div>
					<?php } ?>
					<!--EDITION DES GROUPES-->
					<?php if(MdlUserGroup::addRight()){ ?>
						<div class="menuLine" onclick="lightboxOpen('?ctrl=user&action=VueEditUserGroup')" <?= Txt::tooltip("USER_spaceGroupsEditBis") ?> >
							<div class="menuIcon"><img src="app/img/edit.png"></div>
							<div><?= Txt::trad("USER_spaceGroupsEdit") ?></div>
						</div>
					<?php } ?>
				</div>
			<?php } ?>

			
			<hr>
			<!--MENU D'AFFICHAGE  &  MENU DE TRI-->
			<?= MdlUser::menuDisplayMode().MdlUser::menuSort() ?>
			<!--FILTRAGE ALPHABET-->
			<div class="menuLine <?= Req::isParam("alphabet")?'optionSelect':null ?>">
				<div class="menuIcon"><img src="app/img/alphabet.png"></div>
				<div>
					<div class="menuContextLaunch" for="menuAlphabet"><?= Txt::trad("alphabetFilter").(Req::isParam("alphabet")?'<img src="app/img/arrowRight.png"><b>'.Req::param("alphabet").'</b>':null) ?></div>
					<div id="menuAlphabet" class="menuContext">
						<a <?= Req::isParam("alphabet")?'':'class="linkSelect"' ?> href="?ctrl=user"><?= Txt::trad("displayAll") ?></a>
						<?php foreach($alphabetList as $letter){ ?>
							<a <?= Req::param("alphabet")==$letter?'class="linkSelect"':null ?> href="?ctrl=user&alphabet=<?= $letter ?>"><?= $letter ?></a>
						<?php } ?>
					</div>
				</div>
			</div>
			<!--NB D'UTILISATEURS-->
			<div class="menuLine" <?= Ctrl::$curSpace->allUsersAffected() ? Txt::tooltip("USER_allUsersOnSpace") : null ?> >
				<div class="menuIcon"><img src="app/img/info.png"></div>
				<div><?= $usersTotalNb." ".Txt::trad("USER_users") ?></div>
			</div>
		</div>
	</div>

	<div id="pageContent" class="<?= MdlUser::getDisplayMode()=="line"?"objLines":"objBlocks" ?>">
		<!--LISTE DES USERS-->
		<?php foreach($displayedUsers as $tmpUser){ ?>
			<!--BLOCK DE L'USER-->
			<?= $tmpUser->objContentDiv("objPerson") ?>
				<div class="objContentScroll">
					<div class="objContentTab">
						<div class="objIcon"><?= $tmpUser->tagProfileImg(true,false) ?></div>
						<div class="objLabel" onclick="<?= $tmpUser->lightboxVue() ?>">
							<div class="personLabel">
								<?= $tmpUser->getLabel("full") ?>
								<!--ADMIN GENERAL || ADMIN SPACE-->
								<?php if($tmpUser->isGeneralAdmin()){ ?>
									<img src="app/img/user/userAdminGeneral.png" <?= Txt::tooltip("USER_adminGeneral") ?> class="vAdminIcon">
								<?php }elseif($tmpUser->isSpaceAdmin()){ ?>
									<img src="app/img/user/userAdminSpace.png" <?= Txt::tooltip("USER_adminSpace") ?> class="vAdminIcon">
								<?php } ?>
							</div>
							<?= $tmpUser->getFields("index") ?>
						</div>
					</div>
				</div>
			</div>
		<?php } ?>

		<!--AUCUN CONTENU -->
		<?php if(empty($displayedUsers)){ ?>
			<div class="miscContent emptyContent"><?= Txt::trad("USER_noUser") ?></div>
		<?php } ?>

		<!--MENU DE PAGINATION-->
		<?= MdlUser::menuPagination($usersTotalNb,"alphabet") ?>
	</div>
</div>