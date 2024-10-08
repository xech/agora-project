<!--CHARGE PLUPLOAD (SI BESOIN) -->
<?php if(Req::isParam("addVersion")==false){ ?>
<script type="text/javascript" src="app/js/plupload/plupload.full.min.js"></script>
<script type="text/javascript" src="app/js/plupload/i18n/<?= Txt::trad("CURLANG") ?>.js"></script>
<script type="text/javascript" src="app/js/plupload/jquery.ui.plupload/jquery.ui.plupload.min.js"></script>
<link rel="stylesheet" href="app/js/plupload/jquery.ui.plupload/css/jquery.ui.plupload.css" type="text/css" />
<?php } ?>


<script>
////	Resize
lightboxSetWidth(600);

////	INIT
$(function(){
	////	NOUVELLE VERSION D'UN FICHIER  ||  AJOUT DE FICHIERS (UPLOAD MULTIPLE/SIMPLE)
	<?php if(Req::isParam("addVersion")){ ?>
		$("[name='addFileVersion']").on("change",function(){
			if($("[name='curFileName']").val()!=$("[name='addFileVersion']").val().split('\\').pop())  {notify("<?= Txt::trad("FILE_updatedName") ?>");}
		});
	<?php }else{ ?>
	////	Charge PlUpload
	$("#uploadMultiple").plupload({
		runtimes:"html5",
		url:"?ctrl=file&action=UploadTmpFile&tmpFolderName=<?= $tmpFolderName ?>",
		max_file_size:"<?= (int)ini_get("upload_max_filesize") ?>mb",//remplace 'mo' par 'mb'
		max_file_count:200,//200 fichiers max
		unique_names:true,//On n'envoie pas plusieurs fichiers avec le meme nom
		dragdrop:true,//Fonction de Glisser/deposer de fichiers
		init:{
			//Fichier sélectionnés
			FilesAdded:function(uploader,tmpFiles){
				$(".plupload_droptext, select[name=uploadForm]").hide();	//Masque le "Déposer les fichiers ici" et le <select> d'envoi multiple/simple
				$(".plupload_buttons").css("padding","0px");				//Descend le bouton "Choisir les fichiers" pour laisser la place à la liste de fichiers
				for(var key=0; key<tmpFiles.length; key++){					//Ajoute si besoin l'option pour optimiser l'image
					if(/(jpg|jpeg|png)$/i.test(tmpFiles[key].name))  {$("#imageResizeOption").show();}
				}
			}
		}
	});
	////	PlUpload : Ajoute la taille Max des fichiers dans le "title" du bouton  "Ajouter les fichiers"
	$(".plupload_add").attr("title","<?= $uploadMaxFilesize ?> Maximum par fichier. <?= Txt::trad("FILE_addMultipleFilesTooltip") ?>");
	<?php } ?>
});

////	Controle spécifique à l'objet (cf. "VueObjEditMenuSubmit.php")
function objectFormControl(){
	return new Promise((resolve)=>{
		//// Ajout de fichier via Plupload
		if($("#uploadMultiple").isVisible()){
			if($("#uploadMultiple").plupload("getFiles").length==0){								//Vérif s'il y a au moins un fichier dans la liste
				notify("<?= Txt::trad("FILE_selectFile") ?>");										//Notify si aucun fichier
				resolve(false);																		//Retourne false
			}else{																					//Lance l'upload via Plupload
				$(".plupload_add,#uploadOptions>span").hide();										//Masque le bouton d'ajout & les options d'upload (pas le block d'options)
				$(".plupload_filelist_footer .plupload_file_status").show();						//Affiche le % de progression
				$("#uploadMultiple").plupload("start").on("complete",function(){ resolve(true); });	//Retourne true à la fin des uploads
			}
		}
		//// Ajout de fichier via un input "file" (envoi simple ou nouvelle version de fichier)
		else{
			// Verif si un fichier a été sélectionné
			if($("[name='addFileSimple'],[name='addFileVersion']").isEmpty())	{notify("<?= Txt::trad("FILE_selectFile") ?>");  resolve(false);}
			else																{resolve(true);}
		}
	});
}
</script>


