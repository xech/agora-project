<?php if(Ctrl::$agora->gPeopleEnabled()){ ?><script src="https://apis.google.com/js/api.js"></script><?php } ?>

<script>
////	Resize
lightboxSetWidth(500);

////	INIT
$(function(){
	<?php if(Ctrl::$agora->gPeopleEnabled()){ ?>
	////	Charge l'import des contacts via l'API Google People. Doc: https://developers.google.com/people/quickstart/js?hl=fr
	gapi.load("client:auth2", function(){
		gapi.client.init({
			apiKey:"<?= Ctrl::$agora->gPeopleApiKey ?>",//"gPeopleApiKey" de l'API "People
			clientId:"<?= Ctrl::$agora->gIdentityClientId ?>",//"gIdentityClientId" du Projet
			discoveryDocs:["https://www.googleapis.com/discovery/v1/apis/people/v1/rest"],//Spécification obligatoires
			scope:"https://www.googleapis.com/auth/contacts.readonly"//Type de données à récupérer
		}).then(function(){
			if(gapi.auth2.getAuthInstance().isSignedIn.get())  {gapi.auth2.getAuthInstance().signOut();}		//On se déconnecte par défaut, car si on est déjà connecté, le "listen()" suivant ne se lance pas
			$("#gPeopleImportButton button").on("click",function(){ gapi.auth2.getAuthInstance().signIn(); });	//Clique sur "Importer les contacts" : lance en premier l'authentification via "signIn()"
			gapi.auth2.getAuthInstance().isSignedIn.listen(gPeopleGetContacts);									//Une fois connecté (cf. "listen()"), on lance la récupération des contacts via "gPeopleGetContacts()"
		});
	});

	////	Affiche la liste des personne, appelé via l'API "People"
	function gPeopleGetContacts()
	{
		//Si on est bien connecté : lance la récupération
		if(gapi.auth2.getAuthInstance().isSignedIn.get())
		{
			//Récupère et affiche chaque contacts : "pageSize" = nb maximum d'users à afficher, "sortOrder" = Tri des résultats, "personFields" = champs à récupérer
			gapi.client.people.people.connections.list({resourceName:"people/me", pageSize:100, sortOrder:"FIRST_NAME_ASCENDING", personFields:"names,emailAddresses"}).then(
				function(response){
					//Affiche les contacts
					if(response.result.connections && response.result.connections.length>0)
					{
						//Init
						var mailListToControl=[];
						var contactInputs="";
						//Ajoute chaque contact au formulaire d'invitation
						for(var cpt=0; cpt<response.result.connections.length; cpt++)
						{
							var person=response.result.connections[cpt];
							if(person.names && person.names.length>0 && person.emailAddresses && person.emailAddresses.length>0){
								var mailTmp=		person.emailAddresses[0].value;
								var givenNameTmp=	person.names[0].givenName;
								var familyNameTmp=	person.names[0].familyName;
								if(typeof familyNameTmp==="undefined")  {familyNameTmp="";}
								mailListToControl.push(mailTmp);
								contactInputs+='<div class="contactLine" title="'+mailTmp+'" data-mail="'+mailTmp+'"><input type="checkbox" name="gPeopleContacts[]" value="'+givenNameTmp+'@@'+familyNameTmp+'@@'+mailTmp+'" id="contact'+cpt+'"> &nbsp; <label for="contact'+cpt+'">'+givenNameTmp+' '+familyNameTmp+'</label></div>';
							}
						}
						//Affiche le formulaire avec chaque contact (inputs) et Masque l'autre formulaires a co
						$("#gPeopleForm").prepend(contactInputs).show();
						$("#invitationForm,#gPeopleImportButton").hide();
						//Controle ajax : désactive les mails déjà présents sur l'espace (après affichage des mails importés!)
						$.ajax({url:"?ctrl=user&action=loginExists",data:{mailList:mailListToControl},dataType:"json"}).done(function(resultJson){
							if(resultJson.mailListPresent.length>0){
								for(var cpt=0; cpt<resultJson.mailListPresent.length; cpt++){
									var mailTmp=resultJson.mailListPresent[cpt];
									var newTitle=mailTmp+" : <?= Txt::trad("USER_mailPresentInAccount") ?>";
									$(".contactLine[data-mail='"+mailTmp+"'] input").prop("disabled",true);
									$(".contactLine[data-mail='"+mailTmp+"']").css("opacity","0.8").append("&nbsp; <img src='app/img/info.png'>").attr("title",newTitle).removeClass("tooltipstered");//ajoute de l'opacité et l'icone "info", modif le tooltip, enleve le tooltipster pour ne pas le superposer au title par défaut
								}
							}
						});
						//Sélection d'un utilisateur : controle le quota dispo et affiche un message s'il est dépassé
						$("input[name='gPeopleContacts[]']").on("click",function(){
							var usersQuotaRemaining=<?= MdlUser::usersQuotaRemaining() ?>;
							if($("input[name='gPeopleContacts[]']:checked").length > usersQuotaRemaining){
								$(this).prop("checked",false);
								notify("<?= Txt::trad("USER_importQuotaExceeded") ?>".replace("--USERS_QUOTA_REMAINING--",usersQuotaRemaining).replace("--LIMITE_NB_USERS--","<?= limite_nb_users ?>"));
							}
						});
				   }
				}
			);
		}
	}
	<?php } ?>

	////	Controle du formulaire multiple gPeople et le nombre de contacts sélectionnés
	$("#gPeopleForm").submit(function(event){
		if($("input[name='gPeopleContacts[]']:checked").length==0){
			event.preventDefault();//Stop la validation du form
			notify("<?= Txt::trad("notifSelectUser"); ?>","warning");
		}
	});

	////	Contrôle du formulaire simple
	$("#invitationForm").submit(function(event){
		if(typeof invitationFormControled==="undefined"){
			event.preventDefault();//Stop la validation du form
			if($("input[name='name']").isEmpty() || $("input[name='firstName']").isEmpty())	{notify("<?= Txt::trad("fillFieldsForm") ?>","warning");  return false;}
			if($("input[name='mail']").isMail()==false)										{notify("<?= Txt::trad("mailInvalid") ?>","warning");  return false;}
			$.ajax("?ctrl=user&action=loginExists&mail="+encodeURIComponent($("input[name='mail']").val())).done(function(resultText){	// Verif si le compte utilisateur existe déjà
				if(/true/i.test(resultText))	{notify("<?= Txt::trad("USER_loginExists"); ?>","warning");  return false;}				//L'user existe déjà..
				else							{invitationFormControled=true;  $("#invitationForm").submit();}							//Sinon on confirme le formulaire récursivement !
			});
		}
	});
});
</script>

