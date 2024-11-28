<script>
////	Resize
lightboxSetWidth(800);

////	INIT
$(function(){
	////	INIT L'AFFICHAGE DU FORMULAIRE
	$("select[name='periodType']").val("<?= $curObj->periodType ?>");						//Prérempli la périodicité
	$("select[name='contentVisible']").val("<?= $curObj->contentVisible ?>");				//Prérempli le "contentVisible"
	$("select[name='important']").val("<?= (int)$curObj->important ?>").trigger("change");	//"trigger" sur "important" pour changer la couleur de l'input
	displayPeriodType();																	//Init les options de "périodicité"
	timeSlotBusy();																			//Init les créneaux horaires occupés

	<?php
	////	GUEST OU USER PAS AUTEUR DE L'EVT
	if(Ctrl::$curUser->isUser()==false)	{echo '$(".vEvtOptionAdvanced,#eventAffectations").hide();';}//Guest : masque les options avancées et l'affectation aux agendas (mais garde en "background")
	elseif($curObj->fullRight()==false)	{echo '$(".vEvtOptionAdvanced,.inputTitleName,.descriptionToggle,.descriptionTextarea,#eventDates").hide();';}//User pas auteur de l'evt : masque les principaux champs, sauf les affectations aux agendas
	?>

	////	INIT LE SURLIGNAGE DES AGENDAS PRÉSÉLECTIONNÉS
	$(".vCalInput:checked").each(function(){
		$(this).parents(".vCalAffectBlock").addClass("lineSelect");
	});

	////	CHANGE DE DATE/HEURE/PÉRIODICITÉ : CONTROLE SI LES CRÉNEAUX HORAIRES SONT DÉJÀ OCCUPÉS  &  AFFICHE AU BESOIN LES DETAILS DE PÉRIODICITÉ
	<?php if(Ctrl::$curUser->isUser()){ ?>
	$("[name='dateBegin'],[name='timeBegin'],[name='dateEnd'],[name='timeEnd']").on("change",function(){ timeSlotBusy(); });
	$("[name='periodType'],[name='dateBegin']").on("change",function(){ displayPeriodType(); });
	<?php } ?>

	////	VISIO : "AJOUTER UNE VISIO"
	$("#visioUrlAdd").on("click",function(){
		if(confirm("<?= Txt::trad("VISIO_urlAdd") ?> ?")){				//Confirme l'ajout de la visio
			$(this).hide();												//Masque le label "Ajouter une visio"
			$("#visioOptions").show();									//Affiche l'input / copy / delete
			$("#visioUrlInput").val("<?= Ctrl::$agora->visioUrl() ?>");	//Spécifie l'URL d'une visio avec un identifiant aléatoire
		}
	});

	////	VISIO : SUPPRIME L'URL
	$("#visioUrlDelete").on("click",function(){
		if(confirm("<?= Txt::trad("VISIO_urlDelete") ?> ?")){			//Confirme la suppression de la visio
			$("#visioUrlInput").val("");								//Réinit l'url de la visio
			$("#visioOptions").hide();									//Affiche l'input / copy / delete
			$("#visioUrlAdd").show();									//Affiche le label "Ajouter une visio"
		}
	});

	////	VISIO : LANCE LA VISIO DEPUIS L'UNPUT
	$("#visioUrlInput").on("click",function(){
		launchVisio(this.value);
	});

	////	VISIO : COPIE L'URL DANS LE PRESSE PAPIER
	$("#visioUrlCopy").on("click",function(){
		if(confirm("<?= Txt::trad("VISIO_urlCopy") ?> ?")){
			$("#visioUrlInput").select();
			document.execCommand('copy');
			notify("<?= Txt::trad("copyUrlConfirmed") ?>");
		}
	});

	////	SELECTION D'AGENDA : CHECK/UNCKECK L'INPUT PRINCIPAL D'UN AGENDA VIA SON LABEL
	$(".vCalInput").on("change",function(){
		//Coche une proposition d'evt : affiche la notif "l'événement sera proposé..."
		if(typeof timeoutPropose!="undefined")  {clearTimeout(timeoutPropose);}//Pas de cumul de Timeout !
		timeoutPropose=setTimeout(function(thisInput){
			if(/proposition/i.test(thisInput.name) && $(thisInput).prop("checked"))  {notify("<?= Txt::trad("CALENDAR_inputProposed") ?>");}
		},500,this);//Affiche avec un timeout (cf. sélection d'un groupe d'users). Transmet l'input courant en paramètre via "this"
		//Agenda sélectionné : on surligne le block et affiche si besoin l'option de proposition
		if(this.checked)	{$(this).parents(".vCalAffectBlock").addClass("lineSelect").find(".vCalAffectProposition").show();}
		else				{$(this).parents(".vCalAffectBlock").removeClass("lineSelect").find(".vCalAffectProposition").hide();}
		//"uncheck" si besoin l'option de proposition
		$(this).parents(".vCalAffectBlock").find(".vCalInputProposition").prop("checked",false);
		//Controle d'occupation du créneau horaire de chaque agenda sélectionné
		timeSlotBusy();
	});

	////	CHECK/UNCHECK L'OPTION DE PROPOSITION POUR UN AGENDA
	$(".vCalInputProposition").on("change",function(){
		//"checked" : décoche l'affectation principale et affiche la notif "l'événement sera proposé..."   ||   "unchecked" : masque l'option de proposition et enlève le surlignage de la ligne (retour à l'état initial)
		if(this.checked)	{$(this).parents(".vCalAffectBlock").find(".vCalInput").prop("checked",false);  notify("<?= Txt::trad("CALENDAR_inputProposed") ?>");}
		else				{$(this).parents(".vCalAffectBlock").removeClass("lineSelect").find(".vCalAffectProposition").hide();}
	});
});


