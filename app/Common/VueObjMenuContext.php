<?php
////	 ICONE "BURGER" : LAUNCHER DU MENU CONTEXT
$iconBurgerImg=(!empty($isNewObject))  ?  "menuNew"  :  "menu";									//Icone de nouvel objet?
if(stristr($iconBurger,"small"))  {$iconBurgerImg.="Small";}									//Petite icone?
$iconBurgerClass=(stristr($iconBurger,"inline"))  ?  "objMenuBurgerInline"  :  "objMenuBurger";	//Affichage "inline" ou "float" (par défaut : position absolute à droite)
echo "<img src='app/img/".$iconBurgerImg.".png' for=\"".$curObj->uniqId("objMenu")."\" class='menuLaunch ".$iconBurgerClass."'>";


////	MENU CONTEXTUEL
echo "<div id=\"".$curObj->uniqId("objMenu")."\" class='menuContext'>";

	////	RESPONSIVE : LABEL DE L'OBJET
	if(Req::isMobile())  {echo "<div class='infos'>".$curObj->getLabel()."</div>";}
	////	SÉLECTION DE L'OBJET (si l'objet sélectionnable + s'assurer que le menu de sélection a bien été affiché + on n'affiche pas le conteneur courant)
	if($curObj::isSelectable && Ctrl::$isMenuSelectObjects==true && (empty(Ctrl::$curContainer) || Ctrl::$curContainer->_typeId!=$curObj->_typeId))
	  {echo $curObj->objSelectCheckbox()."<div class='menuLine sLink' onclick=\"objSelect('".$curObj->uniqId("objCheckbox")."')\"><div class='menuIcon'><img src='app/img/check.png'></div><div>".Txt::trad("selectUnselect")."</div></div>";}

	////	MODIFIER L'OBJET
	if(!empty($editLabel))  {echo "<div class='menuLine sLink' onclick=\"lightboxOpen('".$curObj->getUrl("edit")."')\"><div class='menuIcon'><img src='app/img/edit.png'></div><div>".$editLabel."</div></div>";}

	////	COPIER L'ADRESSE/URL D'ACCES (affiche puis masque l'input pour pouvoir être copié..)
	echo "<div class='menuLine sLink' title=\"".Txt::trad("copyUrlInfo")."\" onclick=\"$(this).find('input').show().select();document.execCommand('copy');$(this).find('input').hide();notify('".Txt::trad("copyUrlConfirmed",true)."');\"><div class='menuIcon'><img src='app/img/link.png'></div><div>".Txt::trad("copyUrl")."<input type='text' value=\"".$curObj->getUrlExternal()."\" style='display:none'></div></div>";

	////	CHANGER DE DOSSIER
	if(!empty($moveObjectUrl))  {echo "<div class='menuLine sLink' onclick=\"lightboxOpen('".$moveObjectUrl."')\"><div class='menuIcon'><img src='app/img/folder/folderMove.png'></div><div>".Txt::trad("changeFolder")."</div></div>";}

	////	HISTORIQUE/LOGS
	if(!empty($logUrl))  {echo "<div class='menuLine sLink' onclick=\"lightboxOpen('".$logUrl."')\"><div class='menuIcon'><img src='app/img/log.png'></div><div>".Txt::trad("objHistory")."</div></div>";}

	////	OPTIONS SPECIFIQUES (surcharge "contextMenu()") : METTRE JUSTE AVANT L'OPTION DE SUPPRESSION
	foreach($specificOptions as $tmpOption){
		$actionJsTmp=(!empty($tmpOption["actionJs"])) ?  'onclick="'.$tmpOption["actionJs"].'"'  :  null;
		$tooltipTmp =(!empty($tmpOption["tooltip"]))  ?  'title="'.$tmpOption["tooltip"].'"'  :  null;
		$menuIconTmp=(!empty($tmpOption["iconSrc"]))  ?  '<div class="menuIcon"><img src="app/img/'.$tmpOption["iconSrc"].'"></div>'  :  null;
		echo "<div class='menuLine sLink' ".$actionJsTmp." ".$tooltipTmp.">".$menuIconTmp."<div>".$tmpOption["label"]."</div></div>";
	}

	////	SUPPRIMER L'OBJET (afficher en dernier)
	if(!empty($deleteLabel))  {echo "<div class='menuLine sLink' onclick=\"".$confirmDeleteJs."\"><div class='menuIcon'><img src='app/img/delete.png'></div><div>".$deleteLabel."</div></div>";}

	////	LABELS SPECIFIQUES (Ex: "Agenda affecté à Bob, Will")
	foreach($specificLabels as $tmpLabel){
		$tooltipTmp =(!empty($tmpLabel["tooltip"]))  ?  'title="'.$tmpLabel["tooltip"].'"'  :  null;
		echo "<hr><div class='menuLine menuContextSpecificLabels' ".$tooltipTmp.">".$tmpLabel["label"]."</div>";
	}

	////	OBJET USER : EDIT DU MESSENGER / SUPPRIMER DE L'ESPACE / ESPACES AFFECTES A L'USER
	if(!empty($editMessengerObjUrl))		{echo "<div class='menuLine sLink' onclick=\"lightboxOpen('".$editMessengerObjUrl."')\"><div class='menuIcon'><img src='app/img/messengerSmall.png'></div><div>".Txt::trad("USER_messengerEdit2")."</div></div>";}
	if(!empty($deleteFromCurSpaceConfirm))	{echo "<div class='menuLine sLink' onclick=\"".$deleteFromCurSpaceConfirm."\"><div class='menuIcon'><img src='app/img/delete.png'></div><div>".Txt::trad("USER_deleteFromCurSpace")."</div></div>";}
	if(!empty($userSpaceList))				{echo "<hr><div class='menuLine'><div class='menuIcon'><img src='app/img/space.png'></div><div>".$userSpaceList."</div></div>";}

	////	OBJET DOSSIER : CONTENU DU DOSSIER (nb d'elements & co)
	if($curObj::isFolder==true)  {echo "<hr><div class='menuLine'><div class='menuContextTxtLeft'>".Txt::trad("folderContent")."</div><div>".$curObj->folderContentDescription()."</div></div>";}

	////	AUTEUR ET DATE DE CREATION/MODIF
	if(!empty($autorDateCrea)){
		echo "<hr>";
		if(!empty($autorDateCrea))	{echo "<div class='menuLine'><div class='menuContextTxtLeft'>".Txt::trad("createBy")."</div><div>".$autorDateCrea."</div></div>";}
		if(!empty($autorDateModif))	{echo "<div class='menuLine'><div class='menuContextTxtLeft'>".Txt::trad("modifBy")."</div><div>".$autorDateModif."</div></div>";}
	}

	////	AFFECTATIONS ET DROITS D'ACCES
	if(!empty($affectLabels)){
		echo "<hr>";
		if(!empty($affectLabels["2"]))		{echo "<div class='menuLine sAccessWrite' title=\"".$affectTooltips["2"]."\"><div class='menuContextTxtLeft'><abbr>".Txt::trad("accessWrite")."</abbr></div><div>".$affectLabels["2"]."</div></div>";}
		if(!empty($affectLabels["1.5"]))	{echo "<div class='menuLine sAccessWriteLimit' title=\"".$affectTooltips["1.5"]."\"><div class='menuContextTxtLeft'><abbr>".Txt::trad("accessWriteLimit")."</abbr></div><div>".$affectLabels["1.5"]."</div></div>";}
		if(!empty($affectLabels["1"]))		{echo "<div class='menuLine sAccessRead' title=\"".$affectTooltips["1"]."\"><div class='menuContextTxtLeft'><abbr>".Txt::trad("accessRead")."</abbr></div><div>".$affectLabels["1"]."</div></div>";}
	}

	////	LISTE DES FICHIERS JOINTS
	echo $curObj->attachedFileMenu();

