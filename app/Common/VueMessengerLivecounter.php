<script>	
////	INIT
$(function(){
	////	Init le "messengerDisplay()" & co : Son d'alerte && Mode d'affichage courant (dernier user affiché)
	if(typeof messengerAlert=="undefined"){
		messengerAlert=new Audio("app/misc/messengerAlert.mp3");
		messengerAlert.volume=0.7;//volume à 70%
		messengerAlert.loop=false;
		displayModeIdUserCurrent=null;
	}
	////	Lance les livecounters (footer & messenger)
	livecounterUpdate(true);
	////	Init le Drag/Drop du messenger
	if(isMobile()==false)  {$("#messengerContainer").draggable({handle:"#messengerMove",opacity:0.9});}
});

////	Update les livecounters (principal/messenger) && Messages du messenger
function livecounterUpdate(livecounterInit)
{
	//// Init l'url du "LivecounterUpdate"
	var livecounterUpdateUrl="?ctrl=misc&action=LivecounterUpdate";
	// Vérif si un autre user edite en meme temps le meme element (édition lightbox) : params "editObjId"
	if($(".fancybox-iframe").exist() && find("edit",$(".fancybox-iframe").attr("src")))  {livecounterUpdateUrl+="&editObjId="+getUrlParam("targetObjId",$(".fancybox-iframe").attr("src"));}
	// Edition d'un texte via tinymce : on l'enregistre automatiquement en tant que brouillon/draft (cf. "confirmCloseForm")
	if(confirmCloseForm==true){
		if(typeof tinymce!=="undefined"  ||  ($(".fancybox-iframe").exist() && typeof $(".fancybox-iframe")[0].contentWindow.tinymce!=="undefined")){
			var editorDraft=(typeof tinymce!=="undefined")  ?  tinymce.activeEditor.getContent()  :  $(".fancybox-iframe")[0].contentWindow.tinymce.activeEditor.getContent();//Tinymce d'une lightbox ou d'un page principale (module "mail")
			livecounterUpdateUrl+="&editorDraft="+encodeURIComponent(editorDraft);
		}
	}

	//// Lance l'update ajax du livecounter
	$.ajax({url:livecounterUpdateUrl,dataType:"json"}).done(function(result){
		//// Affiche/Update les livecounters
		if(result.livercounterChanged==true || livecounterInit==true)
		{
			//Masque le Livecounter (aucun user connecté ni message à afficher)
			if(result.livecounterMainHtml.length==0 && result.messengerMessagesHtml.length==0)  {$("#livecounterMain").hide();  $("#pageFooterHtml").show();}
			//Affiche le Livecounter principal
			else{
				//liste des personnes connectées  /  "Personne n'est actuellement connecté.."
				if(result.livecounterMainHtml.length>0){
					$("#livecounterMainTitle").show();
					$("#livecounterMainUsers").html(result.livecounterMainHtml);
				}else{
					$("#livecounterMainTitle").hide();
					$("#livecounterMainUsers").html("<span onclick=\"messengerDisplay('history')\" style='cursor:pointer;' title=\"<?= Txt::trad("MESSENGER_connectedNobody") ?>\">...</span>");
					$("#livecounterMain,#livecounterMainContent").css("height","40px").css("line-height","40px");
				}
				$("#pageFooterHtml").hide();
				$("#livecounterMain").show();
			}
			//Affiche le livecounter "checkboxes" : Retient les users dejà sélectionnés, affiche le nouveau livecounter, puis réaffecte les users précédemment sélectionnés
			var oldCheckedUsers=messengerUsersChecked();
			$("#messengerUsersAjax").html(result.livecounterMessengerHtml);
			for(var tmpKey in oldCheckedUsers)  {$("#messengerUserBox"+oldCheckedUsers[tmpKey]).prop("checked",true);}
		}

		//// Affiche/Update les messages du messenger
		if(result.messengerChanged==true || livecounterInit==true)
		{
			$("#messengerMessagesAjax").html(result.messengerMessagesHtml);	//Ajoute tous les messages à "#messengerMessagesAjax"
			messengerUserStyleFilterMessages(displayModeIdUserCurrent);		//Filtre les messages en fonction de l'affichage courant
			scrollToLastMessages();											//"scroll" vers les derniers messages
			if(result.livercounterChanged==true)  {mainPageDisplay(false);}	//Réinitialise l'affichage de la page principale : MAJ des tooltips, fancybox, etc
		}

		//// Pulsate d'users si ya de nouveaux messages à afficher (Pulsate et Alerte sonore relancé à chaque "livecounterUpdate" si besoin)
		if(result.messengerPulsateUsers.length>0)
		{
			//Fait clignoter l'auteur d'un nouveau message
			var isMessengerAlert=false;
			for(var tmpKey in result.messengerPulsateUsers){
				isMessengerAlert=true;
				var pulsateIdUser=result.messengerPulsateUsers[tmpKey];
				if(messengerIsVisible() && displayModeIdUserCurrent==pulsateIdUser)	{pulsateTimes=3;  messengerUpdateDisplayedUser(pulsateIdUser);}	//Messenger de l'user affiché : 3 "pulsates" & Maj Ajax du "messengerDisplayTime"
				else																{pulsateTimes=50;}												//Messenger de l'user masqué  : 50 "pulsates"
				$("#livecounterMainUsers label[data-idUser='"+pulsateIdUser+"']").stop(true).css("opacity","1").effect("pulsate",{times:pulsateTimes},Math.round(pulsateTimes*800));//Lance/Relance le pulsate
			}
			//Alerte sonore si ya de nouveaux messages : 2 bips à chaque "livecounterUpdate()". Vérif sur mobile que la page est tjs affichée, pour pas avoir de son "parasites" en tache de fond (Attention : ne pas mettre le bloquage au niveau de la relance du "livecounterUpdate()" ci-dessous, car on veut bloquer uniquement les alertes sonores!)
			var mobileAppHidden=(document.visibilityState=="hidden" && isMobile());
			if(isMessengerAlert==true && mobileAppHidden==false){
				setTimeout(function(){ messengerAlert.play(); },3000);//1er bip : Timeout pour éviter le blocage de lecture auto du navigateur (cf. "play() failed")
				setTimeout(function(){ messengerAlert.play(); },6000);//2ème bip
			}
		}

		//// Relance "livecounterUpdate()"
		if(typeof livecounterLoadTime=="undefined")  {livecounterLoadTime=Date.now();}		//Init le time du premier "livecounterUpdate()"
		if(document.visibilityState!="hidden" || (Date.now()-livecounterLoadTime)<600000){	//Stop le "livecounterUpdate()" si la page n'est pas affichée depuis plus de 10mn : pas de requetes Ajax inutiles en tache de fond..
			if(typeof livecounterTimeout!="undefined")  {clearTimeout(livecounterTimeout);}	//Pas de cumul de Timeout
			livecounterTimeout=setTimeout(function(){ livecounterUpdate(); },12000);		//Relance le "livecounterUpdate()" après 12 secondes
		}
	});
}

