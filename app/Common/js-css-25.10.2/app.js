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
	isMainPage=(window.self==window.top);																									//Page principale || Lightbox
	if(typeof window.top.confirmCloseForm==="undefined")	{window.top.confirmCloseForm=false;}											//Formulaire en cours d'édition : valider la fermeture de Page/Lightbox
	if(typeof window.top.windowWidth==="undefined")			{window.top.windowWidth=window.top.document.documentElement.clientWidth;}		//Width de la fenêtre principale (sans scrollbar)
	if(typeof window.top.windowHeight==="undefined")		{window.top.windowHeight=window.top.document.documentElement.clientHeight;}		//Height de la fenêtre principale (idem)
	containerWidth=isMobile() ?  window.top.windowWidth  :  (window.top.windowWidth - $("#moduleMenu").outerWidth(true) - 12);				//Width du container de la page (-12px de scroolbar)

	////	Fenêtre principale
	if(isMainPage==true){
		////	Affichage spécifique d'un module (Ex: ModCalendar, ModTask)
		if(typeof moduleDisplay=="function")  {moduleDisplay();}

		////	Width des objets en affichage "block"
		if($(".objBlocks .objContainer").exist()){
			let marginRight=parseInt($(".objContainer").css("margin-right"));									//Marges de l'objet (cf. "app.css")
			let widthMin=parseInt($(".objContainer").css("min-width")) + marginRight;							//width Min
			let widthMax=parseInt($(".objContainer").css("max-width")) + marginRight;							//width Max
			let lineNbObjs=Math.ceil(containerWidth / widthMax);												//Nb maxi d'objets par ligne : tester sur mobile !
			if(containerWidth < (widthMin*2))				{widthObj=containerWidth;}							//On peut afficher qu'un objet par ligne : prend toute la largeur
			else if($(".objContainer").length<lineNbObjs)	{widthObj=widthMax;}								//Pas assez d'objets pour remplir la 1ère ligne : largeur max
			else											{widthObj=Math.floor(containerWidth/lineNbObjs);}	//Width en fonction du width disponible et du nb d'objets par ligne
			$(".objContainer").outerWidth(widthObj,true);														//Applique le width des objets (true pour prendre en compte les margins)
		}

		////	Width de la fenêtre enregistré dans un Cookie
		if(typeof mainDisplayTimeout!="undefined")  {clearTimeout(mainDisplayTimeout);}											//Un seul timeout
		mainDisplayTimeout=setTimeout(function(){																				//Timeout le tps de finaliser un window resize (tps supérieur à $.fx.speeds)
			document.cookie="windowWidth="+window.top.windowWidth+"; Max-Age=31536000; Priority=High; SameSite=lax;";			//Path courant
			document.cookie="windowWidth="+window.top.windowWidth+"; Max-Age=31536000; Priority=High; SameSite=lax; path=/;";	//Path racine
		},150);
	}
}

/***************************************************************************************************************
 * PRINCIPAUX TRIGGERS :  FANCYBOX  +  CLICK / DBLCLICK D'OBJETS  +  MENUS FLOTTANT  +  TOOLTIPSTER  +  VISIOS
 ***************************************************************************************************************/
