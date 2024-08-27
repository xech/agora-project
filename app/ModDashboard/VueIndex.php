<script>
/*******************************************************************************************
 *	INIT
 *******************************************************************************************/
$(function(){
	////	"Infinite scroll" : Affichage progressif des news et sondages
	$(window).scroll(function(){
		//Timeout pour ne pas charger durant le scroll
		if(typeof scrollTimeout!="undefined")  {clearTimeout(scrollTimeout);}//Pas de cumul de Timeout !
		scrollTimeout=setTimeout(function(){
			//Lance l'infinite scroll quand on arrive en fin de page  (hauteur de page < (scrollTop + hauteur de fenêtre + 20px))
			if($(document).height() < ($(window).scrollTop() + $(window).height() + 20))
			{
				//Init le chargement
				if(typeof loadMoreNews==="undefined"){
					loadMoreNews=loadMorePolls=true;//Marqueur pour savoir si on doit charger des News/Polls en fin de page
					newsOffset=pollsOffset=1;	//Compteur des blocs de news/polls déjà affichés (offset). Commence à "1" car le bloc "0" est affiché au chargement de page
				}
				//Charge les news suivantes (via ".get()" et non ".ajax")
				if($("#contentNews").isVisible() && loadMoreNews==true)
				{
					$("#contentNews").append("<div class='infiniteScrollLoading'><img src='app/img/loading.png'></div>");
					$.get("?ctrl=dashboard&action=GetMoreNews&newsOffset="+newsOffset, function(vueNewsList){
						if(vueNewsList.length==0)  {loadMoreNews=false;}//Passe à false si ya plus rien à charger : évite les requêtes inutiles
						else{
							$("#contentNews").append(vueNewsList);	//Affiche les news
							$(".vNewsContainer").fadeIn(500);	//"fadeIn()" car masquées par défaut via .infiniteScrollHidden
							mainPageDisplay(false);				//Update les tooltips/lightbox
							menuContextInit();					//Maj les menus contextuels des news
							newsOffset++;						//Update le compteur
						}
					});
				}
				//Charge les sondages suivants (via ".get()" et non ".ajax")
				if($("#contentPolls").isVisible() && loadMorePolls==true)
				{
					$("#contentPolls").append("<div class='infiniteScrollLoading'><img src='app/img/loading.png'></div>");
					$.get("?ctrl=dashboard&action=GetMorePolls&pollsNotVoted=<?= Req::param("pollsNotVoted") ?>&pollsOffset="+pollsOffset, function(vuePollsList){
						if(vuePollsList.length==0)  {loadMorePolls=false;}//Passe à false si ya plus rien à charger : évite les requêtes inutiles
						{
							$("#contentPolls").append(vuePollsList);//Affiche les sondages
							$(".vPollsContainer").fadeIn(500);	//"fadeIn()" car masquées par défaut via .infiniteScrollHidden
							mainPageDisplay(false);				//Update les tooltips/lightbox
							menuContextInit();					//Maj les menus contextuels des sondages
							dashboardPollVote();				//Update le "trigger" de vote des sondages
							pollsOffset++;						//Update le compteur
						}
					});
				}
				//Masque si besoin les icones "loading"
				if($(".infiniteScrollLoading").isVisible())  {$(".infiniteScrollLoading").fadeOut(800);}
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
	//Déselectionne les menus principaux, puis sélectionne l'option demandé (via "linkSelect")
	$("[id^=tabMenu]").removeClass("linkSelect");
	$("#tabMenu"+menuName).addClass("linkSelect");
	//Déselectionne les menus contextuels et blocks de contenu principaux, puis sélectionne l'option demandé (via "linkSelect")
	$("[id^=modMenu], #pageCenterContent>div:not(#tabMenus)").hide();
	$("#modMenu"+menuName).fadeIn();
	$("#content"+menuName).show();
	//Rsponsive : affiche le bouton d'ajout d'element uniquement pour les actualités
	(isMobile() && menuName=="News") ? $("#menuMobileAddButton").show() : $("#menuMobileAddButton").hide();
}

/*******************************************************************************************
 *	VOTE D'UN SONDAGE
 *******************************************************************************************/
function dashboardPollVote()
{
	////	VOTE UN SONDAGE
	$("form[id^=pollForm]").submit(function(event){
		//Stop la validation du form
		event.preventDefault();
		//Controle et Soumission Ajax du formulaire
		if($("#"+this.id+" input[name='pollResponse[]']:checked").length==0)  {notify("<?= Txt::trad("DASHBOARD_voteNoResponse") ?>");}
		else{
			//Valide le vote puis affiche le résultat du sondage
			$.ajax({url:"?ctrl=dashboard&action=pollVote",data:$(this).serialize(),dataType:"json"}).done(function(result){
				if(result.vuePollResult.length>0){
					$(".vPollContent"+result._idPoll).html(result.vuePollResult);	//Remplace le formulaire par le résultat du sondage  (+ au besoin le "newsDisplay")
					mainPageDisplay(false);											//Update les tooltips/lightbox de "vuePollResult"
				}
			});
		}
	});
}
</script>


<style>
/*Menu "onglet" et conteneurs principaux : News / Sondages / Nouveautés*/
#tabMenus								{display:table; position:relative; width:100%; height:40px; margin-bottom:10px;}
#tabMenus a								{display:table-cell; width:<?= $isPolls==true?33:50 ?>%; text-align:center; vertical-align:middle; font-size:1.1em;}/*label du menu*/
#tabMenus hr							{display:inline; position:absolute; bottom:0px; left:0px; width:<?= $isPolls==true?33:50 ?>%; height:6px; margin:0px; background:tomato; transition:0.1s ease-in-out;}/*Surlignage des options du module*/
#tabMenus .menuCircle					{margin:0px; margin-left:5px; width:18px; height:18px; line-height:18px; font-size:11px!important;}/*Surcharge sur #tabMenus uniquement!*/
#tabMenuNews.linkSelect ~ hr			{margin-left:0%;}
#tabMenuPolls.linkSelect ~ hr			{margin-left:33%;}
#tabMenuElems.linkSelect ~ hr			{margin-left:<?= $isPolls==true?66:50 ?>%;}
#contentNews,#contentPolls,#contentElems{width:100%; display:none;}/*Masque par défaut les contenus principaux*/
/*MOBILE*/
@media screen and (max-width:1023px){
	#tabMenus.miscContainer	{padding:8px;}/*surcharge*/
}

/*Infinites scrolls : News / Sondages*/
.infiniteScrollHidden					{display:none;}
.infiniteScrollLoading					{text-align:center; padding:10px;}

/*News*/
.vNewsContainer.objContainer			{height:auto!important; padding:15px; padding-right:35px;}	/*surcharge de .objContainer : height adapté au contenu*/
.vNewsDescription						{font-weight:normal;}
.vNewsDescription a						{text-decoration:underline;}								/*idem editeur*/
.vNewsDetail							{margin-top:20px; text-align:center;}						/*Détails centrés*/
.vNewsDetail>div						{display:inline-block; margin:0px 15px; line-height:20px;}	/*"line-height" idem à la taille des img ci-dessous*/
.vNewsDetail img						{max-height:20px;}											/*Icones des details (à la une, etc)*/
.vNewsTopNews							{color:#a40;}												/*texte "Actualité à la une"*/
.calEventProposition					{margin:10px;}												/*propositions d'événements*/
.vNewsDescription h3					{text-align:center;}										/*News par défaut : cf. "INSTALL_dataDashboardNews"*/
.vNewsDescription h4>img				{max-width:30px!important; margin-right:8px;}				/*Idem : images de chaque ligne*/
.vNewsDescription h3, .vNewsDescription h4:nth-last-child(3)	{margin-bottom:25px;}				/*Idem : première ligne + avant-avant dernière ligne + dernière ligne*/
/*MOBILE*/
@media screen and (max-width:1023px){
	.vNewsDescription h3				{font-size:1.5em;}		/*New par défaut*/
	.vNewsDescription h4>img			{margin-bottom:5px;}	/*Idem : images de chaque ligne*/
	.vNewsDetail>div					{margin:5px;}
}

/*Sondages*/
#pageModMenu hr							{margin:20px 0px;}/*Menu context de gauche*/
#pageModMenu .vPollsTitle				{margin:20px 0px; font-size:1.05em;}
#pageModMenu .vPollsContainer ul		{padding-left:10px!important;}
#pageModMenu .submitButtonMain			{margin-top:10px;}
.vPollsContainer.objContainer			{height:auto!important; padding:15px; padding-right:35px;}/*surcharge : height adapté au contenu*/
.vPollsTitle,.vPollsDescription			{text-align:center; margin:15px 0px;}/*Titre et Description*/
#contentPolls .vPollsTitle				{font-size:1.25em;}/*Titre de l'affichage principal (pas avec les news)*/
.vPollsDescription img					{max-height:400px;}/*Affichage des images dans la description*/
.vPollsContainer ul li					{list-style:none; margin-bottom:20px;}
.vPollsDetails							{margin-top:20px; text-align:center;}
.vPollsDetails>div						{display:inline-block; margin:0px 10px;}
div.vPollsDescription:empty, .vPollsDetails:empty	{display:none;}/*masque les divs non remplis*/
.vPollsResponseFile						{margin-top:8px;}/*cf. MdlDashboardPoll*/
.vPollsResponseFile img					{max-width:300px; max-height:120px; vertical-align:middle;}/*idem*/
.vPollResponseInput .vPollsResponseFile	{margin-left:25px;}
.vPollsContainer button					{width:220px!important;}/*surcharge*/
.vPollsResultBarContainer				{width:90%; margin-top:8px; padding:2px; border-radius:5px; background:#fafafa; box-shadow:0px 1px 5px #ddd inset;}
.vPollsResultBar						{display:inline-block; min-width:35px; height:28px; line-height:28px; color:#555; text-align:right; padding-right:5px; border-radius:5px; box-shadow:0px 1px 3px #bbb;}
.vPollsResultBar0						{background:linear-gradient(to top, #e5e5e5, #fcfcfc, #ececec);}
.vPollsResultBar50						{background:linear-gradient(to top, #fd9215, #ffc55b, #fecf15);}
.vPollsResultBar100						{background:linear-gradient(to top, #86bf24, #98d829, #99e21b);}
/*MOBILE*/
@media screen and (max-width:1023px){
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
	<div id="pageModuleMenu">
		<div id="pageModMenu" class="miscContainer">
			<?php
			////	MENU CONTEXTUEL DES "NEWS"
			////
			echo "<div id='modMenuNews'>";
				//// Ajoute une news / Affiche les news "Offline"  /  Tri des news
				if(MdlDashboardNews::addRight())	{echo '<div class="menuLine" onclick="lightboxOpen(\''.MdlDashboardNews::getUrlNew().'\');"><div class="menuIcon"><img src="app/img/plus.png"></div><div>'.Txt::trad("DASHBOARD_addNews").'</div></div>';}
				echo '<div class="menuLine '.($_SESSION["offlineNewsShow"]==true?'linkSelect':null).'" onclick="redir(\'?ctrl=dashboard&offlineNewsShow='.($_SESSION["offlineNewsShow"]==true?'false':'true').'\')" title="'.$offlineNewsNb." ".Txt::trad("DASHBOARD_offlineNewsNb").'"><div class="menuIcon"><img src="app/img/dashboard/newsOffline.png"></div><div>'.Txt::trad("DASHBOARD_offlineNews").($_SESSION["offlineNewsShow"]==true?'&nbsp; <img src="app/img/checkSmall.png">':null).'</div></div>';
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
					if(MdlDashboardPoll::addRight())	{echo '<div class="menuLine" onclick="lightboxOpen(\''.MdlDashboardPoll::getUrlNew().'\');"><div class="menuIcon"><img src="app/img/plus.png"></div><div>'.Txt::trad("DASHBOARD_addPoll").'</div></div>';}
					if(!empty($pollsVotedNb))			{echo '<div class="menuLine '.($_SESSION["pollsVotedShow"]==true?'linkSelect':null).'" onclick="redir(\'?ctrl=dashboard&dashboardPoll=true&pollsVotedShow='.($_SESSION["pollsVotedShow"]==true?'false':'true').'\')" title="'.$pollsVotedNb." ".Txt::trad("DASHBOARD_pollsVotedNb").'"><div class="menuIcon"><img src="app/img/check.png"></div><div>'.Txt::trad("DASHBOARD_pollsVoted").($_SESSION["pollsVotedShow"]==true?'&nbsp; <img src="app/img/checkSmall.png">':null).'</div></div>';}
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
					$titlePeriod=($periodValue=="day")  ?  Txt::trad("today")  :  Txt::trad("DASHBOARD_pluginsTooltip2")." ".date("d/m/Y",$tmpPeriod["timeBegin"])." ".Txt::trad("and")." ".date("d/m/Y",$tmpPeriod["timeEnd"]);
					echo "<div title=\"".Txt::trad("DASHBOARD_pluginsTooltip")." ".$titlePeriod."\">
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
		////	MENU "ONGLET" DU DASHBOARD => SWITCH L'AFFICHAGE DES NEWS / SONDAGES / NOUVEAUTES
		////
		if($isPolls==true || $showNewElems==true)
		{
			echo "<div id='tabMenus' class='miscContainer'>";
				echo "<a onclick=\"dashboardOption('News')\" id='tabMenuNews'>".Txt::trad("DASHBOARD_menuNews")."</a>";
				if($isPolls==true)  {echo "<a onclick=\"dashboardOption('Polls')\" id='tabMenuPolls'>".Txt::trad("DASHBOARD_menuPolls").(!empty($pollsListNewsDisplay)?"<div class='menuCircle' title=\"".Txt::trad("DASHBOARD_pollsNotVoted")." : ".count($pollsListNewsDisplay)."\">".count($pollsListNewsDisplay)."</div>":null)."</a>";}
				echo "<a onclick=\"dashboardOption('Elems')\" id='tabMenuElems'>".Txt::trad("DASHBOARD_menuElems").(!empty($pluginsList)?"<div class='menuCircle'>".count($pluginsList)."</div>":null)."</a>";
				echo "<hr>";//Barre de surlignage (après les menus!)
			echo "</div>";
		}
		
		////	LISTE DES ACTUALITES
		////
		echo "<div id='contentNews'>";
			//// Premières news avant de "infinite scroll"  (Ou sinon "Aucun contenu")
			if(!empty($vueNewsListInitial))  {echo $vueNewsListInitial;}
			else{
				$addElement=(MdlDashboardNews::addRight())  ?  "<div onclick=\"lightboxOpen('".MdlDashboardNews::getUrlNew()."')\"><img src='app/img/plus.png'> ".Txt::trad("DASHBOARD_addNews")."</div>"  :  null;
				echo "<div class='emptyContainer'>".Txt::trad("DASHBOARD_noNews").$addElement."</div>";
			}
		echo "</div>";
		
		////	LISTE DES SONDAGES
		////
		if($isPolls==true)
		{
			echo "<div id='contentPolls'>";
			//// Premiers sondages avant "infinite scroll"  (Ou sinon "Aucun contenu")
			if(!empty($vuePollsListInitial))  {echo $vuePollsListInitial;}
			else{
				$addElement=(MdlDashboardPoll::addRight())  ?  "<div onclick=\"lightboxOpen('".MdlDashboardPoll::getUrlNew()."')\"><img src='app/img/plus.png'> ".Txt::trad("DASHBOARD_addPoll")."</div>"  :  null;
				echo "<div class='emptyContainer'>".Txt::trad("DASHBOARD_noPoll").$addElement."</div>";
			}
			echo "</div>";
		}
		
		////	NOUVEAUX ELEMENTS DE CHAQUE MODULES (CF. PLUGINS)
		////
		if($showNewElems==true)
		{
			echo "<div id='contentElems'><div class='miscContainer'>";
			foreach($pluginsList as $tmpObj)
			{
				//// Affiche le libellé du module
				$tmpObjModuleName=(!empty($tmpObj->moduleName))  ?  $tmpObj->moduleName  :  $tmpObj::moduleName;//Nom du module: "eventProposition" ou objet standard
				if(empty($tmpModuleName) || $tmpModuleName!=$tmpObjModuleName){
					if(!empty($tmpModuleName))  {echo "<hr>";}	//Affiche un séparateur
					$tmpModuleName=$tmpObjModuleName;			//Enregistre le nom du module courant
					echo "<div class='vContentElemsModuleLabel'><img src='app/img/".$tmpModuleName."/icon.png'>".Txt::trad(strtoupper($tmpModuleName)."_headerModuleName")."</div>";
				}
				//// Plugin Spécifique (ex: proposition d'evt)  ||  Plugin lambda
				if(isset($tmpObj->pluginSpecificMenu))  {echo $tmpObj->pluginSpecificMenu;}
				else
				{
					//Date de création et Auteur
					if(isset($tmpObj->dateCrea))  {$tmpObj->pluginTooltip.="<hr>".Txt::trad("creation")." : ".Txt::dateLabel($tmpObj->dateCrea,"dateMini")."<hr>".$tmpObj->autorLabel(true,true);}
					//Tooltip de l'icone : ajoute si besoin "Afficher l'element dans son dossier"
					$tmpObj->pluginTooltipIcon=($tmpObj::isInArbo())  ?  Txt::trad("DASHBOARD_pluginsTooltipRedir")  :  $tmpObj->pluginTooltip;
					//Affiche le plugin
					echo "<div class='menuLine objHover'>
							<div title=\"".Txt::tooltip($tmpObj->pluginTooltipIcon)."\" onclick=\"".$tmpObj->pluginJsIcon."\" class='menuIcon'><img src='app/img/".$tmpObj->pluginIcon."'></div>
							<div title=\"".Txt::tooltip($tmpObj->pluginTooltip)."\" onclick=\"".$tmpObj->pluginJsLabel."\">".$tmpObj->pluginLabel."</div>
						  </div>";
				}
			}
			//// "Aucune nouveauté sur cette période"
			if(empty($pluginsList))  {echo "<div class='emptyContainer'>".Txt::trad("DASHBOARD_pluginEmpty")."</div>";}
			echo "</div></div>";
		}
		?>
	</div>
</div>