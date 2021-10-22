<script>
/*******************************************************************************************
 *	LOAD LA PAGE
 *******************************************************************************************/
$(function(){
	////	CHANGE LE MENU/ONGLET DE L'OBJET (chaque onglet ".objMenuLabel" doit avoir un "for" correspondant à l'Id de son div)
	//Change de menu
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
	//Affiche le premier menu disponible
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
		var objectRight=$(this).val();
		var targetId=objectRight.slice(0, objectRight.lastIndexOf("_"));//exple "1_U2_1.5" => "1_U2"
		$("[id^=objectRightBox_"+targetId+"]").not(this).prop("checked",false);//"uncheck" les autres checkbox du "target"
		labelStyleRightControl(this.id);//Style des labels & Controle des droits
	});

	////	AFFECTATIONS : AFFICHE TOUS LES USERS D'UN ESPACE
	$(".vShowAllUsers").click(function(){
		$($(this).attr("for")+" .vSpaceHideTarget").hide().removeClass("vSpaceHideTarget").fadeIn();//Enlève la class pour masquer les users puis raffiche avec un fadeIn
		$(this).hide();//Masque le menu pour afficher tous les users
		lightboxResize();//Resize le lightbox
	});

	////	AFFECTATIONS : AFFICHE/MASQUE LES BLOCKS D'ESPACES
	//Masque par défaut tous les espaces sans affectations (sauf espace courant)
	$("[id^=spaceTable]").each(function(){
		if(this.id!="spaceTable<?= Ctrl::$curSpace->_id ?>" && $("#"+this.id+" [name='objectRight[]']:checked").length==0)  {$(this).hide();}
	});
	//Masque si besoin le menu "Afficher tous mes espaces"
	if($(".vSpaceTable:hidden").exist())  {$("#ShowAllSpaces").fadeIn();}
	//Click sur "Afficher tous mes espaces"
	$("#ShowAllSpaces").click(function(){
		$('[id^=spaceTable]').fadeIn();//Affiche tous les blocks d'espace
		$(".vSpaceTitle .vSpaceLabel").removeClass("vSpaceLabelHide");//Raffiche le nom de l'espace courant (masqué par défaut)
		$(this).hide();//Masque le menu
	});
	
	////	AFFECTATIONS : INIT
	//Focus sur le premier champ obligatoire (sauf en responsive, pour pas afficher le clavier virtuel)
	<?php if(!empty($curObj::$requiredFields)){ ?>
		if(!isMobile())  {$("input[name='<?=$curObj::$requiredFields[0] ?>']").focus();}
	<?php } ?>
	//Masque et désactive les droits "boxWriteLimit"
	<?php if($curObj::isContainer()==false){ ?>
		$("[name='objectRight[]'][value$='_1.5']").prop("disabled",true);
		$(".vSpaceWriteLimit").hide();
	<?php } ?>
	//Init le style des labels
	labelStyleRightControl();
});

/*******************************************************************************************
 *	STYLISE LES LABELS ET CONTROLE LES DROITS D'ACCÈS
 *******************************************************************************************/
