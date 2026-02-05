<script>
ready(function(){
	/**********************************************************************************************************
	 *	INIT L'AFFICHAGE
	 **********************************************************************************************************/
    $("<?= empty($defaultLogin)?'#connectLogin':'#connectPassword' ?>").focusAlt();					//Focus sur l'input du login ou password
	<?php if(Req::isParam("notify") && in_array("NOTIF_identification",Req::param("notify"))){ ?>	//Pulsate "resetPasswordLabel" si l'authentification est erronée
		$("#resetPasswordLabel").addClass("linkSelect").pulsate(5);
	<?php } ?>

	/**********************************************************************************************************
	 *	CONTROLE L'EMAIL DE RESET DU PASSWORD
	 **********************************************************************************************************/
	$("#resetPasswordFormSendmail").on("submit",function(){
		if($(this).find("[name='resetPasswordMail']").isMail()==false)   {notify("<?= Txt::trad("mailInvalid") ?>");  return false;}
	});

	/**********************************************************************************************************
	 *	FORMULAIRE DE RESET DU PASSWORD ET FORMULAIRE DE VALIDATION D'INVIT. : CONTROLE DES CHAMPS "PASSWORD"
	 **********************************************************************************************************/
	$("#resetPasswordFormUpdate, #invitationPasswordForm").on("submit",function(){
		if($(this).find("[name='newPassword']").isPassword()==false)   {notify("<?= Txt::trad("passwordInvalid"); ?>");  return false;}//Password invalide
	});

	/**********************************************************************************************************
	 *	ESPACE PUBLIQUE :  AFFICHE LE FORMULAIRE DU PASSWORD  ||  ACCÈS DIRECT
	 **********************************************************************************************************/
	$(".publicSpaceOption").click(function(){
		if($(this).attr("data-hasPassword")==="true"){
			$("#spaceNamePublic").html($(this).attr("data-spaceName"));
			$("#idSpacePublic").val($(this).attr("data-idSpace"));
			Fancybox.show([{type:"inline",src:"#publicSpaceForm"}]);
		}else{
			let objUrl=encodeURIComponent($("#objUrlExternal").val());
			redir("index.php?_idSpaceAccess="+$(this).attr("data-idSpace")+"&objUrl="+objUrl);
		}
	});

	/**********************************************************************************************************
	 *	ESPACE PUBLIQUE : CONTROLE DU PASSWORD VIA AJAX
	 **********************************************************************************************************/
	$("#publicSpaceForm").on("submit",function(event){
		event.preventDefault();
		let idSpacePublic=$("#idSpacePublic").val();																							//idSpace
		let password=encodeURIComponent($("[name='publicSpacePassword']").val());																//space password
		let objUrl=encodeURIComponent($("#objUrlExternal").val());																				//Url de l'objet
		$.ajax("index.php?action=PublicSpacePasswordControl&idSpacePublic="+idSpacePublic+"&passwordControl="+password).done(function(result){	//Controle Ajax du password (pas '_idSpaceAccess=' dans l'URL!)
			if(/passwordOK/i.test(result))	{redir("index.php?_idSpaceAccess="+idSpacePublic+"&password="+password+"&objUrl="+objUrl);}			//Redir vers l'espace demandé
			else							{notify("<?= Txt::trad("publicSpacePasswordError") ?>");}											//Notif d'erreur de password
		});
	});
});
</script>


<style>
#headerBar>div:first-child			{text-align:center!important;}/*surcharge*/
#pageCenter							{margin-top:100px;}
.miscContainer						{margin-bottom:20px; width:500px; padding:30px 20px; border-radius:12px!important; text-align:center;}/*surcharge*/
.miscContainer button>img			{margin-right:10px;}
.miscContainer hr					{margin:30px 0px;}
#customLogo							{background-color:rgba(250, 250, 250, 40%); padding:10px;}
#customLogo img						{max-width:100%; max-height:180px;}
#publicSpaceTab						{display:inline-table;} 
#publicSpaceTab>div					{display:table-cell; text-align:left; width:50%; line-height:25px;}
#publicSpaceTab>div:first-child		{text-align:right;}
.publicSpaceOption					{padding:0px 10px;}
.connectOptions						{display:inline-table;}
.connectOptions>div					{display:table-cell; padding:15px;}
.g_id_signin						{margin-inline:auto; margin-top:40px; width:330px;}/*width idem "data-width" */

/*AFFICHAGE RESPONSIVE*/
@media screen and (max-width:1200px){
	.miscContainer							{width:auto;}
	.miscContainer							{margin-bottom:30px; padding:30px;}/*surcharge*/
	#publicSpaceTab, #publicSpaceTab>div	{display:block; width:100%; text-align:left!important;}
	.publicSpaceOption						{margin-left:40px; margin-top:10px;}
	.connectOptions, .connectOptions>div	{display:block;}
	.connectOptions>div						{font-size:0.9rem;}
}
</style>


