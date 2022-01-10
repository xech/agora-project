<style>
/*MENU PRINCIPAL*/
.vHeaderMainLogo								{position:absolute; top:2px; left:2px;}
#headerMainMenuLabels							{padding-left:75px; padding-right:30px; line-height:45px; white-space:nowrap;}/*"padding-left" en fonction du width du "logo.png". "line-height" en fonction de "#headerBar". "nowrap" pour laisser les labels sur une seule ligne (ne pas éclater l'affichage!)*/
#headerUserLabel, #headerSpaceLabel				{display:inline-block; max-width:250px; overflow:hidden; text-overflow:ellipsis;}/*"ellipsis" pour le dépassement de texte*/
.vHeaderArrowRight								{margin:0px 4px 0px 8px; height:15px;}
#headerMenuBurger								{margin-left:10px;}
#headerMainMenuTab								{display:table; margin:0px;}
#headerMainMenuTab>div							{display:table-cell; padding:0px; padding-right:10px!important;}
#headerMainMenuOmnispace						{text-align:center; margin-top:15px;}/*Logo Omnispace*/
#headerMainMenuRight							{border-left:<?= Ctrl::$agora->skin=="black"?"#333":"#eee"?> solid 1px;}
.vSwitchSpace>div:first-child					{text-align:right!important;}/*icone "arrow*/
.vSwitchSpace img[src*=edit]					{visibility:hidden; float:right; cursor:pointer; margin-left:15px; height:20px; transform:scaleX(-1);}/*"scaleX" : inverse l'image*/
.vSwitchSpace:hover img[src*=edit]				{visibility:visible;}/*"scaleX" : inverse l'image*/
/*MENU DES MODULES*/
#headerModuleTab								{display:inline-table; height:100%;}
.vHeaderModule									{display:table-cell; text-align:center; vertical-align:middle; cursor:pointer;}
.vHeaderModuleButton							{margin:0px 3px; padding:3px; border:solid 1px transparent; border-radius:5px;}/*bouton du module : cf. vHeaderModuleCurrent (par défaut border transparent de 1px)*/
.vHeaderModuleLabel								{<?= (Req::isMobile() || Ctrl::$agora->moduleLabelDisplay!="hide") ? "display:inline-block;min-width:45px;" : "display:none" ?>}/*'inline-block' et 'min-width' pour un affichage homogène du label sous les icones (tester à 1300px..)*/
#headerModuleResp								{display:none;}

/*RESPONSIVE*/
@media screen and (max-width:1023px){
	/*MENU PRINCIPAL*/
	.vHeaderMainLogo							{top:10px; left:2px;}
	#headerSpaceLabel							{max-width:160px;}
	#headerMainMenuLabels						{padding-left:36px; padding-right:0px;}/*"padding-left" en fonction du width du "logoResp.png"*/
	#headerMainMenuLabels, #headerModuleResp	{display:block; font-size:1.08em!important; white-space:nowrap;}/*Label de l'espace et du module courant. "nowrap" pour laisser les labels sur une seule ligne (ne pas éclater l'affichage!)*/
	#headerMainMenuTab, #headerMainMenuTab>div	{display:block; padding-right:0px!important; border:0px;}
	#headerMainMenuTab .personImg 				{display:none;}
	#headerMainMenuRight						{border:0px;}
	#headerMainMenuOmnispace					{display:none;}
	/*MENU DES MODULES (intégré dans le menu responsive "#respMenuOne" de VueStructure.php)*/
	#headerModuleTab							{display:none!important;}								/*liste des modules masqués dans la barre de menu..*/
	.vHeaderModule								{display:inline-block; width:142px; text-align:left;}	/*..mais copiés dans le menu responsive (pas plus de 145px de large)*/
	.vHeaderModuleButton						{margin:5px 3px;}
}
</style>


