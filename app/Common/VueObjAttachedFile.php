<script>
/*******************************************************************************************
 *	EXTENSIONS DE FICHIERS POUVANT ETRE INSEREES DANS L'EDITEUR
 *******************************************************************************************/
extFileInsert=["<?= implode('","',File::fileTypes("attachedFileInsert")) ?>"];//Extensions acceptées pour l'insertion d'images/vidéos/mp3
extImage=["<?= implode('","',File::fileTypes("imageBrowser")) ?>"];			//Extensions des images
extVideo=["<?= implode('","',File::fileTypes("videoPlayer")) ?>"];			//Extensions des vidéos
extMp3=["<?= implode('","',File::fileTypes("mp3")) ?>"];					//Extension de MP3

/*******************************************************************************************
 *	SELECTION D'UN FICHIER DANS UN INPUT ".attachedFileInput"
 *******************************************************************************************/
$(function(){
	$(".attachedFileInput").change(function(){
		if(this.files && this.files[0].size < <?= File::uploadMaxFilesize() ?>){								//Vérif la taille du fichier
			var cptFile=Math.round(this.name.replace("attachedFile",""));										//Récupère le compteur du fichier
			$("#attachedFileDivAdd"+(cptFile+1)).fadeIn();														//Affiche l'input suivant
			if($.inArray(extension(this.value),extFileInsert)>=0)  {$("#attachedFileOption"+cptFile).show();}	//Affiche l'option "insérer dans le texte" (cf. Tinymce)
		}
	});
});

/*******************************************************************************************
 *	FICHIER JOINT : SUPPRESSION AJAX ET SUPRESSION DU TAG HTML DANS L'EDITEUR
 *******************************************************************************************/
function attachedFileDelete(_id)
{
	//Demande confirmation
	if(confirm("<?= Txt::trad("confirmDelete") ?>")==false)  {return false;}
	//Lance la suppression et efface le fichier lorsque c'est fait
	$.ajax("?ctrl=object&action=attachedFileDelete&_id="+_id).done(function(result){
		if(/true/i.test(result)){
			$("#attachedFileDivList"+_id).fadeOut();				//Supprime le fichier de la liste
			tinymce.activeEditor.dom.remove("attachedFileTag"+_id);	//Supprime éventuellement l'image/video/mp3 dans l'éditeur (pas besoin de "#" pour selectionner l'id)
		}
	});
}
</script>


<style>
.attachedFileDiv					{margin-top:5px;}											/*Div des inputs et des fichiers déjà enregistré*/
.attachedFileDiv label				{margin-left:10px;}											/*options "insérer dans le texte" et "supprimer"*/
[id^=attachedFileDivList]:hover		{background-color:#eee;}									/*Survol chaque fichier déjà enregistré (cf. padding)*/
[id^=attachedFileDivAdd]:not(#attachedFileDivAdd1), [id^=attachedFileOption]  {display:none;}	/*Masque tous les inputs, sauf le premier input  &&  Masque les boutons "insérer dans le texte" des inputs*/
</style>


<?php
////	INPUTS DES FICHIERS À ATTACHER (si besoin l'option "insérer dans le texte" pour l'éditeur tinyMce)
echo '<div><img src="app/img/attachment.png"> '.Txt::trad("EDIT_attachedFileAdd").' :</div>';
for($cptFile=1; $cptFile<=20; $cptFile++){
	$insertOption=($curObj::htmlEditorField!=null)  ?  '<label onclick="attachedFileInsert('.$cptFile.')" id="attachedFileOption'.$cptFile.'" title="'.Txt::trad("EDIT_attachedFileInsertInfo").'"><img src="app/img/attachedFileInsert.png"> '.Txt::trad("EDIT_attachedFileInsert").'</label>'  :  null;
	echo '<div id="attachedFileDivAdd'.$cptFile.'" class="attachedFileDiv"><input type="file" name="attachedFile'.$cptFile.'" id="attachedFileInput'.$cptFile.'" class="attachedFileInput">'.$insertOption.'</div>';
}

////	LISTE DES FICHIERS JOINTS DEJA ATTACHÉS A UN OBJET (affiche si besoin l'option "insérer dans le texte" pour l'éditeur tinyMce)
if(count($curObj->attachedFileList())>0){
	echo '<hr>';
	foreach($curObj->attachedFileList() as $tmpFile){
		$fileOptions='<label onclick="attachedFileDelete('.$tmpFile["_id"].');" title="'.Txt::trad("delete").'"><img src="app/img/delete.png"></label>';
		if($curObj::htmlEditorField!=null && File::isType("attachedFileInsert",$tmpFile["name"]))  {$fileOptions='<label onclick="attachedFileInsert('.$tmpFile["_id"].',\''.$tmpFile["url"].'\')"><img src="app/img/attachedFileInsert.png" title="'.Txt::trad("EDIT_attachedFileInsertInfo").'"></label>'.$fileOptions;}
		echo '<div id="attachedFileDivList'.$tmpFile["_id"].'" class="attachedFileDiv"><img src="app/img/attachment.png"> '.$tmpFile["name"].$fileOptions.'</div>';
	}
}