////	Affiche/Masque le messenger (displayModeIdUser => "all", "history", "close" ou "_idUser" specifique)
function messengerDisplay(displayModeIdUser)
{
	//// Masque le messenger principal (fermeture demandé ou messenger de l'user deja affiché)
	if(displayModeIdUser=="close" || (messengerIsVisible() && displayModeIdUser==displayModeIdUserCurrent)){
		$("#messengerContainer").hide();
		$("body").css("overflow","visible");//réactive le scroll de page en arriere plan
	}
	//// Affiche le messenger principal
	else
	{
		//// Affichage mobile
		if(isMobile()){
			$("#messengerContainer").outerHeight($(window).height()).outerWidth($(window).width());	//Affiche le messenger en toute hauteur/largeur
			$(window).on("resize",function(){														//Affichage du clavier virtuel puis redimensionnement auto de la page : Resize les blocs "scrollables" des messages
				if(messengerIsVisible()){
					$("#messengerContainer").outerHeight($(window).height());	//Redimensionne le "#messengerContainer"
					messengerContentSize();										//MAJ la taille du "messengerContent" et ses div scrollables
				}
			});
		}
		//// Affichage normal
		else{
			var newLeft=Math.round( ($(window).width()/2) - ($("#messengerContainer").width()/2) );	//Centre le messenger sur la page (via left)
			$("#messengerContainer").css("left",newLeft).resizable({handles:"n,e,s,w"});			//Centre le messenger && Rend le messenger redimensionnable des 4 cotés
			$("#messengerContainer").resize(function(){ messengerContentSize(); });					//MAJ la taille du "messengerContent" et ses div scrollables
		}
		//// Affiche le messenger !
		$("#messengerContainer").show();

		//// Désélectionne tous les users, puis sélectionne si besoin un user spécifique (checkboxe masquée?)
		$("[id^='messengerUserBox']").prop("checked",false);
		if($.isNumeric(displayModeIdUser) && $("#messengerUserBox"+displayModeIdUser).exist())  {$("#messengerUserBox"+displayModeIdUser).prop("checked",true);}

		//// Placeholer de l'input ("Mon message à Boby")
		var placeholderText=($.isNumeric(displayModeIdUser))  ?  "<?= Txt::trad("MESSENGER_addMessageTo") ?> "+$("#livecounterMainUsers label[data-idUser='"+displayModeIdUser+"']").text()  :  "<?= Txt::trad("MESSENGER_addMessageToSelection") ?>";
		$("#messengerPostMessage").attr("placeholder",placeholderText);

		//// Divers
		displayModeIdUserCurrent=displayModeIdUser;												//Enregistre le "displayModeIdUser" courant (cf. "livecounterUpdate()")
		messengerContentSize();																	//Init la taille du "messengerContent" et ses div scrollables
		messengerAlert.pause();																	//Fin de son d'alerte
		$("#livecounterMainUsers label").stop(true).css("opacity","1");							//Fin de "pulsate"
		if($.isNumeric(displayModeIdUser))  {messengerUpdateDisplayedUser(displayModeIdUser);}	//MAJ Ajax du "time" de l'user affiché
		if(isMobile()==false)  {$("#messengerPostMessage").focus();}							//Focus sur le messenger ?
		if(displayModeIdUser=="history")  {$("#messengerPostForm").hide();}						//Mode "history" : masque le formulaire
		$("body").css("overflow","hidden");														//Désactive le scroll de page en arriere plan
	}
	//// Filtre les messages en fonction de l'affichage courant
	messengerUserStyleFilterMessages(displayModeIdUser);
}

