<script>	
////	Affiche les livecounters (footer & messenger)
$(function(){
	//Lance le livecounter
	livecounterUpdate(true);
	//Resize le messenger : MAJ la hauteur de "messengerContent" en fonction de "messengerContainer" (cf. "overflow" scroll)
	$("#messengerContainer").resize(function(){  $("#messengerContent,#messengerContent>div>div").outerHeight( $("#messengerContainer").height()-$("#messengerPostForm").outerHeight(true));  });
});

////	Update les livecounters (principal/messenger) && Messages du messenger
function livecounterUpdate(livecounterInit)
{
	////	INIT SI BESOIN LE SON D'ALERTE
	if(typeof messengerAlert=="undefined"){
		messengerAlert=new Audio("app/misc/messengerAlert.mp3");
		messengerAlert.loop=false;
	}
	////	INIT LE "LivecounterUpdate" (LARGEUR DE PAGE: ASCENSEUR COMPRIS)
	var livecounterUpdateUrl="?ctrl=misc&action=LivecounterUpdate&windowWidth="+$(window).outerWidth(true);
	////	AJOUTE LE PARAMS "editObjId" POUR VÉRIFIER SI UN AUTRE USER EDITE EN MEME TEMPS LE MEME ELEMENT
	if($(".fancybox-iframe").exist() && find("edit",$(".fancybox-iframe").prop("src")))   {livecounterUpdateUrl+="&editObjId="+getUrlParam("targetObjId",$(".fancybox-iframe").prop("src"));}
	////	Si ya edition d'un texte via TinyMce (module mail OU édition dans une lightbox) : on l'enregistre automatiquement en tant que brouillon/draft
	if(typeof tinymce!=="undefined"  ||  ($(".fancybox-iframe").exist() && typeof $(".fancybox-iframe")[0].contentWindow.tinymce!=="undefined")){
		var editorDraft=(typeof tinymce!=="undefined")  ?  tinymce.activeEditor.getContent()  :  $(".fancybox-iframe")[0].contentWindow.tinymce.activeEditor.getContent();
		if(confirmCloseForm==true)  {livecounterUpdateUrl+="&editorDraft="+encodeURIComponent(editorDraft);}//Enregistre le texte s'il y a une édition en cours (cf. "confirmCloseForm")
	}

	////	LANCE LA REQUETE AJAX D'UPDATE DU LIVECOUNTER
	$.ajax({url:livecounterUpdateUrl,dataType:"json"}).done(function(result){
		////	LIVECOUNTER MODIFIÉ
		if(result.livercounterChanged==true || livecounterInit==true)
		{
			//Livecounter principal (masque uniquement si aucun user connecté et aucun message à afficher)
			if(result.livecounterMainHtml.length==0 && result.messengerMessagesHtml.length==0)  {$("#livecounterMain").hide();  $("#pageFooterHtml").show();}
			else{
				//Affiche la liste des personnes connectées  OU  "Plus personne n'est connecté. Afficher la liste des messages"
				if(result.livecounterMainHtml.length>0)	{$("#livecounterMainTitle").show();  $("#livecounterMainUsers").html(result.livecounterMainHtml);}
				else									{$("#livecounterMainTitle").hide();  $("#livecounterMainUsers").html("<span onclick=\"messengerDisplay('history')\" style=\"cursor:pointer;\"><?= Txt::trad("MESSENGER_connectedNobody") ?></span>");}
				$("#pageFooterHtml").hide();
				$("#livecounterMain").show();
			}
			//Livecounter du messenger  (retient les users dejà sélectionnés, puis les réaffecte après affichage du nouveau livecounter)
			var oldCheckedUsers=messengerUsersChecked();
			$("#messengerUsersAjax").html(result.livecounterMessengerHtml);
			for(var tmpKey in oldCheckedUsers)	{$("#messengerUserBox"+oldCheckedUsers[tmpKey]).prop("checked",true);}
		}
		////	NOUVEAU MESSAGE A AFFICHER
		if(result.messengerChanged==true || livecounterInit==true)
		{
			$("#messengerMessagesAjax").html(result.messengerMessagesHtml);
			if(result.livercounterChanged==true)  {mainPageDisplay(false);}//Nouveaux messages : relance l'affichage (tooltips and co)
			if(typeof messengerDisplayLastIdUser=="undefined")  {messengerDisplayLastIdUser=null;}
			messengerUserStyle(messengerDisplayLastIdUser);
			scrollToLastMessages();
		}
		////	PULSATE D'USERS SI YA DE NOUVEAUX MESSAGES A AFFICHER (Pulsate et Alerte sonore relancé à chaque "livecounterUpdate" si besoin)
		if(result.messengerPulsateUsers.length>0)
		{
			//Fait clignoter l'auteur d'un nouveau message (s'il est encore connecté..)
			var isMessengerAlert=false;
			for(var tmpKey in result.messengerPulsateUsers)
			{
				var pulsateIdUser=result.messengerPulsateUsers[tmpKey];
				if($("#livecounterMainUsers label[data-idUser='"+pulsateIdUser+"']").is(":visible")){
					isMessengerAlert=true;
					if(messengerIsVisible(pulsateIdUser))	{pulsateTimes=3;	messengerUpdateDisplayedUser(pulsateIdUser);}//pulsate court & MAJ Ajax du "messengerDisplayTime"
					else									{pulsateTimes=40;}//pulsate long
					$("#livecounterMainUsers label[data-idUser='"+pulsateIdUser+"']").stop(true).css("opacity","1").effect("pulsate",{times:pulsateTimes},Math.round(pulsateTimes*800));//Lance/Relance le pulsate
				}
			}
			//Alerte sonore si ya de nouveaux messages (X2)
			if(isMessengerAlert==true && livecounterUpdateEnabled()){
				messengerAlert.play();
				setTimeout(function(){ messengerAlert.play(); },1000);
			}
		}
		////	RELANCE APRÈS X SECONDES
		if(livecounterUpdateEnabled()){
			if(typeof livecounterUpdateTimeout!="undefined")  {clearTimeout(livecounterUpdateTimeout);}//Annule le dernier "setTimeout" (pas de cumul)
			livecounterUpdateTimeout=setTimeout(function(){ livecounterUpdate(); },<?= LIVECOUNTER_REFRESH*1000 ?>);
		}
	});
}

