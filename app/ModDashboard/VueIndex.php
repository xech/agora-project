<script>
////	INIT
$(function(){
	////	Affichage au chargement : nouveaux "Elems" / sondage "Polls" / "News"
	<?php
	if(Req::isParam("pluginPeriod"))							{echo "dashboardOption('Elems');";}
	elseif(stristr($_SERVER["REQUEST_URI"],"dashboardPoll"))	{echo "dashboardOption('Polls');";}
	else														{echo "dashboardOption('News');";}
	?>

	////	"Infinite scroll" : Affichage progressif des news et sondages
	$(window).scroll(function(){
		//Init le chargement
		if(typeof newsLoading=="undefined"){
			newsLoading=pollsLoading=false;//Pour pas relancer le chargement s'il est déjà en cours et qu'on scroll encore. Ne pas unifier "newsLoading" et "pollsLoading"...
			newsOffsetCpt=pollsOffsetCpt=1;//Compteur d'offset (nb de blocs de news/polls affichés). Commence au bloc "1" car le bloc "0" a déjà été affiché au chargement de la page
		}
		//Lance l'infinite scroll quand on arrive en fin de page  (hauteur de page < (scrollTop + hauteur de fenêtre + 50px))
		if($(document).height() < ($(window).scrollTop() + $(window).height() + 50))
		{
			//Récupère les news suivantes
			if($("#contentNews").is(":visible") && newsLoading==false)
			{
				//Charge les news via ".get()" (plutôt que ".ajax()")
				newsLoading=true;
				$("#contentNews").append("<div id='dashboardLoadingImg'><img src='app/img/dashboard/loading.gif'></div>");
				$.get("?ctrl=dashboard&action=GetMoreNews&offlineNews=<?= Req::getParam("offlineNews") ?>&newsOffsetCpt="+newsOffsetCpt, function(vueNewsList){
					if(vueNewsList.length>0)
					{
						//Affiche les news avec un "fadeIn()" (masquées par défaut via .vNewsHidden), Puis Maj les parametres
						$("#contentNews").append(vueNewsList);
						$(".vNewsContainer").fadeIn(500);
						newsLoading=false;//reste à false si ya plus rien à charger (évite les requêtes inutiles)
						newsOffsetCpt+=1;
						//Nouvelles actus : relance l'affichage (tooltips and co) && les menus contextuels
						mainPageDisplay(false);  initMenuContext();
					}
					//Masque l'icone loading
					$("#dashboardLoadingImg").remove();
				});
			}
			//Récupère les sondages suivants
			if($("#contentPolls").is(":visible") && pollsLoading==false)
			{
				//Charge les sondages via ".get()" (plutôt que ".ajax()")
				pollsLoading=true;
				$("#contentPolls").append("<div id='dashboardLoadingImg'><img src='app/img/dashboard/loading.gif'></div>");
				$.get("?ctrl=dashboard&action=GetMorePolls&pollsNotVoted=<?= Req::getParam("pollsNotVoted") ?>&pollsOffsetCpt="+pollsOffsetCpt, function(vuePollsList){
					if(vuePollsList.length>0)
					{
						//Affiche les sondages avec un "fadeIn()" (masquées par défaut via .vPollsHidden), Puis Maj les parametres
						$("#contentPolls").append(vuePollsList);
						$(".vPollsContainer").fadeIn(500);
						pollsLoading=false;//reste à false si ya plus rien à charger (évite les requêtes inutiles)
						pollsOffsetCpt+=1;
						//Nouveaux sondages : relance l'affichage (tooltips and co)  & les menus contextuels  & le trigger de vote des sondages
						mainPageDisplay(false);  initMenuContext();  dashboardPollVote();
					}
					//Masque l'icone loading
					$("#dashboardLoadingImg").remove();
				});
			}
		}
	});

	////	Init le trigger de vote des sondages
	dashboardPollVote();
});

