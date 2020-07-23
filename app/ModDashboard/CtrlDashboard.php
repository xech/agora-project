<?php
/**
* This file is part of the Agora-Project Software package.
*
* @copyright (c) Agora-Project Limited <https://www.agora-project.net>
* @license GNU General Public License, version 2 (GPL-2.0)
*/


/*
 * Controleur du module "Dashboard"
 */
class CtrlDashboard extends Ctrl
{
	const moduleName="dashboard";
	public static $moduleOptions=["adminAddNews","disablePolls","adminAddPoll"];
	public static $MdlObjects=array("MdlDashboardNews");

	/*
	 * ACTION PAR DEFAUT
	 */
	public static function actionDefault()
	{
		////	Objets Actualités/News
		$vDatas["offlineNewsCount"]=MdlDashboardNews::getNews("count","all",true);
		$vDatasNews["newsList"]=MdlDashboardNews::getNews("list",0,Req::getParam("offlineNews"));//Commence par le block "0"
		$vDatas["vueNewsListInitial"]=self::getVue(Req::getCurModPath()."VueNewsList.php", $vDatasNews);
		////	Objets Sondages/Polls (sauf guest)
		$vDatas["isPolls"]=(Ctrl::$curSpace->moduleOptionEnabled(self::moduleName,"disablePolls") || Ctrl::$curUser->isUser()==false) ?  false  :  true;
		if($vDatas["isPolls"]==true){
			$vDatas["pollsListNewsDisplay"]=MdlDashboardPoll::getPolls("list",0,true,true);//Sondages pas encore votés : affichés à gauche des news
			$vDatas["pollsNotVotedNb"]=MdlDashboardPoll::getPolls("count",0,true);//Nombre de sondages non votés
			$vDatasPollsMain["pollsList"]=MdlDashboardPoll::getPolls("list",0,Req::getParam("pollsNotVoted"));//Affichage principal des sondages
			$vDatas["vuePollsListInitial"]=self::getVue(Req::getCurModPath()."VuePollsList.php", $vDatasPollsMain);
		}
		////	Plugin des nouveaux éléments (sauf guest)
		$vDatas["isNewElems"]=(Ctrl::$curUser->isUser()==false) ?  false  :  true;
		if($vDatas["isNewElems"]==true)
		{
			//Période en préférence / par défaut
			$vDatas["pluginPeriod"]=self::prefUser("pluginPeriod");
			if(in_array($vDatas["pluginPeriod"],["day","week","month","previousConnection"])==false)  {$vDatas["pluginPeriod"]="week";}
			//Periode "jour"/"semaine"/"month"/"previousConnection"
			$vDatas["pluginPeriodOptions"]["day"]  =["timeBegin"=>strtotime(date("Y-m-d 00:00:00")),				"timeEnd"=>strtotime(date("Y-m-d 23:59:59"))];
			$vDatas["pluginPeriodOptions"]["week"] =["timeBegin"=>strtotime("Monday this week 00:00:00"),			"timeEnd"=>strtotime("Sunday this week 23:59:59")];
			$vDatas["pluginPeriodOptions"]["month"]=["timeBegin"=>strtotime("First day of this month 00:00:00"),	"timeEnd"=>strtotime("Last day of this month 23:59:59")];
			if(!empty(Ctrl::$curUser->previousConnection))  {$vDatas["pluginPeriodOptions"]["previousConnection"]=["timeBegin"=>Ctrl::$curUser->previousConnection,"timeEnd"=>time()];}
			//Récupère les nouveaux éléments de chaque module (si la methode "plugin()" existe)
			$periodTimes=$vDatas["pluginPeriodOptions"][$vDatas["pluginPeriod"]];//début/fin de la période sélectionnée
			$pluginParams=array("type"=>"dashboard", "dateTimeBegin"=>date("Y-m-d H:i",$periodTimes["timeBegin"]), "dateTimeEnd"=>date("Y-m-d H:i",$periodTimes["timeEnd"]));
			$vDatas["pluginsList"]=[];
			foreach(self::$curSpace->moduleList() as $tmpModule)
			{
				if(method_exists($tmpModule["ctrl"],"plugin"))
				{
					foreach($tmpModule["ctrl"]::plugin($pluginParams) as $tmpObj)
					{
						//Ajoute un "pluginSpecificMenu" (exple: proposition d'événement du module Calendar)
						if(isset($tmpObj->pluginSpecificMenu))	{$vDatas["pluginsList"]["pluginSpecificMenu"]=$tmpObj;}
						//Si l'objet se trouve dans un conteneur qui a déjà été affiché (dans la "pluginsList") : on continue et ne l'affiche pas (exple: fichiers d'un nouveau dossier)
						elseif(is_object($tmpObj->containerObj()) && array_key_exists($tmpObj->containerObj()->_targetObjId,$vDatas["pluginsList"]))  {continue;}
						//Sinon on formate l'affichage de l'objet
						else
						{
							//Label & tooltips: suppr les balises html (cf. TinyMce) et réduit la taille du texte
							$tmpObj->pluginLabel=Txt::cleanPlugin($tmpObj->pluginLabel,200);
							$tmpObj->pluginTooltip=Txt::cleanPlugin($tmpObj->pluginTooltip,500);
							//Tooltip de l'icone : ajoute si besoin "Afficher l'element dans son dossier"
							$tmpObj->pluginTooltipIcon=($tmpObj::isInArbo())  ?  Txt::trad("DASHBOARD_pluginsTooltipRedir")  :  $tmpObj->pluginTooltip;
							//Tooltip : ajoute si besoin l'icone des "Elements courants" (evts et taches)
							if($tmpObj->pluginIsCurrent){
								$tmpObj->pluginTooltip=" <img src='app/img/newObjCurrent.png'> ".Txt::trad("DASHBOARD_pluginsCurrent")."<hr>".$tmpObj->pluginTooltip;
								$tmpObj->pluginLabel.=" <img src='app/img/newObjCurrent.png'>";
							}
							//Ajoute l'auteur et la date de création
							if(isset($tmpObj->dateCrea))  {$tmpObj->pluginTooltip.="<hr>".Txt::trad("creation")." : ".Txt::displayDate($tmpObj->dateCrea,"full")."<hr>".$tmpObj->displayAutor(true,true);}
							//Ajoute à la "pluginsList"
							$vDatas["pluginsList"][$tmpObj->_targetObjId]=$tmpObj;
						}
					}
				}
			}
		}
		////	Affiche la vue
		static::displayPage("VueIndex.php",$vDatas);
	}

