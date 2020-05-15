<?php
/**
* This file is part of the Agora-Project Software package.
*
* @copyright (c) Agora-Project Limited <https://www.agora-project.net>
* @license GNU General Public License, version 2 (GPL-2.0)
*/


/*
 * Controleur du module "Link"
 */
class CtrlLink extends Ctrl
{
	const moduleName="link";
	public static $folderObjectType="linkFolder";
	public static $moduleOptions=["adminRootAddContent"];
	public static $MdlObjects=array("MdlLink","MdlLinkFolder");

	/*
	 * ACTION PAR DEFAUT
	 */
	public static function actionDefault()
	{
		$vDatas["foldersList"]=self::$curContainer->folders();
		$vDatas["linkList"]=Db::getObjTab("link", "SELECT * FROM ap_link WHERE ".MdlLink::sqlDisplayedObjects(self::$curContainer)." ".MdlLink::sqlSort());
		static::displayPage("VueIndex.php",$vDatas);
	}

	/*
	 * PLUGINS
	 */
	public static function plugin($pluginParams)
	{
		$pluginsList=self::getPluginsFolders($pluginParams,"MdlLinkFolder");
		foreach(MdlLink::getPluginObjects($pluginParams) as $tmpObj)
		{
			$tmpObj->pluginModule=self::moduleName;
			$tmpObj->pluginIcon=self::moduleName."/icon.png";
			$tmpObj->pluginLabel=(!empty($tmpObj->description))  ?  $tmpObj->description  :  $tmpObj->adress;
			$tmpObj->pluginTooltip=$tmpObj->containerObj()->folderPath("text");
			$tmpObj->pluginJsIcon="windowParent.redir('".$tmpObj->getUrl("container")."');";//Redir vers le dossier conteneur
			$tmpObj->pluginJsLabel="window.open('".addslashes($tmpObj->adress)."');";
			$pluginsList[]=$tmpObj;
		}
		return $pluginsList;
	}

	/*
	 * ACTION : Ajout d'un lien
	 */
	public static function actionLinkEdit()
	{
		//Init
		$curObj=Ctrl::getTargetObj();
		$curObj->controlEdit();
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