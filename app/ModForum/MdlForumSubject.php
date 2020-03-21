<?php
/**
* This file is part of the Agora-Project Software package.
*
* @copyright (c) Agora-Project Limited <https://www.agora-project.net>
* @license GNU General Public License, version 2 (GPL-2.0)
*/


/*
 * Modele des sujets du forum
 */
class MdlForumSubject extends MdlObject
{
	const moduleName="forum";
	const objectType="forumSubject";
	const dbTable="ap_forumSubject";
	const hasAccessRight=true;
	const MdlObjectContent="MdlForumMessage";
	const htmlEditorField="description";
	const hasShortcut=true;
	const hasAttachedFiles=true;
	const hasNotifMail=true;
	const hasUsersLike=true;
	public static $pageNbObjects=30;
	public static $requiredFields=array("description");
	public static $searchFields=array("title","description");
	public static $sortFields=array("dateLastMessage@@desc","dateLastMessage@@asc","dateCrea@@desc","dateCrea@@asc","dateModif@@desc","dateModif@@asc","_idUser@@asc","_idUser@@desc","title@@asc","title@@desc","description@@asc","description@@desc");

	/*
	 * Liste des messages d'un sujet & Récupère le dernier message & le nombre de messages 
	 */
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

	/*
	 * L'user courant recoit-il des notifications d'ajout de nouveau message sur le sujet courant?
	 */
	public function curUserNotifyLastMessage()
	{
		return in_array(Ctrl::$curUser->_id,Txt::txt2tab($this->usersNotifyLastMessage));
	}

	/*
	 * L'user courant a-t-il consulté le dernier message?
	 */
	public function curUserLastMessageIsNew()
	{
		return (Ctrl::$curUser->isUser() && !in_array(Ctrl::$curUser->_id,Txt::txt2tab($this->usersConsultLastMessage)));
	}
	
	/*
	 * L'User courant a consulté le dernier message : MAJ DB
	 */
	public function curUserConsultLastMessageMaj()
	{
		if($this->curUserLastMessageIsNew()){
			$usersConsultLastMessage=array_merge([Ctrl::$curUser->_id], Txt::txt2tab($this->usersConsultLastMessage));
			Db::query("UPDATE ap_forumSubject SET usersConsultLastMessage=".Db::formatTab2txt($usersConsultLastMessage)." WHERE _id=".$this->_id);
		}
	}

	/*
	 * Droit d'ajouter un nouveau sujet
	 */
	public static function addRight()
	{
		return (Ctrl::$curUser->isAdminSpace() || (Ctrl::$curUser->isUser() && Ctrl::$curSpace->moduleOptionEnabled(self::moduleName,"adminAddSubject")==false));
	}

	/*
	 * SURCHARGE : Url d'accès (sujet d'un thème particulier ?)
	 */
	public function getUrl($display=null)
	{
		//Url "container" : theme
		if($display=="container"){
			if(!empty($this->_idTheme))					{return "?ctrl=".static::moduleName."&_idTheme=".$this->_idTheme;}	//theme spécifique
			elseif(count(MdlForumTheme::getThemes())>0)	{return "?ctrl=".static::moduleName."&_idTheme=undefinedTheme";}	//"sans theme"
			else										{return "?ctrl=".static::moduleName;}								//accueil du forum
		}
		//Url "parent" : normal
		return parent::getUrl($display);
	}
	
	/*
	 * SURCHARGE : Url d'ajoute d'un nouveau sujet (en fonction du thème?)
	 */
	public static function getUrlNew()
	{
		return parent::getUrlNew().(Req::isParam("_idTheme")?"&_idTheme=".Req::getParam("_idTheme"):null);
	}
}