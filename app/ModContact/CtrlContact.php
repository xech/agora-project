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

	/*******************************************************************************************
	 * VUE : PAGE PRINCIPALE
	 *******************************************************************************************/
	public static function actionDefault()
	{
		$vDatas["contactList"]=Db::getObjTab("contact", "SELECT * FROM ap_contact WHERE ".MdlContact::sqlDisplay(self::$curContainer).MdlContact::sqlSort());
		static::displayPage("VueIndex.php",$vDatas);
	}

	/*******************************************************************************************
	 * PLUGINS DU MODULE
	 *******************************************************************************************/
	public static function getPlugins($params)
	{
		$pluginsList=MdlContactFolder::getPluginFolders($params);
		foreach(MdlContact::getPluginObjects($params) as $tmpObj){
			$tmpObj->pluginIcon=self::moduleName."/icon.png";
			$tmpObj->pluginLabel=$tmpObj->getLabel("full");
			$tmpObj->pluginTooltip=$tmpObj->containerObj()->folderPath("text");
			$tmpObj->pluginJsIcon="window.top.redir('".$tmpObj->getUrl()."')";//Affiche dans son dossier
			$tmpObj->pluginJsLabel=$tmpObj->openVue();
			$pluginsList[]=$tmpObj;
		}
		return $pluginsList;
	}

	/*******************************************************************************************
	 * VUE : DÉTAILS D'UN CONTACT
	 *******************************************************************************************/
	public static function actionVueContact()
	{
		$curObj=Ctrl::getObjTarget();
		$curObj->readControl();
		$vDatas["curObj"]=$curObj;
		static::displayPage("VueContact.php",$vDatas);
	}

	/*******************************************************************************************
	 * VUE : EDITION D'UN CONTACT
	 *******************************************************************************************/
	public static function actionContactEdit()
	{
		//Init
		$curObj=Ctrl::getObjTarget();
		$curObj->editControl();
		////	Valide le formulaire
		if(Req::isParam("formValidate")){
			//Enregistre & recharge l'objet
			$curObj=$curObj->createUpdate("civility=".Db::param("civility").", name=".Db::param("name").", firstName=".Db::param("firstName").", mail=".Db::param("mail").", telephone=".Db::param("telephone").", telmobile=".Db::param("telmobile").", adress=".Db::param("adress").", postalCode=".Db::param("postalCode").", city=".Db::param("city").", country=".Db::param("country").", `function`=".Db::param("function").", companyOrganization=".Db::param("companyOrganization").", `comment`=".Db::param("comment"));
			//Ajoute/supprime l'image / Notifie par mail & Ferme la page
			$curObj->profileImgRecord();
			$curObj->sendMailNotif($curObj->getLabel());
			static::lightboxRedir();
		}
		////	Affiche la vue
		$vDatas["curObj"]=$curObj;
		static::displayPage("VueContactEdit.php",$vDatas);
	}

	/*******************************************************************************************
	 * VUE : IMPORT/EXPORT DE CONTACTS
	 *******************************************************************************************/
	public static function actionEditPersonsImportExport()
	{
		////	Folder courant  &&  Controle d'accès
		$curFolder=self::getObj("contactFolder",Req::param("_idContainer"));
		if(Ctrl::$curUser->isSpaceAdmin()==false)  {static::lightboxRedir();}
		////	Valide le formulaire
		if(Req::isParam("formValidate"))
		{
			//// Export de contacts
			if(Req::param("actionImportExport")=="export"){
				$contactList=Db::getObjTab("contact", "SELECT * FROM ".MdlContact::dbTable." WHERE ".MdlContact::sqlDisplay(self::$curContainer));
				MdlContact::exportPersons($contactList, Req::param("exportType"));
			}
			//// Import de contacts
			elseif(Req::param("actionImportExport")=="import" && Req::isParam("personFields"))
			{
				$personFields=Req::param("personFields");
				foreach(Req::param("personsImport") as $personCpt)
				{
					//Créé le contact  &&  Spécifie le "_idContainer" pour le controle d'accès (cf. "createUpdate()"+"createRight()")
					$curObj=new MdlContact();
					$curObj->_idContainer=$curFolder->_id;
					$sqlFields=null;
					//Récupère la valeur de chaque champ du contact
					foreach(Req::param("agoraFields") as $fieldCpt=>$curFieldName){
						$curFieldVal=(!empty($personFields[$personCpt][$fieldCpt]))  ?  $personFields[$personCpt][$fieldCpt]  :  null;
						if(!empty($curFieldVal) && !empty($curFieldName))  {$sqlFields.="`".$curFieldName."`=".Db::format($curFieldVal).", ";}
					}
					//Enregistre le nouveau contact !
					$curObj=$curObj->createUpdate($sqlFields);
					//Nouveau contact du dossier racine : affecte en lecture à "tous les users" de l'espace courant
					if($curFolder->isRootFolder())  {Db::query("INSERT INTO ap_objectTarget SET objectType=".Db::format($curObj::objectType).", _idObject=".(int)$curObj->_id.", _idSpace=".(int)self::$curSpace->_id.", target='spaceUsers', accessRight='1'");}
				}
				//Ferme la page
				static::lightboxRedir();
			}
		}
		////	Affiche le menu d'Import/Export
		$vDatas["curObjClass"]="MdlContact";
		$vDatas["curFolder"]=$curFolder;
		static::displayPage(Req::commonPath."VuePersonsImportExport.php",$vDatas);
	}

	/*******************************************************************************************
	 * ACTION : CREATION D'UN UTILISATEUR A PARTIR D'UN CONTACT
	 *******************************************************************************************/
	public static function actionContactAddUser()
	{
		if(Ctrl::$curUser->isGeneralAdmin())
		{
			//Init
			$contactRef=Ctrl::getObjTarget();
			$contactRef->editControl();
			//Création du nouveau User
			$newUser=new MdlUser();
			$login=(!empty($contactRef->mail))  ?  $contactRef->mail  :  substr($contactRef->firstName,0,1).substr($contactRef->name,0,5);
			$password=Txt::defaultPassword();
			$sqlFields="civility=".Db::format($contactRef->civility).", name=".Db::format($contactRef->name).", firstName=".Db::format($contactRef->firstName).", mail=".Db::format($contactRef->mail).", telephone=".Db::format($contactRef->telephone).", telmobile=".Db::format($contactRef->telmobile).", adress=".Db::format($contactRef->adress).", postalCode=".Db::format($contactRef->postalCode).", city=".Db::format($contactRef->city).", country=".Db::format($contactRef->country).", `function`=".Db::format($contactRef->function).", companyOrganization=".Db::format($contactRef->companyOrganization).", `comment`=".Db::format($contactRef->comment);
			$newUser=$newUser->createUpdate($sqlFields, $login, $password, Ctrl::$curSpace->_id);
			if(is_object($newUser)){
				Ctrl::notify("CONTACT_createUserConfirmed");
				if(is_file($contactRef->pathImgThumb()))  {copy($contactRef->pathImgThumb(),$newUser->pathImgThumb());}//Récupère l'image?
				$newUser->createCredentialsMail($password);//Mail de notif
			}
			//Redirige
			self::redir($contactRef->getUrl());
		}
	}
}