<script>
////	Resize
lightboxSetWidth(700);
</script>

<style>
.vLogsRow				{display:table-row;}
.vLogsRow>div			{display:table-cell; padding:6px;}
.vLogAction,.vLogUser	{width:120px;}
.vLogDate				{width:140px;}
.vLogAction img			{max-height:16px;}
.vNoLogs				{padding:15px; text-align:center;}

/*MOBILE*/
@media screen and (max-width:440px){
	.vLogsRow, .vLogsRow>div	{display:block!important; width:100%!important;}
	.vLogsRow					{margin-bottom:20px;}
	.vLogsRow>div				{padding:5px;}
}
</style>


<div>
	<div class="lightboxTitle"><?= Txt::trad("objHistory") ?></div>

	<?php
	////	Affiche chaque Log
	foreach($logsList as $tmpLog){
		$logoLog=(preg_match("/(add|modif)/i",$tmpLog["action"]))  ?  "edit"  :  "eye";
		echo "<div class='vLogsRow lineHover'>
				<div class='vLogAction'><img src='app/img/".$logoLog.".png'> ".ucfirst(Txt::trad("LOG_".$tmpLog["action"]))."</div>
				<div class='vLogDate'>".Txt::dateLabel($tmpLog["timestamp"],"labelFull")."</div>
				<div class='vLogUser'>".Ctrl::getObj("user",$tmpLog["_idUser"])->getLabel()."</div>
				<div>".$tmpLog["comment"]."</div>
			  </div>";
	}

	////	Aucun log
	if(empty($logsList))	{echo "<div class='vNoLogs'>".Txt::trad("LOG_noLogs")."<div>";}
	?>
</div>