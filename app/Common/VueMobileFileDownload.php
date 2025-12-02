<style>
#pageCenter					{text-align:center;}
#pageCenter	button			{width:350px; padding:20px; font-size:1.2rem; line-height:30px; margin-top:80px;}
#pageCenter	button img		{margin-right:10px; max-height:35px;}
#pageCenter	button span		{font-style:italic;}
</style>


<div id="pageCenter">
	<button onclick="redir('<?= $urlDownload ?>')">
		<img src="app/img/download.png"><?= Txt::trad("FILE_fileDownload") ?> : <span><?= Req::param("fileName") ?></span>
	</button>
	<br><br>
	<?php if(!empty($appUrl)){ ?>
	<!--Retour à l'appli-->
	<a href="<?= $appUrl ?>">
		<button><img src="app/img/logoSmall.png"><?= Txt::trad("downloadBackToApp") ?></button>
	</a>
	<?php } ?>
</div>