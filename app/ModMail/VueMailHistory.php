<script>
////	RESIZE
lightboxSetWidth(750);

/*******************************************************************************************
 *	OPTION POUR RENVOYER UN ANCIEN EMAIL : RELOAD LA PAGE PRINCIPALE
*******************************************************************************************/
function sendOldMail(typeId)
{
	if(confirm("<?= Txt::trad("MAIL_resendInfo") ?> ?"))  {parent.redir("?ctrl=mail&oldMailTypeId="+typeId);}
}
</script>

<style>
ul									{padding-left:20px;}
li									{margin-bottom:20px;}
.vMailDetailBlock					{display:none;}
.vMailDetail, .vMailDetailOption	{margin-top:10px;}
.vMailDetailOption					{display:inline-block; margin-right:20px;}
.vMailDetailOption img				{max-height:22px;}
.vMailDescription					{margin-top:10px; padding:5px; border:dotted #999 1px;}
.vRecipients						{font-weight:normal;}
</style>


<div>
	<div class="lightboxTitle"><?= Txt::trad("MAIL_historyTitle") ?></div>

	<ul>
	<?php
	////	AFFICHE CHAQUE MAILS ENVOYE
	foreach($mailList as $tmpMail)
	{
		//Date et destinataires du mail
		$autorRecipents="<div class='vMailDetail'>".Txt::trad("MAIL_sendBy")." ".Ctrl::getObj("user",$tmpMail->_idUser)->getLabel()." : ".$tmpMail->dateLabel()."</div>".
						"<div class='vMailDetail'>".Txt::trad("MAIL_recipients")." : ".str_replace(',',' - ',$tmpMail->recipients)."</div>";
		//Récupération de l'email || Suppression de l'email 
		$buttonResend="<div class='vMailDetail vMailDetailOption' onclick=\"sendOldMail('".$tmpMail->_typeId."')\" ".Txt::tooltip("MAIL_resendInfo")."><img src='app/img/mail/resend.png'> ".Txt::trad("MAIL_resend")."</div>";
		$buttonDelete="<div class='vMailDetail vMailDetailOption' onclick=\"confirmDelete('".$tmpMail->getUrl("delete")."')\"><img src='app/img/delete.png'> ".Txt::trad("MAIL_delete")."</div>";
		//Affiche chaque email envoyé
		echo "<li>
				<label onclick=\"$('#mailDetailBlock".$tmpMail->_id."').slideToggle()\">".$tmpMail->title." <img src='app/img/arrowBottom.png'></label>
				<div id=\"mailDetailBlock".$tmpMail->_id."\" class='vMailDetailBlock'>
					".$buttonResend.$buttonDelete.$autorRecipents."
					<div id=\"mailDescription".$tmpMail->_id."\" class='vMailDescription'>".$tmpMail->description."</div>
				</div>
			  </li>";
	}
	?>
	</ul>

	<!--AUCUN MAIL-->
	<?php if(empty($mailList)) {echo "<i>".Txt::trad("MAIL_historyEmpty")."</i>";} ?>
</div>