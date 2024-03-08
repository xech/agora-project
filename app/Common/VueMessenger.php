<script>
/*******************************************************************************************
 *	LOAD LA PAGE
*******************************************************************************************/
$(function(){
	messengerDisplayMode="none";																	//Affichage courant du messenger : "none" / "all" / "idUser"
	messengerCheckedUsers=[];																		//Users "checked" lors d'une discussion à plusieurs
	messengerAlert=new Audio("app/misc/messengerAlert.mp3");										//Charge le son mp3 d'alerte
	messengerAlert.volume=0.5;																		//Volume à 50% par défaut
	messengerAlert.loop=false;																		//Pas de son en boucle
	if(isMobile()==false)  {$("#messengerMain").draggable({handle:"#messengerMove",opacity:0.9});}	//Drag/drop du messenger
	setInterval(function(){ messengerUpdate(); },10000);											//Update le messenger toutes les 10 secondes
	messengerUpdate();																				//Lance le messenger !
});

/*******************************************************************************************
 *	UPDATE LE MESSENGER
*******************************************************************************************/
function messengerUpdate()
{
	//// Init le messenger ?
	initMessenger=(typeof initMessenger=="undefined");

	//// Page affichée : update le pageVisibilityTime  ||  Return "false" si la page n'est pas affichée depuis + de 30mn (soit 1800000 ms. Evite ainsi les requetes Ajax inutiles)
	if(document.visibilityState=="visible")				{pageVisibilityTime=Date.now();}
	else if((Date.now()-pageVisibilityTime)>1800000)	{return false;}

	//// Url du "MessengerUpdate" (Ajax)
	var updateUrl="?ctrl=misc&action=MessengerUpdate";
	if(messengerDisplayMode!="none")  {updateUrl+="&messengerDisplayMode="+messengerDisplayMode;}																			//Mode d'affichage du messenger (cf. $_SESSION["messengerDisplayTimes"])
	if($(".fancybox-iframe").exist() && /edit/i.test($(".fancybox-iframe").attr("src")))  {updateUrl+="&editTypeId="+urlParam("typeId",$(".fancybox-iframe").attr("src"));}	//Vérifie si quelqu'un edite déjà l'objet
	if(typeof tinymce!="undefined"  ||  ($(".fancybox-iframe").exist() && typeof $(".fancybox-iframe")[0].contentWindow.tinymce!="undefined")){								//Vérifie si un éditeur Tinymce est affiché (page principale ou lightbox)
		var editorDraft=(typeof tinymce!="undefined")  ?  editorContent()  :  $(".fancybox-iframe")[0].contentWindow.editorContent();										//Récupère le contenu de l'éditeur
		if(editorDraft && editorDraft.length>0)  {updateUrl+="&editorDraft="+encodeURIComponent(editorDraft);}																//Enregistre dans les brouillons (si pas "undefined"!)
	}

	//// Lance le "MessengerUpdate" (init/update Ajax du livecounter)
	$.ajax({url:updateUrl,dataType:"json"}).done(function(result){
		//// Affiche d'abord les messages du messenger (cf. ".vMessengerOldMessage" suivant)
		if(initMessenger==true || result.messengerUpdate==true)  {$("#messengerMessagesAjax").html(result.messengerMessagesHtml);}

		//// Affiche ensuite les users connectés
		if(initMessenger==true || result.livecounterUpdate==true)
		{
			//Réinit l'affichage
			$("#livecounterMain,#livecounterConnectedLabel,#messengerMultiUsersIcon").hide();
			//Affiche le livecounter principal
			if(result.livecounterMainHtml.length>0){
				$("#livecounterUsers").html(result.livecounterMainHtml);												//Affiche d'abord les users du livecounter principal
				if(!isMobile())  {$("#livecounterConnectedLabel").show();}												//Affiche le label "Connecté:"
				if($(".vLivecounterUser").length>=2)  {$("#messengerMultiUsersIcon").show();}							//Affiche l'icone pour discuter à plusieurs (et voir les anciens messages)
				$("#livecounterMain").css("left", ($(window).width()/2)-($("#livecounterMain").outerWidth()/2) ).show();//Affiche enfin le livecounter centré sur la page
			}
			//Update les users du messenger (checkboxes)
			$("#messengerUsersAjax").html("<div class='vMessengerUser'><?= Txt::trad("MESSENGER_chatWith") ?> :</div>"+result.livecounterFormHtml);
		}

		//// Finalise l'affichage
		if(result.messengerUpdate==true || result.livecounterUpdate==true)  {messengerDisplayUser();}				//Affiche uniquement les messages d'un user OU les messages de tous les users
		if(result.livecounterMainHtml.length>0 || result.messengerMessagesHtml.length>0)  {mainPageDisplay(false);}	//Update les tooltips/lightbox (toujours à la fin)
		messengerCheckedUsers=result.messengerCheckedUsers;															//Update la liste des users "checked" (après post d'un message dans une discussion à plusieurs)

		//// Pulsate & alerte sonore des users ayant posté un nouveau message
		if(result.livecounterUsersPulsate.length>0){
			result.livecounterUsersPulsate.forEach(function(idUserTmp){ $("#livecounterUser"+idUserTmp).pulsate(8); });	//Lance le "pulsate" des users concernés (pas + de 10sec : cf. "messengerUpdateInterval")
			messengerAlert.play();																						//Lance l'alerte sonore
		}
	});
}

