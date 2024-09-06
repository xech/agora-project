<script>
////	INIT
$(function(){
	////	Init la timezone
	var curTimezone = Intl.DateTimeFormat().resolvedOptions().timeZone;
	curTimezone=curTimezone.replace("Europe/Berlin","Europe/Paris");
	$("select option[data-tzName='"+curTimezone+"']").prop("selected", true);

	////	Validation du formulaire
	$("#installForm").submit(function(event){
		//Stop la validation du form
		event.preventDefault();
		//Vérifie que tous les champs sont remplis (sauf password, qui peut être vide)
		var installEmptyField=false;
		$("input,select,textarea").not("[name='db_password']").each(function(){
			if($(this).isEmpty())   {$(this).focusRed();  installEmptyField=true;}
		});
		if(installEmptyField==true)   {notify("<?= Txt::trad("fillFieldsForm") ?>");  return false;}
		//Controle le nom de la DB & le mail & le password
		if(/^[a-z0-9-_]+$/i.test($("[name='db_name']").val())==false)   				{notify("<?= Txt::trad("INSTALL_errorDbNameFormat") ?>","warning");  $("[name='db_name']").focusRed();  return false;}
		if($("[name='adminMailLogin']").isMail()==false)  								{notify("<?= Txt::trad("mailInvalid"); ?>","warning");  return false;}
		if(isValidUserPassword($("[name='adminPassword']").val())==false)				{notify("<?= Txt::trad("passwordInvalid") ?>","warning");	return false;}
		if($("[name='adminPassword']").val()!=$("[name='adminPasswordVerif']").val())	{notify("<?= Txt::trad("passwordConfirmError"); ?>","warning");  return false;}
		//Formulaire validé et confirmé : "Post" via Ajax
		if(confirm("<?= Txt::trad("INSTALL_confirmInstall") ?>")){
			submitButtonLoading();																											//Affiche l'icone "loading"
			$.ajax({url:"index.php",data:$(this).serialize()}).done(function(result){														//Valide le formulaire
				if(/installOk/i.test(result))	{setTimeout(function(){ redir("index.php?disconnect=1&notify=INSTALL_installOk"); },5000);}	//Redir en page d'accueil si install OK (tjs avec"setTimeout()" !)
				else if(result)					{notify(result,"warning");  $(".submitButtonLoading").hide()}								//Sinon Affiche un message d'erreur et masque le "loading"
			});
		}
	});
});
</script>


<style>
#pageCenter						{padding-top:20px; padding-bottom:30px;}/*surcharge*/
#pageCenterContent				{width:700px; padding:10px; margin-top:50px;}/*surcharge*/
form							{margin-top:40px;}
.vHeader						{margin-bottom:40px;}
.vHeader img[src*='logo']		{float:right;}
h3								{margin-top:20px; font-style:italic;}
#spaceDiskLimit					{width:40px;}
#imgLoading						{display:none; float:right;}
</style>


<div id="pageCenter">
	<!--CONTROLE L'ACCESS AU DOSSIER DATAS-->
	<?php if(!is_writable(PATH_DATAS)){ ?>
		<h3><img src="app/img/important.png"> <?= Txt::trad("NOTIF_chmodDATAS") ?></h3>
	<!--FORMULAIRE D'INSTALL-->
	<?php }else{ ?>
		<div id="pageCenterContent" class="miscContainer">
			<form action="index.php" method="post" id="installForm" enctype="multipart/form-data">
				<!--HEADER-->
				<div class="vHeader"><h1><img src="app/img/install.png"> Install <img src="app/img/logoLabel.png"></h1></div>
				<!--LANGUE-->
				<div class="objField"><div><?= Txt::trad("USER_langs") ?></div><div><?= MdlAgora::selectTrad("install",Req::param("curTrad")) ?></div></div>
				<!--CONFIG DB-->
				<h3><?= Txt::trad("INSTALL_dbConnect") ?></h3>
				<div class="objField"><div><?= Txt::trad("INSTALL_dbHost") ?></div><div><input type="text" name="db_host"></div></div>
				<div class="objField"><div><?= Txt::trad("INSTALL_dbName") ?></div><div><input type="text" name="db_name"></div></div>
				<div class="objField"><div><?= Txt::trad("INSTALL_dbLogin") ?></div><div><input type="text" name="db_login"></div></div>
				<div class="objField"><div><?= Txt::trad("password") ?></div><div><input type="password" name="db_password"></div></div>
				<!--ADMIN GENERAL DE L'ESPACE-->
				<h3><?= Txt::trad("INSTALL_adminAgora") ?></h3>
				<div class="objField"><div><?= Txt::trad("name") ?></div><div><input type="text" name="adminName"></div></div>
				<div class="objField"><div><?= Txt::trad("firstName") ?></div><div><input type="text" name="adminFirstName"></div></div>
				<div class="objField"><div><?= Txt::trad("mailLlogin") ?></div><div><input type="text" name="adminMailLogin"></div></div>
				<div class="objField"><div><?= Txt::trad("password") ?></div><div><input type="password" name="adminPassword"></div></div>
				<div class="objField"><div><?= Txt::trad("passwordVerif") ?></div><div><input type="password" name="adminPasswordVerif"></div></div>
				<!--PARAMETRAGE GENERAL DE L'ESPACE-->
				<h3><?= Txt::trad("AGORA_generalSettings") ?></h3>
				<div class="objField">
					<div><?= Txt::trad("AGORA_timezone") ?></div>
					<div>
						<select name="timezone">
							<?php foreach(Tool::$tabTimezones as $tzName=>$timezone)  {echo "<option value=\"".$timezone."\" data-tzName='".$tzName."'>[GMT ".($timezone>0?"+":"").$timezone."] ".$tzName."</option>";}?>
						</select>
					</div>
				</div>
				<div class="objField"><div><?= Txt::trad("AGORA_diskSpaceLimit") ?></div><div><input type="text" name="spaceDiskLimit" value="10" id="spaceDiskLimit"> <?= Txt::trad("gigaOctet") ?></div></div>
				<div class="objField"><div><?= Txt::trad("AGORA_name") ?></div><div><input type="text" name="spaceName"></div></div>
				<div class="objField"><div><?= Txt::trad("AGORA_description") ?></div><div><textarea name="spaceDescription"></textarea></div></div>
				<div class="objField">
					<div><?= Txt::trad("SPACE_publicSpace") ?></div>
					<select name="spacePublic">
						<option value="0"><?= Txt::trad("no") ?></option>
						<option value="1"><?= Txt::trad("yes") ?></option>
					</select>
				</div>
				<!--VALIDATION-->
				<?= Txt::submitButton("validate") ?>
			</form>
		</div>
	<?php } ?>
</div>