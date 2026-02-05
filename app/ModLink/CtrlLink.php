<?php
/**
* This file is part of the Agora-Project Software package
*
* @copyleft Agora-Project <https://www.agora-project.net>
* @license GNU General Public License (GPL-2.0)
*/


/*
 * CONTROLEUR DU MODULE "LINK"
 */
class CtrlLink extends Ctrl
{
	const moduleName="link";
	public static $folderObjType="linkFolder";
	public static $moduleOptions=["adminRootAddContent"];
	public static $MdlObjects=["MdlLink","MdlLinkFolder"];

	/********************************************************************************************************
	 * VUE : PAGE PRINCIPALE
	 ********************************************************************************************************/
	public static function actionDefault()
	{
		$vDatas["linkList"]=Db::getObjTab("link", "SELECT * FROM ap_link WHERE ".MdlLink::sqlDisplay(self::$curContainer).MdlLink::sqlSort());
		static::displayPage("VueIndex.php",$vDatas);
	}

	/********************************************************************************************************
	 * PLUGINS DU MODULE
	 ********************************************************************************************************/
	public static function getPlugins($params)
	{
		$pluginsList=MdlLinkFolder::getPluginFolders($params);
		foreach(MdlLink::getPluginObjects($params) as $tmpObj){
			$tmpObj->pluginIcon=self::moduleName."/icon.png";
			$tmpObj->pluginLabel=(!empty($tmpObj->description))  ?  $tmpObj->description  :  $tmpObj->adress;
			$tmpObj->pluginTooltip=$tmpObj->containerObj()->folderPath("text");
			$tmpObj->pluginJsIcon="window.top.redir('".$tmpObj->getUrl()."')";//Affiche dans son dossier
			$tmpObj->pluginJsLabel="window.open('".addslashes($tmpObj->adress)."')";
			$pluginsList[]=$tmpObj;
		}
		return $pluginsList;
	}

	/********************************************************************************************************
	 * VUE : AJOUT D'UN LIEN
	 ********************************************************************************************************/
	public static function actionVueEditLink()
	{
		//Init
		$curObj=Ctrl::getCurObj();
		$curObj->editControl();
		////	Valide le formulaire
		if(Req::isParam("formValidate")){
			//Enregistre & recharge l'objet
			$adress=filter_var(Req::param("adress"), FILTER_SANITIZE_URL);
			if(!stristr($adress,"http"))  {$adress="http://".$adress;}
			$curObj=$curObj->editRecord("adress=".Db::format($adress).", description=".Db::param("description"));
			//Notifie par mail & Ferme la page
			$curObj->sendMailNotif('<a href="'.$curObj->adress.'" target="_blank"><b>'.$curObj->adress.'</b></a>');
			static::lightboxRedir();
		}
		////	Affiche la vue
		$vDatas["curObj"]=$curObj;
		static::displayPage("VueEditLink.php",$vDatas);
	}
}