function mainTriggers()
{
	////	Fancybox : resize d'Iframe
	lightboxResize();

	////	Fancybox : images & inline (mode "Declarative")
	let fancyboxThumbs=isMobile() ? false : {type:"classic"};
	let fancyboxToolbar={
		display:{left:[], center:["zoomIn","rotateCW","slideshow","fullscreen","thumbs","close"]}
	};
	Fancybox.bind("[data-fancybox='images'],.fancyboxImages", {l10n:fancyboxLang, Thumbs:fancyboxThumbs, Toolbar:fancyboxToolbar});
	Fancybox.bind("[data-fancybox='inline']", {l10n:fancyboxLang, type:"html"});

	////	Click d'objet : sélection  ||  DblClick : édition
	$(".objContainer").off("click dblclick").on("click dblclick",function(event){	//"off()" réinitialise les triggers à chaque relance de "mainTriggers()"
		if(event.type=="dblclick" && $(this).attr("data-urlEdit"))			{lightboxOpen($(this).attr("data-urlEdit"));}
		else if(event.type=="click" && $(".objSelectCheckbox").exist())		{objSelectSwitch(this.id);}
	});

	////	Menu du module flottant
	if($("#moduleMenu").isDisplayed()){
		$(window).on("scroll",function(){
			if(typeof moduleMenuTimeout!="undefined")  {clearTimeout(moduleMenuTimeout);}									//Un seul timeout
			moduleMenuTimeout=setTimeout(function(){																		//Timeout le tps de finaliser le scroll
				let menuHeight=$("#moduleMenu").position().top;																//Position top du menu
				$("#moduleMenu").children().each(function(){ menuHeight+=$(this).outerHeight(true); });						//Ajoute la hauteur de chaque element
				if(menuHeight < window.top.windowHeight)  {$("#moduleMenu").css("padding-top",$(window).scrollTop()+"px");}	//Repositionne le menu en fonction de la fenêtre
			},200);
		});
	}

	////	Tooltipster : init/update les "title"
	tooltipParams={theme:'tooltipster-shadow',contentAsHTML:true};							//Theme et Affichage Html
	let timeoutDuration=$(".tooltipstered").exist() ? 1000 : 50;							//Timeout plus long si update des tooltips via ajax (ex: "messengerUpdate()")
	if(typeof tooltipDisplayTimeout!="undefined")  {clearTimeout(tooltipDisplayTimeout);}	//Un seul timeout
	tooltipDisplayTimeout=setTimeout(function(){											//Timeout le tps de charger
		$("[title]:not([title=''])").tooltipster(tooltipParams);							//Theme "shadow" et Affichage Html
	},timeoutDuration);

	////	Ouvre un lien <a href> via une lightbox (cf. HTMLPurifier)
	$("a.lightboxOpenHref").off("click").on("click",function(event){	//"off()" réinitialise les triggers à chaque relance de "mainTriggers()"
		event.preventDefault();
		lightboxOpen($(this).attr("href"));
	});
}

/**************************************************************************************************
 *  CONTROLES DES CHAMPS
 **************************************************************************************************/
function controleFields()
{
	////	Pas d'autocomplétion des inputs
	$("form input:not(.isAutocomplete)").attr("autocomplete","off");

	////	Formulaire édité : passe "confirmCloseForm" à true  (Timeout le tps de finaliser les 1ers controles de form)
	setTimeout(function(){
		$("#mainForm input, #mainForm select, #mainForm textarea").on("input change keyup",function(){  window.top.confirmCloseForm=true;  });
	},500);

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
		$(".timeBegin, .timeEnd").timepicker({timeFormat:"H:i", step:15, "orientation":(isMobile()?"rb":"lb")});	//Orientation Right/Left + Bottom
		if(navigator.maxTouchPoints > 1 && /(iphone|ipad|macintosh)/i.test(navigator.userAgent)){					//Pas sur Iphone/Ipad car utilise le timePicker system ("macintosh" sur les ipads récents)
			$(".timeBegin, .timeEnd").on("showTimepicker",function(){  $(".timeBegin, .timeEnd").timepicker("hide");  });
		}
	}

	////	Init dateBeginRef + timeBeginRef (en millisecondes!)
	if($(".dateBegin").notEmpty())  {var dateBeginRef=$(".dateBegin").datepicker("getDate").getTime();}
	if($(".timeBegin").notEmpty())  {var timeBeginRef=$(".timeBegin").timepicker("getTime").getTime();}

	////	Datepicker/Timepicker : Controle du DateTime
	$(".dateBegin, .dateEnd, .timeBegin, .timeEnd").on("change",function(){
		//// Controle le format des dates et heures
		if( ($(this).hasClass("dateBegin") || $(this).hasClass("dateEnd"))  &&  $(this).notEmpty()  &&  /^\d{2}\/\d{2}\/\d{4}$/.test(this.value)==false)		{notify(labelDateFormatError);}
		if( ($(this).hasClass("timeBegin") || $(this).hasClass("timeEnd"))  &&  $(this).notEmpty()  &&  /^[0-2][0-9][:][0-5][0-9]$/.test(this.value)==false)	{notify(labelTimeFormatError);}
		//// dateBegin avancé/reculé : dateEnd ajusté
		if($(this).hasClass("dateBegin") && $(".dateEnd").notEmpty()){
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
			setTimeout(function(){																					//Timeout le tps de finaliser l'action du Timepicker
				notify(labelBeginEndError);																			//Notif "La date de début doit précéder la date de fin"
				$(".dateEnd").val($(".dateBegin").val());															//Date de fin = idem début 
				$(".timeEnd").val($(".timeBegin").val());															//Time de fin = idem début 
			},500);
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
	$(".menuContext").on("mouseleave",function(){  $(".menuContext").hide();  });											//Masque si mouseleave sur .menuContext
	$("#menuMobileClose,#menuMobileBg").on("click",function(){  menuMobileClose();  });										//Masque si click sur #menuMobileClose ou #menuMobileBg
	$(".menuLauncher,.menuContext,[href],[onclick]").on("click",function(event){  event.stopPropagation();  });				//Pas de propagation de click (evite un download ou une sélection via "objSelectSwitch()")
	if(!isMobile() && window.top.windowWidth>=1400){																		//Click droit sur .objContainer
		$(".objContainer").on("contextmenu",function(event){  menuContextShow(this,event);  return false;  });				//"return false" pour annuler le menu du browser
	}

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
				if(swipeMenuActive==true && swipeDiff > 100 && (window.top.windowWidth-swipeXstart)<250)  {menuMobileShow();}							//Swipe gauche > 100px et < 250px du bord de page : affiche
				else if(swipeDiff < -10)																  {menuMobileClose(event.touches[0].clientX);}	//swipe droit > 10px : masque le menu (meme si swipeMenuActive==false)
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
			scrollPageTimeout=setTimeout(function(){ pageScrolled=false; },500);											//Réinitialise le scroll : Timeout le tps de charger le tinyMce mobile/horizontal
		});
	}
}

