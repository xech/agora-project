<!--DRAG/DROP SUR MOBILE-->
<script src="app/js/interact.min.js"></script>


<script>
/************************************************************************************************************
 *	AFFICHAGE DES AGENDAS (lancé via  "app.js")
*************************************************************************************************************/
function moduleDisplay()
{
	$(".vSynthDay").outerWidth(  ($("#synthHeader").width()-$(".vSynthLabel").width()) / $("#synthHeader .vSynthDay").length  );	//Width des .vSynthDay (Synthese des agendas)
	$(".vCalMain").outerHeight(  (windowTopHeight - $("#pageContent").offset().top - <?= $footerHeight ?>)  );						//Height des .vCalMain
	$(".vCalVue").outerHeight(   $(".vCalMain").innerHeight() - $(".vCalHeader").outerHeight()  );									//Height des .vCalVue en fonction de .vCalMain
	$(".vEvtBlock").each(function(){									//Background de chaque evt :
		let bgColor=this.getAttribute("data-bgcolor");					//- Récupère le bgColor
		$(this).css("background-color",bgColor);						//- Applique le bgColor
		$(this).find(".vEvtLabel").css("color",contrastColor(bgColor));	//- Couleur de texte en contraste avec bgColor
	});
	if(typeof calendarDisplayTimeout!="undefined")  {clearTimeout(calendarDisplayTimeout);}		//Non cumul de Timeout
	calendarDisplayTimeout=setTimeout(function(){												//Timeout le tps de calculer le width de .vWeekScroller
		calendarDisplay();																		//Dimensions des agendas Month/Week
		evtDraggable();																			//Draggable des evt : tjs après calendarDisplay()
		$(".vCalMain").css("visibility","visible");												//Affiche les agendas après calendarDisplay()
	},10);
}

