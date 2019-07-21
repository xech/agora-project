<?php
/**
* This file is part of the Agora-Project Software package.
*
* @copyright (c) Agora-Project Limited <https://www.agora-project.net>
* @license GNU General Public License, version 2 (GPL-2.0)
*/


/*
 * Controleur pour les opérations diverses
 */
class CtrlMisc extends Ctrl
{
	//Pas d'initialisation complete du controleur
	protected static $initCtrlFull=false;

	/*
	 * ACTION : Recherche d'objets sur tous l'espace
	 */
	public static function actionSearch()
	{
		//Init
		$vDatas=[];
		//Pour module, on liste les types d'objets et leurs champs de recherche : modifie le "title" et le "checked"
		$vDatas["searchFields"]=[];
		foreach(self::$curSpace->moduleList() as $tmpModule)
		{
			if(method_exists($tmpModule["ctrl"],"plugin")){
				foreach($tmpModule["ctrl"]::$MdlObjects as $tmpMdlObject){
					foreach($tmpMdlObject::$searchFields as $tmpField){
						$vDatas["searchFields"][$tmpField]["checked"]=(!Req::isParam("searchFields") || in_array($tmpField,Req::getParam("searchFields"))) ? "checked" : "";
						if(empty($vDatas["searchFields"][$tmpField]["title"]))	{$vDatas["searchFields"][$tmpField]["title"]="";}
						$folderInTitle=preg_match("/".Txt::trad("objectFolder")."/i",$vDatas["searchFields"][$tmpField]["title"]);
						if($tmpMdlObject::isFolder==true && $folderInTitle==false)	{$vDatas["searchFields"][$tmpField]["title"].="- ".Txt::trad("OBJECTfolder")."<br>";}
						elseif($tmpMdlObject::isFolder==false)						{$vDatas["searchFields"][$tmpField]["title"].="- ".Txt::trad("OBJECT".$tmpMdlObject::objectType)."<br>";}
					}
				}
			}
		}
		//Resultat de recherche
		if(Req::isParam("formValidate"))
		{
			//Prépare la recherche
			$vDatas["pluginsList"]=[];
			$pluginParams=array("type"=>"search", "searchText"=>Txt::clean(Req::getParam("searchText")), "searchMode"=>Req::getParam("searchMode"), "searchFields"=>Req::getParam("searchFields"), "creationDate"=>Req::getParam("creationDate"), "searchModules"=>Req::getParam("searchModules"));
			//Récupère les résultats
			foreach(self::$curSpace->moduleList() as $tmpModule){
				if(method_exists($tmpModule["ctrl"],"plugin") && in_array($tmpModule["ctrl"]::moduleName,Req::getParam("searchModules"))){
					$vDatas["pluginsList"]=array_merge($vDatas["pluginsList"], $tmpModule["ctrl"]::plugin($pluginParams));
				}
			}
			//Garde les termes de la recherche en session
			$_SESSION["searchText"]=Req::getParam("searchText");
		}
		//Affiche la vue
		static::displayPage(Req::commonPath."/VueSearch.php",$vDatas);
	}

