<?php
/**
* This file is part of the Agora-Project Software package
*
* @copyleft Agora-Project <https://www.agora-project.net>
* @license GNU General Public License (GPL-2.0)
*/


/*
 * Classe des trads et formatage de texte
 */
class Txt
{
	protected static $trad=[];
	protected static $detectEncoding=null;
	protected static $IntlDateFormatter=null;
	public static $tradList=["francais","english","espanol","italiano","deutsch","portugues"];

	/********************************************************************************************************
	 * CHARGE LES TRADUCTIONS
	 ********************************************************************************************************/
	public static function loadTrads()
	{
		//Charge les trads si besoin (et garde en session)
		if(empty(self::$trad))
		{
			//Sélectionne la traduction de l'appli
			if(Req::isParam("curTrad") && preg_match("/^[A-Z]+$/i",Req::param("curTrad")))	{$_SESSION["curTrad"]=Req::param("curTrad");}	//Trad demandée
			elseif(isset(Ctrl::$curUser) && !empty(Ctrl::$curUser->lang))					{$_SESSION["curTrad"]=Ctrl::$curUser->lang;}	//Trad de la config de l'user
			elseif(!empty(Ctrl::$agora->lang))												{$_SESSION["curTrad"]=Ctrl::$agora->lang;}		//Trad de la config générale
			elseif(empty($_SESSION["curTrad"])){																							//Trad du browser
				$browserTrad=(!empty($_SERVER["HTTP_ACCEPT_LANGUAGE"]))  ?  $_SERVER["HTTP_ACCEPT_LANGUAGE"]  :  null;
				if(preg_match("/^en/i",$browserTrad))		{$_SESSION["curTrad"]="english";}
				elseif(preg_match("/^es/i",$browserTrad))	{$_SESSION["curTrad"]="espanol";}
				elseif(preg_match("/^pt/i",$browserTrad))	{$_SESSION["curTrad"]="portugues";}
				elseif(preg_match("/^it/i",$browserTrad))	{$_SESSION["curTrad"]="italiano";}
				elseif(preg_match("/^de/i",$browserTrad))	{$_SESSION["curTrad"]="deutsch";}
				else										{$_SESSION["curTrad"]="francais";}
			}
			//Charge les trads (classe & methode)
			if(in_array($_SESSION["curTrad"],self::$tradList))  {require_once "app/trad/".$_SESSION["curTrad"].".php";}
			Trad::loadTrads();
		}
	}

	/********************************************************************************************************
	 * AFFICHE UN TEXT TRADUIT
	 ********************************************************************************************************/
	public static function trad($keyTrad, $addSlashes=false)
	{
		//charge les traductions?
		self::loadTrads();
		//renvoie la trad / le $keyTrad
		if(self::isTrad($keyTrad) && $addSlashes==false)	{return self::$trad[$keyTrad];}
		elseif(self::isTrad($keyTrad) && $addSlashes==true)	{return addslashes(self::$trad[$keyTrad]);}
		else												{return $keyTrad;}
	}

	/********************************************************************************************************
	 * VERIFIE SI UNE TRADUCTION EXISTE
	 ********************************************************************************************************/
	public static function isTrad($keyLang)
	{
		//charge les traductions?
		self::loadTrads();
		//retourne le résultat
		return (isset(self::$trad[$keyLang]));
	}

	/********************************************************************************************************
	 * TEXTE VERS TABLEAU : @@1@@2@@3@@ => array("1","2","3")
	 ********************************************************************************************************/
	public static function txt2tab($text)
	{
		return (!empty($text) && !is_array($text))  ?  explode("@@",trim($text,"@@"))  :  array();
	}
	
	/********************************************************************************************************
	 * TABLEAU VERS TEXTE : array("1","2","3") => @@1@@2@@3@@
	 ********************************************************************************************************/
	public static function tab2txt($array)
	{
		if(is_array($array)){
			$array=array_filter($array);//supprime les elements vides
			if(!empty($array))	{return "@@".implode("@@",$array)."@@";}
		}
	}

