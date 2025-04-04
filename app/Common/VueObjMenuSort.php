<style>
.menuSortAscDesc		{text-align:right!important;}
.menuSortAscDesc img	{cursor:pointer;}
</style>

<div class="menuLine">
	<div class="menuIcon"><img src="app/img/sort.png"></div>
	<div>
		<span class="menuLaunch" for="menuSort<?= $menuSortId=Txt::uniqId() ?>"><?= Txt::trad("sortBy")." ".Txt::trad("SORT_".$curSortField) ?> <img src="app/img/sort<?= ucfirst($curSortAscDesc)?>.png"></span>
		<div  class="menuContext" id="menuSort<?= $menuSortId ?>">
			<?php
			//Affiche chaque option de Tri
			foreach($sortFields as $tmpSort)
			{
				//Affiche le "field" qu'une fois (car présent 2 fois dans le $sortFields : "asc" et "desc")
				$tmpSortTab=Txt::txt2tab($tmpSort);
				$fieldTmp=$tmpSortTab[0];
				if(empty($fieldLast) || $fieldLast!=$fieldTmp)
				{
					//Init l'affichage de l'option
					$classLabel	=($fieldTmp==$curSortField)  ?  "linkSelect"  :  null;
					$imgAsc		=($fieldTmp==$curSortField && $curSortAscDesc=="asc")   ?  "sortAscSelect.png"  :  "sortAsc.png";
					$imgDesc	=($fieldTmp==$curSortField && $curSortAscDesc=="desc")  ?  "sortDescSelect.png"  :  "sortDesc.png";
					$urlSort=Tool::getParamsUrl("sort").$addUrlParams."&sort=".$fieldTmp."@@";//Prépare l'url des redirections
					//Affiche l'option : Champ avec les images "Asc" et "Desc"
					echo '<div class="menuLine">
							<div class="'.$classLabel.'" onclick="redir(\''.$urlSort.($curSortAscDesc=="asc"?"desc":"asc").'\')">'.Txt::trad("sortBy2").' '.Txt::trad("SORT_".$fieldTmp).'</div>
							<div class="menuSortAscDesc">
								<img src="app/img/'.$imgAsc.'" '.Txt::tooltip("SORT_ascend").' onclick="redir(\''.$urlSort.'asc\')">
								<img src="app/img/'.$imgDesc.'" '.Txt::tooltip("SORT_descend").' onclick="redir(\''.$urlSort.'desc\')">
							</div>
						</div>';
				}
				//Retient le dernier "field" listé
				$fieldLast=$fieldTmp;
			}
			?>
		</div>
	</div>
</div>