////	Update le livecounter si la page est affichée ou a été affichée dans les 10 dernières minutes (pas moins!!). Evite de faire des requetes Ajax indéfiniment en tache de fond si la page n'est plus affichée..
function livecounterUpdateEnabled()
{
	if(typeof livecounterUpdateTime=="undefined" || document.visibilityState!="hidden" || find("firefox",navigator.userAgent))  {livecounterUpdateTime=Date.now();}//init ou update le timestamp
	return ((Date.now()-livecounterUpdateTime)<600000);
}

////	Affichage / masque le messenger (_idUserOrMode => "_idUser" specifique / "all" / "history" / "close")
function messengerDisplay(_idUserOrMode)
{
	//Masque le messenger principal : fermeture demandé / messenger deja affiché / affichage "all" en mode mobile
	if(_idUserOrMode=="close" || messengerIsVisible(_idUserOrMode) || (isMobile() && _idUserOrMode=="all")){
		$("#messengerContainer").hide();
		$("body").css("overflow","visible");//réactive le scroll de page en arriere plan
	}
	//Affiche le messenger principal
	else
	{
		//Reinit la sélection  &&  sélectionne un user spécifique?
		$("[id^='messengerUserBox']").prop("checked",false);
		if($.isNumeric(_idUserOrMode) && $("#messengerUserBox"+_idUserOrMode).exist())  {$("#messengerUserBox"+_idUserOrMode).prop("checked",true);}
		// Affichage mobile (toute hauteur/largeur)  ||  Affichage normal (Redimensionnable des 4 cotés) 
		if(isMobile())	{$("#messengerContainer").outerHeight(Math.round($(window).height()-$("#livecounterMain").height())).outerWidth($(window).width()).trigger("resize");}
		else			{$("#messengerContainer").resizable({handles:"n,e,s,w"});}
		//Messenger : Positions left/bottom  &&  Affichage final  &&  Trigger "Resize"
		var newLeft  =(isMobile())  ?  0  :  Math.round(($(window).width()/2) - ($("#messengerContainer").outerWidth(true)/2));
		var newBottom=(isMobile())  ?  $("#livecounterMain").outerHeight()  :    $("#livecounterMain").outerHeight()+5;
		$("#messengerContainer").css("left",newLeft).css("bottom",newBottom).show().trigger("resize");
		//Divers : après affichage!
		messengerDisplayLastIdUser=_idUserOrMode;						//Update le dernier user affiché
		scrollToLastMessages();											//Scroll jusqu'aux derniers messages
		messengerAlert.pause();											//Fin de son d'alerte
		$("#livecounterMainUsers label").stop(true).css("opacity","1");	//Fin de "pulsate"
		messengerUpdateDisplayedUser(_idUserOrMode);					//MAJ Ajax du "time" de l'user affiché
		//Placeholer de l'input  &&  focus?
		if($.isNumeric(_idUserOrMode))	{var placeholderText="<?= Txt::trad("MESSENGER_addMessageTo") ?> "+$("#livecounterMainUsers label[data-idUser='"+_idUserOrMode+"']").text();}
		else							{var placeholderText="<?= Txt::trad("MESSENGER_addMessageToSelection") ?>";}
		$("#messengerPostMessage").attr("placeholder",placeholderText);
		if(!isMobile())  {$("#messengerPostMessage").focus();}
		//Mode "history" : masque l'input et la sélection des users
		if(_idUserOrMode=="history"){
			$("#messengerPostForm").hide();
		}
		//Désactive le scroll de page en arriere plan
		$("body").css("overflow","hidden");
	}
	//Style du messenger
	messengerUserStyle(_idUserOrMode);
}

