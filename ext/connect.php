<?php

function &infra_db($debug = false)
{
	infra_cache_no();

	return infra_once('infra_db', function &($debug) {
		$conf = infra_config();
		if (!$debug) {
			$debug = infra_debug();
		}
		$ans = array();
		if (!$conf['infra']['mysql']) {
			return $ans;
		}
		if (!$conf['mysql']) {
			//if($debug)die('Нет конфига для соединения с базой данных. Нужно добавить запись mysql: '.infra_json_encode($conf['/mysql']));
			return $ans;
		}
		$conf = @$conf['mysql'];

		if (!$conf['user']) {
			//if($debug)die('Не указан пользователь для соединения с базой данных');
			return $ans;
		}
		try {
			@$db = new PDO('mysql:host='.$conf['host'].';dbname='.$conf['database'].';port='.$conf['port'], $conf['user'], $conf['password']);
			$db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
			if ($debug) {
				$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			} else {
				$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
			}
				/*array(
				PDO::ATTR_PERSISTENT => true,
				PDO::ATTR_ERRMODE => true,
				PDO::ATTR_ERRMODE=>PDO::ERRMODE_WARNING 
			)*/
			$db->exec('SET CHARACTER SET utf8');
		} catch (PDOException $e) {
			//if($debug)throw $e;
			$db = false;
			/*if(!$debug){
				print "Error!: " . infra_toutf($e->getMessage()) . "<br/>";
				die();
			}*/
		}

		return $db;
	}, array($debug));
}
function infra_stmt($sql)
{
	return infra_once('infra_stmt', function ($sql) {
		$db = infra_db();

		return $db->prepare($sql);
	}, array($sql));
}