////	VOTE D'UN SONDAGE
function dashboardPollVote()
{
	////	VOTE UN SONDAGE
	$("form[id^=pollForm]").submit(function(event){
		//Pas de soumission par défaut du formulaire
		event.preventDefault();
		//Controle et Soumission Ajax du formulaire
		if($("#"+this.id+" input[name='pollResponse[]']:checked").length==0)	{notify("<?= Txt::trad("DASHBOARD_voteNoResponse") ?>");}
		else{
			$.ajax({url:"?ctrl=dashboard&action=pollVote",data:$(this).serialize(),dataType:"json"}).done(function(result){
				//Affiche le résultat du sondage (remplace le formulaire principal, et si besoin le formulaire "newsDisplay")
				if(result.vuePollResult.length>0)  {$(".vPollContent"+result._idPoll).html(result.vuePollResult);}
			});
		}
	});
}

////	AFFICHE UNE OPTION DU DASHBOARD ("menuName"="News"/"Polls"/"Elems")
function dashboardOption(menuName)
{
	//Déselectionne les menus principaux, puis sélectionne l'option demandé (via "sLinkSelect")
	$("[id^=mainMenu]").removeClass("sLinkSelect");
	$("#mainMenu"+menuName).addClass("sLinkSelect");
	//Déselectionne les menus contextuels et blocks de contenu principaux, puis sélectionne l'option demandé (via "sLinkSelect")
	$("[id^=modMenu], .pageCenterContent>div:not(#dashboardMenu)").hide();
	$("#modMenu"+menuName).fadeIn();
	$("#content"+menuName).show();
}
</script>

