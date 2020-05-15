/**
*------------------------V3---------------------------
* This file is part of the Agora-Project Software package.
*
* @copyright (c) Agora-Project Limited <https://www.agora-project.net>
* @license GNU General Public License, version 2 (GPL-2.0)
*/


/*
 * WINDOW LOAD/RESIZE : "windowWidth" & CO (Tester sur Android/Ipad/Firefox. Ne pas placer dans "$(function(){..")
 */
window.onload=function(){
	if(isMainPage==true)
	{
		////	CHARGEMENT DE LA PAGE : ENREGISTRE "windowWidth"
		var reloadWidth=(isTouchDevice() && /windowWidth/i.test(document.cookie)==false);		//Verif si "windowWidth" est déjà enregistré pour "Req::isMobile()" (Toujours en premier!)
		document.cookie="windowWidth="+$(window).width();										//Enregistre ensuite "windowWidth"
		if(reloadWidth===true)  {setTimeout(function(){document.location.reload(true);},500);}	//Reload la page si besoin (Timeout le tps d'enregistrer "windowWidth". Toujours en dernier!)
		////	RESIZE DE FENETRE : ENREGISTRE "windowWidth" & RELANCE "mainPageDisplay()"
		window.onresize=function(){
			document.cookie="windowWidth="+$(window).width();
			if(typeof mainPageDisplayTimeout!="undefined")  {clearTimeout(mainPageDisplayTimeout);}//Pas de cumul de Timeout
			mainPageDisplayTimeout=setTimeout(function(){ mainPageDisplay(true); },100);
		};
		////	CHANGE D'ORIENTATION : RELOAD LA PAGE  (Timeout le tps de finir le "resize" et enregistrer "windowWidth")
		window.onorientationchange=function(){ setTimeout(function(){document.location.reload(true);},500); };
	}
};

/*
 * DOCUMENT READY : LANCE LES FONCTIONS DE BASE
 */
$(function(){
	mainPageDisplay(true);	//Title via Tooltipster / Gallerie d'image via LightBox / largeur des blocks d'objet / etc.
	initMenuContext();		//Initialise les menus contextuels
});

/*
 * DOCUMENT READY : AJOUTE DE NOUVELLES FONCTIONS À JQUERY
 */
