<?php
/**
* This file is part of the Agora-Project Software package.
*
* @copyright (c) Agora-Project Limited <https://www.agora-project.net>
* @license GNU General Public License, version 2 (GPL-2.0)
*/


/*
 * MODELE DES FICHIERS
 */
class MdlFile extends MdlObject
{
	private $_versions=null;
	private $_tumbPath=null;
	private $_hasTumb=null;
	const moduleName="file";
	const objectType="file";
	const dbTable="ap_file";
	const MdlObjectContainer="MdlFileFolder";
	const isFolderContent=true;
	//Propriétés d'IHM
	const isSelectable=true;
	const hasShortcut=true;
	const hasNotifMail=true;
	const hasUsersComment=true;
	const hasUsersLike=true;
	public static $displayModes=["block","line"];
	public static $requiredFields=["name"];
	public static $searchFields=["name","description"];
	public static $sortFields=["name@@asc","name@@desc","dateCrea@@desc","dateCrea@@asc","dateModif@@desc","dateModif@@asc","_idUser@@asc","_idUser@@desc","extension@@asc","extension@@desc","octetSize@@asc","octetSize@@desc","downloadsNb@@desc","downloadsNb@@asc"];

	/*******************************************************************************************
	 * LISTE DES VERSIONS DU FICHIER
	 *******************************************************************************************/
	public function getVersions($forceUpdate=false)
	{
		if($this->_versions===null || $forceUpdate==true)  {$this->_versions=Db::getTab("SELECT * FROM ap_fileVersion WHERE _idFile=".$this->_id." ORDER BY dateCrea desc");}//Le "ORDER BY" place la dernière version en premier!
		return $this->_versions;
	}

	/*******************************************************************************************
	 * DERNIÈRE VERSION DU FICHIER /  VERSION À UNE DATE DONNÉE
	 *******************************************************************************************/
	public function getVersion($dateCrea=null)
	{
		foreach($this->getVersions() as $tmpVersion){
			if($tmpVersion["dateCrea"]==$dateCrea || empty($dateCrea))    {return $tmpVersion;  break;}
		}
	}

	/*******************************************************************************************
	 * CHEMIN DU FICHIER SUR LE DISQUE (DERNIÈRE VERSION / VERSION PRÉCISÉ AVEC "$dateCrea")
	 *******************************************************************************************/
	public function filePath($dateCrea=null)
	{
		$curVersion=$this->getVersion($dateCrea);
		return $this->containerObj()->folderPath("real").$curVersion["realName"];
	}

	/*******************************************************************************************
	 * LIEN POUR AJOUTER DES FICHIERS
	 * $urlParams spécifié pour une nouvelle version de fichier
	 *******************************************************************************************/
	public static function urlAddFiles($urlParams=null)
	{
		//Par défaut on spécifie le dossier courant
		if(empty($urlParams))  {$urlParams="targetObjId=file&_idContainer=".Ctrl::$curContainer->_id;}
		return "?ctrl=".static::moduleName."&action=AddEditFiles&".$urlParams;
	}

	/*******************************************************************************************
	 * URL DE DOWNLOAD/DISPLAY DU FICHIER ($action : "download" ou "display")
	 *******************************************************************************************/
	public function urlDownloadDisplay($action="download", $dateCrea=null)
	{
		$returndUrl="?ctrl=file&action=getFile&targetObjId=".$this->_targetObjId;					//Url de base
		if(!empty($dateCrea))	{$returndUrl.="&dateCrea=".urlencode($dateCrea);}					//Sélectionne une version spécifique du fichier
		if($action=="display")		{$returndUrl.="&display=true";}									//Affiche le fichier dans une lightbox (images/pdf/txt/etc)
		elseif(Req::isMobileApp())  {$returndUrl=CtrlMisc::appGetFileUrl($returndUrl,$this->name);}	//OU Download depuis mobileApp : Switch sur le controleur "ctrl=misc" (cf. "$initCtrlFull=false")
		return $returndUrl."&extension=.".File::extension($this->name);								//Ajoute l'extension du fichier (cf. controles d'action depuis mobileApp)
	}

	/*******************************************************************************************
	 * NOM DE LA VIGNETTE DU FICHIER
	 *******************************************************************************************/
	public function getThumbName()
	{
		return $this->_id."_thumb.jpg";
	}

