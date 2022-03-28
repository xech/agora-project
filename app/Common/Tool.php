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
	 * $options des mails (utiliser "in_array()" pour les tests)
	 * -"hideRecipients":	Masque les destinataires du mail : ajoute tout le monde en copie caché via "AddBCC()"
	 * -"hideUserLabel":	Masque le label de l'user/expéditeur ("Host::notifMail()" : notif automatique)
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
		try {
			$mail->CharSet="UTF-8";

			////	Parametrage DKIM / SMTP
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
			$fromDomain=str_replace("www.","",$_SERVER["SERVER_NAME"]);															//Se base sur le Domaine du serveur (pas sur l'url et le $_SERVER['HTTP_HOST'])
			$fromMail=(!empty(Ctrl::$agora->sendmailFrom))  ?  Ctrl::$agora->sendmailFrom  :  "postmaster@".$fromDomain;		//Email du paramétrage général OU du domaine courant (ex: "postmaster@mondomaine.net")
			$fromLabel=(!empty(Ctrl::$agora->name))  ?  Ctrl::$agora->name  :  $fromDomain;										//Nom de l'espace OU Domaine courant
			$mail->SetFrom($fromMail, $fromLabel);																				//"SetFrom" : utiliser un email correspondant au domaine courant ou un SMTP spécifique (evite la spambox)
			$fromConnectedUser=(isset(Ctrl::$curUser) && method_exists(Ctrl::$curUser,"isUser") && Ctrl::$curUser->isUser());	//Vérif que l'email est envoyé depuis un user connecté
			//Ajoute si besoin le libellé de l'user connecté
			if(in_array("hideUserLabel",$options)==false && $fromConnectedUser){
				$mail->SetFrom($fromMail, $fromLabel." - ".Ctrl::$curUser->getLabel());											//Ajoute le nom de l'user dans le $fromLabel (ex: "Mon espace - BOB SMITH <bob@domaine.tld>")
				if(!empty(Ctrl::$curUser->mail)){																				//Verif si l'user courant a bien spécifié un email
					$mail->AddReplyTo(Ctrl::$curUser->mail, Ctrl::$curUser->getLabel());										//Ajoute son email à "ReplyTo" ?
					if(in_array("receptionNotif",$options))	{$mail->ConfirmReadingTo=Ctrl::$curUser->mail;}						//Demande une confirmation de lecture à l'user courant?
				}
			}

			////	Destinataires (idUser au format text/array)
			$mailsToNotif=null;																																//Prépare la notification finale
			if(in_array("hideRecipients",$options) && $fromConnectedUser==true && !empty(Ctrl::$curUser->mail))  {$mail->AddAddress(Ctrl::$curUser->mail);}	//Destinataires masqués: ajoute l'expéditeur en email principal (evite la spambox)
			if(is_string($mailsTo))  {$mailsTo=explode(",",trim($mailsTo,","));}																			//Liste des destinataires au format "array"
			$mailsTo=array_unique($mailsTo);																												//Elimine les éventuels doublons
			//Ajoute chaque destinataire en adresse principale ou BCC (Copie cachée)
			foreach($mailsTo as $tmpDest){
				if(is_numeric($tmpDest) && method_exists(Ctrl::$curUser,"isUser"))	{$tmpDest=Ctrl::getObj("user",$tmpDest)->mail;}
				if(!empty($tmpDest)){
					$mailsToNotif.=", ".$tmpDest;
					if(in_array("hideRecipients",$options))	{$mail->AddBCC($tmpDest);}
					else									{$mail->AddAddress($tmpDest);}
				}
			}

			////	Sujet & message
			$mail->Subject=$subject;
			if(in_array("noFooter",$options)==false && !empty(Ctrl::$agora->name) && !empty(Ctrl::$curUser)){
				$fromTheSpace=ucfirst(Ctrl::$agora->name);																										//Nom de l'espace principal
				if(!empty(Ctrl::$curSpace->name) && Ctrl::$agora->name!=Ctrl::$curSpace->name)	{$fromTheSpace.=" &raquo; ".Ctrl::$curSpace->name;}				//Ajoute si besoin le nom du sous-espace ("&raquo;"=">>")
				$messageSendBy=(Ctrl::$curUser->isUser())  ?  Txt::trad("MAIL_sendBy")." ".Ctrl::$curUser->getLabel().", "  :  null;							//"Envoyé par boby SMITH"
				$message.="<br><br>".$messageSendBy.Txt::trad("MAIL_fromTheSpace")." <a href=\"".Req::getCurUrl()."\" target='_blank'>".$fromTheSpace."</a>";	//"depuis l'espace Mon espace"
			}
			$mail->MsgHTML($message);

			////	Logo du footer en fin de mail (signe avec un logo spécifique ou le logo par défaut)
			$logoFooterPath=(!empty(Ctrl::$agora->logo))  ?  Ctrl::$agora->pathLogoFooter()  :  "app/img/logoLabel.png";
			if(in_array("noFooter",$options)==false && is_file($logoFooterPath)){
				$mail->AddEmbeddedImage($logoFooterPath,"logoFooterId");
				$mail->MsgHTML($message."<br><br><img src='cid:logoFooterId' style='max-height:100px'>");
			}

			////	Fichiers joints à ajouter
			if(!empty($attachedFiles))
			{
				$fileSizeCpt=0;
				foreach($attachedFiles as $tmpFile)
				{
					//Limite à 20Mo la taille de tous les fichiers, pour pas être rejeté par les serveurs de messagerie
					$tmpFileSize=@filesize($tmpFile["path"]);
					if(is_file($tmpFile["path"]) && ($fileSizeCpt+$tmpFileSize)<File::mailMaxFilesSize){
						$fileSizeCpt+=$tmpFileSize;//Ajoute la taille du fichier au compteur
						if(!empty($tmpFile["cid"]))			{$mail->AddEmbeddedImage($tmpFile["path"],$tmpFile["cid"]);}//Intègre une image dans le message (exple : CID="XYZ" correspond à "<img src='cid:XYZ'>")
						elseif(!empty($tmpFile["name"]))	{$mail->AddAttachment($tmpFile["path"],$tmpFile["name"]);}	//Ajoute un fichier joint classique
					}
				}
			}

			////	Envoi du mail + rapport d'envoi si demandé
			$isSendMail=$mail->Send();
			if(in_array("noNotify",$options)==false){																										//Affiche une notification si l'email a été envoyé ou pas 
				$notifMail=(in_array("objectNotif",$options))  ?  Txt::trad("MAIL_sendNotif")  :  Txt::trad("MAIL_sendOk");									//Affiche si besoin "L'email de notification a bien été envoyé"
				if($isSendMail==true)		{Ctrl::notify($notifMail."<br><br>".Txt::trad("MAIL_recipients")." : ".trim($mailsToNotif,","), "success");}	//Mail correctement envoyé
				elseif(count($mailsTo)>=2)	{Ctrl::notify("MAIL_notSendEverybody");}																		//Mail non envoyé à tous les destinataires
				else						{Ctrl::notify("MAIL_notSend");}																					//Mail non envoyé
			}
			return $isSendMail;
		}
		////	Sinon envoi une exception PHPMailer
		catch (Exception $e){
			echo Txt::trad("MAIL_notSend").". PHPMailer Error : ".$mail->ErrorInfo;
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