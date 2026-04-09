<script>
ready(function(){
	////	AFFICHE LE FORMULAIRE DE SUPPRESION DES ANCIENS MAILS
	$("#deleteMailsLabel").on("click",function(){
		$(this).hide();
		$("#deleteMailsForm").slideDown();
	});

	////	VALIDE LA SUPPRESSION DES ANCIENS MAILS
	$("#deleteMailsForm").on("submit",async function(event){
		event.preventDefault();
		if(await confirmAlt())	{asyncSubmit(this);}
	});
});

////	OPTION POUR RENVOYER UN ANCIEN EMAIL (RELOAD LA PAGE PRINCIPALE)
function sendOldMail(typeId){
	window.top.confirmRedir("?ctrl=mail&reloadMailTypeId="+typeId, "<?= Txt::trad("MAIL_resendInfo") ?>");
}
</script>

<style>
#bodyLightbox		{max-width:800px;}
#deleteMailsLabel	{text-align:right;}
#deleteMailsForm	{display:none; text-align:right; margin-bottom:30px;}
.vMailLabel			{margin-block:5px; padding:8px;}
.vMailDetails		{display:none; margin-top:5px; margin-bottom:20px;}
.vMailDetails div	{padding:5px;}
</style>


<div>
	<div class="lightboxTitle"><img src="app/img/log.png"> <?= Txt::trad("MAIL_historyTitle") ?></div>

	<!--SUPPRESSION DES ANCIENS MAILS-->
	<?php if(!empty($mailList)){ ?>
		<div id="deleteMailsLabel" class="sLink"><?= Txt::trad("MAIL_historyDelete") ?> <img src="app/img/delete.png"></div>
		<form action="index.php" method="post" id="deleteMailsForm">
			<img src="app/img/delete.png"> <?= Txt::trad("MAIL_historyDelete") ?>
			<select name="historyDeleteDays">
				<?php foreach([7,15,30,60,90,180,365,730] as $nbDays){ ?><option value="<?= $nbDays ?>"><?= str_replace("--NB_DAYS--",$nbDays,Txt::trad("MAIL_historyDeleteXDays")) ?></option><?php } ?>
			</select>
			<?= Txt::submitButton("validate",false) ?>
		</form>
	<?php } ?>

	<!--HISTORIQUE DES MAILS ENVOYÉS-->
	<?php foreach($mailList as $tmpMail){ ?>
		<div class="vMailLabel option" onclick="$('#mailDetails<?= $tmpMail->_id ?>').slideToggle()"><img src="app/img/mail.png">&nbsp; <?= $tmpMail->title ?></div>
		<fieldset class="vMailDetails" id="mailDetails<?= $tmpMail->_id ?>">
			<div><?= Txt::trad("MAIL_sendBy").' '.$tmpMail->autorDate(true) ?></div>
			<div><?= Txt::trad("MAIL_recipients").' : '.str_replace(',',' - ',$tmpMail->recipients) ?></div>
			<div class="sLink" onclick="sendOldMail('<?= $tmpMail->typeId ?>')" <?= Txt::tooltip("MAIL_resendInfo") ?>><img src="app/img/mail/resend.png"> <?= Txt::trad("MAIL_resend") ?></div>
			<div class="sLink" onclick="confirmDelete('<?= $tmpMail->getUrl('delete') ?>')"><img src="app/img/delete.png"> <?= Txt::trad("MAIL_delete") ?></div>
			<div class="miscContent"><?= $tmpMail->description ?></div>
		</fieldset>
	<?php } ?>

	<!--AUCUN MAIL-->
	<?php if(empty($mailList)){ ?>
		<fieldset><?= Txt::trad("MAIL_historyEmpty") ?></fieldset>
	<?php } ?>
</div>