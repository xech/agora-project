<script>
ready(function(){
	/********************************************************************************************************
	 *	MOBILE : DÉPLACE LE MENU DES DESTINATAIRES À COTÉ DU MENU DES "OPTIONS"
	 ********************************************************************************************************/
	if(isMobile()){
		$("#recipientMainMenu>*").appendTo("#mobileRecipients");
		$("#recipientMainMenu").hide();	//Masque le block vide
		$("#mobileRecipients").show();	//Affiche le block des destinataires
	}

	/********************************************************************************************************
	 *	PRÉSELECTIONNE LE TITRE DU MAIL
	 ********************************************************************************************************/
	$("[name='title']").focusAlt();

	/********************************************************************************************************
	 *	INIT L'AFFICHAGE DE L'ARBORESCENCE DE CONTACTS
	 ********************************************************************************************************/
	$(".vMailsBlock").each(function(){
		var folderTreeLevel=this.getAttribute("data-folder-tree-level");
		if(typeof folderTreeLevel!=="undefined" && folderTreeLevel>0)
			{$(this).css("padding-left",(folderTreeLevel*18)+"px");}
	});
	
	/********************************************************************************************************
	 *	AJOUTE UNE NOUVELLE UNE URL DE VISIO
	 ********************************************************************************************************/
	$("#visioUrlAdd").on("click",async function(){
		if(await confirmAlt("<?= Txt::trad("VISIO_urlAddConfirm") ?>")){
			let visioUrl ="<?= Ctrl::$agora->visioUrl() ?>";
			tinymce.activeEditor.insertContent('<p>&nbsp;</p><b><?= Txt::trad("VISIO_launch") ?> : <a href="'+visioUrl+'" target="_blank">'+visioUrl+'</a></b>');
		}
	});

	/********************************************************************************************************
	 *	CONTROLE FINAL DU FORMULAIRE (TESTER AVEC INSERTION D'IMAGE)
	*******************************************************************************************/
	$("#mainForm").on("submit", async function(event){
		event.preventDefault();
		if(await confirmAlt("<?= Txt::trad("MAIL_sendMail") ?> ?")){
			////	Sélection d'une personne, d'un titre et d'un description
			if($("[name='personList[]']:checked, [name='groupList[]']:checked").length==0)	{notify("<?= Txt::trad("MAIL_specifyMail") ?>");	return false;}
			else if($("[name='title']").isEmpty())											{notify("<?= Txt::trad("requiredFields")." : ".Txt::trad("MAIL_title") ?>");		return false;}
			else if(isEmptyEditor())														{notify("<?= Txt::trad("requiredFields")." : ".Txt::trad("MAIL_description") ?>");  return false;}
			////	Fichiers joints > 25Mo ?
			var filesSize=0;
			$(".attachedFileInput").each(function(){  if($(this).notEmpty())  {filesSize+=this.files[0].size;}  });
			if(filesSize > <?= File::mailMaxFilesSize ?>  && await confirmAlt("<?= str_replace("--MAXFILESSIZE--",File::mailMaxFilesSizeLabel,Txt::trad("MAIL_maxFileSizeConfirm")) ?>")==false)  {return false;}
			////	Valide le formulaire
			asyncSubmit(this);
		}
	});
});
</script>


<style>
/*Menu "Historique" & "Destinataires"*/
#historyLabel, #recipientLabel		{text-align:center;}
#historyLabel						{border-bottom:solid 1px #bbb; margin-top:20px; padding:20px!important;}
#recipientLabel						{padding:10px;}
.vMailsBlock						{margin-bottom:20px;}
.vMailsLabel 						{display:table; margin-bottom:10px;}
.vMailsLabel>div 					{display:table-cell; vertical-align:middle;}
.vMailsLabel img					{margin-right:10px;}
.vMailsMenu							{padding-left:5px!important;}
.vMailsMenu:not(.vMailsMenuDisplay,:has(input:checked)) {display:none;}
.vMailsMenu>div						{padding:5px;}
.vMailsMenu img[src*=check]			{margin-right:4px;}

/*formulaire principal*/
#pageContent .miscContent			{padding:15px;}
#pageContent [name='title']			{width:100%; height:35px; margin-bottom:20px;}
#mailOptions						{display:table; width:100%; margin-top:30px;}/*tableau d'options*/
#mailOptions>div					{display:table-cell; width:33%; vertical-align:top;}/*colonnes d'options et bouton "Envoyer"*/
#mailOptions>div>div				{padding-block:5px;}/*ligne d'option*/
#mailOptions img[src*=dependency]	{display:none;}
.submitButton						{text-align:right; margin-top:0px;}/*surcharge*/
.submitButton button				{width:220px;}

/*** RESPONSIVE TABLET-SMARTPHONE*/
@media screen and (max-width:1199px){
	#historyLabel					{border-bottom:none; margin:0px;}
	#mobileRecipients, #mailOptions	{margin-top:30px; border:1px solid #ccc; border-radius:3px;}
	#mailOptions, #mailOptions>div	{display:block; width:100%;}
	#mailOptions>div>div			{padding:10px 5px;}/*ligne d'option*/
	.submitButton					{text-align:center; margin-block:30px;}/*surcharge*/
}
</style>


