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
	protected static $IntlDateFormatter=null;
	public static $tradList=["francais","english","espanol","italiano","deutsch","portugues"];

	/********************************************************************************************************
	 * CHARGE LES TRADUCTIONS
	 ********************************************************************************************************/
	public static function loadTrads()
	{
		//Charge les trads en session
		if(empty(self::$trad)){
			if(Req::isParam("curTrad") && preg_match('/^[A-Z]+$/i',Req::param("curTrad")))	{$_SESSION["curTrad"]=Req::param("curTrad");}	//Trad demandée
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
	 * AFFICHE UN TITLE / TOOLTIP DANS UNE BALISE
	 ********************************************************************************************************/
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

	/********************************************************************************************************
	 * REDUCTION DE TEXTE POUR LES TOOLTIPS, LOGS, ETC : ENLEVE LES TAGS HTML
	 ********************************************************************************************************/
	public static function reduce($text, $maxSize=200)
	{
		if(!empty($text)){
			$text=self::clean($text,"min");										//Clean le texte
			if(strlen($text) > $maxSize){										//Vérif si le texte dépasse $maxSize
				$text=substr($text, 0, $maxSize);								//Réduit le texte au $maxSize
				$lastSpace=strrpos($text, ' ');									//Position du dernier espace
				if($lastSpace!==false)	{$text=substr($text, 0, $lastSpace);}	//Enlève le dernier mot après le dernier espace
				$text=trim($text,',').'...';									//Ajoute '...' en fin de texte (trim si besoin les virgules)
			}
			return htmlentities($text);											//Retourne le résultat avec encodage des caractères html (ex: & -> &amp;)
		}
	}

	/*********************************************************************************************************************
	 * CLEAN DE TEXTE : SUPPRIME LES CARACTERES SPECIAUX ET ACCENTUES
	 * $scope="min" 	-> parametres de fichier Ical, etc :			"l'été &amp; (!?)"  ->  "l'été & (!?)"
	 * $scope="normal"	-> noms de fichier, recherche d'objets, etc :	"l'été &amp; (!?)"  ->  "l'été _ (_)"
	 * $scope="max"		-> identifiants, noms en bdd, etc :				"l'été &amp; (!?)"  ->  "l_ete_"
	 *********************************************************************************************************************/
	public static function clean($text, $scope="normal", $charReplace="_")
	{
		//Supprime les balises html et décode les caractères html (ex: '&amp;' de TinyMce devient '&')  &&  Supprime les '&nbsp;', espaces multiples, tabulations et sauts de ligne
		$text=html_entity_decode(strip_tags($text));
		$text=preg_replace(['/&nbsp;/','/\s+/'], ' ', $text);
		//Remplace les caractères accentués
		if($scope=="max"){
			$charsAccent=['/[àáâãäå]/u', '/[ç]/u', '/[èéêë]/u', '/[ìíîï]/u', '/[ñ]/u', '/[òóôõö]/u', '/[ùúûü]/u', '/[ÀÁÂÃÄÅ]/u', '/[Ç]/u', '/[ÈÉÊË]/u', '/[ÌÍÎÏ]/u', '/[Ñ]/u', '/[ÒÓÔÕÖ]/u', '/[ÙÚÛÜ]/u', '/[Ý]/u'];
			$charsBasic =['a', 'c', 'e', 'i', 'n', 'o', 'u', 'A', 'C', 'E', 'I', 'N', 'O', 'U', 'Y'];
			$text=preg_replace($charsAccent, $charsBasic, $text);
		}
		//Conserve les caractères alphanumériques et certains caractères spéciaux
		$charsSpec='\.\-\_';																//conserve les caractères spéciaux  . - _ 
		if($scope!="max")	{$charsSpec.='\s\'\,\(\)\[\]';}									//conserve aussi les espaces  ' , ( ) [ ]
		if($scope=="min")	{$charsSpec.='\*\:\<\>\@\&\?\!\#"\/\\\\';  $charReplace=' ';}	//conserve aussi les  * : < > @ & ? ! # " / \   ('\' doit être échappé 2 fois)
		$text=preg_replace('/[^\p{L}0-9'.$charsSpec.']/u', $charReplace, $text);			//Conserve les lettres (même accentuées) via  \p{L}  +  chiffres via  0-9  + caractère spéciaux
		//Renvoie le résultat
		return trim($text);
	}

	/********************************************************************************************************
	 * RETOURNE UNE CHAINE EN UTF-8
	 ********************************************************************************************************/
	public static function utf8Encode($text)
	{
		$encoding=mb_detect_encoding($text, ['UTF-8','ISO-8859-1','ISO-8859-15','Windows-1252','ASCII'], true);	//Détection de l'encodage
		return ($encoding!='UTF-8')  ?  mb_convert_encoding($text, 'UTF-8', $encoding)  :  $text;				//Return le texte encodé en UTF-8  || Return le texte déjà en UTF-8
	}

	/********************************************************************************************************
	 * RETOURNE UNE CHAINE EN ISO-8859-1
	 ********************************************************************************************************/
	public static function utf8Decode($text)
	{
		return mb_convert_encoding($text, 'ISO-8859-1', 'UTF-8');
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
	 * CONTROLE LA VALIDITÉ D'UN EMAIL
	 ********************************************************************************************************/
	public static function isMail($email){ 
		return (!empty($email) && filter_var($email,FILTER_VALIDATE_EMAIL));
	}

	/***********************************************************************************************************************
	 * CONTROLE LA VALIDITE D'UN PASSWORD : 6 CARACTÈRES MINIMUM, AVEC AU MOINS UNE MAJUSCULE, UNE MINUSCULE ET UN CHIFFRE
	 ***********************************************************************************************************************/
	public static function isValidPassword($password)
	{
		return preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{6,}$/', $password);
	}

	/********************************************************************************************************
	 * CRÉÉ UN PASSWORD TEMPORAIRE PAR DEFAUT
	 ********************************************************************************************************/
	public static function defaultPassword()
	{
		return substr(uniqid(),0,8);
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
	 * $format	=>	"default"	->	8 fevrier"
	 * 			=>	"dateFull"	->	"lun. 8 fevrier"
	 * 			=>	"labelFull"	->	"lun. 8 fevrier 9:05"
	 * 			=>	"mini"		->	"9:05" ou "8 fev. 9:05" (si $diffDays)
	 * 			=>	"dateBasic"	->	"08/02/2050"
	 * 			=>	"dateMini"	->	"08/02"
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
			$formatFull=($format=="dateFull" || $format=="labelFull");
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
				elseif($formatFull==true)														{$pattern="eee d MMMM";}		//Exple: "lun. 8 fevrier"
				elseif($format=="dateBasic")													{$pattern="dd/MM/yyyy";}		//Exple: "08/02/2050"
				elseif($format=="dateMini")														{$pattern="dd/MM";}				//Exple: "08/02"
				elseif($format=="mini" && $diffDays==true)										{$pattern="d MMM";}				//Exple: "8 fev."
				//Ajoute l'année si différente de celle en cours (Ex: "8 juin 2001")
				if(($format=="default" || $formatFull==true)  &&  (date('Y',$timeBegin)!=date('Y') || (!empty($timeEnd) && date('Y',$timeEnd)!=date('Y'))))   {$pattern.=" yyyy";}
				//Ajoute l'heure si on affiche pas que la date (Ex: "9:05")
				if($onlyDate==false)  {$pattern.=" H:mm";}
				//Instancie le pattern via la "IntlDateFormatter()" avec la "lang" et "timezone" locale
				$formatter->setPattern($pattern);

				//Formate le label de début et/ou de fin
				if(!empty($timeBegin))	{$label.=$formatter->format($timeBegin);}																						//Label de début
				if(!empty($timeEnd)){																																	//Label de fin :
					if($diffDays==false && $diffHours==true && $onlyDate==false)	{$formatter->setPattern("H:mm");  $label.='-'.$formatter->format($timeEnd);}		//Même jour + diff heures	-> Ex: "11:30-12:30"
					elseif($diffDays==true)											{$label.='<img src="app/img/arrowRightSmall.png">'.$formatter->format($timeEnd);}	//Jours différents 			-> $pattern idem $timeBegin
					elseif(empty($timeBegin))										{$label.=Txt::trad("end").' : '.$formatter->format($timeEnd);}						//Date de fin sans début	-> $pattern idem $timeBegin
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