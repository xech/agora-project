<?php
/**
* This file is part of the Agora-Project Software package
*
* @copyleft Agora-Project <https://www.agora-project.net>
* @license GNU General Public License (GPL-2.0)
*/


/*
 * CONTROLEUR DU MODULE "FORUM"
 */
class CtrlForum extends Ctrl
{
	const moduleName="forum";
	public static $moduleOptions=["adminAddSubject","adminAddTheme"];
	public static $MdlObjects=["MdlForumSubject","MdlForumMessage"];

	/*******************************************************************************************
	 * VUE : PAGE PRINCIPALE
	 *******************************************************************************************/
	public static function actionDefault()
	{
		////	MESSAGES D'UN SUJET
		$curSubject=Ctrl::getObjTarget();
		if(is_object($curSubject) && $curSubject::objectType=="forumSubject"){
			$curSubject->usersConsultUpdate();
			$vDatas["curSubject"]=$curSubject;
			$vDatas["subjectList"]=[$curSubject];
			$vDatas["forumDisplay"]="suject";
		}
		////	LISTE DES SUJETS
		else{
			$sqlSubjects="SELECT * FROM ".MdlForumSubject::dbTable." WHERE ".MdlForumSubject::sqlDisplay().MdlForumTheme::sqlCategoryFilter().MdlForumSubject::sqlSort();
			$vDatas["subjectsTotalNb"]=count(Db::getTab($sqlSubjects));
			$vDatas["subjectList"]=Db::getObjTab("forumSubject", $sqlSubjects." ".MdlForumSubject::sqlPagination());
			$vDatas["forumDisplay"]="subjectList";
		}
		////	AFFICHAGE
		static::displayPage("VueIndex.php",$vDatas);
	}

	/*******************************************************************************************
	 * PLUGINS DU MODULE
	 *******************************************************************************************/
	public static function getPlugins($params)
	{
		$pluginsList=[];
		//Sujets
		foreach(MdlForumSubject::getPluginObjects($params) as $objSubject){
			$objSubject->pluginIcon=self::moduleName."/icon.png";
			$objSubject->pluginLabel=(!empty($objSubject->title))  ?  $objSubject->title  :  Txt::reduce($objSubject->description);
			$objSubject->pluginTooltip=$objSubject->pluginLabel;
			$objSubject->pluginJsIcon="window.top.redir('".$objSubject->getUrl()."')";//Redir vers le sujet
			$objSubject->pluginJsLabel=$objSubject->pluginJsIcon;
			$pluginsList[]=$objSubject;
		}
		//Messages
		if($params["type"]!="shortcut"){
			foreach(MdlForumMessage::getPluginObjects($params) as $objMessage){
				$objMessage->pluginIcon=self::moduleName."/icon.png";
				$objMessage->pluginLabel=(!empty($objMessage->title))  ?  $objMessage->title  :  Txt::reduce($objMessage->description);
				$objMessage->pluginTooltip=$objMessage->pluginLabel;
				$objMessage->pluginJsIcon="window.top.redir('".$objMessage->getUrl()."')";//Redir vers le message et son sujet
				$objMessage->pluginJsLabel=$objMessage->pluginJsIcon;
				$pluginsList[]=$objMessage;
			}
		}
		return $pluginsList;
	}

	/*******************************************************************************************
	 * AJAX : ACTIVE/DÉSACTIVE LES NOTIFICATIONS DES MESSAGES PAR MAIL
	 *******************************************************************************************/
	public static function actionNotifyLastMessage()
	{
		$curSubject=Ctrl::getObjTarget();
		if($curSubject->readRight()){
			$usersNotifyLastMessage=Txt::txt2tab($curSubject->usersNotifyLastMessage);
			if($curSubject->curUserNotifyLastMessage())		{$usersNotifyLastMessage=array_diff($usersNotifyLastMessage,[Ctrl::$curUser->_id]);		echo "removeUser";}
			else											{$usersNotifyLastMessage[]=Ctrl::$curUser->_id;											echo "addUser";}
			Db::query("UPDATE ap_forumSubject SET usersNotifyLastMessage=".Db::formatTab2txt($usersNotifyLastMessage)." WHERE _id=".$curSubject->_id);
		}
	}

	/*******************************************************************************************
	 * VUE : EDITION D'UN SUJET
	 *******************************************************************************************/
	public static function actionForumSubjectEdit()
	{
		//Init
		$curObj=Ctrl::getObjTarget();
		if($curObj->isNew() && MdlForumSubject::addRight()==false)	{self::noAccessExit();}
		else														{$curObj->editControl();}
		////	Valide le formulaire
		if(Req::isParam("formValidate"))
		{
			//Enregistre & recharge l'objet
			$dateLastMessage=($curObj->isNew())  ?  ", dateLastMessage=".Db::dateNow()  :  null;//Init "dateLastMessage" pour un nouveau sujet (classement des sujets)
			$curObj=$curObj->createUpdate("title=".Db::param("title").", description=".Db::param("description").", _idTheme=".Db::param("_idTheme").", usersConsultLastMessage=".Db::formatTab2txt([Ctrl::$curUser->_id])." ".$dateLastMessage);
			//Notifie par mail & Ferme la page
			$curObj->sendMailNotif();
			static::lightboxRedir();
		}
		////	Affiche la vue
		$vDatas["curObj"]=$curObj;
		if(Req::isParam("_idTheme"))  {$curObj->_idTheme=Req::param("_idTheme");}
		$vDatas["themeList"]=MdlForumTheme::catList();
		static::displayPage("VueForumSubjectEdit.php",$vDatas);
	}

	/*******************************************************************************************
	 * VUE : EDITION D'UN MESSAGE
	 *******************************************************************************************/
	public static function actionForumMessageEdit()
	{
		//Init
		$curObj=Ctrl::getObjTarget();
		$curObj->editControl();
		////	Valide le formulaire
		if(Req::isParam("formValidate")){
			//Enregistre & recharge l'objet
			$idMessageParent=Req::isParam("_idMessageParent")  ?  ", _idMessageParent=".Db::param("_idMessageParent")  :  null;//Rattaché à un message parent?
			$curObj=$curObj->createUpdate("title=".Db::param("title").", description=".Db::param("description").$idMessageParent);
			//MAJ "dateLastMessage" & "usersConsultLastMessage" du sujet conteneur
			Db::query("UPDATE ap_forumSubject SET dateLastMessage=".Db::dateNow().", usersConsultLastMessage=".Db::formatTab2txt([Ctrl::$curUser->_id])." WHERE _id=".$curObj->_idContainer);
			//Notif "auto" si c'est un nouveau message (cf. "Me notifier par mail")
			if($curObj->isNewRecord()==false)	{$notifUserIds=null;}
			else{
				$notifUserIds=array_diff(Txt::txt2tab($curObj->containerObj()->usersNotifyLastMessage), [Ctrl::$curUser->_id]);	//Users qui on demandé une notif (enlève l'auteur courant)
				$notifUserIds=array_intersect($notifUserIds, $curObj->containerObj()->affectedUserIds());						//Enlève les users qui ne sont plus affectés au sujet
			}
			//Notifie par mail aux users spécifiés & Ferme la page
			$curObj->sendMailNotif(null, null, $notifUserIds);
			static::lightboxRedir();
		}
		////	Affiche la vue
		$vDatas["curObj"]=$curObj;
		$vDatas["messageParent"]=Req::isParam("_idMessageParent")  ?  self::getObj("forumMessage",Req::param("_idMessageParent"))  :  null;
		static::displayPage("VueForumMessageEdit.php",$vDatas);
	}
}