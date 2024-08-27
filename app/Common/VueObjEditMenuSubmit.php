<script>
////	INIT
$(function(){
	////	CLICK SUR UN ONGLET DU MENU PRINCIPAL (".objMenuTab" doit avoir un "for" correspondant à au div du menu)
	$(".objMenuTab").on("click",function(){
		$(this).addClass("objMenuTabSelect");			//Sélectionne l'onglet
		$("#"+$(this).attr("for")).fadeIn();			//Affiche le menu associé
		$(".objMenuTab").not(this).each(function(){		//Réinit les autres menus
			$(this).removeClass("objMenuTabSelect");	//Déselectionne l'onglet
			$("#"+$(this).attr("for")).hide();			//Masque le menu associé
		});
	});
	//// INIT : PAR DEFAUT, SELECTIONNE LE PREMIER ONGLET
	$(".objMenuTab:first-child").trigger("click");

	////	AFFECTATIONS : CLICK LE LABEL D'UNE AFFECTATION
	$(".vSpaceTable:visible .vSpaceLabel").on("click",function(){
		//Init
		var boxRead		 ="#objectRightBox_"+this.id+"_1";
		var boxWriteLimit="#objectRightBox_"+this.id+"_15";
		var boxWrite	 ="#objectRightBox_"+this.id+"_2";
		var boxToCheck=null;
		//Bascule les checkbox : lecture / ecriture limité / écriture
		if(!$(boxRead).prop("disabled") && !$(boxRead).prop("checked") && !$(boxWriteLimit).prop("checked") && !$(boxWrite).prop("checked"))	{boxToCheck=boxRead;}		//"1" actif && tout est décochées
		else if(!$(boxWriteLimit).prop("disabled") && !$(boxWriteLimit).prop("checked") && !$(boxWrite).prop("checked"))						{boxToCheck=boxWriteLimit;}	//"1.5" actif && "1.5" décoché && "2" décoché
		else if(!$(boxWrite).prop("disabled")  &&  !$(boxWrite).prop("checked")  &&  ( ($(boxRead).prop("disabled") && $(boxWriteLimit).prop("disabled")) || ($(boxRead).prop("checked") && $(boxWriteLimit).prop("disabled")) || $(boxWriteLimit).prop("checked")))	{boxToCheck=boxWrite;}	//"2" actif && "2" décoché &&  ( ("1" inatif & "1.5" inatif) || ("1" coché & "1.5" inactif) || "1.5" coché)
		//Check la box sélectionnée (avec trigger sur la box)  OU  Uncheck toutes les boxes et Stylise toute la sélection
		if(boxToCheck!=null)	{$(boxToCheck).prop("checked",true).trigger("change");}
		else					{$("[id^=objectRightBox_"+this.id+"]").prop("checked",false).trigger("change");}
	});

	////	AFFECTATIONS : CLICK LA CHECKBOX D'UNE AFFECTATION
	$(".vSpaceTable:visible [id^=objectRightBox]").on("change",function(){
		var targetId=this.value.slice(0, this.value.lastIndexOf("_"));				//exple "1_U2_1.5" => "1_U2"
		$("[id^=objectRightBox_"+targetId+"]").not(this).prop("checked",false);		//"uncheck" les autres checkbox du "target"
		labelRightStyle();															//Style des labels
	});

	////	AFFECTATIONS : CLICK SUR "AFFICHER TOUS LES UTILISATEURS [ET ESPACES]"
	$("#showAllUsers").on("click",function(){
		$(".vSpaceTargetHide").removeClass("vSpaceTargetHide");		//Affiche tous les users et espaces masqués
		$(".vSpaceTable").addClass("fieldsetSub");					//Affiche un block distinct pour chaque espace (gris foncé)
		$(this).hide();												//Masque le menu qui vient d'être cliqué
		labelRightStyle();											//Style des labels
		lightboxResize();											//Resize le lightbox
	});
	//// INIT : AFFICHE LE MENU SI BESOIN
	$("#showAllUsers").toggle($(".vSpaceTargetHide").exist());


	////	INIT AFFECTATIONS : PAR DEFAUT, MASQUE TOUS LES ESPACES SANS AUCUNE AFFECTATION
	$(".vSpaceTable").each(function(){
		if($(this).find("[name='objectRight[]']:checked").length==0)  {$(this).addClass("vSpaceTargetHide");}
	});

	////	INIT AFFECTATIONS : SI L'OBJET N'EST PAS UN CONTENEUR -> MASQUE ET DÉSACTIVE LES DROITS EN ECRITURE LIMITÉ ("boxWriteLimit")
	<?php if($curObj::isContainer()==false){ ?>
		$(".vSpaceWriteLimit").hide();
		$("[name='objectRight[]'][value$='_1.5']").prop("disabled",true);
	<?php } ?>

	////	INIT COMMUN : FOCUS SUR LE PREMIER CHAMP OBLIGATOIRE
	<?php if(!empty($curObj::$requiredFields)){ ?>
		if(!isMobile()){																//Pas de focus sur mobile : sinon affiche le clavier virtuel..
			const inputSelector=$("input[name='<?=$curObj::$requiredFields[0] ?>']");	//Selecteur de l'input
			const originalValue=inputSelector.val();									//Valeur originale de l'input
			inputSelector.val("").focus().val(originalValue);							//Focus sur la fin du texte (remet la valeur de l'input APRES le focus)
		}
	<?php } ?>
});


