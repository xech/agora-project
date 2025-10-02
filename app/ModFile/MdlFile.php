<?php
/**
* This file is part of the Agora-Project Software package
*
* @copyleft Agora-Project <https://www.agora-project.net>
* @license GNU General Public License (GPL-2.0)
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

	/********************************************************************************************************
	 * LISTE DES VERSIONS DU FICHIER
	 ********************************************************************************************************/
	public function getVersions($forceUpdate=false)
	{
		if($this->_versions===null || $forceUpdate==true)
			{$this->_versions=Db::getTab("SELECT * FROM ap_fileVersion WHERE _idFile=".$this->_id." ORDER BY dateCrea desc");}//"ORDER BY" place la dernière version en 1er
		return $this->_versions;
	}

	/********************************************************************************************************
	 * DERNIÈRE VERSION DU FICHIER /  VERSION À UNE DATE DONNÉE
	 ********************************************************************************************************/
	public function getVersion($dateCrea=null)
	{
		foreach($this->getVersions() as $tmpVersion){
			if($tmpVersion["dateCrea"]==$dateCrea || empty($dateCrea))    {return $tmpVersion;  break;}
		}
	}

	/********************************************************************************************************
	 * CHEMIN DU FICHIER SUR LE DISQUE (DERNIÈRE VERSION / VERSION PRÉCISÉ AVEC "$dateCrea")
	 ********************************************************************************************************/
	public function filePath($dateCrea=null)
	{
		$curVersion=$this->getVersion($dateCrea);
		return $this->containerObj()->folderPath("real").$curVersion["realName"];
	}

	/********************************************************************************************************
	 * LIEN POUR AJOUTER DES FICHIERS
	 * $urlParams spécifié pour une nouvelle version de fichier
	 ********************************************************************************************************/
	public static function urlAddFiles($urlParams=null)
	{
		//Par défaut on spécifie le dossier courant
		if(empty($urlParams))  {$urlParams="typeId=file&_idContainer=".Ctrl::$curContainer->_id;}
		return "?ctrl=".static::moduleName."&action=AddEditFiles&".$urlParams;
	}

	/********************************************************************************************************
	 * URL DE DOWNLOAD
	 ********************************************************************************************************/
	public function urlDownload($dateCrea=null)
	{
		$urlDownload="?ctrl=file&action=FileDownload&typeId=".$this->_typeId;								//Url de base
		if(!empty($dateCrea))	{$urlDownload.="&dateCrea=".urlencode($dateCrea);}							//Download une version spécifique
		if(Req::isMobileApp())	{$urlDownload=CtrlMisc::urlMobileFileDownload($urlDownload,$this->name);}	//Download via CtrlMisc
		return $urlDownload;																				//Retourne l'Url
	}

	/********************************************************************************************************
	 * URL D'AFFICHAGE DANS LE BROWSER OU L'APPLI MOBILE (IMG/VIDEO/PDF/TXT)
	 ********************************************************************************************************/
	public function urlDisplay()
	{
		return $this->urlDownload()."&displayFile=true&extension=".File::extension($this->name);
	}

	/********************************************************************************************************
	 * VIGNETTE DU FICHIER : NOM REEL
	 ********************************************************************************************************/
	public function thumbName()
	{
		return $this->_id."_thumb.jpg";
	}

	/********************************************************************************************************
	 * VIGNETTE DU FICHIER : PDF & IMAGICK ACTIVÉ
	 ********************************************************************************************************/
	public function thumbPdfEnabled()
	{
		return (File::isType("pdf",$this->name) && extension_loaded("imagick"));
	}

	/********************************************************************************************************
	 * VIGNETTE DU FICHIER : VERIFIE L'EXISTENCE
	 ********************************************************************************************************/
	public function hasTumb()
	{
		if($this->_hasTumb===null)  {$this->_hasTumb=(strlen($this->thumbPath()) && is_file($this->thumbPath()));}
		return $this->_hasTumb;
	}

	/********************************************************************************************************
	 * VIGNETTE DU FICHIER : PATH REEL
	 ********************************************************************************************************/
	public function thumbPath()
	{
		if($this->_tumbPath===null)  {$this->_tumbPath=(File::isType("imageResize",$this->name) || $this->thumbPdfEnabled())  ?  $this->containerObj()->folderPath("real").$this->thumbName()  :  "";}
		return $this->_tumbPath;
	}

	/********************************************************************************************************
	 * VIGNETTE DU FICHIER : CRÉE OU UPDATE
	 ********************************************************************************************************/
	public function thumbEdit()
	{
		//Fichier de moins de 15Mo?
		if(filesize($this->filePath()) < (File::sizeMo*15))
		{
			//Vignette d'image ou de Pdf
			if(File::isType("imageResize",$this->name))  {return File::imageResize($this->filePath(),$this->thumbPath(),300,300,90);}
			elseif($this->thumbPdfEnabled())
			{
				try {
					$imgTmp=new Imagick();
					$imgTmp->readimage($this->filePath()."[0]"); 
					$imgTmp=$imgTmp->mergeImageLayers(Imagick::LAYERMETHOD_FLATTEN);//Pour pas avoir de background noir
					$imgTmp->writeImage($this->thumbPath());
					$imgTmp->clear();
					$imgTmp->destroy();
					return File::imageResize($this->thumbPath(),$this->thumbPath(),300);
				} catch (Exception $error){
					Ctrl::notify($this->getLabel()." : Création de vignette non permise / Thumbnail creation not allowed");	//Les .pdf avec password renvoient un "Failed to read the file [..]"
					//Ctrl::notify($error->getMessage());																	//Message d'erreur complet renvoyé par le serveur
				}
			}
		}
	}

	/********************************************************************************************************
	 * MENU DES VERSIONS DU FICHIER
	 ********************************************************************************************************/
	public function versionsMenu($displayType)
	{
		$nbVersions=count($this->getVersions());
		if($nbVersions>1){
			$nbVersionsTitle=$nbVersions." ".Txt::trad("FILE_nbFileVersions");
			$displayLabelIcon=($displayType=="label")  ?  $nbVersionsTitle  :  "<img src='app/img/file/versions.png'>";
			return '<a class="versionsMenu" onclick="lightboxOpen(\'?ctrl=file&action=FileVersions&typeId='.$this->_typeId.'\')" '.Txt::tooltip($nbVersionsTitle).'>'.$displayLabelIcon.'</a>';
		}
	}

	/********************************************************************************************************
	 * SURCHARGE : MENU CONTEXTUEL
	 ********************************************************************************************************/
	public function contextMenu($options=null)
	{
		//// ADMIN D'ESPACE : "TÉLÉCHARGÉ PAR" + LISTE DES USERS AYANT TELECHARGE LE FICHIER
		$tooltipDownloadedBy=null;
		if(Ctrl::$curUser->isSpaceAdmin() && !empty($this->downloadedBy)){
			foreach(Txt::txt2tab($this->downloadedBy) as $tmpIdUser)  {$tooltipDownloadedBy.=Ctrl::getObj("user",$tmpIdUser)->getLabel().", ";}
			$tooltipDownloadedBy=Txt::tooltip(Txt::trad("FILE_downloadedBy")." : ".trim($tooltipDownloadedBy,", "));
		}
		//// "TÉLÉCHARGER LE FICHIER" + TOOLTIP "FICHIER TÉLÉCHARGÉ X FOIS"  &&  "X VERSIONS DU FICHIER"  &&  "AJOUTER UNE NOUVELLE VERSION"
		$options["specificOptions"][]=["actionJs"=>"redir('".$this->urlDownload()."')", "iconSrc"=>"download.png", "label"=>Txt::trad("download").' &nbsp;<span class="cursorHelp" '.$tooltipDownloadedBy.'>'.str_replace('--NB_DOWNLOAD--',$this->downloadsNb,Txt::trad("FILE_downloadsNb")).'</span>'];
		if(count($this->getVersions())>1)	{$options["specificOptions"][]=["iconSrc"=>"file/versions.png", "label"=>$this->versionsMenu("label")];}//Avec le lien vers les versions (donc pas de "actionJs"..)
		if($this->editRight())				{$options["specificOptions"][]=["iconSrc"=>"plusSmall.png", "label"=>Txt::trad("FILE_addFileVersion"), "actionJs"=>"lightboxOpen('".static::urlAddFiles("addVersion=true&typeId=".$this->_typeId)."')"];}
		return parent::contextMenu($options);
	}

	/********************************************************************************************************
	 * IMAGE DU FICHIER
	 ********************************************************************************************************/
	public function typeIcon()
	{
		$pathFileTypes="app/img/file/fileType/";
		if($this->hasTumb())								{return $this->thumbPath();}
		elseif(File::isType("pdf",$this->name))				{return $pathFileTypes."pdf.png";}
		elseif(File::isType("textEditor",$this->name))		{return $pathFileTypes."textEditor.png";}
		elseif(File::isType("text",$this->name))			{return $pathFileTypes."text.png";}
		elseif(File::isType("calc",$this->name))			{return $pathFileTypes."calc.png";}
		elseif(File::isType("presentation",$this->name))	{return $pathFileTypes."presentation.png";}
		elseif(File::isType("image",$this->name))			{return $pathFileTypes."image.png";}
		elseif(File::isType("archive",$this->name))			{return $pathFileTypes."archive.png";}
		elseif(File::isType("audio",$this->name))			{return $pathFileTypes."audio.png";}
		elseif(File::isType("video",$this->name))			{return $pathFileTypes."video.png";}
		elseif(File::isType("web",$this->name))				{return $pathFileTypes."web.png";}
		else												{return $pathFileTypes."misc.png";}
	}

	/********************************************************************************************************
	 * SURCHARGE : SUPPRIME UN FICHIER (toutes ses versions OU une version spécifique)	
	 * $deleteVersion : "deleteFolder" / "all" / version précise via "dateCrea"
	 ********************************************************************************************************/
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
				if($deleteVersion==$versionList[0]["dateCrea"] && isset($versionList[1]))
					{Db::query("UPDATE ap_file SET name=".Db::format($versionList[1]["name"]).", octetSize=".Db::format($versionList[1]["octetSize"]).", dateModif=".Db::format($versionList[1]["dateCrea"]).", _idUserModif=".$versionList[1]["_idUser"]." WHERE _id=".$this->_id);}
				////	Supprime les versions demandées du fichier : sur le disque puis dans la table "ap_fileVersion"
				foreach($versionList as $tmpVersion){
					if($deleteVersion=="all" || $deleteVersion==$tmpVersion["dateCrea"]){
						$tmpFilePath=$this->filePath($tmpVersion["dateCrea"]);
						if(is_file($tmpFilePath))  {File::rm($tmpFilePath);}//Toujours controler via "is_file()" !
						Db::query("DELETE FROM ap_fileVersion WHERE _idFile=".$this->_id." AND realName=".Db::format($tmpVersion["realName"]));
					}
				}
				////	Supprime toutes les versions OU la dernière version du fichier : efface auquel cas la vignette, puis efface définitivement le fichier
				if($deleteVersion=="all" || count($versionList)==1){
					if($this->hasTumb())  {File::rm($this->thumbPath());}
					parent::delete();
				}
				////	Si ya une vignette du fichier : on recharge la liste des versions et update la vignette
				elseif($this->hasTumb()){
					$this->getVersions(true);
					$this->thumbEdit();
				}
			}
		}
	}
}