/**************************************************************************************************
 * MENU CONTEXTUEL : AFFICHE SUR DESKTOP
 **************************************************************************************************/
function menuContextShow(launcher, event)
{
	let menuId="#"+$(launcher).attr("for");																										//Id du menu à afficher : attribut "for" de .menuLauncher
	$(menuId).css("max-height", (window.top.windowHeight - 20)+"px");																			//Hauteur max en fonction de la page (#menuMobileMain en "overflow:auto")
	let parentPosition=$(menuId).parents().is(function(){  return (/(relative|absolute)/i.test($(this).css("position")));  });					//Vérif si un des parents est en position relative/absolute
	if(event.type=="contextmenu")	{var posX=(event.pageX - $(launcher).offset().left);	var posY=(event.pageY - $(launcher).offset().top);}	//Position en fonction du click droit sur .objContainer
	else if(parentPosition==true)	{var posX=$(launcher).position().left;			 		var posY=$(launcher).position().top;}				//Position en fonction de .launcher par rapport au parent
	else							{var posX=$(launcher).offset().left;					var posY=$(launcher).offset().top;}					//Position en fonction de .launcher par rapport au document
	let posRight =posX + $(menuId).outerWidth(true);																							//Position du bord right du menu
	let posBottom=posY + $(menuId).outerHeight(true);																							//Position du bord bottom du menu
	if(parentPosition==true)  {posRight+=$(menuId).parent().offset().left;  posBottom+=$(menuId).parent().offset().top;}						//Ajoute si besoin la position du parent
	let pageBottomPosition=(window.top.windowHeight + $(window).scrollTop());																	//Vérif si le menu est près du bottom de la fenêtre
	if(window.top.windowWidth < posRight)	{posX-=(posRight - window.top.windowWidth);}														//Décale le menu s'il est au bord droit de la fenêtre
	if(pageBottomPosition < posBottom)		{posY-=(posBottom - pageBottomPosition);}															//Décale le menu s'il est en bas de la fenêtre
	$(menuId).css({left:posX-8, top:posY-8}).fadeIn(200);																						//Affiche le menu (recentré de 8px)
	$(".menuContext").not(menuId).hide();																										//Masque les autres menus
}

/**************************************************************************************************
 * MENU CONTEXTUEL : AFFICHE SUR MOBILE
 **************************************************************************************************/
