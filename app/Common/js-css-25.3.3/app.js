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
	if(document.readyState!="loading")	{thisFunction();}
	document.addEventListener("DOMContentLoaded",thisFunction);
}

/**************************************************************************************************
 * VARIABLES ET FONCTIONS PRINCIPALES
 **************************************************************************************************/
ready(function(){
	mainDisplay();													//Affichage principal
	window.addEventListener("resize",function(){ mainDisplay(); });	//Relance si windows resize (ou changeorientation)
	tooltipDisplay();												//Affichage des tooltip
	mainTriggers();													//Triggers principaux
	menuContext();													//Affichage des menus contextuels
	controleFields();												//Affichage et controle des champs de formulaire
});

/**************************************************************************************************
 * AFFICHAGE PRINCIPAL
 **************************************************************************************************/
function mainDisplay()
{
	////	Variables de base
	isMainPage=(window.self==window.top);															//Page principale ou Lightbox
	if(typeof window.top.confirmCloseForm=="undefined")  {window.top.confirmCloseForm=false;}		//Formulaire en cours d'édition
	windowWidth=document.documentElement.clientWidth;												//Width de la fenêtre (sans scrollbar)
	windowHeight=document.documentElement.clientHeight;												//Height de la fenêtre (idem)
	containerWidth=(isMobile()) ? (windowWidth) : (windowWidth-$("#pageMenu").outerWidth(true)-12);	//Width du container de la page (-12px de scroolbar)

	////	Fenêtre principale
	if(isMainPage==true){
		////	Affichage spécifique d'un module (ModCalendar, ModTask, etc)
		if(typeof moduleDisplay=="function")  {moduleDisplay();}

		////	Width des objets en affichage "block"
		if($(".objBlocks .objContainer").exist()){
			let marginRight=parseInt($(".objContainer").css("margin-right"));									//Marges de l'objet (cf. "app.css")
			let widthMin=parseInt($(".objContainer").css("min-width")) + marginRight;							//width Min
			let widthMax=parseInt($(".objContainer").css("max-width")) + marginRight;							//width Max
			let lineNbObjs=Math.ceil(containerWidth / widthMax);												//Nb maxi d'objets par ligne
			if(containerWidth < (widthMin*2))				{widthObj=containerWidth;}							//On peut afficher qu'un objet par ligne : prend toute la largeur
			else if($(".objContainer").length<lineNbObjs)	{widthObj=widthMax;}								//Pas assez d'objets pour remplir la 1ère ligne : largeur max
			else											{widthObj=Math.floor(containerWidth/lineNbObjs);}	//Width en fonction du width disponible et du nb d'objets par ligne
			$(".objContainer").outerWidth(widthObj,true);														//Applique le width des objets (true pour prendre en compte les margins)
		}

		////	Width de la fenêtre enregistré dans un Cookie (path courant & racine)
		if(typeof mainDisplayTimeout!="undefined")  {clearTimeout(mainDisplayTimeout);}//Un seul timeout
		mainDisplayTimeout=setTimeout(function(){
			document.cookie="windowWidth="+windowWidth+"; Max-Age=31536000; Priority=High; SameSite=lax;";
			document.cookie="windowWidth="+windowWidth+"; Max-Age=31536000; Priority=High; SameSite=lax; path=/;";
		},100);
	}
}

/**************************************************************************************************
 * INIT/UPDATE DES "TITLE" VIA TOOLTIPSTER
 **************************************************************************************************/
