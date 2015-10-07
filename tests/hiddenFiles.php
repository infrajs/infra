<?php

$ans = array();
$ans['title'] = 'Файлы с точкой в начале должны быть скрыты';

$data = @file_get_contents('?*.infra.json');
if ($data) {
	$ans['result'] = false;
	echo json_encode($ans);
}

$dirs = infra_dirs();
$src = infra_view_getPath().$dirs['data'].'.infra.json';
$data1 = @file_get_contents($src);
$src = $dirs['data'].'.infra.json';
$data2 = @file_get_contents($src);
if ($data1 == $data2) {
	return infra_err($ans);
}
return infra_ret($ans);
