<script>
////	INIT
ready(function(){
	////	Option "Formulaire d'inscription en page de connexion" : Affiche l'option de notif par email ?
	$("input[name='userInscription']").on("change",function(){
		$("#divUserInscriptionNotify").toggle(this.checked);
	}).trigger("change");//Init l'affichage

	////	Check/Uncheck "adminAddPoll" 
	$("input[value='disablePolls']").on("change",function(){ 
		$("input[value='adminAddPoll']").prop("disabled",this.checked);
	}).trigger("change");//Init l'affichage

	////	Change l'ordre d'affichage des modules
	if(isMobile())	{$(".changeOrder").hide();}
	else			{$("#modulesList").sortable({handle:".changeOrder",placeholder:"changeOrderShadow",axis:"y"});}

	////	Sélectionne "Tous les utilisateurs"
	$("#spaceAffecAllUsers").on("change",async function(){
		////	Confirm "Tout sélectionner" / "Tout déselectionner"
		let isChecked=this.checked;
		let tradSelect=(isChecked==true)  ?  "<?= Txt::trad("selectAll") ?>"  :  "<?= Txt::trad("unselectAll") ?>";
		if(await confirmAlt(tradSelect+" ?")){
			$(".spaceAffectLine input[value$='_1']").prop("checked",isChecked).prop("disabled",isChecked);	//Si besoin Check et disabled toutes les Box
			spaceAffectStyle();																				//Style des .spaceAffectLabel
		}
		////	Non confirmé : inverse le "checked" de #spaceAffecAllUsers
		else{
			$(this).prop("checked",!isChecked);
		}
	});
});

////	Controle spécifique du formulaire (cf. "VueObjMenuEdit.php")
function mainFormControl(){
	return new Promise((resolve)=>{
		////	Password de l'espace public : OK ?
		if($("#publicSpace").prop("checked") && $("#publicSpacePassword").notEmpty() && $("#publicSpacePassword").isPassword()==false){
			notify("<?= Txt::trad("passwordInvalid") ?>");
			resolve(false);
		}
		////	Nombre de modules sélectionnés : OK ?
		else if($(".moduleInput:checked").length==0){
			notify("<?= Txt::trad("SPACE_selectModule") ?>");
			resolve(false);
		}
		////	 Formulaire OK
		else{
			resolve(true);
		}
	});
}
</script>


<style>
.vSpaceOption					{margin:20px 0px;}
.vSpaceOption>img				{max-width:18px;}
.vSpaceOptionSub				{margin-top:5px; margin-left:30px;}
.vSpaceOptionSub .infos			{margin-block:10px 20px;}
.vSpaceOption:not(:has(.vSpaceOptionBox:checked)) .vSpaceOptionSub  {display:none;}/*masque le sous-menu si l'option n'est pas électionnée*/
label[for='spaceAffecAllUsers']	{font-size:1.15rem;}

/*modules*/
#modulesList					{list-style-type:none; margin:0px; padding:0px; width:100%;}
.vModuleTab						{display:table; width:100%; margin-block:10px 20px;}
.vModuleTab>div					{display:table-cell; padding:8px 5px; vertical-align:top;}
.vModuleIcon, .changeOrder		{width:30px; max-width:30px;}/*icone du module et du "sortable()"*/
.moduleOption					{display:table; margin-top:8px;}
.moduleOption>div				{display:table-cell;}
.moduleOption>div:first-child	{width:55px; padding-left:10px;}/*checkbox de l'option*/
.vModuleTab:not(:has(.moduleInput:checked)) .moduleOption						{display:none;}/*Modules désactivés : masque les options*/
.vModuleTab:has(.moduleInput[value='calendar']:checked) #moduleCalendarDisabled	{display:none;}/*Module Calendar activé : masque "Le module agenda reste toujours accessible.."*/

/*** RESPONSIVE SMARTPHONE*/
@media screen and (max-width:499px){
	.vModuleIcon, .changeOrder  {display:none!important;}
}
</style>


