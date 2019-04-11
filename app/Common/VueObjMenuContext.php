<?php
////	 LAUNCHER INLINE/BLOCK DU MENU CONTEXT
if(!empty($inlineLauncher))	{echo "<img src='app/img/menuSmall.png' for=\"".$curObj->menuId("objMenu")."\" src='app/img/menuSmall.png' class='menuLaunch'>";}
else						{echo "<img src='app/img/".(!empty($newObjectSinceConnection)?"menuNew.png":"menu.png")."' for=\"".$curObj->menuId("objMenu")."\" class='menuLaunch objMenuBurger'>";}

////	MENU CONTEXTUEL
echo "<div id=\"".$curObj->menuId("objMenu")."\" class='menuContext'>";

	////	RESPONSIVE : LABEL DE L'OBJET
	if(Req::isMobile())	{echo "<div class='infos'>".$curObj->getLabel()."</div><br>";}

	////	SELECTION/DESELECTION
	if($curObj::isSelectable && $curObj::objectType!="user" && empty($inlineLauncher) && Req::isMobile()==false)
		{$mainMenu=true;	echo "<div class='menuLine sLink' onclick=\"objSelect('".$curObj->menuId("objBlock")."')\"><div class='menuIcon'><img src='app/img/check.png'></div><div>".Txt::trad("selectUnselect")."</div></div>";}

	////	MODIFIER
	if(!empty($editLabel))
		{$mainMenu=true;	echo "<div class='menuLine sLink' onclick=\"lightboxOpen('".$curObj->getUrl("edit")."')\"><div class='menuIcon'><img src='app/img/edit.png'></div><div>".$editLabel."</div></div>";}

	////	USER : MODIF MESSENGER
	if(!empty($editMessengerObjUrl))
		{$mainMenu=true;	echo "<div class='menuLine sLink' onclick=\"lightboxOpen('".$editMessengerObjUrl."')\"><div class='menuIcon'><img src='app/img/messenger.png'></div><div>".Txt::trad("USER_messengerEdit")."</div></div>";}

	////	CHANGER DE DOSSIER
	if(!empty($moveObjectUrl))
		{$mainMenu=true;	echo "<div class='menuLine sLink' onclick=\"lightboxOpen('".$moveObjectUrl."')\"><div class='menuIcon'><img src='app/img/folder/folderMove.png'></div><div>".Txt::trad("changeFolder")."</div></div>";}

	////	HISTORIQUE/LOGS
	if(!empty($logUrl))
		{$mainMenu=true;	echo "<div class='menuLine sLink' onclick=\"lightboxOpen('".$logUrl."')\"><div class='menuIcon'><img src='app/img/log.png'></div><div>".Txt::trad("objHistory")."</div></div>";}

	////	USER : SUPPRIMER DE L'ESPACE
	if(!empty($confirmDeleteFromSpace))
		{$mainMenu=true;	echo "<div class='menuLine sLink' onclick=\"".$confirmDeleteFromSpace."\"><div class='menuIcon'><img src='app/img/delete.png'></div><div>".Txt::trad("USER_deleteFromSpace")."</div></div>";}

	////	SUPPRIMER
	if(!empty($deleteLabel))
		{$mainMenu=true;	echo "<div class='menuLine sLink' onclick=\"".$confirmDeleteJs."\"><div class='menuIcon'><img src='app/img/delete.png'></div><div>".$deleteLabel."</div></div>";}

	////	SEPARATEUR
	if(!empty($mainMenu))	{echo "<hr>";}

	////	DIVERSES OPTIONS (MENU PRINCIPAL)
	foreach($specificOptions as $tmpOption){
		$actionJsTmp=(!empty($tmpOption["actionJs"])) ?  'onclick="'.$tmpOption["actionJs"].'"'  :  null;
		$tooltipTmp =(!empty($tmpOption["tooltip"]))  ?  'title="'.$tmpOption["tooltip"].'"'  :  null;
		$iconSrcTmp =(!empty($tmpOption["iconSrc"]))  ?  '<div class="menuIcon"><img src="app/img/'.$tmpOption["iconSrc"].'"></div>'  :  null;
		if(empty($iconSrcTmp))  {$tmpOption["label"]="<span class='menuLineSpecificLabel'>".$tmpOption["label"]."</span>";}
		echo "<div class='menuLine sLink' ".$actionJsTmp." ".$tooltipTmp.">".$iconSrcTmp."<div>".$tmpOption["label"]."</div></div>";
	}

	////	SEPARATEUR
	if(!empty($specificOptions))	{echo "<hr>";}

	////	DOSSIER : CONTENU (nombre d'elements + taille (module fichiers))
	if($curObj::isFolder==true)
		{echo "<div class='menuLine'><div class='menuTxtLeft'>".Txt::trad("folderContent")."</div><div>".$curObj->folderContentDescription()."</div></div><hr>";}

	////	USER : ESPACES AFFECTES A L'UTILISATEUR
	if(!empty($userSpaceList))
		{echo "<div class='menuLine'><div class='menuIcon'><img src='app/img/space.png'></div><div>".$userSpaceList."</div></div>";}

	////	ADMIN USER
	if($curObj::objectType=="user" && $curObj->isAdminSpace()){
		if($curObj->isAdminGeneral())	{echo "<div class='menuLine'><div class='menuIcon'><img src='app/img/user/adminGeneral.png'></div><div>".Txt::trad("USER_adminGeneral")."</div></div>";}
		else							{echo "<div class='menuLine'><div class='menuIcon'><img src='app/img/user/adminSpace.png'></div><div>".Txt::trad("USER_adminSpace")."</div></div>";}
	}

	////	AFFECTATIONS
	if(!empty($affectLabels["2"]))		{echo "<div class='menuLine sAccessWrite cursorHelp' title=\"".$affectTooltips["2"]."\"><div class='menuTxtLeft'>".Txt::trad("accessWrite")."</div><div>".$affectLabels["2"]."</div></div>";}
	if(!empty($affectLabels["1.5"]))	{echo "<div class='menuLine sAccessWriteLimit cursorHelp' title=\"".$affectTooltips["1.5"]."\"><div class='menuTxtLeft'>".Txt::trad("accessWriteLimit")."</div><div>".$affectLabels["1.5"]."</div></div>";}
	if(!empty($affectLabels["1"]))		{echo "<div class='menuLine sAccessRead cursorHelp' title=\"".$affectTooltips["1"]."\"><div class='menuTxtLeft'>".Txt::trad("accessRead")."</div><div>".$affectLabels["1"]."</div></div>";}

	////	SEPARATEUR
	if(!empty($affectLabels))  {echo "<hr>";}

	////	AUTEUR CREATION + DATE
	if(!empty($infosCrea))
		{echo "<div class='menuLine sLink' ".(!empty($curObj->_idUser)?Ctrl::getObj("user",$curObj->_idUser)->userVueHref():null)."><div class='menuTxtLeft'>".Txt::trad("creation")."</div><div class='menuTxtRight'>".$infosCrea["autor"].$infosCrea["date"]."</div></div>";}

	////	AUTEUR MODIF + DATE
	if(!empty($infosModif))
		{echo "<div class='menuLine sLink' ".Ctrl::getObj("user",$curObj->_idUserModif)->userVueHref()."><div class='menuTxtLeft'>".Txt::trad("modification")."</div><div>".$infosModif["autor"].$infosModif["date"]."</div></div>";}
	
	////	ICONE "NOUVEL ELEMENT"
	if(!empty($newObjectSinceConnection))
		{echo "<hr><div class='menuLine' title=\"".Txt::trad("objNewInfos")."\"><div class='menuIcon'><img src='app/img/newObj.png'></div><div><abbr>".Txt::trad("objNew")."</abbr></div></div>";}

	////	FICHIERS JOINTS
	echo $curObj->menuAttachedFiles();

