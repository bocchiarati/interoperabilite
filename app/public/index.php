<?php

$url_api_meteo = "https://www.infoclimat.fr/public-api/gfs/xml?_ll=48.67103,6.15083&_auth=ARsDFFIsBCZRfFtsD3lSe1Q8ADUPeVRzBHgFZgtuAH1UMQNgUTNcPlU5VClSfVZkUn8AYVxmVW0Eb1I2WylSLgFgA25SNwRuUT1bPw83UnlUeAB9DzFUcwR4BWMLYwBhVCkDb1EzXCBVOFQoUmNWZlJnAH9cfFVsBGRSPVs1UjEBZwNkUjIEYVE6WyYPIFJjVGUAZg9mVD4EbwVhCzMAMFQzA2JRMlw5VThUKFJiVmtSZQBpXGtVbwRlUjVbKVIuARsDFFIsBCZRfFtsD3lSe1QyAD4PZA%3D%3D&_c=19f3aa7d766b6ba91191c8be71dd1ab2";
$meteo_content = file_get_contents($url_api_meteo);

file_put_contents("XML/meteo_content.xml", $meteo_content);


// TODO ENREGISTRER LE XSL Dans app/public/XSL/nom et mettre le chemin dans les doubles quote
$xsl_file = file_get_contents("XSL/????.xsl");

$processor = new XSLTProcessor();
//$processor->importStylesheet($xsl_file);
//$new_xml = $processor->transformToXml($meteo_content);

