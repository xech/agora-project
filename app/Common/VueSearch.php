<script>
////	Init
ready(function(){
	////	Focus du champ de recherche
	$("[name='searchText']").focusAlt();

	////	Contrôle du formulaire : Vérif qu'il y ait au moins 3 caracteres alphanumeriques (accent espace apostrophe point tiret acceptés)
	$("form").on("submit",function(){
		const regex = /^[\p{L}\p{M}\d' \-\.]{3,}$/u;
		if(regex.test($("[name=searchText]").val())==false)   {notify("<?= Txt::trad("searchSpecifyText") ?>");  return false;}
	});
});
</script>


<style>
#bodyLightbox						{max-width:800px;}
#searchMainField					{text-align:center;}
#searchMainField *:is(input,button)	{height:40px; margin:10px;}
#searchMainField input				{width:250px;}
#searchMainField button				{width:200px;}
#advancedSearchLabel				{margin-top:20px;}
#advancedSearchBlock				{display:<?= Req::param("advancedSearch")?"block":"none" ?>;}
.vAdvancedSearchTab					{display:table; width:100%; margin-top:20px;}
.vAdvancedSearchTab>div				{display:table-cell;}
.vAdvancedSearchTab>div:first-child	{width:150px; padding-top:5px;}
.vAdvancedSearchOption				{display:inline-block; width:23%; padding:3px;}
.vModuleLabel						{text-align:center; padding-top:20px;}
.vModuleLabel img					{max-height:28px; margin-right:8px;}
.menuLine .vContextMenu				{width:30px; padding-top:10px; vertical-align:top;}
.menuLine mark						{padding:4px 2px;}/*mots surlignés dans les résultats de la recherche*/
.vPluginNews						{display:none; padding:5px; background:#eee; border-radius:5px; cursor:default;}/*affichage complet d'une news*/
.emptyContent						{margin-top:20px;}

/*AFFICHAGE SMARTPHONE*/
@media screen and (max-width:490px){	
#searchMainField *:is(input,button)	{width:300px;}
	.vAdvancedSearchTab, .vAdvancedSearchTab>div	{display:block;}
	.vAdvancedSearchTab								{margin-top:30px;}
	.vAdvancedSearchTab>div:first-child				{margin-bottom:10px;}
	.vAdvancedSearchOption							{width:48%;}
}
</style>


<!--FORMULAIRE DE RECHERCHE-->
<form action="index.php" method="post">
	<div class="lightboxTitle"><?= Txt::trad("searchOnSpace") ?></div>

	<!--CHAMP DE RECHERCHE PRINCIPAL & SUBMIT-->
	<div id="searchMainField">
		<input type="text" name="searchText" value="<?= isset($_SESSION["searchText"]) ? $_SESSION["searchText"] : null ?>" placeholder="<?= Txt::trad("keywords") ?>">
		<?= Txt::submitButton("search",false) ?>
		<div id="advancedSearchLabel" class="sLink" onclick="$('#advancedSearchBlock').show();$('#advancedSearchInput').val(1);"><?= Txt::trad("advancedSearch") ?> <img src="app/img/arrowBottom.png"></div>
		<input type="hidden" name="advancedSearch" value="<?= Req::param("advancedSearch") ?>" id="advancedSearchInput">
	</div>

	<!--RECHERCHE AVANCEE-->
	<div id="advancedSearchBlock">

		<!--DATE DE CREATION-->
		<div class="vAdvancedSearchTab">
			<div><?= Txt::trad("searchDateCrea") ?></div>
			<div>
				<select name="creationDate">
					<option value="all"><?= Txt::trad("all") ?></option>
					<?php foreach(["day"=>1,"week"=>7,"month"=>31,"year"=>365] as $tmpTrad=>$tmpValue){ ?>
						<option value="<?= $tmpValue ?>" <?= Req::param("creationDate")==$tmpValue?'selected':null ?> ><?= Txt::trad("searchDateCrea_".$tmpTrad) ?></option>
					<?php } ?>
				</select>
			</div>
		</div>

		<!--ETENDUE DE DE RECHERCHE-->
		<div class="vAdvancedSearchTab">
			<div><?= Txt::trad("search") ?></div>
			<div>
				<select name="searchMode">
					<?php foreach(["anyWord","exactPhrase"] as $tmpValue){ ?>
						<option value="<?= $tmpValue ?>" <?= Req::param("searchMode")==$tmpValue?'selected':null ?> ><?= Txt::trad("advancedSearch_".$tmpValue) ?></option>
					<?php } ?>
				</select>
			</div>
		</div>

		<!--SELECTION DE MODULES-->
		<div class="vAdvancedSearchTab">
			<div><?= Txt::trad("listModules") ?></div>
			<div>
				<?php
				foreach(Ctrl::$curSpace->moduleList() as $tmpModule){
					if(method_exists($tmpModule["ctrl"],"getPlugins")==false)  {continue;}
					$moduleInputId="searchModules".$tmpModule["moduleName"];
					$moduleChecked=(Req::isParam("searchModules")==false || in_array($tmpModule["moduleName"],Req::param("searchModules")))  ?  "checked='checked'"  :  "";
				?>
					<div class="vAdvancedSearchOption"><input type="checkbox" name="searchModules[]" value="<?= $tmpModule["moduleName"] ?>" id="<?= $moduleInputId ?>" <?= $moduleChecked ?> >
						<label for="<?= $moduleInputId ?>"><?= Txt::trad(strtoupper($tmpModule["moduleName"])."_MODULE_NAME") ?></label>
					</div>
				<?php } ?>
			</div>
		</div>

	</div>
</form>


<!--RESULTATS DE LA RECHERCHE-->
<?php
if(Req::isParam("searchText")){
	foreach($pluginsList as $tmpObj){
		////	"dashboardNews" : label réduit avec possibilité de l'afficher entièrement
		if($tmpObj::objectType=="dashboardNews"){
			$tmpObj->pluginLabel='<div onclick="$(this).hide();$(\'#pluginNews'.$tmpObj->_id.'\').slideDown()">'.Txt::reduce($tmpObj->pluginLabel).' <img src="app/img/arrowBottom.png"></div>
								  <div class="vPluginNews" id="pluginNews'.$tmpObj->_id.'">'.$tmpObj->description.'</div>';
		}
		////	Surligne le texte ou les mots recherchés
		$searchRaw=Req::param("searchText");													//Texte de recherche
    	$variants=[preg_quote($searchRaw, '/'), preg_quote(htmlentities($searchRaw), '/')];		//Texte Brut et avec les entités html (&agrave; &egrave; etc)
		$pattern="/(".implode('|',array_unique($variants)).")/i";								//Motif unique : /(variante1|variante2)/i
		$tmpObj->pluginLabel=preg_replace($pattern, "<mark>$1</mark>", $tmpObj->pluginLabel);	//$1 permet de remplacer par ce qui a été trouvé en respectant la casse
		////	Affiche si besoin le libellé du module
		if(empty($tmpModuleName) || $tmpModuleName!=$tmpObj::moduleName)	{$showModuleTitle=true;  $tmpModuleName=$tmpObj::moduleName;}
		else																{$showModuleTitle=false;}
		?>
			<?php if($showModuleTitle==true){ ?>
				<div class="vModuleLabel"><hr><img src="app/img/<?= $tmpObj::moduleName ?>/icon.png"><?= Txt::trad(strtoupper($tmpObj::moduleName)."_MODULE_NAME") ?></div>
			<?php } ?>
			<div class="menuLine lineHover">
				<div class="vContextMenu"><?= $tmpObj->contextMenu(["burgerLauncher"=>"small-inline"]) ?></div>
				<div onclick="<?= $tmpObj->pluginJsIcon ?>" class="menuIcon"><img src="app/img/<?= $tmpObj->pluginIcon ?>"></div>
				<div onclick="<?= $tmpObj->pluginJsLabel ?>" <?= Txt::tooltip($tmpObj->pluginTooltip) ?> ><?= $tmpObj->pluginLabel ?></div>
			</div>
		<?php
	}
	//Aucun résultat à afficher
	if(empty($pluginsList))  {echo '<div class="miscContent emptyContent">'.Txt::trad("noResults").'</div>';}
}