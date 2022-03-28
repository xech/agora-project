<script>
/*******************************************************************************************
 *	LOAD LA PAGE
 *******************************************************************************************/
$(function(){
	////	CHANGE D'ONGLET / MENU (chaque onglet ".objMenuLabel" doit avoir un "for" correspondant à l'Id de son div)
	$(".objMenuLabel").click(function(){
		//Réinit tous les menus, sauf celui sélectionné
		$(".objMenuLabel").not(this).each(function(){
			$(this).addClass("objMenuLabelUnselect");//déselectionne l'onglet
			$("#"+$(this).attr("for")).hide();//masque le menu associé
		});
		//Affiche le block sélectionné
		$(this).removeClass("objMenuLabelUnselect");//sélectionne l'onglet
		$("#"+$(this).attr("for")).fadeIn();//affiche le menu associé
	});
	//Puis affiche le premier menu disponible
	$(".objMenuLabel:first-child").trigger("click");

	////	AFFECTATIONS : CLICK LE LABEL D'UNE AFFECTATION
	$(".vSpaceTable:visible .vSpaceLabel").click(function(){
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
	$(".vSpaceTable:visible [id^=objectRightBox]").change(function(){
		var targetId=this.value.slice(0, this.value.lastIndexOf("_"));			//exple "1_U2_1.5" => "1_U2"
		$("[id^=objectRightBox_"+targetId+"]").not(this).prop("checked",false);	//"uncheck" les autres checkbox du "target"
		labelStyleRightControl(this.id);										//Style des labels & Controle des droits
	});

	////	AFFECTATIONS : CLICK SUR "AFFICHER TOUS LES UTILISATEURS"
	$("#showAllUsers").click(function(){
		$(".vSpaceTargetHide").removeClass("vSpaceTargetHide");	//Affiche tous les users et espaces masqués
		$(".vSpaceLabelSpace").css("visibility","visible");		//Affiche le nom des espaces (masqué par défaut)
		$(this).hide();											//Masque le menu qui vient d'être cliqué
		labelStyleRightControl();								//Style des labels
		lightboxResize();										//Resize le lightbox
	});

	////	INIT LE MENU
	//// Masque par défaut tous les espaces sans affectations
	$("[id^=spaceTable]").each(function(){
		if($(this).find("[name='objectRight[]']:checked").length==0)  {$(this).addClass("vSpaceTargetHide");}
	});
	//// Affiche le menu "Afficher tous les utilisateurs" s'il ya des ".vSpaceTargetHide"
	$("#showAllUsers").toggle($(".vSpaceTargetHide").exist());
	//// Masque le libellé de l'espace courant si c'est le seul disponible
	if($(".vSpaceLabelSpace").length==1)  {$(".vSpaceLabelSpace").css("visibility","hidden");}
	//// Masque et désactive les droits en ecriture limité ("boxWriteLimit")
	<?php if($curObj::isContainer()==false){ ?>
		$(".vSpaceWriteLimit").hide();
		$("[name='objectRight[]'][value$='_1.5']").prop("disabled",true);
	<?php } ?>
	//// Init le style des labels
	labelStyleRightControl();
	//// Focus sur le premier champ obligatoire (sauf en responsive, pour pas afficher le clavier virtuel)
	<?php if(!empty($curObj::$requiredFields)){ ?>
		if(!isMobile())  {$("input[name='<?=$curObj::$requiredFields[0] ?>']").focus();}
	<?php } ?>
});

/*******************************************************************************************
 *	STYLISE LES LABELS ET CONTROLE LES DROITS D'ACCÈS
 *******************************************************************************************/
function labelStyleRightControl(boxId)
{
	//Réinitialise les class des lignes et labels
	$(".vSpaceTable:visible .vSpaceLabel").removeClass("sAccessRead sAccessWriteLimit sAccessWrite");
	$(".vSpaceTable:visible [id^=targetLine]").removeClass("lineSelect");
	//Stylise les labels des checkbox sélectionnées
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
		//Sujet du forum : affiche "preférez le droit écriture limité" ? (si pas un droit "écriture limité" & box que l'on vient de sélectionner, donc pas les pré-sélections)
		if("<?= $curObj::objectType ?>"=="forumSubject" && targetRight!="15" && boxId && boxId==this.id)  {notify("<?= Txt::trad("FORUM_accessRightInfos") ?>");}
	});
	//Control Ajax d'une affectation (droit d'accès) pour un sous dossier
	<?php if($curObj::isFolder && $curObj->containerObj()->isRootFolder()==false){ ?>
	if(boxId && $("#"+boxId).prop("checked")){
		$.ajax({url:"?ctrl=object&action=AccessRightParentFolder&typeId=<?= $curObj->containerObj()->_typeId ?>&objectRight="+$("#"+boxId).val(), dataType:"json"}).done(function(result){
			if(result.error)  {notify(result.message);}
		});
	}
	<?php } ?>
}

