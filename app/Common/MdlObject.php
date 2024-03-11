<?php
/**
* This file is part of the Agora-Project Software package.
*
* @copyright (c) Agora-Project Limited <https://www.agora-project.net>
* @license GNU General Public License, version 2 (GPL-2.0)
*/


/** Autorise la création dynamique de propriétés récupérées en bdd, dans "__construct()" **/
#[\AllowDynamicProperties]


/*
 * Classe principale des Objects
 */
class MdlObject
{
	//Utilise les classes des menus ("trait")
	use MdlObjectMenus;

	//Propriétés de base
	const moduleName=null;
	const objectType=null;
	const dbTable=null;
	//Propriétés de dépendance
	const MdlObjectContent=null;					//Objet contenu par l'objet courant : objets enfants
	const MdlObjectContainer=null;					//Objet contenant l'objet courant : objet parent
	const isFolder=false;							//Objet de type dossier
	const isFolderContent=false;					//Contenu d'un dossier (fichier, contact, etc)
	protected static $_hasAccessRight=null;			//pas en constante car dépend du context (cf. elems d'une arbo à la racine.. ou pas)
	//Propriétés d'affichage et d'édition (d'IHM)
	const isSelectable=false;						//Sélection multiple : arborescence le plus souvent
	const hasShortcut=false;						//Création de raccourcis sur l'objet
	const hasNotifMail=false;						//Envoyer des notifs d'édition par mail
	const hasAttachedFiles=false;					//Ajouter des pièces jointes
	const hasUsersLike=false;						//Like sur l'objet
	const hasUsersComment=false;					//Ajout de commentaires sur l'objet
	const descriptionEditor=false;					//Editeur html dans la description
	public static $displayModes=[];					//Type d'affichage : ligne/block le plus souvent
	public static $requiredFields=[];				//Champs obligatoires pour valider l'édition d'un objet
	public static $searchFields=[];					//Champs de recherche
	public static $sortFields=[];					//Champs/Options de tri des résulats
	//Valeurs mises en cache
	private $_accessRight=null;
	private $_containerObj=null;
	private $_affectations=null;
	private $_attachedFiles=null;
	private $_attachedFilesMenu=null;
	private $_usersComment=null;
	private $_usersLike=null;
	protected static $_sqlTargets=null;

	/*******************************************************************************************
	 * CONSTRUCTEUR
	 *******************************************************************************************/
	function __construct($objIdOrValues=null)
	{
		////	Init l'id
		$this->_id=0;
		////	Assigne des propriétés à l'objet : Objet déjà créé / Objet à créer
		if(!empty($objIdOrValues)){
			//Récupère les propriétés en BDD ou celles passées en paramètre
			$objValues=(is_numeric($objIdOrValues))  ?  Db::getLine("select * from ".static::dbTable." where _id=".(int)$objIdOrValues)  :  $objIdOrValues;
			//Assigne chaque propriété
			if(!empty($objValues)){
				foreach($objValues as $propertieKey=>$propertieVal)  {$this->$propertieKey=$propertieVal;}
			}
		}
		////	Cast l'id en Interger  + Init l'identifiant générique (ex: "fileFolder-19")
		$this->_id=(int)$this->_id;
		$this->_typeId=static::objectType."-".$this->_id;
	}

	/*******************************************************************************************
	 * RENVOIE LA VALEUR D'UNE PROPRIÉTÉ UNIQUEMENT SI ELLE EXISTE
	 *******************************************************************************************/
	function __get($propertyName)
	{
		if(isset($this->$propertyName))  {return $this->$propertyName;}
	}

	/*******************************************************************************************
	 * VÉRIFIE SI UN OBJET EST DÉJÀ CRÉÉ ET POSSÈDE UN _ID  (L'OJECT PEUT NE PAS ENCORE EXISTER)
	 *******************************************************************************************/
	public static function isObject($curObj=null)
	{
		return (!empty($curObj) && is_object($curObj) && !empty($curObj->_id));
	}

	/*******************************************************************************************
	 * VERIF : OBJECT EN COURS DE CRÉATION ET AVEC UN _ID==0 ?
	 *******************************************************************************************/
	public function isNew(){
		return empty($this->_id);
	}

	/*******************************************************************************************
	 * VERIF : OBJET "CONTAINER" QUI CONTIENT DU "CONTENT" ?  (calendar/forumSubjet/FOLDERS)
	 *******************************************************************************************/
	public static function isContainer(){
		return (static::MdlObjectContent!==null);
	}

	/*******************************************************************************************
	 * VERIF : OBJET "CONTENT" DANS UN "CONTAINER" ?  (file/contact/task/link/forumMessage/calendarEvent ...mais pas les FOLDERS!)
	 *******************************************************************************************/
	public static function isContainerContent(){
		return (static::MdlObjectContainer!==null);
	}

	/*******************************************************************************************
	 * VERIF : OBJET DANS UNE ARBORESCENCE ?  (file/contact/task/link/FOLDERS)
	 *******************************************************************************************/
	public static function isInArbo(){
		return (static::isFolderContent==true || static::isFolder==true);
	}

	/*******************************************************************************************
	 * VERIF : L'OBJET EST UN DOSSIER RACINE ?
	 *******************************************************************************************/
	public function isRootFolder(){
		return (static::isFolder==true && $this->_id==1);
	}

	/*******************************************************************************************
	 * VERIF : L'OBJET EST UN "CONTENT" DANS LE DOSSIER RACINE ? (avec ses propres droits d'accès) ?
	 *******************************************************************************************/
	public function isRootFolderContent(){
		return (static::isFolderContent==true && $this->_idContainer==1);
	}

	/*******************************************************************************************
	 * VERIF : L'OBJET POSSÈDE SES PROPRES DROITS D'ACCÈS ?
	 * L'objet possède explicitement des droits d'accès  ||  L'objet est un dossier  ||  L'objet se trouve dans le dossier racine (file/contact/link/task)
	 *******************************************************************************************/
	public function hasAccessRight(){
		return (static::$_hasAccessRight==true || $this->isRootFolderContent());
	}

	/*******************************************************************************************
	 * VERIF : OBJET "CONTENT" DONT LE DROIT D'ACCÈS DÉPEND D'UN "CONTAINER" ?
	 * Donc pas un objet d'un dossier racine, ni un "calendarEvent" (car affectés à plusieurs agendas)
	 *******************************************************************************************/
	public function accessRightFromContainer(){
		return (static::isContainerContent() && $this->isRootFolderContent()==false && static::objectType!="calendarEvent");
	}

	/*******************************************************************************************
	 * VERIF : L'USER COURANT EST L'AUTEUR DE L'OBJET ?
	 *******************************************************************************************/
	public function isAutor(){
		return (Ctrl::$curUser->isUser() && $this->_idUser==Ctrl::$curUser->_id);
	}

	/*******************************************************************************************
	 * VERIF : OBJET CRÉÉ À L'INSTANT ? (pour les mails de notif and co)
	****************************************************************************************** */
	public function isNewlyCreated(){
		return ($this->isNew()==false && time()-strtotime($this->dateCrea)<5);
	}

