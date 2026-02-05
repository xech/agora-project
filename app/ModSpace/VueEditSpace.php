<script>
////	INIT
ready(function(){
	////	Option "espace public"
	$("#publicSpace, #publicSpacePassword").on("change",function(){
		$("#publicSpacePasswordDiv").toggle($("#publicSpace").prop("checked"));														//#publicSpace checked/unchecked : affiche/masque l'input du password 
		if($("#publicSpace").prop("checked") && $("#publicSpacePassword").isEmpty())  {$("#publicSpaceNotif").show().pulsate(2);}	//Affiche et Pulsate la notif sur la RGPD
	}).trigger("change");//Init l'affichage

	////	Option "Formulaire d'inscription en page de connexion"
	$("input[name='userInscription']").on("change",function(){
		$("#divUserInscriptionNotify").toggle(this.checked);//Affiche l'option de notif par email ?
	}).trigger("change");//Init l'affichage

	////	Sélectionne/Désélectionne un module
	$("input[name='moduleList[]']").on("change",function(){
		//Affiche/masque les options du module en cours
		$("[name='moduleList[]']").each(function(){	 $(".moduleOptions"+$(this).attr("data-moduleName")).toggle(this.checked);  });
		//Si le module "agenda" est désactivé : on affiche "Le module agenda reste toujours accessible.."
		if(this.id=="moduleInput-calendar")  {$("#moduleCalendarDisabled").toggle(!this.checked);}
	}).trigger("change");//Init l'affichage

	////	Option "disablePolls" : active/désactive l'option "adminAddPoll"
	$("input[value='disablePolls']").on("change",function(){ 
		$("input[value='adminAddPoll']").prop("disabled",this.checked);
	}).trigger("change");//Init l'affichage

	////	Change l'ordre d'affichage des modules ("hightlight" : module fantome & "y" : déplacemnt vertical)
	if(isMobile())	{$(".changeOrder").hide();}
	else			{$("#modulesList").sortable({handle:".changeOrder",placeholder:"changeOrderShadow",axis:"y"});}
});

////	Controle spécifique du formulaire (cf. "VueObjMenuEdit.php")
function mainFormControl(){
	return new Promise((resolve)=>{
		//// Controle le password de l'espace public + le nombre de modules sélectionnés
		if($("#publicSpace").prop("checked") && $("#publicSpacePassword").notEmpty() && $("#publicSpacePassword").isPassword()==false)	{notify("<?= Txt::trad("passwordInvalid") ?>");		resolve(false); }
		if($("input[name='moduleList[]']:checked").isEmpty())																			{notify("<?= Txt::trad("SPACE_selectModule") ?>");	resolve(false); }
		else{
			$("#spaceAffecAllUsers input").prop("disabled",false);
			resolve(true);
		}
	});
}
</script>


<style>
.vSpaceOptions							{margin:20px 0px;}
.vSpaceOptions>img						{max-width:18px;}
#publicSpacePasswordDiv, #divUserInscriptionNotify	{margin:5px 0px 0px 30px;}
#publicSpaceNotif						{display:none; margin:10px 0px; line-height:20px;}
label[for='allUsers']					{font-size:1.1rem;}

/*modules*/
#modulesList							{list-style-type:none; margin:0px; padding:0px; width:100%;}
.vModuleLine							{display:table; width:100%; margin:10px 0px; background:<?= Ctrl::$agora->skin=="black"?"#222":"#f1f1f1" ?>; border:<?= Ctrl::$agora->skin=="black"?"#555":"#ddd" ?> 1px solid;}
.vModuleLine>div						{display:table-cell; font-weight:bold; padding:10px;}
.vModuleLine img						{max-height:20px;}
.vModuleLine img[src*='dependency']		{margin-left:8px;}
.vModuleLineIcon						{vertical-align:middle; margin-left:5px;}
div[class^='moduleOptions']				{display:none; padding:3px;}/*masque par défaut les options*/

/*AFFICHAGE SMARTPHONE*/
@media screen and (max-width:490px){
	.vModuleLineIcon	{display:none!important;}
}
</style>