////	GÈRE L'AFFICHAGE DE LA PÉRIODICITÉ
function displayPeriodType()
{
	//Réinitialise les options de périodicité & Affiche au besoin l'options sélectionnée
	$("#periodOptions, #periodTypeLabel, #periodOption_weekDay, #periodOption_month, #periodDateEnd, #periodDateExceptions").hide();
	if($("[name='periodType']").isNotEmpty())  {$("#periodOptions, #periodTypeLabel, #periodDateEnd, #periodDateExceptions, #periodOption_"+$("[name='periodType']").val()).fadeIn();}
	//Affiche les détails de périodicité (ex: "Tous les mois, le 15")
	if($("[name='periodType']").val()=="weekDay")		{$("#periodTypeLabel").html("<?= Txt::trad("CALENDAR_period_weekDay") ?>");}																//"Toutes les semaines"
	else if($("[name='periodType']").val()=="month")	{$("#periodTypeLabel").html("<?= Txt::trad("CALENDAR_period_month").", ".Txt::trad("the") ?> "+$("[name='dateBegin']").val().substr(0,2));}	//"Tous les mois, le 22"
	else if($("[name='periodType']").val()=="year")		{$("#periodTypeLabel").html("<?= Txt::trad("CALENDAR_period_year").", ".Txt::trad("the") ?> "+$("[name='dateBegin']").val().substr(0,5));}	//"Tous les ans, le 15/10"
	//Pré-check si besoin tous les mois
	if($("[name='periodType']").val()=="month" && $("[name*='periodValues_month']:checked").length==0)  {$("input[name*='periodValues_month']").prop("checked","true");}
	//Masque les exceptions de périodicité vides
	$(".periodExceptionDiv").each(function(){
		if($("#"+this.id.replace("Div","Input")).isEmpty())  {$(this).hide();}
	});
}


////	SUPPRIME UNE "PERIODDATEEXCEPTIONS"
function deletePeriodDateExceptions(exceptionCpt)
{
	var inputSelector="#periodExceptionInput"+exceptionCpt;
	if($(inputSelector).isEmpty() || ($(inputSelector).isNotEmpty() && confirm("<?= Txt::trad("delete") ?>?"))){
		$(inputSelector).val("");
		$("#periodExceptionDiv"+exceptionCpt).hide();
	}
}


////	CONTROLE L'OCCUPATION DES CRÉNEAUX HORAIRES DES AGENDAS SÉLECTIONNÉS (AJAX)
function timeSlotBusy()
{
	if(typeof timeoutTimeSlotBusy!="undefined")  {clearTimeout(timeoutTimeSlotBusy);}//Pas de cumul de Timeout !
	timeoutTimeSlotBusy=setTimeout(function(){
		if($("[name='dateBegin']").isNotEmpty() && $("[name='dateEnd']").isNotEmpty() && $("[name='dateBegin']").val()==$("[name='dateEnd']").val()){
			//Init l'url, avec le créneau horaire et les agendas concernés
			var ajaxUrl="?ctrl=calendar&action=timeSlotBusy"+
						"&dateTimeBegin="+encodeURIComponent($("[name='dateBegin']").val()+" "+$("[name='timeBegin']").val())+
						"&dateTimeEnd="+encodeURIComponent($("[name='dateEnd']").val()+" "+$("[name='timeEnd']").val())+
						"&_evtId=<?= $curObj->_id ?>&objectsTypeId[calendar]=";
						$(".vCalInput:checked,.vCalInputProposition:checked").each(function(){  ajaxUrl+=this.value+"-";  });
			//Lance le controle Ajax et renvoie les agendas où le créneau est occupé
			$.ajax(ajaxUrl).done(function(txtResult){
				if(txtResult.length>0)	{$("#timeSlotBusy").fadeIn();  $(".vTimeSlotBusyTable").html(txtResult); }
				else					{$("#timeSlotBusy").hide();}
			});
		}
	}, 1000);
}