////	STYLISE LES LABELS ET CONTROLE LES DROITS D'ACCÈS
////
function labelRightStyle()
{
	////	Réinitialise les class des lignes et labels
	$(".vSpaceTable:visible .vSpaceLabel").removeClass("sAccessRead sAccessWriteLimit sAccessWrite");
	$(".vSpaceTable:visible [id^=targetLine]").removeClass("lineSelect");
	////	Stylise les labels des checkbox sélectionnées
	$("[name='objectRight[]']:checked").each(function(){
		//Récupère le droit de la checkbox && l'id du label correspondant
		var targetRight=this.id.split('_').pop();
		var targetLabelId=this.id.substring(0, this.id.lastIndexOf('_')).replace('objectRightBox_','');
		//Stylise le label
		if(targetRight=="1")		{$("#"+targetLabelId).addClass("sAccessRead");}
		else if(targetRight=="15")	{$("#"+targetLabelId).addClass("sAccessWriteLimit");}
		else if(targetRight=="2")	{$("#"+targetLabelId).addClass("sAccessWrite");}
		//Si on affiche tout, on met les lignes sélectionnées en surbrillance
		if($("#showAllUsers").isVisible()==false)  {$("#targetLine"+targetLabelId).addClass("lineSelect");}
	});
}
//// INIT LE STYLE DES LABELS
$(function(){ labelRightStyle(); });

////	LANCE LE CONTROLE D'UN FORMULAIRE
////
$(function(){
	$("#mainForm").submit(function(event){
		////	Lance les controles s'ils n'ont pas encore été effectués
		if(typeof mainFormControled==="undefined"){
			event.preventDefault();													// Stop la validation du form
			if(mainFormControl()==false)  {return false;}							// Controle principal non validé (champs obligatoires, etc) : return false
			if(typeof objectFormControl==="function"){								// Lance le controle spécifique d'un objet (différé via "Promise" : cf. controles Ajax)
				objectFormControl().then((result)=>{								// Récupère le résultat de la "Promise"
					if(result==true)  {mainFormControled=true;  $(this).submit();}	// Controle différé OK : Submit récursivement
				});
			}
			else  {mainFormControled=true;  $(this).submit();}						// Sinon controle direct OK : Submit récursivement
		}
		////	Controles OK : affiche l'image "loading" && valide le formulaire
		else if(mainFormControled==true){
			submitButtonLoading();
			return true;
		}
	});
});