function menuMobileShow(launcher)
{
	if(typeof menuMobileTimeout!="undefined")  {clearTimeout(menuMobileTimeout);}						//Un seul timeout
	menuMobileTimeout=setTimeout(function(){															//Timeout le tps de finaliser le swipe
		if($("#menuMobileMain").isDisplayed()){															//Menu mobile déjà affiché : Affiche un sous-menu
			$("#"+$(launcher).attr("for")).addClass("menuMobileSubMenu").slideToggle();					
		}else{																							//Affiche le Menu mobile :
			idMenuMobile1=(launcher)  ?  "#"+$(launcher).attr("for")  :  "#headerMenuRight";			//idMenuMobile1 : attr. "for" du launcher ou #headerMenuRight si swipe (liste des modules ou autre)
			idMenuMobile2=(idMenuMobile1=="#headerMenuRight")  ?  "#moduleMenu"  :  null;				//Affiche aussi #moduleMenu (menu de gauche)
			if($(idMenuMobile1).exist()){																//Vérif l'exisence de idMenuMobile1
				$(idMenuMobile1+">*").appendTo("#menuMobileContent1");									//Déplace le contenu de idMenuMobile1 dans menuMobileContent1
				if($(idMenuMobile2).exist())  {$(idMenuMobile2+">*").appendTo("#menuMobileContent2");}	//Déplace le contenu de idMenuMobile2 dans #menuMobileContent2
				$("#menuMobileBg,#menuMobileContent1,#menuMobileContent2").show();						//Affiche le/les contenus
				$("#menuMobileMain").css("right","0px").show("slide",{direction:"right"});				//Réinit la position puis affiche #menuMobileMain
				$("body").css("overflow","hidden");														//Désactive le scroll de page en arriere plan
			}
		}
	},50);
}

/**************************************************************************************************
 * MENU CONTEXTUEL : MASQUE SUR MOBILE
 **************************************************************************************************/
function menuMobileClose(swipeXcurrent)
{
	if($("#menuMobileMain").isDisplayed()){															//Vérif si le menu mobile est visible
		if(swipeXcurrent && parseInt($("#menuMobileMain").css("right")) > -100){					//Masque progressivement le menu sur les 100 premiers pixels de swipe :
			$("#menuMobileMain").css("right", "-"+(swipeXcurrent-swipeXstart)+"px");				//Repositionne en fonction de swipeXstartCurrent
		}else{
			$("#menuMobileBg,#menuMobileContent1,#menuMobileContent2").hide();						//Masque complètement le menu
			$("#menuMobileMain").hide("slide",{direction:"right"});									//Masque #menuMobileMain
			$("#menuMobileContent1>*").appendTo(idMenuMobile1);										//Replace le contenu de menuMobileContent1 dans son div d'origine 
			if($(idMenuMobile2).exist())  {$("#menuMobileContent2>*").appendTo(idMenuMobile2);}		//Replace le contenu de menuMobileContent2 dans son div d'origine 
			$("body").css("overflow","visible");													//Réactive le scroll de page en arriere plan
		}
	}
}

/**************************************************************************************************
 * VÉRIF AFFICHAGE MOBILE/RESPONSIVE <= 1024PX (Idem CSS & JS)
 **************************************************************************************************/
function isMobile()
{
	return (window.top.windowWidth <= 1024);
}

/**************************************************************************************************
 * VÉRIF AFFICHAGE SUR DEVICE TACTILE
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

/***********************************************************************************************************************
 * CONTROLE LA VALIDITE D'UN PASSWORD : 6 CARACTÈRES MINIMUM, AVEC AU MOINS UNE MAJUSCULE, UNE MINUSCULE ET UN CHIFFRE
 ***********************************************************************************************************************/
