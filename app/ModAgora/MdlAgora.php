<?php
/**
* This file is part of the Agora-Project Software package
*
* @copyleft Agora-Project <https://www.agora-project.net>
* @license GNU General Public License (GPL-2.0)
*/


/*
 * Modele de la config de l'Agora
 */
class MdlAgora extends MdlObject
{
	const moduleName="agora";
	const objectType="agora";
	const dbTable="ap_agora";

	/********************************************************************************************************
	 * SURCHARGE : CONSTRUCTEUR
	 ********************************************************************************************************/
	public function __construct()
	{
		//Récupère les paramètres en Bdd
		parent::__construct(Db::getLine("select * from ap_agora"));
		if(empty($this->personsSort))  {$this->personsSort="name";}
	}

	/********************************************************************************************************
	 * PATH DU LOGO EN BAS DE PAGE
	 ********************************************************************************************************/
	public function pathLogoFooter()
	{
		return (!empty($this->logo) && is_file(PATH_DATAS.$this->logo))  ?  PATH_DATAS.$this->logo  :  "app/img/logoSmall.png";
	}

	/********************************************************************************************************
	 * PATH DU LOGO DE PAGE DE CONNEXION
	 ********************************************************************************************************/
	public function pathLogoConnect()
	{
		return (!empty($this->logoConnect) && is_file(PATH_DATAS.$this->logoConnect))  ?  PATH_DATAS.$this->logoConnect  :  null;
	}

	/********************************************************************************************************
	 * VISIO JITSI : URL ALEATOIRE VERS LE SERVEUR DE VISIO (ex: "www.server.tv/visio-AF1GH2")
	 ********************************************************************************************************/
	public function visioUrl()
	{
		if(!empty($this->visioHost)){
			$visioId=str_shuffle("ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890");	//$visioId de 6 caractères aléatoires
			return $this->visioHost."/visio-".substr($visioId,0,6);			//Url avec $visioId
		}
	}

	/********************************************************************************************************
	 * VISIO JITSI : VERIF S'IL EST ACTIVÉ (URL DISPONIBLE)
	 ********************************************************************************************************/
	public function visioEnabled()
	{
		return (!empty($this->visioHost));
	}

	/********************************************************************************************************
	 * GOOGLE OAUTH : VERIF S'IL EST ACTIVÉ
	 ********************************************************************************************************/
	public function gOAuthEnabled()
	{
		return (Req::isMobileApp()==false && !empty($this->gIdentity) && !empty($this->gIdentityClientId));
	}

	/********************************************************************************************************
	 * GOOGLE MAP : VERIF S'IL EST ACTIVÉ
	 ********************************************************************************************************/
	public function gMapsEnabled()
	{
		return (!empty($this->gApiKey) && $this->mapTool=="gmap");
	}
}