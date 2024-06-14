<script>
$(function(){
	////	Logo en page de connexion
	$("#logoConnectSelect").on("change",function(){
		$("#logoConnectImg,#logoConnectFile").hide();
		if(this.value=="<?= Ctrl::$agora->logoConnect ?>")	{$("#logoConnectImg").show();}			//Affiche le logo spécifique
		else if(this.value=="modify")						{$("#logoConnectFile").show();}			//Affiche l'input "file"
	}).trigger("change");

	////	Logo du footer
	$("#logoSelect").on("change",function(){
		$("#logoImg,#logoFile,#logoUrl").hide();
		if(this.value=="<?= Ctrl::$agora->logo ?>")	{$("#logoImg").show();}		//Affiche le logo spécifique / par défaut
		if(this.value=="modify")										{$("#logoFile").show();}	//Affiche l'input "file"
		if(this.value!="")												{$("#logoUrl").show();}		//Affiche l'input "url" du logo
	}).trigger("change");

	////	Vérif le type du fichier
	$("#logoFile,#logoConnectFile,#wallpaperFile").on("change",function(){
		if(/\.(jpg|jpeg|png)/i.test(this.value)==false)  {notify("<?= Txt::trad("AGORA_wallpaperLogoError") ?>");}
	});

	////	Affiche du "mapApiKeyDiv" si "mapTool"=="gmap"  &&   Affiche du "gIdentityClientId" si "gIdentity" est activé  ("trigger()" initie l'affichage)
	$("select[name='mapTool']").on("change",function(){  this.value=="gmap" ? $("#mapApiKeyDiv").fadeIn() : $("#mapApiKeyDiv").fadeOut();  }).trigger("change");
	$("select[name='gIdentity']").on("change",function(){  this.value=="1" ? $("#gIdentityClientIdDiv").fadeIn() : $("#gIdentityClientIdDiv").fadeOut();  }).trigger("change");

	////	Controle du formulaire
	$("#mainForm").submit(function(){
		//Contrôle le nom de l'espace
		if($("input[name='name']").isEmpty())   {notify("<?= Txt::trad("fillFieldsForm") ?>");  return false;}
		//Contrôle de l'espace disque, l'url de serveur de visio, le mapApiKey et gIdentityClientId
		<?php if(Req::isHost()==false){ ?>
		if(isNaN($("#limite_espace_disque").val()))   																		{notify("<?= Txt::trad("AGORA_diskSpaceInvalid") ?>");  return false;}	//doit être un nombre
		if($("input[name='visioHost']").isEmpty()==false && /^https/.test($("input[name='visioHost']").val())==false)		{notify("<?= Txt::trad("AGORA_visioHostInvalid") ?>");  return false;}	//doit commencer par "https"
		if($("input[name='visioHostAlt']").isEmpty()==false && /^https/.test($("input[name='visioHostAlt']").val())==false)	{notify("<?= Txt::trad("AGORA_visioHostInvalid") ?>");  return false;}	//doit commencer par "https"
		if($("select[name='mapTool']").val()=="gmap" && $("input[name='mapApiKey']").isEmpty())								{notify("<?= Txt::trad("AGORA_mapApiKeyInvalid") ?>");  return false;}	//Doit spécifier un "API Key"
		if($("select[name='gIdentity']").val()=="1" && $("input[name='gIdentityClientId']").isEmpty())						{notify("<?= Txt::trad("AGORA_gIdentityKeyInvalid") ?>");  return false;}	//Idem
		<?php } ?>
		return confirm("<?= Txt::trad("AGORA_confirmModif") ?>");
	});
});
</script>


<style>
/*Menu context de gauche*/
#pageModuleMenu .miscContainer	{text-align:center;}/*surcharge*/
#pageModuleMenu button			{width:90%;}
#pageModuleMenu button img		{max-height:25px; margin-left:10px;}
#agoraInfos div					{line-height:35px;}
#backupForm button				{margin:10px 0; width:100%; height:50px; text-align:left;}

