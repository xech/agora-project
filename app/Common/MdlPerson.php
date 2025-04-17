<?php
/**
* This file is part of the Agora-Project Software package
*
* @copyleft Agora-Project <https://www.agora-project.net>
* @license GNU General Public License (GPL-2.0)
*/


/*
 * MODELE DES PERSONNES : UTILISATEURS / CONTACTS
 */
class MdlPerson extends MdlObject
{
	public static $displayModes=["block","line"];
	public static $requiredFields=["name","firstName","login"];
	public static $searchFields=["name","firstName","companyOrganization","function","adress","postalCode","city","country","telephone","telmobile","mail","comment"];
	//Init le cache
	private $_profileImg=null;
	//Formats .csv  ("fieldKeys" : "nom du champ bdd agora"=>"nom du champ d'export csv")
	public static $csvFormats=array(
		//AGORA
		"csv_agora"=>array(
			"delimiter"=>";",
			"enclosure"=>'"',
			"fieldKeys"=>array(
				"civility"=>"civility",
				"name"=>"name",
				"firstName"=>"firstName",
				"companyOrganization"=>"companyOrganization",
				"function"=>"function",
				"adress"=>"adress",
				"postalCode"=>"postalCode",
				"city"=>"city",
				"country"=>"country",
				"telephone"=>"telephone",
				"telmobile"=>"telmobile",
				"mail"=>"mail",
				"comment"=>"comment",
				"login"=>"login",
				"password"=>"password"
			)
		),
		//GMAIL
		"csv_gmail"=>array(
			"delimiter"=>",",
			"enclosure"=>"",
			"fieldKeys"=>array(
				"civility"=>"Name Prefix",
				"firstName"=>"Additional Name",
				"firstName"=>"Given Name",
				"name"=>"Name",
				"name"=>"Family Name",
				"mail"=>"E-mail 1 - Value",
				"telmobile"=>"Phone 1 - Value",
				"function"=>"Organization 1 - Title",
				"companyOrganization"=>"Société",
				"adress"=>"Address 1 - Street",
				"city"=>"Address 1 - City",
				"postalCode"=>"Address 1 - Postal Code",
				"country"=>"Address 1 - Country",
				"comment"=>"Notes"
			)
		),
		//OUTLOOK
		"csv_outlook"=>array(
			"delimiter"=>",",
			"enclosure"=>'"',
			"fieldKeys"=>array(
				"firstName"=>"Prénom",
				"name"=>"Nom",
				"companyOrganization"=>"Société",
				"function"=>"Fonction",
				"adress"=>"Rue (domicile)",
				"city"=>"Ville (domicile)",
				"adress"=>"Code postal (domicile)",
				"country"=>"Pays (domicile)",
				"telephone"=>"Téléphone (domicile)",
				"telmobile"=>"Tél. mobile",
				"mail"=>"Adresse mail",
				"comment"=>"Notes"
			)
		),
		//HOTMAIL
		"csv_hotmail"=>array(
			"delimiter"=>";",
			"enclosure"=>'"',
			"fieldKeys"=>array(
				"civility"=>"Title",
				"firstName"=>"First Name",
				"Middle Name"=>"Middle Name",
				"name"=>"Last Name",
				"companyOrganization"=>"Company",
				"Department"=>"Department",
				"function"=>"Job Title",
				"adress"=>"Home Street",
				"city"=>"Home City",
				"postalCode"=>"Home Postal Code",
				"country"=>"Home Country",
				"telephone"=>"Home Phone",
				"telmobile"=>"Mobile Phone",
				"mail"=>"E-mail Address",
				"comment"=>"Notes"
			)
		),
		//THUNDERBIRD
		"csv_thunderbird"=>array(
			"delimiter"=>",",
			"enclosure"=>"",
			"fieldKeys"=>array(
					"firstName"=>"Prénom",
					"name"=>"Nom de famille",
					"mail"=>"Première adresse électronique",
					"telephone"=>"Tél. personnel",
					"telmobile"=>"Portable",
					"adress"=>"Adresse privée",
					"city"=>"Ville",
					"country"=>"Pays/État",
					"postalCode"=>"Code postal",
					"function"=>"Profession",
					"companyOrganization"=>"Société",
					"comment"=>"Notes"
			)
		)
	);

