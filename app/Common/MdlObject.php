<?php
/**
* This file is part of the Agora-Project Software package
*
* @copyleft Agora-Project <https://www.agora-project.net>
* @license GNU General Public License (GPL-2.0)
*/


/**Création dynamique de propriété ("dynamic property")**/
#[\AllowDynamicProperties]


/*
 * Classe principale des Objects
 */
class MdlObject
{
	//Classes des menus : "trait"
	use MdlObjectMenu;

	//Propriétés de base de l'objet
	const moduleName=null;
	const objectType=null;
	const dbTable=null;
	//Propriétés des catégories / conteneurs / contenus
	const MdlCategory=null;						//Objet catégorie rattaché à l'objet courant	(ex: "MdlForumTheme", "MdlCalendarCategory", "MdlTaskStatus"...)
	const MdlObjectContainer=null;				//Objet conteneur rattaché à l'objet courant	(ex: "MdlFileFolder", "MdlTaskFolder", "MdlCalendar"...)
	const MdlObjectContent=null;				//Objets contenu rattachés à l'objet courant	(ex: "MdlFile", "MdlTask", "MdlCalendarEvent"...)
	const isFolder=false;						//L'Objet courant est un dossier
	const isFolderContent=false;				//L'Objet courant est contenu dans un dossier
	protected static $_hasAccessRight=null;		//Pas en constante car dépend du context (cf. elems d'une arbo à la racine.. ou pas)
	//Propriétés d'affichage et d'édition
	const isSelectable=false;					//Sélection multiple d'objet
	const hasShortcut=false;					//Raccourcis vers l'objet
	const hasNotifMail=false;					//Notifs d'édition par mail
	const hasAttachedFiles=false;				//Fichiers joints de l'objet
	const hasUsersLike=false;					//Likes sur l'objet
	const hasUsersComment=false;				//Commentaires sur l'objet
	const descriptionEditor=false;				//Editeur html dans la description
	public static $displayModes=[];				//Type d'affichage : ligne/block le plus souvent
	public static $requiredFields=[];			//Champs obligatoires pour valider l'édition d'un objet
	public static $searchFields=[];				//Champs de recherche
	public static $sortFields=[];				//Champs/Options de tri des résulats
	//Valeurs en cache
	private $_accessRight=null;
	private $_affectations=null;
	private $_attachedFileList=null;
	private $_attachedFileMenu=null;
	private $_usersComment=null;
	private $_usersLike=null;
	protected static $_sqlTargets=null;

	/********************************************************************************************************
	 * CONSTRUCTEUR
	 ********************************************************************************************************/
	public function __construct($objProperties=null)
	{
		////	_id par défaut (cf"isNew()")
		$this->_id=0;
		////	Assigne les propriétés (objet existant ou nouvel objet)
		if(!empty($objProperties)){
			//Récupère les propriétés en BDD ($objProperties==_id)  ||  Récupère les propriétés en paramètre
			$objValues=(is_numeric($objProperties))  ?  Db::getLine("SELECT * FROM ".static::dbTable." WHERE _id=".(int)$objProperties)  :  $objProperties;
			//Assigne chaque propriété
			if(!empty($objValues)){
				foreach($objValues as $fieldName=>$fieldValue)  {$this->$fieldName=$fieldValue;}
			}
		}
		////	Cast l'id en Interger  + Init le _typeId (ex: "fileFolder-55")
		$this->_id=(int)$this->_id;
		$this->_typeId=static::objectType."-".$this->_id;
	}

	/********************************************************************************************************
	 * RENVOIE LA VALEUR D'UNE PROPRIÉTÉ UNIQUEMENT SI ELLE EXISTE
	 ********************************************************************************************************/
	function __get($propertyName)
	{
		if(isset($this->$propertyName))  {return $this->$propertyName;}
	}

	/********************************************************************************************************
	 * VERIF : OBJET DÉJÀ CRÉÉ, AVEC UN _ID
	 ********************************************************************************************************/
	public static function isObject($curObj=null)
	{
		return (!empty($curObj) && is_object($curObj) && !empty($curObj->_id));
	}

	/********************************************************************************************************
	 * VERIF : OBJECT EN COURS DE CRÉATION
	 ********************************************************************************************************/
	public function isNew(){
		return empty($this->_id);
	}

	/********************************************************************************************************
	 * VERIF : OBJET ENREGISTRÉ À L'INSTANT EN BDD  (cf. mails de notif)
	*********************************************************************************************************/
	public function isNewRecord(){
		return ($this->isNew()==false && time()-strtotime($this->dateCrea)<5);
	}

	/********************************************************************************************************
	 * VERIF : OBJET RÉCENT CRÉÉ AUJOURD'HUI / DEPUIS MA PRÉCÉDENTE CONNEXION
	*********************************************************************************************************/
	public function isRecent(){
		return (!empty($this->dateCrea)  &&  (substr($this->dateCrea,0,10)==date('Y-m-d') || strtotime($this->dateCrea) > Ctrl::$curUser->previousConnection));
	}

	/********************************************************************************************************
	 * VERIF : OBJET "CONTAINER" AVEC DU "CONTENT"  (FOLDERS/calendar/forumSubjet/Calendar)
	 ********************************************************************************************************/
	public static function isContainer(){
		return (static::MdlObjectContent!==null);
	}

	/*****************************************************************************************************
	 * VERIF : OBJET "CONTENT" DANS UN "CONTAINER"  (file/contact/task/link/forumMessage/CalendarEvent)
	 *****************************************************************************************************/
	public static function isInContainer(){
		return (static::MdlObjectContainer!==null);
	}

	/********************************************************************************************************
	 * VERIF : OBJET DANS UNE ARBORESCENCE  (FOLDERS/file/contact/task/link)
	 ********************************************************************************************************/
	public static function isInArbo(){
		return (static::isFolderContent==true || static::isFolder==true);
	}

	/********************************************************************************************************
	 * VERIF : L'OBJET EST UN DOSSIER RACINE
	 ********************************************************************************************************/
	public function isRootFolder(){
		return (static::isFolder==true && $this->_id==1);
	}

	/********************************************************************************************************
	 * VERIF : L'OBJET EST UN "CONTENT" DANS LE DOSSIER RACINE, AVEC SES PROPRES DROITS D'ACCÈS
	 ********************************************************************************************************/
	public function isRootFolderContent(){
		return (static::isFolderContent==true && $this->_idContainer==1);
	}

	/********************************************************************************************************
	 * VERIF : L'OBJET POSSÈDE SES PROPRES DROITS D'ACCÈS
	 ********************************************************************************************************/
	public function hasAccessRight(){
		return (static::$_hasAccessRight==true || $this->isRootFolderContent());
	}

