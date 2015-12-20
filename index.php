<?php
namespace infrajs\infra;
use infrajs\access\Access;
use infrajs\path\Path;

if (!is_file('vendor/autoload.php')) {
	chdir('../../../');	
}
require_once('vendor/autoload.php');

Config::init();

Access::modified();
Access::headers();

Install::init();

Path::init();
