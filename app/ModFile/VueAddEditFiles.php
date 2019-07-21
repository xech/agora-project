<!--CHARGE PLUPLOAD (SI BESOIN) -->
<?php if(Req::isParam("addVersion")==false){ ?>
<script type="text/javascript" src="app/js/plupload/plupload.full.min.js"></script>
<script type="text/javascript" src="app/js/plupload/i18n/<?= Txt::trad("UPLOADER") ?>.js"></script>
<script type="text/javascript" src="app/js/plupload/jquery.ui.plupload/jquery.ui.plupload.min.js"></script>
<link rel="stylesheet" href="app/js/plupload/jquery.ui.plupload/css/jquery.ui.plupload.css" type="text/css" />
<?php } ?>

<script>
////	Resize
lightboxSetWidth(520);

// Init
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
			//Ajout de fichier : Masque le "Déposer les fichiers ici" && Ajoute si besoin l'option pour optimiser l'image
			FilesAdded:function(uploader,tmpFiles){
				$(".plupload_droptext").hide();
				for(var key=0; key<tmpFiles.length; key++){//
					if(/(jpg|jpeg|png)$/i.test(tmpFiles[key].name))  {$("#imageResizeOption").show();}
				}
			}
		}
	});
	////	PlUpload : Ajoute la taille Max des fichiers dans le "title" du bouton  "Ajouter les fichiers"
	$(".plupload_add").attr("title","<?= File::displaySize(File::uploadMaxFilesize()) ?> Max. <?= Txt::trad("FILE_addMultipleFilesInfo") ?>");
	<?php } ?>
});

////	Change le formulaire d'upload : simple/multiple
function uploadFormChange(uploadForm)
{
	$("#uploadMultiple,#uploadSimple").hide();
	$("#"+uploadForm).show();
}

////	Contrôle du formulaire
function formControl()
{
	//Ajout de fichier via Plupload
	if($("#uploadMultiple").is(":visible")){
		if($("#uploadMultiple").plupload("getFiles").length==0)  {notify("<?= Txt::trad("FILE_selectFile") ?>");  return false;}//Aucun fichier sélectionné?
		if(mainFormControl()){//Si le controle global est OK : on lance l'upload via Plupload.. qui validera ensuite le formulaire
			$("#uploadMultiple").plupload("start").on("complete",function(){
				$(".plupload_add").hide();//Masque le bouton "Ajouter des fichiers" en fin d'upload
				$("#filesForm")[0].submit();
			});
		}
		return false;//Retourne "false" car c'est PLUpload qui valide le formulaire (cf. ".on('complete')") : une fois tous les fichiers envoyés dans le dossier temporaire
	}
	//Input File : nouvelle version de fichier
	else{
		if($("[name='addFileSimple']").isEmpty() && $("[name='addFileVersion']").isEmpty())  {notify("<?= Txt::trad("FILE_selectFile") ?>"); return false;}//Aucun fichier sélectionné?
		return mainFormControl();//Controle final (champs obligatoires, etc)
	}
}
</script>


<style>
/*Surcharge de Plupload*/
.plupload_container					{height:210px; min-height:210px; border-radius:3px 3px 0px 0px;}/*conteneur principal*/
.plupload_wrapper					{min-width:100%!important; max-width:100%!important;}/*Evite le scroll horizontal*/
.plupload_header_content			{display:none;}
.plupload_content					{top:0px;}/*Repositionne la liste des fichiers, car ".plupload_header_content" est masqué*/
.plupload_droptext					{text-transform:uppercase; color:#aaa;}/*texte "Déposer les fichiers ici"*/
.plupload_cell, .plupload_buttons	{width:100%; text-align:center; padding:12px;}/*conteneur des boutons principaux*/
.plupload_buttons .plupload_button	{text-transform:uppercase; height:28px; padding-top:18px!important; font-weight:bold!important;}/*Boutons principaux (Ajouter, Arreter, etc)*/
.plupload_buttons .plupload_add 	{width:220px;}/*Boutons Ajouter*/

/*Options d'upload*/
#uploadSimple, #uploadAdd			{padding:10px; border:1px solid #aaa; border-bottom:0px;}/*input d'upload simple : au dessus des options*/
#uploadOptions						{padding:10px; text-align:center; border:1px solid #aaa; border-top:0px; border-radius:0px 0px 3px 3px;}
#uploadOptions>span					{margin-left:20px;}/*Options d'upload*/
#inputDescription					{margin-top:15px;}
#uploadSimple, #imageResizeOption, #inputDescription	{display:none;}

/*Masque les elements inutiles de Plupload (header, progress, etc.)*/
.plupload_filelist_header, .plupload_start, .plupload_progress_container, .plupload_filelist_footer .plupload_file_size, .plupload_filelist_footer .plupload_file_status	{display:none;}

/*RESPONSIVE FANCYBOX (440px)*/
@media screen and (max-width:440px){
	.plupload_droptext 	{display:none!important;}
	.plupload_container	{height:150px; min-height:150px;}
}
</style>


<form action="index.php" method="post" onsubmit="return formControl()" id="filesForm" enctype="multipart/form-data" class="lightboxContent">

	<!--NOUVELLE VERSION D'UN FICHIER  ||  AJOUT DE FICHIERS (UPLOAD MULTIPLE/SIMPLE)-->
	<?php if(Req::isParam("addVersion")){ ?>
		<div id="uploadAdd">
			<input type="file" name="addFileVersion" title="<?= File::displaySize(File::uploadMaxFilesize()) ?> Max">
			<input type="hidden" name="curFileName" value="<?= $curObj->name ?>">
			<input type="hidden" name="addVersion" value="true">
		</div>
	<?php }else{ ?>
		<div id="uploadMultiple"><input type="file" name="addFileMultiple" title="<?= File::displaySize(File::uploadMaxFilesize()) ?> Max"></div>
		<div id="uploadSimple"><input type="file" name="addFileSimple" title="<?= File::displaySize(File::uploadMaxFilesize()) ?> Max"></div>
		<input type="hidden" name="tmpFolderName" value="<?= $tmpFolderName ?>">
	<?php } ?>
		
	<!--OPTIONS : UPLOAD MULTIPLE/SIMPLE && IMAGERESIZE && DESCRIPTION-->
	<div id="uploadOptions">
		<?php if(Req::isParam("addVersion")==false){ ?>
		<select name="uploadForm" onchange="uploadFormChange(this.value)">
			<option value="uploadMultiple"><?= Txt::trad("FILE_uploadMultiple") ?></option>
			<option value="uploadSimple"><?= Txt::trad("FILE_uploadSimple") ?></option>
		</select>
		<?php } ?>
		<span id="imageResizeOption">
			<input type="checkbox" name="imageResize" id="imageResizeInput" value="1" checked> 
			<label for="imageResizeInput"><?= Txt::trad("FILE_imgReduce") ?></label>
		</span>
		<span>
			<span class="sLink" onclick="$('#inputDescription').slideToggle();"><?= Txt::trad("description") ?> <img src="app/img/description.png"></span>
			<textarea name="description" placeholder="<?= Txt::trad("description") ?>" id="inputDescription"></textarea>
		</span>
	</div>

	<!--MENU COMMUN-->
	<?= $curObj->menuEdit() ?>
</form>