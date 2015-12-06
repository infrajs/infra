<?php
namespace infrajs\infra;
use infrajs\infra\Access;
use infrajs\infra\Config;
use infrajs\ans\Ans;


/** 
 * infrajs and standalone
 **/
$src='vendor/autoload.php';
if(!is_file($src)) {
	chdir('../../../');
	require_once($src);
}

$ans=array();

if (isset($_GET['access'])) {
	$ans['test'] = Access::test();
	$ans['debug'] = Access::debug();
	$ans['admin'] = Access::admin();
	return Ans::ret($ans);
}

return Ans::err($ans,'Wrong parameters');