	/*******************************************************************************************
	 * RECUPÈRE L'OBJET CONTENEUR DE L'OBJET COURANT  (file/contact/task/link/subjectMessage/FOLDERS)
	 * Surchargé pour les "calendarEvent" car affectés à plusieurs agendas: donc renvoie plusieurs objets !
	 ******************************************************************************************/
	public function containerObj()
	{
		if($this->_containerObj===null && !empty($this->_idContainer)){
			$MdlObjectContainer=(static::isFolder==true)  ?  get_class($this)  :  static::MdlObjectContainer;//Récup le modèle du dossier courant || Récup le modèle de l'objet parent
			$this->_containerObj=Ctrl::getObj($MdlObjectContainer,$this->_idContainer);
		}
		return $this->_containerObj;
	}

	/*******************************************************************************************
	 * LISTE LES AFFECTATIONS ET DROITS D'ACCES DE L'OBJET (Espaces/groupes/users)
	 *******************************************************************************************/
	public function getAffectations()
	{
		if($this->_affectations===null)
		{
			//Init
			$this->_affectations=$affects=[];
			////	Objet existant : récupère les affectations en Bdd
			if($this->isNew()==false){
				$affects=Db::getTab("SELECT * FROM ap_objectTarget WHERE objectType=".Db::format(static::objectType)." AND _idObject=".$this->_id." ORDER BY _idSpace, target");//"ORDER BY" pour les labels du "contextMenu()"
				if(empty($affects))  {$affects[]=["_idSpace"=>Ctrl::$curSpace->_id, "target"=>"U".$this->_idUser, "accessRight"=>2];}//Aucun droit n'est défini : droit en écriture pour l'auteur (cf. agendas persos et affichage des droits d'accès par défaut dans le menu d'édition)
			}
			////	Nouvel objet : initialise les affectations par défaut en type "string" !
			else{
				$affects[]=["_idSpace"=>Ctrl::$curSpace->_id,  "target"=>"spaceUsers",				"accessRight"=>(static::isContainer() ? 1.5 : 1)];	//Users de l'espace courant : Ecriture limité (conteneurs)  ||  Lecture
				$affects[]=["_idSpace"=>Ctrl::$curSpace->_id,  "target"=>"U".Ctrl::$curUser->_id,	"accessRight"=>2];									//User courant (auteur) : Ecriture
			}
			////	Formate chaque affectation
			foreach($affects as $tmpAffect)
			{
				//Affectations détaillées :  "Espace Bidule > tous les utilisateurs"  /  "Groupe Bidule"  /  "Jean Dupont"
				$tmpAffect["targetType"]=$tmpAffect["target_id"]=$tmpAffect["label"]=null;
				if(preg_match("/^U/",$tmpAffect["target"]))		{$tmpAffect["targetType"]="user";			$tmpAffect["target_id"]=(int)substr($tmpAffect["target"],1);	$tmpAffect["label"]=Ctrl::getObj("user",$tmpAffect["target_id"])->getLabel();}
				elseif(preg_match("/^G/",$tmpAffect["target"]))	{$tmpAffect["targetType"]="group";			$tmpAffect["target_id"]=(int)substr($tmpAffect["target"],1);	$tmpAffect["label"]=Ctrl::getObj("userGroup",$tmpAffect["target_id"])->getLabel();}
				elseif($tmpAffect["target"]=="spaceUsers")		{$tmpAffect["targetType"]="spaceUsers";		$tmpAffect["target_id"]=$tmpAffect["_idSpace"];					$tmpSpace=Ctrl::getObj("space",$tmpAffect["_idSpace"]);		$tmpAffect["label"]=$tmpSpace->getLabel()." <img src='app/img/arrowRight.png'> ".(!empty($tmpSpace->public)?Txt::trad("EDIT_allUsersAndGuests"):Txt::trad("EDIT_allUsers"));}
				//Affectation perso/groupe sur un autre espace : ajoute le nom de l'espace (cf. "contextMenu()")
				if($tmpAffect["_idSpace"]!=Ctrl::$curSpace->_id && $tmpAffect["target"]!="spaceUsers")  {$tmpAffect["label"]=Ctrl::getObj("space",$tmpAffect["_idSpace"])->getLabel()." <img src='app/img/arrowRight.png'> ".$tmpAffect["label"];}
				//Ajoute l'affectation
				$targetKey=(int)$tmpAffect["_idSpace"]."_".$tmpAffect["target"];//concaténation des champs "_idSpace" et "target"
				$this->_affectations[$targetKey]=$tmpAffect;
			}
		}
		return $this->_affectations;
	}

	/*******************************************************************************************
	 * EDITE LES AFFECTATIONS ET DROITS D'ACCÈS DE L'OBJET (cf."editMenuSubmit()"). Par défaut : accès en lecture à l'espace courant
	 *******************************************************************************************/
	public function setAffectations($objectRightSpecific=null)
	{
		////	Object indépendant  &&  "objectRight" spécifié OU droit d'accès spécifiques
		if($this->hasAccessRight()  &&  (Req::isParam("objectRight") || !empty($objectRightSpecific)))
		{
			//Init
			$sqlInsertBase="INSERT INTO ap_objectTarget SET objectType=".Db::format(static::objectType).", _idObject=".$this->_id.", ";
			//Réinitialise les droits, uniquement sur les espaces auxquels l'user courant a accès
			if($this->isNew()==false){
				$sqlSpaces="_idSpace IN (".implode(",",Ctrl::$curUser->getSpaces("ids")).")";
				if(Ctrl::$curUser->isGeneralAdmin())	{$sqlSpaces="(".$sqlSpaces." OR _idSpace is null)";}
				Db::query("DELETE FROM ap_objectTarget WHERE objectType=".Db::format(static::objectType)." AND _idObject=".$this->_id." AND ".$sqlSpaces);
			}
			//Ajoute les nouveaux droits d'accès : passés en paramètre / provenant du formulaire
			$newAccessRight=Req::isParam("objectRight")  ?  Req::param("objectRight")  :  $objectRightSpecific;
			foreach($newAccessRight as $tmpRight){
				$tmpRight=explode("_",$tmpRight);//exple :  "55_U33_2"  devient ["_idSpace"=>"5","target"=>"U3","accessRight"=>"2"]  correspond à droit "2" sur l'user "33" de l'espace "55"
				Db::query($sqlInsertBase." _idSpace=".Db::format($tmpRight[0]).", target=".Db::format($tmpRight[1]).", accessRight=".Db::format($tmpRight[2]));
			}
		}
	}

