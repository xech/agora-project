<link rel="stylesheet" type="text/css" href="app/js/datatables/css/jquery.dataTables.css">
<script type="text/javascript" src="app/js/datatables/jquery.dataTables.min.js"></script>

<script>
////	INIT : Parametrage de DataTables
$(function(){
	//Construction du tableau de donnees
	oTable=$("#tableLogs").dataTable({
        "iDisplayLength": 100,			//nb de lignes par page par défaut
        "aLengthMenu": [100,300],	//menu d'affichage du nb de lignes par page
        "aaSorting": [[0,"desc"]],		//indique sur quelle colonne se fait le tri par défaut
        "oLanguage":{					//Traduction diverses dans le menu
            "sLengthMenu": "_MENU_ logs",										//Menu select du nb de lignes par page
            "sZeroRecords": "<?= Txt::trad("LOG_noLogs") ?>",					//"aucun logs"
            "sInfo": "total : _TOTAL_ logs",									//Nb total de logs
            "sInfoEmpty": "<?= Txt::trad("LOG_noLogs") ?>",						//"aucun logs"
            "sInfoFiltered": "(<?= Txt::trad("LOG_filterSince") ?> _MAX_ logs)",// Ajouté si on filtre les infos dans une table (pour donner une idée de la force du filtrage)
            "sSearch":"<img src='app/img/search.png'>",							//champs "search"
			"oPaginate":{
				"sPrevious": "<img src='app/img/navPrev.png'>",
				"sNext": "<img src='app/img/navNext.png'>"
			}
        }
    });
	//Ajoute le placeholder du champs "search"
	$(".dataTables_filter input").attr("placeholder","<?= Txt::trad("LOG_search") ?>");
	//Filtre sur le input text et "select" du footer
	$("tfoot input, tfoot select").on("keyup change",function(){
		oTable.fnFilter($(this).val(), this.parentNode.cellIndex);
	});
});
</script>

<style>
#pageCenterContent	{padding:10px;}
thead th			{text-align:left;}
#tableLogs			{font-size:0.9em;}
#tableLogs td		{text-align:left; padding:3px;}
#tableLogs th		{text-align:left; padding:8px; padding-left:3px;}
#tableLogs tbody	{color:#333;}/*text toujours en noir*/
#logsDownload		{padding:5px; text-align:center;}
tfoot select, tfoot input	{width:100px;}
[name=search_comment]		{width:450px;}
.dataTables_filter input	{width:200px;}/*champ "recherche"*/
.dataTables_filter img		{max-height:18px;}/*champ "recherche"*/
</style>

<div id="pageCenter">
	<div id="pageCenterContent" class="miscContainer">
		<!--TABLEAU DES LOGS-->
		<table id="tableLogs" class="display">
			<!--HEADER-->
			<thead>
				<tr><?php foreach(CtrlLog::$fieldsList as $tmpFieldId)  {echo "<th>".Txt::trad("LOG_".$tmpFieldId)."</th>";} ?></tr>
			</thead>
			<tbody>
				<?php
				////	AFFICHAGE DES LOGS
				foreach(CtrlLog::logList() as $tmpLog){
					echo "<tr>";
					foreach($tmpLog as $tmpFieldId=>$tmpFieldVal)	{echo "<td>".$tmpFieldVal."</td>";}
					echo "</tr>";
				}
				?>
			</tbody>
			<tfoot>
				<tr>
					<th><input type="text" name="search_date" placeholder="<?= Txt::trad("LOG_filter")." ".Txt::trad("LOG_date") ?>" class="searchInit"></th>
					<th><input type="text" name="search_user" placeholder="<?= Txt::trad("LOG_filter")." ".Txt::trad("LOG_userName") ?>" class="searchInit"></th>
					<th><?= CtrlLog::fieldFilterSelect("spaceName") ?></th>
					<th><?= CtrlLog::fieldFilterSelect("moduleName") ?></th>
					<th><?= CtrlLog::fieldFilterSelect("action") ?></th>
					<th><input type="text" name="search_objectType" placeholder="<?= Txt::trad("LOG_filter")." ".Txt::trad("LOG_objectType") ?>" class="searchInit"></th>
					<th><input type="text" name="search_comment" placeholder="<?= Txt::trad("LOG_filter")." ".Txt::trad("LOG_comment") ?>" class="searchInit"></th>
				</tr>
			</tfoot>
		</table>

		<!--TELECHARGEMENT DES LOGS-->
		<div id="logsDownload">
			<a href="?ctrl=log&action=logsDownload"><img src="app/img/download.png"> <?= Txt::trad("download") ?></a>
		</div>
	</div>
</div>