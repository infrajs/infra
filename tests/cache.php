<?php

	
$ans = array();
$ans['title'] = 'Тест функции кэширующих функций infra_cache infra_admin_cache. Требуется F5';



$conf=infra_config();

$name='test';

$r1=infra_cache_is();

$r2=infra_cache_check(function () use (&$ans) {
	infra_cache_no();
});
$r3=infra_cache_is();
$r4=infra_cache_check(function () use (&$ans) {
});
$r5=infra_cache_is();


infra_test(true);

$ans['admin']=infra_admin();//Обращение к админу запрещает кэширование, потому что переменное значение
$ans['debug']=infra_debug();//Обращение к дебагу запрещает кэширование, потому что переменное значение //К дебагу мы обращаемся во время ошибки, поэтому любой Ans::err Запретит кэш, но ret остаются в кэше
$ans['test']=infra_test(); //Это админ, который может только смотреть, при обращении запрещает кэшировать

$ans['isphp']=infra_isphp();//Нельзя на основе этой переменной менять значение, но кэширование сохраняется

$str =' debug:'.infra_debug();
$str.=' admin:'.infra_admin();
$str.=' isphp:'.infra_isphp();

//infra_debug() - запрещает кэшировать верхний уровень
//Верхний это уровень браузера, его узнать можно по infra_isphp() true будет означать что мы где-то в инклуде

if (infra_isphp()) {
	if (infra_debug()||infra_admin()) {
		//Даже в debug r1 true. Так как это вложенный файл... для браузера будет false
		if (!$r1||$r2||$r3||!$r4||$r5) {
			return infra_err($ans, 'infra_cache_check '.implode(',', array($r1, $r2, $r3, $r4, $r5)).' работает некорректно'.$str);
		}
	} else {
		if (!$r1||$r2||$r3||!$r4||$r5) {
			return infra_err($ans, 'infra_cache_check '.implode(',', array($r1, $r2, $r3, $r4, $r5)).' работает некорректно'.$str);
		}
	}
} else {
	if (infra_debug()||infra_admin()) {
		//Даже в debug r1 true. Так как это вложенный файл... для браузера будет false
		if ($r1||$r2||$r3||!$r4||$r5) {
			return infra_err($ans, 'infra_cache_check '.implode(',', array($r1, $r2, $r3, $r4, $r5)).' работает некорректно'.$str);
		}
	} else {
		if (!$r1||$r2||$r3||!$r4||$r5) {
			return infra_err($ans, 'infra_cache_check работает некорректно'.$str);
		}
	}
}





$ans['test']=false;
infra_admin_cache($name.'!!', function () use (&$ans) {
	infra_cache_no();
	$ans['test'] = true;
});
if (!$ans['test']) {
	return infra_err($ans, 'infra_cache_no В кэшируемой функции запрет на кэширование... но этот запрет не сработал');
}




$ans['counter']=0;
infra_admin_cache($name, function () use (&$ans) {
	$ans['counter']++;
});
infra_admin_cache($name, function () use (&$ans) {
	$ans['counter']++;
});

if (infra_admin()) {
	if ($ans['counter'] != 1) {
		return infra_err($ans, 'infra_admin_cache В с авторизацией должен был сработать один раз');
	}
} else {
	if (infra_debug()) {
		if ($ans['counter'] != 1) {
			return infra_err($ans, 'infra_admin_cache В отладочном режиме должен был сработать один раз, так как debug:true');
		}
	} else {
		if ($ans['counter'] != 0) {
			return infra_err($ans, 'infra_admin_cache В рабочем режиме должен работать кэш, требуется обновить страницу');
		}

	}
}



$ans['counter']=0;
infra_cache(array('test'), $name, function () use (&$ans) {
	$ans['counter']++;
});
infra_cache(array('test'), $name, function () use (&$ans) {
	$ans['counter']++;
});


if (infra_admin()) {
	if ($ans['counter'] != 0) {
		return infra_err($ans, 'infra_cache с авторизацией и с несуществующим файлом'.$str);
	}
} else {
	if (infra_debug()) {
		if ($ans['counter'] != 0) {
			return infra_err($ans, 'infra_cache В отладочном режиме с несуществующим файлом'.$str);
		}
	} else {
		if ($ans['counter'] != 0) {
			return infra_err($ans, 'infra_cache В рабочем режиме с несуществующим файлом  должен работать кэш, требуется обновить страницу');
		}

	}
}


$ans['counter']=0;
infra_cache(array(), $name.'yescache', function () use (&$ans) {
	$ans['counter']++;
});
if ($ans['counter'] != 0) {
	return infra_err($ans, 'infra_cache без условий, требуется обновить страницу');
}

$ans['counter']=0;
infra_cache(array(), $name.'nocache', function () use (&$ans) {
	infra_cache_no();
	$ans['counter']++;
});
if ($ans['counter'] != 1) {
	return infra_err($ans, 'infra_cache без условий infra_cache_no, требуется обновить страницу');
}

return infra_ret($ans, 'Тест пройден'.$str);
