<?php
/**
* This file is part of the Agora-Project Software package
*
* @copyleft Agora-Project <https://www.agora-project.net>
* @license GNU General Public License (GPL-2.0)
*/


/*
 * Controleur express
 */
class CtrlMisc extends Ctrl
{
	//Initialisation limitée du controleur
	protected static $initCtrlFull=false;

	/********************************************************************************************************
	 * AJAX : UPDATE DU MESSENGER & LIVECOUNTER
	 ********************************************************************************************************/
	public static function actionMessengerUpdate()
	{
		//Messenger activé?
		if(self::$curUser->messengerEnabled())
		{
			////	UPDATE LE LIVECOUNTER DE L'USER COURANT EN BDD :  SPECIFIE SI ON EST EN TRAIN DE MODIFIER UN OBJET (via "editTypeId")  &&  ENREGISTRE SI BESOIN LE CONTENU DE L'EDITEUR (via "editorDraft")
			$sqlValues="_idUser=".(int)self::$curUser->_id.", ipAdress=".Db::format($_SERVER["REMOTE_ADDR"]).", editTypeId=".Db::param("editTypeId").", `date`=".Db::format(time());
			if(Req::isParam("editorDraft"))  {$sqlValues.=", editorDraft=".Db::param("editorDraft").", draftTypeId=".Db::param("editTypeId");}//Vérifie si "editorDraft" est spécifié (pour pas l'effacer..)
			Db::query("INSERT INTO ap_userLivecouter SET ".$sqlValues." ON DUPLICATE KEY UPDATE ".$sqlValues);

			////	INIT LE MESSENGER EN DEBUT DE SESSION
			if(!isset($_SESSION["livecounterUsers"])){
				//Init les variables de session
				$_SESSION["livecounterUsers"]=$_SESSION["messengerMessages"]=$_SESSION["messengerDisplayTimes"]=$_SESSION["messengerCheckedUsers"]=[];
				$_SESSION["livecounterUsersHtml"]=$_SESSION["livecounterFormHtml"]=$_SESSION["messengerMessagesHtml"]="";
				//Supprime les anciens livecounters d'users déconnectés et les anciens messages du messenger (> 60 jours) +  et les anciens messages  de visio (> 1 heure)
				Db::query("DELETE FROM ap_userLivecouter WHERE `date` < ".intval(time()-TIME_2MONTHS));
				Db::query("DELETE FROM ap_userMessengerMessage WHERE `date` < ".intval(time()-TIME_2MONTHS));
				Db::query("DELETE FROM ap_userMessengerMessage WHERE message LIKE '%launchVisio%' AND `date` < ".intval(time()-3600));
				//Garde en session les users qui rendent visible leur messenger (cf. paramétrage dans "ap_userMessenger")
				$idsUsersVisibles=[0];//Ajoute un pseudo user '0'
				foreach(self::$curUser->usersVisibles() as $tmpUser)  {$idsUsersVisibles[]=$tmpUser->_id;}
				$messengerUsersSql=Db::getCol("SELECT _id FROM ap_user WHERE _id!=".self::$curUser->_id." AND _id IN (".implode(",",$idsUsersVisibles).") AND _id IN (select _idUserMessenger from ap_userMessenger where allUsers=1 or _idUser=".self::$curUser->_id.")");
				$_SESSION["messengerUsersSql"]=implode(",", array_merge($messengerUsersSql,[0]));//Ajoute un pseudo user '0'
			}

			////	RECUPERE LES USERS CONNECTÉS (LIVECOUNTERS) : VERIF SI YA UN CHANGEMENT DU LIVECOUNTER, AVEC CONNEXION OU DECONNECTION (après 40 secondes d'inactivité: l'user est considéré comme "déconnecté")
			$livercounterUsersOld=$_SESSION["livecounterUsers"];
			$_SESSION["livecounterUsers"]=Db::getObjTab("user", "SELECT DISTINCT T1.* FROM ap_user T1, ap_userLivecouter T2 WHERE T1._id=T2._idUser AND T1._id IN (".$_SESSION["messengerUsersSql"].") AND T2.date > ".(time()-40));
			$result["livecounterUpdate"]=(array_keys($livercounterUsersOld)!=array_keys($_SESSION["livecounterUsers"]));//compare les _idUsers (keys)

			////	RECUPERE LES MESSAGES DU MESSENGER : VERIF SI YA DES NOUVEAUX MESSAGES
			$messengerMessagesListOld=$_SESSION["messengerMessages"];
			$_SESSION["messengerMessages"]=Db::getTab("SELECT * FROM ap_userMessengerMessage WHERE _idUsers LIKE '%@".self::$curUser->_id."@%' ORDER BY date asc");
			$result["messengerUpdate"]=(serialize($messengerMessagesListOld)!=serialize($_SESSION["messengerMessages"]));//compare les messages sérialisés (pas de "count()")

			////	LISTE DES USERS CONNECTÉS (LIVECOUNTERS)
			if($result["livecounterUpdate"]==true){
				$_SESSION["livecounterUsersHtml"]=$_SESSION["livecounterFormHtml"]="";//Réinit
				foreach($_SESSION["livecounterUsers"] as $tmpUser){
					$userImg=(Req::isMobile()==false && $tmpUser->profileImgExist())  ?  $tmpUser->profileImg(false,true)  :  null;	//Image de l'user
					$userTooltip=$tmpUser->getLabel()." &nbsp;".$userImg;															//Tooltip du label de l'user
					$userFirstName=$tmpUser->getLabel("firstName");																	//Prénom de l'user
					//Affichage dans le livecounter et le formulaire du messenger (checkbox)
					$_SESSION["livecounterUsersHtml"].='<label class="vLivecounterUser" id="livecounterUser'.$tmpUser->_id.'" onclick="messengerDisplay('.$tmpUser->_id.')" '.Txt::tooltip(Txt::trad("MESSENGER_chatWith")." ".$userTooltip).'>'.$userImg.$userFirstName.'</label>';
					$_SESSION["livecounterFormHtml"].='<div class="vMessengerUser">
															<input type="checkbox" name="messengerUsers[]" value="'.$tmpUser->_id.'" id="messengerUserCheckbox'.$tmpUser->_id.'" class="messengerUserCheckbox" data-user-label="'.$userFirstName.'" data-user-label-visio="'.Txt::clean(trim($userFirstName),"max").'">
															<label for="messengerUserCheckbox'.$tmpUser->_id.'" '.Txt::tooltip(Txt::trad("select")." ".$userTooltip).'>'.$userImg.$userFirstName.'</label>
													   </div>';
				}
				//Ajoute "inverser la sélection" si ya + de 5 users
				if(count($_SESSION["livecounterUsers"])>5)
					{$_SESSION["livecounterFormHtml"].='<div class="vMessengerUser"><label onclick="$(\'label[for^=messengerUserCheckbox]\').trigger(\'click\')"><img src="app/img/checkSwitch.png"> &nbsp; '.Txt::trad("selectSwitch").'</label></div>';}
			}

			////	LISTE DES MESSAGES DU MESSENGER  &&  DES "PULSATES"
			if($result["messengerUpdate"]==true){
				$_SESSION["messengerMessagesHtml"]="";//init
				foreach($_SESSION["messengerMessages"] as $message){																			//Parcourt chaque message
					$destList=Txt::txt2tab($message["_idUsers"]);																				//List des destinataires
					$autorObj=self::getObj("user",$message["_idUser"]);																			//Label/icone de l'auteur
					if(Req::isMobile())						{$dateAutor=$autorObj->getLabel("firstName")."<br>".date("H:i",$message["date"]);}	//Mobile : "Will 11:00"
					elseif($autorObj->profileImgExist())	{$dateAutor=date("H:i",$message["date"]).$autorObj->profileImg(false,true);}		//Mode normal avec icone de l'user : "11:00 <img>"
					else									{$dateAutor=date("H:i",$message["date"])." - ".$autorObj->getLabel("firstName");}	//Mode normal avec label de l'user : "11:00 - Will"
					if(count($destList)>2)  {$dateAutor.="<img src='app/img/user/iconSmall.png' class='iconUsersMultiple'>";}					//Ajoute si besoin l'icone de discussion à plusieurs
					//Title de l'auteur et des destinataires
					$oldMessageClass="vMessengerOldMessage";																																	//"vMessengerOldMessage" par défaut
					$messageTooltip=Txt::dateLabel($message["date"],"labelFull")." : ".Txt::trad("MESSENGER_messageFrom")." ".$autorObj->getLabel()." ".Txt::trad("MESSENGER_messageSentTo")." ";	//Tooltip des détails du message 
					foreach($destList as $_idUserDest){
						if($_idUserDest!=$autorObj->_id) {$messageTooltip.=self::getObj("user",$_idUserDest)->getLabel().", ";}	//Ajoute le libellé du destinataire
						if(array_key_exists($_idUserDest,$_SESSION["livecounterUsers"]))  {$oldMessageClass=null;}				//Message affecté à un user connnecté : on retire "vMessengerOldMessage" 
					}
					//Affichage du message
					$_SESSION["messengerMessagesHtml"].='<table class="vMessengerMessage '.$oldMessageClass.'" data-idUsers="'.$message["_idUsers"].'" '.Txt::tooltip(rtrim($messageTooltip,", ")).'><tr>
															<td class="vMessengerMessageDateAutor">'.$dateAutor.'</td>
															<td data-idAutor="'.$autorObj->_id.'">'.$message["message"].'</td>
														 </tr></table>';
				}
			}

			////	"PULSATE" LE AUTEURS DES MESSAGES QUI N'ONT PAS ENCORE ÉTÉ VU
			$result["livecounterUsersPulsate"]=[];
			foreach($_SESSION["messengerMessages"] as $message){
				//Pas de pulsate si le message est trop ancien (on n'insiste pas: 120s max en mode normal ou 30s max sur mobile)
				$messageAge=time()-$message["date"];
				if($messageAge>120 || (Req::isMobile() && $messageAge>30))  {continue;}
				//"Pulsate" l'auteur s'il est connecté  &&  (s'il n'a pas encore été affiché || s'il a été affiché avant l'envoi du message)
				$autorId=(int)$message["_idUser"];
				if(isset($_SESSION["livecounterUsers"][$autorId])  &&  (!isset($_SESSION["messengerDisplayTimes"][$autorId]) || $message["date"]>$_SESSION["messengerDisplayTimes"][$autorId]))
					{$result["livecounterUsersPulsate"][]=$autorId;}
			}
			//Supprime les doublons de pulsates  &&  Update le "messengerDisplayTimes" (Si "messengerDisplayMode" est passé paramètre. Toujours à la fin!)
			$result["livecounterUsersPulsate"]=array_unique($result["livecounterUsersPulsate"]);
			self::actionMessengerDisplayTimesUpdate();

			////	RETOURNE LE RÉSULTAT AU FORMAT JSON
			$result["livecounterUsersHtml"]=$_SESSION["livecounterUsersHtml"];
			$result["livecounterFormHtml"]=$_SESSION["livecounterFormHtml"];
			$result["messengerMessagesHtml"]=$_SESSION["messengerMessagesHtml"];
			$result["messengerCheckedUsers"]=$_SESSION["messengerCheckedUsers"];
			echo json_encode($result);
		}
	}

