<?php
/**
* This file is part of the Agora-Project Software package.
*
* @copyright (c) Agora-Project Limited <https://www.agora-project.net>
* @license GNU General Public License, version 2 (GPL-2.0)
*/


/*
 * CONTROLEUR DU MODULE "SPACE"
 */
class CtrlSpace extends Ctrl
{
	const moduleName="space";

	/*******************************************************************************************
	 * VUE : PAGE PRINCIPALE
	 *******************************************************************************************/
	public static function actionDefault()
	{
		//	Controle d'accès && Affiche la vue
		if(Ctrl::$curUser->isAdminGeneral()==false)  {self::noAccessExit();}
		$vDatas["spaceList"]=Db::getObjTab("space", "SELECT * FROM ap_space ".MdlSpace::sqlSort());
		static::displayPage("VueIndex.php",$vDatas);
	}

	/*******************************************************************************************
	 * VUE : PARAMETRAGE D'UN ESPACE
	 *******************************************************************************************/
	public static function actionSpaceEdit()
	{
		//Init
		$curSpace=Ctrl::getObjTarget();
		$curSpace->editControl();
		////	Valide le formulaire
		if(Req::isParam("formValidate"))
		{
			////	Enregistre & recharge l'objet
			$curSpace=$curSpace->createUpdate("name=".Db::param("name").", description=".Db::param("description").", public=".Db::param("public").", `password`=".Db::param("password").", userInscription=".Db::param("userInscription").", userInscriptionNotify=".Db::param("userInscriptionNotify").", usersInvitation=".Db::param("usersInvitation").", wallpaper=".Db::param("wallpaper"));
			////	Affectations des users
			if(Ctrl::$curUser->isAdminSpace())
			{
				//Réinit les droits
				Db::query("DELETE FROM ap_joinSpaceUser WHERE _idSpace=".$curSpace->_id);
				//Affectation "allUsers"
				if(Req::isParam("allUsers"))  {Db::query("INSERT INTO ap_joinSpaceUser SET _idSpace=".$curSpace->_id.", allUsers=1, accessRight=1");}
				//Enregistre les affectations de chaque user
				if(Req::isParam("spaceAffect")){
					foreach(Req::param("spaceAffect") as $curAffect){
						$curAffect=explode("_",$curAffect);//"5_2" (user 5 et droit 2 d'admin) => "[5,2]"
						Db::query("INSERT INTO ap_joinSpaceUser SET _idSpace=".$curSpace->_id.", _idUser=".$curAffect[0].", accessRight=".$curAffect[1]);
					}
				}
			}
			////	Affectations des modules
			Db::query("DELETE FROM ap_joinSpaceModule WHERE _idSpace=".$curSpace->_id);
			foreach(Req::param("moduleList") as $rank=>$moduleName){
				$options=Txt::tab2txt(Req::param($moduleName."Options"));
				Db::query("INSERT INTO ap_joinSpaceModule SET _idSpace=".$curSpace->_id.", moduleName=".Db::format($moduleName).", `rank`=".$rank.", options=".Db::format($options));
			}
			////	Creation de l'agenda d'espace (avec affectation par défaut : lecture pour les users de l'espace)
			if($curSpace->isNewlyCreated() && in_array("calendar",Req::param("moduleList")) && Req::isParam("calendarOptions") && in_array("createSpaceCalendar",Req::param("calendarOptions"))){
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