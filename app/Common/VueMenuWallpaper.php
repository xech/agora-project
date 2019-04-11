<script>
////	Init la page
$(function(){
	//Change dans la liste : "par défaut" / "ajouter" / Affiche le wallpaper
	$("select[name='wallpaper']").change(function(){
		$("#wallpaperImg,#wallpaperAdd,#wallpaperDelete").hide();
		if(this.value=="add")	{$("#wallpaperAdd").show();}
		else{
			$("#wallpaperImg").show();
			var filePath=(this.value=="")  ?  "<?= CtrlMisc::pathWallpaper(null) ?>"  :  $("option[value='"+this.value+"']").attr("data-filePath");
			$("#wallpaperImg img").prop("src",filePath);
			if(!find("<?= WALLPAPER_DEFAULT_PREFIX ?>",this.value) && this.value!="")	{$("#wallpaperDelete").show();}
		}
	});
});
////	Suppression d'un Wallpaper
function wallpaperDelete()
{
	confirmDelete("?ctrl=<?= Req::$curCtrl ?>&action=<?= Req::$curAction ?>&deleteCustomWallpaper="+$("select[name='wallpaper']").val());
}
</script>

<style>
#wallpaperMain					{display:table;}
#wallpaperSelect, #wallpaperImg, #wallpaperAdd	{display:table-cell; padding-right:10px;}
select[name='wallpaper']		{height:90px; max-width:150px;}
#wallpaperImg img				{height:90px;}
#wallpaperAdd, #wallpaperDelete	{display:none;}
option[value='add']				{background-color:#800;color:#fff;}
</style>

<div id="wallpaperMain">
	<div id="wallpaperSelect">
		<select name="wallpaper" size="5">
			<?php
			//"Par défaut" / "Ajouter"
			if(Req::$curCtrl=="agora")	{echo "<option value='add'>".Txt::trad("add")."</option>";}
			else						{echo "<option value='' ".(empty($curWallpaper)?"selected":null).">".Txt::trad("byDefault")."</option>";}
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
	<?php if(Req::$curCtrl=="agora"){ ?>
	<div id="wallpaperDelete" class="sLink" onclick="wallpaperDelete()"><img src="app/img/delete.png"><?= Txt::trad("AGORA_deleteWallpaper") ?></div>
	<?php } ?>
</div>