<?php
/**
* This file is part of the Agora-Project Software package.
*
* @copyright (c) Agora-Project Limited <https://www.agora-project.net>
* @license GNU General Public License, version 2 (GPL-2.0)
*/


/*
 * Anciennes mises à jours jusqu'a v2.17.5
 */
class DbUpdateOld extends DbUpdate
{
	/*
	 * Update simple (insert, change..)
	 */
	public static function updateQueryOld($versionUpdate, $sqlQuery)
	{
		if(self::updateVersion($versionUpdate))	{self::query($sqlQuery);}
	}

	/*
	 * Teste si une table existe & la crée au besoin (return false si la table n'existait pas à l'origine)
	 */
	public static function tableExistOld($versionUpdate, $table, $sqlQuery=null)
	{
		$tabResult=self::getCol("show tables like '".$table."'");
		if(self::updateVersion($versionUpdate) && !empty($sqlQuery) && empty($tabResult))	{self::query($sqlQuery);}
		return (!empty($tabResult)) ? true : false;
	}

	/*
	 * Ancien test si un champ existe
	 */
	public static function fieldExistOld($versionUpdate, $table, $field, $sqlQuery=null)
	{
		$tabResult=self::getCol("show columns from `".$table."` like '".$field."'");
		if(self::updateVersion($versionUpdate) && !empty($sqlQuery) && empty($tabResult))	{self::query($sqlQuery);}
		return (!empty($tabResult)) ? true : false;
	}

	/*
	 * Teste si un champ existe & le renomme au besoin
	 * ATTENTION !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
	 * au besoin, penser a mettre a jour le "fieldRenameOld()" qui le precede, pour que le champ ne soit pas recree a chaque update..
	 */
	public static function fieldRenameOld($versionUpdate, $table, $fieldOld, $sqlQuery)
	{
		$fieldOldExiste=self::fieldExistOld($versionUpdate,$table,$fieldOld);
		if(self::updateVersion($versionUpdate) && $fieldOldExiste==true){
			self::query($sqlQuery);
			return true;
		}
	}

