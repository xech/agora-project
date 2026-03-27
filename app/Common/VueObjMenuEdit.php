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
	$(".vAffectLabel").on("click",function(){
		let affectLine=$(this).closest(".vAffectLine");
		let boxRead			=$(affectLine).find("input[value$='_1']");
		let boxWriteLimit	=$(affectLine).find("input[value$='_1.5']");
		let boxWrite		=$(affectLine).find("input[value$='_2']");
		let notChecked 		=":not(:checked)";
		let available  		=":not(:checked):not(:disabled)";
		if($(boxRead).is(available)  &&  $(boxWrite).is(notChecked)  &&  ($(boxWriteLimit).is(notChecked) || $(boxWriteLimit).exist()==false))	{boxToCheck=boxRead;}		//boxRead 		: si dispo et les autres box décochées
		else if($(boxWriteLimit).is(available)  &&  $(boxRead).is(":checked"))																	{boxToCheck=boxWriteLimit;}	//boxWriteLimit : si dispo et boxRead déjà coché
		else if($(boxWrite).is(available)  &&  ($(boxRead).is(available)==false || $(boxWriteLimit).is(":checked")))							{boxToCheck=boxWrite;}		//boxWrite 		: si dispo et les autres indispo/coché
		else																																	{boxToCheck=null;}			//Tout décoché
		$(affectLine).find("input:not(:disabled)").prop("checked",false);	//Uncheck toutes les checkboxes de la ligne (non disabled)
		if(boxToCheck!=null)  {$(boxToCheck).prop("checked",true);}			//Sélectionne la boxToCheck
		accessRightStyle();													//Style des affectations
	});

	/**********************************************************************************************************
	 *	AFFECTATIONS : CLICK LA CHECKBOX D'UNE AFFECTATION
	 **********************************************************************************************************/
	$(".vAffectBox input").on("change",function(){
		$(this).closest(".vAffectLine").find("input:not(:disabled)").not(this).prop("checked",false);	//Uncheck les autres checkboxes de la ligne (non disabled)
		accessRightStyle();																				//Style des affectations
	});

	/**********************************************************************************************************
	 *	AFFECTATIONS : CLICK  "AFFICHER TOUS LES USERS " OU  "AFFICHER TOUS LES ESPACES"
	 **********************************************************************************************************/
	$("#showAllSpaces, #showAllUsers").on("click",function(){
		if(this.id=="showAllSpaces")	{$(".vSpaceTable, .vAffectLine").show();}		//#showAllSpaces : affiche tous les espaces et targets masqués
		if(this.id=="showAllUsers"){													//#showAllUsers  :
			$(".vSpaceTable:visible .vAffectLine").show();								//Affiche les targets masquées des espaces visibles (avec dejà des affectations)
			if($(".vSpaceTable:not(:visible)").exist())  {$("#showAllSpaces").show();}	//D'autres espaces sont masqués : affiche #showAllSpaces
		}
		$(this).hide();		//Masque le lien clické
		accessRightStyle();	//Style des affectations
		lightboxResize();	//Resize le lightbox
	});

	/**********************************************************************************************************
	 *	AFFECTATIONS : INIT
	 **********************************************************************************************************/
	$(".vSpaceTable:not(:has(input:checked)), .vAffectLine:not(:has(input:checked))").hide();				//Masque les espaces et lignes sans affectation
	if($(".vSpaceTable:visible:has(.vAffectLine:not(:visible))").exist())	{$("#showAllUsers").show();}	//Espaces affichés avec des lignes masquées : #showAllUsers
	else if($(".vSpaceTable:not(:visible)").exist()) 						{$("#showAllSpaces").show();}	//Espaces masqués : #showAllSpaces
	accessRightStyle();																						//Style des affectations
	setTimeout(function(){ lightboxResize(); },1500);														//2ème resize après celui de "mainTriggers()" (si beaucoup de droits d'accès à afficher) 
});

/**********************************************************************************************************
 *	AFFECTATIONS : STYLE DES LABELS/LIGNES
 **********************************************************************************************************/
