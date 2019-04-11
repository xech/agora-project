
<ul id="pollResult<?= $objPoll->_id ?>">
	<?php
	////	Affiche le résultat de chaque réponse au sondage
	$votesNbTotal=$objPoll->votesNb();
	foreach($objPoll->getResponses(true) as $tmpResponse)
	{
		//Init
		$percentBarClass="vReponseBar".$tmpResponse["_id"];
		$votesNb=$objPoll->votesNb($tmpResponse["_id"]);
		$votePercent=(!empty($votesNbTotal)) ? round(($votesNb/$votesNbTotal)*100) : 0;//Controler pour les sondages déjà terminés ..mais sans vote (sinon message d'erreur)
		//Couleur de la percentBar
		if($votePercent>50)		{$percentBarColor="vPollsResultPercentBar100";}
		elseif($votePercent>0)	{$percentBarColor="vPollsResultPercentBar50";}
		else					{$percentBarColor="vPollsResultPercentBar0";}
		//Affiche
		echo "<li title=\"".$votesNb." ".Txt::trad("DASHBOARD_votesNb")."\">
				<label>".$tmpResponse["label"].$objPoll->responseFileDiv($tmpResponse)."</label>
				<div class='vPollsResultPercent'><div class='vPollsResultPercentBar ".$percentBarClass." ".$percentBarColor."'>".$votePercent."%</div></div>
				<script> $(\".".$percentBarClass."\").animate({width:'".$votePercent."%'},700);</script>
			  </li>";
	}
	?>
</ul>