<div id="pageCenter">
	<form action="index.php" method="post" id="mainForm" enctype="multipart/form-data">
		<div id="pageMenu">
			<!--DESTINATAIRES DU PRESENT MAIL-->
			<div id="recipientMainMenu" class="miscContent" >
				<div id="recipientLabel" <?= Txt::tooltip("MAIL_recipientsTooltip") ?>><img src="app/img/mail.png">&nbsp; <?= Txt::trad("MAIL_recipients") ?> <img src="app/img/arrowRight.png"><hr></div>
				<?php
				////	LISTE DES DESTINATAIRES : USERS D'UN ESPACE || CONTACTS D'UN DOSSIER
				foreach($containerList as $tmpContainer){
					$containerId="mailsContainer".$tmpContainer->typeId;
					$personInputSelector='#'.$containerId.' .vMailPersonInput';
					$containerClass=($tmpContainer->typeId==Ctrl::$curSpace->typeId)  ?  "vMailsMenuDisplay"  :  null;//par défaut, on n'affiche que les users de l'espace courant
					$containerTreeLevel=($tmpContainer::isFolder==true)  ?  'data-folder-tree-level="'.$tmpContainer->treeLevel.'"'  :  null;
				?>
					<!--BLOCK D'USERS/CONTACTS-->
					<div class="vMailsBlock" <?= $containerTreeLevel ?> >
						<div class="vMailsLabel link" onclick="$('#<?= $containerId ?>').slideToggle();">
							<div><img src="app/img/mail/<?= $tmpContainer::objectType=="space"?'user.png':'contact.png' ?>"></div>
							<div><?= $tmpContainer->name ?> <img src="app/img/arrowBottom.png"></div>
						</div>
						<div class="vMailsMenu <?= $containerClass ?>" id="<?= $containerId ?>">
							<?php
							////	LISTE DES INPUTS DE PERSONNES
							foreach($tmpContainer->personList as $tmpPerson){
								if(empty($tmpPerson->mail))  {continue;}																	//zap les personnes sans mail
								$inputId=$tmpContainer->typeId.$tmpPerson->typeId;															//Id de l'input
								$inputAttributes=null;																						//Init les Attributs de l'input
								if(Req::param("checkMail")==$tmpPerson->mail)	{$inputAttributes.=' checked ';}							//Préselectionne le mail
								if($tmpPerson::objectType=="user")				{$inputAttributes.=' data-iduser="'.$tmpPerson->_id.'" ';}	//cf selectUsersGroups()
								$personTooltip=($tmpPerson->userMailDisplay())  ?  Txt::tooltip($tmpPerson->mail)  :  null;
							?>
								<div <?= $personTooltip ?>>
									<input type="checkbox" name="personList[]" value="<?= $tmpPerson->typeId ?>" class="vMailPersonInput" id="<?= $inputId ?>" <?= $inputAttributes ?>> 
									<label for="<?= $inputId ?>"><?= $tmpPerson->getLabel() ?></label>
								</div>
							<?php } ?>

							<!--SELECTION D'USERS & GROUPES-->
							<?= $tmpContainer::objectType=="space" ?  MdlUser::selectUsersGroups($tmpContainer,$personInputSelector)  : null ?>
							<!--SELECTION DE CONTACTS-->
							<?php if($tmpContainer::objectType=="contactFolder"){ ?>
								<div onclick="$('<?= $personInputSelector ?>').prop('checked',false).trigger('click')"><img src="app/img/checkSelectAll.png"> <?= Txt::trad("selectAll") ?></div>
								<div onclick="$('<?= $personInputSelector ?>').prop('checked',true).trigger('click')"><img src="app/img/checkUnselectAll.png"> <?= Txt::trad("unselectAll") ?></div>
							<?php } ?>
						</div>
					</div>
				<?php } ?>
			</div>

			<!--HISTORIQUE DES MAILS ENVOYES-->
			<div id="historyLabel" class="miscContent" onclick="lightboxOpen('?ctrl=mail&action=mailHistory');"><img src="app/img/log.png"> <?= Txt::trad("MAIL_historyTitle") ?></div>
		</div>

		<div id="pageContent">
			<div class="miscContent">
				<!--TITRE / DESCRIPTION / MOBILE : LISTE DES DESTINATAIRES-->
				<input type="text" name="title" value="<?= $curObj->title ?>" placeholder="<?= Txt::trad("MAIL_title") ?>" required>
				<?= $curObj->descriptionEditor(false) ?>
				<div id="mobileRecipients"></div>

				<!--OPTIONS DU MAIL-->
				<div id="mailOptions">
					<div>
						<!--OPTIONS DE BASE DES EMAILS-->
						<?= MdlObject::menuSendMail() ?>
					</div>
					<div>
						<!--AJOUTER UNE VISIO-->
						<?php if(Ctrl::$agora->visioEnabled()){ ?>
							<div id="visioUrlAdd" class="link" <?= Txt::tooltip("VISIO_urlAddConfirm") ?> ><img src="app/img/visioSmall.png">&nbsp;<?= Txt::trad("VISIO_urlAdd") ?></div>
						<?php } ?>
						<!--JOINDRE DES FICHIERS-->
						<?= $curObj->attachedFileEdit() ?>
					</div>
					<div>
						<!--BOUTON"SUBMIT"-->
						<?= Txt::submitButton("<img src='app/img/mailSend.png'>&nbsp; ".Txt::trad("MAIL_sendMail")) ?>
						<!--EMAIL RAPPELÉ (cf "reloadMailTypeId")-->
						<?php if(Req::isParam("reloadMailTypeId")){ ?><input type="hidden" name="reloadMailTypeId" value="<?= Req::param("reloadMailTypeId") ?>"><?php } ?>
					</div>
				</div>
			</div>
		</div>
	</form>
</div>