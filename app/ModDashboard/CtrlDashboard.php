<?php
/**
* This file is part of the Agora-Project Software package
*
* @copyleft Agora-Project <https://www.agora-project.net>
* @license GNU General Public License, version 2 (GPL-2.0)
*/


/*
 * CONTROLEUR DU MODULE "DASHBOARD"
 */
class CtrlDashboard extends Ctrl
{
	const moduleName="dashboard";
	public static $moduleOptions=["adminAddNews","adminAddPoll","disablePolls"];
	public static $MdlObjects=["MdlDashboardNews"];

	/*******************************************************************************************
	 * VUE : PAGE PRINCIPALE
	 *******************************************************************************************/
	public static function actionDefault()
	{
		////	Objets Actualités/News
		$vDatas["offlineNewsNb"]=MdlDashboardNews::getNews("offlineNewsNb");			//Nb de news archivées
		$vDatasNews["newsList"]=MdlDashboardNews::getNews("scroll");					//Affichage principal des news "infinite scroll"
		$vDatas["vueNewsListInitial"]=self::getVue(Req::curModPath()."VueNewsList.php", $vDatasNews);
		////	Objets Sondages/Polls (sauf guest)
		$vDatas["isPolls"]=(Ctrl::$curSpace->moduleOptionEnabled(self::moduleName,"disablePolls") || Ctrl::$curUser->isUser()==false) ?  false  :  true;
		if($vDatas["isPolls"]==true){
			$vDatas["pollsVotedNb"]=MdlDashboardPoll::getPolls("pollsVotedNb");			//Nb de sondages votés
			$vDatas["pollsListNewsDisplay"]=MdlDashboardPoll::getPolls("newsDisplay");	//Sondages non votés et affichés avec les news (menu de gauche)
			$vDatasPolls["pollsList"]=MdlDashboardPoll::getPolls("scroll");				//Affichage principal des sondages "infinite scroll"
			$vDatas["vuePollsListInitial"]=self::getVue(Req::curModPath()."VuePollsList.php", $vDatasPolls);
		}
		////	Plugin des nouveaux éléments (sauf guest)
		$vDatas["showNewElems"]=(Ctrl::$curUser->isUser());
		if($vDatas["showNewElems"]==true)
		{
			//Période en préférence / par défaut
			$vDatas["pluginPeriod"]=self::prefUser("pluginPeriod");
			if(in_array($vDatas["pluginPeriod"],["day","week","month","previousConnection"])==false)  {$vDatas["pluginPeriod"]="week";}
			//Periode "jour"/"semaine"/"month"/"previousConnection"
			$vDatas["pluginPeriodOptions"]["day"]  =["timeBegin"=>strtotime("today 00:00:00"),						"timeEnd"=>strtotime("today 23:59:59")];
			$vDatas["pluginPeriodOptions"]["week"] =["timeBegin"=>strtotime("monday this week 00:00:00"),			"timeEnd"=>strtotime("sunday this week 23:59:59")];
			$vDatas["pluginPeriodOptions"]["month"]=["timeBegin"=>strtotime("first day of this month 00:00:00"),	"timeEnd"=>strtotime("last day of this month 23:59:59")];
			if(!empty(Ctrl::$curUser->previousConnection))  {$vDatas["pluginPeriodOptions"]["previousConnection"]=["timeBegin"=>Ctrl::$curUser->previousConnection,"timeEnd"=>time()];}
			//Récupère les résultats via le "getPlugins()" de chaque module (vérif si la methode existe)
			$vDatas["pluginsList"]=[];
			$curPeriod=$vDatas["pluginPeriodOptions"][$vDatas["pluginPeriod"]];//Période affichée
			$pluginParams=array("type"=>"dashboard", "dateTimeBegin"=>date("Y-m-d H:i",$curPeriod["timeBegin"]), "dateTimeEnd"=>date("Y-m-d H:i",$curPeriod["timeEnd"]));
			foreach(self::$curSpace->moduleList() as $tmpModule){
				if(method_exists($tmpModule["ctrl"],"getPlugins"))  {$vDatas["pluginsList"]=array_merge($vDatas["pluginsList"], $tmpModule["ctrl"]::getPlugins($pluginParams));}
			}
		}
		////	Affiche la vue
		static::displayPage("VueIndex.php",$vDatas);
	}

	/*******************************************************************************************
	 * AJAX : RECUPERE LA SUITE DES NEWS VIA L'INFINITE SCROLL
	 *******************************************************************************************/
	public static function actionGetMoreNews()
	{
		$vDatas["infiniteSroll"]=true;
		$vDatas["newsList"]=MdlDashboardNews::getNews("scroll",Req::param("newsOffset"));
		if(!empty($vDatas["newsList"]))  {echo self::getVue(Req::curModPath()."VueNewsList.php", $vDatas);}
	}

