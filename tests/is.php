<?php
use itlife\infrajs\Infrajs;

infra_require('*infrajs/make.php');
$ans = array();
$ans['title'] = 'is.php';

$i = 0;

infrajs::isAdd('test', function (&$layer) {
	global $i;
	++$i;

	return true;
});
infrajs::isAdd('test', function (&$layer) {
	global $i;
	++$i;

	return false;
});
infrajs::isAdd('test', function (&$layer) {
	global $i;
	++$i;

	return true;
});

$layer = array();
$cw = &infrajs::storeLayer($layer);//work
$cc = &infrajs::store();//check
$cw['counter'] = $cc['counter'] = 1;

$r = infrajs::is('test', $layer);
if ($i != 2 && $r) {
	return infra_err($ans, 'err');
}

return infra_ret($ans, 'ret');