<style>
/*Surcharge de Plupload*/
.plupload_container					{height:200px; min-height:200px;}															/*conteneur principal*/
.plupload_wrapper					{min-width:100%!important; max-width:100%!important;}										/*Evite le scroll horizontal*/
.plupload_header					{display:none;}																				/*Masque le header par défaut*/
.plupload_content					{top:0px; height:170px}																		/*Liste des fichiers (cf. ".plupload_header" masqué)*/
.plupload_droptext					{color:#aaa; font-size:1.1em;}																/*"Glisser les fichiers ici". "overflow" pour Firefox*/
.plupload_cell, .plupload_buttons	{width:100%; text-align:center;}															/*conteneur des boutons principaux*/
.plupload_buttons .plupload_button	{padding:15px; width:250px; font-size:1.1em; font-weight:bold; text-transform:uppercase;}	/*Bouton "Choisir les fichiers" /  "Arreter"*/
.ui-widget-header					{border:none!important;	background:none!important;}											/*Annule le background par defaut de jQuery-UI !*/
.ui-resizable-handle				{display:none!important;}

/*Sélection de fichiers*/
#uploadSimple, #uploadAdd, .plupload_container			{padding:10px; border:1px solid #aaa; border-bottom:0px; border-radius:3px 3px 0px 0px;}									/*Block des fichiers (partie haute)*/
#uploadOptions											{padding:8px; padding-top:25px; text-align:center; border:1px solid #aaa; border-top:0px; border-radius:0px 0px 3px 3px;}	/*Block des options (partie basse)*/
#uploadOptions>span										{margin-left:20px;}/*Options d'upload*/
#inputDescription										{margin-top:15px;}
#uploadSimple, #imageResizeOption, #inputDescription	{display:none;}

/*Masque les elements inutiles de Plupload (header, progress, etc.)*/
.plupload_filelist_header, .plupload_start, .plupload_progress_container, .plupload_filelist_footer .plupload_file_size, .plupload_filelist_footer .plupload_file_status  {display:none;}
.plupload_filelist_footer .plupload_file_status  {position:absolute; bottom:13px; right:15px;}/*Repositionne le % de progression*/

/*MOBILE FANCYBOX (440px)*/
@media screen and (max-width:440px){
	.plupload_droptext 	{display:none!important;}
	.plupload_container	{height:180px; min-height:180px;}
}
</style>


<form action="index.php" method="post" id="mainForm" enctype="multipart/form-data">
	<!--TITRE MOBILE-->
	<?= $curObj->titleMobile("FILE_addFile") ?>

	<!--NOUVELLE VERSION D'UN FICHIER  ||  AJOUT DE FICHIERS (UPLOAD MULTIPLE/SIMPLE)-->
	<?php if(Req::isParam("addVersion")){ ?>
		<div id="uploadAdd">
			<input type="file" name="addFileVersion" title="<?= $uploadMaxFilesize ?> Max">
			<input type="hidden" name="curFileName" value="<?= $curObj->name ?>">
			<input type="hidden" name="addVersion" value="true">
		</div>
	<?php }else{ ?>
		<div id="uploadMultiple"><input type="file" name="addFileMultiple" title="<?= $uploadMaxFilesize ?> Max"></div>
		<div id="uploadSimple"><input type="file" name="addFileSimple" title="<?= $uploadMaxFilesize ?> Max"></div>
		<input type="hidden" name="tmpFolderName" value="<?= $tmpFolderName ?>">
	<?php } ?>
		
	<!--OPTIONS : UPLOAD MULTIPLE/SIMPLE && IMAGERESIZE && DESCRIPTION-->
	<div id="uploadOptions">
		<?php if(Req::isParam("addVersion")==false){ ?>
		<select name="uploadForm" onchange="$('#uploadMultiple,#uploadSimple').hide();$('#'+this.value).show();">
			<option value="uploadMultiple"><?= Txt::trad("FILE_uploadMultiple") ?></option>
			<option value="uploadSimple"><?= Txt::trad("FILE_uploadSimple") ?></option>
		</select>
		<?php } ?>
		<span id="imageResizeOption">
			<input type="checkbox" name="imageResize" id="imageResizeInput" value="1" checked> 
			<label for="imageResizeInput"><?= Txt::trad("FILE_imgReduce") ?></label>
		</span>
		<span>
			<span onclick="$('#inputDescription').slideToggle();"><?= Txt::trad("description") ?> <img src="app/img/description.png"></span>
			<textarea name="description" placeholder="<?= Txt::trad("description") ?>" id="inputDescription"></textarea>
		</span>
	</div>

	<!--MENU COMMUN & SUBMIT & CONTROLE DU FORM-->
	<?= $curObj->editMenuSubmit() ?>
</form>