/*Formulaire principal*/
.objField>div					{padding:4px 0px 4px 0px;}/*surcharge*/
input[type='radio']+label		{margin-right:20px;}/*espace entre chaque input + label*/
#logoImg, #logoConnectImg		{margin:0px 10px; max-width:80px; max-height:40px;}/*surcharge ".objField img"*/
#logoUrl						{margin-top:10px;}
#limite_espace_disque			{width:40px;}
#smtpConfig, #ldapConfig		{padding:10px; margin-bottom:20px;}
</style>


<div id="pageCenter">

	<div id="pageModuleMenu">
		<!--VERSIONS D'AGORA / PHP / MYSQL  &&  FONCTIONS PHP DÉSACTIVÉES-->
		<div class="miscContainer" id="agoraInfos">
			<div>Agora-Project / Omnispace version <?= Req::appVersion() ?></div>
			<div><?= Txt::trad("AGORA_dateUpdate")." ".Txt::dateLabel(Ctrl::$agora->dateUpdateDb,"dateMini") ?></div>
			<div><a onclick="lightboxOpen('docs/CHANGELOG.txt')"><button><?= Txt::trad("AGORA_Changelog") ?></button></a></div>
			<div>PHP <?= str_replace(strstr(phpversion(),"-"),"",phpversion()) ?> &nbsp;&nbsp; <?= Db::dbVersion() ?></div>
			<?php if(!function_exists("mail")){ ?><div ><img src="app/img/delete.png"> <?= Txt::trad("AGORA_funcMailDisabled") ?></div><?php } ?>
			<?php if(!function_exists("imagecreatetruecolor")){ ?><div><img src="app/img/delete.png"> <?= Txt::trad("AGORA_funcImgDisabled") ?></div><?php } ?>
			<?php if(!function_exists("ldap_connect")){ ?><div><img src="app/img/delete.png"> <?= Txt::trad("AGORA_ldapDisabled") ?></div><?php } ?>
		</div>
		<!--OPTIONS DE BACKUP-->
		<?php if(Req::isMobile()==false){ ?>
		<form action="index.php" method="post" id="backupForm" class="miscContainer" onsubmit="return confirm('<?= Txt::trad('AGORA_backupConfirm',true) ?>')">
			<button type="submit" name="typeBackup" value="all" title="<?= Txt::trad("AGORA_backupFullTooltip") ?>"><img src="app/img/download.png"> <?= Txt::trad("AGORA_backupFull") ?></button>
			<button type="submit" name="typeBackup" value="db" title="<?= Txt::trad("AGORA_backupDbTooltip") ?>"><img src="app/img/download.png"> <?= Txt::trad("AGORA_backupDb") ?></button>
			<input type="hidden" name="ctrl" value="agora">
			<input type="hidden" name="action" value="getBackup">
		</form>
		<?php } ?>
	</div>

	<div id="pageCenterContent">
		<form action="index.php" method="post" id="mainForm" class="miscContainer" enctype="multipart/form-data">

			<!--NOM DE L'ESPACE PRINCIPAL-->
			<div class="objField" title="<?= Txt::trad("AGORA_nameTooltip") ?>">
				<div><?= Txt::trad("AGORA_name") ?></div>
				<div><input type="text" name="name" value="<?= Ctrl::$agora->name ?>"></div>
			</div>

			<!--DESCRIPTION DE L'ESPACE EN PAGE DE CONNEXION-->
			<div class="objField">
				<div><?= Txt::trad("AGORA_description") ?></div>
				<div><input type="text" name="description" value="<?= Ctrl::$agora->description ?>"></div>
			</div>

			<!--LOGO EN PAGE DE CONNEXION-->
			<div class="objField" title="<?= Txt::trad("AGORA_logoConnectTooltip") ?>">
				<div><?= Txt::trad("AGORA_logoConnect") ?></div>
				<div>
					<?php
					//Options "conserver" / "changer" / "supprimer"
					if(!empty(Ctrl::$agora->logoConnect)){
						echo '<select name="logoConnect" id="logoConnectSelect">
								<option value="'.Ctrl::$agora->logoConnect.'">'.Txt::trad("keepImg").'</option>
								<option value="modify">'.Txt::trad("changeImg").'</option>
								<option value="">'.Txt::trad("delete").'</option>
							  </select>';
					}
					?>
					<input type="file" name="logoConnectFile" id="logoConnectFile">
					<img src="<?= Ctrl::$agora->pathLogoConnect() ?>" id="logoConnectImg">
				</div>
			</div>

			<!--LOGO PRINCIPAL DU FOOTER (DROITE)-->
			<div class="objField">
				<div><?= Txt::trad("AGORA_logo") ?></div>
				<div>
					<select name="logo" id="logoSelect">
						<?php
						//Options "conserver" / "changer" / "par défaut"
						if(!empty(Ctrl::$agora->logo))	{echo '<option value="'.Ctrl::$agora->logo.'">'.Txt::trad("keepImg").'</option>';}
						echo '<option value="modify">'.Txt::trad("changeImg").'</option>
							  <option value="" '.(empty(Ctrl::$agora->logo)?'selected':null).'>'.Txt::trad("byDefault").'</option>';
						?>
					</select>
					<input type="file" name="logoFile" id="logoFile">
					<img src="<?= Ctrl::$agora->pathLogoFooter() ?>" id="logoImg">
					<input type="text" name="logoUrl" id="logoUrl" value="<?= Ctrl::$agora->logoUrl ?>" placeholder="<?= Txt::trad("AGORA_logoUrl") ?>">
				</div>
			</div>

			<!--TEXTE DU FOOTER (GAUCHE)-->
			<div class="objField">
				<div><?= Txt::trad("AGORA_footerHtml") ?></div>
				<div><input type="text" name="footerHtml" value="<?= Ctrl::$agora->footerHtml ?>"></div>
			</div>

			<!--FOND D'ECRAN / WALLPAPER-->
			<div class="objField">
				<div><?= Txt::trad("wallpaper") ?></div>
				<div><?= CtrlMisc::menuWallpaper(Ctrl::$agora->wallpaper) ?></div>
			</div>

			<!--COULEUR DE L'INTERFACE-->
			<div class="objField">
				<div><?= Txt::trad("AGORA_skin") ?></div>
				<div>
				<?php
					$tabRadios=[ ["value"=>"white","trad"=>"AGORA_white"], ["value"=>"black","trad"=>"AGORA_black"] ];
					echo Txt::radioButtons("skin", Ctrl::$agora->skin, $tabRadios);
					?>
				</div>
			</div>

			<!--AFFICHAGE PAR DEFAUT DES DOSSIERS (BLOCK/LINE)-->
			<div class="objField">
				<div><?= Txt::trad("AGORA_folderDisplayMode") ?></div>
				<div>
					<?php
					$tabRadios=[ ["value"=>"block","trad"=>"displayMode_block","img"=>"displayBlock.png"], ["value"=>"line","trad"=>"displayMode_line","img"=>"displayLine.png"] ];
					echo Txt::radioButtons("folderDisplayMode", Ctrl::$agora->folderDisplayMode, $tabRadios);
					?>
				</div>
			</div>

			<!--AFFICHAGE DU NOM DES MODULES-->
			<div class="objField">
				<div><?= Txt::trad("AGORA_moduleLabelDisplay") ?></div>
				<div>
					<?php
					$tabRadios=[ ["value"=>"1","trad"=>"show","img"=>"displayShow.png"], ["value"=>null,"trad"=>"hide","img"=>"displayHide.png"] ];
					echo Txt::radioButtons("moduleLabelDisplay", Ctrl::$agora->moduleLabelDisplay, $tabRadios);
					?>
				</div>
			</div>

			<!--MESSENGER ACTIVE/DESACTIVE-->
			<div class="objField">
				<div><img src="app/img/messenger.png"><?= Txt::trad("AGORA_messengerDisplay") ?></div>
				<div>
					<?php
					$tabRadios=[ ["value"=>"1","trad"=>"show","img"=>"displayShow.png"], ["value"=>null,"trad"=>"hide","img"=>"displayHide.png"] ];
					echo Txt::radioButtons("messengerDisplay", Ctrl::$agora->messengerDisplay, $tabRadios);
					?>
				</div>
			</div>

			<!--LIKES ACTIVE/DESACTIVE-->
			<div class="objField">
				<div><img src="app/img/usersLike.png"><?= Txt::trad("AGORA_usersLikeLabel") ?></div>
				<div>
					<?php
					$tabRadios=[ ["value"=>"1","trad"=>"show","img"=>"displayShow.png"], ["value"=>null,"trad"=>"hide","img"=>"displayHide.png"] ];
					echo Txt::radioButtons("usersLike", Ctrl::$agora->usersLike, $tabRadios);
					?>
				</div>
			</div>

			<!--COMMENTAIRES ACTIVE/DESACTIVE-->
			<div class="objField">
				<div><img src="app/img/usersComment.png"><?= Txt::trad("AGORA_usersCommentLabel") ?></div>
				<div>
					<?php
					$tabRadios=[ ["value"=>"1","trad"=>"show","img"=>"displayShow.png"], ["value"=>null,"trad"=>"hide","img"=>"displayHide.png"] ];
					echo Txt::radioButtons("usersComment", Ctrl::$agora->usersComment, $tabRadios);
					?>
				</div>
			</div>

			<!--AFFICHAGE DES EMAILS DES UTILISATEURS-->
			<div class="objField" title="<?= Txt::trad("AGORA_userMailDisplayTooltip") ?>">
				<div><img src="app/img/mail.png"><?= Txt::trad("AGORA_userMailDisplay") ?></div>
				<div>
					<?php
					$tabRadios=[ ["value"=>"1","trad"=>"show","img"=>"displayShow.png"], ["value"=>null,"trad"=>"hide","img"=>"displayHide.png"] ];
					echo Txt::radioButtons("userMailDisplay", Ctrl::$agora->userMailDisplay, $tabRadios);
					?>
				</div>
			</div>

			<!--TRI DES USERS/CONTACT : NOM OU PRENOM-->
			<div class="objField">
				<div><img src="app/img/user/iconSmall.png"><?= Txt::trad("AGORA_personsSort") ?></div>
				<div>
					<?php
					$tabRadios=[ ["value"=>"firstName","trad"=>"firstName"], ["value"=>"name","trad"=>"name"] ];
					echo Txt::radioButtons("personsSort", Ctrl::$agora->personsSort, $tabRadios);
					?>
				</div>
			</div>

