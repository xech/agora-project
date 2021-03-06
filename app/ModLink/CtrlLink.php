<?php
/**
* This file is part of the Agora-Project Software package.
*
* @copyright (c) Agora-Project Limited <https://www.agora-project.net>
* @license GNU General Public License, version 2 (GPL-2.0)
*/


/*
 * CONTROLEUR DU MODULE "LINK"
 */
class CtrlLink extends Ctrl
{
	const moduleName="link";
	public static $folderObjectType="linkFolder";
	public static $moduleOptions=["adminRootAddContent"];
	public static $MdlObjects=["MdlLink","MdlLinkFolder"];

	/*******************************************************************************************
	 * VUE : PAGE PRINCIPALE
	 *******************************************************************************************/
	public static function actionDefault()
	{
		$vDatas["foldersList"]=self::$curContainer->folders();
		$vDatas["linkList"]=Db::getObjTab("link", "SELECT * FROM ap_link WHERE ".MdlLink::sqlDisplay(self::$curContainer)." ".MdlLink::sqlSort());
		static::displayPage("VueIndex.php",$vDatas);
	}

	/*******************************************************************************************
	 * PLUGINS
	 *******************************************************************************************/
	public static function getModPlugins($params)
	{
		$pluginsList=self::getPluginsFolders($params,"MdlLinkFolder");
		foreach(MdlLink::getPlugins($params) as $tmpObj)
		{
			$tmpObj->pluginModule=self::moduleName;
			$tmpObj->pluginIcon=self::moduleName."/icon.png";
			$tmpObj->pluginLabel=(!empty($tmpObj->description))  ?  $tmpObj->description  :  $tmpObj->adress;
			$tmpObj->pluginTooltip=$tmpObj->containerObj()->folderPath("text");
			$tmpObj->pluginJsIcon="windowParent.redir('".$tmpObj->getUrl()."');";//Affiche le lien dans son dossier conteneur
			$tmpObj->pluginJsLabel="window.open('".addslashes($tmpObj->adress)."');";
			$pluginsList[]=$tmpObj;
		}
		return $pluginsList;
	}

	/*******************************************************************************************
	 * VUE : AJOUT D'UN LIEN
	 *******************************************************************************************/
	public static function actionLinkEdit()
	{
		//Init
		$curObj=Ctrl::getTargetObj();
		$curObj->editControl();
		////	Valide le formulaire
		if(Req::isParam("formValidate")){
			//Enregistre & recharge l'objet
			$curObj=$curObj->createUpdate("adress=".Db::formatParam("adress").", description=".Db::formatParam("description"));
			//Notifie par mail & Ferme la page
			$curObj->sendMailNotif("<a href=\"".$curObj->adress."\" target='_blank'><b>".$curObj->adress."</b></a>");
			static::lightboxClose();
		}
		////	Affiche la vue
		$vDatas["curObj"]=$curObj;
		static::displayPage("VueLinkEdit.php",$vDatas);
	}
}