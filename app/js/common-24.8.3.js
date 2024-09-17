/**
* ================================================================================
* This file is part of the Agora-Project Software package
*
* @copyleft Agora-Project <https://www.agora-project.net>
* @license GNU General Public License, version 2 (GPL-2.0)
*-================================================================================
*/


/**************************************************************************************************
 * SURCHARGE JQUERY POUR AJOUTER DE NOUVELLES FONCTIONS
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
$.fn.isNotEmpty=function(){
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
////	Focus sur un champ surligné en rouge (ajoute .focusRed durant 10 secondes)
$.fn.focusRed=function(){
	this.addClass("focusRed").focus();
	setTimeout(()=>{ this.removeClass("focusRed"); },10000);
};
////	Renvoie la hauteur totale des élements sélectionnées (marge comprise)
$.fn.totalHeight=function(){
	let tmpHeight=0;
	this.each(function(){ tmpHeight+=$(this).outerHeight(true); });
	return Math.floor(tmpHeight);
};
////	Scroll vers un element de la page
$.fn.scrollTo=function(){
	let scrollTopPos=$(this).offset().top - parseInt($("#headerBar,#headerContainer").height()) - 15;//Soustrait la barre de menu principale fixe (#headerBar ou #headerBarCenter en fonction de la page)
	$("html,body").animate({scrollTop:scrollTopPos},300);
};

/**************************************************************************************************
 * DOCUMENT READY : ENREGISTRE LE "windowWidth" DE LA PAGE (RECUPERE COTE SERVEUR)
 **************************************************************************************************/
$(function(){
	if(isMainPage==true){
		let forceReload=(isTouchDevice() && /windowWidth/i.test(document.cookie)==false);	//Cookie "windowWidth" absent sur un appareil tactile : lance un premier reload (cf. affichage des "menuMobile")
		windowWidthReload(forceReload);														//Init le cookie "windowWidth"
		window.onresize=function(){	windowWidthReload(false); };							//Redimensionne la page (width/Height)
		screen.orientation.onchange=function(e){ windowWidthReload(false); };				//Change l'orientation de la page
	}
});
////	Enregistre le "windowWidth" dans un cookie et reload la page si besoin (timeout le temps d'avoir le width final : cf. "onresize"/"onorientationchange")
function windowWidthReload(forceReload){
	if(typeof resizeTimeout!="undefined")  {clearTimeout(resizeTimeout);}//Pas de cumul de Timeout !
	resizeTimeout=setTimeout(function(){
		let pageReload=(forceReload==true || (typeof pageWidthLast!="undefined" && Math.abs($(window).width()-pageWidthLast)>30));	//Reload uniquement si le width a été modifé d'au moins 30px (pas de reload avec l'apparition/disparition de l'ascenseur)
		pageWidthLast=$(window).width();																							//Enregistre/Update le width courant pour le controle ci-dessus
		document.cookie="windowWidth="+$(window).width()+";expires=01 Jan 2050 00:00:00 GMT;samesite=Lax";							//Enregistre/Update le width dans un cookie permanent ("samesite" obligatoire pour les browsers)
		if(pageReload==true && confirmCloseForm==false)  {location.reload();}														//Reload la page ...sauf si on affiche un formulaire (lightbox ou pas)
	},500);
}

/**************************************************************************************************
 * DOCUMENT READY : LANCE LES FONCTIONS DE BASE
 **************************************************************************************************/
$(function(){
	mainPageDisplay(true);	//Title via Tooltipster / Gallerie d'image via LightBox / largeur des blocks d'objet / etc.
	menuContextInit();		//Initialise les menus contextuels
});

