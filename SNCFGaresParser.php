<?php
require_once("./Config.php");
require_once("./CsvParser.php");

/**
* classe de parsing des gares sncf. Sortie formattée en insert sql
**/
class SNCFGaresParser extends CsvParser {

	protected $_columns1 = array("code_uic",
								"libelle_point_arret",
								"train",
								"rer",
								"tram",
								"bus");

	protected $_columns2 = array("a",
								"b",
								"c",
								"d",
								"e",
								"h",
								"j",
								"k",
								"l",
								"n",
								"p",
								"r",
								"u",
								"t4",
								"ter");
	protected $_columns = null;

 	protected $_escape_car = '\\';
	protected $_string_enclosure = '"';
	protected $_separator=";";
	protected $_fileoutput=null;
	protected $_tofilename="D:/www/BrokenStuff/sql/004_SNCFInsertLinesAndStations.sql";

	public function preParse() {
		$this->_columns=array_merge($this->_columns1, $this->_columns2);
		$this->_fileoutput=fopen($this->_tofilename, "wb");
		fwrite($this->_fileoutput, "USE `brokenstuff`;\n");
		fwrite($this->_fileoutput, "insert into location (lo_code,lo_name,lo_path) VALUES\n");
	}
	public function postParse() {
		fwrite($this->_fileoutput, ";\ncommit;");	
		fclose($this->_fileoutput);
	}
	public function parseLine( $line) {
		//array_map('mysql_escape_string',$line);
		$query  = "";


		$query .= (($this->_line_number <= 1) ? "":",\n");
		$firstline=true;
		foreach ($line as $key => $value) {
			if (in_array($key, $this->_columns2) && $value==1) {
				$query .= ($firstline ? "":",");$firstline=false;
				$query .= "('SNCF:". $line['code_uic'] ."',";
				$query .= "'". mysql_escape_string($line['libelle_point_arret'] )."',";
				$query .= "'". Config::getNextPath($key)."')";
			}
		}
		//echo $query;

		fwrite($this->_fileoutput, $query);

		//print_r($line);
		
	}
	public function clean($value) {
		return trim(stripslashes($value));
		//return trim(stripslashes(mb_convert_encoding($value, "pass", "auto")));
	}
}


?>