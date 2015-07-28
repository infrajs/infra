<?php
infra_test(true);
$ans = array();
$ans['title'] = 'Тест на значение отладки debug и test';

$conf=infra_config();
$conf=$conf['infra'];
if (infra_debug()&&!is_string($conf['debug'])&&!is_array($conf['debug'])) {
	return infra_err($ans, 'Значение config.infra.debug = true');
}

if (infra_test()&&!is_string($conf['test'])&&!is_array($conf['test'])) {
	return infra_err($ans, 'Значение config.infra.test = true');
}

if ($conf['debug']&&$conf['debug']!='127.0.0.1'&&$conf['debug']!=['127.0.0.1']) {
	return infra_err($ans, 'debug не должен быть указан на продакшине. config.infra.debug='.$conf['debug']);
}
return infra_ret($ans, 'Безопасные infra.debug:'.$conf['debug'].' и infra.test:'.$conf['test']);
