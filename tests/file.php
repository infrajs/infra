<?php
namespace infrajs\infra;
use infrajs\access\Access;
use infrajs\event\Event;
use infrajs\ans\Ans;
use infrajs\load\Load;

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
