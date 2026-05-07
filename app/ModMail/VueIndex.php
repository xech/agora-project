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
	 *  AFFICHE/MASQUE LES USERS D'UN ESPACE (SAUF ESPACE COURANT)
	 ********************************************************************************************************/
	$(".vMailsLabel").on("click",function(){
		$("#mailsContainer"+this.getAttribute("data-typeid")).slideToggle();
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
.vMailsBlock						{margin:0px 10px 15px 10px;}
.vMailsLabel 						{display:table;}
.vMailsLabel>div 					{display:table-cell; vertical-align:middle;}
.vMailsLabel img					{max-width:24px; margin-right:8px;}
.vMailsMenu							{padding-left:5px!important; display:none;}
.vMailsMenu.vMailsMenuDisplay		{display:block;}
.vMailsMenu>div						{padding:7px;}
.vMailsMenu img[src*=check]			{margin-right:4px;}
/*formulaire principal*/
#pageContent [name='title']			{width:100%; height:35px; margin-bottom:20px;}
#mailOptions						{display:table; width:100%; margin-top:30px;}/*tableau d'options*/
#mailOptions>div					{display:table-cell; width:33%; vertical-align:top;}/*colonnes d'options et bouton "Envoyer"*/
#mailOptions>div>div				{padding-block:5px;}/*ligne d'option*/
#mailOptions img[src*=dependency]	{display:none;}
.submitButtonMain					{text-align:right; margin-top:0px;}/*surcharge*/
.submitButtonMain button			{width:220px; height:50px;}
/*AFFICHAGE RESPONSIVE*/
@media screen and (max-width:1200px){
	#historyLabel					{border-bottom:none; margin:0px;}
	#mobileRecipients, #mailOptions	{margin-top:30px; border:1px solid #ccc; border-radius:3px;}
	#mailOptions, #mailOptions>div	{display:block; width:100%;}
	#mailOptions>div>div			{padding:10px 5px;}/*ligne d'option*/
.submitButtonMain					{text-align:center; margin-block:30px;}/*surcharge*/
}
</style>



<div id="pageCenter">
	<form action="index.php" method="post" id="mainForm" enctype="multipart/form-data">
		<div id="pageMenu">
			<!--DESTINATAIRES DU PRESENT MAIL-->
			<div id="recipientMainMenu" class="miscContent" >
				<div id="recipientLabel" <?= Txt::tooltip("MAIL_recipientsTooltip") ?>><img src="app/img/mail.png">&nbsp; <?= Txt::trad("MAIL_recipients") ?> <img src="app/img/arrowRight.png"><hr></div>
				<?php
				////	LISTE DES DESTINATAIRES : USERS & CONTACTS
				foreach($containerList as $tmpContainer)
				{
					////	INIT
					$cptPerson=0;
					$tmpGroupsFields=$tmpPersonsFields=$tmpSwitchOption=null;
					$mailsMenuClass=($tmpContainer->typeId==Ctrl::$curSpace->typeId)  ?  "vMailsMenuDisplay"  :  null;//par défaut, on n'affiche que les users de l'espace courant
					////	GROUPES D'USERS (prépare l'affichage)
					if($tmpContainer::objectType=="space"){
						foreach(MdlUserGroup::getGroups($tmpContainer) as $tmpGroup){
							$tmpBoxId=$tmpContainer->typeId.$tmpGroup->typeId;
							$tmpGroupsFields.='<div '.Txt::tooltip($tmpGroup->usersLabel).'><input type="checkbox" name="groupList[]" value="'.$tmpGroup->typeId.'" id="'.$tmpBoxId.'"> <label for="'.$tmpBoxId.'"><img src="app/img/user/accessGroup.png"> '.$tmpGroup->title.'</label></div>';
						}
					}
					////	PERSONNES DU CONTENEUR (prépare l'affichage)
					foreach($tmpContainer->personList as $tmpPerson)
					{
						if(empty($tmpPerson->mail))  {continue;}													//zap les personnes sans mail
						if(Req::param("checkMail")==$tmpPerson->mail && empty($personsChecked[$tmpPerson->mail])){	//Préselectionne le mail
							$tmpPerson->mailChecked="checked";														//Checkbox "checked"
							$personsChecked[$tmpPerson->mail]=$tmpPerson->mail;										//Indique qu'il est déjà sélectionné
							$mailsMenuClass="vMailsMenuDisplay";													//Affiche le menu (si besoin)
						}
						$tmpBoxId=$tmpContainer->typeId.$tmpPerson->typeId;
						$userMailTooltip=($tmpPerson->userMailDisplay())  ?  Txt::tooltip($tmpPerson->mail)  :  null;
						$tmpPersonsFields.='<div '.$userMailTooltip.'><input type="checkbox" name="personList[]" value="'.$tmpPerson->typeId.'" id="'.$tmpBoxId.'" '.$tmpPerson->mailChecked.'> <label for="'.$tmpBoxId.'">'.$tmpPerson->getLabel().'</label></div>';
						$cptPerson++;
					}
					////	BOUTON SWITCH LA SELECTION (5 pers. minimum)
					if(count($tmpContainer->personList)>=5){
						$boxSelector="'#mailsContainer".$tmpContainer->typeId." input[name^=personList]'";
						$tmpSwitchOption='<div onclick="$('.$boxSelector.').prop(\'checked\',false).trigger(\'click\')"><img src="app/img/checkAll.png"> '.Txt::trad("selectAll").'</div>
										  <div onclick="$('.$boxSelector.').trigger(\'click\')"><img src="app/img/checkSwitch.png"> '.Txt::trad("selectSwitch").'</div>';
					}
					////	AFFICHE CHAQUE BLOCK D'USERS/CONTACTS
					echo '<div class="vMailsBlock" '.($tmpContainer::isFolder==true?'data-folder-tree-level="'.$tmpContainer->treeLevel.'"':null).'>
							<div class="vMailsLabel sLink" data-typeid="'.$tmpContainer->typeId.'">
								<div><img src="app/img/mail/'.($tmpContainer::objectType=='space'?'user':'contact').'.png"></div>
								<div>'.$tmpContainer->name.' <img src="app/img/arrowBottom.png"></div>
							</div>
							<div class="vMailsMenu '.$mailsMenuClass.'" id="mailsContainer'.$tmpContainer->typeId.'">'.$tmpGroupsFields.$tmpPersonsFields.$tmpSwitchOption.'</div>
						</div>';
				}
				?>
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
						<?= MdlObject::sendMailBasicOptions() ?>
					</div>
					<div>
						<!--AJOUTER UNE VISIO-->
						<?php if(Ctrl::$agora->visioEnabled()){ ?>
							<div id="visioUrlAdd" class="sLink" <?= Txt::tooltip("VISIO_urlAddConfirm") ?> ><img src="app/img/visioSmall.png">&nbsp;<?= Txt::trad("VISIO_urlAdd") ?></div>
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