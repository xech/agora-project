<script>
////	Init
$(function(){
	////	Init l'affichage
	$(".miscContainer").fadeIn(500);																//Affichage "fade" du formulaire de connexion
    $("input[name='<?= empty($defaultLogin) ? "connectLogin" : "connectPassword" ?>']").focus();	//Focus sur l'input du login ou password
	<?php if(Req::isParam("notify") && in_array("NOTIF_identification",Req::param("notify"))){ ?>	//Pulsate "resetPasswordLabel" si l'authentification est erronée
		$("#resetPasswordLabel").addClass("linkSelect").pulsate(10);
	<?php } ?>

	////	Contrôle du formulaire de connexion
	$("#formConnect").submit(function(){
		var connectLogin=$("[name=connectLogin]");
		var connectPassword=$("[name=connectPassword]");
		if(connectLogin.isEmpty() || connectLogin.val()==connectLogin.attr("placeholder") || connectPassword.isEmpty() || connectPassword.val()==connectPassword.attr("placeholder")){
			notify("<?= Txt::trad("specifyLoginPassword") ?>");
			return false;
		}
	});

	////	Controle l'email de reset du password
	$("#resetPasswordMailForm").submit(function(){
		if($(this).find("[name='resetPasswordMail']").isMail()==false)   {notify("<?= Txt::trad("mailInvalid") ?>");  return false;}
	});

	////	Formulaire de reset du password et Formulaire de validation d'invitation : controle des champs "password"
	$("#resetPasswordModifForm, #invitationPasswordForm").submit(function(){
		var newPassword		=$(this).find("[name='newPassword']").val();
		var newPasswordVerif=$(this).find("[name='newPasswordVerif']").val();
		if(!isValidUserPassword(newPassword))	{notify("<?= Txt::trad("passwordInvalid"); ?>");		return false;}//Password invalide
		else if(newPassword!=newPasswordVerif)	{notify("<?= Txt::trad("passwordConfirmError") ?>");	return false;}//Passwords différents
	});

	////	Accès à un espace public : accès direct || affiche le formulaire du password
	$(".publicSpaceLabel").click(function(){
		if($(this).attr("data-hasPassword")==="true"){													//Accès avec password :
			$("[name='_idSpaceAccess']").val($(this).attr("data-idSpace"));								//Enregistre le "_idSpaceAccess" dans l'input du form principal de connexion
			$.fancybox.open({src:'#publicSpaceForm',type:'inline',buttons:['close'],modal:true});		//Affiche le formulaire du password ('modal' pour masquer les boutons par défaut)
		}else{																							//Accès direct à l'espace :
			var objUrlEncoded=encodeURIComponent($("[name='objUrl']").val());							//Récupère "objUrl" du form principal de connexion
			redir("index.php?_idSpaceAccess="+$(this).attr("data-idSpace")+"&objUrl="+objUrlEncoded);	//Redir vers l'espace demandé!
		}
	});

	////	Accès à un espace public : controle du password via Ajax
	$("#publicSpaceForm").submit(function(event){
		event.preventDefault();																														//Stop la validation du form
		var _idSpaceAccess=$("[name='_idSpaceAccess']").val();																						//Récupère le "_idSpaceAccess" dans l'input du form principal de connexion
		var objUrlEncoded=encodeURIComponent($("[name='objUrl']").val());																			//Récupère "objUrl" du form principal de connexion
		var password=encodeURIComponent($("#publicSpacePassword").val());																			//Récupère le password
		$.ajax("?action=PublicSpacePassword&_idSpaceAccess="+_idSpaceAccess+"&password="+password).done(function(result){							//Controle Ajax du password
			if(/passwordError/i.test(result))	{notify("<?= Txt::trad("publicSpacePasswordError") ?>");}											//Notif d'erreur de password
			else								{redir("index.php?_idSpaceAccess="+_idSpaceAccess+"&password="+password+"&objUrl="+objUrlEncoded);}	//Redir vers l'espace demandé!
		});
	});
});
</script>


