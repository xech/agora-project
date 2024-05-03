<script>
////	INIT
$(function(){
	//Active ou désactive l'envoi de notifications email pour les nouveaux messages d'un sujet
	$("#notifyLastMessage").on("click",function(){
		$.ajax("?ctrl=forum&action=notifyLastMessage&typeId=<?= $curSubject->_typeId ?>").done(function(result){
			$("#notifyLastMessage").toggleClass("optionSelect",(result=="addUser"));
		});
	});
});
</script>


<style>
/*Affichage des sujets & messages*/
:root				{--detailsMarginTop:15px;}
.objContainer		{height:auto!important; min-height:auto!important;}/*sujets/messages : hauteur variable en fonction du texte affiché (surcharge)*/
.objContent>div		{padding:15px!important; vertical-align:top;}/*surcharge*/
.vCategory			{margin-top:var(--detailsMarginTop);}
.vDescription		{margin-top:var(--detailsMarginTop); font-weight:normal; user-select:text;}/*description des sujets/messages*/
.vMessagesInfos		{margin-top:var(--detailsMarginTop);}
.vDetails			{white-space:nowrap; text-align:right;}/*Auteur du dernier post*/
.dateLabel			{margin-top:0px;}/*surcharge*/
.objBottomMenu div	{border-radius:10px;}/*surcharge*/

