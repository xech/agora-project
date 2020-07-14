<?php
/**
* This file is part of the Agora-Project Software package.
*
* @copyright (c) Agora-Project Limited <https://www.agora-project.net>
* @license GNU General Public License, version 2 (GPL-2.0)
*/


/*
 * Controller of the "File" module
 */
class CtrlFile extends Ctrl
{
	const moduleName="file";
	public static $folderObjectType="fileFolder";
	public static $moduleOptions=["adminRootAddContent"];
	public static $MdlObjects=array("MdlFile","MdlFileFolder");

	/*
	 * DEFAULT ACTION
	 */
	public static function actionDefault()
	{
		////	Verify write access & Disk Space Usage
		if(Ctrl::$curUser->isAdminGeneral())
		{
			//Write access check
			if(!is_writable(Ctrl::$curContainer->folderPath("real")))
				{Ctrl::addNotif(Txt::trad("FILE_addFileAlert")." (fileFolderId=".Ctrl::$curContainer->_id.")", "warning");}
			//Disk space occupation
			$folderSize=File::folderSize(PATH_MOD_FILE);
			$diskSpacePercent=ceil(($folderSize/limite_espace_disque)*100);
			$txtBar=Txt::trad("diskSpaceUsed")." : ".$diskSpacePercent."%";
			$txtTooltip=Txt::trad("diskSpaceUsedModFile")." : ".File::displaySize($folderSize)." ".Txt::trad("from")." ".File::displaySize(limite_espace_disque);
			$vDatas["diskSpaceAlert"]=($diskSpacePercent>70) ? true : false;
			$vDatas["fillRateBar"]=Tool::percentBar($diskSpacePercent, $txtBar, $txtTooltip, $vDatas["diskSpaceAlert"]);
		}
		///	Folders & Files
		$vDatas["foldersList"]=self::$curContainer->folders();
		$vDatas["filesList"]=Db::getObjTab("file", "SELECT * FROM ap_file WHERE ".MdlFile::sqlDisplayedObjects(self::$curContainer)." ".MdlFile::sqlSort());
		foreach($vDatas["filesList"] as $fileKey=>$tmpFile)
		{
			//Link of the file label: Upload the file (in a new window because it can block the page if it's a big file)
			$tmpFile->labelLink="onclick=\"if(confirm('".Txt::trad("download",true)." ?')) window.open('".$tmpFile->urlDownloadDisplay()."','_blank');\"";
			
			//Link of the icon of the :
			if(File::isType("imageBrowser",$tmpFile->name))								{$tmpFile->iconLink="href=\"".$tmpFile->urlDownloadDisplay("display")."\" data-fancybox='images'";}	//Lightbox d'image ("href" et "data-fancybox" obligatoires)
			elseif(File::isType("pdfTxt",$tmpFile->name) && Req::isMobileApp()==false)	{$tmpFile->iconLink="onclick=\"lightboxOpen('".$tmpFile->urlDownloadDisplay("display")."');\"";}	//Lightbox de pdf ou text
			elseif(File::isType("mediaPlayer",$tmpFile->name))							{$tmpFile->iconLink="onclick=\"lightboxOpen('".$tmpFile->filePath()."');\"";}						//Lightbox de vidéo ou mp3
			else																		{$tmpFile->iconLink=$tmpFile->labelLink;}															//Telechargement direct
			
			//Tooltips and description
			$tmpFile->tooltip=Txt::trad("download")." <i>".$tmpFile->name."</i>";
			$tmpFile->iconTooltip=$tmpFile->name." - ".File::displaySize($tmpFile->octetSize);
			
			if(File::isType("url",$tmpFile->name))	{ 
				$tmpFile->tooltip = $tmpFile->description;
				$tmpFile->labelLink = $tmpFile->iconLink = "onclick=\"lightboxOpen('".$tmpFile->description."');\""; //Lightbox iFrame
			}						
			
			if(!empty($tmpFile->description))	{$tmpFile->iconTooltip.="<hr>".Txt::formatTooltip($tmpFile->description);}
			//image thumbnail/pdf
			if($tmpFile->hasThumb())
			{
				// Thumbnail class : "thumb"
				$tmpFile->hasThumbClass="hasThumb";
				//Image (not pdf): add image resolution && the "thumbLandscape" or "thumbPortrait" class
				if(File::isType("imageBrowser",$tmpFile->name)){
					list($imgWidth,$imgHeight)=getimagesize($tmpFile->filePath());
					$tmpFile->iconTooltip.=" - ".$imgWidth." x ".$imgHeight." ".Txt::trad("pixels");
					$tmpFile->thumbClass=($imgWidth>$imgHeight) ? "thumbLandscape" : "thumbPortrait";
				}
			}
			//Add the file
			$vDatas["filesList"][$fileKey]=$tmpFile;
		}
		////	Displays the view
		static::displayPage("VueIndex.php",$vDatas);
	}

