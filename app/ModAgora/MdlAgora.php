<?php
/**
* This file is part of the Agora-Project Software package
*
* @copyleft Agora-Project <https://www.agora-project.net>
* @license GNU General Public License, version 2 (GPL-2.0)
*/


/*
 * Modele de la config de l'Agora
 */
class MdlAgora extends MdlObject
{
	const moduleName="agora";
	const objectType="agora";
	const dbTable="ap_agora";

	/*******************************************************************************************
	 * SURCHARGE : CONSTRUCTEUR
	 *******************************************************************************************/
	function __construct()
	{
		//Récupère les paramètres en Bdd
		parent::__construct(Db::getLine("select * from ap_agora"));
		if(empty($this->personsSort))  {$this->personsSort="name";}
	}

	/*******************************************************************************************
	 * PATH DU LOGO EN BAS DE PAGE
	 *******************************************************************************************/
	public function pathLogoFooter()
	{
		return (!empty($this->logo) && is_file(PATH_DATAS.$this->logo))  ?  PATH_DATAS.$this->logo  :  "app/img/logoFooter.png";
	}

	/*******************************************************************************************
	 * PATH DU LOGO DE PAGE DE CONNEXION
	 *******************************************************************************************/
	public function pathLogoConnect()
	{
		return (!empty($this->logoConnect) && is_file(PATH_DATAS.$this->logoConnect))  ?  PATH_DATAS.$this->logoConnect  :  null;
	}

	/*******************************************************************************************
	 * VISIO JITSI : URL DU SERVEUR DE VISIO AVEC LE NOM DE LA "ROOM" (ex: "room-AFTKEOPDRF")
	 *******************************************************************************************/
	public function visioUrl()
	{
		if(!empty($this->visioHost)){
			$roomId=str_shuffle("ABCDEFGHIJKLMNOPQRSTUVWXYZ");													//$roomId de 8 caractères aléatoires
			return $this->visioHost."/".(Req::isHost()?"visio-room":"omnispace-room")."-".substr($roomId,0,8);	//Url avec un préfixe + $roomId
		}
	}

	/*******************************************************************************************
	 * VISIO JITSI : VERIF S'IL EST ACTIVÉ (URL DISPONIBLE)
	 *******************************************************************************************/
	public function visioEnabled()
	{
		return (!empty($this->visioHost));
	}

	/*******************************************************************************************
	 * GOOGLE IDENTITY/OAUTH : VERIF S'IL EST ACTIVÉ
	 *******************************************************************************************/
	public function gIdentityEnabled()
	{
		return (Req::isMobileApp()==false && !empty($this->gIdentityClientId) && !empty($this->gIdentity));
	}

	/*******************************************************************************************
	 * GOOGLE CONTACTS/INVITATIONS : VERIF S'IL EST ACTIVÉ
	 *******************************************************************************************/
	public function gPeopleEnabled()
	{
		return (Req::isMobileApp()==false && !empty($this->gIdentityClientId) && !empty($this->gPeopleApiKey));
	}

	/*******************************************************************************************
	 * GOOGLE MAP : VERIF S'IL EST ACTIVÉ
	 *******************************************************************************************/
	public function gMapsEnabled()
	{
		return (!empty($this->mapApiKey) && $this->mapTool=="gmap");
	}
}