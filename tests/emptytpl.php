<?php
namespace infrajs\infra;
use infrajs\access\Access;
use infrajs\event\Event;
use infrajs\ans\Ans;
use infrajs\template\Template;
use infrajs\path\Path;

if (!is_file('vendor/autoload.php')) {
	chdir('../../../../');
	require_once('vendor/autoload.php');
}


/*
	Пустой шаблон также содержи подшаблон root, ошибка что возвращается слово root
*/


$ans = array('title' => 'Проверка что пустой шаблон не возвращает слово root');

$ans['res'] = Template::parse(array(''), true);
if ($ans['res'] !== '') {
	return Ans::err($ans, 'Непройден тест 1 {res}');
}

$ans['res'] = Template::parse(array(''));
if ($ans['res'] !== '') {
	return Ans::err($ans, 'Непройден тест 2 {res}');
}

return Ans::ret($ans, 'Все теcты пройдены');
