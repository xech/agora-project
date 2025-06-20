<?php
/**
* This file is part of the Agora-Project Software package
*
* @copyleft Agora-Project <https://www.agora-project.net>
* @license GNU General Public License (GPL-2.0)
*/


/*
 * MODELE DES SONDAGES
 */
class MdlDashboardPoll extends MdlObject
{
	const moduleName="dashboard";
	const objectType="dashboardPoll";
	const dbTable="ap_dashboardPoll";
	const descriptionEditor=true;
	const hasAttachedFiles=true;
	const hasNotifMail=true;
	const hasUsersLike=true;
	const hasUsersComment=true;
	protected static $_hasAccessRight=true;
	public static $requiredFields=["title"];
	public static $searchFields=["title","description"];
	public static $sortFields=["dateCrea@@desc","dateCrea@@asc","dateModif@@desc","dateModif@@asc","_idUser@@asc","_idUser@@desc","title@@asc","title@@desc","description@@asc","description@@desc"];
	//Valeurs mises en cache
	private $_responseList=null;
	private $_votesNbTotal=null;

	/********************************************************************************************
	 * INSTALL/UPDATE : CRÉÉ UN PREMIER SONDAGE D'EXAMPLE
	 ********************************************************************************************/
	public static function dbFirstRecord()
	{
		//Créé les enregistrements si la table est vide
		if(Db::getVal("SELECT count(*) FROM ".self::dbTable)==0){
			Db::query("INSERT INTO ap_dashboardPoll SET _id=1, title=".Db::format(Txt::trad("INSTALL_dataDashboardPoll")).", _idUser=1, newsDisplay=1, dateCrea=NOW()");
			Db::query("INSERT INTO ap_dashboardPollResponse (_id, _idPoll, label, `rank`) VALUES ('5bd1903d3df9u8t',1,".Db::format(Txt::trad("INSTALL_dataDashboardPollA")).",1), ('5bd1903d3e11dt5',1,".Db::format(Txt::trad("INSTALL_dataDashboardPollB")).",2), ('5bd1903d3e041p7',1,".Db::format(Txt::trad("INSTALL_dataDashboardPollC")).",3)");
			Db::query("INSERT INTO ap_objectTarget (objectType, _idObject, _idSpace, `target`, accessRight) VALUES ('dashboardPoll', 1, 1, 'spaceUsers', 1)");
		}
	}

	/**************************************************************************************************************************************************************
	 * STATIC : SONDAGES À AFFICHER
	 * $mode :  Nb de sondages déjà votés : "pollsVotedNb"  ||  Sondages non votés et affichés avec les news : "newsDisplay"  ||  Affichage infinite scroll = "scroll"
	 **************************************************************************************************************************************************************/
	public static function getPolls($mode, $pollsOffset=0)
	{
		// Init/Switch l'affichage uniquement des sondages déjà votés
		if(empty($_SESSION["pollsVotedShow"]) || Req::isParam("pollsVotedShow"))  {$_SESSION["pollsVotedShow"]=(bool)(Req::param("pollsVotedShow")=="true");}
		// Selection SQL de base
		$sqlSelection=static::sqlDisplay();
		// Filtre uniquement les sondages déjà votés (cf. "pollsVoted") ou les sondages non votés (cf. "newsDisplay")
		if($mode=="newsDisplay" || $mode=="pollsVotedNb" || $_SESSION["pollsVotedShow"]==true){
			$selector=($mode=="newsDisplay") ? "NOT IN" : "IN";
			$sqlSelection.=" AND _id ".$selector." (select _idPoll as _id from ap_dashboardPollResponseVote where _idUser=".Ctrl::$curUser->_id.")";
		}
		// Nb de sondages déjà votés
		if($mode=="pollsVotedNb")	{return Db::getVal("SELECT count(*) FROM ".static::dbTable." WHERE ".$sqlSelection);}
		// Récupère les sondages non votés et affichés avec les news (affiche les sondages les + populaires en premier : cf. "nbVotes")
		elseif($mode=="newsDisplay"){
			$sqlGroupBy="GROUP BY _id, title, description, dateEnd, multipleResponses, newsDisplay, publicVote, dateCrea, _idUser, dateModif, _idUserModif";//Tous les champs dans 'T1' doivent être dans le 'GROUP BY' (cf. "sql_mode=only_full_group_by" du "my.cnf")
			return Db::getObjTab(static::objectType, "SELECT T1.*, COUNT(T2._idResponse) as nbVotes  FROM ap_dashboardPoll T1 LEFT JOIN ap_dashboardPollResponseVote T2 ON T1._id=T2._idPoll  WHERE ".$sqlSelection." AND newsDisplay is not null  ".$sqlGroupBy."  ORDER BY nbVotes DESC, T1.dateCrea DESC  LIMIT 10 OFFSET 0");
		}
		// Sondages pour l'affichage "infinite scroll"
		else{
			$sqlLimit="LIMIT 10 OFFSET ".((int)$pollsOffset * 10);//"infinite scroll" par blocs de 10
			return Db::getObjTab(static::objectType, "SELECT * FROM ".static::dbTable." WHERE ".$sqlSelection." ".static::sqlSort()." ".$sqlLimit);
		}
	}