	/*****************************************************************************************************************************************
	 * RÉCUPÈRE LE DROIT D'ACCÈS DE L'USER COURANT SUR L'OBJET
	 *		3	[total]					objet indépendant *	-> modif/suppression
	 *									objet conteneur **	-> modif/suppression du conteneur + modif/suppression de tout le contenu ***
	 *		2	[ecriture]				objet indépendant *	-> modif/suppression
	 *									objet conteneur **	-> lecture du conteneur + modif/suppression de tout le contenu ***
	 *		1.5	[ecriture limité]		objet indépendant *	-> -non disponible-
	 *									objet conteneur **	-> lecture du conteneur + modif/suppression du contenu créé par l'user courant
	 *		1	[lecture]				objet indépendant *	-> lecture
	 *									objet conteneur **	-> lecture du conteneur
	 *		*	objet indépendant : 		actualités et objets du dossier racine (fichiers, taches, etc)
	 *		**	objet conteneur : 			dossiers, agendas, sujets du forum
	 *		***	contenu d'un conteneur :	fichiers, taches, message du forum, etc qui héritent des droits d'accès du dossier conteneur
	******************************************************************************************************************************************/
	public function accessRight()
	{
		//Mise en cache
		if($this->_accessRight===null)
		{
			//Init
			$this->_accessRight=0;
			////	DROIT D'ACCES TOTAL  =>  Admin général  ||  Auteur de l'objet  ||  Nouvel objet
			if(Ctrl::$curUser->isGeneralAdmin() || $this->isAutor() || $this->createRight())  {$this->_accessRight=3;}
			////	DROIT D'ACCES EN LECTURE POUR UN ACCES EXTERNE
			elseif($this->md5IdControl())  {$this->_accessRight=1;}
			////	DROIT D'ACCES EN FONCTION DU CONTENEUR PARENT
			elseif($this->accessRightFromContainer())  {$this->_accessRight=$this->containerObj()->accessRight();}
			////	DROIT D'ACCES EN FONCTION DES AFFECTATIONS (cf. table "ap_objectTarget")
			elseif($this->hasAccessRight())
			{
				//Init la requete des droits d'acces
				$sqlAccessRight="objectType=".Db::format(static::objectType)." AND _idObject=".$this->_id." AND _idSpace=".Ctrl::$curSpace->_id;
				//Acces total si "isSpaceAdmin()" et objet affecté à l'espace (sauf pour les agendas perso : pas de privilège)
				if(Ctrl::$curUser->isSpaceAdmin() && Db::getVal("SELECT count(*) FROM ap_objectTarget WHERE ".$sqlAccessRight)>0 && static::objectType!="calendar" && $this->type!="user")  {$this->_accessRight=3;}
				//Acces en fonction des affectations à l'user => recupere le droit le plus important !
				else{
					$this->_accessRight=Db::getVal("SELECT max(accessRight) FROM ap_objectTarget WHERE ".$sqlAccessRight." AND target IN (".static::sqlAffectations().")");
					if(Ctrl::$curUser->isUser()==false && $this->_accessRight>1)  {$this->_accessRight=1;}//Guests : droit de lecture au maximum !
				}
			}
		}
		//Renvoie toujours un résultat "float" (cf. droit "1.5" en "ecriture limité"), et même pour les integers 
		return (float)$this->_accessRight;
	}

	/*******************************************************************************************
	 * DROIT DE CRÉER LE NOUVEL OBJET POUR L'USER COURANT
	 *******************************************************************************************/
	public function createRight()
	{
		if($this->_id==0){
			if($this->hasAccessRight() || static::MdlObjectContainer==null)	{return true;}										//Objet avec ses propres droits d'accès OU Objet indépendant (type "user", "space" ou autre)
			elseif($this->accessRightFromContainer())						{return $this->containerObj()->addContentRight();}	//Fonction du "addContentRight()" du parent (sauf "calendarEvent" car affectés à plusieurs agendas)
		}
		return false;//Retourne false dans tous les autres cas
	}

	/*******************************************************************************************
	 * DROIT DE LECTURE SUR L'OBJET POUR L'USER COURANT
	 *******************************************************************************************/
	public function readRight()
	{
		return ($this->accessRight()>0);
	}

	/*******************************************************************************************
	 * CONTENEUR : DROIT D'AJOUTER DU CONTENU AU CONTENEUR POUR L'USER COURANT  (accessRight >= 1.5)
	 *******************************************************************************************/
	public function addContentRight()
	{
		return (static::isContainer() && $this->accessRight()>1);
	}

	/*******************************************************************************************
	 * CONTENEUR : DROIT D'ÉDITER TOUT LE CONTENU DU CONTENEUR POUR L'USER COURANT  (accessRight >= 2)
	 *******************************************************************************************/
	public function editContentRight()
	{
		return (static::isContainer() && $this->accessRight()>=2);
	}

	/*******************************************************************************************
	 * DROIT D'ÉDITER L'OBJET POUR L'USER COURANT  (accessRight==3 pour les conteneurs : proprio ou admins  ||  accessRight==2 pour les autres objets)
	****************************************************************************************** */
	public function editRight()
	{
		return ($this->accessRight()==3  ||  (static::isContainer()==false && $this->accessRight()==2));
	}

	/*******************************************************************************************
	 * DROIT DE SUPPRIMER L'OBJET POUR L'USER COURANT
	 *******************************************************************************************/
	public function deleteRight()
	{
		return ($this->editRight());
	}

	/*******************************************************************************************
	 * DROIT COMPLET SUR L'OBJET POUR L'USER COURANT
	 *******************************************************************************************/
	public function fullRight()
	{
		return ($this->accessRight()==3);
	}

	/*******************************************************************************************
	 * DROIT DE LECTURE DE L'OBJET POUR L'USER COURANT (ERREUR S'IL N'EXISTE PLUS)
	 *******************************************************************************************/
	public function readControl()
	{
		if($this->accessRight()==0 || empty($this->_id))  {Ctrl::noAccessExit();}
	}

	/*******************************************************************************************
	 * CONTROLE LE DROIT D'EDITER L'OBJET POUR L'USER COURANT
	 *******************************************************************************************/
	public function editControl()
	{
		//Controle le droit d'accès en écriture
		if($this->editRight()==false)  {Ctrl::noAccessExit();}
		//Controle si l'objet n'est pas déjà en cour de modification par un autre user (cf. "messengerUpdate()")
		$_idUserEditSameObj=Db::getVal("SELECT _idUser FROM ap_userLivecouter WHERE _idUser!=".Ctrl::$curUser->_id." AND editTypeId=".Db::param("typeId")." AND `date` > ".(time()-60));
		if($this->isNew()==false && !empty($_idUserEditSameObj))  {Ctrl::notify(Txt::trad("elemEditedByAnotherUser")." ".Ctrl::getObj("user",$_idUserEditSameObj)->getLabel()." !");}
	}

	/*******************************************************************************************
	 * LABEL DE L'OBJET (Nom/Titre/etc : cf. "$requiredFields" des objets)
	 *******************************************************************************************/
	public function getLabel()
	{
		//Label principal
		if(!empty($this->name))				{$tmpLabel=$this->name;}		//Ex: nom de fichier
		elseif(!empty($this->title))		{$tmpLabel=$this->title;}		//Ex: task
		elseif(!empty($this->description))	{$tmpLabel=$this->description;}	//Ex: sujet/message du forum (sans titre)
		elseif(!empty($this->adress))		{$tmpLabel=$this->adress;}		//Ex: link
		else								{$tmpLabel=null;}
		//Renvoi un résultat "clean"
		return Txt::reduce($tmpLabel,50);
	}

