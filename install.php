<?php
namespace infrajs\infra;
use infrajs\infra\Config;
use infrajs\mem\Mem;
use infrajs\infra\Load;
use infrajs\path\Path;
use infrajs\infra\Access;

class Install {
	/*
		Устаовка бывает. 
		1 $fluh Было посещение админа. Кэш остаётся, но сбрасывается метка из-за которой некоторые кэши будут обновляться по мере обращения к ним
		2 $readmin Было обновление сайта. Папка cache или memcache очищаются
	*/
	public static function initCheck($readmin = null, $flush = null)
	{
		

		//Папка cache.. если fs
		if (!$flush) {
			$conf = Config::get('path');
			if ($conf['fs']) {
				//Чтобы лишний раз не запускать install
				//Возможна ситуация что папки cache в принципе нет и на диск ничего не записывается
				if (!is_dir($conf['cache'])) {
					$flush = true;
				}
			}
		}
		//Изменился config...
		if (!$readmin&&!$flush) {
			//проверка только если была авторизация админа
			$cmd5 = Mem::get('configmd5');
			//Access::adminIsTime($cmd5['time'], function () use (&$flush, &$rmd5, $cmd5) {
			$rmd5 = array('time' => time());
			$rmd5['result'] = md5(serialize(Config::get()));
			if (!$cmd5 || $rmd5['result'] != $cmd5['result']) {
				$readmin = true;
			}
			//});
		}

		//Файл infra/data/update
		if (!$flush) {
			$dirs = Config::dirs();
			$file = Path::theme($dirs['data'].'update');
			if ($file) {
				$r = @unlink($file);//Файл появляется после заливки из svn и если с транка залить без проверки на продакшин, то файл зальётся и на продакшин
				if (!$r) {
					header('Infra-Update: Error');
				} else {
					$flush=true;
				}
			}
		}

		if (!$flush&&!$readmin) return;
		return;
		if ($flush) {
			Mem::flush();
			$dirs = Config::dirs();
			$r = static::fullrmdir($dirs['cache']);
			header('Infra-Update:'.($r ? 'flush' : 'Fail'));
		}else if($readmin){
			$r=Access::adminSetTime();
			header('Infra-Update:'.($r ? 'readmin' : 'Fail'));
		}

		static::install();
		if (empty($rmd5)) {
			$rmd5 = array('time' => time());
			$rmd5['result'] = md5(serialize(Config::get()));
		}
		Mem::set('configmd5', $rmd5);
	}
	public static function install(){

		$conf = Config::get('path');

		if (!is_file($path['data'].'.infra.json')) {
			$pass = substr(md5(time()), 2, 8);
			//Режим без записи на жёсткий диск
			@file_put_contents($path['data'].'.infra.json', '{"infra":{"admin":{"login":"admin","password":"'.$pass.'"}}');
		}
		
	
		Config::runPlugins(function ($dir) {
			if (realpath($dir) == realpath(__DIR__)) return;
			if (!is_file($dir.'install.php')) return;
			require_once $dir.'install.php';
		});
		$t = Access::adminTime();
		if (!$t) Access::adminSetTime(time());//Нужно чтобы был, а то как-будто админ постоянно
	}
	public static function fullrmdir($delfile, $ischild = true)
	{
		$delfile = Path::theme($delfile);
		if (file_exists($delfile)) {		
			if (is_dir($delfile)) {
				$handle = opendir($delfile);
				while ($filename = readdir($handle)) {
					if ($filename != '.' && $filename != '..') {
						$src = $delfile.$filename;
						if (is_dir($src)) $src .= '/';
						$r=static::fullrmdir($src, true);
						if(!$r)return false;
					}
				}
				closedir($handle);
				if ($ischild) {
					return rmdir($delfile);
				}

				return true;
			} else {
				return unlink($delfile);
			}
		}
		return true;
	}
	public static function checkParentDir($name)
	{
		$dirs = Config::dirs();
		$test = explode('/', $dirs[$name]);
		$test = array_slice($test, 0, sizeof($test) - 2);
		if (!sizeof($test)) {
			return true;
		}
		$test = implode('/', $test).'/';
		if (!is_dir($test)) {
			die('Not Found folder '.$test.' for '.$name.'/');
		}
	}
}