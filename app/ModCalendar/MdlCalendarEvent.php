<?php
/**
* This file is part of the Agora-Project Software package.
*
* @copyright (c) Agora-Project Limited <https://www.agora-project.net>
* @license GNU General Public License, version 2 (GPL-2.0)
*/


/*
 * MODELE DES EVENEMENTS
 */
class MdlCalendarEvent extends MdlObject
{
	const moduleName="calendar";
	const objectType="calendarEvent";
	const dbTable="ap_calendarEvent";
	const MdlObjectContainer="MdlCalendar";
	const hasAttachedFiles=true;
	const hasNotifMail=true;
	const htmlEditorField="description";
	public static $requiredFields=["title","dateBegin","timeBegin","dateEnd","timeEnd"];
	public static $searchFields=["title","description"];
	private $_confirmedCalendars=null;
	private $_propositionCalendars=null;

	/*******************************************************************************************
	 * SURCHARGE : CONSTRUCTEUR
	*******************************************************************************************/
	function __construct($objIdOrValues=null)
	{
		parent::__construct($objIdOrValues);
		//Visibilité
		if(empty($this->contentVisible))	{$this->contentVisible="public";}
		//Categorie
		if(!empty($this->_idCat))	{$this->objCategory=Ctrl::getObj("calendarEventCategory",$this->_idCat);	$this->catColor=$this->objCategory->color;}
		else						{$this->objCategory=null;													$this->catColor="#444";}
		//Masque le title/description si besoin
		if($this->accessRight()<1){
			$this->title="<i>".Txt::trad("CALENDAR_evtPrivate")."</i>";
			$this->description=null;
		}
	}

	/*******************************************************************************************
	 * SURCHARGE : DROIT D'ACCÈS À UN ÉVÉNEMENT
	 * Ajoute le accessRight "0.5" qui permet juste de voir la plage horaire de l'evenement
	 *******************************************************************************************/
	public function accessRight()
	{
		//Init la mise en cache
		if($this->_accessRight===null)
		{
			////	DROIT D'ACCES TOTAL
			$this->_accessRight=parent::accessRight();
			////	SINON DROIT EN FONCTION DES AGENDAS AUQUELS L'ÉVÉNEMENT EST AFFECTÉ
			if($this->_accessRight<3)
			{
				//Récupère le droit maximum en fonction des affectations aux agendas
				$tmpMaxRight=0;
				$allCalendarsFullAccess=true;
				foreach($this->affectedCalendars() as $objCalendar){
					if($objCalendar->accessRight()>$tmpMaxRight)	{$tmpMaxRight=$objCalendar->accessRight();}	//Droit de l'agenda > droit max temporaire
					if($objCalendar->editContentRight()==false)		{$allCalendarsFullAccess=false;}			//L'agenda n'est pas accessible en écriture
				}
				//Attribut le droit d'accès final
				$tmpAccessRight=0;
				if($allCalendarsFullAccess==true)								{$tmpAccessRight=3;}	//Que des agendas accessibles en écriture
				elseif($tmpMaxRight>=2)											{$tmpAccessRight=2;}	//Un agenda (ou+) accessible en écriture
				elseif($tmpMaxRight>=1 && $this->contentVisible=="public")		{$tmpAccessRight=1;}	//Un agenda (ou+) accessible en lecture ou ecriture limité
				elseif($tmpMaxRight>=1 && $this->contentVisible=="public_cache"){$tmpAccessRight=0.5;}	//Idem mais "public_cache" : lecture plage horaire uniquement!
				//Surcharge le droit d'accès?
				if($tmpAccessRight > $this->_accessRight)	{$this->_accessRight=$tmpAccessRight;}
			}
		}
		//Retourne le résultat en float et "cast" aussi les integer (cf. droit "0.5" de l'option "public_cache")
		return (float)$this->_accessRight;
	 }

	/*******************************************************************************************
	 * SURCHARGE : DROIT DE SUPPRIMER UN EVT ("fullRight" UNIQUEMENT)  OU  DÉSAFFECTER UN EVT D'UN AGENDA (cf. "CtrlObject::actionDelete()")
	 *******************************************************************************************/
	public function deleteRight()
	{
		return ($this->fullRight()  ||  (Req::isParam("_idCalDeleteOn") && $this->deleteAffectationRight(Req::getParam("_idCalDeleteOn"))));
	}

