<?php
/**
* This file is part of the Agora-Project Software package.
*
* @copyright (c) Agora-Project Limited <https://www.agora-project.net>
* @license GNU General Public License, version 2 (GPL-2.0)
*/


/*
 * Controleur du module "User"
 */
class CtrlUser extends Ctrl
{
	const moduleName="user";
	public static $moduleOptions=["allUsersAddGroup"];
	public static $MdlObjects=array("MdlUser");

	/*
	 * ACTION PAR DEFAUT
	 */
	public static function actionDefault()
	{
		//Affichage des utilisateurs : "space" / "all"
		if(Req::isParam("displayUsers"))	{$_SESSION["displayUsers"]=(Req::getParam("displayUsers")=="all" && self::$curUser->isAdminGeneral()) ? "all" : "space";}
		//Filtre Alphabet : avec la première lettre du nom
		$vDatas["alphabetList"]=Db::getCol("SELECT DISTINCT UPPER(LEFT(name,1)) as initiale FROM ".MdlUser::dbTable." WHERE ".MdlUser::sqlDisplayedObjects()." ORDER BY initiale");
		$sqlAlphabetFilter=(Req::isParam("alphabet")) ? "AND name LIKE '".Req::getParam("alphabet")."%'" : null;
		//Utilisateurs et menus
		$sqlDisplayedUsers="SELECT * FROM ".MdlUser::dbTable." WHERE ".MdlUser::sqlDisplayedObjects()." ".$sqlAlphabetFilter." ".MdlUser::sqlSort();
		$vDatas["displayedUsers"]=Db::getObjTab("user", $sqlDisplayedUsers." ".MdlUser::sqlPagination());
		$vDatas["usersTotalNb"]=count(Db::getTab($sqlDisplayedUsers));
		$vDatas["usersTotalNbLabel"]=$vDatas["usersTotalNb"]." ".Txt::trad("USER_users");
		if(Ctrl::$curUser->isAdminSpace() && Ctrl::$curSpace->allUsersAffected())	{$vDatas["usersTotalNbLabel"]="<span class='abbr' title=\"".Txt::trad("USER_allUsersOnSpace")."\">".$vDatas["usersTotalNbLabel"]."</span>";}
		$vDatas["menuDisplayUsers"]=(Ctrl::$curUser->isAdminGeneral() && ($_SESSION["displayUsers"]=="all" || count(Ctrl::$curUser->getSpaces())>1)) ? true : false;
		$vDatas["userGroups"]=MdlUserGroup::getGroups(Ctrl::$curSpace);
		//Affiche la page
		static::displayPage("VueIndex.php",$vDatas);
	}

	/*
	 * PLUGINS
	 */
	public static function plugin($pluginParams)
	{
		$pluginsList=array();
		if(preg_match("/(search|dashboard)/i",$pluginParams["type"]))
		{
			foreach(MdlUser::getPluginObjects($pluginParams) as $tmpObj)
			{
				$tmpObj->pluginModule=self::moduleName;
				$tmpObj->pluginIcon="user/accesUser.png";
				$tmpObj->pluginLabel=$tmpObj->getLabel("all");
				$tmpObj->pluginTooltip=$tmpObj->pluginLabel;
				$tmpObj->pluginJsIcon=$tmpObj->pluginJsLabel="lightboxOpen('".$tmpObj->getUrl("vue")."');";//Affiche l'user
				$pluginsList[]=$tmpObj;
			}
			return $pluginsList;
		}
		return $pluginsList;
	}

	/*
	 * ACTION : Vue détaillée d'un utilisateur
	 */
	public static function actionVueUser()
	{
		$curObj=Ctrl::getTargetObj();
		$curObj->controlRead();
		$vDatas["curObj"]=$curObj;
		static::displayPage("VueUser.php",$vDatas);
	}

