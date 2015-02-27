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
	protected $_prefix="SNCFU_";
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

		$coord  = array_map('trim',explode(",", $line['coord_gps_wgs84']));

		$query  = "UPDATE \".\$this->db->dbprefix('location').\" ";
		$query .= "SET lo_geoloc_lat  =". $coord[0] .", ";
		$query .= "lo_geoloc_long =". $coord[1] ." ";
		$query .= "WHERE lo_code='SNCF:". $line['code_uic'] ."'";
		fwrite($this->_fileoutput, "        \$ret=\$ret && \$this->db->simple_query(\"".$query."\");\n");
		$this->_cpt--;
		
	}
	public function clean($value) {
		return trim(stripslashes($value));
		//return trim(stripslashes(mb_convert_encoding($value, "pass", "auto")));
	}
}

?>