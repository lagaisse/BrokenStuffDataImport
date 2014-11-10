<?php
require_once("./Config.php");
require_once("./CsvParser.php");

/**
* classe de parsing des gares sncf. Sortie formattée en insert sql
**/
class RATPStationsGeolocNameParser extends CsvParser {

	protected $_columns = array("station_id",
								"lat",
								"long",
								"nom",
								"ville",
								"reseau");


	protected $_ignoreFirstLine = false;
 	protected $_escape_car = '\\';
	protected $_string_enclosure = '';
	protected $_separator="#";
	protected $_fileoutput=null;
	protected $_tofilename="D:/www/BrokenStuff/sql/007_RATPUpdateStations.sql";

	public function preParse() {
		$this->_fileoutput=fopen($this->_tofilename, "wb");
		fwrite($this->_fileoutput, "USE `brokenstuff`;\n");		
	}
	public function postParse() {
		fwrite($this->_fileoutput, "\ncommit;");	
		fclose($this->_fileoutput);
	}
	public function parseLine( $line) {

		if(!in_array($line['reseau'], Config::$_exception)) 
		{
			$query  = "UPDATE location ";
			$query .= "SET lo_geoloc_lat  =". $line['lat'] .", ";
			$query .= "lo_geoloc_long =". $line['long'] .", ";
			$query .= "lo_name ='". mysql_escape_string($line['nom']) ."' ";
			$query .= "WHERE lo_code='RATP:". $line['station_id'] ."';\n";
			fwrite($this->_fileoutput, $query);
		}
		
	}
	public function clean($value) {
		return trim(stripslashes($value));
		//return trim(stripslashes(mb_convert_encoding($value, "pass", "auto")));
	}
}


?>