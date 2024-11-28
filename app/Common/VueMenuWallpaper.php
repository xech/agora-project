<script>
$(function(){
	/*******************************************************************************************
	 *	CHANGE DE WALLPAPER
	 *******************************************************************************************/
	$("select[name='wallpaper']").on("change click",function(){
		$("#wallpaperImg,#wallpaperAdd,#wallpaperDelete").hide();					//Réinit les valeurs
		if(this.value=="add")  {$("#wallpaperAdd").show();}							//Input pour "Ajouter" un Wallpaper  OU  Affiche la vignette du wallpaper courant
		else{
			var filePath=$('option[value="'+this.value+'"]').attr('data-filePath');	//Path du wallpaper courant
			$("#wallpaperImg img").attr("src",filePath);							//Modifie le "src" du wallpaper
			$("#wallpaperImg").show();												//Affiche le conteneur wallpaper
			if(/<?= WALLPAPER_DEFAULT_DB_PREFIX ?>/i.test(this.value)==false && this.value.length>0)  {$("#wallpaperDelete").show();}//Ajoute l'option de suppression si c'est un Wallpaper "custom" (sans "DB_PREFIX")
		}
	}).trigger("click");//Paramétrage général : Trigger au chargement de la page pour afficher si besoin le "wallpaperDelete"
});

/*******************************************************************************************
 *	SUPPRESSION D'UN WALLPAPER
*******************************************************************************************/
function wallpaperDelete()
{
	confirmDelete("?ctrl=<?= Req::$curCtrl ?>&action=<?= Req::$curAction ?>&deleteCustomWallpaper="+$("select[name='wallpaper']").val());
}
</script>

<style>
#wallpaperMain					{display:table;}
#wallpaperSelect, #wallpaperImg, #wallpaperAdd	{display:table-cell; padding-right:8px; scrollbar-width: 30px;}
select[name='wallpaper']		{height:120px; max-width:180px;}
#wallpaperImg img 				{height:120px; max-width:none;}/*surcharge ".objField img"*/
#wallpaperAdd, #wallpaperDelete	{display:none;}
#wallpaperDelete				{font-size:0.9em;}
option[value='add']				{background:#800;color:white;}
</style>


<div id="wallpaperMain">
	<div id="wallpaperSelect">
		<select name="wallpaper" size="5">
			<?php
			//"Ajouter" un wallpaper  (paramétrage général)  OU  Wallpaper "Par défaut" du paramétrage général (edit d'espace)
			if(Req::$curCtrl=="agora")	{echo "<option value='add'>".Txt::trad("add")."</option>";}
			else						{echo "<option value='' data-filePath=\"".CtrlMisc::pathWallpaper(Ctrl::$agora->wallpaper)."\" ".(empty($curWallpaper)?"selected":null).">".Txt::trad("byDefault")."</option>";}
			//Liste les wallpapers
			foreach($wallpaperList as $cpt=>$tmpWallpaper){
				$tmpWallpaperSelect=($tmpWallpaper["value"]==$curWallpaper || ($cpt==0 && Req::$curCtrl=="agora" && empty($curWallpaper)))  ?  "selected"  :  null;
				echo "<option value=\"".$tmpWallpaper["value"]."\" data-filePath=\"".$tmpWallpaper["path"]."\" ".$tmpWallpaperSelect.">".$tmpWallpaper["name"]."</option>";
			}
			?>
		</select>
	</div>
	<div id="wallpaperImg"><img src="<?= CtrlMisc::pathWallpaper($curWallpaper) ?>"></div>
	<div id="wallpaperAdd"><input type="file" name="wallpaperFile" id="wallpaperFile"></div>
	<?php if(Req::$curCtrl=="agora"){ ?><div id="wallpaperDelete" onclick="wallpaperDelete()"><img src="app/img/delete.png"><?= Txt::trad("AGORA_deleteWallpaper") ?></div><?php } ?>
</div>