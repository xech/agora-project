<script>
////	Resize
lightboxSetWidth(800);

////	INIT
$(function(){
	////	Archive la news si ya une date de mise en ligne (futur)
	$(".dateBegin").change(function(){
		//date d'aujourd'hui en ms
		var dateBegin=$(this).val().split("/");
		var timeBegin=new Date(dateBegin[1]+"/"+dateBegin[0]+"/"+dateBegin[2]);//attention au format : mois/jour/annee
		//Date "online" spécifié && supérieure à aujourd'hui &&  "offline" pas sélectionné
		if($(".dateBegin").isEmpty()==false && Date.now() < timeBegin.valueOf() && $("[name='offline']").prop("checked")==false){
			notify("<?= Txt::trad("DASHBOARD_dateOnlineNotif") ?>");
			$("[name='offline']").trigger("click");
		}
	});
});
</script>

<style>
#newsOptions			{margin-top:22px; text-align:center;}
#newsOptions>div		{display:inline-block; margin-right:20px; margin-top:15px;}
#newsOptions img		{vertical-align:bottom;}
.dateBegin, .dateEnd	{width:160px!important;}/*surcharge*/
.dateBegin::placeholder, .dateEnd::placeholder	{font-size:0.9em;}/*pour afficher le "placeholder"*/
input[name='une'],input[name='offline']	{display:none;}
</style>

<form action="index.php" method="post" onsubmit="return mainFormControl()" enctype="multipart/form-data" class="lightboxContent">
	
	<!--TITRE RESPONSIVE-->
	<?php echo $curObj->editRespTitle("DASHBOARD_addNews"); ?>

	<!--DESCRIPTION (EDITOR)-->
	<textarea name="description"><?= $curObj->description ?></textarea>

	<div id="newsOptions">
		<!--A LA UNE-->
		<div>
			<img src="app/img/dashboard/topNews.png">
			<input type="checkbox" name="une" value="1" id="uneCheckbox" <?= $curObj->une==1?"checked":"" ?>>
			<label for="uneCheckbox" title="<?= Txt::trad("DASHBOARD_topNewsInfo") ?>"><?= Txt::trad("DASHBOARD_topNews") ?></label>	
		</div>
		<!--IS OFFLINE-->
		<div>
			<img src="app/img/dashboard/newsOffline.png">
			<input type="checkbox" name="offline" value="1" id="offlineCheckbox" <?= $curObj->offline==1?"checked":null ?>>
			<label for="offlineCheckbox"><?= Txt::trad("DASHBOARD_offline") ?></label>
		</div>
		<!--DATE ONLINE-->
		<div>
			<img src="app/img/dashboard/dateOnline.png">
			<input type="text" name="dateOnline" class="dateBegin" value="<?= Txt::formatDate($curObj->dateOnline,"dbDatetime","inputDate") ?>" placeholder="<?= Txt::trad("DASHBOARD_dateOnline") ?>" title="<?= Txt::trad("DASHBOARD_dateOnlineInfo") ?>">
		</div>
		<!--DATE OFFLINE-->
		<div>
			<img src="app/img/dashboard/dateOffline.png">
			<input type="text" name="dateOffline" class="dateEnd" value="<?= Txt::formatDate($curObj->dateOffline,"dbDatetime","inputDate") ?>" placeholder="<?= Txt::trad("DASHBOARD_dateOffline") ?>" title="<?= Txt::trad("DASHBOARD_dateOfflineInfo") ?>">
		</div>
	</div>

	<!--MENU COMMUN-->
	<?= $curObj->menuEdit() ?>
</form>