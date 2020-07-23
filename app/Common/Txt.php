<?php
/**
* This file is part of the Agora-Project Software package.
*
* @copyright (c) Agora-Project Limited <https://www.agora-project.net>
* @license GNU General Public License, version 2 (GPL-2.0)
*/


/*
 * Classe des trads et formatage de texte
 */
class Txt
{
	protected static $trad=array();
	protected static $detectEncoding=null;

	/*
	 * Charge les traductions
	 */
	public static function loadTrads()
	{
		//Charge les trads si besoin (et garde en session)
		if(empty(self::$trad))
		{
			//Trad demandée (param)  /  Trad du paramétrage de l'user  /  Trad du paramétrage de l'espace  /  Trad en fonction du navigateur (ctrl=install || agora-project.net)
			if(Req::isParam("curTrad"))																					{$_SESSION["curTrad"]=Req::getParam("curTrad");}
			elseif(isset(Ctrl::$curUser) && !empty(Ctrl::$curUser->lang))												{$_SESSION["curTrad"]=Ctrl::$curUser->lang;}
			elseif(!empty(Ctrl::$agora->lang))																			{$_SESSION["curTrad"]=Ctrl::$agora->lang;}
			elseif(isset($_SERVER["HTTP_ACCEPT_LANGUAGE"]) && preg_match("/^en-US/i",$_SERVER["HTTP_ACCEPT_LANGUAGE"]))	{$_SESSION["curTrad"]="english";}
			elseif(isset($_SERVER["HTTP_ACCEPT_LANGUAGE"]) && preg_match("/^es-ES/i",$_SERVER["HTTP_ACCEPT_LANGUAGE"]))	{$_SESSION["curTrad"]="espanol";}
			else																										{$_SESSION["curTrad"]="francais";}
			//Charge les trads (classe & methode)
			require_once "app/trad/".$_SESSION["curTrad"].".php";
			Trad::loadTradsLang();
		}
	}

	/*
	 * Affiche un text traduit (exple: Txt::trad('rootFolder')")
	 */
	public static function trad($keyTrad, $addSlashes=false)
	{
		//charge les traductions?
		self::loadTrads();
		//renvoie la trad / le $keyTrad
		if(self::isTrad($keyTrad) && $addSlashes==false)	{return self::$trad[$keyTrad];}
		elseif(self::isTrad($keyTrad) && $addSlashes==true)	{return addslashes(self::$trad[$keyTrad]);}
		else												{return $keyTrad."*";}
	}

	/*
	 * Verifie si une traduction existe
	 */
	public static function isTrad($keyLang)
	{
		//charge les traductions?
		self::loadTrads();
		//renvoie le résultat
		return (isset(self::$trad[$keyLang]));
	}

	/*
	 * Texte vers tableau : @@1@@2@@3@@ => array("1","2","3")
	 */
	public static function txt2tab($text)
	{
		return (!empty($text) && !is_array($text)) ? explode("@@",trim($text,"@@")) : array();
	}
	
	/*
	 * Tableau vers texte : array("1","2","3") => @@1@@2@@3@@
	 */
	public static function tab2txt($array)
	{
		if(is_array($array)){
			$array=array_filter($array);//suppr les elements vides
			if(!empty($array))	{return "@@".implode("@@",$array)."@@";}
		}
	}

	/*
	 * Reduction d'un texte (conserve certains balises html)
	 */
	public static function reduce($text, $maxCaracNb=200, $removeLastWord=true)
	{
		$textLength=strlen(strip_tags($text));
		if($textLength>$maxCaracNb)
		{
			$textDisplayed=strip_tags($text,"<p><div><span><a><button><img><br><hr>");							//Conserve certaines balises (cf. descriptions via tinyMce affichées dans "pluginTooltip" ou "MdlObject::sendMailNotif()")
			$maxCaracNb+=round(strlen($textDisplayed)-$textLength);												//Ajoute la taille des balises html dans la compabilisation du nb de caractères
			$text=substr($textDisplayed, 0, $maxCaracNb);														//Réduit la taile du texte
			if(strrpos($text," ")>1 && $removeLastWord==true)	{$text=substr($text,0,strrpos($text," "));}		//Enlève le dernier mot qui dépasse (auquel cas)
			$text=rtrim($text,",")."...";
		}
		return $text;
	}