<div id="headerBarContainer">
	<div id="headerBar">
		<div><?= ucfirst(Ctrl::$agora->name).(!empty(Ctrl::$agora->description)?' - '.Txt::reduce(Ctrl::$agora->description):null) ?></div>
	</div>
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
		<div class="lightboxTitle"><?= Txt::trad("guestAccess") ?> : <span id="spaceNamePublic"></span></div>
		<input type="hidden" name="_idSpaceAccess" id="idSpacePublic">
		<?= Txt::inputPassword("publicSpacePassword",true).Txt::submitButton("validate",false) ?>
	</form>
	<?php }  ?>

	<!--FORMULAIRE DE CONNEXION-->
	<div class="miscContainer">
		<form action="index.php" method="post" id="connectFormSpace" class="connectForm">
			<input type="hidden" name="objUrl" value="<?= Req::param("objUrl") ?>" id="objUrlExternal">	<!--accès direct à un objet via "getUrlExternal()"-->
			<input type="hidden" name="_idSpaceAccess" value="<?= Req::param("_idSpaceAccess") ?>">		<!--idem-->
			<input type="text" name="connectLogin" value="<?= $defaultLogin ?>" id="connectLogin" placeholder="<?= Txt::trad("mailLlogin") ?>" <?= Txt::tooltip("mailLlogin") ?>  class="isAutocomplete" required>
			<?= Txt::inputPassword("connectPassword",true,true).Txt::submitButton("connect") ?>
			<div class="connectOptions">
				<div><input type="checkbox" name="rememberMe" value="1" id="boxRememberMe" checked>&nbsp;<label for="boxRememberMe" <?= Txt::tooltip("connectAutoTooltip") ?> ><?= Txt::trad("connectAuto") ?></label></div>
				<div><a data-fancybox="inline" data-src="#resetPasswordFormSendmail" id="resetPasswordLabel"><?= Txt::trad("resetPassword") ?></a></div><!--Afficher le form ci-dessous-->
			</div>
		</form>

		<!--RESET DU PASSWORD -> ETAPE 1 : ENVOI DE L'EMAIL-->
		<form action="index.php" method="post" id="resetPasswordFormSendmail" class="lightboxInline">
			<div class="lightboxTitle"><?= Txt::trad("resetPassword2") ?></div>
			<input type="text" name="resetPasswordMail" placeholder="<?= Txt::trad("mail") ?>" required>
			<input type="hidden" name="resetPasswordSendMail" value="1">
			<?= Txt::submitButton("send",false) ?>
		</form>

		<!--RESET DU PASSWORD -> ETAPE 2 : MODIF DU PASSWORD-->
		<?php if(!empty($resetPasswordIdOk) && Req::isParam("newPassword")==false){ ?>
			<div data-fancybox="inline" data-src="#resetPasswordFormUpdate"><?= Txt::trad("passwordModif") ?></div>
			<form action="index.php" method="post" id="resetPasswordFormUpdate" class="lightboxInline">
				<div class="lightboxTitle"><?= Txt::trad("passwordModif") ?></div>
				<input type="hidden" name="resetPasswordMail" value="<?= Req::param("resetPasswordMail") ?>">			<!--email du reset-->
				<input type="hidden" name="connectLogin" value="<?= Req::param("resetPasswordMail") ?>">				<!--pré-remplissage après reset-->
				<input type="hidden" name="resetPasswordId" value="<?= Req::param("resetPasswordId") ?>">				<!--ID de vérif-->
				<?= Txt::inputPassword("newPassword",true).Txt::submitButton("validate",false) ?>
			</form>
			<script> ready(function(){ Fancybox.show([{type:"inline",src:"#resetPasswordFormUpdate"}]); }); </script>	<!--Affichage initial-->
		<?php } ?>

		<!--VALIDATION D'INVITATION : INIT DU PASSWORD-->
		<?php if(Req::isParam("_idInvitation") && Req::isParam("newPassword")==false){ ?>
			<div><a data-fancybox="inline" data-src="#invitationPasswordForm"><?= Txt::trad("USER_invitPassword") ?></a></div>
			<form action="index.php" method="post" id="invitationPasswordForm" class="lightboxInline">
				<div class="lightboxTitle"><?= Txt::trad("USER_invitPassword2") ?></div>
				<input type="hidden" name="mail" value="<?= Req::param("mail") ?>">										<!--Affichage initial-->
				<input type="hidden" name="_idInvitation" value="<?= Req::param("_idInvitation") ?>">					<!--ID de vérif-->
				<?= Txt::inputPassword("newPassword",true).Txt::submitButton("validate",false) ?>
			</form>
			<script> ready(function(){ Fancybox.show([{type:"inline",src:"#invitationPasswordForm"}]); }); </script>	<!--Affichage initial-->
		<?php } ?>

		<!--CONNEXION VIA GOOGLE OAUTH-->
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
			}
			</script>
			<div>
				<div class="g_id_signin" data-shape="circle" data-width="330" <?= Txt::tooltip("AGORA_gOAuth") ?>></div>										<!--Bouton Google OAuth-->
				<div id="g_id_onload" data-client_id="<?= Ctrl::$agora->gIdentityClientId ?>" data-callback="gOAuthResponse" data-auto_prompt="false"></div>	<!--Div pour charger l'API-->
			</div>
		<?php } ?>
	
		<!--INSCRIPTION D'USER  ||  SWITCH D'ESPACE-->
		<?php if(!empty($isUserInscription) || Req::isSpaceSwitch()){ ?>
			<hr>
			<div class="connectForm">
				<!--INSCRIPTION D'USER-->
				<?php if(!empty($isUserInscription)){ ?>
					<button onclick="lightboxOpen('?action=userInscription')" <?= Txt::tooltip("userInscriptionTooltip") ?> ><img src="app/img/user/subscribe.png"><?= Txt::trad("userInscription") ?></button>
				<?php } ?>
				<!--SWITCH D'ESPACE-->
				<?php if(Req::isSpaceSwitch()){ ?>
					<button onclick="redir('<?= Req::connectSpaceSwitchUrl() ?>')"><img src="app/img/switch.png"><?= Txt::trad("connectSpaceSwitch") ?></button>
				<?php } ?>
			</div>
		<?php }  ?>
	</div>
</div>