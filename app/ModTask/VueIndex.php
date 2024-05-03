<script>
////	INIT : LARGEUR DE LA TIMELINE (GANTT)
$(function(){
	$(".vTimelineMain").width($("#pageFullContent").width()).show();/*masqué par défaut pour permettre le calcul du width des ".objContainer" via "common.js"*/
});
</script>

<style>
/*LABEL/DETAILS DES TACHES*/
.objBlocks .priorityLabel, .objBlocks .categoryLabel	{display:block; margin-top:7px;}							/*Label de la priorité et du statut (surcharges)*/
.objLines  .priorityLabel, .objLines .categoryLabel		{display:inline-block; margin-left:20px;}					/*Idem*/
.objBlocks .vObjTaskDetails								{position:absolute; display:block; bottom:3px; right:20px;}	/*Détails des taches en affichage "block" : Icones avec "title"*/
.objBlocks .objDetails img								{max-height:20px; margin-right:10px;}						/*Icones des détails*/

/*TIMELINE*/
.vTimelineSeparator					{visibility:hidden; width:100%;}
.vTimelineMain						{margin-top:20px; padding:0px; padding-top:10px;}
.vTimelineMain						{display:none; overflow-x:auto;}								/*masqué par défaut pour le calcul du width des ".objContainer" via "common.js"*/
.vTimelineMain table				{border-collapse:collapse;}
.vTimelineMain td					{vertical-align:middle; white-space:nowrap;}
.vTimelineMonths					{padding-bottom:8px;}	/*Label des mois*/
.vTimelineDays						{padding-left:3px; cursor:help;}
.vTimelineTitle						{padding:0px 10px;}		/*Label de la tâche*/
.vTimelineMain td:not(:first-child)	{min-width:25px;}		/*Cell des jours !!*/
.vTimelineLeftBorder				{border-left:#ccc solid 1px;}
.vTimelineLeftBorder2				{border-left:#eee solid 1px;}
.vTimelineToday						{color:#c00; font-size:1.1em}
.vTimelineMain .progressBar			{width:100%; text-align:left; padding:0px 3px;}/*surcharge : 100% de width en fonction de la durée de la tâche (cf. "colspan" des cellules)*/

/*MOBILE FANCYBOX (440px)*/
@media screen and (max-width:440px){
	.vTimelineMain td:not(:first-child)		{min-width:22px;}
	.vTimelineMain img						{display:none;}
}
</style>

<div id="pageFull">
	<div id="pageModuleMenu">
		<?= MdlTask::menuSelectObjects() ?>
		<div id="pageModMenu" class="miscContainer">
			<?php
			////	MENU D'AJOUT D'ELEMENTS
			if(Ctrl::$curContainer->addContentRight()){
				echo "<div class='menuLine' onclick=\"lightboxOpen('".MdlTask::getUrlNew()."');\"><div class='menuIcon'><img src='app/img/plus.png'></div><div>".Txt::trad("TASK_addTask")."</div></div>
					  <div class='menuLine' onclick=\"lightboxOpen('".MdlTaskFolder::getUrlNew()."')\"><div class='menuIcon'><img src='app/img/folder/folderAdd.png'></div><div>".Txt::trad("addFolder")."</div></div>
					  <hr>";
			}
			////	ARBORESCENCE  &  MENU DES STATUS KANBAN  &  MENU DU MODE D'AFFICHAGE  &  MENU DE TRI  &  DESCRIPTION DU CONTENU
			echo MdlTaskFolder::menuTree().MdlTaskStatus::displayMenu().MdlTask::menuDisplayMode().MdlTask::menuSort().
				"<div class='menuLine'><div class='menuIcon'><img src='app/img/info.png'></div><div>".Ctrl::$curContainer->contentDescription()."</div></div>";
			?>
		</div>
	</div>

	<div id="pageFullContent" class="<?= MdlTask::getDisplayMode()=="line"?"objLines":"objBlocks" ?>">
		<?php
		////	PATH DU DOSSIER COURANT & LISTE DES DOSSIERS
		echo MdlFolder::menuPath(Txt::trad("TASK_addTask"),MdlTask::getUrlNew());
		echo CtrlObject::vueFolders();
		////	LISTE DES TACHES
		foreach($tasksList as $tmpTask)
		{
			echo $tmpTask->objContainer().$tmpTask->contextMenu().
					"<div class='objContentScroll'>
						<div class='objContent'>
							<div class='objIcon objIconOpacity'><img src='app/img/task/iconSmall.png'></div>
							<div class='objLabel' onclick=\"lightboxOpen('".$tmpTask->getUrl("vue")."')\">".ucfirst($tmpTask->title).$tmpTask->categoryLabel().$tmpTask->priorityLabel()."</div>
							<div class='objDetails vObjTaskDetails'>".$tmpTask->responsiblePersons().$tmpTask->advancement().$tmpTask->dateBeginEnd()."</div>
							<div class='objAutorDate'>".$tmpTask->autorDateLabel()."</div>
						</div>
					</div>
				</div>";
		}
		////	AUCUN CONTENU & AJOUTER
		if(empty(CtrlObject::vueFolders()) && empty($tasksList)){
			$addElement=(Ctrl::$curContainer->addContentRight())  ?  "<div onclick=\"lightboxOpen('".MdlTask::getUrlNew()."')\"><img src='app/img/plus.png'> ".Txt::trad("TASK_addTask")."</div>"  :  null;
			echo "<div class='emptyContainer'>".Txt::trad("TASK_noTask").$addElement."</div>";
		}

		////	TIMELINE (GANTT)
		if(!empty($timelineBegin))
		{
			//// INIT LA TIMELINE
			echo "<hr class='vTimelineSeparator'>
				  <div class='vTimelineMain miscContainer'><table>";
					//// HEADER MOIS & JOURS
					$timelineHeaderMonths=$timelineHeaderDays=null;
					foreach($timelineDays as $tmpDay){
						if($tmpDay["newMonthLabel"])  {$timelineHeaderMonths.="<td class='vTimelineMonths' colspan='".$tmpDay["newMonthColspan"]."'>".$tmpDay["newMonthLabel"]."</td>";}
						$timelineHeaderDays.="<td class='vTimelineDays ".$tmpDay["vTimelineToday"]." ".$tmpDay["vTimelineLeftBorder"]."' title=\"".$tmpDay["dayLabelTitle"]."\">".$tmpDay["dayLabel"]."</td>";
					}
					echo "<tr><td class='vTimelineTitle'>&nbsp;</td>".$timelineHeaderMonths."</tr>
						  <tr><td class='vTimelineTitle'>&nbsp;</td>".$timelineHeaderDays."</tr>";
					//// TIMELINE DE CHAQUE TACHE
					foreach($timelineTasks as $tmpTask)
					{
						$tmpTaskCells="";
						//Affiche chaque jour de la timeline pour la tâche courante (cellule du jour || cellule de la tache si le 1er jour de la tache || jour précédant la tache OU jour suivant la tache)
						foreach($timelineDays as $tmpDay){
							$isTaskBegin=($tmpTask->dateBegin==$tmpDay["curDate"]);//La tâche commence la cellule du jour affichée ($tmpDay)
							if($isTaskBegin==true || $tmpDay["timeBegin"]<$tmpTask->timeBegin || $tmpTask->timeEnd<$tmpDay["timeBegin"]){
								$tmpCellColspan=($isTaskBegin==true)  ?  "colspan='".$tmpTask->timelineColspan."'"  :  null;
								$tmpCellLabel  =($isTaskBegin==true)  ?  $tmpTask->timelineGanttBar()  :  "&nbsp;";
								$tmpTaskCells.="<td class=\"vTimelineTaskDays ".$tmpDay["vTimelineLeftBorder"]."\" ".$tmpCellColspan." >".$tmpCellLabel."</td>";}
						}
						//Affiche toute la timeline de la tâche courante
						echo "<tr class='lineHover' onclick=\"lightboxOpen('".$tmpTask->getUrl("vue")."')\">
								<td class='vTimelineTitle' title=\"".Txt::tooltip($tmpTask->title)."\">".Txt::reduce($tmpTask->title,(Req::isMobile()?30:50))."</td>".
								$tmpTaskCells.
							"</tr>";
					}
			echo "</table></div>";
		}
		?>
	</div>
</div>