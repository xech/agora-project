<?php
/**
* This file is part of the Agora-Project Software package
*
* @copyleft Agora-Project <https://www.agora-project.net>
* @license GNU General Public License (GPL-2.0)
*/


/*
 * CONTROLEUR DU MODULE "FILE"
 */
class CtrlFile extends Ctrl
{
	const moduleName="file";
	public static $folderObjType="fileFolder";
	public static $moduleOptions=["adminRootAddContent"];
	public static $MdlObjects=["MdlFile","MdlFileFolder"];

	/*******************************************************************************************
	 * VUE : PAGE PRINCIPALE
	 *******************************************************************************************/
	public static function actionDefault()
	{
		////	Verif l'accès en écriture & Occupation d'espace disque
		if(Ctrl::$curUser->isGeneralAdmin()){
			//Verif l'accès en écriture
			if(!is_writable(Ctrl::$curContainer->folderPath("real")))
				{Ctrl::notify(Txt::trad("FILE_addFileAlert")." (fileFolderId=".Ctrl::$curContainer->_id.")", "error");}
			//Occupation d'espace disque
			$folderSize=File::folderSize(PATH_MOD_FILE);
			$barFillPercent=ceil(($folderSize/limite_espace_disque)*100);
			$barLabel=Txt::trad("diskSpaceUsed")." : ".$barFillPercent."%";
			$barTooltip=Txt::trad("diskSpaceUsedModFile")." : ".File::sizeLabel($folderSize)." ".Txt::trad("from")." ".File::sizeLabel(limite_espace_disque);
			$vDatas["diskSpaceAlert"]=($barFillPercent>70);
			$vDatas["diskSpaceBar"]=Tool::progressBar($barLabel, $barTooltip, $barFillPercent, $vDatas["diskSpaceAlert"]);
		}
		////	Dossiers & Fichiers
		$vDatas["filesList"]=Db::getObjTab("file", "SELECT * FROM ap_file WHERE ".MdlFile::sqlDisplay(self::$curContainer).MdlFile::sqlSort());
		foreach($vDatas["filesList"] as $fileKey=>$tmpFile){
			//// Lien du label/nom du fichier : Download direct
			$tmpFile->labelLink='onclick="confirmRedir(\''.$tmpFile->urlDownload().'\',\''.Txt::trad("download").' ?\')"';
			//// Lien de l'icone/vignette du fichier : Display ou Download
			if(File::isType("imageBrowser",$tmpFile->name))								{$tmpFile->iconLink='href="'.$tmpFile->urlDisplay().'" data-fancybox="images"';}	//Affiche l'image dans la fancybox
			elseif(File::isType("pdf",$tmpFile->name) && Req::isMobileApp())			{$tmpFile->iconLink='onclick="redir(\''.$tmpFile->urlDisplay().'\')"';}				//Download le pdf si isMobileApp
			elseif(File::isType("pdfTxt",$tmpFile->name) && Req::isMobileApp()==false)	{$tmpFile->iconLink='onclick="lightboxOpen(\''.$tmpFile->urlDisplay().'\')"';}		//Affiche le pdf/text dans une Lightbox 
			elseif(File::isType("mediaPlayer",$tmpFile->name))							{$tmpFile->iconLink='onclick="lightboxOpen(\''.$tmpFile->filePath().'\')"';}		//Affiche la video/mp3 dans une Lightbox
			else																		{$tmpFile->iconLink=$tmpFile->labelLink;}											//Download direct
			//// Fichier image : ajoute la résolution et la "thumbClass" en fonction de l'orientation
			if($tmpFile->thumbExist() && File::isType("imageBrowser",$tmpFile->name)){
				list($imgWidth,$imgHeight)=getimagesize($tmpFile->filePath());
				$tmpFile->imageSize=$imgWidth." x ".$imgHeight." ".Txt::trad("pixels");
				$tmpFile->thumbClass=($imgWidth>$imgHeight) ? "thumbLandscape" : "thumbPortrait";
			}
			//// Tooltip
			$tooltipTxt='<i>'.$tmpFile->name.'</i><hr>'.Txt::trad("FILE_fileSize").' : '.File::sizeLabel($tmpFile->octetSize);											//Nom & taille du fichier
			if(!empty($tmpFile->imageSize))		{$tooltipTxt.='<hr>'.Txt::trad("FILE_imageSize").' : '.$tmpFile->imageSize;}											//Taille de l'image
			if(!empty($tmpFile->description))	{$tooltipTxt.='<hr>'.$tmpFile->description;}																			//Description du fichier
			$tmpFile->labelTooltip=$tmpFile->iconTooltip='<img src="app/img/download.png"> '.Txt::trad("FILE_fileDownload").' '.$tooltipTxt;							//Tooltip de download
			if(preg_match("/redir/i",$tmpFile->iconLink)==false)  {$tmpFile->iconTooltip='<img src="app/img/search.png"> '.ucfirst(Txt::trad("show")).' '.$tooltipTxt;}	//Tooltip de l'icone pour afficher le fichier
			//Ajoute le fichier
			$vDatas["filesList"][$fileKey]=$tmpFile;
		}
		////	Affiche la vue
		static::displayPage("VueIndex.php",$vDatas);
	}

