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
		setlocale(LC_TIME, "pt_PT.utf8", "pt_PT.UTF-8", "pt_PT", "pt", "portuguese");

		////	TRADUCTIONS
		self::$trad=array(
			////	Langue courante / Header http / Editeurs Tinymce / Documention pdf
			"CURLANG"=>"pt",
			"DATELANG"=>"pt_PT",
			"EDITORLANG"=>"pt_PT",
			"DOCFILE"=>"docs/DOCUMENTATION_EN.pdf",

			////	Divers
			"mainMenu"=>"Menu principal",
			"menuOptions"=>"Menu de opções disponíveis",
			"fillFieldsForm"=>"Por favor, preencha os campos de formulário",
			"requiredFields"=>"Campo obrigatório",
			"inaccessibleElem"=>"Elemento inacessível",
			"warning"=>"Atenção",
			"elemEditedByAnotherUser"=>"O item está sendo editado por",//"..bob"
			"yes"=>"sim",
			"no"=>"não",
			"none"=>"não",
			"or"=>"ou",
			"and"=>"e",
			"goToPage"=>"Ir para a página",
			"alphabetFilter"=>"Filtro alfabético",
			"displayAll"=>"Mostrar tudo",
			"show"=>"Mostrar",
			"hide"=>"Ocultar",
			"byDefault"=>"Por padrão",
			"changeOrder"=>"Mover para estabelecer a ordem de apresentação dos módulos",
			"mapLocalize"=>"Localizar no mapa",
			"mapLocalizationFailure"=>"Falha de localização do seguinte endereço",
			"mapLocalizationFailure2"=>"Verifique se o endereço existe em www.google.com/maps ou www.opersetmap.org",
			"sendMail"=>"enviar um e-mail",
			"mailInvalid"=>"E-mail não é válido",
			"element"=>"item",
			"elements"=>"itens",
			"folder"=>"pasta",
			"folders"=>"pastas",
			"close"=>"Fechar",
			"confirmCloseForm"=>"Você quer fechar o formulário?",
			"modifRecorded"=>"As mudanças foram registradas",
			"confirm"=>"Confirmar?",
			"comment"=>"Comentário",
			"commentAdd"=>"Adicione um comentário",
			"optional"=>"(opcional)",
			"objNew"=>"Elemento criado recentemente",
			"personalAccess"=>"Acesso pessoal",
			"copyUrl"=>"Copie o endereço web (URL)",
			"copyUrlTooltip"=>"Permite o acesso externo ao elemento : a partir de uma notícia, de um e-mail, de uma mensagem de fórum, de um blogue, etc.",
			"copyUrlConfirmed"=>"O endereço da web foi copiado corretamente.",
			"cancel"=>"Cancelar",

			////	imagens
			"picture"=>"Foto",
			"pictureProfil"=>"Foto de perfil",
			"wallpaper"=>"papel de parede",
			"keepImg"=>"Manter a imagem",
			"changeImg"=>"Mudar a imagem",
			"pixels"=>"Pixels",

			////	Conexão
			"specifyLoginPassword"=>"Por favor, informe um nome de usuário e senha",
			"specifyLogin"=>"Por favor, informe um E-mail/Identificador (sem espaço)",
			"mailLloginNotif"=>"Recomenda-se usar um e-mail como identificador de sessão",
			"mailLlogin"=>"E-mail / Login ID",
			"connect"=>"Entrar",
			"connectAuto"=>"Lembre de mim",
			"connectAutoTooltip"=>"Lembre-se do meu nome de usuário e senha para uma conexão automática",
			"gIdentityUserUnknown"=>"Não está registrado no espaço",
			"connectSpaceSwitch"=>"Conecte-se a outro espaço",
			"connectSpaceSwitchConfirm"=>"Tem certeza de que deseja deixar este espaço para se conectar a outro espaço?",
			"guestAccess"=>"Faça login como convidado",
			"guestAccessTooltip"=>"Faça login em um espaço como o convidado",
			"publicSpacePasswordError"=>"Senha incorreta",
			"disconnectSpace"=>"Fechar Sessão",
			"disconnectSpaceConfirm"=>"Confirmar a desconexão do espaço?",

			////	Password : connexion d'user / edition d'user / reset du password
			"password"=>"Senha",
			"passwordModify"=>"Alterar a senha",
			"passwordToModify"=>"Senha temporária (para alterar no login)",//Mail d'envoi d'invitation
			"passwordToModify2"=>"Senha (altere se necessário)",//Mail de création de compte
			"passwordVerif"=>"confirmar senha",
			"passwordTooltip"=>"Deixe em branco se quiser manter sua senha",
			"passwordInvalid"=>"Sua senha deve conter números, letras e pelo menos 6 caracteres",
			"passwordConfirmError"=>"Sua senha de confirmação não é válida",
			"specifyPassword"=>"Por favor, informe uma senha",
			"resetPassword"=>"Recuperar acesso",
			"resetPassword2"=>"Digite seu endereço de e -mail para receber seus dados de acesso",
			"resetPasswordNotif"=>"Você acabou de enviar um e-mail para o seu endereço para restaurar sua senha. Se não recebeu um e-mail, verifique se o endereço especificado está correto ou se o e-mail não está na sua caixa de SPAM.",
			"resetPasswordMailTitle"=>"Redefina sua senha",
			"resetPasswordMailPassword"=>"Para fazer login no seu Omnispace e restaurar sua senha",
			"resetPasswordMailPassword2"=>"Clique aqui",
			"resetPasswordMailLoginRemind"=>"Lembrete do seu login",
			"resetPasswordIdExpired"=>"O link da web para recuperar a senha expirou... Por favor, reinicie o procedimento",

			////	Type d'affichage
			"displayMode"=>"Visualização",
			"displayMode_line"=>"Lista",
			"displayMode_block"=>"Bloco",

			////	Sélectionner / Déselectionner tous les éléments
			"select"=>"Selecionar",
			"selectUnselect"=>"Marcar / Desmarcar",
			"selectAll"=>"Selecionar tudo",
			"selectNone"=>"Desmarcar tudo",
			"selectSwitch"=>"Alternar seleção",
			"deleteElems"=>"Remover os itens selecionados",
			"changeFolder"=>"Mover em outra pasta",
			"showOnMap"=>"Mostrar em um mapa",
			"showOnMapTooltip"=>"Veja em um mapa os contatos com um endereço, código postal, cidade",
			"notifSelectUser"=>"Obrigado por selecionar um usuário",
			"notifSelectUsers"=>"Obrigado por selecionar pelo menos 2 usuários",
			"selectSpace"=>"Obrigado por selecionar pelo menos um espaço",
			"visibleAllSpaces"=>"Visível em todos os espaços",/*cf. Categories, themes, etc*/
			"visibleOnSpace"=>"Visível no espaço",/*"..Mon espace"*/
			
			////	Temps ("de 11h à 12h", "le 25-01-2007 à 10h30", etc.)
			"from"=>"de ",
			"at"=>"para",
			"the"=>"o",
			"begin"=>"Começo",
			"end"=>"Fim",
			"beginEnd"=>"Começo / Fim",
			"days"=>"dias",
			"day_1"=>"Segunda-feira",
			"day_2"=>"Terça-feira",
			"day_3"=>"Quarta-feira",
			"day_4"=>"Quinta-feira",
			"day_5"=>"Sexta-feira",
			"day_6"=>"Sábado",
			"day_7"=>"Domingo",
			"month_1"=>"Janeiro",
			"month_2"=>"Fevereiro",
			"month_3"=>"Março",
			"month_4"=>"Abril",
			"month_5"=>"Maio",
			"month_6"=>"Junho",
			"month_7"=>"Julho",
			"month_8"=>"Agosto",
			"month_9"=>"Setembro",
			"month_10"=>"Outubro",
			"month_11"=>"Novembro",
			"month_12"=>"Dezembro",
			"today"=>"Hoje",
			"beginEndError"=>"A data final não pode ser antes da data de início",
			"dateFormatError"=>"A data deve estar no formato dd/mm/aaaa",
			
			////	Menus d'édition des objets et editeur tinyMce
			"title"=>"Título",
			"name"=>"Nome",
			"description"=>"Descrição",
			"specifyName"=>"Obrigado por especificar um nome",
			"editorDraft"=>"Recuperar meu texto",
			"editorDraftConfirm"=>"Recuperar o último texto especificado",
			"editorFileInsert"=>"Adicionar imagem ou vídeo",
			"editorFileInsertNotif"=>"Selecione uma imagem em formato JPEG, PNG, GIF ou SVG",
			
			////	Validation des formulaires
			"add"=>"Adicionar",
			"modify"=>"Modificar",
			"record"=>"Gravar",
			"modifyAndAccesRight"=>"Modificar e definir acesso",
			"validate"=>"Validar",
			"send"=>"Enviar",
			"sendTo"=>"Enviar para",
			
			////	Tri d'affichage. Tous les elements (dossier, tache, lien, etc...) ont par défaut une date, un auteur & une description
			"sortBy"=>"Classificado por",
			"sortBy2"=>"Ordenar por",
			"SORT_dateCrea"=>"data de criação",
			"SORT_dateModif"=>"Data de modificação",
			"SORT_title"=>"título",
			"SORT_description"=>"descrição",
			"SORT__idUser"=>"autor",
			"SORT_extension"=>"tipo de arquivo",
			"SORT_octetSize"=>"tamanho",
			"SORT_downloadsNb"=>"transferências",
			"SORT_civility"=>"título",
			"SORT_name"=>"sobrenome",
			"SORT_firstName"=>"primeiro nome",
			"SORT_adress"=>"endereço",
			"SORT_postalCode"=>"CEP",
			"SORT_city"=>"cidade",
			"SORT_country"=>"país",
			"SORT_function"=>"função",
			"SORT_companyOrganization"=>"Empresa / Organização",
			"SORT_lastConnection"=>"Último Login",
			"tri_ascendant"=>"Ascendente",
			"tri_descendant"=>"Descendente",
			
			////	Options de suppression
			"confirmDelete"=>"Você quer excluir permanentemente esse item?",
			"confirmDeleteDbl"=>"Esta ação é definitiva: deseja continuar?",
			"confirmDeleteSelect"=>"Você quer excluir permanentemente a seleção?",
			"confirmDeleteSelectNb"=>"itens selecionados",//"55 éléments sélectionnés"
			"confirmDeleteFolderAccess"=>"Cuidado! Certas sub-pastas não são acessíveis para você: eles serão excluídos!",
			"notifyBigFolderDelete"=>"Deletar as sub-pastas --NB_FOLDERS-- pode demorar, aguarde alguns momentos antes do final do processo",
			"delete"=>"Deletar",
			"notDeletedElements"=>"Alguns itens não foram excluídos porque você não tem os direitos de acesso necessários",
			
			////	Visibilité d'un Objet : auteur et droits d'accès
			"autor"=>"Autor",
			"postBy"=>"Postado por",
			"guest"=>"Convidado",
			"creation"=>"Criação",
			"modification"=>"Modificação",
			"createBy"=>"Criado por",
			"modifBy"=>"Modificado por",
			"objHistory"=>"História do item",
			"all"=>"todos",
			"all2"=>"todos",
			"deletedUser"=>"conta de usuário excluída",
			"folderContent"=>"conteúdo",
			"accessRead"=>"Ler",
			"accessReadTooltip"=>"Acesso na leitura",
			"accessWriteLimit"=>"Escrita limitada",
			"accessWriteLimitTooltip"=>"Acesso de gravação limitado: é possível adicionar -OBJCONTENT- em --OBJLABEL--,<br> mas cada usuário pode apenas modificar/excluir o -OBJCONTENT- que ele criou.",
			"accessWrite"=>"escrita",
			"accessWriteTooltip"=>"Acesso à escrita",
			"accessWriteTooltipContainer"=>"Acesso à escrita: Possibilidade de adicionar, modificar ou suprimir todos os -OBJCONTENT- do --OBJLABEL--",
			"accessAutorPrivilege"=>"Somente o autor e os administradores podem alterar as licenças de acesso ou eliminar --OBJLABEL--",
			"accessRightsInherited"=>"Direitos de acesso herdado de --OBJLABEL--",
			"categoryNotifSpaceAccess"=>"só é acessível no espaço",//Ex: "Thème bidule -n'est accessible que sur l'espace- Machin"
			"categoryNotifChangeOrder"=>"A ordem de visualização foi alterada.",

			////	Libellé des objets
			"OBJECTcontainer"=>"recipiente",
			"OBJECTelement"=>"item",
			"OBJECTfolder"=>"pasta",
			"OBJECTdashboardNews"=>"notícias",
			"OBJECTdashboardPoll"=>"enquete",
			"OBJECTfile"=>"arquivo",
			"OBJECTfileFolder"=>"pasta",
			"OBJECTcalendar"=>"calendário",
			"OBJECTcalendarEvent"=>"evento",
			"OBJECTforumSubject"=>"tópico",
			"OBJECTforumMessage"=>"mensagem",
			"OBJECTcontact"=>"contato",
			"OBJECTcontactFolder"=>"pasta",
			"OBJECTlink"=>"favorito",
			"OBJECTlinkFolder"=>"pasta",
			"OBJECTtask"=>"tarefa",
			"OBJECTtaskFolder"=>"pasta",
			"OBJECTuser"=>"usuário",

			////	Envoi d'un e-mail (nouvel utilisateur, notification de création d'objet, etc...)
			"MAIL_sendOk"=>"O e-mail foi enviado com sucesso",			//ne pas modifier la cle de la trad ! (cf. "Tool::sendMail()")
			"MAIL_sendNotOk"=>"Não foi possível enviar o e-mail...",	//Idem
			"MAIL_recipients"=>"Destinatários",							//Idem
			"MAIL_attachedFileError"=>"O arquivo não foi adicionado ao e-mail porque é muito grande",//Idem
			"MAIL_hello"=>"Olá",
			"MAIL_hideRecipients"=>"Ocultar destinatários",
			"MAIL_hideRecipientsTooltip"=>"Coloque todos os destinatários em uma cópia oculta. Observe que, com esta opção, seu e-mail pode chegar como SPAM em alguns mensageiros",
			"MAIL_addReplyTo"=>"Coloque meu e-mail em resposta",
			"MAIL_addReplyToTooltip"=>"Adicione meu e-mail no campo 'Responder a'. Observe que, com esta opção, seu e-mail pode chegar como SPAM em alguns mensageiros",
			"MAIL_noFooter"=>"Não assine a mensagem",
			"MAIL_noFooterTooltip"=>"Não assine o final da mensagem com o nome do remetente e um link para o espaço",
			"MAIL_receptionNotif"=>"Confirmação de entrega",
			"MAIL_receptionNotifTooltip"=>"Aviso! Alguns clientes de e-mail não oferecem suporte ao recibo de entrega",
			"MAIL_specificMails"=>"Adicionar endereços de e-mail",
			"MAIL_specificMailsTooltip"=>"Adicionar endereços de e-mail não listados no espaço",
			"MAIL_fileMaxSize"=>"Todos os seus anexos não devem exceder 15 MB, alguns serviços de mensagens podem rejeitar e-mails além desse limite. Enviar mesmo assim?",
			"MAIL_sendButton"=>"Enviar e-mail",
			"MAIL_sendBy"=>"Enviado por",//"Envoyé par" M. Trucmuche
			"MAIL_sendNotif"=>"O e-mail de notificação foi enviado!",
			"MAIL_fromTheSpace"=>"Do espaço",//"depuis l'espace Bidule"
			"MAIL_elemCreatedBy"=>"--OBJLABEL-- criado por",//boby
			"MAIL_elemModifiedBy"=>"--OBJLABEL-- modificado por",//boby
			"MAIL_elemAccessLink"=>"Clique aqui para acessar o item no espaço",

			////	Dossier & fichier
			"gigaOctet"=>"GB",
			"megaOctet"=>"MB",
			"kiloOctet"=>"KB",
			"rootFolder"=>"Diretório raiz",
			"rootFolderTooltip"=>"Abra a configuração do espaço para alterar os direitos de acesso à pasta raiz",
			"addFolder"=>"Adicionar um diretório",
			"download"=>"Baixar arquivos",
			"downloadFolder"=>"Baixar a pasta",
			"diskSpaceUsed"=>"Espaço usado",
			"diskSpaceUsedModFile"=>"Espaço usado para arquivos",
			"downloadAlert"=>"Seu arquivo é grande demais para baixá-lo durante o dia (--ARCHIVE_SIZE--). Reinicie o download depois de",//"19h"
			
			////	Infos sur une personne
			"civility"=>"Civilidade",
			"name"=>"Apelido",
			"firstName"=>"Nome",
			"adress"=>"Endereço",
			"postalCode"=>"Código postal",
			"city"=>"Cidade",
			"country"=>"País",
			"telephone"=>"Telefone",
			"telmobile"=>"Celular",
			"mail"=>"E-mail",
			"function"=>"Função",
			"companyOrganization"=>"Empresa / Organização",
			"lastConnection"=>"Última conexão",
			"lastConnection2"=>"Conectado",
			"lastConnectionEmpty"=>"Não está conectado",
			"displayProfil"=>"Ver perfil",
			
			////	Captcha
			"captcha"=>"Copiar os 5 caracteres",
			"captchaTooltip"=>"Escreva os 5 caracteres para identificação",
			"captchaError"=>"A identificação visual não é válida",
			
			////	Rechercher
			"searchSpecifyText"=>"Especifique pelo menos 3 caracteres (alfanumérico e sem caracteres especiais)",
			"search"=>"Buscar",
			"searchDateCrea"=>"Data de criação",
			"searchDateCreaDay"=>"menos de um dia",
			"searchDateCreaWeek"=>"menos de uma semana",
			"searchDateCreaMonth"=>"menos de um mês",
			"searchDateCreaYear"=>"menos de um ano",
			"searchOnSpace"=>"Buscar pelo espaço",
			"advancedSearch"=>"Busca avançada",
			"advancedSearchAnyWord"=>"qualquer palavra",
			"advancedSearchAllWords"=>"todas as palavras",
			"advancedSearchExactPhrase"=>"Frase exata",
			"keywords"=>"Palavras-chave",
			"listModules"=>"Módulos",
			"listFields"=>"Campos",
			"listFieldsElems"=>"Elementos envolvidos",
			"noResults"=>"Não há resultados",
			
			////	Inscription d'utilisateur
			"userInscription"=>"Registre-se no espaço",
			"userInscriptionTooltip"=>"Criar uma nova conta de usuário (validada por um administrador)",
			"userInscriptionSpace"=>"Registre-se no espaço",
			"userInscriptionRecorded"=>"Seu registro será validado o mais rápido possível pelo administrador do espaço",
			"userInscriptionE-mailSubject"=>"Novo registro no espaço",//"Mon espace"
			"userInscriptionE-mailMessage"=>"<i>--NEW_USER_LABEL--</i> solicitou um novo registro para o espaço <i>--SPACE_NAME--</i> : <br><br><i>--NEW_USER_MESSAGE--<i> <br><br>Lembre-se de validar ou invalidar esse registro durante sua próxima conexão.",
			"userInscriptionEdit"=>"Permitir que os visitantes se registrem no espaço",
			"userInscriptionEditTooltip"=>"O registro está na página inicial. Deve ser validado pelo administrador do espaço.",
			"userInscriptionNotif"=>"Notificar por e-mail em cada registro",
			"userInscriptionNotifTooltip"=>"Envie uma notificação por e-mail aos administradores de espaço, após cada registro",
			"userInscriptionPulsate"=>"Registros",
			"userInscriptionValidate"=>"Registros de usuários",
			"userInscriptionValidateTooltip"=>"Validar registros de usuário para o espaço",
			"userInscriptionSelectValidate"=>"Validar registros",
			"userInscriptionSelectInvalidate"=>"Invalidar registros",
			"userInscriptionInvalidateMail"=>"Sua conta não foi validada em",
			
			////	Importer ou Exporter : Contact OU Utilisateurs
			"importExport_user"=>"Importar / Exportar usuários",
			"import_user"=>"Importar usuários no espaço atual",
			"export_user"=>"Exportar usuários do espaço",
			"importExport_contact"=>"Importar / Exportar contatos",
			"import_contact"=>"Importar contatos na pasta atual",
			"export_contact"=>"Exportar contatos da pasta atual",
			"exportFormat"=>"formato",
			"specifyFile"=>"Por favor, especifique um arquivo",
			"fileExtension"=>"O tipo de arquivo não é válido. Deve ser do tipo",
			"importContactRootFolder"=>"Os contatos serão atribuídos por padrão a &quot;todos os usuários do espaço&quot;",//"Mon espace"
			"importInfo"=>"Selecione os campos (Agora) do destino com as listas de suspensão de cada coluna.",
			"importNotif1"=>"Por favor, selecione a coluna de nome nas listas de suspensão",
			"importNotif2"=>"Por favor, selecione pelo menos um contato para importar",
			"importNotif3"=>"O campo Agora já foi selecionado em outra coluna (cada campo Agora pode ser selecionado apenas uma vez)",
			
			////	Messages d'erreur / Notifications
			"NOTIF_identification"=>"Nome de usuário ou senha inválida",
			"NOTIF_presentIp"=>"Esta conta de usuário está sendo usada atualmente em outro computador, com outro endereço IP",
			"NOTIF_noAccessNoSpaceAffected"=>"Sua conta de usuário foi identificada corretamente, mas atualmente não está atribuída a nenhum espaço. Entre em contato com o administrador",
			"NOTIF_noAccess"=>"Você não está logado",
			"NOTIF_fileOrFolderAccess"=>"O arquivo ou pasta não está disponível",
			"NOTIF_diskSpace"=>"O espaço para armazenar seus arquivos não é suficiente, você não pode adicionar arquivos",
			"NOTIF_fileVersionForbidden"=>"Tipo de arquivo não permitido",
			"NOTIF_fileVersion"=>"Tipo de arquivo diferente do original",
			"NOTIF_folderMove"=>"Você não pode mover a pasta para dentro de si mesma!",
			"NOTIF_duplicateName"=>"Um item com o mesmo nome já existe",
			"NOTIF_fileName"=>"Um arquivo com o mesmo nome já existe (não foi substituído)",
			"NOTIF_chmodDATAS"=>"O diretório 'DATAS' não permite acesso de escrita. Você precisa dar um acesso de leitura e escrita para o proprietário e o grupo (''chmod 775'').",
			"NOTIF_usersNb"=>"Você não pode adicionar um novo usuário: é limitado a ", // "...limité à" 10
			
			////	Header / Footer
			"HEADER_displaySpace"=>"Espaços de trabalho",
			"HEADER_displayAdmin"=>"Visualização de Administrador",
			"HEADER_displayAdminEnabled"=>"Visualização de Administrador ativada",
			"HEADER_displayAdminInfo"=>"Esta opção também lhe permite mostrar itens de espaço que não são atribuídos a você",
			"HEADER_searchElem"=>"Buscar no espaço",
			"HEADER_documentation"=>"Documentação",
			"HEADER_shortcuts"=>"Acesso direto",
			"FOOTER_pageGenerated"=>"página gerada em",

			////	Messenger / Visio
			"MESSENGER_headerModuleName"=>"Mensagens",
			"MESSENGER_moduleDescription"=>"Mensagens instantâneas: Bate-papo ao vivo ou videoconferência com pessoas conectadas ao espaço",
			"MESSENGER_messengerTitle"=>"Mensagens instantâneas: clique no nome de uma pessoa para conversar ou iniciar uma videoconferência",
			"MESSENGER_messengerMultiUsers"=>"Converse com outras pessoas selecionando meus interlocutores no painel direito",
			"MESSENGER_connected"=>"Conectado",
			"MESSENGER_nobody"=>"Atualmente, você é a única pessoa conectada ao espaço.",
			"MESSENGER_messageFrom"=>"Mensagem de",
			"MESSENGER_messageTo"=>"enviado a",
			"MESSENGER_chatWith"=>"Conversar com",
			"MESSENGER_addMessageToSelection"=>"Minha mensagem (pessoas selecionadas)",
			"MESSENGER_addMessageTo"=>"Minha mensagem a",
			"MESSENGER_addMessageNotif"=>"Especifique uma mensagem",
			"MESSENGER_visioProposeTo"=>"Enviar uma videochamada para",//..boby
			"MESSENGER_visioProposeToSelection"=>"Envie uma videochamada para pessoas selecionadas",
			"MESSENGER_visioProposeToUsers"=>"Clique aqui para iniciar a videochamada com",//"..Will & Boby"
			
			////	Lancer une Visio
			"VISIO_urlAdd"=>"Adicionar uma videoconferência",
			"VISIO_urlCopy"=>"Copiar o link de videoconferência",
			"VISIO_urlDelete"=>"Eliminar o link de videoconferência",
			"VISIO_launch"=>"Iniciar a videochamada",
			"VISIO_launchFromEvent"=>"Iniciar a videoconferência deste evento",
			"VISIO_urlMail"=>"Adicionar um link ao final do texto para iniciar uma nova videoconferência",
			"VISIO_launchTooltip"=>"Lembre-se de permitir o acesso à sua webcam e microfone",
			"VISIO_launchTooltip2"=>"Clique aqui se tiver problemas para começar a videoconferência",
			"VISIO_installJitsi"=>"Instale o aplicativo gratuito Jitsi para iniciar suas videoconferências",
			"VISIO_launchServerTooltip"=>"Escolha o servidor secundário se o servidor principal não funcionar corretamente.<br>Seus contatos devem selecionar o mesmo servidor de vídeo.",
			"VISIO_launchServerMain"=>"Servidor principal",
			"VISIO_launchServerAlt"=>"Servidor secundário",
			"VISIO_launchButton"=>"Iniciar a videochamada",

			////	VueObjEditMenuSubmit.php
			"EDIT_notifNoSelection"=>"Você deve selecionar ao menos uma pessoa ou um espaço",
			"EDIT_notifNoPersoAccess"=>"Você não foi designado para o item. Validar todos mesmo assim?",
			"EDIT_parentFolderAccessError"=>"Verifique os direitos de acesso da pasta principal <br><i>--FOLDER_NAME--</i><br><br> Também deve haver um direito de acesso a <br><i>--SPACE_LABEL--</ i> &nbsp;>&nbsp; <i>--TARGET_LABEL--</i><br><br> Caso contrário, este arquivo não pode ser acessado!",
			"EDIT_accessRight"=>"Direitos de acesso",
			"EDIT_accessRightContent"=>"Direitos de Acesso ao Conteúdo",
			"EDIT_spaceNoModule"=>"O módulo atual ainda não foi adicionado a este espaço",
			"EDIT_allUsers"=>"Todos os usuários",
			"EDIT_allUsersAndGuests"=>"Todos os usuários e convidados",
			"EDIT_allUsersTooltip"=>"Todos os usuários do espaço <i>--SPACENAME--</i>",
			"EDIT_allUsersAndGuestsTooltip"=>"Todos os usuários do espaço <i>--SPACENAME--</i>, e convidados, mas com acesso apenas da leitura (convidados: pessoas que não têm uma conta de usuário)",
			"EDIT_adminSpace"=>"Administrador do espaço:<br>Acesso de escrita a todos os itens do espaço",
			"EDIT_showAllUsers"=>"Mostrar todos os usuários",
			"EDIT_showAllUsersAndSpaces"=>"Mostre todos os usuários e espaços",
			"EDIT_notifMail"=>"Notificar",
			"EDIT_notifMail2"=>"Envie uma notificação de criação/alteração por e-mail",
			"EDIT_notifMailTooltip"=>"A notificação será enviada para as pessoas designadas para o item (--OBJLABEL--)",
			"EDIT_notifMailTooltipCal"=>"<hr>Se você atribuir o evento a calendários pessoais, a notificação será enviada apenas aos proprietários desses calendários (acesso a escrita).",
			"EDIT_notifMailAddFiles"=>"Anexar arquivos à notificação",
			"EDIT_notifMailSelect"=>"Selecionar os destinatários das notificações",
			"EDIT_accessRightSubFolders"=>"Dar direitos iguais a todas as subpastas",
			"EDIT_accessRightSubFoldersTooltip"=>"Estender os direitos de acesso a subpastas que podem ser editadas",
			"EDIT_shortcut"=>"Acesso direto",
			"EDIT_shortcutInfo"=>"Mostrar acesso direto no menu principal",
			"EDIT_attachedFile"=>"Arquivos anexados",
			"EDIT_attachedFileAdd"=>"Adicionar arquivos",
			"EDIT_attachedFileInsert"=>"Inserir em texto",
			"EDIT_attachedFileInsertTooltip"=>"Inserir imagem/vídeo no texto do editor (formato jpg, png ou mp4)",
			"EDIT_guestName"=>"Seu nome / apelido",
			"EDIT_guestNameNotif"=>"Por favor, especifique um nome / apelido",
			"EDIT_guestMail"=>"Seu e-mail",
			"EDIT_guestMailTooltip"=>"Especifique seu e-mail para a validação de sua proposta",
			"EDIT_guestElementRegistered"=>"Obrigado pela sua proposta: ela será examinada o mais rápido possível antes da validação",
			
			////	Formulaire d'installation
			"INSTALL_dbConnect"=>"Conexão do banco de dados",
			"INSTALL_dbHost"=>"Nome do servidor host (hostname)",
			"INSTALL_dbName"=>"Nome do banco de dados",
			"INSTALL_dbLogin"=>"Nome de usuário",
			"INSTALL_adminAgora"=>"Administrador do Ágora",
			"INSTALL_errorDbNameFormat"=>"Aviso: o nome do banco de dados deve conter apenas caracteres alfanuméricos, hífens ou sublinhados",
			"INSTALL_errorDbConnection"=>"Sem identificação com o banco de dados MariaDB/MySQL",
			"INSTALL_errorDbExist"=>"Aplicativo já instalado: <a href='index.php'>Clique aqui para acessar</a><br><br>Para reiniciar a instalação, lembre-se de excluir o banco de dados",
			"INSTALL_errorDbNoSqlFile"=>"Você não pode acessar o arquivo de instalação do DB.SQL ou foi excluído porque a instalação já foi realizada",
			"INSTALL_PhpOldVersion"=>"Agora-Project --CURRENT_VERSION-- requer uma versão mais recente do PHP",
			"INSTALL_confirmInstall"=>"Confirmar a instalação?",
			"INSTALL_installOk"=>"Agora-Project foi instalado!",
			// Premiers enregistrements en DB
			"INSTALL_agoraDescription"=>"Espaço para troca e trabalho colaborativo",
			"INSTALL_dataDashboardNews"=>"<h3>¡Bem-vindo ao seu novo espaço para compartilhar!</h3>
											<h4><img src='app/img/file/iconSmall.png'> Compartilhe seus arquivos agora no Administrador de Arquivos</h4>
											<h4><img src='app/img/calendar/iconSmall.png'> Compartilhe seus calendários comuns ou seu calendário pessoal</h4>
											<h4><img src='app/img/dashboard/iconSmall.png'> Expanda o suprimento de notícias da sua comunidade</h4>
											<h4><img src='app/img/messenger.png'> Comunique-se através do fórum, mensagens instantâneas ou videoconferências</h4>
											<h4><img src='app/img/task/iconSmall.png'> Centralize suas anotações, projetos e contatos</h4>
											<h4><img src='app/img/mail/iconSmall.png'> Envie boletins por e-mail</h4>
											<h4><img src='app/img/postMessage.png'> <a onclick=\"lightboxOpen('?ctrl=user&action=SendInvitation')\">Clique aqui para enviar e-mails de convite eletrônico e expandir sua comunidade!</a></h4>
											<h4><img src='app/img/pdf.png'> <a href='https://www.omnispace.fr/index.php?ctrl=offline&action=Documentation' target='_blank'>Para mais informações, consulte a documentação oficial do Omnispace & Agora-Project</a></h4>",
			"INSTALL_dataDashboardPoll"=>"O que você acha da ferramenta de notícias?",
			"INSTALL_dataDashboardPollA"=>"Muito interessante!",
			"INSTALL_dataDashboardPollB"=>"Interessante",
			"INSTALL_dataDashboardPollC"=>"Sem interesse",
			"INSTALL_dataCalendarEvt"=>"Bem-vindo ao Omnispace",
			"INSTALL_dataForumSubject1"=>"Bem-vindo ao fórum do Omnispace",
			"INSTALL_dataForumSubject2"=>"Sinta-se à vontade para compartilhar suas perguntas ou discutir os problemas que você deseja.",
			"INSTALL_dataTaskStatus1"=>"Por fazer",
			"INSTALL_dataTaskStatus2"=>"Em andamento",
			"INSTALL_dataTaskStatus3"=>"A validar",
			"INSTALL_dataTaskStatus4"=>"Concluído",

			////	MODULE_PARAMETRAGE
			////
			"AGORA_generalSettings"=>"Administração Geral",
			"AGORA_versions"=>"Versões",
			"AGORA_dateUpdate"=>"Atualização em",
			"AGORA_Changelog"=>"Ver registro da versão",
			"AGORA_funcMailDisabled"=>"A função PHP para enviar e-mails está desativada.",
			"AGORA_funcImgDisabled"=>"A biblioteca PHP GD2 para manipulação de imagem está desativada",
			"AGORA_backupFull"=>"Backup completo",
			"AGORA_backupFullTooltip"=>"Recupere o backup completo do espaço: todos os arquivos e banco de dados",
			"AGORA_backupDb"=>"Faça um backup de banco de dados",
			"AGORA_backupDbTooltip"=>"Recupere apenas o backup do banco de dados espacial",
			"AGORA_backupConfirm"=>"Esta operação pode levar alguns minutos: continuar o download?",
			"AGORA_diskSpaceInvalid"=>"O espaço do disco para arquivos deve ser um número inteiro",
			"AGORA_visioHostInvalid"=>"O endereço da web do seu servidor de videoconferência não é válido: você deve começar com 'https'",
			"AGORA_mapApiKeyInvalid"=>"Se escolher Google Map como ferramenta de mapa, deve especificar uma 'API Key'",
			"AGORA_gIdentityKeyInvalid"=>"Se você escolher a conexão opcional através do Google, deve especificar uma 'API Key' para o Google SignIn",
			"AGORA_confirmModif"=>"Confirmar alterações ?",
			"AGORA_name"=>"Nome do espaço principal",
			"AGORA_nameTooltip"=>"Nome exibido na página de login, em e-mails, etc.",
			"AGORA_description"=>"Descrição na página de login",
			"AGORA_footerHtml"=>"Texto no canto inferior esquerdo de cada página",
			"AGORA_logo"=>"Logotipo no canto inferior direito de cada página",
			"AGORA_logoUrl"=>"URL",
			"AGORA_logoConnect"=>"Logotipo na página de login",
			"AGORA_logoConnectTooltip"=>"Logotipo exibido na parte superior do formulário de conexão",
			"AGORA_lang"=>"Idioma padrão",
			"AGORA_timezone"=>"Fuso horário",
			"AGORA_diskSpaceLimit"=>"Disco disponível para arquivos",
			"AGORA_logsTimeOut"=>"Duração do histórico de eventos (registros)",
			"AGORA_logsTimeOutTooltip"=>"O período de retenção do histórico de eventos diz respeito à adição ou modificação de elementos. Os registros de exclusão são mantidos por no mínimo 1 ano.",
			"AGORA_visioHost"=>"Servidor web de videoconferência Jitsi",
			"AGORA_visioHostTooltip"=>"Url du serveur de visioconférence principal. Exemple : https://framatalk.org ou https://meet.jit.si",
			"AGORA_visioHostAlt"=>"Servidor alternativo de videoconferência",
			"AGORA_visioHostAltTooltip"=>"URL do servidor alternativo de videoconferência: em caso de indisponibilidade do servidor Jitsi principal",
			"AGORA_skin"=>"Cor da interface",
			"AGORA_black"=>"Modo escuro",
			"AGORA_white"=>"Modo claro",
			"AGORA_userMailDisplay"=>"Endereços de e-mail dos usuários visíveis para todos",
			"AGORA_userMailDisplayTooltip"=>"Mostrar/ocultar e-mail no perfil de cada usuário, notificações por e-mail, etc.<br>Observação: o administrador principal sempre poderá visualizar o e-mail de cada usuário",
			"AGORA_moduleLabelDisplay"=>"Nome dos módulos na barra de menu",
			"AGORA_folderDisplayMode"=>"Visualização de pasta padrão",
			"AGORA_wallpaperLogoError"=>"O papel de parede e o logotipo devem estar no formato .jpg ou .png",
			"AGORA_deleteWallpaper"=>"Remover papel de parede",
			"AGORA_usersCommentLabel"=>"Botão 'Comentário' nos itens",
			"AGORA_usersComment"=>"Comente",
			"AGORA_usersComments"=>"comentários",
			"AGORA_usersLikeLabel"=>"Botão 'Eu gosto' nos itens",
			"AGORA_usersLike"=>"Eu gosto !",
			"AGORA_mapTool"=>"Ferramenta de mapeamento",
			"AGORA_mapToolTooltip"=>"Ferramenta de mapeamento para exibir usuários e contatos em um mapa",
			"AGORA_mapApiKey"=>"Chave de API para catografia de mapas do Google",
			"AGORA_mapApiKeyTooltip"=>"Configuração obrigatória para a ferramenta de mapeamento do Google Map : <br>https://developers.google.com/maps/ <br>https://developers.google.com/maps/documentation/javascript/get-api-key",
			"AGORA_gIdentity"=>"Opção de login via Google",
			"AGORA_gIdentityTooltip"=>"Usuários com um identificador com endereço <i>@gmail.com</i> também poderão se conectar por meio de sua conta do Google",
			"AGORA_gIdentityClientId"=>"Chave API para login via Google",
			"AGORA_gIdentityClientIdTooltip"=>"É necessária uma 'chave API' para conexão via Google. Mais informações em <a href='https://developers.google.com/identity/sign-in/web' target='_blank'>https://developers.google.com/identity/sign-in/web</a>",
			"AGORA_gPeopleApiKey"=>"API KEY para importar contatos do Google",
			"AGORA_gPeopleApiKeyTooltip"=>"Uma 'chave API' é necessária para recuperação de contatos do Google/Gmail. Mais informações em <a href='https://developers.google.com/people/' target='_blank'>https://developers.google.com/people/</a>",
			"AGORA_messengerDisplay"=>"Mensagem instantânea",
			"AGORA_personsSort"=>"Classifique usuários e contatos por",
			//SMTP
			"AGORA_smtpLabel"=>"Conexão SMTP & sendMail",
			"AGORA_sendmailFrom"=>"E-mail 'De'",
			"AGORA_sendmailFromPlaceholder"=>"ex: 'noreply@mydomain.com'",
			"AGORA_smtpHost"=>"Endereço do servidor (hostname)",
			"AGORA_smtpPort"=>"Porta do servidor",
			"AGORA_smtpPortTooltip"=>"'25' por padrão. '587' ou '465' para SSL/TLS",
			"AGORA_smtpSecure"=>"Tipo de conexão segura (opcional)",
			"AGORA_smtpSecureTooltip"=>"'ssl' ou 'tls'",
			"AGORA_smtpUsername"=>"Nome do usuário",
			"AGORA_smtpPass"=>"Senha",
			//LDAP
			"AGORA_ldapLabel"=>"Conexão a um servidor LDAP",
			"AGORA_ldapLabelTooltip"=>"Conexão a um servidor LDAP para a criação de usuários no espaço: cf. Opção ''Importação/exportação de usuários'' do módulo ''Usuário''",
			"AGORA_ldapUri"=>"URI LDAP",
			"AGORA_ldapUriTooltip"=>"URI do LDAP completo no formato LDAP://hostname:port ou LDAPS://hostname:port para a criptografia SSL.",
			"AGORA_ldapPort"=>"Porta do servidor",
			"AGORA_ldapPortTooltip"=>"A porta usada para a conexão: '' 389 '' por padrão",
			"AGORA_ldapLogin"=>"DN do administrador LDAP (Distinguished Name)",
			"AGORA_ldapLoginTooltip"=>"por exemplo ''cn=admin,dc=mon-entreprise,dc=com''",
			"AGORA_ldapPass"=>"Senha do administrador",
			"AGORA_ldapDn"=>"DN do grupo de usuários (Distinguished Name)",
			"AGORA_ldapDnTooltip"=>"DN do grupo de usuários: localização dos usuários no diretório. Exemplo ''ou=mon-groupe,dc=mon-entreprise,dc=com''",
			"importLdapFilterTooltip"=>"Filtro de pesquisa LDAP (cf. https://www.php.net/manual/function.ldap-search.php). Exemplo ''(cn=*)'' ou ''(&(samaccountname=MONLOGIN)(cn=*))''",
			"AGORA_ldapDisabled"=>"O módulo PHP para conectar-se a um servidor LDAP não está instalado",
			"AGORA_ldapConnectError"=>"Erro de conexão do servidor LDAP!",

			////	MODULE_LOG
			////
			"LOG_moduleDescription"=>"Logs - Registro de eventos",
			"LOG_path"=>"Caminho",
			"LOG_filter"=>"Filtro",
			"LOG_date"=>"Data e hora",
			"LOG_spaceName"=>"Espaço",
			"LOG_moduleName"=>"Módulo",
			"LOG_objectType"=>"Tipo de objeto",
			"LOG_action"=>"Ação",
			"LOG_userName"=>"Usuário",
			"LOG_ip"=>"IP",
			"LOG_comment"=>"Comentário",
			"LOG_noLogs"=>"Sem registro",
			"LOG_filterSince"=>"filtrado desde",
			"LOG_search"=>"Buscar",
			"LOG_connexion"=>"Conexão",//action
			"LOG_add"=>"Adicionar",//action
			"LOG_delete"=>"Eliminar",//action
			"LOG_modif"=>"Alterar",//action

			////	MODULE_ESPACE
			////
			"SPACE_moduleTooltip"=>"O espaço principal pode ser subdividido em vários espaços (consulte ''subespaço'')",
			"SPACE_manageAllSpaces"=>"Administrar todos os espaços",
			"SPACE_config"=>"Administração do Espaço",//.."mon espace"
			// Index
			"SPACE_confirmDeleteDbl"=>"Confirmar a exclusão? Note que os dados afetados a este espaço serão definitivamente perdidos!",
			"SPACE_space"=>"Espaço",
			"SPACE_spaces"=>"Espaços",
			"SPACE_accessRightUndefined"=>"Definir",
			"SPACE_modules"=>"Módulos",
			"SPACE_addSpace"=>"Adicionar um espaço",
			//Edit
			"SPACE_userAdminAccess"=>"Usuários e administradores do espaço",
			"SPACE_selectModule"=>"Você deve selecionar pelo menos um módulo",
			"SPACE_spaceModules"=>"Módulos do espaço",
			"SPACE_publicSpace"=>"Espaço público: acesso de convidado",
			"SPACE_publicSpaceTooltip"=>"Um espaço público está aberto a pessoas que não têm uma conta de usuário (convidados). Eles podem acessar o espaço da página inicial. Você pode especificar uma senha para proteger o acesso a esse espaço público. Os módulos 'E-mails' e 'Usuarios' não estão disponíveis para os convidados.",
			"SPACE_publicSpaceNotif"=>"Seu espaço é público: se ele contiver dados pessoais (telefone, endereço etc.), lembre-se de especificar uma senha para cumprir a LGPD: Lei Geral de Proteção de Dados",
			"SPACE_usersInvitation"=>"Os usuários podem enviar convites por correio",
			"SPACE_usersInvitationTooltip"=>"Todos os usuários podem enviar e-mail para participar do espaço",
			"SPACE_allUsers"=>"Todos os usuários",
			"SPACE_user"=>"Usuários",
			"SPACE_userTooltip"=>"Usuário do espaço: <br> Acesso normal ao espaço",
			"SPACE_admin"=>"Administrador",
			"SPACE_adminTooltip"=>"O administrador de um espaço é um usuário que pode editar ou eliminar todos os itens presentes no espaço. Você também pode configurar o espaço, criar novas contas de usuário, criar grupos de usuários, enviar e-mail para adicionar novos usuários, etc.",

			////	MODULE_UTILISATEUR
			////
			// Menu principal
			"USER_headerModuleName"=>"Usuários",
			"USER_moduleDescription"=>"Usuários do espaço",
			"USER_option_allUsersAddGroup"=>"Os usuários também podem criar grupos",//OPTION!
			//Index
			"USER_spaceOrAllUsersTooltip"=>"Gerenciar usuários do espaço atual / Gerenciar usuários de todos os espaços (reservado ao administrador geral)",
			"USER_spaceUsers"=>"Usuários do espaço atual",
			"USER_allUsers"=>"Administrar todos os usuários",
			"USER_deleteDefinitely"=>"Deletar definitivamente",
			"USER_deleteFromCurSpace"=>"Desatribuir ao espaço atual",
			"USER_deleteFromCurSpaceConfirm"=>"Cancelar atribuição ao usuário do espaço atual?",
			"USER_allUsersOnSpaceNotif"=>"Todos os usuários são atribuídos a este espaço",
			"USER_user"=>"Usuário",
			"USER_users"=>"Usuários",
			"USER_addExistUser"=>"Adicionar um usuário existente, a esse espaço",
			"USER_addExistUserTitle"=>"Adicionar ao espaço um usuário existente no site: atribuição de espaço",
			"USER_addUser"=>"Adicionar um usuário",
			"USER_addUserSite"=>"Crie um usuário no site: padrão, atribuído a qualquer espaço!",
			"USER_addUserSpace"=>"Crie um usuário no espaço atual",
			"USER_sendCoords"=>"Envie o nome de usuário e a senha",
			"USER_sendCoordsTooltip"=>"Envie aos usuários um e-mail com seu login e um link da web para inicializar sua senha",
			"USER_sendCoordsTooltip2"=>"Envie a cada novo usuário um e-mail com informações de acesso.",
			"USER_sendCoordsConfirm"=>"Confirmar?",
			"USER_sendCoordsMail"=>"Seus dados de acesso ao seu espaço",
			"USER_noUser"=>"Nenhum usuário atribuído a este espaço no momento",
			"USER_spaceList"=>"Espaços do usuário",
			"USER_spaceNoAffectation"=>"Sem espaço",
			"USER_adminGeneral"=>"Administrador geral do site",
			"USER_adminGeneralTooltip"=>"Atenção: O direito de acesso ''administrador geral'' concede muitos privilégios e responsabilidades, em particular para editar todos os itens (calendários, pastas, arquivos, etc.), bem como todos os usuários e espaços. Portanto, é aconselhável atribuir este privilégio a no máximo 2 ou 3 usuários.<br><br>Para privilégios mais restritos, escolha o direito de acesso ''administrador do espaço'' (ver menu principal > ''Configurar o espaço'' )",
			"USER_adminSpace"=>"Administrador do espaço",
			"USER_userSpace"=>"Usuário do espaço",
			"USER_profilEdit"=>"Editar o perfil",
			"USER_myProfilEdit"=>"Editar meu perfil de usuário",
			// Invitation
			"USER_sendInvitation"=>"Envie convites por e-mail",
			"USER_sendInvitationTooltip"=>"Envie convites aos seus contatos para criar uma conta de usuário e ingressar no espaço de trabalho.<hr><img src='app/img/google.png' height=15> Se você tiver uma conta do Google, poderá enviar convites para seus contatos do Gmail.",
			"USER_mailInvitationObject"=>"Convite de", // ..Jean DUPOND
			"USER_mailInvitationFromSpace"=>"convida você para ", // Jean DUPOND "vous invite à rejoindre l'espace" Mon Espace
			"USER_mailInvitationConfirm"=>"Clique aqui para confirmar o convite",
			"USER_mailInvitationWait"=>"Convites para confirmar",
			"USER_exired_idInvitation"=>"O link do seu convite expirou",
			"USER_invitPassword"=>"Confirmar seu convite",
			"USER_invitPassword2"=>"Escolha sua senha para confirmar seu convite",
			"USER_invitationValidated"=>"Seu convite foi validado!",
			"USER_gPeopleImport"=>"Obtener mis contactos de mi dirección de Gmail",
			"USER_importQuotaExceeded"=>"Está limitado a --USERS_QUOTA_REMAINING-- novas contas de usuário, de um total de --LIMITE_NB_USERS-- usuários",
			// groupes
			"USER_spaceGroups"=>"grupos de usuários do espaço",
			"USER_spaceGroupsEdit"=>"modificar os grupos de usuários do espaço",
			"USER_groupEditInfo"=>"Cada grupo pode ser modificado por seu autor ou pelo administrador do espaço",
			"USER_addGroup"=>"Adicionar um grupo",
			"USER_userGroups"=>"Grupos do usuário",
			// Utilisateur_affecter
			"USER_searchPrecision"=>"Por favor, escolha um nome, sobrenome ou endereço de e-mail",
			"USER_userAffectConfirm"=>"Confirmar as atribuições?",
			"USER_userSearch"=>"Buscar usuários para adicionar ao espaço",
			"USER_allUsersOnSpace"=>"Todos os usuários do site já estão atribuídos a este espaço",
			"USER_usersSpaceAffectation"=>"Atribuir usuários ao espaço:",
			"USER_usersSearchNoResult"=>"Não há usuários para esta pesquisa",
			"USER_usersSearchBack"=>"Voltar",
			// Utilisateur_edit & CO
			"USER_langs"=>"Idioma",
			"USER_persoCalendarDisabled"=>"Calendário pessoal desativado",
			"USER_persoCalendarDisabledTooltip"=>"Um calendário pessoal é atribuído por padrão a cada usuário (mesmo que o módulo ''Calendario'' não esteja ativado no espaço). Marque esta opção para desabilitar o calendário pessoal deste usuário.",
			"USER_connectionSpace"=>"Espaço de conexão",
			"USER_loginExists"=>"O login/e-mail já existe. Por favor, escolha outro!",
			"USER_mailPresentInAccount"=>"Já existe uma conta de usuário com este endereço de e-mail",
			"USER_loginAndMailDifferent"=>"Ambos os endereços de e-mail devem ser idênticos",
			"USER_mailNotifObject"=>"Nova conta em",  // "...sur" l'Agora machintruc
			"USER_mailNotifContent"=>"Sua conta de usuário foi criada em",  // idem
			"USER_mailNotifContent2"=>"Conecte-se com o login e senha seguintes",
			"USER_mailNotifContent3"=>"Por favor, guarde este e-mail para seus registros.",
			// Livecounter & Messenger & Visio
			"USER_messengerEdit"=>"Configurar minhas mensagens instantâneas",
			"USER_messengerEdit2"=>"Configurar mensagens instantâneas",
			"USER_livecounterVisibility"=>"Visibilidade em mensagens instantâneas e videoconferências",
			"USER_livecounterAllUsers"=>"Mostrar minha presença quando estou conectado: mensagens / vídeo habilitado",
			"USER_livecounterDisabled"=>"Esconder minha presença quando estou conectado: mensagens / vídeo desativado",
			"USER_livecounterSomeUsers"=>"Apenas certos usuários podem me ver quando estou conectado",

			////	MODULE_TABLEAU BORD
			////
			// Menu principal + options du module
			"DASHBOARD_headerModuleName"=>"Notícias",
			"DASHBOARD_moduleDescription"=>"Notícias, Pesquisas e Itens recentes",
			"DASHBOARD_option_adminAddNews"=>"Somente o administrador pode adicionar notícias",//OPTION!
			"DASHBOARD_option_disablePolls"=>"Desativar pesquisas",//OPTION!
			"DASHBOARD_option_adminAddPoll"=>"Somente o administrador pode adicionar pesquisas",//OPTION!
			//Index
			"DASHBOARD_menuNews"=>"Notícias",
			"DASHBOARD_menuPolls"=>"Pesquisas",
			"DASHBOARD_menuElems"=>"Itens recentes e atuais",
			"DASHBOARD_addNews"=>"Adicionar uma notícia",
			"DASHBOARD_offlineNews"=>"Mostrar notícias arquivadas",
			"DASHBOARD_offlineNewsNb"=>"notícias arquivadas",//"55 actualités archivées"
			"DASHBOARD_noNews"=>"Não há notícias no momento",
			"DASHBOARD_addPoll"=>"Adicionar uma pesquisa",
			"DASHBOARD_pollsVoted"=>"Mostrar apenas enquetes votadas",
			"DASHBOARD_pollsVotedNb"=>"enquetes nas quais já votei",//"55 sondages..déjà voté"
			"DASHBOARD_vote"=>"Vote e veja os resultados",
			"DASHBOARD_voteTooltip"=>"Os votos são anônimos: ninguém saberá sua escolha de voto",
			"DASHBOARD_answerVotesNb"=>"Votada --NB_VOTES-- vezes",//55 votes (sur la réponse)
			"DASHBOARD_pollVotesNb"=>"A pesquisa foi votada --NB_VOTES-- vezes",
			"DASHBOARD_pollVotedBy"=>"Votada por",//Bibi, boby, etc
			"DASHBOARD_noPoll"=>"Não há pesquisa no momento",
			"DASHBOARD_plugins"=>"Novos itens",
			"DASHBOARD_pluginsTooltip"=>"Itens criados",
			"DASHBOARD_pluginsTooltip2"=>"entre",
			"DASHBOARD_plugins_day"=>"de hoje",
			"DASHBOARD_plugins_week"=>"desta semana",
			"DASHBOARD_plugins_month"=>"do mês",
			"DASHBOARD_plugins_previousConnection"=>"desde a última conexão",
			"DASHBOARD_pluginsTooltipRedir"=>"Ver o item na pasta",
			"DASHBOARD_pluginEmpty"=>"Não há novos itens para este período",
			// Actualite/News
			"DASHBOARD_topNews"=>"Principais notícias",
			"DASHBOARD_topNewsTooltip"=>"Notícias no topo da lista",
			"DASHBOARD_offline"=>"Notícia arquivada",
			"DASHBOARD_dateOnline"=>"online em",
			"DASHBOARD_dateOnlineTooltip"=>"Selecione uma data para colocar as notícias on-line automaticamente.<br>Enquanto isso, as notícias permanecem off-line",
			"DASHBOARD_dateOnlineNotif"=>"A notícia é momentaneamente arquivada",
			"DASHBOARD_dateOffline"=>"Data de arquivamento",
			"DASHBOARD_dateOfflineTooltip"=>"Selecione uma data para arquivar automaticamente as notícias",
			// Sondage/Polls
			"DASHBOARD_titleQuestion"=>"Título / Pergunta",
			"DASHBOARD_multipleResponses"=>"Várias respostas possíveis para cada voto",
			"DASHBOARD_newsDisplay"=>"Mostrar com notícias (menu à esquerda)",
			"DASHBOARD_publicVote"=>"Votação do público: a escolha dos eleitores é pública",
			"DASHBOARD_publicVoteInfos"=>"Considere que a votação na modalidade pública pode ser uma barreira à participação na pesquisa.",
			"DASHBOARD_dateEnd"=>"Fim das votações",
			"DASHBOARD_responseList"=>"Possíveis respostas",
			"DASHBOARD_responseNb"=>"Resposta n°",
			"DASHBOARD_addResponse"=>"Adicionar uma resposta",
			"DASHBOARD_controlResponseNb"=>"Por favor, especifique pelo menos 2 respostas possíveis",
			"DASHBOARD_votedPollNotif"=>"Atenção: assim que a enquete for votada, não será mais possível alterar o título ou as respostas",
			"DASHBOARD_voteNoResponse"=>"Por favor, selecione uma resposta",
			"DASHBOARD_exportPoll"=>"Baixar os resultados da pesquisa em PDF",
			"DASHBOARD_exportPollDate"=>"resultado da pesquisa para",

			////	MODULE_FICHIER
			////
			// Menu principal
			"FILE_headerModuleName"=>"Arquivos",
			"FILE_moduleDescription"=>"Administração de Arquivos",
			"FILE_option_adminRootAddContent"=>"Somente o administrador pode adicionar itens no diretório raiz",//OPTION!
			//Index
			"FILE_addFile"=>"Adicionar arquivos",
			"FILE_addFileAlert"=>"Os diretórios do servidor não são acessíveis para gravação! Por favor, entre em contato com o administrador",
			"FILE_downloadSelection"=>"Baixar a seleção",
			"FILE_fileDownload"=>"Baixar",
			"FILE_fileSize"=>"Tamanho do arquivo",
			"FILE_imageSize"=>"Tamanho da imagem",
			"FILE_nbFileVersions"=>"versões de arquivo",//"55 versions du fichier"
			"FILE_downloadsNb"=>"(baixado --NB_DOWNLOAD-- vezes)",
			"FILE_downloadedBy"=>"arquivo enviado por",//"..boby, will"
			"FILE_addFileVersion"=>"Adicionar nova versão do arquivo",
			"FILE_noFile"=>"Não há arquivo neste momento",
			// fichier_edit_ajouter  &  Fichier_edit
			"FILE_fileSizeLimit"=>"Os arquivos não devem exceder", // ...2 Mega Octets
			"FILE_uploadSimple"=>"Envio simples",
			"FILE_uploadMultiple"=>"Enviar múltiplos",
			"FILE_imgReduce"=>"Otimizar a imagem",
			"FILE_updatedName"=>"O nome do arquivo será substituído pela nova versão",
			"FILE_fileSizeError"=>"Arquivo muito grande",
			"FILE_addMultipleFilesTooltip"=>"Pressione '⌘' ou 'Ctrl' para selecionar vários arquivos",
			"FILE_selectFile"=>"Por favor, escolha pelo menos um arquivo",
			"FILE_fileContent"=>"conteúdo",
			// Versions_fichier
			"FILE_versionsOf"=>"Versões de", // versions de fichier.gif
			"FILE_confirmDeleteVersion"=>"Confirma a eliminação desta versão?",

			////	MODULE_AGENDA
			////
			// Menu principal
			"CALENDAR_headerModuleName"=>"Calendários",
			"CALENDAR_moduleDescription"=>"Calendários pessoal e calendários compartilhados",
			"CALENDAR_option_adminAddRessourceCalendar"=>"Somente o administrador pode adicionar recursos de calendários",//OPTION!
			"CALENDAR_option_adminAddCategory"=>"Somente o administrador pode adicionar categorias de eventos",//OPTION!
			"CALENDAR_option_createSpaceCalendar"=>"Criar um calendário compartilhado para o espaço",//OPTION!
			"CALENDAR_moduleAlwaysEnabledInfo"=>"Os usuários que não desativaram seu calendário pessoal em seu perfil de usuário ainda verão o módulo Calendário na barra de menus.",
			//Index
			"CALENDAR_calsList"=>"Calendários disponíveis",
			"CALENDAR_calsListDisplayAll"=>"Ver todos os calendários (administrador)",
			"CALENDAR_hideAllCals"=>"Ocultar todos os calendários",
			"CALENDAR_printCalendars"=>"Imprimir calendários",
			"CALENDAR_printCalendarsInfos"=>"imprimir a página no modo horizontal",
			"CALENDAR_addSharedCalendar"=>"Adicionar um calendário compartilhado",
			"CALENDAR_addSharedCalendarTooltip"=>"Adicionar um calendário compartilhado: para reservar um quarto, veículo, vídeo, etc.",
			"CALENDAR_exportIcal"=>"Exportar eventos (iCal)",
			"CALENDAR_icalUrl"=>"Copie endereço web (URL) para apresentar o calendário de um calendário externo",
			"CALENDAR_icalUrlCopy"=>"Permite o acesso de leitura ao calendário a partir de um calendário externo, como o Thunderbird, Outlook, Google Calendar, etc.",
			"CALENDAR_importIcal"=>"Importar eventos (iCal)",
			"CALENDAR_ignoreOldEvt"=>"Não importe eventos com mais de um ano",
			"CALENDAR_importIcalState"=>"Estado",
			"CALENDAR_importIcalStatePresent"=>"Já está presente",
			"CALENDAR_importIcalStateImport"=>"a importar",
			"CALENDAR_display_day"=>"Dia",
			"CALENDAR_display_4Days"=>"4 dias",
			"CALENDAR_display_workWeek"=>"Semana de trabalho",
			"CALENDAR_display_week"=>"Semana",
			"CALENDAR_display_month"=>"Mês",
			"CALENDAR_weekNb"=>"Ver a semana n°", //...5
			"CALENDAR_periodNext"=>"Período seguinte",
			"CALENDAR_periodPrevious"=>"Período anterior",
			"CALENDAR_evtAffects"=>"No calendario de",
			"CALENDAR_evtAffectToConfirm"=>"Pendente no calendario de",
			"CALENDAR_evtProposed"=>"Eventos propostos a serem confirmados",
			"CALENDAR_evtProposedBy"=>"Propostos por",//..Mr SMITH
			"CALENDAR_evtProposedConfirm"=>"Confirmar a proposta",
			"CALENDAR_evtProposedConfirmBis"=>"A proposta do evento foi integrada à agenda",
			"CALENDAR_evtProposedConfirmMail"=>"Sua proposta de evento foi confirmada",
			"CALENDAR_evtProposedDecline"=>"Rejeitar a proposta",
			"CALENDAR_evtProposedDeclineBis"=>"A proposta foi rejeitada",
			"CALENDAR_evtProposedDeclineMail"=>"Sua proposta de evento foi rejeitada",
			"CALENDAR_deleteEvtCal"=>"Remover apenas nesse calendário?",
			"CALENDAR_deleteEvtCals"=>"Remover em todos os calendários?",
			"CALENDAR_deleteEvtDate"=>"Remover apenas nesta data?",
			"CALENDAR_evtPrivate"=>"Evento privado",
			"CALENDAR_evtAutor"=>"Eventos que criei",
			"CALENDAR_evtAutorInfo"=>"Mostrar somente os eventos que criei",
			"CALENDAR_noEvt"=>"Não há eventos",
			"CALENDAR_synthese"=>"Síntese dos calendários",
			"CALENDAR_calendarsPercentBusy"=>"Calendários ocupados",  // Agendas occupés : 2/5
			"CALENDAR_noCalendarDisplayed"=>"Sem calendário",
			// Evenement
			"CALENDAR_importanceNormal"=>"Importância normal",
			"CALENDAR_importanceHight"=>"Alta importância",
			"CALENDAR_visibilityPublic"=>"Visibilidade normal",
			"CALENDAR_visibilityPrivate"=>"Visibilidade privada",
			"CALENDAR_visibilityPublicHide"=>"Visibilidade semi-privada",
			"CALENDAR_visibilityTooltip"=>"<u>Visibilidade privada</u>: evento visível apenas se o evento estiver acessível para gravação <br><br> <u>Visibilidade semiprivada</u>: mostra apenas o período do evento (sem os detalhes) se o evento é acessível para leitura",
			// Agenda/Evenement : edit
			"CALENDAR_sharedCalendarDescription"=>"Calendario compartilhado do espaço",
			"CALENDAR_noPeriodicity"=>"Uma vez",
			"CALENDAR_period_weekDay"=>"Cada semana",
			"CALENDAR_period_month"=>"Cada mês",
			"CALENDAR_period_year"=>"Cada ano",
			"CALENDAR_periodDateEnd"=>"Fim da periodicidade",
			"CALENDAR_periodException"=>"Exceção da periodicidade",
			"CALENDAR_calendarAffectations"=>"Atribuição a calendários",
			"CALENDAR_addEvt"=>"Adicionar um evento",
			"CALENDAR_addEvtTooltip"=>"Adicionar um evento",
			"CALENDAR_addEvtTooltipBis"=>"Adicionar o evento ao calendário",
			"CALENDAR_proposeEvtTooltip"=>"Propor um evento ao administrador do calendário",
			"CALENDAR_proposeEvtTooltipBis"=>"Propor o evento ao administrador do calendário",
			"CALENDAR_proposeEvtTooltipBis2"=>"Propor o evento ao administrador do calendário: o calendário só é acessível à leitura",
			"CALENDAR_inputProposed"=>"O evento será proposto ao administrador do calendário",
			"CALENDAR_verifCalNb"=>"Por favor, selecione pelo menos um calendário",
			"CALENDAR_noModifTooltip"=>"Edição proibida porque não tem acesso de escrita ao calendário",
			"CALENDAR_editLimit"=>"Você não é o autor do evento: você só pode editar as tarefas em seus calendários",
			"CALENDAR_busyTimeslot"=>"A vaga já está ocupada neste calendário:",
			"CALENDAR_timeSlot"=>"Intervalo de tempo da tela ''semana''",
			"CALENDAR_propositionNotif"=>"Notificar por e-mail de cada proposta de evento",
			"CALENDAR_propositionNotifTooltip"=>"Nota: Cada proposta de evento é validada ou invalidada pelo administrador do calendário.",
			"CALENDAR_propositionGuest"=>"Os convidados podem propor eventos",
			"CALENDAR_propositionGuestTooltip"=>"Nota: Lembre-se de selecionar 'todos os usuários e convidados' em direitos de acesso.",
			"CALENDAR_propositionE-mailSubject"=>"Novo evento proposto por",//.."boby SMITH"
			"CALENDAR_propositionE-mailMessage"=>"Novo evento proposto por --AUTOR_LABEL-- : &nbsp; <i><b>--EVT_TITLE_DATE--</b></i> <br><i>--EVT_DESCRIPTION--</i> <br>Acesse seu espaço para confirmar ou cancelar esta proposta",
			// Categorie : Catégories d'événement
			"CALENDAR_categoryMenuTooltip"=>"Mostrar somente eventos com categoria",
			"CALENDAR_categoryShowAll"=>"Todas as categorias",
			"CALENDAR_categoryShowAllTooltip"=>"Mostrar todas as categorias",
			"CALENDAR_categoryUndefined"=>"Sem categoria",
			"CALENDAR_categoryEditTitle"=>"Edite as categorias",
			"CALENDAR_categoryEditInfo"=>"Cada categoria de evento pode ser modificada por seu autor ou pelo administrador geral",
			"CALENDAR_categoryEditAdd"=>"Adicionar uma categoria de evento",

			////	MODULE_FORUM
			////
			// Menu principal
			"FORUM_headerModuleName"=>"Fórum",
			"FORUM_moduleDescription"=>"Fórum",
			"FORUM_option_adminAddSubject"=>"Somente o administrador pode adicionar assuntos",//OPTION!
			"FORUM_option_adminAddTheme"=>"Somente o administrador pode adicionar tópicos",//OPTION!
			// TRI
			"SORT_dateLastMessage"=>"última mensagem",
			//Index & Sujet
			"FORUM_forumRoot"=>"Início do fórum",
			"FORUM_subject"=>"assunto",
			"FORUM_subjects"=>"assuntos",
			"FORUM_message"=>"mensagem",
			"FORUM_messages"=>"mensagens",
			"FORUM_lastMessageFrom"=>"última mensagem",
			"FORUM_noSubject"=>"Sem assunto no momento",
			"FORUM_subjectBy"=>"assunto de",
			"FORUM_addSubject"=>"Novo assunto",
			"FORUM_displaySubject"=>"Mostrar o assunto",
			"FORUM_addMessage"=>"Responder",
			"FORUM_quoteMessage"=>"Responder",
			"FORUM_quoteMessageInfo"=>"Responder e citar essa mensagem",
			"FORUM_notifyLastPost"=>"Notificar por e-mail",
			"FORUM_notifyLastPostTooltip"=>"Quero receber uma notificação por e-mail para cada nova mensagem",
			// Sujet_edit  &  Message_edit
			"FORUM_notifOnlyReadAccess"=>"Se houver apenas acesso à leitura, ninguém poderá contribuir com o assunto.",
			"FORUM_notifWriteAccess"=>"O acesso de ''escrita'' é destinado aos moderadores:<br>Se for necessário, prefira os direitos de ''escrita limitada''",
			// Categorie : Themes
			"FORUM_categoryMenuTooltip"=>"Mostrar apenas assuntos com tópico",
			"FORUM_categoryShowAll"=>"Todo os tópicos",
			"FORUM_categoryShowAllTooltip"=>"Mostrar todos os temas",
			"FORUM_categoryUndefined"=>"Sem tópico",
			"FORUM_categoryEditTitle"=>"Editar tópicos",
			"FORUM_categoryEditInfo"=>"Cada tópico pode ser modificado pelo seu autor ou pelo administrador geral",
			"FORUM_categoryEditAdd"=>"Adicionar um tópico",

			////	MODULE_TACHE
			////
			// Menu principal
			"TASK_headerModuleName"=>"Tarefas",
			"TASK_moduleDescription"=>"Tarefas",
			"TASK_option_adminRootAddContent"=>"Somente o administrador pode adicionar itens no diretório raiz",//OPTION!
			"TASK_option_adminAddStatus"=>"Somente o administrador pode criar status Kanban",//OPTION!
			// TRI
			"SORT_priority"=>"Prioridade",
			"SORT_advancement"=>"Progresso",
			"SORT_dateBegin"=>"Data de início",
			"SORT_dateEnd"=>"Data de fim",
			//Index
			"TASK_addTask"=>"Adicionar uma tarefa",
			"TASK_noTask"=>"Nenhuma tarefa no momento",
			"TASK_advancement"=>"Progresso",
			"TASK_advancementAverage"=>"Progresso médio",
			"TASK_priority"=>"Prioridade",
			"TASK_priorityUndefined"=>"Prioridade indefinida",
			"TASK_priority1"=>"Baixa",
			"TASK_priority2"=>"média",
			"TASK_priority3"=>"alta",
			"TASK_assignedTo"=>"Atribuído a",
			"TASK_advancementLate"=>"Progresso atrasado",
			"TASK_folderDateBeginEnd"=>"Data de início mais antiga/última data de término",
			//Categorie : Statuts Kanban
			"TASK_categoryMenuTooltip"=>"Mostrar apenas tarefas com estado",
			"TASK_categoryShowAll"=>"Todos os estados",
			"TASK_categoryShowAllTooltip"=>"Mostrar todos os status",
			"TASK_categoryUndefined"=>"Status indefinido",
			"TASK_categoryEditTitle"=>"Editar os estados",
			"TASK_categoryEditInfo"=>"Cada estado pode ser modificado pelo seu autor ou pelo administrador geral.",
			"TASK_categoryEditAdd"=>"Adicionar um estado",

			////	MODULE_CONTACT
			////
			// Menu principal
			"CONTACT_headerModuleName"=>"Contatos",
			"CONTACT_moduleDescription"=>"Diretório de contatos",
			"CONTACT_option_adminRootAddContent"=>"Somente o administrador pode adicionar itens no diretório raiz",//OPTION!
			//Index
			"CONTACT_addContact"=>"Adicionar um contato",
			"CONTACT_noContact"=>"Nenhum contato ainda",
			"CONTACT_createUser"=>"Criar um usuário neste espaço",
			"CONTACT_createUserConfirm"=>"Criar um usuário neste espaço com este contato?",
			"CONTACT_createUserConfirmed"=>"O usuário foi criado",

			////	MODULE_LIEN
			////
			// Menu principal
			"LINK_headerModuleName"=>"Favoritos",
			"LINK_moduleDescription"=>"Favoritos",
			"LINK_option_adminRootAddContent"=>"Somente o administrador pode adicionar itens no diretório raiz",//OPTION!
			//Index
			"LINK_addLink"=>"Adicionar um link",
			"LINK_noLink"=>"Sem links no momento",
			// lien_edit & dossier_edit
			"LINK_adress"=>"Endereço Web",

			////	MODULE_MAIL
			////
			// Menu principal
			"MAIL_headerModuleName"=>"E-mails",
			"MAIL_moduleDescription"=>"Enviar mensagens de e-mail com um único clique!",
			//Index
			"MAIL_specifyMail"=>"Por favor, especificar ao menos um destinatário",
			"MAIL_title"=>"Assunto do e-mail",
			"MAIL_description"=>"Mensagem de e-mail",
			// Historique E-mail
			"MAIL_historyTitle"=>"Histórico dos e-mails enviados",
			"MAIL_delete"=>"Excluir este e-mail",
			"MAIL_resend"=>"Reenviar este e-mail",
			"MAIL_resendInfo"=>"Recuperar o conteúdo deste e-mail e integrá-lo diretamente ao editor para um novo envio",
			"MAIL_historyEmpty"=>"Sem e-mail",
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
			$dateList[$date]="Terça de Páscoa";
		}

		//Fêtes fixes
		$dateList[$year."-01-01"]="Día de Año Nuevo";
		$dateList[$year."-12-25"]="Natal";

		//Retourne le résultat
		return $dateList;
	}
}