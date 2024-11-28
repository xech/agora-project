<?php
/**
* This file is part of the Agora-Project Software package
*
* @copyleft Agora-Project <https://www.agora-project.net>
* @license GNU General Public License, version 2 (GPL-2.0)
*/


/*
 * CONTROLEUR DU MODULE "USER"
 */
class CtrlUser extends Ctrl
{
	const moduleName="user";
	public static $moduleOptions=["allUsersAddGroup"];
	public static $MdlObjects=["MdlUser"];

	/*******************************************************************************************
	 * VUE : PAGE PRINCIPALE
	 *******************************************************************************************/
	public static function actionDefault()
	{
		//Affichage des utilisateurs : "space" / "all"
		if(Req::isParam("displayUsers"))	{$_SESSION["displayUsers"]=(Req::param("displayUsers")=="all" && self::$curUser->isGeneralAdmin()) ? "all" : "space";}
		//Filtre Alphabet : avec la première lettre du nom
		$sqlDisplay=MdlUser::sqlDisplay();
		$vDatas["alphabetList"]=Db::getCol("SELECT DISTINCT UPPER(LEFT(`name`,1)) as `initiale` FROM ".MdlUser::dbTable." WHERE ".$sqlDisplay." ORDER BY `initiale`");
		$sqlAlphabetFilter=(Req::isParam("alphabet") && preg_match("/^[A-Z]+$/i",Req::param("alphabet")))  ?  "AND `name` LIKE '".Req::param("alphabet")."%'"  :  null;
		//Utilisateurs et menus
		$sqlDisplayedUsers="SELECT * FROM ".MdlUser::dbTable." WHERE ".$sqlDisplay." ".$sqlAlphabetFilter." ".MdlUser::sqlSort();
		$vDatas["displayedUsers"]=Db::getObjTab("user", $sqlDisplayedUsers." ".MdlUser::sqlPagination());
		$vDatas["usersTotalNb"]=count(Db::getTab($sqlDisplayedUsers));
		$vDatas["usersTotalNbLabel"]=$vDatas["usersTotalNb"]." ".Txt::trad("USER_users");
		if(Ctrl::$curUser->isSpaceAdmin() && Ctrl::$curSpace->allUsersAffected())	{$vDatas["usersTotalNbLabel"]="<span class='abbr' ".Txt::tooltip("USER_allUsersOnSpace").">".$vDatas["usersTotalNbLabel"]."</span>";}
		$vDatas["menuDisplayUsers"]=(Ctrl::$curUser->isGeneralAdmin() && ($_SESSION["displayUsers"]=="all" || count(Ctrl::$curUser->getSpaces())>1));
		$vDatas["userGroups"]=MdlUserGroup::getGroups(Ctrl::$curSpace);
		//Affiche la page
		static::displayPage("VueIndex.php",$vDatas);
	}

	/*******************************************************************************************
	 * PLUGINS DU MODULE
	 *******************************************************************************************/
	public static function getPlugins($params)
	{
		$pluginsList=[];
		if(preg_match("/(search|dashboard)/i",$params["type"]))
		{
			foreach(MdlUser::getPluginObjects($params) as $tmpObj)
			{
				$tmpObj->pluginIcon="user/user.png";
				$tmpObj->pluginLabel=$tmpObj->getLabel("full");
				$tmpObj->pluginTooltip=$tmpObj->pluginLabel;
				$tmpObj->pluginJsIcon=$tmpObj->pluginJsLabel=$tmpObj->openVue();
				$pluginsList[]=$tmpObj;
			}
			return $pluginsList;
		}
		return $pluginsList;
	}

	/*******************************************************************************************
	 * VUE : DÉTAILS D'UN UTILISATEUR
	 *******************************************************************************************/
	public static function actionVueUser()
	{
		$curObj=Ctrl::getObjTarget();
		$curObj->readControl();
		$vDatas["curObj"]=$curObj;
		static::displayPage("VueUser.php",$vDatas);
	}