	/*******************************************************************************************
	 * URL D'ACCÈS À L'OBJET  :  $display => "vue" / "edit" / "delete" / "default"
	********************************************************************************************/
	public function getUrl($display=null)
	{
		if($this->isNew())  {return "?ctrl=".static::moduleName;}//Objet qui n'existe plus ou pas encore
		else
		{
			$urlCtrl="?ctrl=".static::moduleName;
			if($display=="vue")									{return $urlCtrl."&typeId=".$this->_typeId."&action=Vue".static::objectType;}				//Vue détaillée dans une lightbox (user/contact/task/calendarEvent)
			elseif($display=="delete")							{return "?ctrl=object&action=delete&objectsTypeId[".static::objectType."]=".$this->_id;}	//Url de suppression via "CtrlObject->delete()"
			elseif($display=="edit" && static::isFolder==true)	{return "?ctrl=object&action=EditFolder&typeId=".$this->_typeId;}							//Edite un dossier via "actionEditFolder()"
			elseif($display=="edit")							{return $urlCtrl."&typeId=".$this->_typeId."&action=".static::objectType."Edit";}			//Edite un objet lambda : via une "action" spécifique
			elseif(static::isContainerContent())				{return $urlCtrl."&typeId=".$this->containerObj()->_typeId."&typeIdChild=".$this->_typeId;}	//Affichage par défaut d'un "content" dans son "Container" (file/contact/task/link/forumMessage). Surchargé pour les "calendarEvent" car on doit sélectionner l'agenda principal de l'evt
			else												{return $urlCtrl."&typeId=".$this->_typeId;}												//Affichage par défaut (Folder,news,forumSubject...)
		}
	}

	/*****************************************************************************************************************
	 * URL D'ACCÈS À L'OBJET
	 * Accès direct à l'élément  ||  Accès depuis une notifs mail (cf. "Ctrl::userConnectionSpaceSelection()")
	 *****************************************************************************************************************/
	public function getUrlExternal()
	{
		//Depuis la page de connexion : Cible l'espace courant > puis l'url encodé de l'objet (module + typeId de l'objet)
		return Req::getCurUrl()."/index.php?ctrl=offline&_idSpaceAccess=".Ctrl::$curSpace->_id."&objUrl=".urlencode($this->getUrl());
	}

	/*******************************************************************************************
	 * URL D'EDITION D'UN NOUVEL OBJET
	 *******************************************************************************************/
	public static function getUrlNew()
	{
		$url=(static::isFolder==true)  ?  "?ctrl=object&action=EditFolder"  :  "?ctrl=".static::moduleName."&action=".static::objectType."Edit";//Nouveau dossier ou nouvel objet
		if(!empty(Ctrl::$curContainer))  {$url.="&_idContainer=".Ctrl::$curContainer->_id;}//Ajoute l'id du container?
		return $url."&typeId=".static::objectType."-0";
	}

	/*******************************************************************************************
	 * IDENTIFIANT "md5()" DE L'OBJET POUR UN ACCÈS EXTERNE
	 *******************************************************************************************/
	public function md5Id()
	{
		return md5($this->_id.$this->dateCrea.$this->_idUser);
	}
	
	/*******************************************************************************************
	 * CONTROLE L'IDENTIFIANT MD5 PASSÉ EN PARAMETRE
	 *******************************************************************************************/
	public function md5IdControl()
	{
		return (Req::isParam("md5Id") && $this->md5Id()==Req::param("md5Id"));
	}

	/*******************************************************************************************
	 * SUPPRESSION D'UN OBJET
	 *******************************************************************************************/
	public function delete()
	{
		if($this->deleteRight())
		{
			//Supprime les fichiers joints (s'il y en a)
			foreach($this->attachedFileList() as $tmpFile)  {$this->attachedFileDelete($tmpFile);}
			//Init la sélection de l'objet dans les tables
			$sqlSelectObject="WHERE objectType=".Db::format(static::objectType)." AND _idObject=".$this->_id;
			//Supprime les éventuels "usersComment" & "usersLike"
			if(static::hasUsersComment)	{Db::query("DELETE FROM ap_objectComment ".$sqlSelectObject);}
			if(static::hasUsersLike)	{Db::query("DELETE FROM ap_objectLike ".$sqlSelectObject);}
			//Ajoute le log de suppression, mais pas en dernier!
			Ctrl::addLog("delete",$this);
			//Supprime les droits d'accès (s'il y en a)
			Db::query("DELETE FROM ap_objectTarget ".$sqlSelectObject);
			//Supprime enfin l'objet lui-même !
			Db::query("DELETE FROM ".static::dbTable." WHERE _id=".$this->_id);
		}
	}

	/*******************************************************************************************
	 * DÉPLACE UN OBJET (DOSSIER OU CONTENU) DANS UN AUTRE DOSSIER
	 *******************************************************************************************/
	public function folderMove($newFolderId)
	{
		////	Ancien et nouveau dossier
		$oldFolder=$this->containerObj();
		$newFolder=Ctrl::getObj($oldFolder::objectType, $newFolderId);
		////	Objet pas dans une arbo? Droit d'accès pas ok? || dossier de destination inaccessible sur le disque? || Déplace un dossier à l'interieur de lui même?
		if(static::isInArbo()==false || $this->accessRight()<2 || $newFolder->accessRight()<2 || (static::objectType=="fileFolder" && is_dir($newFolder->folderPath("real"))==false))	{Ctrl::notify(Txt::trad("inaccessibleElem")." : ".$this->name.$this->title);}
		elseif(static::isFolder && $this->isInFolderTree($newFolderId))																													{Ctrl::notify(Txt::trad("NOTIF_folderMove")." : ".$this->name);}
		else
		{
			////	Change le dossier conteneur
			Db::query("UPDATE ".static::dbTable." SET _idContainer=".(int)$newFolderId." WHERE _id=".$this->_id);
			//Contenu de dossier : change les droits d'accès?
			if(static::isFolder==false)
			{
				//Réinitialise les droits d'accès
				Db::query("DELETE FROM ap_objectTarget WHERE objectType=".Db::format(static::objectType)." AND _idObject=".$this->_id);
				//Déplace à la racine : récupère les droits d'accès de l'ancien dossier conteneur
				if($newFolder->isRootFolder()){
					foreach(Db::getTab("SELECT * FROM ap_objectTarget WHERE objectType='".$oldFolder::objectType."' AND _idObject=".$oldFolder->_id) as $oldAccessRight){
						Db::query("INSERT INTO ap_objectTarget SET objectType=".Db::format(static::objectType).", _idObject=".$this->_id.", _idSpace=".(int)$oldAccessRight["_idSpace"].", target=".Db::format($oldAccessRight["target"]).", accessRight=".Db::format($oldAccessRight["accessRight"]));
					}
				}
			}
			////	Reload l'objet (et du cache)
			$reloadedObj=Ctrl::getObj(static::objectType, $this->_id, true);
			////	Déplace un fichier sur le disque
			if(static::objectType=="file"){
				//Deplace chaque version du fichier
				foreach(Db::getTab("SELECT * FROM ap_fileVersion WHERE _idFile=".$this->_id) as $tmpFileVersion){
					rename($oldFolder->folderPath("real").$tmpFileVersion["realName"], $newFolder->folderPath("real").$tmpFileVersion["realName"]);
				}
				//Déplace la vignette
				if($this->hasThumb()){
					rename($oldFolder->folderPath("real").$this->getThumbName(), $newFolder->folderPath("real").$this->getThumbName());
				}
			}
			////	Déplace un dossier sur le disque (du chemin actuel : $this,  vers le nouveau chemin : $reloadedObj)
			elseif(static::objectType=="fileFolder"){
				rename($this->folderPath("real"), $reloadedObj->folderPath("real"));
			}
			////	Ajoute aux logs
			Ctrl::addLog("modif", $reloadedObj, Txt::trad("changeFolder"));
			return true;
		}
	}

