<?php if($curObj::descriptionEditor==true){ ?>
	<script type="text/javascript" src="app/js/tinymce_7.3.0/tinymce.min.js"></script>
	<script>
	/*******************************************************************************************
	 ***********************			INIT L'EDITEUR TINYMCE			************************
	*******************************************************************************************/
	$(function(){
		tinymce.init({
			////	parametrage général
			selector: "textarea[name='description']",			//Selecteur du textarea
			placeholder: "<?= Txt::trad("description") ?>",		//Placeholder du textarea (text par défaut)
			language:"<?= Txt::trad("EDITORLANG") ?>",			//Langue du menu de l'éditeur
			skin: 'tinymce-5',									//ancien skin + clair
			width: "100%",										//Largeur de l'éditeur
			min_height:(<?= $toggleButton==true?150:300 ?>),	//Hauteur par défaut de l'éditeur
			convert_urls: false,								//Urls des liens : ne pas les convertir en "relatives"
			menubar: false,										//Pas de "menubar" en haut de l'éditeur (menu déroulant)
			statusbar: false,									//Pas de "statusbar" en bas de l'éditeur
			allow_script_urls: true,							//Autorise l'ajout de js dans les hrefs ("lightboxOpen()" & co)
			browser_spellcheck: true,							//Correcteur orthographique du browser activé
			contextmenu: false,									//Désactive le menu contextuel de l'éditeur : cf. "browser_spellcheck" ci-dessus
			images_upload_handler: imageUploadHandler,			//Gestion du Drag/Drop d'image
			font_size_formats:"11px 13px 16px 20px 24px 28px 32px",	//Liste des "fontsize" : cf. "content_style" ci-dessus pour le "font-size" par défaut
			content_style:"body {margin:10px;font-size:13px; font-family:Arial,Helvetica,sans-serif;}  p {margin:3px;}  .attachedFileTag {max-width:100%!important;}  .mce-content-body[data-mce-placeholder]:not(.mce-visualblocks)::before {font-weight:normal; padding-left:5px; color:#aaa;}",//Style du texte dans l'éditeur : idem "body" dans "common.css" + Style du placeholder de l'éditeur
			////	Charge les plugins et options de la "toolbar" (autres plugins dispos : code print preview hr anchor pagebreak wordcount fullscreen insertdatetime)
			plugins: "autoresize lists advlist link autolink image charmap emoticons visualchars media nonbreaking table <?= $editorCode ?>",
			toolbar1: (isMobile()  ?  "undo redo | emoticons addMediaFileButton | editorDraft"	:  "undo redo | copy paste removeformat | table charmap <?= $editorCode ?> media emoticons link addMediaFileButton | editorDraft"),
			toolbar2: (isMobile()  ?  "fontsize | bold underline forecolor bullist"				:  "bold italic underline strikethrough forecolor | fontsize | alignleft aligncenter alignright alignjustify | bullist numlist"),
			////	Chargement de l'éditeur : parametrages spécifiques
			setup: function(editor){
				////	Met le focus dans l'éditeur (verif si ya deja un focus ou on est sur mobile)
				editor.on("init",function(){
					if($("input:focus").length==0 && isTouchDevice()==false)  {editor.focus();}
				});
				////	Modif le contenu de l'éditeur
				editor.on("change keyup",function(){
					lightboxResize();					//Resize si besoin le lightbox en fonction du contenu (après "autoresize" : cf. plugin ci-dessus)
					windowParent.confirmCloseForm=true;	//Marqueur pour la confirmation de sortie de formulaire ("windowParent" pour les lightbox)
				});
				////	Récup du brouillon/draft (cf. bouton de la "toolbar1")
				var editorDraftHtml="<?= addslashes(str_replace(["\n","\r"],"",$editorDraft)) ?>";//Pas de \n\r car pb avec les images en pièces jointe
				if(editorDraftHtml.length>0){
					editor.ui.registry.addButton("editorDraft",{
						text: "<?= Txt::trad("editorDraft") ?>",
						icon: "restore-draft",
						tooltip: "<?= Txt::trad("editorDraftConfirm") ?>",
						onSetup:function(_){  $(".tox-tbtn__select-label").pulsate(10);  },																	//Pulsate le bouton "editorDraft" à l'affichage du menu
						onAction:function(_){  if(confirm("<?= Txt::trad("editorDraftConfirm") ?> ?")) {editor.selection.setContent(editorDraftHtml);}  }	//"editorDraft" sélectionné : ajoute le brouillon/draft au texte courant
					});
				}
				////	Fichier joint : Affiche une image/video/mp3 dans l'éditeur (cf. "toolbar1")
				if($(".attachedFileInput").exist()){
					editor.ui.registry.addButton("addMediaFileButton",{
						icon: "image",
						tooltip: "<?= Txt::trad("editorFileInsert") ?>",
						onAction:function(_){
							$("[for='objMenuAttachedFile']").trigger("click");																		//Affiche le menu des fichiers joints (cf. "VueObjAttachedFile.php")
							var fileInput=$(".attachedFileInput").filter(function(){ return this.value==""; }).first();								//Sélectionne le premier "attachedFileInput" disponible
							fileInput.trigger("click").on("change",function(){  attachedFileInsert(this.id.replace('attachedFileInput',''));  });	//Récupere un fichier (trigger "click"+"change")  &&  Affiche le fichier dans l'editeur (ex d'Id: "attachedFileInput55" => 55)
						}
					});
				}
			},
		});
	});

	/*******************************************************************************************
	 *	VERIFIE SI LE CONTENU DE L'EDITEUR EST VIDE
	*******************************************************************************************/
	function isEmptyEditor()
	{
		var content=tinymce.activeEditor.getContent();	//Récupère le contenu de l'éditeur
		content=content.replace(/<p>&nbsp;<\/p>/g,"");	//Enlève les paragraphes vides
		return ($.trim(content).length==0);				//Renvoie "true" si l'éditeur est vide
	}

	/**************************************************************************************************************
	 *	RENVOI LE CONTENU DE L'EDITEUR (cf. "editorDraft" ci-dessus && "messengerUpdate()" du "VueMessenger.php")
	**************************************************************************************************************/
	function editorContent()
	{
		//Renvoie le contenu s'il n'ya pas d'image/fichier temporaire (format "Blob" trop lourd pour "ap_userLivecouter.editorDraft")
		var content=tinymce.activeEditor.getContent();
		if(/attachedFileTagTmp/i.test(content)==false)  {return content;}
	}

	/**************************************************************************************************************
	 *	FICHIER JOINT : AFFICHE UNE IMAGE/VIDEO/MP3 DANS L'EDITEUR (cf. "VueObjAttachedFile.php")
	*	"fileId" = Id temporaire (celui de l'input File mais sans "attachedFileInput")  ||  Id du fichier en Bdd
	**************************************************************************************************************/
	function attachedFileInsert(fileId, fileSrc)
	{
		////	Verif si c'est un nouveau fichier
		var isNewFile=(typeof fileSrc==="undefined");
		////	Nouveau fichier : récupère le path temporaire de l'Input File (ex: "C:\FakePath\image.jpg" sur Chromium)
		if(isNewFile==true)  {fileSrc=$("#attachedFileInput"+fileId).val();}
		////	Controle s'il s'agit d'un fichier autorisé (image/vidéo/mp3)
		var extFileCurrent=extension(fileSrc);
		if($.inArray(extFileCurrent,extFileForEditor)==-1){
			notify("<?= Txt::trad("editorFileInsertNotif") ?>");			//"Merci de sélectionner une image [...]"
			if(isNewFile==true)  {$("#attachedFileInput"+fileId).val("");}	//Réinit l'input File
			return false;
		}
		////	Créé le Tag html du fichier (cf. ".attachedFileTag" du "common.css")
		var fileTagId= (isNewFile==true)  ?  'attachedFileTagTmp'+fileId  :  'attachedFileTag'+fileId;	//Id de la balise html (temporaire ou final)
		var fileTagSrc=(isNewFile==true)  ?  'fileSrcTmp'+fileId  :  fileSrc;							//Src du fichier : Temporaire si nouveau fichier ||  Final si le fichier est dejà sur le serveur
		if($.inArray(extFileCurrent,extFileImage)>=0)		{var fileTag='<a id="'+fileTagId+'" href="'+fileTagSrc+'" data-fancybox="images"><img src="'+fileTagSrc+'" class="attachedFileTag"></a>';}//"data-fancybox" pour l'affichage dans une lightBox
		else if($.inArray(extFileCurrent,extFileVideo)>=0)	{var fileTag='<video id="'+fileTagId+'" controls class="attachedFileTag"><source src="'+fileTagSrc+'" type="video/'+extFileCurrent+'">HTML5 required</video>';}
		else if($.inArray(extFileCurrent,extFileMp3)>=0)	{var fileTag='<audio id="'+fileTagId+'" controls class="attachedFileTag"><source src="'+fileTagSrc+'" type="audio/mp3">HTML5 required</audio>';}
		fileTag="<p>&nbsp;</p>"+fileTag+"<p>&nbsp;</p>";
		////	Affiche la nouvelle image dans l'éditeur (remplace "fileSrcTmp" par l'image)
		if(isNewFile==true && $.inArray(extFileCurrent,extFileImage)>=0){			//Verif s'il s'agit d'une nouvelle image
			var reader=new FileReader();											//Créé un nouveau "FileReader" pour récupérer le contenu de l'image ( "blob")
			reader.readAsDataURL($("#attachedFileInput"+fileId).prop("files")[0]);	//Récupère l'image depuis l'input File : données binaires encodées en base64
			reader.onload=function(){												//Charge la nouvelle image :
				fileTag=fileTag.replace(new RegExp(fileTagSrc,"g"),reader.result);	//Remplace  'fileSrcTmp'+fileId  par le contenu binaire de l'image (RegExp necessaire pour le "replace()" d'une variable)
				tinymce.activeEditor.selection.setContent(fileTag);					//Affiche l'image dans l'éditeur : "imageUploadHandler()" est alors executé
			};
		}
		////	Affiche la balise html dans l'éditeur : Fichier déjà sur le serveur  ||  Nouveau fichier vidéo/mp3 avec un "fileSrcTmp"
		else{
			tinymce.activeEditor.selection.setContent(fileTag);
		}
		////	Resize le lightbox
		lightboxResize();
	}

	/****************************************************************************************************************************************************
	 *	FICHIER JOINT : REMPLACE LE "SRC" DES IMAGES TEMPORAIRES (FORMAT "BLOB") PAR UN ID TEMPORAIRE (cf. "VueObjEditMenuSubmit.php" & "ModMail/index.php") 
	****************************************************************************************************************************************************/
	function attachedFileSrcReplace()
	{
		if(typeof tinymce!="undefined"){
			$(tinymce.activeEditor.getBody()).find("[id*=attachedFileTagTmp]").each(function(){		//Accède à Tinymce via "getBody()" puis parcourt les balises avec un Id "attachedFileTagTmp"
				var fileSrcTmp=this.id.replace("attachedFileTagTmp","fileSrcTmp");					//Recréé le  'fileSrcTmp'+fileId   (ex: "attachedFileTagTmp55" devient "fileSrcTmp55")
				$(this).attr("href",fileSrcTmp);													//Remplace le "href" des balises <a> d'images
				$(this).find("*").attr("src",fileSrcTmp);											//Puis remplace le "src" des balises <img> et <source>
			});
		}
	}

	/****************************************************************************************************************************************************
	 *	FICHIER JOINT : COPIE-COLLE UNE IMAGE/VIDEO/MP3 DANS L'ÉDITEUR
	*	Note:  le "tinymce.init" ci-dessus ne doit être lancé qu'après le chargement complet de la page et du "imageUploadHandler"
	****************************************************************************************************************************************************/
	const imageUploadHandler = (blobInfo, progress) => new Promise((resolve, reject) => {
		////	Vérifie qu'il s'agit d'une image copiée/collée (et pas ajouté via le bouton "addMediaFileButton", qui ne renvoie pas de "FileName")
		var fileName=blobInfo.blob().name;
		if(fileName!=undefined && $.inArray(extension(fileName),extFileImage)>=0)
		{
			////	Affiche un nouvel "attachedFileInput" et y ajoute les datas de l'image
			$("[for='objMenuAttachedFile']").trigger("click");															//Affiche le menu des fichiers joints (cf. "VueObjAttachedFile.php")
			var fileInput=$(".attachedFileInput").filter(function(){ return this.value==""; }).first();					//Sélectionne le premier "attachedFileInput" disponible
			const tmpFile=new File([blobInfo.blob()], fileName, {type:blobInfo.blob().type, lastModified:new Date()});	//Créé un objet Javascript "File" dans lequel on ajoute le contenu du fichier
			const dataTransfer=new DataTransfer();																		//Créé un objet Javascript "DataTransfer"
			dataTransfer.items.add(tmpFile);																			//Ajoute l'objet "File" dans l'objet "DataTransfer"
			fileInput[0].files=dataTransfer.files;																		//Ajoute l'objet "DataTransfer" dans l'input File
			////	Recréé la balise <img> via "attachedFileInsert()"
			$(tinymce.activeEditor.getBody()).find("[src*='"+blobInfo.blobUri()+"']").remove();							//Supprime la balise par défaut de l'image copiée/collée (sélectionne en fonction de son "src")
			attachedFileInsert(fileInput[0].id.replace('attachedFileInput',''));										//Recréé la balise de l'image formaté avec "attachedFileTagTmp" and Co (ex d'Id: "attachedFileInput55" => 55)
		}
	});
	</script>
<?php } ?>


<style>
.descriptionToggle		{margin-bottom:20px; display:inline-block; line-height:35px; margin-left:15px;}							/*Label pour afficher/masquer la description d'un objet. Height identique aux inputs text*/
.descriptionTextarea	{margin-bottom:20px; <?= ($toggleButton==true && empty($curObj->description)) ?"display:none;":null ?>}	/*Textarea masqué par défaut ?*/
/*MOBILE FANCYBOX : 440px*/
@media screen and (max-width:440px){
	.descriptionToggle	{display:block; margin:0px; margin-top:20px; margin-bottom:10px!important;}
}
</style>


<?php if($toggleButton==true){ ?>
	<div class="descriptionToggle" onclick="$('.descriptionTextarea').slideToggle()"><img src="app/img/description.png"> <label><?= Txt::trad("description") ?> <img src="app/img/arrowBottom.png"></label></div>
<?php } ?>
<div class="descriptionTextarea"><textarea name="description"><?= $curObj->description ?></textarea></div>