	/*
	 * AJAX : RECUPERE LA SUITE DES NEWS VIA L'INFINITE SCROLL
	 */
	public static function actionGetMoreNews()
	{
		$vDatas["infiniteSroll"]=true;
		$vDatas["newsList"]=MdlDashboardNews::getNews("list",Req::getParam("newsOffsetCpt"),Req::getParam("offlineNews"));
		if(!empty($vDatas["newsList"]))  {echo self::getVue(Req::getCurModPath()."VueNewsList.php", $vDatas);}
	}

	/*
	 * AJAX : RECUPERE LA SUITE DES SONDAGES VIA L'INFINITE SCROLL
	 */
	public static function actionGetMorePolls()
	{
		$vDatas["infiniteSroll"]=true;
		$vDatas["pollsList"]=MdlDashboardPoll::getPolls("list",Req::getParam("pollsOffsetCpt"),Req::getParam("pollsNotVoted"));
		if(!empty($vDatas["pollsList"]))  {echo self::getVue(Req::getCurModPath()."VuePollsList.php", $vDatas);}
	}

	/*
	 * PLUGINS : RECHERCHE DE NEWS
	 */
	public static function plugin($pluginParams)
	{
		$pluginsList=array();
		if($pluginParams["type"]=="search")
		{
			foreach(MdlDashboardNews::getPluginObjects($pluginParams) as $objNews)
			{
				$objNews->pluginModule=self::moduleName;
				$objNews->pluginIcon=self::moduleName."/icon.png";
				$objNews->pluginLabel="<span onclick=\"$('.pluginNews".$objNews->_id."').fadeIn();$(this).hide();\">".Txt::reduce(strip_tags($objNews->description,"<span><img><br>"))." <img src='app/img/arrowBottom.png'></span>
									   <div class='pluginNews".$objNews->_id."' style='display:none;max-height:800px;overflow:auto;'>".$objNews->contextMenu(["iconBurger"=>"small"])." ".$objNews->description."</div>";//Affiche l'actualité complete avec le menu contextuel!
				$objNews->pluginTooltip=$objNews->displayAutor(true,true);
				$objNews->pluginJsIcon=null;
				$objNews->pluginJsLabel=null;
				$pluginsList[]=$objNews;
			}
		}
		return $pluginsList;
	}

