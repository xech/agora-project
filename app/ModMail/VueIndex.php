<script>
$(function(){
	////	Init l'affichage de l'arborescence de contacts
	$(".vMailsBlock").each(function(){
		var folderTreeLevel=$(this).attr("data-treeLevel");
		if(typeof folderTreeLevel!=="undefined" && folderTreeLevel>0)
			{$(this).css("padding-left",(folderTreeLevel*22)+"px");}
	});

	////	Affiche/masque les users d'un espace (sauf espace courant)
	$(".vMailsLabel").click(function(){
		$("#mailsContainer"+$(this).attr("data-targetObjId")).slideToggle();
	});

	////	Fixe la hauteur de l'éditeur et Préselectionne le titre du mail
	$("[name='title']").focus();
	
	////	Déplace si besoin le menu des destinataires à coté du menu des "options" (ne pas utiliser "isMobile()" car ne doit pas etre activé sur tablettes en mode paysage)
	if(document.body.clientWidth<=1023){
		$("#recipiententMainMenu>*").appendTo("#recipiententRespMenu");
		$("#recipiententRespMenu").show();
	}
});

////    On contrôle le formulaire
function formControl()
{
	//Sélection d'une personne, d'un titre et d'un message
	if($("[name='personList[]']:checked").length==0 && $("[name='groupList[]']:checked").length==0)	{notify("<?= Txt::trad("MAIL_specifyMail") ?>");	return false;}
	else if($("[name='title']").isEmpty())															{notify("<?= Txt::trad("requiredFields")." : ".Txt::trad("title") ?>");	 return false;}
	else if(isEmptyEditor("description"))															{notify("<?= Txt::trad("requiredFields")." : ".Txt::trad("description") ?>");	 return false;}
}
</script>

<style>
/*Menu de gauche*/
.pageModMenuContainer			{width:300px;}/*surcharge*/
#recipiententLabel				{text-align:center;}
#recipiententLabel hr			{margin:2px;}
#recipiententLabel img			{height:18px;}
.vMailsBlock					{margin:10px 0px 0px 4px;}
.vMailsBlock img				{max-width:22px;}
.vMailsMenu						{padding-left:18px!important; display:none;}
.vMailsMenu.vMailsMenuDisplay	{display:block;}
.vMailsMenu>div					{padding:2px;}
.vMailsMenu img					{max-width:18px;}
/*formulaire principal*/
.vMailMain						{padding:10px;}
.vMailMain [name='title']		{width:100%; margin-bottom:20px;}
.vMailOptions					{display:table; width:100%; margin-top:20px;}
.vMailOptions>div				{display:table-cell;}
.vMailOptions>div:last-child	{text-align:right;}
[id^='files']:not([id='files1']){display:none;}
#recipiententRespMenu			{display:none; margin-top:30px;}
.formMainButton					{margin-top:10px;}/*surcharge*/

/*RESPONSIVE*/
@media screen and (max-width:1023px){
	.vMailOptions, .vMailOptions>div	{display:inline-block; margin-bottom:10px;}
}
</style>