	/*
	 * Supprime les caracteres speciaux d'une chaine de caracteres
	 * exemple de $scope avec "L'été!":  download=>"L_été!"  mini=>"L'ete!"  normal=>"L'ete"  maxi=>"L_ete"
	 */
	public static function clean($text, $scope="normal", $replaceBy="_")
	{
		//Enleve les balide éventuelle..
		$text=strip_tags($text);
		// Remplace les caractères pour un téléchargement de fichier/dossier
		if($scope=="download")    {$text=str_replace(array('/','\\','"','\'',':','*','?','<','>','|'), $replaceBy, htmlspecialchars_decode($text));}
		// Remplace les caractères accentués et autres caractères spéciaux
		else
		{
			//Remplace les caractères accentués ou assimilés
			$text=str_replace(["á","à","â","ä"], "a", $text);
			$text=str_replace(["é","è","ê","ë"], "e", $text);
			$text=str_replace(["í","ì","î","ï"], "i", $text);
			$text=str_replace(["ó","ò","ö","ô"], "o", $text);
			$text=str_replace(["ú","ù","ü","û"], "u", $text);
			$text=str_replace("ç", "c", $text);
			$text=str_replace("ñ", "n", $text);
			//Remplace les caracteres spéciaux
			if($scope=="normal" || $scope=="max")
			{
				$carac_ok=($scope=="normal")  ?  array(" ","-",".","_","'","(",")","[","]")  :  array("-",".","_");
				for($i=0; $i<strlen($text); $i++){
					if(!preg_match("/[0-9a-z]/i",$text[$i]) && !in_array($text[$i],$carac_ok))	{$text[$i]=$replaceBy;}
				}
				$text=str_replace($replaceBy.$replaceBy, $replaceBy, $text);
			}
		}
		return trim($text);
	}

	/*
	 * Reduction et nettoyage d'un texte pour un affichage "plugin" (cf. double "quotes" and co)
	 */
	public static function cleanPlugin($text, $maxCaracNb=200, $allowable_tags="<hr><br>")
	{
		return htmlspecialchars(self::reduce(strip_tags($text,$allowable_tags),$maxCaracNb));
	}

	/*
	 * Texte en majuscule
	 */
	public static function maj($text)
	{
		return strtoupper(self::clean($text,"mini"));
	}

	/*
	 * Encode une chaine en UTF-8 ?
	 */
	public static function utf8Encode($text)
	{
		if(static::$detectEncoding===null)	{static::$detectEncoding=function_exists("mb_detect_encoding");}
		return (static::$detectEncoding==false || mb_detect_encoding($text,"UTF-8",true))  ?  $text  :  utf8_encode($text);
	}

	/*
	 * Format les tooltips : ajoute si besoin des hyperliens et fait les retours à la ligne
	 */
	public static function formatTooltip($text)
	{
		$patternHyperlink="/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/";
		return nl2br(preg_replace($patternHyperlink, "<a href='$0' target='_blank'><u>$0</u></a>", $text));
	}

	/*
	 * Formate une date puis encode si besoin en UTF-8
	 */
	public static function formatime($format, $timestamp)
	{
		return self::utf8Encode(strftime($format,$timestamp));
	}

