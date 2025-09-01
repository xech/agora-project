<style>
#menuPagination 					{padding:8px;}/*surcharge*/
#menuPagination .vMenuPageDisabled	{opacity:0.4;}
#menuPagination a					{display:inline-block; padding:10px 8px; border-radius:5px; font-size:1.1rem;}
#menuPagination a:hover, #menuPagination .linkSelect	{background-color:#eee;}
</style>

<div class="objMenuBottom">
	<div class="miscContainer" id="menuPagination">
		<a <?= $pageUrlPrev ?> ><img src="app/img/arrowLeftNav.png"></a>
		<?php for($pageNbTmp=1; $pageNbTmp<=$pageNbTotal; $pageNbTmp++){ ?>
			<a href="<?= $pageUrl.$pageNbTmp ?>" class="<?= $pageNbTmp==$pageNbCur?'linkSelect':null ?>" <?=Txt::tooltip(Txt::trad("goToPage").' '.$pageNbTmp) ?> ><?= $pageNbTmp ?></a>
		<?php } ?>
		<a <?= $pageUrlNext ?> ><img src="app/img/arrowRightNav.png"></a>
	</div>
</div>