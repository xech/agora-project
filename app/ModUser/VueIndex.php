<style>
#displayUsersDiv			{text-align:center; margin:10px 0px 20px;}
#displayUsersDiv select		{height:38px; font-weight:bold;}
.vInputSelectAllUsers		{color:#b00; font-weight:bold;}
.vGroupLabel				{margin-top:5px; cursor:help;}
#menuAlphabet>a				{margin-right:5px;}
.vAdminIcon					{margin-left:5px;}
</style>

<div id="pageFull">
	<div id="pageModuleMenu">
		<?= MdlUser::menuSelectObjects() ?>
		<div id="pageModMenu" class="miscContainer">
			<?php
			////	MENU "USERS DE L'ESPACE"/"TOUS LES USERS"
			if($menuDisplayUsers==true){
				echo "<div id='displayUsersDiv'>
						<select name='displayUsers' class='linkSelect' onChange=\"redir('?ctrl=user&displayUsers='+this.value)\" title=\"".Txt::trad("USER_spaceOrAllUsersTooltip")."\">
							<option value='space'>".Txt::trad("USER_spaceUsers")."</option>
							<option value='all' ".($_SESSION["displayUsers"]=='all'?'selected':null).">".Txt::trad("USER_allUsers")."</option>
						</select>
					  </div><hr>";
			}
			////	GROUPES D'UTILISATEURS (AFFICHAGE ESPACE UNIQUEMENT)
			if($_SESSION["displayUsers"]=="space" && (!empty($userGroups) || MdlUserGroup::addRight()))
			{
				$menuGroups=Txt::trad("USER_spaceGroups");
				if(MdlUserGroup::addRight())  {$menuGroups.="&nbsp; <img src='app/img/edit.png' title=\"".Txt::trad("USER_spaceGroupsEdit")."\" onclick=\"lightboxOpen('?ctrl=user&action=UserGroupEdit');\">";}
				foreach($userGroups as $tmpGroup)  {$menuGroups.="<div class='vGroupLabel' title=\"".Txt::tooltip($tmpGroup->usersLabel)."\"><img src='app/img/user/accessGroup.png'> ".$tmpGroup->title."</div>";}
				echo "<div class='menuLine'>
						<div class='menuIcon'><img src='app/img/user/userGroup.png'></div>
						<div>".$menuGroups."</div>
					  </div><hr>";
			}
			////	AJOUTER UN UTILISATEUR  /  AFFECTER UN USER EXISTANT A L'ESPACE  /  ENVOYER DES INVITATIONS  /  IMPORTER DES UTILISATEURS  /  ENVOI DES COORDONNEES DE CONNEXION
			if(self::$curUser->isSpaceAdmin())			{echo "<div class='menuLine' onclick=\"lightboxOpen('".MdlUser::getUrlNew()."');\" title=\"".Txt::trad($_SESSION["displayUsers"]=='all'?'USER_addUserSite':'USER_addUserSpace')."\"><div class='menuIcon'><img src='app/img/plus.png'></div><div>".Txt::trad("USER_addUser")."</div></div>";}
			if(self::$curUser->isSpaceAdmin() && self::$curSpace->allUsersAffected()==false)  {echo "<div class='menuLine' onclick=\"lightboxOpen('?ctrl=user&action=AffectUsers');\" title=\"".Txt::trad("USER_addExistUserTitle")."\"><div class='menuIcon'><img src='app/img/plus.png'></div><div>".Txt::trad("USER_addExistUser")."</div></div>";}
			if(Ctrl::$curUser->sendInvitationRight())	{echo "<div class='menuLine' onclick=\"lightboxOpen('?ctrl=user&action=SendInvitation');\" title=\"".Txt::trad("USER_sendInvitationTooltip")."\"><div class='menuIcon'><img src='app/img/mail.png'></div><div>".Txt::trad("USER_sendInvitation")."</div></div>";}
			if(self::$curUser->isSpaceAdmin())			{echo "<div class='menuLine' onclick=\"lightboxOpen('?ctrl=user&action=EditPersonsImportExport');\"><div class='menuIcon'><img src='app/img/dataImportExport.png'></div><div>".Txt::trad("importExport_user")."</div></div>";}
			if(self::$curUser->isGeneralAdmin())		{echo "<div class='menuLine' onclick=\"lightboxOpen('?ctrl=user&action=ResetPasswordSendMailUsers');\" title=\"".Txt::trad("USER_sendCoordsTooltip")."\"><div class='menuIcon'><img src='app/img/user/connection.png'></div><div>".Txt::trad("USER_sendCoords")."</div></div>";}
			////	TYPE D'AFFICHAGE / TRI D'AFFICHAGE
			echo "<hr>".MdlUser::menuDisplayMode().MdlUser::menuSort();
			////	FILTRAGE ALPHABET
			$menuAlphabet=null;
			foreach($alphabetList as $tmpLetter)  {$menuAlphabet.="<a href=\"?ctrl=user&alphabet=".$tmpLetter."\" ".(Req::param("alphabet")==$tmpLetter?"class='linkSelect'":null).">".$tmpLetter."</a>";}
			$menuAlphabet.="&nbsp; <a href='?ctrl=user' ".(Req::isParam("alphabet")==false?"class='linkSelect'":null).">".Txt::trad("displayAll")."</a>";
			echo "<div class='menuLine sLink'>
					<div class='menuIcon'><img src='app/img/alphabet.png'></div>
					<div><div class='menuLaunch' for='menuAlphabet'>".Txt::trad("alphabetFilter").(Req::isParam("alphabet")?" : ".Req::param("alphabet"):null)."</div><div id='menuAlphabet' class='menuContext'>".$menuAlphabet."</div></div>
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
			//Icone "admin general" OU Icone"admin space"
			if($tmpUser->isGeneralAdmin())		{$adminIcon='<img src="app/img/user/userAdminGeneral.png" title="'.Txt::trad("USER_adminGeneral").'" class="vAdminIcon">';}
			elseif($tmpUser->isSpaceAdmin())	{$adminIcon='<img src="app/img/user/userAdminSpace.png" title="'.Txt::trad("USER_adminSpace").'" class="vAdminIcon">';}
			else								{$adminIcon=null;}
			//Affiche le block
			echo $tmpUser->objContainer("objPerson").$tmpUser->contextMenu().
				"<div class='objContentScroll'>
					<div class='objContent'>
						<div class='objIcon'>".$tmpUser->personImg(true,false,true)."</div>
						<div class='objLabel' onclick=\"lightboxOpen('".$tmpUser->getUrl("vue")."')\">
							".$tmpUser->getLabel("full").$adminIcon."
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