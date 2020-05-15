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
	 * AJAX : Update du Livecounter et du Messenger
	 */
	public static function actionLivecounterUpdate()
	{
		//Messenger activé?
		if(self::$curUser->messengerEnabled())
		{
			////	UPDATE EN BDD LE LIVECOUNTER DE L'USER COURANT  && AJOUTE "editObjId" SI MODIF EN COURS D'UN OBJET  && AJOUTE "editorDraft" UNIQUEMENT S'IL EST PRECISE
			$sqlValues="_idUser=".(int)self::$curUser->_id.", ipAdress=".Db::format($_SERVER["REMOTE_ADDR"]).", editObjId=".Db::formatParam("editObjId").", date=".Db::format(time());
			if(Req::isParam("editorDraft"))  {$sqlValues.=", editorDraft=".Db::formatParam("editorDraft","editor").", draftTargetObjId=".Db::formatParam("editObjId");}//Ajouter uniquement si "editorDraft" est présent, sinon le "livecounterUpdate" efface le 'draft' et n'a donc plus d'intérêt..
			Db::query("INSERT INTO ap_userLivecouter SET ".$sqlValues." ON DUPLICATE KEY UPDATE ".$sqlValues);

			////	INIT LE MESSENGER EN DEBUT DE SESSION
			if(!isset($_SESSION["livecounterUsersList"]))
			{
				//Init les users connectés  &  La date d'affichage de chaque user  &  La liste des messages  &  L'affichage des users et des messages
				$_SESSION["livecounterUsersList"]=$_SESSION["messengerUserDisplayTime"]=$_SESSION["messengerMessagesList"]=[];
				$_SESSION["livecounterUsersHtml"]=$_SESSION["messengerUsersHtml"]=$_SESSION["messengerMessagesHtml"]="";
				//Supprime les livecounters des users déconnectés & Les vieux messages du messenger & Les vieilles proposition de visio
				Db::query("DELETE FROM ap_userLivecouter WHERE date < ".intval(time()-604800));//Users du Livecounter (avec draft/brouillon du TinyMce) : conservés 7jours max
				Db::query("DELETE FROM ap_userMessengerMessage WHERE date < ".intval(time()-604800));//Messages du messenger : idem
				Db::query("DELETE FROM ap_userMessengerMessage WHERE message LIKE '%launchVisioMessage%' AND date < ".intval(time()-7200));//Messages avec proposition de visio : conservés 2h max
				//Garde en session les users que l'on peut voir et n'ayant pas bloqué leur messenger
				$idsUsers=[0];//pseudo user
				foreach(self::$curUser->usersVisibles() as $tmpUser)  {$idsUsers[]=$tmpUser->_id;}
				$messengerUsersSql=Db::getCol("SELECT _id FROM ap_user WHERE _id!=".self::$curUser->_id." AND _id IN (".implode(",",$idsUsers).") AND _id IN (select _idUserMessenger from ap_userMessenger where allUsers=1 or _idUser=".self::$curUser->_id.")");
				$_SESSION["messengerUsersSql"]=implode(",", array_merge($messengerUsersSql,[0]));//ajoute le pseudo user "0"
			}

			////	RECUPERE LES LIVECOUNTERS (LISTE DES USERS)
			//Verif si ya un changement du livecounter (connection/deconnection)
			$livercounterUsersOld=$_SESSION["livecounterUsersList"];
			$livecounterDateTimeout=time()-40;//On considère les autres users comme "déconnectés" du livecounter au bout de 40 secondes d'inactivité
			$_SESSION["livecounterUsersList"]=Db::getObjTab("user", "SELECT DISTINCT T1.* FROM ap_user T1, ap_userLivecouter T2 WHERE T1._id=T2._idUser AND T1._id IN (".$_SESSION["messengerUsersSql"].") AND T2.date > ".$livecounterDateTimeout);
			$result["livercounterUpdate"]=(count($livercounterUsersOld)!=count($_SESSION["livecounterUsersList"]));//Note : ne pas comparer directement les tableaux d'objet, mais uniquement leur taille

			////	AFFICHAGE DES USERS CONNECTÉS
			if($result["livercounterUpdate"]==true)
			{
				//Init les users du livecounter et du messenger
				$_SESSION["livecounterUsersHtml"]=$_SESSION["messengerUsersHtml"]="";
				//Affiche chaque user connecté
				foreach($_SESSION["livecounterUsersList"] as $tmpUser)
				{
					//Label && Image des users du livecounter principal
					$userTitle=$tmpUser->getLabel()."<br>".Txt::trad("MESSENGER_connectedSince")." ".date("H:i",$tmpUser->lastConnection);
					$messengerUserImg=(Req::isMobile()==false && $tmpUser->hasImg())  ?  $tmpUser->getImg(false,true)."&nbsp;"  :  null;
					$livecounterUserImg=(Req::isMobile()==false && count($_SESSION["livecounterUsersList"])<10)  ?  $messengerUserImg  :  null;//Pas d'img sur mobile ou si ya + de 10 users
					//Affichage des livecounters
					$_SESSION["livecounterUsersHtml"].="<label class='vLivecounterUser' onclick='messengerDisplay(".$tmpUser->_id.");' title=\"".$userTitle."\" data-idUser=".$tmpUser->_id.">".$livecounterUserImg.$tmpUser->getLabel("firstName")."</label>";
					$_SESSION["messengerUsersHtml"].="<div class='vMessengerUser'>
																<input type='checkbox' name='messengerUsers' value='".$tmpUser->_id."' id='messengerUsers".$tmpUser->_id."'>
																<label for='messengerUsers".$tmpUser->_id."' title=\"".Txt::trad("select")." ".$userTitle."\">".$messengerUserImg.$tmpUser->getLabel("firstName")."</label>
															</div>";
				}
				//Ajoute "inverser la sélection" si ya 5 users ou+
				if(count($_SESSION["livecounterUsersList"])>=5)  {$_SESSION["messengerUsersHtml"].="<div class='vMessengerUser' id='checkUserAll'><label onclick=\"$('label[for^=messengerUsers]').trigger('click');\"><img src='app/img/checkSelect.png'> &nbsp; ".Txt::trad("invertSelection")."</label></div>";}
			}

			////	AFFICHAGE DES MESSAGES DU MESSENGER
			$result["messengerPulsateUsers"]=[];
			if(!empty($_SESSION["livecounterUsersList"]))
			{
				//// Verif si ya de nouveaux messages
				$messengerMessagesListOld=$_SESSION["messengerMessagesList"];
				$_SESSION["messengerMessagesList"]=Db::getTab("SELECT * FROM ap_userMessengerMessage WHERE _idUsers LIKE '%@".self::$curUser->_id."@%' ORDER BY date asc");
				$result["messengerUpdate"]=(count($messengerMessagesListOld)!=count($_SESSION["messengerMessagesList"]));
				//// Affichage des messages (Init OU nouveaux messages)
				if($result["messengerUpdate"]==true)
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
						foreach($destList as $userId)  {if($userId!=$objAutor->_id) {$destLabel.=self::getObj("user",$userId)->getLabel().", ";}}
						//Affichage du message
						$_SESSION["messengerMessagesHtml"].="<table class='vMessengerMessage' title=\"".trim($destLabel,", ")."\" data-idUsers=\"".$message["_idUsers"]."\"><tr>
																<td>".date("H:i",$message["date"]).$autorLabelImg.$destMultiple."</td>
																<td data-idAutor=\"".$message["_idUser"]."\">".$message["message"]."</td>
															 </tr></table>";
					}
				}
				//// Fait clignoter les users qui viennent de poster un nouveau message (via "pulsate")
				foreach($_SESSION["messengerMessagesList"] as $message){
					if((time()-$message["date"])>1200)  {continue;}//on zappe les messages qui datent de plus de 20mn (évite le "pulstate" d'anciens messages si on vient de se connecter)
					$idUserTmp=$message["_idUser"];//Auteur du message
					$userInLivecounter=(isset($_SESSION["livecounterUsersList"][$idUserTmp]) && !isset($result["messengerPulsateUsers"][$idUserTmp]));//User connecté (affiché dans le livecounter) mais pas encore ajouté au "messengerPulsateUsers" (évite les doublons de pulsate)
					$userMessagesNotDisplayed=(!isset($_SESSION["messengerUserDisplayTime"][$idUserTmp]) || $message["date"]>$_SESSION["messengerUserDisplayTime"][$idUserTmp]);//User pas encore affiché OU affiché avant que ne soit posté le message
					if($userInLivecounter==true && $userMessagesNotDisplayed==true)  {$result["messengerPulsateUsers"][]=$idUserTmp;}//Ajoute alors l'user au "messengerPulsateUsers"
				}
			}

			////	RETOURNE LE RÉSULTAT AU FORMAT JSON
			$result["livecounterUsersHtml"]=$_SESSION["livecounterUsersHtml"];
			$result["messengerUsersHtml"]=$_SESSION["messengerUsersHtml"];
			$result["messengerMessagesHtml"]=$_SESSION["messengerMessagesHtml"];
			echo json_encode($result);
		}
	}

	/*
	 * AJAX : Update le "time" d'affichage d'un user du messenger
	 */
	public static function actionMessengerUserDisplayTime()
	{
		$_SESSION["messengerUserDisplayTime"][Req::getParam("_idUser")]=time();
	}

	/*
	 * AJAX : Post d'un message sur le messenger (note : les messages sont encodés en "utf8mb4" pour le support des "emoji" sur mobile)
	 */
	public static function actionMessengerPostMessage()
	{
		if(self::$curUser->messengerEnabled())
		{
			$usersIds=Req::getParam("messengerUsers");
			$usersIds[]=self::$curUser->_id;
			$message=Db::formatParam("message");
			if(stristr(Req::getParam("message"),"launchVisioMessage"))  {$message=Db::formatParam("message","editor");}//Appel visio : on ne filtre pas tous les "tags"
			Db::query("INSERT INTO ap_userMessengerMessage SET _idUser=".self::$curUser->_id.", _idUsers=".Db::formatTab2txt($usersIds).", message=".$message.", date=".Db::format(time()));
			$_SESSION["messengerUsers"]=$usersIds;
		}
	}

	/*
	 * VISIO JITSI : URL de la "Room" de l'user courant (Exple : "visioconf.mondomaine.com/5BV3X-omnispace-boby")
	 */
	public static function myVideoRoomURL()
	{
		if(!isset($_SESSION["myVideoRoomURL"]))  {$_SESSION["myVideoRoomURL"]=Ctrl::$agora->visioHost()."/Omnispace-".Txt::uniqId(5)."-".substr(Txt::clean(Ctrl::$curUser->getLabel(),"max"),0,5);}
		return $_SESSION["myVideoRoomURL"];
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
	 * Controle du Captcha (Ajax ou Direct)
	 */
	public static function actionCaptchaControl()
	{
		if($_SESSION["captcha"]==Req::getParam("captcha")){
			if(Req::$curAction=="CaptchaControl")	{echo "true";}//Controle Ajax
			else									{return true;}//Controle Direct
		}
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
			if(!in_array($tmpFile,['.','..']) && File::isType("imageBrowser",$tmpFile)){
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
	 * ACTION : affiche un fichier Ical
	 */
	public static function actionDisplayIcal()
	{
		$objCalendar=self::getTargetObj();
		if(is_object($objCalendar) && $objCalendar->md5IdControl())  {CtrlCalendar::getIcal($objCalendar);}
	}

	/*
	 * Modif l'URL de download/Affichage d'un fichier depuis une mobileApp => modif du controleur, ajout du "nameMd5" et du type de fichier à télécharger
	 */
	public static function appGetFileUrl($downloadUrl, $fileName)
	{
		$downloadUrl.=(stristr($downloadUrl,"ctrl=object"))  ?  "&fileType=attached"  :  "&fileType=modFile";	//Fichier joint d'un objet  OU  Fichier du module "File"  => Toujours modifier en premier !
		$downloadUrl=str_ireplace(["ctrl=object","ctrl=file"],"ctrl=misc",$downloadUrl);						//Switch sur le controleur "ctrl=misc" (cf. "$initCtrlFull=false")
		return $downloadUrl."&nameMd5=".md5($fileName);															//Ajoute le "nameMd5" du controle d'accès (cf. "CtrlObject::actionGetFile()" && "CtrlFile::actionGetFile()")
	}

	/*
	 * ACTION : Download/Affichage d'un fichier depuis une mobileApp (avec controle du "nameMd5" & co)
	 */
	public static function actionGetFile()
	{
		if(Req::isParam(["fileName","filePath"]))		{File::download(Req::getParam("fileName"),Req::getParam("filePath"));}	//Affichage d'un pdf (exple: "Documentation.pdf" du "VueHeaderMenu.php"). Tjs mettre "fromMobileApp=true" dans l'url pour ne pas annuler le "File::download()"
		elseif(Req::getParam("fileType")=="attached")	{CtrlObject::actionGetFile();}											//Download d'un fichier "AttachedFile"
		else											{CtrlFile::actionGetFile();}											//Download d'un fichier du module "File"
	}
}