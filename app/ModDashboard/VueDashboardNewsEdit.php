<script>
////	Archive la news s'il ya une date de publication automatique
ready(function(){
	$(".dateBegin").on("blur",function(){
		setTimeout(function(){																//Timeout le temps d'executer les controles de base du "change" (cf. "app.js")
			if($(".dateBegin").notEmpty() && $("[name='offline']").prop("checked")==false){	//Date de début spécifiée (mise en ligne) et actu pas "offline"
				var dateBegin=$(".dateBegin").val().split("/");
				var timeBegin=new Date(dateBegin[1]+"/"+dateBegin[0]+"/"+dateBegin[2]);		//Date au format "mm/dd/yyy"
				if(Date.now() < timeBegin.valueOf()){										//Date "online" supérieure à aujourd'hui
					notify("<?= Txt::trad("DASHBOARD_dateOnlineNotif") ?>");				//Notif "L'actualité est momentanément archivée.."
					$("[name='offline']").trigger("click");									//Passe l'actu en "offline"
				}
			}
		},500);
	});
});
</script>


<style>
#bodyLightbox			{max-width:900px;}
#newsOptions			{margin-top:22px; text-align:center;}
#newsOptions>div		{display:inline-block; line-height:30px; margin-right:25px;}
.dateBegin, .dateEnd	{width:160px;}/*surcharge pour afficher les placeholders*/
/*RESPONSIVE SMARTPHONE*/
@media screen and (max-width:490px){
	#newsOptions>div		{display:block; margin-bottom:20px;}
	.dateBegin, .dateEnd	{width:230px;}/*surcharge pour afficher les placeholders*/
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
			<label for="uneCheckbox" <?= Txt::tooltip("DASHBOARD_topNewsTooltip") ?> ><?= Txt::trad("DASHBOARD_topNews") ?> <img src="app/img/dashboard/topNews.png"></label>	
		</div>
		<!--IS OFFLINE-->
		<div>
			<input type="checkbox" name="offline" value="1" id="offlineCheckbox" <?= $curObj->offline==1?"checked":null ?>>
			<label for="offlineCheckbox"><?= Txt::trad("DASHBOARD_offline") ?> <img src="app/img/dashboard/newsOffline.png"></label>
		</div>
		<!--DATE ONLINE-->
		<div>
			<input type="text" name="dateOnline" class="dateBegin" value="<?= Txt::formatDate($curObj->dateOnline,"dbDatetime","inputDate") ?>" placeholder="<?= Txt::trad("DASHBOARD_dateOnline") ?>" <?= Txt::tooltip("DASHBOARD_dateOnlineTooltip") ?> >
			<img src="app/img/dateEnd2.png">
		</div>
		<!--DATE OFFLINE-->
		<div>
			<input type="text" name="dateOffline" class="dateEnd" value="<?= Txt::formatDate($curObj->dateOffline,"dbDatetime","inputDate") ?>" placeholder="<?= Txt::trad("DASHBOARD_dateOffline") ?>" <?= Txt::tooltip("DASHBOARD_dateOfflineTooltip") ?> >
			<img src="app/img/dateEnd.png">
		</div>
	</div>

	<!--MENU D'EDITION & VALIDATION DU FORM-->
	<?= $curObj->editMenuSubmit() ?>
</form>