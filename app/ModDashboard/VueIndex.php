<script>
/*******************************************************************************************
 *	INIT
 *******************************************************************************************/
$(function(){
	////	"Infinite scroll" : Affichage progressif des news et sondages
	$(window).scroll(function(){
		//Timeout pour ne pas charger durant le scroll
		if(typeof scrollTimeout!="undefined")  {clearTimeout(scrollTimeout);}//Pas de cumul de Timeout ..et de requête ajax!
		scrollTimeout=setTimeout(function(){
			//Lance l'infinite scroll quand on arrive en fin de page  (hauteur de page < (scrollTop + hauteur de fenêtre + 20px))
			if($(document).height() < ($(window).scrollTop() + $(window).height() + 20))
			{
				//Init le chargement
				if(typeof loadMoreNews=="undefined"){
					loadMoreNews=loadMorePolls=true;//Marqueur pour savoir si on doit charger des News/Polls en fin de page
					newsOffsetCpt=pollsOffsetCpt=1;	//Compteur des blocs de news/polls déjà affichés (offset). Commence à "1" car le bloc "0" est affiché au chargement de page
				}
				//Charge les news suivantes (via ".get()" et non ".ajax")
				if($("#contentNews").is(":visible") && loadMoreNews==true)
				{
					$("#contentNews").append("<div class='dashboardLoadingImg'><img src='app/img/loading2.png'></div>");
					$.get("?ctrl=dashboard&action=GetMoreNews&offlineNews=<?= Req::param("offlineNews") ?>&newsOffsetCpt="+newsOffsetCpt, function(vueNewsList){
						if(vueNewsList.length==0)  {loadMoreNews=false;}//Passe à false si ya plus rien à charger : évite les requêtes inutiles
						else{
							$("#contentNews").append(vueNewsList);	//Affiche les news
							$(".vNewsContainer").fadeIn(500);	//"fadeIn()" car masquées par défaut via .vNewsHidden
							mainPageDisplay(false);				//Update les tooltips/lightbox
							initMenuContext();					//Maj les menus contextuels des news
							newsOffsetCpt++;					//Update le compteur
						}
					});
				}
				//Charge les sondages suivants (via ".get()" et non ".ajax")
				if($("#contentPolls").is(":visible") && loadMorePolls==true)
				{
					$("#contentPolls").append("<div class='dashboardLoadingImg'><img src='app/img/loading2.png'></div>");
					$.get("?ctrl=dashboard&action=GetMorePolls&pollsNotVoted=<?= Req::param("pollsNotVoted") ?>&pollsOffsetCpt="+pollsOffsetCpt, function(vuePollsList){
						if(vuePollsList.length==0)  {loadMorePolls=false;}//Passe à false si ya plus rien à charger : évite les requêtes inutiles
						{
							$("#contentPolls").append(vuePollsList);//Affiche les sondages
							$(".vPollsContainer").fadeIn(500);	//"fadeIn()" car masquées par défaut via .vNewsHidden
							mainPageDisplay(false);				//Update les tooltips/lightbox
							initMenuContext();					//Maj les menus contextuels des sondages
							dashboardPollVote();				//Update le "trigger" de vote des sondages
							pollsOffsetCpt++;					//Update le compteur
						}
					});
				}
				//Masque si besoin les icones "loading"
				if($(".dashboardLoadingImg").is(":visible"))  {$(".dashboardLoadingImg").fadeOut(800);}
			}
		},300);
	});
	
	////	Affichage au chargement : nouveaux "Elems" / sondage "Polls" / "News"
	<?php
	if(Req::isParam("pluginPeriod"))							{echo "dashboardOption('Elems');";}
	elseif(stristr($_SERVER["REQUEST_URI"],"dashboardPoll"))	{echo "dashboardOption('Polls');";}
	else														{echo "dashboardOption('News');";}
	?>

	////	Init le trigger de vote des sondages
	dashboardPollVote();
});

/*******************************************************************************************
 *	AFFICHE UNE OPTION DU DASHBOARD ("menuName"="News"/"Polls"/"Elems")
 *******************************************************************************************/
