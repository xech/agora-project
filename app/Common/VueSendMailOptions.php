<script>
$(function(){
	////	Affiche un nouveau champ "specificMails"
	$("#specificMails").on("click",function(){
		$(".specificMailsDiv:hidden:first").fadeIn().find("input").focusAlt();
	});
	////	Controle un champs "specificMails"
	$("input[name='specificMails[]']").on("focusout",function(){
		//Notif "email invalide"  +  Class .focusPulsate (pas focusPulsate() sinon on focus en boucle)
		if($(this).isNotEmpty() && $(this).isMail()==false)  {notify("<?= Txt::trad("mailInvalid") ?> : "+this.value);  $(this).addClass("focusPulsate");}
	});
});
</script>

<style>
input[name*='mailOptions'], img[src*='arobase']	{margin-right:10px;}
.specificMailsDiv								{display:none;}
.specificMailsDiv input							{width:350px; max-width:90%;}
</style>


<!--Options "Masquer les destinataires"  &&  "Ne pas signer le message"-->
<div title="<?= Txt::trad("MAIL_hideRecipientsTooltip") ?>"><img src="app/img/dependency.png"><input type="checkbox" name="mailOptions[]" value="hideRecipients" id="hideRecipients"><label for="hideRecipients"><?= Txt::trad("MAIL_hideRecipients") ?></label></div>
<div title="<?= Txt::trad("MAIL_noFooterTooltip") ?>"><img src="app/img/dependency.png"><input type="checkbox" name="mailOptions[]" value="noFooter" id="noFooter"><label for="noFooter"><?= Txt::trad("MAIL_noFooter") ?></label></div>

<!--Options "Mettre mon email en réponse"  &&  "Accusé de reception"-->
<?php if(!empty(Ctrl::$curUser->mail)){ ?>
<div title="<?= Txt::trad("MAIL_addReplyToTooltip") ?>"><img src="app/img/dependency.png"><input type="checkbox" name="mailOptions[]" value="addReplyTo" id="addReplyTo"><label for="addReplyTo"><?= Txt::trad("MAIL_addReplyTo") ?></label></div>
<div title="<?= Txt::trad("MAIL_receptionNotifTooltip") ?>"><img src="app/img/dependency.png"><input type="checkbox" name="mailOptions[]" value="receptionNotif" id="receptionNotif"><label for="receptionNotif"><?= Txt::trad("MAIL_receptionNotif") ?></label></div>
<?php } ?>

<!--Option "Ajouter des adresses email"-->
<div id="specificMails" class="sLink" title="<?= Txt::trad("MAIL_specificMailsTooltip") ?>"><img src="app/img/dependency.png"><img src="app/img/arobase.png"><?= Txt::trad("MAIL_specificMails") ?></div>
<?php for($cptMail=1; $cptMail<=20; $cptMail++){ ?><div class="specificMailsDiv"><input type="text" name="specificMails[]"></div><?php } ?>