<script>
////	Resize
lightboxSetWidth(600);

////	INIT
$(function(){
	////	INIT LA PAGE
	<?php if($curObj->fullRight()==false){ ?>
		//L'user courant n'est pas l'auteur de l'evt : masque tous les champs, sauf les affectations aux agendas
		$("#eventDetails,#objMenuLabels,#objMenuBlocks").hide();
	<?php }else{ ?>
		//Prérempli les champs
		$("select[name='periodType']").val("<?= $curObj->periodType ?>");
		$("select[name='contentVisible']").val("<?= $curObj->contentVisible ?>");
		$("select[name='_idCat']").val("<?= $curObj->_idCat ?>").trigger("change");//"trigger" pour changer la couleur de l'input
		$("select[name='important']").val("<?= (int)$curObj->important ?>").trigger("change");//"trigger" pour changer la couleur de l'input. Valeur au format "integer"
		//Affiche les options de "périodicité" & Infos de créneaux horaires occupés
		displayPeriodType();
		timeSlotBusy();
	<?php } ?>
	//Surligne les agendas déjà sélectionnés
	$(".vCalendarInput:checked").each(function(){
		$(this).parents(".vCalAffectBlock").addClass("sLineSelect");
	});

	////	CHANGE DE DATE/HEURE/PÉRIODICITÉ (sauf pour les guests) :  Controle si les créneaux horaires sont déjà occupés  &  Affiche si besoin les details de périodicité
	<?php if(Ctrl::$curUser->isUser()){ ?>
	$("[name='dateBegin'],[name='timeBegin'],[name='dateEnd'],[name='timeEnd']").change(function(){ timeSlotBusy(); });
	$("[name='periodType'],[name='dateBegin']").change(function(){ displayPeriodType(); });
	<?php } ?>

	////	VISIO : CRÉÉ UNE NOUVELLE UNE URL
	$("#visioUrlAdd").click(function(){
		if(confirm("<?= Txt::trad("CALENDAR_visioUrlAdd") ?> ?")){
			var visioRoomId=MD5( Date.now().toString() ).substr(0,10);
			$("#visioUrlInput").val("<?= Ctrl::$agora->visioUrl() ?>"+visioRoomId);//Url de la visio
			$("#visioUrlInput,#visioUrlCopy,#visioUrlDelete").show();//Affiche l'input/copy/delete
			$(this).hide();//Masque le label
		}
	});

	////	VISIO : LANCE LA VISIO DEPUIS L'UNPUT
	$("#visioUrlInput").click(function(){
		if(confirm("<?= Txt::trad("CALENDAR_visioUrlLaunch") ?> ?")){
			window.open(this.value)
		}
	});

	////	VISIO : COPIE L'URL DANS LE PRESSE PAPIER
	$("#visioUrlCopy").click(function(){
		if(confirm("<?= Txt::trad("CALENDAR_visioUrlCopy") ?> ?")){
			$("#visioUrlInput").select();
			document.execCommand('copy');
			notify("<?= Txt::trad("copyUrlConfirmed") ?>");
		}
	});

	////	VISIO : SUPPRIME L'URL
	$("#visioUrlDelete").click(function(){
		if(confirm("<?= Txt::trad("CALENDAR_visioUrlDelete") ?> ?")){
			$("#visioUrlInput").val("");//Réinit l'url de la visio
			$("#visioUrlInput,#visioUrlCopy,#visioUrlDelete").hide();//Masque l'input/copy/delete
			$("#visioUrlAdd").show();//Affiche le label d'ajout
		}
	});

	////	SELECTION D'AGENDA : SWITCH LA SÉLECTION
	$("#calsAffectSwitch").click(function(){
		var calsSelector=".vCalendarInput:enabled";
		if($(calsSelector).length==$(calsSelector+":checked").length)	{$(calsSelector+":checked").trigger("click");}/*désélectionne tous les agendas*/
		else															{$(calsSelector+":not(:checked)").trigger("click");}/*sélectionne les agendas pas encore sélectionnés*/
	});

	////	SELECTION D'AGENDA : CHECK/UNCKECK L'INPUT PRINCIPAL D'UN AGENDA VIA SON LABEL
	$(".vCalendarInput").change(function(){
		//Coche une proposition d'evt : affiche la notif "l'événement sera proposé..."
		if(typeof timeoutPropose!="undefined")  {clearTimeout(timeoutPropose);}//Pas de cumul de Timeout
		timeoutPropose=setTimeout(function(thisInput){
			if(/proposition/i.test(thisInput.name) && $(thisInput).prop("checked"))  {notify("<?= Txt::trad("CALENDAR_inputProposed") ?>");}
		},500,this);//Affiche avec un timeout (cf. sélection d'un groupe d'users). Transmet l'input courant en paramètre via "this"
		//Agenda sélectionné : on surligne le block et affiche si besoin l'option de proposition
		if(this.checked)	{$(this).parents(".vCalAffectBlock").addClass("sLineSelect").find(".vCalAffectProposition").show();}
		else				{$(this).parents(".vCalAffectBlock").removeClass("sLineSelect").find(".vCalAffectProposition").hide();}
		//"uncheck" si besoin l'option de proposition
		$(this).parents(".vCalAffectBlock").find(".vCalendarInputProposition").prop("checked",false);
		//Controle d'occupation du créneau horaire de chaque agenda sélectionné
		timeSlotBusy();
	});

	////	CHECK/UNCHECK L'OPTION DE PROPOSITION POUR UN AGENDA
	$(".vCalendarInputProposition").change(function(){
		//"checked" : décoche l'affectation principale et affiche la notif "l'événement sera proposé..."   ||   "unchecked" : masque l'option de proposition et enlève le surlignage de la ligne (retour à l'état initial)
		if(this.checked)	{$(this).parents(".vCalAffectBlock").find(".vCalendarInput").prop("checked",false);  notify("<?= Txt::trad("CALENDAR_inputProposed") ?>");}
		else				{$(this).parents(".vCalAffectBlock").removeClass("sLineSelect").find(".vCalAffectProposition").hide();}
	});
});

