/************************************************************************************************************
* This file is part of the Agora-Project Software package
*
* @copyleft Agora-Project <https://www.agora-project.net>
* @license GNU General Public License (GPL-2.0)
 ************************************************************************************************************/



/************************************************************************************************************
 * DOM CHARGÉ : LANCE UNE FONCTION (equiv. $(document).ready() de JQuery)
 ************************************************************************************************************/
function ready(thisFunction)
{
	if(document.readyState!="loading")	{thisFunction();}
	document.addEventListener("DOMContentLoaded",thisFunction);
}

/************************************************************************************************************
 * VARIABLES ET FONCTIONS PRINCIPALES
 ************************************************************************************************************/
ready(function(){
	mainDisplay();															//Affichage principal
	window.addEventListener("resize",function(){ mainDisplay(); });			//Relance si windows resize ou orientationchange
	mainTriggers();															//Triggers principaux
	menuContext();															//Affichage des menus contextuels
	controleFields();														//Affichage et controle des champs de formulaire
	window.addEventListener("orientationchange",function(){					//Reload si orientationchange sur mobile/tablette
		if(window.top.confirmCloseForm==false)  {window.location.reload();}
	});
});

/************************************************************************************************************
 * AFFICHAGE PRINCIPAL (UPDATED APRES RESIZE)
 ************************************************************************************************************/
function mainDisplay()
{
	////	Variables de base
	isMainPage=(window.self==window.top);																//Page principale || Lightbox
	if(isMainPage==true)  {confirmCloseForm=false;}														//Confirme une redirection si formulaire en cours d'édition
	windowTopWidth =window.top.document.documentElement.clientWidth;									//Width de la fenêtre principale (sans scrollbar)
	windowTopHeight=window.top.document.documentElement.clientHeight;									//Height de la fenêtre principale (idem)

	////	Fenêtre principale
	if(isMainPage==true){
		$("#headerBarMargin").css("height", ($("#headerBar").outerHeight() + 30));									//Marge de entre la headerBar et le contenu de la page
		document.cookie="windowWidth="+windowTopWidth+"; Max-Age=31536000; Priority=High; SameSite=lax;";			//Width de la fenêtre enregistré dans un Cookie 
		document.cookie="windowWidth="+windowTopWidth+"; Max-Age=31536000; Priority=High; SameSite=lax; path=/;";	//Idem pour le path racine
		if(typeof moduleDisplay=="function")  {moduleDisplay();}													//Affichage spécifique d'un module : ModCalendar, ModTask

		////	Width des objets en affichage "block"
		if($(".objBlocks .objContent").exist()){
			let pageContentWidth=$("#pageContent").width() - (isMobile() ? 0 : 14);												//Width de #pageContent - width de ::-webkit-scrollbar
			let objMargins=parseFloat($(".objContent").css("margin-left")) + parseFloat($(".objContent").css("margin-right"));	//Marges des objets (cf. "app.css")
			let widthMin  =parseFloat($(".objContent").css("min-width")) + objMargins;											//width Min
			let widthMax  =parseFloat($(".objContent").css("max-width")) + objMargins;											//width Max
			let lineObjNb=Math.ceil(pageContentWidth / widthMax);																//Nb d'objets par ligne : tester sur mobile !
			if(pageContentWidth < (widthMin*2))			{widthObj=pageContentWidth;}											//Un objet par ligne : width 100%
			else if($(".objContent").length<lineObjNb)	{widthObj=widthMax;}													//Tous les objets sur une seule ligne : largeur max
			else										{widthObj=Math.floor(pageContentWidth/lineObjNb);}						//Width en fonction de pageContentWidth et lineObjNb
			$(".objContent").outerWidth(widthObj,true);																			//Applique le width des objets (true pour prendre en compte les margins)
		}
	}
}

/***************************************************************************************************************
 * PRINCIPAUX TRIGGERS :  FANCYBOX  +  CLICK / DBLCLICK D'OBJETS  +  MENUS FLOTTANT  +  TOOLTIPSTER
 ***************************************************************************************************************/
