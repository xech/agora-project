<?php
/**
* This file is part of the Agora-Project Software package.
*
* @copyright (c) Agora-Project Limited <https://www.agora-project.net>
* @license GNU General Public License, version 2 (GPL-2.0)
*/


/*
 * MODELE DES MESSAGES DU FORUM
 */
class MdlForumMessage extends MdlObject
{
	const moduleName="forum";
	const objectType="forumMessage";
	const dbTable="ap_forumMessage";
	const MdlObjectContainer="MdlForumSubject";
	const htmlEditorField="description";
	const hasAttachedFiles=true;
	const hasNotifMail=true;
	const hasUsersLike=true;
	public static $requiredFields=["description"];
	public static $searchFields=["title","description"];
	public static $sortFields=["dateCrea@@asc","dateCrea@@desc","dateModif@@desc","dateModif@@asc","_idUser@@asc","_idUser@@desc","title@@asc","title@@desc","description@@asc","description@@desc"];

	/*******************************************************************************************
	 * SURCHARGE : SUPPRIME UN MESSAGE
	 *******************************************************************************************/
	public function delete()
	{
		if($this->deleteRight()){
			Db::query("UPDATE ap_forumMessage SET _idMessageParent=null WHERE _idMessageParent=".$this->_id);//des messages citent le message en question : on supprime la référence
			parent::delete();
		}
	}
}