	/********************************************************************************************************
	 * VERIF : DROITS D'ACCES DE L'OBJET DEPEND D'UN "CONTAINER" (SAUF FOLDERS ET SI DANS LE ROOT FOLDER)
	 ********************************************************************************************************/
	public function hasContainerAccessRight(){
		return (static::isInContainer() && $this->isRootFolderContent()==false);
	}

	/********************************************************************************************************
	 * VERIF : USER COURANT AUTEUR DE L'OBJET
	 ********************************************************************************************************/
	public function isAutor(){
		return (Ctrl::$curUser->isUser() && $this->_idUser==Ctrl::$curUser->_id);
	}

	/********************************************************************************************************
	 * CONTENEUR DE L'OBJET COURANT  (FOLDERS/file/contact/task/link)
	 ********************************************************************************************************/
	public function containerObj()
	{
		if(!empty($this->_idContainer)){
			$MdlObjectContainer=(static::isFolder==true)  ?  get_class($this)  :  static::MdlObjectContainer;
			return Ctrl::getObj($MdlObjectContainer::objectType, $this->_idContainer);
		}
	}

	/********************************************************************************************************
	 * OBJET "CATEGORY" DE L'OBJET COURANT  (theme du forum / categorie d'evts / status des tâches..)
	 ********************************************************************************************************/
	public function categoryObj()
	{
		if(static::MdlCategory!==null){
			$MdlCategory=static::MdlCategory;																				//Objet "catégory" rattaché à l'objet courant
			$dbCategoryField=$MdlCategory::dbParentField;																	//Champ correspondant à l'id de la catégorie (_idCat, idTheme, _idStatus, etc)
			if(!empty($this->$dbCategoryField))  {return Ctrl::getObj($MdlCategory::objectType, $this->$dbCategoryField);}	//Renvoie l'objet catégory
		}
	}

	/********************************************************************************************************
	 * RECUPÈRE LE LABEL DE LA CATEGORIE L'OBJET
	 ********************************************************************************************************/
	public function categoryLabel()
	{
		$categoryObj=$this->categoryObj();
		if(is_object($categoryObj))  {return '<span class="categoryLabel">'.$categoryObj->getLabel().'</span>';}
	}

	/********************************************************************************************************
	 * LISTE LES AFFECTATIONS ET DROITS D'ACCES DE L'OBJET (Espaces/groupes/users)
	 ********************************************************************************************************/
	public function getAffectations()
	{
		if($this->_affectations===null){
			$this->_affectations=$affects=[];
			////	Objet existant : récup les affectations en Bdd
			if($this->isNew()==false){																													
				$affects=Db::getTab("SELECT * FROM ap_objectTarget WHERE objectType=".Db::format(static::objectType)." AND _idObject=".$this->_id." ORDER BY _idSpace, target");//"ORDER BY" pour les labels du "contextMenu()"
				if(empty($affects))  {$affects[]=["_idSpace"=>Ctrl::$curSpace->_id, "target"=>"U".$this->_idUser, "accessRight"=>2];}											//Droit par défaut (cf. agendas persos & co)
			}
			////	Nouvel objet : affectations par défaut
			else{																																		
				$affects[]=["_idSpace"=>Ctrl::$curSpace->_id,  "target"=>"spaceUsers",				"accessRight"=>(static::isContainer() ? 1.5 : 1)];	//Users de l'espace courant (ecriture limité / lecture)
				$affects[]=["_idSpace"=>Ctrl::$curSpace->_id,  "target"=>"U".Ctrl::$curUser->_id,	"accessRight"=>2];									//User courant (ecriture)
			}
			////	Label des affectations
			foreach($affects as $aff){
				//ex: User "Jean Dupont"  || Groupe "Bidule" || "Tous les utilisateurs > Espace XJ200"
				$targetKey=$aff["_idSpace"].'_'.$aff["target"];
				$aff["targetType"]= $aff["targetId"]=$aff["label"]=null;
				if(preg_match("/^U/",$aff["target"]))		{$aff["targetType"]="user";			$aff["targetId"]=(int)substr($aff["target"],1);	 $aff["label"]=Ctrl::getObj("user",$aff["targetId"])->getLabel();}
				elseif(preg_match("/^G/",$aff["target"]))	{$aff["targetType"]="group";		$aff["targetId"]=(int)substr($aff["target"],1);	 $aff["label"]=Ctrl::getObj("userGroup",$aff["targetId"])->getLabel();}
				elseif($aff["target"]=="spaceUsers")		{$aff["targetType"]="spaceUsers";	$aff["targetId"]=(int)$aff["_idSpace"];			 $aff["label"]=Txt::trad("accessAllUsers");}
				//Label de l'espace : "tous les utilisateurs" || affectation sur un autre espace
				if($aff["targetType"]=="spaceUsers" || $aff["_idSpace"]!=Ctrl::$curSpace->_id)   {$aff["label"].='<img src="app/img/arrowRightSmall.png">'.Ctrl::getObj("space",$aff["_idSpace"])->getLabel();}
				//Ajoute l'affectation
				$this->_affectations[$targetKey]=$aff;
			}
		}
		return $this->_affectations;
	}

	/*****************************************************************************************************************************************
	 * EDITE LES AFFECTATIONS ET DROITS D'ACCÈS DE L'OBJET (cf."editMenuSubmit()"). Par défaut : accès en lecture à l'espace courant
	 *****************************************************************************************************************************************/
	public function setAffectations($objectRightSpecific=null)
	{
		////	Object indépendant  &&  "objectRight" spécifié OU droit d'accès spécifiques
		if($this->hasAccessRight()  &&  (Req::isParam("objectRight") || !empty($objectRightSpecific))){
			//Init
			$sqlInsertBase="INSERT INTO ap_objectTarget SET objectType=".Db::format(static::objectType).", _idObject=".$this->_id.", ";
			//Réinitialise les droits, uniquement sur les espaces auxquels l'user courant a accès
			if($this->isNew()==false){
				$sqlSpaces="_idSpace IN (".implode(",",Ctrl::$curUser->spaceList("ids")).")";
				if(Ctrl::$curUser->isGeneralAdmin())	{$sqlSpaces="(".$sqlSpaces." OR _idSpace IS NULL)";}
				Db::query("DELETE FROM ap_objectTarget WHERE objectType=".Db::format(static::objectType)." AND _idObject=".$this->_id." AND ".$sqlSpaces);
			}
			//Ajoute les nouveaux droits d'accès : passés en paramètre / provenant du formulaire
			$newAccessRight=Req::isParam("objectRight")  ?  Req::param("objectRight")  :  $objectRightSpecific;
			foreach($newAccessRight as $tmpRight){
				$tmpRight=explode("_",$tmpRight);//ex:  "55_U33_2"  devient ["_idSpace"=>"5","target"=>"U3","accessRight"=>"2"]  correspond à droit "2" sur l'user "33" de l'espace "55"
				Db::query($sqlInsertBase." _idSpace=".Db::format($tmpRight[0]).", target=".Db::format($tmpRight[1]).", accessRight=".Db::format($tmpRight[2]));
			}
		}
	}

