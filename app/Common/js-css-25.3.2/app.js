/*********************************************************************************
* This file is part of the Agora-Project Software package
*
* @copyleft Agora-Project <https://www.agora-project.net>
* @license GNU General Public License (GPL-2.0)
**********************************************************************************/


/**************************************************************************************************
 * DOM CHARGÉ : LANCE UNE FONCTION
**************************************************************************************************/
function ready(thisFunction){
	if(document.readyState!='loading') {thisFunction();}
	document.addEventListener('DOMContentLoaded', thisFunction);
}

/**************************************************************************************************
 * LANCE/INIT LES FONCTIONS PRINCIPALES
 **************************************************************************************************/
ready(function(){
	mainDisplay();													//Affichage principal
	window.onresize=function(){	mainDisplay(); };					//Relance si window.onresize
	screen.orientation.onchange=function(){	mainDisplay(500); };	//Relance si orientation.onchange (timeout + long)
	mainTriggers();													//Triggers principaux
	controleFields();												//Affichage et controle des champs de formulaire
	menuContext();													//Affichage des menus contextuels
});

/**************************************************************************************************
 * AFFICHAGE PRINCIPAL
 **************************************************************************************************/
function mainDisplay(timoutDuration=20)
{
	if(typeof mainDisplayTimeout!="undefined")  {clearTimeout(mainDisplayTimeout);}//Pas de cumul de Timeout
	mainDisplayTimeout=setTimeout(function(){
		////	"Title" via Tooltipster
		tooltipsterParams={theme:'tooltipster-shadow',contentAsHTML:true,interactive:true};//Affiche les balises Html et liens "http" (cf. ModLink & co)
		$("[title]:not(.noTooltipster,[title=''])").tooltipster(tooltipsterParams);
		
		if(isMainPage==true){
			////	Enregistre le width dans un cookie (tjs avec "samesite")
			document.cookie="windowWidth="+$(window).width()+";Max-Age=31536000;path=/;Priority=high;SameSite=lax";

			////	Calcule le Width des objets en affichage "block"
			if($(".objBlocks .objContainer").exist()){
				let marginRight=parseInt($(".objContainer").css("margin-right"));														//Marges de l'objet (cf. "app.css")
				let widthMin=parseInt($(".objContainer").css("min-width")) + marginRight;												//width Min
				let widthMax=parseInt($(".objContainer").css("max-width")) + marginRight;												//width Max
				let widthDispo=$("#pageFull #pageContent").width();																		//Width disponible (pas de "innerWidth()")
				if($(document).height()==$(window).height() && !isMobile())  {widthDispo-=15;}											//Enlève le width d'une future scrollbar (sauf sur mobile car tjs masqué)
				let lineNbObjs=Math.ceil(widthDispo / widthMax);																		//Nb maxi d'objets par ligne
				if(widthDispo < (widthMin*2))					{widthObj=widthDispo;  $(".objContainer").css("max-width",widthDispo);}	//On peut afficher qu'un objet par ligne : il prendra toute la largeur
				else if($(".objContainer").length<lineNbObjs)	{widthObj=widthMax;}													//Nb d'objets insuffisant pour remplir la 1ère ligne : prend sa largeur max
				else											{widthObj=Math.floor(widthDispo/lineNbObjs);}							//Width des objets en fonction du width disponible et du nb d'objets par ligne
				$(".objContainer").outerWidth(widthObj,true);																			//Applique le width des objets (true pour prendre en compte les margins)
				$(".objBlocks .objContainer").show();																					//Affiche enfin les objets (masqués par défaut via "app.css")
			}
		}
	},timoutDuration);
}

/**************************************************************************************************
 * PRINCIPAUX TRIGGERS : CLICK / DBLCLICK SUR LES OBJETS  +  MENUS FLOTTANT  +  FANCYBOX
 **************************************************************************************************/
function mainTriggers()
{
	////	Click sur un objet pour le sélectionner  ||  dblclick sur un objet pour l'éditer
	$(".objContainer").on("click dblclick",function(event){
		if(event.type=="dblclick" && $(this).attr("data-urlEdit"))		{lightboxOpen($(this).attr("data-urlEdit"));}
		else if(event.type=="click" && $(".objSelectCheckbox").exist())	{objSelectSwitch(this.id);}
	});

	////	Menu du module en position flottante
	if($("#pageMenu").isVisible()){
		$(window).on("scroll",function(){
			if(typeof pageMenuTimeout!="undefined")  {clearTimeout(pageMenuTimeout);}//Pas de cumul de Timeout
			pageMenuTimeout=setTimeout(function(){
				let menuHeight=$("#pageMenu").position().top;															//Position top du menu
				$("#pageMenu").children().each(function(){ menuHeight+=$(this).outerHeight(true); });					//Ajoute la hauteur de chaque element
				if(menuHeight < $(window).height())  {$("#pageMenu").css("padding-top",$(window).scrollTop()+"px");}	//Repositionne le menu en fonction de la fenêtre
			},100);
		});
	}

	////	Fancybox des images & contenu inline
	$("[data-fancybox='images']").fancybox({buttons:(isMobile()?['close']:['slideShow','zoom','fullScreen','close'])});	//Close sur mobile ou slideShow, Zoom, etc
	$("[data-fancybox='inline']").fancybox({buttons:['close'],touch:false,arrows:false,infobar:false,smallBtn:false});	//Pas de navigation via arrows, touch, smallBtn, etc
}

