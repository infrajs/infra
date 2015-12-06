<?php

namespace infrajs\infra;
use infrajs\once\Once;
use infrajs\infra\Load;
use infrajs\path\Path;

class Config {
	private static function add(&$conf, $dir){
		$src = $dir.'.infra.json';
		if (!is_file($src)) return;
		$d = file_get_contents($src);
		$d = Load::json_decode($d,true);
		if (is_array($d)) {
			foreach ($d as $k => &$v) {
				if (@!is_array($conf[$k])) {
					$conf[$k] = array();
				}
				if (isset($d[$k]['pub']) && isset($conf[$k]['pub'])) {
					$d[$k]['pub'] = array_unique(array_merge($d[$k]['pub'], $conf[$k]['pub']));
				}
				if (is_array($v)) {
					foreach ($v as $kk => $vv) {
						$conf[$k][$kk] = $vv;
					}
				} else {
					$conf[$k] = $v;
				}
			}
			if(!empty($d['external'])){
				return array('dir'=>$dir, 'external'=>$d['external']);
			}
		}
	}
	/**
	 * В конфиге обрабатывается параметр external, external применяется во вторую интерация и заменяет имеющийся значения. Также добавляется в dir.search.
	 *
	 **/
	public static function addVendor(&$conf, $src) {
		if (is_dir($src)) {
			$list = scandir($src);//Неизвестный порядок плагинов и порядок применения конфигов
			foreach ($list as $name) {
				if ($name[0] == '.' || !is_dir($src.$name)) continue;
				Config::add($conf, $src.$name.'/');
			}
		}
		//Корень искомой дирректории
		Config::add($conf, $src);
	}
	public static function &get ($plugin = false)
	{
		
		$conf=Once::exec('Config::get', function (){
			$conf = array();
			$dirs = Config::dirs();
			$dirs['search'] = array_reverse($dirs['search']);
			foreach ($dirs['search'] as $src) {
				Config::addVendor($conf, $src);
			}
			$dirs=&Config::dirs();
			/*
				Обработка свойства external
				external: "catalog" включает поиск в папке catalog файлов для Зависимости catalog
				Данные записываются в $dirs
				external:{
					catalog:[path/to/"catalog"/]
				}
			*/
			foreach ($conf as $plugin=>$pdata) { //Сейчас уже неизвестно кто добавил этот параметр в конфиг или кто подменил
				if (empty($pdata['external'])) continue;
				if (!$dirs['external'][$pdata['external']]) $dirs['external'][$pdata['external']]=array(); //Создали dirs.external.catalog=[]
				$plug=$plugin.'/'.$pdata['external'].'/'; //Например cart/catalog/

				foreach ($dirs['search'] as $src) { //Ищим эту указанную в external папку, определяем path/to/
					if (!is_dir($src.$plug)) continue;
					// Нашли vendor/infrajs/cart/catalog/ или data/cart/catalog/
					$dirs['external'][$pdata['external']][]=$src.$plugin.'/'; //Сохраняем без слова catalog
					Config::add($conf, $src.$plug); //Этот конфиг важней того который в data
					
				}
			}
			Config::addVendor($conf, './'); //В корне и в data не может быть external
			Config::addVendor($conf, $dirs['data']);
			$conf['path']=array_merge($conf['path'], $dirs);
			return $conf;
		});
		if ($plugin) return $conf[$plugin];
		else return $conf;
		
	}
	public static function &pub ($plugin = false) {
		$conf=Config::get();
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
	public static function &dirs()
	{
		//Нельзя тут использовать Path::theme потому что Path::theme сам использует dirs
		return Once::exec('Config::dirs', function &() {
			
			$dirs=file_get_contents('vendor/infrajs/path/.infra.json');
			$dirs=Load::json_decode($dirs, true);
			$dirs=$dirs['path'];

			$dirs['search']=array(
				'vendor/infrajs/',
				'vendor/components/',
				'bower_components/'
			);
			$dirs['external']=array();
			//for example "catalog"=>array("vendor/infrajs/cards/")

			if (is_file('.infra.json')) {
				$conf=file_get_contents('.infra.json');
				$conf=Load::json_decode($conf, true);
				if (!empty($conf['path'])) {
					$dirs=array_merge($dirs, $conf['path']);
				}
			}
			return $dirs;
		});
	}
	public static function runPlugins($callback)
	{
		$plugins=Once::exec('Config::runPlugins', function () {
			$plugins=array();
			$dirs = Config::dirs();
			for ($i = 0, $il = sizeof($dirs['search']); $i < $il; ++$i) {
				$dir = $dirs['search'][$i];
				$list = scandir($dir);
				for ($j = 0, $jl = sizeof($list); $j < $jl; ++$j) {
					$plugin = $list[$j];
					if ($plugin{0} == '.') continue;
					if (!is_dir($dir.$plugin)) continue;
					$plugins[] = array('dir' => $dir, 'name' => $plugin);
				}
			}
			return $plugins;
		});
		
		for ($i = 0, $il = sizeof($plugins); $i < $il; ++$i) {
			$pl = $plugins[$i];
			$r = $callback($pl['dir'].$pl['name'].'/', $pl['name']);
			if (!is_null($r)) return $r;
		}
	}
	public static function initRequire()
	{
		Once::exec('Config::initRequire', function() {
			$conf=Config::get();
			foreach ($conf as $name => $plugin) {
				if (empty($plugin['require'])) continue;

				if (!Path::theme($plugin['require'])) {
					echo '<pre>';
					echo 'Plugin "'.$name.'" require error. File not found'."\n";
					print_r($plugin);
					continue;
				}
				Load::req($plugin['require']);
			}
		});
	}	
}