	/*****************************************************************************************************************************************
	 * RÉCUPÈRE LE DROIT D'ACCÈS POUR L'USER COURANT SUR L'OBJET
	 *		3	[total]					objet indépendant *	-> modif/suppression
	 *									objet conteneur **	-> modif/suppression du conteneur + modif/suppression de tout le contenu ***
	 *		2	[ecriture]				objet indépendant *	-> modif/suppression
	 *									objet conteneur **	-> lecture du conteneur + modif/suppression de tout le contenu ***
	 *		1.5	[ecriture limité]		objet indépendant *	-> -non disponible-
	 *									objet conteneur **	-> lecture du conteneur + modif/suppression du contenu créé par l'user courant
	 *		1	[lecture]				objet indépendant *	-> lecture
	 *									objet conteneur **	-> lecture du conteneur
	 *		*	objet indépendant : 		actualités et objets du dossier racine (fichiers, taches..)
	 *		**	objet conteneur : 			dossiers, agendas, sujets du forum
	 *		***	contenu d'un conteneur :	fichiers, taches, message du forum.. qui héritent des droits d'accès du conteneur
	******************************************************************************************************************************************/
	public function accessRight()
	{
		if($this->_accessRight===null){
			$this->_accessRight=0;																														 //INIT
			if(Ctrl::$curUser->isGeneralAdmin() || $this->isAutor() || $this->createRight()) {$this->_accessRight=3;}									 //FULL ACCESS : ADMIN GÉNÉRAL / AUTEUR DE L'OBJET / NOUVEL OBJET
			elseif($this->isRootFolder() || $this->md5IdControl())							 {$this->_accessRight=1;}									 //LECTURE : DOSSIER RACINE (DROIT PAR DEFAUT) / VUE EXTERNE DE L'OBJET
			elseif($this->hasContainerAccessRight())										 {$this->_accessRight=$this->containerObj()->accessRight();} //EN FONCTION DU CONTENEUR PARENT
			elseif($this->hasAccessRight()){																											 //EN FONCTION DES AFFECTATIONS EN BDD
				$isPersonalCalendar=(static::objectType=="calendar" && $this->type=="user");
				$sqlSelect="FROM `ap_objectTarget` WHERE `objectType`=".Db::format(static::objectType)." AND `_idObject`=".$this->_id." AND `_idSpace`=".Ctrl::$curSpace->_id;
				//// ACCES TOTAL :  admin de l'espace et objet affecté à l'espace (sauf agendas persos : pas de privilège admin)
				if(Ctrl::$curUser->isSpaceAdmin()  &&  Db::getVal("SELECT COUNT(*) ".$sqlSelect)>0  &&  $isPersonalCalendar==false){
					$this->_accessRight=3;
				}
				//// ACCES EN FONCTION DES AFFECTATIONS EN BDD
				else{
					$this->_accessRight=Db::getVal("SELECT MAX(accessRight) ".$sqlSelect." AND target IN (".static::sqlAffectations().")");	//Droit le + important pour l'user courant
					if(Ctrl::$curUser->isUser()==false  &&  $this->_accessRight>1)  {$this->_accessRight=1;}								//Droit en lecture pour les Guests
				}
			}
		}
		//Renvoie le résultat "float" (cf. droit "1.5" en "ecriture limité")
		return (float)$this->_accessRight;
	}

	/********************************************************************************************************
	 * DROIT POUR L'USER COURANT DE CRÉER UN NOUVEL OBJET
	 ********************************************************************************************************/
	public function createRight()
	{
		if($this->_id==0){
			if($this->hasAccessRight() || static::isInContainer()==false)	{return true;}										//Obj avec ses propres droits d'accès || Obj indépendant avec un accès spécifique (user/space)
			elseif($this->hasContainerAccessRight())						{return $this->containerObj()->addContentRight();}	//Droits en fonction du "addContentRight()" du parent
		}
		return false;//false si l'objet existe dejà
	}

	/********************************************************************************************************
	 * DROIT POUR L'USER COURANT D'ACCEDER EN LECTURE A L'OBJET
	 ********************************************************************************************************/
	public function readRight()
	{
		return ($this->accessRight()>0);
	}

	/********************************************************************************************************
	 * CONTENEUR : DROIT POUR L'USER COURANT D'AJOUTER DU CONTENU AU CONTENEUR
	 ********************************************************************************************************/
	public function addContentRight()
	{
		return (static::isContainer() && $this->accessRight()>1);
	}

	/********************************************************************************************************
	 * CONTENEUR : DROIT POUR L'USER COURANT D'ÉDITER TOUT LE CONTENU DU CONTENEUR
	 ********************************************************************************************************/
	public function editContentRight()
	{
		return (static::isContainer() && $this->accessRight()>=2);
	}

	/********************************************************************************************************
	 * DROIT POUR L'USER COURANT D'ÉDITER L'OBJET : accessRight==3 POUR LES CONTENEURS
	********************************************************************************************/
	public function editRight()
	{
		return ($this->accessRight()==3  ||  ($this->accessRight()==2 && static::isContainer()==false));
	}

	/********************************************************************************************************
	 * DROIT POUR L'USER COURANT DE SUPPRIMER L'OBJET : METHODE SURCHARGÉE
	 ********************************************************************************************************/
	public function deleteRight()
	{
		return ($this->editRight());
	}

	/********************************************************************************************************
	 * CONTROLE LE DROIT POUR L'USER COURANT DE LIRE L'OBJET
	 ********************************************************************************************************/
	public function readControl()
	{
		//Controle le droit d'accès en lecture && Controle si l'objet existe
		if($this->accessRight()==0 || empty($this->_id))  {Ctrl::noAccessExit();}
	}

	/********************************************************************************************************
	 * CONTROLE LE DROIT POUR L'USER COURANT D'EDITER L'OBJET
	 ********************************************************************************************************/
	public function editControl()
	{
		//Controle l'accès en écriture  &&  Notif si un autre user édite le même objet (cf. "messengerUpdate()")
		if($this->editRight()==false)  {Ctrl::noAccessExit();}
		$otherUserEdit=Db::getVal("SELECT _idUser FROM ap_userLivecouter WHERE _idUser!=".Ctrl::$curUser->_id." AND editTypeId=".Db::param("typeId")." AND `date` > ".(time()-30));
		if($this->isNew()==false && !empty($otherUserEdit))  {Ctrl::notify(Txt::trad("elemEditedByAnotherUser").' '.Ctrl::getObj("user",$otherUserEdit)->getLabel());}
	}

