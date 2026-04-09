<script>
/************************************************************************************************************
 *	INIT
 ************************************************************************************************************/
ready(function(){
	////	"Infinite scroll" : Affichage progressif des news et sondages
	$(window).on("scroll",function(){
		//Timeout pour ne pas charger durant le scroll
		if(typeof scrollTimeout!="undefined")  {clearTimeout(scrollTimeout);}//Un seul timeout
		scrollTimeout=setTimeout(function(){
			//Lance l'infinite scroll quand on arrive en fin de page  (hauteur de page < (scrollTop + hauteur de fenêtre + 20px))
			if($(document).height() < ($(window).scrollTop() + windowTopHeight + 20)){
				//Init le chargement
				if(typeof loadMoreNews==="undefined"){
					loadMoreNews=loadMorePolls=true;//Marqueur pour savoir si on doit charger des News/Polls en fin de page
					newsOffset=pollsOffset=1;	//Compteur des blocs de news/polls déjà affichés (offset). Commence à "1" car le bloc "0" est affiché au chargement de page
				}
				//Charge les news suivantes (via ".get()" et non ".ajax")
				if($("#contentNews").isVisible() && loadMoreNews==true){
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
				if($("#contentPolls").isVisible() && loadMorePolls==true){
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

/************************************************************************************************************
 *	MENU ACTUALITÉS / SONDAGES / NOUVEAUX ELEMENTS
 ************************************************************************************************************/
function dashboardOption(menuName)
{
	//Déselectionne tous les menus -> puis sélectionne le menu demandé
	$("#tabMenus a").removeClass("optionSelect");
	$("#tabMenu"+menuName).addClass("optionSelect");
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

/************************************************************************************************************
 *	VOTE D'UN SONDAGE
 ************************************************************************************************************/
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
/*Menu Actualités / Sondages / Nouveaux elements*/
#tabMenus									{display:table; width:100%; table-layout:fixed;}/*fixed: même width pour chaque cells*/
#tabMenus a									{display:table-cell; text-align:center;}/*label du menu*/
#tabMenus a.optionSelect					{padding-block:5px;}
#tabMenus .circleNb							{margin-left:5px;}
#contentNews,#contentPolls,#contentElems	{display:none;}/*Masque par défaut les contenus principaux*/
.infiniteScrollHidden						{display:none;}
.infiniteScrollLoading						{text-align:center;}

/*News*/
.vNewsContainer.objContent				{height:auto!important; padding:15px; padding-right:35px;}/*surcharge de .objContent : height adapté au contenu*/
.vNewsDescription						{font-weight:normal;}
.vNewsDescription h3					{text-align:center;}/*1ere news par défaut : cf. "INSTALL_dataDashboardNews"*/
.vNewsDescription h4					{font-weight:normal; font-size:1.05rem;}
.vNewsDescription h4 img				{max-width:30px; max-height:25px; margin-inline:10px;}
.vNewsDescription h4:last-child			{margin-bottom:30px;}
.vNewsDetail							{margin-top:20px; margin-bottom:10px; text-align:center;}		/*Détails centrés*/
.vNewsDetail>div						{display:inline-block; margin-inline:15px; line-height:22px;}	/*alignement : "line-height" à la taille des Icones ci-dessous*/
.vNewsDetail img						{max-height:22px;}												/*Icones des details (à la une, etc)*/
.vNewsTopNews							{color:#a40;}													/*texte "Actualité à la une"*/
/*AFFICHAGE RESPONSIVE*/
@media screen and (max-width:1200px){
	.vNewsDescription h3				{font-size:1.3rem;}									/*New par défaut*/
	.vNewsDescription h4				{font-size:1.05rem; clear:left;}					/*Idem. "clear:left" pour aligner avec l'image float : tester width 500px*/
	.vNewsDescription h4>img			{float:left; margin-left:0px; margin-bottom:30px;}	/*Idem*/
}

/*Sondages*/
#pageMenu .vPollsTitle					{margin-block:20px 10px; font-weight:bold;}
#pageMenu .vPollsContainer ul			{padding-left:10px; margin:0px;}
#pageMenu .submitButtonMain				{margin-block:15px;}
.vPollsContainer.objContent				{height:auto!important; padding:15px; padding-right:35px;}/*surcharge : height adapté au contenu*/
.vPollsTitle, .vPollsDescription		{text-align:center; margin:15px 0px;}/*Titre et Description*/
#contentPolls .vPollsTitle				{font-size:1.2rem;}/*Titre de l'affichage principal (pas avec les news)*/
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
/*AFFICHAGE RESPONSIVE*/
@media screen and (max-width:1200px){
	.vPollsContainer ul		{padding-left:0px!important;}
	.vPollsDetails>div		{display:block; margin:8px;}
}

/*Nouveaux elements*/
#modMenuElems>div							{padding:5px;}
#contentElems .menuLine						{padding:3px;}
#contentElems .menuIcon						{width:15px;}
#contentElems .menuIcon img					{max-width:15px;}
.vContentElemsModuleLabel					{text-align:center;}
.vContentElemsModuleLabel:not(:first-child)	{margin-top:30px;}
</style>


<div id="pageCenter">
	<div id="pageMenu">
		<div class="miscContent">

			<!--MENU CONTEXT DES ACTUALITÉS-->
			<div id="modMenuNews">
				<!--AJOUTE UNE NEWS-->
				<?php if(MdlDashboardNews::addRight()){ ?>
				<div class="menuLine forMobileAddElem" onclick="lightboxOpen('<?= MdlDashboardNews::getUrlNew() ?>')">
					<div class="menuIcon"><img src="app/img/plus.png"></div>
					<div><?= Txt::trad("DASHBOARD_addNews") ?></div>
				</div>
				<?php } ?>
				<!--NEWS "OFFLINE"-->
				<div class="menuLine <?= empty($_SESSION["offlineNews"])?"option":"optionSelect" ?>" <?= Txt::tooltip($offlineNewsNb.' '.Txt::trad("DASHBOARD_offlineNewsNb")) ?> >
					<div class="menuIcon"><img src="app/img/dashboard/newsOffline.png"></div>
					<div onclick="redir('?ctrl=dashboard&offlineNews=<?= empty($_SESSION['offlineNews'])?'true':'false' ?>')"><?= Txt::trad("DASHBOARD_offlineNews") ?></div>
				</div>
				<hr>
				<!--TRI DES NEWS-->
				<?= MdlDashboardNews::menuSort() ?>
				<!--LISTE DES SONDAGES (OPTION "NEWSDISPLAY")-->
				<?php
					foreach($pollsListNewsDisplay as $tmpKey=>$tmpPoll){
				?>
					<hr>
					<div class="vPollsContainer">
						<div class="vPollsTitle" <?= Txt::tooltip($tmpPoll->description) ?> ><?= $tmpPoll->title ?></div>
						<div class="vPollContent<?= $tmpPoll->_id ?>"><?= $tmpPoll->vuePollForm(true) ?></div>
					</div>
				<?php
					}
				?>
			</div>

			<!--MENU CONTEXT DES SONDAGES-->
			<?php if($isPolls==true){ ?>
				<div id="modMenuPolls">
					<!--Ajoute un sondage-->
					<?php if(MdlDashboardPoll::addRight()){ ?>
						<div class="menuLine forMobileAddElem" onclick="lightboxOpen('<?= MdlDashboardPoll::getUrlNew() ?>')">
							<div class="menuIcon"><img src="app/img/plus.png"></div>
							<div><?= Txt::trad("DASHBOARD_addPoll") ?></div>
						</div>
					<?php } ?>
					<!--Voir uniquement les sondages votés-->
					<?php if(!empty($pollsVotedNb)){ ?>
						<div class="menuLine <?= $_SESSION["pollsVotedShow"]==true?'optionSelect':null ?>" <?= Txt::tooltip($pollsVotedNb." ".Txt::trad("DASHBOARD_pollsVotedNb")) ?> >
							<div class="menuIcon"><img src="app/img/check.png"></div>
							<div onclick="redir('?ctrl=dashboard&dashboardPoll=true&pollsVotedShow=<?= $_SESSION['pollsVotedShow']==true?'false':'true' ?>')"><?= Txt::trad("DASHBOARD_pollsVoted") ?></div>
						</div>
					<?php } ?>
					<!--Tri des sondages-->
					<?= MdlDashboardPoll::menuSort("&dashboardPoll=true") ?>
				</div>
			<?php } ?>

			<!--MENU CONTEXT DES NOUVEAUX ELEMENTS-->
			<?php if($showNewElems==true){ ?>
				<div id="modMenuElems"><div><?= Txt::trad("DASHBOARD_plugins") ?> :</div>
				<?php
				foreach($pluginPeriodOptions as $periodValue=>$tmpPeriod){
					$titlePeriod=($periodValue=="day")  ?  Txt::trad("today")  :  Txt::trad("DASHBOARD_pluginsTooltip2")." ".date("d/m/Y",$tmpPeriod["timeBegin"])." ".Txt::trad("and")." ".date("d/m/Y",$tmpPeriod["timeEnd"]);
				?>
					<div <?= Txt::tooltip(Txt::trad("DASHBOARD_pluginsTooltip")." ".$titlePeriod) ?> >
						<input name="pluginPeriod" type="radio" id="radioPeriod<?= $periodValue ?>" <?= $pluginPeriod==$periodValue?'checked="checked"':null ?> onclick="redir('?ctrl=dashboard&pluginPeriod=<?= $periodValue ?>')">
						<label for="radioPeriod<?= $periodValue ?>"><?= Txt::trad("DASHBOARD_plugins_".$periodValue) ?></label>
					</div>
				<?php } ?>
				</div>
			<?php } ?>
		</div>
	</div>


	<div id="pageContent">

		<!--MENU DU DASHBORAD-->
		<?php if($isPolls==true || $showNewElems==true){ ?>
			<div class="pathMenu miscContent">
				<div id="tabMenus">
					<!--ACTUALITÉS-->
					<a onclick="dashboardOption('News')" id="tabMenuNews">
						<?= Txt::trad("DASHBOARD_menuNews") ?>
					</a>
					<!--SONDAGES-->
					<?php if($isPolls==true){ ?>
						<a onclick="dashboardOption('Polls')" id="tabMenuPolls">
							<?= Txt::trad("DASHBOARD_menuPolls") ?>
							<?php if(!empty($pollsNotVotedNb)){ ?><span <?= Txt::tooltip(Txt::trad("DASHBOARD_pollsNotVoted").' : '.$pollsNotVotedNb) ?> class="circleNb" > <?= $pollsNotVotedNb ?></span><?php } ?>
						</a>
					<?php } ?>
					<!--NOUVEAUX ELEMENTS-->
					<a onclick="dashboardOption('Elems')" id="tabMenuElems">
						<?= Txt::trad("DASHBOARD_menuElems") ?>
						<?php if(!empty($pluginsList)){ ?><span class="circleNb"><?= count($pluginsList) ?></span><?php } ?>
					</a>
				</div>
			</div>
		<?php } ?>

		<!--LISTE DES ACTUALITÉS-->
		<div id="contentNews">
			<!--PREMIÈRES NEWS (AVANT INFINITE SCROLL)-->
			<?= $vueNewsListInitial ?>
			<!--AUCUNE NEWS-->
			<?php if(empty($vueNewsListInitial)){ ?><div class="miscContent emptyContent"><?= Txt::trad("DASHBOARD_noNews") ?></div><?php } ?>
		</div>

		<!--LISTE DES SONDAGES-->
		<?php if($isPolls==true){ ?>
		<div id="contentPolls">
			<!--PREMIERS SONDAGES (AVANT INFINITE SCROLL)-->
			<?= $vuePollsListInitial ?>
			<!--AUCUN SONDAGE-->
			<?php if(empty($vuePollsListInitial)){ ?><div class="miscContent emptyContent"><?= Txt::trad("DASHBOARD_noPoll") ?></div><?php } ?>
		</div>
		<?php } ?>
		
		<!--LISTE DES NOUVEAUX ELEMENTS (PLUGIN)-->
		<?php if($showNewElems==true){ ?>
		<div id="contentElems">
			<div class="miscContent">
				<!--AFFICHE CHAQUE NOUVEAUTE-->
				<?php foreach($pluginsList as $tmpObj){
					$showModuleLabel=(empty($tmpModuleName) || $tmpModuleName!=$tmpObj::moduleName);
					$tmpModuleName=$tmpObj::moduleName;
					if(isset($tmpObj->dateCrea))  {$tmpObj->pluginTooltip.='<hr>'.Txt::trad("createdBy").' '.$tmpObj->autorDate(true);}
					$tmpObj->pluginTooltipIcon=($tmpObj::isInArbo())  ?  Txt::trad("DASHBOARD_pluginsTooltipRedir")."<hr>".$tmpObj->pluginTooltip  :  $tmpObj->pluginTooltip;//"Afficher l'element dans son dossier"
				?>
					<!--LABEL DU MODULE + HR-->
					<?php if($showModuleLabel==true){ ?>
						<div class="vContentElemsModuleLabel">
							<img src="app/img/<?= $tmpObj::moduleName ?>/iconSmall.png">&nbsp; <?= Txt::trad(strtoupper($tmpObj::moduleName)."_MODULE_NAME") ?>
							<hr>
						</div>
					<?php } ?>
					<!--AFFICHE LE PLUGIN-->
					<div class="menuLine lineHover">
						<div onclick="<?= $tmpObj->pluginJsIcon ?>" <?= Txt::tooltip($tmpObj->pluginTooltipIcon) ?> class="menuIcon"><img src="app/img/<?= $tmpObj->pluginIcon ?>"></div>
						<div onclick="<?= $tmpObj->pluginJsLabel ?>" <?= Txt::tooltip($tmpObj->pluginTooltip) ?> ><?= $tmpObj->pluginLabel ?></div>
					</div>
				<?php } ?>
				<!--AUCUN NOUVEL ELEMENT-->
				<?php if(empty($pluginsList)){ ?><div class="emptyContent"><?= Txt::trad("DASHBOARD_pluginEmpty") ?></div><?php } ?>
			</div>
		</div>
		<?php } ?>
	
	</div>
</div>