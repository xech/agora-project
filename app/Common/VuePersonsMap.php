<style>
#htmlLightbox body	{padding:0px!important;}
</style>

<!--CONTENEUR DE LA CARTE -->
<div id="mapid"></div>

<!--LIBRAIRIE GOOGLE MAPS / LEAFLET-OPENSTREETMAP-->
<?php if($mapTool=="gmap"){ ?>
	<script src="https://maps.googleapis.com/maps/api/js?key=<?= Ctrl::$agora->gApiKey ?>"></script>
<?php }else{ ?>
	<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
	<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<?php } ?>


<script>
/**********************************************************************************************************
 *	INIT LA CARTE ET MARQUE LES ADRESSES
 **********************************************************************************************************/
ready(function(){
	//// Init l'affichage
	mapTool="<?= $mapTool ?>";//"gmap" ou "leaflet"
	$("#mapid").css("width",windowWidth).css("height",windowHeight-(isMobile()?0:80));//Redimensionne le div de la carte
	window.top.$.fancybox.getInstance().update();//Redimensionne le fancybox

	//// Instancie la carte Google Map
	if(mapTool=="gmap"){
		gMap	=new google.maps.Map(document.getElementById("mapid"), {zoom:7});	//Init la carte
		geocoder=new google.maps.Geocoder();										//Init le géocodeur
		bounds	=new google.maps.LatLngBounds();									//bornes la carte (latitude/longitude)
	}
	//// Instancie la carte Leaflet-Openstreetmap
	else{
		leafletMap=L.map("mapid").setView([45,-20], 14);																//Créé une nouvelle carte (par défaut sur l'Atlantique)
		L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png',{attribution:'OpenStreetMap'}).addTo(leafletMap);	//Ajoute les tuiles/vues d'Openstreetmap	
	}
	//// Init les adresses && lance l'affichage de chaque adresse
	adressList=<?= $adressList ?>;//Adresses au format json
	displayAdress();
});

/**********************************************************************************************************
 *	GEOCODE ET MARQUE CHAQUE ADRESSE SUR LA CARTE
 **********************************************************************************************************/
function displayAdress(adressKey)
{
	//// Init
	if(typeof adressKey=="undefined")  {adressKey=0;}
	var tmpAdress=adressList[adressKey];

	//// GOOGLE MAP
	if(mapTool=="gmap"){
		//// Géocode et marque chaque adresse
		geocoder.geocode({"address":tmpAdress.adress}, function(results,status){
			//// Géolocalisation OK : ajoute le marker
			if(status==google.maps.GeocoderStatus.OK){
				// Ajoute les coordonnées
				tmpAdress.lat=results[0].geometry.location.lat();
				tmpAdress.lng=results[0].geometry.location.lng();
				// Image du marker (url + position de la photo par rapport au point du marker = centre/bottom + dimension de la photo)
				var tmpMarkerImage=new google.maps.MarkerImage(tmpAdress.personImg, null, null, new google.maps.Point(19,38), new google.maps.Size(38,38));
				tmpAdress.marker=new google.maps.Marker({map:gMap, title:tmpAdress.personLabel, position:results[0].geometry.location, icon:tmpMarkerImage});
				// Infobulle du marker
				tmpAdress.tooltipHtml=tmpAdress.personLabel+"<div id='streetView"+adressKey+"' style='width:400px;height:300px;'>Street View loading ...</div>";
				tmpAdress.tooltip=new google.maps.InfoWindow( {content:tmpAdress.tooltipHtml} );
				google.maps.event.addListener(tmpAdress.marker, "click", function(){
					tmpAdress.tooltip.open(gMap, tmpAdress.marker);
					setTimeout(function(){//Appel de StreetView une fois l'infobulle chargée
						StreetView=new google.maps.StreetViewPanorama(document.getElementById("streetView"+adressKey));
						StreetView.setPosition(new google.maps.LatLng(tmpAdress.lat,tmpAdress.lng));
					},200);
				});
				// Ajoute le marker pour délimiter la carte (bounds)
				bounds.extend(new google.maps.LatLng(tmpAdress.lat,tmpAdress.lng));
			}
			//..Erreur "OVER_QUERY_LIMIT" (Quota par seconde dépassé) : relance l'affichage de l'adresse avec un délais d'1 seconde
			else if(status==google.maps.GeocoderStatus.OVER_QUERY_LIMIT){
				setTimeout(function(){ displayAdress(adressKey); },1000);
				return false;
			}
			//..Erreur "Adresse non trouvé"
			else{
				notify("<?= Txt::trad("mapLocalizationFailure") ?> :<br>"+tmpAdress.adress+"<br><br><?= Txt::trad("mapLocalizationFailure2") ?>");
			}
			//// Géolocalise l'adresse suivante  OU  Affichage final : recentre et ajuste le zoom en fonction des limites de la carte (bounds)
			if(getAdressKeyNext(adressKey))  {displayAdress(getAdressKeyNext(adressKey));}
			else{
				gMap.fitBounds(bounds);
				setTimeout(function(){ gMap.setZoom(14); },200);//Dézoom juste après le "fitBounds"
			}
		});
	}
	//// LEAFLET-OPENSTREETMAP
	else{
		//// Géocode et marque chaque adresse
		$.ajax({url:"https://nominatim.openstreetmap.org/search?format=json&q="+encodeURIComponent(tmpAdress.adress), dataType:"json"}).done(function(result){
			//// "Adresse non trouvé"  OU  Adresse trouvée et placée sur la carte
			if(result.length==0)  {notify("<?= Txt::trad("mapLocalizationFailure") ?> :<br>"+tmpAdress.adress+"<br><br><?= Txt::trad("mapLocalizationFailure2") ?>");}
			else{
				//DEBUG
				//notify(tmpAdress.adress+"<br>->"+result[0].display_name+"<br>->"+result[0].lat+"<br>->"+result[0].lon);
				//Init la liste des markers
				if(typeof markersList=="undefined")  {markersList=[];}
				//Ajoute la position dans la liste des markers
				var latLngTmp=L.latLng(result[0].lat, result[0].lon);
				markersList.push(latLngTmp);
				//Ajoute l'icone et label de l'user/contact
				var markerIcon=L.icon({ iconUrl:tmpAdress.personImg, iconSize:[38,38] });
				L.marker(latLngTmp,{icon:markerIcon}).bindPopup(tmpAdress.personLabel).addTo(leafletMap);
				//Ajoute le marker pour délimiter la carte (bounds)
				var bounds=new L.LatLngBounds(markersList);
			}
			//// Géolocalise l'adresse suivante  OU  Affichage final : recentre et ajuste le zoom en fonction des limites de la carte ("fitBounds()" et "zoomOut()")
			if(getAdressKeyNext(adressKey))	{displayAdress(getAdressKeyNext(adressKey));}
			else							{leafletMap.fitBounds(bounds).zoomOut(1);}
		});
	}
}

/**********************************************************************************************************
 *	RETOURNE LA 'KEY' DE L'ADRESSE SUIVANTE S'IL Y EN A
 **********************************************************************************************************/
function getAdressKeyNext(adressKey)
{
	var adressKeyNext=parseInt(adressKey+1);
	return (adressKeyNext < adressList.length)  ?  adressKeyNext  :  false;
}
</script>