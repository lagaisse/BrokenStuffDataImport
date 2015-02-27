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
	protected $_prefix="SNCF_";
	protected $_cpt =100;

	public function preParse() {
		$this->_cpt = Config::$_callperfile;
		$this->_columns=array_merge($this->_columns1, $this->_columns2);
	}
	public function postParse() {
		fwrite($this->_fileoutput, Config::create_footer());
		fclose($this->_fileoutput);
	}
	public function parseLine( $line) {
		//array_map('mysql_escape_string',$line);

		// pour chaque ligne, vérifier que l'on est un multiple de 10
		//fermer alors le fichier précédent et en ouvrir un nouveau
		//incrémenter un compteur qui serait bien dans le fichier de config....
		//c'est tout je crois....
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
		$firstline=true;
		foreach ($line as $key => $value) {
			if (in_array($key, $this->_columns2) && $value==1) {
				$query .= ($firstline ? "":",");$firstline=false;
				$query .= "('SNCF:". $line['code_uic'] ."',";
				$query .= "'". mysql_escape_string($line['libelle_point_arret'] )."',";
				$query .= "'". Config::getNextPath($key)."')";
				$this->_cpt--; //can be negative
			}
		}
		fwrite($this->_fileoutput, "        \$ret=\$ret && \$this->db->simple_query(\"insert into \".\$this->db->dbprefix('location').\" (lo_code,lo_name,lo_path) VALUES ".$query."\");\n");
	}
	public function clean($value) {
		return trim(stripslashes($value));
		//return trim(stripslashes(mb_convert_encoding($value, "pass", "auto")));
	}
}