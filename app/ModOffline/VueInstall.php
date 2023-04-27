<script src="app/js/jstz.min.js"></script>
<script>
////	INIT
$(function(){
	////	Init le timezone avec "jstz"
	var curTimezone=$("[data-tzName='"+jstz.determine().name()+"']").val();
	$("[name='timezone']").val(curTimezone);

	////	Si "adminLogin" est un email : on l'ajoute à "adminMail"
	$("input[name='adminLogin']").on("keyup change",function(){
		if($(this).isMail())  {$("input[name='adminMail']").val(this.value);}
	});

	////	Validation du formulaire
	$("form").submit(function(event){
		//Pas de validation par défaut du formulaire
		event.preventDefault();
		//Vérifie que tous les champs sont remplis (sauf password, qui peut être vide)
		var installEmptyField=false;
		$("input,select,textarea").not("[name='db_password']").each(function(){
			if($(this).isEmpty())   {$(this).focusRed();  installEmptyField=true;}
		});
		if(installEmptyField==true)   {notify("<?= Txt::trad("fillFieldsForm") ?>");  return false;}
		//Vérif que le nom de la base de données est bien formaté
		if(/^[a-z0-9-_]+$/i.test($("[name='db_name']").val())==false)   {$("[name='db_name']").focusRed();  notify("<?= Txt::trad("INSTALL_dbErrorName") ?>","warning");  return false;}
		//Controle le mail &  password
		if($("[name='adminMail']").isMail()==false)   {notify("<?= Txt::trad("mailInvalid"); ?>","warning");  return false;}
		if(isValidPassword($("[name='adminPassword']").val())==false)					{notify("<?= Txt::trad("passwordInvalid") ?>","warning");	return false;}
		if($("[name='adminPassword']").val()!=$("[name='adminPasswordVerif']").val())	{notify("<?= Txt::trad("passwordConfirmError"); ?>","warning");  return false;}
		//Installe confirmé : Poste le formulaire via Ajax (avec image "Loading.."), Puis affiche le retour
		if(confirm("<?= Txt::trad("INSTALL_confirmInstall") ?>")){
			$("#imgLoading").show();
			$.ajax({url:"index.php",data:$(this).serialize(),dataType:"json"}).done(function(result){
				if(result.notifError)			{notify(result.notifError,"warning");  $("#imgLoading").hide();}	//Affiche un message d'erreur (de Db?) et masque le "loading"
				else if(result.redirSuccess)	{setTimeout(function(){ redir(result.redirSuccess); },2000);}		//Sinon l'install s'est bien déroulé : redirection avec timeout (le temps que le "config.inc.php" soit bien enregistré)
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
.objField .fieldLabel			{width:300px;}/*surcharge*/
#spaceDiskLimit					{width:40px;}
#imgLoading						{display:none; float:right;}
</style>

<div id="pageCenter">
	<!--CONTROLE L'ACCESS AU DOSSIER DATAS-->
	<?php if(!is_writable(PATH_DATAS)){ ?>
		<h3><img src="app/img/important.png"> <?= Txt::trad("NOTIF_chmodDATAS") ?></h3>
	<!--FORMULAIRE D'INSTALL-->
	<?php }else{ ?>
	<form id="pageCenterContent" class="miscContainer noConfirmClose" enctype="multipart/form-data">
		<!--HEADER-->
		<div class="vHeader"><h1><img src="app/img/install.png"> Install <img src="app/img/logoLabel.png"></h1></div>
		<!--LANGUE-->
		<div class="objField"><div class="fieldLabel"><?= Txt::trad("USER_langs") ?></div><div><?= Txt::menuTrad("install",Req::param("curTrad")) ?></div></div>
		<!--CONFIG DB-->
		<h3><?= Txt::trad("INSTALL_dbConnect") ?></h3>
		<div class="objField"><div class="fieldLabel"><?= Txt::trad("INSTALL_dbHost") ?></div><div><input type="text" name="db_host"></div></div>
		<div class="objField"><div class="fieldLabel"><?= Txt::trad("INSTALL_dbName") ?></div><div><input type="text" name="db_name"></div></div>
		<div class="objField"><div class="fieldLabel"><?= Txt::trad("INSTALL_dbLogin") ?></div><div><input type="text" name="db_login"></div></div>
		<div class="objField"><div class="fieldLabel"><?= Txt::trad("password") ?></div><div><input type="password" name="db_password"></div></div>
		<!--ADMIN GENERAL DE L'ESPACE-->
		<h3><?= Txt::trad("INSTALL_adminAgora") ?></h3>
		<div class="objField"><div class="fieldLabel"><?= Txt::trad("name") ?></div><div><input type="text" name="adminName"></div></div>
		<div class="objField"><div class="fieldLabel"><?= Txt::trad("firstName") ?></div><div><input type="text" name="adminFirstName"></div></div>
		<div class="objField"><div class="fieldLabel"><?= Txt::trad("login") ?></div><div><input type="text" name="adminLogin"></div></div>
		<div class="objField"><div class="fieldLabel"><?= Txt::trad("password") ?></div><div><input type="password" name="adminPassword"></div></div>
		<div class="objField"><div class="fieldLabel"><?= Txt::trad("passwordVerif") ?></div><div><input type="password" name="adminPasswordVerif"></div></div>
		<div class="objField"><div class="fieldLabel"><?= Txt::trad("mail") ?></div><div><input type="text" name="adminMail"></div></div>
		<!--PARAMETRAGE GENERAL DE L'ESPACE-->
		<h3><?= Txt::trad("AGORA_generalSettings") ?></h3>
		<div class="objField">
			<div class="fieldLabel"><?= Txt::trad("AGORA_timezone") ?></div>
			<div>
				<select name="timezone">
					<?php foreach(Tool::$tabTimezones as $tzName=>$timezone)  {echo "<option value=\"".$timezone."\" data-tzName='".$tzName."'>[GMT ".($timezone>0?"+":"").$timezone."] ".$tzName."</option>";}?>
				</select>
			</div>
		</div>
		<div class="objField"><div class="fieldLabel"><?= Txt::trad("AGORA_diskSpaceLimit") ?></div><div><input type="text" name="spaceDiskLimit" value="10" id="spaceDiskLimit"> <?= Txt::trad("gigaOctet") ?></div></div>
		<div class="objField"><div class="fieldLabel"><?= Txt::trad("AGORA_spaceName") ?></div><div><input type="text" name="spaceName"></div></div>
		<div class="objField"><div class="fieldLabel"><?= Txt::trad("description") ?></div><div><textarea name="spaceDescription"></textarea></div></div>
		<div class="objField">
			<div class="fieldLabel"><?= Txt::trad("SPACE_publicSpace") ?></div>
			<select name="spacePublic">
				<option value="0"><?= Txt::trad("no") ?></option>
				<option value="1"><?= Txt::trad("yes") ?></option>
			</select>
		</div>
		<!--VALIDATION-->
		<img src="app/img/loading.png" id="imgLoading">
		<?= Txt::submitButton() ?>
	</form>
	<?php } ?>
</div>