<script>	
////	INIT
$(function(){
	////	Initialise les variables globales
	if(typeof livecounterLoadTime=="undefined"){
		livecounterLoadTime=Date.now();//Time du premier "livecounterUpdate()" 
		idUserDisplayed="none";//Affichage courant : "none" / "all" / "idUser"
		visioButtonLabel=null;
		messengerAlert=new Audio("app/misc/messengerAlert.mp3");//Charge le son d'alerte
		messengerAlert.volume=0.7;//volume à 70%
		messengerAlert.loop=false;//Pas de son en boucle
	}
	////	Init le Drag/Drop du messenger
	if(isMobile()==false)  {$("#messengerMain").draggable({handle:"#messengerMove",opacity:0.9});}
	////	Récupère enfin les livecounters et messages !
	livecounterUpdate(true);
});

////	Update le Livecounter et le Messenger
function livecounterUpdate(initLivecounter)
{
	//// Url du "LivecounterUpdate"
	var livecounterUpdateUrl="?ctrl=misc&action=LivecounterUpdate";
	// Vérif si un autre user edite en meme temps le meme element (édition lightbox) : params "editObjId"
	if($(".fancybox-iframe").exist() && find("edit",$(".fancybox-iframe").attr("src")))  {livecounterUpdateUrl+="&editObjId="+getUrlParam("targetObjId",$(".fancybox-iframe").attr("src"));}
	// On édite un texte via Tinymce ("confirmCloseForm" && editeur en page principale || editeur d'une lightbox) : on enregistre le texte en tant que brouillon
	if(confirmCloseForm==true  && (typeof tinymce!="undefined"  ||  ($(".fancybox-iframe").exist() && typeof $(".fancybox-iframe")[0].contentWindow.tinymce!="undefined"))){
		var editorDraft=(typeof tinymce!=="undefined")  ?  tinymce.activeEditor.getContent()  :  $(".fancybox-iframe")[0].contentWindow.tinymce.activeEditor.getContent();
		livecounterUpdateUrl+="&editorDraft="+encodeURIComponent(editorDraft);
	}

	//// Lance l'update ajax du livecounter
	$.ajax({url:livecounterUpdateUrl,dataType:"json"}).done(function(result){
		//// Init/Update les livecounters
		if(initLivecounter==true || result.livercounterUpdate==true)
		{
			//Liste des users connectées  ||  Plus personne de connecté  ||  Rien à afficher
			if(result.livecounterUsersHtml.length>0)		{$("#livecounterMain").fadeIn();  $("#livecounterUsers").html(result.livecounterUsersHtml);}
			else if(result.messengerMessagesHtml.length>0)	{$("#livecounterMain").fadeIn();  $("#livecounterUsers").html("<span onclick=\"messengerDisplay('all')\" class='sLink' title=\"<?= Txt::trad("MESSENGER_connectedNobodyInfo") ?>\">&nbsp; ....</span>");}
			else											{$("#livecounterMain").hide();}
			//Centre le livecounter sur la page (Ne pas mettre dans container centré : sinon empeche le click de l'icone principal du bas)
			$("#livecounterMain").css("left", ($(window).width()/2)-($("#livecounterMain").outerWidth()/2) );
			//Affiche le label "Connecté" si on est pas sur mobile et qu'on a des users connectés
			(!isMobile() && result.livecounterUsersHtml.length>0)  ?  $("#livecounterLabelConnected").show()  :  $("#livecounterLabelConnected").hide();
			//Update les users du messenger
			var oldUsersChecked=messengerUsersChecked();//Retient la liste des users sélectionnés
			$("#messengerUsersAjax").html(result.messengerUsersHtml);//Affiche les nouveaux users
			for(var tmpIdUser in oldUsersChecked)  {$("#messengerUsers"+oldUsersChecked[tmpIdUser]).prop("checked",true);}//Réaffecte les users précédemment sélectionnés
		}
		//// Init/Update les messages
		if(initLivecounter==true || result.messengerUpdate==true)  {$("#messengerMessagesAjax").html(result.messengerMessagesHtml);}
		//// Update les livecounters ou les messages :  Affiche le label et messages de l'user sélectionné
		if(result.livercounterUpdate==true || result.messengerUpdate==true)  {messengerDisplayUser();}
		//// Update les tooltips/lightbox (toujours)
		if(result.livecounterUsersHtml.length>0 || result.messengerMessagesHtml.length>0)  {mainPageDisplay(false);}

		//// Pulsate d'users si ya de nouveaux messages à afficher (Pulsate et Alerte sonore relancé à chaque "livecounterUpdate" si besoin)
		if(result.messengerPulsateUsers.length>0)
		{
			//Fait clignoter l'auteur d'un nouveau message
			var isMessengerAlert=false;
			for(var tmpKey in result.messengerPulsateUsers){
				isMessengerAlert=true;
				var idUserTmp=result.messengerPulsateUsers[tmpKey];
				if($("#messengerMain").is(":visible") && idUserDisplayed==idUserTmp)	{pulsateTimes=5;  messengerUserDisplayTime(idUserTmp);}	//"Pulsate" 5 fois l'user affiché & Update Ajax du "time" de l'user affiché
				else																	{pulsateTimes=50;}										//"Pulsate" 50 fois l'user masqué
				$(".vLivecounterUser[data-idUser='"+idUserTmp+"']").stop(true,true).pulsate(pulsateTimes);//Lance/Relance le pulsate
			}
			//Alerte sonore si ya de nouveaux messages
			var mobileHiddenPage=(isMobile() && document.visibilityState=="hidden");//Mobile : vérif que la page est toujours affichée, pour pas avoir de son en tache de fond (bloque juste les alertes sonores!)
			if(isMessengerAlert==true && mobileHiddenPage==false)  {setTimeout(function(){messengerAlert.play();},2000);}//Timeout pour éviter le blocage de lecture auto du navigateur (cf. "play() failed")
		}

		//// Relance "livecounterUpdate()" avec Timeout
		if(document.visibilityState!="hidden" || (Date.now()-livecounterLoadTime)<1200000){	//La page n'est pas affichée depuis au moins 20mn : stop le "livecounterUpdate()" et évite les requetes Ajax inutiles
			if(typeof livecounterTimeout!="undefined")  {clearTimeout(livecounterTimeout);}	//Pas de cumul de Timeout (important!)
			livecounterTimeout=setTimeout(function(){ livecounterUpdate(); },10000);		//Relance le "livecounterUpdate()" après 10 secondes
		}
	});
}

