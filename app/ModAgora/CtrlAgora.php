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

	/*
	 * ACTION PAR DEFAUT
	 */
	public static function actionDefault()
	{
		////	Controle d'accès
		if(Ctrl::$curUser->isAdminGeneral()==false)  {self::noAccessExit();}
		////	Valide le formulaire
		if(Req::isParam("formValidate"))
		{
			////	Update le parametrage
			Db::query("UPDATE ap_agora SET 
				name=".Db::formatParam("name").",
				description=".Db::formatParam("description").",
				lang=".Db::formatParam("lang").",
				timezone=".Db::formatParam("timezone").",
				wallpaper=".Db::formatParam("wallpaper").",
				logoUrl=".Db::formatParam("logoUrl").",
				skin=".Db::formatParam("skin").",
				footerHtml=".Db::formatParam("footerHtml","editor").",
				usersLike=".Db::formatParam("usersLike").",
				usersComment=".Db::formatParam("usersComment").",
				mapTool=".Db::formatParam("mapTool").",
				mapApiKey=".Db::formatParam("mapApiKey").",
				gSignin=".Db::formatParam("gSignin").",
				gSigninClientId=".Db::formatParam("gSigninClientId").",
				gPeopleApiKey=".Db::formatParam("gPeopleApiKey").",
				messengerDisabled=".Db::formatParam("messengerDisabled").",
				moduleLabelDisplay=".Db::formatParam("moduleLabelDisplay").",
				personsSort=".Db::formatParam("personsSort").",
				logsTimeOut=".Db::formatParam("logsTimeOut").",
				visioHost=".Db::formatParam("visioHost").",
				sendmailFrom=".Db::formatParam("sendmailFrom").",
				smtpHost=".Db::formatParam("smtpHost").",
				smtpPort=".Db::formatParam("smtpPort").",
				smtpSecure=".Db::formatParam("smtpSecure").",
				smtpUsername=".Db::formatParam("smtpUsername").",
				smtpPass=".Db::formatParam("smtpPass").",
				ldap_server=".Db::formatParam("ldap_server").",
				ldap_server_port=".Db::formatParam("ldap_server_port").",
				ldap_admin_login=".Db::formatParam("ldap_admin_login").",
				ldap_admin_pass=".Db::formatParam("ldap_admin_pass").",
				ldap_base_dn=".Db::formatParam("ldap_base_dn").",
				ldap_crea_auto_users=".Db::formatParam("ldap_crea_auto_users").",
				ldap_pass_cryptage=".Db::formatParam("ldap_pass_cryptage"));
			////	Ajoute un Wallpaper
			if(isset($_FILES["wallpaperFile"]) && File::isType("imageResize",$_FILES["wallpaperFile"]["name"])){
				$wallpaperName=Txt::clean($_FILES["wallpaperFile"]["name"]);
				$wallpaperName=str_replace(".".File::extension($wallpaperName), ".thumb.jpg", $wallpaperName);
				$wallpaperPath=PATH_WALLPAPER_CUSTOM.$wallpaperName;
				move_uploaded_file($_FILES["wallpaperFile"]["tmp_name"], $wallpaperPath);
				if($_FILES["wallpaperFile"]["size"]>409600)  {File::imageResize($wallpaperPath,$wallpaperPath,2000);}//optimise si + de 400ko
				Db::query("UPDATE ap_agora SET wallpaper=".Db::format($wallpaperName));
			}
			////	Logo du footer
			//Logo par défaut / nouveau logo : réinitialise
			if(Req::isParam("logo")==false || Req::getParam("logo")=="modify"){
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
			if(Req::isParam("logoConnect")==false || Req::getParam("logoConnect")=="modify"){
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
			////	Test de connexion LDAP
			if(Req::isParam("ldap_server"))  {MdlPerson::ldapConnect(Req::getParam("ldap_server"),Req::getParam("ldap_server_port"),Req::getParam("ldap_admin_login"),Req::getParam("ldap_admin_pass"),true);}
			//Modifie l'espace disque
			if(Ctrl::isHost()==false && Req::getParam("limite_espace_disque")>0){
				$limite_espace_disque=File::getBytesSize(Req::getParam("limite_espace_disque")."G");//exprimé en Go
				File::updateConfigFile(array("limite_espace_disque"=>$limite_espace_disque));
			}
			//Notif & Relance la page
			Ctrl::addNotif(Txt::trad("modifRecorded"));
			self::redir("?ctrl=".Req::$curCtrl);
		}
		//Supprime un wallpaper?
		if(Req::isParam("deleteCustomWallpaper")){
			$wallpaperPath=PATH_WALLPAPER_CUSTOM.Req::getParam("deleteCustomWallpaper");
			File::rm($wallpaperPath);
		}
		//Affiche la page
		$vDatas["logsTimeOut"]=array(0,30,120,360,720);
		$vDatas["alertMessageBigSav"]=(File::datasFolderSize()>(File::sizeMo*500))  ?  "onclick=\"notify('".Txt::trad("AGORA_backupNotif",true)."','warning')\""  :  null;
		static::displayPage("VueIndex.php",$vDatas);
	}

	/*
	 * RECUPERE UNE SAUVEGARDE
	 */
	public static function actionGetBackup()
	{
		//Init
		if(Ctrl::$curUser->isAdminGeneral()==false)  {self::noAccessExit();}
		$dumpPath=Db::getDump();//Dump de la bdd!
		////	Sauvegarde de tout
		if(Req::getParam("typeBackup")=="all")
		{
			File::archiveSizeControl(File::datasFolderSize(true));//Controle la taille
			ini_set("max_execution_time","1200");//20mn max
			$archiveName="BackupAgora_".strftime("%Y-%m-%d");
			////	Sauvegarde via le shell
			if(Tool::linuxEnv() && Ctrl::isHost())
			{
				$archiveTmpPath=tempnam(sys_get_temp_dir(),"backupAgora".uniqid());
				shell_exec("cd ".PATH_DATAS."; tar -cf ".$archiveTmpPath." *");//-c=creation -f=nom du dossier source
				if(is_file($archiveTmpPath)){
					File::download($archiveName.".tar", $archiveTmpPath, null, false);
					File::rm($archiveTmpPath);
					$isArchive=true;
				}
			}
			////	Sauvegarde en php?
			if(empty($isArchive))	{File::downloadArchive(self::pathDatasFilesList(), $archiveName.".zip");}
		}
		////	Sauvegarde uniquement la Bdd
		else{
			$filesList=[ ["realPath"=>$dumpPath, "zipPath"=>str_replace(PATH_DATAS,null,$dumpPath)] ];
			File::downloadArchive($filesList, "BackupAgoraBdd_".strftime("%Y-%m-%d").".zip");
		}
	}

	/*
	 * ARBORESCENCE DU PATH_DATAS (avec "realPath" / "zipPath" / "emptyFolderZipPath". Fonction recursive!)
	 */
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
			$tmpFileZipPath=str_replace(PATH_DATAS,null,$tmpFileRealPath);
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