	/*******************************************************************************************
	 * VUE : EDITION D'UN USER
	 *******************************************************************************************/
	public static function actionUserEdit()
	{
		//Init
		$curObj=Ctrl::getObjTarget();
		$curObj->editControl();
		//Nb max d'utilisateurs dépassé?
		if($curObj->isNew() && MdlUser::usersQuotaOk()==false)  {static::lightboxClose();}
		////	Valide le formulaire
		if(Req::isParam("formValidate"))
		{
			//Enregistre & recharge l'objet
			$sqlProperties="civility=".Db::param("civility").", name=".Db::param("name").", firstName=".Db::param("firstName").", mail=".Db::param("mail").", telephone=".Db::param("telephone").", telmobile=".Db::param("telmobile").", adress=".Db::param("adress").", postalCode=".Db::param("postalCode").", city=".Db::param("city").", country=".Db::param("country").", `function`=".Db::param("function").", companyOrganization=".Db::param("companyOrganization").", `comment`=".Db::param("comment").", connectionSpace=".Db::param("connectionSpace").", lang=".Db::param("lang");
			if($curObj->editAdminGeneralRight())	{$sqlProperties.=", generalAdmin=".Db::param("generalAdmin");}
			if(Ctrl::$curUser->isGeneralAdmin())	{$sqlProperties.=", calendarDisabled=".Db::param("calendarDisabled");}
			$curObj=$curObj->createUpdate($sqlProperties, Req::param("login"), Req::param("password"));//Ajoute login/password pour les controles standards
			//Objet bien créé/existant : Affectations / Images / etc
			if(MdlObject::isObject($curObj))
			{
				//Ajoute/Modifie/Supprime l'image
				$curObj->profileImgRecord();
				//Affectations aux espaces
				if(Ctrl::$curUser->isGeneralAdmin())
				{
					//Réinit les droits
					Db::query("DELETE FROM ap_joinSpaceUser WHERE _idUser=".$curObj->_id);
					//Attribue les affectations
					if(Req::isParam("spaceAffect")){
						foreach(Req::param("spaceAffect") as $curAffect){
							$curAffect=explode("_",$curAffect);//espace 5 + droit 2 : "5_2" => "[5,2]"
							Db::query("INSERT INTO ap_joinSpaceUser SET _idSpace=".$curAffect[0].", _idUser=".$curObj->_id.", accessRight=".$curAffect[1]);
						}
					}
				}
				//Affectation par défaut à l'espace courant  => si nouvel objet sans affectation définies & affichage "espace" & pour un espace dans lequel tous les users ne sont pas affectés
				if($curObj->isNewRecord() && Req::isParam("spaceAffect")==false && $_SESSION["displayUsers"]=="space" && self::$curSpace->allUsersAffected()==false)
					{Db::query("INSERT INTO ap_joinSpaceUser SET _idSpace=".Ctrl::$curSpace->_id.", _idUser=".$curObj->_id.", accessRight=1");}
				//Notification par mail de création d'user
				if(Req::isParam("notifMail") && Req::isParam("mail"))  {$curObj->newUserCoordsSendMail(Req::param("password"));}
			}
			//Ferme la page
			static::lightboxClose();
		}
		////	Affiche le formulaire
		else
		{
			$vDatas["curObj"]=$curObj;
			$vDatas["spaceList"]=Db::getObjTab("space","select * from ap_space");//Espaces disponilbes
			static::displayPage("VueUserEdit.php",$vDatas);
		}
	}

	/*********************************************************************************************************************
	 * DÉSAFFECTATION D'UN USER À UN ESPACE (ou de plusieurs users : cf. "VueObjMenuSelect" et "objectsTypeId()")
	 *********************************************************************************************************************/
	public static function actionDeleteFromCurSpace()
	{
		$urlRedir=null;
		foreach(self::getObjectsTypeId() as $tmpObj){
			if(empty($urlRedir))  {$urlRedir=$tmpObj->getUrl();}
			$tmpObj->deleteFromCurSpace(Ctrl::$curSpace->_id);
		}
		self::redir($urlRedir);
	}

	/*******************************************************************************************
	 * VUE : PARAMETRAGE DU MESSENGER D'UN UTILISATEUR
	 *******************************************************************************************/
	public static function actionUserEditMessenger()
	{
		//Init
		$curObj=Ctrl::getObjTarget();
		$curObj->editControl();
		////	Valide le formulaire
		if(Req::isParam("formValidate") && Req::isParam("messengerDisplay"))
		{
			//Réinitialise
			Db::query("DELETE FROM ap_userMessenger WHERE _idUserMessenger=".$curObj->_id);
			//Affectation à tous OU à certains users?
			if(Req::param("messengerDisplay")=="all")	{Db::query("INSERT INTO ap_userMessenger SET _idUserMessenger=".$curObj->_id.", allUsers=1");}
			elseif(Req::param("messengerDisplay")=="some" && Req::isParam("messengerSomeUsers")){
				foreach(Req::param("messengerSomeUsers") as $_idUser)	{Db::query("INSERT INTO ap_userMessenger SET _idUserMessenger=".$curObj->_id.", _idUser=".(int)$_idUser);}
			}
			//Réinitialise si besoin le "curUserMessengerEnabled" (cf. "messengerEnabled()")
			if($curObj->_id==Ctrl::$curUser->_id)  {$_SESSION["curUserMessengerEnabled"]=null;}
			//Ferme la page
			static::lightboxClose();
		}
		////	Affiche la vue
		$vDatas["allUsers"]=(Db::getVal("SELECT count(*) FROM ap_userMessenger WHERE _idUserMessenger=".$curObj->_id." AND allUsers=1")>0);
		$vDatas["someUsers"]=Db::getCol("SELECT _idUser FROM ap_userMessenger WHERE _idUserMessenger=".$curObj->_id." AND _idUser IS NOT NULL");
		$vDatas["curObj"]=$curObj;
		static::displayPage("VueUserEditMessenger.php",$vDatas);
	}

