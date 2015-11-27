<?php

/*
Copyright 2008 ITLife, Ltd. http://itlife-studio.ru

infrajs.php Общий инклуд для всех скриптов



----------- functions.php Библиотека ---------------
infra_toutf - перевести строку в кодировку UTF8 если строка ещё не в кодировке UTF8
infra_toFS- перевести строку в кодировку файловой системы
infra_tojs - объект php в строку json
infra_fromjs - строка json в объект php
infra_getBrowser - строка ie ie6 gecko safari opera и тп...
infra_getUrl - синхронный кроссдоменный get запрос работающий на хостингах с ограничением file_get_contents


----------- plugins.php Плагины --------------
infra_plugin - Подключает функции какого-то плагина, возвращает вывод плагина в браузер, или если плагином предусмотренно возвращает объект php ответ плагина
infra_theme - (*some/path/to/file) возвращает пусть от корня сайта до файла согласно системе плагинов

----------- cache.php Плагины --------------
infra_cache - ($conds,$fn,$args); conds - файлы или метки.

----------- login.php Авторизация ---------------
infra_admin(true);//bool, //если true, выкидывает окно авторизации если не авторизирован


----------- не реализовано --------------
modified - будет как-нибудь
state - серверная обработка адреса сайта
?1/12/213 - это корректная ссылка... но вот куда она ведёт.. должен быть редирект чтобы поисковики понимали что это ?Форум/Имя Раздела/Имя Темы
?openid, session - генерируемые при переходах по ссылкам get параметры
statist - интегрировать как-нибудь

*/
	//namespace infrajs\infra;


namespace infrajs\infra;
use infrajs\once\Once;
require_once __DIR__.'/../infra/ext/config.php';
require_once __DIR__.'/../infra/ext/load.php';

class Infra
{
	public static function init()
	{
		infra_require('*infra/ext/view.php');
		infra_require('*infra/ext/mem.php');
		infra_require('*infra/ext/admin.php');

		infra_admin_modified();//Здесь уже выход если у браузера сохранена версия

		infra_require('*infra/ext/forr.php');


		infra_require('*infra/ext/cache.php');


		infra_require('*infra/ext/mail.php');

		infra_require('*infra/ext/events.php');


		infra_require('*infra/ext/seq.php');
		infra_require('*infra/ext/template.php');

		infra_require('*infra/ext/html.php');

		//requires
		$conf=infra_config();

		foreach ($conf as $plugin) {
			if (empty($plugin['require'])) {
				continue;
			}
			infra_require($plugin['require']);
		}

		Once::exec('infra_install', function () {
			infra_install();
			if (infra_test_silent()) {
				error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT);
				ini_set('display_errors', 1);
				@header('Infra-Test:true');
			} else {
				error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT);
				@header('Infra-Test:false');
				ini_set('display_errors', 0);
			}
			if (infra_debug_silent()) {
				@header('Infra-Debug:true');
				infra_cache_no(); //Браузер не кэширует no-store.
			} else {
				@header('Infra-Debug:false');
				infra_cache_yes(); //Браузер кэширует, но проверяет каждый раз no-cache
			}
			if (infra_admin_silent()) {
				@header('Infra-Admin:true');
			} else {
				@header('Infra-Admin:false');
			}

			ext\Crumb::init();

			global $infra;
			infra_fire($infra,'oninit');

			if (!empty($_SERVER['QUERY_STRING'])) {
				$query = urldecode($_SERVER['QUERY_STRING']);
				if ($query{0} == '*'||$query{0} == '~'||$query{0} == '|') {
					$theme = infra_theme('*infra/theme.php');
					include $theme;
					exit;
				}
			}
		});
	}
}