/**************************************************************************************************
 *  CONTROLES DES CHAMPS
 **************************************************************************************************/
function controleFields()
{
	////	Pas d'autocomplétion des inputs
	$("form input:not(.isAutocomplete)").attr("autocomplete","off");

	////	"confirmCloseForm" à true si un formulaire est édité (cf. "redir()" & co)
	setTimeout(function(){
		$("#mainForm").find("input,select,textarea").on("input change keyup",function(){ window.parent.confirmCloseForm=true; });
	},500);//Timeout le tps des pré-controle de formulaire

	////	Controle la taille des fichiers des inputs "file"
	$("input[type='file']").on("change",function(){
		if($(this).notEmpty() && this.files[0].size > valueUploadMaxFilesize){
			$(this).val("");
			notify(labelUploadMaxFilesize);
		}
	});

	////	Couleur de background des options d'un <select>
	$("select option[data-color]").each(function(){
		$(this).css("background",$(this).attr("data-color")).css("color","white");
	});
	$("select").on("change",function(){
		var optionColor=$(this).find("option:selected").attr("data-color");
		if(isValue(optionColor))	{$(this).css("background",optionColor).css("color","white");}
		else						{$(this).css("background","white").css("color","#000");}
	});

	////	Charge le Datepicker
	if(jQuery().datepicker){
		$(".dateInput, .dateBegin, .dateEnd").datepicker({dateFormat:"dd/mm/yy", firstDay:1, showOtherMonths:true, selectOtherMonths:true});
		if(isMobile())  {$(".dateInput, .dateBegin, .dateEnd").prop("readonly",true);}//Input "readonly" sur mobile
	}

	////	Charge le Timepicker
	if(jQuery().timepicker){
		$(".timeBegin, .timeEnd").timepicker({timeFormat:"H:i", step:15, "orientation":(isMobile()?"rb":"lb")});
		if(navigator.maxTouchPoints > 1 && /(iphone|ipad|macintosh)/i.test(navigator.userAgent)){//Pas sur Iphone/Ipad car utilise le timePicker system ("macintosh" sur les ipads récents)
			$(".timeBegin, .timeEnd").on("showTimepicker",function(){  $(".timeBegin, .timeEnd").timepicker("hide");  });
		}
	}

	////	Init dateBeginRef + timeBeginRef (en millisecondes!)
	if($(".dateBegin").notEmpty())  {var dateBeginRef=$(".dateBegin").datepicker("getDate").getTime();}
	if($(".timeBegin").notEmpty())  {var timeBeginRef=$(".timeBegin").timepicker("getTime").getTime();}

	////	Datepicker/Timepicker : Controle du DateTime
	$(".dateBegin, .dateEnd, .timeBegin, .timeEnd").on("change",function(){
		//// Controle le format des dates et heures
		if( ($(this).hasClass("dateBegin") || $(this).hasClass("dateEnd"))  &&  $(this).notEmpty()  &&  /^\d{2}\/\d{2}\/\d{4}$/.test(this.value)==false)  	 {notify(labelDateFormatError);}
		if( ($(this).hasClass("timeBegin") || $(this).hasClass("timeEnd"))  &&  $(this).notEmpty()  &&  /^[0-2][0-9][:][0-5][0-9]$/.test(this.value)==false)   {notify(labelTimeFormatError);}
		//// dateBegin avancé/reculé : dateEnd ajusté
		if($(this).hasClass("dateBegin")){
			let beginDiffTime=($(".dateBegin").datepicker("getDate").getTime() - dateBeginRef);						//Différence entre l'ancienne et la nouvelle .dateBegin (en millisecondes!)
			let dateEndNew=new Date(($(".dateEnd").datepicker("getDate").getTime() + beginDiffTime));				//Calcule la .dateEnd en fonction de la nouvelle .dateBegin
			$(".dateEnd").datepicker("setDate",dateEndNew).pulsate(1);												//Applique la nouvelle .dateEnd avec un "pulsate"
		}
		//// timeBegin avancé/reculé : timeEnd ajusté
		if($(this).hasClass("timeBegin") && $(".dateBegin").val()==$(".dateEnd").val()){							//Verif que .dateBegin == .dateEnd
			let beginDiffTime=($(".timeBegin").timepicker("getTime").getTime() - timeBeginRef);						//Différence entre l'ancien et la nouveau .timeBegin (en millisecondes!)
			let timeEndNew=new Date(($(".timeEnd").timepicker("getTime").getTime() + beginDiffTime));				//Calcule le .timeEnd en fonction du nouveau .timeBegin
			$(".timeEnd").timepicker("setTime",timeEndNew).pulsate(1);												//Applique le nouveau .timeEnd avec un "pulsate"
		}
		//// Verif que le datetime de début soit avant celui de fin
		let dateBegin=$(".dateBegin").val().split("/");																//Date de début au format "dd/MM/yyyy"
		let dateEnd	 =$(".dateEnd").val().split("/");																//Date de fin
		let datetimeBegin	=new Date(dateBegin[1]+"/"+dateBegin[0]+"/"+dateBegin[2]+" "+$(".timeBegin").val());	//Objet Date de début au format "MM/dd/yyyy HH:mm"
		let datetimeEnd		=new Date(dateEnd[1]+"/"+dateEnd[0]+"/"+dateEnd[2]+" "+$(".timeEnd").val());			//Objet Date de fin
		if(datetimeBegin > datetimeEnd){
			setTimeout(function(){
				notify(labelBeginEndError);																			//Notif "La date de début doit précéder la date de fin"
				$(".dateEnd").val($(".dateBegin").val());															//Date de fin = idem début 
				$(".timeEnd").val($(".timeBegin").val());															//Time de fin = idem début 
			},300);																									//Timeout car modif après l'action du Timepicker
		}
		//// PUIS update dateBeginRef + timeBeginRef (en millisecondes!)
		if($(".dateBegin").notEmpty())  {dateBeginRef=$(".dateBegin").datepicker("getDate").getTime();}
		if($(".timeBegin").notEmpty())  {timeBeginRef=$(".timeBegin").timepicker("getTime").getTime();}
	});
}