function tooltipDisplay()
{
	if(typeof tooltipDisplayTimeout!="undefined")  {clearTimeout(tooltipDisplayTimeout);}//Un seul timeout
	tooltipDisplayTimeout=setTimeout(function(){
		tooltipsterParams={theme:'tooltipster-shadow',contentAsHTML:true};						//Theme et Affichage Html
		$("[title]:not(.noTooltip,[title=''],[title*='http'])").tooltipster(tooltipsterParams);	//Tooltipster de base
		$("[title*='http']").tooltipster($.extend(tooltipsterParams,{interactive:true}));		//Tooltipster "interactive" pour accéder aux liens (modLink & co)
	},500);
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
			if(typeof pageMenuTimeout!="undefined")  {clearTimeout(pageMenuTimeout);}//Un seul timeout
			pageMenuTimeout=setTimeout(function(){
				let menuHeight=$("#pageMenu").position().top;													//Position top du menu
				$("#pageMenu").children().each(function(){ menuHeight+=$(this).outerHeight(true); });			//Ajoute la hauteur de chaque element
				if(menuHeight < windowHeight)  {$("#pageMenu").css("padding-top",$(window).scrollTop()+"px");}	//Repositionne le menu en fonction de la fenêtre
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
		$("#mainForm").find("input,select,textarea").on("input change keyup",function(){ window.top.confirmCloseForm=true; });
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
		let bgColor=$(this).find("option:selected").attr("data-color");
		if(isValue(bgColor))	{$(this).css({color:'white',background:bgColor});}
		else					{$(this).css({color:'black',background:'white'});}
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
 * MENU CONTEXTUEL
 **************************************************************************************************/
function menuContext()
{
	////	Affichages / Masquages principaux
	$(".menuLauncher").on("click",function(event){  isMobile() ? menuMobileShow(this) : menuContextShow(this,event);  });	//Affiche si click sur .menuLauncher
	$(".objContainer").on("contextmenu",function(event){  menuContextShow(this,event);  return false;  });					//Affiche si click droit sur .objContainer (return false pour annuler le menu du browser)
	$(".menuContext").on("mouseleave",function(){  $(".menuContext").hide();  });											//Masque si mouseleave sur .menuContext
	$("#menuMobileClose,#menuMobileBg").on("click",function(){  menuMobileClose();  });										//Masque si click sur #menuMobileClose ou #menuMobileBg
	$(".menuLauncher,.menuContext,[href],[onclick]").on("click",function(event){  event.stopPropagation();  });				//Pas de propagation de click (evite un download ou une sélection via "objSelectSwitch()")

	////	Swipe sur mobile
	if(isMobile()){
		pageScrolled=false;
		swipeMenuActive=true;
		document.addEventListener("touchstart",function(event){																//Début de swipe :
			swipeXstart=event.touches[0].clientX;																			//Position X de départ
			swipeYstart=event.touches[0].clientY;																			//Position Y de départ
		});	
		document.addEventListener("touchmove",function(event){																//Swipe en cours :
			if(pageScrolled==false && Math.abs(swipeYstart-event.touches[0].clientY) < 50){									//Aucun scroll en cours && swipe d'amplitude verticale < 50px
				let swipeDiff=(swipeXstart - event.touches[0].clientX);														//Diff entre la position X de départ et celle de fin
				if(swipeMenuActive==true && swipeDiff > 100 && (windowWidth-swipeXstart)<250)	{menuMobileShow();}								//Swipe gauche > 100px et < 250px du bord de page : affiche
				else if(swipeDiff < -10)															{menuMobileClose(event.touches[0].clientX);}	//swipe droit > 10px : masque le menu (meme si swipeMenuActive==false)
			}
		});
		document.addEventListener("touchend",function(){																	//Fin de swipe :
			if(parseInt($("#menuMobileMain").css("right"))<0)  {$("#menuMobileMain").css("right","0px");}					//Masque si besoin le #menuMobileMain
			swipeXstart=swipeYstart=0;																						//Réinit les positions
		});
		//// Verif si un scroll est en cours sur la page
		$(window).add("div").on("scroll",function(){																		//Add "div" : cf. menus horizontaux scrollables (Task Gantt, tinyMce mobile, etc)
			pageScrolled=true;																								//Scroll en cours
			if(typeof scrollPageTimeout!="undefined")  {clearTimeout(scrollPageTimeout);}									//Un seul timeout
			scrollPageTimeout=setTimeout(function(){ pageScrolled=false; },1000);											//Réinitialise le scroll avec un timeout, le tps de charger le tinyMce mobile/horizontal
		});
	}
}

/**************************************************************************************************
 * MENU CONTEXTUEL : AFFICHE SUR DESKTOP
 **************************************************************************************************/
function menuContextShow(launcher, event)
{
	let menuId='#'+$(launcher).attr("for");																								//Id du menu à afficher : attribut "for" du .menuLauncher
	$(menuId).css("max-height", (windowHeight-20)+"px");																				//Hauteur max en fonction de la page (#menuMobileMain en "overflow:auto")
	let parentPosition=$(menuId).parents().is(function(){  return (/(relative|absolute)/i.test($(this).css("position")));  });			//Vérif si un des parents est en position relative/absolute
	if(event.type=="contextmenu")	{var posX=event.pageX-$(launcher).offset().left;  var posY=event.pageY-$(launcher).offset().top;}	//Position en fonction du click droit sur .objContainer
	else if(parentPosition==true)	{var posX=$(launcher).position().left;			  var posY=$(launcher).position().top;}				//Position en fonction de .launcher par rapport au parent
	else							{var posX=$(launcher).offset().left;			  var posY=$(launcher).offset().top;}				//Position en fonction de .launcher par rapport au document
	let posRight =posX + $(menuId).outerWidth(true);																					//Position du bord right du menu
	let posBottom=posY + $(menuId).outerHeight(true);																					//Position du bord bottom du menu
	if(parentPosition==true)  {posRight+=$(menuId).parent().offset().left;  posBottom+=$(menuId).parent().offset().top;}				//Ajoute si besoin la position du parent
	let pageBottomPosition=windowHeight+$(window).scrollTop();																			//Vérif si le menu est près du bottom de la fenêtre
	if(windowWidth < posRight)	{posX-=(posRight-windowWidth);}																			//Décale le menu s'il est au bord droit de la fenêtre
	if(pageBottomPosition < posBottom)	{posY-=(posBottom-pageBottomPosition);}															//Décale le menu s'il est en bas de la fenêtre
	$(menuId).css({left:posX-8, top:posY-8}).fadeIn(200);																				//Affiche le menu (recentré de 8px)
	$(".menuContext").not(menuId).hide();																								//Masque les autres menus
}

/**************************************************************************************************
 * MENU CONTEXTUEL : AFFICHE SUR MOBILE
 **************************************************************************************************/
function menuMobileShow(launcher)
{
	idMenuMain=(launcher)  ?  '#'+$(launcher).attr("for")  :  "#headerMenuMain";				//Menu à afficher : attribut "for" du .menuLauncher ou "#headerMenuMain" si menu "swipé"
	idMenuModule=(idMenuMain=="#headerMenuMain")  ?  "#pageMenu"  :  null;						//Menu du module à ajouter (menu de gauche en affichage Desktop)
	if($(idMenuMain).exist() && $("#menuMobileMain").isVisible()==false){						//Vérif que idMenuMain existe et qu'un menu n'est pas déjà ouvert
		$(idMenuMain+">*").appendTo("#menuMobileContent");										//Déplace idMenuMain dans "#menuMobileContent"
		if($(idMenuModule).exist())  {$(idMenuModule+">*").appendTo("#menuMobileContent2");}	//Déplace idMenuModule dans "#menuMobileContent2"
		$("#menuMobileBg,#menuMobileContent,#menuMobileContent2").show();						//Affiche le contenu du menu
		$("#menuMobileMain").css("right","0px").show("slide",{direction:"right",duration:200});	//Réinit la position puis affiche #menuMobileMain progressivement
		$("body").css("overflow","hidden");														//Désactive le scroll de page en arriere plan
	}
}

/**************************************************************************************************
 * MENU CONTEXTUEL : MASQUE SUR MOBILE
 **************************************************************************************************/
function menuMobileClose(swipeXcurrent)
{
	if($("#menuMobileMain").isVisible()){														//Vérif si le menu est visible
		if(swipeXcurrent && parseInt($("#menuMobileMain").css("right")) > -150)					//Masque progressivement le menu sur les 150 premiers pixels de swipe
			{$("#menuMobileMain").css("right", "-"+(swipeXcurrent-swipeXstart)+"px");}			//Positionne le menu en fonction de swipeXstartCurrent
		else{																					//Masque directement le menu
			$("#menuMobileBg,#menuMobileContent,#menuMobileContent2").hide();					//Masque le contenu du menu
			$("#menuMobileMain").hide("slide",{direction:"right",duration:200});				//Masque #menuMobileMain
			$("#menuMobileContent>*").appendTo(idMenuMain);										//Replace le contenu de "#menuMobileContent" dans son div d'origine 
			if($(idMenuModule).exist())  {$("#menuMobileContent2>*").appendTo(idMenuModule);}	//Replace le contenu de "#menuMobileContent2"
			$("body").css("overflow","visible");												//Réactive le scroll de page en arriere plan
		}
	}
}

/**************************************************************************************************
 * AFFICHAGE MOBILE / RESPONSIVE SI WIDTH <= 1024PX  (Idem CSS et Req.php)
 **************************************************************************************************/
function isMobile()
{
	return (window.top.document.body.clientWidth <= 1024);
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
	let mailRegex=/^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
	return mailRegex.test(mail);
}

/**************************************************************************************************
 * CONTROLE LA VALIDITE D'UN PASSWORD : AU MOINS 6 CARACTÈRES, UN CHIFFRE ET UNE LETTRE
 **************************************************************************************************/
function isValidPassword(password)
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
	if(await confirmAlt(confirmTitle))
		{window.top.location.href=locationUrl;}
}

/**************************************************************************************************
 * REDIRECTION (ASYNC ET A CONFIRMER, SI UN FORMULAIRE EN COURS D'EDITION)
 **************************************************************************************************/
async function redir(locationUrl)
{
	if(window.top.confirmCloseForm==false || await confirmAlt(labelConfirmCloseForm))
		{window.top.location.href=locationUrl;}
}

/**************************************************************************************************
 * REDIRECTION VIA HREF (ASYNC ET A CONFIRMER, SI UN FORMULAIRE EN COURS D'EDITION)
 **************************************************************************************************/
ready(function(){
	$("a[href]:not([data-fancybox])").click(async function(event){
		event.preventDefault();
		if(window.top.confirmCloseForm==false || await confirmAlt(labelConfirmCloseForm)){
			let hrefUrl=$(this).attr("href");
			($(this).attr("target")=="_blank") ? window.top.open(hrefUrl) : window.top.location.href=hrefUrl;
		}
	});
});

/**************************************************************************************************
 * SUBMIT D'UN FORMULAIRE : AFFICHE L'IMG "LOADING" & "DISABLE" LES BUTTONS SUBMIT
 **************************************************************************************************/
function submitLoading()
{
	$(".submitLoading").css("visibility","visible");
	$("button[type='submit']").css("background","#eee").prop("disabled",true);
	setTimeout(function(){
		$(".submitLoading").css("visibility","hidden");
		$("button[type='submit']").css("background","initial").prop("disabled",false);
	 },2000);
}

/**************************************************************************************************
 * SUBMIT ASYNCHRONE D'UN FORMULAIRE  ("async" et "preventDefault()" préalables)
 **************************************************************************************************/
function asyncSubmit(thisForm)
{
	submitLoading();															//Affiche l'img "loading"
	if(typeof attachedFileSrcReplace=="function")  {attachedFileSrcReplace();}	//Remplace le Src des images temporaire de l'éditeur tinymce
	$(thisForm).off("submit").submit();											//Validation finale du formulaire (sans "submit" récursif via "off()")
}

/**************************************************************************************************
 * CONFIRME UNE SUPPRESSION AVEC REDIRECTION  ("labelConfirmXXX" : cf. "VueStructure.php")
 **************************************************************************************************/
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

/**************************************************************************************************
 * OUVRE UNE LIGHTBOX
 **************************************************************************************************/
function lightboxOpen(fileSrc)
{
	////	Ouvre depuis une lightbox : relance depuis la page "parent"
	if(isMainPage==false)		{window.top.$.fancybox.close();  window.top.lightboxOpen(fileSrc);}
	////	Ouvre un pdf sur mobile : nouvelle page
	else if(/\.pdf$/i.test(fileSrc) && isMobile())	{window.top.open(fileSrc);}			
	////	Ouvre un mp3 ou une video
	else if(/\.(mp3|mp4|webm)$/i.test(fileSrc)){
		let fileTag=(/mp3/i.test(fileSrc))  ?  '<div><audio controls><source src="'+fileSrc+'" type="audio/mpeg"></audio></div>'  :  '<video controls><source src="'+fileSrc+'" type="video/'+extension(fileSrc)+'"></video>';
		$.fancybox.open({type:"inline", src:fileTag, buttons:['close']});
	}
	////	Lightbox "iframe"
	else{
		$.fancybox.open({
			type:"iframe",
			src:fileSrc,
			buttons:['close'],
			autoFocus:false,
			opts:{
				beforeClose:function(){
					if(confirmCloseForm==true && typeof lightboxCloseOk==="undefined"){									//Fermeture du formulaire à confirmer
						confirmAlt(labelConfirmCloseForm).then(()=>{ lightboxCloseOk=true;  $.fancybox.close(); });		//Fermeture confirmée => ferme la lightbox récursivement, avec relance du "beforeClose"
						return false;																					//Suspend la fermeture en attendant le résultat du "confirmAlt()"
					}else{confirmCloseForm=false; delete lightboxCloseOk;}												//Sortie du formulaire confirmé : réinit (tester redir() et réouverture de lightbox)
				}
			}
		});
	}
}

/**************************************************************************************************
 * INIT LE WIDTH DE LA LIGHTBOX COURANTE
 **************************************************************************************************/
function lightboxWidth(pageWidth)
{
	ready(function(){
		if(/px/i.test(pageWidth))		{pageWidth=pageWidth.replace("px","");}									//Width en pixel : converti en "Integer"
		else if(/%/.test(pageWidth))	{pageWidth=($(window.top).width()/100) * pageWidth.replace("%","");}	//Width en % : converti en pixel
		if(pageWidth>$(window.top).width())  {pageWidth=$(window.top).width();}									//Width supérieur à window.top : on prend le width de window.top
		if(isValue(pageWidth))  {$("body").css("max-width",parseInt(pageWidth));}								//Applique le "max-width" (pas "width" car peut afficher un scroll horizontal après un "lightboxResize()")
	});
}

/**************************************************************************************************
 * REDUIT/AGRANDIT LA HAUTEUR DE LA LIGHTBOX COURANTE  (cf. show() ou fadeIn() depuis la lightbox)
 **************************************************************************************************/
function lightboxResize()
{
	if(window.top.$(".fancybox-iframe").isVisible()){																				//Verif si le lightbox est affiché
		if(typeof lightboxResizeTimeout!="undefined")  {clearTimeout(lightboxResizeTimeout);}										//Un seul timeout (cf. multiples show(), fadeIn(), etc)
		lightboxResizeTimeout=setTimeout(function(){																				//Lance le resize avec un timeout 200ms minimum (cf. "$.fx.speeds._default=100")
			if(typeof lightboxHeightOld=="undefined" || lightboxHeightOld < window.top.$(".fancybox-iframe").contents().height()){	//Verif : 1er affichage du lightbox ou "fadeIn()" ou modif du tinymce
				window.top.$.fancybox.getInstance().update();																		//Resize du lightbox!
				lightboxHeightOld=window.top.$(".fancybox-iframe").contents().height();												//Enregistre la taille du contenu du lightbox (après update)
			}
		},200);
	}
}


/**********************************************************************************************************************************
 ****************************************************************************************************	          SURCHARGES JQUERY
 **********************************************************************************************************************************/


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
	setTimeout(function(){ $(focusInput).removeClass("focusPulsate"); },20000);
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
	if(isMainPage==false){
		let fadeInBASIC=$.fn.fadeIn;
		let showBASIC=$.fn.show;
		let toggleBASIC=$.fn.toggle;
		let slideToggleBASIC=$.fn.slideToggle;
		let slideDownBASIC=$.fn.slideDown;
		let fadeToggleBASIC=$.fn.fadeToggle;
		$.fn.fadeIn=function(){			lightboxResize();	return fadeInBASIC.apply(this,arguments); };
		$.fn.show=function(){			lightboxResize();	return showBASIC.apply(this,arguments); };
		$.fn.toggle=function(){			lightboxResize();	return toggleBASIC.apply(this,arguments); };
		$.fn.slideToggle=function(){	lightboxResize();	return slideToggleBASIC.apply(this,arguments); };
		$.fn.slideDown=function(){		lightboxResize();	return slideDownBASIC.apply(this,arguments); };
		$.fn.fadeToggle=function(){		lightboxResize();	return fadeToggleBASIC.apply(this,arguments); };
	}
});


