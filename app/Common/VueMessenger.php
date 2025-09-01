<script>
/********************************************************************************************************
 *	LOAD LA PAGE
*********************************************************************************************************/
ready(function(){
	messengerDisplayMode="none";																	//Affichage courant du messenger : "none" / "all" / "idUser"
	messengerCheckedUsers=[];																		//Users "checked" lors d'une discussion à plusieurs
	messengerAlert=new Audio("app/misc/messengerAlert.mp3");										//Charge le son mp3 d'alerte
	messengerAlert.volume=0.5;																		//Volume à 50% par défaut
	messengerAlert.loop=false;																		//Pas de son en boucle
	if(isMobile()==false)  {$("#messengerMain").draggable({handle:"#messengerMove",opacity:0.8});}	//Drag/drop du messenger
	setInterval(function(){ messengerUpdate(); },10000);											//Update le messenger toutes les 10 secondes
	messengerUpdate();																				//Lance le messenger !
});

/********************************************************************************************************
 *	UPDATE LE MESSENGER
*********************************************************************************************************/
function messengerUpdate()
{
	//// Init le messenger ?
	initMessenger=(typeof initMessenger=="undefined");

	//// Page affichée : update le pageVisibilityTime  ||  Return "false" si la page n'est pas affichée depuis + de 30mn (soit 1800000 ms. Evite ainsi les requetes Ajax inutiles)
	if(document.visibilityState=="visible")				{pageVisibilityTime=Date.now();}
	else if((Date.now()-pageVisibilityTime)>1800000)	{return false;}

	//// Url du "MessengerUpdate" (Ajax)
	var updateUrl="?ctrl=misc&action=MessengerUpdate";
	if(messengerDisplayMode!="none")  {updateUrl+="&messengerDisplayMode="+messengerDisplayMode;}																				//Mode d'affichage du messenger (cf. $_SESSION["messengerDisplayTimes"])
	if($(".fancybox__iframe").exist() && /edit/i.test($(".fancybox__iframe").attr("src")))  {updateUrl+="&editTypeId="+urlParam("typeId",$(".fancybox__iframe").attr("src"));}	//Vérif si quelqu'un edite déjà l'objet
	if(typeof tinymce!="undefined"  ||  ($(".fancybox__iframe").exist() && typeof $(".fancybox__iframe")[0].contentWindow.tinymce!="undefined")){								//Vérif si un éditeur Tinymce est affiché (page principale ou lightbox)
		var editorDraft=(typeof tinymce!="undefined")  ?  editorContent()  :  $(".fancybox__iframe")[0].contentWindow.editorContent();											//Récupère le contenu de l'éditeur
		if(editorDraft && editorDraft.length>0)  {updateUrl+="&editorDraft="+encodeURIComponent(editorDraft);}																	//Enregistre dans les brouillons (si pas "undefined"!)
	}

	//// Lance le "MessengerUpdate" (init/update Ajax du livecounter)
	$.ajax({url:updateUrl,dataType:"json"}).done(function(result){
		//// Affiche d'abord les messages du messenger (cf. ".vMessengerOldMessage" suivant)
		if(initMessenger==true || result.messengerUpdate==true)  {$("#messengerMessagesList>div").html(result.messengerMessagesHtml);}

		//// Affiche ensuite les users connectés
		if(initMessenger==true || result.livecounterUpdate==true){
			//Réinit l'affichage
			$("#livecounterMain,#messengerMultiUsers").hide();
			//Affiche le livecounter principal
			if(result.livecounterUsersHtml.length>0){
				$("#livecounterUsers").html(result.livecounterUsersHtml);												//Affiche d'abord les users du livecounter principal
				if($(".vLivecounterUser").length>=2)  {$("#messengerMultiUsers").show();}								//Affiche l'icone pour discuter à plusieurs (et voir les anciens messages)
				$("#livecounterMain").css("left", (windowWidth/2)-($("#livecounterMain").outerWidth(true)/2) ).show();	//Affiche enfin le livecounter centré sur la page
			}
			//Update les users du messenger (checkboxes)
			$("#messengerFormUsers>div").html("<div class='vMessengerUser'><?= Txt::trad("MESSENGER_chatWithMulti") ?></div>"+result.livecounterFormHtml);
		}

		//// Finalise l'affichage
		if(result.messengerUpdate==true || result.livecounterUpdate==true)  {messengerDisplayUser();}					//Affiche uniquement les messages d'un user OU les messages de tous les users
		if(result.livecounterUsersHtml.length>0 || result.messengerMessagesHtml.length>0)  {tooltipDisplay();}			//Update les tooltips
		messengerCheckedUsers=result.messengerCheckedUsers;																//Update la liste des users "checked" (après post d'un message dans une discussion à plusieurs)

		//// Pulsate & alerte sonore des users ayant posté un nouveau message
		if(result.livecounterUsersPulsate.length>0){
			result.livecounterUsersPulsate.forEach(function(idUserTmp){ $("#livecounterUser"+idUserTmp).pulsate(8); });	//Lance le "pulsate" des users concernés (pas + de 10sec : cf. "messengerUpdateInterval")
			messengerAlert.play();																						//Lance l'alerte sonore
		}
	});
}

