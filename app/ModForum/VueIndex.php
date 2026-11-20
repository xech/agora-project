<script>
/**********************************************************************************************************
 *	ACTIVE/DÉSACTIVE LES NOTIFICATIONS PAR MAIL DES NOUVEAUX MESSAGES DU SUJET COURANT
**********************************************************************************************************/
ready(function(){
	$("#notifyLastMessage").on("click",function(){
		$.ajax("index.php?ctrl=forum&action=notifyLastMessage&typeId=<?= isset($curSubject) ? $curSubject->typeId : null ?>").done(function(result){
			$("#notifyLastMessage").toggleClass("optionSelect",(result=="addUser"));
		});
	});
});
</script>


<style>
/*Affichage des sujets & messages*/
.objContainer					{height:auto!important; min-height:auto!important; vertical-align:top; padding:15px 40px 15px 15px;}/*surcharge : hauteur variable des sujets/messages en fonction du texte affiché*/
.objContainerTab>div			{display:block; width:100%;}/*surcharge*/
.vTitle							{font-weight:bold;}
.vTitle:empty					{display:none;}
.vDescription					{margin:15px 0px; font-size:0.95rem; user-select:text;}/*description des sujets/messages*/
.vDetails						{display:table!important; width:100%;}
.vDetails>div					{display:table-cell; vertical-align:middle; line-height:25px;}/*'line-height' : cf '.personImgSmall'*/
.vDetails>div:last-child		{text-align:right; vertical-align:bottom;}
.vLastMessage					{margin-top:5px;}

/*****    RESPONSIVE LARGE*/
@media screen and (max-width:1199px){
	.vMessages					{border-radius:var(--border_radius_min);}
	.vDetails, .vDetails>div	{display:block;}
	.vDetails>div:last-child	{text-align:left; margin-top:15px;}
}
</style>