////	CONTROLE PRINCIPAL DU FORMULAIRE
////
function mainFormControl()
{
	////	Init
	var validForm=true;
	var notifRequiredFields="";

	////	Verif les champs obligatoires ("focusRed()" et notif sur les champs vide)
	<?php
	foreach($curObj::$requiredFields as $tmpField){
		$isEmptyField=($tmpField=="description" && $curObj::descriptionEditor==true)  ?  'isEmptyEditor()'  :  '$("[name='.$tmpField.']").isEmpty()';
		echo 'if('.$isEmptyField.' && $("[name='.$tmpField.']").exist())   {validForm=false;  $("[name='.$tmpField.']").focusRed();  notifRequiredFields+="<br>'.Txt::trad($tmpField).'";}';
	}
	?>

	////	Puis "notify()" si des champs obligatoires sont resté vides
	if(notifRequiredFields.length>0)  {notify("<?= Txt::trad("requiredFields") ?> : "+notifRequiredFields);}

	////	Controle le formatage des mails
	if($("input[name='mail']").isNotEmpty() && $("input[name='mail']").isMail()==false)
		{validForm=false;  notify("<?= Txt::trad("mailInvalid"); ?>");}

	////	Controle les affectations
	if($("input[name='objectRight[]']").exist()){
		//Retourne false s'il n'y a aucune affectation
		if($(":checked[name='objectRight[]']").length==0)
			{validForm=false;  notify("<?= Txt::trad("EDIT_notifNoSelection") ?>");}
		//Retourne false si ya aucun accès attribués à l'user courant (espace ou perso)
		if($(":checked[name='objectRight[]'][value*='spaceUsers'], :checked[name='objectRight[]'][value*='_U<?= Ctrl::$curUser->_id ?>_']").length==0  &&  confirm("<?= Txt::trad("EDIT_notifNoPersoAccess") ?>")==false)
			{validForm=false;}
	}

	////	Fichier joint : remplace le "src" des images temporaires (cf. "editDescription()")
	if(typeof attachedFileSrcReplace==="function")  {attachedFileSrcReplace();}

	////	Retourne le résultat
	return validForm;
}
</script>


<style>
/*OPTIONS D'EDITION (cf. white.css & black.css) */
#objMenuTabs							{margin-top:35px; margin-bottom:-35px; display:table; width:100%; max-width:100%;}
.objMenuTab								{user-select:none; -webkit-user-select:none; display:table-cell; width:auto; padding:10px 5px; opacity:0.75; text-align:center; cursor:pointer; border-radius:5px 5px 0px 0px;}
.objMenuTabSelect						{opacity:1; border-bottom:none;}
.objMenuTab img							{margin-right:10px;}
.objMenuTab[for='objMenuAccessRight']	{min-width:150px;}/*onglet des droits d'accès*/
.objMenuOptions							{margin-top:35px; border-top:0px; border-radius:0px 0px 5px 5px; text-align:left; padding:30px 20px;}
#objMenuAccessRight						{text-align:center;}/*Tableau des droits d'accès*/
/*MOBILE FANCYBOX (440px)*/
@media screen and (max-width:440px){
	.objMenuTab[for='objMenuAccessRight']	{min-width:100px;}/*onglet des droits d'accès*/
	.objMenuTab img							{display:none;}
	.objMenuOptions							{padding:20px 5px;}
}

