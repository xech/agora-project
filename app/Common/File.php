<?php
/**
* This file is part of the Agora-Project Software package.
*
* @copyright (c) Agora-Project Limited <https://www.agora-project.net>
* @license GNU General Public License, version 2 (GPL-2.0)
*/


/*
 * Class des fichiers
 */
class File
{
	//Tailles en octet
	const sizeGo=1073741824;
	const sizeMo=1048576;
	const sizeKo=1024;
	//Taille maxi de la totalité des fichiers attachés aux mails (20Mo)
	const mailMaxFilesSize=20971520;
	//init les types de fichiers
	private static $_fileTypes=null;

	/*
	 * Chmod sur un fichier || Chmod récursif sur un dossier
	 */
	public static function setChmod($path)
	{
		$path=trim($path,"/");
		//Chmod sur un dossier/fichier
		$chmodResult=@chmod($path,0775);
		if(is_dir($path) && $chmodResult==true){
			//chmod sur les fichiers d'un dossier
			foreach(scandir($path) as $tmpFileName){
				if(in_array($tmpFileName,['.','..'])==false)  {self::setChmod($path."/".$tmpFileName);}
			}
		}
	}
	
	/*
	 * Extension du fichier (sans le point!)
	 */
	public static function extension($fileName)
	{
		return strtolower(pathinfo($fileName,PATHINFO_EXTENSION));
	}

	/*
	 * Tableau des types de fichiers
	 */
	public static function fileTypes($typeKey)
	{
		//Init les types de fichiers en fonction de leur extension
		if(static::$_fileTypes===null)
		{
			static::$_fileTypes=array(
				"image"=>array("jpg","jpeg","png","gif","bmp","wbmp","tif","tiff","svg"),
				"imageBrowser"=>array("jpg","jpeg","png","gif"),
				"imageResize"=>array("jpg","jpeg","png"),
				"textEditor"=>array("doc","docx","odt","sxw"),
				"text"=>array("txt","text","rtf"),
				"pdf"=>array("pdf"),
				"calc"=>array("xls","xlsx","ods","sxc"),
				"presentation"=>array("ppt","pptx","pps","ppsx","odp","sxi"),
				"archive"=>array("zip","rar","7z","tar","gz","tgz","iso"),
				"flash"=>array("swf"),
				"html"=>array("htm","html"),
				"web"=>array("htm","html","js","css","php","asp","jsp"),
				"autocad"=>array("dwg","dxf"),
				"executable"=>array("exe","bat","dat","dll","msi"),
				"audio"=>array("mp3","flac","wma","wav","aac","mid"),
				"mp3"=>array("mp3"),
				"video"=>array("mp4","webm","ogg","mkv","flv","avi","qt","mov","wmv","mpg"),
				"videoPlayer"=>array("mp4","webm"),			//lightbox video
				"mediaPlayer"=>array("mp4","webm","mp3"),	//lightbox mp3
				"pdfTxt"=>array("pdf","txt","text"),		//lightbox pdf/txt
				"attachedFileInsert"=>array("jpg","jpeg","png","gif","mp4","webm","mp3"),										//Fichiers joints pouvant être intégrés dans une description (imageBrowser + mediaPlayer)
				"forbidden"=>array("htaccess","sh","so","bin","cgi","rpm","deb","bat","php","phtml","php3","php4","php5","js")	//Fichiers script interdits
			);
		}
		//renvoie les fichiers correspondant aux types
		return (array_key_exists($typeKey,static::$_fileTypes))  ?  static::$_fileTypes[$typeKey]  :  array();
	}

	/*
	 * Controle le type de fichier en fonction de son extension
	 */
	public static function isType($typeKey, $fileName)
	{
		return in_array(self::extension($fileName), self::fileTypes($typeKey));
	}
	