/************************************************************************************************************
 *	DRAG & DROP D'EVT : INIT LES EVT DRAGGABLE ET LES DROPZONES
*************************************************************************************************************/
function evtDraggable()
{
	/************************************************************************************************************
	 *	DESACTIVE LE CLICK DURANT LE DRAG & DROP : POUR PAS AFFICHER LA VUE D'UN EVT
	*************************************************************************************************************/
	document.addEventListener("click", (event)=>{
		if($(".vEvtDrag").isVisible()){
			event.stopPropagation();
			event.preventDefault();
		}
	}, true);//true pour intercepter le clic avant qu'il n'atteigne les éléments enfants

	/************************************************************************************************************
	 *	REINITIALISE LES INSTANCES D'INTERACT : cf page resize > moduleDisplay() > evtDraggable()
	*************************************************************************************************************/
	interact(".vEvtBlock[data-isdraggable='true']").unset();
	interact(".vCellDay").unset();
	interact(".vWeekCell").unset();

	/************************************************************************************************************
	 *	DRAG & DROP D'EVT  >  .vMonthTable / .vWeekHeaderAllday
	*************************************************************************************************************/
	interact(".vCellDay .vEvtBlock[data-isdraggable='true']").draggable({
		modifiers:[
			interact.modifiers.restrictRect({restriction:".vMonthTable, .vWeekHeaderAllday"})//Délimite la dropzone
		],
		listeners:{
			start(event){
				event.target.classList.add("vEvtDrag"); //Style du .vEvtBlock
				evtCellStart=event.target.parentNode;	//.vCellDay de départ
			},
			end(event){
				setTimeout(function(){  event.target.classList.remove("vEvtDrag");  },200);//Timeout : cf "stopPropagation()"
    		}
		}
	});
	interact(".vCellDay").dropzone({
		accept:".vCellDay .vEvtBlock",
		ondragenter(event){
			const evtDrag=event.relatedTarget;
			const cellDrop=event.target;
			$(".tippy-box").hide();														//Masque les tooltips
			if($(cellDrop).find(evtDrag).exist()==false)  {cellDrop.append(evtDrag);}	//Autre cellDrop que celle où se trouve l'evt : Déplace en fin de liste
		},
		ondrop(event){
			const evtDrag=event.relatedTarget;
			const cellDrop=event.target;
			newDateString=cellDrop.getAttribute("data-ymd")+"T"+evtDrag.getAttribute("data-hm")+":00";	//format ISO (ex: "2036-04-02T15:30:00")
			newTimeBegin=new Date(newDateString).getTime() / 1000;										//Timestamp du nouveau begin
			evtDropConfirm(evtDrag, cellDrop, newTimeBegin);											//confirme le changement d'heure
		}
	});

	/************************************************************************************************************
	 *	DRAG & DROP D'EVT  >  .vWeekScroller
	*************************************************************************************************************/
	interact(".vWeekScroller .vEvtBlock[data-isdraggable='true']").draggable({
		hold:isMobile() ? 100 : 0,//Latence sur mobile pour pas deplacer un evt durant le scroll de page
		modifiers:[
			interact.modifiers.restrictRect({restriction:".vCalVue"}),													//Délimite la dropzone
			interact.modifiers.snap({																					//Grille de Snap/Accroche :
				offset:"parent",																						//basé sur le parent
				relativePoints:[{ x:$(".vWeekHourLabel").width(), y:0 }],												//Décalage par rapport à la colonne .vWeekHourLabel
				targets:[interact.snappers.grid({ x:$(".vWeekCell").outerWidth(), y:$(".vWeekCell").outerHeight() })]	//Grid à la dimension des .vWeekCell
			})
		],
		listeners:{
			start(event){
				weekCellTarget=null;										//Init la vWeekCell ciblée
				event.target.classList.add("vEvtDrag");						//Style du .vEvtBlock
				evtStartX=parseFloat(event.target.style.left);				//Position X de départ
				evtStartY=parseFloat(event.target.style.top);				//Position Y de départ
				evtStartHM=$(event.target).find(".vEvtLabelHM").html();		//Date de départ
			},
			end(event){
				setTimeout(function(){  event.target.classList.remove("vEvtDrag");  },200);//Timeout : cf "stopPropagation()"
    		},
			move(event){
				$(".tippy-box").hide();																			//Masque les tooltips
				const weekCellWidthHalf=($(".vWeekCell").outerWidth() / 2);										//Width des evt splités
				const mouseMoveX=(Math.abs(event.dx) > weekCellWidthHalf) ? event.dx : 0;						//Position X relative à la souris, corrigé pour les evt splités
				const evtX=(parseFloat(event.target.style.left) || 0) + mouseMoveX;								//Position X de l'evt
				const evtY=(parseFloat(event.target.style.top) || 0) + event.dy;								//Position Y de l'evt
				event.target.style.left=evtX+'px';																//Applique la position X
				event.target.style.top =evtY+'px';																//Applique la position Y
				$(event.target).parent().find(".vWeekCell").each(function(){									//Récupère la vWeekCell dont la position est la plus proche de l'evt :
					const diffY=(evtY - this.offsetTop);														//-Diff de position Y entre la .vWeekCell et l'evt 
					const diffX=(evtX - this.offsetLeft);														//-Diff de position X
					if(diffY <= 5  && diffX <= (weekCellWidthHalf+5))  {weekCellTarget=this;  return false;}	//-Cell trouvée avec une marge de 5px max (corrigé pour les evt splités)
				});
				if(weekCellTarget!=null){																		//weekCellTarget existe : affiche son label H:M dans .vEvtLabelHM
					$(event.target).find(".vEvtLabelHM").html('<span class="vEvtLabelHMdragged">'+weekCellTarget.getAttribute("data-hm")+'</span>');
				}
			}
		}
	});
	interact(".vWeekCell").dropzone({
		accept:".vWeekScroller .vEvtBlock",
		ondrop(event){
			//weekCellTarget existe : confirme le changement d'heure
			if(weekCellTarget!=null)  {evtDropConfirm(event.relatedTarget, weekCellTarget, weekCellTarget.getAttribute("data-timebegin"));}
		}
	});
}


