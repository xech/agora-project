<script>
////	Selectionne un espace
$(function(){
	var divSpaces="#spaceListMenu<?= $curObj->_typeId ?>";//Selecteur du menu des espaces
	$(divSpaces+" :checkbox").change(function(){
		if(this.value=="all")	{$(divSpaces+" :checkbox").not(this).prop("checked",false);}	//Déselectionne chaque espace si "tous les espaces" est sélectionné
		else					{$(divSpaces+" :checkbox[value='all']").prop("checked",false);}	//Déselectionne "tous les espaces" si un espace est sélectionné
	});
});
</script>

<style>
[id^='spaceListMenu']	{margin-top:10px; overflow:auto; max-height:100px; <?= $displayMenu==false?"display:none;":null ?>}
.spaceListAffectation	{display:inline-block; width:49%; padding:3px;}
label[data-value='all']	{font-style:italic;}

/*RESPONSIVE FANCYBOX (440px)*/
@media screen and (max-width:440px){
	.spaceListAffectation  {display:block; width:98%; padding:5px;}
}
</style>

<div id="spaceListMenu<?= $curObj->_typeId ?>">
	<?php
	////LISTE DES ESPACES
	foreach($spaceList as $tmpSpace){
		$boxId="box".$curObj->_typeId.$tmpSpace->_typeId;
		echo "<div class='spaceListAffectation'>
				<input type='checkbox' name='spaceList[]' value=\"".$tmpSpace->_id."\" id=\"".$boxId."\" ".$tmpSpace->checked.">
				<label for=\"".$boxId."\" data-value=\"".$tmpSpace->_id."\">".$tmpSpace->name."</label>
			  </div>";
	}
	?>
</div>