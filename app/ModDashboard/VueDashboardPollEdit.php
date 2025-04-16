<script>
////	RESIZE
lightboxWidth(700);

////	INIT : DESACTIVE CERTAINS CHAMPS SI LE SONDAGE EST DEJA VOTÉ
<?php if($pollIsVoted==true){ ?>
ready(function(){
	$("input[name='title'],.vPollResponseDiv input,#multipleResponsesInput,#publicVoteInput").prop("disabled",true);
});
<?php } ?>

////	SUPPRESSION DU FICHIER D'UNE REPONSE
async function deleteResponseFile(_idReponse)
{
	if(await confirmAlt("<?= Txt::trad("confirmDelete") ?>")){
		$.ajax({url:"?ctrl=dashboard&action=DeleteResponseFile&typeId=<?= $curObj->_typeId ?>&_idResponse="+_idReponse}).done(function(result){
			if(/true/i.test(result))  {$("#responseFile"+_idReponse).html("<input type='file' name='responsesFile"+_idReponse+"'>");}//Remplace le fichier supprimé par un champ "File"
		});
	}
}

////	Controle spécifique du formulaire (cf. "VueObjMenuEdit.php")
function objectFormControl(){
	return new Promise((resolve)=>{
		let responsesNb=$(".vPollResponseDiv input[name^='responses']").filter(function(){ return this.value; }).length;	//Nombre de "Réponses" spécifiées
		if(responsesNb < 2)		{notify("<?= Txt::trad("DASHBOARD_controlResponseNb") ?>");	 resolve(false);}				//Au moins 2 réponses au sondage
		else					{$("input:disabled").prop("disabled",false);  				 resolve(true);}				//Réactive les champs désactivés
	});
}
</script>


<style>
[name='description']					{width:100%; height:70px; <?= empty($curObj->description)?"display:none;":null ?>}
#responseListLabel						{margin-top:30px;}
.vPollResponseDiv						{margin-top:12px;}
.vPollResponseDiv input[type=text]		{width:90%; margin-right:5px;}
.vPollResponseDiv div.responseFile		{padding:10px;}
.vPollResponseDiv div.responseFileHide	{display:none;}
.vPollResponseHidden					{display:none;}
#responseAdd							{margin-top:15px;}
form .infos								{margin:0px; margin-bottom:20px;}
.pollOptions							{margin-top:15px;}
</style>


<form action="index.php" method="post" id="mainForm" enctype="multipart/form-data">
	<!--TITRE MOBILE-->
	<?= $curObj->titleMobile("DASHBOARD_addPoll") ?>
	
	<!--SONDAGE DEJA VOTÉ : AFFICHE UNE NOTIFICATION "Attention : dès que le sondage est voté la modif des réponses est impossible"-->
	<?php if($pollIsVoted==true){ ?><div class="infos"><img src="app/img/important.png"> <?= Txt::trad("DASHBOARD_votedPollNotif") ?></div><?php } ?>

	<!--TITRE / DESCRIPTION-->
	<input type="text" name="title" value="<?= $curObj->title ?>" class="inputTitleName" placeholder="<?= Txt::trad("DASHBOARD_titleQuestion") ?>">
	<?= $curObj->descriptionEditor() ?>

	<!--LISTE DES REPONSES POSSIBLES (10 maxi)-->
	<div id="responseListLabel"><?= Txt::trad("DASHBOARD_responseList") ?> :</div>
	<?php
	for($tmpKey=0; $tmpKey<=10; $tmpKey++)
	{
		//Init la réponse
		$respTmp		=(isset($pollResponses[$tmpKey]))  ?  $pollResponses[$tmpKey] : null;	//Réponse courante
		$respClass		=(empty($respTmp) && $tmpKey>=3)  ?  "vPollResponseHidden"  :  null;	//Masque les champs vides, à partir du 3ème champ
		$respId			=(!empty($respTmp))  ?  $pollResponses[$tmpKey]["_id"]  :  uniqid();	//Identifiant unique de la réponse
		$respValue		=(!empty($respTmp))  ?  $pollResponses[$tmpKey]["label"]  :  null;		//Valeur/libellé de la réponse
		if(empty($respTmp["fileName"]))	{$respFileHide="responseFileHide";	$respFileContent="<input type='file' name=\"responsesFile".$respId."\">";}
		else							{$respFileHide=null;				$respFileContent="<div id='respFileName".$respId."'><a href=\"".$respTmp["fileUrlDownload"]."\" ".Txt::tooltip("download")."><img src='app/img/attachment.png'> ".$respTmp["fileName"]."</a> &nbsp; <img src='app/img/delete.png' ".Txt::tooltip("delete")." onclick=\"deleteResponseFile('".$respId."');\">";}
		//Affiche la réponse
		echo '<div class="vPollResponseDiv '.$respClass.'">
				<input type="text" name="responses['.$respId.']" value="'.$respValue.'" placeholder="'.Txt::trad("DASHBOARD_responseNb").($tmpKey+1).'">
				<img src="app/img/attachment.png" onclick="$(\'#responseFile'.$respId.'\').slideToggle()" '.Txt::tooltip("EDIT_attachedFileAdd").'>
				<div id="responseFile'.$respId.'" class="responseFile '.$respFileHide.'">'.$respFileContent.'</div>
			  </div>';
	}
	?>

	<!--SONDAGE PAS ENCORE VOTÉ : AJOUTER UNE REPONSE-->
	<?php if($pollIsVoted==false){ ?><div id="responseAdd" onclick="$('.vPollResponseDiv:hidden:first').fadeIn().find('input').focusAlt()"><?= Txt::trad("DASHBOARD_addResponse") ?>&nbsp; <img src="app/img/plusSmall.png"></div><?php } ?>

	<!--REPONSES MULTIPLES  &&  VOTE PUBLIC  &&  AFFICHAGE AVEC LES NEWS ("checked" par défaut)  &&  DATE DE FIN-->
	<br><br>
	<div class="pollOptions">
		<input type="checkbox" name="multipleResponses" value="1" id="multipleResponsesInput" <?= !empty($curObj->multipleResponses) ? "checked" : null ?> >
		<label for="multipleResponsesInput"><?= Txt::trad("DASHBOARD_multipleResponses") ?>
	</div>
	<div class="pollOptions" title="<?= Txt::trad("DASHBOARD_publicVoteInfos") ?>">
		<input type="checkbox" name="publicVote" value="1" id="publicVoteInput" <?= (!empty($curObj->publicVote)) ? "checked" : null ?> >
		<label for="publicVoteInput"><?= Txt::trad("DASHBOARD_publicVote") ?>
	</div>
	<div class="pollOptions">
		<input type="checkbox" name="newsDisplay" value="1" id="newsDisplayInput" <?= (!empty($curObj->newsDisplay) || $curObj->isNew()) ? "checked" : null ?>>
		<label for="newsDisplayInput"><?= Txt::trad("DASHBOARD_newsDisplay") ?>
	</div>
	<div class="pollOptions">
		<img src="app/img/dateEnd.png">
		<?= Txt::trad("DASHBOARD_dateEnd") ?> :
		<input type="text" name="dateEnd" class="dateEnd" value="<?= Txt::formatDate($curObj->dateEnd,"dbDate","inputDate") ?>">
	</div>

	<!--MENU COMMUN & SUBMIT & CONTROLE DU FORM-->
	<?= $curObj->editMenuSubmit(); ?>
</form>