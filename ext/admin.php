<?php

/*
Copyright 2008-2010 ITLife, Ltd. http://itlife-studio.ru


*/
function infra_admin_modified($etag = '')
{
	//$v изменение которой должно создавать новую копию кэша
	if (infra_debug_silent()) {
		return;
	}
	if ($etag) {
		//Мы осознано включаем возможность кэшировать, даже если были запреты до этого! так ак есть Etag и в нём срыты эти не явные условия
		//Таким образом отменяется обращение к базе даных, инициализация сессии и тп.
		infra_cache_yes();
	}
	if (!empty($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
		$last_modified = infra_admin_time();
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
/*function infra_admin($break=null,$ans=array('msg'=>'Требуется авторизация','result'=>0)){
	//infra_admin(true) - пропускает только если ты администратор, иначе выкидывает окно авторизации
	//infra_admin(false) - пропускает только если ты НЕ администратор, иначе выкидывает окно авторизации
	//$ans выводится в json если нажать отмена
	//infra_admin(array('login','pass'));
	$data=infra_config();
	$data=$data['admin'];
	$_ADM_NAME = $data['login'];
	$_ADM_PASS = $data['password'];
	$admin=null;//Неизвестно

	if(is_array($break)){
		$admin=($break[0]===$_ADM_NAME&&$break[1]===$_ADM_PASS);
	}
	infra_cache_no(); //@header('Cache-control:no-store');Метка о том что это место нельзя кэшировать для всех. нужно выставлять даже с session_start так как сессия может быть уже запущенной
	//Кэш делается гостем.. так как скрыт за функцией infra_admin_cache исключение infra_cache когда кэшу интересны только даты изменения файлов.
	$r=session_start();

	if(is_null($admin)&&isset($_SESSION['ADMIN'])){
		$admin=(bool)$_SESSION['ADMIN'];
	}
	if(is_null($admin)){
		$admin=(@$_SERVER['PHP_AUTH_USER']==$_ADM_NAME&&@$_SERVER['PHP_AUTH_PW']==$_ADM_PASS);
		if($admin)$_SESSION['ADMIN']=true;
	}

	if($break===false){
		$admin=false;
		$_SESSION['ADMIN']=false;
	}
	if($admin){
		infra_admin_time_set();
	}

	if($break===true&&!$admin){
		header("WWW-Authenticate: Basic realm=\"Protected Area\"");
		header("HTTP/1.0 401 Unauthorized");
		unset($_SESSION['ADMIN']);
		echo infra_json_encode($ans);
		exit;
	}
	$_SESSION['ADMIN']=$admin;
	return $admin;
}*/
/**
 * Тихая функция, только проверка, без отметок.
 */
function infra_admin_silent()
{
	$data = infra_config();
	$data = $data['admin'];
	$_ADM_NAME = $data['login'];
	$_ADM_PASS = $data['password'];
	if (empty($_SERVER['HTTP_USER_AGENT'])) {
		$_SERVER['HTTP_USER_AGENT'] = '';
	}
	$realkey = md5($_ADM_NAME.$_ADM_PASS.$_SERVER['HTTP_USER_AGENT'].$_SERVER['REMOTE_ADDR']);
	$key = infra_view_getCookie('infra_admin');

	return ($key === $realkey);
}
/**
 * infra_admin(true) - пропускает только если ты администратор, иначе выкидывает окно авторизации
 * infra_admin(false) - пропускает только если ты НЕ администратор, иначе выкидывает окно авторизации
 * $ans выводится в json если нажать отмена
 * infra_admin(array('login','pass'));.
 */
function infra_admin($break = null, $ans = array('msg' => 'Требуется авторизация', 'result' => 0))
{
	$data = infra_config();
	$data = $data['admin'];
	$_ADM_NAME = $data['login'];
	$_ADM_PASS = $data['password'];
	$admin = null;//Неизвестно

	$realkey = md5($_ADM_NAME.$_ADM_PASS.$_SERVER['HTTP_USER_AGENT'].$_SERVER['REMOTE_ADDR']);

	infra_cache_no();
	if (is_array($break)) {
		$admin = ($break[0] === $_ADM_NAME && $break[1] === $_ADM_PASS);
		if ($admin) {
			infra_view_setCookie('infra_admin', $realkey);
		} else {
			infra_view_setCookie('infra_admin');
		}
	} else {
		$key = infra_view_getCookie('infra_admin');
		$admin = ($key === $realkey);
		if ($break === false) {
			infra_view_setCookie('infra_admin');
			$admin = false;
		} elseif ($break === true && !$admin) {
			$admin = (@$_SERVER['PHP_AUTH_USER'] == $_ADM_NAME && @$_SERVER['PHP_AUTH_PW'] == $_ADM_PASS);
			if ($admin) {
				infra_view_setCookie('infra_admin', $realkey);
			} else {
				header('WWW-Authenticate: Basic realm="Protected Area"');
				header('HTTP/1.0 401 Unauthorized');
				echo infra_json_encode($ans);
				exit;
			}
		}
	}

	if ($admin) {
		infra_admin_time_set();
	}

	return $admin;
}
function infra_admin_time_set($t = null)
{
	$dirs = infra_dirs();
	if (is_null($t)) $t = time();
	$adm = array('time' => $t);

	infra_mem_set('infra_admin_time', $adm);
	infra_once('infra_admin_time', $adm['time']);
	return true;
}

/**
 * Отвечает на вопрос! Время настало для сложной обработки?
 * Функция стремится сказать что время ещё не пришло... и последней инстанцией будет выполнение фукции которая должна вернуть true или false
 * Если передать метку времени и функцию это будет означать запуск функции если метка времени старей админской метки
 * В debug режиме функция запускается всегда.
 */
function infra_admin_isTime($cachetime = 0, $callback = false, $re = false)
{
	if (!$cachetime || $re) {
		return true; //Нет кэша... пришло всремя для сложной обработки
	};
	//Мы тут не меняем содержание.. а только отвечам на вопрос можно ли закэшировать... cache_no от Infra_debug тут неуместен
	if (infra_debug_silent() || $cachetime < infra_admin_time()) {
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
 * Если передать метку времени и функцию это будет означать запуск функции если метка времени старей админской метки
 * В debug режиме функция запускается всегда.
 */
function infra_admin_time()
{
	return infra_once('infra_admin_time', function () {
		$adm = infra_mem_get('infra_admin_time');
		if (!$adm) {
			$adm = array();
		}
		if (!isset($adm['time'])) {
			$adm['time'] = 0;
		}

		return $adm['time'];
	});
}
function infra_admin_cache($name, $fn, $args = array(), $re = false)
{
	//Запускается один раз для админа, остальные разы возвращает кэш из памяти
	$name = 'infra_admin_cache_'.$name;

	return infra_once($name, function ($args, $name) use ($name, $fn, $re) {
		$path = $name.'_'.infra_hash($args);
		$data = infra_mem_get($path);
		if (!$data) {
			$data = array('time' => 0);
		}
		$execute = infra_admin_isTime($data['time'], function () {
			return true;
		}, $re);

		if ($execute) {
			$cache = infra_cache_check(function () use (&$data, $fn, $args, $re) {
				$data['result'] = call_user_func_array($fn, array_merge($args, array($re)));
			});
			if ($cache) {
				$data['time'] = time();
				infra_mem_set($path, $data);
			} else {
				infra_mem_delete($path);
			}
		}

		return $data['result'];
	}, array($args, $name), $re);
}
