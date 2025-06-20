<script>
ready(function(){
	////	Affiche un nouveau champ "specificMails"
	$("#specificMails").on("click",function(){
		$(".specificMailsDiv:hidden:first").fadeIn().find("input").focusAlt();
	});
	////	Controle un champs "specificMails"
	$("input[name='specificMails[]']").on("focusout",function(){
		//Notif "email invalide"  +  Class .focusPulsate (pas focusPulsate() sinon on focus en boucle)
		if($(this).notEmpty() && $(this).isMail()==false)  {notify("<?= Txt::trad("mailInvalid") ?> : "+this.value);  $(this).addClass("focusPulsate");}
	});
});
</script>

<style>
input[name*='mailOptions'], #specificMailsPlus	{margin-right:10px;}
#specificMailsPlus								{height:18px;}
.specificMailsDiv								{display:none;}
.specificMailsDiv input							{width:300px; max-width:90%;}
</style>


<!--Options "Masquer les destinataires"  &&  "Ne pas signer le message"-->
<div <?= Txt::tooltip("MAIL_hideRecipientsTooltip") ?> ><img src="app/img/dependency.png"><input type="checkbox" name="mailOptions[]" value="hideRecipients" id="hideRecipients"><label for="hideRecipients"><?= Txt::trad("MAIL_hideRecipients") ?></label></div>
<div <?= Txt::tooltip("MAIL_noFooterTooltip") ?> ><img src="app/img/dependency.png"><input type="checkbox" name="mailOptions[]" value="noFooter" id="noFooter"><label for="noFooter"><?= Txt::trad("MAIL_noFooter") ?></label></div>

<!--Options "Mettre mon email en réponse"  &&  "Accusé de reception"-->
<?php if(!empty(Ctrl::$curUser->mail)){ ?>
<div <?= Txt::tooltip("MAIL_addReplyToTooltip") ?> ><img src="app/img/dependency.png"><input type="checkbox" name="mailOptions[]" value="addReplyTo" id="addReplyTo"><label for="addReplyTo"><?= Txt::trad("MAIL_addReplyTo") ?></label></div>
<div <?= Txt::tooltip("MAIL_receptionNotifTooltip") ?> ><img src="app/img/dependency.png"><input type="checkbox" name="mailOptions[]" value="receptionNotif" id="receptionNotif"><label for="receptionNotif"><?= Txt::trad("MAIL_receptionNotif") ?></label></div>
<?php } ?>

<!--Option "Ajouter des adresses email"-->
<div id="specificMails" class="sLink" <?= Txt::tooltip("MAIL_specificMailsTooltip") ?> ><img src="app/img/dependency.png"><img src="app/img/plusSmall.png" id="specificMailsPlus"><?= Txt::trad("MAIL_specificMails") ?></div>
<?php for($cptMail=1; $cptMail<=20; $cptMail++){ ?><div class="specificMailsDiv"><input type="text" name="specificMails[]"></div><?php } ?>