	/********************************************************************************************************
	 * AJAX : UPDATE LE "MessengerDisplayTimes" D'UN USER OU DE "ALL"
	 ********************************************************************************************************/
	public static function actionMessengerDisplayTimesUpdate()
	{
		//Update l'affichage de l'user affiché || Update l'affichage de tous les users du livecounter ("messengerDisplayMode==all")
		if(is_numeric(Req::param("messengerDisplayMode")))  {$_SESSION["messengerDisplayTimes"][Req::param("messengerDisplayMode")]=time();}
		elseif(Req::param("messengerDisplayMode")=="all"){
			foreach($_SESSION["livecounterUsers"] as $tmpUser)  {$_SESSION["messengerDisplayTimes"][$tmpUser->_id]=time();}
		}
	}

	/********************************************************************************************************
	 * AJAX : POST D'UN MESSAGE SUR LE MESSENGER
	 * Note : les messages sont encodés en "utf8mb4" pour le support des "emoji"
	 ********************************************************************************************************/
	public static function actionMessengerPost()
	{
		if(self::$curUser->messengerEnabled())
		{
			//Init les destinataires du message et le message
			$usersIds=Req::param("messengerUsers");
			$usersIds[]=self::$curUser->_id;
			//Proposition de visio : supprime si besoin les anciens liens de visio identiques
			if(stristr(Req::param("message"),"launchVisio"))  {Db::query("DELETE FROM ap_userMessengerMessage WHERE _idUser=".self::$curUser->_id." AND _idUsers=".Db::formatTab2txt($usersIds)." AND message=".Db::param("message"));}
			//Enregistre le message
			Db::query("INSERT INTO ap_userMessengerMessage SET _idUser=".self::$curUser->_id.", _idUsers=".Db::formatTab2txt($usersIds).", message=".Db::param("message").", `date`=".Db::format(time()));
			//Update les users "checked" lors d'une discussion à plusieurs (3 users minimum : user courant + 2 destinataires au moins)
			if(count($usersIds)>=3)  {$_SESSION["messengerCheckedUsers"]=$usersIds;}
		}
	}