	/*******************************************************************************************
	 * SURCHARGE : CONSTRUCTEUR
	 *******************************************************************************************/
	function __construct($objIdOrValues=null)
	{
		parent::__construct($objIdOrValues);
		//Tri par défaut en fonction du prénom (cf. parametrage general) : switch "name" et "firstName"
		if(Ctrl::$agora->personsSort=="firstName")   {static::$sortFields[0]="firstName@@asc";  static::$sortFields[1]="firstName@@desc";  static::$sortFields[2]="name@@asc"; static::$sortFields[3]="name@@desc";
		}
	}

	/*******************************************************************************************
	 * SURCHARGE : AFFICHE LE LABEL DE L'UTILISATEUR / CONTACT
	 *******************************************************************************************/
	public function getLabel($labelType=null)
	{
		if(empty($this->firstName) && empty($this->name))			{return "<i>".Txt::trad("deletedUser")."</i>";}					//Renvoie "Compte utilisateur supprimé"
		elseif($labelType=="full")									{return $this->civility." ".$this->firstName." ".$this->name;}	//$labelType=="full" 		-> "Dr Boby SMITH"  (cf. profil utilisateur ou contact)
		elseif($labelType=="firstName" && !empty($this->firstName))	{return $this->firstName;}										//$labelType=="firstName"	-> "Boby"
		else														{return $this->firstName." ".$this->name;}						//Par défaut				-> "Boby SMITH"
	}

	/*******************************************************************************************
	 * POSSÈDE UNE ADRESSE ? city + (adress || postalCode)
	 *******************************************************************************************/
	public function hasAdress()
	{
		return (!empty($this->city) && (!empty($this->adress) || !empty($this->postalCode)));
	}

	/*******************************************************************************************
	 * AFFICHAGE L'EMAIL DE L'USER ?
	 *******************************************************************************************/
	public function userMailDisplay()
	{
		//Affiche l'email si :  l'objet est un contact  ||  On peut éditer l'objet  ||  l'option "userMailDisplay" est activée
		return (static::objectType=="contact" || $this->editRight() || !empty(Ctrl::$agora->userMailDisplay));
	}

	/*******************************************************************************************
	 * AFFICHE LES INFOS SUR LA PERSONNE  ($mode : block / line / profile / edit)
	 *******************************************************************************************/
	public function getFields($mode)
	{
		$labels=null;
		//Affichage en page principale ("display mode" block || line)
		if($mode=="block" || $mode=="line")
		{
			if($this->userMailDisplay())  {$labels.=$this->getField("mail",$mode);}
			$labels.=	$this->getField("companyOrganization",$mode).
						$this->getField("function",$mode).
						$this->getField("telephone",$mode).
						$this->getField("telmobile",$mode).
						$this->getField("fullAdress",$mode);
		}
		//Affichage du profil (vue / édition)
		elseif($mode=="profile" || $mode=="edit")
		{
			if($mode=="edit")  				{$labels.=$this->getField("civility",$mode).$this->getField("name",$mode).$this->getField("firstName",$mode)."<hr>";}
			if($this->userMailDisplay())	{$labels.=$this->getField("mail",$mode);}
			$labels.=	$this->getField("telmobile",$mode).
						$this->getField("telephone",$mode).
						$this->getField("adress",$mode).
						$this->getField("postalCode",$mode).
						$this->getField("city",$mode).
						$this->getField("country",$mode).
						$this->getField("function",$mode).
						$this->getField("companyOrganization",$mode).
						$this->getField("comment",$mode);
		}
		//Date de dernière connexion
		if(static::objectType=="user" && $mode!="edit" && Ctrl::$curUser->isSpaceAdmin())  {$labels.=$this->getField("lastConnection",$mode);}
		//Retourne le résultat
		return $labels;
	}

