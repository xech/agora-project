<style>
.vMenuCategory		{padding:2px; padding-left:20px;}
.vMenuCategory>div	{padding:4px;}
</style>


<!--TITRE DES CATEGORIES-->
<div class="menuLine cursorHelp" title="<?= Txt::trad($tradPrefix."_categoryMenuLabelTitle") ?>">
	<div class="menuIcon"><img src="app/img/category.png"></div>
	<div><?= Txt::trad($tradPrefix."_categoryMenuLabel") ?></div>
</div>

<!--AFFICHE CHAQUE CATEGORIE-->
<?php foreach($categoryList as $tmpCat){ ?>
<div class="menuLine vMenuCategory" onclick="redir('?ctrl=<?= Req::$curCtrl ?>&_idCategoryFilter=<?= $tmpCat->_id ?>')" title="<?= Txt::tooltip($tmpCat->description) ?>">
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