	/*
	 * Controle l'upload d'un nouveau fichier : type de fichier autorisé & espace disque suffisant ?
	 */
	public static function controleUpload($fileName, $fileSize, $datasFolderSize=null)
	{
		//Init le $datasFolderSize
		$datasFolderSize=(!empty($datasFolderSize))  ?  $datasFolderSize  :  self::datasFolderSize();
		////	Controle du type de fichier  &  L'espace disque disponible
		if(File::isType("forbidden",$fileName))						{Ctrl::addNotif(Txt::trad("NOTIF_fileVersionForbidden")." : ".$fileName);  return false;}
		elseif(($datasFolderSize+$fileSize) > limite_espace_disque)	{Ctrl::addNotif("NOTIF_diskSpace");  return false;}
		else														{return true;}
	}

	/*
	 * Afficher un player Audio/Video/Flash
	 */
	public static function getMediaPlayer($filePath)
	{
		if(self::isType("videoPlayer",$filePath))	{return "<br><br><video controls controlsList='nodownload' onclick='this.play()'><source src='".$filePath."' type='video/".self::extension($filePath)."'>HTML5 browser is required</video>";}
		elseif(self::isType("mp3",$filePath))		{return "<br><br><audio controls controlsList='nodownload'><source src='".$filePath."' type='audio/mp3'>HTML5 browser is required</audio>";}
		elseif(self::isType("flash",$filePath))		{return "<br><br><object type='application/x-shockwave-flash' data='".$filePath."'><param name='movie' value='".$filePath."'></object>";}
	}

	/*
	 * Telecharge un fichier
	 */
	public static function download($fileName, $filePath=null, $fileContent=null, $exitScript=true)
	{
		////	Annule le download depuis l'appli, pour ne pas bloquer InAppBrowser. Download ensuite le fichier via le browser system, avec en paramètre "fromMobileApp". Note: InAppBrowser et le browser system utilisent les mêmes cookies : "Tool::isMobileApp()" renvoie donc toujours "true"..
		if(Req::isMobileApp() && Req::isParam("fromMobileApp")==false)  {echo "<script>  setTimeout(function(){ window.history.back(); },1000);  </script>";}
		////	Fichier généré à la volée ($fileContent) OU Fichier dans le dossier DATAS
		elseif(!empty($fileContent) || is_file($filePath))
		{
			////	Augmente la duree du script (pas en safemode)
			@set_time_limit(120);
			////	Headers
			header("Content-Type: application/octet-stream");
			header("Content-Disposition: attachment; filename=\"".Txt::clean($fileName,"download")."\"");
			header("Cache-Control: no-store");
			if(!empty($filePath))  {header("Content-Length: ".filesize($filePath));}//fichier dans DATAS
			////	Download d'un fichier généré à la volée
			if(!empty($fileContent)){
				header("Content-Type: text/plain; charset=utf-8");
				echo $fileContent;
			}
			////	Download direct d'un fichier < 50 mo
			elseif(filesize($filePath)<(self::sizeMo*50)){
				readfile($filePath);
			}
			////	Download du fichier par tranche de 1mo
			else{
				session_write_close();//permet de continuer à naviguer sur le site durant le téléchargement!
				$handle=fopen($filePath,"rb");
				while(!feof($handle)){
					print fread($handle,self::sizeMo);
					flush();//Vide les tampons de sortie
					ob_flush();//Envoie le tampon de sortie
				}
				fclose($handle);
				ob_end_clean();//Détruit les données du tampon de sortie et éteint la temporisation de sortie
			}
			////	Fin de script : fonctionnement par défaut ..sauf par exemple si on veut supprimer le fichier temporaire
			if($exitScript==true)  {exit;}
		}
	}

