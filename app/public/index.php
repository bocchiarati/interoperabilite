<?php

$url_api_meteo = "https://www.infoclimat.fr/public-api/gfs/xml?_ll=48.67103,6.15083&_auth=ARsDFFIsBCZRfFtsD3lSe1Q8ADUPeVRzBHgFZgtuAH1UMQNgUTNcPlU5VClSfVZkUn8AYVxmVW0Eb1I2WylSLgFgA25SNwRuUT1bPw83UnlUeAB9DzFUcwR4BWMLYwBhVCkDb1EzXCBVOFQoUmNWZlJnAH9cfFVsBGRSPVs1UjEBZwNkUjIEYVE6WyYPIFJjVGUAZg9mVD4EbwVhCzMAMFQzA2JRMlw5VThUKFJiVmtSZQBpXGtVbwRlUjVbKVIuARsDFFIsBCZRfFtsD3lSe1QyAD4PZA%3D%3D&_c=19f3aa7d766b6ba91191c8be71dd1ab2";
$meteo_content = file_get_contents($url_api_meteo);

file_put_contents("XML/meteo_content.xml", $meteo_content);

$xml_file = simplexml_load_file("XML/meteo_content.xml");
$xsl_file = simplexml_load_file("XSL/meteo.xsl");

$processor = new XSLTProcessor();
$processor->importStylesheet($xsl_file);
echo $processor->transformToXml($xml_file);

