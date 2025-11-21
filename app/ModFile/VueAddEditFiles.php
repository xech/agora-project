<!--CHARGE PLUPLOAD (SI BESOIN) -->
<?php if(Req::isParam("addVersion")==false){ ?>
<script type="text/javascript" src="app/js/plupload/plupload.full.min.js"></script>
<script type="text/javascript" src="app/js/plupload/i18n/<?= Txt::trad("CURLANG") ?>.js"></script>
<script type="text/javascript" src="app/js/plupload/jquery.ui.plupload/jquery.ui.plupload.min.js"></script>
<link rel="stylesheet" href="app/js/plupload/jquery.ui.plupload/css/jquery.ui.plupload.css" type="text/css" />
<?php } ?>


<script>
////	Init
ready(function(){
	////	NOUVELLE VERSION D'UN FICHIER  ||  AJOUT DE FICHIERS (UPLOAD MULTIPLE/SIMPLE)
	<?php if(Req::isParam("addVersion")){ ?>
		$("[name='addFileVersion']").on("change",function(){
			if($("[name='curFileName']").val()!=$("[name='addFileVersion']").val().split('\\').pop())  {notify("<?= Txt::trad("FILE_updatedName") ?>");}
		});
	<?php }else{ ?>
	////	CHARGE PLUPLOAD
	$("#uploadMultiple").plupload({
		runtimes:"html5",
		url:"?ctrl=file&action=UploadTmpFile&tmpFolderName=<?= $tmpFolderName ?>",
		max_file_size:"<?= (int)ini_get("upload_max_filesize") ?>mb",//remplace 'mo' par 'mb'
		max_file_count:200,//200 fichiers max
		unique_names:true,//On n'envoie pas plusieurs fichiers avec le meme nom
		dragdrop:true,//Fonction de Glisser/deposer de fichiers
		init:{
			FilesAdded:function(uploader,tmpFiles){															//Controle chaque fichier sélectionné :
				$("#uploadMultipleSimple").hide();															//masque la sélection d'envoi simple/multiple
				let imageResizeTypes=[<?= "'".implode("','",File::fileTypes("imageResize"))."'" ?>];		//types de fichiers 'imageResize'
				let allowedTypes  	=[<?= "'".implode("','",File::fileTypes("allowed"))."'" ?>];			//types de fichiers 'allowed'
				tmpFiles.forEach(function(tmpFile){															//Controle chaque fichier :
					let fileName=tmpFile.name;																//nom du fichier
					let fileExtension=extension(fileName);													//extension du fichier
					if(imageResizeTypes.includes(fileExtension))  			{$("#resizeImage").show();}		//option pour "optimiser l'image"
					else if(allowedTypes.includes(fileExtension)==false)	{uploader.removeFile(tmpFile);  notify(fileName+" : <?= Txt::trad("NOTIF_fileNotAllowed")?>");}//notify "Fichier interdit"
				});
			}
		}
	});
	////	Tooltip du bouton d'upload. Ex: "50 Mo maximum par fichier. Sélectionnez plusieurs fichiers via la touche Ctrl"
	$(".plupload_button").attr("title","<?= $uploadMaxFilesize.' '.Txt::trad("FILE_addMultipleFilesTooltip") ?>");
	<?php } ?>
});

////	Controle spécifique du formulaire (cf. "VueObjMenuEdit.php")
function objectFormControl(){
	return new Promise((resolve)=>{
		// "Merci de sélectionner au moins un fichier"
		let isPlupload=$("#uploadMultiple").isDisplayed();
		if( (isPlupload==true && $("#uploadMultiple").plupload("getFiles").length==0)  ||  (isPlupload==false && $("[name='addFileSimple'],[name='addFileVersion']").isEmpty())){
			notify("<?= Txt::trad("FILE_selectFile") ?>");
			resolve(false);
		}
		//Envoi des fichiers
		else if(isPlupload==true){																	//Lance Plupload :
			$(".plupload_add, #uploadOptions").hide();												//Masque le bouton de lancement et les options d'upload
			$(".plupload_filelist_footer .plupload_file_status").show();							//Affiche le % de progression
			$("#uploadMultiple").plupload("start").on("complete",function(){  resolve(true);  });	//Retourne true à la fin des uploads
		} else {resolve(true);}																		//Validation directe du formulaire
	});
}
</script>


