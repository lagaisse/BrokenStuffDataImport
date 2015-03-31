<?php
//!#D:\appweb\xampp\php\php.exe -q

require_once("./RATPStationsParser.php");
require_once("./RATPStationsGeolocNameParser.php");

Config::restore();

$fn = "./RATP/ratp_arret_ligne_01.csv";
$parser=new RATPStationsParser();
$parser->parse($fn);

$fn2 = "./RATP/ratp_arret_graphique_01.csv";
$parser2=new RATPStationsGeolocNameParser();
$parser2->parse($fn2);


Config::save();



?>