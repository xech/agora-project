<?php
/**
* This file is part of the Agora-Project Software package
*
* @copyleft Agora-Project <https://www.agora-project.net>
* @license GNU General Public License (GPL-2.0)
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
	const mailMaxFilesSizeLabel="25 Mo max";
	//init les types de fichiers
	private static $_fileTypes=null;

	/********************************************************************************************************
	 * CHMOD SUR UN FICHIER || CHMOD RÉCURSIF SUR UN DOSSIER
	 ********************************************************************************************************/
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

	/********************************************************************************************************
	 * TYPES DE FICHIERS ET LEURS EXTENSIONS ASSOCIÉES
	 ********************************************************************************************************/
	public static function fileTypes($typeKey)
	{
		////	Init les types de fichiers par catégorie
		if(static::$_fileTypes===null){
			static::$_fileTypes=[
				'pdf'=>['pdf'],
				'text'=>['txt','md','rtf','epub'],
				'textEditor'=>['doc','docx','docm','dot','dotx','odt','sxw'],
				'calc'=>['xls','xlsx','xlsm','xlt','xltx','ods','sxc'],
				'presentation'=>['ppt','pptx','pptm','pps','ppsx','odp','sxi'],
				'archive'=>['zip','rar','7z','tar','gz','tgz','bz2'],
				'image'=>['jpg','jpeg','png','gif','bmp','tif','tiff','svg','psd','ai','bmp','webp','ico'],
				'imageResize'=>['jpg','jpeg','png'],
				'video'=>['mp4','mpg','mpeg','webm','mkv','flv','avi','mov','wmv','ogv'],
				'audio'=>['mp3','flac','wma','wav','aac','mid','ogg'],
				'mp3'=>['mp3'],
				'autocad'=>['dwg','dxf'],
				'data'=>['csv','json','xml','db','dbf','mdb','accdb'],
				'misc'=>['log','ics','ical','ifb','vcs','vcf','ai','yaml','yml','gpx','kml','map','gan'],
				'editorInsert'=>['jpg','jpeg','png','gif','mp4','webm'],//Gérés via TinyMce
				'editorImage'=>['jpg','jpeg','png','gif','svg'],
				'editorVideo'=>['mp4','webm'],
				'lightboxTxt'=>['pdf','txt','text','csv','md'],//Affichés via Fancybox
				'lightboxPlayer'=>['mp4','webm','mp3']
				////'forbidden'=>['html','htm','shtml','xhtml','css','js','jse','php','phtml','php3','php4','php5','php7','php8','asp','aspx','jsp','jspx','htaccess','sql','apk','deb','dmg','rpm','sh','pkg','app','appx','ipa','cgi','conf','config','ini','exe','dll','com','bat','msi','msix','bin','cmd','run','jar','elf','so','iso','scr','vbs','vbe','pl','py','pyc','pyo','lnk','bak','swp','wsf','sys','bundle','plugin']
			];
		}
		////	Renvoie tous les fichiers : "allowed" whitelist
		if($typeKey=='allowed'){
			$fileTypes=[];
			foreach(static::$_fileTypes as $tmpExtensions)	{$fileTypes=array_merge($fileTypes,$tmpExtensions);}
			return $fileTypes;
		}
		////	Renvoie les fichiers d'un type spécifique
		elseif(array_key_exists($typeKey,static::$_fileTypes)){
			return static::$_fileTypes[$typeKey];
		}
		////	Renvoie un tableau vide
		return [];
	}

	/********************************************************************************************************
	 * EXTENSION DU FICHIER -> SANS LE POINT
	 ********************************************************************************************************/
	public static function extension($fileName)
	{
		return strtolower(pathinfo($fileName,PATHINFO_EXTENSION));
	}

	/********************************************************************************************************
	 * CONTROLE LE TYPE DE FICHIER EN FONCTION DE SON EXTENSION
	 ********************************************************************************************************/
	public static function isType($typeKey, $fileName)
	{
		return in_array(self::extension($fileName), self::fileTypes($typeKey));
	}

	/*******************************************************************************************************
	 * CONTROLE L'UPLOAD D'UN NOUVEAU FICHIER $_FILES : TYPE DE FICHIER AUTORISÉ & ESPACE DISQUE SUFFISANT ?
	 *******************************************************************************************************/
	public static function uploadControl($tmpFile, $tmpDatasFolderSize=null)
	{
		////	Controle l'accès au fichier ("clearstatcache" des fichiers temporaires : tjs avant "is_file")
		clearstatcache();
		if($tmpFile["error"]==0 && is_file($tmpFile["tmp_name"])){
			////	Init le $datasFolderSize
			$datasFolderSize=(!empty($tmpDatasFolderSize))  ?  $tmpDatasFolderSize  :  self::datasFolderSize();
			////	Type mime du fichier (en complément du controle d'extension)
			$finfo=finfo_open(FILEINFO_MIME_TYPE);
			$forbiddenTypeMime=preg_match("/(php|javascript|shell|x-sh|binary|exec|debian|perl|python|ruby|java|msdownload)/i", finfo_file($finfo,$tmpFile["tmp_name"]));
			////	Controle le type du fichier  &&  S'il a été uploadé via HTTP POST  &&  L'espace disque disponible
			if(self::isType("allowed",$tmpFile["name"])==false || $forbiddenTypeMime==true)					{Ctrl::notify($tmpFile["name"].' : '.Txt::trad("NOTIF_fileNotAllowed"));  return false;}
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
		////	Fichier généré à la volée ($fileContent) OU Fichier dans le dossier DATAS
		if(!empty($fileContent) || is_file($filePath)){
			////	Augmente la duree du script (sauf safemode)
			@set_time_limit(1800);
			////	Headers
			header('Content-Description: File Transfer');
			header('Content-Type: application/octet-stream');
			header('Content-Disposition: attachment; filename="'.Txt::clean($fileName).'"');
			header('Cache-Control: no-store');
			header('Content-Transfer-Encoding: Binary');
			if(!empty($filePath))  {header("Content-Length: ".filesize($filePath));}
			////	Fichier généré à la volée (ex: csv des logs)
			if(!empty($fileContent)){
				echo $fileContent;
			}
			////	Fichier > 50Mo : lecture asynchrone pour pas bloquer la navigation durant le download
			elseif(filesize($filePath) > (self::sizeMo*50)){
				session_write_close();
				$handle=fopen($filePath,"rb");
				while(!feof($handle)){					//Lecture jusqu'à la fin du fichier
					echo fread($handle,self::sizeMo);	//Lecture par tranche de 1Mo 
					ob_flush();							//Vide le buffer de sortie
    				flush();							//Force l'envoi au navigateur
				}
				fclose($handle);
			}
			////	Download direct du fichier
			else{
				readfile($filePath);
			}
			////	Fin de script (action par défaut, sauf si on supprime un fichier tmp par exemple)
			if($exitScript==true)  {exit;}
		}
	}

	/********************************************************************************************************
	 * AFFICHE UN FICHIER DANS LE BROWSER (MASQUE LE PATH REEL DU FICHIER)
	 ********************************************************************************************************/
	public static function display($filePath)
	{
		if(is_file($filePath)){
			if(self::isType("editorImage",$filePath))		{header('Content-Type: image/'.self::extension($filePath));}
			elseif(self::isType("pdf",$filePath))			{header('Content-Type: application/pdf');}
			elseif(self::isType("editorVideo",$filePath))	{header('Content-Type: video/mpeg');}
			elseif(self::isType("mp3",$filePath))			{header('Content-Type: audio/mpeg');}
			elseif(self::isType("text",$filePath))			{header('Content-Type: text/plain');}
			else											{header('Content-Type: application/octet-stream');}
			header('Cache-Control: no-store');
			header('Content-Transfer-Encoding: binary');
			header('Content-Length: '.filesize($filePath));
			readfile($filePath);
			exit;
		}
	}

	/********************************************************************************************************
	 * TAILLE D'UN DOSSIER, EN OCTETS  (fonction récursive. Alternative: "du -sb")
	 ********************************************************************************************************/
	public static function folderSize($folderPath)
	{
		$folderSize=0;
		$folderPath=rtrim($folderPath,"/");														//trim() la fin du path
		if(is_dir($folderPath)){																//Verif qu'il s'agisse d'un dossier
			foreach(scandir($folderPath) as $tmpFileName){										//Parcourt le dossier
				if(in_array($tmpFileName,['.','..'])==false){									//Passe les dossiers "up"
					$filePath=$folderPath."/".$tmpFileName;										//Path du fichier/dossier
					if(is_file($filePath))		{$folderSize+=filesize($filePath);}				//incrémente la taille du dossier
					elseif(is_dir($filePath))	{$folderSize+=self::folderSize($filePath);}		//lance récursivement "folderSize()"
				}
			}
		}
		return $folderSize;
	}

	/********************************************************************************************************
	 * TAILLE DU PATH_DATAS
	 ********************************************************************************************************/
	public static function datasFolderSize($refresh=false)
	{
		//Durée de la valeur gardée en cache : 5mn
		$timeout=300;
		// Récupère la taille de "PATH_DATAS" (si refresh, ou pas encore définie en session, ou si valeur expiré) 
		if($refresh==true || empty($_SESSION["datasFolderSize"]) || (time()-$_SESSION["datasFolderSizeTimeout"])>$timeout){
			$_SESSION["datasFolderSize"]=self::folderSize(PATH_DATAS);
			$_SESSION["datasFolderSizeTimeout"]=time();
		}
		// retourne la valeur
		return $_SESSION["datasFolderSize"];
	}

	/********************************************************************************************************
	 * RETOURNE UNE VALEUR EN OCTETS À PARTIR D'UNE VALEUR EN Go/Mo/Ko (inverse de "sizeLabel()")
	 ********************************************************************************************************/
	public static function getBytesSize($sizeText)
	{
		if(preg_match("/(g|go)$/i",(string)$sizeText))		{return floatval(str_ireplace(['go','g'],'',$sizeText)) * self::sizeGo;}
		elseif(preg_match("/(m|mo)$/i",(string)$sizeText))	{return floatval(str_ireplace(['mo','m'],'',$sizeText)) * self::sizeMo;}
		elseif(preg_match("/(k|ko)$/i",(string)$sizeText))	{return floatval(str_ireplace(['ko','k'],'',$sizeText)) * self::sizeKo;}
		else												{return $sizeText;}
	}

	/*********************************************************************************************************************
	 * RETOURNE LA TAILLE D'UN FICHIER/DOSSIER À PARTIR D'UNE VALEUR EN OCTETS OU D'UN TEXTE (inverse de "getBytesSize()")
	 *********************************************************************************************************************/
	public static function sizeLabel($size, $displayLabel=true)
	{
		$bytesSize=self::getBytesSize($size);
		if($bytesSize>=self::sizeGo)		{$size=round(($bytesSize/self::sizeGo),2);	$tradLabel="gigaOctet";}
		elseif($bytesSize>=self::sizeMo)	{$size=round(($bytesSize/self::sizeMo),1);	$tradLabel="megaOctet";}
		else								{$size=ceil(($bytesSize/self::sizeKo));		$tradLabel="kiloOctet";}
		return ($displayLabel==true)  ?  $size." ".Txt::trad($tradLabel)  :  $size;
	}

	/********************************************************************************************************
	 * RETOURNE LA TAILLE MAX DES FICHIERS UPLOADÉS : EN OCTETS
	 ********************************************************************************************************/
	public static function uploadMaxFilesize($message=false)
	{
		$upload_max_filesize=(int)self::getBytesSize(ini_get("upload_max_filesize"));
		if($message=="error")	{return Txt::trad("FILE_fileSizeError")." :<br>".Txt::trad("FILE_fileSizeLimit")." ".self::sizeLabel($upload_max_filesize);}
		if($message=="info")	{return Txt::trad("FILE_fileSizeLimit")." ".self::sizeLabel($upload_max_filesize);}
		else					{return $upload_max_filesize;}
	}

	/********************************************************************************************************
	 * SUPPRESSION D'UN FICHIER/DOSSIER SUR LE DISQUE
	 ********************************************************************************************************/
	public static function rm($targetPath, $errorMessage=true)
	{
		//Suppr le dernier "/"
		$targetPath=rtrim($targetPath,"/");
		//Fichier/dossier accessible en écriture
		if(file_exists($targetPath) && is_writable($targetPath) && $targetPath!=PATH_MOD_FILE){
			if(is_file($targetPath))	{return unlink($targetPath);}											//Supprime un fichier
			elseif(is_dir($targetPath)){																		//Supprime un dossier :
				foreach(scandir($targetPath) as $fileName){														//Parcourt le dossier
					if(!in_array($fileName,['.','..']))  {self::rm($targetPath."/".$fileName,$errorMessage);}	//Lance récursivement le "rm()" sur le contenu du dossier
				}
				return rmdir($targetPath);																		//Supprime enfin le dossier
			}
		}
		//Return false avec si besoin un message d'erreur
		else{
			if($errorMessage==true)  {Ctrl::notify(Txt::trad("NOTIF_fileOrFolderAccess").' -> '.str_replace(PATH_MOD_FILE,"",$targetPath));}
			return false;
		}
	}

	/********************************************************************************************************
	 * REDIMENSIONNE UNE IMAGE ("imgSrc.png"= "imgDest.jpg")
	 ********************************************************************************************************/
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

	/********************************************************************************************************
	 * GENÈRE UNE ARCHIVE ZIP A PARTIR D'UN TABLEAU DE FICHIERS
	 ********************************************************************************************************/
	public static function downloadArchive($fileList, $archiveName)
	{
		if(!empty($fileList)){
			//temps d'execution
			@set_time_limit(1800);//disabled en safemode
			//Création de l'archive
			$archiveTmpPath=tempnam(self::getTempDir(),"archive".uniqid()).".zip";
			$zip=new ZipArchive();
			$zip->open($archiveTmpPath, ZipArchive::CREATE);
			//Ajout de chaque dossier/fichier à l'archive (avec "realPath" & un "zipPath") ou un dossier vide (avec "emptyFolderZipPath")
			foreach($fileList as $tmpFile){
				if(isset($tmpFile["emptyFolderZipPath"]))  {$zip->addEmptyDir($tmpFile["emptyFolderZipPath"]);}	//Ajoute un dossier vide
				elseif(is_file($tmpFile["realPath"])){															//Fichier accessible ?
					$zip->addFile($tmpFile["realPath"],$tmpFile["zipPath"]);									//Ajoute le fichier
					$fileIndex=$zip->locateName($tmpFile["zipPath"]);											//Index du fichier dans l'archive
        			$zip->setCompressionIndex($fileIndex, ZipArchive::CM_STORE);								//Désactive la compression car bien + rapide
				}
			}
			//Ferme l'archive, Download le zip, puis le supprime
			$zip->close();
			self::download($archiveName, $archiveTmpPath, null, false);
			self::rm($archiveTmpPath);
		}
	}

	/**********************************************************************************************************************************
	 * CONTROLE LE DOWNLOAD D'UNE GROSSE ARCHIVE (SAV & CO) : CONTROLE DE L'HORAIRE POUR NE PAS SATURER LE SERVEUR EN HEURE DE POINTE
	 **********************************************************************************************************************************/
	public static function archiveSizeControl($archiveSize)
	{
		$limitSize=(self::sizeGo*10);	//10Go max en heure de pointe
		$disabledBegin=9;				//debut plage horaire limitée
		$disabledEnd  =16;				//fin   plage horaire limitée
		if(date("G") >= $disabledBegin  &&  date("G") < $disabledEnd  &&  (int)$archiveSize > (int)$limitSize){
			$alertLabel=str_replace("--ARCHIVE_SIZE--", self::sizeLabel($archiveSize), Txt::trad("downloadAlert")).' '.($disabledEnd+1).'H';
			Ctrl::notify($alertLabel, "error");
			Ctrl::redir("?ctrl=".Req::$curCtrl);//Redirige en page principale du module (ne pas mettre de "action")
		}
	}

	/********************************************************************************************************
	 * DOSSIER TEMPORAIRE DU SYSTÈME "/tmp"  OU  DOSSIER TEMPORAIRE "./DATAS/tmp"
	 ********************************************************************************************************/
	public static function getTempDir()
	{
		//Dossier temporaire du systeme  ||  Dossier temporaire dans /DATAS
		if(Req::isHost()){
			$tmpDir=sys_get_temp_dir();
		}else{
			$tmpDir=rtrim(PATH_TMP,"/");//Path sans le dernier "/"
			if(!is_dir($tmpDir))  {mkdir($tmpDir,0770);}//Créé si besoin le dossier
		}
		//Supprime les fichiers tmp de plus de 24h
		foreach(scandir($tmpDir) as $fileName){
			$filePath=$tmpDir."/".$fileName;
			if(!in_array($fileName,['.','..']) && is_file($filePath) && (time()-filemtime($filePath))>86400)  {self::rm($filePath);}
		}
		//Renvoie le path
		return $tmpDir;
	}

	/********************************************************************************************************
	 * MODIFIE LE FICHIER "config.inc.php"
	 ********************************************************************************************************/
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
					if(stristr($lineValue,"limite_nb_utils"))  {$lineValue=str_replace('limite_nb_utils','limite_nb_users',$lineValue);}
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

	/********************************************************************************************************
	 * PATH DE LA DOCUMENTATION PDF (FRANÇAISE / ANGLAISE)
	 ********************************************************************************************************/
	public static function docFile()
	{
		return 'docs/DOCUMENTATION_'.(Txt::trad("CURLANG")=='fr'?'FR':'EN').'.pdf?displayFile=true';//"displayFile" : url d'affichage dans l'appli mobile
	}
}