<?php
/**
* This file is part of the Agora-Project Software package
*
* @copyleft Agora-Project <https://www.agora-project.net>
* @license GNU General Public License, version 2 (GPL-2.0)
*/


/*
 * Classe des Objects => Fichiers joints + Commentaires + Likes
 */
trait MdlObjectMisc
{
	/*******************************************************************************************
	 * FICHIERS JOINT : INFOS SUR UN FICHIER JOINT
	 *******************************************************************************************/
	public static function attachedFileInfos($file)
	{
		if(!empty($file))
		{
			if(is_numeric($file))  {$file=Db::getLine("SELECT * FROM ap_objectAttachedFile WHERE _id=".(int)$file);}	//Si besoin, récupère les infos en bdd
			$file["path"]=PATH_OBJECT_ATTACHMENT.$file["_id"].".".File::extension($file["name"]);						//Path/chemin réel du fichier
			$file["url"]=CtrlObject::attachedFileDisplayUrl($file["_id"], $file["name"]);								//Url pour un affichage du fichier via "actionAttachedFileDisplay()"
			$file["containerObj"]=Ctrl::getObj($file["objectType"],$file["_idObject"]);									//Ajoute l'objet dont dépend le fichier joint
			if(File::isType("imageBrowser",$file["name"]))  {$file["cid"]="attachedFile".$file["_id"];}					//"cid" du fichier pour l'envoi des mails (cf. "attachedFileImageCid()")
			return $file;
		}
	}

	/*******************************************************************************************
	 * FICHIERS JOINT : AJOUTE DANS LE CORPS DE L'EMAIL LES IMAGES EN PIECE JOINTE => "CID"
	 *******************************************************************************************/
	public function attachedFileImageCid($mailMessage)
	{
		foreach($this->attachedFileList() as $tmpFile){																		//Parcourt chaque fichier joint de l'objet courant
			if(!empty($tmpFile["cid"]))  {$mailMessage=str_replace($tmpFile["url"], "cid:".$tmpFile["cid"], $mailMessage);}	//Si c'est une image, on remplace l'url par le "cid" (ex: CID="XYZ" correspond à "<img src='cid:XYZ'>")
		}
		return $mailMessage;
	}

	/*******************************************************************************************
	 * FICHIERS JOINT : TABLEAU DES FICHIERS JOINTS DE L'OBJET
	 *******************************************************************************************/
	public function attachedFileList()
	{
		//Mise en cache
		if($this->_attachedFiles===null){																														
			if(static::hasAttachedFiles!==true)  {$this->_attachedFiles==[];}																					//Ce type d'objet ne gère pas les fichiers joint : tableau vide
			else{
				$this->_attachedFiles=Db::getTab("SELECT * FROM ap_objectAttachedFile WHERE objectType='".static::objectType."' AND _idObject=".$this->_id);	//Récupère les fichiers joints de l'objet en BDD
				foreach($this->_attachedFiles as $key=>$tmpFile)  {$this->_attachedFiles[$key]=self::attachedFileInfos($tmpFile);}								//Ajoute le "path" et autres infos de chaque fichier joint
			}
		}
		//Retoune les résultats (toujours au format "array")
		return (array)$this->_attachedFiles;
	}

	/*********************************************************************************************************************************
	 * FICHIERS JOINT : AFFICHE LES FICHIERS JOINTS DE L'OBJET (Menu contextuel OU vue description. Affiche et propose le téléchargement)
	 *********************************************************************************************************************************/
	public function attachedFileMenu($separator="<hr>")
	{
		//Mise en cache
		if($this->_attachedFilesMenu===null)
		{
			$this->_attachedFilesMenu="";
			//Affiche le menu avec chaque fichiers
			if(count($this->attachedFileList())>0)
			{
				foreach($this->attachedFileList() as $tmpFile){
					$getFileUrl="?ctrl=object&action=AttachedFileDownload&_id=".$tmpFile["_id"];
					if(Req::isMobileApp())  {$getFileUrl=CtrlMisc::urlGetFile($getFileUrl,$tmpFile["name"]);}//Download externe via mobileApp : modif l'url pour switcher sur "ctrl=misc"
					$this->_attachedFilesMenu.="<div class='attachedFileMenu' title=\"".Txt::trad("download")."\" onclick=\"if(confirm('".Txt::trad("download",true)." ?')) redir('".$getFileUrl."');\"><img src='app/img/attachment.png'> ".$tmpFile["name"]."</div>";
				}
			}
		}
		//Retoune les résultats, avec un séparateur différent pour chaque affichage
		if($this->_attachedFilesMenu)  {return $separator.$this->_attachedFilesMenu;}
	}