/**********************************************************************************************************************************
 **************************************************************************************************           FONCTIONS SPECIFIQUES
 **********************************************************************************************************************************/


/**************************************************************************************************
 * AFFECTATIONS DES SPACES<->USERS : "VueSpaceEdit.php" & "VueUserEdit.php"
 **************************************************************************************************/
function spaceAffectations()
{
	//// Click le Label d'une affectation (sauf "allUsers")
	$(".spaceAffectLabel").on("click",function(){
		//init
		let _idTarget=$(this).parent().attr("id").replace("targetLine","");	//Id de l'user ou espace dans le div parent contenant "targetLine" (ex: "targetLine55" -> "55")
		let box1=".spaceAffectInput[value='"+_idTarget+"_1']";				//Checkbox "user"
		let box2=".spaceAffectInput[value='"+_idTarget+"_2']";				//Checkbox "admin"
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
		let targetId=this.value.split("_")[0];																//Id de l'user ou espace (ex: "55_2" -> "55")
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
		let targetId   =this.value.split("_")[0];	//Id de l'user ou espace (ex: "55_2" -> "55")
		let targetRight=this.value.split("_")[1];	//Droit "user" ou "admin" (ex: "55_2" -> "2")
		if(targetRight=="1")		{$("#targetLine"+targetId).addClass("lineSelect sAccessRead");}		//Sélectionne la box "user"
		else if(targetRight=="2")	{$("#targetLine"+targetId).addClass("lineSelect sAccessWrite");}	//Sélectionne la box "admin"
	});
}

