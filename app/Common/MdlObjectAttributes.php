<?php
/**
* This file is part of the Agora-Project Software package.
*
* @copyright (c) Agora-Project Limited <https://www.agora-project.net>
* @license GNU General Public License, version 2 (GPL-2.0)
*/


/*
 * Attribut des Objects : AttachedFiles / Likes / Comments
 */
trait MdlObjectAttributes
{
	//Valeurs mises en cache
	private $_attachedFiles=null;
	private $_attachedFilesMenu=null;
	private $_usersComment=null;
	private $_usersLike=null;

	/*
	 * INPUT "HIDDEN" DE SÉLECTION (cf. "VueObjMenuContext.php" & Co)
	 */
	public function targetObjectsInput()
	{
		return "<input type='checkbox' name='targetObjects[]' class='targetObjectsInput' value=\"".$this->_targetObjId."\" id=\"".$this->menuId("objBlock")."_selectBox\">";
	}

	/*
	 * FICHIER JOINT : Infos sur un fichier joint
	 */
	public static function getAttachedFile($tmpFile)
	{
		if(!empty($tmpFile))
		{
			//Si besoin, récup les infos en bdd
			if(is_numeric($tmpFile))  {$tmpFile=Db::getLine("SELECT * FROM ap_objectAttachedFile WHERE _id=".(int)$tmpFile);}//Si besoin, récup les infos en bdd
			//Ajoute le chemin du fichier  &&  L'url d'affichage (cf. insertion d'image dans la description d'un objet)  && L'objet conteneur
			$tmpFile["path"]=PATH_OBJECT_ATTACHMENT.$tmpFile["_id"].".".File::extension($tmpFile["name"]);
			$tmpFile["url"]="?ctrl=object&amp;action=displayAttachedFile&amp;_id=".$tmpFile["_id"]."&amp;extention=.".File::extension($tmpFile["name"]);//Mettre les "&amp;", car Tinymce l'ajoute apres modif de l'objet..
			$tmpFile["containerObj"]=Ctrl::getObj($tmpFile["objectType"],$tmpFile["_idObject"]);
			return $tmpFile;
		}
	}

	/*
	 * FICHIER JOINT : Liste des fichiers joints de l'objet
	 */
	public function getAttachedFileList()
	{
		//Mise en cache des fichiers joints de l'objet & Ajoute le "path"
		if($this->_attachedFiles===null){
			$this->_attachedFiles=Db::getTab("SELECT * FROM ap_objectAttachedFile WHERE objectType='".static::objectType."' AND _idObject=".$this->_id);
			foreach($this->_attachedFiles as $fileKey=>$tmpFile)  {$this->_attachedFiles[$fileKey]=self::getAttachedFile($tmpFile);}
		}
		//Retoune les résultats
		return $this->_attachedFiles;
	}

	/*
	 * FICHIER JOINT : Menus des fichiers joints de l'objet (menu contextuel ou description) : affiche et propose le téléchargement
	 */
	public function menuAttachedFiles($separator="<hr>")
	{
		if(static::hasAttachedFiles==true)
		{
			//Mise en cache du menu des fichiers joints
			if($this->_attachedFilesMenu===null)
			{
				$this->_attachedFilesMenu="";
				//Affiche le menu avec chaque fichiers
				if(count($this->getAttachedFileList())>0)
				{
					foreach($this->getAttachedFileList() as $tmpFile){
						$getFileUrl="?ctrl=object&action=getFile&_id=".$tmpFile["_id"];
						if(Req::isMobileApp())  {$getFileUrl=CtrlMisc::appGetFileUrl($getFileUrl,$tmpFile["name"]);}//Download depuis mobileApp : Switch sur le controleur "ctrl=misc" (cf. "$initCtrlFull=false")
						$this->_attachedFilesMenu.="<div class='menuAttachedFile sLink' title=\"".Txt::trad("download")."\" onclick=\"if(confirm('".Txt::trad("download",true)." ?')) redir('".$getFileUrl."');\"><img src='app/img/attachment.png'> ".$tmpFile["name"]."</div>";
					}
				}
			}
			//Retoune les résultats, avec un séparateur différent pour chaque affichage
			if($this->_attachedFilesMenu)  {return $separator.$this->_attachedFilesMenu;}
		}
	}

	/*
	 * FICHIER JOINT : Insert un fichier joint dans la description (image/video/audio/flash) : via une requete Sql OU l'editeur html
	 */
	public static function attachedFileInsert($_idFile, $editorInsert=true)
	{
		//Init
		$insertText=null;
		$curFile=self::getAttachedFile($_idFile);
		//Récupère l'image OU le player du média
		if(File::isType("imageBrowser",$curFile["path"]))		{$insertText="<img src='".$curFile["url"]."' style='max-width:100%;'>";}//garder le "style" pour tinyMce..
		elseif(File::isType("mp3",$curFile["path"]))			{$insertText=File::getMediaPlayer($curFile["url"]);}
		elseif(File::isType("videoPlayer",$curFile["path"]))	{$insertText=File::getMediaPlayer($curFile["path"]);}//affichage direct
		//Objet "DashboardNews : ajoute un lien pour le lightBox
		if(static::objectType=="dashboardNews")  {$insertText="<a href='".$curFile["url"]."' data-fancybox='images'>".$insertText."</a>";}
		//Retourne le résultat
		if(!empty($insertText)){
			$insertText="<div id='tagAttachedFile".$curFile["_id"]."' title='".str_replace("'",null,$curFile["name"])."'>".$insertText."</div>";
			return ($editorInsert==true)  ?  "onclick=\"tinymce.activeEditor.setContent(tinymce.activeEditor.getContent()+'".addslashes(str_replace("\"","'",$insertText))."');\""  :  $insertText;
		}
	}