	/*******************************************************************************************
	 * FICHIERS JOINT : AJOUTE LES FICHIERS JOINTS DU "editMenuSubmit()"
	 *******************************************************************************************/
	public function attachedFileAdd()
	{
		if(static::hasAttachedFiles==true)
		{
			foreach($_FILES as $inputId=>$tmpFile)
			{
				//Ajoute chaque fichier joint (cf. "VueObjAttachedFile.php")
				if(stristr($inputId,"attachedFile") && File::uploadControl($tmpFile))
				{
					//Ajoute le fichier en Bdd et dans le dossier de destination
					$_idFile=Db::query("INSERT INTO ap_objectAttachedFile SET name=".Db::format($tmpFile["name"]).", objectType='".static::objectType."', _idObject=".$this->_id, true);
					$filePath=PATH_OBJECT_ATTACHMENT.$_idFile.".".File::extension($tmpFile["name"]);
					$isMoved=move_uploaded_file($tmpFile["tmp_name"], $filePath);
					//Fichier ajouté
					if($isMoved==true)
					{
						//Optimise si besoin le fichier + chmod
						if(File::isType("imageResize",$filePath))  {File::imageResize($filePath,$filePath,1600);}
						File::setChmod($filePath);
						//Nouvelle Image/Vidéo/Mp3 insérée dans l'éditeur TinyMce : remplace le "fileSrcTmp" par le path final
						if(static::descriptionEditor==true && File::isType("attachedFileInsert",$tmpFile["name"])){												//Vérifie qu'il s'agit d'un fichier autorisé
							$inputCpt=str_replace("attachedFile","",$inputId);																					//Récupère le compteur de l'input (cf. "VueObjAttachedFile.php")
							$editorContent=Db::getVal("SELECT `description` FROM `".static::dbTable."` WHERE _id=".$this->_id);									//Récupère le texte de l'éditeur
							$editorContent=str_replace("fileSrcTmp".$inputCpt, CtrlObject::attachedFileDisplayUrl($_idFile,$tmpFile["name"]), $editorContent);	//Remplace "fileSrcTmp" par l'url d'affichage du fichier
							$editorContent=str_replace("attachedFileTagTmp".$inputCpt, "attachedFileTag".$_idFile, $editorContent);								//Remplace "attachedFileTagTmp" par l'id du fichier en BDD
							Db::query("UPDATE ".static::dbTable." SET `description`=".Db::format($editorContent)." WHERE _id=".$this->_id);						//Update le texte de l'éditeur !
						}
					}
				}
			}
			File::datasFolderSize(true);//Recalcule $_SESSION["datasFolderSize"]
		}
	}

	/*******************************************************************************************
	 * FICHIERS JOINT : SUPPRIME UN FICHIER JOINT
	 *******************************************************************************************/
	public function attachedFileDelete($curFile)
	{
		if($this->editRight() && is_array($curFile)){
			File::rm($curFile["path"]);
			if(!is_file($curFile["path"])){
				Db::query("DELETE FROM ap_objectAttachedFile WHERE _id=".(int)$curFile["_id"]);
				return true;
			}
		}
	}

	/*******************************************************************************************
	 * COMMENTAIRES : L'OBJET PEUT AVOIR DES COMMENTAIRES?
	 *******************************************************************************************/
	public function hasUsersComment()
	{
		return (static::hasUsersComment && !empty(Ctrl::$agora->usersComment));
	}

	/*******************************************************************************************
	 * COMMENTAIRES : LISTE LES COMMENTAIRES
	 *******************************************************************************************/
	public function getUsersComment()
	{
		//Mise en cache
		if($this->_usersComment===null)
			{$this->_usersComment=Db::getTab("SELECT * FROM ap_objectComment WHERE objectType='".static::objectType."' AND _idObject=".$this->_id);}
		//Retourne les résultats
		return $this->_usersComment;
	}

	/*******************************************************************************************
	 * COMMENTAIRES : DROIT D'ÉDITION/SUPPRESSION D'UN COMMENTAIRE
	 *******************************************************************************************/
	public static function userCommentEditRight($_idComment)
	{
		if(!empty($_idComment)){
			$idUser=Db::getVal("SELECT _idUser FROM ap_objectComment WHERE _id=".(int)$_idComment);
			return (Ctrl::$curUser->isGeneralAdmin() || $idUser==Ctrl::$curUser->_id);
		}
	}

	/*******************************************************************************************
	 * LIKES : VERIF SI ON PEUT "LIKER" L'OBJET (fonction du type d'objet et du param. général)
	 *******************************************************************************************/
	public function hasUsersLike()
	{
		return (static::hasUsersLike && !empty(Ctrl::$agora->usersLike));
	}

	/*******************************************************************************************
	 * LIKES : LISTE DES USERS AYANT "LIKÉ" L'OBJET
	 *******************************************************************************************/
	public function getUsersLike()
	{
		//Mise en cache
		if($this->_usersLike===null)
			{$this->_usersLike=Db::getObjTab("user", "SELECT * FROM ap_user WHERE _id IN (select _idUser as _id from ap_objectLike where objectType='".static::objectType."' AND _idObject=".$this->_id.")");}
		//Retourne les résultats
		return $this->_usersLike;
	}

	/*******************************************************************************************
	 * LIKES : TOOLTIP DES USERS AYANT "LIKÉ" L'OBJET
	 *******************************************************************************************/
	public function usersLikeTooltip()
	{
		$tooltip=Txt::trad("AGORA_usersLike").'<br>';
		foreach($this->getUsersLike() as $tmpUser)  {$tooltip.=$tmpUser->getLabel().", ";}
		return trim($tooltip,", ");
	}
}