	/********************************************************************************************************
	 * REDUCTION DE TEXTE POUR LES TOOLTIPS, LOGS, ETC : ENLEVE LES TAGS HTML
	 ********************************************************************************************************/
	public static function reduce($text, $maxCaracNb=200)
	{
		if(!empty($text)){
			$text=strip_tags($text);												//Enlève les tags html
			$text=html_entity_decode($text);										//Converti les caractères html (ex: '&egrave;' => 'é')
			$text=str_replace('&nbsp;', ' ', $text);								//Supprime les "&nbsp;"
			$text=preg_replace('!\s+!', ' ', $text);								//Supprime les espaces en trop
			if(strlen($text) > $maxCaracNb){										//Vérifie que le texte ne dépasse pas $maxCaracNb
				$text=substr($text, 0, $maxCaracNb);								//Réduit le texte
				if($maxCaracNb>100)  {$text=substr($text,0,strrpos($text," "));}	//Enlève le dernier mot si $maxCaracNb>100 (sinon on réduit trop le texte)
				$text=rtrim($text,",")."...";										//Ajoute "..." en fin de texte (enlève si besoin la dernière virgule)
			}
			$text=htmlentities($text);												//Re-converti les caractères html (ex: 'é' => '&egrave;')
			return $text;															//Retourne le résultat
		}
	}

	/********************************************************************************************
	 * AFFICHE UN TITLE / TOOLTIP DANS UNE BALISE
	 ********************************************************************************************/
	public static function tooltip($text, $titleAttr=true)
	{
		if(!empty($text)){
			if(self::isTrad($text))  {$text=self::$trad[$text];}			//Récupère si besoin une traduction
			$text=nl2br($text);												//Remplace \n par <br>
			$text=strip_tags($text,'<br><hr><img><span><i>');				//Enlève les balises (sauf <br><hr><img><span><i>)
			$text=str_replace('"','&quot;',$text);							//Remplace les doubles quotes
			if(stristr($text,'http'))  {$text=preg_replace("/(http[s]{0,1}\:\/\/\S{4,})\s{0,}/ims", "<a href='$0' target='_blank'><u>$0</u></a>", $text);}//Créé un hyperlien (tester dans le modLink)
			return ($titleAttr==true)  ?  'title="'.$text.'"'  :  $text;	//Retourne le résultat : avec ou sans `title=`
		}
	}