	/*******************************************************************************************
	 * VUE : IMPORT/EXPORT D'UTILISATEUR
	 *******************************************************************************************/
	public static function actionEditPersonsImportExport()
	{
		////	Controle d'accès && nombre max d'utilisateurs
		if(Ctrl::$curUser->isSpaceAdmin()==false || MdlUser::usersQuotaOk()==false)  {static::lightboxClose();}
		////	Valide le formulaire
		if(Req::isParam("formValidate"))
		{
			//// Export de users
			if(Req::param("actionImportExport")=="export"){
				$userList=Db::getObjTab("user", "SELECT * FROM ".MdlUser::dbTable." WHERE ".MdlUser::sqlDisplay().MdlUser::sqlSort());
				MdlUser::exportPersons($userList, Req::param("exportType"));
			}
			//// Import de users
			elseif(Req::param("actionImportExport")=="import" && Req::isParam("personFields"))
			{
				$personFields=Req::param("personFields");
				foreach(Req::param("personsImport") as $personCpt)
				{
					//Créé l'user
					$curObj=new MdlUser();
					$tmpUser=[];
					$sqlProperties=null;
					//Récupère la valeur de chaque champ
					foreach(Req::param("agoraFields") as $fieldCpt=>$curFieldName){
						$curFieldVal=(!empty($personFields[$personCpt][$fieldCpt]))  ?  $personFields[$personCpt][$fieldCpt]  :  null;//Récupère la valeur correspondante au champ "agora"
						if(!empty($curFieldVal) && !empty($curFieldName) && !preg_match("/^(login|pass)/i",$curFieldName))  {$sqlProperties.="`".$curFieldName."`=".Db::format($curFieldVal).", ";}//Incrémente la requête (sauf si login/password)
						$tmpUser[$curFieldName]=$curFieldVal;//Retient la valeur pour définir le login/password ci-après
					}
					//Login et Password par défaut
					if(empty($tmpUser["login"]) && !empty($tmpUser["mail"]))  {$tmpUser["login"]=$tmpUser["mail"];}//Login email par défaut
					if(empty($tmpUser["login"]))	{$tmpUser["login"]=strtolower( substr(Txt::clean($tmpUser["firstName"],"max",""),0,1).substr(Txt::clean($tmpUser["name"],"max",""),0,8) );}//Ou login prédéfinit par défaut. Ex: "Jean Durant"=>"jdurant"
					if(empty($tmpUser["password"]))	{$tmpUser["password"]=Txt::uniqId(8);}//Password par défaut
					//Enregistre le nouvel utilisateur !
					$curObj=$curObj->createUpdate($sqlProperties, $tmpUser["login"], $tmpUser["password"]);
					//Options de création
					if(MdlObject::isObject($curObj)){
						//Envoi si besoin une notification mail
						if(Req::isParam("notifCreaUser"))  {$curObj->newUserCoordsSendMail($tmpUser["password"]);}
						//Affecte si besoin l'utilisateur aux espaces spécifiés
						if(Req::isParam("spaceAffectList")){
							foreach(Req::param("spaceAffectList") as $_idSpace)  {Db::query("INSERT INTO ap_joinSpaceUser SET _idSpace=".(int)$_idSpace.", _idUser=".$curObj->_id.", accessRight=1");}
						}
					}
				}
				//Ferme la page
				static::lightboxClose();
			}
		}
		////	Affiche le menu d'Import/Export
		$vDatas["curObjClass"]="MdlUser";
		static::displayPage(Req::commonPath."VuePersonsImportExport.php",$vDatas);
	}

