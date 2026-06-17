
<!--LISTE DES CATEGORIES-->
<?php
foreach($categoryList as $tmpCat){
	$urlRedir='?ctrl='.Req::$curCtrl.'&_idCategoryFilter='.$tmpCat->_id;
	if(Req::isParam("curTime"))  {$urlRedir.='&curTime='.Req::param("curTime");}
	$catTooltip=(empty($tmpCat->_id))  ?  Txt::trad($tradPrefix."_CAT_showAllTooltip")  :  Txt::trad($tradPrefix."_CAT_menuTooltip").' '.$tmpCat->getLabel().'<br>'.$tmpCat->description;
?>
	<div class="menuLine <?= $_idCategoryFilter==$tmpCat->_id?'optionSelect':'option' ?>" <?= Txt::tooltip($catTooltip) ?> onclick="redir('<?= $urlRedir ?>')">
		<div><?= $tmpCat->getLabel() ?></div>
	</div>
<?php } ?>


<!--EDITION DES CATEGORIES-->
<?php if(isset($urlEditObjects)){ ?>
<div class="menuLine" onclick="lightboxOpen('<?= $urlEditObjects ?>')">
	<div class="menuIcon"><img src="app/img/edit.png"></div>
	<div><?= Txt::trad($tradPrefix."_CAT_editTitle") ?></div>
</div>
<?php } ?>


<!--SÉPARATEUR-->
<hr>