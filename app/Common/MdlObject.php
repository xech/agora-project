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
	//Utilise les menus et attributs
	use MdlObjectMenus, MdlObjectAttributes;

	//Propriétés de base
	const moduleName=null;
	const objectType=null;
	const dbTable=null;
	//Propriétés de dépendance
	const hasAccessRight=false;
	const isFolder=false;
	const isFolderContent=false;
	const MdlObjectContent=null;
	const MdlObjectContainer=null;
	//Propriétés d'affichage et d'édition (d'IHM)
	const isSelectable=false;
	const hasShortcut=false;
	const hasNotifMail=false;
	const hasAttachedFiles=false;
	const hasUsersLike=false;
	const hasUsersComment=false;
	const htmlEditorField=null;					//Champ "description" le plus souvent
	public static $displayModeOptions=array();	//Type d'affichage : ligne/block le plus souvent
	public static $requiredFields=array();		//Champs obligatoires pour valider l'édition d'un objet
	public static $searchFields=array();		//Champs de recherche
	public static $sortFields=array();			//Champs/Options de tri des résulats
	//Valeurs mises en cache
	private $_accessRight=null;
	private $_containerObj=null;
	private $_affectations=null;
	protected static $_sqlTargets=null;

	/*
	 * Init
	 */
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

	/*
	 * Renvoie la valeur d'une propriété uniquement si elle existe
	 */
	function __get($propertyName)
	{
		if(isset($this->$propertyName))  {return $this->$propertyName;}
	}

	/*
	 * VERIF si l'objet est un conteneur (calendar/forumSubjet & Folders)
	 */
	public static function isContainer(){
		return (static::MdlObjectContent!==null);
	}

	/*
	 * VERIF si l'objet se trouve dans un conteneur.. mais n'est pas un conteneur (file/contact/task/link/forumMessage/calendarEvent ...mais pas les Folders)
	 */
	public static function isContainerContent(){
		return (static::MdlObjectContainer!==null);
	}

	/*
	 * VERIF si l'objet se trouve dans une arborescence (file/contact/task/link & Folders)
	 */
	public static function isInArbo(){
		return (static::isFolderContent==true || static::isFolder==true);
	}

	/*
	 * VERIF si l'objet a ses propres droits d'accès : n'est pas dans un conteneur (news, polls, etc) OU se trouve dans le dossier racine
	 */
	public function isIndependant(){
		return (static::MdlObjectContainer==null || (static::isFolderContent==true && $this->_idContainer==1));
	}

	/*
	 * VERIF si l'objet est un dossier racine
	 */
	public function isRootFolder(){
		return (static::isFolder==true && $this->_id==1);
	}

	/*
	 * VERIF si l'user courant est l'auteur de l'objet 
	 */
	public function isAutor(){
		return (Ctrl::$curUser->isUser() && $this->_idUser==Ctrl::$curUser->_id);
	}

	/*
	 * VERIF si c'est un object en cour de création (_id=0)
	 */
	public function isNew(){
		return (empty($this->_id));
	}

	/*
	 * VERIF si c'est un objet nouvellement créé (créé à l'instant : utilisé pour les mails de notif and co)
	 */
	public function isNewlyCreated(){
		return ($this->isNew()==false && time()-strtotime($this->dateCrea)<2);
	}

	/*
	 * VERIF si l'objet à des droits d'accès qui dépendent d'un conteneur (pas pour les "calendarEvent" car affectés à plusieurs agendas)
	 */
	public function accessRightFromContainer()
	{
		return ($this->isIndependant()==false && static::objectType!="calendarEvent");
	}

	/*
	 * Recupère l'objet conteneur de l'objet courant (file/contact/task/link/subjectMessage & Folders). Surchargé pour les "calendarEvent" car affectés à plusieurs agendas.
	 */
	public function containerObj()
	{
		if($this->_containerObj===null && !empty($this->_idContainer)){
			$MdlObjectContainer=(static::isFolder==true)  ?  get_class($this)  :  static::MdlObjectContainer;//Modèle de l'objet parent (idem si l'objet courant est un dossier..)
			$this->_containerObj=Ctrl::getObj($MdlObjectContainer,$this->_idContainer);
		}
		return $this->_containerObj;
	}

	/*
	 * Label de l'objet : Nom/Titre/etc  (cf. "$requiredFields" des objets)
	 */
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

	/*
	 * Url d'accès à l'objet : "vue" de l'objet (lightbox) / Edition de l'objet / suppression de l'objet / conteneur de l'objet / url du module ($urlBase)
	 */
	public function getUrl($display=null)
	{
		if($this->isNew())  {return "?ctrl=".static::moduleName;}//Objet n'existe pas encore ou n'existe plus
		else
		{
			$urlBase="?ctrl=".static::moduleName."&targetObjId=";
			if($display=="vue")									{return $urlBase.$this->_targetObjId."&action=Vue".static::objectType;}							//Affiche un objet dans une lightbox (user/contact/task/calendarEvent)
			elseif($display=="edit" && static::isFolder==true)	{return "?ctrl=object&action=FolderEdit&targetObjId=".$this->_targetObjId;}						//Edite un dossier dans la lightbox (Folders)
			elseif($display=="edit")							{return $urlBase.$this->_targetObjId."&action=".static::objectType."Edit";}						//Edite un objet dans la lightbox (tout types d'objets)
			elseif($display=="delete")							{return "?ctrl=object&action=delete&targetObjects[".static::objectType."]=".$this->_id;}		//Suppression d'un objet via "?ctrl=object&action=delete" (tout types d'objets)
			elseif($display=="container" && (static::isFolder==true || static::isContainerContent()))  {return $urlBase.$this->containerObj()->_targetObjId."&targetObjIdChild=".$this->_targetObjId;}//Conteneur de l'objet (dossier OU file/contact/task/link/forumMessage). Surchargé pour les "calendarEvent" (affectés à plusieurs agendas) et pour les "forumSubject" (affectés à un thème)
			else												{return $urlBase.$this->_targetObjId;}															//Cible directement l'objet (forumSubject & Folders)
		}
	}

	/*
	 * Url externe d'accès à l'objet (mail and co)
	 */
	public function getUrlExternal()
	{
		//Cible la page de connexion > l'espace courant > puis le conteneur de l'objet (urlencodé)
		return Req::getSpaceUrl()."/?ctrl=offline&_idSpaceAccess=".Ctrl::$curSpace->_id."&targetObjUrl=".urlencode($this->getUrl("container"));
	}

	/*
	 * Url d'edition d'un nouvel objet
	 */
	public static function getUrlNew()
	{
		$url=(static::isFolder==true)  ?  "?ctrl=object&action=FolderEdit"  :  "?ctrl=".static::moduleName."&action=".static::objectType."Edit";//Nouveau dossier / Nouvel objet
		if(!empty(Ctrl::$curContainer))  {$url.="&_idContainer=".Ctrl::$curContainer->_id;}//Ajoute l'id du container?
		return $url."&targetObjId=".static::objectType."-0";
	}

	/*
	 * Identifiant "md5()" de l'objet pour un accès externe
	 */
	public function md5Id()
	{
		return md5($this->_id.$this->dateCrea.$this->_idUser);
	}
	
	/*
	 * Controle l'identifiant md5 passé en parametre
	 */
	public function md5IdControl()
	{
		return (Req::isParam("md5Id") && $this->md5Id()==Req::getParam("md5Id"));
	}

	/*
	 * Liste les affectations de l'objet (Espaces/groupes/users)
	 */
	public function getAffectations()
	{
		if($this->_affectations===null)
		{
			//Init
			$this->_affectations=$tmpAffectations=array();
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
				//Affectations détaillées :  "Espace Bidule (tous les utilisateurs)"  /  "Groupe Bidule"  /  "Jean Dupont"
				$tmpAffect["targetType"]=$tmpAffect["target_id"]=$tmpAffect["label"]=null;
				if($tmpAffect["target"]=="spaceUsers")			{$tmpAffect["targetType"]="spaceUsers";		$tmpAffect["target_id"]=$tmpAffect["_idSpace"];					$tmpAffect["label"]=Ctrl::getObj("space",$tmpAffect["target_id"])->name." : ".strtolower(Txt::trad("SPACE_allUsers"));}
				elseif(substr($tmpAffect["target"],0,1)=="G")	{$tmpAffect["targetType"]="group";			$tmpAffect["target_id"]=(int)substr($tmpAffect["target"],1);	$tmpAffect["label"]=Ctrl::getObj("userGroup",$tmpAffect["target_id"])->title;}
				elseif(substr($tmpAffect["target"],0,1)=="U")	{$tmpAffect["targetType"]="user";			$tmpAffect["target_id"]=(int)substr($tmpAffect["target"],1);	$tmpAffect["label"]=Ctrl::getObj("user",$tmpAffect["target_id"])->getLabel();}
				//Ajoute l'affectation
				$targetKey=(int)$tmpAffect["_idSpace"]."_".$tmpAffect["target"];//concaténation des champs "_idSpace" et "target"
				$this->_affectations[$targetKey]=$tmpAffect;
			}
		}
		return $this->_affectations;
	}

	/*
	 * Affectation des droits d'accès provenant (cf."menuEdit()"). Par défaut : accès en lecture à l'espace courant
	 */
	public function setAffectations($objectRightSpecific=null)
	{
		////	Object indépendant  &&  "objectRight" spécifié OU droit d'accès spécifiques
		if($this->isIndependant()  &&  (Req::isParam("objectRight") || !empty($objectRightSpecific)))
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
				$tmpRight=explode("_",$tmpRight);//exple :  "5_U3_2"  devient ["_idSpace"=>"5","target"=>"U3","accessRight"=>"2"]  correspond à droit "2" sur l'user "3" de l'espace "5"
				Db::query($sqlInsertBase." _idSpace=".Db::format($tmpRight[0]).", target=".Db::format($tmpRight[1]).", accessRight=".Db::format($tmpRight[2]));
			}
		}
	}

	/*
	 * Récupère les droits d'accès de l'user courant sur l'objet :  element conteneur (dossier, agenda, sujet, etc)  OU  element basique (actualité, fichier, taches, etc)
	 *		3	[total]					element conteneur	-> modif/suppression du conteneur + modif/suppression des elements contenus (de premier niveau*)
	 *									element basique		-> modif/suppression
	 *		2	[ecriture]				element conteneur	-> lecture du conteneur + modif/suppression des elements contenus (de premier niveau*)
	 *									element basique		-> modif/suppression
	 *		1.5	[ecriture limité]		element conteneur	-> lecture du conteneur + modif/suppression du contenu qu'on a créé
	 *									element basique		-> -non disponible-
	 *		1	[lecture]				element conteneur	-> lecture du conteneur
	 *									element basique		-> lecture
	 *		(*) les éléments d'un dossier (fichier, taches, etc) héritent des droits d'accès de leur dossier conteneur
	 */
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
			elseif(static::hasAccessRight==true)
			{
				//Init la requete des droits d'acces
				$sqlAccessRight="objectType=".Db::format(static::objectType)." AND _idObject=".$this->_id." AND _idSpace=".Ctrl::$curSpace->_id;
				//Acces total si "isAdminSpace()" et objet affecté à l'espace (sauf pour les agendas perso : pas de privilège)
				if(Ctrl::$curUser->isAdminSpace() && Db::getVal("SELECT count(*) FROM ap_objectTarget WHERE ".$sqlAccessRight)>0 && static::objectType!="calendar" && $this->type!="user")  {$this->_accessRight=3;}
				//Acces en fonction des affectations à l'user => recupere le droit le plus important !
				else{
					$this->_accessRight=Db::getVal("SELECT max(accessRight) FROM ap_objectTarget WHERE ".$sqlAccessRight." AND target IN (".static::sqlTargets().")");
					if(Ctrl::$curUser->isUser()==false && $this->_accessRight>1)  {$this->_accessRight=1;}//Guests : droit de lecture au maximum !
				}
			}
		}
		//Retourne le droit d'accès en valeur flottante (cf. droit "1.5" en "ecriture limité")
		return (float)$this->_accessRight;
	}

	/*
	 * Droit de créer un nouvel objet
	 */
	public function createRight()
	{
		if($this->_id==0){
			if($this->isIndependant())					{return true;}										//"true" si l'objet a ses propres droits d'accès
			elseif($this->accessRightFromContainer())	{return $this->containerObj()->editContentRight();}	//Sinon en fonction du conteneur parent (pas pour les "calendarEvent" car affectés à plusieurs agendas)
		}
	}

	/*
	 * Droit de lecture sur un objet
	 */
	public function readRight()
	{
		return ($this->accessRight()>0);
	}

	/*
	 * Conteneur : Droit d'ajouter du contenu OU d'éditer le contenu qu'on a créé (accessRight=1.5) OU d'éditer tout le contenu (accessRight>=2)
	 */
	public function editContentRight()
	{
		return (static::isContainer() && $this->accessRight()>1);
	}

	/*
	 * Conteneur : Droit d'éditer un conteneur et son contenu (accessRight >= 2)
	 */
	public function editFullContentRight()
	{
		return (static::isContainer() && $this->accessRight()>=2);
	}

	/*
	 * Droit d'édition d'un objet :  accessRight==3  OU  accessRight==2 pour les objets qui ne sont pas des conteneurs (pas de "isIndependant()" car un conteneur est toujours "Independant"!)
	 */
	public function editRight()
	{
		return ($this->accessRight()==3 || (static::isContainer()==false && $this->accessRight()==2));
	}

	/*
	 * Droit de suppression d'un objet
	 */
	public function deleteRight()
	{
		return ($this->editRight());
	}

	/*
	 * Droit complet sur l'objet
	 */
	public function fullRight()
	{
		return ($this->accessRight()==3);
	}

	/*
	 * Controle si on peut lire l'objet (et s'il existe encore) : sinon renvoie une erreur
	 */
	public function controlRead()
	{
		if($this->accessRight()==0 || empty($this->_id))  {Ctrl::noAccessExit();}
	}

	/*
	 * Controle si on peut éditer l'objet
	 */
	public function controlEdit()
	{
		//Controle le droit d'accès en écriture
		if($this->editRight()==false)  {Ctrl::noAccessExit();}
		//Controle si l'objet n'est pas en cour d'édition par un autre user (dans la dernières minute)
		$_idUserEditSameObj=Db::getVal("SELECT _idUser FROM ap_userLivecouter WHERE _idUser!=".Ctrl::$curUser->_id." AND editObjId=".Db::formatParam("targetObjId")." AND date>".(time()-60));
		if($this->isNew()==false && !empty($_idUserEditSameObj))  {Ctrl::addNotif(Txt::trad("warning")." !<br>".Txt::trad("elemEditedByAnotherUser")." ".Ctrl::getObj("user",$_idUserEditSameObj)->getLabel());}
	}

	/*
	 * Suppression d'un objet
	 */
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

	/*
	 * Déplace un objet (dossier?) dans un autre dossier
	 */
	public function folderMove($newFolderId)
	{
		////	Ancien et nouveau dossier
		$oldFolder=$this->containerObj();
		$newFolder=Ctrl::getObj(get_class($oldFolder), $newFolderId);
		////	Objet pas dans une arbo? Droit d'accès pas ok? || dossier de destination inaccessible sur le disque? || Déplace un dossier à l'interieur de lui même?
		if(static::isInArbo()==false || $this->accessRight()<2 || $newFolder->accessRight()<2 || (static::objectType=="fileFolder" && is_dir($newFolder->folderPath("real"))==false))	{Ctrl::addNotif(Txt::trad("inaccessibleElem")." : ".$this->name.$this->title);}
		elseif(static::isFolder && $this->isInFolderTree($newFolderId))																													{Ctrl::addNotif(Txt::trad("NOTIF_folderMove")." : ".$this->name);}
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

	/*
	 * Liste des users affectés à l'objet (surchargé)
	 */
	public function affectedUserIds()
	{
		//Objet de référence pour les affectations
		$userIds=[];
		$refObject=($this->isIndependant())  ?  $this :  $this->containerObj();
		//Récupère les users de chaque affectation
		foreach($refObject->getAffectations() as $affect){
			if($affect["targetType"]=="spaceUsers")	{$userIds=array_merge($userIds, Ctrl::getObj("space",$affect["target_id"])->getUsers("idsTab"));}
			elseif($affect["targetType"]=="group")	{$userIds=array_merge($userIds, Ctrl::getObj("userGroup",$affect["target_id"])->userIds);}
			elseif($affect["targetType"]=="user")	{$userIds[]=$affect["target_id"];}
		}
		//Renvoi la liste des users
		return array_unique($userIds);
	}

	/*
	 * Ajout/Modif d'objet
	 */
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
			////	Invité : ajoute le champs "guest" (nom/surnom) && affiche une notif "Votre proposition sera examiné[..]"
			if(Ctrl::$curUser->isUser()==false && Req::isParam("guest")){
				$sqlProperties.=", guest=".Db::formatParam("guest");
				Ctrl::addNotif("EDIT_guestElementRegistered");
			}
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

	/*
	 * Envoi d'un mail de notification (cf. "menuEdit")
	 */
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
			if(!empty($specificLabel))		{$objContent="<b>".$specificLabel."</b>";}//Nom des fichiers uploadés, etc 
			elseif(!empty($this->title))	{$objContent="<b>".$this->title."</b>";}//forum subject, task, etc
			elseif(!empty($this->name))		{$objContent="<b>".$this->name."</b>";}//folder name, etc
			else							{$objContent=null;}
			//Ajoute si besoin la description
			if(!empty($this->description))	{$objContent.="<br>".$this->description;}
			if(!empty($addDescription))		{$objContent.="<br>".$addDescription;}
			$objContent=Txt::reduce($objContent,3000);//limite la taille de la description (cf. Actualité and co, avec "strip tag" pour éviter d'arriver en spam)
			$objContent=str_replace(PATH_DATAS, Req::getSpaceUrl()."/".PATH_DATAS, $objContent);//Remplace si besoin les chemins relatifs dans le label de l'objet
			$objContentStyle="display:inline-block; background-color:#fafafa; color:#333; border:1px solid #bbb; border-radius:2px; padding:15px;";//Style du corps du message
			$message="<br>".$subject." :<br><br><div style=\"".$objContentStyle."\">".$objContent."</div><br><br><a href=\"".$this->getUrlExternal()."\" target='_blank'>".Txt::trad("MAIL_elemAccessLink")."</a>";//Finalise le message (sur une seule ligne!!)
			////	Users à destination de la notif : destinataires spécifiques OU users affectées à l'objet
			$notifUsersIds=[];
			if(Req::isParam("notifMail"))	{$notifUsersIds=(Req::isParam("notifMailUsers"))  ?  Req::getParam("notifMailUsers")  :  $this->affectedUserIds();}
			////	Ajoute si besoin des destinataires
			if(!empty($addUserIds)){
				if(Req::isParam("notifMail")==false)  {$addedOptions="noSendNotif";}//Pas de demande explicite de l'user courant : on affiche pas de rapport d'envoi
				$notifUsersIds=array_unique(array_merge($notifUsersIds,$addUserIds));
			}
			////	Ajoute si besoin les images jointes à l'objet et intégré dans sa description (exple: images intégrées à une actualité)
			if(static::htmlEditorField!=null)
			{
				if($addFiles==null)  {$addFiles=[];}
				foreach(self::getAttachedFileList() as $tmpFile)
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
				$options="objectEditNotif,addLogoFooter";
				if(Req::isParam("hideRecipients"))	{$options.=",hideRecipients";}
				if(Req::isParam("receptionNotif"))	{$options.=",receptionNotif";}
				if(!empty($addedOptions))			{$options.=",".$addedOptions;}
				Tool::sendMail($notifUsersIds, $subject, $message, $options, $addFiles);
			}
		}
	}

	/*
	 * STATIC SQL : Selection d'objets en fonction des droits d'accès
	 * "targets" (exple) : "spaceUsers" / "U1" / "G1"
	 */
	public static function sqlTargets()
	{
		if(static::$_sqlTargets===null)
		{
			//Elements de l'espace pour les users et invités/guests
			static::$_sqlTargets="'spaceUsers'";
			//Ajoute les éléments affectés à l'user courant et aux groupes de l'user courant
			if(Ctrl::$curUser->isUser()){
				static::$_sqlTargets.=",'U".Ctrl::$curUser->_id."'";
				foreach(MdlUserGroup::getGroups(Ctrl::$curSpace,Ctrl::$curUser) as $tmpGroup)  {static::$_sqlTargets.=",'G".$tmpGroup->_id."'";}
			}
		}
		return static::$_sqlTargets;
	}

	/*
	 * STATIC SQL : Selection d'objets à afficher en fonction => des droits d'accès (curSpace/curUser)  && du dossier ou conteneur courant  && de $_SESSION["displayAdmin"]
	 */
	public static function sqlDisplayedObjects($containerObj=null, $keyId="_id")
	{
		////	INITIALISE LA SELECTION : AJOUTE SI BESOIN UN CONTENEUR (dossier/sujet/etc)
		$sqlConditions=(is_object($containerObj))  ?  ["_idContainer=".$containerObj->_id]  :  [];
		////	SELECTION D'OBJETS EN FONCTION DE LEUR DROIT D'ACCES :  objets indépendants (pas dans un conteneur)   ||  objets dans toute une arborescence (sélection "plugin" via "empty($containerObj)")  ||  objets dans le dossier racine
		if(static::isContainerContent()==false  ||  (static::isFolderContent && empty($containerObj))  ||  (static::isFolderContent && $containerObj->isRootFolder())){
			//"displayAdmin" à "true" : on récupère tous les objets affectés à l'espace, sans distinction
			$sqlTargets=(empty($_SESSION["displayAdmin"]))  ?  "and target in (".static::sqlTargets().")"  :  null;
			//Sélectionne les objets en fonction de leur droit d'accès dans la table "ap_objectTarget"
			$sqlConditions[]=$keyId."  IN  (select _idObject as ".$keyId." from ap_objectTarget where objectType=".Db::format(static::objectType)." and _idSpace=".Ctrl::$curSpace->_id." ".$sqlTargets.")";
		}
		////	FINALISE LA SÉLECTION
		if(count($sqlConditions)==1)		{$sqlReturned=$sqlConditions[0];}						//Une seule condition
		elseif(count($sqlConditions)>=2)	{$sqlReturned="(".implode(" AND ",$sqlConditions).")";}	//Plusieurs conditions : on les fusionne avec "AND"
		else								{$sqlReturned=$keyId." is null";}						//Aucune condition : sélection par défaut pour ne rien retourner
		////	SELECTION "plugin" : RECUPERE LES OBJETS DES CONTENEURS AUQUEL ON A ACCES (dossiers/sujets/etc)
		if(static::isContainerContent() && empty($containerObj)){
			$MdlObjectContainer=static::MdlObjectContainer;
			$sqlReturned="(".$sqlReturned." OR ".$MdlObjectContainer::sqlDisplayedObjects(null,"_idContainer").")";//Appel récursif avec "_idContainer" comme $keyId
		}
		////	RENVOI LE RESULTAT
		return $sqlReturned;
	}

	/*
	 * STATIC SQL : Recupère les objets pour un affichage "plugin" ("dashboard"/"shortcut"/"search")
	 */
	public static function getPluginObjects($pluginParams)
	{
		$returnObjects=[];
		if(isset($pluginParams["type"]))
		{
			//Recupere les elements du plugin!
			$returnObjects=Db::getObjTab(static::objectType, "SELECT * FROM ".static::dbTable." WHERE ".static::sqlPluginObjects($pluginParams)." AND ".static::sqlDisplayedObjects()." ORDER BY dateCrea desc");
			//Ajoute si besoin les plugins "current" du Dashboard (ayant lieu entre aujourd'hui et la fin de la periode selectionné)
			if($pluginParams["type"]=="dashboard" && (static::objectType=="calendarEvent" || static::objectType=="task"))
			{
				$pluginParams["type"]="current";
				$returnObjectsCurrent=Db::getObjTab(static::objectType, "SELECT * FROM ".static::dbTable." WHERE ".static::sqlPluginObjects($pluginParams)." AND ".static::sqlDisplayedObjects()." ORDER BY dateCrea desc");
				foreach($returnObjectsCurrent as $tmpObj){
					$tmpObj->pluginIsCurrent=true;
					$returnObjects[$tmpObj->_id]=$tmpObj;//écrase / ajoute l'objet du tableau
				}
			}
		}
		return $returnObjects;
	}

	/*
	 * STATIC SQL : Filtre les objets en fonction du type de plugin
	 * $pluginParams["type"] => "dashboard": cree dans la periode selectionné / "shortcut": ayant un raccourci / "search": issus d'une recherche
	 */
	public static function sqlPluginObjects($pluginParams)
	{
		if($pluginParams["type"]=="current")		{return "((dateBegin between ".Db::format($pluginParams["dateTimeBegin"])." and ".Db::format($pluginParams["dateTimeEnd"]).")  OR  (dateEnd between ".Db::format($pluginParams["dateTimeBegin"])." and ".Db::format($pluginParams["dateTimeEnd"]).")  OR  (dateBegin < ".Db::format($pluginParams["dateTimeBegin"])." and dateEnd > ".Db::format($pluginParams["dateTimeEnd"])."))";}
		elseif($pluginParams["type"]=="dashboard")	{return "dateCrea between '".$pluginParams["dateTimeBegin"]."' AND '".$pluginParams["dateTimeEnd"]."'";}
		elseif($pluginParams["type"]=="shortcut")	{return "shortcut=1";}
		elseif($pluginParams["type"]=="search")
		{
			$sqlReturned="";
			//Recherche dans tous les champs de l'objet ou uniquement ceux demandés
			$objectSearchFields=(!empty($pluginParams["searchFields"])) ? array_intersect(static::$searchFields,$pluginParams["searchFields"]) : static::$searchFields;
			//Recherche l'expression exacte
			if($pluginParams["searchMode"]=="exactPhrase"){
				foreach($objectSearchFields as $tmpField)	{$sqlReturned.=$tmpField." LIKE ".Db::format($pluginParams["searchText"])." OR "; }//Exple: "title LIKE 'mot1 mot2'"
			}
			//Recherche  "un des mots" ("title like '%mot1%' or title like '%mot2%'")  ||  "tous les mots"  ("title like '%mot1%' and title like '%mot2%'")
			else
			{
				//Récupère les mots cles de la recherche (sup. 3 carac)
				$searchWords=[];
				foreach(explode(" ",$pluginParams["searchText"]) as $valTmp){
					if(strlen($valTmp)>=3)  {$searchWords[]=$valTmp;}
				}
				//Opérateur de liaison (garder les espaces) : "Tous les mots" || "un des mots"
				$linkOperator=($pluginParams["searchMode"]=="allWords")  ?  " and "  :  " or ";
				//Recherche dans chaque champ du type d'objet
				foreach($objectSearchFields as $tmpField)
				{
					$sqlSubSearch="";
					foreach($searchWords as $tmpWord)  {$sqlSubSearch.=$tmpField." like ".Db::format($tmpWord,"likeSearch").$linkOperator;}//Recherche chaque mot / tous les mots
					$sqlReturned.="(".rtrim($sqlSubSearch,$linkOperator).") OR ";//"rtrim" plutôt que "trim" (car bouffe la première lettre du $sqlSubSearch..)
				}
			}
			//Sélection de base
			$sqlReturned="(".rtrim($sqlReturned," OR ").")";
			//Recherche aussi sur la date de creation
			if($pluginParams["creationDate"]!="all"){
				$nbDays=array("day"=>1,"week"=>7,"month"=>31,"year"=>365);
				$beginDate=time()-(86400*$nbDays[$pluginParams["creationDate"]]);
				$sqlReturned="(".$sqlReturned." AND dateCrea BETWEEN '".date("Y-m-d 00:00",$beginDate)."' and '".date("Y-m-d 23:59")."')";
			}
			//retourne le résultat
			return $sqlReturned;
		}
	}

	/*
	 * Affiche l'auteur de l' objet
	 */
	public function displayAutor($getCreator=true, $tradAutor=false)
	{
		$labelAutor=($tradAutor==true) ? Txt::trad("autor")." : ": null;
		if(!empty($this->guest))									{return $labelAutor.$this->guest." (".Txt::trad("guest").")";}				//Invité
		elseif($getCreator==false && !empty($this->_idUserModif))	{return $labelAutor.Ctrl::getObj("user",$this->_idUserModif)->getLabel();}	//Auteur de la dernière modif
		else														{return $labelAutor.Ctrl::getObj("user",$this->_idUser)->getLabel();}		//Créateur de l'objet (par défaut)
	}

	/*
	 * Affiche la date de création OU modif
	 */
	public function displayDate($getDateCrea=true, $format="normal")
	{
		if($getDateCrea==true)	{return Txt::displayDate($this->dateCrea,$format);}
		else					{return Txt::displayDate($this->dateModif,$format);}
	}
	
	/*
	 * Affiche l'auteur et la date au format "objLines"
	 */
	public function displayAutorDate($withAutorIcon=false)
	{
		$autorIcon=($withAutorIcon==true)  ?  Ctrl::getObj("user",$this->_idUser)->getImg(true,true)  :  null;//$smallImg==true : ".personImgSmall"
		return $autorIcon." ".$this->displayAutor()."<div class='objAutorDateCrea'>".$this->displayDate(true,"full")."</div>";
	}

	/*
	 * Traduction avec changement des libelles -OBJLABEL- et --OBJCONTENT-- par ceux des objets concernés
	 */
	public function tradObject($tradKey)
	{
		//Libellé de base
		$tradBase=Txt::trad($tradKey);
		//Remplace -OBJLABEL- par le type de l'objet courant  ("News", "Fichier", "dossier", etc)
		if(Txt::isTrad("OBJECT".static::objectType))  {$tradBase=str_replace("-OBJLABEL-", Txt::trad("OBJECT".static::objectType), $tradBase);}
		//Remplace --OBJCONTENT-- par le type d'objet contenu ("Fichier" si l'objet courant est un dossier)
		if(static::isContainer()){
			$MdlObjectContent=static::MdlObjectContent;
			if(Txt::isTrad("OBJECT".$MdlObjectContent::objectType))  {$tradBase=str_replace("--OBJCONTENT--", Txt::trad("OBJECT".$MdlObjectContent::objectType), $tradBase);}
		}
		// Retour
		return $tradBase;
	}
}