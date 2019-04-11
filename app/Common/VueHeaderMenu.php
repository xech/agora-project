<style>
/*MENU PRINCIPAL*/
.vHeaderArrowRight		{margin-left:5px; margin-right:5px; height:16px;}
#headerMainMenuTab		{display:table; margin:0px;}
#headerMainMenuTab>div	{display:table-cell; padding:0px; padding-right:10px!important;}
#headerMainMenuRight	{border-left:<?= Ctrl::$agora->skin=="black"?"#333":"#eee"?> solid 1px;}
#headerMainMenuRight .menuIconArrow	{text-align:right!important;}
#curSpaceEdit			{margin-left:10px; height:20px; transform:scaleX(-1);}/*"scaleX" : inverse l'image*/
/*MENU DES MODULES*/
#headerModuleTab		{display:inline-table; height:100%;}
.vHeaderModule			{display:table-cell; vertical-align:middle; padding:0px 8px 0px 8px; border:solid 1px transparent; cursor:pointer;}/*par défaut : border transparent*/
.vHeaderModule label	{<?= (Ctrl::$agora->moduleLabelDisplay=="hide" && Req::isMobile()==false)?"display:none;":null; ?>}/*Toujours afficher en responsive!*/
.vHeaderModule:hover, .vHeaderCurModule	{<?= Ctrl::$agora->skin=="black" ? "background:#333;border-color:transparent #555 transparent #555;" : "background:#fff;border-color:transparent #ddd transparent #ddd;" ?>}
#headerModuleResp		{display:none;}

/*RESPONSIVE*/
@media screen and (max-width:1023px){
	/*MENU PRINCIPAL*/
	#headerMainMenuTab, #headerMainMenuTab>div	{display:block; padding-right:0px!important; border:0px;}
	#headerMainMenuRight	{border:0px;}
	#headerMainMenuOmnispace{display:none;}
	/*MENU DES MODULES*/
	#headerModuleTab		{display:none;}										/*liste des modules masqués dans le menu principal...*/
	.vHeaderModule			{display:inline-block; width:150px; padding:8px;}	/*...mais copiés dans le menu responsive*/
	.vHeaderModule:hover, .vHeaderCurModule		{<?= Ctrl::$agora->skin=="black" ? "background:#333;border-color:#555;" : "background:#f9f9f9;border-color:#ddd;" ?> border-radius:5px;}
	.vHeaderModule img		{max-width:30px;}/*Modules du menu contextuel*/
	#headerMainMenuLaucher, #headerModuleResp	{display:block; font-size:1.1em!important;}/*Affiche le label du module courant*/
}

/*RESPONSIVE : TABLETTE EN MODE PAYSAGE*/
@media screen and (min-width:1023px) and (max-width:1200px){
	.vHeaderModule img	{max-width:20px;}
}
</style>