	/********************************************************************************************************
	 * ACTION : RECHERCHE D'OBJETS SUR TOUS L'ESPACE
	 ********************************************************************************************************/
	public static function actionSearch()
	{
		//Init
		$vDatas=[];
		//// Pour chaque module : liste les champs de recherche et les objet concernés
		$vDatas["searchFields"]=[];
		foreach(self::$curSpace->moduleList() as $tmpModule){				//Parcourt chaque module de l'espace
			if(method_exists($tmpModule["ctrl"],"getPlugins")){				//Vérifie l'existence d'une class "getPlugins()"
				foreach($tmpModule["ctrl"]::$MdlObjects as $tmpMdlObject){	//Parcourt chaque type d'objet du module
					foreach($tmpMdlObject::$searchFields as $tmpField){		//Parcourt chaque champ de l'objet
						$vDatas["searchFields"][$tmpField]["checked"]=(!Req::isParam("searchFields") || in_array($tmpField,Req::param("searchFields")))  ?  "checked"  :  null;	//Sélectionne si besoin la checkbox du champ
						if(empty($vDatas["searchFields"][$tmpField]["title"]))	{$vDatas["searchFields"][$tmpField]["title"]="";}												//"title" de la checkbox (objets concernés)
						if($tmpMdlObject::isFolder==true)	{$vDatas["searchFields"][$tmpField]["title"].=" - ".Txt::trad("OBJECTfolder")."<br>";}					  			//Précise qu'il s'agit d'un dossier
						else								{$vDatas["searchFields"][$tmpField]["title"].=" - ".Txt::trad("OBJECT".$tmpMdlObject::objectType)."<br>";}			//Précise le type d'objet : Fichier, Contact..
					}
				}
			}
		}
		//// Resultat de recherche
		if(Req::isParam("formValidate"))
		{
			//Paramétrage de la récupération des plugins
			$vDatas["pluginsList"]=[];
			$pluginParams=array("type"=>"search", "searchText"=>Req::param("searchText"), "searchMode"=>Req::param("searchMode"), "creationDate"=>Req::param("creationDate"), "searchFields"=>Req::param("searchFields"), "searchModules"=>Req::param("searchModules"));
			//Récupère les plugins de chaque module
			foreach(self::$curSpace->moduleList() as $tmpModule){
				if(method_exists($tmpModule["ctrl"],"getPlugins") && in_array($tmpModule["ctrl"]::moduleName,Req::param("searchModules")))	//vérif que "getPlugins()" existe et que le module soit dans "searchModules"
					{$vDatas["pluginsList"]=array_merge($vDatas["pluginsList"], $tmpModule["ctrl"]::getPlugins($pluginParams));}			//Récupère les plugins du module
			}
			//Garde les termes de la recherche en session
			$_SESSION["searchText"]=Req::param("searchText");
		}
		//// Affiche la vue
		static::displayPage(Req::commonPath."VueSearch.php",$vDatas);
	}

