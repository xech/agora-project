<style>
.vNavMenuDisabled	{opacity:0.4;}
</style>

<div class="objBottomMenu">
	<div class="miscContainer">
		<?php
		////	PRECEDENT / NUMÉROS DE PAGE / SUIVANT
		echo "<a ".$prevAttr."><img src='app/img/navPrev.png'></a>";
		for($pageNbTmp=1; $pageNbTmp<=$pageNbTotal; $pageNbTmp++)	{echo "<a href=\"".$hrefBase.$pageNbTmp."\" class=\"".($pageNb==$pageNbTmp?"sLinkSelect":"sLink")."\" title=\"".Txt::trad("goToPage")." ".$pageNbTmp."\">".$pageNbTmp."</a>";}
		echo "<a ".$nextAttr."><img src='app/img/navNext.png'></a>";
		?>
	</div>
</div>