	/*******************************************************************************************
	 * AFFICHE UNE INFO SUR LA PERSONNE  ($mode : "block", "line", "profile", "edit")
	 *******************************************************************************************/
	public function getField($fieldName, $mode)
	{
		//Cast la valeur du champ
		$fieldVal=(string)$this->$fieldName;
		//Edition du champ
		if($mode=="edit"){
			if($fieldName=="comment")	{$fieldVal='<textarea name="'.$fieldName.'">'.strip_tags($fieldVal).'</textarea>';}
			else						{$fieldVal='<input type="text" name="'.$fieldName.'" value="'.strip_tags($fieldVal).'">';}
		}
		//Mail : redirige vers le module mail ou un simple "mailto"
		elseif($fieldName=="mail" && !empty($fieldVal)){
			$mailtoUrl=Ctrl::$curSpace->moduleEnabled("mail")  ?  'onclick="window.top.redir(\'?ctrl=mail&checkedMailto='.$this->$fieldName.'\')"'  :  'href="mailto:'.$this->$fieldName.'"';
			$fieldVal='<a '.$mailtoUrl.' '.Txt::tooltip("sendMail").'>'.$this->$fieldName.'</a>';
		}
		//Dernière connexion
		elseif($fieldName=="lastConnection"){
			if($mode=="profile" && empty($fieldVal))						{$fieldVal=Txt::trad("notConnected");}
			elseif(!empty($fieldVal) && date("Ymd")==date("Ymd",$fieldVal))	{$fieldVal=Txt::trad("connectedToday");}
			elseif(!empty($fieldVal))										{$fieldVal=Txt::trad("connectedThe").' '.Txt::dateLabel($fieldVal,"dateBasic");}
		}
		//Adresse complete : affiche une carte 
		elseif($fieldName=="fullAdress" && $this->hasAdress()){
			$fieldVal='<a onclick="lightboxOpen(\'?ctrl=misc&action=PersonsMap&objectsTypeId['.static::objectType.']='.$this->_id.'\')" '.Txt::tooltip("mapLocalize").'><img src="app/img/map.png"> '.$this->adress.' '.$this->postalCode.' '.$this->city.'</a>';
		}
		//Commentaire
		elseif($fieldName=="comment"){
			$fieldVal=nl2br($fieldVal);
		}
		//Retourne le champ dans son conteneur
		if(!empty($fieldVal)){
			if($mode=="block")		{return '<div class="objPersonDetail">'.$fieldVal.'</div>';}
			elseif($mode=="line")	{return '<div class="objPersonDetail">'.$fieldVal.'</div><img src="app/img/separator.png" class="objPersonDetailSeparator">';}
			else					{return '<div class="objField"><div><img src="app/img/person/'.$fieldName.'.png"> '.Txt::trad($fieldName).'</div><div>'.$fieldVal.'</div></div>';}
		}
	}

	/*******************************************************************************************
	 * IMAGE DE PROFIL : VERIF L'EXISTENCE
	 *******************************************************************************************/
	public function profileImgExist()
	{
		if($this->_profileImg===null)  {$this->_profileImg=is_file($this->pathImgThumb());}
		return $this->_profileImg;
	}

	/*******************************************************************************************
	 * IMAGE DE PROFIL : PATH
	 *******************************************************************************************/
	public function profileImgPath($defaultImg=false)
	{
		if($this->profileImgExist())	{return $this->pathImgThumb().($this->dateModif?"?time=".strtotime($this->dateModif):null);}	//Img avec un "time" pour updater si besoin le cache du browser
		elseif($defaultImg==true)		{return 'app/img/person/personDefault.png';}													//Img par défaut (si demandé)
	}

	/*******************************************************************************************
	 *  IMAGE DU PROFIL : BALISE <IMG>
	 *******************************************************************************************/
	public function profileImg($openProfile=false, $smallImg=false)
	{
		$imgPath=$this->profileImgPath(false);
		if(!empty($imgPath)){
			$personImg='<img src="'.$imgPath.'" class="personImg '.($smallImg==true?"personImgSmall":null).'">';
			if($openProfile==true)  {$personImg='<a onclick="'.$this->openVue().'" '.Txt::tooltip("displayProfil").'>'.$personImg.'</a>';}
			return $personImg;
		}
	}

	/*******************************************************************************************
	 * IMAGE DU PROFIL : MENU D'EDITION
	 *******************************************************************************************/
	public function profileImgMenu()
	{
		////	Ajouter un fichier  OU  Fichier à conserver/modifier/supprimer
		if($this->profileImgExist()!=true)	{return '<input type="file" name="profileImgFile"><input type="hidden" name="profileImgAction" value="change">';}	
		else{
			return '<select name="profileImgAction" onchange="if(this.value==\'change\') {$(\'.profileImgFile\').fadeIn();} else {$(\'.profileImgFile\').fadeOut();}">
						<option value="">'.Txt::trad("keepImg").'</option>
						<option value="change">'.Txt::trad("changeImg").'</option>
						<option value="delete">'.Txt::trad("delete").'</option>
					</select>
					<input type="file" name="profileImgFile" class="profileImgFile" style="display:none;margin-top:10px;">';
		}
	}

