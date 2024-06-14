<style>
.vMenuCategory	{padding:2px;}
</style>


<!--affiche chaque categorie-->
<?php
foreach($categoryList as $tmpCat){
	$categoryTooltip=(!empty($tmpCat->_id))  ?  Txt::trad($tradPrefix."_categoryMenuTooltip").' &nbsp; '.$tmpCat->getLabel().'<br>'.$tmpCat->description  :  Txt::trad($tradPrefix."_categoryShowAllTooltip");
?>
<div class="menuLine vMenuCategory" onclick="redir('?ctrl=<?= Req::$curCtrl ?>&_idCategoryFilter=<?= $tmpCat->_id ?>')" title="<?= Txt::tooltip($categoryTooltip) ?>">
	<div class="<?= $_idCategoryFilter==$tmpCat->_id?'optionSelect':'optionUnselect' ?>"><?= $tmpCat->getLabel() ?></div>
</div>
<?php } ?>


<!--Edition des categories-->
<?php if(isset($urlEditObjects)){ ?>
<div class="menuLine vMenuCategory" onclick="lightboxOpen('<?= $urlEditObjects ?>')">
	<div><img src="app/img/edit.png">&nbsp; <?= Txt::trad($tradPrefix."_categoryEditTitle") ?></div>
</div>
<?php } ?>

<!--Séparateur-->
<hr>