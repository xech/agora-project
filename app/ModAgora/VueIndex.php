<script>
////	INIT
$(function(){
	////	Logo du footer
	$("select[name='logo']").change(function(){
		$("#logoFile,#logoUrl").hide();
		if(this.value=="modify")	{$("#logoUrl,#logoFile").show();}
		else if(this.value!="")		{$("#logoUrl").show();}
	});

	////	Logo en page de connexion
	$("select[name='logoConnect']").change(function(){
		if(this.value=="modify")	{$("#logoConnectFile").show();}
		else						{$("#logoConnectFile").hide();}
	});
	//Affiche "logoConnectFile" si le <select> n'est pas affiché
	if($("select[name='logoConnect']").exist()==false)  {$("#logoConnectFile").show();}

	////	Vérif le type du fichier
	$("#wallpaperFile,#logoFile,#logoConnectFile").change(function(){
		if(!find(".jpg",this.value) && !find(".jpeg",this.value) && !find(".png",this.value))
			{notify("<?= Txt::trad("AGORA_wallpaperLogoError") ?>");}
	});

	////	logsTimeOut par défaut après mise à jour
	if(Math.round("<?= Ctrl::$agora->logsTimeOut ?>")>0 && $("select[name='logsTimeOut'] option[value='<?= Ctrl::$agora->logsTimeOut ?>']").exist()==false)
		{$("select[name='logsTimeOut']").val("30");}
	
	////	Affiche du "mapApiKeyDiv" si "mapTool"=="gmap"
	$("select[name='mapTool']").on("change",function(){   this.value=="gmap" ? $("#mapApiKeyDiv").fadeIn() : $("#mapApiKeyDiv").fadeOut();   }).trigger("change");//"trigger()" initie l'affichage

	////	Affiche du "gSigninClientId" si "gSignin" est activé
	$("select[name='gSignin']").on("change",function(){   this.value=="1" ? $("#gSigninClientIdDiv").fadeIn() : $("#gSigninClientIdDiv").fadeOut();   }).trigger("change");//"trigger()" initie l'affichage
});

////    On contrôle le formulaire
function formControl()
{
	//Contrôle du nom
	if($("[name='name']").isEmpty())   {notify("<?= Txt::trad("fillAllFields") ?>");  return false;}
	//Contrôle de l'espace disque / et de gSignin
	<?php if(Ctrl::isHost()==false){ ?>
	if(isNaN($("[name='limite_espace_disque']").val()))   {notify("<?= Txt::trad("AGORA_diskSpaceInvalid") ?>"); return false;}
	if($("[name='mapTool']").val()=="gmap" && $("[name='mapApiKey']").isEmpty())		{notify("<?= Txt::trad("AGORA_mapApiKey")." : ".Txt::trad("requiredFields") ?>"); return false;}
	if($("[name='gSignin']").val()=="1" && $("[name='gSigninClientId']").isEmpty())		{notify("<?= Txt::trad("AGORA_gPeopleApiKey")." : ".Txt::trad("requiredFields") ?>"); return false;}
	<?php } ?>
	return confirm("<?= Txt::trad("AGORA_confirmModif") ?>");
}
</script>

<style>
.miscContainer				{padding:20px; margin-bottom:40px;}/*surcharge*/
.objField>div				{padding:4px 0px 4px 0px;}/*surcharge*/
.objField .fieldLabel		{width:350px;}/*surcharge*/
#logoFile, #logoConnectFile	{display:none;}
#logoUrl					{margin-top:10px; <?= (empty(Ctrl::$agora->logo)) ? "display:none;":null ?>}
#imgLogo, #imgLogoConnect	{max-height:45px;}
#limite_espace_disque		{width:40px;}
.smtpLdapLabel				{cursor:pointer; margin-top:20px;}
#smtpConfig					{margin-top:10px; <?= (empty(Ctrl::$agora->sendmailFrom) && empty(Ctrl::$agora->smtpHost)) ? "display:none;":null ?>}
#smtpConfig .fieldLabel		{padding-left:20px;}
#ldapConfig					{margin-top:10px; <?= (empty(Ctrl::$agora->ldap_server)) ? "display:none;":null ?>}
#ldapConfig .fieldLabel		{padding-left:20px;}
.vVersions					{padding:20px; padding-left:50px;}
.vBackupForm				{text-align:center;}
.vBackupForm button			{min-width:60%; height:50px; margin:12px;}
.vBackupForm button img		{max-height:25px; margin-left:10px;}