function accessRightStyle()
{
	$(".vAffectLine:visible").removeClass("lineSelect sAccessRead sAccessWrite");	//Réinit le style des lignes
	$(".vAffectLine:has(input:checked)").each(function(){							//Parcourt les lignes sélectionnées
		if($(this).find("input[value$='_2']").is(":checked"))	{$(this).addClass("lineSelect sAccessWrite");}
		else													{$(this).addClass("lineSelect sAccessRead");}
	});
}
</script>


<style>
/*OPTIONS D'EDITION (cf. white.css & black.css) */
#objMenuTabs						{margin-top:35px; margin-bottom:-35px; display:table; width:100%; max-width:100%;}
.objMenuTab							{display:table-cell; width:auto; height:50px; padding:10px 5px; opacity:0.75; text-align:center; vertical-align:middle; word-wrap:break-word; border-radius:8px 8px 0px 0px; user-select:none; cursor:pointer;}
.objMenuTabSelect					{opacity:1; border-bottom:none;}
.objMenuTab[for='menuAccessRight']	{min-width:150px;}/*onglet des droits d'accès*/
.objMenuTab img						{margin-right:10px;}
#objMenuTabs:has(.objMenuTab:nth-child(4)) img {margin-right:6px;} /*Edit Task : réduit le margin si ya 4 onglets dans le menu*/
.objMenuMain						{margin-top:35px; padding:30px; border-top:0px; border-radius:0px 0px 8px 8px; text-align:left;}

/*DROITS D'ACCÈS*/
#menuAccessRight					{text-align:center;}/*Tableau des droits d'accès*/
.vSpaceTable						{display:inline-block; user-select:none; max-width:600px; border:1px solid <?= Ctrl::$agora->skin=='white'?'#e5e5e5':'#555' ?>; border-radius:8px;}
.vSpaceTable:not(:last-child)		{margin-bottom:40px;}
.vSpaceTable>div					{display:table-row;}
.vSpaceTable>div>div				{display:table-cell; padding:6px; text-align:center;}
.vSpaceHeader>div					{vertical-align:top; padding-block:10px!important;}
.vSpaceHeader .vAffectLabel			{padding-left:35px; font-style:italic;}/*Nom de l'espace*/
.vAffectLabel						{width:300px; text-align:left!important; cursor:pointer;}/*Label d'une target*/
.vAffectLabel img					{margin-right:8px;}
.vAffectBox							{width:70px;}/*colonne des checkboxes*/
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
	#objMenuTabs						{font-size:0.95rem; table-layout:fixed;}	/*meme largeur pour chaque colonne*/
	.objMenuMain						{padding-inline:10px;}						/*détail des options*/
	.vSpaceTable						{border:0px;}								/*tableau des droits d'accès*/
	.vSpaceTable>div>div				{padding:8px 3px;}							/*cellules du tableau des droits d'accès*/
	.vSpaceHeader .vAffectBox			{font-size:0.85rem;} 						/*entête des droits d'accès*/
	.vSpaceHeader .vAffectLabel			{padding-left:5px;}							/*Nom de l'espace*/
	.vAffectBox							{width:50px;}								/*colonne des checkboxes des droits d'accès*/
	.objMenuTab img, .vSpaceTable img, .vAffectLabel img  {display:none;}
}
</style>


