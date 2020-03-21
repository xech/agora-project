<script>
////	Resize
lightboxSetWidth(650);

////	INIT
$(function(){
	//Focus du champ (pas en responsive pour ne pas afficher le clavier virtuel)
	if(!isMobile())  {$("[name='searchText']").focus();}
});

////	Contrôle du formulaire
function formControl()
{
	//champ de recherche
	if($("[name=searchText]").val().length<3)
		{notify("<?= Txt::trad("searchSpecifyText") ?>");  return false;}
	//Recherche avancée
	if($("[name=advancedSearch]").val()==1 && ($("input[name='searchModules[]']:checked").isEmpty() || $("input[name='searchFields[]']:checked").isEmpty()))
		{notify("<?= Txt::trad("fillAllFields") ?>");  return false;}
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
input[name="searchText"]	{width:220px; margin-right:5px;}
#advancedSearchLabel		{margin-left:30px; line-height:30px;}
.advancedSearchBlock		{display:<?= Req::getParam("advancedSearch")?"block":"none" ?>;}
.vAdvancedSearchTab			{display:table; margin-top:15px;}
.vAdvancedSearchTab>div		{display:table-cell;}
.vAdvancedSearchTab>div:first-child	{width:110px;}
.vAdvancedSearchOption		{display:inline-block; width:32%; float:left; padding:3px;}
.vModuleLabel				{text-align:center; padding-top:20px;}
.vModuleLabel img			{max-height:28px; margin-right:8px;}
.menuLine					{padding:5px;}
.vSearchResultWord			{color:#900; text-decoration:underline;}

/*RESPONSIVE FANCYBOX (440px)*/
@media screen and (max-width:440px){	
	input[name="searchText"]	{width:150px; margin-right:5px;}
	#advancedSearchLabel		{display:block; margin-top:15px;}
	.vAdvancedSearchOption		{width:48%;}
}
</style>


<form action="index.php" method="post" OnSubmit="return formControl()" class="lightboxContent noConfirmClose">
	<div class="lightboxTitle"><?= Txt::trad("searchOnSpace") ?></div>

	<div id="searchMainField">
		<?= Txt::trad("keywords") ?>
		<input type="text" name="searchText" value="<?= isset($_SESSION["searchText"]) ? $_SESSION["searchText"] : null ?>">
		<?= Txt::submit("search",false) ?>
		<label id="advancedSearchLabel" onclick="displayAdvancedSearch();" class="sLink"><?= Txt::trad("advancedSearch") ?> <img src="app/img/plusSmall.png"></label>
		<input type="hidden" name="advancedSearch" value="<?= Req::getParam("advancedSearch") ?>">
	</div>

	<div class="advancedSearchBlock">
		<!--MODE DE RECHERCHE-->
		<div class="vAdvancedSearchTab">
			<div><?= Txt::trad("search") ?></div>
			<div>
				<select name="searchMode">
					<?php foreach(["someWords","allWords","exactPhrase"] as $tmpOption)	{echo "<option value=\"".$tmpOption."\" ".(Req::getParam("searchMode")==$tmpOption?'selected':null).">".Txt::trad("advancedSearch".ucfirst($tmpOption))."</option>";} ?>
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
					if(method_exists($tmpModule["ctrl"],"plugin")){
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
	$searchTexts=explode(" ",Req::getParam("searchText"));
	foreach($pluginsList as $tmpPlugin)
	{
		//Affiche le libellé du module?
		if(empty($tmpModuleName) || $tmpModuleName!=$tmpPlugin->pluginModule){
			echo "<div class='vModuleLabel'><img src='app/img/".$tmpPlugin->pluginModule."/icon.png'>".Txt::trad(strtoupper($tmpPlugin->pluginModule)."_headerModuleName")."<hr></div>";
			$tmpModuleName=$tmpPlugin->pluginModule;
		}
		//Réduit le label & suppr toutes les balises html (sauf pour les "news" qui s'affichent intégralement, avec un "contextMenu")
		if($tmpPlugin->pluginModule!="dashboard")  {$tmpPlugin->pluginLabel=Txt::reduce(strip_tags($tmpPlugin->pluginLabel),100);}
		//Surligne les mots recherchés dans le label des résultats
		foreach($searchTexts as $searchText)  {$tmpPlugin->pluginLabel=preg_replace("/".$searchText."/i", "<span class='vSearchResultWord'>".$searchText."</span>", $tmpPlugin->pluginLabel);}
		//Affiche les plugins "search"
		echo "<div class='menuLine sLink objHover'>
					<div class='menuIcon' onclick=\"".$tmpPlugin->pluginJsIcon."\"><img src='app/img/".$tmpPlugin->pluginIcon."'></div>
					<div title=\"".$tmpPlugin->pluginTooltip."\" onclick=\"".$tmpPlugin->pluginJsLabel."\">".$tmpPlugin->pluginLabel."</div>
			  </div>";
	}
	//Aucun résultat à afficher
	if(empty($pluginsList))  {echo "<div class='emptyContainer'>".Txt::trad("noResults")."</div>";}
}