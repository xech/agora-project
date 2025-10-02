<script>
/********************************************************************************************************
 *	WIDTH DE LA TIMELINE (lancé via "mainDisplay()" cf "app.js")
*******************************************************************************************/
function moduleDisplay(){
	$(".vTimelineMain").outerWidth(containerWidth,true);
}
</script>


<style>
/*LABEL/DETAILS DES TACHES*/
.vObjTasks .objLabelInfos				{margin-top:10px; font-size:0.95rem;}		/*.categoryLabel et .priorityLabel*/
.vObjTasks .objLabelInfos span			{display:inline-block; margin-right:10px;}
.vObjTasks .categoryColor				{width:14px; height:14px;}					/*.categoryColor idem .priorityLabel*/
.objFolders .progressBar				{margin-top:5px;}							/*Dossiers : margin des .progressBar avec le nb d'elements du dossier*/
.objLines .objContainer					{height:70px;}								/*Line : surcharge la hauteur*/
.objLines .vObjTasks .progressBar		{margin-left:15px;}							/*Line : affichage des .progressBar*/
.objBlocks .vObjTasks .objIconOpacity	{display:none;}								/*Block : masque l'icone*/
.objBlocks .vObjTasks .objDetails		{display:table-cell!important; width:40px; text-align:right;}	/*Block : affiche les .objDetails*/
.objBlocks .vObjTasks .progressBar		{margin-bottom:5px; padding:2px 5px;}		/*Block : .progressBar au format icone -> sans label*/
.objBlocks .vObjTasks .progressBarLabel	{display:none;}								/*Idem*/
.progressBarDelayed						{color:#740;}

/*TIMELINE*/
.vTimelineSeparator						{visibility:hidden; width:100%;}
.vTimelineMain							{overflow-x:auto; margin-top:20px; padding:0px; padding-top:10px;}
.vTimelineMain table					{border-collapse:collapse;}
.vTimelineMain td						{vertical-align:middle; white-space:nowrap;}
.vTimelineMonths						{padding-bottom:8px;}/*Label des mois*/
.vTimelineDays							{padding-left:3px; cursor:help;}
.vTimelineTitle							{padding:0px 10px;}	/*Label de la tâche*/
.vTimelineMain td:not(:first-child)		{min-width:30px;}	/*Cell des jours*/
.vTimelineLeftBorder					{border-left:#ccc solid 1px;}
.vTimelineLeftBorder2					{border-left:#eee solid 1px;}
.vTimelineMain .progressBar				{width:100%; padding:5px 3px; text-align:left; font-size:0.85rem;}/*100% de width (cf. durée de la tâche et "colspan" des cellules)*/
.vTimelineMain .progressBar img[src*=date]	{display:none;}

/*RESPONSIVE SMARTPHONE*/
@media screen and (max-width:490px){
	.vTimelineMain td:not(:first-child)	{min-width:22px;}
	.vTimelineMain img					{display:none;}
}
</style>


<div id="pageFull">
	<div id="moduleMenu">
		<?= MdlTask::menuSelect() ?>
		<div class="miscContainer">
			<?php
			////	MENU D'AJOUT D'ELEMENTS
			if(Ctrl::$curContainer->addContentRight()){
				echo '<div class="menuLine" onclick="lightboxOpen(\''.MdlTask::getUrlNew().'\')"><div class="menuIcon"><img src="app/img/plus.png"></div><div>'.Txt::trad("TASK_addTask").'</div></div>
					  <div class="menuLine" onclick="lightboxOpen(\''.MdlTaskFolder::getUrlNew().'\')"><div class="menuIcon"><img src="app/img/folder/folderAdd.png"></div><div>'.Txt::trad("addFolder").'</div></div>
					  <hr>';
			}
			////	ARBORESCENCE  &  MENU DES STATUS KANBAN  &  MENU DU MODE D'AFFICHAGE  &  MENU DE TRI  &  DESCRIPTION DU CONTENU
			echo MdlTaskFolder::menuTree().MdlTaskStatus::displayMenu().MdlTask::menuDisplayMode().MdlTask::menuSort().
				'<div class="menuLine"><div class="menuIcon"><img src="app/img/info.png"></div><div>'.Ctrl::$curContainer->contentDescription().'</div></div>';
			?>
		</div>
	</div>

	<div id="pageContent" class="<?= MdlTask::getDisplayMode()=="line"?"objLines":"objBlocks" ?>">
		<?php
		////	PATH DU DOSSIER COURANT & LISTE DES DOSSIERS
		echo MdlFolder::menuPath(Txt::trad("TASK_addTask"),MdlTask::getUrlNew()).
			 CtrlObject::vueFolders();

		////	LISTE DES TACHES
		foreach($tasksList as $tmpTask){
			echo $tmpTask->objContainerMenu();
		?>
				<div class="objContainerScroll">
					<div class="objContent vObjTasks">
						<div class="objIcon objIconOpacity"><img src="app/img/task/iconSmall.png"></div>
						<div class="objLabel" onclick="<?= $tmpTask->openVue() ?>">
							<?= ucfirst($tmpTask->title) ?>
							<div class="objLabelInfos"><?= $tmpTask->categoryLabel().$tmpTask->priorityLabel() ?></div>
						</div>
						<div class="objDetails"><?= $tmpTask->responsiblePersons().$tmpTask->progressAdvancement().$tmpTask->progressBeginEnd() ?></div>
						<div class="objAutorDate"><?= $tmpTask->autorDate() ?></div>
					</div>
				</div>
			</div>
		<?php
		}

		////	AUCUN CONTENU & AJOUTER
		if(empty(CtrlObject::vueFolders()) && empty($tasksList)){
			$addElement=(Ctrl::$curContainer->addContentRight())  ?  '<div onclick="lightboxOpen(\''.MdlTask::getUrlNew().'\')"><img src="app/img/plus.png"> '.Txt::trad("TASK_addTask").'</div>'  :  null;
			echo '<div class="miscContainer emptyContainer">'.Txt::trad("TASK_noTask").$addElement.'</div>';
		}

		////	TIMELINE
		if(!empty($timelineBegin))
		{
			//// INIT LA TIMELINE
			echo '<hr class="vTimelineSeparator">
				  <div class="vTimelineMain miscContainer"><table>';
					//// HEADER MOIS & JOURS
					$timelineHeaderMonths=$timelineHeaderDays=null;
					foreach($timelineDays as $tmpDay){
						if($tmpDay["newMonthLabel"])  {$timelineHeaderMonths.='<td class="vTimelineMonths" colspan="'.$tmpDay["newMonthColspan"].'">'.$tmpDay["newMonthLabel"].'</td>';}
						if($tmpDay["curDate"]==date('Y-m-d'))	{$tmpDay["dayLabel"]='<span class="circleNb">'.$tmpDay["dayLabel"].'</span>';}
						$timelineHeaderDays.='<td class="vTimelineDays '.$tmpDay["classLeftBorder"].'" '.Txt::tooltip($tmpDay["dayLabelTitle"]).'>'.$tmpDay["dayLabel"].'</td>';
					}
					echo '<tr><td class="vTimelineTitle">&nbsp;</td>'.$timelineHeaderMonths.'</tr>
						  <tr><td class="vTimelineTitle">&nbsp;</td>'.$timelineHeaderDays.'</tr>';
					//// TIMELINE DE CHAQUE TACHE
					foreach($timelineTasks as $tmpTask)
					{
						$tmpTaskCells=null;
						//Affiche chaque jour de la timeline pour la tâche courante (cellule du jour || cellule de la tache si le 1er jour de la tache || jour précédant la tache OU jour suivant la tache)
						foreach($timelineDays as $tmpDay){
							$isTaskBegin=($tmpTask->dateBegin==$tmpDay["curDate"]);//La tâche commence la cellule du jour affichée ($tmpDay)
							if($isTaskBegin==true || $tmpDay["dayTimeBegin"]<$tmpTask->timeBegin || $tmpTask->timeEnd<$tmpDay["dayTimeBegin"]){
								$tmpCellColspan=($isTaskBegin==true)  ?  "colspan='".$tmpTask->timelineColspan."'"  :  null;
								$tmpCellLabel  =($isTaskBegin==true)  ?  $tmpTask->progressBeginEnd()  :  "&nbsp;";
								$tmpTaskCells.='<td class="vTimelineTaskDays '.$tmpDay["classLeftBorder"].'" '.$tmpCellColspan.'>'.$tmpCellLabel.'</td>';}
						}
						//Affiche toute la timeline de la tâche courante
						echo '<tr class="lineHover" onclick="'.$tmpTask->openVue().'">
								<td class="vTimelineTitle" '.Txt::tooltip($tmpTask->title).'>'.Txt::reduce($tmpTask->title,(Req::isMobile()?30:50)).'</td>'.
								$tmpTaskCells.
							'</tr>';
					}
			echo '</table></div>';
		}
		?>
	</div>
</div>