<!--PAS DE ZOOM DE LA PAGE (SAUF POUR LA CARTE)-->
<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">

<!--CONTENEUR DE LA CARTE-->
<img src="app/img/loading.gif" style="position:absolute;bottom:50px;right:50px;z-index:50;">
<div id="mapid"></div>


<?php
////	CHARGE LA LIBRAIRIE GOOGLE MAP  ||  LIBRAIRIE LEAFLET/OPENSTRETMAP
if($mapTool=="gmap"){
	//"gMapsApiKey" à récupérer sur "https://developers.google.com/maps/documentation/javascript/get-api-key"
	echo '<script src="https://maps.googleapis.com/maps/api/js?key='.Ctrl::$agora->gMapsApiKey().'"></script>';
}else{
	echo '<link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" integrity="sha512-xodZBNTC5n17Xt2atTPuE1HxjVMSvLVW9ocqUKLsCC5CXdbqCmblAshOMAS6/keqq/sMZMZ19scR4PsZChSR7A==" crossorigin=""/>
		  <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js" integrity="sha512-XQoYMqMTK8LvdxXYG3nZ448hOEQiglfqkJs1NOQV44cWnUrBc8PkAOcXy20w0vlaXaVUearIOBhiXZ5V3ynxwA==" crossorigin=""></script>';
}
?>


<script>
////	INIT LA CARTE ET MARQUE LES ADRESSES
$(function(){
	//// Init l'affichage
	mapTool="<?= $mapTool ?>";//Carte Gmap OU Leaflet
	$("#mapid").css("width",parent.$(window).width()+"px").css("height",parent.$(window).height()+"px");//Redimensionne le div de la carte
	parent.$.fancybox.getInstance().update();//Redimensionne le fancybox

	//// Instancie la carte GMAP
	if(mapTool=="gmap"){
		map		=new google.maps.Map(document.getElementById("mapid"), {zoom:8});//Init la carte Gmap
		geocoder=new google.maps.Geocoder();//Init le géocodeur
		bounds	=new google.maps.LatLngBounds();//bornes la carte (latitude/longitude)
	}
	//// Instancie la carte LEAFLET
	else{
		myMap=L.map("mapid").setView([45,-20], 14);																													//Créé une carte positionnée par défaut sur l'Atlantique
		var layerUrl='https://api.mapbox.com/styles/v1/{id}/tiles/{z}/{x}/{y}?access_token={accessToken}';															//Url de la carte générée via mapbox.com
		var layerToken="pk.eyJ1IjoibWFwYm94IiwiYSI6ImNpejY4NXVycTA2emYycXBndHRqcmZ3N3gifQ.rJcFIG214AriISLbB6B5aw";													//Token de la carte
		L.tileLayer(layerUrl, {id:"mapbox/streets-v11", accessToken:layerToken, tileSize:512, zoomOffset:-1, attribution:'OpenStreetMap | Mapbox'}).addTo(myMap);	//Affiche la carte avec la vue "mapbox/streets-v11"
	}

	//// Init les adresses && lance l'affichage de chaque adresse
	adressList=<?= $adressList ?>;//Adresses au format json
	displayAdress();
});

////	GEOCODE ET MARQUE CHAQUE ADRESSE SUR LA CARTE
function displayAdress(adressKey)
{
	//// Init l'affichage
	if(typeof adressKey=="undefined")  {adressKey=0;}
	var tmpAdress=adressList[adressKey];

	//// Carte GMAP
	if(mapTool=="gmap")
	{
		//// Géocode et marque chaque adresse
		geocoder.geocode({"address":tmpAdress.adress}, function(results,status){
			//// Géolocalisation OK : ajoute le marker
			if(status==google.maps.GeocoderStatus.OK)
			{
				//Ajoute les coordonnées
				tmpAdress.lat=results[0].geometry.location.lat();
				tmpAdress.lng=results[0].geometry.location.lng();
				// Image du marker (url + position de la photo par rapport au point du marker = centre/bottom + dimension de la photo)
				var tmpMarkerImage=new google.maps.MarkerImage(tmpAdress.personImg, null, null, new google.maps.Point(19,38), new google.maps.Size(38,38));
				tmpAdress.marker=new google.maps.Marker({map:map, title:tmpAdress.personLabel, position:results[0].geometry.location, icon:tmpMarkerImage});
				// Infobulle du marker
				tmpAdress.tooltipHtml=tmpAdress.personLabel+"<div id='streetView"+adressKey+"' style='width:400px;height:300px;'>Street View loading ...</div>";
				tmpAdress.tooltip=new google.maps.InfoWindow( {content:tmpAdress.tooltipHtml} );
				google.maps.event.addListener(tmpAdress.marker, "click", function(){
					tmpAdress.tooltip.open(map, tmpAdress.marker);
					setTimeout(function(){//Appel de StreetView une fois l'infobulle chargée
						StreetView=new google.maps.StreetViewPanorama(document.getElementById("streetView"+adressKey));
						StreetView.setPosition(new google.maps.LatLng(tmpAdress.lat,tmpAdress.lng));
					},200);
				});
				//Ajoute le marker pour délimiter la carte (bounds)
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
				map.fitBounds(bounds);
				if(map.getZoom()>15)  {map.setZoom(15);}//Dézoom la carte si besoin
				$("img[src*=loading]").hide();
			}
		});
	}
	//// Carte LEAFLET
	else
	{
		//// Géocode et marque chaque adresse
		$.ajax({url:"https://nominatim.openstreetmap.org/search?format=json&q="+encodeURIComponent(tmpAdress.adress), dataType:"json"}).done(function(result){
			//// "Adresse non trouvé"  OU  Adresse trouvée et placée sur la carte
			if(result.length==0)  {notify("<?= Txt::trad("mapLocalizationFailure") ?> :<br>"+tmpAdress.adress+"<br><br><?= Txt::trad("mapLocalizationFailure2") ?>");}
			else{
				//Init la liste des markers
				if(typeof markersList=="undefined")  {markersList=[];}
				//Ajoute la position dans la liste des markers
				var latLngTmp=L.latLng(result[0].lat, result[0].lon);
				markersList.push(latLngTmp);
				//Ajoute l'icone et label de l'user/contact
				var markerIcon=L.icon({ iconUrl:tmpAdress.personImg, iconSize:[38,38] });
				L.marker(latLngTmp,{icon:markerIcon}).bindPopup(tmpAdress.personLabel).addTo(myMap);
				//Ajoute le marker pour délimiter la carte (bounds). Debug:  notify(tmpAdress.adress+" => "+result[0].display_name+" => "+result[0].lat+" => "+result[0].lon);
				var bounds=new L.LatLngBounds(markersList);
			}
			//// Géolocalise l'adresse suivante  OU  Affichage final : recentre et ajuste le zoom en fonction des limites de la carte (bounds)
			if(getAdressKeyNext(adressKey))	{displayAdress(getAdressKeyNext(adressKey));}
			else							{myMap.fitBounds(bounds);  $("img[src*=loading]").hide();}
		});
	}
}

////	RETOURNE LA 'KEY' DE L'ADRESSE SUIVANTE S'IL Y EN A
function getAdressKeyNext(adressKey)
{
	var adressKeyNext=parseInt(adressKey+1);
	return (adressKeyNext < adressList.length)  ?  adressKeyNext  :  false;
}
</script>
