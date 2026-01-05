<?php
echo "<head><link rel='stylesheet' href='css/style_php.css'></head>";
// api GEOLOC IP
// https://ipapi.co/{ip?}/{format}/
// ? = facultatif

// EN LOCAL
$url_api_geoloc_ip = "https://ipapi.co/xml";
// UNE FOIS SUR WEBETU
$url_api_geoloc_ip = "https://ipapi.co/".$_SERVER["REMOTE_ADDR"]."/xml";

$geoloc_ip_content = file_get_contents($url_api_geoloc_ip);

file_put_contents("XML/geoloc_ip_content.xml", $geoloc_ip_content);


$xml_geoloc_ip_file = simplexml_load_file("XML/geoloc_ip_content.xml");
$xsl_geoloc_ip_file = simplexml_load_file("XSL/geoloc_ip.xsl");
$xsl_geoloc_ip_info_file = simplexml_load_file("XSL/geoloc_ip_info.xsl");

$processor = new XSLTProcessor();
$processor->importStylesheet($xsl_geoloc_ip_file);

$geoloc = $processor->transformToXML($xml_geoloc_ip_file);

$adresse = "2Ter Bd Charlemagne, 54000 Nancy";
$api_loca_adresse = "https://api-adresse.data.gouv.fr/search/?q=".urlencode($adresse)."&limit=1";
$data_adresse = json_decode(file_get_contents($api_loca_adresse));

if(empty($geoloc)){
    if (!empty($data_adresse->features)) {
        $coordinates = $data_adresse->features[0]->geometry->coordinates;
        $longitude = $coordinates[0];
        $latitude = $coordinates[1];
        $geoloc = $latitude . "," . $longitude;
    }
}

// POLLUTION
$data_pollution = json_decode(file_get_contents("https://services3.arcgis.com/Is0UwT37raQYl9Jj/arcgis/rest/services/ind_grandest/FeatureServer/0/query?where=lib_zone%3D'Nancy'&outFields=*&returnGeometry=true&f=pjson"));


$geoloc_tab = explode(',', $geoloc);
$lat = $geoloc_tab[0];
$lng = $geoloc_tab[1];

$closest = null;
$minDist = INF;
foreach ($data_pollution->features as $feature) {
    $dist = hypot($feature->attributes->y_wgs84 - $lat, $feature->attributes->x_wgs84 - $lng);
    if ($dist < $minDist) {
        $minDist = $dist;
        $closest = $feature;
    }
}

$processor->importStylesheet($xsl_geoloc_ip_info_file);
$transform_geoloc = $processor->transformToXML($xml_geoloc_ip_file);
$lib_qual = $closest->attributes->lib_qual;
echo <<< GEOLOC
    <div id='geoloc'>
        $transform_geoloc
        <p><strong>Qualite de l'air</strong> : $lib_qual</p>
   </div>;
GEOLOC;

$url_api_meteo = "https://www.infoclimat.fr/public-api/gfs/xml?_ll=".$geoloc."&_auth=ARsDFFIsBCZRfFtsD3lSe1Q8ADUPeVRzBHgFZgtuAH1UMQNgUTNcPlU5VClSfVZkUn8AYVxmVW0Eb1I2WylSLgFgA25SNwRuUT1bPw83UnlUeAB9DzFUcwR4BWMLYwBhVCkDb1EzXCBVOFQoUmNWZlJnAH9cfFVsBGRSPVs1UjEBZwNkUjIEYVE6WyYPIFJjVGUAZg9mVD4EbwVhCzMAMFQzA2JRMlw5VThUKFJiVmtSZQBpXGtVbwRlUjVbKVIuARsDFFIsBCZRfFtsD3lSe1QyAD4PZA%3D%3D&_c=19f3aa7d766b6ba91191c8be71dd1ab2";
$meteo_content = file_get_contents($url_api_meteo);
file_put_contents("XML/meteo_content.xml", $meteo_content);

$xml_file = simplexml_load_file("XML/meteo_content.xml");
$xsl_file = simplexml_load_file("XSL/meteo.xsl");

