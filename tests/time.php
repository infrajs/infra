<?php
namespace infrajs\infra;
use infrajs\access\Access;
use infrajs\event\Event;
use infrajs\ans\Ans;
use infrajs\path\Path;
use infrajs\sequence\Sequence;
use infrajs\load\Load;

if (!is_file('vendor/autoload.php')) {
	chdir('../../../../');
	require_once('vendor/autoload.php');
}

$ans['title'] = 'Временная зона по умолчанию';
$msg = date_default_timezone_get();

return Ans::ret($ans, $msg);
