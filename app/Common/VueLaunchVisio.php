<script>
////	Resize
lightboxSetWidth(650);

////	Lance la visio
$(function(){
	$("button#launchVisio").click(function(){
		////	Url de la visio : utilise le serveur alternatif ?
		visioURL="<?= $visioURL ?>";
		if($("#visioHostServer").exist() && $("#visioHostServer").val()=="alt")  {visioURL=visioURL.replace("<?= Ctrl::$agora->visioHost ?>","<?= Ctrl::$agora->visioHostAlt ?>");}
		////	Lance la visio
		window.open(visioURL);
		////Ouverture de l'appli Jitsi Android:  var visioURLObj=new URL(visioURL);  window.open("intent://"+visioURLObj.hostname+"/"+visioURLObj.pathname+"#Intent;scheme=org.jitsi.meet;package=org.jitsi.meet;end");
	});
});
</script>

<style>
.lightboxContent	{padding-top:40px; padding-bottom:30px; text-align:center; font-size:1.05em;}
#launchVisio		{width:300px; height:60px; border-radius:5px; font-size:1.1em;}
#visioInfos			{display:block; margin:30px;}
</style>

<div class="lightboxContent">
	<?php
	////	Bouton de lancement && Infos sur la visio
	echo "<button id='launchVisio'>".Txt::trad("VISIO_launchButton")." &nbsp; <img src='app/img/visioSmall.png'></button>
		  <a href='docs/VISIO.pdf' target='_blank' id='visioInfos' title=\"".Txt::trad("VISIO_launchHelp")."\"><img src='app/img/pdf.png'>&nbsp; ".Txt::trad("VISIO_launchInfo")."</a>";
	////Selection du serveur de visio
	if(!empty(Ctrl::$agora->visioHostAlt)){
		echo "<div title=\"".Txt::trad("VISIO_launchServerInfo")."\">
				<img src='app/img/info.png'> &nbsp;
				<select id='visioHostServer'>
					<option value='main'>".Txt::trad("VISIO_launchServerMain")."</option>
					<option value='alt'>".Txt::trad("VISIO_launchServerAlt")."</option>
				</select>
			  </div>";
	}
	?>
</div>