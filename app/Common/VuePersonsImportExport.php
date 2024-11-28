<script>
////	Resize
lightboxSetWidth("<?= Req::isParam("actionImportExport")?"95%":"800" ?>");

////	Init
$(function(){
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
		$("#selectImportType").val("csv").trigger("change");//trigger pour init l'affichage
	<?php } ?>

	////	Tableau d'import: init le background des lignes sélectionnées
	$(".vPersonImportCheckbox").on("change",function(){
		$("#rowPerson"+this.value).toggleClass("lineSelect",this.checked);
	}).trigger("change");//trigger pour init l'affichage

	////	Tableau d'import: vérifie que le champ agora (<select>) n'est pas déjà sélectionné sur une autre colonne
	$(".vAgoraFieldSelect").on("change",function(){
		curField=this.value;
		curFieldCpt=$(this).attr("data-fieldCpt");
		$(".vAgoraFieldSelect").each(function(){
			if(curField==this.value  &&  this.value!=""  &&  $(this).attr("data-fieldCpt")!=curFieldCpt){
				$(".vAgoraFieldSelect[name='agoraFields["+curFieldCpt+"]']").val(null);
				notify("<?= Txt::trad("importNotif3") ?>");
				return false;
			}
		});
	});

	////	Contrôle du formulaire
	$("form").submit(function(){
		//Controle que le fichier d'import est au format csv
		if($("#selectImportExport").isVisible() && $("#selectImportExport").val()=="import" && $("#selectImportType").val()=="csv" && extension($("#importCsvFile").val())!="csv"){
			notify("<?= Txt::trad("fileExtension") ?> CSV");
			return false;
		}
		//Controle le tableau d'import?
		if($(".vPersonImportCheckbox").exist()){
			//Le champ Agora "name" doit être sélectionné  &&  Au moins une personne doit être sélectionnée
			if($(".vAgoraFieldSelect option[value=name]:checked").length==0)	{notify("<?= Txt::trad("importNotif1") ?>");  return false;}
			if($(".vPersonImportCheckbox:checked").length==0)					{notify("<?= Txt::trad("importNotif2") ?>");  return false;}
		}
	});
});
</script>


<style>
#importExportBlock, #importBlock, #exportBlock, #importCsvFile, #importLdapDn, #importLdapFilter	{display:none;}
#importExportBlock, #importBlock, #exportBlock	{margin-right:10px;}
form											{text-align:center;}
#importLdapDn									{width:350px;}
#importLdapFilter								{width:200px;}
#tableImport									{width:100%; margin-top:20px; text-align:left;}
.vTableImport									{font-size:0.9em; font-weight:normal;}
.vTableImport select							{width:130px;}
.vTableImport img[src*='switch']				{cursor:pointer;}
.vImportUserOptions								{display:inline-block; text-align:left; margin-top:20px;}
.vSpaceAffect									{margin-left:20px; margin-top:5px;}
</style>