	/*
	 * ACTION : Edition d'une actualité
	 */
	public static function actionDashboardNewsEdit()
	{
		//Init
		$curObj=Ctrl::getTargetObj();
		$curObj->controlEdit();
		if(MdlDashboardNews::addRight()==false)  {self::noAccessExit();}
		////	Valide le formulaire
		if(Req::isParam("formValidate")){
			//Enregistre & recharge l'objet
			$curObj=$curObj->createUpdate("description=".Db::formatParam("description","editor").", une=".Db::formatParam("une").", offline=".Db::formatParam("offline").", dateOnline=".Db::formatParam("dateOnline","date").", dateOffline=".Db::formatParam("dateOffline","date"));
			//Notif par mail & Ferme la page
			$curObj->sendMailNotif();
			static::lightboxClose();
		}
		////	Affiche la vue
		$vDatas["curObj"]=$curObj;
		static::displayPage("VueDashboardNewsEdit.php",$vDatas);
	}

	/*
	 * ACTION : Edition d'un sondage
	 */
	public static function actionDashboardPollEdit()
	{
		//Init
		$curObj=Ctrl::getTargetObj();
		$curObj->controlEdit();
		if(MdlDashboardPoll::addRight()==false)  {self::noAccessExit();}
		$pollIsVoted=($curObj->votesNbTotal()>0);
		////	Valide le formulaire
		if(Req::isParam("formValidate"))
		{
			//Enregistre & recharge l'objet
			$curObj=$curObj->createUpdate("title=".Db::formatParam("title").", description=".Db::formatParam("description","editor").", multipleResponses=".Db::formatParam("multipleResponses").", publicVote=".Db::formatParam("publicVote").", newsDisplay=".Db::formatParam("newsDisplay").", dateEnd=".Db::formatParam("dateEnd","date"));
			//Si le sondage n'a pas encore été voté : possibilité d'éditer les réponses
			if($pollIsVoted==false)
			{
				//Affiche la notif "Attention : dès que le sondage est voté la modif des réponses est impossible"
				Ctrl::addNotif("DASHBOARD_votedPollNotif");
				//Récupère les réponses et éventuellement leur fichier associé ("_idResponse" comme clé)
				$responses=Req::getParam("responses");
				//Supprime si besoin les réponses effacées (modif du sondage)
				foreach($curObj->getResponses() as $tmpResponse){
					if(empty($responses[$tmpResponse["_id"]]))  {$curObj->deleteResponse($tmpResponse["_id"]);}
				}
				//Ajoute/modifie les responses possibles
				foreach($responses as $_idResponse=>$reponseLabel)
				{
					if(!empty($reponseLabel))
					{
						//Enregistre en Bdd
						$reponseRank=(empty($reponseRank)) ? 1 : ($reponseRank+1);
						$sqlValues="_id=".Db::format($_idResponse).", _idPoll=".(int)$curObj->_id.", label=".Db::format($reponseLabel).", rank=".(int)$reponseRank;
						Db::query("INSERT INTO ap_dashboardPollResponse SET ".$sqlValues." ON DUPLICATE KEY UPDATE ".$sqlValues);
						//Enregistre si besoin le fichier de la réponse
						if(!empty($_FILES["responsesFile".$_idResponse]))
						{
							$tmpFile=$_FILES["responsesFile".$_idResponse];
							if(File::controleUpload($tmpFile["name"],$tmpFile["size"])){
								$responseFilePath=$curObj->responseFilePath(["_id"=>$_idResponse,"fileName"=>$tmpFile["name"]]);
								move_uploaded_file($tmpFile["tmp_name"],$responseFilePath);
								if(File::isType("imageResize",$tmpFile["name"]))  {File::imageResize($responseFilePath,$responseFilePath,1024);}//1024px max
								Db::query("UPDATE ap_dashboardPollResponse SET fileName=".Db::format($tmpFile["name"])." WHERE _id=".Db::format($_idResponse));
							}
						}
					}
				}
			}
			//Notif par mail & Ferme la page ("dashboardPoll=true" : cf. "dashboardOption()")
			$pollVote="<ul style='padding-left:20px;'>";
			foreach($curObj->getResponses() as $tmpResponse)  {$pollVote.="<li style='list-style:none;margin:10px;'><input type='radio' name='myPoll'> ".$tmpResponse["label"]."</li>";}
			$pollVote.="</ul><a href='".$curObj->getUrlExternal()."'><button>".Txt::trad("DASHBOARD_vote")."</button></a>";
			$curObj->sendMailNotif(null,$pollVote);
			static::lightboxClose("&dashboardPoll=true");
		}
		////	Affiche la vue
		$vDatas["objPoll"]=$curObj;
		$vDatas["pollResponses"]=$curObj->getResponses();
		$vDatas["pollIsVoted"]=$pollIsVoted;
		static::displayPage("VueDashboardPollEdit.php",$vDatas);
	}

