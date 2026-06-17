<script>
////	INIT
ready(function(){
	////	INIT LE FORMULAIRE (périodicité / contentVisible / important)
	$("select[name='periodType']").val("<?= $curObj->periodType ?>");
	$("select[name='contentVisible']").val("<?= $curObj->contentVisible ?>");
	$("select[name='important']").val("<?= (int)$curObj->important ?>");
	displayPeriodType();

	////	CHANGE DE DATE/HEURE : CONTROLE LES CRÉNEAUX HORAIRES OCCUPÉS
	$(".dateBegin,.timeBegin,.dateEnd,.timeEnd").on("change",function(){ timeSlotBusy(); });

	////	EVT SUR TOUTE LA JOURNEE
	$("#allDayCheckbox").on("change",function(){
		//// Evt sur toute la journée : masque l'heure de début/fin + enregistre les valeurs d'origine (si retour en arrière) + modif les valeurs
		if(this.checked){
			$(".timeBegin,.timeEnd").hide();
			timeBeginValue=$(".timeBegin").val();
			timeEndValue=$(".timeEnd").val();
			$(".timeBegin").val("00:00");
			$(".timeEnd").val("23:59");
		}
		//// Sinon : affiche l'heure de début/fin +  remet les valeurs d'origine OU affiche l'heure courante
		else{
			$(".timeBegin,.timeEnd").show();
			if(typeof timeBeginValue!="undefined"){
				if(timeBeginValue=="00:00"){
					const now=new Date();
					const hoursBegin=String(now.getHours()).padStart(2,'0')+':00';//Exemple: 09:00 et 10:00
					const hoursEnd  =String(now.getHours()+1).padStart(2,'0')+':00';
					$(".timeBegin").val(hoursBegin);
					$(".timeEnd").val(hoursEnd);
				}else{
					$(".timeBegin").val(timeBeginValue);
					$(".timeEnd").val(timeEndValue);
				}
			}
		}
	}).trigger("change");

	////	PÉRIODICITÉ : AFFICHAGE & DETAILS
	$("[name='periodType'],.dateBegin").on("change",function(){ displayPeriodType(); });

	////	PÉRIODICITÉ : AJOUTE UNE DATE D'EXCEPTION
	$("#periodDateExceptionsAdd").on("click",function(){
		$('.vPeriodDateExceptionsInput:hidden:first').fadeIn().css("display","inline-block");
	});

	////	PÉRIODICITÉ : SUPPRIME UNE DATE D'EXCEPTION
	$(".vPeriodDateExceptionsDelete").on("click",async function(){
		if($(this).parent(".vPeriodDateExceptionsInput").find("input").isEmpty() || await confirmAlt("<?= Txt::trad("confirmDelete") ?>")){
			$(this).parent(".vPeriodDateExceptionsInput").find("input").val("");
			$(this).parent(".vPeriodDateExceptionsInput").hide();
		}
	});

	////	VISIO : "AJOUTER UNE VISIO"
	$("#visioUrlAdd").on("click", async function(){
		if(await confirmAlt("<?= Txt::trad("VISIO_urlAddConfirm") ?>")){
			$("#visioUrlInput").val("<?= Ctrl::$agora->visioUrl() ?>");		//Spécifie l'URL de la visio
			$("#visioInputs").show();										//Affiche l'input & co
			$(this).hide();													//Masque "Ajouter une visio"
		}
	});

	////	VISIO : COPIE L'URL DANS LE PRESSE PAPIER
	$("#visioUrlCopy").on("click", async function(){
		if(await confirmAlt("<?= Txt::trad("VISIO_urlCopy") ?>")){
			navigator.clipboard.writeText($("#visioUrlInput").val()).then(()=>{  notify("<?= Txt::trad("copyUrlNotif") ?>");  });
		}
	});

	////	VISIO : SUPPRIME L'URL
	$("#visioUrlDelete").on("click", async function(){
		if(await confirmAlt("<?= Txt::trad("VISIO_urlDelete") ?>")){
			$("#visioUrlInput").val("");	//Réinit l'url
			$("#visioInputs").hide();		//Masque l'input & co
			$("#visioUrlAdd").show();		//Affiche "Ajouter une visio"
		}
	});

	////	SELECTION D'AGENDA : ADD/REMOVE LA CLASS "optionSelect" ET VERIF LES CRÉNEAUX HORAIRES OCCUPÉS
	$(".vCalInput").on("change",function(){
		if(this.checked)	{$(this).parents(".vCalAffectation").addClass("optionSelect");}
		else				{$(this).parents(".vCalAffectation").removeClass("optionSelect");}
		timeSlotBusy();
	}).trigger("change");
});


