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