	/*
	 * AJAX : Vote d'un sondage
	 */
	public static function actionPollVote()
	{
		//Récupère le sondage et Controle l'accès
		$curObj=Ctrl::getTargetObj();
		$curObj->controlRead();
		//Enregistre le vote du sondage (..si aucun vote n'a déjà été fait par l'user courant)
		if($curObj->curUserHasVoted()==false && Req::isParam("pollResponse"))
		{
			//Enregistre chaque réponse du vote ("pollResponse" est toujours un tableau et il peut y avoir plusieurs réponses)
			foreach(Req::getParam("pollResponse") as $tmpResponse)
				{Db::query("INSERT INTO ap_dashboardPollResponseVote SET _idUser=".Ctrl::$curUser->_id.", _idResponse=".Db::format($tmpResponse).", _idPoll=".$curObj->_id);}
			//Récupère la vue des résultats et le renvoie en Json
			$result["vuePollResult"]=$curObj->vuePollResult();
			$result["_idPoll"]=$curObj->_id;
			echo json_encode($result);
		}
	}

	/*
	 * ACTION : Telecharge le fichier d'une réponse
	 */
	public static function actionResponseDownloadFile()
	{
		//Récupère le sondage et Controle l'accès
		$curObj=Ctrl::getTargetObj();
		$curObj->controlRead();
		//Download le fichier de la réponse
		$tmpResponse=$curObj->getResponse(Req::getParam("_idResponse"));
		$responseFilePath=$curObj->responseFilePath($tmpResponse);
		if(is_file($responseFilePath))  {File::download($tmpResponse["fileName"],$responseFilePath);}
	}

	/*
	 * AJAX : Supprime le fichier d'une réponse
	 */
	public static function actionDeleteResponseFile()
	{
		//Récupère le sondage et Controle l'accès
		$curObj=Ctrl::getTargetObj();
		$curObj->controlEdit();
		//Supprime le fichier
		$isDeleted=$curObj->deleteReponseFile(Req::getParam("_idResponse"));
		if($isDeleted==true)  {echo "true";}
	}
}