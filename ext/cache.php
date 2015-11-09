<?php

/*
(c) All right reserved. http://itlife-studio.ru

infra_cache(true,'somefn',array($arg1,$arg2)); - выполняется всегда
infra_cache(true,'somefn',array($arg1,$arg2),$data); - Установка нового значения в кэше 
*/

function infra_cache_fullrmdir($delfile, $ischild = true)
{
	$delfile = infra_theme($delfile);
	if (file_exists($delfile)) {		
		if (is_dir($delfile)) {
			$handle = opendir($delfile);
			while ($filename = readdir($handle)) {
				if ($filename != '.' && $filename != '..') {
					$src = $delfile.$filename;
					if (is_dir($src)) $src .= '/';
					$r=infra_cache_fullrmdir($src, true);
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
function infra_install($readmin = null, $flush = null)
{
	//Изменился config...
	if (!$readmin&&!$flush) {
		//проверка только если была авторизация админа
		$cmd5 = infra_mem_get('configmd5');
		//infra_admin_isTime($cmd5['time'], function () use (&$flush, &$rmd5, $cmd5) {
		$rmd5 = array('time' => time());
		$rmd5['result'] = md5(serialize(infra_config()));
		if (!$cmd5 || $rmd5['result'] != $cmd5['result']) {
			$readmin = true;
		}
		//});
	}

	//Файл infra/data/update
	
	if (!$flush) {
		$dirs = infra_dirs();
		$file = infra_theme($dirs['data'].'update');
		if ($file) {
			$r = @unlink($file);//Файл появляется после заливки из svn и если с транка залить без проверки на продакшин, то файл зальётся и на продакшин
			if (!$r) {
				header('Infra-Update: Error');
			} else {
				$flush=true;
			}
		}
	}

	//Папка cache.. если fs
	if (!$flush) {
		$conf = infra_config();
		if ($conf['infra']['cache'] == 'fs') {
			$dirs = infra_dirs();
			//Чтобы лишний раз не запускать install
			//Возможна ситуация что папки cache в принципе нет и на диск ничего не записывается
			if (!is_dir($dirs['cache'])) {
				$flush = true;
			}
		}
	}
	if (!$flush&&!$readmin) return;

	if ($flush) {
		infra_mem_flush();
		$dirs = infra_dirs();
		$r = @infra_cache_fullrmdir($dirs['cache']);
		header('Infra-Update:'.($r ? 'flush' : 'Fail'));
	}else if($readmin){
		$r=infra_admin_time_set();
		header('Infra-Update:'.($r ? 'readmin' : 'Fail'));
	}
	include(infra_theme('*infra/install.php'));
	if (empty($rmd5)) {
		$rmd5 = array('time' => time());
		$rmd5['result'] = md5(serialize(infra_config()));
	}
	infra_mem_set('configmd5', $rmd5);
}

function infra_cache_is()
{
	//Возможны только значения no-store и no-cache
	$list = headers_list();
	foreach ($list as $name) {
		$r = explode(':', $name, 2);
		if ($r[0] == 'Cache-Control') {
			return (strpos($r[1], 'no-store') === false);
		}
	}

	return true;
}
/**
 * no-store - вообще не сохранять кэш.
 */
function infra_cache_no()
{
	header('Cache-Control: no-store'); //Браузер всегда спрашивает об изменениях. Кэш слоя не делается.
}
/**
 * no-store - кэш сохранять но каждый раз спрашивать не поменялось ли чего.
 */
function infra_cache_yes()
{
	header('Cache-Control: no-cache'); //По умолчанию. Браузер должен всегда спрашивать об изменениях. Кэш слоёв делается.
}
function infra_cache_check($call)
{
	$cache = infra_cache_is();
	if (!$cache) {
		//По умолчанию готовы кэшировать
		infra_cache_yes();
	}
	$call();
	//Смотрим есть ли возражения
	$cache2 = infra_cache_is();

	if (!$cache && $cache2) {
		//Возражений нет и функция вернёт это в $cache2..
		//но уже была установка что кэш не делать... возвращем эту установку для вообще скрипта
		infra_cache_no();
	}

	return $cache2;
}
function infra_cache_clear($name, $args = array())
{
	$name = 'infra_admin_cache_'.$name;
	$hash = infra_once_clear($name, $args);
	infra_mem_delete($hash);

	return $hash;
}
function infra_cache($conds, $name, $fn, $args = array(), $re = false)
{
	if ($re) {
		infra_debug(true);
	}
	$name = 'infra_admin_cache_'.$name;
	return infra_once($name, function ($args, $r, $hash) use ($name, $fn, $conds, $re) {
		$data = infra_mem_get($hash);

		if (!$data) {
			$data = array('time' => 0);
		}
		$execute = infra_admin_isTime($data['time'], function ($cache_time) use ($conds) {
			if (!sizeof($conds)) {
				return false;//Если нет conds кэш навсегда и develop не поможет
			}

			$max_time = 1;
			for ($i = 0, $l = sizeof($conds); $i < $l; ++$i) {
				$mark = $conds[$i];
				$mark = infra_theme($mark);
				if (!$mark) {
					continue;
				}
				$m = filemtime($mark);
				if ($m > $max_time) {
					$max_time = $m;
				}
				if (!is_dir($mark)) {
					continue;
				}
				foreach (glob($mark.'*.*') as $filename) {
					$m = filemtime($filename);
					if ($m > $max_time) {
						$max_time = $m;
					}
				}
			}

			return $max_time > $cache_time;
		}, $re);

		if ($execute) {
			$cache = infra_cache_check(function () use (&$data, $fn, $args, $re) {
				$data['result'] = call_user_func_array($fn, array_merge($args, array($re)));
			});
			if ($cache) {
				$data['time'] = time();
				infra_mem_set($hash, $data);
			} else {
				infra_mem_delete($hash);
			}
		}

		return $data['result'];
	}, array($args));
}
