<?php
/**
* This file is part of the Agora-Project Software package.
*
* @copyright (c) Agora-Project Limited <https://www.agora-project.net>
* @license GNU General Public License, version 2 (GPL-2.0)
*/


/*
 * CONTROLEUR DU MODULE "DASHBOARD"
 */
class CtrlDashboard extends Ctrl
{
	const moduleName="dashboard";
	public static $moduleOptions=["adminAddNews","disablePolls","adminAddPoll"];
	public static $MdlObjects=["MdlDashboardNews"];

	/*******************************************************************************************
	 * VUE : PAGE PRINCIPALE
	 *******************************************************************************************/
	public static function actionDefault()
	{
		////	Objets Actualités/News
		$vDatas["offlineNewsCount"]=MdlDashboardNews::getNews("count","all",true);
		$vDatasNews["newsList"]=MdlDashboardNews::getNews("list",0,Req::param("offlineNews"));//Commence par le block "0"
		$vDatas["vueNewsListInitial"]=self::getVue(Req::curModPath()."VueNewsList.php", $vDatasNews);
		////	Objets Sondages/Polls (sauf guest)
		$vDatas["isPolls"]=(Ctrl::$curSpace->moduleOptionEnabled(self::moduleName,"disablePolls") || Ctrl::$curUser->isUser()==false) ?  false  :  true;
		if($vDatas["isPolls"]==true){
			$vDatas["pollsListNewsDisplay"]=MdlDashboardPoll::getPolls("list",0,true,true);//Sondages pas encore votés : affichés à gauche des news
			$vDatas["pollsNotVotedNb"]=MdlDashboardPoll::getPolls("count",0,true);//Nombre de sondages non votés
			$vDatasPollsMain["pollsList"]=MdlDashboardPoll::getPolls("list",0,Req::param("pollsNotVoted"));//Affichage principal des sondages
			$vDatas["vuePollsListInitial"]=self::getVue(Req::curModPath()."VuePollsList.php", $vDatasPollsMain);
		}
		////	Plugin des nouveaux éléments (sauf guest)
		$vDatas["showNewElems"]=(Ctrl::$curUser->isUser());
		if($vDatas["showNewElems"]==true)
		{
			//Période en préférence / par défaut
			$vDatas["pluginPeriod"]=self::prefUser("pluginPeriod");
			if(in_array($vDatas["pluginPeriod"],["day","week","month","previousConnection"])==false)  {$vDatas["pluginPeriod"]="week";}
			//Periode "jour"/"semaine"/"month"/"previousConnection"
			$vDatas["pluginPeriodOptions"]["day"]  =["timeBegin"=>strtotime(date("Y-m-d 00:00:00")),				"timeEnd"=>strtotime(date("Y-m-d 23:59:59"))];
			$vDatas["pluginPeriodOptions"]["week"] =["timeBegin"=>strtotime("Monday this week 00:00:00"),			"timeEnd"=>strtotime("Sunday this week 23:59:59")];
			$vDatas["pluginPeriodOptions"]["month"]=["timeBegin"=>strtotime("First day of this month 00:00:00"),	"timeEnd"=>strtotime("Last day of this month 23:59:59")];
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
		$vDatas["newsList"]=MdlDashboardNews::getNews("list",Req::param("newsOffsetCpt"),Req::param("offlineNews"));
		if(!empty($vDatas["newsList"]))  {echo self::getVue(Req::curModPath()."VueNewsList.php", $vDatas);}
	}

	/*******************************************************************************************
	 * AJAX : RECUPERE LA SUITE DES SONDAGES VIA L'INFINITE SCROLL
	 *******************************************************************************************/
	public static function actionGetMorePolls()
	{
		$vDatas["infiniteSroll"]=true;
		$vDatas["pollsList"]=MdlDashboardPoll::getPolls("list",Req::param("pollsOffsetCpt"),Req::param("pollsNotVoted"));
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
			foreach(MdlDashboardNews::getPluginObjects($params) as $objNews)
			{
				$objNews->pluginModule=self::moduleName;
				$objNews->pluginIcon=self::moduleName."/icon.png";
				$objNews->pluginLabel=$objNews->description;
				$objNews->pluginTooltip=$objNews->autorLabel(true,true);
				$objNews->pluginJsIcon=null;
				$objNews->pluginJsLabel=null;
				$pluginsList[]=$objNews;
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
		$objNews=Ctrl::getObjTarget();
		$objNews->editControl();
		if(MdlDashboardNews::addRight()==false)  {self::noAccessExit();}
		////	Valide le formulaire
		if(Req::isParam("formValidate")){
			//Enregistre & recharge l'objet
			$objNews=$objNews->createUpdate("description=".Db::param("description","editor").", une=".Db::param("une").", offline=".Db::param("offline").", dateOnline=".Db::param("dateOnline","date").", dateOffline=".Db::param("dateOffline","date"));
			//Notif par mail & Ferme la page
			$objNews->sendMailNotif();
			static::lightboxClose();
		}
		////	Affiche la vue
		$vDatas["objNews"]=$objNews;
		static::displayPage("VueDashboardNewsEdit.php",$vDatas);
	}

	/*******************************************************************************************
	 * VUE : EDITION D'UN SONDAGE
	 *******************************************************************************************/
	public static function actionDashboardPollEdit()
	{
		//Init
		$objPoll=Ctrl::getObjTarget();
		if($objPoll->isNew() && MdlDashboardPoll::addRight()==false)	{self::noAccessExit();}
		else															{$objPoll->editControl();}
		$pollIsVoted=($objPoll->votesNbTotal()>0);
		////	Valide le formulaire
		if(Req::isParam("formValidate"))
		{
			//Enregistre & recharge l'objet
			$objPoll=$objPoll->createUpdate("title=".Db::param("title").", description=".Db::param("description","editor").", multipleResponses=".Db::param("multipleResponses").", publicVote=".Db::param("publicVote").", newsDisplay=".Db::param("newsDisplay").", dateEnd=".Db::param("dateEnd","date"));
			//Si le sondage n'a pas encore été voté : possibilité d'éditer les réponses
			if($pollIsVoted==false)
			{
				//Affiche la notif "Attention : dès que le sondage est voté la modif des réponses est impossible"
				Ctrl::notify("DASHBOARD_votedPollNotif");
				//Récupère les réponses et éventuellement leur fichier associé ("_idResponse" comme clé)
				$responses=Req::param("responses");
				//Supprime si besoin les réponses effacées (modif du sondage)
				foreach($objPoll->getResponses() as $tmpResponse){
					if(empty($responses[$tmpResponse["_id"]]))  {$objPoll->deleteResponse($tmpResponse["_id"]);}
				}
				//Ajoute/modifie les responses possibles
				foreach($responses as $_idResponse=>$reponseLabel)
				{
					if(!empty($reponseLabel))
					{
						//Enregistre en Bdd
						$reponseRank=(empty($reponseRank)) ? 1 : ($reponseRank+1);
						$sqlValues="_id=".Db::format($_idResponse).", _idPoll=".(int)$objPoll->_id.", label=".Db::format($reponseLabel).", `rank`=".(int)$reponseRank;
						Db::query("INSERT INTO ap_dashboardPollResponse SET ".$sqlValues." ON DUPLICATE KEY UPDATE ".$sqlValues);
						//Enregistre si besoin le fichier de la réponse
						if(!empty($_FILES["responsesFile".$_idResponse]))
						{
							$tmpFile=$_FILES["responsesFile".$_idResponse];
							if(File::controleUpload($tmpFile["name"],$tmpFile["size"])){
								$responseFilePath=$objPoll->responseFilePath(["_id"=>$_idResponse,"fileName"=>$tmpFile["name"]]);
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
			foreach($objPoll->getResponses() as $tmpResponse)  {$pollVote.="<li style='list-style:none;margin:10px;'><input type='radio' name='myPoll'> ".$tmpResponse["label"]."</li>";}
			$pollVote.="</ul><a href='".$objPoll->getUrlExternal()."'><button>".Txt::trad("DASHBOARD_vote")."</button></a>";
			$objPoll->sendMailNotif(null,$pollVote);
			static::lightboxClose(null,"&dashboardPoll=true");
		}
		////	Affiche la vue
		$vDatas["objPoll"]=$objPoll;
		$vDatas["pollResponses"]=$objPoll->getResponses();
		$vDatas["pollIsVoted"]=$pollIsVoted;
		static::displayPage("VueDashboardPollEdit.php",$vDatas);
	}

	/*******************************************************************************************
	 * AJAX : VOTE D'UN SONDAGE
	 *******************************************************************************************/
	public static function actionPollVote()
	{
		//Récupère le sondage et Controle l'accès
		$objPoll=Ctrl::getObjTarget();
		$objPoll->readControl();
		//Enregistre le vote du sondage (..si aucun vote n'a déjà été fait par l'user courant)
		if($objPoll->curUserHasVoted()==false && Req::isParam("pollResponse"))
		{
			//Enregistre chaque réponse du vote ("pollResponse" est toujours un tableau et il peut y avoir plusieurs réponses)
			foreach(Req::param("pollResponse") as $tmpResponse)
				{Db::query("INSERT INTO ap_dashboardPollResponseVote SET _idUser=".Ctrl::$curUser->_id.", _idResponse=".Db::format($tmpResponse).", _idPoll=".$objPoll->_id);}
			//Récupère la vue des résultats et le renvoie en Json
			$result["vuePollResult"]=$objPoll->vuePollResult();
			$result["_idPoll"]=$objPoll->_id;
			echo json_encode($result);
		}
	}

	/*******************************************************************************************
	 * TELECHARGE LE FICHIER D'UNE RÉPONSE DE SONDAGE
	 *******************************************************************************************/
	public static function actionResponseDownloadFile()
	{
		//Récupère le sondage et Controle l'accès
		$objPoll=Ctrl::getObjTarget();
		$objPoll->readControl();
		//Download le fichier de la réponse
		$tmpResponse=$objPoll->getResponse(Req::param("_idResponse"));
		$responseFilePath=$objPoll->responseFilePath($tmpResponse);
		if(is_file($responseFilePath))  {File::download($tmpResponse["fileName"],$responseFilePath);}
	}

	/*******************************************************************************************
	 * AJAX : SUPPRIME LE FICHIER D'UNE RÉPONSE DE SONDAGE
	 *******************************************************************************************/
	public static function actionDeleteResponseFile()
	{
		//Récupère le sondage et Controle l'accès
		$objPoll=Ctrl::getObjTarget();
		$objPoll->editControl();
		//Supprime le fichier
		$isDeleted=$objPoll->deleteReponseFile(Req::param("_idResponse"));
		if($isDeleted==true)  {echo "true";}
	}

	/********************************************************************************************
	 * TÉLÉCHARGER LE RÉSULTAT DU SONDAGE EN PDF
	 ********************************************************************************************/
	public static function actionExportPollResult()
	{
		////	RÉCUPÈRE LE SONDAGE ET CONTROLE L'ACCÈS
		$objPoll=Ctrl::getObjTarget();
		$objPoll->editControl();

		////	CREATION DU PDF ET INIT LES CELLULES DES TABLEAUX
		require_once "app/misc/fpdf/fpdf.php";
		$pdf=new FPDF();
		$pdf->AddPage();
		$percentBarWidthMax=180;//width max des barres de %
		$percentBarHeight=5;//Height des barres de %
		$pdf->SetFillColor(245, 245, 245);//Gris clair
		$pdf->SetDrawColor(200);//Trait gris
		$pdf->SetLineWidth(0.3);//Trait de 0.3px

		////	TITRE & DESCRIPTION DU SONDAGE & DATE DU RESULTAT
		$pdf->Ln(15);
		$pdf->SetFont("Arial","B",12);
		$pdf->Write(5, utf8_decode($objPoll->title));
		$pdf->SetFont("Arial",null,9);
		$pdf->Write(5, "   ".utf8_decode(Txt::trad("DASHBOARD_exportPollDate")." ".date("d/m/Y")));
		$pdf->Ln(10);
		$pdf->Write(5, utf8_decode(strip_tags($objPoll->description)));

		////	RESULTAT DE CHAQUE REPONSES
		foreach($objPoll->getResponses(true) as $tmpResponse)
		{
			//Nombre et pourcentage des votes
			$votesNb=$objPoll->votesNb($tmpResponse["_id"]);
			$votesNbLabel=str_replace("--NB_VOTES--",$votesNb,Txt::trad("DASHBOARD_answerVotesNb"));
			$votesPercent=$objPoll->votesPercent($tmpResponse["_id"]);
			$percentBarWidth=($votesPercent>0)  ?  round(($percentBarWidthMax/100) * $votesPercent)  :  7;
			//Affiche la réponse : label + barre de % + users ayant voté la réponse
			$pdf->Ln(12);
			$pdf->SetFont("Arial","B",9);
			$pdf->Write(5, utf8_decode($tmpResponse["label"]));//Label de la réponse
			$pdf->Ln(7);
			$pdf->SetFont("Arial",null,9);
			$pdf->Cell($percentBarWidth, $percentBarHeight, utf8_decode($votesPercent." %  ".$votesNbLabel), "RLTB", 0, "L", 1);
			if(!empty($votesNb) && !empty($objPoll->publicVote))   {$pdf->Ln(7);  $pdf->Write(5, utf8_decode($objPoll->votesUsers($tmpResponse["_id"])));}
		}

		////	FOOTER DU PDF  &&  DOWNLOAD DU FICHIER
		$pdf->Image("app/img/logoLabel.png", 150, 270, null, null, null, utf8_decode(OMNISPACE_URL_LABEL));
		$fileName=Txt::clean($objPoll->getLabel())."_".date("d-m-Y").".pdf";
		$pdf->Output($fileName, "D");
	}
}