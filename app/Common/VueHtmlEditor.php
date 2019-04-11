<style>
/*STYLE TINYMCE*/
.mce-panel						{border-radius:5px;}/*bordures top-left et top-right du menu*/
.mce-btn button					{box-shadow:none;}/*boutons du menu*/
.mce-toolbar:first-child		{border-bottom:1px solid #ddd;}/*Séparation de ligne du menu*/
textarea[name='description']	{height:200px;}/*Hauteur par défaut du "textarea" (à préciser sinon le plugin 'autoresize' ajoute un scroll horizontal..)*/
p								{margin:0px; padding:2px;}/*Lignes du contenu*/
</style>


<script type="text/javascript" src="app/js/tinymce/tinymce.min.js"></script>

<script>
////	Options de la "toolbar" de l'éditeur (Vérifier l'affichage avec une largeur réduide : cf. modTask)
if(isMobile()){
	optionsToolbar1="undo redo | alignleft aligncenter | bullist | emoticons";
	optionsToolbar2="bold italic underline forecolor fontsizeselect";
}else{
	optionsToolbar1="undo redo | bullist numlist | paste removeformat | table charmap emoticons media";//Pour modifier le code HTML il faut l'option "code" (attention aux injections xss!)
	optionsToolbar2="bold italic underline | alignleft aligncenter alignjustify | fontsizeselect forecolor link editorDraft";
}
////	Initialise l'editeur TinyMce
tinymce.init({
	////parametrage général
	width: "100%",
	<?php if(Txt::trad("HTML_EDITOR")!="")  {echo "language:'".Txt::trad("HTML_EDITOR")."',";} ?>
	selector: "textarea[name='<?= $fieldName ?>']",//selecteur du textarea
	menubar: false,//Pas de "menubar" en haut de l'éditeur
	statusbar: false,//Pas de "statusbar" en bas de l'éditeur
	allow_script_urls: true,//Permet l'ajout de js dans les hrefs (exple: "lightboxOpen()")
	browser_spellcheck: true,//Correcteur orthographique du browser activé
	entity_encoding: "raw",//Les caracteres spéciaux ne seront pas enregistrées en html, exceptés certains : &amp; &lt; &gt; &quot;
	//forced_root_block: "div",//Remplacement des balises "<p>" inactivé car "content_style" est plus souple
	content_style: "p  {margin:0px; padding:2px;}",//Le style des balises <p> est ainsi maitrisé : cf. 'app/css/common.css'
	//Charge les plugins et options. Pas de "contextmenu" pour pouvoir afficher copier/coller si "paste" est bloqué par le browser. Autre plugins : print preview hr anchor pagebreak wordcount fullscreen insertdatetime
	plugins: ["autoresize advlist autolink lists link image charmap visualblocks visualchars media nonbreaking table directionality emoticons paste textcolor colorpicker textpattern code"],
	toolbar1: optionsToolbar1,
	toolbar2: optionsToolbar2,
	//Parametrage des plugins
	fontsize_formats: "10pt 12pt 14pt 16pt 18pt",//Taille des caractères en "pt"
	media_alt_source: false,//Désactive le champ alternatif de saisie de source dans la boîte de dialogue des médias
	media_poster: false,//Pas d'envoi de fichier dans la boîte de dialogue des médias
	image_description: false,
	image_title: true,
	////Parametrage spécifique
	setup: function(editor){
		//Init l'éditeur
		editor.on("init",function(){
			this.getDoc().body.style.fontSize="10pt";//Taille par défaut
			if($("input:focus").length==0 && !isMobile())  {editor.focus();}//Focus sur l'éditeur (sauf s'il y a deja un focus ou on est en responsive, pour ne pas afficher le clavier virtuel)
			lightboxResize();//Init la hauteur du lightbox si besoin
		});
		//Modif du contenu
		editor.on("change keyup",function(){
			lightboxResize();//Resize le lightbox 
			windowParent.confirmCloseForm=true;//Marqueur pour demander confirmation de sortie de formulaire ("parent" si on se trouve dans une lightbox)
		});
		//Ajoute si besoin un bouton pour récupérer le brouillon/draft (cf. enregistrements dans "ap_userLivecounter")
		var editorDraftHtml=`<?= $editorDraft ?>`;//Utiliser des `backquotes` pour éviter les erreurs d'affichage avec les simples ou doubles quotes du brouillon/draft
		if(editorDraftHtml.length>0)
		{
			editor.addButton("editorDraft",{
				icon: "restoredraft",
				text: "<?= Txt::trad("editorDraft") ?>",
				tooltip: "<?= Txt::trad("editorDraftConfirm") ?>",
				onclick:function(){
					//Si confirmation : Remplace le texte affiché (vide si nouvel objet) par le brouillon/draft
					if(confirm("<?= Txt::trad("editorDraftConfirm") ?> ?"))  {editor.setContent(editorDraftHtml);}
				}
			});
		}
	}
});

////	Contenu de l'editeur est vide ?
function isEmptyEditor()
{
	//Renvoi le contenu de l'éditeur avec l'option "Text" pour pas récupérer d'éventuelle balise vide ("<p>" ou autre)
	var content=tinymce.activeEditor.getContent({format:'text'});
	if($.trim(content).length==0)	{return true;}
}
</script>