/**************************************************************************************************
 * VALEUR D'UN PARAMETRE DANS UNE URL
 **************************************************************************************************/
function urlParam(paramName, url)
{
	if(typeof url==="undefined")  {url=window.location.href;}			//Url de la page courante
	const urlParams=new URLSearchParams(url);							//Créé un objet 'URLSearchParams'
	if(urlParams.has(paramName))  {return urlParams.get(paramName);}	//Retourne le paramètre s'il existe	
}

/**************************************************************************************************
 * LANCE UNE VISIO (SI BESOIN AVEC LE NOM DES USERS CONCERNES DANS L'URL)
 **************************************************************************************************/
function launchVisio(visioURL)
{
	lightboxOpen("?ctrl=misc&action=LaunchVisio&visioURL="+encodeURIComponent(visioURL));
}

/**************************************************************************************************
 * SWITCH LE "LIKE" D'UN OBJET : UPDATE LE "circleNb"
 **************************************************************************************************/
function usersLikeUpdate(typeId)
{
	if(isValue(typeId)){
		$.ajax({url:"?ctrl=object&action=usersLike&typeId="+typeId, dataType:"json"}).done(function(result){		//Requête Ajax pour switcher le "like"
			let menuId="#usersLike_"+typeId;																		//Id du menu
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
	let idUsers=$(thisGroup).val().split(",");
	for(let tmpKey in idUsers){
		//Groupe "checked" : check l'user du groupe  ||  Sinon on vérifie si l'user est aussi sélectionné dans un autre groupe
		if($(thisGroup).prop("checked"))  {var userChecked=true;}
		else{
			var userChecked=false;
			$("[name='"+thisGroup.name+"']:checked").not(thisGroup).each(function(){
				let otherGroupUserIds=this.value.split(",");
				if($.inArray(idUsers[tmpKey],otherGroupUserIds)!==-1)  {userChecked=true;}
			});
		}
		//Check l'user courant
		$(idContainerUsers+" input[data-idUser="+idUsers[tmpKey]+"]:enabled").prop("checked",userChecked).trigger("change");//"trigger" pour le style du label
	}
}