/**************************************************************************************************
 * MENUS CONTEXTUELS
 * .menuLaunch doit avoir la propriété "for" correspondant à l'ID du menu
 **************************************************************************************************/
function menuContext()
{
	////	MENU MOBILE (width<=1024)
	if(isMobile()){
		//// Affiche le menu context mobile : click du .menuLaunch
		$(".menuLaunch").on("click",function(){
			if($("#menuMobileMain").isVisible()==false)	{menuMobileShow($(this).attr("for"),$(this).attr("forBis"));}					//Menu masqué : on l'affiche
			else										{$("#"+$(this).attr("for")).addClass("menuContextSubMenu").slideToggle();}		//Menu déjà affiché : on affiche le sous-menu
		});
		//// Masque le menu context : click sur "close" ou le background du menu mobile
		$("#menuMobileClose,#menuMobileBg").on("click",function(){
			menuMobileClose();
		});
		//// Swipe sur la page pour afficher/masquer le menu context
		pageScrolled=false;
		swipeMenuActive=true;
		setTimeout(function(){																											//Timeout le tps de charger le menu tinyMce mobile/horizontal (200ms minimum)
			document.addEventListener("touchstart",function(event){																		//Début de swipe :
				swipeStartX=event.touches[0].clientX;																					//Init X
				swipeStartY=event.touches[0].clientY;																					//Init Y
			});
			document.addEventListener("touchend",function(){																			//Fin de swipe :
				swipeStartY=swipeStartX=0;																								//Réinit X+Y
				if(parseInt($("#menuMobileMain").css("right"))<0)  {$("#menuMobileMain").css("right","0px");}							//Replace si besoin le #menuMobileMain
			});
			document.addEventListener("touchmove",function(event){																		//Swipe l'affichage/masquage du menuContext :
				if(pageScrolled==false && swipeMenuActive==true){																		//Vérif si un scroll est en cours et swipeMenuActive est bien activé
					let swipeDiff=(swipeStartX - event.touches[0].clientX);																//Différence entre la position X de départ et de fin
					if(swipeDiff > 100  &&  ($(window).width() - swipeStartX) < 150)  {menuMobileShow("headerMenuMain","pageMenu");}	//Swipe à gauche > 100px et à 150px du bord de page : Affiche le menu principal
					else if(swipeDiff < -10)										  {menuMobileClose(event.touches[0].clientX);}		//swipe à droite > 10px : "Close" progressif du menu
				}
			});
			//// Verif si un scroll est en cours sur la page
			$(window).add("div").on("scroll",function(){																				//Add "div" correspond au menu tinyMce mobile/horizontal
				pageScrolled=true;																										//Scroll en cours
				if(typeof scrollPageTimeout!="undefined")  {clearTimeout(scrollPageTimeout);}											//Pas de cumul de Timeout
				scrollPageTimeout=setTimeout(function(){ pageScrolled=false; },500);													//Réinitialise le scroll
			});
		},200);
	}
	////	MENU DESKTOP
	else{
		$(".menuLaunch").on("click",function(){					menuContextShow(this);  });						//Mouseover/Click d'un "menuLaunch" : affiche le menu classique
		$(".objContainer").on("contextmenu",function(event){	menuContextShow(this,event); return false;  });	//Click Droit d'un objet : affiche le menu context ("Return false" pour pas afficher le menu du browser)
		$(".menuContext").on("mouseleave",function(){			$(".menuContext").fadeOut();  });				//Masque le menu dès qu'on le quitte
		$(".menuLaunch,.menuContext,[href],[onclick]").on("click",function(event){ event.stopPropagation(); });	//Evite le download de fichier ou la sélection d'objet (cf "objSelectSwitch()")
	}
}

