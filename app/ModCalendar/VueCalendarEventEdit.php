<script>
////	Resize
lightboxSetWidth(600);

//Init la page
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
		$(this).parents(".vAffectationBlock").addClass("sTableRowSelect");
	});

	////	Change de date/heure/périodicité (sauf pour les guests) :  Controle si les créneaux horaires sont déjà occupés  &  Affiche si besoin les details de périodicité
	<?php if(Ctrl::$curUser->isUser()){ ?>
	$("[name='dateBegin'],[name='timeBegin'],[name='dateEnd'],[name='timeEnd']").change(function(){ timeSlotBusy(); });
	$("[name='periodType'],[name='dateBegin']").change(function(){ displayPeriodType(); });
	<?php } ?>

	////	Check/Unckeck l'input principal d'un agenda (via son "label")
	$(".vCalendarInput").change(function(){
		//Coche une proposition d'evt : affiche la notif "l'événement sera proposé..."
		if(typeof timeoutPropose!="undefined")  {clearTimeout(timeoutPropose);}//Pas de cumul de Timeout
		timeoutPropose=setTimeout(function(thisInput){
			if(/proposition/i.test(thisInput.name) && $(thisInput).prop("checked"))  {notify("<?= Txt::trad("CALENDAR_inputProposed") ?>");}
		},500,this);//Affiche avec un timeout (cf. sélection d'un groupe d'users). Transmet l'input courant en paramètre via "this"
		//Agenda sélectionné : on surligne le block et affiche si besoin l'option de proposition
		if(this.checked)	{$(this).parents(".vAffectationBlock").addClass("sTableRowSelect").find(".vAffectationAddProposition").show();}
		else				{$(this).parents(".vAffectationBlock").removeClass("sTableRowSelect").find(".vAffectationAddProposition").hide();}
		//"uncheck" si besoin l'option de proposition
		$(this).parents(".vAffectationBlock").find(".vCalendarInputProposition").prop("checked",false);
		//Controle d'occupation du créneau horaire de chaque agenda sélectionné
		timeSlotBusy();
	});

	////	Check/Uncheck l'option de proposition pour un agenda
	$(".vCalendarInputProposition").change(function(){
		//"checked" : décoche l'affectation principale et affiche la notif "l'événement sera proposé..."   ||   "unchecked" : masque l'option de proposition et enlève le surlignage de la ligne (retour à l'état initial)
		if(this.checked)	{$(this).parents(".vAffectationBlock").find(".vCalendarInput").prop("checked",false);  notify("<?= Txt::trad("CALENDAR_inputProposed") ?>");}
		else				{$(this).parents(".vAffectationBlock").removeClass("sTableRowSelect").find(".vAffectationAddProposition").hide();}
	});
});

////	Gère l'affichage de la périodicité
function displayPeriodType()
{
	//Réinitialise les options de périodicité & Affiche au besoin l'options sélectionnée
	$("[id^=periodOption_],#periodDetails,#periodDateEnd,#periodDateExceptions").hide();
	if($("[name='periodType']").isEmpty()==false){
		$("#periodOption_"+$("[name='periodType']").val()).show();
		$("#periodDetails, #periodDateEnd, #periodDateExceptions").show();
	}
	//Pré-check si besoin tous les mois
	if($("[name='periodType']").val()=="month" && $("[name*='periodValues_month']:checked").length==0)  {$("input[name*='periodValues_month']").prop("checked","true");}
	//Affiche les détails de périodicité (exple : "le 15 du mois")
	var periodDetails="";
	if($("[name='periodType']").val()=="month")		{periodDetails="<?= Txt::trad("the") ?> "+$("[name='dateBegin']").val().substr(0,2)+" <?= Txt::trad("CALENDAR_period_dayOfMonth") ?> ";}//"le 15 du mois"
	else if($("[name='periodType']").val()=="year")	{periodDetails="<?= Txt::trad("the") ?> "+$("[name='dateBegin']").val().substr(0,5);}//"le 15/10"
	$("#periodDetails").html(periodDetails);
	//Masque les exceptions de périodicité vides
	$("[id^='periodExceptionDiv']").each(function(){
		if($("#"+this.id.replace("Div","Input")).isEmpty())  {$(this).hide();}
	});
}

