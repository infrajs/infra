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
use infrajs\infra\Config;
use infrajs\infra\Install;
use infrajs\infra\Access;
use infrajs\once\Once;
use infrajs\path\Path;

require_once('vendor/infrajs/path/infra.php');

class Infra
{
	/* Проверка что запущенный php файл находится в корне сайта рядом с vendor
		//Корень сайта относительно этого файла
		$vendorroot = infra_realpath(__DIR__.'/../../../../');//AВ до vendor
		//Корень сайта определёный по рабочей дирректории
		$siteroot = infra_getcwd();
		//Определёный корень сайта двумя способами сравниваем
		//Если результат разный значит система запущена не из той папки где находится vendor с текущим кодом
		if ($siteroot != $vendorroot) {
			die('Start infrajs only from site root - directory which have subfolder vendor with infrajs/infra/');
		}
	*/
	public static function init()
	{	

		Once::exec('Infra::init', function () {

			Access::adminModified();//Здесь уже выход если у браузера сохранена версия
			
			Config::initRequire();
			
			Access::initHeaders();

			//Load::req('*infra/ext/cache.php');
			//Load::req('*infra/ext/mail.php');
						
			Install::initCheck();

			Path::init();
		});
	}
}