/*******************************************************************************************
 *	AFFICHE/MASQUE LE MESSENGER   (messengerDisplayModeNew : "none" / "all" / "idUser")
*******************************************************************************************/
function messengerDisplay(messengerDisplayModeNew)
{
	//// MessengerDisplayMode
	messengerDisplayMode=($("#messengerMain").isVisible() && messengerDisplayModeNew==messengerDisplayMode)  ?  "none"  :  messengerDisplayModeNew;	//"none" si on demande le même "messengerDisplayMode", sinon on enregistre le nouveau "messengerDisplayMode"
	if(messengerDisplayMode!="none")  {$.ajax({url:"?ctrl=misc&action=MessengerDisplayTimesUpdate&messengerDisplayMode="+messengerDisplayMode});}	//Messenger affiché : update le timestamp du "messengerDisplayMode" courant

	//// Masque le messenger principal
	if(messengerDisplayMode=="none"){
		$("#messengerMain").fadeOut();											//Masque le messenger!
		$("#headerModuleButtonMessenger").removeClass("vHeaderModuleCurrent");	//cf. VueHeaderMenu.php
		if(isMobile())  {$("body").css("overflow","visible");}					//Réactive le scroll de la page en arriere plan
	}
	//// Affiche le messenger principal
	else
	{
		//// Affichage du messenger en mode mobile / normal
		if(isMobile()){
			menuMobileClose();											//Masque si besoin le menu principal (cf. icone messenger du menu des modules)
			messengerFullPage();										//Messenger en pleine page.
			$(window).on("resize",function(){ messengerFullPage(); });	//Idem si on resize la page (change d'orientation ou affiche le clavier virtuel)
		}else{
			var messengerLeftPos=($(window).width()/2)-($("#messengerMain").outerWidth(true)/2);//Position Left du messenger, pour pouvoir le centrer ("outerWidth(true)" pour prendre en compte le "margin")
			$("#messengerMain").css("left",messengerLeftPos).resizable({handles:"e,w"});		//Centre le messenger && le rend "resizable" en largeur uniquement
		}
	
		//// Affiche le messenger  &&  Check un ou plusieurs users
		$("#messengerMain").show();											//Affiche le messenger!
		$("#headerModuleButtonMessenger").addClass("vHeaderModuleCurrent");	//cf. VueHeaderMenu.php
		$(".messengerUserCheckbox").prop("checked",false);					//Réinit le "check" des users
		if($.isNumeric(messengerDisplayMode))	{$("#messengerUserCheckbox"+messengerDisplayMode).prop("checked",true);}												//User spécifique "checked"
		else if(messengerDisplayMode=="all")	{ messengerCheckedUsers.forEach(function(idUserTmp){ $("#messengerUserCheckbox"+idUserTmp).prop("checked",true); }); }	//Re-check les users déjà sélectionné lors d'une discussion à plusieurs

		//// Affiche l'input Texte et le bouton de visio
		labelUserDisplayed=($.isNumeric(messengerDisplayMode))  ?  $("#livecounterUser"+messengerDisplayMode).text()  :  null;															//Récup le label de l'user du livecounter
		var placeholderLabel=(labelUserDisplayed==null)  ?  "<?= Txt::trad("MESSENGER_addMessageToSelection") ?>"  :  "<?= Txt::trad("MESSENGER_addMessageTo") ?> "+labelUserDisplayed;	//Placeholer de l'input text
		visioButtonLabel=(labelUserDisplayed==null)  ?  "<?= Txt::trad("MESSENGER_visioProposeToSelection") ?>"  :  "<?= Txt::trad("MESSENGER_visioProposeTo") ?> "+labelUserDisplayed;	//Title du bouton de visio (variable globale)
		$("#messengerMessageForm").attr("placeholder",placeholderLabel);						//Placeholer de l'input text : "Mon message à Boby" OU "Mon message aux personnes sélectionnées"
		if(isTouchDevice()==false)  {$("#messengerMessageForm").focus();}						//Affichage normal : focus sur l'input
		$("#launchVisioButton.tooltipstered").tooltipster("destroy");							//Bouton de visio : Réinitialise si besoin le "tooltipster" avant update ci-après
		$("#launchVisioButton").attr("title",visioButtonLabel).tooltipster(tooltipsterOptions);	//Bouton de visio : title "Proposer une visio à Bob" / "Proposer une visio aux personnes sélectionnées"

		//// Divers
		messengerAlert.pause();									//Fin de son d'alerte
		$(".vLivecounterUser").stop(true).css("opacity","1");	//Fin de "pulsate"
		if(isMobile())  {$("body").css("overflow","hidden");}	//Désactive le scroll de la page en arriere plan
	}
	//// Affiche uniquement les messages d'un user OU les messages de tous les users
	messengerDisplayUser();
}

