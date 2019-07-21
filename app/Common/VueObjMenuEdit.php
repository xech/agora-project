<script>
$(function(){
	////	ONGLETS / OPTIONS (chaque onglet ".objMenuLabel" doit avoir un "for" correspondant à l'Id de son div)
	if($("#objMenuLabels").exist())
	{
		//Change de menu
		$(".objMenuLabel").click(function(){
			//Réinit les autres menus
			$(".objMenuLabel").not(this).each(function(){
				$("#"+$(this).attr("for")).hide();
				$(this).addClass("objMenuLabelUnselect");
			});
			//Affiche le block sélectionné
			$("#"+$(this).attr("for")).show();//affichage direct, pas "fadeIn" (cf. "lightboxResize()")
			$(this).removeClass("objMenuLabelUnselect");
		});
		//Affiche le menu du premier onglet
		$(".objMenuLabel:first-child").trigger("click");
	}

	////	AFFECTATIONS : CLICK DE LABEL
	$(".vSpaceTable:visible .vSpaceLabel").click(function(){
		//Init
		var boxRead		 ="#objectRightBox_"+this.id+"_1";
		var boxWriteLimit="#objectRightBox_"+this.id+"_15";
		var boxWrite	 ="#objectRightBox_"+this.id+"_2";
		var boxToCheck=null;
		//Bascule les checkbox : lecture / ecriture limité / écriture
		if(!$(boxRead).prop("disabled") && !$(boxRead).prop("checked") && !$(boxWriteLimit).prop("checked") && !$(boxWrite).prop("checked"))	{boxToCheck=boxRead;}		//"1" actif && tout est décochées
		else if(!$(boxWriteLimit).prop("disabled") && !$(boxWriteLimit).prop("checked") && !$(boxWrite).prop("checked"))						{boxToCheck=boxWriteLimit;}	//"1.5" actif && "1.5" décoché && "2" décoché
		else if(!$(boxWrite).prop("disabled")  &&  !$(boxWrite).prop("checked")  &&  ( ($(boxRead).prop("disabled") && $(boxWriteLimit).prop("disabled")) || ($(boxRead).prop("checked") && $(boxWriteLimit).prop("disabled")) || $(boxWriteLimit).prop("checked")))	{boxToCheck=boxWrite;}	//"2" actif && "2" décoché &&  ( ("1" inatif & "1.5" inatif) || ("1" coché & "1.5" inactif) || "1.5" coché)
		//Check la box sélectionnée (avec trigger sur la box)  OU  Uncheck toutes les boxes et Stylise toute la sélection
		if(boxToCheck!=null)	{$(boxToCheck).prop("checked",true).trigger("change");}
		else					{$("[id^='objectRightBox_"+this.id+"']").prop("checked",false).trigger("change");}
	});

	////	AFFECTATIONS : CLICK DE CHECKBOX
	$(".vSpaceTable:visible [id^='objectRightBox']").change(function(){
		var objectRight=$(this).val();
		var targetId=objectRight.slice(0, objectRight.lastIndexOf("_"));//exple "1_U2_1.5" => "1_U2"
		$("[id^='objectRightBox_"+targetId+"']").not(this).prop("checked",false);//"uncheck" les autres checkbox du "target"
		labelStyleRightControl(this.id);//Style des labels & Controle des droits
	});

	////	SELECTIONNE UN NOUVEAU FICHIER JOINT
	$("input[name^='addAttachedFile']").change(function(){
		//Fichier OK : affiche l'input suivant et affiche au besoin "insertion dans le text" (+ Check par défaut)
		if(this.files && this.files[0].size < <?= File::uploadMaxFilesize() ?>)
		{
			var cptFile=Math.round(this.name.replace("addAttachedFile",""));
			var fileExtension=extension($(this).val());
			var acceptedExtensions=['<?= implode("','",File::fileTypes("attachedFileInsert")) ?>'];
			if($("#addAttachedFileInsert"+cptFile).exist() && $.inArray(fileExtension,acceptedExtensions)!==-1){
				$("#addAttachedFileDiv"+cptFile+" .addAttachedFileInsertOpt").show();
				$("#addAttachedFileInsert"+cptFile+":not(:checked)").trigger("click");
			}
			$("#addAttachedFileDiv"+(cptFile+1)).fadeIn();
		}
	});

	////	AFFICHE TOUS LES USERS D'UN ESPACE
	$(".vShowAllSpaceUsers").click(function(){
		$($(this).attr("for")+" .vSpaceHideSelection").hide().removeClass('vSpaceHideSelection').fadeIn(500);//enlève la class pour masquer les users.. et raffiche avec un fadeIn
		lightboxResize();//resize le lightbox
		$('body').css('overflow-x','hidden');//enlège l'overflow ajouté dans la foulée..
		$(this).hide();//masque le menu
	});

	////	AFFICHE/MASQUE LES BLOCKS D'ESPACES
	//Masque les espaces sans affectations (sauf espace courant)
	$("[id^=spaceTable]").each(function(){
		if(this.id!="spaceTable<?= Ctrl::$curSpace->_id ?>" && $("#"+this.id+" [name='objectRight[]']:checked").length==0)  {$(this).hide();}
	});
	//Montre "Afficher tous les espaces" ?
	if($(".vSpaceTable:hidden").exist())  {$(".vShowAllSpaces").fadeIn();}

	////	INIT LA PAGE
	//Masque et désactive les droits "boxWriteLimit"
	<?php if($curObj::isContainer()==false){ ?>
		$("[name='objectRight[]'][value$='_1.5']").prop("disabled",true);
		$(".vSpaceWriteLimit").hide();
	<?php } ?>
	//Init le style des labels
	labelStyleRightControl();
	//Fixe la hauteur minimum : évite à "lightboxResize()" de jouer si on passe d'un gros menu à un plus petit..
	$("#objMenuBlocks").css("min-height",$("#objMenuBlocks").height());
	//Focus sur le premier champ obligatoire
	<?php if(!empty($curObj::$requiredFields)){ ?>
		if(!isMobile()){//Pas en responsive pour ne pas afficher le clavier virtuel
			setTimeout(function(){ $("input[name='<?=$curObj::$requiredFields[0] ?>']").focus(); },300);//Timeout pour Edge et FF
		}
	<?php } ?>
});

