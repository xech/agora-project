<?php
/**
* This file is part of the Agora-Project Software package
*
* @copyleft Agora-Project <https://www.agora-project.net>
* @license GNU General Public License (GPL-2.0)
*/


/*
 * MODELE DES UTILISATEURS
 */
class MdlUser extends MdlPerson
{
	const moduleName="user";
	const objectType="user";
	const dbTable="ap_user";
	const isSelectable=true;
	public static $requiredFields=["login","name"];//password facultatif si modification de l'user
	public static $sortFields=["name@@asc","name@@desc","firstName@@asc","firstName@@desc","civility@@asc","civility@@desc","postalCode@@asc","postalCode@@desc","city@@asc","city@@desc","country@@asc","country@@desc","function@@asc","function@@desc","companyOrganization@@asc","companyOrganization@@desc","dateCrea@@desc","dateCrea@@asc","lastConnection@@asc","lastConnection@@desc"];
	//Init le cache
	private $_userSpaces=null;
	private $_isAdminCurSpace=null;
	private $_usersVisibles=null;

	/********************************************************************************************************
	 * PHOTO D'UN UTILISATEUR
	 ********************************************************************************************************/
	public function pathImgThumb()
	{
		return PATH_MOD_USER.$this->_id."_thumb.jpg";
	}

	/********************************************************************************************************
	 * VERIFIE SI L'USER EST UN INVITÉ/GUEST
	 ********************************************************************************************************/
	public function isGuest()
	{
		return empty($this->_id);
	}

	/********************************************************************************************************
	 * VERIFIE SI L'USER EST BIEN IDENTIFIÉ (..SINON C'EST UN INVITÉ/GUEST)
	 ********************************************************************************************************/
	public function isUser()
	{
		return (!empty($this->_id));
	}

	/********************************************************************************************************
	 * VERIFIE SI L'USER EST UN ADMINISTRATEUR GÉNÉRAL
	 ********************************************************************************************************/
	public function isGeneralAdmin()
	{
		return (!empty($this->generalAdmin));
	}

	/********************************************************************************************************
	 * VERIFIE SI L'USER EST UN ADMINISTRATEUR DE L'ESPACE COURANT
	 ********************************************************************************************************/
	public function isSpaceAdmin()
	{
		if($this->_isAdminCurSpace===null)	{$this->_isAdminCurSpace=(Ctrl::$curSpace->accessRightUser($this)==2);}
		return $this->_isAdminCurSpace;
	}

	/********************************************************************************************************
	 * SURCHARGE : VERIF SI L'AUTEUR DE L'OBJET == L'USER CONNECTÉ
	 ********************************************************************************************************/
	public function isAutor(){
		return (Ctrl::$curUser->isUser() && ($this->_id==Ctrl::$curUser->_id || $this->_idUser==Ctrl::$curUser->_id));
	}

	/********************************************************************************************************
	 * SURCHARGE : DROIT D'ACCÈS À L'OBJET
	 ********************************************************************************************************/
	public function accessRight()
	{
		if($this->_accessRight===null){
			$this->_accessRight=parent::accessRight();						//Droit d'accès à l'user
			if(empty($this->_accessRight)){									//Pas d'accès : Ajoute l'accès en lecture pour le messenger
				foreach(Ctrl::$curUser->usersVisibles() as $tmpUser){		//Parcourt les "usersVisibles()" de l'user courant
					if($this->_id==$tmpUser->_id)  {$this->_accessRight=1;}
				}
			}
		}
		return $this->_accessRight;
	}

	/********************************************************************************************************
	 * SURCHARGE : DROIT D'ÉDITION => ACCÈS TOTAL
	 ********************************************************************************************************/
	public function editRight()
	{
		return ($this->accessRight()==3);
	}