	/*
	 * ACTION : Edition d'un user
	 */
	public static function actionUserEdit()
	{
		//Init
		$curObj=Ctrl::getTargetObj();
		$curObj->controlEdit();
		//Nb max d'utilisateurs dépassé?
		if($curObj->isNew() && MdlUser::usersQuotaOk()==false)  {static::lightboxClose();}
		////	Valide le formulaire
		if(Req::isParam("formValidate"))
		{
			//Enregistre & recharge l'objet
			$sqlProperties="civility=".Db::formatParam("civility").", name=".Db::formatParam("name").", firstName=".Db::formatParam("firstName").", mail=".Db::formatParam("mail").", telephone=".Db::formatParam("telephone").", telmobile=".Db::formatParam("telmobile").", adress=".Db::formatParam("adress").", postalCode=".Db::formatParam("postalCode").", city=".Db::formatParam("city").", country=".Db::formatParam("country").", function=".Db::formatParam("function").", companyOrganization=".Db::formatParam("companyOrganization").", comment=".Db::formatParam("comment").", connectionSpace=".Db::formatParam("connectionSpace").", lang=".Db::formatParam("lang");
			if($curObj->editAdminGeneralRight())	{$sqlProperties.=", generalAdmin=".Db::formatParam("generalAdmin");}
			if(Ctrl::$curUser->isAdminGeneral())	{$sqlProperties.=", calendarDisabled=".Db::formatParam("calendarDisabled");}
			$curObj=$curObj->createUpdate($sqlProperties, Req::getParam("login"), Req::getParam("password"));//Ajoute login/password pour les controles standards
			//Objet bien créé/existant : Affectations / Images / etc
			if(is_object($curObj))
			{
				//Ajoute/Modifie/Supprime l'image
				$curObj->editImg();
				//Affectations aux espaces
				if(Ctrl::$curUser->isAdminGeneral())
				{
					//Réinit les droits
					Db::query("DELETE FROM ap_joinSpaceUser WHERE _idUser=".$curObj->_id);
					//Attribue les affectations
					if(Req::isParam("spaceAffect")){
						foreach(Req::getParam("spaceAffect") as $curAffect){
							$curAffect=explode("_",$curAffect);//espace 5 + droit 2 : "5_2" => "[5,2]"
							Db::query("INSERT INTO ap_joinSpaceUser SET _idSpace=".$curAffect[0].", _idUser=".$curObj->_id.", accessRight=".$curAffect[1]);
						}
					}
				}
				//Affectation par défaut à l'espace courant  => si nouvel objet sans affectation définies & affichage "espace" & pour un espace dans lequel tous les users ne sont pas affectés
				if($curObj->isNewlyCreated() && Req::isParam("spaceAffect")==false && $_SESSION["displayUsers"]=="space" && self::$curSpace->allUsersAffected()==false)
					{Db::query("INSERT INTO ap_joinSpaceUser SET _idSpace=".Ctrl::$curSpace->_id.", _idUser=".$curObj->_id.", accessRight=1");}
				//Notification par mail de création d'user
				if(Req::isParam("notifMail") && Req::isParam("mail"))  {$curObj->newUserCoordsSendMail(Req::getParam("password"));}
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

	/*
	 * ACTION : désaffectation d'un user à un espace (ou de plusieurs users : cf. "VueObjMenuSelection" et "targetObjectsAction()")
	 */
	public static function actionDeleteFromCurSpace()
	{
		$urlRedir=null;
		foreach(self::getTargetObjects() as $tmpObj){
			if(empty($urlRedir))  {$urlRedir=$tmpObj->getUrl();}
			$tmpObj->deleteFromCurSpace(Ctrl::$curSpace->_id);
		}
		self::redir($urlRedir);
	}

	/*
	 * ACTION : Parametrage du messenger d'un utilisateur
	 */
	public static function actionUserEditMessenger()
	{
		//Init
		$curObj=Ctrl::getTargetObj();
		$curObj->controlEdit();
		////	Valide le formulaire
		if(Req::isParam("formValidate") && Req::isParam("messengerDisplay"))
		{
			//Réinitialise
			Db::query("DELETE FROM ap_userMessenger WHERE _idUserMessenger=".$curObj->_id);
			//Affectation à tous OU à certains users?
			if(Req::getParam("messengerDisplay")=="all")	{Db::query("INSERT INTO ap_userMessenger SET _idUserMessenger=".$curObj->_id.", allUsers=1");}
			elseif(Req::getParam("messengerDisplay")=="some" && Req::isParam("messengerSomeUsers")){
				foreach(Req::getParam("messengerSomeUsers") as $_idUser)	{Db::query("INSERT INTO ap_userMessenger SET _idUserMessenger=".$curObj->_id.", _idUser=".(int)$_idUser);}
			}
			//Ferme la page
			static::lightboxClose();
		}
		////	Affiche la vue
		$vDatas["allUsers"]=(Db::getVal("SELECT count(*) FROM ap_userMessenger WHERE _idUserMessenger=".$curObj->_id." AND allUsers=1")>0) ? true : false;
		$vDatas["someUsers"]=Db::getCol("SELECT _idUser FROM ap_userMessenger WHERE _idUserMessenger=".$curObj->_id." AND _idUser IS NOT NULL");
		$vDatas["curObj"]=$curObj;
		static::displayPage("VueUserEditMessenger.php",$vDatas);
	}

	/*
	 * ACTION : Persons Import-Export
	 */
	public static function actionEditPersonsImportExport()
	{
		////	Controle du droit d'accès et du nombre max d'utilisateurs
		if(Ctrl::$curUser->isAdminSpace()==false || MdlUser::usersQuotaOk()==false)  {static::lightboxClose();}
		////	Validation de formulaire
		if(Req::isParam("formValidate"))
		{
			//Export de users
			if(Req::getParam("actionImportExport")=="export"){
				$userList=Db::getObjTab("user", "SELECT * FROM ".MdlUser::dbTable." WHERE ".MdlUser::sqlDisplayedObjects()." ".MdlUser::sqlSort());
				MdlUser::exportPersons($userList, Req::getParam("exportType"));
			}
			//Import de users
			elseif(Req::getParam("actionImportExport")=="import" && Req::getParam("personFields"))
			{
				$personFields=Req::getParam("personFields");
				//Créé chaque user
				foreach(Req::getParam("personsImport") as $personCpt)
				{
					$curObj=new MdlUser();
					$sqlProperties=null;
					$tmpUser=[];
					//Ajoute chaque champ du user
					foreach(Req::getParam("agoraFields") as $fieldCpt=>$curFieldName){
						$curFieldVal=(!empty($personFields[$personCpt][$fieldCpt]))  ?  $personFields[$personCpt][$fieldCpt]  :  null;
						$tmpUser[$curFieldName]=$curFieldVal;
						if(!empty($curFieldVal) && !empty($curFieldName) && !preg_match("/^(login|pass)/i",$curFieldName))   {$sqlProperties.=$curFieldName."=".Db::format($curFieldVal).", ";}
					}
					//Password par défaut?
					if(empty($tmpUser["password"]))  {$tmpUser["password"]=Txt::uniqId(8);}
					//Login par défaut?
					if(empty($tmpUser["login"]) && !empty($tmpUser["mail"]))	{$tmpUser["login"]=$tmpUser["mail"];}//mail
					if(empty($tmpUser["login"]))	{$tmpUser["login"]=strtolower(substr(Txt::clean($tmpUser["firstName"],"max",""),0,3)).strtolower(substr(Txt::clean($tmpUser["name"],"max",""),0,8));}//"Gérard D'AGOBERT"=>"gerdagobert"
					//Enregistre l'user
					$curObj=$curObj->createUpdate($sqlProperties, $tmpUser["login"], $tmpUser["password"]);//Ajoute login/password pour les controles standards
					//Options de création
					if(is_object($curObj))
					{
						//Envoi une notification mail
						if(Req::isParam("notifCreaUser"))  {$curObj->newUserCoordsSendMail($tmpUser["password"]);}
						//Affecte aux espaces si besoin
						if(Req::isParam("spaceAffectList")){
							foreach(Req::getParam("spaceAffectList") as $_idSpace)	{Db::query("INSERT INTO ap_joinSpaceUser SET _idSpace=".(int)$_idSpace.", _idUser=".$curObj->_id.", accessRight=1");}
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

	/*
	 * ACTION : Affectation d'un user existant à l'espace courant
	 */
	public static function actionAffectUsers()
	{
		//Administrateur de l'espace courant?
		if(Ctrl::$curUser->isAdminSpace()==false)	{static::lightboxClose();}
		////	Validation de formulaire
		if(Req::isParam("formValidate"))
		{
			////	Affectation d'users
			if(Req::isParam("usersList") && count(Req::getParam("usersList"))>0)
			{
				//Affecte chaque user
				foreach(Req::getParam("usersList") as $_idUser){
					if(is_numeric($_idUser))	{Db::query("INSERT INTO ap_joinSpaceUser SET _idSpace=".Ctrl::$curSpace->_id.",  _idUser=".$_idUser.", accessRight=1");}
				}
				//Ferme la page
				static::lightboxClose();
			}
			////	Recherche d'users?
			elseif(Req::isParam("searchFields"))
			{
				//Champs de recherche
				$sqlSearch=null;
				foreach(Req::getParam("searchFields") as $fieldName=>$fieldVal){
					if(!empty($fieldVal)){
						$sqlSearch.="OR ".$fieldName." LIKE '%".Db::format($fieldVal,"noquotes")."%' ";
						$vDatas["searchFieldsValues"][$fieldName]=$fieldVal;
					}
				}
				//Liste des users toujours pas affectés à l'espace courant
				if(!empty($sqlSearch))  {$vDatas["usersList"]=Db::getObjTab("user", "SELECT * FROM ".MdlUser::dbTable." WHERE _id NOT IN (".Ctrl::$curSpace->getUsers("idsSql").") AND (".trim($sqlSearch,"OR").")");}
			}
		}
		////	Formulaire
		$vDatas["searchFields"]=array("name","firstName","mail");
		static::displayPage("VueAffectUsers.php",$vDatas);
	}

	/*
	 * ACTION : Envoie les coordonnées de connexion à des utilisateurs
	 */
	public static function actionSendCoordinates()
	{
		////	Admin general uniquement
		if(Ctrl::$curUser->isAdminGeneral()==false)  {static::lightboxClose();}
		////	Validation de formulaire
		if(Req::isParam("formValidate") && Req::isParam("usersList")){
			foreach(Req::getParam("usersList") as $userId)  {$isSendmail=Ctrl::getObj("user",$userId)->resetPasswordSendMail();}
			if($isSendmail==true)  {Ctrl::addNotif(Txt::trad("MAIL_sendOk"),"success");}
			static::lightboxClose();
		}
		////	Affichage du formulaire
		$vDatas["usersList"]=Db::getObjTab("user", "SELECT * FROM ".MdlUser::dbTable." WHERE ".MdlUser::sqlDisplayedObjects()." AND LENGTH(mail)>0 AND _id!=".Ctrl::$curUser->_id." ".MdlUser::sqlSort());
		static::displayPage("VueSendCoordinates.php",$vDatas);
	}

	/*
	 * ACTION : Envoi d'invitation
	 */
	public static function actionSendInvitation()
	{
		////	Droit d'envoyer des invitations?  Nb max d'utilisateurs dépassé?
		if(Ctrl::$curUser->sendInvitationRight()==false || MdlUser::usersQuotaOk()==false)  {static::lightboxClose();}
		////	Validation du formulaire (Ajax)
		if(Req::isParam("formValidate"))
		{
			$invitList=[];
			//Contacts du formulaire simple
			if(Txt::isMail(Req::getParam("mail")))  {$invitList[]=["firstName"=>Req::getParam("firstName"), "name"=>Req::getParam("name"), "mail"=>Req::getParam("mail")];}
			//Ou contacts importés via gPeople
			elseif(Req::isParam("gPeopleContacts")){
				foreach(Req::getParam("gPeopleContacts") as $contactTmp){
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
					$confirmUrl=Req::getSpaceUrl()."/?ctrl=offline&disconnect=1&_idInvitation=".$_idInvitation."&mail=".urlencode($invitationTmp["mail"]);
					//Envoi du mail d'invitation.  "Invitation de Jean DUPOND"  =>  "Jean DUPOND vous invite à rejoindre l'espace Mon Espace..."
					$subject=Txt::trad("USER_mailInvitationObject")." ".Ctrl::$curUser->getLabel();
					$mainMessage="<b>".Ctrl::$curUser->getLabel()." ".Txt::trad("USER_mailInvitationFromSpace")." ".Ctrl::$curSpace->name." :</b>
								  <br><br>".Txt::trad("login")." : <b>".$invitationTmp["mail"]."</b>
								  <br>".Txt::trad("passwordToModify")." : <b>".$password."</b>
								  <br><br><a href=\"".$confirmUrl."\" target=\"_blank\"><u><b>".Txt::trad("USER_mailInvitationConfirm")."</u></b></a>"; // Confirmer l'invitation ?
					if(Req::isParam("comment"))  {$mainMessage.="<br><br>".Txt::trad("comment").":<br>".Req::getParam("comment");}
					$isSendMail=Tool::sendMail($invitationTmp["mail"], $subject, $mainMessage);
					//On ajoute l'invitation temporaire
					if($isSendMail==true)  {Db::query("INSERT INTO ap_invitation SET _idInvitation=".Db::format($_idInvitation).", _idSpace=".(int)Ctrl::$curSpace->_id.", name=".Db::format($invitationTmp["name"]).", firstName=".Db::format($invitationTmp["firstName"]).", mail=".Db::format($invitationTmp["mail"]).", password=".Db::format($password).", dateCrea=".Db::dateNow().", _idUser=".Ctrl::$curUser->_id);}
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
			if(Req::isParam("deleteInvitation"))  {Db::query("DELETE FROM ap_invitation WHERE _idUser=".Ctrl::$curUser->_id." AND _idInvitation=".Db::formatParam("_idInvitation"));}
			//Affiche le formulaire
			$vDatas["userFields"]=array("name","firstName","mail");
			$vDatas["invitationList"]=Db::getTab("SELECT * FROM ap_invitation WHERE _idUser=".Ctrl::$curUser->_id);
			static::displayPage("VueSendInvitation.php",$vDatas);
		}
	}

	/*
	 * AJAX : Vérifie la présence d'un compte user OU de plusieurs comptes users (cf "vueSendInvitation.php">"gPeopleGetContacts()")
	 */
	public static function actionLoginAlreadyExist()
	{
		//Vérif un seul compte user
		if(Req::isParam("mail") && MdlUser::loginAlreadyExist(Req::getParam("mail"),Req::getParam("_idUserIgnore")))  {echo "true";}
		//Vérif plusieurs comptes user
		elseif(Req::isParam("mailList"))
		{
			$result["mailListPresent"]=[];
			foreach(Req::getParam("mailList") as $tmpMail){
				if(MdlUser::loginAlreadyExist($tmpMail))  {$result["mailListPresent"][]=$tmpMail;}
			}
			//Renvoi le résultat
			echo json_encode($result);
		}
	}

	/*
	 * ACTION : Edition des groupes d'utilisateurs
	 */
	public static function actionUserGroupEdit()
	{
		//Droit d'ajouter un groupe?
		if(MdlUserGroup::addRight()==false)  {static::lightboxClose();}
		////	Validation de formulaire : edit un groupe
		if(Req::isParam("formValidate")){
			$curObj=Ctrl::getTargetObj();
			$curObj->controlEdit();
			$curObj->createUpdate("title=".Db::formatParam("title").", _idSpace=".Ctrl::$curSpace->_id.", _idUsers=".Db::formatTab2txt(Req::getParam("userList")));
			static::lightboxClose();
		}
		//Users et groupes de l'espace
		$vDatas["usersList"]=Ctrl::$curSpace->getUsers();
		$vDatas["groupList"]=MdlUserGroup::getGroups(Ctrl::$curSpace);
		$vDatas["groupList"][]=new MdlUserGroup();
		foreach($vDatas["groupList"] as $tmpKey=>$tmpGroup){
			if($tmpGroup->editRight()==false)	{unset($vDatas["groupList"][$tmpKey]);}
			else{
				$tmpGroup->tmpId=$tmpGroup->_targetObjId;
				$tmpGroup->createdBy=($tmpGroup->isNew()==false)  ?  Txt::trad("creation")." : ".$tmpGroup->displayAutor()  :  null;
			}
		}
		//Affiche la page
		static::displayPage("VueUserGroupEdit.php",$vDatas);
	}

	/*
	 * ACTION : Inscription des utilisateurs au site
	 */
	public static function actionUserInscriptionValidate()
	{
		//Administrateur de l'espace courant?  Nb max d'utilisateurs dépassé?
		if(Ctrl::$curUser->isAdminSpace()==false || MdlUser::usersQuotaOk()==false)	{static::lightboxClose();}
		//Validation du form
		if(Req::isParam("formValidate") && Req::isParam("inscriptionValidate"))
		{
			//Créé chaque utilisateur validé
			foreach(Req::getParam("inscriptionValidate") as $idInscription)
			{
				$tmpInscription=Db::getLine("SELECT * FROM ap_userInscription WHERE _id=".$idInscription);
				//Invalidation/Validation de l'user
				if(Req::isParam("submitInvalidate")){
					$subject=$mainMessage=Txt::trad("userInscriptionInvalidateMail")." ''".Ctrl::$agora->name."'' (".Req::getSpaceUrl(false).")";//"Votre compte n'a pas été validé sur ''Mon_Espace''"
					$mainMessage="<b>".$mainMessage."</b>";
					Tool::sendMail($tmpInscription["mail"], $subject, $mainMessage);
				}else{
					$curObj=new MdlUser();
					$sqlProperties="name=".Db::format($tmpInscription["name"]).", firstName=".Db::format($tmpInscription["firstName"]).", mail=".Db::format($tmpInscription["mail"]);
					$curObj=$curObj->createUpdate($sqlProperties, $tmpInscription["mail"], $tmpInscription["password"], $tmpInscription["_idSpace"]);//Ajoute login/password pour les controles standards
					$curObj->newUserCoordsSendMail($tmpInscription["password"]);
				}
				//Supprime l'inscription
				Db::query("DELETE FROM ap_userInscription WHERE _id=".(int)$idInscription);
			}
			//Ferme la page
			static::lightboxClose();
		}
		//Affiche le formulaire
		$vDatas["inscriptionList"]=Db::getTab("SELECT * FROM ap_userInscription WHERE _idSpace=".Ctrl::$curSpace->_id);
		static::displayPage("VueUserInscriptionValidate.php",$vDatas);
	}
}