<!--OPTIONS D'AFFICHAGE-->
<?php foreach($displayModes as $tmpDisplayMode){ ?>
	<div class="menuLine <?= $curDisplayMode==$tmpDisplayMode?'optionSelect':'option' ?>" onclick="redir('<?= $displayModeUrl.$tmpDisplayMode ?>')">
		<div class="menuIcon"><img src="app/img/displayMode_<?= $tmpDisplayMode ?>.png"></div>
		<div><?= Txt::trad("displayMode").' '.Txt::trad("displayMode_".$tmpDisplayMode) ?></div>
	</div>
<?php } ?>