<form action="index.php" method="post" id="mainForm" enctype="multipart/form-data">

	<!--NOM / DESCRIPTION-->
	<input type="text" name="name" value="<?= $curObj->name ?>" class="inputTitleName" placeholder="<?= Txt::trad("name") ?>">
	<?= $curObj->descriptionEditor() ?>

	<!--ESPACE PUBLIC (avec password?)-->
	<div class="vSpaceOptions">
		<img src="app/img/user/accessGuest.png"> <input type="checkbox" name="public" id="publicSpace" value="1" <?= (!empty($curObj->public))?'checked':null ?>>
		<label for="publicSpace" <?= Txt::tooltip("SPACE_publicSpaceTooltip") ?> ><?= Txt::trad("SPACE_publicSpace") ?></label>
		<div id="publicSpacePasswordDiv">
			<img src="app/img/dependency.png"> <?= Txt::trad("password") ?> : &nbsp; <input type="text" name="password" value="<?= $curObj->password ?>" id="publicSpacePassword">
			<fieldset id="publicSpaceNotif"><?= Txt::trad("SPACE_publicSpaceNotif") ?></fieldset><!--Notif sur la RGPD-->
		</div>
	</div>

	<!--INSCRIPTION A L'ESPACE-->
	<div class="vSpaceOptions">
		<img src="app/img/edit.png"> <input type="checkbox" name="userInscription" id="userInscription" value="1" <?= (!empty($curObj->userInscription))?'checked':null ?>>
		<label for="userInscription" <?= Txt::tooltip("userInscriptionEditTooltip") ?> ><?= Txt::trad("userInscriptionEdit") ?></label>
		<div id="divUserInscriptionNotify" <?= Txt::tooltip("userInscriptionNotifTooltip") ?> >
			<img src="app/img/dependency.png">
			<input type="checkbox" name="userInscriptionNotify" id="userInscriptionNotify" value="1" <?= (!empty($curObj->userInscriptionNotify))?'checked':null ?>>
			<label for="userInscriptionNotify"><?= Txt::trad("userInscriptionNotif") ?></label>
		</div>
	</div>

	<!--INVITATIONS PAR MAIL-->
	<div class="vSpaceOptions" <?= Txt::tooltip("SPACE_usersInvitationTooltip") ?> >
		<img src="app/img/mail.png"> <input type="checkbox" name="usersInvitation" id="usersInvitation" value="1" <?= (!empty($curObj->usersInvitation))?'checked':null ?>>
		<label for="usersInvitation"><?= Txt::trad("SPACE_usersInvitation") ?></label>
	</div>

	<!--WALLPAPER-->
	<div class="vSpaceOptions">
		<div><?= Txt::trad("wallpaper") ?></div>
		<div><?= CtrlMisc::menuWallpaper($curObj->wallpaper) ?></div>
	</div>

	<!--MODULES DE L'ESPACE-->
	<fieldset>
		<legend><?= Txt::trad("SPACE_spaceModules") ?></legend>
		<div id="modulesList">
		<?php
		////	AFFICHE CHAQUE MODULE ET SES OPTIONS
		foreach($moduleList as $moduleName=>$tmpModule)
		{
			//Prépare chaque option du module
			$moduleOptions=null;
			foreach($tmpModule["ctrl"]::$moduleOptions as $optionName)
			{
				//Création d'agenda : uniquement pour un nouvel espace
				if($optionName=="createSpaceCalendar" && $curObj->isNew()==false)  {continue;}
				//Init l'affichage
				$checkOption=(!empty($tmpModule["options"]) && stristr($tmpModule["options"],$optionName))  ?  "checked"  :  null;
				if($optionName=="createSpaceCalendar")  {$checkOption="checked";}//"check" la création d'un agenda s'il s'agit d'un nouvel espace
				$inputId=$moduleName."Option".$optionName;
				$labelTradId=strtoupper($moduleName)."_OPTION_".$optionName;
				$labelTitle=Txt::isTrad($labelTradId."Info")  ?  Txt::trad($labelTradId."Info")  :  null;
				//Affiche l'option
				$moduleOptions.='<div class="moduleOptions'.$moduleName.'">
									<img src="app/img/dependency.png"><input type="checkbox" name="'.$moduleName.'Options[]" value="'.$optionName.'" id="'.$inputId.'" '.$checkOption.'>
									<label for="'.$inputId.'" title="'.$labelTitle.'">'.Txt::trad($labelTradId).'</label>
								</div>';
			}
			//Module Agenda : si le module est désactivé, on affiche "Le module agenda reste toujours accessible.."
			if($moduleName=="calendar")  {$moduleOptions.='<div class="infos" id="moduleCalendarDisabled"><img src="app/img/info.png"> '.Txt::trad("CALENDAR_moduleAlwaysEnabledInfo").'</div>';}
			//Affiche le module et ses options
			echo '<div class="vModuleLine">
					<div>
						<input type="checkbox" name="moduleList[]" value="'.$moduleName.'" id="moduleInput-'.$moduleName.'" data-moduleName="'.$moduleName.'" '.(empty($tmpModule["disabled"])?"checked":null).'>
						<label for="moduleInput-'.$moduleName.'" title="'.$tmpModule["description"].'">'.$tmpModule["label"].' <img src="app/img/'.$moduleName.'/icon.png" class="vModuleLineIcon"></label>
						'.$moduleOptions.'
					</div>
					<div class="changeOrder" '.Txt::tooltip("changeOrder").'><img src="app/img/changeOrder.png"></div>
				  </div>';
		}
		?>
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
		<div class="spaceAffectLine" id="spaceAffecAllUsers" data-selectAll="<?= Txt::trad("selectAll") ?>" data-selectNone="<?= Txt::trad("selectNone") ?>" <?= Txt::tooltip(Txt::trad("SPACE_allUsersTooltip").' <i>'.$curObj->getLabel().'</i>') ?>>
			<div><img src="app/img/user/accessAllUsers.png"> <?= ucfirst(Txt::trad("SPACE_allUsers")) ?></div>
			<div><input type="checkbox" name="allUsers" value="allUsers" disabled <?= $curObj->allUsersAffected()?'checked':null ?>></div>
			<div>&nbsp;</div>
		</div>
		<!--LISTE DES USERS-->
		<?php
		foreach($userList as $tmpUser){
			$inputAttr_1=$inputAttr_2=null;
			if($curObj->accessRightUser($tmpUser)==2)									{$inputAttr_2=" checked";}	//Admin checked
			if($curObj->allUsersAffected() || $curObj->accessRightUser($tmpUser)==1)	{$inputAttr_1=" checked";}	//User  checked
			if($curObj->allUsersAffected())   											{$inputAttr_1.=" disabled";}//Tous les users : disabled
		?>
			<div class="spaceAffectLine lineHover" id="targetLine_<?= $tmpUser->_id ?>">
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