/*******************************************************************************************
 *	AFFICHE UNIQUEMENT LES MESSAGES D'UN USER OU LES MESSAGES DE TOUS LES USERS
*******************************************************************************************/
function messengerDisplayUser()
{
	//Réinit le surlignage d'user dans le livecounter principal
	$(".vLivecounterUser,#messengerMultiUsersIcon").removeClass("vLivecounterUserSelect");
	//Messenger affiché?
	if(messengerDisplayMode!="none")
	{
		//Réinit l'affichage des messages et des users du formulaire
		$(".vMessengerMessage,#messengerUsersCell").hide();
		//Affiche tous les utilisateurs et messages
		if(messengerDisplayMode=="all"){
			$(".vMessengerMessage,#messengerUsersCell").show();					//Affiche tous les messages
			$("#messengerMultiUsersIcon").addClass("vLivecounterUserSelect");	//Surligne dans le livecounter l'icone "#messengerMultiUsersIcon"
		}
		//Affiche uniquement l'user sélectionné
		else if($.isNumeric(messengerDisplayMode)){
			$(".vMessengerMessage[data-idUsers*='@"+messengerDisplayMode+"@']").show();		//Affiche uniquement les messages concernant l'user sélectionné
			$("#livecounterUser"+messengerDisplayMode).addClass("vLivecounterUserSelect");	//Surligne dans le livecounter l'user sélectionné
		}
		//Affiche "Personne n'est connecté"  ||  Affiche le formulaire pour poster un message
		if($(".messengerUserCheckbox").isEmpty())	{$("#messengerNobodyDiv").show();  $("#messengerPostDiv,#messengerUsersCell").hide();}
		else										{$("#messengerNobodyDiv").hide();  $("#messengerPostDiv").show();}
		//Scroll jusqu'aux derniers messages  &&  Pulsate le dernier message s'il s'agit d'une proposition de visio
		$("#messengerMessagesAjax").scrollTop($("#messengerMessagesAjax").prop("scrollHeight"));												
		$(".vMessengerMessage:last-child .launchVisioMessage").pulsate(3);
	}
}