<div id="pageCenter">
	<div id="pageMenu">
		<?= MdlForumSubject::menuSelect() ?>
		<div class="miscContainer">
		<?php
		////	LISTE DES SUJETS :  AJOUT DE SUJET  &  MENU DES THEMES  &  TRI D'AFFICHAGE  &  NB DE SUJETS
		if($forumDisplay=="subjectList"){
			if(MdlForumSubject::addRight())  {echo '<div class="menuLine forMobileAddElem" onclick="lightboxOpen(\''.MdlForumSubject::getUrlNew().'\')"><div class="menuIcon"><img src="app/img/addBlue.svg"></div><div>'.Txt::trad("FORUM_addSubject").'</div></div><hr>';}
			echo MdlForumTheme::displayMenu().MdlForumSubject::menuSort().'<div class="menuLine"><div class="menuIcon"><img src="app/img/info.svg"></div><div>'.$subjectsTotalNb.' '.Txt::trad($subjectsTotalNb>1?"FORUM_subjects":"FORUM_subject").'</div></div>';
		}
		////	SUJET & MESSAGES ASSOCIES :  AJOUT DE MESSAGE  &  NOTIF PAR MAIL  &  TRI D'AFFICHAGE  &  NB DE MESSAGES
		elseif($forumDisplay=="suject"){
			if($curSubject->addContentRight())  {echo '<div class="menuLine forMobileAddElem" onclick="lightboxOpen(\''.MdlForumMessage::getUrlNew().'\')"><div class="menuIcon"><img src="app/img/addBlue.svg"></div><div>'.Txt::trad("FORUM_addMessage").'</div></div>';}
			if(!empty(Ctrl::$curUser->mail))  	{echo '<div class="menuLine '.($curSubject->curUserNotifyLastMessage()?'optionSelect':'option').'" id="notifyLastMessage" '.Txt::tooltip("FORUM_notifyLastPostTooltip").'><div class="menuIcon"><img src="app/img/mail.svg"></div><div>'.Txt::trad("FORUM_notifyLastPost").'</div></div>';}
			echo "<hr>".MdlForumMessage::menuSort();
		}
		?>
		</div>
	</div>


	<div id="pageContent">

		<!--SUJET >  "RETOUR VERS L'ACCUEIL"-->
		<?php if($forumDisplay=="suject"){ ?>
			<div class="pathMenu miscContainer">
				<div class="pathMenuHome" onclick="redir('index.php?ctrl=forum')"><img src="app/img/home.svg"><?= Txt::trad("FORUM_forumRoot") ?></div>
					<?php if($curSubject->addContentRight()){ ?>
						<div class="pathMenuAdd" <?= Txt::tooltip("FORUM_addMessage") ?> onclick="lightboxOpen('<?= MdlForumMessage::getUrlNew() ?>');"><img src="app/img/addBlue.svg"></div>
					<?php } ?>
				</div>
		<?php } ?>


		<!--LISTE DE SUJETS  ||  SUJET-->
		<?php
		foreach($subjectList as $tmpSubject){
			$subjectLink=$lastMessage=null;
			if($forumDisplay=="subjectList"){
				$subjectLink='onclick="redir(\'index.php?ctrl=forum&typeId='.$tmpSubject->typeId.'\')" '.Txt::tooltip("FORUM_displaySubject");				//Lien vers le sujet et ses messages
				$tmpSubject->description=Txt::reduce($tmpSubject->description,400);																			//Réduction de la description
				$messagesNb=Db::getVal("SELECT COUNT(*) FROM ap_forumMessage WHERE _idContainer=".$tmpSubject->_id);										//Nb de messages pour le sujet
				if(!empty($messagesNb)){																													//Auteur/date du dernier message
					$objLastMessage=Ctrl::getObj("forumMessage", Db::getVal("SELECT MAX(_id) FROM ap_forumMessage WHERE _idContainer=".$tmpSubject->_id));	//Dernier message posté
					$messagesNbLabel=$messagesNb.' '.($messagesNb>1 ? Txt::trad('FORUM_messages') : Txt::trad('FORUM_message'));
					$lastMessage='<div class="vLastMessage">'.$messagesNbLabel.' : '.Txt::trad("FORUM_lastMessageFrom").' '.Txt::dateLabel("dateNum",$objLastMessage->dateCrea).'</div>';
				}
			}
		?>
			<?= $tmpSubject->objContainer() ?>
				<div class="objContainerTab <?= $tmpSubject->alreadyConsulted()==false?'linkSelect':null ?>" <?= $subjectLink ?>>
					<div class="vTitle"><?= $tmpSubject->title ?></div>
					<div class="vDescription"><?= $tmpSubject->description ?></div>
					<div class="vDetails">
						<div><?= $tmpSubject->autorDate(false,true).$lastMessage ?></div>
						<div><?= $tmpSubject->categoryLabel() ?></div>
					</div>
				</div>
			</div>
		<?php } ?>


		<?php
		////	LISTE DES SUJETS : "AUCUN CONTENU"  ||  MENU DE PAGINATION
		if($forumDisplay=="subjectList"){
			if(empty($subjectList))	{echo '<div class="miscContainer emptyContent">'.Txt::trad("FORUM_noSubject").'</div>';}
			else					{echo MdlForumSubject::menuPagination($subjectsTotalNb);}
		}

		////	SUJET COURANT : 
		if($forumDisplay=="suject"){
			////	LISTE DES MESSAGES
			foreach($curSubject->messageList() as $tmpMessage){
				$objMessageParent=(!empty($tmpMessage->_idMessageParent))  ?  Ctrl::getObj("forumMessage",$tmpMessage->_idMessageParent)  :  null;
			?>
				<?= $tmpMessage->objContainer("vMessages") ?>
					<div class="objContainerTab">
						<!--MESSAGE PARENT : CITATION-->
						<?php if(!empty($objMessageParent)){ ?>
							<fieldset class="fieldsetQuote"><img src="app/img/forum/quote.svg"><?= $objMessageParent->title ?><div class="vDescription"><?= $objMessageParent->description ?></div></fieldset>
						<?php } ?>
						<!--TITRE + DESCRIPTION + ATTACHEMENT-->
						<div class="vTitle"><?= $tmpMessage->title ?></div>
						<div class="vDescription"><?= $tmpMessage->description ?></div>
						<?= $tmpMessage->attachedFileMenu() ?>
						<!--AUTEUR/DATE  +  REPONDRE : CITATION-->
						<div class="vDetails">
							<div><?= $tmpMessage->autorDate(false,true) ?></div>
							<div>
								<?php if($curSubject->addContentRight()){ ?>
									<div onclick="lightboxOpen('<?= MdlForumMessage::getUrlNew() ?>&_idMessageParent=<?= $tmpMessage->_id ?>')" <?= Txt::tooltip("FORUM_quoteMessageInfo") ?> ><?= Txt::trad("FORUM_quoteMessage") ?> <img src="app/img/forum/quote.svg"> </div>
								<?php } ?>
							</div>
						</div>
					</div>
				</div>
			<?php
			}

			////	AJOUTER UN MESSAGE
			if($curSubject->addContentRight() && Req::isMobile()==false){
			?>
				<div class="objMenuBottom"><div class="miscContainer" onclick="lightboxOpen('<?= MdlForumMessage::getUrlNew() ?>');"><img src="app/img/addBlue.svg">&nbsp; <?= Txt::trad("FORUM_addMessage") ?></div></div>
			<?php
			}
		}
		?>
	</div>
</div>