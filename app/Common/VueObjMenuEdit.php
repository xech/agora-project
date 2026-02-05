<script>
ready(function(){
	/**********************************************************************************************************
	 *	CONTROLE DU FORMULAIRE PRINCIPAL
	 **********************************************************************************************************/
	$("#mainForm").on("submit", async function(event){
		event.preventDefault();
		////	Controles asynchrones :  Captcha (guest)  ||  Controle spécifique (ex: controle Ajax de champs)
		if(typeof captchaControl=="function"  && await captchaControl()!=true)		{return false;}
		if(typeof mainFormControl=="function" && await mainFormControl()!=true)		{return false;}
		////	Controle les mails
		if($("input[name='mail']").notEmpty() && $("input[name='mail']").isMail()==false)   {notify("<?= Txt::trad("mailInvalid"); ?>");  return false;}
		////	Controle la description de l'éditeur
		<?php if($curObj::descriptionEditor==true && in_array("description",$curObj::$requiredFields)){ ?>
		if(isEmptyEditor())  {notify("<?= Txt::trad("requiredFields")." <i>".Txt::trad("description") ?></i>");  return false;}
		<?php } ?>
		////	Controle les affectations
		if($("input[name='objectRight[]']").exist()){
			let curUserSelector=":checked[name='objectRight[]'][value*='spaceUsers'], :checked[name='objectRight[]'][value*='_U<?= Ctrl::$curUser->_id ?>_']";
			if($(":checked[name='objectRight[]']").length==0)   {notify("<?= Txt::trad("EDIT_notifNoSelection") ?>");  return false;}		//Aucune affectation
			if($(curUserSelector).length==0  &&  await confirmAlt("<?= Txt::trad("EDIT_notifNoPersoAccess") ?>")==false)  {return false;}	//Aucune affectation à l'user courant
		}
		////	Valide le formulaire
		asyncSubmit(this);
	});

	/**********************************************************************************************************
	 *	INIT LES CHAMPS OBLIGATOIRES "required"
	 **********************************************************************************************************/
	<?php foreach($curObj::$requiredFields as $tmKey=>$tmpField){ ?>
		$("input[name=<?= $tmpField ?>]").attr("required","true");							// Ajoute "required" sur les champs obligatoires
		<?php if($tmKey==0){ ?> $("input[name=<?= $tmpField ?>]").focusAlt(); <?php } ?>	// Focus sur le premier champ obligatoire
	<?php } ?>

	/**********************************************************************************************************
	 *	CLICK SUR UN ONGLET DU MENU PRINCIPAL (".objMenuTab" doit avoir un "for" correspondant à au div du menu)
	 **********************************************************************************************************/
	$(".objMenuTab").on("click",function(){
		$(".objMenuTab").removeClass("objMenuTabSelect");	//Réinit tous les onglets
		$(".objMenuMain").hide();							//Réinit tous les menus
		$(this).addClass("objMenuTabSelect");				//Sélectionne l'onglet
		$("#"+this.getAttribute("for")).fadeIn();			//Affiche le menu associé
	});
	////	Init : selectionne le premier onglet
	$(".objMenuTab:first-child").trigger("click");

	/**********************************************************************************************************
	 *	AFFECTATIONS : CLICK LE LABEL D'UNE AFFECTATION
	 **********************************************************************************************************/
	$(".vTargetLabel").on("click",function(){
		let targetLineId	="#"+$(this).closest(".vTargetLine").attr("id");
		let boxRead			=targetLineId+" input[value$='_1']";
		let boxWriteLimit	=targetLineId+" input[value$='_1.5']";
		let boxWrite		=targetLineId+" input[value$='_2']";
		let available  		=":not(:checked):not(:disabled)";
		let boxToCheck		=null;
		if($(boxRead).is(available)  &&  $(boxWrite).is(":not(:checked)")  &&  ($(boxWriteLimit).is(":not(:checked)") || $(boxWriteLimit).exist()==false))	{boxToCheck=boxRead;}		//Si autres box décochées
		else if($(boxWriteLimit).is(available)  &&  $(boxRead).is(":checked"))																				{boxToCheck=boxWriteLimit;}	//Si '1' est coché
		else if($(boxWrite).is(available)  &&  ($(boxRead).is(available)==false || $(boxWriteLimit).is(":checked")))										{boxToCheck=boxWrite;}		//Si '1.5' est coché ou '1' indisponible
		if(boxToCheck!=null)	{$(boxToCheck).prop("checked",true).trigger("change");}					//Check avec trigger pour accessRightStyle()
		else					{$(targetLineId+" input").prop("checked",false).trigger("change");}		//Uncheck les autres box de la ligne
	});

	/**********************************************************************************************************
	 *	AFFECTATIONS : CLICK LA CHECKBOX D'UNE AFFECTATION
	 **********************************************************************************************************/
	$(".vTargetBox input").on("change",function(){
		let targetLineId="#"+$(this).closest(".vTargetLine").attr("id");	//Id de la ligne
		$(targetLineId+" input").not(this).prop("checked",false);			//"uncheck" les autres box de la ligne
		accessRightStyle();													//Style des labels des targets
	});

	/**********************************************************************************************************
	 *	AFFECTATIONS : CLICK  "AFFICHER TOUS LES USERS " OU  "AFFICHER TOUS LES ESPACES"
	 **********************************************************************************************************/
	$("#showAllSpaces, #showAllUsers").on("click",function(){
		if(this.id=="showAllSpaces")	{$(".vSpaceTable, .vTargetLine").show();}		//#showAllSpaces : affiche tous les espaces et targets masqués
		else{																			//#showAllUsers  :
			$(".vSpaceTable:visible .vTargetLine").show();								//Affiche les targets masquées des espaces visibles (avec dejà des affectations)
			if($(".vSpaceTable:not(:visible)").exist())  {$("#showAllSpaces").show();}	//D'autres espaces sont masqués : affiche #showAllSpaces
		}
		$(this).hide();			//Masque #showAllSpaces / #showAllUsers
		accessRightStyle();		//Style des labels des targets
		lightboxResize();		//Resize le lightbox
	});

	/**********************************************************************************************************
	 *	AFFECTATIONS : INIT
	 **********************************************************************************************************/
	$(".vSpaceTable:not(:has(input:checked)), .vTargetLine:not(:has(input:checked))").hide();				//Masque les espaces et targets sans affectation
	if($(".vSpaceTable:visible:has(.vTargetLine:not(:visible))").exist())	{$("#showAllUsers").show();}	//Espaces affichés avec des lignes masquées : #showAllUsers
	else if($(".vSpaceTable:not(:visible)").exist()) 						{$("#showAllSpaces").show();}	//D'autres espaces masqués : #showAllSpaces
	accessRightStyle();																						//Style des labels des targets
	setTimeout(function(){ lightboxResize(); },1500);														//2ème resize après celui de "mainTriggers()" (si beaucoup de droits d'accès à afficher) 
});

