<?php
/*
 * Classe de gestion d'une langue
 */
class Trad extends Txt
{
	/*
	 * Chargement les éléments de traduction
	 */
	public static function loadTradsLang()
	{
		////	Langue courante / Header http / Editeurs Tinymce,DatePicker,etc / Dates formatées par PHP
		self::$trad["CURLANG"]="fr";
		self::$trad["HEADER_HTTP"]="fr";
		self::$trad["DATEPICKER"]="fr";
		self::$trad["HTML_EDITOR"]="fr_FR";
		self::$trad["UPLOADER"]="fr";
		setlocale(LC_TIME, "fr_FR.utf8", "fr_FR.UTF-8", "fr_FR", "fr", "french");

		////	Divers
		self::$trad["OK"]="OK";
		self::$trad["fillAllFields"]="Merci de remplir tous les champs";
		self::$trad["requiredFields"]="Champ obligatoire";
		self::$trad["inaccessibleElem"]="Element inaccessible";
		self::$trad["warning"]="Attention";
		self::$trad["elemEditedByAnotherUser"]="L'élément est en cours d'edition par";//"..bob"
		self::$trad["yes"]="oui";
		self::$trad["no"]="non";
		self::$trad["none"]="aucun";
		self::$trad["or"]="ou";
		self::$trad["and"]="et";
		self::$trad["by"]="par";
		self::$trad["goToPage"]="Aller à la page";
		self::$trad["alphabetFilter"]="Filtre alphabétique";
		self::$trad["displayAll"]="Tout afficher";
		self::$trad["allCategory"]="Toutes les categories";
		self::$trad["important"]="Important";
		self::$trad["show"]="afficher";
		self::$trad["hide"]="masquer";
		self::$trad["byDefault"]="Par défaut";
		self::$trad["mapLocalize"]="Localiser sur une carte";
		self::$trad["mapLocalizationFailure"]="Echec de la localisation de l'adresse suivante";
		self::$trad["mapLocalizationFailure2"]="Merci de vérifier que l'adresse existe bien sur www.google.fr/maps ou www.openstreetmap.org";
		self::$trad["sendMail"]="Envoyer un email";
		self::$trad["mailInvalid"]="L'email n'est pas valide";
		self::$trad["element"]="élément";
		self::$trad["elements"]="éléments";
		self::$trad["folder"]="dossier";
		self::$trad["folders"]="dossiers";
		self::$trad["close"]="Fermer";
		self::$trad["visibleAllSpaces"]="Visible sur tous les espaces";
		self::$trad["confirmCloseForm"]="Fermer le formulaire ?";
		self::$trad["modifRecorded"]="Les modifications ont été enregistrées";
		self::$trad["confirm"]="Confirmer ?";
		self::$trad["comment"]="Commentaire";
		self::$trad["commentAdd"]="Ajouter un commentaire";
		self::$trad["optional"]="(optionnel)";
		self::$trad["objNew"]="Nouvel élément";
		self::$trad["objNewInfos"]="Nouvel élément créé dans les 24 heures ou depuis ma précédente connexion";
		self::$trad["personalAccess"]="Accès personnel";
		self::$trad["copyUrl"]="Copier l'adresse d'accès";
		self::$trad["copyUrlConfirmed"]="L'adresse vers l'élément a bien été copiée : elle peut être intégrée à une news, un événement, un message du forum, etc.<br><br>Elle peut également être utilisée à l'extérieur de l'espace (dans un email, un blog, etc.), mais seules les personnes ayant un accès à l'espace pourront l'utiliser.";

		////	images
		self::$trad["picture"]="Photo";
		self::$trad["wallpaper"]="Fond d'écran";
		self::$trad["keepImg"]="conserver l'image";
		self::$trad["changeImg"]="changer l'image";
		self::$trad["pixels"]="pixels";

		////	Connexion
		self::$trad["specifyLoginPassword"]="Merci de spécifier un identifiant et un mot de passe";//user connexion forms
		self::$trad["specifyLogin"]="Merci de spécifier un email/identifiant (sans espaces)";//user edit
		self::$trad["specifyLoginMail"]="Il est préférable d'utiliser une adresse email comme identifiant de connexion";//idem
		self::$trad["login"]="Email / Identifiant de connexion";//user edit/vue & agora install & import/export d'users (tester)
		self::$trad["loginPlaceholder"]="Email / Identifiant";
		self::$trad["connect"]="Connexion";
		self::$trad["connectAuto"]="Se souvenir de moi";
		self::$trad["connectAutoInfo"]="Retenir mes identifiants pour une connexion automatique";
		self::$trad["gSigninButton"]="Connexion avec Gmail";
		self::$trad["gSigninButtonInfo"]="Connectez-vous avec votre compte Gmail : vous devez déjà posséder sur cet espace un compte avec une adresse <i>@gmail.com</i> comme identifiant";
		self::$trad["gSigninUserNotRegistered"]="n'est pas enregistré sur l'espace avec l'email";//"Boby Smith" n'est pas enregistré sur l'espace avec l'email "boby.smith@gmail.com"
		self::$trad["switchOmnispace"]="Se connecter à un autre espace Omnispace";
		self::$trad["guestAccess"]="Se connecter à un espace public (en invité)";
		self::$trad["spacePassError"]="Mot de passe erroné";
		self::$trad["ieObsolete"]="Votre navigateur Internet Explorer n'est plus mis à jour par Microsoft depuis plusieurs années : ll est fortement conseillé d'utiliser un autre navigateur tel que Firefox, Chrome, Edge ou Safari.";

		////	Password : connexion d'user / edition d'user / reset du password
		self::$trad["password"]="Mot de passe";
		self::$trad["passwordModify"]="Modifier le mot de passe";
		self::$trad["passwordToModify"]="Mot de passe temporaire (à modifier à la confirmation)";//notif email
		self::$trad["passwordVerif"]="Confirmer mot de passe";
		self::$trad["passwordInfo"]="Merci de remplir les champs uniquement si vous souhaitez changer de mot de passe";
		self::$trad["passwordInvalid"]="Attention : votre mot de passe doit comporter au moins 6 caractères, avec au moins une lettre et un chiffre";
		self::$trad["passwordConfirmError"]="Votre confirmation de mot de passe n'est pas valide";
		self::$trad["specifyPassword"]="Merci de spécifier un mot de passe";//user edit/inscription
		self::$trad["resetPassword"]="Mot de passe oublié ?";
		self::$trad["resetPassword2"]="Indiquez votre adresse email pour réinitialiser votre mot de passe de connection";
		self::$trad["resetPasswordNotif"]="Un email vient de vous être envoyé pour réinitialiser votre mot de passe. Si vous ne l'avez pas reçu, vérifiez que l'adresse saisie est bien la bonne ou que l'email n'est pas arrivé dans vos indésirables.";
		self::$trad["resetPasswordMailTitle"]="Réinitialiser votre mot de passe";
		self::$trad["resetPasswordMailPassword"]="Pour vous connecter à votre espace et réinitialiser votre mot de passe";
		self::$trad["resetPasswordMailPassword2"]="merci de cliquer ici";
		self::$trad["resetPasswordMailLoginRemind"]="Rappel de votre identifiant de connexion";
		self::$trad["resetPasswordIdExpired"]="Le lien pour régénérer le mot de passe a expiré. Merci de recommencer la procédure";

		////	Type d'affichage
		self::$trad["displayMode"]="Affichage";
		self::$trad["displayMode_line"]="Liste";
		self::$trad["displayMode_block"]="Bloc";
		
		////	Sélectionner / Déselectionner tous les éléments
		self::$trad["select"]="Sélectionner";
		self::$trad["selectUnselect"]="Sélectionner / Déselectionner";
		self::$trad["selectUnselectAll"]="Tout sélectionner / déselectionner";
		self::$trad["selectAll"]="Tout sélectionner";
		self::$trad["invertSelection"]="Inverser la sélection";
		self::$trad["deleteElems"]="Supprimer la sélection";
		self::$trad["changeFolder"]="Déplacer vers un autre dossier";
		self::$trad["showOnMap"]="Voir les contacts sur une carte";
		self::$trad["selectUser"]="Merci de sélectionner au moins un utilisateur";
		self::$trad["selectUsers"]="Merci de sélectionner au moins 2 utilisateurs";
		self::$trad["selectSpace"]="Merci de sélectionner au moins un espace";
		
		////	Temps ("de 11h à 12h", "le 25-01-2007 à 10h30", etc.)
		self::$trad["from"]="de";
		self::$trad["at"]="à";
		self::$trad["the"]="le";
		self::$trad["begin"]="Début";
		self::$trad["end"]="Fin";
		self::$trad["hourSeparator"]="h";
		self::$trad["days"]="jours";
		self::$trad["day_1"]="Lundi";
		self::$trad["day_2"]="Mardi";
		self::$trad["day_3"]="Mercredi";
		self::$trad["day_4"]="Jeudi";
		self::$trad["day_5"]="Vendredi";
		self::$trad["day_6"]="Samedi";
		self::$trad["day_7"]="Dimanche";
		self::$trad["month_1"]="janvier";
		self::$trad["month_2"]="fevrier";
		self::$trad["month_3"]="mars";
		self::$trad["month_4"]="avril";
		self::$trad["month_5"]="mai";
		self::$trad["month_6"]="juin";
		self::$trad["month_7"]="juillet";
		self::$trad["month_8"]="aout";
		self::$trad["month_9"]="septembre";
		self::$trad["month_10"]="octobre";
		self::$trad["month_11"]="novembre";
		self::$trad["month_12"]="décembre";
		self::$trad["today"]="aujourd'hui";
		self::$trad["displayToday"]="Aujourd'hui";
		self::$trad["beginEndError"]="La date de fin ne peut pas être antérieure à la date de début";
		self::$trad["dateFormatError"]="La date doit être au format jj/mm/AAAA";
		
		////	Nom & Description (pour les menus d'édition principalement)
		self::$trad["title"]="Titre";
		self::$trad["name"]="Nom";
		self::$trad["description"]="Description";
		self::$trad["specifyName"]="Merci de spécifier un nom";
		self::$trad["editorDraft"]="Récupérer mon texte";
		self::$trad["editorDraftConfirm"]="Récuperer le dernier texte que j'ai saisi";
		
		////	Validation des formulaires
		self::$trad["add"]=" Ajouter";
		self::$trad["modify"]=" Modifier";
		self::$trad["modifyAndAccesRight"]="Modifier / Gérer droits d'accès";
		self::$trad["validate"]=" Valider";
		self::$trad["send"]="Envoyer";
		self::$trad["sendTo"]="Envoyer à";

		////	Tri d'affichage. Tous les éléments (dossier, tâche, lien, etc...) ont par défaut une date, un auteur & une description
		self::$trad["sortBy"]="Trié par";
		self::$trad["sortBy2"]="Trier par";
		self::$trad["SORT_dateCrea"]="date de création";
		self::$trad["SORT_dateModif"]="date de modif";
		self::$trad["SORT_title"]="titre";
		self::$trad["SORT_description"]="description";
		self::$trad["SORT__idUser"]="auteur";
		self::$trad["SORT_extension"]="type de fichier";
		self::$trad["SORT_octetSize"]="taille";
		self::$trad["SORT_downloadsNb"]="nb de téléchargements";
		self::$trad["SORT_civility"]="civilité";
		self::$trad["SORT_name"]="nom";
		self::$trad["SORT_firstName"]="prénom";
		self::$trad["SORT_adress"]="adresse";
		self::$trad["SORT_postalCode"]="code postal";
		self::$trad["SORT_city"]="ville";
		self::$trad["SORT_country"]="pays";
		self::$trad["SORT_function"]="fonction";
		self::$trad["SORT_companyOrganization"]="société / organisme";
		self::$trad["tri_ascendant"]="Ascendant";
		self::$trad["tri_descendant"]="Descendant";

		////	Options de suppression
		self::$trad["confirmDelete"]="Confirmer la suppression ?";
		self::$trad["confirmDeleteDbl"]="Cette action est définitive : confirmer tout de même ?";
		self::$trad["confirmDeleteFolderAccess"]="Certains sous-dossiers ne vous sont pas accessibles, car affectés à d'autres utilisateurs : confirmer tout de même ?";
		self::$trad["notifyBigFolderDelete"]="La suppression des --NB_FOLDERS-- dossiers peut prendre un certain temps : merci de patienter un instant avant la fin du processus!";
		self::$trad["delete"]="Supprimer";
		self::$trad["notDeletedElements"]="Certains éléments n'ont pas été supprimé car vous n'avez pas les droits d'accès nécessaires";

		////	Visibilité d'un Objet : auteur et droits d'accès
		self::$trad["autor"]="Auteur";
		self::$trad["postBy"]="Posté par";
		self::$trad["guest"]="invité";
		self::$trad["creation"]="Création";
		self::$trad["modification"]="Modif.";
		self::$trad["objHistory"]="Historique de l'élément";
		self::$trad["all"]="tous";
		self::$trad["unknown"]="personne inconnue";
		self::$trad["accessRead"]="Lecture";
		self::$trad["readInfos"]="Accès en lecture";
		self::$trad["accessWriteLimit"]="Ecriture limitée";
		self::$trad["readLimitInfos"]="Possibilité d'ajouter des --OBJCONTENT--s, sans pouvoir modifier/supprimer les --OBJCONTENT--s créés par d'autres utilisateurs";
		self::$trad["accessWrite"]="Ecriture";
		self::$trad["writeInfos"]="Accès en écriture";
		self::$trad["writeInfosContainer"]="Accès en écriture : possibilité d'ajouter, modifier ou supprimer tous les --OBJCONTENT--s du -OBJLABEL-";
		self::$trad["autorPrivilege"]="Seul l'auteur ou l'administrateur peuvent modifier/supprimer le -OBJLABEL-";
		self::$trad["folderContent"]="contenu";

		////	Libellé des objets (cf. "MdlObject::objectType")
		self::$trad["OBJECTcontainer"]="conteneur";
		self::$trad["OBJECTelement"]="élément";
		self::$trad["OBJECTfolder"]="dossier";
		self::$trad["OBJECTdashboardNews"]="actualité";
		self::$trad["OBJECTdashboardPoll"]="sondage";
		self::$trad["OBJECTfile"]="fichier";
		self::$trad["OBJECTfileFolder"]="dossier";
		self::$trad["OBJECTcalendar"]="agenda";
		self::$trad["OBJECTcalendarEvent"]="evenement";
		self::$trad["OBJECTforumSubject"]="sujet";
		self::$trad["OBJECTforumMessage"]="message";
		self::$trad["OBJECTcontact"]="contact";
		self::$trad["OBJECTcontactFolder"]="dossier";
		self::$trad["OBJECTlink"]="favori";
		self::$trad["OBJECTlinkFolder"]="dossier";
		self::$trad["OBJECTtask"]="note";
		self::$trad["OBJECTtaskFolder"]="dossier";
		self::$trad["OBJECTuser"]="utilisateur";
	
		////	Envoi d'un email (nouvel utilisateur, notification de création d'objet, etc...)
		self::$trad["MAIL_hello"]="Bonjour";
		self::$trad["MAIL_noFooter"]="Ne pas signer le message";
		self::$trad["MAIL_noFooterInfo"]="Ne pas signer la fin du message <br>avec le nom de l'expéditeur et un lien vers l'espace";
		self::$trad["MAIL_hideRecipients"]="Masquer les destinataires";
		self::$trad["MAIL_hideRecipientsInfo"]="Par défaut, les destinataires sont visibles dans le message.<br>Attention car si l'option est coché et qu'il y a trop de destinataires, l'email peut arriver en Spam";
		self::$trad["MAIL_receptionNotif"]="Accusé de reception";
		self::$trad["MAIL_receptionNotifInfo"]="Demander un accusé de réception à l'ouverture de l'email. Notez que certaines messageries ne prennent pas en charge cette fonctionnalité.";
		self::$trad["MAIL_sendBy"]="Envoyé par";  // "Envoyé par" M. Trucmuche
		self::$trad["MAIL_sendOk"]="L'email a bien été envoyé";
		self::$trad["MAIL_sendNotif"]="L'email de notification a bien été envoyé";
		self::$trad["MAIL_notSend"]="L'email n'a pas pu être envoyé...";
		self::$trad["MAIL_fromTheSpace"]="depuis l'espace";//'depuis l'espace' Mon espace
		self::$trad["MAIL_elemCreatedBy"]="-OBJLABEL- créé par";//Dossier 'créé par' boby
		self::$trad["MAIL_elemModifiedBy"]="-OBJLABEL- modifié par";//Dossier 'modifié par' boby
		self::$trad["MAIL_elemAccessLink"]="Cliquez ici pour y accéder sur votre espace";
		
		////	Dossier & fichier
		self::$trad["gigaOctet"]="Go";
		self::$trad["megaOctet"]="Mo";
		self::$trad["kiloOctet"]="Ko";
		self::$trad["rootFolder"]="Dossier racine";
		self::$trad["rootFolderEditInfo"]="Ouvrez le parametrage de l'espace<br>pour pouvoir modifier les droits d'accès au dossier racine";
		self::$trad["addFolder"]="Nouveau dossier";
		self::$trad["download"]="Télécharger le fichier";
		self::$trad["downloadFolder"]="Télécharger le dossier";
		self::$trad["diskSpaceUsed"]="Espace disque utilisé";
		self::$trad["diskSpaceUsedModFile"]="Espace disque utilisé sur le module fichier";
		self::$trad["downloadAlert"]="Votre archive est trop volumineuse pour être téléchargée en journée (--ARCHIVE_SIZE--). Merci de relancer le download après";//"19h"

		////	Infos sur une personne
		self::$trad["civility"]="Civilité";
		self::$trad["name"]="Nom";
		self::$trad["firstName"]="Prénom";
		self::$trad["adress"]="Adresse";
		self::$trad["postalCode"]="Code postal";
		self::$trad["city"]="Ville";
		self::$trad["country"]="Pays";
		self::$trad["telephone"]="Téléphone";
		self::$trad["telmobile"]="Tél. mobile";
		self::$trad["mail"]="Email";
		self::$trad["function"]="Fonction";
		self::$trad["companyOrganization"]="Organisme / Société";
		self::$trad["lastConnection"]="Dernière connexion";
		self::$trad["lastConnection2"]="Connecté le";
		self::$trad["lastConnectionEmpty"]="Pas encore connecté";
		self::$trad["displayProfil"]="Afficher le profil";

		////	Captcha
		self::$trad["captcha"]="Recopier ici les 4 caracteres";
		self::$trad["captchaInfo"]="Merci de recopier les 4 caractères pour votre identification";
		self::$trad["captchaSpecify"]="Merci de spécifier l'identification visuelle à 4 caractères";
		self::$trad["captchaError"]="L'identification visuelle à 4 caractères est erronée";
		
		////	Rechercher
		self::$trad["searchSpecifyText"]="Merci de préciser des mots clés d'au moins 3 caractères";
		self::$trad["search"]="Rechercher";
		self::$trad["searchDateCrea"]="Date de création";
		self::$trad["searchDateCreaDay"]="moins d'un jour";
		self::$trad["searchDateCreaWeek"]="moins d'une semaine";
		self::$trad["searchDateCreaMonth"]="moins d'un mois";
		self::$trad["searchDateCreaYear"]="moins d'un an";
		self::$trad["searchOnSpace"]="Rechercher sur l'espace";
		self::$trad["advancedSearch"]= "Recherche avancée";
		self::$trad["advancedSearchSomeWords"]= "n'importe quel mot";
		self::$trad["advancedSearchAllWords"]= "tous les mots";
		self::$trad["advancedSearchExactPhrase"]= "expression exacte";
		self::$trad["keywords"]="Mots clés";
		self::$trad["listModules"]="Modules";
		self::$trad["listFields"]="Champs";
		self::$trad["listFieldsElems"]="Eléments concernés";
		self::$trad["noResults"]="Aucun résultat";
		
		////	Gestion des inscriptions d'utilisateur
		self::$trad["userInscription"]="m'inscrire sur l'espace";
		self::$trad["userInscriptionInfo"]="Créer un nouveau compte utilisateur, qui sera par la suite validé par un administrateur. Une notification par email vous sera dès lors envoyée.";
		self::$trad["userInscriptionSpace"]="m'inscrire sur l'espace";//.."trucmuche"
		self::$trad["userInscriptionRecorded"]="votre inscription a bien été enregistrée : elle sera validée dès que possible par l'administrateur de l'espace";
		self::$trad["userInscriptionOptionSpace"]="Formulaire d'inscription en page de connexion";
		self::$trad["userInscriptionOptionSpaceInfo"]="Les visiteurs peuvent demander à s'inscrire sur l'espace pour avoir un compte utilisateur : la demande doit être validée par l'administrateur de l'espace !";
		self::$trad["userInscriptionValidate"]="Valider l'inscription d'utilisateurs";
		self::$trad["userInscriptionValidateInfo"]="Valider l'inscription d'utilisateurs sur l'espace";
		self::$trad["userInscriptionInvalidateButton"]="Invalider les inscriptions";
		self::$trad["userInscriptionInvalidateMail"]="Votre compte n'a pas été validé sur";
		
		////	Importer ou Exporter : Contact OU Utilisateurs
		self::$trad["export"]="Export";
		self::$trad["import"]="Import";
		self::$trad["importExport_user"]="d'utilisateurs";
		self::$trad["importExport_contact"]="de contacts";
		self::$trad["exportFormat"]="format";
		self::$trad["specifyFile"]="Merci de spécifier un fichier";
		self::$trad["fileExtension"]="Le type de fichier n'est pas valide. Il doit être de type";
		self::$trad["importInfo"]="Sélectionnez les champs Agora à cibler grâce aux listes déroulantes de chaque colonne.";
		self::$trad["importNotif"]="Merci de sélectionner la colonne nom dans les listes déroulante";
		self::$trad["importNotif2"]="Merci de sélectionner au moins un élément à importer";
		self::$trad["importNotif3"]="Le champ agora à déjà été sélectionné sur une autre colonne (chaque champs agora ne peut être sélectionné qu'une fois)";

		////	Messages d'alerte / d'erreur
		self::$trad["NOTIF_identification"]="Identifiant ou mot de passe invalide";
		self::$trad["NOTIF_presentIp"]="Ce compte utilisateur est actuellement utilisé depuis un autre ordinateur, avec une autre adresse ip. Un compte ne peut être utilisé que sur un seul ordinateur en même temps.";
		self::$trad["NOTIF_noSpaceAccess"]="L'accès au site ne vous est pas autorisé car actuellement, vous n'êtes probablement affecté à aucun espace.";
		self::$trad["NOTIF_fileOrFolderAccess"]="Fichier/Dossier inaccessible";
		self::$trad["NOTIF_diskSpace"]="L'espace pour le stockage de vos fichiers est insuffisant, vous ne pouvez pas ajouter de fichier";
		self::$trad["NOTIF_fileVersion"]="Type de fichier différent de l'original";
		self::$trad["NOTIF_fileVersionForbidden"]="Type de fichier non autorisé";
		self::$trad["NOTIF_folderMove"]="Vous ne pouvez pas déplacer le dossier à l'intérieur de lui-même..!";
		self::$trad["NOTIF_duplicateName"]="Un dossier ou fichier avec le même nom existe déjà";
		self::$trad["NOTIF_fileName"]="Un fichier avec le même nom existe déjà, mais a été conservé (pas remplacé par le nouveau fichier)";
		self::$trad["NOTIF_chmodDATAS"]="Le dossier DATAS n'est pas accessible en écriture : un droit d'accès en ecriture doit être attribué au proprietaire et groupe du dossier (''chmod 775'')";
		self::$trad["NOTIF_usersNb"]="Vous ne pouvez pas créer de nouveau compte utilisateur : nombre limité à "; // "...limité à" 10
		self::$trad["NOTIF_update"]="Mise à jour effectuée le";

		////	Header / Messenger / Visio / Footer
		self::$trad["HEADER_displaySpace"]="Afficher l'espace";
		self::$trad["HEADER_displayAdmin"]="Affichage Administrateur";
		self::$trad["HEADER_displayAdminEnabled"]="Affichage Administrateur activé";
		self::$trad["HEADER_displayAdminInfo"]="Afficher tous les éléments présents sur cet espace (réservé aux admins)";
		self::$trad["HEADER_searchElem"]="Rechercher sur l'espace";
		self::$trad["HEADER_documentation"]="Guide d'utilisation";
		self::$trad["HEADER_disconnect"]="Déconnexion";
		self::$trad["HEADER_shortcuts"]="Raccourcis";
		self::$trad["MESSENGER_messenger"]="Messagerie instantanée";
		self::$trad["MESSENGER_messengerInfo"]="discussion avec plusieurs personnes";
		self::$trad["MESSENGER_connected"]="Connecté";
		self::$trad["MESSENGER_connectedNobody"]="personne n'est connecté";
		self::$trad["MESSENGER_connectedNobodyInfo"]="Plus personne n'est connecté : voir les anciens messages";
		self::$trad["MESSENGER_connectedSince"]="connecté depuis";//connecté depuis 12:45
		self::$trad["MESSENGER_sendAt"]="Envoyé à";
		self::$trad["MESSENGER_addMessageToSelection"]="Mon message aux personnes selectionnées";
		self::$trad["MESSENGER_addMessageTo"]="Mon message à";
		self::$trad["MESSENGER_addMessageNotif"]="Merci de spécifier un message";
		self::$trad["MESSENGER_visioProposeTo"]="Proposer un appel visio à";//..boby
		self::$trad["MESSENGER_visioProposeToSelection"]="Proposer un appel visio aux personnes sélectionnées";
		self::$trad["MESSENGER_visioProposalPending"]="La proposition de visio a bien été envoyée et une nouvelle fenêtre va être lancée : merci d'autoriser l'accès à votre webcam et microphone !";
		self::$trad["MESSENGER_visioProposalLanch"]="Lancer l'appel visio ?";
		self::$trad["MESSENGER_userProposeVisioCall"]="propose un appel visio : cliquer ici pour lancer la visio";//Boby.. "propose un appel visio"
		self::$trad["FOOTER_pageGenerated"]="page générée en";

		////	vueObjMenuEdit
		self::$trad["EDIT_notifNoSelection"]="Vous devez sélectionner au moins une personne ou un espace";
		self::$trad["EDIT_notifNoPersoAccess"]="Vous n'êtes pas affecté à l'élément. valider tout de même ?";
		self::$trad["EDIT_notifWriteAccess"]="Il doit y avoir au moins une personne, groupe ou un espace avec un accès en écriture";
		self::$trad["EDIT_parentFolderAccessError"]="Pensez à vérifiez les droits d'accès du dossier parent ''<i>--FOLDER_NAME--</i>'': S'il n'est pas aussi affecté à ''<i>--TARGET_LABEL--</i>'', le présent dossier ne lui sera pas accessible.";
		self::$trad["EDIT_accessRight"]="Droits d'accès";
		self::$trad["EDIT_accessRightContent"]="Droits d'accès au contenu";
		self::$trad["EDIT_spaceNoModule"]="Le module courant n'a pas encore été ajouté à cet espace";
		self::$trad["EDIT_allUsers"]="Tout les utilisateurs";
		self::$trad["EDIT_allUsersInfo"]="Droit d'acccès pour tous les utilisateurs de l'espace <i>--SPACENAME--</i>";
		self::$trad["EDIT_allUsersAndGuests"]="Tout les utilisateurs et invités";
		self::$trad["EDIT_allUsersAndGuestsInfo"]="Droit d'acccès pour tous les utilisateurs et invités de l'espace <i>--SPACENAME--</i><hr>Les invités ne peuvent avoir qu'un accès en lecture seule aux éléments (invité : personne se connectant à l'espace sans s'identifier).";
		self::$trad["EDIT_adminSpace"]="Administrateur : accès total à tous les éléments de l'espace";
		self::$trad["EDIT_showAllSpaceUsers"]="Voir tous les utilisateurs";
		self::$trad["EDIT_mySpaces"]="Afficher tous mes espaces";
		self::$trad["EDIT_notifMail"]="Notifier par email";
		self::$trad["EDIT_notifMail2"]="Envoyer un email de notification de création";
		self::$trad["EDIT_notifMailInfo"]="La notification sera envoyée aux personnes affectées à l'élément. Si vous ne souhaitez l'envoyer qu'à certains utilisateurs, utilisez l'option ''Spécifier les destinataires''.";
		self::$trad["EDIT_notifMailAddFiles"]="Joindre le/les fichiers à la notification";
		self::$trad["EDIT_notifMailSelect"]="Spécifier les destinataires des notifications";
		self::$trad["EDIT_notifMailMoreUsers"]="Afficher + d'utilisateurs";
		self::$trad["EDIT_accessRightSubFolders"]="Donner les mêmes droits d'accès aux sous-dossiers";
		self::$trad["EDIT_accessRightSubFolders_info"]="Etendre les droits d'accès aux sous-dossiers <br>(uniquement ceux accessibles en écriture)";
		self::$trad["EDIT_shortcut"]="Raccourci";
		self::$trad["EDIT_shortcutInfo"]="Afficher un raccourci dans la barre de menu";
		self::$trad["EDIT_attachedFile"]="Ajouter un fichier ou une image";
		self::$trad["EDIT_attachedFileInfo"]="Attacher une image, video ou autre fichier (peut être intégré au texte)";
		self::$trad["EDIT_attachedFileInsert"]="Intégrer dans le texte";
		self::$trad["EDIT_attachedFileInsertInfo"]="Intégrer l'image ou la video dans le texte.<br>L'insertion sera réalisé après validation du formulaire !<br>Les images doivent être au format jpg ou png et les vidéos au format mp4";
		self::$trad["EDIT_guestName"]="Votre Nom / Pseudo";
		self::$trad["EDIT_guestNameNotif"]="Merci de préciser un nom ou un pseudo";
		self::$trad["EDIT_guestElementRegistered"]="Merci pour votre proposition. Celle-ci sera examinée dans les plus brefs délais avant validation.";

		////	Formulaire d'installation
		self::$trad["INSTALL_dbConnect"]="Connexion à la base de données";
		self::$trad["INSTALL_dbHost"]="Nom du serveur MySql (Hostname)";
		self::$trad["INSTALL_dbName"]="Nom de la Base de Données";
		self::$trad["INSTALL_dbLogin"]="Nom d'utilisateur";
		self::$trad["INSTALL_adminAgora"]="Administrateur de l'Agora";
		self::$trad["INSTALL_dbErrorDbName"]="Attention : le nom de la base de donnée doit comporter de préférence uniquement des caractères alphanumériques, tirets ou underscores";
		self::$trad["INSTALL_dbErrorUnknown"]="La connexion au serveur de base de données a échoué : merci de vérifier les coordonnées de connexion.";
		self::$trad["INSTALL_dbErrorIdentification"]="L'identification au serveur de base de données à échoué";
		self::$trad["INSTALL_dbErrorAppInstalled"]="L'application a déjà été installée sur cette base de données. Merci de supprimer la BDD si vous souhaitez relancer l'installation.";
		self::$trad["INSTALL_PhpOldVersion"]="Agora-Project necessite une version plus recente de PHP";
		self::$trad["INSTALL_confirmInstall"]="Confirmer l'installation ?";
		self::$trad["INSTALL_installOk"]="Agora-Project a bien été installé !";
		self::$trad["INSTALL_spaceDescription"]="Espace de partage et de travail collaboratif";
		self::$trad["INSTALL_dataDashboardNews1"]="Bienvenue sur votre nouvel espace !";
		self::$trad["INSTALL_dataDashboardNews2"]="Cliquez ici pour inviter des personnes à vous y rejoindre";
		self::$trad["INSTALL_dataDashboardNews3"]="Partagez dès maintenant vos Fichiers, un Agenda et un Fil d'Actualités, gérer des Tâches et Projets, échanger des Contacts ou des Liens Internet, dialoguer sur un Forum ou une Messagerie Instantanée, envoyer des Newsletter, etc.";
		self::$trad["INSTALL_dataDashboardPoll"]="Que pensez-vous du nouvel outil de Sondage ?";
		self::$trad["INSTALL_dataDashboardPollA"]="Très intéressant !";
		self::$trad["INSTALL_dataDashboardPollB"]="Intéressant";
		self::$trad["INSTALL_dataDashboardPollC"]="Peu intéressant";
		self::$trad["INSTALL_dataCalendarEvt"]="Bienvenue sur votre espace !";
		self::$trad["INSTALL_dataForumSubject1"]="Bienvenue sur le forum !";
		self::$trad["INSTALL_dataForumSubject2"]="N'hésitez pas à partager vos questions ou évoquer les sujets sur lesquels vous souhaitez échanger.";
		
		////	MODULE_PARAMETRAGE DE L'AGORA
		////
		self::$trad["AGORA_headerModuleName"]="Paramétrage général";
		self::$trad["AGORA_generalSettings"]="Paramétrage général";
		self::$trad["AGORA_backupFull"]="Sauvegarder tous les fichiers";
		self::$trad["AGORA_backupNotif"]="La création du fichier de sauvegarde peut durer quelques minutes... et le téléchargement quelques dizaines de minutes.";
		self::$trad["AGORA_backupDb"]="Sauvegarder la base de données";
		self::$trad["AGORA_diskSpaceInvalid"]="L'espace disque pour les fichiers : doit être un entier";
		self::$trad["AGORA_confirmModif"]="Confirmez-vous les modifications ?";
		self::$trad["AGORA_name"]="Nom du site";
		self::$trad["AGORA_footerHtml"]="Texte en bas de page";
		self::$trad["AGORA_lang"]="Langue par défaut";
		self::$trad["AGORA_timezone"]="Fuseau horaire";
		self::$trad["AGORA_spaceName"]="Nom de l'espace principal";
		self::$trad["AGORA_diskSpaceLimit"]="Espace disque pour les fichiers";
		self::$trad["AGORA_logsTimeOut"]="Conservation de l'historique d'événements (logs)";
		self::$trad["AGORA_logsTimeOutInfo"]="La durée de conservation de l'historique des événements concerne l'ajout ou la modif des éléments. Les logs de suppression sont conservés 1 an minimum.";
		self::$trad["AGORA_visioHost"]="Serveur de visioconférence Jitsi";
		self::$trad["AGORA_visioHostInfo"]="Adresse du serveur de visioconférence Jitsi";
		self::$trad["AGORA_skin"]="Couleur de l'affichage général";
		self::$trad["AGORA_black"]="Mode sombre";
		self::$trad["AGORA_white"]="Mode clair";
		self::$trad["AGORA_wallpaperLogoError"]="L'image de fond d'écran et le logo doivent être au format .jpg ou .png";
		self::$trad["AGORA_deleteWallpaper"]="Supprimer le fond d'écran ?";
		self::$trad["AGORA_logo"]="Logo en bas de page";
		self::$trad["AGORA_logoUrl"]="URL";
		self::$trad["AGORA_logoConnect"]="Logo en page de connexion";
		self::$trad["AGORA_logoConnectInfo"]="Affiché au dessus du formulaire de connexion";
		self::$trad["AGORA_usersCommentLabel"]="Les utilisateurs peuvent commenter les éléments";
		self::$trad["AGORA_usersComment"]="commentaire";
		self::$trad["AGORA_usersComments"]="commentaires";
		self::$trad["AGORA_usersLikeLabel"]="Les utilisateurs peuvent <i>Aimer</i> les éléments (ou pas)";
		self::$trad["AGORA_usersLike_likeSimple"]="J'aime simple";
		self::$trad["AGORA_usersLike_likeOrNot"]="J'aime / J'aime pas";
		self::$trad["AGORA_usersLike_like"]="J'aime!";
		self::$trad["AGORA_usersLike_dontlike"]="Je n'aime pas";
		self::$trad["AGORA_mapTool"]="Outil de cartographie";
		self::$trad["AGORA_mapToolInfo"]="Outil de cartographie pour voir les utilisateurs et contacts sur une carte";
		self::$trad["AGORA_mapApiKey"]="API Key pour la catographie";
		self::$trad["AGORA_mapApiKeyInfo"]="API Key utilisé pour l'outil de cartographie Google Map. Plus d'infos sur https://developers.google.com/maps/documentation/javascript/get-api-key";
		self::$trad["AGORA_gSignin"]="Connexion optionnelle via Gmail (Sign-In)";
		self::$trad["AGORA_gSigninInfo"]="Les utilisateurs peuvent se connecter plus facilement à leur espace via leur compte Gmail (Sign-In) : Pour cela, le compte de l'utilisateur doit avoir comme identifiant une adresse <i>@gmail.com</i>";
		self::$trad["AGORA_gSigninClientId"]="Parametrage Google Sign-In : Client ID";
		self::$trad["AGORA_gSigninClientIdInfo"]="Paramètrage obligatoire pour la connexion via Gmail. Plus d'infos sur <a href='https://developers.google.com/identity/sign-in/' target='_blank'>https://developers.google.com/identity/sign-in/</a>";
		self::$trad["AGORA_gPeopleApiKey"]="Parametrage Google People : API KEY";
		self::$trad["AGORA_gPeopleApiKeyInfo"]="Parametrage obligatoire pour la récupération des contacts Gmail. Plus d'infos sur <a href='https://developers.google.com/people/' target='_blank'>https://developers.google.com/people/</a>";
		self::$trad["AGORA_messengerDisabled"]="Messagerie instantanée activée";
		self::$trad["AGORA_moduleLabelDisplay"]="Nom des modules dans la barre de menu principal";
		self::$trad["AGORA_personsSort"]="Trier les utilisateurs et contacts par";
		self::$trad["AGORA_versions"]="Versions";
		self::$trad["AGORA_dateUpdate"]="mis à jour le";
		self::$trad["AGORA_Changelog"]="Voir le journal des versions";
		self::$trad["AGORA_funcMailDisabled"]="Fonction PHP pour envoyer des emails : désactivée !";
		self::$trad["AGORA_funcMailInfo"]="Certains hébergeurs désactivent la fonction PHP d'envoi d'emails pour des raisons de sécurité ou de saturation des serveurs (SPAM)";
		self::$trad["AGORA_funcImgDisabled"]="Fonction de manipulation d'images et de vignettes (PHP GD2) : désactivée !";
		//SMTP
		self::$trad["AGORA_smtpLabel"]="Connexion SMTP & sendMail";
		self::$trad["AGORA_sendmailFrom"]="Email dans le champ 'From'";
		self::$trad["AGORA_sendmailFromPlaceholder"]="ex: 'noreply@mon-domaine.fr'";
		self::$trad["AGORA_smtpHost"]="Adresse sur serveur (hostname)";
		self::$trad["AGORA_smtpPort"]="Port sur serveur";
		self::$trad["AGORA_smtpPortInfo"]="'25' par défaut. '587' ou '465' pour une connexion SSL/TLS";
		self::$trad["AGORA_smtpSecure"]="Type de connexion chiffrée (optionnel)";
		self::$trad["AGORA_smtpSecureInfo"]="'ssl' ou 'tls'";
		self::$trad["AGORA_smtpUsername"]="Nom d'utilisateur";
		self::$trad["AGORA_smtpPass"]="Mot de passe";
		//LDAP
		self::$trad["AGORA_ldapLabel"]="Connexion à un serveur LDAP";
		self::$trad["AGORA_ldapHost"]="Adresse sur serveur (hostname)";
		self::$trad["AGORA_ldapPort"]="Port sur serveur";
		self::$trad["AGORA_ldapPortInfo"]="''389'' par défaut";
		self::$trad["AGORA_ldapLogin"]="Chaine de connexion pour l'admin";
		self::$trad["AGORA_ldapLoginInfo"]="par exemple ''uid=admin,ou=mon_entreprise''";
		self::$trad["AGORA_ldapPass"]="Mot de passe de l'admin";
		self::$trad["AGORA_ldapDn"]="Groupe d'utilisateurs / base DN";
		self::$trad["AGORA_ldapDnInfo"]="Emplacement des utilisateurs dans l'annuaire.<br>Par exemple ''ou=users,o=mon_organisme''";
		self::$trad["AGORA_ldapConnectError"]="Erreur de connexion au serveur LDAP !";
		self::$trad["AGORA_ldapCreaAutoUsers"]="Création auto d'utilisateurs, si identification";
		self::$trad["AGORA_ldapCreaAutoUsersInfo"]="Créer automatiquement un utilisateur s'il est absent de l'Agora mais présent sur le serveur LDAP : il sera alors affecté aux espaces accessibles à ''tous les utilisateurs du site''.<br>Dans le cas contraire, l'utilisateur ne sera pas créé.";
		self::$trad["AGORA_ldapPassEncrypt"]="Password cryptés sur le serveur";
		self::$trad["AGORA_ldapDisabled"]="Le module PHP de connexion à un serveur LDAP n'est pas installé"; 

		////	MODULE_LOG
		////
		self::$trad["LOG_headerModuleName"]="Logs";
		self::$trad["LOG_moduleDescription"]="Historique des événements (logs)";
		self::$trad["LOG_path"]="Chemin";
		self::$trad["LOG_filter"]="Filtre";
		self::$trad["LOG_date"]="Date/Heure";
		self::$trad["LOG_spaceName"]="Espace";
		self::$trad["LOG_moduleName"]="Module";
		self::$trad["LOG_objectType"]="type d'objet";
		self::$trad["LOG_action"]="Action";
		self::$trad["LOG_userName"]="Utilisateur";
		self::$trad["LOG_ip"]="IP";
		self::$trad["LOG_comment"]="Commentaire";
		self::$trad["LOG_noLogs"]="Aucun log";
		self::$trad["LOG_filterSince"]="filtré à partir des";
		self::$trad["LOG_search"]="Chercher";
		self::$trad["LOG_connexion"]="connexion";//action
		self::$trad["LOG_add"]="ajout";			//action
		self::$trad["LOG_delete"]="suppression";//action
		self::$trad["LOG_modif"]="modification";//action

		////	MODULE_ESPACE
		////
		self::$trad["SPACE_headerModuleName"]="Espaces";
		self::$trad["SPACE_moduleInfo"]="Le site (l'espace principal) peut être subdivisée en plusieurs espaces distinct";
		self::$trad["SPACE_manageSpaces"]="Gérer les espaces du site";
		self::$trad["SPACE_config"]="Paramétrer l'espace";
		//Index
		self::$trad["SPACE_confirmDeleteDbl"]="Notez que seules les données affectées uniquement à cet espace seront effacées. Cependant si vous souhaitez les conserver, pensez d'abord à les réaffecter à un autre espace. Confirmez tout de même la suppression de cet espace ?";
		self::$trad["SPACE_space"]="espace";
		self::$trad["SPACE_spaces"]="espaces";
		self::$trad["SPACE_accessRightUndefined"]="A définir !";
		self::$trad["SPACE_modules"]="Modules";
		self::$trad["SPACE_addSpace"]="Ajouter un espace";
		//Edit
		self::$trad["SPACE_usersAccess"]="Utilisateurs affectés à l'espace";
		self::$trad["SPACE_selectModule"]="Vous devez sélectionner au moins un module";
		self::$trad["SPACE_spaceModules"]="Modules de l'espace";
		self::$trad["SPACE_moduleRank"]="Déplacer le module pour modifier son ordre d'affichage dans la barre de menu";
		self::$trad["SPACE_publicSpace"]="Espace public";
		self::$trad["SPACE_publicSpaceInfo"]="Donne accès aux personnes qui n'ont pas de compte utilisateur : les ''invités''. Il est possible de spécifier un mot de passe pour protéger l'accès à l'espace. Les modules suivants seront inaccessibles aux invités : 'mail' et 'utilisateur' (si l'espace public n'a pas de mot de passe)";
		self::$trad["SPACE_publicSpaceNotif"]="Si votre espace public contient des données sensibles telles que des coordonnées personnelles (module Contact) ou des documents sensibles (module Fichier) : vous êtes tenu d'y ajouter un <u>mot de passe</u>, pour être conforme à la RGPD.<br><br>Le Règlement Général sur la Protection des Données est un règlement de l'Union Européenne constituant le texte de référence en matière de protection des données personnelles.";
		self::$trad["SPACE_usersInvitation"]="Les utilisateurs peuvent envoyer des invitations par email";
		self::$trad["SPACE_usersInvitationInfo"]="Tous les utilisateurs peuvent envoyer des invitations par email pour rejoindre l'espace";
		self::$trad["SPACE_allUsers"]="Tous les utilisateurs";
		self::$trad["SPACE_user"]="Utilisateur";
		self::$trad["SPACE_userInfo"]="Accès normal à l'espace";
		self::$trad["SPACE_admin"]="Administrateur";
		self::$trad["SPACE_adminInfo"]="Administrateur de l'espace : Accès en écriture à tous les éléments de l'espace + envoi d'invitations par email + création d'utilisateurs sur l'espace";

		////	MODULE_UTILISATEUR
		////
		// Menu principal
		self::$trad["USER_headerModuleName"]="Utilisateurs";
		self::$trad["USER_moduleDescription"]="Utilisateurs de l'espace";
		self::$trad["USER_option_allUsersAddGroup"]="Tous les utilisateurs peuvent créer des groupes";//OPTION!
		//Index
		self::$trad["USER_allUsers"]="Gérer tous les utilisateurs du site";
		self::$trad["USER_allUsersInfo"]="Gérer tous les utilisateurs du site : de tous les espaces<br>(réservé à l'administrateur général)";
		self::$trad["USER_spaceUsers"]="Gérer les utilisateurs de l'espace";
		self::$trad["USER_deleteDefinitely"]="Supprimer définitivement";
		self::$trad["USER_deleteFromCurSpace"]="Désaffecter de l'espace courant";
		self::$trad["USER_deleteFromCurSpaceConfirm"]="Confirmer la désaffectation de l'utilisateur à l'espace courant ?";
		self::$trad["USER_allUsersOnSpaceNotif"]="Tous les utilisateurs ont été affectés à cet espace";
		self::$trad["USER_user"]="Utilisateur";
		self::$trad["USER_users"]="utilisateurs";
		self::$trad["USER_addExistUser"]="Ajouter un utilisateur existant";
		self::$trad["USER_addExistUserTitle"]="Ajouter à l'espace courant un utilisateur déjà existant (affecter à l'espace courant)";
		self::$trad["USER_addUser"]="Nouvel utilisateur";
		self::$trad["USER_addUserSite"]="Créer un utilisateur : affecté par défaut à aucun espace !";
		self::$trad["USER_addUserSpace"]="Créer un utilisateur pour l'espace courant";
		self::$trad["USER_sendCoords"]="Envoyer des identifiants";
		self::$trad["USER_sendCoordsInfo"]="Envoyer à des utilisateurs un email avec leur identifiant de connexion et un lien pour initialiser leur mot de passe";
		self::$trad["USER_sendCoordsInfo2"]="Envoyer à chaque nouvel utilisateur un email avec leurs coordonnées de connexion.";
		self::$trad["USER_sendCoordsConfirm"]="Confirmer l'envoi ?";
		self::$trad["USER_sendCoordsMail"]="Vos coordonnées de connexion à votre espace";
		self::$trad["USER_noUser"]="Aucun utilisateur affecté à cet espace pour le moment";
		self::$trad["USER_spaceList"]="Espaces de l'utilisateur";
		self::$trad["USER_spaceNoAffectation"]="aucun espace";
		self::$trad["USER_adminGeneral"]="Administrateur général";
		self::$trad["USER_adminSpace"]="Administrateur de l'espace";
		self::$trad["USER_userSpace"]="Utilisateur de l'espace";
		self::$trad["USER_profilEdit"]="Modifier le profil utilisateur";
		self::$trad["USER_myProfilEdit"]="Modifier mon profil utilisateur";
		// Invitations
		self::$trad["USER_sendInvitation"]="Envoyer des invitations par email";
		self::$trad["USER_sendInvitationInfo"]="Envoyer des invitations à votre entourage pour qu'ils vous rejoignent sur votre espace.<hr><img src='app/img/gSignin.png' height=15> Si vous possédez un compte Gmail, vous pourrez récupérer vos contacts Gmail pour envoyer des invitations.";
		self::$trad["USER_mailInvitationObject"]="Invitation de "; // ..Jean DUPOND
		self::$trad["USER_mailInvitationFromSpace"]="vous invite sur "; // Jean DUPOND "vous invite à rejoindre l'espace" Mon Espace
		self::$trad["USER_mailInvitationConfirm"]="Cliquer ici pour confirmer l'invitation";
		self::$trad["USER_mailInvitationWait"]="Invitation(s) en attente de confirmation";
		self::$trad["USER_exired_idInvitation"]="Le lien de votre invitation a expiré...";
		self::$trad["USER_invitPassword"]="Confirmez votre invitation";
		self::$trad["USER_invitPassword2"]="Choisissez votre mot de passe puis validez votre invitation";
		self::$trad["USER_invitationValidated"]="Votre invitation a été validée !";
		self::$trad["USER_gPeopleImport"]="Récupérer mes contacts Gmail";
		self::$trad["USER_importQuotaExceeded"]="Vous êtes limité à --USERS_QUOTA_REMAINING-- nouveaux comptes utilisateurs, sur un total de --LIMITE_NB_USERS-- utilisateurs";
		// groupes
		self::$trad["USER_spaceGroups"]="Groupes d'utilisateurs de l'espace";
		self::$trad["USER_spaceGroupsEdit"]="Editer les groupes d'utilisateurs de l'espace";
		self::$trad["USER_groupEditInfo"]="Chaque groupe peut être modifié par son auteur ou par l'admin de l'espace";
		self::$trad["USER_addGroup"]="Ajouter un groupe";
		self::$trad["USER_userGroups"]="Groupes de l'utilisateur";
		// Utilisateur_affecter
		self::$trad["USER_searchPrecision"]="Merci de préciser un nom, un prénom ou une adresse email";
		self::$trad["USER_userAffectConfirm"]="Confirmer les affectations ?";
		self::$trad["USER_userSearch"]="Rechercher des utilisateurs pour les ajouter à l'espace";
		self::$trad["USER_allUsersOnSpace"]="Tous les utilisateurs du site sont affectés à cet espace";
		self::$trad["USER_usersSpaceAffectation"]="Affecter des utilisateurs à l'espace :";
		self::$trad["USER_usersSearchNoResult"]="Aucun utilisateur pour cette recherche";
		// Utilisateur_edit & CO
		self::$trad["USER_langs"]="Langue";
		self::$trad["USER_persoCalendarDisabled"]="Agenda personnel désactivé";
		self::$trad["USER_persoCalendarDisabledInfo"]="Les agendas personnels de chaque utilisateur sont affichés par défaut, même si le module Agenda n'est pas activé sur l'espace. Cochez cette option pour ne pas afficher l'agenda personnel de cet utilisateur.";
		self::$trad["USER_connectionSpace"]="Espace affiché à la connexion";
		self::$trad["USER_loginAlreadyExist"]="L'identifiant / email existe déjà. Merci d'en spécifier un autre";
		self::$trad["USER_mailPresentInAccount"]="un compte utilisateur existe déjà avec cette adresse email";
		self::$trad["USER_loginAndMailDifferent"]="Les deux adresses email doivent être identiques";
		self::$trad["USER_mailNotifObject"]="Bienvenue sur ";  //.."mon-espace"
		self::$trad["USER_mailNotifContent"]="Votre compte utilisateur vient d'être créé sur";  //.."mon-espace"
		self::$trad["USER_mailNotifContent2"]="Connectez-vous ici avec les coordonnées suivantes";
		self::$trad["USER_mailNotifContent3"]="Merci de conserver précieusement cet email dans vos archives.";
		// Livecounter / Messenger
		self::$trad["USER_messengerEdit"]="Modifier la messagerie instantanée";
		self::$trad["USER_myMessengerEdit"]="Modifier ma messagerie instantanée";
		self::$trad["USER_livecounterVisibility"]="Personnes qui peuvent me voir en ligne<br> avec qui je peux discuter sur la messagerie instantanée";
		self::$trad["USER_livecounterDisabled"]="Personne ne peut me voir en ligne (messenger désactivé)";
		self::$trad["USER_livecounterAllUsers"]="Tout le monde peut me voir en ligne";
		self::$trad["USER_livecounterSomeUsers"]="Seuls certains utilisateurs peuvent me voir en ligne";

		////	MODULE_TABLEAU BORD
		////
		// Menu principal + options du module
		self::$trad["DASHBOARD_headerModuleName"]="News";
		self::$trad["DASHBOARD_moduleDescription"]="Actualités, Sondages et Nouveaux éléments";
		self::$trad["DASHBOARD_option_adminAddNews"]="Seul l'administrateur peut créer des actualités";//OPTION!
		self::$trad["DASHBOARD_option_disablePolls"]="Désactiver les sondages";//OPTION!
		self::$trad["DASHBOARD_option_adminAddPoll"]="Seul l'administrateur peut créer des sondages";//OPTION!
		//Index
		self::$trad["DASHBOARD_menuNews"]="Actualités";
		self::$trad["DASHBOARD_menuPolls"]="Sondages";
		self::$trad["DASHBOARD_menuElems"]="Nouveautés";
		self::$trad["DASHBOARD_addNews"]="Nouvelle actualité";
		self::$trad["DASHBOARD_newsOffline"]="Actualités archivées";
		self::$trad["DASHBOARD_noNews"]="Aucune actualité pour le moment";
		self::$trad["DASHBOARD_addPoll"]="Ajouter un sondage";
		self::$trad["DASHBOARD_pollsNotVoted"]="Sondages en cours";
		self::$trad["DASHBOARD_pollsNotVotedInfo"]="Afficher uniquement les sondages que vous n'avez pas encore voté";
		self::$trad["DASHBOARD_vote"]="Voter et voir les résultats !";
		self::$trad["DASHBOARD_voteTooltip"]="Le vote est anonyme : personne n'aura connaissance de votre choix";
		self::$trad["DASHBOARD_answerVotesNb"]="Voté --NB_VOTES-- fois";
		self::$trad["DASHBOARD_pollVotesNb"]="Le sondage a été voté --NB_VOTES-- fois";
		self::$trad["DASHBOARD_pollVotedBy"]="Le sondage a été voté par";//Bibi, boby, etc
		self::$trad["DASHBOARD_noPoll"]="Aucun sondage pour le moment";
		self::$trad["DASHBOARD_plugins"]="Nouveaux éléments";
		self::$trad["DASHBOARD_pluginsInfo"]="Eléments créés ou ayant lieu";//"aujourd'hui"..
		self::$trad["DASHBOARD_pluginsInfo2"]="entre le";//..ou "entre le 01/01/2020 et 07/01/2020"
		self::$trad["DASHBOARD_plugins_day"]="d'aujourd'hui";
		self::$trad["DASHBOARD_plugins_week"]="de cette semaine";
		self::$trad["DASHBOARD_plugins_month"]="du mois";
		self::$trad["DASHBOARD_plugins_previousConnection"]="depuis la dernière connexion";
		self::$trad["DASHBOARD_pluginsNew"]="Nouvel élément";
		self::$trad["DASHBOARD_pluginsCurrent"]="Elément courant";
		self::$trad["DASHBOARD_pluginsTooltipRedir"]="Afficher l'élément dans son dossier";
		self::$trad["DASHBOARD_pluginEmpty"]="Pas de nouvel element sur cette période";
		// Edition d'Actualite/News
		self::$trad["DASHBOARD_topNews"]="Actualité à la une";
		self::$trad["DASHBOARD_topNewsInfo"]="Actualité toujours affichée en haut de liste";
		self::$trad["DASHBOARD_offline"]="Actualité archivée";
		self::$trad["DASHBOARD_dateOnline"]="Mise en ligne programmée";
		self::$trad["DASHBOARD_dateOnlineInfo"]="Programmer une date de mise en ligne automatique.<br>Dans cette attente, l'actualité sera archivée";
		self::$trad["DASHBOARD_dateOnlineNotif"]="L'actualité est momentanément archivée, dans l'attente de sa mise en ligne automatique";
		self::$trad["DASHBOARD_dateOffline"]="Archivage programmé";
		self::$trad["DASHBOARD_dateOfflineInfo"]="Programmer une date d'archivage automatiquement de l'actualité";
		// Edition de Sondage/Polls
		self::$trad["DASHBOARD_titleQuestion"]="Titre / Question";
		self::$trad["DASHBOARD_multipleResponses"]="Plusieurs réponses possibles pour chaque vote";
		self::$trad["DASHBOARD_newsDisplay"]="Afficher avec les actualités, dans le menu de gauche";
		self::$trad["DASHBOARD_publicVote"]="Vote public : le choix de chaque votant est public";
		self::$trad["DASHBOARD_publicVoteInfos"]="Le choix de chaque votant sera affiché dans le résultat du sondage. Notez que le vote public peut être un frein à la participation au sondage.";
		self::$trad["DASHBOARD_dateEnd"]="Date de fin des votes";
		self::$trad["DASHBOARD_responseList"]="Responses possibles";
		self::$trad["DASHBOARD_responseNb"]="Response n°";
		self::$trad["DASHBOARD_addResponse"]="Ajouter une réponse";
		self::$trad["DASHBOARD_controlResponseNb"]="Merci de spécifier au moins 2 réponses possibles";
		self::$trad["DASHBOARD_votedPollNotif"]="Attention : dès que le sondage a commencé à être voté, il n'est plus possible de modifier le titre et les réponses du sondage";
		self::$trad["DASHBOARD_voteNoResponse"]="Merci de sélectionner une réponse";
	
		////	MODULE_AGENDA
		////
		// Menu principal
		self::$trad["CALENDAR_headerModuleName"]="Agenda";
		self::$trad["CALENDAR_moduleDescription"]="Agendas personnels et partagés";
		self::$trad["CALENDAR_option_adminAddRessourceCalendar"]="Seul l'administrateur peut créer des agendas partagés";//OPTION!
		self::$trad["CALENDAR_option_adminAddCategory"]="Seul l'administrateur peut créer des categories d'événement";//OPTION!
		self::$trad["CALENDAR_option_createSpaceCalendar"]="Créer un agenda partagé";//OPTION!
		self::$trad["CALENDAR_option_createSpaceCalendarInfo"]="L'agenda portera le même nom que l'espace<br>(option utile si les agendas des utilisateurs sont désactivés)";
		//Index
		self::$trad["CALENDAR_calsList"]="Agendas disponibles";
		self::$trad["CALENDAR_displayAllCals"]="Afficher tous les agendas (réservé aux administrateurs)";
		self::$trad["CALENDAR_hideAllCals"]="Masquer tous les agendas";
		self::$trad["CALENDAR_printCalendars"]="Imprimer l'agenda";
		self::$trad["CALENDAR_printCalendarsInfos"]="Imprimez la page en mode paysage";
		self::$trad["CALENDAR_addSharedCalendar"]="Créer un agenda partagé";
		self::$trad["CALENDAR_addSharedCalendarInfo"]="Créer un agenda partagé :<br>pour les réservation d'une salle, véhicule, vidéoprojecteur, etc.";
		self::$trad["CALENDAR_exportIcal"]="Exporter les événements (iCal)";
		self::$trad["CALENDAR_exportEvtMail"]="Envoyer les événements par email (iCal)";
		self::$trad["CALENDAR_exportEvtMailInfo"]="Pour les intégrer dans un calendrier IPHONE, ANDROID, OUTLOOK, GOOGLE CALENDAR...";
		self::$trad["CALENDAR_exportEvtMailList"]="liste des événements au format .Ical";
		self::$trad["CALENDAR_icalUrl"]="Adresse d'accès externe à l'agenda (Ical)";
		self::$trad["CALENDAR_icalUrlCopy"]="Copier cette adresse pour un accès en lecture à l'agenda à partir d'une autre application tel que Mozilla Thunderbird, Google Calendar, MacOS Calendar, etc";
		self::$trad["CALENDAR_importIcal"]="Importer des événements (iCal)";
		self::$trad["CALENDAR_importIcalState"]="Etat";
		self::$trad["CALENDAR_importIcalStatePresent"]="Déjà présent";
		self::$trad["CALENDAR_importIcalStateImport"]="A importer";
		self::$trad["CALENDAR_inputProposed"]="L'événement sera proposé au propriétaire de l'agenda";
		self::$trad["CALENDAR_displayDay"]="Jour";
		self::$trad["CALENDAR_display4Days"]="4 jours";
		self::$trad["CALENDAR_displayWorkWeek"]="Semaine ouvrée";
		self::$trad["CALENDAR_displayWeek"]="Semaine";
		self::$trad["CALENDAR_displayMonth"]="Mois";
		self::$trad["CALENDAR_weekNb"]="Voir la semaine n°"; //...5
		self::$trad["CALENDAR_periodNext"]="Période suivante";
		self::$trad["CALENDAR_periodPrevious"]="Période précédente";
		self::$trad["CALENDAR_evtAffects"]="Dans l'agenda de";
		self::$trad["CALENDAR_evtAffectToConfirm"]="Attente de confirmation pour les agendas : ";
		self::$trad["CALENDAR_evtProposedFor"]="Evenement proposé pour";//"Videoprojecteur" / "salle de réunion" / etc.
		self::$trad["CALENDAR_evtProposedForMe"]="Proposé pour mon agenda";
		self::$trad["CALENDAR_evtProposedBy"]="Proposé par";  // "Proposé par" M. Bidule
		self::$trad["CALENDAR_evtIntegrate"]="Intégrer l'événement à l'agenda ?";
		self::$trad["CALENDAR_evtNotIntegrate"]="Supprimer la proposition d'événement ?";
		self::$trad["CALENDAR_deleteEvtCal"]="Supprimer uniquement dans cet agenda?";
		self::$trad["CALENDAR_deleteEvtCals"]="Supprimer dans tous les agendas?";
		self::$trad["CALENDAR_deleteEvtDate"]="Supprimer uniquement à cette date?";
		self::$trad["CALENDAR_evtPrivate"]="Évènement privé";
		self::$trad["CALENDAR_evtAutor"]="Événements que j'ai créés";
		self::$trad["CALENDAR_noEvt"]="Aucun événement";
		self::$trad["CALENDAR_synthese"]="Synthèse des agendas";
		self::$trad["CALENDAR_calendarsPercentBusy"]="Agendas occupés";  // Agendas occupés : 2/5
		self::$trad["CALENDAR_noCalendarDisplayed"]="Aucun agenda affiché";
		// Evenement
		self::$trad["CALENDAR_category"]="Catégorie";
		self::$trad["CALENDAR_visibilityPublic"]="visibilité normale";
		self::$trad["CALENDAR_visibilityPublicHide"]="visibilité semi-privée";
		self::$trad["CALENDAR_visibilityPrivate"]="visibilité privée";
		self::$trad["CALENDAR_visibilityInfo"]="<u>visibilité semi-privée</u> : si l'évènement n'est accessible qu'en lecture, seule la plage horaire sera affichée (sans titre ni description).<br><br><u>visibilité privée</u> : visible uniquement pour l'auteur de l'événement";
		// Agenda : edit
		self::$trad["CALENDAR_timeSlot"]="Plage horaire de l'affichage ''semaine''";
		// Evenement : edit
		self::$trad["CALENDAR_noPeriodicity"]="Evenement ponctuel";
		self::$trad["CALENDAR_period_weekDay"]="Toutes les semaines";
		self::$trad["CALENDAR_period_month"]="Tous les mois";
		self::$trad["CALENDAR_period_dayOfMonth"]="du mois";//Le 21 du mois
		self::$trad["CALENDAR_period_year"]="Tous les ans";
		self::$trad["CALENDAR_periodDateEnd"]="Fin de périodicité";
		self::$trad["CALENDAR_periodException"]="Exception de périodicité";
		self::$trad["CALENDAR_calendarAffectations"]="Affectation aux agendas";
		self::$trad["CALENDAR_addEvt"]="Nouvel événement";
		self::$trad["CALENDAR_addEvtTooltip"]="Ajouter un événement à l'agenda";
		self::$trad["CALENDAR_addEvtTooltipBis"]="Ajouter l'événement à l'agenda";
		self::$trad["CALENDAR_proposeEvtTooltip"]="Proposer un événement au propriétaire de l'agenda";
		self::$trad["CALENDAR_proposeEvtTooltipBis"]="Proposer l'événement au propriétaire de cet agenda";
		self::$trad["CALENDAR_proposeEvtTooltipBis2"]="Proposer l'événement au propriétaire de l'agenda <br>(vous n'avez pas accès en écriture à cet agenda)";
		self::$trad["CALENDAR_verifCalNb"]="Merci de sélectionner au moins un agenda";
		self::$trad["CALENDAR_noModifInfo"]="Modification non autorisé (vous n'avez pas accès en écriture à cet agenda)";
		self::$trad["CALENDAR_editLimit"]="Vous n'êtes pas l'auteur de l'événement :<br> Vous ne pouvez donc gérer que les affectations à vos agendas";
		self::$trad["CALENDAR_busyTimeslot"]="Créneau est déjà occupé sur l'agenda suivant :";
		// Categories
		self::$trad["CALENDAR_editCategories"]="Editer les catégories d'événements";
		self::$trad["CALENDAR_editCategoriesRight"]="Chaque categorie peut être modifiée par son auteur ou par l'admin général";
		self::$trad["CALENDAR_addCategory"]="Ajouter une categorie";
		self::$trad["CALENDAR_filterByCategory"]="Filtrer les événements par catégorie";
		
		////	MODULE_FICHIER
		////
		// Menu principal
		self::$trad["FILE_headerModuleName"]="Fichiers";
		self::$trad["FILE_moduleDescription"]="Gestionnaire de fichiers";
		self::$trad["FILE_option_adminRootAddContent"]="Seul l'administrateur peut créer des dossiers et fichiers à la racine";//OPTION!
		//Index
		self::$trad["FILE_addFile"]="Nouveau fichier";
		self::$trad["FILE_addFileAlert"]="Dossier du serveur inaccessible en écriture!  merci de contacter l'administrateur";
		self::$trad["FILE_downloadSelection"]="télécharger la sélection";
		self::$trad["FILE_nbFileVersions"]="versions du fichier";//"55 versions du fichier"
		self::$trad["FILE_downloadsNb"]="Fichier Téléchargé --NB_DOWNLOAD-- fois";
		self::$trad["FILE_downloadedBy"]="Fichier Téléchargé par";//"..boby, will"
		self::$trad["FILE_addFileVersion"]="Ajouter nouvelle version du fichier";
		self::$trad["FILE_noFile"]="Aucun fichier pour le moment";
		// fichier_edit_ajouter  &  Fichier_edit
		self::$trad["FILE_fileSizeLimit"]="Les fichiers ne doivent pas dépasser"; // ...2 Mega Octets
		self::$trad["FILE_uploadSimple"]="Envoi simple";
		self::$trad["FILE_uploadMultiple"]="Envoi multiple";
		self::$trad["FILE_imgReduce"]="Optimiser les images";
		self::$trad["FILE_updatedName"]="Le nom du fichier est différent :<br>celui de la nouvelle version sera donc conservé";
		self::$trad["FILE_fileSizeError"]="Fichier trop volumineux";
		self::$trad["FILE_addMultipleFilesInfo"]="<br>Appuyez sur la touche 'Ctrl' pour sélectionner plusieurs fichiers";
		self::$trad["FILE_selectFile"]="Merci de sélectionner au moins un fichier";
		self::$trad["FILE_fileContent"]="contenu";
		// Versions_fichier
		self::$trad["FILE_versionsOf"]="Versions de"; // versions de fichier.gif
		self::$trad["FILE_confirmDeleteVersion"]="Confirmer la suppression de cette version ?";

		////	MODULE_FORUM
		////
		// Menu principal
		self::$trad["FORUM_headerModuleName"]="Forum";
		self::$trad["FORUM_moduleDescription"]="Forum de discussion";
		self::$trad["FORUM_option_adminAddSubject"]="Seul l'administrateur peut créer des sujets";//OPTION!
		self::$trad["FORUM_option_allUsersAddTheme"]="Tous les utilisateurs peuvent ajouter des thèmes";//OPTION!
		// TRI
		self::$trad["SORT_dateLastMessage"]="dernier message";
		//Index & Sujet
		self::$trad["FORUM_subject"]="sujet";
		self::$trad["FORUM_subjects"]="sujets";
		self::$trad["FORUM_message"]="message";
		self::$trad["FORUM_messages"]="messages";
		self::$trad["FORUM_lastSubject"]="dernier sujet de";
		self::$trad["FORUM_lastMessage"]="dernier message de";
		self::$trad["FORUM_noSubject"]="Aucun sujet pour le moment";
		self::$trad["FORUM_noMessage"]="Aucun message pour le moment";
		self::$trad["FORUM_subjectBy"]="Sujet de";
		self::$trad["FORUM_addSubject"]="Nouveau sujet";
		self::$trad["FORUM_displaySubject"]="Voir le sujet";
		self::$trad["FORUM_addMessage"]="Nouveau message";
		self::$trad["FORUM_quoteMessage"]="Répondre en citant ce message";
		self::$trad["FORUM_notifyLastPost"]="Me notifier à chaque message";
		self::$trad["FORUM_notifyLastPostInfo"]="M'envoyer un email de notification à chaque nouveau message";
		// Sujet_edit  &  Message_edit
		self::$trad["FORUM_accessRightInfos"]="Il est conseillé de sélectionner un accès en ''Ecriture limitée'' : l'accès en ''Ecriture'' est réservé aux modérateurs car il permet de modifier/supprimer tous les messages du sujet.";
		self::$trad["FORUM_themeSpaceAccessInfo"]="Le thème sélectionné est uniquement accessible aux espaces";
		// Themes
		self::$trad["FORUM_subjectTheme"]="Thème";
		self::$trad["FORUM_subjectThemes"]="Thèmes";
		self::$trad["FORUM_forumRoot"]="Accueil du forum";
		self::$trad["FORUM_forumRootResp"]="Accueil";
		self::$trad["FORUM_noTheme"]="Sans thème";
		self::$trad["FORUM_editThemes"]="Editer les thèmes de sujet";
		self::$trad["FORUM_editThemesInfo"]="Chaque theme peut être modifié par son auteur ou par l'administrateur général";
		self::$trad["FORUM_addTheme"]="Ajouter un theme";

		////	MODULE_TACHE
		////
		// Menu principal
		self::$trad["TASK_headerModuleName"]="Tâches";
		self::$trad["TASK_moduleDescription"]="Tâches & Notes";
		self::$trad["TASK_option_adminRootAddContent"]="Seul l'administrateur peut créer des dossiers et tâches à la racine";//OPTION!
		// TRI
		self::$trad["SORT_priority"]="Priorité";
		self::$trad["SORT_advancement"]="Avancement";
		self::$trad["SORT_dateBegin"]="Date de debut";
		self::$trad["SORT_dateEnd"]="Date de fin";
		//Index
		self::$trad["TASK_addTask"]="Nouvelle tâche";
		self::$trad["TASK_noTask"]="Aucune tâche pour le moment";
		self::$trad["TASK_advancement"]="Avancement";
		self::$trad["TASK_advancementAverage"]="Avancement moyen";
		self::$trad["TASK_priority"]="Priorité";
		self::$trad["TASK_priority1"]="Basse";
		self::$trad["TASK_priority2"]="Moyenne";
		self::$trad["TASK_priority3"]="Haute";
		self::$trad["TASK_priority4"]="Critique";
		self::$trad["TASK_responsiblePersons"]="Responsables";
		self::$trad["TASK_advancementLate"]="Avancement en retard";

		////	MODULE_CONTACT
		////
		// Menu principal
		self::$trad["CONTACT_headerModuleName"]="Contacts";
		self::$trad["CONTACT_moduleDescription"]="Annuaire de contacts";
		self::$trad["CONTACT_option_adminRootAddContent"]="Seul l'administrateur peut créer des dossiers et contacts à la racine";//OPTION!
		//Index
		self::$trad["CONTACT_addContact"]="Nouveau contact";
		self::$trad["CONTACT_noContact"]="Aucun contact pour le moment";
		self::$trad["CONTACT_createUser"]="Créer un utilisateur sur cet espace";
		self::$trad["CONTACT_createUserInfo"]="Créer un utilisateur sur cet espace à partir de ce contact ?";
		self::$trad["CONTACT_createUserConfirm"]="L'utilisateur a été créé";

		////	MODULE_LIEN
		////
		// Menu principal
		self::$trad["LINK_headerModuleName"]="Liens";
		self::$trad["LINK_moduleDescription"]="Liens Internet / favoris";
		self::$trad["LINK_option_adminRootAddContent"]="Seul l'administrateur peut créer des dossiers et liens à la racine";//OPTION!
		//Index
		self::$trad["LINK_addLink"]="Nouveau lien";
		self::$trad["LINK_noLink"]="Aucun lien pour le moment";
		// lien_edit & dossier_edit
		self::$trad["LINK_adress"]="Adresse web";

		////	MODULE_MAIL
		////
		// Menu principal
		self::$trad["MAIL_headerModuleName"]="Email";
		self::$trad["MAIL_moduleDescription"]="Envoi d'email aux utilisateurs et/ou contacts (Newsletter)";
		//Index
		self::$trad["MAIL_specifyMail"]="Merci de spécifier au moins un destinataire";
		self::$trad["MAIL_title"]="Titre de l'email";
		self::$trad["MAIL_attachedFile"]="Fichier joint";
		// Historique Email
		self::$trad["MAIL_mailHistory"]="Historique des emails envoyés";
		self::$trad["MAIL_mailHistoryEmpty"]="Aucun email";
		self::$trad["MAIL_recipients"]="Destinataires";
	}

