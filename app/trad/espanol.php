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
		setlocale(LC_TIME, "es_ES.utf8", "es_ES.UTF-8", "es_ES", "es", "spanish");

		////	TRADUCTIONS
		self::$trad=array(
			////	Langue courante / Header http / Editeurs Tinymce / Documention pdf
			"CURLANG"=>"es",
			"DATELANG"=>"es_ES",
			"EDITORLANG"=>"es",

			////	Divers
			"mainMenu"=>"Menú principal",
			"menuOptions"=>"Menú de opciones disponibles",
			"fillFieldsForm"=>"Por favor, rellene los campos del formulario",
			"requiredFields"=>"Campo obligatorio",
			"inaccessibleElem"=>"Elemento inaccesible",
			"warning"=>"Atención",
			"elemEditedByAnotherUser"=>"El elemento está siendo editado por",//"..bob"
			"yes"=>"sí",
			"no"=>"no",
			"none"=>"no",
			"or"=>"o",
			"and"=>"y",
			"goToPage"=>"Ir a la página",
			"alphabetFilter"=>"Filtro alfabético",
			"displayAll"=>"Mostrar todo",
			"show"=>"Mostrar",
			"hide"=>"Ocultar",
			"byDefault"=>"Por defecto",
			"changeOrder"=>"Mover a establecer el orden de presentación de los módulos",
			"mapLocalize"=>"Localizar en el mapa",
			"mapLocalizationFailure"=>"Falla de localización de la siguiente dirección",
			"mapLocalizationFailure2"=>"Verifique que exista la dirección en www.google.com/maps o www.openstreetmap.org",
			"sendMail"=>"enviar un email",
			"mailInvalid"=>"El correo electrónico no es válida",
			"element"=>"elemento",
			"elements"=>"elementos",
			"folder"=>"carpeta",
			"folders"=>"carpetas",
			"close"=>"Cerrar",
			"confirmCloseForm"=>"¿ Cerrar el formulario sin guardar ?",
			"modifRecorded"=>"Los cambios fueron registrados",
			"confirm"=>"¿ Confirmar ?",
			"comment"=>"Comentario",
			"commentAdd"=>"Añadir un comentario",
			"optional"=>"(opcional)",
			"objNew"=>"Elemento creado recientemente",
			"personalAccess"=>"Acceso personal",
			"copyUrl"=>"Copia la dirección web del elemento (URL)",
			"copyUrlTooltip"=>"Permite el acceso externo al elemento : desde una noticia, un correo electrónico, un mensaje de un foro, un blog, etc.",
			"copyUrlConfirmed"=>"La dirección web se ha copiado correctamente.",
			"cancel"=>"Cancelar",

			////	images
			"picture"=>"Foto",
			"pictureProfil"=>"Foto de perfil",
			"wallpaper"=>"papel tapiz",
			"keepImg"=>"mantener la imagen",
			"changeImg"=>"cambiar la imagen",
			"pixels"=>"píxeles",

			////	Connexion
			"specifyLoginPassword"=>"Gracias a especificar un nombre de usuario y contraseña",
			"specifyLogin"=>"Gracias especificar un email/identificador (sin espacio)",
			"mailLloginNotif"=>"Se recomienda utilizar un email como identificador de sesión",
			"mailLlogin"=>"Email / Identificador de conexión",
			"connect"=>"Conexión",
			"connectAuto"=>"Mantente conectado",
			"connectAutoTooltip"=>"Recordar mis datos de inicio de sesión para la conexión automática",
			"gIdentityUserUnknown"=>"no está registrado en el espacio",
			"connectSpaceSwitch"=>"Conectarse a otro espacio",
			"connectSpaceSwitchConfirm"=>"¿ Está seguro de que desea abandonar este espacio para conectarse a otro espacio ?",
			"guestAccess"=>"Iniciar sesión como invitado",
			"guestAccessTooltip"=>"Iniciar sesión en este espacio como invitado",
			"publicSpacePasswordError"=>"Contraseña incorrecta",
			"disconnectSpace"=>"Cerrar sesión",
			"disconnectSpaceConfirm"=>"¿ Confirmar desconexión del espacio ?",

			////	Password : connexion d'user / edition d'user / reset du password
			"password"=>"Contraseña",
			"passwordModify"=>"Cambiar la contraseña",
			"passwordToModify"=>"Contraseña temporal (a cambiar al iniciar sesión)",//Mail d'envoi d'invitation
			"passwordToModify2"=>"Contraseña (cambiar si es necesario)",//Mail de création de compte
			"passwordVerif"=>"Confirmar contraseña",
			"passwordTooltip"=>"Dejar en blanco si desea mantener su contraseña",
			"passwordInvalid"=>"Su contraseña debe contener números, letras y al menos 6 caracteres",
			"passwordConfirmError"=>"Your confirmation password is not valid",
			"specifyPassword"=>"Gracias especificar una contraseña",
			"resetPassword"=>"¿Información de inicio de sesión olvidada ?",
			"resetPassword2"=>"Introduzca su dirección de correo electrónico para recibir sus datos de acceso",
			"resetPasswordNotif"=>"Se acaba de enviar un correo electrónico a su dirección para restablecer su contraseña. Si no ha recibido un correo electrónico, verifique que la dirección especificada sea correcta o que el correo electrónico no esté en su correo no deseado.",
			"resetPasswordMailTitle"=>"Restablecer su contraseña",
			"resetPasswordMailPassword"=>"Para iniciar sesión en su Omnispace y restablecer su contraseña",
			"resetPasswordMailPassword2"=>"haga clic aquí",
			"resetPasswordMailLoginRemind"=>"Recordatorio de su login",
			"resetPasswordIdExpired"=>"El enlace web para regenerar la contraseña ha caducado .. gracias por reiniciar la procedura",

			////	Type d'affichage
			"displayMode"=>"Visualización",
			"displayMode_line"=>"líneas",
			"displayMode_block"=>"Bloques",

			////	Sélectionner / Déselectionner tous les éléments
			"select"=>"Seleccionar",
			"selectUnselect"=>"Seleccionar / Deseleccionar",
			"selectAll"=>"Seleccionar todo",
			"selectNone"=>"Deseleccionar todo",
			"selectSwitch"=>"Invertir selección",
			"deleteElems"=>"Eliminar elementos",
			"changeFolder"=>"Mover a otro carpeta",
			"showOnMap"=>"Mostrar en el mapa",
			"showOnMapTooltip"=>"Ver en un mapa los contactos con dirección, código postal, ciudad",
			"notifSelectUser"=>"Gracias por seleccionar al menos un usuario",
			"notifSelectUsers"=>"Gracias por seleccionar por lo menos dos usuarios",
			"selectSpace"=>"Gracias por elegir al menos un espacio",
			"visibleAllSpaces"=>"Visible en todos los espacios",/*cf. Categories, themes, etc*/
			"visibleOnSpace"=>"Disponible en el espacio",/*"..Mon espace"*/

			////	Temps ("de 11h à 12h", "le 25-01-2007 à 10h30", etc.)
			"from"=>"de",
			"at"=>"a",
			"the"=>"el",
			"begin"=>"Inicio",
			"end"=>"Fin",
			"beginEnd"=>"Inicio / Fin",
			"days"=>"dias",
			"day_1"=>"lunes",
			"day_2"=>"Martes",
			"day_3"=>"miércoles",
			"day_4"=>"Jueves",
			"day_5"=>"Viernes",
			"day_6"=>"Sábado",
			"day_7"=>"Domingo",
			"month_1"=>"Enero",
			"month_2"=>"Febrero",
			"month_3"=>"marzo",
			"month_4"=>"Abril",
			"month_5"=>"Mayo",
			"month_6"=>"Junio",
			"month_7"=>"julio",
			"month_8"=>"agosto",
			"month_9"=>"Septiembre",
			"month_10"=>"octubre",
			"month_11"=>"Noviembre",
			"month_12"=>"Diciembre",
			"today"=>"hoy",
			"beginEndError"=>"La fecha de inicio debe ser anterior a la fecha de finalización.",
			"dateFormatError"=>"La fecha debe estar en el formato dd/mm/AAAA",
			"timeFormatError"=>"La hora debe estar en el formato HH:mm",

			////	Menus d'édition des objets et editeur tinyMce
			"title"=>"Título",
			"name"=>"Nombre",
			"description"=>"Descripción",
			"specifyName"=>"Gracias por especificar un nombre",
			"editorDraft"=>"Recuperar mi texto",
			"editorDraftConfirm"=>"Recuperar el último texto especificado",
			"editorFileInsert"=>"Añadir imagen o video",
			"editorFileInsertNotif"=>"Seleccione una imagen en formato Jpeg, Png, Gif o Svg",
		
			////	Validation des formulaires
			"add"=>"Añadir",
			"modify"=>"Editar",
			"record"=>"Registrar",
			"modifyAndAccesRight"=>"Editar + derechos de acceso",
			"validate"=>"Validar",
			"send"=>"Enviar",
			"sendTo"=>"Enviar a",

			////	Tri d'affichage. Tous les éléments (dossier, tâche, lien, etc...) ont par défaut une date, un auteur & une description
			"sortBy"=>"Ordenado por",
			"sortBy2"=>"Ordenar por",
			"SORT_dateCrea"=>"fecha de creación",
			"SORT_dateModif"=>"fecha de modification",
			"SORT_title"=>"título",
			"SORT_description"=>"descripción",
			"SORT__idUser"=>"autor",
			"SORT_extension"=>"tipo de archivo",
			"SORT_octetSize"=>"tamaño",
			"SORT_downloadsNb"=>"downloads",
			"SORT_civility"=>"civilidad",
			"SORT_name"=>"apellido",
			"SORT_firstName"=>"nombre",
			"SORT_adress"=>"dirección",
			"SORT_postalCode"=>"código postal",
			"SORT_city"=>"ciudad",
			"SORT_country"=>"país",
			"SORT_function"=>"función",
			"SORT_companyOrganization"=>"compañía / organización",
			"SORT_lastConnection"=>"último acceso",
			"tri_ascendant"=>"Ascendente",
			"tri_descendant"=>"Descendente",
			
			////	Options de suppression
			"confirmDelete"=>"¿ Confirmar la eliminación permanente del elemento ?",
			"confirmDeleteDbl"=>"¿ Esta acción es definitiva ¿confirmar de todos modos ?",
			"confirmDeleteSelect"=>"¿ Desea eliminar estos elementos permanentemente ?",
			"confirmDeleteSelectNb"=>"elementos seleccionados",//"55 éléments sélectionnés"
			"confirmDeleteFolderAccess"=>"Advertencia : algunos sub-carpetas no son accessible : serán tambien eliminados !",
			"notifyBigFolderDelete"=>"Eliminar --NB_FOLDERS-- archivos puede ser un poco largo, espere unos momentos antes del final del proceso",
			"delete"=>"Eliminar",
			"notDeletedElements"=>"Algunos elementos no se han eliminado porque no tienes los derechos de acceso necesarios",
			
			////	Visibilité d'un Objet : auteur et droits d'accès
			"autor"=>"Autor",
			"postBy"=>"publicado por",
			"guest"=>"invitado",
			"creation"=>"Creación",
			"modification"=>"Modificación",
			"createBy"=>"Creado por",
			"modifBy"=>"Modificado por",
			"objHistory"=>"histórico del elemento",
			"all"=>"todos",
			"all2"=>"todas",
			"deletedUser"=>"cuenta de usuario eliminada",
			"folderContent"=>"contenido",
			"accessRead"=>"lectura",
			"accessReadTooltip"=>"Acceso de lectura",
			"accessWriteLimit"=>"escritura limitada",
			"accessWriteLimitTooltip"=>"Acceso de escritura limitada: posibilidad de añadir -OBJCONTENT- en el --OBJLABEL--,<br> pero cada usuario solo puede modificar/borrar los -OBJCONTENT- que ha creado.",
			"accessWrite"=>"escritura",
			"accessWriteTooltip"=>"Acceso en escritura",
			"accessWriteTooltipContainer"=>"Acceso en escritura : possibilidad de añadir, modificar o suprimir todos los -OBJCONTENT- del --OBJLABEL--",
			"accessAutorPrivilege"=>"Solo el autor y los administradores pueden cambiar los permisos de acceso o eliminar el --OBJLABEL--",
			"accessRightsInherited"=>"Derechos de acceso heredados del --OBJLABEL--",
			"categoryNotifSpaceAccess"=>"n'est accessible que sur l'espace",//Ex: "Thème bidule -n'est accessible que sur l'espace- Machin"
			"categoryNotifChangeOrder"=>"El orden de visualización ha sido cambiado.",

			////	Libellé des objets
			"OBJECTcontainer"=>"contenedor",
			"OBJECTelement"=>"elemento",
			"OBJECTfolder"=>"carpeta",
			"OBJECTdashboardNews"=>"novedade",
			"OBJECTdashboardPoll"=>"encuesta",
			"OBJECTfile"=>"archivo",
			"OBJECTfileFolder"=>"carpeta",
			"OBJECTcalendar"=>"calendario",
			"OBJECTcalendarEvent"=>"evento",
			"OBJECTforumSubject"=>"tema",
			"OBJECTforumMessage"=>"mensaje",
			"OBJECTcontact"=>"contacto",
			"OBJECTcontactFolder"=>"carpeta",
			"OBJECTlink"=>"favorito",
			"OBJECTlinkFolder"=>"carpeta",
			"OBJECTtask"=>"tarea",
			"OBJECTtaskFolder"=>"carpeta",
			"OBJECTuser"=>"usuario",

			////	Envoi d'un email (nouvel utilisateur, notification de création d'objet, etc...)
			"MAIL_sendOk"=>"¡ E-Mail ha sido enviado!",						//ne pas modifier la cle de la trad ! (cf. "Tool::sendMail()")
			"MAIL_sendNotOk"=>"El correo electrónico no se pudo enviar..",	//Idem
			"MAIL_recipients"=>"destinatarios",								//Idem
			"MAIL_attachedFileError"=>"El archivo no se agregó al correo electrónico porque es demasiado grande",//Idem
			"MAIL_hello"=>"Hola",
			"MAIL_hideRecipients"=>"Ocultar destinatarios",
			"MAIL_hideRecipientsTooltip"=>"Poner todos los destinatarios en copia oculta. Tenga en cuenta que con esta opción su correo electrónico puede llegar en spam en algunas mensajerías",
			"MAIL_addReplyTo"=>"pon mi correo en respuesta",
			"MAIL_addReplyToTooltip"=>"Añadir mi correo electrónico en el campo ''Responder a''. Tenga en cuenta que con esta opción su correo electrónico puede llegar en spam en algunas mensajerías",
			"MAIL_noFooter"=>"No firme el mensaje",
			"MAIL_noFooterTooltip"=>"No firme el final del mensaje con el nombre del remitentey un enlace al espacio",
			"MAIL_receptionNotif"=>"Confirmación de entrega",
			"MAIL_receptionNotifTooltip"=>"Advertencia! algunos clientes de correo electrónico no soportan el recibo de entrega",
			"MAIL_specificMails"=>"Añadir direcciones de correo electrónico",
			"MAIL_specificMailsTooltip"=>"Añadir direcciones de correo electrónico no enumeradas en el espacio",
			"MAIL_fileMaxSize"=>"Todos sus archivos adjuntos no deben exceder los 15 MB, algunos servicios de mensajería pueden rechazar correos electrónicos más allá de este límite. ¿ Enviar de todos modos ?",
			"MAIL_sendButton"=>"Enviar correo electrónico",
			"MAIL_sendBy"=>"Enviado por",//"Envoyé par" M. Trucmuche
			"MAIL_sendNotif"=>"El correo electrónico de notificación ha sido enviado !",
			"MAIL_fromTheSpace"=>"desde el espacio",//"depuis l'espace Bidule"
			"MAIL_elemCreatedBy"=>"--OBJLABEL-- creado por",//boby
			"MAIL_elemModifiedBy"=>"--OBJLABEL-- modificado por",//boby
			"MAIL_elemAccessLink"=>"Haga clic aquí para acceder al elemento en el espacio",

			////	Dossier & fichier
			"gigaOctet"=>"GB",
			"megaOctet"=>"MB",
			"kiloOctet"=>"KB",
			"rootFolder"=>"Carpeta raíz",
			"rootFolderTooltip"=>"Abra la configuración del espacio para cambiar los derechos de acceso a la carpeta raíz",
			"addFolder"=>"añadir un directorio",
			"download"=>"Descargar archivos",
			"downloadFolder"=>"Descargar la carpeta",
			"diskSpaceUsed"=>"Espacio utilizado",
			"diskSpaceUsedModFile"=>"Espacio utilizado para los Archivos",
			"downloadAlert"=>"Su archivo es demasiado grande para descargarlo durante el día (--ARCHIVE_SIZE--). Reinicie la descarga después de las",//"19h"
			"downloadBackToApp"=>"Volver a la aplicación",
			
			////	Infos sur une personne
			"civility"=>"Civilidad",
			"name"=>"Apellido",
			"firstName"=>"Nombre",
			"adress"=>"Dirección",
			"postalCode"=>"Código postal",
			"city"=>"Ciudad",
			"country"=>"País",
			"telephone"=>"Teléfono",
			"telmobile"=>"teléfono móvil",
			"mail"=>"Email",
			"function"=>"Función",
			"companyOrganization"=>"compañía / organización",
			"lastConnection"=>"Última conexión",
			"lastConnection2"=>"Conectado el",
			"lastConnectionEmpty"=>"No está conectado",
			"displayProfil"=>"Ver perfil",
			
			////	Captcha
			"captcha"=>"Copiar los 5 caracteres",
			"captchaTooltip"=>"Por favor, escriba los 5 caracteres para su identificación",
			"captchaError"=>"La identificación visual no es valida",
			
			////	Rechercher
			"searchSpecifyText"=>"Especifique al menos 3 caracteres (alfanuméricos y sin caracteres especiales)",
			"search"=>"Buscar",
			"searchDateCrea"=>"Fecha de creación",
			"searchDateCreaDay"=>"menos de un día",
			"searchDateCreaWeek"=>"menos de una semana",
			"searchDateCreaMonth"=>"menos de un mes",
			"searchDateCreaYear"=>"menos de un año",
			"searchOnSpace"=>"Buscar en el espacio",
			"advancedSearch"=>"Búsqueda avanzada",
			"advancedSearchAnyWord"=>"cualquier palabra",
			"advancedSearchAllWords"=>"todas las palabras",
			"advancedSearchExactPhrase"=>"frase exacta",
			"keywords"=>"Palabras clave",
			"listModules"=>"Módulos",
			"listFields"=>"Campos",
			"listFieldsElems"=>"Elementos involucrados",
			"noResults"=>"No hay resultados",
			
			////	Inscription d'utilisateur
			"userInscription"=>"registrarme al espacio",
			"userInscriptionTooltip"=>"crear una nueva cuenta de usuario (validado por un administrador)",
			"userInscriptionSpace"=>"Registrarme al espacio",
			"userInscriptionRecorded"=>"Su registro será validado tan pronto como sea posible por el administrador del espacio",
			"userInscriptionEmailSubject"=>"Nuevo registro en el espacio",//"Mon espace"
			"userInscriptionEmailMessage"=>"<i>--NEW_USER_LABEL--</i> ha solicitado un nuevo registro para el espacio <i>--SPACE_NAME--</i> : <br><br><i>--NEW_USER_MESSAGE--<i> <br><br>Recuerde validar o invalidar este registro durante su próxima conexión.",
			"userInscriptionEdit"=>"Permitir a los visitantes que se registren en el espacio",
			"userInscriptionEditTooltip"=>"El registro se encuentra en la página de inicio. Debe ser validado por el administrador del espacio.",
			"userInscriptionNotif"=>"Notificar por correo electrónico en cada registro",
			"userInscriptionNotifTooltip"=>"Envíe una notificación por correo electrónico a los administradores del espacio, después de cada registro",
			"userInscriptionPulsate"=>"Registros",
			"userInscriptionValidate"=>"Registros de usuarios",
			"userInscriptionValidateTooltip"=>"Validar registros de usuarios al espacio",
			"userInscriptionSelectValidate"=>"Validar registros",
			"userInscriptionSelectInvalidate"=>"Invalidar registros",
			"userInscriptionInvalidateMail"=>"Su cuenta no ha sido validado en",
			
			////	Importer ou Exporter : Contact OU Utilisateurs
			"importExport_user"=>"Importar / Exportar usuarios",
			"import_user"=>"Importar usuarios en el espacio actual",
			"export_user"=>"Exportar usuarios del espacio",
			"importExport_contact"=>"Importar / Exportar contactos",
			"import_contact"=>"Importar contactos en la carpeta actual",
			"export_contact"=>"Exportar contactos de la carpeta actual",
			"exportFormat"=>"formato",
			"specifyFile"=>"or favor, especifique un archivo",
			"fileExtension"=>"El tipo del archivo no es válido. Debe ser de tipo",
			"importContactRootFolder"=>"Los contactos se asignarán por defecto a &quot;todos los usuarios del espacio&quot;",//"Mon espace"
			"importInfo"=>"Seleccione los campos (Agora) de destino con las listas desplegables de cada columna.",
			"importNotif1"=>"Por favor, seleccione la columna de nombre en las listas desplegables",
			"importNotif2"=>"Por favor, seleccione al menos un contacto para importar",
			"importNotif3"=>"El campo Agora ya ha sido seleccionado en otra columna (cada campo Agora se puede seleccionar sólo una vez)",
			
			////	Messages d'erreur / Notifications
			"NOTIF_identification"=>"Nombre de usuario o contraseña no válida",
			"NOTIF_presentIp"=>"Esta cuenta de usuario se está utilizando actualmente desde otra computadora, con otra dirección IP",
			"NOTIF_noAccessNoSpaceAffected"=>"Su cuenta de usuario se ha identificado correctamente, pero actualmente no está asignado a ningún espacio. Por favor contacte al administrador",
			"NOTIF_noAccess"=>"No estas logueado",
			"NOTIF_fileOrFolderAccess"=>"El archivo o la carpeta no está disponible",
			"NOTIF_diskSpace"=>"El espacio para almacenar sus archivos no es suficiente, no se puede añadir archivos",
			"NOTIF_fileVersionForbidden"=>"Tipo de archivo no permitido",
			"NOTIF_fileVersion"=>"Tipo de archivo diferente del original",
			"NOTIF_folderMove"=>"No se puede mover la carpeta dentro de sí mismo..!",
			"NOTIF_duplicateName"=>"Un elemento con el mismo nombre ya existe",
			"NOTIF_fileName"=>"Un archivo con el mismo nombre ya existe (no ha sido reemplazado)",
			"NOTIF_chmodDATAS"=>"El directorio ''DATAS'' no es accesible por escrito. Usted necesita dar un acceso de lectura y escritura para el propietario y el grupo (''chmod 775'').",
			"NOTIF_usersNb"=>"No se puede añadir un nuevo usuario : se limita a ", // "...limité à" 10
			
			////	Header / Footer
			"HEADER_displaySpace"=>"espacios de trabajo",
			"HEADER_displayAdmin"=>"Visualización de Administrador",
			"HEADER_displayAdminInfo"=>"Esta opción también le permite mostrar elementos del espacio que no están asignados a usted (carpetas, calendarios, etc)",
			"HEADER_displayAdminEnabled"=>"Visualización de Administrador activada",
			"HEADER_searchElem"=>"Buscar en el espacio",
			"HEADER_documentation"=>"Documentación",
			"HEADER_shortcuts"=>"Acceso directo",
			"FOOTER_pageGenerated"=>"página generada en",

			////	Messenger / Visio
			"MESSENGER_headerModuleName"=>"Mensajes",
			"MESSENGER_moduleDescription"=>"Mensajería instantánea: Chatea en vivo o inicia una videoconferencia con las personas conectadas al espacio",
			"MESSENGER_messengerTitle"=>"Mensajería instantánea : haga clic en el nombre de una persona para chatear o iniciar una videoconferencia",
			"MESSENGER_messengerMultiUsers"=>"Chatear con otros seleccionando mis interlocutores en el panel derecho",
			"MESSENGER_connected"=>"Conectado",
			"MESSENGER_nobody"=>"Actualmente eres la única persona conectada al espacio.",
			"MESSENGER_messageFrom"=>"Mensaje de",
			"MESSENGER_messageTo"=>"enviado a",
			"MESSENGER_chatWith"=>"Chatear con",
			"MESSENGER_addMessageToSelection"=>"Mi mensaje (personas seleccionadas)",
			"MESSENGER_addMessageTo"=>"Mi mensaje a",
			"MESSENGER_addMessageNotif"=>"Por favor, especifique un mensaje",
			"MESSENGER_visioProposeTo"=>"Enviar  una videollamada a",//..boby
			"MESSENGER_visioProposeToSelection"=>"Enviar una videollamada a las personas seleccionadas",
			"MESSENGER_visioProposeToUsers"=>"Haga clic aquí para iniciar la videollamada con",//"..Will & Boby"
			
			////	Lancer une Visio
			"VISIO_urlAdd"=>"Añadir una videoconferencia",
			"VISIO_urlCopy"=>"Copia el enlace de la videoconferencia",
			"VISIO_urlDelete"=>"Eliminar el enlace de la videoconferencia",
			"VISIO_urlMail"=>"Agregue un enlace al final del texto para comenzar una nueva videoconferencia",
			"VISIO_launch"=>"Iniciar la videoconferencias",
			"VISIO_launchJitsi"=>"Iniciar videoconferencias <br>con la aplicación Jitsi",
			"VISIO_launchFromEvent"=>"Iniciar la videoconferencia de este evento",
			"VISIO_launchTooltip"=>"Recuerde permitir el acceso a su cámara web y micrófono",
			"VISIO_launchTooltip2"=>"Haga clic aquí si tiene problemas para iniciar la videoconferencia",
			"VISIO_launchServerTooltip"=>"Elija el servidor secundario si el servidor principal no funciona correctamente.<br>Tus contactos deberán seleccionar el mismo servidor de video.",
			"VISIO_launchServerMain"=>"Servidor principal",
			"VISIO_launchServerAlt"=>"Servidor secundario",

			////	VueObjEditMenuSubmit.php
			"EDIT_notifNoSelection"=>"Debe seleccionar al menos una persona o un espacio",
			"EDIT_notifNoPersoAccess"=>"¿ Usted no se ha asignado al elemento. validar todos lo mismo ?",
			"EDIT_parentFolderAccessError"=>"Verifique los derechos de acceso de la carpeta principal <br><i>--FOLDER_NAME--</i><br><br> También debe haber un derecho de acceso para <br><i>--SPACE_LABEL--</ i> &nbsp;>&nbsp; <i>--TARGET_LABEL--</i><br><br> ¡De lo contrario no se podrá acceder a este archivo!",
			"EDIT_accessRight"=>"Derechos de acceso",
			"EDIT_accessRightContent"=>"Derechos de acceso al contenido",
			"EDIT_spaceNoModule"=>"El módulo actual aún no se ha añadido a este espacio",
			"EDIT_allUsers"=>"Todos los usuarios",
			"EDIT_allUsersAndGuests"=>"Todos los usuarios y invitados",
			"EDIT_allUsersTooltip"=>"Todos los usuarios del espacio <i>--SPACENAME--</i>",
			"EDIT_allUsersAndGuestsTooltip"=>"Todos los usuarios del espacio <i>--SPACENAME--</i>, y los invitados pero con acceso solo de lectura (invitados: personas que no tienen una cuenta de usuario)",
			"EDIT_adminSpace"=>"Administrador del espacio:<br>acceso de escritura a todos los elementos del espacio",
			"EDIT_showAllUsers"=>"Mostrar todos los usuarios",
			"EDIT_showAllUsersAndSpaces"=>"Mostrar todos los usuarios y espacios",
			"EDIT_notifMail"=>"Notificar",
			"EDIT_notifMail2"=>"Enviar una notificación de creación/cambio por email",
			"EDIT_notifMailTooltip"=>"La notificación se enviará a las personas asignadas al elemento (--OBJLABEL--)",
			"EDIT_notifMailTooltipCal"=>"<hr>Si asigna el evento a calendarios personales, la notificación solo se enviará a los propietarios de estos calendarios (acceso de escritura).",
			"EDIT_notifMailAddFiles"=>"Adjuntar archivos a la notificación",
			"EDIT_notifMailSelect"=>"Seleccionar los destinatarios de las notificaciones",
			"EDIT_accessRightSubFolders"=>"Dar igualdad de derechos a todos los sub-carpetas",
			"EDIT_accessRightSubFoldersTooltip"=>"Extender los derechos de acceso, a los sub-carpetas que se pueden editar",
			"EDIT_shortcut"=>"Acceso directo",
			"EDIT_shortcutInfo"=>"Mostrar un acceso directo en el menú principal",
			"EDIT_attachedFile"=>"Adjuntos archivos",
			"EDIT_attachedFileAdd"=>"Añadir archivos",
			"EDIT_attachedFileInsert"=>"Insertar en texto",
			"EDIT_attachedFileInsertTooltip"=>"Insertar imagen/video en el texto del editor (formato jpg, png o mp4)",
			"EDIT_guestName"=>"Su nombre / apodo",
			"EDIT_guestNameNotif"=>"Por favor, especifique un nombre / apodo",
			"EDIT_guestMail"=>"Su email",
			"EDIT_guestMailTooltip"=>"Por favor especifique su correo electrónico para la validación de su propuesta",
			"EDIT_guestElementRegistered"=>"Gracias por su propuesta : será examinada lo antes posible antes de la validación",
			
			////	Formulaire d'installation
			"INSTALL_dbConnect"=>"Conexión a la base de datos",
			"INSTALL_dbHost"=>"Nombre del servidor host (hostname)",
			"INSTALL_dbName"=>"Nombre de la base de datos",
			"INSTALL_dbLogin"=>"Nombre de Usuario",
			"INSTALL_adminAgora"=>"Administrador del Ágora",
			"INSTALL_errorDbNameFormat"=>"Advertencia: el nombre de la base de datos debe contener solo caracteres alfanuméricos y guiones o guiones bajos",
			"INSTALL_errorDbConnection"=>"No identificación con la base de datos MariaDB/MySQL",
			"INSTALL_errorDbExist"=>"Aplicación ya instalada: <a href='index.php'>haga clic aquí para acceder</a><br><br>Para reiniciar la instalación, recuerde eliminar la base de datos",
			"INSTALL_errorDbNoSqlFile"=>"No se puede acceder al archivo de instalación db.sql o se eliminó porque la instalación ya se realizó",
			"INSTALL_PhpOldVersion"=>"Agora-Project --CURRENT_VERSION-- requiere una versión más reciente de PHP",
			"INSTALL_confirmInstall"=>"¿ Confirmar instalación ?",
			"INSTALL_installOk"=>"Agora-Project ha sido instalado !",
			// Premiers enregistrements en DB
			"INSTALL_agoraDescription"=>"Espacio para el intercambio y el trabajo colaborativo",
			"INSTALL_dataDashboardNews"=>"<h3>¡Bienvenido a tu nuevo espacio para compartir!</h3>
											<h4><img src='app/img/file/iconSmall.png'> Comparta sus archivos ahora en el administrador de archivos</h4>
											<h4><img src='app/img/calendar/iconSmall.png'> Comparta sus calendarios comunes o su calendario personal</h4>
											<h4><img src='app/img/dashboard/iconSmall.png'> Amplíe el suministro de noticias de su comunidad</h4>
											<h4><img src='app/img/messenger.png'> Comunicarse a través del foro, mensajería instantánea o videoconferencias</h4>
											<h4><img src='app/img/task/iconSmall.png'> Centraliza tus notas, proyectos y contactos</h4>
											<h4><img src='app/img/mail/iconSmall.png'> Enviar boletines por correo electrónico</h4>
											<h4><img src='app/img/postMessage.png'> <a onclick=\"lightboxOpen('?ctrl=user&action=SendInvitation')\">¡Haga clic aquí para enviar correos electrónicos de invitación y hacer crecer su comunidad!</a></h4>
											<h4><img src='app/img/pdf.png'> <a href='https://www.omnispace.fr/index.php?ctrl=offline&action=Documentation' target='_blank'>Para obtener más información, consulte la documentación oficial de Omnispace & Agora-Project</a></h4>",
			"INSTALL_dataDashboardPoll"=>"¿ Qué opinas de la herramienta de noticias ?",
			"INSTALL_dataDashboardPollA"=>"Muy interesante !",
			"INSTALL_dataDashboardPollB"=>"Interesante",
			"INSTALL_dataDashboardPollC"=>"Sin interés",
			"INSTALL_dataCalendarEvt"=>"Bienvenido a Omnispace",
			"INSTALL_dataForumSubject1"=>"Bienvenido al foro de Omnispace",
			"INSTALL_dataForumSubject2"=>"Siéntase libre de compartir sus preguntas o discutir los temas que deseas.",
			"INSTALL_dataTaskStatus1"=>"Por hacer",
			"INSTALL_dataTaskStatus2"=>"En curso",
			"INSTALL_dataTaskStatus3"=>"A validar",
			"INSTALL_dataTaskStatus4"=>"Terminado",

			////	MOD : AGORA
			////
			"AGORA_generalSettings"=>"Administración general",
			"AGORA_Changelog"=>"Ver el registro de versión",
			"AGORA_phpMailDisabled"=>"Función PHP Mail deshabilitada",
			"AGORA_phpLdapDisabled"=>"Función PHP LDAP deshabilitada",
			"AGORA_phpGD2Disabled"=> "Función PHP GD2 deshabilitada",
			"AGORA_backupFull"=>"Copia de seguridad completa",
			"AGORA_backupFullTooltip"=>"Recupere la copia de seguridad completa del espacio: todos los archivos y la base de datos",
			"AGORA_backupDb"=>"Hacer una copia de seguridad de la base de datos",
			"AGORA_backupDbTooltip"=>"Recupere solo la copia de seguridad de la base de datos espacial",
			"AGORA_backupConfirm"=>"¿ Esta operación puede tardar varios minutos: ¿confirmar la descarga ?",
			"AGORA_diskSpaceInvalid"=>"El espacio en disco para los archivos debe ser un número entero",
			"AGORA_visioHostInvalid"=>"La dirección web de su servidor de videoconferencia no es válida: debe comenzar con 'https'",
			"AGORA_gApiKeyInvalid"=>"Si elige Google Map como herramienta de mapeo, debe especificar una 'API Key'",
			"AGORA_gIdentityKeyInvalid"=>"Si elige la conexión opcional a través de Google, debe especificar una 'API Key' para Google SignIn",
			"AGORA_confirmModif"=>"¿ Confirmar los cambios ?",
			"AGORA_name"=>"Nombre del espacio",
			"AGORA_nameTooltip"=>"Nombre que se muestra en la página de inicio de sesión, en correos electrónicos, etc.",
			"AGORA_description"=>"Descripción en la página de inicio de sesión",
			"AGORA_footerHtml"=>"Texto en la parte inferior izquierda de cada página",
			"AGORA_logo"=>"Logotipo en la parte inferior derecha de cada página",
			"AGORA_logoUrl"=>"URL",
			"AGORA_logoConnect"=>"logo / Imagen de la página de conexión",
			"AGORA_logoConnectTooltip"=>"Desplegado encima del formulario de conexión",
			"AGORA_lang"=>"Lenguaje por defecto",
			"AGORA_timezone"=>"Zona horaria",
			"AGORA_diskSpaceLimit"=>"Espacio de disco disponible para los archivos",
			"AGORA_logsTimeOut"=>"Duración del historial de eventos (registros)",
			"AGORA_logsTimeOutTooltip"=>"El período de retención del historial de eventos se refiere a la adición o modificación de los elementos. Los registros de eliminación se mantienen durante al menos 1 año.",
			"AGORA_visioHost"=>"Servidor de videoconferencia Jitsi",
			"AGORA_visioHostTooltip"=>"Dirección del servidor de videoconferencia Jitsi. Ejemplo: https://meet.jit.si",
			"AGORA_visioHostAlt"=>"Servidor de videoconferencia alternativo",
			"AGORA_visioHostAltTooltip"=>"Servidor de videoconferencia alternativo : en caso de indisponibilidad del servidor de video principal",
			"AGORA_skin"=>"Color de la interfaz",
			"AGORA_black"=>"Negro",
			"AGORA_white"=>"Blanco",
			"AGORA_userMailDisplay"=>"Direcciones de correo electrónico de usuario visibles para todos",
			"AGORA_userMailDisplayTooltip"=>"Mostrar el correo electrónico de cada usuario en su perfil, notificaciones por correo electrónico, etc.",
			"AGORA_moduleLabelDisplay"=>"Nombre de los módulos en la barra de menú",
			"AGORA_folderDisplayMode"=>"Visualización en las carpetas",
			"AGORA_wallpaperLogoError"=>"La imagen de fondo y el logotipo debe tener el formato jpg o png",
			"AGORA_deleteWallpaper"=>"Eliminar la imagen de fondo",
			"AGORA_usersCommentLabel"=>"Botón ''Comentarios'' de los elemento",
			"AGORA_usersComment"=>"comentario",
			"AGORA_usersComments"=>"comentarios",
			"AGORA_usersLikeLabel"=>"Botón ''Me gusta'' en los artículos",
			"AGORA_usersLike"=>"Me gusta !",
			"AGORA_mapTool"=>"Herramienta de mapeo",
			"AGORA_mapToolTooltip"=>"Herramienta de mapeo para ver usuarios y contactos en un mapa",
			"AGORA_gApiKey"=>"Clave API de Google para Maps y importación de contactos",
			"AGORA_gApiKeyTooltip"=>"Configuración para Google Maps:<br> https://developers.google.com/maps/documentation/javascript/get-api-key",
			"AGORA_gIdentity"=>"Conexión opcional con Google",
			"AGORA_gIdentityTooltip"=>"Los usuarios pueden conectarse más fácilmente a su espacio a través de su cuenta de Google : para eso, un correo electrónico <i>@gmail.com</ i> ya debe estar registrado en la cuenta del usuario",
			"AGORA_gIdentityClientId"=>"Configuración de Sign-In : Client ID",
			"AGORA_gIdentityClientIdTooltip"=>"Esta configuración es necesaria para Google Sign-In : https://developers.google.com/identity/sign-in/web/",
			"AGORA_messengerDisplay"=>"Mensajería instantánea",
			"AGORA_personsSort"=>"Ordenar los usuarios y contactos",
			//SMTP
			"AGORA_smtpLabel"=>"Conexión SMTP & sendMail",
			"AGORA_sendmailFrom"=>"Email 'From'",
			"AGORA_sendmailFromPlaceholder"=>"eg: 'noreply@mydomain.com'",
			"AGORA_smtpHost"=>"Dirección del servidor (hostname)",
			"AGORA_smtpPort"=>"Puerto de servidor",
			"AGORA_smtpPortTooltip"=>"'25' por defecto. '587' o '465' para SSL/TLS",
			"AGORA_smtpSecure"=>"Tipo de conexión cifrada (opcional)",
			"AGORA_smtpSecureTooltip"=>"'ssl' o 'tls'",
			"AGORA_smtpUsername"=>"Nombre del usuario",
			"AGORA_smtpPass"=>"Contraseña",
			//LDAP
			"AGORA_ldapLabel"=>"Conexión a un servidor LDAP",
			"AGORA_ldapLabelTooltip"=>"Conexión a un servidor LDAP para la creación de usuarios en el espacio : cf. Opción ''Importación/exportación de usuarios'' del módulo ''Usuario''",
			"AGORA_ldapUri"=>"URI LDAP",
			"AGORA_ldapUriTooltip"=>"URI de LDAP completo con el formato LDAP://hostname:port o LDAPS://hostname:port para el cifrado SSL.",
			"AGORA_ldapPort"=>"Puerto del servidor",
			"AGORA_ldapPortTooltip"=>"El puerto utilizado para la conexión: '' 389 '' por defecto",
			"AGORA_ldapLogin"=>"DN del administrador LDAP (Distinguished Name)",
			"AGORA_ldapLoginTooltip"=>"por ejemplo ''cn=admin,dc=mon-entreprise,dc=com''",
			"AGORA_ldapPass"=>"Contraseña del administrador",
			"AGORA_ldapDn"=>"DN del grupo de usuarios (Distinguished Name)",
			"AGORA_ldapDnTooltip"=>"DN del grupo de usuarios : ubicación de los usuarios en el directorio. Ejemplo ''ou=mon-groupe,dc=mon-entreprise,dc=com''",
			"importLdapFilterTooltip"=>"Filtro de búsqueda LDAP (cf. https://www.php.net/manual/function.ldap-search.php). Ejemplo ''(cn=*)'' o ''(&(samaccountname=MONLOGIN)(cn=*))''",
			"AGORA_ldapConnectError"=>"Error de conexión del servidor LDAP !",

			////	MOD : LOG
			////
			"LOG_moduleDescription"=>"Logs - Registro de eventos",
			"LOG_path"=>"Camino",
			"LOG_filter"=>"filtro",
			"LOG_date"=>"Fecha / Hora",
			"LOG_spaceName"=>"Espacio",
			"LOG_moduleName"=>"Módulo",
			"LOG_objectType"=>"typo de objeto",
			"LOG_action"=>"Acción",
			"LOG_userName"=>"Usuario",
			"LOG_ip"=>"IP",
			"LOG_comment"=>"Comentario",
			"LOG_noLogs"=>"Ningún registro",
			"LOG_filterSince"=>"filtrado de la",
			"LOG_search"=>"Buscar",
			"LOG_connexion"=>"Conexión",//action
			"LOG_add"=>"Añadir",//action
			"LOG_delete"=>"eliminar",//action
			"LOG_modif"=>"cambio",//action

			////	MOD : SPACE
			////
			"SPACE_moduleTooltip"=>"El espacio principal se puede subdividir en varios espacios (ver ''subespacio'')",
			"SPACE_manageAllSpaces"=>"Administrar todos los espacios",
			"SPACE_config"=>"Administración del espacio",//.."mon espace"
			// Index
			"SPACE_confirmDeleteDbl"=>"Confirmar eliminación ? Atención, los datos afectados a este espacio seran  definitivamente perdidas !!",
			"SPACE_space"=>"Espacio",
			"SPACE_spaces"=>"Espacios",
			"SPACE_accessRightUndefined"=>"Definir !",
			"SPACE_modules"=>"Módulos",
			"SPACE_addSpace"=>"Añadir un espacio",
			//Edit
			"SPACE_userAdminAccess"=>"Usuarios y administradores del espacio",
			"SPACE_selectModule"=>"Debe seleccionar al menos un módulo",
			"SPACE_spaceModules"=>"Módulos del espacio",
			"SPACE_publicSpace"=>"Espacio público : acceso invitado",
			"SPACE_publicSpaceTooltip"=>"Un espacio público está abierto a personas que no tengan cuenta de usuario (invitados). Podrán acceder al espacio desde la página de inicio. Se puede especificar una contraseña para proteger el acceso a este espacio público. Los módulos 'Emails' y 'Usuarios' no están disponibles para invitados.",
			"SPACE_publicSpaceNotif"=>"Tu espacio es público: si contiene datos personales (teléfono, dirección, etc.) recuerda especificar una contraseña para cumplir con el RGPD: Reglamento General de Protección de Datos",
			"SPACE_usersInvitation"=>"Los usuarios pueden enviar invitaciones por correo",
			"SPACE_usersInvitationTooltip"=>"Todos los usuarios pueden enviar invitaciones por correo electrónico para unirse al espacio",
			"SPACE_allUsers"=>"Todos los usuarios",
			"SPACE_user"=>" Usuarios",
			"SPACE_userTooltip"=>"Usuario del espacio : <br> Acceso normal al espacio",
			"SPACE_admin"=>"Administrador",
			"SPACE_adminTooltip"=>"El administrador de un espacio es un usuario que puede editar o eliminar todos los elementos presentes en el espacio. También puede configurar el espacio, crear nuevas cuentas de usuario, crear grupos de usuarios, enviar invitaciones por correo electrónico para añadir nuevos usuarios, etc.",

			////	MOD : USER
			////
			// Menu principal
			"USER_headerModuleName"=>"Usuarios",
			"USER_moduleDescription"=>"Usuarios del espacio",
			"USER_option_allUsersAddGroup"=>"Los usuarios también pueden crear grupos",//OPTION!
			//Index
			"USER_spaceOrAllUsersTooltip"=>"Administrar usuarios del espacio actual / Administrar usuarios de todos los espacios (reservado para el administrador general)",
			"USER_spaceUsers"=>"Usuarios del espacio corriente",
			"USER_allUsers"=>"Administrar todos los usuarios",
			"USER_deleteDefinitely"=>"Eliminar definitivamente",
			"USER_deleteFromCurSpace"=>"Desasignar al espacio actual",
			"USER_deleteFromCurSpaceConfirm"=>"¿ Desasignar el usuario del espacio actual ?",
			"USER_allUsersOnSpaceNotif"=>"Todo los usuarios son asignados a este espacio",
			"USER_user"=>"Usuario",
			"USER_users"=>"usuarios",
			"USER_addExistUser"=>"Añadir un usuario existente, a ese espacio",
			"USER_addExistUserTitle"=>"Añadir al espacio a un usuario ya existente en el sitio : asignación al espacio",
			"USER_addUser"=>"Añadir un usuario",
			"USER_addUserSite"=>"Crear un usuario en el sitio : por defecto, asignado a ningun espacio !",
			"USER_addUserSpace"=>"Crear un usuario en el espacio actual",
			"USER_sendCoords"=>"Enviar el nombre de usuario y contraseña",
			"USER_sendCoordsTooltip"=>"Envíe a los usuarios un correo electrónico con su Login y un enlace web para inicializar su contraseña",
			"USER_sendCoordsTooltip2"=>"Enviar a cada nuevo usuario un correo electrónico con información de acceso.",
			"USER_sendCoordsConfirm"=>"¿ Confirmar ?",
			"USER_sendCoordsMail"=>"Sus datos de acceso a su espacio",
			"USER_noUser"=>"Ningún usuario asignado a este espacio por el momento",
			"USER_spaceList"=>"Espacios del usuario",
			"USER_spaceNoAffectation"=>"Ningún espacio",
			"USER_adminGeneral"=>"Administrador General del Sitio",
			"USER_adminGeneralTooltip"=>"Advertencia: el derecho de acceso de ''administrador general'' otorga muchos privilegios y responsabilidades, en particular para editar todos los elementos (calendarios, carpetas, archivos, etc.), así como todos los usuarios y espacios. Por lo tanto, es recomendable asignar este privilegio a 2 o 3 usuarios como máximo.<br><br>Para privilegios más restringidos, elija el derecho de acceso ''administrador del espacio'' (ver menú principal > ''Configurar el espacio'')",
			"USER_adminSpace"=>"Administrador del espacio",
			"USER_userSpace"=>"Usuario del espacio",
			"USER_profilEdit"=>"Editar el perfil",
			"USER_myProfilEdit"=>"Editar mi perfil de usuario",
			// Invitation
			"USER_sendInvitation"=>"Enviar invitaciones por email",
			"USER_sendInvitationTooltip"=>"Enviar invitaciones por correo electrónico para unirse al espacio actual. Una vez validada la invitación, se crea automáticamente una cuenta de usuario para la persona interesada.",
			"USER_mailInvitationObject"=>"Invitación de", // ..Jean DUPOND
			"USER_mailInvitationFromSpace"=>"le invita al espacio ", // Jean DUPOND "vous invite à rejoindre l'espace" Mon Espace
			"USER_mailInvitationConfirm"=>"Haga clic aquí para confirmar la invitación",
			"USER_mailInvitationWait"=>"Invitaciones a confirmar",
			"USER_exired_idInvitation"=>"La enlace de su invitación ha caducado",
			"USER_invitPassword"=>"Confirmar su invitación",
			"USER_invitPassword2"=>"Elejir su contraseña para confirmar su invitación",
			"USER_invitationValidated"=>"Su invitación ha sido validado !",
			"USER_gPeopleImport"=>"Obtener mis contactos de mi dirección de Gmail",
			"USER_importQuotaExceeded"=>"Está limitado a --USERS_QUOTA_REMAINING-- nuevas cuentas de usuario, de un total de --LIMITE_NB_USERS-- usuarios",
			// groupes
			"USER_spaceGroups"=>"grupos de usuarios del espacio",
			"USER_spaceGroupsEdit"=>"modificar los grupos de usuarios del espacio",
			"USER_groupEditInfo"=>"Cada grupo puede ser modificado por su autor o por el administrador del espacio",
			"USER_addGroup"=>"Añadir un grupo",
			"USER_userGroups"=>"Grupos del usuario",
			// Utilisateur_affecter
			"USER_searchPrecision"=>"Gracias a especificar un nombre, un apellido o una dirección de correo electrónico",
			"USER_userAffectConfirm"=>"¿ Confirmar las asignaciónes ?",
			"USER_userSearch"=>"Buscar usuarios para añadirlo al espacio",
			"USER_allUsersOnSpace"=>"Todos los usuarios del sitio ya están asignados a este espacio",
			"USER_usersSpaceAffectation"=>"Asignar usuarios al espacio :",
			"USER_usersSearchNoResult"=>"No hay usuarios para esta búsqueda",
			"USER_usersSearchBack"=>"Atrás",
			// Utilisateur_edit & CO
			"USER_langs"=>"Idioma",
			"USER_persoCalendarDisabled"=>"Calendario personal desactivado",
			"USER_persoCalendarDisabledTooltip"=>"Se asigna un calendario personal por defecto a cada usuario (incluso si el módulo ''Calendario'' no está activado en el espacio). Marque esta opción para deshabilitar el calendario personal de este usuario.",
			"USER_connectionSpace"=>"Espacio de conexión",
			"USER_loginExists"=>"El login/email ya existe ¡ Gracias a especificar otro !",
			"USER_mailPresentInAccount"=>"ya existe una cuenta de usuario con esta dirección de correo electrónico",
			"USER_loginAndMailDifferent"=>"Ambas direcciones de correo electrónico deben ser idénticas",
			"USER_mailNotifObject"=>"Nueva cuenta en ",  // "...sur" l'Agora machintruc
			"USER_mailNotifContent"=>"Tu cuenta de usuario ha sido creada en",  // idem
			"USER_mailNotifContent2"=>"Conectar con el login y la contraseña siguientes",
			"USER_mailNotifContent3"=>"Gracias a mantener este correo electrónico para sus archivos.",
			// Livecounter & Messenger & Visio
			"USER_messengerEdit"=>"Configurar mi mensajería instantánea",
			"USER_messengerEdit2"=>"Configurar mensajería instantánea",
			"USER_livecounterVisibility"=>"Visibilidad en mensajería instantánea y videoconferencia",
			"USER_livecounterAllUsers"=>"Mostrar mi presencia cuando estoy conectado: mensajería / video habilitado",
			"USER_livecounterDisabled"=>"Ocultar mi presencia cuando estoy conectado: mensajería / video desactivado",
			"USER_livecounterSomeUsers"=>"Solo ciertos usuarios pueden verme cuando estoy conectado",

			////	MOD : DASHBOARD
			////
			// Menu principal + options du module
			"DASHBOARD_headerModuleName"=>"Noticias",
			"DASHBOARD_moduleDescription"=>"Noticias, Encuestas y Elementos recientes",
			"DASHBOARD_option_adminAddNews"=>"Sólo el administrador puede añadir noticias",//OPTION!
			"DASHBOARD_option_disablePolls"=>"Deshabilitar encuestas",//OPTION!
			"DASHBOARD_option_adminAddPoll"=>"Sólo el administrador puede añadir encuestas",//OPTION!
			//Index
			"DASHBOARD_menuNews"=>"Noticias",
			"DASHBOARD_menuPolls"=>"Encuestas",
			"DASHBOARD_menuElems"=>"Elementos recientes y actuales",
			"DASHBOARD_addNews"=>"Añadir una noticia",
			"DASHBOARD_offlineNews"=>"Mostrar noticias archivadas",
			"DASHBOARD_offlineNewsNb"=>"noticias archivadas",//"55 actualités archivées"
			"DASHBOARD_noNews"=>"No hay noticias por el momento",
			"DASHBOARD_addPoll"=>"Añadir una encuesta",
			"DASHBOARD_pollsVoted"=>"Mostrar solo encuestas votadas",
			"DASHBOARD_pollsVotedNb"=>"encuestas por las que ya he votado",//"55 sondages..déjà voté"
			"DASHBOARD_pollsNotVoted"=>"encuestas no votadas",//55 sondages non votés
			"DASHBOARD_vote"=>"Votar y ver los resultados !",
			"DASHBOARD_voteTooltip"=>"Los votos son anónimos : nadie sabrá su elección de voto",
			"DASHBOARD_answerVotesNb"=>"Votada --NB_VOTES-- veces",//55 votes (sur la réponse)
			"DASHBOARD_pollVotesNb"=>"La encuesta fue votada --NB_VOTES-- veces",
			"DASHBOARD_pollVotedBy"=>"Votada por",//Bibi, boby, etc
			"DASHBOARD_noPoll"=>"No hay encuesta por el momento",
			"DASHBOARD_plugins"=>"Nuevos elementos",
			"DASHBOARD_pluginsTooltip"=>"Elementos creados",
			"DASHBOARD_pluginsTooltip2"=>"entre",
			"DASHBOARD_plugins_day"=>"de hoy",
			"DASHBOARD_plugins_week"=>"de esta semana",
			"DASHBOARD_plugins_month"=>"del mes",
			"DASHBOARD_plugins_previousConnection"=>"desde la última conexión",
			"DASHBOARD_pluginsTooltipRedir"=>"Ver el elemento en la carpeta",
			"DASHBOARD_pluginEmpty"=>"No hay nuevos elementos sobre este periodo",
			// Actualite/News
			"DASHBOARD_topNews"=>"Noticia importante",
			"DASHBOARD_topNewsTooltip"=>"Noticia importante, en la parte superior de la lista",
			"DASHBOARD_offline"=>"Noticia archivada",
			"DASHBOARD_dateOnline"=>"En línea el",
			"DASHBOARD_dateOnlineTooltip"=>"Establecer una fecha de línea automático (en línea). La noticia será 'archivada' 'en el ínterin",
			"DASHBOARD_dateOnlineNotif"=>"La noticia esta archivado en la expectativa de su línea automática",
			"DASHBOARD_dateOffline"=>"Archivar el",
			"DASHBOARD_dateOfflineTooltip"=>"Fije una fecha de archivo automático (Desconectado)",
			// Sondage/Polls
			"DASHBOARD_titleQuestion"=>"Título / Pregunta",
			"DASHBOARD_multipleResponses"=>"Varias respuestas posibles para cada voto",
			"DASHBOARD_newsDisplay"=>"Mostrar con noticias (menú izquierdo)",
			"DASHBOARD_publicVote"=>"Voto público: la elección de los votantes es pública",
			"DASHBOARD_publicVoteInfos"=>"Tenga en cuenta que la votación pública puede ser una barrera para la participación en la encuesta.",
			"DASHBOARD_dateEnd"=>"Fin de votaciones",
			"DASHBOARD_responseList"=>"Posibles respuestas",
			"DASHBOARD_responseNb"=>"Respuesta n°",
			"DASHBOARD_addResponse"=>"Añadir una respuesta",
			"DASHBOARD_controlResponseNb"=>"Por favor, especifique al menos 2 respuestas posibles",
			"DASHBOARD_votedPollNotif"=>"Atención: tan pronto como se vota la encuesta, ya no es posible cambiar el título o las respuestas",
			"DASHBOARD_voteNoResponse"=>"Por favor seleccione una respuesta",
			"DASHBOARD_exportPoll"=>"Descarga los resultados de la encuesta en pdf",
			"DASHBOARD_exportPollDate"=>"resultado de la encuesta al",

			////	MOD : FILE
			////
			// Menu principal
			"FILE_headerModuleName"=>"Archivos",
			"FILE_moduleDescription"=>"Administración de Archivos",
			"FILE_option_adminRootAddContent"=>"Sólo el administrador puede añadir elementos en el directorio raíz",//OPTION!
			//Index
			"FILE_addFile"=>"Añadir archivos",
			"FILE_addFileAlert"=>"Los directorios del servidor no son accesible en escritura !  gracias de contactar el administrador",
			"FILE_downloadSelection"=>"Descargar selección",
			"FILE_fileDownload"=>"Descargar",
			"FILE_fileSize"=>"Tamaño del archivo",
			"FILE_imageSize"=>"Tamaño de la imagen",
			"FILE_nbFileVersions"=>"Archivo versiones",//"55 versions du fichier"
			"FILE_downloadsNb"=>"(descargado --NB_DOWNLOAD-- veces)",
			"FILE_downloadedBy"=>"archivo subido por",//"..boby, will"
			"FILE_addFileVersion"=>"Añadir nueva versión del archivo",
			"FILE_noFile"=>"No hay archivo en este momento",
			// fichier_edit_ajouter  &  Fichier_edit
			"FILE_fileSizeLimit"=>"Los archivos no deben exceder", // ...2 Mega Octets
			"FILE_uploadSimple"=>"Formulario simple",
			"FILE_uploadMultiple"=>"Formulario multiple",
			"FILE_imgReduce"=>"Optimizar la imagen",
			"FILE_updatedName"=>"El nombre del archivo será reemplazado por la nueva versión",
			"FILE_fileSizeError"=>"Archivo demasiado grande",
			"FILE_addMultipleFilesTooltip"=>"Pulse 'Maj' o 'Ctrl' para seleccionar varios archivos",
			"FILE_selectFile"=>"Gracias por elegir al menos un archivo",
			"FILE_fileContent"=>"contenido",
			// Versions_fichier
			"FILE_versionsOf"=>"Versiones de", // versions de fichier
			"FILE_confirmDeleteVersion"=>"¿ Confirme la eliminación de esta versión ?",

			////	MOD : CALENDAR
			////
			// Menu principal
			"CALENDAR_headerModuleName"=>"Calendarios",
			"CALENDAR_moduleDescription"=>"Calendarios personal y calendarios compartidos",
			"CALENDAR_option_adminAddRessourceCalendar"=>"Sólo el administrador puede añadir calendarios de recursos",//OPTION!
			"CALENDAR_option_adminAddCategory"=>"Sólo el administrador puede añadir categorías de eventos",//OPTION!
			"CALENDAR_option_createSpaceCalendar"=>"Crear un calendario compartido para el espacio",//OPTION!
			"CALENDAR_moduleAlwaysEnabledInfo"=>"Los usuarios que no hayan desactivado su calendario personal en su perfil de usuario seguirán viendo el módulo Calendario en la barra de menú.",
			//Index
			"CALENDAR_calsList"=>"Calendarios disponibles",
			"CALENDAR_hideAllCals"=>"Ocultar todo los calendarios",
			"CALENDAR_printCalendars"=>"Imprimir el/los calendarios",
			"CALENDAR_printCalendarsInfos"=>"imprimir la página en modo horizontal",
			"CALENDAR_addSharedCalendar"=>"Añadir un calendario compartido",
			"CALENDAR_addSharedCalendarTooltip"=>"Añadir un calendario compartido : para reservar une habitación, vehiculo, vídeo, etc.",
			"CALENDAR_exportIcal"=>"Exportar los eventos (iCal)",
			"CALENDAR_icalUrl"=>"Copie la dirección web (URL) para mostrar el calendario desde un calendario externo",
			"CALENDAR_icalUrlCopy"=>"Permite el acceso de lectura al calendario desde un calendario externo como Thunderbird, Outlook, Google Calendar, etc.",
			"CALENDAR_importIcal"=>"Importar los eventos (iCal)",
			"CALENDAR_ignoreOldEvt"=>"No importe eventos de más de un año",
			"CALENDAR_importIcalPresent"=>"¿Ya está presente?",
			"CALENDAR_importIcalPresentInfo"=>"Evento ya presente en el calendario ?",
			"CALENDAR_display_3Days"=>"3 días",
			"CALENDAR_display_7Days"=>"7 días",
			"CALENDAR_display_week"=>"Semana",
			"CALENDAR_display_workWeek"=>"Semana de trabajo",
			"CALENDAR_display_month"=>"Mes",
			"CALENDAR_yearWeekNum"=>"Ver la semana n°", //...5
			"CALENDAR_periodNext"=>"Período siguiente",
			"CALENDAR_periodPrevious"=>"Período anterior",
			"CALENDAR_evtAffects"=>"En el calendario de",
			"CALENDAR_evtAffectToConfirm"=>"Pendiente en el calendario de",
			"CALENDAR_evtProposed"=>"Propuesto de eventos a confirmar",
			"CALENDAR_evtProposedBy"=>"Propuestos por",//..Mr SMITH
			"CALENDAR_evtProposedConfirm"=>"Confirma la propuesta",
			"CALENDAR_evtProposedConfirmBis"=>"La propuesta del evento se ha integrado en la agenda",
			"CALENDAR_evtProposedConfirmMail"=>"Tu propuesta de evento ha sido confirmada",
			"CALENDAR_evtProposedDecline"=>"Rechazar la propuesta",
			"CALENDAR_evtProposedDeclineBis"=>"La propuesta ha sido rechazada",
			"CALENDAR_evtProposedDeclineMail"=>"Tu propuesta de evento ha sido rechazada",
			"CALENDAR_deleteEvtCal"=>"¿ Eliminar sólo en ese calendario ?",
			"CALENDAR_deleteEvtCals"=>"¿ Eliminar en todos los calendarios ?",
			"CALENDAR_deleteEvtDate"=>"¿ Eliminar sólo en esta fecha ?",
			"CALENDAR_evtPrivate"=>"Évento privado",
			"CALENDAR_evtAutor"=>"Eventos que he creado",
			"CALENDAR_evtAutorInfo"=>"Mostrar solo eventos que he creado",
			"CALENDAR_noEvt"=>"No hay eventos",
			"CALENDAR_calendarsPercentBusy"=>"Calendarios ocupados",  // Agendas occupés : 2/5
			"CALENDAR_noCalendarDisplayed"=>"No calendario",
			// Evenement
			"CALENDAR_importanceNormal"=>"Importancia normal",
			"CALENDAR_importanceHight"=>"Alta importancia",
			"CALENDAR_visibilityPublic"=>"Visibilidad normal",
			"CALENDAR_visibilityPublicHide"=>"Visibilidad de franja horaria",
			"CALENDAR_visibilityPrivate"=>"Visibilidad privada",
			"CALENDAR_visibilityTooltip"=>"Para personas que solo tienen acceso de lectura al calendario: <br>- Visibilidad de franja horaria : muestra solo la franja horaria ocupada por el evento y oculta los detalles<br>- Visualización privada: no muestra el evento",
			// Agenda/Evenement : edit
			"CALENDAR_sharedCalendarDescription"=>"Calendario compartido del espacio",
			"CALENDAR_noPeriodicity"=>"Una vez",
			"CALENDAR_period_weekDay"=>"Cada semana",
			"CALENDAR_period_month"=>"Cada mes",
			"CALENDAR_period_year"=>"Cada año",
			"CALENDAR_periodDateEnd"=>"Hasta el",
			"CALENDAR_periodException"=>"Excepción de periodicidad",
			"CALENDAR_calendarAffectations"=>"Asignación a los calendarios",
			"CALENDAR_addEvt"=>"Añadir un evento",
			"CALENDAR_addEvtTooltip"=>"Añadir un evento",
			"CALENDAR_addEvtTooltipBis"=>"Añadir el evento al calendario",
			"CALENDAR_proposeEvtTooltip"=>"Proponer un evento al administrador del calendario",
			"CALENDAR_proposeEvtTooltipBis"=>"Proponer el evento al administrador del calendario",
			"CALENDAR_proposeEvtTooltipBis2"=>"Proponer el evento al administrador del calendario : el calendario solo es accesible para lectura",
			"CALENDAR_inputProposed"=>"El evento será propuesto al administrador del calendario",
			"CALENDAR_verifCalNb"=>"Gracias por seleccionar por lo menos un calendario",
			"CALENDAR_noModifTooltip"=>"Edición prohibida porque no tiene acceso de escritura al calendario",
			"CALENDAR_editLimit"=>"Usted no es el autor de el evento : sólo puedes editar las asignaciones a sus calendarios",
			"CALENDAR_busyTimeSlot"=>"La ranura ya está ocupado en este calendario :",
			"CALENDAR_timeSlot"=>"Rango de tiempo de la pantalla ''semana''",
			"CALENDAR_propositionNotif"=>"Notificar por correo electrónico de cada propuesta de evento",
			"CALENDAR_propositionNotifTooltip"=>"Nota: Cada propuesta de evento es validada o invalidada por el administrador del calendario.",
			"CALENDAR_propositionGuest"=>"Los invitados pueden proponer eventos",
			"CALENDAR_propositionGuestTooltip"=>"Nota: Recuerde seleccionar 'todos los usuarios e invitados' en los derechos de acceso.",
			"CALENDAR_propositionEmailSubject"=>"Nuevo evento propuesto por",//.."boby SMITH"
			"CALENDAR_propositionEmailMessage"=>"Nuevo evento propuesto por --AUTOR_LABEL-- : &nbsp; <i><b>--EVT_TITLE_DATE--</b></i> <br><i>--EVT_DESCRIPTION--</i> <br>Accede a tu espacio para confirmar o cancelar esta propuesta",
			// Category : Catégories d'événement
			"CALENDAR_categoryMenuTooltip"=>"Mostrar solo eventos con categoría",
			"CALENDAR_categoryShowAll"=>"Toda las categorías",
			"CALENDAR_categoryShowAllTooltip"=>"Mostrar toda las categorías",
			"CALENDAR_categoryUndefined"=>"Sin categoría",
			"CALENDAR_categoryEditTitle"=>"Editar las categorías",
			"CALENDAR_categoryEditInfo"=>"Cada categoría de evento puede ser modificada por su autor o por el administrador general",
			"CALENDAR_categoryEditAdd"=>"Añadir una categoría de evento",

			////	MOD : FORUM
			////
			// Menu principal
			"FORUM_headerModuleName"=>"Foro",
			"FORUM_moduleDescription"=>"Foro",
			"FORUM_option_adminAddSubject"=>"Sólo el administrador puede añadir sujetos",//OPTION!
			"FORUM_option_adminAddTheme"=>"Sólo el administrador puede añadir temas",//OPTION!
			"SORT_dateLastMessage"=>"último mensaje",
			//Index & Sujet
			"FORUM_forumRoot"=>"Inicio del foro",
			"FORUM_subject"=>"sujeto",
			"FORUM_subjects"=>"sujetos",
			"FORUM_message"=>"mensaje",
			"FORUM_messages"=>"mensajes",
			"FORUM_lastMessageFrom"=>"ultimo de",
			"FORUM_noSubject"=>"Sin sujeto por el momento",
			"FORUM_subjectBy"=>"sujeto de",
			"FORUM_addSubject"=>"Nuevo sujeto",
			"FORUM_displaySubject"=>"Ver el sujeto",
			"FORUM_addMessage"=>"Responder",
			"FORUM_quoteMessage"=>"Responder",
			"FORUM_quoteMessageInfo"=>"Responder y citar a ese mensaje",
			"FORUM_notifyLastPost"=>"Notificar por e-mail",
			"FORUM_notifyLastPostTooltip"=>"Deseo recibir una notificación por correo a cada nuevo mensaje",
			// Sujet_edit  &  Message_edit
			"FORUM_notifOnlyReadAccess"=>"Si solo hay acceso de lectura, nadie puede contribuir al tema.",
			"FORUM_notifWriteAccess"=>"El acceso de ''escritura'' está destinado a los moderadores :<br>Si es necesario, prefiera los derechos de ''escritura limitada''",
			// Category : Themes
			"FORUM_categoryMenuTooltip"=>"Mostrar solo sujetos con tema",
			"FORUM_categoryShowAll"=>"Todo los temas",
			"FORUM_categoryShowAllTooltip"=>"Mostrar todo los temas",
			"FORUM_categoryUndefined"=>"Sin tema",
			"FORUM_categoryEditTitle"=>"Editar los temas",
			"FORUM_categoryEditInfo"=>"Cada tema puede ser modificado por su autor o por el administrador general",
			"FORUM_categoryEditAdd"=>"Añadir un tema",

			////	MOD : TASK
			////
			// Menu principal
			"TASK_headerModuleName"=>"Tareas",
			"TASK_moduleDescription"=>"Tareas",
			"TASK_option_adminRootAddContent"=>"Sólo el administrador puede añadir elementos en el directorio raíz",//OPTION!
			"TASK_option_adminAddStatus"=>"Sólo el administrador puede crear status Kanban",//OPTION!
			"SORT_priority"=>"Prioridad",
			"SORT_advancement"=>"Progreso",
			"SORT_dateBegin"=>"Fecha de inicio",
			"SORT_dateEnd"=>"Fecha de fin",
			//Index
			"TASK_addTask"=>"Añadir una tareas",
			"TASK_noTask"=>"No hay tarea por el momento",
			"TASK_advancement"=>"Progreso",
			"TASK_advancementAverage"=>"Progreso promedio",
			"TASK_priority"=>"Prioridad",
			"TASK_priorityUndefined"=>"Prioridad indefinida",
			"TASK_priority1"=>"Baja",
			"TASK_priority2"=>"promedia",
			"TASK_priority3"=>"alta",
			"TASK_assignedTo"=>"Asignado a",
			"TASK_advancementLate"=>"Progreso retrasado",
			"TASK_folderDateBeginEnd"=>"Fecha de inicio más temprana / de fin más reciente",
			//Categorie : Statuts Kanban
			"TASK_categoryMenuTooltip"=>"Mostrar solo tareas con estado",
			"TASK_categoryShowAll"=>"Todos los estados",
			"TASK_categoryShowAllTooltip"=>"Mostrar todos los estados",
			"TASK_categoryUndefined"=>"Estado indefinido",
			"TASK_categoryEditTitle"=>"Editar los estados",
			"TASK_categoryEditInfo"=>"Cada estado puede ser modificado por su autor o por el administrador general.",
			"TASK_categoryEditAdd"=>"Añadir un estado",

			////	MOD : CONTACT
			////
			// Menu principal
			"CONTACT_headerModuleName"=>"Contactos",
			"CONTACT_moduleDescription"=>"Directorio de contactos",
			"CONTACT_option_adminRootAddContent"=>"Sólo el administrador puede añadir elementos en el directorio raíz",//OPTION!
			//Index
			"CONTACT_addContact"=>"Añadir un contacto",
			"CONTACT_noContact"=>"No hay contacto todavía",
			"CONTACT_createUser"=>"Crear un usuario en este espacio",
			"CONTACT_createUserConfirm"=>"¿ Crear un usuario en este espacio con este contacto ?",
			"CONTACT_createUserConfirmed"=>"El usuario fue creado",

			////	MOD : LINK
			////
			// Menu principal
			"LINK_headerModuleName"=>"Favoritos",
			"LINK_moduleDescription"=>"Favoritos",
			"LINK_option_adminRootAddContent"=>"Sólo el administrador puede añadir elementos en el directorio raíz",//OPTION!
			//Index
			"LINK_addLink"=>"Añadir un enlace",
			"LINK_noLink"=>"No hay enlaces por el momento",
			// lien_edit & dossier_edit
			"LINK_adress"=>"Dirección web",

			////	MOD : MAIL
			////
			// Menu principal
			"MAIL_headerModuleName"=>"Emails",
			"MAIL_moduleDescription"=>"Enviar mensajes de correo electrónico con un solo clic !",
			//Index
			"MAIL_specifyMail"=>"Gracias especificar al menos un destinatario",
			"MAIL_title"=>"Asunto del email",
			"MAIL_description"=>"Mensaje del email",
			// Historique Email
			"MAIL_historyTitle"=>"Historia de los correos electrónicos enviados",
			"MAIL_delete"=>"Eliminar este correo electrónico",
			"MAIL_resend"=>"Reenviar este correo electrónico",
			"MAIL_resendInfo"=>"Recupere el contenido de este correo electrónico e intégrelo directamente en el editor para un nuevo envío",
			"MAIL_historyEmpty"=>"No correo electrónico",
		);
	}

	/*
	 * Jours Fériés de l'année
	 */
	public static function publicHolidays($year)
	{
		$dateList[$year."-01-01"]="Año Nuevo";
		$dateList[$year."-01-06"]="Día de Reyes";
		$dateList[$year."-05-01"]="Día del Trabajador";
		$dateList[$year."-08-15"]="Asunción";
		$dateList[$year."-10-12"]="Fiesta Nacional de España";
		$dateList[$year."-11-01"]="Día de todos los Santos";
		$dateList[$year."-12-06"]="Día de la Constitución";
		$dateList[$year."-12-08"]="Inmaculada Concepción";
		$dateList[$year."-12-25"]="Navidad";
		$dateList[$year."-12-26"]="Sant Esteve";
		if(function_exists("easter_date")){
			$easterTime=easter_date($year);
			$dateList[date("Y-m-d",$easterTime-(86400*3))]	="Jueves Santo";
			$dateList[date("Y-m-d",$easterTime-(86400*2))]	="Viernes Santo";
			$dateList[date("Y-m-d",$easterTime)]			="Pâques";
			$dateList[date("Y-m-d",$easterTime+86400)]		="Lunes de Pascua";
		}
		return $dateList;
	}
}