	/********************************************************************************************************
	 * SURCHARGE : DROIT DE SUPPRESSION
	 ********************************************************************************************************/
	public function deleteRight()
	{
		//Accès total  &&  Pas l'user courant  &&  Pas le dernier "generalAdmin"
		return ($this->editRight()  &&  $this->_id!=Ctrl::$curUser->_id  &&  ($this->isGeneralAdmin()==false || Db::getVal("SELECT count(*) FROM ap_user WHERE generalAdmin=1")>=2));
	}

	/********************************************************************************************************
	 * DROIT DE DESAFFECTATION DE L'ESPACE
	 ********************************************************************************************************/
	public function deleteFromCurSpaceRight()
	{
		return (Ctrl::$curUser->isSpaceAdmin() && Ctrl::$curSpace->allUsersAffected()==false);
	}

	/********************************************************************************************************
	 * LE DROIT "ADMIN GENERAL" PEUT ÊTRE ÉDITÉ PAR L'USER COURANT?
	 ********************************************************************************************************/
	public function editAdminGeneralRight()
	{
		return (Ctrl::$curUser->isGeneralAdmin() && Ctrl::$curUser->_id!=$this->_id);
	}

	/********************************************************************************************************
	 * VERIF SI L'USER COURANT PEUT ENVOYER DES INVITATIONS
	********************************************************************************************/
	public function sendInvitationRight($objSpace=null)
	{
		if($objSpace==null)	{$objSpace=Ctrl::$curSpace;}
		return (Tool::mailEnabled() && ($this->isSpaceAdmin() || (!empty($objSpace->usersInvitation) && $this->isUser())));
	}

	/********************************************************************************************************
	 * VERIF SI LE MESSENGER EST ACTIVÉ POUR L'USER COURANT (Cf. "actionUserEditMessenger()")
	 ********************************************************************************************************/
	public function messengerEnabled()
	{
		if(empty($_SESSION["curUserMessengerEnabled"]))
			{$_SESSION["curUserMessengerEnabled"]=(self::agoraMessengerEnabled() && Db::getVal("SELECT count(*) FROM ap_userMessenger WHERE _idUserMessenger=".$this->_id)>0);}
		return $_SESSION["curUserMessengerEnabled"];
	}

	/********************************************************************************************************
	 * VERIF SI LE MESSENGER EST ACTIVÉ DANS LE PARAM. GENERAL ET QUE L'USER N'EST PAS UN GUEST
	 ********************************************************************************************************/
	public static function agoraMessengerEnabled()
	{
		return (!empty(Ctrl::$agora->messengerDisplay) && Ctrl::$curUser->isUser());
	}

	/********************************************************************************************************
	 * SURCHARGE : SELECTIONNE LES USERS DE TOUT LE SITE OU LES USERS DE L'ESPACE COURANT
	 ********************************************************************************************************/
	public static function sqlDisplay($containerObj=null, $keyId=null)
	{
		return ($_SESSION["displayUsers"]=="all")  ?  "1"  :  "_id IN (".Ctrl::$curSpace->getUsers("idsSql").")";
	}

	/********************************************************************************************************
	 * ESPACES AUXQUELS EST AFFECTÉ L'UTILISATEUR
	 * Retourne un tableau "objects" ou "ids"
	 ********************************************************************************************************/
	public function spaceList($return="objects")
	{
		if($this->_userSpaces===null){
			if($this->isGeneralAdmin())	{$sqlQuery="SELECT * FROM ap_space ORDER BY name ASC";}//Admin général : tous les espaces
			elseif($this->isUser())		{$sqlQuery="SELECT DISTINCT T1.* FROM ap_space T1 LEFT JOIN ap_joinSpaceUser T2 ON T1._id=T2._idSpace WHERE T2._idUser=".$this->_id." OR T2.allUsers=1 ORDER BY name ASC";}//User lambda : espaces affectés
			else						{$sqlQuery="SELECT * FROM ap_space WHERE public=1 ORDER BY name ASC";}//Guest : espaces publics
			$this->_userSpaces=Db::getObjTab("space",$sqlQuery);
		}
		// Retourne un tableau d'objets
		if($return=="objects")  {return $this->_userSpaces;}
		// Retourne un tableau d'identifiants
		else{
			$tabIds=[];
			foreach($this->_userSpaces as $objSpace)  {$tabIds[]=$objSpace->_id;}
			return $tabIds;
		}
	}

