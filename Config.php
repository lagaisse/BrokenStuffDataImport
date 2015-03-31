<?php


/**
* Classe statique de configuration du réseau de transport sncf
*/
class Config 
{
	
	static $_network_back=array(
								'reseau'   => ['lib' => 'RESEAU'     , 'path' => '000000000' , 'cpt' => '004'],
								'rer'      => ['lib' => 'RER'        , 'path' => '001000000' , 'cpt' => '005'],
								'a'        => ['lib' => 'RER A'      , 'path' => '001001000' , 'cpt' => '000'],
								'b'        => ['lib' => 'RER B'      , 'path' => '001002000' , 'cpt' => '000'],
								'c'        => ['lib' => 'RER C'      , 'path' => '001003000' , 'cpt' => '000'],
								'd'        => ['lib' => 'RER D'      , 'path' => '001004000' , 'cpt' => '000'],
								'e'        => ['lib' => 'RER E'      , 'path' => '001005000' , 'cpt' => '000'],
								'train'    => ['lib' => 'Transilien' , 'path' => '002000000' , 'cpt' => '008'],
								'h'        => ['lib' => 'Ligne H'    , 'path' => '002001000' , 'cpt' => '000'],
								'j'        => ['lib' => 'Ligne J'    , 'path' => '002002000' , 'cpt' => '000'],
								'k'        => ['lib' => 'Ligne K'    , 'path' => '002003000' , 'cpt' => '000'],
								'l'        => ['lib' => 'Ligne L'    , 'path' => '002004000' , 'cpt' => '000'],
								'n'        => ['lib' => 'Ligne N'    , 'path' => '002005000' , 'cpt' => '000'],
								'p'        => ['lib' => 'Ligne P'    , 'path' => '002006000' , 'cpt' => '000'],
								'r'        => ['lib' => 'Ligne R'    , 'path' => '002007000' , 'cpt' => '000'],
								'u'        => ['lib' => 'Ligne U'    , 'path' => '002008000' , 'cpt' => '000'],
								'tram'     => ['lib' => 'Tram'       , 'path' => '003000000' , 'cpt' => '001'],
								't4'       => ['lib' => 'T4'         , 'path' => '003001000' , 'cpt' => '000'],
								'resx ter' => ['lib' => 'TER'        , 'path' => '004000000' , 'cpt' => '001'],
								'ter'      => ['lib' => 'trains TER' , 'path' => '004001000' , 'cpt' => '000']);
	static $_network = null;

	static $_exception = array("a",
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
								"ter",
								"bus",
								"rer");
	static $_grp_sze = 3;

	static $_ignored_id=array();

	static protected $_savepath="./config.json";

	static $_fileoffset = 1;
	static $_filepadding = 3;

	static $_filedir = "../BrokenStuff/application/migrations/";

	static $_filename = "location_data";
	static $_filenameext = ".php";

	static $_callperfile=30;

    public static function getNextPath($key)
	{
		//echo $key."\n";
		$path=str_split(self::$_network[$key]['path'],self::$_grp_sze);
		$_grp=count($path);
		$lvl=0;
		foreach ($path as $cle => $valeur) {
			if ($valeur <> "000") $lvl++;
		}
		self::$_network[$key]['cpt'] = str_pad(dechex(hexdec("0x".self::$_network[$key]['cpt'])+1), self::$_grp_sze, "0", STR_PAD_LEFT);
		return str_pad(substr(self::$_network[$key]['path'],0,$lvl*self::$_grp_sze).self::$_network[$key]['cpt'],$_grp*self::$_grp_sze,"0", STR_PAD_RIGHT);
	}

	public static function addNetwork($id, $name) //metro
	{
		if (!array_key_exists($id, self::$_network))
		{
			self::$_network[$id]= ['lib' => $name        , 'path' => self::getNextPath('reseau') , 'cpt' => '000'];
		}
	}
	public static function addLine($id, $name, $network)
	{
		
		if (!array_key_exists($network, self::$_network))
		{
			//echo "reseau ". $network ." non présent => Ajout"."\n";
			self::addNetwork($network, ucwords($network));
		}
		if (!array_key_exists($id, self::$_network))
		{
			self::$_network[$id]= ['lib' => ucwords($name)  , 'path' => self::getNextPath($network) , 'cpt' => '000'];
		}
	}


	public static function getNextFilePath($prefix='')
	{
		$offset= str_pad(++self::$_fileoffset, self::$_filepadding, "0", STR_PAD_LEFT);
		return self::$_filedir.$offset.'_'.$prefix.self::$_filename.'_'.$offset.self::$_filenameext;
	}

	public static function getFilename()
	{
		$offset= str_pad(self::$_fileoffset, self::$_filepadding, "0", STR_PAD_LEFT);
		return $offset.'_'.self::$_filename.'_'.$offset.self::$_filenameext;
	}


    public static function save()
	{
		$f=fopen(self::$_savepath, "wb");
		fwrite($f, json_encode(array('network'=>self::$_network,'filenb'=>self::$_fileoffset),JSON_PRETTY_PRINT));
		fclose($f);
		echo "config saved \n";

	}

	public static function restore()
	{
		if (!file_exists(self::$_savepath)) {
			self::reset();
		}
		else {
			$aux = json_decode(file_get_contents(self::$_savepath),true);
			self::$_network = $aux['network'];
			self::$_fileoffset = $aux['filenb'];
			echo "config restored \n";
		}
	}

	public static function reset()
	{
		self::$_network = self::$_network_back;
		echo "config reset \n";
		self::save();
	}



	public static function create_header($prefix='') {
		$filename=$prefix.ucfirst(Config::$_filename).'_'.str_pad(self::$_fileoffset, self::$_filepadding, "0", STR_PAD_LEFT);
		$header=<<<HEAD
<?php defined('BASEPATH') OR exit('No direct script access allowed');
class Migration_{$filename} extends CI_Migration {
    public function up(){
        \$ret=true;

HEAD;
		return $header;
	}

	public static function create_footer() {
		$footer=<<<FOOT

		return \$ret;
    }

    public function down(){

    }
}
FOOT;
		return $footer;
	}
}
