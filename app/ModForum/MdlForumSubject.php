<?php
/**
* This file is part of the Agora-Project Software package.
*
* @copyright (c) Agora-Project Limited <https://www.agora-project.net>
* @license GNU General Public License, version 2 (GPL-2.0)
*/


/*
 * MODELE DES SUJETS DU FORUM
 */
class MdlForumSubject extends MdlObject
{
	const moduleName="forum";
	const objectType="forumSubject";
	const dbTable="ap_forumSubject";
	const MdlObjectContent="MdlForumMessage";
	const descriptionEditor=true;
	const hasShortcut=true;
	const hasAttachedFiles=true;
	const hasNotifMail=true;
	const hasUsersLike=true;
	const isSelectable=true;
	protected static $_hasAccessRight=true;
	public static $pageNbObjects=30;
	public static $requiredFields=["description"];
	public static $searchFields=["title","description"];
	public static $sortFields=["dateLastMessage@@desc","dateLastMessage@@asc","dateCrea@@desc","dateCrea@@asc","dateModif@@desc","dateModif@@asc","_idUser@@asc","_idUser@@desc","title@@asc","title@@desc","description@@asc","description@@desc"];

	/*******************************************************************************************
	 * LISTE LES MESSAGES D'UN SUJET & RÉCUPÈRE LE DERNIER MESSAGE & LE NOMBRE DE MESSAGES 
	 *******************************************************************************************/
	public function getMessages($orderByDate=false)
	{
		//Récup la liste des messages et leur nombre
		$sqlOrder=($orderByDate==true)  ?  "ORDER BY dateCrea desc"  :  MdlForumMessage::sqlSort();
		$messageList=Db::getObjTab("forumMessage", "SELECT * FROM ap_forumMessage WHERE _idContainer=".$this->_id." ".$sqlOrder);
		//récup le dernier message et le "time" du post
		$this->messagesNb=count($messageList);
		foreach($messageList as $tmpMessage)
		{
			if(empty($this->timeLastPost) || strtotime($tmpMessage->dateCrea)>$this->timeLastPost){
				$this->messageLast=$tmpMessage;
				$this->timeLastPost=strtotime($tmpMessage->dateCrea);
			}
		}
		//Renvoi la liste des messages
		return $messageList;
	}

	/*******************************************************************************************
	 * VERIF : L'USER COURANT RECOIT-IL DES NOTIFICATIONS D'AJOUT DE NOUVEAU MESSAGE SUR LE SUJET COURANT?
	 *******************************************************************************************/
	public function curUserNotifyLastMessage()
	{
		return in_array(Ctrl::$curUser->_id,Txt::txt2tab($this->usersNotifyLastMessage));
	}

	/*******************************************************************************************
	 * VERIF : L'USER COURANT A-T-IL CONSULTÉ LE DERNIER MESSAGE?
	 *******************************************************************************************/
	public function curUserLastMessageIsNew()
	{
		return (Ctrl::$curUser->isUser() && !in_array(Ctrl::$curUser->_id,Txt::txt2tab($this->usersConsultLastMessage)));
	}
	
	/*******************************************************************************************
	 * L'USER COURANT A CONSULTÉ LE DERNIER MESSAGE : MAJ DB
	 *******************************************************************************************/
	public function curUserConsultLastMessageMaj()
	{
		if($this->curUserLastMessageIsNew()){
			$usersConsultLastMessage=array_merge([Ctrl::$curUser->_id], Txt::txt2tab($this->usersConsultLastMessage));
			Db::query("UPDATE ap_forumSubject SET usersConsultLastMessage=".Db::formatTab2txt($usersConsultLastMessage)." WHERE _id=".$this->_id);
		}
	}

	/*******************************************************************************************
	 * DROIT D'AJOUTER UN NOUVEAU SUJET
	 *******************************************************************************************/
	public static function addRight()
	{
		return (Ctrl::$curUser->isSpaceAdmin() || (Ctrl::$curUser->isUser() && Ctrl::$curSpace->moduleOptionEnabled(self::moduleName,"adminAddSubject")==false));
	}

	/*******************************************************************************************
	 * SURCHARGE : URL D'ACCÈS
	 *******************************************************************************************/
	public function getUrl($display=null)
	{
		//Url du theme : cf. "CtrlObject::actionDelete()"
		if($display=="theme"){
			if(!empty($this->_idTheme))					{return "?ctrl=".static::moduleName."&_idThemeFilter=".$this->_idTheme;}	//theme spécifique
			elseif(count(MdlForumTheme::getList())>0)	{return "?ctrl=".static::moduleName."&_idThemeFilter=noTheme";}				//Pseudo thème "sans theme"
			else										{return "?ctrl=".static::moduleName;}										//accueil du forum
		}
		//Url "parent"
		return parent::getUrl($display);
	}

	/*******************************************************************************************
	 * SURCHARGE : URL D'AJOUT D'UN NOUVEAU SUJET (EN FONCTION DU THÈME?)
	 *******************************************************************************************/
	public static function getUrlNew()
	{
		//"_idThemeFilter" du "CtrlForum::actionDefault()"  &&  "_idTheme" pour "CtrlForum::actionForumSubjectEdit()"
		$_idTheme=Req::isParam("_idThemeFilter")  ?  "&_idTheme=".Req::param("_idThemeFilter")  :  null;
		return parent::getUrlNew().$_idTheme;
	}
}