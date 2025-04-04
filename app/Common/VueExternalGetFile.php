<style>
#pageCenter						{text-align: center;}
#pageCenter	button				{width:350px; margin-top:80px; border-radius:10px; font-size:1.3em;}
#pageCenter	button:first-child	{height:150px;}
#pageCenter	button:last-child	{height:100px;}
#pageCenter	button div			{margin-top:15px; font-size:0.9em; font-style:italic; word-wrap:break-word;}
#pageCenter	button img			{max-height:35px; margin-right:10px;}
</style>


<div id="pageCenter">
	<button onclick="redir('<?= $urlDownload ?>')">
		<img src="app/img/download.png"><?= Txt::trad("FILE_fileDownload") ?>
		<div><?= $_GET["fileName"] ?></div>
	</button>

	<?php if(!empty($appUrl)){ ?>
	<button onclick="redir('<?= $appUrl ?>')">
		<img src="app/img/logoSmall.png"><?= Txt::trad("downloadBackToApp") ?>
	</button>
	<?php } ?>
</div>