<?php
/**
* This file is part of the Agora-Project Software package.
*
* @copyright (c) Agora-Project Limited <https://www.agora-project.net>
* @license GNU General Public License, version 2 (GPL-2.0)
*/


/*
 * Mises à jour
 */
class DbUpdate extends Db
{
	/*
	 * Récupère et controle la version de l'appli (+ rapide en session) : renvoi "true" si l'appli doit être mise à jour
	 */
	private static function versionAgoraUpdate($confirmVersion=false)
	{
		//"versionAgora" : Init OU Confirme la version
		if(empty($_SESSION["dbVersionAgora"]) || $confirmVersion==true){
			if(self::tableExist("ap_agora"))			{$_SESSION["dbVersionAgora"]=self::getVal("SELECT version_agora FROM ap_agora");}//Version 3.0 ou+
			elseif(self::tableExist("gt_agora_info"))	{$_SESSION["dbVersionAgora"]=(self::fieldExist("gt_agora_info","version_agora"))  ?  self::getVal("SELECT version_agora FROM gt_agora_info")  :  "2.0.0";}//Version 2.0 ou+ : le champ "version_agora" peut être absent..
			else										{throw new Exception("dbInstall_dbEmpty");}//Sinon renvoi une Exception "dbInstall"
		}
		//Renvoie "true" si l'appli doit être mise à jour en Bdd
		return version_compare($_SESSION["dbVersionAgora"],VERSION_AGORA,"<");
	}

	/*
	 * Mise à jour demandé plus récente que la "dbVersionAgora" : UPDATE!
	 */
	protected static function updateVersion($versionUpdate)
	{
		return (version_compare($_SESSION["dbVersionAgora"],$versionUpdate,"<") || $versionUpdate==null);
	}

	/*
	 * Teste si une table existe & la crée au besoin
	 */
	private static function tableExist($tableName, $createQuery=null)
	{
		$result=self::getCol("show tables like '".$tableName."'");
		if(empty($result) && !empty($createQuery))    {self::query($createQuery);}
		return (!empty($result));
	}

	/*
	 * Teste si un champ existe & le cree au besoin
	 */
	private static function fieldExist($tableName, $fieldName, $createQuery=null)
	{
		$result=self::getCol("show columns from `".$tableName."` like '".$fieldName."'");
		if(empty($result) && !empty($createQuery))    {self::query($createQuery);}
		return (!empty($result));
	}

