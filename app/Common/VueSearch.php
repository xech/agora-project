<script>
////	Resize
lightboxSetWidth(680);

////	INIT
$(function(){
	//Focus du champ (pas en responsive pour ne pas afficher le clavier virtuel)
	if(!isMobile())  {$("[name='searchText']").focus();}
});

////	Contrôle du formulaire
function formControl()
{
	//Texte à rechercher && Champs de recherche avancée
	if($("[name=searchText]").val().length<3)  {notify("<?= Txt::trad("searchSpecifyText") ?>");  return false;}
	if($("input[name='searchModules[]']:checked").isEmpty() || $("input[name='searchFields[]']:checked").isEmpty())  {notify("<?= Txt::trad("fillAllFields") ?>");  return false;}
}

////	Recherche avancée
function displayAdvancedSearch()
{
	$(".advancedSearchBlock").toggle();
	if($("[name=advancedSearch]").val()==1)	{$("[name=advancedSearch]").val(0);}
	else									{$("[name=advancedSearch]").val(1);}
}
</script>

<style>
input[name="searchText"]			{width:250px; margin-right:5px;}
#advancedSearchLabel				{margin-left:30px; line-height:30px;}
.advancedSearchBlock				{display:<?= Req::getParam("advancedSearch")?"block":"none" ?>;}
.vAdvancedSearchTab					{display:table; margin-top:20px;}
.vAdvancedSearchTab>div				{display:table-cell;}
.vAdvancedSearchTab>div:first-child	{width:120px; padding-top:5px;}
.vAdvancedSearchOption				{display:inline-block; width:32%; padding:3px;}
.vModuleLabel						{text-align:center; padding-top:20px;}
.vModuleLabel img					{max-height:28px; margin-right:8px;}
.menuLine							{padding:5px;}
.vSearchResultWord					{color:#900; text-decoration:underline;}/*mots surlignés dans le résultat de recherche*/
.vPluginNews						{display:none; padding:10px; background-color:#eee; border-radius:5px; cursor:default;}/*affichage complet d'une news*/
.vPluginNews .objMenuBurgerInline	{float:right;}/*menu contextuel des news*/

/*RESPONSIVE FANCYBOX (440px)*/
@media screen and (max-width:440px){	
	#searchMainField			{text-align:center;}
	input[name="searchText"]	{margin-bottom:15px;}
	#advancedSearchLabel		{display:block; margin-top:15px;}
	.vAdvancedSearchOption		{width:48%;}
}
</style>


<form action="index.php" method="post" OnSubmit="return formControl()" class="lightboxContent noConfirmClose">
	<div class="lightboxTitle"><?= Txt::trad("searchOnSpace") ?></div>

	<div id="searchMainField">
		<?= Txt::trad("keywords") ?>
		<input type="text" name="searchText" value="<?= isset($_SESSION["searchText"]) ? $_SESSION["searchText"] : null ?>">
		<?= Txt::submitButton("search",false) ?>
		<label id="advancedSearchLabel" onclick="displayAdvancedSearch();" class="sLink"><?= Txt::trad("advancedSearch") ?> <img src="app/img/arrowBottom.png"></label>
		<input type="hidden" name="advancedSearch" value="<?= Req::getParam("advancedSearch") ?>">
	</div>

	<div class="advancedSearchBlock">
		<!--MODE DE RECHERCHE-->
		<div class="vAdvancedSearchTab">
			<div><?= Txt::trad("search") ?></div>
			<div>
				<select name="searchMode">
					<?php foreach(["anyWord","allWords","exactPhrase"] as $tmpOption)	{echo "<option value=\"".$tmpOption."\" ".(Req::getParam("searchMode")==$tmpOption?'selected':null).">".Txt::trad("advancedSearch".ucfirst($tmpOption))."</option>";} ?>
				</select>
			</div>
		</div>
		<!--DATE DE CREATION-->
		<div class="vAdvancedSearchTab">
			<div><?= Txt::trad("searchDateCrea") ?></div>
			<div>
				<select name="creationDate">
					<option value="all"><?= Txt::trad("all") ?></option>
					<?php foreach(["day","week","month","year"] as $tmpOption)	{echo "<option value=\"".$tmpOption."\" ".(Req::getParam("creationDate")==$tmpOption?'selected':null).">".Txt::trad("searchDateCrea".ucfirst($tmpOption))."</option>";} ?>
				</select>
			</div>
		</div>
		<!--SELECTION DE MODULES-->
		<div class="vAdvancedSearchTab">
			<div><?= Txt::trad("listModules") ?></div>
			<div>
				<?php
				foreach(self::$curSpace->moduleList() as $tmpModule){
					if(method_exists($tmpModule["ctrl"],"getModPlugins")){
						$moduleChecked=(Req::isParam("searchModules")==false || in_array($tmpModule["moduleName"],Req::getParam("searchModules")))  ?  "checked='checked'"  :  "";
						$moduleInputId="searchModules".$tmpModule["moduleName"];
						$moduleName=Txt::trad(strtoupper($tmpModule["moduleName"])."_headerModuleName");
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
					echo "<div class='vAdvancedSearchOption' title=\"".$fieldTitle."\"><input type='checkbox' name=\"searchFields[]\" value=\"".$fieldName."\" id='".$fieldInputId."' ".$fieldParams["checked"]."><label for='".$fieldInputId."'>".Txt::trad($fieldName)."</label></div>";
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
	$searchTextList=explode(" ",Req::getParam("searchText"));
	foreach($pluginsList as $tmpObj)
	{
		//// Affiche le libellé du module?
		if(empty($tmpModuleName) || $tmpModuleName!=$tmpObj->pluginModule){
			echo "<div class='vModuleLabel'><hr><img src='app/img/".$tmpObj->pluginModule."/icon.png'>".Txt::trad(strtoupper($tmpObj->pluginModule)."_headerModuleName")."</div>";
			$tmpModuleName=$tmpObj->pluginModule;
		}
		//// Label principal :  Objet lambda  ||  News du dashboard : version "reduce()" par défaut et possibilité d'afficher la version complete avec menu contextuel
		if($tmpObj::objectType!="dashboardNews")  {$pluginLabel=Txt::reduce(strip_tags($tmpObj->pluginLabel));}
		else{
			$pluginLabel="<span onclick=\"$(this).slideUp();$('#pluginNews".$tmpObj->_id."').slideDown();\">".Txt::reduce(strip_tags($tmpObj->pluginLabel))." <img src='app/img/arrowBottom.png'></span>
						  <div class='vPluginNews' id='pluginNews".$tmpObj->_id."'>".$tmpObj->contextMenu(["iconBurger"=>"small"])." ".$tmpObj->description."</div>";
		}
		//// Surligne les mots recherchés dans le label des résultats
		foreach($searchTextList as $tmpText)  {$pluginLabel=preg_replace("/".Txt::clean($tmpText)."/i", "<span class='vSearchResultWord'>".$tmpText."</span>", $pluginLabel);}
		//// Affiche les plugins "search" ("reduce()" pour réduire la taille du texte et des tags html, surtout sur le label principal)
		echo "<div class='menuLine sLink objHover' title=\"".Txt::reduce($tmpObj->pluginTooltip,400)."\">
				<div onclick=\"".$tmpObj->pluginJsIcon."\" class='menuIcon'><img src='app/img/".$tmpObj->pluginIcon."'></div>
				<div onclick=\"".$tmpObj->pluginJsLabel."\">".$pluginLabel."</div>
			  </div>";
	}
	//Aucun résultat à afficher
	if(empty($pluginsList))  {echo "<div class='emptyContainer'>".Txt::trad("noResults")."</div>";}
}