/*DROITS D'ACCÈS*/
.vSpaceTable							{display:inline-table; user-select:none; -webkit-user-select:none; max-width:600px; margin-bottom:30px;}
.vSpaceTable>div						{display:table-row;}
.vSpaceTable>div>div					{display:table-cell; padding:8px; text-align:center;}
.vSpaceTable img						{max-height:18px;}
.vSpaceHeader>div						{vertical-align:top; padding-bottom:10px!important;}
.vSpaceHeader>.vSpaceLabel				{padding-left:10px!important; font-style:italic;}/*Nom de l'espace*/
.vSpaceLabel							{width:280px; text-align:left!important; cursor:pointer;}
.vSpaceRead, .vSpaceWrite 				{width:80px;}/*colonne des checkboxes*/
.vSpaceWriteLimit						{width:110px;}/*idem*/
.vSpaceTargetIcon						{margin-right:8px;}
.vSpaceTargetHide						{display:none!important;}/*Par défaut : masque les users décochés de l'espace courant*/
#showAllUsers, #extendToSubfoldersDiv	{cursor:pointer; margin-bottom:10px;}
/*MOBILE FANCYBOX (440px)*/
@media screen and (max-width:440px){
	.vSpaceLabel								{font-size:0.95em;}/*Nom de l'espace et label des "targets"*/
	.vSpaceRead,.vSpaceWrite,.vSpaceWriteLimit	{width:55px;}/*colonne des checkboxes*/
	.vSpaceTable img, .vSpaceTargetIcon			{display:none;}
}

/*MENU DES NOTIFICATIONS PAR MAIL*/
#notifMailUsersPlus, #notifMailSelectList, #notifMailOptions	{display:none;}
#notifMailSelectList											{padding-left:10px; border-radius:3px;}
#notifMailSelectList>div										{display:inline-block; width:33%; min-width:210px; padding:3px;}
#notifMailOptions>div											{margin-left:10px; margin-top:8px;}
</style>


