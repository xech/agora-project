<?php
/**
* This file is part of the Agora-Project Software package.
*
* @copyright (c) Agora-Project Limited <https://www.agora-project.net>
* @license GNU General Public License, version 2 (GPL-2.0)
*/


/*
 * Controleur du module "Forum"
 */
class CtrlForum extends Ctrl
{
	const moduleName="forum";
	public static $moduleOptions=["adminAddSubject","allUsersAddTheme"];
	public static $MdlObjects=array("MdlForumSubject","MdlForumMessage");

	/*
	 * ACTION PAR DEFAUT
	 */
	public static function actionDefault()
	{
		//Init
		$vDatas["themeList"]=MdlForumTheme::getThemes();
		$vDatas["editThemeMenu"]=false;
		////	AFFICHE D'UN SUJET ET SES MESSAGES
		$curSubject=Ctrl::getTargetObj();
		if(is_object($curSubject) && $curSubject::objectType=="forumSubject")
		{
			$vDatas["displayForum"]="messages";
			$curSubject->curUserConsultLastMessageMaj();//Met à jour si besoin la consultation du dernier message
			$vDatas["curSubject"]=$curSubject;
			$vDatas["subjectMessages"]=$curSubject->getMessages();
		}
		////	AFFICHE LES THÈMES DE SUJET
		elseif(!empty($vDatas["themeList"]) && Req::isParam("_idTheme")==false)
		{
			//Init
			$vDatas["displayForum"]="themes";
			if(MdlForumTheme::addRight())  {$vDatas["editThemeMenu"]=true;}
			$vDatas["themeList"][]=new MdlForumTheme(["undefinedTheme"=>true]);//Pseudo theme "sans theme"
			//Liste des themes
			foreach($vDatas["themeList"] as $tmpKey=>$tmpTheme)
			{
				//Nombre de sujets & Objet du dernier sujet
				$sqlThemeFilter=(!empty($tmpTheme->_id)) ? "_idTheme=".$tmpTheme->_id : "_idTheme is NULL";//Theme normal / "sans theme"
				$tmpTheme->subjectList=Db::getObjTab("forumSubject", "SELECT * FROM ap_forumSubject WHERE ".MdlForumSubject::sqlDisplayedObjects()." AND ".$sqlThemeFilter." ORDER BY dateCrea desc");
				$tmpTheme->subjectsNb=count($tmpTheme->subjectList);
				if($tmpTheme->undefinedTheme==true && empty($tmpTheme->subjectsNb))	{unset($vDatas["themeList"][$tmpKey]);}//Enleve le theme "sans theme" s'il n'y a aucun sujet correspondant..
				elseif($tmpTheme->subjectsNb>0)										{$tmpTheme->subjectLast=reset($tmpTheme->subjectList);}//reset: premier sujet de la liste (le + récent)
				//Nombre de messages & Date du dernier message : tous sujets confondus!
				foreach($tmpTheme->subjectList as $tmpSubject)
				{
					$tmpSubject->getMessages(true);
					if($tmpSubject->messagesNb>0){
						$tmpTheme->messagesNb+=$tmpSubject->messagesNb;
						if(empty($tmpTheme->timeLastPost) || $tmpSubject->timeLastPost>$tmpTheme->timeLastPost)  {$tmpTheme->messageLast=$tmpSubject->messageLast;  $tmpTheme->timeLastPost=$tmpSubject->timeLastPost;}
					}
				}
			}
		}
		////	AFFICHE LES SUJETS (D'UN THEME SPECIFIQUE?)
		else
		{
			//Init
			$vDatas["displayForum"]="subjects";
			if(MdlForumTheme::addRight() && empty($vDatas["themeList"]))  {$vDatas["editThemeMenu"]=true;}
			//Liste les sujets
			if(Req::getParam("_idTheme")=="undefinedTheme")	{$sqlThemeFilter="AND (_idTheme is NULL or _idTheme=0)";}		//sujets "sans theme"
			elseif(Req::isParam("_idTheme"))				{$sqlThemeFilter="AND _idTheme=".Db::formatParam("_idTheme");}	//sujets d'un theme précis
			else											{$sqlThemeFilter=null;}											//tout les sujets
			$sqlDisplayedSubjects="SELECT * FROM ".MdlForumSubject::dbTable." WHERE ".MdlForumSubject::sqlDisplayedObjects()." ".$sqlThemeFilter." ".MdlForumSubject::sqlSort();
			$vDatas["subjectsDisplayed"]=Db::getObjTab("forumSubject", $sqlDisplayedSubjects." ".MdlForumSubject::sqlPagination());
			$vDatas["subjectsTotalNb"]=count(Db::getTab($sqlDisplayedSubjects));
			//Pour chaque sujet : Nombre de messages & Dernier message
			foreach($vDatas["subjectsDisplayed"] as $tmpSubject)  {$tmpSubject->getMessages(true);}
		}
		////	THEME COURANT POUR LE MENU PATH
		if($vDatas["displayForum"]!="themes" && !empty($vDatas["themeList"])){
			if(Req::getParam("_idTheme")=="undefinedTheme" || (is_object($curSubject) && empty($curSubject->_idTheme)))	{$vDatas["curTheme"]=new MdlForumTheme(["undefinedTheme"=>true]);}
			elseif(is_object($curSubject) && !empty($curSubject->_idTheme))												{$vDatas["curTheme"]=self::getObj("forumTheme",$curSubject->_idTheme);}
			elseif(Req::getParam("_idTheme"))																			{$vDatas["curTheme"]=self::getObj("forumTheme",Req::getParam("_idTheme"));}
		}
		////	AFFICHAGE
		static::displayPage("VueIndex.php",$vDatas);
	}

