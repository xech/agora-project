<?php
/**
* This file is part of the Agora-Project Software package.
*
* @copyright (c) Agora-Project Limited <https://www.agora-project.net>
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

	/*
	 * SURCHARGE : Constructeur
	 */
	function __construct()
	{
		parent::__construct(Db::getLine("select * from ap_agora"));
		if(empty($this->personsSort))  {$this->personsSort="name";}
	}

	/*
	 * PATH DU LOGO EN BAS DE PAGE
	 */
	public function pathLogoFooter()
	{
		return (!empty($this->logo) && is_file(PATH_DATAS.$this->logo))  ?  PATH_DATAS.$this->logo  :  "app/img/logo.png";
	}

	/*
	 * PATH DU LOGO DE PAGE DE CONNEXION
	 */
	public function pathLogoConnect()
	{
		return (!empty($this->logoConnect) && is_file(PATH_DATAS.$this->logoConnect))  ?  PATH_DATAS.$this->logoConnect  :  null;
	}

	/*
	 * Si "gSignin" est activé et "ClientId" est renseigné : renvoi le "ClientId"
	 */
	public function gSigninClientId()
	{
		if(!empty($this->gSignin) && Ctrl::isHost())						{return Host::gSigninClientId();}
		elseif(!empty($this->gSignin) && !empty($this->gSigninClientId))	{return $this->gSigninClientId;}
		else																{return false;}
	}
	//"gSignin" Activé?
	public function gSigninEnabled()
	{
		return ($this->gSigninClientId() && Req::isMobileApp()==false);
	}

	/*
	 * Si "gSigninClientId" et "gPeopleApiKey" sont renseignés : renvoi le "gPeopleApiKey"
	 */
	public function gPeopleApiKey()
	{
		if(Ctrl::isHost())														{return Host::gPeopleApiKey();}
		elseif(!empty($this->gSigninClientId) && !empty($this->gPeopleApiKey))	{return $this->gPeopleApiKey;}
		else																	{return false;}
	}
	public function gPeopleEnabled()//API People Activé?
	{
		return ($this->gPeopleApiKey() && Req::isMobileApp()==false);
	}

	/*
	 * Si "mapApiKey" est renseigné : renvoi le "mapApiKey"
	 */
	public function gMapsApiKey()
	{
		if(Ctrl::isHost())					{return Host::gMapsApiKey();}
		elseif(!empty($this->mapApiKey))	{return $this->mapApiKey;}
		else								{return false;}
	}
	public function gMapsEnabled()//API Maps Activé?
	{
		return ($this->gMapsApiKey() && $this->mapTool=="gmap");
	}
}