echo "</div>";


////	MENU BURGER PRINCIPAL : LIKES / COMMENTAIRES / FICHIERS JOINTS / ACCÈS PERSO
if($iconBurger=="floatBig")
{
	echo "<div class='objMiscMenus'>";
		//ICONE DES LIKES (+ "DISLIKES" ?)
		if(!empty($likeMenu)){
			foreach($likeMenu as $likeOption=>$likeValues){
				echo "<div class='objMiscMenuDiv objMenuLikeComment sLink ".$showMiscMenuClass."' id='".$likeValues["menuId"]."' onclick=\"usersLikeValidate('".$curObj->_typeId."','".$likeOption."')\" title=\"".Txt::tooltip($curObj->getUsersLikeTooltip($likeOption))."\">
						<div class='menuCircle ".(empty($likeValues["likeDontLikeNb"])?"menuCircleHide":null)."'>".$likeValues["likeDontLikeNb"]."</div>
						<img src='app/img/usersLike_".$likeOption.".png'>
					 </div>";
			}
		}
		//ICONE DES COMMENTAIRES
		if(!empty($commentMenu)){
			echo "<div class='objMiscMenuDiv objMenuLikeComment sLink ".$showMiscMenuClass."' id='".$commentMenu["menuId"]."' onclick=\"lightboxOpen('".$commentMenu["commentsUrl"]."')\" title=\"".Txt::tooltip($commentMenu["commentTooltip"])."\">
					<div class='menuCircle ".(empty($commentMenu["commentNb"])?"menuCircleHide":null)."'>".$commentMenu["commentNb"]."</div>
					<img src='app/img/usersComment.png'>
				 </div>";
		}
		//ICONE DES FICHIERS JOINTS
		if($curObj->attachedFileMenu()){
			echo "<div class='objMiscMenuDiv menuLaunch' for=\"".$curObj->uniqId("objAttachment")."\"><img src='app/img/attachment.png'></div>
				  <div class='menuContext' id=\"".$curObj->uniqId("objAttachment")."\">".$curObj->attachedFileMenu(null)."</div>";
		}
		//ICONE D'ACCÈS PERSO
		if(!empty($isPersoAccess))
			{echo "<div class='objMiscMenuDiv'><img src='app/img/user/accessUser.png' title=\"".Txt::trad("personalAccess")."\"></div>";}
	echo "</div>";
}