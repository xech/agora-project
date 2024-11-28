<style>
	#pageCenter				{text-align: center;}
.miscContainer				{display:inline-block; width:300px;}
.miscContainer:first-child	{margin-top:100px; padding:20px 40px;}
.miscContainer:last-child	{margin-top:50px; padding:10px 40px;}
</style>


<div id="pageCenter">
	<div class="miscContainer" onclick="redir('<?= $urlDownload ?>')">
		<h3><img src="app/img/download.png"> &nbsp; <?= Txt::trad("FILE_fileDownload") ?> </h3>
		<h2><i><?= $_GET["fileName"] ?></i></h2>
	</div>

	<?php if(!empty($appUrl)){ ?>
	<br>
	<div class="miscContainer" onclick="redir('<?= $appUrl ?>')">
		<h3><img src="app/img/logoMobile.png"> &nbsp; <?= Txt::trad("downloadBackToApp") ?> </h3>
	</div>
	<?php } ?>
</div>