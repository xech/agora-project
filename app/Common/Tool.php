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
 * Classe "boite à outils"
 */
class Tool
{
	/*******************************************************************************************
	 * ENVOI D'UN MAIL
	 * Note : toujours mettre en place un SPF, DKIM et REVERS DNS (évite la spambox)
	 * Note : tester l'envoi des emails via https://www.mail-tester.com/
	 *******************************************************************************************/
	public static function sendMail($mailsTo, $subject, $message, $options=null, $attachedFiles=null)
	{
		////	Vérification de base
		if(empty($mailsTo) || empty($message))	{return false;}

		////	Options par defaut à "False" !
		$opt["hideRecipients"]=(stristr($options,"hideRecipients")) ? true : false;		//Masque les destinataires du mail : ajoute tout le monde en copie caché via "AddBCC()"
		$opt["hideUserLabel"]=(stristr($options,"hideUserLabel")) ? true : false;		//Masque le label de l'user/expéditeur ("Host::notifMail()" : notif automatique)
		$opt["receptionNotif"]=(stristr($options,"receptionNotif")) ? true : false;		//Demande un accusé de réception pour l'user/expéditeur
		$opt["addReplyTo"]=(stristr($options,"addReplyTo")) ? true : false;				//Ajoute le mail de l'user/expéditeur en "replyTo" (déconseillé pas défaut : cf. Spamassassin)
		$opt["noFooter"]=(stristr($options,"noFooter")) ? true : false;					//Masque le footer du message (la signature) : label de l'expéditeur, lien vers l'espace, logo du footer de l'espace
		$opt["noNotify"]=(stristr($options,"noNotify")) ? true : false;					//Pas de notification concernant le succès ou l'échec de l'envoi du mail (cf. "notify()")
		$opt["objectNotif"]=(stristr($options,"objectNotif")) ? true : false;			//Affiche "L'email de notification a bien été envoyé"  au lieu de  "L'email a bien été envoyé" (cf notif d'edition d'un objet)

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
				if(!empty(Ctrl::$agora->smtpSecure))	{$mail->SMTPSecure=Ctrl::$agora->smtpSecure;}	//Sécurise via SSL/TLS
				else									{$mail->SMTPAutoTLS=false;}						//Désactive le SSL/TLS par défaut
				if(!empty(Ctrl::$agora->smtpUsername) && !empty(Ctrl::$agora->smtpPass))   {$mail->Username=Ctrl::$agora->smtpUsername;  $mail->Password=Ctrl::$agora->smtpPass;  $mail->SMTPAuth=true;}//Connection authentifié
			}

			////	Expediteur
			$fromDomain=str_replace("www.","",$_SERVER["SERVER_NAME"]);
			$fromMail=(!empty(Ctrl::$agora->sendmailFrom))  ?  Ctrl::$agora->sendmailFrom  :  "noreply@".$fromDomain;	//Exple: "noreply@mondomaine.net" -> email "FROM" du domaine courant ou d'un SMTP spécifique
			$fromLabel=(!empty(Ctrl::$agora->name))  ?  Ctrl::$agora->name  :  $fromDomain;								//Nom de l'espace OU Nom du domaine courant
			$mail->SetFrom($fromMail, $fromLabel);																		//"SetFrom" : Tjs utiliser un email correspondant au domaine courant ou d'un SMTP spécifique (évite la spambox)
			//Ajoute si besoin le libellé de l'user connecté
			$fromConnectedUser=(isset(Ctrl::$curUser) && method_exists(Ctrl::$curUser,"isUser") && Ctrl::$curUser->isUser());
			if($opt["hideUserLabel"]==false && $fromConnectedUser){
				$mail->SetFrom($fromMail, $fromLabel." - ".Ctrl::$curUser->getLabel());//Ajoute le nom de l'user dans le $fromLabel (exple: "Mon espace - BOBY SMITH <noreply@mondomaine.tld>")
				if(!empty(Ctrl::$curUser->mail) && $opt["addReplyTo"]==true)		{$mail->AddReplyTo(Ctrl::$curUser->mail, Ctrl::$curUser->getLabel());}	//Ajoute un email "ReplyTo" ? à éviter car un email "ReplyTo" différent du $fromMail peut arriver en spambox (cf. Spamassassin)
				if(!empty(Ctrl::$curUser->mail) && $opt["receptionNotif"]==true)	{$mail->ConfirmReadingTo=Ctrl::$curUser->mail;}							//Demande une confirmation de lecture?
			}