	/*******************************************************************************************
	 * DROIT DE DÉSAFFECTER L'ÉVÉNEMENT D'UN AGENDA SPÉCIFIQUE ("fullRight" POUR LES RÉINIT D'AFFECTATION LORS D'UNE MODIF D'EVT)
	 *******************************************************************************************/
	public function deleteAffectationRight($_idCal)
	{
		return ($this->fullRight()  ||  Ctrl::getObj("calendar",$_idCal)->editContentRight());
	}

	/*******************************************************************************************
	 * SURCHARGE : SUPPRESSION D'EVENEMENT
	 *******************************************************************************************/
	public function delete()
	{
		//Supprime sur un agenda spécifique  ||  Supprime un evt périodique à une date précise  ||  Suppression de l'evt sur tous les agendas
		if(Req::isParam("_idCalDeleteOn") && $this->deleteAffectationRight(Req::getParam("_idCalDeleteOn")))	{$this->deleteAffectation(Req::getParam("_idCalDeleteOn"));}
		elseif(Req::isParam("periodDateExceptionsAdd") && $this->fullRight())									{Db::query("UPDATE ap_calendarEvent SET periodDateExceptions=".Db::format($this->periodDateExceptions."@@".Req::getParam("periodDateExceptionsAdd")."@@")." WHERE _id=".$this->_id);}
		elseif($this->fullRight())																				{Db::query("DELETE FROM ap_calendarEventAffectation WHERE _idEvt=".$this->_id);}
		//On supprime l'événement s'il est affecté à aucun agenda
		if(Db::getVal("SELECT count(*) FROM ap_calendarEventAffectation WHERE _idEvt=".$this->_id)==0)  {parent::delete();}
	}

	/*******************************************************************************************
	 * SUPPRIME UNE AFFECTATION À UN AGENDA
	 *******************************************************************************************/
	public function deleteAffectation($_idCal, $reinitAffectations=false)
	{
		//Vérif le droit de supprimer l'affectation
		if($this->deleteAffectationRight($_idCal)){
			//Supprime l'affectation  &&  Supprime l'evt s'il n'est affecté à aucun agenda et qu'on ne modifie pas l'evt (cf. $reinitAffectations via "actionCalendarEventEdit()"). Ne pas utiliser de "$this->delete()" (sinon ça boucle)
			Db::query("DELETE FROM ap_calendarEventAffectation WHERE _idEvt=".$this->_id." AND _idCal=".(int)$_idCal);
			if($reinitAffectations==false && Db::getVal("SELECT count(*) FROM ap_calendarEventAffectation WHERE _idEvt=".$this->_id)==0)  {parent::delete();}
		}
	}

	/*******************************************************************************************
	 * SURCHARGE : RECUPÈRE L'AGENDA PRINCIPAL DE L'ÉVÉNEMENT (AGENDA PERSO OU AGENDA AVEC LE DROIT D'ACCÈS LE PLUS ÉLEVÉ)
	 *******************************************************************************************/
	public function containerObj()
	{
		if($this->_containerObj===null){
			$tmpAccessRight=0;
			foreach($this->affectedCalendars() as $tmpCal){
				if($tmpCal->curUserPerso())							{$this->_containerObj=$tmpCal;	break;}
				elseif($tmpAccessRight < $tmpCal->accessRight())	{$this->_containerObj=$tmpCal;	$tmpAccessRight=$tmpCal->accessRight();}
			}
		}
		return $this->_containerObj;
	}

	/*******************************************************************************************
	 * SURCHARGE : URL D'ACCÈS
	 *******************************************************************************************/
	public function getUrl($display=null)
	{
		//Url par défaut (en fonction de $display)
		if(!empty($display))  {return parent::getUrl($display);}
		//Surcharge : Affiche l'evt à la bonne date et si besoin dans l'agenda principal
		else{
			//Url du module à la bonne date 
			$url="?ctrl=".static::moduleName."&curTime=".strtotime($this->dateBegin);
			//Spécifie si besoin l'agenda principal (affichage de "plugin", url accès direct.. mais inutile après un delete d'evt, sinon on perd la liste des agendas en cours d'affichage)
			if(Req::$curAction!="delete" && $this->containerObj())  {$url.="&displayedCalendars[]=".$this->containerObj()->_id;}
			return $url;
		}
	}