/**************************************************************************************************
 * DOCUMENT READY : SURCHARGE CERTAINES FONCTION JQUERY AVEC "lightboxResize()"
**************************************************************************************************/
$(function(){
	if(isMainPage!==true)
	{
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

/**************************************************************************************************
 * DOCUMENT READY : INITIALISE LES TRIGGERS SUR DESKTOP
 * => Click/DblClick sur ".objContainer", Menu flottant, etc.
 **************************************************************************************************/
$(function(){
	if(isMobile()==false){
		////	Click/DblClick sur les blocks conteneurs des objets : Sélectionne ou Edite un objet
		if($(".objContainer").exist())
		{
			//Trigger si ya click sur le block et que les actions par défaut sont autorisées
			$(".objContainer").on("click",function(){
				//Init
				var blockId="#"+this.id;
				timeDblClick=300;//intervalle entre 2 clics (pas plus de 300ms: cela doit rester un raccourcis du menu contextuel)
				if(typeof lastClickTime==="undefined")	{lastClickTime=Date.now();  lastClickContainerId=this.id;}
				//Double click?
				lastClickDiffTime=(Date.now()-lastClickTime);
				var isDblClick=(lastClickDiffTime>10 && lastClickDiffTime<timeDblClick && lastClickContainerId==this.id);
				//Simple click : switch la sélection (cf. "VueObjMenuSelect.php")
				if(isDblClick==false && typeof objSelectSwitch==="function" && $(".objSelectCheckbox").length>0)  {objSelectSwitch(this.id);}
				//Double click : Edition d'objet
				else if(isDblClick==true && $(blockId).attr("data-urlEdit"))  {lightboxOpen($(blockId).attr("data-urlEdit"));}
				//Update "lastClickTime" & "lastClickContainerId"
				lastClickTime=Date.now();
				lastClickContainerId=this.id;
			});
		}
		////	Menu flottant du module (à gauche)
		if($("#pageModuleMenu").exist())
		{
			var pageMenuPos=$("#pageModuleMenu").position();
			$(window).scroll(function(){
				var pageMenuHeight=pageMenuPos.top;//Init la position top du menu
				$("#pageModuleMenu").children().each(function(){ pageMenuHeight+=$(this).outerHeight(true); });//hauteur de chaque element
				if(pageMenuHeight < $(window).height())  {$("#pageModuleMenu").css("padding-top",$(window).scrollTop()+"px");}
			});
		}
	}
});

/**************************************************************************************************
 * DOCUMENT READY : INITIALISE LES CONTROLES DES CHAMPS
 * => confirmCloseForm, Datepickers, Timepicker, FileSize controls, Integer, etc.
 **************************************************************************************************/
$(function(){
	////	Formulaire "#mainForm" modifié : passe "confirmCloseForm" à "true" pour la confirmation de fermeture ("windowParent" pour cibler les "form" de lightbox)
	setTimeout(function(){
		$("#mainForm").find("input,select,textarea").on("input change keyup",function(){ windowParent.confirmCloseForm=true; });
	},1000);//1 sec. après l'initialisation des controles du formulaire

	////	Init les Datepicker & Timepicker (Vérif que les plugins sont chargés)
	if(jQuery().datepicker){
		$(".dateInput, .dateBegin, .dateEnd").datepicker({dateFormat:"dd/mm/yy", firstDay:1, showOtherMonths:true, selectOtherMonths:true});
		if(isMobile())	{$(".dateInput, .dateBegin, .dateEnd").prop("readonly",true);}//Readonly sur mobile
	}
	if(jQuery().timepicker){
		$(".timeBegin, .timeEnd").timepicker({timeFormat:"H:i", step:15, "orientation":(isMobile()?"rb":"lb")});
	}

	////	Init dateBeginRef + timeBeginRef (en millisecondes!)
	if($(".dateBegin").isNotEmpty())  {var dateBeginRef=$(".dateBegin").datepicker("getDate").getTime();}
	if($(".timeBegin").isNotEmpty())  {var timeBeginRef=$(".timeBegin").timepicker("getTime").getTime();}

	////	Datepicker/Timepicker : Controle du DateTime
	$(".dateBegin, .dateEnd, .timeBegin, .timeEnd").on("change",function(){
		//// Controle le format des dates et heures
		if( ($(this).hasClass("dateBegin") || $(this).hasClass("dateEnd"))  &&  $(this).isNotEmpty()  &&  /^\d{2}\/\d{2}\/\d{4}$/.test(this.value)==false)  	 {notify(labelDateFormatError);}
		if( ($(this).hasClass("timeBegin") || $(this).hasClass("timeEnd"))  &&  $(this).isNotEmpty()  &&  /^[0-2][0-9][:][0-5][0-9]$/.test(this.value)==false)   {notify(labelTimeFormatError);}
		//// Si la .dateBegin est avancée, la .dateEnd est avancée d'autant
		if($(this).hasClass("dateBegin")){
			let beginDiffTime=($(".dateBegin").datepicker("getDate").getTime() - dateBeginRef);						//Différence entre l'ancienne et la nouvelle .dateBegin (en millisecondes!)
			let dateEndNew=new Date(($(".dateEnd").datepicker("getDate").getTime() + beginDiffTime));				//Calcule la .dateEnd en fonction de la nouvelle .dateBegin
			$(".dateEnd").datepicker("setDate",dateEndNew).pulsate(1);												//Applique la nouvelle .dateEnd avec un "pulsate"
		}
		//// Si le .timeBegin est avancé, le .timeEnd est avancé d'autant
		if($(this).hasClass("timeBegin") && $(".dateBegin").val()==$(".dateEnd").val()){							//Verif que .dateBegin == .dateEnd
			let beginDiffTime=($(".timeBegin").timepicker("getTime").getTime() - timeBeginRef);						//Différence entre l'ancien et la nouveau .timeBegin (en millisecondes!)
			let timeEndNew=new Date(($(".timeEnd").timepicker("getTime").getTime() + beginDiffTime));				//Calcule le .timeEnd en fonction du nouveau .timeBegin
			$(".timeEnd").timepicker("setTime",timeEndNew).pulsate(1);												//Applique le nouveau .timeEnd avec un "pulsate"
		}
		//// Verif que le datetime de début soit avant celui de fin
		let dateBegin=$(".dateBegin").val().split("/");																//Date de début au format "dd/MM/yyyy"
		let dateEnd	 =$(".dateEnd").val().split("/");																//Date de fin
		let datetimeBegin	=new Date(dateBegin[1]+"/"+dateBegin[0]+"/"+dateBegin[2]+" "+$(".timeBegin").val());	//Objet Date de début au format "MM/dd/yyyy HH:mm"
		let datetimeEnd		=new Date(dateEnd[1]+"/"+dateEnd[0]+"/"+dateEnd[2]+" "+$(".timeBegin").val());			//Objet Date de fin
		if(datetimeBegin > datetimeEnd){
			setTimeout(function(){
				notify(labelBeginEndError);																			//Notif "La date de début doit précéder la date de fin"
				$(".dateEnd").val($(".dateBegin").val());															//Date de fin = idem début 
				$(".timeEnd").val($(".timeBegin").val());															//Time de fin = idem début 
			},500);																									//Timeout car modif après l'action du Timepicker
		}
		//// PUIS update dateBeginRef + timeBeginRef (en millisecondes!)
		if($(".dateBegin").isNotEmpty())  {dateBeginRef=$(".dateBegin").datepicker("getDate").getTime();}
		if($(".timeBegin").isNotEmpty())  {timeBeginRef=$(".timeBegin").timepicker("getTime").getTime();}
	});

	////	Controle la taille des fichiers des inputs "file"
	$("input[type='file']").on("change",function(){
		if($(this).isNotEmpty() && this.files[0].size > valueUploadMaxFilesize){
			$(this).val("");
			notify(labelUploadMaxFilesize);
		}
	});

	////	Affecte une couleur à un input "select" (chaque option doit avoir un attribut "data-color")
	$("select option").each(function(){
		if(this.getAttribute("data-color"))	{$(this).css("background-color",this.getAttribute("data-color")).css("color","white");}
		else								{$(this).css("background-color","white").css("color","#333");}
	});
	$("select").on("change",function(){
		var optionColor=$(this).find("option:selected").attr("data-color");
		if(isValue(optionColor))	{$(this).css("background-color",optionColor).css("color","white");}
		else						{$(this).css("background-color","white").css("color","#000");}
	});

	////	Pas d'autocomplétion sur TOUS les inputs des formulaires (password, dateBegin, etc) !
	$("form input:not([name*='connectLogin'])").attr("autocomplete","off");
});

/**************************************************************************************************
 * DOCUMENT READY : INITIALISE L'AFFICHAGE DES PAGES PRINCIPALES
 * => Menu flottant / Largeur des blocks d'objet / Clic sur les blocks d'objet / Etc.
 **************************************************************************************************/
function mainPageDisplay(firstLoad)
{
	////	Affiche les "Title" avec Tooltipster
	tooltipsterOptions={theme:"tooltipster-shadow",contentAsHTML:true,delay:400};				//Variable globale : Theme, Affichage Html, Delais d'affichage/masquage rapide
	$("[title]").not(".noTooltip,[title=''],[title*='http']").tooltipster(tooltipsterOptions);	//Applique le tooltipster (sauf si "noTooltip" est spécifié, ou le tooltip contient une URL, ou le title est vide)
	$("[title*='http']").tooltipster($.extend(tooltipsterOptions,{interactive:true}));			//Ajoute "interactive" pour les "title" contenant des liens "http" (cf. description & co). On créé une autre instance car "interactive" peut interférer avec les "menuContext"

	////	Fancybox des images (dans les news, etc) & inline (contenu html)
	var fancyboxImagesButtons=(isMobile()) ? ['close'] : ['fullScreen','thumbs','close'];
	$("[data-fancybox='images']").fancybox({buttons:fancyboxImagesButtons});
	$("[data-fancybox='inline']").fancybox({touch:false,arrows:false,infobar:false,smallBtn:false,buttons:['close']});//Pas de navigation entre les elements "inline" ("touch","arrow","infobar"). Pas de "smallBtn" close, mais plutôt celui en haut à droite.

	////	Initialise toute la page : largeur des blocks d'objet, Footer, etc.
	if(firstLoad===true)
	{
		////	Calcule la largeur des objets ".objContainer" (Affichage "block" uniquement. Calculé en fonction de la largeur de la page : après loading ou resize de la page)
		if($(".objBlocks .objContainer").length>0)
		{
			//Marge & Largeur min/max des objets
			var objMargin=parseInt($(".objContainer").css("margin-right"))+1;
			var objMinWidth=parseInt($(".objContainer").css("min-width"));
			var objMaxWidth=parseInt($(".objContainer").css("max-width")) + objMargin;//ajoute la marge pour l'application du "width()"
			//Largeur disponible
			var containerWidth=$("#pageFullContent").width();//pas de "innerWidth()" car cela ajoute le "padding"
			if(isMobile()==false && $(document).height()==$(window).height())	{containerWidth=containerWidth-18;}//pas encore d'ascenseur : anticipe son apparition (sauf sur mobile ou l'ascenseur est masqué)
			//Calcul la largeur des objets
			var objWidth=null;
			var lineNbObjects=Math.ceil(containerWidth / objMaxWidth);//Nb maxi d'objets par ligne
			if(containerWidth < (objMinWidth*2))				{objWidth=containerWidth;  $(".objContainer").css("max-width",containerWidth);}	//On peut afficher qu'un objet par ligne : il prendra la largeur du conteneur
			else if($(".objContainer").length<lineNbObjects)	{objWidth=objMaxWidth;}															//Nb d'objets insuffisant pour remplir la 1ère ligne : il prendra sa largeur maxi
			else												{objWidth=Math.floor(containerWidth/lineNbObjects);}							//Sinon on calcul : fonction du conteneur et du nb d'objets par ligne
			//Applique la largeur des blocks (enlève le margin: pas pris en compte par le "outerWidth")  &&  Rend visible si ce n'est pas le cas !!
			$(".objContainer").outerWidth(Math.round(objWidth-objMargin)+"px");
		}
		//...affiche à nouveau après l'éventuel calcul ci-dessus : ".objBlocks" masqués par défaut via "common.css"!
		$(".objBlocks").css("visibility","visible");
	}
}

/**************************************************************************************************
 * DOCUMENT READY : INITIALISE LES MENUS CONTEXTUELS
 * chaque launcher (icone/texte/block d'objet) doit avoir la propriété "for" correspondant à l'ID du menu  &&  une class "menuLaunch" (sauf pour les launcher de block d'objet) 
 **************************************************************************************************/
function menuContextInit()
{
	////	MENU MOBILE (width<=1024)
	if(isMobile()){
		//// Click d'un "launcher" (icone/texte) : affiche le menu mobile
		$(".menuLaunch").on("click",function(){
			if($("#menuMobileContent").isVisible()==false)	{menuMobileShow(this.getAttribute("for"),this.getAttribute("forBis"));}			//Menu masqué : on l'affiche
			else											{$("#"+this.getAttribute("for")).addClass("menuContextSubMenu").slideToggle();}	//Menu déjà affiché : on affiche le sous-menu
		});
		//// Swipe sur la page pour afficher/masquer le menu context
		document.addEventListener("touchstart",function(event){															//Début de swipe :
			swipeStartX=event.touches[0].clientX;																		//Init X
			swipeStartY=event.touches[0].clientY;																		//Init Y
		});
		document.addEventListener("touchend",function(){																//Fin de swipe :
			swipeStartY=swipeStartX=0;																					//Réinit X+Y
			if(parseInt($("#menuMobileMain").css("right"))<0)  {$("#menuMobileMain").css("right","0px");}				//Replace si besoin le #menuMobileMain
		});
		document.addEventListener("touchmove",function(event){															//Lance le swipe d'affichage du menuContext :
			if(Math.abs(swipeStartY-event.touches[0].clientY) < 50  &&  typeof isCalendarSwipe=="undefined"){			//Swipe < 50px d'amplitude verticale + Pas d'affichage de Calendar (swipe surchargé)
				if((event.touches[0].clientX - swipeStartX) > 10)  {menuMobileClose(event.touches[0].clientX);}			//Masque le menu : swipe > 10px vers la droite
				else if((swipeStartX - event.touches[0].clientX) > 50  &&  $(window).width()-swipeStartX < 150){		//Affiche le menu : swipe > 50px vers la gauche et à 150px max du bord de page
					if($("#headerModuleTab").exist())			{menuMobileShow("headerModuleTab","pageModMenu");}		//Affiche la liste des modules
					else if($("#headerContextMenu").exist())	{menuMobileShow("headerContextMenu","pageModMenu");}	//Ou affiche le menu principal de la page
				}
			}
		});
		//// Masque le menu context (via l'icone "close" ou background du menu mobile)
		$("#menuMobileClose,#menuMobileBg").on("click",function(){  menuMobileClose();  });
	}
	////	MENU DESKTOP
	else{
		$(".menuLaunch").on("click",function(){					menuContextShow(this);  });						//Mouseover/Click d'un "launcher" (icone/texte) : affiche le menu classique
		$(".objContainer").on("contextmenu",function(event){	menuContextShow(this,event); return false;  });	//Click Droit d'un objet : affiche le menu context ("Return false" pour pas afficher le menu du browser)
		$(".menuContext").on("mouseleave",function(){			$(".menuContext").fadeOut();  });				//Masque le menu dès qu'on le quitte
		$(".menuLaunch,.menuContext,[href],[onclick]").click(function(event){	event.stopPropagation();  });	//Pas de sélection d'objet via "objSelectSwitch()" (Ex: download de fichier ou affichage d'une vue)
	}
}

/*MENU NORMAL : AFFICHE LE MENU CONTEXT*/
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
	$(".menuContext").not(menuId).hide();										//Masque les autres menus
	if(menuPosY>15)  {menuPosX-=15;  menuPosY-=15;}								//Recentre le menu
	$(menuId).css("left",menuPosX+"px").css("top",menuPosY+"px").fadeIn(200);	//Affiche le menu
}

/*MENU MOBILE : AFFICHE (cf. VueStructure.php)*/
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

/*MENU MOBILE : MASQUE*/
function menuMobileClose(swipeStartXCurrent)
{
	if($("#menuMobileMain").isVisible())
	{
		//// Masque progressivement le menu vers la droite : 100 premiers pixels de swipe
		if(typeof swipeStartXCurrent!="undefined" && parseInt($("#menuMobileMain").css("right")) > - 100)  {$("#menuMobileMain").css("right", "-"+(swipeStartXCurrent-swipeStartX)+"px");}
		//// Sinon on masque totalement le menu
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
 * NAVIGATION EN MODE "MOBILE" SI LE WIDTH EST INFÉRIEUR À 1024PX  (IDEM Req.php && && Common.css)
 **************************************************************************************************/
function isMobile()
{
	return (windowParent.document.body.clientWidth<1024);
}

/**************************************************************************************************
 * VÉRIFIE SI ON EST SUR UN APPAREIL TACTILE (Android/Ipad/Iphone ..ou Ipad OS : cf. "Macintosh")
 **************************************************************************************************/
function isTouchDevice()
{
	return (navigator.maxTouchPoints>2 && /android|iphone|ipad|Macintosh/i.test(navigator.userAgent));
}

/**************************************************************************************************
 * VÉRIFIE SI UNE VALEURE N'EST PAS VIDE
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
 * AFFICHE UNE NOTIFICATION (via le Jquery "toastmessage")
 **************************************************************************************************/
function notify(curMessage, typeNotif)
{
	if(typeof curMessage!="undefined")
	{
		//Type de notification :  "success" vert  /  "warning" jaune  /  "notice" bleu (par défaut)
		var type=(typeof typeNotif!="undefined")  ?  typeNotif  :  "notice";
		//Temps d'affichage de la notification  (1 seconde pour 5 caracteres & 10 secondes minimum)
		var stayTime=parseInt(curMessage.length/5);
		if(stayTime<5)  {stayTime=10;}
		//Affiche la notification
		windowParent.$().toastmessage("showToast",{
			text		: curMessage,
			type		: type,
			position	: "top-center",
			stayTime	: (stayTime*1000)//stayTime en microsecondes
		});
	}
}

/*******************************************************************************************************
 * REDIRECTION DEPUIS UNE PAGE PRINCIPALE OU LIGHTBOX : CONFIRME SI BESOIN LA FERMETURE D'UN FORMULAIRE
 *******************************************************************************************************/
function redir(adress)
{
	if(closeFormConfirmed()==false)  {return false;}
	location.href=adress;
}
/*IDEM : REDIRECTION VIA BALISE "<A HREF>"*/
$(function(){
	$("a[href]").click(function(event){
		if(closeFormConfirmed()==false)  {event.preventDefault(); return false;}//Stop l'action par défaut
	});
});

/**************************************************************************************************
 * CONFIRME SI BESOIN LA FERMETURE D'UN FORMULAIRE EN COURS D'EDITION  (page principale ou lightbox)
 **************************************************************************************************/
function closeFormConfirmed()
{
	if(windowParent.confirmCloseForm==false || confirm(windowParent.labelConfirmCloseForm))	{windowParent.confirmCloseForm=false;  return true;}	//Réinit "confirmCloseForm" pour éviter un autre "confirm()" via un "redir()"
	else																					{return false;}											//Annule la fermeture du formulaire
}

/**************************************************************************************************
 * BUTTON SUBMIT : AFFICHE L'ICONE "LOADING"  &&  "DISABLE" DURANT 5 SEC.
 **************************************************************************************************/
function submitButtonLoading()
{
	$(".submitButtonLoading").css("visibility","visible");
	$("form button").css("background","#eee").prop("disabled",true);//"#eee" : couleur du "submitButtonLoading"
	setTimeout(function(){
		$(".submitButtonLoading").css("visibility","hidden");
		$("form button").css("background","initial").prop("disabled",false);
	}, 5000);
}

/**************************************************************************************************
 * OUVRE UNE LIGHTBOX
 * Ne pas lancer via via "href" : plus souple et n'interfère pas avec "stopPropagation()" des "menuContext"
 * tester via : edit object, open pdf/mp3/mp4, userMap, inline html
 **************************************************************************************************/
function lightboxOpen(urlSrc)
{
	////	PDF + affichage sur mobile : OUVRE UNE NOUVELLE PAGE
	if(/\.pdf$/i.test(urlSrc) && isMobile())	{window.open(urlSrc);}
	////	ON EST DANS UNE LIGHTBOX : RELANCE DEPUIS LA PAGE "PARENT"
	else if(isMainPage!==true)					{parent.lightboxOpen(urlSrc);}
	////	LIGHTBOX "INLINE" : LECTEUR MP3 OU VIDEO
	else if(/\.(mp3|mp4|webm)$/i.test(urlSrc)){
		var mediaUrlSrc=(/\.mp3$/i.test(urlSrc))  ?  '<div><audio controls><source src="'+urlSrc+'" type="audio/mpeg">HTML5 required</audio></div>'  :  '<video controls><source src="'+urlSrc+'" type="video/'+extension(urlSrc)+'">HTML5 required</video>';
		$.fancybox.open({type:"inline", src:mediaUrlSrc, buttons:['close']});
	}
	////	LIGHTBOX "IFRAME" : AFFICHAGE PAR DEFAUT
	else{
		$.fancybox.open({
			type:"iframe",
			src:urlSrc,
			opts:{
				buttons:['close'],										//Affiche uniquement le bouton "close"
				autoFocus:false,										//Pas de focus automatique sur le 1er element du formulaire!
				beforeClose:function(){ return closeFormConfirmed(); }	//Affiche un formulaire : confirme la fermeture du lightbox
			}
		});
	}
}

/**************************************************************************************************
 * RESIZE LE WIDTH D'UNE LIGHTBOX  : APPELÉ DEPUIS UNE LIGHTBOX
 **************************************************************************************************/
function lightboxSetWidth(iframeBodyWidth)
{
	//Page entièrement chargé (pas de "$(function(){})" sinon peut poser problème sur Firefox & co)
	window.onload=function(){
		//Width définie en pixel/pourcentage : convertie en entier pixel
		if(/px/i.test(iframeBodyWidth))		{iframeBodyWidth=iframeBodyWidth.replace("px","");}									
		else if(/%/.test(iframeBodyWidth))	{iframeBodyWidth=($(windowParent).width()/100) * iframeBodyWidth.replace("%","");}
		//Width du contenu > Width de la page : le width devient celui de la page "parent"
		if(iframeBodyWidth>$(windowParent).width())  {iframeBodyWidth=$(windowParent).width();}
		//Définie le "max-width" de l'iframe (pas de "width", car cela peut afficher un scroll horizontal à l'agrandissement de la lightbox : cf. "lightboxResize()")
		if(isValue(iframeBodyWidth))  {$("body").css("max-width",parseInt(iframeBodyWidth));}
	};
}

/**************************************************************************************************
 * REDUIT/AGRANDIT LA HAUTEUR DE L'IFRAME "LIGHTBOX"  (après fadeIn(), FadeOut(), modif du TinyMce)
 **************************************************************************************************/
function lightboxResize()
{
	if(isMainPage!=true && windowParent.$(".fancybox-iframe").isVisible()){																//Verif si le lightbox est affiché
		if(typeof lightboxResizeTimeout!="undefined")  {clearTimeout(lightboxResizeTimeout);}											//Pas de cumule des setTimeout (cf. multiples show(), fadeIn() à l'affichage du lightbox)
		lightboxResizeTimeout=setTimeout(function(){																					//Lance le resize avec un timeout 300ms minimum (cf. "$.fx.speeds._default=100")
			if(typeof lightboxHeightOld=="undefined" || lightboxHeightOld < windowParent.$(".fancybox-iframe").contents().height()){	//Verif : 1er affichage du lightbox ou "fadeIn()" ou modif du tinymce
				windowParent.$.fancybox.getInstance().update();																			//Resize du lightbox!
				lightboxHeightOld=windowParent.$(".fancybox-iframe").contents().height();												//Enregistre la taille du contenu du lightbox (après update)
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

/**************************************************************************************************
 * CONFIRME UNE SUPPRESSION PUIS REDIRIGE POUR EFFECTUER LA SUPPRESION
 **************************************************************************************************/
function confirmDelete(redirUrl, labelConfirmDbl, ajaxControlUrl, objLabelId)
{
	////	"labelConfirmDelete" de "VueStructure.php" ("Voulez-vous supprimer...")  &  Ajoute si besoin le label d'un objet (cf "menuContextLabel" et "objLabelId")
	var labelConfirm=(isValue(labelConfirmDelete))  ?  "\n "+labelConfirmDelete  :  null;
	if(isValue(objLabelId) && $("#"+objLabelId).exist())  {labelConfirm+=" \n \n -> "+$("#"+objLabelId).text();}
	////	Confirmation principale
	if(isValue(labelConfirm) && confirm(labelConfirm)==false)  {return false;}
	////	Double confirmation (Ex: "labelConfirmDeleteDbl" de "VueStructure.php" ou "SPACE_confirmDeleteDbl")
	if(isValue(labelConfirmDbl) && confirm(labelConfirmDbl)==false)  {return false;}
	////	Controle Ajax pour une suppression de dossier : "Certains sous-dossiers ne vous sont pas accessibles [...] confirmer ?"
	if(isValue(ajaxControlUrl)){
		$.ajax({url:ajaxControlUrl,dataType:"json"}).done(function(result){
			if(result.confirmDeleteFolderAccess && confirm(result.confirmDeleteFolderAccess)==false)  {return false;}
			if(result.notifyBigFolderDelete)  {notify(result.notifyBigFolderDelete,"warning");}//Notify "merci de patienter un instant avant la fin du processus"
		});
	}
	////	Redirection pour executer la suppression
	redir(redirUrl);
}


/******************************************************************************************************************************************/
/*******************************************	SPECIFIC FUNCTIONS	********************************************************/
/******************************************************************************************************************************************/


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
		if(targetRight=="1")		{$("#targetLine"+targetId).addClass("lineSelect sAccessRead");}	//Sélectionne la box "user"
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
 * SWITCH LE "LIKE" D'UN OBJET : UPDATE LE "menuCircle"
 **************************************************************************************************/
function usersLikeUpdate(typeId)
{
	if(isValue(typeId)){
		$.ajax({url:"?ctrl=object&action=usersLike&typeId="+typeId, dataType:"json"}).done(function(result){			//Requête Ajax pour switcher le "like"
			var menuId="#usersLike_"+typeId;																			//Id du menu
			if(result.likeNb==0)	{$(menuId).addClass("hideMiscMenu").find(".menuCircle").html("");}					//Masque l'icone et le nb de likes
			else					{$(menuId).removeClass("hideMiscMenu").find(".menuCircle").html(result.likeNb);}	//Affiche l'icone..
			$(menuId).attr("title",result.likeTooltip).tooltipster("destroy").tooltipster(tooltipsterOptions);			//Tooltip le menu
			$(menuId).effect("pulsate",{times:1},300);																	//Pulsate rapide du menu
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