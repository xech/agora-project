<?php
////	LISTE DES SONDAGES
foreach($pollsList as $tmpPoll)
{
	////	Init
	$containerClass="vPollsContainer noClickAction";
	if(!empty($infiniteSroll))  {$containerClass.=" vPollsHidden";}//cf. infinite scroll
	$pollContent=($tmpPoll->curUserHasVoted() || $tmpPoll->isFinished())  ?  $tmpPoll->vuePollResult()  :  $tmpPoll->vuePollForm();//Formulaire ou Résultat du sondage
	$imgIsFinished=($tmpPoll->isFinished())  ?  "<div class='vPollsDateEnd'><img src='app/img/dashboard/pollDateEnd.png'> ".Txt::trad("DASHBOARD_dateEnd")." : ".Txt::displayDate($tmpPoll->dateEnd,"dateFull")."</div>"  :  null;//Sondage terminé

	////	Affiche le résultat
	echo $tmpPoll->divContainer($containerClass).$tmpPoll->contextMenu()."
			<div class='vPollsTitle'>".$tmpPoll->title."</div>
			<div class='vPollsDescription'>".$tmpPoll->description."</div>
			<div class=\"vPollContent".$tmpPoll->_id."\">".$pollContent."</div>
			".$imgIsFinished."
		 </div>";
}