<form action="index.php" method="post" onsubmit="return formControl()" enctype="multipart/form-data">
<div class="pageCenter">
	<div class="pageModMenuContainer">
		<div id="pageModMenu" class="miscContainer">
			<div id="recipiententMainMenu">
				<div id="recipiententLabel"><img src="app/img/mail.png"> <?= Txt::trad("MAIL_recipients") ?> <hr></div>
				<?php
				////	LISTE DES DESTINATAIRES : USERS DE L'AGORA / DU MODULE CONTACT
				foreach($containerList as $tmpContainer)
				{
					//Init
					$cptPerson=0;
					$cptPersonLimits=1000;//limite l'affichage à 1000 personnes
					$tmpSwitchOption=$tmpGroupsFields=$tmpPersonsFields=null;
					$mailsMenuClass=($tmpContainer->_targetObjId==Ctrl::$curSpace->_targetObjId)  ?  "vMailsMenuDisplay"  :  null;//par défaut, on n'affiche que les users de l'espace courant
					//SWITCH LA SELECTION (3 à 1000 pers.)
					if(count($tmpContainer->personList)>=3 && count($tmpContainer->personList)<=$cptPersonLimits)
						{$tmpSwitchOption="<div class='sLink' onclick=\"$('#mailsContainer".$tmpContainer->_targetObjId." input[name^=personList]').trigger('click');\"><img src='app/img/switch.png'> ".Txt::trad("invertSelection")."</div>";}
					//GROUPES D'USERS (ESPACE)
					if($tmpContainer::objectType=="space"){
						foreach(MdlUserGroup::getGroups($tmpContainer) as $tmpGroup){
							$tmpBoxId=$tmpContainer->_targetObjId.$tmpGroup->_targetObjId;
							$tmpGroupsFields.="<div title=\"".$tmpGroup->usersLabel."\"><input type='checkbox' name=\"groupList[]\" value=\"".$tmpGroup->_targetObjId."\" id=\"".$tmpBoxId."\"> <label for=\"".$tmpBoxId."\"><img src='app/img/user/userGroup.png'> ".$tmpGroup->title."</label></div>";
						}
					}
					//PERSONNES DU CONTENEUR
					foreach($tmpContainer->personList as $tmpPerson)
					{
						if(empty($tmpPerson->mail) || ($tmpContainer::objectType=="contactFolder" && $cptPerson>$cptPersonLimits))	{continue;}//zap les personnes sans mails et Limite le nb de contacts
						if(Req::getParam("checkedMailto")==$tmpPerson->mail && empty($personsChecked[$tmpPerson->mail])){//Préselectionne le mail
							$tmpPerson->mailChecked="checked";
							$personsChecked[$tmpPerson->mail]=$tmpPerson->mail;//indique qu'il est déjà sélectionné
							$mailsMenuClass="vMailsMenuDisplay";//Affiche le menu (si besoin)
						}
						$tmpBoxId=$tmpContainer->_targetObjId.$tmpPerson->_targetObjId;
						$tmpPersonsFields.="<div title=\"".$tmpPerson->mail."\"><input type='checkbox' name=\"personList[]\" value=\"".$tmpPerson->_targetObjId."\" id=\"".$tmpBoxId."\" ".$tmpPerson->mailChecked."> <label for=\"".$tmpBoxId."\">".$tmpPerson->getLabel()."</label></div>";
						$cptPerson++;
					}
					////	AFFICHE CHAQUE BLOCK D'USERS / CONTACTS
					echo "<div class='vMailsBlock' ".($tmpContainer::objectType=="contactFolder"?"data-treeLevel='".$tmpContainer->treeLevel."'":null).">
							<div class='vMailsLabel sLink' data-targetObjId=\"".$tmpContainer->_targetObjId."\"><img src=\"app/img/".($tmpContainer::objectType=="space"?"user":"contact")."/iconSmall.png\"> ".$tmpContainer->name." <img src='app/img/arrowBottom.png'></div>
							<div class='vMailsMenu ".$mailsMenuClass."' id=\"mailsContainer".$tmpContainer->_targetObjId."\">".$tmpGroupsFields.$tmpPersonsFields.$tmpSwitchOption."</div>
						</div>";
				}
				?>
				<hr>
			</div>
			<div class="menuLine sLink" onclick="lightboxOpen('?ctrl=mail&action=mailHistory');">
				<div class="menuIcon"><img src="app/img/log.png"></div>
				<div><?= Txt::trad("MAIL_mailHistory") ?></div>
			</div>
		</div>
	</div>

	<div class="pageCenterContent">
		<div class="vMailMain miscContainer">
			<!--Formulaire principal-->
			<input type="text" name="title" placeholder="<?= txt::trad("MAIL_title") ?>">
			<textarea name="description"></textarea>
			<?= CtrlMisc::initHtmlEditor("description") ?>
			<!--Menu des destinataires en mode responsive-->
			<div id="recipiententRespMenu" class="miscContainer"></div>
			<!--Options-->
			<div class="vMailOptions">
				<div>
					<?php if(!empty(Ctrl::$curUser->mail)){ ?><div title="<?= Txt::trad("MAIL_receptionNotifInfo") ?>"><input type="checkbox" name="receptionNotif" value="1" id="receptionNotif"><label for="receptionNotif"><?= Txt::trad("MAIL_receptionNotif") ?></label></div><?php } ?>
					<div title="<?= Txt::trad("MAIL_hideRecipientsInfo") ?>"><input type="checkbox" name="hideRecipients" value="1" id="hideRecipients" <?= $checkhideRecipients ?>><label for="hideRecipients"><?= Txt::trad("MAIL_hideRecipients") ?></label></div>
					<div title="<?= Txt::trad("MAIL_noFooterInfo") ?>"><input type="checkbox" name="noFooter" value="1" id="noFooter"><label for="noFooter"><?= Txt::trad("MAIL_noFooter") ?></label></div>
				</div>
				<div title="<?= File::uploadMaxFilesize("info") ?>">
					<?php for($i=1; $i<=10; $i++){ ?><div id="files<?= $i ?>"><?= Txt::trad("MAIL_attachedFile") ?>  <input type="file" name="files<?= $i ?>" onChange="$('#files<?= $i+1 ?>').fadeIn();"></div><?php } ?>
				</div>
			</div>
			<!--"Submit"-->
			<?= Txt::submit("send",true) ?>
		</div>
	</div>
</div>
</form>