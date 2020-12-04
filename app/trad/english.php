<?php
/*
 * Classe de gestion d'une langue
 */
class Trad extends Txt
{
	/*
	 * Chargement les elements de traduction
	 */
	public static function loadTradsLang()
	{
		////	Header http / Editeurs Tinymce,DatePicker,etc / Dates formatées par PHP
		self::$trad["CURLANG"]="en";
		self::$trad["HEADER_HTTP"]="en";
		self::$trad["DATEPICKER"]="en";
		self::$trad["HTML_EDITOR"]="en_GB";//pas "null"
		self::$trad["UPLOADER"]="en";
		setlocale(LC_TIME, "en_US.utf8", "en_US.UTF-8", "en_US", "en", "english");

		////	Divers
		self::$trad["OK"]="OK";
		self::$trad["fillAllFields"]="Thank you for entering all the fields";
		self::$trad["requiredFields"]="Required Fields";
		self::$trad["inaccessibleElem"]="Inaccessible Element";
		self::$trad["warning"]="Warning";
		self::$trad["elemEditedByAnotherUser"]="The element is being edited by";//"..bob"
		self::$trad["yes"]="yes";
		self::$trad["no"]="no";
		self::$trad["none"]="no";
		self::$trad["noneFem"]="no";
		self::$trad["or"]="or";
		self::$trad["and"]="and";
		self::$trad["goToPage"]="Go to page";
		self::$trad["alphabetFilter"]="Alphabetical Filter";
		self::$trad["displayAll"]="Display all";
		self::$trad["allCategory"]="Any category";
		self::$trad["show"]="Show";
		self::$trad["hide"]="Hide";
		self::$trad["byDefault"]="By default";
		self::$trad["mapLocalize"]="Localize on the map";
		self::$trad["mapLocalizationFailureLeaflet"]="Localization of the following address failed";
		self::$trad["mapLocalizationFailureLeaflet2"]="Please check that the following address exists on www.google.com/maps or www.openstreetmap.org";
		self::$trad["sendMail"]="Send an email";
		self::$trad["mailInvalid"]="This email is not valid";
		self::$trad["element"]="element";
		self::$trad["elements"]="elements";
		self::$trad["folder"]="folder";
		self::$trad["folders"]="folders";
		self::$trad["close"]="Close";
		self::$trad["visibleAllSpaces"]="Visible on all spaces";
		self::$trad["confirmCloseForm"]="Are you sure you want to close the form?";
		self::$trad["modifRecorded"]="The changes were saved";
		self::$trad["confirm"]="Confirm ?";
		self::$trad["comment"]="Comment";
		self::$trad["commentAdd"]="Add a comment";
		self::$trad["optional"]="(optional)";
		self::$trad["objNew"]="Recently created item";
		self::$trad["objNewInfos"]="Created since my previous login or created within 24 hours";
		self::$trad["personalAccess"]="Personal access";
		self::$trad["copyUrl"]="Copy the web address for direct access";
		self::$trad["copyUrlInfo"]="The address can be integrated into a news, forum, event, etc : to directly access the element. The address can also be used in an email, blog, etc.";
		self::$trad["copyUrlConfirmed"]="The web address has been copied successfully.";
		////	images
		self::$trad["picture"]="Picture";
		self::$trad["wallpaper"]="wallpaper";
		self::$trad["keepImg"]="Keep image";
		self::$trad["changeImg"]="Change image";
		self::$trad["pixels"]="pixels";

		////	Connexion
		self::$trad["specifyLoginPassword"]="Thank you for choosing a login and a password";
		self::$trad["specifyLogin"]="Thank you for choosing an email/login (without space)";
		self::$trad["specifyLoginMail"]="It is recommended to use an email as login";
		self::$trad["login"]="Email / Login";
		self::$trad["loginPlaceholder"]="Email / Login";
		self::$trad["connect"]="Log In";
		self::$trad["connectAuto"]="Remember me";
		self::$trad["connectAutoInfo"]="Save my login and password to automatically connect";
		self::$trad["gSigninButton"]="Login with Google";
		self::$trad["gSigninButtonInfo"]="Sign in with your Gmail account : to do this, you must already have an account on this space, with an email address <i>@gmail.com</i>";
		self::$trad["gSigninUserNotRegistered"]="is not registered on the space with the email";
		self::$trad["switchOmnispace"]="Switch to another space Omnispace";
		self::$trad["guestAccess"]="Connect to a public space as a guest";
		self::$trad["spacePassError"]="Wrong password";
		self::$trad["ieObsolete"]="Your browser is outdated and does not support all HTML standards : It is advisable to update it or use another browser";
		
		////	Password : connexion d'user / edition d'user / reset du password
		self::$trad["password"]="Password";
		self::$trad["passwordModify"]="Change password";
		self::$trad["passwordToModify"]="Password (to change)";
		self::$trad["passwordVerif"]="Confirm password";
		self::$trad["passwordInfo"]="Leave blank if you want to keep your password";
		self::$trad["passwordInvalid"]="Your password must have at least 6 characters with at least 1 digit and at least 1 letter";
		self::$trad["passwordConfirmError"]="Your confirmation password is not valid";
		self::$trad["specifyPassword"]="Thank you to specify a password";
		self::$trad["resetPassword"]="Forgotten login info ?";
		self::$trad["resetPassword2"]="Enter your email address to receive your login and password";
		self::$trad["resetPasswordNotif"]="An email has just been sent to your address to reset your password. If you have not received an email, please verify that the address specified is correct, or that the email is not in your spams.";
		self::$trad["resetPasswordMailTitle"]="Reset your password";
		self::$trad["resetPasswordMailPassword"]="To login to your space and reset your password";
		self::$trad["resetPasswordMailPassword2"]="Please click here";
		self::$trad["resetPasswordMailLoginRemind"]="Login reminder";
		self::$trad["resetPasswordIdExpired"]="The link to reset your password has expired .. Please restart the procedure";
		
		////	Type d'affichage
		self::$trad["displayMode"]="View";
		self::$trad["displayMode_line"]="List";
		self::$trad["displayMode_block"]="Block";
		
		////	Sélectionner / Déselectionner tous les éléments
		self::$trad["select"]="Select";
		self::$trad["selectUnselect"]="Select / Unselect";
		self::$trad["selectUnselectAll"]="Select/unselect all";
		self::$trad["selectAll"]="Select all";
		self::$trad["invertSelection"]="Reverse the selection";
		self::$trad["deleteElems"]="Remove the selected elements";
		self::$trad["changeFolder"]="Move in another folder";
		self::$trad["showOnMap"]="Show on a map";
		self::$trad["selectUser"]="Thank you for selecting a user";
		self::$trad["selectUsers"]="Thank you for selecting at least 2 users";
		self::$trad["selectSpace"]="Thank you for selecting at least one space";
		
		////	Temps ("de 11h à 12h", "le 25-01-2007 à 10h30", etc.)
		self::$trad["from"]="of ";
		self::$trad["at"]="to";
		self::$trad["the"]="the";
		self::$trad["begin"]="Begin";
		self::$trad["end"]="End";
		self::$trad["hourSeparator"]=":";
		self::$trad["days"]="days";
		self::$trad["day_1"]="Monday";
		self::$trad["day_2"]="Tuesday";
		self::$trad["day_3"]="Wednesday";
		self::$trad["day_4"]="Thursday";
		self::$trad["day_5"]="Friday";
		self::$trad["day_6"]="Saturday";
		self::$trad["day_7"]="Sunday";
		self::$trad["month_1"]="January";
		self::$trad["month_2"]="February";
		self::$trad["month_3"]="March";
		self::$trad["month_4"]="April";
		self::$trad["month_5"]="May";
		self::$trad["month_6"]="June";
		self::$trad["month_7"]="July";
		self::$trad["month_8"]="August";
		self::$trad["month_9"]="September";
		self::$trad["month_10"]="October";
		self::$trad["month_11"]="November";
		self::$trad["month_12"]="December";
		self::$trad["today"]="Today";
		self::$trad["displayToday"]="Today";
		self::$trad["beginEndError"]="The end date can't be before the start date";
		self::$trad["dateFormatError"]="The date must be in the format dd/mm/YYYY";
		
		////	Nom & Description (pour les menus d'édition principalement)
		self::$trad["title"]="Title";
		self::$trad["name"]="Name";
		self::$trad["description"]="Description";
		self::$trad["specifyName"]="Thank you for specifying a name";
		self::$trad["editorDraft"]="Retrieve my text";
		self::$trad["editorDraftConfirm"]="Retrieve the last specified text";
		
		////	Validation des formulaires
		self::$trad["add"]=" Add";
		self::$trad["modify"]=" Modify";
		self::$trad["modifyAndAccesRight"]="Modify & define access";
		self::$trad["validate"]=" Validate";
		self::$trad["send"]="Send";
		self::$trad["sendTo"]="Send to";
		
		////	Tri d'affichage. Tous les elements (dossier, tache, lien, etc...) ont par défaut une date, un auteur & une description
		self::$trad["sortBy"]="Sorted by";
		self::$trad["sortBy2"]="Sort by";
		self::$trad["SORT_dateCrea"]="Creation date";
		self::$trad["SORT_dateModif"]="Change date";
		self::$trad["SORT_title"]="Title";
		self::$trad["SORT_description"]="Description";
		self::$trad["SORT__idUser"]="Author";
		self::$trad["SORT_extension"]="Type of file";
		self::$trad["SORT_octetSize"]="Size";
		self::$trad["SORT_downloadsNb"]="downloads";
		self::$trad["SORT_civility"]="Title";
		self::$trad["SORT_name"]="Last name";
		self::$trad["SORT_firstName"]="First name";
		self::$trad["SORT_adress"]="Address";
		self::$trad["SORT_postalCode"]="Zip code";
		self::$trad["SORT_city"]="City";
		self::$trad["SORT_country"]="Country";
		self::$trad["SORT_function"]="function";
		self::$trad["SORT_companyOrganization"]="Company / Organization";
		self::$trad["tri_ascendant"]="Ascend";
		self::$trad["tri_descendant"]="Descend";
		
		////	Options de suppression
		self::$trad["confirmDelete"]="Confirm the deletion ?";
		self::$trad["confirmDeleteNbElems"]="items selected";//"55 éléments sélectionnés"
		self::$trad["confirmDeleteDbl"]="Are you sure ?!";
		self::$trad["confirmDeleteFolderAccess"]="Caution ! certain sub-folders are not accessible for you : they will be deleted !";
		self::$trad["notifyBigFolderDelete"]="Deleting --NB_FOLDERS-- sub-folders can be a little large, please wait a few moments before the end of the process";
		self::$trad["delete"]="Delete";
		self::$trad["notDeletedElements"]="Some items have not been deleted because you do not have the necessary access rights";
		
		////	Visibilité d'un Objet : auteur et droits d'accès
		self::$trad["autor"]="Author";
		self::$trad["postBy"]="Post by";
		self::$trad["guest"]="Guest";
		self::$trad["creation"]="Creation";
		self::$trad["modification"]="Modification";
		self::$trad["createBy"]="Created by";
		self::$trad["modifBy"]="Modified by";
		self::$trad["objHistory"]="Element history";
		self::$trad["all"]="all";
		self::$trad["deletedUser"]="deleted user account";
		self::$trad["accessRead"]="Read";
		self::$trad["readInfos"]="Access in reading";
		self::$trad["accessWriteLimit"]="limited writing";
		self::$trad["readLimitInfos"]="Limited access in writing: Ability to add -OBJCONTENT-s, without modify or delete those created by other users";
		self::$trad["accessWrite"]="write";
		self::$trad["writeInfos"]="Access in writing";
		self::$trad["writeInfosContainer"]="Access in writing : Ability to add, modify or delete all the -OBJCONTENT-s of the -OBJLABEL-";
		self::$trad["autorPrivilege"]="Only the author and administrators can edit the access rights or delete the -OBJLABEL-";
		self::$trad["accessRightsInherited"]="Access rights inherited from the -OBJLABEL-";
		self::$trad["folderContent"]="content";
		
		////	Libellé des objets
		self::$trad["OBJECTcontainer"]="container";
		self::$trad["OBJECTelement"]="element";
		self::$trad["OBJECTfolder"]="folder";
		self::$trad["OBJECTdashboardNews"]="news";
		self::$trad["OBJECTdashboardPoll"]="poll";
		self::$trad["OBJECTfile"]="file";
		self::$trad["OBJECTfileFolder"]="folder";
		self::$trad["OBJECTcalendar"]="calendar";
		self::$trad["OBJECTcalendarEvent"]="event";
		self::$trad["OBJECTforumSubject"]="topic";
		self::$trad["OBJECTforumMessage"]="message";
		self::$trad["OBJECTcontact"]="contact";
		self::$trad["OBJECTcontactFolder"]="folder";
		self::$trad["OBJECTlink"]="bookmark";
		self::$trad["OBJECTlinkFolder"]="folder";
		self::$trad["OBJECTtask"]="task";
		self::$trad["OBJECTtaskFolder"]="folder";
		self::$trad["OBJECTuser"]="user";
		
		////	Envoi d'un email (nouvel utilisateur, notification de création d'objet, etc...)
		self::$trad["MAIL_hello"]="Hello";
		self::$trad["MAIL_receptionNotif"]="Delivery receipt";
		self::$trad["MAIL_receptionNotifInfo"]="Warning! some email clients don't support delivery receipts";
		self::$trad["MAIL_addReplyTo"]="Add 'Reply to'";
		self::$trad["MAIL_addReplyToInfo"]="Add my email address in the 'Reply to' field. Be careful because if there are too many recipients, the email can be considered as Spam";
		self::$trad["MAIL_hideRecipients"]="Hide recipients";
		self::$trad["MAIL_hideRecipientsInfo"]="By default, email recipients are displayed in the message.";
		self::$trad["MAIL_noFooter"]="Do not sign the message";
		self::$trad["MAIL_noFooterInfo"]="Do not sign the end of the message with the sender's name and a weblink to the space";
		self::$trad["MAIL_fielMaxSize"]="File size limited to 15 MB, because some mailboxes do not accept files beyond this limit and may refuse the email";
		self::$trad["MAIL_sendBy"]="Sent by";  // "Envoyé par" Mr trucmuche
		self::$trad["MAIL_sendOk"]="The email was sent !";
		self::$trad["MAIL_sendNotif"]="The notification email was sent !";
		self::$trad["MAIL_notSend"]="The email could not be sent...";
		self::$trad["MAIL_fromTheSpace"]="from the space";//Mon espace
		self::$trad["MAIL_elemCreatedBy"]="-OBJLABEL- created by";//boby
		self::$trad["MAIL_elemModifiedBy"]="-OBJLABEL- modified by";//boby
		self::$trad["MAIL_elemAccessLink"]="Click here to access the element on your space";

		////	Dossier & fichier
		self::$trad["gigaOctet"]="Gb";
		self::$trad["megaOctet"]="Mb";
		self::$trad["kiloOctet"]="Kb";
		self::$trad["rootFolder"]="Root folder";
		self::$trad["rootFolderEditInfo"]="Open the the space settings<br> to change the access rights to the root folder";
		self::$trad["addFolder"]="add a folder";
		self::$trad["download"]="Download file";
		self::$trad["downloadFolder"]="Download folder";
		self::$trad["diskSpaceUsed"]="Disk space used";
		self::$trad["diskSpaceUsedModFile"]="Disk space used for the File manager";
		self::$trad["downloadAlert"]="Your archive is too large to download during the day (--ARCHIVE_SIZE--). Please restart the download after";//"19h"
		
		////	Infos sur une personne
		self::$trad["civility"]="Title";
		self::$trad["name"]="Name";
		self::$trad["firstName"]="First name";
		self::$trad["adress"]="Address";
		self::$trad["postalCode"]="Zip code";
		self::$trad["city"]="City";
		self::$trad["country"]="country";
		self::$trad["telephone"]="Phone";
		self::$trad["telmobile"]="Mobile Phone";
		self::$trad["mail"]="Email";
		self::$trad["function"]="Function";
		self::$trad["companyOrganization"]="Company /Organization";
		self::$trad["lastConnection"]="Last login";
		self::$trad["lastConnection2"]="Logged in on";
		self::$trad["lastConnectionEmpty"]="Not logged in yet";
		self::$trad["displayProfil"]="Display Profile";
		
		////	Captcha
		self::$trad["captcha"]="Copy the 4 characters";
		self::$trad["captchaInfo"]="Thank you for entering the 4 characters for your identification";
		self::$trad["captchaSpecify"]="Thank you for completing the visual identification";
		self::$trad["captchaError"]="The visual identification is false";
		
		////	Rechercher
		self::$trad["searchSpecifyText"]="Thank you for entering key words of at least 3 characters";
		self::$trad["search"]="Search";
		self::$trad["searchDateCrea"]="Creation date";
		self::$trad["searchDateCreaDay"]="less than one day";
		self::$trad["searchDateCreaWeek"]="less than a week";
		self::$trad["searchDateCreaMonth"]="less than one month";
		self::$trad["searchDateCreaYear"]="less than a year";
		self::$trad["searchOnSpace"]="Search in this space";
		self::$trad["advancedSearch"]= "Advanced Search";
		self::$trad["advancedSearchSomeWords"]= "any word";
		self::$trad["advancedSearchAllWords"]= "all words";
		self::$trad["advancedSearchExactPhrase"]= "exact phrase";
		self::$trad["keywords"]="Key words";
		self::$trad["listModules"]="Modules";
		self::$trad["listFields"]="Fields";
		self::$trad["listFieldsElems"]="Elements involved";
		self::$trad["noResults"]="No result";
		
		////	Gestion des inscriptions d'utilisateur
		self::$trad["userInscription"]="Register on this space";
		self::$trad["userInscriptionInfo"]="Create a new user account (validated by an administrator)";
		self::$trad["userInscriptionSpace"]="Register on the space";
		self::$trad["userInscriptionRecorded"]="Your registration was saved : it will be validated as soon as possible by the administrator of the space";
		self::$trad["userInscriptionOptionSpace"]="Allow visitors to register on the space";
		self::$trad["userInscriptionOptionSpaceInfo"]="The registration is on the homepage of the site. Registration must then be validated by the administrator of the space.";
		self::$trad["userInscriptionValidate"]="Validate user registration";
		self::$trad["userInscriptionValidateInfo"]="Validate user registration on the site";
		self::$trad["userInscriptionInvalidateButton"]="Invalidate registrations";
		self::$trad["userInscriptionInvalidateMail"]="Your account has not been validated on";

		////	Importer ou Exporter : Contact OU Utilisateurs
		self::$trad["export"]="Export";
		self::$trad["import"]="Import";
		self::$trad["importExport_user"]="Users";
		self::$trad["importExport_contact"]="Contacts";
		self::$trad["exportFormat"]="Format";
		self::$trad["specifyFile"]="Thank you for choosing a file";
		self::$trad["fileExtension"]="The file type is invalid. It must be of the type";
		self::$trad["importInfo"]="Select the Agora's fields to target, thanks to the dropdown of each column";
		self::$trad["importNotif"]="Thank you for selecting the name's column in the select boxes";
		self::$trad["importNotif2"]="Thank you for selecting a contact to import";
		self::$trad["importNotif3"]="this agora's field has already been selected in another column (each agora's fields can be selected only once)";

		////	Messages d'alert ou d'erreur
		self::$trad["NOTIF_identification"]="Invalid login or password";
		self::$trad["NOTIF_presentIp"]="This user account is currently being used from another computer, with another ip address. An account can only be used on one computer at the same time.";
		self::$trad["NOTIF_noSpaceAccess"]="Access is not authorized: your user account exists, but it is not currently assigned to any space.";
		self::$trad["NOTIF_fileOrFolderAccess"]="File or folder not accessible";
		self::$trad["NOTIF_diskSpace"]="Space for the storage of your files is insufficient, you cannot add file";
		self::$trad["NOTIF_fileVersionForbidden"]="File type not allowed";
		self::$trad["NOTIF_fileVersion"]="File type different from the original";
		self::$trad["NOTIF_folderMove"]="You cannot move the folder inside..!";
		self::$trad["NOTIF_duplicateName"]="A folder or file with the same name already exists";
		self::$trad["NOTIF_fileName"]="A file with the same name already exists (but not replaced with the current file)";
		self::$trad["NOTIF_chmodDATAS"]="The ''DATAS'' folder is not accessible in writing. You need to give a read-write access to the owner and the group (''chmod 775'').";
		self::$trad["NOTIF_usersNb"]="You cannot add new user: limited to "; // "...limité à" 10
		self::$trad["NOTIF_update"]="Updated on";
		
		////	header menu / Footer
		self::$trad["HEADER_displaySpace"]="Available spaces";
		self::$trad["HEADER_displayAdmin"]="Administrator view";
		self::$trad["HEADER_displayAdminEnabled"]="Administrator view enabled";
		self::$trad["HEADER_displayAdminInfo"]="Show all elements of the current space (reserved for administrators)";
		self::$trad["HEADER_searchElem"]="Search in the space";
		self::$trad["HEADER_documentation"]="Documentation";
		self::$trad["HEADER_disconnect"]="Log out from Agora";
		self::$trad["HEADER_shortcuts"]="Shortcuts";
		self::$trad["MESSENGER_messenger"]="Instant messaging";
		self::$trad["MESSENGER_messengerInfo"]="Chat with several persons at the same time";
		self::$trad["MESSENGER_connected"]="Online";
		self::$trad["MESSENGER_connectedSince"]="connected at";//connecté depuis 12:45
		self::$trad["MESSENGER_connectedNobody"]="No one is connected";
		self::$trad["MESSENGER_connectedNobodyInfo"]="No one is currently connected : see old messages";
		self::$trad["MESSENGER_sendAt"]="Sent to";
		self::$trad["MESSENGER_addMessageToSelection"]="My message (selected persons)";
		self::$trad["MESSENGER_addMessageTo"]="My message to";
		self::$trad["MESSENGER_addMessageNotif"]="Thank you to specify a message";
		self::$trad["MESSENGER_visioProposeTo"]="Propose a video call to";//..boby
		self::$trad["MESSENGER_visioProposeToSelection"]="Propose a video call to the selected people";
		self::$trad["MESSENGER_userProposeVisio"]="Propose you a video call. Click here to start the call";//boby.. "propose un appel visio"
		self::$trad["MESSENGER_visioProposalPending"]="The video proposal has been sent : Click on the instant messaging link to start the video.<br><br> Remember to allow access to your webcam and microphone by selecting ''Allow'' in browser requests !";
		self::$trad["MESSENGER_visioProposalLanch"]="Start the video call ?";
		self::$trad["FOOTER_pageGenerated"]="page generated in";
		
		////	vueObjMenuEdit
		self::$trad["EDIT_notifNoSelection"]="You must select at least a person or a space";
		self::$trad["EDIT_notifNoPersoAccess"]="You are not assigned to the element. validate all the same ?";
		self::$trad["EDIT_notifWriteAccess"]="There must be at least a person or a space assigned in writing";
		self::$trad["EDIT_parentFolderAccessError"]="Remember to check the access rights of the parent folder ''<i>--FOLDER_NAME--</i>'': If it is not also assigned to ''<i>--TARGET_LABEL--</i>'', the present file will not be accessible to him.";
		self::$trad["EDIT_accessRight"]="Access rights";
		self::$trad["EDIT_accessRightContent"]="Access rights to the content";
		self::$trad["EDIT_spaceNoModule"]="The current module has not yet been added to this space";
		self::$trad["EDIT_allUsers"]="All users";
		self::$trad["EDIT_allUsersAndGuests"]="All users and guests";
		self::$trad["EDIT_allUsersInfo"]="All the users of the space <i>--SPACENAME--</i>";
		self::$trad["EDIT_allUsersAndGuestsInfo"]="All the users of the space <i>--SPACENAME--</i> and guests but with a read only access (guests : people who do not have a user account)";
		self::$trad["EDIT_adminSpace"]="Administrator of this space:<br>write access to all the elements of this space";
		self::$trad["EDIT_showAllSpaceUsers"]="Show all users";
		self::$trad["EDIT_mySpaces"]="Display all my spaces";
		self::$trad["EDIT_notifMail"]="Notify";
		self::$trad["EDIT_notifMail2"]="Send a notification of creation/modification by email";
		self::$trad["EDIT_notifMailInfo"]="If you don't select recipients, by default, it will be sent to the persons who are affected to the element";
		self::$trad["EDIT_notifMailAddFiles"]="Attach files to the notification";
		self::$trad["EDIT_notifMailSelect"]="Select the recipients of notifications";
		self::$trad["EDIT_notifMailMoreUsers"]="Display more users";
		self::$trad["EDIT_accessRightSubFolders"]="Assign the same access rights to the under-folders";
		self::$trad["EDIT_accessRightSubFolders_info"]="Extend rights of access, to subfolders that you can edit";
		self::$trad["EDIT_shortcut"]="Shortcut";
		self::$trad["EDIT_shortcutInfo"]="Put a shortcut on the main menu";
		self::$trad["EDIT_attachedFile"]="Add attached files";
		self::$trad["EDIT_attachedFileInfo"]="Attach pictures, videos, Pdf, Word, etc to the current object.<br>Images and videos can be integrated directly to the text editor.";
		self::$trad["EDIT_attachedFileInsert"]="Display in the description";
		self::$trad["EDIT_attachedFileInsertInfo"]="Display the image / video / mp3 player ... in the description above. The insertion is performed after form validation.";
		self::$trad["EDIT_guestName"]="Your Name / Nickname";
		self::$trad["EDIT_guestNameNotif"]="Thank you to specify a Name / Nickname";
		self::$trad["EDIT_guestElementRegistered"]="Thanks for your proposition. This will be examined as soon as possible before validation";
		
		////	Formulaire d'installation
		self::$trad["INSTALL_dbConnect"]="Connection to the database";
		self::$trad["INSTALL_dbHost"]="Hostname of the databases server";
		self::$trad["INSTALL_dbName"]="Name of the database";
		self::$trad["INSTALL_dbLogin"]="User name";
		self::$trad["INSTALL_adminAgora"]="Information about the administrator of the ";
		self::$trad["INSTALL_dbErrorDbName"]="Warning: the name of the database should preferably contain only alphanumeric characters and dashes or underscores";
		self::$trad["INSTALL_dbErrorUnknown"]="The connection to the MariaDB/MySQL database failed";
		self::$trad["INSTALL_dbErrorIdentification"]="The identification to the MariaDB/MySQL database failed";
		self::$trad["INSTALL_dbErrorAppInstalled"]="The installation has already been done. Thank you to remove the database whether to restart the installation.";
		self::$trad["INSTALL_PhpOldVersion"]="Agora-Project requires a newer version of PHP";
		self::$trad["INSTALL_confirmInstall"]="Confirm the installation ?";
		self::$trad["INSTALL_installOk"]="Agora-Project was installed correctly !";
		self::$trad["INSTALL_spaceDescription"]="Space for sharing and collaborative work";
		self::$trad["INSTALL_dataDashboardNews1"]="Welcome on your new Omnispace !";
		self::$trad["INSTALL_dataDashboardNews2"]="Click here to invite people to join you";
		self::$trad["INSTALL_dataDashboardNews3"]="Share your files, a Calendar and a News Feed, manage Tasks and Projects, exchange Contacts or Internet Links, chat on a Forum or Instant Messaging, send Newsletter, etc.";
		self::$trad["INSTALL_dataDashboardPoll"]="What do you think of the new survey tool?";
		self::$trad["INSTALL_dataDashboardPollA"]="Very interesting !";
		self::$trad["INSTALL_dataDashboardPollB"]="Interesting";
		self::$trad["INSTALL_dataDashboardPollC"]="Not interesting";
		self::$trad["INSTALL_dataCalendarEvt"]="Welcome on Omnispace !";
		self::$trad["INSTALL_dataForumSubject1"]="Welcome to the Omnispace forum !";
		self::$trad["INSTALL_dataForumSubject2"]="Feel free to share your questions or discuss the topics you want to share.";

		////	MODULE_PARAMETRAGE
		////
		self::$trad["AGORA_headerModuleName"]="General settings";
		self::$trad["AGORA_generalSettings"]="General Settings";
		self::$trad["AGORA_backupFull"]="Backup all files";
		self::$trad["AGORA_backupNotif"]="The creation of the backup file may take a few minute ... and download a few dozen minutes.";
		self::$trad["AGORA_backupDb"]="Backup database";
		self::$trad["AGORA_diskSpaceInvalid"]="The limiting disk space must be an entirety";
		self::$trad["AGORA_confirmModif"]="Confirm modifications ?";
		self::$trad["AGORA_name"]="Site name";
		self::$trad["AGORA_footerHtml"]="Footer text/html";
		self::$trad["AGORA_lang"]="Language by default";
		self::$trad["AGORA_timezone"]="Timezone";
		self::$trad["AGORA_spaceName"]="Name of principal space";
		self::$trad["AGORA_diskSpaceLimit"]="Space available for the storage of the files";
		self::$trad["AGORA_logsTimeOut"]="Duration of event history (logs)";
		self::$trad["AGORA_logsTimeOutInfo"]="The retention period of the events history concerns the addition or modification of the elements. The deletion logs are kept for at least 1 year.";
		self::$trad["AGORA_visioHost"]="Jitsi videocall server";
		self::$trad["AGORA_visioHostInfo"]="Jitsi videocall server address";
		self::$trad["AGORA_skin"]="Color of the interface";
		self::$trad["AGORA_black"]="Black";
		self::$trad["AGORA_white"]="White";
		self::$trad["AGORA_wallpaperLogoError"]="The wallpaper and the logo must have a .jpg or .png extension";
		self::$trad["AGORA_deleteWallpaper"]="Delete the wallpaper";
		self::$trad["AGORA_logo"]="Logo at the bottom of each page";
		self::$trad["AGORA_logoUrl"]="URL";
		self::$trad["AGORA_logoConnect"]="Logo / Image on login page";
		self::$trad["AGORA_logoConnectInfo"]="Displayed above the login form";
		self::$trad["AGORA_usersCommentLabel"]="Allow users to comment the item";
		self::$trad["AGORA_usersComment"]="comment";
		self::$trad["AGORA_usersComments"]="comments";
		self::$trad["AGORA_usersLikeLabel"]="Users can <i>Like</i> the item";
		self::$trad["AGORA_usersLike_likeSimple"]="Only like";
		self::$trad["AGORA_usersLike_likeOrNot"]="Like / Dislike";
		self::$trad["AGORA_usersLike_like"]="Like!";
		self::$trad["AGORA_usersLike_dontlike"]="Dislike";
		self::$trad["AGORA_mapTool"]="Mapping tool";
		self::$trad["AGORA_mapToolInfo"]="Mapping tool to see users and contacts on a map";
		self::$trad["AGORA_mapApiKey"]="API Key for mapping tool";
		self::$trad["AGORA_mapApiKeyInfo"]="API Key for Google Map mapping tool";
		self::$trad["AGORA_gSignin"]="Optional connection via Gmail (Sign-In)";
		self::$trad["AGORA_gSigninInfo"]="Users can connect more easily to their space through their Gmail account : for that, an email <i>@gmail.com</ i> must already be registered on the account of the user.";
		self::$trad["AGORA_gSigninClientId"]="Google Sign-In settings : Client ID";
		self::$trad["AGORA_gSigninClientIdInfo"]="This setting is required to enable Google Sign-In : https://developers.google.com/identity/sign-in/web/";
		self::$trad["AGORA_gPeopleApiKey"]="Google People settings :  API KEY";
		self::$trad["AGORA_gPeopleApiKeyInfo"]="This setting is required to get Gmail contacts (People 'API KEY') : <a href='https://developers.google.com/people/' target='_blank'>https://developers.google.com/people/</a>";
		self::$trad["AGORA_messengerDisabled"]="Instant messenger enabled";
		self::$trad["AGORA_moduleLabelDisplay"]="Name of modules in the menu bar";
		self::$trad["AGORA_personsSort"]="Sort users and contacts";
		self::$trad["AGORA_versions"]="Versions";
		self::$trad["AGORA_dateUpdate"]="Updated on";
		self::$trad["AGORA_Changelog"]="View the version log";
		self::$trad["AGORA_funcMailDisabled"]="PHP function to send email : disabled !";
		self::$trad["AGORA_funcMailInfo"]="Some hosters disable the PHP function for sending emails for security reasons or saturation servers (SPAM)";
		self::$trad["AGORA_funcImgDisabled"]="Function of handling images and creation of thumbs (PHP GD2) : disabled !";
		//SMTP
		self::$trad["AGORA_smtpLabel"]="Connecting SMTP & sendMail";
		self::$trad["AGORA_sendmailFrom"]="Email in the 'From' field";
		self::$trad["AGORA_sendmailFromPlaceholder"]="ex: 'noreply@my-domain.net'";
		self::$trad["AGORA_smtpHost"]="Server address (hostname)";
		self::$trad["AGORA_smtpPort"]="Port server";
		self::$trad["AGORA_smtpPortInfo"]="'25' by défault. '587' or '465' for SSL/TLS";
		self::$trad["AGORA_smtpSecure"]="Encrypted connection type (option)";
		self::$trad["AGORA_smtpSecureInfo"]="'ssl' or 'tls'";
		self::$trad["AGORA_smtpUsername"]="Username";
		self::$trad["AGORA_smtpPass"]="Password";
		//LDAP
		self::$trad["AGORA_ldapLabel"]="Connecting to an LDAP server";
		self::$trad["AGORA_ldapHost"]="Server address";
		self::$trad["AGORA_ldapPort"]="Port server";
		self::$trad["AGORA_ldapPortInfo"]="''389'' by default";
		self::$trad["AGORA_ldapLogin"]="String connection for admin";
		self::$trad["AGORA_ldapLoginInfo"]="for example ''uid=admin,ou=my_company''";
		self::$trad["AGORA_ldapPass"]="Password of the admin";
		self::$trad["AGORA_ldapDn"]="Group / base DN";
		self::$trad["AGORA_ldapDnInfo"]="Location of directory users.<br> For example ''ou=users,o=my_company''";
		self::$trad["AGORA_ldapConnectError"]="Error connecting to LDAP server !";
		self::$trad["AGORA_ldapCreaAutoUsers"]="Auto creation of users after identification";
		self::$trad["AGORA_ldapCreaAutoUsersInfo"]="Automatically create a user if it is missing from the Agora but present on the LDAP server: it will be assigned to areas accessible to ''all users of the Site''.<br>Otherwise, the user will not be created.";
		self::$trad["AGORA_ldapPassEncrypt"]="Passwords encrypted on the server";
		self::$trad["AGORA_ldapDisabled"]="PHP module for connection to an LDAP server is not installed";

		////	MODULE_LOG
		////
		self::$trad["LOG_headerModuleName"]="Logs";
		self::$trad["LOG_moduleDescription"]="Logs - Event Log";
		self::$trad["LOG_path"]="Path";
		self::$trad["LOG_filter"]="filter";
		self::$trad["LOG_date"]="Date / Time";
		self::$trad["LOG_spaceName"]="space";
		self::$trad["LOG_moduleName"]="module";
		self::$trad["LOG_objectType"]="Object type";
		self::$trad["LOG_action"]="Action";
		self::$trad["LOG_userName"]="User";
		self::$trad["LOG_ip"]="IP";
		self::$trad["LOG_comment"]="comment";
		self::$trad["LOG_noLogs"]="no log";
		self::$trad["LOG_filterSince"]="filtered from";
		self::$trad["LOG_search"]="search";
		self::$trad["LOG_connexion"]="connection";//action
		self::$trad["LOG_add"]="add";//action
		self::$trad["LOG_delete"]="delete";//action
		self::$trad["LOG_modif"]="edit change";//action

		////	MODULE_ESPACE
		////
		self::$trad["SPACE_headerModuleName"]="Spaces";
		self::$trad["SPACE_moduleInfo"]="The site (or main space) can be divided into several spaces";
		self::$trad["SPACE_manageSpaces"]="Manage spaces of the site";
		self::$trad["SPACE_config"]="Settings of the space";
		//Index
		self::$trad["SPACE_confirmDeleteDbl"]="Confirm the deletion ? Attention, this action cannot be undone !";
		self::$trad["SPACE_space"]="space";
		self::$trad["SPACE_spaces"]="spaces";
		self::$trad["SPACE_accessRightUndefined"]="To define !";
		self::$trad["SPACE_modules"]="Modules";
		self::$trad["SPACE_addSpace"]="Add a space";
		//Edit
		self::$trad["SPACE_usersAccess"]="Users assigned to the space";
		self::$trad["SPACE_selectModule"]="You must select a module";
		self::$trad["SPACE_spaceModules"]="Space modules";
		self::$trad["SPACE_moduleRank"]="Move to set the display order of modules";
		self::$trad["SPACE_publicSpace"]="Public space";
		self::$trad["SPACE_publicSpaceInfo"]="Gives access to people who do not have a user account : the 'guests'. It is possible to specify a password to protect access to the space. The following modules will not be accessible to guests : 'mail' and 'user' (if the public space does not have a password)";
		self::$trad["SPACE_publicSpaceNotif"]="If your public space contains sensitive data such as personal contact details (Contact module) or documents (File module): you are required to add password access to your public space, to comply with the GDPR.<hr>The General Data Protection Regulation is a regulation of the European Union constituting the reference text for the protection of personal data.";
		self::$trad["SPACE_usersInvitation"]="Users can send invitations by email";
		self::$trad["SPACE_usersInvitationInfo"]="All users can send email invitations to join the space";
		self::$trad["SPACE_allUsers"]="All the users";
		self::$trad["SPACE_user"]=" User";
		self::$trad["SPACE_userInfo"]="User of the space : <br> Normal access to the space";
		self::$trad["SPACE_admin"]="Administrator";
		self::$trad["SPACE_adminInfo"]="Administrator of the space : Write access to all elements of the space + ability to send email invitations + ability to add users";

		////	MODULE_UTILISATEUR
		////
		// Menu principal
		self::$trad["USER_headerModuleName"]="User";
		self::$trad["USER_moduleDescription"]="Users of the space";
		self::$trad["USER_option_allUsersAddGroup"]="Users can also create groups";
		//Index
		self::$trad["USER_allUsers"]="View all users";
		self::$trad["USER_allUsersInfo"]="View all users from all spaces";
		self::$trad["USER_spaceUsers"]="Users of the space";
		self::$trad["USER_deleteDefinitely"]="Delete permanently";
		self::$trad["USER_deleteFromCurSpace"]="Unassign to the current space";
		self::$trad["USER_deleteFromCurSpaceConfirm"]="Confirm the unassignment of the user to current space ?";
		self::$trad["USER_allUsersOnSpaceNotif"]="All the users are affected to this space";
		self::$trad["USER_user"]="User";
		self::$trad["USER_users"]="users";
		self::$trad["USER_addExistUser"]="Add an existing user to the space";
		self::$trad["USER_addExistUserTitle"]="Add to the space an already existing user on the site : assignment to the space";
		self::$trad["USER_addUser"]="Add User";
		self::$trad["USER_addUserSite"]="Create a user on the site: by default, assigned to any space!";
		self::$trad["USER_addUserSpace"]="Create a user into the current space";
		self::$trad["USER_sendCoords"]="Send login and password";
		self::$trad["USER_sendCoordsInfo"]="Send users an email with their login and a link to initialize their password";
		self::$trad["USER_sendCoordsInfo2"]="Send users an email with their login informations";
		self::$trad["USER_sendCoordsConfirm"]="Passwords will be renewed ! continue ?";
		self::$trad["USER_sendCoordsMail"]="Your login details to your space";
		self::$trad["USER_noUser"]="No user assigned to this space for the moment";
		self::$trad["USER_spaceList"]="Spaces of the user";
		self::$trad["USER_spaceNoAffectation"]="No space";
		self::$trad["USER_adminGeneral"]="General administrator of the site";
		self::$trad["USER_adminSpace"]="Administrator of the space";
		self::$trad["USER_userSpace"]="User of the space";
		self::$trad["USER_profilEdit"]="Modify profile";
		self::$trad["USER_myProfilEdit"]="Modify my user profile";
		// Invitation
		self::$trad["USER_sendInvitation"]="Send invitations by email";
		self::$trad["USER_sendInvitationInfo"]="Send invitations by email to your contacts to join you on the current space.<hr><img src='app/img/gSignin.png' height=15> If you have a Gmail account, you can also get your Gmail contacts to send invitations.";
		self::$trad["USER_mailInvitationObject"]="Invitation of "; // ..Jean DUPOND
		self::$trad["USER_mailInvitationFromSpace"]="invites you to join "; // Jean DUPOND "vous invite à rejoindre l'espace" Mon Espace
		self::$trad["USER_mailInvitationConfirm"]="Click here to confirm the invitation";
		self::$trad["USER_mailInvitationWait"]="Invitations not confirmed yet";
		self::$trad["USER_exired_idInvitation"]="The weblink for your invitation has expired ...";
		self::$trad["USER_invitPassword"]="Confirm your invitation";
		self::$trad["USER_invitPassword2"]="Choose your password to confirm your invitation";
		self::$trad["USER_invitationValidated"]="Your invitation has been validated !";
		self::$trad["USER_gPeopleImport"]="Get my contacts from my Gmail address";
		self::$trad["USER_importQuotaExceeded"]="You are limited to --USERS_QUOTA_REMAINING-- new user accounts, out of a total of --LIMITE_NB_USERS-- users";
		// groupes
		self::$trad["USER_spaceGroups"]="groups of users of the space";
		self::$trad["USER_spaceGroupsEdit"]="edit the groups of users of the space";
		self::$trad["USER_groupEditInfo"]="Each group can be modified by its author or the space administrator";
		self::$trad["USER_addGroup"]="Add a group";
		self::$trad["USER_userGroups"]="User groups";
		// Utilisateur_affecter
		self::$trad["USER_searchPrecision"]="Thank you for specifying a last name, a first name or an address of email";
		self::$trad["USER_userAffectConfirm"]="Confirm assignements?";
		self::$trad["USER_userSearch"]="Search users to add to the current space";
		self::$trad["USER_allUsersOnSpace"]="All the users of the site are already assigned to this space";
		self::$trad["USER_usersSpaceAffectation"]="Assign users to the space :";
		self::$trad["USER_usersSearchNoResult"]="No user found";
		// Utilisateur_edit & CO
		self::$trad["USER_langs"]="Language";
		self::$trad["USER_persoCalendarDisabled"]="Personal calendar disabled";
		self::$trad["USER_persoCalendarDisabledInfo"]="By default, the personal calendar is always visible by the user, even if the Calendar module is not enabled in the space";
		self::$trad["USER_connectionSpace"]="Space displayed after connection";
		self::$trad["USER_loginAlreadyExist"]="The login/email already exists. Please choose another";
		self::$trad["USER_mailPresentInAccount"]="A user account already exists with this email address";
		self::$trad["USER_loginAndMailDifferent"]="Both email addresses must be identical";
		self::$trad["USER_mailNotifObject"]="New account on";  // "...sur" l'Agora machintruc
		self::$trad["USER_mailNotifContent"]="Your user account has been created on";  // idem
		self::$trad["USER_mailNotifContent2"]="Connect with the following login and password";
		self::$trad["USER_mailNotifContent3"]="Thank you for archiving this email.";
		// Livecounter & Messenger & Visio
		self::$trad["USER_messengerEdit"]="Configure my instant messaging";
		self::$trad["USER_messengerEdit2"]="Configure instant messaging";
		self::$trad["USER_livecounterVisibility"]="Visibility on instant messaging and videoconferencing";
		self::$trad["USER_livecounterAllUsers"]="Display my presence when I am connected: messaging / video enabled";
		self::$trad["USER_livecounterDisabled"]="Hide my presence when I am connected: messaging / video disabled";
		self::$trad["USER_livecounterSomeUsers"]="Only certain users can see me when I'm logged in";

		////	MODULE_TABLEAU BORD
		////
		// Menu principal + options du module
		self::$trad["DASHBOARD_headerModuleName"]="News";
		self::$trad["DASHBOARD_moduleDescription"]="News, Polls and Recent elements";
		self::$trad["DASHBOARD_option_adminAddNews"]="Only the admin can add News";//OPTION!
		self::$trad["DASHBOARD_option_disablePolls"]="Disable polls";//OPTION!
		self::$trad["DASHBOARD_option_adminAddPoll"]="Only the admin can add Polls";//OPTION!
		//Index
		self::$trad["DASHBOARD_menuNews"]="News";
		self::$trad["DASHBOARD_menuPolls"]="Polls";
		self::$trad["DASHBOARD_menuElems"]="Recent & current elements";
		self::$trad["DASHBOARD_addNews"]="Add news";
		self::$trad["DASHBOARD_newsOffline"]="Archived news";
		self::$trad["DASHBOARD_noNews"]="No news for the moment";
		self::$trad["DASHBOARD_addPoll"]="Add a poll";
		self::$trad["DASHBOARD_pollsNotVoted"]="Current polls : not voted";
		self::$trad["DASHBOARD_pollsNotVotedInfo"]="Show only polls that you have not voted on yet";
		self::$trad["DASHBOARD_vote"]="Vote and see the results !";
		self::$trad["DASHBOARD_voteTooltip"]="The votes are anonymous : nobody will know your choice of vote";
		self::$trad["DASHBOARD_answerVotesNb"]="Voté --NB_VOTES-- times";
		self::$trad["DASHBOARD_pollVotesNb"]="The poll was voted --NB_VOTES-- times";
		self::$trad["DASHBOARD_pollVotedBy"]="The poll was voted by";//Bibi, boby, etc
		self::$trad["DASHBOARD_noPoll"]="No poll for the moment";
		self::$trad["DASHBOARD_plugins"]="New Elements";
		self::$trad["DASHBOARD_pluginsInfo"]="Elements created";
		self::$trad["DASHBOARD_pluginsInfo2"]="between";
		self::$trad["DASHBOARD_plugins_day"]="of today";
		self::$trad["DASHBOARD_plugins_week"]="of this week";
		self::$trad["DASHBOARD_plugins_month"]="of the month";
		self::$trad["DASHBOARD_plugins_previousConnection"]="since the last login";
		self::$trad["DASHBOARD_pluginsCurrent"]="Current element";
		self::$trad["DASHBOARD_pluginsTooltipRedir"]="View the element in is folder";
		self::$trad["DASHBOARD_pluginEmpty"]="No new elements for this period";
		// Edition d'Actualite/News
		self::$trad["DASHBOARD_topNews"]="Top news";
		self::$trad["DASHBOARD_topNewsInfo"]="News at the top of the list";
		self::$trad["DASHBOARD_offline"]="Archived news";
		self::$trad["DASHBOARD_dateOnline"]="Date online";
		self::$trad["DASHBOARD_dateOnlineInfo"]="Select a date to automatically put the news online.<br>In the meantime, the news is offline";
		self::$trad["DASHBOARD_dateOnlineNotif"]="The news is momentarily archived";
		self::$trad["DASHBOARD_dateOffline"]="Date of archiving";
		self::$trad["DASHBOARD_dateOfflineInfo"]="Select a date to archive automatically the news";
		// Edition de Sondage/Polls
		self::$trad["DASHBOARD_titleQuestion"]="Title / Question";
		self::$trad["DASHBOARD_multipleResponses"]="Several answers possible for each vote";
		self::$trad["DASHBOARD_newsDisplay"]="Show with news (left menu)";
		self::$trad["DASHBOARD_publicVote"]="Public vote: the choice of voters is  public";
		self::$trad["DASHBOARD_publicVoteInfos"]="Note that a public vote can be a barrier to participation to the survey.";
		self::$trad["DASHBOARD_dateEnd"]="End date of the poll";
		self::$trad["DASHBOARD_responseList"]="Possible answers";
		self::$trad["DASHBOARD_responseNb"]="Answer n°";
		self::$trad["DASHBOARD_addResponse"]="Add an answer";
		self::$trad["DASHBOARD_controlResponseNb"]="Please specify at least 2 possible answers";
		self::$trad["DASHBOARD_votedPollNotif"]="Attention: as soon as the poll is voted, it is no longer possible to change the title or the answers";
		self::$trad["DASHBOARD_voteNoResponse"]="Please select an answer";

		////	MODULE_AGENDA
		////
		// Menu principal
		self::$trad["CALENDAR_headerModuleName"]="Calendar";
		self::$trad["CALENDAR_moduleDescription"]="Personal and shared calendar";
		self::$trad["CALENDAR_option_adminAddRessourceCalendar"]="Only the admin can add resource calendars";
		self::$trad["CALENDAR_option_adminAddCategory"]="Only the admin can add a category of event";
		self::$trad["CALENDAR_option_createSpaceCalendar"]="Create a shared agenda";
		self::$trad["CALENDAR_option_createSpaceCalendarInfo"]="The calendar will have the same name than the space. This can be useful if the calendars of the users are disabled.";
		//Index
		self::$trad["CALENDAR_calsList"]="Available calendars";
		self::$trad["CALENDAR_displayAllCals"]="Show all calendars (for administrators)";
		self::$trad["CALENDAR_hideAllCals"]="Hide all calendars";
		self::$trad["CALENDAR_printCalendars"]="Print calendar(s)";
		self::$trad["CALENDAR_printCalendarsInfos"]="Print in landscape mode";
		self::$trad["CALENDAR_addSharedCalendar"]="Add a chared calendar";
		self::$trad["CALENDAR_addSharedCalendarInfo"]="Add a chared calendar :<br>for the reservations of a room, vehicle, videoprojector, etc";
		self::$trad["CALENDAR_exportIcal"]="Export the events (iCal)";
		self::$trad["CALENDAR_exportEvtMail"]="Export the events by email (iCal)";
		self::$trad["CALENDAR_exportEvtMailInfo"]="To integrate in a calendar IPHONE, ANDROID, OUTLOOK, GOOGLE CALENDAR...";
		self::$trad["CALENDAR_exportEvtMailList"]="List of events in .Ical format";
		self::$trad["CALENDAR_icalUrl"]="Url for reading access to the calendar (Ical)";
		self::$trad["CALENDAR_icalUrlCopy"]="Copy this address? This will allow you to read this calendar from another application";
		self::$trad["CALENDAR_importIcal"]="Import the events (iCal)";
		self::$trad["CALENDAR_importIcalState"]="State";
		self::$trad["CALENDAR_importIcalStatePresent"]="Already present";
		self::$trad["CALENDAR_importIcalStateImport"]="To import";
		self::$trad["CALENDAR_inputProposed"]="The event will be proposed to the owner of the calendar";
		self::$trad["CALENDAR_displayDay"]="Day";
		self::$trad["CALENDAR_display4Days"]="4 days";
		self::$trad["CALENDAR_displayWorkWeek"]="Working week";
		self::$trad["CALENDAR_displayWeek"]="Week";
		self::$trad["CALENDAR_displayMonth"]="Month";
		self::$trad["CALENDAR_weekNb"]="See the week n°";
		self::$trad["CALENDAR_periodNext"]="Next period";
		self::$trad["CALENDAR_periodPrevious"]="Preceding period";
		self::$trad["CALENDAR_evtAffects"]="In the calendar of";
		self::$trad["CALENDAR_evtAffectToConfirm"]="Confirmation on standby : ";
		self::$trad["CALENDAR_evtProposedFor"]="Events proposed for"; // "Videoprojecteur" / "salle de réunion" / etc.
		self::$trad["CALENDAR_evtProposedForMe"]="Events proposed for my calendar";
		self::$trad["CALENDAR_evtProposedBy"]="Proposed by";  // "Proposé par" Mr bidule truc
		self::$trad["CALENDAR_evtIntegrate"]="Integrate the event into the calendar ?";
		self::$trad["CALENDAR_evtNotIntegrate"]="Delete the proposal of the event ?";
		self::$trad["CALENDAR_deleteEvtCal"]="Delete only for this calendar ?";
		self::$trad["CALENDAR_deleteEvtCals"]="Delete for all the calendars ?";
		self::$trad["CALENDAR_deleteEvtDate"]="Delete only for this date ?";
		self::$trad["CALENDAR_evtPrivate"]="Private event";
		self::$trad["CALENDAR_evtAutor"]="Events which I created";
		self::$trad["CALENDAR_noEvt"]="No event";
		self::$trad["CALENDAR_synthese"]="Calendars synthesis";
		self::$trad["CALENDAR_calendarsPercentBusy"]="Busy calendars";
		self::$trad["CALENDAR_noCalendarDisplayed"]="No calendars displayed";
		// Evenement
		self::$trad["CALENDAR_category"]="Category";
		self::$trad["CALENDAR_importanceNormal"]="Normal importance";
		self::$trad["CALENDAR_importanceHight"]="High importance";
		self::$trad["CALENDAR_visibilityPublic"]="Normal visibility";
		self::$trad["CALENDAR_visibilityPrivate"]="Private visibility";
		self::$trad["CALENDAR_visibilityPublicHide"]="Semi-private visibility";
		self::$trad["CALENDAR_visibilityInfo"]="<u>private visibility</ u>: visible only to those whose event is accessible in writing <br><br> <u>semi-private visibility</u> : Only the time slot is displayed (without title and details) if the event is read-only";
		// Agenda/Evenement : edit
		self::$trad["CALENDAR_timeSlot"]="Time range of the ''week'' display";
		self::$trad["CALENDAR_noPeriodicity"]="Only once";
		self::$trad["CALENDAR_period_weekDay"]="Every week";
		self::$trad["CALENDAR_period_month"]="Every month";
		self::$trad["CALENDAR_period_dayOfMonth"]="of the month"; // Le 21 du mois
		self::$trad["CALENDAR_period_year"]="Every year";
		self::$trad["CALENDAR_periodDateEnd"]="End of recurrence";
		self::$trad["CALENDAR_periodException"]="Recurrence exception";
		self::$trad["CALENDAR_calendarAffectations"]="Assign to the following calendars";
		self::$trad["CALENDAR_addEvt"]="Add an event";
		self::$trad["CALENDAR_addEvtTooltip"]="Add an event";
		self::$trad["CALENDAR_addEvtTooltipBis"]="Add the event to the calendar";
		self::$trad["CALENDAR_proposeEvtTooltip"]="Propose an event to the owner of the calendar";
		self::$trad["CALENDAR_proposeEvtTooltipBis"]="Propose the event to the owner of the calendar";
		self::$trad["CALENDAR_proposeEvtTooltipBis2"]="Propose the event to the owner of the calendar : calendar accessible only for reading";
		self::$trad["CALENDAR_verifCalNb"]="Thank you for selecting a calendar";
		self::$trad["CALENDAR_noModifInfo"]="Modification forbidden because you don't have access to write in this calendar";
		self::$trad["CALENDAR_editLimit"]="You are not the author of the event: you can only manage your calendars assignments";
		self::$trad["CALENDAR_busyTimeslot"]="The slot is already occupied on this calendar :";
		self::$trad["CALENDAR_visioUrlAdd"]="Add a videoconference";
		self::$trad["CALENDAR_visioUrlCopy"]="Copy the videoconference link";
		self::$trad["CALENDAR_visioUrlDelete"]="Remove the video conference link";
		self::$trad["CALENDAR_visioUrlLaunch"]="Start the videoconference of this event";
		// Categories
		self::$trad["CALENDAR_editCategories"]="Manage event categories";
		self::$trad["CALENDAR_editCategoriesRight"]="Each category can be modified by its author or the general administrator";
		self::$trad["CALENDAR_addCategory"]="Add a category";
		self::$trad["CALENDAR_filterByCategory"]="View only events by caterory";

		////	MODULE_FICHIER
		////
		// Menu principal
		self::$trad["FILE_headerModuleName"]="File manager";
		self::$trad["FILE_moduleDescription"]="File manager";
		self::$trad["FILE_option_adminRootAddContent"]="Only the administrator can add folders and files in the root folder";
		//Index
		self::$trad["FILE_addFile"]="Add files";
		self::$trad["FILE_addFileAlert"]="Folder on the server not accessible in writing! thank you to contact the administrator";
		self::$trad["FILE_downloadSelection"]="Download Selection";
		self::$trad["FILE_nbFileVersions"]="versions of the file";//"55 versions du fichier"
		self::$trad["FILE_downloadsNb"]="File Downloaded --NB_DOWNLOAD-- times";
		self::$trad["FILE_downloadedBy"]="File Downloaded by";//"..boby, will"
		self::$trad["FILE_addFileVersion"]="add a new file version";
		self::$trad["FILE_noFile"]="No file for the moment";
		// Fichier_edit  &  Dossier_edit  &  fichier_edit_ajouter  &  Versions_fichier
		self::$trad["FILE_fileSizeLimit"]="The files should not exceed"; // ...2 Mega Octets
		self::$trad["FILE_uploadSimple"]="Simple upload";
		self::$trad["FILE_uploadMultiple"]="Multiple upload";
		self::$trad["FILE_imgReduce"]="Optimize the image";
		self::$trad["FILE_updatedName"]="The filename will be replaced by the new version";
		self::$trad["FILE_fileSizeError"]="File is too large";
		self::$trad["FILE_addMultipleFilesInfo"]="Button 'Shift' or 'Ctrl' to select multiple files";
		self::$trad["FILE_selectFile"]="Thank you to select at least a file";
		self::$trad["FILE_fileContent"]="Content";
		// Versions_fichier
		self::$trad["FILE_versionsOf"]="Versions of"; // versions de fichier.gif
		self::$trad["FILE_confirmDeleteVersion"]="Confirm the removal of this version ?";

		////	MODULE_FORUM
		////
		// Menu principal
		self::$trad["FORUM_headerModuleName"]="Forum";
		self::$trad["FORUM_moduleDescription"]="Forum";
		self::$trad["FORUM_option_adminAddSubject"]="Only the administrator can add topics";
		self::$trad["FORUM_option_allUsersAddTheme"]="Users can also add themes";
		// TRI
		self::$trad["SORT_dateLastMessage"]="Last message";
		//Index & Sujet
		self::$trad["FORUM_subject"]="Topic";
		self::$trad["FORUM_subjects"]="Topics";
		self::$trad["FORUM_message"]="Message";
		self::$trad["FORUM_messages"]="Messages";
		self::$trad["FORUM_lastSubject"]="Last topic from";
		self::$trad["FORUM_lastMessage"]="Last message from";
		self::$trad["FORUM_noSubject"]="No subject for the moment";
		self::$trad["FORUM_noMessage"]="No message for the moment";
		self::$trad["FORUM_subjectBy"]="Subjet by";
		self::$trad["FORUM_addSubject"]="New topic";
		self::$trad["FORUM_displaySubject"]="View topic";
		self::$trad["FORUM_addMessage"]="Answer";
		self::$trad["FORUM_quoteMessage"]="Answer and quote this message";
		self::$trad["FORUM_notifyLastPost"]="Notify by email";
		self::$trad["FORUM_notifyLastPostInfo"]="Send me a notification by email to each new message";
		// Sujet_edit  &  Message_edit
		self::$trad["FORUM_accessRightInfos"]="Attention: the reading access does not allow to participate in the discussion. So prefer limited write access. Write access should be reserved for moderators.";
		self::$trad["FORUM_themeSpaceAccessInfo"]="The topic is available in the spaces";
		// Themes
		self::$trad["FORUM_subjectTheme"]="Theme";
		self::$trad["FORUM_subjectThemes"]="Themes";
		self::$trad["FORUM_forumRoot"]="Forum Home";
		self::$trad["FORUM_forumRootResp"]="Home";
		self::$trad["FORUM_noTheme"]="Without theme";
		self::$trad["FORUM_editThemes"]="Manage themes";
		self::$trad["FORUM_editThemesInfo"]="Each theme can be modified by its author or the general administrator";
		self::$trad["FORUM_addTheme"]="Add a theme";

		////	MODULE_TACHE
		////
		// Menu principal
		self::$trad["TASK_headerModuleName"]="Tasks";
		self::$trad["TASK_moduleDescription"]="Tasks";
		self::$trad["TASK_option_adminRootAddContent"]="Only the administrator can add folders and tasks in the root folder";
		// TRI
		self::$trad["SORT_priority"]="Priority";
		self::$trad["SORT_advancement"]="Progress";
		self::$trad["SORT_dateBegin"]="Begin date";
		self::$trad["SORT_dateEnd"]="End date";
		//Index
		self::$trad["TASK_addTask"]="Add a task";
		self::$trad["TASK_noTask"]="No task for the moment";
		self::$trad["TASK_advancement"]="Progress";
		self::$trad["TASK_advancementAverage"]="Average progress";
		self::$trad["TASK_priority"]="Priority";
		self::$trad["TASK_priority1"]="Low";
		self::$trad["TASK_priority2"]="Medium";
		self::$trad["TASK_priority3"]="High";
		self::$trad["TASK_priority4"]="Critical";
		self::$trad["TASK_responsiblePersons"]="Leaders";
		self::$trad["TASK_advancementLate"]="Progress delayed";

		////	MODULE_CONTACT
		////
		// Menu principal
		self::$trad["CONTACT_headerModuleName"]="Contacts";
		self::$trad["CONTACT_moduleDescription"]="Directory of contacts";
		self::$trad["CONTACT_option_adminRootAddContent"]="Only the administrator can add folders and contacts in the root folder";
		//Index
		self::$trad["CONTACT_addContact"]="Add a contact";
		self::$trad["CONTACT_noContact"]="No contact for the moment";
		self::$trad["CONTACT_createUser"]="Create a user in this space";
		self::$trad["CONTACT_createUserInfo"]="Create a user in this space from this contact ?";
		self::$trad["CONTACT_createUserConfirm"]="The user was successfully created";

		////	MODULE_LIEN
		////
		// Menu principal
		self::$trad["LINK_headerModuleName"]="Bookmarks";
		self::$trad["LINK_moduleDescription"]="Bookmarks";
		self::$trad["LINK_option_adminRootAddContent"]="Only the administrator can add folders and bookmarks to the root folder";
		//Index
		self::$trad["LINK_addLink"]="Add a bookmark";
		self::$trad["LINK_noLink"]="No bookmark at the moment";
		// lien_edit & dossier_edit
		self::$trad["LINK_adress"]="bookmark";

		////	MODULE_MAIL
		////
		//  Menu principal
		self::$trad["MAIL_headerModuleName"]="Emails";
		self::$trad["MAIL_moduleDescription"]="Send emails in a click!";
		//Index
		self::$trad["MAIL_specifyMail"]="Thank you for entering an address email";
		self::$trad["MAIL_subject"]="Email subject";
		self::$trad["MAIL_message"]="Email message";
		self::$trad["MAIL_attachedFile"]="Attached file";
		// Historique Email
		self::$trad["MAIL_mailHistory"]="History of the emails sent";
		self::$trad["MAIL_mailHistoryEmpty"]="No email";
		self::$trad["MAIL_recipients"]="Recipients";
	}

	/*
	 * Jours Fériés de l'année
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
			$dateList[$date]="Easter Monday";
		}

		//Fêtes fixes
		$dateList[$year."-01-01"]="New Year's Day";
		$dateList[$year."-12-25"]="Christmas";

		//Retourne le résultat
		return $dateList;
	}
}