<?php


	require_once __DIR__.'/../../infra/Infra.php';
	$ans = array();
	$ans['title'] = 'Тест на декодирование JSON';
	$source = '""';

	$data = Load::json_decode($source);
	if ($data !== '') {
		return Ans::err($ans, 'Не может декодировать');
	}

	return Ans::ret($ans, 'Декодировано');
