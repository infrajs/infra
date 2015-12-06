<?php


	require_once __DIR__.'/../../infra/Infra.php';
	$ans = array(
		'title' => 'Проверка функции strtolower',
	);
	$s1 = infra_tofs('Кирилица utf8');
	$s2 = infra_tofs('кирилица utf8');

	if (mb_strtolower($s1) != $s2) {
		return infra_err($ans, 'mb_strtolower не работает');
	}

	return infra_ret($ans, 'infra strtolower работает');
