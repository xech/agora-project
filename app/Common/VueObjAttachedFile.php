<script>
/*******************************************************************************************
 *	EXTENSIONS DE FICHIERS POUVANT ETRE INSEREES DANS L'EDITEUR
 *******************************************************************************************/
extFileForEditor=["<?= implode('","',File::fileTypes("attachedFileInsert")) ?>"];	//Extensions d'images/vidéos/MP3
extFileImage=["<?= implode('","',File::fileTypes("imageBrowser")) ?>"];				//Extensions d'images
extFileVideo=["<?= implode('","',File::fileTypes("videoPlayer")) ?>"];				//Extensions de vidéo
extFileMp3=["<?= implode('","',File::fileTypes("mp3")) ?>"];						//Extensions de MP3

/*******************************************************************************************
 *	SELECTION D'UN FICHIER DANS UN INPUT ".attachedFileInput"
 *******************************************************************************************/
ready(function(){
	$(".attachedFileInput").on("change",function(){
		if(this.files && this.files[0].size < <?= File::uploadMaxFilesize() ?>){									//Vérif la taille du fichier
			var cptFile=Math.round(this.name.replace("attachedFile",""));											//Récupère le compteur du fichier
			$("#attachedFileDivAdd"+(cptFile+1)).fadeIn();															//Affiche l'input suivant
			if($.inArray(extension(this.value),extFileForEditor)>=0)  {$("#attachedFileOption"+cptFile).show();}	//Affiche l'option "insérer dans le texte" (cf. Tinymce)
		}
	});
});

/*******************************************************************************************
 *	FICHIER JOINT : SUPPRESSION AJAX ET SUPRESSION DU TAG HTML DANS L'EDITEUR
 *******************************************************************************************/
async function attachedFileDelete(_id)
{
	if(await confirmAlt("<?= Txt::trad("confirmDelete") ?>")){								//Demande confirmation
		$.ajax("?ctrl=object&action=attachedFileDelete&_id="+_id).done(function(result){	//Lance la suppression Ajax
			if(/true/i.test(result)){														//Vérif la confirmation de delete
				$("#attachedFileDivList"+_id).fadeOut();									//Supprime le fichier de la liste
				tinymce.activeEditor.dom.remove("attachedFileTag"+_id);						//Supprime l'image/video/mp3 dans l'éditeur (pas de "#") : cf. "attachedFileInsert()"
				notify("<?= Txt::trad("confirmDeleteNotify") ?>");							//Notif que la suppression a bien été réalisé
			}
		});
	}
}
</script>


<style>
.attachedFileDiv					{margin-top:5px;}											/*Div des inputs et des fichiers déjà enregistré*/
.attachedFileDiv label				{margin-left:10px;}											/*options "insérer dans le texte" et "supprimer"*/
[id^=attachedFileDivList]:hover		{background:#eee;}											/*Survol chaque fichier déjà enregistré (cf. padding)*/
[id^=attachedFileDivAdd]:not(#attachedFileDivAdd1), [id^=attachedFileOption]  {display:none;}	/*Masque tous les inputs, sauf le premier input  &&  Masque les boutons "insérer dans le texte" des inputs*/
</style>


<?php
////	INPUTS DES FICHIERS À ATTACHER (si besoin l'option "insérer dans le texte" pour l'éditeur tinyMce)
echo '<div><img src="app/img/attachment.png"> '.Txt::trad("EDIT_attachedFileAdd").' :</div>';
for($cptInputFile=1; $cptInputFile<=20; $cptInputFile++){
	$insertOption=($curObj::descriptionEditor==true)  ?  '<label onclick="attachedFileInsert('.$cptInputFile.')" id="attachedFileOption'.$cptInputFile.'" '.Txt::tooltip("EDIT_attachedFileInsertTooltip").'><img src="app/img/attachedFileInsert.png"> '.Txt::trad("EDIT_attachedFileInsert").'</label>'  :  null;
	echo '<div id="attachedFileDivAdd'.$cptInputFile.'" class="attachedFileDiv"><input type="file" name="attachedFile'.$cptInputFile.'" id="attachedFileInput'.$cptInputFile.'" class="attachedFileInput">'.$insertOption.'</div>';
}

////	LISTE DES FICHIERS JOINTS DEJA ATTACHÉS A UN OBJET (affiche si besoin l'option "insérer dans le texte" pour l'éditeur tinyMce)
if(count($curObj->attachedFileList())>0){
	foreach($curObj->attachedFileList() as $tmpFile){
		$fileOptions='<label onclick="attachedFileDelete('.$tmpFile["_id"].');" '.Txt::tooltip("delete").'><img src="app/img/delete.png"></label>';
		//Insertion de l'image/video/mp3 dans l'éditeur  &&  Affichage d'une miniature de l'image
		if($curObj::descriptionEditor==true){
			if(File::isType("attachedFileInsert",$tmpFile["name"]))		{$fileOptions.='<label onclick="attachedFileInsert('.$tmpFile["_id"].',\''.$tmpFile["url"].'\')"><img src="app/img/attachedFileInsert.png" '.Txt::tooltip("EDIT_attachedFileInsertTooltip").'></label>';}
			if(File::isType("imageBrowser",$tmpFile["name"]))			{$fileOptions.=' &nbsp; <img src="'.$tmpFile["url"].'" class="attachedFileInsertImg">';}
		}
		echo '<hr><div id="attachedFileDivList'.$tmpFile["_id"].'" class="attachedFileDiv"><img src="app/img/attachment.png"> '.$tmpFile["name"].$fileOptions.'</div>';
	}
}