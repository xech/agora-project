<script>
////	Resize
lightboxSetWidth(520);

////	SUPPRESSION DU FICHIER D'UNE REPONSE
function deleteResponseFile(_idReponse)
{
	if(confirm("<?= Txt::trad("confirmDelete") ?>")){
		$.ajax({url:"?ctrl=dashboard&action=DeleteResponseFile&targetObjId=<?= $objPoll->_targetObjId ?>&_idResponse="+_idReponse}).done(function(result){
			if(find("true",result))  {$("#responseFile"+_idReponse).html("<input type='file' name='responsesFile"+_idReponse+"'>");}//Remplace le fichier supprimé par un champ "File"
		});
	}
}

////	Contrôle du formulaire
function formControl()
{
	//Au moins 2 réponses au sondage
	var responsesNb=$(".vPollResponseDiv input[name^=responses]").filter(function(){ return $(this).val(); }).length;
	if(responsesNb<2)  {notify("<?= Txt::trad("DASHBOARD_controlResponseNb") ?>");  return false;}
	//Controle final (champs obligatoires, affectations/droits d'accès, etc)
	return mainFormControl();
}
</script>


<style>
input[name=title]			{width:90%; margin-right:8px;}
textarea[name=description]	{margin-top:12px; <?= empty($objPoll->description)?"display:none;":null ?>}
#responseListLabel			{margin-top:20px;}
.vPollResponseDiv			{margin-top:12px;}
.vPollResponseDiv input[type=text]		{width:90%; margin-right:5px;}
.vPollResponseDiv div.responseFile		{padding:10px;}
.vPollResponseDiv div.responseFileHide	{display:none;}
.vPollResponseHidden		{display:none;}
#responseAdd				{margin-top:12px; text-align:right;}
form .infos					{margin:0px; margin-bottom:20px;}
.pollOptions				{margin-top:20px;}
</style>


<form action="index.php" method="post" onsubmit="return formControl()" enctype="multipart/form-data" class="lightboxContent">
	<!--AJOUTE UNE NOTIFICATION "Attention : dès que le sondage est voté la modif des réponses est impossible"-->
	<?php if($pollIsVoted==true){ ?>
	<div class="infos"><img src="app/img/important.png"> <?= Txt::trad("DASHBOARD_votedPollNotif") ?></div>
	<?php } ?>

	<!--TITRE & DESCRIPTION-->
	<input type="text" name="title" value="<?= $objPoll->title ?>"  <?= $pollIsVoted==true?'readonly':null ?>  placeholder="<?= Txt::trad("DASHBOARD_titleQuestion") ?>" >
	<img src="app/img/description.png" class="sLink" onclick="$('textarea[name=description]').slideToggle();" title="<?= Txt::trad("description") ?>">
	<textarea name="description" placeholder="<?= Txt::trad("description") ?>"><?= $objPoll->description ?></textarea>

	<!--LISTE DES REPONSES POSSIBLES (10 maxi)-->
	<div id="responseListLabel"><?= Txt::trad("DASHBOARD_responseList") ?> :</div>
	<?php
	for($tmpKey=0; $tmpKey<=10; $tmpKey++)
	{
		//Init la réponse
		$respTmp		=(isset($pollResponses[$tmpKey]))  ?  $pollResponses[$tmpKey] : null;		//Réponse courante
		$respClass		=(empty($respTmp) && $tmpKey>=3)  ?  "vPollResponseHidden"  :  null;		//Masque les champs vides, à partir du 3ème champ
		$respId			=(!empty($respTmp))  ?  $pollResponses[$tmpKey]["_id"]  :  Txt::uniqId();	//Identifiant unique de la réponse (15 caracteres, pas moins)
		$respValue		=(!empty($respTmp))  ?  $pollResponses[$tmpKey]["label"]  :  null;			//Valeur/libellé de la réponse
		$respReadonly	=($pollIsVoted==true)  ?  "readonly"  :  null;								//Sondage déjà voté : réponse en lecture seul (ne pas mettre "disabled"..)
		if(empty($respTmp["fileName"]))	{$respFileHide="responseFileHide";	$respFileContent="<input type='file' name=\"responsesFile".$respId."\">";}
		else							{$respFileHide=null;				$respFileContent="<div id='respFileName".$respId."'><a href=\"".$respTmp["fileUrlDownload"]."\" title=\"".Txt::trad("download")."\"><img src='app/img/attachment.png'> ".$respTmp["fileName"]."</a> &nbsp; <img src='app/img/delete.png' class='sLink' title=\"".Txt::trad("delete")."\" onclick=\"deleteResponseFile('".$respId."');\">";}
		//Affiche la réponse
		echo "<div class='vPollResponseDiv ".$respClass."'>
				<input type='text' name=\"responses[".$respId."]\" value=\"".$respValue."\" ".$respReadonly." placeholder=\"".Txt::trad("DASHBOARD_responseNb").($tmpKey+1)."\">
				<img src='app/img/attachment.png' class='sLink' onclick=\"$('#responseFile".$respId."').slideToggle();\" title=\"".Txt::trad("EDIT_attachedFile")."\">
				<div id='responseFile".$respId."' class='responseFile ".$respFileHide."'>".$respFileContent."</div>
			  </div>";
	}
	?>

	<!--AJOUTER UNE REPONSE-->
	<?php if($pollIsVoted==false){ ?>
	<div id="responseAdd">
		<span class="sLink" onclick="$('.vPollResponseDiv:hidden:first').fadeIn();$('.vPollResponseDiv input:visible:last').focus();"><img src="app/img/plusSmall.png"> <?= Txt::trad("DASHBOARD_addResponse") ?></span>
	</div>
	<?php } ?>

	<!--AFFICHAGE AVEC LES NEWS & REPONSES MULTIPLES & DATE DE FIN-->
	<div class="pollOptions">
		<input type="checkbox" name="newsDisplay" value="1" id="newsDisplayInput" <?= (!empty($objPoll->newsDisplay) || $objPoll->isNew()) ? "checked" : null ?>>
		<label for="newsDisplayInput"><?= Txt::trad("DASHBOARD_newsDisplay") ?>
	</div>
	<div class="pollOptions">
		<input type="checkbox" name="multipleResponses" value="1" id="multipleResponsesInput" <?= !empty($objPoll->multipleResponses) ? "checked" : null ?>>
		<label for="multipleResponsesInput"><?= Txt::trad("DASHBOARD_multipleResponses") ?>
	</div>
	<div class="pollOptions">
		<img src="app/img/dashboard/pollDateEnd.png">
		<?= Txt::trad("DASHBOARD_dateEnd") ?> :
		<input type="text" name="dateEnd" class="dateEnd" value="<?= Txt::formatDate($objPoll->dateEnd,"dbDate","inputDate") ?>">
	</div>

	<!--MENU COMMUN-->
	<?= $objPoll->menuEdit(); ?>
</form>