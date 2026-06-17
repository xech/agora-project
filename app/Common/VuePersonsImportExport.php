<script>
////	Init
ready(function(){
	////	Switch le formulaire d'import ou d'export
	$("#selectImportExport").on("change",function(){
		$("#importBlock").toggle(this.value=="import");//affiche/masque
		$("#exportBlock").toggle(this.value=="export");//idem
	});

	////	Affiche les inputs d'import
	$("#selectImportType").on("change",function(){
		$("#importCsvFile").toggle(this.value=="csv");//affiche/masque
		$("#importLdapDn,#importLdapFilter").toggle(this.value=="ldap");//idem
	});

	////	Init l'affichage d'import d'users (à la fin)
	<?php if(Req::isParam("actionImportExport")==false){ ?>
		$("#importExportBlock,#importBlock").show();
		$("#selectImportExport").val("import");
		$("#selectImportType").val("csv").trigger("change");//Init l'affichage
	<?php } ?>

	////	Tableau d'import: init le background des lignes sélectionnées
	$(".vLineImportCheckbox").on("change",function(){
		$("#rowPerson"+this.value).toggleClass("lineSelect",this.checked);
	}).trigger("change");//Init l'affichage

	////	Tableau d'import: vérifie que le champ agora (<select>) n'est pas déjà sélectionné sur une autre colonne
	$(".vAgoraFieldSelect").on("change",function(){
		curField=this.value;
		curFieldCpt=this.getAttribute("data-field-cpt");
		$(".vAgoraFieldSelect").each(function(){
			if(curField==this.value  &&  this.value!=""  &&  this.getAttribute("data-field-cpt")!=curFieldCpt){
				$(".vAgoraFieldSelect[name='agoraFields["+curFieldCpt+"]']").val(null);
				notify("<?= Txt::trad("importNotif3") ?>");
				return false;
			}
		});
	});

	////	Contrôle du formulaire
	$("#mainForm").on("submit",function(){
		//Controle que le fichier d'import est au format csv
		if($("#selectImportExport").isVisible() && $("#selectImportExport").val()=="import" && $("#selectImportType").val()=="csv" && extension($("#importCsvFile").val())!="csv"){
			notify("<?= Txt::trad("fileExtension") ?> CSV");
			return false;
		}
		//Controle le tableau d'import : Le champ Agora "name" doit être sélectionné, et au moins une personne doit être sélectionnée
		if($(".vLineImportCheckbox").exist()){
			if($(".vAgoraFieldSelect option[value=name]:checked").length==0)	{notify("<?= Txt::trad("importNotif1") ?>");  return false;}
			if($(".vLineImportCheckbox:checked").length==0)						{notify("<?= Txt::trad("importNotif2") ?>");  return false;}
		}
	});
});
</script>


<style>
#bodyLightbox 					{max-width:<?= Req::isParam("actionImportExport") ? '1800px' : '800px' ?>;}
form							{text-align:center;}
#importLdapDn					{width:350px;}
#importLdapFilter				{width:200px;}
#importTable					{font-size:0.9rem; margin-top:20px; text-align:left; font-weight:normal;}
#importTable select				{font-size:0.9rem; width:fit-content;}
#importTable img[src*='switch']	{cursor:pointer;}
.vImportOptions					{display:inline-block; text-align:left; margin-top:20px;}
.vSpaceAffect					{margin-left:20px; margin-top:5px;}
#importExportBlock, #importBlock, #exportBlock, #importCsvFile, #importLdapDn, #importLdapFilter	{display:none;}
#importExportBlock, #importBlock, #exportBlock														{margin-right:10px;}
</style>


