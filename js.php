<?php
use infrajs\access\Access;
use infrajs\load\Load;
use infrajs\infra\Infra;
use infrajs\infra\Config;
use infrajs\infra\Each;
use infrajs\event\Event;
use infrajs\view\View;
use infrajs\path\Path;

$re = isset($_GET['re']); //Modified re нужно обновлять с ctrl+F5

if (!is_file('vendor/autoload.php')) {
	chdir('../../../');
	require_once('vendor/autoload.php');
	Infra::init();
}



$html = Access::adminCache('infra_js_php', function ($str) {
	View::$js .= 'window.infra={}; window.infrajs={};';
	View::$js .= 'infra.conf=('.Load::json_encode($conf).');infra.config=function(){return infra.conf;};';
	View::$js .= '
		define("?-infra/js.php", ["?-controller/init.js"], function (infrajs) { 
			console.log("js defined");
			return infrajs; 
		});

	';

	//$conf = Infra::pub();
	$r=Path::theme('-jquery/jquery.min.js');

	View::js('-jquery/jquery.min.js');

	View::js('-event/event.js');

	
	View::js('-hash/hash.js');
	View::js('-once/once.js');
	View::js('-load/load.js');
	
		
	

	View::js('-infra/src/Each.js');
	View::js('-view/view.js');

	


	View::js('-template/template.js');



	View::js('-controller/src/Crumb.js');




	View::js('-loader/loader.js');

	

	$conf=Config::get();
	foreach($conf as $name=>$c){
		if (empty($c['js'])) continue;
		Each::exec($c['js'], function($js){
			View::js($js);
		});
	}

	Event::fire('onjs');
	return View::js();
}, array($_SERVER['QUERY_STRING']), $re);
@header('content-type: text/javascript; charset=utf-8');
echo $html;