////	GÈRE L'AFFICHAGE DE LA PÉRIODICITÉ
function displayPeriodType()
{
	//Réinitialise les options de périodicité & Affiche au besoin l'options sélectionnée
	$("#periodTypeLabel, #periodOption_weekDay, #periodOption_month, #periodDateEnd, #periodDateExceptions").hide();
	if($("[name='periodType']").isEmpty()==false)  {$("#periodTypeLabel, #periodDateEnd, #periodDateExceptions, #periodOption_"+$("[name='periodType']").val()).slideDown();}
	//Pré-check si besoin tous les mois
	if($("[name='periodType']").val()=="month" && $("[name*='periodValues_month']:checked").length==0)  {$("input[name*='periodValues_month']").prop("checked","true");}
	//Affiche les détails de périodicité (exple : "le 15 du mois")
	var periodTypeLabelTmp="";
	if($("[name='periodType']").val()=="month")		{periodTypeLabelTmp="<?= Txt::trad("the") ?> "+$("[name='dateBegin']").val().substr(0,2)+" <?= Txt::trad("CALENDAR_period_dayOfMonth") ?> ";}//"le 15 du mois"
	else if($("[name='periodType']").val()=="year")	{periodTypeLabelTmp="<?= Txt::trad("the") ?> "+$("[name='dateBegin']").val().substr(0,5);}//"le 15/10" de l'année
	$("#periodTypeLabel").html(periodTypeLabelTmp);
	//Masque les exceptions de périodicité vides
	$(".periodExceptionDiv").each(function(){
		if($("#"+this.id.replace("Div","Input")).isEmpty())  {$(this).hide();}
	});
}

////	SUPPRIME UNE "PERIODDATEEXCEPTIONS"
function deletePeriodDateExceptions(exceptionCpt)
{
	var inputSelector="#periodExceptionInput"+exceptionCpt;
	if($(inputSelector).isEmpty() || ($(inputSelector).isEmpty()==false && confirm("<?= Txt::trad("delete") ?>?"))){
		$(inputSelector).val("");
		$("#periodExceptionDiv"+exceptionCpt).hide();
	}
}

