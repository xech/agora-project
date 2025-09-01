<script>
////	Lance la visio
ready(function(){
	$(".launchVisio").on("click",function(){
		visioURL=$(this).attr("data-visioURL");																										// Visio via le browser ou l'appli Jitsi
		if($("#visioHostServer").val()=="alt")  {visioURL=visioURL.replace("<?= Ctrl::$agora->visioHost ?>","<?= Ctrl::$agora->visioHostAlt ?>");}	// Url du serveur alternatif
		window.open(visioURL);																														// Lance la visio !
	});
});
</script>

<style>
.divOptions						{margin:40px 0px; text-align:center; font-size:1.1rem;}
.divOptions .launchVisio		{width:320px; height:80px; padding:10px 5px; font-size:1.2rem;}
.divOptions .launchVisio img	{margin:5px;}
.divOptions .launchVisioIcon	{float:left; margin:10px;}
</style>


<div>
	<!--LANCE LA VISIO VIA L'APPLI JITSI-->
	<?php if(Req::isMobileApp()){ ?>
		<div class="divOptions">
			<button class="launchVisio" data-visioURL="<?= $visioURLJitsi ?>"><img src="app/img/visioSmall.png" class="launchVisioIcon"><?= Txt::trad("VISIO_launchJitsi") ?><img src="app/img/jitsi.png"></button>
		</div>
	<?php } ?>
	<!--LANCE LA VISIO-->
	<div class="divOptions">
		<button class="launchVisio" data-visioURL="<?= $visioURL ?>"><img src="app/img/visioSmall.png" class="launchVisioIcon"><?= Txt::trad("VISIO_launch") ?></button>
	</div>
	<!--SERVEURS DE VISIO-->
	<div class="divOptions" <?= empty(Ctrl::$agora->visioHostAlt) ? 'style="display:none"' : null ?> >
		<select id="visioHostServer" <?= Txt::tooltip("VISIO_launchServerTooltip") ?>>
			<option value="main" selected><?= Txt::trad("VISIO_launchServerMain") ?></option>
			<option value="alt"><?= Txt::trad("VISIO_launchServerAlt") ?></option>
		</select>
	</div>
	<!--GUIDE PDF-->
	<div class="divOptions">
		<a href="docs/VISIO.pdf?displayFile=true" target="_blank" <?= Txt::tooltip("VISIO_launchTooltip2") ?>><img src="app/img/pdf.png">&nbsp; <?= Txt::trad("VISIO_launchTooltip") ?></a>
	</div>
</div>