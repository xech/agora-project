<script>
ready(function(){
	/********************************************************************************************************
 	*	RECUP UN BACKUP
	*******************************************************************************************/
	$(".vButtonBackup").on("click",function(){
		confirmRedir("?ctrl=agora&action=getBackup&typeBackup="+$(this).attr("data-typeBackup"), "<?= Txt::trad("AGORA_backupConfirm") ?>");
	});

	/********************************************************************************************************
	 *	LOGO EN PAGE DE CONNEXION
	 ********************************************************************************************************/
	$("#logoConnectSelect").on("change",function(){
		$("#logoConnectImg,#logoConnectFile").hide();
		if(this.value=="<?= Ctrl::$agora->logoConnect ?>")	{$("#logoConnectImg").show();}			//Affiche le logo spécifique
		else if(this.value=="modify")						{$("#logoConnectFile").show();}			//Affiche l'input "file"
	}).trigger("change");

	/********************************************************************************************************
	 *	LOGO DU FOOTER
	 ********************************************************************************************************/
	$("#logoSelect").on("change",function(){
		$("#logoImg,#logoFile,#logoUrl").hide();
		if(this.value=="<?= Ctrl::$agora->logo ?>")	{$("#logoImg").show();}		//Affiche le logo spécifique / par défaut
		if(this.value=="modify")										{$("#logoFile").show();}	//Affiche l'input "file"
		if(this.value!="")												{$("#logoUrl").show();}		//Affiche l'input "url" du logo
	}).trigger("change");

	/********************************************************************************************************
	 *	VÉRIF LE TYPE DU FICHIER
	 ********************************************************************************************************/
	$("#logoFile,#logoConnectFile,#wallpaperFile").on("change",function(){
		if(/\.(jpg|jpeg|png)$/i.test(this.value)==false)  {notify("<?= Txt::trad("AGORA_wallpaperLogoError") ?>");}
	});

	/*****************************************************************************************************************************************************************
	 *	"mapTool"=="gmap" > AFFICHE "gApiKeyDiv"  &&  "gIdentity" ACTIVÉ (GOOGLE OAUTH) > AFFICHE "gIdentityClientIdDiv"  &&  TRIGGER "change" > INIT DE LA PAGE
	 *****************************************************************************************************************************************************************/
	$("select[name='mapTool']").on("change",function(){  this.value=="gmap" ? $("#gApiKeyDiv").fadeIn() : $("#gApiKeyDiv").fadeOut();  }).trigger("change");
	$("select[name='gIdentity']").on("change",function(){  this.value=="1" ? $("#gIdentityClientIdDiv").fadeIn() : $("#gIdentityClientIdDiv").fadeOut();  }).trigger("change");

	/********************************************************************************************************
	 *	CONTROLE DU FORMULAIRE
	 ********************************************************************************************************/
	$("#mainForm").on("submit",async function(event){
		event.preventDefault();
		////	Contrôle le nom de l'espace
		if($("input[name='name']").isEmpty())   {notify("<?= Txt::trad("emptyFields") ?>");  return false;}
		////	Contrôle de l'espace disque, l'url de serveur de visio, le gApiKey et gIdentityClientId
		<?php if(Req::isHost()==false){ ?>
		if(isNaN($("#limite_espace_disque").val()))   																	{notify("<?= Txt::trad("AGORA_diskSpaceInvalid") ?>");		return false;}//doit être un nombre
		if($("input[name='visioHost']").notEmpty() && /^https/.test($("input[name='visioHost']").val())==false)			{notify("<?= Txt::trad("AGORA_visioHostInvalid") ?>");		return false;}//doit commencer par "https"
		if($("input[name='visioHostAlt']").notEmpty() && /^https/.test($("input[name='visioHostAlt']").val())==false)	{notify("<?= Txt::trad("AGORA_visioHostInvalid") ?>");		return false;}//Idem
		if($("select[name='mapTool']").val()=="gmap" && $("input[name='gApiKey']").isEmpty())							{notify("<?= Txt::trad("AGORA_gApiKeyInvalid") ?>");		return false;}//Doit avoir un "API Key"
		if($("select[name='gIdentity']").val()=="1" && $("input[name='gIdentityClientId']").isEmpty())					{notify("<?= Txt::trad("AGORA_gOAuthKeyInvalid") ?>");	return false;}//Idem
		<?php } ?>
		////	Valide le formulaire
		if(await confirmAlt("<?= Txt::trad("AGORA_confirmModif") ?>"))  {asyncSubmit(this);}
	});
});
</script>

