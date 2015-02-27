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
	protected $_firstline=true;
	protected $_prefix="RATP_";
	protected $_lignenb =1;
	protected $_cpt =100;

	public function preParse() {
		$this->_cpt = Config::$_callperfile;
	}
	public function postParse() {

		$query ="";
		//print_r(Config::$_network);
		foreach (Config::$_network as $cle => $valeur) {
			if (trim($valeur['path'],"0")=="") continue;

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


			$query = "insert into \".\$this->db->dbprefix('location').\" (lo_code,lo_name,lo_path) VALUES";
			$query .= "('". $cle ."',";
			$query .= "'". mysql_escape_string($valeur['lib'])."',";
			$query .= "'". $valeur['path']."')";
			fwrite($this->_fileoutput,"        \$ret=\$ret && \$this->db->simple_query(\"".$query."\");\n");
			$this->_cpt--;
		}

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
			$query .= "insert into \".\$this->db->dbprefix('location').\" (lo_code,lo_name,lo_path) VALUES";
			$query .= "('RATP:". $line['station_id'] ."',";
			$query .= "'NONAME',";
			$query .= "'". Config::getNextPath($ligne_id)."')";
			fwrite($this->_fileoutput,"        \$ret=\$ret && \$this->db->simple_query(\"".$query."\");\n");
			$this->_cpt--;
		}

		//if ($this->_line_number > 2) { die();}
		
	}
	public function clean($value) {
		return trim(stripslashes($value));
		//return trim(stripslashes(mb_convert_encoding($value, "pass", "auto")));
	}
}


?>