	/********************************************************************************************************
	 * SURCHARGE : SUPPRIME UN USER DÉFINITIVEMENT (ADMIN GÉNÉRAL UNIQUEMENT!)
	 ********************************************************************************************************/
	public function delete()
	{
		if($this->deleteRight())
		{
			if($this->profileImgExist())  {unlink($this->pathImgThumb());}
			// Suppression des tables de jointures et tables annexes
			Db::query("DELETE FROM ap_joinSpaceUser			WHERE _idUser=".$this->_id);
			Db::query("DELETE FROM ap_objectTarget			WHERE target=".Db::format("U".$this->_id));
			Db::query("DELETE FROM ap_userLivecouter		WHERE _idUser=".$this->_id);
			Db::query("DELETE FROM ap_userMessenger			WHERE _idUserMessenger=".$this->_id." OR _idUser=".$this->_id);
			Db::query("DELETE FROM ap_userMessengerMessage	WHERE _idUser=".$this->_id);
			Db::query("DELETE FROM ap_userPreference		WHERE _idUser=".$this->_id);
			Db::query("DELETE FROM ap_objectLike			WHERE _idUser=".$this->_id);
			Db::query("UPDATE ap_userGroup					SET _idUsers=REPLACE(_idUsers,'@@".$this->_id."@@','')");
			//Suppr l'agenda perso
			$objCalendar=Ctrl::getObj("calendar", Db::getLine("SELECT * FROM ap_calendar WHERE type='user' AND _idUser=".$this->_id));
			$objCalendar::$isUserDelete=true;//Suppression d'agenda perso via "MdlCalendar::deleteRight()"
			$objCalendar->delete();
			//Suppr l'user
			parent::delete();
		}
	}

	/********************************************************************************************************
	 * SURCHARGE : DÉSAFFECTE/SUPPRIME UN USER D'UN ESPACE (ADMIN D'ESPACE UNIQUEMENT!)
	 ********************************************************************************************************/
	public function deleteFromCurSpace($_idSpace)
	{
		if(Ctrl::$curUser->isSpaceAdmin()){
			Db::query("DELETE FROM ap_joinSpaceUser WHERE _idUser=".$this->_id." AND _idSpace=".(int)$_idSpace);
			if(Db::getVal("SELECT count(*) FROM ap_joinSpaceUser WHERE _idSpace=".(int)$_idSpace." AND allUsers=1")>0)  {Ctrl::notify("USER_allUsersOnSpace");}
		}
	}

	/********************************************************************************************************
	 *  USERS QUE L'USER COURANT PEUT VOIR SUR L'ENSEMBLE DE SES ESPACES
	 ********************************************************************************************************/
	public function usersVisibles($mailFilter=false)
	{
		if($this->_usersVisibles===null){
			$idsSql=null;
			foreach($this->spaceList() as $objSpace)  {$idsSql.=",".$objSpace->getUsers("idsSql");}
			$this->_usersVisibles=Db::getObjTab("user", "SELECT * FROM ap_user WHERE _id IN (".trim($idsSql,",").") ORDER BY ".Ctrl::$agora->personsSort);
		}
		//Par défaut, on enlève l'user courant  /  "mailFilter" => garde uniquement les users avec mail (cf. notifMailUsers)
		$usersVisibles=$this->_usersVisibles;
		if($mailFilter==false)  {unset($usersVisibles[Ctrl::$curUser->_id]);}
		else{
			foreach($usersVisibles as $tmpUser){
				if(empty($tmpUser->mail))  {unset($usersVisibles[$tmpUser->_id]);}
			}
		}
		return $usersVisibles;
	}