	/*******************************************************************************************
	 * VUE : AFFECTATION À L'ESPACE COURANT D'AUTRES USERS PRESENTS SUR LE SITE
	 *******************************************************************************************/
	public static function actionAffectUsers()
	{
		//Administrateur de l'espace courant?
		if(Ctrl::$curUser->isSpaceAdmin()==false)  {static::lightboxClose();}
		////	Valide l'un des deux formulaires
		if(Req::isParam("formValidate"))
		{
			////	Recherche d'users
			if(Req::isParam("searchFields")){
				$sqlSearch=null;
				foreach(Req::param("searchFields") as $fieldName=>$fieldVal){
					if(!empty($fieldVal)){
						$sqlSearch.="OR ".$fieldName." LIKE '%".Db::format($fieldVal,"noquotes")."%' ";
						$vDatas["searchFieldsValues"][$fieldName]=$fieldVal;
					}
				}
				//Users pouvant être affectés à l'espace courant
				if(!empty($sqlSearch)){
					$vDatas["usersList"]=Db::getObjTab("user", "SELECT * FROM ".MdlUser::dbTable." WHERE _id NOT IN (".Ctrl::$curSpace->getUsers("idsSql").") AND (".trim($sqlSearch,"OR").")");
				}
			}
			////	Affecte les users sélectionnés
			elseif(!empty(Req::param("usersList"))){
				foreach(Req::param("usersList") as $_idUser){
					if(is_numeric($_idUser))  {Db::query("INSERT INTO ap_joinSpaceUser SET _idSpace=".Ctrl::$curSpace->_id.",  _idUser=".$_idUser.", accessRight=1");}
				}
				static::lightboxClose();
			}
		}
		////	Affiche l'un des deux formulaires (recherche d'users & sélection d'users)
		$vDatas["searchFields"]=array("name","firstName","mail");
		static::displayPage("VueAffectUsers.php",$vDatas);
	}

	/*******************************************************************************************
	 * VUE : ENVOI UN EMAIL POUR REINITIALISER LES COORDONNEES DE CONNEXION D'USERS
	 *******************************************************************************************/
	public static function actionResetPasswordSendMailUsers()
	{
		////	Admin general uniquement
		if(Ctrl::$curUser->isGeneralAdmin()==false)  {static::lightboxClose();}
		////	Valide le formulaire : envoi de plusieurs mails en série !
		if(Req::isParam("formValidate") && Req::isParam("usersList")){
			foreach(Req::param("usersList") as $userId)  {$isSendmail=Ctrl::getObj("user",$userId)->resetPasswordSendMail();}
			if($isSendmail==true)  {Ctrl::notify("MAIL_sendOk","success");}
			static::lightboxClose();
		}
		////	Affichage du formulaire
		$vDatas["usersList"]=Db::getObjTab("user", "SELECT * FROM ".MdlUser::dbTable." WHERE ".MdlUser::sqlDisplay()." AND LENGTH(mail)>0 AND _id!=".Ctrl::$curUser->_id." ".MdlUser::sqlSort());
		static::displayPage("VueResetPasswordSendMailUsers.php",$vDatas);
	}

