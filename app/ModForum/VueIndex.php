<script>
$(function(){
	/**********************************************************************************************************
	 *	ACTIVE/DÉSACTIVE LES NOTIFICATIONS PAR MAIL DES NOUVEAUX MESSAGES
	**********************************************************************************************************/
	$("#notifyLastMessage").on("click",function(){
		$.ajax("?ctrl=forum&action=notifyLastMessage&typeId=<?= $curSubject->_typeId ?>").done(function(result){
			$("#notifyLastMessage").toggleClass("optionSelect",(result=="addUser"));
		});
	});
});
</script>

<style>
/*Affichage des sujets & messages*/
.objContainer					{height:auto!important; min-height:auto!important; vertical-align:top; padding:15px 50px 15px 15px;}/*surcharge : hauteur variable des sujets/messages en fonction du texte affiché*/
.objContent>div					{display:block; width:100%;}/*surcharge*/
.vTitle:empty					{display:none;}
.vDescription					{margin:15px 0px; font-weight:normal; user-select:text;}/*description des sujets/messages*/
.vDetails						{display:table!important; width:100%;}
.vDetails>div					{display:table-cell; vertical-align:middle;}
.vDetails>div:last-child		{text-align:right;}
.vLastMessage					{margin-top:10px;}
.objBottomMenu div				{border-radius:10px;}/*surcharge*/