	/*********************************************************************************************************
	 * VERIFIE SI LE LOGIN EXISTE DÉJÀ CHEZ UN USER (OU CHEZ UN AUTRE USER, SI ON MODIFIE LE LOGIN D'UN USER)
	 *********************************************************************************************************/
	public static function loginExists($login, $_idUserIgnore=null)
	{
		$sql_idUserIgnore=(!empty($_idUserIgnore))  ?  "AND _id!=".(int)$_idUserIgnore  :  null;
		return (!empty($login)  &&  Db::getVal("SELECT count(*) FROM ap_user WHERE (login=".Db::format($login)." OR mail=".Db::format($login).") ".$sql_idUserIgnore)>0);
	}

	/********************************************************************************************************
	 * NOMBRE D'UTILISATEURS MAXI DÉJÀ ATTEINT?
	 ********************************************************************************************************/
	public static function usersQuotaOk($notify=true)
	{
		//Quota Ok ...sinon on ajoute une notif?
		if(self::usersQuotaRemaining()>0)  {return true;}
		else{
			if($notify==true){
				$msgUgrade=(Req::isHost()) ? Host::notifUpgradeUsers() : null;//Propose l'upgrade?
				Ctrl::notify(Txt::trad("NOTIF_usersNb")." ".limite_nb_users.$msgUgrade);
			}
			return false;
		}
	}

	/********************************************************************************************************
	 * NOMBRE D'UTILISATEURS RESTANT
	 ********************************************************************************************************/
	public static function usersQuotaRemaining()
	{
		if(defined("limite_nb_users"))  {return (int)(limite_nb_users - Db::getVal("SELECT count(*) FROM ap_user"));}
	}

	/********************************************************************************************************
	 * SURCHARGE : AJOUT/MODIF D'UTILISATEUR
	 ********************************************************************************************************/
	public function editRecord($sqlFields, $login=null, $clearPassword=null, $spaceId=null)
	{
		////	Controles : quota atteint ? Login existe déjà ?
		if($this->isNew() && static::usersQuotaOk()==false)  {return false;}
		if(self::loginExists($login,$this->_id))   {Ctrl::notify(Txt::trad("USER_loginExists")." (".$login.")");  return false;}
		////	Ajoute le login, le password? si l'agenda perso est désactivé?
		$sqlFields=trim(trim($sqlFields),",");
		$sqlFields.=", `login`=".Db::format($login);
		if(!empty($clearPassword))  {$sqlFields.=", `password`=".Db::format(password_hash($clearPassword,PASSWORD_DEFAULT));}
		////	Nouvel User : ajoute le parametrage du messenger, l'agenda perso, et si besoin affecte l'user à un Espace.
		$reloadedObj=parent::editRecord($sqlFields);
		if($reloadedObj->isNewRecord()){
			Db::query("INSERT INTO ap_userMessenger SET _idUserMessenger=".$reloadedObj->_id.", allUsers=1");//Affecte l'user à tout le monde sur le messenger
			Db::query("INSERT INTO ap_calendar SET _idUser=".$reloadedObj->_id.", type='user'");//créé l'agenda, même si l'agenda est désactivé par défaut
			if(!empty($spaceId)){
				$tmpSpace=Ctrl::getObj("space",$spaceId);
				if($tmpSpace->allUsersAffected()==false)  {Db::query("INSERT INTO ap_joinSpaceUser SET _idSpace=".(int)$spaceId.", _idUser=".$reloadedObj->_id.", accessRight=1");}
			}
		}
		////	Retourne l'objet rechargé
		return $reloadedObj;
	}