	/********************************************************************************************************
	 * STATIC : DROIT DE CRÉER UN SONDAGE
	 ********************************************************************************************************/
	public static function addRight()
	{
		return (Ctrl::$curUser->isSpaceAdmin() || (Ctrl::$curUser->isUser() && Ctrl::$curSpace->moduleOptionEnabled(self::moduleName,"adminAddPoll")==false));
	}

	/********************************************************************************************************
	 * LISTE DES RÉPONSES D'UN SONDAGE
	 ********************************************************************************************************/
	public function getResponses($orderByNbVotes=false)
	{
		if($this->_responseList===null){
			//Réponses du sondage (trié par "rank"), avec pour chaque réponse : le nb de votes ("GROUP BY") et auquel cas le chemin du fichier
			$this->_responseList=Db::getTab("SELECT T1.*, COUNT(T2._idResponse) as nbVotes  FROM ap_dashboardPollResponse T1 LEFT JOIN ap_dashboardPollResponseVote T2 ON T1._id=T2._idResponse  WHERE T1._idPoll=".$this->_id."  GROUP BY _id, _idPoll, label, `rank`, fileName  ORDER BY `rank` ASC");//Tous les champs dans 'T1' doivent être dans le 'GROUP BY' (cf. "sql_mode=only_full_group_by" du "my.cnf")
			foreach($this->_responseList as $tmpKey=>$tmpResponse){
				if(!empty($tmpResponse["fileName"])){
					$this->_responseList[$tmpKey]["filePath"]=$this->responseFilePath($tmpResponse);
					$this->_responseList[$tmpKey]["fileUrlDownload"]="?ctrl=dashboard&action=ResponseDownloadFile&typeId=".$this->_typeId."&_idResponse=".$tmpResponse["_id"];
				}
			}
		}
		//Tri par défaut ("rank") OU par nombre de votes
		if($orderByNbVotes==false || function_exists("array_column")==false)	{return $this->_responseList;}
		else{
			$responseList=$this->_responseList;
			$columnToSort=array_column($this->_responseList,"nbVotes");//Colonne où porte le tri
			array_multisort($columnToSort,SORT_DESC,$responseList);//Tri le tableau
			return $responseList;
		}
	}

	/********************************************************************************************************
	 * INFOS SUR UNE RÉPONSE
	 ********************************************************************************************************/
	public function getResponse($_idResponse)
	{
		//Parcourt la liste des réponses et renvoi la réponse demandée
		foreach($this->getResponses() as $tmpResponse){
			if($tmpResponse["_id"]==$_idResponse)  {return $tmpResponse;}
		}
	}

	/********************************************************************************************************
	 * NOMBRE DE VOTES POUR LE SONDAGE OU POUR UNE RÉPONSE DU SONDAGE
	 ********************************************************************************************************/
	public function votesNb($_idResponse=false)
	{
		$sqlResponse=(!empty($_idResponse))  ?  "AND _idResponse=".Db::format($_idResponse)  :  null;
		return Db::getVal("SELECT count(DISTINCT _idUser) FROM ap_dashboardPollResponseVote WHERE _idPoll=".$this->_id." ".$sqlResponse);//"DISTINCT _idUser" car un user peut choisir plusieurs réponses
	}