function mainTriggers()
{
	////	Fancybox : resize d'Iframe
	lightboxResize();

	////	Fancybox : images & inline (mode "Declarative")
	let fancyboxThumbs=isMobile() ? false : {type:"classic"};
	let fancyboxToolbar={
		display:{left:[], right:["zoomIn","rotateCW","slideshow","fullscreen","thumbs","close"]}
	};
	Fancybox.bind("[data-fancybox='images'],.fancyboxImages", {l10n:fancyboxLang, Thumbs:fancyboxThumbs, Toolbar:fancyboxToolbar});
	Fancybox.bind("[data-fancybox='inline']", {l10n:fancyboxLang, type:"html"});

	////	DblClick : édition  ||  Click : sélection
	$(".objContent").off("click dblclick").on("click dblclick",function(event){													//off("click") annule les triggers précédents à chaque "mainTriggers()"
		if(event.type=="dblclick" && this.hasAttribute("data-url-edit"))	{lightboxOpen(this.getAttribute("data-url-edit"));}	//Note : pas de "dblclick" pour sur mobile
		else if(event.type=="click" && $(".objSelectCheckbox").exist())		{objSelectSwitch(this.id);}
	});

	////	Menu du module flottant
	if($("#pageMenu").isVisible()){
		$(window).on("scroll",function(){
			if(typeof pageMenuTimeout!="undefined")  {clearTimeout(pageMenuTimeout);}								//Non cumul de Timeout
			pageMenuTimeout=setTimeout(function(){																	//Timeout le tps de finaliser le scroll
				let menuHeight=$("#pageMenu").position().top;														//Position top du menu
				$("#pageMenu>*:visible").each(function(){ menuHeight+=$(this).outerHeight(true); });				//Ajoute la hauteur de chaque element
				if(menuHeight < windowTopHeight)  {$("#pageMenu").css("padding-top",$(window).scrollTop()+"px");}	//Repositionne le menu en fonction de la fenêtre
			},50);
		});
	}

	////	Tooltipster : init/update les "title"
	tooltipParams={theme:'tooltipster-shadow',delay:700,contentAsHTML:true};				//Theme et Affichage Html
	let timeoutDuration=$(".tooltipstered").exist() ? 1000 : 50;							//Timeout plus long si update des tooltips via ajax (ex: "messengerUpdate()")
	if(typeof tooltipDisplayTimeout!="undefined")  {clearTimeout(tooltipDisplayTimeout);}	//Non cumul de Timeout
	tooltipDisplayTimeout=setTimeout(function(){											//Timeout le tps de charger
		$("[title]:not(.notooltip,[title=''])").tooltipster(tooltipParams);					//Theme "shadow" et Affichage Html
	},timeoutDuration);

	////	Ouvre un lien <a href> via une lightbox (cf. HTMLPurifier)
	$("a.lightboxOpenHref").off("click").on("click",function(event){//off("click") annule les triggers précédents à chaque "mainTriggers()"
		event.preventDefault();
		lightboxOpen(this.getAttribute("href"));
	});

	////	Affiche/Masque le password
	$("img.passwordDisplay").on("click",function(){
		let inputPassword="#"+this.getAttribute("for");
		if($(inputPassword).attr("type")==="password")	{$(inputPassword).attr("type","text");		$("img.passwordDisplay").addClass("passwordDisplayShow");}		//Affiche le password
		else											{$(inputPassword).attr("type","password");	$("img.passwordDisplay").removeClass("passwordDisplayShow");}	//Masque le password
	});
}

/************************************************************************************************************
 *  CONTROLES DES CHAMPS
 ************************************************************************************************************/
