<?php


	require_once __DIR__.'/../../infra/Infra.php';
	$ans = array();
	$ans['title'] = 'events.php';

	$i = 1;
	$obj = array();

	infra_wait($obj, 'ontest', function () {
		global $i;
		++$i;
	});

	infra_listen($obj, 'ontest', function () {
		global $i;
		++$i;
	});

	infra_fire($obj, 'ontest');
	infra_fire($obj, 'ontest');

	global $infra;
	global $j;

	$j = 1;
	Event::listeng('obj.onsome', function ($obj) {
		global $j;
		++$j;
	});
	infra_wait($infra, 'obj.onsome', function ($obj) {
		global $j;
		++$j;
	});
	infra_fire($obj, 'obj.onsome');
	infra_fire($obj2, 'obj.onsome');

	if ($i != 4 && $j != 4) {
		return Ans::err($ans, 'err');
	}

	return Ans::ret($ans, 'ret');
