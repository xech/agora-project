<script>
////	Resize
lightboxSetWidth(550);

////	INIT
$(function(){
	////	Controle du login
	$("input[name='login']").focusout(function(){
		if($(this).isMail())	{$("input[name='mail']").val(this.value);}											//Login "mail" : on préremplit alors le champ "mail" du dessous
		else					{notify("<?= Txt::trad("specifyLoginMail") ?>");  $(this).addClass("focusRed");}	//Sinon "notify()" : "Merci de mettre de préférence un email" (pas de "focusRed()" car champ pas obligatoire)
	});

	////	Contrôle du formulaire
	$("#mainForm").submit(function(event){
		//Le formulaire doit d'abord être controlé
		if(typeof mainFormControled=="undefined")
		{
			//Pas de validation par défaut du formulaire
			event.preventDefault();
			//Verif si le login/email est présent et sans espace
			if($("input[name='login']").isEmpty())  {notify("<?= Txt::trad("specifyLogin"); ?>","warning");  return false;}
			//Vérif si le login (type email) et l'email sont bien identiques
			if($("input[name='login']").isMail() && $("input[name='mail']").isEmpty()==false && $("input[name='login']").val()!=$("input[name='mail']").val())   {notify("<?= Txt::trad("USER_loginAndMailDifferent"); ?>","warning");  return false;}
			//Verif la présence du password : nouvel user uniquement
			<?php if($curObj->isNew()){ ?>if($("[name='password']").isEmpty())  {notify("<?= Txt::trad("specifyPassword"); ?>","warning");  return false;}<?php } ?>
			//Vérif le password et sa confirmation
			if($("[name='password']").isEmpty()==false){
				if(isValidPassword($("[name='password']").val())==false)			{notify("<?= Txt::trad("passwordInvalid") ?>","warning");		return false;}
				if($("[name='password']").val()!=$("[name='passwordVerif']").val())	{notify("<?= Txt::trad("passwordConfirmError") ?>","warning");	return false;}
			}
			// Verif si le compte utilisateur existe déjà
			$.ajax("?ctrl=user&action=loginExists&mail="+encodeURIComponent($("input[name='login']").val())+"&_idUserIgnore=<?= $curObj->_id ?>").done(function(resultText){
				if(/true/i.test(resultText))	{notify("<?= Txt::trad("USER_loginExists"); ?>","warning");  return false;}	//L'user existe déjà..
				else if(mainFormControl())		{mainFormControled=true;  $("#mainForm").submit();}							//Controle principal ok : image "Loading" & confirme le formulaire !
			});
		}
	});

	////	Init les affectations des Spaces<->Users (cf. "common.js")
	spaceAffectations();
});
</script>

