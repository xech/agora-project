<style>
#pageCenter					{text-align:center;}
#pageCenter	button			{width:350px; padding:20px; font-size:1.2rem; line-height:30px; margin-top:80px;}
#pageCenter	button img		{margin-right:10px; max-height:35px;}
#pageCenter	button span		{font-style:italic;}
</style>


<div id="pageCenter">
	<!--DOWNLOAD LE FICHIER-->
	<button onclick="redir('<?= $urlDownload ?>')">
		<img src="app/img/download.png"><?= Txt::trad("FILE_fileDownload") ?> : <span><?= Req::param("fileName") ?></span>
	</button>
	<br><br>

	<!--RETOUR À L'APPLI-->
	<?php if(!empty($urlBackToApp)){ ?>
		<a href="<?= $urlBackToApp ?>">
			<button><img src="app/img/logo.png"><?= Txt::trad("downloadBackToApp") ?></button>
		</a>
	<?php } ?>
</div>