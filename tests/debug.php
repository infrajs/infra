<?php
namespace infrajs\infra;
use infrajs\access\Access;
use infrajs\event\Event;
use infrajs\ans\Ans;

if (!is_file('vendor/autoload.php')) {
	chdir('../../../../');
	require_once('vendor/autoload.php');
}

Access::test(true);
$ans = array();
$ans['title'] = 'Тест на значение отладки debug и test';

$conf = Config::get();
$conf = $conf['infra'];
if (Access::debug() && !is_string($conf['debug']) && !is_array($conf['debug'])) {
	return Ans::err($ans, 'Значение config.infra.debug = true');
}

if (Access::test() && !is_string($conf['test']) && !is_array($conf['test'])) {
	return Ans::err($ans, 'Значение config.infra.test = true');
}

$debug=$conf['debug'];
if($debug){
	if (!is_array($debug)) {
		$debug=array($debug);
	}

	$key=array_search('::1',$debug);
	if ($key !== false) {
		array_splice($debug,$key,1);
	}

	$key=array_search('127.0.0.1',$debug);
	if ($key !== false) {
		array_splice($debug,$key,1);
	}

	if($debug){	
		return Ans::err($ans, 'debug позволяет увидеть логин пароль админа. debug не должен содержать левые Ip адреса. Не должен быть указан на продакшине. config.infra.debug='.$conf['debug']);
	}
} else {
	//debug вообще запрещён всё ок
}





return Ans::ret($ans, 'Безопасные infra.debug:'.$conf['debug'].' и infra.test:'.$conf['test']);
