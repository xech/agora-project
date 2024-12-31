<?php
/**
* This file is part of the Agora-Project Software package
*
* @copyleft Agora-Project <https://www.agora-project.net>
* @license GNU General Public License, version 2 (GPL-2.0)
*/


/*
 * Controleur du module de config de l'Agora
 */
class CtrlAgora extends Ctrl
{
	const moduleName="agora";

	/*******************************************************************************************
	 * VUE : PAGE PRINCIPALE
	 *******************************************************************************************/
	public static function actionDefault()
	{
		////	Controle d'accès
		if(Ctrl::$curUser->isGeneralAdmin()==false)  {self::noAccessExit();}
		////	Valide le formulaire
		if(Req::isParam("formValidate"))
		{
			////	Update le parametrage
			Db::query("UPDATE ap_agora SET 
				name=".Db::param("name").",
				description=".Db::param("description").",
				lang=".Db::param("lang").",
				timezone=".Db::param("timezone").",
				wallpaper=".Db::param("wallpaper").",
				logoUrl=".Db::param("logoUrl").",
				skin=".Db::param("skin").",
				footerHtml=".Db::param("footerHtml").",
				usersLike=".Db::param("usersLike").",
				usersComment=".Db::param("usersComment").",
				mapTool=".Db::param("mapTool").",
				gApiKey=".Db::param("gApiKey").",
				gIdentity=".Db::param("gIdentity").",
				gIdentityClientId=".Db::param("gIdentityClientId").",
				messengerDisplay=".Db::param("messengerDisplay").",
				moduleLabelDisplay=".Db::param("moduleLabelDisplay").",
				folderDisplayMode=".Db::param("folderDisplayMode").",
				personsSort=".Db::param("personsSort").",
				userMailDisplay=".Db::param("userMailDisplay").",
				logsTimeOut=".Db::param("logsTimeOut").",
				visioHost=".Db::param("visioHost").",
				visioHostAlt=".Db::param("visioHostAlt").",
				sendmailFrom=".Db::param("sendmailFrom").",
				smtpHost=".Db::param("smtpHost").",
				smtpPort=".Db::param("smtpPort").",
				smtpSecure=".Db::param("smtpSecure").",
				smtpUsername=".Db::param("smtpUsername").",
				smtpPass=".Db::param("smtpPass").",
				ldap_server=".Db::param("ldap_server").",
				ldap_server_port=".Db::param("ldap_server_port").",
				ldap_admin_login=".Db::param("ldap_admin_login").",
				ldap_admin_pass=".Db::param("ldap_admin_pass").",
				ldap_base_dn=".Db::param("ldap_base_dn"));
			////	Ajoute un Wallpaper
			if(isset($_FILES["wallpaperFile"]) && File::isType("imageResize",$_FILES["wallpaperFile"]["name"]))
			{
				$wallpaperName=Txt::clean($_FILES["wallpaperFile"]["name"],"max");
				$wallpaperName=str_replace(".".File::extension($wallpaperName), ".jpg", $wallpaperName);
				$wallpaperPath=PATH_WALLPAPER_CUSTOM.$wallpaperName;
				move_uploaded_file($_FILES["wallpaperFile"]["tmp_name"], $wallpaperPath);
				if($_FILES["wallpaperFile"]["size"]>409600)  {File::imageResize($wallpaperPath,$wallpaperPath,2000);}//optimise si + de 400ko
				Db::query("UPDATE ap_agora SET wallpaper=".Db::format($wallpaperName));
			}
			////	Logo du footer
			//Logo par défaut / nouveau logo : réinitialise
			if(Req::isParam("logo")==false || Req::param("logo")=="modify"){
				Db::query("UPDATE ap_agora SET logo=NULL");
				if(is_file(PATH_DATAS.Ctrl::$agora->logo))  {File::rm(PATH_DATAS.Ctrl::$agora->logo);}//pas de "pathLogoFooter()" car il renvoie toujours un logo..
			}
			//Ajoute un nouveau logo
			if(isset($_FILES["logoFile"]) && File::isType("imageResize",$_FILES["logoFile"]["name"])){
				$logoFileName="logo_thumb.".File::extension($_FILES["logoFile"]["name"]);
				move_uploaded_file($_FILES["logoFile"]["tmp_name"], PATH_DATAS.$logoFileName);
				File::imageResize(PATH_DATAS.$logoFileName, PATH_DATAS.$logoFileName, 200, 80);
				Db::query("UPDATE ap_agora SET logo=".Db::format($logoFileName));
			}
			////	Logo de la page de connexion
			//Logo par défaut / nouveau logo : réinitialise
			if(Req::isParam("logoConnect")==false || Req::param("logoConnect")=="modify"){
				Db::query("UPDATE ap_agora SET logoConnect=NULL");
				if(is_file(PATH_DATAS.Ctrl::$agora->logoConnect))  {File::rm(PATH_DATAS.Ctrl::$agora->logoConnect);}
			}
			//Ajoute un nouveau logo
			if(isset($_FILES["logoConnectFile"]) && File::isType("imageResize",$_FILES["logoConnectFile"]["name"])){
				$logoConnectFileName="logoConnect.".File::extension($_FILES["logoConnectFile"]["name"]);
				move_uploaded_file($_FILES["logoConnectFile"]["tmp_name"], PATH_DATAS.$logoConnectFileName);
				File::imageResize(PATH_DATAS.$logoConnectFileName, PATH_DATAS.$logoConnectFileName, 700, 300);
				Db::query("UPDATE ap_agora SET logoConnect=".Db::format($logoConnectFileName));
			}
			////	Test la connexion LDAP
			if(Req::isParam("ldap_server"))
				{MdlPerson::ldapConnect(Req::param("ldap_server"),Req::param("ldap_server_port"),Req::param("ldap_admin_login"),Req::param("ldap_admin_pass"),true);}
			////	Modif l'espace disque
			if(Req::isHost()==false && Req::param("limite_espace_disque")>0){
				$limite_espace_disque=File::getBytesSize(Req::param("limite_espace_disque")."G");//exprimé en Go
				File::updateConfigFile(array("limite_espace_disque"=>$limite_espace_disque));
			}
			////	Notif & Relance la page
			Ctrl::notify("modifRecorded","success");
			self::redir("index.php?ctrl=".Req::$curCtrl);
		}
		////	Supprime un wallpaper?
		if(Req::isParam("deleteCustomWallpaper")){
			$wallpaperPath=PATH_WALLPAPER_CUSTOM.Req::param("deleteCustomWallpaper");
			File::rm($wallpaperPath);
		}
		////	Affiche la page
		static::displayPage("VueIndex.php");
	}

	/*******************************************************************************************
	 * RECUPERE UNE SAUVEGARDE  DUMP / COMPLETE
	 *******************************************************************************************/
	public static function actionGetBackup()
	{
		////	Contole d'accès et Backup la Bdd
		if(Ctrl::$curUser->isGeneralAdmin()==false)  {self::noAccessExit();}
		$dumpPath=Db::getDump();
		////	Sauvegarde de tout
		if(Req::param("typeBackup")=="all")
		{
			File::archiveSizeControl(File::datasFolderSize(true));//Controle la taille de l'archive
			ini_set("max_execution_time","600");//10mn max
			$archiveName="BackupAgora_".date("Y-m-d");
			//// Sauvegarde via "shell_exec()"
			if(Req::isLinux() && function_exists('shell_exec')){
				$archiveTmpPath=tempnam(File::getTempDir(),"backupAgora".uniqid());
				shell_exec("cd ".PATH_DATAS."; tar -cf ".$archiveTmpPath." *");//-c=creation -f=nom du dossier
				if(is_file($archiveTmpPath)){
					File::download($archiveName.".tar", $archiveTmpPath, null, false);
					File::rm($archiveTmpPath);
				}
			}
			//// Sauvegarde via "pathDatasFilesList()"
			else{
				File::downloadArchive(self::pathDatasFilesList(), $archiveName.".zip");
			}
		}
		////	Sauvegarde uniquement la Bdd
		else{
			$filesList=[ ["realPath"=>$dumpPath, "zipPath"=>str_replace(PATH_DATAS,"",$dumpPath)] ];
			File::downloadArchive($filesList, "BackupAgoraBdd_".date("Y-m-d").".zip");
		}
		////	Supprime le dump de la Bdd
		File::rm($dumpPath);
	}

	/*******************************************************************************************
	 * RECUPERE UN TABLEAU DE L'ARBORESCNCE DE FICHIERS DE PATH_DATAS (fonction recursive)
	 *******************************************************************************************/
	public static function pathDatasFilesList($tmpPath=null)
	{
		//Init
		$filesList=[];
		if($tmpPath==null)	{$tmpPath=PATH_DATAS;}
		$tmpPath=rtrim($tmpPath,"/");//"trim" la fin du path
		//Liste les fichiers du path courant
		foreach(scandir($tmpPath) as $tmpFileName)
		{
			$tmpRealPath=$tmpPath."/".$tmpFileName;
			$tmpZipPath=str_replace(PATH_DATAS,"",$tmpRealPath);
			//Ajoute un fichier/dossier
			if(is_file($tmpRealPath))	{$filesList[]=["realPath"=>$tmpRealPath, "zipPath"=>$tmpZipPath];}
			elseif(is_dir($tmpRealPath) && $tmpFileName!='.' && $tmpFileName!='..'){
				$filesList[]=["emptyFolderZipPath"=>$tmpZipPath];
				$filesList=array_merge($filesList,self::pathDatasFilesList($tmpRealPath));//lancement récursif
			}
		}
		// Retourne le résultat final
		return $filesList;
	}
}