/************************************************************************************************************
 *	DRAG & DROP D'EVT : CONFIRME ET ENREGISTRE LE NOUVEAU TIMEBEGIN VIA AJAX
*************************************************************************************************************/
function evtDropConfirm(evtDrag, cellDrop, newTimeBegin)
{
	////	Lance le confirm si l'evt a été déplacé : timebegin modifié
	if(newTimeBegin!=parseInt(evtDrag.getAttribute("data-timebegin"))){
		////	Old/New dateLabel
		isWeekScroller=cellDrop.hasAttribute("data-hm");
		oldDateLabel=evtDrag.getAttribute("data-datelabel");
		newDateLabel=cellDrop.getAttribute("data-datelabel");
		////	.vWeekScroller : Ajoute l'H:M
		if(isWeekScroller==true){
			oldDateLabel+=" "+evtDrag.getAttribute("data-hm");
			newDateLabel+=" "+cellDrop.getAttribute("data-hm");
		}
		////	Parametrage du confirm()
		const confirmParams={
			title:"<?= Txt::trad("CALENDAR_evtChangeTime") ?>",
			content:'<div class="vEvtConfirmOldDate"><img src="app/img/arrowRight.png"> '+oldDateLabel+'</div><img src="app/img/arrowRight.png"> '+newDateLabel,
			buttons:{
				////	Annulation
				reject:{
					text:"<?= Txt::trad("confirmCancel") ?>",
					btnClass:"btn-default",
					action:function(){
						if(isWeekScroller==true)	{$(evtDrag).animate({left:evtStartX,top:evtStartY},200);  $(evtDrag).find(".vEvtLabelHM").html(evtStartHM);}	//Replace l'evt à sa position, avec son LabelHM d'origine
						else						{evtCellStart.append(evtDrag);}																					//Replace l'evt dans sa cellule d'origine
					}
				},
				////	Confirmation acceptée
				accept:{
					text:"<?= Txt::trad("confirm") ?>",
					btnClass:"btn-green",
					action:function(){
						////	TypeId de l'evt + Url d'enregistrement du nouveau datetime
						let evtTypeId=evtDrag.getAttribute("data-typeid");
						let ajaxUrl="?ctrl=calendar&action=EvtChangeTime&newTimeBegin="+newTimeBegin+"&typeId="+evtTypeId;
						$.ajax({url:ajaxUrl,dataType:"json"}).done(function(result){
							if(result.changed){
								////	Parcourt chaque instance de l'evt pour chaque agenda affiché
								$(".vEvtBlock[data-typeid='"+evtTypeId+"']").each(function(){
									////	Update les attributs de l'evt (timeBegin, timeEnd..)
									for(var keyAttr in result.attributes)  {this.setAttribute(keyAttr, result.attributes[keyAttr]);}
									////	Update le tooltip et le label de la date
									$(this).find(".vEvtLabel").tooltipUpdate(result.tooltip);
									$(this).find(".vEvtLabelHM").html(result.evtLabelDate);
									////	Vue Month : Déplace l'evt dans les autres agendas
									if(isWeekScroller==false && this!=evtDrag)  {$(this).parents(".vCalVue").find(".vCellDay[data-ymd="+cellDrop.getAttribute("data-ymd")+"]").append(this);}
								});
								////	Notif  +  Reload l'affichage
								notify("<?= Txt::trad("CALENDAR_evtChangeTimeConfirmed") ?>","success");
								calendarDisplay();
							}
							else if(result.error)  {notify("Update error");}
						});
					}
				}
			}
		}
		$.confirm(Object.assign(confirmParamsDefault, confirmParams));
	}
}


