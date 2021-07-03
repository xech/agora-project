<?php
/**
* This file is part of the Agora-Project Software package.
*
* @copyright (c) Agora-Project Limited <https://www.agora-project.net>
* @license GNU General Public License, version 2 (GPL-2.0)
*/


/*
 * Classe principale des Objects
 */
class MdlObject
{
	//Utilise les classes des menus et attributs
	use MdlObjectMenus, MdlObjectAttributes;

	//Propriétés de base
	const moduleName=null;
	const objectType=null;
	const dbTable=null;
	//Propriétés de dépendance
	const MdlObjectContent=null;
	const MdlObjectContainer=null;
	const isFolder=false;
	const isFolderContent=false;
	protected static $_hasAccessRight=null;
	//Propriétés d'affichage et d'édition (d'IHM)
	const isSelectable=false;
	const hasShortcut=false;
	const hasNotifMail=false;
	const hasAttachedFiles=false;
	const hasUsersLike=false;
	const hasUsersComment=false;
	const htmlEditorField=null;			//Champ "description" le plus souvent
	public static $displayModes=[];		//Type d'affichage : ligne/block le plus souvent
	public static $requiredFields=[];	//Champs obligatoires pour valider l'édition d'un objet
	public static $searchFields=[];		//Champs de recherche
	public static $sortFields=[];		//Champs/Options de tri des résulats
	//Valeurs mises en cache
	private $_accessRight=null;
	private $_containerObj=null;
	private $_affectations=null;
	protected static $_sqlTargets=null;

	/*******************************************************************************************
	 * CONSTRUCTEUR
	 *******************************************************************************************/
	function __construct($objIdOrValues=null)
	{
		////	Par défaut
		$this->_id=0;
		////	Assigne des propriétés à l'objet : Objet déjà créé / Objet à créer
		if(!empty($objIdOrValues)){
			//Récupère les propriétés en bdd / propriétés déjà passées en paramètre
			$objValues=(is_numeric($objIdOrValues))  ?  Db::getLine("select * from ".static::dbTable." where _id=".(int)$objIdOrValues)  :  $objIdOrValues;
			//S'il y a des propriétés
			if(!empty($objValues)){
				foreach($objValues as $propertieKey=>$propertieVal)  {$this->$propertieKey=$propertieVal;}
			}
		}
		////	Identifiant tjs numérique + Identifiant générique (exple : "fileFolder-19")
		$this->_id=(int)$this->_id;
		$this->_targetObjId=static::objectType."-".$this->_id;
	}

