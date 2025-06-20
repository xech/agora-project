<script>
////	Init
ready(function(){
	////	Focus du champ de recherche
	$("[name='searchText']").focusAlt();

	////	Recherche avancée
	$("#advancedSearchLabel").on("click",function(){
		$(".advancedSearchBlock").toggle();
		if($("[name=advancedSearch]").val()==1)	{$("[name=advancedSearch]").val(0);}
		else									{$("[name=advancedSearch]").val(1);}
	});

	////	Contrôle du formulaire
	$("form").on("submit",function(){
		//Vérif que les mots recherchés (au moins 3 caracteres alphanumeriques)  &&  Au moins un module et champ de recherche sont sélectionnés (cf. recherche avancée)
		var searchText=$("[name=searchText]").val();
		if(searchText.length<3 || /[\w]/.test(searchText)==false)													{notify("<?= Txt::trad("searchSpecifyText") ?>");  return false;}
		else if($("[name='searchModules[]']:checked").isEmpty() || $("[name='searchFields[]']:checked").isEmpty())	{notify("<?= Txt::trad("emptyFields") ?>");  return false;}
	});
});
</script>


<style>
#bodyLightbox						{max-width:700px;}
input[name="searchText"]			{width:250px; margin-right:5px;}
#advancedSearchLabel				{margin-left:30px; line-height:30px;}
.advancedSearchBlock				{display:<?= Req::param("advancedSearch")?"block":"none" ?>;}
.vAdvancedSearchTab					{display:table; margin-top:20px;}
.vAdvancedSearchTab>div				{display:table-cell;}
.vAdvancedSearchTab>div:first-child	{width:120px; padding-top:5px;}
.vAdvancedSearchOption				{display:inline-block; width:32%; padding:3px;}
.vModuleLabel						{text-align:center; padding-top:20px;}
.vModuleLabel img					{max-height:28px; margin-right:8px;}
.menuLine							{padding:5px;}
.menuLine mark						{padding:2px;}/*mots de la recherche surlignés*/
.menuLine .vContextMenu				{width:50px; vertical-align:top;}
.vPluginNews						{display:none; padding:5px; background:#eee; border-radius:5px; cursor:default;}/*affichage complet d'une news*/
.emptyContainer						{margin-top:20px;}

/*RESPONSIVE SMARTPHONE*/
@media screen and (max-width:490px){	
	#searchMainField			{text-align:center;}
	input[name="searchText"]	{margin-bottom:15px;}
	#advancedSearchLabel		{display:block; margin-top:15px;}
	.vAdvancedSearchOption		{width:48%;}
}
</style>


<!--FORMULAIRE DE RECHERCHE-->
<form action="index.php" method="post">
	<div class="lightboxTitle"><?= Txt::trad("searchOnSpace") ?></div>

	<div id="searchMainField">
		<?= Txt::trad("keywords") ?>
		<input type="text" name="searchText" value="<?= isset($_SESSION["searchText"]) ? $_SESSION["searchText"] : null ?>">
		<?= Txt::submitButton("search",false) ?>
		<label id="advancedSearchLabel"><?= Txt::trad("advancedSearch") ?> <img src="app/img/arrowBottom.png"></label>
		<input type="hidden" name="advancedSearch" value="<?= Req::param("advancedSearch") ?>">
	</div>

	<div class="advancedSearchBlock">
		<!--MODE DE RECHERCHE-->
		<div class="vAdvancedSearchTab">
			<div><?= Txt::trad("search") ?></div>
			<div>
				<select name="searchMode">
					<?php foreach(["anyWord","allWords","exactPhrase"] as $tmpOption)	{echo "<option value=\"".$tmpOption."\" ".(Req::param("searchMode")==$tmpOption?'selected':null).">".Txt::trad("advancedSearch".ucfirst($tmpOption))."</option>";} ?>
				</select>
			</div>
		</div>
		<!--DATE DE CREATION-->
		<div class="vAdvancedSearchTab">
			<div><?= Txt::trad("searchDateCrea") ?></div>
			<div>
				<select name="creationDate">
					<option value="all"><?= Txt::trad("all") ?></option>
					<?php foreach(["day"=>1,"week"=>7,"month"=>31,"year"=>365] as $tmpLabel=>$tmpValue)	{echo "<option value=\"".$tmpValue."\" ".(Req::param("creationDate")==$tmpValue?'selected':null).">".Txt::trad("searchDateCrea".ucfirst($tmpLabel))."</option>";} ?>
				</select>
			</div>
		</div>
		<!--SELECTION DE MODULES-->
		<div class="vAdvancedSearchTab">
			<div><?= Txt::trad("listModules") ?></div>
			<div>
				<?php
				foreach(Ctrl::$curSpace->moduleList() as $tmpModule){
					if(method_exists($tmpModule["ctrl"],"getPlugins")){
						$moduleChecked=(Req::isParam("searchModules")==false || in_array($tmpModule["moduleName"],Req::param("searchModules")))  ?  "checked='checked'"  :  "";
						$moduleInputId="searchModules".$tmpModule["moduleName"];
						$moduleName=Txt::trad(strtoupper($tmpModule["moduleName"])."_MODULE_NAME");
						echo "<div class='vAdvancedSearchOption'><input type='checkbox' name='searchModules[]' value='".$tmpModule["moduleName"]."' id='".$moduleInputId."' ".$moduleChecked."><label for='".$moduleInputId."'>".$moduleName."</label></div>";
					}
				}
				?>
			</div>
		</div>
		<!--SELECTION DES CHAMPS DE RECHERCHE-->
		<div class="vAdvancedSearchTab">
			<div><?= Txt::trad("listFields") ?></div>
			<div>
				<?php
				foreach($searchFields as $fieldName=>$fieldParams){
					$fieldTitle=Txt::trad("listFieldsElems")." :<br>".$fieldParams["title"];
					$fieldInputId="searchFields".$fieldName;
					echo "<div class='vAdvancedSearchOption' ".Txt::tooltip($fieldTitle)."><input type='checkbox' name=\"searchFields[]\" value=\"".$fieldName."\" id='".$fieldInputId."' ".$fieldParams["checked"]."><label for='".$fieldInputId."'>".Txt::trad($fieldName)."</label></div>";
				}
				?>
			</div>
		</div>
	</div>
</form>


<!--RESULTATS DE LA RECHERCHE-->
<?php
if(Req::isParam("searchText"))
{
	//Résultats à afficher
	foreach($pluginsList as $tmpObj)
	{
		//// Header des plugins du module : affiche si besoin le libellé du module
		if(empty($tmpModuleName) || $tmpModuleName!=$tmpObj::moduleName){
			echo "<div class='vModuleLabel'><hr><img src='app/img/".$tmpObj::moduleName."/icon.png'>".Txt::trad(strtoupper($tmpObj::moduleName)."_MODULE_NAME")."</div>";
			$tmpModuleName=$tmpObj::moduleName;
		}
		//// Label des objets lambda  ||  "dashboardNews" : "reduce()" du label/description + possibilité de l'afficher entièrement + menu contextuel
		if($tmpObj::objectType!="dashboardNews")  {$pluginLabel=$tmpObj->pluginLabel;}
		else{
			$pluginLabel="<div onclick=\"$(this).hide();$('#pluginNews".$tmpObj->_id."').slideDown();\">".Txt::reduce($tmpObj->pluginLabel)." <img src='app/img/arrowBottom.png'></div>
						  <div class='vPluginNews' id='pluginNews".$tmpObj->_id."'>".$tmpObj->description."</div>";
		}
		//// Surligne le texte ou les mots recherchés
		$searchText=html_entity_decode(Req::param("searchText"));											//Décode les accents de l'éditeur (&agrave; &egrave; etc)
		$pluginLabel=preg_replace("/".$searchText."/i", "<mark>".$searchText."</mark>", $pluginLabel);		//Surligne l'expression exacte en gardant la casse (pas avec "str_ireplace")
		//// Affiche le plugin
		echo '<div class="menuLine lineHover">
				<div onclick="'.$tmpObj->pluginJsIcon.'" class="menuIcon"><img src="app/img/'.$tmpObj->pluginIcon.'"></div>
				<div onclick="'.$tmpObj->pluginJsLabel.'" '.Txt::tooltip($tmpObj->pluginTooltip).'>'.$pluginLabel.'</div>
				<div class="vContextMenu">'.$tmpObj->contextMenu(["launcherIcon"=>"inlineSmall"]).'</div>
			  </div>';
	}
	//Aucun résultat à afficher
	if(empty($pluginsList))  {echo '<div class="miscContainer emptyContainer">'.Txt::trad("noResults").'</div>';}
}