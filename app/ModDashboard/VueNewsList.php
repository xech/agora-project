<?php
////	LISTE DES NEWS
foreach($newsList as $tmpNews)
{
	////	Class du container ("vNewsHidden" : cf. infinite scroll)
	$newsClass=(empty($infiniteSroll))  ?  "vNewsContainer"  :  "vNewsContainer vNewsHidden";
	////	Détails de la News :  Date de création & Auteur  / News à la une (haut de liste) / News archivée (offline) / Date de mise en ligne/hors ligne
	$newsDetails=null;
	if(!empty($tmpNews->dateCrea))		{$newsDetails.="<span>".Txt::trad("postBy")." ".$tmpNews->displayAutor()." - ".Txt::displayDate($tmpNews->dateCrea,"dateFull")."</span>";}//news par défaut : sans auteur ni date
	if(!empty($tmpNews->une))			{$newsDetails.="<span class='vNewsTopNews' title=\"".Txt::trad("DASHBOARD_topNewsInfo")."\"><img src='app/img/dashboard/topNews.png'> ".Txt::trad("DASHBOARD_topNews")."</span>";}
	elseif(!empty($tmpNews->offline))	{$newsDetails.="<span><img src='app/img/dashboard/newsOffline.png'> ".Txt::trad("DASHBOARD_offline")."</span>";}
	if(!empty($tmpNews->dateOnline))	{$newsDetails.="<span title=\"".Txt::trad("DASHBOARD_dateOnline")." : ".Txt::displayDate($tmpNews->dateOnline,"full")."\"><img src='app/img/dashboard/dateOnline.png'> ".Txt::displayDate($tmpNews->dateOnline)."</span>";}
	if(!empty($tmpNews->dateOffline))	{$newsDetails.="<span title=\"".Txt::trad("DASHBOARD_dateOffline")." : ".Txt::displayDate($tmpNews->dateOffline,"full")."\"><img src='app/img/dashboard/dateOffline.png'> ".Txt::displayDate($tmpNews->dateOffline)."</span>";}

	////	Affiche l'actu
	echo $tmpNews->divContainer($newsClass).$tmpNews->contextMenu()."
			<div class='vNewsDescription'>".$tmpNews->description."</div>
			<div class='vNewsDetail'>".$newsDetails.$tmpNews->menuAttachedFiles(null)."</div>
		 </div>";
}