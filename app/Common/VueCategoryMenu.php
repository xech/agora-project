<style>
.vMenuCategory			{margin:2px;}
.vMenuCategory > div	{padding:4px;}
</style>


<!--LISTE DES CATEGORIES-->
<?php
foreach($categoryList as $tmpCat){
	$catTooltip=(empty($tmpCat->_id))  ?  Txt::trad($tradPrefix."_CAT_showAllTooltip")  :  Txt::trad($tradPrefix."_CAT_menuTooltip").' '.$tmpCat->getLabel().'<br>'.$tmpCat->description;
?>
	<div class="menuLine vMenuCategory" onclick="redir('?ctrl=<?= Req::$curCtrl ?>&_idCategoryFilter=<?= $tmpCat->_id ?>')" <?= Txt::tooltip($catTooltip) ?>>
		<div class="<?= $_idCategoryFilter==$tmpCat->_id ? 'optionSelect' : 'option' ?>"><?= $tmpCat->getLabel() ?></div>
	</div>
<?php } ?>


<!--EDITION DES CATEGORIES-->
<?php if(isset($urlEditObjects)){ ?>
<div class="menuLine vMenuCategory" onclick="lightboxOpen('<?= $urlEditObjects ?>')">
	<div><img src="app/img/edit.png">&nbsp; <?= Txt::trad($tradPrefix."_CAT_editTitle") ?></div>
</div>
<?php } ?>


<!--SÉPARATEUR-->
<hr>