<?php

namespace itlife\infra\ext;

class Ans
{
	public static function err($ans, $msg = null)
	{
		$ans['result'] = 0;
		infra_cache_no();
		if ($msg) {
			$ans['msg'] = $msg;
		}

		return self::ans($ans);
	}
	public static function log($ans, $msg = '', $data = null)
	{
		$ans['result'] = 0;
		if ($msg) {
			$ans['msg'] = $msg;
		}
		if (infra_debug() && !is_null($data)) {
			$ans['msg'] .= '<pre><code>'.print_r($data, true).'</code></pre>';
		}

		error_log(basename(__FILE__).$msg);

		return self::ans($ans);
	}
	public static function ret($ans, $msg = false)
	{
		if ($msg) {
			$ans['msg'] = $msg;
		}
		$ans['result'] = 1;

		return self::ans($ans);
	}
	public static function ans($ans)
	{
		if (infra_isphp()) {
			return $ans;
		} else {
			//error_reporting(E_ALL);
			//ini_set('display_errors',1);
			header('Content-type:application/json; charset=utf-8');//Ответ формы не должен изменяться браузером чтобы корректно конвертирвоаться в объект js, если html то ответ меняется
			echo json_encode($ans, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
		}
	}
	public static function txt($ans)
	{
		if (infra_isphp()) {
			return $ans;
		} else {
			header('Content-type:text/html; charset=utf-8');//Ответ формы не должен изменяться браузером чтобы корректно конвертирвоаться в объект js, если html то ответ меняется
			echo $ans;
		}
	}
}