	/*
	 * Affichage d'une date
	 * $timeBegin & $timeEnd : Timestamp unix ou format DateTime
	 * $format => normal / full / mini / date / dateFull / dateMini
	 */
	public static function displayDate($timeBegin, $format="normal", $timeEnd=null)
	{
		// Vérif de base. $dateBegin peut être vide, mais $dateEnd spécifié (task)
		if(!empty($timeBegin) || !empty($timeEnd))
		{
			//init
			$fEnd="";
			$hourSeparation=self::trad("hourSeparator");
			$imgSeparator=" <img src='app/img/arrowRight.png'> ";
			//Formate en timestamp si besoin
			if(!is_numeric($timeBegin))						{$timeBegin=strtotime($timeBegin);}
			if(!is_numeric($timeEnd) && !empty($timeEnd))	{$timeEnd=strtotime($timeEnd);}
			// Format du mois et de l'année si != de l'année courante
			$fMonthYear=($format=="full") ? "%B" : "%b";
			if((!empty($timeBegin) && date("y",$timeBegin)!=date("y")) || (!empty($timeEnd) && date("y",$timeEnd)!=date("y")))  {$fMonthYear.=" %Y";}
			//Format du jour et de l'heure
			$fDayMonthYear="%e ".$fMonthYear;
			$fHM=(Tool::winEnv()?"%H":"%k").$hourSeparation."%M";//exple "9h30" ("%k" affichera "9h" alors que "%H" affichera "09h" mais reste comptatible windows)
			$dayBeginEnd=$hourBeginEnd=false;
			if(!empty($timeEnd)){
				if(date("ymd",$timeBegin)!=date("ymd",$timeEnd))	{$dayBeginEnd=true;}//JourDebut!=jourFin
				if(date("H:i",$timeBegin)!=date("H:i",$timeEnd))	{$hourBeginEnd=true;}//heureDebut!=heureFin
			}

			//NORMAL
			if($format=="normal"){
				$fBegin=$fDayMonthYear." ".$fHM;							//8 fév. (15) 11h30
				if($dayBeginEnd==true)		{$fEnd=$imgSeparator.$fBegin;}	//8 fév. (15) 11h30 > 15 mars (15) 17h30
				elseif($hourBeginEnd==true)	{$fEnd="-".$fHM;}				//8 fév. (15) 11h30-12h30
			}
			//FULL
			if($format=="full"){
				$fDayMonthYear="%a ".$fDayMonthYear;						//lundi 8 février (2015)
				$fBegin=$fDayMonthYear." ".$fHM;							//lundi 8 février (2015) 11h30
				if($dayBeginEnd==true)		{$fEnd=$imgSeparator.$fBegin;}	//lundi 8 février (2015) 11h30 > mercredi 15 mars (2015) 17h30
				elseif($hourBeginEnd==true)	{$fEnd="-".$fHM;}				//lundi 8 février (2015) 11h30-12h30
			}
			//MINI (evenement dans agenda)
			elseif($format=="mini"){
				if($dayBeginEnd==true)		{$fBegin=$fDayMonthYear;	$fEnd=$imgSeparator.$fBegin;}	//8 fev. (15) > 15 mars
				elseif($hourBeginEnd==true)	{$fBegin=$fHM;				$fEnd="-".$fBegin;}				//11h30-12h30
				else						{$fBegin=$fHM;}												//11h30
			}
			//DATE (element affiché en mode liste)
			elseif($format=="date"){
				$fBegin=$fDayMonthYear;										//8 fév. (2015)
				if($dayBeginEnd==true)	{$fEnd=$imgSeparator.$fBegin;}		//8 fév. (2015) > 15 mars (2015)
			}
			//DATE FULL
			elseif($format=="dateFull"){
				$fBegin=$fDayMonthYear="%A ".$fDayMonthYear;				//lundi 8 fév. (2015)
				if($dayBeginEnd==true)	{$fEnd=$imgSeparator.$fBegin;}		//lundi 8 fév. (2015) > mercredi 15 mars (2015)
			}
			//DATE MINI
			elseif($format=="dateMini"){
				$fBegin=$fDayMonthYear="%d/%m/%Y";							// 8/02/2015
				if($dayBeginEnd==true)	{$fEnd=$imgSeparator.$fBegin;}		// 8/02/2015 > 15/03/2015
			}

			//Applique le formatage demandé avec la configuration locale (timezone)
			if(!empty($timeBegin) && !empty($timeEnd))	{$timeTxt=strftime($fBegin,$timeBegin).strftime($fEnd,$timeEnd);}//date début > date fin
			elseif(!empty($timeBegin))					{$timeTxt=strftime($fBegin,$timeBegin);}//date début
			elseif(!empty($timeEnd))					{$timeTxt=Txt::trad("end")." : ".trim(strftime($fEnd,$timeEnd),$imgSeparator);}//Fin : date fin

			//Formate les minutes "00" et les "Aujourd'hui"
			$timeTxt=str_replace(" 0".$hourSeparation."00", null, $timeTxt);//Efface D'ABORD les " 0h00" (les tasks peuvent avoir juste une date, sans heure précise)
			$timeTxt=str_replace($hourSeparation."00", $hourSeparation, $timeTxt);//Enleves les minutes "00" aux heures pleines (exple: "12h00" -> "12h")
			if(preg_match("/(normal|full)/i",$format) && date("Ymd")==date("Ymd",$timeBegin))  {$timeTxt=str_replace(strftime($fDayMonthYear), self::trad("today"), $timeTxt);}//Affiche "Aujourd'hui" (ne pas afficher uniquement l'heure..)

			//On renvoie le résultat (encodé en UTF-8 ?)
			return static::utf8Encode($timeTxt);
		}
	}

