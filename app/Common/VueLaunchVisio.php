<script>
////	Lance la visio
ready(function(){
	$("button[data-visioURL]").on("click",function(){
		visioURL=$(this).attr("data-visioURL");																										// Visio via le browser ou l'appli Jitsi
		if($("#visioHostServer").val()=="alt")  {visioURL=visioURL.replace("<?= Ctrl::$agora->visioHost ?>","<?= Ctrl::$agora->visioHostAlt ?>");}	// Url du serveur alternatif
		window.open(visioURL);																														// Lance la visio !
	});
});
</script>

<style>
#visioOptions>div					{margin-block:30px; text-align:center;}
#visioOptions button				{width:350px; padding:15px; font-size:1.2rem; line-height:30px;}
#visioOptions img[src*=visioSmall]	{float:left; margin:5px;}
#visioGuide							{margin-top:50px!important;}
</style>


<div id="visioOptions">
	<!--LANCE LA VISIO-->
	<div>
		<button data-visioURL="<?= $visioURL ?>"><img src="app/img/visioSmall.png"><?= Txt::trad("VISIO_launch") ?></button>
	</div>
	<!--LANCE LA VISIO VIA JITSI-->
	<?php if(Req::isMobileApp()){ ?>
	<div>
		<button data-visioURL="<?= $visioURLJitsi ?>"><img src="app/img/visioSmall.png"><?= Txt::trad("VISIO_launchJitsi") ?> <img src="app/img/jitsi.png"></button>
	</div>
	<?php } ?>
	<!--SERVEURS DE VISIO-->
	<div <?= empty(Ctrl::$agora->visioHostAlt) ? 'style="display:none"' : null ?> >
		<select id="visioHostServer" <?= Txt::tooltip("VISIO_launchServerTooltip") ?>>
			<option value="main" selected><?= Txt::trad("VISIO_launchServerMain") ?> : <?= str_replace('https://','',Ctrl::$agora->visioHost) ?></option>
			<option value="alt"><?= Txt::trad("VISIO_launchServerAlt") ?> : <?= str_replace('https://','',Ctrl::$agora->visioHostAlt) ?></option>
		</select>
	</div>
	<!--GUIDE PDF-->
	<div id="visioGuide">
		<div onclick="lightboxOpen('docs/VISIO.pdf')"><?= Txt::trad("VISIO_launchGuide") ?> <img src="app/img/pdf.png"></div>
	</div>
</div>