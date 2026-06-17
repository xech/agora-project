<!DOCTYPE html>
<html lang="<?= Txt::trad("CURLANG") ?>" id="<?= Ctrl::$isMainPage==true?'htmlMainPage':'htmlLightbox' ?>">
	<head>
		<!-- AGORA-PROJECT :: UNDER THE GENERAL PUBLIC LICENSE V2 :: https://www.gnu.org -->
		<meta charset="UTF-8">
		<meta content="text/html; charset=utf-8" http-equiv="Content-Type">
		<meta http-equiv="content-language" content="<?= Txt::trad("CURLANG") ?>">
		<link rel="icon" type="image/png" href="app/img/favicon.png">
		<meta name="viewport" content="width=device-width,initial-scale=1.0,minimum-scale=1.0,<?= Req::isMobileApp()?'user-scalable=no':'maximum-scale=2.0' ?>">
		<!--REFERENCEMENT/SEO-->
		<title><?= !empty(Ctrl::$agora->name) ? Ctrl::$agora->name : "Omnispace.fr - Agora-Project" ?></title>
		<meta name="application-name" content="Agora-Project">
		<meta name="application-url" content="https://www.agora-project.net">
		<meta name="Description" content="Agora-Project : a workspace to share your files, calendars, tasks and projects with your team.">
		<!--  JQUERY -->
		<script src="app/js/jquery-3.7.1.min.js"></script>
		<script src="app/js/jquery-ui_1.14.2/jquery-ui.min.js"></script>
		<script src="app/js/jquery-ui_1.14.2/datepicker-<?= Txt::trad("CURLANG") ?>.js"></script>
		<link rel="stylesheet" href="app/js/jquery-ui_1.14.2/jquery-ui.css">
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
		////	Parametres et trads pour app.js
		isMobileApp				=<?= Req::isMobileApp()==true ? "true" : "false" ?>;
		fancyboxLang			=Fancybox.l10n.<?= Txt::trad("FANCYBOXLANG") ?>;	
		valueUploadMaxFilesize	=<?= File::uploadMaxFilesize() ?>;
		TRAD_uploadMaxFilesize	="<?= File::uploadMaxFilesize("error") ?>";
		TRAD_confirm			="<?= Txt::trad("confirm") ?>";
		TRAD_confirmCancel		="<?= Txt::trad("confirmCancel") ?>";
		TRAD_confirmCloseForm	="<?= Txt::trad("confirmCloseForm") ?>";
		TRAD_confirmDelete		="<?= Txt::trad("confirmDelete") ?>";
		TRAD_confirmDeleteInfo	="<?= Txt::trad("confirmDeleteInfo") ?>";
		TRAD_beginEndError		="<?= Txt::trad("beginEndError") ?>";
		TRAD_dateFormatError	="<?= Txt::trad("dateFormatError") ?>";
		TRAD_timeFormatError	="<?= Txt::trad("timeFormatError") ?>";

		ready(function(){
			////	Affiche les notify
			<?php foreach(Ctrl::$notify as $tmpNotif){ ?>
				notify("<?= Txt::trad($tmpNotif["message"]) ?>","<?= $tmpNotif["type"] ?>");
			<?php } ?>
			////	Affiche un objet via l'url de partage ("getUrlExternal()") : Focus le block de l'objet ("data-typeid")  +  Affiche l'objet ou le pdf/img (.typeIdTargetClick)  + Exclu du trigger les VueEdit et .menuContext
			<?php if(Req::isParam("typeIdTarget")){ ?>
			setTimeout(function(){
				$("div[data-typeid='<?= Req::param("typeIdTarget") ?>']").trigger("click").find("div[onclick*='action=Vue'], .typeIdTargetClick").not("div[onclick*='action=VueEdit'], .menuContext *").trigger("click");
			}, 300);
			<?php } ?>
			////	Footer & Notify d'un host
			<?php if(Req::isHost()) {Host::footerJsNotify();} ?>
			////	Mobile : Bouton "+" en bas de page pour ajouter un élément
			if(isMobile() && $(".forMobileAddElem").exist()){
				let onclickAttr=$(".forMobileAddElem").attr("onclick");	//Attribut "onclick" du bouton principal d'ajout d'element
				$("#mobileAddElem").show().attr("onclick",onclickAttr);	//Affiche le "+" et ajoute le "onclick"
			}
		});
		</script>

		
		<!--WALLPAPER FULLSIZE-->
		<?php if(isset($pathWallpaper)){ ?>
			<style>  html  {background:url('<?= addslashes($pathWallpaper) ?>') no-repeat center fixed; background-size:cover;}  </style>
		<?php } ?>
	</head>


	<body id="<?= Ctrl::$isMainPage==true?'bodyMainPage':'bodyLightbox' ?>">
	
		<!--CONTENU PRINCIPAL DE LA PAGE-->
		<?php
		if(!empty($headerMenu))		{echo $headerMenu;}
		if(!empty($mainContent))	{echo $mainContent;}
		if(!empty($messenger))		{echo $messenger;}
		?>

		<!--FOOTER EN PAGE PRINCIPALE-->
		<?php if(isset($footerLogoUrl)){ ?>
			<div id="pageFooterHtml"><?= Ctrl::$agora->footerHtml ?></div>
			<div id="pageFooterIcon"><a href="<?= $footerLogoUrl ?>" target="_blank" <?= Txt::tooltip($footerLogoTooltip) ?> ><img src="<?= Ctrl::$agora->pathLogoFooter() ?>"></a></div>
		<?php } ?>

		<!--MOBILE : MENU CONTEXT-->
		<div id="menuMobileBg"></div>
		<div id="menuMobileMain">
			<div id="menuMobileClose"><img src="app/img/close.png"></div>
			<div id="menuMobileHeader"></div>
			<div id="menuMobileContent"></div>
		</div>

		<!--MOBILE : BOUTON "+" EN BAS A DROITE-->
		<div id="mobileAddElem"><img src="app/img/plusBig.png"></div>

	</body>
</html>