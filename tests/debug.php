<?php
infra_test(true);
$ans = array();
$ans['title'] = 'Тест на значение отладки debug и test';

$conf=infra_config();
$conf=$conf['infra'];
if (infra_debug()&&!is_string($conf['debug'])&&!is_array($conf['debug'])) {
	return infra_err($ans, 'Значение debug = true');
}

if (infra_test()&&!is_string($conf['test'])&&!is_array($conf['test'])) {
	return infra_err($ans, 'Значение test = true');
}
return infra_ret($ans, 'Безопасные infra.debug:'.$conf['debug'].' и infra.test:'.$conf['test']);