<style>
body										{--buttons-width:300px!important;}/*Variable: largeur des boutons et Inputs*/
button>img									{margin-right:10px;}
#headerBar>div								{padding:0px 20px;}/*surcharge*/
#pageCenter									{margin-top:120px;}/*surcharge*/
.miscContainer								{display:none; max-width:500px;/*sur mobile*/ padding:30px 10px; margin:0px auto 0px auto; border-radius:5px; text-align:center;}/*surcharge*/
.miscContainer hr							{margin:30px 0px;}
#customLogo									{margin-bottom:40px;}
#customLogo img								{max-width:100%; max-height:250px;}
#formConnect input[type=text], #formConnect input[type=password], #formConnect button, .vMainButton	{width:var(--buttons-width); height:45px; border-radius:5px; margin-bottom:15px;}/*surcharge*/
.vConnectOptions							{display:inline-table;}
.vConnectOptions>div						{display:table-cell; padding:10px;}
.vLightboxForm								{display:none;}
.vLightboxForm input						{height:35px; margin:5px; width:230px;}
.vLightboxForm button						{height:35px; margin:5px; width:150px;}
.g_id_signin								{margin-left:auto; margin-right:auto; width:var(--buttons-width);}/*button gIdentity & Iframe*/
#publicSpaceTab								{display:inline-table; margin-left:auto; margin-right:auto;} 
#publicSpaceTab>div							{display:table-cell; line-height:30px;} 
#publicSpaceTab ul							{margin:0px;}

