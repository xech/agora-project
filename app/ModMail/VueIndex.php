<script>
$(function(){
	/*******************************************************************************************
	 *	PRÉSELECTIONNE LE TITRE DU MAIL
	 *******************************************************************************************/
	$("[name='title']").focus();

	/*******************************************************************************************
	 *	INIT L'AFFICHAGE DE L'ARBORESCENCE DE CONTACTS
	 *******************************************************************************************/
	$(".vMailsBlock").each(function(){
		var folderTreeLevel=$(this).attr("data-treeLevel");
		if(typeof folderTreeLevel!=="undefined" && folderTreeLevel>0)
			{$(this).css("padding-left",(folderTreeLevel*22)+"px");}
	});

	/*******************************************************************************************
	 *  AFFICHE/MASQUE LES USERS D'UN ESPACE (SAUF ESPACE COURANT)
	 *******************************************************************************************/
	$(".vMailsLabel").click(function(){
		$("#mailsContainer"+$(this).attr("data-typeId")).slideToggle();
	});
	
	/*******************************************************************************************
	 *	RESPONSIVE : DÉPLACE LE MENU DES DESTINATAIRES À COTÉ DU MENU DES "OPTIONS"
	 *******************************************************************************************/
	if(isMobile()){
		$("#recipientMainMenu>*").appendTo("#mobileRecipients");
		$("#mobileRecipients").show();
	}

	/*******************************************************************************************
	 *	VISIO : AJOUTE UNE NOUVELLE UNE URL
	 *******************************************************************************************/
	$("#visioUrlAdd").click(function(){
		if(confirm("<?= Txt::trad("VISIO_urlMail") ?> ?")){
			var visioUrl="<?= Ctrl::$agora->visioUrl() ?>";
			tinymce.activeEditor.insertContent("<p>&nbsp;</p><b><?= Txt::trad("VISIO_launch") ?> : <a href=\""+visioUrl+"\" target='_blank'>"+visioUrl+"</a></b>");
		}
	});
});

/*******************************************************************************************
 *	CONTROLE FINAL DU FORMULAIRE (TESTER AVEC INSERTION D'IMAGE)
 *******************************************************************************************/
function formControl()
{
	//// Sélection d'une personne, d'un titre et d'un description
	if($("[name='personList[]']:checked, [name='groupList[]']:checked").length==0)	{notify("<?= Txt::trad("MAIL_specifyMail") ?>");	return false;}
	else if($("[name='title']").isEmpty())											{notify("<?= Txt::trad("requiredFields")." : ".Txt::trad("MAIL_title") ?>");	return false;}
	else if(isEmptyEditor())														{notify("<?= Txt::trad("requiredFields")." : ".Txt::trad("MAIL_description") ?>");	return false;}
	//// Fichier joint : remplace le "src" des images temporaires (cf. "VueObjHtmlEditor.php")
	attachedFileTmpSrc();
	//// Affiche l'icone "Loading"
	submitButtonLoading();
}
</script>


<style>
/*Menu de gauche*/
#pageModuleMenu						{width:300px;}/*surcharge*/
#recipientLabel						{text-align:center; margin-top:5px;}
#recipientLabel hr					{margin:5px;}/*surcharge*/
#recipientLabel img					{max-height:22x;}
.vMailsBlock						{margin:10px 10px;}
.vMailsLabel 						{display:table;}
.vMailsLabel>div 					{display:table-cell; vertical-align:middle;}
.vMailsLabel img					{max-width:24px; margin-right:10px;}
.vMailsMenu							{padding-left:18px!important; display:none;}
.vMailsMenu.vMailsMenuDisplay		{display:block;}
.vMailsMenu>div						{padding:5px;}
#recipientMainMenu					{margin-bottom:10px;}
/*formulaire principal*/
#mailContainer						{padding:10px;}
#mailContainer [name='title']		{width:100%; height:35px; margin-bottom:20px;}
#mailOptions						{display:table; width:100%; margin-top:20px;}
#mailOptions>div					{display:table-cell;}
#mailOptions>div:last-child			{text-align:right;}
#mailOptions>div>div				{margin:7px;}
.submitButtonMain button			{width:220px; height:50px;}
/*RESPONSIVE*/
@media screen and (max-width:1023px){
	#mobileRecipients, #mailOptions	{margin-top:30px; border:1px solid #ccc; border-radius:3px;}
	#mailOptions, #mailOptions>div	{display:inline-block;}
}
</style>