	/*******************************************************************************************
	 * CHEMIN DE LA VIGNETTE JPG D'UNE IMAGE OU D'UN PDF (créé ou à créer)
	 *******************************************************************************************/
	public function getThumbPath()
	{
		if($this->_tumbPath===null){
			if(File::isType("imageResize",$this->name) || (File::isType("pdf",$this->name) && extension_loaded("imagick")))	{$this->_tumbPath=$this->containerObj()->folderPath("real").$this->getThumbName();}
			else																											{$this->_tumbPath="";}
		}
		return $this->_tumbPath;
	}

	/*******************************************************************************************
	 * VERIFIE S'IL EXISTE UNE VIGNETTE POUR LE FICHIER
	 *******************************************************************************************/
	public function hasThumb()
	{
		if($this->_hasTumb===null)
			{$this->_hasTumb=(strlen($this->getThumbPath()) && is_file($this->getThumbPath()));}
		return $this->_hasTumb;
	}

	/*******************************************************************************************
	 * CRÉATION/MAJ LA VIGNETTE DU FICHIER (Image / Pdf)
	 *******************************************************************************************/
	public function createThumb()
	{
		//Fichier de moins de 15Mo?
		if(filesize($this->filePath()) < (File::sizeMo*15))
		{
			if(File::isType("imageResize",$this->name))  {return File::imageResize($this->filePath(),$this->getThumbPath(),300,300,90);}
			elseif(File::isType("pdf",$this->name) && extension_loaded("imagick"))
			{
				$tmpThumb=new Imagick();
				$tmpThumb->readimage($this->filePath()."[0]"); 
				$tmpThumb=$tmpThumb->mergeImageLayers(Imagick::LAYERMETHOD_FLATTEN);//Pour pas avoir de background noir
				$tmpThumb->writeImage($this->getThumbPath());
				$tmpThumb->clear();
				$tmpThumb->destroy();
				return File::imageResize($this->getThumbPath(),$this->getThumbPath(),300);
			}
		}
	}

	/*******************************************************************************************
	 * MENU DES VERSIONS DU FICHIER
	 *******************************************************************************************/
	public function versionsMenu($displayType)
	{
		$nbVersions=count($this->getVersions());
		if($nbVersions>1){
			$nbVersionsTitle=$nbVersions." ".Txt::trad("FILE_nbFileVersions");
			$displayLabelIcon=($displayType=="label") ? $nbVersionsTitle : "<img src='app/img/file/versions.png'>";
			return "<a onclick=\"lightboxOpen('?ctrl=file&action=FileVersions&targetObjId=".$this->_targetObjId."')\" class=\"vVersionsMenu\" title=\"".$nbVersionsTitle."\">".$displayLabelIcon."</a>";
		}
	}

	/*******************************************************************************************
	 * SURCHARGE : MENU CONTEXTUEL
	 *******************************************************************************************/
	public function contextMenu($options=null)
	{
		//// ADMIN D'ESPACE : "TÉLÉCHARGÉ PAR" + LISTE DES USERS AYANT TELECHARGE LE FICHIER
		$tooltipDownloadedBy=null;
		if(Ctrl::$curUser->isAdminSpace() && !empty($this->downloadedBy)){
			foreach(Txt::txt2tab($this->downloadedBy) as $tmpIdUser)  {$tooltipDownloadedBy.=Ctrl::getObj("user",$tmpIdUser)->getLabel().", ";}
			$tooltipDownloadedBy="title=\"".Txt::trad("FILE_downloadedBy")." : ".trim($tooltipDownloadedBy, ", ")."\"";
		}
		//// "TÉLÉCHARGER LE FICHIER" + TOOLTIP "FICHIER TÉLÉCHARGÉ X FOIS"  &&  "X VERSIONS DU FICHIER"  &&  "AJOUTER UNE NOUVELLE VERSION"
		$options["specificOptions"][]=["actionJs"=>"window.open('".$this->urlDownloadDisplay()."')", "iconSrc"=>"download.png", "label"=>Txt::trad("download")." &nbsp;<span class='cursorHelp' ".$tooltipDownloadedBy.">".str_replace("--NB_DOWNLOAD--",$this->downloadsNb,Txt::trad("FILE_downloadsNb"))."</span>"];
		if(count($this->getVersions())>1)	{$options["specificOptions"][]=["iconSrc"=>"file/versions.png", "label"=>$this->versionsMenu("label")];}//Avec le lien vers les versions (donc pas de "actionJs"..)
		if($this->editRight())				{$options["specificOptions"][]=["iconSrc"=>"plus.png", "label"=>Txt::trad("FILE_addFileVersion"), "actionJs"=>"lightboxOpen('".static::urlAddFiles("addVersion=true&targetObjId=".$this->_targetObjId)."')"];}
		return parent::contextMenu($options);
	}