	/*******************************************************************************************
	 * AJAX : RECUPERE LA SUITE DES SONDAGES VIA L'INFINITE SCROLL
	 *******************************************************************************************/
	public static function actionGetMorePolls()
	{
		$vDatas["infiniteSroll"]=true;
		$vDatas["pollsList"]=MdlDashboardPoll::getPolls("scroll",Req::param("pollsOffset"));
		if(!empty($vDatas["pollsList"]))  {echo self::getVue(Req::curModPath()."VuePollsList.php", $vDatas);}
	}

	/*******************************************************************************************
	 * PLUGINS DU MODULE : RECHERCHE DE NEWS
	 *******************************************************************************************/
	public static function getPlugins($params)
	{
		$pluginsList=[];
		if($params["type"]=="search")
		{
			foreach(MdlDashboardNews::getPluginObjects($params) as $curObj)
			{
				$curObj->pluginIcon=self::moduleName."/icon.png";
				$curObj->pluginLabel=$curObj->description;
				$curObj->pluginTooltip=$curObj->autorLabel(true,true);
				$curObj->pluginJsIcon=null;
				$curObj->pluginJsLabel=null;
				$pluginsList[]=$curObj;
			}
		}
		return $pluginsList;
	}

	/*******************************************************************************************
	 * VUE : EDITION D'UNE ACTUALITÉ
	 *******************************************************************************************/
	public static function actionDashboardNewsEdit()
	{
		//Init
		$curObj=Ctrl::getObjTarget();
		$curObj->editControl();
		if(MdlDashboardNews::addRight()==false)  {self::noAccessExit();}
		////	Valide le formulaire
		if(Req::isParam("formValidate")){
			//Enregistre & recharge l'objet
			$curObj=$curObj->createUpdate("description=".Db::param("description").", une=".Db::param("une").", offline=".Db::param("offline").", dateOnline=".Db::param("dateOnline","inputDate").", dateOffline=".Db::param("dateOffline","inputDate"));
			//Notif par mail & Ferme la page
			$curObj->sendMailNotif();
			static::lightboxClose();
		}
		////	Affiche la vue
		$vDatas["curObj"]=$curObj;
		static::displayPage("VueDashboardNewsEdit.php",$vDatas);
	}

