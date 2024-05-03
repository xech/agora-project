<?php
/**
* This file is part of the Agora-Project Software package.
*
* @copyright (c) Agora-Project Limited <https://www.agora-project.net>
* @license GNU General Public License, version 2 (GPL-2.0)
*/


/*
 * MODELE DES STATUS KANBAN DES TACHES
 */
class MdlTaskStatus extends MdlCategory
{
	const moduleName="task";
	const objectType="taskStatus";
	const dbTable="ap_taskStatus";
	const dbParentTable="ap_task";
	const dbParentField="_idStatus";
	const tradPrefix="TASK";
	const optionAdminAddCategory="adminAddStatus";

	/********************************************************************************************
	 * INSTALL/UPDATE : CRÉÉ SI BESOIN LES STATUS KANBAN DE BASE ("A FAIRE", "EN COURS", ETC.)
	 ********************************************************************************************/
	public static function dbFirstRecord()
	{
		if(Db::getVal("SELECT count(*) FROM ".self::dbTable)==0)
			{Db::query("INSERT INTO ".self::dbTable." (`_id`, `color`, `title`, `_idUser`, `rank`) VALUES  (1,'#888888','".Txt::trad("INSTALL_dataTaskStatus1")."', '1', '1'),  (2,'#000088','".Txt::trad("INSTALL_dataTaskStatus2")."', '1', '2'),  (3,'#cc8800','".Txt::trad("INSTALL_dataTaskStatus3")."', '1', '3'),  (4,'#008800','".Txt::trad("INSTALL_dataTaskStatus4")."', '1', '4')");}
	}
}