	/*
	 * PLUGINS
	 */
	public static function plugin($pluginParams)
	{
		$pluginsList=self::getPluginsFolders($pluginParams,"MdlFileFolder");
		foreach(MdlFile::getPluginObjects($pluginParams) as $tmpObj)
		{
			$tmpObj->pluginModule=self::moduleName;
			$tmpObj->pluginIcon=self::moduleName."/fileType/misc.png";
			$tmpObj->pluginLabel=$tmpObj->name;
			$tmpObj->pluginTooltip=$tmpObj->containerObj()->folderPath("text");
			$tmpObj->pluginJsIcon="windowParent.redir('".$tmpObj->getUrl("container")."');";//Redir vers le dossier conteneur
			$tmpObj->pluginJsLabel="if(confirm('".Txt::trad("download",true)." ?')){windowParent.redir('".$tmpObj->urlDownloadDisplay()."');}";
			$pluginsList[]=$tmpObj;
		}
		return $pluginsList;
	}

	/*
	 * ACTION : Viewing/Downloading a file in DATAS
	 */
	public static function actionGetFile()
	{
		if(Req::isParam("targetObjId"))
		{
			//Retrieves the file and controls access rights
			$curFile=self::getTargetObj();
			if($curFile->readRight()  ||  (Req::isParam("nameMd5") && md5($curFile->name)==Req::getParam("nameMd5")))
			{
				//Displaying the file
				if(Req::isParam("display"))  {File::display($curFile->filePath());}
				//Download the file
				else
				{
					//Add the current user to "downloadedBy".
					$sqlDownloadedBy=null;
					if(Ctrl::$curUser->isUser()){
						$curFile->downloadedBy=array_unique(array_merge([Ctrl::$curUser->_id], Txt::txt2tab($curFile->downloadedBy)));//"array_unique()" car l'user courant peut avoir déjà téléchargé le fichier
						$sqlDownloadedBy=", downloadedBy=".Db::format(Txt::tab2txt($curFile->downloadedBy));
					}
					//Update the table by incrementing "downloadsNb" and if possible "downloadedBy".
					Db::query("UPDATE ".$curFile::dbTable." SET downloadsNb=(downloadsNb + 1) ".$sqlDownloadedBy." WHERE _id=".$curFile->_id);
					//Then download the file
					$curVersion=$curFile->getVersion(Req::getParam("dateCrea"));
					File::download($curVersion["name"], $curFile->filePath(Req::getParam("dateCrea")));
				}
			}
		}
	}