<form action="index.php" method="post" enctype="multipart/form-data">
	<!--SELECTION D'IMPORT/EXPORT-->
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
		<input type="text" name="importLdapDn" id="importLdapDn" value="<?= Ctrl::$agora->ldap_base_dn ?>" title="<?= Txt::trad("AGORA_ldapDnTooltip") ?>">
		<input type="text" name="importLdapFilter" id="importLdapFilter" value="(cn=*)" title="<?= Txt::trad("importLdapFilterTooltip") ?>">
	</span>
	<!--INPUTS D'EXPORT-->
	<span id="exportBlock">
		<select name="exportType">
			<option value="vcard">VCARD (.vcf)</option>
			<option value="ldif">LDIF</option>
			<?php foreach(MdlPerson::$csvFormats as $tmpKey=>$csvFormat)  {echo "<option value='".$tmpKey."'>".strtoupper(str_replace('_',' ',$tmpKey))."</option>";} ?>
		</select>
	</span>

	<?php
	////	TABLEAU D'IMPORT
	if(Req::param("importType")=="ldap" || (Req::param("importType")=="csv" && !empty($_FILES["importFile"])))
	{
		//Init la liste des personnes à importer && Si on importe des Users, on récupère aussi les login/password 
		$importPersons=[];
		$importLoginPassword=($curObjClass::objectType=="user");

		////	RECUPERE LES VALEURS DE L'IMPORT CSV
		if(Req::param("importType")=="csv")
		{
			//Liste des champs (en fonction de la premiere ligne) + définit le delimiteur de champ + nb de champs
			$csvDelimiters=[";"=>0, ","=>0, "\t"=>0, "|"=>0];
			$fileHandle=fopen($_FILES["importFile"]["tmp_name"], "r");													//Charge le CSV		
			$firstLine=fgets($fileHandle);																				//Récupère la première ligne du csv
			foreach($csvDelimiters as $tmpDelimiter=>&$count)  {$count=count(str_getcsv($firstLine,$tmpDelimiter));}	//Incrémente chaque valeur de $csvDelimiters via "&$count"
			$delimiter=array_search(max($csvDelimiters), $csvDelimiters);												//Définit le délimiter en fonction de la plus grande clé
			//Champs du header et personnes à importer
			$headerFields=[];																							//Init $headerFields
			foreach(explode($delimiter,$firstLine) as $tmpVal)  {$headerFields[]=trim($tmpVal,"'\"");}					//Parcourt la $firstLine et ajoute chaque valeur au $headerFields (sans quotes)
			$fileHandle=fopen($_FILES["importFile"]["tmp_name"],"r");													//Charge tout le CSV		
			while(($data=fgetcsv($fileHandle,10000,$delimiter))!==false)  {$importPersons[]=$data;}						//Ajoute chaque ligne du csv à $importPersons
		}
		////	RECUPERE LES VALEURS DE L'IMPORT LDAP
		elseif(Req::param("importType")=="ldap")
		{
			$ldapSearch=MdlPerson::ldapSearch($importLoginPassword, Req::param("importLdapDn"), Req::param("importLdapFilter"));
			if(!empty($ldapSearch)){
				$headerFields=$ldapSearch["headerFields"];	//Récupère chaque champ du header
				$importPersons=$ldapSearch["ldapPersons"];	//Liste des personnes à importer
			}
		}

		////	AFFICHE LE TABLEAU D'IMPORT
		if(empty($importPersons))  {echo "<div class='emptyContainer'>".Txt::trad("noResults")."</div>";}//"aucun resultat"
		else
		{
			////	INFOS
			echo Txt::trad("importInfo")."<hr>";

			////	TABLEAU DES PERSONNES A IMPORTER
			echo "<div id='tableImport'><table class='vTableImport'>";
				//HEADER DU TABLEAU : INPUT "SELECT" DES CHAMPS "AGORA"
				echo "<tr>";
					//Bouton "switch" la sélection des personnes importées
					echo "<th><img src='app/img/switch.png' onclick=\"$('.vPersonImportCheckbox').trigger('click');\" ".Txt::tooltip("selectSwitch")."></th>";
					//Pour chaque colonne, on affiche un input "select" avec chaque champ "agora" (type "csv_agora")
					for($fieldCpt=0; $fieldCpt < count($headerFields); $fieldCpt++){
						echo "<th><select name='agoraFields[".$fieldCpt."]' class='vAgoraFieldSelect' data-fieldCpt='".$fieldCpt."'><option></option>";	//Début du <select> et option vide (champ pas importé)
						foreach(MdlPerson::$csvFormats["csv_agora"]["fieldKeys"] as $agoraFieldName){													//Parcourt les champs "agora" disponibles
							if($importLoginPassword==true || !preg_match("/(login|password)/i",$agoraFieldName)){										//Vérif si c'est un login/password et s'ils sont importables
								$selectField=(Txt::clean($headerFields[$fieldCpt],"max")==$agoraFieldName)  ?  "selected"  :  null;						//Sélectionne le champ "agora" s'il correspond au champ de l'import 
								echo "<option value='".$agoraFieldName."' ".$selectField.">".Txt::trad($agoraFieldName)."</option>";					//Affiche l'option du champ "agora"
							}
						}
						echo "</select></th>";
					}
				echo "</tr>";
				//LIGNES DES PERSONNES A IMPORTER
				foreach($importPersons as $personCpt=>$personValues)
				{
					$checkedPerson=($personCpt>0 || Req::param("importType")!="csv") ? "checked" : null;
					echo "<tr id='rowPerson".$personCpt."' class='vRowPersons'>";
						echo "<td><input type='checkbox' name='personsImport[]' value='".$personCpt."' class='vPersonImportCheckbox' ".$checkedPerson."></td>";
						//Affiche chaque champ de chaque personnes
						foreach($personValues as $fieldCpt=>$fieldValue){
							$tmpLabel=$tmpValue=Txt::utf8Encode($fieldValue);																					//Convertit si besoin la valeur en UTF8
							if(preg_match("/^pass/i",$headerFields[$fieldCpt]) && !empty($checkedPerson)){														//Import d'un password?
								if(strlen($tmpValue)>=32)	{$tmpLabel=$tmpValue=null;}																			//Password déjà crypté non importable
								else						{$tmpLabel=preg_replace("/./","*",$tmpLabel);}														//Sinon on masque le password
							}
							echo "<td>".$tmpLabel."<input type='hidden' name=\"personFields[".$personCpt."][".$fieldCpt."]\" value=\"".$tmpValue."\"></td>";	//Affiche le champ
						}
					echo "</tr>";
				}
			echo "</table></div>";

			////	IMPORT D'USER : NOTIF PAR MAIL && ESPACES D'AFFECTATION
			if($curObjClass::objectType=="user")
			{
				echo '<div class="vImportUserOptions">';
					echo '<input type="checkbox" name="notifCreaUser" value="1" id="notifCreaUser"><label for="notifCreaUser" '.Txt::tooltip("USER_sendCoordsTooltip2").'>'.Txt::trad("USER_sendCoords").'</label><hr>';
					echo "<div>".Txt::trad("USER_spaceList")." :</div>";
					foreach(Ctrl::$curUser->getSpaces() as $tmpSpace){
						if($tmpSpace->accessRight()==2){
							$tmpChecked =($tmpSpace->isCurSpace() || $tmpSpace->allUsersAffected()) ? "checked" : null;	//Affecté à tous les users / espace courant
							$tmpDisabled=($tmpSpace->allUsersAffected()) ? "disabled" : null;							//Affecté à tous les users
							echo '<div class="vSpaceAffect">
									<input type="checkbox" name="spaceAffectList[]" value="'.$tmpSpace->_id.'" id="spaceAffect'.$tmpSpace->_id.'" '.$tmpChecked.' '.$tmpDisabled.'>
									<label for="spaceAffect'.$tmpSpace->_id.'">'.$tmpSpace->name.'</label>
								  </div>';
						}
					}
				echo "</div>";
			}
			////	IMPORT DE CONTACTS DANS UN DOSSIER RACINE : AFFECTATION PAR DEFAUT A "TOUS LES UTILISATEURS DE L'ESPACE"
			elseif($curObjClass::objectType=="contact" && $curFolder->isRootFolder())
				{echo "<div class='vImportUserOptions'><img src='app/img/accessRight.png'>".Txt::trad("importContactRootFolder")." <i>".Ctrl::$curSpace->name."</i></div>";}
		}
	}

	////	TYPEID DU DOSSIER CONTENEUR (TYPE "CONTACT")  &&  BOUTON DE VALIDATION
	if(Req::isParam("typeId"))  {echo '<input type="hidden" name="_idContainer" value="'.Ctrl::getObjTarget()->_id.'">';}
	echo Txt::submitButton("validate");
	?>
</form>