	/*******************************************************************************************
	 * RENVOIE LA VALEUR D'UNE PROPRIÉTÉ UNIQUEMENT SI ELLE EXISTE
	 *******************************************************************************************/
	function __get($propertyName)
	{
		if(isset($this->$propertyName))  {return $this->$propertyName;}
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
	 * VERIF : OBJECT EN COURS DE CRÉATION ET AVEC UN _ID==0 ?
	 *******************************************************************************************/
	public function isNew(){
		return (empty($this->_id));
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
			$MdlObjectContainer=(static::isFolder==true)  ?  get_class($this)  :  static::MdlObjectContainer;//l'objet courant est un dossiers || Récupère le modèle de l'objet parent
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
			$this->_affectations=$tmpAffectations=[];
			////	Objet existant : récupère les affectations en Bdd ("ORDER BY" pour le libellé des affectations dans le "contextMenu()")
			if($this->isNew()==false)	{$tmpAffectations=Db::getTab("SELECT * FROM ap_objectTarget WHERE objectType=".Db::format(static::objectType)." AND _idObject=".$this->_id." ORDER BY _idSpace, target");}
			////	Nouvel objet : initialise les affectations par défaut en type "string" !
			else{
				$tmpAffectations[]=["_idSpace"=>Ctrl::$curSpace->_id, "target"=>"spaceUsers",			 "accessRight"=>(static::isContainer()?"1.5":"1")];	//Espace courant : Lecture || Ecriture limité pour les conteneurs. Attention : "accessRight" est de type "string"!
				$tmpAffectations[]=["_idSpace"=>Ctrl::$curSpace->_id, "target"=>"U".Ctrl::$curUser->_id, "accessRight"=>"2"];								//Accès écriture pour l'user courant ..qui est aussi l'auteur
			}
			////	Formate les affectations
			foreach($tmpAffectations as $tmpAffect)
			{
				//Affectations détaillées :  "Espace Bidule > tous les utilisateurs"  /  "Groupe Bidule"  /  "Jean Dupont"
				$tmpAffect["targetType"]=$tmpAffect["target_id"]=$tmpAffect["label"]=null;
				if($tmpAffect["target"]=="spaceUsers")				{$tmpAffect["targetType"]="spaceUsers";		$tmpAffect["target_id"]=$tmpAffect["_idSpace"];					$tmpAffect["label"]=Ctrl::getObj("space",$tmpAffect["_idSpace"])->name." <img src='app/img/arrowRight.png'> ".strtolower(Txt::trad("SPACE_allUsers"));}
				elseif(preg_match("/^G/",$tmpAffect["target"]))		{$tmpAffect["targetType"]="group";			$tmpAffect["target_id"]=(int)substr($tmpAffect["target"],1);	$tmpAffect["label"]=Ctrl::getObj("userGroup",$tmpAffect["target_id"])->title;}
				elseif(preg_match("/^U/",$tmpAffect["target"]))		{$tmpAffect["targetType"]="user";			$tmpAffect["target_id"]=(int)substr($tmpAffect["target"],1);	$tmpAffect["label"]=Ctrl::getObj("user",$tmpAffect["target_id"])->getLabel();}
				//Affectation individuelle sur un autre espace que l'espace courant : ajoute le nom de l'espace
				if($tmpAffect["_idSpace"]!=Ctrl::$curSpace->_id && $tmpAffect["target"]!="spaceUsers")  {$tmpAffect["label"]=Ctrl::getObj("space",$tmpAffect["_idSpace"])->name." <img src='app/img/arrowRight.png'> ".$tmpAffect["label"];}
				//Ajoute l'affectation
				$targetKey=(int)$tmpAffect["_idSpace"]."_".$tmpAffect["target"];//concaténation des champs "_idSpace" et "target"
				$this->_affectations[$targetKey]=$tmpAffect;
			}
		}
		return $this->_affectations;
	}

