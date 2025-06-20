<?php
/**
* This file is part of the Agora-Project Software package
*
* @copyleft Agora-Project <https://www.agora-project.net>
* @license GNU General Public License (GPL-2.0)
*/


/*
 * CONTROLEUR DU MODULE "SPACE"
 */
class CtrlSpace extends Ctrl
{
	const moduleName="space";

	/********************************************************************************************************
	 * VUE : PAGE PRINCIPALE
	 ********************************************************************************************************/
	public static function actionDefault()
	{
		//	Controle d'accès && Affiche la vue
		if(Ctrl::$curUser->isGeneralAdmin()==false)  {self::noAccessExit();}
		$vDatas["spaceList"]=Db::getObjTab("space", "SELECT * FROM ap_space ".MdlSpace::sqlSort());
		static::displayPage("VueIndex.php",$vDatas);
	}

	/********************************************************************************************************
	 * VUE : PARAMETRAGE D'UN ESPACE
	 ********************************************************************************************************/
	public static function actionSpaceEdit()
	{
		//Init
		$curObj=Ctrl::getCurObj();
		$curObj->editControl();
		////	Valide le formulaire
		if(Req::isParam("formValidate"))
		{
			////	Enregistre & recharge l'objet
			$oldSpaceName=($curObj->isNewRecord()==false)  ?  $curObj->name  :  null;
			$curObj=$curObj->editRecord("name=".Db::param("name").", description=".Db::param("description").", public=".Db::param("public").", `password`=".Db::param("password").", userInscription=".Db::param("userInscription").", userInscriptionNotify=".Db::param("userInscriptionNotify").", usersInvitation=".Db::param("usersInvitation").", wallpaper=".Db::param("wallpaper"));
			////	Affectations des users
			if(Ctrl::$curUser->isSpaceAdmin())
			{
				//Réinit les droits
				Db::query("DELETE FROM ap_joinSpaceUser WHERE _idSpace=".$curObj->_id);
				//Affectation "allUsers"
				if(Req::isParam("allUsers"))  {Db::query("INSERT INTO ap_joinSpaceUser SET _idSpace=".$curObj->_id.", allUsers=1, accessRight=1");}
				//Enregistre les affectations de chaque user
				if(Req::isParam("spaceAffect")){
					foreach(Req::param("spaceAffect") as $curAffect){
						$curAffect=explode("_",$curAffect);//"5_2" (user 5 et droit 2 d'admin) => "[5,2]"
						Db::query("INSERT INTO ap_joinSpaceUser SET _idSpace=".$curObj->_id.", _idUser=".$curAffect[0].", accessRight=".$curAffect[1]);
					}
				}
			}
			////	Affectations des modules
			Db::query("DELETE FROM ap_joinSpaceModule WHERE _idSpace=".$curObj->_id);
			foreach(Req::param("moduleList") as $rank=>$moduleName){
				$options=Txt::tab2txt(Req::param($moduleName."Options"));
				Db::query("INSERT INTO ap_joinSpaceModule SET _idSpace=".$curObj->_id.", moduleName=".Db::format($moduleName).", `rank`=".$rank.", options=".Db::format($options));
			}
			////	Nouvel espace : Creation de l'agenda partagé de l'espace (affectation par défaut : lecture pour les users de l'espace)
			if($curObj->isNewRecord() && in_array("calendar",Req::param("moduleList")) && Req::isParam("calendarOptions") && in_array("createSpaceCalendar",Req::param("calendarOptions"))){
				$newCalendar=new MdlCalendar();
				$newCalendar=$newCalendar->editRecord("title=".Db::format($curObj->name).", description=".Db::format(Txt::trad("CALENDAR_sharedCalendarDescription")).", type='ressource'");
				Db::query("INSERT INTO ap_objectTarget SET objectType='calendar', _idObject=".$newCalendar->_id.", _idSpace=".$curObj->_id.", target='spaceUsers', accessRight='1.5'");
			}
			////	Modif d'espace : synchronise le nom de l'agenda partagé et ajoute au besoin "agenda partagé de l'espace"
			elseif(!empty($oldSpaceName) && in_array("calendar",Req::param("moduleList"))){
				Db::query("UPDATE ap_calendar SET title=".Db::format($curObj->name).", description=".Db::format(Txt::trad("CALENDAR_sharedCalendarDescription"))." WHERE title=".Db::format($oldSpaceName));
			}
			//Ferme la page
			static::lightboxRedir();
		}
		////	Liste de tous les users du site  &&  Liste de tous les modules disponibles
		$vDatas["userList"]=Db::getObjTab("user","SELECT * FROM ap_user ORDER BY ".Ctrl::$agora->personsSort);
		$vDatas["moduleList"]=$curObj->moduleList(true);
		//Ajoute les modules désactivés
		foreach(MdlSpace::availableModules() as $moduleName=>$tmpModule){
			if(empty($vDatas["moduleList"][$moduleName])){
				$tmpModule["disabled"]=true;
				$vDatas["moduleList"][$moduleName]=$tmpModule;
			}
		}
		////	Affiche la vue
		$vDatas["curObj"]=$curObj;
		static::displayPage("VueSpaceEdit.php",$vDatas);
	}
}