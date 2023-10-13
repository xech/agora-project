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
	//Taille maxi de la totalité des fichiers attachés aux mails (cf. "message_size_limit" du /etc/postfix/mail.cf à 25Mo)
	const mailMaxFilesSize=26214400;
	//init les types de fichiers
	private static $_fileTypes=null;

	/*******************************************************************************************
	 * CHMOD SUR UN FICHIER || CHMOD RÉCURSIF SUR UN DOSSIER
	 *******************************************************************************************/
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
	
	/*******************************************************************************************
	 * EXTENSION DU FICHIER -> SANS LE POINT !
	 *******************************************************************************************/
	public static function extension($fileName)
	{
		return strtolower(pathinfo($fileName,PATHINFO_EXTENSION));
	}

	/*******************************************************************************************
	 * TABLEAU DES EXTENSIONS DE FICHIERS
	 *******************************************************************************************/
	public static function fileTypes($typeKey)
	{
		//Init les types de fichiers en fonction de leur extension
		if(static::$_fileTypes===null)
		{
			static::$_fileTypes=[
				"image"=>["jpg","jpeg","png","gif","bmp","wbmp","tif","tiff","svg"],
				"imageBrowser"=>["jpg","jpeg","png","gif","svg"],
				"imageResize"=>["jpg","jpeg","png"],
				"textEditor"=>["doc","docx","odt","sxw"],
				"text"=>["txt","text","rtf"],
				"pdf"=>["pdf"],
				"pdfTxt"=>["pdf","txt","text"],
				"calc"=>["xls","xlsx","ods","sxc"],
				"presentation"=>["ppt","pptx","pps","ppsx","odp","sxi"],
				"archive"=>["zip","rar","7z","tar","gz","tgz","iso"],
				"flash"=>["swf"],
				"html"=>["htm","html"],
				"web"=>["htm","html","js","css","php","asp","jsp"],
				"autocad"=>["dwg","dxf"],
				"executable"=>["exe","bat","dat","dll","msi"],
				"audio"=>["mp3","flac","wma","wav","aac","mid"],
				"mp3"=>["mp3"],
				"video"=>["mp4","webm","ogg","mkv","flv","avi","qt","mov","wmv","mpg"],
				"videoPlayer"=>["mp4","webm"],
				"mediaPlayer"=>["mp4","webm","mp3"],
				"attachedFileInsert"=>["jpg","jpeg","png","gif","mp4","webm","mp3"],							//Fichiers joints intégrable dans l'éditeur TinyMce
				"forbiddenExt"=>["php","phtml","js","htaccess","sh","so","bin","cgi","rpm","deb","bat","exe"],	//Fichiers non autorisés
			];
		}
		//renvoie les fichiers correspondant aux types
		return (array_key_exists($typeKey,static::$_fileTypes))  ?  static::$_fileTypes[$typeKey]  :  [];
	}

	/*******************************************************************************************
	 * CONTROLE LE TYPE DE FICHIER EN FONCTION DE SON EXTENSION
	 *******************************************************************************************/
	public static function isType($typeKey, $fileName)
	{
		return in_array(self::extension($fileName), self::fileTypes($typeKey));
	}

	/*******************************************************************************************************
	 * CONTROLE L'UPLOAD D'UN NOUVEAU FICHIER $_FILES : TYPE DE FICHIER AUTORISÉ & ESPACE DISQUE SUFFISANT ?
	 *******************************************************************************************************/
	public static function uploadControl($tmpFile, $tmpDatasFolderSize=null)
	{
		//Controle l'accès au fichier
		if($tmpFile["error"]==0 && is_file($tmpFile["tmp_name"]))
		{
			////	Init le $datasFolderSize
			$datasFolderSize=(!empty($tmpDatasFolderSize))  ?  $tmpDatasFolderSize  :  self::datasFolderSize();
			////	Récupère le type mime du fichier
			$isForbiddenMimeType=preg_match("/(php|javascript|shell|binary|executable|msdownload|debian)/i", mime_content_type($tmpFile["tmp_name"]));
			////	Controle le type du fichier  &&  S'il a été uploadé via HTTP POST  &&  L'espace disque disponible
			if(self::isType("forbiddenExt",$tmpFile["name"]) || $isForbiddenMimeType==true)					{Ctrl::notify(Txt::trad("NOTIF_fileVersionForbidden")." : ".$tmpFile["name"]);  return false;}
			elseif(is_uploaded_file($tmpFile["tmp_name"])==false && Req::param("tmpFolderName")==false)		{Ctrl::notify("NOTIF_fileOrFolderAccess");  return false;}
			elseif(($datasFolderSize+$tmpFile["size"]) > limite_espace_disque)								{Ctrl::notify("NOTIF_diskSpace");  return false;}
			else																							{return true;}
		}
	}

	/************************************************************************************************************************************
	 * DOWNLOAD UN FICHIER :  DU DOSSIER /DATAS (cf. $filePath)  ||  GÉNÉRÉ À LA VOLÉE (cf. $fileContent. Ex: listing logs/contacts)
	 ************************************************************************************************************************************/
	public static function download($fileName, $filePath=null, $fileContent=null, $exitScript=true)
	{
		////	Old mobileApp sous Cordova : annule le download pour ne pas bloquer InAppBrowser, et lancer le download via le browser system (InAppBrowser et le browser system utilisent les mêmes cookies: "isMobileApp()" renvoie donc toujours "true")
		if(Req::isMobileApp() && Req::isParam("fromMobileApp")==false)  {echo "<script>  setTimeout(function(){ window.history.back(); },1000);  </script>";}
		////	Fichier généré à la volée ($fileContent) OU Fichier dans le dossier DATAS
		elseif(!empty($fileContent) || is_file($filePath))
		{
			////	Augmente la duree du script (sauf safemode)
			@set_time_limit(120);
			////	Headers
			header("Content-Type: application/octet-stream");
			header("Cache-Control: no-store");
			header("Content-Transfer-Encoding: Binary"); 
			header("Content-Disposition: attachment; filename=\"".Txt::clean($fileName)."\"");
			if(!empty($filePath))  {header("Content-Length: ".filesize($filePath));}
			////	Fichier généré à la volée (ex: csv des logs)
			if(!empty($fileContent)){
				echo $fileContent;
			}
			////	Download d'un fichier > 20Mo par tranche de 1Mo, pour pas bloquer la navigation durant le download (tester en prod!)
			elseif(filesize($filePath) > (self::sizeMo*20)){
				session_write_close();
				$handle=fopen($filePath,"rb");
				while(!feof($handle)){
					print fread($handle,self::sizeMo);
					flush();
					ob_flush();
				}
				fclose($handle);
				ob_end_clean();
			}
			////	Download direct du fichier
			else{
				readfile($filePath);
			}
			////	Fin de script (action par défaut, sauf si on supprime un fichier tmp par exemple)
			if($exitScript==true)  {exit;}
		}
	}

	/*******************************************************************************************
	 * AFFICHE UN FICHIER DANS LE BROWSER (masque le path reel du fichier img/pdf/etc)
	 *******************************************************************************************/
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
			header('Cache-Control: no-store');
			header('Content-Transfer-Encoding: binary');
			header('Content-Length: '.filesize($filePath));
			readfile($filePath);
			exit;
		}
	}

	/*******************************************************************************************
	 * TAILLE D'UN DOSSIER, EN OCTETS  (fonction récursive. Alternative: "du -sb")
	 *******************************************************************************************/
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

	/*******************************************************************************************
	 * TAILLE DU PATH_DATAS
	 *******************************************************************************************/
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

	/*******************************************************************************************
	 * RETOURNE UNE VALEUR EN OCTETS, À PARTIR D'UNE VALEUR EN Go/Mo/Ko	(ex: 10Mo)
	 *******************************************************************************************/
	public static function getBytesSize($sizeText)
	{
		if(preg_match("/(g|go)$/i",$sizeText))		{return str_ireplace(["go","g"],"",$sizeText) * self::sizeGo;}
		elseif(preg_match("/(m|mo)$/i",$sizeText))	{return str_ireplace(["mo","m"],"",$sizeText) * self::sizeMo;}
		elseif(preg_match("/(k|ko)$/i",$sizeText))	{return str_ireplace(["ko","k"],"",$sizeText) * self::sizeKo;}
		else										{return $sizeText;}
	}

	/*******************************************************************************************
	 * RETOURNE LA TAILLE D'UN FICHIER/DOSSIER À PARTIR D'UNE VALEUR EN OCTETS ..OU D'UN TEXTE (ex: 10Mo)
	 *******************************************************************************************/
	public static function displaySize($size, $displayLabel=true)
	{
		$bytesSize=self::getBytesSize($size);
		if($bytesSize>=self::sizeGo)		{$size=round(($bytesSize/self::sizeGo),2);	$tradLabel="gigaOctet";}
		elseif($bytesSize>=self::sizeMo)	{$size=round(($bytesSize/self::sizeMo),1);	$tradLabel="megaOctet";}
		else								{$size=round(($bytesSize/self::sizeKo),0);	$tradLabel="kiloOctet";}
		return ($displayLabel==true)  ?  $size." ".Txt::trad($tradLabel)  :  $size;
	}

	/*******************************************************************************************
	 * RETOURNE LA TAILLE MAX DES FICHIERS UPLOADÉS : EN OCTETS
	 *******************************************************************************************/
	public static function uploadMaxFilesize($message=false)
	{
		$upload_max_filesize=(int)self::getBytesSize(ini_get("upload_max_filesize"));
		if($message=="error")	{return Txt::trad("FILE_fileSizeError")." :<br>".Txt::trad("FILE_fileSizeLimit")." ".self::displaySize($upload_max_filesize);}
		if($message=="info")	{return Txt::trad("FILE_fileSizeLimit")." ".self::displaySize($upload_max_filesize);}
		else					{return $upload_max_filesize;}
	}

	/*******************************************************************************************
	 * SUPPRESSION D'UN FICHIER/DOSSIER SUR LE DISQUE
	 *******************************************************************************************/
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

	/*******************************************************************************************
	 * VERIFIE SI UN DOSSIER OU UN FICHIER EST ACCESSIBLE EN ÉCRITURE
	 *******************************************************************************************/
	public static function isWritable($targetPath, $errorMessage=true)
	{
		if(file_exists($targetPath) && is_writable($targetPath) && $targetPath!=PATH_MOD_FILE)	{return true;}
		else{
			if($errorMessage==true)  {Ctrl::notify(Txt::trad("NOTIF_fileOrFolderAccess")." : ".str_replace(PATH_MOD_FILE,"",$targetPath));}
			return false;
		}
	}

	/*******************************************************************************************
	 * REDIMENSIONNE UNE IMAGE ("imgSrc.png"= "imgDest.jpg")
	 *******************************************************************************************/
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
					$imgTmp=new Imagick($imgPathSrc);
					//Vérifie s'il faut réorienter l'image
					$imgOrientation=$imgTmp->getImageOrientation();
					if($imgOrientation==6)		{$imgRotation=90;}
					elseif($imgOrientation==8)	{$imgRotation=-90;}
					if(isset($imgRotation)){
						list($newWidth,$newHeight)=[$newHeight,$newWidth];//Switch le width et height?
						$imgTmp->rotateImage("#000",$imgRotation);
					}
					//Compresse && Resize && enregistre l'image
					$imgTmp->setImageCompressionQuality($compressionQuality); 
					$imgTmp->thumbnailImage($newWidth, $newHeight);
					$imgTmp->writeImage($imgPathDest);
					$imgTmp->clear();
					$imgTmp->destroy();
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

	/*******************************************************************************************
	 * GENÈRE UNE ARCHIVE ZIP
	 *******************************************************************************************/
	public static function downloadArchive($filesList, $archiveName)
	{
		if(!empty($filesList))
		{
			//temps d'execution
			@set_time_limit(240);//disabled en safemode
			//Création de l'archive
			$archiveTmpPath=tempnam(self::getTempDir(),"archive".uniqid()).".zip";
			$zip=new ZipArchive();
			$zip->open($archiveTmpPath, ZipArchive::CREATE);
			//Ajout de chaque fichier à l'archive (avec "realPath" & un "zipPath") ou un dossier vide (avec "emptyFolderZipPath")
			foreach($filesList as $tmpFile){
				if(isset($tmpFile["emptyFolderZipPath"]))	{$zip->addEmptyDir($tmpFile["emptyFolderZipPath"]);}
				elseif(is_file($tmpFile["realPath"]))		{$zip->addFile($tmpFile["realPath"],$tmpFile["zipPath"]);}
			}
			//Ferme l'archive, Download le zip, puis le supprime
			$zip->close();
			self::download($archiveName, $archiveTmpPath, null, false);
			self::rm($archiveTmpPath);
		}
	}

	/*******************************************************************************************
	 * CONTROLE LE DOWNLOAD D'UNE GROSSE ARCHIVE (SAV & CO) : CONTROLE DE L'HORAIRE POUR NE PAS SATURER LE SERVEUR EN HEURE DE POINTE
	 *******************************************************************************************/
	public static function archiveSizeControl($archiveSize)
	{
		$archiveSizeControl=true;
		$limitSize=(self::sizeGo*2);	//2Go max (tester avec un 'top' du systeme)
		$disabledBegin=9;				//debut plage horaire de limitation
		$disabledEnd=18;				//fin plage horaire de limitation
		if($archiveSizeControl==true && date("G") > $disabledBegin && date("G") < $disabledEnd && (int)$archiveSize > $limitSize){
			$alertLabel=str_replace("--ARCHIVE_SIZE--", self::displaySize($archiveSize), Txt::trad("downloadAlert"))." ".$disabledEnd."H";
			Ctrl::notify($alertLabel, "warning");
			Ctrl::redir("?ctrl=".Req::$curCtrl);//Redirige en page principale du module (ne pas mettre de "action")
		}
	}

	/*******************************************************************************************
	 * DOSSIER TEMPORAIRE DU SYSTÈME "/tmp"  OU  DOSSIER TEMPORAIRE "./DATAS/tmp"
	 *******************************************************************************************/
	public static function getTempDir()
	{
		//Dossier temporaire du systeme  ||  Dossier temporaire dans /DATAS
		if(Req::isHost()){
			$tmpDir=sys_get_temp_dir();
			if(is_dir(PATH_TMP) && is_writable(PATH_TMP))  {self::rm(PATH_TMP);}//suppr l'ancien PATH_TMP si besoin
		}else{
			$tmpDir=rtrim(PATH_TMP,"/");//Path sans le dernier "/"
			if(!is_dir($tmpDir))  {mkdir($tmpDir,0770);}//Créé si besoin le dossier
		}
		//Supprime les fichiers temporaires de plus de 48h (fichiers tjs présents si le script est interrompu)
		foreach(scandir($tmpDir) as $tmpFileName){
			$tmpFile=$tmpDir."/".$tmpFileName;
			if(in_array($tmpFileName,['.','..'])==false && is_file($tmpFile) && (time()-filemtime($tmpFile))>172800)  {self::rm($tmpFile);}
		}
		//Renvoie le path
		return $tmpDir;
	}

	/*******************************************************************************************
	 * MODIFIE LE FICHIER "config.inc.php"
	 *******************************************************************************************/
	public static function updateConfigFile($constantsEdit=null, $constantsDelete=null)
	{
		//Fichier accessible en écriture?
		$configFilePath=PATH_DATAS."config.inc.php";
		if(!is_writable($configFilePath))  {throw new Exception("config.inc.php not writable/accessible");}
		else
		{
			//Récupère le fichier sous forme de tableau de lignes
			$configLines=file($configFilePath);
			if(count($configLines)>1)
			{
				//Liste des constantes modifiées
				$constantsModified=[];
				//// Modifie ou supprime des constantes du fichier
				foreach($configLines as $lineKey=>$lineValue)
				{
					//Modifie "limite_nb_utils" : agora v2
					if(stristr($lineValue,"limite_nb_utils"))  {$lineValue=str_replace("limite_nb_utils","limite_nb_users",$lineValue);}
					//Supprime la constante de la ligne courante ?
					if(!empty($constantsDelete)){
						foreach($constantsDelete as $constName){
							if(!empty($constName) && stristr($lineValue,'"'.$constName.'"'))  {$lineValue="";}
						}
					}
					//Modifie la constante de la ligne courante ?
					if(!empty($constantsEdit)){
						foreach($constantsEdit as $constName=>$constValue){
							if(stristr($lineValue,$constName)){
								if($constName=="db_password")						{$constValue="'".addslashes($constValue)."'";}	//guillemet simple pour les passwords car n'interprete pas les "$" comme des variables
								elseif($constValue===true || $constName=="true")	{$constValue='true';}							//booléen sans guillemet
								elseif($constValue===false || $constName=="false")	{$constValue='false';}							//idem
								else												{$constValue='"'.$constValue.'"';}				//guillemet double
								$lineValue="define(\"".$constName."\", ".$constValue.");\n";										//modif la constante
								$constantsModified[]=$constName;																	//ajoute à la liste des constantes modifiées
							}
						}
					}
					//Enregistre la ligne
					$configLines[$lineKey]=$lineValue;
				}
				//// Ajoute des constantes au fichier
				if(!empty($constantsEdit)){
					foreach($constantsEdit as $constName=>$constValue){
						if(!in_array($constName,$constantsModified)){
							if($constName=="db_password")						{$constValue="'".addslashes($constValue)."'";}	//guillemet simple pour les passwords car n'interprete pas les "$" comme des variables
							elseif($constValue===true || $constName=="true")	{$constValue='true';}							//booléen sans guillemet
							elseif($constValue===false || $constName=="false")	{$constValue='false';}							//idem
							else												{$constValue='"'.$constValue.'"';}				//guillemet double
							$lineValue="define(\"".$constName."\", ".$constValue.");\n";										//modif la constante
						}
					}
				}
				////	ON REMPLACE LE FICHIER !
				$fileContent=implode("", $configLines);
				$fp=fopen($configFilePath, "w");
				fwrite($fp, $fileContent);
				fclose($fp);
			}
		}
	}
}