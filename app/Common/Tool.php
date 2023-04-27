<?php
/**
* This file is part of the Agora-Project Software package.
*
* @copyright (c) Agora-Project Limited <https://www.agora-project.net>
* @license GNU General Public License, version 2 (GPL-2.0)
*/


//Namespace de PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


/*
 * CLASSE "BOITE À OUTILS"
 */
class Tool
{
	/*******************************************************************************************
	 * ENVOI D'UN MAIL
	 * ***************
	 * $options des mails ("in_array()" pour les tests)
	 * -"hideRecipients":	Masque les destinataires du mail : ajoute tout le monde en copie caché via "AddBCC()"
	 * -"addReplyTo":		Ajoute l'email de l'expériteur dans le "replyTo"
	 * -"receptionNotif":	Demande un accusé de réception pour l'user/expéditeur
	 * -"noFooter": 		Masque le footer du message (la signature) : label de l'expéditeur, lien vers l'espace, logo du footer de l'espace
	 * -"noNotify": 		Pas de notification concernant le succès ou l'échec de l'envoi du mail (cf. "notify()")
	 * -"objectNotif": 		Affiche "L'email de notification a bien été envoyé"  au lieu de  "L'email a bien été envoyé" (cf notif d'edition d'un objet)
	 * Notes :
	 * - toujours mettre en place un SPF, DKIM et REVERS DNS (évite la spambox)
	 * - tester l'envoi des emails via https://www.mail-tester.com/
	 *******************************************************************************************/
	public static function sendMail($mailsTo, $subject, $message, $options=null, $attachedFiles=null)
	{
		////	Vérifs de base && Init les options
		if(empty($mailsTo) || empty($message))	{return false;}
		if(empty($options))  {$options=[];}

		////	Charge une première fois PHPMailer et crée une nouvelle instance
		if(!defined("phpmailerLoaded")){
			require 'app/misc/PHPMailer/src/Exception.php';
			require 'app/misc/PHPMailer/src/PHPMailer.php';
			require 'app/misc/PHPMailer/src/SMTP.php';
			define("phpmailerLoaded",true);
		}
		$mail=new PHPMailer();

		////	Envoi l'email via PHPMailer
		try{
			////	Parametrage CHARSET / DKIM / SMTP
			$mail->CharSet="UTF-8";
			if(defined("DKIM_domain") && defined("DKIM_private") && defined("DKIM_selector"))   {$mail->DKIM_domain=DKIM_domain;   $mail->DKIM_private=DKIM_private;   $mail->DKIM_selector=DKIM_selector;}
			if(Req::isDevServer() && is_file("../PARAMS/smtp.inc.php"))  {require_once "../PARAMS/smtp.inc.php";}//Config spécifique (Ctrl::$agora->sendmailFrom & co)
			if(!empty(Ctrl::$agora->smtpHost) && !empty(Ctrl::$agora->smtpPort)){
				$mail->isSMTP();
				$mail->Host=Ctrl::$agora->smtpHost;
				$mail->Port=(int)Ctrl::$agora->smtpPort;
				if(empty(Ctrl::$agora->smtpSecure))	{$mail->SMTPAutoTLS=false;}						//Specifie qu'il n'y a pas de connexion TLS
				else								{$mail->SMTPSecure=Ctrl::$agora->smtpSecure;}	//Précise le type de connexion sécurisé TLS
				if(preg_match("/WIN/i",PHP_OS) && !empty(Ctrl::$agora->smtpSecure))  {$mail->SMTPOptions=['ssl'=>['verify_peer'=>false,'verify_peer_name'=>false,'allow_self_signed'=>true]];}//TLS bridé sur Wamp
				if(!empty(Ctrl::$agora->smtpUsername) && !empty(Ctrl::$agora->smtpPass))   {$mail->Username=Ctrl::$agora->smtpUsername;  $mail->Password=Ctrl::$agora->smtpPass;  $mail->SMTPAuth=true;}//Connection authentifié
			}

			////	Expediteur
			$serverName=str_replace("www.","",$_SERVER["SERVER_NAME"]);															//Domaine du serveur (pas de $_SERVER['HTTP_HOST'])
			$fromMail=(!empty(Ctrl::$agora->sendmailFrom))  ?  Ctrl::$agora->sendmailFrom  :  "ne_pas_repondre@".$serverName;	//Email du paramétrage général OU du domaine courant (ex: "ne_pas_repondre@mondomaine.net")
			$mail->SetFrom($fromMail, ucfirst($serverName));																	//"SetFrom" fixe (cf. score des antispams)
			//Controles de base
			if(in_array("noTimeControl",$options)==false && (time()-@$_SESSION["sendMailTime"])<10)	{echo "please wait 10 sec."; exit;}	//Temps minimum entre chaque mail
			else																					{$_SESSION["sendMailTime"]=time();}	//Enregistre le timestamp de l'envoi
			if(empty($_SESSION["sendMailCounter"][date("Y-m-d-H")]))	{$_SESSION["sendMailCounter"][date("Y-m-d-H")]=1;}		//Init le compteur de nb max de mail/heure
			elseif($_SESSION["sendMailCounter"][date("Y-m-d-H")]>=30)	{echo "30 mails/h max"; exit;}							//- quota dépassé
			else														{$_SESSION["sendMailCounter"][date("Y-m-d-H")]++;}		//- incrémente le compteur
			if(Req::isHost())  {Host::sendMailControl($message);}																//Controle des spambots
			$fromUserWithMail=(isset(Ctrl::$curUser) && Ctrl::$curUser->isUser() && !empty(Ctrl::$curUser->mail));				//Verif que l'expediteur est un user authentifié avec un email
			//Ajoute si besoin l'email de l'user en replyTo ou pour une demande de notif de lecture
			if($fromUserWithMail==true){
				if(in_array("addReplyTo",$options))		{$mail->AddReplyTo(Ctrl::$curUser->mail, Ctrl::$curUser->getLabel());}	//Ajoute si besoin un "ReplyTo" avec son email (tjs en option: cf. score des antispams)
				if(in_array("receptionNotif",$options))	{$mail->ConfirmReadingTo=Ctrl::$curUser->mail;}							//Ajoute une demande de notification de lecture (envoyé à l'expéditeur du présent mail)
			}

			////	Destinataires (idUser au format text/array)
			$mailsToNotif=null;																									//Prépare la notification finale via "notify()"
			if($fromUserWithMail==true && in_array("hideRecipients",$options))  {$mail->AddAddress(Ctrl::$curUser->mail);}		//Destinataires masqués: ajoute l'expéditeur en email principal (tjs en option: cf. score des antispams)
			if(is_string($mailsTo))  {$mailsTo=explode(",",trim($mailsTo,","));}												//Liste des destinataires au format "array"
			foreach((array)Req::param("specificMails") as $tmpMail){  if(Txt::isMail($tmpMail)) {$mailsTo[]=$tmpMail;}  }		//Ajoute des emails spécifiques/complémentaires
			$mailsTo=array_unique($mailsTo);																					//Elimine les éventuels doublons
			//Ajoute chaque destinataire en adresse principale ou BCC (Copie cachée)
			foreach($mailsTo as $tmpDest){
				if(is_numeric($tmpDest) && isset(Ctrl::$curUser))  {$tmpDest=Ctrl::getObj("user",$tmpDest)->mail;}				//Récupère l'email d'un user
				if(!empty($tmpDest)){																							//Email existe bien
					$tmpDest=filter_var($tmpDest, FILTER_SANITIZE_EMAIL);														//Enlève les espace et autre caractères (indispensable!)
					if(PHPMailer::validateAddress($tmpDest)){																	//Email valide
						$mailsToNotif.=", ".$tmpDest;																			//Ajoute l'email pour les notifs
						if(in_array("hideRecipients",$options))	{$mail->AddBCC($tmpDest);}										//Ajoute l'email en copy caché
						else									{$mail->AddAddress($tmpDest);}									//Ajoute l'email en clair
					}
				}
			}

			////	Sujet & message
			$mail->Subject=(!empty(Ctrl::$agora->name))  ?  ucfirst($subject)." - ".Ctrl::$agora->name  :  ucfirst($subject);									//"Sujet de mon email - Mon espace"
			if(in_array("noFooter",$options)==false && !empty(Ctrl::$agora->name) && !empty(Ctrl::$curUser)){													//Footer du message :
				$curSpaceLabel=ucfirst(Ctrl::$agora->name);																										//Label de l'espace
				if(!empty(Ctrl::$curSpace->name) && Ctrl::$agora->name!=Ctrl::$curSpace->name)  {$curSpaceLabel.=" &raquo; ".Ctrl::$curSpace->name;}			//Ajoute le nom du sous-espace (">> sous-espace")
				$curUserLabel=(Ctrl::$curUser->isUser())  ?  Txt::trad("MAIL_sendBy")." ".Ctrl::$curUser->getLabel().", "  :  null;								//"Envoyé par boby SMITH"...
				$message.="<br><br>".$curUserLabel.Txt::trad("MAIL_fromTheSpace")." <a href=\"".Req::getCurUrl()."\" target='_blank'>".$curSpaceLabel."</a>";	//"Depuis <a>mon-espace</a>"
			}
			$mail->msgHTML($message);

			////	Logo du footer en fin de mail : logo spécifique ou par défaut (toujours mettre un "alt", même vide : cf. score des antispams)
			$logoFooterPath=(!empty(Ctrl::$agora->logo))  ?  Ctrl::$agora->pathLogoFooter()  :  "app/img/logoLabel.png";
			if(in_array("noFooter",$options)==false && is_file($logoFooterPath)){
				$mail->AddEmbeddedImage($logoFooterPath,"logoFooterId");
				$mail->msgHTML($message."<br><br><img src='cid:logoFooterId' style='max-height:100px' alt=''>");
			}

			////	Fichiers joints à ajouter
			if(!empty($attachedFiles)){
				$fileSizeCpt=0;
				foreach($attachedFiles as $tmpFile){
					//Taille du fichier
					$tmpFileSize=@filesize($tmpFile["path"]);
					//Controle l'accès et la taille du fichier (25M max par défaut)  ||  Fichier Ok : on l'ajoute à l'email
					if(!is_file($tmpFile["path"]) || ($fileSizeCpt+$tmpFileSize) > File::mailMaxFilesSize)  {Ctrl::notify(Txt::trad("MAIL_attachedFileError")."<br>".$tmpFile["name"]." = ".File::displaySize($tmpFileSize)." (".File::displaySize(File::mailMaxFilesSize)." max)");}
					else{
						$fileSizeCpt+=$tmpFileSize;//Ajoute la taille du fichier au compteur
						if(!empty($tmpFile["cid"]))			{$mail->AddEmbeddedImage($tmpFile["path"],$tmpFile["cid"]);}	//Intègre une image dans le message (ex: CID="XYZ" correspond à "<img src='cid:XYZ'>")
						elseif(!empty($tmpFile["name"]))	{$mail->AddAttachment($tmpFile["path"],$tmpFile["name"]);}		//Ajoute un fichier joint classique
					}
				}
			}

			////	Envoi du mail + rapport d'envoi si demandé
			$sendReturn=$mail->Send();
			if(in_array("noNotify",$options)==false){																										//Affiche une notification si l'email a été envoyé ou pas 
				$notifMail=(in_array("objectNotif",$options))  ?  Txt::trad("MAIL_sendNotif")  :  Txt::trad("MAIL_sendOk");									//Affiche si besoin "L'email de notification a bien été envoyé"
				if($sendReturn==true)			{Ctrl::notify($notifMail."<br><br>".Txt::trad("MAIL_recipients")." : ".trim($mailsToNotif,","), "success");}//Mail correctement envoyé
				if(!empty($mail->ErrorInfo))	{Ctrl::notify("Mailer Error :<br>".Txt::clean($mail->ErrorInfo));}											//Mail envoyé avec des erreurs
			}
			return $sendReturn;//tjs renvoyer
		}
		////	Exception PHPMailer
		catch (Exception $error){
			Ctrl::notify(Txt::trad("MAIL_notSend")."<br><br>Mailer Error :<br>".Txt::clean($mail->ErrorInfo));
		}
	}

