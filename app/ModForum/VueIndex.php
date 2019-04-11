<script>
$(function(){
	//Active/désactive les notifications des messages par mail
	<?php if($displayForum=="messages"){ ?>
		$("#notifyLastMessage").click(function(){
			$.ajax("?ctrl=forum&action=notifyLastMessage&targetObjId=<?= $curSubject->_targetObjId ?>").done(function(ajaxResult){
				if(ajaxResult=="addUser")	{$("#notifyLastMessage").addClass("vNotifyLastMessageSelect");}
				else						{$("#notifyLastMessage").removeClass("vNotifyLastMessageSelect");}
			});
		});
		//Selectionne "Me notifier par email"?
		if("<?= (int)$curSubject->curUserNotifyLastMessage() ?>"=="1")  {$("#notifyLastMessage").addClass("vNotifyLastMessageSelect");}
	<?php } ?>
});
</script>

<style>
/*General*/
.pageModMenuContainer{<?= ($pageFullOrCenter=="pageCenter") ? "display:none" : null ?>}
.objLines .objContainer			{height:auto!important; min-height:80px; padding-right:40px!important;}/*surcharge: + de hauteur et hauteur auto pour que le texte ne déborde pas s'il y en a beaucoup*/
.objLines .objContent>div		{padding:10px;}/*surcharge*/
.objLines .objMiscMenus			{width:40px;}/*surcharge: idem "pading-right" ci-dessus. Tester avec des Likes et attachedFiles*/
.objLines .objMiscMenus>div		{margin-top:8px;}/*surcharge*/
.vObjDetails					{white-space:nowrap; line-height:20px; text-align:right;}
.vObjDetails hr					{display:none;}
/*Themes*/
.vThemes						{min-width:800px!important;}/*surcharge ".pageCenterContent"*/
.vThemes .objContainer			{padding-right:10px!important;}/*surcharge*/
.vThemeTitle					{text-transform:uppercase;}
.vThemeDescription				{margin-top:7px; font-weight:normal;}
/*Sujet & Message*/
.vSubjectMessages				{cursor:default;}
.vSubjectMessages>div			{vertical-align:top!important;}
.vSubjMessDescription			{margin-top:10px; font-weight:normal;}
.vSubjMessQuote					{margin-right:8px;}
.vSubjNew						{color:#800;}/*un peu + discret que le sLinkSelect*/
.vMessageQuoted					{display:inline-block; overflow:auto; max-height:100px; margin-bottom:10px; padding:8px 15px 8px 15px; border-radius:5px; font-style:italic; background-color:<?= Ctrl::$agora->skin=="black"?"#333":"#eee" ?>;}
.vMessageQuoted [src*='quote']	{float:right; opacity:0.5;}
.vNotifyLastMessageSelect		{color:#a00; font-style:italic;}
.objContent hr					{background:linear-gradient(to right,<?= Ctrl::$agora->skin=="black"?"#888":"#eee" ?>,transparent)!important; margin-top:6px; margin-bottom:6px;}
.objBottomMenu>div				{min-width:150px;}

/*RESPONSIVE*/
@media screen and (max-width:1023px){
	.objLines .objContainer			{padding-right:35px!important;}
	.objContent, .objContent>div	{display:inline-block; width:100%!important;}/*surcharge*/
	.vObjDetails					{text-align:left; white-space:normal;}
	.vObjDetails hr					{display:block;}
</style>

<div class="<?= $pageFullOrCenter ?>">
	<div class="pageModMenuContainer">
		<div id="pageModMenu" class="miscContainer">
		<?php
		////	EDIT LES THEMES
		if(!empty($editTheme))  {echo "<div class='menuLine sLink' onclick=\"lightboxOpen('?ctrl=forum&action=ForumThemeEdit');\"><div class='menuIcon'><img src='app/img/category.png'></div><div>".Txt::trad("FORUM_editThemes")."</div></div>";}
		////	MENU DES SUJETS
		if($displayForum=="subjects"){
			echo MdlForumSubject::menuSort();
			$tradSubjects=($subjectsTotalNb>1) ? "FORUM_subjects" : "FORUM_subject";
			echo "<div class='menuLine'><div class='menuIcon'><img src='app/img/info.png'></div><div>".$subjectsTotalNb." ".Txt::trad($tradSubjects)."</div></div>";
		}
		////	MENU D'UN SUJET ET SES MESSAGES
		if($displayForum=="messages"){
			if(!empty(Ctrl::$curUser->mail))			{echo "<div class='menuLine sLink' id='notifyLastMessage' title=\"".Txt::trad("FORUM_notifyLastPostInfo")."\"><div class='menuIcon'><img src='app/img/mail.png'></div><div>".Txt::trad("FORUM_notifyLastPost")."</div></div>";}
			echo "<hr>".MdlForumMessage::menuSort()."<div class='menuLine'><div class='menuIcon'><img src='app/img/info.png'></div><div>".$curSubject->messagesNb." ".Txt::trad($curSubject->messagesNb>1?"FORUM_messages":"FORUM_message")."</div></div>";
		}
		?>
		</div>
	</div>

	<div class="objLines <?= ($displayForum=="theme" && empty($editTheme))?"pageCenterContent vThemes":"pageFullContent" ?>">
		<?php
		////	PATH DU FORUM (ACCUEIL FORUM > THEME COURANT > SUBJET COURANT > AJOUTER SUJET/MESSAGE)
		if($displayForum!="theme")
		{
			$pathMenuOptions="<div><img src='app/img/forum/iconSmall.png'></div><div class='sLink' onclick=\"redir('?ctrl=forum')\">".Txt::trad("FORUM_forumRoot")."</div>";
			$labelLength=(Req::isMobile())  ?  25  :  50;
			if(!empty($curTheme))	{$pathMenuOptions.="<div><img src='app/img/arrowRightBig.png'></div><div class='sLink' onclick=\"redir('?ctrl=forum&_idTheme=".$curTheme->idThemeUrl."')\">".Txt::reduce($curTheme->display(),$labelLength)."</div>";}
			if(!empty($curSubject))	{$pathMenuOptions.="<div><img src='app/img/arrowRightBig.png'></div><div>".strip_tags(Txt::reduce($curSubject->title?$curSubject->title:$curSubject->description,$labelLength))."</div>";}
			if($displayForum=="subjects" && MdlForumSubject::addRight())				{$pathMenuOptions.="<div><img src='app/img/arrowRightAdd.png'></div><div class='sLink' onclick=\"lightboxOpen('".MdlForumSubject::getUrlNew()."')\" title=\"".Txt::trad("FORUM_addSubject")."\"><img src='app/img/plus.png'></div>";}
			if($displayForum=="messages" && Ctrl::$curContainer->editContentRight())	{$pathMenuOptions.="<div><img src='app/img/arrowRightAdd.png'></div><div class='sLink' onclick=\"lightboxOpen('".MdlForumMessage::getUrlNew()."');\" title=\"".Txt::trad("FORUM_addMessage")."\"><img src='app/img/plus.png'></div>";}
			echo "<div class='pathMenu miscContainer'>".$pathMenuOptions."</div>";
		}

		////	LISTE DES THEMES
		if($displayForum=="theme")
		{
			foreach($themeList as $tmpTheme)
			{
				//Init
				$subjectsNb=$messagesNb="&nbsp;";
				if(!empty($tmpTheme->subjectsNb))	{$subjectsNb=$tmpTheme->subjectsNb." ".Txt::trad($tmpTheme->subjectsNb>1?"FORUM_subjects":"FORUM_subject").".&nbsp; ".Txt::trad("FORUM_lastPost")." ".$tmpTheme->subjectLast->displayAutor().", ".$tmpTheme->subjectLast->displayDate();}
				if(!empty($tmpTheme->messagesNb))	{$messagesNb=$tmpTheme->messagesNb." ".Txt::trad($tmpTheme->messagesNb>1?"FORUM_messages":"FORUM_message").".&nbsp; ".Txt::trad("FORUM_lastPost")." ".$tmpTheme->messageLast->displayAutor().", ".$tmpTheme->messageLast->displayDate();}
				//Affichage
				echo "<div class='objContainer' onClick=\"redir('?ctrl=forum&_idTheme=".$tmpTheme->idThemeUrl."')\">
						<div class='objContent sLink'>
							<div><div class='vThemeTitle'>".$tmpTheme->display()."</div><div class='vThemeDescription'>".$tmpTheme->description."</div></div>
							<div class='vObjDetails'><div>".$subjectsNb."</div><div>".$messagesNb."</div></div>
						</div>
					</div>";
			}
		}

		////	LISTE DES SUJETS
		if($displayForum=="subjects")
		{
			////AFFICHAGE DES SUJETS
			foreach($subjectsDisplayed as $tmpSubject)
			{
				//Init
				$styleNewSubject=($tmpSubject->curUserLastMessageIsNew())  ?  "vSubjNew"  :  null;
				$displayedTitle=(!empty($tmpSubject->title))  ?  $tmpSubject->title."<hr>"  :  null;
				$nbMessagesLastPost=$tmpSubject->messagesNb." ".Txt::trad($tmpSubject->messagesNb>1?"FORUM_messages":"FORUM_message");
				if(!empty($tmpSubject->messagesNb))  {$nbMessagesLastPost.=" : ".Txt::trad("FORUM_lastPost")." ".$tmpSubject->messageLast->displayAutor().", ".$tmpSubject->messageLast->displayDate();}
				//Affichage
				echo $tmpSubject->divContainer("alternateLines").$tmpSubject->contextMenu().
					"<div class='objContent sLink ".$styleNewSubject."' onclick=\"redir('?ctrl=forum&targetObjId=".$tmpSubject->_targetObjId."')\" title=\"".Txt::trad("FORUM_displaySubject")."\">
						<div>".$displayedTitle."<div class='vSubjMessDescription'>".Txt::reduce(strip_tags($tmpSubject->description),200)."</div>
						</div>
						<div class='vObjDetails'>
							<div>".Txt::trad("postBy")." ".$tmpSubject->displayAutor().", ".$tmpSubject->displayDate()."</div>
							<div>".$nbMessagesLastPost."</div>
						</div>
					</div>
				</div>";
			}
			////"AUCUN CONTENU"? || MENU DE PAGINATION
			if(empty($subjectsDisplayed))	{echo "<div class='emptyContainer'>".Txt::trad("FORUM_noSubject")."</div>";}
			else							{echo MdlForumSubject::menuPagination($subjectsTotalNb,"_idTheme");}
			////AJOUTER UN SUJET
			if(MdlForumSubject::addRight())  {echo "<div class='objBottomMenu'><div class='miscContainer sLink' onclick=\"lightboxOpen('".MdlForumSubject::getUrlNew()."')\"><img src='app/img/plus.png'> ".Txt::trad("FORUM_addSubject")."</div></div>";}
		}

		////	LISTE DES MESSAGES ..PRECEDE DU SUJET
		if($displayForum=="messages")
		{
			////SUJET
			$displayedTitle=(!empty($curSubject->title))  ?  $curSubject->title."<hr>"  :  null;
			echo $curSubject->divContainer("alternateLines").$curSubject->contextMenu().
					"<div class='objContent vSubjectMessages'>
						<div>".
							$displayedTitle.
							"<div class='vSubjMessDescription'>".$curSubject->description.$curSubject->menuAttachedFiles()."</div>
						</div>
						<div class='vObjDetails'><hr>".$curSubject->displayAutorDate(true)."</div>
					</div>
				</div>";
			////MESSAGES DU SUJET
			foreach($subjectMessages as $tmpMessage)
			{
				//Init
				$displayedTitle=$quotedMessage=null;
				if(!empty($tmpMessage->title))		{$displayedTitle=$tmpMessage->title."<hr>";}
				if($curSubject->editContentRight())	{$displayedTitle="<a href=\"javascript:lightboxOpen('".MdlForumMessage::getUrlNew()."&_idMessageParent=".$tmpMessage->_id."')\" title=\"".Txt::trad("FORUM_quoteMessage")."\" class='vSubjMessQuote'><img src='app/img/forum/quoteReponse.png'></a>".$displayedTitle;}
				if(!empty($tmpMessage->_idMessageParent)){
					$tmpMessageParent=Ctrl::getObj(get_class($tmpMessage),$tmpMessage->_idMessageParent);
					$quotedMessage="<div class='vMessageQuoted'><img src='app/img/forum/quote.png'>".$tmpMessageParent->title."<br>".$tmpMessageParent->description."</div><br>";
				}
				//Affichage
				echo $tmpMessage->divContainer("alternateLines").$tmpMessage->contextMenu().
						"<div class='objContent vSubjectMessages'>
							<div>".$displayedTitle."<div class='vSubjMessDescription'>".$quotedMessage.$tmpMessage->description.$tmpMessage->menuAttachedFiles()."</div>
							</div>
							<div class='vObjDetails'><hr>".$tmpMessage->displayAutorDate(true)."</div>
						</div>
					</div>";
			}
			////REPONDRE AU SUJET
			if(Ctrl::$curContainer->editContentRight())  {echo "<div class='objBottomMenu'><div class='miscContainer sLink' onclick=\"lightboxOpen('".MdlForumMessage::getUrlNew()."');\"><img src='app/img/plus.png'> ".Txt::trad("FORUM_addMessage")."</div></div>";}
		}
		?>
	</div>
</div>