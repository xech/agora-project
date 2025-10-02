<!DOCTYPE html>
<html lang="<?= Txt::trad("CURLANG") ?>" id="<?= Ctrl::$isMainPage==true?'htmlMainPage':'htmlLightbox' ?>">
	<head>
		<!-- AGORA-PROJECT :: UNDER THE GENERAL PUBLIC LICENSE V2 :: https://www.gnu.org -->
		<meta charset="UTF-8">
		<meta content="text/html; charset=utf-8" http-equiv="Content-Type">
		<meta http-equiv="content-language" content="<?= Txt::trad("CURLANG") ?>">
		<meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no"><!--Pas de zoom sur mobile-->
		<link rel="icon" type="image/png" href="app/img/favicon.png">
		<!--REFERENCEMENT-->
		<title><?= !empty(Ctrl::$agora->name) ? Ctrl::$agora->name : "Omnispace.fr - Agora-Project" ?></title>
		<meta name="Description" content="<?= !empty(Ctrl::$agora->description) ? Ctrl::$agora->description : "Omnispace.fr - Agora-Project" ?>">
		<meta name="application-name" content="Agora-Project">
		<meta name="application-url" content="https://www.agora-project.net">
		<!--  JQUERY -->
		<script src="app/js/jquery-3.7.1.min.js"></script>
		<script src="app/js/jquery-ui_1.14.0/jquery-ui.min.js"></script>
		<script src="app/js/jquery-ui_1.14.0/datepicker-<?= Txt::trad("CURLANG") ?>.js"></script><!--traduction-->
		<link rel="stylesheet" href="app/js/jquery-ui_1.14.0/jquery-ui.css">
		<!-- LIBRAIRIES JS -->
		<script src="app/js/fancybox_5.0.36/fancybox.umd.js"></script>
		<script src="app/js/fancybox_5.0.36/l10n/<?= Txt::trad("FANCYBOXLANG") ?>.umd.js"></script>
		<link rel="stylesheet" href="app/js/fancybox_5.0.36/fancybox.css" />
		<script type="text/javascript" src="app/js/tooltipster/tooltipster.bundle.min.js"></script>
		<link rel="stylesheet" type="text/css" href="app/js/tooltipster/tooltipster.bundle.css">
		<link rel="stylesheet" type="text/css" href="app/js/tooltipster/tooltipster-sideTip-shadow.min.css">
		<script type="text/javascript" src="app/js/toastmessage-notify/jquery.toastmessage.js"></script>
		<link rel="stylesheet" type="text/css" href="app/js/toastmessage-notify/toastmessage.css">
		<script src="app/js/jquery-confirm/jquery-confirm.min.js"></script>
		<link rel="stylesheet" href="app/js/jquery-confirm/jquery-confirm.min.css">
		<script src="app/js/timepicker_1.14.1/jquery.timepicker.min.js"></script>
		<link rel="stylesheet" type="text/css" href="app/js/timepicker_1.14.1/jquery.timepicker.css">
		<!-- JAVASCRIPT & CSS PRINCIPAUX (TJS À LA FIN)-->
		<script src="app/Common/js-css-<?= Req::appVersion() ?>/app.js"></script>
		<link  href="app/Common/js-css-<?= Req::appVersion() ?>/app.css" rel="stylesheet" type="text/css">
		<link  href="app/Common/js-css-<?= Req::appVersion() ?>/<?= (is_object(Ctrl::$agora) && Ctrl::$agora->skin=="black")?"black.css":"white.css" ?>" rel="stylesheet" type="text/css">

		<script>
		////	Parametres et labels principaux (cf. app.js)
		isMobileApp				=<?= Req::isMobileApp()==true ? "true" : "false" ?>;
		fancyboxLang			=Fancybox.l10n.<?= Txt::trad("FANCYBOXLANG") ?>;	
		valueUploadMaxFilesize	=<?= File::uploadMaxFilesize() ?>;
		labelUploadMaxFilesize	="<?= File::uploadMaxFilesize("error") ?>";
		labeCopyUrlNotif		="<?= Txt::trad("copyUrlNotif") ?>";
		labelConfirm			="<?= Txt::trad("confirm") ?>";
		labelConfirmOk			="<?= Txt::trad("confirmOk") ?>";
		labelConfirmCancel		="<?= Txt::trad("confirmCancel") ?>";
		labelConfirmDownload	="<?= Txt::trad("confirmDownload") ?>";
		labelConfirmCloseForm	="<?= Txt::trad("confirmCloseForm") ?>";
		labelConfirmDelete		="<?= Txt::trad("confirmDelete") ?>";
		labelConfirmDeleteAlert	="<?= Txt::trad("confirmDeleteAlert") ?>";
		labelBeginEndError		="<?= Txt::trad("beginEndError") ?>";
		labelDateFormatError	="<?= Txt::trad("dateFormatError") ?>";
		labelTimeFormatError	="<?= Txt::trad("timeFormatError") ?>";

		////	Au chargement de la page
		ready(function(){
			//// Mobile : Bouton "Add" du footer ("plusBig.png")
			if(isMobile()){
				var addElemButton=$("#moduleMenu img[src*='plus']").first().parents(".menuLine");								//Sélectionne le premier bouton "Add"
				if(addElemButton.exist())  {$("#menuMobileAddButton").attr("onclick",addElemButton.attr("onclick")).show();}	//Ajoute l'attribut "onclick" et affiche le bouton
			}
			//// Affiche des notifs
			<?php foreach(Ctrl::$notify as $tmpNotif){ ?>
				notify("<?= Txt::trad($tmpNotif["message"]) ?>","<?= $tmpNotif["type"] ?>");
			<?php } ?>
			//// Affiche la vue d'un objet depuis une Url de partage (cf. "getUrlExternal()" et "typeIdTarget")
			<?php if(Req::isParam("typeIdTarget")){ ?>
			setTimeout(function(){
				//Focus le block de l'objet via son "data-typeId" ("objContainerMenu()")  +  Affiche la vue de l'objet (fichier pdf/img via .typeIdTarget)  +  Exclu. les VueEdit et .menuContext
				$("div[data-typeId='<?= Req::param("typeIdTarget") ?>']").trigger("click").find("div[onclick*='action=Vue'], .typeIdTarget").not("div[onclick*='action=VueEdit'], .menuContext *").trigger("click");
			}, 500);
			<?php } ?>
			//// Footer & Notify du host
			<?php if(Req::isHost()) {Host::footerJsNotify();} ?>
		});
		</script>

		<style>
		/*WALLPAPER EN PAGE PRINCIPALE ("background-size:cover" = fullsize)*/
		<?= (Req::isMobile()==false && isset($pathWallpaper)) ? "html  {background:url('".addslashes($pathWallpaper)."') no-repeat center fixed;background-size:cover;}" : null ?>

		/*Init*/
		#pageFooterHtml, #pageFooterIcon	{position:fixed; z-index:100;}/*z-index idem #headerBar*/
		#pageFooterHtml						{bottom:15px; left:15px; font-weight:normal; color:#eee; text-shadow:0px 0px 9px #000;}/*"Left:80px" pour pouvoir afficher l'icone du messengerStandby*/
		#pageFooterIcon						{bottom:5px; right:5px;}
		#pageFooterIcon img					{max-height:70px; max-width:200px;}
		/*RESPONSIVE MEDIUM*/
		@media screen and (max-width:1024px){
			#pageFooterHtml, #pageFooterIcon	{display:none;}
		}
		</style>
	</head>

	<body id="<?= Ctrl::$isMainPage==true?'bodyMainPage':'bodyLightbox' ?>">
	
		<!--CONTENU PRINCIPAL DE LA PAGE-->
		<?php
		if(!empty($headerMenu))		{echo $headerMenu;}
		if(!empty($mainContent))	{echo $mainContent;}
		if(!empty($messenger))		{echo $messenger;}
		?>

		<!--FOOTER EN PAGE PRINCIPALE-->
		<?php if(Ctrl::$isMainPage==true && is_object(Ctrl::$agora)){ ?>
			<div id="pageFooterHtml"><?= Ctrl::$agora->footerHtml ?></div>
			<div id="pageFooterIcon"><a href="<?= $footerLogoUrl ?>" target="_blank" <?= Txt::tooltip($footerLogoTooltip) ?> ><img src="<?= Ctrl::$agora->pathLogoFooter() ?>"></a></div>
		<?php } ?>

		<!--MENU CONTEXT RESPONSIVE (cf. app.js / app.css)-->
		<div id="menuMobileBg"></div>
		<div id="menuMobileMain">
			<div id="menuMobileClose"><img src="app/img/close.png"></div>
			<div id="menuMobileContent1"></div>
			<div id="menuMobileContent2"></div>
		</div>

		<!--BOUTON "AJOUTER" SUR MOBILE-->
		<div id="menuMobileAddButton"><img src="app/img/plusBig.png"></div>

	</body>
</html>