////	Controle spécifique à l'objet (cf. "VueObjEditMenuSubmit.php")
function objectFormControl()
{
	return new Promise((resolve)=>{
		//// Controle le nombre d'affectations aux agendas
		if($(".vCalInput:checked,.vCalInputProposition:checked").isEmpty())
			{notify("<?= Txt::trad("CALENDAR_verifCalNb") ?>");  resolve(false);}
		//// Controle des "guests"
		if($("input[name='guest']").exist()){
			//// Controle du champ "guest" & "guestMail"
			if($("input[name='guest']").val().length<3)													{notify("<?= Txt::trad("EDIT_guestNameNotif") ?>");  resolve(false);}
			if($("input[name='guestMail']").isEmpty() || $("input[name='guestMail']").isMail()==false)	{notify("<?= Txt::trad("mailInvalid") ?>");  resolve(false);}
			//// Controle du Captcha via Ajax
			$.ajax("?ctrl=misc&action=CaptchaControl&captcha="+encodeURIComponent($("#captchaText").val())).done(function(result){
				if(/true/i.test(result)==false)		{notify("<?=Txt::trad("captchaError") ?>");  resolve(false);}
				else								{resolve(true);}							
			});
		}
		//// Controle OK : Renvoi "true"
		else  {resolve(true);}
	});
}
</script>