////	PÉRIODICITÉ : AFFICHAGE & DETAILS
function displayPeriodType()
{
	//Réinitialise les options de périodicité & Affiche au besoin l'options sélectionnée
	$("#periodFieldset, #periodLegend, #periodType_weekDay, #periodType_month, #periodDateEnd, #periodDateExceptions").hide();
	if($("[name='periodType']").notEmpty())  {$("#periodFieldset, #periodLegend, #periodDateEnd, #periodDateExceptions, #periodType_"+$("[name='periodType']").val()).fadeIn();}
	//Affiche les détails de périodicité (ex: "Tous les mois, le 15")
	let dateBeginValue=$(".dateBegin").val();
	if($("[name='periodType']").val()=="weekDay")		{$("#periodLegend").html("<?= Txt::trad("CALENDAR_period_weekDay") ?>");}																//"Toutes les semaines"
	else if($("[name='periodType']").val()=="month")	{$("#periodLegend").html(String("<?= Txt::trad("CALENDAR_period_monthDetail") ?>").replace("--DATE--",dateBeginValue.substr(0,2)));}	//"Tous les 15 du mois"
	else if($("[name='periodType']").val()=="year")		{$("#periodLegend").html(String("<?= Txt::trad("CALENDAR_period_yearDetail") ?>").replace("--DATE--",dateBeginValue.substr(0,5)));}		//"Tous les ans, le 15/10"
	//Pré-check si besoin tous les mois
	if($("[name='periodType']").val()=="month" && $("[name*='periodValues_month']:checked").length==0)  {$("input[name*='periodValues_month']").prop("checked","true");}
}

////	CRÉNEAUX HORAIRES OCCUPÉS SUR LES AGENDAS SÉLECTIONNÉS
function timeSlotBusy()
{
	const timeout=(performance.now()>2000) ? 500 : 1;//Page chargée il y a moins de 3sec?
	if(typeof timeoutTimeSlotBusy!="undefined")  {clearTimeout(timeoutTimeSlotBusy);}//Non cumul de Timeout
	timeoutTimeSlotBusy=setTimeout(function(){
		if($(".dateBegin").notEmpty()  &&  $(".dateEnd").notEmpty()  &&  $(".dateBegin").val()==$(".dateEnd").val()){
			//Init l'url, avec le créneau horaire et les agendas concernés
			let dateTimeBegin=encodeURIComponent($(".dateBegin").val()+" "+$(".timeBegin").val());
			let dateTimeEnd  =encodeURIComponent($(".dateEnd").val()+" "+$(".timeEnd").val());
			var ajaxUrl="?ctrl=calendar&action=timeSlotBusy&dateTimeBegin="+dateTimeBegin+"&dateTimeEnd="+dateTimeEnd+"&_evtId=<?= $curObj->_id ?>";
			$(".vCalInput:checked").each(function(){  ajaxUrl+="&calendarIds[]="+this.value;  });
			//Lance le controle Ajax et renvoie les agendas où le créneau est occupé (mainTriggers() : Update les tooltips)
			$.ajax(ajaxUrl).done(function(txtResult){
				if(txtResult.length>0)	{$("#timeSlotBusy").fadeIn();  $(".timeSlotBusyContent").html(txtResult);  mainTriggers();}
				else					{$("#timeSlotBusy").fadeOut();}
			});
		}
	}, timeout);
}

