<?php

$tpl="{:inc.test}{inc::}*infra/tests/resources/inc.tpl";

$data=array();


$res=infra_template_parse(array($tpl), $data);
$ans['res']=$res;
if ($res!='Привет!') {
	return infra_err($ans, 'Неожиданный резльтат');
}

return infra_ret($ans);