	/*
	 * LANCE LA MISE À JOUR DE LA BDD
	 */
	public static function lauchUpdate()
	{
		////	RÉCUP LA VERSION DE L'APPLI && LANCE LA MISE A JOUR?
		if(self::versionAgoraUpdate())
		{
			////	VERIF DE BASE : VERSION DE PHP SI MIGRATION & ACCES AU FICHIER DE CONFIG
			Req::verifPhpVersion();
			if(!is_writable(PATH_DATAS."config.inc.php"))	{echo "<h2>Config file not writable (config.inc.php)</h2>";  exit;}
			////	DOUBLE VERIF (SI l'APPLI A DEJA ETE MAJ PAR UN AUTRE USER.. LE $_SESSION["dbVersionAgora"] DE L'USER COURANT DEVIENT ALORS OBSOLETE!)
			if(self::versionAgoraUpdate(true)==false)	{$_SESSION=array();  Ctrl::redir("?ctrl=offline");}/*Si ya une connexion auto de l'user, on met à jour de manière transparente : donc pas de "disconnect=true"!*/
			////	UPDATE EN COURS : NOTIFICATION ET SORTIE DE SCRIPT
			$updateLOCK=PATH_DATAS."updateLOCK.log";
			if(!is_file($updateLOCK))	{file_put_contents($updateLOCK,"LOCKING UPDATE - VERROUILAGE DE MISE A JOUR");}
			else{
				if((time()-filemtime($updateLOCK))<60)	{echo "<br>Update in progress : please wait a few seconds<br><br>Mise à jour en cours : merci d'attendre quelques secondes";}
				else									{echo "<br>The update generates errors : check the logs of Apache for details.<br>If the issue is resolved : come back to the previous version, delete the 'DATAS/updateLOCK.log' file, and try again the update procedure.<br><br>La mise a jour a généré des erreurs : consultez les logs d'Apache pour plus de détails.<br>Si le problème est résolu : revenez sur la version précédente, supprimez le fichier 'DATAS/updateLOCK.log', puis relancez la mise à jour.";}
				exit;
			}
			////	PAS D'INTERRUPTION DE SCRIPT
			ignore_user_abort(true);
			@set_time_limit(120);//désactivé en safemode 
			////	SAUVEGARDE LA BDD && MAJ SI BESOIN LA VERSION 2
			self::getDump();
			if(version_compare($_SESSION["dbVersionAgora"],"3.0.0","<"))	{DbUpdateOld::lauchUpdate();}

			////	MAJ V3.0.0
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
				foreach($tabsRenamed as $tableNameOld=>$tableNameNew)	{Db::query("RENAME TABLE `".$tableNameOld."` TO `".$tableNameNew."`");}

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
					$tableIndexes=array();
					foreach(self::getTab("SHOW INDEXES FROM ".$tableName." WHERE Key_name NOT LIKE 'PRIMARY'") as $tmpIndex)    {self::query("ALTER TABLE ".$tableName." DROP INDEX `".$tmpIndex["Key_name"]."`");}
					//Mise à jour des champs de chaque table
					foreach(self::getTab("SHOW COLUMNS FROM ".$tableName) as $tmpField)
					{
						//Nom et Propriétés du nouveau champ
						$fieldOldName=$fieldNewName=$tmpField["Field"];
						$isIdContainer=($fieldOldName=="id_dossier_parent" || preg_grep("/".$tableName."-".$fieldOldName."/i",$tabIdParentContainer)) ? true : false;//"preg_grep()" car "in_array()" est sensible à la casse, et sous windows les tables sont envoyées en minucules..
						if(strtolower($tmpField["Extra"])=="auto_increment")		{$fieldNewName=$primaryKey="_id";}					//Champs principal "_id" : cle primaire
						elseif($isIdContainer)										{$fieldNewName=$primaryKey="_idContainer";}			//Champs de l'objet parent : "id_dossier"=>"_idContainer"
						elseif(array_key_exists($fieldOldName,$tabFieldsRenamed))	{$fieldNewName=$tabFieldsRenamed[$fieldOldName];}	//Champ à renommer : "id_utilisateur"=>"_idUser"
						//Renomme le champ et ajoute le "type" et "Extra"
						$fieldProperties=$tmpField["Type"]." ".$tmpField["Extra"];//exple: "mediumint(8)"
						if(strtolower($tmpField["Null"])=="no")  {$fieldProperties.=" NOT NULL";}//exple: "unsigned auto_increment"
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
				self::query("UPDATE ap_agora SET skin='white' WHERE skin='blanc' OR skin is null");
				self::query("UPDATE ap_agora SET timezone=REPLACE(timezone,'.',':')");//"-5.00" devient "-5:00"
				//Supprime les doublons?
				$nbRows=Db::getVal("select count(*) from ap_agora");
				if($nbRows>1)	{Db::query("delete from ap_agora limit ".($nbRows-1));}

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
					foreach($oldWallpaperConserved as $oldFile=>$newFile)	{self::query("UPDATE ".$tmpTable." SET wallpaper=".Db::format($newFile)." WHERE wallpaper=".Db::format($oldFile));}
				}

				////	RENOMME DES MODULES DANS "ap_joinSpaceModule"
				$newModuleNames=array("dashboard"=>"tableau_bord", "file"=>"fichier", "calendar"=>"agenda", "link"=>"lien", "task"=>"tache", "user"=>"utilisateurs");
				foreach($newModuleNames as $modNewName=>$modOldName)	{self::query("UPDATE ap_joinSpaceModule SET moduleName='".$modNewName."' WHERE moduleName='".$modOldName."'");}

				////	MODIF LES "_idContainer" EMPTY
				$containerContentTables=array("ap_file","ap_link","ap_task","ap_contact");
				foreach($containerContentTables as $tmpTable)	{self::query("UPDATE ".$tmpTable." SET _idContainer='1' WHERE _idContainer is null or _idContainer='0'");}

				////	MODIF LES LOGS & REINIT LES PREFERENCES DES USERS
				self::query("UPDATE ap_log SET action='add' WHERE action='ajout'");
				self::query("UPDATE ap_log SET action='delete' WHERE action='suppr'");
				self::query("DELETE FROM ap_userPreference WHERE keyVal!='tdb_periode' AND keyVal NOT LIKE 'type_affichage_%'");
				$prefUpdates=array();
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
				//Créé le champs "responsiblePersons"
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
						if(File::isType("imageBrowser",$tmpFile["name"]) || File::isType("mp3",$tmpFile["name"]) || File::isType("videoPlayer",$tmpFile["name"]))
						{
							//chemin du fichier joint
							$fileExtension=".".File::extension($tmpFile["name"]);
							$oldPath="../".(Ctrl::isHost()?PATH_DATAS:'stock_fichiers/')."fichiers_objet/".$tmpFile["_id"].$fileExtension;//exple : "../stock_fichiers/fichiers_objet/123.jpg"
							$newPath="index.php?ctrl=object&action=DisplayAttachedFile&_id=".$tmpFile["_id"]."&extension=".$fileExtension;//cf. "MdlObjectAttributes.php"
							//Mp3 ("url_encode" : lecteur mp3)  ||  Videos 
							if(File::isType("mp3",$tmpFile["name"]))  {$newPath=urlencode($newPath);}
							elseif(File::isType("videoPlayer",$tmpFile["name"])){
								$oldPath="../".$oldPath;//Racine depuis le player : "../../"
								$newPath="../".str_replace("fichiers_objet","objectAttachment",$oldPath);//Racine depuis le player : "../../../"
							}
							//Mets à jour le lien vers le fichier joint
							self::query("UPDATE ".$tmpTable." SET description=REPLACE(description,'".$oldPath."','".$newPath."')");
						}
					}
					//Chemins du dossier de fichiers joints (faire en 2 fois : "path_data" des "HOST"!), des anciens player video/mp3, des plugins tinyMCE, etc
					$descriptionUpdates=array();
					$descriptionUpdates[]="description=REPLACE(description,'../commun/dewplayer-mini.swf','app/misc/dewplayer.swf')";//player mp3
					$descriptionUpdates[]="description=REPLACE(description,'../divers/video','app/misc/jwplayer')";//player video
					$descriptionUpdates[]="description=REPLACE(description,'../divers/tiny_mce/plugins','app/js/tinymce/plugins')";//plugins tinymce
					$descriptionUpdates[]="description=REPLACE(description,'plugins/emotions','plugins/emoticons')";//idem
					$descriptionUpdates[]="description=REPLACE(description,'../module_fichier/index.php?id_dossier=','index.php?ctrl=file&targetObjId=fileFolder-')";//liens vers les dossiers de fichiers
					$descriptionUpdates[]="description=REPLACE(description,'../".(Ctrl::isHost()?PATH_DATAS:'stock_fichiers/')."gestionnaire_fichiers/','".PATH_MOD_FILE."')";//liens vers les fichiers
					$descriptionUpdates[]="description=REPLACE(description,'stock_fichiers/','DATAS/')";
					self::query("UPDATE ".$tmpTable." SET ".implode(", ",$descriptionUpdates));
				}

