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


	/*******************************************************************************************
	 * AJAX : UPDATE DU MESSENGER & LIVECOUNTER
	 *******************************************************************************************/
	public static function actionMessengerUpdate()
	{
		//Messenger activé?
		if(self::$curUser->messengerEnabled())
		{
			////	UPDATE LE LIVECOUNTER DE L'USER COURANT EN BDD :  SPECIFIE SI ON EST EN TRAIN DE MODIFIER UN OBJET (via "editObjId")  &&  ENREGISTRE SI BESOIN LE CONTENU DE L'EDITEUR (via "editorDraft")
			$sqlValues="_idUser=".(int)self::$curUser->_id.", ipAdress=".Db::format($_SERVER["REMOTE_ADDR"]).", editObjId=".Db::formatParam("editObjId").", `date`=".Db::format(time());
			if(Req::isParam("editorDraft"))  {$sqlValues.=", editorDraft=".Db::formatParam("editorDraft","editor").", draftTargetObjId=".Db::formatParam("editObjId");}//Vérifie si "editorDraft" est spécifié (pour pas l'effacer..)
			Db::query("INSERT INTO ap_userLivecouter SET ".$sqlValues." ON DUPLICATE KEY UPDATE ".$sqlValues);

			////	INIT LE MESSENGER EN DEBUT DE SESSION
			if(!isset($_SESSION["livecounterUsers"]))
			{
				//Init les variables de session
				$_SESSION["livecounterUsers"]=$_SESSION["messengerMessages"]=$_SESSION["messengerDisplayTimes"]=$_SESSION["messengerCheckedUsers"]=[];
				$_SESSION["livecounterMainHtml"]=$_SESSION["livecounterFormHtml"]=$_SESSION["messengerMessagesHtml"]="";
				//Supprime les livecounters des users déconnectés (+ de 7 jours)  &&  Les vieux messages du messenger (+ de 15 jours. cf. trad "MESSENGER_nobodyTitle")  &&  Les vieilles propositions de visio (+ de 2h)
				Db::query("DELETE FROM ap_userLivecouter WHERE `date` < ".intval(time()-604800));
				Db::query("DELETE FROM ap_userMessengerMessage WHERE `date` < ".intval(time()-1209600));
				Db::query("DELETE FROM ap_userMessengerMessage WHERE message LIKE '%launchVisioMessage%' AND `date` < ".intval(time()-7200));
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
			if($result["livecounterUpdate"]==true)
			{
				$_SESSION["livecounterMainHtml"]=$_SESSION["livecounterFormHtml"]="";//Réinit
				foreach($_SESSION["livecounterUsers"] as $tmpUser)
				{
					//Image/Label des users du livecounter principal
					$userImg=(Req::isMobile()==false && $tmpUser->hasImg())  ?  $tmpUser->getImg(false,true)  :  null;//Verif qu'on soit pas en mode mobile et si l'image existe
					$userTitle=$tmpUser->getLabel()." &nbsp;".$userImg;
					$userFirstName=$tmpUser->getLabel("firstName");
					//Affichage de l'user dans le livecounter principal et le formulaire du messenger
					$_SESSION["livecounterMainHtml"].="<label class='vLivecounterUser' id='livecounterUser".$tmpUser->_id."' onclick='messengerDisplay(".$tmpUser->_id.");' title=\"".Txt::trad("MESSENGER_chatWith")." ".$userTitle."\">".$userImg.$userFirstName."</label>";
					$_SESSION["livecounterFormHtml"].="<div class='vMessengerUser'>
															<input type='checkbox' name='messengerUsers[]' value='".$tmpUser->_id."' id='messengerUserCheckbox".$tmpUser->_id."' class='messengerUserCheckbox' data-user-label=\"".$userFirstName."\" data-user-label-visio=\"".Txt::clean($userFirstName,"max")."\">
															<label for='messengerUserCheckbox".$tmpUser->_id."' title=\"".Txt::trad("select")." ".$userTitle."\">".$userImg.$userFirstName."</label>
													   </div>";
				}
				//Ajoute "inverser la sélection" si ya + de 5 users
				if(count($_SESSION["livecounterUsers"])>5)	{$_SESSION["livecounterFormHtml"].="<div class='vMessengerUser'><label onclick=\"$('label[for^=messengerUserCheckbox]').trigger('click');\"><img src='app/img/checkSelect.png'> &nbsp; ".Txt::trad("invertSelection")."</label></div>";}
			}

			////	LISTE DES MESSAGES DU MESSENGER  &&  DES "PULSATES"
			if($result["messengerUpdate"]==true)
			{
				//Init la liste des messages & la liste des users connectés (y compris l'user courant)
				$userConnectedIds=array_merge(array_keys($_SESSION["livecounterUsers"]), [self::$curUser->_id]);
				$_SESSION["messengerMessagesHtml"]="";//init
				foreach($_SESSION["messengerMessages"] as $message)
				{
					//Label/icone de l'auteur du message
					$destList=Txt::txt2tab($message["_idUsers"]);
					$autorObj=self::getObj("user",$message["_idUser"]);
					if(Req::isMobile())				{$dateAutor=$autorObj->getLabel("firstName")."<br>".date("H:i",$message["date"]);}	//Responsive :  "Will<br>11:00 "
					elseif($autorObj->hasImg())		{$dateAutor=date("H:i",$message["date"]).$autorObj->getImg(false,true);}			//Mode normal avec icone de l'user : "11:00 <img>"
					else							{$dateAutor=date("H:i",$message["date"])." - ".$autorObj->getLabel("firstName");}	//Mode normal avec label de l'user : "11:00 - Will"
					if(count($destList)>2)  {$dateAutor.="<img src='app/img/user/iconSmall.png' class='iconUsersMultiple'>";}			//Ajoute si besoin l'icone de discussion à plusieurs
					//Title de l'auteur et des destinataires
					$oldMessageClass="vMessengerOldMessage";//Par défaut on ajoute la class "vMessengerOldMessage"...
					$messageTitle=Txt::dateLabel($message["date"],"full")." : ".Txt::trad("MESSENGER_messageFrom")." ".$autorObj->getLabel()." ".Txt::trad("MESSENGER_messageTo")." ";//Date/heure et auteur du message 
					foreach($destList as $_idUserDest){
						if($_idUserDest!=$autorObj->_id) {$messageTitle.=self::getObj("user",$_idUserDest)->getLabel().", ";}	//Ajoute le libellé du destinataire
						if(array_key_exists($_idUserDest,$_SESSION["livecounterUsers"]))  {$oldMessageClass=null;}				//Le message est bien affecté à un user connnecté : on retire la class "vMessengerOldMessage" 
					}
					//Affichage du message
					$_SESSION["messengerMessagesHtml"].="<table class='vMessengerMessage ".$oldMessageClass."' title=\"".rtrim($messageTitle,", ")."\" data-idUsers=\"".$message["_idUsers"]."\"><tr>
															<td class='vMessengerMessageDateAutor'>".$dateAutor."</td>
															<td data-idAutor=\"".$autorObj->_id."\">".$message["message"]."</td>
														 </tr></table>";
				}
			}

			////	"PULSATE" LE AUTEURS DES MESSAGES QUI N'ONT PAS ENCORE ÉTÉ VU
			$result["livecounterUsersPulsate"]=[];
			foreach($_SESSION["messengerMessages"] as $message){
				//Pas de pulsate si le message est trop ancien (on n'insiste pas: 120s max en mode normal ou 30s max en responsive)
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
			$result["livecounterMainHtml"]=$_SESSION["livecounterMainHtml"];
			$result["livecounterFormHtml"]=$_SESSION["livecounterFormHtml"];
			$result["messengerMessagesHtml"]=$_SESSION["messengerMessagesHtml"];
			$result["messengerCheckedUsers"]=$_SESSION["messengerCheckedUsers"];
			echo json_encode($result);
		}
	}

	/*******************************************************************************************
	 * AJAX : UPDATE LE "MessengerDisplayTimes" : AFFICHAGE D'UN USER OU DE "ALL"
	 *******************************************************************************************/
	public static function actionMessengerDisplayTimesUpdate()
	{
		//Update l'affichage de l'user affiché || Update l'affichage de tous les users du livecounter ("messengerDisplayMode==all")
		if(is_numeric(Req::getParam("messengerDisplayMode")))  {$_SESSION["messengerDisplayTimes"][Req::getParam("messengerDisplayMode")]=time();}
		elseif(Req::getParam("messengerDisplayMode")=="all"){
			foreach($_SESSION["livecounterUsers"] as $tmpUser)  {$_SESSION["messengerDisplayTimes"][$tmpUser->_id]=time();}
		}
	}

	/*******************************************************************************************
	 * AJAX : POST D'UN MESSAGE SUR LE MESSENGER (note : les messages sont encodés en "utf8mb4" pour le support des "emoji" sur mobile)
	 *******************************************************************************************/
	public static function actionMessengerPost()
	{
		if(self::$curUser->messengerEnabled())
		{
			//Init les destinataires du message et le message
			$usersIds=Req::getParam("messengerUsers");
			$usersIds[]=self::$curUser->_id;
			$message=Db::formatParam("message");
			//Proposition de visio : modifie le message
			if(stristr(Req::getParam("message"),"launchVisio")){
				$message=Db::formatParam("message","editor");//Récupère le message sans filtrer les tags html (mode "editor")
				Db::query("DELETE FROM ap_userMessengerMessage WHERE _idUser=".self::$curUser->_id." AND _idUsers=".Db::formatTab2txt($usersIds)." AND message=".$message);//Supprime si besoin les anciens liens de visio identiques
			}
			//Enregistre le message
			Db::query("INSERT INTO ap_userMessengerMessage SET _idUser=".self::$curUser->_id.", _idUsers=".Db::formatTab2txt($usersIds).", message=".$message.", `date`=".Db::format(time()));
			//Update les users "checked" lors d'une discussion à plusieurs (3 users minimum : user courant + 2 destinataires au moins)
			if(count($usersIds)>=3)  {$_SESSION["messengerCheckedUsers"]=$usersIds;}
		}
	}

	/*******************************************************************************************
	 * ACTION : RECHERCHE D'OBJETS SUR TOUS L'ESPACE
	 *******************************************************************************************/
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

	/*******************************************************************************************
	 * VUE : MENU "CAPTCHA"
	 *******************************************************************************************/
	public static function menuCaptcha()
	{
		return self::getVue(Req::commonPath."VueCaptcha.php");
	}

	/*******************************************************************************************
	 *  ACTION : AFFICHE L'IMAGE D'UN MENU "CAPTCHA"
	 *******************************************************************************************/
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

	/*******************************************************************************************
	 * COULEUR AU FORMAT HEXADECIMAL POUR UN CAPTCHA
	 *******************************************************************************************/
	protected static function captchaColor($colors)
	{
		return preg_match("/^#?([\dA-F]{6})$/i",$colors,$rgb) ? hexdec($rgb[1]) : false;
	}

	/*******************************************************************************************
	 * CONTROLE DU CAPTCHA (AJAX OU DIRECT)
	 *******************************************************************************************/
	public static function actionCaptchaControl()
	{
		if($_SESSION["captcha"]==Req::getParam("captcha")){
			if(Req::$curAction=="CaptchaControl")	{echo "true";}//Controle Ajax
			else									{return true;}//Controle Direct
		}
	}

	/*******************************************************************************************
	 * VUE : INITIALISATION DE L'EDITEUR TINYMCE (doit déjà y avoir un champ "textarea")
	 *******************************************************************************************/
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

	/*******************************************************************************************
	 * VUE : AFFICHE DES PERSONNES SUR UNE CARTE (contacts/utilisateurs)
	 *******************************************************************************************/
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

	/*******************************************************************************************
	 * VUE : MENU DE SELECTION DU WALLPAPER
	 *******************************************************************************************/
	public static function menuWallpaper($curWallpaper)
	{
		//Wallpapers disponibles
		$vDatas["wallpaperList"]=array();
		$filesList=array_merge(scandir(PATH_WALLPAPER_DEFAULT),scandir(PATH_WALLPAPER_CUSTOM));
		foreach($filesList as $tmpFile){
			if(!in_array($tmpFile,['.','..']) && File::isType("imageBrowser",$tmpFile)){
				$path=(is_file(PATH_WALLPAPER_DEFAULT.$tmpFile))  ?  PATH_WALLPAPER_DEFAULT.$tmpFile  :  PATH_WALLPAPER_CUSTOM.$tmpFile;
				$value=(is_file(PATH_WALLPAPER_DEFAULT.$tmpFile))  ?  WALLPAPER_DEFAULT_DB_PREFIX.$tmpFile  :  $tmpFile;
				$nameRacine=str_replace(File::extension($tmpFile),null,$tmpFile);
				$vDatas["wallpaperList"][]=array("path"=>$path, "value"=>$value, "name"=>$tmpFile, "nameRacine"=>$nameRacine);
			}
		}
		//Affiche le menu
		$vDatas["wallpaperList"]=Tool::sortArray($vDatas["wallpaperList"],"nameRacine");
		$vDatas["curWallpaper"]=$curWallpaper;
		return self::getVue(Req::commonPath."VueMenuWallpaper.php",$vDatas);
	}

	/*******************************************************************************************
	 * PATH D'UN WALLPAPER  (cf. Ctrl::$curSpace->wallpaper && Ctrl::$agora->wallpaper)
	 *******************************************************************************************/
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

	/*******************************************************************************************
	 * ACTION : AFFICHE UN FICHIER ICAL
	 *******************************************************************************************/
	public static function actionDisplayIcal()
	{
		$objCalendar=self::getTargetObj();
		if(is_object($objCalendar) && $objCalendar->md5IdControl())  {CtrlCalendar::getIcal($objCalendar);}
	}

	/*******************************************************************************************
	 * MODIF L'URL DE DOWNLOAD/AFFICHAGE D'UN FICHIER DEPUIS UNE MOBILEAPP => modif du controleur, ajout du "nameMd5" et du type de fichier à télécharger
	 *******************************************************************************************/
	public static function appGetFileUrl($downloadUrl, $fileName)
	{
		$downloadUrl.=(stristr($downloadUrl,"ctrl=object"))  ?  "&fileType=attached"  :  "&fileType=modFile";	//Fichier joint d'un objet  OU  Fichier du module "File"  => Toujours modifier en premier !
		$downloadUrl=str_ireplace(["ctrl=object","ctrl=file"],"ctrl=misc",$downloadUrl);						//Switch sur le controleur "ctrl=misc" (cf. "$initCtrlFull=false")
		return $downloadUrl."&nameMd5=".md5($fileName);															//Ajoute le "nameMd5" du controle d'accès (cf. "CtrlObject::actionGetFile()" && "CtrlFile::actionGetFile()")
	}

	/*******************************************************************************************
	 * ACTION : DOWNLOAD/AFFICHAGE D'UN FICHIER DEPUIS UNE MOBILEAPP (avec controle du "nameMd5" & co)
	 *******************************************************************************************/
	public static function actionGetFile()
	{
		if(Req::isParam(["fileName","filePath"]))		{File::download(Req::getParam("fileName"),Req::getParam("filePath"));}	//Affichage d'un pdf (exple: "Documentation.pdf" du "VueHeaderMenu.php"). Tjs mettre "fromMobileApp=true" dans l'url pour ne pas annuler le "File::download()"
		elseif(Req::getParam("fileType")=="attached")	{CtrlObject::actionGetFile();}											//Download d'un fichier "AttachedFile"
		else											{CtrlFile::actionGetFile();}											//Download d'un fichier du module "File"
	}
}