<?php

	use infrajs\infra\ext\Crumb;

$ans = array();
	$ans['title'] = 'Хлебные крошки';

	$obj = Crumb::getInstance('test/check');
	$parent = Crumb::getInstance('test');
	if (Crumb::$childs['test/check'] !== $obj) {
		return infra_err($ans, 'Некорректно определяется крошка 1');
	}
	if (Crumb::$childs['test'] !== $parent) {
		return infra_err($ans, 'Некорректно определяется крошка 2');
	}

	if ($obj->parent !== $parent) {
		return infra_err($ans, 'Некорректно определён parent');
	}

	Crumb::change('test/hi');
	$obj = Crumb::getInstance('test');

	if (!$obj->is) {
		return infra_err($ans, 'Не применилась крошка на втором уровне');
	}

$root = Crumb::getInstance();

	Crumb::change('');
	$crumb = Crumb::getInstance('');
	$f = $crumb->query;

	Crumb::change('test');

	$s = &Crumb::getInstance('some');
	$s2 = &Crumb::getInstance('some');
	$r = infra_isEqual($s, $s2);

	$s = Crumb::$childs;
	$r2 = infra_isEqual($s[''], Crumb::getInstance());

	$r = $r && $r2;

	$crumb = Crumb::getInstance('test');
	$crumb2 = Crumb::getInstance('test2');

	if (!($f == null && $r && !is_null($crumb->query) && is_null($crumb2->query))) {
		return infra_err($ans, 'Изменения крошек');
	}

	Crumb::change('test/test');
	$inst = Crumb::getInstance('test/test/test');

return infra_ret($ans, 'Всё ок');
