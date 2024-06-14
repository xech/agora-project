<?php
/**
* This file is part of the Agora-Project Software package
*
* @copyleft Agora-Project <https://www.agora-project.net>
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
	const MdlCategory="MdlForumTheme";
	const descriptionEditor=true;
	const hasShortcut=true;
	const hasAttachedFiles=true;
	const hasNotifMail=true;
	const hasUsersLike=true;
	const isSelectable=true;
	protected static $_hasAccessRight=true;
	public static $pageNbObjects=20;
	public static $requiredFields=["description"];
	public static $searchFields=["title","description"];
	public static $sortFields=["dateCrea@@desc","dateCrea@@asc","dateLastMessage@@desc","dateLastMessage@@asc","dateModif@@desc","dateModif@@asc","_idUser@@asc","_idUser@@desc","title@@asc","title@@desc","description@@asc","description@@desc"];

	/*******************************************************************************************
	 * LISTE LES MESSAGES DU SUJET
	 *******************************************************************************************/
	public function messageList()
	{
		return Db::getObjTab("forumMessage", "SELECT * FROM ap_forumMessage WHERE _idContainer=".$this->_id." ".MdlForumMessage::sqlSort());
	}

	/************************************************************************************************
	 * L'USER COURANT VIENT DE CONSULTER LE SUJET ET LE DERNIER MESSAGE : AJOUTE A LA LISTE EN BDD
	 ************************************************************************************************/
	public function usersConsultUpdate()
	{
		if(Ctrl::$curUser->isUser() && $this->alreadyConsulted()==false){
			$usersConsultLastMessage=array_merge([Ctrl::$curUser->_id], Txt::txt2tab($this->usersConsultLastMessage));
			Db::query("UPDATE ap_forumSubject SET usersConsultLastMessage=".Db::formatTab2txt($usersConsultLastMessage)." WHERE _id=".$this->_id);
		}
	}

	/*******************************************************************************************
	 * VERIF : L'USER COURANT A-T-IL CONSULTÉ LE DERNIER MESSAGE ?
	 *******************************************************************************************/
	public function alreadyConsulted()
	{
		return (Ctrl::$curUser->isUser()==false || in_array(Ctrl::$curUser->_id,Txt::txt2tab($this->usersConsultLastMessage)));
	}

	/*******************************************************************************************
	 * VERIF : L'USER COURANT RECOIT-IL DES NOTIFICATIONS D'AJOUT DE NOUVEAU MESSAGE SUR LE SUJET COURANT?
	 *******************************************************************************************/
	public function curUserNotifyLastMessage()
	{
		return in_array(Ctrl::$curUser->_id,Txt::txt2tab($this->usersNotifyLastMessage));
	}

	/*******************************************************************************************
	 * DROIT D'AJOUTER UN NOUVEAU SUJET
	 *******************************************************************************************/
	public static function addRight()
	{
		return (Ctrl::$curUser->isSpaceAdmin() || (Ctrl::$curUser->isUser() && Ctrl::$curSpace->moduleOptionEnabled(self::moduleName,"adminAddSubject")==false));
	}
}