	/*
	 * Formatage d'une date
	 * Exple : "2050-12-31 12:50:00" => "31/12/2050"
	 */
	public static function formatDate($dateValue, $inFormat, $outFormat, $emptyHourNull=false)
	{
		$dateValue=trim($dateValue);
		$formatList=["dbDatetime"=>"Y-m-d H:i", "dbDate"=>"Y-m-d", "inputDatetime"=>"d/m/Y H:i", "inputDate"=>"d/m/Y", "inputHM"=>"H:i", "time"=>"U"];
		if(!empty($dateValue) && array_key_exists($inFormat,$formatList) && array_key_exists($outFormat,$formatList))
		{
			//Formate la date d'entrée
			if($inFormat=="inputDatetime" && strlen($dateValue)<16)		{$dateValue.=" 00:00";}//Ajoute les minutes/secontes si besoin, sinon $date retourne false..
			elseif($inFormat=="dbDatetime" && strlen($dateValue)>16)	{$dateValue=substr($dateValue,0,16);}//enlève les microsecondes si besoin, sinon $date retourne false..
			$date=DateTime::createFromFormat($formatList[$inFormat], $dateValue);
			//Formate la date de sortie
			if(is_object($date)){
				$return=$date->format($formatList[$outFormat]);
				if($outFormat=="inputHM" && $return=="00:00" && $emptyHourNull==true)	{$return=null;}
				return $return;
			}
		}
	}

	/*
	 * Inputs "hidden" de base (Ctrl, Action, etc)  &&  Bouton "submit" du Formulaire
	 */
	public static function submitButton($tradSubmit="validate", $isMainButton=true)
	{
		return "<input type='hidden' name='ctrl' value=\"".Req::$curCtrl."\">
				<input type='hidden' name='action' value=\"".Req::$curAction."\">
				<input type='hidden' name='formValidate' value='1'>
				".(Req::isParam("targetObjId")  ?  "<input type='hidden' name='targetObjId' value=\"".Req::getParam("targetObjId")."\">"  :  null)."
				<div class='".($isMainButton==true?'submitButtonMain':'submitButtonInline')."'>
					<button type='submit'>".( self::isTrad($tradSubmit) ? self::trad($tradSubmit) : $tradSubmit)."</button>
				</div>";
	}

	/*
	 * Menu de sélection de la langue
	 */
	public static function menuTrad($typeConfig, $selectedLang=null)
	{
		// Langue "francais" par défaut
		if(empty($selectedLang))	{$selectedLang="francais";}
		//Ouvre le dossier des langues & init le "Onchange"
		$onchange=($typeConfig=="install")  ?  "redir('?ctrl=".Req::$curCtrl."&action=".Req::$curAction."&curTrad='+this.value);"  :  "$('.menuTradIcon').attr('src','app/trad/'+this.value+'.png');";
		// Affichage
		$menuLangOptions=null;
		foreach(scandir("app/trad/") as $tmpFileLang){
			if(strstr($tmpFileLang,".php")){
				$tmpLang=str_replace(".php",null,$tmpFileLang);
				$tmpLabel=($typeConfig=="user" && $tmpLang==Ctrl::$agora->lang)  ?  $tmpLang." (".Txt::trad("byDefault").")"  :  $tmpLang;
				$menuLangOptions.= "<option value=\"".$tmpLang."\" ".($tmpLang==$selectedLang?"selected":null)."> ".$tmpLabel."</option>";
			}
		}
		return "<select name='lang' onchange=\"".$onchange."\">".$menuLangOptions."</select> &nbsp; <img src='app/trad/".$selectedLang.".png' class='menuTradIcon'>";
	}

	/*
	 * Créé un identifiant unique
	 */
	public static function uniqId($length=15)
	{
		//"md5" car deux "uniqid()" à intervalles proches auront le même début. "rand()" pour pas prendre en compte que le microtime du "uniqid()"
		return substr(md5(uniqid(rand())), 0, $length);
	}

	/*
	 * Vérifie la validité d'un email
	 */
	public static function isMail($email){ 
		return (!empty($email) && filter_var($email,FILTER_VALIDATE_EMAIL));
	}
}