////	Filtre les messages en fonction de l'affichage courant ("all"/"history" ou user spécifique)  &&  Style de l'user dans le licounter principal
function messengerUserStyleFilterMessages(displayModeIdUser)
{
	//Réinit : Masque le livecounter "checkboxes" et la liste des messages  &&  Déselectionne tous les users du livecounter principal
	$("#messengerUsersAjax,.vMessengerMessage").hide();
	$("#livecounterMainUsers label[data-idUser]").removeClass("vLivecounterMainUsersLabelSelect");
	//Affichage "all" / "history" / User spécifique
	if(messengerIsVisible())
	{
		if(displayModeIdUser=="all")  {$("#messengerUsersAjax,.vMessengerMessage").show();}	//"all"	: affiche les messages de tous les users + le livecounter "checkboxes"
		else if(displayModeIdUser=="history")  {$(".vMessengerMessage").show();}			//"history"	: affiche les messages de tous les users
		else if($.isNumeric(displayModeIdUser)){											//User : affiche uniquement les messages de l'user courant et le surligne dans le livecounter principal (cf. "vLivecounterMainUsersLabelSelect")
			$(".vMessengerMessage[data-idUsers*='@"+displayModeIdUser+"@']").show();
			$("#livecounterMainUsers label[data-idUser='"+displayModeIdUser+"']").addClass("vLivecounterMainUsersLabelSelect");
		}
	}
}

