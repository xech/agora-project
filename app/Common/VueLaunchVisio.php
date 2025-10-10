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
#visioMenu						{text-align:center;}
#visioMenu>div					{margin-block:40px 20px;}
#visioMenu button				{width:350px; padding:25px; font-size:1.2rem; line-height:20px; border-radius:10px;}
#visioMenu img[src*=visioSmall]	{float:left; margin-inline:5px;}
</style>


<div id="visioMenu">
	<!--LANCE LA VISIO-->
	<div>
		<button data-visioURL="<?= $visioURL ?>"><img src="app/img/visioSmall.png"><?= Txt::trad("VISIO_launch") ?></button>
	</div>
	<!--SERVEURS DE VISIO-->
	<div <?= empty(Ctrl::$agora->visioHostAlt) ? 'style="display:none"' : null ?> >
		<select id="visioHostServer" <?= Txt::tooltip("VISIO_launchServerTooltip") ?>>
			<option value="main" selected><?= Txt::trad("VISIO_launchServerMain") ?> : <?= str_replace('https://','',Ctrl::$agora->visioHost) ?></option>
			<option value="alt"><?= Txt::trad("VISIO_launchServerAlt") ?> : <?= str_replace('https://','',Ctrl::$agora->visioHostAlt) ?></option>
		</select>
	</div>
	<!--GUIDE PDF-->
	<div onclick="lightboxOpen('docs/VISIO.pdf')">
		<?= Txt::trad("VISIO_launchGuide") ?> <img src="app/img/pdf.png">
	</div>
</div>