////	Controle spécifique (cf. "VueObjMenuEdit.php")
function mainFormControl()
{
	return new Promise((resolve)=>{
		if($(".vCalInput:checked").isEmpty())															{resolve(false);  notify("<?= Txt::trad("CALENDAR_verifCalNb") ?>");}	//Aucune affectation aux agendas
		else if($("input[name='guest']").exist() && $("input[name='guest']").val().length<3)			{resolve(false);  notify("<?= Txt::trad("EDIT_guestNameNotif") ?>");}	//Préciser un nom ou pseudo
		else if($("input[name='guestMail']").exist() && $("input[name='guestMail']").isMail()==false)	{resolve(false);  notify("<?= Txt::trad("mailInvalid") ?>");}			//Mail invalide
		else																							{resolve(true);}
	});
}
</script>


<style>
/*GENERAL*/
#bodyLightbox						{max-width:850px;}
.vEvtOptionInline					{display:inline-block; margin:10px 20px 10px 0px; line-height:35px;}/*line-height: #allDayCheckbox + #visioUrlAdd*/
.beginEndLabel						{display:none;}
#beginEndSeparator					{display:inline-block;}
#guestMenu							{text-align:center;}
input[name='guestMail']				{margin-left:20px;}
input[name='location']				{width:400px;}
<?= Ctrl::$curUser->isGuest() ? '.vEvtGuestHide {display:none;}' : null ?>

/*PÉRIODICITÉ*/
#periodFieldset					 	{display:none; margin-block:15px;}/*surcharge le margin-top du fieldset*/
#periodFieldset>div					{margin-bottom:20px; line-height:30px;}/*blocks principaux*/
.vPeriodCheckboxDays				{display:inline-block; width:13%;}
.vPeriodCheckboxMonths				{display:inline-block; width:15%;}
.vPeriodDateExceptionsInput			{display:inline-block; margin:0px 10px;}
.vPeriodDateExceptionsInput:has(input[value=''])	{display:none;}

/*VISIOCONFERENCE*/
#visioUrlInput						{width:250px; font-size:0.9rem;}
<?= empty($curObj->visioUrl)?'#visioInputs':'#visioUrlAdd' ?>	{display:none;}/*masque l'input de la visio OU "Ajouter une visio"*/

/*AFFECTATION AUX AGENDAS*/
#calAffectationsOverflow								{max-height:300px; overflow-y:auto;}
.vCalAffectation										{display:inline-table!important;}/*surcharge*/
.vCalAffectation>div									{display:table-cell;}
.vCalAffectation label									{display:block; line-height:22px;}
.vCalAffectation .vCalInput								{display:none;}
.vCalAffectation .vCalProposeOption						{width:30px; text-align:center; cursor:help;}/*curseur "?"*/
.vCalAffectation .vCalProposeOptionQuestion				{width:20px; height:20px; background-image: url('app/img/dot.png') center no-repeat;}
.vCalAffectation:not(.optionSelect) .vCalProposeOption	{display:none;}/*masque si l'agenda n'est pas sélectionné*/

/*AFFICHAGE DE "timeSlotBusy"*/
#timeSlotBusy						{display:none;}
#timeSlotBusy table:first-child		{margin-top:10px;}
#timeSlotBusy table td:first-child	{min-width:150px; vertical-align:top; padding-right:20px;}

