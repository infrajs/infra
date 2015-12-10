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
use infrajs\infra\Install;
use infrajs\infra\Access;
use infrajs\once\Once;
use infrajs\load\Load;
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
			 * Infra::config('path') сейчас возвращает данные, которые не используются в функции Path::theme
			 * Интеграция описана в *path/infra.php путь на который по умолчанию Path находить уж должен
			 **/
			Path::req('*path/infra.php');

			/**
			 * Выход если у браузера сохранена версия.
			 * Проверяется debug или нет и пути path должны уже быть установлены
			 **/
			Access::adminModified();
			

			Infra::initInstall();

			Infra::initRequire();
			
			Access::initHeaders();
						
			Path::init();
		});
	}
	private static function initInstall()
	{
		Event::handler('install', function () {
			header('Infra-Update: OK');
		});
		$update=false;
		$file = Path::theme('~update');
		if ($file) {
			$r = @unlink($file);//Файл появляется после заливки из svn и если с транка залить без проверки на продакшин, то файл зальётся и на продакшин
			if (!$r) throw new \Exception('Infra-Update: Error');	
			if (Path::theme('|')) $update=true;
		}

		//Изменился config...
		//проверка только если была авторизация админа
		$cmd5 = Mem::get('configmd5');
		$rmd5 = array('time' => time());
		$rmd5['result'] = md5(serialize(Infra::config()));
		if (!$cmd5 || $rmd5['result'] != $cmd5['result']) {
			Mem::set('configmd5', $rmd5);//Кофиг .infra.json нельзя геренировать программно, только читать.	
			$update=true;
		}

		if ($update) {
			$r = Path::fullrmdir('|');
			if(!$r) throw new \Exception('Infra-Update: Error');
			Event::fire('install');
		}
	}
	private static function addConf(&$conf, $dir)
	{
		$src = $dir.'.infra.json';
		if (!is_file($src)) return;
		$d = file_get_contents($src);
		$d = Load::json_decode($d,true);
		if (is_array($d)) {
			foreach ($d as $k => &$v) {
				if (@!is_array($conf[$k])) {
					$conf[$k] = array();
				}
				if (isset($d[$k]['pub']) && isset($conf[$k]['pub'])) {
					$d[$k]['pub'] = array_unique(array_merge($d[$k]['pub'], $conf[$k]['pub']));
				}
				if (is_array($v)) {
					foreach ($v as $kk => $vv) {
						$conf[$k][$kk] = $vv;
					}
				} else {
					$conf[$k] = $v;
				}
			}
			if(!empty($d['external'])){
				return array('dir'=>$dir, 'external'=>$d['external']);
			}
		}
	}
	/**
	 * В конфиге обрабатывается параметр external, external применяется во вторую интерация и заменяет имеющийся значения. Также добавляется в dir.search.
	 *
	 **/
	public static function addVendor(&$conf, $src) {
		if (is_dir($src)) {
			$list = scandir($src);//Неизвестный порядок плагинов и порядок применения конфигов
			foreach ($list as $name) {
				if ($name[0] == '.' || !is_dir($src.$name)) continue;
				Infra::addConf($conf, $src.$name.'/');
			}
		}
		//Корень искомой дирректории
		static::addConf($conf, $src);
	}
	public static function &config($plugin = false)
	{
		
		$conf=Once::exec('Infra::config', function (){
			$conf = array();
			$dirs = &Infra::dirs();
			$dirs['search'] = array_reverse($dirs['search']);
			foreach ($dirs['search'] as $src) {
				Infra::addVendor($conf, $src);
			}
			/*
				Обработка свойства external
				external: "catalog" включает поиск в папке catalog файлов для Зависимости catalog
				Данные записываются в $dirs
				external:{
					catalog:[path/to/"catalog"/]
				}
			*/
			foreach ($conf as $plugin=>$pdata) { //Сейчас уже неизвестно кто добавил этот параметр в конфиг или кто подменил
				if (empty($pdata['external'])) continue;
				if (!$dirs['external'][$pdata['external']]) $dirs['external'][$pdata['external']]=array(); //Создали dirs.external.catalog=[]
				$plug=$plugin.'/'.$pdata['external'].'/'; //Например cart/catalog/

				foreach ($dirs['search'] as $src) { //Ищим эту указанную в external папку, определяем path/to/
					if (!is_dir($src.$plug)) continue;
					// Нашли vendor/infrajs/cart/catalog/ или data/cart/catalog/
					$dirs['external'][$pdata['external']][]=$src.$plugin.'/'; //Сохраняем без слова catalog
					Infra::addConf($conf, $src.$plug); //Этот конфиг важней того который в data
					
				}
			}
			Infra::addVendor($conf, './'); //В корне и в data не может быть external
			Infra::addVendor($conf, $dirs['data']);
			$conf['path']=array_merge($conf['path'], $dirs);
			return $conf;
		});
		if ($plugin) return $conf[$plugin];
		else return $conf;
		
	}
	public static function &pub ($plugin = false) {
		$conf=Infra::config();
		foreach ($conf as $i => $part) {
			$pub = @$part['pub'];
			if (is_array($pub)) {
				foreach ($part as $name => $val) {
					if (!in_array($name, $pub)) {
						unset($conf[$i][$name]);
					}
				}
			} else {
				unset($conf[$i]);
			}
		}
		if ($plugin) return $conf[$plugin];
		else return $conf;
	}
	public static function &dirs()
	{
		//Нельзя тут использовать Path::theme потому что Path::theme сам использует dirs
		return Once::exec('Infra::dirs', function &() {
			
			$dirs=file_get_contents('vendor/infrajs/path/.infra.json');
			$dirs=Load::json_decode($dirs, true);
			$dirs=$dirs['path'];
			$dirs=array_merge(Path::$conf, $dirs); // Объединили конфиги 

			if (is_file('.infra.json')) {
				$conf=file_get_contents('.infra.json');
				$conf=Load::json_decode($conf, true);
				if (!empty($conf['path'])) {
					$dirs=array_merge($dirs, $conf['path']);
				}
			}
			return $dirs;
		});
	}
	
	public static function initRequire()
	{
		Once::exec('Infra::initRequire', function() {

			$conf=Infra::config();
			foreach ($conf as $name => $plugin) {
				if (empty($plugin['require'])) continue;

				if (!Path::theme($plugin['require'])) {
					echo '<pre>';
					echo 'Plugin "'.$name.'" require error. File not found'."\n";
					print_r($plugin);
					continue;
				}
				Path::req($plugin['require']);
			}
		});
	}
}
