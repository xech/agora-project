<?php
/**
* This file is part of the Agora-Project Software package.
*
* @copyright (c) Agora-Project Limited <https://www.agora-project.net>
* @license GNU General Public License, version 2 (GPL-2.0)
*/


/*
 * CONTROLEUR DES PAGES "OFFLINE" & DE CONNEXION A L'ESPACE
 */
class CtrlOffline extends Ctrl
{
	const moduleName="offline";

	/*******************************************************************************************
	 * VUE : PAGE PRINCIPALE
	 *******************************************************************************************/
	public static function actionDefault()
	{
		//Init
		$vDatas=[];
		////	Reset du password
		if(Req::isParam("resetPasswordMail"))
		{
			// Affiche la notif d'envoie de l'email (que l'email soit bon ou pas, par mesure de sécurité) : "Un email vient de vous être envoyé [...] Si vous ne l'avez pas reçu, vérifiez que l’adresse saisie est bien la bonne"
			if(Req::isParam("resetPasswordSendMail"))  {Ctrl::notify("resetPasswordNotif");}
			// Vérif si l'user existe
			$tmpUser=Db::getLine("SELECT * FROM ".MdlUser::dbTable." WHERE mail=".Db::param("resetPasswordMail")." OR `login`=".Db::param("resetPasswordMail"));
			if(!empty($tmpUser))
			{
				// Récupère l'user
				$tmpUser=Ctrl::getObj("user",$tmpUser);
				// Envoie l'email de reset du password
				if(Req::isParam("resetPasswordSendMail"))  {$tmpUser->resetPasswordSendMail();}
				// L'user clique ensuite sur le lien présent dans l'email : affiche puis enregistre le formulaire du nouveau password
				elseif(Req::isParam("resetPasswordId"))
				{
					//Vérifie le "resetPasswordId()"
					$vDatas["resetPasswordIdOk"]=($tmpUser->resetPasswordId()==Req::param("resetPasswordId"));
					//Enregistre le nouveau password ("resetPasswordId" OK)					
					if($vDatas["resetPasswordIdOk"]==true && Req::isParam("newPassword")){
						$sqlNewPassword=password_hash(Req::param("newPassword"),PASSWORD_DEFAULT);
						Db::query("UPDATE ".MdlUser::dbTable." SET `password`=".Db::format($sqlNewPassword)." WHERE _id=".(int)$tmpUser->_id);
						Ctrl::notify("modifRecorded","success");
					}
					//Notify "Le lien de renouvellement de password a expiré" ("resetPasswordId" expiré)
					elseif($vDatas["resetPasswordIdOk"]!=true)  {self::notify("resetPasswordIdExpired");}
				}
			}
		}
		////	Confirmation d'invitation
		elseif(Req::isParam(["_idInvitation","mail"]))
		{
			//Infos de l'invitation
			$tmpInvit=Db::getLine("SELECT * FROM ap_invitation WHERE _idInvitation=".Db::param("_idInvitation")." AND mail=".Db::param("mail"));
			//Invitation expiré ?
			if(empty($tmpInvit))	{Ctrl::notify("USER_exired_idInvitation");}
			//Valide l'invitation avec le "newPassword" et créé le nouvel utilisateur
			elseif(Req::isParam("newPassword") && MdlUser::usersQuotaOk())
			{
				$newUser=new MdlUser();
				$sqlProperties="name=".Db::format($tmpInvit["name"]).", firstName=".Db::format($tmpInvit["firstName"]).", mail=".Db::format($tmpInvit["mail"]);
				$newUser=$newUser->createUpdate($sqlProperties, $tmpInvit["mail"], Req::param("newPassword"), $tmpInvit["_idSpace"]);
				if(is_object($newUser)){
					Db::query("DELETE FROM ap_invitation WHERE _idInvitation=".Db::format($tmpInvit["_idInvitation"]));
					$_COOKIE["AGORAP_LOG"]=$tmpInvit["mail"];//Préremplis le 'login'
					$newUser->newUserCoordsSendMail(Req::param("newPassword"));
					Ctrl::notify("USER_invitationValidated","success");
				}
			}
		}
		////	Affiche la page
		$vDatas["userInscription"]=(Db::getVal("select count(*) from ap_space where userInscription=1")>0  &&  Req::isMobileApp()==false);
		$vDatas["objPublicSpaces"]=Db::getObjTab("space", "select * from ap_space where public=1 order by name");
		if(Req::isParam("login"))				{$vDatas["defaultLogin"]=Req::param("login");}//Login par défaut : passé en parametre
		elseif(!empty($_COOKIE["AGORAP_LOG"]))	{$vDatas["defaultLogin"]=$_COOKIE["AGORAP_LOG"];}//Login par défaut : en cookie
		else									{$vDatas["defaultLogin"]=null;}
		//Affiche la vue
		static::displayPage("VueConnection.php",$vDatas);
	}

