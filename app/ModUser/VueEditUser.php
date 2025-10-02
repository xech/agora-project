<script>
////	INIT
ready(function(){
	////	Modif du login
	$("input[name='login']").on("focusout keyup",function(event){
		if($(this).isMail()) 				{$("input[name='mail']").val(this.value);}	//Préremplit le champ "mail"
		else if(event.type=="focusout")		{$("#mailLloginNotif").pulsate(1);}			//Pulsate "Il est conseillé d'utiliser un email.."
	});
	////	Init les affectations des Spaces<->Users
	spaceAffectations();
});

////	Controle spécifique du formulaire (cf. "VueObjMenuEdit.php")
function objectFormControl(){
	return new Promise((resolve)=>{
		//// Vérif de base
		if($("input[name='login']").isEmpty())																		{notify("<?= Txt::trad("specifyLogin") ?>");		resolve(false);}//Login obligatoire
		if($("[name='password']").isEmpty() && <?= $curObj->isNew()?'true':'false'?>==true)							{notify("<?= Txt::trad("specifyPassword") ?>");		resolve(false);}//Password obligatoire (new users)													
		if($("[name='password']").notEmpty() && isValidPassword($("[name='password']").val())==false)				{notify("<?= Txt::trad("passwordInvalid") ?>");		resolve(false);}//Password invalide
		if($("[name='password']").notEmpty() && $("[name='password']").val()!=$("[name='passwordVerif']").val())	{notify("<?= Txt::trad("passwordVerifError") ?>");	resolve(false);}//"passwordVerif" invalide
		//// Vérif si le Login (format mail) est identique à l'email
		if($("input[name='login']").isMail() && $("input[name='mail']").isMail() && $("input[name='mail']").val()!=$("input[name='login']").val())
			{notify("<?= Txt::trad("USER_loginAndMailDifferent") ?>");  resolve(false);}
		//// Verif Ajax finale : un compte existe déjà avec le même login ?
		$.ajax("?ctrl=user&action=loginExists&mail="+encodeURIComponent($("input[name='login']").val())+"&_idUserIgnore=<?= $curObj->_id ?>").done(function(result){
			if(/true/i.test(result))	{notify("<?= Txt::trad("USER_loginExists") ?>");	resolve(false);}
			else						{resolve(true);}
		});
	});
}
</script>


<style>
#mailLloginNotif, #passwordModifNotif	{display:none; margin:15px 0px 0px 0px; padding:15px; color:#500; text-align:center;}
#passwordModifLabel						{margin-bottom:15px;}
select[name="connectionSpace"]			{width:100%}
<?=$curObj->isNew() ? "#passwordModifLabel" : "#passwordInput,#passwordInput2" ?>  {display:none;}
</style>