/********************************************************************************************************
 *	AFFICHE/MASQUE LE MESSENGER   (messengerDisplayModeNew : "none" / "all" / "idUser")
*********************************************************************************************************/
function messengerDisplay(messengerDisplayModeNew)
{
	//// MessengerDisplayMode
	messengerDisplayMode=($("#messengerMain").isDisplayed() && messengerDisplayModeNew==messengerDisplayMode)  ?  "none"  :  messengerDisplayModeNew;	//"none" si on demande le même "messengerDisplayMode", sinon on enregistre le nouveau "messengerDisplayMode"
	if(messengerDisplayMode!="none")  {$.ajax({url:"?ctrl=misc&action=MessengerDisplayTimesUpdate&messengerDisplayMode="+messengerDisplayMode});}		//Messenger affiché : update le timestamp du "messengerDisplayMode" courant

	//// Masque le messenger principal
	if(messengerDisplayMode=="none"){
		$("#messengerMain").fadeOut();										//Masque le messenger!
		$("#headerModuleMessenger").removeClass("vHeaderModuleCurrent");	//cf. VueHeaderMenu.php
		if(isMobile())  {$("body").css("overflow","visible");}				//Réactive le scroll de la page en arriere plan
	}
	//// Affiche le messenger principal
	else{
		//// Affichage du messenger en mode mobile / normal
		if(isMobile()){
			menuMobileClose();												//Masque si besoin le menu principal (cf. icone messenger du menu des modules)
			messengerMobileDisplay();										//Messenger en pleine page.
			$(window).on("resize",function(){ messengerMobileDisplay(); });	//Idem si on resize la page (change d'orientation ou affiche le clavier virtuel)
		}else{
			var messengerLeftPos=(windowWidth/2)-($("#messengerMain").outerWidth(true)/2);	//Position Left du messenger, pour pouvoir le centrer ("outerWidth(true)" pour prendre en compte le "margin")
			$("#messengerMain").css("left",messengerLeftPos).resizable({handles:"e,w"});	//Centre le messenger && le rend "resizable" en largeur uniquement
		}
	
		//// Affiche le messenger  &&  Check un ou plusieurs users
		$("#messengerMain").show();										//Affiche le messenger!
		$("#headerModuleMessenger").addClass("vHeaderModuleCurrent");	//cf. VueHeaderMenu.php
		$(".messengerUserCheckbox").prop("checked",false);				//Réinit le "check" des users
		if($.isNumeric(messengerDisplayMode))	{$("#messengerUserCheckbox"+messengerDisplayMode).prop("checked",true);}												//User spécifique "checked"
		else if(messengerDisplayMode=="all")	{ messengerCheckedUsers.forEach(function(idUserTmp){ $("#messengerUserCheckbox"+idUserTmp).prop("checked",true); }); }	//Re-check les users déjà sélectionné lors d'une discussion à plusieurs

		//// Affiche l'input Texte et le bouton de visio
		labelUserDisplayed=($.isNumeric(messengerDisplayMode))  ?  $("#livecounterUser"+messengerDisplayMode).text()  :  null;															//Récup le label de l'user du livecounter
		var placeholderLabel=(labelUserDisplayed==null)  ?  "<?= Txt::trad("MESSENGER_messageToSelected") ?>"  :  "<?= Txt::trad("MESSENGER_messageTo") ?> "+labelUserDisplayed;		//Placeholer de l'input text
		visioButtonLabel=(labelUserDisplayed==null)  ?  "<?= Txt::trad("MESSENGER_visioProposeToSelection") ?>"  :  "<?= Txt::trad("MESSENGER_visioProposeTo") ?> "+labelUserDisplayed;	//Title du bouton de visio (variable globale)
		$("#messengerFormInput").attr("placeholder",placeholderLabel);	//Placeholer de l'input text : "Mon message à Boby" OU "Mon message aux personnes sélectionnées"
		$("#messengerFormInput").focusAlt();							//Focus sur l'input
		$("#launchVisioButton").tooltipUpdate(visioButtonLabel);		//Update le tooltip du bouton "Proposer une visio à Bob" / "Proposer une visio aux personnes sélectionnées"

		//// Divers
		messengerAlert.pause();									//Fin de son d'alerte
		$(".vLivecounterUser").stop(true).css("opacity","1");	//Fin de "pulsate"
		if(isMobile())  {$("body").css("overflow","hidden");}	//Désactive le scroll de la page en arriere plan
	}
	//// Affiche uniquement les messages d'un user OU les messages de tous les users
	messengerDisplayUser();
}

