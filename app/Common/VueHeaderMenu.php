<style>
:root										{--headerMenuBorder:<?= Ctrl::$agora->skin=="black"?"#333":"#eee"?> solid 1px;}
#headerBarLeft								{padding-inline:75px 20px; white-space:nowrap;}/*padding > cf #headerMainLogo  +  nowrap > labels sur une ligne pour pas éclater l'affichage*/
#headerMobileModule							{display:none;}
#headerMainLogo								{position:absolute; left:3px; top:5px;}
#headerBarLeft>div							{display:inline-block; max-width:250px; overflow:hidden; text-overflow:ellipsis;}/*"ellipsis" pour le dépassement de texte*/
#headerBar>div#menuMainContext				{display:none; padding:5px; border-radius:20px; top:5px!important; left:5px!important;}/*surcharge "#headerBar>div"*/
#menuMainTab								{display:table;}
#menuMainTab>div							{display:table-cell; padding:5px;}
#menuMainTab>div:not(:first-child)			{border-left:var(--headerMenuBorder);}/*Colonnes du menu principal*/
.menuMainAdmin								{padding-left:40px;}
.menuMainAdmin img							{max-width:20px; margin-right:10px;}
.menuMainShortcut							{max-height:24px; margin-right:10px;}
#menuMainTab .editButton					{visibility:hidden; float:right; cursor:pointer; margin-left:10px; height:20px; transform:scaleX(-1);}/*Image "edit" d'espace. "scaleX" : inverse l'image*/
#menuMainTab .menuLine:hover .editButton	{visibility:visible;}
#menuOmnispace								{display:table; width:100%; border-top:var(--headerMenuBorder);}
#menuOmnispace>div							{display:table-cell; vertical-align:middle; padding:5px; padding-top:10px;}
#menuOmnispace>div:last-child				{text-align:right;}
.vHeaderModule								{display:inline-block; margin:0px; padding:7px; border:1px solid transparent; border-radius:20px; text-align:center; vertical-align:middle; cursor:pointer;}
.vHeaderModule label						{margin-left:5px; min-width:40px; display:<?= $moduleLabelDisplay==true?'inline-block':'none' ?>}/*'min-width' pour un affichage homogène*/
.vHeaderModuleCurrent						{font-weight:bold; padding:7px 12px}
.vHeaderModuleCurrent,.vHeaderModule:hover	{background:<?= Ctrl::$agora->skin=="black"?"#383838":"white"?>; border:solid 1px <?= Ctrl::$agora->skin=="black"?"#555":"#d8d8d8"?>!important;}
/*** RESPONSIVE REDUIT DU HEADER*/
@media screen and (min-width:1200px) and (max-width:1430px){
	.vHeaderModule							{padding:4px 12px 5px 12px;}
	.vHeaderModule label					{margin:0px; margin-top:4px; display:<?= $moduleLabelDisplay==true?'block':'none' ?>}
}
/*** RESPONSIVE TABLET-SMARTPHONE*/
@media screen and (max-width:1199px){
	#headerBarLeft							{padding-inline:10px;}
	#headerBarLeft, #headerMobileModule		{display:block; line-height:50px; font-size:1.1em!important; white-space:nowrap;}/*Label de l'espace et du module courant. "nowrap" pour laisser les labels sur une seule ligne et pas éclater l'affichage!*/
	#headerMainLogo, #headerUserLabel		{display:none!important;}
	#headerMobileModule>img					{max-height:30px;}
	#headerSpaceLabel						{max-width:180px; text-transform:capitalize;}
	#menuMainTab, #menuMainTab>div			{display:block; padding:0px; border-left:none!important;}/*cf. --headerMenuBorder*/
	#menuMainTab>div						{padding-block:15px;}
	#menuMainTab>div:not(:first-child)		{border-top:var(--headerMenuBorder);}
	#menuMainTab .editButton				{visibility:visible;}/*tjs visible*/
	#menuOmnispace>div:last-child			{display:none;}
	.vHeaderModule							{display:inline-block; width:49%; padding:6px 2px; text-align:left;}/*Modules affichés dans "#menuMobileMain"*/
	.vHeaderModuleCurrent					{width:99%; text-align:center; margin-block:15px;}/*Module courant sur toute la largeur*/
	#headerBarRight .vHeaderModule			{display:none;}/*Modules masqués dans le header car affichés dans "#menuMobileMain"*/
	.vHeaderModule label					{display:inline-block; margin-left:10px;}/*toujours affiché : cf. $moduleLabelDisplay*/
}
</style>