////	STYLISE LES LABELS ET CONTROLE LES DROITS D'ACCÈS
function labelStyleRightControl(boxId)
{
	//Réinitialise les class des lignes et labels
	$(".vSpaceTable:visible .vSpaceLabel").removeClass("sAccessRead sAccessWriteLimit sAccessWrite");
	$(".vSpaceTable:visible [id^=targetLine]").removeClass("sTableRowSelect");
	//Stylise les labels des checkbox sélectionnées
	$("[name='objectRight[]']:checked").each(function(){
		//Récupère le droit de la checkbox && l'id du label correspondant
		var targetRight=this.id.split('_').pop();
		var targetLabelId=this.id.substring(0, this.id.lastIndexOf('_')).replace('objectRightBox_','');
		//Stylise le label
		if(targetRight=="1")		{$("#"+targetLabelId).addClass("sAccessRead");}
		else if(targetRight=="15")	{$("#"+targetLabelId).addClass("sAccessWriteLimit");}
		else if(targetRight=="2")	{$("#"+targetLabelId).addClass("sAccessWrite");}
		//Ligne sélectionnée : surligne
		$("#targetLine"+targetLabelId).addClass("sTableRowSelect");
		//Sujet du forum : affiche "preférez le droit écriture limité" ?	=> pas un droit "écriture limité"  & box que l'on vient de sélectionner (pas les pré-sélections)
		if("<?= $curObj::objectType ?>"=="forumSubject" && targetRight!="15" && boxId && boxId==this.id)
			{notify("<?= Txt::trad("FORUM_accessRightInfos") ?>");}
	});
	//Control Ajax d'une affectation (droit d'accès) pour un sous dossier
	<?php if($curObj::isFolder && $curObj->containerObj()->isRootFolder()==false){ ?>
	if(boxId && $("#"+boxId).prop("checked")){
		$.ajax({url:"?ctrl=object&action=AccessRightParentFolder&targetObjId=<?= $curObj->containerObj()->_targetObjId ?>&objectRight="+$("#"+boxId).val(), dataType:"json"}).done(function(result){
			if(result.error)  {notify(result.message);}
		});
	}
	<?php } ?>
}