/**********************************************************************************************************
 *	AFFECTATIONS : STYLISE LES LABELS DES TARGETS VISIBLES
 **********************************************************************************************************/
function accessRightStyle()
{
	$(".vTargetLine:visible").removeClass("lineSelect sAccessRead sAccessWrite");	//Réinit le style des lignes
	$(".vTargetLine:has(input:checked)").each(function(){							//Style chaque ligne sélectionnée
		let lineId="#"+this.id;
		if($(lineId+" input[value$='_1']").is(":checked"))	{$(lineId).addClass("lineSelect sAccessRead");}
		else												{$(lineId).addClass("lineSelect sAccessWrite");}
	});
}
</script>


<style>
/*OPTIONS D'EDITION (cf. white.css & black.css) */
#objMenuTabs						{margin-top:35px; margin-bottom:-35px; display:table; width:100%; max-width:100%;}
.objMenuTab							{display:table-cell; width:auto; padding:10px 5px; opacity:0.75; text-align:center; border-radius:8px 8px 0px 0px; user-select:none; cursor:pointer;}
.objMenuTabSelect					{opacity:1; border-bottom:none;}
.objMenuTab img						{margin-right:10px;}
.objMenuTab[for='menuAccessRight']	{min-width:150px;}		/*onglet des droits d'accès*/
.objMenuMain						{margin-top:35px; padding:30px; border-top:0px; border-radius:0px 0px 8px 8px; text-align:left;}