$(function(){
	////	Vitesse par défaut des effets "fadeIn()", "toggle()", etc
	$.fx.speeds._default=100;
	////	Verifie l'existance d'un element
	$.fn.exist=function(){
		return (this.length>0) ? true : false;
	};
	////	Verifie si l'element n'a pas de valeur ("empty") ...et aussi s'il existe
	$.fn.isEmpty=function(){
		return (this.length==0 || this.val().length==0) ? true : false;
	};
	////	Vérifie si l'element est un email (cf. "isMail()")
	$.fn.isMail=function(){
		var mailRegex=/^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
		return mailRegex.test(this.val());
	};
	////	Fait clignoter un element avec l'effet "pulsate"/"blink" (5 fois par défaut. Toujours lancé après chargement de la page!)
	$.fn.pulsate=function(times){
		if(typeof times=="undefined")  {var times=5;}
		this.effect("pulsate",{times:parseInt(times)},parseInt(times*1000));
	};
	////	Focus sur un champs surligné en rouge
	$.fn.focusRed=function(){
		this.addClass("focusRed").focus();
	};
	////	Affichage/Toggle d'element : "surcharge" des fonctions de Jquery pour appliquer le "lightboxResize()"
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


/*
 * DOCUMENT READY : INITIALISE LES PRINCIPAUX TRIGGERS
 * => Click/DblClick sur ".objContainer", Menu flottant, etc.
 */
$(function(){
	////	Triggers sur Mobile
	if(isMobile()){
		//Récupère la position du "touch" pour les swipes de menu ou autre (cf. "responsiveSwipe()")
		document.addEventListener("touchstart",function(event){  swipeBeginX=event.touches[0].clientX;  swipeBeginY=event.touches[0].clientY; });
		document.addEventListener("touchend",function(){  swipeBeginX=swipeBeginY=0;  });
	}
	////	Triggers en mode Desktop
	else
	{
		////	Click/DblClick sur les blocks conteneurs des objets : Sélectionne ou Edite un objet
		if($(".objContainer").exist())
		{
			//Trigger si ya click sur le block et que les actions par défaut sont autorisées
			$(".objContainer").click(function(){
				//Init
				var blockId="#"+this.id;
				timeDblClick=300;//intervalle entre 2 clics (pas plus de 300ms: cela doit rester un raccourcis du menu contextuel)
				if(typeof timeLastClick=="undefined")	{timeLastClick=Date.now();  containerIdLastClick=this.id;}
				//Double click?
				diffNowAndLastClick=(Date.now()-timeLastClick);
				var isDblClick=(diffNowAndLastClick>10 && diffNowAndLastClick<timeDblClick && containerIdLastClick==this.id);
				//Action sur l'objet
				if(isDblClick==true && $(blockId).attr("data-urlEdit"))		{lightboxOpen($(blockId).attr("data-urlEdit"));}//dblClick + "data-urlEdit" => édition d'objet
				else if(isDblClick==false && typeof objSelect=="function")	{objSelect(this.id);}							//click + fonction "objSelect()" => lance la fonction (cf. "VueObjMenuSelection.php")
				//Update "lastClickTime" & "containerIdLastClick"
				timeLastClick=Date.now();
				containerIdLastClick=this.id;
			});
			//Pas de sélection si on clique un lien d'un bloc
			$(".objContainer a").click(function(event){ event.stopPropagation(); });
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

/*
 * DOCUMENT READY : INITIALISE LES CONTROLES DES CHAMPS
 * => Datepickers, Timepicker, FileSize controls, Integer, etc.
 */
$(function(){
	////	Formulaire modifié : passe "confirmCloseForm" à "true" pour la confirmation de fermeture (".noConfirmClose" : sauf les forms de connexion and co. Ajoute "parent" pour cibler les "form" de lightbox)
	setTimeout(function(){
		$("form:not(.noConfirmClose)").find("input,select,textarea").on("change keyup",function(){ windowParent.confirmCloseForm=true; });
	},500);//500ms après l'init et préremplissage du formulaire

	////	Init le Datepicker jquery-UI
	$(".dateInput, .dateBegin, .dateEnd").datepicker({
		dateFormat:"dd/mm/yy",
		firstDay:1,
		showOtherMonths: true,
		selectOtherMonths: true,
		onSelect:function(date){
			//Select .dateBegin -> bloque la date minimum de .dateEnd (mais pas inversement!)
			if($(this).hasClass("dateBegin"))	{$(".dateEnd").datepicker("option","minDate",date);}
			//Trigger sur le champ concerné pour continuer l'action
			$(this).trigger("change");
		}
	});

	////	Init le plugin Timepicker (jquery-UI)
	if(jQuery().timepicker){
		var timepickerMinutesStep=(isMobile())  ?  5  :  15;//Palier de 5mn en "responsive", car champs en "readonly"
		$(".timeBegin, .timeEnd").timepicker({timeFormat:"H:i", step:timepickerMinutesStep});
	}

	////	Readonly sur les datepickers et timepickers
	if(isMobile())	{$(".dateInput,.dateBegin,.dateEnd,.timeBegin,.timeEnd").prop("readonly",true).css("background-color","white");}

	////	Controle les dates de début/fin
	$(".dateBegin, .dateEnd, .timeBegin, .timeEnd").change(function(){
		//Masque le champ H:M?
		if($(this).hasClass("dateBegin") || $(this).hasClass("dateEnd")){
			var timeClass=$(this).hasClass("dateBegin") ? ".timeBegin" : ".timeEnd";
			if($(this).isEmpty()==false)	{$(timeClass).show();}
			else							{$(timeClass).hide();  $(timeClass).val(null);}
		}
		//Controle des date/time
		if($(".dateBegin").isEmpty()==false || $(".dateEnd").isEmpty()==false)
		{
			//Controle des "H:M"
			if($(this).hasClass("timeBegin") || $(this).hasClass("timeEnd"))
			{
				//Champ à controler
				var timeClass=$(this).hasClass("timeBegin") ? ".timeBegin" : ".timeEnd";
				//controle Regex des H:M
				var timeRegex=/^[0-2][0-9][:][0-5][0-9]$/;
				if($(timeClass).isEmpty()==false && timeRegex.test($(timeClass).val())==false){
					notify("H:m error");
					$(timeClass).val(null);
					return false;
				}
				//précise H:M de fin si vide et début précisé
				if($(".timeEnd").isEmpty())  {$(".timeEnd").val($(".timeBegin").val());}
			}
			//Début après Fin : message d'erreur
			if($(".dateBegin").isEmpty()==false && $(".dateEnd").isEmpty()==false)
			{
				var timestampBegin=$(".dateBegin").datepicker("getDate").getTime()/1000;//getTime() renvoie des millisecondes..
				var timestampEnd=$(".dateEnd").datepicker("getDate").getTime()/1000;//idem
				if($(".timeBegin").isEmpty()==false)	{var hourMinute=$(".timeBegin").val().split(":");	timestampBegin=timestampBegin + (hourMinute[0]*3600) + (hourMinute[1]*60);}
				if($(".timeEnd").isEmpty()==false)		{var hourMinute=$(".timeEnd").val().split(":");		timestampEnd=timestampEnd + (hourMinute[0]*3600) + (hourMinute[1]*60);}
				if(timestampBegin > timestampEnd)
				{
					//Date/heure de fin reculé : message d'erreur
					if($(this).hasClass("dateEnd") || $(this).hasClass("timeEnd"))	{notify(labelDateBeginEndControl);}
					//Modif la date/heure de fin ("setTimeout" pour éviter une re-modif du timePicker)
					setTimeout(function(){
						$(".dateEnd").val($(".dateBegin").val());
						$(".timeEnd").val($(".timeBegin").val());
					},500);
				}
			}
		}
	});

	////	Controle la taille des fichiers des inputs "file"
	$("input[type='file']").change(function(){
		if($(this).isEmpty()==false && this.files[0].size > valueUploadMaxFilesize){
			$(this).val("");
			notify(labelUploadMaxFilesize);
		}
	});

	////	Affecte une couleur à un input "select" (chaque option doit avoir un attribut "data-color")
	$("select option").each(function(){
		if(this.getAttribute("data-color"))  {$(this).css("background-color",this.getAttribute("data-color")).css("color","#fff");}
	});
	$("select").change(function(){
		var optionColor=$(this).find("option:selected").attr("data-color");
		if(isEmptyValue(optionColor)==false)	{$(this).css("background-color",optionColor).css("color","#fff");}
		else									{$(this).css("background-color","#fff").css("color","#000");}
	});
	
	////	Pas d'autocomplétion sur TOUS les inputs des formulaires (password, dateBegin, etc) !
	$("form input").attr("autocomplete","off");
});

/*
 * DOCUMENT READY : INITIALISE L'AFFICHAGE DES PAGES PRINCIPALES
 * => Menu flottant / Largeur des blocks d'objet / Clic sur les blocks d'objet / Etc.
 */
function mainPageDisplay(firstLoad)
{
	////	Affiche les "Title" avec Tooltipster
	tooltipsterOptions={contentAsHTML:true,delay:400,maxWidth:500,theme:"tooltipster-shadow"};//variable globale
	$("[title]").not(".noTooltip,[title=''],[title*='http']").tooltipster(tooltipsterOptions);
	$("[title*='http']").tooltipster($.extend(tooltipsterOptions,{interactive:true}));//Ajoute "interactive" pour les "title" contenant des liens "http" (cf. description & co). On créé une autre instance car "interactive" peut interférer avec les "menuContext"

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
			if(isMobile()==false && $(document).height()==$(window).height())	{containerWidth=containerWidth-18;}//pas encore d'ascenseur : anticipe son apparition (sauf en responsive ou l'ascenseur est masqué)
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

		////	Affichage du footer
		$("#pageFooterHtml").css("max-width", parseInt($(window).width()-$("#pageFooterIcon").width()-20));//Pour que "pageFooterHtml" ne se superpose pas à "pageFooterIcon"
		setTimeout(function(){  $("#pageFull,#pageCenter").css("margin-bottom",footerHeight());  },300);//"margin-bottom" sous le contenu principal : pour pas être masqué par le Footer. Timeout car "footerHeight()" est fonction du "#livecounterMain" chargé en Ajax..
	}
}

/*
 * DOCUMENT READY : INITIALISE LES MENUS CONTEXTUELS
 * chaque launcher (icone/texte/block d'objet) doit avoir la propriété "for" correspondant à l'ID du menu  &&  une class "menuLaunch" (sauf pour les launcher de block d'objet) 
 */
function initMenuContext()
{
	////	MENU RESPONSIVE (width<=1024)
	if(isMobile()){
		//// Click d'un "launcher" (icone/texte) : affiche le menu responsive
		$(".menuLaunch").click(function(){
			if($("#respMenuContent").is(":visible")==false)	{showRespMenu(this.getAttribute("for"),this.getAttribute("forBis"));}			//Menu masqué : on l'affiche
			else											{$("#"+this.getAttribute("for")).addClass("menuContextSubMenu").slideToggle();}	//Menu déjà affiché : on affiche le sous-menu
		});
		//// Swipe sur la page pour afficher/masquer le menu
		document.addEventListener("touchmove",function(event){
			if(typeof swipeBeginX!="undefined"  &&  Math.abs(swipeBeginY - event.touches[0].clientY) < 40){					//Swipe horizontal de 40px maxi d'amplitude verticale
				if((event.touches[0].clientX - swipeBeginX) > 10)  {hideRespMenu(event.touches[0].clientX);}				//Swipe right : masque progressivement le menu  (swipe d'au moins 10px)
				else if((swipeBeginX - event.touches[0].clientX) > 50  &&  parseInt($(window).width()-swipeBeginX)<100){	//Swipe left : affiche le menu  (swipe d'au moins 50px, à moins de 100px du bord droit de la page)
					if($("#headerModuleTab").exist())		{showRespMenu("headerModuleTab","pageModMenu");}				//Affiche la liste des modules ("headerModuleTab")
					else if($("#headerMainMenu").exist())	{showRespMenu("headerMainMenu","pageModMenu");}					//Affiche sinon le menu principal de la page ("headerMainMenu")
				}
			}
		});
		//// Swipe terminé ("touchend") && menu en partie masqué => on le raffiche totalement (cf. position "right" du "hideRespMenu()")
		document.addEventListener("touchend",function(){
			if($("#respMenuMain").is(":visible") && parseInt($("#respMenuMain").css("right"))<0)  {$("#respMenuMain").css("right","0px");}
		});
		//// Click sur l'icone "close" ou le background du menu responsive : masque le menu
		$("#respMenuClose,#respMenuBg").click(function(){ hideRespMenu(); });
	}
	////	MENU NORMAL (tester sur tablette)
	else{
		//// Mouseover/Click d'un "launcher" (icone/texte) : affiche le menu classique
		$(".menuLaunch").on("mouseover click",function(){ showMenuContext(this); });
		//// Click Droit d'un block d'objet : affiche le menu context ("Return false" pour pas afficher le menu du browser)
		$(".objContainer[for^='objMenu_']").on("contextmenu",function(event){ showMenuContext(this,event);  return false; });
		//// Masque le menu dès qu'on le quitte
		$(".menuContext").on("mouseleave",function(){ $(".menuContext").hide(); });
	}
	////	Click/Survol le corps de la page : masque le menu contextuel
	$("#pageFull,#pageCenter").on("click mouseenter", function(){ $(".menuContext").hide(); });
	////	Click sur un menu context ou son launcher : pas de propagation au block ".objContainer" (exple: pour pas sélectionner un block d'objet via "objSelect()")
	$(".menuLaunch,.menuContext").click(function(event){ event.stopPropagation(); });
}
/*MENU NORMAL : AFFICHE LE MENU CONTEXT*/
function showMenuContext(thisLauncher, event)
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
	////	Positionne et affiche le menu
	if(menuPosY>15)  {menuPosX-=15;  menuPosY-=15;}//recentre le menu de 20px
	$(menuId).css("left",menuPosX+"px").css("top",menuPosY+"px").slideDown(20);//Positionne le menu. Affiche avec un rapide slide (pas de "show()"!)
	$(".menuContext").not(menuId).hide();//Masque les autres menus
}
/*MENU RESPONSIVE : AFFICHE LE MENU CONTEXT (cf. VueStructure.php)*/
function showRespMenu(menuOneSourceId, menuTwoSourceId)
{
	//// Id des menus d'origine qui seront intégrés au menu responsive
	respMenuOneSourceId="#"+menuOneSourceId;
	respMenuTwoSourceId=(typeof menuTwoSourceId=="string") ? "#"+menuTwoSourceId : null;
	//// Vérifie que le menu demandé existe bien
	if($(respMenuOneSourceId).exist())
	{
		//Déplace les menus demandés dans "#respMenuOne" et "#respMenuTwo"  ("appendTo()" conserve les listeners, contrairement à "html()")
		$(respMenuOneSourceId+">*").appendTo("#respMenuOne");
		if($(respMenuTwoSourceId).exist())  {$(respMenuTwoSourceId+">*").appendTo("#respMenuTwo");  $("#respMenuTwo").show();}
		//Affiche le menu et son contenu
		$("#respMenuOne,#respMenuBg").fadeIn(50);
		$("#respMenuMain").css("right","0px").show("slide",{direction:"right"});//Réinit si besoin la position "right" (cf. "hideRespMenu()"), Puis affiche le menu
		//Désactive le scroll de page en arriere plan
		$("body").css("overflow","hidden");
	}
}
/*MENU RESPONSIVE : MASQUE LE MENU RESPONSIVE*/
function hideRespMenu(swipeCurrentX)
{
	if($("#respMenuMain").is(":visible"))
	{
		//// Masque progressivement le menu sur les 80 premiers pixels de "swipe" (déplace le menu vers la droite en même temps que le swipe)
		if(typeof swipeCurrentX!="undefined" && parseInt($("#respMenuMain").css("right")) > -80)  {$("#respMenuMain").css("right", "-"+(swipeCurrentX-swipeBeginX)+"px");}
		//// Sinon on masque totalement le menu
		else{
			//Masque les menus
			$("#respMenuOne,#respMenuTwo,#respMenuBg").fadeOut(50);
			$("#respMenuMain").hide("slide",{direction: "right"});
			//Remet le contenu de "#respMenuOne" et "#respMenuTwo" dans leur div d'origine ("respMenuOneSourceId" et "respMenuTwoSourceId")
			$("#respMenuOne>*").appendTo(respMenuOneSourceId);
			if(respMenuTwoSourceId!=null)  {$("#respMenuTwo>*").appendTo(respMenuTwoSourceId);}
			//Réactive le scroll de page en arriere plan
			$("body").css("overflow","visible");
		}
	}
}

/*
 * Controle s'il s'agit d'un mail
 */
function isMail(mail)
{
	var mailRegex=/^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
	return mailRegex.test(mail);
}

/*
 * Controle la validité d'un password : au moins 6 caractères && au moins un chiffre && au moins une lettre
 */
function isValidPassword(password)
{
	return (password.length>=6 && /[0-9]/.test(password) && /[a-z]/i.test(password));
}

/*
 * Cherche une expression dans une chaine de caracteres
 */
function find(needle, haystack)
{
	//Converti tout en minuscule & recherche la position de needle dans haystack
	if(typeof haystack!="undefined")  {return (haystack.toString().toLowerCase().indexOf(needle.toString().toLowerCase()) >= 0);}
}

/*
 * Affiche un message de notification (via le plugin Jquery "toastmessage")
 */
function notify(curMessage, typeNotif)
{
	if(typeof curMessage!="undefined")
	{
		//Type de notification :  "success" vert  /  "warning" jaune  /  "notice" bleu (par défaut)
		var type=(typeof typeNotif!="undefined")  ?  typeNotif  :  "notice";
		//Temps d'affichage de la notification  (1 seconde pour 10 caracteres & 5 secondes minimum)
		var stayTime=parseInt(curMessage.length/10);
		if(stayTime<5)  {stayTime=5;}
		//Affiche la notification
		windowParent.$().toastmessage("showToast",{
			text		: curMessage,
			type		: type,
			position	: "top-center",
			stayTime	: (stayTime*1000)//stayTime en microsecondes
		});
	}
}

/*
 * Controle les redirections de page
 */
function redir(adress)
{
	//On annule la fermeture d'un formulaire en cours  ?
	if(closeFormCanceled())  {return false;}
	//Redirection : depuis une page principale ou une lightbox
	location.href=adress;
}
/*Idem avec "<a href>"*/
$(function(){
	$("a").click(function(event){
		//On annule la fermeture d'un formulaire en cours  ?
		if(closeFormCanceled())  {event.preventDefault();  return false;}
	});
});

/*
 * Annule la fermeture du formulaire en cours d'édition (lightbox OU page principale)
 */
function closeFormCanceled()
{
	if(windowParent.confirmCloseForm==true && confirm(windowParent.labelConfirmCloseForm)==false)	{return true;}//Annule la fermeture du formulaire
	else																							{windowParent.confirmCloseForm=false;}//Réinit le "confirmCloseForm" pour éviter un nouveau "confirm()" via un "redir()"
}

/*
 * Ouvre une lightbox via une fonction (et non des "href" : plus souple + n'interfère pas avec "stopPropagation()" des "menuContext")
 * tests : edit object, open pdf/mp3, open mp4/webm, open userMap, open inline html
 */
function lightboxOpen(urlSrc)
{
	////	OUVRE UNE LIGHTBOX DEPUIS UNE LIGHTBOX (MODE RECURSIF)
	if(isMainPage!==true)						{parent.lightboxOpen(urlSrc);}
	/////	OUVRE UN PDF DANS UNE NOUVELLE PAGE (EN RESPONSIVE)
	else if(/pdf$/i.test(urlSrc) && isMobile())	{window.open(urlSrc);}
	////	OUVRE UN LIGHTBOX "INLINE" AVEC LECTEUR MP3/VIDEO
	else if(/(mp3|mp4|webm)$/i.test(urlSrc))
	{
		//MP3 OU VIDEO (tester aussi en responsive)
		if(/mp3$/i.test(urlSrc))	{var urlSrcBis='<div style="padding:45px;"><audio id="lightboxMedia" controls><source src="'+urlSrc+'" type="audio/mpeg">HTML5 is required</audio></div>';}
		else						{var urlSrcBis='<video id="lightboxMedia" controls><source src="'+urlSrc+'" type="video/'+extension(urlSrc)+'">HTML5 is required</video>';}
		//Ouverture du player Audio/Video
		$.fancybox.open({
			type:"inline",
			src:urlSrcBis,
			opts:{
				buttons:[],//mini bouton 'close' déjà affiché par défaut..
				afterShow:function(){
					$.fancybox.getInstance().update();//Dimentionne le lightbox en fonction du contenu
					$("#lightboxMedia").get(0).play();//Lance la lecture auto (pas dans la balise <video> car sinon la lecture continue à la fermeture du lightbox)
				}
			}
		});
	}
	////	OUVRE UNE LIGHTBOX "IFRAME" : AFFICHAGE PAR DEFAUT
	else
	{
		$.fancybox.open({
			type:"iframe",
			src:urlSrc,
			opts:{
				buttons:['close'],//Affiche uniquement le bouton "close"
				autoFocus:false,//Pas de focus automatique sur le 1er element du formulaire!
				afterShow:function(){
					$.fancybox.getInstance().update();//Dimentionne le lightbox en fonction du contenu
				},
				beforeClose:function(){
					if(closeFormCanceled())  {return false;}//Annule la fermeture du formulaire?
				}
			}
		});
	}
}

/*
 * Width d'une Lightbox : appelé depuis une lightbox (530px minimum pour l'édition d'un objet : cf. menu des droits d'accès)
 */
function lightboxSetWidth(iframeBodyWidth)
{
	$(function(){
		//Width définie en pixel/pourcentage : convertie en entier pixel
		if(find("px",iframeBodyWidth))		{iframeBodyWidth=iframeBodyWidth.replace("px","");}									
		else if(find("%",iframeBodyWidth))	{iframeBodyWidth=($(windowParent).width()/100) * iframeBodyWidth.replace("%","");}
		//Width du contenu > Width de la page : le width devient celui de la page "parent"
		if(iframeBodyWidth>$(windowParent).width())  {iframeBodyWidth=$(windowParent).width();}
		//Définie le "max-width" de l'iframe (pas de "width", car cela peut afficher un scroll horizontal à l'agrandissement de la lightbox : cf. "lightboxResize()")
		if(isEmptyValue(iframeBodyWidth)==false)  {$("body").css("max-width",parseInt(iframeBodyWidth));}
	});
}

/*
 * Agrandit si besoin la hauteur d'une Lightbox Iframe :  Suite à un fadeIn(), FadeOut(), etc  OU  au chargement du TinyMce
 */
function lightboxResize()
{
	//Resize si le lightbox est visible
	if(isMainPage!=true && windowParent.$(".fancybox-iframe").is(":visible"))
	{
		//Pas de cumul de Timeout
		if(typeof lightboxResizeTimeout!="undefined")  {clearTimeout(lightboxResizeTimeout);}
		//Ajoute un timeout de 350ms minimum (temps minimum pour laisser les "fadeIn" ou autre se faire : cf. "$.fx.speeds._default" à 100ms)
		lightboxResizeTimeout=setTimeout(function(){
			//Resize uniquement s'il y a augmentation de la hauteur (pas si ya diminution: évite ainsi les resizes trop fréquents)
			var lightboxHeightNew=windowParent.$(".fancybox-iframe").contents().height();
			if(typeof lightboxHeightOld=="undefined" || lightboxHeightNew > lightboxHeightOld){
				lightboxHeightOld=lightboxHeightNew;//MAJ du "lightboxHeightOld" qu'on garde en référence
				windowParent.$.fancybox.getInstance().update();//lance l'update!
			}
		},350);
	}
}

/*
 * Ferme le lightbox & reload la page principale (appelé depuis le lightbox)
 */
function lightboxClose(urlSpecific, urlMoreParms)
{
	var reloadUrl=(!isEmptyValue(urlSpecific))  ?  urlSpecific  :  parent.location.href;				//Url passée en parametre OU Url de la page "parent"
	if(find("msgNotif",reloadUrl))	{reloadUrl=reloadUrl.substring(0,reloadUrl.indexOf('&msgNotif'));}	//Enlève si besoin les anciens parametres "msgNotif"
	if(!isEmptyValue(urlMoreParms))	{reloadUrl+=urlMoreParms;}											//Ajoute si besoin de nouveaux parametres "msgNotif" ou autre
	parent.location.replace(reloadUrl);																	//Reload la page principale
}

/*
 * Navigation en mode "mobile" si le width est inférieur à 1024px  (IDEM Req.php && && Common.css)
 */
function isMobile()
{
	return (windowParent.document.body.clientWidth<1024);
}

/*
 * Navigation sur un appareil tactile (Android/Ipad/Iphone)
 */
function isTouchDevice()
{
	return (/android|iphone|ipad|BlackBerry/i.test(navigator.userAgent));
}

/*
 * Confirmer une suppression puis rediriger pour effectuer la suppresion
 */
function confirmDelete(redirUrl, labelConfirmDeleteDbl, ajaxControlUrl)
{
	////	"Confirmer la suppression ?"
	if(confirm(labelConfirmDelete)==false)  {return false;}
	////	"Cette action est définitive : confirmer tout de même ?" (ou un autre label)
	if(isEmptyValue(labelConfirmDeleteDbl)==false && confirm(labelConfirmDeleteDbl)==false)  {return false;}
	////	Suppression directe
	if(isEmptyValue(ajaxControlUrl))  {redir(redirUrl);}
	////	OU suppression après controle Ajax (dossier)
	else{
		$.ajax({url:ajaxControlUrl,dataType:"json"}).done(function(result){
			if(result.confirmDeleteFolderAccess && confirm(result.confirmDeleteFolderAccess)==false)  {return false;}	//Confirm "Certains sous-dossiers ne vous sont pas accessibles... confirmer?"
			if(result.notifyBigFolderDelete)  {notify(result.notifyBigFolderDelete,"warning");}							//Notify "merci de patienter un instant avant la fin du processus"
			redir(redirUrl);																							//Lance la suppression
		});
	}
}

/*
 * Scroll vers un element
 */
function toScroll(thisSelector)
{
	$("html,body").animate({scrollTop:Math.round($(thisSelector).offset().top-110)},200);//Enlève 110px du header menu fixe
}

/*
 * Extension d'un fichier (sans le point!)
 */
function extension(fileName)
{
	if(isEmptyValue(fileName)==false)	{return fileName.split('.').pop().toLowerCase();}
}

/*
 * Vérifie si une chaine est au format Json (cf. Omnispace)
 */
function isJsonString(string)
{
	try{
		JSON.parse(string);
	}catch(e){
		return false;
	}
	return true;
}

/*
 * Vérifie si une valeure est "empty" (équivalent à php)
 */
function isEmptyValue(value)
{
	return (value==null || typeof value=="undefined" || value=="" || value==0);
}


/***************************************************************************************************************************/
/*******************************************	SPECIFIC FUNCTIONS	********************************************************/
/***************************************************************************************************************************/

/*
 * Calcul la hauteur disponible pour le contenu principal de la page
 */
function availableContentHeight()
{
	//Height de la fenêtre (mais pas la page), moins la Position "top" du conteneur, moins le paddingTop du conteneur, moins le paddingBottom du conteneur, moins le Height du footer
	var containerSelectors="#pageCenterContent,#pageFullContent,.emptyContainer";
	return Math.round($(window).height() - $(containerSelectors).offset().top - parseInt($(containerSelectors).css("padding-top")) - parseInt($(containerSelectors).css("padding-bottom")) - footerHeight());
}

/*
 * Calcul la hauteur du footer
 */
function footerHeight()
{
	//Icone du footer / Text html du footer  /  LivecounterMain (recup la hauteur préétablie via CSS, le contenu du livecounter est chargé après via Ajax)
	var footerHeightTmp=0;
	$("#pageFooterHtml:visible,#pageFooterIcon:visible,#livecounterMain:visible").each(function(){
		if($(this).html().length>0 && footerHeightTmp<$(this).outerHeight(true))  {footerHeightTmp=$(this).outerHeight(true);}//Controle du ".length" car le contenu peut être vide mais quant même affiché ("&nbsp;" & co)
	});
	return footerHeightTmp+2;//+ 2px de marge (cf. "blox-shadow" des blocs)
}

/*
 * Confirmation (..ou pas) d'ajout d'un événement (modules Dashboard et Calendar)
 */
function proposedEventConfirm(_idCal, _idEvt, proposedEventDivId)
{
	//Init
	var confirmed=false;
	var ajaxUrl="?ctrl=calendar&action=proposedEventConfirm&targetObjId=calendar-"+_idCal+"&_idEvt="+_idEvt;
	//Demande de confirmation
	if(confirm(labelEvtConfirm))			{ajaxUrl+="&confirmed=1";  confirmed=true;}//Evt ajouté
	else if(confirm(labelEvtConfirmNot))	{ajaxUrl+="&confirmed=0";  confirmed=true;}//Evt rejeté (propostion refusée)
	//Lance la requête en Ajax
	if(confirmed==true){
		$.ajax(ajaxUrl).done(function(){
			//Recharge la page et le calendrier
			if(getUrlParam("ctrl",window.location.href)=="calendar")  {redir("?ctrl=calendar");}
			//Masque la proposition d'evt et masque tout le menu si ya plus aucune proposition
			else{
				$("#"+proposedEventDivId).hide();
				if($(".proposedEventList li:visible").length==0)  {$(".proposedEventLabel,.proposedEventList").hide();}
			}
		});
	}
}

/*
 * Affectations des Spaces<->Users : userEdit OU spaceEdit (Click de Label/Checkbox)
 */
function initSpaceAffectations()
{
	////	Sélectionne "allUsers" : toutes les checkboxes avec droit "user" sont passées en "disabled"+"checked" (sinon on les réactive et uncheck)
	$("input#allUsers").click(function(){
		var allUsersChecked=$(this).prop("checked");
		$(".spaceAffectInput[value$='_1']").prop("disabled",allUsersChecked).prop("checked",allUsersChecked);//Switch le "disabled"+"Checked"
		spaceAffectLabelStyle();//Réinit le style des labels (pas de "trigger" sur ".spaceAffectInput" : pour pas décocher les checkboxes "admin" déjà cochées)
	}).trigger("change");//"trigger" : init le style des labels au chargement de la page

	//// Click de Label (sauf "Tous les utilisateurs" : avec un attribut "for")
	$(".spaceAffectLabel").click(function(){
		//init
		var _idTarget=$(this).parent().attr("id").replace("targetLine","");//Si le div parent du label contient un "targetLine" : on récupère l'id de l'user ou de l'espace. Exple: "targetLine55" -> "55"
		var box1=".spaceAffectInput[value='"+_idTarget+"_1']";
		var box2=".spaceAffectInput[value='"+_idTarget+"_2']";
		//Bascule les checkboxes
		var boxToCheck=null;
		if($(box1).prop("disabled")==false && $(box1).prop("checked")==false && $(box2).prop("checked")==false)	{boxToCheck=box1;}
		else if($(box1).prop("checked") && $(box2).prop("checked")==false)										{boxToCheck=box2;}
		//Uncheck toutes les boxes (sauf celles "disabled")
		$(".spaceAffectInput[value^='"+_idTarget+"_']:not(:disabled)").prop("checked",false);
		//Check la box sélectionnée && Style des labels
		if(boxToCheck!=null)  {$(boxToCheck).prop("checked",true);}
		spaceAffectLabelStyle();
	});

	//// Click de Checkbox
	$(".spaceAffectInput").change(function(){
		var targetId=this.value.split("_")[0];//"55_2" : récup l'idSpace/idUser "55"
		$("[name='spaceAffect[]'][value^='"+targetId+"_']:not(:disabled)").not(this).prop("checked",false);//"uncheck" les autres box (sauf disabled)
		spaceAffectLabelStyle();//Style des labels
	});

	//// Init le style des labels
	spaceAffectLabelStyle();
};

/*
 * Applique un style à chaque label, en fonction de la checkbox cochée
 */
function spaceAffectLabelStyle()
{
	//Réinit le style des affectations
	$(".spaceAffectLine").removeClass("sLineSelect sAccessRead sAccessWrite");
	//Stylise les labels && la ligne sélectionnées
	$(".spaceAffectInput:checked").each(function(){
		//"55_2" : récup l'idSpace/idUser "55" et le droit "2"
		var targetId   =this.value.split("_")[0];
		var targetRight=this.value.split("_")[1];
		//Stylise la ligne
		if(targetRight=="2")		{$("#targetLine"+targetId).addClass("sLineSelect sAccessWrite");}
		else if(targetRight=="1")	{$("#targetLine"+targetId).addClass("sLineSelect sAccessRead");}
	});
}

/*
 * Récupère dans une Url la valeur d'un parametre
 */
function getUrlParam(paramName, url)
{
	paramName=paramName.replace(/[\[\]]/g, "\\$&");
	var regex=new RegExp("[?&]"+paramName+"(=([^&#]*)|&|#|$)");
	var results=regex.exec(url);
	if(!results)			{return null;}
	else if(!results[2])	{return '';}
	else					{return decodeURIComponent(results[2].replace(/\+/g," "));}
}

/*
 * Valide le "like"/"dontlike" d'un objet
 */
function usersLikeValidate(targetObjId, likeValue)
{
	if(isEmptyValue(targetObjId)==false && isEmptyValue(likeValue)==false)
	{
		//Requête Ajax
		$.ajax({url:"?ctrl=object&action=UsersLikeValidate&targetObjId="+targetObjId+"&likeValue="+likeValue, dataType:"json"}).done(function(result){
			//Init les id
			var menuIdLike		="#likeMenu_"+targetObjId+"_like";
			var menuIdDontLike	="#likeMenu_"+targetObjId+"_dontlike";
			var idCircleLike    =menuIdLike+" .menuCircle";
			var idCircleDontlike=menuIdDontLike+" .menuCircle";
			//Nb de likes/dontlikes dans les cercles
			$(idCircleLike+", "+idCircleDontlike).addClass("menuCircleHide");//Réinit (Masque par défaut)
			if(parseInt(result.nbLikes)>0)		{$(idCircleLike).removeClass("menuCircleHide").html(result.nbLikes);}
			if(parseInt(result.nbDontlikes)>0)	{$(idCircleDontlike).removeClass("menuCircleHide").html(result.nbDontlikes);}
			//Liste des users dans les tooltip/title
			$(menuIdLike).attr("title",result.usersLikeList).tooltipster("destroy").tooltipster(tooltipsterOptions);
			$(menuIdDontLike).attr("title",result.usersDontlikeList).tooltipster("destroy").tooltipster(tooltipsterOptions);
			//Fait clignoter le like/dontlike  &&  affiche le tooltip (trigger mouseover)
			var menuId=(likeValue=="like") ? menuIdLike : menuIdDontLike;
			$(menuId).effect("pulsate",{times:1},300).trigger("mouseover");
			//Affichage permanent du "objMiscMenus"
			if(result.nbLikes>0 || result.nbDontlikes>0)  {$(menuId).parent(".objMiscMenus").find(".objMenuLikeComment").addClass("showMiscMenu");}
		});
	}
}

/*
 * Check/uncheck un groupe d'users (tester l'edition d'evt avec les groupes pour affectation aux agendas ET les groupes pour notification par email)
 * Note : les inputs des groupes doivent avoir un "name" spécifique ET les inputs d'user doivent avoir une propriété "data-idUser"
 * On passe en paramètre le "this" de l'input du groupe ET l'id du conteneur des inputs d'users ("idContainerUsers") pour définir le périmère des inputs d'users
 */
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
				var otherGroupUserIds=$(this).val().split(",");
				if($.inArray(idUsers[tmpKey],otherGroupUserIds)!==-1)  {userChecked=true;}
			});
		}
		//Check l'user courant
		$(idContainerUsers+" input[data-idUser="+idUsers[tmpKey]+"]:enabled").prop("checked",userChecked).trigger("change");//"trigger" pour le style du label
	}
}