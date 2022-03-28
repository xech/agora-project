<script type="text/javascript" src="app/js/tinymce/tinymce.min.js"></script>
<script>
/*******************************************************************************************
 *	INITIALISE L'EDITEUR TINYMCE
 *******************************************************************************************/
tinymce.init({
	////	parametrage général
	selector: "textarea[name='<?= $fieldName ?>']",			//Selecteur du textarea
	language:"<?= Txt::trad("HTML_EDITOR") ?>",				//Langue du menu de l'éditeur
	width: "100%",											//Largeur de l'éditeur
	min_height:(isMainPage==true?350:250),					//Hauteur par défaut de l'éditeur (cf. "lightboxResize()")
	menubar: false,											//Pas de "menubar" en haut de l'éditeur (menu déroulant)
	statusbar: false,										//Pas de "statusbar" en bas de l'éditeur
	allow_script_urls: true,								//Autorise l'ajout de js dans les hrefs (cf. "lightboxOpen()")
	browser_spellcheck: true,								//Correcteur orthographique du browser activé
	contextmenu: false,										//Désactive le menu contextuel de l'éditeur : cf. "browser_spellcheck" ci-dessus
	fontsize_formats: "11px 13px 16px 20px 24px 28px 32px",	//Liste des "fontsize" : cf. "content_style" ci-dessus pour le "font-size" par défaut
	content_style:"body{margin:10px;font-size:13px;font-family:Arial,Helvetica,sans-serif;}  p{margin:0px;padding:3px;}  .attachedFileTag{max-width:100%;}",//Style dans l'éditeur : idem "app/css/common.css" !
	////	Charge les plugins et options de la "toolbar" (autres plugins dispos : code print preview hr anchor pagebreak wordcount fullscreen insertdatetime)
	plugins: ["autoresize lists advlist link autolink image charmap emoticons visualchars media nonbreaking table paste"],
	toolbar1: (isMobile()  ?  "undo redo | emoticons attachedFileImg | editorDraft"  :  "undo redo | copy paste removeformat | table charmap media emoticons link attachedFileImg | editorDraft"),//Option "code" pour modifier le code HTML
	toolbar2: (isMobile()  ?  "fontsizeselect | bold underline forecolor bullist"  :  "bold italic underline strikethrough forecolor | fontsizeselect | alignleft aligncenter alignright alignjustify | bullist numlist"),
	////	Chargement de l'éditeur : parametrages spécifiques
	setup: function(editor){
		//// Init : Focus l'éditeur (sauf en responsive pour pas afficher le clavier virtuel ..ou si ya deja un focus)
		editor.on("init",function(){
			if($("input:focus").length==0 && !isMobile())  {editor.focus();}
		});
		//// Modif le contenu de l'éditeur
		editor.on("change keyup",function(){
			lightboxResize();					//Resize si besoin le lightbox en fonction du contenu (après "autoresize" : cf. plugin ci-dessus)
			windowParent.confirmCloseForm=true;	//Marqueur pour demander confirmation de sortie de formulaire ("windowParent" si on est dans une lightbox)
		});
		//// Bouton de récupération du brouillon/draft (cf. "toolbar1" ci-dessus et enregistrements dans "ap_userLivecounter")
		var editorDraftHtml="<?= addslashes(str_replace(["\n","\r"],null,$editorDraft)) ?>";//Pas de \n\r car pb avec les images en pièces jointe
		if(editorDraftHtml.length>0)
		{
			editor.ui.registry.addButton("editorDraft",{
				icon: "restore-draft",
				text: "<span id='editorDraftLabel'><?= Txt::trad("editorDraft") ?></span>",
				tooltip: "<?= Txt::trad("editorDraftConfirm") ?>",
				onSetup:function(_){  $("#editorDraftLabel").pulsate(7);  },																		//Pulsate le bouton "editorDraft" à l'affichage du menu
				onAction:function(_){  if(confirm("<?= Txt::trad("editorDraftConfirm") ?> ?")) {editor.selection.setContent(editorDraftHtml);}  }	//"editorDraft" sélectionné : ajoute le brouillon/draft au texte courant
			});
		}
		//// Bouton d'insertion d'image (cf. "toolbar1" ci-dessus)
		if($(".attachedFileInput").exist())
		{
			editor.ui.registry.addButton("attachedFileImg",{
				icon: "image",
				tooltip: "<?= Txt::trad("editorFileInsert") ?>",
				onAction:function(_){
					$("[for='objMenuAttachedFile']").click();																//"VueObjMenuEdit.php" : affiche le menu/onglet des fichiers joints
					var fileInput=$(".attachedFileInput").filter(function(){ return !this.value; }).first();				//Sélectionne le premier input "attachedFile" disponible
					fileInput.click().change(function(){ attachedFileInsert(this.id.replace("attachedFileInput","")); });	//Récupère le fichier (trigger "click") puis ajoute l'image dans le texte via "attachedFileInsert()" avec en parametre le numéro de l'input (exple : "attachedFileInput5". Voir "VueObjAttacheedFile.php")		
				}
			});
		}
	},
});

/*******************************************************************************************
 *	VERIFIE SI LE CONTENU DE L'EDITEUR EST VIDE
 *******************************************************************************************/
function isEmptyEditor()
{
	var content=tinymce.activeEditor.getContent();	//Récupère le contenu de l'éditeur
	content=content.replace(/<p>&nbsp;<\/p>/g,"");	//Remplace les paragraphes <p> vides
	return ($.trim(content).length==0);				//Renvoie "true" si l'éditeur est vide
}

