<?php
/**
* This file is part of the Agora-Project Software package
*
* @copyleft Agora-Project <https://www.agora-project.net>
* @license GNU General Public License (GPL-2.0)
*/


/*
 * MISE A JOUR DE LA DB
 */
class DbUpdate extends Db
{
	/********************************************************************************************
	 * TESTE SI LE CHAMP D'UNE TABLE EXISTE : CRÉE SI BESOIN
	 ********************************************************************************************/
	public static function fieldExist($tableName, $fieldName, $createQuery=null)
	{
		$result=self::getCol("show columns from `".$tableName."` like '".$fieldName."'");
		if(empty($result) && !empty($createQuery))  {self::query($createQuery);}
		return (!empty($result));
	}

	/********************************************************************************************
	 * TESTE SI UNE TABLE EXISTE : CRÉE SI BESOIN
	 ********************************************************************************************/
	public static function tableExist($tableName, $createQuery=null)
	{
		$result=self::getCol("show tables like '".$tableName."'");
		if(empty($result) && !empty($createQuery))  {self::query($createQuery);}
		return (!empty($result));
	}

	/********************************************************************************************
	 * MISE À JOUR DEMANDÉ PLUS RÉCENTE QUE LA "dbAppVersion" : UPDATE!
	 ********************************************************************************************/
	public static function updateVersion($versionUpdate)
	{
		return version_compare(Ctrl::$agora->version_agora, $versionUpdate, "<");
	}

