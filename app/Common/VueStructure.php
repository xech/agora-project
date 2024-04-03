<!doctype html>
<html lang="<?= Txt::trad("CURLANG") ?>" id="<?= Ctrl::$isMainPage==true?'htmlMainPage':'htmlLightbox' ?>">
	<head>
		<!-- AGORA-PROJECT :: UNDER THE GENERAL PUBLIC LICENSE V2 :: http://www.gnu.org -->
		<meta charset="UTF-8">
		<meta content="text/html; charset=utf-8" http-equiv="Content-Type">
		<meta http-equiv="content-language" content="<?= Txt::trad("CURLANG") ?>">
		<meta http-equiv="X-UA-Compatible" content="IE=Edge"><!--IE : mode de compatibilité via Edge-->
		<meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no"><!--Pas de zoom sur mobile-->
		<link rel="icon" type="image/png" href="app/img/favicon.png">
		<!--REFERENCEMENT-->
		<title><?= !empty(Ctrl::$agora->name) ? Ctrl::$agora->name : "Omnispace.fr - Agora-Project" ?></title>
		<meta name="Description" content="<?= !empty(Ctrl::$agora->description) ? Ctrl::$agora->description : "Omnispace.fr - Agora-Project" ?>">
		<meta name="application-name" content="Agora-Project">
		<meta name="application-url" content="https://www.agora-project.net">
		<!-- JQUERY & JQUERY-UI -->
		<script src="app/js/jquery-3.7.1.min.js"></script>
		<script src="app/js/jquery-ui/jquery-ui.min.js"></script>
		<script src="app/js/jquery-ui/datepicker-<?= Txt::trad("CURLANG") ?>.js"></script><!--traduction-->
		<link rel="stylesheet" href="app/js/jquery-ui/jquery-ui.css">
		<!-- JQUERY PLUGINS -->
		<script src="app/js/fancybox/dist/jquery.fancybox.min.js"></script>
		<link  href="app/js/fancybox/dist/jquery.fancybox.css" rel="stylesheet">
		<script type="text/javascript" src="app/js/tooltipster/tooltipster.bundle.min.js"></script>
		<link rel="stylesheet" type="text/css" href="app/js/tooltipster/tooltipster.bundle.css">
		<link rel="stylesheet" type="text/css" href="app/js/tooltipster/tooltipster-sideTip-shadow.min.css">
		<script type="text/javascript" src="app/js/toastmessage/jquery.toastmessage.js"></script>
		<link rel="stylesheet" type="text/css" href="app/js/toastmessage/toastmessage.css">
		<script src="app/js/jquery-confirm/jquery-confirm.min.js"></script>
		<link rel="stylesheet" href="app/js/jquery-confirm/jquery-confirm.min.css">
		<script src="app/js/timepicker/jquery.timepicker.min.js"></script>
		<link rel="stylesheet" type="text/css" href="app/js/timepicker/jquery.timepicker.css">
		<!-- JAVASCRIPT & CSS PRINCIPAUX (TOUJOURS À LA FIN!)-->
		<script src="app/js/common-<?= Req::appVersion() ?>.js"></script>
		<link href="app/css/common-<?= Req::appVersion() ?>.css" rel="stylesheet" type="text/css">
		<link href="app/css/<?= $skinCss ?>.css" rel="stylesheet" type="text/css">
	
		<script>
		////	Parametres de base et labels de "common.js"
		isMainPage=<?= Ctrl::$isMainPage==true ? "true" : "false" ?>;
		windowParent=(isMainPage==true) ? window : window.parent;//Accès à la page principale via common.js
		confirmCloseForm=false;//Confirmation de fermeture de formulaire (Ex: édition d'objet)
		labelConfirmCloseForm		="<?= Txt::trad("confirmCloseForm") ?>";
		labelConfirmDelete			="<?= Txt::trad("confirmDelete") ?>";
		labelConfirmDeleteDbl		="<?= Txt::trad("confirmDeleteDbl") ?>";
		labelDateBeginEndControl	="<?= Txt::trad("beginEndError") ?>";
		labelUploadMaxFilesize		="<?= File::uploadMaxFilesize("error") ?>";
		valueUploadMaxFilesize		=<?= File::uploadMaxFilesize() ?>;

		////	Au chargement de la page
		$(function(){
			<?php
			////	Affiche si besoin des notifications
			foreach(Ctrl::$notify as $tmpNotif){
				if(Txt::isTrad($tmpNotif["message"]))	{$tmpNotif["message"]=Txt::trad($tmpNotif["message"]);}
				else									{$tmpNotif["message"]=str_replace(["\"","'"],["&quot;","&apos;"],strip_tags($tmpNotif["message"],"<br>"));}//Evite les XSS : tester avec  &notify[]=<i>test<%2fi>")%3balert(1)%2f%2f
				echo 'notify("'.$tmpNotif["message"].'", "'.$tmpNotif["type"].'");';
			}
			?>
			////	Mobile : Affiche le bouton en bas de page pour ajouter un nouvel element
			if(isMobile()){
				var addElemButton=$("#pageModMenu img[src*='plus.png']").first().parents(".menuLine");//Sélectionne le div ".menuLine" du premier bouton "Ajouter"
				if(addElemButton.exist())  {$("#menuMobileAddButton").attr("onclick",addElemButton.attr("onclick")).show();}//Ajoute l'attribut "onclick", puis affiche ce bouton
			}
		});
		</script>

		<?php
		////	FOOTER JAVASCRIPT DU HOST ?
		if(Req::isHost())  {Host::footerJsNotify();}
		?>

		<style>
		/*WALLPAPER EN PAGE PRINCIPALE*/
		@media screen and (min-width:1024px){
			<?= isset($pathWallpaper) ? "html  {background:url('".addslashes($pathWallpaper)."') no-repeat center fixed;background-size:cover;}" : null ?>/*"cover": background fullsize*/
		}

		/*FOOTER*/
		#pageFooterHtml, #pageFooterIcon			{position:fixed; bottom:0px; z-index:20; display:inline-block; font-weight:normal;}/*pas de margin*/
		#pageFooterHtml								{padding:15px; color:#eee; text-shadow:0px 0px 9px #000;}/*"Left:80px" pour pouvoir afficher l'icone du messengerStandby*/
		#pageFooterIcon								{right:2px; bottom:3px;}
		#pageFooterIcon img							{max-height:65px; max-width:200px;}
		#pageFooterSpecial							{display:inline-block; margin:0px 0px -7px -7px; background-color:rgba(0,0,0,0.7); border-radius:5px; padding:8px; color:#c00; font-weight:bold;}/*host*/
		#menuMobileAddButton, #menuMobileBg, #menuMobileMain	{display:none;}/*Masquer par défaut les principaux elements sur mobile*/

		/*MOBILE  (cf. "common.js")*/
		@media screen and (max-width:1023px){
			#pageFooterHtml, #pageFooterIcon		{visibility:hidden;}/*pas de "display:none" pour laisser de la marge avec le contenu de la page pour le Messenger/Livecounter et le "menuMobileAddButton"*/
			#menuMobileMain, #menuMobileBg				{position:fixed; top:0px; right:0px; height:100%;}
			#menuMobileBg								{z-index:100; width:100%; background-color:rgba(0,0,0,0.7);}/*z-index à 100 : idem ".menuContext"*/
			#menuMobileMain							{z-index:101; max-width:360px!important; overflow:auto; padding:10px; padding-top:30px; font-size:1.05em!important; <?= @Ctrl::$agora->skin=='black'?'background:#333;border:solid 1px #444;':'background:#fff;border:solid 1px #ddd;' ?>}
			#menuMobileMain #menuMobileClose			{position:absolute; top:7px; right:7px;}/*tester avec la mobileApp*/
			#menuMobileMain .menuLine					{padding:3px;}/*uniformise la présentation (cf. menu espace ou users)*/
			#menuMobileMain .menuLine>div:first-child	{padding-right:10px;}/*idem*/
			#menuMobileMain hr						{margin:10px 0px;}/*surcharge*/
			#menuMobileTwo							{display:none; margin-top:10px; border-radius:5px;}
			#menuMobileTwo, .vHeaderModuleCurrent		{<?= @Ctrl::$agora->skin=="black" ? "background:#444!important;border:solid 1px #555;" : "background:#eee!important;border:solid 1px #ddd;" ?>}
			#menuMobileAddButton							{z-index:20; position:fixed; bottom:5px; right:5px; filter:drop-shadow(0px 2px 4px #ccc);}/*Bouton d'ajout d'elem. "z-index" identique aux menus contextuels*/
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

		////	MENU MOBILE (cf. common.js : peut fusionner 2 menus. Ex: liste des modules & menu context du module)  &&  ICONE "PLUS" POUR AJOUTER UN ELEMENT
		if(Req::isMobile()){
			echo "<div id='menuMobileBg'></div>
				  <div id='menuMobileMain'>
					<div id='menuMobileClose'><img src='app/img/closeMobile.png'></div>
					<div id='menuMobileContent'> <div id='menuMobileOne'></div> <div id='menuMobileTwo'></div> </div>
				  </div>
				  <div id='menuMobileAddButton'><img src='app/img/plusBig.png'></div>";
		}

		////	PAGE PRINCIPALE : TEXTE PERSONNALISÉ DU FOOTER (OU SCRIPT)  &&  ICONE DE L'ESPACE
		if(Ctrl::$isMainPage==true && is_object(Ctrl::$agora)){
			//Mise à jour récente : notification "footerHtml" spécifique pour l'admin
			if(Ctrl::$curUser->isSpaceAdmin() && Ctrl::$curUser->previousConnection<strtotime(Ctrl::$agora->dateUpdateDb))
				{Ctrl::$agora->footerHtml="<span id='footerHtmlUpdate' onclick=\"lightboxOpen('docs/CHANGELOG.txt')\" style='cursor:pointer'>Updated to version ".Req::appVersion()."</span><script>$('#footerHtmlUpdate').pulsate();</script>";}
			//Affiche le footer
			$pageFooterIconTooltip="".OMNISPACE_URL_LABEL." - ".Txt::trad("FOOTER_pageGenerated")." ".round((microtime(true)-TPS_EXEC_BEGIN),3)." secondes";
			echo "<div id='pageFooterHtml'>".Ctrl::$agora->footerHtml."</div>
				  <div id='pageFooterIcon'><a href=\"".$pathLogoUrl."\" target='_blank' title=\"".Txt::tooltip($pageFooterIconTooltip)."\"><img src=\"".Ctrl::$agora->pathLogoFooter()."\"></a></div>";
		}
		?>
	</body>
</html>