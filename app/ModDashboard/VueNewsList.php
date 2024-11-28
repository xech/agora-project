<?php
////	LISTE DES NEWS
$infiniteScrollHidden=(!empty($infiniteSroll)) ? 'infiniteScrollHidden' : null;
foreach($newsList as $tmpNews)
{
	echo $tmpNews->divContainerContextMenu('vNewsContainer '.$infiniteScrollHidden).
			'<div class="vNewsDescription">'.$tmpNews->description.'</div>'.
			'<div class="vNewsDetail">'.
				(!empty($tmpNews->dateCrea) ?		'<div>'.$tmpNews->autorDateLabel().'</div>'  : null).
				(!empty($tmpNews->une) ?  			'<div class="vNewsTopNews" '.Txt::tooltip("DASHBOARD_topNewsTooltip").'><img src="app/img/dashboard/topNews.png"> '.Txt::trad("DASHBOARD_topNews").'</div>'  : null).
				(!empty($tmpNews->dateOnline) ?		'<div '.Txt::tooltip(Txt::trad("DASHBOARD_dateOnline").' : '.Txt::dateLabel($tmpNews->dateOnline,"dateFull")).'><img src="app/img/dashboard/dateOnline.png"> '.Txt::dateLabel($tmpNews->dateOnline,"dateMini").'</div>'  : null).
				(!empty($tmpNews->dateOffline) ?	'<div '.Txt::tooltip(Txt::trad("DASHBOARD_dateOffline").' : '.Txt::dateLabel($tmpNews->dateOffline,"dateFull")).'><img src="app/img/dashboard/dateOffline.png"> '.Txt::dateLabel($tmpNews->dateOffline,"dateMini").'</div>'  : null).
				(!empty($tmpNews->offline) ?  		'<div><img src="app/img/dashboard/newsOffline.png"> '.Txt::trad("DASHBOARD_offline").'</div>'  : null).
				$tmpNews->attachedFileMenu(null).
			'</div>'.
		'</div>';
}