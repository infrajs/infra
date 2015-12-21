<?php

use infrajs\access\Access;
use infrajs\event\Event;
use infrajs\ans\Ans;
use infrajs\path\Path;
use infrajs\each\Each;
use infrajs\config\Config;

if (!is_file('vendor/autoload.php')) {
	chdir('../../../../');
	require_once('vendor/autoload.php');
}

$ans = array();

$ans['title'] = 'Проверка наличия папок';

$conf = Config::get();

if ($conf['mem']['type'] == 'fs') {
	$dirs = Config::get('path');

	if (!Path::theme($dirs['cache'])) {
		return Ans::err($ans, 'Нет папки '.$dirs['cache']);
	}
	if (!Path::theme($dirs['data'])) {
		return Ans::err($ans, 'Нет папки '.$dirs['data']);
	}

	return Ans::ret($ans, 'Обязательные папки есть');
} else {
	return Ans::ret($ans, 'Используется memcache. Папки не создаются.');
}