	/********************************************************************************************************
	 * LABEL DE L'OBJET : CHAMP PRINCIPAL (CF "$requiredFields")
	 ********************************************************************************************************/
	public function getLabel()
	{
		//Label principal
		if(!empty($this->name))				{$text=$this->name;}		//Ex: nom de fichier
		elseif(!empty($this->title))		{$text=$this->title;}		//Ex: titre d'une 'task'
		elseif(!empty($this->description))	{$text=$this->description;}	//Ex: actualité, message du forum
		elseif(!empty($this->adress))		{$text=$this->adress;}		//Ex: Url d'un 'link'
		else								{$text=null;}
		//Retourne le label de l'objet
		return Txt::reduce($text,50);
	}

	/********************************************************************************************************
	 * URL D'ACCÈS À L'OBJET  :  $display => "vue"/ "edit" / "delete" / accès depuis la page principale
	*********************************************************************************************************/
	public function getUrl($display=null)
	{
		$url="?ctrl=".static::moduleName;
		if($display=="vue")					{return $url."&action=Vue".ucfirst(static::objectType)."&typeId=".$this->_typeId;}			//Vue dans une lightbox (Task/User/Contact/etc)
		elseif($display=="edit")			{return $url."&action=VueEdit".ucfirst(static::objectType)."&typeId=".$this->_typeId;}		//Edition un objet
		elseif($display=="delete")			{return "?ctrl=object&action=delete&typeId=".$this->_typeId;}								//Suppression d'un objet via "actionDelete()"
		elseif(static::isInContainer())		{return $url."&typeId=".$this->containerObj()->_typeId."&typeIdTarget=".$this->_typeId;}	//Affichage du conteneur d'un objet (File/Task/CalendarEvent/etc)
		else								{return $url."&typeId=".$this->_typeId;}													//Affichage par défaut (News/Folder/etc)
	}

	/********************************************************************************************************
	 * URL DE PARTAGE D'UN OBJET (NOTIFS MAIL, ETC)
	 * Via "userConnectionSpaceSelection()"  >  cible l'espace courant  >  puis redir vers l'url de l'objet
	 ********************************************************************************************************/
	public function getUrlExternal()
	{
		return Req::curUrl().'/index.php?ctrl=offline&_idSpaceAccess='.Ctrl::$curSpace->_id.'&objUrl='.urlencode($this->getUrl());
	}

	/********************************************************************************************************
	 * URL D'EDITION D'UN NOUVEL OBJET
	 ********************************************************************************************************/
	public static function getUrlNew()
	{
		$url=(new static())->getUrl("edit");									//URL d'édition d'un nouvel objet
		if(!empty(Ctrl::$curContainer) && static::objectType!="calendarEvent")	//Ajoute l'id du container (Folder/File/ForumMessage/etc sauf CalendarEvent)
			{$url.='&_idContainer='.Ctrl::$curContainer->_id;}
		return $url;
	}

	/********************************************************************************************************
	 * LIEN POUR AFFICHER LA VUE DE L'OBJET
	*********************************************************************************************************/
	public function openVue()
	{
		return "lightboxOpen('".$this->getUrl("vue")."');";
	}

	/********************************************************************************************************
	 * IDENTIFIANT "md5()" DE L'OBJET POUR UN ACCÈS EXTERNE
	 ********************************************************************************************************/
	public function md5Id()
	{
		return md5($this->_id.$this->dateCrea.$this->_idUser);
	}
	
	/********************************************************************************************************
	 * CONTROLE L'IDENTIFIANT MD5 PASSÉ EN PARAMETRE
	 ********************************************************************************************************/
	public function md5IdControl()
	{
		return (Req::isParam("md5Id") && $this->md5Id()==Req::param("md5Id"));
	}

	/********************************************************************************************************
	 * SUPPRESSION D'UN OBJET
	 ********************************************************************************************************/
	public function delete()
	{
		if($this->deleteRight()){																	//Controle d'accès
			$sqlSelect="WHERE objectType='".static::objectType."' AND _idObject=".$this->_id;		//Sélection SQL de l'objet
			if(static::hasUsersComment)	{Db::query("DELETE FROM ap_objectComment ".$sqlSelect);}	//Supprime les "commentaires"
			if(static::hasUsersLike)	{Db::query("DELETE FROM ap_objectLike ".$sqlSelect);}		//Supprime les "likes"
			foreach($this->attachedFileList() as $tmpFile)  {$this->attachedFileDelete($tmpFile);}	//Supprime les fichiers joints
			Ctrl::addLog("delete",$this);															//Ajoute le log de suppression
			Db::query("DELETE FROM ap_objectTarget ".$sqlSelect);									//Supprime les droits d'accès
			Db::query("DELETE FROM ".static::dbTable." WHERE _id=".$this->_id);						//Supprime ENFIN l'objet !
		}
	}