/*DROITS D'ACCÈS*/
#menuAccessRight					{text-align:center;}/*Tableau des droits d'accès*/
.vSpaceTable						{display:inline-block; user-select:none; max-width:600px; margin-bottom:40px; border:1px solid <?= Ctrl::$agora->skin=='white'?'#e5e5e5':'#555' ?>; border-radius:8px;}
.vSpaceTable>div					{display:table-row;}
.vSpaceTable>div>div				{display:table-cell; padding:6px; text-align:center;}
.vSpaceHeader>div					{vertical-align:top; padding-block:10px!important;}
.vSpaceHeader .vTargetLabel			{padding-left:35px; font-style:italic;}/*Nom de l'espace*/
.vTargetLabel						{width:300px; text-align:left!important; cursor:pointer;}/*Label d'une target*/
.vTargetLabel img					{margin-right:8px;}
.vTargetBox							{width:70px;}/*colonne des checkboxes*/
#showAllUsers, #showAllSpaces		{display:none; margin-top:-20px;}/*margin-top: cf. vSpaceTable*/

/*MENU DES NOTIFICATIONS PAR MAIL*/
#notifMailOptions>div				{margin-left:15px; margin-top:12px;}
#notifMailOptions>div input			{margin-right:8px;}/*surcharge "VueSendMailOptions.php"*/
#notifMailSelectList				{margin-top:10px; text-align: left;}/*surcharge*/
#notifMailSelectList>div			{display:inline-block; width:230px; padding:5px 2px;}
#notifMailSelectList>div input		{margin-right:5px; margin-bottom:5px;}
#notifMailUsersPlus, #notifMailSelectList, #notifMailOptions  {display:none;}

/*AFFICHAGE SMARTPHONE*/
@media screen and (max-width:490px){
	#objMenuTabs, .objMenuMain			{font-size:0.8rem;}					/*menu des options + Détail des options*/
	.objMenuMain						{padding-inline:10px;}				/*détail des options*/
	.vSpaceTable						{font-size:0.75rem; border:0px;}	/*tableau des droits d'accès*/
	.vSpaceTable>div>div				{padding:8px 3px;}					/*cellules du tableau des droits d'accès*/
	.vSpaceHeader .vTargetLabel			{padding-left:0px;}					/*Nom de l'espace*/
	.vTargetBox							{width:50px;}						/*colonne des checkboxes des droits d'accès*/
	.objMenuTab img, .vSpaceTable img, .vTargetLabel img  {display:none;}
}
</style>


