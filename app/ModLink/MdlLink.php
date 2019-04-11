<?php
/**
* This file is part of the Agora-Project Software package.
*
* @copyright (c) Agora-Project Limited <https://www.agora-project.net>
* @license GNU General Public License, version 2 (GPL-2.0)
*/


/*
 * Modele des Liens
 */
class MdlLink extends MdlObject
{
	const moduleName="link";
	const objectType="link";
	const dbTable="ap_link";
	const hasAccessRight=true;//Elems à la racine
	const MdlObjectContainer="MdlLinkFolder";
	const isFolderContent=true;
	const isSelectable=true;
	const hasShortcut=true;
	const hasAttachedFiles=true;
	const hasNotifMail=true;
	const hasUsersComment=true;
	const hasUsersLike=true;
	public static $displayModeOptions=array("block","line");
	public static $requiredFields=array("adress");
	public static $searchFields=array("adress","description");
	public static $sortFields=array("dateCrea@@desc","dateCrea@@asc","dateModif@@desc","dateModif@@asc","_idUser@@asc","_idUser@@desc","description@@asc","description@@desc","adress@@asc","adress@@desc");
}