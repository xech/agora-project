<script>
$(function(){
	/*******************************************************************************************
	 *	MOBILE : DÉPLACE LE MENU DES DESTINATAIRES À COTÉ DU MENU DES "OPTIONS"
	 *******************************************************************************************/
	if(isMobile()){
		$("#recipientMainMenu>*").appendTo("#mobileRecipients");
		$("#mobileRecipients").show();
	}

	/*******************************************************************************************
	 *	PRÉSELECTIONNE LE TITRE DU MAIL
	 *******************************************************************************************/
	if(isMobile()==false){
		$("[name='title']").focus();
	}

	/*******************************************************************************************
	 *	INIT L'AFFICHAGE DE L'ARBORESCENCE DE CONTACTS
	 *******************************************************************************************/
	$(".vMailsBlock").each(function(){
		var folderTreeLevel=$(this).attr("data-folderTreeLevel");
		if(typeof folderTreeLevel!=="undefined" && folderTreeLevel>0)
			{$(this).css("padding-left",(folderTreeLevel*18)+"px");}
	});

	/*******************************************************************************************
	 *  AFFICHE/MASQUE LES USERS D'UN ESPACE (SAUF ESPACE COURANT)
	 *******************************************************************************************/
	$(".vMailsLabel").on("click",function(){
		$("#mailsContainer"+$(this).attr("data-typeId")).slideToggle();
	});
	
	/*******************************************************************************************
	 *	AJOUTE UNE NOUVELLE UNE URL DE VISIO
	 *******************************************************************************************/
	$("#visioUrlAdd").on("click",function(){
		if(confirm("<?= Txt::trad("VISIO_urlMail") ?> ?")){
			var visioUrl="<?= Ctrl::$agora->visioUrl() ?>";
			tinymce.activeEditor.insertContent("<p>&nbsp;</p><b><?= Txt::trad("VISIO_launch") ?> : <a href=\""+visioUrl+"\" target='_blank'>"+visioUrl+"</a></b>");
		}
	});

	/*******************************************************************************************
	 *	CONTROLE FINAL DU FORMULAIRE (TESTER AVEC INSERTION D'IMAGE)
	*******************************************************************************************/
	$("#sendMailForm").submit(function(){
		//// Init
		var validForm=true;
		//// Sélection d'une personne, d'un titre et d'un description
		if($("[name='personList[]']:checked, [name='groupList[]']:checked").length==0)	{validForm=false;  notify("<?= Txt::trad("MAIL_specifyMail") ?>");  }
		else if($("[name='title']").isEmpty())											{validForm=false;  notify("<?= Txt::trad("requiredFields")." : ".Txt::trad("MAIL_title") ?>");}
		else if(isEmptyEditor())														{validForm=false;  notify("<?= Txt::trad("requiredFields")." : ".Txt::trad("MAIL_description") ?>");}
		//// Fichier joint : remplace le "src" des images temporaires (cf. "editDescription()")
		attachedFileSrcReplace();
		//// Fichiers joints : la taille ne doit pas dépasser 15Mo
		var filesSize=0;
		$(".attachedFileInput").each(function(){
			if($(this).isEmpty()==false){
				filesSize+=this.files[0].size;
				if(filesSize > (15 * 1048576))  {validForm=confirm("<?= Txt::trad("MAIL_fileMaxSize") ?>");}
			}
		});
		//// Controle OK : affiche l'icone "loading"
		if(validForm==true)  {submitButtonLoading();}
		return validForm;
	});
});
</script>


<style>
/*Menu "Historique" & "Destinataires"*/
#historyLabel								{border-bottom:solid 1px #bbb; margin-bottom:20px;}
#historyLabel, #recipientLabel				{text-align:center; padding:10px;}
#recipientLabel img							{max-height:22x;}
.vMailsBlock								{margin:0px 10px 15px 10px;}
.vMailsLabel 								{display:table;}
.vMailsLabel>div 							{display:table-cell; vertical-align:middle;}
.vMailsLabel img							{max-width:24px; margin-right:8px;}
.vMailsMenu									{padding-left:5px!important; display:none;}
.vMailsMenu.vMailsMenuDisplay				{display:block;}
.vMailsMenu>div								{padding:7px;}
/*formulaire principal*/
#mailContainer								{padding:10px;}
#mailContainer [name='title']				{width:100%; height:35px; margin-bottom:20px;}
#mailOptions								{display:table; width:100%; margin-top:20px;}
#mailOptions>div							{display:table-cell;}/*options et bouton "Envoyer"*/
#mailOptions>div>div						{margin:10px;}/*lignes des options*/
#mailOptions img[src*=dependency]			{display:none;}
#visioUrlAdd img[src*=plusSmall]			{display:none;}
#visioUrlAdd:hover img[src*=plusSmall]		{display:inline;}
.submitButtonMain button					{width:220px; height:50px;}
/*MOBILE*/
@media screen and (max-width:1023px){
	#historyLabel					{border-bottom:none; margin:0px;}
	#mobileRecipients, #mailOptions	{margin-top:30px; border:1px solid #ccc; border-radius:3px;}
	#mailOptions, #mailOptions>div	{display:inline-block;}
}
</style>