	/*
	 * ACTION: Download a zip archive (folder / selected items)
	 */
	public static function actionDownloadArchive()
	{
		$archiveSize=0;
		$filesList=array();
		//// Adds the selected folders to the archive
		foreach(self::getTargetObjects("fileFolder") as $curFolder)
		{
			$archiveSize+=File::folderSize($curFolder->folderPath("real"));
			$archiveName=$curFolder->containerObj()->name;
			$containerFolderPathZip=$curFolder->containerObj()->folderPath("zip");
			if($curFolder->readRight())
			{
				//Parcourt chaque dossier de l'arborescence & Ajoute chaque fichier
				foreach($curFolder->folderTree() as $tmpFolder)
				{
					$folderPathZip=substr($tmpFolder->folderPath("zip"),strlen($containerFolderPathZip));//On part du chemin du dossier courant de la page ("racine/dossier/sous-dossier" -> "sous-dossier")
					$folderFiles=Db::getObjTab("file","SELECT * FROM ap_file WHERE _idContainer=".$tmpFolder->_id);
					if(empty($folderFiles))  {$filesList[]=array("emptyFolderZipPath"=>$folderPathZip);}
					else{
						foreach($folderFiles as $tmpFile)	{$filesList[]=array("realPath"=>$tmpFile->filePath(),"zipPath"=>$folderPathZip.Txt::clean($tmpFile->name,"download"));}
					}
				}
			}
		}
		////	Adds the selected files to the archive
		foreach(self::getTargetObjects("file") as $curFile){
			$archiveSize+=$curFile->octetSize;
			$archiveName=$curFile->containerObj()->name;
			if($curFile->readRight())  {$filesList[]=array("realPath"=>$curFile->filePath(),"zipPath"=>$curFile->name);}
		}
		////	Controls the size of the archive and sends it
		if(!empty($filesList)){
			File::archiveSizeControl($archiveSize);
			File::downloadArchive($filesList,$archiveName.".zip");
		}
	}

	/*
	 * VIEW: Modifying a file
	 */
	public static function actionFileEdit()
	{
		////	Charge le fichier
		$curObj=Ctrl::getTargetObj();
		$curObj->controlEdit();
		////	Valide le formulaire
		if(Req::isParam("formValidate"))
		{
			//Enregistre & recharge le fichier + update la dernière version
			$fileName=Req::getParam("name").Req::getParam("dotExtension");
			$curObj=$curObj->createUpdate("name=".Db::format($fileName).", description=".Db::formatParam("description"));
			$lastVersion=$curObj->getVersion();
			Db::Query("UPDATE ap_fileVersion SET name=".Db::format($fileName).", description=".Db::formatParam("description")." WHERE _idFile=".$lastVersion["_idFile"]." AND dateCrea=".Db::format($lastVersion["dateCrea"]));
			//Modif contenu du fichier texte/html
			if(Req::isParam("fileContent") && Req::getParam("fileContent")!=Req::getParam("fileContentOld"))
			{
				$folderPath=$curObj->containerObj()->folderPath("real");
				$newFileRealName=$curObj->_id."_".time().Req::getParam("dotExtension");
				$fp=fopen($folderPath.$newFileRealName, "w");
				fwrite($fp, stripslashes(Req::getParam("fileContent")));//au cas ou "magic_quote_gpc" est activé..
				fclose($fp);
				Db::query("INSERT INTO ap_fileVersion SET _idFile=".$curObj->_id.", name=".Db::formatParam("name").", realName=".Db::format($newFileRealName).", description=".Db::formatParam("description").", octetSize=".(int)filesize($folderPath.$newFileRealName).", dateCrea=".Db::dateNow().", _idUser=".Ctrl::$curUser->_id);
			}
			//Notifie par mail & Ferme la page
			$curObj->sendMailNotif();
		}
		////	Affiche la vue
		else
		{
			$vDatas["curObj"]=$curObj;
			//Fichier directement éditable (text/html) ?
			if(File::isType("text",$curObj->name) || File::isType("html",$curObj->name)){
				$vDatas["fileContent"]=implode("",file($curObj->filePath()));
				if(File::isType("html",$curObj->name))  {$vDatas["initHtmlEditor"]=true;}
			}
			static::displayPage("VueFileEdit.php",$vDatas);
		}
	}

