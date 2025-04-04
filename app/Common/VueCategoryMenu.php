<style>
.vMenuCategory	{padding:1px;}
</style>


<!--affiche chaque categorie-->
<?php
foreach($categoryList as $tmpCat){
	$categoryTooltip=(!empty($tmpCat->_id))  ?  Txt::trad($tradPrefix."_CAT_menuTooltip").' &nbsp; '.$tmpCat->getLabel().'<br>'.$tmpCat->description  :  Txt::trad($tradPrefix."_CAT_showAllTooltip");
?>
<div class="menuLine vMenuCategory" onclick="redir('?ctrl=<?= Req::$curCtrl ?>&_idCategoryFilter=<?= $tmpCat->_id ?>')" <?= Txt::tooltip($categoryTooltip) ?>>
	<div class="<?= $_idCategoryFilter==$tmpCat->_id?'optionSelect':'option' ?>"><?= $tmpCat->getLabel() ?></div>
</div>
<?php } ?>


<!--Edition des categories-->
<?php if(isset($urlEditObjects)){ ?>
<div class="menuLine vMenuCategory" onclick="lightboxOpen('<?= $urlEditObjects ?>')">
	<div><img src="app/img/edit.png">&nbsp; <?= Txt::trad($tradPrefix."_CAT_editTitle") ?></div>
</div>
<?php } ?>

<!--Séparateur-->
<hr>