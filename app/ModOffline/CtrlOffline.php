<?php
/**
* This file is part of the Agora-Project Software package
*
* @copyleft Agora-Project <https://www.agora-project.net>
* @license GNU General Public License (GPL-2.0)
*/


/*
 * CONTROLEUR DES PAGES "OFFLINE" & DE CONNEXION A L'ESPACE
 */
class CtrlOffline extends Ctrl
{
	const moduleName="offline";

	/********************************************************************************************************
	 * VUE : PAGE PRINCIPALE
	 ********************************************************************************************************/
	public static function actionDefault()
	{
		////	Reset du password
		if(Req::isParam("resetPasswordMail")){
			$tmpUser=Db::getLine("SELECT * FROM ".MdlUser::dbTable." WHERE `mail`=".Db::param("resetPasswordMail")." OR `login`=".Db::param("resetPasswordMail"));
			if(empty($tmpUser))  {Ctrl::notify("resetPasswordMailNotRegistered");}
			else{
				$tmpUser=Ctrl::getObj("user",$tmpUser);
				////	ETAPE 1 : ENVOI DE L'EMAIL
				if(Req::isParam("resetPasswordSendMail")){
					$tmpUser->resetPasswordSendMail();
					Ctrl::notify("resetPasswordNotif");//Notif spécifique
				}
				////	ETAPE 2 : MODIF DU PASSWORD
				elseif(Req::isParam("resetPasswordId")){
					if($tmpUser->resetPasswordIdVerif()==false)	{self::notify("resetPasswordIdExpired");}	//resetPasswordId expiré
					elseif(Req::isParam("newPassword")==false)	{$vDatas["resetPasswordChangeForm"]=true;}	//Formulaire du nouveau password
					else										{$tmpUser->resetPasswordRecord();}			//Enregistre le nouveau password
				}
			}
		}

		////	Confirme une invitation
		if(Req::isParam(["_idInvitation","mail"])){
			$tmpInvit=Db::getLine("SELECT * FROM ap_invitation WHERE _idInvitation=".Db::param("_idInvitation")." AND mail=".Db::param("mail"));
			if(empty($tmpInvit))  					{Ctrl::notify("USER_exired_idInvitation");}														//Invitation expiré
			elseif(MdlUser::usersQuotaOk()==false)  {Ctrl::notify("USER_quotaExceeded");}															//Quota d'users atteint
			elseif(Req::isParam("newPassword")){																									//Invitation valide : créé un nouvel user avec le "newPassword"
				$newUser=new MdlUser();
				$sqlFields="name=".Db::format($tmpInvit["name"]).", firstName=".Db::format($tmpInvit["firstName"]).", mail=".Db::format($tmpInvit["mail"]);
				$newUser=$newUser->editRecord($sqlFields, $tmpInvit["mail"], Req::param("newPassword"), $tmpInvit["_idSpace"]);
				if(is_object($newUser)){
					Db::query("DELETE FROM ap_invitation WHERE _idInvitation=".Db::format($tmpInvit["_idInvitation"]));
					$_COOKIE["AGORAP_LOG"]=$tmpInvit["mail"];//Préremplis le 'login'
					$newUser->createCredentialsMail(Req::param("newPassword"),true);
					Ctrl::notify("USER_invitationValidated","success");
				}
			}
		}
	
		////	Affiche la vue
		$vDatas["isUserInscription"]=(Db::getVal("select count(*) from ap_space where userInscription=1")>0  &&  Req::isMobileApp()==false);
		$vDatas["objPublicSpaces"]=Db::getObjTab("space", "select * from ap_space where public=1 order by name");
		if(Req::isParam("login"))				{$vDatas["defaultLogin"]=Req::param("login");}		//Login par défaut : en parametre
		elseif(!empty($_COOKIE["AGORAP_LOG"]))	{$vDatas["defaultLogin"]=$_COOKIE["AGORAP_LOG"];}	//Login par défaut : en cookie
		else									{$vDatas["defaultLogin"]=null;}
		static::displayPage("VueConnection.php",$vDatas);
	}