	/*
	 * VIEW: Adding files
	 */
	public static function actionAddEditFiles()	{
		////	Load Object & Access Controls
		$curObj=Ctrl::getTargetObj();
		$curObj->controlEdit();
		$folderPath=$curObj->containerObj()->folderPath("real");
		if(!is_dir($folderPath) || !is_writable($folderPath))  {Ctrl::noAccessExit(Txt::trad("NOTIF_fileOrFolderAccess")." : ".$curObj->containerObj()->name);}
		////	Validates the form
		if(Req::isParam("formValidate"))
		{
			//Init
			@set_time_limit(240);//disabled en safemode
			$newFiles=$notifFilesLabel=$notifFiles=[];
			////	RECOVER FILES ALREADY SENT WITH "PLUPLOAD"
			if(Req::getParam("uploadForm")=="uploadMultiple" && Req::isParam("tmpFolderName") && preg_match("/[a-z0-9]/i",Req::getParam("tmpFolderName")))
			{
				$tmpDirPath=sys_get_temp_dir()."/".Req::getParam("tmpFolderName")."/";
				if(is_dir($tmpDirPath)){
					foreach(scandir($tmpDirPath) as $tmpFileName){
						$tmpFilePath=$tmpDirPath.$tmpFileName;
						if(is_file($tmpFilePath))  {$newFiles[]=array("tmpPath"=>$tmpFilePath,"name"=>$tmpFileName);}
					}
				}
			}
			////	RECOVER FILES SENDED WITH $_FILE ("addFileVersion" OR "addFileSimple")
			elseif(!empty($_FILES))
			{
				foreach($_FILES as $fileKey=>$tmpFile){
					if($tmpFile["error"]==0){

						$newFiles[]=["tmpPath"=>$tmpFile["tmp_name"], "name"=>$tmpFile["name"]];//Adds the file
						if(Req::isParam("addVersion") && File::extension($curObj->name)!=File::extension($tmpFile["name"]))
							{Ctrl::addNotif(Txt::trad("NOTIF_fileVersion")." : ".File::extension($tmpFile["name"])." -> ".File::extension($tmpFile["name"]));}//Notifies if needed of the change of file extension
					}
				}
			}
	
			////	Adds each file
			$datasFolderSize=File::datasFolderSize();
			foreach($newFiles as $fileKey=>$tmpFile) {
				////	File control
				$fileSize=filesize($tmpFile["tmpPath"]);
				if(File::controleUpload($tmpFile["name"],$fileSize,$datasFolderSize))	{
					////	Checks if another file with the same name already exists
					if(Db::getVal("SELECT count(*) FROM ap_file WHERE _idContainer=".(int)$curObj->_idContainer." AND _id!=".$curObj->_id." AND name=".Db::format($tmpFile["name"]))>0)
						{Ctrl::addNotif(Txt::trad("NOTIF_fileName")." :<br><br>".$tmpFile["name"]);}
					////	Loads the file, saves its properties and reloads the object.
					$tmpObj=Ctrl::getTargetObj();// new file (create) OR new version of the file (update)
					$tmpObj=$lastObjFile=$tmpObj->createUpdate("name=".Db::format($tmpFile["name"]).", description=".Db::formatParam("description").", octetSize=".Db::format($fileSize));
					////	Adds the version of the file
					$sqlVersionFileName=$tmpObj->_id."_".time().".".File::extension($tmpFile["name"]);
					Db::query("INSERT INTO ap_fileVersion SET _idFile=".$tmpObj->_id.", name=".Db::format($tmpFile["name"]).", realName=".Db::format($sqlVersionFileName).", octetSize=".Db::format($fileSize).", description=".Db::formatParam("description").", dateCrea=".Db::dateNow().", _idUser=".Ctrl::$curUser->_id);
					// Db::query("INSERT INTO ap_fileVersion SET _idFile=".$tmpObj->_id.", name=".Db::format($newName).", realName=".Db::format($sqlVersionFileName).", octetSize=".Db::format($fileSize).", description=".Db::format($newDescription).", dateCrea=".Db::dateNow().", _idUser=".Ctrl::$curUser->_id);
					copy($tmpFile["tmpPath"], $tmpObj->filePath());//copy in the final folder (after saving the version in DB!!!)
					File::setChmod($tmpObj->filePath());
					////	Creation de vignette && ImageResize à 1600px maxi?
					$tmpObj->createThumb();
					if(File::isType("imageResize",$tmpFile["name"]) && Req::isParam("imageResize")){
						File::imageResize($tmpObj->filePath(), $tmpObj->filePath(), 1600);
						clearstatcache();//Pour mettre à jour le "filesize()"
						$fileSize=(int)filesize($tmpObj->filePath());
						Db::query("UPDATE ap_file SET octetSize=".Db::format($fileSize)." WHERE _id=".$tmpObj->_id);
						Db::query("UPDATE ap_fileVersion SET octetSize=".Db::format($fileSize)." WHERE _idFile=".$tmpObj->_id." AND realName=".Db::format($sqlVersionFileName));
					}
					if(File::isType("url",$tmpFile["name"])){
						$readFile = fopen($tmpFile["tmpPath"], "r");
						while(!feof($readFile)) {
							$line = fgets($readFile);
							$pos = strpos($line, "URL=");
							if($pos !== false){
								$newDescription = str_replace("watch?v=", "embed/", substr($line, $pos+4));
							}
						}
						fclose($readFile);
						Db::query("UPDATE ap_file SET `description`=".Db::format($newDescription)." WHERE _id=".$tmpObj->_id);
						Db::query("UPDATE ap_fileVersion SET `description`=".Db::format($newDescription)." WHERE _idFile=".$tmpObj->_id." AND realName=".Db::format($sqlVersionFileName));
					}
					////	Incrémente l'espace disque total
					$datasFolderSize+=$fileSize;
					////	Prepare la notif mail (Affiche le nom des 10 premiers fichiers ..puis le nombre de fichiers restant)
					if(count($notifFilesLabel)<10)		{$notifFilesLabel[]=$tmpObj->name.(!empty($tmpObj->description)?"<br>".$tmpObj->description:null);}
					elseif(count($notifFilesLabel)==10)	{$notifFilesLabel[]="... + ".(count($newFiles)-5)." ".Txt::trad("OBJECTfile");}
					////	Joint le fichier à la notif?
					if(Req::isParam("notifMailAddFiles"))  {$notifFiles[]=array("path"=>$tmpObj->filePath(),"name"=>$tmpObj->name);}
				}
			}
			////	Notifie par mail?  Supprime le dossier temporaire?  Ferme la page
			if(!empty($lastObjFile))  {$lastObjFile->sendMailNotif(implode("<br><br>",$notifFilesLabel), null, $notifFiles);}
			if(!empty($tmpDirPath) && is_dir($tmpDirPath))  {File::rm($tmpDirPath);}
			File::datasFolderSize(true);//Maj du nouveau "datasFolderSize" (force)
			static::lightboxClose();
		}
		////	Affiche la vue
		$vDatas["curObj"]=$curObj;
		$vDatas["tmpFolderName"]="tmpUploadFolder".uniqid(mt_rand());
		$vDatas["uploadMaxFilesize"]=File::displaySize(File::uploadMaxFilesize());
		static::displayPage("VueAddEditFiles.php",$vDatas);
	}

