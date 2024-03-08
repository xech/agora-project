<script>
////	Resize
lightboxSetWidth(850);

////	INIT
$(function(){
	////	Archive la news si ya une date de mise en ligne (futur)
	$(".dateBegin").on("change",function(){
		//date d'aujourd'hui en ms
		var dateBegin=this.value.split("/");
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
.dateBegin::placeholder, .dateEnd::placeholder	{font-size:0.9em;}/*Taille du "placeholder"*/
/*MOBILE FANCYBOX (440px)*/
@media screen and (max-width:440px){
	.dateBegin, .dateEnd	{width:125px!important;}/*surcharge*/
	.dateBegin::placeholder, .dateEnd::placeholder	{font-size:0.7em;}/*Taille du "placeholder"*/
}
</style>


<form action="index.php" method="post" id="mainForm" enctype="multipart/form-data">
	
	<!--TITRE MOBILE-->
	<?= $curObj->titleMobile("DASHBOARD_addNews") ?>

	<!--DESCRIPTION -->
	<?= $curObj->editDescription(false) ?>

	<div id="newsOptions">
		<!--A LA UNE-->
		<div>
			<input type="checkbox" name="une" value="1" id="uneCheckbox" <?= $curObj->une==1?"checked":"" ?>>
			<label for="uneCheckbox" title="<?= Txt::trad("DASHBOARD_topNewsTooltip") ?>"><img src="app/img/dashboard/topNews.png"> <?= Txt::trad("DASHBOARD_topNews") ?></label>	
		</div>
		<!--IS OFFLINE-->
		<div>
			<input type="checkbox" name="offline" value="1" id="offlineCheckbox" <?= $curObj->offline==1?"checked":null ?>>
			<label for="offlineCheckbox"><img src="app/img/dashboard/newsOffline.png"> <?= Txt::trad("DASHBOARD_offline") ?></label>
		</div>
		<!--DATE ONLINE-->
		<div>
			<img src="app/img/dashboard/dateOnline.png">
			<input type="text" name="dateOnline" class="dateBegin" value="<?= Txt::formatDate($curObj->dateOnline,"dbDatetime","inputDate") ?>" placeholder="<?= Txt::trad("DASHBOARD_dateOnline") ?>" title="<?= Txt::trad("DASHBOARD_dateOnlineTooltip") ?>">
		</div>
		<!--DATE OFFLINE-->
		<div>
			<img src="app/img/dashboard/dateOffline.png">
			<input type="text" name="dateOffline" class="dateEnd" value="<?= Txt::formatDate($curObj->dateOffline,"dbDatetime","inputDate") ?>" placeholder="<?= Txt::trad("DASHBOARD_dateOffline") ?>" title="<?= Txt::trad("DASHBOARD_dateOfflineTooltip") ?>">
		</div>
	</div>

	<!--MENU COMMUN & SUBMIT & CONTROLE DU FORM-->
	<?= $curObj->editMenuSubmit() ?>
</form>