/*MENU CONTEXT : AFFICHE*/
function menuContextShow(thisLauncher, event)
{
	////	Récup l'Id du menu  &&  Hauteur max du menu en fonction de la hauteur de page (cf. "overflow:scroll")
	var menuId="#"+$(thisLauncher).attr("for");
	$(menuId).css("max-height", Math.round($(window).height()-30)+"px");
	////	Vérif si un des parents est en position "relative|absolute|fixed"
	var parentRelativeAbsolute=false;
	$(menuId).parents().each(function(){  if(/(relative|absolute|fixed)/i.test($(this).css("position"))) {parentRelativeAbsolute=true; return false;}  });
	////	Position du menu
	if(event && event.type=="contextmenu")	{var menuPosX=event.pageX-$(thisLauncher).offset().left;	var menuPosY=event.pageY - $(thisLauncher).offset().top;}//En fonction click droit sur ".objContainer". Ajuste la position en fonction de ".objContainer" (toujours en position relative/absolute)
	else if(parentRelativeAbsolute==true)	{var menuPosX=$(thisLauncher).position().left;				var menuPosY=$(thisLauncher).position().top;}			 //En fonction de sa position absolute/relative
	else									{var menuPosX=$(thisLauncher).offset().left;				var menuPosY=$(thisLauncher).offset().top;}				 //En fonction de sa position sur la page
	////	Repositionne le menu s'il est au bord droit/bas de la page
	//Positions du menu + largeur/hauteur : bordure droite et bas du menu
	var menuRightPos =menuPosX + $(menuId).outerWidth(true);
	var menuBottomPos=menuPosY + $(menuId).outerHeight(true);
	//"Parent" en position relative/absolute : ajoute sa position sur la page
	if(/(relative|absolute|fixed)/i.test($(menuId).parent().css("position")))  {menuRightPos+=$(menuId).parent().offset().left;  menuBottomPos+=$(menuId).parent().offset().top;}
	//Ajuste si besoin la position si on est en bordure de page
	var pageBottomPosition=$(window).height()+$(window).scrollTop();
	if($(window).width() < menuRightPos)	{menuPosX=menuPosX-(menuRightPos-$(window).width());}
	if(pageBottomPosition < menuBottomPos)	{menuPosY=menuPosY-(menuBottomPos-pageBottomPosition);}
	////	Positionne et Affiche le menu
	$(".menuContext:not("+menuId+")").hide();									//Masque les autres menus
	if(menuPosY>15)  {menuPosX-=15;  menuPosY-=15;}								//Recentre le menu
	$(menuId).css("left",menuPosX+"px").css("top",menuPosY+"px").fadeIn(200);	//Affiche le menu
}

/*MENU MOBILE : AFFICHE*/
function menuMobileShow(menuSourceOne, menuSourceTwo)
{
	idMenuSourceOne="#"+menuSourceOne;																						// Id du "menuSourceOne"
	idMenuSourceTwo=(menuSourceTwo)  ?  "#"+menuSourceTwo  :  null;															// Id du "menuSourceTwo"
	if($(idMenuSourceOne).exist() && $("#menuMobileMain").isVisible()==false){												//Vérif que "menuSourceOne" existe bien et qu'un menu contextuel n'est pas déjà ouvert
		$(idMenuSourceOne+">*").appendTo("#menuMobileOne");																	//Déplace "menuSourceOne" dans "#menuMobileOne"
		if($(idMenuSourceTwo).exist())  {$(idMenuSourceTwo+">*").appendTo("#menuMobileTwo"); $("#menuMobileTwo").show();}	//Déplace "menuSourceTwo" dans "#menuMobileTwo"
		$("#menuMobileOne,#menuMobileBg").fadeIn(50);																		//Affiche le menu et son contenu
		$("#menuMobileMain").css("right","0px").show("slide",{direction:"right",duration:200});								//Réinit la position "right" (cf. "menuMobileClose()") + affiche le menu
		$("body").css("overflow","hidden");																					//Désactive le scroll de page en arriere plan
	}
}

/*MENU MOBILE : FERME*/
function menuMobileClose(swipeStartXCurrent)
{
	if($("#menuMobileMain").isVisible())
	{
		//// Masque progressivement le menu (150 premiers pixels de swipe)  ||  Masque totalement le menu
		if(typeof swipeStartXCurrent!="undefined" && parseInt($("#menuMobileMain").css("right")) > - 150)  {$("#menuMobileMain").css("right", "-"+(swipeStartXCurrent-swipeStartX)+"px");}
		else{
			$("#menuMobileOne,#menuMobileTwo,#menuMobileBg").fadeOut(50);					//Masque le contenu du menu et son background
			$("#menuMobileMain").hide("slide",{direction: "right",duration:200});			//Masque le menu principal
			$("#menuMobileOne>*").appendTo(idMenuSourceOne);								//Remet le contenu de "#menuMobileOne" dans son div d'origine ("idMenuSourceOne")
			if(idMenuSourceTwo!=null)  {$("#menuMobileTwo>*").appendTo(idMenuSourceTwo);}	//Remet le contenu de "#menuMobileTwo" dans son div d'origine ("idMenuSourceTwo")
			$("body").css("overflow","visible");											//Réactive le scroll de page en arriere plan
		}
	}
}

/**************************************************************************************************
 * AFFICHAGE MOBILE / RESPONSIVE SI WIDTH <= 1024PX  (Idem CSS et Req.php)
 **************************************************************************************************/
function isMobile()
{
	return (window.parent.document.body.clientWidth<=1024);
}

/**************************************************************************************************
 * AFFICHAGE SUR DEVICE TACTILE
 **************************************************************************************************/
function isTouchDevice()
{
	return (navigator.maxTouchPoints > 1);
}

/**************************************************************************************************
 * VÉRIFIE SI UNE VALEURE N'EST PAS VIDE (equiv "isEmpty()")
 **************************************************************************************************/
function isValue(value)
{
	return (typeof value!="undefined" && value!=null && value!="" && value!=0);
}

/**************************************************************************************************
 * CONTROLE S'IL S'AGIT D'UN MAIL
 **************************************************************************************************/
function isMail(mail)
{
	var mailRegex=/^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
	return mailRegex.test(mail);
}

/**************************************************************************************************
 * CONTROLE LE PASSWORD D'UN USER : AU MOINS 6 CARACTÈRES, UN CHIFFRE ET UNE LETTRE
 **************************************************************************************************/
function isValidUserPassword(password)
{
	return (password.length>=6 && /[0-9]/.test(password) && /[a-z]/i.test(password));
}

/**************************************************************************************************
 * EXTENSION D'UN FICHIER (SANS LE POINT)
 **************************************************************************************************/