	/*
	 * ACTION : Upload d'un fichier temporaire via Plupload
	 */
	public static function actionUploadTmpFile()
	{
		if(Req::isParam("tmpFolderName") && preg_match("/[a-z0-9]/i",Req::getParam("tmpFolderName")) && !empty($_FILES))
		{
			//Init/Crée le dossier temporaire
			$tmpDirPath=sys_get_temp_dir()."/".Req::getParam("tmpFolderName")."/";
			if(!is_dir($tmpDirPath))  {mkdir($tmpDirPath);}
			//Vérifie l'accès au dossier temporaire && y place chaque fichier correctement uploadé
			if(is_writable($tmpDirPath)){
				foreach($_FILES as $tmpFile){
					if($tmpFile["error"]==0)  {move_uploaded_file($tmpFile["tmp_name"], $tmpDirPath.$tmpFile["name"]);}
				}
			}
		}
	}

	/*
	 * VUE : Versions d'un fichier
	 */
	public static function actionFileVersions()
	{
		$curObj=self::getTargetObj();
		$vDatas["curObj"]=$curObj;
		static::displayPage("VueFileVersions.php",$vDatas);
	}
	
	/*
	 * ACTION : Suppresion d'un version de fichier
	 */
	public static function actionDeleteFileVersion()
	{
		$curObj=self::getTargetObj();
		$curObj->delete(Req::getParam("dateCrea"));
		static::lightboxClose();
	}
}