/*******************************************************************************************
 *	RENVOI LE CONTENU DE L'EDITEUR (CF. "VueMessenger.php")
 *******************************************************************************************/
function editorContent()
{
	var content=tinymce.activeEditor.getContent();						//Récupère le contenu de l'éditeur
	if(/attachedFileTagInput/i.test(content)==false)  {return content;}	//Renvoie le contenu s'il contient pas d'image temporaire (car le format Blob d'une image est trop lourd pour l'envoyer en ajax dans l'editorDraft)
}

/*******************************************************************************************
 *	FICHIER JOINT : INSERT UN TAG HTML D'IMAGE / VIDEO / MP3 DANS L'EDITEUR
 *******************************************************************************************/
function attachedFileInsert(fileId, displayUrl)
{
	//// Verif si c'est un nouveau fichier (cf. input "file)  &&  Id de la balise html du fichier  &&  Path du fichier
	var isNewFile=(typeof displayUrl=="undefined");
	if(isNewFile==true)	{var tagId="attachedFileTagInput"+fileId;  var displayUrl=$("#attachedFileInput"+fileId).val();}	//Id du tag html temporaire en fonction de l'input + "FakePath" de l'input
	else				{var tagId="attachedFileTag"+fileId;}																//Id du tag html en fonction de l'_id réel du fichier
	var fileExt=extension(displayUrl);
	//// Controle s'il s'agit bien d'une image/vidéo/mp3
	if($.inArray(fileExt,extFileInsert)==-1){
		notify("<?= Txt::trad("editorFileInsertNotif") ?>");//"Merci de sélectionner une image .jpeg"
		if(isNewFile==true)  {$("#attachedFileInput"+fileId).val("");}//réinit l'input
		return false;
	}
	//// Créé le Tag html du fichier (cf. ".attachedFileTag" du "common.css")
	if($.inArray(fileExt,extImage)>=0)		{fileTag='<a href="SRCINPUT" data-fancybox="images" id="'+tagId+'"><img src="SRCINPUT" class="attachedFileTag"></a>';}//"data-fancybox" pour afficher dans une lightBox
	else if($.inArray(fileExt,extVideo)>=0)	{fileTag='<video controls controlsList="nodownload" id="'+tagId+'" class="attachedFileTag"><source src="SRCINPUT" type="video/'+fileExt+'">HTML5 required</video>';}
	else if($.inArray(fileExt,extMp3)>=0)	{fileTag='<audio controls controlsList="nodownload" id="'+tagId+'" class="attachedFileTag"><source src="SRCINPUT" type="audio/mp3">HTML5 required</audio>';}
	fileTag='<p>&nbsp;</p>'+fileTag;//retour à la ligne
	//// Nouvelle image : affiche l'image dans l'éditeur
	if(isNewFile==true && $.inArray(fileExt,extImage)>=0){																	//Verif l'extension du fichier (cf. "extImage" de "VueObjAttachedFile.php")
		var reader=new FileReader();																						//Lance le "FileReader" Javascript pour récupérer le contenu de l'image
		reader.readAsDataURL($("#attachedFileInput"+fileId).prop("files")[0]);												//Récupère l'image depuis le "FakePath" de l'input, sous forme d'URL/Blob
		reader.onload=function(){ tinymce.activeEditor.selection.setContent(fileTag.replace(/SRCINPUT/g,reader.result)); };	//Affiche l'image dans l'éditeur ("g" remplace tous les "SRCINPUT")
	}
	//// Nouveau fichier vidéo/mp3 OU Fichier joint déjà ajouté : affiche directement le tag html sans lire l'input
	else{
		fileSrc=(isNewFile==true)  ?  "SRCINPUT"+fileId  :  displayUrl;					//Nouveau fichier : ajoute le fileId au "SRCINPUT" (cf. "MdlObject->attachedFileAdd()")  OU  Remplace "SRCINPUT" par le path réel
		tinymce.activeEditor.selection.setContent(fileTag.replace(/SRCINPUT/g,fileSrc));//Remplace le "SRCINPUT" ("g" remplace tous les "SRCINPUT")
	}
	////	Resize le lightbox
	lightboxResize();
}

/********************************************************************************************************************************************************
 *	FICHIERS JOINTS TEMPORAIRES D'IMAGES : REMPLACE LE "SRC" AU FORMAT BLOB PAR UN ID TEMPORAIRE (cf. "VueObjMenuEdit.php" & "ModMail/index.php") 
 ********************************************************************************************************************************************************/
function attachedFileReplaceSRCINPUT()
{
	if(typeof tinymce!="undefined"){
		$(tinymce.activeEditor.getBody()).find("a[id*=attachedFileTagInput]").each(function(){	//Accède à Tinymce via "getBody()" puis parcourt les tags <a> ayant un "attachedFileTagInput" (cf. "attachedFileInsert()" ci-dessus)
			srcInput=this.id.replace("attachedFileTagInput","SRCINPUT");						//Init le href/src temporaire à partir de l'id du tag (exple: "attachedFileTagInput555" devient "SRCINPUT555")
			$(this).attr("href",srcInput);														//Remplace le "href" du tag <a> (tag <a> du fancybox des images)
			$(tinymce.activeEditor.getBody()).find("#"+this.id+" img").attr("src",srcInput);	//Accède à Tinymce via "getBody()" (encore une fois!) puis remplace le "src" du tag <img>
		});
	}
}
</script>