	/********************************************************************************************************
	 * DÉPLACE UN OBJET DANS UN AUTRE DOSSIER (DOSSIER OU CONTENU D'UN DOSSIER)
	 ********************************************************************************************************/
	public function folderMove($newFolderId)
	{
		////	Ancien et nouveau dossier
		$oldFolder=$this->containerObj();
		$newFolder=Ctrl::getObj($oldFolder::objectType, $newFolderId);
		////	Objet dans une arbo & droit d'accès ok ?
		if(static::isInArbo()==false || $this->accessRight()<2 || $newFolder->accessRight()<2 || (static::objectType=="fileFolder" && is_dir($newFolder->folderPath("real"))==false))
			{Ctrl::notify(Txt::trad("inaccessibleElem")." : ".$this->name.$this->title);}
		////	Dossier de destination accessible & pas à l'interieur de lui même ?
		elseif(static::isFolder===true && $this->isInCurrentTree($newFolder))
			{Ctrl::notify(Txt::trad("NOTIF_folderMove")." : ".$this->name);}
		////	Déplacement du dossier Ok
		else{
			////	Change le dossier parent en BDD
			Db::query("UPDATE ".static::dbTable." SET _idContainer=".(int)$newFolderId." WHERE _id=".$this->_id);
			////	Update les droits d'accès
			if(static::isFolder==false){
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
			$curObj=Ctrl::getObj(static::objectType, $this->_id, true);
			////	Déplace un fichier sur le disque
			if(static::objectType=="file"){
				//Deplace chaque version du fichier
				foreach(Db::getTab("SELECT * FROM ap_fileVersion WHERE _idFile=".$this->_id) as $tmpFileVersion){
					rename($oldFolder->folderPath("real").$tmpFileVersion["realName"], $newFolder->folderPath("real").$tmpFileVersion["realName"]);
				}
				//Déplace la vignette
				if($this->hasTumb()){
					rename($oldFolder->folderPath("real").$this->thumbName(), $newFolder->folderPath("real").$this->thumbName());
				}
			}
			////	Déplace un dossier sur le disque (du chemin actuel : $this,  vers le nouveau chemin : $curObj)
			elseif(static::objectType=="fileFolder"){
				rename($this->folderPath("real"), $curObj->folderPath("real"));
			}
			////	Ajoute aux logs
			Ctrl::addLog("modif", $curObj, Txt::trad("changeFolder"));
			return true;
		}
	}

	/********************************************************************************************************
	 * LISTE DES USERS AFFECTÉS À L'OBJET ($accessWrite==true pour filtrer uniquement les accès en écriture )
	 ********************************************************************************************************/
	public function affectedUserIds($accessWrite=false)
	{
		//Init les users et l'objet de référence des affectations (ex: dossier parent d'un fichier)
		$userIds=[];
		$refObject=($this->hasAccessRight())  ?  $this :  $this->containerObj();
		//Récupère les users de chaque affectation
		foreach($refObject->getAffectations() as $affect){
			if($accessWrite==true && $affect["accessRight"]<2)	{continue;}																						//Uniquement accès en écriture ? (cf. agendas perso)
			elseif($affect["targetType"]=="spaceUsers")			{$userIds=array_merge($userIds, Ctrl::getObj("space",$affect["targetId"])->getUsers("idsTab"));}//Ajoute tous les users de l'espace
			elseif($affect["targetType"]=="group")				{$userIds=array_merge($userIds, Ctrl::getObj("userGroup",$affect["targetId"])->userIds);}		//Ajoute les users du groupe
			elseif($affect["targetType"]=="user")				{$userIds[]=$affect["targetId"];}																//Ajoute l'user
		}
		//Retourne la liste des users
		return array_unique($userIds);
	}

	/********************************************************************************************************
	 * AJOUT/MODIF D'OBJET
	 ********************************************************************************************************/
	public function editRecord($sqlFields)
	{
		if($this->editRight()){
			$sqlFields=trim(trim($sqlFields),",");																									//Init $sqlFields
			if(Req::isParam("guest"))			{$sqlFields.=", guest=".Db::param("guest").", guestMail=".Db::param("guestMail");}					//Auteur "guest"
			if(Req::isParam("_idContainer"))	{$sqlFields.=", _idContainer=".Db::param("_idContainer");}											//"_idContainer" de l'objet
			if(static::hasShortcut==true)		{$sqlFields.=", shortcut=".Db::param("shortcut");}													//"shortcut" sur l'objet
			if(static::objectType!="agora"){																										//Objet lambda :
				if($this->isNew())					{$sqlFields.=", dateCrea=".Db::dateNow().", _idUser=".Db::format(Ctrl::$curUser->_id);}			//Auteur/Date de création
				elseif(static::objectType!="mail")	{$sqlFields.=", dateModif=".Db::dateNow().", _idUserModif=".Db::format(Ctrl::$curUser->_id);}	//Auteur/Date de modification
			}
			if($this->isNew())	{$_id=(int)Db::query("INSERT INTO ".static::dbTable." SET ".$sqlFields, true);}										//INSERT UN NOUVEL OBJET
			else				{Db::query("UPDATE ".static::dbTable." SET ".$sqlFields." WHERE _id=".$this->_id);   $_id=$this->_id;}				//UPDATE L'OBJET
			$curObj=Ctrl::getObj(static::objectType, $_id, true);																					//Charge les nouvelles propriétés (cache updated)
			$curObj->setAffectations();																												//Ajoute les droits d'accès
			$curObj->attachedFileAdd();																												//Ajoute les fichiers joints
			Ctrl::addLog(($curObj->isNewRecord()?"add":"modif"), $curObj);																			//Enregistre dans les Logs
			return Ctrl::getObj(static::objectType, $_id, true);																					//Renvoie l'objet (cache updated)
		}
	}

	/********************************************************************************************************
	 * ENVOI UNE NOTIF PAR MAIL DE L'EDITION DE L'OBJET (cf. "menuEdit")
	 ********************************************************************************************************/
	public function sendMailNotif($specificLabel=null, $addFiles=null, $addUserIds=null)
	{
		if(Req::isParam("notifMail") || !empty($addUserIds)){
			////	Sujet
			$tradSubject=($this->isNew() || $this->isNewRecord())  ?  "MAIL_elemCreatedBy"  :  "MAIL_elemModifiedBy";//Ex: "Fichier créé par Paul"
			$subject=ucfirst($this->tradObject($tradSubject))." ".Ctrl::$curUser->getLabel();
			////	Message : label/description de l'objet
			$descriptionLabel=in_array('description',static::$requiredFields);			//Label = description (champ principal)
			if(!empty($specificLabel))			{$message=$specificLabel;}				//Nom des fichiers uploadés, etc
			elseif($descriptionLabel==true)		{$message=$this->descriptionMail();}	//Description envoyee par mail
			else								{$message=$this->getLabel();}			//Label principal (title, name, etc)
			if($descriptionLabel==false && !empty($this->description))  {$message.='<br><br><b>'.$this->descriptionMail().'</b>';}//Ajoute si besoin la description (ex: fichiers uploadés)
			////	Message : mise en forme
			$message='<div style="margin:20px 0px;">'.$subject.' :</div>
					  <div style="margin:20px 0px;max-width:95%;padding:10px 20px;background:#eee;border:1px solid #bbb;border-radius:5px;">'.$message.'</div>
					  <a href="'.$this->getUrlExternal().'" target="_blank">'.Txt::trad("MAIL_elemAccessLink").'</a>';
			////	Destinataires de la notif :  Users spécifiés ou affectés à l'objet
			$mailUserIds=[];
			if(Req::isParam("notifMail")){
				$mailUserIds=Req::isParam("notifMailUsers")  ?  Req::param("notifMailUsers")  :  $this->affectedUserIds();
			}
			////	Destinataires passés en paramètres (ex: "notifyLastMessage" du forum)
			if(!empty($addUserIds)){
				if(Req::isParam("notifMail")==false)  {$noNotify=true;}//Pas de notif d'envoie du mail
				$mailUserIds=array_unique(array_merge($mailUserIds,$addUserIds));
			}
			////	Envoi du message
			if(!empty($mailUserIds)){
				$options[]=(!empty($noNotify))  ?  "noNotify"  :  "objectNotif";								//Options "notify()" : pas de notif OU notif "L'email de notification a bien été envoyé"
				if(Req::isParam("mailOptions"))  {$options=array_merge($options,Req::param("mailOptions"));}	//Options sélectionnées par l'user
				$attachedFiles=$this->attachedFileList();														//Fichiers joints de l'objet
				if(!empty($addFiles))  {$attachedFiles=array_merge($addFiles,$attachedFiles);}					//Ajoute les fichiers spécifiques (ex: fichier ".ics" d'un évenement)
				Tool::sendMail($mailUserIds, $subject, $message, $options, $attachedFiles);						//Envoie l'email
			}
		}
	}

	/********************************************************************************************************
	 * STATIC SQL : PREPARE LA SELECTION D'OBJETS EN FONCTION DE LEUR AFFECTATION
	 * "targets" (exple) : "spaceUsers" / "U1" / "G1"
	 ********************************************************************************************************/
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

	/**************************************************************************************************
	 * STATIC SQL : OBJETS A AFFICHER EN FONCTION DU CONTENEUR ET DES DROITS D'ACCÈS DE L'USER COURANT
	 **************************************************************************************************/
	public static function sqlDisplay($containerObj=null, $_idKey="_id")
	{
		////	Init les conditions et sélectionne si besoin un conteneur
		$conditions=(is_object($containerObj))  ?  ["_idContainer=".$containerObj->_id]  :  [];
		////	Selection en fonction des droits d'acces (cf. "ap_objectTarget" et $_hasAccessRight)   ||  Selectionne les objets d'une arbo (toute l'arbo si $containerObj==null ou isRootFolder()==true)
		if(static::$_hasAccessRight==true  ||  (static::isFolderContent==true && ($containerObj==null || $containerObj->isRootFolder()))){
			$sqlTargets=(!empty($_SESSION["displayAdmin"]) && Ctrl::$curUser->isSpaceAdmin())  ?  null  :  "and `target` in (".static::sqlAffectations().")";//Sélection "displayAdmin" || en fonction des droits d'acces
			$conditions[]=$_idKey." IN (select _idObject as ".$_idKey." from ap_objectTarget where objectType='".static::objectType."' and _idSpace=".Ctrl::$curSpace->_id." ".$sqlTargets.")";
		}
		////	Fusionne toutes les conditions avec "AND"  ||  Sélection par défaut (pour pas retourner d'objet ni d'erreur sql)
		$sqlDisplay=(!empty($conditions))  ?  "(".implode(" AND ",$conditions).")"  :  $_idKey." IS NULL";
		////	Selection de "plugin" : selectionne les objets des conteneurs auquel on a accès (dossiers/sujets/evt..)
		if($containerObj==null && static::isInContainer()){
			$sqlDisplay="(".$sqlDisplay." OR ".static::MdlObjectContainer::sqlDisplay(null,"_idContainer").")";//Appel récursif avec "_idContainer" comme $_idKey
		}
		////	Renvoie le résultat (avec des espaces avant/après)
		return " ".$sqlDisplay." ";
	}

	/********************************************************************************************************
	 * RECUPÈRE LES OBJETS D'UN "PLUGIN" (FONCTION DE $params ET DES DROITS D'ACCES)
	 ********************************************************************************************************/
	public static function getPluginObjects($params)
	{
		return (!empty($params))  ?  Db::getObjTab(static::objectType, "SELECT * FROM ".static::dbTable." WHERE ".static::sqlPlugins($params)." AND ".static::sqlDisplay()." ORDER BY dateCrea desc")  :  [];
	}

	/********************************************************************************************************
	 * RECUPERE LES PLUGINS DE TYPE "FOLDER"
	 ********************************************************************************************************/
	public static function getPluginFolders($params)
	{
		$pluginsList=[];
		foreach(static::getPluginObjects($params) as $objFolder)
		{
			$objFolder->pluginIcon="folder/folderSmall.png";
			$objFolder->pluginLabel=$objFolder->name;
			$objFolder->pluginTooltip=$objFolder->folderPath("text");
			if(!empty($objFolder->description))  {$objFolder->pluginTooltip.="<hr>".Txt::reduce($objFolder->description);}
			$objFolder->pluginJsIcon="window.top.redir('".$objFolder->getUrl()."')";//Redir vers le dossier
			$objFolder->pluginJsLabel=$objFolder->pluginJsIcon;
			$pluginsList[]=$objFolder;
		}
		return $pluginsList;
	}

	/*************************************************************************************************************************************************
	 * STATIC SQL : SELECTIONNE LES OBJETS EN FONCTION DU TYPE DE PLUGIN
	 * $params["type"] =>  "dashboard" : cree dans la periode selectionné  ||  "shortcut" : ayant un raccourci  ||  "search" : issus d'une recherche
	 *************************************************************************************************************************************************/
	public static function sqlPlugins($params)
	{
		////	PLUGINS DASHBOARD / SHORTCUT / SEARCH
		if($params["type"]=="dashboard")	{return "dateCrea BETWEEN ".Db::format($params["dateTimeBegin"])." AND ".Db::format($params["dateTimeEnd"]);}
		elseif($params["type"]=="shortcut")	{return "shortcut=1";}
		elseif($params["type"]=="search"){
			////	Init la requete SQL et Les champs de recherche (tous ou uniquement ceux demandés)
			$returnSql=null;
			$objSearchFields=(!empty($params["searchFields"]))  ?  array_intersect(static::$searchFields,$params["searchFields"])  :  static::$searchFields;
			////	Recherche "l'expression exacte"
			if($params["searchMode"]=="exactPhrase"){
				foreach($objSearchFields as $tmpField){																											//Recherche sur chaque champ de l'objet
					$searchText=($tmpField=="description" && static::descriptionEditor==true) ?  htmlentities($params["searchText"])  :  $params["searchText"];	//Texte brut ou avec les accents de l'éditeur (&agrave; &egrave; etc)
					$returnSql.=" `".$tmpField."` LIKE ".Db::format($searchText,"sqlLike")." OR ";																//"sqlLike" pour délimiter le texte &&  "OR" pour rechercher le champ suivant
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
						$sqlWords.="`".$tmpField."` LIKE ".Db::format($tmpWord,"sqlLike").$operatorWords;								//"sqlLike" pour délimiter le texte
					}	
					$returnSql.=" (".trim($sqlWords,$operatorWords).") OR ";															//Supprime le dernier $operatorWords  &&  Ajoute "OR" pour chercher sur le champ suivant
				}
			}
			////	Supprime le dernier opérateur "OR" entre chaque champ de recherche
			$returnSql=" (".trim($returnSql," OR ").") ";
			////	Filtre en fonction de la date de creation
			if($params["creationDate"]!="all"){
				$timeCreationDate=time() - (86400 * $params["creationDate"]);
				$returnSql.=" AND dateCrea >= '".date("Y-m-d 00:00",$timeCreationDate)."' ";
			}
			////	Retourne le résultat
			return $returnSql;
		}
	}

