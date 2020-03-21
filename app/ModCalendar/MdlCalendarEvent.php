<?php
/**
* This file is part of the Agora-Project Software package.
*
* @copyright (c) Agora-Project Limited <https://www.agora-project.net>
* @license GNU General Public License, version 2 (GPL-2.0)
*/


/*
 * Modele des evenements
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
	public static $requiredFields=array("title","dateBegin","timeBegin","dateEnd","timeEnd");
	public static $searchFields=array("title","description");
	private $_confirmedCalendars=null;
	private $_propositionCalendars=null;

	/*
	 * SURCHARGE : Constructeur
	*/
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

	/*
	 * SURCHARGE : Droit d'accès à un événement
	 * Ajoute le accessRight "0.5" qui permet juste de voir la plage horaire de l'evenement
	 */
	public function accessRight()
	{
		//Init la mise en cache
		if($this->_accessRight===null)
		{
			//Droit par défaut
			$this->_accessRight=parent::accessRight();
			if($this->_accessRight<3)
			{
				//Droit en fonction des agendas auquels l'événement est affecté : supérieur?
				$tmpAccessRight=$tmpMaxRight=0;
				$allCalendarsFullAccess=true;
				foreach($this->affectedCalendars() as $objCalendar){
					if($objCalendar->accessRight()>$tmpMaxRight)	{$tmpMaxRight=$objCalendar->accessRight();}//Droit de l'agenda > droit max temporaire
					if($objCalendar->editFullContentRight()==false)	{$allCalendarsFullAccess=false;}//L'agenda pas accessible en écriture
				}
				if($allCalendarsFullAccess==true)								{$tmpAccessRight=3;}	//Que des agendas accessibles en écriture
				elseif($tmpMaxRight>=2)											{$tmpAccessRight=2;}	//Au moins 1 agenda accessible en écriture
				elseif($tmpMaxRight>=1 && $this->contentVisible=="public")		{$tmpAccessRight=1;}	//Au moins 1 agenda accessible en lecture/ecriture limité
				elseif($tmpMaxRight>=1 && $this->contentVisible=="public_cache"){$tmpAccessRight=0.5;}	//Au moins 1 agenda accessible en lecture/ecriture limité  :  lecture plage horaire uniquement!
				//Surcharge le droit d'accès?
				if($tmpAccessRight > $this->_accessRight)	{$this->_accessRight=$tmpAccessRight;}
			}
		}
		return $this->_accessRight;
 	}

	/*
	 * SURCHARGE : suppression d'evenement
	 */
	public function delete()
	{
		//Id de l'agenda à désaffecter de l'evt
		$calDeleteOn=(Req::isParam("_idCalDeleteOn"))  ?  Ctrl::getObj("calendar",Req::getParam("_idCalDeleteOn"))  :  null;
		//Supprime sur un agenda spécifique ("deleteAffectation()")  ||  Supprime un evt périodique à une date précise ("periodDateExceptions")  ||  Suppression de l'evt sur tous les agendas ("DELETE FROM ap_calendarEventAffectation")
		if(!empty($calDeleteOn) && ($this->fullRight() || $calDeleteOn->editRight()))	{$this->deleteAffectation($calDeleteOn->_id);}
		elseif(Req::isParam("periodDateExceptionsAdd") && $this->fullRight())			{Db::query("UPDATE ap_calendarEvent SET periodDateExceptions=".Db::format($this->periodDateExceptions."@@".Req::getParam("periodDateExceptionsAdd")."@@")." WHERE _id=".$this->_id);}
		elseif($this->fullRight())														{Db::query("DELETE FROM ap_calendarEventAffectation WHERE _idEvt=".$this->_id);}
		//On supprime l'événement s'il est affecté à aucun agenda
		if(Db::getVal("SELECT count(*) FROM ap_calendarEventAffectation WHERE _idEvt=".$this->_id)==0)  {parent::delete();}
	}

	/*
	 * SURCHARGE : Recupère l'agenda principal de l'événement
	 * Priorité des agendas :  Agenda personnel >> Agenda en "fullRight()" >> Agenda en "editContentRight()" >> Agenda en "readRight()"
	 */
	public function containerObj()
	{
		if($this->_containerObj===null)
		{
			$tmpPriority=0;
			foreach($this->affectedCalendars() as $tmpCal){
				if($tmpCal->isMyPerso())								{$this->_containerObj=$tmpCal;	break;}
				elseif($tmpPriority<3 && $tmpCal->accessRight()==3)		{$this->_containerObj=$tmpCal;	$tmpPriority=3;}
				elseif($tmpPriority<2 && $tmpCal->accessRight()==2)		{$this->_containerObj=$tmpCal;	$tmpPriority=2;}
				elseif($tmpPriority==0 && $tmpCal->accessRight()>0)		{$this->_containerObj=$tmpCal;	$tmpPriority=1;}
			}
		}
		return $this->_containerObj;
	}

	/*
	 * SURCHARGE : Url d'accès
	 */
	public function getUrl($display=null)
	{
		//Url de l'evt (edit/vue/etc) : "getUrl()" parent   || Delete d'evt : page principale à la date de l'evt   ||  Conteneur de l'evt : page principale à la date de l'evt et avec les agendas concernés
		if($display!="container")					{return parent::getUrl($display);}
		elseif(Req::$curAction=="delete")			{return "?ctrl=".static::moduleName."&curTime=".strtotime($this->dateBegin);}
		elseif(is_object($this->containerObj()))	{return "?ctrl=".static::moduleName."&curTime=".strtotime($this->dateBegin)."&displayedCalendars[]=".$this->containerObj()->_id;}
	}

	/*
	 * Agendas (objets) où l'evenement est affecté
	 * $confirmed = true / false / "all" pour récupérer toutes les affectations
	 */
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

	/*
	 * Texte des agendas où l'evenement est affecté + ceux ou il est en attente de confirmation
	 */
	public function affectedCalendarsLabel()
	{
		if(Ctrl::$curUser->isUser())
		{
			$calendarsConfirmed=$calendarsUnconfirmed=null;
			foreach($this->affectedCalendars(true) as $objCalendar)		{$calendarsConfirmed.=", ".$objCalendar->title;}
			foreach($this->affectedCalendars(false) as $objCalendar)	{$calendarsUnconfirmed.=", ".$objCalendar->title;}
			if(!empty($calendarsConfirmed))		{$calendarsConfirmed=Txt::trad("CALENDAR_evtAffects")." ".trim($calendarsConfirmed,",")."<br>";}
			if(!empty($calendarsUnconfirmed))	{$calendarsUnconfirmed=Txt::trad("CALENDAR_evtAffectToConfirm")." ".trim($calendarsUnconfirmed,",");}
			return $calendarsConfirmed.$calendarsUnconfirmed;
		}
	}

	/*
	 * SURCHARGE : droit de suppression => fullRight
	 */
	public function deleteRight()
	{
		return ($this->fullRight() && $this->isNew()==false);
	}

	/*
	 * SURCHARGE : Menu contextuel
	 */
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

	/*
	 * SURCHARGE : Liste des users affectés à l'objet (users de chaque agenda ou l'evt est affeté)
	 */
	public function affectedUserIds()
	{
		$affectedUserIds=[];
		foreach($this->affectedCalendars("all") as $tmpCal)  {$affectedUserIds=array_merge($tmpCal->affectedUserIds(),$affectedUserIds);}
		return array_unique($affectedUserIds);
	}

	/*
	 * Vérifie s'il s'agit d'un evenement passé. Prend éventuellement en compte la périodicité !
	 */
	public function isOldEvt($referenceTime)
	{
		return ((int)$referenceTime>0 && strtotime($this->dateEnd)<$referenceTime  &&  (empty($this->periodType) || (!empty($this->periodDateEnd) && strtotime($this->periodDateEnd)<$referenceTime)));
	}

	/*
	 * Supprime une affectation à un agenda
	 */
	public function deleteAffectation($_idCal, $editEvtReinitAffectations=false)
	{
		//Vérif l'accès total à l'evt OU l'accès en écriture au contenu de l'agenda
		if($this->fullRight() || Ctrl::getObj("calendar",$_idCal)->editContentRight())
		{
			//Supprime l'affectation à l'agenda en question
			Db::query("DELETE FROM ap_calendarEventAffectation WHERE _idEvt=".$this->_id." AND _idCal=".(int)$_idCal);
			//Supprime l'evt s'il n'est affecté à aucun agenda (sauf en cas de modif d'evt et de réinitialisation des affectations, via "actionCalendarEventEdit()")
			if($editEvtReinitAffectations==false && count(Db::getTab("SELECT * FROM ap_calendarEventAffectation WHERE _idEvt=".$this->_id))==0)  {parent::delete();}//Pas de "$this->delete();" (sinon boucle infinie!)
		}
	}
}