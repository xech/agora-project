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
						$sqlNewPassword=MdlUser::passwordSha1(Req::param("newPassword"));
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
						$subject=Txt::trad("userInscriptionNotifSubject")." ".$curSpace->name;
						$mainMessage="<br>".str_replace(["--SPACE_NAME--","--NEW_USER_LABEL--","--NEW_USER_MESSAGE--"], [$curSpace->name,$newUserLabel,Req::param("message")], Txt::trad("userInscriptionNotifMessage"));
						Tool::sendMail($adminMails, $subject, $mainMessage, ["noNotify"]);
					}
				}
			}
			//Renvoie le résultat
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
	public static function actionPublicSpaceAccess()
	{
		$passwordValid=Db::getVal("SELECT count(*) FROM ap_space WHERE _id=".Db::param("publicSpace_idSpaceAccess")." AND BINARY `password`=".Db::param("publicSpacePassword"));//"BINARY"=>case sensitive
		echo empty($passwordValid) ? "false" : "true";
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
				setcookie("AGORAP_LOG", $objUser->login, (time()+315360000));									//Enregistre login : connexion auto
				setcookie("AGORAP_PASS", $objUser->password, (time()+315360000));								//Enregistre password : idem
				echo "userConnected";																			//Retour OK
			}
		}
	}

	/*******************************************************************************************
	 * ACTION : INSTALL DE L'AGORA
	 *******************************************************************************************/
	public static function actionInstall()
	{
		////	Init
		static::$isMainPage=true;
		$dbFile="app/ModOffline/db.sql";

		////	Controle de version PHP  && Verif si l'application est déjà installée  &&  Vérif si le fichier "db.sql" est toujours disponible
		Req::verifPhpVersion();
		if(defined("db_host") && defined("db_login") && defined("db_password") && defined("db_name") && self::installDbControl(db_host,db_login,db_password,db_name)=="dbErrorAlreadyInstalled")
			{self::noAccessExit(Txt::trad("INSTALL_dbErrorAlreadyInstalled"));}
		elseif(is_file($dbFile)==false)
			{self::noAccessExit(Txt::trad("INSTALL_dbErrorNoSqlFile"));}

		////	Affiche/Valide le formulaire
		if(Req::isParam("formValidate")==false)  {static::displayPage("VueInstall.php");}
		else
		{
			////	CONTROLES LES PARAMS D'ACCES A LA BDD
			$installDbControl=self::installDbControl(Req::param("db_host"),Req::param("db_login"), Req::param("db_password"), Req::param("db_name"));
			if($installDbControl!="dbAvailable" && $installDbControl!="dbToCreate")  {$result["notifError"]=Txt::trad("INSTALL_".$installDbControl);}
			////	CONTROLE OK : INSTALL
			else
			{
				////	CHMOD DE "PATH_DATAS" & MODIF DU FICHIER DE CONFIG
				File::setChmod(PATH_DATAS);
				$AGORA_SALT=Txt::uniqId(8);
				$spaceDiskLimit=File::getBytesSize(Req::param("spaceDiskLimit")."go");
				File::updateConfigFile(["AGORA_SALT"=>$AGORA_SALT, "db_host"=>Req::param("db_host"), "db_login"=>Req::param("db_login"), "db_password"=>Req::param("db_password"), "db_name"=>Req::param("db_name"), "limite_nb_users"=>"10000", "limite_espace_disque"=>$spaceDiskLimit]);

				////	CREE LA BASE DE DONNEES (AVEC CONTROLES D'ACCES)
				if($installDbControl=="dbToCreate"){
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
				File::rm($dbFile);

				////	INITIALISE LES TABLES DE LA BDD
				//Init les données
				$spaceName=Req::param("spaceName");
				$spaceDescription=Txt::trad("INSTALL_spaceDescription");
				$spaceDescriptionBis=Req::param("spaceDescription");
				$spaceTimeZone=Req::param("timezone");
				$spaceLang=Req::param("lang");
				$spacePublic=(Req::param("spacePublic")==1)  ?  1  :  "NULL";
				$adminLogin=Req::param("adminLogin");
				$adminPassword=MdlUser::passwordSha1(Req::param("adminPassword"),$AGORA_SALT);
				$adminName=Req::param("adminName");
				$adminFirstName=Req::param("adminFirstName");
				$adminMail=Req::param("adminMail");
				$newsDescription=Txt::trad("INSTALL_dataDashboardNews");

/**************************!!!!		ATTENTION : TOUJOURS UTILISER "$objPDO->query()"	!!!!********************/
				//Paramétrage général
				$objPDO->query("UPDATE ap_agora SET `name`=".$objPDO->quote($spaceName).", `description`=".$objPDO->quote($spaceDescription).", version_agora=".$objPDO->quote(Req::appVersion()).", timezone=".$objPDO->quote($spaceTimeZone).", lang=".$objPDO->quote($spaceLang).", dateUpdateDb=NOW()");
				//Paramétrage du 1er espace
				$objPDO->query("UPDATE ap_space SET `name`=".$objPDO->quote($spaceName).", `description`=".$objPDO->quote($spaceDescriptionBis).", public=".$spacePublic." WHERE _id=1");
				//User principal (admin général)
				$objPDO->query("UPDATE ap_user SET `login`=".$objPDO->quote($adminLogin).", `password`=".$objPDO->quote($adminPassword).", `name`=".$objPDO->quote($adminName).", firstName=".$objPDO->quote($adminFirstName).", mail=".$objPDO->quote($adminMail)." WHERE _id=1");
				//Renomme l'agenda de l'espace
				$objPDO->query("UPDATE ap_calendar SET `title`=".$objPDO->quote($spaceName)." WHERE _id=1 AND type='ressource'");
				//INSERT LA PREMIÈRE ACTUALITÉ
				$objPDO->query("INSERT INTO ap_dashboardNews SET `description`=".$objPDO->quote($newsDescription).", _idUser=1, dateCrea=NOW()");
				$objPDO->query("INSERT INTO ap_objectTarget SET objectType='dashboardNews', _idObject=".(int)$objPDO->lastInsertId().", _idSpace=1, target='spaceUsers', accessRight=1");
				//INSERT LE PREMIER SONDAGE
				$objPDO->query("INSERT INTO ap_dashboardPoll SET _id=1, title=".$objPDO->quote(Txt::trad("INSTALL_dataDashboardPoll")).", _idUser=1, newsDisplay=1, dateCrea=NOW()");
				$objPDO->query("INSERT INTO ap_dashboardPollResponse (_id, _idPoll, label, `rank`) VALUES ('5bd1903d3df9u8t',1,".$objPDO->quote(Txt::trad("INSTALL_dataDashboardPollA")).",1), ('5bd1903d3e11dt5',1,".$objPDO->quote(Txt::trad("INSTALL_dataDashboardPollB")).",2), ('5bd1903d3e041p7',1,".$objPDO->quote(Txt::trad("INSTALL_dataDashboardPollC")).",3)");
				$objPDO->query("INSERT INTO ap_objectTarget (objectType, _idObject, _idSpace, `target`, accessRight) VALUES ('dashboardPoll', 1, 1, 'spaceUsers', 1)");
				//INSERT LE PREMIER EVT SUR L'AGENDA COMMUN
				$objPDO->query("INSERT INTO ap_calendarEvent SET title=".$objPDO->quote(Txt::trad("INSTALL_dataCalendarEvt")).", dateBegin=NOW(), dateEnd=NOW(), contentVisible='public', dateCrea=NOW(), _idUser=1");
				$objPDO->query("INSERT INTO ap_calendarEventAffectation SET _idEvt=1, _idCal=1, confirmed=1");
				//INSERT LE PREMIER SUJET DU FORUM
				$objPDO->query("INSERT INTO ap_forumSubject SET title=".$objPDO->quote(Txt::trad("INSTALL_dataForumSubject1")).", description=".$objPDO->quote(Txt::trad("INSTALL_dataForumSubject2")).", dateCrea=NOW(), _idUser=1");
				$objPDO->query("INSERT INTO ap_objectTarget SET objectType='forumSubject', _idObject=1, _idSpace=1, `target`='spaceUsers', accessRight='1.5'");
/***************************************************************************************************************************/

				//REDIRECTION AVEC NOTIFICATION
				$result["redirSuccess"]="index.php?disconnect=1&notify=INSTALL_installOk";
			}
			//RENVOI LE RESULTAT
			echo json_encode($result);
		}
	}

	/*******************************************************************************************
	 * VERIFIE LA CONNEXION À LA DATABASE
	 *******************************************************************************************/
	public static function installDbControl($db_host, $db_login, $db_password, $db_name)
	{
		//Connection PDO
		try{
			//Vérif la connexion à la db
			$objPDO=new PDO("mysql:host=".$db_host.";dbname=".$db_name.";charset=utf8;", $db_login, $db_password, array(PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION));
			//Vérif si l'appli est déjà installée sur la db
			$result=$objPDO->query("SHOW TABLES FROM `".$db_name."` WHERE `Tables_in_".$db_name."` LIKE 'gt_%' OR `Tables_in_".$db_name."` LIKE 'ap_%'");
			if(count($result->fetchAll(PDO::FETCH_COLUMN,0))>0)  {return "dbErrorAlreadyInstalled";}//Erreur: L'application est déjà installée
		}
		//Erreur de connexion à la bdd
		catch(PDOException $exception){
			if(preg_match("/(unknown|inconnue)/i",$exception->getMessage()))	{return "dbToCreate";}			//Erreur: Bdd non installée
			else																{return "dbErrorConnect";}	//Erreur: Pas de connexion à la Bdd
		}
		//Pas d'erreur : Db disponible
		return "dbAvailable";
	}
}