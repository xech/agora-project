<?php
////	 LAUNCHER DU MENU CONTEXTUEL
$launcherIcon=(empty($options["launcherIcon"]))  ?  "floatBig"  :  $options["launcherIcon"];	//Options du menu burger :  "floatBig" (défault)  /  "floatSmall"  /  "inlineBig"  /  "inlineSmall"
$burgerIcon=($curObj->isRecent())  ?  "menuNew"  :  "menu";										//Nouvel objet : "menuNew"
if(stristr($launcherIcon,"small"))	{$burgerIcon.="Small";}										//Taille du menu burger : "small" / "Big"
$launcherFloat=(stristr($launcherIcon,"float"))  ?  "objMenuContextFloat"  :  null;				//Menu burger "float" : position absolute à droite
$launcherLabel=(!empty($options["launcherLabel"]))  ?  $options["launcherLabel"]  :  null;		//Text/label ajouté au menu burger (cf. agendas)
echo '<span for="'.$objMenuId.'" class="menuLaunch '.$launcherFloat.'"><img src="app/img/'.$burgerIcon.'.png" '.Txt::tooltip("menuOptions").'> '.$launcherLabel.'</span>';


////	MENU CONTEXTUEL
echo '<div id="'.$objMenuId.'" class="menuContext">';

	////	LABEL DE L'OBJET
	echo '<div class="menuContextLabel" id="'.$curObj->uniqId("objLabel").'">'.$curObj->getLabel().'</div><hr>';

	////	MODIFIER L'OBJET
	if(!empty($editLabel))  {echo '<div class="menuLine" onclick="lightboxOpen(\''.$curObj->getUrl("edit").'\')"><div class="menuIcon"><img src="app/img/edit.png"></div><div>'.$editLabel.'</div></div>';}

	////	SÉLECTION DE L'OBJET
	if($curObj->isSelectable()){
		echo '<div class="menuLine" onclick="objSelectSwitch(\''.$curObj->uniqId("objCheckbox").'\')"><div class="menuIcon"><img src="app/img/check.png"></div><div>'.Txt::trad("selectUnselect").'</div></div>
			  <input type="checkbox" name="objectsTypeId[]" class="objSelectCheckbox" value="'.$curObj->_typeId.'" id="'.$curObj->uniqId("objCheckbox").'">';
	}

	////	CHANGER DE DOSSIER
	if(!empty($moveObjectUrl))  {echo '<div class="menuLine" onclick="lightboxOpen(\''.$moveObjectUrl.'\')"><div class="menuIcon"><img src="app/img/folder/folderMove.png"></div><div>'.Txt::trad("changeFolder").'</div></div>';}

	////	HISTORIQUE/LOGS
	if(!empty($logUrl))  {echo '<div class="menuLine" onclick="lightboxOpen(\''.$logUrl.'\')"><div class="menuIcon"><img src="app/img/log.png"></div><div>'.Txt::trad("objHistory").'</div></div>';}

	////	COPIER L'ADRESSE/URL D'ACCES (affiche puis masque l'input pour pouvoir être copié..)
	if(!empty($getUrlExternal))  {echo '<div class="menuLine" '.Txt::tooltip("copyUrlTooltip").' onclick="$(this).find(\'input\').show().select();document.execCommand(\'copy\');$(this).find(\'input\').hide();notify(\''.Txt::trad("copyUrlNotif",true).'\')"><div class="menuIcon"><img src="app/img/link.png"></div><div>'.Txt::trad("copyUrl").'<input type="text" value="'.$getUrlExternal.'" style="display:none"></div></div>';}

	////	OPTIONS SPECIFIQUES (surcharge "contextMenu()") : METTRE JUSTE AVANT L'OPTION DE SUPPRESSION
	if(!empty($options["specificOptions"])){
		foreach($options["specificOptions"] as $tmpOption){
			$actionJsTmp=(!empty($tmpOption["actionJs"])) ?  'onclick="'.$tmpOption["actionJs"].'"'  :  null;
			$tooltipTmp =(!empty($tmpOption["tooltip"]))  ?  Txt::tooltip($tmpOption["tooltip"])  :  null;
			$menuIconTmp=(!empty($tmpOption["iconSrc"]))  ?  '<div class="menuIcon"><img src="app/img/'.$tmpOption["iconSrc"].'"></div>'  :  null;
			echo '<div class="menuLine" '.$actionJsTmp.' '.$tooltipTmp.'>'.$menuIconTmp.'<div>'.$tmpOption["label"].'</div></div>';
		}
	}

	////	SUPPRIMER L'OBJET (dernière option)
	if(!empty($deleteLabel))  {echo '<div class="menuLine" onclick="'.$confirmDeleteJs.'"><div class="menuIcon"><img src="app/img/delete.png"></div><div>'.$deleteLabel.'</div></div>';}

	////	LABELS SPECIFIQUES (Ex: "Agenda affecté à Bob, Will")
	if(!empty($options["specificLabels"])){
		foreach($options["specificLabels"] as $tmpLabel){
			$tooltipTmp =(!empty($tmpLabel["tooltip"]))  ?  Txt::tooltip($tmpLabel["tooltip"])  :  null;
			echo '<hr><div class="menuLine menuContextSpecificLabels" '.$tooltipTmp.'>'.$tmpLabel["label"].'</div>';
		}
	}

	////	OBJET USER : EDIT DU MESSENGER / SUPPRIMER DE L'ESPACE / ESPACES AFFECTES A L'USER
	if(!empty($userEditMessengerUrl))		{echo '<div class="menuLine" onclick="lightboxOpen(\''.$userEditMessengerUrl.'\')"><div class="menuIcon"><img src="app/img/messenger.png"></div><div>'.Txt::trad("USER_livecounterVisibility").'</div></div>';}
	if(!empty($deleteFromCurSpaceConfirm))	{echo '<div class="menuLine" onclick="'.$deleteFromCurSpaceConfirm.'"><div class="menuIcon"><img src="app/img/delete.png"></div><div>'.Txt::trad("USER_deleteFromCurSpace").'</div></div>';}
	if(!empty($userSpaceList))				{echo '<hr><div class="menuLine"><div class="menuIcon"><img src="app/img/space.png"></div><div>'.$userSpaceList.'</div></div>';}

	////	OBJET DOSSIER : CONTENU DU DOSSIER (nb d'elements & co)
	if($curObj::isFolder==true)  {echo '<hr><div class="menuLine"><div class="menuContextTxtLeft">'.Txt::trad("folderContent").'</div><div>'.$curObj->contentDescription().'</div></div>';}

	////	AUTEUR ET DATE DE CREATION/MODIF
	if(!empty($autorDateCrea)){
		echo '<hr>';
		if(!empty($autorDateCrea))	{echo '<div class="menuLine"><div class="menuContextTxtLeft">'.Txt::trad("createdBy").'</div><div>'.$autorDateCrea.'</div></div>';}
		if(!empty($autorDateModif))	{echo '<div class="menuLine"><div class="menuContextTxtLeft">'.Txt::trad("modifBy").'</div><div>'.$autorDateModif.'</div></div>';}
		if($curObj->isRecent())		{echo '<div class="menuLine" '.Txt::tooltip("objNewTooltip").'><div class="menuContextTxtLeft">&nbsp;</div><div>'.Txt::trad("objNew").'&nbsp;<img src="app/img/menuNewSmall.png"></div></div>';}
	}

	////	AFFECTATIONS ET DROITS D'ACCES
	if(!empty($affectLabels)){
		echo '<hr>';
		if(!empty($affectLabels["2"]))		{echo '<div class="menuLine sAccessWrite" '.Txt::tooltip($affectTooltips["2"]).'><div class="menuContextTxtLeft">'.Txt::trad("accessWrite").'</div><div>'.$affectLabels["2"].'</div></div>';}
		if(!empty($affectLabels["1.5"]))	{echo '<div class="menuLine sAccessWriteLimit" '.Txt::tooltip($affectTooltips["1.5"]).'><div class="menuContextTxtLeft">'.Txt::trad("accessWriteLimit").'</div><div>'.$affectLabels["1.5"].'</div></div>';}
		if(!empty($affectLabels["1"]))		{echo '<div class="menuLine sAccessRead" '.Txt::tooltip($affectTooltips["1"]).'><div class="menuContextTxtLeft">'.Txt::trad("accessRead").'</div><div>'.$affectLabels["1"].'</div></div>';}
	}

	////	LISTE DES FICHIERS JOINTS
	echo $curObj->attachedFileMenu();