/*******************************************************************************************
 *	MOBILE : RESIZE LE MESSENGER POUR QU'IL PRENNE TOUTE LA PAGE
 *	Tester avec de nombreux messages (scrollBar) + 5 users connectés + changements d'orientation + Tablette
*******************************************************************************************/
function messengerFullPage(){
	//SetTimeout le temps de calculer la hauteur du "#livecounterMain" et éventuellement du clavier virtuel
	setTimeout(function(){
		$("#messengerMain").height( $(window).height() ).width( $(window).width() );	//Le messenger prend toute la page
		$("#messengerBottomMargin").outerHeight( $("#livecounterMain").outerHeight() );	//Hauteur du "#messengerBottomMargin" en fonction du "#livecounterMain"
		$(".vMessengerContent,.vMessengerScroll").height( $(window).height()-$("#messengerMove,#messengerPostAndNobody,#livecounterMain").totalHeight() );//Resize les divs scrollables en fonction de la hauteur disponible
	},200);
}

/*******************************************************************************************
 *	CONTROLE & POST DU MESSAGE DU MESSENGER
*******************************************************************************************/
function messengerPost(event)
{
	//Stop la validation du form
	if(typeof event!="undefined")  {event.preventDefault();}
	//Vérif qu'un message est spécifié  &&  Vérif qu'un user ou + est sélectionné
	if($("#messengerMessageForm").isEmpty())  			{notify("<?= Txt::trad("MESSENGER_addMessageNotif") ?>");  return false;}
	if($(".messengerUserCheckbox:checked").length==0)	{notify("<?= Txt::trad("notifSelectUser") ?>");  return false;}
	// Poste le message via Ajax
	$.ajax({url:"?ctrl=misc&action=messengerPost",data:$("#messengerForm").serialize(),type:"POST"}).done(function(){
		$("#messengerMessageForm").val("");						//Réinit l'input text
		if(!isMobile())  {$("#messengerMessageForm").focus();}	//Focus à nouveau sur l'input
		messengerUpdate();										//Update les messages pour afficher le post
	});
}

/*******************************************************************************************
 *	PROPOSITION DE VISIO : POST UN MESSAGE AVEC LE LIEN DE VISIO
*******************************************************************************************/
function proposeVisio()
{
	//Vérif qu'au moins un user est sélectionné  &&  Confirme la visio (cf. tooltip "visioButtonLabel")
	if($(".messengerUserCheckbox:checked").length==0)  {notify("<?= Txt::trad("notifSelectUser") ?>");  return false;}
	else if(confirm(visioButtonLabel+" ?"))
	{
		visioUsers="<?= Ctrl::$curUser->getLabel("firstName") ?>";														//Init la liste des destinaires avec l'user courant
		visioURL="<?= Ctrl::$agora->visioUrl()."-".Txt::clean(trim(Ctrl::$curUser->getLabel("firstName")),"max") ?>";	//Init l'Url de la visio avec l'user courant
		$(".messengerUserCheckbox:checked").each(function(){															//Label de chaque user sélectionné :
			visioUsers+=" & "+$(this).attr("data-user-label");															//- ajoute dans la liste des destinaires
			visioURL+="-"+$(this).attr("data-user-label-visio");														//- ajoute dans l'url de la visio : incorpore le label de chaque participant dans le "roomId"
		});
		var visioMessage="<?= Txt::trad("MESSENGER_visioProposeToUsers") ?> "+visioUsers+"<img src='app/img/visioSmall.png'>";					//Message "Cliquez ici pour lancer la visioconférence : Will & Boby"
		$("#messengerMessageForm").val("<a href=\"javascript:launchVisio('"+visioURL+"')\" class='launchVisioMessage'>"+visioMessage+"</a>");	//Post le message dans le messenger avec le "launchVisio()" : tester le lien car filtré via "Db::format()" & CO !
		messengerPost();
	}
}
</script>


