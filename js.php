<?php
use infrajs\access\Access;
use infrajs\load\Load;
use infrajs\infra\Infra;
use infrajs\infra\Each;
use infrajs\event\Event;
use infrajs\view\View;
use infrajs\path\Path;

$re = isset($_GET['re']); //Modified re нужно обновлять с ctrl+F5

Infra::req();

$html = Access::adminCache('infra_js_php', function ($str) {
	
	View::js('-jquery/jquery.js');
	View::$js .= 'window.infra={};';
	View::js('-load/load.js');
	View::js('-layer-config/config.js');
	$conf = Infra::pub();
	View::$js .= 'infra.conf=('.Load::json_encode($conf).');infra.config=function(){return infra.conf;};';

	View::js('-infra/src/Each.js');
	View::js('-view/view.js');

	View::js('-template/template.js');
	View::js('-controller/src/Crumb.js');
	View::js('-loader/loader.js');


	View::js('-controller/infrajs.js');//

	Event::fire('onjs');

	$conf=Infra::config();
	foreach($conf as $name=>$c){
		if (empty($c['js'])) continue;
		Each::exec($c['js'], function($js){
			View::js($js);
		});
	}

	return View::js();
}, array($_SERVER['QUERY_STRING']), $re);
@header('content-type: text/javascript; charset=utf-8');
echo $html;