echo '</div>';


////	MENU BURGER PRINCIPAL : LIKES / COMMENTAIRES / FICHIERS JOINTS / ACCÈS PERSO
if($launcherIcon=="floatBig")
{
	echo '<div class="objMiscMenus">';
		//MENU DES COMMENTAIRES
		if($curObj->hasUsersComment()){
			$commentNb=count($curObj->getUsersComment());
			$commentTooltip=$commentNb." ".Txt::trad($commentNb>1?"AGORA_usersComments":"AGORA_usersComment")." : ".Txt::trad("commentAdd");
			$commentOnclick="lightboxOpen('?ctrl=object&action=UsersComment&typeId=".$curObj->_typeId."')";
			echo '<div class="objMiscMenuDiv '.(empty($commentNb)?"hideMiscMenu":null).'" id="usersComment_'.$curObj->_typeId.'" onclick="'.$commentOnclick.'" '.Txt::tooltip($commentTooltip).'>
					<span class="circleNb">'.(!empty($commentNb)?$commentNb:null).'</span>
					<img src="app/img/usersComment.png">
				  </div>';
		}
		//MENU DES LIKES
		if($curObj->hasUsersLike()){
			$likeNb=count($curObj->getUsersLike());
			$likeOnclick="usersLikeUpdate('".$curObj->_typeId."')";
			echo '<div class="objMiscMenuDiv '.(empty($likeNb)?"hideMiscMenu":null).'" id="usersLike_'.$curObj->_typeId.'" onclick="'.$likeOnclick.'" '.Txt::tooltip($curObj->usersLikeTooltip()).'>
					<span class="circleNb">'.(!empty($likeNb)?$likeNb:null).'</span>
					<img src="app/img/usersLike.png">
				  </div>';
		}
		//ICONE DES FICHIERS JOINTS
		if($curObj->attachedFileMenu()){
			echo '<div class="objMiscMenuDiv menuLaunch" for="'.$curObj->uniqId("objAttachment").'"><img src="app/img/attachment.png"></div>
				  <div class="menuContext" id="'.$curObj->uniqId("objAttachment").'">'.$curObj->attachedFileMenu(null).'</div>';
		}
		//ICONE D'ACCÈS PERSO
		if(!empty($isPersoAccess))
			{echo '<div class="objMiscMenuDiv"><img src="app/img/user/accessUser.png" '.Txt::tooltip("personalAccess").'></div>';}
	echo '</div>';
}