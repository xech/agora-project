<?php
////	LISTE DES SONDAGES
foreach($pollsList as $tmpPoll)
{
	////	Class du container ("vPollsHidden" : cf. infinite scroll)
	$containerClass=(empty($infiniteSroll))  ?  "vPollsContainer"  :  "vPollsContainer vPollsHidden";
	////	Formulaire de vote OU Résultat du sondage  &&  Date de fin du sondage
	$pollContent=($tmpPoll->curUserHasVoted() || $tmpPoll->isFinished())  ?  $tmpPoll->vuePollResult()  :  $tmpPoll->vuePollForm();
	$imgIsFinished=($tmpPoll->isFinished())  ?  "<div class='vPollsDateEnd'><img src='app/img/dashboard/pollDateEnd.png'> ".Txt::trad("DASHBOARD_dateEnd")." : ".Txt::displayDate($tmpPoll->dateEnd,"dateFull")."</div>"  :  null;

	////	Affiche le résultat
	echo $tmpPoll->divContainer($containerClass).$tmpPoll->contextMenu()."
			<div class='vPollsTitle'>".$tmpPoll->title."</div>
			<div class='vPollsDescription'>".$tmpPoll->description."</div>
			<div class=\"vPollContent".$tmpPoll->_id."\">".$pollContent."</div>
			".$imgIsFinished."
		 </div>";
}