	/*
	 * Readfile du fichier pour un affichage direct par le browser ..sans mettre le chemin réel dans le html
	 */
	public static function display($filePath)
	{
		if(is_file($filePath))
		{
			//Init le "Content-Type"
			if(self::isType("imageBrowser",$filePath))	{$contentType="image/".self::extension($filePath);}
			elseif(self::isType("pdf",$filePath))		{$contentType="application/pdf";}
			elseif(self::isType("text",$filePath))		{$contentType="text/plain;";}
			else										{$contentType="application/octet-stream";}
			//Envoie le "Header"
			header('Content-Type: '.$contentType);
			header('Content-Length: '.filesize($filePath));
			header('Cache-Control: no-store');
			header('Content-Transfer-Encoding: binary');
			header('Accept-Ranges: bytes');//Pour ne pas bloquer la lecture des balises audio/video
			readfile($filePath);
			exit;
		}
	}

	/*
	 * Taille d'un dossier, en octets  (fonction récursive. Alternative: "du -sb")
	 */
	public static function folderSize($folderPath)
	{
		$folderSize=0;
		$folderPath=rtrim($folderPath,"/");//"trimer" uniquement la fin du chemin
		// Récupère la taille d'un dossier
		if(is_dir($folderPath))
		{
			// Parcourt le dossier courant -> récupère la taille des fichiers / lance récursivement "folderSize()"
			foreach(scandir($folderPath) as $tmpFileName)
			{
				if(in_array($tmpFileName,['.','..'])==false){
					$filePath=$folderPath."/".$tmpFileName;
					if(is_file($filePath))		{$folderSize+=filesize($filePath);}
					elseif(is_dir($filePath))	{$folderSize+=self::folderSize($filePath);}
				}
			}
		}
		// Retourne le résultat
		return $folderSize;
	}

	/*
	 * Taille du PATH_DATAS
	 */
	public static function datasFolderSize($refresh=false)
	{
		//Durée de la valeur gardée en cache : 10mn
		$timeout=600;
		// Récupère la taille de "PATH_DATAS" (si refresh, ou pas encore définie en session, ou si valeur expiré) 
		if($refresh==true || empty($_SESSION["datasFolderSize"]) || (time()-$_SESSION["datasFolderSizeTimeout"])>$timeout){
			$_SESSION["datasFolderSize"]=self::folderSize(PATH_DATAS);
			$_SESSION["datasFolderSizeTimeout"]=time();
		}
		// retourne la valeur
		return $_SESSION["datasFolderSize"];
	}

	/*
	 * Retourne une valeur en octets, à partir d'une valeur en Go/Mo/Ko	(exple : 10Mo)
	 */
	public static function getBytesSize($sizeText)
	{
		if(preg_match("/(g|go)$/i",$sizeText))		{return str_ireplace(["go","g"],null,$sizeText) * self::sizeGo;}
		elseif(preg_match("/(m|mo)$/i",$sizeText))	{return str_ireplace(["mo","m"],null,$sizeText) * self::sizeMo;}
		elseif(preg_match("/(k|ko)$/i",$sizeText))	{return str_ireplace(["ko","k"],null,$sizeText) * self::sizeKo;}
		else										{return $sizeText;}
	}

	/*
	 * Affiche une taille (fichier/dossier) à partir d'une valeur en octets ..ou d'un texte (exple : 10Mo)
	 */
	public static function displaySize($size, $displayLabel=true)
	{
		$bytesSize=self::getBytesSize($size);
		if($bytesSize>=self::sizeGo)		{$size=round(($bytesSize/self::sizeGo),2);	$tradLabel="gigaOctet";}
		elseif($bytesSize>=self::sizeMo)	{$size=round(($bytesSize/self::sizeMo),1);	$tradLabel="megaOctet";}
		else								{$size=round(($bytesSize/self::sizeKo),0);	$tradLabel="kiloOctet";}
		return ($displayLabel==true)  ?  $size." ".Txt::trad($tradLabel)  :  $size;
	}

