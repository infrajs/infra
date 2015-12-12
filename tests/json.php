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

$ans = array();
$ans['title'] = 'Тест на декодирование JSON';
$source = '""';

$data = Load::json_decode($source);
if ($data !== '') {
	return Ans::err($ans, 'Не может декодировать');
}

return Ans::ret($ans, 'Декодировано');
