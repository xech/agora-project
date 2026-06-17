<?php
/**
* This file is part of the Agora-Project Software package
*
* @copyleft Agora-Project <https://www.agora-project.net>
* @license GNU General Public License (GPL-2.0)
*/


/*
 * CONTROLEUR DU MODULE "CONTACT"
 */
class CtrlContact extends Ctrl
{
	const moduleName="contact";
	public static $folderObjType="contactFolder";
	public static $moduleOptions=["adminRootAddContent"];
	public static $MdlObjects=["MdlContact","MdlContactFolder"];

	/********************************************************************************************************
	 * VUE : PAGE PRINCIPALE
	 ********************************************************************************************************/
	public static function actionDefault()
	{
		$vDatas["contactList"]=Db::getObjTab("contact", "SELECT * FROM ap_contact WHERE ".MdlContact::sqlDisplay(self::$curContainer).MdlContact::sqlSort());
		static::displayPage("VueIndex.php",$vDatas);
	}

	/********************************************************************************************************
	 * PLUGINS DU MODULE
	 ********************************************************************************************************/
	public static function getPlugins($params)
	{
		$pluginsList=MdlContactFolder::getPluginFolders($params);
		foreach(MdlContact::getPluginObjects($params) as $tmpObj){
			$tmpObj->pluginIcon=self::moduleName."/icon.png";
			$tmpObj->pluginLabel=$tmpObj->getLabel("full");
			$tmpObj->pluginTooltip=$tmpObj->containerObj()->folderPath("text");
			$tmpObj->pluginJsIcon="window.top.redir('".$tmpObj->getUrl()."')";//Affiche dans son dossier
			$tmpObj->pluginJsLabel=$tmpObj->lightboxVue();
			$pluginsList[]=$tmpObj;
		}
		return $pluginsList;
	}

	/********************************************************************************************************
	 * VUE : DÉTAILS D'UN CONTACT
	 ********************************************************************************************************/
	public static function actionVueContact()
	{
		$curObj=Ctrl::getCurObj();
		$curObj->readControl();
		$vDatas["curObj"]=$curObj;
		static::displayPage("VueContact.php",$vDatas);
	}

	/********************************************************************************************************
	 * VUE : EDITION D'UN CONTACT
	 ********************************************************************************************************/
	public static function actionVueEditContact()
	{
		//Init
		$curObj=Ctrl::getCurObj();
		$curObj->editControl();
		////	Valide le formulaire
		if(Req::isParam("formValidate")){
			//Enregistre & recharge l'objet
			$curObj=$curObj->editRecord("name=".Db::param("name").", firstName=".Db::param("firstName").", civility=".Db::param("civility").", mail=".Db::param("mail").", telephone=".Db::param("telephone").", telmobile=".Db::param("telmobile").", adress=".Db::param("adress").", postalCode=".Db::param("postalCode").", city=".Db::param("city").", country=".Db::param("country").", `function`=".Db::param("function").", companyOrganization=".Db::param("companyOrganization").", `comment`=".Db::param("comment"));
			//Ajoute/supprime l'image / Notifie par mail & Ferme la page
			$curObj->setProfileImg();
			$curObj->sendMailNotif($curObj->getLabel());
			static::lightboxRedir();
		}
		////	Affiche la vue
		$vDatas["curObj"]=$curObj;
		static::displayPage("VueEditContact.php",$vDatas);
	}