<div class="headerBar noPrint">

	<!--LOGO + LABELS + MENU PRINCIPAL-->
	<div>
		<label id="headerMainMenuLaucher" class="menuLaunch" for="headerMainMenu">
			<img src="app/img/<?= Req::isMobile()?"logoMobile.png":"logo.png"?>">&nbsp;
			<?php
			//Labels de l'utilisateur et de l'espace courant
			if(Ctrl::$curUser->isUser() && Req::isMobile()==false)  {echo Ctrl::$curUser->getLabel()."<img src='app/img/arrowRightBig.png' class='vHeaderArrowRight'>";}
			echo (Req::isMobile())  ?  Txt::reduce(Ctrl::$curSpace->name,25)  :  Ctrl::$curSpace->name;
			echo " &nbsp;<img src='app/img/menuSmall.png'>";
			?>
		</label>
		<div class="menuContext" id="headerMainMenu">
			<div id="headerMainMenuTab">
				<div>
					<?php
					////	VALIDE L'INSCRIPTION D'UTILISATEURS
					if($userInscriptionValidation==true){
						echo "<div class='menuLine sLink' id='headerRegisterUser' onclick=\"lightboxOpen('?ctrl=user&action=registerUser')\" title=\"".Txt::trad("usersInscriptionValidateInfo")."\"><div class='menuIcon'><img src='app/img/check.png'></div><div>".Txt::trad("usersInscriptionValidate")."</div></div>"
							."<hr><script> $('#headerRegisterUser').effect('pulsate',{times:100},100000); </script>";
					}
					////	INVITE (CONNEXION)  ||  UTILISATEUR
					if(Ctrl::$curUser->isUser()==false)  {echo "<div class='menuLine sLink' onclick=\"redir('?disconnect=1')\"><div class='menuIcon'><img src='app/img/logout.png'></div><div>".Txt::trad("connect")."</div></div>";}
					else{
						////	RECHERCHER SUR L'ESPACE + ENVOYER INVITATION + DOCUMENTATION
						echo "<div class='menuLine sLink' onclick=\"lightboxOpen('?ctrl=misc&action=Search')\"><div class='menuIcon'><img src='app/img/search.png'></div><div>".Txt::trad("HEADER_searchElem")."</div></div>";
						if(Ctrl::$curUser->sendInvitationRight())  {echo "<div class='menuLine sLink' onclick=\"lightboxOpen('?ctrl=user&action=SendInvitation')\" title=\"".Txt::trad("USER_sendInvitationInfo")."\"><div class='menuIcon'><img src='app/img/mail.png'></div><div>".Txt::trad("USER_sendInvitation")."</div></div>";}
						echo "<div class='menuLine sLink' onclick=\"lightboxOpen('docs/DOCUMENTATION_".(Txt::trad("CURLANG")=='fr'?'FR':'EN').".pdf')\"><div class='menuIcon'><img src='app/img/info.png'></div><div>".Txt::trad("HEADER_documentation")."</div></div>";
						echo "<hr>";
						////	EDIT PROFILE + EDIT MESSENGER + DECONNEXION
						echo "<div class='menuLine sLink' onclick=\"lightboxOpen('".Ctrl::$curUser->getUrl("edit")."')\"><div class='menuIcon'><img src='app/img/edit.png'></div><div>".Txt::trad("USER_myProfilEdit")."</div></div>";
						if(Ctrl::$curUser->messengerEdit())  {echo "<div class='menuLine sLink' onclick=\"lightboxOpen('?ctrl=user&action=UserEditMessenger&targetObjId=".Ctrl::$curUser->_targetObjId."')\" title=\"".Txt::trad("USER_livecounterVisibility")."\"><div class='menuIcon'><img src='app/img/messenger.png'></div><div>".Txt::trad("USER_myMessengerEdit")."</div></div>";}
						echo "<div class='menuLine sLink' onclick=\"redir('?disconnect=1')\"><div class='menuIcon'><img src='app/img/logout.png'></div><div>".Txt::trad("HEADER_disconnect")."</div></div>";
					}
					////	ADMIN SPACE => EDITION DE L'ESPACE COURANT + LOGS/HISTORIQUE DES ELEMENTS + AFFICHAGE ADMIN
					if(Ctrl::$curUser->isAdminSpace()){
						echo "<hr>"
							."<div class='menuLine sLink' onclick=\"lightboxOpen('".Ctrl::$curSpace->getUrl("edit")."')\"><div class='menuIcon'><img src='app/img/params.png'></div><div>".Txt::trad("SPACE_config")."</div></div>"
							."<div class='menuLine sLink' onclick=\"redir('?ctrl=log')\"><div class='menuIcon'><img src='app/img/log.png'></div><div>".Txt::trad("LOG_moduleDescription")."</div></div>"
							."<div class='menuLine ".(empty($_SESSION["displayAdmin"])?'sLink':'sLinkSelect')."' onclick=\"redir('?ctrl=".Req::$curCtrl."&displayAdmin=".(empty($_SESSION["displayAdmin"])?"true":"false")."')\" title=\"".Txt::trad("HEADER_displayAdminInfo")."\"><div class='menuIcon'><img src='app/img/eye.png'></div><div>".Txt::trad("HEADER_displayAdmin")."</div></div>";
					}
					////	ADMIN GENERAL => EDITION DE TOUS LES USERS + EDITION DE TOUS LES ESPACES + ESPACE DISQUE UTILISE + PARAMETRAGE GENERAL
					if(Ctrl::$curUser->isAdminGeneral()){
						echo "<hr>"
							."<div class='menuLine sLink' onclick=\"redir('?ctrl=agora')\"><div class='menuIcon'><img src='app/img/paramsGeneral.png'></div><div>".Txt::trad("AGORA_generalSettings")."</div></div>"
							."<div class='menuLine sLink' onclick=\"redir('?ctrl=user&displayUsers=all')\" title=\"".Txt::trad("USER_allUsersInfo")."\"><div class='menuIcon'><img src='app/img/user/icon.png'></div><div>".Txt::trad("USER_allUsers")."</div></div>"
							."<div class='menuLine sLink' onclick=\"redir('?ctrl=space')\" title=\"".Txt::trad("SPACE_moduleInfo")."\"><div class='menuIcon'><img src='app/img/space.png'></div><div>".Txt::trad("SPACE_manageSpaces")."</div></div>"
							."<div class='menuLine'><div class='menuIcon'><img src='app/img/diskSpace".($diskSpaceAlert==true?'Alert':null).".png'></div><div>".Txt::trad("diskSpaceUsed")." : ".$diskSpacePercent."% ".Txt::trad("from")." ".File::displaySize(limite_espace_disque)."</div></div>";
					}
					////	LOGO OMNISPACE
					echo "<hr><div class='menuLine sLink' id='headerMainMenuOmnispace' onclick=\"window.open('".OMNISPACE_URL_PUBLIC."')\" title=\"".OMNISPACE_URL_LABEL."\"><div class='menuIcon'>&nbsp;</div><div><img src='app/img/logoLabel.png'></div></div>";
					?>
				</div>
				<?php
				////	PANNEAU DE DROITE : ESPACES DISPONIBLES && RACCOURCIS
				if(count(Ctrl::$curUser->getSpaces())>1 || !empty($pluginsShortcut))
				{
					echo "<div id='headerMainMenuRight'>";
					////	ESPACES DISPONIBLES
					if(count(Ctrl::$curUser->getSpaces())>1){
						echo "<div class='menuLine'><div class='menuIcon'><img src='app/img/space.png'></div><div>".Txt::trad("HEADER_displaySpace")." :</div></div>";
						foreach(Ctrl::$curUser->getSpaces() as $tmpSpace){
							$curSpaceEdit=($tmpSpace->isCurSpace() && Ctrl::$curUser->isAdminSpace())  ?  "<img src='app/img/edit.png' id='curSpaceEdit' onclick=\"lightboxOpen('".$tmpSpace->getUrl("edit")."');event.stopPropagation();\" title=\"".Txt::trad("SPACE_config")."\">"  :  null;
							echo "<div class='menuLine ".($tmpSpace->isCurSpace()?'sLinkSelect':'sLink')."' onclick=\"redir('?_idSpaceAccess=".$tmpSpace->_id."')\" title=\"".$tmpSpace->description."\"><div class='menuIcon menuIconArrow'><img src='app/img/arrowRightBig.png'></div><div>".$tmpSpace->name.$curSpaceEdit."</div></div>";
						}
					}
					////	Affiche les plugins "shortcut" ("pluginLabel" : Réduit le label & suppr toutes les balises html)
					if(!empty($pluginsShortcut)){
						echo "<hr><div class='menuLine'><div class='menuIcon'><img src='app/img/shortcut.png'></div><div>".Txt::trad("HEADER_shortcuts")." :</div></div>";
						foreach($pluginsShortcut as $tmpPlugin)  {echo "<div class='menuLine sLink' onclick=\"".$tmpPlugin->pluginJsLabel."\" title=\"".$tmpPlugin->pluginTooltip."\"><div class='menuIcon'><img src='app/img/".$tmpPlugin->pluginIcon."'></div><div>".Txt::reduce(strip_tags($tmpPlugin->pluginLabel),40)."</div></div>";}
					}
					echo "</div>";
				}
				?>
			</div>
		</div>
	</div>

	<!--LISTE DES MODULES-->
	<div>
		<div id="headerModuleTab">
		<?php
		//Pour chaque module : init l'icone+label, retient si besoin pour l'affichage responsive ..puis affiche le module
		foreach($moduleList as $tmpMod){
			echo "<div onclick=\"redir('".$tmpMod["url"]."')\" title=\"".$tmpMod["description"]."\" class=\"vHeaderModule ".($tmpMod["isCurModule"]==true?'vHeaderCurModule':null)."\">"
					."<img src='app/img/".$tmpMod["moduleName"]."/".(Ctrl::$agora->moduleLabelDisplay=='hide'?'icon':'iconSmall').".png'> <label>".$tmpMod["label"]."</label>"
				."</div>";
			if($tmpMod["isCurModule"]==true)  {$respModLabel="<img src='app/img/".$tmpMod["moduleName"]."/iconSmall.png'>&nbsp; <label>".$tmpMod["label"]."</label>";}
		}
		?>
		</div>
		
		<!--RESPONSIVE : MODULE COURANT-->
		<div id="headerModuleResp" class="menuLaunch" for="headerModuleTab" forBis="pageModMenu"><?= isset($respModLabel) ? $respModLabel : null ?> <img src='app/img/menuSmall.png'>&nbsp;</div>
	</div>
</div>