	/*********************************************************************************************************************
	 * SUPPRIME LES CARACTERES SPECIAUX ET ACCENTUES
	 * $scope="min" 	-> parametres de fichier Ical, etc :			"l'été &amp; (!?)"  ->  "l'été & (!?)"
	 * $scope="normal"	-> noms de fichier, recherche d'objets, etc :	"l'été &amp; (!?)"  ->  "l'été _ (_)"
	 * $scope="max"		-> identifiants, noms en bdd, etc :				"l'été &amp; (!?)"  ->  "l_ete_"
	 *********************************************************************************************************************/
	public static function clean($text, $scope="normal", $replaceBy="_")
	{
		//Editeur TinyMce && injection XSS : enleve les balises html via "strip_tags()" && décode les caractères html (&quot; &amp; etc) via "html_entity_decode()"
		$text=html_entity_decode(strip_tags($text));
		//Remplace les caractères accentués
		if($scope=="max"){
			$accentedChars=['Š'=>'S','š'=>'s','Ž'=>'Z','ž'=>'z','À'=>'A','Á'=>'A','Â'=>'A','Ã'=>'A','Ä'=>'A','Å'=>'A','Æ'=>'A','Ç'=>'C','È'=>'E','É'=>'E','Ê'=>'E','Ë'=>'E','Ì'=>'I','Í'=>'I','Î'=>'I','Ï'=>'I','Ñ'=>'N','Ò'=>'O','Ó'=>'O','Ô'=>'O','Õ'=>'O','Ö'=>'O','Ø'=>'O','Ù'=>'U','Ú'=>'U','Û'=>'U','Ü'=>'U','Ý'=>'Y','Þ'=>'B','ß'=>'Ss','à'=>'a','á'=>'a','â'=>'a','ã'=>'a','ä'=>'a','å'=>'a','æ'=>'a','ç'=>'c','è'=>'e','é'=>'e','ê'=>'e','ë'=>'e','ì'=>'i','í'=>'i','î'=>'i','ï'=>'i','ð'=>'o','ñ'=>'n','ò'=>'o','ó'=>'o','ô'=>'o','õ'=>'o','ö'=>'o','ø'=>'o','ù'=>'u','ú'=>'u','û'=>'u','ý'=>'y','þ'=>'b','ÿ'=>'y'];
			$text=strtr($text, $accentedChars);
		}
		//Conserve uniquement les caractères alphanumériques et certains caractères spéciaux
		$acceptedChars=['.','-','_'];																												//Caractères spéciaux conservés : scope "max"
		if($scope!="max")	{$acceptedChars=array_merge($acceptedChars, ["'",',',' ','(',')','[',']']);}											//Idem : scope "normal" et "min"
		if($scope=="min")	{$acceptedChars=array_merge($acceptedChars, ['"','/','\\','*',':','<','>','@','&','?','!','#']);  $replaceBy=" ";}		//Idem : scope "min"  +  change le $replaceBy par des espaces
		foreach(preg_split('//u',$text) as $tmpChars){																								//pas de "str_split()" car ne reconnait pas les caractères accentués
			if(!preg_match("/[\p{Nd}\p{L}]/u",$tmpChars) && !in_array($tmpChars,$acceptedChars))  {$text=str_replace($tmpChars,$replaceBy,$text);}	//valeurs décimales via "\p{Nd}" + lettres via "\p{L}" (même accentuées)
		}
		//Minimise le nb de $replaceBy et renvoie le résultat
		$text=str_replace($replaceBy.$replaceBy, $replaceBy, $text);
		return trim($text);
	}

	/********************************************************************************************************
	 * ENCODE SI BESOIN UNE CHAINE EN UTF-8
	 ********************************************************************************************************/
	public static function utf8Encode($text)
	{
		if(static::$detectEncoding===null)	{static::$detectEncoding=function_exists("mb_detect_encoding");}
		return (static::$detectEncoding==false || mb_detect_encoding($text,"UTF-8",true))  ?  $text  :  utf8_encode($text);
	}

	/********************************************************************************************************
	 * BOUTON SUBMIT DU FORMULAIRE  &&  INPUTS HIDDEN (Ctrl, Action, TypeId, etc)
	 ********************************************************************************************************/
	public static function submitButton($keyTrad="record", $isMainButton=true)
	{
		return '<div class="'.($isMainButton==true?'submitButtonMain':'submitButtonInline').'">
					<button type="submit">'.self::trad($keyTrad).' <img src="app/img/loading.png" class="submitLoading"></button>
				</div>
				<input type="hidden" name="ctrl" value="'.Req::$curCtrl.'">
				<input type="hidden" name="action" value="'.Req::$curAction.'">
				<input type="hidden" name="formValidate" value="1">'.
				(Req::isParam("typeId") ? '<input type="hidden" name="typeId" value="'.Req::param("typeId").'">' : null);
	}

	/********************************************************************************************************
	 * CRÉÉ UN PASSWORD PAR DEFAUT
	 ********************************************************************************************************/
	public static function defaultPassword()
	{
		return substr(uniqid(),0,8);
	}

	/********************************************************************************************************
	 * VÉRIFIE LA VALIDITÉ D'UN EMAIL
	 ********************************************************************************************************/
	public static function isMail($email){ 
		return (!empty($email) && filter_var($email,FILTER_VALIDATE_EMAIL));
	}

	/***************************************************************************************************************************/
	/*******************************************	FORMATAGE DES DATES		****************************************************/
	/***************************************************************************************************************************/
	