	/*******************************************************************************************
	 * EDITE LES AFFECTATIONS ET DROITS D'ACCÈS DE L'OBJET (cf."menuEdit()"). Par défaut : accès en lecture à l'espace courant
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
				if(Ctrl::$curUser->isAdminGeneral())	{$sqlSpaces="(".$sqlSpaces." OR _idSpace is null)";}
				Db::query("DELETE FROM ap_objectTarget WHERE objectType=".Db::format(static::objectType)." AND _idObject=".$this->_id." AND ".$sqlSpaces);
			}
			//Ajoute les nouveaux droits d'accès : passés en paramètre / provenant du formulaire
			$newAccessRight=(Req::isParam("objectRight"))  ?  Req::getParam("objectRight")  :  $objectRightSpecific;
			foreach($newAccessRight as $tmpRight){
				$tmpRight=explode("_",$tmpRight);//exple :  "55_U33_2"  devient ["_idSpace"=>"5","target"=>"U3","accessRight"=>"2"]  correspond à droit "2" sur l'user "33" de l'espace "55"
				Db::query($sqlInsertBase." _idSpace=".Db::format($tmpRight[0]).", target=".Db::format($tmpRight[1]).", accessRight=".Db::format($tmpRight[2]));
			}
		}
	}

	/*******************************************************************************************
	 * RÉCUPÈRE LES DROITS D'ACCÈS SUR L'OBJET POUR L'USER COURANT :  element conteneur (dossier, agenda, sujet, etc)  OU  element basique (actualité, fichier, taches, etc)
	 *		3	[total]					element conteneur	-> modif/suppression du conteneur + modif/suppression des elements contenus (de premier niveau*)
	 *									element basique		-> modif/suppression
	 *		2	[ecriture]				element conteneur	-> lecture du conteneur + modif/suppression des elements contenus (de premier niveau*)
	 *									element basique		-> modif/suppression
	 *		1.5	[ecriture limité]		element conteneur	-> lecture du conteneur + modif/suppression du contenu qu'on a créé
	 *									element basique		-> -non disponible-
	 *		1	[lecture]				element conteneur	-> lecture du conteneur
	 *									element basique		-> lecture
	 *		(*) les éléments d'un dossier (fichier, taches, etc) héritent des droits d'accès de leur dossier conteneur
	****************************************************************************************** */
	public function accessRight()
	{
		//Init la mise en cache
		if($this->_accessRight===null)
		{
			//Init
			$this->_accessRight=0;
			////	DROIT D'ACCES TOTAL  =>  Admin général  ||  Auteur de l'objet  ||  Nouvel objet
			if(Ctrl::$curUser->isAdminGeneral() || $this->isAutor() || $this->createRight())  {$this->_accessRight=3;}
			////	DROIT D'ACCES EN LECTURE POUR UN ACCES EXTERNE
			elseif($this->md5IdControl())  {$this->_accessRight=1;}
			////	DROIT D'ACCES EN FONCTION DU CONTENEUR PARENT
			elseif($this->accessRightFromContainer())  {$this->_accessRight=$this->containerObj()->accessRight();}
			////	DROIT D'ACCES EN FONCTION DES AFFECTATIONS (cf. table "ap_objectTarget")
			elseif($this->hasAccessRight())
			{
				//Init la requete des droits d'acces
				$sqlAccessRight="objectType=".Db::format(static::objectType)." AND _idObject=".$this->_id." AND _idSpace=".Ctrl::$curSpace->_id;
				//Acces total si "isAdminSpace()" et objet affecté à l'espace (sauf pour les agendas perso : pas de privilège)
				if(Ctrl::$curUser->isAdminSpace() && Db::getVal("SELECT count(*) FROM ap_objectTarget WHERE ".$sqlAccessRight)>0 && static::objectType!="calendar" && $this->type!="user")  {$this->_accessRight=3;}
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
		//Controle si l'objet n'est pas en cour d'édition par un autre user (dans la dernières minute)
		$_idUserEditSameObj=Db::getVal("SELECT _idUser FROM ap_userLivecouter WHERE _idUser!=".Ctrl::$curUser->_id." AND editObjId=".Db::formatParam("targetObjId")." AND `date` > ".(time()-60));
		if($this->isNew()==false && !empty($_idUserEditSameObj))  {Ctrl::notify(Txt::trad("elemEditedByAnotherUser")." ".Ctrl::getObj("user",$_idUserEditSameObj)->getLabel()." !");}
	}

	/*******************************************************************************************
	 * LABEL DE L'OBJET (Nom/Titre/etc : cf. "$requiredFields" des objets)
	 *******************************************************************************************/
	public function getLabel()
	{
		//Label principal
		if(!empty($this->name))				{$tmpLabel=$this->name;}		//exple: nom de fichier
		elseif(!empty($this->title))		{$tmpLabel=$this->title;}		//exple: task
		elseif(!empty($this->description))	{$tmpLabel=$this->description;}	//exple: sujet/message du forum (sans titre)
		elseif(!empty($this->adress))		{$tmpLabel=$this->adress;}		//exple: link
		else								{$tmpLabel=null;}
		//Renvoi un résultat "clean" & sans tags
		return Txt::reduce(strip_tags($tmpLabel),40);
	}

	/*******************************************************************************************
	 * URL D'ACCÈS À L'OBJET  ($display: "vue"/"edit"/"delete"/default)
	****************************************************************************************** */
	public function getUrl($display=null)
	{
		if($this->isNew())  {return "?ctrl=".static::moduleName;}//Objet qui n'existe pas encore (ou plus)
		else
		{
			$urlBase="?ctrl=".static::moduleName."&targetObjId=";
			if($display=="vue")									{return $urlBase.$this->_targetObjId."&action=Vue".static::objectType;}							//Vue détaillée dans une lightbox (user/contact/task/calendarEvent)
			elseif($display=="edit" && static::isFolder==true)	{return "?ctrl=object&action=FolderEdit&targetObjId=".$this->_targetObjId;}						//Edite un dossier dans une lightbox via "actionFolderEdit()"
			elseif($display=="edit")							{return $urlBase.$this->_targetObjId."&action=".static::objectType."Edit";}						//Edite un objet dans une lightbox
			elseif($display=="delete")							{return "?ctrl=object&action=delete&targetObjects[".static::objectType."]=".$this->_id;}		//Url de suppression via "CtrlObject->delete()"
			elseif(static::isContainerContent())				{return $urlBase.$this->containerObj()->_targetObjId."&targetObjIdChild=".$this->_targetObjId;}	//Affichage par défaut d'un "content" dans son "Container" (file/contact/task/link/forumMessage). Surchargé pour les "calendarEvent" car on doit sélectionner l'agenda principal de l'evt
			else												{return $urlBase.$this->_targetObjId;}															//Affichage par défaut (Folder,news,forumSubject...)
		}
	}

	/*******************************************************************************************
	 * URL EXTERNE D'ACCÈS À L'OBJET (notif mail and co)
	 *******************************************************************************************/
	public function getUrlExternal()
	{
		//Cible la page de connexion > l'espace courant > puis le conteneur de l'objet (urlencodé)
		return Req::getCurUrl()."/?ctrl=offline&_idSpaceAccess=".Ctrl::$curSpace->_id."&targetObjUrl=".urlencode($this->getUrl());
	}

	/*******************************************************************************************
	 * URL D'EDITION D'UN NOUVEL OBJET
	 *******************************************************************************************/
	public static function getUrlNew()
	{
		$url=(static::isFolder==true)  ?  "?ctrl=object&action=FolderEdit"  :  "?ctrl=".static::moduleName."&action=".static::objectType."Edit";//Nouveau dossier / Nouvel objet
		if(!empty(Ctrl::$curContainer))  {$url.="&_idContainer=".Ctrl::$curContainer->_id;}//Ajoute l'id du container?
		return $url."&targetObjId=".static::objectType."-0";
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
		return (Req::isParam("md5Id") && $this->md5Id()==Req::getParam("md5Id"));
	}

	/*******************************************************************************************
	 * SUPPRESSION D'UN OBJET
	 *******************************************************************************************/
	public function delete()
	{
		if($this->deleteRight())
		{
			//Supprime les fichiers joints
			if(static::hasAttachedFiles==true){
				foreach($this->getAttachedFileList() as $tmpFile)  {$this->deleteAttachedFile($tmpFile);}
			}
			//Init la sélection de l'objet dans les tables
			$sqlSelectObject="WHERE objectType=".Db::format(static::objectType)." AND _idObject=".$this->_id;
			//Supprime les éventuels "usersComment" & "usersLike"
			if(static::hasUsersComment)	{Db::query("DELETE FROM ap_objectComment ".$sqlSelectObject);}
			if(static::hasUsersLike)	{Db::query("DELETE FROM ap_objectLike ".$sqlSelectObject);}
			//Ajoute le log de suppression, mais pas en dernier!
			Ctrl::addLog("delete",$this);
			//Supprime les droits d'accès, puis enfin l'objet lui-même!
			Db::query("DELETE FROM ap_objectTarget ".$sqlSelectObject);
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
		$newFolder=Ctrl::getObj(get_class($oldFolder), $newFolderId);
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
					foreach(Db::getTab("SELECT * FROM ap_objectTarget WHERE objectType='".$oldFolder::objectType."' AND _idObject=".$oldFolder->_id) as $oldFolderAccessRight){
						Db::query("INSERT INTO ap_objectTarget SET objectType=".Db::format(static::objectType).", _idObject=".$this->_id.", _idSpace=".$oldFolderAccessRight["_idSpace"].", target='".$oldFolderAccessRight["target"]."', accessRight='".$oldFolderAccessRight["accessRight"]."'");
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
	 * LISTE DES USERS AFFECTÉS À L'OBJET (surchargé)
	 *******************************************************************************************/
	public function affectedUserIds()
	{
		//Objet de référence pour les affectations
		$userIds=[];
		$refObject=($this->hasAccessRight())  ?  $this :  $this->containerObj();
		//Récupère les users de chaque affectation
		foreach($refObject->getAffectations() as $affect){
			if($affect["targetType"]=="spaceUsers")	{$userIds=array_merge($userIds, Ctrl::getObj("space",$affect["target_id"])->getUsers("idsTab"));}
			elseif($affect["targetType"]=="group")	{$userIds=array_merge($userIds, Ctrl::getObj("userGroup",$affect["target_id"])->userIds);}
			elseif($affect["targetType"]=="user")	{$userIds[]=$affect["target_id"];}
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
			if(Req::isParam("_idContainer"))	{$sqlProperties.=", _idContainer=".Db::formatParam("_idContainer");}
			if(static::hasShortcut==true)		{$sqlProperties.=", shortcut=".Db::formatParam("shortcut");}
			////	LANCE L'INSERT/UPDATE !!
			if($this->isNew())	{$_id=(int)Db::query("INSERT INTO ".static::dbTable." SET ".$sqlProperties, true);}
			else{
				Db::query("UPDATE ".static::dbTable." SET ".$sqlProperties." WHERE _id=".$this->_id);
				$_id=$this->_id;
			}
			////	Reload l'objet pour prendre en compte les nouvelles propriétés ("true" pour l'update du cache)
			$reloadedObj=Ctrl::getObj(static::objectType, $_id, true);
			////	Ajoute si besoin les droits d'accès et/ou les fichiers joints
			$reloadedObj->setAffectations();
			$reloadedObj->addAttachedFiles();
			////	Reload à nouveau l'objet (exple: si la description est maj avec insertion d'image)
			$reloadedObj=Ctrl::getObj(static::objectType, $_id, true);
			////	Ajoute aux Logs  &  Renvoie l'objet rechargé
			$logAction=(strtotime($reloadedObj->dateCrea)==time())  ?  "add"  :  "modif";
			Ctrl::addLog($logAction,$reloadedObj);
			return $reloadedObj;
		}
	}

	/*******************************************************************************************
	 * ENVOI D'UN MAIL DE NOTIFICATION (cf. "menuEdit")
	 *******************************************************************************************/
	public function sendMailNotif($specificLabel=null, $addDescription=null, $addFiles=null, $addUserIds=null)
	{
		//Notification demandé par l'auteur de l'objet  OU  Destinataires ajoutés automatiquement (Exple: notif automatique d'un nouveau message du forum)
		if(Req::isParam("notifMail") || !empty($addUserIds))
		{
			////	Sujet et Message : "Fichier créé par boby SMITH"
			$tradCreaModif=($this->isNew() || $this->isNewlyCreated())  ?  "MAIL_elemCreatedBy"  :  "MAIL_elemModifiedBy";//"-OBJLABEL- créé par" / "-OBJLABEL- modifié par"
			$subject=ucfirst($this->tradObject($tradCreaModif))." ".Ctrl::$curUser->getLabel();
			////	Message
			//Label principal de l'objet : spécifique
			if(!empty($specificLabel))		{$objContent="<b>".$specificLabel."</b>";}	//Nom des fichiers uploadés, etc 
			elseif(!empty($this->title))	{$objContent="<b>".$this->title."</b>";}	//forum subject, task, etc
			elseif(!empty($this->name))		{$objContent="<b>".$this->name."</b>";}		//folder name, etc
			else							{$objContent=null;}
			//Ajoute si besoin la description
			if(!empty($this->description))	{$objContent.="<br>".$this->description;}
			if(!empty($addDescription))		{$objContent.="<br>".$addDescription;}
			$objContent=Txt::reduce($objContent,8000);											//Limite la taille du texte (tester avec 5000 carctères + une mise en page, car "reduce()" lance aussi un "strip_tag()")
			$objContent=str_replace(PATH_DATAS, Req::getCurUrl()."/".PATH_DATAS, $objContent);	//Remplace si besoin les chemins relatifs dans le label de l'objet
			$objContentStyle="display:inline-block;background-color:#f5f5f5;color:#333;border:1px solid #bbb;border-radius:3px;padding:15px;";//Style du corps du message
			$message="<br>".$subject." :<br><br><div style=\"".$objContentStyle."\">".$objContent."</div><br><br><a href=\"".$this->getUrlExternal()."\" target='_blank'>".Txt::trad("MAIL_elemAccessLink")."</a>";//Finalise le message (sur une seule ligne!!)
			////	Users à destination de la notif : destinataires spécifiques OU users affectées à l'objet (tous!)
			$notifUsersIds=[];
			if(Req::isParam("notifMail"))	{$notifUsersIds=(Req::isParam("notifMailUsers"))  ?  Req::getParam("notifMailUsers")  :  $this->affectedUserIds();}
			////	Ajoute si besoin les destinataires passés en paramètres
			if(!empty($addUserIds)){
				if(Req::isParam("notifMail")==false)  {$addedOptions="noNotify";}//Par défaut, on n'affiche pas si le mail a bien été envoyé ou pas (cf. "notify()")
				$notifUsersIds=array_unique(array_merge($notifUsersIds,$addUserIds));
			}
			////	Ajoute si besoin les images jointes à l'objet et intégré dans sa description (exple: images intégrées à une actualité)
			if(static::htmlEditorField!=null)
			{
				if($addFiles==null)  {$addFiles=[];}
				foreach($this->getAttachedFileList() as $tmpFile)
				{
					//Vérifie s'il s'agit d'une image et si son url est présente dans le message (exple: "?ctrl=object&action=displayAttachedFile...")
					if(File::isType("imageBrowser",$tmpFile["name"]) && stristr($message,$tmpFile["url"])){
						$tmpFileCid="attachedFile".$tmpFile["_id"];								//"cid" de l'image dans l'email
						$message=str_replace($tmpFile["url"], "cid:".$tmpFileCid, $message);	//Remplace l'url de l'image par le "cid" de l'image ajouté en pièce jointe
						$addFiles[]=["path"=>$tmpFile["path"], "cid"=>$tmpFileCid];				//Ajoute la pièce jointe du mail avec le "cid" correspondant
					}
				}
			}
			////	Envoi du message
			if(!empty($notifUsersIds))
			{
				$options="objectNotif";
				if(Req::getParam("receptionNotif"))	{$options.="receptionNotif,";}
				if(Req::getParam("addReplyTo"))		{$options.="addReplyTo,";}
				if(Req::getParam("hideRecipients"))	{$options.="hideRecipients,";}
				if(!empty($addedOptions))			{$options.=",".$addedOptions;}
				Tool::sendMail($notifUsersIds, $subject, $message, $options, $addFiles);
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
		$return=(!empty($conditions))  ?  "(".implode(' AND ',$conditions).")"  :  $keyId." IS NULL";
		////	Selection de "plugin" : selectionne les objets des conteneurs auquel on a acces (dossiers/sujets..)
		if($containerObj==null && static::isContainerContent()){
			$MdlObjectContainer=static::MdlObjectContainer;
			$return="(".$return." OR ".$MdlObjectContainer::sqlDisplay(null,"_idContainer").")";//Appel récursif avec "_idContainer" comme $keyId
		}
		////	Renvoie le résultat
		return $return;
	}

	/*******************************************************************************************
	 * STATIC SQL : RECUPÈRE LES OBJETS D'UN "PLUGIN" ($params["type"] : "dashboard"/"shortcut"/"search")
	 *******************************************************************************************/
	public static function getPlugins($params)
	{
		$returnObjects=[];
		if(isset($params["type"]))
		{
			//// Recupere les elements en fonction de leurs droits d'accès
			$sqlDisplay=static::sqlDisplay();
			$returnObjects=Db::getObjTab(static::objectType, "SELECT * FROM ".static::dbTable." WHERE ".static::sqlPlugins($params)." AND ".$sqlDisplay." ORDER BY dateCrea desc");
			//// Ajoute si besoin les plugins courant du Dashboard : ayant lieu dans la periode affichée (idem "evtList()")
			if($params["type"]=="dashboard" && (static::objectType=="calendarEvent" || static::objectType=="task")){
				$sqlTimeSlot="(  (dateBegin between ".Db::format($params["dateTimeBegin"])." and ".Db::format($params["dateTimeEnd"]).")  OR  (dateEnd between ".Db::format($params["dateTimeBegin"])." and ".Db::format($params["dateTimeEnd"]).")  OR  (dateBegin < ".Db::format($params["dateTimeBegin"])." and dateEnd > ".Db::format($params["dateTimeEnd"]).")  )";
				$currentObjs=Db::getObjTab(static::objectType, "SELECT * FROM ".static::dbTable." WHERE ".$sqlTimeSlot." AND ".$sqlDisplay." ORDER BY dateCrea desc");
				foreach($currentObjs as $tmpObj)   {$tmpObj->pluginIsCurrent=true;  $returnObjects[$tmpObj->_id]=$tmpObj;}//ajoute/remplace l'objet du tableau
			}
		}
		return $returnObjects;
	}

	/*******************************************************************************************
	 * STATIC SQL : SELECTIONNE LES OBJETS EN FONCTION DU TYPE DE PLUGIN
	 * $params["type"] => "dashboard": cree dans la periode selectionné / "shortcut": ayant un raccourci / "search": issus d'une recherche
	 *******************************************************************************************/
	public static function sqlPlugins($params)
	{
		if($params["type"]=="dashboard")	{return "dateCrea BETWEEN ".Db::format($params["dateTimeBegin"])." AND ".Db::format($params["dateTimeEnd"]);}
		elseif($params["type"]=="shortcut")	{return "shortcut=1";}
		elseif($params["type"]=="search")
		{
			$return=null;
			//// Champs concernés par la recherche : tous les champs de l'objet ou uniquement ceux demandés
			$objSearchFields=(!empty($params["searchFields"]))  ?  array_intersect(static::$searchFields,$params["searchFields"])  :  static::$searchFields;
			//// Prépare les termes de la recherche
			$searchText=Txt::clean($params["searchText"]);
			//// Recherche l'expression exacte
			if($params["searchMode"]=="exactPhrase"){
				//Formate chaque recherche d'expression : "likesearch" pour les "%" && "editor" pour ne pas avoir de "htmlspecialchars()" perturbant le "htmlentities()"
				foreach($objSearchFields as $tmpField)  {$return.="`".$tmpField."` LIKE ".Db::format($searchText)." OR `".$tmpField."` LIKE ".Db::format(htmlentities($searchText),"likeSearch,editor")." OR ";}
			}
			//// Recherche n'importe quel mot spécifié OU Recherche tous les mots spécifiés
			else
			{
				//Récupère les mots cles de la recherche (>=3 carac)
				$searchWords=[];
				foreach(explode(" ",$searchText) as $tmpWord){
					if(strlen($tmpWord)>=3){
						$searchWords[]=$tmpWord;														//Recherche le texte brut
						if($params["searchMode"]=="anyWord")  {$searchWords[]=htmlentities($tmpWord);}	//Recherche aussi les caractères html accentués (cf. tinyMce avec "&eacute;", "&egrave;"..)
					}
				}
				//Opérateur de liaison (garder les espaces) : "n'importe quel mot" || "Tous les mots"
				$operator=($params["searchMode"]=="anyWord")  ?  "OR"  :  "AND";
				//Pour chaque champ de l'objet, on ajoute une sélection SQL pour chaque mot recherché
				foreach($objSearchFields as $tmpField){
					$sqlField=null;
					foreach($searchWords as $tmpWord)  {$sqlField.="`".$tmpField."` LIKE ".Db::format($tmpWord,"likeSearch,editor")." ".$operator;}	//"likesearch" pour les "%" && "editor" pour ne pas avoir de "htmlspecialchars()" perturbant le "htmlentities()" ci-dessus
					$return.="(".trim($sqlField,$operator).") OR ";																					//supprime le dernier $operator entre chaque mot recherché && ajoute un "OR" pour la recherche sur un autre champ
				}
			}
			//// Sélection de base : supprime le dernier opérateur "OR" entre chaque champ de recherche (cf "rtrim()")
			$return="(".trim($return,"OR ").")";
			//// Filtre en fonction de la date de creation
			if($params["creationDate"]!="all"){
				$nbDays=["day"=>1,"week"=>7,"month"=>31,"year"=>365];
				$timeCreationDate=time() - (86400 * $nbDays[$params["creationDate"]]);
				$return.=" AND dateCrea >= '".date("Y-m-d 00:00",$timeCreationDate)."'";
			}
			//// Retourne le résultat
			return $return;
		}
	}

	/*******************************************************************************************
	 * AFFICHE L'AUTEUR DE L' OBJET
	 *******************************************************************************************/
	public function autorLabel($getCreator=true, $tradAutor=false)
	{
		$labelAutor=($tradAutor==true)  ?  Txt::trad("autor")." : "  :  null;
		if(!empty($this->guest))			{return $labelAutor.$this->guest." (".Txt::trad("guest").")";}				//Invité
		elseif($getCreator==true)			{return $labelAutor.Ctrl::getObj("user",$this->_idUser)->getLabel();}		//Créateur de l'objet
		elseif(!empty($this->_idUserModif))	{return $labelAutor.Ctrl::getObj("user",$this->_idUserModif)->getLabel();}	//Auteur de dernière modif
	}

	/*******************************************************************************************
	 * AFFICHE LA DATE DE CRÉATION OU MODIFICATION
	 *******************************************************************************************/
	public function dateLabel($getDateCrea=true, $format="normal")
	{
		if($getDateCrea==true)	{return Txt::dateLabel($this->dateCrea,$format);}
		else					{return Txt::dateLabel($this->dateModif,$format);}
	}

	/*******************************************************************************************
	 * AFFICHE L'AUTEUR ET LA DATE AU FORMAT "OBJLINES"
	 *******************************************************************************************/
	public function autorDateLabel($withAutorIcon=false)
	{
		$autorIcon=($withAutorIcon==true)  ?  Ctrl::getObj("user",$this->_idUser)->getImg(true,true)  :  null;
		return $autorIcon." ".$this->autorLabel()."<div class='objAutorDateCrea'>".$this->dateLabel(true,"full")."</div>";
	}

	/*******************************************************************************************
	 * TRADUCTION AVEC CHANGEMENT DES LIBELLES -OBJLABEL- ET -OBJCONTENT- PAR CEUX DES OBJETS CONCERNÉS
	 *******************************************************************************************/
	public function tradObject($tradKey)
	{
		//// Trad de base
		$trad=Txt::trad($tradKey);
		//// Trad de l'objet courant
		if(Txt::isTrad("OBJECT".static::objectType)){
			$objLabel=Txt::trad("OBJECT".static::objectType);
			$trad=str_replace("-OBJLABEL-", $objLabel, $trad);//Remplace -OBJLABEL-
			if(static::isContainerContent())  {$trad=str_replace("-OBJCONTENT-", $objLabel, $trad);}//Remplace -OBJCONTENT-
		}
		//// Trad des objets "content" d'un "Container" : remplace -OBJCONTENT-
		if(static::isContainer()){
			$MdlObjectContent=static::MdlObjectContent;
			if(Txt::isTrad("OBJECT".$MdlObjectContent::objectType))  {$trad=str_replace("-OBJCONTENT-", Txt::trad("OBJECT".$MdlObjectContent::objectType), $trad);}
		}
		//// Retourne la trad
		return $trad;
	}
}