	/*******************************************************************************************
	 * LISTE DES USERS AFFECTÉS À L'OBJET
	 *******************************************************************************************/
	public function affectedUserIds($onlyWriteAccess=false)
	{
		//Init les users et l'objet de référence des affectations (ex: dossier parent d'un fichier)
		$userIds=[];
		$refObject=($this->hasAccessRight())  ?  $this :  $this->containerObj();
		//Récupère les users de chaque affectation
		foreach($refObject->getAffectations() as $affect){
			if($onlyWriteAccess==true && $affect["accessRight"]<2)	{continue;}																							//Uniquement accès en écriture ? (cf. agendas perso)
			elseif($affect["targetType"]=="spaceUsers")				{$userIds=array_merge($userIds, Ctrl::getObj("space",$affect["target_id"])->getUsers("idsTab"));}	//Ajoute tous les users de l'espace
			elseif($affect["targetType"]=="group")					{$userIds=array_merge($userIds, Ctrl::getObj("userGroup",$affect["target_id"])->userIds);}			//Ajoute les users du groupe
			elseif($affect["targetType"]=="user")					{$userIds[]=$affect["target_id"];}																	//Ajoute l'user
		}
		//Renvoi la liste des users
		return array_unique($userIds);
	}

	/*******************************************************************************************
	 * AJOUT/MODIF D'OBJET
	 *******************************************************************************************/
	public function createUpdate($sqlProperties)
	{
		if($this->editRight())
		{
			////	Enleve les éventuels espaces et virgules en début/fin de requête
			$sqlProperties=trim(trim($sqlProperties),",");
			////	Date et Auteur : création ou modif
			if(static::objectType!="agora"){
				if($this->isNew())	{$sqlProperties.=", dateCrea=".Db::dateNow().", _idUser=".Db::format(Ctrl::$curUser->_id);}
				else				{$sqlProperties.=", dateModif=".Db::dateNow().", _idUserModif=".Db::format(Ctrl::$curUser->_id);}
			}
			////	Propriétés optionnelles "_idContainer", "shortcut" (attention au decochage)
			if(Req::isParam("_idContainer"))	{$sqlProperties.=", _idContainer=".Db::param("_idContainer");}
			if(static::hasShortcut==true)		{$sqlProperties.=", shortcut=".Db::param("shortcut");}
			////	LANCE L'INSERT/UPDATE !!
			if($this->isNew())	{$_id=(int)Db::query("INSERT INTO ".static::dbTable." SET ".$sqlProperties, true);}
			else{
				Db::query("UPDATE ".static::dbTable." SET ".$sqlProperties." WHERE _id=".$this->_id);
				$_id=$this->_id;
			}
			////	Reload l'objet pour prendre en compte les nouvelles propriétés ("true" pour l'update du cache)
			$reloadedObj=Ctrl::getObj(static::objectType, $_id, true);
			////	Ajoute si besoin les droits d'accès
			$reloadedObj->setAffectations();
			////	Ajoute si besoin les fichiers joints
			$reloadedObj->attachedFileAdd();
			////	Reload à nouveau l'objet (ex: si la description est maj avec insertion d'image)
			$reloadedObj=Ctrl::getObj(static::objectType, $_id, true);
			////	Ajoute aux Logs  &  Renvoie l'objet rechargé
			$logAction=(strtotime($reloadedObj->dateCrea)==time())  ?  "add"  :  "modif";
			Ctrl::addLog($logAction,$reloadedObj);
			return $reloadedObj;
		}
	}

	/*******************************************************************************************
	 * ENVOI UNE NOTIF PAR MAIL DE L'EDITION DE L'OBJET (cf. "menuEdit")
	 *******************************************************************************************/
	public function sendMailNotif($messageSpecific=null, $addFiles=null, $addUserIds=null)
	{
		//Notification demandé par l'auteur de l'objet  OU  Destinataires passés en paramètres (ex: notif de nouveaux messages du forum)
		if(Req::isParam("notifMail") || !empty($addUserIds))
		{
			////	Sujet : "Fichier créé par boby SMITH"
			$tradCreaModif=($this->isNew() || $this->isNewlyCreated())  ?  "MAIL_elemCreatedBy"  :  "MAIL_elemModifiedBy";//Ex: "Fichier créé par" / "News modifiée par"
			$subject=ucfirst($this->tradObject($tradCreaModif))." ".Ctrl::$curUser->getLabel();
			////	Message : Label principal de l'objet et si besoin description de l'objet
			if(!empty($messageSpecific))	{$messageObj="<div><b>".$messageSpecific."</b></div>";}	//ex: nom des fichiers uploadés, etc 
			elseif(!empty($this->title))	{$messageObj="<div><b>".$this->title."</b></div>";}		//ex: titre des sujets, task, etc
			elseif(!empty($this->name))		{$messageObj="<div><b>".$this->name."</b></div>";}		//ex: nom des dossiers, etc
			else							{$messageObj=null;}
			if(!empty($this->description))	{$messageObj.="<div><b>".$this->description."</b></div>";}
			////	Message : Remplace les chemins relatifs en chemins absolus && Ajoute le max-width des images des descriptions
			$messageObj=str_replace(PATH_DATAS, Req::getCurUrl()."/".PATH_DATAS, $messageObj);
			$messageObj=str_replace("<img ", "<img style='max-width:100%!important;cursor:initial' ", $messageObj);
			////	Message : Corps du mail (pas de balise <style> car souvent supprimés par les clients mail)
			$message="<div style='margin:20px 0px'>".$subject." :</div>
					  <div style='margin:20px 0px;padding:10px;max-width:1024px;background-color:#eee;color:#333;border:1px solid #bbb;border-radius:3px;'>".$messageObj."</div>
					  <a href=\"".$this->getUrlExternal()."\" target='_blank'>".Txt::trad("MAIL_elemAccessLink")."</a>";
			////	Destinataires de la notif : Users spécifiques OU Users affectées à l'objet (lecture ou+)
			$mailUserIds=[];
			if(Req::isParam("notifMail"))	{$mailUserIds=Req::isParam("notifMailUsers") ? Req::param("notifMailUsers") : $this->affectedUserIds();}
			////	Ajoute si besoin les destinataires passés en paramètres (ex: notif de nouveaux messages du forum)
			if(!empty($addUserIds)){
				if(Req::isParam("notifMail")==false)  {$noNotify=true;}//Pas de "notifiy()" sur l'envoie de l'email
				$mailUserIds=array_unique(array_merge($mailUserIds,$addUserIds));
			}
			////	Envoi du message
			if(!empty($mailUserIds))
			{
				$options[]=(!empty($noNotify))  ?  "noNotify"  :  "objectNotif";										//Options "notify()" : pas de notif OU notif "L'email de notification a bien été envoyé"
				if(Req::isParam("mailOptions"))  		{$options=array_merge($options,Req::param("mailOptions"));}		//Options sélectionnées par l'user
				if(static::descriptionEditor==true)		{$message=$this->attachedFileImageCid($message);}				//Affiche si besoin les images en pièce jointe dans le corps du mail
				if(Req::isDevServer())  				{$message=str_replace($_SERVER['HTTP_HOST']."/omnispace","www.omnispace.fr",$message);}		//Evite le spam en DEV (cf. "getUrlExternal()")
				$attachedFiles=$this->attachedFileList();																//Fichiers joints de l'objet
				if(!empty($addFiles))  {$attachedFiles=array_merge($addFiles,$attachedFiles);}							//Ajoute si besoin les fichiers spécifiques (ex: fichier ".ics" d'un évenement)
				Tool::sendMail($mailUserIds, $subject, $message, $options, $attachedFiles);								//Envoie l'email
			}
		}
	}

