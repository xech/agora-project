<style>
/*MENU PRINCIPAL*/
:root										{--headerMenuBorder:<?= Ctrl::$agora->skin=="black"?"#333":"#eee"?> solid 1px;}
#headerMenuLeft								{padding-left:75px; padding-right:20px; line-height:45px; white-space:nowrap;}/*"padding-left" pour afficher "#headerMainLogo" + "line-height" idem "#headerBar" + "nowrap" des labels sur une seule ligne (ne pas éclater l'affichage!)*/
#headerMobileModule							{display:none;}
#headerMainLogo								{position:absolute; top:2px; left:0px;}
#headerUserLabel, #headerSpaceLabel			{display:inline-block; max-width:250px; overflow:hidden; text-overflow:ellipsis;}/*"ellipsis" pour le dépassement de texte*/
#headerBurgerLogo							{margin-left:10px;}
#headerMenuLeft img[src*=arrowRight]		{margin:0px 4px 0px 8px;}
#headerBar>#menuMainContainer				{display:none; padding:10px; box-shadow:0px 0px 15px black; border-radius:8px 8px 25px 8px; top:2px!important; left:2px!important;}/*surcharge "#headerBar>div"*/
#menuMainTab								{display:table;}
#menuMainTab>div							{display:table-cell;}
#menuMainTab>div:not(:first-child)			{border-left:var(--headerMenuBorder);}/*Colonnes du menu principal*/
.menuMainArrow								{text-align:right!important;}/*icone "arrow*/
.editButton									{visibility:hidden; float:right; cursor:pointer; margin-left:10px; height:20px; transform:scaleX(-1);}/*Image "edit" d'espace. "scaleX" : inverse l'image*/
.menuLine:hover .editButton					{visibility:visible;}
.menuMainShortcutIcon						{max-height:24px; margin-right:10px;}
#menuMainOmnispace							{border-top:var(--headerMenuBorder); text-align:right; padding-top:10px;}
/*MENU DES MODULES*/
#headerMenuMain								{display:inline-table; height:100%;}
.vHeaderModule								{display:table-cell; text-align:center; vertical-align:middle; cursor:pointer;}
.vHeaderModuleButton						{margin:0px 3px; padding:3px; border:solid 1px transparent; border-radius:5px;}/*bouton du module (par défaut border transparent de 1px)*/
.vHeaderModuleButton:hover,.vHeaderModuleCurrent  {<?= Ctrl::$agora->skin=="black"?"background:#444;border:solid 1px #777;":"background:white;border:solid 1px #ccc;"?>}/*module courant*/
.vHeaderModuleLabel							{<?= (Req::isMobile() || !empty(Ctrl::$agora->moduleLabelDisplay)) ? "display:inline-block;min-width:45px;" : "display:none" ?>}/*'inline-block' et 'min-width' pour un affichage homogène du label sous les icones (tester à 1300px..)*/

/*MOBILE*/
@media screen and (max-width:1024px){
	/*MENU PRINCIPAL*/
	#headerMenuLeft							{padding-left:52px; padding-right:10px;}/*"padding-left" en fonction du width du "logoSmall.png"*/
	#headerMenuLeft, #headerMobileModule	{display:block; line-height:50px; font-size:1.1em!important; white-space:nowrap;}/*Label de l'espace et du module courant. "nowrap" pour laisser les labels sur une seule ligne et pas éclater l'affichage!*/
	#headerMainLogo							{top:3px; left:2px;}
	#headerSpaceLabel						{max-width:180px; text-transform:lowercase;}
	#headerBurgerLogo						{margin-left:5px;}
	#headerBar>#menuMainContainer			{border-radius:0px; top:0px!important; left:0px!important;}/*surcharge "#headerBar>div"*/
	#menuMainContainer, #menuMainTab, #menuMainTab>div	{display:block; padding:0px;}
	#menuMainTab>div:not(:first-child)		{border-left:none; border-top:var(--headerMenuBorder);}/*Blocks du menu principal*/
	.editButton								{visibility:visible;}/*tjs visible*/
	/*MENU DES MODULES (intégré dans le "#menuMobileOne" de VueStructure.php)*/
	#headerMenuMain							{display:none;}															/*liste des modules masqués dans la barre de menu..*/
	.vHeaderModule							{display:inline-block; width:50%; text-align:left; font-size:1.1em;}	/*..puis copiés dans le menu mobile*/
	.vHeaderModule img						{margin-right:10px;}
	.vHeaderModuleButton					{margin-top:5px; padding:5px;}
}
</style>


