<?php
////	 ICONE "BURGER" : LAUNCHER DU MENU CONTEXT
$iconBurgerName=(!empty($isNewObject))  ?  "menuNew"  :  "menu";//nouvel objet?
if($iconBurger=="small")  {$iconBurgerName.="Small";}//petite icone?
$iconBurgerClass=($iconBurger=="float")  ?  "objMenuBurger"  :  "objMenuBurgerInline";//affichage "float" (par défaut) ou "inline"
echo "<img src='app/img/".$iconBurgerName.".png' for=\"".$curObj->menuId("objMenu")."\" class='menuLaunch ".$iconBurgerClass."'>";


////	MENU CONTEXTUEL
echo "<div id=\"".$curObj->menuId("objMenu")."\" class='menuContext'>";

	////	RESPONSIVE : LABEL DE L'OBJET
	if(Req::isMobile())  {echo "<div class='infos'>".$curObj->getLabel()."</div>";}

	////	SELECTION DE L'OBJET, VIA UNE CHECKBOX "HIDDEN" (Pas sur mobile et pour le dossier courant)
	if($curObj::isSelectable && Req::isMobile()==false && ($curObj::isFolder==false || Ctrl::$curContainer->_targetObjId!=$curObj->_targetObjId))  {echo "<div class='menuLine sLink' onclick=\"objSelect('".$curObj->menuId("objBlock")."')\"><div class='menuIcon'><img src='app/img/check.png'></div><div>".Txt::trad("selectUnselect")."</div></div>".$curObj->targetObjectsInput();}

	////	MODIFIER L'OBJET
	if(!empty($editLabel))  {echo "<div class='menuLine sLink' onclick=\"lightboxOpen('".$curObj->getUrl("edit")."')\"><div class='menuIcon'><img src='app/img/edit.png'></div><div>".$editLabel."</div></div>";}

	////	CHANGER L'OBJET DE DOSSIER
	if(!empty($moveObjectUrl))  {echo "<div class='menuLine sLink' onclick=\"lightboxOpen('".$moveObjectUrl."')\"><div class='menuIcon'><img src='app/img/folder/folderMove.png'></div><div>".Txt::trad("changeFolder")."</div></div>";}

	////	COPIER L'ADRESSE/URL D'ACCES DE L'OBJET (affiche puis masque l'input pour être copié)
	if(Ctrl::$curUser->isUser())  {echo "<div class='menuLine sLink' onclick=\"$(this).find('input').show().select();document.execCommand('copy');$(this).find('input').hide();notify('".Txt::trad("copyUrlConfirmed",true)."');\" title=\"".Txt::trad("copyUrlInfo")."\"><div class='menuIcon'><img src='app/img/link.png'></div><div>".Txt::trad("copyUrl")."<input type='text' value=\"".$curObj->getUrlExternal()."\" style='display:none'></div></div>";}

	////	HISTORIQUE/LOGS DE L'OBJET
	if(!empty($logUrl))  {echo "<div class='menuLine sLink' onclick=\"lightboxOpen('".$logUrl."')\"><div class='menuIcon'><img src='app/img/log.png'></div><div>".Txt::trad("objHistory")."</div></div>";}

	////	USER : EDIT DU MESSENGER
	if(!empty($editMessengerObjUrl))  {echo "<div class='menuLine sLink' onclick=\"lightboxOpen('".$editMessengerObjUrl."')\"><div class='menuIcon'><img src='app/img/messenger.png'></div><div>".Txt::trad("USER_messengerEdit2")."</div></div>";}

	////	USER : SUPPRIMER DE L'ESPACE
	if(!empty($deleteFromCurSpaceConfirm))  {echo "<div class='menuLine sLink' onclick=\"".$deleteFromCurSpaceConfirm."\"><div class='menuIcon'><img src='app/img/delete.png'></div><div>".Txt::trad("USER_deleteFromCurSpace")."</div></div>";}

	////	DIVERSES OPTIONS SPECIFIQUE (surcharge de "contextMenu()")
	foreach($specificOptions as $tmpOption){
		$actionJsTmp=(!empty($tmpOption["actionJs"])) ?  'onclick="'.$tmpOption["actionJs"].'"'  :  null;
		$tooltipTmp =(!empty($tmpOption["tooltip"]))  ?  'title="'.$tmpOption["tooltip"].'"'  :  null;
		$menuIconTmp=(!empty($tmpOption["iconSrc"]))  ?  '<div class="menuIcon"><img src="app/img/'.$tmpOption["iconSrc"].'"></div>'  :  null;
		echo "<div class='menuLine sLink' ".$actionJsTmp." ".$tooltipTmp.">".$menuIconTmp."<div>".$tmpOption["label"]."</div></div>";
	}

	////	SUPPRIMER L'OBJET (afficher en dernier)
	if(!empty($deleteLabel))  {echo "<div class='menuLine sLink' onclick=\"".$confirmDeleteJs."\"><div class='menuIcon'><img src='app/img/delete.png'></div><div>".$deleteLabel."</div></div>";}

	////	DIVERS LABELS (Exple: "Agenda affecté à Bob, Will")
	foreach($specificLabels as $tmpLabel){
		$tooltipTmp =(!empty($tmpLabel["tooltip"]))  ?  'title="'.$tmpLabel["tooltip"].'"'  :  null;
		echo "<hr><div class='menuLine specificLabels' ".$tooltipTmp.">".$tmpLabel["label"]."</div>";
	}

	////	USER : ESPACES AFFECTES A L'UTILISATEUR
	if(!empty($userSpaceList))  {echo "<hr><div class='menuLine'><div class='menuIcon'><img src='app/img/space.png'></div><div>".$userSpaceList."</div></div>";}

	////	DOSSIER : CONTENU DU DOSSIER (nb d'elements + taille du dossier pour le module "File")
	if($curObj::isFolder==true)  {echo "<hr><div class='menuLine'><div class='menuTxtLeft'>".Txt::trad("folderContent")."</div><div>".$curObj->folderContentDescription()."</div></div>";}

	////	AUTEUR/DATE DE CREATION (+ NOUVEL OBJET?)  &&  AUTEUR/DATE DE MODIF
	if(!empty($autorDateCrea))	{echo "<hr><div class='menuLine'><div class='menuTxtLeft'>".Txt::trad("createBy")."</div><div>".$autorDateCrea."</div></div>";}
	if(!empty($autorDateModif))	{echo "<hr><div class='menuLine'><div class='menuTxtLeft'>".Txt::trad("modifBy")."</div><div>".$autorDateModif."</div></div>";}

	////	AFFECTATIONS ET DROITS D'ACCES
	if(!empty($affectLabels["2"]))		{echo "<hr><div class='menuLine sAccessWrite' title=\"".$affectTooltips["2"]."\"><div class='menuTxtLeft'><abbr>".Txt::trad("accessWrite")."</abbr></div><div>".$affectLabels["2"]."</div></div>";}
	if(!empty($affectLabels["1.5"]))	{echo "<hr><div class='menuLine sAccessWriteLimit' title=\"".$affectTooltips["1.5"]."\"><div class='menuTxtLeft'><abbr>".Txt::trad("accessWriteLimit")."</abbr></div><div>".$affectLabels["1.5"]."</div></div>";}
	if(!empty($affectLabels["1"]))		{echo "<hr><div class='menuLine sAccessRead' title=\"".$affectTooltips["1"]."\"><div class='menuTxtLeft'><abbr>".Txt::trad("accessRead")."</abbr></div><div>".$affectLabels["1"]."</div></div>";}

	////	LISTE DES FICHIERS JOINTS
	echo $curObj->menuAttachedFiles();