////	MAJ Ajax du "time" de l'user affiché
function messengerUpdateDisplayedUser(_idUser)
{
	$.ajax({url:"?ctrl=misc&action=MessengerUpdateDisplayedUser&_idUser="+_idUser});
}
////	Verif si le messenger est affiché
function messengerIsVisible()
{
	return ($("#messengerContainer").is(":visible"));
}
////	Dimensionne le "messengerContent" et ses div scrollables ("#messengerUsersAjax"+"#messengerMessagesAjax") en fonction du "messengerContainer"
function messengerContentSize()
{
	$("#messengerContent,#messengerUsersAjax,#messengerMessagesAjax").outerHeight( $("#messengerContainer").height()-$("#messengerPostForm").outerHeight(true));
	scrollToLastMessages();//Scroll jusqu'aux derniers messages
}
////	Affiche les derniers messages (en bas de "messengerMessagesAjax")
function scrollToLastMessages()
{
	setTimeout(function(){ $("#messengerMessagesAjax").scrollTop($("#messengerMessagesAjax").prop("scrollHeight")); },200);
}
////	Users sélectionnés (retourne un tableau)
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
/*Livecounter*/
#livecounterMain					{display:none; position:fixed; z-index:31; bottom:0px; left:0px; width:100%; height:55px; text-align:center;}/*Toujours préciser un "height" pour le calcul du "footerHeight()"*/
#livecounterMainContent				{position:relative; display:inline-block; height:55px; line-height:55px; background-color:#333; color:#ddd!important; padding-left:60px; padding-right:30px; border-radius:5px 5px 0px 0px;}/*Le "line-height" centre toujours verticalement le label des users ..meme s'ils n'ont pas de photos*/
#livecounterMainIcon				{position:absolute; left:2px; top:10px; opacity:0.7; cursor:pointer;}/*Icone du messenger*/
#livecounterMainUsers label			{padding:5px; margin-right:15px;}/*cf. "actionLivecounterUpdate()"*/
#livecounterMainUsers .personImg	{width:36px; height:35px; margin-right:5px;}/*cf. "actionLivecounterUpdate()"*/
.vLivecounterMainUsersLabelSelect	{border:solid 1px #777; border-radius:3px; background-color:#555;}

/*Messenger (Container & Messages & Users & Form) */
#messengerContainer							{display:none; position:fixed; z-index:30; bottom:-5px; width:600px; height:600px; min-width:300px; min-height:250px; padding:20px; padding-bottom:120px; border-radius:5px; background-color:#111; color:#ddd!important;}/*"z-index:30" et "padding-bottom:120" car "#messengerContainer" englobe le "livecounterMainContent"*/
#messengerContainer .personImg				{width:22px; height:22px; margin-left:5px;}/*cf. "actionLivecounterUpdate()"*/
#messengerClose								{position:absolute; top:-10px; right:-10px;}
#messengerMove								{height:18px; margin-bottom:10px; cursor:move; background-image:url(app/img/dragDrop.png);}
#messengerContent							{display:table; width:100%; border-radius:5px;}
#messengerContent>div						{display:table-cell;}
#messengerMessagesAjax						{overflow-y:auto;background-image:url(app/img/messengerBig.png); background-repeat:no-repeat; background-position:95% 95%;}
#messengerMessagesAjax::-webkit-scrollbar	{width:15px; background:#333;}/*width & background de la scrollbar*/
#messengerMessagesAjax::-webkit-scrollbar-thumb	{background:#888;}/*barre de scroll*/
.vMessengerMessage							{display:table; margin:3px;}/*liste des messages du '#messengerMessagesAjax'*/
.vMessengerMessage>div						{display:table-cell; padding:5px; cursor:help; vertical-align:middle;}
.vMessengerMessage>div:first-child			{min-width:80px; color:#888;}/*heure et auteur du message*/
.vMessengerMessage>div:last-child			{font-style:italic;}/*text du "curUser"*/
#messengerUsersAjax							{width:180px; background:#555; border-radius:3px;}
#messengerUsersAjax>div						{margin:10px;}
#messengerPostForm							{margin-top:20px; text-align:center; vertical-align:bottom;}
#messengerPostMessage, #messengerPostButton	{height:34px;}
#messengerPostMessage						{width:70%; font-weight:bold; border-radius:3px;}
#messengerPostButton						{width:28%; max-width:100px; margin-bottom:2px;}

/*RESPONSIVE*/
@media screen and (max-width:1023px){
	#livecounterMain					{height:42px;}
	#livecounterMainContent				{height:42px; line-height:42px; width:100%; padding:0px; background:linear-gradient(to top,#111,#333); border-radius:0px;}
	#livecounterMainIcon				{display:none!important;}
	#livecounterMainUsers label			{margin-right:10px; font-size:0.9em;}
	#messengerContainer					{bottom:0px; border-radius:0px; padding:10px; padding-top:20px; padding-bottom:60px;}/*Mettre "bottom:0" (et non "top:0") : sinon le clavier viruel cachera le formulaire d'envoi de message!*/
	#messengerMove						{display:none!important;}
	#messengerClose						{top:0px; right:0px;}
}
</style>

<!--MESSENGER-->
<div id="messengerContainer">
	<!--FERME/DEPLACE LE MESSENGER -->
	<a id="messengerClose" onclick="messengerDisplay('close');" title="<?= Txt::trad("close") ?>"><img src="app/img/close.png"></a>
	<div id="messengerMove">&nbsp;</div>
	<!--MESSAGES ET USERS SELECTIONNES ("hidden" par défaut)-->
	<div id="messengerContent">
		<div id="messengerMessagesAjaxScrollContainer"><div id="messengerMessagesAjax">&nbsp;</div></div>
		<div id="messengerUsersAjax">&nbsp;</div>
	</div>
	<!--POST MESSAGE & CO (Toujours en dessous des messages, pour pas être masqué par le clavier virtuel sur l'appli mobile)-->
	<div id="messengerPostForm">
		<input type="text" name="message" id="messengerPostMessage" maxlength="1000" onkeyup="if(event.keyCode==13){messengerPost();}">
		<button id="messengerPostButton" onclick="messengerPost();"><img src="app/img/postMessage.png"> <?= Txt::trad("send") ?></button>
	</div>
</div>

<!--LIVECOUNTER PRINCIPAL + ICONE MESSENGER-->
<div id="livecounterMain">
	<span id="livecounterMainContent">
		<img src="app/img/messenger.png" id="livecounterMainIcon" onclick="messengerDisplay('all');" title="<?= Txt::trad("MESSENGER_messenger").(!empty($_SESSION["livercounterUsers"])?" : ".Txt::trad("MESSENGER_messengerInfo"):null) ?>">
		<span id="livecounterMainTitle"><?= Txt::trad("MESSENGER_connected") ?> <img src="app/img/arrowRight.png"> &nbsp; </span>
		<span id="livecounterMainUsers"></span>
	</span>
</div>