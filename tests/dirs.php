<?php

$ans = array();

$ans['title'] = 'Проверка наличия папок';

$conf = Infra::config();

if ($conf['infra']['cache'] == 'fs') {
	$dirs = infra_dirs();

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
