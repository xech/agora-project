<style>
.menuSortAscDesc		{text-align:right!important;}
.menuSortAscDesc img	{cursor:pointer;}
</style>

<div class="menuLine">
	<div class="menuIcon"><img src="app/img/sort.png"></div>
	<div>
		<span class="menuLauncher" for="objMenuSort<?= $objectType ?>"><?= Txt::trad("sortBy")." ".Txt::trad("SORT_".$curSortField) ?> <img src="app/img/sort<?= ucfirst($curSortValue)?>.png"></span>
		<div  class="menuContext" id="objMenuSort<?= $objectType ?>">
			<?php
			//// Affiche chaque option de Tri
			foreach($sortFields as $keySort=>$tmpSort){
				//// Affiche le "field" qu'une fois (car présent 2 fois dans le $sortFields : "asc" et "desc")
				$tmpSortTab=Txt::txt2tab($tmpSort);
				$fieldTmp=$tmpSortTab[0];
				if(empty($fieldLast) || $fieldLast!=$fieldTmp){
					$classLabel	=($fieldTmp==$curSortField)  ?  "linkSelect"  :  null;
					$imgAsc		=($fieldTmp==$curSortField && $curSortValue=="asc")   ?  "sortAscSelect.png"  :  "sortAsc.png";
					$imgDesc	=($fieldTmp==$curSortField && $curSortValue=="desc")  ?  "sortDescSelect.png"  :  "sortDesc.png";
					$urlSort=Tool::paramsUrl("sort").$addUrlParams."&sort=".$fieldTmp;
					$fieldLast=$fieldTmp;
				?>
					<div class="menuLine">
						<div class="<?= $classLabel ?>" onclick="redir('<?= $urlSort.($curSortValue=='asc'?'@@desc':'@@asc') ?>')"><?= Txt::trad("sortBy2").' '.Txt::trad("SORT_".$fieldTmp) ?></div>
						<div class="menuSortAscDesc">
							<img src="app/img/<?= $imgAsc ?>" <?= Txt::tooltip("SORT_ascend") ?> onclick="redir('<?= $urlSort.'@@asc' ?>')">
							<img src="app/img/<?= $imgDesc ?>" <?= Txt::tooltip("SORT_descend") ?> onclick="redir('<?= $urlSort.'@@desc' ?>')">
						</div>
					</div>
				<?php
				}
			}
			?>
		</div>
	</div>
</div>