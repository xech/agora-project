<!doctype html>
<html lang="<?= Txt::trad("HEADER_HTTP") ?>" id="<?= Ctrl::$isMainPage==true?'htmlMainPage':'htmlLightbox' ?>">
	<head>
		<!-- AGORA-PROJECT :: UNDER THE GENERAL PUBLIC LICENSE V2 :: http://www.gnu.org -->
		<meta charset="UTF-8">
		<meta content="text/html; charset=utf-8" http-equiv="Content-Type">
		<meta http-equiv="content-language" content="<?= Txt::trad("HEADER_HTTP") ?>">
		<meta http-equiv="X-UA-Compatible" content="IE=Edge"><!--IE : mode de compatibilité via Edge-->
		<meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no"><!--Pas de zoom en responsive-->
		<link rel="icon" type="image/gif" href="app/img/favicon.png" />
		<!--REFERENCEMENT-->
		<title><?= !empty(Ctrl::$agora->name) ? Ctrl::$agora->name : "Omnispace.fr - Agora-Project" ?></title>
		<meta name="Description" content="<?= !empty(Ctrl::$agora->description) ? Ctrl::$agora->description : "Omnispace.fr - Agora-Project" ?>">
		<meta name="application-name" content="Agora-Project">
		<meta name="application-url" content="https://www.agora-project.net">
		<!-- JQUERY & JQUERY-UI -->
		<script src="app/js/jquery-3.6.0.min.js"></script>
		<script src="app/js/jquery-ui/jquery-ui.min.js"></script>
		<script src="app/js/jquery-ui/datepicker-<?= Txt::trad("DATEPICKER") ?>.js"></script><!--traduction-->
		<link rel="stylesheet" href="app/js/jquery-ui/jquery-ui.css">
		<!-- JQUERY PLUGINS -->
		<script src="app/js/fancybox/dist/jquery.fancybox.min.js"></script>
		<link  href="app/js/fancybox/dist/jquery.fancybox.css" rel="stylesheet">
		<script type="text/javascript" src="app/js/tooltipster/tooltipster.bundle.min.js"></script>
		<link rel="stylesheet" type="text/css" href="app/js/tooltipster/tooltipster.bundle.min.css">
		<link rel="stylesheet" type="text/css" href="app/js/tooltipster/tooltipster-sideTip-shadow.min.css">
		<script type="text/javascript" src="app/js/toastmessage/jquery.toastmessage.js"></script>
		<link rel="stylesheet" type="text/css" href="app/js/toastmessage/toastmessage.css">
		<script src="app/js/jquery-confirm/jquery-confirm.min.js"></script>
		<link rel="stylesheet" href="app/js/jquery-confirm/jquery-confirm.min.css">
		<script src="app/js/timepicker/jquery.timepicker.min.js"></script>
		<link rel="stylesheet" type="text/css" href="app/js/timepicker/jquery.timepicker.css">
		<!-- JAVASCRIPT & CSS PRINCIPAUX (TOUJOURS À LA FIN!)-->
		<script src="app/js/common-<?= VERSION_AGORA?>.js"></script>
		<link href="app/css/common-<?= VERSION_AGORA?>.css" rel="stylesheet" type="text/css">
		<link href="app/css/<?= $skinCss ?>.css" rel="stylesheet" type="text/css">
	
		<script>
		////	Alerte pour MSIE (sauf IE-11: encore très utilisé)
		if(/msie/i.test(navigator.userAgent))  {alert("<?= Txt::trad("ieObsolete") ?>");}
		////	Divers params
		isMainPage=<?= Ctrl::$isMainPage==true ? "true" : "false" ?>;
		windowParent=(isMainPage==true) ? window : window.parent;//Si l'espace est intégré dans un Iframe (cf. redirection "invisible" de domaine)
		confirmCloseForm=false;//Init la confirmation de fermeture de page (cf. lightbox d'édition)
		////	Labels de "common.js"
		labelCancel					="<?= Txt::trad("cancel") ?>";
		labelConfirmCloseForm		="<?= Txt::trad("confirmCloseForm") ?>";
		labelConfirmDelete			="<?= Txt::trad("confirmDelete") ?>";
		labelSpecifyLoginPassword	="<?= Txt::trad("specifyLoginPassword") ?>";
		labelDateBeginEndControl	="<?= Txt::trad("beginEndError") ?>";
		labelUploadMaxFilesize		="<?= File::uploadMaxFilesize("error") ?>";
		valueUploadMaxFilesize		=<?= File::uploadMaxFilesize() ?>;
		////	Au chargement de la page
		$(function(){
			<?php
			////	Affiche si besoin des notifications
			foreach(Ctrl::$notify as $tmpNotif){
				if(Txt::isTrad($tmpNotif["message"]))  {$tmpNotif["message"]=Txt::trad($tmpNotif["message"]);}//notif à traduire?
				echo 'notify("'.$tmpNotif["message"].'", "'.$tmpNotif["type"].'");';
			}
			?>
			//// Responsive : Affiche le bouton en bas de page pour ajouter un nouvel element
			if(isMobile()){
				var addElemButton=$("#pageModMenu img[src*='plus.png']").first().parents(".menuLine");//Sélectionne le div ".menuLine" du premier bouton "Ajouter"
				if(addElemButton.exist())  {$("#respAddButton").attr("onclick",addElemButton.attr("onclick")).show();}//Ajoute l'attribut "onclick" au bouton responsive, puis affiche ce bouton
			}
		});
		</script>

		<?php
		////	FOOTER JAVASCRIPT DU HOST ?
		if(Ctrl::isHost())  {Host::footerJsNotify();}
		?>

		<style>
		/*WALLPAPER EN PAGE PRINCIPALE*/
		@media screen and (min-width:1024px){
			<?= isset($pathWallpaper) ? "html  {background:url('".addslashes($pathWallpaper)."') no-repeat center fixed;background-size:cover;}" : null ?>/*"cover": background fullsize*/
		}

		/*FOOTER*/
		#pageFooterHtml, #pageFooterIcon			{position:fixed; bottom:0px; z-index:20; display:inline-block; font-weight:normal;}/*pas de margin*/
		#pageFooterHtml								{left:80px; padding:5px; padding-right:10px; color:#eee; font-size:1.1em; text-shadow:0px 0px 9px #000;}/*"Left:80px" pour pouvoir afficher l'icone du messengerStandby*/
		#pageFooterIcon								{right:2px; bottom:3px;}
		#pageFooterIcon img							{max-height:65px; max-width:200px;}
		#pageFooterSpecial							{display:inline-block; margin:0px 0px -7px -7px; background-color:rgba(0,0,0,0.7); border-radius:5px; padding:8px; color:#c00; font-weight:bold;}/*host*/
		#respAddButton, #respMenuBg, #respMenuMain {display:none;}/*Masquer par défaut les principaux elements responsives*/

		/*RESPONSIVE*/
		@media screen and (max-width:1023px){
			#pageFooterHtml, #pageFooterIcon		{visibility:hidden;}/*pas de "display:none" pour laisser de la marge avec le contenu de la page pour le Messenger/Livecounter et le "respAddButton"*/
			/*Menu responsive : cf. "common.js"*/
			#respAddButton							{z-index:20; position:fixed; bottom:8px; right:8px; filter:drop-shadow(0px 2px 4px #ccc);}/*Bouton d'ajout d'elem. "z-index" identique aux menus contextuels*/
			#respMenuMain, #respMenuBg				{position:fixed; top:0px; right:0px; height:100%;}
			#respMenuBg								{z-index:100; width:100%; background-color:rgba(0,0,0,0.7);}/*z-index à 100 : idem ".menuContext"*/
			#respMenuMain							{z-index:101; max-width:330px!important; overflow:auto; padding:10px; padding-top:30px; font-size:1.1em!important; background:linear-gradient(135deg,<?= @Ctrl::$agora->skin=='black'?'#555,#333':'#eee,#fff' ?> 100px);}/*max-width: cf. "#pageModuleMenu"*/
			#respMenuMain #respMenuClose			{position:absolute; top:7px; right:7px;}/*tester avec Ionic*/
			#respMenuMain .menuLine					{padding:3px;}/*uniformise la présentation (cf. menu espace ou users)*/
			#respMenuMain .menuLine>div:first-child	{padding-right:10px;}/*idem*/
			#respMenuMain hr						{background:#ddd; margin-top:15px; margin-bottom:15px;}/*surcharge*/
			#respMenuTwo							{display:none; margin-top:10px; border-radius:5px; <?= @Ctrl::$agora->skin=="black" ? "background:#333;border:solid 1px #555;" : "background:#f5f5f5;border:solid 1px #ddd;" ?>}/*cf. style de ".vHeaderModule" en responsive*/
		}

		/*IMPRESSION*/
		@media print{
			[id^='pageFooter']	{display:none!important;}
		}
		</style>
	</head>

	<body>
		<?php
		////	VUES PRINCIPALES : MENU DU HEADER + CORPS DE LA PAGE + MESSENGER
		if(!empty($headerMenu))		{echo $headerMenu;}
		if(!empty($mainContent))	{echo $mainContent;}
		if(!empty($messenger))		{echo $messenger;}

		////	RESPONSIVE (cf. common.js) : MENU RESPONSIVE (peut fusionner 2 menus. Exple: liste des modules & menu context du module)  &&  ICONE "PLUS" D'AJOUT D'ELEMENT
		if(Req::isMobile()){
			echo "<div id='respMenuBg'></div>
				  <div id='respMenuMain'>
					<div id='respMenuClose'><img src='app/img/closeResp.png'></div>
					<div id='respMenuContent'> <div id='respMenuOne'></div> <div id='respMenuTwo'></div> </div>
				  </div>
				  <div id='respAddButton'><img src='app/img/plusResp.png'></div>";
		}

		////	PAGE PRINCIPALE : TEXTE PERSONNALISÉ DU FOOTER (OU SCRIPT)  &&  ICONE DE L'ESPACE
		if(Ctrl::$isMainPage==true && is_object(Ctrl::$agora)){
			echo "<div id='pageFooterHtml'>".Ctrl::$agora->footerHtml."</div>
				  <div id='pageFooterIcon'><a href=\"".$pathLogoUrl."\" target='_blank' title=\"".Txt::tooltip($pathLogoTitle)."\"><img src=\"".Ctrl::$agora->pathLogoFooter()."\"></a></div>";
		}
		?>
	</body>
</html>