/********************************************************************************************************
 *	AFFICHE UNIQUEMENT LES MESSAGES D'UN USER OU LES MESSAGES DE TOUS LES USERS
*********************************************************************************************************/
function messengerDisplayUser()
{
	//Réinit le surlignage d'user dans le livecounter principal
	$(".vLivecounterUser,#messengerMultiUsers").removeClass("vLivecounterUserSelect");
	//Messenger affiché?
	if(messengerDisplayMode!="none"){
		//Réinit l'affichage des messages et des users du formulaire
		$(".vMessengerMessage,#messengerFormUsers").hide();
		//Affiche tous les utilisateurs et messages
		if(messengerDisplayMode=="all"){
			$(".vMessengerMessage,#messengerFormUsers").show();				//Affiche tous les messages
			$("#messengerMultiUsers").addClass("vLivecounterUserSelect");	//Surligne l'icone "#messengerMultiUsers"
		}
		//Affiche uniquement l'user sélectionné
		else if($.isNumeric(messengerDisplayMode)){
			$(".vMessengerMessage[data-idUsers*='@"+messengerDisplayMode+"@']").show();		//Affiche uniquement les messages concernant l'user sélectionné
			$("#livecounterUser"+messengerDisplayMode).addClass("vLivecounterUserSelect");	//Surligne dans le livecounter l'user sélectionné
		}
		//Affiche "Personne n'est connecté"  ||  Affiche le formulaire pour poster un message
		if($(".messengerUserCheckbox").isEmpty())	{$("#messengerNobodyDiv").show();  $("#messengerPostDiv,#messengerFormUsers").hide();}
		else										{$("#messengerNobodyDiv").hide();  $("#messengerPostDiv").show();}
		//Scroll jusqu'aux derniers messages  &&  Pulsate le dernier message s'il s'agit d'une proposition de visio
		$("#messengerMessagesList>div").scrollTop($("#messengerMessagesList>div").prop("scrollHeight"));												
		$(".vMessengerMessage:last-child .launchVisioMessage").pulsate(3);
	}
}

/**********************************************************************************************************************
 *	MESSENGER AFFICHA SUR MOBILE EN FULL PAGE  (Tester avec 5 users connectés, changements d'orientation, etc)
***********************************************************************************************************************/
function messengerMobileDisplay(){
	setTimeout(function(){																			//Timeout après affichage du clavier virtuel
		$("#messengerFormMain").css('padding-bottom', $("#livecounterMain").outerHeight(true) +15);	//Margin-bottom du "#messengerFormMain" pour afficher le #livecounterMain
		let contentHeight=windowHeight-$("#messengerHeader,#messengerFormMain").totalHeight() -15;	//Hauteur du contenu principal : messages
		$(".vMessengerContent,.vMessengerContent>div").height(contentHeight);						//Resize les divs principaux et divs scrollables
	},50);
}

/********************************************************************************************************
 *	CONTROLE & POST DU MESSAGE DU MESSENGER
*********************************************************************************************************/
function messengerPost(event)
{
	//Stop la validation du form
	if(typeof event!="undefined")  {event.preventDefault();}
	//Vérif qu'un message est spécifié  &&  Vérif qu'un user ou + est sélectionné
	if($("#messengerFormInput").isEmpty())  			{notify("<?= Txt::trad("MESSENGER_addMessageNotif") ?>");  return false;}
	if($(".messengerUserCheckbox:checked").length==0)	{notify("<?= Txt::trad("notifSelectUser") ?>");  return false;}
	// Poste le message via Ajax
	$.ajax({url:"?ctrl=misc&action=messengerPost",data:$("#messengerForm").serialize(),method:"POST"}).done(function(){
		$("#messengerFormInput").val("");		//Réinit l'input text
		$("#messengerFormInput").focusAlt();	//Focus à nouveau sur l'input
		messengerUpdate();						//Update les messages pour afficher le post
	});
}