////	CONTROLE OCCUPATION CRÉNEAUX HORAIRES DES AGENDAS SÉLECTIONNÉS : EN AJAX
function timeSlotBusy()
{
	//Lance la requête ajax, avec un "timeout"
	if(typeof timeoutTimeSlotBusy!="undefined")  {clearTimeout(timeoutTimeSlotBusy);}//Pas de cumul de Timeout ..et de requête ajax!
	timeoutTimeSlotBusy=setTimeout(function(){
		//Prépare la requete de controle Ajax, avec la liste des Agendas sélectionnés : affectations accessibles en écriture
		if($("[name='dateBegin']").isEmpty()==false && $("[name='dateEnd']").isEmpty()==false)
		{
			//Init l'url, avec le créneau horaire et les agendas concernés
			var ajaxUrl="?ctrl=calendar&action=timeSlotBusy"+
						"&dateTimeBegin="+encodeURIComponent($("[name='dateBegin']").val()+" "+$("[name='timeBegin']").val())+
						"&dateTimeEnd="+encodeURIComponent($("[name='dateEnd']").val()+" "+$("[name='timeEnd']").val())+
						"&_evtId=<?= $curObj->_id ?>&targetObjects[calendar]=";
			$(".vCalendarInput:checked,.vCalendarInputProposition:checked").each(function(){ ajaxUrl+=this.value+"-"; });
			//Lance le controle Ajax et renvoie les agendas où le créneau est occupé
			$.ajax(ajaxUrl).done(function(txtResult){
				if(txtResult.length>0)	{$("#timeSlotBusy").fadeIn();  $(".vTimeSlotBusyTable").html(txtResult); }
				else					{$("#timeSlotBusy").hide();}
			});
		}
	}, 1000);
}

////	CONTRÔLE DU FORMULAIRE
function formControl()
{
	//Controle le nombre d'affectations aux agendas
	if($(".vCalendarInput:checked,.vCalendarInputProposition:checked").isEmpty())  {notify("<?= Txt::trad("CALENDAR_verifCalNb") ?>"); return false;}
	//Controle final (champs obligatoires, etc)
	return mainFormControl();
}
</script>


<style>
#blockDescription						{margin:20px 0px; <?= empty($curObj->description)?"display:none;":null ?>}
#eventDetails							{text-align:center;}
.vEventDetails							{display:inline-block; margin-top:20px; margin-right:20px;}

/*PÉRIODICITÉ*/
#periodTypeLabel											{display:none; margin-left:5px; text-decoration:underline;}
#periodOption_weekDay, #periodOption_month					{display:none; margin-top:20px; text-align:left; vertical-align:middle;}/*liste des checkboxes de jours ou de mois*/
#periodOption_weekDay>div, #periodOption_month>div			{display:none; display:inline-block; width:25%; padding:5px;}
#periodDateEnd, #periodDateExceptions, .periodExceptionDiv	{display:none; display:inline-block; line-height:40px; margin-top:10px; margin-right:10px;}

/*VISIOCONFERENCE*/
#visioUrlAdd, #visioUrlInput, #visioUrlCopy, #visioUrlDelete	{cursor:pointer;}
#visioUrlAdd													{<?= !empty($curObj->visioUrl)?"display:none;":null ?>}
#visioUrlInput, #visioUrlCopy, #visioUrlDelete					{<?= empty($curObj->visioUrl)?"display:none;":null ?>}
#visioUrlInput													{width:260px; font-size:0.95em;}