	/*
	 * Lance la mise à jour de la Bdd!
	 */
	public static function lauchUpdate()
	{
		////	AJOUTE CHAMP "users_notifier_dernier_message" && "users_consult_dernier_message"
		self::fieldExistOld("2.8.0", "gt_forum_sujet", "users_notifier_dernier_message", "ALTER TABLE gt_forum_sujet ADD users_notifier_dernier_message TEXT DEFAULT NULL");
		self::fieldExistOld("2.8.0", "gt_forum_sujet", "users_consult_dernier_message", "ALTER TABLE gt_forum_sujet ADD users_consult_dernier_message TEXT DEFAULT NULL");
		
		////	AJOUTE TABLE "INVITATIONS"
		self::tableExistOld("2.8.0", "gt_invitation", "CREATE TABLE gt_invitation (id_invitation TINYTEXT, id_espace int, nom TINYTEXT, prenom TINYTEXT, mail TINYTEXT, pass TINYTEXT, date DATETIME)");

		////	AJOUTE CHAMPS PARAMETRAGE AGENDA SUR TABLE "gt_utilisateur"
		self::fieldExistOld("2.8.0", "gt_utilisateur", "agenda_plage_horaire", "ALTER TABLE gt_utilisateur ADD agenda_plage_horaire TINYTEXT");

		////	AJOUTE JOINTURE DANS "gt_jointure_objet" POUR LES ELEMENTS DES DOSSIERS RACINE
		$tab_elements=array("tache","lien","fichier","contact");
		$espaces=self::getCol("SELECT id_espace FROM gt_espace");
		foreach($tab_elements as $elem)
		{
			$elems_racine=self::getCol("SELECT id_".$elem." FROM gt_".$elem." WHERE id_dossier='1' AND id_".$elem." NOT IN (SELECT id_objet as id_".$elem." FROM gt_jointure_objet WHERE type_objet='".$elem."')");
			// On ajoute les jointures manquantes : chaque element est rattaché à chaque espace en lecture
			foreach($espaces as $id_espace){
				foreach($elems_racine as $id_elem)	{ self::updateQueryOld("2.8.0", "INSERT INTO gt_jointure_objet SET type_objet='".$elem."', id_objet='".intval($id_elem)."', id_espace='".intval($id_espace)."', tous='1', id_utilisateur=null, droit='1'"); }
			}
		}

		////	AJOUTE LE CHAMPS "date_crea" DANS LA TABLE "gt_utilisateur"
		self::fieldExistOld("2.8.0", "gt_utilisateur", "date_crea", "ALTER TABLE gt_utilisateur ADD date_crea DATETIME DEFAULT NULL AFTER commentaire");

		////	ON DEPLACE LE CHAMPS "fond_ecran" VERS "gt_agora_info"  &  CHANGE LE NOM DU DOSSIER DES FONDS D'ECRAN  &  AJOUTE LE DOSSIER STOCK_FICHIERS/TMP
		self::fieldExistOld("2.8.0", "gt_agora_info", "fond_ecran", "ALTER TABLE gt_agora_info ADD fond_ecran TEXT AFTER langue");
		if(self::updateVersion("2.8.0")){
			if(is_dir(PATH_DATAS."fond_ecran_espace/"))	{rename(PATH_DATAS."fond_ecran_espace/", PATH_DATAS."fond_ecran/");}
			if(!is_dir(PATH_DATAS."tmp/"))				{mkdir(PATH_DATAS."tmp/");}
		}

		////	AJOUTER CHAMPS "password" DANS LA TABLE "gt_espace"
		self::fieldExistOld("2.8.0", "gt_espace", "password", "ALTER TABLE gt_espace ADD password TINYTEXT DEFAULT NULL AFTER description");

		////	AJOUTER CHAMPS "id_utilisateur" DANS LA TABLE "gt_invitation"
		self::fieldExistOld("2.8.0", "gt_invitation", "id_utilisateur", "ALTER TABLE gt_invitation ADD id_utilisateur INT UNSIGNED AFTER id_invitation");

		////	ON CHANGE LE NOM DU CHAMP "afficher_tdb" en "raccourci"
		$tab_tdb_raccourci=array("gt_tache", "gt_tache_dossier", "gt_lien", "gt_lien_dossier", "gt_contact", "gt_contact_dossier", "gt_fichier", "gt_fichier_dossier", "gt_forum_sujet");
		foreach($tab_tdb_raccourci as $tab_tmp){
			//Change "afficher_tdb" en "raccourci"?
			if(self::fieldExistOld("2.8.0",$tab_tmp,"afficher_tdb") && self::fieldExistOld("2.8.0",$tab_tmp,"raccourci")==false)	{self::fieldRenameOld("2.8.0", $tab_tmp, "afficher_tdb", "ALTER TABLE ".$tab_tmp." CHANGE `afficher_tdb` `raccourci` TINYINT DEFAULT NULL");}
			//"raccourci" toujours inexistant?
			if(self::fieldExistOld("2.8.0",$tab_tmp,"raccourci")==false)	{self::fieldExistOld("2.8.0", $tab_tmp, "raccourci", "ALTER TABLE ".$tab_tmp." ADD raccourci TINYINT");}
		}

		////	ON MODIFIE LA "PRIORITE" DES TACHES
		self::fieldRenameOld("2.8.0", "gt_tache", "important", "ALTER TABLE gt_tache CHANGE `important` `priorite` TINYTEXT DEFAULT NULL");
		self::updateQueryOld("2.8.0", "UPDATE gt_tache SET priorite='haute' WHERE priorite='1'");
		self::updateQueryOld("2.8.0", "UPDATE gt_tache SET priorite=null WHERE priorite='0'");

		////	AJOUTE LA TABLE DES PREFERENCES UTILISATEUR  &  SUPPRIME LE CHAMP "module_actualite_tri" de "gt_agora_info"
		self::tableExistOld("2.8.0", "gt_utilisateur_preferences", "CREATE TABLE gt_utilisateur_preferences (id_utilisateur INT UNSIGNED, cle TINYTEXT, valeur TINYTEXT)");
		if(self::fieldExistOld("2.8.0", "gt_agora_info","module_actualite_tri"))	{self::updateQueryOld("2.8.0", "ALTER TABLE gt_agora_info DROP module_actualite_tri");}

		////	AJOUTE LES CHAMPS SUR LA PERIODICITE DES EVENEMENTS
		self::fieldExistOld("2.8.0", "gt_agenda_evenement", "period_jour_semaine", "ALTER TABLE gt_agenda_evenement ADD period_jour_semaine TINYTEXT");
		self::fieldExistOld("2.8.0", "gt_agenda_evenement", "period_jour_mois", "ALTER TABLE gt_agenda_evenement ADD period_jour_mois TINYTEXT");
		self::fieldExistOld("2.8.0", "gt_agenda_evenement", "period_mois", "ALTER TABLE gt_agenda_evenement ADD period_mois TINYTEXT");
		self::fieldExistOld("2.8.0", "gt_agenda_evenement", "period_annee", "ALTER TABLE gt_agenda_evenement ADD period_annee TINYINT");
		self::fieldExistOld("2.8.0", "gt_agenda_evenement", "period_date_fin", "ALTER TABLE gt_agenda_evenement ADD period_date_fin DATE DEFAULT NULL");

		////	SUPPRIME LE CHAMP SUR L'AFFICHAGE PAR DEFAUT DE L'AGENDA
		if(self::fieldExistOld("2.8.0", "gt_utilisateur","agenda_affichage"))	{self::updateQueryOld("2.8.0", "ALTER TABLE gt_utilisateur DROP agenda_affichage");}

		////	AJOUTE LE CHAMP DES OPTIONS DE MODULE POUR CHAQUE ESPACE
		self::fieldExistOld("2.8.0", "gt_jointure_espace_module", "options", "ALTER TABLE gt_jointure_espace_module ADD options TEXT");

		////	SI LE FOND D'ECRAN PAR DEFAUT EST NON DEFINI
		if(self::getVal("SELECT fond_ecran FROM gt_agora_info")=="")	{self::updateQueryOld("2.8.0", "UPDATE gt_agora_info SET fond_ecran='default@@1.jpg'");}

		////	AJOUT DU CHAMP D'EXCEPTION DE PERIODICITE
		self::fieldExistOld("2.8.0", "gt_agenda_evenement", "period_date_exception", "ALTER TABLE gt_agenda_evenement ADD period_date_exception TEXT AFTER period_date_fin");

		////	AJOUT DU CHAMP D'ACTIVATION DE L'AGENDA
		self::fieldExistOld("2.8.0", "gt_utilisateur", "agenda_desactive", "ALTER TABLE gt_utilisateur ADD agenda_desactive TINYINT");

		////	AJOUTE CHAMPS "date_debut" DANS LA TABLE "gt_tache"  &  CREATION TABLE "gt_tache_responsable"
		self::fieldExistOld("2.8.0", "gt_tache", "date_debut", "ALTER TABLE gt_tache ADD date_debut DATETIME DEFAULT NULL AFTER avancement");
		self::tableExistOld("2.8.0", "gt_tache_responsable", "CREATE TABLE gt_tache_responsable (id_tache INT UNSIGNED, id_utilisateur INT UNSIGNED)");

		////	AJOUTE TABLE ET DOSSIER DES FICHIERS ATTACHES AUX OBJETS
		$oldPathObjectAttachement=PATH_DATAS."fichiers_objet/";
		if(self::updateVersion("2.8.0") && !is_dir($oldPathObjectAttachement) && !is_dir(PATH_OBJECT_ATTACHMENT))	{mkdir($oldPathObjectAttachement);}
		self::tableExistOld("2.8.0", "gt_jointure_objet_fichier", "CREATE TABLE gt_jointure_objet_fichier (id_fichier INT UNSIGNED AUTO_INCREMENT, nom_fichier TEXT, type_objet TEXT, id_objet INT UNSIGNED, PRIMARY KEY (id_fichier))");

		////	CORRECTIF TABLE TACHE ABSENTES (erreur d'install)
		self::tableExistOld("2.8.0", "gt_tache", "CREATE TABLE gt_tache (id_tache INT UNSIGNED AUTO_INCREMENT, id_dossier INT UNSIGNED, id_utilisateur INT UNSIGNED, invite TEXT, titre TEXT, description TEXT, priorite TINYTEXT, avancement TINYINT, date_debut DATETIME, date_fin DATETIME, date DATETIME, raccourci TINYINT, PRIMARY KEY (id_tache))");

		////	CREATION DU CHAMP "skin" TABLE PARAMETRAGE
		self::fieldExistOld("2.8.0", "gt_agora_info", "skin", "ALTER TABLE gt_agora_info ADD skin TINYTEXT");

		////	CREATION DES FONDS D'ECRAN DE L'ESPACE
		self::fieldExistOld("2.8.0", "gt_espace", "fond_ecran", "ALTER TABLE gt_espace ADD fond_ecran TEXT");

		////	SUPPRIME LES FICHIERS JOINTS "FANTOMES" DES EVENEMENTS
		//$fichiers_fantome=self::getTab("SELECT * FROM gt_jointure_objet_fichier WHERE type_objet='evenement' AND id_objet='0'");
		//foreach($fichiers_fantome as $fichier_tmp)	{ suppr_fichier_joint($fichier_tmp["id_fichier"], $fichier_tmp["nom_fichier"]); }

		////	CREATION DU CHAMP "A LA UNE"
		self::fieldExistOld("2.8.0", "gt_actualite", "une", "ALTER TABLE gt_actualite ADD une TINYINT");

		////	CREATION DU CHAMP "edition_popup" & "footer_html" dans la table "gt_agora_info"
		self::fieldExistOld("2.8.0", "gt_agora_info", "edition_popup", "ALTER TABLE gt_agora_info ADD edition_popup TINYINT");
		self::fieldExistOld("2.8.0", "gt_agora_info", "footer_html", "ALTER TABLE gt_agora_info ADD footer_html TEXT");

		////	AJOUT DU CHAMP "COMPETENCES" ET "HOBBIES" DANS LA TABLE "gt_utilisateur" & "gt_contact"
		self::fieldExistOld("2.8.0", "gt_utilisateur", "competences", "ALTER TABLE gt_utilisateur ADD competences TEXT AFTER siteweb");
		self::fieldExistOld("2.8.0", "gt_utilisateur", "hobbies", "ALTER TABLE gt_utilisateur ADD hobbies TEXT AFTER competences");
		self::fieldExistOld("2.8.0", "gt_contact", "competences", "ALTER TABLE gt_contact ADD competences TEXT AFTER siteweb");
		self::fieldExistOld("2.8.0", "gt_contact", "hobbies", "ALTER TABLE gt_contact ADD hobbies TEXT AFTER competences");

		////	ON CRYPTE LES MOTS DE PASSE EN CLAIR AVEC sha1 (sur 40 caracteres)
		if(function_exists("sha1"))	{self::updateQueryOld("2.8.0", "UPDATE gt_utilisateur SET pass=sha1(pass) WHERE CHAR_LENGTH(pass)!=40");}

		////	CREATION DE LA TABLE DES THEMES DU FORUM
		self::tableExistOld("2.8.0", "gt_forum_theme", "CREATE TABLE gt_forum_theme (id_theme INT UNSIGNED AUTO_INCREMENT, id_utilisateur INT UNSIGNED, id_espaces TEXT, titre TINYTEXT, description TEXT DEFAULT NULL, couleur TEXT, PRIMARY KEY (id_theme))");
		self::fieldExistOld("2.8.0", "gt_forum_sujet", "id_theme", "ALTER TABLE gt_forum_sujet ADD id_theme INT UNSIGNED AFTER date");

		////	ARCHIVAGE DES ACTUALITES
		self::fieldExistOld("2.8.0", "gt_actualite", "offline", "ALTER TABLE gt_actualite ADD offline TINYINT");
		self::fieldExistOld("2.8.0", "gt_actualite", "date_offline", "ALTER TABLE gt_actualite ADD date_offline DATETIME DEFAULT NULL");

		////	CHAMP POUR LA DATE EFFECTIVE DE LA MISE A JOUR
		self::fieldExistOld("2.8.0", "gt_agora_info", "mise_a_jour_effective", "ALTER TABLE gt_agora_info ADD mise_a_jour_effective INT UNSIGNED");

		////	CHAMP POUR LE LOGO EN BAS A DROITE
		self::fieldExistOld("2.8.0", "gt_agora_info", "logo", "ALTER TABLE gt_agora_info ADD logo TEXT AFTER fond_ecran");

		////	CHAMP POUR LA DATE D'AJOUT DU DERNIER SUJET + MISE A JOUR DES INFOS
		self::fieldExistOld("2.8.0", "gt_forum_sujet", "date_dernier_message", "ALTER TABLE gt_forum_sujet ADD date_dernier_message DATETIME DEFAULT NULL");
		self::fieldExistOld("2.8.0", "gt_forum_sujet", "auteur_dernier_message", "ALTER TABLE gt_forum_sujet ADD auteur_dernier_message TINYTEXT DEFAULT NULL");
		self::fieldExistOld("2.8.0", "gt_forum_sujet", "users_consult_dernier_message", "ALTER TABLE gt_forum_sujet ADD users_consult_dernier_message TEXT DEFAULT NULL");

		////	CREATION DU CHAMP "precedente_connexion"
		self::fieldExistOld("2.8.0", "gt_utilisateur", "precedente_connexion", "ALTER TABLE gt_utilisateur ADD precedente_connexion INT UNSIGNED AFTER derniere_connexion");

		////	CREATION DU CHAMP "charge_jour_homme", "budget_disponible", "budget_engage", "devise" sur la table "gt_tache"
		self::fieldExistOld("2.8.0", "gt_tache", "charge_jour_homme", "ALTER TABLE gt_tache ADD charge_jour_homme FLOAT AFTER avancement");
		self::fieldExistOld("2.8.0", "gt_tache", "budget_disponible", "ALTER TABLE gt_tache ADD budget_disponible INT UNSIGNED AFTER charge_jour_homme");
		self::fieldExistOld("2.8.0", "gt_tache", "budget_engage", "ALTER TABLE gt_tache ADD budget_engage INT UNSIGNED AFTER budget_disponible");
		self::fieldExistOld("2.8.0", "gt_tache", "devise", "ALTER TABLE gt_tache ADD devise TINYTEXT AFTER budget_engage");
		self::updateQueryOld("2.8.0", "UPDATE gt_tache SET date_fin=REPLACE(date_fin,'00:00:00','23:59:59')");

		////	NETTOYAGE DE PRIMPTEMPS
		self::updateQueryOld("2.8.0", "DELETE FROM gt_jointure_objet WHERE id_objet='0'");
		self::updateQueryOld("2.8.0", "ALTER TABLE gt_tache CHANGE date_debut date_debut DATE DEFAULT NULL, CHANGE date_fin date_fin DATE DEFAULT NULL");
		self::updateQueryOld("2.8.0", "UPDATE gt_agenda_evenement SET period_date_fin=null WHERE period_date_fin<'2000-01-01'");
		self::updateQueryOld("2.8.0", "ALTER TABLE gt_agenda_evenement CHANGE period_date_fin period_date_fin DATE DEFAULT NULL");

		////	CREATION DU CHAMP  "editeur_text_mode" + "logo_url" + "messenger_desactive" + "libelle_module" + "tri_personnes"  SUR LA TABLE DE PARAMETRAGE
		self::fieldExistOld("2.8.0", "gt_agora_info", "editeur_text_mode", "ALTER TABLE gt_agora_info ADD editeur_text_mode TINYTEXT AFTER edition_popup");
		$fieldExist=self::fieldExistOld("2.8.0", "gt_agora_info", "logo_url", "ALTER TABLE gt_agora_info ADD logo_url TINYTEXT AFTER logo");
		if($fieldExist==false)	{self::updateQueryOld("2.8.0", "UPDATE gt_agora_info SET logo_url=".self::format(OMNISPACE_URL_PUBLIC));}
		self::fieldExistOld("2.8.0", "gt_agora_info", "messenger_desactive", "ALTER TABLE gt_agora_info ADD messenger_desactive TINYINT");
		self::fieldExistOld("2.8.0", "gt_agora_info", "libelle_module", "ALTER TABLE gt_agora_info ADD libelle_module TINYTEXT");
		$fieldExist=self::fieldExistOld("2.8.0", "gt_agora_info", "tri_personnes", "ALTER TABLE gt_agora_info ADD tri_personnes ENUM('nom','prenom') NOT NULL");
		if($fieldExist==false)	{self::updateQueryOld("2.8.0", "UPDATE gt_agora_info SET tri_personnes='prenom'");}

		////	PASSAGE DE LA BASE DE DONNEES EN UTF8
		if(self::getVal("SELECT CHARSET(nom) from gt_agora_info")=="latin1")
		{
			// Modif de la base de données
			self::query("ALTER DATABASE `".db_name."` CHARACTER SET UTF8");
			// Modif de chaque table & de chaque colonne
			foreach(self::getCol("SHOW TABLES FROM `".db_name."` ") as $nom_table) {
				self::query("ALTER TABLE ".$nom_table." CHARACTER SET UTF8");
				self::query("ALTER TABLE ".$nom_table." CONVERT TO CHARACTER SET UTF8");
			}
		}

		////	SUPPRIME LES PREFERENCES ERRONNEES
		self::updateQueryOld("2.8.0", "DELETE FROM gt_utilisateur_preferences WHERE  cle like '%.php%'");

		////	CREATION DU CHAMP "evt_affichage_couleur"
		self::fieldExistOld("2.8.0", "gt_agenda", "evt_affichage_couleur", "ALTER TABLE gt_agenda ADD evt_affichage_couleur ENUM('background','border') NOT NULL");

		////	MODIFIE LE CHAMPS "priorité" de "gt_tache"
		self::updateQueryOld("2.8.0", "UPDATE gt_tache SET priorite='1' WHERE priorite='basse'");
		self::updateQueryOld("2.8.0", "UPDATE gt_tache SET priorite='2' WHERE priorite='moyenne'");
		self::updateQueryOld("2.8.0", "UPDATE gt_tache SET priorite='3' WHERE priorite='haute'");
		self::updateQueryOld("2.8.0", "UPDATE gt_tache SET priorite='4' WHERE priorite='critique'");

		////	MODIF DES CHAMPS "date_debut" et "date_fin" de type "DATETIME"
		if(self::fieldExistOld("2.8.0","gt_tache","heure_debut"))	{self::updateQueryOld("2.8.0", "ALTER TABLE gt_tache DROP heure_debut");}
		if(self::fieldExistOld("2.8.0","gt_tache","heure_fin"))		{self::updateQueryOld("2.8.0", "ALTER TABLE gt_tache DROP heure_fin");}
		self::updateQueryOld("2.8.0", "ALTER TABLE gt_tache CHANGE date_debut date_debut DATETIME DEFAULT NULL");
		self::updateQueryOld("2.8.0", "ALTER TABLE gt_tache CHANGE date_fin date_fin DATETIME DEFAULT NULL");

		////	MODIF DES CHAMPS DE PERIODICITE DE L'AGENDA
		// Créé les nouveaux champs
		self::fieldExistOld("2.8.0", "gt_agenda_evenement", "periodicite_type", "ALTER TABLE gt_agenda_evenement ADD periodicite_type TINYTEXT AFTER visibilite_contenu");
		self::fieldExistOld("2.8.0", "gt_agenda_evenement", "periodicite_valeurs", "ALTER TABLE gt_agenda_evenement ADD periodicite_valeurs TINYTEXT AFTER periodicite_type");
		// Transfert des données
		self::updateQueryOld("2.8.0", "UPDATE gt_agenda_evenement SET periodicite_type='jour_semaine', periodicite_valeurs=period_jour_semaine  WHERE period_jour_semaine is not null and period_jour_semaine!=0");
		self::updateQueryOld("2.8.0", "UPDATE gt_agenda_evenement SET periodicite_type='jour_mois', periodicite_valeurs=period_jour_mois  WHERE period_jour_mois is not null and period_jour_mois!=0");
		self::updateQueryOld("2.8.0", "UPDATE gt_agenda_evenement SET periodicite_type='mois', periodicite_valeurs=period_mois  WHERE period_mois is not null and period_mois!=0");
		self::updateQueryOld("2.8.0", "UPDATE gt_agenda_evenement SET periodicite_type='annee', periodicite_valeurs=period_annee  WHERE period_annee is not null and period_annee!=0");
		// Supprime les anciens champs
		if(self::fieldExistOld("2.8.0","gt_agenda_evenement","period_jour_semaine"))	{self::updateQueryOld("2.8.0", "ALTER TABLE gt_agenda_evenement DROP period_jour_semaine, DROP period_jour_mois, DROP period_mois, DROP period_annee");}
		// Nettoyage divers
		self::updateQueryOld("2.8.0", "UPDATE gt_agenda_evenement SET periodicite_valeurs=null WHERE periodicite_valeurs='' OR periodicite_valeurs='0'");
		self::updateQueryOld("2.8.0", "UPDATE gt_agenda_evenement SET periodicite_type=null WHERE periodicite_valeurs is null");
		self::updateQueryOld("2.8.0", "UPDATE gt_agenda_evenement SET period_date_exception=null WHERE period_date_exception=''");
		self::updateQueryOld("2.8.0", "UPDATE gt_utilisateur SET agenda_desactive=null WHERE agenda_desactive!=1");
		self::updateQueryOld("2.8.0", "DELETE FROM gt_utilisateur_livecounter");

		////	CREATION DE LA TABLE DE GROUPE D'UTILISATEUR POUR CHAQUE ESPACE
		self::tableExistOld("2.8.0", "gt_espace_groupe", "CREATE TABLE gt_espace_groupe (id_groupe INT UNSIGNED AUTO_INCREMENT, id_utilisateur INT UNSIGNED, id_espace INT UNSIGNED, titre TINYTEXT, id_utilisateurs TEXT, PRIMARY KEY (id_groupe))");

		////	UNIFICATION DES CHAMPS DE TYPE "ARRAY" (remplace "id" ou "#" ou autre par "@@")
		self::updateQueryOld("2.8.0", "UPDATE gt_forum_theme SET id_espaces=null WHERE id_espaces=''");
		self::updateQueryOld("2.8.0", "UPDATE gt_forum_theme SET id_espaces=replace(replace(replace(id_espaces,'idid','id'), 'id','|'), '|','@@')");
		self::updateQueryOld("2.8.0", "UPDATE gt_utilisateur_messenger SET id_utilisateur_destinataires=replace(replace(replace(id_utilisateur_destinataires, 'idid','id'), 'id','|'), '|','@@')");
		self::updateQueryOld("2.8.0", "UPDATE gt_utilisateur_preferences SET valeur=replace(replace(replace(valeur, '##','|'), '@@','|'), '|','@@')");
		self::updateQueryOld("2.8.0", "UPDATE gt_agora_info SET fond_ecran=replace(replace(fond_ecran, '@@','|'), '|','@@')");		//pour versions test
		self::updateQueryOld("2.8.0", "UPDATE gt_jointure_espace_module SET options=replace(replace(options, '@@','|'), '|','@@')");	//idem
		self::updateQueryOld("2.8.0", "UPDATE gt_espace_groupe SET id_utilisateurs=REPLACE(id_utilisateurs,'|','@@')");				//idem

		////	v2.11.0 : AJOUT DU CHAMP "TIMEZONE" DANS L'AGORA  (rattrape un bug avec "version_compare()")
		self::fieldExistOld(null, "gt_agora_info", "timezone", "ALTER TABLE gt_agora_info ADD timezone TINYTEXT DEFAULT NULL AFTER langue");

		////	v2.11.1 : Update DES FONDS D'ECRAN :  default@@default5.jpg  =>  default@@5.jpg
		self::updateQueryOld("2.11.1", "UPDATE gt_agora_info SET fond_ecran=replace(replace(fond_ecran,'default.jpg','1.jpg') ,'default@@default','default@@')");
		self::updateQueryOld("2.11.1", "UPDATE gt_espace SET fond_ecran=replace(replace(fond_ecran,'default.jpg','1.jpg') ,'default@@default','default@@')");

		////	v2.11.2 : DEPLACEMENT DES PLAGES HORAIRES DU PROFIL D'UTILISATEUR VERS L'EDITION DE L'AGENDA
		self::fieldExistOld("2.11.2", "gt_agenda", "plage_horaire", "ALTER TABLE gt_agenda ADD plage_horaire TINYTEXT DEFAULT NULL");
		if(self::fieldExistOld("2.11.2","gt_utilisateur","agenda_plage_horaire"))
		{
			foreach(self::getTab("SELECT id_utilisateur, agenda_plage_horaire FROM gt_utilisateur") as $user_tmp){
				if($user_tmp["agenda_plage_horaire"]!="")	{self::updateQueryOld("2.11.2", "UPDATE gt_agenda SET plage_horaire='".$user_tmp["agenda_plage_horaire"]."' WHERE id_utilisateur='".$user_tmp["id_utilisateur"]."' AND type='utilisateur'");}
			}
		}
		if(self::fieldExistOld("2.11.2","gt_utilisateur","agenda_plage_horaire"))	{self::updateQueryOld("2.11.2", "ALTER TABLE gt_utilisateur DROP agenda_plage_horaire");}
		////	v2.11.2 : MODIFIE LES PREFERENCES SUR L'AFFICHAGE DES DOSSIERS + AJOUTE LA DESCRIPTION DES THEMES DES FORUMS
		self::updateQueryOld("2.11.2", "UPDATE gt_utilisateur_preferences SET cle=REPLACE(cle,'type_affichage_dossier_','type_affichage_')");
		self::fieldExistOld(null, "gt_forum_theme", "description", "ALTER TABLE gt_forum_theme ADD description TEXT DEFAULT NULL AFTER titre");
		////	v2.11.2 : ON ETEND LES GROUPES D'UTILISATEUR A PLUSIEURS ESPACES
		if(self::tableExistOld("2.11.2","gt_espace_groupe") && self::tableExistOld("2.11.2","gt_utilisateur_groupe")!=true)  {self::updateQueryOld("2.11.2", "RENAME TABLE gt_espace_groupe TO gt_utilisateur_groupe");}
		$fieldExist=self::fieldExistOld("2.11.2", "gt_utilisateur_groupe", "id_espaces", "ALTER TABLE gt_utilisateur_groupe ADD id_espaces TEXT DEFAULT NULL AFTER id_utilisateurs");
		if($fieldExist!=true){
			self::updateQueryOld("2.11.2", "UPDATE gt_utilisateur_groupe SET id_espaces=CONCAT('@@',id_espace,'@@') WHERE id_espace is not null");
			self::updateQueryOld("2.11.2", "ALTER TABLE gt_utilisateur_groupe DROP id_espace");
		}
		if(self::tableExistOld("2.11.2","gt_espace_groupe") && self::tableExistOld("2.11.2","gt_utilisateur_groupe"))	{self::updateQueryOld("2.11.2", "DROP TABLE gt_espace_groupe");}
		////	v2.11.2 : ON CHANGE LE TYPE DU CHAMP VIGNETTE & ON AJOUTE LE NOM DU FICHIER
		self::updateQueryOld("2.11.2", "ALTER TABLE gt_fichier CHANGE vignette vignette TINYTEXT DEFAULT NULL");
		self::updateQueryOld("2.11.2", "UPDATE gt_fichier SET vignette=CONCAT(id_fichier,extension) WHERE vignette=1");
		self::updateQueryOld("2.11.2", "UPDATE gt_fichier SET vignette=null WHERE vignette=0");

		////	v2.12.0 : CONVERSION DES LIMITES D'ESPACE DISQUE (1 Mo=1048576 octets)
		if(is_int(limite_espace_disque/1024000))	{File::updateConfigFile(array("limite_espace_disque"=>(int)((limite_espace_disque/1024000)*1048576)));}
		////	v2.12.0 : AJOUT D'UN SALT DANS LES PASSWORDS  +  NOUVELLE GESTION DES DROITS D'ACCES (AJOUT DE  "TARGET")  +  ETC
		$fieldExist=self::fieldExistOld("2.12.0", "gt_jointure_objet", "target", "ALTER TABLE gt_jointure_objet ADD target TINYTEXT NOT NULL AFTER id_espace");
		if($fieldExist!=true)
		{
			////	ajout d'un "grain de sel" dans les mots de passe deja cryptes en sha1..
			$AGORA_SALT=MdlUser::getSalt();
			foreach(self::getTab("select * from gt_utilisateur") as $user_tmp){
				//Pas de  "sha1(AGORA_SALT.sha1($user_tmp["pass"]))"  car le sha1 à dejà ete appliqué précédement en v2.8
				self::updateQueryOld("2.12.0", "UPDATE gt_utilisateur SET pass='".sha1($AGORA_SALT.$user_tmp["pass"])."' WHERE id_utilisateur='".$user_tmp["id_utilisateur"]."'");
			}
			////	champs "droit" : "TINYINT" -> "float"
			self::updateQueryOld("2.12.0", "ALTER TABLE gt_jointure_objet CHANGE droit droit FLOAT(3) UNSIGNED DEFAULT NULL");
			////	Droit d'accès aux sujets : 2 -> 1.5 par défaut
			self::updateQueryOld("2.12.0", "UPDATE gt_jointure_objet SET droit='1.5' WHERE type_objet='sujet' AND droit='2'");
			////	Ajoute le droit d'accès spécifique aux invités (objets affectés à tous les users d'un espace public)
			foreach(self::getTab("SELECT * FROM gt_jointure_objet WHERE tous='1' AND id_espace IN (select id_espace from gt_jointure_espace_utilisateur where invites='1')") as $objet_tmp){
				// ajoute le droit écriture limité (1.5) pour les invités sur les dossiers en écriture, sinon lecture (1)
				$droit_tmp=($objet_tmp["droit"]=="2" && stristr($objet_tmp["type_objet"],"dossier"))  ?  "1.5"  :  "1";
				self::updateQueryOld("2.12.0", "INSERT INTO gt_jointure_objet SET type_objet='".$objet_tmp["type_objet"]."', id_objet='".$objet_tmp["id_objet"]."', id_espace='".$objet_tmp["id_espace"]."', target='invites', droit='".$droit_tmp."'");
			}
			////	Modifie chaque droit d'accès pour "tous" et "id_utilisateur"
			foreach(self::getTab("SELECT * FROM gt_jointure_objet WHERE tous > 0 OR id_utilisateur > 0") as $objet_tmp){
				if($objet_tmp["tous"]>0)	{ $sql_select="tous='1'";											$sql_new_value="tous"; }
				else						{ $sql_select="id_utilisateur='".$objet_tmp["id_utilisateur"]."'";	$sql_new_value="U".$objet_tmp["id_utilisateur"]; }
				self::updateQueryOld("2.12.0", "UPDATE  gt_jointure_objet  SET  target='".$sql_new_value."'  WHERE type_objet='".$objet_tmp["type_objet"]."' AND id_objet='".$objet_tmp["id_objet"]."' AND id_espace='".$objet_tmp["id_espace"]."' AND ".$sql_select);
			}
			////	supprime les champs "tous" et "id_utilisateur"
			if(self::fieldExistOld("2.12.0","gt_jointure_objet","tous"))			{self::updateQueryOld("2.12.0", "ALTER TABLE gt_jointure_objet DROP tous");}
			if(self::fieldExistOld("2.12.0","gt_jointure_objet","id_utilisateur"))	{self::updateQueryOld("2.12.0", "ALTER TABLE gt_jointure_objet DROP id_utilisateur");}
			////	passage des champs text en tinytext
			self::updateQueryOld("2.12.0", "ALTER TABLE gt_tache_dossier CHANGE invite invite TINYTEXT DEFAULT NULL");
			self::updateQueryOld("2.12.0", "ALTER TABLE gt_tache CHANGE invite invite TINYTEXT DEFAULT NULL");
			self::updateQueryOld("2.12.0", "ALTER TABLE gt_lien_dossier CHANGE invite invite TINYTEXT DEFAULT NULL");
			self::updateQueryOld("2.12.0", "ALTER TABLE gt_lien CHANGE invite invite TINYTEXT DEFAULT NULL");
			self::updateQueryOld("2.12.0", "ALTER TABLE gt_contact_dossier CHANGE invite invite TINYTEXT DEFAULT NULL");
			self::updateQueryOld("2.12.0", "ALTER TABLE gt_contact CHANGE invite invite TINYTEXT DEFAULT NULL");
			self::updateQueryOld("2.12.0", "ALTER TABLE gt_fichier_dossier CHANGE invite invite TINYTEXT DEFAULT NULL");
			self::updateQueryOld("2.12.0", "ALTER TABLE gt_fichier CHANGE invite invite TINYTEXT DEFAULT NULL");
			self::updateQueryOld("2.12.0", "ALTER TABLE gt_fichier_version CHANGE invite invite TINYTEXT DEFAULT NULL");
			self::updateQueryOld("2.12.0", "ALTER TABLE gt_forum_sujet CHANGE invite invite TINYTEXT DEFAULT NULL");
			self::updateQueryOld("2.12.0", "ALTER TABLE gt_forum_message CHANGE invite invite TINYTEXT DEFAULT NULL");
			self::updateQueryOld("2.12.0", "ALTER TABLE gt_agenda_evenement CHANGE invite invite TINYTEXT DEFAULT NULL");
			self::updateQueryOld("2.12.0", "ALTER TABLE gt_jointure_objet_fichier CHANGE type_objet type_objet TINYTEXT DEFAULT NULL");
			self::updateQueryOld("2.12.0", "ALTER TABLE gt_forum_theme CHANGE couleur couleur TINYTEXT DEFAULT NULL");
			////	optimisation des dossier parent
			self::updateQueryOld("2.12.0", "UPDATE gt_contact_dossier SET id_dossier_parent='0' WHERE id_dossier='1'");
			self::updateQueryOld("2.12.0", "UPDATE gt_tache_dossier SET id_dossier_parent='0' WHERE id_dossier='1'");
			self::updateQueryOld("2.12.0", "UPDATE gt_fichier_dossier SET id_dossier_parent='0' WHERE id_dossier='1'");
			self::updateQueryOld("2.12.0", "UPDATE gt_lien_dossier SET id_dossier_parent='0' WHERE id_dossier='1'");
		}

		////	v2.12.1 : MISE EN LIGNE DES ACTUALITES
		if(self::fieldExistOld(null,"gt_actualite","archive") && self::fieldExistOld(null,"gt_actualite","offline")==false)				{self::fieldRenameOld("2.12.1", "gt_actualite", "archive", "ALTER TABLE gt_actualite CHANGE archive offline TINYINT DEFAULT NULL");}
		if(self::fieldExistOld(null,"gt_actualite","date_archivage") && self::fieldExistOld(null,"gt_actualite","date_offline")==false)	{self::fieldRenameOld("2.12.1", "gt_actualite", "date_archivage", "ALTER TABLE gt_actualite CHANGE date_archivage date_offline DATETIME DEFAULT NULL");}
		self::fieldExistOld("2.12.1", "gt_actualite", "date_online", "ALTER TABLE gt_actualite ADD date_online DATETIME DEFAULT NULL AFTER offline");

		////	v2.12.3 : IDENTIFIANT DE RENOUVELLEMENT DE MOT DE PASSE
		self::fieldExistOld("2.12.3", "gt_utilisateur", "id_newpassword", "ALTER TABLE gt_utilisateur ADD id_newpassword TINYTEXT DEFAULT NULL");
		////	v2.12.3 : CORRECTION DU BUG DE "id_message_parent"
		self::updateQueryOld("2.12.3", "ALTER TABLE gt_forum_message CHANGE id_message_parent id_message_parent INT(10) UNSIGNED DEFAULT NULL");
		self::updateQueryOld("2.12.3", "UPDATE gt_forum_message SET id_message_parent=null WHERE id_message_parent='0'");

		////	v2.12.4 : AJOUT DE LA TABLE DES LOGS
		self::tableExistOld("2.12.4", "gt_logs", "CREATE TABLE gt_logs (action VARCHAR(50), module VARCHAR(50), type_objet VARCHAR(50), id_objet INT UNSIGNED, date DATETIME, id_utilisateur INT UNSIGNED, id_espace INT UNSIGNED, ip VARCHAR(100), commentaire VARCHAR(300), KEY action (action), KEY module (module), KEY type_objet (type_objet), KEY id_objet (id_objet), KEY date (date))");
		self::fieldExistOld("2.12.4", "gt_fichier", "nb_downloads", "ALTER TABLE gt_fichier ADD nb_downloads INT UNSIGNED NOT NULL DEFAULT '0' AFTER vignette");
		self::fieldExistOld("2.12.4", "gt_jointure_objet_fichier", "nb_downloads", "ALTER TABLE gt_jointure_objet_fichier ADD nb_downloads INT UNSIGNED NOT NULL DEFAULT '0'");
		self::fieldExistOld("2.12.4", "gt_agora_info", "logs_jours_conservation", "ALTER TABLE gt_agora_info ADD logs_jours_conservation SMALLINT UNSIGNED DEFAULT '15'");

		////	v2.12.5 : Update DES LOGS
		self::updateQueryOld("2.12.5", "UPDATE gt_logs SET action='consult2' WHERE action='telechargement'");
		self::updateQueryOld("2.12.5", "ALTER TABLE gt_logs CHANGE commentaire commentaire VARCHAR(300) DEFAULT NULL");
		if(self::fieldExistOld("2.12.5","gt_logs","id_log"))	{self::updateQueryOld("2.12.5", "ALTER TABLE gt_logs DROP id_log");}

		////	v2.12.5 :  DEPLACE  "date", "id_utilisateur", "invite"  &  AJOUT DE  "date_modif", "id_utilisateur_modif"
		if(self::fieldExistOld("2.12.5","gt_actualite","date_crea")==false)
		{
			// Pour toutes les tables suivante : on déplace/créé les champs adéquats
			$tables_tmp=array("gt_actualite"=>"actualite", "gt_agenda_evenement"=>"evenement", "gt_contact"=>"contact", "gt_contact_dossier"=>"contact_dossier", "gt_fichier"=>"fichier", "gt_fichier_dossier"=>"fichier_dossier", "gt_fichier_version"=>"", "gt_forum_message"=>"message", "gt_forum_sujet"=>"sujet", "gt_historique_mails"=>"", "gt_invitation"=>"", "gt_lien"=>"lien", "gt_lien_dossier"=>"lien_dossier", "gt_tache"=>"tache", "gt_tache_dossier"=>"tache_dossier");
			foreach($tables_tmp as $nom_table => $type_objet)
			{
				// Renomme "date" en "date_crea", modif "id_utilisateur" et "invite"
				if(self::fieldExistOld(null,$nom_table,"date") && self::fieldExistOld(null,$nom_table,"date_crea")==false)	{self::updateQueryOld("2.12.5", "ALTER TABLE ".$nom_table." CHANGE date date_crea DATETIME DEFAULT NULL");}
				self::updateQueryOld("2.12.5", "ALTER TABLE ".$nom_table." CHANGE id_utilisateur id_utilisateur INT DEFAULT NULL");
				if(self::fieldExistOld("2.12.5",$nom_table,"invite"))	{self::updateQueryOld("2.12.5", "ALTER TABLE ".$nom_table." CHANGE invite invite TINYTEXT DEFAULT NULL AFTER id_utilisateur");}
				// Table d'élément
				if(preg_match("/(gt_fichier_version|gt_historique_mails|gt_invitation)/i",$nom_table)==false)
				{
					// Ajoute les champs 'date_modif' et 'id_utilisateur_modif' en fin de table
					self::fieldExistOld("2.12.5", $nom_table, "date_modif", "ALTER TABLE ".$nom_table." ADD `date_modif` DATETIME DEFAULT NULL");
					self::fieldExistOld("2.12.5", $nom_table, "id_utilisateur_modif", "ALTER TABLE ".$nom_table." ADD `id_utilisateur_modif` INT DEFAULT NULL");
					// Récupère les dates de modif des logs
					foreach(self::getTab("SELECT * FROM gt_logs WHERE action='modif' AND type_objet='".$type_objet."'") as $log_tmp){
						$field_id_objet=(stristr($type_objet,"dossier"))  ?  "id_dossier"  :  "id_".$type_objet;
						self::updateQueryOld("2.12.5", "UPDATE ".$nom_table." SET date_modif='".$log_tmp["date"]."', id_utilisateur_modif='".$log_tmp["id_utilisateur"]."' WHERE ".$field_id_objet."='".$log_tmp["id_objet"]."'");
					}
				}
			}
			// On modifie le tri des préférences
			self::updateQueryOld("2.12.5", "UPDATE gt_utilisateur_preferences SET valeur=REPLACE(valeur,'date@@','date_crea@@')");
		}

		////	V2.12.7 :  AJOUTE LE CHAMP "NOM" À LA TABLE "GT_FICHIER_VERSION" POUR GARDER LE NOM D'ORIGINE DU FICHIER
		$fieldExist=self::fieldExistOld("2.12.7", "gt_fichier_version", "nom", "ALTER TABLE gt_fichier_version ADD nom TINYTEXT DEFAULT NULL AFTER id_fichier");
		if($fieldExist==false)
		{
			foreach(self::getCol("SELECT id_fichier FROM gt_fichier_version WHERE nom is null") as $id_fichier_tmp){
				$nom_tmp=self::getVal("SELECT nom FROM gt_fichier WHERE id_fichier='".intval($id_fichier_tmp)."'");
				self::query("UPDATE gt_fichier_version SET nom=".self::format($nom_tmp)." WHERE id_fichier='".intval($id_fichier_tmp)."'");
			}
		}

		////	V2.12.8 : RATTRAPAGE DE "TIMEZONE" + "CONTACTS"
		$agoraTimezone=self::getVal("SELECT timezone FROM gt_agora_info");
		if($agoraTimezone=="-12.00" || $agoraTimezone=="1" || empty($agoraTimezone))	{self::query("UPDATE gt_agora_info SET timezone='1.00'");}
		self::fieldExistOld("2.12.8", "gt_contact", "date_modif", "ALTER TABLE gt_contact ADD `date_modif` DATETIME DEFAULT NULL");
		self::fieldExistOld("2.12.8", "gt_contact", "id_utilisateur_modif", "ALTER TABLE gt_contact ADD `id_utilisateur_modif` INT DEFAULT NULL");

		////	V2.13.0 : AJOUT DES "ESPACES" ET "DESCRIPTION" AUX CATÉGORIES D'ÉVÉNEMENT
		self::fieldExistOld("2.13.0", "gt_agenda_categorie", "id_espaces", "ALTER TABLE gt_agenda_categorie ADD `id_espaces` TEXT DEFAULT NULL AFTER id_utilisateur");
		self::fieldExistOld("2.13.0", "gt_agenda_categorie", "description", "ALTER TABLE gt_agenda_categorie ADD `description` TEXT DEFAULT NULL AFTER titre");
		////	V2.13.0 : RENOMME "DATE_CREATION" EN "DATE_CREA" DANS "GT_UTILISATEUR"
		if(self::fieldExistOld("2.13.0","gt_utilisateur","date_crea")==false)	{self::fieldRenameOld("2.13.0", "gt_utilisateur", "date_creation", "ALTER TABLE gt_utilisateur CHANGE `date_creation` `date_crea` DATETIME DEFAULT NULL");}
		elseif(self::fieldExistOld("2.13.0","gt_utilisateur","date_creation"))	{self::query("ALTER TABLE gt_utilisateur DROP date_creation");}
		////	V2.13.0 : AJOUTE LE CHAMPS "INSCRIPTION_USERS" DANS LA TABLE "GT_ESPACE"
		self::fieldExistOld("2.13.0", "gt_espace", "inscription_users", "ALTER TABLE gt_espace ADD inscription_users TINYINT UNSIGNED DEFAULT NULL AFTER password");
		////	V2.13.0 : AJOUTE LA TABLE D'INSCRIPTION DES USERS
		self::tableExistOld("2.13.0", "gt_utilisateur_inscription", "CREATE TABLE gt_utilisateur_inscription (id_inscription INT UNSIGNED AUTO_INCREMENT, id_espace INT UNSIGNED, nom TINYTEXT, prenom TINYTEXT, mail TINYTEXT, identifiant TINYTEXT, pass TINYTEXT, message TEXT DEFAULT NULL, date DATETIME, PRIMARY KEY (id_inscription))");
		////	V2.13.0 : CORRECTIF POUR SUPPRIMER LES AGENDAS DES UTILISATEURS SUPPRIMES (old bug)
		self::query("DELETE FROM gt_agenda WHERE type='utilisateur' AND id_utilisateur NOT IN (select id_utilisateur from gt_utilisateur)");

		////	V2.13.1 : AJOUT DU CHAMP "version_agora" DANS LA TABLE "gt_agora_info" & SUPPRESSION DU CHAMP "version_agora" DANS "config.inc.php"
		self::fieldExistOld("2.13.1", "gt_agora_info", "version_agora", "ALTER TABLE gt_agora_info ADD version_agora TINYTEXT");
		if(defined("version_agora")){File::updateConfigFile(null, array("version_agora"));}

		////	V2.13.2 : SUPPR LES CONSTANTES OBSOLETES DE CONFIG.INC.PHP
		if(defined("lang_defaut"))	{File::updateConfigFile(null, array("agora_installer","memory_limit","max_execution_time","taille_limit_vignette","lang_defaut"));}

		////	V2.14.0 : AJOUT DU CHAMP "ip_controle" DANS "gt_utilisateur" (supprime la table "gt_utilisateur_adresse_ip")
		$fieldExist=self::fieldExistOld("2.14.0", "gt_utilisateur", "ip_controle", "ALTER TABLE gt_utilisateur ADD ip_controle TEXT DEFAULT NULL");
		if($fieldExist==false)
		{
			// On ajoute un "ip_controle" à l'utilisateur courant  (@@192.168.1.1@@  ou @@192.168.1.1@@127.0.0.1@@)
			foreach(self::getTab("SELECT * FROM gt_utilisateur_adresse_ip") as $ip_tmp)
			{
				$ip_controle_tmp=self::getVal("SELECT ip_controle FROM gt_utilisateur WHERE id_utilisateur='".$ip_tmp["id_utilisateur"]."'");
				$ip_controle_tmp=(empty($ip_controle_tmp))  ?  "@@".$ip_tmp["adresse_ip"]."@@"  :  $ip_controle_tmp.$ip_tmp["adresse_ip"]."@@";
				self::query("UPDATE gt_utilisateur SET ip_controle='".$ip_controle_tmp."' WHERE id_utilisateur='".$ip_tmp["id_utilisateur"]."'");
			}
			// On supprime la table des adresse IP de controle
			self::query("DROP TABLE  gt_utilisateur_adresse_ip");
		}

		////	V2.15.0 : AJOUT DU CHAMP "invitations_users" DANS "gt_espace"
		$fieldExist=self::fieldExistOld("2.15.0", "gt_espace", "invitations_users", "ALTER TABLE gt_espace ADD invitations_users TINYINT DEFAULT NULL");
		if($fieldExist==false)
		{
			foreach(self::getTab("SELECT DISTINCT id_espace FROM gt_jointure_espace_utilisateur WHERE envoi_invitation='1' AND tous_utilisateurs='1'") as $espace_tmp){
				self::query("UPDATE gt_espace SET invitations_users='1' WHERE id_espace='".$espace_tmp["id_espace"]."'");
			}
			self::query("ALTER TABLE gt_jointure_espace_utilisateur DROP envoi_invitation");
		}
		////	V2.15.0 : SUPPR LA CONSTANTE "duree_livecounter_recharge" DE CONFIG.INC.PHP
		if(defined("duree_livecounter_recharge"))	{File::updateConfigFile(null, array("duree_livecounter_recharge"));}

		////	V2.15.1 : AJOUTE LE CHAMP "gt_espace"->"public" +++
		$fieldExist=self::fieldExistOld("2.15.1", "gt_espace", "public", "ALTER TABLE gt_espace ADD public TINYINT DEFAULT NULL AFTER description");
		if($fieldExist==false)
		{
			// TRANSFERT LES DONNEES DE "espace public"  +  SUPPRIME "gt_jointure_espace_utilisateur"->"invite"
			foreach(self::getTab("SELECT DISTINCT id_espace FROM gt_jointure_espace_utilisateur WHERE invites='1'") as $espace_tmp){
				self::query("UPDATE gt_espace SET public='1' WHERE id_espace='".$espace_tmp["id_espace"]."'");
			}
			self::query("DELETE FROM gt_jointure_espace_utilisateur WHERE invites='1'");
			self::query("ALTER TABLE gt_jointure_espace_utilisateur DROP invites");
			// OPTIMISATION DES CLES PRIMAIRES DE DIVERSES TABLES (CHAMPS DONT LA CLAUSE 'WHERE' EST TOUT LE TEMPS PRESENTE)
			self::updateQueryOld("2.15.1", "ALTER TABLE gt_fichier_version ADD INDEX (`id_fichier`)");
			self::updateQueryOld("2.15.1", "ALTER TABLE gt_jointure_espace_module ADD INDEX (`id_espace`)");
			self::updateQueryOld("2.15.1", "ALTER TABLE gt_jointure_espace_utilisateur ADD INDEX (`id_espace`)");
			self::updateQueryOld("2.15.1", "ALTER TABLE `gt_utilisateur_preferences` ADD INDEX (`id_utilisateur`)");
			//  OPTIMISE LA TABLE "gt_jointure_objet"
			self::updateQueryOld("2.15.1", "ALTER TABLE gt_jointure_objet CHANGE type_objet type_objet TINYTEXT DEFAULT NULL");
		}

		////	V2.16.0 : AJOUTE LES CHAMP RELATIFS A UNE CONNEXION LDAP
		$fieldExist=self::fieldExistOld("2.16.0", "gt_agora_info", "ldap_server", "ALTER TABLE gt_agora_info ADD ldap_server TINYTEXT DEFAULT NULL");
		$fieldExist=self::fieldExistOld("2.16.0", "gt_agora_info", "ldap_server_port", "ALTER TABLE gt_agora_info ADD ldap_server_port TINYTEXT DEFAULT NULL");
		$fieldExist=self::fieldExistOld("2.16.0", "gt_agora_info", "ldap_admin_login", "ALTER TABLE gt_agora_info ADD ldap_admin_login TINYTEXT DEFAULT NULL");
		$fieldExist=self::fieldExistOld("2.16.0", "gt_agora_info", "ldap_admin_pass", "ALTER TABLE gt_agora_info ADD ldap_admin_pass TINYTEXT DEFAULT NULL");
		$fieldExist=self::fieldExistOld("2.16.0", "gt_agora_info", "ldap_groupe_dn", "ALTER TABLE gt_agora_info ADD ldap_groupe_dn TINYTEXT DEFAULT NULL");
		$fieldExist=self::fieldExistOld("2.16.0", "gt_agora_info", "ldap_crea_auto_users", "ALTER TABLE gt_agora_info ADD ldap_crea_auto_users TINYINT DEFAULT NULL");
		$fieldExist=self::fieldExistOld("2.16.0", "gt_agora_info", "ldap_pass_cryptage", "ALTER TABLE gt_agora_info ADD ldap_pass_cryptage ENUM('aucun','md5','sha') NOT NULL");

		////	V2.16.3 : AJOUTE LE CHAMPS "agenda_perso_desactive" DE LA TABLE "gt_agora_info"
		self::fieldExistOld("2.16.3", "gt_agora_info", "agenda_perso_desactive", "ALTER TABLE gt_agora_info ADD agenda_perso_desactive TINYINT AFTER messenger_desactive");
	}
}