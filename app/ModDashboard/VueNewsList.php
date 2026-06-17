<?php
////	LISTE DES NEWS
$infiniteScrollHidden=(!empty($infiniteSroll)) ? 'infiniteScrollHidden' : null;
foreach($newsList as $tmpNews)
{
	echo $tmpNews->objContentDiv('vNewsContainer '.$infiniteScrollHidden).
			'<div class="vNewsDescription">'.$tmpNews->description.'</div>'.
			'<div class="vNewsDetail">'.
				(!empty($tmpNews->dateCrea) ?		'<div>'.$tmpNews->autorDate(true).'</div>'  : null).
				(!empty($tmpNews->une) ?  			'<div class="vNewsTopNews" '.Txt::tooltip("DASHBOARD_topNewsTooltip").'><img src="app/img/dashboard/topNews.png">&nbsp; '.Txt::trad("DASHBOARD_topNews").'</div>'  : null).
				(!empty($tmpNews->dateOnline) ?		'<div '.Txt::tooltip(Txt::trad("DASHBOARD_dateOnline").' : '.Txt::dateLabel("dateNum",$tmpNews->dateOnline)).'>&nbsp; <img src="app/img/dashboard/dateOnline.png"> '.Txt::dateLabel("dateNum",$tmpNews->dateOnline).'</div>'  : null).
				(!empty($tmpNews->dateOffline) ?	'<div '.Txt::tooltip(Txt::trad("DASHBOARD_dateOffline").' : '.Txt::dateLabel("dateNum",$tmpNews->dateOffline)).'>&nbsp; <img src="app/img/dateEnd.png"> '.Txt::dateLabel("dateNum",$tmpNews->dateOffline).'</div>'  : null).
				(!empty($tmpNews->offline) ?  		'<div><img src="app/img/dashboard/newsOffline.png">&nbsp; '.Txt::trad("DASHBOARD_offline").'</div>'  : null).
				$tmpNews->attachedFileMenu(false).
			'</div>'.
		'</div>';
}