/********************************************************************************************************
 *	PROPOSITION DE VISIO : POST UN MESSAGE AVEC LE LIEN DE VISIO
*********************************************************************************************************/
async function proposeVisio()
{
	//Vérif qu'au moins un user est sélectionné
	if($(".messengerUserCheckbox:checked").length==0)  {notify("<?= Txt::trad("notifSelectUser") ?>");  return false;}
	//Confirme la visio (cf. tooltip "visioButtonLabel")
	if(await confirmAlt(visioButtonLabel+" ?")){
		visioUsers	="<?= Ctrl::$curUser->getLabel("firstName") ?>";												//Init la liste des destinaires avec l'user courant
		visioURL	="<?= Ctrl::$agora->visioUrl()."-".Txt::clean(Ctrl::$curUser->getLabel("firstName"),"max") ?>";	//Init l'Url de la visio avec l'user courant
		$(".messengerUserCheckbox:checked").each(function(){														//Label de chaque user sélectionné :
			visioUsers	+=" & "+$(this).attr("data-user-label");													//Ajoute dans la liste des destinaires
			visioURL	+="-"+$(this).attr("data-user-label-visio");												//Ajoute dans l'url de la visio : incorpore le label de chaque participant dans le "visioId"
		});
		var visioMessage="<?= Txt::trad("MESSENGER_visioProposeToUsers") ?> "+visioUsers+'<img src="app/img/visioSmall.png">';				//Message "Cliquez ici pour lancer la visioconférence : Will & Boby"
		$("#messengerFormInput").val('<a href="javascript:launchVisio(\''+visioURL+'\')" class="launchVisioMessage">'+visioMessage+'</a>');	//Post le message avec le "launchVisio()" (tester car filtré via "Db::format()")
		messengerPost();
	}
}
</script>


<style>
/*Livecounter principal + Messenger*/
#livecounterMain, #messengerMain  				{display:none; position:fixed; max-width:100%!important; max-height:100%!important; color:#ddd!important;}