	/*
	 * Retourne la taille max des fichiers uploadés : en Octets
	 */
	public static function uploadMaxFilesize($message=false)
	{
		$upload_max_filesize=(int)self::getBytesSize(ini_get("upload_max_filesize"));
		if($message=="error")	{return Txt::trad("FILE_fileSizeError")." :<br>".Txt::trad("FILE_fileSizeLimit")." ".self::displaySize($upload_max_filesize);}
		if($message=="info")	{return Txt::trad("FILE_fileSizeLimit")." ".self::displaySize($upload_max_filesize);}
		else					{return $upload_max_filesize;}
	}

	/*
	 * Suppression d'un fichier/dossier sur le disque
	 */
	public static function rm($targetPath, $errorMessage=true)
	{
		//suppr le dernier "/"
		$targetPath=rtrim($targetPath,"/");
		//Verifie l'accès en écriture (avec message d'erreur au besoin?)
		if(self::isWritable($targetPath,$errorMessage))
		{
			//Supprime un fichier OU Supprime récursivement un dossier
			if(is_file($targetPath))	{return unlink($targetPath);}
			elseif(is_dir($targetPath) && $targetPath!=PATH_MOD_FILE)
			{
				//Supprime le contenu du dossier (récursivité)
				foreach(scandir($targetPath) as $tmpFileName){
					if(in_array($tmpFileName,['.','..'])==false)  {self::rm($targetPath."/".$tmpFileName,$errorMessage);}
				}
				//Supprime enfin le dossier
				return rmdir($targetPath);
			}
		}
	}

	/*
	 * Verifie si un dossier ou un fichier est accessible en écriture
	 */
	public static function isWritable($targetPath, $errorMessage=true)
	{
		if(file_exists($targetPath) && is_writable($targetPath) && $targetPath!=PATH_MOD_FILE)	{return true;}
		else{
			if($errorMessage==true)  {Ctrl::addNotif(Txt::trad("NOTIF_fileOrFolderAccess")." : ".str_replace(PATH_MOD_FILE,"",$targetPath));}
			return false;
		}
	}

	/*
	 * Redimensionne une image ("imgSrc.png"= "imgDest.jpg")
	 */
	public static function imageResize($imgPathSrc, $imgPathDest, $maxWidth, $maxHeight=null, $compressionQuality=85)
	{
		// Verifs de base
		if(self::isType("imageResize",$imgPathSrc) && function_exists("getimagesize") && is_file($imgPathSrc) && is_numeric($maxWidth))
		{
			////	Récupère la taile de l'image et vérifie l'intégrité du fichier
			$getimagesize=@getimagesize($imgPathSrc);
			if(is_array($getimagesize) && in_array($getimagesize[2],[IMAGETYPE_JPEG,IMAGETYPE_GIF,IMAGETYPE_PNG]))
			{
				//Init
				$resizeReturn=false;
				list($oldWidth,$oldHeight)=$getimagesize;
				////	Nouvelle taille de l'image, en fonction du cadre de référence
				if(empty($maxHeight))	{$maxHeight=$maxWidth;}//height=width
				if($oldWidth<$maxWidth && $oldHeight<$maxHeight)	{$newWidth=$oldWidth;	$newHeight=$oldHeight;}//conserve la taille
				elseif($oldWidth>$oldHeight)						{$newWidth=$maxWidth;	$newHeight=round(($maxWidth / $oldWidth) * $oldHeight);}//paysage
				else												{$newHeight=$maxHeight;	$newWidth=round(($maxHeight / $oldHeight) * $oldWidth);}//portrait
				////	Resize via la lib "Imagick"
				if(extension_loaded("imagick"))
				{
					$imgImagick=new Imagick($imgPathSrc);
					//Vérifie s'il faut réorienter l'image
					$imgOrientation=$imgImagick->getImageOrientation();
					if($imgOrientation==6)		{$imgRotation=90;}
					elseif($imgOrientation==8)	{$imgRotation=-90;}
					if(isset($imgRotation)){
						list($newWidth,$newHeight)=[$newHeight,$newWidth];//Switch le width et height?
						$imgImagick->rotateImage("#000",$imgRotation);
					}
					//Compresse && Resize && enregistre l'image
					$imgImagick->setImageCompressionQuality($compressionQuality); 
					$imgImagick->thumbnailImage($newWidth, $newHeight);
					$imgImagick->writeImage($imgPathDest);
					$resizeReturn=true;
				}
				////	Resize via la lib "GD"
				elseif(function_exists("imagecreatefromjpeg"))
				{
					// Créé une image temporaire
					$thumb=imagecreatetruecolor($newWidth,$newHeight);
					if(preg_match("/jpe?g$/i",$imgPathSrc))		{$source=imagecreatefromjpeg($imgPathSrc);}
					elseif(preg_match("/gif$/i",$imgPathSrc))	{$source=imagecreatefromgif($imgPathSrc);}
					elseif(preg_match("/png$/i",$imgPathSrc)){
						imagesavealpha($thumb,true);//conserve la transparence des .png
						$transColour=imagecolorallocatealpha($thumb, 0, 0, 0, 127);
						imagefill($thumb,0,0,$transColour);
						$source=imagecreatefrompng($imgPathSrc);
					}
					// Resize & Enregistre l'image
					if($source!=false)
					{
						imagecopyresized($thumb, $source, 0, 0, 0, 0, $newWidth, $newHeight, $oldWidth, $oldHeight);
						if(preg_match("/jpe?g$/i",$imgPathDest))	{imagejpeg($thumb,$imgPathDest,$compressionQuality);}
						elseif(preg_match("/gif$/i",$imgPathDest))	{imagegif($thumb,$imgPathDest);}
						elseif(preg_match("/png$/i",$imgPathDest))	{imagepng($thumb,$imgPathDest,8);}
						$resizeReturn=true;
					}
				}
				////	Chmod et retourne true
				if($resizeReturn==true){
					self::setChmod($imgPathDest);
					return true;
				}
			}
		}
	}