	/*******************************************************************************************
	 * IMAGE DU PROFIL : ENREGISTRE / SUPPRIME (cf. "profileImgMenu()")
	 *******************************************************************************************/
	public function profileImgRecord()
	{
		if(Req::isParam("profileImgAction"))
		{
			// Supprime
			if(Req::param("profileImgAction")=="delete")	{unlink($this->pathImgThumb());}
			// Ajoute / change
			if(Req::param("profileImgAction")=="change" && !empty($_FILES["profileImgFile"]) && File::isType("imageResize",$_FILES["profileImgFile"]["name"])){
				move_uploaded_file($_FILES["profileImgFile"]["tmp_name"], $this->pathImgThumb());
				File::imageResize($this->pathImgThumb(),$this->pathImgThumb(),200);
			}
		}
	}

	/*******************************************************************************************
	 * EXPORTE DES PERSONNES AU FORMAT SPÉCIFIÉ
	 *******************************************************************************************/
	public static function exportPersons($personObjList, $exportType)
	{
		//Init
		$fileContent=null;
		////	EXPORT CSV
		if(strstr($exportType,"csv"))
		{
			//Nom et champs du .csv
			$csv=static::$csvFormats[$exportType];
			$fileName=$exportType.".csv";
			//Enlève la colonne "password" pour tous les exports csv  &&  la colonne "login" pour les contacts 
			unset($csv["fieldKeys"]["password"]);
			if(static::objectType!="user")  {unset($csv["fieldKeys"]["login"]);}
			//Créé l'entête du fichier CSV (ajoute la colonne "groups" pour les users)
			foreach($csv["fieldKeys"] as $fieldAgora=>$fieldCsv)  {$fileContent.=$csv["enclosure"].$fieldCsv.$csv["enclosure"].$csv["delimiter"];}
			if(static::objectType=="user")  {$fileContent.=$csv["enclosure"]."groups".$csv["enclosure"].$csv["delimiter"];}
			$fileContent.="\n";
			//Ajoute chaque user/contact
			foreach($personObjList as $tmpPerson)
			{
				//Ajoute chaque champ du user/contact
				foreach($csv["fieldKeys"] as $fieldAgora=>$fieldCsv){
					if($csv["enclosure"]=="'")	{$tmpPerson->$fieldAgora=addslashes($tmpPerson->$fieldAgora);}//Addslashes de la valeur si besoin
					$fileContent.=(!empty($tmpPerson->$fieldAgora))  ?  $csv["enclosure"].$tmpPerson->$fieldAgora.$csv["enclosure"].$csv["delimiter"]  :  $csv["delimiter"];
				}
				//User : ajoute la liste des groupes
				if(static::objectType=="user"){
					foreach(MdlUserGroup::getGroups(null,$tmpPerson) as $tmpGroup)  {$fileContent.=$csv["enclosure"].$tmpGroup->title.$csv["enclosure"].$csv["delimiter"];}
				}
				//Retour à la ligne
				$fileContent.="\n";
			}
		}
		////	EXPORT LDIF
		elseif($exportType=="ldif")
		{
			//Init
			$fileName="contact.ldif";
			//Ajout de chaque personne
			foreach($personObjList as $tmpPerson)
			{
				$fileContent.="dn: cn=".$tmpPerson->firstName." ".$tmpPerson->name."\n";
				$fileContent.="objectclass: top\n";
				$fileContent.="objectclass: person\n";
				$fileContent.="objectclass: organizationalPerson\n";
				$fileContent.="cn: ".$tmpPerson->firstName." ".$tmpPerson->name."\n";
				$fileContent.="givenName: ".$tmpPerson->firstName."\n";
				$fileContent.="sn: ".$tmpPerson->name."\n";
				if(!empty($tmpPerson->mail))				{$fileContent.="mail: ".$tmpPerson->mail."\n";}
				if(!empty($tmpPerson->telephone))			{$fileContent.="homePhone: ".$tmpPerson->telephone."\n";}
				if(!empty($tmpPerson->telephone))			{$fileContent.="telephonenumber: ".$tmpPerson->telephone."\n";}
				if(!empty($tmpPerson->telmobile))			{$fileContent.="mobile: ".$tmpPerson->telmobile."\n";}
				if(!empty($tmpPerson->adress))				{$fileContent.="homeStreet: ".$tmpPerson->adress."\n";}
				if(!empty($tmpPerson->city))				{$fileContent.="mozillaHomeLocalityName: ".$tmpPerson->city."\n";}
				if(!empty($tmpPerson->postalCode))			{$fileContent.="mozillaHomePostalCode: ".$tmpPerson->postalCode."\n";}
				if(!empty($tmpPerson->country))				{$fileContent.="mozillaHomeCountryName: ".$tmpPerson->country."\n";}
				if(!empty($tmpPerson->companyOrganization))	{$fileContent.="company: ".$tmpPerson->companyOrganization."\n";}
				if(!empty($tmpPerson->function))			{$fileContent.="title: ".$tmpPerson->function."\n";}
				if(!empty($tmpPerson->comment))				{$fileContent.="description: ".$tmpPerson->comment."\n";}
				$fileContent.="\n";
			}
		}
		////	EXPORT VCARD (.vcf)
		elseif($exportType=="vcard")
		{
			//Init
			$fileName="contacts_agora.vcf";
			//Ajout de chaque personne au fichier Vcard
			foreach($personObjList as $tmpPerson)
			{
				$fileContent .="BEGIN:VCARD\n";
				$fileContent .="VERSION:2.1\n";//V2.1 pour une complatibilité Android
				$fileContent.="FN:".$tmpPerson->firstName." ".$tmpPerson->name."\n";
				$fileContent.="N:".$tmpPerson->name.";".$tmpPerson->firstName."\n";
				if(!empty($tmpPerson->telmobile))			{$fileContent.="TEL;CELL:".$tmpPerson->telmobile."\n";}
				if(!empty($tmpPerson->telephone))			{$fileContent.="TEL;HOME:".$tmpPerson->telephone."\n";}
				if(!empty($tmpPerson->mail))				{$fileContent.="EMAIL: ".$tmpPerson->mail."\n";}
				if(!empty($tmpPerson->adress))				{$fileContent.="ADR;TYPE=home:;;".$tmpPerson->adress.";".$tmpPerson->city.";;".$tmpPerson->postalCode.";".$tmpPerson->country."\n";}
				if(!empty($tmpPerson->companyOrganization))	{$fileContent.="ORG:".$tmpPerson->companyOrganization."\n";}
				if(!empty($tmpPerson->function))			{$fileContent.="TITLE:".$tmpPerson->function."\n";}
				if(!empty($tmpPerson->comment))				{$fileContent.="NOTE:".$tmpPerson->comment."\n";}
				$fileContent.="END:VCARD\n";
			}
		}
		/////   LANCEMENT DU TELECHARGEMENT
		File::download($fileName, null, $fileContent);
	}

