<?php


	require_once __DIR__.'/../../infra/Infra.php';
	$ans = array(
		'title' => 'Проверка функции strtolower',
	);
	$s1 = Path::tofs('Кирилица utf8');
	$s2 = Path::tofs('кирилица utf8');

	if (mb_strtolower($s1) != $s2) {
		return Ans::err($ans, 'mb_strtolower не работает');
	}

	return Ans::ret($ans, 'infra strtolower работает');
