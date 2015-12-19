<?php
namespace infrajs\infra;
use infrajs\mem\Mem;
use infrajs\load\Load;
use infrajs\path\Path;
use infrajs\once\Once;
use infrajs\nostore\Nostore;

class Req {
	public static function reqsrc($src)
	{
		Each::exec($src, function ($src){
			Path::req($src);
		});
	}
	public static function exec($name)
	{
		$conf=Config::get($name);
		if(!isset($conf['require'])) return;
		return static::reqsrc($conf['require']);
	}
}
Req::exec('path');