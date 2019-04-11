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
	<?php if(Req::isParam("addVersion")){ ?>
		////	Nouvelle version de fichier avec un nom différent : affiche une notif.
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
		unique_names:true,//On n'envoie pas plusieurs fichiers avec le même nom
		dragdrop:true,//Fonction de Glisser/déposer de fichiers
		views:{list:true,thumbs:true,active:"list"},//Différentes vues des fichiers à uploader
		init:{
			//Ajout de fichiers : triggers
			FilesAdded:function(uploader,tmpFiles){
				for(var key=0; key<tmpFiles.length; key++)  {imageOptimize(tmpFiles[key].name);}//Optimise l'image?
				$(".plupload_droptext").hide();//Masque le "Déposer les fichiers ici"
			}
		}
	});

	////	PlUpload : Ajoute la taille Max des fichiers dans le "title" du bouton  "selectionner les fichiers"
	$(".plupload_add").attr("title","<?= File::displaySize(File::uploadMaxFilesize()) ?> Max. <?= Txt::trad("FILE_addMultipleFilesInfo") ?>");
	
	////	Responsive : le formulaire simple est affiché par défaut
	isMobile() ? uploadFormChange("uploadSimple") : uploadFormChange("uploadMultiple");
	<?php } ?>

	////	Affiche l'option d'optimisation d'image
	$("[name='addFileSimple'],[name='addFileVersion']").on("change",function(){ imageOptimize($(this).val()); });
});

////	Change le formulaire d'upload : simple/multiple
function uploadFormChange(uploadForm)
{
	//Masque les 2 formualaires ...puis affiche celui sélectionné
	$("#uploadMultiple,#uploadSimple").hide();
	$("#"+uploadForm).show();
}

////	Affiche l'option "redimension d'image" OU Affiche les fichiers au format "liste"
function imageOptimize(fileName)
{
	if(/(jpg|jpeg|png)$/i.test(fileName))	{$("#imageResizeOption").show();}
	else									{$("label[for='uploader_view_list']").trigger("click");}
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
		return false;//Retourne "false" car c'est l'uploader (ci-dessus) qui valide le formulaire
	}
	//Input File : nouvelle version de fichier
	else{
		if($("[name='addFileSimple']").isEmpty() && $("[name='addFileVersion']").isEmpty())  {notify("<?= Txt::trad("FILE_selectFile") ?>"); return false;}//Aucun fichier sélectionné?
		return mainFormControl();//Controle final (champs obligatoires, etc)
	}
}
</script>


<style>
/*Init*/
#uploadMultiple, #uploadSimple, #imageResizeOption, #inputDescription	{display:none;}
[name='uploadForm']			{margin-bottom:10px;}
.vUploadOptions				{margin-top:15px; text-align:right;}

/*Surcharge de Plupload*/
.plupload_wrapper			{min-width:100%!important; max-width:100%!important;}/*Evite le scroll horizontal*/
.plupload_container			{height:250px; min-height:250px;}
.plupload_logo, .plupload_header_title, .plupload_header_text, .plupload_start, .plupload_filelist_header	{display:none;}/*masque les elements inutiles : logo, titre, etc.*/
.plupload_header_content	{height:35px;}/*Header de l'uploader à 35px au lieu de 57px*/
.plupload_view_list .plupload_content, .plupload_view_thumbs .plupload_content	{top:35px;}	/*Repositionne la liste des fichiers. Cf. "plupload_header_content" ci-dessus*/
.plupload_view_switch		{top:5px; right:10px;}/*repositionne le menu list/thumbs*/
.plupload_droptext 			{text-transform:uppercase; color:#aaa;}/*texte "Déposer les fichiers ici"*/
.plupload_add				{text-transform:uppercase; width:220px; font-weight:bold!important;}/*Bouton d'ajout de fichiers*/

/*RESPONSIVE FANCYBOX (440px)*/
@media screen and (max-width:440px){
	.plupload_droptext 	{color:#fff;}
	.plupload_container	{height:200px; min-height:200px;}
}
</style>


<form action="index.php" method="post" onsubmit="return formControl()" id="filesForm" enctype="multipart/form-data" class="lightboxContent">
	<?php if(Req::isParam("addVersion")){ ?>
		<!--NOUVELLE VERSION D'UN FICHIER-->
		<input type="file" name="addFileVersion" title="<?= File::displaySize(File::uploadMaxFilesize()) ?> Max">
		<input type="hidden" name="curFileName" value="<?= $curObj->name ?>">
		<input type="hidden" name="addVersion" value="true">
	<?php }else{ ?>
		<!--NOUVEAU FICHIER :  UPLOAD MULTIPLE (PLUPLOAD) || UPLOAD SIMPLE -->
		<select name="uploadForm" onchange="uploadFormChange(this.value)">
			<option value="uploadMultiple"><?= Txt::trad("FILE_uploadMultiple") ?></option>
			<option value="uploadSimple"><?= Txt::trad("FILE_uploadSimple") ?></option>
		</select>
		<div id="uploadMultiple"><input type="file" name="addFileMultiple" title="<?= File::displaySize(File::uploadMaxFilesize()) ?> Max"></div>
		<div id="uploadSimple"><input type="file" name="addFileSimple" title="<?= File::displaySize(File::uploadMaxFilesize()) ?> Max"></div>
		<input type="hidden" name="tmpFolderName" value="<?= $tmpFolderName ?>">
	<?php } ?>

	<!--imageResize-->
	<div class="vUploadOptions" id="imageResizeOption">
		<input type="checkbox" name="imageResize" id="imageResizeInput" value="1" checked> 
		<label for="imageResizeInput"><?= Txt::trad("FILE_imgReduce") ?></label>
	</div>

	<!--description-->
	<div class="vUploadOptions">
		<div class="sLink" onclick="$('#inputDescription').slideToggle();"><?= Txt::trad("description") ?> <img src="app/img/description.png"></div>
		<textarea name="description" placeholder="<?= Txt::trad("description") ?>" id="inputDescription"></textarea>
	</div>

	<!--MENU COMMUN-->
	<?= $curObj->menuEdit() ?>
</form>