<!--AFFICHE LES MENUS-->
<?php if(Ctrl::$curUser->isUser() && (!empty($menuAccessRight) || !empty($menuNotifMail) || !empty($menuAttachedFile) || !empty($menuShortcut))){ ?>


	<!--ONGLETS DES MENUS (droits d'accès / fichier joint / notif mail / shortcut)-->
	<div id="objMenuTabs">
		<?php if(!empty($menuAccessRight)){ ?>
			<div class="objMenuTab" for="menuAccessRight"><img src="app/img/eye.png"><?= Txt::trad("EDIT_accessRight") ?></div><?php } ?>
		<?php if(!empty($menuNotifMail)){ ?>
			<div class="objMenuTab" for="menuNotifMail"><img src="app/img/mail.png"><?= Txt::trad("EDIT_notifMail") ?></div><?php } ?>
		<?php if(!empty($menuAttachedFile)){ ?>
			<div class="objMenuTab" for="menuAttachedFile"><img src="app/img/attachment.png"><?= Txt::trad("EDIT_attachedFileAdd") ?>&nbsp;<?= !empty($attachedFilesNb)?'<span class="circleNb">'.$attachedFilesNb.'</span>':null ?></div><?php } ?>
		<?php if(!empty($menuShortcut)){ ?>
			<div class="objMenuTab" for="menuShortcut"><img src="app/img/shortcut.png"><?= Txt::trad("EDIT_shortcut").($curObj->shortcut?' * ':null) ?></div><?php } ?>
	</div>


	<!--MENU DES AFFECTATIONS / DROITS D'ACCES-->
	<?php if(!empty($menuAccessRight)){ ?>
		<div class="objMenuMain" id="menuAccessRight">
			<!--DROITS D'ACCES DE CHAQUE ESPACE-->
			<?php foreach($spaceAffectations as $tmpSpace){ ?>
				<!--TABLEAU DE L'ESPACE COURANT-->
				<div class="vSpaceTable">
					<!--ENTETE-->
					<div class="vSpaceHeader">
						<div class="vTargetLabel" <?= Txt::tooltip($tmpSpace->name.'<br>'.$tmpSpace->description) ?>><?= Txt::reduce($tmpSpace->name) ?></div>
						<div class="vTargetBox"><?= Txt::trad("accessRead") ?></div>
						<?php if($curObj::isContainer()){ ?><div class="vTargetBox"><?= Txt::trad("accessWriteLimit") ?></div><?php } ?>
						<div class="vTargetBox"><?= Txt::trad("accessWrite") ?></div>
					</div>
					<!--TARGETS DE L'ESPACE-->
					<?php foreach($tmpSpace->targetLines as $targetId=>$targetTmp){ ?>
						<div class="vTargetLine lineHover" id="targetLine_<?= $targetId ?>">
							<div class="vTargetLabel" <?= $targetTmp["tooltip"] ?> ><img src="app/img/user/<?= $targetTmp["icon"] ?>"><?= $targetTmp["label"] ?></div>
							<!--CHECKBOXES DE LA TARGET-->
							<?php foreach($targetTmp["checkboxes"] as $tmpRight=>$tmpAttr){ ?>
								<div class="vTargetBox" <?= $affectTooltips[$tmpRight] ?>><input type="checkbox" name="objectRight[]" <?= $tmpAttr ?>></div>
							<?php } ?>
						</div>
					<?php } ?>
				</div>
			<?php } ?>
			<!--MENU "AFFICHER TOUS LES USERS" + "AFFICHER TOUS LES ESPACES" (+ d'un user ou espace)-->
			<?php if(count($spaceAffectations)>1 || count($tmpSpace->targetLines)>1){ ?>
				<div id="showAllUsers" class="sLink"><?= Txt::trad("EDIT_showAllUsers") ?> <img src="app/img/arrowBottom.png"></div>
				<div id="showAllSpaces" class="sLink"> <img src="app/img/space.png"> <?= Txt::trad("EDIT_showAllSpaces") ?> <img src="app/img/arrowBottom.png"></div>
			<?php } ?>
			<!--MENU "ETENDRE LES DROITS AUX SOUS-DOSSIERS"-->
			<?php if(!empty($extendSubfolders)){ ?>
				<hr><input type="checkbox" name="extendSubfolders" value="1" id="extendSubfoldersBox">
				<label for="extendSubfoldersBox" <?= Txt::tooltip("EDIT_extendSubfoldersTooltip") ?>><?= Txt::trad("EDIT_extendSubfolders") ?></label>
				<script>$("#extendSubfoldersBox").pulsate(20);</script>
			<?php } ?>
		</div>
	<?php } ?>


	<!--MENU DES NOTIFS MAIL-->
	<?php if(!empty($menuNotifMail)){ ?>
		<div class="objMenuMain" id="menuNotifMail">
			<!--CHECKBOX PRINCIPALE-->
			<input type="checkbox" name="notifMail" value="1" onchange="$('#notifMailOptions').slideToggle()" id="boxNotifMail">
			<label for="boxNotifMail" <?= Txt::tooltip($notifMailTooltip) ?>><?= Txt::trad("EDIT_notifMail2") ?></label>
			<!--BLOCK DES OPTIONS-->
			<div id="notifMailOptions">
				<!--OPTION DU MODULE "FILE" > "Joindre les fichiers à la notification"-->
				<?php if($curObj::objectType=="file"){ ?>
					<div><img src="app/img/dependency.png"><input type="checkbox" name="notifMailAddFiles" value="1" id="boxNotifMailAddFiles"><label for="boxNotifMailAddFiles" <?= Txt::tooltip(Txt::trad("FILE_fileSizeLimit").' '.File::sizeLabel(File::mailMaxFilesSize)) ?> ><?= Txt::trad("EDIT_notifMailAddFiles") ?> <img src="app/img/attachment.png"></label></div>
				<?php } ?>
				<!--OPTIONS DE BASE DES EMAILS-->
				<?= MdlObject::sendMailBasicOptions() ?>
				<!--OPTION POUR CHOISIR LES DESTINATAIRES-->
				<div><img src="app/img/dependency.png"><input type="checkbox" name="notifMailSelect" value="1" onclick="$('#notifMailSelectList').slideToggle();" id="notifMailSelectBox"><label for="notifMailSelectBox"><?= Txt::trad("EDIT_notifMailSelect") ?> <img src="app/img/user/accessAllUsers.png"></label></div>
				<fieldset id="notifMailSelectList">
					<!--GROUPE D'USERS DE L'ESPACE COURANT-->
					<?php foreach($curSpaceUserGroups as $tmpGroup){ ?>
						<div <?= Txt::tooltip(Txt::trad("selectUnselect").' :<br>'.$tmpGroup->usersLabel) ?> >
							<input type="checkbox" name="notifUsersGroup[]" value="<?= implode(",",$tmpGroup->userIds) ?>" id="notifUsersGroup<?= $tmpGroup->_typeId ?>" onchange="userGroupSelect(this,'#notifMailSelectList')">
							<label for="notifUsersGroup<?= $tmpGroup->_typeId ?>"><img src="app/img/user/accessGroup.png"> <?= $tmpGroup->title ?></label>
						</div>
					<?php } ?>
					<!--LISTE DE TOUS LES USERS (par défaut ceux de l'espace courant)-->
					<?php foreach($notifMailUsers as $tmpUser){ ?>
						<div id="divNotifMailUser<?= $tmpUser->_id ?>" <?= !in_array($tmpUser->_id,$curSpaceUsersIds) ? 'style="display:none"' : null ?>>
							<input type="checkbox" name="notifMailUsers[]" value="<?= $tmpUser->_id ?>" id="notifMailUsersBox<?= $tmpUser->_typeId ?>" data-idUser="<?= $tmpUser->_id ?>">
							<label for="notifMailUsersBox<?= $tmpUser->_typeId ?>" <?= $tmpUser->userMailDisplay() ? Txt::tooltip($tmpUser->mail) : null ?> ><?= $tmpUser->getLabel() ?></label>
						</div>
					<?php } ?>
					<!--AFFICHER LES UTILISATEURS DE TOUS LES ESPACES-->
					<?php if(count($notifMailUsers)>count($curSpaceUsersIds)){ ?>
						<div onclick="$('[id^=divNotifMailUser]').fadeIn();$(this).fadeOut()"><img src="app/img/arrowBottom.png"> <?= Txt::trad("EDIT_showAllUsers") ?></div>
					<?php } ?>
				</fieldset>
			</div>
		</div>
	<?php } ?>


	<!--MENU DES FICHIERS JOINTS-->
	<?php if(!empty($menuAttachedFile)){ ?>
		<div class="objMenuMain" id="menuAttachedFile"><?= $curObj->attachedFileEdit() ?></div>
	<?php } ?>


	<!--MENU DES SHORTCUT-->
	<?php if(!empty($menuShortcut)){ ?>
		<div class="objMenuMain" id="menuShortcut">
			<input type="checkbox" name="shortcut" id="boxShortcut" value="1" <?= $curObj->shortcut?'checked':null ?> >
			<label for="boxShortcut"><?= Txt::trad("EDIT_shortcutInfo") ?></label>
		</div>
	<?php } ?>


<?php } ?>


<!--_ID DU CONTENEUR-->
<?php if(!empty($curObj->_idContainer)){ ?>
	<input type="hidden" name="_idContainer" value="<?= $curObj->_idContainer ?>">
<?php } ?>


<!--BOUTON DE VALIDATION-->
<?= Txt::submitButton() ?>