ready(function(){
	/************************************************************************************************************
	 *	PROPOSITION D'EVT : PULSATE LE MENU
	 ************************************************************************************************************/
	if($(".evtProposition").exist() && $("#headerMobileModule").isVisible())
		{$("#headerMobileModule").pulsate();}

	/************************************************************************************************************
	 *	PROPOSITION D'EVT : CONFIRME/ANNULE UNE PROPOSITION
	 ************************************************************************************************************/
	$(".evtProposition").on("click",function(){
		let ajaxUrl="?ctrl=calendar&action=evtPropositionConfirm&typeId=calendar-"+this.getAttribute("data-idcal")+"&_idEvt="+this.getAttribute("data-idevt");
		let redirUrl="?ctrl=calendar&notify=";
		let confirmParams={
			title:"<?= Txt::trad("CALENDAR_evtProposition") ?> :",
			content:this.getAttribute("data-details"),//Date, auteur..
			buttons:{
				cancel:{text:"<?= Txt::trad("confirmCancel") ?>"},
				accept:{
					btnClass:"btn-green",
					text:"<?= Txt::trad("CALENDAR_evtProposeConfirm") ?>",
					action:function(){
						$.ajax(ajaxUrl+"&isConfirmed=true").done(function(){  redir(redirUrl+"CALENDAR_evtProposeConfirmed");  });
					}
				},
				reject:{
					btnClass:"btn-dark",
					text:"<?= Txt::trad("CALENDAR_evtProposeDecline") ?>",
					action:function(){
						$.ajax(ajaxUrl+"&isDeclined=true").done(function(){  redir(redirUrl+"CALENDAR_evtProposeDeclined");  });
					}
				},
			}
		}
		$.confirm(Object.assign(confirmParamsDefault, confirmParams));
	});

	/************************************************************************************************************
	 *	SUBMIT LA LISTE DES AGENDAS AFFICHES
	 ************************************************************************************************************/
	$("input[name='displayedCalendars[]']").on("change",function(){
		$("#readableCalendarsForm").submit();
	});

	/************************************************************************************************************
	 *	CALENDRIER DU MOIS (MENU DE GAUCHE, cf JQUERY UI)
	 ************************************************************************************************************/
	$("#datepickerCalendar").datepicker({
		firstDay:1,										//Début de semaine le lundi
		showOtherMonths:true,							//Affiche les jours des mois précédents/suivants
		defaultDate:"<?= date("Y-m-d",$curTime) ?>",	//Mois/Date affiché
		dateFormat:"yy-mm-dd",							//Utilisé par "dayYmd" ci-dessous
		onSelect:function(dayYmd){						//Sélectionne une date : redirection
			let dateObj=new Date(dayYmd);
			redir("?ctrl=calendar&curTime="+(dateObj.getTime()/1000));
		}
	});
	/////	DATEPICKER : SURLIGNE LES JOURS DE LA SEMAINE AFFICHÉE
	<?php foreach($periodDays as $tmpDay){ ?>
		$(".ui-datepicker .ui-state-default[data-date='<?= $tmpDay["dayOfMonth"] ?>']").removeClass("ui-state-active").addClass("ui-state-highlight");
	<?php } ?>

	/************************************************************************************************************
	 *	SWIPE GAUCHE/DROITE SUR MOBILE : PERIODE PRECEDENTE/SUIVANTE
	 ************************************************************************************************************/
	if(isTouchDevice()){
		swipeMenuShowOff=true;																//Désactive l'affichage du menu context via swipe
		document.addEventListener("touchstart",function(event){ buttonPrevNext=null; });	//Début de swipe
		document.addEventListener("touchmove",function(event){								//Direction du swipe :
			if(swipeAmplitudeY < 80){														//Swipe d'amplitude < 80px  (cf "menuContext()")
				if(swipeToRight > 100)		{buttonPrevNext=".vCalPrev";}					//Affiche la période précédente
				else if(swipeToLeft > 100)	{buttonPrevNext=".vCalNext";}					//Affiche la période suivante
			}
		});
		document.addEventListener("touchend",function(){																//Fin de swipe :
			if(buttonPrevNext!=null && $(".vEvtDrag").isVisible()==false && $("#menuMobileMain").isVisible()==false){	//buttonPrevNext spécifé + Pas de drag/drop en cours + Menu context masqué
				$(buttonPrevNext).addClass("vCalPrevNextPulsate").effect("pulsate",{times:2},800);						//Pulsate le bouton de la Prev/Next
				setTimeout(function(){  $(buttonPrevNext).trigger("click");  },400);									//Trigger "Click" pour afficher la période
			}
		});
	}
});
</script>


<style>
/*Page principale + Menu du module + Footer*/
#pageFooterHtml, #pageFooterIcon				{display:none;}
#pageFull										{margin-bottom:0px;}
#pageContent									{padding-bottom:10px;}/*Surcharge*/
.evtPropositionTitle, .evtProposition			{padding:7px;}
.evtProposition hr								{margin-block:7px;}
#readableCalendarsForm							{max-height:300px; overflow-y:auto;}
#readableCalendarsTitle							{display:table; width:100%;}
#readableCalendarsTitle>div						{display:table-cell;}
#readableCalendarsTitle #readableCalsAdmin		{text-align:right; filter:saturate(0);}
#readableCalendarsTitle:not(:hover) #readableCalsAdmin	{visibility:hidden;}
.vReadableCalendar								{display:table; width:100%;}/*idem .menuLine*/
.vReadableCalendar>div							{display:table-cell; vertical-align:middle;}
.vReadableCalendar label						{display:block;}/*toute la ligne est clickable*/
.vReadableCalendar>div:last-child				{width:17px;}/*contextMenu()*/
.vReadableCalendar input, .vReadableCalendar:not(:hover) .menuContextLaunch	{display:none;}
.ui-datepicker									{box-shadow:none; width:100%; border:0px!important;}/*surcharge*/
.ui-datepicker thead							{display:none;}										/*Header: ligne du label des jours*/
.ui-datepicker .ui-state-default				{padding-block:5px;}								/*surcharge*/