<?php
////	MENU DES DROITS D'ACCES ET DES OPTIONS
if(Ctrl::$curUser->isUser() && (!empty($objMenuAccessRight) || !empty($objMenuNotifMail) || !empty($objMenuAttachedFile) || !empty($objMenuShortcut)))
{
	////	ONGLETS DES MENUS (droits d'accès / fichier joint / notif mail / shortcut)
	echo '<div id="objMenuTabs">';
		if(!empty($objMenuAccessRight))  	{echo '<div class="objMenuTab" for="objMenuAccessRight"><img src="app/img/accessRight.png">'.$objMenuAccessRightLabel.'</div>';}
		if(!empty($objMenuNotifMail))		{echo '<div class="objMenuTab" for="objMenuNotifMail"><img src="app/img/mail.png">'.Txt::trad("EDIT_notifMail").'</div>';}
		if(!empty($objMenuAttachedFile))	{echo '<div class="objMenuTab" for="objMenuAttachedFile"><img src="app/img/attachment.png">'.Txt::trad("EDIT_attachedFileAdd").(!empty($curObj->attachedFileList())?'&nbsp;<div class="menuCircle">'.count($curObj->attachedFileList()).'</div>':null).'</div>';}
		if(!empty($objMenuShortcut))		{echo '<div class="objMenuTab '.($curObj->shortcut?'linkSelect':null).' '.(Req::isMobile()?'hide':null).'" for="objMenuShortcut"><img src="app/img/shortcut.png">'.Txt::trad("EDIT_shortcut").'</div>';}
	echo '</div>';

	////	MENU DES DROITS D'ACCES (OBJETS INDEPENDANTS)
	if(!empty($objMenuAccessRight))
	{
		echo '<div class="objMenuOptions" id="objMenuAccessRight">';
		//DROITS D'ACCES DE CHAQUE ESPACE
		foreach($accessRightSpaces as $tmpSpace)
		{
			//BLOCK + TABLEAU + ENTETE DE L'ESPACE (nom de l'espace + entete des droits d'acces)
			echo '<div class="vSpaceTable">
						<div class="vSpaceHeader">
							<div class="vSpaceLabel" title="'.$tmpSpace->name.'<br>'.$tmpSpace->description.'">'.Txt::reduce($tmpSpace->name,40).'</div>
							<div class="vSpaceRead" title="'.Txt::trad("accessReadTooltip").'">'.Txt::trad("accessRead").'</div>
							<div class="vSpaceWriteLimit" title="'.$accessWriteLimitTooltip.'">'.Txt::trad("accessWriteLimit").'</div>
							<div class="vSpaceWrite" title="'.Txt::trad("accessWriteTooltip").'">'.Txt::trad("accessWrite").'</div>
						</div>';
			//TARGETS DE L'ESPACE (id des checkboxes deja dans "boxProp"!)
			foreach($tmpSpace->targetLines as $targetLine)
			{
				$targetHide=(!empty($targetLine["isChecked"]) || ($tmpSpace->isCurSpace() && count($tmpSpace->targetLines)<5))  ?  null  :  "vSpaceTargetHide";		//Affiche les targets "checked"" ou toutes celles de l'espace courant s'il compte moins de 5 users
				$targetIconAdmin=(!empty($targetLine["onlyFullAccess"]))  ?  "vSpaceTargetIconAdmin"  :  null;														//Icone d'un admin de l'espace?
				$targetIcon=(!empty($targetLine["icon"]))  ?  '<img src="app/img/'.$targetLine["icon"].'" class="vSpaceTargetIcon '.$targetIconAdmin.'">'  : null;	//Icone spécifiée pour la target?
				echo '<div class="lineHover '.$targetHide.'" id="targetLine'.$targetLine["targetId"].'">
						<div class="vSpaceLabel" id="'.$targetLine["targetId"].'" title="'.$targetLine["tooltip"].'">'.$targetIcon.$targetLine["label"].'</div>
						<div class="vSpaceRead" title="'.Txt::trad("accessReadTooltip").'"><input type="checkbox" name="objectRight[]" '.$targetLine["boxProp"]["1"].'></div>
						<div class="vSpaceWriteLimit" title="'.$accessWriteLimitTooltip.'"><input type="checkbox" name="objectRight[]" '.$targetLine["boxProp"]["1.5"].'></div>
						<div class="vSpaceWrite" title="'.Txt::trad("accessWriteTooltip").'"><input type="checkbox" name="objectRight[]" '.$targetLine["boxProp"]["2"].'></div>
					  </div>';
			}
			//Fin du block principal "vSpaceTable"
			echo '</div>';
		}
		//Menu "Afficher tous les utilisateurs [et espaces]"  &&  Menu "Etendre les droits aux sous-dossiers"
		echo '<div id="showAllUsers">'.(count($accessRightSpaces)==1?Txt::trad("EDIT_showAllUsers"):Txt::trad("EDIT_showAllUsersAndSpaces")).' <img src="app/img/arrowBottom.png"></div>';
		if(!empty($extendToSubfolders))  {echo '<div id="extendToSubfoldersDiv"><hr><input type="checkbox" name="extendToSubfolders" id="extendToSubfolders" value="1"><label for="extendToSubfolders" title="'.Txt::trad("EDIT_accessRightSubFoldersTooltip").'">'.Txt::trad("EDIT_accessRightSubFolders").'</label></div><script>$("#extendToSubfoldersDiv").pulsate(4);</script>';}
		//Fin du "objMenuAccessRight"
		echo '</div>';
	}
	////	MENU DES NOTIFS MAIL
	if(!empty($objMenuNotifMail))
	{
		echo '<div class="objMenuOptions" id="objMenuNotifMail">';
		//CHECKBOX PRINCIPALE & BLOCK DES OPTIONS
		$notifMailTooltip=$curObj->tradObject("EDIT_notifMailTooltip");
		if($curObj::objectType=="calendarEvent")  {$notifMailTooltip.=Txt::trad("EDIT_notifMailTooltipCal");}//"la notification ne sera envoyée qu'aux propriétaires de ces agendas"
		echo '<input type="checkbox" name="notifMail" id="boxNotifMail" value="1" onChange="$(\'#notifMailOptions\').slideToggle();"> <label for="boxNotifMail" title="'.$notifMailTooltip.'">'.Txt::trad("EDIT_notifMail2").'</label>';
		echo '<div id="notifMailOptions">';
			//Option du module "File" : "Joindre les fichiers à la notification"
			if($curObj::objectType=="file")  {echo '<div><img src="app/img/dependency.png"><input type="checkbox" name="notifMailAddFiles" value="1" id="boxNotifMailAddFiles"><label for="boxNotifMailAddFiles" title="'.Txt::trad("FILE_fileSizeLimit").' '.File::displaySize(File::mailMaxFilesSize).'">'.Txt::trad("EDIT_notifMailAddFiles").' <img src="app/img/attachment.png"></label></div>';}
			// Options de base des emails (cf. Tool::sendMail()")
			echo MdlObject::sendMailBasicOptions();
			//Option "Choisir les destinataires"
			echo '<div><img src="app/img/dependency.png"><input type="checkbox" name="notifMailSelect" id="boxNotifMailSelect" value="1" onclick="$(\'#notifMailSelectList\').slideToggle();">&nbsp; <label for="boxNotifMailSelect">'.Txt::trad("EDIT_notifMailSelect").' <img src="app/img/user/accessAll.png"></label></div>';
			echo '<div id="notifMailSelectList">';
				//Groupe d'users de l'espace courant
				foreach($curSpaceUserGroups as $tmpGroup){
					echo '<div title="'.Txt::trad("selectUnselect").' : '.$tmpGroup->usersLabel.'">
							<input type="checkbox" name="notifUsersGroup[]" value="'.implode(",",$tmpGroup->userIds).'" id="notifUsersGroup'.$tmpGroup->_typeId.'" onchange="userGroupSelect(this,\'#notifMailSelectList\');">
							<label for="notifUsersGroup'.$tmpGroup->_typeId.'"><img src="app/img/user/accessGroup.png"> '.$tmpGroup->title.'</label>
						  </div>';
				}
				//Liste de tous les users : affiche par défaut uniquement ceux l'espace courant
				foreach($notifMailUsers as $tmpUser){
					$userMailTooltip=($tmpUser->userMailDisplay())  ?  'title="'.Txt::tooltip($tmpUser->mail).'"'  :  null;
					echo '<div id="divNotifMailUser'.$tmpUser->_id.'" '.(!in_array($tmpUser->_id,$curSpaceUsersIds)?'style="display:none"':null).'>
							<input type="checkbox" name="notifMailUsers[]" value="'.$tmpUser->_id.'" id="boxNotif'.$tmpUser->_typeId.'" data-idUser="'.$tmpUser->_id.'">
							<label for="boxNotif'.$tmpUser->_typeId.'" '.$userMailTooltip.'>'.$tmpUser->getLabel().'</label>
						  </div>';
				}
				//"Afficher tous les utilisateurs" des tous les espaces
				if(count($notifMailUsers)>count($curSpaceUsersIds))  {echo '<div onclick="$(\'[id^=divNotifMailUser]\').fadeIn();$(this).fadeOut();"><img src="app/img/arrowBottom.png"> '.Txt::trad("EDIT_showAllUsers").'</div>';}
		//Fin des "notifMailSelectList" et "notifMailOptions" + "objMenuNotifMail"
		echo '</div></div></div>';
	}
	////	MENU DES FICHIERS JOINTS
	if(!empty($objMenuAttachedFile)){
		echo '<div class="objMenuOptions" id="objMenuAttachedFile">'.$curObj->attachedFile().'</div>';
	}
	////	MENU DES SHORTCUT
	if(!empty($objMenuShortcut)){
		echo '<div class="objMenuOptions" id="objMenuShortcut"><input type="checkbox" name="shortcut" id="boxShortcut" value="1" '.($curObj->shortcut?'checked':null).'> <label for="boxShortcut">'.Txt::trad("EDIT_shortcutInfo").'</label></div>';
	}
}

////	_ID DU CONTENEUR  &&  BOUTON DE VALIDATION
if(!empty($curObj->_idContainer))  {echo '<input type="hidden" name="_idContainer" value="'.$curObj->_idContainer .'">';}
echo Txt::submitButton();