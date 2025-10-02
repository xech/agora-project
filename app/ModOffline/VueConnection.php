<script>
ready(function(){
	/**********************************************************************************************************
	 *	INIT L'AFFICHAGE
	 **********************************************************************************************************/
    $("input[name='<?= empty($defaultLogin) ? "connectLogin" : "connectPassword" ?>']").focusAlt();	//Focus sur l'input du login ou password
	<?php if(Req::isParam("notify") && in_array("NOTIF_identification",Req::param("notify"))){ ?>	//Pulsate "resetPasswordLabel" si l'authentification est erronée
		$("#resetPasswordLabel").addClass("linkSelect").pulsate(20);
	<?php } ?>

	/**********************************************************************************************************
	 *	CONTRÔLE DU FORMULAIRE DE CONNEXION
	 **********************************************************************************************************/
	$("#formConnect").on("submit",function(){
		let connectLogin=$("[name=connectLogin]");
		let connectPassword=$("[name=connectPassword]");
		if(connectLogin.isEmpty() || connectLogin.val()==connectLogin.attr("placeholder") || connectPassword.isEmpty() || connectPassword.val()==connectPassword.attr("placeholder")){
			notify("<?= Txt::trad("specifyLoginPassword") ?>");
			return false;
		}
	});

	/**********************************************************************************************************
	 *	CONTROLE L'EMAIL DE RESET DU PASSWORD
	 **********************************************************************************************************/
	$("#resetPasswordFormSendmail").on("submit",function(){
		if($(this).find("[name='resetPasswordMail']").isMail()==false)   {notify("<?= Txt::trad("mailInvalid") ?>");  return false;}
	});

	/**********************************************************************************************************
	 *	FORMULAIRE DE RESET DU PASSWORD ET FORMULAIRE DE VALIDATION D'INVITATION : CONTROLE DES CHAMPS "PASSWORD"
	 **********************************************************************************************************/
	$("#resetPasswordFormUpdate, #invitationPasswordForm").on("submit",function(){
		let newPassword		=$(this).find("[name='newPassword']").val();
		let newPasswordVerif=$(this).find("[name='newPasswordVerif']").val();
		if(isValidPassword(newPassword)==false)		{notify("<?= Txt::trad("passwordInvalid"); ?>");	return false;}//Password invalide
		else if(newPassword!=newPasswordVerif)		{notify("<?= Txt::trad("passwordVerifError") ?>");	return false;}//Passwords différents
	});

	/**********************************************************************************************************
	 *	ESPACE PUBLIQUE :  AFFICHE LE FORMULAIRE DU PASSWORD  ||  ACCÈS DIRECT
	 **********************************************************************************************************/
	$(".publicSpaceOption").click(function(){
		if($(this).attr("data-hasPassword")==="true"){
			$("#publicSpaceForm [name='_idSpaceAccess']").val($(this).attr("data-idSpace"));
			$("#publicSpaceForm #publicSpaceName").html($(this).attr("data-spaceName"));
			Fancybox.show([{type:"inline",src:"#publicSpaceForm"}]);
		}else{
			redir("index.php?_idSpaceAccess="+$(this).attr("data-idSpace")+"&objUrl="+encodeURIComponent($("#formConnect [name='objUrl']").val()));
		}
	});

	/**********************************************************************************************************
	 *	ESPACE PUBLIQUE : CONTROLE DU PASSWORD VIA AJAX
	 **********************************************************************************************************/
	$("#publicSpaceForm").on("submit",function(event){
		event.preventDefault();
		let idSpaceAccess=$("#publicSpaceForm [name='_idSpaceAccess']").val();
		let objUrl=encodeURIComponent($("#formConnect [name='objUrl']").val());																			//"objUrl" du form de connexion
		let password=encodeURIComponent($("#publicSpacePassword").val());																				//Récupère le password
		$.ajax("index.php?action=PublicSpacePasswordControl&_idSpaceAccessControl="+idSpaceAccess+"&passwordControl="+password).done(function(result){	//Controle Ajax du password (pas '_idSpaceAccess=' dans l'URL!)
			if(/passwordOK/i.test(result))	{redir("index.php?_idSpaceAccess="+idSpaceAccess+"&password="+password+"&objUrl="+objUrl);}					//Redir vers l'espace demandé
			else							{notify("<?= Txt::trad("publicSpacePasswordError") ?>");}													//Notif d'erreur de password
		});
	});
});
</script>