	/*******************************************************************************************
	 * ACTION : INSCRIPTION D'UTILISATEUR
	 *******************************************************************************************/
	public static function actionUserInscription()
	{
		////	Valide le formulaire via Ajax
		if(Req::isParam("formValidate"))
		{
			//Verifie si le login/mail existe déjà  &&  Vérifie le Captcha
			if(MdlUser::loginExists(Req::param("mail")))	{$result["notifError"]=Txt::trad("USER_loginExists");}
			elseif(CtrlMisc::actionCaptchaControl()==false)	{$result["notifError"]=Txt::trad("captchaError");}
			//Enregistre l'user et renvoi l'url avec le message de succès
			else{
				Db::query("INSERT INTO ap_userInscription SET _idSpace=".Db::param("_idSpace").", name=".Db::param("name").", firstName=".Db::param("firstName").", mail=".Db::param("mail").", `password`=".Db::param("password").", message=".Db::param("message").", `date`=".Db::dateNow());
				$result["redirSuccess"]="index.php?notify=userInscriptionRecorded";
				//Envoie une notif aux admins de l'espace?
				$curSpace=Ctrl::getObj("space",Req::param("_idSpace"));
				if(!empty($curSpace->userInscriptionNotify))
				{
					$adminMails=[];
					foreach($curSpace->getUsers() as $tmpUser)  {if($curSpace->accessRightUser($tmpUser)==2) {$adminMails[]=$tmpUser->mail;}}
					if(!empty($adminMails)){
						$newUserLabel=Req::param("name")." ".Req::param("firstName");
						$subject=Txt::trad("userInscriptionEmailSubject")." ".$curSpace->name;
						$mainMessage="<br>".str_replace(["--SPACE_NAME--","--NEW_USER_LABEL--","--NEW_USER_MESSAGE--"], [$curSpace->name,$newUserLabel,Req::param("message")], Txt::trad("userInscriptionEmailMessage"));
						Tool::sendMail($adminMails, $subject, $mainMessage, ["noNotify"]);
					}
				}
			}
			//Retourne le résultat
			echo json_encode($result);
		}
		////	Affiche le formulaire
		else
		{
			$vDatas["objSpacesInscription"]=Db::getObjTab("space", "SELECT * FROM ap_space WHERE userInscription=1");
			static::displayPage("VueUserInscription.php",$vDatas);
		}
	}

	/*******************************************************************************************
	 * AJAX : TEST LE PASSWORD DE CONNEXION À UN ESPACE PUBLIC
	 *******************************************************************************************/
	public static function actionPublicSpacePassword()
	{
		$passwordValid=Db::getVal("SELECT count(*) FROM ap_space WHERE _id=".Db::param("_idSpaceAccess")." AND BINARY `password`=".Db::param("password"));//"BINARY"=>case sensitive
		if(empty($passwordValid))  {echo "passwordError";}
	}

	/*******************************************************************************************
	 * AJAX : AUTHENTIFICATION VIA GOOGLE IDENTITY / OAUTH 
	 * https://developers.google.com/identity/gsi/web/guides/overview
	 *******************************************************************************************/
	public static function actionGIdentityControl()
	{
		require_once 'app/misc/google-api-php-client/vendor/autoload.php';										//Charge l'API Google Identity/Oauth
		$gClient=new Google_Client(["client_id"=>Ctrl::$agora->gIdentityClientId]);								//Créé un client Google Identity
		$gClientUser=$gClient->verifyIdToken(Req::param("credential"));											//Vérifie le token du client et récupère ses infos
		if(!empty($gClientUser)){																				//Client Google authentifié par l'API ?
			$tmpUser=Db::getLine("SELECT * FROM ap_user WHERE `login`=".Db::format($gClientUser["email"]));		//Verif si un user existe déjà avec le même email
			if(!empty($tmpUser)){																				//Données récupérées?
				$objUser=Ctrl::getObj("user",$tmpUser);															//Charge l'objet "user"
				if($objUser->hasImg()==false && !empty($gClientUser["picture"])){								//Enregistre l'image du profil Google de l'user ?
					$imgPath=File::getTempDir()."/".uniqid().".png";											//Path de l'image temporaire
					file_put_contents($imgPath, file_get_contents($gClientUser["picture"]));					//Enregistre l'image dans le fichier tmp
					File::imageResize($imgPath,$objUser->pathImgThumb(),200);									//Redimensionne l'image
				}
				self::userAuthToken("create",$objUser->_id);													//Créé le token de connexion auto
				echo "userConnected";																			//Retour OK
			}
		}
	}

