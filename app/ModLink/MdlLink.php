<?php
/**
* This file is part of the Agora-Project Software package
*
* @copyleft Agora-Project <https://www.agora-project.net>
* @license GNU General Public License, version 2 (GPL-2.0)
*/


/*
 * MODELE DES LIENS/FAVORIS
 */
class MdlLink extends MdlObject
{
	const moduleName="link";
	const objectType="link";
	const dbTable="ap_link";
	const MdlObjectContainer="MdlLinkFolder";
	const isFolderContent=true;
	const isSelectable=true;
	const hasShortcut=true;
	const hasAttachedFiles=true;
	const hasNotifMail=true;
	const hasUsersComment=true;
	const hasUsersLike=true;
	public static $displayModes=["block","line"];
	public static $requiredFields=["adress"];
	public static $searchFields=["adress","description"];
	public static $sortFields=["dateCrea@@desc","dateCrea@@asc","dateModif@@desc","dateModif@@asc","_idUser@@asc","_idUser@@desc","description@@asc","description@@desc","adress@@asc","adress@@desc"];
}