/*Messages & citation de message ("quote")*/
.vMessages			{padding-bottom:20px; border-left:5px solid #ccc; border-right:5px solid #ccc; border-radius:10px;}/*padding-bottom pour */
.vMessageQuoted		{position:relative; display:inline-block; overflow:auto; max-height:100px; margin:10px 0px 20px; padding:10px; padding-left:40px; font-style:italic;}/*"position:relative" : cf. "vMessageQuotedImg" */
.vMessageQuotedImg	{position:absolute; top:5px; left:5px; opacity:0.5;}
.vMessageQuoteAdd	{position:absolute; bottom:8px; left:8px; cursor:pointer;}/*Citer un message*/

/*MOBILE*/
@media screen and (max-width:1023px){
	.objContent, .objContent>div	{display:inline-block; width:100%!important;}/*surcharge*/
	.vDetails						{text-align:left; white-space:normal; padding-top:0px!important;}
	.vMessages						{border-radius:5px;}
}
</style>


<div id="pageCenter">
	<div id="pageModuleMenu">
		<?= MdlForumSubject::menuSelectObjects() ?>
		<div id="pageModMenu" class="miscContainer">
		<?php
		////	LISTE DES SUJETS :  AJOUT DE SUJET  &  MENU DES THEMES  &  TRI D'AFFICHAGE  &  NB DE SUJETS
		if(!isset($curSubject)){
			if(MdlForumSubject::addRight())  {echo '<div class="menuLine" onclick="lightboxOpen(\''.MdlForumSubject::getUrlNew().'\')"><div class="menuIcon"><img src="app/img/plus.png"></div><div>'.Txt::trad("FORUM_addSubject").'</div></div><hr>';}
			echo MdlForumTheme::displayMenu().MdlForumSubject::menuSort().'<div class="menuLine"><div class="menuIcon"><img src="app/img/info.png"></div><div>'.$subjectsTotalNb.' '.Txt::trad($subjectsTotalNb>1?"FORUM_subjects":"FORUM_subject").'</div></div>';
		}
		////	SUJET & MESSAGES ASSOCIES :  AJOUT DE MESSAGE  &  NOTIF PAR MAIL  &  TRI D'AFFICHAGE  &  NB DE MESSAGES
		else{
			if($curSubject->addContentRight())  {echo '<div class="menuLine" onclick="lightboxOpen(\''.MdlForumMessage::getUrlNew().'\')"><div class="menuIcon"><img src="app/img/plus.png"></div><div>'.Txt::trad("FORUM_addMessage").'</div></div>';}
			if(!empty(Ctrl::$curUser->mail))  	{echo '<div class="menuLine sLink '.($curSubject->curUserNotifyLastMessage()?'optionSelect':'optionUnselect').'" id="notifyLastMessage" title="'.Txt::trad("FORUM_notifyLastPostTooltip").'"><div class="menuIcon"><img src="app/img/mail.png"></div><div>'.Txt::trad("FORUM_notifyLastPost").'</div></div>';}
			echo "<hr>".MdlForumMessage::menuSort().'<div class="menuLine"><div class="menuIcon"><img src="app/img/info.png"></div><div>'.$curSubject->messagesInfos().'</div></div>';
		}
		?>
		</div>
	</div>

	<div id="pageCenterContent">
		<?php
		////	LISTE DES SUJETS
		////
		if(!isset($curSubject))
		{
			foreach($subjectsDisplayed as $tmpSubject)
			{
				$isNewSubject=($tmpSubject->alreadyConsulted()==false)  ?  "linkSelect"  :  null;
				echo $tmpSubject->objContainer().$tmpSubject->contextMenu().
					'<div class="objContent '.$isNewSubject.'" onclick="redir(\'?ctrl=forum&typeId='.$tmpSubject->_typeId.'\')" title="'.Txt::trad("FORUM_displaySubject").'">
						<div>
							<div class="vTitle">'.$tmpSubject->title.'</div>
							<div class="vDescription">'.Txt::reduce($tmpSubject->description,400).'</div>
							<div class="vCategory">'.$tmpSubject->categoryLabel().'</div>
							<div class="vMessagesInfos">'.$tmpSubject->messagesInfos().'</div>
						</div>
						<div class="vDetails">'.$tmpSubject->autorDateLabel(true).'</div>
					</div>
				</div>';
			}
			//"AUCUN CONTENU"  ||  MENU DE PAGINATION
			if(empty($subjectsDisplayed))	{echo "<div class='emptyContainer'>".Txt::trad("FORUM_noSubject")."</div>";}
			else							{echo MdlForumSubject::menuPagination($subjectsTotalNb);}
		}

		////	SUJET  &  MESSAGES ASSOCIES
		////
		else
		{
			//// MENU "RETOUR VERS L'ACCUEIL"
			echo '<div class="pathMenu miscContainer">
					<div class="pathMenuHome" onclick="redir(\'?ctrl=forum\')"><img src="app/img/forum/iconSmall.png">&nbsp; '.txt::trad("FORUM_forumRoot").'</div>
					'.($curSubject->addContentRight() ? '<div class="pathMenuAdd" onclick="lightboxOpen(\''.MdlForumMessage::getUrlNew().'\')" title="'.Txt::trad("FORUM_addMessage").'"><img src="app/img/arrowRightBig.png">&nbsp;<img src="app/img/plus.png"></div>' : null).'
					</div>';

			//// SUJET
			echo $curSubject->objContainer("fieldsetSub").$curSubject->contextMenu().
					'<div class="objContent">
						<div>
							<div class="vTitle">'.$curSubject->title.'</div>
							<div class="vDescription">'.$curSubject->description.'</div>
							<div class="vCategory">'.$curSubject->categoryLabel().'</div>
							'.$curSubject->attachedFileMenu().'
						</div>
						<div class="vDetails">'.$curSubject->autorDateLabel(true).'</div>
					</div>
				</div>';
			//// MESSAGES DU SUJET
			foreach($curSubject->messageList() as $tmpMessage)
			{
				//Citer un message ("quote") : affiche si besoin le message cité  &  affiche un bouton pour citer le message courant
				if(empty($tmpMessage->_idMessageParent))  {$quotedMessage=null;}
				else{
					$quotedMessageObj=Ctrl::getObj("forumMessage",$tmpMessage->_idMessageParent);
					$quotedMessage='<fieldset class="vMessageQuoted"><img src="app/img/forum/quote.png" class="vMessageQuotedImg">'.$quotedMessageObj->title.'<div class="vDescription">'.$quotedMessageObj->description.'</div></fieldset>';
				}
				$subjMessQuote=($curSubject->addContentRight())  ?  '<img src="app/img/forum/quote.png" class="vMessageQuoteAdd" onclick="lightboxOpen(\''.MdlForumMessage::getUrlNew().'&_idMessageParent='.$tmpMessage->_id.'\')" title="'.Txt::trad("FORUM_quoteMessage").'">'  :  null;
				//Affichage
				echo $tmpMessage->objContainer("vMessages").$tmpMessage->contextMenu().
						'<div class="objContent">
							<div>
								'.$quotedMessage.'
								<div class="vTitle">'.$tmpMessage->title.'</div>
								<div class="vDescription">'.$tmpMessage->description.'</div>'
								.$tmpMessage->attachedFileMenu().$subjMessQuote.
							'</div>
							<div class="vDetails">'.$tmpMessage->autorDateLabel(true).'</div>
						</div>
					</div>';
			}
			//REPONDRE AU SUJET (sur mobile on affiche le bouton du bas : "menuMobileAddButton")
			if($curSubject->addContentRight() && Req::isMobile()==false)  {echo '<div class="objBottomMenu"><div class="miscContainer" onclick="lightboxOpen(\''.MdlForumMessage::getUrlNew().'\')"><img src="app/img/forum/addMessage.png"> &nbsp; '.Txt::trad("FORUM_addMessage").'</div></div>';}
		}
		?>
	</div>
</div>