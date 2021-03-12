<script>
////	RESIZE
lightboxSetWidth(750);

////	OPTION POUR RENVOYER UN EMAIL
function resendMail(_id)
{
	if(confirm("<?= Txt::trad("MAIL_resendInfo") ?> ?")){
		parent.tinymce.activeEditor.insertContent($("#mailDescription"+_id).html());	//Place le contenu de l'ancien mail dans l'éditeur tinyMce 
		parent.confirmCloseForm=false;													//Pas de confirmation de fermeture de fancybox
		parent.$.fancybox.close();														//On ferme le fancybox
	}
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


<div class="lightboxContent">
	<div class="lightboxTitle"><?= Txt::trad("MAIL_historyTitle") ?></div>

	<ul>
	<?php
	//LISTE DES MAILS
	foreach($mailList as $mailTmp)
	{
		//Date et destinataires du mail
		$autorRecipents="<div class='vMailDetail'>".Txt::trad("MAIL_sendBy")." ".Ctrl::getObj("user",$mailTmp["_idUser"])->getLabel()." : ".Txt::dateLabel($mailTmp["dateCrea"])."</div>".
						"<div class='vMailDetail'>".Txt::trad("MAIL_recipients")." : ".str_replace(',',' - ',$mailTmp["recipients"])."</div>";
		//Récupération de l'email || Suppression de l'email 
		$buttonResend="<div class='vMailDetail vMailDetailOption sLink' onclick=\"resendMail(".$mailTmp["_id"].");\" title=\"".Txt::trad("MAIL_resendInfo")."\"><img src='app/img/mail/resend.png'> ".Txt::trad("MAIL_resend")."</div>";
		$buttonDelete="<div class='vMailDetail vMailDetailOption sLink' onclick=\"confirmDelete('?ctrl=".Req::$curCtrl."&action=".Req::$curAction."&actionDelete=true&_idMail=".$mailTmp["_id"]."');\"><img src='app/img/delete.png'> ".Txt::trad("MAIL_delete")."</div>";
		//Affiche chaque email envoyé
		echo "<li>
				<label onclick=\"$('#mailDetailBlock".$mailTmp["_id"]."').slideToggle()\">".$mailTmp["title"]." <img src='app/img/arrowBottom.png'></label>
				<div id=\"mailDetailBlock".$mailTmp["_id"]."\" class='vMailDetailBlock'>
					".$autorRecipents.$buttonResend.$buttonDelete."
					<div id=\"mailDescription".$mailTmp["_id"]."\" class='vMailDescription'>".$mailTmp["description"]."</div>
				</div>
			  </li>";
	}
	?>
	</ul>

	<!--AUCUN MAIL-->
	<?php if(empty($mailList)) {echo "<i>".Txt::trad("MAIL_historyEmpty")."</i>";} ?>
</div>