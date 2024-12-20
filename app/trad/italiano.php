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
		////	Date formattate da PHP
		setlocale(LC_TIME, "it_IT.utf8", "it_IT.UTF-8", "it_IT", "it", "italiano");

		////	TRADUCTIONS
		self::$trad=array(
			////	Langue courante / Header http / Editeurs Tinymce / Documention pdf
			"CURLANG"=>"it",
			"DATELANG"=>"it_IT",
			"EDITORLANG"=>"it_IT",

			////	Divers
			"mainMenu"=>"Menu principale",
			"menuOptions"=>"Menu delle opzioni sisponibili",
			"fillFieldsForm"=>"Compilare i campi del modulo",
			"requiredFields"=>"Campi obbligatori",
			"inaccessibleElem"=>"Elemento inaccessibile",
			"warning"=>"Avviso",
			"elemEditedByAnotherUser"=>"L'elemento è stato modificato da",//"..bob".
			"yes"=>"Si",
			"no"=>"no",
			"none"=>"no",
			"or"=>"o",
			"and"=>"e",
			"goToPage"=>"Vai alla pagina",
			"alphabetFilter"=>"Filtro alfabetico",
			"displayAll"=>"Visualizza tutto",
			"show"=>"Mostra",
			"hide"=>"Nascondi",
			"byDefault"=>"Impostazione predefinita",
			"changeOrder"=>"Spostare per impostare ordine di visualizzazione dei moduli",
			"mapLocalize"=>"Localizza sulla mappa",
			"mapLocalizationFailure"=>"La localizzazione del seguente indirizzo non è riuscita",
			"mapLocalizationFailure2"=>"Verificare l'esistenza del seguente indirizzo su www.google.com/maps o www.openstreetmap.org",
			"sendMail"=>"Invia un e-mail",
			"mailInvalid"=>"Questa e-mail non è valida",
			"element"=>"elemento",
			"elements"=>"elementi",
			"folder"=>"cartella",
			"folders"=>"cartelle",
			"close"=>"Chiudi",
			"confirmCloseForm"=>"Sei sicuro di voler chiudere il modulo?",
			"modifRecorded"=>"Le modifiche sono state salvate",
			"confirm"=>"Conferma?",
			"comment"=>"Commento",
			"commentAdd"=>"Aggiungi un commento",
			"optional"=>"(opzionale)",
			"objNew"=>"Oggetto creato di recente",
			"personalAccess"=>"Accesso personale",
			"copyUrl"=>"Copia il link/url nell'elemento",
			"copyUrlTooltip"=>"L'indirizzo di condivisione (URL) consente l'accesso esterno da un'e-mail, un blog, ecc.",
			"copyUrlConfirmed"=>"L indirizzo web è stato copiato con successo",
			"cancel"=>"Annulla",

			////	images
			"picture"=>"Immagine",
			"pictureProfil"=>"Immagine del profilo",
			"wallpaper"=>"Sfondo",
			"keepImg"=>"Mantieni immagine",
			"changeImg"=>"Cambia immagine",
			"pixels"=>"pixels",

			////	Connexion
			"specifyLoginPassword"=>"Grazie per aver scelto un login e una password",//user connexion forms
			"specifyLogin"=>"Grazie per aver scelto un e-mail/login (senza spazi)",//user edit
			"mailLloginNotif"=>"Si consiglia di utilizzare un e-mail come ID di accesso",//idem
			"mailLlogin"=>"Email / Login",
			"connect"=>"Accedi",
			"connectAuto"=>"Ricordati di me",
			"connectAutoTooltip"=>"Ricorda i miei dati di accesso per la connessione automatica",
			"gIdentityUserUnknown"=>"non è registrato nello spazio",//"boby.smith@gmail.com" n'est pas enregistré sur l'espace
			"connectSpaceSwitch"=>"Connettiti a un altro spazio",
			"connectSpaceSwitchConfirm"=>"Confermi la disconnessione per connetterti ad un altro spazio?",
			"guestAccess"=>"Accedi come ospite",
			"guestAccessTooltip"=>"Accedi a questo spazio come ospite",
			"publicSpacePasswordError"=>"Password errata",
			"disconnectSpace"=>"Esci",
			"disconnectSpaceConfirm"=>"Confermare l'uscita dallo spazio?",

			////	Password : connexion d'user / edition d'user / reset du password
			"password"=>"Password",
			"passwordModify"=>"Cambiare la password",
			"passwordToModify"=>"Password temporanea (da modificare all'accesso)",//Mail d'envoi d'invitation
			"passwordToModify2"=>"Password (da modificare se necessario)",//Mail de création de user
			"passwordVerif"=>"Conferma password",
			"passwordTooltip"=>"Compila i campi solo se desideri modificare la password",
			"passwordInvalid"=>"La password deve contenere numeri, lettere e almeno 6 caratteri",
			"passwordConfirmError"=>"La password di conferma non è valida",
			"specifyPassword"=>"Grazie per aver specificato una password",
			"resetPassword"=>"Informazioni di accesso dimenticate?",
			"resetPassword2"=>"Inserisci il tuo indirizzo e-mail per ricevere login e password",
			"resetPasswordNotif"=>"È stata appena inviata un e-mail al vostro indirizzo per reimpostare la password. Se non avete ricevuto l'e-mail, verificate che l'indirizzo specificato sia corretto o che l'e-mail non sia presente nello spam",
			"resetPasswordMailTitle"=>"Reimposta la password",
			"resetPasswordMailPassword"=>"Per accedere al proprio spazio e reimpostare la password",
			"resetPasswordMailPassword2"=>"Fare clic qui",
			"resetPasswordMailLoginRemind"=>"Promemoria del tuo ID di accesso",
			"resetPasswordIdExpired"=>"Il link per rigenerare la password è scaduto. Si prega di avviare nuovamente la procedura",			

			////	Type d'affichage
			"displayMode"=>"Vista",
			"displayMode_line"=>"Elenco",
			"displayMode_block"=>"Blocchi",
			
			////	Sélectionner / Déselectionner tous les éléments
			"select"=>"Seleziona",
			"selectUnselect"=>"Seleziona / Deseleziona",
			"selectAll"=>"Seleziona tutto",
			"selectNone"=>"Deseleziona tutto",
			"selectSwitch"=>"Cambia selezione",
			"deleteElems"=>"Rimuove gli elementi selezionati",
			"changeFolder"=>"Sposta in un altra cartella",
			"showOnMap"=>"Mostra su una mappa",
			"showOnMapTooltip"=>"Visualizza su una mappa i contatti con indirizzo, codice postale e città",
			"notifSelectUser"=>"Grazie per aver selezionato un utente",
			"notifSelectUsers"=>"Grazie per aver selezionato almeno 2 utenti",
			"selectSpace"=>"Grazie per aver selezionato almeno uno spazio",
			"visibleAllSpaces"=>"Visibile in tutti gli spazi",/*cf. Categorie, temi, ecc*/
			"visibleOnSpace"=>"Visibile nello spazio",/*"..Mio spazio "*/
			
			////	Temps ("de 11h à 12h", "le 25-01-2007 à 10h30", ecc.)
			"from"=>"di ",
			"at"=>"al",
			"the"=>"il",
			"begin"=>"Inizio",
			"end"=>"End",
			"beginEnd"=>"Inizio/Fine",
			"days"=>"giorni",
			"day_1"=>"lunedì",
			"day_2"=>"martedì",
			"day_3"=>"mercoledì",
			"day_4"=>"giovedì",
			"day_5"=>"venerdì",
			"day_6"=>"sabato",
			"day_7"=>"domenica",
			"month_1"=>"gennaio",
			"month_2"=>"febbraio",
			"month_3"=>"marzo",
			"month_4"=>"aprile",
			"month_5"=>"maggio",
			"month_6"=>"giugno",
			"month_7"=>"luglio",
			"month_8"=>"agosto",
			"month_9"=>"settembre",
			"month_10"=>"ottobre",
			"month_11"=>"novembre",
			"month_12"=>"dicembre",
			"today"=>"oggi",
			"beginEndError"=>"La data di fine non può precedere la data di inizio",
			"dateFormatError"=>"La data deve essere nel formato gg/mm/aaaa",
			"timeFormatError"=>"L'ora deve essere in formato HH:mm",
			
			////	Menus d'édition des objets et editeur tinyMce
			"title"=>"Titolo",
			"name"=>"Nome",
			"description"=>"Descrizione",
			"specifyName"=>"Si prega di specificare un nome",
			"editorDraft"=>"Recupera il mio testo",
			"editorDraftConfirm"=>"Recupera l'ultimo testo specificato",
			"editorFileInsert"=>"Aggiungi immagine o video",
			"editorFileInsertNotif"=>"Selezionare un immagine in formato Jpeg, Png, Gif o Svg",
			
			////	Validation des formulaires
			"add"=>"Aggiungi",
			"modify"=>"Modifica",
			"record"=>"Salva",
			"modifyAndAccesRight"=>"Modifica e definizione dell'accesso",
			"validate"=>"Convalida",
			"send"=>"Invia",
			"sendTo"=>"Invia a",

			////	Tri d'affichage. Tous les éléments (dossier, tâche, lien, etc...) ont par défaut une date, un auteur & une description
			"sortBy"=>"Ordinato per",
			"sortBy2"=>"Ordina per",
			"SORT_dateCrea"=>"data di creazione",
			"SORT_dateModif"=>"data di modifica",
			"SORT_title"=>"titolo",
			"SORT_description"=>"descrizione",
			"SORT__idUser"=>"autore",
			"SORT_extension"=>"tipo di file",
			"SORT_octetSize"=>"dimensione",
			"SORT_downloadsNb"=>"download",
			"SORT_civility"=>"titolo",
			"SORT_name"=>"cognome",
			"SORT_firstName"=>"nome",
			"SORT_adress"=>"indirizzo",
			"SORT_postalCode"=>"codice postale",
			"SORT_city"=>"città",
			"SORT_country"=>"paese",
			"SORT_function"=>"funzione",
			"SORT_companyOrganization"=>"azienda / organizzazione",
			"SORT_lastConnection"=>"ultimo accesso",
			"tri_ascendant"=>"Ascendente",
			"tri_descendant"=>"Discendente",
		
			////	Options de suppression
			"confirmDelete"=>"Vuoi eliminare definitivamente questi elementi?",
			"confirmDeleteDbl"=>"Questa azione è definitiva: confermate tutti gli stessi elementi?",
			"confirmDeleteSelect"=>"Si desidera eliminare definitivamente la selezione?",
			"confirmDeleteSelectNb"=>"elementi selezionati",//"55 elementi selezionati".
			"confirmDeleteFolderAccess"=>"Attenzione! Alcune sottocartelle non sono accessibili: saranno eliminate!",
			"notifyBigFolderDelete"=>"L'eliminazione di --NB_FOLDERS-- sottocartelle può essere un po  grande, attendere qualche istante prima della fine del processo",
			"delete"=>"Elimina",
			"notDeletedElements"=>"Alcuni elementi non sono stati eliminati perché non si dispone dei diritti di accesso necessari",

			////	Visibilité d'un Objet : auteur et droits d'accès
			"autor"=>"Autore",
			"postBy"=>"Postato da",
			"guest"=>"Ospite",
			"creation"=>"Creazione",
			"modification"=>"Modifica",
			"createBy"=>"Creato da",
			"modifBy"=>"Modificato da",
			"objHistory"=>"Storia dell'elemento",
			"all"=>"tutti",
			"all2"=>"tutti",
			"deletedUser"=>"account utente eliminato",
			"folderContent"=>"contenuto",
			"accessRead"=>"Leggi",
			"accessReadTooltip"=>"Accesso in lettura",
			"accessWriteLimit"=>"scrittura limitata",
			"accessWriteLimitTooltip"=>"Accesso in scrittura limitato: ogni utente può modificare o eliminare solo gli -OBJCONTENT- che ha creato in questo --OBJLABEL--",
			"accessWrite"=>"Scrittura",
			"accessWriteTooltip"=>"Accesso in scrittura",
			"accessWriteTooltipContainer"=>"Accesso in scrittura : possibilità di aggiungere, modificare o eliminare tutti gli -OBJCONTENT-- della --OBJLABEL--",
			"accessAutorPrivilege"=>"Solo l'autore e gli amministratori possono modificare o rimuovere questo --OBJLABEL--",
			"accessRightsInherited"=>"Diritti di accesso ereditati da --OBJLABEL-- superiore",
			"categoryNotifSpaceAccess"=>"è accessibile solo nello spazio",//Ex: "Thème bidule -n est accessible que sur l'espace- Machin"
			"categoryNotifChangeOrder"=>"L'ordine di visualizzazione è stato modificato",

			////	Libellé des objets (cf. "MdlObject::objectType")
			"OBJECTcontainer"=>"contenitore",
			"OBJECTelement"=>"elemento",
			"OBJECTfolder"=>"cartella",
			"OBJECTdashboardNews"=>"news",
			"OBJECTdashboardPoll"=>"sondaggio",
			"OBJECTfile"=>"file",
			"OBJECTfileFolder"=>"cartella",
			"OBJECTcalendar"=>"calendario",
			"OBJECTcalendarEvent"=>"evento",
			"OBJECTforumSubject"=>"argomento",
			"OBJECTforumMessage"=>"messaggio",
			"OBJECTcontact"=>"contatto",
			"OBJECTcontactFolder"=>"cartella",
			"OBJECTlink"=>"segnalibro",
			"OBJECTlinkFolder"=>"cartella",
			"OBJECTtask"=>"attività",
			"OBJECTtaskFolder"=>"cartella",
			"OBJECTuser"=>"utente",

			////	Envoi d'un email
			"MAIL_sendOk"=>"L'e-mail è stata inviata con successo",
			"MAIL_sendNotOk"=>"Impossibile inviare l'e-mail",
			"MAIL_recipients"=>"Destinatari",
			"MAIL_attachedFileError"=>"Il file non è stato aggiunto all'e-mail perché è troppo grande",
			"MAIL_hello"=>"Ciao",
			"MAIL_hideRecipients"=>"Nascondi destinatari",
			"MAIL_hideRecipientsTooltip"=>"Metti tutti i destinatari in copia nascosta. Si noti che con questa opzione l'e-mail potrebbe arrivare nello spam in alcune caselle di posta elettronica",
			"MAIL_addReplyTo"=>"Metti il mio messaggio di posta elettronica in risposta",
			"MAIL_addReplyToTooltip"=>"Aggiungi il mio messaggio di posta elettronica nel campo   Rispondi a  . Si noti che con questa opzione l'e-mail potrebbe finire nello spam in alcune caselle di posta elettronica",
			"MAIL_noFooter"=>"Non firmare il messaggio",
			"MAIL_noFooterTooltip"=>"Non firmare la fine del messaggio con il nome del mittente e un collegamento web allo spazio",
			"MAIL_receptionNotif"=>"Ricevuta di consegna",
			"MAIL_receptionNotifTooltip"=>"Attenzione! Alcuni client di posta elettronica non supportano le ricevute di consegna",
			"MAIL_specificMails"=>"Aggiungi indirizzi email",
			"MAIL_specificMailsTooltip"=>"Aggiungi indirizzi email non elencati nello spazio",
			"MAIL_fileMaxSize"=>"Tutti i tuoi allegati non devono superare i 15 MB. Alcuni servizi di messaggistica potrebbero rifiutare le email oltre questo limite. Inviare lo stesso?",
			"MAIL_sendButton"=>"Invia e-mail",
			"MAIL_sendBy"=>"Inviato da",//"Envoyé par" M. Trucmuche
			"MAIL_sendNotif"=>"L'e-mail di notifica è stata inviata",
			"MAIL_fromTheSpace"=>"dallo spazio",//"depuis l'espace Bidule"
			"MAIL_elemCreatedBy"=>"--OBJLABEL-- creato da",//Dossier 'créé par' boby
			"MAIL_elemModifiedBy"=>"--OBJLABEL-- modificato da",//Dossier modifié par 'Boby'
			"MAIL_elemAccessLink"=>"Clicca qui per accedervi dal tuo spazio",

			////	Dossier & fichier
			"gigaOctet"=>"Gb",
			"megaOctet"=>"Mb",
			"kiloOctet"=>"Kb",
			"rootFolder"=>"Cartella principale",
			"rootFolderTooltip"=>"Aprire le impostazioni dello spazio per modificare i diritti di accesso alla cartella principale",
			"addFolder"=>"Aggiungi una cartella",
			"download"=>"Scarica un file",
			"downloadFolder"=>"Scarica cartella",
			"diskSpaceUsed"=>"Spazio su disco utilizzato",
			"diskSpaceUsedModFile"=>"Spazio su disco utilizzato per il gestore di file",
			"downloadAlert"=>"L'archivio è troppo grande per essere scaricato durante il giorno (--ARCHIVE_SIZE--). Riavviare il download dopo",//"19h".
			"downloadBackToApp"=>"Torna all'app",
			
			////	Infos sur une personne
			"civility"=>"Titolo",
			"name"=>"Cognome",
			"firstName"=>"Nome",
			"adress"=>"Indirizzo",
			"postalCode"=>"Codice postale",
			"city"=>"Città",
			"country"=>"paese",
			"telephone"=>"Telefono",
			"telmobile"=>"Telefono cellulare",
			"mail"=>"Email",
			"function"=>"Funzione",
			"companyOrganization"=>"Azienda/Organizzazione",
			"lastConnection"=>"Ultimo accesso",
			"lastConnection2"=>"Accesso effettuato",
			"lastConnectionEmpty"=>"Non ancora connesso",
			"displayProfil"=>"Visualizza profilo",
			
			////	Captcha
			"captcha"=>"Copia i 5 caratteri",
			"captchaTooltip"=>"Grazie per aver inserito i 5 caratteri per l'identificazione",
			"captchaError"=>"L'identificazione visiva è falsa",
			
			////	Rechercher
			"searchSpecifyText"=>"Specificare almeno 3 caratteri (alfanumerici e non speciali)",
			"search"=>"Ricerca",
			"searchDateCrea"=>"Data di creazione",
			"searchDateCreaDay"=>"meno di un giorno",
			"searchDateCreaWeek"=>"meno di una settimana",
			"searchDateCreaMonth"=>"meno di un mese",
			"searchDateCreaYear"=>"meno di un anno",
			"searchOnSpace"=>"Cerca in questo spazio",
			"advancedSearch"=>"Ricerca avanzata",
			"advancedSearchAnyWord"=>"qualsiasi parola",
			"advancedSearchAllWords"=>"tutte le parole",
			"advancedSearchExactPhrase"=>"frase esatta",
			"keywords"=>"Parole chiave",
			"listModules"=>"Moduli",
			"listFields"=>"Campi",
			"listFieldsElems"=>"Elementi coinvolti",
			"noResults"=>"Nessun risultato",
			
			////	Inscription d utilisateur
			"userInscription"=>"Registrati su questo spazio",
			"userInscriptionTooltip"=>"Crea un nuovo account utente (convalidato da un amministratore)",
			"userInscriptionSpace"=>"Registrati nello spazio",
			"userInscriptionRecorded"=>"La registrazione è stata salvata: sarà convalidata al più presto dall'amministratore dello spazio",
			"userInscriptionEmailSubject"=>"Nuova registrazione nello spazio",//"Mon espace"
			"userInscriptionEmailMessage"=>"È stata richiesta una nuova registrazione da parte di <i>--NEW_USER_LABEL--</i> per lo spazio <i>--SPACE_NAME--</i> : <br><br><i>--NEW_USER_MESSAGE--<i> <br><br>Ricordatevi di convalidare o invalidare questa registrazione durante la vostra prossima connessione.",
			"userInscriptionEdit"=>"Consenti ai visitatori di registrarsi nello spazio",
			"userInscriptionEditTooltip"=>"La registrazione avviene nella homepage del sito. La registrazione deve poi essere convalidata dall'amministratore dello spazio",
			"userInscriptionNotif"=>"Notifica via e-mail a ogni registrazione",
			"userInscriptionNotifTooltip"=>"Invia una notifica via e-mail agli amministratori dello spazio, dopo ogni registrazione",
			"userInscriptionPulsate"=>"Registrazione",
			"userInscriptionValidate"=>"Convalida registrazione utente",
			"userInscriptionValidateTooltip"=>"Convalida la registrazione dell'utente sul sito",
			"userInscriptionSelectValidate"=>"Convalida registrazioni",
			"userInscriptionSelectInvalidate"=>"Invalida registrazioni",
			"userInscriptionInvalidateMail"=>"La tua registrazione non è stata convalidata su",

			////	Importer ou Exporter : Contact OU Utilisateurs
			"importExport_user"=>"Importazione/esportazione di utenti",
			"import_user"=>"Importazione di utenti nello spazio corrente",
			"export_user"=>"Esporta gli utenti dello spazio corrente",
			"importExport_contact"=>"Importazione/esportazione di contatti",
			"import_contact"=>"Importa i contatti nella cartella corrente",
			"export_contact"=>"Esporta contatti dalla cartella corrente",
			"exportFormat"=>"Formato",
			"specifyFile"=>"Seleziona un file un file",
			"fileExtension"=>"Il tipo di file non è valido. Deve essere del tipo",
			"importContactRootFolder"=>"I contatti verranno assegnati per impostazione predefinita a &quot;tutti gli utenti dello spazio&quot;",//"Mon espace"
			"importInfo"=>"Selezionare i campi di Agorà a cui puntare, grazie al menu a tendina di ciascuna colonna",
			"importNotif1"=>"Scegli la colonna del nome nelle caselle di selezione",
			"importNotif2"=>"Scegli un contatto da importare",
			"importNotif3"=>"Il campo di questa agorà è già stato selezionato in un altra colonna (i campi di ogni agorà possono essere selezionati una sola volta)",

			////	Messages d'erreur / Notifications
			"NOTIF_identification"=>"Login o password non validi",
			"NOTIF_presentIp"=>"Questo account utente viene attualmente utilizzato da un altro computer, con un altro indirizzo IP",
			"NOTIF_noAccessNoSpaceAffected"=>"L'account utente è stato identificato correttamente, ma non è attualmente assegnato ad alcuno spazio. Contattare l'amministratore",
			"NOTIF_noAccess"=>"L utente è stato disconnesso",
			"NOTIF_fileOrFolderAccess"=>"File o cartella non accessibile",
			"NOTIF_diskSpace"=>"Lo spazio per la memorizzazione dei file è insufficiente, non è possibile aggiungere file",
			"NOTIF_fileVersionForbidden"=>"Tipo di file non consentito",
			"NOTIF_fileVersion"=>"Tipo di file diverso dall'originale",
			"NOTIF_folderMove"=>"Non è possibile spostare la cartella all'interno...!",
			"NOTIF_duplicateName"=>"Esiste già un elemento con lo stesso nome",
			"NOTIF_fileName"=>"Esiste già un file con lo stesso nome (ma non è stato sostituito con il file corrente)",
			"NOTIF_chmodDATAS"=>"La cartella   DATAS   non è accessibile in scrittura. È necessario concedere un accesso in lettura-scrittura al proprietario e al gruppo (  chmod 775  )",
			"NOTIF_usersNb"=>"Non è possibile aggiungere un nuovo utente: limitato a ", // "...limité à" 10
			
			////	Header / Footer
			"HEADER_displaySpace"=>"spazi di lavoro",
			"HEADER_displayAdmin"=>"Visualizzazione amministratore",
			"HEADER_displayAdminEnabled"=>"Visualizzazione amministratore abilitata",
			"HEADER_displayAdminInfo"=>"Questa opzione consente di visualizzare anche gli elementi dello spazio non assegnati all'utente",
			"HEADER_searchElem"=>"Ricerca nello spazio",
			"HEADER_documentation"=>"Documentazione",
			"HEADER_shortcuts"=>"Scorciatoie",
			"FOOTER_pageGenerated"=>"Pagina generata in",

			////	Messenger / Visio
			"MESSENGER_headerModuleName"=>"Messaggi",
			"MESSENGER_moduleDescription"=>"Messaggistica istantanea: chattare in diretta o avviare una videoconferenza con persone collegate allo spazio",
			"MESSENGER_messengerTitle"=>"Messaggistica istantanea: fare clic sul nome di una persona per chattare o avviare una videoconferenza",
			"MESSENGER_messengerMultiUsers"=>"Chatta con altri selezionando i miei interlocutori nel riquadro di destra",
			"MESSENGER_connected"=>"Online",
			"MESSENGER_nobody"=>"Al momento sei l'unica persona connessa allo spazio",
			"MESSENGER_messageFrom"=>"Messaggio da",
			"MESSENGER_messageTo"=>"Inviato a",
			"MESSENGER_chatWith"=>"Chatta con",
			"MESSENGER_addMessageToSelection"=>"Il mio messaggio (persone selezionate)",
			"MESSENGER_addMessageTo"=>"Il mio messaggio a",
			"MESSENGER_addMessageNotif"=>"Grazie per aver specificato un messaggio",
			"MESSENGER_visioProposeTo"=>"Proponi una videochiamata a",//..boby
			"MESSENGER_visioProposeToSelection"=>"Proponi una videochiamata alle persone selezionate",
			"MESSENGER_visioProposeToUsers"=>"Fare clic qui per avviare la videochiamata con",//"..Will & Boby".
			
			////	Lancer une Visio
			"VISIO_urlAdd"=>"Aggiungi una videoconferenza",
			"VISIO_urlCopy"=>"Copia il link alla videoconferenza",
			"VISIO_urlDelete"=>"Rimuovi il link alla videoconferenza",
			"VISIO_urlMail"=>"Aggiungi un link alla fine del testo per avviare una nuova videoconferenza",
			"VISIO_launch"=>"Avvia la videoconferenza",
			"VISIO_launchJitsi"=>"Avviare la videoconferenza <br>con l'applicazione Jitsi",
			"VISIO_launchFromEvent"=>"Avvia la videoconferenza dell'evento",
			"VISIO_launchTooltip"=>"Ricordati di consentire l'accesso alla tua webcam e al tuo microfono!",
			"VISIO_launchTooltip2"=>"Problemi con la fotocamera o il microfono durante la tua videoconferenza? Segui la documentazione <img src='app/img/pdf.png'>",
			"VISIO_launchServerTooltip"=>"Scegli il server secondario se il server primario non funziona correttamente:<br>Importante: i tuoi contatti devono selezionare il tuo stesso server.",
			"VISIO_launchServerMain"=>"Server video principale",
			"VISIO_launchServerAlt"=>"Server video secondario",

			////	VueObjEditMenuSubmit.php
			"EDIT_notifNoSelection"=>"È necessario selezionare almeno una persona o uno spazio",
			"EDIT_notifNoPersoAccess"=>"Non sei assegnato all'elemento. convalidare tutti gli stessi ?",
			"EDIT_parentFolderAccessError"=>"Verificare i diritti di accesso della cartella padre <br><i>--FOLDER_NAME--</i><br><br>Deve essere presente anche un diritto di accesso per <br><i>--SPACE_LABEL--</i> &nbsp;>&nbsp; <i>--TARGET_LABEL--</i><br><br>In caso contrario questo file non sarà accessibile!",
			"EDIT_accessRight"=>"Diritti di accesso",
			"EDIT_accessRightContent"=>"Diritti di accesso al contenuto",
			"EDIT_spaceNoModule"=>"Il modulo corrente non è ancora stato aggiunto a questo spazio",
			"EDIT_allUsers"=>"Tutti gli utenti",
			"EDIT_allUsersTooltip"=>"Tutti gli utenti e gli ospiti",
			"EDIT_allUsersAndGuests"=>"Tutti gli utenti e gli ospiti",
			"EDIT_allUsersAndGuestsTooltip"=>"Diritto di accesso per tutti gli utenti e gli ospiti dello spazio <i>--SPACENAME--</i>.<hr>Gli ospiti hanno solo accesso in lettura allo spazio (persone senza un account utente)",
			"EDIT_adminSpace"=>"Amministratore: accesso totale a tutti gli elementi dello spazio",
			"EDIT_showAllUsers"=>"Visualizza tutti gli utenti",
			"EDIT_showAllUsersAndSpaces"=>"Visualizza tutti gli utenti e gli spazi",
			"EDIT_notifMail"=>"Notifica via email",
			"EDIT_notifMail2"=>"Invia una notifica via email",
			"EDIT_notifMailTooltip"=>"Invia una notifica via email alle persone assegnate all'elemento (--OBJLABEL--)",
			"EDIT_notifMailTooltipCal"=>"<hr>Se si assegna l'evento ai calendari personali, la notifica verrà inviata solo ai proprietari di questi calendari (accesso in scrittura).",
			"EDIT_notifMailAddFiles"=>"Allega file alla notifica",
			"EDIT_notifMailSelect"=>"Seleziona i destinatari delle notifiche",
			"EDIT_accessRightSubFolders"=>"Assegna gli stessi diritti di accesso alle sottocartelle",
			"EDIT_accessRightSubFoldersTooltip"=>"Estendi i diritti di accesso alle sottocartelle che puoi modificare",
			"EDIT_shortcut"=>"Scorciatoia",
			"EDIT_shortcutInfo"=>"Aggiungi un collegamento al menu principale",
			"EDIT_attachedFile"=>"File allegati",
			"EDIT_attachedFileAdd"=>"Aggiungi file allegati",
			"EDIT_attachedFileInsert"=>"Inserisci nel testo",
			"EDIT_attachedFileInsertTooltip"=>"Inserisci immagine/video nel testo dell'editor (formato jpg, png o mp4)",
			"EDIT_guestName"=>"Il tuo nome/nickname",
			"EDIT_guestNameNotif"=>"Specifica un nome/nickname",
			"EDIT_guestMail"=>"Il tuo indirizzo e-mail",
			"EDIT_guestMailTooltip"=>"Specificare l'e-mail per la convalida della proposta",
			"EDIT_guestElementRegistered"=>"Grazie per la proposta. Verrà esaminata al più presto prima della convalida",
			
			////	Formulaire d'installation
			"INSTALL_dbConnect"=>"Connessione al database",
			"INSTALL_dbHost"=>"Nome host del server di database",
			"INSTALL_dbName"=>"Nome del database",
			"INSTALL_dbLogin"=>"Nome utente",
			"INSTALL_adminAgora"=>"Informazioni sull amministratore del database",
			"INSTALL_errorDbNameFormat"=>"Attenzione: il nome del database deve contenere preferibilmente solo caratteri alfanumerici e trattini o underscore",
			"INSTALL_errorDbConnection"=>"L identificazione al database MariaDB/MySQL non è riuscita",
			"INSTALL_errorDbExist"=>"Applicazione già installata: <a href= index.php >cliccare qui per accedervi</a><br><br>Per riavviare l'installazione, ricordarsi di cancellare il database",
			"INSTALL_errorDbNoSqlFile"=>"Il file di installazione db.sql non è accessibile o è stato eliminato perché l'installazione è già stata eseguita",
			"INSTALL_PhpOldVersion"=>"Agora-Project --CURRENT_VERSION-- richiede una versione più recente di PHP",
			"INSTALL_confirmInstall"=>"Confermare l'installazione?",
			"INSTALL_installOk"=>"Agora-Project è stato installato correttamente!",
			// Premiers enregistrements en DB
			"INSTALL_agoraDescription"=>"Spazio per la condivisione e il lavoro collaborativo",
			"INSTALL_dataDashboardNews"=>"<h3>Benvenuto nel tuo nuovo spazio di condivisione!</h3>
											<h4><img src='app/img/file/iconSmall.png'> Condividi ora i tuoi file nel gestore di file</h4>
											<h4><img src='app/img/calendar/iconSmall.png'> Condividi i calendari comuni o il tuo calendario personale</h4>
											<h4><img src='app/img/dashboard/iconSmall.png'> Espandi il feed di notizie della tua comunità</h4>
											<h4><img src='app/img/messenger.png'> Comunicare attraverso il forum, la messaggistica istantanea o le videoconferenze</h4>
											<h4><img src='app/img/task/iconSmall.png'> Centralizza le tue note, i tuoi progetti e i tuoi contatti</h4>
											<h4><img src='app/img/mail/iconSmall.png'> Invia le newsletter via e-mail</h4>
											<h4><img src='app/img/postMessage.png'> <a onclick=\"lightboxOpen('?ctrl=user&action=SendInvitation')\">Clicca qui per inviare le email di invito e far crescere la tua community!</a></h4>
											<h4><img src='app/img/pdf.png'> <a href='https://www.omnispace.fr/index.php?ctrl=offline&action=Documentation' target='_blank'>Per maggiori informazioni, consulta la documentazione ufficiale di Omnispace & Agora-Project</a></h4>",
			"INSTALL_dataDashboardPoll"=>"Cosa ne pensi del news feed?",
			"INSTALL_dataDashboardPollA"=>"Molto interessante!",
			"INSTALL_dataDashboardPollB"=>"Interessante",
			"INSTALL_dataDashboardPollC"=>"Non interessante",
			"INSTALL_dataCalendarEvt"=>"Benvenuto su Omnispace!",
			"INSTALL_dataForumSubject1"=>"Benvenuto su Omnispace!",
			"INSTALL_dataForumSubject2"=>"Sentitevi liberi di condividere le vostre domande o di discutere gli argomenti che volete condividere.",
			"INSTALL_dataTaskStatus1"=>"Da fare",
			"INSTALL_dataTaskStatus2"=>"In corso",
			"INSTALL_dataTaskStatus3"=>"Da convalidare",
			"INSTALL_dataTaskStatus4"=>"Terminato",

			////	MOD : AGORA
			////
			"AGORA_generalSettings"=>"Impostazioni generali",
			"AGORA_Changelog"=>"Registrazione della versione",
			"AGORA_phpMailDisabled"=>"Funzione PHP Mail disabilitata",
			"AGORA_phpLdapDisabled"=>"Funzione LDAP PHP disabilitata",
			"AGORA_phpGD2Disabled"=> "Funzione PHP GD2 disabilitata",
			"AGORA_backupFull"=>"Backup completo",
			"AGORA_backupFullTooltip"=>"Ripristina il backup completo dello spazio: tutti i file e il database",
			"AGORA_backupDb"=>"Backup del database",
			"AGORA_backupDbTooltip"=>"Recupera solo il backup del database dello spazio",
			"AGORA_backupConfirm"=>"Questa operazione potrebbe richiedere alcuni minuti: confermi il download?",
			"AGORA_diskSpaceInvalid"=>"Lo spazio su disco per i file deve essere un numero intero",
			"AGORA_visioHostInvalid"=>"L'indirizzo web del server di videoconferenza non è valido: deve iniziare con 'https'",
			"AGORA_mapApiKeyInvalid"=>"Se scegli Google Map come strumento di mappatura, devi specificare una 'Chiave API'",
			"AGORA_gIdentityKeyInvalid"=>"Se scegli la connessione opzionale tramite Google, devi specificare una 'Chiave API' per l'accesso con Google",
			"AGORA_confirmModif"=>"Confermi le modifiche?",
			"AGORA_name"=>"Nome dello spazio principale",
			"AGORA_nameTooltip"=>"Nome visualizzato nella pagina di accesso, nelle e-mail, ecc.",
			"AGORA_description"=>"Descrizione nella pagina di accesso",
			"AGORA_footerHtml"=>"Testo in basso a sinistra di ogni pagina",
			"AGORA_logo"=>"Logo in basso a destra in ogni pagina",
			"AGORA_logoUrl"=>"URL",
			"AGORA_logoConnect"=>"Logo nella pagina di connessione",
			"AGORA_logoConnectTooltip"=>"Logo visualizzato nella parte superiore del modulo di connessione",
			"AGORA_lang"=>"Lingua predefinita",
			"AGORA_timezone"=>"Fuso orario",
			"AGORA_diskSpaceLimit"=>"Spazio su disco per i file",
			"AGORA_logsTimeOut"=>"Conservazione della cronologia degli eventi (registri)",
			"AGORA_logsTimeOutTooltip"=>"Il periodo di conservazione della cronologia degli eventi riguarda l'aggiunta o la modifica di elementi. I registri di eliminazione vengono conservati per un minimo di 1 anno.",
			"AGORA_visioHost"=>"Server di videoconferenza Jitsi",
			"AGORA_visioHostTooltip"=>"Url del server di videoconferenza principale. Esempio: https://framatalk.org o https://meet.jit.si",
			"AGORA_visioHostAlt"=>"Server di videoconferenza alternativo",
			"AGORA_visioHostAltTooltip"=>"Url del server di videoconferenza alternativo: in caso di indisponibilità del server Jitsi principale",
			"AGORA_skin"=>"Colore interfaccia",
			"AGORA_black"=>"Display scuro",
			"AGORA_white"=>"Display luce",
			"AGORA_userMailDisplay"=>"Indirizzi email degli utenti visibili a tutti",
			"AGORA_userMailDisplayTooltip"=>"Mostra/nascondi l'e-mail nel profilo di ciascun utente, notifiche e-mail, ecc.<br>Nota: l'amministratore principale sarà sempre in grado di visualizzare l'e-mail di ciascun utente",
			"AGORA_moduleLabelDisplay"=>"Nomi dei moduli nella barra dei menu",
			"AGORA_folderDisplayMode"=>"Visualizzazione cartella predefinita",
			"AGORA_wallpaperLogoError"=>"Lo sfondo e il logo devono essere in formato .jpg o .png",
			"AGORA_deleteWallpaper"=>"Elimina sfondo",
			"AGORA_usersCommentLabel"=>"Pulsante Commento sugli elementi",
			"AGORA_usersComment"=>"commento",
			"AGORA_usersComments"=>"commenti",
			"AGORA_usersLikeLabel"=>"Pulsante Mi piace sugli elementi",
			"AGORA_usersLike"=>"Mi piace!",
			"AGORA_mapTool"=>"Strumento di mappatura",
			"AGORA_mapToolTooltip"=>"Strumento di mappatura per vedere utenti e contatti su una mappa",
			"AGORA_mapApiKey"=>"Chiave API per la cartografia di Google Map",
			"AGORA_mapApiKeyTooltip"=>"Impostazione obbligatoria per lo strumento di mappatura di Google Map: <br>https://developers.google.com/maps/ <br>https://developers.google.com/maps/documentation/javascript /get -chiave-api",
			"AGORA_gIdentity"=>"Opzione di accesso tramite Google",
			"AGORA_gIdentityTooltip"=>"Gli utenti con un identificatore con indirizzo <i>@gmail.com</i> potranno connettersi anche tramite il proprio account Google",
			"AGORA_gIdentityClientId"=>"Chiave API per la connessione tramite Google",
			"AGORA_gIdentityClientIdTooltip"=>"Per la connessione tramite Google è necessaria una 'chiave API'. Ulteriori informazioni su <a href='https://developers.google.com/identity/sign-in/web' target=' _blank'> https://developers.google.com/identity/sign-in/web</a>",
			"AGORA_gPeopleApiKey"=>"API KEY per importare contatti Google",
			"AGORA_gPeopleApiKeyTooltip"=>"Per recuperare i contatti Google/Gmail è necessaria una 'chiave API'. Maggiori informazioni su <a href='https://developers.google.com/people/' target='_blank' >https:/ /developers.google.com/people/</a>",
			"AGORA_messengerDisplay"=>"Messaggistica istantanea",
			"AGORA_personsSort"=>"Ordina utenti e contatti per",
			//SMTP
			"AGORA_smtpLabel"=>"Connessione SMTP e sendMail",
			"AGORA_sendmailFrom"=>"E-mail  Da ",
			"AGORA_sendmailFromPlaceholder"=>"es:  noreply@mydomain.com ",
			"AGORA_smtpHost"=>"Indirizzo del server (nome host)",
			"AGORA_smtpPort"=>"Porta server",
			"AGORA_smtpPortTooltip"=>" 25  per errore.  587  o  465  per SSL/TLS",
			"AGORA_smtpSecure"=>"Tipo di connessione crittografata (opzione)",
			"AGORA_smtpSecureTooltip"=>" ssl  o  tls ",
			"AGORA_smtpUsername"=>"Nome utente",
			"AGORA_smtpPass"=>"Password",
			//LDAP
			"AGORA_ldapLabel"=>"Connessione a un server LDAP",
			"AGORA_ldapLabelTooltip"=>"Connessione a un server LDAP per la creazione di utenti sul vostro spazio: cfr. opzione   Importazione/esportazione utenti   del modulo   Utente  ",
			"AGORA_ldapUri"=>"URI LDAP",
			"AGORA_ldapUriTooltip"=>"URI LDAP completo come LDAP://nome host:porta o LDAPS://nome host:porta per la crittografia SSL",
			"AGORA_ldapPort"=>"Porta del server",
			"AGORA_ldapPortTooltip"=>"La porta utilizzata per la connessione: '389' per impostazione predefinita",
			"AGORA_ldapLogin"=>"DN dell'amministratore LDAP (Distinguished Name)",
			"AGORA_ldapLoginTooltip"=>"ad esempio   cn=admin,dc=mon-entreprise,dc=com  ",
			"AGORA_ldapPass"=>"Password dell'amministratore",
			"AGORA_ldapDn"=>"DN del gruppo (Distinguished Name)",
			"AGORA_ldapDnTooltip"=>"DN del gruppo: posizione degli utenti nella directory. Esempio   ou=mon-groupe,dc=mon-entreprise,dc=com  ",
			"importLdapFilterTooltip"=>"Filtro di ricerca LDAP (cfr. https://www.php.net/manual/function.ldap-search.php). Esempio   (cn=*)   o   (&(samaccountname=MONLOGIN)(cn=*))  ",
			"AGORA_ldapConnectError"=>"Errore di connessione al server LDAP!",

			////	MOD : LOG
			////
			"LOG_moduleDescription"=>"Registri - Registro eventi",
			"LOG_path"=>"Percorso",
			"LOG_filter"=>"Filtro",
			"LOG_date"=>"Data/Ora",
			"LOG_spaceName"=>"spazio",
			"LOG_moduleName"=>"modulo",
			"LOG_objectType"=>"Tipo di oggetto",
			"LOG_action"=>"Azione",
			"LOG_userName"=>"Utente",
			"LOG_ip"=>"IP",
			"LOG_comment"=>"commento",
			"LOG_noLogs"=>"Nessun registro",
			"LOG_filterSince"=>"filtrato da",
			"LOG_search"=>"ricerca",
			"LOG_connexion"=>"connessione",//azione
			"LOG_add"=>"aggiungi",//azione
			"LOG_delete"=>"cancella",//azione
			"LOG_modif"=>"modifica modifica",//azione

			////	MOD : SPACE
			////
			"SPACE_moduleTooltip"=>"Lo spazio principale può essere suddiviso in più spazi (vedere 'sottospazio')",
			"SPACE_manageAllSpaces"=>"Gestione di tutti gli spazi",
			"SPACE_config"=>"Impostazioni dello spazio",//.."mon espace"
			//Index
			"SPACE_confirmDeleteDbl"=>"Attenzione : questa azione è definitiva. Tieni presente che verranno cancellati solo i dati presenti in questo spazio. Confermare comunque l'eliminazione?",
			"SPACE_space"=>"spazio",
			"SPACE_spaces"=>"spazi",
			"SPACE_accessRightUndefined"=>"Da definire!",
			"SPACE_modules"=>"Moduli",
			"SPACE_addSpace"=>"Aggiungi uno spazio",
			//Edit
			"SPACE_userAdminAccess"=>"Utenti e amministratori dello spazio",
			"SPACE_selectModule"=>"È necessario selezionare un modulo",
			"SPACE_spaceModules"=>"Moduli dello spazio",
			"SPACE_publicSpace"=>"Spazio pubblico: accesso ospiti",
			"SPACE_publicSpaceTooltip"=>"Uno spazio pubblico è aperto alle persone che non dispongono di un account utente (ospiti). Questi potranno accedere allo spazio dalla pagina iniziale. È possibile specificare una password per proteggere l'accesso a questo spazio pubblico. I moduli  Email  e  Utenti  non sono disponibili per gli ospiti.",
			"SPACE_publicSpaceNotif"=>"Il vostro spazio è pubblico: se contiene dati personali (telefono, indirizzo, ecc.) ricordatevi di specificare una password per rispettare il GDPR: General Data Protection Regulation",
			"SPACE_usersInvitation"=>"Gli utenti possono inviare inviti via e-mail",
			"SPACE_usersInvitationTooltip"=>"Tutti gli utenti possono inviare inviti via e-mail per unirsi allo spazio",
			"SPACE_allUsers"=>"Tutti gli utenti",
			"SPACE_user"=>" Utente",
			"SPACE_userTooltip"=>"Utente dello spazio: <br> Accesso normale allo spazio",
			"SPACE_admin"=>"Amministratore",
			"SPACE_adminTooltip"=>"L'amministratore di uno spazio è un utente che può modificare o eliminare tutti gli elementi presenti nello spazio. Può inoltre configurare lo spazio, creare nuovi account utente, creare gruppi di utenti, inviare inviti via e-mail per aggiungere nuovi utenti, ecc,",

			////	MOD : USER
			////
			// Menu principal
			"USER_headerModuleName"=>"Utente",
			"USER_moduleDescription"=>"Utenti dello spazio",
			"USER_option_allUsersAddGroup"=>"Gli utenti possono anche creare gruppi",
			//Index
			"USER_spaceOrAllUsersTooltip"=>"Gestione degli utenti dello spazio visualizzato / Gestione degli utenti di tutti gli spazi (riservata all'amministratore generale)",
			"USER_spaceUsers"=>"Utenti attuali dello spazio",
			"USER_allUsers"=>"Gestione di tutti gli utenti",
			"USER_deleteDefinitely"=>"Elimina definitivamente",
			"USER_deleteFromCurSpace"=>"Disassegnare allo spazio corrente",
			"USER_deleteFromCurSpaceConfirm"=>"Disassegnare l'utente allo spazio corrente?",
			"USER_allUsersOnSpaceNotif"=>"Tutti gli utenti sono interessati da questo spazio",
			"USER_user"=>"Utente",
			"USER_users"=>"Utenti",
			"USER_addExistUser"=>"Aggiungi un utente esistente allo spazio",
			"USER_addExistUserTitle"=>"Aggiungi allo spazio un utente già esistente sul sito: assegnazione allo spazio",
			"USER_addUser"=>"Aggiungi utente",
			"USER_addUserSite"=>"Crea un utente sul sito: per impostazione predefinita, assegnato a qualsiasi spazio!",
			"USER_addUserSpace"=>"Crea un utente nello spazio corrente",
			"USER_sendCoords"=>"Invia login e password",
			"USER_sendCoordsTooltip"=>"Invia agli utenti un e-mail con il login e un link per inizializzare la password",
			"USER_sendCoordsTooltip2"=>"Invia agli utenti un e-mail con le informazioni di accesso",
			"USER_sendCoordsConfirm"=>"Le password saranno rinnovate! Continuare?",
			"USER_sendCoordsMail"=>"I dati di accesso al vostro spazio",
			"USER_noUser"=>"Nessun utente assegnato a questo spazio per il momento",
			"USER_spaceList"=>"Spazi dell'utente",
			"USER_spaceNoAffectation"=>"Nessuno spazio",
			"USER_adminGeneral"=>"Amministratore generale del sito",
			"USER_adminGeneralTooltip"=>"Attenzione: il diritto di accesso   amministratore generale   conferisce numerosi privilegi e responsabilità, in particolare la modifica di tutti gli elementi (calendari, cartelle, file, ecc.), nonché di tutti gli utenti e gli spazi. È quindi consigliabile assegnare questo privilegio a 2 o 3 utenti al massimo.<br><br>Per privilegi più limitati, scegliere il diritto di accesso   amministratore dello spazio   (vedere menu principale >   Impostare lo spazio  )",
			"USER_adminSpace"=>"Amministratore dello spazio",
			"USER_userSpace"=>"Utente dello spazio",
			"USER_profilEdit"=>"Modifica profilo",
			"USER_myProfilEdit"=>"Modifica il mio profilo utente",
			// Invitations
			"USER_sendInvitation"=>"Invia inviti via e-mail",
			"USER_sendInvitationTooltip"=>"Invia inviti ai tuoi contatti, per creare un account utente e partecipare all'area di lavoro.<hr><img src= app/img/google.png  height=15> Se hai un account Google, potrai inviare inviti ai tuoi contatti Gmail.",
			"USER_mailInvitationObject"=>"Invito di ", // ..Jean DUPOND
			"USER_mailInvitationFromSpace"=>"invita a unirsi a ", // Jean DUPOND "vous invite à rejoindre l'espace" Mon Espace
			"USER_mailInvitationConfirm"=>"Fare clic qui per confermare l'invito",
			"USER_mailInvitationWait"=>"Inviti non ancora confermati",
			"USER_exired_idInvitation"=>"Il link web per il vostro invito è scaduto...",
			"USER_invitPassword"=>"Conferma l'invito",
			"USER_invitPassword2"=>"Scegli la password per confermare l'invito",
			"USER_invitationValidated"=>"Il vostro invito è stato convalidato!",
			"USER_gPeopleImport"=>"Ottieni i miei contatti dal mio indirizzo Gmail",
			"USER_importQuotaExceeded"=>"L utente è limitato a --USERS_QUOTA_REMAINING-- nuovi account utente, su un totale di --LIMITE_NB_USERS-- utenti",
			// groupes
			"USER_spaceGroups"=>"gruppi di utenti dello spazio",
			"USER_spaceGroupsEdit"=>"modifica i gruppi di utenti dello spazio",
			"USER_groupEditInfo"=>"Ogni gruppo può essere modificato dal suo autore o dall'amministratore dello spazio",
			"USER_addGroup"=>"Aggiungi un gruppo",
			"USER_userGroups"=>"Gruppi di utenti",
			// Utilisateur_affecter
			"USER_searchPrecision"=>"Grazie per aver specificato un cognome, un nome o un indirizzo e-mail",
			"USER_userAffectConfirm"=>"Confermare le assegnazioni?",
			"USER_userSearch"=>"Cerca gli utenti da aggiungere allo spazio corrente",
			"USER_allUsersOnSpace"=>"Tutti gli utenti del sito sono già assegnati a questo spazio",
			"USER_usersSpaceAffectation"=>"Assegna gli utenti allo spazio:",
			"USER_usersSearchNoResult"=>"Nessun utente trovato",
			"USER_usersSearchBack"=>"Indietro",
			// Utilisateur_edit & CO
			"USER_langs"=>"Lingua",
			"USER_persoCalendarDisabled"=>"Calendario personale disabilitato",
			"USER_persoCalendarDisabledTooltip"=>"A ogni utente viene assegnata per impostazione predefinita un agenda personale (anche se il modulo   Calendario   non è attivato nello spazio). Selezionare questa opzione per disabilitare l'agenda personale di questo utente.",
			"USER_connectionSpace"=>"Spazio visualizzato dopo la connessione",
			"USER_loginExists"=>"Il login/email esiste già. Scegliere un altro",
			"USER_mailPresentInAccount"=>"Esiste già un account utente con questo indirizzo e-mail",
			"USER_loginAndMailDifferent"=>"Entrambi gli indirizzi e-mail devono essere identici",
			"USER_mailNotifObject"=>"Nuovo account su",
			"USER_mailNotifContent"=>"L account utente è stato creato su",
			"USER_mailNotifContent2"=>"Connettiti con il seguente login e password",
			"USER_mailNotifContent3"=>"Grazie per aver archiviato questa e-mail",
			// Edition du Livecounter / Messenger / Visio
			"USER_messengerEdit"=>"Configura la mia messaggistica istantanea",
			"USER_messengerEdit2"=>"Configura la messaggistica istantanea",
			"USER_livecounterVisibility"=>"Visibilità su messaggistica istantanea e videoconferenza",
			"USER_livecounterAllUsers"=>"Visualizza la mia presenza quando sono connesso: messaggistica/video abilitati",
			"USER_livecounterDisabled"=>"Nascondi la mia presenza quando sono connesso: messaggistica/video disattivati",
			"USER_livecounterSomeUsers"=>"Solo alcuni utenti possono vedermi quando sono connesso",

			////	MOD : DASHBOARD
			////
			// Menu principal + options du module
			"DASHBOARD_headerModuleName"=>"Notizie",
			"DASHBOARD_moduleDescription"=>"Notizie, sondaggi ed elementi recenti",
			"DASHBOARD_option_adminAddNews"=>"Solo l'amministratore può aggiungere notizie",
			"DASHBOARD_option_disablePolls"=>"Disabilita i sondaggi",
			"DASHBOARD_option_adminAddPoll"=>"Solo l'amministratore può aggiungere sondaggi",
			// Index
			"DASHBOARD_menuNews"=>"Notizie",
			"DASHBOARD_menuPolls"=>"Sondaggi",
			"DASHBOARD_menuElems"=>"Nuovi elementi",
			"DASHBOARD_addNews"=>"Aggiungi notizie",
			"DASHBOARD_offlineNews"=>"Mostra notizie archiviate",
			"DASHBOARD_offlineNewsNb"=>"Notizie archiviate",//"55 actualités archivées"
			"DASHBOARD_noNews"=>"Nessuna notizia per il momento",
			"DASHBOARD_addPoll"=>"Aggiungi un sondaggio",
			"DASHBOARD_pollsVoted"=>"Mostra solo i sondaggi votati",
			"DASHBOARD_pollsVotedNb"=>"sondaggi per i quali ho già votato",//"55 sondaggi..déjà voté"
			"DASHBOARD_pollsNotVoted"=>"sondaggi non votati",//55 sondages non votés
			"DASHBOARD_vote"=>"Vota e vedi i risultati!",
			"DASHBOARD_voteTooltip"=>"I voti sono anonimi: nessuno saprà la tua scelta di voto",
			"DASHBOARD_answerVotesNb"=>"Votate --NB_VOTES-- volte",
			"DASHBOARD_pollVotesNb"=>"Il sondaggio è stato votato --NB_VOTES-- volte",
			"DASHBOARD_pollVotedBy"=>"Votato da",//Bibi, boby, ecc.
			"DASHBOARD_noPoll"=>"Nessun sondaggio per il momento",
			"DASHBOARD_plugins"=>"Nuovi elementi",
			"DASHBOARD_pluginsTooltip"=>"Elementi creati",
			"DASHBOARD_pluginsTooltip2"=>"tra",
			"DASHBOARD_plugins_day"=>"di oggi",
			"DASHBOARD_plugins_week"=>"di questa settimana",
			"DASHBOARD_plugins_month"=>"del mese",
			"DASHBOARD_plugins_previousConnection"=>"dall'ultimo accesso",
			"DASHBOARD_pluginsTooltipRedir"=>"Visualizza l'elemento nella cartella",
			"DASHBOARD_pluginEmpty"=>"Nessun nuovo elemento per questo periodo",
			// Actualite/News
			"DASHBOARD_topNews"=>"Top news",
			"DASHBOARD_topNewsTooltip"=>"Notizie in cima all'elenco",
			"DASHBOARD_offline"=>"Notizie archiviate",
			"DASHBOARD_dateOnline"=>"Data online",
			"DASHBOARD_dateOnlineTooltip"=>"Selezionare una data per mettere automaticamente online le notizie.<br>Nel frattempo, le notizie sono offline",
			"DASHBOARD_dateOnlineNotif"=>"Le notizie sono momentaneamente archiviate",
			"DASHBOARD_dateOffline"=>"Data di archiviazione",
			"DASHBOARD_dateOfflineTooltip"=>"Selezionare una data per archiviare automaticamente le notizie",
			// Sondage/Polls
			"DASHBOARD_titleQuestion"=>"Titolo / Domanda",
			"DASHBOARD_multipleResponses"=>"Sono possibili più risposte per ogni voto",
			"DASHBOARD_newsDisplay"=>"Mostra con le notizie (menu a sinistra)",
			"DASHBOARD_publicVote"=>"Voto pubblico: la scelta dei votanti è pubblica",
			"DASHBOARD_publicVoteInfos"=>"Si noti che il voto pubblico può costituire un ostacolo alla partecipazione al sondaggio",
			"DASHBOARD_dateEnd"=>"Fine delle votazioni",
			"DASHBOARD_responseList"=>"Risposte possibili",
			"DASHBOARD_responseNb"=>"Risposta n°",
			"DASHBOARD_addResponse"=>"Aggiungi una risposta",
			"DASHBOARD_controlResponseNb"=>"Specificare almeno 2 risposte possibili",
			"DASHBOARD_votedPollNotif"=>"Attenzione: non appena il sondaggio viene votato, non è più possibile modificare il titolo o le risposte",
			"DASHBOARD_voteNoResponse"=>"Selezionare una risposta",
			"DASHBOARD_exportPoll"=>"Scarica i risultati del sondaggio in formato pdf",
			"DASHBOARD_exportPollDate"=>"risultato del sondaggio a partire da",

			////	MOD : FILE
			////
			// Menu principale
			"FILE_headerModuleName"=>"File",
			"FILE_moduleDescription"=>"Festore di file",
			"FILE_option_adminRootAddContent"=>"Solo l'amministratore può aggiungere cartelle e file nella cartella principale",
			// Index
			"FILE_addFile"=>"Aggiungi file",
			"FILE_addFileAlert"=>"Cartella sul server non accessibile per iscritto! Si prega di contattare l'amministratore",
			"FILE_downloadSelection"=>"Selezione download",
			"FILE_fileDownload"=>"Download",
			"FILE_fileSize"=>"Dimensione file",
			"FILE_imageSize"=>"Dimensione immagine",
			"FILE_nbFileVersions"=>"Versioni del file",
			"FILE_downloadsNb"=>"(scaricato --NB_DOWNLOAD-- volte)",
			"FILE_downloadedBy"=>"file scaricato da",
			"FILE_addFileVersion"=>"aggiungi una nuova versione di file",
			"FILE_noFile"=>"Nessun file per il momento",
			// Edit
			"FILE_fileSizeLimit"=>"I file non devono superare",
			"FILE_uploadSimple"=>"Caricamento semplice",
			"FILE_uploadMultiple"=>"Caricamento multiplo",
			"FILE_imgReduce"=>"Ottimizza l'immagine",
			"FILE_updatedName"=>"Il nome del file verrà sostituito dalla nuova versione",
			"FILE_fileSizeError"=>"Il file è troppo grande",
			"FILE_addMultipleFilesTooltip"=>"Pulsante  Shift  o  Ctrl  per selezionare più file",
			"FILE_selectFile"=>"Grazie per aver selezionato almeno un file",
			"FILE_fileContent"=>"Contenuto",
			// Versions
			"FILE_versionsOf"=>"Versioni di",
			"FILE_confirmDeleteVersion"=>"Confermare la rimozione di questa versione?",

			////	MOD : CALENDAR
			////
			// Menu principal
			"CALENDAR_headerModuleName"=>"Calendario",
			"CALENDAR_moduleDescription"=>"Calendario personale e condiviso",
			"CALENDAR_option_adminAddRessourceCalendar"=>"Solo l'amministratore può aggiungere calendari di risorse",
			"CALENDAR_option_adminAddCategory"=>"Solo l'amministratore può aggiungere una categoria di eventi",
			"CALENDAR_option_createSpaceCalendar"=>"Crea un calendario condiviso per questo spazio",
			"CALENDAR_moduleAlwaysEnabledInfo"=>"Gli utenti che non hanno disattivato il calendario personale nel loro profilo utente vedranno comunque il modulo Calendario nella barra dei menu",
			// Index
			"CALENDAR_calsList"=>"Calendari disponibili",
			"CALENDAR_hideAllCals"=>"Nascondi tutti i calendari",
			"CALENDAR_printCalendars"=>"Stampa calendario",
			"CALENDAR_printCalendarsInfos"=>"Stampa la pagina in modalità orizzontale",
			"CALENDAR_addSharedCalendar"=>"Crea un calendario condiviso",
			"CALENDAR_addSharedCalendarTooltip"=>"Crea un calendario condiviso: per prenotare una camera, un veicolo, un videoproiettore, ecc.",
			"CALENDAR_exportIcal"=>"Esporta gli eventi (iCal)",
			"CALENDAR_icalUrl"=>"Copia il link/url per visualizzare il calendario su un applicazione esterna",
			"CALENDAR_icalUrlCopy"=>"Consente di leggere gli eventi del calendario tramite un applicazione esterna come Microsoft Outlook, Google Calendar, Mozilla Thunderbird, ecc,",
			"CALENDAR_importIcal"=>"Importa gli eventi (iCal)",
			"CALENDAR_ignoreOldEvt"=>"Non importare eventi più vecchi di un anno",
			"CALENDAR_importIcalPresent"=>"Già presente?",
			"CALENDAR_importIcalPresentInfo"=>"Evento già presente nel calendario ?",
			"CALENDAR_display_3Days"=>"3 giorni",
			"CALENDAR_display_7Days"=>"7 giorni",
			"CALENDAR_display_week"=>"Settimana",
			"CALENDAR_display_month"=>"Mese",
			"CALENDAR_yearWeekNum"=>"Vedi il numero della settimana", //...5
			"CALENDAR_periodNext"=>"Periodo successivo",
			"CALENDAR_periodPrevious"=>"Periodo precedente",
			"CALENDAR_evtAffects"=>"Nel calendario di",
			"CALENDAR_evtAffectToConfirm"=>"Conferma in attesa nel calendario di",
			"CALENDAR_evtProposed"=>"Proposte di eventi da confermare",
			"CALENDAR_evtProposedBy"=>"Proposto da",//..Mr SMITH
			"CALENDAR_evtProposedConfirm"=>"Conferma la proposta",
			"CALENDAR_evtProposedConfirmBis"=>"La proposta di evento è stata integrata nel calendario",
			"CALENDAR_evtProposedConfirmMail"=>"La proposta di evento è stata confermata",
			"CALENDAR_evtProposedDecline"=>"Rifiuta la proposta",
			"CALENDAR_evtProposedDeclineBis"=>"La proposta è stata rifiutata",
			"CALENDAR_evtProposedDeclineMail"=>"La proposta di evento è stata rifiutata",
			"CALENDAR_deleteEvtCal"=>"Cancellare solo per questo calendario?",
			"CALENDAR_deleteEvtCals"=>"Eliminare per tutti i calendari?",
			"CALENDAR_deleteEvtDate"=>"Cancellare solo per questa data?",
			"CALENDAR_evtPrivate"=>"Evento privato",
			"CALENDAR_evtAutor"=>"Eventi che ho creato",
			"CALENDAR_evtAutorInfo"=>"Mostra solo gli eventi che ho creato",
			"CALENDAR_noEvt"=>"Nessun evento",
			"CALENDAR_calendarsPercentBusy"=>"Calendari occupati",
			"CALENDAR_noCalendarDisplayed"=>"Nessun calendario visualizzato",
			// Evenement
			"CALENDAR_importanceNormal"=>"Importanza normale",
			"CALENDAR_importanceHight"=>"Alta importanza",
			"CALENDAR_visibilityPublic"=>"Visualizzazione normale",
			"CALENDAR_visibilityPublicHide"=>"Visualizzazione della fascia oraria",
			"CALENDAR_visibilityPrivate"=>"Visualizzazione privata",
			"CALENDAR_visibilityTooltip"=>"Per le persone che hanno accesso solo in lettura al calendario e all'evento:<br>- Visualizzazione della fascia oraria: mostra solo la fascia occupata e nasconde i dettagli<br>- Visualizzazione privata: non mostrare l'evento",
			// Edit
			"CALENDAR_sharedCalendarDescription"=>"Calendario condiviso dello spazio",
			"CALENDAR_noPeriodicity"=>"Solo una volta",
			"CALENDAR_period_weekDay"=>"Ogni settimana",
			"CALENDAR_period_month"=>"Ogni mese",
			"CALENDAR_period_year"=>"Ogni anno",
			"CALENDAR_periodDateEnd"=>"fino al",
			"CALENDAR_periodException"=>"Eccezione di ricorrenza",
			"CALENDAR_calendarAffectations"=>"Assegnazione ai seguenti calendari",
			"CALENDAR_addEvt"=>"Aggiungi un evento",
			"CALENDAR_addEvtTooltip"=>"Aggiungi un evento",
			"CALENDAR_addEvtTooltipBis"=>"Aggiungi l'evento al calendario",
			"CALENDAR_proposeEvtTooltip"=>"Proporre un evento all'amministratore del calendario",
			"CALENDAR_proposeEvtTooltipBis"=>"Proporre l'evento all'amministratore/proprietario del calendario",
			"CALENDAR_proposeEvtTooltipBis2"=>"Proporre l'evento all'amministratore/proprietario del calendario: il calendario è accessibile solo in lettura",
			"CALENDAR_inputProposed"=>"L'evento verrà proposto all'amministratore del calendario",
			"CALENDAR_verifCalNb"=>"Grazie per aver selezionato un calendario",
			"CALENDAR_noModifTooltip"=>"Modifica vietata perché non si ha accesso alla scrittura in questo calendario",
			"CALENDAR_editLimit"=>"Non sei l'autore dell'evento: puoi solo gestire le assegnazioni dei calendari",
			"CALENDAR_busyTimeSlot"=>"Lo slot è già occupato in questo calendario:",
			"CALENDAR_timeSlot"=>"Intervallo di tempo della visualizzazione della   settimana  ",
			"CALENDAR_propositionNotif"=>"Notifica via e-mail di ogni proposta di evento",
			"CALENDAR_propositionNotifTooltip"=>"Nota: ogni proposta di evento viene convalidata o invalidata dall'amministratore del calendario",
			"CALENDAR_propositionGuest"=>"Gli ospiti possono proporre eventi",
			"CALENDAR_propositionGuestTooltip"=>"Nota: ricordarsi di selezionare  tutti gli utenti e gli ospiti  nei diritti di accesso sottostanti.",
			"CALENDAR_propositionEmailSubject"=>"Nuovo evento proposto da",//.. "boby SMITH"
			"CALENDAR_propositionEmailMessage"=>"Nuovo evento proposto da --AUTOR_LABEL-- : &nbsp; <i><b>--EVT_TITLE_DATE--</b></i> <br><i>--EVT_DESCRIPTION--</i> <br>Accedi al tuo spazio per confermare o annullare questa proposta",
			// Category : Catégories d'événement
			"CALENDAR_categoryMenuTooltip"=>"Mostra solo gli eventi con categoria",
			"CALENDAR_categoryShowAll"=>"Tutte le categorie",
			"CALENDAR_categoryShowAllTooltip"=>"Mostra tutte le categorie",
			"CALENDAR_categoryUndefined"=>"Senza categoria",
			"CALENDAR_categoryEditTitle"=>"Modifica categorie",
			"CALENDAR_categoryEditInfo"=>"Ogni categoria di eventi può essere modificata dal suo autore o dall'amministratore generale",
			"CALENDAR_categoryEditAdd"=>"Aggiungi una categoria di eventi",

			////	MOD : FORUM
			////
			// Menu principal
			"FORUM_headerModuleName"=>"Forum",
			"FORUM_moduleDescription"=>"Forum di discussione",
			"FORUM_option_adminAddSubject"=>"Solo l'amministratore può aggiungere argomenti",
			"FORUM_option_adminAddTheme"=>"Solo l'amministratore può aggiungere temi",
			"SORT_dateLastMessage"=>"Ultimo messaggio",
			//Index & Sujet
			"FORUM_forumRoot"=>"Pagina iniziale del forum",
			"FORUM_subject"=>"Argomento",
			"FORUM_subjects"=>"Argomenti",
			"FORUM_message"=>"Messaggio",
			"FORUM_messages"=>"Messaggi",
			"FORUM_lastMessageFrom"=>"ultimo di",
			"FORUM_noSubject"=>"Nessun oggetto per il momento",
			"FORUM_subjectBy"=>"Oggetto da",
			"FORUM_addSubject"=>"Nuovo argomento",
			"FORUM_displaySubject"=>"Visualizza argomento",
			"FORUM_addMessage"=>"Risposta",
			"FORUM_quoteMessage"=>"Rispondi",
			"FORUM_quoteMessageInfo"=>"Rispondi e cita questo messaggio",
			"FORUM_notifyLastPost"=>"Notifica via e-mail",
			"FORUM_notifyLastPostTooltip"=>"Inviami una notifica via e-mail per ogni nuovo messaggio",
			// Edit
			"FORUM_notifOnlyReadAccess"=>"Se l'accesso è solo in lettura, nessuno può contribuire all'argomento",
			"FORUM_notifWriteAccess"=>" l'accesso in scrittura è destinato ai moderatori :<br>Preferite piuttosto i diritti di   Scrittura limitata  ",
			// Category : Themes
			"FORUM_categoryMenuTooltip"=>"Mostra solo argomenti con tema",
			"FORUM_categoryShowAll"=>"Tutti i temi",
			"FORUM_categoryShowAllTooltip"=>"Mostra tutti i temi",
			"FORUM_categoryUndefined"=>"Senza tema",
			"FORUM_categoryEditTitle"=>"Modifica temi",
			"FORUM_categoryEditInfo"=>"Ogni tema può essere modificato dal suo autore o dall'amministratore generale",
			"FORUM_categoryEditAdd"=>"Aggiungi un tema",

			////	MOD : TASK
			////
			// Menu principal
			"TASK_headerModuleName"=>"Attività",
			"TASK_moduleDescription"=>"Attività / Note",
			"TASK_option_adminRootAddContent"=>"Solo l'amministratore può aggiungere cartelle e attività nella cartella principale",
			"TASK_option_adminAddStatus"=>"Solo l'amministratore può creare uno stato Kanban",
			"SORT_priority"=>"Priorità",
			"SORT_advancement"=>"Avanzamento",
			"SORT_dateBegin"=>"Data inizio",
			"SORT_dateEnd"=>"Data fine",
			//Index
			"TASK_addTask"=>"Aggiungi un attività",
			"TASK_noTask"=>"Nessuna attività per il momento",
			"TASK_advancement"=>"Avanzamento",
			"TASK_advancementAverage"=>"Avanzamento medio",
			"TASK_priority"=>"Priorità",
			"TASK_priorityUndefined"=>"Priorità non definita",
			"TASK_priority1"=>"Bassa",
			"TASK_priority2"=>"Media",
			"TASK_priority3"=>"Alta",
			"TASK_assignedTo"=>"Assegnato a",
			"TASK_advancementLate"=>"Avanzamento ritardato",
			"TASK_folderDateBeginEnd"=>"Data di inizio più precoce / data di fine più recente",
			//Categorie : Statuts Kanban
			"TASK_categoryMenuTooltip"=>"Mostra solo le attività con stato",
			"TASK_categoryShowAll"=>"Tutti gli stati",
			"TASK_categoryShowAllTooltip"=>"Mostra tutti gli stati",			
			"TASK_categoryUndefined"=>"Stato non definito",
			"TASK_categoryEditTitle"=>"Modifica stato",
			"TASK_categoryEditInfo"=>"Ogni stato può essere modificato dal suo autore o dall'amministratore generale",
			"TASK_categoryEditAdd"=>"Aggiungi uno stato",

			////	MOD : CONTACT
			////
			// Menu principal
			"CONTACT_headerModuleName"=>"Contatti",
			"CONTACT_moduleDescription"=>"Directory di contatti",
			"CONTACT_option_adminRootAddContent"=>"Solo l'amministratore può aggiungere cartelle e contatti nella cartella principale",
			//Index
			"CONTACT_addContact"=>"Aggiungi un contatto",
			"CONTACT_noContact"=>"Nessun contatto per il momento",
			"CONTACT_createUser"=>"Crea un utente in questo spazio",
			"CONTACT_createUserConfirm"=>"Creare un utente in questo spazio a partire da questo contatto?",
			"CONTACT_createUserConfirmed"=>"L utente è stato creato con successo",

			////	MOD : LINK
			////
			// Menu principal
			"LINK_headerModuleName"=>"Segnalibri",
			"LINK_moduleDescription"=>"Segnalibri",
			"LINK_option_adminRootAddContent"=>"Solo l'amministratore può aggiungere cartelle e segnalibri alla cartella principale",
			//Index
			"LINK_addLink"=>"Aggiungi un segnalibro",
			"LINK_noLink"=>"Nessun segnalibro al momento",
			//Edit
			"LINK_adress"=>"segnalibro",

			////	MOD : MAIL
			////
			// Menu principal
			"MAIL_headerModuleName"=>"Email",
			"MAIL_moduleDescription"=>"Invia e-mail con un clic!",
			//Index
			"MAIL_specifyMail"=>"Grazie per aver inserito un indirizzo e-mail",
			"MAIL_title"=>"Oggetto dell'e-mail",
			"MAIL_description"=>"Messaggio e-mail",
			// Historique
			"MAIL_historyTitle"=>"Cronologia delle e-mail inviate",
			"MAIL_delete"=>"Elimina questa e-mail",
			"MAIL_resend"=>"Reinvia questa e-mail",
			"MAIL_resendInfo"=>"Recupera il contenuto di questa e-mail e lo integra direttamente nell'editor per un nuovo invio",
			"MAIL_historyEmpty"=>"Nessuna email",
		);
	}

	/*
	 * Jours Fériés de l'année
	 */
	public static function publicHolidays($year)
	{
		$dateList[$year."-01-01"]="Capodanno";
		$dateList[$year."-06-01"]="Epifania";
		$dateList[$year."-04-25"]="Festa Della Liberazione";
		$dateList[$year."-05-01"]="Festa del Lavoro";
		$dateList[$year."-06-02"]="Festa della Repubblica Italiana";
		$dateList[$year."-08-15"]="Assunzione";
		$dateList[$year."-11-01"]="Tutti i santi";
		$dateList[$year."-12-08"]="Immacolata Concezione";
		$dateList[$year."-12-25"]="Natale";
		$dateList[$year."-12-26"]="Santo Stefano";
		if(function_exists("easter_date")){
			$easterTime=easter_date($year);
			$dateList[date("Y-m-d",$easterTime)]		="Pasqua";
			$dateList[date("Y-m-d",$easterTime+86400)]	="Lunedì di Pasquetta";
		}
		return $dateList;
	}
}