////	SUPPRESSION D'UN FICHIER JOINT
function deleteAttachedFile(_id)
{
	//Demande confirmation
	if(confirm("<?= Txt::trad("confirmDelete") ?>")==false)  {return false;}
	//Lance la suppression et efface le fichier lorsque c'est fait
	$.ajax("?ctrl=object&action=deleteAttachedFile&_id="+_id).done(function(result){
		if(find("ok",result)){
			$("#menuAttachedFile"+_id).fadeOut();//Supprime le fichier de la liste
			tinymce.activeEditor.dom.remove("tagAttachedFile"+_id);//Supprime éventuellement l'image dans l'éditeur (pas besoin de "#" pour selectionner l'id)
		}
	});
}

////	CONTROLE FINAL DU FORMULAIRE
function mainFormControl()
{
	//Init
	var validForm=true;

	////	Verif si besoin les champs obligatoires : si vide, affiche une notification et focus le champ
	var notifRequiredFields="";
	<?php
	foreach($curObj::$requiredFields as $tmpField){
		$isEmptyField=($tmpField==$curObj::htmlEditorField)  ?  "isEmptyEditor('".$curObj::htmlEditorField."')"  :  "$('[name=".$tmpField."]').isEmpty()";
		echo "if($('[name=".$tmpField."]').exist() && ".$isEmptyField.")   {validForm=false;  $('[name=".$tmpField."]').focusRed();  notifRequiredFields+=\"&nbsp;".Txt::trad($tmpField)."<br>\";}";
	}
	?>
	//Notif sur les champs vides
	if(notifRequiredFields.length>0)  {notify("<?= Txt::trad("requiredFields") ?> : "+notifRequiredFields);}

	////	Controle un mail (si besoin)
	if($("input[name='mail']").isEmpty()==false && $("input[name='mail']").isMail()==false)   {validForm=false;  notify("<?= Txt::trad("mailInvalid"); ?>");}

	////	Controle le formatage des dates
	$(".dateInput,.dateBegin,.dateEnd").each(function(){
		if(this.value.length>0){
			var dateMatch=/^\d{2}\/\d{2}\/\d{4}$/.exec(this.value);
			if(dateMatch==null)   {validForm=false;  notify("<?= Txt::trad("dateFormatError") ?>");}
		}
	});

	////	Controle les affectations
	if($("[name='objectRight[]']").exist())
	{
		//Aucune affectation : false
		if($(":checked[name='objectRight[]']").length==0)   {validForm=false;  notify("<?= Txt::trad("EDIT_notifNoSelection") ?>");}
		//Sujet du forum et uniquement des accès en lecture : false!
		if("<?= $curObj::objectType ?>"=="forumSubject" && $(":checked[name='objectRight[]'][value$='_1.5'], :checked[name='objectRight[]'][value$='_2']").length==0)   {validForm=false;  notify("<?= Txt::trad("EDIT_notifWriteAccess") ?>");}
		//Aucun accès pour l'user courant?
		var nbCurUserAccess=$(":checked[name='objectRight[]'][value*='spaceUsers'], :checked[name='objectRight[]'][value*='_U<?= Ctrl::$curUser->_id ?>_']").length;
		if(nbCurUserAccess==0 && confirm("<?= Txt::trad("EDIT_notifNoPersoAccess") ?>")==false)  {validForm=false;}
	}

	////	Controle OK
	if(validForm==true)  {$(".loadingImg").css("display","block");}
	return validForm;
}
</script>

