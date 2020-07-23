<style>
/*MENU PRINCIPAL*/
.vHeaderMainLogo								{position:absolute; top:2px; left:2px;}
#headerMainMenuLabels							{padding-left:75px; padding-right:30px; line-height:45px; white-space:nowrap;}/*"padding-left" en fonction du width du "logo.png". "line-height" en fonction de "#headerBar". "nowrap" pour laisser les labels sur une seule ligne (ne pas éclater l'affichage!)*/
#headerUserLabel, #headerSpaceLabel				{display:inline-block; max-width:250px; overflow:hidden; text-overflow:ellipsis;}/*"ellipsis" pour le dépassement de texte*/
.vHeaderArrowRight								{margin:0px 4px 0px 8px; height:15px;}
#headerMainMenuTab								{display:table; margin:0px;}
#headerMainMenuTab>div							{display:table-cell; padding:0px; padding-right:10px!important;}
#headerMainMenuRight							{border-left:<?= Ctrl::$agora->skin=="black"?"#333":"#eee"?> solid 1px;}
#headerMainMenuRight .menuIconArrow				{text-align:right!important;}
#headerMainMenuOmnispace						{text-align:center; margin-top:15px;}
#curSpaceEdit									{margin-left:10px; height:20px; transform:scaleX(-1);}/*"scaleX" : inverse l'image*/
#headerMenuBurger								{margin-left:10px;}
/*MENU DES MODULES*/
#headerModuleTab								{display:inline-table; height:100%;}
.vHeaderModule									{display:table-cell; <?= (Ctrl::$agora->moduleLabelDisplay=="hide") ? "padding:0px 10px 0px 10px;" : "padding:0px 5px 0px 5px;" ?> text-align:center; vertical-align:middle; border:solid 1px transparent; cursor:pointer;}/*Par défaut, le border est transparent*/
.vHeaderModule:hover, .vHeaderCurModule			{<?= Ctrl::$agora->skin=="black" ? "background:#333;border-color:transparent #555 transparent #555;" : "background:#fff;border-color:transparent #ddd transparent #ddd;" ?>}/*Sélectionne le module courant*/
.vHeaderModuleLabel								{<?= (Ctrl::$agora->moduleLabelDisplay=="hide" && Req::isMobile()==false) ? "display:none" : "display:inline-block;min-width:50px;" ?>}/*'inline-block' et 'min-width' pour un affichage homogène du label sous les icones (tester à 1300px..)*/
#headerModuleResp								{display:none;}

/*RESPONSIVE*/
@media screen and (max-width:1023px){
	/*MENU PRINCIPAL*/
	.vHeaderMainLogo							{top:5px; left:0px;}
	#headerSpaceLabel							{max-width:180px;}
	#headerMainMenuLabels						{padding-left:58px; padding-right:10px;}/*"padding-left" en fonction du width du "logoMobile.png"*/
	#headerMainMenuLabels, #headerModuleResp	{display:block; font-size:1.08em!important; white-space:nowrap;}/*Label de l'espace et du module courant. "nowrap" pour laisser les labels sur une seule ligne (ne pas éclater l'affichage!)*/
	#headerMainMenuTab, #headerMainMenuTab>div	{display:block; padding-right:0px!important; border:0px;}
	#headerMainMenuRight						{border:0px;}
	#headerMainMenuOmnispace					{display:none;}
	/*MENU DES MODULES (intégré dans le menu responsive "#respMenuOne" de VueStructure.php)*/
	#headerModuleTab							{display:none!important;}/*liste des modules masqués dans le menu principal...*/
	.vHeaderModule								{display:inline-block; padding:8px; margin:0px 0px 4px 4px; width:145px; text-align:left;}/*...mais copiés dans le menu responsive (pas plus de 145px de large)*/
	.vHeaderModule:hover, .vHeaderCurModule		{<?= Ctrl::$agora->skin=="black" ? "background:#333;border:solid 1px #555;" : "background:#f9f9f9;border:solid 1px #ddd;" ?> border-radius:5px;}/*mod. courant: background grisé*/
}
</style>


