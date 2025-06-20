<?php if($curObj::descriptionEditor==true){ ?>
	<script type="text/javascript" src="app/js/tinymce_7.9.1/tinymce.min.js"></script>
	<script>
	ready(function(){
		/******************************************************************************************
		*	INIT L'EDITEUR TINYMCE POUR LA DESCRIPTION  (cf "$curObj::descriptionEditor")
		*******************************************************************************************/
		tinymce.init({
			////	parametrage général
			selector: "textarea[name='description']",										//Selecteur du textarea
			license_key: "gpl",																//Licence Key
			placeholder: "<?= Txt::trad("description") ?>",									//Placeholder du textarea (text par défaut)
			language:"<?= Txt::trad("EDITORLANG") ?>",										//Langue du menu de l'éditeur
			skin: "<?= Ctrl::$agora->skin=="black" ? "tinymce-5-dark" : "tinymce-5" ?>",	//Editeur blanc/noir
			content_css: "<?= Ctrl::$agora->skin=="black" ? "dark": "default" ?>",			//Skin du contenu
			width: "100%",																	//Largeur de l'éditeur
			min_height:(<?= $toggleButton==true?150:300 ?>),								//Hauteur par défaut de l'éditeur
			convert_urls: false,															//Urls des liens : ne pas les convertir en "relatives"
			menubar: false,																	//Pas de "menubar" en haut de l'éditeur (menu déroulant)
			statusbar: false,																//Pas de "statusbar" en bas de l'éditeur
			allow_script_urls: true,														//Autorise l'ajout de js dans les hrefs ("lightboxOpen()" & co)
			browser_spellcheck: true,														//Correcteur orthographique du browser activé
			contextmenu: false,																//Désactive le menu contextuel de l'éditeur : cf. "browser_spellcheck" ci-dessus
			images_upload_handler: imageUploadHandler,										//Gestion du Drag/Drop d'image
			content_css:"app/Common/js-css-<?= Req::appVersion() ?>/editor.css",			//Style du texte dans l'éditeur
			font_size_formats:"0.8em 1em 1.2em 1.4em 1.6em 1.8em 2em 2.2em 2.4em 2.6em",	//Liste des "font-size" disponibles
			////	Plugins +  Menubar + Toolbar
			plugins: "preview searchreplace autolink link image media table charmap advlist lists wordcount charmap emoticons autoresize anchor <?= Ctrl::$curUser->isGeneralAdmin() ? "code" : null ?>",
			menubar: "<?= Ctrl::$curUser->isUser() ? "file edit view insert format tools table" : null ?>",//Barre de menu principale
			toolbar: "undo redo bold italic underline forecolor fontsize align bullist numlist outdent indent addMediaFileButton | editorDraft",
			toolbar_mode: "sliding",
			////	Chargement de l'éditeur : parametrages spécifiques
			setup: function(editor){
				////	Met le focus dans l'éditeur (sauf si deja un focus ou si "isTouchDevice()")
				editor.on("init",function(){
					if($("input:focus").length==0 && isTouchDevice()==false)  {editor.focus();}
				});
				////	Modif le contenu de l'éditeur
				editor.on("change keyup",function(){
					lightboxResize();					//Resize si besoin le lightbox en fonction du contenu (après "autoresize" : cf. plugin ci-dessus)
					window.top.confirmCloseForm=true;	//Marqueur pour la confirmation de sortie de formulaire
				});
				////	Récup du brouillon/draft (cf. bouton de la "toolbar1")
				var editorDraftHtml="<?= addslashes(str_replace(["\n","\r"],"",$editorDraft)) ?>";//Pas de \n\r car pb avec les images en pièces jointe
				if(editorDraftHtml.length>0){
					editor.ui.registry.addButton("editorDraft",{
						text: "<?= Txt::trad("editorDraft") ?>",
						icon: "restore-draft",
						tooltip: "<?= Txt::trad("editorDraftConfirm") ?>",
						onSetup:function(_){  $("button[data-mce-name='editordraft']").pulsate(3);  },	//Pulsate le bouton "editorDraft" (brouillon) à l'affichage du menu
						onAction:async function(_){														//Click sur "editorDraft" : ajoute le dernier draft à l'éditeur
							if(await confirmAlt("<?= Txt::trad("editorDraftConfirm") ?>"))  {editor.selection.setContent(editorDraftHtml);}
						}
					});
				}
				////	Affiche une image/video/mp3 (fichier joint) dans l'éditeur : cf. "toolbar1" && "VueObjAttachedFileEdit.php"
				if($(".attachedFileInput").exist()){
					editor.ui.registry.addButton("addMediaFileButton",{
						icon: "image",
						tooltip: "<?= Txt::trad("editorFileInsert") ?>",
						onAction:function(_){
							$("[for='objMenuAttachedFile']").trigger("click");											//Affiche le menu des fichiers joints
							let fileInput=$(".attachedFileInput").filter(function(){ return this.value==""; }).first();	//Sélectionne le premier input "attachedFileInput" disponible (empty)
							fileInput.trigger("click").on("change",function(){ attachedFileInsert(this.id); });			//Récupere un fichier (trigger "click"+"change") et l'affiche dans l'editeur
						}
					});
				}
			},
		});
	});

	/********************************************************************************************************
	 *	VERIFIE SI LE CONTENU DE L'EDITEUR EST VIDE
	********************************************************************************************/
	function isEmptyEditor()
	{
		var content=tinymce.activeEditor.getContent();	//Récupère le contenu de l'éditeur
		content=content.replace(/<p>&nbsp;<\/p>/g,"");	//Enlève les paragraphes vides
		return ($.trim(content).length==0);				//Renvoie "true" si l'éditeur est vide
	}

	/********************************************************************************************************
	 *	RENVOI LE CONTENU DE L'EDITEUR  (cf. "editorDraft")
	********************************************************************************************/
	function editorContent()
	{
		var content=tinymce.activeEditor.getContent();
		if(/attachedFileTagTmp/i.test(content)==false)  {return content;}//Verif s'il ya un fichier temporaire (format "Blob" trop lourd)
	}

	/********************************************************************************************************
	 *	VERIFIE SI LE FICHIER PEUT ETRE INSEREES DANS L'EDITEUR : IMG / VIDEO / MP3
	********************************************************************************************/
	function isType(fileType, fileName)
	{
		if(fileType=="editorInsert")		{var fileTypes=["<?= implode('","',File::fileTypes("editorInsert")) ?>"];}	//Images/vidéos/Mp3
		else if(fileType=="imageBrowser")	{var fileTypes=["<?= implode('","',File::fileTypes("imageBrowser")) ?>"];}	//Images
		else if(fileType=="videoBrowser")	{var fileTypes=["<?= implode('","',File::fileTypes("videoBrowser")) ?>"];}	//Vidéos
		else if(fileType=="mp3")			{var fileTypes=["<?= implode('","',File::fileTypes("mp3")) ?>"];}			//Mp3
		else								{var fileTypes=[];}
		return fileTypes.includes(extension(fileName));
	}

	/****************************************************************************************************************************************************
	 *	FICHIER JOINT : INSERE UNE IMAGE/VIDEO/MP3 DANS L'EDITEUR  (cf. "VueObjAttachedFileEdit.php")
	*****************************************************************************************************************************************************/
	function attachedFileInsert(fileId, displayUrl)
	{
		fileId=fileId.toString().replace("attachedFileInput","");					//Id de l'input ou du fichier en Bdd
		let isNewFile=(typeof displayUrl==="undefined");							//Verif si c'est un nouveau fichier
		if(isNewFile==true)  {displayUrl=$("#attachedFileInput"+fileId).val();}		//Nouveau fichier : récupère le path temporaire de l'Input (ex: "C:\FakePath\image.jpg")
		if(isType("editorInsert",displayUrl)==false){								//Verif si c'est une image/vidéo/mp3
			notify("<?= Txt::trad("editorFileInsertNotif") ?>");					//Notif "Merci de sélectionner une image.."
			if(isNewFile==true)  {$("#attachedFileInput"+fileId).val("");}			//Réinit l'input
			return false;
		}
		////	Créé le Tag html du fichier (cf. ".attachedFileTag" du "app.css")
		let tagId= (isNewFile==true)  ?  "attachedFileTagTmp"+fileId  :  "attachedFileTag"+fileId;	//Id de la balise : Temporaire si nouveau fichier ||  Final si fichier dejà sur le serveur
		let tagSrc=(isNewFile==true)  ?  "attachedFileSrcTmp"+fileId  :  displayUrl;				//Src du fichier  : Idem
		if(isType("imageBrowser",displayUrl))		{var fileTag='<img   id="'+tagId+'" class="attachedFileTag" src="'+tagSrc+'" data-fancybox="images">';}
		else if(isType("videoBrowser",displayUrl))	{var fileTag='<video id="'+tagId+'" class="attachedFileTag" controls><source src="'+tagSrc+'" type="video/'+extension(displayUrl)+'">HTML5 Video</video>';}
		else if(isType("mp3",displayUrl))			{var fileTag='<audio id="'+tagId+'" class="attachedFileTag" controls><source src="'+tagSrc+'" type="audio/mpeg">HTML5 Audio</audio>';}
		fileTag="<br><br>"+fileTag+"<br><br>";
		////	Nouvelle image : remplace  src="attachedFileSrcTmpXX"  par le contenu binaire de l'image
		if(isNewFile==true && isType("imageBrowser",displayUrl)){					//Verif si c'est une nouvelle image
			let reader=new FileReader();											//Créé un nouveau "FileReader" pour récupérer l'image au format "blob"
			reader.readAsDataURL($("#attachedFileInput"+fileId).prop("files")[0]);	//Récupère l'image depuis l'input File : données binaires encodées en base64
			reader.onload=function(){												//Charge la nouvelle image
				fileTag=fileTag.replace(new RegExp(tagSrc,"g"),reader.result);		//Remplace  'attachedFileSrcTmp'+fileId  par le contenu binaire de l'image (toujours via RegExp!)
				tinymce.activeEditor.selection.setContent(fileTag);					//Affiche l'image dans l'éditeur : "imageUploadHandler()" est alors executé
			};
		}
		////	Affiche un fichier déjà enregistré  ||  Nouveau fichier vidéo/mp3
		else  {tinymce.activeEditor.selection.setContent(fileTag);}
	}

	/****************************************************************************************************************************************************
	 *	FICHIER JOINT : REMPLACE LE "SRC" TEMPORAIRE AU FORMAT "BLOB", PAR UN ID TEMPORAIRE MODIFIÉ ENSUITE VIA "attachedFileAdd()"
	*****************************************************************************************************************************************************/
	$("form").on("submit",function(){
		$(tinymce.activeEditor.getBody()).find("[id*=attachedFileTagTmp]").each(function(){	//Récupère le contenu de l'éditeur : parcourt les balises avec un Id "attachedFileTagTmp"
			let fileSrcTmp=this.id.replace("attachedFileTagTmp","attachedFileSrcTmp");		//Init le src du fichier temporaire, à partir de son Id  (ex: "attachedFileTagTmpXX" -> "attachedFileSrcTmpXX")
			$(this).attr("src",fileSrcTmp);													//Remplace le "src" des <img>
			$(this).find("source").attr("src",fileSrcTmp);									//Remplace le "src" des <source> video/audio
		});
	});

	/****************************************************************************************************************************************************
	 *	FICHIER JOINT : AJOUTE VIA DRAG-DROP DANS L'ÉDITEUR  (Tjs charger avant "tinymce.init")
	*****************************************************************************************************************************************************/
	const imageUploadHandler = (blobInfo, progress) => new Promise((resolve, reject) => {
		let fileName=blobInfo.blob().name;																				//Récupère le fileName du fichier "dropped"
		if(fileName!=undefined && isType("imageBrowser",fileName)){														//Affiche un nouvel "attachedFileInput" et y ajoute les datas de l'image
			$("[for='objMenuAttachedFile']").trigger("click");															//Affiche le menu des fichiers joints (cf. "VueObjAttachedFileEdit.php")
			var fileInput=$(".attachedFileInput").filter(function(){ return this.value==""; }).first();					//Sélectionne le premier "attachedFileInput" disponible
			const tmpFile=new File([blobInfo.blob()], fileName, {type:blobInfo.blob().type, lastModified:new Date()});	//Créé un objet Javascript "File" dans lequel on ajoute le contenu du fichier
			const dataTransfer=new DataTransfer();																		//Créé un objet Javascript "DataTransfer"
			dataTransfer.items.add(tmpFile);																			//Ajoute l'objet "File" dans l'objet "DataTransfer"
			fileInput[0].files=dataTransfer.files;																		//Ajoute l'objet "DataTransfer" dans l'input File
			////	Recréé la balise <img>
			$(tinymce.activeEditor.getBody()).find("[src*='"+blobInfo.blobUri()+"']").remove();							//Supprime la balise par défaut de l'image copiée/collée (sélectionne en fonction de son "src")
			attachedFileInsert(fileInput[0].id);																		//Recréé la balise de l'image formaté avec "attachedFileTagTmp" and Co (ex d'Id: "attachedFileInput55" => 55)
		}
	});
	</script>
<?php } ?>


<style>
.descriptionToggle		{margin-bottom:20px; display:inline-block; line-height:35px; margin-left:15px;}							/*Label pour afficher/masquer la description d'un objet. Height identique aux inputs text*/
.descriptionTextarea	{margin-bottom:20px; <?= ($toggleButton==true && empty($curObj->description)) ?"display:none;":null ?>}	/*Textarea masqué par défaut ?*/
.tox-promotion			{display:none;}/*Masque le bouton "Upgrade !"*/

/*RESPONSIVE SMARTPHONE*/
@media screen and (max-width:490px){
	.descriptionToggle	{display:block; margin:0px; margin-top:20px; margin-bottom:10px!important;}
}
</style>


<?php if($toggleButton==true){ ?>
	<div class="descriptionToggle" onclick="$('.descriptionTextarea').slideToggle()"><img src="app/img/description.png"> <label><?= Txt::trad("description") ?> <img src="app/img/arrowBottom.png"></label></div>
<?php } ?>
<div class="descriptionTextarea"><textarea name="description"><?= $curObj->description ?></textarea></div>