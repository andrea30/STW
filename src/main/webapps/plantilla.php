﻿<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>Trabajo STW</title>
    <link rel="stylesheet" type="text/css" href="plantilla.css" media="screen" />
    <script src="jquery-1.11.3.min.js"></script>
    <script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=true"></script>
    <script type="text/javascript" src="gmaps.js"></script>
</head>
<body>
	<div id="bizis" class="cont">
		<form method="POST" action="/ruta">
		<fieldset>
		<legend>Bizis:</legend>
			Dirección:
			<input type="text" name="origen"/>
			<br><br>
			<select class="centrar" id="estaciones" name="destino">
                <script type="text/javascript"> 
                    xhttp=new XMLHttpRequest();
                    xhttp.open('GET', '/estaciones' ,false);
                    xhttp.send();
                    var documento=xhttp.responseText;
                    var obj = JSON.parse(documento);
                    var estado = obj.estado;

                    if(estado){         /* creo las opciones del spinner */
                        var ini = obj.infoBizi.start; var total = obj.infoBizi.rows;
                        select = document.getElementById("estaciones");

                        for(var line = ini; line < total; line++){
                            var estacion = obj.infoBizi.result[line].title;
                            var lat = obj.infoBizi.result[line].geometry.coordinates[1];
                            var lng = obj.infoBizi.result[line].geometry.coordinates[0];
                            var opt = document.createElement('option');

                            opt.value = lat+', '+lng;
                            opt.innerHTML = estacion;
                            select.appendChild(opt);
                        }
                    }
                    
                </script>
				
			</select>
			<input id="submit" type="submit" value="Calcula" class="centrar"/>
		</fieldset>
		</form>
	</div>
	    <div id="mapa" class="cont">
		<fieldset>
		<legend>Mapa:</legend>
        <button type="button" id="compactar" align="left"> Compactar ruta</button>
        <button type="button" id="dibujar" align="right"> Dibujar puntos</button>
        <div id="map" style="width:600px; height:400px;"></div>
        <?php
            echo "
            <script type=\"text/javascript\">
                var map, lat, lng, lat1, lng1;
                var geocoder, origen, destino;

                $(function(){
                    $(\"#dibujar\").on('click', codeAddress2);

                    function geolocalizar(){
                        GMaps.geolocate({success: function(position){
                            //obtenemos la posicion actual
                            lat = position.coords.latitude;  // guarda coords en lat y lng
                            lng = position.coords.longitude;
                            var myLatlng = new google.maps.LatLng(lat, lng);
                            var myOptions = {
                                    zoom: 13,
                                    center: myLatlng,
                                    mapTypeId: google.maps.MapTypeId.ROADMAP,
                                };
                            map = new google.maps.Map($(\"#map\").get(0), myOptions);
                            var actual = new google.maps.Marker({
                              position: myLatlng,
                              map: map,
                              title: \"ud. esta aqui\"
                            });
							
                            //generamos la peticion del JSON con las estaciones de bizi
                            xhttp=new XMLHttpRequest();
                            xhttp.open(\"GET\",\"/estaciones\",false);
                            xhttp.send();
                            var documento=xhttp.responseText;
                            var obj = JSON.parse(documento);
                            var estado = obj.estado;

                            if(estado){
                                var ini = obj.infoBizi.start; var total = obj.infoBizi.rows;

                                for(var line = ini; line < total; line++){
                                    var lat = obj.infoBizi.result[line].geometry.coordinates[1];
                                    var lng = obj.infoBizi.result[line].geometry.coordinates[0];
                                    var pos = new google.maps.LatLng(lat,lng);
                                    var marker = new google.maps.Marker({
                                          position: pos,
                                          map: map,
                                          title: 'Estacion '+obj.infoBizi.result[line].id+\": \"+obj.infoBizi.result[line].title+
                                                '   Estado: '+obj.infoBizi.result[line].estado+
                                                '   Bicis: '+obj.infoBizi.result[line].bicisDisponibles+
                                                '   Anclajes: '+obj.infoBizi.result[line].anclajesDisponibles,
                                          icon: \"http://www.zaragoza.es/contenidos/iconos/bizi/conbicis.png\"
                                      });
                                }
                            }
                            },error: function(error) { alert('Geolocalización falla: '+error.message); },
                            not_supported: function(){ alert(\"Su navegador no soporta geolocalización\"); },
                        });
                    };

                    function codeAddress(e) {
                        console.log(\"holis\");
                        geocoder = new google.maps.Geocoder();
                        /*------DIRECCION ORIGEN------*/
                        var address = \"Via de la Hispanidad 120, Zaragoza\"; /*QUITAR DIRECCION HARDCODEADA*/
                        geocoder.geocode( { 'address': address}, function(results, status) {
                            if (status == google.maps.GeocoderStatus.OK) {
                                origen = results[0].geometry.location;
                                var marker = new google.maps.Marker({
                                    map: map,
                                    position: origen
                                });
                            } else {
                                alert(\"Geocode was not successful for the following reason: \" + status);
                            }
                        });
                        /*--------DIRECCION DESTINO--------*/
                        var address2 = \"Plaza San Francisco 1, Zaragoza\"; /*QUITAR DIRECCION HARDCODEADA*/
                        geocoder.geocode( { 'address': address2}, function(results2, status) {
                            if (status == google.maps.GeocoderStatus.OK) {
                                destino = results2[0].geometry.location;
                                var marker = new google.maps.Marker({
                                    map: map,
                                    position: destino
                                });
                            } else {
                                alert(\"Geocode was not successful for the following reason: \" + status);
                            }
                        });

                        /*------DIBUJAR RUTA------*/
                        var directionsService = new google.maps.DirectionsService();
                        var directionsDisplay = new google.maps.DirectionsRenderer();
                        directionsDisplay.setMap(map);

                        console.log(\"traza 1\");

                        var request = {
                            origin:origen,
                            destination:destino,
                            travelMode: google.maps.TravelMode.WALKING
                        };
                        console.log(\"traza 2\");
                        directionsService.route(request, function(result, status) {
                            if (status == google.maps.DirectionsStatus.OK) {
                                directionsDisplay.setDirections(result);
                            }
                        });
                    }
                    /*------------------------FUNCION BUENA--------------------------*/
                    function codeAddress2(e) {
                        xhttp=new XMLHttpRequest();
                        xhttp.open(\"GET\",\"ruta.json\",false);
                        xhttp.send();
                        var documento=xhttp.responseText;
                        console.log(documento);
                        var obj = JSON.parse(documento);
                        var estado = obj.estado;

                        if(estado){
                            var orig = new google.maps.LatLng(obj.origen.lat, obj.origen.lng);
                            var dest = new google.maps.LatLng(obj.destino.lat, obj.destino.lng);
                            var marker = new google.maps.Marker({
                                map: map,
                                position: orig
                            });
                            var marker = new google.maps.Marker({
                                map: map,
                                position: dest
                            });
                            var directionsService = new google.maps.DirectionsService();
                            var directionsDisplay = new google.maps.DirectionsRenderer();
                            directionsDisplay.setMap(map);
                            var request = {
                                origin: orig,
                                destination: dest,
                                travelMode: google.maps.TravelMode.WALKING
                            };
                            directionsService.route(request, function(result, status) {
                                if (status == google.maps.DirectionsStatus.OK) {
                                    directionsDisplay.setDirections(result);
                                }
                            });
                        }
                    }

                    function enlazarMarcador(e){
                        // muestra ruta entre marcas anteriores y actuales
                        map.drawRoute({
                            origin: [lat, lng],  // origen en coordenadas anteriores
                            // destino en coordenadas del click o toque actual
                            destination: [e.latLng.lat(), e.latLng.lng()],
                            travelMode: 'driving',
                            strokeColor: '#000000',
                            strokeOpacity: 0.6,
                            strokeWeight: 5
                        });
                        lat = e.latLng.lat();   // guarda coords para marca siguiente
                        lng = e.latLng.lng();

                        map.addMarker({ lat: lat, lng: lng});  // pone marcador en mapa
                    };

                    function compactarRuta(){
                        map.cleanRoute();
                        map.removeMarkers();
                        map.addMarker({ lat: lat1, lng: lng1});
                        map.addMarker({ lat: lat, lng: lng});
                        map.drawRoute({
                            origin: [lat1, lng1],
                            destination: [lat, lng],
                            travelMode: 'driving',
                            strokeColor: '#FF0000',
                            strokeOpacity: 0.8,
                            strokeWeight: 5
                        });
                    }
                    geolocalizar();
                });
            </script>
            ";
        ?>
		</fieldset>
	</div>
	<div id="p6" class="cont">
        <fieldset>
        <legend>Previsión meteorológica:</legend>
        <div id="tableTiempo">
        
        <?php
            $fp=fopen("../resources/municipios.txt", "r");
            $linea;
            $municipios=array();
            $codigos=array();

            while( ($linea=fgets($fp)) !== false ){
                list($cpro, $mun, $dc, $nombre)=split("[\r\t]+", $linea);

                array_push($municipios, $nombre);
                array_push($codigos, $cpro.$mun);
            }
            fclose($fp);
        //***
           $codigo=50001;
		   $munSel="Abanto";
		   if(isset($_GET['mun'])){
                $munSel=$_GET['mun'];
                $codigo=obtenerCod($munSel, $municipios, $codigos);
            }

            function obtenerCod($string, $muni, $codi) {
                $cont=1;
                foreach($muni as $m){
                    if($m==$string){
                        return $codi[$cont];
                    }
                    $cont+=1;
                }
                return 0;
            }
		//**********
            try{
				$clienteSOAP = new SoapClient('http://localhost:8080/axis/services/Tiempo?wsdl');
				
				$xmlTiempo = $clienteSOAP->DescargarInfoTiempo($codigo);
				$html = $clienteSOAP->GenerarHTML($xmlTiempo);
				$json = $clienteSOAP->GenerarJSON($xmlTiempo);
				echo("<h2>Tiempo en ".$munSel.":</h2>");
				echo($html);
			 
			} catch(SoapFault $e){
				echo "<h3>Error al obtener el tiempo. Prueba mas tarde.</h3>";
			}

        ?>
        </div>
        <form method="POST" action="/tiempo">
            <select class="centrar" name="mun">
                <?php
                foreach($municipios as $res){
                    echo '<option value="'.$res.'">'.$res.'</option>';
                }
                ?>
            </select>
            <input id="submit" type="submit" value="Obtener información meteorológica" class="centrar"/>
        </form>
        </fieldset>
    </div>

</body>
</html>