<hr><!--SEPARATEUR-->

			<!--LANG-->
			<div class="objField">
				<div><img src="app/img/earth.png"><?= Txt::trad("AGORA_lang") ?></div>
				<div><?= Txt::menuTrad("agora",Ctrl::$agora->lang) ?></div>
			</div>

			<!--TIMEZONE-->
			<div class="objField">
				<div><img src="app/img/earth.png"><?= Txt::trad("AGORA_timezone") ?></div>
				<div>
					<select name="timezone">
						<?php foreach(Tool::$tabTimezones as $tmpLabel=>$timezone){
							$tmpSelected=($timezone==Tool::$tabTimezones[Ctrl::$curTimezone])  ?  "selected"  :  null;
							echo '<option value="'.$timezone.'" '.$tmpSelected.'>[gmt '.($timezone>0?'+':null).$timezone.'] '.$tmpLabel.'</option>';
						} ?>
					</select>
				</div>
			</div>

			<!--LOGS TIMEOUT-->
			<div class="objField" title="<?= Txt::trad("AGORA_logsTimeOutTooltip") ?>">
				<div><img src="app/img/log.png"><?= Txt::trad("AGORA_logsTimeOut") ?></div>
				<div>
					<select name="logsTimeOut">
						<?php foreach([0,30,120,360,720] as $tmpTimeOut){
							$tmpSelected=($tmpTimeOut==Ctrl::$agora->logsTimeOut)  ?  "selected"  :  null;
							echo "<option value='".$tmpTimeOut."' ".$tmpSelected.">".$tmpTimeOut."</option>";
						} ?>
					</select>
					<?= Txt::trad("days") ?>
				</div>
			</div>

			<!--DISK SPACE (AUTO-HEBERGEMENT)-->
			<?php if(Req::isHost()==false){ ?>
			<div class="objField">
				<div><img src="app/img/diskSpace.png"><?= Txt::trad("AGORA_diskSpaceLimit") ?></div>
				<div><input type="text" name="limite_espace_disque" id="limite_espace_disque" value="<?= round((limite_espace_disque/File::sizeGo),2) ?>"> <?= Txt::trad("gigaOctet")?></div>
			</div>
			<?php } ?>