			////	Destinataires (format text / array d'idUser)
			$mailsToNotif=null;																															//Prépare la notification finale
			if($opt["hideRecipients"]==true && $fromConnectedUser==true && !empty(Ctrl::$curUser->mail))  {$mail->AddAddress(Ctrl::$curUser->mail);}	//Destinataires masqués: ajoute l'expéditeur en email principal (evite la spambox)
			if(is_string($mailsTo))  {$mailsTo=explode(",",trim($mailsTo,","));}																		//Prépare la liste des destinataires
			//Ajoute chaque destinataire en adresse principale ou BCC (Copie cachée)
			foreach($mailsTo as $tmpDest){
				if(is_numeric($tmpDest) && method_exists(Ctrl::$curUser,"isUser"))	{$tmpDest=Ctrl::getObj("user",$tmpDest)->mail;}
				if(!empty($tmpDest)){
					$mailsToNotif.=", ".$tmpDest;
					if($opt["hideRecipients"]==true)	{$mail->AddBCC($tmpDest);}
					else								{$mail->AddAddress($tmpDest);}
				}
			}

			////	Sujet & message
			$mail->Subject=htmlspecialchars($subject);
			if($opt["noFooter"]==false && !empty(Ctrl::$agora->name) && !empty(Ctrl::$curUser)){
				$fromTheSpace=ucfirst(Ctrl::$agora->name);																										//Nom de l'espace principal
				if(!empty(Ctrl::$curSpace->name) && Ctrl::$agora->name!=Ctrl::$curSpace->name)	{$fromTheSpace.=" / ".Ctrl::$curSpace->name;}					//Ajoute si besoin le nom du sous-espace
				$messageSendBy=(Ctrl::$curUser->isUser())  ?  Txt::trad("MAIL_sendBy")." ".Ctrl::$curUser->getLabel().", "  :  null;							//Envoyé par "boby SMITH"
				$message.="<br><br>".$messageSendBy.Txt::trad("MAIL_fromTheSpace")." <a href=\"".Req::getSpaceUrl()."\" target='_blank'>".$fromTheSpace."</a>";	//..depuis l'espace "Mon espace"
			}
			$mail->MsgHTML($message);

			////	Logo du footer en fin de mail (signe avec un logo spécifique ou le logo par défaut)
			$logoFooterPath=(!empty(Ctrl::$agora->logo))  ?  Ctrl::$agora->pathLogoFooter()  :  "app/img/logoLabel.png";
			if($opt["noFooter"]==false && is_file($logoFooterPath)){
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

			////	Envoi du mail + rapport d'envoi si demande ("notify()")
			$isSendMail=$mail->Send();
			if($opt["noNotify"]==false){
				$notifMail=($opt["objectNotif"]==true) ? Txt::trad("MAIL_sendNotif") : Txt::trad("MAIL_sendOk");
				if($isSendMail==true)	{Ctrl::addNotif($notifMail."<br><br>".Txt::trad("MAIL_recipients")." : ".trim($mailsToNotif,","), "success");}
				else					{Ctrl::addNotif("MAIL_notSend");}
			}
			return $isSendMail;
		}
		////	Sinon envoi une exception PHPMailer
		catch (Exception $e) {
			echo Txt::trad("MAIL_notSend").". PHPMailer Error : ".$mail->ErrorInfo;
		}
	}

	/*******************************************************************************************
	 * URL FILTRÉ DES PARAMETRES PASSÉS EN GET
	 *******************************************************************************************/
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

	/*******************************************************************************************
	 * TRI UN TABLEAU MULTIDIMENTIONNEL
	 *******************************************************************************************/
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
		if($orangeBarAlert==true)	{$percentBarImg="percentBarAlert";}//avancement retard ou autre (barre orange)
		elseif($fillPercent==100)	{$percentBarImg="percentBar100";}//terminé à 100% : vert clair
		else						{$percentBarImg="percentBarCurrent";}//en cours : vert pale
		//renvoie la percentbar
		return "<div class='percentBar' style='width:".$barWidth.";' title=\"".$txtTooltip."\">
					<div class='percentBarContent' style='background-image:url(app/img/".$percentBarImg.".png);background-size:".(int)$fillPercent."% 100%;'>".$txtBar."</div>
				</div>";
	}
}