////	Affiche/Masque le messenger
function messengerDisplay(idUserDisplayedRequest)
{
	////	Masque le messenger de l'user s'il est déjà affiché  OU  Enregistre l'affichage courant
	if($("#messengerMain").is(":visible") && idUserDisplayedRequest==idUserDisplayed)	{idUserDisplayed="none";}
	else																				{idUserDisplayed=idUserDisplayedRequest;}
	// Update Ajax du "time" de l'user affiché
	if($.isNumeric(idUserDisplayed))  {messengerUserDisplayTime(idUserDisplayed);}

	//// Masque le messenger principal
	if(idUserDisplayed=="none"){
		$("#messengerMain").fadeOut();//Masque le messenger
		$("body").css("overflow","visible");//réactive le scroll de page en arriere plan
	}
	//// Affiche le messenger principal
	else
	{
		//// Affichage mobile
		if(isMobile()){
			$("#messengerMain").outerHeight($(window).height()).outerWidth($(window).width());	//Affiche le messenger sur toute la hauteur/largeur de la page
			$(window).on("resize",function(){													//Affichage du clavier du mobile (resize de la fenêtre) : Redimensionne le "#messengerMain" et les ".vMessengerAjax" scrollables
				if($("#messengerMain").is(":visible")){  $("#messengerMain").outerHeight($(window).height());  messengerResizeScrollers();  }
			});
		}
		//// Affichage normal
		else{
			$("#messengerMain").css("left", ($(window).width()/2)-($("#messengerMain").outerWidth()/2) ).resizable({handles:"n,e,s,w"});//Centre le messenger (via position 'left') && Init le "resize" du messenger (4 cotés)
			$("#messengerMain").resize(function(){ messengerResizeScrollers();});														//Redimensionne les ".vMessengerAjax" scrollables
		}

		//// Affiche le messenger !
		$("#messengerMain").css("padding-bottom",($("#livecounterMain").outerHeight(true)+15));//Padding bottom du "#messengerMain" en fonction de "#livecounterMain" (lorsqu'il est affiché, le messenger "enveloppe" le livecounter principal)
		messengerResizeScrollers();//Init ensuite les ".vMessengerAjax" scrollables
		$("#messengerMain").fadeIn(200);

		//// Désélectionne tous les users, puis sélectionne au besoin l'user à afficher
		$("input[name='messengerUsers']").prop("checked",false);
		if($.isNumeric(idUserDisplayed))  {$("#messengerUsers"+idUserDisplayed).prop("checked",true);}

		//// Affiche le formulaire, l'input Text, l'appel visio
		labelUserDisplayed=($.isNumeric(idUserDisplayed))  ?  $(".vLivecounterUser[data-idUser='"+idUserDisplayed+"']").text()  :  null;
		var placeholderLabel=(labelUserDisplayed==null)  ?  "<?= Txt::trad("MESSENGER_addMessageToSelection") ?>"  :  "<?= Txt::trad("MESSENGER_addMessageTo") ?> "+labelUserDisplayed;
		visioButtonLabel=(labelUserDisplayed==null)  ?  "<?= Txt::trad("MESSENGER_visioProposeToSelection") ?>"  :  "<?= Txt::trad("MESSENGER_visioProposeTo") ?> "+labelUserDisplayed;
		$("#messengerPostMessage").attr("placeholder",placeholderLabel);//Placeholer : "Mon message à Boby" OU "Mon message aux personnes sélectionnées"
		if(isTouchDevice()==false)  {$("#messengerPostMessage").focus();}//Affichage normal : focus sur l'input
		$("#visioLauncherButton").attr("title",visioButtonLabel).tooltipster(tooltipsterOptions);//Title du bouton de visio : "Proposer une visio à Boby" OU "Proposer une visio aux personnes sélectionnées"

		//// Divers
		messengerAlert.pause();									//Fin de son d'alerte
		$(".vLivecounterUser").stop(true).css("opacity","1");	//Fin de "pulsate"
		$("body").css("overflow","hidden");						//Désactive le scroll de page en arriere plan
	}
	//// Affiche le label et messages de l'user sélectionné
	messengerDisplayUser();
}