	/********************************************************************************************************
	 * PERSONNES AYANT VOTÉ POUR LE SONDAGE OU POUR UNE RÉPONSE DU SONDAGE
	 ********************************************************************************************************/
	public function votesUsers($_idResponse=false)
	{
		$sqlResponse=(!empty($_idResponse))  ?  "AND _idResponse=".Db::format($_idResponse)  :  null;
		$usersVoters=Db::getCol("SELECT DISTINCT _idUser FROM ap_dashboardPollResponseVote WHERE _idPoll=".$this->_id." ".$sqlResponse);
		if(!empty($usersVoters)){
			$usersLabel=null;
			foreach($usersVoters as $tmpIdUser)  {$usersLabel.=Ctrl::getObj("user",$tmpIdUser)->getLabel().", ";}
			return Txt::trad("DASHBOARD_pollVotedBy")." ".trim($usersLabel,", ");
		}
	}

	/********************************************************************************************************
	 * POURCENTAGE DES VOTES POUR UNE RÉPONSE DU SONDAGE
	 ********************************************************************************************************/
	public function votesPercent($_idResponse)
	{
		return ($this->votesNbTotal()>0)  ?  round(($this->votesNb($_idResponse) / $this->votesNbTotal()) * 100)  :  0;
	}

	/********************************************************************************************************
	 * NOMBRE TOTAL DE VOTES POUR LE SONDAGE (GARDE EN CACHE)
	 ********************************************************************************************************/
	public function votesNbTotal()
	{
		if($this->_votesNbTotal===null)  {$this->_votesNbTotal=$this->votesNb();}
		return $this->_votesNbTotal;
	}

	/********************************************************************************************************
	 * L'USER COURANT A DÉJÀ VOTÉ LE SONDAGE ?
	 ********************************************************************************************************/
	public function curUserHasVoted()
	{
		return (Db::getVal("SELECT count(*) FROM ap_dashboardPollResponseVote WHERE _idPoll=".$this->_id." AND _idUser=".Ctrl::$curUser->_id) > 0);
	}

	/********************************************************************************************************
	 * VÉRIFIE SI LE SONDAGE EST TERMINÉ
	 ********************************************************************************************************/
	public function isFinished()
	{
		return (!empty($this->dateEnd) && Txt::formatDate($this->dateEnd,"dbDate","time")<time());
	}

	/********************************************************************************************************
	 * RÉCUPÈRE LES RÉSULTATS D'UN SONDAGE
	 ********************************************************************************************************/
	public function vuePollResult()
	{
		$vDatas["curObj"]=$this;
		return Ctrl::getVue(Req::curModPath()."vuePollResult.php", $vDatas);
	}

	/********************************************************************************************************
	 * RÉCUPÈRE LE FORMULAIRE DE VOTE D'UN SONDAGE
	 ********************************************************************************************************/
	public function vuePollForm($newsDisplay=false)
	{
		$vDatas["curObj"]=$this;
		$vDatas["newsDisplay"]=($newsDisplay==true)  ?  "newsDisplay"  :  null;
		$vDatas["submitButtonTooltip"]=(!empty($this->publicVote))  ?  Txt::trad("DASHBOARD_publicVote")  :  Txt::trad("DASHBOARD_voteTooltip");
		return Ctrl::getVue(Req::curModPath()."vuePollForm.php", $vDatas);
	}

	/********************************************************************************************************
	 * SURCHARGE : SUPPRESSION D'UN SONDAGE
	 ********************************************************************************************************/
	public function delete()
	{
		//Supprime chaque reponses du sondage
		foreach($this->getResponses() as $tmpResponse)	{$this->deleteResponse($tmpResponse["_id"],true);}
		//Supprime l'objet lui-même
		parent::delete();
	}

	/********************************************************************************************************
	 * SUPPRIME UNE RÉPONSE DU SONDAGE
	 * $forceDelete à false (édition du sondage) : ne supprime pas la réponse si le sondage a déjà été voté
	 ********************************************************************************************************/
	public function deleteResponse($_idResponse, $forceDelete=false)
	{
		//On supprime les votes de la réponse, Puis supprime la réponse elle-même
		if($this->editRight() && ($forceDelete==true || $this->votesNb($_idResponse)==0))
		{
			//Supprime en bdd
			Db::query("DELETE FROM ap_dashboardPollResponseVote WHERE _idPoll=".$this->_id." AND _idResponse=".Db::format($_idResponse));
			Db::query("DELETE FROM ap_dashboardPollResponse		WHERE _idPoll=".$this->_id." AND _id=".Db::format($_idResponse));
			//Supprime le fichier si besoin
			$this->deleteReponseFile($_idResponse);
		}
	}