/*Messages & citation de message : "quote"*/
.vMessages						{border-left:5px solid #bbb; border-radius:8px;}
.vMessageQuoted					{position:relative; display:block; overflow:auto; max-height:100px; margin:15px 0px; padding:10px; padding-left:40px; font-style:italic;}/*"position:relative" : cf. "vMessageQuotedImg" */
.vMessageQuotedImg				{position:absolute; top:5px; left:5px; opacity:0.5;}

/*MOBILE*/
@media screen and (max-width:1024px){
	.vMessages					{border-radius:5px;}
	.vDetails, .vDetails>div	{display:block;}
	.vDetails>div:last-child	{text-align:left; margin-top:15px;}
}
</style>


<div id="pageCenter">
	<div id="pageModuleMenu">
		<?= MdlForumSubject::menuSelect() ?>
		<div id="pageModMenu" class="miscContainer">
		<?php
		////	LISTE DES SUJETS :  AJOUT DE SUJET  &  MENU DES THEMES  &  TRI D'AFFICHAGE  &  NB DE SUJETS
		if($forumDisplay=="subjectList"){
			if(MdlForumSubject::addRight())  {echo '<div class="menuLine" onclick="lightboxOpen(\''.MdlForumSubject::getUrlNew().'\')"><div class="menuIcon"><img src="app/img/plusSmall.png"></div><div>'.Txt::trad("FORUM_addSubject").'</div></div><hr>';}
			echo MdlForumTheme::displayMenu().MdlForumSubject::menuSort().'<div class="menuLine"><div class="menuIcon"><img src="app/img/info.png"></div><div>'.$subjectsTotalNb.' '.Txt::trad($subjectsTotalNb>1?"FORUM_subjects":"FORUM_subject").'</div></div>';
		}
		////	SUJET & MESSAGES ASSOCIES :  AJOUT DE MESSAGE  &  NOTIF PAR MAIL  &  TRI D'AFFICHAGE  &  NB DE MESSAGES
		else{
			if($curSubject->addContentRight())  {echo '<div class="menuLine" onclick="lightboxOpen(\''.MdlForumMessage::getUrlNew().'\')"><div class="menuIcon"><img src="app/img/plusSmall.png"></div><div>'.Txt::trad("FORUM_addMessage").'</div></div>';}
			if(!empty(Ctrl::$curUser->mail))  	{echo '<div class="menuLine sLink '.($curSubject->curUserNotifyLastMessage()?'optionSelect':'option').'" id="notifyLastMessage" '.Txt::tooltip("FORUM_notifyLastPostTooltip").'><div class="menuIcon"><img src="app/img/mail.png"></div><div>'.Txt::trad("FORUM_notifyLastPost").'</div></div>';}
			echo "<hr>".MdlForumMessage::menuSort();
		}
		?>
		</div>
	</div>

	<div id="pageCenterContent">
		<?php
		////	SUJET COURANT : MENU "RETOUR VERS L'ACCUEIL"
		if($forumDisplay=="suject")
		{
			echo '<div class="pathMenu miscContainer">
					<div class="pathMenuHome" onclick="redir(\'?ctrl=forum\')"><img src="app/img/forum/iconSmall.png">&nbsp; '.Txt::trad("FORUM_forumRoot").'</div>
					'.($curSubject->addContentRight() ? '<div class="pathMenuAdd" onclick="lightboxOpen(\''.MdlForumMessage::getUrlNew().'\')" '.Txt::tooltip("FORUM_addMessage").'><img src="app/img/arrowRightBig.png">&nbsp;<img src="app/img/plus.png"></div>' : null).'
					</div>';
		}

		////	LISTE DES SUJETS  ||  SUJET COURANT
		foreach($subjectList as $tmpSubject)
		{
			$newSubjectClass=$subjectLastMessage=$subjectLink=null;
			if($forumDisplay=="subjectList"){
				$subjectLink='onclick="redir(\'?ctrl=forum&typeId='.$tmpSubject->_typeId.'\')" '.Txt::tooltip("FORUM_displaySubject");					//Lien vers le sujet et ses messages
				$tmpSubject->description=Txt::reduce($tmpSubject->description,400);																		//Réduction de la description
				if($tmpSubject->alreadyConsulted()==false)  {$newSubjectClass="linkSelect";}															//Nouveau sujet en surbrillance
				$messagesNb=Db::getVal("SELECT COUNT(*) FROM ap_forumMessage WHERE _idContainer=".$tmpSubject->_id);									//Nb de messages pour le sujet
				if(!empty($messagesNb)){																												//Auteur/date du dernier message
					$lastMessage=Ctrl::getObj("forumMessage", Db::getVal("SELECT MAX(_id) FROM ap_forumMessage WHERE _idContainer=".$tmpSubject->_id));	//Dernier message posté
					$subjectLastMessage='<div class="vLastMessage">'.$messagesNb.' '.(Txt::trad($messagesNb>1?'FORUM_messages':'FORUM_message')).' : '.Txt::trad("FORUM_lastMessageFrom").' '.$lastMessage->autorLabel().' <img src="app/img/arrowRightBig.png"> '.$lastMessage->dateLabel().'</div>';
				}
			}
			echo $tmpSubject->divContainerContextMenu().
				'<div class="objContent '.$newSubjectClass.'" '.$subjectLink.'>
					<div class="vTitle">'.$tmpSubject->title.'</div>
					<div class="vDescription">'.$tmpSubject->description.'</div>
					<div class="vDetails">
						<div>'.$tmpSubject->autorDateLabel(true).$subjectLastMessage.'</div>
						<div>'.$tmpSubject->categoryLabel().'</div>
					</div>
				</div>
			</div>';
		}

		////	LISTE DES SUJETS : "AUCUN CONTENU"  ||  MENU DE PAGINATION
		if($forumDisplay=="subjectList"){
			if(empty($subjectList))	{echo "<div class='emptyContainer'>".Txt::trad("FORUM_noSubject")."</div>";}
			else					{echo MdlForumSubject::menuPagination($subjectsTotalNb);}
		}

		////	SUJET COURANT : LISTE DES MESSAGES
		if($forumDisplay=="suject")
		{
			foreach($curSubject->messageList() as $tmpMessage)
			{
				//Citer un message ("quote") : affiche si besoin le message cité  &  affiche un bouton pour citer le message courant
				if(empty($tmpMessage->_idMessageParent))  {$quotedMessage=null;}
				else{
					$quotedMessageObj=Ctrl::getObj("forumMessage",$tmpMessage->_idMessageParent);
					$quotedMessage='<fieldset class="vMessageQuoted"><img src="app/img/forum/quote.png" class="vMessageQuotedImg">'.$quotedMessageObj->title.'<div class="vDescription">'.$quotedMessageObj->description.'</div></fieldset>';
				}
				$subjMessQuote=($curSubject->addContentRight())  ?  '<div onclick="lightboxOpen(\''.MdlForumMessage::getUrlNew().'&_idMessageParent='.$tmpMessage->_id.'\')" '.Txt::tooltip("FORUM_quoteMessageInfo").'>'.Txt::trad("FORUM_quoteMessage").' <img src="app/img/forum/quote.png"> </div>'  :  null;
				//Affichage
				echo $tmpMessage->divContainerContextMenu("vMessages").
						'<div class="objContent">
							'.$quotedMessage.'
							<div class="vTitle">'.$tmpMessage->title.'</div>
							<div class="vDescription">'.$tmpMessage->description.'</div>'
							.$tmpMessage->attachedFileMenu().'
							<div class="vDetails">
								<div>'.$tmpMessage->autorDateLabel(true).'</div>
								<div>'.$subjMessQuote.'</div>
							</div>
						</div>
					</div>';
			}
			//REPONDRE AU SUJET (sur mobile on affiche le bouton du bas : "menuMobileAddButton")
			if($curSubject->addContentRight() && Req::isMobile()==false)  {echo '<div class="objBottomMenu"><div class="miscContainer" onclick="lightboxOpen(\''.MdlForumMessage::getUrlNew().'\')"><img src="app/img/forum/addMessage.png"> &nbsp; '.Txt::trad("FORUM_addMessage").'</div></div>';}
		}
		?>
	</div>
</div>