				////	MAJ "ap_calendar" ET "ap_calendarEvent"
				db::query("UPDATE ap_calendar SET type='user' WHERE type='utilisateur'");
				foreach(Db::getTab("SELECT * FROM ap_calendarEvent WHERE periodValues is not null") as $tmpEvt){
					$newTmpValues=[];
					foreach(explode(",",$tmpEvt["periodValues"]) as $tmpVal)	{$newTmpValues[]=(int)$tmpVal;}
					Db::query("UPDATE ap_calendarEvent SET periodValues=".Db::formatTab2txt($newTmpValues)." WHERE _id=".$tmpEvt["_id"]);
				}
				db::query("UPDATE ap_calendarEvent SET periodType='weekDay' WHERE periodType='jour_semaine'");
				db::query("UPDATE ap_calendarEvent SET periodType='monthDay' WHERE periodType='jour_mois'");
				db::query("UPDATE ap_calendarEvent SET periodType='month' WHERE periodType='mois'");
				db::query("UPDATE ap_calendarEvent SET periodType='year' WHERE periodType='annee'");

				////	MAJ "ap_forumSubject"
				foreach(db::getTab("SELECT * FROM ap_forumSubject WHERE usersConsultLastMessage is not null OR usersNotifyLastMessage is not null") as $tmpSubject){
					if(!empty($tmpSubject["usersConsultLastMessage"]))	{$tmpSubject["usersConsultLastMessage"]=explode("uu",trim($tmpSubject["usersConsultLastMessage"],"u"));}
					if(!empty($tmpSubject["usersNotifyLastMessage"]))	{$tmpSubject["usersNotifyLastMessage"]=explode("uu",trim($tmpSubject["usersNotifyLastMessage"],"u"));}
					Db::query("UPDATE ap_forumSubject SET usersConsultLastMessage=".Db::formatTab2txt($tmpSubject["usersConsultLastMessage"]).", usersNotifyLastMessage=".Db::formatTab2txt($tmpSubject["usersNotifyLastMessage"])." WHERE _id=".Db::format($tmpSubject["_id"]));
				}

				////	AJOUT DE CHAMPS DATE ET AUTEUR
				foreach(array("ap_space","ap_user","ap_userGroup","ap_calendar","ap_calendarEventCategory","ap_forumTheme") as $tmpTable){
					self::fieldExist($tmpTable, "dateCrea", "ALTER TABLE ".$tmpTable." ADD dateCrea DATETIME DEFAULT NULL");
					self::fieldExist($tmpTable, "_idUser", "ALTER TABLE ".$tmpTable." ADD _idUser MEDIUMINT UNSIGNED DEFAULT NULL AFTER dateCrea");
					self::fieldExist($tmpTable, "dateModif", "ALTER TABLE ".$tmpTable." ADD dateModif DATETIME DEFAULT NULL AFTER _idUser");
					self::fieldExist($tmpTable, "_idUserModif", "ALTER TABLE ".$tmpTable." ADD _idUserModif MEDIUMINT UNSIGNED DEFAULT NULL AFTER dateModif");
				}

				////	MAJ DES GROUPES
				self::fieldExist("ap_userGroup", "_idSpace", "ALTER TABLE ap_userGroup ADD _idSpace MEDIUMINT UNSIGNED DEFAULT NULL AFTER title");//Créé le champ "_idSpace"
				foreach(Db::getTab("SELECT * FROM ap_userGroup") as $tmpGroup)
				{
					//Users, Spaces et Affectations de l'ancien groupe
					$groupUserIds=Txt::txt2tab($tmpGroup["_idUsers"]);
					$groupSpaceIds=(empty($tmpGroup["_idSpaces"]) || $tmpGroup["_idSpaces"]=="all")  ?  Db::getCol("SELECT _id FROM ap_space")  :  Txt::txt2tab($tmpGroup["_idSpaces"]);
					//Recréé un groupe par espace et ses affectations
					foreach($groupSpaceIds as $tmpIdSpace)
					{
						//Recréé le groupe pour l'espace
						$allUsersInSpace=(Db::getVal("SELECT count(*) FROM ap_joinSpaceUser WHERE _idSpace=".$tmpIdSpace." AND allUsers=1")>0)  ?  true  :  false;//Tous les users du site sont affectés à l'espace?
						$groupUserIdsNew=($allUsersInSpace==true)  ?  $groupUserIds  :  array_intersect($groupUserIds, Db::getCol("SELECT _idUser FROM ap_joinSpaceUser WHERE _idSpace=".$tmpIdSpace));//On prends tous les users du groupe d'origine  OU  les users affectés au groupe d'origine ET à l'espace courant
						$newGroupId=Db::query("INSERT INTO ap_userGroup SET _idUser=".Db::format($tmpGroup["_idUser"]).", title=".Db::format($tmpGroup["title"]).", _idSpace=".Db::format($tmpIdSpace).", _idUsers=".Db::format(Txt::tab2txt($groupUserIdsNew)).", dateCrea=".Db::format($tmpGroup["dateCrea"]), true);
						//Recréé les jointures des objets affectés à l'ancien espace->groupe (avec l'id du nouveau groupe)
						$groupOldAffectations=Db::getTab("SELECT * FROM ap_objectTarget WHERE _idSpace=".Db::format($tmpIdSpace)." AND target='G".(int)$tmpGroup["_id"]."'");
						foreach($groupOldAffectations as $tmpAffect){
							Db::query("INSERT INTO ap_objectTarget SET objectType=".Db::format($tmpAffect["objectType"]).", _idObject=".Db::format($tmpAffect["_idObject"]).", _idSpace=".Db::format($tmpIdSpace).", target=".Db::format('G'.$newGroupId).", accessRight=".Db::format($tmpAffect["accessRight"]));
						}
					}
					//Supprime l'ancien groupe et les anciennes affectations
					Db::query("DELETE FROM ap_userGroup WHERE _id=".(int)$tmpGroup["_id"]);
					Db::query("DELETE FROM ap_objectTarget WHERE target='G".(int)$tmpGroup["_id"]."'");
				}