	/*******************************************************************************************
	 * IMAGE DU FICHIER
	 *******************************************************************************************/
	public function typeIcon()
	{
		$pathFileTypes="app/img/file/fileType/";
		if($this->hasThumb())								{return $this->getThumbPath();}
		elseif(File::isType("pdf",$this->name))				{return $pathFileTypes."pdf.png";}
		elseif(File::isType("textEditor",$this->name))		{return $pathFileTypes."textEditor.png";}
		elseif(File::isType("text",$this->name))			{return $pathFileTypes."text.png";}
		elseif(File::isType("calc",$this->name))			{return $pathFileTypes."calc.png";}
		elseif(File::isType("presentation",$this->name))	{return $pathFileTypes."presentation.png";}
		elseif(File::isType("image",$this->name))			{return $pathFileTypes."image.png";}
		elseif(File::isType("archive",$this->name))			{return $pathFileTypes."archive.png";}
		elseif(File::isType("audio",$this->name))			{return $pathFileTypes."audio.png";}
		elseif(File::isType("video",$this->name))			{return $pathFileTypes."video.png";}
		elseif(File::isType("executable",$this->name))		{return $pathFileTypes."executable.png";}
		elseif(File::isType("web",$this->name))				{return $pathFileTypes."web.png";}
		elseif(File::isType("autocad",$this->name))			{return $pathFileTypes."autocad.png";}
		else												{return $pathFileTypes."misc.png";}
	}

	/*******************************************************************************************
	 * SURCHARGE : SUPPRIME UN FICHIER (toutes ses versions OU une version spécifique)	
	 * $deleteVersion : "deleteFolder" / "all" / version précise via "dateCrea"
	 *******************************************************************************************/
	public function delete($deleteVersion="all")
	{
		if($this->deleteRight())
		{
			//// Supprime tout le dossier conteneur : on supprime le fichier uniquement en Bdd car sa suppression sur le disque se fait avec celle du dossier parent (beaucoup plus rapide quand on a des milliers de fichiers!)
			if($deleteVersion=="deleteFolder"){
				Db::query("DELETE FROM ap_fileVersion WHERE _idFile=".$this->_id);
				parent::delete();
			}
			//// Supprime une ou toutes les versions du fichier
			else
			{
				////	Récupère toutes les versions du fichier
				$versionList=$this->getVersions();
				////	Si on supprime la dernière version d'un fichier : update les propriétés principales du fichier (nom/taille/etc) avec celles l'avant dernière version
				if($deleteVersion==$versionList[0]["dateCrea"] && isset($versionList[1]))  {Db::query("UPDATE ap_file SET name=".Db::format($versionList[1]["name"]).", octetSize=".Db::format($versionList[1]["octetSize"]).", dateModif=".Db::format($versionList[1]["dateCrea"]).", _idUserModif=".$versionList[1]["_idUser"]." WHERE _id=".$this->_id);}
				////	Supprime les versions demandées du fichier : sur le disque puis dans la table "ap_fileVersion"
				foreach($versionList as $tmpVersion){
					if($deleteVersion=="all" || $deleteVersion==$tmpVersion["dateCrea"]){
						$tmpFilePath=$this->filePath($tmpVersion["dateCrea"]);
						if(is_file($tmpFilePath))  {File::rm($tmpFilePath);}//Toujours controler via "is_file()"!!
						Db::query("DELETE FROM ap_fileVersion WHERE _idFile=".$this->_id." AND realName=".Db::format($tmpVersion["realName"]));
					}
				}
				////	Supprime toutes les versions OU la dernière version du fichier : efface auquel cas la vignette, puis efface définitivement le fichier
				if($deleteVersion=="all" || count($versionList)==1){
					if($this->hasThumb())  {File::rm($this->getThumbPath());}
					parent::delete();
				}
				////	Sinon si ya une vignette du fichier : on recharge la liste des versions et update la vignette
				elseif($this->hasThumb()){
					$this->getVersions(true);
					$this->createThumb();
				}
			}
		}
	}
}