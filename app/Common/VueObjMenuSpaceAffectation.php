<script>
////	Selectionne un espace
$(function(){
	var divSpaces="#spaceListMenu<?= $curObj->_targetObjId ?>";//Selecteur du menu des espaces
	$(divSpaces+" :checkbox").change(function(){
		if($(this).val()=="all")	{$(divSpaces+" :checkbox").not(this).prop("checked",false);}	//Déselectionne chaque espace si "tous les espaces" est sélectionné
		else						{$(divSpaces+" :checkbox[value='all']").prop("checked",false);}	//Déselectionne "tous les espaces" si un espace est sélectionné
	});
});
</script>

<style>
[id^='spaceListMenu']	{margin-top:10px; overflow:auto; max-height:100px; <?= $displayMenu==false?"display:none;":null ?>}
.spaceListAffectation	{display:inline-block; width:49%; padding:3px;}
label[data-value='all']	{font-style:italic;}
</style>

<div id="spaceListMenu<?= $curObj->_targetObjId ?>">
	<?php
	////LISTE DES ESPACES
	foreach($spaceList as $tmpSpace){
		$boxId="box".$curObj->_targetObjId.$tmpSpace->_targetObjId;
		echo "<div class='spaceListAffectation'>
				<input type='checkbox' name='spaceList[]' value=\"".$tmpSpace->_id."\" id=\"".$boxId."\" ".$tmpSpace->checked.">
				<label for=\"".$boxId."\" data-value=\"".$tmpSpace->_id."\">".$tmpSpace->name."</label>
			  </div>";
	}
	?>
</div>