function extension(fileName)
{
	if(isValue(fileName))  {return fileName.split(".").pop().toLowerCase();}
}

/**************************************************************************************************
 * AFFICHE UNE NOTIFICATION (cf. "toastmessage")
 **************************************************************************************************/
function notify(curMessage, notifType)
{
	if(typeof curMessage!="undefined"){
		$().toastmessage("showToast",{
			text		: curMessage,
			position	: "top-center",
			type		: (typeof notifType!="undefined" ? notifType : "notice"),	//Type "notice" / "success" / "warning"
			stayTime	: (curMessage.length < 100 ? 5000 : 20000)					//5 secondes d'affichage (20 si > 100 caractères)
		});
	}
}

/**************************************************************************************************
 * CONFIRM() : PARAMETRAGE PAR DEFAUT
 **************************************************************************************************/
ready(function(){
	confirmParamsDefault={
		animation:"zoom",							//Animation en entrée/sortie
		boxWidth: isMobile() ? "380px" : "500px",	//Width de la box
		closeIcon:true,								//Icone "close"
		useBootstrap:false,							//Pas de dépendence à bootstrap
	}
});

/**************************************************************************************************
 * CONFIRM() ALTERNATIF  (utiliser si besoin avec "async function()" puis "await confirmAlt()")
 **************************************************************************************************/
function confirmAlt(confirmTitle, confirmContent){
	return new Promise((resolve)=>{
		//// Init le confirm (cf. "labelConfirm" de "VueStructure.php")
		let confirmParams={
			title:isValue(confirmTitle) ? confirmTitle : labelConfirm+" ?",
			content:isValue(confirmContent) ? confirmContent : null,
			buttons:{
				cancel:	{text:labelConfirmCancel},//pas de "resolve()"!
				confirm:{text:labelConfirm,	action:()=>{resolve(true);}, btnClass:'btn-blue'},
			}
		}
		//// Lance le Confirm (paramétrage par défaut + spécifique)
		$.confirm(Object.assign(confirmParamsDefault,confirmParams));
	});
}

/**************************************************************************************************
 * ASYNC REDIRECTION A CONFIRMER  (ex: "Télécharger le fichiers ?")
 **************************************************************************************************/
async function confirmRedir(locationUrl, confirmTitle)
{
	if(await confirmAlt(confirmTitle))  {window.location=locationUrl;}
}

/**************************************************************************************************
 * ASYNC REDIRECTION A CONFIRMER, SI UN FORMULAIRE EN COURS D'EDITION
 **************************************************************************************************/
async function redir(locationUrl)
{
	if(window.parent.confirmCloseForm==false || await confirmAlt(labelConfirmCloseForm))   {window.location=locationUrl;}
}

/********************************************************************************************************************************
 * ASYNC REDIRECTION A CONFIRMER, SI UN FORMULAIRE EN COURS D'EDITION (idem ci-dessus mais pour <a href> sauf "data-fancybox")
 ********************************************************************************************************************************/
ready(function(){
	$("a[href]:not([data-fancybox])").click(async function(event){
		event.preventDefault();
		if(window.parent.confirmCloseForm==false || await confirmAlt(labelConfirmCloseForm)){
			let hrefUrl=$(this).attr("href");
			($(this).attr("target")=="_blank") ? window.open(hrefUrl) : window.location=hrefUrl;
		}
	});
});

/********************************************************************************************************************************
 * VALIDE UN FORMULAIRE DE MANIERE ASYNCHRONE (submit final sans récursivité jquery ni "event.preventDefault()")
 ********************************************************************************************************************************/
function submitFinal(thisForm)
{
	submitLoading();
	$(thisForm).off("submit").submit();
}

/**************************************************************************************************
 * SUBMIT DE FORMULAIRE : AFFICHE TEMPORAIREMENT L'IMG "LOADING"  &  "DISABLE" LE BUTTON SUBMIT
 **************************************************************************************************/
function submitLoading()
{
	$(".submitLoading").css("visibility","visible");
	$("form button[type='submit']").css("background","#eee").prop("disabled",true);
	setTimeout(function(){
		$(".submitLoading").css("visibility","hidden");
		$("form button[type='submit']").css("background","initial").prop("disabled",false);
	 },2000);
}

/********************************************************************************************************************************
 * CONFIRME UNE SUPPRESSION AVEC REDIRECTION  ("labelConfirmXXX" : cf. "VueStructure.php")
 ********************************************************************************************************************************/
async function confirmDelete(deleteUrl, confirmContentAdd, ajaxControlUrl)
{
	let confirmContent='<div class="confirmDeleteAlert">'+labelConfirmDeleteAlert+'</div>';													// Détail du confirm "Attention : cette action est définitive !"
	if(isValue(confirmContentAdd))  {confirmContent+='<img src="app/img/arrowRightBig.png"> '+confirmContentAdd;}							// Ajoute le label de l'objet, le nb d'objets sélectionnés, etc.
	if(await confirmAlt(labelConfirmDelete,confirmContent)){																				// Confirm "Confirmer la suppression ?"
		if(!isValue(ajaxControlUrl))  {redir(deleteUrl);}																					// Lance la suppression directe
		else{																																// Controle Ajax avant suppression de dossier
			$.ajax({url:ajaxControlUrl,dataType:"json"}).done(async function(result){														// Lance le controle Ajax
				if(result.notifDeleteWait) 														{notify(result.notifDeleteWait,"error");}	// Gros dossiers : notif "Merci de patienter un instant"
				if(result.confirmDeleteFolder && await confirmAlt(result.confirmDeleteFolder))	{redir(deleteUrl);}							// "Certains ss-dossiers ne sont pas accessibles ..confirmer la suppression ?"
				else																			{redir(deleteUrl);}							// Lance la suppression
			});
		}
	}
}