<!--AFFICHE LES MENUS-->
<?php if(Ctrl::$curUser->isUser() && (!empty($menuAccessRight) || !empty($menuNotifMail) || !empty($menuAttachedFile) || !empty($menuShortcut))){ ?>


	<!--ONGLETS DES MENUS-->
	<div id="objMenuTabs">
		<!--AFFECTATIONS-->
		<?php if(!empty($menuAccessRight)){ ?>
			<div class="objMenuTab" for="menuAccessRight"><img src="app/img/eye.png"><?= Txt::trad("EDIT_accessRight") ?></div>
		<?php } ?>
		<!--NOTIF MAIL-->
		<?php if(!empty($menuNotifMail)){ ?>
			<div class="objMenuTab" for="menuNotifMail"><img src="app/img/mail.png"><?= Txt::trad("EDIT_notifMail") ?></div>
		<?php } ?>
		<!--FICHIER JOINT-->
		<?php if(!empty($menuAttachedFile)){ ?>
			<div class="objMenuTab" for="menuAttachedFile"><img src="app/img/attachment.png"><?= Txt::trad("EDIT_attachedFileAdd") ?>&nbsp;<?= !empty($attachedFilesNb)?'<span class="circleNb">'.$attachedFilesNb.'</span>':null ?></div>
		<?php } ?>
		<!--SHORTCUT-->
		<?php if(!empty($menuShortcut)){ ?>
			<div class="objMenuTab" for="menuShortcut"><img src="app/img/shortcut.png"><?= Txt::trad("EDIT_shortcut").($curObj->shortcut?' * ':null) ?></div>
		<?php } ?>
	</div>


	<!--MENU DES AFFECTATIONS / DROITS D'ACCES-->
	<?php if(!empty($menuAccessRight)){ ?>
		<div class="objMenuMain" id="menuAccessRight">
			<!--AFFECTATIONS POUR CHAQUE ESPACE-->
			<?php foreach($spaceAffectations as $tmpSpace){ ?>
				<!--TABLEAU DE L'ESPACE COURANT-->
				<div class="vSpaceTable">
					<!--ENTETE-->
					<div class="vSpaceHeader">
						<div class="vAffectLabel" <?= Txt::tooltip($tmpSpace->name.'<br>'.$tmpSpace->description) ?>><?= Txt::reduce($tmpSpace->name) ?></div>
						<div class="vAffectBox"><?= Txt::trad("accessRead") ?></div>
						<?php if($curObj::isContainer()){ ?><div class="vAffectBox"><?= Txt::trad("accessWriteLimit") ?></div><?php } ?>
						<div class="vAffectBox"><?= Txt::trad("accessWrite") ?></div>
					</div>
					<!--AFFECTIONS SUR L'ESPACE-->
					<?php foreach($tmpSpace->targetLines as $targetId=>$targetTmp){ ?>
						<div class="vAffectLine lineHover" id="targetLine_<?= $targetId ?>">
							<div class="vAffectLabel" <?= $targetTmp["tooltip"] ?> ><img src="app/img/user/<?= $targetTmp["icon"] ?>"><?= $targetTmp["label"] ?></div>
							<!--CHECKBOXES DE LA TARGET-->
							<?php foreach($targetTmp["checkboxes"] as $tmpRight=>$tmpAttr){ ?>
								<div class="vAffectBox" <?= $affectTooltips[$tmpRight] ?>><input type="checkbox" name="objectRight[]" <?= $tmpAttr ?>></div>
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
							<input type="checkbox" name="notifUsersGroup[]" value="<?= implode(",",$tmpGroup->userIds) ?>" id="notifUsersGroup<?= $tmpGroup->typeId ?>" onchange="userGroupSelect(this,'#notifMailSelectList')">
							<label for="notifUsersGroup<?= $tmpGroup->typeId ?>"><img src="app/img/user/accessGroup.png"> <?= $tmpGroup->title ?></label>
						</div>
					<?php } ?>
					<!--LISTE DE TOUS LES USERS (par défaut ceux de l'espace courant)-->
					<?php foreach($notifMailUsers as $tmpUser){ ?>
						<div id="divNotifMailUser<?= $tmpUser->_id ?>" <?= !in_array($tmpUser->_id,$curSpaceUsersIds) ? 'style="display:none"' : null ?>>
							<input type="checkbox" name="notifMailUsers[]" value="<?= $tmpUser->_id ?>" id="notifMailUsersBox<?= $tmpUser->typeId ?>" data-idUser="<?= $tmpUser->_id ?>">
							<label for="notifMailUsersBox<?= $tmpUser->typeId ?>" <?= $tmpUser->userMailDisplay() ? Txt::tooltip($tmpUser->mail) : null ?> ><?= $tmpUser->getLabel() ?></label>
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