	/********************************************************************************************************
	 * VUE : IMPORT/EXPORT DE CONTACTS
	 ********************************************************************************************************/
	public static function actionVueImportExport()
	{
		////	Controle d'accès  && Folder courant 
		if(Ctrl::$curUser->isSpaceAdmin()==false)  {static::lightboxRedir();}
		$curContainer=Ctrl::$curContainer;//cf param "typeId"
		////	Valide le formulaire
		if(Req::isParam("formValidate")){
			//// Export de contacts
			if(Req::param("actionImportExport")=="export"){
				$contactList=Db::getObjTab("contact", "SELECT * FROM ".MdlContact::dbTable." WHERE ".MdlContact::sqlDisplay($curContainer));
				MdlContact::exportPersons(Req::param("exportType"), $curContainer->getLabel(), $contactList);
			}
			//// Import de contacts
			elseif(Req::param("actionImportExport")=="import" && Req::isParam("personFields")){
				$personFields=Req::param("personFields");
				foreach(Req::param("personsImport") as $personCpt){
					//// Créé le contact  ("_idContainer" pour le controle d'accès via "editRecord()")
					$curObj=new MdlContact();
					$sqlFields="`_idContainer`=".Db::format($curContainer->_id).", ";
					//// Récupère la valeur de chaque champ du contact
					foreach(Req::param("agoraFields") as $fieldCpt=>$fieldName){
						$fieldVal=(!empty($personFields[$personCpt][$fieldCpt]))  ?  $personFields[$personCpt][$fieldCpt]  :  null;
						if(!empty($fieldVal) && !empty($fieldName))  {$sqlFields.="`".$fieldName."`=".Db::format($fieldVal).", ";}
					}
					//// Enregistre le nouveau contact !
					$curObj=$curObj->editRecord($sqlFields);
					//// Nouveau contact du dossier racine : affecte en lecture à "tous les users" de l'espace courant
					if($curContainer->isRootFolder())
						{Db::query("INSERT INTO ap_objectTarget SET objectType=".Db::format($curObj::objectType).", _idObject=".(int)$curObj->_id.", _idSpace=".(int)self::$curSpace->_id.", target='spaceUsers', accessRight='1'");}
				}
				//// Ferme la page
				static::lightboxRedir();
			}
		}
		////	Affiche le menu d'Import/Export
		$vDatas["curContainer"]=$curContainer;
		$vDatas["objectType"]="contact";
		static::displayPage(Req::commonPath."VuePersonsImportExport.php",$vDatas);
	}

	/********************************************************************************************************
	 * ACTION : EXPORT D'UN CONTACT AU FORMAT VCARD
	 ********************************************************************************************************/
	public static function actionExportVcard()
	{
		$curObj=self::getCurObj();
		MdlContact::exportPersons("vcard", $curObj->getLabel(), [$curObj]);
	}

	/********************************************************************************************************
	 * ACTION : CREATION D'UN UTILISATEUR A PARTIR D'UN CONTACT
	 ********************************************************************************************************/
	public static function actionContactAddUser()
	{
		if(Ctrl::$curUser->isGeneralAdmin())
		{
			//Init
			$contactRef=Ctrl::getCurObj();
			$contactRef->editControl();
			//Création du nouveau User
			$newUser=new MdlUser();
			$login=(!empty($contactRef->mail))  ?  $contactRef->mail  :  substr($contactRef->firstName,0,1).substr($contactRef->name,0,5);
			$password=Txt::defaultPassword();
			$sqlFields="civility=".Db::format($contactRef->civility).", name=".Db::format($contactRef->name).", firstName=".Db::format($contactRef->firstName).", mail=".Db::format($contactRef->mail).", telephone=".Db::format($contactRef->telephone).", telmobile=".Db::format($contactRef->telmobile).", adress=".Db::format($contactRef->adress).", postalCode=".Db::format($contactRef->postalCode).", city=".Db::format($contactRef->city).", country=".Db::format($contactRef->country).", `function`=".Db::format($contactRef->function).", companyOrganization=".Db::format($contactRef->companyOrganization).", `comment`=".Db::format($contactRef->comment);
			$newUser=$newUser->editRecord($sqlFields, $login, $password, Ctrl::$curSpace->_id);
			if(is_object($newUser)){
				Ctrl::notify("CONTACT_createUserConfirmed");
				if($contactRef->isProfileImg())  {copy($contactRef->pathProfileImg(),$newUser->pathProfileImg());}//Récupère l'image?
				$newUser->createCredentialsMail($password);//Mail de notif
			}
			//Redirige
			self::redir($contactRef->getUrl());
		}
	}
}