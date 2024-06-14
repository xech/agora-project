<?php
////	LISTE DES SONDAGES
$infiniteScrollHidden=(!empty($infiniteSroll)) ? 'infiniteScrollHidden' : null;
foreach($pollsList as $tmpPoll)
{
	$pollContent=($tmpPoll->curUserHasVoted() || $tmpPoll->isFinished())  ?  $tmpPoll->vuePollResult()  :  $tmpPoll->vuePollForm();//Formulaire OU Résultat du sondage
	echo $tmpPoll->divContainerContextMenu('vPollsContainer '.$infiniteScrollHidden).
			'<div class="vPollsTitle">'.$tmpPoll->title.'</div>'.
			'<div class="vPollsDescription">'.$tmpPoll->description.'</div>'.
			'<div class="vPollContent'.$tmpPoll->_id.'">'.$pollContent.'</div>'.
			'<div class="vPollsDetails">'.
				(!empty($tmpPoll->dateEnd)  ?  '<div><img src="app/img/dashboard/pollDateEnd.png">'.Txt::trad("DASHBOARD_dateEnd").' : '.Txt::dateLabel($tmpPoll->dateEnd,"dateFull").'</div>'  :  null).
				$tmpPoll->attachedFileMenu(null).'</div>'.
		'</div>';
}