<form action="index.php" method="post" onsubmit="return formControl()" enctype="multipart/form-data">
<div id="pageCenter">
	<div id="pageModuleMenu">
		<div id="pageModMenu">
			<div id="recipientMainMenu" class="miscContainer">
				<div id="recipientLabel"><img src="app/img/mail.png"> <?= Txt::trad("MAIL_recipients") ?> :<hr></div>
				<?php
				////	LISTE DES DESTINATAIRES : USERS DE L'AGORA / DU MODULE CONTACT
				foreach($containerList as $tmpContainer)
				{
					//Init
					$cptPerson=0;
					$cptPersonLimits=1000;//limite l'affichage à 1000 personnes
					$tmpSwitchOption=$tmpGroupsFields=$tmpPersonsFields=null;
					$mailsMenuClass=($tmpContainer->_typeId==Ctrl::$curSpace->_typeId)  ?  "vMailsMenuDisplay"  :  null;//par défaut, on n'affiche que les users de l'espace courant
					//SWITCH LA SELECTION (3 à 1000 pers.)
					if(count($tmpContainer->personList)>=3 && count($tmpContainer->personList)<=$cptPersonLimits)
						{$tmpSwitchOption="<div class='sLink' onclick=\"$('#mailsContainer".$tmpContainer->_typeId." input[name^=personList]').trigger('click');\"><img src='app/img/switch.png'> ".Txt::trad("invertSelection")."</div>";}
					//GROUPES D'USERS (ESPACE)
					if($tmpContainer::objectType=="space"){
						foreach(MdlUserGroup::getGroups($tmpContainer) as $tmpGroup){
							$tmpBoxId=$tmpContainer->_typeId.$tmpGroup->_typeId;
							$tmpGroupsFields.="<div title=\"".Txt::tooltip($tmpGroup->usersLabel)."\"><input type='checkbox' name=\"groupList[]\" value=\"".$tmpGroup->_typeId."\" id=\"".$tmpBoxId."\"> <label for=\"".$tmpBoxId."\"><img src='app/img/user/userGroup.png'> ".$tmpGroup->title."</label></div>";
						}
					}
					//PERSONNES DU CONTENEUR
					foreach($tmpContainer->personList as $tmpPerson)
					{
						if(empty($tmpPerson->mail) || ($tmpContainer::objectType=="contactFolder" && $cptPerson>$cptPersonLimits))	{continue;}//zap les personnes sans mails et Limite le nb de contacts
						if(Req::param("checkedMailto")==$tmpPerson->mail && empty($personsChecked[$tmpPerson->mail])){//Préselectionne le mail
							$tmpPerson->mailChecked="checked";
							$personsChecked[$tmpPerson->mail]=$tmpPerson->mail;//indique qu'il est déjà sélectionné
							$mailsMenuClass="vMailsMenuDisplay";//Affiche le menu (si besoin)
						}
						$tmpBoxId=$tmpContainer->_typeId.$tmpPerson->_typeId;
						$tmpPersonsFields.="<div title=\"".Txt::tooltip($tmpPerson->mail)."\"><input type='checkbox' name=\"personList[]\" value=\"".$tmpPerson->_typeId."\" id=\"".$tmpBoxId."\" ".$tmpPerson->mailChecked."> <label for=\"".$tmpBoxId."\">".$tmpPerson->getLabel()."</label></div>";
						$cptPerson++;
					}
					////	AFFICHE CHAQUE BLOCK D'USERS / CONTACTS
					echo "<div class='vMailsBlock' ".($tmpContainer::objectType=="contactFolder"?"data-treeLevel='".$tmpContainer->treeLevel."'":null).">
							<div class='vMailsLabel sLink' data-typeId=\"".$tmpContainer->_typeId."\">
								<div><img src=\"app/img/mail/".($tmpContainer::objectType=="space"?"user":"contact").".png\"></div>
								<div>".$tmpContainer->name." <img src='app/img/arrowBottom.png'></div>
							</div>
							<div class='vMailsMenu ".$mailsMenuClass."' id=\"mailsContainer".$tmpContainer->_typeId."\">".$tmpGroupsFields.$tmpPersonsFields.$tmpSwitchOption."</div>
						</div>";
				}
				?>
			</div>
			<div class="miscContainer">
				<div class="menuLine sLink" onclick="lightboxOpen('?ctrl=mail&action=mailHistory');">
					<div class="menuIcon"><img src="app/img/log.png"></div>
					<div><?= Txt::trad("MAIL_historyTitle") ?></div>
				</div>
			</div>
		</div>
	</div>

	<div id="pageCenterContent">
		<div id="mailContainer" class="miscContainer">

			<!--TITRE ET EDITEUR DU MAIL-->
			<input type="text" name="title" value="<?= $curMail->title ?>" placeholder="<?= txt::trad("MAIL_title") ?>">
			<textarea name="description"><?= $curMail->description ?></textarea>
			<?= CtrlObject::htmlEditor("description") ?>

			<!--RESPONSIVE : LISTE DES DESTINATAIRES-->
			<div id="mobileRecipients"></div>

			<!--OPTIONS DU MAIL-->
			<div id="mailOptions">
				<div>
					<?php
					//// Options  "Masquer les destinataires"  &  "Ne pas signer le message"
					echo "<div title=\"".Txt::trad("MAIL_hideRecipientsInfo")."\"><input type='checkbox' name='mailOptions[]' value='hideRecipients' id='hideRecipients'> <label for='hideRecipients'>".Txt::trad("MAIL_hideRecipients")."</label></div>
						  <div title=\"".Txt::trad("MAIL_noFooterInfo")."\"><input type='checkbox' name='mailOptions[]' value='noFooter' id='noFooter'> <label for='noFooter'>".Txt::trad("MAIL_noFooter")."</label></div>";
					//// Options  "Ajouter 'ReplyTo'"  &  "Accusé de réception"  (user courant avec un email)
					if(!empty(Ctrl::$curUser->mail)){
						echo "<div title=\"".Txt::trad("MAIL_receptionNotifInfo")."\"><input type='checkbox' name='mailOptions[]' value='receptionNotif' id='receptionNotif'> <label for='receptionNotif'>".Txt::trad("MAIL_receptionNotif")."</label></div>
							  <div title=\"".Txt::trad("MAIL_addReplyToInfo")."\"><input type='checkbox' name='mailOptions[]' value='addReplyTo' id='addReplyTo'> <label for='addReplyTo'>".Txt::trad("MAIL_addReplyTo")."</label></div>";
					}
					//// Option  "Ajouter une visioconférence"  &  "joindre des fichiers"
					if(Ctrl::$agora->visioEnabled())  {echo "<div id='visioUrlAdd' class='sLink' title=\"".Txt::trad("VISIO_urlMail")."\"><img src='app/img/visioSmall.png'>&nbsp; ".Txt::trad("VISIO_urlAdd")."</div>";}
					echo CtrlObject::attachedFile($curMail);
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