function controleFields()
{
	////	Pas d'autocomplétion des inputs
	$("form input:not(.isAutocomplete)").attr("autocomplete","off");

	////	Formulaire édité : passe "confirmCloseForm" à true. Timeout le tps de finaliser les controles de form
	setTimeout(function(){
		$("#mainForm input, #mainForm select, #mainForm textarea").on("input change keyup",function(){  window.top.confirmCloseForm=true;  });
	},500);

	////	Controle la taille des fichiers des inputs "file"
	$("input[type='file']").on("change",function(){
		if($(this).notEmpty() && this.files[0].size > valueUploadMaxFilesize){
			$(this).val("");
			notify(TRAD_uploadMaxFilesize);
		}
	});

	////	<select> :  bgColor de l'input et de chaque <option>
	$("select").on("change",function(){
		let bgColor=$(this).find("option:selected").attr("data-color");
		if(isValue(bgColor))	{$(this).css({background:bgColor,color:'white'});}
	});
	$("select option").each(function(){
		let bgColor=this.getAttribute("data-color");
		if(isValue(bgColor))	{$(this).css({background:bgColor,color:'white'});}
	});

	////	Charge le Datepicker
	if(jQuery().datepicker){
		$(".dateInput, .dateBegin, .dateEnd").datepicker({dateFormat:"dd/mm/yy", firstDay:1, showOtherMonths:true, selectOtherMonths:true});
		if(isMobile())  {$(".dateInput, .dateBegin, .dateEnd").prop("readonly",true);}//Input "readonly" sur mobile
	}

	////	Charge le Timepicker
	if(jQuery().timepicker){
		$(".timeBegin, .timeEnd").timepicker({timeFormat:"H:i", step:15, "orientation":(isMobile()?"rb":"lb")});	//Orientation Right/Left + Bottom
		if(navigator.maxTouchPoints > 1 && /iPad|iPhone|iPod|MacIntel/i.test(navigator.userAgent)){					//Iphone/Ipad utilise le timePicker system (MacIntel: Ipads récents)
			$(".timeBegin, .timeEnd").on("showTimepicker",function(){  $(".timeBegin, .timeEnd").timepicker("hide");  });
		}
	}

	////	Récupère un objet Date au format ISO (exemple : 2030-04-21T14:30:45 -> HH:MM:SS en option)
	const objDate = function(inputDate, inputTime){
		if($(inputDate).notEmpty()){
			const [day, month, year] = $(inputDate).val().split("/");
			const time=$(inputTime).notEmpty()  ?  "T"+$(inputTime).val()+":00"  :  "";
			return new Date(`${year}-${month}-${day}${time}`);
		}
	};

	////	Controles des Datepicker / Timepicker (option)
	$(".dateBegin, .dateEnd, .timeBegin, .timeEnd").on("change",function(){
		if($(this).notEmpty()){
			//// Controle le format jj/mm/yyyy ou hh:mm
			const isDateInput=($(this).hasClass("dateBegin") || $(this).hasClass("dateEnd"));
			if(isDateInput==true  &&  /^\d{2}\/\d{2}\/\d{4}$/.test(this.value)==false)		{notify(TRAD_dateFormatError);}
			if(isDateInput==false &&  /^[0-2][0-9][:][0-5][0-9]$/.test(this.value)==false)	{notify(TRAD_timeFormatError);}
			//// Objets Datetime
			const dateBegin = objDate(".dateBegin", ".timeBegin");
			const dateEnd   = objDate(".dateEnd", ".timeEnd");
			//// Controles les dates de début / fin
			if($(".dateBegin").notEmpty() && $(".dateEnd").notEmpty() && typeof lastDateBegin!="undefined"){
				//// Diff entre l'ancienne et la nouvelle datetime
				const timeDiff=dateBegin.getTime() - lastDateBegin.getTime();
				const newDateEnd=new Date( dateEnd.getTime() + timeDiff );
				//// Modif dateBegin -> ajuste dateEnd
				if($(this).hasClass("dateBegin") && $(".dateEnd").notEmpty()){
					$(".dateEnd").datepicker("setDate",newDateEnd).pulsate(1);
				}
				//// Modif timeBegin -> ajuste timeEnd
				else if($(this).hasClass("timeBegin") && $(".dateBegin").val()==$(".dateEnd").val()){
					$(".timeEnd").timepicker("setTime",newDateEnd).pulsate(1);
				}
				//// Modif dateEnd ou timeEnd -> Verif qu'il ne soit pas avant le début
				if(($(this).hasClass("dateEnd") || $(this).hasClass("timeEnd"))  &&  dateEnd < dateBegin){
					notify(TRAD_beginEndError);
					$(".dateEnd").datepicker("setDate",lastDateEnd).pulsate(1);
					$(".timeEnd").timepicker("setTime",lastDateEnd).pulsate(1);
				}
			}
			//// Enregistre la date de début / fin
			lastDateBegin = objDate(".dateBegin", ".timeBegin");
			lastDateEnd   = objDate(".dateEnd", ".timeEnd");
		}
	});
	
	////	Init les dates de début / fin
	var lastDateBegin = objDate(".dateBegin", ".timeBegin");
	var lastDateEnd   = objDate(".dateEnd", ".timeEnd");
}

/************************************************************************************************************
 * MENU CONTEXTUEL
 ************************************************************************************************************/