	/*******************************************************************************************
	 * PLUGINS DU MODULE
	 *******************************************************************************************/
	public static function getPlugins($params)
	{
		$pluginsList=MdlFileFolder::getPluginFolders($params);
		foreach(MdlFile::getPluginObjects($params) as $tmpObj){
			$tmpObj->pluginIcon=self::moduleName."/fileType/misc.png";
			$tmpObj->pluginLabel=$tmpObj->name;
			$tmpObj->pluginTooltip=$tmpObj->containerObj()->folderPath("text");
			$tmpObj->pluginJsIcon="window.parent.redir('".$tmpObj->getUrl()."')";//Affiche dans son dossier
			$tmpObj->pluginJsLabel="confirmRedir('".$tmpObj->urlDownload()."','".Txt::trad("download",true)." ?')";
			$pluginsList[]=$tmpObj;
		}
		return $pluginsList;
	}

	/*******************************************************************************************
	 * AFFICHAGE/DOWNLOAD D'UN FICHIER DANS LE DATAS
	 *******************************************************************************************/
	public static function actionGetFile()
	{
		if(Req::isParam("typeId"))
		{
			//Récupère le fichier et controle le droit d'accès ("nameMd5" : cf. "actionExternalGetFile()")
			$curFile=self::getObjTarget();
			if($curFile->readRight()  ||  md5($curFile->name)==Req::param("nameMd5")){
				//Affiche dans le browser ou l'appli (pdf/img/video)  OU  Download direct du fichier
				if(Req::isParam("displayFile"))   {File::display($curFile->filePath());}
				else{
					//Ajoute l'user courant à "downloadedBy"
					$sqlDownloadedBy=null;
					if(Ctrl::$curUser->isUser()){
						$curFile->downloadedBy=array_unique(array_merge([Ctrl::$curUser->_id], Txt::txt2tab($curFile->downloadedBy)));//"array_unique()" car l'user courant peut avoir déjà téléchargé le fichier
						$sqlDownloadedBy=", downloadedBy=".Db::format(Txt::tab2txt($curFile->downloadedBy));
					}
					//Update la table en incrémentant "downloadsNb" et si possible "downloadedBy"
					Db::query("UPDATE ".$curFile::dbTable." SET downloadsNb=(downloadsNb + 1) ".$sqlDownloadedBy." WHERE _id=".$curFile->_id);
					//Télécharge ensuite le fichier
					$curVersion=$curFile->getVersion(Req::param("dateCrea"));
					File::download($curVersion["name"], $curFile->filePath(Req::param("dateCrea")));
				}
			}
		}
	}