function labelStyleRightControl(boxId)
{
	//Réinitialise les class des lignes et labels
	$(".vSpaceTable:visible .vSpaceLabel").removeClass("sAccessRead sAccessWriteLimit sAccessWrite");
	$(".vSpaceTable:visible [id^=targetLine]").removeClass("sLineSelect");
	//Stylise les labels des checkbox sélectionnées
	$("[name='objectRight[]']:checked").each(function(){
		//Récupère le droit de la checkbox && l'id du label correspondant
		var targetRight=this.id.split('_').pop();
		var targetLabelId=this.id.substring(0, this.id.lastIndexOf('_')).replace('objectRightBox_','');
		//Stylise le label
		if(targetRight=="1")		{$("#"+targetLabelId).addClass("sAccessRead");}
		else if(targetRight=="15")	{$("#"+targetLabelId).addClass("sAccessWriteLimit");}
		else if(targetRight=="2")	{$("#"+targetLabelId).addClass("sAccessWrite");}
		//Ligne sélectionnée : surligne
		$("#targetLine"+targetLabelId).addClass("sLineSelect");
		//Sujet du forum : affiche "preférez le droit écriture limité" ?	=> pas un droit "écriture limité"  & box que l'on vient de sélectionner (pas les pré-sélections)
		if("<?= $curObj::objectType ?>"=="forumSubject" && targetRight!="15" && boxId && boxId==this.id)
			{notify("<?= Txt::trad("FORUM_accessRightInfos") ?>");}
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
	if(typeof attachedFileTmpSrc=="function")  {attachedFileTmpSrc();}

	////	Controle OK : affiche l'icone "loading"
	if(validForm==true)  {submitButtonLoading();}
	return validForm;
}
</script>

<style>
/*OPTIONS D'EDITION (ex 'fieldset')*/
#objMenuBlocks							{text-align:left; margin-top:33px;}
#objMenuLabels							{display:table; width:100%; margin:33px 0px -33px 0px;}
.objMenuLabel							{display:table-cell; padding:10px 5px 5px 5px; text-align:center; cursor:pointer; border-radius:3px 3px 0px 0px;}
.objMenuLabel[for='objMenuMain']		{min-width:150px!important;}/*droits d'accès*/
.objMenuLabel>span						{display:inline-block; margin:0px 5px 0px 5px;}
.objMenuLabel:not(.objMenuLabelUnselect){border-bottom:none!important;}
.objMenuLabelUnselect					{opacity:0.8;}

/*DROITS D'ACCÈS*/
#objMenuMain							{text-align:center;}
[id^=spaceTable]						{text-align:center; margin-top:15px; margin-bottom:30px;}
.vSpaceTable							{display:inline-table; min-width:450px; max-width:100%; background-color:<?= Ctrl::$agora->skin=="black"?"#333":"#f8f8f8;"?>}/*idem responsive!*/
.vSpaceTable>div						{display:table-row;}
.vSpaceTable>div>div					{display:table-cell; padding:5px 3px 5px 3px;}
.vSpaceTable img						{max-height:15px;}
.vSpaceLabel							{cursor:pointer; text-align:left;}
.vSpaceTitle>div						{vertical-align:middle;}
.vSpaceTitle .vSpaceLabelHide			{visibility:hidden;}/*masque le nom de l'espace courant*/
.vSpaceTitle>div:first-child			{font-style:italic;}/*nom de l'espace*/
.vSpaceTitle>div:not(:first-child)		{width:65px; text-align:center;}/*colonne des checkboxes*/
.vSpaceTargetIcon						{margin-right:10px;}/*icone de target/de user*/
.vSpaceTargetIconAdmin					{filter:brightness(0.9);}/*icone d'admin : plus foncé*/
.vShowAllUsers .vSpaceLabel				{padding-top:10px; padding-left:30px;}/*"Afficher plus d'utilisateurs"*/
.vSpaceHideTarget						{display:none!important;}/*Users de l'espace courant non sélectionnés: masqués par défaut*/
#ShowAllSpaces, #ExtendToSubfolders		{text-align:center; cursor:pointer;}

/*MENU DES NOTIFICATIONS PAR MAIL*/
#notifMailUsersPlus, #notifMailSelectList, #notifMailOptions	{display:none;}
#notifMailSelectList					{padding-left:25px; border-radius:3px;}
#notifMailSelectList>div				{display:inline-block; width:190px; padding:3px;}
#notifMailSelectList>hr					{margin:3px;}
#notifMailOptions>div					{margin-left:10px; margin-top:8px;}

/*RESPONSIVE & FANCYBOX (440px)*/
@media screen and (max-width:440px){
	.objMenuLabel[for='objMenuMain']	{min-width:80px!important;}
	.objMenuLabel img					{display:none; }
	.vSpaceTable						{min-width:100%;}
	.vSpaceTitle, .vSpaceLabel			{font-size:0.9em;}/*Entête du tableau et label des "targets"*/
	.vSpaceTitle>div:not(:first-child)	{width:50px;}/*colonne des checkboxes*/
	.vSpaceTargetIcon					{display:none;}
}
</style>


<?php
////	INITIALISE L'EDITEUR HTML D'UN CHAMP (description ou autre) ?
if($curObj::htmlEditorField!==null)  {echo CtrlObject::htmlEditor($curObj::htmlEditorField);}

////	MENU DES DROITS D'ACCES ET DES OPTIONS
if(Ctrl::$curUser->isUser() && !empty($accessRightMenu) || !empty($attachedFiles) || !empty($moreOptions))
{
	////	ONGLETS DES MENUS
	echo '<div id="objMenuLabels" class="noSelect">';
		if(!empty($accessRightMenu))	{echo '<div class="objMenuLabel" for="objMenuMain"><img src="app/img/accessRight.png"> '.$accessRightMenuLabel.'</div>';}
		if(!empty($attachedFiles)){
			$AttachedFileNb=(count($curObj->attachedFileList())>0)  ? '&nbsp; <div class="menuCircle">'.count($curObj->attachedFileList()).'</div>'  :  null;
			echo '<div class="objMenuLabel" for="objMenuAttachedFile"><img src="app/img/attachment.png"> '.Txt::trad("EDIT_attachedFileAdd").$AttachedFileNb.'</div>';
		}
		if(!empty($moreOptions)){
			echo '<div class="objMenuLabel" for="objMenuMoreOptions">';
				if(!empty($notifMail))	{echo '<span><img src="app/img/mail.png"> '.Txt::trad("EDIT_notifMail").' &nbsp;</span>';}
				if(!empty($notifMail) && !empty($shortcut))  {echo '<img src="app/img/separator.png">';}
				if(!empty($shortcut))	{echo '<span title="'.Txt::trad("EDIT_shortcutInfo").'" '.(!empty($shortcutChecked)?'class="sLinkSelect"':null).'><img src="app/img/shortcut.png"> '.Txt::trad("EDIT_shortcut").'</span>';}
			echo '</div>';
		}
	echo '</div>';

	//// DIV DES MENUS
	echo '<div id="objMenuBlocks" class="lightboxBlock">';
		////	MENU DES DROITS D'ACCES (OBJETS INDEPENDANTS)
		if(!empty($accessRightMenu))
		{
			echo '<div id="objMenuMain">';
				//DROIT D'ACCES DES BLOCK D'ESPACES
				foreach($spacesAccessRight as $spaceCpt=>$tmpSpace)
				{
					//BLOCK DE L'ESPACE  &&  TABLEAU D'UN ESPACE  &&  ENTETE DE L'ESPACE (nom de l'espace et droits d'acces)
					echo '<div id="spaceTable'.$tmpSpace->_id.'">
							<div class="vSpaceTable noSelect">
								<div class="vSpaceTitle">
									<div class="vSpaceLabel '.($tmpSpace->isCurSpace()?"vSpaceLabelHide":null).'" title="'.$tmpSpace->name.'<br>'.$tmpSpace->description.'">'.Txt::reduce($tmpSpace->name,40).'</div>
									<div class="vSpaceRead noTooltip" title="'.Txt::trad("readInfos").'">'.Txt::trad("accessRead").'</div>
									<div class="vSpaceWriteLimit noTooltip" title="'.$writeReadLimitInfos.'">'.Txt::trad("accessWriteLimit").'</div>
									<div class="vSpaceWrite noTooltip" title="'.Txt::trad("writeInfos").'">'.Txt::trad("accessWrite").'</div>
								  </div>';
							//TARGETS DE L'ESPACE (id des checkboxes deja dans "boxProp"!)
							$tmpSpace->hiddenSelection=false;
							foreach($tmpSpace->targetLines as $targetLine)
							{
								$targetLine["classHideUser"]=(empty($targetLine["isChecked"]) && $tmpSpace->isCurSpace())  ?  'vSpaceHideTarget'  :  null;
								if(!empty($targetLine["classHideUser"]))  {$tmpSpace->hiddenSelection=true;}
								$targetLine["tooltip"]=(!empty($targetLine["tooltip"]))  ?  'title="'.$targetLine["tooltip"].'"'  :  null;
								$userIconClass=(!empty($targetLine["onlyFullAccess"]))  ?  'vSpaceTargetIcon vSpaceTargetIconAdmin'  :  'vSpaceTargetIcon';
								$targetLine["icon"]=(!empty($targetLine["icon"]))  ?  '<img src="app/img/'.$targetLine["icon"].'" class="'.$userIconClass.'">'  :  null;
								echo '<div class="vSpaceTarget sTableRow '.$targetLine["classHideUser"].'" id="targetLine'.$targetLine["targetId"].'">
										<div class="vSpaceLabel" id="'.$targetLine["targetId"].'" '.$targetLine["tooltip"].'>'.$targetLine["icon"].$targetLine["label"].'</div>
										<div class="vSpaceRead noTooltip" title="'.Txt::trad("readInfos").'"><input type="checkbox" name="objectRight[]" '.$targetLine["boxProp"]["1"].'></div>
										<div class="vSpaceWriteLimit noTooltip" title="'.$writeReadLimitInfos.'"><input type="checkbox" name="objectRight[]" '.$targetLine["boxProp"]["1.5"].'></div>
										<div class="vSpaceWrite noTooltip" title="'.Txt::trad("writeInfos").'"><input type="checkbox" name="objectRight[]" '.$targetLine["boxProp"]["2"].'></div>
									  </div>';
							}
							//"AFFICHER PLUS D'UTILISATEURS"
							if($tmpSpace->hiddenSelection==true)  {echo '<div class="vShowAllUsers" for="#spaceTable'.$tmpSpace->_id.'"><div class="vSpaceLabel">'.Txt::trad("EDIT_displayMoreUsers").' <img src="app/img/arrowBottom.png"></div></div>';}
						echo '</div>';
					//BLOCK DE L'ESPACE
					echo '</div>';
				}
				//ETENDRE LES DROITS AUX SOUS-DOSSIERS  &&  "AFFICHER TOUS LES ESPACES"
				if(!empty($extendToSubfolders))  {echo '<div id="ExtendToSubfolders"><hr><input type="checkbox" name="extendToSubfolders" id="extendToSubfolders" value="1" checked="checked"><label for="extendToSubfolders" title="'.Txt::trad("EDIT_accessRightSubFolders_info").'">'.Txt::trad("EDIT_accessRightSubFolders").'</label></div><script>$("#ExtendToSubfolders").pulsate(5);</script>';}
				if(count($spacesAccessRight)>1)  {echo '<div id="ShowAllSpaces"><hr>'.Txt::trad("EDIT_mySpaces").' <img src="app/img/arrowBottom.png"></div><script>$("#ShowAllSpaces").pulsate(2);</script>';}
			echo '</div>';
		}

		////	MENU DES FICHIERS JOINTS
		if(!empty($attachedFiles)){
			echo '<div id="objMenuAttachedFile">'.CtrlObject::attachedFile($curObj).'</div>';
		}

		////	MENU DES NOTIFS MAIL & SHORTCUT
		if(!empty($moreOptions))
		{
			echo '<div id="objMenuMoreOptions">';
			//// MENU DES NOTIFS MAIL
			if(!empty($notifMail))
			{
				//CHECKBOX PRINCIPAL & OPTIONS
				echo '<br><img src="app/img/mail.png">&nbsp;<input type="checkbox" name="notifMail" id="boxNotifMail" value="1" onChange="$(\'#notifMailOptions\').slideToggle();">&nbsp;<label for="boxNotifMail" title="'.Txt::trad("EDIT_notifMailInfo").'">'.Txt::trad("EDIT_notifMail2").'</label>';
				echo '<div id="notifMailOptions">';
					//JOINT LES FICHIERS A LA NOTIFICATION (pas dans les "mailOptions[]" : cf. "actionAddEditFiles()")
					if($curObj::objectType=="file" && $curObj->_id==0)  {echo '<div><img src="app/img/dependency.png"><input type="checkbox" name="notifMailAddFiles" value="1" id="boxNotifMailAddFiles"><label for="boxNotifMailAddFiles" title="'.Txt::trad("FILE_fileSizeLimit").' '.File::displaySize(File::mailMaxFilesSize).'">'.Txt::trad("EDIT_notifMailAddFiles").'</label></div>';}
					//AJOUTE "ReplyTo"  &&  AJOUTE UN ACCUSÉ DE RÉCEPTION
					if(!empty(Ctrl::$curUser->mail)){
						echo '<div title="'.Txt::trad("MAIL_addReplyToInfo").'"><img src="app/img/dependency.png"><input type="checkbox" name="mailOptions[]" value="addReplyTo" id="addReplyTo"> <label for="addReplyTo">'.Txt::trad("MAIL_addReplyTo").'</label></div>
							  <div title="'.Txt::trad("MAIL_receptionNotifInfo").'"><img src="app/img/dependency.png"><input type="checkbox" name="mailOptions[]" value="receptionNotif" id="receptionNotif"> <label for="receptionNotif">'.Txt::trad("MAIL_receptionNotif").'</label></div>';
					}
					//MASQUER LES DESTINATAIRES
					echo '<div title="'.Txt::trad("MAIL_hideRecipientsInfo").'"><img src="app/img/dependency.png"><input type="checkbox" name="mailOptions[]" value="hideRecipients" id="hideRecipients"> <label for="hideRecipients">'.Txt::trad("MAIL_hideRecipients").'</label></div>';
					//SPECIFIER LES DESTINATAIRES && LISTE DETAILLE DES UTILISATEURS
					echo '<div><img src="app/img/dependency.png"><input type="checkbox" name="notifMailSelect" id="boxNotifMailSelect" value="1" onclick="$(\'#notifMailSelectList\').slideToggle();"><label for="boxNotifMailSelect">'.Txt::trad("EDIT_notifMailSelect").' <img src="app/img/user/userGroup.png"></label></div>';
					echo '<div id="notifMailSelectList">';
						//Affiche les users de tous mes espaces
						foreach($notifMailUsers as $tmpUser){
							echo '<div id="divNotifMailUser'.$tmpUser->_id.'" '.(!in_array($tmpUser->_id,$curSpaceUsersIds)?'style="display:none"':null).'>
									<input type="checkbox" name="notifMailUsers[]" value="'.$tmpUser->_id.'" id="boxNotif'.$tmpUser->_typeId.'" data-idUser="'.$tmpUser->_id.'">
									<label for="boxNotif'.$tmpUser->_typeId.'" title="'.$tmpUser->mail.'">'.$tmpUser->getLabel().'</label>
								  </div>';
						}
						//Selection d'un groupe d'utilisateurs
						if(!empty($curSpaceUserGroups))  {echo '<hr>';}
						foreach($curSpaceUserGroups as $tmpGroup){
							echo '<div title="'.Txt::trad("selectUnselect").' :<br> '.$tmpGroup->usersLabel.'">
									<input type="checkbox" name="notifUsersGroup[]" value="'.implode(",",$tmpGroup->userIds).'" id="notifUsersGroup'.$tmpGroup->_typeId.'" onchange="userGroupSelect(this,\'#notifMailSelectList\');">
									<label for="notifUsersGroup'.$tmpGroup->_typeId.'"><img src="app/img/user/userGroup.png"> '.$tmpGroup->title.'</label>
								  </div>';
						}
					echo '</div>';
					//Masque par défaut les users absent de l'espace courant
					if(count($notifMailUsers)>count($curSpaceUsersIds))  {echo '<br><div onclick="$(\'[id^=divNotifMailUser]\').fadeIn();$(this).fadeOut();" class="sLink">'.Txt::trad("EDIT_displayMoreUsers").'</div>';}
				echo '</div>';
			}
			//// MENU SHORTCUT (raccourci)
			if(!empty($shortcut))  {echo '<br><br><img src="app/img/shortcut.png">&nbsp;<input type="checkbox" name="shortcut" id="boxShortcut" value="1" '.$shortcutChecked.'>&nbsp;<label for="boxShortcut">'.Txt::trad("EDIT_shortcutInfo").'</label>';}
			//// Fin du menu
			echo '</div>';
		}
	//// Fin des menus
	echo '</div>';
}

////	BOUTON DE VALIDATION ET INPUTS HIDDEN ("ctrl"/"action"/etc)  &&  "_idContainer"  &&  ICONE "LOADING"
echo Txt::submitButton();
if(!empty($curObj->_idContainer))  {echo '<input type="hidden" name="_idContainer" value="'.$curObj->_idContainer .'">';}