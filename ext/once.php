<?php

global $infra_once;
$infra_once = array();
function infra_hash($args, $r = false)
{
	//Функция которая передаётся аргументом надо передвать как use
	if (is_array($args)) {
		$a = array();
		foreach ($args as $k => $v) {
			$a[$k] = infra_hash($v, true);
		}
	} else {
		if (is_callable($args)) {
			$a = 'func!';
		} else {
			//Заглушка для функции
			$a = $args;
		}
	}
	if ($r) {
		return serialize($a);
	}

	return md5(serialize($a));
}
/**
 * Возвращает хэш переданных параметров name и args.
 * Используется для идентификации кэша в infra_cache.
 */
function infra_once_clear($name, $args)
{
	global $infra_once;
	$strargs = infra_hash($args);
	$hash = $name.$strargs;

	unset($infra_once[$hash]);

	return $hash;
}
function &infra_once($name, $call, $args = array(), $re = false)
{
	global $infra_once;
	$strargs = infra_hash($args);
	$hash = $name.$strargs;

	if (!is_callable($call)) {
		$re = false;
		$infra_once[$hash] = array('result' => $call);
	}
	if (isset($infra_once[$hash]) && !$re) {
		return $infra_once[$hash]['result'];
	}
	$infra_once[$hash] = array('exec' => true);

	$v = array_merge($args, array($re, $hash));

	$v = call_user_func_array($call, $v);

	$infra_once[$hash]['result'] = $v;

	return $infra_once[$hash]['result'];
}
/*

infra_once('somefunc',function(){
	
},array($name));

infra_once('somefunc',$value,array($name));

*/
