<?php
/**
* This file is part of the Agora-Project Software package.
*
* @copyright (c) Agora-Project Limited <https://www.agora-project.net>
* @license GNU General Public License, version 2 (GPL-2.0)
*/


//Namespace de PHPMailer v6.0.5
//use PHPMailer\PHPMailer\PHPMailer;


/*
 * Classe "boite à outils"
 */
class Tool
{
	private static $_winEnv=null;
	private static $_linuxEnv=null;

	/*
	 * Envoi d'un mail
	 */
	public static function sendMail($mailsTo, $subject, $message, $options=null, $attachedFiles=null)
	{
		////	Vérification de base
		if(empty($mailsTo) || empty($message))	{return false;}

		////	Options par defaut à "False" !
		$opt["senderNoReply"]=(stristr($options,"senderNoReply")) ? true : false;		//Défaut : affiche le nom/prénom de l'expéditeur du mail (sinon "noreply")
		$opt["receptionNotif"]=(stristr($options,"receptionNotif")) ? true : false;		//Défaut : pas de notification de réception du message (à l'expéditeur)
		$opt["hideRecipients"]=(stristr($options,"hideRecipients")) ? true : false;		//Défaut : affiche les destinataires du mail à tous le monde
		$opt["noFooter"]=(stristr($options,"noFooter")) ? true : false;					//Défaut : affiche un footer dans le message
		$opt["addLogoFooter"]=(stristr($options,"addLogoFooter")) ? true : false;		//Défaut : pas de logo du footer en fin de mail
		$opt["noSendNotif"]=(stristr($options,"noSendNotif")) ? true : false;			//Défaut : affiche un message de retour pour savoir si le mail a bien été envoyé (..ou pas)
		$opt["objectEditNotif"]=(stristr($options,"objectEditNotif")) ? true : false;	//Défaut : idem mais avec un message adapté aux mails de notification

		////	Charge et crée l'instance PHPMailer
		if(!defined("phpmailerLoaded")){
			require("app/misc/PHPMailer/PHPMailerAutoload.php");
			define("phpmailerLoaded",true);
		}
		$mail=new PHPMailer();
		$mail->CharSet="UTF-8";
		$mail->Priority=1;//haute

		////	Parametrage DKIM / SMTP
		if(defined("DKIM_domain") && defined("DKIM_private") && defined("DKIM_selector"))   {$mail->DKIM_domain=DKIM_domain;   $mail->DKIM_private=DKIM_private;   $mail->DKIM_selector=DKIM_selector;}
		if(defined("SMTP_CONFIG") && is_file(SMTP_CONFIG))  {require_once SMTP_CONFIG;}//Parametrage smtp spécifique (cf "config.inc.php" & "params.php")
		if(!empty(Ctrl::$agora->smtpHost) && !empty(Ctrl::$agora->smtpPort)){
			$mail->isSMTP();
			$mail->Host=Ctrl::$agora->smtpHost;
			$mail->Port=(int)Ctrl::$agora->smtpPort;
			if(!empty(Ctrl::$agora->smtpSecure))	{$mail->SMTPSecure=Ctrl::$agora->smtpSecure;}//Sécurise via SSL/TLS
			else									{$mail->SMTPAutoTLS=false;}//Désactive le SSL/TLS par défaut
			if(!empty(Ctrl::$agora->smtpUsername) && !empty(Ctrl::$agora->smtpPass))   {$mail->Username=Ctrl::$agora->smtpUsername;  $mail->Password=Ctrl::$agora->smtpPass;  $mail->SMTPAuth=true;}//Connection authentifié
		}

		////	Expediteur
		$fromDomain=str_replace("www.","",$_SERVER["HTTP_HOST"]);
		$fromMail=(!empty(Ctrl::$agora->sendmailFrom))  ?  Ctrl::$agora->sendmailFrom  :  "noreply@".$fromDomain;
		$fromLabel=(!empty(Ctrl::$agora->name))  ?  Ctrl::$agora->name  :  $fromDomain;
		$mail->SetFrom($fromMail, $fromLabel);
		//Expediteur "user"
		if($opt["senderNoReply"]==false && isset(Ctrl::$curUser) && !empty(Ctrl::$curUser->mail)){
			$mail->SetFrom($fromMail, $fromLabel." - ".Ctrl::$curUser->getLabel());
			$mail->AddReplyTo(Ctrl::$curUser->mail, Ctrl::$curUser->getLabel());
			if($opt["receptionNotif"]==true)  {$mail->ConfirmReadingTo=Ctrl::$curUser->mail;}
		}

		////	Destinataires (format text / array d'idUser)
		//Ajoute l'user courant en "AddAddress()" si "hideRecipients" (pour eviter le Spam)
		if($opt["hideRecipients"]==true && method_exists(Ctrl::$curUser,"isUser") && !empty(Ctrl::$curUser->mail))   {$mail->AddAddress(Ctrl::$curUser->mail);}
		//Ajoute chaque destinataire
		$mailsToNotif=null;
		if(is_string($mailsTo))  {$mailsTo=explode(",",trim($mailsTo,","));}
		foreach($mailsTo as $tmpDest)
		{
			if(is_numeric($tmpDest) && method_exists(Ctrl::$curUser,"isUser"))	{$tmpDest=Ctrl::getObj("user",$tmpDest)->mail;}
			if(!empty($tmpDest)){
				$mailsToNotif.=", ".$tmpDest;
				if($opt["hideRecipients"]==true)	{$mail->AddBCC($tmpDest);}//Copie cachée (sauf wamp)
				else								{$mail->AddAddress($tmpDest);}
			}
		}

		////	Sujet & message
		$mail->Subject=htmlspecialchars($subject);
		if($opt["noFooter"]==false && !empty(Ctrl::$agora->name) && !empty(Ctrl::$curUser)){
			$fromTheSpace=ucfirst(Ctrl::$agora->name);
			if(!empty(Ctrl::$curSpace->name) && Ctrl::$agora->name!=Ctrl::$curSpace->name)	{$fromTheSpace.=" / ".Ctrl::$curSpace->name;}
			$messageSendBy=(Ctrl::$curUser->isUser())  ?  Txt::trad("MAIL_sendBy")." ".Ctrl::$curUser->getLabel().", "  :  null;
			$message.="<br><br>".$messageSendBy.Txt::trad("MAIL_fromTheSpace")." <a href=\"".Req::getSpaceUrl()."\" target='_blank'>".$fromTheSpace."</a>";
		}
		$mail->MsgHTML($message);

		////	Logo du footer en fin de mail (signature)
		$logoFooterPath=(!empty(Ctrl::$agora->logo))  ?  Ctrl::$agora->pathLogoFooter()  :  "app/img/logoLabel.png";//logo spécifique OU logo par défaut
		if($opt["addLogoFooter"]==true && is_file($logoFooterPath)){
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
				$tmpFileSize=filesize($tmpFile["path"]);
				if(is_file($tmpFile["path"]) && ($fileSizeCpt+$tmpFileSize)<File::mailMaxFilesSize){
					$fileSizeCpt+=$tmpFileSize;//Ajoute la taille du fichier au compteur
					if(!empty($tmpFile["name"]))	{$mail->AddAttachment($tmpFile["path"],$tmpFile["name"]);}	//Ajoute le fichier joint
					if(!empty($tmpFile["cid"]))		{$mail->AddEmbeddedImage($tmpFile["path"],$tmpFile["cid"]);}//Intègre l'image dans le message (si "cid"="XYZ", l'image est placée dans "<img src='cid:XYZ'>")
				}
			}
		}