////	Style du messenger et des livecounters : Affiche les messages de tous les users OU les messages d'un user et le surligne dans le livecounter
function messengerUserStyle(_idUserOrMode)
{
	//Réinit : masque par défaut la sélection d'users et de tous les messages  &&  Enlève la sélection d'un user dans le livecounter principal
	$("#messengerUsersAjaxDiv,.vMessengerMessage").hide();
	$("#livecounterMainUsers label[data-idUser]").removeClass("vLivecounterMainUsersLabelSelect");
	//Affichage "all"  : affiche la liste des users (checkboxes) et de tous les messages
	if(_idUserOrMode=="all")  {$("#messengerUsersAjaxDiv,.vMessengerMessage").show();}
	//Affichage "history" : affiche uniquement les messages de tous les users
	else if(_idUserOrMode=="history")  {$(".vMessengerMessage").show();}
	//User spécifique
	else if($.isNumeric(_idUserOrMode) && messengerIsVisible(_idUserOrMode)){
		$(".vMessengerMessage[data-idUsers*='@"+_idUserOrMode+"@']").show();//Affiche les messages de l'user
		$("#livecounterMainUsers label[data-idUser='"+_idUserOrMode+"']").addClass("vLivecounterMainUsersLabelSelect");//Surligne l'user dans le "livecounterMain"
	}
}

////	MAJ Ajax du "time" de l'user affiché
function messengerUpdateDisplayedUser(_idUser)
{
	$.ajax({url:"?ctrl=misc&action=MessengerUpdateDisplayedUser&_idUser="+_idUser});
}

////	Verif si le messenger est affiché
function messengerIsVisible(_idUser)
{
	return ($("#messengerContainer").is(":visible") && _idUser==messengerDisplayLastIdUser);
}

////	Affiche les derniers messages (en bas de "messengerMessagesAjax")
function scrollToLastMessages()
{
	setTimeout(function(){ $("#messengerMessagesAjax").scrollTop($("#messengerMessagesAjax").prop("scrollHeight")); },200);
}

////	Users sélectionnés : renvoi un tableau
function messengerUsersChecked()
{
	return $("[name='messengerPostUsers']:checked").map(function(){ return $(this).val(); }).get();
}

////	Controle & post du message du messenger (note: pb de scroll sous chrome apres lancement de "messengerPost()")
function messengerPost()
{
	//Vérif du message et user spécifié
	if($("#messengerPostMessage").isEmpty())  {notify("<?= Txt::trad("MESSENGER_addMessageNotif") ?>");  return false;}
	var checkedUsers=messengerUsersChecked();
	if(checkedUsers.length==0)  {notify("<?= Txt::trad("selectUser") ?>");  return false;}
	// On poste le message, relance l'affichage des messages et récupère les messages
	$.ajax({url:"?ctrl=misc&action=MessengerPostMessage", data:{message:$("#messengerPostMessage").val(),messengerPostUsers:checkedUsers}, type:"POST"}).done(function(){
		$("#messengerPostMessage").val("");
		if(!isMobile())  {$("#messengerPostMessage").focus();}
		livecounterUpdate();
	});
}
</script>

