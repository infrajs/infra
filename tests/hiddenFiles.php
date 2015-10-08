<?php

$ans = array();
$ans['title'] = 'Файлы с точкой в начале неточный тест. infra/data/.infra.json';


//Проверка 1. Обращение через систему. 
$data = @file_get_contents('?*.infra.json');
if ($data) {
	$ans['result'] = false;
	echo json_encode($ans);
}

//Проверка 2. Обращение без системы. 
$dirs = infra_dirs();
$src = infra_view_getPath().$dirs['data'].'.infra.json';

$data1 = @file_get_contents($src);
$src = $dirs['data'].'.infra.json';
$data2 = file_get_contents($src);

if ($data1==$data2) {
	return infra_err($ans);
}
return infra_ret($ans);