////	Affiche le label et messages de l'user sélectionné (ou de tous les users)
function messengerDisplayUser()
{
	//Réinit les users du livecounter principal (meme si le messenger est masqué)
	$(".vLivecounterUser[data-idUser]").removeClass("vLivecounterUserSelect");
	//Messenger affiché?
	if(idUserDisplayed!="none")
	{
		//Affiche tous les messages et utilisateurs  OU  Affiche uniquement les messages de l'user sélectionné
		if(idUserDisplayed=="all")  {$(".vMessengerMessage,#messengerUsersCell").show();}
		else if($.isNumeric(idUserDisplayed)){
			$(".vMessengerMessage,#messengerUsersCell").hide();//Masque les messages et la liste des users
			$(".vMessengerMessage[data-idUsers*='@"+idUserDisplayed+"@']").show();//Affiche uniquement les messages de l'user sélectionné
			$(".vLivecounterUser[data-idUser='"+idUserDisplayed+"']").addClass("vLivecounterUserSelect");//Livecounter : sélectionne l'user
		}
		//Scroll jusqu'aux derniers messages
		$("#messengerMessagesAjax").scrollTop($("#messengerMessagesAjax").prop("scrollHeight"));
		//Affiche/masque le formulaire en fonction du livecounter
		if($("input[name='messengerUsers']").isEmpty())	{$("#messengerPostForm,#messengerUsersCell").hide();}
		else											{$("#messengerPostForm").show();}
		//Pulsate si ya des proposition de visio
		$(".launchVisioMessage").last().pulsate(5);
	}
}

