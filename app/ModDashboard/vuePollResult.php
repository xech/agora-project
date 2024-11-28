
<ul id="pollResult<?= $curObj->_id ?>">
	<?php
	////	Affiche le résultat de chaque réponse au sondage
	foreach($curObj->getResponses(true) as $tmpResponse)
	{
		//Nombre de votes et users ayant voté (si le vote est public)
		$votesNb	 =$curObj->votesNb($tmpResponse["_id"]);
		$votesPercent=$curObj->votesPercent($tmpResponse["_id"]);
		$responseTitle=str_replace("--NB_VOTES--",$votesNb,Txt::trad("DASHBOARD_answerVotesNb"));
		$responseTitle.=(!empty($votesNb) && !empty($curObj->publicVote))  ?  " : ".$curObj->votesUsers($tmpResponse["_id"])  :  null;
		//Couleur et ID de la pollsResultBar
		$pollsResultBarId="vPollsResultBar".$tmpResponse["_id"];
		if($votesPercent>50)	{$pollsResultBarColor="vPollsResultBar100";}
		elseif($votesPercent>0)	{$pollsResultBarColor="vPollsResultBar50";}
		else					{$pollsResultBarColor="vPollsResultBar0";}
		//Affiche
		echo '<li '.Txt::tooltip($responseTitle).'>
				<label>'.$tmpResponse["label"].$curObj->responseFileDiv($tmpResponse).'</label>
				<div class="vPollsResultBarContainer"><div class="vPollsResultBar '.$pollsResultBarId.' '.$pollsResultBarColor.'">'.$votesPercent.'%</div></div>
				<script> $(".'.$pollsResultBarId.'").animate({width:"'.$votesPercent.'%"},300); </script>
			  </li>';
	}
	?>
</ul>