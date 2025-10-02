<script>
/********************************************************************************************************
 *	SELECTION D'UN FICHIER DANS UN INPUT ".attachedFileInput"
 *******************************************************************************************/
ready(function(){
	$(".attachedFileInput").on("change",function(){
		if(this.files && this.files[0].size < <?= File::uploadMaxFilesize() ?>){			//Vérif la taille du fichier
			var cptFile=Math.round(this.name.replace("attachedFile",""));					//Récupère le compteur du fichier
			$("#attachedFileAdd"+(cptFile+1)).fadeIn();										//Affiche l'input suivant
			if(typeof attachedFileInsert=="function" && isType("editorInsert",this.value))	//Controle si l'option "attachedFileInsert()" existe
				{$("#attachedFileInsert"+cptFile).show();}									//Affiche l'option "insérer dans le texte" (cf. Tinymce)
		}
	});
});

/********************************************************************************************************
 *	FICHIER JOINT : SUPPRESSION AJAX ET SUPRESSION DU TAG HTML DANS L'EDITEUR
 *******************************************************************************************/
async function attachedFileDelete(fileId)
{
	if(await confirmAlt("<?= Txt::trad("confirmDelete") ?>")){																//Demande confirmation
		$.ajax("?ctrl=object&action=attachedFileDelete&_id="+fileId).done(function(result){									//Lance la suppression Ajax
			if(/true/i.test(result)){																						//Vérif la confirmation de delete
				$("#attachedFileList"+fileId).fadeOut();																	//Supprime le fichier de la liste
				if(typeof attachedFileInsert=="function")  {tinymce.activeEditor.dom.remove("attachedFileTag"+fileId);}		//Supprime l'image/video/mp3 dans l'éditeur (cf. VueObjEditor.php)
				notify("<?= Txt::trad("confirmDeleteNotify") ?>");															//Notif que la suppression a bien été réalisé
			}
		});
	}
}
</script>

<style>
.vAttachedFileDiv								{margin-top:10px; padding:5px;}							/*Inputs d'ajout de fichiers & Fichiers joints existants*/
.vAttachedFileThumb								{max-width:80px!important; max-height:60px!important;}	/*Vignettes des images*/
.vAttachedFileThumb, .vAttachedFileDiv label	{margin-left:15px;}										/*Vignettes & options des fichiers existants*/
[id^=attachedFileAdd]:not(#attachedFileAdd1), [id^=attachedFileInsert]  {display:none;}					/*Masque tous les inputs (sauf le premier)  &  Masque l'option "insérer dans le texte" des inputs*/
</style>


<!--"JOINDRE DES FICHIERS" : TITRE-->
<div><img src="app/img/attachment.png"> <?= Txt::trad("EDIT_attachedFileAdd") ?> :</div>

<!--"JOINDRE DES FICHIERS" : LISTE DES INPUTS-->
<?php for($inputCpt=1; $inputCpt<=20; $inputCpt++){ ?>
<div id="attachedFileAdd<?= $inputCpt ?>" class="vAttachedFileDiv">
	<input type="file" name="attachedFile<?= $inputCpt ?>" id="attachedFileInput<?= $inputCpt ?>" class="attachedFileInput">
	<!--OPTION "INSÉRER DANS LE TEXTE" DE TINYMCE-->
	<?php if($curObj::descriptionEditor==true){ ?><label onclick="attachedFileInsert(<?= $inputCpt ?>)" id="attachedFileInsert<?= $inputCpt ?>" <?= Txt::tooltip("EDIT_attachedFileInsertTooltip") ?> ><img src="app/img/editorInsert.png"> <?= Txt::trad("EDIT_attachedFileInsert") ?></label><?php } ?>
</div>
<?php } ?>

<!--FICHIERS JOINTS EXISTANTS-->
<?php foreach($curObj->attachedFileList() as $tmpFile){ ?>
<hr>
<div id="attachedFileList<?= $tmpFile["_id"] ?>" class="lineHover vAttachedFileDiv">
	<!--LABEL DU FICHIER  &  SI BESOIN THUMB DE L'IMAGE-->
	<label onclick="confirmRedir('<?= $tmpFile['urlDownload'] ?>',labelConfirmDownload)"><img src="app/img/attachment.png"> <?= $tmpFile["name"] ?></a>
	<?php if(File::isType("editorImage",$tmpFile["name"])){ ?><img src="<?= $tmpFile["displayUrl"] ?>" class="vAttachedFileThumb"><?php } ?>
	<!--OPTION "INSÉRER DANS LE TEXTE" DE TINYMCE  &  OPTION "SUPPRIMER"-->
	<?php if($curObj::descriptionEditor==true && File::isType("editorInsert",$tmpFile["name"])){ ?><label onclick="attachedFileInsert(<?= $tmpFile['_id'] ?>,'<?= $tmpFile['displayUrl'] ?>')" <?= Txt::tooltip("EDIT_attachedFileInsertTooltip") ?> ><img src="app/img/editorInsert.png"> <?= Txt::trad("EDIT_attachedFileInsert") ?></label><?php } ?>
	<label onclick="attachedFileDelete(<?= $tmpFile['_id'] ?>)"><img src="app/img/delete.png"> <?= Txt::trad("delete") ?></label>
</div>
<?php } ?>