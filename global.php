<?php
namespace infrajs\infra;
use infrajs\infra\Ans;
use infrajs\infra\Load;
use infrajs\view\View;
use infrajs\path\Path;
use infrajs\event\Event;
use infrajs\template\Template;
use infrajs\sequence\Sequence;

function Ans::ret($ans, $str = false)
{
	return Ans::ret($ans, $str);
}
function Ans::err($ans, $str = false)
{
	return Ans::err($ans, $str);
}
function infra_ans($ans)
{
	return Ans::ans($ans);
}
function infra_log($ans, $str, $data)
{
	return Ans::log($ans, $str);
}
function Path::req($path)
{
	return Path::req($path);
}


$fn = function ($path) {
	return Path::theme($path);
};
Sequence::set(Template::$scope, array('infra', 'theme'), $fn);

$conf = &Config::pub();
Sequence::set(Template::$scope, array('infra', 'conf'), $conf);

$fn = function () {
	return View::getPath();
};
Sequence::set(Template::$scope, array('infra', 'view', 'getPath'), $fn);

$fn = function () {
	return View::getHost();
};
Sequence::set(Template::$scope, array('infra', 'view', 'getHost'), $fn);

$fn = function ($s) {
	return Sequence::short($s);
};
Sequence::set(Template::$scope, array('infra', 'seq', 'short'), $fn);

$fn = function ($s) {
	return Sequence::right($s);
};
Sequence::set(Template::$scope, array('infra', 'seq', 'right'), $fn);

$fn = function () {
	return View::getRoot();
};
Sequence::set(Template::$scope, array('infra', 'view', 'getRoot'), $fn);
$fn = function ($src) {
	return Load::srcInfo($src);
};
Sequence::set(Template::$scope, array('infra', 'srcinfo'), $fn);

$host = $_SERVER['HTTP_HOST'];
$p = explode('?', $_SERVER['REQUEST_URI']);
$pathname = $p[0];
Sequence::set(Template::$scope, array('location', 'host'), $host);
Sequence::set(Template::$scope, array('location', 'pathname'), $pathname);


Event::handler('onjs', function () {	
	View::js('*infra/access.js');
});



/**/