<style>
.vFieldConnexion				{color:#a00; font-weight:bold!important;}
#divPasswordLabel				{margin:15px 0px 15px 0px;}
<?= $curObj->isNew()  ?  "#divPasswordLabel {display:none;}"  :  "#divPassword,#divPasswordVerif {display:none;}" ?>
.vFieldLabelSpecial				{margin:15px 0px 15px 0px;}
.vFieldLabelSpecial img			{max-height:18px;}
select[name="connectionSpace"]	{width:100%}
</style>


<form action="index.php" method="post" id="mainForm" class="lightboxContent" enctype="multipart/form-data">
	<!--TITRE RESPONSIVE-->
	<?= $curObj->editRespTitle("USER_addUser") ?>

	<!--IMAGE-->
	<div class="objField">
		<div class="fieldLabel"><?= $curObj->hasImg()  ?  "<div class='personLabelImg'>".$curObj->getImg()."</div>"  :  "<img src='app/img/person/photo.png'> ".Txt::trad("pictureProfil") ?></div>
		<div><?= $curObj->displayImgMenu() ?></div>
	</div>
	<hr>

	<!--Login / Password-->
	<div class="objField"><div class="fieldLabel vFieldConnexion"><?= Txt::trad("login") ?></div><div><input type="text" name="login" value="<?= $curObj->login ?>"></div></div>
	<div class="objField sLinkSelect" id="divPasswordLabel" onClick="$('#divPassword,#divPasswordVerif').css('display','table');$(this).hide();"><div><?= Txt::trad("passwordModify") ?> <img src="app/img/arrowBottom.png"></div></div>
	<div class="objField" id="divPassword" title="<?= $curObj->isNew()==false?Txt::trad("passwordInfo"):null ?>"><div class="fieldLabel vFieldConnexion"><abbr><?= Txt::trad("password") ?></abbr></div><div><input type="password" name="password"></div></div>
	<div class="objField" id="divPasswordVerif" title="<?= $curObj->isNew()==false?Txt::trad("passwordInfo"):null ?>"><div class="fieldLabel vFieldConnexion"><abbr><?= Txt::trad("passwordVerif") ?></abbr></div><div><input type="password" name="passwordVerif"></div></div>
	<hr>

	<!-- CHAMPS PRINCIPAUX !-->
	<?= $curObj->getFieldsValues("edit") ?>
	<hr>

	<!--ESPACE DE CONNEXION-->
	<?php if(count($curObj->getSpaces())>0){ ?>
	<div class="objField">
		<div class="fieldLabel"><img src="app/img/user/connection.png"><?= Txt::trad("USER_connectionSpace") ?></div>
		<div><select name="connectionSpace"><?php foreach($curObj->getSpaces() as $tmpSpace)  {echo "<option value='".$tmpSpace->_id."' ".($tmpSpace->_id==$curObj->connectionSpace?'selected':null).">".$tmpSpace->name."</option>";} ?></select></div>
	</div>
	<?php } ?>

	<!--LANGUE DE L'USER-->
	<div class="objField"><div class="fieldLabel"><img src="app/img/country.png"><?= Txt::trad("USER_langs") ?></div><div><?= Txt::menuTrad("user",$curObj->lang) ?></div></div>

	<!--NOTIFICATION DE CREATION  && ADMIN GENERAL  &&  AGENDA PERSO DESACTIVE-->
	<hr>
	<?php if(empty($curObj->_id) && function_exists("mail")){ ?><div class="vFieldLabelSpecial"><input type="checkbox" name="notifMail" id="notifMail" value="1" checked='checked'> <label for="notifMail"><?= Txt::trad("EDIT_notifMail2") ?> <img src="app/img/mail.png"></label></div><?php } ?>
	<?php if($curObj->editAdminGeneralRight()){ ?><div class="vFieldLabelSpecial"><input type="checkbox" name="generalAdmin" id="generalAdmin" value="1" <?= !empty($curObj->generalAdmin)?'checked':null ?>> <label for="generalAdmin" title="<?= Txt::trad("USER_adminGeneralInfo") ?>"><?= Txt::trad("USER_adminGeneral") ?> <img src="app/img/user/userAdminGeneral.png"></label></div><?php } ?>
	<?php if(Ctrl::$curUser->isAdminGeneral()){ ?><div class="vFieldLabelSpecial"><input type="checkbox" name="calendarDisabled" id="calendarDisabled" value="1" <?= (!empty($curObj->calendarDisabled))?'checked':null ?>> <label for="calendarDisabled" title="<?= Txt::trad("USER_persoCalendarDisabledInfo") ?>"><?= Txt::trad("USER_persoCalendarDisabled") ?></label></div><?php } ?>

	<!--ESPACES AFFECTES A L'UTILISATEUR-->
	<?php if(Ctrl::$curUser->isAdminGeneral()){ ?>
	<div class="lightboxBlockTitle"><?= Txt::trad("USER_spaceList") ?></div>
	<div class="lightboxBlock">
		<div class="spaceAffectLine">
			<label>&nbsp;</label>
			<div title="<?= Txt::trad("SPACE_userInfo") ?>"><img src="app/img/user/user.png"> <?= Txt::trad("SPACE_user") ?></div>
			<div title="<?= Txt::trad("SPACE_adminInfo") ?>"><img src="app/img/user/userAdminSpace.png"> <?= Txt::trad("SPACE_admin") ?></div>
		</div>
		<?php
		foreach($spaceList as $tmpSpace)
		{
			$userChecked =($tmpSpace->userAffectation($curObj)==1) ? "checked" : null;	//Sélectionne la box "user"
			$adminChecked=($tmpSpace->userAffectation($curObj)==2) ? "checked" : null;	//Sélectionne la box "admin"
			$userDisabled=($tmpSpace->allUsersAffected()) ? "disabled" : null;			//Désactive la checkbox "user" si "allUsers" est sélectionné
			$userTooltip=($tmpSpace->allUsersAffected())  ?  Txt::trad("USER_allUsersOnSpaceNotif")  :  Txt::trad("SPACE_userInfo");
			echo '<div class="spaceAffectLine lineHover" id="targetLine'.$tmpSpace->_id.'">
					<label class="spaceAffectLabel" title="'.Txt::tooltip($userTooltip).'">'.$tmpSpace->name.'</label>
					<div> <input type="checkbox" name="spaceAffect[]" class="spaceAffectInput" value="'.$tmpSpace->_id.'_1" '.$userChecked.' '.$userDisabled.'></div>
					<div title="'.Txt::trad("SPACE_adminInfo").'"><input type="checkbox" name="spaceAffect[]" class="spaceAffectInput" value="'.$tmpSpace->_id.'_2" '.$adminChecked.'></div>
				  </div>';
		}
		?>
	</div>
	<?php } ?>

	<!--MENU COMMUN-->
	<?= $curObj->menuEdit() ?>
</form>