	/*******************************************************************************************
	 * CONNEXION A UN SERVEUR LDAP
	 *******************************************************************************************/
	public static function ldapConnect($ldap_server=null, $ldap_server_port=null, $ldap_admin_login=null, $ldap_admin_pass=null, $displayError=true)
	{
		// Controle si la connexion LDAP est activée
		if(!function_exists("ldap_connect"))  {return false;}
		// Récupère la config du paramétrage général (sinon c'est un test de connexion du paramétrage général)
		if(empty($ldap_server))			{$ldap_server		=Ctrl::$agora->ldap_server;}
		if(empty($ldap_server_port))	{$ldap_server_port	=Ctrl::$agora->ldap_server_port;}
		if(empty($ldap_admin_login))	{$ldap_admin_login	=Ctrl::$agora->ldap_admin_login;}
		if(empty($ldap_admin_pass))		{$ldap_admin_pass	=Ctrl::$agora->ldap_admin_pass;}
		// Initialise la connexion au serveur LDAP et vérifie si l'uri donnée est plausible ($ldap_server)
		$ldapConnectServer=ldap_connect($ldap_server, $ldap_server_port);
		ldap_set_option($ldapConnectServer, LDAP_OPT_PROTOCOL_VERSION, 3);	//Utiliser LDAP Protocol V3! (v2 par défaut)
		ldap_set_option($ldapConnectServer, LDAP_OPT_REFERRALS, 0);			//Pour Active Directory
		// Lien de connexion au serveur Ldap (identification) en tant qu'admin
		$ldapConnect=ldap_bind($ldapConnectServer, $ldap_admin_login, $ldap_admin_pass);
		if($ldapConnect==false && $displayError==true)  {Ctrl::notify("AGORA_ldapConnectError");}
		// Retourne la connexion ldap si c'est ok
		return ($ldapConnect==false)  ?  false  :  $ldapConnectServer;
	}