<div id="headerBar">

	<!--LOGO + LABEL DE L'USER ET L'ESPACE COURANT-->
	<div id="headerMenuLeft" class="menuLaunch" for="menuMainContainer"  <?= Txt::tooltip("mainMenu") ?>>
		<?php
		////	LOGO PRINCIPAL  +  VALIDATION D'INSCRIPTION D'USER
		echo '<img src="app/img/'.(Req::isMobile()?'logoSmall':'logo').'.png" id="headerMainLogo">';
		if($userInscriptionValidate==true)  {echo '<img src="app/img/user/subscribe.png" class="pulsate" '.Txt::trad("userInscriptionPulsate").'><img src="app/img/arrowRightBig.png">';}
		////	LABEL DE L'USER COURANT  +  DE L'ESPACE COURANT  +  ICONE BURGER
		if(Ctrl::$curUser->isUser() && Req::isMobile()==false)  {echo '<div id="headerUserLabel">'.Ctrl::$curUser->getLabel().'</div><img src="app/img/arrowRightBig.png">';}
		echo '<div id="headerSpaceLabel">'.Ctrl::$curSpace->name.'</div><img src="app/img/'.(Req::isMobile()?'menuSmall':'menu').'.png" id="headerBurgerLogo">';
		?>
	</div>

	<!--MENU CONTEXT PRINCIPAL-->
	<div class="menuContext" id="menuMainContainer">
		<div id="menuMainTab">
			<!--MENU PRINCIPAL (RECHERCHE, DOC, PROFIL, ETC)-->
			<div>
				<?php
				if(Ctrl::$curUser->isUser()){
					////	INSCRIPTION D'USERS  +  RECHERCHER  +  ENVOI D'INVITATION  +  DOCUMENTATION
					if($userInscriptionValidate==true)  {echo '<div class="menuLine pulsate" onclick="lightboxOpen(\'?ctrl=user&action=UserInscriptionValidate\')" '.Txt::tooltip("userInscriptionValidateTooltip").'><div class="menuIcon"><img src="app/img/user/subscribe.png"></div><div>'.Txt::trad("userInscriptionValidate").'</div></div>';}
					echo '<div class="menuLine" onclick="lightboxOpen(\'?ctrl=misc&action=Search\')"><div class="menuIcon"><img src="app/img/search.png"></div><div>'.Txt::trad("searchOnSpace").'</div></div>';
					if(Ctrl::$curUser->sendInvitationRight())	{echo '<div class="menuLine" onclick="lightboxOpen(\'?ctrl=user&action=SendInvitation\')" '.Txt::tooltip("USER_sendInvitationTooltip").'><div class="menuIcon"><img src="app/img/mail.png"></div><div>'.Txt::trad("USER_sendInvitation").'</div></div>';}
					$jsDoc=Req::isMobileApp()  ?  "redir('".File::docFile()."')"  :  "lightboxOpen('".File::docFile()."')";
					echo '<div class="menuLine" onclick="'.$jsDoc.'"><div class="menuIcon"><img src="app/img/documentation.png"></div><div>'.Txt::trad("HEADER_documentation").'</div></div>';
					////	  MODIF DU PROFIL  +  MESSENGER DE L'USER  +  DECONNEXION DE L'ESPACE PRINCIPAL
					echo '<hr>';
					echo '<div class="menuLine" onclick="lightboxOpen(\''.Ctrl::$curUser->getUrl("edit").'\')"><div class="menuIcon"><img src="app/img/edit.png"></div><div>'.Txt::trad("USER_myProfilEdit").' &nbsp;'.Ctrl::$curUser->profileImg(false,true).'</div></div>';
					if(MdlUser::agoraMessengerEnabled())  {echo '<div class="menuLine" onclick="lightboxOpen(\'?ctrl=user&action=UserEditMessenger&typeId='.Ctrl::$curUser->_typeId.'\')"><div class="menuIcon"><img src="app/img/messenger.png"></div><div>'.Txt::trad("USER_livecounterVisibility").'</div></div>';}
					echo '<div class="menuLine" onclick="confirmRedir(\'?disconnect=1\',\''.Txt::trad("disconnectSpaceConfirm",true).'\')"><div class="menuIcon"><img src="app/img/logout.png"></div><div>'.Txt::trad("disconnectSpace").'</div></div>';
					////	 ADMIN GENERAL :  EDIT TOUS LES USERS  +  PARAMETRAGE GENERAL  +  HISTORIQUE DES EVENEMENTS (LOGS)  +  
					if(Ctrl::$curUser->isGeneralAdmin()){
						echo '<hr>';
						echo '<div class="menuLine" onclick="redir(\'?ctrl=log\')"><div class="menuIcon"><img src="app/img/log.png"></div><div>'.Txt::trad("LOG_MODULE_DESCRIPTION").'</div></div>';
						echo '<div class="menuLine" onclick="redir(\'?ctrl=user&displayUsers=all\')"><div class="menuIcon"><img src="app/img/user/iconSmall.png"></div><div>'.Txt::trad("USER_allUsers").'</div></div>';
						echo '<div class="menuLine" onclick="redir(\'?ctrl=agora\')"><div class="menuIcon"><img src="app/img/settingsGeneral.png"></div><div>'.Txt::trad("AGORA_generalSettings").'</div></div>';
					}
					////	  SWITCH D'ESPACE
					if(Req::isSpaceSwitch())  {echo '<hr><div class="menuLine" onclick="confirmRedir(\''.Req::connectSpaceSwitchUrl().'\',\''.Txt::trad("connectSpaceSwitch",true).' ?\')"><div class="menuIcon"><img src="app/img/switch.png"></div><div>'.Txt::trad("connectSpaceSwitch").'</div></div>';}
				}
				?>
			</div>
			<?php
			////	MENU DES ESPACES 
			if($spaceListMenu==true || Ctrl::$curUser->isSpaceAdmin()){
				echo '<div>';
				////	SWITCH D'ESPACE (2 espaces ou +)
				if($spaceListMenu==true){
					echo '<div class="menuLine"><div class="menuIcon"><img src="app/img/space.png"></div><div>'.Txt::trad("HEADER_displaySpace").' :</div></div>';
					foreach($spaceList as $tmpSpace){
						$iconSpaceEdit=($tmpSpace->editRight())  ?  '<img src="app/img/settingsCurSpace.png" class="editButton" onclick="lightboxOpen(\''.$tmpSpace->getUrl("edit").'\')" '.Txt::tooltip("SPACE_config").'>'  :  null;
						echo '<div class="menuLine '.($tmpSpace->isCurSpace()?'linkSelect':null).'">
								<div class="menuIcon menuMainArrow"><img src="app/img/arrowRightBig.png"></div>
								<div><span onclick="redir(\'?_idSpaceAccess='.$tmpSpace->_id.'\')" '.Txt::tooltip($tmpSpace->description).'>'.$tmpSpace->name.'</span>'.$iconSpaceEdit.'</div>
							</div>';
					}
				}
				////	ADMIN D'ESPACE :  GERER L'ESPACE COURANT  +  GERER TOUS LES ESPACES + AFFICHAGE "ADMINISTRATEUR"
				if(Ctrl::$curUser->isSpaceAdmin()){
					if($spaceListMenu==true)  {echo '<hr>';}
					echo '<div class="menuLine" onclick="lightboxOpen(\''.Ctrl::$curSpace->getUrl("edit").'\')"><div class="menuIcon"><img src="app/img/settingsCurSpace.png"></div><div>'.Txt::trad("SPACE_config").' <i>'.Txt::reduce(Ctrl::$curSpace->name,35).'</i></div></div>';
					if(Ctrl::$curUser->isGeneralAdmin())  {echo '<div class="menuLine" onclick="redir(\'?ctrl=space\')" '.Txt::tooltip("SPACE_moduleTooltip").'><div class="menuIcon"><img src="app/img/settingsSpaces.png"></div><div>'.Txt::trad("SPACE_manageAllSpaces").'</div></div>';}
					echo '<div class="menuLine '.(!empty($_SESSION["displayAdmin"])?'optionSelect':'option').'" onclick="redir(\'?ctrl='.Req::$curCtrl.'&displayAdmin='.(empty($_SESSION["displayAdmin"])?'true':'false').'\')" '.Txt::tooltip("HEADER_displayAdminInfo").'><div class="menuIcon"><img src="app/img/eye.png"></div><div>'.Txt::trad("HEADER_displayAdmin").'</div></div>';
				}
				echo '</div>';
			}
			////	MENU DES SHORTCUTS
			if(!empty($pluginsShortcut) && Ctrl::$curUser->isUser()){
				echo '<div>
						<div class="menuLine"><div class="menuIcon"><img src="app/img/shortcut.png"></div><div>'.Txt::trad("HEADER_shortcuts").' :</div></div>';
						foreach($pluginsShortcut as $tmpObj){
							echo '<div class="menuLine" '.Txt::tooltip($tmpObj->pluginTooltip).'>
									<div class="menuIcon menuMainArrow"><img src="app/img/arrowRightBig.png"></div>
									<div><img src="app/img/'.$tmpObj->pluginIcon.'" onclick="'.$tmpObj->pluginJsIcon.'" class="menuMainShortcutIcon"><span onclick="'.$tmpObj->pluginJsLabel.'">'.$tmpObj->pluginLabel.'</span>'.$tmpObj->editButtom().'</div>
								</div>';
						}
				echo '</div>';
			}
			////	GUEST : RETOUR EN PAGE DE CONNEXION
			elseif(Ctrl::$curUser->isUser()==false){
				echo '<div><div class="menuLine" onclick="redir(\'?disconnect=1\')"><div class="menuIcon"><img src="app/img/logout.png"></div><div>'.Txt::trad("connect").'</div></div></div>';
			}
			?>
		</div>
		<!--LOGO PRINCIPAL-->
		<div id="menuMainOmnispace" onclick="window.open('<?= OMNISPACE_URL_PUBLIC ?>')" <?= Txt::tooltip(OMNISPACE_URL_LABEL) ?> ><img src="app/img/logoLabel.png"></div>
	</div>

	<!--MODULES DISPONIBLES-->
	<div>
		<div id="headerMenuMain">
			<?php
			////	MODULES DE L'ESPACE
			foreach($moduleList as $tmpMod){
				//Affiche le module courant
				$isCurModule=($tmpMod["moduleName"]==static::moduleName);
				$tmpModCurClass=($isCurModule==true)  ?  "vHeaderModuleCurrent"  :  null;
				$tmpModIcon=(Req::isMobile() || !empty(Ctrl::$agora->moduleLabelDisplay))  ?  "iconSmall.png"  :  "icon.png";
				echo '<div class="vHeaderModule" onclick="redir(\''.$tmpMod["url"].'\')" '.Txt::tooltip($tmpMod["description"]).'>
						<div class="vHeaderModuleButton '.$tmpModCurClass.'"><img src="app/img/'.$tmpMod["moduleName"].'/'.$tmpModIcon.'"> <span class="vHeaderModuleLabel">'.$tmpMod["label"].'</span></div>
					  </div>';
				//Sur mobile, on retient le label du module courant
				if(Req::isMobile() && $isCurModule==true)  {$mobileModuleLabel='<img src="app/img/'.$tmpMod["moduleName"].'/iconSmall.png"> <label>'.$tmpMod["label"].'</label>';}
			}
			////	AFFICHAGE DU MESSENGER : cf. "VueMessenger.php"
			if(Ctrl::$curUser->messengerEnabled()){
				echo '<div class="vHeaderModule" onclick="messengerDisplay(\'all\')" '.Txt::tooltip("MESSENGER_MODULE_DESCRIPTION").'>
						<div class="vHeaderModuleButton" id="headerModuleButtonMessenger"><img src="app/img/messenger.png"> <span class="vHeaderModuleLabel">'.Txt::trad("MESSENGER_MODULE_NAME").'</span></div>
					  </div>';
			}
			?>
		</div>

		<!--MENU MOBILE : LABEL DU MODULE COURANT-->
		<div id="headerMobileModule" class="menuLaunch" for="headerMenuMain" forBis="pageMenu"><?= isset($mobileModuleLabel) ? $mobileModuleLabel : "modules" ?> <img src="app/img/menuSmall.png">&nbsp;</div>
	</div>
</div>