	/*
	 * AJAX : Livecounters (principal/messenger) et Messages du messenger
	 */
	public static function actionLivecounterUpdate()
	{
		//Messenger activé?
		if(self::$curUser->messengerEnabled())
		{
			////	UPDATE EN BDD LE LIVECOUNTER DE L'USER COURANT, AVEC "editObjId" SI MODIF EN COURS D'UN OBJET. ON AJOUTE "editorDraft" UNIQUEMENT S'IL EST PRECISE
			$sqlValues="_idUser=".(int)self::$curUser->_id.", ipAdress=".Db::format($_SERVER["REMOTE_ADDR"]).", editObjId=".Db::formatParam("editObjId").", date=".Db::format(time());
			if(Req::isParam("editorDraft"))  {$sqlValues.=", editorDraft=".Db::formatParam("editorDraft","editor").", draftTargetObjId=".Db::formatParam("editObjId");}//Ajouter uniquement si "editorDraft" est présent, sinon le "livecounterUpdate" efface le 'draft' et n'a donc plus d'intérêt..
			Db::query("INSERT INTO ap_userLivecouter SET ".$sqlValues." ON DUPLICATE KEY UPDATE ".$sqlValues);

			////	INIT LE MESSENGER EN DEBUT DE SESSION
			if(!isset($_SESSION["livercounterUsers"]))
			{
				//Supprime les vieux messages du messenger (24h maxi) & livecounters des users déconnectés
				Db::query("DELETE FROM ap_userMessengerMessage WHERE date < ".intval(time()-MESSENGER_TIMEOUT));
				Db::query("DELETE FROM ap_userLivecouter WHERE date < ".intval(time()-MESSENGER_TIMEOUT));//24h aussi car on conserve le "editorDraft" du tinyMce, même si le "LIVECOUNTER_TIMEOUT" ne dure que quelques secondes..
				//Garde en session les users que l'on peut voir et n'ayant pas bloqué leur messenger
				$idsUsers=[0];//pseudo user
				foreach(self::$curUser->usersVisibles() as $tmpUser)  {$idsUsers[]=$tmpUser->_id;}
				$messengerUsersSql=Db::getCol("SELECT _id FROM ap_user WHERE _id!=".self::$curUser->_id." AND _id IN (".implode(",",$idsUsers).") AND _id IN (select _idUserMessenger from ap_userMessenger where allUsers=1 or _idUser=".self::$curUser->_id.")");
				$_SESSION["messengerUsersSql"]=implode(",", array_merge($messengerUsersSql,[0]));//ajoute le pseudo user "0"
				//Init les users connectés  &  La date de dernier affichage de chaque users  &  Les livecounters  &  La liste des messages
				$_SESSION["livercounterUsers"]=$_SESSION["messengerUpdateDisplayedUser"]=$_SESSION["messengerMessagesList"]=[];
				$_SESSION["livecounterMainHtml"]=$_SESSION["livecounterMessengerHtml"]=$_SESSION["messengerMessagesHtml"]="";
				$initLivecounterSession=true;
			}

			////	LIVECOUNTERS : LISTE DES USERS
			//Verif si ya un changement du livecounter (connection/deconnection)
			$livercounterUsersOld=$_SESSION["livercounterUsers"];
			$_SESSION["livercounterUsers"]=Db::getObjTab("user", "SELECT DISTINCT T1.* FROM ap_user T1, ap_userLivecouter T2 WHERE T1._id=T2._idUser AND T1._id IN (".$_SESSION["messengerUsersSql"].") AND T2.date > ".intval(time()-LIVECOUNTER_TIMEOUT));
			$result["livercounterChanged"]=(count($livercounterUsersOld)!=count($_SESSION["livercounterUsers"]));//ne pas comparer directement les tableaux d'objet.. mais leur taille (uliliser plutot "mb_strlen(serialize($array))" ?)
			//Affichage des users connectés
			if($result["livercounterChanged"]==true)
			{
				$_SESSION["livecounterMainHtml"]=$_SESSION["livecounterMessengerHtml"]="";
				foreach($_SESSION["livercounterUsers"] as $tmpUser)
				{
					//Affichage des livecounters
					$userImg=$tmpUser->hasImg()  ?  $tmpUser->getImg(false,true)."&nbsp;"  :  null;
					$userTitle=$tmpUser->getLabel()."<br>".Txt::trad("MESSENGER_connectedSince")." ".date("H:i",$tmpUser->lastConnection);
					$_SESSION["livecounterMainHtml"].="<label onclick='messengerDisplay(".$tmpUser->_id.");' title=\"".$userTitle."\" data-idUser=".$tmpUser->_id.">".$userImg.$tmpUser->getLabel("firstName")."</label>";
					$_SESSION["livecounterMessengerHtml"].="<div class='vMessengerUser'>
																<input type='checkbox' name='messengerPostUsers' value='".$tmpUser->_id."' id='messengerUserBox".$tmpUser->_id."'>
																<label for='messengerUserBox".$tmpUser->_id."' title=\"".Txt::trad("select")." ".$userTitle."\">".$userImg.$tmpUser->getLabel("firstName")."</label>
															</div>";
					//On vient de se connecter : ajoute au "messengerUpdateDisplayedUser" pour pas avoir de pulsate si ya des messages d'une ancienne session..
					if(!in_array($tmpUser->_id,$_SESSION["messengerUpdateDisplayedUser"]) && isset($initLivecounterSession))   {$_SESSION["messengerUpdateDisplayedUser"][$tmpUser->_id]=time();}
				}
			}

			////	MESSAGES DU MESSENGER
			$result["messengerPulsateUsers"]=[];
			if(!empty($_SESSION["livercounterUsers"]))
			{
				//Verif si ya de nouveaux messages
				$messengerMessagesListOld=$_SESSION["messengerMessagesList"];
				$_SESSION["messengerMessagesList"]=Db::getTab("SELECT * FROM ap_userMessengerMessage WHERE _idUsers LIKE '%@".self::$curUser->_id."@%' ORDER BY date asc");
				$result["messengerChanged"]=(count($messengerMessagesListOld)!=count($_SESSION["messengerMessagesList"]));
				//Affichage des messages (Init OU nouveaux messages)
				if($result["messengerChanged"]==true)
				{
					$_SESSION["messengerMessagesHtml"]="";
					foreach($_SESSION["messengerMessagesList"] as $message)
					{
						//Init l'affichage
						$objAutor=self::getObj("user",$message["_idUser"]);
						$autorLabelImg=$objAutor->hasImg()  ?  $objAutor->getImg(false,true)  :  " - ".$objAutor->getLabel("firstName");
						//Liste des destinataires
						$destList=Txt::txt2tab($message["_idUsers"]);
						$destMultiple=(count($destList)>2)  ?  "**"  :  null;
						$destLabel=Txt::trad("MESSENGER_sendAt")." ";
						foreach($destList as $userId)    {if($userId!=$objAutor->_id)  {$destLabel.=self::getObj("user",$userId)->getLabel().", ";}}
						//Affichage du message
						$_SESSION["messengerMessagesHtml"].="<div class='vMessengerMessage' title=\"".trim($destLabel,", ")."\" data-idUsers=\"".$message["_idUsers"]."\">
																<div>".date("H:i",$message["date"]).$autorLabelImg.$destMultiple."</div>
																<div data-idAutor=\"".$message["_idUser"]."\">".$message["message"]."</div>
															 </div>";
					}
				}
				//On "pulsate" les autres users qui viennent de poster un nouveau message qui n'a pas encore été vu par l'user courant
				foreach($_SESSION["messengerMessagesList"] as $message){
					$idUserTmp=$message["_idUser"];
					//User pas encore ajouté à "messengerPulsateUsers"  &&  User toujours présent dans le livecounter  &&  (User pas encore affiché || affiché avant l'envoi de son message)
					if(!isset($result["messengerPulsateUsers"][$idUserTmp])  &&  isset($_SESSION["livercounterUsers"][$idUserTmp])  && (!isset($_SESSION["messengerUpdateDisplayedUser"][$idUserTmp]) || $message["date"]>$_SESSION["messengerUpdateDisplayedUser"][$idUserTmp]))   {$result["messengerPulsateUsers"][]=$idUserTmp;}
				}
			}

			////	RETOURNE LE RÉSULTAT (format JSON)
			$result["livecounterMainHtml"]=$_SESSION["livecounterMainHtml"];
			$result["livecounterMessengerHtml"]=$_SESSION["livecounterMessengerHtml"];
			$result["messengerMessagesHtml"]=$_SESSION["messengerMessagesHtml"];
			echo json_encode($result);
		}
	}