function menuContext()
{
	////	Affichages / Masquages principaux
	$(".menuContextLaunch").on("click",function(event){  isMobile() ? menuMobileShow(this) : menuContextShow(this,event);  });	//Affiche si click sur .menuContextLaunch
	$(".menuContext").on("mouseleave",function(){  $(".menuContext").hide();  });												//Masque le menu si mouseleave sur .menuContext
	$(document).on("click",function(){  $(".menuContext").hide();  });															//Masque si click sur la page, hors du menu (cf Tablette mode paysage)
	$("#menuMobileClose,#menuMobileBg").on("click",function(){  menuMobileClose();  });											//Masque si click sur #menuMobileClose ou #menuMobileBg (black opacity)
	$(".menuContextLaunch,.menuContext,[href],[onclick]").on("click",function(event){  event.stopPropagation();  });			//Pas de propagation de click (evite un download ou une sélection via "objSelectSwitch()")
	if(windowTopWidth>=1300){																									//Click droit sur .objContent si width > 1300px
		$(".objContent").on("contextmenu",function(event){  menuContextShow(this,event);  return false;  });					//"return false" pour annuler le menu du browser
	}

	////	Affichage via swipe sur mobile
	if(isTouchDevice()){
		swipeMenuOn=true;
		document.addEventListener("touchstart",(event)=>{
			touchStartX=event.touches[0].clientX;
			touchStartY=event.touches[0].clientY;
			percentToBorderRight=Math.round(((windowTopWidth-touchStartX) / windowTopWidth) * 100);
		});
		document.addEventListener("touchmove",(event)=>{
			swipeToLeft =(touchStartX - event.touches[0].clientX);														//Diff entre la position X de départ et celle en cours (variable globale !)
			swipeToRight=(event.touches[0].clientX - touchStartX);														//Idem
			swipeAmplitudeY=Math.abs(touchStartY - event.touches[0].clientY);											//Amplitude verticale du swipe : Math.abs car doit être > 0 (variable globale !)
			if(swipeMenuOn==true && swipeAmplitudeY < 80 ){																//Swipe actif  + Amplitude verticale < 80px
				let swipeMenuShow=(typeof swipeMenuShowOff=="undefined" && $("#menuMobileMain").isVisible()==false);	//Affichage du menu pas désactivé (cf calendar)  +  Menu pas encore affiché
				if(swipeToLeft > 100  &&  swipeMenuShow==true  &&  percentToBorderRight < 35)	{menuMobileShow();}		//swipe vers la gauche > 100px et à moins de 35% du bord droit de la page
				else if(swipeToRight > 100  &&  $("#menuMobileMain").isVisible())				{menuMobileClose();}	//swipe vers la droite > 100px
			}
		});
		$(window).add("div").on("scroll",function(){										//// Scroll en cours (page ou div Task Gantt, tinyMce mobile...)
			swipeMenuOn=false;																//désactive le swipe durant le scroll
			if(typeof scrollPageTimeout!="undefined")  {clearTimeout(scrollPageTimeout);}	//Non cumul de Timeout
			scrollPageTimeout=setTimeout(function(){ swipeMenuOn=true; },500);				//Réinitialise le scroll : Timeout le tps de charger le tinyMce mobile/horizontal
		});
	}
}

/************************************************************************************************************
 * MENU CONTEXTUEL : AFFICHE SUR DESKTOP
 ************************************************************************************************************/
function menuContextShow(launcher, event)
{
	let menuId="#"+$(launcher).attr("for");																											//Id du menu à afficher (.menuContextLaunch et attribut "for")
	$(menuId).css("max-height", (window.innerHeight-10)+"px");																						//Hauteur max en fonction de la page (#menuMobileMain avec "overflow:auto")
	let isRelativePos=$(menuId).parents().is(function(){  return (/relative|absolute/i.test($(this).css("position")));  });							//Div parent en position relative/absolute
	if(event.type=="contextmenu")	{var posLeft=(event.pageX - $(launcher).offset().left);	var posTop=(event.pageY - $(launcher).offset().top);}	//Position du click droit de la souris
	else if(isRelativePos==true)	{var posLeft=$(launcher).position().left;			 	var posTop=$(launcher).position().top;}					//Position du .launcher par rapport au parent
	else							{var posLeft=$(launcher).offset().left;					var posTop=$(launcher).offset().top;}					//Position du .launcher par rapport au document
	let posRight =posLeft + $(menuId).outerWidth(true);																								//Position du bord right du menu
	let posBottom=posTop + $(menuId).outerHeight(true);																								//Position du bord bottom du menu
	if(isRelativePos==true)   {posRight+=$(menuId).parent().offset().left;  posBottom+=$(menuId).parent().offset().top;}							//Ajoute si besoin la position du parent
	let posRightPage =(window.innerWidth  + window.pageXOffset);																					//"right"  position de la page affiché
	let posBottomPage=(window.innerHeight + window.pageYOffset);																					//"bottom" position de la page affiché
	if(posRight > posRightPage)											{posLeft-=(posRight - posRightPage);}										//Décale le menu s'il est au bord droit de la fenêtre
	if(posBottom > posBottomPage && $("#bodyLightbox").exist()==false)	{posTop-=(posBottom - posBottomPage);}										//Décale le menu s'il est en bas de la fenêtre (sauf si "lightboxResize()")
	$(menuId).css("left",(posLeft-10)).css("top",(posTop-10)).fadeIn(200);																			//Affiche le menu (recentré de 10px)
	$(".menuContext").not(menuId).hide();																											//Masque les autres menus
}

/************************************************************************************************************
 * MENU CONTEXTUEL : AFFICHE SUR MOBILE
 ************************************************************************************************************/
