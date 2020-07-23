<script type="text/javascript" src="app/js/tinymce/tinymce.min.js"></script>

<style>
/*STYLE TINYMCE*/
.mce-panel						{border-radius:5px;}/*bordures top-left et top-right du menu*/
.mce-btn button					{box-shadow:none;}/*boutons du menu*/
.mce-toolbar:first-child		{border-bottom:1px solid #ddd;}/*Séparation de ligne du menu*/
</style>

<script>
////	Options de la "toolbar" de l'éditeur (Vérifier l'affichage avec une largeur réduide : cf. modTask)
if(isMobile()){
	optionsToolbar1="undo redo bullist emoticons videoCall editorDraft";
	optionsToolbar2="bold italic underline fontsizeselect forecolor alignleft aligncenter ";
}else{
	optionsToolbar1="undo redo | copy paste removeformat | table charmap emoticons media";//Pour modifier le code HTML il faut l'option "code" (attention aux injections xss!)
	optionsToolbar2="bold italic underline | fontsizeselect | forecolor link | alignleft aligncenter alignjustify | bullist numlist | videoCall | editorDraft";
}
////	Initialise l'editeur TinyMce
tinymce.init({
	////parametrage général
	<?php if(strlen(Txt::trad("HTML_EDITOR")))  {echo 'language:"'.Txt::trad("HTML_EDITOR").'",';} ?>
	width: "100%",
	autoresize_min_height:(isMainPage==true?350:180),//Hauteur par défaut de l'iframe/textarea de l'éditeur (à préciser pour que le "lightboxResize()" au chargement de la page ne le réduise pas à "130px")
	selector: "textarea[name='<?= $fieldName ?>']",//selecteur du textarea
	content_style: "body {font-size:13px;font-family:Arial,Helvetica,Tahoma,Sans-Serif;padding-top:5px;}  p {margin:0px;padding:2px;}",//Style de l'iframe/textarea de l'éditeur (cf. <body> et <p> de "app/css/common.css")
	//forced_root_block: "div",//Désactivé car l'option "content_style" des balises <p> (ci-dessus) est + souple à gérer
	entity_encoding: "raw",//Les caracteres spéciaux ne seront pas enregistrées en html, exceptés certains : &amp; &lt; &gt; &quot;
	menubar: false,//Pas de "menubar" en haut de l'éditeur
	statusbar: false,//Pas de "statusbar" en bas de l'éditeur
	allow_script_urls: true,//Permet l'ajout de js dans les hrefs (exple: "lightboxOpen()")
	browser_spellcheck: true,//Correcteur orthographique du browser activé
	//Charge les plugins et options. Pas de "contextmenu" pour pouvoir afficher copier/coller si "paste" est bloqué par le browser. Autre plugins : print preview hr anchor pagebreak wordcount fullscreen insertdatetime
	plugins: ["autoresize advlist autolink lists link image charmap visualblocks visualchars media nonbreaking table directionality emoticons paste textcolor colorpicker textpattern code"],
	toolbar1: optionsToolbar1,
	toolbar2: optionsToolbar2,
	//Parametrage des plugins
	fontsize_formats: "13px 16px 18px 22px 26px",//Liste des "fontsize" en "px" (cf. "init" ci-dessous et style du "<body>" dans "app/css/common.css")
	media_alt_source: false,//Désactive le champ alternatif de saisie de source dans la boîte de dialogue des médias
	media_poster: false,//Pas d'envoi de fichier dans la boîte de dialogue des médias
	image_description: false,
	image_title: true,
	////Parametrage spécifique
	setup: function(editor){
		//Init l'éditeur
		editor.on("init",function(){
			//Focus sur l'éditeur (sauf s'il y a deja un focus ou qu'on est en responsive, pour pas afficher le clavier virtuel)
			if($("input:focus").length==0 && !isMobile())  {editor.focus();}
			//Pulsate du bouton "restoredraft" du brouillon/draft
			$(".mce-i-restoredraft").effect('pulsate',{times:10},10000);
		});
		//Modif le contenu de l'éditeur
		editor.on("change keyup",function(){
			lightboxResize();//Resize le lightbox (auquel cas) en fonction du contenu de l'éditeur (cf. "autoresize")
			windowParent.confirmCloseForm=true;//Marqueur pour demander confirmation de sortie de formulaire (précise "parent" si l'éditeur se trouve dans une lightbox)
		});
		//Schedule a Video Call
		editor.addButton("videoCall", {
				icon: "preview",
				text: "Video Call",
				tooltip: "Schedule a video call",
				onclick: function(){
					var URL = "https://meet.jit.si/WeDoChange/"+ Math.random().toString(36).substr(2, 10);
					editor.insertContent("<a href='" + URL + "' target='_blank'>Join video call with Jitsi</a>");
				}
			});
	
		//Ajoute si besoin un bouton pour récupérer le brouillon/draft (cf. enregistrements dans "ap_userLivecounter")
		var editorDraftHtml=`<?= $editorDraft ?>`;//Utiliser des `backquotes` pour éviter les erreurs d'affichage avec les simples ou doubles quotes présents dans le texte du brouillon/draft
		if(editorDraftHtml.length>0)	{
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