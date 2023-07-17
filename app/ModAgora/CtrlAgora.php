<?php
/**
* This file is part of the Agora-Project Software package.
*
* @copyright (c) Agora-Project Limited <https://www.agora-project.net>
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
		if(Ctrl::$curUser->isAdminGeneral()==false)  {self::noAccessExit();}
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
				mapApiKey=".Db::param("mapApiKey").",
				gIdentity=".Db::param("gIdentity").",
				gIdentityClientId=".Db::param("gIdentityClientId").",
				gPeopleApiKey=".Db::param("gPeopleApiKey").",
				messengerDisabled=".Db::param("messengerDisabled").",
				moduleLabelDisplay=".Db::param("moduleLabelDisplay").",
				folderDisplayMode=".Db::param("folderDisplayMode").",
				personsSort=".Db::param("personsSort").",
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
			Ctrl::notify(Txt::trad("modifRecorded"));
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
	 * RECUPERE UNE SAUVEGARDE
	 *******************************************************************************************/
	public static function actionGetBackup()
	{
		//Init
		if(Ctrl::$curUser->isAdminGeneral()==false)  {self::noAccessExit();}
		$dumpPath=Db::getDump();//Dump de la bdd!
		////	Sauvegarde de tout
		if(Req::param("typeBackup")=="all")
		{
			File::archiveSizeControl(File::datasFolderSize(true));//Controle la taille
			ini_set("max_execution_time","1200");//20mn max
			$archiveName="BackupAgora_".date("Y-m-d");
			////	Sauvegarde via "shell_exec()"
			if(Req::isHost())
			{
				$archiveTmpPath=tempnam(File::getTempDir(),"backupAgora".uniqid());
				shell_exec("cd ".PATH_DATAS."; tar -cf ".$archiveTmpPath." *");//-c=creation -f=nom du dossier source
				if(is_file($archiveTmpPath)){
					File::download($archiveName.".tar", $archiveTmpPath, null, false);
					File::rm($archiveTmpPath);
					$isArchive=true;
				}
			}
			////	Sinon sauvegarde via "downloadArchive()"
			if(empty($isArchive))  {File::downloadArchive(self::pathDatasFilesList(), $archiveName.".zip");}
		}
		////	Sauvegarde uniquement la Bdd
		else{
			$filesList=[ ["realPath"=>$dumpPath, "zipPath"=>str_replace(PATH_DATAS,"",$dumpPath)] ];
			File::downloadArchive($filesList, "BackupAgoraBdd_".date("Y-m-d").".zip");
		}
	}

	/*******************************************************************************************
	 * ARBORESCENCE DU PATH_DATAS (avec "realPath" / "zipPath" / "emptyFolderZipPath". Fonction recursive!)
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
			$tmpFileRealPath=$tmpPath."/".$tmpFileName;
			$tmpFileZipPath=str_replace(PATH_DATAS,"",$tmpFileRealPath);
			//Ajoute un fichier/dossier
			if(is_file($tmpFileRealPath))	{$filesList[]=["realPath"=>$tmpFileRealPath, "zipPath"=>$tmpFileZipPath];}
			elseif(in_array($tmpFileName,['.','..'])==false && is_dir($tmpFileRealPath)){
				$filesList[]=["emptyFolderZipPath"=>$tmpFileZipPath];
				$filesList=array_merge($filesList,self::pathDatasFilesList($tmpFileRealPath));//lancement récursif
			}
		}
		// Retourne le résultat final
		return $filesList;
	}
}