	/***************************************************************************************************************
	 * RECUPERES DES PERSONNES DE L'ANNUAIRE LDAP  (exple de $importLdapFilter -> "(&(samaccountname=MONLOGIN)(cn=*))" )
	 ***************************************************************************************************************/
	public static function ldapSearch($importLoginPassword, $importLdapDn, $importLdapFilter)
	{
		$ldapConnect=self::ldapConnect();
		if($ldapConnect!=false)
		{
			// Champs Agora => Attributs LDAP correspondants (Le champ plus plausible en dernier & toujours en minucule!)
			$ldapFields=array(
				"civility"				=>["designation","initials"],
				"name"					=>["sn","lastname","name"],
				"firstName"				=>["knownas","givenname","firstname"],
				"mail"					=>["email","mail"],
				"telmobile"				=>["mobiletelephonenumber","mobile"],
				"telephone"				=>["hometelephonenumber","homephone","telephonenumber"],
				"adress"				=>["postaladdress","homepostaladdress","streetaddress","street"],
				"postalCode"			=>["postalcode","homepostalcode"],
				"city"					=>["localityname","city","l"],
				"companyOrganization"	=>["department","organizationalunitname","ou","organizationname","company"],
				"function"				=>["title","titleall","function"],
				"comment"				=>["description","comment"]
			);
			// Champs Agora  => On ajoute le login/password s'il s'agit d'utilisateurs
			if($importLoginPassword==true){
				$ldapFields["login"]=["uid","samaccountname"];
				$ldapFields["password"]=["userpassword","password"];
			}
			// Récupere les users LDAP
			$ldapSearch=ldap_search($ldapConnect, $importLdapDn, $importLdapFilter);
			if($ldapSearch!=false)
			{
				$searchPersons=ldap_get_entries($ldapConnect, $ldapSearch);
				if($searchPersons["count"]>0)
				{
					////	Champs Agora à utiliser
					$importedFields=[];
					foreach($searchPersons as $userAttributes){
						//Pour chaque champs de l'utilisateur importé : vérif si le champ ldap correspond à un champ Agora
						foreach($ldapFields as $agoraField=>$ldapTmpFields){
							foreach($ldapTmpFields as $ldapField){
								if(!empty($userAttributes[$ldapField][0]) && !in_array($agoraField,$importedFields))  {$importedFields[]=$agoraField;}
							}
						}
					}
					////	Attributs / valeurs de chaque contact
					$importedPersons=[];
					foreach($searchPersons as $userKey=>$userAttributes)
					{
						if(is_numeric($userKey))
						{
							$importedPerson=[];
							foreach($ldapFields as $agoraField=>$ldapTmpFields)
							{
								//Cle du tableau d'entête correspondant au champ visé (tableau d'import: numéro de colonne du champ agora || import direct : nom du champ agora)
								$fieldCpt=array_search($agoraField,$importedFields);
								$fieldKey=$fieldCpt;
								// Ajoute la valeur si l'attribut ldap correspond à un champ de l'agora (..et qu'il n'a pas déjà été ajouté avec un autre attribut)
								foreach($ldapTmpFields as $ldapField){
									if(isset($userAttributes[$ldapField][0]))  {$importedPerson[$fieldKey]=$userAttributes[$ldapField][0];}
								}
								//Champ non spécifié : "null"
								if(empty($importedPerson[$fieldKey]))  {$importedPerson[$fieldKey]="";}//pas de null
								//Re-tri les champs en fonction du numéro de colonne du champ agora
								ksort($importedPerson);
							}
							//Ajoute les Valeurs à l'user temporaire
							$importedPersons[]=$importedPerson;
						}
					}
					//Ferme la connexion et retourne le résultat
					ldap_close($ldapConnect);
					return ["headerFields"=>$importedFields, "ldapPersons"=>$importedPersons];
				}
			}
		}
	}
}