/*Livecounter principal*/
#livecounterMain								{z-index:121;/*idem #menuMobileMain +1*/ bottom:5px; background:#222; padding:3px 20px; border-radius:20px;}
#livecounterIcon								{margin-right:15px;}
#messengerMultiUsers							{margin-left:10px; padding:3px;}
.vLivecounterUser								{display:inline-block; line-height:35px; padding:2px 5px; margin-left:10px; border:solid 1px transparent; color:white!important;}/*Label des users (cf. "actionMessengerUpdate()")*/
.vLivecounterUser:hover,.vLivecounterUserSelect	{background:#393939; border:solid 1px #777; border-radius:10px;}/*Label d'un user sélectionné*/
.vLivecounterUser .personImg					{width:35px; height:35px; margin-right:8px;}/*Image des users (cf. "actionMessengerUpdate()")*/

/*Messenger : contenu principal*/
#messengerMain									{z-index:120;/*idem #menuMobileMain*/ bottom:0px; background:#222; padding:10px; width:800px; min-width:300px; border-radius:5px; border:0px;}/*z-index idem .menuContext*/
#messengerMain td								{vertical-align:top;}
#messengerHeader								{height:22px;}
#messengerMove									{float:left; width:95%; cursor:move; background-image:url(app/img/messengerMove.png); background-repeat:repeat-x;}
#messengerClose									{float:right;}
#messengerFormUsers								{width:200px;}
#messengerMessagesList							{background-image:url(app/img/messengerBackground.png); background-repeat:no-repeat; background-position:50% 50%;}
.vMessengerContent, .vMessengerContent>div		{height:500px;}				/*Height prédéfini pour éviter que ".vMessengerContent>div" ne puissent agrandir automatiquement le <table>*/
.vMessengerContent>div							{overflow-y:auto;}			/*divs scrollables : tester avec de nombreux messages*/
.vMessengerContent>div::-webkit-scrollbar		{background:transparent;}	/*background du scrollbar*/
.vMessengerContent>div::-webkit-scrollbar-thumb	{background:#555;}			/*couleur du scrollbar*/
.vMessengerMessage								{width:100%;}
.vMessengerMessage tr:hover						{background:#393939;}
.vMessengerMessage td							{padding:4px; cursor:help; vertical-align:middle;}
.vMessengerMessageDateAutor						{min-width:80px; width:1%; white-space:nowrap; vertical-align:top; color:#888; font-size:0.9rem;}/*Heure/label de l'auteur. Width ajusté via 'nowrap'*/
.vMessengerMessage .personImg					{width:22px; height:22px; margin-left:8px;}	/*image des users dans les messages (cf. "actionMessengerUpdate()")*/
.vMessengerMessage .iconUsersMultiple			{height:18px; margin-bottom:5px;}			/*Icone de discussion à plusieurs*/
.vMessengerMessage a							{color:white;}								/*lien des visios*/
.vMessengerUser									{margin:10px;}
.vMessengerUser input							{display:none;}
.vMessengerUser input:checked+label				{color:#679cd8;}/*Idem .linkSelect de black.css*/
.vMessengerUser .personImg						{width:22px; height:22px; margin-right:5px;}/*img des users dans le form de selection (cf. "actionMessengerUpdate()")*/

/*Messenger : formulaire*/
#messengerFormUsers>div, #messengerPostDiv, #messengerNobodyDiv	 {background:#393939; border-radius:5px;}
#messengerFormMain								{height:50px; padding-bottom:60px; text-align:center;}/*60px de padding-bottom pour afficher du "#livecounterMain" qui s'y supperpose*/
#messengerPostDiv								{padding:10px;}
#messengerFormInput, #messengerFormButton		{height:40px; box-shadow:none; border-radius:10px;}
#messengerFormInput								{width:400px; max-width:60%;}
#messengerFormButton							{width:100px; margin-left:5px; margin-bottom:3px;}
#launchVisioButton								{margin-left:20px; cursor:pointer;}
.launchVisioMessage img[src*='visioSmall']		{margin-left:10px;}
#messengerNobodyDiv								{padding:20px;}

/*RESPONSIVE SMALL*/
@media screen and (max-width:1024px){
	.vLivecounterUser							{margin-inline:0px; padding-block:10px;}
	.vLivecounterUser .personImg				{display:none;}
	#messengerMain								{width:100%!important; height:100%!important; border:none!important; box-shadow:none!important; border-radius:0px; padding:0px; font-size:0.9rem;}
	#messengerHeader							{text-align:right; height:30px;}/*cf. #messengerClose*/
	#messengerMove								{display:none;}
	#messengerClose								{float:none;}
	.vMessengerMessageDateAutor					{min-width:60px; max-width:100px;}/*Width toujours ajusté mais 100px max*/
	#messengerFormUsers							{width:130px;}
	.vMessengerUser								{margin:12px 4px;}
	#messengerFormInput							{width:200px;}/*tester avec le clavier viruel : qu'il ne cache pas le formulaire !*/
	#messengerFormButton						{width:80px;}
	#messengerFormButton img					{display:none;}
}
</style>


<!--LIVECOUNTER PRINCIPAL : LISTE DES USERS CONNECTES-->
<table id="livecounterMain">
	<tr>
		<td><img src="app/img/messenger.png" id="livecounterIcon"></td>
		<td id="livecounterUsers"></td>
		<td><img src="app/img/user/iconSmall.png" onclick="messengerDisplay('all')" id="messengerMultiUsers" <?= Txt::tooltip("MESSENGER_messengerMultiUsers") ?>></td>
	</tr>
</table>


<!--MESSENGER : LISTE DES MESSAGES & FORMULAIRE-->
<form id="messengerForm">
	<table id="messengerMain">
		<tr>
			<td colspan="2" id="messengerHeader">
				<!--DRAG/DROP & CLOSE DU MESSENGER-->
				<div id="messengerMove">&nbsp;</div>
				<img src="app/img/messengerClose.png" id="messengerClose" onclick="messengerDisplay('none')" <?= Txt::tooltip("close") ?> >
			</td>
		</tr>
		<tr>
			<!--SELECTION DES USERS ("hidden" par défaut)  &  LISTE DES MESSAGES-->
			<td id="messengerFormUsers" class="vMessengerContent"><div>&nbsp;</div></td>		
			<td id="messengerMessagesList" class="vMessengerContent"><div>&nbsp;</div></td>
		</tr>
		<tr>
			<td colspan="2" id="messengerFormMain">
				<!--INPUT TEXT & BUTTON DU FORMULAIRE-->
				<div id="messengerPostDiv">
					<input type="text" name="message" id="messengerFormInput" maxlength="1000">
					<button id="messengerFormButton" onclick="messengerPost(event);"><img src="app/img/postMessage.png"> <?= Txt::trad("send") ?></button>
					<?php if(Ctrl::$agora->visioEnabled()){ ?><img src="app/img/visio.png" id="launchVisioButton" onclick="proposeVisio()" <?= Txt::tooltip("MESSENGER_visioProposeToSelection") ?> ><?php } ?>
				</div>
				<!--"Personne connecté actuellement"-->
				<div id="messengerNobodyDiv">
					<?= Txt::trad("MESSENGER_nobody") ?>
				</div>
			</td>
		</tr>
	</table>
</form>