<div id="headerBar" class="noPrint">

	<!--LOGO + LABELS + MENU PRINCIPAL-->
	<div>
		<label id="headerMainMenuLabels" class="menuLaunch" for="headerMainMenu">
			<?php
			////	LOGO PRINCIPAL  &&  ICONE "SEARCH"
			echo "<img src=\"app/img/".(Req::isMobile()?'logoResp.png':'logo.png')."\" class='vHeaderMainLogo'>";
			if(Req::isMobile()==false)  {echo "<img src='app/img/search.png'><img src='app/img/arrowRightBig.png' class='vHeaderArrowRight'>";}
			////	ICONE DE "VALIDATION D'INSCRIPTION D'UTILISATEUR"
			if($userInscriptionValidate==true && Req::isMobile()==false)  {echo "<span class='pulsate'><img src='app/img/check.png'> ".Txt::trad("userInscriptionPulsate")."</span><img src='app/img/arrowRightBig.png' class='vHeaderArrowRight'>";}
			////	LABEL DE L'UTILISATEUR COURANT  &&  LABEL DE L'ESPACE COURANT  &&  ICONE BURGER/ARROW
			if(Ctrl::$curUser->isUser() && Req::isMobile()==false)  {echo "<div id='headerUserLabel'>".Ctrl::$curUser->getLabel()."</div><img src='app/img/arrowRightBig.png' class='vHeaderArrowRight'>";}
			echo "<div id='headerSpaceLabel'>".(Req::isMobile()?strtolower(Ctrl::$curSpace->name):Ctrl::$curSpace->name)."</div>";
			echo Req::isMobile()  ?  "<img src='app/img/arrowBottom.png'>"  :  "<img src='app/img/menuSmall.png' id='headerMenuBurger'>";
			?>
		</label>
		<div class="menuContext" id="headerMainMenu">
			<div id="headerMainMenuTab">
				<div>
					<?php
					////	RECHERCHER SUR L'ESPACE (GUESTS & USERS)
					echo "<div class='menuLine sLink' onclick=\"lightboxOpen('?ctrl=misc&action=Search')\"><div class='menuIcon'><img src='app/img/search.png'></div><div>".Txt::trad("HEADER_searchElem")."</div></div>";
					////	MENU DE CONNEXION DES GUESTS
					if(Ctrl::$curUser->isUser()==false)  {echo "<div class='menuLine sLink' onclick=\"redir('?disconnect=1')\"><div class='menuIcon'><img src='app/img/logout.png'></div><div>".Txt::trad("connect")."</div></div>";}
					////	MENU DES USERS
					else
					{
						////	VALIDE L'INSCRIPTION D'UTILISATEURS
						if($userInscriptionValidate==true)  {echo "<div class='menuLine sLink pulsate' onclick=\"lightboxOpen('?ctrl=user&action=UserInscriptionValidate')\" title=\"".Txt::trad("userInscriptionValidateInfo")."\"><div class='menuIcon'><img src='app/img/check.png'></div><div>".Txt::trad("userInscriptionValidate")."</div></div>";}
						////	ENVOI D'INVITATION
						if(Ctrl::$curUser->sendInvitationRight())  {echo "<div class='menuLine sLink' onclick=\"lightboxOpen('?ctrl=user&action=SendInvitation')\" title=\"".Txt::trad("USER_sendInvitationInfo")."\"><div class='menuIcon'><img src='app/img/mail.png'></div><div>".Txt::trad("USER_sendInvitation")."</div></div>";}
						////	DOCUMENTATION
						$docFileName=(Txt::trad("CURLANG")=="fr")  ?  "DOCUMENTATION_FR.pdf"  :  "DOCUMENTATION_EN.pdf";
						$docLink=(Req::isMobileApp())  ?  "redir('?ctrl=misc&action=GetFile&fileName=".$docFileName."&filePath=".urlencode("docs/".$docFileName)."&fromMobileApp=true');"  :  "lightboxOpen('docs/".$docFileName."');";
						echo "<div class='menuLine sLink' onclick=\"".$docLink."\"><div class='menuIcon'><img src='app/img/info.png'></div><div>".Txt::trad("HEADER_documentation")."</div></div>";
						////	EDITION DU PROFIL USER  &&  EDITION DU MESSENGER  &&  DECONNEXION
						echo "<hr><div class='menuLine sLink' onclick=\"lightboxOpen('".Ctrl::$curUser->getUrl("edit")."')\"><div class='menuIcon'><img src='app/img/edit.png'></div><div>".Txt::trad("USER_myProfilEdit")." &nbsp;".Ctrl::$curUser->getImg(false,true)."</div></div>";
						if(Ctrl::$curUser->messengerAvailable())  {echo "<div class='menuLine sLink' onclick=\"lightboxOpen('?ctrl=user&action=UserEditMessenger&typeId=".Ctrl::$curUser->_typeId."')\" title=\"".Txt::trad("USER_livecounterVisibility")."\"><div class='menuIcon'><img src='app/img/messengerSmall.png'></div><div>".Txt::trad("USER_messengerEdit")."</div></div>";}
						echo "<div class='menuLine sLink' onclick=\"redir('?disconnect=1')\"><div class='menuIcon'><img src='app/img/logout.png'></div><div>".Txt::trad("HEADER_disconnect")."</div></div>";
						////	SEPARATEUR D'ADMINISTRATION
						if(Ctrl::$curUser->isAdminSpace())  {echo "<hr>";}
						////	PARAMETRAGE GENERAL
						if(Ctrl::$curUser->isAdminGeneral())  {echo "<div class='menuLine sLink' onclick=\"redir('?ctrl=agora')\"><div class='menuIcon'><img src='app/img/settingsGeneral.png'></div><div>".Txt::trad("AGORA_generalSettings")."</div></div>";}
						////	PARAMETRAGE DE L'ESPACE COURANT
						if(Ctrl::$curUser->isAdminSpace())  {echo "<div class='menuLine sLink' onclick=\"lightboxOpen('".Ctrl::$curSpace->getUrl("edit")."')\"><div class='menuIcon'><img src='app/img/settings.png'></div><div>".Txt::trad("SPACE_config")." <i>".Txt::reduce(Ctrl::$curSpace->name,35)."</i></div></div>";}
						////	 EDITION DES ESPACES DU SITE
						if(Ctrl::$curUser->isAdminGeneral())  {echo "<div class='menuLine sLink' onclick=\"redir('?ctrl=space')\" title=\"".Txt::trad("SPACE_moduleInfo")."\"><div class='menuIcon'><img src='app/img/space.png'></div><div>".Txt::trad("SPACE_manageSpaces")."</div></div>";}
						////	PARAMETRAGE DES UTILISATEURS
						if(Ctrl::$curUser->isAdminGeneral())  {echo "<div class='menuLine sLink' onclick=\"redir('?ctrl=user&displayUsers=all')\" title=\"".Txt::trad("USER_allUsersInfo")."\"><div class='menuIcon'><img src='app/img/user/icon.png'></div><div>".Txt::trad("USER_allUsers")."</div></div>";}
						////	AFFICHAGE "ADMINISTRATEUR"
						if(Ctrl::$curUser->isAdminSpace())  {echo "<div class='menuLine ".(empty($_SESSION["displayAdmin"])?'sLink':'sLinkSelect')."' onclick=\"redir('?ctrl=".Req::$curCtrl."&displayAdmin=".(empty($_SESSION["displayAdmin"])?"true":"false")."')\" title=\"".Txt::trad("HEADER_displayAdminInfo")."\"><div class='menuIcon'><img src='app/img/eye.png'></div><div>".Txt::trad("HEADER_displayAdmin")."</div></div>";}
						////	MODULE "LOGS"
						if(Ctrl::$curUser->isAdminSpace())  {echo "<div class='menuLine sLink' onclick=\"redir('?ctrl=log')\"><div class='menuIcon'><img src='app/img/log.png'></div><div>".Txt::trad("LOG_moduleDescription")."</div></div>";}
						////	ESPACE DISQUE UTILISE
						if(Ctrl::$curUser->isAdminGeneral())  {echo "<div class='menuLine'><div class='menuIcon'><img src='app/img/diskSpace".($diskSpaceAlert==true?'Alert':null).".png'></div><div>".Txt::trad("diskSpaceUsed")." : ".$diskSpacePercent."% ".Txt::trad("from")." ".File::displaySize(limite_espace_disque)."</div></div>";}
						////	ESPACE DISQUE UTILISE
						if(Ctrl::$curUser->isAdminGeneral())  {"<div class='menuLine'><div class='menuIcon'><img src='app/img/diskSpace".($diskSpaceAlert==true?'Alert':null).".png'></div><div>".Txt::trad("diskSpaceUsed")." : ".$diskSpacePercent."% ".Txt::trad("from")." ".File::displaySize(limite_espace_disque)."</div></div>";}
					}
					////	LOGO OMNISPACE (GUESTS & USERS)
					echo "<hr><div class='sLink' id='headerMainMenuOmnispace' onclick=\"window.open('".OMNISPACE_URL_PUBLIC."')\" title=\"".OMNISPACE_URL_LABEL."\"><img src='app/img/logoLabel.png'></div>";
  					?>
				</div>
				<?php
				////	PANNEAU DE DROITE : ESPACES DISPONIBLES && RACCOURCIS
				if($showSpaceList==true || !empty($pluginsShortcut))
				{
					echo "<div id='headerMainMenuRight'>";
					////	ESPACES DISPONIBLES
					if($showSpaceList==true){
						echo "<div class='menuLine'><div class='menuIcon'><img src='app/img/space.png'></div><div>".Txt::trad("HEADER_displaySpace")." :</div></div>";
						foreach(Ctrl::$curUser->getSpaces() as $tmpSpace){
							$iconSpaceEdit=($tmpSpace->editRight())  ?  "<img src='app/img/edit.png' onclick=\"lightboxOpen('".$tmpSpace->getUrl("edit")."');\" title=\"".Txt::trad("SPACE_config")."\">"  :  null;
							echo "<div class='menuLine vSwitchSpace'><div class='menuIcon'><img src='app/img/arrowRightBig.png'></div><div><span class='".($tmpSpace->isCurSpace()?'sLinkSelect':'sLink')."' onclick=\"redir('?_idSpaceAccess=".$tmpSpace->_id."')\" title=\"".Txt::tooltip($tmpSpace->description)."\">".$tmpSpace->name."</span>".$iconSpaceEdit."</div></div>";
						}
					}
					////	Affiche les plugins "shortcut" ("reduce()" pour réduire la taille du texte et des tags html, surtout sur le label principal)
					if(!empty($pluginsShortcut)){
						if($showSpaceList==true)  {echo "<hr>";}
						echo "<div class='menuLine'><div class='menuIcon'><img src='app/img/shortcut.png'></div><div>".Txt::trad("HEADER_shortcuts")." :</div></div>";
						foreach($pluginsShortcut as $tmpObj){
							echo "<div class='menuLine sLink' title=\"".Txt::tooltip($tmpObj->pluginTooltip)."\">
									<div onclick=\"".$tmpObj->pluginJsIcon."\" class='menuIcon'><img src='app/img/".$tmpObj->pluginIcon."'></div>
									<div onclick=\"".$tmpObj->pluginJsLabel."\">".$tmpObj->pluginLabel."</div>
								  </div>";
						}
					}
					echo "</div>";
				}
				?>
			</div>
		</div>
	</div>

	<div>
		<!--MODULES DE L'ESPACE-->
		<div id="headerModuleTab">
			<?php
			foreach($moduleList as $tmpMod){
				//Affiche le module courant
				$tmpModClass=($tmpMod["isCurModule"]==true)  ?  "vHeaderModuleCurrent"  :  null;
				$tmpModIcon=(Req::isMobile() || Ctrl::$agora->moduleLabelDisplay!="hide")  ?  "iconSmall.png"  :  "icon.png";
				echo '<div class="vHeaderModule" onclick="redir(\''.$tmpMod["url"].'\')" title="'.Txt::tooltip($tmpMod["description"]).'">
						<div class="vHeaderModuleButton '.$tmpModClass.'"><img src="app/img/'.$tmpMod["moduleName"].'/'.$tmpModIcon.'"> <span class="vHeaderModuleLabel">'.$tmpMod["label"].'</span></div>
					  </div>';
				//Mod Responsive : retient le module courant
				if(Req::isMobile() && $tmpMod["isCurModule"]==true)  {$respModLabel='<img src="app/img/'.$tmpMod["moduleName"].'/iconSmall.png"> <label>'.$tmpMod["label"].'</label>';}
			}
			?>
			<!--MESSENGER (cf. "VueMessenger")-->
			<?php if(self::$curUser->messengerEnabled()) { ?>
			<div class="vHeaderModule" onclick="messengerDisplay('all')" title="<?= Txt::trad("MESSENGER_nobody")."<br>".Txt::trad("MESSENGER_nobodyTitle") ?>">
				<div class="vHeaderModuleButton"><img src="app/img/messenger.png"></div>
			</div>
			<?php } ?>
		</div>
	
		<!--RESPONSIVE : LABEL DU MODULE COURANT-->
		<div id="headerModuleResp" class="menuLaunch" for="headerModuleTab" forBis="pageModMenu"><?= isset($respModLabel) ? $respModLabel : "modules" ?> <img src='app/img/arrowBottom.png'>&nbsp;</div>
	</div>
</div>