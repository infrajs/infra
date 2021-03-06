<?php
namespace infrajs\infra;
use infrajs\access\Access;
use infrajs\event\Event;
use infrajs\ans\Ans;

if (!is_file('vendor/autoload.php')) {
	chdir('../../../../');
	require_once('vendor/autoload.php');
}

$ans = array();
$ans['title'] = 'Проверка окружения';

$v = phpversion();
$ver = explode('.', $v);
if ($ver[0] < 5 || ($ver[0] == 5 && $ver[1] < 4)) {
	return Ans::err($ans, 'Требуется более новая версия php от 5.4 сейчас '.$v);
}

/*
	5.4 - json_encode($data,JSON_UNESCAPED_UNICODE); (http://php.net/manual/en/migration54.incompatible.php что включает 5.4 версия)
	5.3 - используются анонимные функции
	5.3 - не всегда ставится закрывающие тег php
*/
if (mb_internal_encoding() !== 'UTF-8') {
	return Ans::err($ans, 'mb_internal_encoding()!=="UTF-8" '.mb_internal_encoding());
}

//allow_call_time_reference http://php.net/manual/en/language.references.pass.php


return Ans::ret($ans, 'ОК');