	/*******************************************************************************************
	 * ACTION : INSTALL DE L'ESPACE
	 *******************************************************************************************/
	public static function actionInstall()
	{
		////	Init
		static::$isMainPage=true;
		$dbFile="app/ModOffline/db.sql";

		////	Controle de version PHP  &&  Verif si l'application est déjà installée  &&  Vérif si le fichier "db.sql" est toujours disponible
		Req::verifPhpVersion();
		if(defined("db_host") && defined("db_login") && defined("db_password") && defined("db_name") && DbInstall::dbControl(db_host,db_login,db_password,db_name)=="errorDbExist")
			{self::noAccessExit(Txt::trad("INSTALL_errorDbExist"));}
		elseif(is_file($dbFile)==false)
			{self::noAccessExit(Txt::trad("INSTALL_errorDbNoSqlFile"));}

		////	Affiche ou Valide le formulaire
		if(Req::isParam("formValidate")==false)  {static::displayPage("VueInstall.php");}
		else
		{
			////	CONTROLES LES PARAMS D'ACCES A LA BDD
			$dbControl=DbInstall::dbControl(Req::param("db_host"),Req::param("db_login"), Req::param("db_password"), Req::param("db_name"));
			if(preg_match("/error/i",$dbControl))  {$result=Txt::trad("INSTALL_".$dbControl);}
			////	CONTROLE OK : INSTALL
			else
			{
				////	CHMOD DE "PATH_DATAS" & MODIF DU FICHIER DE CONFIG
				File::setChmod(PATH_DATAS);
				$spaceDiskLimit=File::getBytesSize(Req::param("spaceDiskLimit")."go");
				File::updateConfigFile(["db_host"=>Req::param("db_host"), "db_login"=>Req::param("db_login"), "db_password"=>Req::param("db_password"), "db_name"=>Req::param("db_name"), "limite_nb_users"=>"10000", "limite_espace_disque"=>$spaceDiskLimit]);

				////	CREE LA BASE DE DONNEES DU NOUVEL ESPACE  &&  PUIS ON S'Y CONNECTE !
				if($dbControl=="dbAbsent"){
					$pdoSpace=new PDO("mysql:host=".Req::param("db_host"),Req::param("db_login"),Req::param("db_password"));
					$pdoSpace->query("CREATE DATABASE `".Req::param("db_name")."` DEFAULT CHARACTER SET utf8 DEFAULT COLLATE utf8_general_ci;");
				}
				//Se connecte au sgbd && Importe la Bdd
				$pdoSpace=new PDO("mysql:host=".Req::param("db_host").";dbname=".Req::param("db_name").";charset=utf8;", Req::param("db_login"), Req::param("db_password"));
				$handle=fopen($dbFile,"r");
				foreach(explode(";",fread($handle,filesize($dbFile))) as $tmpQuery){
					if(strlen($tmpQuery)>5)  {$pdoSpace->query($tmpQuery);}
				}
				//Supprime le fichier Sql après l'import
				File::rm($dbFile);

				////	INSTALL LES PARAMETRES DE BASE DE LA DB (nom, description, 1er user, etc)
				$installParams["version_agora"]		=Req::appVersion();
				$installParams["spaceName"]			=Req::param("spaceName");
				$installParams["spaceDescription"]	=Req::param("spaceDescription");
				$installParams["spaceTimeZone"]		=Req::param("timezone");
				$installParams["spaceLang"]			=Req::param("lang");
				$installParams["spacePublic"]		=(Req::param("spacePublic")==1)  ?  1  :  "NULL";
				$installParams["adminName"]			=Req::param("adminName");
				$installParams["adminFirstName"]	=Req::param("adminFirstName");
				$installParams["adminMailLogin"]	=Req::param("adminMailLogin");
				$installParams["adminPassword"]		=password_hash(Req::param("adminPassword"),PASSWORD_DEFAULT);
				DbInstall::initParams($pdoSpace, $installParams);

				//REDIRECTION AVEC NOTIFICATION
				$result="installOK";
			}
			//RETOURNE LE RESULTAT
			echo $result;
		}
	}
}