/*** RESPONSIVE SMARTPHONE*/
@media screen and (max-width:499px){
	.vEvtOptionInline								{display:block; margin:20px 0px;}
	#beginEndSeparator								{display:block; visibility:hidden; line-height:15px;}
	.beginEndLabel									{display:inline-block; width:60px; line-height:35px;}
	.vPeriodCheckboxDays, .vPeriodCheckboxMonths	{width:33%!important;}
	#timeSeparator									{display:none;}
	#calAffectations legend							{padding-inline:10px;}
	.vCalAffectation.option							{width:100%; margin-block}/*surcharge*/
	.vCalAffectation .vCalProposeOption				{display:none;}
	#timeSlotBusy table td:first-child				{min-width:100px; vertical-align:top; padding-right:20px;}
}
</style>


<form action="index.php" method="post" id="mainForm" enctype="multipart/form-data">
	<!--TITRE MOBILE-->
	<?= $curObj->titleMobile("CALENDAR_addEvt") ?>

	<!--TITRE / DESCRIPTION-->
	<input type="text" name="title" value="<?= $curObj->title ?>" class="inputTitleName" placeholder="<?= Txt::trad("title") ?>">
	<?= $curObj->descriptionEditor() ?>

	<!--DATE DEBUT & FIN-->
	<div class="vEvtOptionInline">
		<span class="beginEndLabel"><?= Txt::trad("begin") ?></span>
		<input type="text" name="dateBegin" class="dateBegin" value="<?= Txt::formatDate($curObj->dateBegin,"dbDatetime","inputDate") ?>" <?= Txt::tooltip("begin") ?>>
		<input type="time" name="timeBegin" class="timeBegin" value="<?= Txt::formatDate($curObj->dateBegin,"dbDatetime","inputHM") ?>" <?= Txt::tooltip("begin") ?>>
		<span id="beginEndSeparator"><img src="app/img/arrowRightSmall.png"></span>
		<span class="beginEndLabel"><?= Txt::trad("end") ?></span>
		<input type="text" name="dateEnd" class="dateEnd" value="<?= Txt::formatDate($curObj->dateEnd,"dbDatetime","inputDate") ?>" <?= Txt::tooltip("end") ?>>
		<input type="time" name="timeEnd" class="timeEnd" value="<?= Txt::formatDate($curObj->dateEnd,"dbDatetime","inputHM") ?>" <?= Txt::tooltip("end") ?>>
	</div>

	<!--TOUTE LA JOURNEE-->
	<div class="vEvtOptionInline">
		<input type="checkbox" name="allDay" id="allDayCheckbox" value="1" <?= $curObj->allDay==1?'checked':null ?> >
		<label for="allDayCheckbox"><?= Txt::trad("CALENDAR_allDay") ?> <img src="app/img/calendar/allDay.png"></label>
	</div>

	<!--PERIODICITE : SELECTION DU TYPE-->
	<div class="vEvtOptionInline vEvtGuestHide">
		<select name="periodType">
			<option value=""><?= Txt::trad("CALENDAR_noRepeat") ?></option>
			<option value="weekDay"><?= Txt::trad("CALENDAR_period_weekDay") ?></option>
			<option value="month"><?= Txt::trad("CALENDAR_period_month") ?></option>
			<option value="year"><?= Txt::trad("CALENDAR_period_year") ?></option>
		</select>
	</div>

	<!--PERIODICITE : DETAILS-->
	<fieldset id="periodFieldset" class="vEvtGuestHide">
		<!--DETAIL DES PERIODICITES MOIS/ANNEE (ex: "le 22 du mois")-->
		<legend><img src="app/img/calendar/period.png"> <span id="periodLegend"></span></legend>
		<!--PERIODICITE : JOURS DE LA SEMAINE-->
		<div id="periodType_weekDay">
			<?php for($cpt=1; $cpt<=7; $cpt++){ ?>
			<span class="vPeriodCheckboxDays">
				<input type="checkbox" name="periodValues_weekDay[]" value="<?= $cpt ?>" id="periodValues_weekDay<?= $cpt ?>" <?= ($curObj->periodType=="weekDay" && in_array($cpt,Txt::txt2tab($curObj->periodValues))) ? "checked" : null ?> >
				<label for="periodValues_weekDay<?= $cpt ?>"><?= Txt::trad("day_".$cpt) ?></label>
			</span>
			<?php } ?>
		</div>
		<!--PERIODICITE : MOIS DE L'ANNEE-->
		<div id="periodType_month">
			<?php for($cpt=1; $cpt<=12; $cpt++){ ?>
			<span class="vPeriodCheckboxMonths">
				<input type="checkbox" name="periodValues_month[]" value="<?= $cpt ?>" id="periodValues_month<?= $cpt ?>" <?= ($curObj->periodType=="month" && in_array($cpt,Txt::txt2tab($curObj->periodValues))) ? "checked" : null ?> >
				<label for="periodValues_month<?= $cpt ?>"><?= Txt::trad("month_".$cpt) ?></label>
			</span>
			<?php } ?>
		</div>
		<!--PERIODICITE : DATES D'EXCEPTION-->
		<div id="periodDateExceptions">
			<label id="periodDateExceptionsAdd" <?= Txt::tooltip("add") ?>><img src="app/img/calendar/periodDateExceptions.png"> <?= Txt::trad("CALENDAR_periodDateExceptions") ?> <img src="app/img/plusSmall.png"></label>
			<?php for($cpt=0; $cpt<20; $cpt++){ ?>
			<span class="vPeriodDateExceptionsInput">
				<img src="app/img/arrowRight.png">
				<input type="text" name="periodDateExceptions[]" value="<?= isset($periodDateExceptions[$cpt]) ? $periodDateExceptions[$cpt] : null ?>" class="dateInput">
				<img src="app/img/delete.png" class="vPeriodDateExceptionsDelete link" <?= Txt::tooltip("delete") ?> >
			</span>
			<?php } ?>
		</div>
		<!--PERIODICITE : FIN-->
		<div id="periodDateEnd">
			<img src="app/img/dateEnd.png"> <?= Txt::trad("CALENDAR_periodDateEnd") ?> <img src="app/img/arrowRight.png">
			<input type="text" name="periodDateEnd" class="dateInput" value="<?= Txt::formatDate($curObj->periodDateEnd,"dbDate","inputDate") ?>">
		</div>
	</fieldset>

	<!--SEPARATEUR DE FIN DES PARAMETRE DE TEMPS-->
	<br id="timeSeparator">

	<!--CATEGORIE DE L'EVT-->
	<div class="vEvtOptionInline">
		<?= MdlCalendarCategory::selectInput($curObj->_idCat) ?>
	</div>

	<!--IMPORTANT-->
	<div class="vEvtOptionInline vEvtGuestHide">
		<input type="checkbox" name="important" id="importantCheckbox" value="1" <?= $curObj->important==1?'checked':null ?> >
		<label for="importantCheckbox"><?= Txt::trad("CALENDAR_importantEvent") ?> <img src="app/img/important.png"></label>
	</div>

	<!--VISIBILITE-->
	<div class="vEvtOptionInline vEvtGuestHide">
		<select name="contentVisible" <?= Txt::tooltip("CALENDAR_visibilityTooltip") ?>>
			<option value="public"><?= Txt::trad("CALENDAR_visibilityPublic") ?></option>
			<option value="public_cache"><?= Txt::trad("CALENDAR_visibilityPublicHide") ?></option>
			<option value="prive"><?= Txt::trad("CALENDAR_visibilityPrivate") ?></option>
		</select>
	</div>

	<!--ADRESSE-->
	<div class="vEvtOptionInline">
		<input type="text" name="location" value="<?= $curObj->location ?>" id="locationInput" placeholder="<?= Txt::trad("adress") ?>" <?= Txt::tooltip("adress") ?> >
	</div>

	<!--VISIOCONFERENCE-->
	<?php if(Ctrl::$agora->visioEnabled()){ ?>
	<div class="vEvtOptionInline vEvtGuestHide">
		<span id="visioUrlAdd" class="link" <?= Txt::tooltip("VISIO_urlAddConfirm") ?>><img src="app/img/visioSmall.png"> <?= Txt::trad("VISIO_urlAdd") ?></span>
		<span id="visioInputs">
			<input type="text" name="visioUrl" value="<?= $curObj->visioUrl ?>" id="visioUrlInput" readonly>
			<img src="app/img/copy.png" id="visioUrlCopy" class="link" <?= Txt::tooltip("VISIO_urlCopy") ?>>
			<img src="app/img/delete.png" id="visioUrlDelete" class="link" <?= Txt::tooltip("VISIO_urlDelete") ?>>
		</span>
	</div>
	<?php } ?>

	<!--AFFECTATIONS AUX AGENDAS-->
	<fieldset id="calAffectations" class="vEvtGuestHide">
		<legend><?= Txt::trad("CALENDAR_calendarAffectations") ?></legend>
		<div id="calAffectationsOverflow">
			<?php
			////	AGENDAS DE RESSOURCES & AGENDAS PERSONNELS
			foreach($affectationCalendars as $tmpCal){
				$tmpCalAttr=null;
				if($tmpCal->_id==Req::param("_idCal") || $curObj->isAffectedCalendar($tmpCal))	{$tmpCalAttr.=' checked ';}
				if($tmpCal->isPersonal())														{$tmpCalAttr.=' data-iduser="'.$tmpCal->_idUser.'" ';}//cf selectUsersGroups()
			?>
				<div class="vCalAffectation option userInputDiv">
					<!--AFFECTATION A L'AGENDA-->
					<div <?= Txt::tooltip($tmpCal->tooltip) ?>>
						<input type="checkbox" name="affectationCalendars[]" value="<?= $tmpCal->_id ?>" class="vCalInput" id="calInput<?= $tmpCal->typeId ?>" <?= $tmpCalAttr ?> >
						<label for="calInput<?= $tmpCal->typeId ?>" ><?= ($tmpCal->isRessource()?'<img src="app/img/calendar/typeRessource.png">':null)." ".$tmpCal->title ?></label>
					</div>
					<!--OPTION DE PROPOSITION-->
					<?php if($tmpCal->proposeOption==true){ ?>
						<div <?= Txt::tooltip("CALENDAR_proposeEvtTooltipBis") ?> class="vCalProposeOption">	
							<input type="checkbox" name="proposeOptionCalendars[]" value="<?= $tmpCal->_id ?>" <?= $curObj->isAffectedCalendar($tmpCal,false)?'checked':null ?>>
							<span class="vCalProposeOptionQuestion"></span>
						</div>
					<?php } ?>
				</div>
			<?php } ?>
		</div>
		<!--SELECTION D'USERS & GROUPES-->
		<?= MdlUser::selectUsersGroups(Ctrl::$curSpace, "#calAffectations .vCalInput") ?>
		<!--CRENEAU HORAIRE DEJA OCCUPE DANS LES AGENDAS SELECTIONNES-->
		<div id="timeSlotBusy" class="sAccessRead">
			<hr>
			<?= Txt::trad("CALENDAR_busyTimeSlot") ?>
			<div class="timeSlotBusyContent"></div>
		</div>
	</fieldset>

	<!--MENU D'IDENTIFICATION DES GUESTS-->
	<?php if(Ctrl::$curUser->isGuest()){ ?>
		<fieldset id="guestMenu">
			<input type="text" name="guest" placeholder="<?= Txt::trad("EDIT_guestName") ?>">
			<input type="text" name="guestMail" placeholder="<?= Txt::trad("EDIT_guestMail") ?>" <?= Txt::tooltip("EDIT_guestMailTooltip") ?> >
			<hr><?= CtrlMisc::menuCaptcha() ?>
		</fieldset>
	<?php } ?>

	<!--MENU D'EDITION & VALIDATION DU FORM-->
	<?= $curObj->editMenuSubmit() ?>
</form>