<style>
/*OPTIONS D'EDITION (ex 'fieldset')*/
#objMenuBlocks				{text-align:left; margin-top:33px;}
#objMenuLabels				{display:table; width:100%; margin:33px 0px -33px 0px;}
.objMenuLabel				{display:table-cell; padding:3px; padding-top:8px; padding-bottom:8px; text-align:center; cursor:pointer; border-radius:3px 3px 0px 0px;}
.objMenuLabel[for='objMenuMain']		{min-width:150px!important;}
.objMenuLabel>span						{display:inline-block; margin-left:10px;}
.objMenuLabel:not(.objMenuLabelUnselect){border-bottom:none!important;}
.objMenuLabelUnselect		{opacity:0.7;}

/*DROITS D'ACCÈS*/
#objMenuMain				{text-align:center;}
[id^=spaceTable]			{text-align:center; margin-top:20px; margin-bottom:40px;}
.vSpaceTable				{display:inline-table; min-width:400px; max-width:97%;}/*idem responsive!*/
.vSpaceTable img			{max-height:15px;}
.vSpaceTable>div			{display:table-row;}
.vSpaceTable>div>div		{display:table-cell; padding:4px;}
.vSpaceTable .vSpaceLabel	{cursor:pointer; text-align:left;}
.vSpaceTitle				{background-color:#f9f9f9;}
.vSpaceTitle>div:not(:first-child)	{width:70px; text-align:center;}/*colonne des checkboxes*/
.vSpaceHideSelection		{display:none!important;}
.vShowAllSpaceUsers, .vShowAllSpaces, .vExtendToSubfolders	{text-align:center; cursor:pointer;}
.vShowAllSpaceUsers						{margin-top:10px;}
.vShowAllSpaces,.vExtendToSubfolders	{margin-top:20px;}

/*FICHIERS JOINTS*/
#addAttachedFileLabel		{margin-top:10px; margin-bottom:20px;}
[id^='addAttachedFileDiv'], [id^='menuAttachedFile']	{margin:15px;}
[id^='addAttachedFileDiv']:not(#addAttachedFileDiv1)	{display:none;}/*Affiche par défaut le premier Input*/
.addAttachedFileInsertOpt	{display:none;}

/*NOTIFICATION PAR MAIL AND CO*/
#notifMailUsersPlus, #notifMailSelectList, #notifMailOptions	{display:none;}
#notifMailSelectList		{padding-left:25px; border-radius:3px;}
#notifMailSelectList>div	{display:inline-block; width:190px; padding:3px;}
#notifMailOptions>div		{margin-left:10px; margin-top:8px;}


/*RESPONSIVE FANCYBOX (440px)*/
@media screen and (max-width:440px){
	.objMenuLabel[for='objMenuMain']	{min-width:80px!important;}
	.objMenuLabel img					{display:none;}
	.vSpaceTable						{min-width:300px;}
	.vSpaceTitle>div:not(:first-child)	{width:60px;}/*colonne des checkboxes*/
}
</style>


<?php
////	INITIALISE L'EDITEUR HTML D'UN CHAMP (description ou autre) ?
if($curObj::htmlEditorField!==null)  {echo CtrlMisc::initHtmlEditor($curObj::htmlEditorField);}

////	CONTENU DES MENUS
if(!empty($accessRightMenu) || !empty($attachedFiles) || !empty($moreOptions))
{
	////	ONGLETS DES MENUS
	echo "<div id='objMenuLabels' class='noSelect'>";
		if(!empty($accessRightMenu))	{echo "<div class='objMenuLabel' for='objMenuMain'><img src='app/img/edit.png'> ".$accessRightMenuLabel."</div>";}
		if(!empty($attachedFiles))		{echo "<div class='objMenuLabel ".(!empty($attachedFilesList)?"sLinkSelect":null)."' for='objMenuAttachedFiles'><img src='app/img/editAttachment.png'> ".Txt::trad("EDIT_attachedFile")."</div>";}
		if(!empty($moreOptions)){
			echo "<div class='objMenuLabel' for='objMenuMoreOptions'>";
				if(!empty($notifMail))	{echo "<span> <img src='app/img/editNotif.png'> ".Txt::trad("EDIT_notifMail")." &nbsp;</span>";}
				if(!empty($shortcut))	{echo "<span title=\"".Txt::trad("EDIT_shortcutInfo")."\" ".(!empty($shortcutChecked)?"class='sLinkSelect'":null)."><img src='app/img/shortcut.png'> ".Txt::trad("EDIT_shortcut")." &nbsp;</span>";}
			echo "</div>";
		}
	echo "</div>";

	////	OPTIONS D'EDITION
	echo "<div id='objMenuBlocks' class='lightboxBlock'>";

		////	MENU PRINCIPAL : DROITS D'ACCES (OBJETS INDEPENDANTS)
		if(!empty($accessRightMenu))
		{
			echo "<div id='objMenuMain'>";
				//DROIT D'ACCES DES BLOCK D'ESPACES
				foreach($spacesAccessRight as $spaceCpt=>$tmpSpace)
				{
					//BLOCK DE L'ESPACE
					echo "<div id=\"spaceTable".$tmpSpace->_id."\">";
						//TABLEAU D'UN ESPACE
						echo "<div class='vSpaceTable noSelect'>";
							//ENTETE DE L'ESPACE
							echo "<div class='vSpaceTitle'>
									<div class='vSpaceLabel' title=\"".$tmpSpace->description."\">".($tmpSpace->isCurSpace()?'&nbsp;':Txt::reduce($tmpSpace->name,35))."</div>
									<div class='vSpaceRead noTooltip' title=\"".Txt::trad("readInfos")."\">".Txt::trad("accessRead")."</div>
									<div class='vSpaceWriteLimit noTooltip' title=\"".$writeReadLimitInfos."\">".Txt::trad("accessWriteLimit")."</div>
									<div class='vSpaceWrite noTooltip' title=\"".Txt::trad("writeInfos")."\">".Txt::trad("accessWrite")."</div>
								  </div>";
							//TARGETS DE L'ESPACE (id des checkboxes deja dans "boxProp"!)
							$tmpSpace->hiddenSelection=false;
							foreach($tmpSpace->targetLines as $targetLine)
							{
								$targetLine["classHideUser"]=(empty($targetLine["isChecked"]) && $tmpSpace->isCurSpace())  ?  "vSpaceHideSelection"  :  null;
								if(!empty($targetLine["classHideUser"]))  {$tmpSpace->hiddenSelection=true;}
								$targetLine["tooltip"]=(!empty($targetLine["tooltip"]))  ?  "title=\"".$targetLine["tooltip"]."\""  :  null;
								$targetLine["icon"]=(!empty($targetLine["icon"]))  ?  "<img src='app/img/".$targetLine["icon"]."'>"  :  null;
								echo "<div class='vSpaceTarget sTableRow ".$targetLine["classHideUser"]."' id=\"targetLine".$targetLine["targetId"]."\">
										<div class='vSpaceLabel' id=\"".$targetLine["targetId"]."\" ".$targetLine["tooltip"].">".$targetLine["icon"]." &nbsp;".$targetLine["label"]."</div>
										<div class='vSpaceRead noTooltip' title=\"".Txt::trad("readInfos")."\"><input type='checkbox' name='objectRight[]' ".$targetLine["boxProp"]["1"]."></div>
										<div class='vSpaceWriteLimit noTooltip' title=\"".$writeReadLimitInfos."\"><input type='checkbox' name='objectRight[]' ".$targetLine["boxProp"]["1.5"]."></div>
										<div class='vSpaceWrite noTooltip' title=\"".Txt::trad("writeInfos")."\"><input type='checkbox' name='objectRight[]' ".$targetLine["boxProp"]["2"]."></div>
									  </div>";
							}
						echo "</div>";
						//BOUTON "AFFICHER TOUS LES UTILISATEURS"
						if($tmpSpace->hiddenSelection==true)  {echo "<div class='vShowAllSpaceUsers' for='#spaceTable".$tmpSpace->_id."'>".Txt::trad("EDIT_showAllSpaceUsers")." <img src='app/img/arrowBottom.png'></div>";}
					//BLOCK DE L'ESPACE
					echo "</div>";
				}
				// "AFFICHER TOUS LES ESPACES"  /  ETENDRE LES DROITS AUX SOUS-DOSSIERS
				if(count($spacesAccessRight)>1)  {echo "<div class='vShowAllSpaces' onclick=\"$(this).hide();$('[id^=spaceTable]').fadeIn();\"><hr>".Txt::trad("EDIT_mySpaces")." <img src='app/img/arrowBottom.png'></div>";}
				if(!empty($extendToSubfolders))  {echo "<div class='vExtendToSubfolders'><hr><label for='extendToSubfolders' title=\"".Txt::trad("EDIT_accessRightSubFolders_info")."\">".Txt::trad("EDIT_accessRightSubFolders")."</label><input type='checkbox' name='extendToSubfolders' id='extendToSubfolders' value='1'></div>";}
			echo "</div>";
		}

		////	MENU "ATTACHED FILES" (FICHIERS JOINTS)
		if(!empty($attachedFiles))
		{
			echo "<div id='objMenuAttachedFiles'>";
					//Infos
					echo "<div id='addAttachedFileLabel'><img src='app/img/attachment.png'> ".Txt::trad("EDIT_attachedFileInfo")." :</div>";
					//Fichiers à ajouter (10 maxi)
					for($cptFile=1; $cptFile<=10; $cptFile++)
					{
						$attachedFileOptions=null;
						if($curObj::htmlEditorField!==null){
							$attachedFileOptions="<span class='addAttachedFileInsertOpt'>
													<input type='checkbox' name=\"addAttachedFileInsert".$cptFile."\" id=\"addAttachedFileInsert".$cptFile."\" value='1'>
													<label for=\"addAttachedFileInsert".$cptFile."\" title=\"".Txt::trad("EDIT_attachedFileInsertInfo")."\" class='abbr'>".Txt::trad("EDIT_attachedFileInsert")."</label>
												  </span>";
						}
						echo "<div id=\"addAttachedFileDiv".$cptFile."\"><input type='file' name=\"addAttachedFile".$cptFile."\">".$attachedFileOptions."</div>";
					}
					//Fichiers déjà enregistrés
					if(!empty($attachedFilesList))
					{
						echo "<hr>";
						foreach($attachedFilesList as $tmpFile){
							$fileOptions=" &nbsp;<img src='app/img/delete.png' class='sLink' title=\"".Txt::trad("delete")."\" onclick=\"deleteAttachedFile(".$tmpFile["_id"].");\">";
							if($curObj::htmlEditorField!==null && File::controlType("attachedFileInsert",$tmpFile["name"]))  {$fileOptions.=" &nbsp;<img src='app/img/editAttachmentInsertText.png' title=\"".Txt::trad("EDIT_attachedFileInsertInfo")."\" ".MdlObject::attachedFileInsert($tmpFile["_id"],true)." class='sLink'>";}
							echo "<div id=\"menuAttachedFile".$tmpFile["_id"]."\"><img src='app/img/dot.png'> ".$tmpFile["name"]." ".$fileOptions."</div>";
						}
					}
			echo "</div>";
		}
		
		////	MENU "MORE OPTIONS" (NOTIF / SHORTCUT)
		if(!empty($moreOptions))
		{
			echo "<div id='objMenuMoreOptions'>";
			////	MENU "NOTIF MAIL" (notifications par mail)
			if(!empty($notifMail))
			{
				//CHECKBOX PRINCIPAL & OPTIONS
				echo "<img src='app/img/editNotif.png'>&nbsp;<input type='checkbox' name='notifMail' id='boxNotifMail' value='1' onChange=\"$('#notifMailOptions').slideToggle();\"> <label for='boxNotifMail' title=\"".Txt::trad("EDIT_notifMailInfo")."\">".Txt::trad("EDIT_notifMail2")."</label>
					<div id='notifMailOptions'>";
					//JOINDRE L'OBJET FICHIER A LA NOTIFICATION ?
					if($curObj::objectType=="file" && $curObj->_id==0)  {echo "<div><img src='app/img/dependency.png'><input type='checkbox' name='notifMailAddFiles' id='boxNotifMailAddFiles' value='1'><label for='boxNotifMailAddFiles' title=\"".Txt::trad("FILE_fileSizeLimit")." ".File::displaySize(File::mailMaxFilesSize)."\">".Txt::trad("EDIT_notifMailAddFiles")."</label></div>";}
					//MONTRER LES DESTINATAIRES DANS LE MESSAGE  /  ACCUSE DE RECEPTION  /  SPECIFIER LES DESTINATAIRES
					echo "<div><img src='app/img/dependency.png'><input type='checkbox' name='hideRecipients' id='boxhideRecipients' value='1'><label for='boxhideRecipients' title=\"".Txt::trad("MAIL_hideRecipientsInfo")."\">".Txt::trad("MAIL_hideRecipients")."</label></div>
						  <div><img src='app/img/dependency.png'><input type='checkbox' name='receptionNotif' id='boxReceptionNotif' value='1'><label for='boxReceptionNotif' title=\"".Txt::trad("MAIL_receptionNotifInfo")."\">".Txt::trad("MAIL_receptionNotif")."</label></div>
						  <div><img src='app/img/dependency.png'><input type='checkbox' name='notifMailSelect' id='boxNotifMailSelect' value='1' onclick=\"$('#notifMailSelectList').slideToggle();\"><label for='boxNotifMailSelect'>".Txt::trad("EDIT_notifMailSelect")."</label></div>";
					//LISTE DETAILLE DES UTILISATEURS (masque d'abord les users absent de l'espace courant)
					echo "<div id='notifMailSelectList'>";
						foreach($notifMailUsers as $tmpUser)  {echo "<div id=\"divNotifMailUser".$tmpUser->_id."\" ".(!in_array($tmpUser->_id,$curSpaceUsersIds)?"style='display:none'":null)."><input type='checkbox' name='notifMailUsers[]' value=\"".$tmpUser->_id."\" id=\"boxNotifMailUsers".$tmpUser->_id."\"> <label for=\"boxNotifMailUsers".$tmpUser->_id."\" title=\"".$tmpUser->mail."\">".$tmpUser->getLabel()."</label></div>";}
						if(count($notifMailUsers)>count($curSpaceUsersIds))  {echo "<br><div onclick=\"$('[id^=divNotifMailUser]').fadeIn();$(this).fadeOut();\" class='sLink'>".Txt::trad("EDIT_notifMailMoreUsers")."</div>";}
					echo "</div>";
				echo "</div>";
			}
			////	MENU "SHORTCUT" (raccourci)
			if(!empty($shortcut))
				{echo "<br><br><img src='app/img/shortcut.png'>&nbsp; <input type='checkbox' name='shortcut' id='boxShortcut' value='1' ".$shortcutChecked."><label for='boxShortcut'>".Txt::trad("EDIT_shortcutInfo")."</label>";}
			echo "</div>";
		}
	//OPTIONS D'EDITION: FIN
	echo "</div>";
}

////	BOUTON DE VALIDATION ET INPUTS HIDDEN ("ctrl"/"action"/etc)  &&  "_idContainer"  &&  ICONE "LOADING"
echo Txt::submit();
if(!empty($curObj->_idContainer))  {echo "<input type='hidden' name='_idContainer' value=\"".$curObj->_idContainer ."\">";}
echo "<div class='loadingImg'><img src='app/img/loading.gif'></div>";