function menuMobileShow(launcher)
{
	if(typeof menuMobileTimeout!="undefined")  {clearTimeout(menuMobileTimeout);}								//Non cumul de Timeout
	menuMobileTimeout=setTimeout(function(){																	//Timeout le tps de finaliser le swipe
		if($("#menuMobileMain").isVisible()){																	//Menu mobile déjà affiché : Affiche un sous-menu
			$("#"+$(launcher).attr("for")).addClass("menuMobileSubMenu").slideToggle();
		}else{																									//Affiche le Menu mobile :
			menuMobileHeader=(launcher)  ?  "#"+$(launcher).attr("for")  :  "#headerBarRight";					//menuMobileHeader : attr. "for" du launcher ou #headerBarRight si swipe (liste des modules ou autre)
			menuMobileContent=(menuMobileHeader=="#headerBarRight")  ?  "#pageMenu"  :  null;					//Affiche aussi #pageMenu (menu de gauche)
			if($(menuMobileHeader).exist()){																	//Vérif l'exisence de menuMobileHeader
				$(menuMobileHeader+">*").appendTo("#menuMobileHeader");											//Déplace le contenu de menuMobileHeader dans menuMobileHeader
				if($(menuMobileContent).exist())  {$(menuMobileContent+">*").appendTo("#menuMobileContent");}	//Déplace le contenu de menuMobileContent dans #menuMobileContent
				$('#menuMobileHeader .vHeaderModuleCurrent').appendTo('#menuMobileHeader');						//Déplace le module courant à la fin de la liste des modules
				$("#menuMobileBg,#menuMobileHeader,#menuMobileContent").show();									//Affiche le/les contenus
				$("#menuMobileMain").css("right","0px").show("slide",{direction:"right"});						//Réinit la position puis affiche #menuMobileMain
				$("body").css("overflow","hidden");																//Désactive le scroll de page en arriere plan
			}
		}
	},50);
}

/************************************************************************************************************
 * MENU CONTEXTUEL : MASQUE SUR MOBILE
 ************************************************************************************************************/
function menuMobileClose()
{
	if($("#menuMobileMain").isVisible()){															//Vérif si le menu mobile est visible
		$("#menuMobileBg,#menuMobileHeader,#menuMobileContent").hide();								//Masque complètement le menu
		$("#menuMobileMain").hide("slide",{direction:"right"});										//Masque #menuMobileMain
		$("#menuMobileHeader>*").appendTo(menuMobileHeader);										//Replace le contenu de menuMobileHeader dans son div d'origine 
		if($(menuMobileContent).exist())  {$("#menuMobileContent>*").appendTo(menuMobileContent);}	//Replace le contenu de menuMobileContent dans son div d'origine 
		$("body").css("overflow","visible");														//Réactive le scroll de page en arriere plan
	}
}

/************************************************************************************************************
 * VÉRIF AFFICHAGE RESPONSIVE : WIDTH FENETRE PRINCIPALE <= 1200px (Idem CSS & JS)
 ************************************************************************************************************/
function isMobile()
{
	return (windowTopWidth <= 1200);
}

/************************************************************************************************************
 * VÉRIF AFFICHAGE SUR DEVICE TACTILE  (windowWidth : cf tests)
 ************************************************************************************************************/
function isTouchDevice()
{
	return (navigator.maxTouchPoints > 1 || windowTopWidth <= 450);
}

/************************************************************************************************************
 * VÉRIF SI UNE VALEURE N'EST PAS VIDE (equiv "isEmpty()")
 ************************************************************************************************************/
function isValue(value)
{
	return (typeof value!="undefined" && value!=null && value!="" && value!=0);
}

/************************************************************************************************************
 * CONTROLE S'IL S'AGIT D'UN MAIL
 ************************************************************************************************************/
function isMail(mail)
{
	let regex=/^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
	return regex.test(mail);
}

/************************************************************************************************************
 * CONTROLE UN PASSWORD : AU MOINS 12 CARACTERES AVEC LETTRE + CHIFFRE + EVENTUELLEMENT CARAC. SPECIAUX
 ************************************************************************************************************/
function isPassword(password)
{
	let regex=/^(?=.*[a-zA-Z])(?=.*\d).{12,}$/;
	return regex.test(password);
}

/************************************************************************************************************
 * EXTENSION D'UN FICHIER (SANS LE POINT)
 ************************************************************************************************************/
function extension(fileName)
{
	if(isValue(fileName))  {return fileName.split(".").pop().toLowerCase();}
}

/************************************************************************************************************
 * AFFICHE UNE NOTIFICATION (cf. "toastmessage")
 ************************************************************************************************************/
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

/************************************************************************************************************
 * CONFIRM() : PARAMETRAGE PAR DEFAUT
 ************************************************************************************************************/
ready(function(){
	confirmParamsDefault={
		animation:"zoom",							//Animation en entrée/sortie
		boxWidth: isMobile() ? "390px" : "500px",	//Width de la box
		closeIcon:true,								//Icone "close"
		useBootstrap:false,							//Pas de dépendence à bootstrap
	}
});

/************************************************************************************************************
 * CONFIRM() ALTERNATIF  (utiliser si besoin avec "async function()" puis "await confirmAlt()")
 ************************************************************************************************************/