<div id="headerBarContainer">
	<div id="headerBar">

		<!--HEADERBAR LEFT :  LOGO PRINCIPAL  +  LABEL DE L'USER  +  LABEL L'ESPACE  +  VALIDATION D'INSCRIPTION-->
		<div id="headerBarLeft" class="menuContextLaunch" for="menuMainContext" <?= Txt::tooltip("mainMenu") ?> >
			<img src="app/img/logoHeader.png" id="headerMainLogo">
			<?php if(Ctrl::$curUser->isUser()){ ?><div id="headerUserLabel"><?= Ctrl::$curUser->getLabel("firstName") ?><img src="app/img/arrowRight.png"></div><?php } ?>
			<div id="headerSpaceLabel"><?= Ctrl::$curSpace->name ?> <img src="app/img/menuSmall.png"></div>
			<?php if($userInscriptionValidate==true){ ?><img src="app/img/user/subscribe.png" class="pulsate" <?= Txt::tooltip("userInscriptionValidateTooltip") ?>><?php } ?>
		</div>

		<!--MENU CONTEXT PRINCIPAL-->
		<div class="menuContext" id="menuMainContext">
			<div id="menuMainTab">

				<!--COLONNE 1 : MENU PRINCIPAL-->
				<div>

					<!--RECHERCHER (USER + GUEST)-->
					<div class="menuLine" onclick="lightboxOpen('?ctrl=misc&action=Search')">
						<div class="menuIcon"><img src="app/img/search.png"></div>
						<div><?= Txt::trad("searchOnSpace") ?></div>
					</div>

					<!--MENU GUESTS : CONNEXION-->
					<?php if(Ctrl::$curUser->isGuest()){ ?>
					<div class="menuLine">
						<div class="menuIcon"><img src="app/img/logout.png"></div>
						<div><a href="?disconnect=1"><?= Txt::trad("connect") ?></a></div>
					</div>
					<?php } ?>

					<!--MENU USERS-->
					<?php if(Ctrl::$curUser->isUser()){ ?>
						<!--INSCRIPTION D'USERS-->
						<?php if($userInscriptionValidate==true){ ?>
						<div class="menuLine pulsate" onclick="lightboxOpen('?ctrl=user&action=UserInscriptionValidate')" <?= Txt::tooltip("userInscriptionValidateTooltip") ?>>
							<div class="menuIcon"><img src="app/img/user/subscribe.png"></div>
							<div><?= Txt::trad("userInscriptionValidate") ?></div>
						</div>
						<?php } ?>
						<!--ENVOI D'INVITATION-->
						<?php if(Ctrl::$curUser->sendInvitationRight()){ ?>
						<div class="menuLine" onclick="lightboxOpen('?ctrl=user&action=SendInvitation')" <?= Txt::tooltip("USER_sendInvitationTooltip") ?>>
							<div class="menuIcon"><img src="app/img/mailBig.png"></div>
							<div><?= Txt::trad("USER_sendInvitation") ?></div>
						</div>
						<?php } ?>
						<!--DOCUMENTATION-->
						<div class="menuLine" onclick="lightboxOpen('<?= File::docFile() ?>')">
							<div class="menuIcon"><img src="app/img/documentation.png"></div>
							<div><?= Txt::trad("HEADER_documentation") ?></div>
						</div>
						<hr>
						<!--EDITION DU PROFIL UTILISATEUR-->
						<div class="menuLine" onclick="lightboxOpen('<?= Ctrl::$curUser->getUrl('edit') ?>')">
							<div class="menuIcon"><img src="app/img/edit.png"></div>
							<div><?= Txt::trad("USER_myProfilEdit") ?> &nbsp; <?= Ctrl::$curUser->tagProfileImg(false,true) ?></div>
						</div>
						<!--CONFIG DU MESSENGER-->
						<?php if(MdlUser::agoraMessengerEnabled()){ ?>
						<div class="menuLine" onclick="lightboxOpen('?ctrl=user&action=UserEditMessenger&typeId=<?= Ctrl::$curUser->typeId ?>')">
							<div class="menuIcon"><img src="app/img/messengerSmall.png"></div>
							<div><?= Txt::trad("USER_livecounterVisibility") ?></div>
						</div>
						<?php } ?>
						<!--DECONNEXION DE L'ESPACE-->
						<div class="menuLine" onclick="confirmRedir('?disconnect=1','<?= Txt::trad('disconnectSpaceConfirm',true) ?>')">
							<div class="menuIcon"><img src="app/img/logout.png"></div>
							<div><?= Txt::trad("disconnectSpace") ?></div>
						</div>
						<!--OPTIONS ADMIN GENERAL-->
						<?php if(Ctrl::$curUser->isGeneralAdmin()){ ?>
							<hr>
							<!--GERER TOUS LES USERS-->
							<div class="menuLine">
								<div class="menuIcon"><img src="app/img/user/iconSmall.png"></div>
								<div><a href="?ctrl=user&displayUsers=all"><?= Txt::trad("USER_allUsers") ?></a></div>
							</div>
							<!--GERER TOUS LES ESPACES-->
							<div class="menuLine" <?= Txt::tooltip("SPACE_moduleTooltip") ?>>
								<div class="menuIcon"><img src="app/img/space.png"></div>
								<div><a href="?ctrl=space"><?= Txt::trad("HEADER_manageAllSpaces") ?></a></div>
							</div>
							<!--PARAMETRAGE GENERAL-->
							<div class="menuLine">
								<div class="menuIcon"><img src="app/img/settingsGeneral.png"></div>
								<div><a href="?ctrl=agora"><?= Txt::trad("AGORA_generalSettings") ?></a></div>
							</div>
						<?php } ?>
					<?php } ?>

				</div>

				<!--COLONNE 2 : ESPACES-->
				<?php if(Ctrl::$curUser->isUser() && count($spaceList)>=2 || Ctrl::$curUser->isSpaceAdmin()){ ?>
				<div>
					<?php foreach($spaceList as $tmpSpace){ ?>
						<!--LABEL DE L'ESPACE-->
						<div class="menuLine <?= $tmpSpace->isCurSpace()?'lineSelect':null ?>"  <?= Txt::tooltip(Txt::trad("HEADER_spaceSwitch")."<hr>".$tmpSpace->description) ?>>
							<div class="menuIcon"><img src="app/img/space.png"></div>
							<div><a href="?_idSpaceAccess=<?= $tmpSpace->_id ?>"><?= $tmpSpace->name ?></a></div>
						</div>
						<!--OPTIONS ADMIN D'ESPACE-->
						<?php if(Ctrl::$curSpace->_id==$tmpSpace->_id && $tmpSpace->editRight()){ ?>
							<!--GERER L'ESPACE-->
							<div class="menuLine menuMainAdmin" onclick="lightboxOpen('<?= $tmpSpace->getUrl('edit') ?>')" <?= Txt::tooltip("SPACE_configInfo") ?>>
								<img src="app/img/edit.png"><?= Txt::trad("SPACE_config") ?>
							</div>
							<!--USERS DE L'ESPACE-->
							<div class="menuLine menuMainAdmin">
								<a href="?ctrl=user&displayUsers=space"><img src="app/img/user/iconSmall.png"><?= Txt::trad("USER_spaceUsers") ?></a>
							</div>
							<!--LOGS DE L'ESPACE-->
							<div class="menuLine menuMainAdmin">
								<a href="?ctrl=log"><img src="app/img/log.png"><?= Txt::trad("LOG_MODULE_DESCRIPTION") ?></a>
							</div>
							<!--AFFICHAGE ADMIN-->
							<div class="menuLine menuMainAdmin <?= empty($_SESSION['displayAdmin']) ? null : 'optionSelect' ?>" <?= Txt::tooltip("HEADER_displayAdminInfo") ?>>
								<a href="?ctrl=<?= Req::$curCtrl ?>&displayAdmin=<?= empty($_SESSION['displayAdmin'])?'true':'false' ?>"><img src="app/img/eye.png"><?= Txt::trad("HEADER_displayAdmin") ?></a>
							</div>
						<?php } ?>
					<?php } ?>
				</div>
				<?php } ?>

				<!--COLONNE 3 : SHORTCUTS-->
				<?php if(Ctrl::$curUser->isUser() && !empty($pluginsShortcut)){ ?>
				<div>
					<div class="menuLine"><div class="menuIcon"><img src="app/img/shortcut.png"></div><div><?= Txt::trad("HEADER_shortcuts") ?> :</div></div>
					<?php foreach($pluginsShortcut as $tmpObj){ ?>
						<div class="menuLine" <?= Txt::tooltip($tmpObj->pluginTooltip) ?> >
							<div class="menuIcon"><img src="app/img/arrowRight.png"></div>
							<div>
								<img src="app/img/<?= $tmpObj->pluginIcon ?>" onclick="<?= $tmpObj->pluginJsIcon ?>" class="menuMainShortcut">
								<span onclick="<?= $tmpObj->pluginJsLabel ?>"><?= Txt::reduce($tmpObj->pluginLabel,50) ?></span>
								<?= $tmpObj->editButtom() ?>
							</div>
						</div>
					<?php } ?>
				</div>
				<?php } ?>

			</div>

			<!--MENU OMNISPACE-->
			<div id="menuOmnispace">
				<?php if(Req::connectSpaceSwitch()){ ?>
					<div onclick="confirmRedir('<?= Req::connectSpaceSwitchUrl() ?>','<?= $connectSpaceSwitchLabel ?>')"><img src="app/img/switch.png"> <?= Txt::trad("connectSpaceSwitch") ?></div>
				<?php } ?>
				<div><a href="<?= OMNISPACE_URL_PUBLIC ?>" target="_blank" title="<?= OMNISPACE_URL_LABEL ?>"><img src="app/img/logoSmall.png"></a></div>
			</div>
		</div>

		<!--MENU RIGHT : MODULES DISPONIBLES-->
		<div>
			<div id="headerBarRight">
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
			<div id="headerMobileModule" class="menuContextLaunch" for="headerBarRight"><?= !empty($modCurMobileIcon) ? $modCurMobileIcon : 'Menu' ?>&nbsp;<img src="app/img/menuSmall.png">&nbsp;</div>
		</div>
	</div>
</div>

<!--MARGE ENTRE LA HEADERBAR ET LE CONTENU DE LA PAGE-->
<div id="headerBarMargin">&nbsp;</div>