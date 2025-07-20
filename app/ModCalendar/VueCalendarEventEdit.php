<script>
////	INIT
ready(function(){
	////	INIT LE FORMULAIRE (périodicité / contentVisible / important)
	$("select[name='periodType']").val("<?= $curObj->periodType ?>");
	$("select[name='contentVisible']").val("<?= $curObj->contentVisible ?>");
	$("select[name='important']").val("<?= (int)$curObj->important ?>");
	displayPeriodType();

	////	CHANGE DE DATE/HEURE : CONTROLE LES CRÉNEAUX HORAIRES OCCUPÉS
	$("[name='dateBegin'],[name='timeBegin'],[name='dateEnd'],[name='timeEnd']").on("change",function(){ timeSlotBusy(); });

	////	PÉRIODICITÉ : AFFICHAGE & DETAILS
	$("[name='periodType'],[name='dateBegin']").on("change",function(){ displayPeriodType(); });

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
			$("#visioOptions").show();										//Affiche l'input / copy / delete
			$("#visioUrlInput").val("<?= Ctrl::$agora->visioUrl() ?>");		//Spécifie l'URL d'une visio avec un identifiant aléatoire
			$(this).hide();													//Masque "Ajouter une visio"
		}
	});

	////	VISIO : COPIE L'URL DANS LE PRESSE PAPIER
	$("#visioUrlCopy").on("click", async function(){
		if(await confirmAlt("<?= Txt::trad("VISIO_urlCopy") ?>")){
			let visioUrlVal=$("#visioUrlInput").val();							//Récupère l'Url
			navigator.clipboard.writeText(visioUrlVal).then(()=>{				//Copie l'url dans le clipboard (Presse-papiers)
				notify(visioUrlVal+" <br> <?= Txt::trad("copyUrlNotif") ?>");	//Notify "L'url a bien été copiée"
			});
		}
	});

	////	VISIO : SUPPRIME L'URL
	$("#visioUrlDelete").on("click", async function(){
		if(await confirmAlt("<?= Txt::trad("VISIO_urlDelete") ?>")){
			$("#visioUrlInput").val("");	//Réinit l'url de la visio
			$("#visioOptions").hide();		//Affiche l'input / copy / delete
			$("#visioUrlAdd").show();		//Affiche le label "Ajouter une visio"
		}
	});

	////	VISIO : LANCE LA VISIO DEPUIS L'UNPUT
	$("#visioUrlInput").on("click",function(){
		launchVisio(this.value);
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
	let dateBeginValue=$("[name='dateBegin']").val();
	if($("[name='periodType']").val()=="weekDay")		{$("#periodLegend").html("<?= Txt::trad("CALENDAR_period_weekDay") ?>");}																//"Toutes les semaines"
	else if($("[name='periodType']").val()=="month")	{$("#periodLegend").html(String("<?= Txt::trad("CALENDAR_period_monthDetail") ?>").replace("--DATE--",dateBeginValue.substr(0,2)));}	//"Tous les 15 du mois"
	else if($("[name='periodType']").val()=="year")		{$("#periodLegend").html(String("<?= Txt::trad("CALENDAR_period_yearDetail") ?>").replace("--DATE--",dateBeginValue.substr(0,5)));}		//"Tous les ans, le 15/10"
	//Pré-check si besoin tous les mois
	if($("[name='periodType']").val()=="month" && $("[name*='periodValues_month']:checked").length==0)  {$("input[name*='periodValues_month']").prop("checked","true");}
}

////	CRÉNEAUX HORAIRES OCCUPÉS SUR LES AGENDAS SÉLECTIONNÉS
function timeSlotBusy()
{
	if(typeof timeoutTimeSlotBusy!="undefined")  {clearTimeout(timeoutTimeSlotBusy);}	//Un seul timeout
	timeoutTimeSlotBusy=setTimeout(function(){
		if($("[name='dateBegin']").notEmpty()  &&  $("[name='dateEnd']").notEmpty()  &&  $("[name='dateBegin']").val()==$("[name='dateEnd']").val()){
			//Init l'url, avec le créneau horaire et les agendas concernés
			var ajaxUrl="?ctrl=calendar&action=timeSlotBusy"+
						"&dateTimeBegin="+encodeURIComponent($("[name='dateBegin']").val()+" "+$("[name='timeBegin']").val())+
						"&dateTimeEnd="+encodeURIComponent($("[name='dateEnd']").val()+" "+$("[name='timeEnd']").val())+
						"&_evtId=<?= $curObj->_id ?>";
			$(".vCalInput:checked").each(function(){ ajaxUrl+="&calendarIds[]="+this.value; });
			//Lance le controle Ajax et renvoie les agendas où le créneau est occupé
			$.ajax(ajaxUrl).done(function(txtResult){
				if(txtResult.length>0)	{$("#timeSlotBusy").fadeIn();  $(".timeSlotBusyContent").html(txtResult);  tooltipDisplay();}
				else					{$("#timeSlotBusy").fadeOut();}
			});
		}
	}, 1000);//Cf sélect. plusieurs agendas d'affilé
}

////	Controle spécifique (cf. "VueObjMenuEdit.php")
function objectFormControl()
{
	return new Promise((resolve)=>{
		if($(".vCalInput:checked").isEmpty())															{notify("<?= Txt::trad("CALENDAR_verifCalNb") ?>");  resolve(false);}	//Aucune affectation aux agendas
		else if($("input[name='guest']").exist() && $("input[name='guest']").val().length<3)			{notify("<?= Txt::trad("EDIT_guestNameNotif") ?>");  resolve(false);}	//"Merci de préciser un nom ou un pseudo"
		else if($("input[name='guestMail']").exist() && $("input[name='guestMail']").isMail()==false)	{notify("<?= Txt::trad("mailInvalid") ?>");			 resolve(false);}	//Mail invalide
		else																							{resolve(true);}
	});
}
</script>


<style>
/*GENERAL*/
#bodyLightbox						{max-width:850px;}
legend			 					{font-size:1.05em; text-align: center!important;}
.vEvtOptionInline					{display:inline-block; margin:25px 25px 0px 0px;}
.beginEndLabel						{display:none}
#beginEndSeparator					{margin:0px 5px;}
#guestMenu							{text-align:center;}
input[name='guestMail']				{margin-left:20px;}
<?= Ctrl::$curUser->isGuest() ? '.vEvtGuestHide {display:none;}' : null ?>

/*PÉRIODICITÉ*/
#periodFieldset					 	{display:none; margin:20px 0px;}
#periodFieldset>div					{margin-bottom:20px; line-height:30px;}/*blocks principaux*/
.vPeriodCheckboxDays				{display:inline-block; width:14%;}
.vPeriodCheckboxMonths				{display:inline-block; width:16%;}
.vPeriodDateExceptionsInput			{display:inline-block; margin:0px 10px;}
.vPeriodDateExceptionsInput:has(input[value=''])	{display:none;}

/*VISIOCONFERENCE*/
#visioUrlAdd						{line-height:35px;}
#visioUrlInput						{width:250px; font-size:0.95em;}
<?= empty($curObj->visioUrl) ? "#visioOptions{display:none;}" : "#visioUrlAdd{display:none;}" ?>/*masque "Ajouter une visio"  ||  masque l'input de la visio*/

/*AFFECTATION AUX AGENDAS*/
#calAffectationsOverflow			{max-height:500px; overflow-y:auto;}
.vCalAffectation					{display:inline-block; width:32%; margin:2px;}
.vCalAffectation .vCalInput			{display:none;}
.vCalAffectation label				{display:inline-block; margin:3px; width:80%;}/*label rattaché à ".vCalInput"*/
.vCalAffectation .vCalProposeOption						{float:right; margin:3px;}/*Option de proposition d'evt*/
.vCalAffectation:not(.optionSelect) .vCalProposeOption	{display:none;}/*masque l'input de proposition si l'agenda n'est pas sélectionné*/

/*AFFICHAGE DE "timeSlotBusy"*/
#timeSlotBusy						{display:none;}
#timeSlotBusy table:first-child		{margin-top:10px;}
#timeSlotBusy table td:first-child	{min-width:130px; vertical-align:top; padding-right:20px;}

/*RESPONSIVE SMARTPHONE*/
@media screen and (max-width:490px){
	#beginEndSeparator								{visibility:hidden; display:block;}
	.beginEndLabel									{display:inline-block; width:50px;}
	.vPeriodCheckboxDays, .vPeriodCheckboxMonths	{width:33%!important;}
	.vCalAffectation								{width:100%; margin:3px 0px;}
	.vCalAffectation label							{padding:5px;}
}
</style>


<form action="index.php" method="post" id="mainForm" enctype="multipart/form-data">
	<!--TITRE MOBILE-->
	<?= $curObj->titleMobile("CALENDAR_addEvt") ?>

	<!--TITRE / DESCRIPTION-->
	<input type="text" name="title" value="<?= $curObj->title ?>" class="inputTitleName" placeholder="<?= Txt::trad("title") ?>">
	<?= $curObj->descriptionEditor() ?>

	<!--DATE DEBUT & FIN-->
	<div class="vEvtOptionInline" id="eventDates">
		<span class="beginEndLabel"><?= Txt::trad("begin") ?></span>
		<input type="text" name="dateBegin" class="dateBegin" value="<?= Txt::formatDate($curObj->dateBegin,"dbDatetime","inputDate") ?>" <?= Txt::tooltip("begin") ?>>
		<input type="time" name="timeBegin" class="timeBegin" value="<?= Txt::formatDate($curObj->dateBegin,"dbDatetime","inputHM") ?>" <?= Txt::tooltip("begin") ?>>
		<span id="beginEndSeparator"><img src="app/img/arrowRightSmall.png"></span>
		<span class="beginEndLabel"><?= Txt::trad("end") ?></span>
		<input type="text" name="dateEnd" class="dateEnd" value="<?= Txt::formatDate($curObj->dateEnd,"dbDatetime","inputDate") ?>" <?= Txt::tooltip("end") ?>>
		<input type="time" name="timeEnd" class="timeEnd" value="<?= Txt::formatDate($curObj->dateEnd,"dbDatetime","inputHM") ?>" <?= Txt::tooltip("end") ?>>
	</div>

	<!--CATEGORIE DE L'EVT-->
	<div class="vEvtOptionInline">
		<?= MdlCalendarCategory::selectInput($curObj->_idCat) ?>
	</div>

	<!--PERIODICITE : SELECTION DU TYPE-->
	<div class="vEvtOptionInline vEvtGuestHide">
		<select name="periodType">
			<option value=""><?= Txt::trad("CALENDAR_noPeriodicity") ?></option>
			<option value="weekDay"><?= Txt::trad("CALENDAR_period_weekDay") ?></option>
			<option value="month"><?= Txt::trad("CALENDAR_period_month") ?></option>
			<option value="year"><?= Txt::trad("CALENDAR_period_year") ?></option>
		</select>
	</div>

	<!--PERIODICITE : DETAILS-->
	<fieldset class="vEvtGuestHide" id="periodFieldset">
		<!--DETAIL DES PERIODICITES MOIS/ANNEE (ex: "le 22 du mois")-->
		<legend>
			<img src="app/img/calendar/period.png"> <span id="periodLegend"></span>
		</legend>
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
				<img src="app/img/delete.png" class="vPeriodDateExceptionsDelete sLink" <?= Txt::tooltip("delete") ?> >
			</span>
			<?php } ?>
		</div>
		<!--PERIODICITE : FIN-->
		<div id="periodDateEnd">
			<img src="app/img/dateEnd.png"> <?= Txt::trad("CALENDAR_periodDateEnd") ?> <img src="app/img/arrowRight.png">
			<input type="text" name="periodDateEnd" class="dateInput" value="<?= Txt::formatDate($curObj->periodDateEnd,"dbDate","inputDate") ?>">
		</div>
	</fieldset>

	<!--IMPORTANT-->
	<div class="vEvtOptionInline vEvtGuestHide">
		<select name="important">
			<option value="0"><?= Txt::trad("CALENDAR_importanceNormal") ?></option>
			<option value="1" data-color="#900"><?= Txt::trad("CALENDAR_importanceHight") ?></option>
		</select>
	</div>

	<!--VISIBILITE-->
	<div class="vEvtOptionInline vEvtGuestHide">
		<select name="contentVisible" <?= Txt::tooltip("CALENDAR_visibilityTooltip") ?>>
			<option value="public"><?= Txt::trad("CALENDAR_visibilityPublic") ?></option>
			<option value="public_cache"><?= Txt::trad("CALENDAR_visibilityPublicHide") ?></option>
			<option value="prive"><?= Txt::trad("CALENDAR_visibilityPrivate") ?></option>
		</select>
	</div>

	<!--VISIOCONFERENCE-->
	<?php if(Ctrl::$agora->visioEnabled()){ ?>
	<div class="vEvtOptionInline vEvtGuestHide">
		<span id="visioUrlAdd" class="sLink" <?= Txt::tooltip("VISIO_urlAddConfirm") ?>><img src="app/img/visioSmall.png"> <?= Txt::trad("VISIO_urlAdd") ?></span>
		<span id="visioOptions">
			<input type="text" name="visioUrl" value="<?= $curObj->visioUrl ?>" id="visioUrlInput" class="sLink" <?= Txt::tooltip("VISIO_launchFromEvent") ?> readonly>
			<img src="app/img/copy.png" id="visioUrlCopy" class="sLink" <?= Txt::tooltip("VISIO_urlCopy") ?>>
			<img src="app/img/delete.png" id="visioUrlDelete" class="sLink" <?= Txt::tooltip("VISIO_urlDelete") ?>>
		</span>
	</div>
	<?php } ?>

	<!--AFFECTATIONS AUX AGENDAS-->
	<fieldset class="vEvtGuestHide" id="calAffectations">
		<legend><?= Txt::trad("CALENDAR_calendarAffectations") ?></legend>
		<div id="calAffectationsOverflow">
			<!--AGENDAS DE RESSOURCES & AGENDAS PERSONNELS-->
			<?php foreach($affectationCalendars as $tmpCal){ ?>
				<div class="vCalAffectation option">
					<!--AFFECTATION A L'AGENDA-->
					<input type="checkbox" name="affectationCalendars[]" value="<?= $tmpCal->_id ?>" id="box<?= $tmpCal->_typeId ?>" class="vCalInput" <?= $tmpCal->inputAttr ?> >
					<label for="box<?= $tmpCal->_typeId ?>" <?= Txt::tooltip($tmpCal->tooltip) ?> ><?= ($tmpCal->type=="ressource"?'<img src="app/img/calendar/typeRessource.png">':null)." ".$tmpCal->title ?></label>
					<!--OPTION DE PROPOSITION-->
					<?php if($tmpCal->proposeOption==true){ ?>
					<input type="checkbox" name="proposeOptionCalendars[]" value="<?= $tmpCal->_id ?>" <?= $curObj->isAffectedCalendar($tmpCal,false)?'checked':null ?> class="vCalProposeOption" <?= Txt::tooltip("CALENDAR_proposeOptionTooltip") ?>>
					<?php } ?>
				</div>
			<?php } ?>
			<!--SWITCH LA SELECTION D'UN GROUPE D'USERS-->
			<?php if(count($affectationCalendars)>2){ ?>
				<hr>
				<div class="vCalAffectation option" onclick="$('.vCalInput').trigger('click')">
					<label><img src="app/img/checkSwitch.png"> <?= Txt::trad("selectSwitch") ?></label>
				</div>
				<?php foreach($curSpaceUserGroups as $tmpGroup){ ?>
				<div class="vCalAffectation option" <?=Txt::tooltip(Txt::trad("selectUnselect")." :<br>".$tmpGroup->usersLabel) ?>>
					<input type="checkbox" name="calUsersGroup[]" value="<?= implode(",",$tmpGroup->userIds) ?>" id="calUsersGroup<?= $tmpGroup->_typeId ?>" onchange="userGroupSelect(this,'#calAffectationsOverflow')">
					<label for="calUsersGroup<?= $tmpGroup->_typeId ?>"><img src="app/img/user/accessGroup.png"> <?= $tmpGroup->title ?></label>
				</div>
				<?php } ?>
			<?php } ?>
		</div>

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