	/********************************************************************************************************
	 * CREATION D'USER : ENVOI DES CREDENTIALS PAR EMAIL (LOGIN/PASS)
	 ********************************************************************************************************/
	public function createCredentialsMail($clearPassword, $hidePassword=false)
	{
		//Récupère l'email (login en priorité)
		$mailTo=(Txt::isMail($this->login))  ?  $this->login  :  $this->mail;
		//Email non spécifié / Envoi du mail de reset de password
		if(Txt::isMail($mailTo)==false)  {Ctrl::notify("email not specified");}
		else{
			$passwordLabel=($hidePassword==true)  ?  substr_replace($clearPassword,'*****',-5)  :  $clearPassword;			//Password avec les 5 derniers caractères masqués
			$connectUrl=Req::curUrl()."/index.php?login=".$this->login;														//Url vers l'espace
			$mailSubject=Txt::trad("USER_mailNotifObject").' '.ucfirst(Ctrl::$agora->name);									//"Bienvenue sur Mon-espace"
			$mailMessage=Txt::trad("USER_mailNotifContent").' <i>'.Ctrl::$agora->name.' - '.Req::curUrl(false).'</i>'.		//"Votre compte utilisateur vient d'être créé sur 'Mon espace - www.mon-espace.net'"
						 '<br><br><a href="'.$connectUrl.'" target="_blank">'.Txt::trad("USER_mailNotifContent2").'</a> :'.	//"Connectez-vous ici avec les coordonnées suivantes"
						 '<br><br>'.Txt::trad("mailLlogin").' : <b>'.$this->login.'</b>'.									//"Email / Identifiant de connexion"
						 '<br>'.Txt::trad("passwordByDefault")." : <b>".$passwordLabel.'</b>'.								//"Mot de passe par défaut"
						 '<br><br>'.Txt::trad("USER_mailNotifContent3");													//"Pensez à conserver cet email dans vos archives"
			return Tool::sendMail($mailTo, $mailSubject, $mailMessage);
		}
	}

	/*****************************************************************************************************
	 * PASSWORD HASHÉ (SALT+SHA1)	=> OBSOLETE DEPUIS v23.4 : GARDER POUR RÉTRO-COMPATIBILITÉ TEMPORAIRE
	 *****************************************************************************************************/
	public static function passwordSha1($clearPassword)
	{
		$passwordSalt=(!defined("AGORA_SALT") || empty(AGORA_SALT))  ?  "Ag0rA-Pr0j3cT"  :  AGORA_SALT;
		return sha1($passwordSalt.sha1($clearPassword));
	}

	/********************************************************************************************************
	 * IDENTIFIANT TEMPORAIRE POUR LA RÉINITIALISATION DU PASSWORD
	 ********************************************************************************************************/
	public function resetPasswordId()
	{
		return sha1($this->login.$this->password);
	}

	/********************************************************************************************************
	 * ENVOI DU MAIL DE RESET DE PASSWORD
	 ********************************************************************************************************/
	public function resetPasswordSendMail()
	{
		//Récupère l'email (champ login ou mail)
		$mailTo=(Txt::isMail($this->login))  ?  $this->login  :  $this->mail;
		//Email non spécifié / Envoi du mail de reset de password
		if(Txt::isMail($mailTo)==false)  {Ctrl::notify("email not specified");}
		else{
			$resetPasswordUrl=Req::curUrl()."/index.php?ctrl=offline&resetPasswordMail=".urlencode($mailTo)."&resetPasswordId=".$this->resetPasswordId();
			$mailSubject=Txt::trad("resetPasswordMailTitle");
			$mailMessage=Txt::trad("MAIL_hello").',<br><br>'.
					 	 '<b>'.Txt::trad("resetPasswordMailPassword").' <a href="'.$resetPasswordUrl.'" target="_blank">'.Txt::trad("resetPasswordMailPassword2").'</a></b>'.
					 	 '<br><br>'.Txt::trad("resetPasswordMailLoginRemind").' : <i>'.$this->login.'</i>';
			return Tool::sendMail($mailTo, $mailSubject, $mailMessage, ["noNotify"]);
		}
	}
}