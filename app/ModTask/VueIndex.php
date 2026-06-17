<script>
/************************************************************************************************************
 *	WIDTH DE LA TIMELINE (lancé via  "app.js")
*************************************************************************************************************/
function moduleDisplay(){
	$("#timelineMain").width(1);										//#timelineMain au minimum (pas masqué) pour calculer le width de #pageContent
	$("#timelineMain").outerWidth( $("#pageContent").width() ,true);	//Affiche le width
}
</script>


<style>
/*LABEL/DETAILS DES TACHES*/
.vObjTasks .objLabelDetail					{margin-top:10px; font-size:0.9rem;}		/*.categoryLabel et .priorityLabel*/
.vObjTasks .objLabelDetail span				{display:inline-block; margin-right:10px;}	/*idem*/
.objLines .objContent						{height:65px;}								/*surcharge*/
.objLines .progressBar						{margin-left:15px;}							/*.progressBar*/
.objBlocks .objIconOpacity					{display:none;}								/*masque l'icone*/
.objBlocks .objDetails						{display:table-cell; width:40px; text-align:right;}/*cellule des .progressBar*/
.objBlocks .objFolders .objDetails			{width:100px;}								/*détail des dossiers*/
.objBlocks .objDetails .progressBar			{margin-block:2px;}							/*.progressBar au format icone : sans label*/
.objBlocks .progressBarLabel				{display:none;}								/*idem*/
.progressBarDelayed							{color:#740;}

/*TIMELINE*/
#timelineSeparator							{visibility:hidden; width:100%;}
#timelineMain								{overflow-x:auto; padding:0px; padding-top:10px;}
#timelineMain table							{max-width:100%; border-collapse:collapse; white-space:nowrap;}
#timelineMain td							{vertical-align:middle;}
.vTimelineMonths							{padding-bottom:8px;}/*Label des mois*/
.vTimelineDays								{padding-left:3px; cursor:help;}
.vTimelineTitle								{max-width:350px; padding:0px 10px; overflow:hidden; text-overflow:ellipsis;}	/*Label de la tâche*/
#timelineMain td:not(:first-child)			{min-width:30px;}	/*Cell des jours*/
.vTimelineLeftBorder						{border-left:#ccc solid 1px;}
.vTimelineLeftBorder2						{border-left:#eee solid 1px;}
#timelineMain .progressBar					{width:100%; margin:0px!important; padding:5px 2px;}/*100% de width des cellules : cf "colspan"*/
#timelineMain .progressBar img[src*=date]	{display:none;}
#timelineMain  .progressBarLabel			{display:inline-block;}/*affiche toujours le contenu du .progressBarLabel*/

/*** RESPONSIVE SMARTPHONE*/
@media screen and (max-width:499px){
	#timelineMain							{font-size:0.9rem;}
	#timelineMain td:not(:first-child)		{min-width:22px;}
	#timelineMain img						{display:none;}
	.vTimelineTitle							{max-width:220px;}
}
</style>


<div id="pageFull">
	<div id="pageMenu">
		<?= MdlTask::menuSelect() ?>
		<div class="miscContent">
			<?php
			////	MENU D'AJOUT D'ELEMENTS
			if(Ctrl::$curContainer->addContentRight()){
				echo '<div class="menuLine forMobileAddElem" onclick="lightboxOpen(\''.MdlTask::getUrlNew().'\')"><div class="menuIcon"><img src="app/img/plus.png"></div><div>'.Txt::trad("TASK_addTask").'</div></div>
					  <div class="menuLine" onclick="lightboxOpen(\''.MdlTaskFolder::getUrlNew().'\')"><div class="menuIcon"><img src="app/img/plusAddFolder.png"></div><div>'.Txt::trad("addFolder").'</div></div>
					  <hr>';
			}
			////	ARBORESCENCE  &  MENU DES STATUS KANBAN  &  MENU DU MODE D'AFFICHAGE  &  MENU DE TRI  &  DESCRIPTION DU CONTENU
			echo MdlTaskFolder::menuTree().MdlTaskStatus::displayMenu().MdlTask::menuDisplayMode().MdlTask::menuSort().
				'<div class="menuLine"><div class="menuIcon"><img src="app/img/info.png"></div><div>'.Ctrl::$curContainer->contentDescription().'</div></div>';
			?>
		</div>
	</div>

	<div id="pageContent" class="<?= MdlTask::getDisplayMode()=="line"?"objLines":"objBlocks" ?>">

		<!--PATH DU DOSSIER COURANT & LISTE DES DOSSIERS-->
		<?= MdlFolder::menuPath(Txt::trad("TASK_addTask"),MdlTask::getUrlNew()).CtrlObject::vueFolders() ?>

		<!--LISTE DES TACHES-->
		<?php
		foreach($tasksList as $tmpTask){
			echo $tmpTask->objContentDiv();
		?>
				<div class="objContentScroll">
					<div class="objContentTab vObjTasks">
						<div class="objIcon objIconOpacity"><img src="app/img/task/iconSmall.png"></div>
						<div class="objLabel" onclick="<?= $tmpTask->lightboxVue() ?>">
							<?= ucfirst($tmpTask->title) ?>
							<div class="objLabelDetail"><?= $tmpTask->categoryLabel().$tmpTask->priorityLabel() ?></div>
						</div>
						<div class="objDetails"><?= $tmpTask->responsiblePersons().$tmpTask->progressAdvancement().$tmpTask->progressBeginEnd() ?></div>
						<div class="objAutorDate"><?= $tmpTask->autorDate(true) ?></div>
					</div>
				</div>
			</div>
		<?php } ?>

		<!--AUCUN CONTENU & AJOUTER-->
		<?php if(empty(CtrlObject::vueFolders()) && empty($tasksList)){ ?>
			<div class="miscContent emptyContent">
				<?= Txt::trad("TASK_noTask") ?>
				<?php if(Ctrl::$curContainer->addContentRight()){ ?><div onclick="lightboxOpen('<?= MdlTask::getUrlNew() ?>')"><img src="app/img/plus.png"> <?= Txt::trad("TASK_addTask") ?></div><?php } ?>
			</div>
		<?php } ?>

		<!--TIMELINE-->
		<?php if(!empty($timelineBegin)){ ?>
			<hr id="timelineSeparator">
			<div id="timelineMain" class="miscContent">
				<table>

					<?php
					////	HEADER MOIS & JOURS
					$timelineHeaderMonths=$timelineHeaderDays=null;
					foreach($timelineDays as $tmpDay){
						if($tmpDay["newMonthLabel"])			{$timelineHeaderMonths.='<td class="vTimelineMonths" colspan="'.$tmpDay["newMonthColspan"].'">'.$tmpDay["newMonthLabel"].'</td>';}
						if($tmpDay["curDate"]==date('Y-m-d'))	{$tmpDay["dayLabel"]='<span class="circleNb">'.$tmpDay["dayLabel"].'</span>';}
						$timelineHeaderDays.='<td class="vTimelineDays '.$tmpDay["classLeftBorder"].'" '.Txt::tooltip($tmpDay["dayLabelTitle"]).'>'.$tmpDay["dayLabel"].'</td>';
					}
					?>
					<tr><td class="vTimelineTitle">&nbsp;</td><?= $timelineHeaderMonths ?></tr>
					<tr><td class="vTimelineTitle">&nbsp;</td><?= $timelineHeaderDays ?></tr>

					<?php
					////	TIMELINE DE CHAQUE TÂCHE
					foreach($timelineTasks as $tmpTask){
						//Affiche chaque jour de la timeline pour la tâche courante (cellule du jour || cellule de la tache si le 1er jour de la tache || jour précédant la tache OU jour suivant la tache)
						$tmpTaskCells=null;
						foreach($timelineDays as $tmpDay){
							$isTaskBegin=($tmpTask->dateBegin==$tmpDay["curDate"]);//La tâche commence la cellule du jour affichée ($tmpDay)
							if($isTaskBegin==true || $tmpDay["dayTimeBegin"]<$tmpTask->timeBegin || $tmpTask->timeEnd<$tmpDay["dayTimeBegin"]){
								$tmpCellColspan=($isTaskBegin==true)  ?  "colspan='".$tmpTask->timelineColspan."'"  :  null;
								$tmpCellLabel  =($isTaskBegin==true)  ?  $tmpTask->progressBeginEnd()  :  "&nbsp;";
								$tmpTaskCells.='<td class="vTimelineTaskDays '.$tmpDay["classLeftBorder"].'" '.$tmpCellColspan.'>'.$tmpCellLabel.'</td>';
							}
						}
					?>
					<tr class="lineHover" onclick="<?= $tmpTask->lightboxVue() ?>">
						<td class="vTimelineTitle" <?= Txt::tooltip($tmpTask->title) ?>><?= $tmpTask->title ?></td>
						<?= $tmpTaskCells ?>
					</tr>
					<?php } ?>

				</table>
			</div>
		<?php } ?>
	</div>
</div>