	/*******************************************************************************************
	 * VUE : ENVOI D'INVITATION
	 *******************************************************************************************/
	public static function actionSendInvitation()
	{
		////	Droit d'envoyer des invitations?  Nb max d'utilisateurs dépassé?
		if(Ctrl::$curUser->sendInvitationRight()==false || MdlUser::usersQuotaOk()==false)  {static::lightboxClose();}
		////	Validation du formulaire (Ajax)
		if(Req::isParam("formValidate"))
		{
			$invitList=[];
			//Contacts du formulaire simple
			if(Txt::isMail(Req::param("mail")))  {$invitList[]=["firstName"=>Req::param("firstName"), "name"=>Req::param("name"), "mail"=>Req::param("mail")];}
			//Ou contacts importés via gPeople
			elseif(Req::isParam("gPeopleContacts")){
				foreach(Req::param("gPeopleContacts") as $contactTmp){
					$contactTmp=explode("@@",$contactTmp);
					if(Txt::isMail($contactTmp[2]))  {$invitList[]=["firstName"=>$contactTmp[0], "name"=>$contactTmp[1], "mail"=>$contactTmp[2]];}
				}
			}
			//Envoi de chaque invitation
			if(!empty($invitList))
			{
				foreach($invitList as $invitationTmp)
				{
					$_idInvitation=Txt::uniqId();
					$password=Txt::uniqId(8);
					$confirmUrl=Req::getCurUrl()."/index.php?ctrl=offline&disconnect=1&_idInvitation=".$_idInvitation."&mail=".urlencode($invitationTmp["mail"]);
					//Envoi du mail d'invitation
					$mailSubject=Txt::trad("USER_mailInvitationObject").' '.Ctrl::$curUser->getLabel();														//"Invitation de Jean DUPOND"
					$mailMessage='<b>'.Ctrl::$curUser->getLabel().' '.Txt::trad("USER_mailInvitationFromSpace").' <i>'.Ctrl::$curSpace->name.' :</i></b>'.	//"Jean DUPOND vous invite sur l'espace 'Espace Bidule'"
								 '<br><br>'.Txt::trad("mailLlogin").' : <b>'.$invitationTmp["mail"].'</b>'.													//"Email / Identifiant de connexion : truc@bidule.com"
								 '<br>'.Txt::trad("passwordToModify").' : <b>'.$password.'</b>'.															//"Mot de passe temporaire (à modifier en page de connexion) : XXXXX"
								 '<br><br><a href="'.$confirmUrl.'" target="_blank"><u><b>'.Txt::trad("USER_mailInvitationConfirm").'</u></b></a>'; 		//"Cliquez ici pour confirmer l'invitation"
					if(Req::isParam("comment"))  {$mailMessage.='<br><br>'.Txt::trad("comment").':<br>'.Req::param("comment");}								//"Mon commentaire..."
					$isSendMail=Tool::sendMail($invitationTmp["mail"], $mailSubject, $mailMessage, ["noTimeControl"]);										//"noTimeControl" pour l'envoi de mails en série
					//On ajoute l'invitation temporaire
					if($isSendMail==true)  {Db::query("INSERT INTO ap_invitation SET _idInvitation=".Db::format($_idInvitation).", _idSpace=".(int)Ctrl::$curSpace->_id.", name=".Db::format($invitationTmp["name"]).", firstName=".Db::format($invitationTmp["firstName"]).", mail=".Db::format($invitationTmp["mail"]).", `password`=".Db::format($password).", dateCrea=".Db::dateNow().", _idUser=".Ctrl::$curUser->_id);}
				}
			}
			//Ferme la page
			static::lightboxClose();
		}
		////	Affiche le formulaire
		else
		{
			//Supprime les invitations non confirmées depuis 6 mois  &&  Supprime au besoin une invitation spécifique
			Db::query("DELETE FROM ap_invitation WHERE UNIX_TIMESTAMP(dateCrea) < '".(time()-(86400*180))."'");
			if(Req::isParam("deleteInvitation"))  {Db::query("DELETE FROM ap_invitation WHERE _idUser=".Ctrl::$curUser->_id." AND _idInvitation=".Db::param("_idInvitation"));}
			//Affiche le formulaire
			$vDatas["userFields"]=array("name","firstName","mail");
			$vDatas["invitationList"]=Db::getTab("SELECT * FROM ap_invitation WHERE _idUser=".Ctrl::$curUser->_id);
			static::displayPage("VueSendInvitation.php",$vDatas);
		}
	}

	/*******************************************************************************************
	 * AJAX : VÉRIFIE LA PRÉSENCE D'UN COMPTE USER OU DE PLUSIEURS COMPTES USERS (cf "vueSendInvitation.php">"gPeopleGetContacts()")
	 *******************************************************************************************/
	public static function actionloginExists()
	{
		//Vérif un seul compte user
		if(Req::isParam("mail") && MdlUser::loginExists(Req::param("mail"),Req::param("_idUserIgnore")))  {echo "true";}
		//Vérif plusieurs comptes user
		elseif(Req::isParam("mailList"))
		{
			$result["mailListPresent"]=[];
			foreach(Req::param("mailList") as $tmpMail){
				if(MdlUser::loginExists($tmpMail))  {$result["mailListPresent"][]=$tmpMail;}
			}
			//Retourne le résultat
			echo json_encode($result);
		}
	}

