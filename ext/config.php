<?php

//Copyright 2008-2013 http://itlife-studio.ru
/*
	infra_config
*/

if (DIRECTORY_SEPARATOR == '/') {
	function infra_realpath($dir)
	{
		return realpath($dir);
	}
	function infra_getcwd()
	{
		return getcwd();
	}
} else {
	function infra_realpath($dir)
	{
		$dir = realpath($dir);

		return str_replace(DIRECTORY_SEPARATOR, '/', $dir);
	}
	function infra_getcwd()
	{
		$dir = getcwd();

		return str_replace(DIRECTORY_SEPARATOR, '/', $dir);
	}
}
/* Проверка что запущенный php файл находится в корне сайта рядом с vendor
	//Корень сайта относительно этого файла
	$vendorroot = infra_realpath(__DIR__.'/../../../../');//AВ до vendor
	//Корень сайта определёный по рабочей дирректории
	$siteroot = infra_getcwd();
	//Определёный корень сайта двумя способами сравниваем
	//Если результат разный значит система запущена не из той папки где находится vendor с текущим кодом
	if ($siteroot != $vendorroot) {
		die('Start infrajs only from site root - directory which have subfolder vendor with itlife/infra/');
	}
*/
function infra_pluginRun($callback)
{
	$dirs = infra_dirs();
	global $infra_plugins;
	if (empty($infra_plugins)) {
		$infra_plugins = array();
		for ($i = 0, $il = sizeof($dirs['search']); $i < $il; ++$i) {
			$dir = $dirs['search'][$i];
			$list = scandir($dir);
			for ($j = 0, $jl = sizeof($list); $j < $jl; ++$j) {
				$plugin = $list[$j];
				if ($plugin{0} == '.') continue;
				if (!is_dir($dir.$plugin)) continue;
				$infra_plugins[] = array('dir' => $dir, 'name' => $plugin);
			}
		}
	}
	for ($i = 0, $il = sizeof($infra_plugins); $i < $il; ++$i) {
		$pl = $infra_plugins[$i];
		$r = $callback($pl['dir'].$pl['name'].'/', $pl['name']);
		if (!is_null($r)) {
			return $r;
		}
	}
}
function &infra_dirs()
{
	global $infra_dirs;
	if (!empty($infra_dirs)) {
		return $infra_dirs;
	}

	$infra_dirs = array(
		'cache' => 'cache/',
		'data' => 'data/',
		'search' => array(
			'data/',
			'./',
			'vendor/infrajs/'
		)
	);

	if(is_file('.infra.json')){
		$conf=file_get_contents('.infra.json');
		$conf=json_decode($conf, true);
		if(!empty($conf['dirs'])){
			$infra_dirs=array_merge($infra_dirs, $conf['dirs']);
		}
	}
	return $infra_dirs;
}
function infra_test_silent()
{
	if (infra_debug_silent()) {
		return true;
	}
	$conf = infra_config();
	$ips = $conf['infra']['test'];
	if (is_array($ips)) {
		$is = in_array($_SERVER['REMOTE_ADDR'], $ips);
	} elseif (is_string($ips)) {
		$is = ($_SERVER['REMOTE_ADDR'] == $ips);
	} else {
		$is = !!$ips;
	}

	return $is;
}
function infra_debug_silent()
{
	if (infra_admin_silent()) {
		return true;
	}
	$conf = infra_config();
	$ips = $conf['infra']['debug'];
	if (is_array($ips)) {
		$is = in_array($_SERVER['REMOTE_ADDR'], $ips);
	} elseif (is_string($ips)) {
		$is = ($_SERVER['REMOTE_ADDR'] == $ips);
	} else {
		$is = !!$ips;
	}

	return $is;
}
function infra_test($r = false)
{
	infra_cache_no();
	$is = infra_test_silent();
	if ($r) {
		if (!$is) {
			header('HTTP/1.0 403 Forbidden');
			die('{"msg":"Required config.infra.test:['.$_SERVER['REMOTE_ADDR'].']"}');
		}
	} else {
		return $is;
	}
}

function infra_debug($r = false)
{
	infra_cache_no();
	$is = infra_debug_silent();
	if ($is) {
		infra_admin_time_set();
	}
	if ($r) {
		if (!$is) {
			header('HTTP/1.0 403 Forbidden');
			die('{"msg":"Required config.infra.debug:['.$_SERVER['REMOTE_ADDR'].']"}');
		}
	} else {
		return $is;
	}
}
function infra_config_add(&$data, $dir){
	$src=$dir.'.infra.json';
	if (!is_file($src)) {
		$src=$dir.'.config.json';
		if (!is_file($src)) {
			return;
		}
	}
	$d = file_get_contents($src);
	$d = infra_json_decode($d);
	if (is_array($d)) {
		foreach ($d as $k => &$v) {
			if (@!is_array($data[$k])) {
				$data[$k] = array();
			}
			if (isset($d[$k]['pub']) && isset($data[$k]['pub'])) {
				$d[$k]['pub'] = array_unique(array_merge($d[$k]['pub'], $data[$k]['pub']));
			}
			if (is_array($v)) {
				foreach ($v as $kk => $vv) {
					$data[$k][$kk] = $vv;
				}
			} else {
				$data[$k] = $v;
			}
		}
		if(!empty($d['external'])){
			return array('dir'=>$dir, 'external'=>$d['external']);
		}
	}
}
function &infra_config($sec = false)
{
	$sec = $sec ? 'secure' : 'unsec';

	global $infra_config;
	if (isset($infra_config)) {
		return $infra_config[$sec];
	}
	$infra_config=array();
	$data = array();
	$dirs = infra_dirs();
	$dirs['search'] = array_reverse($dirs['search']);
	foreach ($dirs['search'] as $src) {
		if (is_dir($src)) {
			$list = scandir($src);//Неизвестный порядок плагинов и порядок применения конфигов
			foreach ($list as $name) {
				if ($name[0] == '.' || !is_dir($src.$name)) continue;
				infra_config_add($data, $src.$name.'/');
			}
		}
		//Корень искомой дирректории
		infra_config_add($data, $src);
	}

	$dirs=&infra_dirs();
	foreach ($data as $plugin=>$pdata) {
		if (empty($pdata['external'])) continue;
		$plug=$plugin.'/'.$pdata['external'].'/';

		foreach ($dirs['search'] as $src) {
			if ($src[0] == '.' || !is_dir($src.$plug)) continue;
			$src=$src.$plug;
			array_unshift($dirs['search'], $src);
			$list = scandir($src);//Неизвестный порядок плагинов и порядок применения конфигов
			foreach ($list as $name) {
				if ($name[0] == '.' || !is_dir($src.$name)) continue;
				infra_config_add($data, $src.$name.'/');
			}
			infra_config_add($data, $src);
			break;
		}
	}
	$infra_config['unsec'] = $data;
	foreach ($data as $i => $part) {
		$pub = @$part['pub'];
		if (is_array($pub)) {
			foreach ($part as $name => $val) {
				if (!in_array($name, $pub)) {
					unset($data[$i][$name]);
				}
			}
		} else {
			unset($data[$i]);
		}
	}

	$infra_config['secure'] = $data;

	return $infra_config[$sec];
}
