<?php

use infrajs\access\Access;
use infrajs\event\Event;
use infrajs\ans\Ans;
use infrajs\load\Load;
use infrajs\each\Each;
use infrajs\config\Config;

if (!is_file('vendor/autoload.php')) {
	chdir('../../../../');
	require_once('vendor/autoload.php');
}

$ans = array(
	'title' => 'Тест на совпадение названия указанного файла и его путь',
);

$file = Load::nameInfo('*1 file@23.txt');
$src = Load::srcInfo('*1 file@23.txt');

if ($file['id'] != 23 && $src['src'] != '*1 file@23.txt') {
	return Ans::err($ans, 'Такого файла не существует или не правидьно указан путь');
}

return Ans::ret($ans, 'Путь указан правильно, файл найден');