/*AFFECTATION AUX AGENDAS*/
.lightboxBlock.vCalAffectOptions		{padding:4px 0px 4px 6px;}/*surcharge*/
#calsAffectDiv							{max-height:135px; overflow-y:auto;}
.vCalAffectBlock						{display:inline-block; width:48%; margin:2px; margin-right:5px; border-radius:3px;}
.vCalAffectBlock .vCalendarInput		{display:none;}
.vCalAffectBlock label					{display:inline-block; width:75%; padding:5px 3px 5px 3px;}
.vCalAffectBlock img					{max-height:16px;}
.vCalAffectBlockBis label				{width:100%;}
.vCalAffectProposition					{display:none; float:right; padding:3px; background:#ddd;}
.vCalAffectProposition input			{margin-right:2px;}
input[name='calUsersGroup[]']			{display:none;}

/*GUESTS : MASQUE LES OPTIONS AVANCEES & LE MENU D'AFFECTATION AUX AGENDAS (conserve en "background" l'agenda présélectionné pour l'enregistrement du formulaire)*/
<?php if(Ctrl::$curUser->isUser()==false){ ?>
.vEventDetailsAdvanced, .vCalAffectOptions	{display:none;}
<?php } ?>

/*DÉTAILS SUR L'AFFECTATION*/
#timeSlotBusy							{display:none;}
.vTimeSlotBusyTable						{display:table; margin-top:6px;}
.vTimeSlotBusyRow						{display:table-row;}/*cf. "actionTimeSlotBusy()"*/
.vTimeSlotBusyCell						{display:table-cell; padding:4px; vertical-align:middle;}/*idem*/

/*RESPONSIVE FANCYBOX (440px)*/
@media screen and (max-width:440px){
	.vEventDetails										{margin-top:30px; margin-right:10px;}
	.vCalAffectBlock									{width:96%;}
	.vCalAffectBlock label								{padding:8px 3px 8px 3px;}
	#periodOption_weekDay>div, #periodOption_month>div	{width:33%;}
}
</style>


<form action="index.php" method="post" onsubmit="return formControl()" enctype="multipart/form-data" class="lightboxContent">
	<!--TITRE RESPONSIVE-->
	<?php echo $curObj->editRespTitle("CALENDAR_addEvt"); ?>
	
	<!--PAS AUTEUR DE L'EVT : "VOUS N'AVEZ PAS D'ACCES AUX DETAILS"-->
	<?php if($curObj->fullRight()==false)  {echo "<div class='infos'><img src='app/img/info.png'> ".Txt::trad("CALENDAR_editLimit")."</div><br>";} ?>

	<div id="eventDetails">

		<!--TITRE & DESCRIPTION (EDITOR)-->
		<input type="text" name="title" value="<?= $curObj->title ?>" class="textBig" placeholder="<?= Txt::trad("title") ?>"> &nbsp;
		<img src="app/img/description.png" class="sLink" title="<?= Txt::trad("description") ?>" onclick="$('#blockDescription').slideToggle()">
		<div id="blockDescription">
			<textarea name="description" placeholder="<?= Txt::trad("description") ?>"><?= $curObj->description ?></textarea>
		</div>

		<!--DATE DEBUT & FIN-->
		<span class="vEventDetails">
			<input type="text" name="dateBegin" class="dateBegin" value="<?= Txt::formatDate($curObj->dateBegin,"dbDatetime","inputDate") ?>" placeholder="<?= Txt::trad("begin") ?>">
			<input type="text" name="timeBegin" class="timeBegin" value="<?= Txt::formatDate($curObj->dateBegin,"dbDatetime","inputHM") ?>" placeholder="H:m">
			&nbsp; <img src="app/img/arrowRight.png"> &nbsp; 
			<input type="text" name="dateEnd" class="dateEnd" value="<?= Txt::formatDate($curObj->dateEnd,"dbDatetime","inputDate") ?>" placeholder="<?= Txt::trad("end") ?>">
			<input type="text" name="timeEnd" class="timeEnd" value="<?= Txt::formatDate($curObj->dateEnd,"dbDatetime","inputHM") ?>" placeholder="H:m">
		</span>
	
		<!--PERIODICITE/RECCURENCE-->
		<span class="vEventDetails vEventDetailsAdvanced">
			<select name="periodType">
				<option value=""><?= Txt::trad("CALENDAR_noPeriodicity") ?></option>
				<option value="weekDay"><?= Txt::trad("CALENDAR_period_weekDay") ?></option>
				<option value="month"><?= Txt::trad("CALENDAR_period_month") ?></option>
				<option value="year"><?= Txt::trad("CALENDAR_period_year") ?></option>
			</select>
			<span id="periodTypeLabel">&nbsp;</span><!--Exple: "le 15 du mois"-->
		</span>


		<!--PERIODICITE: JOURS DE LA SEMAINE-->
		<div id="periodOption_weekDay">
			<?php
			for($cpt=1; $cpt<=7; $cpt++){
				$periodValueChecked=($curObj->periodType=="weekDay" && in_array($cpt,$tabPeriodValues))  ?  "checked"  :  null;
				echo "<div>
						<input type='checkbox' name='periodValues_weekDay[]' value='".$cpt."' id='periodValues_weekDay".$cpt."' ".$periodValueChecked." >
						<label for='periodValues_weekDay".$cpt."'>".Txt::trad("day_".$cpt)."</label>
					  </div>";
			}
			?>
		</div>

		<!--PERIODICITE: MOIS DE L'ANNEE-->
		<div id="periodOption_month">
			<?php
			for($cpt=1; $cpt<=12; $cpt++){
				$periodValueChecked=($curObj->periodType=="month" && in_array($cpt,$tabPeriodValues))  ?  "checked"  :  null;
				echo "<div>
						<input type='checkbox' name='periodValues_month[]' value='".$cpt."' id='periodValues_month".$cpt."' ".$periodValueChecked." >
						<label for='periodValues_month".$cpt."'>".Txt::trad("month_".$cpt)."</label>
					  </div>";
			}
			?>
		</div>

		<!--PERIODICITE: FIN-->
		<div id="periodDateEnd">
			<?= Txt::trad("CALENDAR_periodDateEnd") ?> <input type="text" name="periodDateEnd" class="dateInput" value="<?= Txt::formatDate($curObj->periodDateEnd,"dbDate","inputDate") ?>">
		</div>
	
		<!--EXCEPTIONS DE PERIODICITE-->
		<div id="periodDateExceptions">
			<span class="sLink" onclick="$('.periodExceptionDiv:hidden').first().show()"><?= Txt::trad("CALENDAR_periodException") ?> <img src="app/img/plusSmall.png"></span>
		</div>
		<?php
		////	Dates d'exceptions de périodicité (10 max)
		for($cpt=1; $cpt<=10; $cpt++){
			echo "<div id='periodExceptionDiv".$cpt."' class='periodExceptionDiv'>
					<input type='text' name='periodDateExceptions[]' value=\"".(isset($periodDateExceptions[$cpt])?$periodDateExceptions[$cpt]:null)."\" class='dateInput' id='periodExceptionInput".$cpt."'>
					<img src='app/img/delete.png' title=\"".Txt::trad("delete")."\" class='sLink' onclick=\"deletePeriodDateExceptions(".$cpt.");\">
				  </div>";
		}
		?>
		
		<!--CATEGORIE-->
		<span class="vEventDetails">
			<?= Txt::trad("CALENDAR_category") ?>:
			<select name="_idCat">
				<option value=""><?= Txt::trad("noneFem") ?></option>
				<?php foreach(MdlCalendarEventCategory::getCategories() as $tmpCat){ ?>
				<option value="<?= $tmpCat->_id ?>" data-color="<?= $tmpCat->color ?>"><?= $tmpCat->title ?></option>
				<?php } ?>
			</select>
		</span>
		
		<!--IMPORTANT-->
		<span class="vEventDetails vEventDetailsAdvanced">
			<select name="important">
				<option value="0"><?= Txt::trad("CALENDAR_importanceNormal") ?></option>
				<option value="1" data-color="#900"><?= Txt::trad("CALENDAR_importanceHight") ?></option>
			</select>
		</span>

		<!--VISIBILITE-->
		<span class="vEventDetails vEventDetailsAdvanced">
			<select name="contentVisible" title="<?= Txt::trad("CALENDAR_visibilityInfo") ?>">
				<option value="public"><?= Txt::trad("CALENDAR_visibilityPublic") ?></option>
				<option value="prive"><?= Txt::trad("CALENDAR_visibilityPrivate") ?></option>
				<option value="public_cache"><?= Txt::trad("CALENDAR_visibilityPublicHide") ?></option>
			</select>
		</span>

		<!--VISIOCONFERENCE-->
		<span class="vEventDetails vEventDetailsAdvanced">
			<img src="app/img/visioSmall.png">&nbsp; 
			<span id="visioUrlAdd"><?= Txt::trad("CALENDAR_visioUrlAdd") ?></span>
			<input type="text" name="visioUrl" value="<?= $curObj->visioUrl ?>" id="visioUrlInput" title="<?= Txt::trad("CALENDAR_visioUrlLaunch") ?>" readonly>
			<img src="app/img/copy.png" id="visioUrlCopy" title="<?= Txt::trad("CALENDAR_visioUrlCopy") ?>">
			<img src="app/img/delete.png" id="visioUrlDelete" title="<?= Txt::trad("CALENDAR_visioUrlDelete") ?>">
		</span>
	</div>

	<!--AFFECTATIONS AUX AGENDAS-->
	<div class="lightboxBlockTitle vCalAffectOptions"><?= Txt::trad("CALENDAR_calendarAffectations") ?></div>
	<div class="lightboxBlock vCalAffectOptions">
		<?php
		echo "<div id='calsAffectDiv'>";
		////	AGENDAS DE RESSOURCES & AGENDAS PERSONNELS
		foreach($affectationCalendars as $tmpCal)
		{
			//Nom de l'input
			$calInputName=($tmpCal->inputType=="affectation")  ?  "affectationCalendars[]"  :  "propositionCalendars[]";
			//Réinit l'affectation/proposition après validation du form?
			$moreInputs=($tmpCal->reinitCalendarInput==true)  ?  "<input type='hidden' name='reinitCalendars[]' value=\"".$tmpCal->_id."\">"  :  null;
			//Agenda d'user ou de ressource
			if($tmpCal->type=="user")	{$calIcon="typeUser.png";		$calIdUser="data-idUser=\"".$tmpCal->_idUser."\"";}
			else						{$calIcon="typeRessource.png";	$calIdUser=null;}
			//Astérisque "*" sur les agendas non-modifiables || proposition
			if($tmpCal->isDisabled!=null)				{$tmpCal->title.=" &#42;&#42;";}
			elseif($tmpCal->inputType=="proposition")	{$tmpCal->title.=" &#42;";}
			//Affiche l'option de proposition d'événement (en plus du champ principal avec le label)
			if($tmpCal->inputType=="affectation" && $tmpCal->curUserCalendar()==false){
				if($curObj->isNew()==false && in_array($tmpCal,$curObj->affectedCalendars(false)))  {$propositionShow="style='display:block;'"; $propositionChecked="checked"; $tmpCal->isChecked=null;}//Proposition pré-sélectionnée : on l'affiche et décoche l'input principal
				else																				{$propositionShow=$propositionChecked=null;}														//Sinon on masque par défaut l'option de proposition
				$moreInputs.="<div class='vCalAffectProposition' ".$propositionShow." title=\"".Txt::trad("CALENDAR_proposeEvtTooltipBis")."\"><input type='checkbox' name='propositionCalendars[]' value=\"".$tmpCal->_id."\" ".$propositionChecked." class='vCalendarInputProposition'><img src='app/img/calendar/propose.png'></div>";
			}
			//Affiche l'input d'affectation/proposition
			echo "<div class='vCalAffectBlock sTableRow'>
					<input type='checkbox' name='".$calInputName."' value=\"".$tmpCal->_id."\" id=\"box".$tmpCal->_targetObjId."\" class='vCalendarInput' ".$tmpCal->isChecked." ".$tmpCal->isDisabled." ".$calIdUser.">
					<label for=\"box".$tmpCal->_targetObjId."\" class='noTooltip' title=\"".$tmpCal->tooltip."\"><img src=\"app/img/calendar/".$calIcon."\"> ".$tmpCal->title."</label>
					".$moreInputs."
				  </div>";
		}
		////	TOUT SELECTIONNER/DESELECTIONNER  OU SELECTION D'UN GROUPE D'UTILISATEURS
		if(count($affectationCalendars)>2)
		{
			echo "<hr><div class='vCalAffectBlock vCalAffectBlockBis sTableRow' id='calsAffectSwitch'><label><img src='app/img/check.png'> ".Txt::trad("selectUnselectAll")."</label></div>";
			foreach($curSpaceUserGroups as $tmpGroup){
				echo "<div class='vCalAffectBlock vCalAffectBlockBis sTableRow' title=\"".Txt::trad("selectUnselect")." :<br>".$tmpGroup->usersLabel."\">
						<input type='checkbox' name=\"calUsersGroup[]\" value=\"".implode(",",$tmpGroup->userIds)."\" id='calUsersGroup".$tmpGroup->_targetObjId."' onchange=\"userGroupSelect(this,'#calsAffectDiv');\">
						<label for='calUsersGroup".$tmpGroup->_targetObjId."'><img src='app/img/user/userGroup.png'> ".$tmpGroup->title."</label>
					  </div>";
			}
		}
		echo "</div>";
		?>
		<!--CRENEAU HORAIRE OCCUPE?-->
		<div id="timeSlotBusy" class="sAccessWriteLimit">
			<hr><?= Txt::trad("CALENDAR_busyTimeslot") ?>
			<div class="vTimeSlotBusyTable"></div>
		</div>
	</div>

	<!--MENU COMMUN-->
	<?= $curObj->menuEdit() ?>
</form>