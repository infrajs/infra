<?php
namespace infrajs\infra;
use infrajs\infra\Config;
use infrajs\hash\Hash;
use infrajs\once\Once;
use infrajs\mem\Mem;
use infrajs\view\View;

class Access {
	public static function isTest()
	{
		if (self::isDebug()) return true;

		$conf = Config::get('infra');
		$ips = $conf['test'];
		if (is_array($ips)) {
			$is = in_array($_SERVER['REMOTE_ADDR'], $ips);
		} elseif (is_string($ips)) {
			$is = ($_SERVER['REMOTE_ADDR'] == $ips);
		} else {
			$is = !!$ips;
		}

		return $is;
	}
	public static function isDebug()
	{
		if (self::isAdmin()) return true;

		$conf = Config::get('infra');
		$ips = $conf['debug'];
		if (is_array($ips)) {
			$is = in_array($_SERVER['REMOTE_ADDR'], $ips);
		} elseif (is_string($ips)) {
			$is = ($_SERVER['REMOTE_ADDR'] == $ips);
		} else {
			$is = !!$ips;
		}

		return $is;
	}
	public static function test($die = false)
	{
		header('Cache-Control: no-store'); //no-store ключевое слово используемое в infra_cache
		$is = self::isTest();
		if (!$die) return $is;
		if ($is) return;
		header('HTTP/1.0 403 Forbidden');
		die('{"msg":"Required config.infra.test:['.$_SERVER['REMOTE_ADDR'].']"}');
	}