/********************************************************************************************************************************
 * OUVRE UNE LIGHTBOX
 * Ne pas lancer via via "href" : plus souple et n'interfère pas avec "stopPropagation()" des "menuContext"
 * tester via : edit object, open pdf/mp3/mp4, userMap, inline html
 ********************************************************************************************************************************/
function lightboxOpen(urlSrc)
{
	////	DEPUIS UNE LIGHTBOX : RELANCE DEPUIS LA PAGE "PARENT"
	if(isMainPage!==true){
		parent.lightboxOpen(urlSrc);
	}
	////	PDF SUR MOBILE : OUVRE UNE NOUVELLE PAGE
	else if(/\.pdf$/i.test(urlSrc) && isMobile()){
		window.open(urlSrc);
	}
	////	LIGHTBOX "INLINE" : LECTEUR MP3 OU VIDEO
	else if(/\.(mp3|mp4|webm)$/i.test(urlSrc)){
		$.fancybox.open({
			type:"inline",
			buttons:['close'],
			src:(/mp3/i.test(urlSrc))  ?  '<div><audio controls><source src="'+urlSrc+'" type="audio/mpeg"</audio></div>'  :  '<video controls><source src="'+urlSrc+'" type="video/'+extension(urlSrc)+'"></video>',
		});
	}
	////	LIGHTBOX "IFRAME" : AFFICHAGE PAR DEFAUT
	else{
		$.fancybox.open({
			type:"iframe",
			src:urlSrc,
			opts:{
				buttons:['close'],																						//Affiche uniquement le bouton "close"
				autoFocus:false,																						//Pas de focus automatique sur le 1er element du formulaire!
				beforeClose:function(){																					//Controle à la fermeture du lightbox
					if(confirmCloseForm==true && typeof lightboxCloseOk==="undefined"){									//Confirme la sortie du formulaire et du lightbox
						confirmAlt(labelConfirmCloseForm).then(()=>{ lightboxCloseOk=true;  $.fancybox.close(); });		//"Fermer le formulaire ?" => ferme la lightbox (récursivement avec relance du "beforeClose")
						return false;																					//Suspend la fermeture en attendant le résultat du "confirmAlt()"
					}else{confirmCloseForm=false; delete lightboxCloseOk;}												//Réinit à la fermeture (tester réouverture de lightbox et "redir()")
				}
			}
		});
	}
}

/**************************************************************************************************
 * RESIZE LE WIDTH D'UNE LIGHTBOX  : APPELÉ DEPUIS UNE LIGHTBOX
 **************************************************************************************************/
function lightboxSetWidth(iframeBodyWidth)
{
	//Page entièrement chargé (pas de "ready(function(){})" sinon peut poser problème sur Firefox & co)
	window.onload=function(){
		//Width définie en pixel/pourcentage : convertie en entier pixel
		if(/px/i.test(iframeBodyWidth))		{iframeBodyWidth=iframeBodyWidth.replace("px","");}									
		else if(/%/.test(iframeBodyWidth))	{iframeBodyWidth=($(window.parent).width()/100) * iframeBodyWidth.replace("%","");}
		//Width du contenu > Width de la page : le width devient celui de la page "parent"
		if(iframeBodyWidth>$(window.parent).width())  {iframeBodyWidth=$(window.parent).width();}
		//Définie le "max-width" de l'iframe (pas de "width", car cela peut afficher un scroll horizontal à l'agrandissement de la lightbox : cf. "lightboxResize()")
		if(isValue(iframeBodyWidth))  {$("body").css("max-width",parseInt(iframeBodyWidth));}
	};
}

/**************************************************************************************************
 * REDUIT/AGRANDIT LA HAUTEUR DE L'IFRAME "LIGHTBOX"  (après fadeIn(), FadeOut(), modif du TinyMce)
 **************************************************************************************************/
function lightboxResize()
{
	if(isMainPage!=true && window.parent.$(".fancybox-iframe").isVisible()){															//Verif si le lightbox est affiché
		if(typeof lightboxResizeTimeout!="undefined")  {clearTimeout(lightboxResizeTimeout);}											//Pas de cumul de Timeout (cf. multiples show(), fadeIn(), etc)
		lightboxResizeTimeout=setTimeout(function(){																					//Lance le resize avec un timeout 250ms minimum (cf. "$.fx.speeds._default=100")
			if(typeof lightboxHeightOld=="undefined" || lightboxHeightOld < window.parent.$(".fancybox-iframe").contents().height()){	//Verif : 1er affichage du lightbox ou "fadeIn()" ou modif du tinymce
				window.parent.$.fancybox.getInstance().update();																		//Resize du lightbox!
				lightboxHeightOld=window.parent.$(".fancybox-iframe").contents().height();												//Enregistre la taille du contenu du lightbox (après update)
			}
		},250);
	}
}

/**************************************************************************************************
 * RELOAD LA PAGE PRINCIPALE DEPUIS UNE LIGHTBOX (ex: après edit d'objet)
 **************************************************************************************************/