<div id="headerBar" class="noPrint">

	<!--LOGO + LABELS + MENU PRINCIPAL-->
	<div>
		<label id="headerMainMenuLabels" class="menuLaunch" for="headerMainMenu">
			<?php
			////	LOGO PRINCIPAL  &&  ICONE "SEARCH"
			echo "<img src=\"app/img/".(Req::isMobile()?'logoMobile.png':'logo.png')."\" class='vHeaderMainLogo'>";
			if(Req::isMobile()==false)  {echo "<img src='app/img/search.png'><img src='app/img/arrowRightBig.png' class='vHeaderArrowRight'>";}
			////	ICONE DE "VALIDATION D'INSCRIPTION D'UTILISATEUR"
			if($userInscriptionValidate==true && Req::isMobile()==false)  {echo "<img src='app/img/check.png' class='vHeaderUserInscription'><img src='app/img/arrowRightBig.png' class='vHeaderArrowRight'>";}
			////	LABEL DE L'UTILISATEUR COURANT  &&  LABEL DE L'ESPACE COURANT  &&  ICONE BURGER/ARROW
			if(Ctrl::$curUser->isUser() && Req::isMobile()==false)  {echo "<div id='headerUserLabel'>".Ctrl::$curUser->getLabel()."</div><img src='app/img/arrowRightBig.png' class='vHeaderArrowRight'>";}
			echo "<div id='headerSpaceLabel'>".ucfirst(strtolower(Ctrl::$curSpace->name))."</div>";
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
						if($userInscriptionValidate==true)  {echo "<div class='menuLine sLink vHeaderUserInscription' onclick=\"lightboxOpen('?ctrl=user&action=UserInscriptionValidate')\" title=\"".Txt::trad("userInscriptionValidateInfo")."\"><div class='menuIcon'><img src='app/img/check.png'></div><div>".Txt::trad("userInscriptionValidate")."</div></div> <script> $('.vHeaderUserInscription').effect('pulsate',{times:30},30000); </script>";}
						////	ENVOI D'INVITATION
						if(Ctrl::$curUser->sendInvitationRight())  {echo "<div class='menuLine sLink' onclick=\"lightboxOpen('?ctrl=user&action=SendInvitation')\" title=\"".Txt::trad("USER_sendInvitationInfo")."\"><div class='menuIcon'><img src='app/img/mail.png'></div><div>".Txt::trad("USER_sendInvitation")."</div></div>";}
						////	DOCUMENTATION
						$docFileName=(Txt::trad("CURLANG")=="fr")  ?  "DOCUMENTATION_FR.pdf"  :  "DOCUMENTATION_EN.pdf";
						$docLink=(Req::isMobileApp())  ?  "redir('?ctrl=misc&action=GetFile&fileName=".$docFileName."&filePath=".urlencode("docs/".$docFileName)."&fromMobileApp=true');"  :  "lightboxOpen('docs/".$docFileName."');";
						echo "<div class='menuLine sLink' onclick=\"".$docLink."\"><div class='menuIcon'><img src='app/img/info.png'></div><div>".Txt::trad("HEADER_documentation")."</div></div>";
						////	EDITION DU PROFIL USER  &&  EDITION DU MESSENGER  &&  DECONNEXION
						echo "<hr><div class='menuLine sLink' onclick=\"lightboxOpen('".Ctrl::$curUser->getUrl("edit")."')\"><div class='menuIcon'><img src='app/img/edit.png'></div><div>".Txt::trad("USER_myProfilEdit")." &nbsp;".Ctrl::$curUser->getImg(false,true)."</div></div>";
						if(Ctrl::$curUser->messengerEdit())  {echo "<div class='menuLine sLink' onclick=\"lightboxOpen('?ctrl=user&action=UserEditMessenger&targetObjId=".Ctrl::$curUser->_targetObjId."')\" title=\"".Txt::trad("USER_livecounterVisibility")."\"><div class='menuIcon'><img src='app/img/messenger.png'></div><div>".Txt::trad("USER_myMessengerEdit")."</div></div>";}
						echo "<div class='menuLine sLink' onclick=\"redir('?disconnect=1')\"><div class='menuIcon'><img src='app/img/logout.png'></div><div>".Txt::trad("HEADER_disconnect")."</div></div>";
						////	SEPARATEUR D'ADMINISTRATION
						if(Ctrl::$curUser->isAdminSpace())  {echo "<hr>";}
						////	PARAMETRAGE GENERAL
						if(Ctrl::$curUser->isAdminGeneral())  {echo "<div class='menuLine sLink' onclick=\"redir('?ctrl=agora')\"><div class='menuIcon'><img src='app/img/paramsGeneral.png'></div><div>".Txt::trad("AGORA_generalSettings")."</div></div>";}
						////	PARAMETRAGE DE L'ESPACE COURANT
						if(Ctrl::$curUser->isAdminSpace())  {echo "<div class='menuLine sLink' onclick=\"lightboxOpen('".Ctrl::$curSpace->getUrl("edit")."')\"><div class='menuIcon'><img src='app/img/params.png'></div><div>".Txt::trad("SPACE_config")." <i>".Txt::reduce(Ctrl::$curSpace->name,35,false)."</i></div></div>";}
						////	 EDITION DE TOUS LES ESPACES
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
							$curSpaceEdit=($tmpSpace->isCurSpace() && Ctrl::$curUser->isAdminSpace())  ?  "<img src='app/img/edit.png' class='sLink' id='curSpaceEdit' onclick=\"lightboxOpen('".$tmpSpace->getUrl("edit")."');\" title=\"".Txt::trad("SPACE_config")."\">"  :  null;
							echo "<div class='menuLine'><div class='menuIcon menuIconArrow'><img src='app/img/arrowRightBig.png'></div><div><span class='".($tmpSpace->isCurSpace()?'sLinkSelect':'sLink')."' onclick=\"redir('?_idSpaceAccess=".$tmpSpace->_id."')\" title=\"".$tmpSpace->description."\">".$tmpSpace->name."</span>".$curSpaceEdit."</div></div>";
						}
					}
					////	Affiche les plugins "shortcut" ("pluginLabel" : Réduit le label & suppr toutes les balises html)
					if(!empty($pluginsShortcut)){
						if($showSpaceList==true)  {echo "<hr>";}
						echo "<div class='menuLine'><div class='menuIcon'><img src='app/img/shortcut.png'></div><div>".Txt::trad("HEADER_shortcuts")." :</div></div>";
						foreach($pluginsShortcut as $tmpObj){
							//Label & tooltips: suppr les balises html (cf. TinyMce) et réduit la taille du texte
							$tmpObj->pluginLabel=Txt::cleanPlugin($tmpObj->pluginLabel,40,null);
							$tmpObj->pluginTooltip=Txt::cleanPlugin($tmpObj->pluginTooltip,300);
							echo "<div class='menuLine sLink' onclick=\"".$tmpObj->pluginJsLabel."\" title=\"".$tmpObj->pluginTooltip."\"><div class='menuIcon'><img src='app/img/".$tmpObj->pluginIcon."'></div><div>".$tmpObj->pluginLabel."</div></div>";
						}
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
			echo "<div onclick=\"redir('".$tmpMod["url"]."')\" title=\"".$tmpMod["description"]."\" class=\"vHeaderModule ".($tmpMod["isCurModule"]==true?'vHeaderCurModule':null)."\">
					<img src='app/img/".$tmpMod["moduleName"]."/".((Ctrl::$agora->moduleLabelDisplay=='hide' && Req::isMobile()==false)?'icon':'iconSmall').".png'> <span class='vHeaderModuleLabel'>".$tmpMod["label"]."</span>
				  </div>";
			if($tmpMod["isCurModule"]==true)  {$respModLabel="<img src='app/img/".$tmpMod["moduleName"]."/iconSmall.png'> <label>".$tmpMod["label"]."</label>";}
		}
		?>
		</div>
		
		<!--RESPONSIVE : LABEL DU MODULE COURANT-->
		<div id="headerModuleResp" class="menuLaunch" for="headerModuleTab" forBis="pageModMenu"><?= isset($respModLabel) ? $respModLabel : "modules" ?> <img src='app/img/arrowBottom.png'>&nbsp;</div>
	</div>
</div>