<style>
#invitationForm input, #invitationForm textarea	{width:100%!important; margin-bottom:10px!important;}
.orLabel					{margin-top:40px; margin-bottom:40px;}/*surcharge*/
#gPeopleImportButton		{text-align:center;}
#gPeopleImportButton button	{height:45px!important; width:300px!important; margin-bottom:30px;}
#gPeopleForm				{display:none;}
#gPeopleForm .contactLine	{display:inline-block!important; width:50%; margin-bottom:10px;}
#gPeopleForm textarea		{margin-top:15px;}
#invitationListHr			{margin-top:30px;}
#invitationListDiv			{display:none;}
#invitationListDiv li		{margin:10px;}
.submitButtonMain			{padding:0px; padding-top:10px;}/*surcharge*/
</style>


<div>
	<div class="lightboxTitle"><?= Txt::trad("USER_sendInvitation") ?> <img src="app/img/info.png" title="<?= Txt::trad("USER_sendInvitationTooltip") ?>"></div>

	<!--INVITATION SIMPLE-->
	<form id="invitationForm">
		<!--ENVOI D'UNE INVITATION-->
		<?php foreach($userFields as $tmpField){ ?><input type="text" name="<?= $tmpField ?>" placeholder="<?= Txt::trad($tmpField) ?>"><?php } ?>
		<textarea name="comment" placeholder="<?= Txt::trad("commentAdd") ?>"><?= Req::param("comment") ?></textarea>
		<?= Txt::submitButton("send") ?>
	</form>

	<!--INVITATION AVEC IMPORT DES CONTACTS GMAIL-->
	<?php if(Ctrl::$agora->gPeopleEnabled()){ ?>
	<div id="gPeopleImportButton">
		<div class="orLabel"><div><hr></div><div><?= Txt::trad("or") ?></div><div><hr></div></div>
		<button><img src="app/img/google.png"> <?= Txt::trad("USER_gPeopleImport") ?></button>
	</div>
	<form id="gPeopleForm">
		<textarea name="comment" placeholder="<?= Txt::trad("commentAdd") ?>"><?= Req::param("comment") ?></textarea>
		<?= Txt::submitButton("send") ?>
	</form>
	<?php } ?>

	<!--INVITATIONS EN ATTENTES ENVOYEES PAR L'USER COURANT-->
	<?php if(!empty($invitationList)){ ?>
	<div id="invitationList">
		<hr id="invitationListHr">
		<div onclick="$('#invitationListDiv').fadeToggle();"><img src="app/img/mail.png">&nbsp; <?= count($invitationList)." ".Txt::trad("USER_mailInvitationWait") ?></div>
		<ul id="invitationListDiv">
			<?php
			//Invitations déjà envoyées
			foreach($invitationList as $tmpInvitation){
				$objSpace=Ctrl::getObj("space",$tmpInvitation["_idSpace"]);
				$deleteInvitationImg="<img src='app/img/delete.png' style='height:20px' ".Txt::tooltip("delete")." onclick=\"confirmDelete('?ctrl=user&action=sendInvitation&deleteInvitation=true&_idInvitation=".$tmpInvitation["_idInvitation"]."')\" >";
				echo "<li>".$tmpInvitation["name"]." ".$tmpInvitation["firstName"]." - ".$tmpInvitation["mail"]." - ".Txt::dateLabel($tmpInvitation["dateCrea"])."&nbsp; ".$deleteInvitationImg."<br><img src='app/img/arrowRight.png' style='height:8px'> ".$objSpace->name."</li>";
			}
			?>
		</ul>
	</div>
	<?php } ?>
</div>