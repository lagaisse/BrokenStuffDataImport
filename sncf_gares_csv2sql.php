<?php
//!#D:\appweb\xampp\php\php.exe -q

require_once("./SNCFGaresParser.php");
require_once("./SNCFGaresGeolocParser.php");


Config::restore();

$fn2 = "./SNCF/sncf-lignes-par-gares-idf.csv";
$parser2=new SNCFGaresParser();
$parser2->parse($fn2);

$fn = "./SNCF/sncf-gares-et-arrets-transilien-ile-de-france.csv";
$parser=new SNCFGaresGeolocParser();
$parser->parse($fn);



Config::save();




/*
*/



?>