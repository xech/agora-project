<script>
////	INIT
$(function(){
	// apparition en "fade" du formulaire
	$(".miscContainer").fadeIn(500);
	// On met le focus sur l'input du login (ou le password)
    $("input[name='<?= empty($defaultLogin) ? "connectLogin" : "connectPassword" ?>']").focus();
	//Fait clignoter le "labelResetPassword" si une mauvaise authentification vient d'être faite
	<?php if(Req::isParam("notify") && in_array("NOTIF_identification",Req::param("notify"))){ ?>
		$("#labelResetPassword").addClass("sLinkSelect").pulsate(10);
	<?php } ?>
});

////	Accès guest à un espace  (accès direct ou avec password)
function publicSpaceAccess(_idSpace, isPassword)
{
	//Sélection d'un espace dans la liste ?
	var spaceSelected=(typeof _idSpace!="undefined" && typeof isPassword!="undefined");
	// Accès direct sans password  ||  Affiche le formulaire de saisie du password  ||  Controle ajax du password
	if(spaceSelected==true && isPassword===false)		{redir("?_idSpaceAccess="+_idSpace);}
	else if(spaceSelected==true && isPassword===true)	{$("#publicSpace_idSpace").val(_idSpace);  $("#publicSpaceFormLabel").trigger("click");}
	else if(spaceSelected==false){
		var _idSpaceUrl=parseInt($('#publicSpace_idSpace').val());
		var passwordUrl=encodeURIComponent($('#publicSpacePassword').val());
		$.ajax("?action=publicSpaceAccess&_idSpace="+_idSpaceUrl+"&password="+passwordUrl).done(function(ajaxResult){
			if(/true/i.test(ajaxResult))	{confirmCloseForm=false;  redir("?_idSpaceAccess="+_idSpaceUrl+"&password="+passwordUrl);}
			else							{notify("<?= Txt::trad("spacePassError") ?>");}
		});
	}
}

////	Controle l'email de reset du password
function resetPasswordControlSend()
{
	if($("[name='resetPasswordMail']").isMail()==false)  {notify("<?= Txt::trad("mailInvalid") ?>");  return false;}
}

////	Controle du formulaire de reset du password
function resetPasswordControlNew()
{
	if(!isValidPassword($("[name='newPassword']").val()))						{notify("<?= Txt::trad("passwordInvalid"); ?>");		return false;}//Password invalide
	if($("[name='newPassword']").val()!=$("[name='newPasswordVerif']").val())	{notify("<?= Txt::trad("passwordConfirmError") ?>");	return false;}//Passwords différents
}

////	Contrôle d'identification / connexion!
function controlConnect()
{
	var inputLogin=$("[name=connectLogin]");
	var inputPassword=$("[name=connectPassword]");
	if(inputLogin.isEmpty() || inputLogin.val()==inputLogin.attr("placeholder") || inputPassword.isEmpty() || inputPassword.val()==inputPassword.attr("placeholder")){
		notify(labelSpecifyLoginPassword);
		return false;
	}
}
</script>


<style>
body										{--buttons-width:290px!important;}/*Variable: largeur des boutons et Inputs*/
#headerBar>div								{padding:0px 20px;}/*surcharge*/
#pageCenter									{margin-top:120px;}/*surcharge*/
.miscContainer								{display:none; max-width:500px;/*pour le responsive*/ padding:30px 10px; margin:0px auto 0px auto; border-radius:5px; text-align:center;}/*surcharge*/
.miscContainer hr							{margin:30px 0px;}
#customLogo									{margin-bottom:40px;}
#customLogo img								{max-width:100%; max-height:250px;}
#formConnect input[type=text], #formConnect input[type=password], #formConnect button, .vMainButton	{width:var(--buttons-width); height:45px; border-radius:5px; margin-bottom:15px;}/*surcharge*/
.vConnectOptions							{display:inline-table;}
.vConnectOptions>div						{display:table-cell; padding:10px;}
.vPasswordForms, #publicSpaceFormLabel		{display:none;}
.vPasswordForms input						{height:35px; margin:5px; width:230px;}
.vPasswordForms button						{height:35px; margin:5px; width:150px;}
.g_id_signin								{margin-left:auto; margin-right:auto; width:var(--buttons-width);}/*button gIdentity & Iframe*/
#publicSpaceTab								{display:inline-table; margin-left:auto; margin-right:auto;} 
#publicSpaceTab>div							{display:table-cell; text-align:left; font-size:1.05em;} 
#publicSpaceTab ul							{margin:0px;}
#publicSpaceTab li							{list-style:circle; margin-bottom:15px;}

