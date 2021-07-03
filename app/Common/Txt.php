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
	protected static $trad=[];
	protected static $detectEncoding=null;

	/*******************************************************************************************
	 * CHARGE LES TRADUCTIONS
	 *******************************************************************************************/
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

	/*******************************************************************************************
	 * AFFICHE UN TEXT TRADUIT (exple: Txt::trad('rootFolder')")
	 *******************************************************************************************/
	public static function trad($keyTrad, $addSlashes=false)
	{
		//charge les traductions?
		self::loadTrads();
		//renvoie la trad / le $keyTrad
		if(self::isTrad($keyTrad) && $addSlashes==false)	{return self::$trad[$keyTrad];}
		elseif(self::isTrad($keyTrad) && $addSlashes==true)	{return addslashes(self::$trad[$keyTrad]);}
		else												{return $keyTrad."*";}
	}

	/*******************************************************************************************
	 * VERIFIE SI UNE TRADUCTION EXISTE
	 *******************************************************************************************/
	public static function isTrad($keyLang)
	{
		//charge les traductions?
		self::loadTrads();
		//renvoie le résultat
		return (isset(self::$trad[$keyLang]));
	}

	/*******************************************************************************************
	 * TEXTE VERS TABLEAU : @@1@@2@@3@@ => array("1","2","3")
	 *******************************************************************************************/
	public static function txt2tab($text)
	{
		return (!empty($text) && !is_array($text)) ? explode("@@",trim($text,"@@")) : array();
	}
	
	/*******************************************************************************************
	 * TABLEAU VERS TEXTE : array("1","2","3") => @@1@@2@@3@@
	 *******************************************************************************************/
	public static function tab2txt($array)
	{
		if(is_array($array)){
			$array=array_filter($array);//supprime les elements vides
			if(!empty($array))	{return "@@".implode("@@",$array)."@@";}
		}
	}

	/********************************************************************************************
	 * REDUCTION D'UN TEXTE (conserve certaines balises html)
	 ********************************************************************************************/
	public static function reduce($text, $maxCaracNb=200, $removeLastWord=true)
	{
		//Vérif si on dépasse la longeur max (texte brut sans tag ni caractère html)
		$textLength=strlen(htmlspecialchars_decode(strip_tags($text)));
		if($textLength>$maxCaracNb){
			$text=htmlspecialchars_decode(strip_tags($text,"<p><div><span><a><button><img><br><hr>"));		//Minimise les tags html de Tinymce pour les "title" et "sendMailNotif()" (idem "Req::filterParam()")
			$maxCaracNb+=round(strlen($text)-$textLength);													//Ajoute la taille des balises html dans le nb de caractères
			$text=substr($text, 0, $maxCaracNb);															//Réduit la taile du texte
			if(strrpos($text," ")>1 && $removeLastWord==true)  {$text=substr($text,0,strrpos($text," "));}	//Enlève le dernier mot qui dépasse (déconseillé si $maxCaracNb < 100 car peut réduire fortement la taille du texte)
			$text=rtrim($text,",")."...";																	//Ajoute un "..." à la fin du texte
		}
		//Renvoie le résultat (Converti les doubles quotes pour les "title", car Tinymce ne les converti pas ..contrairement aux accents "&egrave;" & co)
		return str_replace('"','&quot;',$text);
	}

	/*******************************************************************************************
	 * SUPPRIME LES CARACTERES SPECIAUX D'UNE CHAINE DE CARACTERES (dowload de fichier & co)
	 * Exple de $scope avec  "<div>L'ÉTÉ (!)</div>"  :  min -> "l'été (_)"  max -> "l_été__"
	 *******************************************************************************************/
	public static function clean($text, $scope="min", $replaceAccents=false, $replaceBy="_")
	{
		//Enleve les éventuelles balises et convertit les caractères spéciaux html
		$text=htmlspecialchars_decode(strip_tags($text));
		//Remplace si besoin les caractères accentués
		if($replaceAccents==true){
			$searchedCarac=explode(",", "å,á,à,â,ä,è,é,ê,ë,í,î,ï,ì,ò,ó,ô,ö,ø,ú,ù,û,ü,ÿ,ç,ñ,Å,Á,À,Â,Ä,È,É,Ê,Ë,Í,Î,Ï,Ì,Ò,Ó,Ô,Ö,Ø,Ú,Ù,Û,Ü,Ÿ,Ç,Ñ,æ,œ,Æ,Œ");
			$replacedCarac=explode(",", "a,a,a,a,a,e,e,e,e,i,i,i,i,o,o,o,o,o,u,u,u,u,y,c,n,A,A,A,A,A,E,E,E,E,I,I,I,I,O,O,O,O,O,U,U,U,U,Y,C,N,ae,oe,AE,OE");
			$text=str_replace($searchedCarac, $replacedCarac, $text);
		}
		//Conserve uniquement les caractères alphanumériques et certains caractères spéciaux
		$acceptedCarac=($scope=="max")  ?  ['-','.','_']  :  ['-','.','_',' ','\'','(',')','[',']','@'];
		foreach(preg_split('//u',$text) as $tmpCarac){																								//pas de "str_split()" qui ne reconnait pas les caractères accentués..
			if(!preg_match("/[\p{Nd}\p{L}]/u",$tmpCarac) && !in_array($tmpCarac,$acceptedCarac))  {$text=str_replace($tmpCarac,$replaceBy,$text);}	//valeurs décimales via "\p{Nd}" + lettres via "\p{L}" (même accentuées)
		}
		//Minimise le nb de $replaceBy et renvoie le résultat
		$text=str_replace($replaceBy.$replaceBy, $replaceBy, $text);
		return trim($text);
	}

	/*******************************************************************************************
	 * ENCODE SI BESOIN UNE CHAINE EN UTF-8
	 *******************************************************************************************/
	public static function utf8Encode($text)
	{
		if(static::$detectEncoding===null)	{static::$detectEncoding=function_exists("mb_detect_encoding");}
		return (static::$detectEncoding==false || mb_detect_encoding($text,"UTF-8",true))  ?  $text  :  utf8_encode($text);
	}

	/*******************************************************************************************
	 * FORMAT LES TOOLTIPS : AJOUTE SI BESOIN DES HYPERLIENS ET FAIT LES RETOURS À LA LIGNE
	 *******************************************************************************************/
	public static function formatTooltip($text)
	{
		$patternHyperlink="/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/";
		return nl2br(preg_replace($patternHyperlink, "<a href='$0' target='_blank'><u>$0</u></a>", $text));
	}

	/*******************************************************************************************
	 * FORMATE UNE DATE PUIS ENCODE SI BESOIN EN UTF-8
	 *******************************************************************************************/
	public static function formatime($format, $timestamp)
	{
		return self::utf8Encode(strftime($format,$timestamp));
	}

	/*****************************************************************************************************
	 * AFFICHAGE D'UNE DATE
	 * $timeBegin & $timeEnd : Timestamp unix ou format DateTime
	 * $format => normal / full / mini / dateFull / dateMini
	 * Note : les 'task' peuvent avoir un $dateBegin à null et un $dateEnd non-null (cf. "dateBeginEnd()")
	 *****************************************************************************************************/
	public static function dateLabel($timeBegin, $format="normal", $timeEnd=null)
	{
		// Vérif de base
		if(!empty($timeBegin) || !empty($timeEnd))
		{
			//Formate en timestamp si besoin ('dateTime' de bdd en entrée?)
			if(!is_numeric($timeBegin))						{$timeBegin=strtotime($timeBegin);}
			if(!empty($timeEnd) && !is_numeric($timeEnd))	{$timeEnd=strtotime($timeEnd);}
			//Controle si les jours de debut/fin sont différents et si les heures de debut/fin sont différentes
			$diffDays=$diffHours=false;
			if(!empty($timeBegin) && !empty($timeEnd)){
				if(date("ymd",$timeBegin)!=date("ymd",$timeEnd))  {$diffDays=true;}
				if(date("H:i",$timeBegin)!=date("H:i",$timeEnd))  {$diffHours=true;}
			}
			//Prépare le formatage via "strftime()"
			$_begin=$_end=null;																							//Init le formatage complet du début/fin
			$_HM="%k:%M";																								//Format de l'heure (ex: "9:30")
			$_DMY=($format=="full" || $format=="dateFull")  ?  "%a %e"  :  "%e";										//Format du jour 'full' ou 'normal' (ex: 'lun. 8' ou '8')
			$_DMY.=" %b";																								//Format du mois abrégé (ex: 'fev.')
			if(date("y",$timeBegin)!=date("y") || (!empty($timeEnd) && date("y",$timeEnd)!=date("y")))  {$_DMY.=" %Y";}	//Format de l'année si différente de l'année en cours (ex: '2050')
			$separator=" <img src='app/img/arrowRight.png'> ";															//Image de séparation de début/fin

			//NORMAL OU FULL
			if($format=="normal" || $format=="full"){
				$_begin=$_DMY." ".$_HM;													//[lun.] 8 fév. 2050 11:30
				if($diffDays==true)			{$_end=$separator.$_begin;}					//[lun.] 8 fév. 2050 11:30 > [mer.] 15 mars 2050 17:30
				elseif($diffHours==true)	{$_end="-".$_HM;}							//[lun.] 8 fév. 2050 11:30-12:30
			}
			//MINI
			elseif($format=="mini"){
				if($diffDays==true)			{$_begin=$_DMY;	$_end=$separator.$_begin;}	//8 fev. 2050 > 15 mars 2050
				elseif($diffHours==true)	{$_begin=$_HM;	$_end="-".$_begin;}			//11:30-12:30
				else						{$_begin=$_HM;}								//11:30
			}
			//DATE FULL
			elseif($format=="dateFull"){
				$_begin=$_DMY;															//lun. 8 fév. 2050
				if($diffDays==true)	{$_end=$separator.$_begin;}							//lun. 8 fév. 2050 > mercredi 15 mars 2050
			}
			//DATE MINI (..OU SI $FORMAT N'EXISTE PAS)
			else{
				$_begin=$_DMY="%d/%m/%Y";												//8/02/2015
				if($diffDays==true)	{$_end=$separator.$_begin;}							//8/02/2015 > 15/03/2015
			}

			//Applique le formatage demandé avec la configuration locale (timezone)
			if(!empty($timeBegin) && !empty($timeEnd))	{$dateLabel=strftime($_begin,$timeBegin).strftime($_end,$timeEnd);}				//Date de début + fin
			elseif(!empty($timeBegin))					{$dateLabel=strftime($_begin,$timeBegin);}										//Date de début
			elseif(!empty($timeEnd))					{$dateLabel=Txt::trad("end")." : ".trim(strftime($_end,$timeEnd),$separator);}	//Date de fin

			//Formate les minutes "00" et affiche si besoin "Aujourd'hui"
			if($diffHours==false && $diffHours==false)  {$dateLabel=str_replace(" 0:00", null, $dateLabel);}//Efface les "0:00" (cf. tasks qui peuvent ne pas avoir d'heure)
			if($format=="mini")  {$dateLabel=str_replace(":00","h",$dateLabel);}							//Enleve les minutes ":00" aux heures pleines (ex pour les événements : "12:00" -> "12h")
			elseif(($format=="normal" || $format=="full") && date("Ymd")==date("Ymd",$timeBegin))  {$dateLabel=str_replace(strftime($_DMY),self::trad("today"),$dateLabel);}	//Affiche "Aujourd'hui" au lieu du $_DMY

			//Renvoie le résultat (encodé si besoin en UTF-8)
			return static::utf8Encode($dateLabel);
		}
	}

	/*******************************************************************************************
	 * FORMATAGE D'UNE DATE  (Exple : "2050-12-31 12:50:00" => "31/12/2050")
	 *******************************************************************************************/
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

	/*******************************************************************************************
	 * INPUTS "HIDDEN" DE BASE (Ctrl, Action, etc)  &&  BOUTON "SUBMIT" DU FORMULAIRE
	 *******************************************************************************************/
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

	/*******************************************************************************************
	 * MENU DE SÉLECTION DE LA LANGUE
	 *******************************************************************************************/
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

	/*******************************************************************************************
	 * CRÉÉ UN IDENTIFIANT UNIQUE
	 *******************************************************************************************/
	public static function uniqId($length=15)
	{
		//"md5" car deux "uniqid()" à intervalles proches auront le même début. "rand()" pour pas prendre en compte que le microtime du "uniqid()"
		return substr(md5(uniqid(rand())), 0, $length);
	}

	/*******************************************************************************************
	 * VÉRIFIE LA VALIDITÉ D'UN EMAIL
	 *******************************************************************************************/
	public static function isMail($email){ 
		return (!empty($email) && filter_var($email,FILTER_VALIDATE_EMAIL));
	}
}