<style>
#headerBar									{line-height:50px; text-align:center;}/*surcharge*/
#pageCenter									{margin-top:100px;}
.miscContainer								{width:500px; margin:10px auto; padding:25px 10px; border-radius:10px; text-align:center;}/*surcharge*/
.miscContainer button>img					{margin-right:10px;}
.miscContainer hr							{margin:30px 0px;}
.miscContainer input:not([type=checkbox]), .miscContainer button  {width:320px;/*cf .g_id_signin*/ min-height:45px!important;/*cf responsive*/ border-radius:5px; margin-bottom:20px!important;}

/*Logo custom*/
#customLogo									{background-color:rgba(250, 250, 250, 40%); padding:10px;}
#customLogo img								{max-width:100%; max-height:180px;}

/*Accès invité*/
#publicSpaceTab								{display:inline-table;} 
#publicSpaceTab>div							{display:table-cell; text-align:left; width:50%; line-height:25px;}
#publicSpaceTab>div:first-child				{text-align:right;}
.publicSpaceOption							{padding:0px 10px;}

/*Form de connexion*/
.vConnectOptions							{display:inline-table;}
.vConnectOptions>div						{display:table-cell; padding:0px 15px;}
.g_id_signin								{margin:40px auto!important; width:300px;}/*button gOAuth & Iframe*/

/*RESPONSIVE MEDIUM*/
@media screen and (max-width:1024px){
	#headerBar								{font-size:1rem;}/*surcharge*/
	#headerBar span							{display:none;}
	.miscContainer							{width:95%; margin-top:30px!important; border-radius:10px!important;}/*surcharge*/
	#publicSpaceTab, #publicSpaceTab>div	{display:block; width:100%; text-align:left!important;}
	.publicSpaceOption						{margin-left:40px; margin-top:10px;}
	.vConnectOptions>div					{font-size:0.9rem;}
}
</style>


<div id="headerBar">
	<?= ucfirst(Ctrl::$agora->name).(!empty(Ctrl::$agora->description) ? '<span> - '.ucfirst(Ctrl::$agora->description).'<span>' : null) ?></span>
</div>