	/*
	 * PLUGINS
	 */
	public static function plugin($pluginParams)
	{
		$pluginsList=array();
		//Sujets
		foreach(MdlForumSubject::getPluginObjects($pluginParams) as $objSubject)
		{
			$objSubject->pluginModule=self::moduleName;
			$objSubject->pluginIcon=self::moduleName."/icon.png";
			$objSubject->pluginLabel=(!empty($objSubject->title))  ?  $objSubject->title  :  $objSubject->description;
			$objSubject->pluginTooltip=$objSubject->pluginLabel;
			$objSubject->pluginJsIcon="windowParent.redir('".$objSubject->getUrl()."');";//Redir vers le sujet
			$objSubject->pluginJsLabel=$objSubject->pluginJsIcon;
			$pluginsList[]=$objSubject;
		}
		//messages
		if($pluginParams["type"]!="shortcut")
		{
			foreach(MdlForumMessage::getPluginObjects($pluginParams) as $objMessage)
			{
				$objMessage->pluginModule=self::moduleName;
				$objMessage->pluginIcon=self::moduleName."/icon.png";
				$objMessage->pluginLabel=(!empty($objMessage->title))  ?  $objMessage->title  :  $objMessage->description;
				$objMessage->pluginTooltip=$objMessage->pluginLabel;
				$objMessage->pluginJsIcon="windowParent.redir('".$objMessage->getUrl("container")."');";//Redir vers le sujet (conteneur)
				$objMessage->pluginJsLabel=$objMessage->pluginJsIcon;
				$pluginsList[]=$objMessage;
			}
		}
		return $pluginsList;
	}

	/*
	 * AJAX : Active/désactive les notifications des messages par mail
	 */
	public static function actionNotifyLastMessage()
	{
		$curSubject=Ctrl::getTargetObj();
		if($curSubject->readRight()){
			$usersNotifyLastMessage=Txt::txt2tab($curSubject->usersNotifyLastMessage);
			if($curSubject->curUserNotifyLastMessage())		{$usersNotifyLastMessage=array_diff($usersNotifyLastMessage,[Ctrl::$curUser->_id]);		echo "removeUser";}
			else											{$usersNotifyLastMessage[]=Ctrl::$curUser->_id;											echo "addUser";}
			Db::query("UPDATE ap_forumSubject SET usersNotifyLastMessage=".Db::formatTab2txt($usersNotifyLastMessage)." WHERE _id=".$curSubject->_id);
		}
	}