				////	"DATAS/" : RENOMME LES SOUS-DOSSIERS DE "DATAS" && SUPPRIME LE DOSSIER "tmp" && CHMOD RECURSIF
				clearstatcache();//Réinit avant de faire un "rename()"!!
				$dirsToRename=array("gestionnaire_fichiers"=>PATH_MOD_FILE, "photos_utilisateurs"=>PATH_MOD_USER, "photos_contact"=>PATH_MOD_CONTACT, "fichiers_objet"=>PATH_OBJECT_ATTACHMENT, "fond_ecran"=>PATH_WALLPAPER_CUSTOM);
				foreach($dirsToRename as $oldDirName=>$newDirPath){
					$oldDirPath=PATH_DATAS.$oldDirName."/";
					if(is_dir($oldDirPath) && !is_dir($newDirPath))    {rename($oldDirPath,$newDirPath);}
				}
				if(is_dir(PATH_DATAS."tmp/"))	{File::rm(PATH_DATAS."tmp/");}
				File::setChmod(PATH_DATAS);

				////	"DATAS/" : DEPLACE/RECREE LES VIGNETTES D'IMAGE & SUPPRIME L'ANCIEN DOSSIER DE VIGNETTES (APRES MAJ!)
				$oldThumbDirPath=PATH_DATAS."gestionnaire_fichiers_vignettes/";
				if(is_dir($oldThumbDirPath))
				{
					//Liste les fichiers avec une vignette
					foreach(Db::getObjTab("file","select * from ap_file where length(vignette)>0") as $tmpFile)
					{
						$thumbOk=false;
						$oldThumbPath=$oldThumbDirPath.$tmpFile->vignette;
						$newThumbPath=$tmpFile->getThumbPath();
						$containerPathTmp=$tmpFile->containerObj()->folderPath("real");
						//Déplace la vignette?
						if(strlen($newThumbPath)>0 && is_file($oldThumbPath) && is_dir($containerPathTmp))	{$thumbOk=rename($oldThumbPath,$newThumbPath);}
						//Recréé la vignette?
						if($thumbOk==false && is_file($tmpFile->filePath()))	{$tmpFile->createThumb();}
					}
					File::rm($oldThumbDirPath);//Supprime l'ancien dossier des vignettes
				}

				////	"DATAS/" : AJOUTE "thumb" AUX PHOTOS D'USERS ET CONTACTS
				foreach(Db::getTab("SELECT * FROM ap_user WHERE LENGTH(picture)>0") as $tmpPerson)
				{
					$tmpPersonImg=PATH_MOD_USER.$tmpPerson["picture"];
					if(is_file($tmpPersonImg)){
						File::imageResize($tmpPersonImg, PATH_MOD_USER.$tmpPerson["_id"]."_thumb.jpg", 200);
						File::rm($tmpPersonImg);
					}
				}
				foreach(Db::getTab("SELECT * FROM ap_contact WHERE LENGTH(picture)>0") as $tmpPerson)
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
				if(Ctrl::isHost())  {$deleteConst[]="db_host";}
				File::updateConfigFile(null,$deleteConst);

