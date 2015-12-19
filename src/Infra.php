<?php

/*

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
Access::admin(true);//bool, //если true, выкидывает окно авторизации если не авторизирован


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
use infrajs\access\Access;
use infrajs\event\Event;
use infrajs\load\Load;
use infrajs\mem\Mem;
use infrajs\path\Path;



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
			/**
			 * Надо чтобы применился .infra.json конфиг с путями
			 * Config::get('path') сейчас возвращает данные, которые не используются в функции Path::theme
			 * Интеграция описана в -path/infra.php путь на который по умолчанию Path находить уж должен
			 **/

			/**
			 * Выход если у браузера сохранена версия.
			 * Проверяется debug или нет и пути path должны уже быть установлены
			 **/
			Config::init();
			
			Access::adminModified();

			Infra::initInstall();

			Access::initHeaders();

			$query=urldecode($_SERVER['QUERY_STRING']);		

			Path::init($query);

		});
	}
	private static function initInstall()
	{
		Event::handler('oninstall', function () {
			header('Infra-Update: OK');
		});
		$update=false;
		if(Path::$conf['fs']){
			$file = Path::theme('~update');
			if ($file) {
				$update=true;
			} else {
				$dir = Path::theme('!');
				if (!$dir) {
					$update=true;
				}
			}
		}
		
		if ($update) {
			$r = Path::fullrmdir('!');
			if(!$r) throw new \Exception('Infra-Update: Error');
			Event::fire('oninstall');
		}
		//Сначало инстал а потом уже можно делать Mem::set
		if (Path::$conf['fs'] && $file) {
			$r = @unlink($file);//Файл появляется после заливки из svn и если с транка залить без проверки на продакшин, то файл зальётся и на продакшин
			if (!$r) {
				echo '<pre>';
				throw new \Exception('Infra-Update: Error');
			}
		}
	}
}
