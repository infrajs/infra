<?php

$ans = array();
$ans['title'] = 'Файлы с точкой в начале должны быть скрыты';

$data = @file_get_contents('?*.config.json');
if ($data) {
	$ans['result'] = false;
	echo json_encode($ans);
}

$dirs = infra_dirs();
$src = infra_view_getPath().$dirs['data'].'.config.json';
$data = @file_get_contents($src);

if ($data) {
	return infra_err($ans);
}
return infra_ret($ans);