<form action="index.php" method="post" enctype="multipart/form-data" id="mainForm">

	<!--SELECTION D'IMPORT || EXPORT-->
	<span id="importExportBlock">
		<select name="actionImportExport" id="selectImportExport">
			<option value="import"><?= Txt::trad("import_".Req::$curCtrl) ?></option>
			<option value="export"><?= Txt::trad("export_".Req::$curCtrl) ?></option>
		</select> &nbsp;
		<?= Txt::trad("exportFormat") ?>
	</span>

	<!--INPUTS D'IMPORT-->
	<span id="importBlock">
		<select name="importType" id="selectImportType">
			<option value="csv">CSV</option>
			<?php if(Ctrl::$agora->ldap_server){ ?><option value="ldap">LDAP</option><?php } ?>
		</select><br><br><br>
		<input type="file" name="importFile" id="importCsvFile">
		<input type="text" name="importLdapDn" id="importLdapDn" value="<?= Ctrl::$agora->ldap_base_dn ?>" <?= Txt::tooltip("AGORA_ldapDnTooltip") ?> >
		<input type="text" name="importLdapFilter" id="importLdapFilter" value="(cn=*)" <?= Txt::tooltip("importLdapFilterTooltip") ?> >
	</span>

	<!--INPUTS D'EXPORT-->
	<span id="exportBlock">
		<select name="exportType">
			<option value="vcard">VCARD</option>
			<option value="ldif">LDIF</option>
			<option value="csv">CSV</option>
		</select>
	</span>

	<!--IMPORT DE CONTACTS : ID DU DOSSIER-->
	<?php if(isset($curContainer)){ ?>
		<input type="hidden" name="typeId" value="<?= $curContainer->typeId ?>">
	<?php } ?>


	<!--FORMULAIRE D'IMPORT-->
	<?php
	if(Req::param("importType")=="ldap" || (Req::param("importType")=="csv" && !empty($_FILES["importFile"]))){
		$importList=[];

		////	RECUPERE LES VALEURS DE L'IMPORT CSV
		if(Req::param("importType")=="csv"){
			$importFields=[];																	//Init $importFields
			$enclosure="\"";																	//Caractère d'encadrement
			$escape="\\";																		//caractères et d'échappement
			$separatorList=[";"=>0, ","=>0, "\t"=>0, "|"=>0];									//Init les delimiteurs de champs du CSV
			$fileHandle=fopen($_FILES["importFile"]["tmp_name"], "r");							//Charge le CSV		
			$firstLine=fgets($fileHandle);														//Récupère la première ligne du csv
			foreach($separatorList as $separatorTmp=>&$count)									//Parcourt chaque délimiters
				{$count=count(str_getcsv($firstLine, $separatorTmp, $enclosure, $escape));}		//- Incrémente le nombre de fois ou le délimiteur est présent ('"' et "\\" : caractères d'encadrement et d'échappement)
			$separator=array_search(max($separatorList), $separatorList, true);					//Définit le délimiter en fonction de la plus grande clé
			foreach(explode($separator,$firstLine) as $tmpVal){									//Parcourt la $firstLine :
				$tmpVal=Txt::clean(trim($tmpVal,$enclosure),"min");								//- Clean le champ
				if(!empty($tmpVal))  {$importFields[]=$tmpVal;}									//- ajoute chaque valeur au $importFields (sans quotes)
			}
			while(($data=fgetcsv($fileHandle, null, $separator, $enclosure, $escape))!==false)	//Parcourt chaque ligne du CSV  ('"' et "\\" : caractères et d'échappement)
				{$importList[]=$data;}															//- Ajoute la ligne du csv à $importList
		}
		////	RECUPERE LES VALEURS DE L'IMPORT LDAP
		elseif(Req::param("importType")=="ldap"){
			$ldapSearch=MdlPerson::ldapSearch($importLoginPassword, Req::param("importLdapDn"), Req::param("importLdapFilter"));
			if(!empty($ldapSearch)){
				$importFields=$ldapSearch["headerFields"];	//Récupère chaque champ du header
				$importList=$ldapSearch["ldapPersons"];		//Liste des personnes à importer
			}
		}
	?>

		<!--TABLEAU D'IMPORT-->
		<?php if(!empty($importList)){ ?>
			<!--INFOS SUR L'IMPORT-->
			<?= Txt::trad("importInfo") ?><hr>

			<!--TABLEAU DES PERSONNES A IMPORTER-->
			<table id="importTable">
				<!--HEADER DU TABLEAU : BOUTON DE SÉLECTION  &&  INPUTS "SELECT" DE CHAQUE CHAMP "AGORA"-->
				<tr>
					<th><img src="app/img/checkSelectAll.png" onclick="$('.vLineImportCheckbox').prop('checked',true);" <?= Txt::tooltip("selectAll") ?>></th>
					<?php for($fieldCpt=0; $fieldCpt < count($importFields); $fieldCpt++){ ?>
					<th>
						<select name="agoraFields[<?= $fieldCpt ?>]" class="vAgoraFieldSelect" data-field-cpt="<?= $fieldCpt ?>">
							<option></option>
							<?php
							////	Parcourt chaque champ à importer
							foreach(MdlPerson::$csvFields["fieldKeys"] as $fieldName){
								//// Pas d'import de des groupes, ni de login/password pour les contacts
								if($fieldName=="groups" || ($objectType=="contact" && preg_match("/(login|password)/i",$fieldName)))   {continue;}
								//// Sélectionne le champ
								$selectField=(Txt::clean($importFields[$fieldCpt],"max")==$fieldName)  ?  "selected"  :  null;
							?>
								<option value="<?= $fieldName ?>" <?= $selectField ?>><?= Txt::trad($fieldName) ?></option>
							<?php } ?>
						</select>
					</th>
					<?php } ?>
				</tr>
				
				<!--CHECKBOX ET CHAMPS DE CHAQUE PERSONNE À IMPORTER-->
				<?php foreach($importList as $lineCpt=>$lineValues){ ?>
					<tr id="rowPerson<?= $lineCpt ?>">
						<td><input type="checkbox" name="personsImport[]" value="<?= $lineCpt ?>" class="vLineImportCheckbox" checked="checked"></td>
						<?php
						foreach($lineValues as $fieldCpt=>$fieldVal){
							$fieldVal=$fieldLabel=Txt::utf8Encode($fieldVal);
							if(array_key_exists($fieldCpt,$importFields) && $importFields[$fieldCpt]=="password" && $fieldVal!="password")  {$fieldLabel="**********";}
						?>
							<td><?= $fieldLabel ?><input type="hidden" name="personFields[<?= $lineCpt ?>][<?= $fieldCpt ?>]" value="<?= $fieldVal ?>"></td>
						<?php } ?>
					</tr>
				<?php } ?>
			</table>

			<!--IMPORT D'USER-->
			<?php if($objectType=="user"){ ?>
				<div class="vImportOptions">
					<!--NOTIF PAR MAIL-->
					<input type="checkbox" name="notifCreaUser" value="1" id="notifCreaUser">
					<label for="notifCreaUser" <?= Txt::tooltip("USER_sendCoordsTooltip2") ?> ><?= Txt::trad("USER_sendCoords") ?></label>
					<hr>
					<!--ESPACES D'AFFECTATION-->
					<div><?= Txt::trad("USER_spaceList") ?> :</div>
					<?php
					foreach(Ctrl::$curUser->spaceList() as $tmpSpace){
						if($tmpSpace->editRight()==false)  {continue;}
						$isChecked=$isDisabled=$spaceTooltip=null;
						if($tmpSpace->isCurSpace() || $tmpSpace->allUsersAffected())  {$isChecked='checked';}
						if($tmpSpace->allUsersAffected()){
							$isDisabled='disabled';
							$spaceTooltip=Txt::tooltip("USER_allUsersOnSpace");
						}
					?>
						<div class="vSpaceAffect" <?= $spaceTooltip ?> >
							<input type="checkbox" name="spaceAffectList[]" value="<?= $tmpSpace->_id ?>" id="spaceAffect<?= $tmpSpace->_id ?>" <?= $isChecked.' '.$isDisabled ?> >
							<label for="spaceAffect<?= $tmpSpace->_id ?>"><?= $tmpSpace->name ?></label>
						</div>
					<?php } ?>
				</div>
			<?php } ?>

			<!--IMPORT DE CONTACTS DANS UN DOSSIER RACINE : AFFECTATION PAR DEFAUT A "TOUS LES UTILISATEURS DE L'ESPACE"-->
			<?php if(isset($curContainer) && $curContainer->isRootFolder()){ ?>
				<div class='vImportOptions'><img src="app/img/info.png"> <?= Txt::trad("importContactRootFolder") ?> <i><?= Ctrl::$curSpace->name ?></i></div>
			<?php } ?>

	<!--TABLEAU & FORMULAIRE D'IMPORT => FIN-->
	<?php
		}
	}
	?>

	<!--BOUTON DE VALIDATION-->
	<?= Txt::submitButton("validate") ?>
</form>