<style>
fieldset								{text-align:center; margin:0px; padding:30px;}										/*surcharge tt les fieldset*/
fieldset#uploadMultiple					{padding:0px;}																		/*fieldset Plupload*/
.plupload_wrapper						{min-width:100%!important; max-width:100%!important;}								/*surcharge pour eviter le scroll horizontal*/
.plupload_container						{height:220px; min-height:220px; max-width:100%; background-color:transparent;}		/*conteneur principal*/
.plupload_content						{top:0px; height:160px;}															/*dropzone & liste des fichiers (160px minimum)*/
.plupload_droptext						{font-size:1rem; opacity:0.5;}														/*dropzone*/
.plupload_filelist_content				{text-align:left; font-size:0.95rem;}												/*liste des fichiers*/
.plupload_file_size						{min-width:70px;}																	/*taille de chaque fichier*/
.plupload_filelist_footer				{top:145px; border:none; background:none;}											/*Tableau : bouton d'upload et % de progression*/
.plupload_filelist_footer td			{padding:5px;} 																		/*idem*/
.plupload_buttons						{width:100%; text-align:center;}													/*idem : boutons de lancement/arrêt*/
.plupload_button						{width:250px; padding:15px; border-radius:7px; background:linear-gradient(#fff,#eee); font-size:1.05rem; font-weight:bold!important;}/*idem*/
.plupload_file_status 					{position:absolute; bottom:15px; right:15px;}										/*% de progression*/
.ui-state-highlight						{background-color:#d2f5b8!important; border-block-color:#ccc!important;}			/*fichier en cours d'upload*/
#uploadOptions							{display:table; margin-inline:auto; margin-top:30px;}								/*options d'upload*/
#uploadOptions>div						{display:table-cell; padding-inline:20px; vertical-align:middle;}					/*idem*/
#descriptionTextarea					{margin-top:20px;}																	/*textarea description*/
#uploadSimple, #uploadOptions #resizeImage, #descriptionTextarea, .plupload_progress_container, .plupload_file_status											{display:none;}				/*masque par défaut*/
.plupload_header, .plupload_filelist_header, .plupload_start, .plupload_filelist_footer td:is(.plupload_file_action,.plupload_file_size), .ui-resizable-handle	{display:none!important;}	/*masque toujours*/
/*AFFICHAGE SMARTPHONE*/
@media screen and (max-width:490px){
	.plupload_droptext 	{display:none!important;}
	#uploadOptions>div	{display:inline-block; padding:10px;}
}
</style>


<form action="index.php" method="post" id="mainForm" enctype="multipart/form-data">
	<!--TITRE MOBILE-->
	<?= $curObj->titleMobile("FILE_addFile") ?>

	<!--NOUVELLE VERSION D'UN FICHIER  ||  AJOUT DE FICHIERS DE FICHIERS VIA PLUPLOAD OU INPUT FILE-->
	<?php if(Req::isParam("addVersion")){ ?>
		<fieldset id="uploadAddVersion">
			<input type="file" name="addFileVersion" <?= Txt::tooltip($uploadMaxFilesize." Max") ?> >
			<input type="hidden" name="curFileName" value="<?= $curObj->name ?>">
			<input type="hidden" name="addVersion" value="true">
		</fieldset>
	<?php }else{ ?>
		<fieldset id="uploadMultiple"><input type="file" name="addFileMultiple" <?= Txt::tooltip($uploadMaxFilesize." Max") ?>></fieldset>
		<fieldset id="uploadSimple"><input type="file" name="addFileSimple" <?= Txt::tooltip($uploadMaxFilesize." Max") ?>></fieldset>
		<input type="hidden" name="tmpFolderName" value="<?= $tmpFolderName ?>">
	<?php } ?>
		
	<!--OPTIONS : UPLOAD MULTIPLE/SIMPLE  &&  IMAGERESIZE  &&  DESCRIPTION-->
	<div id="uploadOptions">
		<?php if(Req::isParam("addVersion")==false){ ?>
		<div id="uploadMultipleSimple" <?= Txt::tooltip("FILE_uploadSimpleMultiple") ?>>
			<select name="uploadForm" onchange="$('#uploadMultiple,#uploadSimple').hide();$('#'+this.value).show();">
				<option value="uploadMultiple"><?= Txt::trad("FILE_uploadMultiple") ?></option>
				<option value="uploadSimple"><?= Txt::trad("FILE_uploadSimple") ?></option>
			</select>
		</div>
		<?php } ?>
		<div id="resizeImage">
			<input type="checkbox" name="imageResize" id="imageResizeInput" value="1" checked> 
			<label for="imageResizeInput"><?= Txt::trad("FILE_imgReduce") ?></label>
		</div>
		<div id="uploadDescription" onclick="$('#descriptionTextarea').slideToggle();">
			<img src="app/img/description.png"> <?= Txt::trad("description") ?> <img src="app/img/arrowBottom.png">
		</div>
	</div>
	
	<!--DESCRIPTION TEXTAREA-->
	<textarea name="description" placeholder="<?= Txt::trad("description") ?>" id="descriptionTextarea"></textarea>

	<!--MENU D'EDITION & VALIDATION DU FORM-->
	<?= $curObj->editMenuSubmit() ?>
</form>