echo "</div>";


////	ICONES FLOTTANTES :  LIKES / COMMENTAIRES / FICHIERS JOINTS / ACCÈS PERSO
if($iconBurger=="float")
{
	echo "<div class='objMiscMenus'>";
		//ICONE DES LIKES (+ "DISLIKES" ?)
		if(!empty($likeMenu)){
			foreach($likeMenu as $likeOption=>$likeValues){
				echo "<div class='objMiscMenuDiv objMenuLikeComment sLink ".$showMiscMenuClass."' id='".$likeValues["menuId"]."' onclick=\"usersLikeValidate('".$curObj->_targetObjId."','".$likeOption."')\" title=\"".$curObj->getUsersLikeTooltip($likeOption)."\">
						<div class='menuCircle ".(empty($likeValues["likeDontLikeNb"])?"menuCircleHide":null)."'>".$likeValues["likeDontLikeNb"]."</div>
						<img src='app/img/usersLike_".$likeOption.".png'>
					 </div>";
			}
		}
		//ICONE DES COMMENTAIRES
		if(!empty($commentMenu)){
			echo "<div class='objMiscMenuDiv objMenuLikeComment sLink ".$showMiscMenuClass."' id='".$commentMenu["menuId"]."' onclick=\"lightboxOpen('".$commentMenu["commentsUrl"]."')\" title=\"".$commentMenu["commentTooltip"]."\">
					<div class='menuCircle ".(empty($commentMenu["commentNb"])?"menuCircleHide":null)."'>".$commentMenu["commentNb"]."</div>
					<img src='app/img/usersComment.png'>
				 </div>";
		}
		//ICONE DES FICHIERS JOINTS
		if($curObj->menuAttachedFiles()){
			echo "<div class='objMiscMenuDiv menuLaunch' for=\"".$curObj->menuId("objAttachment")."\"><img src='app/img/attachment.png'></div>
				  <div class='menuContext' id=\"".$curObj->menuId("objAttachment")."\">".$curObj->menuAttachedFiles(null)."</div>";
		}
		//ICONE D'ACCÈS PERSO
		if(!empty($isPersoAccess))
			{echo "<div class='objMiscMenuDiv'><img src='app/img/user/user.png' title=\"".Txt::trad("personalAccess")."\"></div>";}
	echo "</div>";
}