	/*
	 * AJAX : Update la date d'affichage du messenger d'un user (gardé en session)
	 */
	public static function actionMessengerUpdateDisplayedUser()
	{
		$_SESSION["messengerUpdateDisplayedUser"][Req::getParam("_idUser")]=time();
	}

	/*
	 * AJAX : Post d'un message sur le messenger
	 */
	public static function actionMessengerPostMessage()
	{
		if(self::$curUser->messengerEnabled())
		{
			$usersIds=Req::getParam("messengerPostUsers");
			$usersIds[]=self::$curUser->_id;
			Db::query("INSERT INTO ap_userMessengerMessage SET _idUser=".self::$curUser->_id.", _idUsers=".Db::formatTab2txt($usersIds).", message=".Db::formatParam("message").", date=".Db::format(time()));
			$_SESSION["messengerPostUsers"]=$usersIds;
		}
	}

	/*
	 * VUE : Menu "captcha"
	 */
	public static function menuCaptcha()
	{
		return self::getVue(Req::commonPath."VueCaptcha.php");
	}

	/*
	 *  ACTION : Affiche l'image d'un menu "captcha"
	 */
	public static function actionCaptchaImg()
	{
		//Init
		$width=120;
		$height=28;
		$fontSize=20;
		$caracNb=4;
		$colorLines=array("#DD6666","#66DD66","#6666DD","#DDDD66","#DD66DD","#66DDDD","#666666");
		$colorFonts=array("#880000","#008800","#000088","#888800","#880088","#008888","#000000");
		$caracs="ABCDEFGHKMNPQRSTUVWXYZ2345689";
		//Creation de l'image
		$image=imagecreatetruecolor($width, $height);
		imagefilledrectangle($image, 0, 0, $width-1, $height-1, self::captchaColor("#FFFFFF"));
		//Dessine 15 lines en background
		for($i=0; $i < 15; $i++){
			imageline($image, mt_rand(0,$width-1), mt_rand(0,$height-1), mt_rand(0,$width-1), mt_rand(0,$height-1), self::captchaColor($colorLines[mt_rand(0,count($colorLines)-1)]));
		}
		//Dessine le texte
		$_SESSION["captcha"]="";
		$y=($height/2) + ($fontSize/2);
		for($i=0; $i < $caracNb; $i++)
		{
			// pour chaque caractere : Police + couleur + angulation
			$captchaFont="app/misc/captchaFonts/".mt_rand(1,4).".ttf";
			$color=self::captchaColor($colorFonts[mt_rand(0,count($colorFonts)-1)]);
			$angle=mt_rand(-20,20);
			// sélectionne le caractère au hazard
			$char=substr($caracs, mt_rand(0,strlen($caracs) - 1), 1);
			$x=(intval(($width/$caracNb) * $i) + ($fontSize / 2)) - 4;
			$_SESSION["captcha"].=$char;
			imagettftext($image, $fontSize, $angle, $x, $y, $color, $captchaFont, $char);
		}
		// Captcha dans Session + affichage de l'image
		header("Content-Type: image/jpeg");
		imagejpeg($image);
	}