/*******************************************************************************************
 *	CONTROLE FINAL DU FORMULAIRE
 *******************************************************************************************/
function mainFormControl()
{
	//Init
	var validForm=true;

	////	Verif les champs obligatoires ("focusRed()" et notif sur les champs vide)
	var notifRequiredFields="";
	<?php
	foreach($curObj::$requiredFields as $tmpField){
		$isEmptyField=($tmpField==$curObj::htmlEditorField)  ?  'isEmptyEditor()'  :  '$("[name='.$tmpField.']").isEmpty()';
		echo 'if('.$isEmptyField.' && $("[name='.$tmpField.']").exist())   {validForm=false;  $("[name='.$tmpField.']").focusRed();  notifRequiredFields+="'.Txt::trad($tmpField).' &nbsp;";}';
	}
	?>
	//Notify sur les champs vides
	if(notifRequiredFields.length>0)  {notify("<?= Txt::trad("requiredFields") ?> : "+notifRequiredFields);}

	////	Controle les mails
	if($("input[name='mail']").isEmpty()==false && $("input[name='mail']").isMail()==false)   {validForm=false;  notify("<?= Txt::trad("mailInvalid"); ?>");}

	////	Controle le formatage des dates
	$(".dateInput,.dateBegin,.dateEnd").each(function(){
		if(this.value.length>0){
			var dateMatch=/^\d{2}\/\d{2}\/\d{4}$/.exec(this.value);
			if(dateMatch==null)   {validForm=false;  notify("<?= Txt::trad("dateFormatError") ?>");}
		}
	});

	////	Controle les affectations
	if($("input[name='objectRight[]']").exist())
	{
		//Aucune affectation : false
		if($(":checked[name='objectRight[]']").length==0)   {validForm=false;  notify("<?= Txt::trad("EDIT_notifNoSelection") ?>");}
		//Sujet du forum et uniquement des accès en lecture : false!
		if("<?= $curObj::objectType ?>"=="forumSubject" && $(":checked[name='objectRight[]'][value$='_1.5'], :checked[name='objectRight[]'][value$='_2']").length==0)   {validForm=false;  notify("<?= Txt::trad("EDIT_notifWriteAccess") ?>");}
		//Aucun accès pour l'user courant?
		var nbCurUserAccess=$(":checked[name='objectRight[]'][value*='spaceUsers'], :checked[name='objectRight[]'][value*='_U<?= Ctrl::$curUser->_id ?>_']").length;
		if(nbCurUserAccess==0 && confirm("<?= Txt::trad("EDIT_notifNoPersoAccess") ?>")==false)  {validForm=false;}
	}

	////	Fichier joint : remplace le "src" des images temporaires (cf. "VueObjHtmlEditor.php")
	if(typeof attachedFileReplaceSRCINPUT=="function")  {attachedFileReplaceSRCINPUT();}

	////	Controle OK : affiche l'icone "loading"
	if(validForm==true)  {submitButtonLoading();}
	return validForm;
}
</script>

<style>
/*OPTIONS D'EDITION (ex 'fieldset')*/
#objMenuLabels								{display:table; width:100%; margin-top:35px; margin-bottom:-35px;}/*cf. "lightboxBlockTitle"*/
.objMenuLabel								{display:table-cell; padding:10px 3px; text-align:center; cursor:pointer; border-radius:3px 3px 0px 0px;}
.objMenuLabel>img							{margin-right:8px;}
.objMenuLabel[for='objMenuAccessRight']		{min-width:150px!important;}/*droits d'accès*/
.objMenuLabel:not(.objMenuLabelUnselect)	{border-bottom:none!important;}
.objMenuLabelUnselect						{opacity:0.75;}
#objMenuBlocks>div:not(#objMenuAccessRight)	{margin:10px 0px 10px 30px; text-align:left;}/*block de chaque menu (sauf des droits d'accès)*/