function lightboxClose(urlRedir, urlParms)
{
	if(isValue(urlRedir)==false)  {urlRedir=parent.location.href;}								//Récupère l'url de la page principale "parent"
	if(/notify/i.test(urlRedir))  {urlRedir=urlRedir.substring(0,urlRedir.indexOf('&notify'));}	//Enlève les anciens "notify()"
	if(isValue(urlParms))  {urlRedir+=urlParms;}												//Ajoute de nouveaux parametres notify() & co
	parent.location.replace(urlRedir);															//Redir la page principale "parent"
}


/******************************************************************************************************************************************
 *********************************************************************************           FONCTIONS SPECIFIQUES          ***************
 ******************************************************************************************************************************************/


/**************************************************************************************************
 * AFFECTATIONS DES SPACES<->USERS : "VueSpaceEdit.php" & "VueUserEdit.php"
 **************************************************************************************************/
function spaceAffectations()
{
	//// Click le Label d'une affectation (sauf "allUsers")
	$(".spaceAffectLabel").on("click",function(){
		//init
		var _idTarget=$(this).parent().attr("id").replace("targetLine","");	//Id de l'user ou espace dans le div parent contenant "targetLine" (ex: "targetLine55" -> "55")
		var box1=".spaceAffectInput[value='"+_idTarget+"_1']";				//Checkbox "user"
		var box2=".spaceAffectInput[value='"+_idTarget+"_2']";				//Checkbox "admin"
		//Switch de checkbox
		var boxToCheck=null;
		if($(box1).prop("checked")==false && $(box2).prop("checked")==false)	{boxToCheck=box1;}	//Check la box "user"
		else if($(box1).prop("checked") && $(box2).prop("checked")==false)		{boxToCheck=box2;}	//Check la box "admin"
		//Uncheck toutes les boxes (sauf celles "disabled")  &&  Check la box sélectionnée  &&  Stylise les labels
		$(".spaceAffectInput[value^='"+_idTarget+"_']:not(:disabled)").prop("checked",false);
		if(boxToCheck!=null)  {$(boxToCheck).prop("checked",true);}
		spaceAffectationsLabel();
	});

	//// Click la checkbox d'une affectation
	$(".spaceAffectInput").on("change",function(){
		var targetId=this.value.split("_")[0];																//Id de l'user ou espace (ex: "55_2" -> "55")
		$("[name='spaceAffect[]'][value^='"+targetId+"_']:not(:disabled)").not(this).prop("checked",false);	//Uncheck les autres box de l'user ou espace (sauf celles disabled)
		spaceAffectationsLabel();																			//Stylise les labels
	});

	//// Init le style des labels
	spaceAffectationsLabel();
};

/**************************************************************************************************
 * APPLIQUE UN STYLE À CHAQUE LABEL, EN FONCTION DE LA CHECKBOX COCHÉE
 **************************************************************************************************/
function spaceAffectationsLabel()
{
	//Réinit le style des affectations
	$(".spaceAffectLine").removeClass("lineSelect sAccessRead sAccessWrite");
	//Stylise les labels && la ligne sélectionnées
	$(".spaceAffectInput:checked").each(function(){
		var targetId   =this.value.split("_")[0];	//Id de l'user ou espace (ex: "55_2" -> "55")
		var targetRight=this.value.split("_")[1];	//Droit "user" ou "admin" (ex: "55_2" -> "2")
		if(targetRight=="1")		{$("#targetLine"+targetId).addClass("lineSelect sAccessRead");}		//Sélectionne la box "user"
		else if(targetRight=="2")	{$("#targetLine"+targetId).addClass("lineSelect sAccessWrite");}	//Sélectionne la box "admin"
	});
}

/**************************************************************************************************
 * VALEUR D'UN PARAMETRE DANS UNE URL
 **************************************************************************************************/
function urlParam(paramName, url)
{
	if(/(msie|trident)/i.test(window.navigator.userAgent)==false){			//Verif si le browser prend en charge "URL.SearchParams" (Safari aussi?)
		if(typeof url==="undefined")  {url=window.location.href;}			//Pas d'Url en paramètre : récupère l'Url de la page courante
		const urlParams=new URLSearchParams(url);							//Créé un objet 'URLSearchParams'
		if(urlParams.has(paramName))  {return urlParams.get(paramName);}	//Retourne le paramètre s'il existe	
	}
}

/**************************************************************************************************
 * SWITCH LE "LIKE" D'UN OBJET : UPDATE LE "circleNb"
 **************************************************************************************************/
function usersLikeUpdate(typeId)
{
	if(isValue(typeId)){
		$.ajax({url:"?ctrl=object&action=usersLike&typeId="+typeId, dataType:"json"}).done(function(result){		//Requête Ajax pour switcher le "like"
			var menuId="#usersLike_"+typeId;																		//Id du menu
			if(result.likeNb==0)	{$(menuId).addClass("hideMiscMenu").find(".circleNb").html("");}				//Masque l'icone et le nb de likes
			else					{$(menuId).removeClass("hideMiscMenu").find(".circleNb").html(result.likeNb);}	//Affiche l'icone..
			$(menuId).tooltipsterUpdate(result.likeTooltip);														//Tooltip le menu
			$(menuId).effect("pulsate",{times:1},300);																//Pulsate rapide du menu
		});
	}
}

/**************************************************************************************************
 * CHECK/UNCHECK UN GROUPE D'USERS
 * Tester : edition d'evt avec les groupes pour affectation aux agendas ET les groupes pour notification par email
 * Note : les inputs des groupes doivent avoir un "name" spécifique ET les inputs d'user doivent avoir une propriété "data-idUser"
 * On passe en paramètre le "this" de l'input du groupe ET l'id du conteneur des inputs d'users ("idContainerUsers") pour définir le périmère des inputs d'users
 **************************************************************************************************/