	/*
	 * Couleur au format hexadecimal pour un Captcha
	 */
	protected static function captchaColor($colors)
	{
		return preg_match("/^#?([\dA-F]{6})$/i",$colors,$rgb) ? hexdec($rgb[1]) : false;
	}

	/*
	 * Controle du Captcha
	 */
	public static function captchaControl($captcha)
	{
		return (!empty($captcha) && $captcha==$_SESSION["captcha"]);
	}

	/*
	 * VUE : Initialisation de l'editeur TinyMCE (doit déjà y avoir un champ "textarea")
	 */
	public static function initHtmlEditor($fieldName)
	{
		//Nom du champ "textarea"
		$vDatas["fieldName"]=$fieldName;
		//Sélectionne au besoin le "draftTargetObjId" pour n'afficher que le brouillon/draft de l'objet précédement édité (on n'utilise pas "editObjId" car il est effacé dès qu'on sort de l'édition de l'objet...)
		$sqlTargetObjId=(Req::isParam("targetObjId"))  ?  "draftTargetObjId=".Db::formatParam("targetObjId")  :  "draftTargetObjId IS NULL";
		$vDatas["editorDraft"]=Db::getVal("SELECT editorDraft FROM ap_userLivecouter WHERE _idUser=".Ctrl::$curUser->_id." AND ".$sqlTargetObjId);
		//Affiche la vue de l'éditeur TinyMce
		return self::getVue(Req::commonPath."VueHtmlEditor.php",$vDatas);
	}

	/*
	 * VUE : Affiche des personnes sur une carte (contacts/utilisateurs)
	 */
	public static function actionPersonsMap()
	{
		//Liste les personnes/adresses à afficher
		$adressList=[];
		foreach(Ctrl::getTargetObjects() as $tmpPerson)
		{
			//La personne est visible et possède une adresse
			if($tmpPerson->readRight() && method_exists($tmpPerson,"hasAdress") && $tmpPerson->hasAdress()){
				$tmpAdress=trim($tmpPerson->adress.", ".$tmpPerson->postalCode." ".str_ireplace("cedex",null,$tmpPerson->city)." ".$tmpPerson->country,  ", ");
				$tmpLabel=$tmpPerson->getLabel()." <br> ".$tmpAdress;
				if(!empty($tmpPerson->companyOrganization) || !empty($tmpPerson->function))  {$tmpLabel.="<br>".trim($tmpPerson->function." - ".$tmpPerson->companyOrganization, " - ");}
				$tmpImg=($tmpPerson->hasImg())  ?  $tmpPerson->getImgPath()  :  "app/img/mapBig.png";
				$adressList[]=["adress"=>$tmpAdress, "personLabel"=>$tmpLabel, "personImg"=>$tmpImg];
			}
		}
		//Affiche la carte Gmap ou Leaflet
		$vDatas["adressList"]=json_encode($adressList);
		$vDatas["mapTool"]=Ctrl::$agora->gMapsEnabled()  ?  "gmap"  :  "leaflet";
		static::displayPage(Req::commonPath."VuePersonsMap.php",$vDatas);
	}