	/********************************************************************************************************
	 * INIT "IntlDateFormatter" POUR FORMATER UN TIMESTAMP EN FONCTION DE LA LANG ET TIMEZONE
	 ********************************************************************************************************/
	public static function dateFormatter()
	{
		if(static::$IntlDateFormatter===null){
			if(class_exists('IntlDateFormatter'))	{static::$IntlDateFormatter=new IntlDateFormatter(Txt::trad("DATELANG"), IntlDateFormatter::SHORT, IntlDateFormatter::SHORT);}
			else 									{static::$IntlDateFormatter=false;}
		}
		return static::$IntlDateFormatter;
	}

	/********************************************************************************************************
	 * FORMATAGE D'UNE DATE/HEURE EN FONCTION D'UN TIMESTAMP
	 * $format	=>	"default"	->	"8 fevrier"
	 * 			=>	"dateFull"	->	"lun. 8 fevrier"
	 * 			=>	"labelFull"	->	"lun. 8 fevrier 9:05"
	 * 			=>	"mini"		->	"9:05" ou "8 fev. 9:05" (si $diffDays)
	 * 			=>	"dateBasic"	->	"08/02/2050"
	 * Note : les objets "task" peuvent avoir une $dateEnd sans $timeBegin
	 ********************************************************************************************************/
	public static function dateLabel($timeBegin=null, $format="default", $timeEnd=null)
	{
		//Controles de base
		if((!empty($timeBegin) || !empty($timeEnd)))
		{
			//Convertit si besoin un DateTime (Bdd) en Timestamp unix
			if(!empty($timeBegin) && !is_numeric($timeBegin))	{$timeBegin=strtotime($timeBegin);}
			if(!empty($timeEnd) && !is_numeric($timeEnd))		{$timeEnd=strtotime($timeEnd);}

			//Init "IntlDateFormatter"
			$onlyDate=($format=="default" || preg_match("/date/i",$format));
			$formatFull=preg_match("/full/i",$format);
			$formatter=self::dateFormatter();
			if(is_object($formatter)){
				//Debut/fin sur plusieurs jours ou heures
				$diffDays=$diffHours=false;
				if(!empty($timeBegin) && !empty($timeEnd)){
					if(date("Ymd",$timeBegin)!=date("Ymd",$timeEnd))  {$diffDays=true;}
					if(date("H:i",$timeBegin)!=date("H:i",$timeEnd))  {$diffHours=true;}
				}

				//Formatage de la date via "setPattern()" :  https://unicode-org.github.io/icu/userguide/format_parse/datetime/
				$label=$pattern="";																								//Init (pas de "null")
				if($format!="mini" && empty($timeEnd) && date("Ymd",$timeBegin)==date("Ymd"))	{$label=self::trad("today");}	//"Aujourd'hui" (pas dans le $pattern)
				elseif($format=="default")														{$pattern="d MMMM";}			//Exple: "8 fevrier"
				elseif($formatFull==true)														{$pattern="eeee d MMMM";}		//Exple: "lundi 8 fevrier"
				elseif($format=="dateBasic")													{$pattern="dd/MM/yyyy";}		//Exple: "08/02/2050"
				elseif($format=="mini" && $diffDays==true)										{$pattern="d MMM";}				//Exple: "8 fev."
				//Ajoute l'année si différente de celle en cours (Ex: "8 juin 2001")
				if(($format=="default" || $formatFull==true)  &&  (date('Y',$timeBegin)!=date('Y') || (!empty($timeEnd) && date('Y',$timeEnd)!=date('Y'))))   {$pattern.=" yyyy";}
				//Ajoute l'heure si on affiche pas que la date (Ex: "9:05")
				if($onlyDate==false)  {$pattern.=" H:mm";}
				//Instancie le pattern via la "IntlDateFormatter()" avec la "lang" et "timezone" locale
				$formatter->setPattern($pattern);

				//Formate le label de début et/ou de fin
				if(!empty($timeBegin))	{$label.=$formatter->format($timeBegin);}																					//Label de début
				if(!empty($timeEnd)){																																//Label de fin :
					if($diffDays==false && $diffHours==true && $onlyDate==false)	{$formatter->setPattern("H:mm");  $label.='-'.$formatter->format($timeEnd);}	//Même jour + diff heures	-> Ex: "11:30-12:30"
					elseif($diffDays==true)											{$label.='<img src="app/img/arrowRight.png"> '.$formatter->format($timeEnd);}	//Jours différents 			-> $pattern idem $timeBegin
					elseif(empty($timeBegin))										{$label.=Txt::trad("end").' : '.$formatter->format($timeEnd);}					//Date de fin sans début	-> $pattern idem $timeBegin
				}

				//Retourne le résultat en utf-8
				return static::utf8Encode($label);
			}
			//Si "IntlDateFormatter" non instancié : format "date()"
			else  {return ($onlyDate==true) ? date("d/m/Y",$timeBegin) : date("d/m/Y H:i",$timeBegin);}
		}
	}

