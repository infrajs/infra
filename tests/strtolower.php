<?php
namespace infrajs\infra;
use infrajs\access\Access;
use infrajs\event\Event;
use infrajs\ans\Ans;
use infrajs\path\Path;
use infrajs\sequence\Sequence;
use infrajs\load\Load;

if (!is_file('vendor/autoload.php')) {
	chdir('../../../../');
	require_once('vendor/autoload.php');
}

$ans = array(
	'title' => 'Проверка функции strtolower',
);
$s1 = 'Кирилица utf8';
$s2 = 'кирилица utf8';

if (mb_strtolower($s1) != $s2) {
	return Ans::err($ans, 'mb_strtolower не работает ');
}

return Ans::ret($ans, 'infra strtolower работает');