<form action="index.php" method="post" id="mainForm" enctype="multipart/form-data">
	<!--TITRE MOBILE-->
	<?= $curObj->titleMobile("USER_addUser") ?>

	<!--IMAGE-->
	<div class="objField">
		<div><?= $curObj->profileImgExist()  ?  "<div class='personProfileImg'>".$curObj->profileImg()."</div>"  :  "<img src='app/img/person/photo.png'> ".Txt::trad("pictureProfil") ?></div>
		<div><?= $curObj->profileImgMenu() ?></div>
	</div>
	<hr>

	<!--Login / Password-->
	<fieldset id="mailLloginNotif"><?= Txt::trad("mailLloginNotif") ?></fieldset>
	<div class="objField"><div class="vFieldConnexion"><?= Txt::trad("mailLlogin") ?></div><div><input type="text" name="login" value="<?= $curObj->login ?>"></div></div>
	<div class="objField" id="passwordModifLabel"><div><a onclick="$('#passwordModifNotif,#passwordInput,#passwordInput2').show();$(this).hide();"><?= Txt::trad("passwordModify") ?> <img src="app/img/arrowBottom.png"></a></div></div>
	<fieldset id="passwordModifNotif"><?= Txt::trad("passwordTooltip") ?></fieldset>
	<div class="objField" id="passwordInput"><div class="vFieldConnexion"><?= Txt::trad("password") ?></div><div><input type="password" name="password"></div></div>
	<div class="objField" id="passwordInput2"><div class="vFieldConnexion"><?= Txt::trad("passwordVerif") ?></div><div><input type="password" name="passwordVerif"></div></div>
	<hr>

	<!-- CHAMPS PRINCIPAUX !-->
	<?= $curObj->getFields("edit") ?>
	<hr>

	<!--ESPACE DE CONNEXION-->
	<?php if(count($curObj->spaceList())>0){ ?>
	<div class="objField">
		<div><img src="app/img/user/connection.png"><?= Txt::trad("USER_connectionSpace") ?></div>
		<div><select name="connectionSpace"><?php foreach($curObj->spaceList() as $tmpSpace)  {echo "<option value='".$tmpSpace->_id."' ".($tmpSpace->_id==$curObj->connectionSpace?'selected':null).">".$tmpSpace->name."</option>";} ?></select></div>
	</div>
	<?php } ?>

	<!--LANGUE DE L'USER-->
	<div class="objField"><div><img src="app/img/country.png"><?= Txt::trad("USER_langs") ?></div><div><?= MdlUser::selectTrad("user",$curObj->lang) ?></div></div>

	<!--NOTIFICATION DE CREATION  && ADMIN GENERAL  &&  AGENDA PERSO DESACTIVE-->
	<hr>
	<?php if(empty($curObj->_id) && Tool::mailEnabled()){ ?><div class="objField"><input type="checkbox" name="notifMail" id="notifMail" value="1" checked='checked'> <label for="notifMail"><?= Txt::trad("EDIT_notifMail2") ?> <img src="app/img/mail.png"></label></div><?php } ?>
	<?php if($curObj->editAdminGeneralRight()){ ?><div class="objField"><input type="checkbox" name="generalAdmin" id="generalAdmin" value="1" <?= !empty($curObj->generalAdmin)?'checked':null ?>> <label for="generalAdmin" <?= Txt::tooltip("USER_adminGeneralTooltip") ?>><?= Txt::trad("USER_adminGeneral") ?> <img src="app/img/user/userAdminGeneral.png"></label></div><?php } ?>
	<?php if(Ctrl::$curUser->isGeneralAdmin()){ ?><div class="objField"><input type="checkbox" name="calendarDisabled" id="calendarDisabled" value="1" <?= (!empty($curObj->calendarDisabled))?'checked':null ?>> <label for="calendarDisabled" <?= Txt::tooltip("USER_persoCalendarDisabledTooltip") ?>><?= Txt::trad("USER_persoCalendarDisabled") ?></label></div><?php } ?>

	<!--ESPACES AFFECTES A L'UTILISATEUR-->
	<?php if(Ctrl::$curUser->isGeneralAdmin()){ ?>
	<fieldset>
		<legend><?= Txt::trad("USER_spaceList") ?></legend>
		<div class="spaceAffectLine">
			<label>&nbsp;</label>
			<div <?= Txt::tooltip("SPACE_userTooltip") ?>><img src="app/img/user/user.png"> <?= Txt::trad("SPACE_user") ?></div>
			<div <?= Txt::tooltip("SPACE_adminTooltip") ?>><img src="app/img/user/userAdminSpace.png"> <?= Txt::trad("SPACE_admin") ?></div>
		</div>
		<?php
		foreach($spaceList as $tmpSpace)
		{
			$userChecked =($tmpSpace->userAffectation($curObj)==1) ? "checked" : null;	//Sélectionne la box "user"
			$adminChecked=($tmpSpace->userAffectation($curObj)==2) ? "checked" : null;	//Sélectionne la box "admin"
			$userDisabled=($tmpSpace->allUsersAffected())  ?  "disabled" : null;		//Désactive la checkbox "user" si "allUsers" est sélectionné
			$userTooltip =($tmpSpace->allUsersAffected())  ?  Txt::trad("USER_allUsersOnSpace")  :  Txt::trad("SPACE_userTooltip");
			echo '<div class="spaceAffectLine lineHover" id="targetLine'.$tmpSpace->_id.'">
					<label class="spaceAffectLabel" '.Txt::tooltip($userTooltip).'>'.$tmpSpace->name.'</label>
					<div> <input type="checkbox" name="spaceAffect[]" class="spaceAffectInput" value="'.$tmpSpace->_id.'_1" '.$userChecked.' '.$userDisabled.'></div>
					<div '.Txt::tooltip("SPACE_adminTooltip").'><input type="checkbox" name="spaceAffect[]" class="spaceAffectInput" value="'.$tmpSpace->_id.'_2" '.$adminChecked.'></div>
				  </div>';
		}
		?>
	</fieldset>
	<?php } ?>

	<!--MENU D'EDITION & VALIDATION DU FORM-->
	<?= $curObj->editMenuSubmit() ?>
</form>