	/********************************************************************************************
	 * LANCE SI BESOIN LA MISE À JOUR DE LA DB !
	 ********************************************************************************************/
	public static function lauchUpdate()
	{
		////
		////	VERSION 2 (SANS NUMERO DE VERSION EN DB) : UPDATE NECESSAIRE EN 3.8
		////
		if(empty(Ctrl::$agora->version_agora)){
			throw new Exception("Update error : Please update Agora-Project to v3.8 first, before updating to the latest version -> https://github.com/xech/agora-project/releases/tag/3.8.0");
		}
		////
		////	ESPACE FRAICHEMENT INSTALLÉ : ON CREE LES PREMIERS ENREGISTREMENTS (NEWS, AGENDA, ETC.)
		////
		elseif(self::getVal("SELECT count(*) FROM ap_user WHERE _id=1 AND lastConnection IS NULL")>0  &&  self::getVal("SELECT count(*) FROM ap_dashboardNews")==0)//Admin général pas encore connecté && première actu à créer
		{
			//Première actualité
			$idNews=self::query("INSERT INTO ap_dashboardNews SET `description`=".self::format(Txt::trad("INSTALL_dataDashboardNews")).", _idUser=1, dateCrea=NOW()",  true);
			self::query("INSERT INTO ap_objectTarget SET objectType='dashboardNews', _idObject=".(int)$idNews.", _idSpace=1, target='spaceUsers', accessRight=1");
			//Agenda de l'espace principal (même nom que l'espace principal)  &&  Premier événement sur l'agenda partagé
			$firstSpaceName=self::getVal("SELECT `name` FROM ap_space WHERE _id=1");
			self::query("UPDATE ap_calendar SET `title`=".self::format($firstSpaceName).", description=".self::format(Txt::trad("CALENDAR_sharedCalendarDescription"))." WHERE _id=1 AND type='ressource'");
			self::query("INSERT INTO ap_calendarEvent SET title=".self::format(Txt::trad("INSTALL_dataCalendarEvt")).", dateBegin=NOW(), dateEnd=NOW(), contentVisible='public', dateCrea=NOW(), _idUser=1");
			self::query("INSERT INTO ap_calendarEventAffectation SET _idEvt=1, _idCal=1, confirmed=1");
			//Insert le premier sujet du forum
			self::query("INSERT INTO ap_forumSubject SET title=".self::format(Txt::trad("INSTALL_dataForumSubject1")).", description=".self::format(Txt::trad("INSTALL_dataForumSubject2")).", dateCrea=NOW(), _idUser=1");
			self::query("INSERT INTO ap_objectTarget SET objectType='forumSubject', _idObject=1, _idSpace=1, `target`='spaceUsers', accessRight='1.5'");
			//Créé un exemple de sondage
			MdlDashboardPoll::dbFirstRecord();
			//Créé les colonnes kanban de base
			MdlTaskStatus::dbFirstRecord();
		}
		////
		////	VERSION DE L'APPLI SUPERIEURE A CELLE DE LA DB : MISE A JOUR DE LA DB !
		////
		elseif(version_compare(Ctrl::$agora->version_agora, Req::appVersion(), "<"))
		{
			////	VERIF LA VERSION DE PHP & L'ACCES AU FICHIER DE CONFIG
			Req::verifPhpVersion();
			if(is_writable(PATH_DATAS."config.inc.php")==false)  {throw new Exception("Update error : Config.inc.php is not writable");}
			////	VERROUILAGE DE LA MISE A JOUR
			$updateLock=PATH_DATAS."UPDATE_LOCK.log";
			if(is_file($updateLock)==false)				{file_put_contents($updateLock,"LOCKED UPDATE - VERROUILAGE DE LA MISE A JOUR");}
			elseif((time()-filemtime($updateLock))<10)	{throw new Exception("Update in progress : please wait a few seconds");}
			else										{throw new Exception("Update error : check Apache/PHP logs for details<br><br>When the issue is resolved : delete the '".$updateLock."' file");}
			////	ALLONGE L'EXECUTION DU SCRIPT  &&  SAUVEGARDE LA DB
			ignore_user_abort(true);
			@set_time_limit(120);//pas en safemode
			$dumpPath=self::getDump();

			////	MAJ v3.0.0
			if(self::updateVersion("3.0.0"))
			{
				////	MAJ LE NOM DES TABLES!
				$tabsRenamed=array(
					"gt_actualite"=>"ap_dashboardNews",
					"gt_agenda"=>"ap_calendar",
					"gt_agenda_categorie"=>"ap_calendarEventCategory",
					"gt_agenda_evenement"=>"ap_calendarEvent",
					"gt_agenda_jointure_evenement"=>"ap_calendarEventAffectation",
					"gt_agora_info"=>"ap_agora",
					"gt_contact"=>"ap_contact",
					"gt_contact_dossier"=>"ap_contactFolder",
					"gt_espace"=>"ap_space",
					"gt_fichier"=>"ap_file",
					"gt_fichier_dossier"=>"ap_fileFolder",
					"gt_fichier_version"=>"ap_fileVersion",
					"gt_forum_message"=>"ap_forumMessage",
					"gt_forum_sujet"=>"ap_forumSubject",
					"gt_forum_theme"=>"ap_forumTheme",
					"gt_historique_mails"=>"ap_mailHistory",
					"gt_invitation"=>"ap_invitation",
					"gt_jointure_espace_module"=>"ap_joinSpaceModule",
					"gt_jointure_espace_utilisateur"=>"ap_joinSpaceUser",
					"gt_jointure_messenger_utilisateur"=>"ap_userMessenger",
					"gt_jointure_objet"=>"ap_objectTarget",
					"gt_jointure_objet_fichier"=>"ap_objectAttachedFile",
					"gt_lien"=>"ap_link",
					"gt_lien_dossier"=>"ap_linkFolder",
					"gt_logs"=>"ap_log",
					"gt_tache"=>"ap_task",
					"gt_tache_dossier"=>"ap_taskFolder",
					"gt_utilisateur"=>"ap_user",
					"gt_utilisateur_groupe"=>"ap_userGroup",
					"gt_utilisateur_inscription"=>"ap_userInscription",
					"gt_utilisateur_livecounter"=>"ap_userLivecouter",
					"gt_utilisateur_messenger"=>"ap_userMessengerMessage",
					"gt_utilisateur_preferences"=>"ap_userPreference"
				);
				foreach($tabsRenamed as $tableNameOld=>$tableNameNew)	{self::query("RENAME TABLE `".$tableNameOld."` TO `".$tableNameNew."`");}

				////	MAJ DES CHAMPS DE TOUTES LES TABLES !
				$tabFieldsRenamed=array(
					//Identifiants (ou groupe d'identifiants)
					"id_utilisateur"=>"_idUser",
					"id_utilisateur_expediteur"=>"_idUser",
					"id_utilisateur_modif"=>"_idUserModif",
					"id_utilisateurs"=>"_idUsers",
					"id_utilisateur_destinataires"=>"_idUsers",
					"id_espace"=>"_idSpace",
					"id_espaces"=>"_idSpaces",
					"id_evenement"=>"_idEvt",
					"id_agenda"=>"_idCal",
					"id_categorie"=>"_idCat",
					"id_message_parent"=>"_idMessageParent",
					"id_invitation"=>"_idInvitation",
					"id_fichier"=>"_idFile",
					"id_theme"=>"_idTheme",
					"id_invitation"=>"_idInvitation",
					"id_utilisateur_messenger"=>"_idUserMessenger",
					"id_tache"=>"_idTask",
					"id_newpassword"=>"_idNewPassword",
					"id_objet"=>"_idObject",
					//Champs principaux
					"type_objet"=>"objectType",
					"identifiant"=>"login",
					"pass"=>"password",
					"nom"=>"name",
					"prenom"=>"firstName",
					"nom_reel"=>"realName",
					"nom_fichier"=>"name",
					"nom_module"=>"moduleName",
					"module"=>"moduleName",
					"civilite"=>"civility",
					"titre"=>"title",
					"commentaire"=>"comment",
					"invite"=>"guest",
					"raccourci"=>"shortcut",
					"date_crea"=>"dateCrea",
					"date_modif"=>"dateModif",
					"date_debut"=>"dateBegin",
					"date_fin"=>"dateEnd",
					"photo"=>"picture",
					"societe_organisme"=>"companyOrganization",
					"fonction"=>"function",
					"adresse"=>"adress",
					"codepostal"=>"postalCode",
					"ville"=>"city",
					"pays"=>"country",
					"siteweb"=>"website",
					"competences"=>"skills",
					//Champs spécifiques
					"mise_a_jour_effective"=>"dateUpdateDb",
					"footer_html"=>"footerHtml",
					"messenger_desactive"=>"messengerDisabled",
					"agenda_perso_desactive"=>"personalCalendarsDisabled",
					"libelle_module"=>"moduleLabelDisplay",
					"tri_personnes"=>"personsSort",
					"logs_jours_conservation"=>"logsTimeOut",
					"ldap_groupe_dn"=>"ldap_base_dn",
					"inscription_users"=>"usersInscription",
					"invitations_users"=>"usersInvitation",
					"date_online"=>"dateOnline",
					"date_offline"=>"dateOffline",
					"couleur"=>"color",
					"fond_ecran"=>"wallpaper",
					"taille_octet"=>"octetSize",
					"nb_downloads"=>"downloadsNb",
					"logo_url"=>"logoUrl",
					"date_dernier_message"=>"dateLastMessage",
					"users_consult_dernier_message"=>"usersConsultLastMessage",
					"users_notifier_dernier_message"=>"usersNotifyLastMessage",
					"classement"=>"rank",
					"tous_utilisateurs"=>"allUsers",
					"droit"=>"accessRight",
					"priorite"=>"priority",
					"avancement"=>"advancement",
					"charge_jour_homme"=>"humanDayCharge",
					"budget_disponible"=>"budgetAvailable",
					"budget_engage"=>"budgetEngaged",
					"admin_general"=>"generalAdmin",
					"langue"=>"lang",
					"derniere_connexion"=>"lastConnection",
					"precedente_connexion"=>"previousConnection",
					"espace_connexion"=>"connectionSpace",
					"agenda_desactive"=>"calendarDisabled",
					"ip_controle"=>"ipControlAdresses",
					"adresse_ip"=>"ipAdress",
					"date_verif"=>"date",
					"cle"=>"keyVal",
					"valeur"=>"value",
					"evt_affichage_couleur"=>"evtColorDisplay",
					"plage_horaire"=>"timeSlot",
					"visibilite_contenu"=>"contentVisible",
					"periodicite_type"=>"periodType",
					"periodicite_valeurs"=>"periodValues",
					"period_date_fin"=>"periodDateEnd",
					"period_date_exception"=>"periodDateExceptions",
					"confirme"=>"confirmed",
					"destinataires"=>"recipients"
				);
				$tabIdParentContainer=array("ap_contact-id_dossier","ap_file-id_dossier","ap_link-id_dossier","ap_task-id_dossier","ap_forumMessage-id_sujet");
				////	LANCE LA MAJ DES CHAMPS DES TABLES ("ap_" et "gt_" uniquement)
				$updatedTables=array_merge(self::getCol("SHOW TABLES LIKE 'gt_%'"), self::getCol("SHOW TABLES LIKE 'ap_%'"));
				foreach($updatedTables as $tableName)
				{
					//réinitialise les Index (cles non primaires)
					$primaryKey=null;
					$tableIndexes=[];
					foreach(self::getTab("SHOW INDEXES FROM ".$tableName." WHERE Key_name NOT LIKE 'PRIMARY'") as $tmpIndex)    {self::query("ALTER TABLE ".$tableName." DROP INDEX `".$tmpIndex["Key_name"]."`");}
					//Mise à jour des champs de chaque table
					foreach(self::getTab("SHOW COLUMNS FROM ".$tableName) as $tmpField)
					{
						//Nom et Propriétés du nouveau champ
						$fieldOldName=$fieldNewName=$tmpField["Field"];
						$isIdContainer=($fieldOldName=="id_dossier_parent" || preg_grep("/".$tableName."-".$fieldOldName."/i",$tabIdParentContainer));//"preg_grep()" car "in_array()" est sensible à la casse, et sous windows les tables sont envoyées en minucules..
						if(strtolower($tmpField["Extra"])=="auto_increment")		{$fieldNewName=$primaryKey="_id";}					//Champs principal "_id" : cle primaire
						elseif($isIdContainer)										{$fieldNewName=$primaryKey="_idContainer";}			//Champs de l'objet parent : "id_dossier"=>"_idContainer"
						elseif(array_key_exists($fieldOldName,$tabFieldsRenamed))	{$fieldNewName=$tabFieldsRenamed[$fieldOldName];}	//Champ à renommer : "id_utilisateur"=>"_idUser"
						//Renomme le champ et ajoute le "type" et "Extra"
						$fieldProperties=$tmpField["Type"]." ".$tmpField["Extra"];
						if(strtolower($tmpField["Null"])=="no")  {$fieldProperties.=" NOT NULL";}
						if(preg_match("/tinytext/i",$tmpField["Type"]))  {$fieldProperties=str_ireplace("tinytext","varchar(255)",$fieldProperties);}//change le type "tinytext" en "varchar(255)"
						self::query("ALTER TABLE `".$tableName."` CHANGE `".$fieldOldName."` `".$fieldNewName."` ".$fieldProperties);
						//Ajoute un index pour ce champ?
						if(substr($fieldNewName,0,3)=="_id" && in_array($fieldNewName,array("_idUsers","_idSpaces","_idUserModif"))==false)
							{$tableIndexes[]=(preg_match("/(text|varchar)/i",$tmpField["Type"]))  ?  "`".$fieldNewName."`(255)"  :  "`".$fieldNewName."`";}
					}
					//Update les cles primaires et les indexes
					if(!empty($primaryKey))		{self::query("ALTER TABLE ".$tableName." DROP PRIMARY KEY, ADD PRIMARY KEY (`_id`)");}
					if(!empty($tableIndexes))	{self::query("ALTER TABLE ".$tableName." ADD INDEX `indexes` (".implode(",",$tableIndexes).")");}
				}

				////	MAJ DE "ap_agora" ("dateUpdateDb", "skin", etc)
				self::query("ALTER TABLE ap_agora CHANGE `ldap_pass_cryptage` `ldap_pass_cryptage` VARCHAR(255) DEFAULT NULL");
				self::query("UPDATE ap_agora SET ldap_pass_cryptage=null WHERE ldap_pass_cryptage='aucun'");
				self::query("ALTER TABLE ap_agora CHANGE `personsSort` `personsSort` VARCHAR(255) DEFAULT NULL");
				self::query("UPDATE ap_agora SET personsSort='firstName' WHERE personsSort='prenom'");
				self::query("UPDATE ap_agora SET personsSort='name' WHERE personsSort='nom'");
				self::query("UPDATE ap_agora SET dateUpdateDb=null WHERE dateUpdateDb='0'");
				self::query("ALTER TABLE ap_agora CHANGE `dateUpdateDb` `dateUpdateDb` DATE DEFAULT NULL");
				self::query("UPDATE ap_agora SET skin='black' WHERE skin='noir'");
				self::query("UPDATE ap_agora SET skin='white' WHERE skin='blanc' OR skin IS NULL");
				self::query("UPDATE ap_agora SET timezone=REPLACE(timezone,'.',':')");//"-5.00" devient "-5:00"
				//Supprime les doublons?
				$nbRows=self::getVal("select count(*) from ap_agora");
				if($nbRows>1)	{self::query("delete from ap_agora limit ".($nbRows-1));}

				////	MAJ DES TRADS
				self::query("UPDATE ap_agora SET lang='english' WHERE lang='nederlands' OR lang='italian'");
				self::query("UPDATE ap_user SET lang='english' WHERE lang='nederlands' OR lang='italian'");

				////	MAJ LES "Wallpaper"
				$wallpaperTables=array("ap_agora","ap_space");
				$oldWallpaperConserved=array("default@@2.jpg"=>"default@@old21.jpg", "default@@3.jpg"=>"default@@old22.jpg", "default@@5.jpg"=>"default@@old23.jpg", "default@@25.jpg"=>"default@@old24.jpg", "default@@23.jpg"=>"default@@old25.jpg", "default@@22.jpg"=>"default@@old26.jpg", "default@@13.jpg"=>"default@@old27.jpg", "default@@14.jpg"=>"default@@old28.jpg", "default@@18.jpg"=>"default@@old29.jpg", "default@@21.jpg"=>"default@@old30.jpg");
				foreach($wallpaperTables as $tmpTable){
					//Anciens wallpapers effacés
					self::query("UPDATE ".$tmpTable." SET wallpaper=null WHERE wallpaper IN ('default@@4.jpg','default@@6.jpg','default@@7.jpg','default@@8.jpg','default@@9.jpg','default@@10.jpg','default@@11.jpg','default@@12.jpg','default@@15.jpg','default@@16.jpg','default@@17.jpg','default@@19.jpg','default@@20.jpg','default@@24.jpg')");
					//Anciens wallpapers concervés
					foreach($oldWallpaperConserved as $oldFile=>$newFile)	{self::query("UPDATE ".$tmpTable." SET wallpaper=".self::format($newFile)." WHERE wallpaper=".self::format($oldFile));}
				}

				////	RENOMME DES MODULES DANS "ap_joinSpaceModule"
				$newModuleNames=array("dashboard"=>"tableau_bord", "file"=>"fichier", "calendar"=>"agenda", "link"=>"lien", "task"=>"tache", "user"=>"utilisateurs");
				foreach($newModuleNames as $modNewName=>$modOldName)	{self::query("UPDATE ap_joinSpaceModule SET moduleName='".$modNewName."' WHERE moduleName='".$modOldName."'");}

				////	MODIF LES "_idContainer" EMPTY
				$containerContentTables=array("ap_file","ap_link","ap_task","ap_contact");
				foreach($containerContentTables as $tmpTable)	{self::query("UPDATE ".$tmpTable." SET _idContainer='1' WHERE _idContainer IS NULL or _idContainer='0'");}

				////	MODIF LES LOGS & REINIT LES PREFERENCES DES USERS
				self::query("UPDATE ap_log SET action='add' WHERE action='ajout'");
				self::query("UPDATE ap_log SET action='delete' WHERE action='suppr'");
				self::query("DELETE FROM ap_userPreference WHERE keyVal!='tdb_periode' AND keyVal NOT LIKE 'type_affichage_%'");
				$prefUpdates=[];
				$prefUpdates[]="keyVal=REPLACE(keyVal,'tdb_periode','pluginPeriod')";
				$prefUpdates[]="value=REPLACE(value,'jour','day')";
				$prefUpdates[]="value=REPLACE(value,'semaine','week')";
				$prefUpdates[]="value=REPLACE(value,'mois','month')";
				$prefUpdates[]="value=REPLACE(value,'connexion','connect')";
				$prefUpdates[]="keyVal=REPLACE(keyVal,'type_affichage_fichier_','displayMode_fileFolder-')";
				$prefUpdates[]="keyVal=REPLACE(keyVal,'type_affichage_contact_','displayMode_contactFolder-')";
				$prefUpdates[]="keyVal=REPLACE(keyVal,'type_affichage_lien_','displayMode_linkFolder-')";
				$prefUpdates[]="keyVal=REPLACE(keyVal,'type_affichage_tache_','displayMode_taskFolder-')";
				$prefUpdates[]="value=REPLACE(value,'liste','line')";
				$prefUpdates[]="value=REPLACE(value,'bloc','block')";
				self::query("UPDATE ap_userPreference SET ".implode(", ",$prefUpdates));

				////	MAJ "ap_objectTarget" : RENOMME LES "targets" & LES TYPES D'OBJETS
				self::query("UPDATE ap_objectTarget SET target='allSpaces' WHERE target='tous_espaces'");
				self::query("UPDATE ap_objectTarget SET target='spaceUsers' WHERE target='tous'");
				self::query("UPDATE ap_objectTarget SET target='spaceGuests' WHERE target='invites'");
				$newTypeNames=array("dashboardNews"=>"actualite", "task"=>"tache", "taskFolder"=>"tache_dossier", "file"=>"fichier", "fileFolder"=>"fichier_dossier", "contactFolder"=>"contact_dossier", "link"=>"lien", "linkFolder"=>"lien_dossier", "forumSubject"=>"sujet", "forumMessage"=>"message", "calendar"=>"agenda", "calendarEvent"=>"evenement");
				foreach($newTypeNames as $typeNewName=>$typeOldName){
					self::query("UPDATE ap_objectTarget SET objectType='".$typeNewName."' WHERE objectType='".$typeOldName."'");
					self::query("UPDATE ap_objectAttachedFile SET objectType='".$typeNewName."' WHERE objectType='".$typeOldName."'");
				}

				////	TACHES
				//"PAS" DE 10%
				self::query("UPDATE ap_task SET advancement=ROUND(FLOOR(advancement/10)*10)");
				//Créé le champ "responsiblePersons"
				self::fieldExist("ap_task", "responsiblePersons", "ALTER TABLE ap_task ADD responsiblePersons TEXT DEFAULT NULL");
				//Récupère les données pour "responsiblePersons"
				if(self::tableExist("gt_tache_responsable"))
				{
					//Récupère les données. Note : le nom des champs ont été remplacés dans "gt_tache_responsable"
					foreach(self::getCol("SELECT _id FROM ap_task WHERE _id IN (select _idTask as _id from gt_tache_responsable)") as $tmpId){
						$responsiblePersons="@@";
						foreach(self::getCol("SELECT DISTINCT _idUser FROM gt_tache_responsable WHERE _idTask=".$tmpId) as $userId)   {$responsiblePersons.=$userId."@@";}
						self::query("UPDATE ap_task SET responsiblePersons=".self::format($responsiblePersons)." WHERE _id=".$tmpId);
					}
					//supprime la table "gt_tache_responsable"
					self::query("DROP TABLE gt_tache_responsable");
				}

				////	MAJ DANS LES DESCRIPTIONS DES "PATH" : FICHIERS JOINTS, DES PLUGINS TINYMCE, ETC
				$descriptionUpdates=array("ap_dashboardNews"=>"dashboardNews", "ap_calendarEvent"=>"calendarEvent", "ap_forumSubject"=>"forumSubject", "ap_forumMessage"=>"forumMessage", "ap_task"=>"task");
				foreach($descriptionUpdates as $tmpTable=>$objectType)
				{
					//Nouveau chemin des fichiers joint (images, mp3, videos)
					foreach(self::getTab("SELECT * FROM ap_objectAttachedFile WHERE objectType='".$objectType."'") as $tmpFile)
					{
						if(File::isType("editorImage",$tmpFile["name"]) || File::isType("mp3",$tmpFile["name"]) || File::isType("editorVideo",$tmpFile["name"]))
						{
							//chemin du fichier joint
							$fileExtension=".".File::extension($tmpFile["name"]);
							$oldPath="../".(Req::isHost()?PATH_DATAS:'stock_fichiers/')."fichiers_objet/".$tmpFile["_id"].$fileExtension;//ex: "../stock_fichiers/fichiers_objet/123.jpg"
							$newPath="index.php?ctrl=object&action=AttachedFileDisplay&_id=".$tmpFile["_id"]."&extension=".$fileExtension;//cf. "MdlObject.php"
							//Mp3 ("url_encode" : lecteur mp3)  ||  Videos 
							if(File::isType("mp3",$tmpFile["name"]))  {$newPath=urlencode($newPath);}
							elseif(File::isType("editorVideo",$tmpFile["name"])){
								$oldPath="../".$oldPath;//Racine depuis le player : "../../"
								$newPath="../".str_replace("fichiers_objet","objectAttachment",$oldPath);//Racine depuis le player : "../../../"
							}
							//Mets à jour le lien vers le fichier joint
							self::query("UPDATE ".$tmpTable." SET description=REPLACE(description,'".$oldPath."','".$newPath."')");
						}
					}
					//Chemins du dossier de fichiers joints (faire en 2 fois : "path_data" des "HOST"!), des anciens player video/mp3, des plugins tinyMCE, etc
					$descriptionUpdates=[];
					$descriptionUpdates[]="description=REPLACE(description,'../commun/dewplayer-mini.swf','app/misc/dewplayer.swf')";//player mp3
					$descriptionUpdates[]="description=REPLACE(description,'../divers/video','app/misc/jwplayer')";//player video
					$descriptionUpdates[]="description=REPLACE(description,'../divers/tiny_mce/plugins','app/js/tinymce/plugins')";//plugins tinymce
					$descriptionUpdates[]="description=REPLACE(description,'plugins/emotions','plugins/emoticons')";//idem
					$descriptionUpdates[]="description=REPLACE(description,'../module_fichier/index.php?id_dossier=','index.php?ctrl=file&typeId=fileFolder-')";//liens vers les dossiers de fichiers
					$descriptionUpdates[]="description=REPLACE(description,'../".(Req::isHost()?PATH_DATAS:'stock_fichiers/')."gestionnaire_fichiers/','".PATH_MOD_FILE."')";//liens vers les fichiers
					$descriptionUpdates[]="description=REPLACE(description,'stock_fichiers/','DATAS/')";
					self::query("UPDATE ".$tmpTable." SET ".implode(", ",$descriptionUpdates));
				}

				////	MAJ "ap_calendar" ET "ap_calendarEvent"
				self::query("UPDATE ap_calendar SET type='user' WHERE type='utilisateur'");
				foreach(self::getTab("SELECT * FROM ap_calendarEvent WHERE periodValues IS NOT NULL") as $tmpEvt){
					$newTmpValues=[];
					foreach(explode(",",$tmpEvt["periodValues"]) as $tmpVal)	{$newTmpValues[]=(int)$tmpVal;}
					self::query("UPDATE ap_calendarEvent SET periodValues=".self::formatTab2txt($newTmpValues)." WHERE _id=".$tmpEvt["_id"]);
				}
				self::query("UPDATE ap_calendarEvent SET periodType='weekDay' WHERE periodType='jour_semaine'");
				self::query("UPDATE ap_calendarEvent SET periodType='monthDay' WHERE periodType='jour_mois'");
				self::query("UPDATE ap_calendarEvent SET periodType='month' WHERE periodType='mois'");
				self::query("UPDATE ap_calendarEvent SET periodType='year' WHERE periodType='annee'");

				////	MAJ "ap_forumSubject"
				foreach(self::getTab("SELECT * FROM ap_forumSubject WHERE usersConsultLastMessage IS NOT NULL OR usersNotifyLastMessage IS NOT NULL") as $tmpSubject){
					if(!empty($tmpSubject["usersConsultLastMessage"]))	{$tmpSubject["usersConsultLastMessage"]=explode("uu",trim($tmpSubject["usersConsultLastMessage"],"u"));}
					if(!empty($tmpSubject["usersNotifyLastMessage"]))	{$tmpSubject["usersNotifyLastMessage"]=explode("uu",trim($tmpSubject["usersNotifyLastMessage"],"u"));}
					self::query("UPDATE ap_forumSubject SET usersConsultLastMessage=".self::formatTab2txt($tmpSubject["usersConsultLastMessage"]).", usersNotifyLastMessage=".self::formatTab2txt($tmpSubject["usersNotifyLastMessage"])." WHERE _id=".self::format($tmpSubject["_id"]));
				}

				////	AJOUT DE CHAMPS DATE ET AUTEUR
				foreach(array("ap_space","ap_user","ap_userGroup","ap_calendar","ap_calendarEventCategory","ap_forumTheme") as $tmpTable){
					self::fieldExist($tmpTable, "dateCrea",		"ALTER TABLE ".$tmpTable." ADD dateCrea DATETIME DEFAULT NULL");
					self::fieldExist($tmpTable, "_idUser",		"ALTER TABLE ".$tmpTable." ADD _idUser int DEFAULT NULL AFTER dateCrea");
					self::fieldExist($tmpTable, "dateModif",	"ALTER TABLE ".$tmpTable." ADD dateModif DATETIME DEFAULT NULL AFTER _idUser");
					self::fieldExist($tmpTable, "_idUserModif",	"ALTER TABLE ".$tmpTable." ADD _idUserModif int DEFAULT NULL AFTER dateModif");
				}

				////	MAJ DES GROUPES
				self::fieldExist("ap_userGroup", "_idSpace", "ALTER TABLE ap_userGroup ADD _idSpace int DEFAULT NULL AFTER title");//Créé le champ "_idSpace"
				foreach(self::getTab("SELECT * FROM ap_userGroup") as $tmpGroup)
				{
					//Users, Spaces et Affectations de l'ancien groupe
					$groupUserIds=Txt::txt2tab($tmpGroup["_idUsers"]);
					$groupSpaceIds=(empty($tmpGroup["_idSpaces"]) || $tmpGroup["_idSpaces"]=="all")  ?  self::getCol("SELECT _id FROM ap_space")  :  Txt::txt2tab($tmpGroup["_idSpaces"]);
					//Recréé un groupe par espace et ses affectations
					foreach($groupSpaceIds as $tmpIdSpace)
					{
						//Recréé le groupe pour l'espace
						$allUsersInSpace=(self::getVal("SELECT count(*) FROM ap_joinSpaceUser WHERE _idSpace=".$tmpIdSpace." AND allUsers=1")>0);//Tous les users du site sont affectés à l'espace?
						$groupUserIdsNew=($allUsersInSpace==true)  ?  $groupUserIds  :  array_intersect($groupUserIds, self::getCol("SELECT _idUser FROM ap_joinSpaceUser WHERE _idSpace=".$tmpIdSpace));//On prends tous les users du groupe d'origine  OU  les users affectés au groupe d'origine ET à l'espace courant
						$newGroupId=self::query("INSERT INTO ap_userGroup SET _idUser=".self::format($tmpGroup["_idUser"]).", title=".self::format($tmpGroup["title"]).", _idSpace=".self::format($tmpIdSpace).", _idUsers=".self::format(Txt::tab2txt($groupUserIdsNew)).", dateCrea=".self::format($tmpGroup["dateCrea"]), true);
						//Recréé les jointures des objets affectés à l'ancien espace->groupe (avec l'id du nouveau groupe)
						$groupOldAffectations=self::getTab("SELECT * FROM ap_objectTarget WHERE _idSpace=".self::format($tmpIdSpace)." AND target='G".(int)$tmpGroup["_id"]."'");
						foreach($groupOldAffectations as $tmpAffect){
							self::query("INSERT INTO ap_objectTarget SET objectType=".self::format($tmpAffect["objectType"]).", _idObject=".self::format($tmpAffect["_idObject"]).", _idSpace=".self::format($tmpIdSpace).", target=".self::format('G'.$newGroupId).", accessRight=".self::format($tmpAffect["accessRight"]));
						}
					}
					//Supprime l'ancien groupe et les anciennes affectations
					self::query("DELETE FROM ap_userGroup WHERE _id=".(int)$tmpGroup["_id"]);
					self::query("DELETE FROM ap_objectTarget WHERE target='G".(int)$tmpGroup["_id"]."'");
				}

				////	"DATAS/" : RENOMME LES SOUS-DOSSIERS DE "DATAS" && SUPPRIME LE DOSSIER "tmp" && CHMOD RECURSIF
				clearstatcache();//Réinit avant de faire un "rename()"!!
				$dirsToRename=array("gestionnaire_fichiers"=>PATH_MOD_FILE, "photos_utilisateurs"=>PATH_MOD_USER, "photos_contact"=>PATH_MOD_CONTACT, "fichiers_objet"=>PATH_OBJECT_ATTACHMENT, "fond_ecran"=>PATH_WALLPAPER_CUSTOM);
				foreach($dirsToRename as $oldDirName=>$newDirPath){
					$oldDirPath=PATH_DATAS.$oldDirName."/";
					if(is_dir($oldDirPath) && !is_dir($newDirPath))    {rename($oldDirPath,$newDirPath);}
				}
				File::setChmod(PATH_DATAS);

				////	"DATAS/" : DEPLACE/RECREE LES VIGNETTES D'IMAGE & SUPPRIME L'ANCIEN DOSSIER DE VIGNETTES (APRES MAJ!)
				$oldThumbDirPath=PATH_DATAS."gestionnaire_fichiers_vignettes/";
				if(is_dir($oldThumbDirPath))
				{
					//Liste les fichiers avec une vignette
					foreach(self::getObjTab("file","select * from ap_file where length(vignette)>0") as $tmpFile)
					{
						$thumbOk=false;
						$oldThumbPath=$oldThumbDirPath.$tmpFile->vignette;
						$newThumbPath=$tmpFile->thumbPath();
						$containerPathTmp=$tmpFile->containerObj()->folderPath("real");
						//Déplace la vignette?
						if(strlen($newThumbPath)>0 && is_file($oldThumbPath) && is_dir($containerPathTmp))  {$thumbOk=rename($oldThumbPath,$newThumbPath);}
						//Recréé la vignette?
						if($thumbOk==false && is_file($tmpFile->filePath()))  {$tmpFile->thumbEdit();}
					}
					File::rm($oldThumbDirPath);//Supprime l'ancien dossier des vignettes
				}

				////	"DATAS/" : AJOUTE "thumb" AUX PHOTOS D'USERS ET CONTACTS
				foreach(self::getTab("SELECT * FROM ap_user WHERE LENGTH(picture)>0") as $tmpPerson)
				{
					$tmpPersonImg=PATH_MOD_USER.$tmpPerson["picture"];
					if(is_file($tmpPersonImg)){
						File::imageResize($tmpPersonImg, PATH_MOD_USER.$tmpPerson["_id"]."_thumb.jpg", 200);
						File::rm($tmpPersonImg);
					}
				}
				foreach(self::getTab("SELECT * FROM ap_contact WHERE LENGTH(picture)>0") as $tmpPerson)
				{
					$tmpPersonImg=PATH_MOD_CONTACT.$tmpPerson["picture"];
					if(is_file($tmpPersonImg)){
						File::imageResize($tmpPersonImg, PATH_MOD_CONTACT.$tmpPerson["_id"]."_thumb.jpg", 200);
						File::rm($tmpPersonImg);
					}
				}

				////	"DATAS/" : MAJ DU "DATAS/.htaccess"  &&  CREATION DU "DATAS/wallpaper/.htaccess"  &&  MAJ DU "config.inc.php"
				$majHtaccess="Deny from all\n\n<Files ~ '(?i:thumb\.jpg|thumb\.png|thumb\.gif|.mp4|.webm|.ogg|.mkv|.flv)$'>\nAllow from all\n</Files>";
				File::rm(PATH_DATAS.".htaccess",false);
				$fp=fopen(PATH_DATAS.".htaccess", "w");
				fwrite($fp, $majHtaccess);
				fclose($fp);
				$majHtaccessBis="Deny from all\n\n<Files ~ '(?i:.jpg|.jpeg|.png|.gif)$'>\nAllow from all\n</Files>";
				File::rm(PATH_DATAS."wallpaper/.htaccess",false);
				$fp=fopen(PATH_DATAS."wallpaper/.htaccess", "w");
				fwrite($fp, $majHtaccessBis);
				fclose($fp);
				$deleteConst=array("agora_maintenance","controle_ip","duree_livecounter","duree_messages_messenger");
				if(Req::isHost())  {$deleteConst[]="db_host";}
				File::updateConfigFile(null,$deleteConst);

				////	MAJ DU LOGO DU FOOTER (POUR CORRESPONDRE AU .htaccess)
				$mainLogo=self::getVal("SELECT logo from ap_agora");
				if(!empty($mainLogo) && is_file(PATH_DATAS.$mainLogo))
				{
					$logoFileName="logo_thumb.".str_ireplace("jpeg","jpg",File::extension($mainLogo));
					File::imageResize(PATH_DATAS.$mainLogo, PATH_DATAS.$logoFileName, 200, 80);
					self::query("UPDATE ap_agora SET logo=".self::format($logoFileName));
					File::rm(PATH_DATAS.$mainLogo);
				}

				////	SUPPRESSION DE TABLE ET CHAMPS : A LA TOUTE FIN!
				if(self::tableExist("gt_module"))									{self::query("DROP TABLE gt_module");}
				if(self::fieldExist("ap_file","extension"))							{self::query("ALTER TABLE ap_file DROP extension");}
				if(self::fieldExist("ap_fileFolder","nom_reel"))					{self::query("ALTER TABLE ap_fileFolder DROP nom_reel");}
				if(self::fieldExist("ap_agora","adresse_web"))						{self::query("ALTER TABLE ap_agora DROP adresse_web");}
				if(self::fieldExist("ap_agora","mise_a_jour"))						{self::query("ALTER TABLE ap_agora DROP mise_a_jour");}
				if(self::fieldExist("ap_agora","editeur_text_mode"))				{self::query("ALTER TABLE ap_agora DROP editeur_text_mode");}
				if(self::fieldExist("ap_agora","edition_popup"))					{self::query("ALTER TABLE ap_agora DROP edition_popup");}
				if(self::fieldExist("ap_forumSubject","auteur_dernier_message"))	{self::query("ALTER TABLE ap_forumSubject DROP auteur_dernier_message");}
				if(self::fieldExist("ap_file","vignette"))							{self::query("ALTER TABLE ap_file DROP vignette");}
				if(self::fieldExist("ap_user","picture"))							{self::query("ALTER TABLE ap_user DROP picture");}
				if(self::fieldExist("ap_contact","picture"))						{self::query("ALTER TABLE ap_contact DROP picture");}
				if(self::fieldExist("ap_task","devise"))							{self::query("ALTER TABLE ap_task DROP devise");}
				if(self::fieldExist("ap_userGroup","_idSpaces"))					{self::query("ALTER TABLE ap_userGroup DROP _idSpaces");}
			}

			if(self::updateVersion("3.0.5"))
			{
				////	MODIF LES DROITS D'ACCÈS DU DOSSIER RACINE : DÉSORMAIS UNE OPTION DE CHAQUE ESPACE
				foreach(self::getTab("SELECT * FROM ap_objectTarget WHERE objectType like '%Folder' AND _idObject='1' AND accessRight='1'") as $tmpRight)
				{
					//Options du module de l'espace : Ajoute l'option "seul les admin peuvent ajouter du contenu à la racine"
					$sqlSpaceModuleOptions="_idSpace=".self::format($tmpRight["_idSpace"])." AND moduleName=".self::format(str_replace("Folder","",$tmpRight["objectType"]));
					$spaceModuleOptions=Txt::txt2tab(self::getVal("SELECT options FROM ap_joinSpaceModule WHERE ".$sqlSpaceModuleOptions));
					$spaceModuleOptions[]="AdminRootFolderAddContent";
					self::query("UPDATE ap_joinSpaceModule SET options=".self::format(Txt::tab2txt($spaceModuleOptions))." WHERE ".$sqlSpaceModuleOptions);
					//Supprime l'ancien droit d'accès du dossier racine
					self::query("DELETE FROM ap_objectTarget WHERE _idSpace=".self::format($tmpRight["_idSpace"])." AND objectType=".self::format($tmpRight["objectType"])." AND _idObject='1' AND accessRight='1'");
				}
			}

			if(self::updateVersion("3.1.5"))
			{
				////	MAJ DU "DATAS/.htaccess" ET SUPPR "DATAS/wallpaper/.htaccess"
				$majHtaccess="Deny from all\n\n<Files ~ '(?i:\.jpg|\.jpeg|\.png|\.gif|\.mp4|\.webm|\.ogg|\.mkv|\.flv)$'>\nAllow from all\n</Files>";
				file_put_contents(PATH_DATAS.".htaccess", $majHtaccess);
				File::rm(PATH_DATAS."wallpaper/.htaccess",false);
				////	TABLE "ap_agora" : "dateUpdateDb" au format "DATETIME"
				self::query("ALTER TABLE ap_agora CHANGE `dateUpdateDb` `dateUpdateDb` DATETIME DEFAULT NULL");
			}

			if(self::updateVersion("3.1.9"))
			{
				self::query("ALTER TABLE ap_file CHANGE `downloadsNb` `downloadsNb` smallint NOT NULL DEFAULT '0'");
			}

			if(self::updateVersion("3.1.10"))
			{
				//Update 'ap_userLivecouter' : ajoute 'editObjId' pour le controle de double édition
				self::fieldExist("ap_userLivecouter", "editObjId", "ALTER TABLE ap_userLivecouter ADD editObjId TINYTEXT DEFAULT NULL AFTER ipAdress");
				//Update 'ap_userLivecouter' : "_idUser" en cle primaire
				self::query("TRUNCATE TABLE ap_userLivecouter");//vide la table par precaution
				$isPrimaryKey=self::getTab("SHOW INDEXES FROM ap_userLivecouter WHERE Key_name LIKE 'PRIMARY'");
				if(empty($isPrimaryKey))	{self::query("ALTER TABLE ap_userLivecouter ADD PRIMARY KEY (`_idUser`)");}
			}

			if(self::updateVersion("3.2.0"))
			{
				//Modifie l'affichage du label des modules dans la barre de menu
				self::query("UPDATE ap_agora SET moduleLabelDisplay='hide' WHERE moduleLabelDisplay IS NULL");
				self::query("UPDATE ap_agora SET moduleLabelDisplay=null WHERE moduleLabelDisplay IS NOT NULL AND moduleLabelDisplay NOT LIKE 'hide'");
				//Fichiers joints : 'downloadsNb' doit avoir une valeur par défaut
				self::query("ALTER TABLE ap_objectAttachedFile CHANGE `downloadsNb` `downloadsNb` smallint NOT NULL DEFAULT '0'");
				//Enleve la selection de couleur dans le messenger
				if(self::fieldExist("ap_userMessengerMessage","color"))  {self::query("ALTER TABLE ap_userMessengerMessage DROP color");}
				//Enleve la gestion de l'affichage des evts d'agenda
				if(self::fieldExist("ap_calendar","evtColorDisplay"))  {self::query("ALTER TABLE ap_calendar DROP evtColorDisplay");}
			}

			if(self::updateVersion("3.2.2"))
			{
				//Ajoute le logo en page d'accueil
				self::fieldExist("ap_agora", "logoConnect", "ALTER TABLE ap_agora ADD logoConnect VARCHAR(255) DEFAULT NULL AFTER logoUrl");
				//Ajoute le parametrage sendmailFrom & SMTP
				self::fieldExist("ap_agora", "sendmailFrom", "ALTER TABLE ap_agora ADD sendmailFrom TINYTEXT DEFAULT NULL AFTER logsTimeOut");
				self::fieldExist("ap_agora", "smtpHost", "ALTER TABLE ap_agora ADD smtpHost TINYTEXT DEFAULT NULL AFTER sendmailFrom");
				self::fieldExist("ap_agora", "smtpPort", "ALTER TABLE ap_agora ADD smtpPort SMALLINT DEFAULT NULL AFTER smtpHost");
				self::fieldExist("ap_agora", "smtpSecure", "ALTER TABLE ap_agora ADD smtpSecure TINYTEXT DEFAULT NULL AFTER smtpPort");
				self::fieldExist("ap_agora", "smtpUsername", "ALTER TABLE ap_agora ADD smtpUsername TINYTEXT DEFAULT NULL AFTER smtpSecure");
				self::fieldExist("ap_agora", "smtpPass", "ALTER TABLE ap_agora ADD smtpPass TINYTEXT DEFAULT NULL AFTER smtpUsername");
			}

			if(self::updateVersion("3.2.3"))
			{
				//Modifie les anciennes dénominations des objets ('type') dans les Logs
				$newTypeNames=array("dashboardNews"=>"actualite", "task"=>"tache", "taskFolder"=>"tache_dossier", "file"=>"fichier", "fileFolder"=>"fichier_dossier", "contactFolder"=>"contact_dossier", "link"=>"lien", "linkFolder"=>"lien_dossier", "forumSubject"=>"sujet", "forumMessage"=>"message", "calendar"=>"agenda", "calendarEvent"=>"evenement");
				foreach($newTypeNames as $typeNewName=>$typeOldName)  {self::query("UPDATE ap_log SET objectType='".$typeNewName."' WHERE objectType='".$typeOldName."'");}
				//Suppression des affectations obsoletes aux dossiers racine (résiduelles)
				self::query("DELETE FROM ap_objectTarget WHERE objectType IN ('fileFolder','contactFolder','taskFolder','linkFolder') AND _idObject='1'");
				//Users/Contacts : Transfert de 'fax', 'website', 'skills', 'hobbies' dans le champ 'comment'
				foreach(["ap_user","ap_contact"] as $tmpTable){
					foreach(["fax","website","skills","hobbies"] as $tmpField){
						self::query("UPDATE ".$tmpTable." SET `comment`=CONCAT(comment, '\r\n-".$tmpField." : ', ".$tmpField.") WHERE comment IS NOT NULL");//'comment' n'est pas null : ajoute à la suite avec retour à la ligne
						self::query("UPDATE ".$tmpTable." SET `comment`=CONCAT('-".$tmpField." : ', ".$tmpField.") WHERE comment IS NULL");					//'comment' est null : ajoute directement les données
					}
				}
				//Simplifie la durée des logs : 15j devient 30j et 60j devient 120j
				self::query("UPDATE ap_agora SET logsTimeOut=30 WHERE logsTimeOut=15");
				self::query("UPDATE ap_agora SET logsTimeOut=120 WHERE logsTimeOut=60");
				//Affectations "allSpaces" obsoletes : transfert sur chaque espace
				$spaceList=self::getCol("SELECT _id FROM ap_space");
				foreach(self::getTab("SELECT * FROM ap_objectTarget WHERE target='allSpaces'") as $tmpAffect){
					foreach($spaceList as $tmpSpaceId)  {self::query("INSERT INTO ap_objectTarget SET objectType=".self::format($tmpAffect["objectType"]).", _idObject=".(int)$tmpAffect["_idObject"].", _idSpace=".(int)$tmpSpaceId.", target='spaceUsers', accessRight=".self::format($tmpAffect["accessRight"]));}
				}
				self::query("DELETE FROM ap_objectTarget WHERE target='allSpaces'");
			}

			if(self::updateVersion("3.2.4"))
			{
				//Delete les champs obsoletes de "ap_task" : "budgetAvailable" "budgetEngaged" "humanDayCharge"
				foreach(["budgetAvailable","budgetEngaged","humanDayCharge"] as $tmpField){
					if(self::fieldExist("ap_task",$tmpField))	{self::query("ALTER TABLE ap_task DROP ".$tmpField);}
				}
				//Delete les champs obsoletes de "ap_contact" et "ap_user" : "fax" "website" "skills" "hobbies"
				foreach(["ap_contact","ap_user"] as $tmpTable){
					foreach(["fax","website","skills","hobbies"] as $tmpField){
						if(self::fieldExist($tmpTable,$tmpField))	{self::query("ALTER TABLE ".$tmpTable." DROP ".$tmpField);}
					}
				}
			}

			if(self::updateVersion("3.3.1"))
			{
				//Ajoute La table "ap_objectLike"
				if(self::tableExist("ap_objectLike")==false){
					self::query("CREATE TABLE ap_objectLike (`objectType` varchar(255) not null, `_idObject` int not null, `_idUser` int not null, `value` tinyint not null) DEFAULT CHARSET=utf8");
					self::query("ALTER TABLE ap_objectLike ADD INDEX `indexes` (`objectType`(255), `_idObject`)");
				}
				//Ajoute la table "ap_objectComment"
				if(self::tableExist("ap_objectComment")==false){
					self::query("CREATE TABLE ap_objectComment	(_id int not null, objectType varchar(255) not null, _idObject int not null, _idUser int not null, dateCrea datetime not null, comment text not null) DEFAULT CHARSET=utf8");
					self::query("ALTER TABLE ap_objectComment ADD PRIMARY KEY (`_id`), ADD INDEX `indexes` (`_id`,`objectType`(255), `_idObject`)");
					self::query("ALTER TABLE ap_objectComment MODIFY _id int NOT NULL AUTO_INCREMENT");
				}
				//Ajoute  "usersLike" et "usersComment" à la table "ap_agora"
				if(self::fieldExist("ap_agora", "usersLike")==false){
					self::fieldExist("ap_agora", "usersLike",	"ALTER TABLE ap_agora ADD usersLike varchar(255) DEFAULT NULL AFTER footerHtml");
					self::fieldExist("ap_agora", "usersComment","ALTER TABLE ap_agora ADD usersComment tinyint DEFAULT NULL AFTER usersLike");
					self::query("UPDATE ap_agora SET usersLike='likeSimple', usersComment=1");
				}
				//Change le type "tinytext" en "varchar(255)"
				foreach(["sendmailFrom","smtpHost","smtpSecure","smtpUsername","smtpPass","editObjId"] as $tmpField){
					$tmpTable=($tmpField=="editObjId")  ?  "ap_userLivecouter"  :  "ap_agora";
					self::query("ALTER TABLE `".$tmpTable."` CHANGE `".$tmpField."` `".$tmpField."` varchar(255) DEFAULT NULL");
				}
				//Supprime les logs obsoletes
				self::query("DELETE FROM ap_log WHERE action='consult2'");
			}

			if(self::updateVersion("3.3.5"))
			{
				// MAJ DU "DATAS/.htaccess" (flv pour la rétrocompatibilité)
				$majHtaccess="Deny from all\n\n<Files ~ '(?i:\.jpg|\.jpeg|\.png|\.gif|\.mp3|\.mp4|\.webm|\.flv)$'>\nAllow from all\n</Files>";
				file_put_contents(PATH_DATAS.".htaccess", $majHtaccess);
				// Ajout du champ de gestion des icones de dossiers
				foreach(["ap_contactFolder","ap_fileFolder","ap_linkFolder","ap_taskFolder"] as $tmpTable)
					{self::fieldExist($tmpTable, "icon", "ALTER TABLE ".$tmpTable." ADD icon VARCHAR(255) DEFAULT NULL AFTER description");}
			}
			
			if(self::updateVersion("3.4.1"))
			{
				// Ajoute le type d'outil de cartographie utilisé ("gmap ou "leaflet") et l'Identifiant utilisé (pour gmap)
				self::fieldExist("ap_agora", "mapTool",		"ALTER TABLE ap_agora ADD mapTool varchar(255) DEFAULT 'gmap' AFTER usersComment");
				self::fieldExist("ap_agora", "mapApiKey",	"ALTER TABLE ap_agora ADD mapApiKey varchar(255) DEFAULT NULL AFTER mapTool");
			}

			if(self::updateVersion("3.4.2"))
			{
				//Ajoute le parametrage Google Signin
				self::fieldExist("ap_agora", "gSignin",			"ALTER TABLE ap_agora ADD gSignin tinyint DEFAULT NULL AFTER mapApiKey");
				self::fieldExist("ap_agora", "gSigninClientId",	"ALTER TABLE ap_agora ADD gSigninClientId varchar(255) DEFAULT NULL AFTER gSignin");//uniquement pour AP
				self::fieldExist("ap_agora", "gPeopleApiKey",	"ALTER TABLE ap_agora ADD gPeopleApiKey varchar(255) DEFAULT NULL AFTER gSigninClientId");//idem
				if(Req::isHost())  {self::query("UPDATE ap_agora SET gSignin=1");}
			}

			if(self::updateVersion("3.4.3"))
			{
				// MAJ DU "DATAS/.htaccess"
				$majHtaccess="order allow,deny \n\n <Files ~ '\.(?i:jpg|jpeg|png|gif|mp3|mp4|webm|flv)$'> \n allow from all \n </Files>";
				file_put_contents(PATH_DATAS.".htaccess", $majHtaccess);
				// Suppression du champ "ap_user">"ipControlAdresses"
				if(self::fieldExist("ap_user","ipControlAdresses"))  {self::query("ALTER TABLE ap_user DROP ipControlAdresses");}
			}

			if(self::updateVersion("3.4.4"))
			{
				//Ajoute le brouillon/draft de l'éditeur tinyMce
				self::fieldExist("ap_userLivecouter", "editorDraft", "ALTER TABLE ap_userLivecouter ADD editorDraft TEXT DEFAULT NULL AFTER editObjId");
				self::fieldExist("ap_userLivecouter", "draftTargetObjId", "ALTER TABLE ap_userLivecouter ADD draftTargetObjId TINYTEXT DEFAULT NULL AFTER editorDraft");
			}

			if(self::updateVersion("3.5.0"))
			{
				//Supprime l'ancien champ de réinit de password
				if(self::fieldExist("ap_user","_idNewPassword"))  {self::query("ALTER TABLE ap_user DROP _idNewPassword");}
				//Ajoute la table de sondage "ap_dashboardPoll"
				if(self::tableExist("ap_dashboardPoll")==false){
					self::query("CREATE TABLE ap_dashboardPoll (_id int NOT NULL,  title varchar(200) NOT NULL,  description varchar(2000) DEFAULT NULL,  dateEnd date DEFAULT NULL,  multipleResponses tinyint DEFAULT NULL,  newsDisplay tinyint DEFAULT NULL,  dateCrea datetime NOT NULL,  _idUser int NOT NULL,  dateModif datetime DEFAULT NULL,  _idUserModif int DEFAULT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8");
					self::query("ALTER TABLE ap_dashboardPoll ADD PRIMARY KEY (_id)");
					self::query("ALTER TABLE ap_dashboardPoll MODIFY _id int NOT NULL AUTO_INCREMENT");
				}
				//Ajoute la table de sondage "ap_dashboardPollResponse"
				if(self::tableExist("ap_dashboardPollResponse")==false){
					self::query("CREATE TABLE ap_dashboardPollResponse (_id varchar(255) NOT NULL,  _idPoll int NOT NULL,  label varchar(500) NOT NULL,  `rank` tinyint NOT NULL,  fileName varchar(200) DEFAULT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8");
					self::query("ALTER TABLE ap_dashboardPollResponse ADD PRIMARY KEY (_id(20))");
				}
				//Ajoute la table de sondage "ap_dashboardPollResponseVote"
				if(self::tableExist("ap_dashboardPollResponseVote")==false){
					self::query("CREATE TABLE ap_dashboardPollResponseVote (_idUser int NOT NULL, _idResponse varchar(255) NOT NULL, _idPoll int NOT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8");
					self::query("ALTER TABLE ap_dashboardPollResponseVote ADD PRIMARY KEY (_idUser,_idResponse(20))");
				}
				//Créé un exemple de sondage
				MdlDashboardPoll::dbFirstRecord();
				//Créé le dossier "DATAS/modDashboard"
				if(!file_exists(PATH_MOD_DASHBOARD)){
					$isCreated=mkdir(PATH_MOD_DASHBOARD);
					if($isCreated==true)  {File::setChmod(PATH_MOD_DASHBOARD);}
				}
				//Modifie le nom de certaines options de module
				self::query("UPDATE ap_joinSpaceModule SET options=REPLACE(options,'ajout_actualite_admin','adminAddNews')");
				self::query("UPDATE ap_joinSpaceModule SET options=REPLACE(options,'ajout_agenda_ressource_admin','adminAddRessourceCalendar')");
				self::query("UPDATE ap_joinSpaceModule SET options=REPLACE(options,'ajout_categorie_admin','adminAddCategory')");
				self::query("UPDATE ap_joinSpaceModule SET options=REPLACE(options,'ajout_sujet_admin','adminAddSubject')");
				self::query("UPDATE ap_joinSpaceModule SET options=REPLACE(options,'ajout_sujet_theme','allUsersAddTheme')");
				self::query("UPDATE ap_joinSpaceModule SET options=REPLACE(options,'ajout_utilisateurs_groupe','allUsersAddGroup')");
				self::query("UPDATE ap_joinSpaceModule SET options=REPLACE(options,'AdminRootFolderAddContent','adminRootAddContent')");
				//Rétablir le chemin des emoticones de tinymce
				foreach(["ap_dashboardNews","ap_forumMessage","ap_forumSubject"] as $tmpTable)	{self::query("UPDATE ".$tmpTable." SET description=REPLACE(description,'tinymce_4.8.2','tinymce')");}
			}

			if(self::updateVersion("3.6.3"))
			{
				//Supprime les tables "guest" dans les table ou il est présent (avec "_idUser"), sauf dans la table "ap_calendarEvent" !
				foreach(self::getCol("SHOW TABLES LIKE 'ap_%'") as $tmpTable){
					if(self::fieldExist($tmpTable,"guest") && self::fieldExist($tmpTable,"_idUser") && $tmpTable!="ap_calendarEvent")  {self::query("ALTER TABLE ".$tmpTable." DROP guest");}
				}
				//Corrige l'accès aux emoticons sur AP v3.5.0 (tinyMce v4.8.2)
				foreach(["ap_dashboardNews","ap_calendarEvent","ap_forumSubject","ap_forumMessage","ap_task"] as $tmpTable)  {self::query("UPDATE ".$tmpTable." SET description=REPLACE(description,'app/js/tinymce_4.8.2/','app/js/tinymce/')");}
				//Corrige les traductions des sondages par défaut
				self::query("UPDATE ap_dashboardPoll SET title=REPLACE(title,'What do you think of the new survey tool?',".self::format(Txt::trad("INSTALL_dataDashboardPoll")).")");
				self::query("UPDATE ap_dashboardPollResponse SET label=REPLACE(label,'Essential !',".self::format(Txt::trad("INSTALL_dataDashboardPollA"))."),  label=REPLACE(label,'Pretty interesting',".self::format(Txt::trad("INSTALL_dataDashboardPollB"))."),  label=REPLACE(label,'Not very useful',".self::format(Txt::trad("INSTALL_dataDashboardPollC")).")");
				//Affecte le sondage par défaut à tous les espaces disponibles
				foreach(self::getCol("SELECT _id FROM ap_space WHERE _id NOT IN (select _idSpace from ap_objectTarget where objectType='dashboardPoll' and _idObject=1)") as $_idSpace)  {self::query("INSERT INTO ap_objectTarget (objectType, _idObject, _idSpace, target, accessRight) VALUES ('dashboardPoll', 1, ".(int)$_idSpace.", 'spaceUsers', 1)");}
				//Suppression de l'ancien champ "personalCalendarsDisabled"
				if(self::fieldExist("ap_agora","personalCalendarsDisabled"))  {self::query("ALTER TABLE ap_agora DROP personalCalendarsDisabled");}
			}

			if(self::updateVersion("3.6.5"))
			{
				//Correction du champ "guest" pour les propositions d'événements
				self::fieldExist("ap_calendarEvent","guest",		"ALTER TABLE ap_calendarEvent ADD guest varchar(255) DEFAULT NULL AFTER _idUser");
				//Fichiers : Ajoute un champ pour la liste des personnes ayant téléchargé un fichier
				self::fieldExist("ap_file","downloadedBy",			"ALTER TABLE ap_file ADD downloadedBy varchar(10000) DEFAULT NULL AFTER downloadsNb");
				//Sondage : Ajoute une option pour pouvoir afficher le résultat de chaque votant 
				self::fieldExist("ap_dashboardPoll","publicVote",	"ALTER TABLE ap_dashboardPoll ADD publicVote tinyint DEFAULT NULL AFTER newsDisplay");
				//Suppression des affectations obsoletes aux dossiers racine (résiduelles)
				self::query("DELETE FROM ap_objectTarget WHERE objectType IN ('fileFolder','contactFolder','taskFolder','linkFolder') AND _idObject='1'");
			}

			if(self::updateVersion("3.7.0"))
			{
				//Durée par défaut des logs : 120 jours
				self::query("UPDATE ap_agora SET logsTimeOut=120 WHERE logsTimeOut=30");
				//Modifie la préférence d'affichage de l'agenda : "3days" devient "4days"
				self::query("UPDATE ap_userPreference SET value='4days' WHERE keyVal='calendarDisplayMode' AND value='3days'");
				//Augmente la taille max des commentaires des logs à 1000 caractères
				self::query("ALTER TABLE ap_log CHANGE `comment` `comment` VARCHAR(1000) DEFAULT NULL");
			}

			if(self::updateVersion("3.7.1"))
			{
				//Supprime les votes sur les anciens sondages "fantome"
				self::query("DELETE FROM ap_dashboardPollResponseVote WHERE _idPoll=0");
				//Ajoute le support des 'emoji' dans les messages du messenger : cf. 'utf8mb4'
				if(version_compare(PHP_VERSION,7,">="))  {self::query("ALTER TABLE ap_userMessengerMessage CHANGE `message` `message` TEXT CHARACTER SET utf8mb4");}
			}

			if(self::updateVersion("3.7.3.1"))
			{
				//Ajoute le paramétrage du serveur Jitsi
				self::fieldExist("ap_agora", "visioHost", "ALTER TABLE ap_agora ADD visioHost varchar(255) DEFAULT NULL AFTER logsTimeOut");
			}

			if(self::updateVersion("3.7.4.2"))
			{
				//Supprime si besoin l'ancien fichier PATH_WALLPAPER_CUSTOM/.htaccess
				if(is_file(PATH_WALLPAPER_CUSTOM.".htaccess"))  {File::rm(PATH_WALLPAPER_CUSTOM.".htaccess");}
				//Ajoute l'url de visio dans les evenements d'agenda
				self::fieldExist("ap_calendarEvent", "visioUrl", "ALTER TABLE ap_calendarEvent ADD visioUrl varchar(255) DEFAULT NULL AFTER contentVisible");
			}

			if(self::updateVersion("3.8.0"))
			{
				//Espace :  Renomme le champ 'usersInscription' en 'userInscription'  &&  Ajoute l'option de notif mail à l'admin après chaque inscription d'un user
				if(self::fieldExist("ap_space","usersInscription"))  {self::query("ALTER TABLE ap_space CHANGE `usersInscription` `userInscription` tinyint DEFAULT NULL");}
				self::fieldExist("ap_space", "userInscriptionNotify", "ALTER TABLE ap_space ADD userInscriptionNotify tinyint DEFAULT NULL AFTER userInscription");
				//Agenda :  Ajoute l'option de notification par email à chaque proposition d'événement  &&  Ajoute l'option de proposition d'événement pour les guests
				self::fieldExist("ap_calendar", "propositionNotify", "ALTER TABLE ap_calendar ADD `propositionNotify` varchar(1) DEFAULT NULL AFTER timeSlot");
				self::fieldExist("ap_calendar", "propositionGuest",  "ALTER TABLE ap_calendar ADD `propositionGuest` varchar(1) DEFAULT NULL AFTER propositionNotify");
				//Agenda et proposition d'evenement d'un guest :  Ajoute un champ "guestMail" pour les notifications par mail de validation/invalidation d'evt
				self::fieldExist("ap_calendarEvent", "guestMail", "ALTER TABLE ap_calendarEvent ADD `guestMail` varchar(255) DEFAULT NULL AFTER guest");
				//Agendas affectés à un espace public et avec "tous les users" en écriture : Précoche l'option "propositionGuest" 
				foreach(self::getCol("SELECT _idObject FROM ap_objectTarget WHERE objectType='calendar' AND `target`='spaceUsers' AND accessRight=2 AND _idSpace IN (select _id as _idSpace from ap_space where public=1)") as $idCalendar)
					{self::query("UPDATE ap_calendar SET propositionGuest=1 WHERE _id=".(int)$idCalendar);}
			}

			if(self::updateVersion("21.6"))
			{
				//Ajoute le mode d'affichage par défaut des objets (liste/block)
				self::fieldExist("ap_agora", "folderDisplayMode", "ALTER TABLE ap_agora ADD `folderDisplayMode` varchar(255) DEFAULT 'block' AFTER moduleLabelDisplay");
			}

			if(self::updateVersion("21.10"))
			{
				//Ajoute l'url alternative des visios
				self::fieldExist("ap_agora", "visioHostAlt", "ALTER TABLE ap_agora ADD `visioHostAlt` varchar(255) DEFAULT NULL AFTER visioHost");
				//Renomme la table des emails envoyés
				if(self::tableExist("ap_mailHistory"))  {self::query("RENAME TABLE `ap_mailHistory` TO `ap_mail`");}
				//Renomme le champ "editObjId" en "editTypeId"
				if(self::fieldExist("ap_userLivecouter","editObjId"))  {self::query("ALTER TABLE ap_userLivecouter CHANGE `editObjId` `editTypeId` TINYTEXT DEFAULT NULL");}
				//Renomme le champ "draftTargetObjId" en "draftTypeId"
				if(self::fieldExist("ap_userLivecouter","draftTargetObjId"))  {self::query("ALTER TABLE ap_userLivecouter CHANGE `draftTargetObjId` `draftTypeId` TINYTEXT DEFAULT NULL");}
				//Change le type de champ "ap_dashboardPoll.description"
				self::query("ALTER TABLE `ap_dashboardPoll` CHANGE `description` `description` TEXT DEFAULT NULL");
			}

			if(self::updateVersion("21.12.3"))
			{
				//Allège la gestion des connexions ldap
				if(self::fieldExist("ap_agora","ldap_crea_auto_users"))	{self::query("ALTER TABLE ap_agora DROP ldap_crea_auto_users");}
				if(self::fieldExist("ap_agora","ldap_pass_cryptage"))	{self::query("ALTER TABLE ap_agora DROP ldap_pass_cryptage");}
			}

			if(self::updateVersion("22.3.1"))
			{
				//Ré-Ajoute au besoin les champs "editorDraft" du Livecouter
				self::fieldExist("ap_userLivecouter", "editorDraft", "ALTER TABLE ap_userLivecouter ADD editorDraft TEXT DEFAULT NULL");
				self::fieldExist("ap_userLivecouter", "draftTypeId", "ALTER TABLE ap_userLivecouter ADD draftTypeId TINYTEXT DEFAULT NULL");
				//Ajoute le support des emojis (cf. utf8mb4) dans les descriptions d'objets et le editorDraft
				if(version_compare(PHP_VERSION,7,">=")){
					foreach(["MdlCalendarEvent","MdlDashboardNews","MdlDashboardPoll","MdlForumMessage","MdlForumSubject","MdlMail","MdlTask"] as $objMdl)
						{self::query("ALTER TABLE ".$objMdl::dbTable." CHANGE `description` `description` TEXT CHARACTER SET utf8mb4");}
					self::query("ALTER TABLE ap_userLivecouter CHANGE `editorDraft` `editorDraft` TEXT CHARACTER SET utf8mb4");
				}
				//Remplace l'url d'affichage des images dans les descriptions TinyMce (cf. "actionAttachedFileDisplay()")
				foreach(["ap_dashboardNews","ap_dashboardPoll","ap_calendarEvent","ap_forumSubject","ap_forumMessage","ap_task"] as $tmpTable)
					{self::query("UPDATE ".$tmpTable." SET description=REPLACE(description,'displayAttachedFile','attachedFileDisplay')");}
			}

			if(self::updateVersion("22.12.0"))
			{
				//Conversion en Utf8 d'anciennes tables en Latin1 (verifier que la table n'est pas deja en utf8/utf8mb4)
				foreach(["ap_userInscription","ap_log"] as $nom_table) {
					$tableCollationTmp=self::getVal("SELECT table_collation FROM information_schema.tables WHERE table_schema='".db_name."' AND table_name='".$nom_table."'");
					if(preg_match("/utf8/i",$tableCollationTmp)==false){
						self::query("ALTER TABLE ".$nom_table." CHARACTER SET UTF8");
						self::query("ALTER TABLE ".$nom_table." CONVERT TO CHARACTER SET UTF8");
					}
				}
				//Commentaires des logs en utf8mb4
				if(version_compare(PHP_VERSION,7,">="))  {self::query("ALTER TABLE ap_log CHANGE `comment` `comment` TEXT CHARACTER SET utf8mb4");}
			}

			if(self::updateVersion("23.2.3"))
			{
				//Renomme les champs "gSignin" et "gSigninClientId" en "gIdentity" et "gIdentityClientId" (cf. Google OAuth)
				if(self::fieldExist("ap_agora","gSignin"))  		{self::query("ALTER TABLE ap_agora CHANGE `gSignin` `gIdentity` tinyint DEFAULT NULL");}
				if(self::fieldExist("ap_agora","gSigninClientId"))  {self::query("ALTER TABLE ap_agora CHANGE `gSigninClientId` `gIdentityClientId` varchar(255) DEFAULT NULL");}
			}

			if(self::updateVersion("23.4.2"))
			{
				//Ajoute la table pour la connexion auto via token
				if(self::tableExist("ap_userAuthToken")==false)
					{self::query("CREATE TABLE `ap_userAuthToken` (`_idUser` int NOT NULL, `userAuthToken` varchar(255) NOT NULL, `dateCrea` datetime NOT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8");}
			}

			if(self::updateVersion("23.10.3"))
			{
				//Suppression d'anciens champs
				if(self::fieldExist("ap_user","picture"))			{self::query("ALTER TABLE `ap_user` DROP `picture`");}
				if(self::fieldExist("ap_user","passwordToken"))		{self::query("ALTER TABLE `ap_user` DROP `passwordToken`");}
				if(self::fieldExist("ap_contact","picture"))		{self::query("ALTER TABLE `ap_contact` DROP `picture`");}
			}

			if(self::updateVersion("24.2.3"))
			{
				//Agendas d'espace : ajoute la description "Agenda partagé de l'espace"
				foreach(self::getLine("SELECT `name` FROM ap_space") as $tmpSpaceName){
					self::query("UPDATE `ap_calendar` SET `description`=CONCAT(".self::format(Txt::trad("CALENDAR_sharedCalendarDescription")).") WHERE `type`='ressource' AND `title`=".self::format($tmpSpaceName));
				}
				//Forum : par défaut tout le monde peut ajouter des thèmes (même logique que les categories d'evt). Remplace donc l'option permissive "allUsersAddTheme" par une option restrictive "adminAddTheme"
				self::query("UPDATE `ap_joinSpaceModule` SET `options`=CONCAT('@@adminAddTheme@@') WHERE `moduleName`='forum' AND (`options` IS NULL OR `options` NOT LIKE '%allUsersAddTheme%')");	//Ajoute la nouvelle option
				self::query("UPDATE `ap_joinSpaceModule` SET `options`=REPLACE(`options`,'@@allUsersAddTheme@@','') WHERE `moduleName`='forum' AND `options` LIKE '%allUsersAddTheme%'");			//PUIS supprime l'ancienne
				//Task :  Modif la "priority" de "Critical" vers "High"  &&  Modif le type de "dateBegin"/"dateEnd" de "datetime" vers "date"
				self::query("UPDATE `ap_task` SET `priority`='3' WHERE `priority`='4'");
				self::query("ALTER TABLE `ap_task` CHANGE `dateBegin` `dateBegin` DATE NULL DEFAULT NULL");
				self::query("ALTER TABLE `ap_task` CHANGE `dateEnd` `dateEnd` DATE NULL DEFAULT NULL");
				//Task Kanban :  Créé le champ `_idStatus` dans la table "ap_task"  &&  Créé la table "ap_TaskStatus" des statuts/colonnes Kanban  &&   Créé les satuts/colonnes kanban de base
				self::fieldExist("ap_task", "_idStatus",  "ALTER TABLE `ap_task` ADD `_idStatus` int DEFAULT NULL AFTER `description`");
				self::tableExist("ap_taskStatus",  "CREATE TABLE `ap_taskStatus` (`_id` int NOT NULL AUTO_INCREMENT,  `_idSpaces` text,  `title` varchar(255) DEFAULT NULL,  `description` text,  `color` varchar(255) DEFAULT NULL,  `rank` smallint DEFAULT NULL,  `dateCrea` datetime DEFAULT NULL,  `_idUser` int DEFAULT NULL,  `dateModif` datetime DEFAULT NULL,  `_idUserModif` int DEFAULT NULL,  PRIMARY KEY (`_id`))  ENGINE=InnoDB DEFAULT CHARSET=utf8");
				MdlTaskStatus::dbFirstRecord();
				//Catégories d'evt  &&  Themes du forum : créé le champ `rank`
				self::fieldExist("ap_calendarEventCategory", "rank",	"ALTER TABLE `ap_calendarEventCategory` ADD `rank` smallint DEFAULT NULL AFTER `color`");
				self::fieldExist("ap_forumTheme", "rank",  				"ALTER TABLE `ap_forumTheme` ADD `rank` smallint DEFAULT NULL AFTER `color`");
			}

			if(self::updateVersion("24.4.5"))
			{
				//Renomme la table des catégories d'événement
				if(self::tableExist("ap_calendarEventCategory"))  {self::query("RENAME TABLE `ap_calendarEventCategory` TO `ap_calendarCategory`");}
				//Ajoute le champ "browserId" des tokens de connexion auto
				self::fieldExist("ap_userAuthToken", "browserId", "ALTER TABLE `ap_userAuthToken` ADD `browserId` varchar(255) AFTER `userAuthToken`");
			}

			if(self::updateVersion("24.6.4"))
			{
				//Ajoute le champ "userMailDisplay" pour pouvoir masquer l'emails des users
				self::fieldExist("ap_agora", "userMailDisplay", "ALTER TABLE `ap_agora` ADD `userMailDisplay` TINYINT DEFAULT '1' AFTER `personsSort`");
				//Champ "moduleLabelDisplay" : inverse la valeur en booleen  &&  Change le type du champ en booleen
				$moduleLabelDisplay=(Ctrl::$agora->moduleLabelDisplay=="hide")  ?  null  :  "1";
				self::query("UPDATE `ap_agora` SET `moduleLabelDisplay`=".self::format($moduleLabelDisplay));
				self::query("ALTER TABLE `ap_agora` CHANGE `moduleLabelDisplay` `moduleLabelDisplay` TINYINT DEFAULT '1'");
				//Inverse la valeur booleenne  &&  renomme le champ "messengerDisabled" en "messengerDisplay"
				if(self::fieldExist("ap_agora","messengerDisabled")){
					$messengerDisplay=(!empty(Ctrl::$agora->messengerDisabled))  ?  null  :  "1";
					self::query("UPDATE `ap_agora` SET `messengerDisabled`=".self::format($messengerDisplay));
					self::query("ALTER TABLE `ap_agora` CHANGE `messengerDisabled` `messengerDisplay` TINYINT DEFAULT '1'");
				}
				//Réinit les valeurs par défaut de "skin" et "folderDisplayMode"
				self::query("UPDATE `ap_agora` SET `skin`='white' WHERE `skin` IS NULL");
				self::query("UPDATE `ap_agora` SET `folderDisplayMode`='block' WHERE `folderDisplayMode` IS NULL");
				//Modif l'option "ap_agora">"usersLike" en booleen (anciennes valeurs possibles : "likeSimple" ou "likeOrNot")
				$usersLike=(!empty(Ctrl::$agora->usersLike))  ?  "1"  :  null;
				self::query("UPDATE `ap_agora` SET `usersLike`=".self::format($usersLike));
				self::query("ALTER TABLE `ap_agora` CHANGE `usersLike` `usersLike` TINYINT DEFAULT NULL");
				//Supprime les enregistrements "dontlike" (value!='1') puis supprime le champ "value"
				if(self::fieldExist("ap_objectLike","value")){
					self::query("DELETE FROM `ap_objectLike` WHERE `value`!='1'");
					self::query("ALTER TABLE `ap_objectLike` DROP `value`");
				}
			}

			if(self::updateVersion("24.11.2"))
			{
				//Modifie la préférence d'affichage de l'agenda : "4Days" -> "3Days" et "day" -> "week"
				self::query("UPDATE `ap_userPreference` SET value='3Days' WHERE keyVal='calendarDisplayMode' AND value='4Days'");
				self::query("UPDATE `ap_userPreference` SET value='week'  WHERE keyVal='calendarDisplayMode' AND value='day'");
			}

			if(self::updateVersion("25.1.0"))
			{
				//"gPeopleApiKey" & "mapApiKey" fusionnés avec "gApiKey"
				if(self::fieldExist("ap_agora","gPeopleApiKey"))	{self::query("ALTER TABLE `ap_agora` DROP `gPeopleApiKey`");}
				if(self::fieldExist("ap_agora", "mapApiKey"))		{self::query("ALTER TABLE `ap_agora` CHANGE `mapApiKey` `gApiKey` VARCHAR(255) DEFAULT NULL");}
			}

			if(self::updateVersion("25.3.3"))
			{
				//Ajoute "shortcut" à la table "ap_calendarEvent"
				self::fieldExist("ap_calendarEvent", "shortcut", "ALTER TABLE `ap_calendarEvent` ADD `shortcut` tinyint DEFAULT NULL AFTER `periodDateExceptions`");
			}

			if(self::updateVersion("25.6.4"))
			{
				//// Remplace les tags  <a href="xxxx" data-fancybox="images">  par  <a data-src="xxxx" data-fancybox="images">  (cf Fancybox v5)
				foreach(["ap_calendarEvent","ap_dashboardNews","ap_dashboardPoll","ap_forumMessage","ap_forumSubject","ap_mail","ap_task"] as $tableName){
					foreach(self::getTab("SELECT * FROM `".$tableName."` WHERE `description` like '%data-fancybox%'") as $tmpRecord){
						libxml_use_internal_errors(true);															//Evite les erreurs et avertissements
						$dom=new DOMDocument;																		//Créé un nouveau DOMDocument
						$dom->loadHTML($tmpRecord['description'], LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);	//Charge la description HTML (sans les éléments html/body...)
						libxml_clear_errors();																		//Vide le buffer d'erreur libxml
						$xpath=new DOMXPath($dom);																	//Créé un XPath pour naviguer dans le DOM
						$nodes=$xpath->query('//a[@data-fancybox="images"][@href]');								//Trouve les tags <a> avec l'attribut 'data-fancybox="images"' et 'href'
						foreach($nodes as $node){																	//Parcourt chaque tag
							$href=$node->getAttribute('href');														//Récupère la valeur de href
							$node->setAttribute('data-src', $href);													//Ajoute un attribut "data-src" avec la valeur de href
							$node->removeAttribute('href');															//Supprime le href
						}
						$newDescription=$dom->saveHTML();
						self::query("UPDATE `".$tableName."` SET `description`=".self::format($newDescription)." WHERE _id=".(int)$tmpRecord['_id']);
					}
				}
				//// Correction de libellé de catégorie d'evt
				self::query("UPDATE `ap_calendarCategory` SET title='réunion' WHERE title='rÃ©union'");
				self::query("UPDATE `ap_calendarCategory` SET title='congés' WHERE title='congÃ©s'");
			}

			if(self::updateVersion("25.10.4"))
			{
				//Remplace dans les News lightboxOpen() par .lightboxOpenHref
				$newsSearch ='href="javascript:lightboxOpen(\'?ctrl=user&action=SendInvitation\')"';
				$newsReplace='href="?ctrl=user&amp;action=SendInvitation" class="lightboxOpenHref"';
				self::query("UPDATE `ap_dashboardNews` SET `description`=REPLACE(`description`, ".self::format($newsSearch).", ".self::format($newsReplace).") ");
			}

			////////////////////////////////////////	MODIFIER   DB.SQL  +  VERSION.TXT  +  CHANGELOG.TXT   !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
			////////////////////////////////////////
			////////////////////////////////////////


			////	CHANGE LES "dateUpdateDb" + "version_agora" PUIS OPTIMISE LES TABLES
			self::query("UPDATE `ap_agora` SET `dateUpdateDb`=".self::dateNow().", `version_agora`='".Req::appVersion()."'");
			foreach(self::getCol("SHOW TABLES LIKE 'ap_%'") as $tableName)  {self::query("OPTIMIZE TABLE `".$tableName."`");}
			////	UPDATE OK : ON SUPPRIME $updateLock et $dumpPath 
			if(is_file($updateLock)){
				File::rm($updateLock);
				File::rm($dumpPath);
			}
			////	REINIT LA SESSION & REDIRECTION ..SANS DECONNECTER!
			$_SESSION=[];
			Ctrl::redir("?ctrl=offline");
		}
	}
}