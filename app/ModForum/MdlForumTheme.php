<?php
/**
* This file is part of the Agora-Project Software package
*
* @copyleft Agora-Project <https://www.agora-project.net>
* @license GNU General Public License (GPL-2.0)
*/


/*
 * MODELE DES THEMES DE SUJETS
 */
class MdlForumTheme extends MdlCategory
{
	const moduleName="forum";
	const objectType="forumTheme";
	const dbTable="ap_forumTheme";
	const dbParentTable="ap_forumSubject";
	const dbParentField="_idTheme";
	const tradPrefix="FORUM";
	const optionAdminAddCategory="adminAddTheme";
}