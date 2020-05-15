<script>
////	INIT : LARGEUR DE LA TIMELINE (GANTT)
$(function(){
	$(".vTimelineBlock").width($("#pageFullContent").width()).show();/*masqué par défaut pour permettre le calcul du width des ".objContainer" via "common.js"*/
});
</script>

<style>
/*LABEL ET DETAILS DES TACHES*/
.objLabelBg							{background-image:url(app/img/task/iconBg.png);}/*surcharge : Background des taches*/
.objBlocks .vObjTaskDetails			{position:absolute; display:block; bottom:3px; right:20px;}/*Détails des taches en affichage "block" : Icones flottantes avec "title"*/
.objLines .percentBar				{margin-left:10px;}/*"PercentBar" (Folder/Task)*/
.objBlocks .objDetails img			{max-height:20px; margin-right:10px;}/*Icones avec "title"*/
.objLines .objDetails img			{max-height:16px; margin-right:5px;}/*Icone des "PercentBar" (Folder/Task)*/

/*TIMELINE*/
.vTimelineHr						{visibility:hidden; width:100%;}
.objBlocks .vTimelineHr				{margin-top:120px!important; clear:both;}/*"clear" les "objBlocks" flottants au dessus : évite l'affichage de scroll "fantome" sous firefox*/
.vTimelineBlock						{display:none; overflow-x:auto;}/*masqué par défaut pour permettre le calcul du width des ".objContainer" via "common.js"*/
.vTimelineBlock>table				{border-collapse:collapse;}
.vTimelineMonths, .vTimelineDays, .vTimelineTaskDays	{vertical-align:middle; width:22px; min-width:22px;}
.vTimelineMonths					{min-width:100px!important;}
.vTimelineTitle, .vTimelineDays, .vTimelineTaskDays	{padding:0px; white-space:nowrap; vertical-align:middle;}
.vTimelineTitle						{padding:0px 10px 0px 10px;}
.vTimelineDays						{font-size:0.9em;}
.vTimelineDays:hover				{background-color:#eee;}
.vTimelineLeftBorder				{border-left:#ddd solid 1px;}
.vTimelineToday						{color:#c00; font-size:1.1em}
.vTimelineBlock .percentBar			{margin:0px;}/*surcharge*/
.vTimelineBlock .percentBarContent	{text-align:left; cursor:pointer;}
/*RESPONSIVE FANCYBOX (440px)*/
@media screen and (max-width:440px){
	input[type=text], input[type=password], input[type=file], textarea, select, button	{height:30px; padding:3px;}
	.ui-datepicker-calendar .ui-state-default	{height:24px!important;}/*surcharge du datepicker*/
	.vTimelineMonths, .vTimelineDays, .vTimelineTaskDays	{width:18px; min-width:18px;}
	.vTimelineBlock .percentBar img		{display:none;}/*surcharge*/
}
</style>

<div id="pageFull">

	<div id="pageModuleMenu">
		<div id="pageModMenu" class="miscContainer">
			<?php
			////	AJOUT D'ELEMENTS
			if(Ctrl::$curContainer->editContentRight()){
				echo "<div class='menuLine sLink' onclick=\"lightboxOpen('".MdlTask::getUrlNew()."');\"><div class='menuIcon'><img src='app/img/plus.png'></div><div>".Txt::trad("TASK_addTask")."</div></div>
					  <div class='menuLine sLink' onclick=\"lightboxOpen('".MdlTaskFolder::getUrlNew()."')\"><div class='menuIcon'><img src='app/img/folder/folderAdd.png'></div><div>".Txt::trad("addFolder")."</div></div>
					  <hr>";
			}
			?>
			<!--FILTRE PAR PRIORITE-->
			<div class="menuLine sLink">
				<div class="menuIcon"><img src="app/img/task/priority<?= Req::getParam("filterPriority") ?>.png"></div>
				<div>
					<span class="menuLaunch" for="menuPriority"><?= Txt::trad("TASK_priority")." ".(Req::getParam("filterPriority")>=1?Txt::trad("TASK_priority".Req::getParam("filterPriority")):null) ?></span>
					<div id="menuPriority" class="menuContext">
						<?php for($tmpPriority=0; $tmpPriority<=4; $tmpPriority++){
							if($tmpPriority==0)  {$tmpPriority=null;}
							echo "<div class='menuLine'><div class='menuIcon'><img src='app/img/task/priority".$tmpPriority.".png'></div><div><a onclick=\"redir('".Tool::getParamsUrl("filterPriority")."&filterPriority=".$tmpPriority."')\" ".($tmpPriority==Req::getParam("filterPriority")?"class='sLinkSelect'":null).">".(empty($tmpPriority)?Txt::trad("displayAll"):Txt::trad("TASK_priority".$tmpPriority))."</a></div></div>";
						} ?>
					</div>
				</div>
			</div>
			<hr>
			<?php
			////	ARBORESCENCE  &  MENU DE SELECTION/AFFICHAGE/TRI
			echo CtrlObject::folderTreeMenu().MdlTask::menuSelectObjects().MdlTask::menuDisplayMode().MdlTask::menuSort();
			?>
			<div class="menuLine"><div class="menuIcon"><img src="app/img/info.png"></div><div><?= Ctrl::$curContainer->folderContentDescription() ?></div></div>
		</div>
	</div>

	<div id="pageFullContent" class="<?= MdlTask::getDisplayMode()=="line"?"objLines":"objBlocks" ?>">

		<?php
		////	PATH DU DOSSIER COURANT & LISTE DES DOSSIERS
		echo CtrlObject::folderPathMenu(Txt::trad("TASK_addTask"),MdlTask::getUrlNew());
		echo $foldersList;
		////	LISTE DES TACHES
		foreach($tasksList as $tmpTask)
		{
			echo $tmpTask->divContainer().$tmpTask->contextMenu().
					"<div class='objContentScroll'>
						<div class='objContent'>
							<div class='objLabel objLabelBg'><a href=\"javascript:lightboxOpen('".$tmpTask->getUrl("vue")."')\">".$tmpTask->priority()." ".$tmpTask->title."</a></div>
							<div class='objDetails vObjTaskDetails'>".$tmpTask->responsiblePersons().$tmpTask->advancement().$tmpTask->dateBeginEnd()."</div>
							<div class='objAutorDate'>".$tmpTask->displayAutorDate()."</div>
						</div>
					</div>
				</div>";
		}
		////	AUCUN CONTENU & AJOUTER
		if(empty($foldersList) && empty($tasksList)){
			$addElement=(Ctrl::$curContainer->editContentRight())  ?  "<div class='sLink' onclick=\"lightboxOpen('".MdlTask::getUrlNew()."')\"><img src='app/img/plus.png'> ".Txt::trad("TASK_addTask")."</div>"  :  null;
			echo "<div class='emptyContainer'>".Txt::trad("TASK_noTask").$addElement."</div>";
		}

		////	TIMELINE (GANTT)
		if(!empty($timelineBegin))
		{
			//// INIT LA TIMELINE
			echo "<hr class='vTimelineHr'>
				  <div class='vTimelineBlock miscContainer'><table>";
				//// HEADER MOIS & JOURS
				$timelineHeaderMonths=$timelineHeaderDays=null;
				foreach($timelineDays as $tmpDay){
					if($tmpDay["newMonthLabel"])  {$timelineHeaderMonths.="<td colspan='".$tmpDay["newMonthColspan"]."' class='vTimelineMonths vTimelineLeftBorder'>".$tmpDay["newMonthLabel"]."</td>";}
					$timelineHeaderDays.="<td class='vTimelineDays ".$tmpDay["vTimelineToday"]." ".$tmpDay["vTimelineLeftBorder"]." cursorHelp' title=\"".$tmpDay["dayLabelTitle"]."\">".$tmpDay["dayLabel"]."</td>";
				}
				echo "<tr><td class='vTimelineTitle'>&nbsp;</td>".$timelineHeaderMonths."</tr>
					  <tr><td class='vTimelineTitle'>&nbsp;</td>".$timelineHeaderDays."</tr>";
				//// TIMELINE DE CHAQUE TACHE
				foreach($timelineTasks as $tmpTask)
				{
					$taskDateBegin=date("Y-m-d",$tmpTask->timeBegin);
					$tmpTaskCells="";
					foreach($timelineDays as $tmpDay){//Affiche les jours de la timeline (cellule du jour || cellule de la tache si le 1er jour de la tache || jour précédant la tache OU jour suivant la tache)
						$isTaskDateBegin=($taskDateBegin==$tmpDay["curDate"]) ? true : false;
						if($isTaskDateBegin==true || $tmpDay["timeBegin"]<$tmpTask->timeBegin || $tmpTask->timeEnd<$tmpDay["timeBegin"])
							{$tmpTaskCells.="<td class=\"vTimelineTaskDays ".$tmpDay["vTimelineLeftBorder"]."\" ".($isTaskDateBegin==true?"colspan='".$tmpTask->timelineColspan."'":null)." >".($isTaskDateBegin==true?$tmpTask->timelineBeginEnd():"&nbsp;")."</td>";}
					}
					echo "<tr class='sTableRow'>
							<td class='vTimelineTitle'><a href=\"javascript:lightboxOpen('".$tmpTask->getUrl("vue")."')\" title=\"".$tmpTask->title."\">".Txt::reduce($tmpTask->title,(Req::isMobile()?35:50))."</a></td>".
							$tmpTaskCells.
						 "</tr>";
				}
			echo "</table></div>";
		}
		?>
	</div>
</div>