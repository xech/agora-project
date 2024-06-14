<?php
/**
* This file is part of the Agora-Project Software package
*
* @copyleft Agora-Project <https://www.agora-project.net>
* @license GNU General Public License, version 2 (GPL-2.0)
*/


/*
 * MODELE DES DOSSIERS DE CONTACTS
 */
class MdlContactFolder extends MdlFolder
{
	const moduleName="contact";
	const objectType="contactFolder";
	const dbTable="ap_contactFolder";
	const MdlObjectContent="MdlContact";
}
