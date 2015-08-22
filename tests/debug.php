<?php

infra_test(true);
$ans = array();
$ans['title'] = 'Тест на значение отладки debug и test';

$conf = infra_config();
$conf = $conf['infra'];
if (infra_debug() && !is_string($conf['debug']) && !is_array($conf['debug'])) {
	return infra_err($ans, 'Значение config.infra.debug = true');
}

if (infra_test() && !is_string($conf['test']) && !is_array($conf['test'])) {
	return infra_err($ans, 'Значение config.infra.test = true');
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
		return infra_err($ans, 'debug позволяет увидеть логин пароль админа. debug не должен содержать левые Ip адреса. Не должен быть указан на продакшине. config.infra.debug='.$conf['debug']);
	}
} else {
	//debug вообще запрещён всё ок
}





return infra_ret($ans, 'Безопасные infra.debug:'.$conf['debug'].' и infra.test:'.$conf['test']);