	/********************************************************************************************************
	 * AUTEUR DE L'OBJET : CRÉATION
	 ********************************************************************************************************/
	public function autorLabel()
	{
		if(!empty($this->guest))	{return $this->guest.' ('.Txt::trad("guest").')';}			//GUEST
		else						{return Ctrl::getObj("user",$this->_idUser)->getLabel();}	//USER
	}

	/********************************************************************************************************
	 * AUTEUR & DATE : CRÉATION || MODIFICATION
	 ********************************************************************************************************/
	public function autorDate($isModif=true, $profileImg=false)
	{
		if(!empty($this->guestMail)){																					//GUEST
			$tooltipMail=(Ctrl::$curUser->isSpaceAdmin())  ?  Txt::tooltip($this->guestMail)  :  null;					//Affiche le Mail?
			$autorLabel='<span '.$tooltipMail.'>'.$this->autorLabel().'<span>';
		}else{																											//USER
			if($isModif==true && !empty($this->_idUserModif))	{$objUser=Ctrl::getObj("user",$this->_idUserModif);}	//Auteur de la modif
			else												{$objUser=Ctrl::getObj("user",$this->_idUser);}			//Auteur de la création
			if($profileImg==true)	{$autorLabel=$objUser->profileImg(false,true).' &nbsp;'.$objUser->getLabel();}		//Image + Label
			else					{$autorLabel=$objUser->getLabel();}													//Label uniquement
			$autorLabel='<span onclick="'.$objUser->openVue().'">'.$autorLabel.'</span>';  								//Lien vers le profil de l'user
		}
		$dateEdit=($isModif==true && !empty($this->dateModif))  ?  $this->dateModif  :  $this->dateCrea;				//Date de créa/modif
		return $autorLabel.'&nbsp;<img src="app/img/arrowRightSmall.png">&nbsp;'.Txt::dateLabel($dateEdit,"labelFull");	//Garder "&nbsp;" au cas où <img> est masqué
	}