	/*
	 * Generer archive zip
	 */
	public static function downloadArchive($filesList, $archiveName)
	{
		if(!empty($filesList))
		{
			//temps d'execution
			@set_time_limit(240);//disabled en safemode
			//Création de l'archive
			$archiveTmpPath=tempnam(sys_get_temp_dir(),"archive".uniqid());
			$zip=new ZipArchive();
			$zip->open($archiveTmpPath, ZipArchive::CREATE);
			//Ajout de chaque fichier à l'archive (avec "realPath" & un "zipPath") ou un dossier vide (avec "emptyFolderZipPath")
			foreach($filesList as $tmpFile){
				if(isset($tmpFile["emptyFolderZipPath"]))	{$zip->addEmptyDir($tmpFile["emptyFolderZipPath"]);}
				elseif(is_file($tmpFile["realPath"]))		{$zip->addFile($tmpFile["realPath"],$tmpFile["zipPath"]);}
			}
			//Ferme l'archive, envoi le zip, puis le supprime
			$zip->close();
			self::download($archiveName, $archiveTmpPath, null, false);
			self::rm($archiveTmpPath);
			//Supprime les fichiers de plus de 48h dans "/tmp" (fichiers non supprimés si le script est abandonné avant la fin..)
			foreach(scandir(sys_get_temp_dir()) as $tmpFileName){
				$tmpFilePath=sys_get_temp_dir()."/".$tmpFileName;
				if(in_array($tmpFileName,['.','..'])==false && is_file($tmpFilePath) && (time()-filemtime($tmpFilePath))>172800)  {self::rm($tmpFilePath);}
			}
		}
	}