/*RESPONSIVE*/
@media screen and (max-width:1023px){
	#headerBar>div							{display:block; padding:4px; text-align:left!important; font-weight:normal;}/*surcharge*/
	#pageCenter								{margin-top:70px;}/*surcharge*/
	.miscContainer							{width:100%!important;}
	#publicSpaceTab, #publicSpaceTab>div	{display:block; margin-bottom:15px;} 
}
</style>


<div id="headerBar">
	<div><?= Ctrl::$agora->name ?></div>
	<div><?= Ctrl::$agora->description ?></div>
</div>


<div id="pageCenter">
	<div class="miscContainer">
		<!--LOGO CUSTOM-->
		<?php if(Ctrl::$agora->pathLogoConnect())  {echo '<div id="customLogo"><img src="'.Ctrl::$agora->pathLogoConnect().'"></div>';} ?>

		<!--FORMULAIRE PRINCIPAL DE CONNEXION-->
		<form action="index.php" method="post" id="formConnect" class="noConfirmClose" onsubmit="return controlConnect()">
			<input type="text" name="connectLogin" value="<?= $defaultLogin ?>" placeholder="<?= Txt::trad("loginPlaceholder") ?>" title="<?= Txt::trad("loginPlaceholder") ?>">
			<input type="password" name="connectPassword" value="<?= Req::param("newPassword") ?>" placeholder="<?= Txt::trad("password") ?>">
			<?php if(Req::isParam(["objUrl","_idSpaceAccess"])){ ?>
				<input type="hidden" name="objUrl" value="<?= Req::param("objUrl") ?>">
				<input type="hidden" name="_idSpaceAccess" value="<?= Req::param("_idSpaceAccess") ?>">
			<?php } ?>
			<button type="submit"><?= Txt::trad("connect") ?></button>
			<div class="vConnectOptions">
				<div><input type="checkbox" name="rememberMe" value="1" id="boxRememberMe" checked><label for="boxRememberMe" title="<?= Txt::trad("connectAutoInfo") ?>"><?= Txt::trad("connectAuto") ?></label></div>
				<div><a data-fancybox="inline" data-src="#formResetPassword" id="labelResetPassword"><?= Txt::trad("resetPassword") ?></a></div>
			</div>
		</form>

		<!--RESET DU PASSWORD : FORMULAIRE D'ENVOI DU MAIL-->
		<form id="formResetPassword" class="vPasswordForms" action="index.php" method="post" onsubmit="return resetPasswordControlSend();">
			<?= Txt::trad("resetPassword2") ?><hr>
			<input type="text" name="resetPasswordMail" placeholder="<?= Txt::trad("mail") ?>">
			<input type="hidden" name="resetPasswordSendMail" value="1">
			<?= Txt::submitButton("send",false) ?>
		</form>

		<!--RESET DU PASSWORD : FORMULAIRE DE MODIF DU PASSWORD => 2 ÈME ETAPE-->
		<?php if(!empty($resetPasswordIdOk) && Req::isParam("newPassword")==false){ ?>
			<div><a data-fancybox="inline" data-src="#formResetPasswordBis" id="formResetPasswordBisLabel"><?= Txt::trad("passwordModify") ?></a></div>
			<form id="formResetPasswordBis" class="vPasswordForms" action="index.php" method="post" onsubmit="return resetPasswordControlNew();">
				<?= Txt::trad("passwordModify") ?><hr>
				<input type="password" name="newPassword" placeholder="<?= Txt::trad("password") ?>"><br>		<!--nouveau password-->
				<input type="password" name="newPasswordVerif" placeholder="<?= Txt::trad("passwordVerif") ?>">	<!--nouveau password : verif-->
				<input type="hidden" name="resetPasswordMail" value="<?= Req::param("resetPasswordMail") ?>">	<!--vérif du reset-->
				<input type="hidden" name="resetPasswordId" value="<?= Req::param("resetPasswordId") ?>">		<!--idem-->
				<input type="hidden" name="connectLogin" value="<?= Req::param("resetPasswordMail") ?>">		<!--pré-remplissage du login après reset-->
				<br><?= Txt::submitButton("validate",false) ?>
			</form>
			<script>
			//Lance le fancybox dès l'affichage de la page
			setTimeout(function(){ $("#formResetPasswordBisLabel").trigger("click"); },300);
			</script>
		<?php } ?>

		<!--VALIDATION D'INVITATION : INIT DU PASSWORD-->
		<?php if(Req::isParam("_idInvitation") && Req::isParam("newPassword")==false){ ?>
			<div><a data-fancybox="inline" data-src="#formInvitPassword" id="formInvitPasswordLabel"><?= Txt::trad("USER_invitPassword") ?></a></div>
			<form id="formInvitPassword" class="vPasswordForms" action="index.php" method="post" onsubmit="return resetPasswordControlNew();">
				<?= Txt::trad("USER_invitPassword2") ?><hr>
				<input type="password" name="newPassword" placeholder="<?= Txt::trad("password") ?>"><br><!--nouveau password-->
				<input type="password" name="newPasswordVerif" placeholder="<?= Txt::trad("passwordVerif") ?>">
				<input type="hidden" name="_idInvitation" value="<?= Req::param("_idInvitation") ?>"><!--pour récupérer l'invit-->
				<input type="hidden" name="mail" value="<?= Req::param("mail") ?>">
				<br><?= Txt::submitButton("validate",false) ?>
			</form>
			<script>
			//Lance le fancybox dès l'affichage de la page
			setTimeout(function(){ $("#formInvitPasswordLabel").trigger("click"); },300);
			</script>
		<?php } ?>

		<!--CONNEXION AVEC GOOGLE IDENTITY (https://developers.google.com/identity/gsi/web/guides/overview)-->
		<?php if(Ctrl::$agora->gIdentityEnabled()){ ?>
			<hr>
			<script src="https://accounts.google.com/gsi/client" async defer></script>	<!--Charge la librairie Google Identity-->
			<script src="app/js/jwt-decode.js"></script>								<!--Charge le décodeur JSON Web Token (JWT)-->
			<script>
			////	Callback pour traiter l'appel à Google Identity
			function gIdentityResponse(response){
				const jsonResponse=jwt_decode(response.credential);																		//Décode le JSON Web Token
				$.ajax("?action=GIdentityControl&credential="+response.credential).done(function(ajaxResult){							//Controle Ajax de la connexion de l'user
					if(/userConnected/i.test(ajaxResult))	{redir("index.php");}														//User connecté : recharge la page courante
					else									{notify(jsonResponse.email+" <?= Txt::trad("gIdentityUserUnknown") ?>");}	//Notif d'erreur
				});
				//notify("ID: "+jsonResponse.sub+"<br>Email: "+jsonResponse.email+"<br>Full Name: "+jsonResponse.name+"<br>Given Name: "+jsonResponse.given_name+"<br>Family Name: "+jsonResponse.family_name);
			}
			</script>
			<div id="g_id_onload" data-client_id="<?= Ctrl::$agora->gIdentityClientId ?>" data-callback="gIdentityResponse" data-auto_prompt="false"></div> <!--Div pour charger l'API ("data-auto_prompt" masque le popup)-->
			<div class="g_id_signin" data-type="standard" data-shape="circle" data-size="large" data-width="290"></div>										<!--Bouton gIdentity (Iframe avec "data-width" idem "--buttons-width")-->
		<?php }  ?>

		<!--CONNEXION INVITE : LISTE DES ESPACES-->
		<?php if(!empty($objPublicSpaces)){ ?>
			<hr>
			<div id="publicSpaceTab">
				<div><img src="app/img/user/accessGuest.png"> <?= Txt::trad("guestAccess") ?> :</div>
				<div><ul>
				<?php foreach($objPublicSpaces as $tmpSpace)  {echo '<li class="sLink" onclick="publicSpaceAccess('.$tmpSpace->_id.','.($tmpSpace->password?'true':'false').')" title="'.Txt::trad("guestAccessInfo").'">'.$tmpSpace->name.'</li>';} ?>
				</ul></div>
			</div>
		<?php }  ?>

		<!--CONNEXION INVITE : INPUT PASSWORD-->
		<a data-fancybox="inline" data-src="#publicSpaceForm" id="publicSpaceFormLabel"><?= Txt::trad("password") ?></a>	<!--masqué par défaut : sert uniquement à afficher le form du password-->
		<form id="publicSpaceForm" class="vPasswordForms" onsubmit="publicSpaceAccess();return false;">						<!--return false pour pas envoyer le form-->
			<input type="hidden" name="_idSpace" id="publicSpace_idSpace">
			<input type="password" name="spacePassword" id="publicSpacePassword" placeholder="<?= Txt::trad("password") ?>">
			<?= Txt::submitButton("validate",false) ?>
		</form>
	
		<!--INSCRIPTION D'USER-->
		<?php if(!empty($userInscription)){ ?>
			<hr><button class="vMainButton" onclick="lightboxOpen('?action=userInscription')" title="<?= Txt::trad("userInscriptionInfo") ?>"><img src="app/img/check.png"> <?= Txt::trad("userInscription") ?></button>
		<?php }  ?>

		<!--SWITCH D'ESPACE (APP MOBILE)-->
		<?php if(Req::isMobileApp()){ ?>
			<hr><button class="vMainButton" onclick="redir('<?= Req::connectSpaceSwitchUrl() ?>')"><img src="app/img/switch.png"> <?= Txt::trad("connectSpaceSwitch") ?></button>
		<?php }  ?>

	</div>
</div>