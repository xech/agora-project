
<ul id="pollResult<?= $objPoll->_id ?>">
	<?php
	////	Affiche le résultat de chaque réponse au sondage
	foreach($objPoll->getResponses(true) as $tmpResponse)
	{
		//Init
		$responseVotesNb=$objPoll->votesNb($tmpResponse["_id"]);
		$votePercent=($objPoll->votesNbTotal()>0)  ?  round(($responseVotesNb/$objPoll->votesNbTotal())*100)  :  0;//Controler pour les sondages déjà terminés ..mais sans vote (sinon message d'erreur)
		//Détail des votes si "publicVote" est "true"
		if(empty($objPoll->publicVote) || $votePercent==0)  {$publicVoteDetails=null;}
		else{
			$publicVoteDetails=" : ".Txt::trad("DASHBOARD_pollVotedBy")." ";
			$usersVoters=Db::getCol("SELECT DISTINCT _idUser FROM ap_dashboardPollResponseVote WHERE _idPoll=".$objPoll->_id." AND _idResponse=".Db::format($tmpResponse["_id"]));
			foreach($usersVoters as $tmpIdUser)  {$publicVoteDetails.=Ctrl::getObj("user",$tmpIdUser)->getLabel().", ";}
			$publicVoteDetails=trim($publicVoteDetails,", ");
		}
		//Couleur et ID de la pollsResultBar
		$pollsResultBarId="vPollsResultBar".$tmpResponse["_id"];
		if($votePercent>50)		{$pollsResultBarColor="vPollsResultBar100";}
		elseif($votePercent>0)	{$pollsResultBarColor="vPollsResultBar50";}
		else					{$pollsResultBarColor="vPollsResultBar0";}
		//Affiche
		echo "<li title=\"".str_replace("--NB_VOTES--",$responseVotesNb,Txt::trad("DASHBOARD_answerVotesNb"))." ".$publicVoteDetails."\">
				<label>".$tmpResponse["label"].$objPoll->responseFileDiv($tmpResponse)."</label>
				<div class='vPollsResultBarContainer'><div class='vPollsResultBar ".$pollsResultBarId." ".$pollsResultBarColor."'>".$votePercent."%</div></div>
				<script> $(\".".$pollsResultBarId."\").animate({width:'".$votePercent."%'},700);</script>
			  </li>";
	}
	?>
</ul>