	/********************************************************************************************************
	 * FORMATAGE SPECIFIQUE EN FONCTION D'UN TIMESTAMP
	 ********************************************************************************************************/
	public static function timeLabel($timestamp, $pattern)
	{
		$formatter=self::dateFormatter();
		if(is_object($formatter)){													//Vérif que dateFormatter() est bien instancié 
			$formatter->setPattern($pattern);										//Instancie le pattern
			return static::utf8Encode($formatter->format($timestamp));				//Retourne le résultat en utf-8
		}else{
			$dateFormat=str_replace(['MMMM','MMM','ccc'], ['F','M','l'], $pattern);	//Format adapté à "date" (mois/jours) 
			return date($dateFormat,$timestamp);									//Retourne le résultat via "date()"
		}
	}

	/********************************************************************************************************
	 * FORMATAGE D'UN DATETIME
	 * 	$format	=> "dbDatetime"		-> "2077-12-31 12:50"
	 * 			=> "dbDate"			-> "2077-12-31"
	 * 			=> "inputDatetime"	-> "31/12/2077 12:50"
	 * 			=> "inputDate"		-> "31/12/2077"
	 * 			=> "inputHM"		-> "12:50"
	 ********************************************************************************************************/
	public static function formatDate($dateValue, $inFormat, $outFormat, $emptyHourNull=false)
	{
		$dateValue=trim((string)$dateValue);																											//Cast l'entrée
		$formatList=["dbDatetime"=>"Y-m-d H:i", "dbDate"=>"Y-m-d", "inputDatetime"=>"d/m/Y H:i", "inputDate"=>"d/m/Y", "inputHM"=>"H:i", "time"=>"U"];	//Liste des formats disponibles
		if(!empty($dateValue) && array_key_exists($inFormat,$formatList) && array_key_exists($outFormat,$formatList)){									//Controle les params
			if($inFormat=="inputDatetime" && strlen($dateValue)<16)		{$dateValue.=" 00:00";}															//Ajoute les minutes/sec. si besoin, sinon $date retourne false..
			elseif($inFormat=="dbDatetime" && strlen($dateValue)>16)	{$dateValue=substr($dateValue,0,16);}											//enlève les microsecondes si besoin, sinon $date retourne false..
			$dateObj=DateTime::createFromFormat($formatList[$inFormat], $dateValue);																	//Créé l'objet DateTime
			$dateObj->setTimeZone(new DateTimeZone(Ctrl::$curTimezone));																				//Applique le bon timezone
			if(is_object($dateObj)){																													//Controle l'objet
				$return=$dateObj->format($formatList[$outFormat]);																						//Formate la date de sortie
				if($outFormat=="inputHM" && $return=="00:00" && $emptyHourNull==true)	{$return=null;}													//null si besoin
				return $return;																															//Renvoie la date
			}
		}
	}

	/********************************************************************************************************
	 * DATES DE PASSAGE À L'HEURE D'ÉTÉ / HIVER
	 ********************************************************************************************************/
	public static function timeChangeDates($year)
	{
		$summer=new DateTime('last sunday of March '.$year);	//heure d'été	: dernier dimanche de mars
		$winter=new DateTime('last sunday of October '.$year);	//heure d'hiver	: dernier dimanche d'octobre
		return ["summer"=>$summer->format('Y-m-d'), "winter"=>$winter->format('Y-m-d')];
	}
}