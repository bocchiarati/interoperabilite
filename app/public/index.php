<?php

// api GEOLOC IP
// https://ipapi.co/{ip?}/{format}/
// ? = facultatif

// EN LOCAL
$url_api_geoloc_ip = "https://ipapi.co/xml";
// UNE FOIS SUR WEBETU
//$url_api_geoloc_ip = "https://ipapi.co/".$_SERVER["REMOTE_ADDR"]."/xml";

$geoloc_ip_content = file_get_contents($url_api_geoloc_ip);

file_put_contents("XML/geoloc_ip_content.xml", $geoloc_ip_content);


$xml_geoloc_ip_file = simplexml_load_file("XML/geoloc_ip_content.xml");
$xsl_geoloc_ip_file = simplexml_load_file("XSL/geoloc_ip.xsl");
$xsl_geoloc_ip_info_file = simplexml_load_file("XSL/geoloc_ip_info.xsl");

$processor = new XSLTProcessor();
$processor->importStylesheet($xsl_geoloc_ip_file);

$geoloc = $processor->transformToXML($xml_geoloc_ip_file);

$processor->importStylesheet($xsl_geoloc_ip_info_file);
echo $processor->transformToXML($xml_geoloc_ip_file);

$url_api_meteo = "https://www.infoclimat.fr/public-api/gfs/xml?_ll=".$geoloc."&_auth=ARsDFFIsBCZRfFtsD3lSe1Q8ADUPeVRzBHgFZgtuAH1UMQNgUTNcPlU5VClSfVZkUn8AYVxmVW0Eb1I2WylSLgFgA25SNwRuUT1bPw83UnlUeAB9DzFUcwR4BWMLYwBhVCkDb1EzXCBVOFQoUmNWZlJnAH9cfFVsBGRSPVs1UjEBZwNkUjIEYVE6WyYPIFJjVGUAZg9mVD4EbwVhCzMAMFQzA2JRMlw5VThUKFJiVmtSZQBpXGtVbwRlUjVbKVIuARsDFFIsBCZRfFtsD3lSe1QyAD4PZA%3D%3D&_c=19f3aa7d766b6ba91191c8be71dd1ab2";
$meteo_content = file_get_contents($url_api_meteo);
file_put_contents("XML/meteo_content.xml", $meteo_content);

$xml_file = simplexml_load_file("XML/meteo_content.xml");
$xsl_file = simplexml_load_file("XSL/meteo.xsl");

$processor->importStylesheet($xsl_file);
echo $processor->transformToXml($xml_file);

$env = parse_ini_file(".env");
$apiKey = $env["API_KEY"];
echo <<< MAP
<head>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
         integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
         crossorigin=""/>
     
     <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
         integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
         crossorigin=""></script>
</head>
<body> 
    <div style='height: 750px; margin-bottom: 10em; width:75%; margin-left:auto; margin-right:auto' id='map'></div>
    <script>
        const map = L.map('map').setView([$geoloc], 13);
        
        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
        }).addTo(map);
        
        const marker = L.marker([$geoloc]).addTo(map);
        
        const trafficLayer = L.tileLayer(`https://{s}.api.tomtom.com/traffic/map/4/tile/flow/relative/{z}/{x}/{y}.png?key=$apiKey`, {
            maxZoom: 22,
            tileSize: 256,
            subdomains: ['a', 'b', 'c', 'd'],
            opacity: 0.7
        }).addTo(map);
    </script>
</body>
MAP;

$data = json_decode(file_get_contents("https://carto.g-ny.eu/data/cifs/cifs_waze_v2.json"));

echo <<< ICON
    <script>
        var chantierIcon = L.icon({
            iconUrl: 'image/chantier.png',
            iconSize: [40, 40],
            iconAnchor: [22, 94],
            popupAnchor: [-3, -76],
        });
    </script>
ICON;

foreach ($data->incidents as $incident) {
    // Extraction des coordonnées (séparation de la chaîne par espace)
    $coords = explode(' ', $incident->location->polyline);
    $lat = $coords[0];
    $lng = $coords[1];
    $type = $incident->type;
    $start = $incident->starttime;
    $end = $incident->endtime;

    echo <<< INCIDENT
        <script>
            L.marker([$lat , $lng ]).addTo(map).bindPopup("Date de debut : $start, Date de fin : $end").setIcon(chantierIcon);
        </script>
INCIDENT;
}
