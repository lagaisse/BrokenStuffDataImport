<?php

/**
 * Classe pour gérer les traitements en csv
 */
 abstract class CsvParser {

  /**
   * Liste des colonnes du fichier csv
   */
  protected $_columns = array();

  /**
   * Liste des colonnes des formats de colonnes
   */
  protected $_columns_format = array();

  /**
   * Est-ce que l'on doit ignorer la première ligne (si elle contient le nom des colonnes par exemple)
   */
  protected $_ignoreFirstLine = true;

  /**
   * Le séparateur pour les colonnes ';' par défaut, mais ça peut être ','
   */
  protected $_separator = ';';
 
 /**
   * Le caractère d'encadrement des chaînes de caractères, par défaut "
   */
  protected $_string_enclosure = '"';

 /**
   * Le caractère d'encadrement des chaînes de caractères, par défaut "
   */
  protected $_escape_car = '\\';


  protected $_line_number = 0;

  public function parse($filename) {
    if(!file_exists($filename)) {
      throw new Exception('File not found: '.$filename);
    }

    $this->preParse();

    $f = fopen($filename,'rb');
    if($f == null){
      throw new Exception('Cound not open '.$filename.' for read');
    }
    
    $this->_line_number = 0;    
    if($this->_ignoreFirstLine) {
      fgets($f,4096);
      $this->_line_number++;
    }


    while($line = fgets($f,4096)) {
      $line = $this->clean($line);
      $line = str_getcsv($line,$this->_separator,$this->_string_enclosure, $this->_escape_car); //print_r($line);
      if(count($line) != count($this->_columns)) continue;
      $line = array_combine($this->_columns,$line);
      array_walk($line,array($this,'clean'));
      $this->parseLine($line);
      $this->_line_number++; 
    }

    $this->postParse();
  }

  /**
   * Méthode qui est executée pour chaque ligne du fichier csv
   * @param $line array La ligne a traiter
   * @param $i Le numéro de la ligne
   */
  abstract public function parseLine($line);

  /**
   * Méthode executer avant que le fichier ne soit parser
   */
  public function preParse() {

  }

  /**
   * Méthode executer après que tous le fichier ai été parsé
   */
  public function postParse() {

  }

  /**
   * Méthode pour nettoyer le fichier csv
   */
  public function clean($value) {
    return trim(stripslashes($value));
  }
}


?>