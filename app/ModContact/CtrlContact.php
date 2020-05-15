<?php
/**
* This file is part of the Agora-Project Software package.
*
* @copyright (c) Agora-Project Limited <https://www.agora-project.net>
* @license GNU General Public License, version 2 (GPL-2.0)
*/


/*
 * Controleur du module "Contact"
 */
class CtrlContact extends Ctrl
{
	const moduleName="contact";
	public static $folderObjectType="contactFolder";
	public static $moduleOptions=["adminRootAddContent"];
	public static $MdlObjects=array("MdlContact","MdlContactFolder");

	/*
	 * ACTION PAR DEFAUT
	 */
	public static function actionDefault()
	{
		$vDatas["foldersList"]=self::$curContainer->folders();
		$vDatas["contactList"]=Db::getObjTab("contact", "SELECT * FROM ap_contact WHERE ".MdlContact::sqlDisplayedObjects(self::$curContainer)." ".MdlContact::sqlSort());
		static::displayPage("VueIndex.php",$vDatas);
	}

	/*
	 * PLUGINS
	 */
	public static function plugin($pluginParams)
	{
		$pluginsList=self::getPluginsFolders($pluginParams,"MdlContactFolder");
		foreach(MdlContact::getPluginObjects($pluginParams) as $tmpObj)
		{
			$tmpObj->pluginModule=self::moduleName;
			$tmpObj->pluginIcon=self::moduleName."/icon.png";
			$tmpObj->pluginLabel=$tmpObj->getLabel("all");
			$tmpObj->pluginTooltip=$tmpObj->containerObj()->folderPath("text");
			$tmpObj->pluginJsIcon="windowParent.redir('".$tmpObj->getUrl("container")."');";//Redir vers le dossier conteneur
			$tmpObj->pluginJsLabel="lightboxOpen('".$tmpObj->getUrl("vue")."');";
			$pluginsList[]=$tmpObj;
		}
		return $pluginsList;
	}

	/*
	 * ACTION : Vue détaillée d'un contact
	 */
	public static function actionVueContact()
	{
		$curObj=Ctrl::getTargetObj();
		$curObj->controlRead();
		$vDatas["curObj"]=$curObj;
		static::displayPage("VueContact.php",$vDatas);
	}

	/*
	 * ACTION : Edition d'un contact
	 */
	public static function actionContactEdit()
	{
		//Init
		$curObj=Ctrl::getTargetObj();
		$curObj->controlEdit();
		////	Valide le formulaire
		if(Req::isParam("formValidate")){
			//Enregistre & recharge l'objet
			$curObj=$curObj->createUpdate("civility=".Db::formatParam("civility").", name=".Db::formatParam("name").", firstName=".Db::formatParam("firstName").", mail=".Db::formatParam("mail").", telephone=".Db::formatParam("telephone").", telmobile=".Db::formatParam("telmobile").", adress=".Db::formatParam("adress").", postalCode=".Db::formatParam("postalCode").", city=".Db::formatParam("city").", country=".Db::formatParam("country").", function=".Db::formatParam("function").", companyOrganization=".Db::formatParam("companyOrganization").", comment=".Db::formatParam("comment"));
			//Ajoute/supprime l'image / Notifie par mail & Ferme la page
			$curObj->editImg();
			$curObj->sendMailNotif($curObj->getLabel());
			static::lightboxClose();
		}
		////	Affiche la vue
		$vDatas["curObj"]=$curObj;
		static::displayPage("VueContactEdit.php",$vDatas);
	}

	/*
	 * ACTION : Import/Export de contacts
	 */
	public static function actionEditPersonsImportExport()
	{
		////	Controle d'accès
		if(Ctrl::$curUser->isAdminSpace()==false)  {static::lightboxClose();}
		////	Validation de formulaire
		if(Req::isParam("formValidate"))
		{
			//Export de contacts
			if(Req::getParam("actionImportExport")=="export"){
				$contactList=Db::getObjTab("contact", "SELECT * FROM ap_contact WHERE ".MdlContact::sqlDisplayedObjects(self::$curContainer));
				MdlContact::exportPersons($contactList, Req::getParam("exportType"));
			}
			//Import de contacts
			elseif(Req::getParam("actionImportExport")=="import" && Req::getParam("personFields"))
			{
				$personFields=Req::getParam("personFields");
				//Créé chaque contact
				foreach(Req::getParam("personsImport") as $personCpt)
				{
					$curObj=new MdlContact();
					$sqlProperties=null;
					//Ajoute chaque champ du contact
					foreach(Req::getParam("agoraFields") as $fieldCpt=>$curFieldName){
						$curFieldVal=(!empty($personFields[$personCpt][$fieldCpt]))  ?  $personFields[$personCpt][$fieldCpt]  :  null;
						if(!empty($curFieldVal) && !empty($curFieldName))	{$sqlProperties.=$curFieldName."=".Db::format($curFieldVal).", ";}
					}
					//Enregistre le contact & Droits d'accès en lecture sur l'espaces courant?
					$curObj=$curObj->createUpdate($sqlProperties);
					if($curObj->isIndependant())	{$curObj->setAffectations([Ctrl::$curSpace->_id."_spaceUsers_1"]);}
				}
				//Ferme la page
				static::lightboxClose();
			}
		}
		////	Affiche le menu d'Import/Export
		$vDatas["curObjClass"]="MdlContact";
		static::displayPage(Req::commonPath."VuePersonsImportExport.php",$vDatas);
	}

	/*
	 * ACTION : Creation d'un utilisateur a partir d'un contact
	 */
	public static function actionContactAddUser()
	{
		if(Ctrl::$curUser->isAdminGeneral())
		{
			//Init
			$contactRef=Ctrl::getTargetObj();
			$contactRef->controlEdit();
			//Création du nouveau User
			$newUser=new MdlUser();
			$login=(!empty($contactRef->mail))  ?  $contactRef->mail  :  substr($contactRef->firstName,0,1).substr($contactRef->name,0,5);
			$password=Txt::uniqId(8);
			$sqlProperties="civility=".Db::format($contactRef->civility).", name=".Db::format($contactRef->name).", firstName=".Db::format($contactRef->firstName).", mail=".Db::format($contactRef->mail).", telephone=".Db::format($contactRef->telephone).", telmobile=".Db::format($contactRef->telmobile).", adress=".Db::format($contactRef->adress).", postalCode=".Db::format($contactRef->postalCode).", city=".Db::format($contactRef->city).", country=".Db::format($contactRef->country).", function=".Db::format($contactRef->function).", companyOrganization=".Db::format($contactRef->companyOrganization).", comment=".Db::format($contactRef->comment);
			$newUser=$newUser->createUpdate($sqlProperties, $login, $password, Ctrl::$curSpace->_id);
			if(is_object($newUser)){
				Ctrl::addNotif("CONTACT_createUserConfirm");
				if(is_file($contactRef->pathImgThumb()))  {copy($contactRef->pathImgThumb(),$newUser->pathImgThumb());}//Récupère l'image?
			}
			//Redirige
			self::redir($contactRef->getUrl("container"));
		}
	}
}