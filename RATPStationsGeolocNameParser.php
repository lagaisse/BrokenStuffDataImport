<?php
require_once("./Config.php");
require_once("./CsvParser.php");

/**
* classe de parsing des gares sncf. Sortie formattée en insert sql
**/
class RATPStationsGeolocNameParser extends CsvParser {

	protected $_columns = array("station_id",
								"long",
								"lat",
								"nom",
								"ville",
								"reseau");


	protected $_ignoreFirstLine = false;
 	protected $_escape_car = '\\';
	protected $_string_enclosure = '';
	protected $_separator="#";
	protected $_fileoutput=null;
	protected $_prefix="RATPU_";
	protected $_lignenb =1;
	protected $_cpt =100;

	public function preParse() {
		$this->_cpt = Config::$_callperfile;
	}
	public function postParse() {
		fwrite($this->_fileoutput, Config::create_footer());
		fclose($this->_fileoutput);
	}
	public function parseLine( $line) {
		if ($this->_cpt<=0||$this->_line_number==($this->_ignoreFirstLine ? 1:0)) {
			if ($this->_fileoutput!=null)
			{
				fwrite($this->_fileoutput, Config::create_footer());
				fclose($this->_fileoutput);
			}
			$this->_fileoutput=fopen(Config::getNextFilePath($this->_prefix), "wb");
			$this->_cpt = Config::$_callperfile;
			fwrite($this->_fileoutput, Config::create_header($this->_prefix));
		}



		if(!in_array($line['reseau'], Config::$_exception)) 
		{
			$query  = "UPDATE \".\$this->db->dbprefix('location').\" ";
			$query .= "SET lo_geoloc_lat  =". $line['lat'] .", ";
			$query .= "lo_geoloc_long =". $line['long'] .", ";
			$query .= "lo_name ='". mysql_escape_string($line['nom']) ."' ";
			$query .= "WHERE lo_code='RATP:". $line['station_id'] ."'";
			fwrite($this->_fileoutput, "        \$ret=\$ret && \$this->db->simple_query(\"".$query."\");\n");
			$this->_cpt--;
		}
		
	}
	public function clean($value) {
		return trim(stripslashes($value));
		//return trim(stripslashes(mb_convert_encoding($value, "pass", "auto")));
	}
}


?>