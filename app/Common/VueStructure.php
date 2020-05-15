<!doctype html>
<html lang="<?= Txt::trad("HEADER_HTTP") ?>" id="<?= Ctrl::$isMainPage==true?'htmlMainPage':'htmlLightbox' ?>">
	<head>
		<!-- AGORA-PROJECT :: UNDER THE GENERAL PUBLIC LICENSE V2 :: http://www.gnu.org -->
		<meta charset="UTF-8">
		<meta content="text/html; charset=utf-8" http-equiv="Content-Type">
		<meta http-equiv="content-language" content="<?= Txt::trad("HEADER_HTTP") ?>">
		<meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
		<link rel="icon" type="image/gif" href="app/img/favicon.png" />
		<title><?= !empty(Ctrl::$agora->name) ? Ctrl::$agora->name : "Omnispace.fr - Agora-Project" ?></title>
		<!--REFERENCEMENT-->
		<meta name="Description" content="<?= !empty(Ctrl::$agora->description) ? Ctrl::$agora->description : "Omnispace.fr - Agora-Project" ?>">
		<meta name="application-name" content="Agora-Project">
		<meta name="application-url" content="https://www.agora-project.net">
		<meta http-equiv="X-UA-Compatible" content="IE=Edge"><!--mode compatibilité IE-->
		<!-- JQUERY & JQUERY-UI -->
		<script src="app/js/jquery-3.4.1.min.js"></script>
		<script src="app/js/jquery-ui/jquery-ui.min.js"></script>
		<link rel="stylesheet" href="app/js/jquery-ui/jquery-ui.css">
		<script src="app/js/jquery-ui/datepicker-<?= Txt::trad("DATEPICKER") ?>.js"></script><!--langue du jquery-ui datepicker-->
		<!-- JQUERY PLUGINS -->
		<link  href="app/js/fancybox/dist/jquery.fancybox.css" rel="stylesheet">
		<script src="app/js/fancybox/dist/jquery.fancybox.min.js"></script>
		<script type="text/javascript" src="app/js/tooltipster/tooltipster.bundle.min.js"></script>
		<link rel="stylesheet" type="text/css" href="app/js/tooltipster/tooltipster.bundle.min.css">
		<link rel="stylesheet" type="text/css" href="app/js/tooltipster/themes/tooltipster-sideTip-shadow.min.css">
		<script type="text/javascript" src="app/js/toastmessage/jquery.toastmessage.js"></script>
		<link rel="stylesheet" type="text/css" href="app/js/toastmessage/toastmessage.css">
		<script src="app/js/timepicker/jquery.timepicker.min.js"></script>
		<link rel="stylesheet" type="text/css" href="app/js/timepicker/jquery.timepicker.css">
		<!-- JS & CSS DE L'AGORA -->
		<script src="app/js/common-3.7.2.js"></script><!--toujours après Jquery & plugins Jquery !!-->
		<link href="app/css/common-3.7.2.css" rel="stylesheet" type="text/css">
		<link href="app/css/<?= $skinCss ?>.css?v<?= VERSION_AGORA ?>" rel="stylesheet" type="text/css">
	
		<script>
		////	Alerte pour MSIE (sauf IE-11: encore très utilisé)
		if(/msie/i.test(navigator.userAgent))  {alert("<?= Txt::trad("ieObsolete") ?>");}
		////	Divers params
		isMainPage=<?= Ctrl::$isMainPage==true ? "true" : "false" ?>;
		windowParent=(isMainPage==true) ? window : window.parent;//Si l'espace est intégré dans un Iframe (cf. redirection "invisible" de domaine)
		confirmCloseForm=false;//Confirmation de fermeture de page (exple: lightbox d'édition)
		////	Divers labels de "common.js"
		labelConfirmCloseForm="<?= Txt::trad("confirmCloseForm") ?>";
		labelSpecifyLoginPassword="<?= Txt::trad("specifyLoginPassword") ?>";
		labelUploadMaxFilesize="<?= File::uploadMaxFilesize("error") ?>";
		valueUploadMaxFilesize=<?= File::uploadMaxFilesize() ?>;
		labelConfirmDelete="<?= Txt::trad("confirmDelete") ?>";
		labelDateBeginEndControl="<?= Txt::trad("beginEndError") ?>";
		labelEvtConfirm="<?= Txt::trad("CALENDAR_evtIntegrate") ?>";
		labelEvtConfirmNot="<?= Txt::trad("CALENDAR_evtNotIntegrate") ?>";
		////	Au chargement de la page
		$(function(){
			<?php
			////	Fermeture de lightbox ($msgNotif en parametre?)
			if(Ctrl::$lightboxClose==true)  {echo 'lightboxClose(null,"'.Ctrl::$lightboxCloseParams.'");';}
			////	Affiche si besoin des notifications (messages à traduire?)
			foreach(Ctrl::$msgNotif as $tmpNotif){
				if(Txt::isTrad($tmpNotif["message"]))  {$tmpNotif["message"]=Txt::trad($tmpNotif["message"]);}
				echo 'notify("'.$tmpNotif["message"].'","'.$tmpNotif["type"].'");';
			}
			?>
			//// Responsive : Affiche le bouton en bas de page pour ajouter un nouvel element
			if(isMobile()){
				var addElemButton=$("#pageModMenu img[src*='plus.png']").first().parents(".menuLine");//Sélectionne le div ".menuLine" du premier bouton "Ajouter"
				if(addElemButton.exist())  {$("#respAddButton").attr("onclick",addElemButton.attr("onclick")).show();}//Ajoute l'attribut "onclick" au bouton responsive, puis affiche ce bouton
			}
		});
		</script>

		<style>
		/*WALLPAPER EN PAGE PRINCIPALE*/
		@media screen and (min-width:1024px){
			html  {<?= isset($pathWallpaper) ? "background:url(".$pathWallpaper.") no-repeat center fixed;" : null ?> background-size:cover;}/*fullsize via "cover"*/
		}

		/*FOOTER*/
		#pageFooterHtml, #pageFooterIcon	{position:fixed; bottom:0px; z-index:20; display:inline-block; font-weight:normal;}/*pas de margin*/
		#pageFooterHtml						{left:0px; padding:5px; padding-right:10px; color:#eee; text-shadow:0px 0px 9px #000;}
		#pageFooterIcon						{right:2px; bottom:3px;}
		#pageFooterIcon img					{max-height:50px; max-width:200px;}
		#pageFooterSpecial					{display:inline-block; margin:0px 0px -7px -7px; background-color:rgba(0,0,0,0.7); border-radius:5px; padding:8px; color:#c00; font-weight:bold;}/*host*/
		#respAddButton, #respMenuBg, #respMenuMain {display:none;}/*Masquer par défaut les principaux elements responsives*/

		/*RESPONSIVE*/
		@media screen and (max-width:1023px){
			#pageFooterHtml, #pageFooterIcon		{visibility:hidden;}/*pas de "display:none" pour laisser la marge au "respAddButton" et au Livecounter*/
			/*Menu responsive : cf. "common.js"*/
			#respAddButton							{z-index:40; position:fixed; bottom:8px; right:8px; filter:drop-shadow(0px 2px 4px #ccc);}/*Bouton d'ajout d'elem. "z-index" identique aux menus contextuels*/
			#respMenuMain, #respMenuBg				{position:fixed; top:0px; right:0px; height:100%;}
			#respMenuBg								{z-index:100; width:100%; background-color:rgba(0,0,0,0.7);}
			#respMenuMain							{z-index:101; max-width:330px!important; overflow:auto; padding:10px; padding-top:30px; font-size:1.1em!important; background:linear-gradient(135deg,<?= Ctrl::$agora->skin=='black'?'#555,#333':'#eee,#fff' ?> 100px);}/*max-width: cf. "#pageModuleMenu"*/
			#respMenuMain #respMenuClose			{position:absolute; top:2px; right:2px; filter:invert(100%);}/*tester avec Ionic*/
			#respMenuMain .menuLine					{padding:3px;}/*uniformise la présentation (cf. menu espace ou users)*/
			#respMenuMain .menuLine>div:first-child	{padding-right:10px;}/*idem*/
			#respMenuMain hr						{background:#ddd; margin-top:15px; margin-bottom:15px;}/*surcharge*/
			#respMenuTwo							{display:none; margin-top:10px; <?= Ctrl::$agora->skin=="black" ? "background:#333;border:solid 1px #555;" : "background:#f5f5f5;border:solid 1px #ddd;" ?> border-radius:5px;}/*cf. style de ".vHeaderModule" en responsive*/
		}

		/*IMPRESSION*/
		@media print{
			[id^='pageFooter']	{display:none!important;}
		}
		</style>

		<?php
		////	Footer javascript du Host ?
		if(Ctrl::isHost())  {Host::footerJs();}
		?>
	</head>

	<body>
		<?php
		////	VUES PRINCIPALES : MENU DU HEADER + CORPS DE LA PAGE + MESSENGER
		echo $headerMenu.$mainContent.$messengerLivecounter;

		////	RESPONSIVE (cf. common.js) : MENU RESPONSIVE (peut fusionner 2 menus. Exple: liste des modules & menu context du module)  &&  ICONE "PLUS" D'AJOUT D'ELEMENT
		if(Req::isMobile()){
			echo "<div id='respMenuBg'></div>
				  <div id='respMenuMain'>
					<div id='respMenuClose'><img src='app/img/close.png'></div>
					<div id='respMenuContent'> <div id='respMenuOne'></div> <div id='respMenuTwo'></div> </div>
				  </div>
				  <div id='respAddButton'><img src='app/img/plusResp.png'></div>";
		}

		////	PAGE PRINCIPALE : FOOTER (TEXT PERSONNALISE OU SCRIPT)  &&  ICONE DE L'ESPACE
		if(Ctrl::$isMainPage==true && is_object(Ctrl::$agora)){
			echo "<div id='pageFooterHtml'>".Ctrl::$agora->footerHtml."</div>
				  <div id='pageFooterIcon'><a href=\"".$pathLogoUrl."\" target='_blank' title=\"".$pathLogoTitle."\"><img src=\"".Ctrl::$agora->pathLogoFooter()."\"></a></div>";
		}
		?>
	</body>
</html>