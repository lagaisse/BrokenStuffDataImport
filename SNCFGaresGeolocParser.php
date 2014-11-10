<?php
require_once("./Config.php");
require_once("./CsvParser.php");
/**
* classe de parsing de la geolocalisation des gares sncf. Sortie formatter en update sql
**/

class SNCFGaresGeolocParser extends CsvParser{
	protected $_columns = array("code_uic",
								"libelle_point_d_arret",
								"libelle",
								"libelle_stif_info_voyageurs",
								"libelle_sms_gare",
								"nom_gare",
								"adresse",
								"code_insee_commune",
								"commune",
								"x_lambert_ii_etendu",
								"y_lambert_ii_etendu",
								"coord_gps_wgs84",
								"zone_navigo",
								"gare_non_sncf");


 	protected $_escape_car = '\\';
	protected $_string_enclosure = '"';
	protected $_separator=";";
	protected $_fileoutput=null;
	protected $_tofilename="D:/www/BrokenStuff/sql/005_SNCFUpdateStationsLocation.sql";
		 
	public function preParse() {
		$this->_fileoutput=fopen($this->_tofilename, "wb");
		fwrite($this->_fileoutput, "USE `brokenstuff`;\n");
	}
	public function postParse() {
		fwrite($this->_fileoutput, "\ncommit;");	
		fclose($this->_fileoutput);
	}
	public function parseLine( $line) {
		
		$coord  = array_map('trim',explode(",", $line['coord_gps_wgs84']));

		$query  = "UPDATE location ";
		$query .= "SET lo_geoloc_lat  =". $coord[0] .", ";
		$query .= "lo_geoloc_long =". $coord[1] ." ";
		$query .= "WHERE lo_code='SNCF:". $line['code_uic'] ."';\n";
		fwrite($this->_fileoutput, $query);

		//print_r($line);
		
	}
	public function clean($value) {
		return trim(stripslashes($value));
		//return trim(stripslashes(mb_convert_encoding($value, "pass", "auto")));
	}
}

?>