	/*
	 * Jours Fériés de l'année (sur quatre chiffre)
	 */
	public static function celebrationDays($year)
	{
		// Init
		$dateList=array();

		//Fêtes mobiles (si la fonction de récup' de paques existe)
		if(function_exists("easter_date"))
		{
			$daySecondes=86400;
			$paquesTime=easter_date($year);
			$date=date("Y-m-d", $paquesTime+$daySecondes);
			$dateList[$date]="Lundi de pâques";
			$date=date("Y-m-d", $paquesTime+($daySecondes*39));
			$dateList[$date]="Jeudi de l'ascension";
			$date=date("Y-m-d", $paquesTime+($daySecondes*50));
			$dateList[$date]="Lundi de pentecôte";
		}

		//Fêtes fixes
		$dateList[$year."-01-01"]="Jour de l'an";
		$dateList[$year."-05-01"]="Fête du travail";
		$dateList[$year."-05-08"]="Armistice 39-45";
		$dateList[$year."-07-14"]="Fête nationale";
		$dateList[$year."-08-15"]="Assomption";
		$dateList[$year."-11-01"]="Toussaint";
		$dateList[$year."-11-11"]="Armistice 14-18";
		$dateList[$year."-12-25"]="Noël";

		//Retourne le résultat
		return $dateList;
	}
}