$processor->importStylesheet($xsl_file);
$transform_meteo = $processor->transformToXml($xml_file);
echo <<<METEO
    <h1 class="title"> METEO </h1>
    <div id='meteo'> $transform_meteo </div>;
METEO;

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
    <h1 class="title"> MAP INFO-TRAFIC</h1>
    <div id='map'></div>
    <script>
        const map = L.map('map').setView([$geoloc ], 13);
        
        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
        }).addTo(map);
        
        const marker = L.marker([$geoloc ]).addTo(map);
        
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


// COVID :
$covid_data = json_decode(file_get_contents("https://www.data.gouv.fr/api/1/datasets/r/d2671c6c-c0eb-4e12-b69a-8e8f87fc224c"));
$date = [];
$casConfirme = [];

foreach ($covid_data as $covid) {
    $date[] = $covid->date;
    $casConfirme[] = $covid->casConfirmes;
}

$labels_json = json_encode($date);
$cases_json = json_encode($casConfirme);

echo <<< COVID
<h1 class="title"> Evolution de la pandmie du COVID-19</h1>
<canvas id="covidChart"></canvas>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  const labels = $labels_json
  const cases = $cases_json  

  new Chart(document.getElementById('covidChart'), {
    type: 'line',
    data: {
      labels: labels,
      datasets: [{
        label: 'Cas Confirmés',
        data: cases,
        borderColor: 'rgb(75, 192, 192)',
        fill: false
      }]
    }
  })    
</script>
COVID;

if (!empty($data_adresse->features)) {
    $coordinates = $data_adresse->features[0]->geometry->coordinates;
    $longitude = $coordinates[0];
    $latitude = $coordinates[1];

    echo <<< MARKER_ADRESSE
        <script>
        const ecoleIcon = L.icon({
            iconUrl: 'image/ecole.png',
            iconSize: [40, 40],
            iconAnchor: [22, 94],
            popupAnchor: [-3, -76],
        });
            L.marker([$latitude , $longitude ]).addTo(map).setIcon(ecoleIcon);
        </script>
MARKER_ADRESSE;
}


echo <<<APIS
<footer>
    <h3 style="color: #6ec2f3; border-bottom: 1px solid #343a40; padding-bottom: 10px;">Sources</h3>
    <ul style="
        list-style: none; 
        padding: 0; 
        display: grid; 
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); 
        gap: 15px;
    ">
        <li><strong style="color: white;">📍 Géolocalisation IP :</strong> <a href="https://ipapi.co/" style="color: #fd746c; text-decoration: none;">ipapi.co</a></li>
        <li><strong style="color: white;">🏠 Adresse :</strong> <a href="https://adresse.data.gouv.fr/" style="color: #fd746c; text-decoration: none;">data.gouv.fr</a></li>
        <li><strong style="color: white;">🍃 Qualité de l'air :</strong> <a href="https://www.atmo-grandest.eu/" style="color: #fd746c; text-decoration: none;">Atmo Grand Est</a></li>
        <li><strong style="color: white;">☁️ Météo :</strong> <a href="https://www.infoclimat.fr/" style="color: #fd746c; text-decoration: none;">Infoclimat</a></li>
        <li><strong style="color: white;">🗺️ Cartographie :</strong> <a href="https://www.openstreetmap.org/" style="color: #fd746c; text-decoration: none;">OpenStreetMap</a></li>
        <li><strong style="color: white;">🚗 Trafic :</strong> <a href="https://developer.tomtom.com/" style="color: #fd746c; text-decoration: none;">TomTom API</a></li>
        <li><strong style="color: white;">⚠️ Incidents :</strong> <a href="https://carto.g-ny.eu/" style="color: #fd746c; text-decoration: none;">G-NY Nancy</a></li>
        <li><strong style="color: white;">📊 COVID-19 :</strong> <a href="https://www.data.gouv.fr/" style="color: #fd746c; text-decoration: none;">Santé Publique France</a></li>
        <li><strong style="color: white;">Github :</strong> <a href="https://github.com/bocchiarati/interoperabilite" style="color: #fd746c; text-decoration: none;">Github Projet</a></li>
    </ul>
</footer>
APIS;