<script>
////	INIT
$(function(){
	//Active ou désactive l'envoi de notifications email pour les nouveaux messages d'un sujet
	<?php if($displayForum=="messages"){ ?>
		$("#notifyLastMessage").click(function(){
			$.ajax("?ctrl=forum&action=notifyLastMessage&targetObjId=<?= $curSubject->_targetObjId ?>").done(function(ajaxResult){
				if(ajaxResult=="addUser")	{$("#notifyLastMessage").addClass("vNotifyLastMessage");}
				else						{$("#notifyLastMessage").removeClass("vNotifyLastMessage");}
			});
		});
		//Selectionne "Me notifier par email"?
		if("<?= (int)$curSubject->curUserNotifyLastMessage() ?>"=="1")  {$("#notifyLastMessage").addClass("vNotifyLastMessage");}
	<?php } ?>
});
</script>

<style>
/*General*/
.vNotifyLastMessage			{color:#a00; font-style:italic;}
.objContainer, .objContent	{height:auto!important;}/*surcharge la hauteur "auto" (pas fixe)*/
.objContainer				{padding-right:35px!important;}/*"padding-right" pour les "objMiscMenus" : cf. ci-dessous*/
.objMiscMenus				{width:40px;}/*surcharge pour les "objMiscMenus" : affichés les uns sur les autres*/
.objContent>div				{padding:12px;}/*surcharge pour un contenu plus aéré*/
.vObjDetails				{white-space:nowrap; line-height:20px; text-align:right;}/*Auteur du dernier post*/
.vObjDetails hr				{display:none;}/*affichés en responsive*/

/*Themes*/
.vThemes .objContainer		{padding-right:10px!important;}/*surcharge le "padding-right" des "objMiscMenus"*/
.vThemes .objContent		{height:90px!important;}
.vThemeTitle				{text-transform:uppercase;}
.vThemeDescription			{margin-top:7px; font-weight:normal;}

/*Sujet & Message*/
.vSubjectMessages			{padding-left:25px; user-select:text!important;}/*sélection du text possible*/
.vSubjMessDescription		{font-weight:normal;}
.vSubjNew					{color:<?= Ctrl::$agora->skin=="black"?"#f77":"#933" ?>;}/*plus discret que le sLinkSelect*/
.objContent hr				{background:linear-gradient(to right,<?= Ctrl::$agora->skin=="black"?"#888":"#eee" ?>,transparent)!important; margin-top:6px; margin-bottom:6px;}
.vSubjMessQuote				{position:absolute; top:5px; left:3px; cursor:pointer;}
.vMessageQuoted				{position:relative; display:inline-block; overflow:auto; max-height:100px; margin-top:5px; margin-bottom:10px; padding:8px 35px 8px 35px; background-color:<?= Ctrl::$agora->skin=="black"?"#555":"#f1f1f1" ?>; border-radius:5px; font-style:italic; font-weight:normal;}
.vMessageQuoted [src*='quote']	{position:absolute; top:5px; left:5px; opacity:0.5;}
.vSubjectMessages .vObjDetails	{vertical-align:top!important;}

/*RESPONSIVE*/
@media screen and (max-width:1023px){
	.vThemes .objContent			{height:auto!important;}/*"auto" pour pas faire déborder le contenu*/
	.objContainer					{margin-top:3px;}/*idem ".objBlocks .objContainer"*/
	.objContent, .objContent>div	{display:inline-block; width:100%!important;}/*surcharge*/
	.vObjDetails					{text-align:left; white-space:normal; padding-top:0px!important;}
	.vObjDetails hr					{display:block;}
}
</style>

<div id="pageCenter">
	<div id="pageModuleMenu">
		<div id="pageModMenu" class="miscContainer">
		<?php
		////	MENU DES SUJETS
		if($displayForum=="subjects"){
			if(MdlForumSubject::addRight())  {echo "<div class='menuLine sLink' onclick=\"lightboxOpen('".MdlForumSubject::getUrlNew()."')\"><div class='menuIcon'><img src='app/img/plus.png'></div><div>".Txt::trad("FORUM_addSubject")."</div></div><hr>";}
			echo MdlForumSubject::menuSort()."<div class='menuLine'><div class='menuIcon'><img src='app/img/info.png'></div><div>".$subjectsTotalNb." ".Txt::trad($subjectsTotalNb>1?"FORUM_subjects":"FORUM_subject")."</div></div>";
		}
		////	MENU D'UN SUJET ET SES MESSAGES
		if($displayForum=="messages"){
			if(Ctrl::$curContainer->editContentRight())  {echo "<div class='menuLine sLink' onclick=\"lightboxOpen('".MdlForumMessage::getUrlNew()."')\"><div class='menuIcon'><img src='app/img/plus.png'></div><div>".Txt::trad("FORUM_addMessage")."</div></div><hr>";}
			if(!empty(Ctrl::$curUser->mail))  {echo "<div class='menuLine sLink' id='notifyLastMessage' title=\"".Txt::trad("FORUM_notifyLastPostInfo")."\"><div class='menuIcon'><img src='app/img/mail.png'></div><div>".Txt::trad("FORUM_notifyLastPost")."</div></div>";}
			echo "<hr>".MdlForumMessage::menuSort()."<div class='menuLine'><div class='menuIcon'><img src='app/img/info.png'></div><div>".$curSubject->messagesNb." ".Txt::trad($curSubject->messagesNb>1?"FORUM_messages":"FORUM_message")."</div></div>";
		}
		////	MENU DES THEMES
		if($editThemeMenu==true)  {echo "<div class='menuLine sLink' onclick=\"lightboxOpen('?ctrl=forum&action=ForumThemeEdit');\"><div class='menuIcon'><img src='app/img/edit.png'></div><div>".Txt::trad("FORUM_editThemes")."</div></div>";}
		if($displayForum=="themes")  {echo "<div class='menuLine'><div class='menuIcon'><img src='app/img/info.png'></div><div>".count($themeList)." ".Txt::trad("FORUM_subjectThemes")."</div></div>";}
		?>
		</div>
	</div>

	<div id="pageCenterContent" class="<?= $displayForum=="themes"?"vThemes":null ?>">
		<?php
		////	PATH DU FORUM (ACCUEIL FORUM > THEME COURANT > SUBJET COURANT > AJOUTER SUJET/MESSAGE)
		//Début du menu
		echo "<div class='pathMenu miscContainer'>";
			//Label "Accueil du forum"
			$forumRootLink=(!empty($curTheme) || !empty($curSubject))  ?  "class='sLink' onclick=\"redir('?ctrl=forum')\""  :  null;//Lien pour retourner à la racine du forum (sauf si on y est déjà..)
			$forumRootLabel=(Req::isMobile() && !empty($curTheme))  ?  txt::trad("FORUM_forumRootResp")  :  txt::trad("FORUM_forumRoot");//Masque le label "Accueil du forum" si on est sur mobile et qu'on affiche un thème (on laisse + de place à ce dernier)
			echo "<div ".$forumRootLink."><img src='app/img/forum/iconSmall.png'> &nbsp;".$forumRootLabel."</div>";
			//Label du Theme courant
			$pathLabelMaxLength=(Req::isMobile())  ?  40  :  80;
			if(!empty($curTheme)){
				$curThemeLink=(!empty($curSubject))  ?  "class='sLink' onclick=\"redir('?ctrl=forum&_idTheme=".$curTheme->idThemeUrl."')\""  :  null;//Lien pour retourner au theme (sauf si on y est déjà..)
				echo "<div ".$curThemeLink."><img src='app/img/arrowRightBig.png'> ".Txt::reduce($curTheme->display(),$pathLabelMaxLength)."</div>";
			}
			//Label du sujet courant (sauf en mode "mobile", pour laisser la place au thème courant)
			if(!empty($curSubject) && Req::isMobile()==false)  {echo "<div><img src='app/img/arrowRightBig.png'> ".Txt::reduce(strip_tags($curSubject->title?$curSubject->title:$curSubject->description), $pathLabelMaxLength)."</div>";}
			//Bouton "plus" d'ajout de sujet ou message
			if($displayForum=="subjects" && MdlForumSubject::addRight())				{echo "<div class='pathIcon' onclick=\"lightboxOpen('".MdlForumSubject::getUrlNew()."')\" title=\"".Txt::trad("FORUM_addSubject")."\"><img src='app/img/arrowRightBig2.png'><img src='app/img/plus.png'></div>";}
			if($displayForum=="messages" && Ctrl::$curContainer->editContentRight())	{echo "<div class='pathIcon' onclick=\"lightboxOpen('".MdlForumMessage::getUrlNew()."')\" title=\"".Txt::trad("FORUM_addMessage")."\"><img src='app/img/arrowRightBig2.png'><img src='app/img/plus.png'></div>";}
		//Fin du menu
		echo "</div>";


		////	LISTE DES THEMES
		if($displayForum=="themes")
		{
			foreach($themeList as $tmpTheme)
			{
				//Description
				$tmpDescription=(!empty($tmpTheme->description))  ?  "<div class='vThemeDescription'>".$tmpTheme->description."</div>"  :  null;
				//Derniers posts
				$lastPost=null;
				if(!empty($tmpTheme->messageLast))  {$lastPost.=Txt::trad("FORUM_lastMessage")." ".$tmpTheme->messageLast->displayAutor()." : ".$tmpTheme->messageLast->displayDate()."<br>";}//note: s'il y a un message ..ya forcément un sujet
				if(!empty($tmpTheme->subjectLast))  {$lastPost.=Txt::trad("FORUM_lastSubject")." ".$tmpTheme->subjectLast->displayAutor()." : ".$tmpTheme->subjectLast->displayDate();}
				if(empty($lastPost))				{$lastPost.=Txt::trad("FORUM_noSubject");}
				//Affichage
				echo "<div class='objContainer alternateLines' onClick=\"redir('?ctrl=forum&_idTheme=".$tmpTheme->idThemeUrl."')\">
						<div class='objContent sLink'>
							<div><div class='vThemeTitle'>".$tmpTheme->display()."</div>".$tmpDescription."</div>
							<div class='vObjDetails'><hr>".$lastPost."</div>
						</div>
					</div>";
			}
		}

		////	LISTE DES SUJETS
		if($displayForum=="subjects")
		{
			foreach($subjectsDisplayed as $tmpSubject)
			{
				//Init
				$isNewSubject=($tmpSubject->curUserLastMessageIsNew())  ?  "vSubjNew"  :  null;
				$displayedTitle=(!empty($tmpSubject->title))  ?  $tmpSubject->title."<hr>"  :  null;
				if(empty($tmpSubject->messagesNb))  {$lastPost=Txt::trad("FORUM_noMessage");}
				else{
					$lastPost=Txt::trad("FORUM_lastMessage")." ".$tmpSubject->messageLast->displayAutor();
					if(Req::isMobile()==false)  {$lastPost.=" : ".$tmpSubject->messageLast->displayDate();}
				}
				//Affichage
				echo $tmpSubject->divContainer("alternateLines").$tmpSubject->contextMenu().
					"<div class='objContent sLink ".$isNewSubject."' onclick=\"redir('?ctrl=forum&targetObjId=".$tmpSubject->_targetObjId."')\" title=\"".Txt::trad("FORUM_displaySubject")."\">
						<div>".$displayedTitle."<div class='vSubjMessDescription'>".Txt::reduce(strip_tags($tmpSubject->description),200)."</div>
						</div>
						<div class='vObjDetails'>
							<hr>
							<div>".$tmpSubject->displayAutorDate(true)."</div>
							<div>".$lastPost."</div>
						</div>
					</div>
				</div>";
			}
			//"AUCUN CONTENU"? || MENU DE PAGINATION
			if(empty($subjectsDisplayed))	{echo "<div class='emptyContainer'>".Txt::trad("FORUM_noSubject")."</div>";}
			else							{echo MdlForumSubject::menuPagination($subjectsTotalNb,"_idTheme");}
			//AJOUTER UN SUJET
			if(MdlForumSubject::addRight())  {echo "<div class='objBottomMenu'><div class='miscContainer sLink' onclick=\"lightboxOpen('".MdlForumSubject::getUrlNew()."')\"><img src='app/img/plus.png'> ".Txt::trad("FORUM_addSubject")."</div></div>";}
		}

		////	LISTE DES MESSAGES ..PRECÉDÉ DU SUJET
		if($displayForum=="messages")
		{
			//SUJET
			$displayedTitle=(!empty($curSubject->title))  ?  $curSubject->title."<hr>"  :  null;
			echo $curSubject->divContainer("alternateLines").$curSubject->contextMenu().
					"<div class='objContent vSubjectMessages'>
						<div>".$displayedTitle."<div class='vSubjMessDescription'>".$curSubject->description.$curSubject->menuAttachedFiles()."</div></div>
						<div class='vObjDetails'><hr>".$curSubject->displayAutorDate(true)."</div>
					</div>
				</div>";
			//MESSAGES DU SUJET
			foreach($subjectMessages as $tmpMessage)
			{
				//Titre du message  &&  bouton pour citer le message ("quote")  &&  Citation d'un message "parent"
				$subjMessQuote=$displayedTitle=$quotedMessage=null;
				if($curSubject->editContentRight())	{$subjMessQuote="<span class='vSubjMessQuote' onclick=\"lightboxOpen('".MdlForumMessage::getUrlNew()."&_idMessageParent=".$tmpMessage->_id."')\" title=\"".Txt::trad("FORUM_quoteMessage")."\"><img src='app/img/forum/quoteReponse.png'></span>";}
				if(!empty($tmpMessage->title))		{$displayedTitle=$tmpMessage->title."<hr>";}
				if(!empty($tmpMessage->_idMessageParent)){
					$quotedMessageObj=Ctrl::getObj(get_class($tmpMessage),$tmpMessage->_idMessageParent);
					$quotedMessage="<div class='vMessageQuoted'>".$quotedMessageObj->title."<br>".$quotedMessageObj->description."<img src='app/img/forum/quote.png'></div><br>";
				}
				//Affichage
				echo $tmpMessage->divContainer("alternateLines").$tmpMessage->contextMenu().
						"<div class='objContent vSubjectMessages'>
							<div>".$subjMessQuote.$quotedMessage.$displayedTitle."<div class='vSubjMessDescription'>".$tmpMessage->description.$tmpMessage->menuAttachedFiles()."</div></div>
							<div class='vObjDetails'><hr>".$tmpMessage->displayAutorDate(true)."</div>
						</div>
					</div>";
			}
			//REPONDRE AU SUJET (en responsive on affiche le bouton du bas : "respAddButton")
			if(Ctrl::$curContainer->editContentRight())  {echo "<div class='objBottomMenu'><div class='miscContainer sLink' onclick=\"lightboxOpen('".MdlForumMessage::getUrlNew()."');\"><img src='app/img/forum/addMessage.png'> &nbsp; ".Txt::trad("FORUM_addMessage")."</div></div>";}
		}
		?>
	</div>
</div>