echo "</div>";


////	LIKES / COMMENTAIRES / FICHIERS JOINTS / ACCÈS PERSO / ADMIN USER / CHECKBOX HIDDEN
if(empty($inlineLauncher))
{
	echo "<div class='objMiscMenus ".(!empty($hideMiscMenu)?"hideMiscMenu":null)."'>";
		//LIKES (ET "DISLIKES" ?)
		if(!empty($likeMenu)){
			foreach($likeMenu as $likeOption=>$likeValues){
				echo "<div class='objMiscMenuDiv objMenuLikeComment sLink' id='".$likeValues["menuId"]."' onclick=\"usersLikeValidate('".$curObj->_targetObjId."','".$likeOption."')\" title=\"".$curObj->getUsersLikeTooltip($likeOption)."\">
						<div class='objMiscMenuCircle ".(empty($likeValues["likeDontLikeNb"])?"objMiscMenuCircleHide":null)."'>".$likeValues["likeDontLikeNb"]."</div>
						<img src='app/img/usersLike_".$likeOption.".png'>
					 </div>";
			}
		}
		//COMMENTAIRES
		if(!empty($commentMenu)){
			echo "<div class='objMiscMenuDiv objMenuLikeComment sLink' id='".$commentMenu["menuId"]."' onclick=\"lightboxOpen('".$commentMenu["commentsUrl"]."')\" title=\"".$commentMenu["commentTooltip"]."\">
					<div class='objMiscMenuCircle ".(empty($commentMenu["commentNb"])?"objMiscMenuCircleHide":null)."'>".$commentMenu["commentNb"]."</div>
					<img src='app/img/usersComment.png'>
				 </div>";
		}
		//FICHIERS JOINTS
		if($curObj->menuAttachedFiles()){
			echo "<div class='objMiscMenuDiv menuLaunch' for=\"".$curObj->menuId("objAttachment")."\"><img src='app/img/attachment.png'></div>
				  <div class='menuContext' id=\"".$curObj->menuId("objAttachment")."\">".$curObj->menuAttachedFiles(null)."</div>";
		}
		//ACCÈS PERSO
		if(!empty($isPersoAccess))	{echo "<div class='objMiscMenuDiv'><img src='app/img/user/user.png' title=\"".Txt::trad("personalAccess")."\"></div>";}
		//ADMIN USER
		if($curObj::objectType=="user"){
			if($curObj->isAdminGeneral())		{echo "<div class='objMiscMenuDiv'><img src='app/img/user/adminGeneral.png' title=\"".Txt::trad("USER_adminGeneral")."\" class='cursorHelp'></div>";}
			elseif($curObj->isAdminSpace())	{echo "<div class='objMiscMenuDiv'><img src='app/img/user/adminSpace.png' title=\"".Txt::trad("USER_adminSpace")."\" class='cursorHelp'></div>";}
		}
		//CHECKBOX DE SÉLECTION DE L'OBJET  (HIDDEN)
		if($curObj::isSelectable)	{echo "<input type='checkbox' name='targetObjects[]' value=\"".$curObj->_targetObjId."\" id=\"".$curObj->menuId("objBlock")."_selectBox\">";}
	echo "</div>";
}