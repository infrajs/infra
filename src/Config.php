<?php
namespace infrajs\infra;
use infrajs\mem\Mem;
use infrajs\load\Load;
use infrajs\path\Path;
use infrajs\once\Once;
use infrajs\access\Access;
use infrajs\nostore\Nostore;

class Config {
	public static $conf=array();
	public static $exec=false;
	public static function init(){
		Config::load('.infra.json');
		Config::load('~.infra.json');
		Config::get('load');
		Config::get('once');
		Config::get('path');

		spl_autoload_register(function($class_name){
			if(Config::$exec) return;
			$p=explode('\\',$class_name);
			if(sizeof($p)<3) return;
			$name=$p[1];
			if(!Path::theme('-'.$name.'/')) return;
			Config::$exec=true;
			spl_autoload_call($class_name);
			Config::$exec=false;
			static::get($name);			
		}, true, true);
		set_error_handler(function(){ //bugfix
			ini_set('display_errors',true);
		});
		
		

		
	}
	public static function get($name)
	{
		Config::load('-'.$name.'/.infra.json', $name);
		return Config::$conf[$name];
	}
	public static function reqsrc($src)
	{
		Each::exec($src, function ($src){
			Path::req($src);
		});
	}
	public static function load($src, $name = null)
	{
		Once::exec('Config::load::'.$src, function () use ($src, $name) {
			
			$path = Path::theme($src);
			if (!$path) {
				return;
				//if(!$name) return;
				//echo '<pre>';
				//throw new \Exception('Конфиг не найден '.$src);
			}
			$d=file_get_contents($path);

			$d=Load::json_decode($d);
			if ($name) {
				Config::accept($name, $d);
			} else {
				foreach ($d as $k => &$v) {
					Config::accept($k, $v);
				}
			}
		});
	}
	public static function accept($name, $v)
	{
		$conf=&Config::$conf;
		if (empty($conf[$name])) $conf[$name] = array();
		foreach ($v as $kk => $vv) {
			if (isset($conf[$name][$kk])) continue; //То что уже есть в конфиге круче вновь прибывшего
			if ($kk == 'require') {
				static::reqsrc('-'.$name.'/'.$vv);
			}else if ($kk == 'conf') {
				$conf[$name]=array_merge($vv::$conf, $conf[$name]);
				$vv::$conf=&$conf[$name];
			} else {
				$conf[$name][$kk] = $vv;
			}
		}
	}
	public static function &pub ($plugin = false) {
		//$conf=Config::get();
		foreach ($conf as $i => $part) {
			$pub = @$part['pub'];
			if (is_array($pub)) {
				foreach ($part as $name => $val) {
					if (!in_array($name, $pub)) {
						unset($conf[$i][$name]);
					}
				}
			} else {
				unset($conf[$i]);
			}
		}
		if ($plugin) return $conf[$plugin];
		else return $conf;
	}
}