<?php
////	LISTE DES NEWS
foreach($newsList as $tmpNews)
{
	////	Class du container
	$newsClass=(empty($infiniteSroll))  ?  "vNewsContainer"  :  "vNewsContainer infiniteScrollHidden";
	////	Détails de la News :  Date de création & Auteur  / A la une (top list) / Archivée (offline) / Date de mise en ligne ou d'archivage (offline)  / Fichiers joints
	$newsDetails=null;
	if(!empty($tmpNews->dateCrea))		{$newsDetails.="<div>".Txt::trad("postBy")." ".$tmpNews->autorLabel()." - ".Txt::dateLabel($tmpNews->dateCrea)."</div>";}//news par défaut : sans auteur ni date
	if(!empty($tmpNews->une))			{$newsDetails.="<div class='vNewsTopNews' title=\"".Txt::trad("DASHBOARD_topNewsTooltip")."\"><img src='app/img/dashboard/topNews.png'> ".Txt::trad("DASHBOARD_topNews")."</div>";}
	if(!empty($tmpNews->dateOnline))	{$newsDetails.="<div title=\"".Txt::trad("DASHBOARD_dateOnline")." : ".Txt::dateLabel($tmpNews->dateOnline,"dateFull")."\"><img src='app/img/dashboard/dateOnline.png'> ".Txt::dateLabel($tmpNews->dateOnline,"date")."</div>";}
	if(!empty($tmpNews->dateOffline))	{$newsDetails.="<div title=\"".Txt::trad("DASHBOARD_dateOffline")." : ".Txt::dateLabel($tmpNews->dateOffline,"dateFull")."\"><img src='app/img/dashboard/dateOffline.png'> ".Txt::dateLabel($tmpNews->dateOffline,"date")."</div>";}
	if(!empty($tmpNews->offline))		{$newsDetails.="<div><img src='app/img/dashboard/newsOffline.png'> ".Txt::trad("DASHBOARD_offline")."</div>";}
	$newsDetails.=$tmpNews->attachedFileMenu(null);

	////	Affiche l'actu
	echo $tmpNews->objContainer($newsClass).$tmpNews->contextMenu()."
			<div class='vNewsDescription'>".$tmpNews->description."</div>
			<div class='vNewsDetail'>".$newsDetails."</div>
		 </div>";
}