	/*******************************************************************************************
	 * STATIC SQL : PREPARE LA SELECTION D'OBJETS EN FONCTION DE LEUR AFFECTATION
	 * "targets" (exple) : "spaceUsers" / "U1" / "G1"
	 *******************************************************************************************/
	protected static function sqlAffectations()
	{
		if(static::$_sqlTargets===null)
		{
			//Objets affectés à tous les users de l'espace (et si besoin les 'guests')
			static::$_sqlTargets="'spaceUsers'";
			//Ajoute les objets affectés à l'user courant et ceux affectés à ses groupes
			if(Ctrl::$curUser->isUser()){
				static::$_sqlTargets.=",'U".Ctrl::$curUser->_id."'";
				foreach(MdlUserGroup::getGroups(Ctrl::$curSpace,Ctrl::$curUser) as $tmpGroup)  {static::$_sqlTargets.=",'G".$tmpGroup->_id."'";}
			}
		}
		return static::$_sqlTargets;
	}

	/*******************************************************************************************
	 * STATIC SQL : OBJETS A AFFICHER EN FONCTION DES DROITS D'ACCÈS DE L'USER COURANT
	 *******************************************************************************************/
	public static function sqlDisplay($containerObj=null, $keyId="_id")
	{
		////	Init les conditions et sélectionne si besoin un conteneur
		$conditions=(is_object($containerObj))  ?  ["_idContainer=".$containerObj->_id]  :  [];
		////	Selection en fonction des droits d'acces dans "ap_objectTarget" (cf. "hasAccessRight()") :  Objets avec des droits d'accès || Objets d'une arbo (de toute l'arbo si sélection de "plugin" || Objets à la racine)
		if(static::$_hasAccessRight==true  ||  (static::isFolderContent==true && ($containerObj==null || $containerObj->isRootFolder()))){
			$sqlTargets=(!empty($_SESSION["displayAdmin"]))  ?  null  :  "and `target` in (".static::sqlAffectations().")";//Sélectionne tous les objets de l'espace ("null")  ||  Sélection en fonction des affectations
			$conditions[]=$keyId." IN (select _idObject as ".$keyId." from ap_objectTarget where objectType='".static::objectType."' and _idSpace=".Ctrl::$curSpace->_id."  ".$sqlTargets.")";
		}
		////	Fusionne toutes les conditions avec "AND"  ||  Sélection par défaut (retourne aucune erreur ni objet)
		$returnSql=(!empty($conditions))  ?  "(".implode(' AND ',$conditions).")"  :  $keyId." IS NULL";
		////	Selection de "plugin" : selectionne les objets des conteneurs auquel on a acces (dossiers/sujets..)
		if($containerObj==null && static::isContainerContent()){
			$MdlObjectContainer=static::MdlObjectContainer;
			$returnSql="(".$returnSql." OR ".$MdlObjectContainer::sqlDisplay(null,"_idContainer").")";//Appel récursif avec "_idContainer" comme $keyId
		}
		////	Renvoi le résultat
		return $returnSql;
	}

	/*******************************************************************************************
	 * RECUPÈRE LES OBJETS D'UN "PLUGIN" (FONCTION DE $params ET DES DROITS D'ACCES)
	 *******************************************************************************************/
	public static function getPluginObjects($params)
	{
		return (!empty($params))  ?  Db::getObjTab(static::objectType, "SELECT * FROM ".static::dbTable." WHERE ".static::sqlPlugins($params)." AND ".static::sqlDisplay()." ORDER BY dateCrea desc")  :  [];
	}

	/*******************************************************************************************
	 * RECUPERE LES PLUGINS DE TYPE "FOLDER"
	 *******************************************************************************************/
	public static function getPluginFolders($params)
	{
		$pluginsList=[];
		foreach(static::getPluginObjects($params) as $objFolder)
		{
			$objFolder->pluginIcon="folder/folderSmall.png";
			$objFolder->pluginLabel=$objFolder->name;
			$objFolder->pluginTooltip=$objFolder->folderPath("text");
			if(!empty($objFolder->description))  {$objFolder->pluginTooltip.="<hr>".Txt::reduce($objFolder->description);}
			$objFolder->pluginJsIcon="windowParent.redir('".$objFolder->getUrl()."');";//Redir vers le dossier
			$objFolder->pluginJsLabel=$objFolder->pluginJsIcon;
			$pluginsList[]=$objFolder;
		}
		return $pluginsList;
	}

	/*******************************************************************************************
	 * STATIC SQL : SELECTIONNE LES OBJETS EN FONCTION DU TYPE DE PLUGIN
	 * $params["type"] =>  "dashboard" : cree dans la periode selectionné  ||  "shortcut" : ayant un raccourci  ||  "search" : issus d'une recherche
	 *******************************************************************************************/
	public static function sqlPlugins($params)
	{
		if($params["type"]=="dashboard")	{return "dateCrea BETWEEN ".Db::format($params["dateTimeBegin"])." AND ".Db::format($params["dateTimeEnd"]);}
		elseif($params["type"]=="shortcut")	{return "shortcut=1";}
		elseif($params["type"]=="search")
		{
			////	Init la requete SQL  &&  La liste des champs de recherche (tous ou uniquement ceux demandés)
			$returnSql=null;
			$objSearchFields=(!empty($params["searchFields"]))  ?  array_intersect(static::$searchFields,$params["searchFields"])  :  static::$searchFields;
			////	Recherche "l'expression exacte"
			if($params["searchMode"]=="exactPhrase"){
				foreach($objSearchFields as $tmpField){																											//Recherche sur chaque champ de l'objet
					$searchText=($tmpField=="description" && static::descriptionEditor==true) ?  htmlentities($params["searchText"])  :  $params["searchText"];	//Texte brut ou avec les accents de l'éditeur (&agrave; &egrave; etc)
					$returnSql.="`".$tmpField."` LIKE ".Db::format($searchText,"sqlLike")." OR ";																//"sqlLike" délimite le texte avec "%"  &&  "OR" pour rechercher sur le champ suivant
				}
			}
			////	Recherche "n'importe quel mot"  ||  Recherche "Tous les mots"
			else{
				$searchWords=explode(" ",$params["searchText"]);																		//Liste des mots clés recherchés
				$operatorWords=($params["searchMode"]=="anyWord")  ?  " OR "  :  " AND ";												//Opérateur entre chaque mot : "n'importe quel mot" ou "Tous les mots" (laisser les espaces)
				foreach($objSearchFields as $tmpField){																					//Recherche sur chaque champ de l'objet
					$sqlWords=null;																										//Init la sous-requete pour chaque mot
					foreach($searchWords as $tmpWord){																					//Sélection SQL pour chaque mot recherché
						$tmpWord=($tmpField=="description" && static::descriptionEditor==true)  ?  htmlentities($tmpWord)  :  $tmpWord;	//Texte brut ou avec les accents de l'éditeur (&agrave; &egrave; etc)
						$sqlWords.="`".$tmpField."` LIKE ".Db::format($tmpWord,"sqlLike").$operatorWords;								//"sqlLike" délimite le texte avec "%"  
					}	
					$returnSql.="(".trim($sqlWords,$operatorWords).") OR ";																//Supprime le dernier $operatorWords  &&  Ajoute "OR" pour chercher sur le champ suivant
				}
			}
			////	Supprime le dernier opérateur "OR" entre chaque champ de recherche
			$returnSql="(".trim($returnSql," OR ").")";
			////	Filtre en fonction de la date de creation
			if($params["creationDate"]!="all"){
				$timeCreationDate=time() - (86400 * $params["creationDate"]);
				$returnSql.=" AND dateCrea >= '".date("Y-m-d 00:00",$timeCreationDate)."'";
			}
			////	Retourne le résultat
			return $returnSql;
		}
	}

