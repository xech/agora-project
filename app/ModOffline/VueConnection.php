<script>
$(function(){
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
	$("#formConnect").submit(function(){
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
	$("#resetPasswordMailForm").submit(function(){
		if($(this).find("[name='resetPasswordMail']").isMail()==false)   {notify("<?= Txt::trad("mailInvalid") ?>");  return false;}
	});

	/**********************************************************************************************************
	 *	FORMULAIRE DE RESET DU PASSWORD ET FORMULAIRE DE VALIDATION D'INVITATION : CONTROLE DES CHAMPS "PASSWORD"
	 **********************************************************************************************************/
	$("#resetPasswordModifForm, #invitationPasswordForm").submit(function(){
		let newPassword		=$(this).find("[name='newPassword']").val();
		let newPasswordVerif=$(this).find("[name='newPasswordVerif']").val();
		if(!isValidUserPassword(newPassword))	{notify("<?= Txt::trad("passwordInvalid"); ?>");		return false;}//Password invalide
		else if(newPassword!=newPasswordVerif)	{notify("<?= Txt::trad("passwordConfirmError") ?>");	return false;}//Passwords différents
	});

	/**********************************************************************************************************
	 *	ACCÈS À UN ESPACE PUBLIQUE : ACCÈS DIRECT || AFFICHE LE FORMULAIRE DU PASSWORD
	 **********************************************************************************************************/
	$("#publicSpaceTab .option").click(function(){
		$("[name='_idSpaceAccess']").val($(this).attr("data-idSpace"));											//Enregistre le "_idSpaceAccess"
		if($(this).attr("data-hasPassword")==="true"){															//Accès avec password : 
			let facyboxOptions= {src:'#publicSpaceForm',type:'inline',buttons:['close'],modal:true};			//Option d'affichage du Fancybox
			$.fancybox.open(facyboxOptions);																	//Affiche le formulaire ('modal': sans bouton par défaut)
		}	
		else{																									//Accès direct à l'espace :
			let objUrlEncoded=encodeURIComponent($("[name='objUrl']").val());									//"objUrl" encodé
			redir("index.php?_idSpaceAccess="+$("[name='_idSpaceAccess']").val()+"&objUrl="+objUrlEncoded);		//Redir vers l'espace demandé
		}
	});

	/**********************************************************************************************************
	 *	ACCÈS À UN ESPACE PUBLIQUE : CONTROLE DU PASSWORD VIA AJAX
	 **********************************************************************************************************/
	$("#publicSpaceForm").submit(function(event){
		event.preventDefault();																																			//Pas de validation du form
		let objUrlEncoded=encodeURIComponent($("[name='objUrl']").val());																								//Récupère "objUrl" du form principal de connexion
		let password=encodeURIComponent($("#publicSpacePassword").val());																								//Récupère le password
		$.ajax("?action=PublicSpacePassword&_idSpaceAccess="+$("[name='_idSpaceAccess']").val()+"&password="+password).done(function(result){							//Controle Ajax du password
			if(/passwordError/i.test(result))	{notify("<?= Txt::trad("publicSpacePasswordError") ?>");}																//Notif d'erreur de password
			else								{redir("index.php?_idSpaceAccess="+$("[name='_idSpaceAccess']").val()+"&password="+password+"&objUrl="+objUrlEncoded);}	//Redir vers l'espace demandé!
		});
	});
});
</script>

<style>
body										{--inputWidth:300px;}/*Variable: largeur des boutons et Inputs*/
#headerBar>div								{padding:0px 20px;}/*surcharge*/
.miscContainer								{width:500px; margin:10px auto; padding:25px 10px; border-radius:10px; text-align:center;}/*surcharge*/
.miscContainer input:not([type=checkbox]), .miscContainer button	{width:var(--inputWidth); height:45px!important; border-radius:5px; margin-bottom:12px!important;}
.miscContainer button						{margin-bottom:25px!important;}
.miscContainer button>img					{margin-right:10px;}
.miscContainer hr							{margin:30px 0px;}

/*Logo custom*/
#customLogo									{background-color:rgba(250, 250, 250, 40%); padding:10px;}
#customLogo img								{max-width:100%; max-height:180px;}

/*Accès invité*/
#publicSpaceTab								{display:inline-table;} 
#publicSpaceTab>div							{display:table-cell; text-align:left; width:50%; line-height:25px;}
#publicSpaceTab>div:first-child				{text-align:right;}
#publicSpaceTab .option						{padding:0px 10px;}

/*Form de connexion*/
.vConnectOptions							{display:inline-table;}
.vConnectOptions>div						{display:table-cell; padding:0px 15px;}
.g_id_signin								{margin:40px auto!important; width:var(--inputWidth);}/*button gIdentity & Iframe*/
.vLightboxForm								{display:none; text-align:center}
.vLightboxForm input, .vLightboxForm button	{height:35px!important; margin:10px 5px!important; border-radius:5px;}

/*MOBILE*/
@media screen and (max-width:1024px){
	body									{--inputWidth:340px;}/*Variable: largeur des boutons et Inputs*/
	#headerBar>div							{display:block; padding:3px 10px; text-align:left!important; font-weight:normal;}/*surcharge*/
	.miscContainer							{width:100%; margin:30px 0px;}
	#publicSpaceTab, #publicSpaceTab>div	{display:block; width:100%; text-align:left!important;}
	#publicSpaceTab .option					{margin-left:40px; margin-top:10px;}
	.vConnectOptions>div					{font-size:0.9em;}
}
</style>


<div id="headerBar">
	<div><?= Ctrl::$agora->name ?></div>
	<div><?= Ctrl::$agora->description ?></div>
</div>

<div id="pageCenter">

	<!--LOGO CUSTOM-->
	<?php if(Ctrl::$agora->pathLogoConnect()) { ?>
		<div id="customLogo" class="miscContainer"><img src="<?= Ctrl::$agora->pathLogoConnect() ?>"></div>
	<?php } ?>


	<!--CONNEXION A UN ESPACE PUBLIC (INVITE)-->
	<?php if(!empty($objPublicSpaces)){ ?>
	<div id="publicSpaceTab" class="miscContainer">
		<div><img src="app/img/user/guest.png"> <?= Txt::trad("guestAccess") ?> &nbsp; <img src="app/img/arrowRightBig.png"></div>
		<div>
			<?php foreach($objPublicSpaces as $tmpSpace){ ?>
				<div class="option" data-idSpace="<?= $tmpSpace->_id ?>" data-hasPassword="<?= $tmpSpace->password?'true':'false' ?>" title="<?= Txt::trad("guestAccessTooltip")."<br>".$tmpSpace->description ?>"><?= $tmpSpace->name ?></div>
			<?php } ?>
		</div>
	</div>
	<form id="publicSpaceForm" class="vLightboxForm">
		<input type="password" id="publicSpacePassword" placeholder="<?= Txt::trad("password") ?>">
		<?= Txt::submitButton("validate",false) ?>
	</form>
	<?php }  ?>


	<!--AUTHENTIFICATION & OPTIONS-->
	<div class="miscContainer">
		<!--FORMULAIRE DE CONNEXION-->
		<form action="index.php" method="post" id="formConnect">
			<input type="text" name="connectLogin" value="<?= $defaultLogin ?>" placeholder="<?= Txt::trad("mailLlogin") ?>" title="<?= Txt::trad("mailLlogin") ?>"  class="isAutocomplete">
			<input type="password" name="connectPassword" value="<?= Req::param("newPassword") ?>" placeholder="<?= Txt::trad("password") ?>" title="<?= Txt::trad("password") ?>" class="isAutocomplete">
			<input type="hidden" name="objUrl" value="<?= Req::param("objUrl") ?>">					<!--accès direct à un objet via "getUrlExternal()"-->
			<input type="hidden" name="_idSpaceAccess" value="<?= Req::param("_idSpaceAccess") ?>">	<!--idem + accès à un espace publique-->
			<button type="submit"><?= Txt::trad("connect") ?></button>
			<div class="vConnectOptions">
				<div><input type="checkbox" name="rememberMe" value="1" id="boxRememberMe" checked>&nbsp;<label for="boxRememberMe" title="<?= Txt::trad("connectAutoTooltip") ?>"><?= Txt::trad("connectAuto") ?></label></div>
				<div><a data-fancybox="inline" data-src="#resetPasswordMailForm" id="resetPasswordLabel"><?= Txt::trad("resetPassword") ?></a></div><!--Afficher le form ci-dessous-->
			</div>
		</form>

		<!--BOUTON DE CONNEXION AVEC GOOGLE IDENTITY (https://developers.google.com/identity/gsi/web/guides/overview)-->
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
				////DEBUG::notify("ID: "+jsonResponse.sub+"<br>Email: "+jsonResponse.email+"<br>Full Name: "+jsonResponse.name+"<br>Given Name: "+jsonResponse.given_name+"<br>Family Name: "+jsonResponse.family_name);
			}
			</script>
			<div id="g_id_onload" data-client_id="<?= Ctrl::$agora->gIdentityClientId ?>" data-callback="gIdentityResponse" data-auto_prompt="false"></div> <!--Div pour charger l'API ("data-auto_prompt" masque le popup)-->
			<div class="g_id_signin" data-type="standard" data-shape="circle" data-size="large" data-width="290"></div>										<!--Bouton gIdentity (Iframe avec "data-width" idem "--buttons-width")-->
		<?php }  ?>

		<!--FORM DE RESET DU PASSWORD -> ETAPE 1 : ENVOI DE L'EMAIL-->
		<form action="index.php" method="post" id="resetPasswordMailForm" class="vLightboxForm">
			<?= Txt::trad("resetPassword2") ?><hr>
			<input type="text" name="resetPasswordMail" placeholder="<?= Txt::trad("mail") ?>">
			<input type="hidden" name="resetPasswordSendMail" value="1">
			<?= Txt::submitButton("send",false) ?>
		</form>

		<!--FORM DE RESET DU PASSWORD -> ETAPE 2 : MODIF DU PASSWORD-->
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

		<!--FORM DE VALIDATION D'INVITATION : INIT DU PASSWORD-->
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

		<!--INSCRIPTION D'USER  ||  SWITCH D'ESPACE-->
		<?= (!empty($isUserInscription) || Req::isSpaceSwitch())  ?  "<hr>"  :  null ?>
		<?php if(!empty($isUserInscription)){ ?>
			<button onclick="lightboxOpen('?action=userInscription')" title="<?= Txt::trad("userInscriptionTooltip") ?>"><img src="app/img/user/subscribe.png"><?= Txt::trad("userInscription") ?></button>
		<?php }
		if(Req::isSpaceSwitch()){ ?>
			<button onclick="redir('<?= Req::connectSpaceSwitchUrl() ?>')"><img src="app/img/switch.png"><?= Txt::trad("connectSpaceSwitch") ?></button>
		<?php }  ?>

	</div>
</div>