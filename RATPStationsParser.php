<?php
require_once("./Config.php");
require_once("./CsvParser.php");

/**
* classe de parsing des gares sncf. Sortie formattée en insert sql
**/
class RATPStationsParser extends CsvParser {

	protected $_columns = array("station_id",
								"ligne",
								"reseau");


	protected $_ignoreFirstLine = false;
 	protected $_escape_car = '\\';
	protected $_string_enclosure = '';
	protected $_separator="#";
	protected $_fileoutput=null;
	protected $_tofilename="D:/www/BrokenStuff/sql/006_RATPInsertLinesAndStations.sql";
	protected $_firstline=true;

	public function preParse() {
		$this->_fileoutput=fopen($this->_tofilename, "wb");
		fwrite($this->_fileoutput, "USE `brokenstuff`;\n");
		fwrite($this->_fileoutput, "insert into location (lo_code,lo_name,lo_path) VALUES\n");
	}
	public function postParse() {
		$query ="";
		//print_r(Config::$_network);
		foreach (Config::$_network as $cle => $valeur) {
			if (trim($valeur['path'],"0")=="") continue;
			$query .= ",\n('". $cle ."',";
			$query .= "'". mysql_escape_string($valeur['lib'])."',";
			$query .= "'". $valeur['path']."')";
		}
		fwrite($this->_fileoutput,$query);

		fwrite($this->_fileoutput, ";\ncommit;");	
		fclose($this->_fileoutput);
	}
	public function parseLine( $line) {
		$query  = "";
		

		//mise en cache des nouvelles lignes à la volée
		//calcul des id et noms de lignes 
		$num_ligne="". strtolower(substr($line['ligne'],0,strpos($line['ligne'], " ")));
		
		switch ($line['reseau']) {
			case 'metro':
				$ligne_id = "M" . $num_ligne ;
				$ligne_name = "Ligne ". $num_ligne;
				break;
			case 'bus':
				$ligne_id = "bus" . $num_ligne ;
				$ligne_name = "Ligne ". $num_ligne;
				break;			
			case 'rer':
				$ligne_id = "" . strtolower($num_ligne);
				$ligne_name = "RER ". strtoupper($num_ligne);
				break;
			default:
				$ligne_id = "" . strtolower($num_ligne);
				$ligne_name = $num_ligne;
				break;
		}
		if(!array_key_exists($ligne_id, Config::$_network)&& !in_array($line['reseau'], Config::$_exception) ) 
		{
			Config::addLine($ligne_id,$ligne_name,$line['reseau']);
		}

		if(!in_array($line['reseau'], Config::$_exception) && !in_array($ligne_id, Config::$_exception)) //do not import stations already provided by sncf (lignes A & B)or bus
		{
			$query .= (($this->_firstline) ? "":",\n"); $this->_firstline = false;
			$query .= "('RATP:". $line['station_id'] ."',";
			$query .= "'NONAME',";
			$query .= "'". Config::getNextPath($ligne_id)."')";
			fwrite($this->_fileoutput, $query);
		}

		//if ($this->_line_number > 2) { die();}
		
	}
	public function clean($value) {
		return trim(stripslashes($value));
		//return trim(stripslashes(mb_convert_encoding($value, "pass", "auto")));
	}
}


?>