	/********************************************************************************************************
	 * VUE : MENU DE LANCEMENT DE VISIOCONFERENCE
	 ********************************************************************************************************/
	public static function actionLaunchVisio()
	{
		$vDatas["visioURL"]=urldecode(Req::param("visioURL"));																// Url de la visioconf
		if(is_object(Ctrl::$curUser))  {$vDatas["visioURL"].="#userInfo.displayName=%22".Ctrl::$curUser->getLabel()."%22";}	// Ajoute le nom de l'user courant dans l'Url
		if(Req::isMobileApp()){																								// Appli mobile
			$vDatas["visioURL"].="#frommobileapp_getfile";																	// - Lance la visio via le browser system (cf. contrôle de l'Url via "main.dart")
			$vDatas["visioURLJitsi"]="org.jitsi.meet://".str_replace("https://","",$vDatas["visioURL"]);					// - Lance la visio depuis l'appli Jitsi (bouton secondaire)
		}
		static::displayPage(Req::commonPath."VueLaunchVisio.php",$vDatas);
	}

	/********************************************************************************************************
	 * VUE : MENU "CAPTCHA"
	 ********************************************************************************************************/
	public static function menuCaptcha()
	{
		return self::getVue(Req::commonPath."VueCaptcha.php");
	}

	/********************************************************************************************************
	 * AJAX : CONTROLE DU CAPTCHA
	 ********************************************************************************************************/
	public static function actionCaptchaControl()
	{
		if($_SESSION["captcha"]==Req::param("captcha"))  {echo "controlOK";}
	}

