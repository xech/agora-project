<link rel="stylesheet" type="text/css" href="app/js/datatables_2.3.5/datatables.min.css">
<script type="text/javascript" src="app/js/datatables_2.3.5/datatables.min.js"></script>

<script>
/**********************************************************************************************************
 *	PARAMETRAGE DE DATATABLES
**********************************************************************************************************/
ready(function(){
	////	Init le tableau
	var logTable=$("#logTable").DataTable({
        "iDisplayLength": 50,									//Nb de lignes par page par défaut
        "aLengthMenu": [50,100,300],							//Menu du nb de lignes par page
        "aaSorting": [[0,"desc"]],								//Tri par défaut sur la 1ere colonne
        "oLanguage":{											//Libellés du menu :
            "sLengthMenu": "_MENU_ logs / page",				//<select> du nb de lignes par page (header)
			"sSearch":"",										//Pas de libellé pour le champ "search", mais un placeholder (header)
            "sInfo": "Total : _TOTAL_ logs",					//Nb total de logs (footer)
            "sInfoEmpty": "<?= Txt::trad("LOG_noLogs") ?>",		//"aucun logs" (footer)
        }
    });
	////	Ajoute le placeholder du champs "search"
	$(".dt-search input").attr("placeholder","<?= Txt::trad("LOG_search") ?>");
	////	Filtres du footer
	$("tfoot input, tfoot select").on("keyup change",function(){
		let columnIndex = $(this).closest('th').index();
		logTable.column(columnIndex).search(this.value).draw();
	});
});
</script>

<style>
#pageContent						{width:1250px!important;}							/*width par défaut du tableau (verif en responsive)*/
#logTitle, #logsDownload			{padding:10px; text-align:center;}					/*entête du tableau et Download de l'historique*/
#logTable thead .dt-column-title	{text-align:left!important;}						/*label des colonnes*/
#logTable td, #logTable th			{text-align:left; padding:4px; vertical-align:top;}	/*cellules du tableau*/
table.dataTable td:first-child		{width:100px!important;}							/*width de la 1ere colonne*/
tfoot select, tfoot input			{width:100%!important; font-size:0.95rem;}			/*filtres select/input du footer*/	
tfoot select option[value=""]		{background-color:#bbb;}							/*Option par défaut : vide*/
</style>


<div id="pageCenter">
	<div id="pageContent" class="miscContainer">
		<div id="logTitle"><?= Txt::trad("LOG_MODULE_DESCRIPTION").' : '.Ctrl::$curSpace->getLabel() ?></div>
		<hr>
		<table id="logTable">
			<!--HEADER DU TABLEAU-->
			<thead>
				<tr>
					<?php foreach(CtrlLog::$logFields as $fieldName){ ?>
						<th><?= Txt::trad("LOG_".$fieldName) ?></th>
					<?php } ?>
				</tr>
			</thead>
			<!--LISTE DES LOGS-->
			<tbody>
				<?php foreach($logList as $tmpLog){ ?>
					<tr class="lineHover">
						<?php foreach(CtrlLog::$logFields as $fieldName){ ?>
							<td><?= $tmpLog[$fieldName] ?></td>
						<?php } ?>
					</tr>
				<?php } ?>
			</tbody>
			<!--FOOTER DU TABLEAU-->
			<tfoot>
				<tr>
					<th><input type="text" <?= Txt::tooltip(Txt::trad("LOG_filterBy").' '.Txt::trad("LOG_date")) ?> placeholder="<?= Txt::trad("LOG_date") ?>"></th>
					<th><?= CtrlLog::selectFilter($logList,"userName") ?></th>
					<th><?= CtrlLog::selectFilter($logList,"moduleName") ?></th>
					<th><?= CtrlLog::selectFilter($logList,"objectType") ?></th>
					<th><?= CtrlLog::selectFilter($logList,"action") ?></th>
					<th><input type="text" <?= Txt::tooltip(Txt::trad("LOG_filterBy").' '.Txt::trad("LOG_comment")) ?> placeholder="<?= Txt::trad("LOG_comment") ?>"></th>
				</tr>
			</tfoot>
		</table>

		<!--TELECHARGEMENT DES LOGS-->
		<div id="logsDownload">
			<a href="?ctrl=log&action=logsDownload"><img src="app/img/download.png"> <?= Txt::trad("LOG_download") ?></a>
		</div>
	</div>
</div>