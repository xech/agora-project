<style>
#pageCenter						{text-align:center;}
#pageCenter	button				{width:350px; padding:25px; font-size:1.2rem; line-height:20px; border-radius:10px; margin-top:80px;}
#pageCenter	button img			{margin-right:10px;}
#pageCenter	#fileName			{margin-top:15px; font-size:0.9rem; font-style:italic; word-wrap:break-word;}
</style>


<div id="pageCenter">
	<button onclick="redir('<?= $urlDownload ?>')">
		<img src="app/img/download.png"><?= Txt::trad("FILE_fileDownload") ?>
		<div id="fileName"><?= Req::param("fileName") ?></div>
	</button>

	<?php if(!empty($appUrl)){ ?>
	<!--Retour à l'appli-->
	<a href="<?= $appUrl ?>">
		<button><img src="app/img/logoSmall.png"><?= Txt::trad("downloadBackToApp") ?></button>
	</a>
	<?php } ?>
</div>