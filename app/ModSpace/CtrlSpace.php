<?php
/**
* This file is part of the Agora-Project Software package.
*
* @copyright (c) Agora-Project Limited <https://www.agora-project.net>
* @license GNU General Public License, version 2 (GPL-2.0)
*/


/*
 * Controleur du module "Space" (gestion des espaces!)
 */
class CtrlSpace extends Ctrl
{
	const moduleName="space";

	/*
	 * ACTION PAR DEFAUT
	 */
	public static function actionDefault()
	{
		if(Ctrl::$curUser->isAdminGeneral()==false)  {self::noAccessExit();}
		$vDatas["spaceList"]=Db::getObjTab("space", "SELECT * FROM ap_space ".MdlSpace::sqlSort());
		static::displayPage("VueIndex.php",$vDatas);
	}

	/*
	 * ACTION : parametrage de l'espace
	 */
	public static function actionSpaceEdit()
	{
		//Init
		$curSpace=Ctrl::getTargetObj();
		$curSpace->controlEdit();
		////	Valide le formulaire
		if(Req::isParam("formValidate"))
		{
			////	Enregistre & recharge l'objet
			$curSpace=$curSpace->createUpdate("name=".Db::formatParam("name").", description=".Db::formatParam("description").", public=".Db::formatParam("public").", password=".Db::formatParam("password").", usersInscription=".Db::formatParam("usersInscription").", usersInvitation=".Db::formatParam("usersInvitation").", wallpaper=".Db::formatParam("wallpaper"));
			////	Affectations des users
			if(Ctrl::$curUser->isAdminGeneral())
			{
				//Réinit les droits
				Db::query("DELETE FROM ap_joinSpaceUser WHERE _idSpace=".$curSpace->_id);
				//Affectation "allUsers"
				if(Req::isParam("allUsers"))	{Db::query("INSERT INTO ap_joinSpaceUser SET _idSpace=".$curSpace->_id.", allUsers=1, accessRight=1");}
				//Enregistre les affectations
				if(Req::isParam("spaceAffect")){
					foreach(Req::getParam("spaceAffect") as $curAffect){
						$curAffect=explode("_",$curAffect);//user 5 + droit 2 : "5_2" => "[5,2]"
						Db::query("INSERT INTO ap_joinSpaceUser SET _idSpace=".$curSpace->_id.", _idUser=".$curAffect[0].", accessRight=".$curAffect[1]);
					}
				}
			}
			////	Affectations des modules
			Db::query("DELETE FROM ap_joinSpaceModule WHERE _idSpace=".$curSpace->_id);
			foreach(Req::getParam("moduleList") as $rank=>$moduleName){
				$options=Txt::tab2txt(Req::getParam($moduleName."Options"));
				Db::query("INSERT INTO ap_joinSpaceModule SET _idSpace=".$curSpace->_id.", moduleName=".Db::format($moduleName).", rank=".$rank.", options=".Db::format($options));
			}
			////	Creation de l'agenda d'espace (avec affectation par défaut : lecture pour les users de l'espace)
			if($curSpace->isNewlyCreated() && in_array("calendar",Req::getParam("moduleList")) && in_array("createSpaceCalendar",Req::getParam("calendarOptions"))){
				$newCalendar=new MdlCalendar(0);
				$newCalendar=$newCalendar->createUpdate("title=".Db::format($curSpace->name).", type='ressource'");
				Db::query("INSERT INTO ap_objectTarget SET objectType='calendar', _idObject=".$newCalendar->_id.", _idSpace=".$curSpace->_id.", target='spaceUsers', accessRight='1.5'");
			}
			//Ferme la page
			static::lightboxClose();
		}
		////	Liste de tous les users du site  &&  Liste de tous les modules disponibles
		$vDatas["userList"]=Db::getObjTab("user","SELECT * FROM ap_user ORDER BY ".Ctrl::$agora->personsSort);
		$vDatas["moduleList"]=$curSpace->moduleList(false);
		//Ajoute les modules désactivés
		foreach(MdlSpace::availableModuleList() as $moduleName=>$tmpModule){
			if(empty($vDatas["moduleList"][$moduleName])){
				$tmpModule["disabled"]=true;
				$vDatas["moduleList"][$moduleName]=$tmpModule;
			}
		}
		////	Affiche la vue
		$vDatas["curSpace"]=$curSpace;
		static::displayPage("VueSpaceEdit.php",$vDatas);
	}
}