<form action="index.php" method="post" id="mainForm" enctype="multipart/form-data">

	<!--NOM / DESCRIPTION-->
	<input type="text" name="name" value="<?= $curObj->name ?>" class="inputTitleName" placeholder="<?= Txt::trad("name") ?>">
	<?= $curObj->descriptionEditor() ?>

	<!--ESPACE PUBLIC (avec password?)-->
	<div class="vSpaceOption">
		<img src="app/img/user/accessGuest.png">
		<input type="checkbox" name="public" value="1" id="publicSpaceBox" class="vSpaceOptionBox" <?= (!empty($curObj->public))?'checked':null ?>>
		<label for="publicSpaceBox" <?= Txt::tooltip("SPACE_publicSpaceTooltip") ?> ><?= Txt::trad("SPACE_publicSpace") ?></label>
		<!--MOT DE PASSE ET INFOS SUR LA RGPD-->
		<div class="vSpaceOptionSub">
			<img src="app/img/dependency.png"> <?= Txt::trad("password") ?> : 
			<input type="text" name="password" value="<?= $curObj->password ?>" id="publicSpacePassword">
			<div class="infos"><?= Txt::trad("SPACE_publicSpaceRGPD") ?></div>
		</div>
	</div>

	<!--INSCRIPTION A L'ESPACE-->
	<div class="vSpaceOption">
		<img src="app/img/edit.png">
		<input type="checkbox" name="userInscription" value="1" id="userInscriptionBox" class="vSpaceOptionBox" <?= (!empty($curObj->userInscription))?'checked':null ?>>
		<label for="userInscriptionBox" <?= Txt::tooltip("userInscriptionEditTooltip") ?> ><?= Txt::trad("userInscriptionEdit") ?></label>
		<!--NOTIF MAIL A CHAQUE INSCRIPTION-->
		<div class="vSpaceOptionSub" id="divUserInscriptionNotify" <?= Txt::tooltip("userInscriptionNotifTooltip") ?> >
			<img src="app/img/dependency.png">
			<input type="checkbox" name="userInscriptionNotify" id="userInscriptionNotify" value="1" <?= (!empty($curObj->userInscriptionNotify))?'checked':null ?>>
			<label for="userInscriptionNotify"><?= Txt::trad("userInscriptionNotif") ?></label>
		</div>
	</div>

	<!--INVITATIONS PAR MAIL-->
	<div class="vSpaceOption" <?= Txt::tooltip("SPACE_usersInvitationTooltip") ?> >
		<img src="app/img/mail.png"> <input type="checkbox" name="usersInvitation" id="usersInvitationBox" value="1" <?= (!empty($curObj->usersInvitation))?'checked':null ?>>
		<label for="usersInvitationBox"><?= Txt::trad("SPACE_usersInvitation") ?></label>
	</div>

	<!--WALLPAPER-->
	<div class="vSpaceOption">
		<div><?= Txt::trad("wallpaper") ?></div>
		<div><?= CtrlMisc::menuWallpaper($curObj->wallpaper) ?></div>
	</div>

	<!--MODULES DE L'ESPACE-->
	<fieldset>
		<legend><?= Txt::trad("SPACE_spaceModules") ?></legend>
		<div id="modulesList">
			<!--BLOCK DES MODULES DE L'ESPACE-->
			<?php foreach($curObj->moduleList(true) as $modName=>$module){ ?>
				<div class="vModuleTab lineSelect">
					<div class="vModuleIcon"><img src="app/img/<?= $modName ?>/iconSmall.png"></div>
					<div>
						<!--INPUT DU MODULE-->
						<input type="checkbox" name="moduleList[]" value="<?= $modName ?>" class="moduleInput" id="moduleInput<?= $modName ?>" data-module-name="<?= $modName ?>" <?= isset($module["enabled"])?'checked':null ?> >
						<label for="moduleInput<?= $modName ?>" title="<?= $module["description"] ?>"><?= $module["label"] ?></label>
						<!--OPTIONS DU MODULE-->
						<?php
						foreach($module["optionsAvailable"] as $optionName){
							if($optionName=="createSpaceCalendar" && $curObj->isNew()==false)  {continue;}						//Option "Créer un agenda pour l'espace" : nouvel espace uniquement
							$inputTradId=strtoupper($modName)."_OPTION_".$optionName;											//Id de l'input et des Trads
							$optionTooltip=Txt::isTrad($inputTradId."Info")  ?  Txt::tooltip($inputTradId."Info")  :  null;		//Tooltip
							$checked=($optionName=="createSpaceCalendar"  ||  (isset($module["options"]) && stristr($module["options"],$optionName)))  ?  "checked"  :  null;//Check l'option
						?>
							<div class="moduleOption">
								<div><img src="app/img/dependency.png"><input type="checkbox" name="<?= $modName ?>_options[]" value="<?= $optionName ?>" id="<?= $inputTradId ?>" <?= $checked ?>></div>
								<div><label for="<?= $inputTradId ?>" <?= $optionTooltip ?> ><?= Txt::trad($inputTradId) ?></label></div>
							</div>
						<?php } ?>
						<!--MODULE CALENDAR "Le module agenda reste toujours accessible.."-->
						<?php if($modName=="calendar"){ ?><div class="infos" id="moduleCalendarDisabled"><img src="app/img/important.png"> <?= Txt::trad("CALENDAR_moduleAlwaysEnabledInfo") ?></div><?php } ?>
					</div>
					<div class="changeOrder" <?= Txt::tooltip("changeOrder") ?>><img src="app/img/changeOrder.png"></div>
				</div>
			<?php } ?>
		</div>
	</fieldset>

	<!--SPACE <=> USERS-->
	<?php if(Ctrl::$curUser->isSpaceAdmin()){ ?>
	<fieldset>
		<legend><?= Txt::trad("SPACE_userAdminAccess") ?></legend>
		<!--ENTETE-->
		<div class="spaceAffectLine">
			<div>&nbsp;</div>
			<div><img src="app/img/user/user.png"> <?= Txt::trad("SPACE_user") ?></div>
			<div><img src="app/img/user/userAdminSpace.png"> <?= Txt::trad("SPACE_admin") ?></div>
		</div>
		<!--TOUS LES USERS-->
		<div class="spaceAffectLine" <?= Txt::tooltip(Txt::trad("SPACE_allUsersTooltip").' <i>'.$curObj->getLabel().'</i>') ?>>
			<div><label for="spaceAffecAllUsers"><img src="app/img/user/accessAllUsers.png"> <?= ucfirst(Txt::trad("SPACE_allUsers")) ?></label></div>
			<div><input type="checkbox" name="allUsers" value="allUsers" id="spaceAffecAllUsers" <?= $curObj->allUsersAffected()?'checked':null ?>></div>
			<div>&nbsp;</div>
		</div>
		<!--LISTE DES USERS (cf app.js)-->
		<?php
		foreach($userList as $tmpUser){
			$inputAttr_1=$inputAttr_2=null;
			if($curObj->accessRightUser($tmpUser)==2)									{$inputAttr_2=" checked";}	//Admin checked
			if($curObj->allUsersAffected() || $curObj->accessRightUser($tmpUser)==1)	{$inputAttr_1=" checked";}	//User  checked
			if($curObj->allUsersAffected())   											{$inputAttr_1.=" disabled";}//Tous les users : disabled
		?>
			<div class="spaceAffectLine lineHover">
				<div class="spaceAffectLabel"><?= $tmpUser->getLabel() ?></div>
				<div class="spaceAffectBox" <?= Txt::tooltip("SPACE_userTooltip") ?>> <input type="checkbox" name="spaceAffect[]" value="<?= $tmpUser->_id ?>_1" <?= $inputAttr_1 ?> ></div>
				<div class="spaceAffectBox" <?= Txt::tooltip("SPACE_adminTooltip") ?>><input type="checkbox" name="spaceAffect[]" value="<?= $tmpUser->_id ?>_2" <?= $inputAttr_2 ?> ></div>
			</div>
		<?php } ?>
	</fieldset>
	<?php } ?>

	<!--MENU D'EDITION & VALIDATION DU FORM-->
	<?= $curObj->editMenuSubmit() ?>
</form>