<style>
/*Menu principal/contextuel de chaque option*/
#dashboardMenu					{display:table; position:relative; width:100%; height:40px; margin-bottom:10px;}
#dashboardMenu>a				{display:table-cell; width:<?= $isPolls==true?33:50 ?>%; text-align:center; vertical-align:middle;}/*label du menu*/
#dashboardMenu hr				{display:inline; position:absolute; bottom:0px; left:0px; width:<?= $isPolls==true?33:50 ?>%; height:5px; margin:0px; background:tomato; transition:0.1s ease-in-out;}/*Surlignage des options du module*/
#mainMenuNews.sLinkSelect ~ hr	{margin-left:0%;}
#mainMenuPolls.sLinkSelect ~ hr	{margin-left:33%;}
#mainMenuElems.sLinkSelect ~ hr	{margin-left:<?= $isPolls==true?66:50 ?>%;}
#dashboardLoadingImg			{text-align:center;}
[id^=modMenu], .pageCenterContent>div:not(#dashboardMenu)	{display:none;}/*masque par défaut tous les menus et contenus du module, sauf le menu principal*/
#contentNews>div, #contentPolls>div, #contentElems>div		{width:100%;}

/*Affichage des News*/
.vNewsContainer					{padding-top:5px; padding-bottom:10px;}/*pas de "padding-right"*/
.vNewsHidden					{display:none;}/*cf. infinite scroll*/
.vNewsDescription				{font-weight:normal; padding:10px;}
.vNewsDescription a				{text-decoration:underline;}/*on garde le style des liens de l'editeur*/
.vNewsDetail					{text-align:center;}
.vNewsDetail>span				{margin-left:15px;}
.vNewsTopNews					{color:#c40;}
#modMenuNews .vPollsTitleMain	{border-top:#ccc 1px solid; border-bottom:#ccc 1px solid; margin-top:30px; margin-bottom:10px; padding:10px; text-align:center;}
#modMenuNews .vPollsTitle		{margin-top:0px; margin-bottom:20px;}/*surcharge*/
#modMenuNews ul					{padding-left:10px!important;}/*surcharge*/
#modMenuNews hr:last-of-type	{display:none;}
#vNewsPollsHr					{background:#ddd;}
#vNewsPollsHr:last-of-type		{display:none;}

/*Affichage des sondages*/
.vPollsContainer				{padding-top:15px; padding-bottom:15px;}/*pas de "padding-right"*/
.vPollsContainer ul li			{list-style:none; margin-bottom:20px;}
.vPollsTitle					{text-align:center; font-style:italic; margin-bottom:30px;}
.vPollsDescription				{text-align:center; font-weight:normal; margin-bottom:30px;}
.vPollsDateEnd					{text-align:center;}
.vPollsHidden					{display:none;}/*cf. infinite scroll*/
.vPollsResponseFile				{margin-top:8px;}/*cf. MdlDashboardPoll*/
.vPollsResponseFile img			{max-width:100px; max-height:50px; vertical-align:middle;}/*idem*/
.vPollResponseInput .vPollsResponseFile	{margin-left:25px;}
.vPollsContainer .formMainButton		{margin:10px;}/*surcharge*/
.vPollsContainer button			{width:200px!important;}/*surcharge*/
.vPollsResultPercent			{width:90%; height:35px; margin-top:8px; padding:4px; border-radius:5px; background-color:#fafafa; box-shadow:0px 1px 5px #ddd inset;}
.vPollsResultPercentBar			{display:inline-block; min-width:35px; height:25px; line-height:25px; text-align:right; padding-right:5px; border-radius:5px; box-shadow:0px 1px 3px #bbb;}
.vPollsResultPercentBar0		{background:linear-gradient(to top, #e5e5e5, #fcfcfc, #ececec);}
.vPollsResultPercentBar50		{background:linear-gradient(to top, #fd9215, #ffc55b, #fecf15);}
.vPollsResultPercentBar100		{background:linear-gradient(to top, #86bf24, #98d829, #99e21b);}
#pollsLoading					{text-align:center;}

/*Affichage des elements (nouveaux/courants)*/
#modMenuElems>div					{padding:5px;}
#contentElems .menuLine				{padding:3px;}
#contentElems .menuIcon				{width:15px;}
#contentElems .menuIcon img			{max-width:15px;}
.vContentElemsModuleLabel			{text-align:center; padding-top:20px;}
.vContentElemsModuleLabel img		{max-height:28px; margin-right:8px;}
/*RESPONSIVE*/
@media screen and (max-width:1023px){
	.vNewsContainer	{padding-top:10px; padding-bottom:10px;}
}
</style>

<div class="pageCenter">
	<div class="pageModMenuContainer">
		<div id="pageModMenu" class="miscContainer">

			<!--MENU CONTEXT "NEWS"-->
			<div id="modMenuNews">
				<?php
				//Ajoute une news / Affiche les news "Offline"  /  Tri des news
				if(MdlDashboardNews::addRight())	{echo "<div class='menuLine sLink' onclick=\"lightboxOpen('".MdlDashboardNews::getUrlNew()."');\"><div class='menuIcon'><img src='app/img/plus.png'></div><div>".Txt::trad("DASHBOARD_addNews")."</div></div>";}
				if(!empty($offlineNewsCount))		{echo "<div class='menuLine ".(Req::getParam("offlineNews")==1?'sLinkSelect':'sLink')."' onclick=\"redir('?ctrl=dashboard&offlineNews=".(Req::getParam("offlineNews")==1?0:1)."')\"><div class='menuIcon'><img src='app/img/dashboard/newsOffline.png'></div><div>".Txt::trad("DASHBOARD_newsOffline").(!empty($offlineNewsCount)?"<div class='objMiscMenuCircle objMiscMenuCircleInline'>".$offlineNewsCount."</div>":null)."</div></div>";}
				echo MdlDashboardNews::menuSort();
				//Affichage des sondages (option "newsDisplay")
				if(!empty($pollsListNewsDisplay)){
					echo "<div class='vPollsTitleMain'>".Txt::trad("DASHBOARD_pollsNotVoted")." <div class='objMiscMenuCircle objMiscMenuCircleInline'>".$pollsNotVotedNb."</div></div>";
					foreach($pollsListNewsDisplay as $tmpKey=>$tmpPoll)  {echo "<div class='vPollsContainer'><div class='vPollsTitle' title=\"".$tmpPoll->description."\">".$tmpPoll->title."</div><div class=\"vPollContent".$tmpPoll->_id."\">".$tmpPoll->vuePollForm(true)."</div></div><hr>";}
				}
				?>
			</div>

			<!--MENU CONTEXT DES SONDAGES/POLLS-->
			<?php if($isPolls==true){ ?>
			<div id="modMenuPolls">
				<?php
				//Ajoute un sondage  /  Voir uniquement les sondages à voter  /  Tri des sondages 
				if(MdlDashboardPoll::addRight()){echo "<div class='menuLine sLink' onclick=\"lightboxOpen('".MdlDashboardPoll::getUrlNew()."');\"><div class='menuIcon'><img src='app/img/plus.png'></div><div>".Txt::trad("DASHBOARD_addPoll")."</div></div>";}
				if(!empty($pollsNotVotedNb))	{echo "<div class='menuLine ".(Req::getParam("pollsNotVoted")==1?'sLinkSelect':'sLink')."' onclick=\"redir('?ctrl=dashboard&dashboardPoll=true&pollsNotVoted=".(Req::getParam("pollsNotVoted")==1?0:1)."')\" title=\"".Txt::trad("DASHBOARD_pollsNotVotedInfo")."\"><div class='menuIcon'><img src='app/img/check.png'></div><div>".Txt::trad("DASHBOARD_pollsNotVoted")."<div class='objMiscMenuCircle objMiscMenuCircleInline'>".$pollsNotVotedNb."</div></div></div>";}
				echo MdlDashboardPoll::menuSort(null,"&dashboardPoll=true");
				?>
			</div>
			<?php } ?>
			
			<!--MENU CONTEXT DES NOUVEAUX ELEMENTS (Plugins) : AFFICHAGE JOUR/SEMAINE/MOIS-->
			<?php if($isNewElems==true){ ?>
			<div id="modMenuElems">
				<div><?= Txt::trad("DASHBOARD_plugins") ?> :</div>
				<?php
				foreach($pluginPeriodOptions as $periodValue=>$tmpPeriod){
					$selectedPeriod=($pluginPeriod==$periodValue)  ?  "checked='checked'"  :  null;
					$titlePeriod=($periodValue=="day")  ?  Txt::trad("today")  :  Txt::trad("DASHBOARD_pluginsInfo2")." ".date("d/m/Y",$tmpPeriod["timeBegin"])." ".Txt::trad("and")." ".date("d/m/Y",$tmpPeriod["timeEnd"]);
					echo "<div class='noTooltip' title=\"".Txt::trad("DASHBOARD_pluginsInfo")." ".$titlePeriod."\">
							<input name='pluginPeriod' type='radio' id='radioPeriod".$periodValue."' ".$selectedPeriod." onclick=\"redir('?ctrl=dashboard&pluginPeriod=".$periodValue."')\">
							<label for='radioPeriod".$periodValue."'>".Txt::trad("DASHBOARD_plugins_".$periodValue)."</label>
						  </div>";
				}
				?>
			</div>
			<?php } ?>
		</div>
	</div>
	<div class="pageCenterContent">
		<!--MENU TOP DU DASHBOARD => POUR POUVOIR AFFICHER LES SONDAGES ET NOUVEAUX ELEMENTS-->
		<?php if($isPolls==true || $isNewElems==true){ ?>
		<div id="dashboardMenu" class="miscContainer">
			<a href="javascript:dashboardOption('News')"  id="mainMenuNews"><?= Txt::trad("DASHBOARD_menuNews") ?></a>
			<?php if($isPolls==true){ ?><a href="javascript:dashboardOption('Polls')" id="mainMenuPolls"><?= Txt::trad("DASHBOARD_menuPolls").(!empty($pollsNotVotedNb)?"<div class='objMiscMenuCircle objMiscMenuCircleInline' title=\"".Txt::trad("DASHBOARD_pollsNotVoted")." : ".$pollsNotVotedNb."\">".$pollsNotVotedNb."</div>":null) ?></a><?php } ?>
			<a href="javascript:dashboardOption('Elems')" id="mainMenuElems"><?= Txt::trad("DASHBOARD_menuElems").(!empty($pluginsList)?"<div class='objMiscMenuCircle objMiscMenuCircleInline'>".count($pluginsList)."</div>":null) ?></a>
			<hr /><!--Barre de surlignage : après les menus!-->
		</div>
		<?php } ?>

		<!--LISTE DES ACTUALITES "NEWS"-->
		<div id="contentNews">
			<?php
			////	Premières news avant "infinite scroll"  ||  Aucun contenu + "ajouter"
			if(!empty($vueNewsListInitial))	{echo $vueNewsListInitial;}
			else{
				$addElement=(MdlDashboardNews::addRight())  ?  "<div class='sLink' onclick=\"lightboxOpen('".MdlDashboardNews::getUrlNew()."')\"><img src='app/img/plus.png'> ".Txt::trad("DASHBOARD_addNews")."</div>"  :  null;
				echo "<div class='emptyContainer'>".Txt::trad("DASHBOARD_noNews").$addElement."</div>";
			}
			?>
		</div>

		<!--LISTE DES SONDAGES "POLLS"-->
		<?php if($isPolls==true){ ?>
		<div id="contentPolls">
			<?php
			////	Premiers sondages avant "infinite scroll"  ||  Aucun contenu + "ajouter"
			if(!empty($vuePollsListInitial))	{echo $vuePollsListInitial;}
			else{
				$addElement=(MdlDashboardPoll::addRight())  ?  "<div class='sLink' onclick=\"lightboxOpen('".MdlDashboardPoll::getUrlNew()."')\"><img src='app/img/plus.png'> ".Txt::trad("DASHBOARD_addPoll")."</div>"  :  null;
				echo "<div class='emptyContainer'>".Txt::trad("DASHBOARD_noPoll").$addElement."</div>";
			}
			?>
		</div>
		<?php } ?>

		<!--LISTE DES NOUVEAUX ELEMENTS (plugins)-->
		<?php if($isNewElems==true){ ?>
		<div id="contentElems">
			<div class="miscContainer">
			<?php
			////	NOUVEAUX ELEMENTS DE CHAQUE MODULES (cf. plugins)
			foreach($pluginsList as $tmpObj)
			{
				//Affiche le libellé du module?
				if(empty($tmpModuleName) || $tmpModuleName!=$tmpObj->pluginModule){
					echo "<div class='vContentElemsModuleLabel'><img src='app/img/".$tmpObj->pluginModule."/icon.png'>".Txt::trad(strtoupper($tmpObj->pluginModule)."_headerModuleName")."<hr></div>";
					$tmpModuleName=$tmpObj->pluginModule;
				}
				//Plugin Spécifique (exple: events à confirmer)  ||  Affichage normal
				if(isset($tmpObj->pluginSpecificMenu))  {echo $tmpObj->pluginSpecificMenu;}
				else{
					echo "<div class='menuLine sLink objHover'>
							<div title=\"".$tmpObj->pluginTooltipIcon."\" onclick=\"".$tmpObj->pluginJsIcon."\" class='menuIcon'><img src='app/img/".$tmpObj->pluginIcon."'></div>
							<div title=\"".$tmpObj->pluginTooltip."\" onclick=\"".$tmpObj->pluginJsLabel."\">".$tmpObj->pluginLabel."</div>
						  </div>";
				}
			}
			//"Aucune nouveauté sur cette période"
			if(empty($pluginsList))  {echo "<div class='emptyContainer'>".Txt::trad("DASHBOARD_pluginEmpty")."</div>";}
			?>
			</div>
		</div>
		<?php } ?>
	</div>
</div>