////	Supprime une "PeriodDateExceptions"
function deletePeriodDateExceptions(exceptionCpt)
{
	var inputSelector="#periodExceptionInput"+exceptionCpt;
	if($(inputSelector).isEmpty() || ($(inputSelector).isEmpty()==false && confirm("<?= Txt::trad("delete") ?>?"))){
		$(inputSelector).val("");
		$("#periodExceptionDiv"+exceptionCpt).hide();
	}
}

////	Controle occupation créneaux horaires des agendas sélectionnés : en AJAX
function timeSlotBusy()
{
	//Lance la requête ajax, avec un "timeout"
	if(typeof timeoutTimeSlotBusy!="undefined")  {clearTimeout(timeoutTimeSlotBusy);}//Pas de cumul de Timeout
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

////	Contrôle du formulaire
function formControl()
{
	//Controle le nombre d'affectations aux agendas
	if($(".vCalendarInput:checked,.vCalendarInputProposition:checked").isEmpty())  {notify("<?= Txt::trad("CALENDAR_verifCalNb") ?>"); return false;}
	//Controle final (champs obligatoires, etc)
	return mainFormControl();
}
</script>

<style>
#blockDescription			{margin-top:15px; <?= empty($curObj->description)?"display:none;":null ?>}
#eventDetails				{text-align:center;}
.vEventDetails				{display:inline-block; margin:10px;}
.vContentVisibleTitle		{text-align:left;}

/*PÉRIODICITÉ*/
[id^='periodOption_'], #periodDetails, #periodDateEnd, #periodDateExceptions	{display:none; margin:10px; text-align:left; vertical-align:middle; margin-left:30px;}
[id^='periodOption_']>div	{display:inline-block;}
#periodOption_weekDay>div	{width:24%;}
#periodOption_month>div		{width:24%;}
#periodDateExceptions>div	{margin:5px;}
#periodDetails				{text-decoration:underline;}

/*AFFECTATION AUX AGENDAS*/
#divAffectationCalendars			{max-height:135px; overflow-y:auto;}
.vAffectationBlock					{display:inline-block; width:48%; margin:2px; margin-right:5px; border-radius:3px;}
.vAffectationBlock .vCalendarInput	{display:none;}
.vAffectationBlock label			{display:inline-block; width:75%; padding:5px;}
.vAffectationBlock img				{max-height:18px;}
.vAffectationAddProposition			{display:none; float:right; padding:3px; background:#ddd;}
.vAffectationAddProposition input	{margin-right:2px;}
input[name='calUsersGroup[]']		{display:none;}

/*GUESTS : MASQUE LES OPTIONS AVANCEES & LE MENU D'AFFECTATION AUX AGENDAS (conserve en "background" l'agenda présélectionné pour l'enregistrement du formulaire)*/
<?php if(Ctrl::$curUser->isUser()==false){ ?>
.vEventDetailsAdvanced, .optionsAffect	{display:none;}
<?php } ?>

/*DÉTAILS SUR L'AFFECTATION*/
#timeSlotBusy				{display:none;}
.vTimeSlotBusyTable			{display:table; margin-top:6px;}
.vTimeSlotBusyRow			{display:table-row;}/*cf. "actionTimeSlotBusy()"*/
.vTimeSlotBusyCell			{display:table-cell; padding:4px; vertical-align:middle;}/*idem*/

/*RESPONSIVE FANCYBOX (440px)*/
@media screen and (max-width:440px){
	select[name="periodType"], select[name="contentVisible"]	{margin-top:10px;}
	.vAffectationBlock											{width:98%;}
	.vAffectationBlock label									{padding:8px;}
}
</style>

<form action="index.php" method="post" onsubmit="return formControl()" enctype="multipart/form-data" class="lightboxContent">

	<!--PAS AUTEUR DE L'EVT : "VOUS N'AVEZ PAS D'ACCES AUX DETAILS"-->
	<?php if($curObj->fullRight()==false)  {echo "<div class='infos'><img src='app/img/info.png'> ".Txt::trad("CALENDAR_editLimit")."</div><br>";} ?>

	<div id="eventDetails">

		<!--TITRE & DESCRIPTION (EDITOR)-->
		<input type="text" name="title" value="<?= $curObj->title ?>" class="textBig" placeholder="<?= Txt::trad("title") ?>">
		<img src="app/img/description.png" class="sLink" title="<?= Txt::trad("description") ?>" onclick="$('#blockDescription').slideToggle()">
		<div id="blockDescription">
			<textarea name="description" placeholder="<?= Txt::trad("description") ?>"><?= $curObj->description ?></textarea>
		</div>
		<br><br>

		<!--DATE DEBUT & FIN-->
		<span class="vEventDetails">
			<input type="text" name="dateBegin" class="dateBegin" value="<?= Txt::formatDate($curObj->dateBegin,"dbDatetime","inputDate") ?>" placeholder="<?= Txt::trad("begin") ?>">
			<input type="text" name="timeBegin" class="timeBegin" value="<?= Txt::formatDate($curObj->dateBegin,"dbDatetime","inputHM") ?>" placeholder="H:m">
			&nbsp; <img src="app/img/arrowRight.png"> &nbsp; 
			<input type="text" name="dateEnd" class="dateEnd" value="<?= Txt::formatDate($curObj->dateEnd,"dbDatetime","inputDate") ?>" placeholder="<?= Txt::trad("end") ?>">
			<input type="text" name="timeEnd" class="timeEnd" value="<?= Txt::formatDate($curObj->dateEnd,"dbDatetime","inputHM") ?>" placeholder="H:m">
		</span>
		
		<!--CATEGORIE-->
		<span class="vEventDetails">
			<?= Txt::trad("CALENDAR_category") ?>
			<select name="_idCat">
				<option value=""></option>
				<?php foreach(MdlCalendarEventCategory::getCategories() as $tmpCat){ ?>
				<option value="<?= $tmpCat->_id ?>" data-color="<?= $tmpCat->color ?>"><?= $tmpCat->title ?></option>
				<?php } ?>
			</select>
		</span>
		
		<!--IMPORTANT-->
		<span class="vEventDetails vEventDetailsAdvanced">
			<?= Txt::trad("important") ?>
			<select name="important">
				<option value="0"><?= Txt::trad("no") ?></option>
				<option value="1" data-color="#900"><?= Txt::trad("yes") ?></option>
			</select>
		</span>
	
		<!--VISIBILITE-->
		<span class="vEventDetails vEventDetailsAdvanced">
			<select name="contentVisible" title="<div class='vContentVisibleTitle'><?= Txt::trad("CALENDAR_visibilityInfo") ?></div>">
				<option value="public"><?= Txt::trad("CALENDAR_visibilityPublic") ?></option>
				<option value="public_cache"><?= Txt::trad("CALENDAR_visibilityPublicHide") ?></option>
				<option value="prive"><?= Txt::trad("CALENDAR_visibilityPrivate") ?></option>
			</select>
		</span>
	
		<!--PERIODICITE-->
		<span class="vEventDetails vEventDetailsAdvanced">
			<select name="periodType">
				<option value=""><?= Txt::trad("CALENDAR_noPeriodicity") ?></option>
				<option value="weekDay"><?= Txt::trad("CALENDAR_period_weekDay") ?></option>
				<option value="month"><?= Txt::trad("CALENDAR_period_month") ?></option>
				<option value="year"><?= Txt::trad("CALENDAR_period_year") ?></option>
			</select>
		</span>

		<!--PERIODICITE: DETAIL (exple: "le 15 du mois"-->
		<div id="periodDetails">&nbsp;</div>

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
			<span class="sLink" onclick="$('[id^=periodExceptionDiv]:hidden').first().show()"><?= Txt::trad("CALENDAR_periodException") ?> <img src="app/img/plusSmall.png"></span>
			<?php
			//Liste des exceptions (10 maxi)
			for($cpt=1; $cpt<=10; $cpt++){
				echo "<div id='periodExceptionDiv".$cpt."'>
						<input type='text' name='periodDateExceptions[]' value=\"".(isset($periodDateExceptions[$cpt])?$periodDateExceptions[$cpt]:null)."\" class='dateInput' id='periodExceptionInput".$cpt."'>
						<img src='app/img/delete.png' title=\"".Txt::trad("delete")."\" class='sLink' onclick=\"deletePeriodDateExceptions(".$cpt.");\">
					  </div>";
			}
			?>
		</div>
	</div>

	<!--AFFECTATIONS AUX AGENDAS-->
	<div class="lightboxBlockTitle optionsAffect"><?= Txt::trad("CALENDAR_calendarAffectations") ?> <img src="app/img/switch.png" class="sLink" onclick="$('.vCalendarInput:enabled').trigger('click');" title="<?= Txt::trad("invertSelection") ?>"></div>
	<div class="lightboxBlock optionsAffect">
		<?php
		echo "<div id='divAffectationCalendars'>";
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
			//Astérisque sur les agendas non-modifiables || proposition
			if($tmpCal->isDisabled!=null)				{$tmpCal->title.=" &#42;&#42;";}
			elseif($tmpCal->inputType=="proposition")	{$tmpCal->title.=" &#42;";}
			//Affiche l'option de proposition d'événement (en plus du champ principal avec le label)
			if($tmpCal->inputType=="affectation" && $tmpCal->isMyPerso()==false){
				if($curObj->isNew()==false && in_array($tmpCal,$curObj->affectedCalendars(false)))  {$propositionShow="style='display:block;'"; $propositionChecked="checked"; $tmpCal->isChecked=null;}//Proposition pré-sélectionnée : on l'affiche et décoche l'input principal
				else																				{$propositionShow=$propositionChecked=null;}														//Sinon on masque par défaut l'option de proposition
				$moreInputs.="<div class='vAffectationAddProposition' ".$propositionShow." title=\"".Txt::trad("CALENDAR_proposeEvtTooltipBis")."\"><input type='checkbox' name='propositionCalendars[]' value=\"".$tmpCal->_id."\" ".$propositionChecked." class='vCalendarInputProposition'><img src='app/img/calendar/propose.png'></div>";
			}
			//Affiche l'input d'affectation/proposition
			echo "<div class='vAffectationBlock sTableRow'>
					<input type='checkbox' name='".$calInputName."' value=\"".$tmpCal->_id."\" id=\"box".$tmpCal->_targetObjId."\" class='vCalendarInput' ".$tmpCal->isChecked." ".$tmpCal->isDisabled." ".$calIdUser.">
					<label for=\"box".$tmpCal->_targetObjId."\" title=\"".$tmpCal->tooltip."\"><img src=\"app/img/calendar/".$calIcon."\"> ".$tmpCal->title."</label>
					".$moreInputs."
				  </div>";
		}
		////	SELECTION D'UN GROUPE D'UTILISATEURS
		if(!empty($curSpaceUserGroups))  {echo "<hr>";}
		foreach($curSpaceUserGroups as $tmpGroup){
			echo "<div class='vAffectationBlock sTableRow' title=\"".Txt::trad("selectUnselect")." :<br>".$tmpGroup->usersLabel."\">
					<input type='checkbox' name=\"calUsersGroup[]\" value=\"".implode(",",$tmpGroup->userIds)."\" id='calUsersGroup".$tmpGroup->_targetObjId."' onchange=\"userGroupSelect(this,'#divAffectationCalendars');\">
					<label for='calUsersGroup".$tmpGroup->_targetObjId."'><img src='app/img/user/userGroup.png'> ".$tmpGroup->title."</label>
				  </div>";
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