	/*******************************************************************************************
	 * VUE : EDITION D'UN SONDAGE
	 *******************************************************************************************/
	public static function actionDashboardPollEdit()
	{
		//Init
		$curObj=Ctrl::getObjTarget();
		if($curObj->isNew() && MdlDashboardPoll::addRight()==false)	{self::noAccessExit();}
		else														{$curObj->editControl();}
		$pollIsVoted=($curObj->votesNbTotal()>0);
		////	Valide le formulaire
		if(Req::isParam("formValidate"))
		{
			//Enregistre & recharge l'objet
			$curObj=$curObj->createUpdate("title=".Db::param("title").", description=".Db::param("description").", multipleResponses=".Db::param("multipleResponses").", publicVote=".Db::param("publicVote").", newsDisplay=".Db::param("newsDisplay").", dateEnd=".Db::param("dateEnd","inputDate"));
			//Si le sondage n'a pas encore été voté : possibilité d'éditer les réponses
			if($pollIsVoted==false)
			{
				//Affiche la notif "Attention : dès que le sondage est voté la modif des réponses est impossible"
				Ctrl::notify("DASHBOARD_votedPollNotif");
				//Récupère les réponses et éventuellement leur fichier associé ("_idResponse" comme clé)
				$responses=Req::param("responses");
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
						$sqlValues="_id=".Db::format($_idResponse).", _idPoll=".(int)$curObj->_id.", label=".Db::format($reponseLabel).", `rank`=".(int)$reponseRank;
						Db::query("INSERT INTO ap_dashboardPollResponse SET ".$sqlValues." ON DUPLICATE KEY UPDATE ".$sqlValues);
						//Enregistre si besoin le fichier de la réponse
						if(!empty($_FILES["responsesFile".$_idResponse]))
						{
							$tmpFile=$_FILES["responsesFile".$_idResponse];
							if(File::uploadControl($tmpFile)){
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
			$curObj->sendMailNotif($pollVote);
			static::lightboxClose(null,"&dashboardPoll=true");
		}
		////	Affiche la vue
		$vDatas["curObj"]=$curObj;
		$vDatas["pollResponses"]=$curObj->getResponses();
		$vDatas["pollIsVoted"]=$pollIsVoted;
		static::displayPage("VueDashboardPollEdit.php",$vDatas);
	}

	/*******************************************************************************************
	 * AJAX : VOTE D'UN SONDAGE
	 *******************************************************************************************/
	public static function actionPollVote()
	{
		//Récupère le sondage et Controle l'accès
		$curObj=Ctrl::getObjTarget();
		$curObj->readControl();
		//Enregistre le vote du sondage (..si aucun vote n'a déjà été fait par l'user courant)
		if($curObj->curUserHasVoted()==false && Req::isParam("pollResponse"))
		{
			//Enregistre chaque réponse du vote ("pollResponse" est toujours un tableau et il peut y avoir plusieurs réponses)
			foreach(Req::param("pollResponse") as $tmpResponse)
				{Db::query("INSERT INTO ap_dashboardPollResponseVote SET _idUser=".Ctrl::$curUser->_id.", _idResponse=".Db::format($tmpResponse).", _idPoll=".$curObj->_id);}
			//Récupère la vue des résultats et le renvoie en Json
			$result["vuePollResult"]=$curObj->vuePollResult();
			$result["_idPoll"]=$curObj->_id;
			echo json_encode($result);
		}
	}

	/*******************************************************************************************
	 * TELECHARGE LE FICHIER D'UNE RÉPONSE DE SONDAGE
	 *******************************************************************************************/
	public static function actionResponseDownloadFile()
	{
		//Récupère le sondage et Controle l'accès
		$curObj=Ctrl::getObjTarget();
		$curObj->readControl();
		//Download le fichier de la réponse
		$tmpResponse=$curObj->getResponse(Req::param("_idResponse"));
		$responseFilePath=$curObj->responseFilePath($tmpResponse);
		if(is_file($responseFilePath))  {File::download($tmpResponse["fileName"],$responseFilePath);}
	}

	/*******************************************************************************************
	 * AJAX : SUPPRIME LE FICHIER D'UNE RÉPONSE DE SONDAGE
	 *******************************************************************************************/
	public static function actionDeleteResponseFile()
	{
		//Récupère le sondage et Controle l'accès
		$curObj=Ctrl::getObjTarget();
		$curObj->editControl();
		//Supprime le fichier
		$isDeleted=$curObj->deleteReponseFile(Req::param("_idResponse"));
		if($isDeleted==true)  {echo "true";}
	}

	/********************************************************************************************
	 * TÉLÉCHARGER LE RÉSULTAT DU SONDAGE EN PDF
	 ********************************************************************************************/
	public static function actionExportPollResult()
	{
		////	RÉCUPÈRE LE SONDAGE ET CONTROLE L'ACCÈS
		$curObj=Ctrl::getObjTarget();
		$curObj->editControl();

		////	CREATION DU PDF ET INIT LES CELLULES DES TABLEAUX
		require_once "app/misc/fpdf/fpdf.php";
		$pdf=new FPDF();
		$pdf->AddPage();
		$progressBarWidthMax=180;//width max des barres de %
		$progressBarHeight=5;//Height des barres de %
		$pdf->SetFillColor(245, 245, 245);//Gris clair
		$pdf->SetDrawColor(200);//Trait gris
		$pdf->SetLineWidth(0.3);//Trait de 0.3px

		////	TITRE & DESCRIPTION DU SONDAGE & DATE DU RESULTAT
		$pdf->Ln(15);
		$pdf->SetFont("Arial","B",12);
		$pdf->Write(5, utf8_decode($curObj->title));
		$pdf->SetFont("Arial",null,9);
		$pdf->Write(5, "   ".utf8_decode(Txt::trad("DASHBOARD_exportPollDate")." ".date("d/m/Y")));
		$pdf->Ln(10);
		$pdf->Write(5, utf8_decode(strip_tags($curObj->description)));

		////	RESULTAT DE CHAQUE REPONSES
		foreach($curObj->getResponses(true) as $tmpResponse)
		{
			//Nombre et pourcentage des votes
			$votesNb=$curObj->votesNb($tmpResponse["_id"]);
			$votesNbLabel=str_replace("--NB_VOTES--",$votesNb,Txt::trad("DASHBOARD_answerVotesNb"));
			$votesPercent=$curObj->votesPercent($tmpResponse["_id"]);
			$progressBarWidth=($votesPercent>0)  ?  round(($progressBarWidthMax/100) * $votesPercent)  :  7;
			//Affiche la réponse : label + barre de % + users ayant voté la réponse
			$pdf->Ln(12);
			$pdf->SetFont("Arial","B",9);
			$pdf->Write(5, utf8_decode($tmpResponse["label"]));//Label de la réponse
			$pdf->Ln(7);
			$pdf->SetFont("Arial",null,9);
			$pdf->Cell($progressBarWidth, $progressBarHeight, utf8_decode($votesPercent." %  ".$votesNbLabel), "RLTB", 0, "L", 1);
			if(!empty($votesNb) && !empty($curObj->publicVote))   {$pdf->Ln(7);  $pdf->Write(5, utf8_decode($curObj->votesUsers($tmpResponse["_id"])));}
		}

		////	FOOTER DU PDF  &&  DOWNLOAD DU FICHIER
		$pdf->Image("app/img/logoLabel.png", 150, 270, null, null, null, utf8_decode(OMNISPACE_URL_LABEL));
		$fileName=Txt::clean($curObj->getLabel())."_".date("d-m-Y").".pdf";
		$pdf->Output($fileName, "D");
	}
}