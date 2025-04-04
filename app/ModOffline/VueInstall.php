<script>
ready(function(){
	/**********************************************************************************************************
	 *	INIT LA TIMEZONE
	 **********************************************************************************************************/
	var curTimezone = Intl.DateTimeFormat().resolvedOptions().timeZone;
	curTimezone=curTimezone.replace("Europe/Berlin","Europe/Paris");
	$("select option[data-tzName='"+curTimezone+"']").prop("selected", true);

	/**********************************************************************************************************
	 *	VALIDATION DU FORMULAIRE
	 **********************************************************************************************************/
	$("#installForm").on("submit", async function(event){
		event.preventDefault();
	
		//// Vérif que les principaux champs sont remplis
		var installEmptyField=false;
		$("input,select").each(function(){
			if($(this).isEmpty())   {$(this).focusPulsate();  installEmptyField=true;}
		});
		if(installEmptyField==true)   {notify("<?= Txt::trad("emptyFields") ?>");  return false;}
	
		//// Controle le nom de la DB & le mail & le password
		if(/^[a-z0-9-_]+$/i.test($("[name='db_name']").val())==false)   				{notify("<?= Txt::trad("INSTALL_errorDbNameFormat") ?>","error");  $("[name='db_name']").focusPulsate();  return false;}
		if($("[name='adminMailLogin']").isMail()==false)  								{notify("<?= Txt::trad("mailInvalid") ?>","error");				return false;}
		if(isValidUserPassword($("[name='adminPassword']").val())==false)				{notify("<?= Txt::trad("passwordInvalid") ?>","error");			return false;}
		if($("[name='adminPassword']").val()!=$("[name='adminPasswordVerif']").val())	{notify("<?= Txt::trad("passwordConfirmError") ?>","error");	return false;}
	
		//// Formulaire validé et confirmé : "Post" via Ajax
		if(await confirmAlt("<?= Txt::trad("INSTALL_confirmInstall") ?>")){
			submitLoading();																			//Img "loading"
			$.ajax({url:"index.php",data:$(this).serialize(),type:"POST"}).done(function(result){	//Submit Ajax
				if(/installOk/i.test(result)==false)	{notify(result);}							//Erreur
				else{																				//Install Ok
					notify("<?= Txt::trad("INSTALL_installOk") ?>");								//Notify
					confirmCloseForm=false;															//Reinit confirmCloseForm
					setTimeout(function(){ redir("index.php?Ctrl=offline&disconnect=1"); },3000);	//Redir en page d'accueil avec un Timeout de 3sec minimum
				}												
			});
		}
	});
});
</script>

<style>
form								{padding:20px;}
 #formTitle							{margin-bottom:40px;}
 #formTitle	span					{font-size:1.4em; margin:0px 20px;}
 #formTitle img[src*='logoLabel']	{float:right; margin-top:-10px}
form h3								{margin:40px 0px 10px; padding-bottom:5px; border-bottom:#ddd solid 1px;}
#spaceDiskLimit						{width:50px;}
</style>


<div id="pageCenter">
	<div id="pageContent" class="miscContainer">
		<?php
		////	PAS D'ACCES AU DOSSIER DATAS  ||  FORMULAIRE D'INSTALL
		if(!is_writable(PATH_DATAS))  {echo '<h2><img src="app/img/importantBig.png"> &nbsp; '.Txt::trad("NOTIF_chmodDATAS").'</h2>';}
		else{
		?>
			<form action="index.php" method="post" id="installForm" enctype="multipart/form-data">
				<!--HEADER-->
				<div id="formTitle"><img src="app/img/install.png"><span>Installation</span><?= MdlAgora::selectTrad("install",Req::param("curTrad")) ?><img src="app/img/logoLabel.png"></div>
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
				<div class="objField"><div><?= Txt::trad("AGORA_diskSpaceLimit") ?></div><div><input type="text" name="spaceDiskLimit" value="100" id="spaceDiskLimit"> <?= Txt::trad("gigaOctet") ?></div></div>
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
		<?php } ?>
	</div>
</div>