<?php

$re = isset($_GET['re']); //Modified re нужно обновлять с ctrl+F5

$html = infra_admin_cache('infra_js_php', function ($str) {
	global $infra;
	
	$loadTEXT = function ($path) {
		$html = infra_loadTEXT($path);
		$html = 'infra.store("loadTEXT")["'.$path.'"]={value:"'.$html.'",status:"pre"};'; //код отметки о выполненных файлах
		return $html;
	};
	$loadJSON = function ($path) {
		$obj = infra_loadJSON($path);
		$html = 'infra.store("loadJSON")["'.$path.'"]={value:'.infra_json_encode($obj).',status:"pre"};'; //код отметки о выполненных файлах
		return $html;
	};
	$require = function ($path) {
		$html = "\n\n".'//requrie '.$path."\n";
		$html .= infra_loadTEXT($path).';';
		$html .= 'infra.store("require")["'.$path.'"]={value:true};'; //код отметки о выполненных файлах
		return $html;
	};

	
	$infra['require']=$require;
	$infra['loadJSON']=$loadJSON;
	$infra['loadTEXT']=$loadTEXT;
	$infra['js'] = '';
	$infra['js'] .= 'window.infra={};';
	$infra['js'] .= $require('*infra/ext/load.js');
	$infra['js'] .= $require('*infra/ext/config.js');
	$conf = infra_config('secure');
	$infra['js'] .= 'infra.conf=('.infra_json_encode($conf).');infra.config=function(){return infra.conf;};';

	//=======================
	//

	$infra['js'] .= $require('*infra/ext/forr.js');
	$infra['js'] .= $require('*infra/ext/view.js');
	

	$infra['js'] .= $require('*sequence/sequence.js');

	$infra['js'] .= $require('*infra/access.js');

	$infra['js'] .= $require('*event/event.js');

	//Внутри расширений зависимости подключаются, если используется API
	//Здесь подключение дублируется, тем более только здесь это попадёт в кэш
	$infra['js'] .= $require('*infra/ext/html.js');
	$infra['js'] .= $require('*infra/ext/template.js');
	$infra['js'] .= $require('*infra/ext/Crumb.js');
	$infra['js'] .= $require('*infra/ext/loader.js');

	

	$infra['js'] .= $require('*controller/infrajs.js');//

	
	infra_fire($infra, 'onjs');

	$infra['js'] .= 'define(["?*once/once.js"], function(){ return infra })';

	return $infra['js'];
}, array($_SERVER['QUERY_STRING']), $re);
@header('content-type: text/javascript; charset=utf-8');
echo $html;