function dashboardOption(menuName)
{
	//Déselectionne les menus principaux, puis sélectionne l'option demandé (via "sLinkSelect")
	$("[id^=mainMenu]").removeClass("sLinkSelect");
	$("#mainMenu"+menuName).addClass("sLinkSelect");
	//Déselectionne les menus contextuels et blocks de contenu principaux, puis sélectionne l'option demandé (via "sLinkSelect")
	$("[id^=modMenu], #pageCenterContent>div:not(#dashboardMenu)").hide();
	$("#modMenu"+menuName).fadeIn();
	$("#content"+menuName).show();
	//Rsponsive : affiche le bouton d'ajout d'element uniquement pour les actualités
	(isMobile() && menuName=="News") ? $("#respAddButton").show() : $("#respAddButton").hide();
}

/*******************************************************************************************
 *	VOTE D'UN SONDAGE
 *******************************************************************************************/
function dashboardPollVote()
{
	////	VOTE UN SONDAGE
	$("form[id^=pollForm]").submit(function(event){
		//Pas de soumission par défaut du formulaire
		event.preventDefault();
		//Controle et Soumission Ajax du formulaire
		if($("#"+this.id+" input[name='pollResponse[]']:checked").length==0)  {notify("<?= Txt::trad("DASHBOARD_voteNoResponse") ?>");}
		else{
			$.ajax({url:"?ctrl=dashboard&action=pollVote",data:$(this).serialize(),dataType:"json"}).done(function(result){
				//Affiche le résultat du sondage (remplace le formulaire principal, et si besoin le formulaire "newsDisplay")
				if(result.vuePollResult.length>0){
					$(".vPollContent"+result._idPoll).html(result.vuePollResult);
					mainPageDisplay(false);//Update les tooltips/lightbox
				}
			});
		}
	});
}
</script>