	/*
	 * Controle le download d'une grosse archive (sav & co) : controle de l'horaire pour ne pas saturer le serveur en heure de pointe
	 */
	public static function archiveSizeControl($archiveSize)
	{
		$archiveSizeControl=true;
		$limitSize=(self::sizeGo*2);//2Go max (tester avec un 'top' du systeme)
		$disabledBegin=9;//debut plage horaire de limitation
		$disabledEnd=19;//fin plage horaire de limitation
		if($archiveSizeControl==true && date("G") > $disabledBegin && date("G") < $disabledEnd && (int)$archiveSize > $limitSize){
			$alertLabel=str_replace("--ARCHIVE_SIZE--", self::displaySize($archiveSize), Txt::trad("downloadAlert"))." ".$disabledEnd."H";
			Ctrl::addNotif($alertLabel, "warning");
			Ctrl::redir("?ctrl=".Req::$curCtrl);//Redirige en page principale du module (ne pas mettre de "action")
		}
	}


	/***************************************************************************************************************************/
	/*******************************************	SPECIFIC METHODS	********************************************************/
	/***************************************************************************************************************************/
	
	/*
	 * Modif du "config.inc.php"
	 */
	public static function updateConfigFile($tabAddModifConst=null, $tabDeleteConst=null)
	{
		// FICHIER ACCESSIBLE EN ÉCRITURE?
		$configFilePath=PATH_DATAS."config.inc.php";
		if(!is_writable($configFilePath))	{throw new Exception("config.inc.php : the file doesn't exist or is not writable");}
		else
		{
			//Récupère le fichier sous forme de tableau
			$configTab=file($configFilePath);
			if(count($configTab)>1)
			{
				$modifiedConstants=array();
				////	Parcourt chaque ligne/constante du fichier
				foreach($configTab as $lineKey=>$lineValue)
				{
					//ON MODIFIE "limite_nb_utils" : AGORA V2
					if(stristr($lineValue,"limite_nb_utils"))	{$lineValue=str_replace("limite_nb_utils","limite_nb_users",$lineValue);}
					//SUPPRIME LA CONSTANTE COURANTE?
					if(!empty($tabDeleteConst)){
						foreach($tabDeleteConst as $constName){
							if(!empty($constName) && stristr($lineValue,'"'.$constName.'"'))	{$lineValue="";}
						}
					}
					//MODIF LA CONSTANTE COURANTE : SI ELLE EST DANS "$tabAddModifConst"
					if(!empty($tabAddModifConst)){
						foreach($tabAddModifConst as $constName=>$constValue){
							if(stristr($lineValue,$constName)){
								if($constValue===true || $constName=="true")		{$constValue="true";}//true sans guillemet
								elseif($constValue===false || $constName=="false")	{$constValue="false";}//false sans guillemet
								else												{$constValue="\"".$constValue."\"";}//const non booléenne entre guillemet
								$lineValue="define(\"".$constName."\", ".$constValue.");\n";
								$modifiedConstants[]=$constName;//Ajoute à la liste des constantes modifiées
							}
						}
					}
					//SUPPRIME AU BESOIN LA BALISE PHP DE FERMETURE (INUTILE ET PEUT POSER PB LORS D'AJOUT DE CONSTANTE)
					$lineValue=str_replace("?>","",$lineValue);
					// ENREGISTRE LA VALEUR FINALE DE LA LIGNE !!
					$configTab[$lineKey]=$lineValue;
				}
				////	AJOUTE LES CONSTANTES DE ABSENTES DU FICHIER (ET PAS PRECEDEMENT MODIFIES!)
				if(!empty($tabAddModifConst)){
					foreach($tabAddModifConst as $constName=>$constValue){
						//contante pas modifiée : on l'ajoute au fichier!
						if(!in_array($constName,$modifiedConstants)){
							if($constValue===true || $constName=="true")		{$constValue="true";}//true sans guillemet
							elseif($constValue===false || $constName=="false")	{$constValue="false";}//false sans guillemet
							else												{$constValue="\"".$constValue."\"";}//const non booléenne entre guillemet
							$configTab[]="define(\"".$constName."\", ".$constValue.");\n";
						}
					}
				}
				////	ON REMPLACE LE FICHIER !
				$fileContent=implode("", $configTab);
				$fp=fopen($configFilePath, "w");
				fwrite($fp, $fileContent);
				fclose($fp);
			}
		}
	}
}