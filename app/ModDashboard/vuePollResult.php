
<ul id="pollResult<?= $objPoll->_id ?>">
	<?php
	////	Affiche le résultat de chaque réponse au sondage
	foreach($objPoll->getResponses(true) as $tmpResponse)
	{
		//Nombre de votes et users ayant voté (si le vote est public)
		$votesNb	 =$objPoll->votesNb($tmpResponse["_id"]);
		$votesPercent=$objPoll->votesPercent($tmpResponse["_id"]);
		$responseTitle=str_replace("--NB_VOTES--",$votesNb,Txt::trad("DASHBOARD_answerVotesNb"));
		$responseTitle.=(!empty($votesNb) && !empty($objPoll->publicVote))  ?  " : ".$objPoll->votesUsers($tmpResponse["_id"])  :  null;
		//Couleur et ID de la pollsResultBar
		$pollsResultBarId="vPollsResultBar".$tmpResponse["_id"];
		if($votesPercent>50)	{$pollsResultBarColor="vPollsResultBar100";}
		elseif($votesPercent>0)	{$pollsResultBarColor="vPollsResultBar50";}
		else					{$pollsResultBarColor="vPollsResultBar0";}
		//Affiche
		echo "<li title=\"".Txt::tooltip($responseTitle)."\">
				<label>".$tmpResponse["label"].$objPoll->responseFileDiv($tmpResponse)."</label>
				<div class='vPollsResultBarContainer'><div class='vPollsResultBar ".$pollsResultBarId." ".$pollsResultBarColor."'>".$votesPercent."%</div></div>
				<script> $(\".".$pollsResultBarId."\").animate({width:'".$votesPercent."%'},500);</script>
			  </li>";
	}
	?>
</ul>