/*DROITS D'ACCÈS*/
#objMenuAccessRight						{text-align:center;}
.vSpaceTable							{display:inline-table; width:100%; max-width:500px; margin:12px 0px; padding:5px 0px; border-radius:8px; background:<?= Ctrl::$agora->skin=="black"?"#333":"#f9f9f9"?>;}
.vSpaceTable>div						{display:table-row;}
.vSpaceTable>div>div					{display:table-cell; padding:5px; text-align:center;}
.vSpaceTable img						{max-height:20px;}
.vSpaceHeader>div						{vertical-align:top; padding-bottom:10px!important;}
.vSpaceLabelSpace						{text-align:left!important; padding-left:10px!important; font-style:italic;}/*nom de l'espace*/
.vSpaceLabel							{text-align:left!important; cursor:pointer;}
.vSpaceRead, .vSpaceWrite 				{width:70px;}/*colonne des checkboxes*/
.vSpaceWriteLimit						{width:110px;}/*idem*/
.vSpaceTargetIcon						{margin-right:8px;}
#showAllUsers, #extendToSubfoldersDiv	{cursor:pointer; margin-bottom:10px;}
.vSpaceTargetHide						{display:none!important;}/*Par défaut : masque les users décochés de l'espace courant*/

/*MENU DES NOTIFICATIONS PAR MAIL*/
#notifMailUsersPlus, #notifMailSelectList, #notifMailOptions  {display:none;}
#notifMailSelectList					{padding-left:10px; border-radius:3px;}
#notifMailSelectList>div				{display:inline-block; width:33%; min-width:210px; padding:3px;}
#notifMailOptions>div					{margin-left:10px; margin-top:8px;}

/*RESPONSIVE & FANCYBOX (440px)*/
@media screen and (max-width:440px){
	.objMenuLabel img							{display:none;}
	.vSpaceHeader, .vSpaceLabel					{font-size:0.95em;}/*Entête du tableau et label des "targets"*/
	.vSpaceRead,.vSpaceWrite,.vSpaceWriteLimit	{width:55px;}/*colonne des checkboxes*/
	.vSpaceTargetIcon							{display:none;}
	#objMenuBlocks>div:not(#objMenuAccessRight)	{margin:5px;}/*block de chaque menu*/
}
</style>


<?php
////	INITIALISE L'EDITEUR HTML D'UN CHAMP : DESCRIPTION OU AUTRE
if($curObj::htmlEditorField!==null)  {echo CtrlObject::htmlEditor($curObj::htmlEditorField);}