function confirmAlt(confirmTitle, confirmDetails){
	return new Promise((resolve)=>{
		let confirmParams={
			title:isValue(confirmTitle) ? confirmTitle : TRAD_confirm+" ?",
			content:isValue(confirmDetails) ? confirmDetails : null,
			buttons:{
				cancel:	{ btnClass:'btn-default', text:TRAD_confirmCancel, action:()=>{resolve(false);} },
				confirm:{ btnClass:'btn-blue',	  text:TRAD_confirm,	   action:()=>{resolve(true);} },
			}
		}
		$.confirm(Object.assign(confirmParamsDefault, confirmParams));
	});
}

/************************************************************************************************************
 * ASYNC : REDIRECTION A CONFIRMER
 ************************************************************************************************************/
async function confirmRedir(locationUrl, confirmTitle)
{
	if(await confirmAlt(confirmTitle))
		{window.top.location.href=locationUrl;}
}

/************************************************************************************************************
 * ASYNC : REDIRECTION A CONFIRMER SI UN FORMULAIRE EN COURS D'EDITION
 ************************************************************************************************************/
async function redir(locationUrl)
{
	if(window.top.confirmCloseForm==false || await confirmAlt(TRAD_confirmCloseForm))
		{window.top.location.href=locationUrl;}
}

/************************************************************************************************************
 * ASYNC : CONFIRME UNE SUPPRESSION AVEC REDIRECTION
 ************************************************************************************************************/
