<style>
.vEvtDetail						{display:table;}
.vEvtDetail>div					{display:table-cell;}
.vEvtDetailIcon					{width:40px;}			
.vEvtDetailIcon img				{max-width:25px;}
.vEvtDetail .categoryColor		{width:20px; height:20px; margin-right:20px;}
.attachedFileMenu 				{display:block; margin-inline:0px;}/*surcharge*/
.attachedFileMenu img 			{margin-right:20px;}/*surcharge*/
hr:last-of-type					{display:none;}
</style>


<div>
	<!--MENU CONTEXTUEL & D'EDITION / TITRE-->
	<div class="lightboxTitle"><?= $curObj->lightboxMenu().$curObj->title ?></div>

	<!--DESCRIPTION-->
	<?php if(!empty($curObj->description)){ ?>
		<div class="vEvtDetail">
			<div class="vEvtDetailIcon"><img src="app/img/description.png"></div>
			<div><?= $curObj->description ?></div>
		</div><hr>
	<?php } ?>

	<!--DATE-->
	<div class="vEvtDetail">
		<div class="vEvtDetailIcon"><img src="app/img/calendar/clock.png"></div>
		<div><?= $curObj->dateLabel() ?></div>
	</div><hr>

	<!--PERIODICITE-->
	<?php if(!empty($labelPeriod)){ ?>
		<div class="vEvtDetail">
			<div class="vEvtDetailIcon"><img src="app/img/calendar/period.png"></div>
			<div><?= $labelPeriod ?></div>
		</div><hr>
	<?php } ?>

	<!--CATEGORIE-->
	<?php if($curObj->_idCat){ ?>
		<div class="vEvtDetail">
			<div><?= $curObj->categoryLabel() ?></div>
		</div><hr>
	<?php } ?>

	<!--IMPORTANT-->
	<?php if($curObj->important){ ?>
		<div class="vEvtDetail">
			<div class="vEvtDetailIcon"><img src="app/img/important.png"></div>
			<div><?= Txt::trad("CALENDAR_importantEvent") ?></div>
		</div><hr>
	<?php } ?>

	<!--AFFECTATIONS AUX AGENDAS-->
	<?php if(Ctrl::$curUser->isUser()){ ?>
		<div class="vEvtDetail">
			<div class="vEvtDetailIcon"><img src="app/img/calendar/iconSmall.png"></div>
			<div><?= $curObj->affectedCalendarsLabel() ?></div>
		</div><hr>
	<?php } ?>

	<!--VISIBILITE SPECIALE-->
	<?php if(!empty($contentVisibility)){ ?>
		<div class="vEvtDetail">
			<div class="vEvtDetailIcon"><img src="app/img/displayHide.png"></div>
			<div><?= Txt::tooltip("CALENDAR_visibilityTooltip")." : ".$contentVisibility ?></div>
		</div><hr>
	<?php } ?>

	<!--VISIOCONFERENCE-->
	<?php if(!empty($curObj->visioUrl)){ ?>
		<div class="vEvtDetail">
			<div class="vEvtDetailIcon"><img src="app/img/visioSmall.png"></div>
			<div><a href="?ctrl=misc&action=LaunchVisio&visioURL=<?= urlencode($curObj->visioUrl) ?>" class="lightboxOpenHref"><?= Txt::trad("VISIO_launchFromEvent") ?></a></div>
		</div><hr>
	<?php } ?>

	<!--FICHIERS JOINTS-->
	<?= $curObj->attachedFileMenu()	?>
</div>