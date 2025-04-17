<style>
#displayUsersSelect		{margin:10px; height:40px; border-radius:5px; font-weight:bold; cursor:pointer;}
.vDisplayUsersSelectAll	{background-color:#059!important; color:white!important}
#menuAlphabet>a			{padding:8px;}
.vAdminIcon				{margin-left:5px;}
</style>

<div id="pageFull">
	<div id="pageMenu">
		<?= MdlUser::menuSelect() ?>
		<div class="miscContainer">
			<!--ADD USER /  INVITATIONS  /  IMPORTER DES USERS  /  ENVOI DES CREDENTIALS  /  AFFECT USER -->
			<?php
			$affectNewUsers=(Ctrl::$curUser->isSpaceAdmin() && Ctrl::$curSpace->allUsersAffected()==false);
			if(Ctrl::$curUser->isSpaceAdmin())			{echo '<div class="menuLine" onclick="lightboxOpen(\''.MdlUser::getUrlNew().'\')" '.Txt::tooltip($_SESSION["displayUsers"]=='all'?'USER_addUserSite':'USER_addUserSpace').'><div class="menuIcon"><img src="app/img/plus.png"></div><div>'.Txt::trad("USER_addUser").'</div></div>';}
			if(Ctrl::$curUser->sendInvitationRight())	{echo '<div class="menuLine" onclick="lightboxOpen(\'?ctrl=user&action=SendInvitation\')" '.Txt::tooltip("USER_sendInvitationTooltip").'><div class="menuIcon"><img src="app/img/mail.png"></div><div>'.Txt::trad("USER_sendInvitation").'</div></div>';}
			if(Ctrl::$curUser->isGeneralAdmin())		{echo '<div class="menuLine" onclick="lightboxOpen(\'?ctrl=user&action=ResetPasswordSendMailUsers\')" '.Txt::tooltip("USER_sendCoordsTooltip").'><div class="menuIcon"><img src="app/img/user/connection.png"></div><div>'.Txt::trad("USER_sendCoords").'</div></div>';}
			if(Ctrl::$curUser->isSpaceAdmin())			{echo '<div class="menuLine" onclick="lightboxOpen(\'?ctrl=user&action=EditPersonsImportExport\')"><div class="menuIcon"><img src="app/img/dataImportExport.png"></div><div>'.Txt::trad("importExport_user").'</div></div>';}
			if($affectNewUsers==true)  					{echo '<div class="menuLine" onclick="lightboxOpen(\'?ctrl=user&action=AffectUsers\')" '.Txt::tooltip("USER_addExistUserTitle").'><div class="menuIcon"><img src="app/img/plusSmall.png"></div><div>'.Txt::trad("USER_addExistUser").'</div></div>';}
			?>

			<!--GROUPES D'UTILISATEURS-->
			<?php if($_SESSION["displayUsers"]=="space" && (!empty($userGroups) || MdlUserGroup::addRight())){ ?>
				<hr><div <?= MdlUserGroup::addRight() ?  Txt::tooltip("USER_spaceGroupsEdit").' onclick="lightboxOpen(\'?ctrl=user&action=UserGroupEdit\')"'  :  null ?>>
					<div class="menuLine"><div class="menuIcon"><img src='app/img/user/userGroup.png'></div><div><?= Txt::trad("USER_spaceGroups") ?></div></div>
					<?php foreach($userGroups as $tmpGroup){ ?>
						<div class="menuLine"><div class="menuIcon"></div><div><img src='app/img/arrowRightBig.png'> <?= ucfirst($tmpGroup->title) ?></div></div>
					<?php } ?>
				</div>
			<?php } ?>

			<!--"USERS DE L'ESPACE" / "TOUS LES USERS"-->
			<?php if($menuDisplayUsers==true){ ?>
				<hr>
				<select name="displayUsers" id="displayUsersSelect" <?= Txt::tooltip("USER_spaceOrAllUsersTooltip").($_SESSION["displayUsers"]=="all"?'class="vDisplayUsersSelectAll"':null) ?> onChange="redir('?ctrl=user&displayUsers='+this.value)">
					<option value="space"><?= Txt::trad("USER_spaceUsers") ?></option>
					<option value="all" <?= $_SESSION["displayUsers"]=="all"?"selected":null ?> ><?= Txt::trad("USER_allUsers") ?></option>
				</select>
			<?php } ?>

			<!--TYPE D'AFFICHAGE / TRI D'AFFICHAGE / FILTRAGE ALPHABET / NB D'UTILISATEURS-->
			<hr>
			<?php
			//// Menus type et tri d'affichage
			echo MdlUser::menuDisplayMode().MdlUser::menuSort();
			//// Menu "alphabet"
			$curAlphabet=Req::isParam("alphabet")  ?  " : ".Req::param("alphabet")  :  null;
			$menuAlphabet='<a href="?ctrl=user" '.(!Req::isParam("alphabet")?'class="linkSelect"':null).'>'.Txt::trad("displayAll").'</a>';
			foreach($alphabetList as $tmpLetter)  {$menuAlphabet.='<a href="?ctrl=user&alphabet='.$tmpLetter.'" '.(Req::param("alphabet")==$tmpLetter?'class="linkSelect"':null).'>'.$tmpLetter.'</a>';}
			?>
			<div class="menuLine">
				<div class="menuIcon"><img src="app/img/alphabet.png"></div>
				<div><div class="menuLauncher" for="menuAlphabet"><?= Txt::trad("alphabetFilter").$curAlphabet ?></div><div id="menuAlphabet" class="menuContext"><?= $menuAlphabet ?></div></div>
			</div>
			<div class="menuLine" <?= Ctrl::$curSpace->allUsersAffected() ? Txt::tooltip("USER_allUsersOnSpace") : null ?> >
				<div class="menuIcon"><img src="app/img/info.png"></div>
				<div><?= $usersTotalNb." ".Txt::trad("USER_users") ?></div>
			</div>
		</div>
	</div>

	<div id="pageContent" class="<?= MdlUser::getDisplayMode()=="line"?"objLines":"objBlocks" ?>">
		<?php
		////	LISTE DES USERS
		foreach($displayedUsers as $tmpUser){
			if($tmpUser->isGeneralAdmin())		{$adminIcon='<img src="app/img/user/userAdminGeneral.png" '.Txt::tooltip("USER_adminGeneral").' class="vAdminIcon">';}	//Admin general
			elseif($tmpUser->isSpaceAdmin())	{$adminIcon='<img src="app/img/user/userAdminSpace.png" '.Txt::tooltip("USER_adminSpace").' class="vAdminIcon">';}		//Admin space
			else								{$adminIcon=null;}
			echo $tmpUser->objContainerMenu("objPerson").
				'<div class="objContainerScroll">
					<div class="objContent">
						<div class="objIcon">'.$tmpUser->profileImg(true,false).'</div>
						<div class="objLabel" onclick="'.$tmpUser->openVue().'">
							<div class="objPersonDetail">'.$tmpUser->getLabel("full").$adminIcon.'</div>
							<div class="objPersonDetails">'.$tmpUser->getFields(MdlUser::getDisplayMode()).'</div>
						</div>
					</div>
				</div>
			</div>';
		}
		////	AUCUN CONTENU  &&  MENU DE PAGINATION
		if(empty($displayedUsers))	{echo '<div class="miscContainer emptyContainer">'.Txt::trad("USER_noUser").'</div>';}
		echo MdlUser::menuPagination($usersTotalNb,"alphabet");
		?>
	</div>
</div>