<style>
/*GENERAL*/
legend			 						{font-size:1.05em;}
.vEvtOptionInline						{display:inline-block; margin:25px 25px 0px 0px;}
.beginEndLabel							{display:none}
#beginEndSeparator						{margin:0px 5px;}
@media screen and (max-width:440px){
	#beginEndSeparator					{visibility:hidden; display:block;}
	.beginEndLabel						{display:inline-block; width:50px;}
	input:read-only						{background:#eee!important;}/*sélection des dates & times*/
}

/*PÉRIODICITÉ*/
#periodOptions					 							{display:none; margin-top:20px; margin-bottom:10px;}
#periodOption_weekDay, #periodOption_month					{text-align:left; vertical-align:middle;}/*checkboxes des jours ou des mois*/
#periodOption_weekDay>div, #periodOption_month>div			{display:inline-block; width:25%; padding:0px 10px 10px 0px;}
#periodDateEnd, #periodDateExceptions, .periodExceptionDiv	{display:inline-block; margin:10px 5px; line-height:35px;}
/*MOBILE*/
@media screen and (max-width:440px){
	#periodOption_weekDay>div, #periodOption_month>div		{width:33%;}
}

/*VISIOCONFERENCE*/
#visioUrlAdd							{line-height:35px;}
#visioUrlInput							{width:280px; font-size:0.95em;}
<?= empty($curObj->visioUrl) ? "#visioOptions{display:none;}" : "#visioUrlAdd{display:none;}" ?>/*masque "Ajouter une visio"  ||  masque l'input de la visio*/

/*AFFECTATION AUX AGENDAS*/
#calsAffectDiv							{max-height:200px; overflow-y:auto;}
#calsAffectDiv hr						{margin:3px;}
.vCalAffectBlock						{display:inline-block; width:32%; margin:2px; margin-right:5px; border-radius:3px;}
.vCalAffectBlock .vCalInput				{display:none;}
.vCalAffectBlock label					{display:inline-block; width:75%; padding:5px 3px 5px 3px;}
.vCalAffectBlock img					{max-height:18px;}
.vCalAffectBlockBis label				{width:100%;}
.vCalAffectProposition					{display:none; float:right; height:25px; padding:3px; background:#e5e5e5;}
.vCalAffectProposition input			{margin-right:5px;}
input[name='calUsersGroup[]']			{display:none;}
/*MOBILE*/
@media screen and (max-width:440px){
	#calsAffectDiv						{max-height:500px;}
	.vCalAffectBlock					{width:96%;}
	.vCalAffectBlock label				{padding:8px 3px 8px 3px;}
}

/*GUESTS*/
#guestMenu								{text-align:center;}
input[name='guestMail']					{margin-left:20px;}

/*AFFICHAGE DU "timeSlotBusy()"*/
#timeSlotBusy							{display:none;}
.vTimeSlotBusyTable						{display:table; margin-top:6px;}
.vTimeSlotBusyRow						{display:table-row;}
.vTimeSlotBusyCell						{display:table-cell; min-width:120px; padding:4px; vertical-align:middle;}
</style>


<form action="index.php" method="post" id="mainForm" enctype="multipart/form-data">
	<!--TITRE MOBILE  &&  SI BESOIN "VOUS N'AVEZ PAS D'ACCES AUX DETAILS DE L'EVT"-->
	<?= $curObj->titleMobile("CALENDAR_addEvt") ?>
	<?php if($curObj->fullRight()==false)  {echo "<div class='infos'><img src='app/img/info.png'> ".Txt::trad("CALENDAR_editLimit")."</div><br>";} ?>

	<!--TITRE / DESCRIPTION-->
	<input type="text" name="title" value="<?= $curObj->title ?>" class="inputTitleName" placeholder="<?= Txt::trad("title") ?>">
	<?= $curObj->editDescription() ?>

	<!--DATE DEBUT & FIN-->
	<div class="vEvtOptionInline" id="eventDates">
		<span class="beginEndLabel"><?= Txt::trad("begin") ?></span>
		<input type="text" name="dateBegin" class="dateBegin" value="<?= Txt::formatDate($curObj->dateBegin,"dbDatetime","inputDate") ?>" <?= Txt::tooltip("begin") ?>>
		<input type="time" name="timeBegin" class="timeBegin" value="<?= Txt::formatDate($curObj->dateBegin,"dbDatetime","inputHM") ?>" <?= Txt::tooltip("begin") ?>>
		<span id="beginEndSeparator"><img src="app/img/arrowRight.png"></span>
		<span class="beginEndLabel"><?= Txt::trad("end") ?></span>
		<input type="text" name="dateEnd" class="dateEnd" value="<?= Txt::formatDate($curObj->dateEnd,"dbDatetime","inputDate") ?>" <?= Txt::tooltip("end") ?>>
		<input type="time" name="timeEnd" class="timeEnd" value="<?= Txt::formatDate($curObj->dateEnd,"dbDatetime","inputHM") ?>" <?= Txt::tooltip("end") ?>>
	</div>

	<!--<SELECT> DE LA CATEGORIE-->
	<div class="vEvtOptionInline vEvtOptionAdvanced">
		<?= MdlCalendarCategory::selectInput($curObj->_idCat) ?>
	</div>

	<!--PERIODICITE-->
	<div class="vEvtOptionInline vEvtOptionAdvanced">
		<select name="periodType">
			<option value=""><?= Txt::trad("CALENDAR_noPeriodicity") ?></option>
			<option value="weekDay"><?= Txt::trad("CALENDAR_period_weekDay") ?></option>
			<option value="month"><?= Txt::trad("CALENDAR_period_month") ?></option>
			<option value="year"><?= Txt::trad("CALENDAR_period_year") ?></option>
		</select>
	</div>

	<!--PERIODICITE : DIV DES OPTIONS-->
	<fieldset class="vEvtOptionAdvanced" id="periodOptions">
		<!--PERIODICITE: DETAIL POUR LES PERIODICITES MOIS/ANNEE (ex: "le 22 du mois")-->
		<legend id="periodTypeLabel">&nbsp;</legend>
		<!--PERIODICITE: JOURS DE LA SEMAINE-->
		<div id="periodOption_weekDay">
			<?php
			for($cpt=1; $cpt<=7; $cpt++){
				$periodValueChecked=($curObj->periodType=="weekDay" && in_array($cpt,$tabPeriodValues))  ?  "checked"  :  null;
				echo '<div>
						<input type="checkbox" name="periodValues_weekDay[]" value="'.$cpt.'" id="periodValues_weekDay'.$cpt.'" '.$periodValueChecked.' >
						<label for="periodValues_weekDay'.$cpt.'">'.Txt::trad("day_".$cpt).'</label>
					  </div>';
			}
			?>
		</div>
		<!--PERIODICITE: MOIS DE L'ANNEE-->
		<div id="periodOption_month">
			<?php
			for($cpt=1; $cpt<=12; $cpt++){
				$periodValueChecked=($curObj->periodType=="month" && in_array($cpt,$tabPeriodValues))  ?  "checked"  :  null;
				echo '<div>
						<input type="checkbox" name="periodValues_month[]" value="'.$cpt.'" id="periodValues_month'.$cpt.'" '.$periodValueChecked.' >
						<label for="periodValues_month'.$cpt.'">'.Txt::trad("month_".$cpt).'</label>
					  </div>';
			}
			?>
		</div>
		<!--PERIODICITE: FIN-->
		<div id="periodDateEnd">
			<?= Txt::trad("CALENDAR_periodDateEnd") ?> <input type="text" name="periodDateEnd" class="dateInput" value="<?= Txt::formatDate($curObj->periodDateEnd,"dbDate","inputDate") ?>">
		</div>
		<!--EXCEPTIONS DE PERIODICITE-->
		<div id="periodDateExceptions">
			<span  onclick="$('.periodExceptionDiv:hidden:first').fadeIn()"><?= Txt::trad("CALENDAR_periodException") ?> <img src="app/img/plusSmall.png"></span>
		</div>
		<?php
		////	Dates d'exceptions de périodicité (10 max)
		for($cpt=1; $cpt<=10; $cpt++){
			echo '<div id="periodExceptionDiv'.$cpt.'" class="periodExceptionDiv">
					<input type="text" name="periodDateExceptions[]" value="'.(isset($periodDateExceptions[$cpt])?$periodDateExceptions[$cpt]:null).'" class="dateInput" id="periodExceptionInput'.$cpt.'">
					<img src="app/img/delete.png" onclick="deletePeriodDateExceptions('.$cpt.')" '.Txt::tooltip("delete").'>
				  </div>';
		}
		?>
	</fieldset>

	<!--IMPORTANT-->
	<div class="vEvtOptionInline vEvtOptionAdvanced">
		<select name="important">
			<option value="0"><?= Txt::trad("CALENDAR_importanceNormal") ?></option>
			<option value="1" data-color="#900"><?= Txt::trad("CALENDAR_importanceHight") ?></option>
		</select>
	</div>

	<!--VISIBILITE-->
	<div class="vEvtOptionInline vEvtOptionAdvanced">
		<select name="contentVisible" <?= Txt::tooltip("CALENDAR_visibilityTooltip") ?>>
			<option value="public"><?= Txt::trad("CALENDAR_visibilityPublic") ?></option>
			<option value="public_cache"><?= Txt::trad("CALENDAR_visibilityPublicHide") ?></option>
			<option value="prive"><?= Txt::trad("CALENDAR_visibilityPrivate") ?></option>
		</select>
	</div>

	<!--VISIOCONFERENCE-->
	<?php if(Ctrl::$agora->visioEnabled()){ ?>
	<div class="vEvtOptionInline vEvtOptionAdvanced">
		<span id="visioUrlAdd" class="sLink"><img src="app/img/visioSmall.png"> <?= Txt::trad("VISIO_urlAdd") ?></span>
		<span id="visioOptions">
			<input type="text" name="visioUrl" value="<?= $curObj->visioUrl ?>" id="visioUrlInput" class="sLink" <?= Txt::tooltip("VISIO_launchFromEvent") ?> readonly>
			<img src="app/img/copy.png" id="visioUrlCopy" class="sLink" <?= Txt::tooltip("VISIO_urlCopy") ?>>
			<img src="app/img/delete.png" id="visioUrlDelete" class="sLink" <?= Txt::tooltip("VISIO_urlDelete") ?>>
		</span>
	</div>
	<?php } ?>

	<!--AFFECTATIONS AUX AGENDAS-->
	<fieldset id="eventAffectations">
		<legend><?= Txt::trad("CALENDAR_calendarAffectations") ?></legend>
		<?php
		echo '<div id="calsAffectDiv">';
		////	AGENDAS DE RESSOURCES & AGENDAS PERSONNELS
		foreach($affectationCalendars as $tmpCal)
		{
			//Nom de l'input  &&  Icone du label (Agenda d'user ou de ressource)
			$inputName=($tmpCal->mainInput=="affectation")  ?  "affectationCalendars[]"  :  "propositionCalendars[]";
			if($tmpCal->type=="user")	{$iconLabel="typeUser.png";			$dataIdUser='data-idUser="'.$tmpCal->_idUser.'"';}
			else						{$iconLabel="typeRessource.png";	$dataIdUser=null;}
			//Astérisque "**" sur les agendas non-modifiables || proposition d'evt possible
			if($tmpCal->isDisabled!=null || $tmpCal->mainInput=="proposition")	{$tmpCal->title.=" &#42;&#42;";}
			//Réinit l'affectation/proposition après validation du form?  &&  Ajoute l'option de proposition d'événement ?
			$moreInputs=($tmpCal->reinitCalendarInput==true)  ?  '<input type="hidden" name="reinitCalendars[]" value="'.$tmpCal->_id.'">'  :  null;
			if($tmpCal->mainInput=="affectation" && $tmpCal->isPersonalCalendar()==false){
				if($curObj->isNew()==false && in_array($tmpCal,$curObj->affectedCalendars(false)))  {$propositionShow="style='display:block;'";  $propositionChecked="checked";  $tmpCal->isChecked=null;}	//Proposition pré-sélectionnée : on l'affiche et décoche l'input principal
				else																				{$propositionShow=$propositionChecked=null;}															//Sinon on masque par défaut l'option de proposition
				$moreInputs.='<div class="vCalAffectProposition" '.$propositionShow.' '.Txt::tooltip("CALENDAR_proposeEvtTooltipBis").'><input type="checkbox" name="propositionCalendars[]" value="'.$tmpCal->_id.'" '.$propositionChecked.' class="vCalInputProposition"><img src="app/img/calendar/propose.png"></div>';
			}
			//Affiche l'input d'affectation/proposition
			echo '<div class="vCalAffectBlock lineHover">
					<input type="checkbox" name="'.$inputName.'" value="'.$tmpCal->_id.'" id="box'.$tmpCal->_typeId.'" class="vCalInput" '.$tmpCal->isChecked.' '.$tmpCal->isDisabled.' '.$dataIdUser.'>
					<label for="box'.$tmpCal->_typeId.'" class="noTooltip" '.Txt::tooltip($tmpCal->labelTooltip).'><img src="app/img/calendar/'.$iconLabel.'"> '.$tmpCal->title.'</label>
					'.$moreInputs.'
				  </div>';
		}
		////	SWITCH LA SELECTION OU SELECTIONNE UN GROUPE D'USERS
		if(count($affectationCalendars)>2)
		{
			echo "<hr><div class='vCalAffectBlock vCalAffectBlockBis lineHover' onclick=\"$('.vCalInput:enabled').trigger('click')\"><label><img src='app/img/checkSmall.png'> ".Txt::trad("selectSwitch")."</label></div>";
			foreach($curSpaceUserGroups as $tmpGroup){
				echo '<div class="vCalAffectBlock vCalAffectBlockBis lineHover" '.Txt::tooltip(Txt::trad("selectUnselect")." :<br>".$tmpGroup->usersLabel).'>
						<input type="checkbox" name="calUsersGroup[]" value="'.implode(",",$tmpGroup->userIds).'" id="calUsersGroup'.$tmpGroup->_typeId.'" onchange="userGroupSelect(this,\'#calsAffectDiv\')">
						<label for="calUsersGroup'.$tmpGroup->_typeId.'"><img src="app/img/user/accessGroup.png"> '.$tmpGroup->title.'</label>
					  </div>';
			}
		}
		echo '</div>';
		?>
		<!--CRENEAU HORAIRE OCCUPE?-->
		<div id="timeSlotBusy" class="sAccessWriteLimit">
			<hr><?= Txt::trad("CALENDAR_busyTimeslot") ?>
			<div class="vTimeSlotBusyTable"></div>
		</div>
	</fieldset>

	<?php
	////	MENU D'IDENTIFICATION DES GUESTS & CAPTCHA
	if(Ctrl::$curUser->isUser()==false){
		echo '<fieldset id="guestMenu">
				<input type="text" name="guest" placeholder="'.Txt::trad("EDIT_guestName").'">
				<input type="text" name="guestMail" placeholder="'.Txt::trad("EDIT_guestMail").'" '.Txt::tooltip("EDIT_guestMailTooltip").'>
				<hr>'.CtrlMisc::menuCaptcha().'
			  </fieldset>';
	}

	////	MENU COMMUN	(VALIDATION DU FORM UNIQUEMENT)
	if($curObj->fullRight())	{echo $curObj->editMenuSubmit();}
	else						{echo Txt::submitButton();}
	?>
</form>