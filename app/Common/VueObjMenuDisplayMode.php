<div class="menuLine">
	<div class="menuIcon"><img src="app/img/display<?= ucfirst($displayMode) ?>.png"></div>
	<div>
		<span class="menuLaunch" for="menuDisplayMode"><?= Txt::trad("displayMode")." ".Txt::trad("displayMode_".$displayMode) ?></span>
		<div  class="menuContext" id="menuDisplayMode">
			<?php
			//Options d'affichage
			foreach($displayModeOptions as $tmpDisplay){
			echo "<div class='menuLine'>
					<div class='menuIcon'><img src='app/img/display".ucfirst($tmpDisplay).".png'></div>
					<div><a onclick=\"redir('".$displayModeUrl.$tmpDisplay."')\" ".($displayMode==$tmpDisplay?"class='sLinkSelect'":null).">".Txt::trad("displayMode")." ".Txt::trad("displayMode_".$tmpDisplay)."</a></div>
				</div>";
			}
			?>
		</div>
	</div>
</div>