	/********************************************************************************************************
	 * ACTION : INSCRIPTION D'UTILISATEUR
	 ********************************************************************************************************/
	public static function actionUserInscription()
	{
		////	Valide le formulaire via Ajax
		if(Req::isParam("formValidate"))
		{
			////	Verif l'existance du login/mail
			if(MdlUser::loginExists(Req::param("mail")))  {$result=Txt::trad("USER_loginExists");}
			////	Enregistre l'inscription
			else{
				//// Enregistre en DB
				$result="inscriptionOK";
				$password=Txt::defaultPassword();
				Db::query("INSERT INTO ap_userInscription SET `_idSpace`=".Db::param("_idSpace").", `name`=".Db::param("name").", `firstName`=".Db::param("firstName").", `mail`=".Db::param("mail").", `password`=".Db::format($password).", `message`=".Db::param("message").", `date`=".Db::dateNow());
				//// Envoie un mail de notif aux admins de l'espace ?
				$curSpace=Ctrl::getObj("space",Req::param("_idSpace"));
				if(!empty($curSpace->userInscriptionNotify)){
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
			//// Retourne le résultat
			echo $result;
		}
		////	Affiche le formulaire
		else{
			$vDatas["objSpacesInscription"]=Db::getObjTab("space", "SELECT * FROM ap_space WHERE userInscription=1");
			static::displayPage("VueUserInscription.php",$vDatas);
		}
	}

	/********************************************************************************************************
	 * AJAX : CONTROLE LE PASSWORD DE CONNEXION À UN ESPACE PUBLIC  ("BINARY" : case sensitive)
	 ********************************************************************************************************/
	public static function actionPublicSpacePasswordControl()
	{
		$passwordValid=Db::getVal("SELECT count(*) FROM ap_space WHERE `_id`=".Db::param("idSpacePublic")." AND BINARY `password`=".Db::param("passwordControl"));
		if(!empty($passwordValid))  {echo "passwordOK";}
	}

	/********************************************************************************************************
	 * AJAX : AUTHENTIFICATION VIA GOOGLE OAUTH 
	 ********************************************************************************************************/
	public static function actiongOAuthControl()
	{
		require_once 'app/misc/google-api-php-client/vendor/autoload.php';										//Charge l'API Google Oauth
		$gClient=new Google_Client(["client_id"=>Ctrl::$agora->gIdentityClientId]);								//Créé un client Google Oauth
		$gClientUser=$gClient->verifyIdToken(Req::param("credential"));											//Vérifie le token du client et récupère ses infos
		if(!empty($gClientUser)){																				//Client Google authentifié par l'API ?
			$tmpUser=Db::getLine("SELECT * FROM ap_user WHERE `login`=".Db::format($gClientUser["email"]));		//Verif si un user existe déjà avec le même email
			if(!empty($tmpUser)){																				//Données récupérées?
				$objUser=Ctrl::getObj("user",$tmpUser);															//Charge l'objet "user"
				if($objUser->isProfileImg()==false && !empty($gClientUser["picture"])){							//Enregistre l'image du profil Google de l'user ?
					$imgPath=File::getTempDir()."/".uniqid().".png";											//Path de l'image temporaire
					file_put_contents($imgPath, file_get_contents($gClientUser["picture"]));					//Enregistre l'image dans le fichier tmp
					File::imageResize($imgPath,$objUser->pathProfileImg(),200);									//Redimensionne l'image
				}
				self::userAuthToken(true,$objUser->_id);														//Créé le token de connexion auto
				echo "userConnected";																			//Retour OK
			}
		}
	}

	/********************************************************************************************************
	 * ACTION : INSTALL DE L'ESPACE
	 ********************************************************************************************************/
	public static function actionInstall()
	{
		////	Init
		static::$isMainPage=true;
		$dbFile="app/misc/db.sql";

		////	CONTROLES :  Version PHP  &&  Install déjà réalisée  &&  Accès à "db.sql"
		Req::verifPhpVersion();
		if(defined("db_host") && defined("db_login") && defined("db_password") && defined("db_name") && DbInstall::dbControl(db_host,db_login,db_password,db_name)=="errorDbExist")
			{self::noAccessExit("INSTALL_errorDbExist");}
		elseif(is_file($dbFile)==false)
			{self::noAccessExit("INSTALL_errorDbNoSqlFile");}

		////	VALIDE LE FORMULAIRE
		if(Req::isParam("formValidate")){
			////	CONTROLES LES PARAMS D'ACCES A LA BDD
			$dbControl=DbInstall::dbControl(Req::param("db_host"),Req::param("db_login"), Req::param("db_password"), Req::param("db_name"));
			if(preg_match("/error/i",$dbControl))  {$result=Txt::trad("INSTALL_".$dbControl);}
			////	CONTROLE OK : INSTALL
			else{
				////	CHMOD DE "PATH_DATAS" & MODIF DU FICHIER DE CONFIG
				File::setChmod(PATH_DATAS);
				$spaceDiskLimit=File::getBytesSize(Req::param("spaceDiskLimit")."go");
				$paramsEdit=["db_host"=>Req::param("db_host"), "db_login"=>Req::param("db_login"), "db_password"=>Req::param("db_password"), "db_name"=>Req::param("db_name"), "limite_nb_users"=>"100000", "limite_espace_disque"=>$spaceDiskLimit];
				File::updateConfigFile($paramsEdit);

				////	CREE LA BASE DE DONNEES DU NOUVEL ESPACE  &&  PUIS ON S'Y CONNECTE !
				if($dbControl=="dbAbsent"){
					$objPDO=new PDO("mysql:host=".Req::param("db_host"),Req::param("db_login"),Req::param("db_password"));
					$objPDO->query("CREATE DATABASE `".Req::param("db_name")."` DEFAULT CHARACTER SET utf8 DEFAULT COLLATE utf8_general_ci;");
				}
				//Se connecte au sgbd && Importe la Bdd
				$objPDO=new PDO("mysql:host=".Req::param("db_host").";dbname=".Req::param("db_name").";charset=utf8;", Req::param("db_login"), Req::param("db_password"));
				$handle=fopen($dbFile,"r");
				foreach(explode(";",fread($handle,filesize($dbFile))) as $tmpQuery){
					if(strlen($tmpQuery)>5)  {$objPDO->query($tmpQuery);}
				}
				//Supprime le fichier Sql après l'import
				//File::rm($dbFile);

				////	INSTALL LES PARAMETRES DE BASE DE LA DB (nom, description, 1er user, etc)
				$installParams=[
					"version_agora"=>		Req::appVersion(),
					"spaceName"=>			Req::param("spaceName"),
					"spaceDescription"=>	Req::param("spaceDescription"),
					"spaceTimeZone"=>		Req::param("timezone"),
					"spaceLang"=>			Req::param("lang"),
					"spacePublic"=>			Req::param("spacePublic"),
					"adminName"=>			Req::param("adminName"),
					"adminFirstName"=>		Req::param("adminFirstName"),
					"adminMailLogin"=>		Req::param("adminMailLogin"),
					"adminPassword"=>		password_hash(Req::param("adminPassword"),PASSWORD_DEFAULT)
				];
				DbInstall::initParams($objPDO, $installParams);

				//REDIRECTION AVEC NOTIFICATION
				$result="installOk";
			}
			//RETOURNE LE RESULTAT
			echo $result;
		}
		////	AFFICHE LE FORMULAIRE !!
		else {static::displayPage("VueInstall.php");}
	}
}