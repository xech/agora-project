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
	protected static $IntlDateFormatter=null;

	/*******************************************************************************************
	 * CHARGE LES TRADUCTIONS
	 *******************************************************************************************/
	public static function loadTrads()
	{
		//Charge les trads si besoin (et garde en session)
		if(empty(self::$trad))
		{
			//Sélectionne la traduction
			if(Req::isParam("curTrad"))																						{$_SESSION["curTrad"]=Req::param("curTrad");}	//Trad demandée
			elseif(isset(Ctrl::$curUser) && !empty(Ctrl::$curUser->lang))													{$_SESSION["curTrad"]=Ctrl::$curUser->lang;}	//Trad de l'user
			elseif(!empty(Ctrl::$agora->lang))																				{$_SESSION["curTrad"]=Ctrl::$agora->lang;}		//Trad de l'espace
			elseif(empty($_SESSION["curTrad"])){																															//Trad par défaut en fonction du browser
				if(isset($_SERVER["HTTP_ACCEPT_LANGUAGE"]) && preg_match("/^en-US/i",$_SERVER["HTTP_ACCEPT_LANGUAGE"]))		{$_SESSION["curTrad"]="english";}
				elseif(isset($_SERVER["HTTP_ACCEPT_LANGUAGE"]) && preg_match("/^es-ES/i",$_SERVER["HTTP_ACCEPT_LANGUAGE"]))	{$_SESSION["curTrad"]="espanol";}
				elseif(empty($_SESSION["curTrad"]))																			{$_SESSION["curTrad"]="francais";}
			}
			//Charge les trads (classe & methode)
			require_once "app/trad/".$_SESSION["curTrad"].".php";
			Trad::loadTradsLang();
		}
	}

	/*******************************************************************************************
	 * AFFICHE UN TEXT TRADUIT (ex: Txt::trad('rootFolder')")
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

	/*******************************************************************************************
	 * REDUCTION DE TEXTE POUR LES TOOLTIPS, LOGS, ETC : ENLEVE LES TAGS HTML
	 *******************************************************************************************/
	public static function reduce($text, $maxCaracNb=200)
	{
		$text=html_entity_decode(strip_tags($text));							//Enlève les tags html (pour pas briser l'affichage d'un tag html..) && Converti les caractères html accentués (&egrave; &eacute; etc)
		$text=str_replace('&nbsp;', '', $text);									//Enlève les &nbsp; (pas pris en charge par "html_entity_decode()"..)
		if(strlen($text)>$maxCaracNb){											//Vérif la taille du texte brut
			$text=substr($text, 0, $maxCaracNb);								//Réduit le texte en fonction de $maxCaracNb
			if($maxCaracNb>100)  {$text=substr($text,0,strrpos($text," "));}	//Enlève le dernier mot si $maxCaracNb>100 (sinon on réduit trop le texte)
			$text=rtrim($text,",")."...";										//Ajoute "..." en fin de texte (enlève éventuellement la dernière virgule)
		}
		//Renvoi le résultat
		return self::tooltip($text);
	}

	/********************************************************************************************
	 * PREPARE L'AFFICHAGE D'UN TEXTE COMPLEXE DANS UN TOOLTIP/TITLE (DESCRIPTION & CO)
	 ********************************************************************************************/
	public static function tooltip($text)
	{
		if(!empty($text)){
			$text=nl2br(str_replace('"','&quot;',$text));	//Remplace les quotes et retours à la ligne puis renvoie le résultat !
			$text=strip_tags($text,"<span><img><br><hr>");	//Enlève les principale balises
			if(stristr($text,"http"))  {$text=preg_replace("/(http[s]{0,1}\:\/\/\S{4,})\s{0,}/ims", "<a href='$0' target='_blank'><u>$0</u></a>", $text);}	//Place les urls dans une balise <a>
			return $text;
		}
	}

	/********************************************************************************************
	 * SUPPRIME LES CARACTERES SPECIAUX
	 * $scope="min" pour les noms de fichier ou la recherche :	"L'ÉTÉ (!)"  ->  "l'été (_)"
	 * $scope="max" pour les identifiants ou les noms en bdd :	"L'ÉTÉ (!)"  ->  "l_ete__"
	 ********************************************************************************************/
	public static function clean($text, $scope="min", $replaceBy="_")
	{
		//Enleve les éventuels balises et caractères html de l'éditeur (&quot; &eacute; &amp; etc)
		$text=html_entity_decode(strip_tags($text));
		//Remplace les caractères accentués
		if($scope=="max"){
			$searchedCarac=explode(",", "å,á,à,â,ä,è,é,ê,ë,í,î,ï,ì,ò,ó,ô,ö,ø,ú,ù,û,ü,ÿ,ç,ñ,Å,Á,À,Â,Ä,È,É,Ê,Ë,Í,Î,Ï,Ì,Ò,Ó,Ô,Ö,Ø,Ú,Ù,Û,Ü,Ÿ,Ç,Ñ,æ,œ,Æ,Œ");
			$replacedCarac=explode(",", "a,a,a,a,a,e,e,e,e,i,i,i,i,o,o,o,o,o,u,u,u,u,y,c,n,A,A,A,A,A,E,E,E,E,I,I,I,I,O,O,O,O,O,U,U,U,U,Y,C,N,ae,oe,AE,OE");
			$text=str_replace($searchedCarac, $replacedCarac, $text);
		}
		//Conserve uniquement les caractères alphanumériques et certains caractères spéciaux
		$acceptedCarac=($scope=="max")  ?  ['.','-','_']  :  ['.','-','_',',',':',' ','\'','(',')','[',']','@'];
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

	/*****************************************************************************************************************
	 * INSTANCIE "IntlDateFormatter" POUR LA MANIPULATION DES DATES, AVEC LA "LANG" ET "TIMEZONE" LOCALE
	 *****************************************************************************************************************/
	public static function IntlDateFormatterObj()
	{
		if(static::$IntlDateFormatter===null){
			if(extension_loaded("intl"))	{static::$IntlDateFormatter=new IntlDateFormatter(Txt::trad("DATELANG"), IntlDateFormatter::SHORT, IntlDateFormatter::SHORT);}
			else 							{static::$IntlDateFormatter=false;}//L'extention PHP "intl" n'est pas instanciée (cf. perso.free & Co)
		}
		return static::$IntlDateFormatter;
	}

	/*******************************************************************************************
	 * FORMATE UNE DATE PUIS ENCODE SI BESOIN EN UTF-8
	 *******************************************************************************************/
	public static function formatime($pattern, $timestamp)
	{
		//Récupère l'objet "IntlDateFormatter" && Vérif si l'objet est instancié
		$dateFormat=self::IntlDateFormatterObj();
		if(is_object($dateFormat)){
			$dateFormat->setPattern($pattern);				//Init le format/pattern de sortie
			$dateLabel=$dateFormat->format($timestamp);		//Formate la date
			return static::utf8Encode($dateLabel);			//Renvoie le résultat en utf-8
		}
		//Sinon renvoie au format "date()". Remplace le pattern utilisé par "IntlDateFormatter()" (https://www.php.net/manual/fr/datetime.format.php)
		else{																							
			$pattern=str_replace(["yyyy","MMMM","MMM","cccc","ccc"], ["Y","F","M","l","D"], $pattern);
			return date($pattern,$timestamp);
		}
	}

	/***************************************************************************************************************************************************************************
	 * AFFICHAGE D'UNE DATE ET HEURE
	 * $timeBegin & $timeEnd =>  Timestamp unix  ||  format DateTime en Bdd
	 * $format 				 =>  "normal" -> "lun. 8 mars 2050 9:05"  ||  "mini" -> "8 mar. 2050" ou "9:05"  ||  "dateFull" -> "lundi 8 mars 2050"  ||  "date" -> "8/03/2050"
	 * Note : les "task" peuvent avoir un $dateBegin null et un $dateEnd non null (cf. "MdlTask::dateBeginEnd()")
	 ***************************************************************************************************************************************************************************/
	public static function dateLabel($timeBegin=null, $format="normal", $timeEnd=null)
	{
		//Controles de base
		if((!empty($timeBegin) || !empty($timeEnd)))
		{
			//Convertit si besoin en timestamp
			if(!empty($timeBegin) && !is_numeric($timeBegin))	{$timeBegin=strtotime($timeBegin);}
			if(!empty($timeEnd) && !is_numeric($timeEnd))		{$timeEnd=strtotime($timeEnd);}

			//Récupère l'objet "IntlDateFormatter" && Vérif si l'objet est instancié
			$dateFormat=self::IntlDateFormatterObj();
			if(is_object($dateFormat))
			{
				//Jours ou heures de debut/fin différents
				$diffDays=$diffHours=false;
				if(!empty($timeBegin) && !empty($timeEnd)){
					if(date("ymd",$timeBegin)!=date("ymd",$timeEnd))	{$diffDays=true;}
					if(date("H:i",$timeBegin)!=date("H:i",$timeEnd))	{$diffHours=true;}
				}

				//Formatage du jour/mois => patterns sur https://unicode-org.github.io/icu/userguide/format_parse/datetime/
				$dateLabel=$pattern=null;																			//Init le label et le pattern
				if($format=="normal" && date("Ymd")==date("Ymd",$timeBegin))	{$dateLabel=self::trad("today");}	//Affiche "Aujourd'hui" (pas dans le $pattern !)
				elseif($format=="normal" || $format=="dateFull")				{$pattern="eee d MMMM";}			//jour réduit, jour du mois et mois	-> Ex: "lun. 8 mars"
				elseif($format=="mini" && $diffDays==true)						{$pattern="d MMM";}					//jour du mois et mois réduit		-> Ex: "8 mar."
				//Formatage année (si différente de l'année courante)
				if($format!="mini" &&  ((!empty($timeBegin) && date("y",$timeBegin)!=date("y")) || (!empty($timeEnd) && date("y",$timeEnd)!=date("y"))))  {$pattern.=" Y";}
				//Formatage heure:minute || date
				if(preg_match("/date/i",$format)==false)	{$pattern.=" H:mm";}	//Heure sur 24h	(pas "date"/"dateFull)	-> Ex: "9:05" ou "23:23"
				elseif($format=="date")						{$pattern="dd/MM/Y";}	//Date au format basique				-> Ex: "08/03/2050"

				//Applique le formatage via la class "IntlDateFormatter()" avec la "lang" et "timezone" locale
				$dateFormat->setPattern($pattern);
				if(!empty($timeBegin))	{$dateLabel.=$dateFormat->format($timeBegin);}																//Label de début
				if(!empty($timeEnd)){																												//Label de fin :
					if($diffDays==false && $diffHours==true)	{$dateFormat->setPattern("H:mm");  $dateLabel.="-".$dateFormat->format($timeEnd);}	//- Même jour mais diff. heures : ajoute l'heure de fin	-> Ex: "11:30-12:30"
					elseif($diffDays==true)						{$dateLabel.=" <img src='app/img/arrowRight.png'> ".$dateFormat->format($timeEnd);}	//- Diff. jours : ajoute la date de fin 				-> $pattern idem
					else										{$dateLabel.=Txt::trad("end")." : ".$dateFormat->format($timeEnd);}					//- Date de fin uniquement, pas de début 				-> $pattern idem
				}

				//Simplifie les heures pleines -> Ex: "12:00"->"12h"
				if($format=="mini")  {$dateLabel=str_replace(":00", "h", $dateLabel);}
				//Renvoie le résultat en utf-8
				return static::utf8Encode($dateLabel);
			}
			//Sinon renvoie au format "date()"
			else{
				return (preg_match("/date/i",$format))  ?  date("d/m/y",$timeBegin)  :  date("d/m/y H:i",$timeBegin);
			}
		}
	}

	/*******************************************************************************************
	 * FORME UN DATETIME  (ex: "2050-12-31 12:50:00" => "31/12/2050")
	 *******************************************************************************************/
	public static function formatDate($dateValue, $inFormat, $outFormat, $emptyHourNull=false)
	{
		$dateValue=trim((string)$dateValue);
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
	 * INPUTS HIDDEN (Ctrl, Action, TypeId, etc)  &&  BOUTON SUBMIT DU FORMULAIRE
	 *******************************************************************************************/
	public static function submitButton($tradSubmit="validate", $isMainButton=true)
	{
		return '<input type="hidden" name="ctrl" value="'.Req::$curCtrl.'">
				<input type="hidden" name="action" value="'.Req::$curAction.'">
				<input type="hidden" name="formValidate" value="1">
				'.(Req::isParam('typeId')?'<input type="hidden" name="typeId" value="'.Req::param("typeId").'">':null).'
				<div class="'.($isMainButton==true?'submitButtonMain':'submitButtonInline').'">
					<button type="submit">'.(self::isTrad($tradSubmit)?self::trad($tradSubmit):$tradSubmit).' <img src="app/img/loadingSmall.png" class="submitButtonLoading"></button>
				</div>';
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
				$tmpLang=str_replace(".php","",$tmpFileLang);
				$tmpLabel=($typeConfig=="user" && $tmpLang==Ctrl::$agora->lang)  ?  $tmpLang." (".Txt::trad("byDefault").")"  :  $tmpLang;
				$menuLangOptions.= "<option value=\"".$tmpLang."\" ".($tmpLang==$selectedLang?"selected":null)."> ".$tmpLabel."</option>";
			}
		}
		return "<select name='lang' onchange=\"".$onchange."\">".$menuLangOptions."</select> &nbsp; <img src='app/trad/".$selectedLang.".png' class='menuTradIcon'>";
	}

	/*******************************************************************************************
	 * CRÉÉ UN IDENTIFIANT UNIQUE D'UNE CERTAINE LONGEUR
	 *******************************************************************************************/
	public static function uniqId($length=15)
	{
		//"uniqid()" se base sur le microtime du systeme : on ajoute donc un prefixe via "rand()" et mouline le tout en "md5()"
		return substr(md5(uniqid(rand())), 0, $length);
	}

	/*******************************************************************************************
	 * VÉRIFIE LA VALIDITÉ D'UN EMAIL
	 *******************************************************************************************/
	public static function isMail($email){ 
		return (!empty($email) && filter_var($email,FILTER_VALIDATE_EMAIL));
	}
}