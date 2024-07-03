<?php
/*
 * Classe de traduction
 */
class Trad extends Txt
{
	/*
	 * Chargement les elements de traduction
	 */
	public static function loadTradsLang()
	{
		////	Dates formatées par PHP
		setlocale(LC_TIME, "fr_FR.utf8", "fr_FR.UTF-8", "fr_FR", "fr", "french");

		////	TRADUCTIONS
		self::$trad=array(
			////	Langue courante / Header http / Editeurs Tinymce / Documention pdf
			"CURLANG"=>"fr",
			"DATELANG"=>"fr_FR",
			"EDITORLANG"=>"fr_FR",
			"DOCFILE"=>"docs/DOCUMENTATION_FR.pdf",

			////	Divers
			"mainMenu"=>"Menu principal",
			"menuOptions"=>"Menu des options disponibles",
			"fillFieldsForm"=>"Merci de remplir les champs du formulaire",
			"requiredFields"=>"Champ obligatoire",
			"inaccessibleElem"=>"L'élément demandé n'est pas accessible",
			"warning"=>"Attention",
			"elemEditedByAnotherUser"=>"Ce formulaire est actuellement édité par",//"..bob"
			"yes"=>"oui",
			"no"=>"non",
			"none"=>"aucun",
			"or"=>"ou",
			"and"=>"et",
			"goToPage"=>"Aller à la page",
			"alphabetFilter"=>"Filtre alphabétique",
			"displayAll"=>"Tout afficher",
			"show"=>"Afficher",
			"hide"=>"Masquer",
			"byDefault"=>"Par défaut",
			"changeOrder"=>"Déplacer pour modifier l'ordre d'affichage",
			"mapLocalize"=>"Afficher sur une carte",
			"mapLocalizationFailure"=>"Echec de la localisation de l'adresse suivante",
			"mapLocalizationFailure2"=>"Merci de vérifier que l'adresse existe bien sur www.google.fr/maps ou www.openstreetmap.org",
			"sendMail"=>"Envoyer un email",
			"mailInvalid"=>"L'email n'est pas valide",
			"element"=>"élément",
			"elements"=>"éléments",
			"folder"=>"dossier",
			"folders"=>"dossiers",
			"close"=>"Fermer",
			"confirmCloseForm"=>"Fermer le formulaire sans enregistrer ?",
			"modifRecorded"=>"Les modifications ont bien été enregistrées",
			"confirm"=>"Confirmer ?",
			"comment"=>"Commentaire",
			"commentAdd"=>"Ajouter un commentaire",
			"optional"=>"(optionnel)",
			"objNew"=>"Elément créé récemment",
			"personalAccess"=>"Accès personnel",
			"copyUrl"=>"Copier l'adresse web de l'élément",
			"copyUrlTooltip"=>"L'adresse web de l'élément (URL) permet un accès direct à celui-ci : <br> pour être intégré à une actualité, un email, un message du forum, etc.",
			"copyUrlConfirmed"=>"L'adresse web a bien été copiée.",
			"cancel"=>"Annuler",

			////	images
			"picture"=>"Photo",
			"pictureProfil"=>"Photo de profil",
			"wallpaper"=>"Fond d'écran",
			"keepImg"=>"conserver l'image",
			"changeImg"=>"changer l'image",
			"pixels"=>"pixels",

			////	Connexion
			"specifyLoginPassword"=>"Merci de spécifier un identifiant et un mot de passe",//user connexion forms
			"specifyLogin"=>"Merci de spécifier un email/identifiant (sans espaces)",//user edit
			"mailLloginNotif"=>"Il est conseillé d'utiliser un email comme identifiant de connexion",//idem
			"mailLlogin"=>"Email / Identifiant de connexion",
			"connect"=>"Connexion",
			"connectAuto"=>"Se souvenir de moi",
			"connectAutoTooltip"=>"Retenir mon identifiant / mot de passe pour une connexion automatique",
			"gIdentityUserUnknown"=>"n'est pas enregistré sur l'espace",//"boby.smith@gmail.com" n'est pas enregistré sur l'espace
			"connectSpaceSwitch"=>"Me connecter à un autre espace",
			"connectSpaceSwitchConfirm"=>"Confirmer la déconnexion pour vous connecter à un autre espace ?",
			"guestAccess"=>"Connexion invité",
			"guestAccessTooltip"=>"Me connecter à cet espace en tant qu'invité",
			"publicSpacePasswordError"=>"Mot de passe erroné",
			"disconnectSpace"=>"Me déconnecter de l'espace",
			"disconnectSpaceConfirm"=>"Confirmer la déconnexion de l'espace principal ?",

			////	Password : connexion d'user / edition d'user / reset du password
			"password"=>"Mot de passe",
			"passwordModify"=>"Modifier le mot de passe",
			"passwordToModify"=>"Mot de passe temporaire (à modifier en page de connexion)",		//Mail d'envoi d'invitation
			"passwordToModify2"=>"Mot de passe (à modifier si besoin sur votre profil utilisateur)",//Mail de création de user
			"passwordVerif"=>"Confirmer mot de passe",
			"passwordTooltip"=>"Merci de remplir les champs uniquement si vous souhaitez changer de mot de passe",
			"passwordInvalid"=>"Votre mot de passe doit comporter des chiffres, lettres et au moins 6 caractères",
			"passwordConfirmError"=>"Votre confirmation de mot de passe n'est pas valide",
			"specifyPassword"=>"Merci de spécifier un mot de passe",
			"resetPassword"=>"Mot de passe oublié ?",
			"resetPassword2"=>"Indiquez votre adresse email pour réinitialiser votre mot de passe de connection",
			"resetPasswordNotif"=>"Un email vient de vous être envoyé pour réinitialiser votre mot de passe. Si vous ne l'avez pas reçu, vérifiez que votre email a correctement été saisi.",
			"resetPasswordMailTitle"=>"Réinitialiser votre mot de passe",
			"resetPasswordMailPassword"=>"Pour réinitialiser votre mot de passe et vous reconnecter",
			"resetPasswordMailPassword2"=>"merci de cliquer ici",
			"resetPasswordMailLoginRemind"=>"Rappel de votre identifiant de connexion",
			"resetPasswordIdExpired"=>"Le lien pour régénérer le mot de passe a expiré. Merci de recommencer la procédure",

			////	Type d'affichage
			"displayMode"=>"Affichage",
			"displayMode_line"=>"Lignes",
			"displayMode_block"=>"Blocs",
			
			////	Sélectionner / Déselectionner tous les éléments
			"select"=>"Sélectionner",
			"selectUnselect"=>"Sélectionner / Déselectionner",
			"selectAll"=>"Tout sélectionner",
			"selectNone"=>"Tout désélectionner",
			"selectSwitch"=>"Inverser la sélection",
			"deleteElems"=>"Supprimer la sélection",
			"changeFolder"=>"Déplacer vers un autre dossier",
			"showOnMap"=>"Voir les contacts sur une carte",
			"showOnMapTooltip"=>"Voir sur une carte les contacts avec une adresse, un code postal et une ville",
			"notifSelectUser"=>"Merci de sélectionner au moins un utilisateur",
			"notifSelectUsers"=>"Merci de sélectionner au moins 2 utilisateurs",
			"selectSpace"=>"Merci de sélectionner au moins un espace",
			"visibleAllSpaces"=>"Disponible sur tous les espaces",/*cf. Categories, themes, etc*/
			"visibleOnSpace"=>"Disponible sur l'espace",/*"..Mon espace"*/
			
			////	Temps ("de 11h à 12h", "le 25-01-2007 à 10h30", etc.)
			"from"=>"de",
			"at"=>"à",
			"the"=>"le",
			"begin"=>"Début",
			"end"=>"Fin",
			"beginEnd"=>"Début / Fin",
			"days"=>"jours",
			"day_1"=>"Lundi",
			"day_2"=>"Mardi",
			"day_3"=>"Mercredi",
			"day_4"=>"Jeudi",
			"day_5"=>"Vendredi",
			"day_6"=>"Samedi",
			"day_7"=>"Dimanche",
			"month_1"=>"janvier",
			"month_2"=>"fevrier",
			"month_3"=>"mars",
			"month_4"=>"avril",
			"month_5"=>"mai",
			"month_6"=>"juin",
			"month_7"=>"juillet",
			"month_8"=>"aout",
			"month_9"=>"septembre",
			"month_10"=>"octobre",
			"month_11"=>"novembre",
			"month_12"=>"décembre",
			"today"=>"aujourd'hui",
			"beginEndError"=>"La date de fin ne peut pas être antérieure à la date de début",
			"dateFormatError"=>"La date doit être au format jj/mm/AAAA",
			
			////	Menus d'édition des objets et editeur tinyMce
			"title"=>"Titre",
			"name"=>"Nom",
			"description"=>"Description",
			"specifyName"=>"Merci de spécifier un nom",
			"editorDraft"=>"Récupérer mon texte",
			"editorDraftConfirm"=>"Récuperer le dernier texte que j'ai saisi",
			"editorFileInsert"=>"Ajouter une image ou vidéo",
			"editorFileInsertNotif"=>"Merci de sélectionner une image au format jpg, png, gif ou une vidéo au format mp4 ou webm",
			
			////	Validation des formulaires
			"add"=>"Ajouter",
			"modify"=>"Modifier",
			"record"=>"Enregistrer",
			"modifyAndAccesRight"=>"Modifier l'élément et ses droits d'accès",
			"validate"=>"Valider",
			"send"=>"Envoyer",
			"sendTo"=>"Envoyer à",

			////	Tri d'affichage. Tous les éléments (dossier, tâche, lien, etc...) ont par défaut une date, un auteur & une description
			"sortBy"=>"Trié par",
			"sortBy2"=>"Trier par",
			"SORT_dateCrea"=>"date de création",
			"SORT_dateModif"=>"date de modif",
			"SORT_title"=>"titre",
			"SORT_description"=>"description",
			"SORT__idUser"=>"auteur",
			"SORT_extension"=>"type de fichier",
			"SORT_octetSize"=>"taille",
			"SORT_downloadsNb"=>"nb de téléchargements",
			"SORT_civility"=>"civilité",
			"SORT_name"=>"nom",
			"SORT_firstName"=>"prénom",
			"SORT_adress"=>"adresse",
			"SORT_postalCode"=>"code postal",
			"SORT_city"=>"ville",
			"SORT_country"=>"pays",
			"SORT_function"=>"fonction",
			"SORT_companyOrganization"=>"société / organisme",
			"SORT_lastConnection"=>"dernière connexion",
			"tri_ascendant"=>"Ascendant",
			"tri_descendant"=>"Descendant",

			////	Options de suppression
			"confirmDelete"=>"Confirmer la suppression définitive de l'élément ?",
			"confirmDeleteDbl"=>"Cette action est définitive : confirmer tout de même ?",
			"confirmDeleteSelect"=>"Voulez-vous supprimer définitivement les éléments sélectionnés ?",
			"confirmDeleteSelectNb"=>"éléments sélectionnés",//"55 éléments sélectionnés"
			"confirmDeleteFolderAccess"=>"Certains sous-dossiers ne vous sont pas accessibles, car affectés à d'autres utilisateurs : confirmer tout de même ?",
			"notifyBigFolderDelete"=>"La suppression des --NB_FOLDERS-- dossiers peut prendre un certain temps : merci de patienter un instant avant la fin du processus",
			"delete"=>"Supprimer",
			"notDeletedElements"=>"Certains éléments n'ont pas été supprimé car vous n'avez pas les droits d'accès nécessaires",

			////	Visibilité d'un Objet : auteur et droits d'accès
			"autor"=>"Auteur",
			"postBy"=>"par",
			"guest"=>"invité",
			"creation"=>"Création",
			"modification"=>"Modification",
			"createBy"=>"Créé par",
			"modifBy"=>"Modifié par",
			"objHistory"=>"Historique de l'élément",
			"all"=>"tous",
			"all2"=>"toutes",
			"deletedUser"=>"compte utilisateur supprimé",
			"folderContent"=>"contenu",
			"accessRead"=>"Lecture",
			"accessReadTooltip"=>"Accès en lecture",
			"accessWriteLimit"=>"Ecriture limitée",
			"accessWriteLimitTooltip"=>"Accès en écriture limité : chaque utilisateur ne peut modifier ou supprimer que les -OBJCONTENT-s qu'il a créé dans ce --OBJLABEL--.",
			"accessWrite"=>"Ecriture",
			"accessWriteTooltip"=>"Accès en écriture",
			"accessWriteTooltipContainer"=>"Accès en écriture : possibilité de modifier ou supprimer tous les -OBJCONTENT-s du --OBJLABEL--",
			"accessAutorPrivilege"=>"Seul l'auteur et les administrateurs peuvent modifier ou supprimer ce --OBJLABEL--",
			"accessRightsInherited"=>"Droits d'accès hérités du --OBJLABEL-- parent",
			"categoryNotifSpaceAccess"=>"n'est accessible que sur l'espace",//Ex: "Thème bidule -n'est accessible que sur l'espace- Machin"
			"categoryNotifChangeOrder"=>"L'ordre d'affichage a bien été modifié",

			////	Libellé des objets (cf. "MdlObject::objectType")
			"OBJECTcontainer"=>"conteneur",
			"OBJECTelement"=>"élément",
			"OBJECTfolder"=>"dossier",
			"OBJECTdashboardNews"=>"actualité",
			"OBJECTdashboardPoll"=>"sondage",
			"OBJECTfile"=>"fichier",
			"OBJECTfileFolder"=>"dossier",
			"OBJECTcalendar"=>"agenda",
			"OBJECTcalendarEvent"=>"événement",
			"OBJECTforumSubject"=>"sujet",
			"OBJECTforumMessage"=>"message",
			"OBJECTcontact"=>"contact",
			"OBJECTcontactFolder"=>"dossier",
			"OBJECTlink"=>"favori",
			"OBJECTlinkFolder"=>"dossier",
			"OBJECTtask"=>"tâche",
			"OBJECTtaskFolder"=>"dossier",
			"OBJECTuser"=>"utilisateur",

			////	Envoi d'un email
			"MAIL_sendOk"=>"L'email a bien été envoyé",				//ne pas modifier la cle de la trad ! (cf. "Tool::sendMail()")
			"MAIL_sendNotOk"=>"L'email n'a pas pu être envoyé...",	//Idem
			"MAIL_recipients"=>"Destinataires",						//Idem
			"MAIL_attachedFileError"=>"Le fichier n'a pas été ajouté à l'email car il est trop volumineux",//Idem
			"MAIL_hello"=>"Bonjour",
			"MAIL_hideRecipients"=>"Masquer les destinataires",
			"MAIL_hideRecipientsTooltip"=>"Mettre tous les destinataires en copie caché",
			"MAIL_addReplyTo"=>"Mettre mon email en réponse",
			"MAIL_addReplyToTooltip"=>"Ajouter mon email dans le champ ''Répondre à'' <br>Notez qu'avec cette option votre email peut arriver dans le dossier spam de certaines messageries",
			"MAIL_noFooter"=>"Ne pas signer le message",
			"MAIL_noFooterTooltip"=>"Ne pas signer la fin du message : nom de l'expéditeur et lien vers l'espace",
			"MAIL_receptionNotif"=>"Accusé de reception",
			"MAIL_receptionNotifTooltip"=>"Demander un accusé de réception à l'ouverture de l'email <br>Notez que certaines messageries ne prennent pas en charge cette fonctionnalité",
			"MAIL_specificMails"=>"Ajouter des adresses email",
			"MAIL_specificMailsTooltip"=>"Ajouter des adresses email non répertoriées sur l'espace",
			"MAIL_fileMaxSize"=>"L'ensemble de vos pièces jointes ne devraient pas dépasser 15 Mo, Certaines messageries pouvant refuser les emails au delà de cette limite. Envoyer tout de même ?",
			"MAIL_sendButton"=>"Envoyer l'email",
			"MAIL_sendBy"=>"Envoyé par",//"Envoyé par" M. Trucmuche
			"MAIL_sendNotif"=>"L'email de notification a bien été envoyé",
			"MAIL_fromTheSpace"=>"depuis l'espace",//"depuis l'espace Bidule"
			"MAIL_elemCreatedBy"=>"--OBJLABEL-- créé par",//Dossier 'créé par' boby
			"MAIL_elemModifiedBy"=>"--OBJLABEL-- modifié par",//Dossier modifié par 'Boby'
			"MAIL_elemAccessLink"=>"Cliquez ici pour y accéder sur votre espace",
			
			////	Dossier & fichier
			"gigaOctet"=>"Go",
			"megaOctet"=>"Mo",
			"kiloOctet"=>"Ko",
			"rootFolder"=>"Dossier principal",
			"rootFolderTooltip"=>"Pour modifier les droits d'accès du dossier principal, rendez-vous dans le menu ''Parametrer l'espace''",
			"addFolder"=>"Ajouter un dossier",
			"download"=>"Télécharger le fichier",
			"downloadFolder"=>"Télécharger le dossier",
			"diskSpaceUsed"=>"Espace disque utilisé",
			"diskSpaceUsedModFile"=>"Espace disque utilisé sur le module fichier",
			"downloadAlert"=>"Votre archive est trop volumineuse pour être téléchargée en journée (--ARCHIVE_SIZE--). Merci de relancer le download après",//"19h"

			////	Infos sur une personne
			"civility"=>"Civilité",
			"name"=>"Nom",
			"firstName"=>"Prénom",
			"adress"=>"Adresse",
			"postalCode"=>"Code postal",
			"city"=>"Ville",
			"country"=>"Pays",
			"telephone"=>"Téléphone",
			"telmobile"=>"Tél. mobile",
			"mail"=>"Email",
			"function"=>"Fonction",
			"companyOrganization"=>"Organisme / Société",
			"lastConnection"=>"Dernière connexion",
			"lastConnection2"=>"Connecté le",
			"lastConnectionEmpty"=>"Pas encore connecté",
			"displayProfil"=>"Afficher le profil",

			////	Captcha
			"captcha"=>"Recopier ici les 5 caracteres",
			"captchaTooltip"=>"Merci de recopier les 5 caractères pour votre identification",
			"captchaError"=>"L'identification visuelle est erronée (5 caractères à recopier)",
			
			////	Rechercher
			"searchSpecifyText"=>"Merci de préciser au moins 3 caractères (alphanumériques et sans caractères spéciaux)",
			"search"=>"Rechercher",
			"searchDateCrea"=>"Date de création",
			"searchDateCreaDay"=>"moins d'un jour",
			"searchDateCreaWeek"=>"moins d'une semaine",
			"searchDateCreaMonth"=>"moins d'un mois",
			"searchDateCreaYear"=>"moins d'un an",
			"searchOnSpace"=>"Rechercher sur l'espace",
			"advancedSearch"=>"Recherche avancée",
			"advancedSearchAnyWord"=>"n'importe quel mot",
			"advancedSearchAllWords"=>"tous les mots",
			"advancedSearchExactPhrase"=>"l'expression exacte",
			"keywords"=>"Mots clés",
			"listModules"=>"Modules",
			"listFields"=>"Champs",
			"listFieldsElems"=>"Eléments concernés",
			"noResults"=>"Aucun résultat",

			////	Inscription d'utilisateur
			"userInscription"=>"m'inscrire sur l'espace",
			"userInscriptionTooltip"=>"Créer un nouveau compte utilisateur, qui sera par la suite validé par un administrateur. Une notification par email vous sera dès lors envoyée.",
			"userInscriptionSpace"=>"M'inscrire sur l'espace",//.."trucmuche"
			"userInscriptionRecorded"=>"votre inscription a bien été enregistrée : elle sera validée dès que possible par l'administrateur de l'espace",
			"userInscriptionEmailSubject"=>"Nouvelle inscription sur l'espace",//"Mon espace"
			"userInscriptionEmailMessage"=>"Une nouvelle inscription a été demandée par <i>--NEW_USER_LABEL--</i> pour l'espace <i>--SPACE_NAME--</i> : <br><br><i>--NEW_USER_MESSAGE--<i> <br><br>Pensez à confirmer ou annuler cette inscription lors de votre prochaine connexion.",
			"userInscriptionEdit"=>"Formulaire d'inscription en page de connexion",
			"userInscriptionEditTooltip"=>"Les visiteurs peuvent demander à s'inscrire sur l'espace pour avoir un compte utilisateur&nbsp;: la demande est ensuite validée par l'administrateur de l'espace",
			"userInscriptionNotif"=>"Me notifier par email à chaque inscription",
			"userInscriptionNotifTooltip"=>"Envoyer une notification par mail aux administrateurs de l'espace après chaque inscription",
			"userInscriptionPulsate"=>"Inscriptions",
			"userInscriptionValidate"=>"Valider les demandes d'inscription",
			"userInscriptionValidateTooltip"=>"Valider les demandes d'inscription à l'espace",
			"userInscriptionSelectValidate"=>"Valider les inscriptions sélectionnées",
			"userInscriptionSelectInvalidate"=>"Ne pas confirmer les inscriptions sélectionnées",
			"userInscriptionInvalidateMail"=>"Désolé mais votre inscription n'a pas été validée sur",

			////	Importer ou Exporter : Contact OU Utilisateurs
			"importExport_user"=>"Importer / Exporter des utilisateurs",
			"import_user"=>"Importer des utilisateurs dans l'espace courant",
			"export_user"=>"Exporter les utilisateurs de l'espace courant",
			"importExport_contact"=>"Importer / Exporter des contacts",
			"import_contact"=>"Importer des contacts dans le dossier courant",
			"export_contact"=>"Exporter les contacts du dossier courant",
			"exportFormat"=>"au format",
			"specifyFile"=>"Merci de spécifier un fichier",
			"fileExtension"=>"Le type de fichier n'est pas valide. Il doit être de type",
			"importContactRootFolder"=>"Les contacts importés dans le dossier principal sont affectés par défaut à &quot;tous les utilisateurs de l'espace&quot;",//"Mon espace"
			"importInfo"=>"Sélectionnez les champs Agora à cibler grâce aux listes déroulantes de chaque colonne",
			"importNotif1"=>"Merci de sélectionner la colonne 'nom' dans les listes déroulante",
			"importNotif2"=>"Merci de sélectionner au moins un élément à importer",
			"importNotif3"=>"Le champ agora à déjà été sélectionné sur une autre colonne (chaque champs agora ne peut être sélectionné qu'une fois)",

			////	Messages d'erreur / Notifications
			"NOTIF_identification"=>"Identifiant ou mot de passe invalide",
			"NOTIF_presentIp"=>"Ce compte utilisateur est actuellement utilisé depuis un autre terminal, avec une autre adresse IP",
			"NOTIF_noAccessNoSpaceAffected"=>"Votre compte utilisateur a bien été identifié, mais vous n'êtes actuellement affecté à aucun espace. Merci de contacter l'administrateur pour vérifier vos droits d'accès",
			"NOTIF_noAccess"=>"Vous êtes déconnecté",
			"NOTIF_fileOrFolderAccess"=>"Fichier/Dossier inaccessible",
			"NOTIF_diskSpace"=>"L'espace pour le stockage de vos fichiers est insuffisant, vous ne pouvez pas ajouter de fichier",
			"NOTIF_fileVersion"=>"Type de fichier différent de l'original",
			"NOTIF_fileVersionForbidden"=>"Type de fichier non autorisé",
			"NOTIF_folderMove"=>"Vous ne pouvez pas déplacer le dossier à l'intérieur de lui-même !",
			"NOTIF_duplicateName"=>"Un élément avec le même nom existe déjà",//Cf. Nom de dossier, d'agenda, etc
			"NOTIF_fileName"=>"Un fichier avec le même nom existe déjà, mais a été conservé (pas remplacé par le nouveau fichier)",
			"NOTIF_chmodDATAS"=>"Le dossier DATAS n'est pas accessible en écriture : un droit d'accès en ecriture doit être attribué au proprietaire et groupe du dossier (''chmod 775'')",
			"NOTIF_usersNb"=>"Vous ne pouvez pas créer de nouveau compte utilisateur : nombre limité à ", // "...limité à" 10

			////	Header / Footer
			"HEADER_displaySpace"=>"espaces de travail",
			"HEADER_displayAdmin"=>"Affichage Administrateur",
			"HEADER_displayAdminEnabled"=>"Affichage Administrateur activé",
			"HEADER_displayAdminInfo"=>"Cette option permet d'afficher également les éléments de l'espace qui ne vous sont pas attribués",
			"HEADER_searchElem"=>"Rechercher sur l'espace",
			"HEADER_documentation"=>"Guide d'utilisation",
			"HEADER_shortcuts"=>"Raccourcis",
			"FOOTER_pageGenerated"=>"page générée en",

			////	Messenger / Visio
			"MESSENGER_headerModuleName"=>"Messages",
			"MESSENGER_moduleDescription"=>"Messages instantanés : discutez en direct ou lancez une visioconférence avec les personnes connectées à l'espace",
			"MESSENGER_messengerTitle"=>"Messages instantanés : cliquer sur le nom d'une personne pour discuter ou lancer une visioconférence",
			"MESSENGER_messengerMultiUsers"=>"Discuter à plusieurs en sélectionnant mes interlocuteurs dans le volet de droite",
			"MESSENGER_connected"=>"Connecté",
			"MESSENGER_nobody"=>"Vous êtes actuellement la seule personne connectée à l'espace",
			"MESSENGER_messageFrom"=>"Message de",
			"MESSENGER_messageTo"=>"envoyé à",
			"MESSENGER_chatWith"=>"Discuter avec",
			"MESSENGER_addMessageToSelection"=>"Mon message aux personnes selectionnées",
			"MESSENGER_addMessageTo"=>"Mon message à",
			"MESSENGER_addMessageNotif"=>"Merci de spécifier un message",
			"MESSENGER_visioProposeTo"=>"Proposer une visioconférence à",//..boby
			"MESSENGER_visioProposeToSelection"=>"Proposer une visioconférence aux personnes sélectionnées",
			"MESSENGER_visioProposeToUsers"=>"Cliquer ici pour lancer la visioconférence entre",//"..Will & Boby"

			////	Lancer une Visio
			"VISIO_urlAdd"=>"Ajouter une visioconférence",
			"VISIO_urlCopy"=>"Copier le lien de la visioconférence",
			"VISIO_urlDelete"=>"Supprimer le lien de la visioconférence",
			"VISIO_launch"=>"Lancer la visioconférence",
			"VISIO_launchFromEvent"=>"Lancer la visioconférence de l'événement",
			"VISIO_urlMail"=>"Ajouter un lien pour lancer une nouvelle visiofonférence",
			"VISIO_launchTooltip"=>"Pensez à autoriser l'accès à votre webcam et microphone !",
			"VISIO_launchTooltip2"=>"Problèmes de caméra ou de micro au lancement de votre visioconférence ? Suivez le guide <img src='app/img/pdf.png'>",
			"VISIO_installJitsi"=>"Installez gratuitement l'application Jitsi pour lancer vos visioconférences",
			"VISIO_launchServerTooltip"=>"Choisissez le serveur secondaire si le serveur principal ne fonctionne pas comme souhaité :<br>Notez que vos interlocuteurs devront sélectionner le même serveur de visioconférence que vous.",
			"VISIO_launchServerMain"=>"Serveur de visio principal",
			"VISIO_launchServerAlt"=>"Serveur de visio secondaire",
			"VISIO_launchButton"=>"Lancer la visioconférence",

			////	VueObjEditMenuSubmit.php
			"EDIT_notifNoSelection"=>"Vous devez sélectionner au moins une personne ou un espace",
			"EDIT_notifNoPersoAccess"=>"Vous n'êtes pas affecté à l'élément. valider tout de même ?",
			"EDIT_parentFolderAccessError"=>"Vérifiez les droits d'accès du dossier parent  <br><i>--FOLDER_NAME--</i><br><br>  Il doit aussi y avoir un droit d'accès pour  <br><i>--SPACE_LABEL--</i> &nbsp;>&nbsp;  <i>--TARGET_LABEL--</i><br><br>  Sans quoi le présent dossier ne sera pas accessible !",
			"EDIT_accessRight"=>"Droits d'accès",
			"EDIT_accessRightContent"=>"Droits d'accès au contenu",
			"EDIT_spaceNoModule"=>"Le module courant n'a pas encore été ajouté à cet espace",
			"EDIT_allUsers"=>"Tout les utilisateurs",
			"EDIT_allUsersTooltip"=>"Droit d'acccès pour tous les utilisateurs de l'espace <i>--SPACENAME--</i>",
			"EDIT_allUsersAndGuests"=>"Tout les utilisateurs et invités",
			"EDIT_allUsersAndGuestsTooltip"=>"Droit d'acccès pour tous les utilisateurs et invités de l'espace <i>--SPACENAME--</i>.<hr>Les invités n'ont qu'un accès en lecture à l'espace (personnes sans compte utilisateur)",
			"EDIT_adminSpace"=>"Administrateur : accès total à tous les éléments de l'espace",
			"EDIT_showAllUsers"=>"Afficher tous les utilisateurs",
			"EDIT_showAllUsersAndSpaces"=>"Afficher tous les utilisateurs et espaces",
			"EDIT_notifMail"=>"Notifier par email",
			"EDIT_notifMail2"=>"Envoyer une notification par email",
			"EDIT_notifMailTooltip"=>"Envoyer une notification par email aux personnes affectées à l'élément (--OBJLABEL--)",
			"EDIT_notifMailTooltipCal"=>"<hr>Si vous affectez l'événement à des agendas personnels, alors la notification ne sera envoyée qu'aux propriétaires de ces agendas (accès en écriture).",
			"EDIT_notifMailAddFiles"=>"Joindre le/les fichiers à la notification",
			"EDIT_notifMailSelect"=>"Choisir les destinataires des notifications",
			"EDIT_accessRightSubFolders"=>"Donner les mêmes droits d'accès aux sous-dossiers",
			"EDIT_accessRightSubFoldersTooltip"=>"Etendre les droits d'accès aux sous-dossiers (uniquement ceux accessibles en écriture)",
			"EDIT_shortcut"=>"Raccourci",
			"EDIT_shortcutInfo"=>"Afficher un raccourci dans la barre de menu",
			"EDIT_attachedFile"=>"Fichiers joints",
			"EDIT_attachedFileAdd"=>"Joindre des fichiers",
			"EDIT_attachedFileInsert"=>"Insérer dans le texte",
			"EDIT_attachedFileInsertTooltip"=>"Insérer l'image dans le texte de l'éditeur (format .jpeg/.png/.gif/.mp4)",
			"EDIT_guestName"=>"Votre Nom / Pseudo",
			"EDIT_guestNameNotif"=>"Merci de préciser un nom ou un pseudo",
			"EDIT_guestMail"=>"Votre email",
			"EDIT_guestMailTooltip"=>"Merci de spécifier votre email pour la validation de votre proposition",
			"EDIT_guestElementRegistered"=>"Merci pour votre contribution : elle sera vérifiée prochainement avant d'être validée par un administrateur.",

			////	Formulaire d'installation
			"INSTALL_dbConnect"=>"Connexion à la base de données",
			"INSTALL_dbHost"=>"Serveur MariaDB ou MySql (Hostname)",
			"INSTALL_dbName"=>"Nom de la Base de Données",
			"INSTALL_dbLogin"=>"Nom d'utilisateur",
			"INSTALL_adminAgora"=>"Administrateur principal",
			"INSTALL_errorDbNameFormat"=>"Attention : le nom de la base de donnée doit comporter de préférence uniquement des caractères alphanumériques, tirets ou underscores",
			"INSTALL_errorDbConnection"=>"La connexion à la base de données a échoué : merci de vérifier les coordonnées de connexion",
			"INSTALL_errorDbExist"=>"Application déjà installée : <a href='index.php'>cliquez ici pour y accéder</a><br><br>Pour relancer l'install, pensez à supprimer la BDD",
			"INSTALL_errorDbNoSqlFile"=>"Le fichier d'installation db.sql n'est pas accessible ou a été supprimé car l'installation a déjà été effectuée",
			"INSTALL_PhpOldVersion"=>"Agora-Project --CURRENT_VERSION-- necessite une version plus recente de PHP",
			"INSTALL_confirmInstall"=>"Confirmer l'installation ?",
			"INSTALL_installOk"=>"Agora-Project a bien été installé !",
			// Premiers enregistrements en DB
			"INSTALL_agoraDescription"=>"Espace de partage et de travail collaboratif",
			"INSTALL_dataDashboardNews"=>  "<h3>Bienvenue sur votre nouvel espace de partage !</h3>
											<h4><img src='app/img/file/iconSmall.png'> Partagez dès maintenant vos fichiers dans le gestionnaire de fichiers</h4>
											<h4><img src='app/img/calendar/iconSmall.png'> Créez des événements sur l'agenda partagé ou votre agenda personnel</h4>
											<h4><img src='app/img/dashboard/iconSmall.png'> Développez le fil d'actualités de votre communauté</h4>
											<h4><img src='app/img/messenger.png'> Communiquez via le forum, la messagerie instantanée ou des visioconférences</h4>
											<h4><img src='app/img/task/iconSmall.png'> Centralisez vos tâches, projets et contacts</h4>
											<h4><img src='app/img/mail/iconSmall.png'> Envoyez des newsletters par email</h4>
											<h4><img src='app/img/pdf.png'> <a href='https://www.omnispace.fr/index.php?ctrl=offline&action=Documentation' target='_blank'>Consulter le guide d'utilisation</a></h4>
											<br><h3><img src='app/img/postMessage.png'> <a onclick=\"lightboxOpen('?ctrl=user&action=SendInvitation')\">Envoyer des invitations et développer ma communauté !</a></h3><br>",
			"INSTALL_dataDashboardPoll"=>"Que pensez-vous du fil d'actualité ?",
			"INSTALL_dataDashboardPollA"=>"Très intéressant !",
			"INSTALL_dataDashboardPollB"=>"Intéressant",
			"INSTALL_dataDashboardPollC"=>"Peu intéressant",
			"INSTALL_dataCalendarEvt"=>"Bienvenue sur votre espace !",
			"INSTALL_dataForumSubject1"=>"Bienvenue sur le forum !",
			"INSTALL_dataForumSubject2"=>"N'hésitez pas à partager vos questions sur ce forum et évoquer les sujets sur lesquels vous souhaitez échanger.",
			"INSTALL_dataTaskStatus1"=>"A faire",
			"INSTALL_dataTaskStatus2"=>"En cours",
			"INSTALL_dataTaskStatus3"=>"A valider",
			"INSTALL_dataTaskStatus4"=>"Terminé",

			////	MODULE_PARAMETRAGE DE L'AGORA
			////
			"AGORA_generalSettings"=>"Paramétrage général",
			"AGORA_versions"=>"Versions",
			"AGORA_dateUpdate"=>"mis à jour le",
			"AGORA_Changelog"=>"Voir le journal des versions",
			"AGORA_funcMailDisabled"=>"La fonction PHP pour envoyer des emails est désactivée",
			"AGORA_funcImgDisabled"=>"La librairie PHP GD2 pour la manipulation d'images est désactivée",
			"AGORA_backupFull"=>"Sauvegarde complète",
			"AGORA_backupFullTooltip"=>"Récupérer la sauvegarde complète de l'espace : ensemble des fichiers ainsi que la base de données",
			"AGORA_backupDb"=>"Sauvegarder la base de données",
			"AGORA_backupDbTooltip"=>"Récupérer uniquement la sauvegarde de la base de données de l'espace",
			"AGORA_backupConfirm"=>"Cette opération peut durer de nombreuses minutes : confirmer le téléchargement ?",
			"AGORA_diskSpaceInvalid"=>"L'espace disque pour les fichiers doit être un entier",
			"AGORA_visioHostInvalid"=>"L'adresse web du serveur de visioconférence est invalide : elle doit commencer par 'https'",
			"AGORA_mapApiKeyInvalid"=>"Si vous choisissez Google Map comme outil de cartographie, vous devez y spécifier un 'API Key'",
			"AGORA_gIdentityKeyInvalid"=>"Si vous choisissez la connexion optionnelle via Google, vous devez y spécifier un 'API Key' pour Google SignIn",
			"AGORA_confirmModif"=>"Confirmer les modifications ?",
			"AGORA_name"=>"Nom de l'espace principal",
			"AGORA_nameTooltip"=>"Nom affiché en page de connexion, dans les emails, etc.",
			"AGORA_description"=>"Description en page de connexion",
			"AGORA_footerHtml"=>"Texte en bas à gauche de chaque page",
			"AGORA_logo"=>"Logo en bas à droite de chaque page",
			"AGORA_logoUrl"=>"URL",
			"AGORA_logoConnect"=>"Logo en page de connexion",
			"AGORA_logoConnectTooltip"=>"Logo affiché en tête du formulaire de connexion",
			"AGORA_lang"=>"Langue par défaut",
			"AGORA_timezone"=>"Fuseau horaire",
			"AGORA_diskSpaceLimit"=>"Espace disque pour les fichiers",
			"AGORA_logsTimeOut"=>"Conservation de l'historique d'événements (logs)",
			"AGORA_logsTimeOutTooltip"=>"La durée de conservation de l'historique des événements concerne l'ajout ou la modif des éléments. Les logs de suppression sont conservés 1 an minimum.",
			"AGORA_visioHost"=>"Serveur de visioconférence Jitsi",
			"AGORA_visioHostTooltip"=>"Url du serveur de visioconférence principal. Exemple : https://framatalk.org ou https://meet.jit.si",
			"AGORA_visioHostAlt"=>"Serveur de visioconférence alternatif",
			"AGORA_visioHostAltTooltip"=>"Url du serveur de visioconférence alternatif : en cas d'indisponibilité du serveur Jitsi principal",
			"AGORA_skin"=>"Couleur de l'interface",
			"AGORA_black"=>"Mode sombre",
			"AGORA_white"=>"Mode clair",
			"AGORA_userMailDisplay"=>"Adresses email des utilisateurs visibles par tous",
			"AGORA_userMailDisplayTooltip"=>"Afficher/Masquer l'email dans le profil de chaque utilisateur, les notifications par mail, etc.<br>Note : l'administrateur principal pourra toujours consulter l'email de chaque utilisateur",
			"AGORA_moduleLabelDisplay"=>"Nom des modules dans la barre de menu",
			"AGORA_folderDisplayMode"=>"Affichage par défaut des dossiers",
			"AGORA_wallpaperLogoError"=>"Le fond d'écran et le logo doivent être au format .jpg ou .png",
			"AGORA_deleteWallpaper"=>"Supprimer le fond d'écran",
			"AGORA_usersCommentLabel"=>"Bouton ''commenter'' sur les éléments",
			"AGORA_usersComment"=>"commentaire",
			"AGORA_usersComments"=>"commentaires",
			"AGORA_usersLikeLabel"=>"Bouton ''J'aime'' sur les éléments",
			"AGORA_usersLike"=>"J'aime !",
			"AGORA_mapTool"=>"Outil de cartographie",
			"AGORA_mapToolTooltip"=>"Outil de cartographie pour voir les utilisateurs et contacts sur une carte",
			"AGORA_mapApiKey"=>"API Key pour la cartographie Google Map",
			"AGORA_mapApiKeyTooltip"=>"Parametrage obligatoire pour l'outil de cartographie Google Map : <br>https://developers.google.com/maps/ <br>https://developers.google.com/maps/documentation/javascript/get-api-key",
			"AGORA_gIdentity"=>"Option de connexion via Google",
			"AGORA_gIdentityTooltip"=>"Les utilisateurs ayant un identifiant avec une adresse <i>@gmail.com</i> pourront aussi se connecter via leur compte Google",
			"AGORA_gIdentityClientId"=>"API Key pour la connexion via Google",
			"AGORA_gIdentityClientIdTooltip"=>"Une 'API Key' est nécessaire pour la connexion via Google. Plus d'infos sur <a href='https://developers.google.com/identity/sign-in/web' target='_blank'>https://developers.google.com/identity/sign-in/web</a>",
			"AGORA_gPeopleApiKey"=>"API KEY pour importer les contacts Google",
			"AGORA_gPeopleApiKeyTooltip"=>"Une 'API Key' est nécessaire pour la récupération des contacts Google / Gmail. Plus d'infos sur <a href='https://developers.google.com/people/' target='_blank'>https://developers.google.com/people/</a>",
			"AGORA_messengerDisplay"=>"Messagerie instantanée",
			"AGORA_personsSort"=>"Trier les utilisateurs et contacts par",
			//SMTP
			"AGORA_smtpLabel"=>"Connexion SMTP & sendMail",
			"AGORA_sendmailFrom"=>"Email 'From'",
			"AGORA_sendmailFromPlaceholder"=>"Ex: 'noreply@mydomain.com'",
			"AGORA_smtpHost"=>"Adresse du serveur SMTP (hostname)",
			"AGORA_smtpPort"=>"Port sur serveur",
			"AGORA_smtpPortTooltip"=>"'25' par défaut. '587' ou '465' pour une connexion SSL/TLS",
			"AGORA_smtpSecure"=>"Type de connexion chiffrée (optionnel)",
			"AGORA_smtpSecureTooltip"=>"'ssl' ou 'tls'",
			"AGORA_smtpUsername"=>"Nom d'utilisateur",
			"AGORA_smtpPass"=>"Mot de passe",
			//LDAP
			"AGORA_ldapLabel"=>"Connexion à un serveur LDAP",
			"AGORA_ldapLabelTooltip"=>"Connexion à un serveur LDAP pour la création d'utilisateur sur votre espace : cf. option ''Import/export d'utilisateur'' du module ''Utilisateur''",
			"AGORA_ldapUri"=>"URI LDAP",
			"AGORA_ldapUriTooltip"=>"URI LDAP complet de la forme LDAP://hostname:port ou LDAPS://hostname:port pour le chiffrement SSL.",
			"AGORA_ldapPort"=>"Port du serveur",
			"AGORA_ldapPortTooltip"=>"Le port utilisé pour la connexion : ''389'' par défaut",
			"AGORA_ldapLogin"=>"DN de l'administrateur LDAP (Distinguished Name)",
			"AGORA_ldapLoginTooltip"=>"par exemple ''cn=admin,dc=mon-entreprise,dc=com''",
			"AGORA_ldapPass"=>"Mot de passe de l'administrateur LDAP",
			"AGORA_ldapDn"=>"DN du groupe d'utilisateurs (Distinguished Name)",
			"AGORA_ldapDnTooltip"=>"DN du groupe d'utilisateurs : emplacement des utilisateurs dans l'annuaire. Exemple ''ou=mon-groupe,dc=mon-entreprise,dc=com''",
			"importLdapFilterTooltip"=>"Filtre de recherche LDAP (cf. https://www.php.net/manual/function.ldap-search.php). Exemple ''(cn=*)'' ou ''(&(samaccountname=MONLOGIN)(cn=*))''",
			"AGORA_ldapDisabled"=>"Le module PHP de connexion à un serveur LDAP n'est pas installé",
			"AGORA_ldapConnectError"=>"Erreur de connexion au serveur LDAP !",

			////	MODULE_LOG
			////
			"LOG_moduleDescription"=>"Historique des événements (logs)",
			"LOG_path"=>"Chemin",
			"LOG_filter"=>"Filtre",
			"LOG_date"=>"Date/Heure",
			"LOG_spaceName"=>"Espace",
			"LOG_moduleName"=>"Module",
			"LOG_objectType"=>"type d'objet",
			"LOG_action"=>"Action",
			"LOG_userName"=>"Utilisateur",
			"LOG_ip"=>"IP",
			"LOG_comment"=>"Commentaire",
			"LOG_noLogs"=>"Aucun log",
			"LOG_filterSince"=>"filtré à partir des",
			"LOG_search"=>"Chercher",
			"LOG_connexion"=>"connexion",//action
			"LOG_add"=>"ajout",			//action
			"LOG_delete"=>"suppression",//action
			"LOG_modif"=>"modification",//action

			////	MODULE_ESPACE
			////
			"SPACE_moduleTooltip"=>"L'espace principal peut être subdivisé en plusieurs espaces,<br>appelés également ''sous-espace''",
			"SPACE_manageAllSpaces"=>"Gérer les espaces de travail",
			"SPACE_config"=>"Paramétrer l'espace",//.."mon espace"
			//Index
			"SPACE_confirmDeleteDbl"=>"Notez que seules les données affectées uniquement à cet espace seront effacées. Cependant si vous souhaitez les conserver, pensez d'abord à les réaffecter à un autre espace. Confirmez tout de même la suppression de cet espace ?",
			"SPACE_space"=>"espace",
			"SPACE_spaces"=>"espaces",
			"SPACE_accessRightUndefined"=>"A définir !",
			"SPACE_modules"=>"Modules",
			"SPACE_addSpace"=>"Créer un nouvel espace",
			//Edit
			"SPACE_userAdminAccess"=>"Utilisateurs et Administrateurs de l'espace",
			"SPACE_selectModule"=>"Vous devez sélectionner au moins un module",
			"SPACE_spaceModules"=>"Modules de l'espace",
			"SPACE_publicSpace"=>"Espace public : accès invité",
			"SPACE_publicSpaceTooltip"=>"Un espace public est ouvert aux personnes n'ayant pas de compte utilisateur (invités). Ils pourront alors accéder à l'espace depuis la page d'accueil. Vous pouvez spécifier un mot de passe pour protéger l'accès à cet espace public. Les modules 'Email' et 'Utilisateurs' ne sont pas disponibles pour les invités.",
			"SPACE_publicSpaceNotif"=>"Si votre espace public contient des données personnelles (téléphone, adresse, etc) pensez à spécifier un mot de passe pour être conforme à la RGPD : Règlement Général sur la Protection des Données",
			"SPACE_usersInvitation"=>"Les utilisateurs peuvent envoyer des invitations par email",
			"SPACE_usersInvitationTooltip"=>"Tous les utilisateurs peuvent envoyer des invitations par email pour rejoindre l'espace",
			"SPACE_allUsers"=>"Tous les utilisateurs",
			"SPACE_user"=>"Utilisateur",
			"SPACE_userTooltip"=>"Accès normal à l'espace",
			"SPACE_admin"=>"Administrateur",
			"SPACE_adminTooltip"=>"L'administrateur d'un espace est un utilisateur pouvant éditer ou supprimer tous les élements présents sur l'espace. Il peut également paramétrer l'espace, créer de nouveaux comptes utilisateurs, créer des groupes d'utilisateurs, envoyer des invitations par mail pour ajouter de nouveaux utilisateurs, etc.",

			////	MODULE_UTILISATEUR
			////
			// Menu principal
			"USER_headerModuleName"=>"Utilisateurs",
			"USER_moduleDescription"=>"Utilisateurs de l'espace",
			"USER_option_allUsersAddGroup"=>"Tous les utilisateurs peuvent créer des groupes",//OPTION
			//Index
			"USER_spaceOrAllUsersTooltip"=>"Gérer les utilisateurs de l'espace affiché / Gérer les utilisateurs de tous les espaces (réservé à l'administrateur principal)",
			"USER_spaceUsers"=>"Gérer les utilisateurs de l'espace courant",
			"USER_allUsers"=>"Gérer tous les utilisateurs",
			"USER_deleteDefinitely"=>"Supprimer définitivement",
			"USER_deleteFromCurSpace"=>"Ne plus affecter à l'espace courant",
			"USER_deleteFromCurSpaceConfirm"=>"Ne plus affecter l'utilisateur à l'espace courant ?",
			"USER_allUsersOnSpaceNotif"=>"Tous les utilisateurs ont été affectés à cet espace",
			"USER_user"=>"Utilisateur",
			"USER_users"=>"utilisateurs",
			"USER_addExistUser"=>"Ajouter un utilisateur existant",
			"USER_addExistUserTitle"=>"Ajouter à l'espace courant un utilisateur déjà existant (affecter à l'espace courant)",
			"USER_addUser"=>"Créer un nouvel utilisateur",
			"USER_addUserSite"=>"Créer un utilisateur : affecté par défaut à aucun espace !",
			"USER_addUserSpace"=>"Créer un utilisateur pour l'espace courant",
			"USER_sendCoords"=>"Envoyer des identifiants",
			"USER_sendCoordsTooltip"=>"Envoyer à plusieurs utilisateurs un email avec leur identifiant de connexion et un lien pour initialiser leur mot de passe",
			"USER_sendCoordsTooltip2"=>"Envoyer à chaque nouvel utilisateur un email avec leurs coordonnées de connexion.",
			"USER_sendCoordsConfirm"=>"Confirmer l'envoi ?",
			"USER_sendCoordsMail"=>"Vos coordonnées de connexion à votre espace",
			"USER_noUser"=>"Aucun utilisateur affecté à cet espace pour le moment",
			"USER_spaceList"=>"Espaces de l'utilisateur",
			"USER_spaceNoAffectation"=>"aucun espace",
			"USER_adminGeneral"=>"Administrateur principal",
			"USER_adminGeneralTooltip"=>"Attention : le droit d'accès ''Administrateur principal'' donne de nombreux privilèges et responsabilités, notament pour pouvoir éditer tous les éléments (agendas, dossiers, fichiers, etc), ainsi que tous les utilisateurs et espaces. Il est donc conseillé d'attribuer ce privilège à 2 ou 3 personnes maximum.<br><br>Pour des privilèges plus restreints, choississez plutôt le droit d'accès ''administrateur d'espace'' (cf. menu principal > ''Paramétrer l'espace'')",
			"USER_adminSpace"=>"Administrateur de l'espace",
			"USER_userSpace"=>"Utilisateur de l'espace",
			"USER_profilEdit"=>"Modifier le profil utilisateur",
			"USER_myProfilEdit"=>"Modifier mon profil utilisateur",
			// Invitations
			"USER_sendInvitation"=>"Envoyer des invitations par email",
			"USER_sendInvitationTooltip"=>"Envoyer des invitations à vos contacts, pour créer un compte utilisateur et rejoindre l'espace de travail.<hr><img src='app/img/google.png' height=15> Vous pouvez également envoyer des invitations à vos contacts Gmail",
			"USER_mailInvitationObject"=>"Invitation de", // ..Jean DUPOND
			"USER_mailInvitationFromSpace"=>"vous invite sur l'espace", // Jean DUPOND "vous invite à rejoindre l'espace" Mon Espace
			"USER_mailInvitationConfirm"=>"Cliquez ici pour confirmer l'invitation",
			"USER_mailInvitationWait"=>"Invitation(s) en attente de confirmation",
			"USER_exired_idInvitation"=>"Le lien de votre invitation a expiré...",
			"USER_invitPassword"=>"Confirmez votre invitation",
			"USER_invitPassword2"=>"Choisissez votre mot de passe puis validez votre invitation",
			"USER_invitationValidated"=>"Votre invitation a été validée !",
			"USER_gPeopleImport"=>"Récupérer mes contacts Google / Gmail",
			"USER_importQuotaExceeded"=>"Vous êtes limité à --USERS_QUOTA_REMAINING-- nouveaux comptes utilisateurs, sur un total de --LIMITE_NB_USERS-- utilisateurs",
			// groupes
			"USER_spaceGroups"=>"Groupes d'utilisateurs de l'espace",
			"USER_spaceGroupsEdit"=>"Editer les groupes d'utilisateurs de l'espace",
			"USER_groupEditInfo"=>"Chaque groupe peut être modifié par son auteur ou par l'admin de l'espace",
			"USER_addGroup"=>"Créer un nouveau groupe",
			"USER_userGroups"=>"Groupes de l'utilisateur",
			// Utilisateur_affecter
			"USER_searchPrecision"=>"Merci de préciser un nom, un prénom ou une adresse email",
			"USER_userAffectConfirm"=>"Confirmer les affectations ?",
			"USER_userSearch"=>"Rechercher des utilisateurs pour les ajouter à l'espace",
			"USER_allUsersOnSpace"=>"Tous les utilisateurs du site sont affectés à cet espace",
			"USER_usersSpaceAffectation"=>"Affecter des utilisateurs à l'espace :",
			"USER_usersSearchNoResult"=>"Aucun utilisateur pour cette recherche",
			"USER_usersSearchBack"=>"Retour",
			// Utilisateur_edit & CO
			"USER_langs"=>"Langue",
			"USER_persoCalendarDisabled"=>"Agenda personnel désactivé",
			"USER_persoCalendarDisabledTooltip"=>"Un agenda personnel est attribué par défaut à chaque utilisateur (affiché même si le module ''Agenda'' n'est pas activé sur l'espace). Cochez cette option pour désactiver l'agenda personnel de cet utilisateur.",
			"USER_connectionSpace"=>"Espace affiché à la connexion",
			"USER_loginExists"=>"L'identifiant / email existe déjà. Merci d'en spécifier un autre",
			"USER_mailPresentInAccount"=>"un compte utilisateur existe déjà avec cette adresse email",
			"USER_loginAndMailDifferent"=>"Les deux adresses email doivent être identiques",
			"USER_mailNotifObject"=>"Bienvenue sur ",  //.."mon-espace"
			"USER_mailNotifContent"=>"Votre compte utilisateur vient d'être créé sur",  //.."mon-espace"
			"USER_mailNotifContent2"=>"Connectez-vous ici avec les coordonnées suivantes",
			"USER_mailNotifContent3"=>"Pensez à conserver cet email dans vos archives",
			// Edition du Livecounter / Messenger / Visio
			"USER_messengerEdit"=>"Paramétrer ma messagerie instantanée",
			"USER_messengerEdit2"=>"Paramétrer la messagerie instantanée",
			"USER_livecounterVisibility"=>"Visibilité sur la messagerie instantanée et la visioconférence",
			"USER_livecounterAllUsers"=>"Afficher ma présence lorsque je suis connecté : messagerie/visio activées",
			"USER_livecounterDisabled"=>"Masquer ma présence lorsque je suis connecté : messagerie/visio désactivées",
			"USER_livecounterSomeUsers"=>"Seul certains utilisateurs peuvent me voir lorsque je suis connecté",

			////	MODULE_TABLEAU BORD
			////
			// Menu principal + options du module
			"DASHBOARD_headerModuleName"=>"News",
			"DASHBOARD_moduleDescription"=>"Actualités, Sondages et Nouveaux éléments",
			"DASHBOARD_option_adminAddNews"=>"Seul l'administrateur peut créer des actualités",//OPTION
			"DASHBOARD_option_disablePolls"=>"Désactiver les sondages",//OPTION
			"DASHBOARD_option_adminAddPoll"=>"Seul l'administrateur peut créer des sondages",//OPTION
			//Index
			"DASHBOARD_menuNews"=>"Actualités",
			"DASHBOARD_menuPolls"=>"Sondages",
			"DASHBOARD_menuElems"=>"Nouveautés",
			"DASHBOARD_addNews"=>"Créer une nouvelle actualité",
			"DASHBOARD_offlineNews"=>"Voir les actualités archivées",
			"DASHBOARD_offlineNewsNb"=>"actualités archivées",//"55 actualités archivées"
			"DASHBOARD_noNews"=>"Aucune actualité pour le moment",
			"DASHBOARD_addPoll"=>"Créer un nouveau sondage",
			"DASHBOARD_pollsVoted"=>"Voir uniquement les sondages votés",
			"DASHBOARD_pollsVotedNb"=>"sondages pour lesquels j'ai déjà voté",//"55 sondages..déjà voté"
			"DASHBOARD_vote"=>"Voter et voir les résultats !",
			"DASHBOARD_voteTooltip"=>"Le vote est anonyme : personne n'aura connaissance de votre choix",
			"DASHBOARD_answerVotesNb"=>"Voté --NB_VOTES-- fois",
			"DASHBOARD_pollVotesNb"=>"Le sondage a été voté --NB_VOTES-- fois",
			"DASHBOARD_pollVotedBy"=>"Voté par",//Bibi, boby, etc
			"DASHBOARD_noPoll"=>"Aucun sondage pour le moment",
			"DASHBOARD_plugins"=>"Nouveaux éléments créés",
			"DASHBOARD_pluginsTooltip"=>"Eléments créés",//.."aujourd'hui"
			"DASHBOARD_pluginsTooltip2"=>"entre le",//.."01/01/2020 et 07/01/2020"
			"DASHBOARD_plugins_day"=>"aujourd'hui",
			"DASHBOARD_plugins_week"=>"cette semaine",
			"DASHBOARD_plugins_month"=>"ce mois",
			"DASHBOARD_plugins_previousConnection"=>"depuis la dernière connexion",
			"DASHBOARD_pluginsTooltipRedir"=>"Afficher l'élément dans son dossier",
			"DASHBOARD_pluginEmpty"=>"Pas de nouvel element sur cette période",
			// Actualite/News
			"DASHBOARD_topNews"=>"Actualité à la une",
			"DASHBOARD_topNewsTooltip"=>"Actualité toujours affichée en haut de liste",
			"DASHBOARD_offline"=>"Actualité archivée",
			"DASHBOARD_dateOnline"=>"Mise en ligne programmée",
			"DASHBOARD_dateOnlineTooltip"=>"Programmer une date de mise en ligne automatique. Dans cette attente, l'actualité sera archivée",
			"DASHBOARD_dateOnlineNotif"=>"L'actualité est momentanément archivée, dans l'attente de sa mise en ligne automatique",
			"DASHBOARD_dateOffline"=>"Archivage programmé",
			"DASHBOARD_dateOfflineTooltip"=>"Programmer une date d'archivage automatiquement de l'actualité",
			// Sondage/Polls
			"DASHBOARD_titleQuestion"=>"Titre / Question",
			"DASHBOARD_multipleResponses"=>"Plusieurs réponses possibles pour chaque vote",
			"DASHBOARD_newsDisplay"=>"Afficher avec les actualités (menu de gauche)",
			"DASHBOARD_publicVote"=>"Le choix de chaque votant est public",
			"DASHBOARD_publicVoteInfos"=>"Le choix de chaque votant sera affiché dans le résultat du sondage. Notez que le vote public peut être un frein à la participation au sondage.",
			"DASHBOARD_dateEnd"=>"Fin des votes",//suivi d'une date
			"DASHBOARD_responseList"=>"Responses possibles",
			"DASHBOARD_responseNb"=>"Response n°",
			"DASHBOARD_addResponse"=>"Ajouter une réponse",
			"DASHBOARD_controlResponseNb"=>"Merci de spécifier au moins 2 réponses possibles",
			"DASHBOARD_votedPollNotif"=>"Attention : dès que le sondage a commencé à être voté, il n'est plus possible de modifier le titre et les réponses du sondage",
			"DASHBOARD_voteNoResponse"=>"Merci de sélectionner une réponse",
			"DASHBOARD_exportPoll"=>"Télécharger le résultat du sondage en pdf",
			"DASHBOARD_exportPollDate"=>"résultat du sondage en date du",

			////	MODULE_FICHIER
			////
			// Menu principal
			"FILE_headerModuleName"=>"Fichiers",
			"FILE_moduleDescription"=>"Gestionnaire de fichiers",
			"FILE_option_adminRootAddContent"=>"Seul l'administrateur peut créer des dossiers et fichiers à la racine",//OPTION
			//Index
			"FILE_addFile"=>"Ajouter un fichier",
			"FILE_addFileAlert"=>"Dossier du serveur inaccessible en écriture!  merci de contacter l'administrateur",
			"FILE_downloadSelection"=>"télécharger la sélection",
			"FILE_fileDownload"=>"Télécharger",
			"FILE_fileSize"=>"Taille du fichier",
			"FILE_imageSize"=>"Dimension de l'image",
			"FILE_nbFileVersions"=>"versions du fichier",//"55 versions du fichier"
			"FILE_downloadsNb"=>"(téléchargé --NB_DOWNLOAD-- fois)",
			"FILE_downloadedBy"=>"fichier téléchargé par",//"..boby, will"
			"FILE_addFileVersion"=>"Ajouter une nouvelle version du fichier",
			"FILE_noFile"=>"Aucun fichier pour le moment",
			// fichier_edit_ajouter  &  Fichier_edit
			"FILE_fileSizeLimit"=>"Les fichiers ne doivent pas dépasser", // ...2 Mega Octets
			"FILE_uploadSimple"=>"Envoi simple",
			"FILE_uploadMultiple"=>"Envoi multiple",
			"FILE_imgReduce"=>"Optimiser les images",
			"FILE_updatedName"=>"Le nom du fichier est différent : celui de la nouvelle version sera donc conservé",
			"FILE_fileSizeError"=>"Fichier trop volumineux",
			"FILE_addMultipleFilesTooltip"=>"Appuyez sur la touche 'Ctrl' pour sélectionner plusieurs fichiers",
			"FILE_selectFile"=>"Merci de sélectionner au moins un fichier",
			"FILE_fileContent"=>"contenu",
			// Versions_fichier
			"FILE_versionsOf"=>"Versions de", // versions de fichier.gif
			"FILE_confirmDeleteVersion"=>"Confirmer la suppression de cette version ?",

			////	MODULE_AGENDA
			////
			// Menu principal
			"CALENDAR_headerModuleName"=>"Agenda",
			"CALENDAR_moduleDescription"=>"Agendas partagés et personnels",
			"CALENDAR_option_adminAddRessourceCalendar"=>"Seul l'administrateur peut créer des agendas partagés",//OPTION
			"CALENDAR_option_adminAddCategory"=>"Seul l'administrateur peut créer des categories d'événement",//OPTION
			"CALENDAR_option_createSpaceCalendar"=>"Créer un agenda partagé pour l'espace",//OPTION
			"CALENDAR_moduleAlwaysEnabledInfo"=>"Les utilisateurs n'ayant pas désactivé leur agenda personnel dans leur profil utilisateur verront toujours le module Agenda dans la barre de menu",
			//Index
			"CALENDAR_calsList"=>"Agendas disponibles",
			"CALENDAR_calsListDisplayAll"=>"Afficher tous les agendas (réservé aux administrateurs)",
			"CALENDAR_hideAllCals"=>"Masquer tous les agendas",
			"CALENDAR_printCalendars"=>"Imprimer l'agenda",
			"CALENDAR_printCalendarsInfos"=>"Imprimez la page en mode paysage",
			"CALENDAR_addSharedCalendar"=>"Créer un agenda partagé",
			"CALENDAR_addSharedCalendarTooltip"=>"Créer un agenda partagé : pour les réservation d'une salle, véhicule, vidéoprojecteur, etc.",
			"CALENDAR_exportIcal"=>"Exporter les événements au format iCal",
			"CALENDAR_icalUrl"=>"Copier le lien (url) pour afficher l'agenda depuis un agenda externe",
			"CALENDAR_icalUrlCopy"=>"Permet un accès en lecture aux événements de l'agenda depuis un agenda externe tel que Thunderbird, Outlook, Google Calendar, etc.",
			"CALENDAR_importIcal"=>"Importer des événements au format iCal",
			"CALENDAR_ignoreOldEvt"=>"Ne pas importer les événements de plus d'un an",
			"CALENDAR_importIcalState"=>"Etat",
			"CALENDAR_importIcalStatePresent"=>"Déjà présent",
			"CALENDAR_importIcalStateImport"=>"A importer",
			"CALENDAR_display_day"=>"Jour",
			"CALENDAR_display_4Days"=>"4 jours",
			"CALENDAR_display_workWeek"=>"Semaine ouvrée",
			"CALENDAR_display_week"=>"Semaine",
			"CALENDAR_display_month"=>"Mois",
			"CALENDAR_weekNb"=>"Voir la semaine n°", //...5
			"CALENDAR_periodNext"=>"Période suivante",
			"CALENDAR_periodPrevious"=>"Période précédente",
			"CALENDAR_evtAffects"=>"Dans l'agenda de",
			"CALENDAR_evtAffectToConfirm"=>"Attente de confirmation dans l'agenda de",
			"CALENDAR_evtProposed"=>"Proposition d'événement à confirmer",
			"CALENDAR_evtProposedBy"=>"Proposé par",//..Mr SMITH
			"CALENDAR_evtProposedConfirm"=>"Confirmer la proposition",
			"CALENDAR_evtProposedConfirmBis"=>"La proposition d'événement a bien été ajouté à l'agenda",
			"CALENDAR_evtProposedConfirmMail"=>"Votre proposition d'événement a bien été confirmée par",
			"CALENDAR_evtProposedDecline"=>"Décliner la proposition",
			"CALENDAR_evtProposedDeclineBis"=>"La proposition a été décliné",
			"CALENDAR_evtProposedDeclineMail"=>"Votre proposition d'événement a été déclinée",
			"CALENDAR_deleteEvtCal"=>"Supprimer uniquement dans cet agenda?",
			"CALENDAR_deleteEvtCals"=>"Supprimer dans tous les agendas?",
			"CALENDAR_deleteEvtDate"=>"Supprimer uniquement à cette date?",
			"CALENDAR_evtPrivate"=>"Événement privé",
			"CALENDAR_evtAutor"=>"Événements que j'ai créés",
			"CALENDAR_evtAutorInfo"=>"Afficher uniquement les événements que j'ai créés",
			"CALENDAR_noEvt"=>"Aucun événement",
			"CALENDAR_synthese"=>"Synthèse des agendas",
			"CALENDAR_calendarsPercentBusy"=>"Agendas occupés",  // Agendas occupés : 2/5
			"CALENDAR_noCalendarDisplayed"=>"Aucun agenda affiché",
			// Evenement
			"CALENDAR_importanceNormal"=>"Importance normale",
			"CALENDAR_importanceHight"=>"Importance haute",
			"CALENDAR_visibilityPublic"=>"Visibilité normale",
			"CALENDAR_visibilityPrivate"=>"Visibilité privée",
			"CALENDAR_visibilityPublicHide"=>"Visibilité semi-privée",
			"CALENDAR_visibilityTooltip"=>"<u>visibilité privée</u> : événement uniquement visible par son auteur<br><br><u>visibilité semi-privée</u> : seule la plage horaire de l'événement est visible par les personnes n'y ayant accès qu'en lecture (titre et détails de l'événement masqués)",
			// Agenda/Evenement : edit
			"CALENDAR_sharedCalendarDescription"=>"Agenda partagé de l'espace",
			"CALENDAR_noPeriodicity"=>"Une seule fois",
			"CALENDAR_period_weekDay"=>"Toutes les semaines",
			"CALENDAR_period_month"=>"Tous les mois",
			"CALENDAR_period_year"=>"Tous les ans",
			"CALENDAR_periodDateEnd"=>"Fin de récurrence",
			"CALENDAR_periodException"=>"Exception de récurrence",
			"CALENDAR_calendarAffectations"=>"Affectation aux agendas",
			"CALENDAR_addEvt"=>"Créer un nouvel événement",
			"CALENDAR_addEvtTooltip"=>"Ajouter un événement à l'agenda",
			"CALENDAR_addEvtTooltipBis"=>"Ajouter l'événement à l'agenda",
			"CALENDAR_proposeEvtTooltip"=>"Proposer un événement à l'administrateur de l'agenda",
			"CALENDAR_proposeEvtTooltipBis"=>"Proposer l'événement à l'administrateur/propriétaire de cet agenda",
			"CALENDAR_proposeEvtTooltipBis2"=>"Proposer l'événement à l'administrateur/propriétaire de cet agenda : l'agenda ne vous est accessible qu'en lecture !",
			"CALENDAR_inputProposed"=>"L'événement sera d'abord proposé à l'administrateur/propriétaire de cet agenda, avant d'y être éventuellement ajouté",
			"CALENDAR_verifCalNb"=>"Merci de sélectionner au moins un agenda",
			"CALENDAR_noModifTooltip"=>"Modification non autorisé (vous n'avez pas accès en écriture à cet agenda)",
			"CALENDAR_editLimit"=>"Vous n'êtes pas l'auteur de l'événement : Vous ne pouvez donc gérer que les affectations à vos agendas",
			"CALENDAR_busyTimeslot"=>"Créneau est déjà occupé sur l'agenda suivant :",
			"CALENDAR_timeSlot"=>"Plage horaire pour l'affichage \"semaine\"",
			"CALENDAR_propositionNotif"=>"Me notifier par email à chaque propositions d'événement",
			"CALENDAR_propositionNotifTooltip"=>"Chaque proposition d'événement sera validé ou invalidé par l'administrateur de l'agenda.",
			"CALENDAR_propositionGuest"=>"Les invités peuvent proposer des événements",
			"CALENDAR_propositionGuestTooltip"=>"Pensez à sélectionnez 'tous les utilisateur et invités' dans les droits d'accès ci-dessous.",
			"CALENDAR_propositionEmailSubject"=>"Nouvel événement proposé par",//.."boby SMITH"
			"CALENDAR_propositionEmailMessage"=>"Nouvel événement proposé par --AUTOR_LABEL-- : &nbsp; <i><b>--EVT_TITLE_DATE--</b></i> <br><i>--EVT_DESCRIPTION--</i> <br>Accédez à votre espace pour confirmer ou annuler cette proposition",
			// Categorie : Catégories d'événement
			"CALENDAR_categoryMenuTooltip"=>"Afficher uniquement les événements avec la catégorie",
			"CALENDAR_categoryShowAll"=>"Toutes les catégories",
			"CALENDAR_categoryShowAllTooltip"=>"Afficher toutes les catégories",
			"CALENDAR_categoryUndefined"=>"Catégorie non définie",
			"CALENDAR_categoryEditTitle"=>"Editer les catégories d'événement",
			"CALENDAR_categoryEditInfo"=>"Chaque catégorie peut être modifiée par son auteur ou par l'admin général",
			"CALENDAR_categoryEditAdd"=>"Ajouter une catégorie",

			////	MODULE_FORUM
			////
			// Menu principal
			"FORUM_headerModuleName"=>"Forum",
			"FORUM_moduleDescription"=>"Forum de discussion",
			"FORUM_option_adminAddSubject"=>"Seul l'administrateur peut créer des sujets",//OPTION
			"FORUM_option_adminAddTheme"=>"Seul l'administrateur peut créer des thèmes",//OPTION
			// TRI
			"SORT_dateLastMessage"=>"dernier message",
			//Index & Sujet
			"FORUM_forumRoot"=>"Accueil du forum",
			"FORUM_subject"=>"sujet",
			"FORUM_subjects"=>"sujets",
			"FORUM_message"=>"message",
			"FORUM_messages"=>"messages",
			"FORUM_lastMessageFrom"=>"dernier de",
			"FORUM_noSubject"=>"Aucun sujet pour le moment",
			"FORUM_subjectBy"=>"Sujet de",
			"FORUM_addSubject"=>"Créer un nouveau sujet",
			"FORUM_displaySubject"=>"Voir le sujet",
			"FORUM_addMessage"=>"Ajouter un message",
			"FORUM_quoteMessage"=>"Répondre",
			"FORUM_quoteMessageInfo"=>"Répondre en citant ce message",
			"FORUM_notifyLastPost"=>"Me notifier à chaque message",
			"FORUM_notifyLastPostTooltip"=>"M'envoyer un email de notification à chaque nouveau message",
			// Sujet_edit  &  Message_edit
			"FORUM_notifOnlyReadAccess"=>"Attention : s'il n'y que des accès en lecture, personne ne pourra contribuer au sujet...",
			"FORUM_notifWriteAccess"=>"L'accès en ''Ecriture'' est destiné aux modérateurs du sujet :<br>Il est donc conseillé d'utiliser le droit en ''Ecriture limité''",
			// Categorie : Themes
			"FORUM_categoryMenuTooltip"=>"Afficher uniquement les sujets avec le thème",
			"FORUM_categoryShowAll"=>"Tous les thèmes",
			"FORUM_categoryShowAllTooltip"=>"Afficher tous les thèmes",
			"FORUM_categoryUndefined"=>"Thème non défini",
			"FORUM_categoryEditTitle"=>"Editer les thèmes du forum",
			"FORUM_categoryEditInfo"=>"Chaque theme peut être modifié par son auteur ou par l'admin général",
			"FORUM_categoryEditAdd"=>"Ajouter un theme",

			////	MODULE_TACHE
			////
			// Menu principal
			"TASK_headerModuleName"=>"Tâches",
			"TASK_moduleDescription"=>"Tâches / Notes",
			"TASK_option_adminRootAddContent"=>"Seul l'administrateur peut créer des dossiers et tâches à la racine",//OPTION
			"TASK_option_adminAddStatus"=>"Seul l'administrateur peut créer des statuts de tâches",//OPTION
			// TRI
			"SORT_priority"=>"Priorité",
			"SORT_advancement"=>"Avancement",
			"SORT_dateBegin"=>"Date de debut",
			"SORT_dateEnd"=>"Date de fin",
			//Index
			"TASK_addTask"=>"Créer une nouvelle tâche",
			"TASK_noTask"=>"Aucune tâche pour le moment",
			"TASK_advancement"=>"Avancement",
			"TASK_advancementAverage"=>"Avancement moyen",
			"TASK_priority"=>"Priorité",
			"TASK_priorityUndefined"=>"Priorité non défini",
			"TASK_priority1"=>"Basse",
			"TASK_priority2"=>"Normale",
			"TASK_priority3"=>"Haute",
			"TASK_assignedTo"=>"Assigné à",
			"TASK_advancementLate"=>"Avancement en retard",
			"TASK_folderDateBeginEnd"=>"Debut au plus tôt / Fin au plus tard",
			//Categorie : Statuts Kanban
			"TASK_categoryMenuTooltip"=>"Afficher uniquement les tâches avec le statut",
			"TASK_categoryShowAll"=>"Tous les statuts",
			"TASK_categoryShowAllTooltip"=>"Afficher tous les statuts",
			"TASK_categoryUndefined"=>"Statut non défini",
			"TASK_categoryEditTitle"=>"Editer les statuts des tâches",
			"TASK_categoryEditInfo"=>"Chaque statut Kanban peut être modifié par son auteur ou par l'admin général",
			"TASK_categoryEditAdd"=>"Ajouter un statut Kanban",

			////	MODULE_CONTACT
			////
			// Menu principal
			"CONTACT_headerModuleName"=>"Contacts",
			"CONTACT_moduleDescription"=>"Annuaire de contacts",
			"CONTACT_option_adminRootAddContent"=>"Seul l'administrateur peut créer des dossiers et contacts à la racine",//OPTION
			//Index
			"CONTACT_addContact"=>"Créer un nouveau contact",
			"CONTACT_noContact"=>"Aucun contact pour le moment",
			"CONTACT_createUser"=>"Créer un utilisateur sur cet espace",
			"CONTACT_createUserConfirm"=>"Créer un utilisateur sur cet espace à partir de ce contact ?",
			"CONTACT_createUserConfirmed"=>"L'utilisateur a été créé",

			////	MODULE_LIEN
			////
			// Menu principal
			"LINK_headerModuleName"=>"Liens",
			"LINK_moduleDescription"=>"Liens Internet et sites Internet favoris",
			"LINK_option_adminRootAddContent"=>"Seul l'administrateur peut créer des dossiers et liens à la racine",//OPTION
			//Index
			"LINK_addLink"=>"Créer un nouveau lien",
			"LINK_noLink"=>"Aucun lien pour le moment",
			// lien_edit & dossier_edit
			"LINK_adress"=>"Adresse web",

			////	MODULE_MAIL
			////
			// Menu principal
			"MAIL_headerModuleName"=>"Email",
			"MAIL_moduleDescription"=>"Envoi d'email aux utilisateurs et/ou contacts (Newsletter)",
			//Index
			"MAIL_specifyMail"=>"Merci de spécifier au moins un destinataire",
			"MAIL_title"=>"Sujet de l'email",
			"MAIL_description"=>"Message de l'email",
			// Historique Email
			"MAIL_historyTitle"=>"Historique des emails envoyés",
			"MAIL_delete"=>"Supprimer l'email",
			"MAIL_resend"=>"Renvoyer l'email",
			"MAIL_resendInfo"=>"Récupérer le contenu de cet email et l'intégrer directement dans l'éditeur pour un nouvel envoi",
			"MAIL_historyEmpty"=>"Aucun email pour le moment",
		);
	}

	/*
	 * Jours Fériés de l'année (sur quatre chiffre)
	 */
	public static function celebrationDays($year)
	{
		// Init
		$dateList=[];

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