	/*******************************************************************************************
	 * AUTEUR DE L'OBJET
	 *******************************************************************************************/
	public function autorLabel($getCreator=true, $tradAutor=false)
	{
		$labelAutor=($tradAutor==true)  ?  Txt::trad("autor")." : "  :  null;
		if(!empty($this->guest))			{return $labelAutor.$this->guest." (".Txt::trad("guest").")";}				//Invité
		elseif($getCreator==true)			{return $labelAutor.Ctrl::getObj("user",$this->_idUser)->getLabel();}		//Créateur de l'objet
		elseif(!empty($this->_idUserModif))	{return $labelAutor.Ctrl::getObj("user",$this->_idUserModif)->getLabel();}	//Auteur de dernière modif
	}

	/*******************************************************************************************
	 * DATE DE CRÉATION OU MODIFICATION
	 *******************************************************************************************/
	public function dateLabel($isDateCrea=true, $format="dateFull")
	{
		//Renvoie la date de création si elle est demandée || si "dateModif" n'est pas (encore) spécifié 
		return ($isDateCrea==true || empty($this->dateModif))  ?  Txt::dateLabel($this->dateCrea,$format)  :  Txt::dateLabel($this->dateModif,$format);
	}

	/*******************************************************************************************
	 * AUTEUR DE L' OBJET + DATE DE CRÉATION OU MODIFICATION
	 *******************************************************************************************/
	public function autorDateLabel($withAutorIcon=false)
	{
		$autorIcon=($withAutorIcon==true)  ?  Ctrl::getObj("user",$this->_idUser)->getImg(true,true)  :  null;
		return $autorIcon." ".$this->autorLabel()."<div class='objAutorDateCrea'>".$this->dateLabel(false)."</div>";
	}

	/*******************************************************************************************
	 * LIBELLE DE L'OBJET (CHANGE -OBJLABEL- & CO)
	 *******************************************************************************************/
	public function tradObject($tradKey)
	{
		//// Traduction principale
		$trad=Txt::trad($tradKey);
		//// Remplace le label principal de l'objet (ex: "news", "fichier", "dossier")
		$trad=str_replace("-OBJLABEL-", Txt::trad("OBJECT".static::objectType), $trad);
		//// Remplace le label du contenu de l'objet (ex: "fichier" si l'objet courant est un dossier)
		if(static::isContainer()){
			$MdlObjectContent=static::MdlObjectContent;
			$trad=str_replace("-OBJCONTENT-", Txt::trad("OBJECT".$MdlObjectContent::objectType), $trad);
		}
		//// Remplace le label du conteneur de l'objet (ex: "agenda" si l'objet courant est un evt)
		if(static::isContainerContent()){
			$MdlObjectContainer=static::MdlObjectContainer;
			$trad=str_replace("-OBJCONTAINER-", Txt::trad("OBJECT".$MdlObjectContainer::objectType), $trad);
		}
		/// Retourne la trad
		return $trad;
	}

	/*******************************************************************************************
	 * FICHIER JOINT : INFOS SUR UN FICHIER JOINT
	 *******************************************************************************************/
	public static function attachedFileInfos($file)
	{
		if(!empty($file))
		{
			if(is_numeric($file))  {$file=Db::getLine("SELECT * FROM ap_objectAttachedFile WHERE _id=".(int)$file);}	//Si besoin, récupère les infos en bdd
			$file["path"]=PATH_OBJECT_ATTACHMENT.$file["_id"].".".File::extension($file["name"]);						//Path/chemin réel du fichier
			$file["url"]=CtrlObject::attachedFileDisplayUrl($file["_id"], $file["name"]);								//Url pour un affichage du fichier via "actionAttachedFileDisplay()"
			$file["containerObj"]=Ctrl::getObj($file["objectType"],$file["_idObject"]);									//Ajoute l'objet dont dépend le fichier joint
			if(File::isType("imageBrowser",$file["name"]))  {$file["cid"]="attachedFile".$file["_id"];}					//"cid" du fichier pour l'envoi des mails (cf. "attachedFileImageCid()")
			return $file;
		}
	}

	/*******************************************************************************************
	 * FICHIER JOINT : AJOUTE DANS LE CORPS DE L'EMAIL LES IMAGES EN PIECE JOINTE => "CID"
	 *******************************************************************************************/
	public function attachedFileImageCid($mailMessage)
	{
		foreach($this->attachedFileList() as $tmpFile){																		//Parcourt chaque fichier joint de l'objet courant
			if(!empty($tmpFile["cid"]))  {$mailMessage=str_replace($tmpFile["url"], "cid:".$tmpFile["cid"], $mailMessage);}	//Si c'est une image, on remplace l'url par le "cid" (ex: CID="XYZ" correspond à "<img src='cid:XYZ'>")
		}
		return $mailMessage;
	}

	/*******************************************************************************************
	 * FICHIER JOINT : TABLEAU DES FICHIERS JOINTS DE L'OBJET
	 *******************************************************************************************/
	public function attachedFileList()
	{
		//Mise en cache
		if($this->_attachedFiles===null){																														
			if(static::hasAttachedFiles!==true)  {$this->_attachedFiles==[];}																					//Ce type d'objet ne gère pas les fichiers joint : tableau vide
			else{
				$this->_attachedFiles=Db::getTab("SELECT * FROM ap_objectAttachedFile WHERE objectType='".static::objectType."' AND _idObject=".$this->_id);	//Récupère les fichiers joints de l'objet en BDD
				foreach($this->_attachedFiles as $key=>$tmpFile)  {$this->_attachedFiles[$key]=self::attachedFileInfos($tmpFile);}								//Ajoute le "path" et autres infos de chaque fichier joint
			}
		}
		//Retoune les résultats (toujours au format "array")
		return (array)$this->_attachedFiles;
	}