	/********************************************************************************************************
	 *  ACTION : AFFICHE L'IMAGE D'UN MENU "CAPTCHA"
	 ********************************************************************************************************/
	public static function actionCaptchaImg()
	{
		//Init
		$width=160;
		$height=30;
		$fontSize=24;
		$caracNb=5;
		$colorLines=array("#DD6666","#66DD66","#6666DD","#DDDD66","#DD66DD","#66DDDD","#666666");
		$colorFonts=array("#880000","#008800","#000088","#888800","#880088","#008888","#000000");
		$caracs="ABCDEFGHKMNPQRSTUVWXYZ2345689";
		//Creation de l'image
		$image=imagecreatetruecolor($width, $height);
		imagefilledrectangle($image, 0, 0, $width-1, $height-1, self::captchaColor("#FFFFFF"));
		//Dessine 10 lines en background
		for($i=0; $i < 10; $i++){
			imageline($image, mt_rand(0,$width-1), mt_rand(0,$height-1), mt_rand(0,$width-1), mt_rand(0,$height-1), self::captchaColor($colorLines[mt_rand(0,count($colorLines)-1)]));
		}
		//Dessine le texte
		$_SESSION["captcha"]="";
		$y=($height/2) + ($fontSize/2);
		for($i=0; $i < $caracNb; $i++)
		{
			// pour chaque caractere : Police + couleur + angulation
			$captchaFont="app/misc/captchaFonts/".mt_rand(1,3).".ttf";
			$color=self::captchaColor($colorFonts[mt_rand(0,count($colorFonts)-1)]);
			$angle=mt_rand(-15,15);//variation en degrés
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

	/********************************************************************************************************
	 * COULEUR AU FORMAT HEXADECIMAL POUR UN CAPTCHA
	 ********************************************************************************************************/
	protected static function captchaColor($colors)
	{
		return preg_match("/^#?([\dA-F]{6})$/i",$colors,$rgb) ? hexdec($rgb[1]) : false;
	}

	/********************************************************************************************************
	 * VUE : AFFICHE DES PERSONNES SUR UNE CARTE (contacts/utilisateurs)
	 ********************************************************************************************************/
	public static function actionPersonsMap()
	{
		//Liste les personnes/adresses à afficher
		$adressList=[];
		foreach(Ctrl::getCurObjects() as $tmpPerson){
			//La personne est visible et possède une adresse
			if($tmpPerson->readRight() && method_exists($tmpPerson,"hasAdress") && $tmpPerson->hasAdress()){
				$tmpAdress=trim($tmpPerson->adress.", ".$tmpPerson->postalCode." ".str_ireplace("cedex","",$tmpPerson->city)." ".$tmpPerson->country,  ", ");
				$tmpLabel=$tmpPerson->getLabel()." <br> ".$tmpAdress;
				if(!empty($tmpPerson->companyOrganization) || !empty($tmpPerson->function))  {$tmpLabel.="<br>".trim($tmpPerson->function." - ".$tmpPerson->companyOrganization, " - ");}
				$tmpImg=($tmpPerson->profileImgExist())  ?  $tmpPerson->profileImgPath()  :  "app/img/mapBig.png";
				$adressList[]=["adress"=>$tmpAdress, "personLabel"=>$tmpLabel, "personImg"=>$tmpImg];
			}
		}
		//Affiche la carte : "gmap" ou "leaflet"
		$vDatas["adressList"]=json_encode($adressList);
		$vDatas["mapTool"]=Ctrl::$agora->gMapsEnabled()  ?  "gmap"  :  "leaflet";
		static::displayPage(Req::commonPath."VuePersonsMap.php",$vDatas);
	}

	/********************************************************************************************************
	 * VUE : MENU DE SELECTION DU WALLPAPER
	 ********************************************************************************************************/
	public static function menuWallpaper($curWallpaper)
	{
		//Wallpapers disponibles
		$vDatas["wallpaperList"]=[];
		$filesList=array_merge(scandir(PATH_WALLPAPER_DEFAULT),scandir(PATH_WALLPAPER_CUSTOM));
		foreach($filesList as $tmpFile){
			if(!in_array($tmpFile,['.','..']) && File::isType("imageBrowser",$tmpFile)){
				if(is_file(PATH_WALLPAPER_DEFAULT.$tmpFile))	{$path=PATH_WALLPAPER_DEFAULT.$tmpFile;		$value=WALLPAPER_DEFAULT_DB_PREFIX.$tmpFile;	$sortName=str_replace(File::extension($tmpFile),"",$tmpFile);}//Tri en fonction de la valeur numérique
				else											{$path=PATH_WALLPAPER_CUSTOM.$tmpFile;		$value=$tmpFile;								$sortName="zz".$tmpFile;}//Place les wallpapers customs à la fin
				$vDatas["wallpaperList"][]=["path"=>$path, "value"=>$value, "name"=>$tmpFile, "sortName"=>$sortName];
			}
		}
		//Affiche le menu (trie les wallpapers par nom)
		$vDatas["wallpaperList"]=Tool::sortArray($vDatas["wallpaperList"],"sortName");
		$vDatas["curWallpaper"]=$curWallpaper;
		return self::getVue(Req::commonPath."VueMenuWallpaper.php",$vDatas);
	}

	/********************************************************************************************************
	 * PATH D'UN WALLPAPER ENREGISTRE EN BDD (cf. Ctrl::$curSpace->wallpaper && Ctrl::$agora->wallpaper)
	 ********************************************************************************************************/
	public static function pathWallpaper($fileName=null)
	{
		//Récup le chemin et vérifie la présence du fichier
		if(!empty($fileName)){
			$pathWallpaper=(strstr($fileName,WALLPAPER_DEFAULT_DB_PREFIX))  ?  PATH_WALLPAPER_DEFAULT.trim($fileName,WALLPAPER_DEFAULT_DB_PREFIX)  :  PATH_WALLPAPER_CUSTOM.$fileName;
			if(is_file($pathWallpaper))  {return $pathWallpaper;}
		}
		//Sinon retourne le wallpaper par défaut
		return PATH_WALLPAPER_DEFAULT."1.jpg";
	}

	/********************************************************************************************************
	 * ACTION : AFFICHE UN FICHIER ICAL (cf. "MdlCalendar->contextMenu()")
	 ********************************************************************************************************/
	public static function actionDisplayIcal()
	{
		$objCalendar=self::getCurObj();
		if(is_object($objCalendar) && $objCalendar->md5IdControl())  {CtrlCalendar::getIcal($objCalendar);}
	}

	/**********************************************************************************************************************************************
	 * URL : DOWNLOAD DEPUIS L'EXTERIEUR, VIA MOBILEAPP OU NOTIF MAIL
	 * exple:	"?ctrl=file&action=GetFile&typeId=file-1"
	 * 		=>  "?ctrl=misc&action=ExternalGetFile&typeId=file-1&ctrlBis=file&nameMd5=184dfd315adbbed13729076606b1afac&fileName=Documentation.pdf"
	 **********************************************************************************************************************************************/
	public static function urlExternalGetFile($urlDownload, $fileName)
	{
		$ctrlBis=stristr($urlDownload,"ctrl=file")  ?  "file"  :  "object";														//Défini d'abord le controleur secondaire : "file" ou "object" ("attachedFile")
		$urlDownload=str_ireplace(["ctrl=file","ctrl=object"], "ctrl=misc", $urlDownload);										//Puis switch sur le controleur "misc" (cf. "$initCtrlFull=false")
		$urlDownload=str_ireplace(["action=GetFile","action=AttachedFileDownload"], "action=ExternalGetFile", $urlDownload);	//Puis switch sur l'action "ExternalGetFile"
		return $urlDownload."&ctrlBis=".$ctrlBis."&nameMd5=".md5($fileName)."&fileName=".urldecode($fileName);					//Retourne l'url avec le nom du fichier et le "nameMd5" pour le controle d'accès
	}

	/*********************************************************************************************************************************
	 * ACTION : DOWNLOAD DEPUIS L'EXTERIEUR, VIA MOBILEAPP OU NOTIF MAIL  (cf. contrôle de l'Url via "main.dart")
	 *********************************************************************************************************************************/
	public static function actionExternalGetFile()
	{
		////	Download/Affiche le fichier (pdf/img/video)
		if(Req::isParam("launchDownload") || Req::isParam("displayFile")){
			if(Req::param("ctrlBis")=="file")	{CtrlFile::actionGetFile();}					//Download un fichier du ModFile
			else								{CtrlObject::actionAttachedFileDownload();}		//Download le fichier joint d'un objet
		}
		////	Affiche le bouton de download depuis le browser system
		else{
			static::$isMainPage=true;
			$vDatas["urlDownload"]=$_SERVER['REQUEST_URI']."&launchDownload=true";	//Url de download du fichier ("launchDownload" pour lancer le download ci-dessus)
			if(preg_match("/(iphone|ipad|macintosh)/i",$_SERVER['HTTP_USER_AGENT']))	{$vDatas["appUrl"]="http://apps.apple.com/fr/app/omnispace/id1296301531";}
			elseif(preg_match("/android/i",$_SERVER['HTTP_USER_AGENT']))				{$vDatas["appUrl"]="http://play.google.com/store/apps/details?id=fr.omnispace.www";}
			static::displayPage(Req::commonPath."VueExternalGetFile.php", $vDatas);
		}
	}
}