<style>
/*Menu principal/contextuel de chaque option*/
#dashboardMenu							{display:table; position:relative; width:100%; height:40px; margin-bottom:10px;}
#dashboardMenu>a						{display:table-cell; width:<?= $isPolls==true?33:50 ?>%; text-align:center; vertical-align:middle; font-size:1.1em;}/*label du menu*/
#dashboardMenu hr						{display:inline; position:absolute; bottom:0px; left:0px; width:<?= $isPolls==true?33:50 ?>%; height:6px; margin:0px; background:tomato; transition:0.1s ease-in-out;}/*Surlignage des options du module*/
.menuCircleBis							{margin:0px 0px 0px 5px; width:18px; height:18px; line-height:18px; font-size:11px!important; background-color:#c40;}/*surcharge*/
#mainMenuNews.sLinkSelect ~ hr			{margin-left:0%;}
#mainMenuPolls.sLinkSelect ~ hr			{margin-left:33%;}
#mainMenuElems.sLinkSelect ~ hr			{margin-left:<?= $isPolls==true?66:50 ?>%;}
.dashboardLoadingImg					{text-align:center; padding:20px;}
[id^=modMenu], #pageCenterContent>div:not(#dashboardMenu)	{display:none;}/*masque par défaut tous les menus et contenus du module, sauf le menu principal*/
#contentNews>div, #contentPolls>div, #contentElems>div		{width:100%;}

/*Affichage des News*/
.vNewsHidden							{display:none;}/*cf. infinite scroll*/
.vNewsContainer.objContainer			{height:auto!important; padding:15px; padding-right:35px;}/*surcharge de .objContainer : height adapté au contenu*/
.vNewsDescription						{font-weight:normal;}
.vNewsDescription a						{text-decoration:underline;}/*idem editeur*/
.vNewsDetail							{margin-top:20px; text-align:center;}
.vNewsDetail span, .vNewsDetail .menuAttachedFile	{display:inline-block; margin-right:20px;}
.vNewsTopNews							{color:#c40;}
.calEventProposition					{margin:10px;}/*propositions d'événements*/

/*New par défaut : "INSTALL_dataDashboardNews"*/
.vNewsDescription h3					{text-align:center;}
.vNewsDescription h3, .vNewsDescription h4:nth-last-child(3), .vNewsDescription h4:nth-last-child(1)  {margin-bottom:30px;}/*première ligne + avant-avant dernière ligne + dernière ligne*/
.vNewsDescription h4>img				{max-width:30px!important; margin-right:8px;}
/*RESPONSIVE*/
@media screen and (max-width:1023px){
	.vNewsDescription h3	{font-size:1.4em;}
}

/*Affichage des sondages*/
#modMenuNews hr							{margin:20px 0px;}/*Menu contextuel de gauche*/
#modMenuNews .vPollsTitle				{margin:20px 0px; font-size:1.05em;}/*Menu contextuel de gauche*/
#modMenuNews .vPollsContainer ul		{padding-left:10px!important;}/*surcharge*/
.vPollsHidden							{display:none;}/*cf. infinite scroll*/
.vPollsContainer.objContainer			{height:auto!important; padding:15px; padding-right:35px;}/*surcharge de .objContainer : height adapté au contenu*/
.vPollsTitle,.vPollsDescription			{text-align:center; margin:15px 0px;}/*Titre et Description*/
#contentPolls .vPollsTitle				{font-size:1.25em;}/*Titre de l'affichage principal (pas avec les news)*/
.vPollsDescription img					{max-height:400px;}/*Affichage des images dans la description*/
.vPollsContainer ul li					{list-style:none; margin-bottom:20px;}
.vPollsDetails							{margin-top:20px; text-align:center;}
div.vPollsDescription:empty, .vPollsDetails:empty		{display:none;}/*masque les divs non remplis*/
.vPollsDetails span, .vPollsDetails .menuAttachedFile	{display:inline-block; margin-right:20px;}
.vPollsResponseFile						{margin-top:8px;}/*cf. MdlDashboardPoll*/
.vPollsResponseFile img					{max-width:300px; max-height:120px; vertical-align:middle;}/*idem*/
.vPollResponseInput .vPollsResponseFile	{margin-left:25px;}
.vPollsContainer .submitButtonMain		{padding-top:10px;}/*surcharge*/
.vPollsContainer button					{width:220px!important;}/*surcharge*/
.vPollsResultBarContainer				{width:90%; margin-top:8px; padding:2px; border-radius:5px; background-color:#fafafa; box-shadow:0px 1px 5px #ddd inset;}
.vPollsResultBar						{display:inline-block; min-width:35px; height:28px; line-height:28px; color:#555; text-align:right; padding-right:5px; border-radius:5px; box-shadow:0px 1px 3px #bbb;}
.vPollsResultBar0						{background:linear-gradient(to top, #e5e5e5, #fcfcfc, #ececec);}
.vPollsResultBar50						{background:linear-gradient(to top, #fd9215, #ffc55b, #fecf15);}
.vPollsResultBar100						{background:linear-gradient(to top, #86bf24, #98d829, #99e21b);}
/*RESPONSIVE*/
@media screen and (max-width:1023px){
	.vPollsContainer ul		{padding-left:0px!important;}
}

/*Affichage des elements (nouveaux/courants)*/
#modMenuElems>div						{padding:5px;}
#contentElems .menuLine					{padding:3px;}
#contentElems .menuIcon					{width:15px;}
#contentElems .menuIcon img				{max-width:15px;}
.vContentElemsModuleLabel				{text-align:center;}
.vContentElemsModuleLabel img			{max-height:28px; margin-right:8px;}
</style>

<div id="pageCenter">
	<div id="pageModuleMenu">
		<div id="pageModMenu" class="miscContainer">

			<?php
			////	MENU CONTEXTUEL DES "NEWS"
			////
			echo "<div id='modMenuNews'>";
				//// Ajoute une news / Affiche les news "Offline"  /  Tri des news
				if(MdlDashboardNews::addRight())	{echo "<div class='menuLine sLink' onclick=\"lightboxOpen('".MdlDashboardNews::getUrlNew()."');\"><div class='menuIcon'><img src='app/img/plus.png'></div><div>".Txt::trad("DASHBOARD_addNews")."</div></div>";}
				if(!empty($offlineNewsCount))		{echo "<div class='menuLine ".(Req::param("offlineNews")==1?'sLinkSelect':'sLink')."' onclick=\"redir('?ctrl=dashboard&offlineNews=".(Req::param("offlineNews")==1?0:1)."')\"><div class='menuIcon'><img src='app/img/dashboard/newsOffline.png'></div><div>".Txt::trad("DASHBOARD_newsOffline").(!empty($offlineNewsCount)?"<div class='menuCircle menuCircleBis'>".$offlineNewsCount."</div>":null)."</div></div>";}
				echo MdlDashboardNews::menuSort();
				//// Affichage des sondages (option "newsDisplay")
				if(Req::isMobile()==false && !empty($pollsListNewsDisplay)){
					foreach($pollsListNewsDisplay as $tmpKey=>$tmpPoll)  {echo "<hr><div class='vPollsContainer'><div class='vPollsTitle' title=\"".Txt::tooltip($tmpPoll->description)."\">".$tmpPoll->title."</div><div class=\"vPollContent".$tmpPoll->_id."\">".$tmpPoll->vuePollForm(true)."</div></div>";}
				}
			echo "</div>";

			////	MENU CONTEXTUEL DES SONDAGES
			////
			if($isPolls==true)
			{
				echo "<div id='modMenuPolls'>";
					//Ajoute un sondage  /  Voir uniquement les sondages à voter  /  Tri des sondages 
					if(MdlDashboardPoll::addRight())	{echo "<div class='menuLine sLink' onclick=\"lightboxOpen('".MdlDashboardPoll::getUrlNew()."');\"><div class='menuIcon'><img src='app/img/plus.png'></div><div>".Txt::trad("DASHBOARD_addPoll")."</div></div>";}
					if(!empty($pollsNotVotedNb))		{echo "<div class='menuLine ".(Req::param("pollsNotVoted")==1?'sLinkSelect':'sLink')."' onclick=\"redir('?ctrl=dashboard&dashboardPoll=true&pollsNotVoted=".(Req::param("pollsNotVoted")==1?0:1)."')\" title=\"".Txt::trad("DASHBOARD_pollsNotVotedInfo")."\"><div class='menuIcon'><img src='app/img/check.png'></div><div>".Txt::trad("DASHBOARD_pollsNotVoted")." <div class='menuCircle menuCircleBis'>".$pollsNotVotedNb."</div></div></div>";}
					echo MdlDashboardPoll::menuSort(null,"&dashboardPoll=true");
				echo "</div>";
			}

			////	MENU CONTEXTUEL DES NOUVEAUX ELEMENTS (plugins: affichage jour/semaine/mois)
			////
			if($showNewElems==true)
			{
				echo "<div id='modMenuElems'><div>".Txt::trad("DASHBOARD_plugins")." :</div>";
				foreach($pluginPeriodOptions as $periodValue=>$tmpPeriod)
				{
					$selectedPeriod=($pluginPeriod==$periodValue)  ?  "checked='checked'"  :  null;
					$titlePeriod=($periodValue=="day")  ?  Txt::trad("today")  :  Txt::trad("DASHBOARD_pluginsInfo2")." ".date("d/m/Y",$tmpPeriod["timeBegin"])." ".Txt::trad("and")." ".date("d/m/Y",$tmpPeriod["timeEnd"]);
					echo "<div title=\"".Txt::trad("DASHBOARD_pluginsInfo")." ".$titlePeriod."\">
							<input name='pluginPeriod' type='radio' id='radioPeriod".$periodValue."' ".$selectedPeriod." onclick=\"redir('?ctrl=dashboard&pluginPeriod=".$periodValue."')\">
							<label for='radioPeriod".$periodValue."'>".Txt::trad("DASHBOARD_plugins_".$periodValue)."</label>
						  </div>";
				}
				echo "</div>";
			}
			?>
		</div>
	</div>

	<div id="pageCenterContent">
		<?php
		////	MENU TOP DU DASHBOARD => POUR POUVOIR AFFICHER LES SONDAGES ET NOUVEAUX ELEMENTS
		////
		if($isPolls==true || $showNewElems==true)
		{
			echo "<div id='dashboardMenu' class='miscContainer'>";
				echo "<a href=\"javascript:dashboardOption('News')\"  id='mainMenuNews'>".Txt::trad("DASHBOARD_menuNews")."</a>";
				if($isPolls==true)  {echo "<a href=\"javascript:dashboardOption('Polls')\" id='mainMenuPolls'>".Txt::trad("DASHBOARD_menuPolls").(!empty($pollsNotVotedNb)?"<div class='menuCircle menuCircleBis' title=\"".Txt::trad("DASHBOARD_pollsNotVoted")." : ".$pollsNotVotedNb."\">".$pollsNotVotedNb."</div>":null)."</a>";}
				echo "<a href=\"javascript:dashboardOption('Elems')\" id='mainMenuElems'>".Txt::trad("DASHBOARD_menuElems").(!empty($pluginsList)?"<div class='menuCircle menuCircleBis'>".count($pluginsList)."</div>":null)."</a>";
				echo "<hr>";//Barre de surlignage (après les menus!)
			echo "</div>";
		}
		
		////	LISTE DES ACTUALITES
		////
		echo "<div id='contentNews'>";
			////	Premières news avant de "infinite scroll"  (Ou sinon "Aucun contenu")
			if(!empty($vueNewsListInitial))  {echo $vueNewsListInitial;}
			else{
				$addElement=(MdlDashboardNews::addRight())  ?  "<div class='sLink' onclick=\"lightboxOpen('".MdlDashboardNews::getUrlNew()."')\"><img src='app/img/plus.png'> ".Txt::trad("DASHBOARD_addNews")."</div>"  :  null;
				echo "<div class='emptyContainer'>".Txt::trad("DASHBOARD_noNews").$addElement."</div>";
			}
		echo "</div>";
		
		////	LISTE DES SONDAGES
		////
		if($isPolls==true)
		{
			echo "<div id='contentPolls'>";
			////	Premiers sondages avant "infinite scroll"  (Ou sinon "Aucun contenu")
			if(!empty($vuePollsListInitial))	{echo $vuePollsListInitial;}
			else{
				$addElement=(MdlDashboardPoll::addRight())  ?  "<div class='sLink' onclick=\"lightboxOpen('".MdlDashboardPoll::getUrlNew()."')\"><img src='app/img/plus.png'> ".Txt::trad("DASHBOARD_addPoll")."</div>"  :  null;
				echo "<div class='emptyContainer'>".Txt::trad("DASHBOARD_noPoll").$addElement."</div>";
			}
			echo "</div>";
		}
		
		////	LISTE DES NOUVEAUX ELEMENTS (plugins)
		////
		if($showNewElems==true)
		{
			echo "<div id='contentElems'><div class='miscContainer'>";
			////	NOUVEAUX ELEMENTS DE CHAQUE MODULES (cf. plugins)
			foreach($pluginsList as $tmpObj)
			{
				//// Affiche le libellé du module?
				if(empty($tmpModuleName) || $tmpModuleName!=$tmpObj::moduleName){
					echo "<div class='vContentElemsModuleLabel'><hr><img src='app/img/".$tmpObj::moduleName."/icon.png'>".Txt::trad(strtoupper($tmpObj::moduleName)."_headerModuleName")."</div>";
					$tmpModuleName=$tmpObj::moduleName;
				}
				//// Plugin Spécifique (ex: proposition d'evt)  ||  Plugin lambda
				if(isset($tmpObj->pluginSpecificMenu))  {echo $tmpObj->pluginSpecificMenu;}
				else
				{
					//Date de création et Auteur
					if(isset($tmpObj->dateCrea))  {$tmpObj->pluginTooltip.="<hr>".Txt::trad("creation")." : ".Txt::dateLabel($tmpObj->dateCrea,"date")."<hr>".$tmpObj->autorLabel(true,true);}
					//Tooltip de l'icone : ajoute si besoin "Afficher l'element dans son dossier"
					$tmpObj->pluginTooltipIcon=($tmpObj::isInArbo())  ?  Txt::trad("DASHBOARD_pluginsTooltipRedir")  :  $tmpObj->pluginTooltip;
					//Affiche le plugin
					echo "<div class='menuLine sLink objHover'>
							<div title=\"".Txt::tooltip($tmpObj->pluginTooltipIcon)."\" onclick=\"".$tmpObj->pluginJsIcon."\" class='menuIcon'><img src='app/img/".$tmpObj->pluginIcon."'></div>
							<div title=\"".Txt::tooltip($tmpObj->pluginTooltip)."\" onclick=\"".$tmpObj->pluginJsLabel."\">".$tmpObj->pluginLabel."</div>
						  </div>";
				}
			}
			////"Aucune nouveauté sur cette période"
			if(empty($pluginsList))  {echo "<div class='emptyContainer'>".Txt::trad("DASHBOARD_pluginEmpty")."</div>";}
			echo "</div></div>";
		}
		?>
	</div>
</div>