async function confirmDelete(deleteUrl, confirmDetailsBis, ajaxControlUrl)
{
	let confirmDetails='<div class="confirmDeleteInfo">'+TRAD_confirmDeleteInfo+'</div>';											// Détail du confirm "cette action est définitive"
	if(isValue(confirmDetailsBis))  {confirmDetails+='<img src="app/img/arrowRight.png"> '+confirmDetailsBis;}						// Ajoute le label de l'objet, le nb d'objets sélectionnés, etc.
	if(await confirmAlt(TRAD_confirmDelete,confirmDetails)){																		// Confirm "Confirmer la suppression ?"
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

/************************************************************************************************************
 * REDIRECTION HREF : CONFIRMATION ASYNCHRONE SI FORMULAIRE EN COURS D'EDITION
 ************************************************************************************************************/
ready(function(){
	//":not()" :  "_blank" ouvre une nouvelle fenêtre  et  "[data-fancybox]" + "a.lightboxOpenHref" sont lancés via mainTriggers()
	$("a[href]:not([target='_blank'],[data-fancybox],.lightboxOpenHref)").click(async function(event){
		event.preventDefault();
		if(window.top.confirmCloseForm==false || await confirmAlt(TRAD_confirmCloseForm))
			{window.top.location.href=this.getAttribute("href");}
	});
});

/************************************************************************************************************
 * SUBMIT UN FORMULAIRE : AFFICHE L'IMG "LOADING" + "DISABLE" LES BUTTONS SUBMIT
 ************************************************************************************************************/
function submitLoading()
{
	$(".loadingImage").show();
	$("button[type='submit']").prop("disabled",true);
	setTimeout(function(){
		$(".loadingImage").hide();
		$("button[type='submit']").prop("disabled",false);
	 },5000);//tester avec ajax + error, et upload de big files
}

/************************************************************************************************************
 * SUBMIT ASYNCHRONE D'UN FORMULAIRE  ("async" et "preventDefault()" préalables)
 ************************************************************************************************************/
function asyncSubmit(thisForm)
{
	submitLoading();					//Affiche l'img "loading"
	$(thisForm).off("submit").submit();	//off("submit") annule le trigger précédent, Puis submit() relance la validation finale
}

/************************************************************************************************************
 * OUVRE UNE LIGHTBOX  (ex: "?ctrl=file&action=FileDownload&typeId=file-1&displayFile=true&extension=pdf")
 ************************************************************************************************************/
function lightboxOpen(fileSrc)
{
	if(isMainPage==false)								{window.top.lightboxOpen(fileSrc);}											//Relance lightboxOpen() depuis la page "parent"
	else if(/pdf/i.test(fileSrc) && isTouchDevice())	{window.top.open(fileSrc);}													//Pdf sur mobile app :  nouvelle fenetre
	else if(/pdf|txt/i.test(fileSrc))					{Fancybox.show([{src:fileSrc, type:"iframe", width:1200, height:1200}]);}	//Pdf/Txt sur desktop : nouvel iframe
	else if(/mp4|mp3|webm/i.test(fileSrc))				{Fancybox.show([{src:fileSrc, type:"html5video"}]);}						//Video/Audio
	else{
		Fancybox.show([{type:"iframe", src:fileSrc}],{
				l10n:fancyboxLang,																									//Charge les traductions des boutons
				closeExisting:/edit/i.test(fileSrc),																				//Ferme au besoin une Fancybox dejà ouverte
				dragToClose:false,																									//Désactive la fermeture de Fancybox via "drop"
				on:{
					shouldClose:function(fancybox,slide){																			//Controle à la fermeture du Fancybox
						if(window.top.confirmCloseForm==true){																		//Formulaire en cours d'édition : fermeture à confirmer
							slide.preventDefault();																					//- Suspend la fermeture via Fancybox
							confirmAlt(TRAD_confirmCloseForm).then(()=>{  window.top.confirmCloseForm=false; fancybox.close();  });	//- Fermeture confirmée : relance récursivement fancybox.close()
						}
					}
				}
			}
		);
	}
}

/************************************************************************************************************
 * RELOAD LA PAGE PRINCIPALE DEPUIS UNE LIGHTBOX (ex: après edit d'objet)
 ************************************************************************************************************/
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

/************************************************************************************************************
 * WIDTH / HEIGHT DE LA LIGHTBOX : LANCEE DEPUIS SON CONTENU VIA mainTriggers(), show(), etc.
 ************************************************************************************************************/
function lightboxResize()
{
	const resizeWidthDefault=650;																				//Width par défaut des lightbox
	if(isMainPage==false && window.top.$(".fancybox__iframe").exist()){											//Iframe affichée ?
		if(typeof lightboxTimeout!="undefined")  {clearTimeout(lightboxTimeout);}								//Non cumul de Timeout
		lightboxTimeout=setTimeout(function(){																	//Timeout le temps de lancer les show(), fadeIn(), etc (toujours > à $.fx.speeds)
			let cssWidth=window.getComputedStyle(document.body).getPropertyValue("max-width");					//Width du contenu de l'iframe : cf. "max-width" de #bodyLightbox (en "px" ou "%")
			let resizeWidth=parseInt(cssWidth);																	//resizeWidth en Integer
			if(Number.isInteger(resizeWidth)==false) 	{resizeWidth=resizeWidthDefault;}						//resizeWidth par défaut si "max-width" non spécifié (même width que ".fancybox__content" dans "app.css")
			if(/%/.test(cssWidth))						{resizeWidth=(windowTopWidth/100) * resizeWidth;}		//resizeWidth en % de width de la page principale
			else if(resizeWidth > windowTopWidth)		{resizeWidth=windowTopWidth;}							//resizeWidth toujours <= à windowTopWidth
			window.top.$(".fancybox__content,.fancybox__iframe").css("width",resizeWidth+"px");					//Applique le width au fancybox
			let lightboxHeight=(windowTopWidth <= 490)  ?  windowTopHeight  :  document.body.scrollHeight+10;	//Toute la hauteur sur smartphone (fullpage) || Height du body de l'iframe
			if(typeof lightboxHeightLast=="undefined" || lightboxHeight > lightboxHeightLast){					//Init le height OU agrandit le height après un show(), fadeIn(), etc
				window.top.$(".fancybox__content,.fancybox__iframe").css("height",lightboxHeight+"px");			//Applique le height à lightboxContent & lightboxIframe
				lightboxHeightLast=lightboxHeight;																//Enregistre le height
			}
		},200);
	}
}

/************************************************************************************************************
 * COULEUR DE TEXTE EN CONTRASTE AVEC LE BACKGROUND-COLOR (equivalent css de "contrast-color")
 ************************************************************************************************************/
function contrastColor(bgColor)
{
	const cleanHex=bgColor.replace('#', '');					// On retire le "#" si l'utilisateur l'a mis
	const r = parseInt(cleanHex.substr(0, 2), 16);				//extrait le Rouge
	const g = parseInt(cleanHex.substr(2, 2), 16);				//extrait le Vert
	const b = parseInt(cleanHex.substr(4, 2), 16);				//extrait le Bleu
	const luminance = (0.299 * r) + (0.587 * g) + (0.114 * b);	//Calcule la luminosité perçue
	return (luminance > 145) ? '#333' : '#ffffff';		  //Texte en noir ou blanc en fonction de la luminosité
}

/************************************************************************************************************
 * SURCHARGES JQUERY : AJOUTE "lightboxResize()" A CERTAINES FONCTIONS
 ************************************************************************************************************/
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

/************************************************************************************************************
 * SURCHARGES JQUERY : AJOUTE UN ".fail()" A "$.ajax" POUR AFFICHER LES ERREURS DANS LA CONSOLE
 ************************************************************************************************************/
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

/************************************************************************************************************
 * SURCHARGES JQUERY : AJOUTE DE NOUVELLES FONCTIONS
 ************************************************************************************************************/
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
$.fn.isVisible=function(){
	return this.is(":visible");
};
////	Verifie si l'element est un email (cf. "isMail()")
$.fn.isMail=function(){
	return isMail(this.val());
};
////	Verifie si l'element est un password (cf. "isPassword()")
$.fn.isPassword=function(){
	return isPassword(this.val());
};
////	Clignotement / "Blink" d'un element (toute les secondes et 3 fois par défaut : cf. "times")
$.fn.pulsate=function(pTimes){
	if(typeof pTimes=="undefined")  {var pTimes=3;}
	this.effect("pulsate",{times:parseInt(pTimes)},parseInt(pTimes*1000));
};
////	Focus alternatif à la fin du texte (uniquement sur certains inputs & pas sur mobile : cf. clavier virtuel)
$.fn.focusAlt=function(){
	if(this.is("input[type='text'],input[type='password'],textarea") && isTouchDevice()==false){
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
	let scrollTopPos=($(this).offset().top - $("#headerBar").outerHeight() - 30);
	$("html,body").animate({scrollTop:scrollTopPos},100);
};
////	Update le title et reload les tooltips
$.fn.tooltipUpdate=function(title){
	$(this).attr("title",title).tooltipster("destroy").tooltipster(tooltipParams);
};




/**********************************************************************************************************************************
 **************************************************************************************************           FONCTIONS SPECIFIQUES
 **********************************************************************************************************************************/



/************************************************************************************************************
 * AFFECTATIONS USER <=> SPACE  :  "VueSpaceEdit.php" & "VueUserEdit.php"
 ************************************************************************************************************/
ready(function(){
	if($(".spaceAffectBox").exist()){
		////	CLICK LE LABEL D'UNE AFFECATION
		$(".spaceAffectLabel").on("click",function(){
			let affectLine=$(this).closest(".spaceAffectLine");
			let boxUser =$(affectLine).find("input[value$='_1']");
			let boxAdmin=$(affectLine).find("input[value$='_2']");
			let available  	=":not(:checked):not(:disabled)";
			if($(boxUser).is(available) && $(boxAdmin).is(":not(:checked)"))	{boxToCheck=boxUser;}	//boxUser  dispo et boxAdmin décochée
			else if($(boxAdmin).is(available))									{boxToCheck=boxAdmin;}	//boxAdmin dispo
			else																{boxToCheck=null;}		//Tout décoché
			$(affectLine).find("input:not(:disabled)").prop("checked",false);							//Réinit les checkboxes (non disabled)
			if(boxToCheck!=null)  {$(boxToCheck).prop("checked",true);}									//Sélectionne la boxToCheck
			spaceAffectStyle();																			//Style des affectations
		});

		////	CLICK LA CHECKBOX D'UNE AFFECTATION
		$(".spaceAffectBox input").on("change",function(){
			let affectLine=$(this).closest(".spaceAffectLine");
			$(affectLine).find("input:not(:disabled)").not(this).prop("checked",false);	//Uncheck les autres checkboxes de la ligne (non disabled)
			spaceAffectStyle();															//Style des affectations
		});
		
		////	STYLE DES AFFECTATIONS : INIT
		spaceAffectStyle();
	}
});

/************************************************************************************************************
 * AFFECTATIONS USER <=> SPACE : STYLE DES LABELS/LIGNES
 ************************************************************************************************************/
function spaceAffectStyle()
{
	$(".spaceAffectLine").removeClass("lineSelect accessRead accessWrite");	//Réinit le style des lignes
	$(".spaceAffectLine:has(.spaceAffectBox input:checked)").each(function(){	//Parcourt les lignes sélectionnées (.spaceAffectBox uniquement)
		if($(this).find("input[value$='_2']").is(":checked"))	{$(this).addClass("lineSelect accessWrite");}
		else													{$(this).addClass("lineSelect accessRead");}
	});
}

/************************************************************************************************************
 * VALEUR D'UN PARAMETRE DANS UNE URL
 ************************************************************************************************************/
function urlParam(param, url)
{
	if(typeof url==="undefined")  {url=window.location.href;}				//Url de la page courante
	const urlParams=new URLSearchParams(url);								//Créé un objet 'URLSearchParams'
	if(urlParams.has(param))	{return urlParams.get(param).toString();}	//Retourne le paramètre s'il existe
	else						{return "";}								//Renvoie toujours une chaine vide (pas de null)
}

/************************************************************************************************************
 * SWITCH LE "LIKE" D'UN OBJET : UPDATE LE "circleNb"
 ************************************************************************************************************/
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

/********************************************************************************************************************
 * CHECK/UNCHECK DES GROUPES D'USERS  =>  les inputs d'user doivent avoir un attribut "data-iduser" !
 ********************************************************************************************************************/
function userGroupSelect(inputselector, idUsers)
{
	////	Selector des inputs d'users  +  User-ids du groupe
	let inputs=inputselector;
	let idUsersTab=idUsers.split(",");
	////	Check chaque user du groupe
	for(tmpKey in idUsersTab){
		$(inputs+"[data-iduser='"+idUsersTab[tmpKey]+"']:enabled").prop("checked",true).trigger("change");
	}
}