	/*********************************************************************************************************************************
	 * FICHIER JOINT : AFFICHE LES FICHIERS JOINTS DE L'OBJET (Menu contextuel OU vue description. Affiche et propose le téléchargement)
	 *********************************************************************************************************************************/
	public function attachedFileMenu($separator="<hr>")
	{
		//Mise en cache
		if($this->_attachedFilesMenu===null)
		{
			$this->_attachedFilesMenu="";
			//Affiche le menu avec chaque fichiers
			if(count($this->attachedFileList())>0)
			{
				foreach($this->attachedFileList() as $tmpFile){
					$getFileUrl="?ctrl=object&action=AttachedFileDownload&_id=".$tmpFile["_id"];
					if(Req::isMobileApp())  {$getFileUrl=CtrlMisc::urlGetFile($getFileUrl,$tmpFile["name"]);}//Download externe via mobileApp : modif l'url pour switcher sur "ctrl=misc"
					$this->_attachedFilesMenu.="<div class='attachedFileMenu' title=\"".Txt::trad("download")."\" onclick=\"if(confirm('".Txt::trad("download",true)." ?')) redir('".$getFileUrl."');\"><img src='app/img/attachment.png'> ".$tmpFile["name"]."</div>";
				}
			}
		}
		//Retoune les résultats, avec un séparateur différent pour chaque affichage
		if($this->_attachedFilesMenu)  {return $separator.$this->_attachedFilesMenu;}
	}

	/*******************************************************************************************
	 * FICHIER JOINT : AJOUTE LES FICHIERS JOINTS DU "editMenuSubmit()"
	 *******************************************************************************************/
	public function attachedFileAdd()
	{
		if(static::hasAttachedFiles==true)
		{
			foreach($_FILES as $inputId=>$tmpFile)
			{
				//Ajoute chaque fichier joint (cf. "VueObjAttachedFile.php")
				if(stristr($inputId,"attachedFile") && File::uploadControl($tmpFile))
				{
					//Ajoute le fichier en Bdd et dans le dossier de destination
					$_idFile=Db::query("INSERT INTO ap_objectAttachedFile SET name=".Db::format($tmpFile["name"]).", objectType='".static::objectType."', _idObject=".$this->_id, true);
					$filePath=PATH_OBJECT_ATTACHMENT.$_idFile.".".File::extension($tmpFile["name"]);
					$isMoved=move_uploaded_file($tmpFile["tmp_name"], $filePath);
					//Fichier ajouté
					if($isMoved==true)
					{
						//Optimise si besoin le fichier + chmod
						if(File::isType("imageResize",$filePath))  {File::imageResize($filePath,$filePath,1600);}
						File::setChmod($filePath);
						//Nouvelle Image/Vidéo/Mp3 insérée dans l'éditeur TinyMce : remplace le "fileSrcTmp" par le path final
						if(static::descriptionEditor==true && File::isType("attachedFileInsert",$tmpFile["name"])){												//Vérifie qu'il s'agit d'un fichier autorisé
							$inputCpt=str_replace("attachedFile","",$inputId);																					//Récupère le compteur de l'input (cf. "VueObjAttachedFile.php")
							$editorContent=Db::getVal("SELECT `description` FROM `".static::dbTable."` WHERE _id=".$this->_id);									//Récupère le texte de l'éditeur
							$editorContent=str_replace("fileSrcTmp".$inputCpt, CtrlObject::attachedFileDisplayUrl($_idFile,$tmpFile["name"]), $editorContent);	//Remplace "fileSrcTmp" par l'url d'affichage du fichier
							$editorContent=str_replace("attachedFileTagTmp".$inputCpt, "attachedFileTag".$_idFile, $editorContent);								//Remplace "attachedFileTagTmp" par l'id du fichier en BDD
							Db::query("UPDATE ".static::dbTable." SET `description`=".Db::format($editorContent)." WHERE _id=".$this->_id);						//Update le texte de l'éditeur !
						}
					}
				}
			}
			File::datasFolderSize(true);//Recalcule $_SESSION["datasFolderSize"]
		}
	}

	/*******************************************************************************************
	 * FICHIER JOINT : SUPPRIME UN FICHIER JOINT
	 *******************************************************************************************/
	public function attachedFileDelete($curFile)
	{
		if($this->editRight() && is_array($curFile)){
			File::rm($curFile["path"]);
			if(!is_file($curFile["path"])){
				Db::query("DELETE FROM ap_objectAttachedFile WHERE _id=".(int)$curFile["_id"]);
				return true;
			}
		}
	}

	/*******************************************************************************************
	 * COMMENTAIRES : L'OBJET PEUT AVOIR DES COMMENTAIRES?
	 *******************************************************************************************/
	public function hasUsersComment()
	{
		return (static::hasUsersComment && !empty(Ctrl::$agora->usersComment));
	}

	/*******************************************************************************************
	 * COMMENTAIRES : LISTE LES COMMENTAIRES
	 *******************************************************************************************/
	public function getUsersComment()
	{
		//Mise en cache
		if($this->_usersComment===null)
			{$this->_usersComment=Db::getTab("SELECT * FROM ap_objectComment WHERE objectType='".static::objectType."' AND _idObject=".$this->_id);}
		//Renvoi les résultats
		return $this->_usersComment;
	}

	/*******************************************************************************************
	 * COMMENTAIRE : DROIT D'ÉDITION/SUPPRESSION D'UN COMMENTAIRE
	 *******************************************************************************************/
	public static function userCommentEditRight($_idComment)
	{
		if(!empty($_idComment)){
			$idUser=Db::getVal("SELECT _idUser FROM ap_objectComment WHERE _id=".(int)$_idComment);
			return (Ctrl::$curUser->isGeneralAdmin() || $idUser==Ctrl::$curUser->_id);
		}
	}

	/*******************************************************************************************
	 * LIKES : L'OBJET PEUT AVOIR DES "LIKES"?
	 *******************************************************************************************/
	public function hasUsersLike()
	{
		return (static::hasUsersLike && !empty(Ctrl::$agora->usersLike));
	}

	/*******************************************************************************************
	 * LIKES : LISTE DES "LIKE"/"DONTLIKE" (passer en parametre)
	 *******************************************************************************************/
	public function getUsersLike($like_dontlike)
	{
		//Mise en cache
		if($this->_usersLike===null){
			//Init les "like" et "dontike"
			$this->_usersLike=["like"=>[],"dontlike"=>[]];
			//Récupère les users (non supprimés) qui ont posté un "like" ou "dontlike"
			foreach(Db::getTab("SELECT * FROM ap_objectLike WHERE objectType='".static::objectType."' AND _idObject=".$this->_id." AND _idUser IN (select _id from ap_user)")  as  $tmpLike){
				if($tmpLike["value"]==1)	{$this->_usersLike["like"][]=$tmpLike;}
				else						{$this->_usersLike["dontlike"][]=$tmpLike;}
			}
		}
		//Renvoie le tableau de résultats
		return $this->_usersLike[$like_dontlike];
	}

	/*******************************************************************************************
	 * LIKES : RÉCUPÈRE LE TOOLTIP ($like_dontlike => "like" ou "donlike")
	 *******************************************************************************************/
	public function getUsersLikeTooltip($like_dontlike)
	{
		$tooltip=Txt::trad("AGORA_usersLike_".$like_dontlike)."<br>";
		foreach($this->getUsersLike($like_dontlike) as $tmpLike)  {$tooltip.=Ctrl::getObj("user",$tmpLike["_idUser"])->getLabel().", ";}
		return trim($tooltip,", ");
	}
}