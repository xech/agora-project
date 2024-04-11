<?php
/**
* This file is part of the Agora-Project Software package.
*
* @copyright (c) Agora-Project Limited <https://www.agora-project.net>
* @license GNU General Public License, version 2 (GPL-2.0)
*/


/*
 * MODELE DES CATEGORIES D'EVENEMENTS
 */
class MdlCalendarEventCategory extends MdlObjectCategory
{
	const moduleName="calendar";
	const objectType="calendarEventCategory";
	const dbTable="ap_calendarEventCategory";
	const dbTableParent="ap_calendarEvent";
	const _idFieldName="_idCat";
	const optionAdminAddCategory="adminAddCategory";
	public static $requiredFields=["title"];
	public static $sortFields=["title@asc","title@desc"];
}