<?php
/**
* This file is part of the Agora-Project Software package
*
* @copyleft Agora-Project <https://www.agora-project.net>
* @license GNU General Public License (GPL-2.0)
*/


/*
 * MODELE DES DOSSIERS DE FICHIERS
 */
class MdlFileFolder extends MdlFolder
{
	const moduleName="file";
	const objectType="fileFolder";
	const dbTable="ap_fileFolder";
	const MdlObjectContent="MdlFile";

	/*******************************************************************************************
	 * SURCHARGE : MENU CONTEXTUEL AVEC L'OPTION "TELECHARGER LE DOSSIER"
	 *******************************************************************************************/
	public function contextMenu($options=null)
	{
		if($this->isRootFolder()==false && Ctrl::$curUser->isUser() && $this->readRight())
			{$options["specificOptions"][]=["actionJs"=>"window.open('?ctrl=".static::moduleName."&action=downloadArchive&objectsTypeId[fileFolder]=".$this->_id."')", "iconSrc"=>"download.png", "label"=>Txt::trad("downloadFolder")];}
		return parent::contextMenu($options);
	}

	/*******************************************************************************************
	 * SURCHARGE : AJOUT/MODIF DE DOSSIER
	 *******************************************************************************************/
	public function createUpdate($sqlFields)
	{
		$reloadedObj=parent::createUpdate($sqlFields);
		//Créé un nouveau dossier sur le disque?
		if(!file_exists($reloadedObj->folderPath("real"))){
			$isCreated=mkdir($reloadedObj->folderPath("real"));
			if($isCreated==false)	{self::noAccessExit(Txt::trad("NOTIF_fileOrFolderAccess"));}
			else					{File::setChmod($reloadedObj->folderPath("real"));}
		}
		////	Retourne l'objet rechargé
		return $reloadedObj;
	}
}