/*Synthese des agendas*/
#synthBlock.miscContent							{padding:2px 8px; margin-bottom:20px;}/*surcharge*/
#synthTable										{display:table; width:100%; max-width:100%;}
#synthHeader, .vSynthLine						{display:table-row;}
#synthHeader									{font-size:0.9em!important;}
#synthHeader .vSynthDayCurDay					{color:#c00;}
.vSynthLabel									{display:table-cell; width:150px; white-space:nowrap; padding-right:10px; vertical-align:middle;}
.vSynthDay										{display:table-cell; vertical-align:middle; text-align:center; height:22px;}
.vSynthDayEvts									{display:table; width:100%; height:100%;}
.vSynthDayEvt									{display:table-cell; border-left:transparent;}
.vSynthDayEvts:hover							{opacity:0.8;}
.vSynthDayEvtTooltip							{text-align:left;}
.vSynthDayEvtTooltip ul							{margin:0px; margin-top:5px; padding-left:10px;}
.vSynthDayCal									{background:#ddd; border:dotted 1px #eee;}
.vSynthDayCal.vSynthDayCalWE					{background:#ccc;}

/*Vue de chaque agenda*/
.vCalMain										{min-height:500px; padding:0px; visibility:hidden;}/*Masqué le tps du calcul de l'affichage*/
.vCalMain:not(:last-child)						{margin-bottom:50px;}
.vCalVue										{max-width:100%; width:100%; user-select:none!important; -webkit-user-select:none!important;}
.vCalHeader										{display:table; width:100%; font-size:1.1rem;}
.vCalHeader>div									{display:table-cell; padding:10px; vertical-align:middle;}
.vCalHeaderLeft, .vCalHeaderCenter				{table-layout:fixed;}/*table-layout pour fixer un width equivalent*/
.vCalHeaderLeftLabel							{margin-right:10px; vertical-align:middle;}
.vCalHeaderCenter								{text-align:center;}
.vCalHeaderCenter .vCalPrevNext					{padding:10px 15px; border-radius:var(--radius-field);}
.vCalHeaderCenter .vCalPrevNext:hover			{background-color:#eee;}
[id^=monthsYearsMenu]							{width:300px; overflow:visible;}
#monthsYearsMenuContainer a						{display:inline-block; width:85px; padding:5px; text-align:left;}
.vCalHeaderRight								{min-width:420px; width:420px; text-align:right;}
.vCalHeaderRight>span							{margin-inline:3px;}
.vCalHeaderRight button							{box-shadow:none;}
.vCalLabelDays									{padding:8px 4px; text-align:center; text-transform:capitalize;}

/*Evenements*/
.vEvtBlock										{min-height:22px; padding:3px; padding-right:20px;/*cf menu burger*/ box-shadow:1px 1px 2px #555; border-radius:4px!important;}
.vCellDay .vEvtBlock							{position:relative; height:22px; max-width:99%; margin-bottom:2px;}/*Evt sur une ligne : evts du mois ou journée entière*/
.vCellDay .menuContextLaunchFloat				{top:0px; right:0px;}/*décale le menu "burger"*/
.vEvtLabel										{overflow:hidden; text-overflow:ellipsis; color:white;}
.vEvtBlock[data-ispast='true'] .vEvtLabel		{opacity:0.7;}/*événements passés*/
.vCellDay .vEvtLabel							{font-size:0.85rem; white-space:nowrap;}/*white-space : texte sur une seule ligne*/
.vEvtConfirmOldDate								{opacity:0.5; margin-bottom:7px;}
.vEvtConfirmOldDate img							{visibility:hidden;}/*1er arrowRight masqué"*/
.vEvtDrag										{z-index:100!important; box-shadow:1px 1px 10px 4px #888;}/*Evt en cours de déplacement*/

/*** RESPONSIVE TABLET-SMARTPHONE*/
@media screen and (max-width:1199px){
	#pageContent								{padding:0px!important;}/*surcharge*/
	#readableCalsAdmin 							{visibility:visible;}
	.vReadableCalendar>div:last-child			{display:none;}/*contextMenu()*/
	.vCalMain									{width:100%; box-shadow:none; margin-bottom:0;}
	.vCalHeader									{white-space:nowrap;}
	.vCalHeader>div								{padding:4px; width:auto; text-transform:lowercase;}
	.vCalHeaderLeft, .vCalHeaderCenter			{min-width:100px;}
	.vCalHeaderLeftLabel						{display:inline-block; font-size:1rem; max-width:140px; margin-inline:0px; overflow:hidden; text-overflow:ellipsis;}/*Max-width avec inline-block + hidden + ellipsis*/
	.vCalHeaderLeftLabel::first-letter			{text-transform:uppercase}
	.vCalHeaderCenter .vCalPrevNext				{padding:4px;}
	.vCalHeaderCenter .vCalPrevNextPulsate		{background-color:#ddd;}
	.vCalHeaderRight							{min-width:auto; width:auto;}
	.vCalHeaderRight>span						{display:inline-block; line-height:35px; vertical-align:middle;}
	.vCalHeaderRight img						{min-height:20px;}
	.vEvtBlock									{overflow:hidden; padding-right:3px;}
	.vCalHeader .personImgSmall, .vEvtBlock .menuContextLaunchFloat {display:none!important;}/*Masque les icone des users, les dates des evt, le bouton burger des evt*/
}

/* IMPRESSION */
@media print{
	@page											{size:landscape;}/*format paysage*/
	html, body										{background-image:none!important;}/*surcharge*/
	body											{-webkit-print-color-adjust:exact; print-color-adjust:exact;}/*conserve les couleurs des evts*/
	.vCalMain, .vWeekHeader, .vWeekHeaderAllday, .vWeekTable, .vMonthHeader, .vMonthTable	{min-width:1200px!important; max-width:1200px!important; max-height:100%!important;}
	.vEvtBlock										{padding:2px!important; line-height:12px!important; font-size:12px!important; }
	.vCalMain										{box-shadow:none;}
	.vCalMain:not(:last-child)						{page-break-after:always;}/*saut de page après chaque agenda (sauf le dernier)*/
	.vCalHeader>div									{padding-block:15px; font-size:1.2rem;}
	.vCalHeaderCenter								{width:50%; text-align:right;}
	.vWeekScroller									{overflow:visible!important;}/*pas d'overflow scroll en affichage "week"*/
	.vMonthDayCell									{border:3px solid #ddd!important;}
	#synthBlock, .vCalPrevNext, .vCalHeaderRight, .vWeekNbOfYear	{display:none!important;}
	.circleNb										{background-color:white; color:black;}
}
</style>


<div id="pageFull">
	<div id="pageMenu">
		<!--PROPOSITIONS D'EVT-->
		<?php if(!empty($evtPropositions)){ ?>
			<div class="miscContent">
				<div class="evtPropositionTitle pulsate"><img src="app/img/important.png">&nbsp;<?= Txt::trad("CALENDAR_evtProposition") ?></div>
				<?php foreach($evtPropositions as $evtTmp){ ?>
					<div class="evtProposition optionSelect" data-idevt="<?= $evtTmp["_idEvt"] ?>" data-idcal="<?= $evtTmp["_idCal"] ?>" data-details="<?= strip_tags($evtTmp["evtPropDetails"],'<br><hr>') ?>" <?= Txt::tooltip($evtTmp["evtPropDetails"]) ?> >
						<?= $evtTmp["evtPropLabel"] ?>
					</div>
				<?php } ?>
			</div>
		<?php } ?>

		<div class="miscContent">
			<!--AGENDAS DISPONIBLES-->
			<?php if(!empty($readableCalendars)){ ?>
				<form action="index.php" id="readableCalendarsForm">
					<!--TITRE + OPTION D'AFFICHAGE ADMIN-->
					<div id="readableCalendarsTitle">
						<div><?= Txt::trad("CALENDAR_readableCalendars") ?> :</div>
						<div id="readableCalsAdmin"><?php if(Ctrl::$curUser->isSpaceAdmin()){ ?><img src="app/img/plusSmall.png" <?= Txt::tooltip("CALENDAR_displayAdmin") ?> onclick="redir('?ctrl=<?= Req::$curCtrl ?>&displayAdmin=<?= empty($_SESSION['displayAdmin'])?'true':'false' ?>')"><?php } ?></div>
					</div>
					<!--LISTE DES AGENDAS (Cf "getPref('displayedCalendars')")-->
					<?php foreach($readableCalendars as $tmpCal){ ?>
						<div class="vReadableCalendar">
							<div>
								<div class="<?= $tmpCal->isDisplayed==true?'optionSelect':'option' ?>" <?= Txt::tooltip(Txt::trad("CALENDAR_displayHide").'<hr>'.$tmpCal->description) ?>>
									<input type="checkbox" name="displayedCalendars[]" value="<?= $tmpCal->_id ?>" id="boxDisplay<?= $tmpCal->typeId ?>" <?= $tmpCal->isDisplayed==true?'checked':null ?> >
									<label for="boxDisplay<?= $tmpCal->typeId ?>"><?= $tmpCal->title ?></label>
								</div>
							</div>
							<div><?= $tmpCal->contextMenu(["burgerLauncher"=>"small-inline"]) ?></div>
						</div>
					<?php } ?>
					<input type="hidden" name="ctrl" value="<?= Req::$curCtrl ?>">
					<input type="hidden" name="curTime" value="<?= Req::param("curTime") ?>">
			</form>
			<hr>
			<?php }	?>

			<!--MENU DES CATEGORIES-->
			<?= MdlCalendarCategory::displayMenu()	?>

			<!--CREER UN AGENDA PARTAGE-->
			<?php if(MdlCalendar::addRight()){ ?>
			<div class="menuLine" onclick="lightboxOpen('<?= MdlCalendar::getUrlNew() ?>');" <?= Txt::tooltip("CALENDAR_addSharedCalendarTooltip") ?>>
				<div class="menuIcon"><img src="app/img/calendar/calendarAdd.png"></div>
				<div><?= Txt::trad("CALENDAR_addSharedCalendar") ?></div>
			</div>
			<?php } ?>

			<!--EVTS PROPRIO-->
			<?php if(Ctrl::$curUser->isUser()){ ?>
			<div class="menuLine" onclick="lightboxOpen('?ctrl=calendar&action=MyEvents')" <?= Txt::tooltip("CALENDAR_evtAutorInfo") ?>>
				<div class="menuIcon"><img src="app/img/edit.png"></div>
				<div><?= Txt::trad("CALENDAR_evtAutor") ?></div>
			</div>
			<?php } ?>

			<!--IMPRIMER LA PAGE-->
			<?php if(Req::isMobile()==false){ ?>
			<div class="menuLine" onclick="calendarDisplay(true);print();" <?= Txt::tooltip("CALENDAR_printCalendarsInfos") ?>>
				<div class="menuIcon"><img src="app/img/print.png"></div>
				<div><?= Txt::trad("CALENDAR_printCalendars") ?></div>
			</div>
			<?php } ?>

			<!--CALENDRIER MOIS VIA LE DATEPICKER-->
			<?= $displayMode!="month" ? '<hr><div id="datepickerCalendar"></div>' : null ?>
		</div>
	</div>

	<div id="pageContent">

		<!--SYNTHESE DES AGENDAS -->
		<?php if(!empty($periodSynthese)){ ?>
			<div id="synthBlock" class="miscContent">
				<div id="synthTable">
					<!--HEADER DE LA SYNTHESE-->
					<div id="synthHeader">
						<div class="vSynthLabel">&nbsp;</div>
						<?php foreach($periodSynthese as $dayYmd=>$tmpDay)  {echo '<div class="vSynthDay '.($dayYmd==date("Y-m-d")?"vSynthDayCurDay":null).'">'.(int)date("d",$tmpDay["dayTimeBegin"]).'</div>';} ?>
					</div>
					<!--AFFICHE CHAQUE AGENDA : LIBELLE & CHAQUE JOUR DE L'AGENDA-->
					<?php foreach($displayedCalendars as $tmpCal){ ?>
					<div class="vSynthLine">
						<div class="vSynthLabel" onclick="$('#calBlock<?= $tmpCal->typeId ?>').scrollTo();"><?= $tmpCal->title ?></div>
						<?php
						foreach($periodSynthese as $tmpDay){
							$tmpEvtTooltip='<div class="vSynthDayEvtTooltip">'.$tmpCal->title.'<br>'.Txt::dateLabel("default",$tmpDay["dayTimeBegin"]).' :<br>';
							foreach($tmpDay["dayEvtList"][$tmpCal->_id] as $tmpEvt)	{$tmpEvtTooltip.='<br>'.$tmpEvt->dateLabel("mini").' : '.Txt::reduce($tmpEvt->title,60);}
							$tmpEvtTooltip.='</div>';
							$syntheseDayCalWE=$syntheseDayEvts=null;
							if($tmpDay["dayOfWeek"]>5)	{$syntheseDayCalWE="vSynthDayCalWE";}
							foreach($tmpDay["dayEvtList"][$tmpCal->_id] as $tmpEvt)	{$syntheseDayEvts.='<div class="vSynthDayEvt" onclick="'.$tmpEvt->lightboxVue().'" style="background-color:'.$tmpEvt->bgColor.'">&nbsp;</div>';}
							echo '<div class="vSynthDay vSynthDayCal '.$syntheseDayCalWE.'">
									<div class="vSynthDayEvts" '.Txt::tooltip($tmpEvtTooltip).'>'.$syntheseDayEvts.'</div>
								  </div>';
						}
						?>
					</div>
					<?php } ?>
				</div>
			</div>
		<?php } ?>

		<!--AFFICHE CHAQUE AGENDA-->
		<?php foreach($displayedCalendars as $tmpCal){ ?>
		<div class="vCalMain miscContent" id="calBlock<?= $tmpCal->typeId ?>">
			<div class="vCalHeader">
				<!--TITRE DE L'AGENDA-->
				<div class="vCalHeaderLeft">
					<?php
					////	LABEL DE L'AGENDA  +  L'ICONE DE L'USER  +  MENU CONTEXT
					$calLabel='<span class="vCalHeaderLeftLabel" '.Txt::tooltip($tmpCal->description).'>'.$tmpCal->title.'</span>';
					if($tmpCal->isPersonal())  {$calLabel.=Ctrl::getObj("user",$tmpCal->_idUser)->tagProfileImg(true,true);}
					echo $tmpCal->contextMenu(["burgerLauncher"=>"small-inline", "burgerLauncherLabel"=>$calLabel]);
					?>
				</div>
				<!--PERIODE AFFICHEE  +  PRECEDENT/SUIVANT  +  MENU CONTEXT MONTHS/YEARS-->
				<div class="vCalHeaderCenter">
					<span class="vCalPrevNext vCalPrev" onclick="redir('?ctrl=calendar&curTime=<?= $timePrev ?>')" <?= Txt::tooltip("CALENDAR_periodPrev") ?>><img src="app/img/arrowLeftNav.png"></span>
					<span class="menuContextLaunch vCalHeaderMonth" for="monthsYearsMenu<?= $tmpCal->typeId ?>"><?= ucfirst($monthLabel) ?></span>
					<?php if(!empty($monthsYearsMenu))  {echo "<div class='menuContext' id='monthsYearsMenu".$tmpCal->typeId."'><div id='monthsYearsMenuContainer'>".$monthsYearsMenu."</div></div>";} ?>
					<span class="vCalPrevNext vCalNext" onclick="redir('?ctrl=calendar&curTime=<?= $timeNext ?>')" <?= Txt::tooltip("CALENDAR_periodNext") ?>><img src="app/img/arrowRightNav.png"></span>
				</div>
				
				<!--PROPOSER/AJOUTER UN EVT  +  "AUJOURD'HUI"  +  AFFICHAGE MONTH/WEEK/ETC-->
				<div class="vCalHeaderRight">
					<span onclick="redir('?ctrl=calendar&curTime=<?= time() ?>')" <?= Txt::tooltip("displayToday") ?> >
						<?= Req::isMobile() ? '<img src="app/img/calendar/displayToday.png">' : '<button>'.Txt::trad("today").'</button>' ?>
					</span>
					<span class="menuContextLaunch" for="menuDisplayMode<?= $tmpCal->typeId ?>">
						<?= Req::isMobile() ? '<img src="app/img/calendar/display'.ucfirst($displayMode).'.png">' : '<button>'.Txt::trad("CALENDAR_display_".$displayMode).' <img src="app/img/arrowBottom.png"></button>' ?>
					</span>
					<div class="menuContext" id="menuDisplayMode<?= $tmpCal->typeId ?>">
						<?php foreach($displayModeList as $displayModeTmp){ ?>
						<div class="menuLine <?= $displayModeTmp==$displayMode?"linkSelect":null ?>" onclick="redir('?ctrl=calendar&calendarDisplayMode=<?= $displayModeTmp ?>')">
							<div class="menuIcon"><img src="app/img/calendar/display<?= ucfirst($displayModeTmp) ?>.png"></div><div><?= ucfirst(Txt::trad("CALENDAR_display_".$displayModeTmp)) ?></div>
						</div>
						<?php } ?>
					</div>
					<?php if($tmpCal->addProposeEvt()){ ?>
					<span onclick="lightboxOpen('<?= $tmpCal->urlNewEvt ?>')" <?= $tmpCal->addEvtTooltip ?> >
						<?= Req::isMobile() ? '<img src="app/img/plus.png">' : '<button><img src="app/img/plusSmall.png">&nbsp; '.Txt::trad("CALENDAR_addEvt").'</button>' ?>
					</span>
					<?php } ?>
				</div>
			</div>
			<!-- VUE MONTH / WEEK DE L'AGENDA (Cf "VueCalendarMonth"/"VueCalendarWeek")-->
			<?= $tmpCal->calendarVue ?>
		</div>
		<?php } ?>

		<!--AUCUN AGENDA-->
		<?php if(empty($displayedCalendars)){ ?>
			<div class="miscContent emptyContent"><?= Txt::trad("CALENDAR_noCalendarDisplayed") ?></div>
		<?php } ?>
	</div>
</div>