<style>
/*Menu context de gauche*/
.vAgoraVersion					{text-align:center; margin-top:10px;}
.vButtonLogs, .vButtonBackup	{width:90%; height:45px; margin:15px; text-align:left; border-radius:10px;}
#moduleMenu img					{max-height:25px; margin-right:10px;}
#moduleMenu hr					{margin:20px 0px;}

/*Formulaire principal*/
#vMainFormLabel					{text-align:center;}
input[type='radio']+label		{margin-right:20px;}/*espace entre chaque input + label*/
#logoImg, #logoConnectImg		{margin:0px 10px; max-width:80px; max-height:40px;}/*surcharge ".objField img"*/
#logoUrl						{margin-top:10px;}
#limite_espace_disque			{width:40px;}
#smtpConfig, #ldapConfig		{padding:10px; margin-bottom:20px;}
</style>


<div id="pageCenter">

	<div id="moduleMenu">
		<!--ESPACE DISQUE UTILISÉ-->
		<div class="miscContainer">
			<img src="app/img/diskSpace<?= $diskSpaceAlert==true?'Alert':null ?>.png"> <?= Txt::trad("diskSpaceUsed") ?> : <?= $diskSpacePercent.'% '.Txt::trad("from").' '.File::sizeLabel(limite_espace_disque) ?>
		</div>
		<!--VERSIONS D'AGORA  &&  FONCTIONS PHP DÉSACTIVÉES-->
		<div class="miscContainer">
			<div class="vAgoraVersion" title="PHP <?= phpversion().' - '.Db::dbVersion()?>">Agora-Project version <?= Req::appVersion()?> &nbsp;<img src="app/img/info.png"></div>
			<button class="vButtonLogs" onclick="lightboxOpen('docs/CHANGELOG.txt')"><img src="app/img/info.png"> <?= Txt::trad("AGORA_Changelog") ?></button>
			<?php if(!function_exists("mail")){ ?>					<hr><img src="app/img/info.png"><?= Txt::trad("AGORA_phpMailDisabled") ?><?php } ?>
			<?php if(!function_exists("imagecreatetruecolor")){ ?>	<hr><img src="app/img/info.png"><?= Txt::trad("AGORA_phpGD2Disabled") ?><?php } ?>
			<?php if(!function_exists("ldap_connect")){ ?>			<hr><img src="app/img/info.png"><?= Txt::trad("AGORA_phpLdapDisabled") ?><?php } ?>
		</div>
		<!--VOPTIONS DE BACKUP-->
		<?php if(Req::isMobile()==false){ ?>
		<div class="miscContainer">
			<button class="vButtonBackup" data-typeBackup="all" <?= Txt::tooltip("AGORA_backupFullTooltip") ?> ><img src="app/img/download.png"> <?= Txt::trad("AGORA_backupFull") ?></button>
			<button class="vButtonBackup" data-typeBackup="db"  <?= Txt::tooltip("AGORA_backupDbTooltip") ?> ><img src="app/img/download.png"> <?= Txt::trad("AGORA_backupDb") ?></button>
		</div>
		<?php } ?>
	</div>

	<div id="pageContent">
		<div class="miscContainer">
			<form action="index.php" method="post" id="mainForm" enctype="multipart/form-data">
				<div id="vMainFormLabel"><img src="app/img/settingsGeneral.png"> <?= Txt::trad("AGORA_generalSettings") ?></div>
				<hr>

				<!--NOM DE L'ESPACE PRINCIPAL-->
				<div class="objField" <?= Txt::tooltip("AGORA_nameTooltip") ?> >
					<div><?= Txt::trad("AGORA_name") ?></div>
					<div><input type="text" name="name" value="<?= Ctrl::$agora->name ?>"></div>
				</div>

				<!--DESCRIPTION DE L'ESPACE EN PAGE DE CONNEXION-->
				<div class="objField">
					<div><?= Txt::trad("AGORA_description") ?></div>
					<div><input type="text" name="description" value="<?= Ctrl::$agora->description ?>"></div>
				</div>

				<!--LOGO EN PAGE DE CONNEXION-->
				<div class="objField" <?= Txt::tooltip("AGORA_logoConnectTooltip") ?> >
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
						echo MdlAgora::radioButtons("skin", Ctrl::$agora->skin, $tabRadios);
						?>
					</div>
				</div>

				<!--AFFICHAGE PAR DEFAUT DES DOSSIERS (BLOCK/LINE)-->
				<div class="objField">
					<div><?= Txt::trad("AGORA_folderDisplayMode") ?></div>
					<div>
						<?php
						$tabRadios=[ ["value"=>"block","trad"=>"displayMode_block","img"=>"displayBlock.png"], ["value"=>"line","trad"=>"displayMode_line","img"=>"displayLine.png"] ];
						echo MdlAgora::radioButtons("folderDisplayMode", Ctrl::$agora->folderDisplayMode, $tabRadios);
						?>
					</div>
				</div>

				<!--AFFICHAGE DU NOM DES MODULES-->
				<div class="objField">
					<div><?= Txt::trad("AGORA_moduleLabelDisplay") ?></div>
					<div>
						<?php
						$tabRadios=[ ["value"=>"1","trad"=>"show","img"=>"displayShow.png"], ["value"=>null,"trad"=>"hide","img"=>"displayHide.png"] ];
						echo MdlAgora::radioButtons("moduleLabelDisplay", Ctrl::$agora->moduleLabelDisplay, $tabRadios);
						?>
					</div>
				</div>

				<!--MESSENGER ACTIVE/DESACTIVE-->
				<div class="objField">
					<div><img src="app/img/messenger.png"><?= Txt::trad("AGORA_messengerDisplay") ?></div>
					<div>
						<?php
						$tabRadios=[ ["value"=>"1","trad"=>"show","img"=>"displayShow.png"], ["value"=>null,"trad"=>"hide","img"=>"displayHide.png"] ];
						echo MdlAgora::radioButtons("messengerDisplay", Ctrl::$agora->messengerDisplay, $tabRadios);
						?>
					</div>
				</div>

				<!--LIKES ACTIVE/DESACTIVE-->
				<div class="objField">
					<div><img src="app/img/usersLike.png"><?= Txt::trad("AGORA_usersLikeLabel") ?></div>
					<div>
						<?php
						$tabRadios=[ ["value"=>"1","trad"=>"show","img"=>"displayShow.png"], ["value"=>null,"trad"=>"hide","img"=>"displayHide.png"] ];
						echo MdlAgora::radioButtons("usersLike", Ctrl::$agora->usersLike, $tabRadios);
						?>
					</div>
				</div>

				<!--COMMENTAIRES ACTIVE/DESACTIVE-->
				<div class="objField">
					<div><img src="app/img/usersComment.png"><?= Txt::trad("AGORA_usersCommentLabel") ?></div>
					<div>
						<?php
						$tabRadios=[ ["value"=>"1","trad"=>"show","img"=>"displayShow.png"], ["value"=>null,"trad"=>"hide","img"=>"displayHide.png"] ];
						echo MdlAgora::radioButtons("usersComment", Ctrl::$agora->usersComment, $tabRadios);
						?>
					</div>
				</div>

				<!--AFFICHAGE DES EMAILS DES UTILISATEURS-->
				<div class="objField" <?= Txt::tooltip("AGORA_userMailDisplayTooltip") ?> >
					<div><img src="app/img/mail.png"><?= Txt::trad("AGORA_userMailDisplay") ?></div>
					<div>
						<?php
						$tabRadios=[ ["value"=>"1","trad"=>"show","img"=>"displayShow.png"], ["value"=>null,"trad"=>"hide","img"=>"displayHide.png"] ];
						echo MdlAgora::radioButtons("userMailDisplay", Ctrl::$agora->userMailDisplay, $tabRadios);
						?>
					</div>
				</div>

				<!--TRI DES USERS/CONTACT : NOM OU PRENOM-->
				<div class="objField">
					<div><img src="app/img/user/iconSmall.png"><?= Txt::trad("AGORA_personsSort") ?></div>
					<div>
						<?php
						$tabRadios=[ ["value"=>"firstName","trad"=>"firstName"], ["value"=>"name","trad"=>"name"] ];
						echo MdlAgora::radioButtons("personsSort", Ctrl::$agora->personsSort, $tabRadios);
						?>
					</div>
				</div>

			<hr>

				<!--LANG-->
				<div class="objField">
					<div><img src="app/img/earth.png"><?= Txt::trad("AGORA_lang") ?></div>
					<div><?= MdlAgora::selectTrad("agora",Ctrl::$agora->lang) ?></div>
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
				<div class="objField" <?= Txt::tooltip("AGORA_logsTimeOutTooltip") ?> >
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

			<hr>

				<!--SERVEURS JITSI (AUTO-HEBERGEMENT)-->
				<?php if(Req::isHost()==false){ ?>
				<div class="objField" <?= Txt::tooltip("AGORA_visioHostTooltip") ?> >
					<div><img src="app/img/visio.png"><?= Txt::trad("AGORA_visioHost") ?></div>
					<div><input type="text" name="visioHost" value="<?= Ctrl::$agora->visioHost ?>"></div>
				</div>
				<div class="objField" <?= Txt::tooltip("AGORA_visioHostAltTooltip") ?> >
					<div><img src="app/img/visio.png"><?= Txt::trad("AGORA_visioHostAlt") ?></div>
					<div><input type="text" name="visioHostAlt" value="<?= Ctrl::$agora->visioHostAlt ?>"></div>
				</div>
				<?php } ?>

				<!--CARTOGRAPHIE : OPENSTREETMAP OU GOOGLE MAP-->
				<div class="objField" <?= Txt::tooltip("AGORA_mapToolTooltip") ?> >
					<div><img src="app/img/map.png"><?= Txt::trad("AGORA_mapTool") ?></div>
					<div>
						<select name="mapTool">
							<option value="leaflet">OpenStreetMap</option>
							<option value="gmap" <?= Ctrl::$agora->mapTool=="gmap"?"selected":null ?>>Google Map</option>
						</select>
					</div>
				</div>

				<!--GOOGLE MAP APIKEY (AUTO-HEBERGEMENT)-->
				<?php if(Req::isHost()==false){ ?>
					<div class="objField" id="gApiKeyDiv" <?= Txt::tooltip("AGORA_gApiKeyTooltip") ?> >
						<div><img src="app/img/google.png"><?= Txt::trad("AGORA_gApiKey") ?></div>
						<div><input type="text" name="gApiKey" value="<?= Ctrl::$agora->gApiKey ?>"></div>
					</div>
				<?php } ?>

				<!--GOOGLE OAUTH ENABLED/DISABLED-->
				<div class="objField" <?= Txt::tooltip("AGORA_gOAuthTooltip") ?> >
					<div><img src="app/img/google.png"><?= Txt::trad("AGORA_gOAuth") ?></div>
					<div>
						<?php
						$tabRadios=[ ["value"=>"1","trad"=>"show","img"=>"displayShow.png"], ["value"=>null,"trad"=>"hide","img"=>"displayHide.png"] ];
						echo MdlAgora::radioButtons("gIdentity", Ctrl::$agora->gIdentity, $tabRadios);
						?>
					</div>
				</div>

				<!--GOOGLE OAUTH "CLIENT ID" (AUTO-HEBERGEMENT)-->
				<?php if(Req::isHost()==false){ ?>
					<div class="objField" id="gIdentityClientIdDiv" <?= Txt::tooltip("AGORA_gOAuthClientIdTooltip") ?> >
						<div><img src="app/img/google.png"><?= Txt::trad("AGORA_gOAuthClientId") ?></div>
						<div><input type="text" name="gIdentityClientId" value="<?= Ctrl::$agora->gIdentityClientId ?>"></div>
					</div>
				<?php } ?>

				<!--PARAMETRAGE SMTP POUR L'ENVOI DE MAILS (AUTO-HEBERGEMENT)-->
				<?php if(Req::isHost()==false){ ?>
					<div class="objField" onclick="$('#smtpConfig').slideToggle()">
						<div><img src="app/img/postMessage.png"> <?= Txt::trad("AGORA_smtpLabel") ?> <img src="app/img/arrowBottom.png"></div>
					</div>
					<fieldset id="smtpConfig" <?= empty(Ctrl::$agora->smtpHost)?"style='display:none'":null ?>>
						<div class="objField">
							<div><?= Txt::trad("AGORA_smtpHost") ?></div>
							<div><input type="text" name="smtpHost" value="<?= Ctrl::$agora->smtpHost ?>"></div>
						</div>
						<div class="objField" <?= Txt::tooltip("AGORA_smtpPortTooltip") ?> >
							<div><?= Txt::trad("AGORA_smtpPort") ?></div>
							<div><input type="text" name="smtpPort" value="<?= Ctrl::$agora->smtpPort ?>"></div>
						</div>
						<div class="objField" <?= Txt::tooltip("AGORA_smtpSecureTooltip") ?> >
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
					<div class="objField" onclick="$('#ldapConfig').slideToggle()" <?= Txt::tooltip("AGORA_ldapLabelTooltip") ?> >
						<div><img src="app/img/user/ldap.png"> <?= Txt::trad("AGORA_ldapLabel") ?> <img src="app/img/arrowBottom.png"></div>
					</div>
					<fieldset id="ldapConfig" <?= empty(Ctrl::$agora->ldap_server)?"style='display:none'":null ?>>
						<div class="objField" <?= Txt::tooltip("AGORA_ldapUriTooltip") ?> >
							<div><?= Txt::trad("AGORA_ldapUri") ?></div>
							<div><input type="text" name="ldap_server" value="<?= Ctrl::$agora->ldap_server ?>"></div>
						</div>
						<div class="objField" <?= Txt::tooltip("AGORA_ldapPortTooltip") ?> >
							<div><?= Txt::trad("AGORA_ldapPort") ?></div>
							<div><input type="text" name="ldap_server_port" value="<?= Ctrl::$agora->ldap_server_port ?>"></div>
						</div>
						<div class="objField" <?= Txt::tooltip("AGORA_ldapLoginTooltip") ?> >
							<div><?= Txt::trad("AGORA_ldapLogin") ?></div>
							<div><input type="text" name="ldap_admin_login" value="<?= Ctrl::$agora->ldap_admin_login ?>"></div>
						</div>
						<div class="objField">
							<div><?= Txt::trad("AGORA_ldapPass") ?></div>
							<div><input type="password" name="ldap_admin_pass" value="<?= Ctrl::$agora->ldap_admin_pass ?>"></div>
						</div>
						<div class="objField" <?= Txt::tooltip("AGORA_ldapDnTooltip") ?> >
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
</div>