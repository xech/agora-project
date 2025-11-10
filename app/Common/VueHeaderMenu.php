<script>
ready(function(){
	//// Margin-top du contenu de la page en fonction de #headerBar
	$("#pageFull, #pageCenter").css("margin-top", ($("#headerBar").outerHeight() + 30));
});
</script>

<style>
:root										{--headerMenuBorder:<?= Ctrl::$agora->skin=="black"?"#333":"#eee"?> solid 1px;}
#headerMenuLeft								{padding-left:75px; padding-right:20px; line-height:45px; white-space:nowrap;}/*"padding-left" pour afficher "#headerMainLogo" + "line-height" idem "#headerBar" + "nowrap" des labels sur une seule ligne (ne pas éclater l'affichage!)*/
#headerMobileModule							{display:none;}
#headerMainLogo								{content:url('app/img/logo.png'); position:absolute; left:0px; top:2px;}
#headerUserLabel, #headerSpaceLabel			{display:inline-block; max-width:250px; overflow:hidden; text-overflow:ellipsis;}/*"ellipsis" pour le dépassement de texte*/
.headerArrowBottom							{margin-left:5px; margin-right:2px;}
#headerMenuLeft img[src*=arrowRight]		{margin-left:5px;}
#headerBar>#menuMainContext					{display:none; padding:5px; box-shadow:0px 0px 15px black; border-radius:10px; top:5px!important; left:5px!important;}/*surcharge "#headerBar>div" de app.css*/
#menuMainTab								{display:table;}
#menuMainTab>div							{display:table-cell; padding:5px;}
#menuMainTab>div:not(:first-child)			{border-left:var(--headerMenuBorder);}/*Colonnes du menu principal*/
.menuMainArrow								{text-align:right!important;}/*icone "arrow*/
#menuMainTab .editButton					{visibility:hidden; float:right; cursor:pointer; margin-left:10px; height:20px; transform:scaleX(-1);}/*Image "edit" d'espace. "scaleX" : inverse l'image*/
#menuMainTab .menuLine:hover .editButton	{visibility:visible;}
.menuMainShortcut							{max-height:24px; margin-right:10px;}
#menuMainOmnispace							{border-top:var(--headerMenuBorder); text-align:right; padding-top:10px;}
.vHeaderModule								{display:inline-block; margin:0px; padding:4px; text-align:center; vertical-align:middle; border:1px solid transparent; border-radius:10px; cursor:pointer;}
.vHeaderModule label						{margin-left:5px; min-width:40px; display:<?= $moduleLabelDisplay==true?'inline-block':'none' ?>}/*'min-width' pour un affichage homogène*/
/*AFFICHAGE SMARTPHONE + TABLET*/
@media screen and (min-width:1025px) and (max-width:1350px){
	.vHeaderModule label					{margin-top:7px; display:<?= $moduleLabelDisplay==true?'block':'none' ?>}
}
/*AFFICHAGE SMARTPHONE + TABLET*/
@media screen and (max-width:1024px){
	#headerMenuLeft							{padding-left:40px; padding-right:10px;}/*"padding-left" en fonction du width du "logoXSmall.png"*/
	#headerMenuLeft, #headerMobileModule	{display:block; line-height:50px; font-size:1.1em!important; white-space:nowrap;}/*Label de l'espace et du module courant. "nowrap" pour laisser les labels sur une seule ligne et pas éclater l'affichage!*/
	#headerMainLogo							{content:url('app/img/logoXSmall.png'); left:0px; top:10px;}
	#headerMobileModule>img					{max-height:30px;}
	#headerUserLabel						{display:none;}
	#headerSpaceLabel						{max-width:180px; text-transform:capitalize;}
	#menuMainTab, #menuMainTab>div			{display:block; padding:0px; border:none!important;}/*cf. --headerMenuBorder*/
	#menuMainTab .editButton				{visibility:visible;}/*tjs visible*/
	#menuMobileMain .vHeaderModule			{display:inline-block; width:49%; margin:5px 0px; padding:5px; text-align:left; font-size:1.1rem;}/*Modules affichés dans "#menuMobileMain"*/
	#headerMenuRight .vHeaderModule			{display:none;}/*Modules masqués dans le header car affichés dans "#menuMobileMain"*/
	.vHeaderModule label					{display:inline-block; margin-left:10px;}/*toujours affiché : cf. $moduleLabelDisplay*/
}
</style>