	/********************************************************************************************************
	 * PATH DU FICHIER D'UNE RÉPONSE ($tmpResponse : "_id" + "fileName" nécessaires)
	 ********************************************************************************************************/
	public function responseFilePath($tmpResponse)
	{
		return PATH_MOD_DASHBOARD.$tmpResponse["_id"].".".File::extension($tmpResponse["fileName"]);
	}

	/********************************************************************************************************
	 * AFFICHAGE DU FICHIER D'UNE RÉPONSE ($tmpResponse : "_id" + "fileName" + "fileUrlDownload" nécessaires)
	 ********************************************************************************************************/
	public function responseFileDiv($tmpResponse)
	{
		//Il y a un fichier ?
		if(!empty($tmpResponse["fileName"])){
			//Image avec un lien pour l'afficher OU Nom du fichier avec un lien de téléchargement
			if(File::isType("imageBrowser",$tmpResponse["fileName"]))	{$responseFileDiv='<a href="'.$this->responseFilePath($tmpResponse).'" data-fancybox="images" '.Txt::tooltip($tmpResponse["fileName"]).'><img src="'.$this->responseFilePath($tmpResponse).'"></a>';}
			else														{$responseFileDiv='<a href="'.$tmpResponse["fileUrlDownload"].'" '.Txt::tooltip("download").'><img src="app/img/attachment.png"> '.$tmpResponse["fileName"].'</a>';}
			return "<div class='vPollsResponseFile'>".$responseFileDiv."</div>";
		}
	}

	/********************************************************************************************************
	 * SUPPRIME LE FICHIER D'UNE RÉPONSE
	 ********************************************************************************************************/
	public function deleteReponseFile($_idResponse)
	{
		$tmpResponse=$this->getResponse($_idResponse);
		if(!empty($tmpResponse["fileName"]) && is_file($this->responseFilePath($tmpResponse))){
			$isDeleted=unlink($this->responseFilePath($tmpResponse));
			if($isDeleted==true)  {Db::query("UPDATE ap_dashboardPollResponse SET fileName=null WHERE _idPoll=".$this->_id." AND _id=".Db::format($_idResponse));}
			return $isDeleted;
		}
	}

	/********************************************************************************************************
	 * VUE : SURCHARGE DU MENU CONTEXTUEL
	 ********************************************************************************************************/
	public function contextMenu($options=null)
	{
		//// Ajoute le nombre de votes pour le sondage (avec Tooltip si admin)
		$tooltipVotedBy=(Ctrl::$curUser->isSpaceAdmin())  ?  $this->votesUsers()  :  null;
		$options["specificOptions"][]=["iconSrc"=>"info.png", "label"=>'<span class="cursorHelp" '.Txt::tooltip($tooltipVotedBy).'>'.str_replace('--NB_VOTES--',$this->votesNbTotal(),Txt::trad("DASHBOARD_pollVotesNb")).'</span>'];
		//// Date de fin de vote  &&  Vote est public  &&  Export pdf du résultat d'un sondage
		if(!empty($this->dateEnd))				{$options["specificOptions"][]=["iconSrc"=>"dateEnd.png", "label"=>"<span style='cursor:default'>".Txt::trad("DASHBOARD_dateEnd")." : ".Txt::dateLabel($this->dateEnd,"dateFull")."</span>"];}
		if(!empty($this->publicVote))			{$options["specificOptions"][]=["iconSrc"=>"eye.png", "label"=>"<span style='cursor:default'>".Txt::trad("DASHBOARD_publicVote")."</span>"];}
		if(Ctrl::$curUser->isGeneralAdmin())	{$options["specificOptions"][]=["actionJs"=>"redir('?ctrl=dashboard&action=ExportPollResult&typeId=".$this->_typeId."')", "iconSrc"=>"download.png", "label"=>Txt::trad("DASHBOARD_exportPoll")];}
		//// Retourne le menu contextuel
		return parent::contextMenu($options);
	}
}