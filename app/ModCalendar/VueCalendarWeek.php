<?php if($tmpCal->isFirstCal==true){ ?>
	<script>
	/************************************************************************************************************
	 *	DIMENSIONNE & AFFICHE LES AGENDAS
	 ************************************************************************************************************/
	function calendarDisplay(isPrint)
	{
		////	DIMENSION DES CELLULES DES AGENDAS
		$(".vWeekScroller").height(1);																//Hauteur minimum (pas masqué) pour calculer le width de .vWeekCell
		if(isPrint==true)  {$(".vWeekHeader, .vWeekHeaderAllday, .vWeekTable").width(1200);}		//Print : fixe le width des <table> de l'agenda (idem css "@media print{}")
		weekCellHeight=Math.floor( ($(".vCalVue").height() - $(".vWeekHeader").height()) / 48 );	//Height basé sur une plage horaire 8h-20h (48 = 12h x 4 cells de 15mn)
		if(weekCellHeight<7.5 || isPrint==true)  {weekCellHeight=7.5;}								//Height minimum des .weekCell (tester print en résolution 1440*900)
		$(".vWeekCell").outerHeight(weekCellHeight);												//-> Applique le height
		weekScrollTop=<?= $tmpCal->timeSlotBegin ?> * weekCellHeight * 4;							//Scrolltop par défaut en fonction du timeslotbegin
		$(".vWeekScroller").scrollTop(weekScrollTop);												//-> Applique le scrolltop

		////	EVTS DE CHAQUE AGENDA : DIMENSION & POSITION 
		prevEvtTimeEnd=0;
		$(".vCalVue").each(function(){
			////	TRI LES EVTS PAR TIMEBEGIN
			const calVueTmp=this;
			const evtBlockList=$(this).find(".vEvtBlock").sort(function(a,b){
				return $(a).attr("data-timebegin") - $(b).attr("data-timebegin")
			});
			////	PARCOURT CHAQUE EVT DE L'AGENDA
			evtBlockList.each(function(){
				////	INIT
				const evtYmd=this.getAttribute("data-ymd");											//Date à laquelle l'evt est affiché (cf evt sur plusieurs jours)
				const evtDayIndex=$(evtBlockList).filter("[data-ymd='"+evtYmd+"']").index(this);	//Récupère le classement/index de l'evt parmi les autres evt du jour
				const evtTimeBegin=this.getAttribute("data-timebegin");								//timebegin de l'evt
				const dayFirstCell=document.querySelector(".vWeekCell[data-ymd='"+evtYmd+"']");		//Selecteur de la 1ere cellule du jour (0:00)
				////	EVT "ALL DAY" DANS LE HEADER
				if(this.getAttribute("data-allday")=="true"){
					$(calVueTmp).find(".vWeekHeaderAllday .vWeekHeaderAlldayCell[data-ymd='"+evtYmd+"']").append(this);	//Déplace l'evt dans le header
					$(calVueTmp).find(".vWeekHeaderAllday").show();														//Affiche le header
				}
				////	POSITIONNE L'EVT
				else{
					let evtWidth=dayFirstCell.getBoundingClientRect().width;													//Width de l'evt
					let evtX=dayFirstCell.offsetLeft;																			//Position X de l'evt en fonction de la position du jour
					let evtSameTime=$(calVueTmp).find(".vEvtBlock[data-ymd='"+evtYmd+"'][data-timebegin='"+evtTimeBegin+"']");	//Verif si d'autres evts commencent en même temps
					////	D'AUTRES EVTS COMMENCENT EN MÊME TEMPS : SPLIT L'EVT
					if(evtSameTime.length > 1){
						evtWidth=evtWidth / evtSameTime.length;		//Largeur en fonction du nb d'evt à afficher cote à cote
						evtX+=evtWidth * evtSameTime.index(this);	//Décale l'evt en fonction de son rang (index) parmi les autres evts
						this.classList.add("vEvtBlockSuperposed");	//Bordure accentuée et z-index
					}
					////	EVT SUR LE MÊME CRÉNEAU QU'UN AUTRE EVT : DÉCALE L'EVT
					else if(evtDayIndex > 0  && (prevEvtTimeEnd > evtTimeBegin || prevEvtTimeEnd > this.getAttribute("data-timeend"))){
						this.classList.add("vEvtBlockSuperposed");	//Bordure accentuée et z-index
						evtWidth-=35;								//Réduit la largeur de l'evt de 20px
						evtX+=35;									//Décale d'autant sur la droite				
					}
					////	POSITION / DIMENSIONS DE L'EVT
					let evtHeight=(weekCellHeight/900) * this.getAttribute("data-timeduration");				//Hauteur de l'evt (900sec = cell de 15mn)
					let evtY=(weekCellHeight/900) * this.getAttribute("data-timefromdaybegin");					//Position top
					if(dayFirstCell.getAttribute("data-summer-change"))			{evtY+=(weekCellHeight*4);}		//Journée de changement en heure d'été : décale d'une heure
					else if(dayFirstCell.getAttribute("data-winter-change"))	{evtY-=(weekCellHeight*4);}		//Idem pour l'heure d'hiver
					this.style.left=evtX+'px';																	//Positonne l'evt en X
					this.style.top =evtY+'px';																	//Positonne l'evt en Y
					//this.style.zIndex=evtDayIndex+10;															//Applique un z-index (10+n) => CONTROLER L'AFFICHAGE DES .menuContext
					$(this).outerWidth(evtWidth-2).outerHeight(evtHeight-2);									//Dimensions de l'evt (-2px pour les distinguer)
					$(this).find(".vEvtLabel").outerHeight($(this).height());									//Hauteur au vEvtLabel (pas de css "height:inherit")
					////	UPDATE DE VARIABLES
					if(evtY < weekScrollTop && this.getAttribute("data-timefromdaybegin")!=0)  {$(calVueTmp).find(".vWeekScroller").scrollTop(evtY);}	//Ajuste le scrolltop de l'agenda si l'evt est plus tôt
					if(evtDayIndex==0 || prevEvtTimeEnd < this.getAttribute("data-timeend"))   {prevEvtTimeEnd=this.getAttribute("data-timeend");}		//Update "prevEvtTimeEnd" pour l'evt d'après : 1er evt du jour ou timeEnd supérieur
				}
			});
			////	HEIGHT DU ".vWeekScroller"
			const headerAllDayHeight=$(this).find(".vWeekHeaderAllday").isVisible()  ?  $(this).find(".vWeekHeaderAllday").outerHeight()  :  0;	//Height de .vWeekHeaderAllday
			const weekScrollerHeight=$(this).height() - $(this).find(".vWeekHeader").height() - headerAllDayHeight;								//Height de .vWeekScroller
			$(this).find(".vWeekScroller").outerHeight(weekScrollerHeight);																		//Update le height
		});
	}

	/************************************************************************************************************
	 *	SELECTIONNE UN CRÉNEAU HORAIRE POUR AJOUTER UN EVT
	 ************************************************************************************************************/
	ready(function(){
		if(isMobile()==false){
			let isMouseDown=startTimeBegin=startTimeEnd=null;
			$(".vWeekCellAddEvt").on("mousedown mousemove mouseup",function(event){
				if(event.type=="mousedown"){											//// Début de sélection : init les valeurs
					isMouseDown=true;													// Debut de sélection
					startYmd=this.getAttribute("data-ymd");								// Jour Ymd
					startTimeBegin=parseInt(this.getAttribute("data-timebegin"));		// Time du début de sélection
					startTimeEnd  =parseInt(this.getAttribute("data-timeend"));			// Time de fin de sélection
					this.classList.add("lineSelect");									// Sélection du .vWeekCell
				}
				else if(event.type=="mousemove" && isMouseDown==true && startYmd==this.getAttribute("data-ymd")){	//// Continue la sélection sur le même jour
					startTimeEnd=parseInt(this.getAttribute("data-timeend"));										// Update le Time de fin de sélection
					$(".vWeekCell[data-ymd='"+startYmd+"']").each(function(){										// Sélection/déselection des .vWeekCell (descend/monte la souris : ajoute/enlève .lineSelect)
						if(startTimeBegin <= parseInt(this.getAttribute("data-timebegin"))  &&  parseInt(this.getAttribute("data-timeend")) <= startTimeEnd)	{this.classList.add("lineSelect");}
						else																																	{this.classList.remove("lineSelect");}
					});
				}
				else if(event.type=="mouseup" && startTimeBegin < startTimeEnd){	//// Fin de sélection : ouvre l'édition d'un nouvel événement !
					lightboxOpen("<?= MdlCalendarEvent::getUrlNew() ?>&_idCal="+this.getAttribute("data-idcal")+"&newEvtTimeBegin="+startTimeBegin+"&newEvtTimeEnd="+startTimeEnd);
					isMouseDown=startTimeBegin=startTimeEnd=null;	// Réinit les valeurs
					$(".vWeekCell").removeClass("lineSelect");		// Réinit .lineSelect
				}
			});
		}
	});
	</script>


	<style>
	:root											{--table-border:1px solid <?= Ctrl::$agora->skin=='white'?'#dededf':'#333' ?>;}
	.vCalVue										{height:100%;}
	.vWeekScroller									{position:relative; width:100%; max-width:100%; overflow-y:scroll;}				/*Partie visible de l'agenda*/
	.vWeekScroller::-webkit-scrollbar, .vWeekHeaderScrollbar	{width:13px;}														/*scrollbar des agendas et Width "fantome"*/
	.vWeekHeader, .vWeekHeaderAllday, .vWeekTable	{width:100%; max-width:100%; border-collapse:collapse; table-layout:fixed;}		/*fusionne les border + table-layout pour fixer un width equivalent (sauf .vWeekHourLabel)*/
	.vWeekDayLabel 									{height:32px; line-height:32px; text-align:center; text-transform:capitalize;}
	.vWeekDayLabel .circleNb						{width:30px; height:30px; line-height:30px; font-size:1.1rem;}	/*surcharge*/
	.vWeekDayLabel .vPublicHoliday					{margin-left:10px;}												/*Icone du jour férié*/
	.vWeekDayLabel:not(:hover) .vWeekDayAddEvt		{visibility:hidden;}											/*Icone pour ajouter un evt sur le jour*/
	.vWeekHeaderAllday								{display:none;}													/*masque par défaut*/
	.vWeekHeaderAlldayCell							{vertical-align:top;}
	.vWeekHourLabel									{width:40px; text-align:center; vertical-align:top; color:#888; font-size:0.9rem;}/*Libellé des heures : 1ere colonne du tableau*/
	.vWeekCell										{padding:0px; border-left:var(--table-border); }						/*Cellules du tableau : créneaux de 15mn*/
	.vWeekTable tr:nth-child(4n+1) .vWeekCell 		{border-top:var(--table-border);}										/*borter-top toutes les 4 lignes (1er 1/4 d'heure)*/
	.vWeekCellRedLine								{border-top:solid 1px #f00;}										   /*Heure courante : border-top rouge*/
	.vLineNotTimeSlot								{background:<?= Ctrl::$agora->skin=="white"?"#fbfbfb" : "#222" ?>} /*Heures en dehors du TimeSlot*/
	.vWeekScroller .vEvtBlock						{position:absolute;}/*Tester un evt de 15mn*/
	.vWeekScroller .menuContextLaunchFloat			{top:2px; right:2px;}/*idem*/
	.vWeekScroller .vEvtLabel						{font-size:0.9rem;}
	.vEvtBlockSuperposed							{box-shadow:0px 0px 3px black;}			/*Evts superposés*/
	.vEvtLabelHM									{margin-top:2px;}						/*Label de l'heure*/
	.vEvtLabelHMdragged								{font-size:1.2rem; font-weight:bold;}	/*Label durant un drag & drop*/
	.vMobileAddEvt									{display:none;}

	/*** RESPONSIVE TABLET-SMARTPHONE*/
	@media screen and (max-width:1199px){
		.vWeekTable							{max-width:99%;}
		.vWeekHourLabel, .vEvtLabel			{font-size:0.8rem;}
		.vWeekHourLabel						{width:25px;}
		.vEvtLabelHMdragged					{font-size:1rem;}
		.vWeekCell:active .vMobileAddEvt	{display:block; position:absolute}/*affiche si on selectionne la ligne*/
		.vWeekHourLabel00					{display:none;}
		.vEvtBlock 							{touch-action:none;}/*Pas de scroll durant le drag & drop d'evt*/
		.vEvtBlock:not(:hover) .vEvtLabelHM	{display:none;}
	}
	</style>
<?php } ?>


<div class="vCalVue">

	<!--HEADER DES JOURS DE LA SEMAINE-->
	<table class="vWeekHeader">
		<tr>
			<td class="vWeekHourLabel">&nbsp;</td>
			<?php
			foreach($periodDays as $dayYmd=>$tmpDay){
				$dayTooltip=$dayAddEvtLink=null;
				if($tmpCal->addProposeEvt()){
					$dayTooltip=Txt::trad("CALENDAR_addEvtTooltip")." : ".Txt::dateLabel("dateDefault",$tmpDay["dayTimeBegin"]);
					$dayAddEvtLink="onclick=\"lightboxOpen('".$tmpCal->urlNewEvt."&newEvtTimeBegin=".$tmpDay["dayTimeBegin"]."&newEvtAllDay=true')\"";
				}
				if(!empty($tmpDay["publicHoliday"]))  {$dayTooltip.='<hr>'.$tmpDay["publicHoliday"];}
			?>
				<!--LABEL DU JOUR  +  NUM. DU JOUR DU MOIS  +  JOUR FERIE-->
				<td class="vWeekDayLabel" <?= $dayAddEvtLink ?> <?= Txt::tooltip($dayTooltip) ?>>
					<?= Txt::timeLabel($tmpDay["dayTimeBegin"],'ccc') ?>
					<span class="<?= $tmpDay["isToday"]==true?'circleNb':null ?>"><?= $tmpDay["dayOfMonth"] ?></span>
					<?php if(!empty($tmpDay["publicHoliday"])){ ?><img src="app/img/calendar/publicHoliday.png" class="vPublicHoliday"><?php } ?>
				</td>
			<?php } ?>
			<td class="vWeekHeaderScrollbar">&nbsp;</td>
		</tr>
	</table>

	<!--HEADER DES EVT "ALLDAY" POUR CHAQUE JOUR DE LA SEMAINE-->
	<table class="vWeekHeaderAllday">
		<tr>
			<td class="vWeekHourLabel">&nbsp;</td>
			<?php foreach($periodDays as $dayYmd=>$tmpDay){ ?>
				<td class="vWeekHeaderAlldayCell vCellDay" data-datelabel="<?= Txt::dateLabel("dateDefault",$tmpDay["dayTimeBegin"]) ?>" data-ymd="<?= $dayYmd ?>"></td>
			<?php } ?>
			<td class="vWeekHeaderScrollbar">&nbsp;</td>
		</tr>
	</table>

	<!--AGENDA SCROLLABLE-->
	<div class="vWeekScroller">
		<table class="vWeekTable">
			<?php
			////	BOUCLE SUR LES LIGNES DU TABLEAU (CRÉNEAUX DE 15MN)
			for($tmp15mn=0; $tmp15mn<96; $tmp15mn++){
				$isHourBegin=($tmp15mn % 4===0);
			?>
				<tr <?= ($tmp15mn < ($tmpCal->timeSlotBegin*4) || ($tmpCal->timeSlotEnd*4) <= $tmp15mn) ?  'class="vLineNotTimeSlot"'  : null ?> >
					<!--COLONNE DES HEURES (modulo '%' verif si multiple de 4)-->
					<?php if($isHourBegin==true){ ?><td class="vWeekHourLabel" rowspan="4"><?= ($tmp15mn/4) ?><span class="vWeekHourLabel00">:00</span></td><?php } ?>
					<?php
					////	CELLULE DE CHAQUE JOUR AFFICHÉ
					foreach($periodDays as $dayYmd=>$tmpDay){
						$cellClass="vWeekCell notooltip";
						$cellTimeBegin=$tmpDay["dayTimeBegin"]+($tmp15mn*900);										//Time du début du 1/4 d'heure
						$cellTimeEnd=$cellTimeBegin+900;															//Time de fin
						if($cellTimeBegin <= time() && time() <= $cellTimeEnd)	{$cellClass.=" vWeekCellRedLine ";}	//Heure courante : RedLine
						if($tmpCal->addProposeEvt())  							{$cellClass.=" vWeekCellAddEvt ";}	//Droit d'ajouter un Evt
						$cellAttributes='data-timebegin="'.$cellTimeBegin.'" '.
										'data-timeend="'.$cellTimeEnd.'" '.
										'data-datelabel="'.Txt::dateLabel("dateDefault",$cellTimeBegin).'" '.
										'data-ymd="'.$dayYmd.'" '.
										'data-hm="'.date("H:i",$cellTimeBegin).'" '.
										'data-idcal="'.$tmpCal->_id.'" '.
										'data-summer-change="'.$tmpDay["summerChange"].'" '.
										'data-winter-change="'.$tmpDay["winterChange"].'" ';
					?>
						<!--CELLULE DU 1/4 D'HEURE & D'AJOUT D'EVT-->
						<td class="<?= $cellClass ?> " title="<?= date('H:i',$cellTimeBegin) ?>" <?= $cellAttributes ?>>
							<?php if(Req::isMobile() && $isHourBegin==true){ ?>
								<img src="app/img/plusSmall.png" class="vMobileAddEvt" onclick="lightboxOpen('<?= $tmpCal->urlNewEvt.'&newEvtTimeBegin='.$cellTimeBegin ?>')">
							<?php } ?>
						</td>
					<?php } ?>
				</tr>
			<?php } ?>
		</table>

		<!--EVENEMENTS DE CHAQUE JOURS DE LA SEMAINE-->
		<?php
		foreach($tmpCal->evtListDays as $evtList){
			foreach($evtList as $tmpEvt){
		?>
			<?= $tmpEvt->objContentDiv("vEvtBlock",$tmpEvt->contextMenuOptions) ?>
				<div class="vEvtLabel" <?= Txt::tooltip($tmpEvt->tooltip) ?> onclick="<?= $tmpEvt->lightboxVue() ?>">
					<?= $tmpEvt->title ?>
					<div class="vEvtLabelHM"><?= $tmpEvt->dateLabel("mini") ?></div>
				</div>
			</div>
		<?php
			}
		}
		?>
	</div>
	
</div>