<style>
/*Livecounter Main*/
#livecounterMain					{display:none; position:fixed; z-index:20; bottom:0px; left:0px; width:100%; height:50px; text-align:center;}/*fixe déjà une hauteur de base pour le calcul du "footerHeight()"*/
#livecounterMainContent				{position:relative; display:inline-block; height:50px; color:#ddd!important; background-color:rgba(0,0,0,0.9); padding-left:60px; padding-right:30px; line-height:50px; border-radius:5px 5px 0px 0px;}/*'line-height' centre verticalement le label des users, meme s'ils n'ont pas de photos*/
#livecounterMainIcon				{position:absolute; left:-10px; top:-2px; cursor:pointer;}
#livecounterMainTitle				{margin-right:15px;}
#livecounterMainUsers label			{padding:7px 7px 7px 0px; margin-right:15px;}/*cf. "actionLivecounterUpdate()"*/
#livecounterMainUsers .personImg	{width:36px; height:36px; margin-right:8px;}/*cf. "actionLivecounterUpdate()"*/
.vLivecounterMainUsersLabelSelect	{border:solid 1px #555; border-radius:3px; background-color:#333;}

/*Messenger*/
#messengerContainer					{display:none; position:fixed; z-index:23; min-width:330px; width:600px; min-height:200px; height:500px; border-radius:5px; padding:30px 10px 30px 10px; color:#ddd!important; background-color:rgba(0,0,0,0.95);}
#messengerContainer .personImg		{width:24px; height:24px; margin-left:5px;}/*cf. "actionLivecounterUpdate()"*/
#messengerClose						{position:absolute; top:-10px; right:-10px;}
#messengerPostForm					{margin-bottom:20px; text-align:center; vertical-align:bottom;}
#messengerPostMessage, #messengerPostButton	{height:34px;}
#messengerPostMessage				{width:70%; font-weight:bold; border-radius:3px;}
#messengerPostButton				{width:28%; max-width:100px; margin-bottom:2px;}
#messengerContent					{display:table; width:100%; border-radius:5px;}
#messengerContent>div				{display:table-cell;}
#messengerContent>div:first-child	{width:120px; background:#333; border-radius:3px;}
#messengerDrag						{width:16px; vertical-align:middle; cursor:move; background-image:url(app/img/dragDrop.png);}
#messengerUsersAjax, #messengerMessagesAjax	{overflow-y:auto;}
#messengerUsersAjax>div				{margin:5px;}
#messengerMessagesAjax				{background-image:url(app/img/messengerBig.png); background-repeat:no-repeat; background-position:95% 95%;}
.vMessengerMessage					{display:table;}
.vMessengerMessage>div				{display:table-cell; padding:5px; cursor:help; vertical-align:middle;}
.vMessengerMessage>div:first-child	{min-width:80px; color:#888;}/*heure et auteur du message*/
.vMessengerMessage>div:last-child	{font-style:italic;}/*text du "curUser"*/

/*RESPONSIVE*/
@media screen and (max-width:1023px){
	#livecounterMainContent		{width:100%; background:linear-gradient(to top,#111,#333); padding:0px; text-align:right; border-radius:0px; border-top:solid 1px #555;}/*padding left & right à zero car width=100%*/
	#livecounterMainIcon		{left:0; top:8px; opacity:0.3;}
	#livecounterMainTitle		{display:none;}/*masque "connectés:"*/
	#livecounterMainUsers label	{text-transform:uppercase;}
	#messengerContainer			{border-radius:0px; padding:25px 12px 15px 12px; background-color:#000;}/*background-color == #messengerContent*/
	#messengerPostForm			{margin-top:10px;}
	#messengerDrag				{display:none!important;}
	#messengerClose				{top:0px; right:0px;}
}
</style>

<!--MESSENGER-->
<div id="messengerContainer" onMouseOver="$(this).draggable({handle:'#messengerDrag',opacity:0.9});">
	<!--FERME-->
	<a id="messengerClose" onclick="messengerDisplay('close');" title="<?= Txt::trad("close") ?>"><img src="app/img/<?= Req::isMobile()?"closeResp":"close" ?>.png"></a>
	<!--POST MESSAGE & CO (Au dessus des messages: pour pas être masqué par le clavier virtuel en responsive)-->
	<div id="messengerPostForm">
		<input type="text" name="message" id="messengerPostMessage" maxlength="1000" onkeyup="if(event.keyCode==13){messengerPost();}">
		<button id="messengerPostButton" onclick="messengerPost();"><img src="app/img/postMessage.png"> <?= Txt::trad("send") ?></button>
	</div>
	<!--USERS & MESSAGES-->
	<div id="messengerContent">
		<div id="messengerUsersAjaxDiv"><div id="messengerUsersAjax">&nbsp;</div></div>
		<div id="messengerMessagesAjaxDiv"><div id="messengerMessagesAjax">&nbsp;</div></div>
		<div id="messengerDrag">&nbsp;</div>
	</div>
</div>

<!--LIVECOUNTER PRINCIPAL + ICONE MESSENGER-->
<div id="livecounterMain">
	<span id="livecounterMainContent">
		<img src="app/img/messenger.png" id="livecounterMainIcon" onclick="messengerDisplay('all');" title="<?= Txt::trad("MESSENGER_messenger") ?>">
		<span id="livecounterMainTitle"><?= Txt::trad("MESSENGER_connected") ?> :</span>
		<span id="livecounterMainUsers"></span>
	</span>
</div>