	/*
	 * ACTION : Edition des themes de sujet
	 */
	public static function actionForumThemeEdit()
	{
		////	Droit d'ajouter un theme?
		if(MdlForumTheme::addRight()==false)  {static::lightboxClose();}
		////	Validation de formulaire
		if(Req::isParam("formValidate")){
			$curObj=Ctrl::getTargetObj();
			$curObj->controlEdit();
			//Modif d'un theme
			$_idSpaces=(!in_array("all",Req::getParam("spaceList")))  ?  Txt::tab2txt(Req::getParam("spaceList"))  :  null;
			$curObj->createUpdate("title=".Db::formatParam("title").", description=".Db::formatParam("description").", color=".Db::formatParam("color").", _idSpaces=".Db::format($_idSpaces));
			//Ferme la page
			static::lightboxClose();
		}
		////	Liste des themes
		$vDatas["themesList"]=MdlForumTheme::getThemes(true);
		$vDatas["themesList"][]=new MdlForumTheme();//nouveau theme vide
		foreach($vDatas["themesList"] as $tmpKey=>$tmpTheme){
			if($tmpTheme->editRight()==false)	{unset($vDatas["themesList"][$tmpKey]);}
			else{
				$tmpTheme->tmpId=$tmpTheme->_targetObjId;
				$tmpTheme->createdBy=($tmpTheme->isNew()==false)  ?  Txt::trad("creation")." : ".$tmpTheme->displayAutor()  :  null;
			}
		}
		////	Affiche la vue
		static::displayPage("VueForumThemeEdit.php",$vDatas);
	}

	/*
	 * ACTION : Edition d'un sujet
	 */
	public static function actionForumSubjectEdit()
	{
		//Init
		$curObj=Ctrl::getTargetObj();
		$curObj->controlEdit();
		if(MdlForumSubject::addRight()==false)   {self::noAccessExit();}
		////	Valide le formulaire
		if(Req::isParam("formValidate"))
		{
			//Enregistre & recharge l'objet
			$dateLastMessage=($curObj->isNew())  ?  ", dateLastMessage=".Db::dateNow()  :  null;//Init "dateLastMessage" pour un nouveau sujet (classement des sujets)
			$curObj=$curObj->createUpdate("title=".Db::formatParam("title").", description=".Db::formatParam("description","editor").", _idTheme=".Db::formatParam("_idTheme").", usersConsultLastMessage=".Db::formatTab2txt([Ctrl::$curUser->_id])." ".$dateLastMessage);
			//Notifie par mail & Ferme la page
			$curObj->sendMailNotif();
			static::lightboxClose();
		}
		////	Affiche la vue
		$vDatas["curObj"]=$curObj;
		if(Req::isParam("_idTheme"))	{$curObj->_idTheme=Req::getParam("_idTheme");}
		$vDatas["themesList"]=MdlForumTheme::getThemes();
		static::displayPage("VueForumSubjectEdit.php",$vDatas);
	}

	/*
	 * ACTION : Edition d'un message
	 */
	public static function actionForumMessageEdit()
	{
		//Init
		$curObj=Ctrl::getTargetObj();
		$curObj->controlEdit();
		////	Valide le formulaire
		if(Req::isParam("formValidate")){
			//Enregistre & recharge l'objet
			$idMessageParent=(Req::isParam("_idMessageParent"))  ?  ", _idMessageParent=".Db::formatParam("_idMessageParent")  :  null;//Rattaché à un message parent?
			$curObj=$curObj->createUpdate("title=".Db::formatParam("title").", description=".Db::formatParam("description","editor").$idMessageParent);
			//MAJ "dateLastMessage" & "usersConsultLastMessage" du sujet conteneur
			Db::query("UPDATE ap_forumSubject SET dateLastMessage=".Db::dateNow().", usersConsultLastMessage=".Db::formatTab2txt([Ctrl::$curUser->_id])." WHERE _id=".$curObj->_idContainer);
			//Notif "auto" si c'est un nouveau message (cf. "Me notifier par mail")
			if($curObj->isNewlyCreated()==false)	{$notifUserIds=null;}
			else{
				$notifUserIds=array_diff(Txt::txt2tab($curObj->containerObj()->usersNotifyLastMessage), [Ctrl::$curUser->_id]);//Users qui on demandé une notif .. et enlève l'auteur courant
				$notifUserIds=array_intersect($notifUserIds, $curObj->containerObj()->affectedUserIds());//Enlève les users qui n'ont plus accès au sujet en question
			}
			//Notifie par mail & Ferme la page
			$curObj->sendMailNotif(null, null, null, $notifUserIds);
			static::lightboxClose();
		}
		////	Affiche la vue
		$vDatas["curObj"]=$curObj;
		$vDatas["messageParent"]=(Req::isParam("_idMessageParent"))  ?  self::getObj("forumMessage",Req::getParam("_idMessageParent"))  :  null;
		static::displayPage("VueForumMessageEdit.php",$vDatas);
	}
}