	/*******************************************************************************************
	 * AGENDAS (OBJETS) OÙ L'EVENEMENT EST AFFECTÉ
	 * $confirmed = true / false / "all" pour récupérer toutes les affectations
	 *******************************************************************************************/
	public function affectedCalendars($confirmed=true)
	{
		if($this->_confirmedCalendars===null){
			$sqlAffectations="SELECT * FROM ap_calendar WHERE _id in (select _idCal as _id from ap_calendarEventAffectation T2 WHERE _idEvt=".$this->_id;
			$this->_confirmedCalendars=Db::getObjTab("calendar",$sqlAffectations." and confirmed=1)");
			$this->_propositionCalendars=Db::getObjTab("calendar", $sqlAffectations." and confirmed is null)");
		}
		if($confirmed===true)		{return $this->_confirmedCalendars;}
		elseif($confirmed===false)	{return $this->_propositionCalendars;}
		elseif($confirmed=="all")	{return array_merge($this->_confirmedCalendars,$this->_propositionCalendars);}
	}

	/*******************************************************************************************
	 * TEXTE DES AGENDAS OÙ L'EVENEMENT EST AFFECTÉ + CEUX OU IL EST EN ATTENTE DE CONFIRMATION
	 *******************************************************************************************/
	public function affectedCalendarsLabel()
	{
		if(Ctrl::$curUser->isUser())
		{
			$calendarsConfirmed=$calendarsProposed=null;
			foreach($this->affectedCalendars(true) as $objCalendar)		{$calendarsConfirmed.=", <i>".$objCalendar->title."</i>";}
			foreach($this->affectedCalendars(false) as $objCalendar)	{$calendarsProposed.=", <i>".$objCalendar->title."</i>";}
			if(!empty($calendarsConfirmed))	{$calendarsConfirmed=Txt::trad("CALENDAR_evtAffects")." ".trim($calendarsConfirmed,",")."<br>";}
			if(!empty($calendarsProposed))	{$calendarsProposed=Txt::trad("CALENDAR_evtAffectToConfirm")." ".trim($calendarsProposed,",");}
			return $calendarsConfirmed.$calendarsProposed;
		}
	}

	/*******************************************************************************************
	 * SURCHARGE : MENU CONTEXTUEL
	 *******************************************************************************************/
	public function contextMenu($options=null)
	{
		//// Evt dans plusieurs agendas
		if(count($this->affectedCalendars())>1){
			//Remplace "supprimer" par "Supprimer dans tous les agendas"
			$options["deleteLabel"]=Txt::trad("CALENDAR_deleteEvtCals");
			//Si l'agenda "courant" peut être édité : ajoute "Supprimer uniquement dans cet agenda"
			if(!empty($options["_idCal"]) && Ctrl::getObj("calendar",$options["_idCal"])->editRight())  {$options["specificOptions"][]=["actionJs"=>"confirmDelete('".$this->getUrl("delete")."&_idCalDeleteOn=".$options["_idCal"]."')", "iconSrc"=>"delete.png", "label"=>Txt::trad("CALENDAR_deleteEvtCal")];}
		}
		//// Evt périodique et date spécifiée : ajoute "Supprimer uniquement à cette date"
		if(!empty($options["curDateTime"]) && !empty($this->periodType) && $this->fullRight())  {$options["specificOptions"][]=["actionJs"=>"confirmDelete('".$this->getUrl("delete")."&periodDateExceptionsAdd=".date("Y-m-d",$options["curDateTime"])."')", "iconSrc"=>"delete.png", "label"=>Txt::trad("CALENDAR_deleteEvtDate")];}
		//// Liste des agendas où est affecté l'evenement
		$options["specificLabels"][]=["label"=>$this->affectedCalendarsLabel()];
		//// Renvoie le menu surchargé
		return parent::contextMenu($options);
	}

	/*******************************************************************************************
	 * SURCHARGE : LISTE DES USERS AFFECTÉS À L'OBJET (USERS DE CHAQUE AGENDA OU L'EVT EST AFFETÉ)
	 *******************************************************************************************/
	public function affectedUserIds()
	{
		$affectedUserIds=[];
		foreach($this->affectedCalendars("all") as $tmpCal)  {$affectedUserIds=array_merge($tmpCal->affectedUserIds(),$affectedUserIds);}
		return array_unique($affectedUserIds);
	}

	/*******************************************************************************************
	 * VÉRIFIE S'IL S'AGIT D'UN EVENEMENT PASSÉ (PREND EN COMPTE LA PÉRIODICITÉ)
	 *******************************************************************************************/
	public function isOldEvt($referenceTime)
	{
		return ((int)$referenceTime>0 && strtotime($this->dateEnd)<$referenceTime  &&  (empty($this->periodType) || (!empty($this->periodDateEnd) && strtotime($this->periodDateEnd)<$referenceTime)));
	}
}