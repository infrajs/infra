<?php
if (!is_file('vendor/autoload.php')) {
	chdir('../../../');	
}
require_once('vendor/autoload.php');
infrajs\infra\Infra::init();