/*MOBILE*/
@media screen and (max-width:1023px){
	#headerBar>div							{display:block; padding:4px; text-align:left!important; font-weight:normal;}/*surcharge*/
	#pageCenter								{margin-top:70px;}/*surcharge*/
	.miscContainer							{width:100%!important;}
	#publicSpaceTab>div						{display:table-row;} 
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
		<form action="index.php" method="post" id="formConnect">
			<input type="text" name="connectLogin" value="<?= $defaultLogin ?>" placeholder="<?= Txt::trad("mailLlogin") ?>" title="<?= Txt::trad("mailLlogin") ?>">
			<input type="password" name="connectPassword" value="<?= Req::param("newPassword") ?>" placeholder="<?= Txt::trad("password") ?>" title="<?= Txt::trad("password") ?>">
			<input type="hidden" name="objUrl" value="<?= Req::param("objUrl") ?>">					<!--accès direct à un objet via "getUrlExternal()"-->
			<input type="hidden" name="_idSpaceAccess" value="<?= Req::param("_idSpaceAccess") ?>">	<!--idem-->
			<button type="submit"><?= Txt::trad("connect") ?></button>
			<div class="vConnectOptions">
				<div><input type="checkbox" name="rememberMe" value="1" id="boxRememberMe" checked><label for="boxRememberMe" title="<?= Txt::trad("connectAutoTooltip") ?>"><?= Txt::trad("connectAuto") ?></label></div>
				<div><a data-fancybox="inline" data-src="#resetPasswordMailForm" id="resetPasswordLabel"><?= Txt::trad("resetPassword") ?></a></div><!--Afficher le form ci-dessous-->
			</div>
		</form>


		<!--RESET DU PASSWORD : ENVOI DE L'EMAIL => 1ERE ETAPE-->
		<form action="index.php" method="post" id="resetPasswordMailForm" class="vLightboxForm">
			<?= Txt::trad("resetPassword2") ?><hr>
			<input type="text" name="resetPasswordMail" placeholder="<?= Txt::trad("mail") ?>">
			<input type="hidden" name="resetPasswordSendMail" value="1">
			<?= Txt::submitButton("send",false) ?>
		</form>


		<!--RESET DU PASSWORD : MODIF DU PASSWORD => 2EME ETAPE-->
		<?php if(!empty($resetPasswordIdOk) && Req::isParam("newPassword")==false){ ?>
			<div><a data-fancybox="inline" data-src="#resetPasswordModifForm" id="resetPasswordModifFormLabel"><?= Txt::trad("passwordModify") ?></a></div>
			<form action="index.php" method="post" id="resetPasswordModifForm" class="vLightboxForm">
				<?= Txt::trad("passwordModify") ?><hr>
				<input type="password" name="newPassword" placeholder="<?= Txt::trad("password") ?>"><br>			<!--nouveau password-->
				<input type="password" name="newPasswordVerif" placeholder="<?= Txt::trad("passwordVerif") ?>">		<!--nouveau password : verif-->
				<input type="hidden" name="resetPasswordMail" value="<?= Req::param("resetPasswordMail") ?>">		<!--pour le controle du reset-->
				<input type="hidden" name="resetPasswordId" value="<?= Req::param("resetPasswordId") ?>">			<!--idem-->
				<input type="hidden" name="connectLogin" value="<?= Req::param("resetPasswordMail") ?>">			<!--pour le pré-remplissage du champ login après validation du form-->
				<br><?= Txt::submitButton("validate",false) ?>
			</form>
			<script> $(function(){ $("#resetPasswordModifFormLabel").trigger("click"); }); </script>				<!--Affiche au chargement de la page-->
		<?php } ?>


		<!--VALIDATION D'INVITATION : INIT DU PASSWORD-->
		<?php if(Req::isParam("_idInvitation") && Req::isParam("newPassword")==false){ ?>
			<div><a data-fancybox="inline" data-src="#invitationPasswordForm" id="invitationPasswordFormLabel"><?= Txt::trad("USER_invitPassword") ?></a></div>
			<form action="index.php" method="post" id="invitationPasswordForm" class="vLightboxForm">
				<?= Txt::trad("USER_invitPassword2") ?><hr>
				<input type="password" name="newPassword" placeholder="<?= Txt::trad("password") ?>"><br>			<!--nouveau password-->
				<input type="password" name="newPasswordVerif" placeholder="<?= Txt::trad("passwordVerif") ?>">		<!--nouveau password : verif-->
				<input type="hidden" name="_idInvitation" value="<?= Req::param("_idInvitation") ?>">				<!--pour le controle de l'invitation-->
				<input type="hidden" name="mail" value="<?= Req::param("mail") ?>">									<!--idem-->
				<br><?= Txt::submitButton("validate",false) ?>
			</form>
			<script> $(function(){ $("#invitationPasswordFormLabel").trigger("click"); }); </script>				<!--Affiche au chargement de la page-->
		<?php } ?>


		<!--CONNEXION AVEC GOOGLE IDENTITY (https://developers.google.com/identity/gsi/web/guides/overview)-->
		<?php if(Ctrl::$agora->gIdentityEnabled()){ ?>
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
				////notify("ID: "+jsonResponse.sub+"<br>Email: "+jsonResponse.email+"<br>Full Name: "+jsonResponse.name+"<br>Given Name: "+jsonResponse.given_name+"<br>Family Name: "+jsonResponse.family_name);
			}
			</script>
			<hr>
			<div id="g_id_onload" data-client_id="<?= Ctrl::$agora->gIdentityClientId ?>" data-callback="gIdentityResponse" data-auto_prompt="false"></div> <!--Div pour charger l'API ("data-auto_prompt" masque le popup)-->
			<div class="g_id_signin" data-type="standard" data-shape="circle" data-size="large" data-width="290"></div>										<!--Bouton gIdentity (Iframe avec "data-width" idem "--buttons-width")-->
		<?php }  ?>


		<!--CONNEXION A UN ESPACE PUBLIC (INVITE)-->
		<?php if(!empty($objPublicSpaces)){ ?>
			<hr>
			<div id="publicSpaceTab">
				<div><img src="app/img/user/guest.png"> <?= Txt::trad("guestAccess") ?> :</div>
				<div><ul>
				<?php foreach($objPublicSpaces as $tmpSpace){ ?>
					<li class="publicSpaceLabel sLink" data-idSpace="<?= $tmpSpace->_id ?>" data-hasPassword="<?= $tmpSpace->password?'true':'false' ?>" title="<?= Txt::trad("guestAccessTooltip") ?>"><?= $tmpSpace->name ?></li>
				<?php } ?>
				</ul></div>
			</div>
			<form id="publicSpaceForm" class="vLightboxForm">
				<input type="password" id="publicSpacePassword" placeholder="<?= Txt::trad("password") ?>">
				<?= Txt::submitButton("validate",false) ?>
			</form>
		<?php }  ?>


		<!--INSCRIPTION D'USER-->
		<?php if(!empty($userInscription)){ ?>
			<hr><button class="vMainButton" onclick="lightboxOpen('?action=userInscription')" title="<?= Txt::trad("userInscriptionTooltip") ?>"><img src="app/img/user/subscribe.png"><?= Txt::trad("userInscription") ?></button>
		<?php }  ?>


		<!--SWITCH D'ESPACE-->
		<?php if(Req::isSpaceSwitch()){ ?>
			<hr><button class="vMainButton" onclick="redir('<?= Req::connectSpaceSwitchUrl() ?>')"><img src="app/img/login.png"><?= Txt::trad("connectSpaceSwitch") ?></button>
		<?php }  ?>

	</div>
</div>