<script>
////	INIT
ready(function(){
	////	Modif du login
	$("input[name='login']").on("focusout keyup",function(event){
		if($(this).isMail())	{$("#mailLloginNotif").slideUp();  $("input[name='mail']").val(this.value);}	//Login mail : préremplit le champ "email"
		else					{$("#mailLloginNotif").slideDown();}											//Sinon on affiche "utilisez un email comme login"
	}).trigger("focusout");
});

////	Controle spécifique du formulaire (cf. "VueObjMenuEdit.php")
function mainFormControl(){
	return new Promise((resolve)=>{
		//// Vérif de base
		if($("input[name='login']").isEmpty())													{notify("<?= Txt::trad("specifyLogin") ?>");	 resolve(false);}//Login obligatoire
		if($("[name='password']").notEmpty() && $("[name='password']").isPassword()==false)		{notify("<?= Txt::trad("passwordInvalid") ?>");	 resolve(false);}//Password invalide
		//// Vérif si le Login email est identique à l'email
		if($("input[name='login']").isMail()  &&  $("input[name='mail']").isMail()  &&  $("input[name='mail']").val()!=$("input[name='login']").val())
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
.infos	{margin-block:8px;}/*surcharge*/
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

	<!--LOGIN-->
	<div class="objField">
		<div><?= Txt::trad("mailLlogin") ?></div>
		<div><input type="text" name="login" value="<?= $curObj->login ?>"><div class="infos" id="mailLloginNotif"><?= Txt::trad("mailLloginNotif") ?></div></div>
	</div>

	<!--PASSWORD-->
	<div class="objField">
		<div><?= Txt::trad("password") ?></div>
		<div><?= Txt::inputPassword("password",$curObj->isNew()) ?></div>
	</div>
	<hr>

	<!-- CHAMPS PRINCIPAUX !-->
	<?= $curObj->getFields("edit") ?>
	<hr>

	<!--ESPACE DE CONNEXION-->
	<?php if(count($curObj->spaceList())>0){ ?>
	<div class="objField">
		<div><img src="app/img/user/connection.png"><?= Txt::trad("USER_connectionSpace") ?></div>
		<div><select name="connectionSpace" id="connectionSpace"><?php foreach($curObj->spaceList() as $tmpSpace)  {echo "<option value='".$tmpSpace->_id."' ".($tmpSpace->_id==$curObj->connectionSpace?'selected':null).">".$tmpSpace->name."</option>";} ?></select></div>
	</div>
	<?php } ?>

	<!--LANGUE DE L'USER-->
	<div class="objField">
		<div><img src="app/img/country.png"><?= Txt::trad("USER_langs") ?></div>
		<div><?= MdlUser::selectTrad("user",$curObj->lang) ?></div>
	</div>
	<hr>

	<!--NOTIF MAIL DE CREATION D'USER-->
	<?php if(empty($curObj->_id) && Tool::mailEnabled()){ ?>
	<div class="objField"><div>
		<input type="checkbox" name="notifMail" id="notifMail" value="1" checked='checked'>
		<label for="notifMail"><?= Txt::trad("EDIT_notifMail2") ?> <img src="app/img/mail.png"></label>
	</div></div>
	<?php } ?>

	<!--ADMIN GENERAL-->
	<?php if($curObj->editAdminGeneralRight()){ ?>
	<div class="objField"><div>
		<input type="checkbox" name="generalAdmin" id="generalAdmin" value="1" <?= !empty($curObj->generalAdmin)?'checked':null ?>>
		<label for="generalAdmin" <?= Txt::tooltip("USER_adminGeneralTooltip") ?>><?= Txt::trad("USER_adminGeneral") ?> <img src="app/img/user/userAdminGeneral.png"></label>
	</div></div>
	<?php } ?>

	<!--AGENDA PERSO DESACTIVE-->
	<?php if(Ctrl::$curUser->isGeneralAdmin()){ ?>
	<div class="objField"><div>
		<input type="checkbox" name="calendarDisabled" id="calendarDisabled" value="1" <?= (!empty($curObj->calendarDisabled))?'checked':null ?>>
		<label for="calendarDisabled" <?= Txt::tooltip("USER_persoCalendarDisabledTooltip") ?>><?= Txt::trad("USER_persoCalendarDisabled") ?></label>
	</div></div>
	<?php } ?>

	<!--USER <=> SPACES-->
	<?php if(Ctrl::$curUser->isGeneralAdmin()){ ?>
	<fieldset>
		<legend><?= Txt::trad("USER_spaceList") ?></legend>
		<!--ENTETE-->
		<div class="spaceAffectLine">
			<div>&nbsp;</div>
			<div><img src="app/img/user/user.png"> <?= Txt::trad("SPACE_user") ?></div>
			<div><img src="app/img/user/userAdminSpace.png"> <?= Txt::trad("SPACE_admin") ?></div>
		</div>
		<!--LISTE DES ESPACES-->
		<?php
		foreach($spaceList as $tmpSpace){
			$inputAttr_1=$inputAttr_2=$tootipAllUsers=null;
			if($tmpSpace->accessRightUser($curObj)==2)										{$inputAttr_2=" checked";}															//Admin checked
			if($tmpSpace->allUsersAffected() || $tmpSpace->accessRightUser($curObj)==1)		{$inputAttr_1=" checked";}															//User checked
			if($tmpSpace->allUsersAffected())   											{$inputAttr_1.=" disabled";  $tootipAllUsers=Txt::tooltip("USER_allUsersOnSpace");}	//Tous les users affectés à l'espace
		?>
			<div class="spaceAffectLine lineHover" id="targetLine_<?= $tmpSpace->_id ?>">
				<div class="spaceAffectLabel" <?= $tootipAllUsers ?>><?= $tmpSpace->getLabel() ?></div>
				<div class="spaceAffectBox" <?= Txt::tooltip("SPACE_userTooltip") ?>> <input type="checkbox" name="spaceAffect[]" value="<?= $tmpSpace->_id ?>_1" <?= $inputAttr_1 ?> ></div>
				<div class="spaceAffectBox" <?= Txt::tooltip("SPACE_adminTooltip") ?>><input type="checkbox" name="spaceAffect[]" value="<?= $tmpSpace->_id ?>_2" <?= $inputAttr_2 ?> ></div>
			</div>
		<?php } ?>
	</fieldset>
	<?php } ?>

	<!--MENU D'EDITION & VALIDATION DU FORM-->
	<?= $curObj->editMenuSubmit() ?>
</form>