	public static function debug($die = false)
	{
		header('Cache-Control: no-store'); //no-store ключевое слово используемое в infra_cache
		$is = self::isDebug();
		if ($is) self::adminSetTime();
		if(!$die) return $is;
		if ($is) return;
		header('HTTP/1.0 403 Forbidden');
		die('{"msg":"Required config.infra.debug:['.$_SERVER['REMOTE_ADDR'].']"}');
	}
	public static function init() {
		Config::initRequires();
	}
	public static function initHeaders() {
		if (Access::isTest()) {
			error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT);
			ini_set('display_errors', 1);
			@header('Infra-Test:true');
		} else {
			error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT);
			@header('Infra-Test:false');
			ini_set('display_errors', 0);
		}
		if (Access::isDebug()) {
			@header('Infra-Debug:true');
			header('Cache-Control: no-store'); //Браузер не кэширует no-store.
		} else {
			@header('Infra-Debug:false');
			header('Cache-Control: no-cache'); //Браузер кэширует, но проверяет каждый раз no-cache
		}
		if (Access::isAdmin()) {
			@header('Infra-Admin:true');
		} else {
			@header('Infra-Admin:false');
		}
	}
	/**
	 * Тихая функция, только проверка, без отметок.
	 */
	public static function isAdmin()
	{
		$data = Config::get();
		$data = $data['admin'];
		$_ADM_NAME = $data['login'];
		$_ADM_PASS = $data['password'];
		if (empty($_SERVER['HTTP_USER_AGENT'])) {
			$_SERVER['HTTP_USER_AGENT'] = '';
		}
		$realkey = md5($_ADM_NAME.$_ADM_PASS.$_SERVER['HTTP_USER_AGENT'].$_SERVER['REMOTE_ADDR']);
		$key = View::getCookie('infra_admin');

		return ($key === $realkey);
	}
	/**
	 * infra_admin(true) - пропускает только если ты администратор, иначе выкидывает окно авторизации
	 * infra_admin(false) - пропускает только если ты НЕ администратор, иначе выкидывает окно авторизации
	 * $ans выводится в json если нажать отмена
	 * infra_admin(array('login','pass'));.
	 */
	public static function admin($break = null, $ans = array('msg' => 'Требуется авторизация', 'result' => 0))
	{
		$data = Config::get();
		$data = $data['admin'];
		$_ADM_NAME = $data['login'];
		$_ADM_PASS = $data['password'];
		$admin = null;//Неизвестно

		$realkey = md5($_ADM_NAME.$_ADM_PASS.$_SERVER['HTTP_USER_AGENT'].$_SERVER['REMOTE_ADDR']);

		header('Cache-Control: no-store'); //no-store ключевое слово используемое в infra_cache
		if (is_array($break)) {
			$admin = ($break[0] === $_ADM_NAME && $break[1] === $_ADM_PASS);
			if ($admin) {
				View::setCookie('infra_admin', $realkey);
			} else {
				View::setCookie('infra_admin');
			}
		} else {
			$key = View::getCookie('infra_admin');
			$admin = ($key === $realkey);
			if ($break === false) {
				View::setCookie('infra_admin');
				$admin = false;
			} elseif ($break === true && !$admin) {
				$admin = (@$_SERVER['PHP_AUTH_USER'] == $_ADM_NAME && @$_SERVER['PHP_AUTH_PW'] == $_ADM_PASS);
				if ($admin) {
					View::setCookie('infra_admin', $realkey);
				} else {
					header('WWW-Authenticate: Basic realm="Protected Area"');
					header('HTTP/1.0 401 Unauthorized');
					echo json_encode($ans);
					exit;
				}
			}
		}

		if ($admin) {
			static::adminSetTime();
		}

		return $admin;
	}
	public static function adminSetTime($t = null)
	{
		if (is_null($t)) $t = time();
		$adm = array('time' => $t);

		Mem::set('infra_admin_time', $adm);
	
		Once::exec('infra_admin_time', $adm['time']);
		return true;
	}

	/**
	 * Отвечает на вопрос! Время настало для сложной обработки?
	 * Функция стремится сказать что время ещё не пришло... и последней инстанцией будет выполнение фукции которая должна вернуть true или false
	 * Если передать метку времени и функцию это будет означать запуск функции если метка времени старей админской метки
	 * В debug режиме функция запускается всегда.
	 */
	public static function adminIsTime($cachetime = 0, $callback = false, $re = false)
	{
		if (!$cachetime || $re) {
			return true; //Нет кэша... пришло всремя для сложной обработки
		};
		//Мы тут не меняем содержание.. а только отвечам на вопрос можно ли закэшировать... cache_no от Infra_debug тут неуместен
		if (self::isDebug() || $cachetime < self::adminTime()) {
			if ($callback) {
				return !!$callback($cachetime); //Только функция сможет сказать надо или нет
			}
			return true;
		}

		return false;
	}

	/**
	 * Время когда админ что-то сделал (время последнего обращения к функции infra_admin и её результате true)
	 * Функция работает без параметров...возвращает дату последних изменений админа для всей системы
	 */
	public static function adminTime()
	{
		return Once::exec('infra_admin_time', function () {
			$adm = Mem::get('infra_admin_time');
			if (!$adm) {
				$adm = array();
			}
			if (!isset($adm['time'])) {
				$adm['time'] = 0;
			}

			return $adm['time'];
		});
	}
	public static function adminCache($name, $fn, $args = array(), $re = false)
	{
		//Запускается один раз для админа, остальные разы возвращает кэш из памяти
		$name = 'Access::adminCache '.$name;
		return Once::exec($name, function ($args, $name) use ($name, $fn, $re) {
			$path = $name.'_'.Hash::make($args);
			$data = Mem::get($path);
			if (!$data) {
				$data = array('time' => 0);
			}
			$execute = self::adminIsTime($data['time'], function () {
				return true;
			}, $re);

			if ($execute) {
				$cache = infra_cache_check(function () use (&$data, $fn, $args, $re) {
					$data['result'] = call_user_func_array($fn, array_merge($args, array($re)));
				});
				if ($cache) {
					$data['time'] = time();
					Mem::set($path, $data);
				} else {
					Mem::delete($path);
				}
			}

			return $data['result'];
		}, array($args, $name), $re);
	}
	public static function adminModified($etag = '')
	{
		//$v изменение которой должно создавать новую копию кэша
		if (self::isDebug()) return;
		
		if ($etag) {
			//Мы осознано включаем возможность кэшировать, даже если были запреты до этого! так ак есть Etag и в нём срыты эти неявные условия
			//Таким образом отменяется обращение к базе даных, инициализация сессии и тп.
			header('Cache-Control: no-cache'); //no-cache ключевое слово используемое в infra_cache
		}
		if (!empty($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
			$last_modified = self::adminTime();
			/*
				Warning: strtotime(): It is not safe to rely on the system's timezone settings. You are *required* to use the date.timezone setting or the date_default_timezone_set() function. In case you used any of those methods and you are still getting this warning, you most likely misspelled the timezone identifier. We selected the timezone 'UTC' for now, but please set date.timezone to select your timezone
			*/
			if (@strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) > $last_modified) {
				if (empty($_SERVER['HTTP_IF_NONE_MATCH']) || $_SERVER['HTTP_IF_NONE_MATCH'] == $etag) {
					//header('ETag: '.$etag);
					//header('Last-Modified: '.$_SERVER['HTTP_IF_MODIFIED_SINCE']);
					header('HTTP/1.0 304 Not Modified');
					exit;
				}
			}
		}
		header('ETag: '.$etag);

		$now = gmdate('D, d M Y H:i:s', time()).' GMT';
		header('Last-Modified: '.$now);
	}
}