	/*******************************************************************************************
	 * URL FILTRÉ DES PARAMETRES PASSÉS EN GET
	 *******************************************************************************************/
	public static function getParamsUrl($paramsExclude=null)
	{
		//Init
		$getParamsUrl=[];
		$paramsExclude=(!empty($paramsExclude)) ? explode(",",$paramsExclude) : array();
		//Filtre les parametres passés en Get
		parse_str($_SERVER["QUERY_STRING"],$getParams);//$getParams est retourné par "parse_str()"
		foreach($getParams as $paramKey=>$paramVal){
			if(!in_array($paramKey,$paramsExclude))  {$getParamsUrl[$paramKey]=$paramVal;}
		}
		//Renvoie l'url à partir du tableau
		return "?".http_build_query($getParamsUrl);
	}

	/*******************************************************************************************
	 * TRI UN TABLEAU MULTIDIMENTIONNEL
	 *******************************************************************************************/
	public static function sortArray($sortedArray, $sortedField)
	{
		// Créé un tableau temporaire avec uniquement le champ à trier, puis trie ce tableau
		$tmpArray=$returnArray=[];
		foreach($sortedArray as $key=>$value)  {$tmpArray[$key]=$value[$sortedField];}
		asort($tmpArray);
		// Reconstruit le tableau multidimensionnel à partir tableau temporaire trié, puis renvoie le résultat
		foreach($tmpArray as $key=>$value)	{$returnArray[$key]=$sortedArray[$key];}
		return $returnArray;
	}