////	MENU DES DROITS D'ACCES ET DES OPTIONS
if(Ctrl::$curUser->isUser() && (!empty($objMenuAccessRight) || !empty($objMenuNotifMail) || !empty($objMenuAttachedFile) || !empty($objMenuShortcut)))
{
	////	ONGLETS DES MENUS (droits d'accès / fichier joint / notif mail / shortcut)
	echo '<div id="objMenuLabels">';
		if(!empty($objMenuAccessRight))  	{echo '<div class="objMenuLabel" for="objMenuAccessRight"><img src="app/img/accessRight.png">'.$objMenuAccessRightLabel.'</div>';}
		if(!empty($objMenuNotifMail))		{echo '<div class="objMenuLabel" for="objMenuNotifMail"><img src="app/img/mail.png">'.Txt::trad("EDIT_notifMail").'</div>';}
		if(!empty($objMenuAttachedFile))	{echo '<div class="objMenuLabel" for="objMenuAttachedFile"><img src="app/img/attachment.png">'.Txt::trad("EDIT_attachedFileAdd").(!empty($curObj->attachedFileList())?'&nbsp;<div class="menuCircle">'.count($curObj->attachedFileList()).'</div>':null).'</div>';}
		if(!empty($objMenuShortcut))		{echo '<div class="objMenuLabel '.($curObj->shortcut?'sLinkSelect':null).' '.(Req::isMobile()?'hide':null).'" for="objMenuShortcut"><img src="app/img/shortcut.png">'.Txt::trad("EDIT_shortcut").'</div>';}
	echo '</div>';

	//// DIV DES MENUS
	echo '<div id="objMenuBlocks" class="lightboxBlock">';
		////	MENU DES DROITS D'ACCES (OBJETS INDEPENDANTS)
		if(!empty($objMenuAccessRight))
		{
			echo '<div id="objMenuAccessRight">';
			//DROITS D'ACCES DE CHAQUE ESPACE
			foreach($accessRightSpaces as $tmpSpace)
			{
				//BLOCK + TABLEAU + ENTETE DE L'ESPACE (nom de l'espace + entete des droits d'acces)
				echo '<div class="vSpaceTable" id="spaceTable'.$tmpSpace->_id.'">
							<div class="vSpaceHeader">
								<div class="vSpaceLabelSpace" title="'.$tmpSpace->name.'<br>'.$tmpSpace->description.'">'.Txt::reduce($tmpSpace->name,40).'</div>
								<div class="vSpaceRead" title="'.Txt::trad("accessReadInfo").'">'.Txt::trad("accessRead").'</div>
								<div class="vSpaceWriteLimit" title="'.$accessWriteLimitInfo.'">'.Txt::trad("accessWriteLimit").'</div>
								<div class="vSpaceWrite" title="'.Txt::trad("accessWriteInfo").'">'.Txt::trad("accessWrite").'</div>
							</div>';
				//TARGETS DE L'ESPACE (id des checkboxes deja dans "boxProp"!)
				foreach($tmpSpace->targetLines as $targetLine)
				{
					$targetHide=(!empty($targetLine["isChecked"]) || ($tmpSpace->isCurSpace() && count($tmpSpace->targetLines)<5))  ?  null  :  "vSpaceTargetHide";		//Affiche les targets "checked"" ou toutes celles de l'espace courant s'il compte moins de 5 users
					$targetIconAdmin=(!empty($targetLine["onlyFullAccess"]))  ?  "vSpaceTargetIconAdmin"  :  null;														//Icone d'un admin de l'espace?
					$targetIcon=(!empty($targetLine["icon"]))  ?  '<img src="app/img/'.$targetLine["icon"].'" class="vSpaceTargetIcon '.$targetIconAdmin.'">'  : null;	//Icone spécifiée pour la target?
					echo '<div class="lineHover '.$targetHide.'" id="targetLine'.$targetLine["targetId"].'">
							<div class="vSpaceLabel" id="'.$targetLine["targetId"].'" title="'.$targetLine["tooltip"].'">'.$targetIcon.$targetLine["label"].'</div>
							<div class="vSpaceRead" title="'.Txt::trad("accessReadInfo").'"><input type="checkbox" name="objectRight[]" '.$targetLine["boxProp"]["1"].'></div>
							<div class="vSpaceWriteLimit" title="'.$accessWriteLimitInfo.'"><input type="checkbox" name="objectRight[]" '.$targetLine["boxProp"]["1.5"].'></div>
							<div class="vSpaceWrite" title="'.Txt::trad("accessWriteInfo").'"><input type="checkbox" name="objectRight[]" '.$targetLine["boxProp"]["2"].'></div>
						  </div>';
				}
				//Fin du block principal "spaceTable"
				echo '</div>';
			}
			//Menu "Afficher tous les utilisateurs" (.."et espace")  &&  Menu "Etendre les droits aux sous-dossiers"
			echo '<div id="showAllUsers">'.(count($accessRightSpaces)==1?Txt::trad("EDIT_showAllUsers"):Txt::trad("EDIT_showAllUsersAndSpaces")).' <img src="app/img/arrowBottom.png"></div>';
			if(!empty($extendToSubfolders))  {echo '<div id="extendToSubfoldersDiv"><hr><input type="checkbox" name="extendToSubfolders" id="extendToSubfolders" value="1" checked="checked"><label for="extendToSubfolders" title="'.Txt::trad("EDIT_accessRightSubFolders_info").'">'.Txt::trad("EDIT_accessRightSubFolders").'</label></div><script>$("#extendToSubfoldersDiv").pulsate(3);</script>';}
			//Fin du "objMenuAccessRight"
			echo '</div>';
		}
		////	MENU DES NOTIFS MAIL
		if(!empty($objMenuNotifMail))
		{
			echo '<div id="objMenuNotifMail">';
			//CHECKBOX PRINCIPALE & BLOCK DES OPTIONS
			$notifMailTooltip=$curObj->tradObject("EDIT_notifMailInfo");
			if($curObj::objectType=="calendarEvent")  {$notifMailTooltip.=Txt::trad("EDIT_notifMailInfoCal");}//"la notification ne sera envoyée qu'aux propriétaires de ces agendas"
			echo '<input type="checkbox" name="notifMail" id="boxNotifMail" value="1" onChange="$(\'#notifMailOptions\').slideToggle();"> <label for="boxNotifMail" title="'.$notifMailTooltip.'">'.Txt::trad("EDIT_notifMail2").'</label>';
			echo '<div id="notifMailOptions">';
				//Option "Joindre les fichiers à la notification" (cf. module "File", donc absent des "mailOptions[]")
				if($curObj::objectType=="file" && $curObj->_id==0)  {echo '<div><img src="app/img/dependency.png"><input type="checkbox" name="notifMailAddFiles" value="1" id="boxNotifMailAddFiles"><label for="boxNotifMailAddFiles" title="'.Txt::trad("FILE_fileSizeLimit").' '.File::displaySize(File::mailMaxFilesSize).'">'.Txt::trad("EDIT_notifMailAddFiles").' <img src="app/img/attachment.png"></label></div>';}
				//Option "Masquer les destinataires" & "Accusé de réception"
				echo '<div title="'.Txt::trad("MAIL_hideRecipientsInfo").'"><img src="app/img/dependency.png"><input type="checkbox" name="mailOptions[]" value="hideRecipients" id="hideRecipients"> <label for="hideRecipients">'.Txt::trad("MAIL_hideRecipients").'</label></div>';
				if(!empty(Ctrl::$curUser->mail))  {echo '<div title="'.Txt::trad("MAIL_receptionNotifInfo").'"><img src="app/img/dependency.png"><input type="checkbox" name="mailOptions[]" value="receptionNotif" id="receptionNotif"> <label for="receptionNotif">'.Txt::trad("MAIL_receptionNotif").'</label></div>';}
				//Option "Choisir les destinataires"
				echo '<div><img src="app/img/dependency.png"><input type="checkbox" name="notifMailSelect" id="boxNotifMailSelect" value="1" onclick="$(\'#notifMailSelectList\').slideToggle();"><label for="boxNotifMailSelect">'.Txt::trad("EDIT_notifMailSelect").' <img src="app/img/user/accessAll.png"></label></div>';
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
						echo '<div id="divNotifMailUser'.$tmpUser->_id.'" '.(!in_array($tmpUser->_id,$curSpaceUsersIds)?'style="display:none"':null).'>
								<input type="checkbox" name="notifMailUsers[]" value="'.$tmpUser->_id.'" id="boxNotif'.$tmpUser->_typeId.'" data-idUser="'.$tmpUser->_id.'">
								<label for="boxNotif'.$tmpUser->_typeId.'" title="'.$tmpUser->mail.'">'.$tmpUser->getLabel().'</label>
							  </div>';
					}
					//"Afficher tous les utilisateurs" des tous les espaces
					if(count($notifMailUsers)>count($curSpaceUsersIds))  {echo '<div onclick="$(\'[id^=divNotifMailUser]\').fadeIn();$(this).fadeOut();" class="sLink"><img src="app/img/arrowBottom.png"> '.Txt::trad("EDIT_showAllUsers").'</div>';}
			//Fin des "notifMailSelectList" et "notifMailOptions" + "objMenuNotifMail"
			echo '</div></div></div>';
		}
		////	MENU DES FICHIERS JOINTS
		if(!empty($objMenuAttachedFile)){
			echo '<div id="objMenuAttachedFile">'.CtrlObject::attachedFile($curObj).'</div>';
		}
		////	MENU DES SHORTCUT
		if(!empty($objMenuShortcut)){
			echo '<div id="objMenuShortcut"><input type="checkbox" name="shortcut" id="boxShortcut" value="1" '.($curObj->shortcut?'checked':null).'> <label for="boxShortcut">'.Txt::trad("EDIT_shortcutInfo").'</label></div>';
		}
	//// Fin du "objMenuBlocks"
	echo '</div>';
}

////	TYPEID DU CONTENEUR  &&  BOUTON DE VALIDATION
if(!empty($curObj->_idContainer))  {echo '<input type="hidden" name="_idContainer" value="'.$curObj->_idContainer .'">';}
echo Txt::submitButton();