////	Controle & post du message du messenger (note: pb de scroll sous chrome apres lancement de "messengerPost()")
function messengerPost()
{
	//Vérif du message et user spécifié
	if($("#messengerPostMessage").isEmpty())  {notify("<?= Txt::trad("MESSENGER_addMessageNotif") ?>");  return false;}
	var checkedUsers=messengerUsersChecked();
	if(checkedUsers.length==0)  {notify("<?= Txt::trad("selectUser") ?>");  return false;}
	// On poste le message
	$.ajax({url:"?ctrl=misc&action=MessengerPostMessage", data:{message:$("#messengerPostMessage").val(),messengerUsers:checkedUsers}, type:"POST"}).done(function(){
		$("#messengerPostMessage").val("");//Réinit le formulaire
		if(!isMobile())  {$("#messengerPostMessage").focus();}
		livecounterUpdate();//Relance l'affichage des messages
	});
}


////	Click sur le bouton de proposition de visio
function initiateVisio()
{
	//Confirme la proposition de visio (récupère le "title")
	if(confirm(visioButtonLabel+" ?")){
		//Envoie un message au destinataire ("Boby propose un appel visio")
		var roomUrl="<?= CtrlMisc::myVideoRoomURL() ?>";
		$("#messengerPostMessage").val("<a href=\"javascript:launchVisio('"+roomUrl+"')\" class='launchVisioMessage'><?= Ctrl::$curUser->getLabel()." ".Txt::trad("MESSENGER_userProposeVisioCall") ?> <img src='app/img/visio.png'></a>");
		messengerPost();
		// Envoi d'une notification ("la proposition a bien été envoyée...") PUIS Lance la visio (avec timeout, le temps de lire le message)
		notify("<?= Txt::trad("MESSENGER_visioProposalPending") ?>","success");
		setTimeout(function(){ launchVisio(roomUrl,true); },12000);
	}
}

////	Lance une visio dans un nouvel onglet/iframe
function launchVisio(roomUrl,initiate)
{
	if(initiate===true || confirm("<?= Txt::trad("MESSENGER_visioProposalLanch") ?>"))
		{window.open(roomUrl);}
}

////	Update Ajax du "time" de l'affichage de l'user
function messengerUserDisplayTime(_idUser){
	$.ajax({url:"?ctrl=misc&action=messengerUserDisplayTime&_idUser="+_idUser});
}
////	Redimensionne les div "vMessengerAjax" scrollables en fonction du "#messengerMain"
function messengerResizeScrollers(){
	$(".vMessengerAjax").hide().height(  $("#messengerMain").height()-$("#messengerMove").outerHeight(true)-$("#messengerPostForm").outerHeight(true)  ).show();//"hide()" pour pas fausser la hauteur du "#messengerMain" lors de son "resize()"
}
////	Users sélectionnés : retourne un tableau
function messengerUsersChecked(){
	return $("input[name='messengerUsers']:checked").map(function(){ return $(this).val(); }).get();
}
</script>

<style>
/*Livecounter principal et Messenger*/
#livecounterMain, #messengerMain			{display:none; bottom:0px; position:fixed; max-width:100%!important; color:#ddd!important; box-shadow:0px 0px 3px 2px rgba(0,0,0,0.3);}
#livecounterMain							{z-index:51; background:#333; min-height:50px; padding:10px 20px 10px 20px; border-radius:5px 5px 0px 0px;}
#messengerMain								{z-index:50; background:#111; min-height:300px; min-width:300px; height:550px; width:650px; padding:20px; padding-top:10px; vertical-align:top; border-radius:5px; border:0px;}