	/********************************************************************************************************
	 * LIBELLE DE L'OBJET
	 ********************************************************************************************************/
	public function tradObject($tradKey)
	{
		//// Traduction principale
		$trad=Txt::trad($tradKey);
		$type=static::objectType;
		//// Label de l'objet
		$trad=str_replace(["--OBJ_LABEL--","--OBJ_LABEL_TO--"], [Txt::trad("OBJ_".$type),Txt::trad("OBJ_".$type."_TO")], $trad);								//Ex: "--OBJ_LABEL--"=>"actualité" / "--OBJ_LABEL_TO--"=>"à l'actualité"
		//// Label du contenu de l'objet
		if(static::isContainer())		{$trad=str_replace("--OBJ_LABEL_CONTENT--", Txt::trad("OBJ_".static::MdlObjectContent::objectType."_CONTENT"), $trad);}	//Ex: "les fichiers" du dossier, "les événements" de l'agenda
		elseif(static::isInContainer())	{$trad=str_replace("--OBJ_LABEL_CONTENT--", Txt::trad("OBJ_".static::objectType."_CONTENT"), $trad);}					//Ex: "les messages" du sujet
		/// Retourne la trad
		return $trad;
	}

	/********************************************************************************************************
	 * FICHIERS JOINTS : EDITION DES FICHIERS DE L'OBJET (cf. "VueObjMenuEdit.php")
	 ********************************************************************************************************/
	public function attachedFileEdit()
	{
		$vDatas["curObj"]=$this;
		return Ctrl::getVue(Req::commonPath."VueObjAttachedFile.php",$vDatas);
	}

	/********************************************************************************************************
	 * FICHIERS JOINTS : INFOS SUR UN FICHIER
	 ********************************************************************************************************/
	public static function attachedFileInfos($file)
	{
		if(!empty($file)){
			if(is_numeric($file))  {$file=Db::getLine("SELECT * FROM ap_objectAttachedFile WHERE _id=".(int)$file);}				//Si besoin, récupère les infos en bdd
			$file["path"]=PATH_OBJECT_ATTACHMENT.$file["_id"].".".File::extension($file["name"]);									//Path/chemin réel du fichier
			if(is_file($file["path"])){																								//Vérifie que le fichier est bien accessible
				$file["urlDownload"]='?ctrl=object&action=AttachedFileDownload&_id='.$file["_id"];									//Url de download du fichier
				if(Req::isMobileApp())  {$file["urlDownload"]=CtrlMisc::urlDownloadMobileApp($file["urlDownload"],$file["name"]);}	//Url de download via CtrlMisc
				$file["parentObj"]=Ctrl::getObj($file["objectType"],$file["_idObject"]);											//Objet auquel est rattaché le fichier
				$file["displayUrl"]=self::attachedFileDisplayUrl($file["_id"], $file["name"]);										//Url d'affichage du fichier ou de l'image (cf "actionAttachedFileDisplay()")
				if(File::isType("editorImage",$file["name"]))  {$file["cid"]="attachedFile".$file["_id"];}							//"cid" des images intégrées aux emails (cf "descriptionMail()")
				return $file;
			}
		}
	}

	/********************************************************************************************************
	 * FICHIERS JOINTS : URL D'AFFICHAGE D'UN FICHIER  (cf "actionAttachedFileDisplay()")
	 ********************************************************************************************************/
	public static function attachedFileDisplayUrl($fileId, $fileName)
	{
		return "?ctrl=object&action=AttachedFileDisplay&_id=".$fileId."&extension=.".File::extension($fileName);
	}

	/********************************************************************************************************
	 * FICHIERS JOINTS : LISTE LES FICHIERS JOINTS
	 ********************************************************************************************************/
	public function attachedFileList()
	{
		if($this->_attachedFileList===null){
			if(static::hasAttachedFiles!==true)  {$this->_attachedFileList==[];}
			else{
				$this->_attachedFileList=Db::getTab("SELECT * FROM ap_objectAttachedFile WHERE objectType='".static::objectType."' AND _idObject=".$this->_id);	//Récupère les fichiers joints de l'objet en BDD
				foreach($this->_attachedFileList as $key=>$tmpFile)  {$this->_attachedFileList[$key]=self::attachedFileInfos($tmpFile);}						//Ajoute le "path" et autres infos de chaque fichier joint
			}
		}
		return (array)$this->_attachedFileList;
	}

	/********************************************************************************************************
	 * FICHIERS JOINTS : AFFICHE LES FICHIERS JOINTS  (dans un .menuContext OU .objContainer)
	 ********************************************************************************************************/
	public function attachedFileMenu($separator="<hr>")
	{
		if($this->_attachedFileMenu===null){
			$this->_attachedFileMenu="";
			foreach($this->attachedFileList() as $tmpFile){
				if(!empty($tmpFile["urlDownload"])){
					$this->_attachedFileMenu.='<div class="attachedFileMenu" onclick="confirmRedir(\''.$tmpFile["urlDownload"].'\',labelConfirmDownload)" '.Txt::tooltip("download").'>
													<img src="app/img/attachment.png"> '.$tmpFile["name"].'
												</div>';
				}
			}
		}
		if($this->_attachedFileMenu)  {return $separator.$this->_attachedFileMenu;}
	}

