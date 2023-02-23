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
		////	Dates formatées par PHP
		setlocale(LC_TIME, "es_ES.utf8", "es_ES.UTF-8", "es_ES", "es", "spanish");

		////	TRADUCTIONS
		self::$trad=array(
			////	Header http / Editeurs Tinymce,DatePicker,etc
			"CURLANG"=>"es",
			"DATELANG"=>"es_ES",
			"EDITORLANG"=>"es",

			////	Divers
			"fillFieldsForm"=>"Por favor, rellene los campos del formulario",
			"requiredFields"=>"Campo obligatorio",
			"inaccessibleElem"=>"Elemento inaccesible",
			"warning"=>"Atención",
			"elemEditedByAnotherUser"=>"El elemento está siendo editado por",//"..bob"
			"yes"=>"sí",
			"no"=>"no",
			"none"=>"no",
			"noneFem"=>"no",
			"or"=>"o",
			"and"=>"y",
			"goToPage"=>"Ir a la página",
			"alphabetFilter"=>"Filtro alfabético",
			"displayAll"=>"Mostrar todo",
			"allCategory"=>"Cualquier categoría",
			"show"=>"mostrar",
			"hide"=>"ocultar",
			"byDefault"=>"Por defecto",
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
			"visibleAllSpaces"=>"Visible en todos los espacios",
			"confirmCloseForm"=>"¿Quieres cerrar el formulario?",
			"modifRecorded"=>"Los cambios fueron registrados",
			"confirm"=>"¿ Confirmar ?",
			"comment"=>"Comentario",
			"commentAdd"=>"Añadir un comentario",
			"optional"=>"(opcional)",
			"objNew"=>"Elemento creado recientemente",
			"personalAccess"=>"Acceso personal",
			"copyUrl"=>"Copie el enlace/URL al elemento",
			"copyUrlInfo"=>"Este enlace/url permite el acceso directo al elemento: <br> Puede integrarse en una noticia, un tema de foro, un correo electrónico, un blog (acceso externo), etc.",
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
			"specifyLoginMail"=>"Se recomienda utilizar un email como identificador de sesión",
			"login"=>"Email / Identificador de conexión",
			"loginPlaceholder"=>"Email / Identificador",
			"connect"=>"Conexión",
			"connectAuto"=>"Recuérdame",
			"connectAutoInfo"=>"Recordar mi nombre de usuario y la contraseña para una conexión automática",
			"gIdentityButton"=>"iniciar sesión con google",
			"gIdentityButtonInfo"=>"Inicie sesión con su cuenta Google : ya debe tener una cuenta en este espacio, con una dirección de correo electrónico <i>@gmail.com</i>",
			"gIdentityUserUnknown"=>"no está registrado en el espacio",
			"connectSpaceSwitch"=>"Conectarse a otro espacio",
			"connectSpaceSwitchConfirm"=>"¿Está seguro de que desea abandonar este espacio para conectarse a otro espacio?",
			"guestAccess"=>"Iniciar sesión como invitado",
			"guestAccessInfo"=>"Iniciar sesión en un espacio como invitado",
			"spacePassError"=>"Contraseña incorrecta",
			"ieObsolete"=>"Su navegador es demasiado viejo y no soporta todos los elementos de HTML : Se recomienda actualizarlo o utilizar otro navegador",
			
			////	Password : connexion d'user / edition d'user / reset du password
			"password"=>"Contraseña",
			"passwordModify"=>"Cambiar la contraseña",
			"passwordToModify"=>"Contraseña temporal (a cambiar al iniciar sesión)",//Mail d'envoi d'invitation
			"passwordToModify2"=>"Contraseña (cambiar si es necesario)",//Mail de création de compte
			"passwordVerif"=>"Confirmar contraseña",
			"passwordInfo"=>"Dejar en blanco si desea mantener su contraseña",
			"passwordInvalid"=>"Su contraseña debe tener al menos 6 caracteres con al menos 1 dígito y al menos 1 letra",
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
			"displayMode_line"=>"Lista",
			"displayMode_block"=>"Bloque",

			////	Sélectionner / Déselectionner tous les éléments
			"select"=>"Seleccionar",
			"selectUnselect"=>"Seleccionar / Deseleccionar",
			"selectAll"=>"Seleccionar todo",
			"selectSwitch"=>"Invertir selección",
			"deleteElems"=>"Eliminar elementos",
			"changeFolder"=>"Mover a otro carpeta",
			"showOnMap"=>"Mostrar en el mapa",
			"showOnMapInfo"=>"Ver en un mapa los contactos con dirección, código postal, ciudad",
			"selectUser"=>"Gracias por seleccionar al menos un usuario",
			"selectUsers"=>"Gracias por seleccionar por lo menos dos usuarios",
			"selectSpace"=>"Gracias por elegir al menos un espacio",

			////	Temps ("de 11h à 12h", "le 25-01-2007 à 10h30", etc.)
			"from"=>"de",
			"at"=>"a",
			"the"=>"el",
			"begin"=>"inicio",
			"end"=>"Fin",
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
			"beginEndError"=>"La fecha de fin no puede ser anterior a la fecha de inicio",
			"dateFormatError"=>"La fecha debe estar en el formato dd/mm/AAAA",

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
			"record"=>"Guardar cambios",
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
			"SORT_name"=>"appelido",
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
			"confirmDelete"=>"¿ Confirmar eliminación ?",
			"confirmDeleteNbElems"=>"elementos seleccionados",//"55 éléments sélectionnés"
			"confirmDeleteDbl"=>"¿ Está seguro ?",
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
			"deletedUser"=>"cuenta de usuario eliminada",
			"folderContent"=>"contenido",
			"accessRead"=>"lectura",
			"accessReadInfo"=>"Acceso de lectura",
			"accessWriteLimit"=>"escritura limitada",
			"accessWriteLimitInfo"=>"Acceso de escritura limitado: posibilidad de agregar -OBJCONTENT- en el -OBJLABEL-,<br> pero cada usuario solo puede modificar/borrar los -OBJCONTENT- que ha creado.",
			"accessWrite"=>"escritura",
			"accessWriteInfo"=>"Acceso en escritura",
			"accessWriteInfoContainer"=>"Acceso en escritura : possibilidad de añadir, modificar o suprimir todos los -OBJCONTENT- del -OBJLABEL-",
			"accessAutorPrivilege"=>"Solo el autor y los administradores pueden cambiar los permisos de acceso o eliminar el -OBJLABEL-",
			"accessRightsInherited"=>"Derechos de acceso heredados del -OBJLABEL-",
			
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
			"MAIL_hello"=>"Hola",
			"MAIL_hideRecipients"=>"Ocultar destinatarios",
			"MAIL_hideRecipientsInfo"=>"Poner todos los destinatarios en copia oculta. Tenga en cuenta que con esta opción su correo electrónico puede llegar en spam en algunas mensajerías",
			"MAIL_addReplyTo"=>"pon mi correo en respuesta",
			"MAIL_addReplyToInfo"=>"Agregar mi correo electrónico en el campo ''Responder a''. Tenga en cuenta que con esta opción su correo electrónico puede llegar en spam en algunas mensajerías",
			"MAIL_noFooter"=>"No firme el mensaje",
			"MAIL_noFooterInfo"=>"No firme el final del mensaje con el nombre del remitentey un enlace al espacio",
			"MAIL_receptionNotif"=>"Confirmación de entrega",
			"MAIL_receptionNotifInfo"=>"Advertencia! algunos clientes de correo electrónico no soportan el recibo de entrega",
			"MAIL_specificMails"=>"Agregar direcciones de correo electrónico",
			"MAIL_specificMailsInfo"=>"Agregar direcciones de correo electrónico no enumeradas en el espacio",
			"MAIL_fileMaxSize"=>"Todos sus archivos adjuntos no deben exceder los 15 MB, algunos servicios de mensajería pueden rechazar correos electrónicos más allá de este límite. ¿Enviar de todos modos?",
			"MAIL_sendButton"=>"Enviar correo electrónico",
			"MAIL_sendBy"=>"Enviado por",//"Envoyé par" M. Trucmuche
			"MAIL_sendOk"=>"El correo electrónico ha sido enviado !",
			"MAIL_sendNotif"=>"El correo electrónico de notificación ha sido enviado !",
			"MAIL_notSend"=>"El correo electrónico no se pudo enviar",
			"MAIL_notSendEverybody"=>"El correo electrónico no se envió a todos los destinatarios: si es posible, verifique la validez de los correos electrónicos",
			"MAIL_fromTheSpace"=>"desde el espacio",//"depuis l'espace Bidule"
			"MAIL_elemCreatedBy"=>"-OBJLABEL- creado por",//boby
			"MAIL_elemModifiedBy"=>"-OBJLABEL- modificado por",//boby
			"MAIL_elemAccessLink"=>"Haga clic aquí para acceder al elemento en el espacio",

			////	Dossier & fichier
			"gigaOctet"=>"GB",
			"megaOctet"=>"MB",
			"kiloOctet"=>"KB",
			"rootFolder"=>"Carpeta raíz",
			"rootFolderEditInfo"=>"Abra la configuración del espacio<br>para cambiar los derechos de acceso a la carpeta raíz",
			"addFolder"=>"añadir un directorio",
			"download"=>"Descargar archivos",
			"downloadFolder"=>"Descargar la carpeta",
			"diskSpaceUsed"=>"Espacio utilizado",
			"diskSpaceUsedModFile"=>"Espacio utilizado para los Archivos",
			"downloadAlert"=>"Su archivo es demasiado grande para descargarlo durante el día (--ARCHIVE_SIZE--). Reinicie la descarga después de las",//"19h"
			
			////	Infos sur une personne
			"civility"=>"Civilidad",
			"name"=>"Appelido",
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
			"captchaInfo"=>"Por favor, escriba los 5 caracteres para su identificación",
			"captchaError"=>"La identificación visual no es valida",
			
			////	Rechercher
			"searchSpecifyText"=>"Por favor, especifique las palabras clave de al menos 3 caracteres",
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
			"userInscriptionInfo"=>"crear una nueva cuenta de usuario (validado por un administrador)",
			"userInscriptionSpace"=>"Registrarme al espacio",
			"userInscriptionRecorded"=>"Su registro será validado tan pronto como sea posible por el administrador del espacio",
			"userInscriptionNotifSubject"=>"Nuevo registro en el espacio",//"Mon espace"
			"userInscriptionNotifMessage"=>"<i>--NEW_USER_LABEL--</i> ha solicitado un nuevo registro para el espacio <i>--SPACE_NAME--</i> : <br><br><i>--NEW_USER_MESSAGE--<i> <br><br>Recuerde validar o invalidar este registro durante su próxima conexión.",
			"userInscriptionEdit"=>"Permitir a los visitantes que se registren en el espacio",
			"userInscriptionEditInfo"=>"El registro se encuentra en la página de inicio. Debe ser validado por el administrador del espacio.",
			"userInscriptionNotifyEdit"=>"Notificar por correo electrónico en cada registro",
			"userInscriptionNotifyEditInfo"=>"Envíe una notificación por correo electrónico a los administradores del espacio, después de cada registro",
			"userInscriptionPulsate"=>"Registros",
			"userInscriptionValidate"=>"Registros de usuarios",
			"userInscriptionValidateInfo"=>"Validar registros de usuarios al espacio",
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
			"NOTIF_presentIp"=>"Esta cuenta de usuario se está utilizando actualmente desde otra computadora, con otra dirección IP. Una cuenta solo se puede usar en un computadora al mismo tiempo.",
			"NOTIF_noSpaceAccess"=>"Su cuenta de usuario se ha identificado correctamente, pero actualmente no está asignado a ningún espacio. Por favor contacte al administrador",
			"NOTIF_noAccess"=>"No estas logueado",
			"NOTIF_fileOrFolderAccess"=>"El archivo o la carpeta no está disponible",
			"NOTIF_diskSpace"=>"El espacio para almacenar sus archivos no es suficiente, no se puede añadir archivos",
			"NOTIF_fileVersionForbidden"=>"Tipo de archivo no permitido",
			"NOTIF_fileVersion"=>"Tipo de archivo diferente del original",
			"NOTIF_folderMove"=>"No se puede mover la carpeta dentro de sí mismo..!",
			"NOTIF_duplicateName"=>"Un archivo o carpeta con el mismo nombre ya existe",
			"NOTIF_fileName"=>"Un archivo con el mismo nombre ya existe (no ha sido reemplazado)",
			"NOTIF_chmodDATAS"=>"El directorio ''DATAS'' no es accesible por escrito. Usted necesita dar un acceso de lectura y escritura para el propietario y el grupo (''chmod 775'').",
			"NOTIF_usersNb"=>"No se puede añadir un nuevo usuario : se limita a ", // "...limité à" 10
			
			////	Header / Footer
			"HEADER_displaySpace"=>"Espacios disponibles",
			"HEADER_displayAdmin"=>"Visualización de Administrador",
			"HEADER_displayAdminEnabled"=>"Visualización de Administrador activada",
			"HEADER_displayAdminInfo"=>"Mostrar todos los elementos del espacio (solo para los administradores)",
			"HEADER_searchElem"=>"Buscar en el espacio",
			"HEADER_documentation"=>"Documentación",
			"HEADER_disconnect"=>"Cerrar sesión del Ágora",
			"HEADER_shortcuts"=>"Acceso directo",
			"FOOTER_pageGenerated"=>"página generada en",

			////	Messenger / Visio
			"MESSENGER_headerModuleName"=>"Mensajes",
			"MESSENGER_moduleDescription"=>"Mensajería instantánea: Chatea en vivo o inicia una videoconferencia con las personas conectadas al espacio",
			"MESSENGER_messengerTitle"=>"Mensajería instantánea : haga clic en el nombre de una persona para chatear o iniciar una videoconferencia",
			"MESSENGER_messengerMultiUsers"=>"Chatear con otros seleccionando mis interlocutores en el panel derecho",
			"MESSENGER_connected"=>"Conectado",
			"MESSENGER_nobody"=>"Actualmente eres el único usuario que inició sesión en el espacio.<br> Nota: sus conversaciones anteriores se guardan durante 30 días",
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
			"VISIO_urlAdd"=>"Agregar una videoconferencia",
			"VISIO_urlCopy"=>"Copia el enlace de la videoconferencia",
			"VISIO_urlDelete"=>"Eliminar el enlace de la videoconferencia",
			"VISIO_launch"=>"Iniciar la videollamada",
			"VISIO_launchFromEvent"=>"Iniciar la videoconferencia de este evento",
			"VISIO_urlMail"=>"Agregue un enlace al final del texto para comenzar una nueva videoconferencia",
			"VISIO_launchInfo"=>"Recuerde permitir el acceso a su cámara web y micrófono",
			"VISIO_launchHelp"=>"Haga clic aquí si tiene problemas para iniciar la videoconferencia",
			"VISIO_installJitsi"=>"Instale la aplicación gratuita Jitsi para iniciar sus videoconferencias",
			"VISIO_launchServerInfo"=>"Elija el servidor secundario si el servidor principal no funciona correctamente.<br>Tus contactos deberán seleccionar el mismo servidor de video.",
			"VISIO_launchServerMain"=>"Servidor principal",
			"VISIO_launchServerAlt"=>"Servidor secundario",
			"VISIO_launchButton"=>"Iniciar la videollamada",

			////	vueObjMenuEdit
			"EDIT_notifNoSelection"=>"Debe seleccionar al menos una persona o un espacio",
			"EDIT_notifNoPersoAccess"=>"Usted no se ha asignado al elemento. validar todos lo mismo ?",
			"EDIT_notifWriteAccess"=>"Debe haber al menos una persona o un espacio asignado para escribir",
			"EDIT_parentFolderAccessError"=>"Recuerde verificar los derechos de acceso de la carpeta superior ''<i>--FOLDER_NAME--</i>'': si no está también asignada a ''<i>--TARGET_LABEL--</i>'', el archivo actual no será accesible para el.",
			"EDIT_accessRight"=>"Derechos de acceso",
			"EDIT_accessRightContent"=>"Derechos de acceso al contenido",
			"EDIT_spaceNoModule"=>"El módulo actual aún no se ha añadido a este espacio",
			"EDIT_allUsers"=>"Todos los usuarios",
			"EDIT_allUsersAndGuests"=>"Todos los usuarios y invitados",
			"EDIT_allUsersInfo"=>"Todos los usuarios del espacio <i>--SPACENAME--</i>",
			"EDIT_allUsersAndGuestsInfo"=>"Todos los usuarios del espacio <i>--SPACENAME--</i>, y los invitados pero con acceso solo de lectura (invitados: personas que no tienen una cuenta de usuario)",
			"EDIT_adminSpace"=>"Administrador del espacio:<br>acceso de escritura a todos los elementos del espacio",
			"EDIT_showAllUsers"=>"Mostrar todos los usuarios",
			"EDIT_showAllUsersAndSpaces"=>"Mostrar todos los usuarios y espacios",
			"EDIT_notifMail"=>"Notificar",
			"EDIT_notifMail2"=>"Enviar una notificación de creación/cambio por email",
			"EDIT_notifMailInfo"=>"La notificación se enviará a las personas asignadas al elemento (-OBJLABEL-)",
			"EDIT_notifMailInfoCal"=>"<hr>Si asigna el evento a calendarios personales, la notificación solo se enviará a los propietarios de estos calendarios (acceso de escritura).",
			"EDIT_notifMailAddFiles"=>"Adjuntar archivos a la notificación",
			"EDIT_notifMailSelect"=>"Seleccionar los destinatarios de las notificaciones",
			"EDIT_accessRightSubFolders"=>"Dar igualdad de derechos a todos los sub-carpetas",
			"EDIT_accessRightSubFolders_info"=>"Extender los derechos de acceso, a los sub-carpetas que se pueden editar",
			"EDIT_shortcut"=>"Acceso directo",
			"EDIT_shortcutInfo"=>"Mostrar un acceso directo en el menú principal",
			"EDIT_attachedFile"=>"Adjuntos archivos",
			"EDIT_attachedFileAdd"=>"Añadir archivos",
			"EDIT_attachedFileInsert"=>"Insertar en texto",
			"EDIT_attachedFileInsertInfo"=>"Insertar imagen/video en el texto del editor (formato jpg, png o mp4)",
			"EDIT_guestName"=>"Su nombre / apodo",
			"EDIT_guestNameNotif"=>"Por favor, especifique un nombre / apodo",
			"EDIT_guestMail"=>"Su email",
			"EDIT_guestMailInfo"=>"Por favor especifique su correo electrónico para la validación de su propuesta",
			"EDIT_guestElementRegistered"=>"Gracias por su propuesta : será examinada lo antes posible antes de la validación",
			
			////	Formulaire d'installation
			"INSTALL_dbConnect"=>"Conexión a la base de datos",
			"INSTALL_dbHost"=>"Nombre del servidor host (hostname)",
			"INSTALL_dbName"=>"Nombre de la base de datos",
			"INSTALL_dbLogin"=>"Nombre de Usuario",
			"INSTALL_adminAgora"=>"Administrador del Ágora",
			"INSTALL_dbErrorDbName"=>"Advertencia: el nombre de la base de datos debe contener solo caracteres alfanuméricos y guiones o guiones bajos",
			"INSTALL_dbErrorUnknown"=>"No hay conexión con la base de datos MariaDB/MySQL",
			"INSTALL_dbErrorIdentification"=>"No identificación con la base de datos MariaDB/MySQL",
			"INSTALL_dbErrorAppInstalled"=>"La instalación ya se ha realizado en esta base de datos. Gracias simplemente eliminar la base de datos si se debe reiniciar la instalación.",
			"INSTALL_PhpOldVersion"=>"Agora-Project requiere una versión más reciente de PHP",
			"INSTALL_confirmInstall"=>"¿ Confirmar instalación ?",
			"INSTALL_installOk"=>"Agora-Project ha sido instalado !",
			"INSTALL_spaceDescription"=>"Espacio para el intercambio y el trabajo colaborativo",
			"INSTALL_dataDashboardNews"=>"<h3>¡Bienvenido a tu nuevo espacio para compartir!</h3>
													<h4><img src='app/img/file/iconSmall.png'> Comparta sus archivos ahora en el administrador de archivos</h4>
													<h4><img src='app/img/calendar/iconSmall.png'> Comparta sus calendarios comunes o su calendario personal</h4>
													<h4><img src='app/img/dashboard/iconSmall.png'> Amplíe el suministro de noticias de su comunidad</h4>
													<h4><img src='app/img/messenger.png'> Comunicarse a través del foro, mensajería instantánea o videoconferencias</h4>
													<h4><img src='app/img/task/iconSmall.png'> Centraliza tus notas, proyectos y contactos</h4>
													<h4><img src='app/img/mail/iconSmall.png'> Enviar boletines por correo electrónico</h4>
													<h4><img src='app/img/postMessage.png'> <a href=\"javascript:lightboxOpen('?ctrl=user&action=SendInvitation')\">¡Haga clic aquí para enviar correos electrónicos de invitación y hacer crecer su comunidad!</a></h4>
													<h4><img src='app/img/pdf.png'> <a href='https://www.omnispace.fr/index.php?ctrl=offline&action=Documentation' target='_blank'>Para obtener más información, consulte la documentación oficial de Omnispace & Agora-Project</a></h4>",
			"INSTALL_dataDashboardPoll"=>"¿Qué opinas de la herramienta de noticias?",
			"INSTALL_dataDashboardPollA"=>"Muy interesante !",
			"INSTALL_dataDashboardPollB"=>"Interesante",
			"INSTALL_dataDashboardPollC"=>"Sin interés",
			"INSTALL_dataCalendarEvt"=>"Bienvenido a Omnispace",
			"INSTALL_dataForumSubject1"=>"Bienvenido al foro de Omnispace",
			"INSTALL_dataForumSubject2"=>"Siéntase libre de compartir sus preguntas o discutir los temas que deseas.",

			////	MODULE_PARAMETRAGE
			////
			"AGORA_generalSettings"=>"Administración general",
			"AGORA_versions"=>"Versiones",
			"AGORA_dateUpdate"=>"actualización el",
			"AGORA_Changelog"=>"Ver el registro de versión",
			"AGORA_funcMailDisabled"=>"La función PHP para enviar correos electrónicos está deshabilitada.",
			"AGORA_funcImgDisabled"=>"La biblioteca PHP GD2 para la manipulación de imágenes está deshabilitada",
			"AGORA_backupFull"=>"Copia de seguridad completa",
			"AGORA_backupFullInfo"=>"Recupere la copia de seguridad completa del espacio: todos los archivos y la base de datos",
			"AGORA_backupDb"=>"Hacer una copia de seguridad de la base de datos",
			"AGORA_backupDbInfo"=>"Recupere solo la copia de seguridad de la base de datos espacial",
			"AGORA_backupConfirm"=>"Esta operación puede tardar varios minutos: ¿confirmar la descarga?",
			"AGORA_diskSpaceInvalid"=>"El espacio en disco para los archivos debe ser un número entero",
			"AGORA_visioHostInvalid"=>"La dirección web de su servidor de videoconferencia no es válida: debe comenzar con 'https'",
			"AGORA_mapApiKeyInvalid"=>"Si elige Google Map como herramienta de mapeo, debe especificar una 'API Key'",
			"AGORA_gIdentityKeyInvalid"=>"Si elige la conexión opcional a través de Google, debe especificar una 'API Key' para Google SignIn",
			"AGORA_confirmModif"=>"Confirmar los cambios ?",
			"AGORA_name"=>"Nombre del sitio",
			"AGORA_footerHtml"=>"Footer / Pie de página texto/html",
			"AGORA_lang"=>"Lenguaje por defecto",
			"AGORA_timezone"=>"Zona horaria",
			"AGORA_spaceName"=>"Nombre del espacio principal",
			"AGORA_diskSpaceLimit"=>"Espacio de disco disponible para los archivos",
			"AGORA_logsTimeOut"=>"Duración del historial de eventos (registros)",
			"AGORA_logsTimeOutInfo"=>"El período de retención del historial de eventos se refiere a la adición o modificación de los elementos. Los registros de eliminación se mantienen durante al menos 1 año.",
			"AGORA_visioHost"=>"Servidor de videoconferencia Jitsi",
			"AGORA_visioHostInfo"=>"Dirección del servidor de videoconferencia Jitsi. Ejemplo: https://meet.jit.si",
			"AGORA_visioHostAlt"=>"Servidor de videoconferencia alternativo",
			"AGORA_visioHostAltInfo"=>"Servidor de videoconferencia alternativo : en caso de indisponibilidad del servidor de video principal",
			"AGORA_skin"=>"Color de la interfaz",
			"AGORA_black"=>"Negro",
			"AGORA_white"=>"Blanco",
			"AGORA_wallpaperLogoError"=>"La imagen de fondo y el logotipo debe tener el formato jpg o png",
			"AGORA_deleteWallpaper"=>"Eliminar la imagen de fondo",
			"AGORA_logo"=>"Logotipo en pie de página",
			"AGORA_logoUrl"=>"URL",
			"AGORA_logoConnect"=>"logo / Imagen de la página de conexión",
			"AGORA_logoConnectInfo"=>"Desplegado encima del formulario de conexión",
			"AGORA_usersCommentLabel"=>"Permitir a los usuarios hacer comentarios sobre el elemento",
			"AGORA_usersComment"=>"comentario",
			"AGORA_usersComments"=>"comentarios",
			"AGORA_usersLikeLabel"=>"Los usuarios pueden <i>Aprobar</i> el elemento",
			"AGORA_usersLike_likeSimple"=>"Solo Me gusta",
			"AGORA_usersLike_likeOrNot"=>"Me gusta / No me gusta",
			"AGORA_usersLike_like"=>"Me gusta!",
			"AGORA_usersLike_dontlike"=>"No me gusta",
			"AGORA_mapTool"=>"Herramienta de mapeo",
			"AGORA_mapToolInfo"=>"Herramienta de mapeo para ver usuarios y contactos en un mapa",
			"AGORA_mapApiKey"=>"'API Key' para herramienta de mapeo",
			"AGORA_mapApiKeyInfo"=>"API Key para la herramienta de mapeo Google Map",
			"AGORA_gIdentity"=>"Conexión opcional con Google",
			"AGORA_gIdentityInfo"=>"Los usuarios pueden conectarse más fácilmente a su espacio a través de su cuenta de Google : para eso, un correo electrónico <i>@gmail.com</ i> ya debe estar registrado en la cuenta del usuario",
			"AGORA_gIdentityClientId"=>"Configuración de Sign-In : Client ID",
			"AGORA_gIdentityClientIdInfo"=>"Esta configuración es necesaria para Google Sign-In : https://developers.google.com/identity/sign-in/web/",
			"AGORA_gPeopleApiKey"=>"Configuración de Google People : API KEY",
			"AGORA_gPeopleApiKeyInfo"=>"Esta configuración es necesaria para recuperar contactos de Google / Gmail : <a href='https://developers.google.com/people/' target='_blank'>https://developers.google.com/people/</a>",
			"AGORA_messengerDisabled"=>"Mensajería instantánea activada",
			"AGORA_moduleLabelDisplay"=>"Nombre de los módulos en la barra de menú",
			"AGORA_folderDisplayMode"=>"Visualización en las carpetas",
			"AGORA_personsSort"=>"Ordenar los usuarios y contactos",
			//SMTP
			"AGORA_smtpLabel"=>"Conexión SMTP & sendMail",
			"AGORA_sendmailFrom"=>"Email 'From'",
			"AGORA_sendmailFromPlaceholder"=>"eg: 'noreply@mydomain.com'",
			"AGORA_smtpHost"=>"Dirección del servidor (hostname)",
			"AGORA_smtpPort"=>"Puerto de servidor",
			"AGORA_smtpPortInfo"=>"'25' por defecto. '587' o '465' para SSL/TLS",
			"AGORA_smtpSecure"=>"Tipo de conexión cifrada (opcional)",
			"AGORA_smtpSecureInfo"=>"'ssl' o 'tls'",
			"AGORA_smtpUsername"=>"Nombre del usuario",
			"AGORA_smtpPass"=>"Contraseña",
			//LDAP
			"AGORA_ldapLabel"=>"Conexión a un servidor LDAP",
			"AGORA_ldapLabelInfo"=>"Conexión a un servidor LDAP para la creación de usuarios en el espacio : cf. Opción ''Importación/exportación de usuarios'' del módulo ''Usuario''",
			"AGORA_ldapUri"=>"URI LDAP",
			"AGORA_ldapUriInfo"=>"URI de LDAP completo con el formato LDAP://hostname:port o LDAPS://hostname:port para el cifrado SSL.",
			"AGORA_ldapPort"=>"Puerto del servidor",
			"AGORA_ldapPortInfo"=>"El puerto utilizado para la conexión: '' 389 '' por defecto",
			"AGORA_ldapLogin"=>"DN del administrador LDAP (Distinguished Name)",
			"AGORA_ldapLoginInfo"=>"por ejemplo ''cn=admin,dc=mon-entreprise,dc=com''",
			"AGORA_ldapPass"=>"Contraseña del administrador",
			"AGORA_ldapDn"=>"DN del grupo de usuarios (Distinguished Name)",
			"AGORA_ldapDnInfo"=>"DN del grupo de usuarios : ubicación de los usuarios en el directorio. Ejemplo ''ou=mon-groupe,dc=mon-entreprise,dc=com''",
			"importLdapFilterInfo"=>"Filtro de búsqueda LDAP (cf. https://www.php.net/manual/function.ldap-search.php). Ejemplo ''(cn=*)'' o ''(&(samaccountname=MONLOGIN)(cn=*))''",
			"AGORA_ldapDisabled"=>"El módulo PHP para conectarse a un servidor LDAP no está instalado",
			"AGORA_ldapConnectError"=>"Error de conexión del servidor LDAP !",

			////	MODULE_LOG
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

			////	MODULE_ESPACE
			////
			"SPACE_moduleInfo"=>"El sitio (o el espacio principal) puede ser subdivisado en varios espacios",
			"SPACE_manageSpaces"=>"Gestión de los spacios del sitio",
			"SPACE_config"=>"Administración del espacio",
			// Index
			"SPACE_confirmDeleteDbl"=>"Confirmar eliminación ? Atención, los datos afectados a este espacio seran  definitivamente perdidas !!",
			"SPACE_space"=>"Espacio",
			"SPACE_spaces"=>"Espacios",
			"SPACE_accessRightUndefined"=>"Definir !",
			"SPACE_modules"=>"Módulos",
			"SPACE_addSpace"=>"Añadir un espacio",
			//Edit
			"SPACE_usersAccess"=>"Usuarios asignados al espacio",
			"SPACE_selectModule"=>"Debe seleccionar al menos un módulo",
			"SPACE_spaceModules"=>"Módulos del espacio",
			"SPACE_moduleRank"=>"Mover a establecer el orden de presentación de los módulos",
			"SPACE_publicSpace"=>"Espacio público : acceso invitado",
			"SPACE_publicSpaceInfo"=>"Un espacio público está abierto a las personas que no tienen una cuenta de usuario: los 'invitados'. Puede especificar una contraseña genérica para proteger el acceso a este espacio pública. Los siguientes módulos serán inaccesibles para los invitados : 'mail' y 'user' (si el espacio público no tiene una contraseña)",
			"SPACE_publicSpaceNotif"=>"Si el espacio público contiene datos confidenciales, como información de contacto personal (módulo de Contacto) o documentos (módulo de Archivo): debe agregar una contraseña de acceso para cumplir con el GDPR. <Hr> El Reglamento general de protección de datos es un reglamento de la Unión Europea que constituye el texto de referencia para la protección de datos personales.",
			"SPACE_usersInvitation"=>"Los usuarios pueden enviar invitaciones por correo",
			"SPACE_usersInvitationInfo"=>"Todos los usuarios pueden enviar invitaciones por correo electrónico para unirse al espacio",
			"SPACE_allUsers"=>"Todos los usuarios",
			"SPACE_user"=>" Usuarios",
			"SPACE_userInfo"=>"Usuario del espacio : <br> Acceso normal al espacio",
			"SPACE_admin"=>"Administrador",
			"SPACE_adminInfo"=>"Administrador del espacio : <br>-ecceso en escritura a todos los elementos del espacio <br>- posibilidad de enviar invitaciones por correo electrónico <br>- añadir nuevos usuarios <br>- configuración del espacio",

			////	MODULE_UTILISATEUR
			////
			// Menu principal
			"USER_headerModuleName"=>"Usuarios",
			"USER_moduleDescription"=>"Usuarios del espacio",
			"USER_option_allUsersAddGroup"=>"Los usuarios también pueden crear grupos",
			//Index
			"USER_allUsers"=>"Ver todos los usuarios",
			"USER_allUsersInfo"=>"Todos los usuarios de todos los espacios",
			"USER_spaceUsers"=>"Usuarios del espacio corriente",
			"USER_deleteDefinitely"=>"Eliminar definitivamente",
			"USER_deleteFromCurSpace"=>"Desasignar del espacio corriente",
			"USER_deleteFromCurSpaceConfirm"=>"¿ Confirmar la desasignación del usuario al espacio corriente ?",
			"USER_allUsersOnSpaceNotif"=>"Todo los usuarios son asignados a este espacio",
			"USER_user"=>"Usuario",
			"USER_users"=>"usuarios",
			"USER_addExistUser"=>"Añadir un usuario existente, a ese espacio",
			"USER_addExistUserTitle"=>"Añadir al espacio a un usuario ya existente en el sitio : asignación al espacio",
			"USER_addUser"=>"Añadir un usuario",
			"USER_addUserSite"=>"Crear un usuario en el sitio : por defecto, asignado a ningun espacio !",
			"USER_addUserSpace"=>"Crear un usuario en el espacio actual",
			"USER_sendCoords"=>"Enviar el nombre de usuario y contraseña",
			"USER_sendCoordsInfo"=>"Envíe a los usuarios un correo electrónico con su Login y un enlace web para inicializar su contraseña",
			"USER_sendCoordsInfo2"=>"Enviar a cada nuevo usuario un correo electrónico con información de acceso.",
			"USER_sendCoordsConfirm"=>"¿ Confirmar ?",
			"USER_sendCoordsMail"=>"Sus datos de acceso a su espacio",
			"USER_noUser"=>"Ningún usuario asignado a este espacio por el momento",
			"USER_spaceList"=>"Espacios del usuario",
			"USER_spaceNoAffectation"=>"Ningún espacio",
			"USER_adminGeneral"=>"Administrador General del Sitio",
			"USER_adminGeneralInfo"=>"El administrador general puede gestionar todas las configuraciones del sitio, todos los usuarios, espacios y elementos. Tiene control total sobre la configuración y los elementos del espacio: por lo tanto, es recomendable asignar este privilegio a dos o tres usuarios máximo.",
			"USER_adminSpace"=>"Administrador del espacio",
			"USER_userSpace"=>"Usuario del espacio",
			"USER_profilEdit"=>"Editar el perfil",
			"USER_myProfilEdit"=>"Editar mi perfil de usuario",
			// Invitation
			"USER_sendInvitation"=>"Enviar invitaciones por email",
			"USER_sendInvitationInfo"=>"Envía invitaciones por correo electrónico a tus contactos para unirte al espacio.<hr><img src='app/img/google.png' height=15> Si tienes una cuenta Gmail, también puedes enviar invitaciones a tus contactos Gmail.",
			"USER_mailInvitationObject"=>"Invitación de ", // ..Jean DUPOND
			"USER_mailInvitationFromSpace"=>"le invita a ", // Jean DUPOND "vous invite à rejoindre l'espace" Mon Espace
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
			// Utilisateur_edit & CO
			"USER_langs"=>"Idioma",
			"USER_persoCalendarDisabled"=>"Calendario personal desactivado",
			"USER_persoCalendarDisabledInfo"=>"Se asigna un calendario personal por defecto a cada usuario (incluso si el módulo ''Calendario'' no está activado en el espacio). Marque esta opción para deshabilitar el calendario personal de este usuario.",
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

			////	MODULE_TABLEAU BORD
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
			"DASHBOARD_vote"=>"Votar y ver los resultados !",
			"DASHBOARD_voteTooltip"=>"Los votos son anónimos : nadie sabrá su elección de voto",
			"DASHBOARD_answerVotesNb"=>"Votada --NB_VOTES-- veces",//55 votes (sur la réponse)
			"DASHBOARD_pollVotesNb"=>"La encuesta fue votada --NB_VOTES-- veces",
			"DASHBOARD_pollVotedBy"=>"Votada por",//Bibi, boby, etc
			"DASHBOARD_noPoll"=>"No hay encuesta por el momento",
			"DASHBOARD_plugins"=>"Nuevos elementos",
			"DASHBOARD_pluginsInfo"=>"Elementos creados",
			"DASHBOARD_pluginsInfo2"=>"entre",
			"DASHBOARD_plugins_day"=>"de hoy",
			"DASHBOARD_plugins_week"=>"de esta semana",
			"DASHBOARD_plugins_month"=>"del mes",
			"DASHBOARD_plugins_previousConnection"=>"desde la última conexión",
			"DASHBOARD_pluginsTooltipRedir"=>"Ver el elemento en la carpeta",
			"DASHBOARD_pluginEmpty"=>"No hay nuevos elementos sobre este periodo",
			// Actualite/News
			"DASHBOARD_topNews"=>"Noticia importante",
			"DASHBOARD_topNewsInfo"=>"Noticia importante, en la parte superior de la lista",
			"DASHBOARD_offline"=>"Noticia archivada",
			"DASHBOARD_dateOnline"=>"En línea el",
			"DASHBOARD_dateOnlineInfo"=>"Establecer una fecha de línea automático (en línea). La noticia será 'archivada' 'en el ínterin",
			"DASHBOARD_dateOnlineNotif"=>"La noticia esta archivado en la expectativa de su línea automática",
			"DASHBOARD_dateOffline"=>"Archivar el",
			"DASHBOARD_dateOfflineInfo"=>"Fije una fecha de archivo automático (Desconectado)",
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

			////	MODULE_AGENDA
			////
			// Menu principal
			"CALENDAR_headerModuleName"=>"Calendarios",
			"CALENDAR_moduleDescription"=>"Calendarios personal y calendarios compartidos",
			"CALENDAR_option_adminAddRessourceCalendar"=>"Sólo el administrador puede añadir calendarios de recursos",
			"CALENDAR_option_adminAddCategory"=>"Sólo el administrador puede añadir categorías de eventos",
			"CALENDAR_option_createSpaceCalendar"=>"Crear un calendario compartido para el espacio",
			"CALENDAR_option_createSpaceCalendarInfo"=>"El calendario tendrá el mismo nombre que el espacio. Puede ser útil si los calendarios de los usuarios están desactivados.",
			"CALENDAR_option_moduleDisabled"=>"Los usuarios que no hayan desactivado su calendario personal en su perfil de usuario seguirán viendo el módulo Calendario en la barra de menú.",
			//Index
			"CALENDAR_calsList"=>"Calendarios disponibles",
			"CALENDAR_displayAllCals"=>"Ver todo los calendarios (administrador)",
			"CALENDAR_hideAllCals"=>"Ocultar todo los calendarios",
			"CALENDAR_printCalendars"=>"Imprimir el/los calendarios",
			"CALENDAR_printCalendarsInfos"=>"imprimir la página en modo horizontal",
			"CALENDAR_addSharedCalendar"=>"Añadir un calendario compartido",
			"CALENDAR_addSharedCalendarInfo"=>"Añadir un calendario compartido : para reservar une habitación, vehiculo, vídeo, etc.",
			"CALENDAR_exportIcal"=>"Exportar los eventos (iCal)",
			"CALENDAR_icalUrl"=>"Copie el enlace/url para ver el calendario en una aplicación externa",
			"CALENDAR_icalUrlCopy"=>"Permite la lectura de los eventos del calendario en formato Ical, a través de una aplicación externa como Microsoft Outlook, Google Calendar, Mozilla Thunderbird, etc.",
			"CALENDAR_importIcal"=>"Importar los eventos (iCal)",
			"CALENDAR_ignoreOldEvt"=>"No importe eventos de más de un año",
			"CALENDAR_importIcalState"=>"Estado",
			"CALENDAR_importIcalStatePresent"=>"Ya está presente",
			"CALENDAR_importIcalStateImport"=>"a importar",
			"CALENDAR_displayMode"=>"Visualización",
			"CALENDAR_display_day"=>"Día",
			"CALENDAR_display_4Days"=>"4 días",
			"CALENDAR_display_workWeek"=>"Semana de trabajo",
			"CALENDAR_display_week"=>"Semana",
			"CALENDAR_display_month"=>"Mes",
			"CALENDAR_weekNb"=>"Ver la semana n°", //...5
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
			"CALENDAR_noEvt"=>"No hay eventos",
			"CALENDAR_synthese"=>"Síntesis de los calendarios",
			"CALENDAR_calendarsPercentBusy"=>"Calendarios ocupados",  // Agendas occupés : 2/5
			"CALENDAR_noCalendarDisplayed"=>"No calendario",
			// Evenement
			"CALENDAR_category"=>"Categoría",
			"CALENDAR_importanceNormal"=>"Importancia normal",
			"CALENDAR_importanceHight"=>"Alta importancia",
			"CALENDAR_visibilityPublic"=>"Visibilidad normal",
			"CALENDAR_visibilityPrivate"=>"Visibilidad privada",
			"CALENDAR_visibilityPublicHide"=>"Visibilidad semi-privada",
			"CALENDAR_visibilityInfo"=>"<u>Visibilidad privada</u> : evento visible sólo si el evento es accesible en escritura <br><br> <u>Visibilidad semi-privada</u> : solo muestrar el período del evento (sin los detailles) si el evento es accesible de lectura",
			// Agenda/Evenement : edit
			"CALENDAR_noPeriodicity"=>"Una vez",
			"CALENDAR_period_weekDay"=>"Cada semana",
			"CALENDAR_period_month"=>"Cada mes",
			"CALENDAR_period_year"=>"Cada año",
			"CALENDAR_periodDateEnd"=>"Fin de periodicidad",
			"CALENDAR_periodException"=>"Excepción de periodicidad",
			"CALENDAR_calendarAffectations"=>"Asignación a los calendarios",
			"CALENDAR_addEvt"=>"Añadir un evento",
			"CALENDAR_addEvtTooltip"=>"Añadir un evento",
			"CALENDAR_addEvtTooltipBis"=>"Añadir el evento al calendario",
			"CALENDAR_proposeEvtTooltip"=>"Proponer un evento al propietario del calendario",
			"CALENDAR_proposeEvtTooltipBis"=>"Proponer el evento al propietario del calendario",
			"CALENDAR_proposeEvtTooltipBis2"=>"Proponer el evento al propietario del calendario : calendario accesible solo en lectura",
			"CALENDAR_inputProposed"=>"El evento será propuesto al propietario del calendario",
			"CALENDAR_verifCalNb"=>"Gracias por seleccionar por lo menos un calendario",
			"CALENDAR_noModifInfo"=>"Edición prohibida porque no tiene acceso de escritura al calendario",
			"CALENDAR_editLimit"=>"Usted no es el autor de el evento : sólo puedes editar las asignaciones a sus calendarios",
			"CALENDAR_busyTimeslot"=>"La ranura ya está ocupado en este calendario :",
			"CALENDAR_timeSlot"=>"Rango de tiempo de la pantalla ''semana''",
			"CALENDAR_propositionNotify"=>"Notificar por correo electrónico de cada propuesta de evento",
			"CALENDAR_propositionNotifyInfo"=>"Nota: Cada propuesta de evento es validada o invalidada por el propietario del calendario.",
			"CALENDAR_propositionGuest"=>"Los invitados pueden proponer eventos",
			"CALENDAR_propositionGuestInfo"=>"Nota: Recuerde seleccionar 'todos los usuarios e invitados' en los derechos de acceso.",
			"CALENDAR_propositionNotifTitle"=>"Nuevo evento propuesto por",//.."boby SMITH"
			"CALENDAR_propositionNotifMessage"=>"Nuevo evento propuesto por --AUTOR_LABEL-- : &nbsp; <i><b>--EVT_TITLE_DATE--</b></i> <br><i>--EVT_DESCRIPTION--</i> <br>Accede a tu espacio para confirmar o cancelar esta propuesta",
			// Categories
			"CALENDAR_editCategories"=>"Administrar las categorías de eventos",
			"CALENDAR_editCategoriesRight"=>"Cada categoría puede ser modificado por su autor o por el administrador general",
			"CALENDAR_addCategory"=>"Añadir una categoría",
			"CALENDAR_filterByCategory"=>"Ver los eventos por categoría",

			////	MODULE_FICHIER
			////
			// Menu principal
			"FILE_headerModuleName"=>"Archivos",
			"FILE_moduleDescription"=>"Administración de Archivos",
			"FILE_option_adminRootAddContent"=>"Sólo el administrador puede añadir elementos en el directorio raíz",
			//Index
			"FILE_addFile"=>"Añadir archivos",
			"FILE_addFileAlert"=>"Los directorios del servidor no son accesible en escritura !  gracias de contactar el administrador",
			"FILE_downloadSelection"=>"Descargar selección",
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
			"FILE_addMultipleFilesInfo"=>"Pulse 'Maj' o 'Ctrl' para seleccionar varios archivos",
			"FILE_selectFile"=>"Gracias por elegir al menos un archivo",
			"FILE_fileContent"=>"contenido",
			// Versions_fichier
			"FILE_versionsOf"=>"Versiones de", // versions de fichier.gif
			"FILE_confirmDeleteVersion"=>"¿ Confirme la eliminación de esta versión ?",

			////	MODULE_FORUM
			////
			// Menu principal
			"FORUM_headerModuleName"=>"Foro",
			"FORUM_moduleDescription"=>"Foro",
			"FORUM_option_adminAddSubject"=>"Sólo el administrador puede añadir sujetos",
			"FORUM_option_allUsersAddTheme"=>"Los usuarios también pueden añadir temas",
			// TRI
			"SORT_dateLastMessage"=>"último mensaje",
			//Index & Sujet
			"FORUM_subject"=>"sujeto",
			"FORUM_subjects"=>"sujetos",
			"FORUM_message"=>"mensaje",
			"FORUM_messages"=>"mensajes",
			"FORUM_lastSubject"=>"último sujetos de",
			"FORUM_lastMessage"=>"último mensaje de",
			"FORUM_noSubject"=>"Sin sujeto por el momento",
			"FORUM_noMessage"=>"Sin mensaje por el momento",
			"FORUM_subjectBy"=>"sujeto de",
			"FORUM_addSubject"=>"Nuevo sujeto",
			"FORUM_displaySubject"=>"Ver el sujeto",
			"FORUM_addMessage"=>"Responder",
			"FORUM_quoteMessage"=>"Responder y citar a ese mensaje",
			"FORUM_notifyLastPost"=>"Notificar por e-mail",
			"FORUM_notifyLastPostInfo"=>"Deseo recibir una notificación por correo a cada nuevo mensaje",
			// Sujet_edit  &  Message_edit
			"FORUM_accessRightInfos"=>"Atención: el acceso a la lectura no permite participar en la discusión. Por lo tanto, prefiero el acceso de escritura limitado. El acceso de escritura debe reservarse para moderadores",
			"FORUM_themeSpaceAccessInfo"=>"El tema está disponible en los espacios",
			// Themes
			"FORUM_subjectTheme"=>"Tema",
			"FORUM_subjectThemes"=>"Temas",
			"FORUM_forumRoot"=>"Inicio del foro",
			"FORUM_forumRootResp"=>"Inicio",
			"FORUM_noTheme"=>"Sin tema",
			"FORUM_editThemes"=>"Gestión de los temas",
			"FORUM_editThemesInfo"=>"Cada tema puede ser modificado por su autor o por el administrador general",
			"FORUM_addTheme"=>"Añadir un tema",

			////	MODULE_TACHE
			////
			// Menu principal
			"TASK_headerModuleName"=>"Tareas",
			"TASK_moduleDescription"=>"Tareas",
			"TASK_option_adminRootAddContent"=>"Sólo el administrador puede añadir elementos en el directorio raíz",
			// TRI
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
			"TASK_priority1"=>"Baja",
			"TASK_priority2"=>"promedia",
			"TASK_priority3"=>"alta",
			"TASK_priority4"=>"Crítica",
			"TASK_responsiblePersons"=>"Responsables",
			"TASK_advancementLate"=>"Progreso retrasado",

			////	MODULE_CONTACT
			////
			// Menu principal
			"CONTACT_headerModuleName"=>"Contactos",
			"CONTACT_moduleDescription"=>"Directorio de contactos",
			"CONTACT_option_adminRootAddContent"=>"Sólo el administrador puede añadir elementos en el directorio raíz",
			//Index
			"CONTACT_addContact"=>"Añadir un contacto",
			"CONTACT_noContact"=>"No hay contacto todavía",
			"CONTACT_createUser"=>"Crear un usuario en este espacio",
			"CONTACT_createUserInfo"=>"¿ Crear un usuario en este espacio con este contacto ?",
			"CONTACT_createUserConfirm"=>"El usuario fue creado",

			////	MODULE_LIEN
			////
			// Menu principal
			"LINK_headerModuleName"=>"Favoritos",
			"LINK_moduleDescription"=>"Favoritos",
			"LINK_option_adminRootAddContent"=>"Sólo el administrador puede añadir elementos en el directorio raíz",
			//Index
			"LINK_addLink"=>"Añadir un enlace",
			"LINK_noLink"=>"No hay enlaces por el momento",
			// lien_edit & dossier_edit
			"LINK_adress"=>"Dirección web",

			////	MODULE_MAIL
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
			"MAIL_recipients"=>"Destinatarios",
		);
	}

	/*
	 * Jours Fériés de l'année
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
			$dateList[$date]="Lunes de Pascua";
		}

		//Fêtes fixes	$dateList[$year."-01-01"]="Día de Año Nuevo";
		$dateList[$year."-01-06"]="Epifanía";
		$dateList[$year."-05-01"]="Día del Trabajo";
		$dateList[$year."-08-15"]="Asunción";
		$dateList[$year."-10-12"]="Día de la Hispanidad";
		$dateList[$year."-11-01"]="Toussaint";
		$dateList[$year."-12-06"]="Día de la Constitución";
		$dateList[$year."-12-08"]="Inmaculada Concepción";
		$dateList[$year."-12-25"]="Navidad";

		//Retourne le résultat
		return $dateList;
	}
}