<hr><!--SEPARATEUR-->

			<!--SERVEURS JITSI (AUTO-HEBERGEMENT)-->
			<?php if(Req::isHost()==false){ ?>
			<div class="objField" title="<?= Txt::trad("AGORA_visioHostTooltip") ?>">
				<div><img src="app/img/visio.png"><?= Txt::trad("AGORA_visioHost") ?></div>
				<div><input type="text" name="visioHost" value="<?= Ctrl::$agora->visioHost ?>"></div>
			</div>
			<div class="objField" title="<?= Txt::trad("AGORA_visioHostAltTooltip") ?>">
				<div><img src="app/img/visio.png"><?= Txt::trad("AGORA_visioHostAlt") ?></div>
				<div><input type="text" name="visioHostAlt" value="<?= Ctrl::$agora->visioHostAlt ?>"></div>
			</div>
			<?php } ?>

			<!--CARTOGRAPHIE : OPENSTREETMAP OU GOOGLE MAP-->
			<div class="objField" title="<?= Txt::trad("AGORA_mapToolTooltip") ?>">
				<div><img src="app/img/map.png"><?= Txt::trad("AGORA_mapTool") ?></div>
				<div>
					<select name="mapTool">
						<option value="leaflet">OpenStreetMap / Leaflet</option>
						<option value="gmap" <?= Ctrl::$agora->mapTool=="gmap"?"selected":null ?>>Google Map</option>
					</select>
				</div>
			</div>

			<!--GOOGLE MAP APIKEY (AUTO-HEBERGEMENT)-->
			<?php if(Req::isHost()==false){ ?>
				<div class="objField" id="mapApiKeyDiv" title="<?= Txt::trad("AGORA_mapApiKeyTooltip") ?>">
					<div><img src="app/img/map.png"><?= Txt::trad("AGORA_mapApiKey") ?></div>
					<div><input type="text" name="mapApiKey" value="<?= Ctrl::$agora->mapApiKey ?>"></div>
				</div>
			<?php } ?>

			<!--GOOGLE SIGNIN ENABLED/DISABLED-->
			<div class="objField" title="<?= Txt::trad("AGORA_gIdentityTooltip") ?>">
				<div><img src="app/img/google.png"><?= Txt::trad("AGORA_gIdentity") ?></div>
				<div>
					<?php
					$tabRadios=[ ["value"=>"1","trad"=>"show","img"=>"displayShow.png"], ["value"=>null,"trad"=>"hide","img"=>"displayHide.png"] ];
					echo Txt::radioButtons("gIdentity", Ctrl::$agora->gIdentity, $tabRadios);
					?>
				</div>
			</div>

			<!--GOOGLE SIGNIN "CLIENT ID" & PEOPLE "API KEY" (AUTO-HEBERGEMENT)-->
			<?php if(Req::isHost()==false){ ?>
				<div class="objField" id="gIdentityClientIdDiv" title="<?= Txt::trad("AGORA_gIdentityClientIdTooltip") ?>">
					<div><img src="app/img/google.png"><?= Txt::trad("AGORA_gIdentityClientId") ?></div>
					<div><input type="text" name="gIdentityClientId" value="<?= Ctrl::$agora->gIdentityClientId ?>"></div>
				</div>
				<div class="objField" title="<?= Txt::trad("AGORA_gPeopleApiKeyTooltip") ?>">
					<div><img src="app/img/google.png"><?= Txt::trad("AGORA_gPeopleApiKey") ?></div>
					<div><input type="text" name="gPeopleApiKey" value="<?= Ctrl::$agora->gPeopleApiKey ?>"></div>
				</div>
			<?php } ?>

			<!--PARAMETRAGE SMTP POUR L'ENVOI DE MAILS (AUTO-HEBERGEMENT)-->
			<?php if(Req::isHost()==false){ ?>
				<div class="objField" onclick="$('#smtpConfig').fadeToggle()">
					<div><img src="app/img/postMessage.png"> <?= Txt::trad("AGORA_smtpLabel") ?> <img src="app/img/arrowBottom.png"></div>
				</div>
				<fieldset id="smtpConfig" <?= empty(Ctrl::$agora->smtpHost)?"style='display:none'":null ?>>
					<div class="objField">
						<div><?= Txt::trad("AGORA_smtpHost") ?></div>
						<div><input type="text" name="smtpHost" value="<?= Ctrl::$agora->smtpHost ?>"></div>
					</div>
					<div class="objField" title="<?= Txt::trad("AGORA_smtpPortTooltip") ?>">
						<div><?= Txt::trad("AGORA_smtpPort") ?></div>
						<div><input type="text" name="smtpPort" value="<?= Ctrl::$agora->smtpPort ?>"></div>
					</div>
					<div class="objField" title="<?= Txt::trad("AGORA_smtpSecureTooltip") ?>">
						<div><?= Txt::trad("AGORA_smtpSecure") ?></div>
						<div><input type="text" name="smtpSecure" value="<?= Ctrl::$agora->smtpSecure ?>"></div>
					</div>
					<div class="objField">
						<div><?= Txt::trad("AGORA_smtpUsername") ?></div>
						<div><input type="text" name="smtpUsername" value="<?= Ctrl::$agora->smtpUsername ?>"></div>
					</div>
					<div class="objField">
						<div><?= Txt::trad("AGORA_smtpPass") ?></div>
						<div><input type="password" name="smtpPass" value="<?= Ctrl::$agora->smtpPass ?>"></div>
					</div>
					<div class="objField">
						<div><?= Txt::trad("AGORA_sendmailFrom") ?></div>
						<div><input type="text" name="sendmailFrom" value="<?= Ctrl::$agora->sendmailFrom ?>" placeholder="<?= Txt::trad("AGORA_sendmailFromPlaceholder") ?>"></div>
					</div>
				</fieldset>
			<?php } ?>

			<!--PARAMETRAGE LDAP-->
			<?php if(function_exists("ldap_connect")){ ?>
				<div class="objField" onclick="$('#ldapConfig').fadeToggle()" title="<?= Txt::trad("AGORA_ldapLabelTooltip") ?>">
					<div><img src="app/img/user/ldap.png"> <?= Txt::trad("AGORA_ldapLabel") ?> <img src="app/img/arrowBottom.png"></div>
				</div>
				<fieldset id="ldapConfig" <?= empty(Ctrl::$agora->ldap_server)?"style='display:none'":null ?>>
					<div class="objField" title="<?= Txt::trad("AGORA_ldapUriTooltip") ?>">
						<div><?= Txt::trad("AGORA_ldapUri") ?></div>
						<div><input type="text" name="ldap_server" value="<?= Ctrl::$agora->ldap_server ?>"></div>
					</div>
					<div class="objField" title="<?= Txt::trad("AGORA_ldapPortTooltip") ?>">
						<div><?= Txt::trad("AGORA_ldapPort") ?></div>
						<div><input type="text" name="ldap_server_port" value="<?= Ctrl::$agora->ldap_server_port ?>"></div>
					</div>
					<div class="objField" title="<?= Txt::trad("AGORA_ldapLoginTooltip") ?>">
						<div><?= Txt::trad("AGORA_ldapLogin") ?></div>
						<div><input type="text" name="ldap_admin_login" value="<?= Ctrl::$agora->ldap_admin_login ?>"></div>
					</div>
					<div class="objField">
						<div><?= Txt::trad("AGORA_ldapPass") ?></div>
						<div><input type="password" name="ldap_admin_pass" value="<?= Ctrl::$agora->ldap_admin_pass ?>"></div>
					</div>
					<div class="objField" title="<?= Txt::trad("AGORA_ldapDnTooltip") ?>">
						<div><?= Txt::trad("AGORA_ldapDn") ?></div>
						<div><input type="text" name="ldap_base_dn" value="<?= Ctrl::$agora->ldap_base_dn ?>"></div>
					</div>
				</fieldset>
			<?php } ?>

			<!--VALIDATION DU FORMULAIRE-->
			<?= '<hr>'.Txt::submitButton() ?>
		</form>
	</div>
</div>