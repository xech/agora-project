<script>
////	INIT
$(function(){
	$("[name=displayUsers]").val("<?= $_SESSION["displayUsers"] ?>");
	if($("[name=displayUsers]").val()=="all")  {$("[name=displayUsers]").addClass("vInputSelectAllUsers");}
});
</script>

<style>
.vDisplayUsers				{text-align:center; margin-top:10px; margin-bottom:15px;}
.vInputSelectAllUsers		{color:#b00; font-weight:bold;}
.vGroupLabel				{margin-top:5px; cursor:help;}
#menuAlphabet>a				{margin-right:5px;}
.vAdminRightIcon			{position:absolute; bottom:5px; right:5px; cursor:help;}
</style>

<div id="pageFull">
	<div id="pageModuleMenu">
		<div id="pageModMenu" class="miscContainer">
			<?php
			////	MENU "USERS DE L'ESPACE"/"TOUS LES USERS"
			if($menuDisplayUsers==true){
				echo "<div class='sLink vDisplayUsers' title=\"".Txt::trad("USER_allUsersInfo")."\">
						<select name='displayUsers' onChange=\"redir('?ctrl=user&displayUsers='+this.value)\">
							<option value='space'>".Txt::trad("USER_spaceUsers")."</option>
							<option value='all'>".Txt::trad("USER_allUsers")."</option>
						</select>
					  </div><hr>";
			}
			////	GROUPES D'UTILISATEURS (AFFICHAGE ESPACE UNIQUEMENT)
			if($_SESSION["displayUsers"]=="space" && (!empty($userGroups) || MdlUserGroup::addRight()))
			{
				$linkGroupsEdit=(MdlUserGroup::addRight())  ?  "class='sLink' title=\"<img src='app/img/edit.png'>&nbsp; ".Txt::trad("USER_spaceGroupsEdit")."\" onclick=\"lightboxOpen('?ctrl=user&action=UserGroupEdit');\""  :  null;
				$menuGroups="<div ".$linkGroupsEdit.">".Txt::trad("USER_spaceGroups")."</div>";
				foreach($userGroups as $tmpGroup)  {$menuGroups.="<div class='vGroupLabel' title=\"".$tmpGroup->usersLabel."\"><img src='app/img/arrowRight.png'> ".$tmpGroup->title."</div>";}
				echo "<div class='menuLine'>
						<div class='menuIcon'><img src='app/img/user/userGroup.png'></div>
						<div>".$menuGroups."</div>
					  </div><hr>";
			}
			////	AJOUTER UN UTILISATEUR  /  AFFECTER UN USER EXISTANT A L'ESPACE  /  ENVOYER DES INVITATIONS  /  IMPORTER DES UTILISATEURS  /  ENVOI DES COORDONNEES DE CONNEXION
			if(self::$curUser->isAdminSpace())			{echo "<div class='menuLine sLink' onclick=\"lightboxOpen('".MdlUser::getUrlNew()."');\" title=\"".Txt::trad($_SESSION["displayUsers"]=='all'?'USER_addUserSite':'USER_addUserSpace')."\"><div class='menuIcon'><img src='app/img/plus.png'></div><div>".Txt::trad("USER_addUser")."</div></div>";}
			if(self::$curUser->isAdminSpace() && self::$curSpace->allUsersAffected()==false)  {echo "<div class='menuLine sLink' onclick=\"lightboxOpen('?ctrl=user&action=AffectUsers');\" title=\"".Txt::trad("USER_addExistUserTitle")."\"><div class='menuIcon'><img src='app/img/plus.png'></div><div>".Txt::trad("USER_addExistUser")."</div></div>";}
			if(Ctrl::$curUser->sendInvitationRight())	{echo "<div class='menuLine sLink' title=\"".Txt::trad("USER_sendInvitationInfo")."\" onclick=\"lightboxOpen('?ctrl=user&action=SendInvitation');\"><div class='menuIcon'><img src='app/img/mail.png'></div><div>".Txt::trad("USER_sendInvitation")."</div></div>";}
			if(self::$curUser->isAdminSpace())			{echo "<div class='menuLine sLink' onclick=\"lightboxOpen('?ctrl=user&action=EditPersonsImportExport');\"><div class='menuIcon'><img src='app/img/dataImportExport.png'></div><div>".Txt::trad("import")."/".Txt::trad("export")." ".Txt::trad("importExport_user")."</div></div>";}
			if(self::$curUser->isAdminGeneral())		{echo "<div class='menuLine sLink' title=\"".Txt::trad("USER_sendCoordsInfo")."\" onclick=\"lightboxOpen('?ctrl=user&action=SendCoordinates');\"><div class='menuIcon'><img src='app/img/user/connection.png'></div><div>".Txt::trad("USER_sendCoords")."</div></div>";}
			////	SELECTION D'UTILISATEURS / TYPE D'AFFICHAGE / TRI D'AFFICHAGE
			echo "<hr>".MdlUser::menuSelectObjects().MdlUser::menuDisplayMode().MdlUser::menuSort();
			////	FILTRAGE ALPHABET
			$menuAlphabet=null;
			foreach($alphabetList as $tmpLetter)  {$menuAlphabet.="<a href=\"?ctrl=user&alphabet=".$tmpLetter."\" ".(Req::getParam("alphabet")==$tmpLetter?"class='sLinkSelect'":null).">".$tmpLetter."</a>";}
			$menuAlphabet.="&nbsp; <a href='?ctrl=user' ".(Req::isParam("alphabet")==false?"class='sLinkSelect'":null).">".Txt::trad("displayAll")."</a>";
			echo "<div class='menuLine sLink'>
					<div class='menuIcon'><img src='app/img/alphabet.png'></div>
					<div><div class='menuLaunch' for='menuAlphabet'>".Txt::trad("alphabetFilter").(Req::isParam("alphabet")?" : ".Req::getParam("alphabet"):null)."</div><div id='menuAlphabet' class='menuContext'>".$menuAlphabet."</div></div>
				 </div>";
			////	NB D'UTILISATEURS
			echo "<div class='menuLine'><div class='menuIcon'><img src='app/img/info.png'></div><div>".$usersTotalNbLabel."</div></div>";
			?>
		</div>
	</div>
	<div id="pageFullContent" class="<?= MdlUser::getDisplayMode()=="line"?"objLines":"objBlocks" ?>">
		<?php
		////	LISTE DES USERS
		foreach($displayedUsers as $tmpUser)
		{
			//Menu contextuel OU Input de sélection de l'user
			$contextMenu=null;
			if($tmpUser->editRight())			{$contextMenu=$tmpUser->contextMenu();}
			elseif(Ctrl::$curUser->isUser())	{$contextMenu=$tmpUser->targetObjectsInput();}
			//Icone "admin general" OU Icone"admin space"
			if($tmpUser->isAdminGeneral())		{$contextMenu.="<img src='app/img/user/adminGeneral.png' title=\"".Txt::trad("USER_adminGeneral")."\" class='vAdminRightIcon'>";}
			elseif($tmpUser->isAdminSpace())	{$contextMenu.="<img src='app/img/user/adminSpace.png' title=\"".Txt::trad("USER_adminSpace")."\" class='vAdminRightIcon'>";}
			//Affiche le block
			echo $tmpUser->divContainer("objPerson").$contextMenu.
				"<div class='objContentScroll'>
					<div class='objContent'>
						<div class='objIcon'>".$tmpUser->getImg(true,false,true)."</div>
						<div class='objLabel'>
							<a href=\"javascript:lightboxOpen('".$tmpUser->getUrl("vue")."');\">".$tmpUser->getLabel("all")."</a>
							<div class='objPersonDetails'>".$tmpUser->getFieldsValues(MdlUser::getDisplayMode())."</div>
						</div>
					</div>
				</div>
			</div>";
		}
		////	AUCUN CONTENU  &&  MENU DE PAGINATION
		if(empty($displayedUsers))	{echo "<div class='emptyContainer'>".Txt::trad("USER_noUser")."</div>";}
		echo MdlUser::menuPagination($usersTotalNb,"alphabet");
		?>
	</div>
</div>