function isValidPassword(password)
{
	return /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{6,}$/.test(password);
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
 * ASYNC : REDIRECTION A CONFIRMER
 **************************************************************************************************/
async function confirmRedir(locationUrl, confirmTitle)
{
	if(await confirmAlt(confirmTitle))
		{window.top.location.href=locationUrl;}
}

/**************************************************************************************************
 * CONFIRME UNE SUPPRESSION AVEC REDIRECTION  (labelConfirmDelete de "VueStructure.php")
 **************************************************************************************************/
async function confirmDelete(deleteUrl, confirmContentAdd, ajaxControlUrl)
{
	let confirmContent='<div class="confirmDeleteAlert">'+labelConfirmDeleteAlert+'</div>';											// Détail du confirm "Attention : cette action est définitive !"
	if(isValue(confirmContentAdd))  {confirmContent+='<img src="app/img/arrowRight.png"> '+confirmContentAdd;}						// Ajoute le label de l'objet, le nb d'objets sélectionnés, etc.
	if(await confirmAlt(labelConfirmDelete,confirmContent)){																		// Confirm "Confirmer la suppression ?"
		if(!isValue(ajaxControlUrl))  {window.location.href=deleteUrl;}																// Suppression directe (pas de "window.top.location" : cf. lightbox des commentaires ou autre)
		else{																														// Controle Ajax avant suppression de dossier
			$.ajax({url:ajaxControlUrl, dataType:"json"}).done(async function(result){												// Lance le controle Ajax
				if(result.confirmDeleteWait) 													{notify(result.confirmDeleteWait);}	// "Merci de patienter un instant" pour les gros dossiers
				if(result.confirmDeleteFolder && await confirmAlt(result.confirmDeleteFolder))	{window.location.href=deleteUrl;}	// "Certains ss-dossiers ne sont pas accessibles...confirmer ?"
				else																			{window.location.href=deleteUrl;}	// Suppression directe
			});
		}
	}
}

/**************************************************************************************************
 * ASYNC : REDIRECTION (CONFIRM SI UN FORMULAIRE EN COURS D'EDITION)
 **************************************************************************************************/
async function redir(locationUrl)
{
	if(window.top.confirmCloseForm==false || await confirmAlt(labelConfirmCloseForm))
		{window.top.location.href=locationUrl;}
}

/**************************************************************************************************
 * REDIRECTION HREF : CONFIRMATION ASYNCHRONE SI FORMULAIRE EN COURS D'EDITION
 **************************************************************************************************/
ready(function(){
	//":not()" :  "_blank" ouvre une nouvelle fenêtre  et  "[data-fancybox]" + "a.lightboxOpenHref" sont lancés via mainTriggers()
	$("a[href]:not([target='_blank'],[data-fancybox],.lightboxOpenHref)").click(async function(event){
		event.preventDefault();
		if(window.top.confirmCloseForm==false || await confirmAlt(labelConfirmCloseForm))
			{window.top.location.href=$(this).attr("href");}
	});
});

/********************************************************************************************************************************
 * SUBMIT UN FORMULAIRE : AFFICHE L'IMG "LOADING" + "DISABLE" LES BUTTONS SUBMIT (2 sec max : cf ajax form record)
 ********************************************************************************************************************************/
function submitLoading()
{
	$(".submitLoading").css("visibility","visible");
	$("button[type='submit']").css("background","#eee").prop("disabled",true);
	setTimeout(function(){
		$(".submitLoading").css("visibility","hidden");
		$("button[type='submit']").css("background","initial").prop("disabled",false);
	 },4000);
}

/**************************************************************************************************
 * SUBMIT ASYNCHRONE D'UN FORMULAIRE  ("async" et "preventDefault()" préalables)
 **************************************************************************************************/
function asyncSubmit(thisForm)
{
	submitLoading();					//Affiche l'img "loading"
	$(thisForm).off("submit").submit();	//Validation finale du formulaire  ("off()" réinitialise les précédents triggers "submit")
}

/**************************************************************************************************
 * OUVRE UNE LIGHTBOX
 **************************************************************************************************/
function lightboxOpen(fileSrc)
{
	if(isMainPage==false)						{window.top.lightboxOpen(fileSrc);}													//Relance lightboxOpen() depuis la page "parent"
	else if(/pdf/i.test(fileSrc) && isMobile())	{window.top.open(fileSrc);}															//Pdf sur mobile
	else if(/(pdf|txt)/i.test(fileSrc))			{Fancybox.show([{type:"iframe", src:fileSrc, width:1200, height:2000}]);}			//Pdf/Txt sur desktop
	else if(/(mp4|webm)$/i.test(fileSrc))		{Fancybox.show([{type:"html5video", src:fileSrc}]);}								//Video
	else if(/mp3$/i.test(fileSrc))				{Fancybox.show([{type:"html", src:'<audio controls autoplay><source src="'+fileSrc+'" type="audio/mpeg">Audio</audio>'}]);}//Mp3
	else{
		Fancybox.show([{type:"iframe", src:fileSrc}],
			{
				l10n:fancyboxLang,																									//Charge les traductions des boutons
				closeExisting:/edit/i.test(fileSrc),																				//Ferme au besoin une Fancybox dejà ouverte
				dragToClose:false,																									//Désactive la fermeture de Fancybox via "drop"
				on:{
					shouldClose:function(fancybox,slide){																			//Controle à la fermeture du Fancybox
						if(window.top.confirmCloseForm==true){																		//Formulaire en cours d'édition : fermeture à confirmer
							slide.preventDefault();																					//- Suspend la fermeture via Fancybox
							confirmAlt(labelConfirmCloseForm).then(()=>{  window.top.confirmCloseForm=false; fancybox.close();  });	//- Fermeture confirmée : relance récursivement fancybox.close()
						}
					}
				}
			}
		);
	}
}

/**************************************************************************************************
 * WIDTH DE LA LIGHTBOX (cf max-width du body)  +  HEIGHT DYNAMIQUE  (cf show() toggle() etc.)
 **************************************************************************************************/
function lightboxResize()
{
	ready(function(){
		//// Contenu/Iframe du lightbox
		lightboxContent=window.top.document.querySelector(".fancybox__content");
		lightboxIframe =window.top.document.querySelector(".fancybox__iframe");
		if(isMainPage==false && lightboxIframe){
			//// Width/Height de la lightbox
			if(typeof lightboxTimeout!="undefined")  {clearTimeout(lightboxTimeout);}											//Un seul timeout
			lightboxTimeout=setTimeout(function(){																				//Timeout le tps de lancer les show(), fadeIn(), etc. (tps supérieur à $.fx.speeds)
				let cssWidth=window.getComputedStyle(document.body).getPropertyValue("max-width");								//"max-width" de "#bodyLightbox" ("px" ou "%")
				let lightboxWidth=parseInt(cssWidth);																			//Parse le width en Integer
				if(Number.isInteger(lightboxWidth)==false) 		{lightboxWidth=650;}											//Width par défaut si "max-width" non spécifié (idem app.css)
				if(/%/.test(cssWidth))							{lightboxWidth=(window.top.windowWidth/100) * lightboxWidth;}	//Width en % de windowWidth
				else if(lightboxWidth > window.top.windowWidth)	{lightboxWidth=window.top.windowWidth;}							//Width doit être inférieur à windowWidth
				lightboxContent.style.width =lightboxWidth+"px";																//Applique le width à lightboxContent
				lightboxIframe.style.width  =lightboxWidth+"px";																//Applique le width à lightboxIframe
				let lightboxHeight=document.body.scrollHeight;																	//Height du body de l'iframe
				if(typeof lightboxHeightLast=="undefined" || lightboxHeight > lightboxHeightLast){								//Init ou ajuste le lightboxHeight (après show(), fadeIn(), etc)
					lightboxContent.style.height =lightboxHeight+"px";															//Applique le height à lightboxContent
					lightboxIframe.style.height	 =lightboxHeight+"px";															//Applique le height à lightboxIframe
					lightboxHeightLast=lightboxHeight;																			//Enregistre le height
				}
			},150);
		}
	});
}

/**************************************************************************************************
 * RELOAD LA PAGE PRINCIPALE DEPUIS UNE LIGHTBOX (ex: après edit d'objet)
 **************************************************************************************************/
function lightboxRedir(urlNotify)
{
	const urlObj=new URL(window.top.location.href);												//Url de la page principale (Objet)
	const paramList=["typeId","curTime","dashboardPoll"]										//Params à récupérer ("ctrl" du module, "typeId" du dossier, "curTime" de l'agenda, affichage "dashboardPoll")
	const urlParams=urlObj.searchParams;														//Parametres à rechercher (Objet)
	let urlRedir=urlObj.origin + urlObj.pathname + "?ctrl="+urlParams.get("ctrl").toString();	//Url sans ses paramètres, excepté "ctrl"
	paramList.forEach(function(param){															//Parcours chaque parametre recherché
		if(urlParams.has(param))  {urlRedir+="&"+param+"="+urlParams.get(param).toString();}	//Ajoute le param dans urlRedir
	});
	window.top.location.href=urlRedir+urlNotify;												//Reload la page principale avec les nouvelles notifications
}

/**************************************************************************************************
 * SURCHARGES JQUERY : AJOUTE "lightboxResize()" A CERTAINES FONCTIONS
 **************************************************************************************************/
ready(function(){
	if(isMainPage==false){
		let showBASIC=$.fn.show;
		let fadeInBASIC=$.fn.fadeIn;
		let toggleBASIC=$.fn.toggle;
		let slideDownBASIC=$.fn.slideDown;
		let slideToggleBASIC=$.fn.slideToggle;
		$.fn.show=function(){			lightboxResize();	return showBASIC.apply(this,arguments); };
		$.fn.fadeIn=function(){			lightboxResize();	return fadeInBASIC.apply(this,arguments); };
		$.fn.toggle=function(){			lightboxResize();	return toggleBASIC.apply(this,arguments); };
		$.fn.slideDown=function(){		lightboxResize();	return slideDownBASIC.apply(this,arguments); };
		$.fn.slideToggle=function(){	lightboxResize();	return slideToggleBASIC.apply(this,arguments); };
	}
});

/**************************************************************************************************
 * SURCHARGES JQUERY : AJOUTE UN ".fail()" A "$.ajax" POUR AFFICHER LES ERREURS DANS LA CONSOLE
 **************************************************************************************************/
var originalAjax=$.ajax; 											// Sauvegarde la fonction originale
$.ajax=function(options){											// Surcharge $.ajax
	if(typeof options==="string")  {options={url:options};}			// Si c'est une URL (forme raccourcie) on le convertit en objet
	var originalFail=options.fail;									// Sauvegarde si besoin la fonction fail existante
	var jqXHR=originalAjax.call(this,options);						// Création de la promesse avec la fonction originale
	jqXHR.fail(function(xhr,status,error){							// Ajout du fail par défaut
		console.log("AJAX ERROR :", error);							// Affiche l'erreur dans la console
        if(originalFail)  {originalFail(xhr,status,error);}			// Ancien callback fail() s'il existait
	});
	return jqXHR;
};

/**************************************************************************************************
 * SURCHARGES JQUERY : AJOUTE DE NOUVELLES FONCTIONS
 **************************************************************************************************/
////	Vitesse par défaut des effets "fadeIn()", "toggle()", etc
$.fx.speeds._default=100;
////	Verifie si l'element existe
$.fn.exist=function(){
	return (this.length>0);
};
////	Verifie si l'element/input est vide
$.fn.isEmpty=function(){
	return (this.length==0 || this.val().trim()==="");
};
////	Verifie si l'element/input n'est pas vide
$.fn.notEmpty=function(){
	return (this.isEmpty()==false);
};
////	Verifie si l'element est affiché
$.fn.isDisplayed=function(){
	return this.is(":visible");
};
////	Verifie si l'element est un email (cf. "isMail()")
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
////	Focus et pulsate via css  (20 secondes)
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
////	Update le title et reload les tooltips
$.fn.tooltipUpdate=function(title){
	$(this).attr("title",title).tooltipster("destroy").tooltipster(tooltipParams);
};



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
function urlParam(param, url)
{
	if(typeof url==="undefined")  {url=window.location.href;}				//Url de la page courante
	const urlParams=new URLSearchParams(url);								//Créé un objet 'URLSearchParams'
	if(urlParams.has(param))	{return urlParams.get(param).toString();}	//Retourne le paramètre s'il existe
	else						{return "";}								//Renvoie toujours une chaine vide (pas de null)
}

/**************************************************************************************************
 * SWITCH LE "LIKE" D'UN OBJET : UPDATE LE "circleNb"
 **************************************************************************************************/
function usersLikeUpdate(typeId)
{
	if(isValue(typeId)){
		$.ajax({url:"?ctrl=object&action=usersLike&typeId="+typeId, dataType:"json"}).done(function(result){			//Requête Ajax pour switcher le "like"
			let menuId="#usersLike_"+typeId;																			//Id du menu
			if(result.likeNb==0)	{$(menuId).addClass("hide").find(".circleNb").html("");}							//Masque l'icone et le nb de likes
			else					{$(menuId).removeClass("hide").find(".circleNb").html(result.likeNb).pulsate(1);}	//Affiche l'icone
			$(menuId).tooltipUpdate(result.likeTooltip);																//Update les tooltips
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