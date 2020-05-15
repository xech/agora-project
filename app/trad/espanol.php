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
		self::$trad["CURLANG"]="es";
		self::$trad["HEADER_HTTP"]="es";
		self::$trad["DATEPICKER"]="es";
		self::$trad["HTML_EDITOR"]="es";
		self::$trad["UPLOADER"]="es";
		setlocale(LC_TIME, "es_ES.utf8", "es_ES.UTF-8", "es_ES", "es", "spanish");

		////	Divers
		self::$trad["OK"]="OK";
		self::$trad["fillAllFields"]="Gracias rellene todos los campos";
		self::$trad["requiredFields"]="Campo obligatorio";
		self::$trad["inaccessibleElem"]="Elemento inaccesible";
		self::$trad["warning"]="Atención";
		self::$trad["elemEditedByAnotherUser"]="El elemento está siendo editado por";//"..bob"
		self::$trad["yes"]="sí";
		self::$trad["no"]="no";
		self::$trad["none"]="no";
		self::$trad["or"]="o";
		self::$trad["and"]="y";
		self::$trad["by"]="by";
		self::$trad["goToPage"]="Ir a la página";
		self::$trad["alphabetFilter"]="Filtro alfabético";
		self::$trad["displayAll"]="Mostrar todo";
		self::$trad["allCategory"]="Cualquier categoría";
		self::$trad["important"]="importante";
		self::$trad["show"]="mostrar";
		self::$trad["hide"]="ocultar";
		self::$trad["byDefault"]="Por defecto";
		self::$trad["mapLocalize"]="Localizar en el mapa";
		self::$trad["mapLocalizationFailure"]="Falla de localización de la siguiente dirección";
		self::$trad["mapLocalizationFailure2"]="Verifique que exista la dirección en www.google.com/maps o www.openstreetmap.org";
		self::$trad["sendMail"]="enviar un email";
		self::$trad["mailInvalid"]="El correo electrónico no es válida";
		self::$trad["element"]="elemento";
		self::$trad["elements"]="elementos";
		self::$trad["folder"]="carpeta";
		self::$trad["folders"]="carpetas";
		self::$trad["close"]="Cerrar";
		self::$trad["visibleAllSpaces"]="Visible en todos los espacios";
		self::$trad["confirmCloseForm"]="¿Quieres cerrar el formulario?";
		self::$trad["modifRecorded"]="Los cambios fueron registrados";
		self::$trad["confirm"]="Confirmar ?";
		self::$trad["comment"]="Comentario";
		self::$trad["commentAdd"]="Añadir un comentario";
		self::$trad["optional"]="(opcional)";
		self::$trad["objNew"]="nuevo elemento";
		self::$trad["objNewInfos"]="Creado desde mi anterior conexión o creado dentro de las 24 horas";
		self::$trad["personalAccess"]="Acceso personal";
		self::$trad["copyUrl"]="Copie la dirección de acceso";
		self::$trad["copyUrlConfirmed"]="La dirección se ha copiado : se puede integrar en una noticia, un evento, una tarea, etc. <br><br> También se puede utilizar fuera del espacio (en correo electrónico, blog, etc.), pero solo las personas con acceso al espacio pueden usarlo.";

		////	images
		self::$trad["picture"]="foto";
		self::$trad["wallpaper"]="papel tapiz";
		self::$trad["keepImg"]="mantener la imagen";
		self::$trad["changeImg"]="cambiar la imagen";
		self::$trad["pixels"]="píxeles";

		////	Connexion
		self::$trad["specifyLoginPassword"]="Gracias a especificar un nombre de usuario y contraseña";
		self::$trad["specifyLogin"]="Gracias especificar un email/identificador (sin espacio)";
		self::$trad["specifyLoginMail"]="Se recomienda utilizar un email como identificador de sesión";
		self::$trad["login"]="Email / Identificador de conexión";
		self::$trad["loginPlaceholder"]="Email / Identificador";
		self::$trad["connect"]="Conexión";
		self::$trad["connectAuto"]="Recuérdame";
		self::$trad["connectAutoInfo"]="Recordar mi nombre de usuario y la contraseña para una conexión automática";
		self::$trad["gSigninButton"]="iniciar sesión con google";
		self::$trad["gSigninButtonInfo"]="Inicie sesión con su cuenta de Gmail : ya debe tener una cuenta en este espacio, con una dirección de correo electrónico <i>@gmail.com</i>";
		self::$trad["gSigninUserNotRegistered"]="no está registrado en el espacio con el correo electrónico";
		self::$trad["switchOmnispace"]="Conectarse a otro espacio Omnispace";
		self::$trad["guestAccess"]="Conéctese a un espacio público como invitado";
		self::$trad["spacePassError"]="Contraseña incorrecta";
		self::$trad["ieObsolete"]="Su navegador es demasiado viejo y no soporta todos los elementos de HTML : Se recomienda actualizarlo o utilizar otro navegador";
		
		////	Password : connexion d'user / edition d'user / reset du password
		self::$trad["password"]="Contraseña";
		self::$trad["passwordModify"]="Cambiar la contraseña";
		self::$trad["passwordToModify"]="Contraseña (cambiar)";
		self::$trad["passwordVerif"]="Confirmar contraseña";
		self::$trad["passwordInfo"]="Dejar en blanco si desea mantener su contraseña";
		self::$trad["passwordInvalid"]="Su contraseña debe tener al menos 6 caracteres con al menos 1 dígito y al menos 1 letra";
		self::$trad["passwordConfirmError"]="Your confirmation password is not valid";
		self::$trad["specifyPassword"]="Gracias especificar una contraseña";
		self::$trad["resetPassword"]="Información de inicio de sesión olvidada ?";
		self::$trad["resetPassword2"]="Introduzca su dirección de correo electrónico para recibir sus datos de acceso";
		self::$trad["resetPasswordNotif"]="Se acaba de enviar un correo electrónico a su dirección para restablecer su contraseña. Si no ha recibido un correo electrónico, verifique que la dirección especificada sea correcta o que el correo electrónico no esté en su correo no deseado.";
		self::$trad["resetPasswordMailTitle"]="Restablecer su contraseña";
		self::$trad["resetPasswordMailPassword"]="Para iniciar sesión en su Omnispace y restablecer su contraseña";
		self::$trad["resetPasswordMailPassword2"]="haga clic aquí";
		self::$trad["resetPasswordMailLoginRemind"]="Recordatorio de su login";
		self::$trad["resetPasswordIdExpired"]="El enlace web para regenerar la contraseña ha caducado .. gracias por reiniciar la procedura";

		////	Type d'affichage
		self::$trad["displayMode"]="Mostrar";
		self::$trad["displayMode_line"]="Lista";
		self::$trad["displayMode_block"]="Bloque";

		////	Sélectionner / Déselectionner tous les éléments
		self::$trad["select"]="Seleccionar";
		self::$trad["selectUnselect"]="Seleccionar / Deseleccionar";
		self::$trad["selectUnselectAll"]="Seleccionar/deseleccionar todo";
		self::$trad["selectAll"]="Seleccionar todo";
		self::$trad["invertSelection"]="Invertir selección";
		self::$trad["deleteElems"]="Eliminar elementos";
		self::$trad["changeFolder"]="Mover a otro carpeta";
		self::$trad["showOnMap"]="Mostrar en el mapa";
		self::$trad["selectUser"]="Gracias por seleccionar al menos un usuario";
		self::$trad["selectUsers"]="Gracias por seleccionar por lo menos dos usuarios";
		self::$trad["selectSpace"]="Gracias por elegir al menos un espacio";

		////	Temps ("de 11h à 12h", "le 25-01-2007 à 10h30", etc.)
		self::$trad["from"]="de";
		self::$trad["at"]="a";
		self::$trad["the"]="el";
		self::$trad["begin"]="inicio";
		self::$trad["end"]="Fin";
		self::$trad["hourSeparator"]="h";
		self::$trad["days"]="dias";
		self::$trad["day_1"]="lunes";
		self::$trad["day_2"]="Martes";
		self::$trad["day_3"]="miércoles";
		self::$trad["day_4"]="Jueves";
		self::$trad["day_5"]="Viernes";
		self::$trad["day_6"]="Sábado";
		self::$trad["day_7"]="Domingo";
		self::$trad["month_1"]="Enero";
		self::$trad["month_2"]="Febrero";
		self::$trad["month_3"]="marzo";
		self::$trad["month_4"]="Abril";
		self::$trad["month_5"]="Mayo";
		self::$trad["month_6"]="Junio";
		self::$trad["month_7"]="julio";
		self::$trad["month_8"]="agosto";
		self::$trad["month_9"]="Septiembre";
		self::$trad["month_10"]="octubre";
		self::$trad["month_11"]="Noviembre";
		self::$trad["month_12"]="Diciembre";
		self::$trad["today"]="hoy";
		self::$trad["displayToday"]="Hoy";
		self::$trad["beginEndError"]="La fecha de fin no puede ser anterior a la fecha de inicio";
		self::$trad["dateFormatError"]="La fecha debe estar en el formato dd/mm/AAAA";

		////	Nom & Description (pour les menus d'édition principalement)
		self::$trad["title"]="Título";
		self::$trad["name"]="Nombre";
		self::$trad["description"]="Descripción";
		self::$trad["specifyName"]="Gracias por especificar un nombre";
		self::$trad["editorDraft"]="Recuperar mi texto";
		self::$trad["editorDraftConfirm"]="Recuperar el último texto especificado";

		////	Validation des formulaires
		self::$trad["add"]="Añadir";
		self::$trad["modify"]="Editar";
		self::$trad["modifyAndAccesRight"]="Editar + derechos de acceso";
		self::$trad["validate"]=" Validar";
		self::$trad["send"]="Enviar";
		self::$trad["sendTo"]="Enviar a";

		////	Tri d'affichage. Tous les éléments (dossier, tâche, lien, etc...) ont par défaut une date, un auteur & une description
		self::$trad["sortBy"]="Ordenado por";
		self::$trad["sortBy2"]="Ordenar por";
		self::$trad["SORT_dateCrea"]="fecha de creación";
		self::$trad["SORT_dateModif"]="fecha de modification";
		self::$trad["SORT_title"]="Título";
		self::$trad["SORT_description"]="Descripción";
		self::$trad["SORT__idUser"]="autor";
		self::$trad["SORT_extension"]="Tipo de archivo";
		self::$trad["SORT_octetSize"]="tamaño";
		self::$trad["SORT_downloadsNb"]="downloads";
		self::$trad["SORT_civility"]="civilidad";
		self::$trad["SORT_name"]="appelido";
		self::$trad["SORT_firstName"]="nombre";
		self::$trad["SORT_adress"]="dirección";
		self::$trad["SORT_postalCode"]="Código postal";
		self::$trad["SORT_city"]="ciudad";
		self::$trad["SORT_country"]="país";
		self::$trad["SORT_function"]="función";
		self::$trad["SORT_companyOrganization"]="compañía / organización";
		self::$trad["tri_ascendant"]="Ascendente";
		self::$trad["tri_descendant"]="Descendente";
		
		////	Options de suppression
		self::$trad["confirmDelete"]="Confirmar eliminación ?";
		self::$trad["confirmDeleteDbl"]="Está seguro ?!";
		self::$trad["confirmDeleteFolderAccess"]="Advertencia : algunos sub-carpetas no son accessible : serán tambien eliminados !";
		self::$trad["notifyBigFolderDelete"]="Eliminar --NB_FOLDERS-- archivos puede ser un poco largo, espere unos momentos antes del final del proceso";
		self::$trad["delete"]="Eliminar";
		self::$trad["notDeletedElements"]="Algunos elementos no se han eliminado porque no tienes los derechos de acceso necesarios";
		
		////	Visibilité d'un Objet : auteur et droits d'accès
		self::$trad["autor"]="Autor";
		self::$trad["postBy"]="publicado por";
		self::$trad["guest"]="invitado";
		self::$trad["creation"]="Creación";
		self::$trad["modification"]="Modif";
		self::$trad["objHistory"]="histórico del elemento";
		self::$trad["all"]="todos";
		self::$trad["unknown"]="desconocido";
		self::$trad["accessRead"]="lectura";
		self::$trad["readInfos"]="Acceso de lectura";
		self::$trad["accessWriteLimit"]="escritura limitada";
		self::$trad["readLimitInfos"]="Acceso en escritura limitada : possibilidad de añadir --OBJCONTENT--s, sin modificar o suprimir los creados por otros usuarios";
		self::$trad["accessWrite"]="escritura";
		self::$trad["writeInfos"]="Acceso en escritura";
		self::$trad["writeInfosContainer"]="Acceso en escritura : possibilidad de añadir, modificar o suprimir todos los --OBJCONTENT-- del -OBJLABEL-";
		self::$trad["autorPrivilege"]="Solo el autor y los administradores pueden cambiar los permisos de acceso o eliminar el -OBJLABEL-";
		self::$trad["folderContent"]="contenido";
		
		////	Libellé des objets
		self::$trad["OBJECTcontainer"]="contenedor";
		self::$trad["OBJECTelement"]="elemento";
		self::$trad["OBJECTfolder"]="carpeta";
		self::$trad["OBJECTdashboardNews"]="novedade";
		self::$trad["OBJECTdashboardPoll"]="encuesta";
		self::$trad["OBJECTfile"]="archivo";
		self::$trad["OBJECTfileFolder"]="carpeta";
		self::$trad["OBJECTcalendar"]="calendario";
		self::$trad["OBJECTcalendarEvent"]="evento";
		self::$trad["OBJECTforumSubject"]="tema";
		self::$trad["OBJECTforumMessage"]="mensaje";
		self::$trad["OBJECTcontact"]="contacto";
		self::$trad["OBJECTcontactFolder"]="carpeta";
		self::$trad["OBJECTlink"]="favorito";
		self::$trad["OBJECTlinkFolder"]="carpeta";
		self::$trad["OBJECTtask"]="tarea";
		self::$trad["OBJECTtaskFolder"]="carpeta";
		self::$trad["OBJECTuser"]="usuario";
		
		////	Envoi d'un email (nouvel utilisateur, notification de création d'objet, etc...)
		self::$trad["MAIL_hello"]="Hola";
		self::$trad["MAIL_noFooter"]="No firme el mensaje";
		self::$trad["MAIL_noFooterInfo"]="No firme el final del mensaje con el nombre del remitentey un enlace al espacio";
		self::$trad["MAIL_hideRecipients"]="Ocultar los destinatarios";
		self::$trad["MAIL_hideRecipientsInfo"]="Por defecto, los destinatarios de correo electrónico aparecen en el mensaje.";
		self::$trad["MAIL_receptionNotif"]="Confirmación de entrega";
		self::$trad["MAIL_receptionNotifInfo"]="Advertencia! algunos clientes de correo electrónico no soportan el recibo de entrega";
		self::$trad["MAIL_sendBy"]="Enviado por";  // "Envoyé par" M. Trucmuche
		self::$trad["MAIL_sendOk"]="El correo electrónico ha sido enviado !";
		self::$trad["MAIL_sendNotif"]="El correo electrónico de notificación ha sido enviado !";
		self::$trad["MAIL_notSend"]="El correo electrónico no se pudo enviar...";
		self::$trad["MAIL_fromTheSpace"]="en el espacio";//truc
		self::$trad["MAIL_elemCreatedBy"]="-OBJLABEL- creado por";//boby
		self::$trad["MAIL_elemModifiedBy"]="-OBJLABEL- modificado por";//boby
		self::$trad["MAIL_elemAccessLink"]="Haga clic aquí para acceder al elemento en su espacio";

		////	Dossier & fichier
		self::$trad["gigaOctet"]="GB";
		self::$trad["megaOctet"]="MB";
		self::$trad["kiloOctet"]="KB";
		self::$trad["rootFolder"]="Carpeta raíz";
		self::$trad["rootFolderEditInfo"]="Abra la configuración del espacio<br>para cambiar los derechos de acceso a la carpeta raíz";
		self::$trad["addFolder"]="añadir un directorio";
		self::$trad["download"]="Descargar archivos";
		self::$trad["downloadFolder"]="Descargar la carpeta";
		self::$trad["diskSpaceUsed"]="Espacio utilizado";
		self::$trad["diskSpaceUsedModFile"]="Espacio utilizado para los Archivos";
		self::$trad["downloadAlert"]="Su archivo es demasiado grande para descargarlo durante el día (--ARCHIVE_SIZE--). Reinicie la descarga después de las";//"19h"
		
		////	Infos sur une personne
		self::$trad["civility"]="Civilidad";
		self::$trad["name"]="Appelido";
		self::$trad["firstName"]="Nombre";
		self::$trad["adress"]="Dirección";
		self::$trad["postalCode"]="Código postal";
		self::$trad["city"]="Ciudad";
		self::$trad["country"]="País";
		self::$trad["telephone"]="Teléfono";
		self::$trad["telmobile"]="teléfono móvil";
		self::$trad["mail"]="Email";
		self::$trad["function"]="Función";
		self::$trad["companyOrganization"]="compañía / organización";
		self::$trad["lastConnection"]="Última conexión";
		self::$trad["lastConnection2"]="Conectado el";
		self::$trad["lastConnectionEmpty"]="No está conectado";
		self::$trad["displayProfil"]="Ver perfil";
		
		////	Captcha
		self::$trad["captcha"]="Copiar los 4 caracteres";
		self::$trad["captchaInfo"]="Por favor, escriba los 4 caracteres para su identificación";
		self::$trad["captchaSpecify"]="Por favor, especifique la identificación visual";
		self::$trad["captchaError"]="La identificación visual no es valida";
		
		////	Rechercher
		self::$trad["searchSpecifyText"]="Por favor, especifique las palabras clave de al menos 3 caracteres";
		self::$trad["search"]="Buscar";
		self::$trad["searchDateCrea"]="Fecha de creación";
		self::$trad["searchDateCreaDay"]="menos de un día";
		self::$trad["searchDateCreaWeek"]="menos de una semana";
		self::$trad["searchDateCreaMonth"]="menos de un mes";
		self::$trad["searchDateCreaYear"]="menos de un año";
		self::$trad["searchOnSpace"]="Buscar en el espacio";
		self::$trad["advancedSearch"]= "Búsqueda avanzada";
		self::$trad["advancedSearchSomeWords"]= "cualquier palabra";
		self::$trad["advancedSearchAllWords"]= "todas las palabras";
		self::$trad["advancedSearchExactPhrase"]= "frase exacta";
		self::$trad["keywords"]="Palabras clave";
		self::$trad["listModules"]="Módulos";
		self::$trad["listFields"]="Campos";
		self::$trad["listFieldsElems"]="Elementos involucrados";
		self::$trad["noResults"]="No hay resultados";
		
		////	Gestion des inscriptions d'utilisateur
		self::$trad["userInscription"]="registrarme al espacio";
		self::$trad["userInscriptionInfo"]="crear una nueva cuenta de usuario (validado por un administrador)";
		self::$trad["userInscriptionSpace"]="registrarme al espacio";
		self::$trad["userInscriptionRecorded"]="Su registro será validado tan pronto como sea posible por el administrador del espacio";
		self::$trad["userInscriptionOptionSpace"]="Permitir a los visitantes que se registren en el espacio";
		self::$trad["userInscriptionOptionSpaceInfo"]="El registro se encuentra en la página de inicio. Debe ser validado por el administrador del espacio.";
		self::$trad["userInscriptionValidate"]="Registros de usuarios";
		self::$trad["userInscriptionValidateInfo"]="Validar registros de usuarios al espacio";
		self::$trad["userInscriptionInvalidateButton"]="Invalidar registros";
		self::$trad["userInscriptionInvalidateMail"]="Su cuenta no ha sido validado en";
		
		////	Importer ou Exporter : Contact OU Utilisateurs
		self::$trad["export"]="Exportar";
		self::$trad["import"]="Importar";
		self::$trad["importExport_user"]="usuarios";
		self::$trad["importExport_contact"]="contactos";
		self::$trad["exportFormat"]="formato";
		self::$trad["specifyFile"]="or favor, especifique un archivo";
		self::$trad["fileExtension"]="El tipo del archivo no es válido. Debe ser de tipo";
		self::$trad["importInfo"]="Seleccione los campos (Agora) de destino con las listas desplegables de cada columna.";
		self::$trad["importNotif"]="Por favor, seleccione la columna de nombre en las listas desplegables";
		self::$trad["importNotif2"]="Por favor, seleccione al menos un contacto para importar";
		self::$trad["importNotif3"]="El campo Agora ya ha sido seleccionado en otra columna (cada campo Agora se puede seleccionar sólo una vez)";
		
		////	Messages d'alert ou d'erreur
		self::$trad["NOTIF_identification"]="Nombre de usuario o contraseña no válida";
		self::$trad["NOTIF_presentIp"]="Esta cuenta de usuario se está utilizando actualmente desde otra computadora, con otra dirección IP. Una cuenta solo se puede usar en un computadora al mismo tiempo.";
		self::$trad["NOTIF_noSpaceAccess"]="El acceso no esta actualmente permitido con este cuente : probablemente no asignado a un espacio.";
		self::$trad["NOTIF_fileOrFolderAccess"]="El archivo o la carpeta no está disponible";
		self::$trad["NOTIF_diskSpace"]="El espacio para almacenar sus archivos no es suficiente, no se puede añadir archivos";
		self::$trad["NOTIF_fileVersionForbidden"]="Tipo de archivo no permitido";
		self::$trad["NOTIF_fileVersion"]="Tipo de archivo diferente del original";
		self::$trad["NOTIF_folderMove"]="No se puede mover la carpeta dentro de sí mismo..!";
		self::$trad["NOTIF_duplicateName"]="Un archivo o carpeta con el mismo nombre ya existe";
		self::$trad["NOTIF_fileName"]="Un archivo con el mismo nombre ya existe (no ha sido reemplazado)";
		self::$trad["NOTIF_chmodDATAS"]="El directorio ''DATAS'' no es accesible por escrito. Usted necesita dar un acceso de lectura y escritura para el propietario y el grupo (''chmod 775'').";
		self::$trad["NOTIF_usersNb"]="No se puede añadir un nuevo usuario : se limita a "; // "...limité à" 10
		self::$trad["NOTIF_update"]="Actualizado el";
		
		////	header menu / Footer
		self::$trad["HEADER_displaySpace"]="Espacios disponibles";
		self::$trad["HEADER_displayAdmin"]="Visualización de Administrador";
		self::$trad["HEADER_displayAdminEnabled"]="Visualización de Administrador activada";
		self::$trad["HEADER_displayAdminInfo"]="Mostrar todos los elementos del espacio (solo para los administradores)";
		self::$trad["HEADER_searchElem"]="Buscar en el espacio";
		self::$trad["HEADER_documentation"]="Documentación";
		self::$trad["HEADER_disconnect"]="Cerrar sesión del Ágora";
		self::$trad["HEADER_shortcuts"]="Acceso directo";
		self::$trad["MESSENGER_messenger"]="Mensajería instantánea";
		self::$trad["MESSENGER_messengerInfo"]="chatear con varias personas al mismo tiempo";
		self::$trad["MESSENGER_connected"]="Conectado";
		self::$trad["MESSENGER_connectedNobody"]="Nadie esta conectado";
		self::$trad["MESSENGER_connectedNobodyInfo"]="Nadie esta conectado actualmente : ver los mensajes antiguos";
		self::$trad["MESSENGER_connectedSince"]="conectado a";//connecté depuis 12:45
		self::$trad["MESSENGER_sendAt"]="Enviado a";
		self::$trad["MESSENGER_addMessageToSelection"]="Mi mensaje (personas seleccionadas)";
		self::$trad["MESSENGER_addMessageTo"]="Mi mensaje a";
		self::$trad["MESSENGER_addMessageNotif"]="Por favor, especifique un mensaje";
		self::$trad["MESSENGER_visioProposeTo"]="Enviar  una videollamada a";//..boby
		self::$trad["MESSENGER_visioProposeToSelection"]="Enviar una videollamada a las personas seleccionadas";
		self::$trad["MESSENGER_userProposeVisioCall"]="propone una videollamada. Haga clic aquí para iniciarla.";//boby.. "vous propose un appel visio"
		self::$trad["MESSENGER_visioProposalPending"]="La propuesta de video ha sido enviada. Se abrirá una nueva ventana de video: permita el acceso a su cámara web y micrófono";
		self::$trad["MESSENGER_visioProposalLanch"]="¿Iniciar la videollamada?";
		self::$trad["FOOTER_pageGenerated"]="página generada en";
		
		////	vueObjMenuEdit
		self::$trad["EDIT_notifNoSelection"]="Debe seleccionar al menos una persona o un espacio";
		self::$trad["EDIT_notifNoPersoAccess"]="Usted no se ha asignado al elemento. validar todos lo mismo ?";
		self::$trad["EDIT_notifWriteAccess"]="Debe haber al menos una persona o un espacio asignado para escribir";
		self::$trad["EDIT_parentFolderAccessError"]="Recuerde verificar los derechos de acceso de la carpeta superior ''<i>--FOLDER_NAME--</i>'': si no está también asignada a ''<i>--TARGET_LABEL--</i>'', el archivo actual no será accesible para el.";
		self::$trad["EDIT_accessRight"]="Derechos de acceso";
		self::$trad["EDIT_accessRightContent"]="Derechos de acceso al contenido";
		self::$trad["EDIT_spaceNoModule"]="El módulo actual aún no se ha añadido a este espacio";
		self::$trad["EDIT_allUsers"]="Todos los usuarios";
		self::$trad["EDIT_allUsersAndGuests"]="Todos los usuarios y invitados";
		self::$trad["EDIT_allUsersInfo"]="Todos los usuarios del espacio <i>--SPACENAME--</i>";
		self::$trad["EDIT_allUsersAndGuestsInfo"]="Todos los usuarios del espacio <i>--SPACENAME--</i>, y los invitados pero con acceso solo de lectura (invitados: personas que no tienen una cuenta de usuario)";
		self::$trad["EDIT_adminSpace"]="Administrador del espacio:<br>acceso de escritura a todos los elementos del espacio";
		self::$trad["EDIT_showAllSpaceUsers"]="Mostrar todos los usuarios";
		self::$trad["EDIT_mySpaces"]="Mostrar todos mis espacios";
		self::$trad["EDIT_notifMail"]="Notificar";
		self::$trad["EDIT_notifMail2"]="Enviar una notificación de creación/cambio por email";
		self::$trad["EDIT_notifMailInfo"]="Si no selecciona los destinatarios, de forma predeterminada, se enviará a las personas afectadas al elemento";
		self::$trad["EDIT_notifMailAddFiles"]="Adjuntar archivos a la notificación";
		self::$trad["EDIT_notifMailSelect"]="Seleccionar los destinatarios de las notificaciones";
		self::$trad["EDIT_notifMailMoreUsers"]="Mostrar mas usuarios";
		self::$trad["EDIT_accessRightSubFolders"]="Dar igualdad de derechos a todos los sub-carpetas";
		self::$trad["EDIT_accessRightSubFolders_info"]="Extender los derechos de acceso, a los sub-carpetas que se pueden editar";
		self::$trad["EDIT_shortcut"]="Acceso directo";
		self::$trad["EDIT_shortcutInfo"]="Mostrar un acceso directo en el menú principal";
		self::$trad["EDIT_attachedFile"]="Añadir archivos";
		self::$trad["EDIT_attachedFileInfo"]="Añadir fotos, vídeos, PDF, Word.. al objeto actual.<br>Imágenes y videos se pueden integrar directamente en el editor.";
		self::$trad["EDIT_attachedFileInsert"]="Mostrar en la descripción";
		self::$trad["EDIT_attachedFileInsertInfo"]="Mostrar la imagen / video / mp3... en la descripción anterior. La inserción se realiza después de la validación del formulario.";
		self::$trad["EDIT_guestName"]="Su nombre / apodo";
		self::$trad["EDIT_guestNameNotif"]="Por favor, especifique un nombre / apodo";
		self::$trad["EDIT_guestElementRegistered"]="Gracias por su propuesta : será examinada lo antes posible antes de la validación";
		
		////	Formulaire d'installation
		self::$trad["INSTALL_dbConnect"]="Conexión a la base de datos";
		self::$trad["INSTALL_dbHost"]="Nombre del servidor host (hostname)";
		self::$trad["INSTALL_dbName"]="Nombre de la base de datos";
		self::$trad["INSTALL_dbLogin"]="Nombre de Usuario";
		self::$trad["INSTALL_adminAgora"]="Administrador del Ágora";
		self::$trad["INSTALL_dbErrorDbName"]="Advertencia: el nombre de la base de datos MySQL debe contener solo caracteres alfanuméricos y guiones o guiones bajos";
		self::$trad["INSTALL_dbErrorUnknown"]="No hay conexión con la base de datos MySQL";
		self::$trad["INSTALL_dbErrorIdentification"]="No identificación con la base de datos MySQL";
		self::$trad["INSTALL_dbErrorAppInstalled"]="La instalación ya se ha realizado en esta base de datos. Gracias simplemente eliminar la base de datos si se debe reiniciar la instalación.";
		self::$trad["INSTALL_PhpOldVersion"]="Agora-Project requiere una versión más reciente de PHP";
		self::$trad["INSTALL_confirmInstall"]="Confirmar instalación ?";
		self::$trad["INSTALL_installOk"]="Agora-Project ha sido instalado !";
		self::$trad["INSTALL_spaceDescription"]="Espacio para el intercambio y el trabajo colaborativo";
		self::$trad["INSTALL_dataDashboardNews1"]="Bienvenido a tu nuevo espacio Omnispace";
		self::$trad["INSTALL_dataDashboardNews2"]="Haga clic aquí para invitar a personas a unirse a usted";
		self::$trad["INSTALL_dataDashboardNews3"]="Comparta archivos, agenda y noticias, administre tareas y proyectos, intercambie contactos o enlaces a Internet, chatee en foros o envíe mensajes instantáneos, envíe boletines, etc.";
		self::$trad["INSTALL_dataDashboardPoll"]="¿Qué opinas de la nueva herramienta de encuesta?";
		self::$trad["INSTALL_dataDashboardPollA"]="Muy interesante !";
		self::$trad["INSTALL_dataDashboardPollB"]="Interesante";
		self::$trad["INSTALL_dataDashboardPollC"]="Sin interés";
		self::$trad["INSTALL_dataCalendarEvt"]="Bienvenido a Omnispace";
		self::$trad["INSTALL_dataForumSubject1"]="Bienvenido al foro de Omnispace";
		self::$trad["INSTALL_dataForumSubject2"]="Siéntase libre de compartir sus preguntas o discutir los temas que deseas.";

		////	MODULE_PARAMETRAGE
		////
		self::$trad["AGORA_headerModuleName"]="Administración general";
		self::$trad["AGORA_generalSettings"]="Administración general";
		self::$trad["AGORA_backupFull"]="Copia de seguridad de todos los archivos";
		self::$trad["AGORA_backupNotif"]="La creación de la copia de seguridad puede tardar unos minutos ... y descargar una docena de minutos.";
		self::$trad["AGORA_backupDb"]="Copia de seguridad de la base de datos";
		self::$trad["AGORA_diskSpaceInvalid"]="El límite de espacio de disco debe ser un número entero";
		self::$trad["AGORA_confirmModif"]="Confirmar los cambios ?";
		self::$trad["AGORA_name"]="Nombre del sitio";
		self::$trad["AGORA_footerHtml"]="Footer / Pie de página texto/html";
		self::$trad["AGORA_lang"]="Lenguaje por defecto";
		self::$trad["AGORA_timezone"]="Zona horaria";
		self::$trad["AGORA_spaceName"]="Nombre del espacio principal";
		self::$trad["AGORA_diskSpaceLimit"]="Espacio de disco disponible para los archivos";
		self::$trad["AGORA_logsTimeOut"]="Duración del historial de eventos (registros)";
		self::$trad["AGORA_logsTimeOutInfo"]="El período de retención del historial de eventos se refiere a la adición o modificación de los elementos. Los registros de eliminación se mantienen durante al menos 1 año.";
		self::$trad["AGORA_visioHost"]="Servidor de videoconferencia Jitsi";
		self::$trad["AGORA_visioHostInfo"]="Dirección del servidor de videoconferencia Jitsi";
		self::$trad["AGORA_skin"]="Color de la interfaz";
		self::$trad["AGORA_black"]="Negro";
		self::$trad["AGORA_white"]="Blanco";
		self::$trad["AGORA_wallpaperLogoError"]="La imagen de fondo y el logotipo debe tener el formato .jpg ou .png";
		self::$trad["AGORA_deleteWallpaper"]="Eliminar la imagen de fondo ?";
		self::$trad["AGORA_logo"]="Logotipo en pie de página";
		self::$trad["AGORA_logoUrl"]="URL";
		self::$trad["AGORA_logoConnect"]="logo / Imagen de la página de conexión";
		self::$trad["AGORA_logoConnectInfo"]="Desplegado encima del formulario de conexión";
		self::$trad["AGORA_usersCommentLabel"]="Permitir a los usuarios hacer comentarios sobre el elemento";
		self::$trad["AGORA_usersComment"]="comentario";
		self::$trad["AGORA_usersComments"]="comentarios";
		self::$trad["AGORA_usersLikeLabel"]="Los usuarios pueden <i>Aprobar</i> el elemento";
		self::$trad["AGORA_usersLike_likeSimple"]="Solo Me gusta";
		self::$trad["AGORA_usersLike_likeOrNot"]="Me gusta / No me gusta";
		self::$trad["AGORA_usersLike_like"]="Me gusta!";
		self::$trad["AGORA_usersLike_dontlike"]="No me gusta";
		self::$trad["AGORA_mapTool"]="Herramienta de mapeo";
		self::$trad["AGORA_mapToolInfo"]="Herramienta de mapeo para ver usuarios y contactos en un mapa";
		self::$trad["AGORA_mapApiKey"]="'API Key' para herramienta de mapeo";
		self::$trad["AGORA_mapApiKeyInfo"]="API Key para la herramienta de mapeo Google Map";
		self::$trad["AGORA_gSignin"]="Conexión opcional con Gmail (Sign-In)";
		self::$trad["AGORA_gSigninInfo"]="Los usuarios pueden conectarse más fácilmente a su espacio a través de su cuenta de Gmail : para eso, un correo electrónico <i>@gmail.com</ i> ya debe estar registrado en la cuenta del usuario";
		self::$trad["AGORA_gSigninClientId"]="Configuración de Sign-In : Client ID";
		self::$trad["AGORA_gSigninClientIdInfo"]="Esta configuración es necesaria para Google Sign-In : https://developers.google.com/identity/sign-in/web/";
		self::$trad["AGORA_gPeopleApiKey"]="Configuración de Google People : API KEY";
		self::$trad["AGORA_gPeopleApiKeyInfo"]="Esta configuración es necesaria para recuperar contactos de Gmail (People 'API KEY') : <a href='https://developers.google.com/people/' target='_blank'>https://developers.google.com/people/</a>";
		self::$trad["AGORA_messengerDisabled"]="Mensajería instantánea activada";
		self::$trad["AGORA_moduleLabelDisplay"]="Nombre de los módulos en la barra de menú";
		self::$trad["AGORA_personsSort"]="Ordenar los usuarios y contactos";
		self::$trad["AGORA_versions"]="Versiones";
		self::$trad["AGORA_dateUpdate"]="actualización el";
		self::$trad["AGORA_Changelog"]="Ver el registro de versión";
		self::$trad["AGORA_funcMailDisabled"]="Función de PHP para enviar correos electrónicos : desactivada !";
		self::$trad["AGORA_funcMailInfo"]="Algunos ''Host'' desactivan la función PHP para enviar correos electrónicos, por razones de seguridad ou saturación de los servidores (SPAM)";
		self::$trad["AGORA_funcImgDisabled"]="Función de la manipulación de imágenes y miniaturas (PHP GD2) : desactivada !";
		//SMTP
		self::$trad["AGORA_smtpLabel"]="Conexión SMTP & sendMail";
		self::$trad["AGORA_sendmailFrom"]="Email en el campo 'From'";
		self::$trad["AGORA_sendmailFromPlaceholder"]="ex: 'mi-dominio.es'";
		self::$trad["AGORA_smtpHost"]="Dirección del servidor (hostname)";
		self::$trad["AGORA_smtpPort"]="Puerto de servidor";
		self::$trad["AGORA_smtpPortInfo"]="'25' por defecto. '587' o '465' para SSL/TLS";
		self::$trad["AGORA_smtpSecure"]="Tipo de conexión cifrada (opcional)";
		self::$trad["AGORA_smtpSecureInfo"]="'ssl' o 'tls'";
		self::$trad["AGORA_smtpUsername"]="Nombre del usuario";
		self::$trad["AGORA_smtpPass"]="Contraseña";
		//LDAP
		self::$trad["AGORA_ldapLabel"]="Conexión a un servidor LDAP";
		self::$trad["AGORA_ldapHost"]="Dirección del servidor";
		self::$trad["AGORA_ldapPort"]="Puerto de servidor";
		self::$trad["AGORA_ldapPortInfo"]="''389'' por defecto";
		self::$trad["AGORA_ldapLogin"]="Cadena de conexión para admin";
		self::$trad["AGORA_ldapLoginInfo"]="por ejemplo ''uid=admin,ou=my_company''";
		self::$trad["AGORA_ldapPass"]="Contraseña del administrador";
		self::$trad["AGORA_ldapDn"]="Grupo / base DN";
		self::$trad["AGORA_ldapDnInfo"]="Localización de los usuarios del directorio.<br> por ejemplo ''ou=users,o=my_company''";
		self::$trad["AGORA_ldapConnectError"]="Error al conectar con el servidor LDAP !";
		self::$trad["AGORA_ldapCreaAutoUsers"]="Auto creación de usuarios después identificación";
		self::$trad["AGORA_ldapCreaAutoUsersInfo"]="Creación automática de un usuario si no está en el Agora, pero presente en el servidor LDAP : se le asignará a los espacios accesibles a ''todos los usuarios del sitio''.<br>De lo contrario, el usuario no se creará.";
		self::$trad["AGORA_ldapPassEncrypt"]="Contraseñas cifrado en el servidor";
		self::$trad["AGORA_ldapDisabled"]="Módulo PHP para la conexión a un servidor LDAP no está instalado";

		////	MODULE_LOG
		////
		self::$trad["LOG_headerModuleName"]="Logs";
		self::$trad["LOG_moduleDescription"]="Logs - Registro de eventos";
		self::$trad["LOG_path"]="Camino";
		self::$trad["LOG_filter"]="filtro";
		self::$trad["LOG_date"]="Fecha / Hora";
		self::$trad["LOG_spaceName"]="Espacio";
		self::$trad["LOG_moduleName"]="Módulo";
		self::$trad["LOG_objectType"]="typo de objeto";
		self::$trad["LOG_action"]="Acción";
		self::$trad["LOG_userName"]="Usuario";
		self::$trad["LOG_ip"]="IP";
		self::$trad["LOG_comment"]="Comentario";
		self::$trad["LOG_noLogs"]="Ningún registro";
		self::$trad["LOG_filterSince"]="filtrado de la";
		self::$trad["LOG_search"]="Buscar";
		self::$trad["LOG_connexion"]="Conexión";//action
		self::$trad["LOG_add"]="Añadir";//action
		self::$trad["LOG_delete"]="eliminar";//action
		self::$trad["LOG_modif"]="cambio";//action

		////	MODULE_ESPACE
		////
		self::$trad["SPACE_headerModuleName"]="Espacios";
		self::$trad["SPACE_moduleInfo"]="El sitio (o el espacio principal) puede ser subdivisado en varios espacios";
		self::$trad["SPACE_manageSpaces"]="Gestión de los spacios del sitio";
		self::$trad["SPACE_config"]="Administración del espacio";
		// Index
		self::$trad["SPACE_confirmDeleteDbl"]="Confirmar eliminación ? Atención, los datos afectados a este espacio seran  definitivamente perdidas !!";
		self::$trad["SPACE_space"]="Espacio";
		self::$trad["SPACE_spaces"]="Espacios";
		self::$trad["SPACE_accessRightUndefined"]="Definir !";
		self::$trad["SPACE_modules"]="Módulos";
		self::$trad["SPACE_addSpace"]="Añadir un espacio";
		//Edit
		self::$trad["SPACE_usersAccess"]="Usuarios asignados al espacio";
		self::$trad["SPACE_selectModule"]="Debe seleccionar al menos un módulo";
		self::$trad["SPACE_spaceModules"]="Módulos del espacio";
		self::$trad["SPACE_moduleRank"]="Mover a establecer el orden de presentación de los módulos";
		self::$trad["SPACE_publicSpace"]="Espacio Público";
		self::$trad["SPACE_publicSpaceInfo"]="Da acceso a personas que no tienen una cuenta de usuario : los 'invitados'. Es posible especificar una contraseña para proteger el acceso al espacio. Los siguientes módulos serán inaccesibles para los invitados : 'mail' y 'user' (si el espacio público no tiene una contraseña)";
		self::$trad["SPACE_publicSpaceNotif"]="Si el espacio público contiene datos confidenciales, como información de contacto personal (módulo de Contacto) o documentos (módulo de Archivo): debe agregar una contraseña de acceso para cumplir con el GDPR. <Hr> El Reglamento general de protección de datos es un reglamento de la Unión Europea que constituye el texto de referencia para la protección de datos personales.";
		self::$trad["SPACE_usersInvitation"]="Los usuarios pueden enviar invitaciones por correo";
		self::$trad["SPACE_usersInvitationInfo"]="Todos los usuarios pueden enviar invitaciones por correo electrónico para unirse al espacio";
		self::$trad["SPACE_allUsers"]="Todos los usuarios";
		self::$trad["SPACE_user"]=" Usuarios";
		self::$trad["SPACE_userInfo"]="Usuario del espacio : <br> Acceso normal al espacio";
		self::$trad["SPACE_admin"]="Administrador";
		self::$trad["SPACE_adminInfo"]="Administrador del espacio : ecceso en escritura a todos los elementos del espacio + posibilidad de enviar invitaciones por correo electrónico + añadir nuevos usuarios";

		////	MODULE_UTILISATEUR
		////
		// Menu principal
		self::$trad["USER_headerModuleName"]="Usuarios";
		self::$trad["USER_moduleDescription"]="Usuarios del espacio";
		self::$trad["USER_option_allUsersAddGroup"]="Los usuarios también pueden crear grupos";
		//Index
		self::$trad["USER_allUsers"]="Ver todos los usuarios";
		self::$trad["USER_allUsersInfo"]="Todos los usuarios de todos los espacios";
		self::$trad["USER_spaceUsers"]="Usuarios del espacio";
		self::$trad["USER_deleteDefinitely"]="Eliminar definitivamente";
		self::$trad["USER_deleteFromCurSpace"]="Desasignar del espacio corriente";
		self::$trad["USER_deleteFromCurSpaceConfirm"]="Confirmar la desasignación del usuario al espacio corriente ?";
		self::$trad["USER_allUsersOnSpaceNotif"]="Todo los usuarios son asignados a este espacio";
		self::$trad["USER_user"]="Usuario";
		self::$trad["USER_users"]="usuarios";
		self::$trad["USER_addExistUser"]="Añadir un usuario existente, a ese espacio";
		self::$trad["USER_addExistUserTitle"]="Añadir al espacio a un usuario ya existente en el sitio : asignación al espacio";
		self::$trad["USER_addUser"]="Añadir un usuario";
		self::$trad["USER_addUserSite"]="Crear un usuario en el sitio : por defecto, asignado a ningun espacio !";
		self::$trad["USER_addUserSpace"]="Crear un usuario en el espacio actual";
		self::$trad["USER_sendCoords"]="Enviar el nombre de usuario y contraseña";
		self::$trad["USER_sendCoordsInfo"]="Envíe a los usuarios un correo electrónico con su Login y un enlace web para inicializar su contraseña";
		self::$trad["USER_sendCoordsInfo2"]="Enviar a cada nuevo usuario un correo electrónico con información de acceso.";
		self::$trad["USER_sendCoordsConfirm"]="Confirmar ?";
		self::$trad["USER_sendCoordsMail"]="Sus datos de acceso a su espacio";
		self::$trad["USER_noUser"]="Ningún usuario asignado a este espacio por el momento";
		self::$trad["USER_spaceList"]="Espacios del usuario";
		self::$trad["USER_spaceNoAffectation"]="Ningún espacio";
		self::$trad["USER_adminGeneral"]="Administrador General del Sitio";
		self::$trad["USER_adminSpace"]="Administrador del espacio";
		self::$trad["USER_userSpace"]="Usuario del espacio";
		self::$trad["USER_profilEdit"]="Editar el perfil";
		self::$trad["USER_myProfilEdit"]="Editar mi perfil de usuario";
		// Invitation
		self::$trad["USER_sendInvitation"]="Enviar invitaciones por email";
		self::$trad["USER_sendInvitationInfo"]="Envía invitaciones por correo electrónico a tus contactos para unirte al espacio.<hr><img src='app/img/gSignin.png' height=15> Si tienes una cuenta Gmail, también puedes enviar invitaciones a tus contactos Gmail.";
		self::$trad["USER_mailInvitationObject"]="Invitación de "; // ..Jean DUPOND
		self::$trad["USER_mailInvitationFromSpace"]="le invita a "; // Jean DUPOND "vous invite à rejoindre l'espace" Mon Espace
		self::$trad["USER_mailInvitationConfirm"]="Haga clic aquí para confirmar la invitación";
		self::$trad["USER_mailInvitationWait"]="Invitaciones a confirmar";
		self::$trad["USER_exired_idInvitation"]="La enlace de su invitación ha caducado";
		self::$trad["USER_invitPassword"]="Confirmar su invitación";
		self::$trad["USER_invitPassword2"]="Elejir su contraseña para confirmar su invitación";
		self::$trad["USER_invitationValidated"]="Su invitación ha sido validado !";
		self::$trad["USER_gPeopleImport"]="Obtener mis contactos de mi dirección de Gmail";
		self::$trad["USER_importQuotaExceeded"]="Está limitado a --USERS_QUOTA_REMAINING-- nuevas cuentas de usuario, de un total de --LIMITE_NB_USERS-- usuarios";
		// groupes
		self::$trad["USER_spaceGroups"]="grupos de usuarios del espacio";
		self::$trad["USER_spaceGroupsEdit"]="modificar los grupos de usuarios del espacio";
		self::$trad["USER_groupEditInfo"]="Cada grupo puede ser modificado por su autor o por el administrador del espacio";
		self::$trad["USER_addGroup"]="Añadir un grupo";
		self::$trad["USER_userGroups"]="Grupos del usuario";
		// Utilisateur_affecter
		self::$trad["USER_searchPrecision"]="Gracias a especificar un nombre, un apellido o una dirección de correo electrónico";
		self::$trad["USER_userAffectConfirm"]="Confirmar las asignaciónes ?";
		self::$trad["USER_userSearch"]="Buscar usuarios para añadirlo al espacio";
		self::$trad["USER_allUsersOnSpace"]="Todos los usuarios del sitio ya están asignados a este espacio";
		self::$trad["USER_usersSpaceAffectation"]="Asignar usuarios al espacio :";
		self::$trad["USER_usersSearchNoResult"]="No hay usuarios para esta búsqueda";
		// Utilisateur_edit & CO
		self::$trad["USER_langs"]="Idioma";
		self::$trad["USER_persoCalendarDisabled"]="Calendario personal desactivado";
		self::$trad["USER_persoCalendarDisabledInfo"]="Por defecto, el calendar personal esta siempre accessible al usuario, incluso si el módulo Agenda del espacio no está activado";
		self::$trad["USER_connectionSpace"]="Espacio de conexión";
		self::$trad["USER_loginAlreadyExist"]="El login/email ya existe ¡ Gracias a especificar otro !";
		self::$trad["USER_mailPresentInAccount"]="ya existe una cuenta de usuario con esta dirección de correo electrónico";
		self::$trad["USER_loginAndMailDifferent"]="Ambas direcciones de correo electrónico deben ser idénticas";
		self::$trad["USER_mailNotifObject"]="Nueva cuenta en ";  // "...sur" l'Agora machintruc
		self::$trad["USER_mailNotifContent"]="Su cuenta de usuario ha sido creada en";  // idem
		self::$trad["USER_mailNotifContent2"]="Conectar con el login y la contraseña siguientes";
		self::$trad["USER_mailNotifContent3"]="Gracias a mantener este correo electrónico para sus archivos.";
		// Utilisateur_Messenger
		self::$trad["USER_messengerEdit"]="Editar la mensajería instantánea";
		self::$trad["USER_myMessengerEdit"]="Editar mi mensajería instantánea";
		self::$trad["USER_livecounterVisibility"]="Usuarios que podran verme en línea y hablar en la mensajería instantánea";
		self::$trad["USER_livecounterDisabled"]="Mensajería desactivado (todos los usuarios no pueden verme)";
		self::$trad["USER_livecounterAllUsers"]="Todos los usuarios pueden verme";
		self::$trad["USER_livecounterSomeUsers"]="Algunos usuarios pueden verme";

		////	MODULE_TABLEAU BORD
		////
		// Menu principal + options du module
		self::$trad["DASHBOARD_headerModuleName"]="Noticias";
		self::$trad["DASHBOARD_moduleDescription"]="Noticias, Encuestas y Elementos recientes";
		self::$trad["DASHBOARD_option_adminAddNews"]="Sólo el administrador puede añadir noticias";//OPTION!
		self::$trad["DASHBOARD_option_disablePolls"]="Deshabilitar encuestas";//OPTION!
		self::$trad["DASHBOARD_option_adminAddPoll"]="Sólo el administrador puede añadir encuestas";//OPTION!
		//Index
		self::$trad["DASHBOARD_menuNews"]="Noticias";
		self::$trad["DASHBOARD_menuPolls"]="Encuestas";
		self::$trad["DASHBOARD_menuElems"]="Elementos recientes y actuales";
		self::$trad["DASHBOARD_addNews"]="Añadir una noticia";
		self::$trad["DASHBOARD_newsOffline"]="Noticias archivadas";
		self::$trad["DASHBOARD_noNews"]="No hay noticias por el momento";
		self::$trad["DASHBOARD_addPoll"]="Añadir una encuesta";
		self::$trad["DASHBOARD_pollsNotVoted"]="Encuestas actuales : no votadas";
		self::$trad["DASHBOARD_pollsNotVotedInfo"]="Mostrar solo las encuestas que aún no has votado";
		self::$trad["DASHBOARD_vote"]="Votar y ver los resultados !";
		self::$trad["DASHBOARD_voteTooltip"]="Los votos son anónimos : nadie sabrá su elección de voto";
		self::$trad["DASHBOARD_answerVotesNb"]="Votada --NB_VOTES-- veces";//55 votes (sur la réponse)
		self::$trad["DASHBOARD_pollVotesNb"]="La encuesta fue votada --NB_VOTES-- veces";
		self::$trad["DASHBOARD_pollVotedBy"]="La encuesta fue votada por";//Bibi, boby, etc
		self::$trad["DASHBOARD_noPoll"]="No hay encuesta por el momento";
		self::$trad["DASHBOARD_plugins"]="Nuevos elementos";
		self::$trad["DASHBOARD_pluginsInfo"]="Elementos creados";
		self::$trad["DASHBOARD_pluginsInfo2"]="entre";
		self::$trad["DASHBOARD_plugins_day"]="de hoy";
		self::$trad["DASHBOARD_plugins_week"]="de esta semana";
		self::$trad["DASHBOARD_plugins_month"]="del mes";
		self::$trad["DASHBOARD_plugins_previousConnection"]="desde la última conexión";
		self::$trad["DASHBOARD_pluginsNew"]="Elemento nuevo";
		self::$trad["DASHBOARD_pluginsCurrent"]="Elemento actual";
		self::$trad["DASHBOARD_pluginsTooltipRedir"]="Ver el elemento en la carpeta";
		self::$trad["DASHBOARD_pluginEmpty"]="No hay nuevos elementos sobre este periodo";
		// Edition d'Actualite/News
		self::$trad["DASHBOARD_topNews"]="Noticia importante";
		self::$trad["DASHBOARD_topNewsInfo"]="Noticia importante, en la parte superior de la lista";
		self::$trad["DASHBOARD_offline"]="Noticia archivada";
		self::$trad["DASHBOARD_dateOnline"]="En línea el";
		self::$trad["DASHBOARD_dateOnlineInfo"]="Establecer una fecha de línea automático (en línea). La noticia será 'archivada' 'en el ínterin";
		self::$trad["DASHBOARD_dateOnlineNotif"]="La noticia esta archivado en la expectativa de su línea automática";
		self::$trad["DASHBOARD_dateOffline"]="Archivar el";
		self::$trad["DASHBOARD_dateOfflineInfo"]="Fije una fecha de archivo automático (Desconectado)";
		// Edition de Sondage/Polls
		self::$trad["DASHBOARD_titleQuestion"]="Título / Pregunta";
		self::$trad["DASHBOARD_multipleResponses"]="Varias respuestas posibles para cada voto";
		self::$trad["DASHBOARD_newsDisplay"]="Mostrar con noticias (menú izquierdo)";
		self::$trad["DASHBOARD_publicVote"]="Voto público: la elección de los votantes es pública";
		self::$trad["DASHBOARD_publicVoteInfos"]="Tenga en cuenta que la votación pública puede ser una barrera para la participación en la encuesta.";
		self::$trad["DASHBOARD_dateEnd"]="Fecha de fin de la encuesta";
		self::$trad["DASHBOARD_responseList"]="Posibles respuestas";
		self::$trad["DASHBOARD_responseNb"]="Respuesta n°";
		self::$trad["DASHBOARD_addResponse"]="Añadir una respuesta";
		self::$trad["DASHBOARD_controlResponseNb"]="Por favor, especifique al menos 2 respuestas posibles";
		self::$trad["DASHBOARD_votedPollNotif"]="Atención: tan pronto como se vota la encuesta, ya no es posible cambiar el título o las respuestas";
		self::$trad["DASHBOARD_voteNoResponse"]="Por favor seleccione una respuesta";

		////	MODULE_AGENDA
		////
		// Menu principal
		self::$trad["CALENDAR_headerModuleName"]="Calendarios";
		self::$trad["CALENDAR_moduleDescription"]="Calendarios personal y calendarios compartidos";
		self::$trad["CALENDAR_option_adminAddRessourceCalendar"]="Sólo el administrador puede añadir calendarios de recursos";
		self::$trad["CALENDAR_option_adminAddCategory"]="Sólo el administrador puede añadir categorías de eventos";
		self::$trad["CALENDAR_option_createSpaceCalendar"]="Crear un calendario compartido para el espacio";
		self::$trad["CALENDAR_option_createSpaceCalendarInfo"]="El calendario tendrá el mismo nombre que el espacio. Puede ser útil si los calendarios de los usuarios están desactivados.";
		//Index
		self::$trad["CALENDAR_calsList"]="Calendarios disponibles";
		self::$trad["CALENDAR_displayAllCals"]="Ver todo los calendarios (administrador)";
		self::$trad["CALENDAR_hideAllCals"]="Ocultar todo los calendarios";
		self::$trad["CALENDAR_printCalendars"]="Imprimir el/los calendarios";
		self::$trad["CALENDAR_printCalendarsInfos"]="imprimir la página en modo horizontal";
		self::$trad["CALENDAR_addSharedCalendar"]="Añadir un calendario compartido";
		self::$trad["CALENDAR_addSharedCalendarInfo"]="Añadir un calendario compartido : para reservar une habitación, vehiculo, vídeo, etc.";
		self::$trad["CALENDAR_exportIcal"]="Exportar los eventos (iCal)";
		self::$trad["CALENDAR_exportEvtMail"]="Exportar los eventos por e-mail (iCal)";
		self::$trad["CALENDAR_exportEvtMailInfo"]="Para integrar en un calendario IPHONE, ANDROID, OUTLOOK, GOOGLE CALENDAR...";
		self::$trad["CALENDAR_exportEvtMailList"]="lista de los eventos .ical";
		self::$trad["CALENDAR_icalUrl"]="Url para acceso de lectura a la agenda (Ical)";
		self::$trad["CALENDAR_icalUrlCopy"]="Copiar esta dirección? permitirá leer este calendario desde otra aplicación.";
		self::$trad["CALENDAR_importIcal"]="Importar los eventos (iCal)";
		self::$trad["CALENDAR_importIcalState"]="Estado";
		self::$trad["CALENDAR_importIcalStatePresent"]="Ya está presente";
		self::$trad["CALENDAR_importIcalStateImport"]="a importar";
		self::$trad["CALENDAR_inputProposed"]="El evento será propuesto al propietario del calendario";
		self::$trad["CALENDAR_displayDay"]="Día";
		self::$trad["CALENDAR_display4Days"]="4 días";
		self::$trad["CALENDAR_displayWorkWeek"]="Semana de trabajo";
		self::$trad["CALENDAR_displayWeek"]="Semana";
		self::$trad["CALENDAR_displayMonth"]="Mes";
		self::$trad["CALENDAR_weekNb"]="Ver la semana n°"; //...5
		self::$trad["CALENDAR_periodNext"]="Período siguiente";
		self::$trad["CALENDAR_periodPrevious"]="Período anterior";
		self::$trad["CALENDAR_evtAffects"]="En el calendario de";
		self::$trad["CALENDAR_evtAffectToConfirm"]="Pendiente de confirmación : ";
		self::$trad["CALENDAR_evtProposedFor"]="Eventos propuestos para"; // "Videoprojecteur" / "salle de réunion" / etc.
		self::$trad["CALENDAR_evtProposedForMe"]="Eventos propuestos para mi calendario";
		self::$trad["CALENDAR_evtProposedBy"]="Propuestos por";  // "Proposé par" M. Bidule
		self::$trad["CALENDAR_evtIntegrate"]="Integrar el evento al calendario ?";
		self::$trad["CALENDAR_evtNotIntegrate"]="Eliminar el evento propuesto ?";
		self::$trad["CALENDAR_deleteEvtCal"]="Eliminar sólo en ese calendario ?";
		self::$trad["CALENDAR_deleteEvtCals"]="Eliminar en todos los calendarios ?";
		self::$trad["CALENDAR_deleteEvtDate"]="Eliminar sólo en esta fecha ?";
		self::$trad["CALENDAR_evtPrivate"]="Évento privado";
		self::$trad["CALENDAR_evtAutor"]="Eventos que he creado";
		self::$trad["CALENDAR_noEvt"]="No hay eventos";
		self::$trad["CALENDAR_synthese"]="Síntesis de los calendarios";
		self::$trad["CALENDAR_calendarsPercentBusy"]="Calendarios ocupados";  // Agendas occupés : 2/5
		self::$trad["CALENDAR_noCalendarDisplayed"]="No calendario";
		// Evenement
		self::$trad["CALENDAR_category"]="Categoría";
		self::$trad["CALENDAR_visibilityPublic"]="Visibilidad normal";
		self::$trad["CALENDAR_visibilityPublicHide"]="Visibilidad semi-privada";
		self::$trad["CALENDAR_visibilityPrivate"]="Visibilidad privada";
		self::$trad["CALENDAR_visibilityInfo"]="<u>Visibilidad semi-privada</u> : solo muestrar el período del evento (sin los detailles) si el evento es accesible de lectura.<br><br><u>Visibilidad privada</u> : visible sólo si el evento es accesible en escritura.";
		//  Agenda : edit
		self::$trad["CALENDAR_timeSlot"]="Rango de tiempo de la pantalla ''semana''";
		// Evenement : edit
		self::$trad["CALENDAR_noPeriodicity"]="Evento puntual";
		self::$trad["CALENDAR_period_weekDay"]="Cada semana";
		self::$trad["CALENDAR_period_month"]="Cada mes";
		self::$trad["CALENDAR_period_dayOfMonth"]="del mes"; // Le 21 du mois
		self::$trad["CALENDAR_period_year"]="Cada año";
		self::$trad["CALENDAR_periodDateEnd"]="Fin de periodicidad";
		self::$trad["CALENDAR_periodException"]="Excepción de periodicidad";
		self::$trad["CALENDAR_calendarAffectations"]="Asignación a los calendarios";
		self::$trad["CALENDAR_addEvt"]="Añadir un evento";
		self::$trad["CALENDAR_addEvtTooltip"]="Añadir un evento";
		self::$trad["CALENDAR_addEvtTooltipBis"]="Añadir el evento al calendario";
		self::$trad["CALENDAR_proposeEvtTooltip"]="Proponer un evento al propietario del calendario";
		self::$trad["CALENDAR_proposeEvtTooltipBis"]="Proponer el evento al propietario del calendario";
		self::$trad["CALENDAR_proposeEvtTooltipBis2"]="Proponer el evento al propietario del calendario : calendario accesible solo en lectura";
		self::$trad["CALENDAR_verifCalNb"]="Gracias por seleccionar por lo menos un calendario";
		self::$trad["CALENDAR_noModifInfo"]="Edición prohibida porque no tiene acceso de escritura al calendario";
		self::$trad["CALENDAR_editLimit"]="Usted no es el autor de el evento : sólo puedes editar las asignaciones a sus calendarios";
		self::$trad["CALENDAR_busyTimeslot"]="La ranura ya está ocupado en este calendario :";
		// Categories
		self::$trad["CALENDAR_editCategories"]="Administrar las categorías de eventos";
		self::$trad["CALENDAR_editCategoriesRight"]="Cada categoría puede ser modificado por su autor o por el administrador general";
		self::$trad["CALENDAR_addCategory"]="Añadir una categoría";
		self::$trad["CALENDAR_filterByCategory"]="Ver los eventos por categoría";

		////	MODULE_FICHIER
		////
		// Menu principal
		self::$trad["FILE_headerModuleName"]="Archivos";
		self::$trad["FILE_moduleDescription"]="Administración de Archivos";
		self::$trad["FILE_option_adminRootAddContent"]="Sólo el administrador puede añadir elementos en el directorio raíz";
		//Index
		self::$trad["FILE_addFile"]="Añadir archivos";
		self::$trad["FILE_addFileAlert"]="Los directorios del servidor no son accesible en escritura !  gracias de contactar el administrador";
		self::$trad["FILE_downloadSelection"]="Descargar selección";
		self::$trad["FILE_nbFileVersions"]="Archivo versiones";//"55 versions du fichier"
		self::$trad["FILE_downloadsNb"]="Archivo descargado --NB_DOWNLOAD-- veces";
		self::$trad["FILE_downloadedBy"]="Archivo subido por";//"..boby, will"
		self::$trad["FILE_addFileVersion"]="Añadir nueva versión del archivo";
		self::$trad["FILE_noFile"]="No hay archivo en este momento";
		// fichier_edit_ajouter  &  Fichier_edit
		self::$trad["FILE_fileSizeLimit"]="Los archivos no deben exceder"; // ...2 Mega Octets
		self::$trad["FILE_uploadSimple"]="Formulario simple";
		self::$trad["FILE_uploadMultiple"]="Formulario multiple";
		self::$trad["FILE_imgReduce"]="Optimizar la imagen";
		self::$trad["FILE_updatedName"]="El nombre del archivo será reemplazado por la nueva versión";
		self::$trad["FILE_fileSizeError"]="Archivo demasiado grande";
		self::$trad["FILE_addMultipleFilesInfo"]="Pulse 'Maj' o 'Ctrl' para seleccionar varios archivos";
		self::$trad["FILE_selectFile"]="Gracias por elegir al menos un archivo";
		self::$trad["FILE_fileContent"]="contenido";
		// Versions_fichier
		self::$trad["FILE_versionsOf"]="Versiones de"; // versions de fichier.gif
		self::$trad["FILE_confirmDeleteVersion"]="Confirme la eliminación de esta versión ?";

		////	MODULE_FORUM
		////
		// Menu principal
		self::$trad["FORUM_headerModuleName"]="Foro";
		self::$trad["FORUM_moduleDescription"]="Foro";
		self::$trad["FORUM_option_adminAddSubject"]="Sólo el administrador puede añadir sujetos";
		self::$trad["FORUM_option_allUsersAddTheme"]="Los usuarios también pueden añadir temas";
		// TRI
		self::$trad["SORT_dateLastMessage"]="último mensaje";
		//Index & Sujet
		self::$trad["FORUM_subject"]="sujeto";
		self::$trad["FORUM_subjects"]="sujetos";
		self::$trad["FORUM_message"]="mensaje";
		self::$trad["FORUM_messages"]="mensajes";
		self::$trad["FORUM_lastSubject"]="último sujetos de";
		self::$trad["FORUM_lastMessage"]="último mensaje de";
		self::$trad["FORUM_noSubject"]="Sin sujeto por el momento";
		self::$trad["FORUM_noMessage"]="Sin mensaje por el momento";
		self::$trad["FORUM_subjectBy"]="sujeto de";
		self::$trad["FORUM_addSubject"]="Nuevo sujeto";
		self::$trad["FORUM_displaySubject"]="Ver el sujeto";
		self::$trad["FORUM_addMessage"]="Responder";
		self::$trad["FORUM_quoteMessage"]="Responder y citar a ese mensaje";
		self::$trad["FORUM_notifyLastPost"]="Notificar por e-mail";
		self::$trad["FORUM_notifyLastPostInfo"]="Deseo recibir una notificación por correo a cada nuevo mensaje";
		// Sujet_edit  &  Message_edit
		self::$trad["FORUM_accessRightInfos"]="Atención: el acceso a la lectura no permite participar en la discusión. Por lo tanto, prefiero el acceso de escritura limitado. El acceso de escritura debe reservarse para moderadores";
		self::$trad["FORUM_themeSpaceAccessInfo"]="El tema está disponible en los espacios";
		// Themes
		self::$trad["FORUM_subjectTheme"]="Tema";
		self::$trad["FORUM_subjectThemes"]="Temas";
		self::$trad["FORUM_forumRoot"]="Inicio del foro";
		self::$trad["FORUM_forumRootResp"]="Inicio";
		self::$trad["FORUM_noTheme"]="Sin tema";
		self::$trad["FORUM_editThemes"]="Gestión de los temas";
		self::$trad["FORUM_editThemesInfo"]="Cada tema puede ser modificado por su autor o por el administrador general";
		self::$trad["FORUM_addTheme"]="Añadir un tema";

		////	MODULE_TACHE
		////
		// Menu principal
		self::$trad["TASK_headerModuleName"]="Tareas";
		self::$trad["TASK_moduleDescription"]="Tareas";
		self::$trad["TASK_option_adminRootAddContent"]="Sólo el administrador puede añadir elementos en el directorio raíz";
		// TRI
		self::$trad["SORT_priority"]="Prioridad";
		self::$trad["SORT_advancement"]="Progreso";
		self::$trad["SORT_dateBegin"]="Fecha de inicio";
		self::$trad["SORT_dateEnd"]="Fecha de fin";
		//Index
		self::$trad["TASK_addTask"]="Añadir una tareas";
		self::$trad["TASK_noTask"]="No hay tarea por el momento";
		self::$trad["TASK_advancement"]="Progreso";
		self::$trad["TASK_advancementAverage"]="Progreso promedio";
		self::$trad["TASK_priority"]="Prioridad";
		self::$trad["TASK_priority1"]="Baja";
		self::$trad["TASK_priority2"]="promedia";
		self::$trad["TASK_priority3"]="alta";
		self::$trad["TASK_priority4"]="Crítica";
		self::$trad["TASK_responsiblePersons"]="Responsables";
		self::$trad["TASK_advancementLate"]="Progreso retrasado";

		////	MODULE_CONTACT
		////
		// Menu principal
		self::$trad["CONTACT_headerModuleName"]="Contactos";
		self::$trad["CONTACT_moduleDescription"]="Directorio de contactos";
		self::$trad["CONTACT_option_adminRootAddContent"]="Sólo el administrador puede añadir elementos en el directorio raíz";
		//Index
		self::$trad["CONTACT_addContact"]="Añadir un contacto";
		self::$trad["CONTACT_noContact"]="No hay contacto todavía";
		self::$trad["CONTACT_createUser"]="Crear un usuario en este espacio";
		self::$trad["CONTACT_createUserInfo"]="Crear un usuario en este espacio con este contacto ?";
		self::$trad["CONTACT_createUserConfirm"]="El usuario fue creado";

		////	MODULE_LIEN
		////
		// Menu principal
		self::$trad["LINK_headerModuleName"]="Favoritos";
		self::$trad["LINK_moduleDescription"]="Favoritos";
		self::$trad["LINK_option_adminRootAddContent"]="Sólo el administrador puede añadir elementos en el directorio raíz";
		//Index
		self::$trad["LINK_addLink"]="Añadir un enlace";
		self::$trad["LINK_noLink"]="No hay enlaces por el momento";
		// lien_edit & dossier_edit
		self::$trad["LINK_adress"]="Dirección web";

		////	MODULE_MAIL
		////
		// Menu principal
		self::$trad["MAIL_headerModuleName"]="Emails";
		self::$trad["MAIL_moduleDescription"]="Enviar mensajes de correo electrónico con un solo clic !";
		//Index
		self::$trad["MAIL_specifyMail"]="Gracias especificar al menos un destinatario";
		self::$trad["MAIL_title"]="Título del correo electrónico";
		self::$trad["MAIL_attachedFile"]="Archivo adjunto";
		// Historique Email
		self::$trad["MAIL_mailHistory"]="Historia de los correos electrónicos enviados";
		self::$trad["MAIL_mailHistoryEmpty"]="No correo electrónico";
		self::$trad["MAIL_recipients"]="Destinatarios";
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