	/********************************************************************************************************
	 * FICHIERS JOINTS : AJOUTE DES FICHIERS   (cf. "VueObjAttachedFile.php")
	 ********************************************************************************************************/
	public function attachedFileAdd()
	{
		if(static::hasAttachedFiles==true){
			foreach($_FILES as $inputName=>$tmpFile){
				//Ajoute chaque fichier joint
				if(stristr($inputName,"attachedFile") && File::uploadControl($tmpFile)){
					//Ajoute en BDD + Déplace dans le dossier de destination
					$_idFile=Db::query("INSERT INTO ap_objectAttachedFile SET name=".Db::format($tmpFile["name"]).", objectType='".static::objectType."', _idObject=".$this->_id,  true);
					$filePath=PATH_OBJECT_ATTACHMENT.$_idFile.".".File::extension($tmpFile["name"]);
					$isMoved=move_uploaded_file($tmpFile["tmp_name"], $filePath);
					//Fichier ajouté
					if($isMoved==true){
						//Optimise si besoin l'image + chmod du fichier
						if(File::isType("imageResize",$filePath))  {File::imageResize($filePath,$filePath,1600);}
						File::setChmod($filePath);
						//Ajoute une Image / Vidéo / Mp3 dans la description de l'objet
						if(static::descriptionEditor==true && File::isType("editorInsert",$tmpFile["name"])){									//Vérif s'il s'agit d'un fichier inséré dans l'éditeur TinyMce							
							$inputCpt=str_replace("attachedFile", "", $inputName);																//Cpt de l'input : présent dans "attachedFileTagTmp" et "attachedFileSrcTmp"
							$displayUrl=self::attachedFileDisplayUrl($_idFile, $tmpFile["name"]);												//Url d'affichage du fichier
							$this->description=str_replace("attachedFileSrcTmp".$inputCpt, $displayUrl, $this->description);					//Remplace "attachedFileSrcTmp" par l'url d'affichage du fichier
							$this->description=str_replace("attachedFileTagTmp".$inputCpt, "attachedFileTag".$_idFile, $this->description);		//Remplace "attachedFileTagTmp" par le "attachedFileTag" final du fichier
							Db::query("UPDATE ".static::dbTable." SET `description`=".Db::format($this->description)." WHERE _id=".$this->_id);	//Update le texte de l'éditeur !
						}
					}
				}
			}
			File::datasFolderSize(true);//Recalcule $_SESSION["datasFolderSize"]
		}
	}

	/********************************************************************************************************
	 * FICHIERS JOINTS : SUPPRIME UN FICHIER JOINT
	 ********************************************************************************************************/
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

	/*****************************************************************************************************************
 	 * FICHIERS JOINTS : CHARGE LA DESCRIPTION HTML (DOM) POUR MODIFIER LES TAGS DES FICHIERS  (<a> <img> etc)
	 *****************************************************************************************************************/
	public function descriptionDOM()
	{
		libxml_use_internal_errors(true);													//Evite les erreurs et avertissements
		$dom=new DOMDocument;																//Créé un nouveau DOMDocument
		$dom->loadHTML($this->description, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);	//Charge la description HTML de l'objet (sans les éléments html/body...)
		libxml_clear_errors();																//Vide le buffer d'erreur libxml
		return $dom;																		//Renvoie le DOM de la description
	}

	/*****************************************************************************************************************************
	 * DESCRIPTION ENVOYEE PAR MAIL AVEC IMAGES : MODIFIE LA "SRC" DES IMAGES PAR UN "CID"  (ex: <img src="cid:attachedFile55">)
	 *****************************************************************************************************************************/
	public function descriptionMail()
	{
		$dom=$this->descriptionDOM();														//Récupère le DOM de la description
		$xpath=new DOMXPath($dom);															//Créé un XPath pour naviguer dans le DOM
		foreach($this->attachedFileList() as $tmpFile){										//Charge chaque image
			if(!empty($tmpFile["cid"])){													//Vérif si l'image contient un "cid"
				$nodes=$xpath->query('//img[@id="attachedFileTag'.$tmpFile["_id"].'"]');	//Trouve les tags <img> avec l'attribut id="attachedFileTagXX"
				foreach($nodes as $node){													//Parcourt chaque tag
					$node->setAttribute('src', 'cid:'.$tmpFile["cid"]);						//Update le "src" avec le "cid" de l'image
					$node->setAttribute('style', 'max-width:90%;');							//Ajoute un "max-width"
				}
			}
		}
		return $dom->saveHTML();
	}

	/********************************************************************************************************
	 * COMMENTAIRES : L'OBJET PEUT AVOIR DES COMMENTAIRES?
	 ********************************************************************************************************/
	public function hasUsersComment()
	{
		return (static::hasUsersComment && !empty(Ctrl::$agora->usersComment));
	}

	/********************************************************************************************************
	 * COMMENTAIRES : LISTE LES COMMENTAIRES
	 ********************************************************************************************************/
	public function getUsersComment()
	{
		if($this->_usersComment===null)
			{$this->_usersComment=Db::getTab("SELECT * FROM ap_objectComment WHERE objectType='".static::objectType."' AND _idObject=".$this->_id);}
		return $this->_usersComment;
	}

	/********************************************************************************************************
	 * COMMENTAIRES : DROIT D'ÉDITION/SUPPRESSION D'UN COMMENTAIRE
	 ********************************************************************************************************/
	public static function userCommentEditRight($_idComment)
	{
		if(!empty($_idComment)){
			$idUser=Db::getVal("SELECT _idUser FROM ap_objectComment WHERE _id=".(int)$_idComment);
			return (Ctrl::$curUser->isGeneralAdmin() || $idUser==Ctrl::$curUser->_id);
		}
	}

	/********************************************************************************************************
	 * LIKES : VERIF SI ON PEUT "LIKER" L'OBJET (fonction du type d'objet et du param. général)
	 ********************************************************************************************************/
	public function hasUsersLike()
	{
		return (static::hasUsersLike && !empty(Ctrl::$agora->usersLike));
	}

	/********************************************************************************************************
	 * LIKES : LISTE DES USERS AYANT "LIKÉ" L'OBJET
	 ********************************************************************************************************/
	public function getUsersLike()
	{
		if($this->_usersLike===null)
			{$this->_usersLike=Db::getObjTab("user", "SELECT * FROM ap_user WHERE _id IN (select _idUser as _id from ap_objectLike where objectType='".static::objectType."' AND _idObject=".$this->_id.")");}
		return $this->_usersLike;
	}

	/********************************************************************************************************
	 * LIKES : TOOLTIP DES USERS AYANT "LIKÉ" L'OBJET
	 ********************************************************************************************************/
	public function usersLikeTooltip()
	{
		$tooltip=Txt::trad("AGORA_usersLike").'<br>';
		foreach($this->getUsersLike() as $tmpUser)  {$tooltip.=$tmpUser->getLabel().", ";}
		return trim($tooltip,", ");
	}
}