<form action="index.php" method="post" id="sendMailForm" class="confirmCloseForm" enctype="multipart/form-data">
	<div id="pageCenter">
		<div id="pageModuleMenu">
			<div id="pageModMenu" class="miscContainer">
				<!--Historique-->
				<div id="historyLabel" onclick="lightboxOpen('?ctrl=mail&action=mailHistory');"><img src="app/img/log.png"> <?= Txt::trad("MAIL_historyTitle") ?></div>
				<!--Destinataires-->
				<div id="recipientMainMenu">
					<div id="recipientLabel"><img src="app/img/mail.png"> <?= Txt::trad("MAIL_recipients") ?> :<hr></div>
					<?php
					////	LISTE DES DESTINATAIRES : USERS & CONTACTS
					foreach($containerList as $tmpContainer)
					{
						////	INIT
						$cptPerson=0;
						$tmpGroupsFields=$tmpPersonsFields=$tmpSwitchOption=null;
						$mailsMenuClass=($tmpContainer->_typeId==Ctrl::$curSpace->_typeId)  ?  "vMailsMenuDisplay"  :  null;//par défaut, on n'affiche que les users de l'espace courant
						////	GROUPES D'USERS (prépare l'affichage)
						if($tmpContainer::objectType=="space"){
							foreach(MdlUserGroup::getGroups($tmpContainer) as $tmpGroup){
								$tmpBoxId=$tmpContainer->_typeId.$tmpGroup->_typeId;
								$tmpGroupsFields.='<div title="'.Txt::tooltip($tmpGroup->usersLabel).'"><input type="checkbox" name="groupList[]" value="'.$tmpGroup->_typeId.'" id="'.$tmpBoxId.'"> <label for="'.$tmpBoxId.'"><img src="app/img/user/accessGroup.png"> '.$tmpGroup->title.'</label></div>';
							}
						}
						////	PERSONNES DU CONTENEUR (prépare l'affichage)
						foreach($tmpContainer->personList as $tmpPerson)
						{
							if(empty($tmpPerson->mail))  {continue;}														//zap les personnes sans mail
							if(Req::param("checkedMailto")==$tmpPerson->mail && empty($personsChecked[$tmpPerson->mail])){	//Préselectionne le mail
								$tmpPerson->mailChecked="checked";															//Checkbox "checked"
								$personsChecked[$tmpPerson->mail]=$tmpPerson->mail;											//Indique qu'il est déjà sélectionné
								$mailsMenuClass="vMailsMenuDisplay";														//Affiche le menu (si besoin)
							}
							$tmpBoxId=$tmpContainer->_typeId.$tmpPerson->_typeId;
							$userMailTooltip=($tmpPerson->userMailDisplay())  ?  'title="'.Txt::tooltip($tmpPerson->mail).'"'  :  null;
							$tmpPersonsFields.='<div '.$userMailTooltip.'><input type="checkbox" name="personList[]" value="'.$tmpPerson->_typeId.'" id="'.$tmpBoxId.'" '.$tmpPerson->mailChecked.'> <label for="'.$tmpBoxId.'">'.$tmpPerson->getLabel().'</label></div>';
							$cptPerson++;
						}
						////	BOUTON SWITCH LA SELECTION (5 pers. minimum)
						if(count($tmpContainer->personList)>=5)
							{$tmpSwitchOption='<div onclick="$(\'#mailsContainer'.$tmpContainer->_typeId.' input[name^=personList]\').trigger(\'click\')"><img src="app/img/checkSmall.png"> '.Txt::trad("selectSwitch").'</div>';}
						////	AFFICHE CHAQUE BLOCK D'USERS/CONTACTS
						echo '<div class="vMailsBlock" '.($tmpContainer::isFolder==true?'data-folderTreeLevel="'.$tmpContainer->treeLevel.'"':null).'>
								<div class="vMailsLabel sLink" data-typeId="'.$tmpContainer->_typeId.'">
									<div><img src="app/img/mail/'.($tmpContainer::objectType=='space'?'user':'contact').'.png"></div>
									<div>'.$tmpContainer->name.' <img src="app/img/arrowBottom.png"></div>
								</div>
								<div class="vMailsMenu '.$mailsMenuClass.'" id="mailsContainer'.$tmpContainer->_typeId.'">'.$tmpGroupsFields.$tmpPersonsFields.$tmpSwitchOption.'</div>
							</div>';
					}
					?>
				</div>
			</div>
		</div>

		<div id="pageCenterContent">
			<div id="mailContainer" class="miscContainer">

				<!--TITRE / DESCRIPTION-->
				<input type="text" name="title" value="<?= $curObj->title ?>" placeholder="<?= txt::trad("MAIL_title") ?>">
				<?= $curObj->editDescription(false) ?>

				<!--MOBILE : LISTE DES DESTINATAIRES-->
				<div id="mobileRecipients"></div>

				<!--OPTIONS DU MAIL-->
				<div id="mailOptions">
					<div>
						<?php
						// Options de base des emails (cf. Tool::sendMail()")
						echo MdlObject::sendMailBasicOptions();
						//// "Ajouter une visioconférence"
						if(Ctrl::$agora->visioEnabled())  {echo '<div id="visioUrlAdd" class="sLink" title="'.Txt::trad("VISIO_urlMail").'"><img src="app/img/visioSmall.png">&nbsp; '.Txt::trad("VISIO_urlAdd").' <img src="app/img/plusSmall.png"></div>';}
						//// "joindre des fichiers"
						echo $curObj->attachedFile();
						?>
					</div>
					<div>
						<?php
						//// Ancien email rappelé via "oldMailTypeId"  &  Bouton"Submit"
						if(Req::isParam("oldMailTypeId"))  {echo "<input type='hidden' name='oldMailTypeId' value=\"".Req::param("oldMailTypeId")."\">";}
						echo Txt::submitButton("<img src='app/img/postMessage.png'> ".Txt::trad("MAIL_sendButton"));
						?>
					</div>
				</div>
			</div>
		</div>
	</div>
</form>