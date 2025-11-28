<script>
/********************************************************************************************************
 *	INIT
 *******************************************************************************************/
ready(function(){
	////	"Infinite scroll" : Affichage progressif des news et sondages
	$(window).on("scroll",function(){
		//Timeout pour ne pas charger durant le scroll
		if(typeof scrollTimeout!="undefined")  {clearTimeout(scrollTimeout);}//Un seul timeout
		scrollTimeout=setTimeout(function(){
			//Lance l'infinite scroll quand on arrive en fin de page  (hauteur de page < (scrollTop + hauteur de fenêtre + 20px))
			if($(document).height() < ($(window).scrollTop() + window.top.windowHeight + 20)){
				//Init le chargement
				if(typeof loadMoreNews==="undefined"){
					loadMoreNews=loadMorePolls=true;//Marqueur pour savoir si on doit charger des News/Polls en fin de page
					newsOffset=pollsOffset=1;	//Compteur des blocs de news/polls déjà affichés (offset). Commence à "1" car le bloc "0" est affiché au chargement de page
				}
				//Charge les news suivantes (via ".get()" et non ".ajax")
				if($("#contentNews").isDisplayed() && loadMoreNews==true){
					$("#contentNews").append("<div class='infiniteScrollLoading'><img src='app/img/loading.png'></div>");
					$.get("?ctrl=dashboard&action=GetMoreNews&newsOffset="+newsOffset, function(vueNewsList){
						if(vueNewsList.length==0)  {loadMoreNews=false;}//Passe à false si ya plus rien à charger : évite les requêtes inutiles
						else{
							$("#contentNews").append(vueNewsList);	//Affiche les news
							$(".vNewsContainer").fadeIn(500);		//"fadeIn()" car masquées par défaut via .infiniteScrollHidden
							menuContext();							//Update les menus contextuels
							mainTriggers();							//Update les tooltips
							newsOffset++;							//Update le compteur
						}
					});
				}
				//Charge les sondages suivants (via ".get()" et non ".ajax")
				if($("#contentPolls").isDisplayed() && loadMorePolls==true){
					$("#contentPolls").append("<div class='infiniteScrollLoading'><img src='app/img/loading.png'></div>");
					$.get("?ctrl=dashboard&action=GetMorePolls&pollsNotVoted=<?= Req::param("pollsNotVoted") ?>&pollsOffset="+pollsOffset, function(vuePollsList){
						if(vuePollsList.length==0)  {loadMorePolls=false;}	//Passe à false si ya plus rien à charger : évite les requêtes inutiles
						else{
							$("#contentPolls").append(vuePollsList);		//Affiche les sondages
							$(".vPollsContainer").fadeIn(500);				//"fadeIn()" car masquées par défaut via .infiniteScrollHidden
							menuContext();									//Update les menus contextuels
							mainTriggers();									//Update les tooltips
							dashboardPollVote();							//Update le "trigger" de vote des sondages
							pollsOffset++;									//Update le compteur
						}
					});
				}
				//Masque si besoin les icones "loading"
				if($(".infiniteScrollLoading").isDisplayed())  {$(".infiniteScrollLoading").fadeOut(800);}
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

/********************************************************************************************************
 *	MENU ACTUALITÉS / SONDAGES / NOUVEAUTÉS
 *******************************************************************************************/
function dashboardOption(menuName)
{
	//Déselectionne tous les menus -> puis sélectionne le menu demandé
	$("#tabMenus a").removeClass("linkSelect");
	$("#tabMenu"+menuName).addClass("linkSelect");
	//Masque les menus contextuels et les contenus principaux -> puis sélectionne le menu contextuel et le contenu demandé
	$("div[id^=modMenu], #pageContent>div[id^=content]").hide();
	$("#modMenu"+menuName).fadeIn();
	$("#content"+menuName).show();
	//Sourligne le menu demandé
	underMenusLeft="0px";
	if(menuName=="Polls")		{underMenusLeft="33%";}
	else if(menuName=="Elems")	{underMenusLeft="<?= $isPolls==true?'66%':'50%' ?>";}
	$("#underMenus").css("margin-left",underMenusLeft);
}

/********************************************************************************************************
 *	VOTE D'UN SONDAGE
 *******************************************************************************************/
function dashboardPollVote()
{
	////	VOTE UN SONDAGE
	$("form[id^=pollForm]").on("submit",function(event){
		event.preventDefault();
		//// Controle et Soumission Ajax du formulaire
		if($("#"+this.id+" input[name='pollResponse[]']:checked").length==0)
			{notify("<?= Txt::trad("DASHBOARD_voteNoResponse") ?>");}
		//// Valide le vote puis affiche le résultat du sondage
		else{
			$.ajax({url:"?ctrl=dashboard&action=pollVote", data:$(this).serialize(), method:"POST", dataType:"json"}).done(function(result){
				if(result.vuePollResult.length>0){
					$(".vPollContent"+result._idPoll).html(result.vuePollResult);	//Remplace le form. par le résultat du sondage  (+ au besoin le "newsDisplay")
					mainTriggers();													//Update les tooltips
				}
			});
		}
	});
}
</script>


<style>
/*Menu Actualités / Sondages / Nouveautés*/
.pathMenu.miscContainer					{width:99.5%; padding:0px;}/*surcharge*/
#tabMenus								{display:table; width:100%; padding:0px; padding-top:10px; table-layout:fixed;}/*fixed: même width pour chaque cells*/
#tabMenus a								{display:table-cell; text-align:center; font-size:1.05rem;}/*label du menu*/
#tabMenus .circleNb						{margin-left:5px; font-size:0.9rem;}
#underMenus								{display:inline-block; width:<?= $isPolls==true?'33.33%':'50%' ?>; height:5px; margin-bottom:-8px; padding:0px; background:tomato; transition:0.1s ease-in-out;}/*Surligne les options du module*/
#contentNews,#contentPolls,#contentElems{width:100%; display:none;}/*Masque par défaut les contenus principaux*/
/*AFFICHAGE SMARTPHONE + TABLET*/
@media screen and (max-width:1200px){
	.pathMenu.miscContainer				{width:98%;}/*surcharge : idem app.css*/
	#tabMenus							{padding:10px;}
	#underMenus							{display:none;}
}

/*Infinites scrolls : News / Sondages*/
.infiniteScrollHidden					{display:none;}
.infiniteScrollLoading					{text-align:center; padding:10px;}

/*News*/
.vNewsContainer.objContainer			{height:auto!important; padding:15px; padding-right:35px;}		/*surcharge de .objContainer : height adapté au contenu*/
.vNewsDescription						{font-weight:normal;}
.vNewsDescription a						{text-decoration:underline;}									/*idem editeur*/
.vNewsDetail							{margin-top:20px; margin-bottom:10px; text-align:center;}		/*Détails centrés*/
.vNewsDetail>div						{display:inline-block; margin-inline:15px; line-height:20px;}	/*"line-height" idem à la taille des img ci-dessous*/
.vNewsDetail img						{max-height:20px;}												/*Icones des details (à la une, etc)*/
.vNewsTopNews							{color:#a40;}													/*texte "Actualité à la une"*/
/*News par défaut (cf. "INSTALL_dataDashboardNews")*/
.vNewsDescription h3					{text-align:center;}							
.vNewsDescription h4 img				{max-width:33px!important; margin-left:10px; margin-right:10px;}/*cf. width réel des "iconSmall.png"*/
.vNewsDescription h4:last-child			{margin-bottom:20px;}
/*AFFICHAGE SMARTPHONE + TABLET*/
@media screen and (max-width:1200px){
	.vNewsDescription h3				{font-size:1.3rem;}									/*New par défaut*/
	.vNewsDescription h4				{font-size:1.05rem; clear:left;}					/*Idem. "clear:left" pour aligner avec l'image float : tester width 500px*/
	.vNewsDescription h4>img			{float:left; margin-left:0px; margin-bottom:30px;}	/*Idem*/
}

/*Sondages*/
#moduleMenu .vPollsTitle				{margin:20px 0px; font-size:1.05rem;}
#moduleMenu .vPollsContainer ul			{padding-left:10px!important;}
#moduleMenu .submitButtonMain			{margin-top:10px;}
.vPollsContainer.objContainer			{height:auto!important; padding:15px; padding-right:35px;}/*surcharge : height adapté au contenu*/
.vPollsTitle,.vPollsDescription			{text-align:center; margin:15px 0px;}/*Titre et Description*/
#contentPolls .vPollsTitle				{font-size:1.25rem;}/*Titre de l'affichage principal (pas avec les news)*/
.vPollsDescription img					{max-height:400px;}/*Affichage des images dans la description*/
.vPollsContainer ul li					{list-style:none; margin-bottom:20px;}
.vPollsDetails							{margin-top:20px; text-align:center;}
.vPollsDetails>div						{display:inline-block; margin:0px 10px;}
div.vPollsDescription:empty, .vPollsDetails:empty	{display:none;}/*masque les divs non remplis*/
.vPollsResponseFile						{margin-top:8px;}/*cf. MdlDashboardPoll*/
.vPollsResponseFile img					{max-width:300px; max-height:120px; vertical-align:middle;}/*idem*/
.vPollResponseInput .vPollsResponseFile	{margin-left:25px;}
.vPollsContainer button					{width:240px!important;}/*surcharge*/
.vPollsResultBarContainer				{width:90%; margin-top:8px; padding:2px; border-radius:5px; background:#fafafa; box-shadow:0px 1px 5px #ddd inset;}
.vPollsResultBar						{display:inline-block; min-width:35px; height:28px; line-height:28px; color:#555; text-align:right; padding-right:5px; border-radius:5px; box-shadow:0px 1px 3px #bbb;}
.vPollsResultBar0						{background:linear-gradient(to top, #e5e5e5, #fcfcfc, #ececec);}
.vPollsResultBar50						{background:linear-gradient(to top, #fd9215, #ffc55b, #fecf15);}
.vPollsResultBar100						{background:linear-gradient(to top, #86bf24, #98d829, #99e21b);}
/*AFFICHAGE SMARTPHONE + TABLET*/
@media screen and (max-width:1200px){
	.vPollsContainer ul		{padding-left:0px!important;}
	.vPollsDetails>div		{display:block; margin:8px;}
}

/*Nouveaux elements*/
#modMenuElems>div						{padding:5px;}
#contentElems .menuLine					{padding:3px;}
#contentElems .menuIcon					{width:15px;}
#contentElems .menuIcon img				{max-width:15px;}
.vContentElemsModuleLabel				{text-align:center;}
.vContentElemsModuleLabel img			{max-height:28px; margin-right:8px;}
</style>


<div id="pageCenter">
	<div id="moduleMenu">
		<div class="miscContainer">
			<?php
			////	MENU CONTEXT DES ACTUALITÉS
			echo "<div id='modMenuNews'>";
				//// Ajoute une news / Affiche les news "Offline"  /  Tri des news
				if(MdlDashboardNews::addRight())	{echo '<div class="menuLine" onclick="lightboxOpen(\''.MdlDashboardNews::getUrlNew().'\');"><div class="menuIcon"><img src="app/img/plus.png"></div><div>'.Txt::trad("DASHBOARD_addNews").'</div></div>';}
				echo '<div class="menuLine '.(!empty($_SESSION["offlineNews"])?'optionSelect':'option').'" onclick="redir(\'?ctrl=dashboard&offlineNews='.(empty($_SESSION["offlineNews"])?'true':'false').'\')" title="'.$offlineNewsNb." ".Txt::trad("DASHBOARD_offlineNewsNb").'"><div class="menuIcon"><img src="app/img/dashboard/newsOffline.png"></div><div>'.Txt::trad("DASHBOARD_offlineNews").'</div></div>'.
					  '<hr>'.MdlDashboardNews::menuSort();
				//// Affichage des sondages (option "newsDisplay")
				if(Req::isMobile()==false && !empty($pollsListNewsDisplay)){
					foreach($pollsListNewsDisplay as $tmpKey=>$tmpPoll)
						{echo '<hr><div class="vPollsContainer"><div class="vPollsTitle" '.Txt::tooltip($tmpPoll->description).'>'.$tmpPoll->title.'</div><div class="vPollContent'.$tmpPoll->_id.'">'.$tmpPoll->vuePollForm(true).'</div></div>';}
				}
			echo "</div>";

			////	MENU CONTEXT DES SONDAGES
			if($isPolls==true){
				echo "<div id='modMenuPolls'>";
					//Ajoute un sondage  /  Voir uniquement les sondages à voter  /  Tri des sondages 
					if(MdlDashboardPoll::addRight())	{echo '<div class="menuLine" onclick="lightboxOpen(\''.MdlDashboardPoll::getUrlNew().'\');"><div class="menuIcon"><img src="app/img/plus.png"></div><div>'.Txt::trad("DASHBOARD_addPoll").'</div></div>';}
					if(!empty($pollsVotedNb))			{echo '<div class="menuLine '.($_SESSION["pollsVotedShow"]==true?'linkSelect':null).'" onclick="redir(\'?ctrl=dashboard&dashboardPoll=true&pollsVotedShow='.($_SESSION["pollsVotedShow"]==true?'false':'true').'\')" '.Txt::tooltip($pollsVotedNb." ".Txt::trad("DASHBOARD_pollsVotedNb")).'><div class="menuIcon"><img src="app/img/check.png"></div><div>'.Txt::trad("DASHBOARD_pollsVoted").($_SESSION["pollsVotedShow"]==true?'&nbsp; <img src="app/img/check.png">':null).'</div></div>';}
					echo MdlDashboardPoll::menuSort("&dashboardPoll=true");
				echo "</div>";
			}

			////	MENU CONTEXT DES NOUVEAUTÉS
			if($showNewElems==true){
				echo "<div id='modMenuElems'><div>".Txt::trad("DASHBOARD_plugins")." :</div>";
				foreach($pluginPeriodOptions as $periodValue=>$tmpPeriod)
				{
					$selectedPeriod=($pluginPeriod==$periodValue)  ?  "checked='checked'"  :  null;
					$titlePeriod=($periodValue=="day")  ?  Txt::trad("today")  :  Txt::trad("DASHBOARD_pluginsTooltip2")." ".date("d/m/Y",$tmpPeriod["timeBegin"])." ".Txt::trad("and")." ".date("d/m/Y",$tmpPeriod["timeEnd"]);
					echo "<div ".Txt::tooltip(Txt::trad("DASHBOARD_pluginsTooltip")." ".$titlePeriod).">
							<input name='pluginPeriod' type='radio' id='radioPeriod".$periodValue."' ".$selectedPeriod." onclick=\"redir('?ctrl=dashboard&pluginPeriod=".$periodValue."')\">
							<label for='radioPeriod".$periodValue."'>".Txt::trad("DASHBOARD_plugins_".$periodValue)."</label>
						  </div>";
				}
				echo "</div>";
			}
			?>
		</div>
	</div>

	<div id="pageContent">
		<?php
		////	MENU DES ACTUALITÉS / SONDAGES / NOUVEAUTÉS
		if($isPolls==true || $showNewElems==true){
			echo '<div class="pathMenu miscContainer">
					<div id="tabMenus">
						<a onclick="dashboardOption(\'News\')" id="tabMenuNews">'.Txt::trad("DASHBOARD_menuNews").'</a>
						'.($isPolls==true ?  '<a onclick="dashboardOption(\'Polls\')" id="tabMenuPolls">'.Txt::trad("DASHBOARD_menuPolls").(!empty($pollsListNewsDisplay)?'<span class="circleNb" '.Txt::tooltip(Txt::trad("DASHBOARD_pollsNotVoted").' : '.count($pollsListNewsDisplay)).'>'.count($pollsListNewsDisplay).'</span>':null).'</a>'  :  null).'
						<a onclick="dashboardOption(\'Elems\')" id="tabMenuElems">'.Txt::trad("DASHBOARD_menuElems").(!empty($pluginsList)?'<span class="circleNb">'.count($pluginsList).'</span>':null).'</a>
					</div>
					<div id="underMenus">&nbsp;</div>
				</div>';
		}
		
		////	LISTE DES ACTUALITÉS
		echo "<div id='contentNews'>";
			//// Premières news avant de "infinite scroll"
			echo $vueNewsListInitial;
			//// Aucune news
			if(empty($vueNewsListInitial)){
				$addElement=(MdlDashboardNews::addRight())  ?  "<div onclick=\"lightboxOpen('".MdlDashboardNews::getUrlNew()."')\"><img src='app/img/plus.png'> ".Txt::trad("DASHBOARD_addNews")."</div>"  :  null;
				echo '<div class="miscContainer emptyContainer">'.Txt::trad("DASHBOARD_noNews").$addElement.'</div>';
			}
		echo "</div>";
		
		////	LISTE DES SONDAGES
		if($isPolls==true){
			echo "<div id='contentPolls'>";
			//// Premiers sondages avant "infinite scroll"
			echo $vuePollsListInitial;
			//// Aucun sondage
			if(empty($vuePollsListInitial)){
				$addElement=(MdlDashboardPoll::addRight())  ?  "<div onclick=\"lightboxOpen('".MdlDashboardPoll::getUrlNew()."')\"><img src='app/img/plus.png'> ".Txt::trad("DASHBOARD_addPoll")."</div>"  :  null;
				echo '<div class="miscContainer emptyContainer">'.Txt::trad("DASHBOARD_noPoll").$addElement.'</div>';
			}
			echo "</div>";
		}
		
		////	LISTE DES NOUVEAUTÉS (PLUGINS)
		if($showNewElems==true){
			echo '<div id="contentElems"><div class="miscContainer">';
			foreach($pluginsList as $tmpObj){
				//// Libellé du module (séparateur?)
				if(empty($tmpModuleName) || $tmpModuleName!=$tmpObj::moduleName){
					if(!empty($tmpModuleName))  {echo "<hr>";}	//Affiche un séparateur
					$tmpModuleName=$tmpObj::moduleName;			//Enregistre le nom du module courant
					echo '<div class="vContentElemsModuleLabel"><img src="app/img/'.$tmpModuleName.'/icon.png">'.Txt::trad(strtoupper($tmpModuleName).'_MODULE_NAME').'</div>';
				}
				//// Affiche le plugin
				if(isset($tmpObj->dateCrea))  {$tmpObj->pluginTooltip.="<hr>".Txt::trad("createdBy")." ".$tmpObj->autorDate();}
				$tmpObj->pluginTooltipIcon=($tmpObj::isInArbo())  ?  Txt::trad("DASHBOARD_pluginsTooltipRedir")."<hr>".$tmpObj->pluginTooltip  :  $tmpObj->pluginTooltip;//Ajoute si besoin "Afficher l'element dans son dossier"
				echo '<div class="menuLine lineHover">
						<div onclick="'.$tmpObj->pluginJsIcon.'" '.Txt::tooltip($tmpObj->pluginTooltipIcon).' class="menuIcon"><img src="app/img/'.$tmpObj->pluginIcon.'"></div>
						<div onclick="'.$tmpObj->pluginJsLabel.'" '.Txt::tooltip($tmpObj->pluginTooltip).'>'.$tmpObj->pluginLabel.'</div>
					  </div>';
			}
			//// "Aucune nouveauté sur cette période"
			if(empty($pluginsList))  {echo '<div class="emptyContainer">'.Txt::trad("DASHBOARD_pluginEmpty").'</div>';}
			echo "</div></div>";
		}
		?>
	</div>
</div>