		////	Envoi du mail + rapport d'envoi si demande
		$isSendMail=$mail->Send();
		if($opt["noSendNotif"]==false){
			$notifMail=($opt["objectEditNotif"]==true) ? Txt::trad("MAIL_sendNotif") : Txt::trad("MAIL_sendOk");
			if($isSendMail==true)	{Ctrl::addNotif($notifMail."<br><br>".Txt::trad("MAIL_recipients")." : ".trim($mailsToNotif,","), "success");}
			else					{Ctrl::addNotif("MAIL_notSend");}
		}
		return $isSendMail;
	}

	/*
	 * Url filtré des parametres passés en Get
	 */
	public static function getParamsUrl($paramsExclude=null)
	{
		//Init
		$getParamsUrl=array();
		$paramsExclude=(!empty($paramsExclude)) ? explode(",",$paramsExclude) : array();
		//Filtre les parametres passés en Get
		parse_str($_SERVER["QUERY_STRING"],$getParams);//$getParams est retourné par "parse_str()"
		foreach($getParams as $paramKey=>$paramVal){
			if(!in_array($paramKey,$paramsExclude))  {$getParamsUrl[$paramKey]=$paramVal;}
		}
		//Renvoie l'url à partir du tableau
		return "?".http_build_query($getParamsUrl);
	}

	/*
	 * Tri un tableau multidimentionnel
	 */
	public static function sortArray($sortedArray, $sortedField, $ascDesc="asc", $fixFirstLine=false)
	{
		// Créé un tableau temporaire avec juste la cle du tableau principal et le champ à trier
		$keyFirstResult=null;
		$tmpArray=$returnArray=array();
		foreach($sortedArray as $key=>$value){
			if($fixFirstLine==true && empty($keyFirstResult))	{$keyFirstResult=$key;}//Retient le premier resultat
			else												{$tmpArray[$key]=$value[$sortedField];}
		}
		// Tri ascendant ou descendant (avec maintient des index)
		($ascDesc=="asc")  ?  asort($tmpArray)  :  arsort($tmpArray);
		// Rajoute si besoin le premier résultat (cf. ci-dessus)
		if(isset($keyFirstResult))	{$returnArray[$keyFirstResult]=$sortedArray[$keyFirstResult];}
		// Reconstruit le tableau multidimensionnel à partir du tableau temporaire trié
		foreach($tmpArray as $key=>$value)	{$returnArray[$key]=$sortedArray[$key];}
		// Retourne le tableau trié
		return $returnArray;
	}

	/*
	 * Recherche une valeur dans un tableau multidimentionnel
	 */
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

	/*
	 * Verifie si on est sur un environnement Windows
	 */
	public static function winEnv()
	{
		if(self::$_winEnv===null)  {self::$_winEnv=(strtoupper(substr(PHP_OS,0,3))=="WIN");}
		return self::$_winEnv;
	}

	/*
	 * Verifie si on est sur un environnement Linux
	 */
	public static function linuxEnv()
	{
		if(self::$_linuxEnv===null)  {self::$_linuxEnv=preg_match("/linux/i",PHP_OS);}
		return self::$_linuxEnv;
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
	
	
	/*
	 * ColorPicker / Selecteur de couleurs.
	 * $bgTxtColor="background-color"/"color"
	 */
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

	/*
	 * Barre de pourcentage
	 */
	public static function percentBar($fillPercent, $txtBar, $txtTooltip, $orangeBarAlert=false, $barWidth=null)
	{
		//Width de "100%" par défaut && Remplissage à 100% maximum
		if(empty($barWidth))	{$barWidth="100%";}
		if($fillPercent>100)	{$fillPercent=100;}
		//Couleur de barre de remplissage
		if($orangeBarAlert==true)	{$percentBarImg="percentBarAlert";}//avancement retard ou autre (barre orange)
		elseif($fillPercent==100)	{$percentBarImg="percentBar100";}//terminé à 100% : vert clair
		else						{$percentBarImg="percentBarCurrent";}//en cours : vert pale
		//renvoie la percentbar
		return "<div class='percentBar' style='width:".$barWidth.";' title=\"".$txtTooltip."\">
					<div class='percentBarContent' style='background-image:url(app/img/".$percentBarImg.".png);background-size:".(int)$fillPercent."% 100%;'>".$txtBar."</div>
				</div>";
	}
}