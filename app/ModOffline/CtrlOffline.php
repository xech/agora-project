<?php
/**
* This file is part of the Agora-Project Software package.
*
* @copyright (c) Agora-Project Limited <https://www.agora-project.net>
* @license GNU General Public License, version 2 (GPL-2.0)
*/


/*
 * Controleur des pages "Offline"
 */
class CtrlOffline extends Ctrl
{
	const moduleName="offline";

	/*
	 * ACTION PAR DEFAUT : connexion à l'espace
	 */
	public static function actionDefault()
	{
		//Init
		$vDatas=[];
		////	Reset du password
		if(Req::isParam("resetPasswordMail"))
		{
			// Affiche la notif d'envoie de l'email (que l'email soit bon ou pas, par mesure de sécurité) : "Un email vient de vous être envoyé [...] Si vous ne l'avez pas reçu, vérifiez que l’adresse saisie est bien la bonne"
			if(Req::isParam("resetPasswordSendMail"))  {Ctrl::addNotif("resetPasswordNotif");}
			// Vérif si l'user existe
			$tmpUser=Db::getLine("SELECT * FROM ".MdlUser::dbTable." WHERE mail=".Db::formatParam("resetPasswordMail")." OR login=".Db::formatParam("resetPasswordMail"));
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
					$vDatas["resetPasswordIdOk"]=($tmpUser->resetPasswordId()==Req::getParam("resetPasswordId"));
					//"resetPasswordId" OK : enregistre le nouveau password!					
					if($vDatas["resetPasswordIdOk"]==true && Req::isParam("newPassword")){
						$sqlNewPassword=MdlUser::passwordSha1(Req::getParam("newPassword"));
						Db::query("UPDATE ".MdlUser::dbTable." SET password=".Db::format($sqlNewPassword)." WHERE _id=".(int)$tmpUser->_id);
						Ctrl::addNotif("modifRecorded","success");
					}
					//"resetPasswordId" pas OK : affiche "Le lien de renouvellement de password a expiré"
					elseif($vDatas["resetPasswordIdOk"]!=true)  {self::addNotif("resetPasswordIdExpired");}
				}
			}
		}
		////	Confirmation d'invitation
		elseif(Req::isParam(["_idInvitation","mail"]))
		{
			//Infos de l'invitation
			$tmpInvit=Db::getLine("SELECT * FROM ap_invitation WHERE _idInvitation=".Db::formatParam("_idInvitation")." AND mail=".Db::formatParam("mail"));
			//Invitation expiré ?
			if(empty($tmpInvit))	{Ctrl::addNotif("USER_exired_idInvitation");}
			//Valide l'invitation avec le "newPassword" et créé le nouvel utilisateur
			elseif(Req::isParam("newPassword") && MdlUser::usersQuotaOk())
			{
				$newUser=new MdlUser();
				$sqlProperties="name=".Db::format($tmpInvit["name"]).", firstName=".Db::format($tmpInvit["firstName"]).", mail=".Db::format($tmpInvit["mail"]);
				$newUser=$newUser->createUpdate($sqlProperties, $tmpInvit["mail"], Req::getParam("newPassword"), $tmpInvit["_idSpace"]);
				if(is_object($newUser)){
					Db::query("DELETE FROM ap_invitation WHERE _idInvitation=".Db::format($tmpInvit["_idInvitation"]));
					$_COOKIE["AGORAP_LOG"]=$tmpInvit["mail"];//Préremplis le 'login'
					$newUser->newUserCoordsSendMail(Req::getParam("newPassword"));
					Ctrl::addNotif("USER_invitationValidated","success");
				}
			}
		}
		////	Affiche la page
		$vDatas["usersInscription"]=(Db::getVal("select count(*) from ap_space where usersInscription=1")>0  &&  Req::isMobileApp()==false);
		$vDatas["objPublicSpaces"]=Db::getObjTab("space", "select * from ap_space where public=1 order by name");
		if(Req::isParam("login"))				{$vDatas["defaultLogin"]=Req::getParam("login");}//Login par défaut : passé en parametre
		elseif(!empty($_COOKIE["AGORAP_LOG"]))	{$vDatas["defaultLogin"]=$_COOKIE["AGORAP_LOG"];}//Login par défaut : en cookie
		else									{$vDatas["defaultLogin"]=null;}
		//Affiche la vue
		static::displayPage("VueConnection.php",$vDatas);
	}

	/*
	 * ACTION : Inscription d'utilisateur
	 */
	public static function actionUsersInscription()
	{
		////	Valide le formulaire (Ajax)
		if(Req::isParam("formValidate"))
		{
			//Verifie si le login existe déjà  &&  Vérif le Captcha  &&  Si tout est ok, on enregistre l'user et renvoi l'url avec le message de succès
			if(MdlUser::loginAlreadyExist(Req::getParam("mail")))	{$result["notifError"]=Txt::trad("USER_loginAlreadyExist");}
			elseif(CtrlMisc::actionCaptchaControl()==false)			{$result["notifError"]=Txt::trad("captchaError");}
			else{
				Db::query("INSERT INTO ap_userInscription SET _idSpace=".Db::formatParam("_idSpace").", name=".Db::formatParam("name").", firstName=".Db::formatParam("firstName").", mail=".Db::formatParam("mail").", password=".Db::formatParam("password").", message=".Db::formatParam("message").", date=".Db::dateNow());
				$result["redirSuccess"]="index.php?msgNotif[]=userInscriptionRecorded";
			}
			//Renvoie le résultat
			echo json_encode($result);
		}
		////	Affiche le formulaire
		else
		{
			$vDatas["objSpacesInscription"]=Db::getObjTab("space", "SELECT * FROM ap_space WHERE usersInscription=1");
			static::displayPage("VueUsersInscription.php",$vDatas);
		}
	}

	/*
	 * ACTION : Install de l'Agora
	 */
	public static function actionInstall()
	{
		////	Init  & Controle de version PHP  & Verif si l'application est déjà installée
		static::$isMainPage=true;
		Req::verifPhpVersion();
		if(defined("db_host") && defined("db_login") && defined("db_password") && defined("db_name") && self::installDbControl(db_host,db_login,db_password,db_name)=="dbErrorAppInstalled")  {self::noAccessExit(Txt::trad("INSTALL_dbErrorAppInstalled"));}

		////	Valide le formulaire
		if(Req::isParam("formValidate"))
		{
			////	CONTROLES DE BASE
			$installDbControl=self::installDbControl(Req::getParam("db_host"),Req::getParam("db_login"), Req::getParam("db_password"), Req::getParam("db_name"));
			if($installDbControl!="dbAvailable" && $installDbControl!="dbToCreate")  {$result["notifError"]=Txt::trad("INSTALL_".$installDbControl);}
			////	CONTROLE OK : INSTALL
			else
			{
				////	CHMOD DE "PATH_DATAS" & MODIF DU FICHIER DE CONFIG
				File::setChmod(PATH_DATAS);
				$AGORA_SALT=Txt::uniqId(8);
				$spaceDiskLimit=File::getBytesSize(Req::getParam("spaceDiskLimit")."go");
				File::updateConfigFile(["AGORA_SALT"=>$AGORA_SALT, "db_host"=>Req::getParam("db_host"), "db_login"=>Req::getParam("db_login"), "db_password"=>Req::getParam("db_password"), "db_name"=>Req::getParam("db_name"), "limite_nb_users"=>"10000", "limite_espace_disque"=>$spaceDiskLimit]);

				////	CREE LA BASE DE DONNEES (AVEC CONTROLES D'ACCES)
				if($installDbControl=="dbToCreate"){
					$objPDO=new PDO("mysql:host=".Req::getParam("db_host"),Req::getParam("db_login"),Req::getParam("db_password"));
					$objPDO->query("CREATE DATABASE `".Req::getParam("db_name")."` DEFAULT CHARACTER SET utf8 DEFAULT COLLATE utf8_general_ci;");
				}
				//Se connecte au sgbd & Importe la Bdd!
				$objPDO=new PDO("mysql:host=".Req::getParam("db_host").";dbname=".Req::getParam("db_name").";charset=utf8;", Req::getParam("db_login"), Req::getParam("db_password"));
				$dbFile="app/ModOffline/db.sql";
				$handle=fopen($dbFile,"r");
				foreach(explode(";",fread($handle,filesize($dbFile))) as $tmpQuery){
					if(strlen($tmpQuery)>5)  {$objPDO->query($tmpQuery);}
				}

				////	INITIALISE LES TABLES DE LA BDD  (pas de "Db::format()", car instancie un "new PDO()")
				//Init les données
				$spaceName=Req::getParam("spaceName");
				$spaceDescription=Txt::trad("INSTALL_spaceDescription");
				$spaceDescriptionBis=Req::getParam("spaceDescription");
				$spaceTimeZone=Req::getParam("timezone");
				$spaceLang=Req::getParam("lang");
				$spacePublic=(Req::getParam("spacePublic")==1)  ?  1  :  "NULL";
				$adminLogin=Req::getParam("adminLogin");
				$adminPassword=MdlUser::passwordSha1(Req::getParam("adminPassword"),$AGORA_SALT);
				$adminName=Req::getParam("adminName");
				$adminFirstName=Req::getParam("adminFirstName");
				$adminMail=Req::getParam("adminMail");
				$newsDescription="<p style='font-weight:bold;font-size:1.2em;'>".Txt::trad("INSTALL_dataDashboardNews1")."</p><br>
								  <p style='font-weight:bold;'><a href=\"javascript:lightboxOpen('?ctrl=user&action=SendInvitation')\">".Txt::trad("INSTALL_dataDashboardNews2")."</a></p><br>
								  <p style='font-weight:bold;'>".Txt::trad("INSTALL_dataDashboardNews3")."</p><br>";

	/***************************************************************************************************************************/
				//Paramétrage général
				$objPDO->query("UPDATE ap_agora SET name=".$objPDO->quote($spaceName).", description=".$objPDO->quote($spaceDescription).", timezone=".$objPDO->quote($spaceTimeZone).", lang=".$objPDO->quote($spaceLang).", dateUpdateDb=NOW(), version_agora='".VERSION_AGORA."'");
				//Paramétrage du premier espace
				$objPDO->query("UPDATE ap_space SET name=".$objPDO->quote($spaceName).", description=".$objPDO->quote($spaceDescriptionBis).", public=".$spacePublic." WHERE _id=1");
				//User principal (admin général)
				$objPDO->query("UPDATE ap_user SET login=".$objPDO->quote($adminLogin).", password=".$objPDO->quote($adminPassword).", name=".$objPDO->quote($adminName).", firstName=".$objPDO->quote($adminFirstName).", mail=".$objPDO->quote($adminMail)." WHERE _id=1");
				//Renomme l'agenda de l'espace
				$objPDO->query("UPDATE ap_calendar SET title=".$objPDO->quote($spaceName)." WHERE _id=1 AND type='ressource'");
				//INSERT LA PREMIÈRE ACTUALITÉ
				$objPDO->query("INSERT INTO ap_dashboardNews SET description=".$objPDO->quote($newsDescription).", _idUser=1, dateCrea=NOW()");
				$objPDO->query("INSERT INTO ap_objectTarget SET objectType='dashboardNews', _idObject=".(int)$objPDO->lastInsertId().", _idSpace=1, target='spaceUsers', accessRight=1");
				//INSERT LE PREMIER SONDAGE
				$objPDO->query("INSERT INTO ap_dashboardPoll SET _id=1, title=".$objPDO->quote(Txt::trad("INSTALL_dataDashboardPoll")).", _idUser=1, newsDisplay=1, dateCrea=NOW()");
				$objPDO->query("INSERT INTO ap_dashboardPollResponse (_id, _idPoll, label, rank) VALUES ('5bd1903d3df9u8t',1,".$objPDO->quote(Txt::trad("INSTALL_dataDashboardPollA")).",1), ('5bd1903d3e11dt5',1,".$objPDO->quote(Txt::trad("INSTALL_dataDashboardPollB")).",2), ('5bd1903d3e041p7',1,".$objPDO->quote(Txt::trad("INSTALL_dataDashboardPollC")).",3)");
				$objPDO->query("INSERT INTO ap_objectTarget (objectType, _idObject, _idSpace, target, accessRight) VALUES ('dashboardPoll', 1, 1, 'spaceUsers', 1)");
				//INSERT LE PREMIER EVT SUR L'AGENDA COMMUN
				$objPDO->query("INSERT INTO ap_calendarEvent SET title=".$objPDO->quote(Txt::trad("INSTALL_dataCalendarEvt")).", dateBegin=NOW(), dateEnd=NOW(), contentVisible='public', dateCrea=NOW(), _idUser=1");
				$objPDO->query("INSERT INTO ap_calendarEventAffectation SET _idEvt=1, _idCal=1, confirmed=1");
				//INSERT LE PREMIER SUJET DU FORUM
				$objPDO->query("INSERT INTO ap_forumSubject SET title=".$objPDO->quote(Txt::trad("INSTALL_dataForumSubject1")).", description=".$objPDO->quote(Txt::trad("INSTALL_dataForumSubject2")).", dateCrea=NOW(), _idUser=1");
				$objPDO->query("INSERT INTO ap_objectTarget SET objectType='forumSubject', _idObject=1, _idSpace=1, target='spaceUsers', accessRight='1.5'");
	/***************************************************************************************************************************/

				//REDIRECTION AVEC NOTIFICATION
				$result["redirSuccess"]="index.php?disconnect=1&msgNotif[]=INSTALL_installOk";
			}
			//RENVOI LE RESULTAT
			echo json_encode($result);
		}
		////	Affiche le formulaire
		else
		{
			Txt::loadTrads();
			static::displayPage("VueInstall.php");
		}
	}

	/*
	 * Verifie la connexion à la DataBase
	 */
	public static function installDbControl($db_host, $db_login, $db_password, $db_name)
	{
		//Connection PDO
		try{
			//Vérif la connexion à la db
			$objPDO=new PDO("mysql:host=".$db_host.";dbname=".$db_name.";charset=utf8;", $db_login, $db_password, array(PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION));
			//Vérif si l'appli est déjà installée sur la db
			$result=$objPDO->query("SHOW TABLES FROM `".$db_name."` WHERE `Tables_in_".$db_name."` LIKE 'gt_%' OR `Tables_in_".$db_name."` LIKE 'ap_%'");
			if(count($result->fetchAll(PDO::FETCH_COLUMN,0))>0)  {return "dbErrorAppInstalled";}//Erreur: L'application est déjà installée
		}
		//Erreur de connexion à Mysql/bdd
		catch(PDOException $exception){
			if(preg_match("/(unknown|inconnue)/i",$exception->getMessage()))	{return "dbToCreate";}				//Erreur: Database non créé => on créé automatiquement la DB !
			elseif(preg_match("/(denied|interdit)/i",$exception->getMessage()))	{return "dbErrorIdentification";}	//Erreur: User Mysql non identifié
			else																{return "dbErrorUnknown";}			//Erreur: Probleme d'accès inconnu
		}
		//Pas d'erreur : Db disponible
		return "dbAvailable";
	}

	/*
	 * AJAX : Test le password de connexion à un espace public
	 */
	public static function actionPublicSpaceAccess()
	{
		$password=Db::getVal("SELECT count(*) FROM ap_space WHERE _id=".Db::formatParam("_idSpace")." AND BINARY password=".Db::formatParam("password"));//"BINARY"=>case sensitive
		echo (empty($password)) ? "false" : "true";
	}

	/*
	 * AJAX : Authentification via gSignIn
	 */
	public static function actionGSigninAuth()
	{
		//Récup l'API Google Sign-In pour vérif de l'user
		require_once 'app/misc/google-api-php-client/vendor/autoload.php';
		$gClient=new Google_Client(["client_id"=>Ctrl::$agora->gSigninClientId()]);//Charge l'API avec le "ClientId"
		$gClientUser=$gClient->verifyIdToken(Req::getParam("id_token"));//Vérifie le token du client et récupère ses infos
		//User vérifié par l'API
		if(!empty($gClientUser))
		{
			//Verif si un compte utilisateur avec le même email existe sur l'espace
			$tmpUser=Db::getLine("SELECT * FROM ap_user WHERE login=".Db::format($gClientUser["email"]));
			if(!empty($tmpUser))
			{
				//Récup l'user (obj) && Enregistre login & password pour une connexion auto
				$objUser=Ctrl::getObj("user",$tmpUser);
				setcookie("AGORAP_LOG", $objUser->login, (time()+315360000));
				setcookie("AGORAP_PASS", $objUser->password, (time()+315360000));
				//Enregistre l'image de l'user?
				if($objUser->hasImg()==false && !empty($gClientUser["picture"])){
					$tmpImagePath=sys_get_temp_dir()."/".uniqid().".".File::extension($gClientUser["picture"]);
					file_put_contents($tmpImagePath, file_get_contents($gClientUser["picture"]));
					if(is_file($tmpImagePath) && filesize($tmpImagePath)>0)  {File::imageResize($tmpImagePath,$objUser->pathImgThumb(),200);}
				}
				//Retour OK
				echo "userConnected";
			}
		}
	}
}