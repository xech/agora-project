<script>
////	Option pour renvoyer un ancien email : reload la page principale
function sendOldMail(typeId){
	window.top.confirmRedir("?ctrl=mail&reloadMailTypeId="+typeId, "<?= Txt::trad("MAIL_resendInfo") ?>");
}
</script>

<style>
#bodyLightbox			{max-width:900px;}
li						{margin-top:20px;}
.vMailDetails			{display:none;}
.vMailDetails>div		{margin-top:15px;}
.vMailDetails label		{margin-right:20px;}
</style>


<div>
	<div class="lightboxTitle"><?= Txt::trad("MAIL_historyTitle") ?></div>

	<ul>
	<!--ANCIENS MAILS-->
	<?php foreach($mailList as $tmpMail){ ?>
	<li>
		<label onclick="$('#mailDetails<?= $tmpMail->_id ?>').slideToggle()"><?= $tmpMail->title ?> <img src='app/img/arrowBottom.png'></label>
		<div id="mailDetails<?= $tmpMail->_id ?>" class="vMailDetails">
			<div><?= Txt::trad("MAIL_sendBy").' '.$tmpMail->autorDate() ?></div>
			<div><?= Txt::trad("MAIL_recipients").' : '.str_replace(',',' - ',$tmpMail->recipients) ?></div>
			<div>
				<label onclick="sendOldMail('<?= $tmpMail->_typeId ?>')" <?= Txt::tooltip("MAIL_resendInfo") ?>><img src="app/img/mail/resend.png"> <?= Txt::trad("MAIL_resend") ?></label>
				<label onclick="confirmDelete('<?= $tmpMail->getUrl('delete') ?>')"><img src="app/img/delete.png"> <?= Txt::trad("MAIL_delete") ?></label>
			</div>
			<div class="miscContainer"><?= $tmpMail->description ?></div>
		</div>
	</li>
	<?php } ?>
	</ul>

	<!--AUCUN MAIL-->
	<?php if(empty($mailList)) {echo "<i>".Txt::trad("MAIL_historyEmpty")."</i>";} ?>
</div>