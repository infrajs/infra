<?php

/*
Copyright 2008-2013 ITLife, Ltd. http://itlife-studio.ru

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
	//namespace itlife\infra;




namespace itlife\infra;

class Infra
{
	public static function init()
	{
		/*
			игнор цифр, и расширения infra/infra
		*/

		require_once(__DIR__.'/../infra/ext/config.php');

		require_once(__DIR__.'/../infra/ext/load.php');





		//Продакшин должен быть таким же как и тестовый сервер, в том числе и с выводом ошибок. Это упрощает поддержку. Меньше различий в ошибках.
		//ini_set('error_reporting',E_ALL ^ E_STRICT ^ E_NOTICE);
		//error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT);
		//Strict Standards: Only variables should be assigned by reference
		//Notice: Only variable references should be returned by reference
		//Notice: Undefined index:
		//ini_set('display_errors',1);


		infra_require('*infra/ext/admin.php');


		infra_require('*infra/ext/cache.php');




		infra_require('*infra/ext/once.php');




		infra_require('*infra/ext/mail.php');
		infra_require('*infra/ext/forr.php');


		infra_require('*infra/ext/mem.php');
		infra_require('*infra/ext/events.php');
		infra_require('*infra/ext/connect.php');
		infra_require('*infra/ext/view.php');



		infra_require('*infra/ext/seq.php');
		infra_require('*infra/ext/template.php');


		infra_require('*infra/ext/html.php');



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

		ext\crumb::init();
		if (!empty($_SERVER['QUERY_STRING'])) {
			$query = urldecode($_SERVER['QUERY_STRING']);
			if ($query{0} == '*') {
				$theme = infra_theme('*infra/theme.php');
				return include $theme;
			}
		}
	}
}
