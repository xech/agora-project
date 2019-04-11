<?php
/**
* This file is part of the Agora-Project Software package.
*
* @copyright (c) Agora-Project Limited <https://www.agora-project.net>
* @license GNU General Public License, version 2 (GPL-2.0)
*/


/*
 * Modele des dossiers de liens
 */
class MdlLinkFolder extends MdlObjectFolder
{
	const moduleName="link";
	const objectType="linkFolder";
	const dbTable="ap_linkFolder";
	const hasAccessRight=true;
	const MdlObjectContent="MdlLink";
}