<style>
/*Principal*/
#messengerMain, #livecounterMain 			{display:none; position:fixed; max-width:100%!important; max-height:100%!important; color:#ddd!important; box-shadow:0px 0px 3px 2px rgba(0,0,0,0.3);}/*"position:fixed" pour que sur moblie, le clavier viruel ne cache pas le formulaire*/
#messengerMain								{z-index:30; bottom:0px!important; background:#111; padding:20px; padding-top:10px; width:850px; min-width:300px; border-radius:5px; border:0px;}
#messengerMain td							{vertical-align:top;}
#messengerBottomMargin						{height:60px;}/*marge du bas du messenger : pour afficher le livecounter ci-dessus qui s'y superpose (cf. "#livecounterMain td" ci-dessus)*/
#messengerNobodyDiv							{position:relative; background:#333; padding:10px; margin:10px; line-height:30px; border-radius:10px;}
#messengerNobodyDiv img[src*=messenger]		{position:absolute; top:-20px; left:-10px;}
#livecounterMain							{z-index:31; bottom:5px!important; background:#333; padding:18px 30px; border-radius:5px;}
#livecounterMain td							{vertical-align:middle;}

/*Livecounter principal : #livecounterMain*/
#livecounterConnectedLabel					{margin-left:10px;}
#livecounterConnectedLabel>img				{margin-left:5px;}
.vLivecounterUser							{padding:10px; margin-left:5px; border:solid 1px transparent;}	/*Label de chaque user : height de 30px (cf. "CtrlMisc::actionMessengerUpdate")*/
.vLivecounterUserSelect						{border:solid 1px #777; border-radius:3px; background:#555;}	/*Label d'un user sélectionné*/
.vLivecounterUser .personImg				{width:30px; height:30px; margin-right:5px;}					/*Image des users (cf. "CtrlMisc::actionMessengerUpdate")*/
#messengerMultiUsersIcon					{padding-left:10px; cursor:pointer;}

/*Messenger : #messengerMain*/
#messengerMove								{height:16px; cursor:move; background-image:url(app/img/dragDrop.png);}
#messengerClose								{position:absolute; top:-10px; right:-10px; cursor:pointer;}
.vMessengerContent, .vMessengerScroll		{height:450px;}		/*Fixe la hauteur du contenu principal (pas + de 500px) : pour éviter que les ".vMessengerScroll" ne puissent agrandir automatiquement le <table> en hauteur (si ya beaucoup de messages à afficher)*/
.vMessengerScroll							{overflow-y:auto;}	/*divs scrollables*/
.vMessengerScroll::-webkit-scrollbar		{background:#333;}	/*couleur de background*/
.vMessengerScroll::-webkit-scrollbar-thumb	{background:#888;}	/*couleur de la barre de scroll*/
#messengerMessagesCell						{background-image:url(app/img/messengerBackground.png); background-repeat:no-repeat; background-position:50% 50%;}
.vMessengerMessage							{width:100%;}
.vMessengerMessage tr:hover					{background:#444;}/*survol d'un message*/
.vMessengerMessage td						{padding:4px; cursor:help; vertical-align:middle;}
.vMessengerMessageDateAutor					{min-width:80px; width:1%; vertical-align:top; color:#888; white-space:nowrap; font-size:0.9em;}/*Heure et label de l'auteur. Width ajusté au contenu via 'nowrap'*/
.vMessengerMessage .personImg				{width:22px; height:22px; margin-left:8px;}/*image des users dans les messages (cf. "CtrlMisc::actionMessengerUpdate")*/
.vMessengerMessage .iconUsersMultiple		{height:15px; margin-bottom:10px; margin-left:2px;}/*Icone de discussion à plusieurs*/
.vMessengerMessage a						{color:#fff;}/*lien des visios*/
#messengerUsersCell							{width:200px;}
#messengerUsersAjax							{background:#333; border-radius:3px;}
.vMessengerUser								{margin:10px;}
.vMessengerUser input						{display:none;}
.vMessengerUser input:checked+label			{color:#f88;}										/*surcharge du 'linkSelect'*/
.vMessengerUser .personImg					{width:24px; height:24px; margin-right:5px;}		/*image des users dans le form de selection (cf. "CtrlMisc::actionMessengerUpdate")*/
#messengerPostAndNobody						{height:40px; padding-top:10px; text-align:center;}
#messengerMessageForm, #messengerButtonForm	{height:40px!important;}
#messengerMessageForm						{width:60%; font-weight:bold; border-radius:3px;}
#messengerButtonForm						{width:140px; margin-bottom:3px;}
#launchVisioButton							{margin-left:10px; cursor:pointer;}/*bouton de proposition de visio*/
.launchVisioMessage img[src*='visioSmall']	{margin-left:10px;}

/*MOBILE*/
@media screen and (max-width:1023px){
	#livecounterMain						{padding:10px; bottom:-5px!important; font-size:1.1em;}
	#livecounterConnectedLabel				{display:none;}						/*masque le "Connecté :"*/
	.vLivecounterUser						{display:inline-flex;}				/*tester l'affichage sur mobile avec 10 personnes (cf. 'display:inline-flex')*/
	.vLivecounterUser .personImg			{display:none;}						/*Image des users (cf. "CtrlMisc::actionMessengerUpdate")*/
	#messengerMain							{border-radius:0px; padding:0px;}	
	#messengerMove							{background-image:none;}			/*masque "dragDrop.png"*/
	#messengerClose							{top:12px; right:2px;}				/*repositionne le "close"*/
	.vMessengerMessage td					{padding:2px;}
	.vMessengerMessageDateAutor				{min-width:60px; max-width:100px;}	/*Width toujours ajusté mais 100px maxi*/
	#messengerUsersCell						{width:120px;}
	.vMessengerUser							{margin:5px;}
	.vMessengerUser:first-child				{margin-top:20px;}					/*"Discuter avec" est décalé pour pouvoir afficher l'icone "close"*/
	#messengerButtonForm					{width:80px;}
	#messengerButtonForm img				{display:none;}
	.vMessengerScroll::-webkit-scrollbar	{width:5px;}
}
</style>

<!--LIVECOUNTER PRINCIPAL : LISTE DES USERS CONNECTES-->
<table id="livecounterMain">
	<tr>
		<td class="cursorHelp" title="<?= Txt::trad("MESSENGER_messengerTitle") ?>"><img src="app/img/messenger.png"><span id="livecounterConnectedLabel"><?= Txt::trad("MESSENGER_connected") ?><img src="app/img/arrowRight.png"></span></td>
		<td id="livecounterUsers"></td>
		<td id="messengerMultiUsersIcon" onclick="messengerDisplay('all');" title="<?= Txt::trad("MESSENGER_messengerMultiUsers") ?>"><img src="app/img/user/iconSmall.png"></td>
	</tr>
</table>

<!--MESSENGER : LISTE DES MESSAGES & FORMULAIRE DE POST DE MESSAGE-->
<form id="messengerForm">
	<table id="messengerMain">
		<tr>
			<td id="messengerMove" colspan="2"><img src="app/img/closeMessenger.png" id="messengerClose" onclick="messengerDisplay('none');" title="<?= Txt::trad("close") ?>"></td>
		</tr>
		<tr>
			<td id="messengerMessagesCell" class="vMessengerContent"><div id="messengerMessagesAjax" class="vMessengerScroll">&nbsp;</div></td>	<!--LISTE DES MESSAGES-->
			<td id="messengerUsersCell" class="vMessengerContent"><div id="messengerUsersAjax" class="vMessengerScroll">&nbsp;</div></td>		<!--SELECTION D'USERS ("hidden" par défaut)-->
		</tr>
		<tr>
			<td id="messengerPostAndNobody" colspan="2">
				<div id="messengerPostDiv">
					<input type="text" name="message" id="messengerMessageForm" maxlength="1000">
					<button id="messengerButtonForm" onclick="messengerPost(event);"><img src="app/img/postMessage.png"> <?= Txt::trad("send") ?></button>
					<?php if(Ctrl::$agora->visioEnabled()){ ?><img src="app/img/visio.png" id="launchVisioButton" onclick="proposeVisio()"><?php } ?>
				</div>
				<div id="messengerNobodyDiv"><img src="app/img/messenger.png"> <?= Txt::trad("MESSENGER_nobody") ?></div>
			</td>
		</tr>
		<tr><td id="messengerBottomMargin" colspan="2">&nbsp;</td></tr>
	</table>
</form>