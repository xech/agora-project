<script>
////	Resize
lightboxSetWidth(850);

////	Archive la news s'il ya une date de publication automatique
////	trigger "blur" avec timeout : le temps d'executer les controles de base du trigger "change" (cf. "app.js")
ready(function(){
	$(".dateBegin").on("blur",function(){
		setTimeout(function(){
			if($(".dateBegin").notEmpty()){
				var dateBegin=$(".dateBegin").val().split("/");
				var timeBegin=new Date(dateBegin[1]+"/"+dateBegin[0]+"/"+dateBegin[2]);//Format "MM/dd/yyy"
				//Date "online" supérieure à aujourd'hui : passe si besoin l'actu en "offline"
				if(Date.now() < timeBegin.valueOf() && $("[name='offline']").prop("checked")==false){
					$("[name='offline']").trigger("click");
					notify("<?= Txt::trad("DASHBOARD_dateOnlineNotif") ?>");
				}
			}
		},200);
	});
});
</script>


<style>
#newsOptions			{margin-top:22px; text-align:center;}
#newsOptions>div		{display:inline-block; line-height:30px; margin-right:25px;}
.dateBegin, .dateEnd	{width:180px;}/*surcharge pour afficher les placeholders*/
/*MOBILE FANCYBOX (440px)*/
@media screen and (max-width:440px){
	#newsOptions>div		{display:block; margin-bottom:20px;}
	.dateBegin, .dateEnd	{width:220px;}/*surcharge pour afficher les placeholders*/
}
</style>


<form action="index.php" method="post" id="mainForm" enctype="multipart/form-data">
	
	<!--TITRE MOBILE-->
	<?= $curObj->titleMobile("DASHBOARD_addNews") ?>

	<!--DESCRIPTION -->
	<?= $curObj->descriptionEditor(false) ?>

	<div id="newsOptions">
		<!--A LA UNE-->
		<div>
			<input type="checkbox" name="une" value="1" id="uneCheckbox" <?= $curObj->une==1?"checked":"" ?>>
			<label for="uneCheckbox" title="<?= Txt::trad("DASHBOARD_topNewsTooltip") ?>"><?= Txt::trad("DASHBOARD_topNews") ?> <img src="app/img/dashboard/topNews.png"></label>	
		</div>
		<!--IS OFFLINE-->
		<div>
			<input type="checkbox" name="offline" value="1" id="offlineCheckbox" <?= $curObj->offline==1?"checked":null ?>>
			<label for="offlineCheckbox"><?= Txt::trad("DASHBOARD_offline") ?> <img src="app/img/dashboard/newsOffline.png"></label>
		</div>
		<!--DATE ONLINE-->
		<div>
			<input type="text" name="dateOnline" class="dateBegin" value="<?= Txt::formatDate($curObj->dateOnline,"dbDatetime","inputDate") ?>" placeholder="<?= Txt::trad("DASHBOARD_dateOnline") ?>" title="<?= Txt::trad("DASHBOARD_dateOnlineTooltip") ?>">
			<img src="app/img/dateEnd2.png">
		</div>
		<!--DATE OFFLINE-->
		<div>
			<input type="text" name="dateOffline" class="dateEnd" value="<?= Txt::formatDate($curObj->dateOffline,"dbDatetime","inputDate") ?>" placeholder="<?= Txt::trad("DASHBOARD_dateOffline") ?>" title="<?= Txt::trad("DASHBOARD_dateOfflineTooltip") ?>">
			<img src="app/img/dateEnd.png">
		</div>
	</div>

	<!--MENU COMMUN & SUBMIT & CONTROLE DU FORM-->
	<?= $curObj->editMenuSubmit() ?>
</form>