				////	MAJ DU LOGO DU FOOTER (POUR CORRESPONDRE AU .htaccess)
				$mainLogo=db::getVal("SELECT logo from ap_agora");
				if(!empty($mainLogo) && is_file(PATH_DATAS.$mainLogo))
				{
					$logoFileName="logo_thumb.".str_ireplace("jpeg","jpg",File::extension($mainLogo));
					File::imageResize(PATH_DATAS.$mainLogo, PATH_DATAS.$logoFileName, 200, 80);
					db::query("UPDATE ap_agora SET logo=".Db::format($logoFileName));
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
			
			////	MAJ V3.0.5
			if(self::updateVersion("3.0.5"))
			{
				////	MODIF LES DROITS D'ACCÈS DU DOSSIER RACINE : DÉSORMAIS UNE OPTION DE CHAQUE ESPACE
				foreach(Db::getTab("SELECT * FROM ap_objectTarget WHERE objectType like '%Folder' AND _idObject='1' AND accessRight='1'") as $tmpRight)
				{
					//Options du module de l'espace : Ajoute l'option "seul les admin peuvent ajouter du contenu à la racine"
					$sqlSpaceModuleOptions="_idSpace=".Db::format($tmpRight["_idSpace"])." AND moduleName=".Db::format(str_replace("Folder","",$tmpRight["objectType"]));
					$spaceModuleOptions=Txt::txt2tab(Db::getVal("SELECT options FROM ap_joinSpaceModule WHERE ".$sqlSpaceModuleOptions));
					$spaceModuleOptions[]="AdminRootFolderAddContent";
					Db::query("UPDATE ap_joinSpaceModule SET options=".Db::format(Txt::tab2txt($spaceModuleOptions))." WHERE ".$sqlSpaceModuleOptions);
					//Supprime l'ancien droit d'accès du dossier racine
					Db::query("DELETE FROM ap_objectTarget WHERE _idSpace=".Db::format($tmpRight["_idSpace"])." AND objectType=".Db::format($tmpRight["objectType"])." AND _idObject='1' AND accessRight='1'");
				}
			}

			////	MAJ V3.1.5
			if(self::updateVersion("3.1.5"))
			{
				////	MAJ DU "DATAS/.htaccess" ET SUPPR "DATAS/wallpaper/.htaccess"
				$majHtaccess="Deny from all\n\n<Files ~ '(?i:\.jpg|\.jpeg|\.png|\.gif|\.mp4|\.webm|\.ogg|\.mkv|\.flv)$'>\nAllow from all\n</Files>";
				file_put_contents(PATH_DATAS.".htaccess", $majHtaccess);
				File::rm(PATH_DATAS."wallpaper/.htaccess",false);
				////	TABLE "ap_agora" : "dateUpdateDb" au format "DATETIME"
				self::query("ALTER TABLE ap_agora CHANGE `dateUpdateDb` `dateUpdateDb` DATETIME DEFAULT NULL");
			}

			////	MAJ V3.1.9
			if(self::updateVersion("3.1.9"))
			{
				self::query("ALTER TABLE ap_file CHANGE `downloadsNb` `downloadsNb` INT(10) UNSIGNED NOT NULL DEFAULT '0'");
			}

			////	MAJ V3.1.10
			if(self::updateVersion("3.1.10"))
			{
				//Update 'ap_userLivecouter' : ajoute 'editObjId' pour le controle de double édition
				self::fieldExist("ap_userLivecouter", "editObjId", "ALTER TABLE ap_userLivecouter ADD editObjId TINYTEXT DEFAULT NULL AFTER ipAdress");
				//Update 'ap_userLivecouter' : "_idUser" en cle primaire
				self::query("TRUNCATE TABLE ap_userLivecouter");//vide la table par precaution
				$isPrimaryKey=self::getTab("SHOW INDEXES FROM ap_userLivecouter WHERE Key_name LIKE 'PRIMARY'");
				if(empty($isPrimaryKey))	{self::query("ALTER TABLE ap_userLivecouter ADD PRIMARY KEY (`_idUser`)");}
			}
			
			////	MAJ V3.2.0
			if(self::updateVersion("3.2.0"))
			{
				//Modifie l'affichage du label des modules dans la barre de menu
				self::query("UPDATE ap_agora SET moduleLabelDisplay='hide' WHERE moduleLabelDisplay is null");
				self::query("UPDATE ap_agora SET moduleLabelDisplay=null WHERE moduleLabelDisplay is not null AND moduleLabelDisplay NOT LIKE 'hide'");
				//Fichiers joints : 'downloadsNb' doit avoir une valeur par défaut
				self::query("ALTER TABLE ap_objectAttachedFile CHANGE `downloadsNb` `downloadsNb` INT(10) UNSIGNED NOT NULL DEFAULT '0'");
				//Enleve la selection de couleur dans le messenger
				if(self::fieldExist("ap_userMessengerMessage","color"))  {self::query("ALTER TABLE ap_userMessengerMessage DROP color");}
				//Enleve la gestion de l'affichage des evts d'agenda
				if(self::fieldExist("ap_calendar","evtColorDisplay"))  {self::query("ALTER TABLE ap_calendar DROP evtColorDisplay");}
			}

			////	MAJ V3.2.2
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
			
			////	MAJ V3.2.3
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
						Db::query("UPDATE ".$tmpTable." SET comment=CONCAT(comment, '\r\n-".$tmpField." : ', ".$tmpField.") WHERE comment IS NOT NULL");//'comment' n'est pas null : ajoute à la suite avec retour à la ligne
						Db::query("UPDATE ".$tmpTable." SET comment=CONCAT('-".$tmpField." : ', ".$tmpField.") WHERE comment IS NULL");					//'comment' est null : ajoute directement les données
					}
				}
				//Simplifie la durée des logs : 15j devient 30j et 60j devient 120j
				Db::query("UPDATE ap_agora SET logsTimeOut=30 WHERE logsTimeOut=15");
				Db::query("UPDATE ap_agora SET logsTimeOut=120 WHERE logsTimeOut=60");
				//Affectations "allSpaces" obsoletes : transfert sur chaque espace
				$spaceList=Db::getCol("SELECT _id FROM ap_space");
				foreach(Db::getTab("SELECT * FROM ap_objectTarget WHERE target='allSpaces'") as $tmpAffect){
					foreach($spaceList as $tmpSpaceId)  {Db::query("INSERT INTO ap_objectTarget SET objectType=".Db::format($tmpAffect["objectType"]).", _idObject=".(int)$tmpAffect["_idObject"].", _idSpace=".(int)$tmpSpaceId.", target='spaceUsers', accessRight=".Db::format($tmpAffect["accessRight"]));}
				}
				Db::query("DELETE FROM ap_objectTarget WHERE target='allSpaces'");
			}

			////	MAJ V3.2.4
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

			////	MAJ V3.3.1
			if(self::updateVersion("3.3.1"))
			{
				//Ajoute La table "ap_objectLike"
				if(self::tableExist("ap_objectLike")==false){
					self::query("CREATE TABLE ap_objectLike (objectType varchar(255) not null, _idObject mediumint(8) not null, _idUser mediumint(8) not null, value tinyint(1) not null) DEFAULT CHARSET=utf8");
					self::query("ALTER TABLE ap_objectLike ADD INDEX `indexes` (`objectType`(255), `_idObject`)");
				}
				//Ajoute la table "ap_objectComment"
				if(self::tableExist("ap_objectComment")==false){
					self::query("CREATE TABLE ap_objectComment	(_id mediumint(8) unsigned not null, objectType varchar(255) not null, _idObject mediumint(8) not null, _idUser mediumint(8) not null, dateCrea datetime not null, comment text not null) DEFAULT CHARSET=utf8");
					self::query("ALTER TABLE ap_objectComment ADD PRIMARY KEY (`_id`), ADD INDEX `indexes` (`_id`,`objectType`(255), `_idObject`)");
					self::query("ALTER TABLE ap_objectComment MODIFY _id mediumint(8) unsigned NOT NULL AUTO_INCREMENT");
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

			////	MAJ V3.3.5 (et aussi V3.3.4 pour la gestion des "icon")
			if(self::updateVersion("3.3.5"))
			{
				// MAJ DU "DATAS/.htaccess" (flv pour la rétrocompatibilité)
				$majHtaccess="Deny from all\n\n<Files ~ '(?i:\.jpg|\.jpeg|\.png|\.gif|\.mp3|\.mp4|\.webm|\.flv)$'>\nAllow from all\n</Files>";
				file_put_contents(PATH_DATAS.".htaccess", $majHtaccess);
				// Ajout du champ de gestion des icones de dossiers
				foreach(["ap_contactFolder","ap_fileFolder","ap_linkFolder","ap_taskFolder"] as $tmpTable)
					{self::fieldExist($tmpTable, "icon", "ALTER TABLE ".$tmpTable." ADD icon VARCHAR(255) DEFAULT NULL AFTER description");}
			}
			
			////	MAJ V3.4.1
			if(self::updateVersion("3.4.1"))
			{
				// Ajoute le type d'outil de cartographie utilisé (Gmap ou Leaflet) et l'Identifiant utilisé (pour gmap)
				self::fieldExist("ap_agora", "mapTool",		"ALTER TABLE ap_agora ADD mapTool varchar(255) DEFAULT 'gmap' AFTER usersComment");
				self::fieldExist("ap_agora", "mapApiKey",	"ALTER TABLE ap_agora ADD mapApiKey varchar(255) DEFAULT NULL AFTER mapTool");
			}
			////	MAJ V3.4.2
			if(self::updateVersion("3.4.2"))
			{
				//Ajoute le parametrage Google Signin
				self::fieldExist("ap_agora", "gSignin",			"ALTER TABLE ap_agora ADD gSignin tinyint DEFAULT NULL AFTER mapApiKey");
				self::fieldExist("ap_agora", "gSigninClientId",	"ALTER TABLE ap_agora ADD gSigninClientId varchar(255) DEFAULT NULL AFTER gSignin");//uniquement pour AP
				self::fieldExist("ap_agora", "gPeopleApiKey",	"ALTER TABLE ap_agora ADD gPeopleApiKey varchar(255) DEFAULT NULL AFTER gSigninClientId");//idem
				if(Ctrl::isHost())  {self::query("UPDATE ap_agora SET gSignin=1");}
			}
			////	MAJ V3.4.3
			if(self::updateVersion("3.4.3"))
			{
				// MAJ DU "DATAS/.htaccess"
				$majHtaccess="order allow,deny \n\n <Files ~ '\.(?i:jpg|jpeg|png|gif|mp3|mp4|webm|flv)$'> \n allow from all \n </Files>";
				file_put_contents(PATH_DATAS.".htaccess", $majHtaccess);
				// Suppression du champ "ap_user">"ipControlAdresses"
				if(self::fieldExist("ap_user","ipControlAdresses"))  {self::query("ALTER TABLE ap_user DROP ipControlAdresses");}
			}
			////	MAJ V3.4.4
			if(self::updateVersion("3.4.4"))
			{
				//Ajoute le brouillon/draft de l'éditeur tinyMce
				self::fieldExist("ap_userLivecouter", "editorDraft", "ALTER TABLE ap_userLivecouter ADD editorDraft TEXT DEFAULT NULL AFTER editObjId");
				self::fieldExist("ap_userLivecouter", "draftTargetObjId", "ALTER TABLE ap_userLivecouter ADD draftTargetObjId TINYTEXT DEFAULT NULL AFTER editorDraft");
			}
			////	MAJ V3.5.0
			if(self::updateVersion("3.5.0"))
			{
				//Supprime l'ancien champ de réinit de password
				if(self::fieldExist("ap_user","_idNewPassword"))  {self::query("ALTER TABLE ap_user DROP _idNewPassword");}
				//Ajoute la table de sondage "ap_dashboardPoll"
				if(self::tableExist("ap_dashboardPoll")==false){
					self::query("CREATE TABLE ap_dashboardPoll (_id mediumint(8) unsigned NOT NULL,  title varchar(200) NOT NULL,  description varchar(2000) DEFAULT NULL,  dateEnd date DEFAULT NULL,  multipleResponses tinyint(1) unsigned DEFAULT NULL,  newsDisplay tinyint(1) unsigned DEFAULT NULL,  dateCrea datetime NOT NULL,  _idUser mediumint(8) unsigned NOT NULL,  dateModif datetime DEFAULT NULL,  _idUserModif mediumint(8) unsigned DEFAULT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8");
					self::query("ALTER TABLE ap_dashboardPoll ADD PRIMARY KEY (_id)");
					self::query("ALTER TABLE ap_dashboardPoll MODIFY _id mediumint(8) unsigned NOT NULL AUTO_INCREMENT");
				}
				//Ajoute la table de sondage "ap_dashboardPollResponse"
				if(self::tableExist("ap_dashboardPollResponse")==false){
					self::query("CREATE TABLE ap_dashboardPollResponse (_id varchar(255) NOT NULL,  _idPoll mediumint(8) unsigned NOT NULL,  label varchar(500) NOT NULL,  rank tinyint(2) unsigned NOT NULL,  fileName varchar(200) DEFAULT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8");
					self::query("ALTER TABLE ap_dashboardPollResponse ADD PRIMARY KEY (_id(20))");
				}
				//Ajoute la table de sondage "ap_dashboardPollResponseVote"
				if(self::tableExist("ap_dashboardPollResponseVote")==false){
					self::query("CREATE TABLE ap_dashboardPollResponseVote (_idUser mediumint(8) unsigned NOT NULL,  _idResponse varchar(255) NOT NULL,  _idPoll mediumint(8) unsigned NOT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8");
					self::query("ALTER TABLE ap_dashboardPollResponseVote ADD PRIMARY KEY (_idUser,_idResponse(20))");
					$createPollTable=true;
				}
				//Créé un exemple de sondage
				if(!empty($createPollTable)){
					self::query("INSERT INTO ap_dashboardPoll SET _id=1, title=".Db::format(Txt::trad("INSTALL_dataDashboardPoll")).", _idUser=1, newsDisplay=1, dateCrea=NOW()");
					self::query("INSERT INTO ap_dashboardPollResponse (_id, _idPoll, label, rank) VALUES ('5bd1903d3df9u8t',1,".Db::format(Txt::trad("INSTALL_dataDashboardPollA")).",1), ('5bd1903d3e11dt5',1,".Db::format(Txt::trad("INSTALL_dataDashboardPollB")).",2), ('5bd1903d3e041p7',1,".Db::format(Txt::trad("INSTALL_dataDashboardPollC")).",3)");
					self::query("INSERT INTO ap_objectTarget (objectType, _idObject, _idSpace, target, accessRight) VALUES ('dashboardPoll', 1, 1, 'spaceUsers', 1)");
				}
				//Créé le dossier "DATAS/modDashboard"
				if(!file_exists(PATH_MOD_DASHBOARD)){
					$isCreated=mkdir(PATH_MOD_DASHBOARD);
					if($isCreated==true)  {File::setChmod(PATH_MOD_DASHBOARD);}
				}
				//Modifie le nom de certaines options de module
				self::query("UPDATE ap_joinSpaceModule SET options=REPLACE(options,'ajout_utilisateurs_groupe','allUsersAddGroup')");
				self::query("UPDATE ap_joinSpaceModule SET options=REPLACE(options,'ajout_actualite_admin','adminAddNews')");
				self::query("UPDATE ap_joinSpaceModule SET options=REPLACE(options,'ajout_agenda_ressource_admin','adminAddRessourceCalendar')");
				self::query("UPDATE ap_joinSpaceModule SET options=REPLACE(options,'ajout_categorie_admin','adminAddCategory')");
				self::query("UPDATE ap_joinSpaceModule SET options=REPLACE(options,'ajout_sujet_admin','adminAddSubject')");
				self::query("UPDATE ap_joinSpaceModule SET options=REPLACE(options,'ajout_sujet_theme','allUsersAddTheme')");
				self::query("UPDATE ap_joinSpaceModule SET options=REPLACE(options,'AdminRootFolderAddContent','adminRootAddContent')");
				//Rétablir le chemin des emoticones de tinymce
				foreach(["ap_dashboardNews","ap_forumMessage","ap_forumSubject"] as $tmpTable)	{self::query("UPDATE ".$tmpTable." SET description=REPLACE(description,'tinymce_4.8.2','tinymce')");}
			}
			////	MAJ V3.6.3
			if(self::updateVersion("3.6.3"))
			{
				//Supprime les tables "guest" dans les table ou il est présent (avec "_idUser"), sauf dans la table "ap_calendarEvent"
				foreach(self::getCol("SHOW TABLES LIKE 'ap_%'") as $tmpTable){
					if(self::fieldExist($tmpTable,"guest") && self::fieldExist($tmpTable,"_idUser") && $tmpTable!="ap_calendarEvent")  {self::query("ALTER TABLE ".$tmpTable." DROP guest");}
				}
				//Corrige l'accès aux emoticons sur AP v3.5.0 (tinyMce v4.8.2)
				foreach(["ap_dashboardNews","ap_calendarEvent","ap_forumSubject","ap_forumMessage","ap_task"] as $tmpTable)  {self::query("UPDATE ".$tmpTable." SET description=REPLACE(description,'app/js/tinymce_4.8.2/','app/js/tinymce/')");}
				//Corrige les traductions des sondages par défaut
				self::query("UPDATE ap_dashboardPoll SET title=REPLACE(title,'What do you think of the new survey tool?','".Txt::trad("INSTALL_dataDashboardPoll")."')");
				self::query("UPDATE ap_dashboardPollResponse SET label=REPLACE(label,'Essential !',".Db::format(Txt::trad("INSTALL_dataDashboardPollA"))."),  label=REPLACE(label,'Pretty interesting',".Db::format(Txt::trad("INSTALL_dataDashboardPollB"))."),  label=REPLACE(label,'Not very useful',".Db::format(Txt::trad("INSTALL_dataDashboardPollC")).")");
				//Affecte le sondage par défaut à tous les espaces disponibles
				foreach(Db::getCol("SELECT _id FROM ap_space WHERE _id NOT IN (select _idSpace from ap_objectTarget where objectType='dashboardPoll' and _idObject=1)") as $_idSpace)  {self::query("INSERT INTO ap_objectTarget (objectType, _idObject, _idSpace, target, accessRight) VALUES ('dashboardPoll', 1, ".(int)$_idSpace.", 'spaceUsers', 1)");}
				//Suppression de l'ancien champ "personalCalendarsDisabled"
				if(self::fieldExist("ap_agora","personalCalendarsDisabled"))  {self::query("ALTER TABLE ap_agora DROP personalCalendarsDisabled");}
			}
			////	MAJ V3.6.5
			if(self::updateVersion("3.6.5"))
			{
				//Correction du champ "guest" pour les propositions d'événements
				self::fieldExist("ap_calendarEvent","guest",		"ALTER TABLE ap_calendarEvent ADD guest varchar(255) DEFAULT NULL AFTER _idUser");
				//Fichiers : Ajoute un champ pour la liste des personnes ayant téléchargé un fichier
				self::fieldExist("ap_file","downloadedBy",			"ALTER TABLE ap_file ADD downloadedBy varchar(10000) DEFAULT NULL AFTER downloadsNb");
				//Sondage : Ajoute une option pour pouvoir afficher le résultat de chaque votant 
				self::fieldExist("ap_dashboardPoll","publicVote",	"ALTER TABLE ap_dashboardPoll ADD publicVote tinyint(1) DEFAULT NULL AFTER newsDisplay");
				//Suppression des affectations obsoletes aux dossiers racine (résiduelles)
				self::query("DELETE FROM ap_objectTarget WHERE objectType IN ('fileFolder','contactFolder','taskFolder','linkFolder') AND _idObject='1'");
			}
			////	MAJ V3.7.0
			if(self::updateVersion("3.7.0"))
			{
				//Durée par défaut des logs : 120 jours
				Db::query("UPDATE ap_agora SET logsTimeOut=120 WHERE logsTimeOut=30");
				//Modifie la préférence d'affichage de l'agenda : "3days" devient "4days"
				Db::query("UPDATE ap_userPreference SET value='4days' WHERE keyVal='calendarDisplayMode' AND value='3days'");
				//Augmente la taille max des commentaires des logs à 1000 caractères
				Db::query("ALTER TABLE ap_log CHANGE `comment` `comment` VARCHAR(1000) DEFAULT NULL");
			}
			////	MAJ V3.7.1
			if(self::updateVersion("3.7.1"))
			{
				//Supprime les votes sur les anciens sondages "fantome"
				Db::query("DELETE FROM ap_dashboardPollResponseVote WHERE _idPoll=0");
				//Ajoute le support des 'emoji' dans les messages du messenger : cf. 'utf8mb4'
				if(version_compare(PHP_VERSION,7,">="))  {Db::query("ALTER TABLE ap_userMessengerMessage CHANGE `message` `message` TEXT CHARACTER SET utf8mb4");}
			}
			////	MAJ V3.7.2
			if(self::updateVersion("3.7.2"))
			{
				//Ajoute le paramétrage du serveur Jitsi
				self::fieldExist("ap_agora", "visioHost", "ALTER TABLE ap_agora ADD visioHost varchar(255) DEFAULT NULL AFTER logsTimeOut");
			}
			///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
			////!!!!	MODIFIER SI BESOIN LA BDD "ModOffline/db.sql"	!!!!!!!!!!!!
			////!!!!	MODIFIER LE NUMERO DE VERSION DANS  "app/Common/Params.php" + "app/Common/VueStructure.php" + "app/js/common-3.x.x.js"  +  "app/css/common-3.x.x.js" + "RELEASES.txt"
			///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
			///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////



			////	MAJ "dateUpdateDb" && "version_agora"
			self::query("UPDATE ap_agora SET dateUpdateDb=".Db::dateNow().", version_agora='".VERSION_AGORA."'");
			////	SUPPRIME $updateLOCK
			File::rm($updateLOCK);
			////	OPTIMISE LES TABLES
			foreach(self::getCol("SHOW TABLES LIKE 'ap_%'") as $tableName)    {self::query("OPTIMIZE TABLE `".$tableName."`");}
			////	REINIT LA SESSION & REDIRECTION
			$_SESSION=[];
			Ctrl::redir("?ctrl=offline");
		}
	}
}