	/*******************************************************************************************
	 * VUE : EDITION DES GROUPES D'UTILISATEURS
	 *******************************************************************************************/
	public static function actionUserGroupEdit()
	{
		//Droit d'editer/ajouter un groupe?
		if(MdlUserGroup::addRight()==false)  {static::lightboxClose();}
		////	Valide le formulaire : edit un groupe
		if(Req::isParam("formValidate")){
			$curObj=Ctrl::getObjTarget();
			$curObj->editControl();
			$curObj->createUpdate("title=".Db::param("title").", _idSpace=".Ctrl::$curSpace->_id.", _idUsers=".Db::formatTab2txt(Req::param("userList")));
			static::lightboxClose();
		}
		//Users et groupes de l'espace (en 1er un nouveau groupe "vierge")
		$vDatas["usersList"]=Ctrl::$curSpace->getUsers();
		$vDatas["groupList"]=array_merge([new MdlUserGroup()], MdlUserGroup::getGroups(Ctrl::$curSpace));
		foreach($vDatas["groupList"] as $tmpKey=>$tmpGroup){
			if($tmpGroup->editRight()==false)	{unset($vDatas["groupList"][$tmpKey]);}
			else{
				$tmpGroup->tmpId=$tmpGroup->_typeId;
				$tmpGroup->createdBy=($tmpGroup->isNew()==false)  ?  Txt::trad("createBy")." ".$tmpGroup->autorLabel()  :  null;
			}
		}
		//Affiche la page
		static::displayPage("VueUserGroupEdit.php",$vDatas);
	}

	/*******************************************************************************************
	 * INSCRIPTIONS D'USERS SUR LES ESPACES ADMINISTRÉS PAR L'USER COURANT
	 *******************************************************************************************/
	public static function userInscriptionValidate()
	{
		//Mise en cache dans une variable de session
		if(empty($_SESSION["userInscriptionValidate"])){
			$_SESSION["userInscriptionValidate"]=[];
			$userInscriptions=Db::getTab("SELECT * FROM ap_userInscription WHERE _idSpace IN (".implode(",",Ctrl::$curUser->getSpaces("ids")).") ORDER BY _idSpace");//Inscriptions sur les espaces de l'user courant
			foreach($userInscriptions as $tmpInscription){
				if(Ctrl::getObj("space",$tmpInscription["_idSpace"])->editRight())  {$_SESSION["userInscriptionValidate"][]=$tmpInscription;}//Ajoute l'inscription si l'user courant administre l'espace
			};
		}
		//Retourne le résultat
		return $_SESSION["userInscriptionValidate"];
	}

	/*******************************************************************************************
	 * ACTION : VALIDATION DES INSCRIPTIONS A L'ESPACE
	 *******************************************************************************************/
	public static function actionUserInscriptionValidate()
	{
		//Administrateur de l'espace courant?  Nb max d'utilisateurs dépassé?
		if(Ctrl::$curUser->isSpaceAdmin()==false || MdlUser::usersQuotaOk()==false)  {static::lightboxClose();}
		//Validation du formulaire
		if(Req::isParam(["formValidate","inscriptionValidate"]))
		{
			//Traite chaque inscription
			foreach(Req::param("inscriptionValidate") as $idInscription)
			{
				//Récupère l'inscription
				$tmpInscription=Db::getLine("SELECT * FROM ap_userInscription WHERE _id=".Db::format($idInscription));
				//Valide l'inscription (pas de "submitInvalidate")
				if(Req::isParam("submitInvalidate")==false){
					$curObj=new MdlUser();
					$sqlProperties="name=".Db::format($tmpInscription["name"]).", firstName=".Db::format($tmpInscription["firstName"]).", mail=".Db::format($tmpInscription["mail"]);
					$curObj=$curObj->createUpdate($sqlProperties, $tmpInscription["mail"], $tmpInscription["password"], $tmpInscription["_idSpace"]);//Ajoute login/password pour les controles standards
					if(is_object($curObj))  {$curObj->newUserCoordsSendMail($tmpInscription["password"]);}//Notif si l'user a bien été créé
				}
				//Invalide l'inscription et demande d'envoie la notif "Votre compte n'a pas été validé.."
				elseif(Req::isParam(["submitInvalidate","inscriptionNotify"])){
					$mailSubject=$mailMessage=Txt::trad("userInscriptionInvalidateMail")." ''".Ctrl::$agora->name."'' (".Req::getCurUrl(false).")";
					Tool::sendMail($tmpInscription["mail"], $mailSubject, $mailMessage);
				}
				//Supprime l'inscription
				Db::query("DELETE FROM ap_userInscription WHERE _id=".(int)$idInscription);
			}
			//Réinitialise la liste des inscriptions (cf. "userInscriptionValidate()")  &&  Ferme la page
			unset($_SESSION["userInscriptionValidate"]);
			static::lightboxClose();
		}
		//Affiche le formulaire
		static::displayPage("VueUserInscriptionValidate.php");
	}
}