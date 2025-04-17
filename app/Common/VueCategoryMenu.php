<style>
.vMenuCategory	{padding:1px;}
</style>


<!--affiche chaque categorie-->
<?php
foreach($categoryList as $tmpCat){
	$labelClass=($_idCategoryFilter==$tmpCat->_id)  ?  'optionSelect'  :  'option';
	if(!empty($tmpCat->_id)){
		$categoryTooltip=Txt::trad($tradPrefix."_CAT_menuTooltip").' <img src="app/img/arrowRight.png"> '.$tmpCat->getLabel().'<br>'.$tmpCat->description;
	}else{
		$categoryTooltip=Txt::trad($tradPrefix."_CAT_showAllTooltip");		
		if($labelClass=="optionSelect")	{$labelClass.=' optionSelectAll';}
	}
?>
	<div class="menuLine vMenuCategory" onclick="redir('?ctrl=<?= Req::$curCtrl ?>&_idCategoryFilter=<?= $tmpCat->_id ?>')" <?= Txt::tooltip($categoryTooltip) ?>>
		<div class="<?= $labelClass ?>"><?= $tmpCat->getLabel() ?></div>
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