<?php
/**
* This file is part of the Agora-Project Software package.
*
* @copyright (c) Agora-Project Limited <https://www.agora-project.net>
* @license GNU General Public License, version 2 (GPL-2.0)
*/


/*
 * Modele des dossiers de contacts
 */
class MdlContactFolder extends MdlObjectFolder
{
	const moduleName="contact";
	const objectType="contactFolder";
	const dbTable="ap_contactFolder";
	const hasAccessRight=true;
	const MdlObjectContent="MdlContact";
}