	/*
	 * VUE : menuWallpaper
	 */
	public static function menuWallpaper($curWallpaper)
	{
		//Wallpapers disponibles
		$vDatas["wallpaperList"]=array();
		$filesList=array_merge(scandir(PATH_WALLPAPER_DEFAULT),scandir(PATH_WALLPAPER_CUSTOM));
		foreach($filesList as $tmpFile){
			if(!in_array($tmpFile,['.','..']) && File::controlType("imageBrowser",$tmpFile)){
				$path=(is_file(PATH_WALLPAPER_DEFAULT.$tmpFile))  ?  PATH_WALLPAPER_DEFAULT.$tmpFile  :  PATH_WALLPAPER_CUSTOM.$tmpFile;
				$value=(is_file(PATH_WALLPAPER_DEFAULT.$tmpFile))  ?  WALLPAPER_DEFAULT_PREFIX.$tmpFile  :  $tmpFile;
				$nameRacine=str_replace(File::extension($tmpFile),null,$tmpFile);
				$vDatas["wallpaperList"][]=array("path"=>$path, "value"=>$value, "name"=>$tmpFile, "nameRacine"=>$nameRacine);
			}
		}
		//Affiche le menu
		$vDatas["wallpaperList"]=Tool::sortArray($vDatas["wallpaperList"],"nameRacine");
		$vDatas["curWallpaper"]=$curWallpaper;
		return self::getVue(Req::commonPath."VueMenuWallpaper.php",$vDatas);
	}

	/*
	 * PATH D'UN WALLPAPER  (cf. Ctrl::$curSpace->wallpaper && Ctrl::$agora->wallpaper)
	 */
	public static function pathWallpaper($fileName=null)
	{
		//Récup le chemin et vérifie la présence du fichier
		if(!empty($fileName)){
			$pathWallpaper=(strstr($fileName,WALLPAPER_DEFAULT_PREFIX)) ? PATH_WALLPAPER_DEFAULT.trim($fileName,WALLPAPER_DEFAULT_PREFIX) : PATH_WALLPAPER_CUSTOM.$fileName;
			if(is_file($pathWallpaper))		{return $pathWallpaper;}
		}
		//Sinon retourne le wallpaper par défaut
		return PATH_WALLPAPER_DEFAULT."1.jpg";
	}

	/*
	 * ACTION : Download de fichier depuis mobileApp
	 */
	public static function actionGetFile()
	{
		if(Req::getParam("fileType")=="attached")	{CtrlObject::actionGetFile();}//AttachedFile
		else										{CtrlFile::actionGetFile();}//File object
	}

	/*
	 * URL : Download un fichier depuis mobileApp ("AttachedFile" ou "modFile")
	 */
	public static function appGetFileUrl($downloadUrl, $fileName)
	{
		$downloadUrl=str_ireplace(["ctrl=object","ctrl=file"],"ctrl=misc",$downloadUrl);						//Switch sur le controleur "ctrl=misc" (cf. "$initCtrlFull=false")
		$downloadUrl.=(stristr($downloadUrl,"ctrl=object"))  ?  "&fileType=attached"  :  "&fileType=modFile";	//Fichier joint d'un objet lambda OU Fichier du gestionnaire de fichier
		return $downloadUrl.="&nameMd5=".md5($fileName);														//Ajoute le "nameMd5" pour le controle d'accès (cf. "CtrlObject::actionGetFile()" && "CtrlFile::actionGetFile()")
	}
	
	/*
	 * ACTION : affiche un fichier Ical
	 */
	public static function actionDisplayIcal()
	{
		$objCalendar=self::getTargetObj();
		if(is_object($objCalendar) && $objCalendar->md5IdControl())  {CtrlCalendar::getIcal($objCalendar);}
	}
}