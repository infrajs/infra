<?php

/*
(c) All right reserved. http://itlife-studio.ru

infra_cache(true,'somefn',array($arg1,$arg2)); - выполняется всегда
infra_cache(true,'somefn',array($arg1,$arg2),$data); - Установка нового значения в кэше 
*/

function infra_cache_fullrmdir($delfile, $ischild = true)
{
	//$dirs=infra_dirs();
	$delfile = infra_theme($delfile);
	if (file_exists($delfile)) {
		//chmod($delfile,0777);
		if (is_dir($delfile)) {
			$handle = opendir($delfile);
			while ($filename = readdir($handle)) {
				if ($filename != '.' && $filename != '..') {
					$src = $delfile.$filename;
					if (is_dir($src)) {
						$src .= '/';
					}
					infra_cache_fullrmdir($src, true);
				}
			}
			closedir($handle);
			if ($ischild) {
				rmdir($delfile);
			}

			return;
		} else {
			return unlink($delfile);
		}
	}
}
function infra_install()
{
	$cmd5=infra_mem_get('configmd5');
	$rmd5=md5(serialize(infra_config()));
	if (!$cmd5) {
		$flush=null;
	} else if ($rmd5 != $cmd5) {
		$flush=true;
	}
	$dirs = infra_dirs();
	if (!$flush) {
		$file = infra_theme($dirs['data'].'update');
		if ($file) {
			$r = @unlink($file);//Файл появляется после заливки из svn и если с транка залить без проверки на продакшин, то файл зальётся и на продакшин
			if (!$r) {
				return; //Нет прав на удаление
			}
			$flush=true;
		}
	}
	if (!$flush) {
		if (!is_dir($dirs['cache'])) {
			$conf=infra_config();
			//Чтобы лишний раз не запускать install
			//Возможна ситуация что папки cache в принципе нет и на диск ничего не записывается
			if ($conf['infra']['cache']=='fs') {
				$flush=true;
			}
		}
	}
	if ($flush) {
		infra_mem_flush();
		$r = @infra_cache_fullrmdir($dirs['cache']);
		header('Infra-Update:'.($r ? 'Fail' : 'OK'));
		require_once __DIR__.'/../../infra/install.php';
		infra_mem_set('configmd5', $rmd5);
	}
	
}

function infra_cache_path($name, $args = null)
{
	return 'infra_cache '.$name.' '.infra_hash($args);
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
 * no-store - вообще не сохранять кэш
 */
function infra_cache_no()
{
	header('Cache-Control: no-store'); //Браузер всегда спрашивает об изменениях. Кэш слоя не делается.
}
/**
 * no-store - кэш сохранять но каждый раз спрашивать не поменялось ли чего
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

function infra_cache($conds, $name, $fn, $args = array(), $re = false)
{
	$path = infra_cache_path($name, array($conds, $args));
	$data=infra_mem_get($path);
	if (!$data) {
		$data=array('time'=>0);
	}
	$execute = infra_admin_isTime($data['time'], function ($cache_time) use ($conds) {
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
			$data['time']=time();
			infra_mem_set($path, $data);
		} else {
			infra_mem_delete($path);
		}
	}

	return $data['result'];
}