	/*
	 * FICHIER JOINT : Ajoute les fichiers joints du "menuEdit()"
	 */
	public function addAttachedFiles()
	{
		if(static::hasAttachedFiles==true)
		{
			$curDatasFolderSize=File::datasFolderSize();
			foreach($_FILES as $inputId=>$tmpFile)
			{
				//Fichier joint?
				if(stristr($inputId,"addAttachedFile") && $tmpFile["error"]==0 && File::controleUpload($tmpFile["name"],$tmpFile["size"]))
				{
					//Ajoute le fichier en Bdd et dans le dossier de destination
					$attachedFileId=Db::query("INSERT INTO ap_objectAttachedFile SET name=".Db::format($tmpFile["name"]).", objectType='".static::objectType."', _idObject=".$this->_id, true);
					$fileDestPath=PATH_OBJECT_ATTACHMENT.$attachedFileId.".".File::extension($tmpFile["name"]);
					$isMoved=move_uploaded_file($tmpFile["tmp_name"], $fileDestPath);
					if($isMoved!=false)
					{
						//Optimise le fichier
						if(File::isType("imageResize",$fileDestPath))  {File::imageResize($fileDestPath,$fileDestPath,1400);}
						File::setChmod($fileDestPath);
						//Ajoute l'image/vidéo/Mp3 dans la description
						$insertCheckboxId=str_replace("addAttachedFile","addAttachedFileInsert",$inputId);
						if(static::htmlEditorField!=null && Req::isParam($insertCheckboxId) && File::isType("attachedFileInsert",$tmpFile["name"])){
							$newEditorValue=Db::getVal("SELECT ".static::htmlEditorField." FROM ".static::dbTable." WHERE _id=".$this->_id).self::attachedFileInsert($attachedFileId,false);
							Db::query("UPDATE ".static::dbTable." SET ".static::htmlEditorField."=".Db::format($newEditorValue,"editor")." WHERE _id=".$this->_id);
						}
						$curDatasFolderSize+=$tmpFile["size"];
					}
				}
			}
			File::datasFolderSize(true);//Recalcule $_SESSION["datasFolderSize"]
		}
	}

	/*
	 * FICHIER JOINT : Supprime un fichier joint
	 */
	public function deleteAttachedFile($curFile)
	{
		if($this->editRight() && is_array($curFile)){
			File::rm($curFile["path"]);
			if(!is_file($curFile["path"])){
				Db::query("DELETE FROM ap_objectAttachedFile WHERE _id=".(int)$curFile["_id"]);
				return true;
			}
		}
	}

	/*
	 * COMMENTAIRES : l'objet peut avoir des commentaires?
	 */
	public function hasUsersComment()
	{
		return (static::hasUsersComment && !empty(Ctrl::$agora->usersComment));
	}

	/*
	 * COMMENTAIRES : Liste les commentaires
	 */
	public function getUsersComment()
	{
		//Mise en cache des commentaires de l'objet
		if($this->_usersComment===null)
			{$this->_usersComment=Db::getTab("SELECT * FROM ap_objectComment WHERE objectType='".static::objectType."' AND _idObject=".$this->_id);}
		//Renvoi les résultats
		return $this->_usersComment;
	}

	/*
	 * COMMENTAIRE : droit d'édition/suppression d'un commentaire
	 */
	public static function userCommentEditRight($_idComment)
	{
		if(!empty($_idComment)){
			$idUser=Db::getVal("SELECT _idUser FROM ap_objectComment WHERE _id=".(int)$_idComment);
			return (Ctrl::$curUser->isAdminGeneral() || $idUser==Ctrl::$curUser->_id);
		}
	}

	/*
	 * LIKES : l'objet peut avoir des "likes"?
	 */
	public function hasUsersLike()
	{
		return (static::hasUsersLike && !empty(Ctrl::$agora->usersLike));
	}

	/*
	 * LIKES : Liste des "like"/"dontlike" (passer en parametre)
	 */
	public function getUsersLike($like_dontlike)
	{
		//Mise en cache des likes de l'objet
		if($this->_usersLike===null){
			$this->_usersLike=["like"=>[],"dontlike"=>[]];
			foreach(Db::getTab("SELECT * FROM ap_objectLike WHERE objectType='".static::objectType."' AND _idObject=".$this->_id)  as  $tmpLike){
				if($tmpLike["value"]==1)	{$this->_usersLike["like"][]=$tmpLike;}
				else						{$this->_usersLike["dontlike"][]=$tmpLike;}
			}
		}
		//Renvoie le résultat
		return $this->_usersLike[$like_dontlike];
	}

	/*
	 * LIKES : Récupère le tooltip ($like_dontlike => "like" ou "donlike")
	 */
	public function getUsersLikeTooltip($like_dontlike)
	{
		$tooltip=Txt::trad("AGORA_usersLike_".$like_dontlike)."<br>";
		foreach($this->getUsersLike($like_dontlike) as $tmpLike)	{$tooltip.=Ctrl::getObj("user",$tmpLike["_idUser"])->getLabel().", ";}
		return trim($tooltip,", ");
	}
}