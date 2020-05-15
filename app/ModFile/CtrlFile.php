<?php
/**
* This file is part of the Agora-Project Software package.
*
* @copyright (c) Agora-Project Limited <https://www.agora-project.net>
* @license GNU General Public License, version 2 (GPL-2.0)
*/


/*
 * Controleur du module "File"
 */
class CtrlFile extends Ctrl
{
	const moduleName="file";
	public static $folderObjectType="fileFolder";
	public static $moduleOptions=["adminRootAddContent"];
	public static $MdlObjects=array("MdlFile","MdlFileFolder");

	/*
	 * ACTION PAR DEFAUT
	 */
	public static function actionDefault()
	{
		////	Verif l'accès en écriture & Occupation d'espace disque
		if(Ctrl::$curUser->isAdminGeneral())
		{
			//Verif l'accès en écriture
			if(!is_writable(Ctrl::$curContainer->folderPath("real")))
				{Ctrl::addNotif(Txt::trad("FILE_addFileAlert")." (fileFolderId=".Ctrl::$curContainer->_id.")", "warning");}
			//Occupation d'espace disque
			$folderSize=File::folderSize(PATH_MOD_FILE);
			$diskSpacePercent=ceil(($folderSize/limite_espace_disque)*100);
			$txtBar=Txt::trad("diskSpaceUsed")." : ".$diskSpacePercent."%";
			$txtTooltip=Txt::trad("diskSpaceUsedModFile")." : ".File::displaySize($folderSize)." ".Txt::trad("from")." ".File::displaySize(limite_espace_disque);
			$vDatas["diskSpaceAlert"]=($diskSpacePercent>70) ? true : false;
			$vDatas["fillRateBar"]=Tool::percentBar($diskSpacePercent, $txtBar, $txtTooltip, $vDatas["diskSpaceAlert"]);
		}
		////	Dossiers & Fichiers
		$vDatas["foldersList"]=self::$curContainer->folders();
		$vDatas["filesList"]=Db::getObjTab("file", "SELECT * FROM ap_file WHERE ".MdlFile::sqlDisplayedObjects(self::$curContainer)." ".MdlFile::sqlSort());
		foreach($vDatas["filesList"] as $fileKey=>$tmpFile)
		{
			//Lien du label du fichier : Télécharge le fichier (dans une nouvelle fenêtre car cela peut bloquer la page si c'est un gros fichier)
			$tmpFile->labelLink="onclick=\"if(confirm('".Txt::trad("download",true)." ?')) window.open('".$tmpFile->urlDownloadDisplay()."','_blank');\"";
			//Lien de l'icone du fichier :
			if(File::isType("imageBrowser",$tmpFile->name))								{$tmpFile->iconLink="href=\"".$tmpFile->urlDownloadDisplay("display")."\" data-fancybox='images'";}	//Lightbox d'image ("href" et "data-fancybox" obligatoires)
			elseif(File::isType("pdfTxt",$tmpFile->name) && Req::isMobileApp()==false)	{$tmpFile->iconLink="onclick=\"lightboxOpen('".$tmpFile->urlDownloadDisplay("display")."');\"";}	//Lightbox de pdf ou text
			elseif(File::isType("mediaPlayer",$tmpFile->name))							{$tmpFile->iconLink="onclick=\"lightboxOpen('".$tmpFile->filePath()."');\"";}						//Lightbox de vidéo ou mp3
			else																		{$tmpFile->iconLink=$tmpFile->labelLink;}															//Telechargement direct
			//Tooltips et description
			$tmpFile->tooltip=Txt::trad("download")." <i>".$tmpFile->name."</i>";
			$tmpFile->iconTooltip=$tmpFile->name." - ".File::displaySize($tmpFile->octetSize);
			if(!empty($tmpFile->description))	{$tmpFile->iconTooltip.="<hr>".Txt::formatTooltip($tmpFile->description);}
			//Vignette d'image/pdf
			if($tmpFile->hasThumb())
			{
				//Classe de la vignette : "thumb"
				$tmpFile->hasThumbClass="hasThumb";
				//Image (pas pdf) : ajoute la résolution d'image && la classe "thumbLandscape" ou "thumbPortrait"
				if(File::isType("imageBrowser",$tmpFile->name)){
					list($imgWidth,$imgHeight)=getimagesize($tmpFile->filePath());
					$tmpFile->iconTooltip.=" - ".$imgWidth." x ".$imgHeight." ".Txt::trad("pixels");
					$tmpFile->thumbClass=($imgWidth>$imgHeight) ? "thumbLandscape" : "thumbPortrait";
				}
			}
			//Ajoute le fichier
			$vDatas["filesList"][$fileKey]=$tmpFile;
		}
		////	Affiche la vue
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
	 * ACTION : Affichage/Download d'un fichier dans le DATAS
	 */
	public static function actionGetFile()
	{
		if(Req::isParam("targetObjId"))
		{
			//Récupère le fichier et controle le droit d'accès
			$curFile=self::getTargetObj();
			if($curFile->readRight()  ||  (Req::isParam("nameMd5") && md5($curFile->name)==Req::getParam("nameMd5")))
			{
				//Affichage du fichier
				if(Req::isParam("display"))  {File::display($curFile->filePath());}
				//Download du fichier
				else
				{
					//Ajout l'user courant à "downloadedBy"
					$sqlDownloadedBy=null;
					if(Ctrl::$curUser->isUser()){
						$curFile->downloadedBy=array_unique(array_merge([Ctrl::$curUser->_id], Txt::txt2tab($curFile->downloadedBy)));//"array_unique()" car l'user courant peut avoir déjà téléchargé le fichier
						$sqlDownloadedBy=", downloadedBy=".Db::format(Txt::tab2txt($curFile->downloadedBy));
					}
					//Update la table en incrémentant "downloadsNb" et si possible "downloadedBy"
					Db::query("UPDATE ".$curFile::dbTable." SET downloadsNb=(downloadsNb + 1) ".$sqlDownloadedBy." WHERE _id=".$curFile->_id);
					//Télécharge ensuite le fichier
					$curVersion=$curFile->getVersion(Req::getParam("dateCrea"));
					File::download($curVersion["name"], $curFile->filePath(Req::getParam("dateCrea")));
				}
			}
		}
	}

	/*
	 * ACTION : Download d'une archive zip (dossier / elements sélectionnés)
	 */
	public static function actionDownloadArchive()
	{
		$archiveSize=0;
		$filesList=array();
		////	Ajoute à l'archive les dossiers sélectionnés
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
		////	Ajoute à l'archive les fichiers sélectionnés
		foreach(self::getTargetObjects("file") as $curFile){
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

	/*
	 * VUE : Modif d'un fichier
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
	 * VUE : Ajout de fichiers
	 */
	public static function actionAddEditFiles()
	{
		////	Charge l'objet & Controles d'accès
		$curObj=Ctrl::getTargetObj();
		$curObj->controlEdit();
		$folderPath=$curObj->containerObj()->folderPath("real");
		if(!is_dir($folderPath) || !is_writable($folderPath))  {Ctrl::noAccessExit(Txt::trad("NOTIF_fileOrFolderAccess")." : ".$curObj->containerObj()->name);}
		////	Valide le formulaire
		if(Req::isParam("formValidate"))
		{
			//Init
			@set_time_limit(240);//disabled en safemode
			$newFiles=$notifFilesLabel=$notifFiles=[];
			////	RECUPERE LES FICHIERS DEJA ENVOYÉS AVEC "PLUPLOAD"
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
			////	RECUPERE LES FICHIERS ENVOYÉS AVEC $_FILE ("addFileVersion" OU "addFileSimple")
			elseif(!empty($_FILES))
			{
				foreach($_FILES as $fileKey=>$tmpFile){
					if($tmpFile["error"]==0){
						$newFiles[]=["tmpPath"=>$tmpFile["tmp_name"], "name"=>$tmpFile["name"]];//Ajoute le fichier
						if(Req::isParam("addVersion") && File::extension($curObj->name)!=File::extension($tmpFile["name"]))
							{Ctrl::addNotif(Txt::trad("NOTIF_fileVersion")." : ".File::extension($tmpFile["name"])." -> ".File::extension($tmpFile["name"]));}//Notifie si besoin du changement d'extension du fichier
					}
				}
			}
	
			////	AJOUTE CHAQUE FICHIER
			$datasFolderSize=File::datasFolderSize();
			foreach($newFiles as $fileKey=>$tmpFile)
			{
				////	Controle du fichier
				$fileSize=filesize($tmpFile["tmpPath"]);
				if(File::controleUpload($tmpFile["name"],$fileSize,$datasFolderSize))
				{
					////	Vérifie si un autre fichier existe déjà avec le meme nom
					if(Db::getVal("SELECT count(*) FROM ap_file WHERE _idContainer=".(int)$curObj->_idContainer." AND _id!=".$curObj->_id." AND name=".Db::format($tmpFile["name"]))>0)
						{Ctrl::addNotif(Txt::trad("NOTIF_fileName")." :<br><br>".$tmpFile["name"]);}
					////	Charge le fichier, enregistre ses propriétés et recharge l'objet
					$tmpObj=Ctrl::getTargetObj();//nouveau fichier (create) OU nouvelle version du fichier (update)
					$tmpObj=$lastObjFile=$tmpObj->createUpdate("name=".Db::format($tmpFile["name"]).", description=".Db::formatParam("description").", octetSize=".Db::format($fileSize));
					////	Ajoute la version du fichier
					$sqlVersionFileName=$tmpObj->_id."_".time().".".File::extension($tmpFile["name"]);
					Db::query("INSERT INTO ap_fileVersion SET _idFile=".$tmpObj->_id.", name=".Db::format($tmpFile["name"]).", realName=".Db::format($sqlVersionFileName).", octetSize=".Db::format($fileSize).", description=".Db::formatParam("description").", dateCrea=".Db::dateNow().", _idUser=".Ctrl::$curUser->_id);
					copy($tmpFile["tmpPath"], $tmpObj->filePath());//copie dans le dossier final (après avoir enregistré la version en Bdd!!)
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