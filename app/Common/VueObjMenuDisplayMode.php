<div class="menuLine">
	<div class="menuIcon"><img src="app/img/display<?= ucfirst($curDisplayMode) ?>.png"></div>
	<div>
		<span class="menuLaunch" for="menuDisplayMode"><?= Txt::trad("displayMode")." ".Txt::trad("displayMode_".$curDisplayMode) ?></span>
		<div  class="menuContext" id="menuDisplayMode">
			<?php
			//Options d'affichage
			foreach($displayModes as $tmpDisplayMode){
			echo "<div class='menuLine'>
					<div class='menuIcon'><img src='app/img/display".ucfirst($tmpDisplayMode).".png'></div>
					<div><a onclick=\"redir('".$displayModeUrl.$tmpDisplayMode."')\" ".($curDisplayMode==$tmpDisplayMode?"class='linkSelect'":null).">".Txt::trad("displayMode")." ".Txt::trad("displayMode_".$tmpDisplayMode)."</a></div>
				</div>";
			}
			?>
		</div>
	</div>
</div>