/*RESPONSIVE FANCYBOX (440px)*/
@media screen and (max-width:440px){
	.fieldLabel		{padding-right:10px!important;}
}
</style>


<div id="pageCenter">
	<div id="pageCenterContent">
		<form action="index.php" method="post" onsubmit="return formControl()" class="miscContainer" enctype="multipart/form-data">
			<!--NAME-->
			<div class="objField">
				<div class="fieldLabel"><?= Txt::trad("AGORA_name") ?></div>
				<div><input type="text" name="name" value="<?= Ctrl::$agora->name ?>"></div>
			</div>
			<!--DESCRIPTION-->
			<div class="objField">
				<div class="fieldLabel"><?= Txt::trad("description") ?></div>
				<div><input type="text" name="description" value="<?= Ctrl::$agora->description ?>"></div>
			</div>
			<!--FOOTER HTML-->
			<div class="objField">
				<div class="fieldLabel"><?= Txt::trad("AGORA_footerHtml") ?></div>
				<div><textarea name="footerHtml"><?= Ctrl::$agora->footerHtml ?></textarea></div>
			</div>
		<hr>
			<!--WALLPAPER-->
			<div class="objField">
				<div class="fieldLabel"><?= Txt::trad("wallpaper") ?></div>
				<div><?= CtrlMisc::menuWallpaper(Ctrl::$agora->wallpaper) ?></div>
			</div>
			<!--LOGO FOOTER-->
			<div class="objField">
				<div class="fieldLabel"><?= Txt::trad("AGORA_logo") ?></div>
				<div>
					<img src="<?= Ctrl::$agora->pathLogoFooter() ?>" id="imgLogo">
					<select name="logo">
						<?php
						echo "<option value=''>".Txt::trad("byDefault")."</option>";
						if(!empty(Ctrl::$agora->logo))	{echo "<option value=\"".Ctrl::$agora->logo."\" selected>".Txt::trad("keepImg")."</option>";}
						echo "<option value='modify'>".Txt::trad("changeImg")."</option>";
						?>
					</select>
					<input type="file" name="logoFile" id="logoFile">
					<input type="text" name="logoUrl" id="logoUrl" value="<?= Ctrl::$agora->logoUrl ?>" placeholder="<?= Txt::trad("AGORA_logoUrl") ?>">
				</div>
			</div>
			<!--LOGO CONNECT-->
			<div class="objField">
				<div class="fieldLabel"><abbr title="<?= Txt::trad("AGORA_logoConnectInfo") ?>"><?= Txt::trad("AGORA_logoConnect") ?></abbr></div>
				<div>
					<img src="<?= Ctrl::$agora->pathLogoConnect() ?>" id="imgLogoConnect">
					<?php
					//Logo spécifié : selectionne "conserver" / "modifier" / "supprimer"
					if(!empty(Ctrl::$agora->logoConnect)){
						echo "<select name=\"logoConnect\">
								<option value=\"".Ctrl::$agora->logoConnect."\">".Txt::trad("keepImg")."</option>
								<option value='modify'>".Txt::trad("changeImg")."</option>
								<option value=''>".Txt::trad("delete")."</option>
							  </select>";
					}
					?>
					<input type="file" name="logoConnectFile" id="logoConnectFile">
				</div>
			</div>
			<!--SKIN COLOR-->
			<div class="objField">
				<div class="fieldLabel"><?= Txt::trad("AGORA_skin") ?></div>
				<div>
					<select name="skin">
						<option value="white"><?= Txt::trad("AGORA_white") ?></option>
						<option value="black" <?= Ctrl::$agora->skin=="black"?"selected":null ?>><?= Txt::trad("AGORA_black") ?></option>
					</select>
				</div>
			</div>
			<!--MODULE LABEL DISPLAY-->
			<div class="objField">
				<div class="fieldLabel"><?= Txt::trad("AGORA_moduleLabelDisplay") ?></div>
				<div>
					<select name="moduleLabelDisplay">
						<option value=""><?= Txt::trad("show") ?></option>
						<option value="hide" <?= Ctrl::$agora->moduleLabelDisplay=="hide"?"selected":null ?>><?= Txt::trad("hide") ?></option>
					</select>
				</div>
			</div>
		<hr>
			<!--LANG-->
			<div class="objField">
				<div class="fieldLabel"><img src="app/img/public.png"><?= Txt::trad("AGORA_lang") ?></div>
				<div><?= Txt::menuTrad("agora",Ctrl::$agora->lang) ?></div>
			</div>
			<!--TIMEZONE-->
			<div class="objField">
				<div class="fieldLabel"><img src="app/img/public.png"><?= Txt::trad("AGORA_timezone") ?></div>
				<div>
					<select name="timezone">
						<?php foreach(Tool::$tabTimezones as $tmpLabel=>$timezone)  {echo "<option value=\"".$timezone."\" ".($timezone==Tool::$tabTimezones[Ctrl::$curTimezone]?'selected':null).">[gmt ".($timezone>0?"+":"").$timezone."] ".$tmpLabel."</option>";}?>
					</select>
				</div>
			</div>
			<!--DISK SPACE (AGORA-PROJECT)-->
			<?php if(Ctrl::isHost()==false){ ?>
			<div class="objField">
				<div class="fieldLabel"><img src="app/img/diskSpace.png"><?= Txt::trad("AGORA_diskSpaceLimit") ?></div>
				<div><input type="text" name="limite_espace_disque" id="limite_espace_disque" value="<?= round((limite_espace_disque/File::sizeGo),2) ?>"> <?= Txt::trad("gigaOctet")?></div>
			</div>
			<?php } ?>
			<!--LOGS TIMEOUT-->
			<div class="objField">
				<div class="fieldLabel"><img src="app/img/log.png"> <abbr title="<?= Txt::trad("AGORA_logsTimeOutInfo") ?>"><?= Txt::trad("AGORA_logsTimeOut") ?></abbr></div>
				<div>
					<select name="logsTimeOut">
						<?php foreach($logsTimeOut as $tmpTime)  {echo "<option value='".$tmpTime."' ".($tmpTime==Ctrl::$agora->logsTimeOut?"selected":null).">".$tmpTime."</option>";} ?>
					</select>
					<?= Txt::trad("days") ?>
				</div>
			</div>
		<hr>
			<!--SERVER JITSI (AGORA-PROJECT)-->
			<?php if(Ctrl::isHost()==false){ ?>
			<div class="objField">
				<div class="fieldLabel"><img src="app/img/visio.png"><abbr title="<?= Txt::trad("AGORA_visioHostInfo") ?>"><?= Txt::trad("AGORA_visioHost") ?></abbr></div>
				<div><input type="text" name="visioHost" value="<?= Ctrl::$agora->visioHost ?>"></div>
			</div>
			<?php } ?>
			<!--LIKES-->
			<div class="objField">
				<div class="fieldLabel"><img src="app/img/usersLike_like.png"><?= Txt::trad("AGORA_usersLikeLabel") ?></div>
				<div>
					<select name="usersLike">
						<option value="likeSimple"><?= Txt::trad("AGORA_usersLike_likeSimple") ?> <img src="app/img/usersLike_like.png"></option>
						<option value="likeOrNot" <?= Ctrl::$agora->usersLike=="likeOrNot"?"selected":null ?>><?= Txt::trad("AGORA_usersLike_likeOrNot") ?></option>
						<option value="" <?= empty(Ctrl::$agora->usersLike)?"selected":null ?>><?= Txt::trad("no") ?></option>
					</select>
				</div>
			</div>
			<!--COMMENTS-->
			<div class="objField">
				<div class="fieldLabel"><img src="app/img/usersComment.png"><?= Txt::trad("AGORA_usersCommentLabel") ?></div>
				<div>
					<select name="usersComment">
						<option value="1"><?= Txt::trad("yes") ?></option>
						<option value="0" <?= empty(Ctrl::$agora->usersComment)?"selected":null ?>><?= Txt::trad("no") ?></option>
					</select>
				</div>
			</div>
			<!--MESSENGER DISABLED-->
			<div class="objField">
				<div class="fieldLabel"><img src="app/img/messenger.png"><?= Txt::trad("AGORA_messengerDisabled") ?></div>
				<div>
					<select name="messengerDisabled">
						<option value=""><?= Txt::trad("yes") ?></option>
						<option value="1" <?= !empty(Ctrl::$agora->messengerDisabled)?"selected":null ?>><?= Txt::trad("no") ?></option>
					</select>
				</div>
			</div>
			<!--PERSONS SORT-->
			<div class="objField">
				<div class="fieldLabel"><img src="app/img/user/iconSmall.png"><?= Txt::trad("AGORA_personsSort") ?></div>
				<div>
					<select name="personsSort">
						<option value="firstName"><?= Txt::trad("firstName") ?></option>
						<option value="name" <?= Ctrl::$agora->personsSort=="name"?"selected":null ?>><?= Txt::trad("name") ?></option>
					</select>
				</div>
			</div>
			<!--MAP TOOLS-->
			<div class="objField">
				<div class="fieldLabel"><img src="app/img/map.png"><abbr title="<?= Txt::trad("AGORA_mapToolInfo") ?>"><?= Txt::trad("AGORA_mapTool") ?></abbr></div>
				<div>
					<select name="mapTool">
						<option value="gmap" <?= Ctrl::$agora->mapTool!="leaflet"?"selected":null ?>>Google Map</option>
						<option value="leaflet" <?= Ctrl::$agora->mapTool=="leaflet"?"selected":null ?>>Leaflet / OpenStreetMap</option>
					</select>
				</div>
			</div>
			<!--MAP TOOL APIKEY (AGORA-PROJECT)-->
			<?php if(Ctrl::isHost()==false){ ?>
			<div class="objField" id="mapApiKeyDiv">
				<div class="fieldLabel"><img src="app/img/map.png"><abbr title="<?= Txt::trad("AGORA_mapApiKeyInfo") ?>"><?= Txt::trad("AGORA_mapApiKey") ?></abbr></div>
				<div><input type="text" name="mapApiKey" value="<?= Ctrl::$agora->mapApiKey ?>"></div>
			</div>
			<?php } ?>
			<!--GOOGLE SIGNIN ACTIVE ?-->
			<div class="objField">
				<div class="fieldLabel"><img src="app/img/gSignin.png"><abbr title="<?= Txt::trad("AGORA_gSigninInfo") ?>"><?= Txt::trad("AGORA_gSignin") ?></abbr></div>
				<div>
					<select name="gSignin">
						<option value="1"><?= Txt::trad("yes") ?></option>
						<option value="0" <?= empty(Ctrl::$agora->gSignin)?"selected":null ?>><?= Txt::trad("no") ?></option>
					</select>
				</div>
			</div>
			<!--SIGNIN "CLIENT ID" & PEOPLE "API KEY" (AGORA-PROJECT)-->
			<?php if(Ctrl::isHost()==false){ ?>
			<div class="objField" id="gSigninClientIdDiv">
				<div class="fieldLabel"><img src="app/img/gSignin.png"><abbr title="<?= Txt::trad("AGORA_gSigninClientIdInfo") ?>"><?= Txt::trad("AGORA_gSigninClientId") ?></abbr></div>
				<div><input type="text" name="gSigninClientId" value="<?= Ctrl::$agora->gSigninClientId ?>"></div>
			</div>
			<div class="objField">
				<div class="fieldLabel"><img src="app/img/gSignin.png"><abbr title="<?= Txt::trad("AGORA_gPeopleApiKeyInfo") ?>"><?= Txt::trad("AGORA_gPeopleApiKey") ?></abbr></div>
				<div><input type="text" name="gPeopleApiKey" value="<?= Ctrl::$agora->gPeopleApiKey ?>"></div>
			</div>
			<?php } ?>
		<hr>
			<!--PARAMETRAGE LDAP (CONNEXION D'USERS)-->
			<div class="smtpLdapLabel" onclick="$('#ldapConfig').fadeToggle()"><?= Txt::trad("AGORA_ldapLabel") ?> <img src="app/img/plusSmall.png"></div>
			<?php
			//Module de connexion Ldap désactivé / activé
			if(!function_exists("ldap_connect"))  {echo "<div class='infos'>".Txt::trad("AGORA_ldapDisabled")."</div>";}
			else{
			?>
			<div id="ldapConfig">
				<div class="objField">
					<div class="fieldLabel"><?= Txt::trad("AGORA_ldapHost") ?></div>
					<div><input type="text" name="ldap_server" value="<?= Ctrl::$agora->ldap_server ?>"></div>
				</div>
				<div class="objField">
					<div class="fieldLabel"><abbr title="<?= Txt::trad("AGORA_ldapPortInfo") ?>"><?= Txt::trad("AGORA_ldapPort") ?></abbr></div>
					<div><input type="text" name="ldap_server_port" value="<?= Ctrl::$agora->ldap_server_port ?>"></div>
				</div>
				<div class="objField">
					<div class="fieldLabel"><abbr title="<?= Txt::trad("AGORA_ldapLoginInfo") ?>"><?= Txt::trad("AGORA_ldapLogin") ?></abbr></div>
					<div><input type="text" name="ldap_admin_login" value="<?= Ctrl::$agora->ldap_admin_login ?>"></div>
				</div>
				<div class="objField">
					<div class="fieldLabel"><?= Txt::trad("AGORA_ldapPass") ?></div>
					<div><input type="password" name="ldap_admin_pass" value="<?= Ctrl::$agora->ldap_admin_pass ?>"></div>
				</div>
				<div class="objField">
					<div class="fieldLabel"><abbr title="<?= Txt::trad("AGORA_ldapDnInfo") ?>"><?= Txt::trad("AGORA_ldapDn") ?></abbr></div>
					<div><input type="text" name="ldap_base_dn" value="<?= Ctrl::$agora->ldap_base_dn ?>"></div>
				</div>
				<div class="objField">
					<div class="fieldLabel"><abbr title="<?= Txt::trad("AGORA_ldapCreaAutoUsersInfo") ?>"><?= Txt::trad("AGORA_ldapCreaAutoUsers") ?></abbr></div>
					<div>
						<select name="ldap_crea_auto_users">
							<option value="1"><?= Txt::trad("yes") ?></option>
							<option value="" <?= empty(Ctrl::$agora->ldap_crea_auto_users)?"selected":null ?>><?= Txt::trad("no") ?></option>
						</select>
					</div>
				</div>
				<div class="objField">
					<div class="fieldLabel"><?= Txt::trad("AGORA_ldapPassEncrypt") ?></div>
					<div>
						<select name="ldap_pass_cryptage">
							<option value="1"><?= Txt::trad("none") ?></option>
							<option value="sha" <?= Ctrl::$agora->ldap_pass_cryptage=="sha"?"selected":null ?>>SHA</option>
							<option value="md5" <?= Ctrl::$agora->ldap_pass_cryptage=="md5"?"selected":null ?>>Md5</option>
						</select>
					</div>
				</div>
			</div>
			<?php } ?>

			<!--PARAMETRAGE SMTP POUR L'ENVOI DE MAILS (AGORA-PROJECT)-->
			<?php if(Ctrl::isHost()==false){ ?>
			<div class="smtpLdapLabel" onclick="$('#smtpConfig').fadeToggle()"><?= Txt::trad("AGORA_smtpLabel") ?> <img src="app/img/plusSmall.png"></div>
			<div id="smtpConfig">
				<div class="objField">
					<div class="fieldLabel"><?= Txt::trad("AGORA_sendmailFrom") ?></div>
					<div><input type="text" name="sendmailFrom" value="<?= Ctrl::$agora->sendmailFrom ?>" placeholder="<?= Txt::trad("AGORA_sendmailFromPlaceholder") ?>"></div>
				</div>
				<div class="objField">
					<div class="fieldLabel"><?= Txt::trad("AGORA_smtpHost") ?></div>
					<div><input type="text" name="smtpHost" value="<?= Ctrl::$agora->smtpHost ?>"></div>
				</div>
				<div class="objField" title="<?= Txt::trad("AGORA_smtpPortInfo") ?>">
					<div class="fieldLabel"><?= Txt::trad("AGORA_smtpPort") ?></div>
					<div><input type="text" name="smtpPort" value="<?= Ctrl::$agora->smtpPort ?>"></div>
				</div>
				<div class="objField" title="<?= Txt::trad("AGORA_smtpSecureInfo") ?>">
					<div class="fieldLabel"><?= Txt::trad("AGORA_smtpSecure") ?></div>
					<div><input type="text" name="smtpSecure" value="<?= Ctrl::$agora->smtpSecure ?>"></div>
				</div>
				<div class="objField">
					<div class="fieldLabel"><?= Txt::trad("AGORA_smtpUsername") ?></div>
					<div><input type="text" name="smtpUsername" value="<?= Ctrl::$agora->smtpUsername ?>"></div>
				</div>
				<div class="objField">
					<div class="fieldLabel"><?= Txt::trad("AGORA_smtpPass") ?></div>
					<div><input type="password" name="smtpPass" value="<?= Ctrl::$agora->smtpPass ?>"></div>
				</div>
			</div>
			<?php } ?>

			<!--VALIDATION DU FORMULAIRE-->
			<?= Txt::submitButton("modify") ?>
		</form>

		<!--INFOS & VERSIONS-->
		<div class="miscContainer vVersions">
				Agora-Project version <?= Ctrl::$agora->version_agora ?> &nbsp; (<?= Txt::trad("AGORA_dateUpdate")." ".Txt::displayDate(Ctrl::$agora->dateUpdateDb,"dateMini") ?>) &nbsp; &nbsp;
				<a href="javascript:lightboxOpen('docs/CHANGELOG.txt')"><button><?= Txt::trad("AGORA_Changelog") ?></button></a> <br><br>
				PHP <?= str_replace(strstr(phpversion(),"-"),null,phpversion()) ?> <img src="app/img/separator.png"> 
				MySQL <?= Db::dbVersion() ?>
				<?php if(!function_exists("mail")){ ?><div title="<?= Txt::trad("AGORA_funcMailInfo") ?>"><img src="app/img/delete.png"> &nbsp; <?= Txt::trad("AGORA_funcMailDisabled") ?></div><?php } ?>
				<?php if(!function_exists("imagecreatetruecolor")){ ?><div><img src="app/img/delete.png"> &nbsp; <?= Txt::trad("AGORA_funcImgDisabled") ?></div><?php } ?>
		</div>
		
		<!--SAUVEGARDER LA BDD ET LE FICHIERS-->
		<?php if(Req::isMobile()==false){ ?>
		<form class="miscContainer vBackupForm" action="index.php" method="post">
			<input type="hidden" name="ctrl" value="agora">
			<input type="hidden" name="action" value="getBackup">
			<button type="submit" name="typeBackup" value="all" <?= $alertMessageBigSav ?>><img src="app/img/download.png"> <?= Txt::trad("AGORA_backupFull") ?> <img src="app/img/folder/folderSmall.png"><img src="app/img/diskSpace.png"></button>
			<button type="submit" name="typeBackup" value="db"><img src="app/img/download.png"> <?= Txt::trad("AGORA_backupDb") ?> <img src="app/img/diskSpace.png"></button>
		</form>
		<?php } ?>
	</div>
</div>