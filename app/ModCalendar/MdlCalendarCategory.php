<?php
/**
* This file is part of the Agora-Project Software package
*
* @copyleft Agora-Project <https://www.agora-project.net>
* @license GNU General Public License, version 2 (GPL-2.0)
*/


/*
 * MODELE DES CATEGORIES D'EVENEMENTS
 */
class MdlCalendarCategory extends MdlCategory
{
	const moduleName="calendar";
	const objectType="calendarCategory";
	const dbTable="ap_calendarCategory";
	const dbParentTable="ap_calendarEvent";
	const dbParentField="_idCat";
	const tradPrefix="CALENDAR";
	const optionAdminAddCategory="adminAddCategory";
}