	/*******************************************************************************************
	 * RECHERCHE UNE VALEUR DANS UN TABLEAU MULTIDIMENTIONNEL
	 *******************************************************************************************/
	public static function arraySearch($curTable, $searchValue)
	{
		if(is_array($curTable)){
			//Dans le tableau courant
			if(in_array($searchValue,$curTable))  {return true;}
			//Dans un sous-tableaux ? (recherche récursive)
			foreach($curTable as $tableElem){
				if(is_array($tableElem) && self::arraySearch($tableElem,$searchValue))	{return true;}
			}
			//Sinon Recherche infructueuse
			return false;
		}
	}

	/***************************************************************************************************************************/
	/*******************************************	SPECIFIC METHODS	********************************************************/
	/***************************************************************************************************************************/

	// Tableau des timeszones
	public static $tabTimezones=array(
		"Kwajalein"=>"-12:00",
		"Pacific/Midway"=>"-11:00",
		"Pacific/Honolulu"=>"-10:00",
		"America/Anchorage"=>"-9:00",
		"America/Los_Angeles"=>"-8:00",
		"America/Denver"=>"-7:00",
		"America/Mexico_City"=>"-6:00",
		"America/New_York"=>"-5:00",
		"America/Guyana"=>"-4:00",
		"America/Buenos_Aires"=>"-3:00",
		"America/Sao_Paulo"=>"-3:00",
		"Atlantic/South_Georgia"=>"-2:00",
		"Atlantic/Azores"=>"-1:00",
		"Europe/London"=>"0:00",
		"Europe/Paris"=>"1:00",
		"Europe/Helsinki"=>"2:00",
		"Europe/Moscow"=>"3:00",
		"Asia/Dubai"=>"4:00",
		"Asia/Karachi"=>"5:00",
		"Asia/Dhaka"=>"6:00",
		"Asia/Jakarta"=>"7:00",
		"Asia/Hong_Kong"=>"8:00",
		"Asia/Tokyo"=>"9:00",
		"Australia/Sydney"=>"10:00",
		"Asia/Magadan"=>"11:00",
		"Pacific/Fiji"=>"12:00",
		"Pacific/Tongatapu"=>"13:00");
	
	
	/*******************************************************************************************
	 * COLORPICKER / SELECTEUR DE COULEURS ($bgTxtColor : "background-color"/"color")
	 *******************************************************************************************/
	public static function colorPicker($inputText, $inputColor, $bgTxtColor="background-color")
	{
		$colorMap=null;
		$menuContextId=Txt::uniqId();
		$colors=array("#9b9b9b","#cb0000","#f56b00","#ffcb2f","#f482a4","#32cb00","#00d2cb","#3166ff","#6434fc","#656565","#9a0000","#ce6301","#cd9934","#999903","#009901","#329a9d","#3531ff","#6200c9","#343434","#680100","#963400","#986536","#646809","#036400","#34696d","#00009b","#303498","#000000","#330001","#643403","#663234","#343300","#013300","#003532","#010066","#340096");
		foreach(array_reverse($colors) as $key=>$tmpColor){
			$colorMap.="<div class='colorPickerCell' style=\"background:".$tmpColor.";\" OnClick=\"$('#".$inputText."').css('".$bgTxtColor."','".$tmpColor."'); $('#".$inputColor."').val('".$tmpColor."');\">&nbsp;</div>";
			if((($key+1)%9)==0)	{$colorMap.="</div><div class='colorPickerRow'>";}
		}
		return "<div class='colorPicker menuContext' id='".$menuContextId."'>
					<div class='colorPickerTable'><div class='colorPickerRow'>".$colorMap."</div></div>
				</div>
				<img src='app/img/colorPicker.png' class='menuLaunch' for='".$menuContextId."'>";
	}

	/*******************************************************************************************
	 * BARRE DE POURCENTAGE
	 *******************************************************************************************/
	public static function percentBar($fillPercent, $txtBar, $txtTooltip, $orangeBarAlert=false, $barWidth=null)
	{
		//Width de "100%" par défaut && Remplissage à 100% maximum
		if(empty($barWidth))	{$barWidth="100%";}
		if($fillPercent>100)	{$fillPercent=100;}
		//Couleur de barre de remplissage
		if($orangeBarAlert==true)	{$percentBarImg="percentBarAlert";}		//avancement retard ou autre (barre orange)
		elseif($fillPercent==100)	{$percentBarImg="percentBar100";}		//terminé à 100% : vert
		else						{$percentBarImg="percentBarCurrent";}	//en cours : vert clair
		//renvoie la percentbar
		return "<div class='percentBar' style='width:".$barWidth.";' title=\"".Txt::tooltip($txtTooltip)."\">
					<div class='percentBarContent' style='background-image:url(app/img/".$percentBarImg.".png);background-size:".(int)$fillPercent."% 100%;'>".$txtBar."</div>
				</div>";
	}
}