/*Contenu du Livecounter*/
.vLivecounterUser							{padding:7px; margin-left:10px; border:solid 1px transparent;}/*Label de chaque user (cf. "CtrlMisc::actionLivecounterUpdate")*/
.vLivecounterUserSelect						{border:solid 1px #777; border-radius:3px; background:#555;}
.vLivecounterUser .personImg				{width:30px; height:30px; margin-right:5px;}/*image des users (cf. "CtrlMisc::actionLivecounterUpdate")*/

/*Contenu du Messenger*/
#messengerMain .personImg					{width:22px; height:22px; margin-left:7px;}/*image des users dans les messages et le formulaire de sélection (cf. "CtrlMisc::actionLivecounterUpdate")*/
#messengerMove								{height:16px; cursor:move; background-image:url(app/img/dragDrop.png);}
#messengerClose								{position:absolute; top:-10px; right:-10px; cursor:pointer;}
#messengerMessagesCell						{background-image:url(app/img/messengerBackground.png); background-repeat:no-repeat; background-position:50% 50%;}
#messengerUsersCell							{width:200px;}
.vMessengerAjax								{overflow-y:auto;}/*"messengerMessagesAjax" et "messengerUsersAjax"*/
.vMessengerAjax::-webkit-scrollbar			{background:#333; width:15px;}/*scrollbar: background et width*/
.vMessengerAjax::-webkit-scrollbar-thumb	{background:#888;}/*scrollbar: couleur de la barre*/
.vMessengerMessage tr:hover					{background:#444;}/*survol d'un message*/
.vMessengerMessage td						{padding:4px; cursor:help; vertical-align:middle;}
.vMessengerMessage td:first-child			{min-width:70px; color:#888;}/*heure et auteur du message*/
.vMessengerMessage td a						{color:#fff;}
#messengerUsersAjax							{background:#555; border-radius:3px;}
.vMessengerUser								{margin:10px;}
#checkUserAll								{margin-top:20px;}
#messengerPostForm							{height:40px; padding-top:10px; text-align:center;}
#messengerPostMessage, #messengerPostButton	{height:35px;}
#messengerPostMessage						{width:60%; font-weight:bold; border-radius:3px;}
#messengerPostButton						{width:25%; margin-bottom:2px;}
#visioLauncherButton						{margin-left:10px; cursor:pointer;}

/*RESPONSIVE*/
@media screen and (max-width:1023px){
	#livecounterLabelConnected			{display:none;}/*masque "Connecté :"*/
	.vLivecounterUser					{display:inline-flex;}/*Tester l'affichage avec 10 personnes et en responsive (cf. 'display:inline-flex')*/
	.vLivecounterUser .personImg		{display:none;}
	#messengerMain						{bottom:0px; border-radius:0px; padding:0px;}/*Mettre "bottom:0" pour que le clavier viruel ne cache pas le formulaire!*/
	#messengerMove						{background-image:none;}/*masque "dragDrop.png"*/
	#messengerClose						{top:12px; right:2px;}
	.vMessengerAjax::-webkit-scrollbar	{width:5px;}/*normalement : réduite par défaut par le browser*/
	.vMessengerMessage td				{padding:2px;}
	#messengerUsersCell					{width:120px;}
	.vMessengerUser input				{display:none;}
}
</style>


<!--LIVECOUNTER PRINCIPAL-->
<div id="livecounterMain">
	<span id="livecounterTitle" onclick="messengerDisplay('all');" title="<?= Txt::trad("MESSENGER_messenger")." : ".Txt::trad("MESSENGER_messengerInfo") ?>">
		<img src="app/img/messenger.png" class="sLink">
		<label id="livecounterLabelConnected"><?= Txt::trad("MESSENGER_connected") ?> &nbsp;<img src="app/img/arrowRight.png"></label>
	</span>
	<span id="livecounterUsers"></span>
</div>

<!--MESSENGER & LIVECOUNTER SECONDAIRE (utiliser une <table>)-->
<table id="messengerMain">
	<tr>
		<td id="messengerMove" colspan="2"><img src="app/img/close.png" id="messengerClose" onclick="messengerDisplay('none');" title="<?= Txt::trad("close") ?>"></td>
	</tr>
	<tr>
		<td id="messengerMessagesCell"><div id="messengerMessagesAjax" class="vMessengerAjax">&nbsp;</div></td><!--LISTE DES MESSAGES-->
		<td id="messengerUsersCell"><div id="messengerUsersAjax" class="vMessengerAjax">&nbsp;</div></td><!--SELECTION D'USERS ("hidden" par défaut)-->
	</tr>
	<tr>
		<td id="messengerPostForm" colspan="2">
			<input type="text" name="message" id="messengerPostMessage" maxlength="1000" onkeyup="if(event.keyCode==13){messengerPost();}">
			<button id="messengerPostButton" onclick="messengerPost();"><img src="app/img/postMessage.png"> <?= Txt::trad("send") ?></button>
			<?php if(Ctrl::$agora->jitsiEnabled()){ ?>
			<img src="app/img/visio.png" id="visioLauncherButton" onclick="initiateVisio()">
			<?php } ?>
		</td>
	</tr>
</table>