<div id="headerBar">

	<!--MENU CONTEXT PRINCIPAL-->
	<div class="menuContext" id="menuMainContext">
		<!--TABLEAU 1-3 COLONNES-->
		<div id="menuMainTab">

			<!--COLONNE 1 : OPTIONS PRINCIPALES-->
			<?php if(Ctrl::$curUser->isUser()){ ?>
			<div>
				<!--INSCRIPTION D'USERS  +  RECHERCHER  +  ENVOI D'INVITATION  +  DOCUMENTATION-->
				<?php if($userInscriptionValidate==true){ ?><div class="menuLine pulsate" onclick="lightboxOpen('?ctrl=user&action=UserInscriptionValidate')" <?= Txt::tooltip("userInscriptionValidateTooltip") ?>><div class="menuIcon"><img src="app/img/user/subscribe.png"></div><div><?= Txt::trad("userInscriptionValidate") ?></div></div><?php } ?>
				<div class="menuLine" onclick="lightboxOpen('?ctrl=misc&action=Search')"><div class="menuIcon"><img src="app/img/search.png"></div><div><?= Txt::trad("searchOnSpace") ?></div></div>
				<?php if(Ctrl::$curUser->sendInvitationRight()){ ?><div class="menuLine" onclick="lightboxOpen('?ctrl=user&action=SendInvitation')" <?= Txt::tooltip("USER_sendInvitationTooltip") ?>><div class="menuIcon"><img src="app/img/mail.png"></div><div><?= Txt::trad("USER_sendInvitation") ?></div></div><?php } ?>
				<div class="menuLine" onclick="lightboxOpen('<?= File::docFile() ?>')"><div class="menuIcon"><img src="app/img/documentation.png"></div><div><?= Txt::trad("HEADER_documentation") ?></div></div>
				<hr>
				<!--MODIF DU PROFIL  +  MESSENGER DE L'USER  +  DECONNEXION DE L'ESPACE PRINCIPAL  +  SWITCH D'ESPACE-->
				<div class="menuLine" onclick="lightboxOpen('<?= Ctrl::$curUser->getUrl('edit') ?>')"><div class="menuIcon"><img src="app/img/edit.png"></div><div><?= Txt::trad("USER_myProfilEdit") ?> &nbsp; <?= Ctrl::$curUser->profileImg(false,true) ?></div></div>
				<?php if(MdlUser::agoraMessengerEnabled()){ ?><div class="menuLine" onclick="lightboxOpen('?ctrl=user&action=UserEditMessenger&typeId=<?= Ctrl::$curUser->_typeId ?>')"><div class="menuIcon"><img src="app/img/messenger.png"></div><div><?= Txt::trad("USER_livecounterVisibility") ?></div></div><?php } ?>
				<div class="menuLine" onclick="confirmRedir('?disconnect=1','<?= Txt::trad('disconnectSpaceConfirm',true) ?>')"><div class="menuIcon"><img src="app/img/logout.png"></div><div><?= Txt::trad("disconnectSpace") ?></div></div>
				<?php if(Req::isSpaceSwitch()){ ?><div class="menuLine" onclick="confirmRedir('<?= Req::connectSpaceSwitchUrl() ?>','<?= Txt::trad('connectSpaceSwitch',true) ?>')"><div class="menuIcon"><img src="app/img/switch.png"></div><div><?= Txt::trad("connectSpaceSwitch") ?></div></div><?php } ?>
				<!--ADMIN GENERAL :  EDIT TOUS LES USERS  +  PARAMETRAGE GENERAL  +  HISTORIQUE DES EVENEMENTS (LOGS)-->
				<?php if(Ctrl::$curUser->isGeneralAdmin()){ ?>
				<hr>
				<div class="menuLine" onclick="redir('?ctrl=log')"><div class="menuIcon"><img src="app/img/log.png"></div><div><?= Txt::trad("LOG_MODULE_DESCRIPTION") ?></div></div>
				<div class="menuLine" onclick="redir('?ctrl=user&displayUsers=all')"><div class="menuIcon"><img src="app/img/user/iconSmall.png"></div><div><?= Txt::trad("USER_allUsers") ?></div></div>
				<div class="menuLine" onclick="redir('?ctrl=agora')"><div class="menuIcon"><img src="app/img/settingsGeneral.png"></div><div><?= Txt::trad("AGORA_generalSettings") ?></div></div>
				<?php } ?>
			</div>
			<?php } ?>

			<!--COLONNE 2 : GUEST || LISTE / GESTION DES ESPACES-->
			<?php if(Ctrl::$curUser->isGuest() || $spaceListMenu==true || Ctrl::$curUser->isSpaceAdmin()){ ?>
			<div>
				<!--CONNEXION GUEST-->
				<?php if(Ctrl::$curUser->isGuest()){ ?>
					<div class="menuLine" onclick="redir('?disconnect=1')"><div class="menuIcon"><img src="app/img/logout.png"></div><div><?= Txt::trad("connect") ?></div></div>
				<?php } ?>
				<!--LISTE DES ESPACES-->
				<?php if($spaceListMenu==true){ ?>
					<div class="menuLine"><div class="menuIcon"><img src="app/img/space.png"></div><div><?= Txt::trad("HEADER_displaySpace") ?> :</div></div>
					<?php foreach($spaceList as $tmpSpace){ ?>
					<div class="menuLine <?= $tmpSpace->isCurSpace()?'linkSelect':null ?>">
						<div class="menuIcon menuMainArrow"><img src="app/img/arrowRightSmall.png"></div>
						<div>
							<span onclick="redir('?_idSpaceAccess=<?= $tmpSpace->_id ?>')" <?= Txt::tooltip($tmpSpace->description) ?> ><?= $tmpSpace->name ?></span>
							<?php if($tmpSpace->editRight()){ ?><img src="app/img/edit.png" class="editButton" onclick="lightboxOpen('<?= $tmpSpace->getUrl('edit') ?>')" <?= Txt::tooltip("SPACE_config") ?> ><?php } ?>
						</div>
					</div>
					<?php } ?>
				<?php } ?>
				<!--GESTION DES ESPACES  +  AFFICHAGE "ADMINISTRATEUR"-->
				<?php if(Ctrl::$curUser->isSpaceAdmin()){ ?>
					<?= $spaceListMenu==true ? '<hr>' : null ?>
					<div class="menuLine" onclick="lightboxOpen('<?= Ctrl::$curSpace->getUrl('edit') ?>')"><div class="menuIcon"><img src="app/img/settingsCurSpace.png"></div><div><?= Txt::trad("SPACE_config") ?> <i><?= Txt::reduce(Ctrl::$curSpace->name,35) ?></i></div></div>
					<?php if(Ctrl::$curUser->isGeneralAdmin()){ ?><div class="menuLine" onclick="redir('?ctrl=space')" <?= Txt::tooltip("SPACE_moduleTooltip") ?> ><div class="menuIcon"><img src="app/img/settingsSpaces.png"></div><div><?= Txt::trad("SPACE_manageAllSpaces") ?></div></div><?php } ?>
					<div class="menuLine <?= empty($_SESSION['displayAdmin'])?'option':'optionSelect' ?>" onclick="redir('?ctrl=<?= Req::$curCtrl ?>&displayAdmin=<?= empty($_SESSION['displayAdmin'])?'true':'false' ?>')" <?= Txt::tooltip("HEADER_displayAdminInfo") ?>><div class="menuIcon"><img src="app/img/eye.png"></div><div><?= Txt::trad("HEADER_displayAdmin") ?></div></div>
				<?php } ?>
			</div>
			<?php } ?>

			<!--COLONNE 3 : SHORTCUTS-->
			<?php if(!empty($pluginsShortcut) && Ctrl::$curUser->isUser()){ ?>
			<div>
				<div class="menuLine"><div class="menuIcon"><img src="app/img/shortcut.png"></div><div><?= Txt::trad("HEADER_shortcuts") ?> :</div></div>
				<?php foreach($pluginsShortcut as $tmpObj){ ?>
					<div class="menuLine" <?= Txt::tooltip($tmpObj->pluginTooltip) ?> >
						<div class="menuIcon menuMainArrow"><img src="app/img/arrowRightSmall.png"></div>
						<div>
							<img src="app/img/<?= $tmpObj->pluginIcon ?>" onclick="<?= $tmpObj->pluginJsIcon ?>" class="menuMainShortcut">
							<span onclick="<?= $tmpObj->pluginJsLabel ?>"><?= $tmpObj->pluginLabel ?></span><?= $tmpObj->editButtom() ?>
						</div>
					</div>
				<?php } ?>
			</div>
			<?php } ?>
		</div>

		<!--LOGO OMNISPACE-->
		<div id="menuMainOmnispace" onclick="window.open('<?= OMNISPACE_URL_PUBLIC ?>')" <?= Txt::tooltip(OMNISPACE_URL_LABEL) ?> ><img src="app/img/logoLabel.png"></div>
	</div>


	<!--MENU LEFT : LOGO PRINCIPAL  &  LABEL DE L'USER  &  LABEL L'ESPACE COURANT  &  ICONE DE VALIDATION D'INSCRIPTION-->
	<div id="headerMenuLeft" class="menuLauncher" for="menuMainContext" <?= Txt::tooltip("mainMenu") ?> >
		<img src="app/img/logo.png" id="headerMainLogo">
		<?php if(Ctrl::$curUser->isUser()){ ?><div id="headerUserLabel"><?= Ctrl::$curUser->getLabel("firstName") ?><img src="app/img/arrowRight.png"></div><?php } ?>
		<div id="headerSpaceLabel"><?= Ctrl::$curSpace->name ?></div><img src="app/img/arrowBottom.png" class="headerArrowBottom">
		<?php if($userInscriptionValidate==true){ ?><img src="app/img/user/subscribe.png" class="pulsate" <?= Txt::tooltip("userInscriptionValidateTooltip") ?>><?php } ?>
	</div>

	<!--MENU RIGHT : MODULES DISPONIBLES-->
	<div>
		<div id="headerMenuRight">
			<!--MODULES DE L'ESPACE-->
			<?php
			foreach($moduleList as $tmpMod){
				$moduleCurrent=($tmpMod["moduleName"]==static::moduleName);															//Module courant ?
				$modCurIcon='app/img/'.$tmpMod["moduleName"].'/';																	//Path des images du module
				$modCurIcon.=($moduleLabelDisplay==true)  ?  'iconSmall.png'  :  'icon.png';										//Grande icone si on masque le label du module
				if($moduleCurrent==true)  {$modCurMobileIcon='<img src="'.$modCurIcon.'"> <label>'.$tmpMod["label"].'</label>';}	//Icone du menu mobile
			?>
				<div onclick="redir('<?= $tmpMod['url'] ?>')" class="vHeaderModule <?= $moduleCurrent==true?'vHeaderModuleCurrent':null ?>" <?= Txt::tooltip($tmpMod["description"]) ?> >
					<img src="<?= $modCurIcon ?>"><label><?= $tmpMod["label"] ?></label>
				</div>
			<?php } ?>
			
			<!--MENU MESSENGER-->
			<?php if(Ctrl::$curUser->messengerEnabled()){ ?>
				<div onclick="messengerDisplay('all')" class="vHeaderModule" id="headerModuleMessenger" <?= Txt::tooltip("MESSENGER_MODULE_DESCRIPTION") ?> >
					<img src="app/img/<?= $moduleLabelDisplay==true ? 'messengerSmall.png' : 'messenger.png' ?>"><label><?= Txt::trad("MESSENGER_MODULE_NAME") ?></label>
				</div>
			<?php } ?>
		</div>

		<!--MENU MOBILE : LABEL DU MODULE COURANT-->
		<div id="headerMobileModule" class="menuLauncher" for="headerMenuRight"><?= !empty($modCurMobileIcon) ? $modCurMobileIcon : 'Menu' ?><img src="app/img/arrowBottom.png" class="headerArrowBottom"></div>
	</div>
</div>