<div id="pageCenter">

	<!--LOGO CUSTOM-->
	<?php if(Ctrl::$agora->pathLogoConnect()) { ?>
		<div id="customLogo" class="miscContainer"><img src="<?= Ctrl::$agora->pathLogoConnect() ?>"></div>
	<?php } ?>


	<!--CONNEXION A UN ESPACE PUBLIC (INVITE)-->
	<?php if(!empty($objPublicSpaces)){ ?>
	<div class="miscContainer">
		<div id="publicSpaceTab">
			<div><img src="app/img/user/guest.png"> <?= Txt::trad("guestAccess") ?> &nbsp; <img src="app/img/arrowRight.png"></div>
			<div>
				<?php foreach($objPublicSpaces as $tmpSpace){ ?>
					<div class="option publicSpaceOption" data-idSpace="<?= $tmpSpace->_id ?>" data-spaceName="<?= $tmpSpace->name ?>" data-hasPassword="<?= $tmpSpace->password?'true':'false' ?>" <?= Txt::tooltip(Txt::trad("guestAccessTooltip").'<br>'.$tmpSpace->description) ?> ><?= $tmpSpace->name ?></div>
				<?php } ?>
			</div>
		</div>
	</div>
	<form id="publicSpaceForm" class="lightboxInline">
		<div class="lightboxTitle"><?= Txt::trad("guestAccess") ?> : <span id="publicSpaceName"></span></div>
		<input type="password" id="publicSpacePassword" placeholder="<?= Txt::trad("password") ?>" required>
		<input type="hidden" name="_idSpaceAccess">
		<?= Txt::submitButton("validate",false) ?>
	</form>
	<?php }  ?>


	<!--AUTHENTIFICATION & OPTIONS-->
	<div class="miscContainer">
		<!--FORMULAIRE DE CONNEXION-->
		<form action="index.php" method="post" id="formConnect">
			<input type="text" name="connectLogin" value="<?= $defaultLogin ?>" placeholder="<?= Txt::trad("mailLlogin") ?>" <?= Txt::tooltip("mailLlogin") ?>  class="isAutocomplete">
			<input type="password" name="connectPassword" value="<?= Req::param("newPassword") ?>" placeholder="<?= Txt::trad("password") ?>" <?= Txt::tooltip("password") ?> class="isAutocomplete">
			<input type="hidden" name="objUrl" value="<?= Req::param("objUrl") ?>">					<!--accès direct à un objet via "getUrlExternal()"-->
			<input type="hidden" name="_idSpaceAccess" value="<?= Req::param("_idSpaceAccess") ?>">	<!--idem-->
			<button type="submit"><?= Txt::trad("connect") ?></button>
			<div class="vConnectOptions">
				<div><input type="checkbox" name="rememberMe" value="1" id="boxRememberMe" checked>&nbsp;<label for="boxRememberMe" <?= Txt::tooltip("connectAutoTooltip") ?> ><?= Txt::trad("connectAuto") ?></label></div>
				<div><a data-fancybox="inline" data-src="#resetPasswordFormSendmail" id="resetPasswordLabel"><?= Txt::trad("resetPassword") ?></a></div><!--Afficher le form ci-dessous-->
			</div>
		</form>

		<!--BOUTON DE CONNEXION AVEC GOOGLE OAUTH-->
		<?php if(Ctrl::$agora->gOAuthEnabled()){ ?>
			<script src="https://accounts.google.com/gsi/client" async defer></script>	<!--Charge la librairie Google Oauth-->
			<script src="app/js/jwt-decode.js"></script>								<!--Charge le décodeur JSON Web Token (JWT)-->
			<script>
			////	Callback pour traiter l'appel à Google Oauth
			function gOAuthResponse(response){
				const jsonResponse=jwt_decode(response.credential);																	//Décode le JSON Web Token
				$.ajax("index.php?action=gOAuthControl&credential="+response.credential).done(function(ajaxResult){					//Controle Ajax de la connexion de l'user
					if(/userConnected/i.test(ajaxResult))	{redir("index.php");}													//User connecté : recharge la page courante
					else									{notify(jsonResponse.email+" <?= Txt::trad("gOAuthUserUnknown") ?>");}	//Notif d'erreur
				});
				////DEBUG::notify("ID: "+jsonResponse.sub+"<br>Email: "+jsonResponse.email+"<br>Full Name: "+jsonResponse.name+"<br>Given Name: "+jsonResponse.given_name+"<br>Family Name: "+jsonResponse.family_name);
			}
			</script>
			<div id="g_id_onload" data-client_id="<?= Ctrl::$agora->gIdentityClientId ?>" data-callback="gOAuthResponse" data-auto_prompt="false"></div>	<!--Div pour charger l'API ("data-auto_prompt" masque le popup)-->
			<div class="g_id_signin" data-type="standard" data-shape="circle" data-size="large" data-width="290" <?= Txt::tooltip("AGORA_gOAuth") ?>></div>	<!--Bouton gOAuth (Iframe avec "data-width" idem "--buttons-width")-->
		<?php } ?>

		<!--FORM DE RESET DU PASSWORD -> ETAPE 1 : ENVOI DE L'EMAIL-->
		<form action="index.php" method="post" id="resetPasswordFormSendmail" class="lightboxInline">
			<div class="lightboxTitle"><?= Txt::trad("resetPassword2") ?></div>
			<input type="text" name="resetPasswordMail" placeholder="<?= Txt::trad("mail") ?>" required>
			<input type="hidden" name="resetPasswordSendMail" value="1">
			<?= Txt::submitButton("send",false) ?>
		</form>

		<!--FORM DE RESET DU PASSWORD -> ETAPE 2 : MODIF DU PASSWORD-->
		<?php if(!empty($resetPasswordIdOk) && Req::isParam("newPassword")==false){ ?>
			<div data-fancybox="inline" data-src="#resetPasswordFormUpdate"><?= Txt::trad("passwordModify") ?></div>
			<form action="index.php" method="post" id="resetPasswordFormUpdate" class="lightboxInline">
				<div class="lightboxTitle"><?= Txt::trad("passwordModify") ?></div>
				<input type="password" name="newPassword" placeholder="<?= Txt::trad("password") ?>" required>
				<input type="password" name="newPasswordVerif" placeholder="<?= Txt::trad("passwordVerif") ?>" required>	<!--nouveau password-->
				<input type="hidden" name="resetPasswordMail" value="<?= Req::param("resetPasswordMail") ?>">				<!--nouveau password : verif-->
				<input type="hidden" name="resetPasswordId" value="<?= Req::param("resetPasswordId") ?>">					<!--controle resetPasswordId-->
				<input type="hidden" name="connectLogin" value="<?= Req::param("resetPasswordMail") ?>">					<!--pré-remplissage du login après validation-->
				<br><?= Txt::submitButton("validate",false) ?>
			</form>
			<script> ready(function(){ Fancybox.show([{type:"inline",src:"#resetPasswordFormUpdate"}]); }); </script>		<!--Affichage initial-->
		<?php } ?>

		<!--FORM DE VALIDATION D'INVITATION : INIT DU PASSWORD-->
		<?php if(Req::isParam("_idInvitation") && Req::isParam("newPassword")==false){ ?>
			<div><a data-fancybox="inline" data-src="#invitationPasswordForm"><?= Txt::trad("USER_invitPassword") ?></a></div>
			<form action="index.php" method="post" id="invitationPasswordForm" class="lightboxInline">
				<div class="lightboxTitle"><?= Txt::trad("USER_invitPassword2") ?></div>
				<input type="password" name="newPassword" placeholder="<?= Txt::trad("password") ?>" required>				<!--nouveau password-->
				<input type="password" name="newPasswordVerif" placeholder="<?= Txt::trad("passwordVerif") ?>" required>	<!--nouveau password : verif-->
				<input type="hidden" name="_idInvitation" value="<?= Req::param("_idInvitation") ?>">						<!--controle _idInvitation-->
				<input type="hidden" name="mail" value="<?= Req::param("mail") ?>">											<!--Affichage initial-->
				<br><?= Txt::submitButton("validate",false) ?>
			</form>
			<script> ready(function(){ Fancybox.show([{type:"inline",src:"#invitationPasswordForm"}]); }); </script>	<!--Affiche au chargement-->
		<?php } ?>

		<!--INSCRIPTION D'USER  ||  SWITCH D'ESPACE-->
		<?= (!empty($isUserInscription) || Req::isSpaceSwitch())  ?  "<hr>"  :  null ?>
		<?php if(!empty($isUserInscription)){ ?>
			<button onclick="lightboxOpen('?action=userInscription')" <?= Txt::tooltip("userInscriptionTooltip") ?> ><img src="app/img/user/subscribe.png"><?= Txt::trad("userInscription") ?></button>
		<?php }
		if(Req::isSpaceSwitch()){ ?>
			<button onclick="redir('<?= Req::connectSpaceSwitchUrl() ?>')"><img src="app/img/switch.png"><?= Txt::trad("connectSpaceSwitch") ?></button>
		<?php }  ?>

	</div>
</div>