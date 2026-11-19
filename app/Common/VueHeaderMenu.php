<style>
.bodyWhite										{--headerMenuBorder:#e5e5e5; --headerMenuBackground:white;}
.bodyBlack										{--headerMenuBorder:#333333; --headerMenuBackground:#383838;}
#headerBar img[src*='menu.svg']					{margin-inline:5px;}
#headerBarLeft									{white-space:nowrap;}/*labels sur une ligne pour pas éclater l'affichage*/
#headerBarLeft>span								{max-width:200px; line-height:20px; overflow:hidden; text-overflow:ellipsis;}/*"ellipsis" pour le dépassement de texte*/
#menuContextMainDiv								{display:none; border-radius:20px; top:5px!important; left:5px!important;}/*conteneur du #menuContextMain*/
#menuContextMain								{display:table;}					/*Tableau du menu principal*/
#menuContextMain>div							{display:table-cell; padding:5px;}	/*Colonnes du menu principal*/
#menuContextMain>div:not(:last-child)			{border-right:solid 1px var(--headerMenuBorder);}
#menuContextMain .menuMainSubmenu				{padding-left:40px;}
#menuContextMain .menuMainSubmenu img			{max-width:20px; margin-right:10px;}
#menuContextMain .editButton					{visibility:hidden; float:right; cursor:pointer; margin-left:15px;}
#menuContextMain .menuLine:hover .editButton	{visibility:visible;}
#headerBarRight									{text-align:right;}
.vHeaderModule									{display:inline-block; min-width:70px; margin-block:3px; padding:3px 8px; border:1px solid transparent; text-align:center;}/*min-width: largeurs uniformes*/
.vHeaderModule img[src*='svg']					{width:24px; height:24px;}/*flat icons*/
.vHeaderModule img[src*='png']					{width:28px; height:28px;}/*old icons*/
.vHeaderModule label							{display:block; margin-top:3px;}
.vHeaderModuleCurrent, .vHeaderModule:hover		{background:var(--headerMenuBackground); transition:background 0.4s; border:1px solid var(--headerMenuBorder); border-radius:10px;}
.vHeaderModuleCurrent img[src*='svg']			{filter:var(--icon_blue);}
#headerBar>div:is(#menuContextMainDiv,#headerBarRightMobile)	{display:none;}/*surcharge "#headerBar>div"*/


/*****    RESPONSIVE LARGE*/
@media screen and (max-width:1199px){
	#headerBarLeft, #headerBarRightMobile		{font-size:1.1em; white-space:nowrap;}/*Label de l'espace et du module courant. "nowrap" pour laisser les labels sur une seule ligne et pas éclater l'affichage!*/
	#headerBarRightMobile						{display:table-cell!important; text-align:right;}/*surcharge "#headerBar>div"*/
	#headerBarRightMobile img[src*='menu.svg']	{transform:scaleX(-1);}/*inverse l'image*/
	#headerSpaceLabel							{max-width:40%; line-height:25px!important;}/*aligner les labels avec menu.svg*/
	#menuContextMain,#menuContextMain>div		{display:block; padding:0px;}
	#menuContextMain>div						{padding-block:15px;}
	#menuContextMain>div:not(:last-child)		{border-right:none; border-bottom:solid 2px var(--headerMenuBorder); padding-bottom:20px; margin-bottom:10px;}
	#menuContextMain hr							{display: none;}
	.vHeaderModule								{display:inline-block; width:49%; padding:5px; text-align:left;}/*Liste des modules: affichés dans "#menuMobileMain"*/
	.vHeaderModule label						{display:inline-block; margin-left:10px;}/*toujours affiché : cf. $moduleLabelDisplay*/
	.vHeaderModuleCurrent						{width:100%; margin-block:15px; text-align:center; border-inline:0px; border-block:1px solid var(--headerMenuBorder); border-radius:0px;}/*Module courant sur toute la largeur*/
	#headerUserLabel, #headerBarRight .vHeaderModule	{display:none!important;}/*Masque le labelk de l'user + La liste des modules: affichés dans "#menuMobileMain"*/
}
</style>



<div id="headerBarMarginShadow">&nbsp;</div>
<div id="headerBarContainer">
	<div id="headerBar">

		<!--HEADERBAR LEFT :  LOGO PRINCIPAL  +  LABEL DE L'USER  +  LABEL L'ESPACE  +  VALIDATION D'INSCRIPTION-->
		<div id="headerBarLeft" class="menuContextLaunch" for="menuContextMainDiv" <?= Txt::tooltip("mainMenu") ?> >
			<img src="app/img/menu.svg">
			<?php if(Ctrl::$curUser->isUser()){ ?>
				<span id="headerUserLabel"><?= Ctrl::$curUser->getLabel() ?>&nbsp;<img src="app/img/arrowLabel.svg"></span>
			<?php } ?>
			<span id="headerSpaceLabel">
				<?= Ctrl::$curSpace->name ?>
			</span>
			<?php if($userInscriptionValidate==true){ ?>
				<span><img src="app/img/arrowLabel.svg"><img src="app/img/subscribe.svg" <?= Txt::tooltip("userInscriptionValidateTooltip") ?> class="pulsate"></span>
			<?php } ?>
		</div>


		<!--MENU CONTEXT PRINCIPAL-->
		<div class="menuContext" id="menuContextMainDiv">
			<div id="menuContextMain">

				<!--COLONNE 1 : MENU PRINCIPAL-->
				<div>

					<!--RECHERCHER-->
					<div class="menuLine" onclick="lightboxOpen('?ctrl=misc&action=Search')" <?= Txt::tooltip("searchOnSpace") ?>>
						<div class="menuIcon"><img src="app/img/search.svg"></div>
						<div><?= Txt::trad("search") ?></div>
					</div>

					<!--CONNEXION (GUEST)-->
					<?php if(Ctrl::$curUser->isGuest()){ ?>
					<div class="menuLine">
						<div class="menuIcon"><img src="app/img/logout.svg"></div>
						<div><a href="?disconnect=1"><?= Txt::trad("connect") ?></a></div>
					</div>
					<?php } ?>

					<!--MENU USERS-->
					<?php if(Ctrl::$curUser->isUser()){ ?>
						<!--VALIDATION D'INSCRIPTION D'USERS-->
						<?php if($userInscriptionValidate==true){ ?>
						<div class="menuLine pulsate" onclick="lightboxOpen('?ctrl=user&action=UserInscriptionValidate');" <?= Txt::tooltip("userInscriptionValidateTooltip") ?>>
							<div class="menuIcon"><img src="app/img/subscribe.svg"></div>
							<div><?= Txt::trad("userInscriptionValidate") ?></div>
						</div>
						<?php } ?>
						<!--ENVOI D'INVITATION-->
						<?php if(Ctrl::$curUser->sendInvitationRight()){ ?>
						<div class="menuLine" onclick="lightboxOpen('?ctrl=user&action=SendInvitation');" <?= Txt::tooltip("USER_sendInvitationTooltip") ?>>
							<div class="menuIcon"><img src="app/img/mail.svg"></div>
							<div><?= Txt::trad("USER_sendInvitation") ?></div>
						</div>
						<?php } ?>
						<!--DOCUMENTATION-->
						<div class="menuLine" onclick="lightboxOpen('<?= File::docFile() ?>');">
							<div class="menuIcon"><img src="app/img/documentation.svg"></div>
							<div><?= Txt::trad("HEADER_documentation") ?></div>
						</div>
						<hr>
						<!--PROFIL UTILISATEUR-->
						<div class="menuLine" onclick="lightboxOpen('<?= Ctrl::$curUser->getUrl('edit') ?>');" <?= Txt::tooltip("USER_myProfileTooltip") ?>>
							<div class="menuIcon"><img src="app/img/edit.svg"></div>
							<div><?= Txt::trad("USER_myProfile") ?> &nbsp; <?= Ctrl::$curUser->profileImg(false,true) ?></div>
						</div>
						<!--CONFIG DU MESSENGER-->
						<?php if(MdlUser::agoraMessengerEnabled()){ ?>
						<div class="menuLine" onclick="lightboxOpen('?ctrl=user&action=UserEditMessenger&typeId=<?= Ctrl::$curUser->typeId ?>');">
							<div class="menuIcon"><img src="app/img/chat.svg"></div>
							<div><?= Txt::trad("USER_livecounterVisibility") ?></div>
						</div>
						<?php } ?>
						<!--DECONNEXION DE L'ESPACE-->
						<div class="menuLine" onclick="confirmRedir('index.php?disconnect=1','<?= Txt::trad('disconnectSpaceConfirm',true) ?>');">
							<div class="menuIcon"><img src="app/img/logout.svg"></div>
							<div><?= Txt::trad("disconnectSpace") ?></div>
						</div>
						<!--SWITCH D'ESPACE-->
						<?php if(Req::connectSpaceSwitch()){ ?>
						<div class="menuLine" onclick="confirmRedir('<?= Req::connectSpaceSwitchUrl() ?>','<?= addslashes($connectSpaceSwitchLabel) ?>');">
							<div class="menuIcon"><img src="app/img/switch.svg"></div>
							<div><?= Txt::trad("connectSpaceSwitch") ?></div>
						</div>
						<?php } ?>
						<!--OPTIONS ADMIN GENERAL-->
						<?php if(Ctrl::$curUser->isGeneralAdmin()){ ?>
							<hr>
							<!--GERER TOUS LES USERS-->
							<div class="menuLine" <?= Txt::tooltip("USER_allUsersToolip") ?>>
								<div class="menuIcon"><img src="app/img/users.svg"></div>
								<div><a href="index.php?ctrl=user&displayUsers=all"><?= Txt::trad("USER_allUsers") ?></a></div>
							</div>
							<!--PARAMETRAGE GENERAL-->
							<div class="menuLine">
								<div class="menuIcon"><img src="app/img/setting.svg"></div>
								<div><a href="index.php?ctrl=agora"><?= Txt::trad("AGORA_generalSettings") ?></a></div>
							</div>
						<?php } ?>
					<?php } ?>

				</div>

				<!--COLONNE 2 : ESPACES-->
				<div>
					<?php
					////	AFFICHE CHAQUE ESPACE
					foreach($spaceList as $tmpSpace){
						$spaceLabelClass=$spaceSwitchTrad=null;
						if($tmpSpace->isCurSpace())	{$spaceLabelClass='lineSelect';}
						else						{$spaceSwitchTrad=Txt::trad("HEADER_spaceSwitch")."<hr>";}
					?>
						<!--LABEL DE L'ESPACE-->
						<div class="menuLine <?= $spaceLabelClass ?>"  <?= Txt::tooltip($spaceSwitchTrad.$tmpSpace->description) ?>>
							<div class="menuIcon"><img src="app/img/space.svg"></div>
							<div><a href="?_idSpaceAccess=<?= $tmpSpace->_id ?>"><?= $tmpSpace->name ?></a></div>
						</div>
						<!--OPTIONS ADMIN D'ESPACE-->
						<?php if(Ctrl::$curSpace->_id==$tmpSpace->_id && $tmpSpace->editRight()){ ?>
							<!--GERER L'ESPACE-->
							<div class="menuLine menuMainSubmenu" onclick="lightboxOpen('<?= $tmpSpace->getUrl('edit') ?>')" <?= Txt::tooltip("SPACE_configInfo") ?>>
								<img src="app/img/edit.svg"><?= Txt::trad("SPACE_config") ?>
							</div>
							<!--USERS DE L'ESPACE-->
							<div class="menuLine menuMainSubmenu">
								<a href="index.php?ctrl=user&displayUsers=space"><img src="app/img/users.svg"><?= Txt::trad("USER_spaceUsers") ?></a>
							</div>
							<!--LOGS DE L'ESPACE-->
							<div class="menuLine menuMainSubmenu">
								<a href="index.php?ctrl=log"><img src="app/img/history.svg"><?= Txt::trad("LOG_MODULE_DESCRIPTION") ?></a>
							</div>
							<!--AFFICHAGE ADMIN-->
							<div class="menuLine menuMainSubmenu <?= empty($_SESSION['displayAdmin']) ? null : 'optionSelect' ?>" <?= Txt::tooltip("HEADER_displayAdminInfo") ?>>
								<a href="index.php?ctrl=<?= Req::$curCtrl ?>&displayAdmin=<?= empty($_SESSION['displayAdmin'])?'true':'false' ?>"><img src="app/img/eye.svg"><?= Txt::trad("HEADER_displayAdmin") ?></a>
							</div>
						<?php } ?>
					<?php } ?>
					<!--GERER TOUS LES ESPACES - OPTIONS ADMIN GENERAL-->
					<?php if(Ctrl::$curUser->isGeneralAdmin()){ ?>
						<hr>
						<div class="menuLine" <?= Txt::tooltip("SPACE_moduleTooltip") ?>>
							<div class="menuIcon"><img src="app/img/space.svg"></div>
							<div><a href="index.php?ctrl=space"><?= Txt::trad("HEADER_manageAllSpaces") ?></a></div>
						</div>
					<?php } ?>
				</div>

				<!--COLONNE 3 : SHORTCUTS-->
				<?php if(Ctrl::$curUser->isUser() && !empty($pluginsShortcut)){ ?>
				<div>
					<div class="menuLine">
						<div class="menuIcon"><img src="app/img/shortcut.svg"></div>
						<div><?= Txt::trad("HEADER_shortcuts") ?> :</div>
					</div>
					<?php foreach($pluginsShortcut as $tmpObj){ ?>
					<div class="menuLine menuMainSubmenu" <?= Txt::tooltip($tmpObj->pluginTooltip) ?> >
						<img src="app/img/<?= $tmpObj->pluginIcon ?>" onclick="<?= $tmpObj->pluginJsIcon ?>">
						<span onclick="<?= $tmpObj->pluginJsLabel ?>"><?= Txt::reduce($tmpObj->pluginLabel,50) ?></span>
						<?= $tmpObj->editButtom() ?>
					</div>
					<?php } ?>
				</div>
				<?php } ?>

			</div>
		</div>

		<!--MENU RIGHT : MODULES DISPONIBLES-->
		<div id="headerBarRight">
			<!--MODULES DE L'ESPACE-->
			<?php
			foreach($moduleList as $tmpMod){
				if($tmpMod["moduleName"]==static::moduleName)	{$curModClass='vHeaderModuleCurrent';  $curModLabel='<label>'.$tmpMod["label"].'</label>';}	//Module courant
				else											{$curModClass=null;}																		//Autres modules
				$modCurIcon='app/img/'.$tmpMod["moduleName"].'/'.(!empty(Ctrl::$agora->moduleIconFlat)?'icon.svg':'icon.png');								//Icone svg / Grande png (old)
			?>
				<div onclick="redir('<?= $tmpMod['url'] ?>')" class="vHeaderModule <?= $curModClass ?>" <?= Txt::tooltip($tmpMod["description"]) ?> >
					<img src="<?= $modCurIcon ?>"><label><?= $tmpMod["label"] ?></label>
				</div>
			<?php } ?>
			
			<!--MENU MESSENGER-->
			<?php if(Ctrl::$curUser->messengerEnabled()){ ?>
				<div onclick="messengerDisplay('all')" class="vHeaderModule" id="headerModuleMessenger" <?= Txt::tooltip("MESSENGER_MODULE_DESCRIPTION") ?> >
					<img src="app/img/chat.svg"><label><?= Txt::trad("MESSENGER_MODULE_NAME") ?></label>
				</div>
			<?php } ?>
		</div>

		<!--MENU MOBILE : LABEL DU MODULE COURANT-->
		<div id="headerBarRightMobile" class="menuContextLaunch" for="headerBarRight"><?= !empty($curModLabel) ? $curModLabel : 'Menu' ?>
			<img src="app/img/menu.svg">
		</div>


	</div>
</div>