	/*******************************************************************************************
	 * DOWNLOAD D'UNE ARCHIVE ZIP (DOSSIER / ELEMENTS SÉLECTIONNÉS)
	 *******************************************************************************************/
	public static function actionDownloadArchive()
	{
		$archiveSize=0;
		$filesList=[];
		////	Ajoute à l'archive les dossiers sélectionnés
		foreach(self::getObjectsTypeId("fileFolder") as $curFolder)
		{
			$archiveSize+=File::folderSize($curFolder->folderPath("real"));
			$archiveName=(count(Req::param("objectsTypeId"))==1)  ?  $curFolder->name  :  $curFolder->containerObj()->name;
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
						foreach($folderFiles as $tmpFile)  {$filesList[]=array("realPath"=>$tmpFile->filePath(),"zipPath"=>$folderPathZip.Txt::clean($tmpFile->name));}
					}
				}
			}
		}
		////	Ajoute à l'archive les fichiers sélectionnés
		foreach(self::getObjectsTypeId("file") as $curFile){
			$archiveSize+=$curFile->octetSize;
			$archiveName=$curFile->containerObj()->name;
			if($curFile->readRight())  {$filesList[]=array("realPath"=>$curFile->filePath(),"zipPath"=>$curFile->name);}
		}
		////	Controle la taille de l'archive et l'envoie
		if(!empty($filesList)){
			File::archiveSizeControl($archiveSize);
			File::downloadArchive($filesList,$archiveName.".zip");
		}
	}

	/*******************************************************************************************
	 * VUE : MODIF D'UN FICHIER
	 *******************************************************************************************/
	public static function actionFileEdit()
	{
		////	Charge le fichier
		$curObj=Ctrl::getObjTarget();
		$curObj->editControl();
		////	Valide le formulaire
		if(Req::isParam("formValidate"))
		{
			//Enregistre & recharge le fichier + update la dernière version
			$fileName=Req::param("name").Req::param("dotExtension");
			$curObj=$curObj->createUpdate("name=".Db::format($fileName).", description=".Db::param("description"));
			$lastVersion=$curObj->getVersion();
			Db::Query("UPDATE ap_fileVersion SET name=".Db::format($fileName).", description=".Db::param("description")." WHERE _idFile=".$lastVersion["_idFile"]." AND dateCrea=".Db::format($lastVersion["dateCrea"]));
			//Notifie par mail & Ferme la page
			$curObj->sendMailNotif();
			static::lightboxClose();
		}
		////	Affiche la vue
		else
		{
			$vDatas["curObj"]=$curObj;
			static::displayPage("VueFileEdit.php",$vDatas);
		}
	}

	/*******************************************************************************************
	 * VUE : AJOUT DE FICHIERS
	 *******************************************************************************************/
	public static function actionAddEditFiles()
	{
		////	Charge l'objet & Controles d'accès
		$curObj=Ctrl::getObjTarget();
		$curObj->editControl();
		$folderPath=$curObj->containerObj()->folderPath("real");
		if(!is_dir($folderPath) || !is_writable($folderPath))  {Ctrl::noAccessExit(Txt::trad("NOTIF_fileOrFolderAccess")." : ".$curObj->containerObj()->name);}
		////	Valide le formulaire
		if(Req::isParam("formValidate"))
		{
			//Init
			@set_time_limit(240);//disabled en safemode
			$newFiles=$notifFilesLabel=$notifFiles=[];
			////	FICHIERS ENVOYÉS VIA "PLUPLOAD"
			if(Req::param("uploadForm")=="uploadMultiple" && Req::isParam("tmpFolderName") && preg_match("/[a-z0-9]/i",Req::param("tmpFolderName")))
			{
				$tmpDirPath=File::getTempDir()."/".Req::param("tmpFolderName")."/";
				if(is_dir($tmpDirPath)){
					foreach(scandir($tmpDirPath) as $tmpFileName){
						$tmpFilePath=$tmpDirPath.$tmpFileName;
						if(is_file($tmpFilePath))  {$newFiles[]=["error"=>0, "tmp_name"=>$tmpFilePath, "name"=>$tmpFileName, "size"=>filesize($tmpFilePath)];}//Parametres idem à $_FILES
					}
				}
			}
			////	FICHIERS ENVOYÉS VIA L'INPUT DE TYPE "FILE" ("addFileVersion"/"addFileSimple")
			elseif(!empty($_FILES))
			{
				foreach($_FILES as $tmpFile){
					if($tmpFile["error"]==0){
						$newFiles[]=$tmpFile;																					//Ajoute le fichier
						if(Req::isParam("addVersion") && File::extension($curObj->name)!=File::extension($tmpFile["name"]))		//Notif si besoin du changement d'extension du fichier
							{Ctrl::notify(Txt::trad("NOTIF_fileVersion")." : ".File::extension($tmpFile["name"])." -> ".File::extension($tmpFile["name"]));}
					}
				}
			}
			////	AJOUTE CHAQUE FICHIER
			$tmpDatasFolderSize=File::datasFolderSize();
			foreach($newFiles as $tmpFile)
			{
				////	Controle du fichier
				if(File::uploadControl($tmpFile,$tmpDatasFolderSize))
				{
					////	Vérifie si un autre fichier existe déjà avec le meme nom
					if(Db::getVal("SELECT count(*) FROM ap_file WHERE _idContainer=".(int)$curObj->_idContainer." AND _id!=".$curObj->_id." AND name=".Db::format($tmpFile["name"]))>0)
						{Ctrl::notify(Txt::trad("NOTIF_fileName")." :<br><br>".$tmpFile["name"]);}
					////	Charge le fichier (nouveau fichier OU nouvelle version du fichier)  &&  Enregistre ses propriétés  &&  Recharge l'objet
					$tmpObj=Ctrl::getObjTarget();
					$tmpObj=$lastObjFile=$tmpObj->createUpdate("name=".Db::format($tmpFile["name"]).", description=".Db::param("description").", octetSize=".Db::format($tmpFile["size"]));
					////	Ajoute la nouvelle version du fichier
					$sqlVersionFileName=$tmpObj->_id."_".time().".".File::extension($tmpFile["name"]);
					Db::query("INSERT INTO ap_fileVersion SET _idFile=".$tmpObj->_id.", name=".Db::format($tmpFile["name"]).", realName=".Db::format($sqlVersionFileName).", octetSize=".Db::format($tmpFile["size"]).", description=".Db::param("description").", dateCrea=".Db::dateNow().", _idUser=".Ctrl::$curUser->_id);
					copy($tmpFile["tmp_name"], $tmpObj->filePath());//copie dans le dossier final, après avoir enregistré la version en Bdd !
					File::setChmod($tmpObj->filePath());
					////	Créé ou update la vignette && Optimise si besoin l'image (1920px max)
					$tmpObj->thumbEdit();
					if(File::isType("imageResize",$tmpFile["name"]) && Req::isParam("imageResize")){
						File::imageResize($tmpObj->filePath(), $tmpObj->filePath(), 1920);
						clearstatcache();//Pour mettre à jour le "filesize()"
						$tmpFile["size"]=(int)filesize($tmpObj->filePath());
						Db::query("UPDATE ap_file SET octetSize=".Db::format($tmpFile["size"])." WHERE _id=".$tmpObj->_id);
						Db::query("UPDATE ap_fileVersion SET octetSize=".Db::format($tmpFile["size"])." WHERE _idFile=".$tmpObj->_id." AND realName=".Db::format($sqlVersionFileName));
					}
					////	Incrémente la taille temporaire de l'espace disque total
					$tmpDatasFolderSize+=$tmpFile["size"];
					////	Prepare la notif mail (Affiche le nom des 15 premiers fichiers ..puis le nombre de fichiers restant)
					if(count($notifFilesLabel)<15)		{$notifFilesLabel[]=$tmpObj->name;}
					elseif(count($notifFilesLabel)==15)	{$notifFilesLabel[]="... + ".(count($newFiles)-15)." ".Txt::trad("OBJECTfile")."s";}
					////	Joint le fichier à la notif (limite à 20 fichiers)
					if(Req::isParam("notifMailAddFiles") && count($notifFiles)<=20)  {$notifFiles[]=array("path"=>$tmpObj->filePath(),"name"=>$tmpObj->name);}
				}
			}
			////	Notifie par mail?  &&  Supprime le dossier temporaire?  &&  Maj du nouveau "datasFolderSize" (force)  &&  Ferme la page
			if(!empty($lastObjFile))  {$lastObjFile->sendMailNotif(implode("<br><br>",$notifFilesLabel), $notifFiles);}
			if(!empty($tmpDirPath) && is_dir($tmpDirPath))  {File::rm($tmpDirPath);}
			File::datasFolderSize(true);
			static::lightboxClose();
		}
		////	Affiche la vue
		$vDatas["curObj"]=$curObj;
		$vDatas["tmpFolderName"]="tmpUploadFolder".uniqid(mt_rand());
		$vDatas["uploadMaxFilesize"]=File::sizeLabel(File::uploadMaxFilesize());
		static::displayPage("VueAddEditFiles.php",$vDatas);
	}

	/*******************************************************************************************
	 * AJAX : UPLOAD D'UN FICHIER TEMPORAIRE VIA PLUPLOAD
	 *******************************************************************************************/
	public static function actionUploadTmpFile()
	{
		if(Req::isParam("tmpFolderName") && preg_match("/[a-z0-9]/i",Req::param("tmpFolderName")) && !empty($_FILES))
		{
			//Init/Crée le dossier temporaire
			$tmpDirPath=File::getTempDir()."/".Req::param("tmpFolderName")."/";
			if(!is_dir($tmpDirPath))  {mkdir($tmpDirPath);}
			//Vérifie l'accès au dossier temporaire && y place chaque fichier correctement uploadé
			if(is_writable($tmpDirPath)){
				foreach($_FILES as $tmpFile){
					if(File::uploadControl($tmpFile))  {move_uploaded_file($tmpFile["tmp_name"], $tmpDirPath.$tmpFile["name"]);}
				}
			}
		}
	}

	/*******************************************************************************************
	 * VUE : VERSIONS D'UN FICHIER
	 *******************************************************************************************/
	public static function actionFileVersions()
	{
		$curObj=self::getObjTarget();
		$vDatas["curObj"]=$curObj;
		static::displayPage("VueFileVersions.php",$vDatas);
	}
	
	/*******************************************************************************************
	 * SUPPRESION D'UNE VERSION D'UN FICHIER
	 *******************************************************************************************/
	public static function actionDeleteFileVersion()
	{
		$curObj=self::getObjTarget();
		$curObj->delete(Req::param("dateCrea"));
		static::lightboxClose();
	}
}