function userGroupSelect(thisGroup, idContainerUsers)
{
	//Check/uncheck chaque users du groupe
	var idUsers=$(thisGroup).val().split(",");
	for(var tmpKey in idUsers)
	{
		//Groupe "checked" : check l'user du groupe  ||  Sinon on vérifie si l'user est aussi sélectionné dans un autre groupe
		if($(thisGroup).prop("checked"))  {var userChecked=true;}
		else{
			var userChecked=false;
			$("[name='"+thisGroup.name+"']:checked").not(thisGroup).each(function(){
				var otherGroupUserIds=this.value.split(",");
				if($.inArray(idUsers[tmpKey],otherGroupUserIds)!==-1)  {userChecked=true;}
			});
		}
		//Check l'user courant
		$(idContainerUsers+" input[data-idUser="+idUsers[tmpKey]+"]:enabled").prop("checked",userChecked).trigger("change");//"trigger" pour le style du label
	}
}

/**************************************************************************************************
 * LANCE UNE VISIO (SI BESOIN AVEC LE NOM DES USERS CONCERNES DANS L'URL)
 **************************************************************************************************/
function launchVisio(visioURL)
{
	lightboxOpen("?ctrl=misc&action=LaunchVisio&visioURL="+encodeURIComponent(visioURL));
}


/******************************************************************************************************************************************
 **************************************************************************************          SURCHARGES JQUERY          ***************
 ******************************************************************************************************************************************/


/**************************************************************************************************
 * NOUVELLES FONCTIONS JQUERY
 **************************************************************************************************/
////	Vitesse par défaut des effets "fadeIn()", "toggle()", etc
$.fx.speeds._default=100;
////	Verifie l'existance d'un element
$.fn.exist=function(){
	return (this.length>0);
};
////	Verifie si l'element ou l'input est vide
$.fn.isEmpty=function(){
	return (this.length==0 || this.val().trim().length==0);
};
////	Verifie si l'element ou l'input n'est pas vide
$.fn.notEmpty=function(){
	return (this.isEmpty()==false);
};
////	Verifie si l'element est visible
$.fn.isVisible=function(){
	return this.is(":visible");
};
////	Vérifie si l'element est un email (cf. "isMail()")
$.fn.isMail=function(){
	return isMail(this.val());
};
////	Clignotement / "Blink" d'un element (toute les secondes et 4 fois par défaut : cf. "times")
$.fn.pulsate=function(pTimes){
	if(typeof pTimes=="undefined")  {var pTimes=4;}
	this.effect("pulsate",{times:parseInt(pTimes)},parseInt(pTimes*1000));
};
////	Focus alternatif à la fin du texte (sauf sur mobile : cf. clavier virtuel, et uniquement sur certains inputs)
$.fn.focusAlt=function(){
	if(isTouchDevice()==false && this.is("input[type='text'],input[type='password'],textarea")){
		this.focus();
		this[0].setSelectionRange(this[0].value.length,this[0].value.length);//Place le curseur en fin de texte
	}
};
////	Focus et pulsate via css  (.focusPulsate durant 20 secondes)
$.fn.focusPulsate=function(){
	this.addClass("focusPulsate").focusAlt();
	let focusInput=this;
	setTimeout(function(){  $(focusInput).removeClass("focusPulsate");  },20000);
};
////	Renvoie la hauteur totale des élements sélectionnées (marge comprise)
$.fn.totalHeight=function(){
	let tmpHeight=0;
	this.each(function(){ tmpHeight+=$(this).outerHeight(true); });
	return Math.floor(tmpHeight);
};
////	Scroll vers un element de la page
$.fn.scrollTo=function(){
	let scrollTopPos=$(this).offset().top - parseInt($("#headerBar,#headerBarCenter").height()) - 15;//Soustrait la barre de menu principale
	$("html,body").animate({scrollTop:scrollTopPos},300);
};
////	Update le title et reload le tooltipster
$.fn.tooltipsterUpdate=function(title){
	$(this).attr("title",title).tooltipster("destroy").tooltipster(tooltipsterParams);
};

/**************************************************************************************************
 * SURCHARGE DE FONCTIONS JQUERY POUR AGRANDIR AUTOMATIQUEMENT LES LIGHTBOXES
**************************************************************************************************/
ready(function(){
	if(isMainPage!==true){
		var fadeInBASIC=$.fn.fadeIn;
		var showBASIC=$.fn.show;
		var toggleBASIC=$.fn.toggle;
		var slideToggleBASIC=$.fn.slideToggle;
		var slideDownBASIC=$.fn.slideDown;
		var fadeToggleBASIC=$.fn.fadeToggle;
		$.fn.fadeIn=function(){			lightboxResize();	return fadeInBASIC.apply(this,arguments); };
		$.fn.show=function(){			lightboxResize();	return showBASIC.apply(this,arguments); };
		$.fn.toggle=function(){			lightboxResize();	return toggleBASIC.apply(this,arguments); };
		$.fn.slideToggle=function(){	lightboxResize();	return slideToggleBASIC.apply(this,arguments); };
		$.fn.slideDown=function(){		lightboxResize();	return slideDownBASIC.apply(this,arguments); };
		$.fn.fadeToggle=function(){		lightboxResize();	return fadeToggleBASIC.apply(this,arguments); };
	}
});