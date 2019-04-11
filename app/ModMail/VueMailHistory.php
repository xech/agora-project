<script>
lightboxSetWidth(650);//Resize
</script>

<style>
li					{margin-bottom:10px;}
[id^='mailDetails']	{display:none;}
.vMailDescription	{margin-top:10px; padding:5px; border:dotted #999 1px;}
.vRecipients		{font-weight:normal;}
</style>


<div class="lightboxContent">
	<div class="lightboxTitle"><?= Txt::trad("MAIL_mailHistory") ?></div>

	<ul>
	<?php
	//LISTE DES MAILS
	foreach($mailList as $tmpMail){
		$deleteUrl="?ctrl=".Req::$curCtrl."&action=".Req::$curAction."&actionDelete=true&_idMail=".$tmpMail["_id"];
	?>
		<li>
			<label onclick="$('#mailDetails<?= $tmpMail["_id"] ?>').slideToggle()"><?= $tmpMail["title"] ?></label>
			<img src="app/img/delete.png" onclick="confirmDelete('<?= $deleteUrl ?>');" class="sLink">
			<div id="mailDetails<?= $tmpMail["_id"] ?>">
				<?= Txt::trad("MAIL_sendBy")." ".Ctrl::getObj("user",$tmpMail["_idUser"])->getLabel()." - ".Txt::displayDate($tmpMail["dateCrea"]) ?><br>
				<?= Txt::trad("MAIL_recipients") ?> :
				<span class="vRecipients"><?= str_replace(",", ", ", $tmpMail["recipients"]) ?></span>
				<div class="vMailDescription"><?= strip_tags($tmpMail["description"],"<p><div>") ?></div>
			</div>
		</li>
	<?php } ?>
	</ul>

	<!--AUCUN MAIL-->
	<?php if(empty($mailList)) {echo "<i>".Txt::trad("MAIL_mailHistoryEmpty")."</i>";} ?>
</div>